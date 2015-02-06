<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

JHtml::_('behavior.modal');
$doc = JFactory::getDocument();
if (!version_compare(JVERSION, '3.0.0', 'ge'))
	$doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
$doc->addScriptDeclaration('
	jQuery(function($){
		$("a.inline").click(function(event){
			event.preventDefault();
			$base = $(this);
			var href=$base.attr("href");
			$base.attr("href","").unbind("click").click(function(event){event.preventDefault();});
			$.get(href,function(data){
				$base.parent().append(data);
			});
			return false;
		});
	});		
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper');?>" method="post" name="adminForm" id="adminForm">
<?php 
	$arr = $this->data;
	echo "<table class='adminlist table table-striped table-condensed'>";
	echo sprintf("<thead><tr><th>%s</th><th class='right w20'>%s</th></tr></thead><tbody>",
		JText::_("COM_LITTLEHELPER_RECYCLED_DESC"),
		JText::_("COM_LITTLEHELPER_RECYCLED_COUNT")
		);
	$i=0;
	if (!empty($arr)) {
		//foreach($arr as $desc=>$count) {
		foreach($arr as $item) {
			$desc = $item->kind;
			$count = $item->count;
			$descString = JText::_("COM_LITTLEHELPER_TRASHDESC_".strtoupper($desc));

			if (!empty($item->link)) {
				//$function = "Joomla.popupWindow('$item->link', 'Trash content', '90%', '90%', 1)";
				//$descString = sprintf('<a href="%s" class="inline" rel="{handler: \'iframe\', size: {x: 800, y: 400}}" >%s</a>',
				$descString = sprintf('<a href="%s" class="inline" title="%s">%s</a>',
					$item->link,
						JText::_("COM_LITTLEHELPER_TRASH_CLICKFORMORE"),
						$descString);
			}
			if ($desc=='featured')
				$class='db';
			else if ($desc=='cache')
				$class='cache';
			else
				$class = 'recycle'; 
			echo "<tr class='row". $i++ % 2 . "'><td class='$class'>$descString</td><td class='right'>$count</td></tr>";
		}
	}
	echo "</tbody></table>";
?>

	<input type="hidden" name="task" value="" />
</form>