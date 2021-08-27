<?php
/**
 * LittleHelper component helper.
 * Main helper: initialize common styles and submenus, check that folders exist (if assigned)
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

class LittleHelperHelper
{
	public static $extension = 'com_littlehelper';
	/**
	 * Performs initialization tasks and checks if folders exist (only if it's assigned in the 
	 * options)
	 */
	public static function init() {
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $params->get('params');
		
		if (!empty($params->favicons_sourcepath)) {
			$fn = JPATH_SITE."/images/".$params->favicons_sourcepath;
			if (!file_exists($fn)) {
				if (mkdir($fn,0755)) {
					chmod($fn,0755);
					//JError::raiseNotice(103,sprintf(JText::_("COM_LITTLEHELPER_FAVICON_FOLDER_CREATED"),$fn));
				} else {
					JError::raiseWarning(104,sprintf(JText::_("COM_LITTLEHELPER_FAVICON_FOLDER_CANNOT_CREATE"),$fn));
				}
			}
// 			$fn = JPATH_SITE."/images/".$params->favicons_sourcepath."/source";
// 			if (!file_exists($fn)) {
// 				if (mkdir($fn,0755)) {
// 					chmod($fn,0755);
// 					//JError::raiseNotice(103,sprintf(JText::_("COM_LITTLEHELPER_FAVICON_FOLDER_CREATED"),$fn));
// 				} else {
// 					JError::raiseWarning(104,sprintf(JText::_("COM_LITTLEHELPER_FAVICON_FOLDER_CANNOT_CREATE"),$fn));
// 				}
// 			}
				
		}
	}
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
			JSubMenuHelper::addEntry(
				JText::_('COM_LITTLEHELPER_INTRO'),
				'index.php?option=com_littlehelper&view=littlehelper',
				$vName == 'default' || $vName == 'littlehelper' || $vName == '');
			
			JSubMenuHelper::addEntry(
				JText::_('COM_LITTLEHELPER_TRASH_AND_CACHE'),
				'index.php?option=com_littlehelper&view=trash_n_cache',
				$vName == 'trash_n_cache');
			
			
			JSubMenuHelper::addEntry(
				JText::_('COM_LITTLEHELPER_FAVICON'),
				'index.php?option=com_littlehelper&view=favicon',
				$vName == 'favicon');
			
			if (JFactory::getUser()->authorise('littlehelper.super', 'com_littlehelper')) {
				JSubMenuHelper::addEntry(
					JText::_('COM_LITTLEHELPER_HTACCESS'),
					'index.php?option=com_littlehelper&view=htaccess',
					$vName == 'htaccess');		
				JSubMenuHelper::addEntry(
					JText::_('COM_LITTLEHELPER_ROOTHTACCESS'),
					'index.php?option=com_littlehelper&view=roothtaccess',
					$vName == 'roothtaccess');
				
				JSubMenuHelper::addEntry(
					JText::_('COM_LITTLEHELPER_HUMANS'),
					'index.php?option=com_littlehelper&view=humans',
					$vName == 'humans');		
			}
			
			JSubMenuHelper::addEntry(
				JText::_('COM_LITTLEHELPER_ROBOTS'),
				'index.php?option=com_littlehelper&view=robots',
				$vName == 'robots');
			// add the help button
			$helpurl = JText::_("COM_LITTLEHELPER_URL");
			JToolBarHelper::help('',false,$helpurl);
				
			
	}
	public static function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet("components/com_littlehelper/assets/css/littlehelper.css");	
	}
}