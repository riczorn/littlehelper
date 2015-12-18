<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();

?>
<script>
		function htaccessdelete(key) {
			if (key=="MAIN") return;
			document.getElementById("htaccesskey").value=key;
			Joomla.submitform("htaccess.delete", document.getElementById("htaccess-form"));
		}
		function htaccesscreate(key) {
			if (key=="MAIN") return;
			var htAlert = "<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_WARN_CREATE_HTACCESS"); ?>";
			if (confirm(htAlert)) {
				document.getElementById("htaccesskey").value=key;
				Joomla.submitform("htaccess.create", document.getElementById("htaccess-form"));
			}
		}
		function htaccessrestore(key,file) {
			if (key=="MAIN") return;
			var htAlert = "<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_WARN_RESTORE_HTACCESS");?>";
					
			htAlert = htAlert + "\n" + file;
			if (confirm(htAlert)) {
				document.getElementById("htaccesskey").value=key;
				Joomla.submitform("htaccess.restore", document.getElementById("htaccess-form"));
			}
		}
		
		function indexcreate(key) {
			document.getElementById("htaccesskey").value=key;
			Joomla.submitform("htaccess.createindex", document.getElementById("htaccess-form"));
		}		
		function unlock() {
			var htAlert = "<?php  
				echo JText::_("COM_LITTLEHELPER_HTACCESS_WARN_CREATE_ROOTHTACCESS")
					. ' '. JText::_("COM_LITTLEHELPER_HTACCESS_WARN_UNLOCK"); ?>";
			if (confirm(htAlert)) {
				document.location.href="index.php?option=com_littlehelper&view=roothtaccess";
			}
		}
	
</script>
<div id="exploitbox">
<?php echo JText::_("COM_LITTLEHELPER_EXPLOIT_DESC"); ?>
<a class="button popup" href="index.php?option=com_littlehelper&task=htaccess.findExploits">
<?php echo JText::_("COM_LITTLEHELPER_EXPLOIT_LABEL"); ?>
</a>
</div>
<?php echo JText::_("COM_LITTLEHELPER_HTACCESS_DESC"); ?><br><br>
<table class="adminlist htfiles table table-striped table-condensed">
<tr><th><?php echo JText::_("COM_LITTLEHELPER_HTACCESS_TYPE"); ?></th><th>.htaccess</th><th>index.html</th></tr>
<?php 
$odd = false;
$_imgOk = 		"<span class='htx info protected' title=\"%s %s\">".JText::_("COM_LITTLEHELPER_HTACCESS_FOLDER_IS_PROTECTED")."</span>";
$_imghtDelete =  "<a class='htx delete'       title=\"%s %s\" onclick='htaccessdelete(\"%%s\")'>".JText::_("COM_LITTLEHELPER_HTACCESS_DELETE")."</a>";
$_imghtCreate =  "<a class='htx protect'       title=\"%s %s\" onclick='htaccesscreate(\"%%s\")'>".JText::_("COM_LITTLEHELPER_HTACCESS_PROTECT")."</a>";
$_imghtRestore = "<a class='htx restore' 	  title=\"%s %s\" onclick='htaccessrestore(\"%%s\",\"%s\")'>".JText::_("COM_LITTLEHELPER_HTACCESS_RESTORE")."</a>";
$_imgIndexCreate = "<a class='htx create' 	  title=\"%s\" onclick='indexcreate(\"%%s\")'>".JText::_("COM_LITTLEHELPER_HTACCESS_CREATEINDEX")."</a>";
$_imgLocked    =  "<a class='locked' id='lockbutton'	  title=\"%s\" href=\"index.php?option=com_littlehelper&view=roothtaccess\">%s</a>";

foreach ($this->htfiles as $htfile) {
	$class = $htfile->exists?"exists":"";
	$class .= $htfile->kind=='default'?" default":"";
	$class .= $odd?" row0":" row1";
	$linkCreate = $linkDelete = $imghtDelete = $imgOk = "";
	
	$odd = !$odd;
	if ($htfile->exists) {
		$imgOk = sprintf($_imgOk,JText::_("COM_LITTLEHELPER_HTACCESS_FILE_EXISTS"),$htfile->file);

		$imghtDelete = sprintf($_imghtDelete, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_DELETE_TOOLTIP"),$htfile->file);
		$linkDelete = sprintf($imghtDelete,strtoupper($htfile->code));
		$status = $imgOk.$linkDelete;
	} else {
		$imghtRestore = sprintf($this->getRestoreLink($_imghtRestore, $htfile->file)
			,$htfile->code);
		if (strtolower($htfile->code)=="main") {
			$imghtCreate = sprintf($_imghtCreate, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_CREATE_BOILERPLATE_TOOLTIP"), $htfile->file);
			//$linkCreate = sprintf($imghtCreateJ,strtoupper($htfile->code));
		} else  {
			$imghtCreate = sprintf($_imghtCreate, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_CREATE_TOOLTIP"), $htfile->file);
		}
		$linkCreate = sprintf($imghtCreate,strtoupper($htfile->code));
		
		$status = $linkCreate.$imghtRestore;
	}
	$linkIndexCreate = "";
	if (!empty($htfile->index)) {
		if ($htfile->indexExists) {
			$imgOk = sprintf($_imgOk,JText::_("COM_LITTLEHELPER_INDEX_FILE_EXISTS"),$htfile->index);
			$statusIndex = $imgOk;
		} else {
			$imgIndexCreate = sprintf($_imgIndexCreate, JText::_("COM_LITTLEHELPER_INDEX_CREATE_TOOLTIP")) ;
			$linkIndexCreate = sprintf($imgIndexCreate,strtoupper($htfile->code));
			$statusIndex = $linkIndexCreate;
		}
	} else { // Joomla root, we don't want it:
		$statusIndex = "";
	}
	
	if (strtolower($htfile->code)=="main") {
		// we need to wrap the code and add an extra security measure:
		$linkLocked = sprintf($_imgLocked,JText::_(""),JText::_("COM_LITTLEHELPER_HTACCESS_VIEW_ROOT_FUNCTIONS"));
		$status = $linkLocked;
		
	}
	$title = JText::_("COM_LITTLEHELPER_HTACCESS_".strtoupper($htfile->code));
	echo sprintf("<tr class='%s'><td class='title'>%s</td>".
			"<td class='htaccess' title='%s'>%s</td>".
			"<td class='index' title='%s'>%s</td></tr>",
			$class,
			$title,

			$htfile->file,
			$status,

			$htfile->index,
			$statusIndex
		);
}
?>
</table>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="htaccess-form">
<input type="hidden" name="task" value="" />
<input type="hidden" name="key" id="htaccesskey" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>

