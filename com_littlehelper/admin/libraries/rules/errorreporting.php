<?php
/**
 * @author     Denis Dulici <denis@mijosoft.com>
 * @date       18.08.2013
 * @copyright  Copyright (C) 2008 - 2013 mijosoft.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Include the rule base class
require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/rule.php';

/**
 * JedcheckerRulesErrorreporting
 *
 * This class searches all files for the php error_reporting function
 * Developers are discouraged to use this in their joomla extensions
 * as users are able to set the error reporting in the global config
 *
 * @since  1.5
 */
class JedcheckerRulesErrorreporting extends JEDcheckerRule
{
	/**
	 * The formal ID of this rule. For example: SE1.
	 *
	 * @var    string
	 */
	protected $id = 'errorreporting';

	/**
	 * The title or caption of this rule.
	 *
	 * @var    string
	 */
	protected $title = 'COM_JEDCHECKER_RULE_ERRORREPORTING';

	/**
	 * The description of this rule.
	 *
	 * @var    string
	 */
	protected $description = 'COM_JEDCHECKER_RULE_ERRORREPORTING_DESC';


	/**
	 * Initiates the file search and check
	 *
	 * @return    void
	 */
	public function check()
	{
		// Find all php files of the extension
		$files = JFolder::files($this->basedir, '.php$', true, true);

		// Iterate through all files
		foreach ($files as $file)
		{
			// Try to find the base64 use in the file
			if ($this->find($file))
			{
				// Add as error to the report if it was not found
				$this->report->addWarning($file, JText::_('COM_JEDCHECKER_ERROR_ERRORREPORTING'));
			}
		}
	}

	/**
	 * Reads a file and searches for any encoding function defined in the params
	 * Not a very clever way of doing this, but it should be fine for now
	 *
	 * @param   string  $file  - The path to the file
	 *
	 * @return    boolean            True if the statement was found, otherwise False.
	 */
	protected function find($file)
	{
		$content = (array) file($file);

		// Get the functions to look for
		$encodings = explode(',', $this->params->get('errorreportings'));

		foreach ($encodings as $encoding)
		{
			$encoding = trim($encoding);

			foreach ($content AS $line)
			{
				// Search for "base64"
				$pos_1 = stripos($line, $encoding);

				if ($pos_1 !== false)
				{
					return true;
				}
			}
		}

		return false;
	}
}
