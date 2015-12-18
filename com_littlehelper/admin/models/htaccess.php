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
	
	/**
	 * Fix the output of a system command i.e. grep to show on an html page
	 * @param unknown $buff
	 */
	private function printBufferHtml($res, $buff) {
		
		if ($res) {
			echo "Result: $res <span class='ok'>No matching files found, which is good.</span>";
		} else {
			echo "<span class='warn'>".count($buff). " matching files found.</span>";
			return "<pre>" . join("<br>",explode("\n",str_replace('<','&lt;',$buff))) ."</pre>";
		}
	}
	
	public function findExploits() {
		echo "<p>Please note, this is an <b>experimental</b> function: no harm can come to you, but the results may be incomplete</p>";
		echo "<h1>" . JText::_("COM_LITTLEHELPER_EXPLOIT_TITLE_SEARCH") . "</h1>";
		echo "<style>.warn {color:red}</style>";
		echo "<h3>" . JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_FILES") ."</h3>";
		$array = ['libraries/joomla/exporter.php','libraries/simplepie/simplepie.lib.php'];
		$list = array();
		foreach ($array as $file) {
			if (file_exists(JPATH_SITE.'libraries/joomla/exporter.php')) {
				$list[] = sprintf( "<li>%s %s</li>",$file , JText::_("COM_LITTLEHELPER_EXPLOIT_FILE_EXISTS"));
			}			
		}
		echo (count($list)) ? ("<ul>".join("\n",$list)."</ul>") : JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_NOTFOUND");

		
		
		echo "<h3>" . JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_GARBLED_CALLS") ."</h3>";
		$calls = ['\\043\\056\\052\\043\\145', '\\145\\166\\141\\154', '\\142\\141\\163\\145\\066\\064\\137\\144\\145\\143\\157\\144\\145'];
		$cd = 'cd '.escapeshellarg(JPATH_SITE).';';
		foreach($calls as $call) {
			$command = $cd.' grep -r \'' . $call  . '\' .';
			echo "<hr>Command: $command<br>";
			$buff = false;
			list($res,$buff) = $this->shellExec($command);
			echo $this->printBufferHtml($res, $buff);
		}
		
		
		
		echo "<h3>" . JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_UNSAFE_CALLS") ."</h3>";
		$calls = ['eval', 'assert', 'base64_decode'];
		foreach($calls as $call) {
			$command = $cd.' grep -r -e \'[\n\W]' . $call . '\s*[\(]\' .';
			$buff = false;
			list($res,$buff) = $this->shellExec($command);
			echo "<hr>Command: $command<br>";
			echo $this->printBufferHtml($res, $buff);
		}
		echo "<h3>" . JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_UNSAFE_VARS") ."</h3>";
		$vars = ['_COOKIE', '_POST', '_GET', '_SESSION'];
		foreach($vars as $var) {
			$command = $cd.' grep -r -e "\$[\{ ]*' . $var . '[\} ]*" .';
			$buff = false;
			list($res,$buff) = $this->shellExec($command);
			echo "<hr>Command: $command<br>";
			echo $this->printBufferHtml($res, $buff);
		}
		echo ('<h2>'.JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_END").'</h2>');
		echo JText::_("COM_LITTLEHELPER_EXPLOIT_SEARCH_RESULTS");
	}
	
	private function shellExec($cmd) {
	 	$cmd .= " 2>&1";
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
	 	}
	 	
	 	return array($res,$buff);
	}
	
}