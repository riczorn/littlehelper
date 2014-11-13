<?php

/**
 * com_littlehelper Trash n Cache model
 *
 * The cache functionalities are on top; recycle bin Trash functions follow.
 * While cache is pretty straightforward, trash requires a little explanation.
 *
 * $trashes hold a list of all actions. Actions are built in the initialization by adding
 * 		buildTrashItem			// to perform some initialization and direct-to-db tasks i.e.
 * 								// removing featured items;
 * 		and
 * 		builtTrashModelItem		// to ask Joomla's model to clean the trash; the db is queried only
 * 								// to determine items count.
 *
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelTrash_n_Cache extends JModelLegacy {
	protected $trashes;
	protected $params;

	protected $extraModels=array();
	/**
	 * @access    public
	 * @return    void
	*/
	function __construct() {
		parent::__construct();
		$this->trashes = array();
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		// please note: the model loads as $params->get('params')->recycle_modules
		// as it takes into consideration the <fields> in config.
		$this->params = $params->get('params');
		
		// the following is now obsolete: versions which are updated from 1.5 or 1.7 may have different state/published column names
		$defaultPublishedColumnName = 'published';
		if (version_compare(JVERSION, '3.0.0', 'ge'))
			$defaultPublishedColumnName = 'state';
		
		//	buildTrashModelItem($kind, $componentName=null, $tableName=null,  $tableObject=null, $publishField='state', $sqlPart=null) {
		// breaks on weblinks: published Field is wrong?
		$this->trashes[] = $this->buildTrashModelItem('modules', 'modules','modules','module','published');
		$this->trashes[] = $this->buildTrashModelItem('menuitems',   'menus',  'menu','menu',   'published');
		$this->trashes[] = $this->buildTrashItem('content','content', '`#__content_frontpage` WHERE content_id in (select id from #__content where `{publishField}`=-2)');
		$this->trashes[] = $this->buildTrashModelItem('content2',null,'content','content','state');

		// now we'll delete the unused featured items.
		if (!empty($this->params->recycle_featured_keep)) {
			$startfromOrdering=(int)($this->params->recycle_featured_keep);
		} else {
			$startfromOrdering = 100;
		}
		if ($startfromOrdering>0)
			$this->trashes[] = $this->buildTrashItem('featured', 'content_frontpage', "`#__content_frontpage` WHERE ordering>$startfromOrdering");

		//BannersTableBanner
		$this->trashes[] = $banners = $this->buildTrashModelItem('banners','banners','banners','banner','state');
		$banners->tableObjectPrefix = "BannersTable";
		
		//CategoriesTableCategory
		$this->trashes[] = $categories = $this->buildTrashModelItem('categories','categories','categories','category','published');
		$categories->tableObjectPrefix = "CategoriesTable";
		
		//ContactTableContact
		$this->trashes[] = $contacts = $this->buildTrashModelItem('contacts','contact','contact_details','contact','published');
		$contacts->tableObjectPrefix = "ContactTable";
		
		//WeblinksTableWeblink
		$this->trashes[] = $weblinks = $this->buildTrashModelItem('weblinks','weblinks','weblinks','weblink',$defaultPublishedColumnName);
		$weblinks->tableObjectPrefix = "WeblinksTable";
		
		//MessagesTableMessage
		$this->trashes[] = $messages = $this->buildTrashModelItem('messages','messages','messages','message',$defaultPublishedColumnName);
		$messages->tableObjectPrefix = "MessagesTable";
		
		// NewsfeedsTableNewsfeed
		$this->trashes[] = $newsfeeds = $this->buildTrashModelItem('newsfeeds','newsfeeds','newsfeeds','newsfeed','published');
		$newsfeeds->tableObjectPrefix = "NewsfeedsTable";
		
		//RedirectTableLink
		$this->trashes[] = $weblinks = $this->buildTrashModelItem('redirect','redirect','redirect_links','Link',$defaultPublishedColumnName);
		$weblinks->tableObjectPrefix = "RedirectTable";
		
		$this->_data=array();  // this will hold the results (items count for display)
	}


	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////   recycle bin functions   ///////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Method to get deleted items count +  cache size
	 * @return hashtable with keys 'content','modules','menuitems'...'cache'.
	 */
	public function getData() {
		// Load the data
		foreach($this->trashes as $trash) {
			// defaults:
			$trash->link = null; // link will be used to build a popup link on the line item.
			$trash->count = 0;
			
			if ($trash->enabled) {
				$trashCount = $trash->getCount();
				
				$trash->count = $this->formatCacheElements($trashCount);
				// for items that have a component bound and have items created, add a link to 
				// view the trashed items.
				if ( $trashCount>0 && !empty($trash->componentName)) {
					$trash->link = "index.php?tmpl=component&option=com_littlehelper&task=trash_n_cache.showTrashedItems&component=$trash->componentName";
					if (!empty($trash->viewParams)) {
						$trash->link .= "&".$trash->viewParams;
					}
				}
				/* now it's appropriate to change the format to create an array with:
				- title (desc)
				- link = option=com_content&filter_state=-2
				- count/kb
				*/
				//$this->_data[$kind]=$this->formatCacheElements($trash->getCount());
				
			} else {
				//$this->_data[$trash->kind]=JText::_("COM_LITTLEHELPER_OPTIONS_DISABLED").$this->formatCacheElements($trash->getCount());
				$trash->count = JText::_("COM_LITTLEHELPER_OPTIONS_DISABLED").$this->formatCacheElements($trash->getCount());
			}
			$this->_data[] = $trash;
		}

		// cache space occupied
		require_once JPATH_COMPONENT.'/helpers/trash_n_cache.php';
		//$this->_data['cache'] = LittleHelperHelperTrash_n_Cache::foldersizebytes(dirname(JPATH_BASE).'/cache');
		$cache = new stdClass();
		$cache->count = LittleHelperHelperTrash_n_Cache::foldersizebytes(dirname(JPATH_BASE).'/cache');
		$cache->kind="cache";
		$cache->link=null;
		$this->_data[] = $cache;		
		

		return $this->_data;
	}
	
	function getItem($componentName) {
		foreach($this->trashes as $trash) {
			if ($trash->componentName==$componentName) {
				$trashContent = $trash->getData();
				return $trashContent;
			}			
		}
		return array("no trash for component");
	}

	/**
	 * Format the cache elements for display
	 * @param unknown_type $elementCount
	 * @return string
	 */
	private function formatCacheElements($elementCount) {
		if (0+$elementCount==0) {
			return JText::_("COM_LITTLEHELPER_NO_ELEMENTS");
		}
		else {
			return JText::plural("COM_LITTLEHELPER_ELEMENTS", $elementCount);
		}
	}

	/**
	 * This is used for a sql-only action, such as deleting some records from the db, 
	 * or deleting linked items in other tables after the model has been used to delete, 
	 * i.e. featured items when deleting content items.
	 * 
	 * @param unknown_type $kind
	 * @param unknown_type $tableName
	 * @param unknown_type $sqlPart
	 * @return Ambigous <boolean, stdClass, LittleHelperModelTrash_n_CacheItem>
	 */
	private function buildTrashItem($kind, $tableName, $sqlPart) {
		return $this->buildTrashModelItem($kind,null,$tableName,null,null,$sqlPart);
	}
	
	/**
	 * Create a Trash_n_CacheItem class and initialize its data
	 * 
	 * @param unknown_type $kind			// this is the key: both for description and permission and grouping
	 * @param unknown_type $componentName	// can be null or component name i.e. "content": in the latter case
	 * 										// Joomla's Table model for the given component is invoked for deletion,
	 * 										// and sqlPart is used only for getting the count of items.
	 * @param unknown_type $tableName
	 * @param unknown_type $publishField
	 * @param unknown_type $sqlPart			// if componentName = null, sqlPart must be filled in
	 * @return boolean|stdClass
	 */
	private function buildTrashModelItem($kind, $componentName=null, $tableName=null,  $tableObject=null, $publishField='state', $sqlPart=null) {
		require_once(dirname(__FILE__).'/trash_n_cacheitem.php');
		$result = new LittleHelperModelTrash_n_CacheItem();

		$result->description=JText::_("COM_LITTLEHELPER_TRASH_KIND_".strtoupper($kind));
		if (!empty($this->params->{"recycle_$kind"}))
			$result->enabled =$this->params->{"recycle_$kind"};
		else
			$result->enabled = true;

		$result->componentName = $componentName;
		$result->kind = $kind;
		$result->tableName = $tableName = $tableName?$tableName:$componentName;
		$result->tableObject = $tableObject?$tableObject:$tableName;

		$db = JFactory::getDbo();
		
		// query table and fix $publishField if necessary!
		$columnNames = $db->getTableColumns("#__$tableName");
		if (!empty($publishField) && array_key_exists($publishField,$columnNames))
			$result->publishField = $publishField;
		else if (array_key_exists('state',$columnNames))
			$result->publishField = 'state';
		else if (array_key_exists('published',$columnNames))
			$result->publishField = 'published';
		else {
			$result->publishField = $publishField;
			if ($publishField != null) {
				// content_frontpage and other non-standard-joomla-cms-model queries are not required
				// to have a $publishField
				JError::raiseWarning(514,"Cannot determine the name of the published/state field for #__$tableName");
			}
		}
		
		if ($sqlPart)
			$result->sqlPart = str_replace("{publishField}", $result->publishField, $sqlPart);
		else
			$result->sqlPart = "`#__$tableName` WHERE `$result->publishField`=-2";
		return $result;
	}

	/**
	 * Empty recycle bin (iterate through trashes).
	 */
	public function emptyRecycleBin() {
		$backup = array();
		foreach($this->trashes as $trash) {
			if ($trash->enabled) {
				$trash->emptyTrash();
				$backup[] = $trash->backup;
			}
		}

		$this->saveBackup($backup);
		JError::raiseNotice(200,JText::_("COM_LITTLEHELPER_TRASH_DONE"));
	}
	
	/**
	 * Clean the administrator's cache.
	 * This function is invoked by the custom field in the plugin to
	 * ensure that the admin cache will be cleaned when a user chooses the
	 * custom logos for the admin.
	 */
	public function clearAdministratorCache() {
		//$adminCacheFolder = JPATH_ADMINISTRATOR . '/cache/*';
		$adminCacheFolder = '/home/fasterjoomla/public_html/administrator/cache';
		$cachesToClean = scandir($adminCacheFolder);
		foreach($cachesToClean as $key=>$singleCacheItem) {
			if (count_chars($singleCacheItem)==0
					|| $singleCacheItem[0]=='.'
					|| $singleCacheItem[0]=='*'
					|| $singleCacheItem[0]=='?'
					|| $singleCacheItem=='index.html')
						unset($cachesToClean[$key]);
		}
		$res = '';
		$total = 0;
		foreach ( $cachesToClean as $i=>$cacheFolder ) {
			$cache_dir = JPATH_ADMINISTRATOR.'/cache/'.$cacheFolder;
		 	list($res,$errormessage) = $this->removeFolder($cache_dir);
		 	$total += $res;
		}
		
		return ($total == count($cachesToClean));
	}

	/**
	 * Export a dump of the records and assets which will be deleted in .sql format (UTF-8).
	 * The backup goes to /tmp/recycle_bin_backup
	 * 
	 * Note: the former approach to use SELECT INTO OUTFILE has been abandoned because it 
	 * 		doesn't work on some shared hosts - it requires some extra permissions.
	 * 
	 * @param unknown_type $backup
	 * @return number|boolean
	 */
	private function saveBackup($backup) {
		$config = JFactory::getConfig();
		// http://www.hikashop.com/en/forum/3-bug-report/76183-error-call-to-undefined-method-jregistry.html
		if(version_compare(JVERSION,'1.6.0') < 0){
			$tmp_path = $config->getValue('tmp_path');
		} else {
			$tmp_path = $config->get('tmp_path');
		}
		
		
		$tmp_path = $tmp_path."/recycle_bin_backup/";
		$this->writeBackupReadme($tmp_path);
		
		$backupFile = $tmp_path."backup_littlehelper_trash_".date('Y-m-d---H-i-s').".sql";
		error_log("LittleHelper Trash Backup to file $backupFile");
		require_once(JPATH_COMPONENT."/helpers/htaccess.php");
		if (! LittleHelperHelperHtaccess::testFilesPermissions(null,$backupFile)) {
			JError::raiseNotice(500,'cannot open file '.$backupFile);
		}
		$backupString = "";
		foreach($backup as $backupLines) {
			if (!empty($backupLines)) {
				$backupString .= $backupLines . "\n\n";
			}
		}
		if (!empty($backupString)) {
			$backupString = "-- LittleHelper Database Backup
-- Generated on ".date("Y/m/d H:i.s")."
		
SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
SET time_zone = \"+00:00\";
/*!40101 SET NAMES utf8 */;
		
		\n\n".$backupString;
			return file_put_contents($backupFile, $backupString);
		} else 
			return true;

	}
	
	/**
	 * Writes the readme to the backup folder, also changing some values (db name and db user, but
	 * for obvious security reasons _NOT_ db password)
	 * @param unknown_type $tmp_path
	 * @return number|boolean
	 */
	private function writeBackupReadme($tmp_path) {
		$readmeTarget = $tmp_path."readme.txt";
		
		if (!file_exists($readmeTarget)) {
			$readmeSource = JPATH_COMPONENT."/assets/txt/readme_recycle_backup.txt";
			$readme = file_get_contents($readmeSource);
			$search = array('{DBNAME}','{DBUSER}');
			$config = JFactory::getConfig();
			
			if(version_compare(JVERSION,'1.6.0') < 0){
				$replace = array(
					$config->getValue('db'),
					$config->getValue('user')
				);
			} else {
				$replace = array(
					$config->get('db'),
					$config->get('user')
				);
			}

			$readme = str_replace($search,$replace,$readme);
			require_once(JPATH_COMPONENT."/helpers/htaccess.php");
			if (! LittleHelperHelperHtaccess::testFilesPermissions(null,$readmeTarget)) {
				JError::raiseNotice(500,'cannot open file '.$backupFile);
			}
			
			return file_put_contents($readmeTarget, $readme);
		}
		return true;
	}

	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////   cache functions   ///////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Cleans the cache.  First lists all cache contents or loads them from the options
	 * (see option "clean all cache" or "clean selected extensions' caches")
	 * Then parses the list, and uses the appropriate method for removal
	 * @param unknown_type $useJoomla
	 */
	public function performCleanCache($useJoomla=true)  {
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $params->get('params');
		$cachesToClean = array();
		if (!file_exists(JPATH_SITE."/cache"))
			mkdir(JPATH_SITE."/cache",0755);

		if (empty($params->clean_all_cache) || $params->clean_all_cache) {
			$cachesToClean = scandir(JPATH_SITE."/cache");
		} else {
			$moreCaches = explode("\n",$params->extra_extensions);
			foreach ($moreCaches as $oneCache) {
				$oneCache = trim($oneCache);
				if (!empty($oneCache)) {
					$cachesToClean[] = $oneCache;
				}
			}
		}
		// now let's remove invalid items
		echo "<h3>".JText::_("COM_LITTLEHELPER_CLEANING")."</h3>";
		foreach($cachesToClean as $key=>$singleCacheItem) {
			if (count_chars($singleCacheItem)==0
					|| $singleCacheItem[0]=='.'
					|| $singleCacheItem[0]=='*'
					|| $singleCacheItem[0]=='?'
					|| $singleCacheItem=='index.html')
				unset($cachesToClean[$key]);
		}
		$res = 0;
		if (count($cachesToClean)>0) {
			echo "<div class='result'>";
			foreach ($cachesToClean as $i=>$item) {
				$res += $this->cleanCacheFolder($item,$useJoomla);
			}
			echo "</div>";
			if ($res == count($cachesToClean)) {
				echo "<div class='result global'>".JText::_("COM_LITTLEHELPER_GLOBALRESULT")." ".JText::_("COM_LITTLEHELPER_OK")."</div>";
					
			} else {
				echo "<div class='result global error'>".JText::_("COM_LITTLEHELPER_GLOBALRESULT")." ".JText::_("COM_LITTLEHELPER_ERROR")."</div>";
			}
		} else {
			if (!empty($params->clean_all_cache) && !($params->clean_all_cache))
				echo "<div class='result global error'>".JText::_("COM_LITTLEHELPER_ERROR")." ".JText::_("COM_LITTLEHELPER_TIP_SELECT_OPTIONS")."</div>";
		}
	}
	/**
	 * Request removal of the $folder (which could also be a file!)
	 * @param unknown_type $folder
	 * res = 1: success
	 * res = 0: failure
	 * @return array(result, errormessage)
	 */
	private function removeFolder($folder) {
		$errormessage = "";
		$output = "";
		$res = 1;
		if (file_exists($folder)) {
			list($res,$output)=$this->shellrmdir($folder);
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
	private function shellrmdir($folder)
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
		 // instead, since we're not running risks, and this is also
		 // handling wrong file permissions, and if any of the above worked
		 // there won't be anything to clear, so we're not adding overhead,
		 // we use this unconditionally.  Of course, we're losing $res but
		 // do we really care?
		 require_once JPATH_ADMINISTRATOR.'/components/com_littlehelper/helpers/trash_n_cache.php';
		 $res = LittleHelperHelperTrash_n_Cache::removeFolderPHP($folder);
		 return array($res,$buff);
	}

	/**
	 * Generic function to clean a single folder of cache,
	 * either invoking Joomla method JCache::clean or
	 * rm -rf on the filesystem.
	 * Returns 1 if success, 0 if failure.

	 * @param unknown_type $cacheFolder
	 * @param unknown_type $useJoomla
	 * @return number
	 */
	private function cleanCacheFolder($cacheFolder, $useJoomla=true) {
		if (empty($cacheFolder) || (strpos($cacheFolder, '/')!==false) || (strpos($cacheFolder, '*')!==false) || (strpos($cacheFolder, "\\")!==false) || (strpos($cacheFolder, '..')!==false)) {
			$errormessage = sprintf(JText::_("COM_LITTLEHELPER_ERROR_WRONGCHARS"),$cacheFolder);
			echo "<div class='result error'>$errormessage</div>";
			return 0;
		}

		$output = null;
		if ($useJoomla) {
		 	$cache = JFactory::getCache($cacheFolder,'');
		 	$res = $cache->clean()?1:0;
		} else {
		 	$cache_dir = dirname(JPATH_BASE).'/cache/'.$cacheFolder;
		 	list($res,$errormessage) = $this->removeFolder($cache_dir);
		 	// values are: 0 = ok; else !ok
		}
		
		if ($res != 0) {
	 		echo "<div class='result'><span class='folder'>$cacheFolder</span><span class='value'>".JText::_("COM_LITTLEHELPER_OK")."</span></div>";
		}
		else {
		 	echo "<div class='result error'><span class='folder'>$cacheFolder ".JText::_("COM_LITTLEHELPER_ERROR")."</span><span class='value'>$res</span>";
		 	if ($output != null) {
		 		echo "<br>".JText::_("COM_LITTLEHELPER_SYSTEMOUTPUT")."<pre>".join("<br>",$output)."</pre>";
		 	}
		 	echo "</div>";
		}
		return $res==0?0:1;
	}
}

