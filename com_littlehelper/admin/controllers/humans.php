<?php
/**
 * humans.txt editor
 *
 * @package    LittleHelper
 * @author     Riccardo Zorn <code@fasterjoomla.com>
 * @copyright  2011 Riccardo Zorn
 * @license    GNU/GPL v2
 * @link       http://www.fasterjoomla.com/littlehelper
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerHumans extends JControllerForm
{
	protected $default_view = 'humans';

	function __construct()
	{
		parent::__construct();

		// Register Extra tasks

	}

	/**
	 * This is just the edit function
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * Cancel edit humans.txt
	 * @see JControllerForm::cancel()
	 */
	public function cancel($key = null)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}

	/**
	 * Save  humans
	 *
	 * @param null $key
	 * @param null $urlVar
	 */
	public function save($key = null, $urlVar = null)
	{
		$humans = JFactory::getApplication()->input->get('humans', '', 'raw');

		if ($this->getModel()->save($humans))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false), JText::_("COM_LITTLEHELPER_HUMANS_MSG_SAVED"));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=humans', false));
		}

		$this->redirect();
	}
}
