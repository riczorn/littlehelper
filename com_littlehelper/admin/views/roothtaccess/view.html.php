<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

//jimport('joomla.application.component.view');

class LittleHelperViewRoothtaccess extends JViewLegacy
{
	function display($tpl = null)
	{
		// this is using the main htaccess model, there is no dedicated model.
		$model = JModelLegacy::getInstance('htaccess','LittleHelperModel'); // $this->getModel('htaccess');
		$htfiles = $model->load();
		/**
		 * $htaccess will contain an array of .htaccess files structures 
		 * i.e.
		 * /code/filename/exists/ismodified
		 * and will be rendered with a checkbox to optionally create the missing files
		 */
		$htfile = false;
		foreach ($htfiles as $htf) {
			if (strtolower($htf->code)=="main")  {
				$htfile = $htf;
				break;
			}
		}
		$this->assignRef('htfile', $htfile);
		
		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_HTACCESS_ROOT"),'littlehelper' );		
		JToolBarHelper::preferences('com_littlehelper');
				
		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();
		
		parent::display($tpl);
	}
	
	/**
	 * Searches the folder for the latest version to restore; if found, it will return the button code.
	 * @param unknown_type $_imghtRestore
	 * @param unknown_type $htfilename
	 */
	function getRestoreLink($_imghtRestore, $htfilename) {
		require_once(JPATH_ADMINISTRATOR."/components/com_littlehelper/helpers/htaccess.php");
		$htfile =	LittleHelperHelperHtaccess::getLastFile( $htfilename."_backup_*");
		if (!empty($htfile)) {
			return(sprintf($_imghtRestore, JText::_("COM_LITTLEHELPER_HTACCESS_FILE_RESTORE_TOOLTIP"), $htfile, $htfile));
		} else {
			return "";
		}
	}
}
