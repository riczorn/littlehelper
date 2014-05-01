<?php
/**
 * LittleHelper component helper.
 * Trash n Cache helper: Some utility functions to manage files and folders
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
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
class LittleHelperHelperTrash_n_Cache
{

	/**
	 * Formats a size with the right suffix for bytes
	 * @param unknown_type $size
	 */
	static function formatsize($size) {
	    $mod = 1024;
	    $units = explode(' ','B KB MB GB TB PB');
	    for ($i = 0; $size > $mod; $i++) {
	        $size /= $mod;
	    }
	    return round($size, 2) . ' ' . $units[$i];
	}
	
	/**
	 * Returns the size of a folder
	 * @param unknown_type $path
	 */
	public static function foldersize($path) 
	{
	    $total_size = 0;
	    if (!file_exists($path)) return 0;
	    $files = scandir($path);
	
	    foreach($files as $t) {
	        if (is_dir(rtrim($path, '/') . '/' . $t)) {
	            if ($t<>"." && $t<>"..") {
	                $size = self::foldersize(rtrim($path, '/') . '/' . $t);
	                $total_size += $size;
	            }
	        } else {
	            $size = filesize(rtrim($path, '/') . '/' . $t);
	            $total_size += $size;
	        }   
	    }
	    return $total_size;
	}
	
	static function foldersizebytes($path) 
	{
		return self::formatsize(self::foldersize($path));
	}
	/**
	 * Since passthrough exec or system may not always be available, this
	 * is a fallback.
	 * @param unknown_type $folder
	 */
	static function removeFolderPHP($dir) {
		if (file_exists($dir)) {
			if (!is_dir($dir) || is_link($dir)) return unlink($dir);
			foreach (scandir($dir) as $file) {
				if ($file == '.' || $file == '..') continue;
				if (!self::removeFolderPHP($dir . DIRECTORY_SEPARATOR . $file)) {
					chmod($dir . DIRECTORY_SEPARATOR . $file, 0777);
					if (!self::removeFolderPHP($dir . DIRECTORY_SEPARATOR . $file)) return false;
				};
			}
			return rmdir($dir);
		} else return true;
	}
}
