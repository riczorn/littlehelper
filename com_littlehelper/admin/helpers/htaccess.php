<?php
/**
 * LittleHelper component helper.
 * htaccess helper: perform filesystem checks and gets the name of the last backup file
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * LittleHelper component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_littlehelper
 * @since		1.6
 */
class LittleHelperHelperHtaccess
{
	public static $extension = 'com_littlehelper';
	
	/**
	 * Returns the last file by date that matches the provided pattern, or null.
	 * @param unknown_type $pathpattern
	 * @return mixed
	 */
	public static function getLastFile($pathpattern) {
		$files = glob($pathpattern);
		if (!empty($files)) {
			$files = array_combine($files, array_map("filemtime", $files));
			arsort($files);
			return key($files);
		} else {
			return "";}
	}
	
	/**
	 *  Common routine checks files existance and permissions before create/copy,
	 *  return true or false,
	 *  $source: must exist
	 *  $destination: must be writeable + create folder if it doesn't exist;
	 * @param unknown_type $source
	 * @param unknown_type $destination
	 */
	
	public static function testFilesPermissions($source,$destination,$removeDest=false) {
		if ($source && !file_exists($source)) {
			JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_MISSING_SOURCE") . $source);
			return false;
		}
		
		// let's see if the path is valid, if not create with 0755.
		if (!file_exists(dirname($destination))) {
			if (!mkdir(dirname($destination),0755,true)) {
				JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_CREATEFOLDER"). " ".dirname($destination));
				return false;
			} 
		}

		if (file_exists($destination)) {
			if ($removeDest) {
				@unlink($destination);
			} else 
			if (!is_writable($destination)) {
				JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_DESTINATION_NOT_WRITEABLE") . $destination);
				return false;
			}
		}

		
		return true;
	}
}