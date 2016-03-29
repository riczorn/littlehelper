<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn <code@fasterjoomla.com>
 * @copyright  2011 Riccardo Zorn
 * @license    GNU/GPL v2
 * @link       http://www.fasterjoomla.com/littlehelper
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerFalang extends JControllerForm
{
	protected $default_view = 'falang';

	function __construct()
	{
		parent::__construct();

	}

	/**
	 * This is the main view
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * invert languages
	 * expecting sourceId and targetId
	 *
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function invert($cachable = false, $urlparams = false)
	{
		$model = $this->getModel();
		$input = JFactory::getApplication()->input;
		$sourceId = $input->get('sourceId',0);
		$targetId = $input->get('targetId',0);
		$model->invert($sourceId, $targetId);

	}
}
