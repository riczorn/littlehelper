<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

//jimport('joomla.application.component.view');

class LittleHelperViewtrash_n_cache extends JViewLegacy
{
	function display($tpl = null)
	{
		// read parameters and assign to self for use in the view's template
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $params->get('params');
		$this->assignRef( 'params', $params );
		
		$data = $this->get('Data');
		$this->assignRef('data',$data);
		
		
		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_TRASH_AND_CACHE"),'littlehelper' );

		if (empty($params->button_recycle) || (int)($params->button_recycle)==1) {
			JToolBarHelper::custom( 'trash_n_cache.emptyrecyclebin','trash','trash',JText::_("COM_LITTLEHELPER_CMD_RECYCLE_SHORT"), false);
		}
		
		if (empty($params->button_cache) || (int)($params->button_cache)==1) {
			if (!isset($params->button_cache_usefs) || (int)($params->button_cache_usefs)==1) {
				JToolBarHelper::custom( 'trash_n_cache.cleanfscache','ccfs' ,'ccfs',JText::_("COM_LITTLEHELPER_CMD_FS_SHORT"),false);
			} else{
				JToolBarHelper::custom( 'trash_n_cache.cleanjoomlacache','ccjoomla','ccjoomla',JText::_("COM_LITTLEHELPER_CMD_JOOMLA_SHORT"), false);
			}
		}

		JToolBarHelper::preferences('com_littlehelper');
		require_once JPATH_COMPONENT.'/helpers/littlehelper.php';
		LittleHelperHelper::addStyles();

		parent::display($tpl);
	}
}
