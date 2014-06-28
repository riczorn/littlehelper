<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

class LittleHelperViewFavicon extends JViewLegacy
{
	
	function display($tpl = null)
	{
		// read parameters and assign to self for use in the view's template
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $params->get('params');
		$this->assignRef( 'params', $params );
		// the following session and config are needed by the upload form in default template.
		$this->session = JFactory::getSession();
		$this->config = JComponentHelper::getParams('com_media');
		
		$model = $this->getModel();
		$this->images = $model->getImages();
		
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		$head = LittleHelperHelperFavicon::getHead();
		$this->head = str_replace('>', '&gt;', str_replace('<', '&lt;', $head));
		
		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_FAVICON"),'littlehelper' );
		
		//JToolBarHelper::media_manager('icons',JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_UPLOAD"));
		$fiPath = "";
		if (!empty($this->params->favicons_sourcepath)) {
			$fiPath = $this->params->favicons_sourcepath;
			JToolBarHelper::custom(
				"mediamanager_favicon",
				'media','media',JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_MEDIA"),false);
			JToolBarHelper::custom(
			"pluginmanager_sak",
			'plugin','plugin',JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_MANAGE"),false);
		}
		// JToolBarHelper , I can't find how to add an url instead of a task. So here goes...

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
				   Joomla.submitbutton = function(task) {
				        switch(task) {
				            case 'mediamanager_favicon':
				
				                window.location.href = 'index.php?option=com_media&folder=$fiPath';
				
				            break;
				            case 'pluginmanager_sak':
				
				                window.location.href = 'index.php?option=com_plugins&view=plugins&filter_search=Little+Helper';
				
				            break;
							default:
								Joomla.submitform(task, document.getElementById('adminForm'));
				        }
				
				    }
				");

		JToolBarHelper::custom('favicon.publish','saveicons','saveicons',JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_SAVE"),false);
		JToolBarHelper::preferences('com_littlehelper');

		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();
		parent::display($tpl);
	}
	
	
	/**
	 * Get the com_media upload form (we're using com_media to upload files,
	 * and this is just the com_media upload form stripped of extra content.
	 * A return-url parameter is added to bring the user back here after the upload
	 * is completed.
	 * Version 2.0: This is now outdated by the new upload feature, but it's still there for compatibility.
	 * @param unknown_type $fiPath
	 */
	function getUploadForm($fiPath) {
		$return = "";// doesn't work : &return=".baseX64Xencode(JRoute::_("index.php?option=com_littlehelper&view=favicon",false));
		$linksession = "&".$this->session->getName().'='.$this->session->getId();
		$asset = JFactory::getApplication()->input->get('asset','');
		$asset = $asset?'&asset='.$asset:'';
		$author = JFactory::getApplication()->input->get('author','');
		$author = $author?'&author='.$author:'';
		
		$linktoken ="&".JSession::getFormToken() . "=1";
		$link = "index.php?option=com_media&amp;task=file.upload&amp;tmpl=component".
			"$linksession$asset$author$linktoken&format=&view=images$return";
		$link = JRoute::_(JURI::base().$link,false);
		// behaviour modal is already available
		?>
		
	
		<form class="uploadform" action="<?php echo $link;?>"
		 		 id="uploadForm" name="uploadForm" method="post" 
		 		  enctype="multipart/form-data">
		 			<input type="hidden" value="<?php echo $fiPath; ?>" name="folder" />
		 			<fieldset id="uploadform" >
		 				<legend><?php echo $this->config->get('upload_maxsize')=='0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></legend>
		 				<div class='dropAltHolder'><?php echo JText::_("COM_LITTLEHELPER_FAVICONS_DROPHERE"); ?></div>
		 					<input type="file" id="upload-file" name="Filedata[]" multiple />
		 					<input type="submit" id="upload-submit" class="fancybutton" value="<?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?>"/>
		 				<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_littlehelper&task=favicon.clearResized'); ?>" />
		 			</fieldset>	 			
		 </form>

		 <?php   		 	
	}
}
