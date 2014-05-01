<?php
/**
 * @version		$Id: mod_littlehelper.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


 $langpath =JPATH_ADMINISTRATOR.'/components/com_littlehelper';
 $language = JFactory::getLanguage();
 $language->load('com_littlehelper', $langpath, 'en-GB', true);
 $language->load('com_littlehelper', $langpath, null, true);

 // behavior modal etc?
 JHtml::_('behavior.modal');
 
require_once dirname(__FILE__).'/helper.php';
$toolbar = modLittleHelperHelper::getButtons($module->position);
require JModuleHelper::getLayoutPath('mod_littlehelper', $params->get('layout', 'default'));
