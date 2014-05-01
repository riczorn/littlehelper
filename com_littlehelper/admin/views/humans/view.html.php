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

class LittleHelperViewHumans extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		list($humanstxt,$humansfilename) = $model->load();
		
		$this->assignRef('humanstxt', $humanstxt);
		$this->assignRef('humansfilename',$humansfilename);
		
		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_HUMANS"),'littlehelper' );
		
		JToolBarHelper::save('humans.save');
		JToolBarHelper::cancel('humans.cancel');
		
		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();
		
		parent::display($tpl);
	}
}
