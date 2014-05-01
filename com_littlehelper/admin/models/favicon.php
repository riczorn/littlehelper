<?php
/**
 * com_littlehelper favicon model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelFavicon extends JModelLegacy {
	protected $componentName = 'com_littlehelper';
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * Set default folder to /images/icons, create the folder and 
	 * return its path 
	 */
	function createDefault() {
		$mparams = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $mparams->get('params');
		if (!empty($params->favicons_sourcepath))
			$fi = $params->favicons_sourcepath;
		else
			$fi = null;
		if (empty($fi) || $fi=="-1") {
			$newFolderName = JPATH_SITE.'/images/icons';
			$mparams->set('params.favicons_sourcepath','icons');
			$this->saveParams($mparams, $this->componentName);
			require_once JPATH_COMPONENT.'/helpers/littlehelper.php';
			
			LittleHelperHelper::init(); // will create the folder if necessary
			return sprintf(JText::_("COM_LITTLEHELPER_FAVICON_FOLDER_CREATED"),"images/icons");
		}
		else return false;
	}
	
	/**
	 * Copy the resized images to the final apple precomposed items
	 * with proper naming;
	 * Create the favicon;
	 * Copy it to template and administrator;
	 * 
	 * @return string|boolean
	 */
	function publish() {
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		$returnMessage = "";
		$images = LittleHelperHelperFavicon::getImages();
		$destFolder = LittleHelperHelperFavicon::$templatePath;
		if (!file_exists(JPATH_SITE.$destFolder))
			mkdir(JPATH_SITE.$destFolder,0755);
		$copied = 0;
		foreach(array(144,114,72,57) as $size) {
			$image = $images[$size];
			if ($size==57) $sizeName=""; else
				$sizeName = $size."x".$size."-";
			$destFileName = sprintf("%s%sapple-touch-icon-%sprecomposed.png",
					JPATH_SITE,$destFolder,$sizeName);

			if (copy(JPATH_SITE.$image->path . $image->name, $destFileName)) {
				$copied++;
			} else {
				JError::raiseWarning(517,JText::_("COM_LITTLEHELPER_FILE_ERROR_CREATEFILE").": ".$destFileName.var_export(error_get_last(),true));
			}
		}
		if ($copied==4) $returnMessage .= JText::_("COM_LITTLEHELPER_FAVICON_APPLE_COPIED")." $destFolder;";
		
		$result = LittleHelperHelperFavicon::createFavicon();
		$returnMessage .= " " . $result;
		return "$returnMessage";
	}
	
	/**
	 * Remove all component-generated files.
	 */
	public function clearResized() {
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		LittleHelperHelperFavicon::initPaths();
		require_once JPATH_COMPONENT.'/helpers/trash_n_cache.php';
		LittleHelperHelperTrash_n_Cache::removeFolderPHP(JPATH_SITE.LittleHelperHelperFavicon::$thumbsPath);
	}
	
	/**
	 * If the images path is not set, an option is available in the backend view; this is 
	 * a helper function to support saving the params.
	 * @param unknown_type $params
	 */
	private function saveParams($params, $extensionName, $type='component') {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__extensions AS a');
		$query->set('a.params = ' . $db->quote((string)$params));
		$query->where(sprintf('a.element = %s AND a.%s = %s',
				$db->quote($extensionName),
				$db->quoteName('type'),
				$db->quote($type)
				));
		$db->setQuery($query);
		return $db->query();
	}
	
	/**
	 * Save the plugin configuration 
	 * @return mixed
	 */
	public function saveConfiguration() {
		require_once(JPATH_ADMINISTRATOR."/components/com_littlehelper/helpers/favicon.php");
		LittleHelperHelperFavicon::initPaths();
		$head = LittleHelperHelperFavicon::getHead(true);
		$params = new stdClass();
		$params->markup = $head;
		
		$jsonparams = json_encode($params);
		if ($this->saveParams($jsonparams, 'littlehelper', 'plugin'))
			return JText::_("COM_LITTLEHELPER_FAVICONS_SAVECONFIG_SUCCESS");
		else
			return false; 
	}
	
	/**
	 * Enable or disable the littlehelper plugin
	 * @param unknown_type $enabled
	 */
	public function setPluginState($enabled = false) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__extensions AS a');
		$query->set('a.enabled = ' . $db->quote($enabled?'1':'0'));
		$query->where(sprintf('a.element = %s AND a.%s = %s',
				$db->quote('littlehelper'),
				$db->quoteName('type'),
				$db->quote('plugin')));
		$db->setQuery($query);
		if ($db->query())
			if ($enabled)
				return JText::_("COM_LITTLEHELPER_FAVICONS_ENABLEPLUGIN_SUCCESS");
			else
				return JText::_("COM_LITTLEHELPER_FAVICONS_DISABLEPLUGIN_SUCCESS");
		else
			return false;
	} 
	
	/**
	 * Retrieves the images in /images/icons/ and fills an array with their 
	 * imageInfo structures
	 * sizes, 
	 * names and possible size matches for our custom icons 
	 * @return multitype:stdClass
	 */
	public function getImages() {
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		return LittleHelperHelperFavicon::getImages();
	}
	
}