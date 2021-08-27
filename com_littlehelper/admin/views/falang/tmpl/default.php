<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		//if (task == 'menu.cancel' || confirm('salvo?')) {
			Joomla.submitform(task, document.getElementById('falang-form'));
		//}
	}
</script>

<div>
<img src="components/com_littlehelper/assets/images/falang.png" class="leftimage"/>

<h2>falang</h2>
Traduzioni disponibili:
	<table class="currentItems">
	<tr><td>Lingua</td><td>Tabella</td><td class='right'>Voci tradotte</td></tr>
<?php
	foreach ($this->items as $key=>$item) {
		echo "<tr><td>$item->language</td><td>$item->table</td><td class='right'>$item->count</td></tr>";
	}


?>
	</table>

	<h3>
	Lingue caricate</h3>
	<p>Qui è possibile, per le lingue diverse da quella di default, invertirne i contenuti.
	Ovvero, se ho come lingua principale l'Inglese e decido di modificare in Italiano,
	mi ritroverò con tutte le traduzioni di falang inaccessibili e comunque malfunzionanti: alias tradotti
	a casaccio e storie del genere.
	<br>
		Quindi, dopo aver modificato la lingua principale, potrò scegliere di invertire le lingue, e
		questo script assegnerà tutti i valori corrispondenti incrociandoli, e quindi sostituirà anche
		l'id di lingua della tabella di falang, ottenendo una inversione completa:
		nel testo principale avrò l'italiano e in quello tradotto l'inglese.
	</p>
	<table class="currentLangs">
		<tr><td>Lingua</td><td>Id Lingua</td><td>Ordinamento</td><td class='right'>Funzioni</td></tr>
		<?php
		$mainLangId = $this->languages[0]->lang_id;
		foreach ($this->languages as $key=>$language) {
			echo "<tr><td>";
			echo $language->language;
			echo "</td><td>";
			echo $language->lang_id;
			echo "</td><td>";
			echo $language->ordering;
			echo "</td><td class='right'>";
			if ($language->lang_id !== $mainLangId) {
				$url = 'index.php?option=com_littlehelper&task=falang.invert'.
					'&sourceId='.$mainLangId.
					'&targetId='.$language->lang_id;
				echo sprintf("<a href='%s'>Inverti con lingua principale</a>",
					$url);
			}

			echo "</td></tr>";
		}


		?>
	</table>
<br>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="falang-form">
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>



</form>
</div>
