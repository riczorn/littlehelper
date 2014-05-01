<?php
/**
 * robots.txt editor
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerRobots extends JControllerForm
{

	protected $default_view = 'robots';
	
   function __construct() {
        parent::__construct();
    }
	
	/**
	 * This is just the edit function
	 * @see JController::display()
	 */
	public function display($cachable = false, $urlparams = false)
	{	
		parent::display();
	}
	
	/**
	 * Cancel edit robots.txt
	 * @see JControllerForm::cancel()
	 */
	public function cancel($key = null) {
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}
	/**
	 * Save  robots
	 */
	public function save($key = null, $urlVar = null) {
		$robots = JFactory::getApplication()->input->get('robots','','string');
		if ($this->getModel()->save($robots)) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false),JText::_("COM_LITTLEHELPER_ROBOTS_MSG_SAVED"));
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=robots', false));
		}
		$this->redirect();		
	}

	/**
	 * Remove the restriction on the images/ folder.
	 */
	public function fix() {
		$robots = JFactory::getApplication()->input->get('robots','','string');
		if ($this->getModel()->fix($robots)) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=robots', false),JText::_("COM_LITTLEHELPER_ROBOTS_MSG_FIXED"));
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=robots', false));
		}
		$this->redirect();		
	}
}
