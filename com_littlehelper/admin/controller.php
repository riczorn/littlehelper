<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn <code@fasterjoomla.com>
 * @copyright  2011 Riccardo Zorn
 * @license    GNU/GPL v2
 * @link       https://www.fasterjoomla.com/littlehelper
 */

/**
 * Main controller, will just display the first page; all others are handled by
 * subcontrollers in the controllers directory.
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');

/**
 * Main Controller
 *
 * @since  0.1
 */
class LittleHelperController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters
	 * 								 and their variable types,
	 * 								 for valid values see
	 * 								 {@link JFilterInput::clean()}.
	 *
	 * @return JController object to support chaining.
	 */
	public function display ($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/littlehelper.php';

		// Load the submenu.
		LittleHelperHelper::init();
		LittleHelperHelper::addSubmenu(JFactory::getApplication()->input->get('view'));

		parent::display();
	}

	/**
	 * Create the module in the specified position (cpanel or status)
	 *
	 * @param   unknown_type  $cachable   // is obj cachable
	 * @param   unknown_type  $urlparams  // any url params
	 *
	 * @return  undefined
	 */
	public function createmodule ($cachable = false, $urlparams = false)
	{
		$position = JFactory::getApplication()->input->get('position');
		$this->getModel('littlehelper')->createModule($position);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}

	/**
	 * Re-publish the module with ID
	 *
	 * @param   unknown_type  $cachable   // is obj cachable
	 * @param   unknown_type  $urlparams  // any url params
	 *
	 * @return  undefined
	 */
	public function publishmodule ($cachable = false, $urlparams = false)
	{
		$id = JFactory::getApplication()->input->get('moduleid', null, 'int');
		$this->getModel('littlehelper')->publishModule($id);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}
}
