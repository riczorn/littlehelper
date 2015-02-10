<?php
/**
 * LittleHelper component helper.
 * Trash n Cache helper: Some utility functions to manage files and folders
 * Most notably it contains the removeFolder() with the shell functions.
 * These are sometimes marked as viral, so this file needs to be submitted to 
 * anvirus companies for manual exclusion after each change.
 * 
 * @version    2.3.3
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @copyright  Riccardo Zorn
 * @license    GNU/GPL
 *
 *             Created on 22-Dec-2011
 *             Last updated on 10-Feb-2015 
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
		if (!self::isRemoveFolderAuthorised())
			return false;
		
		if (file_exists($dir)) {
			if (!is_dir($dir) || is_link($dir)) return unlink($dir);
			foreach (scandir($dir) as $file) {
				if ($file == '.' || $file == '..') continue;
				if (!self::removeFolderPHP($dir . DIRECTORY_SEPARATOR . $file)) {
					chmod($dir . DIRECTORY_SEPARATOR . $file, 0777);
					if (!self::removeFolderPHP($dir . DIRECTORY_SEPARATOR . $file)) {
						chmod($dir . DIRECTORY_SEPARATOR . $file, 0755);
						return false;
					}
				};
			}
			return rmdir($dir);
		} else return true;
	}
	
	/**
	 * Request removal of the $folder (which could also be a file!)
	 * @param unknown_type $folder
	 * res = 1: success
	 * res = 0: failure
	 * @return array(result, errormessage)
	 */
	public static function removeFolder($folder) {
		if (!self::isRemoveFolderAuthorised())
			return array(0,"Not authorised");
		
		$errormessage = "";
		$output = "";
		$res = 1;
		if (file_exists($folder)) {
			list($res,$output)=self::shellrmdir($folder);
			$errormessage = $output;
		} else {
			// default res = 1
			$errormessage =sprintf(JText::_("COM_LITTLEHELPER_ERROR_NOEXIST"),"<b>$folder</b>");
			echo "<div class='result gray'>$errormessage</div>";
		}
		return (array($res,$errormessage));
	}
	
	/**
	* Attempt to remove the $folder with shell (linux) commands, which should be faster.
	 * Several attempts are made, hoping to find a way to invoke system commands. This
	 * depends on the webserver configuration and its users' privileges.
	 * After this is done, regardless of output, a php-recursive function is invoked.
	 * The latter should clean all leftover files in case any of the system
	 * commands were successful.

	 * @param unknown_type $folder
	 * @return multitype:Ambigous <string, unknown> boolean
	 */
	 private static function shellrmdir($folder)
	 {
	 $cmd = "rm -rf ".escapeshellarg("$folder") . " 2>&1";
	 		$res = false;
	 		if(function_exists('system'))
	 		{
		 	@ob_start();
		 	@system($cmd,$res);
		 	$buff = @ob_get_contents();
		 	@ob_end_clean();

		 	}
		 	elseif(function_exists('exec'))
		 	{
		 		@exec($cmd,$results,$res);
		 		$buff = "";
		 		foreach($results as $result)
		 		{
		 			$buff .= $result;
		 		}

		 	}
		 	elseif(function_exists('passthru'))
		 	{
		 		@ob_start();
		 		@passthru($cmd,$res);
		 		$buff = @ob_get_contents();
		 		@ob_end_clean();

		 	}
		 	elseif(function_exists('shell_exec'))
		 	{
		 		$buff = @shell_exec($cmd);
		 		$res = 1;
		 	} else {
		 		// most likely none of the passthru system exec are available:
		 		// thus we need to use a php recursion:
		 	}
		 	// instead, since we're not taking risks, and this is also
		 	// handling wrong file permissions, and if any of the above worked
		 	// there won't be anything to clear, so we're not adding overhead,
		 	// we use this unconditionally.  Of course, we're losing $res but
		 	// do we really care?
		 	self::removeFolderPHP($folder);
		 	return array($res,$buff);
	}
	
	/**
	 * Return the name of the calling class; this is used by 
	 * isRemoveFolderAuthorised() see the comment there.
	 */
	private static function get_calling_class() {
	
		//get the trace
		$trace = debug_backtrace();
	
		// Get the class that is asking for who awoke it
		$class = $trace[1]['class'];
	
		// +1 to i cos we have to account for calling this function
		for ( $i=1; $i<count( $trace ); $i++ ) {
			if ( isset( $trace[$i] ) ) // is it set?
				if ( $class != $trace[$i]['class'] ) // is it a different class
				return $trace[$i]['class'];
		}
	}
	/**
	 * We want to prevent execution from the wrong classes.
	 * The methods removeFolder() and removeFolderPHP() are extremely delicate.
	 * So we get the calling class and see if it's one of our own. 
	 * 
	 * @return boolean
	 */
	private static function isRemoveFolderAuthorised() {
		switch (self::get_calling_class()) {
			case 'LittleHelperModelTrash_n_Cache':
				return true; 
				break;
		} 
		return false;
	}
}
