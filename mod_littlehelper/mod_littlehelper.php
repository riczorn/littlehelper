<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL v2
 */

defined('_JEXEC') or die;


$langpath =JPATH_ADMINISTRATOR.'/components/com_littlehelper';
$language = JFactory::getLanguage();
// this is not working in J3+, go figure!
// the required strings have been copied to the mod_littlehelper/language files.
$language->load('com_littlehelper', $langpath, 'en-GB', true);
$language->load('com_littlehelper', $langpath, null, true);

 
require_once dirname(__FILE__).'/helper.php';

if (!modLittleHelperHelper::excludeComps()) {
	// for some reason this breaks when other components are loaded.
	JHtml::_('behavior.modal');
}
$toolbar = modLittleHelperHelper::getButtons($module->position);
require JModuleHelper::getLayoutPath('mod_littlehelper', $params->get('layout', 'default'));
