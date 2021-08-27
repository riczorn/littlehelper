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

class LittleHelperViewFalang extends JViewLegacy
{
	public $items;
	public $languages;
	function display($tpl = null)
	{
		$model = $this->getModel();
		$this->items = $model->load();
		$this->languages = $model->loadLanguages();

		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_FALANG"),'littlehelper' );

		JToolBarHelper::save('falang.save');
		JToolBarHelper::custom('falang.fix','fix','fix',JText::_("COM_LITTLEHELPER_CMD_FIX"),false);
		JToolBarHelper::cancel('falang.cancel');

		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();

		parent::display($tpl);
	}

}
