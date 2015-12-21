<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn <code@fasterjoomla.com>
 * @copyright  2011 Riccardo Zorn
 * @license    GNU/GPL v2
 * @link       http://www.fasterjoomla.com/littlehelper
 */

/**
 * A word on the component.
 *
 * This is a fairly dangerous component, and we put a lot of effort to prevent
 * possible
 * misuse and potential problems.
 *
 * However, I must state that access to this should be limited to your
 * super-administrators.
 * Repeatedly cleaning the cache will affect adversely your site performance
 * (specifically, the subsequent request will take longer).
 *
 * Please read the docs on the component homepage and carefully choose your
 * settings.
 *
 * -- about file deletion --
 * File deletion is performed "the hard way", i.e. without using the Joomla
 * models,
 * rather using shell functions or php unlink in a recursive function. Cache
 * file deletions are not backed up.
 *
 * Recycle bin deletion is also done the hard way: diving into the database and
 * finding those:
 * - articles whose state=-2,
 * - modules and menu items whose published=-2 (where published=0 = suspended
 * and =1 = published).
 * Why joomla articles use state instead of published still puzzles me,
 * however...
 *
 * Since this may be unintentional, in case you accidentally remove items from
 * your recycle bin, you will
 * find the database dump in /tmp/recycle_bin_backup folder.
 */
defined('_JEXEC') or die();

// Access checks are done internally because of different requirements for the
// two controllers.

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Let's make sure we get the language strings translated:
$langpath = JPATH_ADMINISTRATOR . '/components/com_littlehelper';
$language = JFactory::getLanguage();
$language->load('com_littlehelper', $langpath, 'en-GB', true);
$language->load('com_littlehelper', $langpath, null, true);

$controller = JControllerLegacy::getInstance('LittleHelper');
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));
$controller->redirect();
