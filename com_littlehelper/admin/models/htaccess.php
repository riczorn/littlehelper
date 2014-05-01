<?php

/**
 * com_littlehelper htaccess model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelHtaccess extends JModelLegacy {
	
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
		$this->_data=array();
	}
	
	/**
	 * Determine if we're running on apache: otherwise all .htaccess logic is meaningless.
	 */
	public function isApache() {
		// This should really be implemented along with support for IIS files.
		return true;
	}
	
	/**
	 * Find out which .htaccess are already on the filesystem
	 */
	public function load() {
		$htfiles = array();
		
		foreach(array("","images","tmp","cache","administrator/cache") as $key) {
			$keyDS = empty($key)?"":$key."/";
			$code = empty($key)?"MAIN":strtoupper($key);
			$code = str_replace("/","_",$code);
			
			$fileName = JPATH_SITE."/".$keyDS.".htaccess";
			$indexFile = "";
			$indexFileExists = false;
			
			if (!empty($keyDS)) {
				$indexFile = JPATH_SITE."/".$keyDS."index.html";
				$indexFileExists = file_exists($indexFile);
			}
				
			$kind = "littlehelper default";
			if (empty($key)) {
				$kind = "joomla default";
			}
			
			$htfiles[] = (object) array(
					'code' => $code,
					'file' => $fileName,
					'exists' => file_exists($fileName),
					'index' => $indexFile,
					'indexExists' => $indexFileExists,
					'kind' => 'joomla default');
		}
		return $htfiles;
	}
	
	/**
	 * Save the specified .htaccess to disk.
	 * @return true for success
	 */
	public function save($htcontents) {
		$filename = JPATH_SITE."/humans.txt";
		if (!$this->testFilesPermissions(false,$filename)) {
			return false;
		}
		if (file_put_contents($filename, $htcontents)) {
			return true;
		} else {
			JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_SAVE")." $filename");
			return false;
		}
	}

	/**
	 * Delete (i.e. rename to a backup) the specified .htaccess file
	 * @param unknown_type $key
	 * @return boolean
	 */
	public function delete($key) {
		$filename = $this->getFilename($key);
		if (!$filename) {
			JError::raiseWarning(100,JText::_("COM_LITTLEHELPER_FILE_ERROR_FOLDER_DOESNT_EXIST").$filename);
			return false;
		} else {
			$backupname = $filename . "_backup_" . date('Y-m-d_H-i-s');
			
			if (rename($filename,$backupname)) {
				return true;
			} else {
				JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_RENAME"). " $filename");
				return false;
			}
		}
	}

	/**
	 * Restores the last saved backup for this file:
	 * @param unknown_type $key
	 * @return boolean
	 */
	public function restore($key) {
		$filename = $this->getFilename($key);
		
		require_once(JPATH_COMPONENT."/helpers/htaccess.php");
		$htfile =	LittleHelperHelperHtaccess::getLastFile( $filename."_backup_*");
		if (!empty($htfile)) {
			$source = $htfile;
			if (!$this->testFilesPermissions($source,$filename)) {
				return false;
			}
			if (copy($source, $filename)) {
				return true;
			} else {
				JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_RESTORE"). "  $source ".error_get_last());
				return false;
			}
		}
	}
	
	/**
	 * Create the requested .htaccess or index.html file
	 * For .htaccess, getFilename() returns the appropriate source to use
	 * @param unknown_type $key
	 * @param unknown_type $filename
	 * @return boolean
	 */
	public function create($key,$filename='.htaccess') {
		$filename = $this->getFilename($key,$filename);

		$source = dirname(dirname(__FILE__))."/assets/txt/htaccess_folders.txt";
		if (strtolower($key)=='main' || empty($key)) {
			JError::raiseWarning(500,"Internal dev error, create cannot be invoked for the root");
			return;
			$source = dirname(dirname(__FILE__))."/assets/txt/htaccess_boilerplate.txt";
		}
		if (!$this->testFilesPermissions($source,$filename)) {
			return false;
		}
		
		if (copy($source,$filename)) {
			return true;
		} else {
			JError::raiseWarning(100, sprintf(JText::_("COM_LITTLEHELPER_FILE_ERROR_COPY"), $source, $filename ).
				join(",",error_get_last()));
			return false;
		}
	}
	
	/**
	 *  return true or false as appropriate, 
	 *  $source: must exist
	 *  $destination: must be writeable + create folder if it doesn't exist;
	 * @param unknown_type $source
	 * @param unknown_type $destination
	 */ 
	private function testFilesPermissions($source,$destination) {
		require_once(JPATH_COMPONENT."/helpers/htaccess.php");
		//LittleHelperHelperHtaccess::$context = 'HTACCESS';
		return LittleHelperHelperHtaccess::testFilesPermissions($source,$destination);
	}

	private function safeHtaccessCopy($source,$destination,$symlinks) {
		require_once(JPATH_COMPONENT."/helpers/htaccess.php");
		if (!LittleHelperHelperHtaccess::testFilesPermissions($source,$destination))
			return false;
		if (!$symlinks)
			return copy($source,$destination);
		else {
			// comment the line Options +FollowSymLinks
			$htLines = explode("\n",file_get_contents($source));
			foreach($htLines as $key=>$line) {
				/* Match any syntax:
				 * Options +FollowSymLinks
				 *   Options +FollowSymlinks */
				if (preg_match("@^[ \t]*Options[ \t]+\+FollowSymLinks@i", $line)) {
					$htLines[$key] = "#littlehelper: commented the next line hoping the file will work\n#".$line;
				}
			}
			$ht = implode("\n",$htLines);
			if (file_put_contents($destination, $ht)) {
				return true;
			} else {
				JError::raiseWarning(100, "Could not save to $filename");
				return false;
			}
		}
	}
	private function _getRootHTAccessPath($kind) {
		if ($kind!="joomla")
			$source = JPATH_COMPONENT."/assets/txt/htaccess_boilerplate.txt";
		else
			$source = JPATH_COMPONENT."/assets/txt/htaccess_joomla.txt";
		return $source;
	}
	
	public function testRoot($kind,$symlinks) {
		$source = $this->_getRootHTAccessPath($kind);		
		$destination = JPATH_SITE.'/components/com_littlehelper/.htaccess';
		return ($this->safeHtaccessCopy($source, $destination, $symlinks));
	}
	
	/**
	 * Create the Joomla default .htaccess, i.e. rename htaccess.txt in the root.
	 * @param unknown_type $key
	 * @return boolean
	 */
	public function createRoot($kind,$symlinks) {
		$destination = $this->getFilename('main');
		$source = $this->_getRootHTAccessPath($kind);		
		return ($this->safeHtaccessCopy($source, $destination, $symlinks));
	}
	
	/**
	 * Return the appropriate filename location for .htaccess (default) or index.html files
	 * that we want to create / test.
	 * @param unknown_type $key
	 * @param unknown_type $filename
	 * @return string
	 */
	private function getFilename($key, $filename='.htaccess') {
		$paths = array('main','images','tmp','cache','administrator_cache');
		$filepath = "";
		$comparekey = trim(strtolower($key));
		foreach ($paths as $path) {
			if ($comparekey==$path) {
				if ($path=='administrator_cache') {
					$keyDS = str_replace('_','/',$path)."/";
				} else {
					$keyDS = $path=='main'?"":$path."/";
				}
				
				$filepath = JPATH_SITE."/".$keyDS.$filename;
			}
		}
		return $filepath;
	}
}