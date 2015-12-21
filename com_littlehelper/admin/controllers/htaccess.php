<?php
/**
 * .htaccess files manager. Appears as "Security" in the component menu
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

class LittleHelperControllerHtaccess extends JControllerForm
{

	protected $default_view = 'htaccess';

	function __construct ()
	{
		parent::__construct();
	}

	/**
	 * Method to display the view.
	 */
	public function display ($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * Cancel edit
	 * 
	 * @see JControllerForm::cancel()
	 */
	public function cancel ($key = null)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper', false));
		$this->redirect();
	}

	/**
	 * Rename an .
	 * htaccess to a random backup name
	 */
	public function delete ()
	{
		$key = JFactory::getApplication()->input->get('key');
		if ($this->getModel()->delete($key))
		{
			$successMessage = strtolower($key) . "/.htaccess " . JText::_("COM_LITTLEHELPER_FILE_RENAMED");
			
			if ($key == "main" || $key == "")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=roothtaccess', false), $successMessage);
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false), $successMessage);
			}
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false));
		}
		$this->redirect();
	}

	/**
	 * Copy a sample .
	 * htaccess in the new place
	 */
	public function create ()
	{
		$key = JFactory::getApplication()->input->get('key');
		if ($this->getModel()->create($key))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false), 
					strtolower($key) . "/.htaccess " . JText::_("COM_LITTLEHELPER_FILE_CREATED"));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false));
		}
		$this->redirect();
	}

	/**
	 * Create index.html in empty dirs
	 */
	public function createIndex ()
	{
		$key = JFactory::getApplication()->input->get('key');
		if ($this->getModel()->create($key, 'index.html'))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false), 
					strtolower($key) . "/index.html " . JText::_("COM_LITTLEHELPER_FILE_CREATED"));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false));
		}
		$this->redirect();
	}

	/**
	 * Create the Joomla root .
	 * htaccess (copy htaccess.txt)
	 */
	public function createRoot ()
	{
		$jinput = JFactory::getApplication()->input;
		$kind = $jinput->get('kind'); // which is insignificant here: this can
		                              // only be invoked in the root.
		$symlinks = $jinput->get('symlinks', '') == 'remove';
		if ($this->getModel()->createRoot($kind, $symlinks))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false), JText::_("COM_LITTLEHELPER_FILE_ROOT_CREATED"));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false));
		}
		$this->redirect();
	}

	/**
	 * Restore a backup of an .
	 * htaccess
	 */
	public function restore ()
	{
		$key = JFactory::getApplication()->input->get('key');
		if ($this->getModel()->restore($key))
		{
			$successMessage = strtolower($key) . "/.htaccess " . JText::_("COM_LITTLEHELPER_FILE_RESTORED");
			if ($key == "main" || $key == "")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=roothtaccess', false), $successMessage);
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false), $successMessage);
			}
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=htaccess', false));
		}
		$this->redirect();
	}

	public function testRoot ()
	{
		$jinput = JFactory::getApplication()->input;
		$kind = $jinput->get('kind'); // which is insignificant here: this can
		                              // only be invoked for the root.
		$symlinks = $jinput->get('symlinks', '') == 'remove';
		if ($this->getModel()->testRoot($kind, $symlinks))
		{
			$this->setRedirect(JURI::root(true) . '/components/com_littlehelper/littlehelper.php');
			$this->redirect();
		}
		else
		{
			// output error message and exit:
			echo "<span class='error'>";
			echo sprintf(JText::_("COM_LITTLEHELPER_FILE_ERROR_TEST_CREATE"), $source, $destination);
			echo "</span>";
			exit();
		}
	}

	/**
	 * Search for common exploits, and return the list of issues with the
	 * filenames.
	 */
	public function findExploits ()
	{
		$model = $this->getModel();
		$model->findExploits();
		$input = JFactory::getApplication()->input;
		
		// exit;
	}
}
