<?php

/**
 * com_littlehelper Falang model
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();

class LittleHelperModelFalang extends JModelLegacy {

	/**
	 * @access    public
	 * @return    void
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Method to load a view of available translations
	 */
	public function load() {
		$db = JFactory::getDbo();
		$result = $db->setQuery('SELECT l.title as language, a.language_id, a.reference_table as "table", count(a.reference_field) as "count"
			FROM `#__falang_content` AS a
			 LEFT JOIN `#__languages` as l on (a.language_id=l.lang_id)
			where a.reference_field="title" group by a.language_id, a.reference_table order by a.language_id')->loadObjectList();
		return $result;
	}

	public function loadLanguages() {
		$db = JFactory::getDbo();
		$result = $db->setQuery('SELECT l.lang_id, l.title as language, l.ordering, count(a.reference_field) as "count"
			FROM `#__languages` as l
			 LEFT JOIN `#__falang_content` AS a on (a.language_id=l.lang_id AND a.reference_field="title")
			 where l.published>0 AND l.access>0
			 group by l.lang_id order by l.ordering')->loadObjectList();
		return $result;
	}

	/**
	 * Main language invert function. Will list all available items for translation
	 * (only content, menus, modules supported currently), and pass them on to the appropriate invert functions.
	 *
	 * @param $sourceId
	 * @param $targetId
	 */
	public function invert($sourceId, $targetId) {

		$db = JFactory::getDbo();
		echo "<h1>inverto lingue falang $sourceId, $targetId</h1>";

		$items = $db->setQuery('select reference_id, reference_table from #__falang_content where language_id='.
			$targetId.' group by reference_id, reference_table')->loadObjectList();
		if ($items)
		foreach ($items as $item) {
			echo "<h2>Translate $item->id, $item->reference_table</h2>";
			switch ($item->reference_table) {
				case 'menu':
				case 'content':
				case 'modules':
					$this->invertElement($item, $sourceId, $targetId);
					break;
				default:
					echo "Type not supported: $item->reference_table<br>";
					break;
			}
		}
	}

	/**
	 * Invert a single element's content
	 *
	 * @param $element
	 * @param $sourceId  the source language id
	 * @param $targetId  the target language id
	 */
	public function invertElement($element, $sourceId, $targetId) {
		// select menu item:
		$db = JFactory::getDbo();
		$table = '#__'.$element->reference_table;
		// set up the filters, only these fields should be translated / inverted:
		$translateElements = array();
		$translateElements['menu'] = array('title','alias','path');
		$translateElements['modules'] = array('title','content');
		$translateElements['content'] = array('title','alias','introtext','fulltext','metakey','metadesc');

		$joomlaItem  = $db->setQuery("select * from $table where id=".$element->reference_id)->loadObject();
		echo "<h3>Element $element->reference_table</h3>";
		//var_dump($menuItem);
		if ($joomlaItem) {
			$translations = $db->setQuery('select * from #__falang_content where language_id='.
				$targetId.' and reference_id= '.$db->quote($element->reference_id))->loadObjectList();
			echo "<h4>Translations $element->reference_id</h4>";
			//var_dump($translations);
			echo "<table>";
			echo "<tr><td>id</td><td>ref_id</td><td>field</td><td>Orig</td><td>Transl</td></tr>";
			foreach($translations as $translation) {
				if (in_array($translation->reference_field, $translateElements[$element->reference_table])) {
					$mainVal = $joomlaItem->{$translation->reference_field};
					$joomlaItem->{$translation->reference_field} = $translation->value;
					if (!empty($mainVal)) {
						$translation->value = $mainVal;
						echo "<tr><td>".$translation->id."</td><td>".$translation->reference_id."</td><td>".
							$translation->reference_field."</td><td>$mainVal</td><td>".
							$joomlaItem->{$translation->reference_field}."</td></tr>";
					}
				} else echo "/ignored " . $translation->reference_field;
				$translation->language_id = $sourceId;
				$db->updateObject('#__falang_content', $translation, ['id']);
			}
			echo "</table>";
			$db->updateObject($table, $joomlaItem, ['id']);
		}
	}
}
