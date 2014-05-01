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

class LittleHelperViewLittlehelper extends JViewLegacy
{
	public $componentsUrl = "index.php?option=com_littlehelper";
	public $adminModulesUrl = "index.php?option=com_modules&amp;filter_module=mod_littlehelper&amp;filter_client_id=1";
	public $adminModuleConfig = "index.php?option=com_modules&amp;view=module&amp;layout=edit&amp;id=";
	
	function display($tpl = null)
	{
		$data = $this->get('Data');
		$this->assignRef('data',$data);

		$faqlink = sprintf("<a href='%s' target='_blank' class='fancybutton faq'>%s</a>",JText::_("COM_LITTLEHELPER_FAQ_URL"), JText::_("COM_LITTLEHELPER_INTRO_THEFAQ"));
		$this->assignRef('faqlink',$faqlink);
		JToolBarHelper::title(   JText::_("COM_LITTLEHELPER"),'littlehelper' );	
		//JToolBarHelper::custom( 'default','default','default',JText::_("COM_LITTLEHELPER_CMD_DEFAULT"), false);
		JToolBarHelper::preferences('com_littlehelper');
		
		require_once(JPATH_COMPONENT."/helpers/littlehelper.php");
		
		LittleHelperHelper::addStyles();		
		parent::display($tpl);
	}
	
	/**
	 * return the appropriate markup if the module mod_littlehelper exists at the required position
	 * The position can be status or cpanel
	 * @param unknown_type $position
	 */
	protected function getModuleInfo($position) {
		if (in_array($position,array('status','cpanel'))) {
			$modules  = $this->getModel()->getModulesList();
			foreach ($modules as $module) {
				if ($module->position == $position) {
					return $this->getModuleMarkup($module,$position);
				}
			}
			// else... module wasn't found, then let's be kind and offer to publish it for the user.
			return $this->getModuleMarkup(false,$position);
		} else {
			JError::raiseError(500,'Unsupported position '.$position);
		}
	}
	
	private function getModuleMarkup($module,$position) {
		$result = array("<div class='moduleinfo $position'>");
		$result[] = "<h3>".JText::_("COM_LITTLEHELPER_MODULE_IN_POS")." $position</h3>";
		
		if ($module) {
			$pub = (int)($module->published);
			$states = array(0=>JText::_("JUNPUBLISHED"), 1=>JText::_("JPUBLISHED"), -2=>JText::_("JTRASHED"));
			if (array_key_exists($pub, $states)) {
				$result[] = sprintf(JText::_("COM_LITTLEHELPER_MODULE_PUBLISHED"),$module->title,$module->id,$states[$pub],$position);
			} else {
				JError::raiseWarning(100, 
					sprintf(JText::_("COM_LITTLEHELPER_MODULE_PUBLISHED_ERROR"), $module->title, $module->id, $pub));
			}
			if ($pub == 1) {
					return "";
			}
			if (in_array($pub, array(-2,0)) ) {
				$result[] = "<a class='fancybutton modulecreate' href='$this->componentsUrl&amp;task=publishmodule&amp;moduleid=$module->id'>"
						.JText::_("JTOOLBAR_PUBLISH")."</a><br>";
			}
			// com_modules doesn't allow direct access? there must be some other params? read the source.
// 			if (!DEFINED('COM_LITTLEHELPER_LINKTO_MODS'))
// 				DEFINE('COM_LITTLEHELPER_LINKTO_MODS',"1");
// 			$result[] = "<a class='fancybutton' href='$this->adminModulesUrl'>".JText::_("COM_LITTLEHELPER_MODULE_OPEN_CONFIG")."</a>";
		} else {
			$result[]= "<span class='error'>".JText::_("COM_LITTLEHELPER_MODULE_NOT_IN_POS")." $position</span><br>";
			$result[] = "<a class='fancybutton module' href='$this->componentsUrl&amp;task=createmodule&amp;position=$position'>".JText::_("JACTION_CREATE")."</a><br>";
		}
		$result[] = "</div>";
		return implode("\n",$result);
	}
}
