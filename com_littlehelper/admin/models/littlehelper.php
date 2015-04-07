<?php

/**
 * com_littlehelper main model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelLittleHelper extends JModelLegacy {
	
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}
	private $modulesList = null;
	
	/**
	 * This is used in the view to help the user publish or create the modules.
	 * @return NULL
	 */
	public function getModulesList() {
		if (empty($this->modulesList)) {
			$db = JFactory::getDbo();
			$query = "select id,title,module,published,access,position from #__modules WHERE module='mod_littlehelper'";
			$db->setQuery($query);
			$this->modulesList = $db->loadObjectList();
		}
		return $this->modulesList;
	}
	
	/**
	 * Publish a trashed or unpublished module
	 * @param unknown_type $moduleid
	 * @return mixed
	 */
	public function publishModule($moduleid) {
		$db = JFactory::getDbo();
		$query = "UPDATE #__modules set published='1' WHERE id='$moduleid'";
		$db->setQuery($query);
		return $db->query(); 
	} 

	/**
	 * Create the module in the specified position: also adds the #__modules_menu fake reference 
	 * which is necessary in J2.5/3 if we want our module to actually display (otherwise it will 
	 * say "None" in the pages column in module manager and not show up)
	 * @param unknown_type $moduleid
	 * @return mixed
	 */
	public function createModule($moduleposition) {
		$db = JFactory::getDbo();
		$query = "DELETE FROM `#__modules` WHERE `module`='mod_littlehelper' AND ISNULL(`position`)";
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_DELETE").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}
		if (strtolower($moduleposition)=="cpanel") 
			$moduleTitle = JText::_("COM_LITTLEHELPER_MODULES_POS_CPANEL");
		else
			$moduleTitle = JText::_("COM_LITTLEHELPER_MODULES_POS_STATUS");
		$query = sprintf("INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
			(%s, '', '', 1, %s, 0, 1, 'mod_littlehelper', 1, 0, '', 1, '*');
			",
				$db->quote($moduleTitle),
				$db->quote($moduleposition));
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_CREATE").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}

		$query = sprintf("INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES
			(%s,'0');",
			$db->quote($db->insertid()));
	
		$db->setQuery($query);
		if ( !$db->query()) {
			$error = JText::_("COM_LITTLEHELPER_MODULES_ERROR_CANT_MENU").": ".$db->getErrorMsg();
			JError::raiseWarning(500,$error);
			return false;
		}

		return true;
	}	
	
	/**
	 * Send a very basic simple email message to the current user, 
	 * including the configuration
	 * of the website's mail functions.
	 * @return boolean|mixed
	 */
	public function sendemail() {

		$data = 'Courtesy of Fasterjoomla.com, keeping your sites tidy since 1994.';
		$error = '';
		// now let's send an email to the admin users
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$sender = array(
				$config->get( 'mailfrom' ),
				$config->get( 'fromname' ) );
	
		$mailer->setSender($sender);
		$app = JFactory::getApplication();
		$mailrecipients = JFactory::getUser()->get('email');
		$app->enqueueMessage('Sending email to '.$mailrecipients);
		$recipient = explode(',',$mailrecipients);
		if (empty($recipient)) {
			$app->enqueueMessage(JText::_("COM_LITTLEHELPER_MAIL_NO_RECIPIENT"));
			return false;
		}
		$mailer->addRecipient($recipient);
		$error = "\n\n".$data."\n\n".$error;
	
		$user = JFactory::getUser();
		$sitename = $config->get('sitename');
		
		$message = array();
		$message[] = sprintf(JText::_("COM_LITTLEHELPER_MAIL_MESSAGE"),$sitename);
		$message[] = "\n\n";
		$message[] =("Server mail configuration");
		
		$message[] = $this->echoParam($config, 'mailer');
		$message[] = $this->echoParam($config, 'mailfrom');
		$message[] = $this->echoParam($config, 'fromname');
		$message[] = $this->echoParam($config, 'sendmail');
		$message[] = $this->echoParam($config, 'smtpauth');
		$message[] = $this->echoParam($config, 'smtpuser');
		// I won't echo the password, but I want to test it for whitespace:
		$this->echoParam($config, 'smtppass');
		$message[] = $this->echoParam($config, 'smtphost');
		$message[] = $this->echoParam($config, 'smtpsecure');
		$message[] = $this->echoParam($config, 'smtpport');
		$message[] = $this->echoParam($config, 'mailonline');
		
		$message[] = "\n\n";
		$message[] = $data;
		
		$body   = join("\n", $message);

		$mailer->setSubject(JText::_("COM_LITTLEHELPER_MAIL_SUBJECT"));
		$mailer->setBody($body);

		try {
			$result = $mailer->Send();
		} catch (Exception $e) {
			$app->enqueueMessage($e->message,'warning');
			$result = false;
		}
		
		
		$app->enqueueMessage("Message: <pre>".str_replace("\n","<br>",$body)."</pre>");
		

		
		return $result;
	}
	
	/**
	 * Used to build the configuration part of the test email
	 * @param unknown $paramName
	 */
	private function echoParam($config, $paramName) {
		$languageConstant = "COM_CONFIG_FIELD_".strtoupper($paramName)."_LABEL";
		$label = JText::_($languageConstant);
		if ($label == $languageConstant) {
			$label = ucfirst($paramName);
		}
		$value = $config->get( $paramName );
		if ($value !== trim($value," \n\r\f\t")) {
			$label .= ' *';
			$app = JFactory::getApplication(); 
			$app->enqueueMessage($paramName . ' * '. JText::_("COM_LITTLEHELPER_MAIL_CONFIG_EMPTY"),'warning');
		}
		return  (str_pad($label,11).": '".$value."'");
	}
}