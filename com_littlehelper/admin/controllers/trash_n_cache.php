<?php
/**
 * Trash & Cache: Recycle bin + Clean cache
 *
 * @package LittleHelper
 * @author Riccardo Zorn <code@fasterjoomla.com>
 * @copyright 2011 Riccardo Zorn
 * @license GNU/GPL v2
 * @link http://www.fasterjoomla.com/littlehelper
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Plugin controller class.
 *
 * @since 1.6
 */
class LittleHelperControllerTrash_n_Cache extends JControllerForm
{

	/**
	 *
	 * @var string default view.
	 * @since 1.6
	 */
	protected $default_view = 'trash_n_cache';

	function __construct ()
	{
		parent::__construct();

		// Register Extra tasks
	}

	/**
	 * Invoked from the toolbar or the module, clean the cache using
	 * JCache->clean;
	 * will have no effect on non-joomla files placed in the cache folder.
	 */
	public function cleanjoomlacache ()
	{
		$model = $this->getModel();
		$model->performCleanCache(true);
		$this->additionalInit();
		parent::display();
	}

	/**
	 * This clears the administrator's cache.
	 * Might only be useful for developers
	 */
	public function clearAdministratorCache ()
	{
		$model = $this->getModel();
		$model->clearAdministratorCache(true);
		$this->additionalInit();
		parent::display();
	}

	/**
	 * Invoked from the toolbar or the module, clean the cache
	 * will delete all folders and display errors as appropriate.
	 */
	public function cleanfscache ()
	{
		$model = $this->getModel();
		$model->performCleanCache(false);
		JFactory::getApplication()->input->set('task', '');
		$this->additionalInit();
		parent::display();
	}

	/**
	 * Loads the components tables
	 * to empty the Recycle bin
	 */
	public function emptyRecycleBin ()
	{
		$this->additionalInit();
		$model = $this->getModel();
		$model->emptyRecycleBin();
		parent::display();
	}

	/**
	 * Examines the component parameter and shows the list of trashed items for
	 * that component
	 *
	 * @return undefined unused
	 */
	public function showTrashedItems ()
	{
		$component = JFactory::getApplication()->input->get('component');
		$model = $this->getModel();

		if ($componentTrash = $model->getItem($component))
		{
			$vName = 'trash_n_cacheitem';
			$document = JFactory::getDocument();
			$vFormat = $document->getType();

			if ($view = $this->getView($vName, $vFormat))
			{
				$view->setModel($model, true);
				$view->componentTrash = $componentTrash;

				$view->display();
			}
		}
	}

	/**
	 * Create the styles and toolbar and submenus for direct calls.
	 *
	 * @return undefined unused
	 */
	private function additionalInit ()
	{
		require_once JPATH_COMPONENT . '/helpers/littlehelper.php';

		// Do folders exist?
		LittleHelperHelper::init();
		LittleHelperHelper::addSubmenu($this->default_view);
	}
}
