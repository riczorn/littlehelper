<?php
/**
 * some generic utilities, send test email
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerLittlehelper extends JControllerForm
{

	protected $default_view = 'toomanyfiles';
	
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
	 * Save  robots
	 */
	public function sendemail($key = null, $urlVar = null) {
		$app = JFactory::getApplication();
		
		if ($this->getModel()->sendemail() ) {
			$app->enqueueMessage(JText::_('COM_LITTLEHELPER_MAIL_SENT'));
		} else {
			$app->enqueueMessage(JText::_('COM_LITTLEHELPER_MAIL_NOT_SENT'));
		}
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=littlehelper', false));
		$this->redirect();
	}

}
