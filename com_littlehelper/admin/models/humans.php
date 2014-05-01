<?php

/**
 * com_littlehelper Humanstxt model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelHumans extends JModelLegacy {
	
	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Method to load a default humans.txt or read the current one.
	 * @return hashtable with keys 'content','modules','menuitems'.
	 */
	public function load() {
		$filename = JPATH_SITE."/humans.txt";
		$humanstxt = "";
		if (file_exists($filename)) {
			$humanstxt = file_get_contents($filename);
		} else {
			$filename = dirname(dirname(__FILE__))."/assets/txt/humans.txt";
			if (file_exists($filename)) {
				$humanstxt = file_get_contents($filename);
			} else {
				JError::raiseWarning(100, "Default humans.txt not found, installation corrupt?");
			}
		}
		return array($humanstxt,$filename);
	}
	
	/**
	 * Save the humans.txt to disk.
	 * @return true for success
	 */
	public function save($humans) {
		$filename = JPATH_SITE."/humans.txt";
		if (file_put_contents($filename, $humans)) {
			return true;
		} else {
			JError::raiseWarning(100, "Could not save to $filename");
			return false;
		}
	}
	
	
}