<?php

/**
 * com_littlehelper Robotstxt model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelRobots extends JModelLegacy {
	
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Method to load a default robots.txt or read the current one.
	 * @return hashtable with keys 'content','modules','menuitems'.
	 */
	public function load() {
		$filename = JPATH_SITE."/robots.txt";
		$robotstxt = "";
		if (file_exists($filename)) {
			$robotstxt = file_get_contents($filename);
		} else {
			$filename = JPATH_SITE."/robots.txt.dist";
			JError::raiseWarning(1013,JText::_("COM_LITTLEHELPER_ROBOTS_NOROBOTS")); 
			if (file_exists($filename)) {
				$robotstxt = file_get_contents($filename);
			} else {
				JError::raiseWarning(100, "Default robots.txt not found, installation corrupt?");
			}
		}
		return array($robotstxt,$filename);
	}
	
	/**
	 * Save the robots.txt to disk.
	 * @return true for success
	 */
	public function save($robots) {
		$filename = JPATH_SITE."/robots.txt";
		if (file_put_contents($filename, $robots)) {
			return true;
		} else {
			JError::raiseWarning(100, "Could not save to $filename");
			return false;
		}
	}
	
	/**
	 * Fix the robots.txt. (adds a comment on the images line.)
	 * @return true for success
	 */
	public function fix($robots) {
		$robotsLines = explode("\n",$robots);
		foreach($robotsLines as $key=>$line) {
			if (preg_match("@^Disallow:[ \t]*/images/[ \t]*@", $line)) {
				$robotsLines[$key] = "#littlehelper: commented the next line so google news can show article images\n#".$line;
			}
			if (preg_match("@^Disallow:[ \t]*/templates/[ \t]*@", $line)) {
				$robotsLines[$key] = "#littlehelper: commented the next line so google can determine the site's responsiveness\n#".$line;
			}
			if (preg_match("@^Disallow:[ \t]*/media/[ \t]*@", $line)) {
				$robotsLines[$key] = "#littlehelper: commented the next line so google can determine the site's responsiveness\n#".$line;
			}			
		}
		$robots = implode("\n",$robotsLines);
		$filename = JPATH_SITE."/robots.txt";
		if (file_put_contents($filename, $robots)) {
			return true;
		} else {
			JError::raiseWarning(100, "Could not save to $filename");
			return false;
		}
	}
	
	/**
	 * Will return the sitemap url of any known sitemap extensions:
	 * or false if no extensions were found
	 * 
	 * Supported extensions: 
	 * 
	 * - xmap
	 * - jsitemap (currently waiting for actual code from developer)
	 */
	public function getSitemapUrl() {
		
		if (file_exists(JPATH_SITE.'/components/com_xmap/index.html')) {
			// attempt to find xmap url:
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')->from('#__xmap_sitemap')->where('is_default>0')->where('state>0');
			try {
				$xmapId = $db->setQuery($query)->loadResult();
			} catch (Exception $e) {
				// xmap not installed?	
			}
	
			if ($xmapId) {
				return "index.php?option=com_xmap&view=xml&tmpl=component&id=".$xmapId;
			}	
		}
		// attempt to find the jsitemap url: 		index.php?option=com_jmap&view=sitemap&format=xml
		if (file_exists(JPATH_SITE.'/components/com_jmap/index.html')) {
			return "index.php?option=com_jmap&view=sitemap&format=xml";
		}
		return false;
	}
	
	public function fixsitemap($robots) {
		$robotsLines = explode("\n",$robots);
		// make sure the Sitemap: entry contains the site url:
		$sitemapFound = false;
		foreach($robotsLines as $key=>$line) {
			if (preg_match("@^Sitemap[ \t]*:[ \t]*@i", $line)) {
				$sitemapFound = true;
				$sitemapUrl = preg_replace("@^Sitemap[ \t]*:[ \t]*(.*)[ \t]*$@i","$1",$line);
				if (strpos($sitemapUrl,'//')===false) {
					$sitemapUrl = JUri::root(false). ltrim($sitemapUrl,'/');
					$robotsLines[$key] = "#littlehelper: commented the next line as it didn't contain the hostname\n".
						"#".$line."\n".
						"Sitemap: ".$sitemapUrl;
				}
			}
		}
		if (!$sitemapFound) {
			$sitemapUrl = $this->getSitemapUrl();
			$sitemapUrl = JUri::root(false) . ltrim($sitemapUrl,'/');
			$robotsLines[] = "Sitemap: ". $sitemapUrl;
		}
		
		$robots = implode("\n", $robotsLines);
		$filename = JPATH_SITE . "/robots.txt";
		if (file_put_contents($filename, $robots)) {
			return true;
		} else {
			JError::raiseWarning(100, "Could not save to $filename");
			return false;
		}
	}
	
}