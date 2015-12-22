<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       26.10.15
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


jimport('joomla.filesystem');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

jimport('joomla.filesystem.file'); // fasterjoomla

/**
 * Class jedcheckerControllerPolice
 * @since  1.0
 */
class JedcheckerControllerPolice extends JControllerlegacy
{
	/**
	 * Runs all the rules on the given directory
	 * @return bool
	 */
	public function check()
	{
		$rule = JRequest::getString('rule');

		JLoader::discover('jedcheckerRules', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/rules/');
		// fasterjoomla
		//JLoader::discover('jedcheckerRules', dirname(__FILE__) . '/libraries/rules/');

		$path  = JPATH_SITE;//fasterjoomla JFactory::getConfig()->get('tmp_path') . '/jed_checker/unzipped';
		$class = 'jedcheckerRules' . ucfirst($rule);

		// Stop if the class does not exist
		if (!class_exists($class))
		{
			return false;
		}

		// Loop through each folder and police it

		$folders = $this->getFolders();

		foreach ($folders as $folder)
		{
			$this->police($class, $folder);
		}

		return true;
	}

	/**
	 *
	 */
	protected function scanFolder($folder, & $folders) {
		$tmp_folders = JFolder::folders($folder);

		if (!empty($tmp_folders))
		{
			foreach ($tmp_folders as $tmp_folder)
			{
				$folders[] = $folder . '/' . $tmp_folder;
				$this->scanFolder($folder . '/' . $tmp_folder, $folders);
			}
		}
	}

	/**
	 * Get the folders that should be checked
	 * @return array
	 */
	protected $folders;
	protected function getFolders()
	{
		if ($this->folders) return $this->folders;
		$this->folders = array();

		// Add the folders in the "jed_checked/unzipped" folder
		$path        = JPATH_BASE; // fasterjoomla JFactory::getConfig()->get('tmp_path') . '/jed_checker/unzipped';

		$this->scanFolder($path, $this->folders);

// 		$tmp_folders = JFolder::folders($path);

// 		if (!empty($tmp_folders))
// 		{
// 			foreach ($tmp_folders as $tmp_folder)
// 			{
// 				$folders[] = $path . '/' . $tmp_folder;
// 			}
// 		}

		// Parse the local.txt file and parse it
		$local = JFactory::getConfig()->get('tmp_path') . '/jed_checker/local.txt';

		return $this->folders;
	}

	/**
	 * Run each rule and echo the result
	 *
	 * @param   string $class  - the class anme
	 * @param   string $folder - the folder where the component is located
	 *
	 * @return void
	 */
	private $filesChecked = 0;
	protected function police($class, $folder)
	{
		$this->filesChecked++;
		//if ($this->filesChecked > 1000) return;
		// Prepare rule properties
		$properties = array('basedir' => $folder);

		// Create instance of the rule
		$police = new $class($properties);

		// Perform check
		$police->check();

		// Get the report and then print it
		$report = $police->get('report');

		// fasterjoomla
		$html = $report->getHTML();
		if (strlen(trim($html))>3) {
			echo "<br>Folder no. ".$this->filesChecked.":".$folder . "<br>";

			echo '<span class="rule">'
				. JText::_('COM_JEDCHECKER_RULE') . ' ' . JText::_($police->get('id'))
				. ' - ' . JText::_($police->get('title'))
				. '</span><br/>'
				. $report->getHTML();
		}

		$this->flush();
	}
	private function flush ()
	{
		echo str_repeat(' ', 10000);
		flush();
		ob_flush();
	}
}
