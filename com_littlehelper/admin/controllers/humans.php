<?php
/**
 * humans.txt editor
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerHumans extends JControllerForm
{

	protected $default_view = 'humans';
	
   function __construct() {
        parent::__construct();

        // Register Extra tasks
       
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
	 * Cancel edit humans.txt
	 * @see JControllerForm::cancel()
	 */
	public function cancel($key = null) {
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}
	/**
	 * Save  humans
	 */
	public function save($key = null, $urlVar = null) {
		$humans = JFactory::getApplication()->input->get('humans','','raw');
		if ($this->getModel()->save($humans)) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false),JText::_("COM_LITTLEHELPER_HUMANS_MSG_SAVED"));
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=humans', false));
		}
		$this->redirect();		
	}
	

}
