<?php

/**
 * com_littlehelper main model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelLittleHelper extends JModelLegacy {
	
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}
	private $modulesList = null;
	
	/**
	 * This is used in the view to help the user publish or create the modules.
	 * @return NULL
	 */
	public function getModulesList() {
		if (empty($this->modulesList)) {
			$db = JFactory::getDbo();
			$query = "select id,title,module,published,access,position from #__modules WHERE module='mod_littlehelper'";
			$db->setQuery($query);
			$this->modulesList = $db->loadObjectList();
		}
		return $this->modulesList;
	}
	
	/**
	 * Publish a trashed or unpublished module
	 * @param unknown_type $moduleid
	 * @return mixed
	 */
	public function publishModule($moduleid) {
		$db = JFactory::getDbo();
		$query = "UPDATE #__modules set published='1' WHERE id='$moduleid'";
		$db->setQuery($query);
		return $db->query(); 
	} 

	/**
	 * Create the module in the specified position: also adds the #__modules_menu fake reference 
	 * which is necessary in J2.5/3 if we want our module to actually display (otherwise it will 
	 * say "None" in the pages column in module manager and not show up)
	 * @param unknown_type $moduleid
	 * @return mixed
	 */
	public function createModule($moduleposition) {
		$db = JFactory::getDbo();
		$query = "DELETE FROM `#__modules` WHERE `module`='mod_littlehelper' AND ISNULL(`position`)";
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_DELETE").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}
		if (strtolower($moduleposition)=="cpanel") 
			$moduleTitle = JText::_("COM_LITTLEHELPER_MODULES_POS_CPANEL");
		else
			$moduleTitle = JText::_("COM_LITTLEHELPER_MODULES_POS_STATUS");
		$query = sprintf("INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
			(%s, '', '', 1, %s, 0, 1, 'mod_littlehelper', 1, 0, '', 1, '*');
			",
				$db->quote($moduleTitle),
				$db->quote($moduleposition));
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_CREATE").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}

		$query = sprintf("INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES
			(%s,'0');",
			$db->quote($db->insertid()));
	
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_MENU").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}

		return true;
	}	
}