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
			Joomla.submitform(task, document.getElementById('humans-form'));
		//}
	}
</script>

<div>
<!-- img src="components/com_littlehelper/assets/images/logo.png" class="leftimage"/ -->
<a href="http://humanstxt.org"  target="_blank"><img class="leftimage" width="222" height="64" alt="humans.txt - We are people, not machines" class="logo" src="components/com_littlehelper/assets/images/res/logo-humans.png"></a><h2>Humans</h2>

<?php echo JText::_("COM_LITTLEHELPER_HUMANS_DESC"); ?><br>
<br>
<span class='warn'><?php echo JText::_("COM_LITTLEHELPER_HUMANS_HEADER_REMINDER"); ?><pre>&lt;link rel="author" href="humans.txt" /&gt;</pre></span><br>


<br>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="humans-form">
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	
	<textarea name="humans" cols="130" rows="20" class="textbox"><?php echo $this->humanstxt; ?></textarea>
<br><em><?php echo JText::_("COM_LITTLEHELPER_HUMANS_FILELOC")?>: <?php echo $this->humansfilename; ?></em>
</form>
</div>
