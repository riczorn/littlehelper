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

class LittleHelperViewRobots extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		list($robotstxt,$robotsfilename) = $model->load();
		
		$this->assignRef('robotstxt', $robotstxt);
		$this->assignRef('robotsfilename',$robotsfilename);
		
		JToolBarHelper::title( "<em>".  JText::_("COM_LITTLEHELPER")."</em> ".JText::_("COM_LITTLEHELPER_ROBOTS"),'littlehelper' );
		
		JToolBarHelper::save('robots.save');
		JToolBarHelper::custom('robots.fix','fix','fix',JText::_("COM_LITTLEHELPER_CMD_FIX"),false);
		JToolBarHelper::cancel('robots.cancel');
		
		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();
		
		parent::display($tpl);
	}
	
	/** 
	 * display the correct syntax for a robots.txt file's sitemap line.
	 */
	protected function getSyntax() {
		return "<pre>Sitemap: /index.php?option=com_xmap&view=xml&tmpl=component&id=1</pre>";
	}
}
