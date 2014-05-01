<?php
/**
 * This view is only invoked through ajax.
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

	$arr = $this->componentTrash;
	echo "<table class='adminlist table table-striped table-condensed'>";
	echo sprintf("<thead><tr><th>%s</th><th></th></tr></thead>
			<tbody>",
			''
			//JText::_("COM_LITTLEHELPER_TRASH_KIND_".strtoupper($this->kind)) //JText::_("COM_LITTLEHELPER_RECYCLED_DESC")
		);
	$i=0;
	if (!empty($arr)) {
		//var_dump($arr);
		foreach($arr as $item) {
			$class = 'recycle-low'; 
			// each row is an array 
			//$descString = 'deleted item '.join(",",$item);
			$nameString = $item['name'];
			$descString = substr( $item['description'],0,100);
			echo "<tr class='row". $i++ % 2 . "'><td class='$class'>$nameString</td><td class='description'>$descString</td></tr>";
		}
	}
	echo "</tbody></table>";
?>

