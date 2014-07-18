<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL v2
 */

defined('_JEXEC') or die;

$langpath =JPATH_ADMINISTRATOR.'/components/com_littlehelper';
$language = JFactory::getLanguage();
$language->load('com_littlehelper', $langpath, 'en-GB', true);
$language->load('com_littlehelper', $langpath, null, true);

// behavior modal etc?
JHtml::_('behavior.modal');
 
require_once dirname(__FILE__).'/helper.php';
if (modLittleHelperHelper::excludeK2())
	return;
$toolbar = modLittleHelperHelper::getButtons($module->position);
require JModuleHelper::getLayoutPath('mod_littlehelper', $params->get('layout', 'default'));
