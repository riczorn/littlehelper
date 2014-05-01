<?php
/**
 * Main controller, will just display the first page; all others are handled by 
 * subcontrollers in the controllers directory. 
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * LittleHelper Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_littlehelper
 * @since 1.5
 */
class LittleHelperController extends JControllerLegacy
{

	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/littlehelper.php';
	
		// Load the submenu.
		LittleHelperHelper::init();
		LittleHelperHelper::addSubmenu(JFactory::getApplication()->input->get('view'));

		parent::display();
	}
	
	/**
	 * Create the module in the specified position (cpanel or status)
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function createmodule($cachable = false, $urlparams = false)
	{
		$position = JFactory::getApplication()->input->get('position');
		$this->getModel('littlehelper')->createModule($position);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}
	/**
	 * Re-publish the module with ID
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function publishmodule($cachable = false, $urlparams = false)
	{
		$id = JFactory::getApplication()->input->get('moduleid',null,'int');
		$this->getModel('littlehelper')->publishModule($id);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}	
}
