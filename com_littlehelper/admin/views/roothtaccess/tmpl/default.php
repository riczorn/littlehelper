<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();

?>
<script>
		function htaccessdelete() {
			var htAlert = "<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_WARN_DELETE_ROOTHTACCESS"); ?>";
			if (confirm(htAlert)) {
				Joomla.submitform("htaccess.delete", document.getElementById("htaccess-form"));
			}
		}
		
		function htaccesscreateroot() {
			var url = getUrl(false); // false for create
			
			testTimer = setTimeout(onTestFailure,3000);
			document.location.href=url;
		}
		
		function htaccessrestoreroot(key,file) {
			var htAlert = "<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_WARN_RESTORE_ROOTHTACCESS");?>";
			
			htAlert = htAlert + "\n" + file;
			if (confirm(htAlert)) {
				Joomla.submitform("htaccess.restore", document.getElementById("htaccess-form"));
			}
		}		

		<?php
		/**
		 *  Before we install a wrong .htaccess file in the root, let's make sure 
		 *  it works.  So we install it into the frontend component's view
		 *  and setup a timer.  If the script works, it invokes onTestSuccess.
		 *  If it doesn't the timer kicks in and invokes onTestFailure.
		 *  
		 *  It writes the appropriate .htaccess file
		 *  this is done by the controller which then redirects to 
		 *  /components/com_littlehelper/littlehelper.php
		 *  
		 *  See the phpDoc of the onTestSuccess and onTestFailure callbacks below;
		 */
		?>
		var testTimer = false;
		function clearTimer() {
			if (testTimer) {
				clearTimeout(testTimer);
				testTimer = false;
			}
		}
		function showTestInProgress(show) {
			if (show) 
				document.getElementById("testrunning").style.display="inline-block";
			else
				document.getElementById("testrunning").style.display="none";
		}
		
		function starthtaccesstest() {
			clearTimer();
			document.getElementById("rht_container").style.display='block';
			document.getElementById("leave_symlinks").checked=true;
			htaccesstest();
		}

		function getUrl(doTest) {
			var task='testRoot';
			if (doTest) {
				description = '<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_DESC_TESTING"); ?>';
				task = 'testRoot';
			} else {
				description = '<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_DESC_CREATING"); ?>';
				task = 'createRoot';
			}
			var symlinks = "";
			
			var message = description+" <?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_BP"); ?>";
			var url = "index.php?option=com_littlehelper&task=htaccess."+task;
			if (document.getElementById("kind_joomla").checked) {
				message = description+" <?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_JOOMLA"); ?>";
				url = url + "&kind=joomla";
			}
			if (document.getElementById("no_symlinks").checked) {
				message = message + " " + "<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_DESC_NO_SYMLINKS"); ?>";
				url = url + "&symlinks=remove";
			}
			showInfo(message);
			return url;
		}
		
		function htaccesstest() {
			var url = getUrl(true); // true for test
			showTestInProgress(true);
			
			testTimer = setTimeout(onTestFailure,3000);
			setTimeout('document.getElementById("rht_preview").src="'+url+'";',1000);
		}
		
		function onTestSuccess() {
			clearTimer();
			showTestInProgress(false);
			showLayers(true); // this will show the "write .htaccess" button.
			// test was successful, php works.  
		}
		
		function onTestFailure() {
			clearTimer();
			showTestInProgress(false);
			if (!document.getElementById("no_symlinks").checked) {
				// let's test again without the symlinks directive:
				document.getElementById("no_symlinks").checked = true;
				htaccesstest();
			} else {
				showInfo("<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_FAILED"); ?>"); 
				
			}
		}		
		
		function showLayers(isSuccess) {
			document.getElementById("rht_buttons_info").style.display = "none";
			document.getElementById("rht_buttons_success").style.display = isSuccess?"block":"none";
		}
		
		function showInfo(message) {
			document.getElementById("rht_buttons_success").style.display = "none";
			document.getElementById("rht_buttons_info").style.display = "block";
			document.getElementById("rht_buttons_info").innerHTML = message;
}
</script>
<?php 

/**
 * Developers please read:
 * This page tests and writes .htaccess files to the root.
 * Since this could break your site if the .htaccess files conflict with your Apache configuration,
 * we are performing a test of the files using the frontend component's url.
 */

$_imgOk = 		"<span class='htx info protected' title=\"%s %s\">The root .htaccess file exists</span>";
$_imghtDelete =  "<a class='fancybutton delete'       title=\"%s %s\" onclick='htaccessdelete(\"%%s\")'>delete root .htaccess</a>";
$_imghtCreate =  "<a class='fancybutton create'       title=\"%s %s\" onclick='htaccesscreateroot(\"%%s\")'>Save the .htaccess file</a>";
$_imghtTest =  "<a class='fancybutton create test'       title=\"%s %s\" onclick='starthtaccesstest(\"%%s\")'>test</a>";
$_imghtRestore = "<a class='fancybutton restore' 	  title=\"%s %s\" onclick='htaccessrestoreroot(\"%%s\",\"%s\")'>restore last .htaccess from backup</a>";

$htfile = $this->htfile;
	$class = $htfile->exists?"exists":"";
	$class .= $htfile->kind=='default'?" default":"";
	$linkCreate = $linkTest = $linkDelete = $imghtDelete = $imgOk = $status = "";
	$title = JText::_("COM_LITTLEHELPER_HTACCESS_ROOT");
	$testtitle = JText::_("COM_LITTLEHELPER_HTACCESS_ROOT_TEST");
	$imghtCreate = sprintf($_imghtCreate, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_CREATE_BOILERPLATE_TOOLTIP"), $htfile->file);
	$linkCreate = sprintf($imghtCreate,strtoupper($htfile->code));
	
	if ($htfile->exists) {
		$imgOk = sprintf($_imgOk,JText::_("COM_LITTLEHELPER_HTACCESS_FILE_EXISTS"),$htfile->file);
		
		$imghtDelete = sprintf($_imghtDelete, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_DELETE_TOOLTIP"),$htfile->file);
		$linkDelete = sprintf($imghtDelete,strtoupper($htfile->code));
		$imghtRestore = $imgOk.$linkDelete;
	} else {
		$imghtTest = sprintf($_imghtTest, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_TEST_TOOLTIP"), $htfile->file);
		
		
		$linkTest = sprintf($imghtTest,strtoupper($htfile->code)) ;
		
		$imghtRestore = sprintf($this->getRestoreLink($_imghtRestore, $htfile->file)
				, $htfile->code);
	} 
	$buttonrestore = $imghtRestore;
	
	$buttonstest = sprintf("<div class='%s'><span class='title'>%s</span>".
		"<span class='htaccess' title='%s'>%s</span>".
		"</div>",
		$class,
		$testtitle,
		$htfile->file,
		$linkTest
	);
	$buttonCreate = sprintf("<div class='%s'>%s".
		"</div>",
		$class,
		$linkCreate
	);

?>

<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="htaccess-form">
<input type="hidden" name="task" value="" />
<input type="hidden" name="key" id="htaccesskey" value="main" />
<?php echo JHtml::_('form.token'); ?>
</form>

<div id="roothtaccessbuttons">
<div style="float:right"><?php echo $imghtRestore; ?></div>
<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_CHOOSE"); ?>
<br>
<ul class="left50">
	<li>
		<input type="radio" name="htaccess_kind" id="kind_bp" checked="true" />
		<label for="kind_bp"><?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_BP"); ?></label>
	</li>
	<li>
		<input type="radio" name="htaccess_kind" id="kind_joomla" />
		<label for="kind_joomla"><?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TEST_JOOMLA"); ?></label>
	</li>
</ul>
<div style="display:none">
	/* Developers: the following is hidden but it is used for testing, do not remove! */
	<ul class="left50">
		<li>
			<input type="radio" name="htaccess_symlinks" id="leave_symlinks" checked="true" />
			<label for="leave_symlinks">Use the directive: Options +FollowSymLinks</label>
		</li>
		<li>
			<input type="radio" name="htaccess_symlinks" id="no_symlinks" />
			<label for="no_symlinks">Remove the directive: Options +FollowSymLinks</label>
		</li>
	</ul>
</div>
<div class="clear:both"></div>
<a class='fancybutton create test'       title="<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_FILE_TEST_TOOLTIP");?>"
	 onclick='starthtaccesstest()'><?php echo JText::_("COM_LITTLEHELPER_HTACCESS_FILE_TEST");?></a>

<span id="testrunning" style="display:none"></span>
</div>

<div id="rht_container" class="preview" style="display:none">
	<div id="rht_buttons_info" class="buttons">
		<?php echo JText::_("COM_LITTLEHELPER_TEST_HTACCESS_FAIL"); ?>
	</div>	
	
	<iframe id="rht_preview"></iframe>
	<div id="rht_buttons_success" class="buttons">
		<?php echo JText::_("COM_LITTLEHELPER_TEST_HTACCESS_SUCCESS"); ?>
		<?php echo $buttonCreate; ?>
	</div>

	
</div>
