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
		$this->trashes[] = $this->buildTrashItem('content','content', 
				'`#__content_frontpage` WHERE content_id in (select id from #__content where `{publishField}`={valueTrash})');
		$this->trashes[] = $content = $this->buildTrashModelItem('content2',null,'content','content','state');
		//$content->tableObjectPrefix = "ContentTable";
		
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
		if ($weblinks = $this->buildTrashModelItem('weblinks','weblinks','weblinks','weblink',$defaultPublishedColumnName)) {
			$weblinks->tableObjectPrefix = "WeblinksTable";
			$this->trashes[] = $weblinks;
		}
		
		//MessagesTableMessage
		if ($messages = $this->buildTrashModelItem('messages','messages','messages','message',$defaultPublishedColumnName)) {
			  $messages->tableObjectPrefix = "MessagesTable";
			  $this->trashes[] = $messages;
		}
		
		// NewsfeedsTableNewsfeed
		if ($newsfeeds = $this->buildTrashModelItem('newsfeeds','newsfeeds','newsfeeds','newsfeed','published')) {
			$newsfeeds->tableObjectPrefix = "NewsfeedsTable";
			$this->trashes[] = $newsfeeds;
		}
		
		//RedirectTableLink
		if ($weblinks = $this->buildTrashModelItem('redirect','redirect','redirect_links','Link',$defaultPublishedColumnName)) {
			$weblinks->tableObjectPrefix = "RedirectTable";
			$this->trashes[] = $weblinks;
		}

		//Tags
		if ($tags = $this->buildTrashModelItem('tags','tags','tags','Tag',$defaultPublishedColumnName)) {
			$tags->tableObjectPrefix = "TagsTable";
			$this->trashes[] = $tags;
		}
		
		// K2
		if ($k2_items = $this->buildTrashModelItem('k2', null, 'k2_items', null, "trash", "`#__k2_items` WHERE trash>0")) {
			$k2_items->valuePublishUp = 0;
			$k2_items->valuePublishDown = 1;
			//$k2_items->valueDirection = array('>','<');
			$this->trashes[] = $k2_items;
		}
		
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
	 * 										// ATTENTION! if you haven't ensured the right classes exist for the 
	 * 										// component and table, simply pass null and the sql will be executed
	 * 										// instead.
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
		$result->valuePublishUp = 1; // 1 and up is published;
		$result->valueTrash = -2;
		//$result->valueDirection = array('<','>');
		

		$db = JFactory::getDbo();
		try {
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
		} catch (Exception $e) {
			return false; // component not installed;
		} 
		if ($sqlPart) {
			$sqlPart = str_replace("{publishField}", $result->publishField, $sqlPart);
			$sqlPart = str_replace("{valuePublishUp}", $result->valuePublishUp, $sqlPart);
			$sqlPart = str_replace("{valueTrash}", $result->valueTrash, $sqlPart);
// 			$sqlPart = str_replace("{valueDirectionTrash}", $result->valueDirection[0], $sqlPart);
// 			$sqlPart = str_replace("{valueDirectionPublished}", $result->valueDirection[1], $sqlPart);
			$result->sqlPart = $sqlPart;
		}
		else
			$result->sqlPart = "`#__$tableName` WHERE `$result->publishField`='$result->valueTrash'";
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
		$adminCacheFolder = JPATH_ADMINISTRATOR . '/cache';
		// i.e. $adminCacheFolder = '/home/fasterjoomla/public_html/administrator/cache';
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
			require_once JPATH_ADMINISTRATOR.'/components/com_littlehelper/helpers/trash_n_cache.php';
			list($res,$errormessage) = LittleHelperHelperTrash_n_Cache::removeFolder($cache_dir);
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
		 	require_once JPATH_ADMINISTRATOR.'/components/com_littlehelper/helpers/trash_n_cache.php';
		 	list($res,$errormessage) = LittleHelperHelperTrash_n_Cache::removeFolder($cache_dir);
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

