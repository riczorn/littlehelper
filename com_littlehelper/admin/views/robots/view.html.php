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
		JToolBarHelper::custom('robots.fixsitemap','fix','fix',JText::_("COM_LITTLEHELPER_CMD_FIX_SITEMAP"),false);
		JToolBarHelper::cancel('robots.cancel');
		
		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		LittleHelperHelper::addStyles();
		
		parent::display($tpl);
	}
	
	/** 
	 * Display the correct syntax for a robots.txt file's sitemap line.
	 * Please note: this will see if jSitemap or xMap are installed, and propose the suggested change.
	 */
	protected function getSyntax() {
		$model = $this->getModel();
		if ($sitemapUrl = $model->getSitemapUrl()) {
		
			return "<pre>Sitemap: ". JUri::root(false). $sitemapUrl. "</pre>";
		} else {
			return "No sitemap software detected; find out your sitemap url; the syntax is as shown below:<pre>"
						.JUri::root(false).'sitemap.xml</pre>';
		}
	}
}
