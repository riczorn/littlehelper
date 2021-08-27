<?php

/**
 * com_littlehelper Trash n Cache.item model
 * This contains the logic to display and clean a trash item. 
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelTrash_n_CacheItem extends JObject {

	public $kind;			// this is the key: both for description and permission and grouping
	/**
	 * can be null or component name i.e. "content": 
	 * - if it's null, the sqlPart will be used for delete actions;
	 * - in the latter case
	 *   Joomla's Table model for the given component is invoked for deletion,
	 *   and sqlPart is used only for getting the count of items.
	 * @var unknown_type
	 */
	public $componentName;
	public $tableName;		// this is the part after #__ which will be added automatically
	public $tableObject;	// this is the suffix of the JTable class i.e. "content" for JTableContent
	public $tableObjectPrefix='JTable'; // this is optional, not in the constructor.
	public $publishField;
	public $valuePublishUp;
	public $valueTrash;
	//public $valueDirection;
	public $viewParams;
	public $sqlPart;			// if componentName = null, sqlPart must be filled in
	// the following are calculated fields:
	public $description; // will be filled in automatically with the appropriate translation
	public $enabled; // will be filled in at trash_n_cache initialization
	public $backup; // here the backup sql will be inserted by backupItems()

	/**
	 * Return an array with the ids of the items in the trash or satisfying the condition sqlPart
	 * @return Ambigous <mixed, NULL, multitype:mixed >|multitype:string
	 */
	public function getItems() {
		$db = JFactory::getDbo();
		$query = 'SELECT id FROM '.$this->sqlPart . " LIMIT 0,5000";
		$db->setQuery($query);
		
		if (is_array($res = $db->loadColumn(0))) {
			return $res;
		} else {
			JError::raiseWarning(500,"Could not find trash items: ".$db->stderr(false));
			return array("Error");
		}
	}
	
	public function getData() {
		$db = JFactory::getDbo();
		$query = 'SELECT * FROM '.$this->sqlPart;
		$db->setQuery($query);
	
		if (is_array($list = $db->loadAssocList())) {
			// it would be nice to make sure a "name" key exists, and assign it to title / alias / whatever
			foreach($list as $mainkey=>$res) {
				if (empty($res['name'])) {
					foreach(array('title','alias') as $fieldname) {
						if (isset($res[$fieldname])) {
							$res['name'] = $res[$fieldname];
							break;
						}
					}
				}
				if (empty($res['name'])) {
					foreach($res as $key=>$value) {
						if (strpos($key,'name')>0)
						{
							$res['name']=$res[$key];
						}
					}
				}
				if (empty($res['description'])) {
					foreach(array('introtext','fulltext','descr','note') as $fieldname) {
						if (isset($res[$fieldname])) {
							$res['description'] = $res[$fieldname];
							break;
						}
					}
				}
				$res['name'] = strip_tags($res['name']);
				$res['description'] = strip_tags($res['description']);
				$list[$mainkey] = $res;
			}
			// now ideally both name and description are set. Let's strip html tags:
			return $list;
		} else {
			JError::raiseWarning(500,"Could not find trash items: ".$db->stderr(false));
			return array("Error");
		}
	}

	/**
	 * Return the number of items in the trash or satisfying the condition sqlPart
	 * @return Ambigous <mixed, NULL>|string
	 */
	function getCount() {
		$db = JFactory::getDbo();
		$query = 'SELECT count(*) FROM '.$this->sqlPart;
		$db->setQuery($query);
		if (($res = $db->loadResult())!==0) {
			return $res;
		} else {
			JError::raiseWarning(500,"Could not gather trash items count: ".$db->stderr(false));
			return "Error";
		}
	}
	
	/**
	 * if componentName is assigned, let's delete with the Joomla Table model;
	 * else run the db function DELETE
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	function emptyTrash() {
		if ($this->getCount()) {
			if (!empty($this->componentName )) {
				return $this->emptyTrashModel();
			} else {
				return $this->emptyTrashDb();
			}
		}
		
		return true;
	}	
	
	/**
	 * Perform the cleaning of a components' trash using Joomla JTable module.
	 * @return boolean
	 */
	function emptyTrashModel() {
		$compParams = JComponentHelper::getParams("com_$this->componentName");
		
		if (file_exists($tPath = JPATH_ADMINISTRATOR  . "/components/com_$this->componentName/tables")) {
			$filename = strtolower($this->tableObject);
			if (file_exists($tPath."/".$filename.".php")) {
				JLoader::import( $filename,  $tPath);
			}
		}
		$table = JTable::getInstance($this->tableObject,$this->tableObjectPrefix);
		if ($table && ($idsToDelete = $this->getItems())) {
			$this->backupItems();
			$errMsgId = array();
			foreach($idsToDelete as $id) {
				if (!$table->delete($id)) $errMsgId[] = "$id";
			}
			$errMsg = array();
			if (count($errMsgId)) {
				// Most likely we are trying to empty the trash of an element which should have assets but doesn't.
				// so we shall proceed with $this->emptyTrashDb();
				$this->emptyTrashDb();
				error_log("COM_LITTLEHELPER_TRASH_ERROR_ASSETS_FAILOVER_DB");
				error_log(var_export($errMsgId, true));
				$errMsg[] = sprintf(JText::_("COM_LITTLEHELPER_TRASH_ERROR_ASSETS_FAILOVER_DB"),
						$this->kind, 'Details saved in Server logs');
				// $errMsg[] = sprintf(JText::_("COM_LITTLEHELPER_TRASH_ERROR_ASSETS_FAILOVER_DB"),
				//	$this->kind, implode(",",$errMsgId));
			}
			if (is_callable(array($table, 'getErrors') )) {
				$errors = $table->getErrors();
				if (count($errors)) {
					foreach($errors as $error)
						if ($error) 
							$errMsg[] = $error;
				}
				if (count($errMsg)) {
					JError::raiseWarning(515,implode("\n",$errMsg));
					return false;
				}
			}
		}

		return true;
	}	
	
	/**
	 * Performs trash cleaning for items which don't have an appropriate JTable descendant
	 * @return mixed|boolean
	 */
	function emptyTrashDB() {
		$this->backupItems();
		
		$db = JFactory::getDbo();
		$query = 'DELETE FROM '.$this->sqlPart;
		$db->setQuery($query);
		if ($res = $db->query()) {
			return $res;
		} else {
			JError::raiseNotice(500,"Could not clear trash item: ".$this->kind." ".$db->getErrorMsg());
		}
		return false;
	}
	
	/**
	 * invoked before recycle bin is emptied, will simply store a text property in $this->backup
	 * which will later be retrieved by the calling routine 
	 * 		LittleHelperModelTrash_n_Cache->emptyRecycleBin() 
	 * once all objects are cleaned up, and saved to disk.
	 */
	function backupItems() {
		$db = JFactory::getDbo();
		$query = 'SELECT * FROM '.$this->sqlPart;
		$db->setQuery($query);
		$itemsToBackup = $db->loadAssocList();

		$sql = "";
		if (!empty($itemsToBackup)) {
			$num_fields = count($itemsToBackup[0]);
			$config = JFactory::getConfig();
			// http://www.hikashop.com/en/forum/3-bug-report/76183-error-call-to-undefined-method-jregistry.html
			if(version_compare(JVERSION,'1.6.0') < 0){
				$dbprefix = $config->getValue('dbprefix');
			} else {
				$dbprefix = $config->get('dbprefix');
			}
			//$dbprefix = JFactory::getConfig()->getValue('dbprefix');
				
			$table = $this->tableName;
			// $sql .= '-- DROP TABLE '.$db->quoteName($dbprefix.$table).';';
			// $sql .= "\n\n".$db->getTableCreate($dbprefix.$table).";\n\n";
			
			$sql = "-- ".strtoupper($table)." Trashed items\n\n";
			
			// get fields List:
			$fieldNames = array();
			$hasAssets = false;
			foreach($itemsToBackup[0] as $field=>$value) {
				$fieldNames[] = $db->quoteName($field);
				if ($field=='asset_id') {
					$hasAssets = true;
				}
			}
			$fieldList = implode(", ", $fieldNames);
			$assets = array();
			
			// now export the rows.
			foreach($itemsToBackup as $item)
			{
				if ($hasAssets) {
					$assets[$item['id']]=$item['asset_id'];
				}
				$sql.= 'INSERT IGNORE INTO '.$db->quoteName($dbprefix.$table);
				$sql .= " ($fieldList) ";
				
				$sql.=' VALUES (';
				$values = array();
				foreach($item as $field=>$value) {
					$values[] = $db->quote($value);
				}
				$sql .= implode(",",$values);
				$sql.= ");\n";
			}
			
			$sql.="\n\n";
			$sql .= $this->backupAssets($assets,$dbprefix);
		}
		
		$this->backup = $sql;
		return true;
	}
	
	/**
	 * The $assets is an associative array[itemid]=>cacheid
	 * This will return the INSERT sql for the selected assets, which will - most likely - be deleted 
	 * along with the objects they are related to.
	 * Beware: restoring without the assets will make the items visible in Joomla, but Joomla won't be able 
	 * to delete them again since the assets are missing; so neither Jooma nor LittleHelper will be able to 
	 * delete them and you'd be left with the only option to manually edit the database.
	 * 
	 * @param unknown_type $assets
	 * @param unknown_type $dbprefix
	 * @return string
	 */
	function backupAssets($assets,$dbprefix) {
		$db = JFactory::getDbo();
		$sql = "";
		foreach($assets as $key=>$asset) {
			$query = sprintf("select * from #__assets where id='%s'",$asset);
			$db->setQuery($query);
			$assetData = $db->loadAssoc();
			if (!empty($assetData)) {
				$fieldNames = array();
				foreach($assetData as $field=>$value) {
					$fieldNames[] = $db->quoteName($field);
				}
				$fieldList = implode(", ", $fieldNames);
				
				$sql.= 'INSERT IGNORE INTO '.$db->quoteName($dbprefix.'assets');
				$sql .= " ($fieldList) ";
			
				$sql.=' VALUES(';
				$values = array();
				foreach($assetData as $field=>$value) {
					$values[] = $db->quote($value);
				}
				$sql .= implode(",",$values);
				$sql.= ");\n";
			}
		}
		return $sql;
	}
}
