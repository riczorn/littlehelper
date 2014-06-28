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
			error_log($filename);
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
		
}