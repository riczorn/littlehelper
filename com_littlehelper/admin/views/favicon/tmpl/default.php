<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */
/*
 * Icons from http://commons.wikimedia.org/wiki/Tango_icons
 * with some photoshopping
 */
defined('_JEXEC') or die;
$document = JFactory::getDocument();

/*
 * Let's see if we need to add jQuery, and load all libraries:
 */
if (LittleHelperHelperFavicon::$master) {
	$defaultImage = JUri::base(true).LittleHelperHelperFavicon::$master->path . LittleHelperHelperFavicon::$master->name;
} else {
	// first installation
	$defaultImage = null;
}
$assetDir = 'components/com_littlehelper/assets/';
if(version_compare(JVERSION,'2.9.99','lt')) {
	$document->addScript($assetDir . "js/jquery-1.10.2.min.js");
} else {
	JHtml::_('jquery.framework');
}
$document->addScript($assetDir . "js/jquery.Jcrop.min.js");
$document->addScript($assetDir . "js/jquery.color.js");
$document->addScript($assetDir . "js/jquery.rixxcropper.js");
$document->addStyleSheet($assetDir . "css/jquery.Jcrop.css");
$document->addStyleSheet($assetDir . "css/jquery.rixxcropper.css");

/*
 * This loads the initialization scripts; all scripts are placed in this file for ease of editing.
 */
$document->addScriptDeclaration(
		$src = file_get_contents(JPATH_SITE .'/administrator/'. $assetDir . "js/favicon_inlined.js")
	);

// won't work for sites in subfolders: $sitepath = ltrim(dirname(JUri::base(true)),"/") ;
$sitepath = JUri::base(true);
$sitepath = str_replace('/administrator','',$sitepath);


if (!empty($this->params->favicons_sourcepath))
	$fiPath = $this->params->favicons_sourcepath . "/source";
else
	$fiPath = null;


?>

<form
	action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>"
	method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
</form>

<?php 	 

if (empty($fiPath)) {
	echo "<span class='warn'>";
	echo JText::_("COM_LITTLEHELPER_FAVICONS_NO_SOURCE_PATH");
	echo "</span>";
	echo "<br><a href='index.php?option=com_littlehelper&task=favicon.createDefault' class='fancybutton foldercreate'>".JText::_("COM_LITTLEHELPER_FAVICONS_CREATE")."</a>";
	return;
}



// toolbox
if (!empty($fiPath)) { 

	echo "
		<div class='chktransp box'>
		<h3>".JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_BOX_TITLE")."</h3> <br>";
	
	
	echo $this->getUploadForm($fiPath);
		
	if (!empty($this->images)) {
		echo "
			<a href='javascript:changeBackground();' class='fancybutton testtransp'>".JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_TEST_TRANSPARENCY")."</a>
			<br>";
		
		echo '<a href="index.php?option=com_littlehelper&task=favicon.publish" class="fancybutton save">'. JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_SAVE")."</a>";
		/**
		 * Here we propose the markup and we have one button to save it.
		 * The button enables the plugin if necessary, and saves its parameters.
		 * a second button takes to the plugin administration to disable it.
		 * The params are saved to the plugin as getting them from the component would be slower.
		 * 7
		 * The following buttons are now in the toolbar:
		 */
		// echo "<a href='index.php?option=com_littlehelper&task=favicon.enablePlugin' class='fancybutton enableplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_ENABLE")."</a>";
		// echo "<a href='index.php?option=com_littlehelper&task=favicon.disablePlugin' class='fancybutton disableplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_DISABLE")."</a>";
		// echo "<a href='index.php?option=com_plugins&view=plugins&filter_search=Little+Helper' class='fancybutton manageplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_MANAGE")."</a>";
	}
	echo "</div>";
} 
?>

<div class='sa_favicon_main'>
	<h3 class="step"><?php echo JText::_("COM_LITTLEHELPER_FAVICON_TITLE_GALLERY"); ?></h3>
	<div id="gallery">
		<?php 
			$files = scandir($source = JPATH_SITE . LittleHelperHelperFavicon::$sourcePath);
			foreach ($files as $file) {
				if (is_file($source.$file)) {
					printf("<img src='%s' class='galleryimage' onclick='switchImage(this);' />", 
						$sitepath.LittleHelperHelperFavicon::$sourcePath.$file);
				}
			}
			/* admin favicons: */
			// @TODO show admin favicons.
		?>
</div>

	<h3 class="step"><?php echo JText::_("COM_LITTLEHELPER_FAVICON_TITLE_DROPCROP"); ?></h3>
	<div id="dcrop">
		<div class="jc-demo-box">
			<img src="<?php echo $defaultImage; ?>" id="target"
				alt="[Jcrop Example]" />
			<div id="preview-pane">
				<div class="preview-container">
					<img src="<?php echo $defaultImage; ?>" class="jcrop-preview"
						alt="Preview" />
				</div>
				<div class="buttons-container">
					<select nomultiple size="10" id="imagesizes">
						<option value="all" selected><?php echo JText::_("All Sizes"); ?></option>
						<option disabled><?php echo JText::_("-- or choose below --"); ?></option>
					  <?php
							foreach ( LittleHelperHelperFavicon::$favicons as $key => $favicon ) {
								printf ( '<option value="%s">%1$s</option>', $key );
							}
							?>
				  </select>
					<!-- a class="button" href="javascript:saveAll();">Save</a> -->
					<a class="button" href="javascript:saveSize();">Save</a>

					<div id="progressdiv">
						<p id="upload" class="hidden">
							<label>Drag &amp; drop not supported, but you can still upload
								via this input field:<br>
							<input type="file">
							</label>
						</p>

						<p>
							Upload progress:
							<progress id="uploadprogress" min="0" max="100" value="0">0</progress>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<h3 class="step"><?php echo JText::_("COM_LITTLEHELPER_FAVICON_TITLE_PREVIEW"); ?></h3>
	<div id="step1">
		<div class="folder">
	 <?php 
	 if (!empty($fiPath)) {
			echo "<div class='chosenImages' ><div class='topList'>";
			
			
			if (!empty($this->images)) {
				
			
				if (!empty($this->images)) {
					echo "<ul id='chosenImages'>";
						
					foreach ($this->images as $image) {	
						if (empty($image->name) || empty($image->path)) continue;
						$resizedText = $image->resized?JText::_("COM_LITTLEHELPER_FAVICON_RESIZED"):"<i>".JText::_("COM_LITTLEHELPER_FAVICON_ORIGINAL")."</i>";	
						echo sprintf("
							<li>%s<br><img src='%s%s%s' class='thumb %s' /><br>
								<span class='size'>%sx%s</span><br>
								<span class='notes'>%s</span>
								</li>",$image->description,
									$sitepath, $image->path , $image->name."?rand=". rand(120120,990390),
									$image->size,
									$image->width, $image->height,
									$resizedText);
					}
					echo "</ul>";
				}
				echo "</div>";
			
			
				//echo "<span class='warn'>".JText::_("COM_LITTLEHELPER_FAVICON_QUALITY_NOTICE")."</span></div>";
			} else {
				echo "</div></div>";
			}
	 	?>
	 	</div>
		</div>
		<?php 
	 } // here ends the if (empty($fiPath)) {...} else { 
 ?>
 
</div>


