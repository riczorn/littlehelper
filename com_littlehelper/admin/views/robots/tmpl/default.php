<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
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
			Joomla.submitform(task, document.getElementById('robots-form'));
		//}
	}
</script>

<div>
<img src="components/com_littlehelper/assets/images/robots.png" class="leftimage"/>
<a href="http://www.robotstxt.org"  target="_blank"></a>
<h2>robots</h2>

<?php echo JText::_("COM_LITTLEHELPER_ROBOTS_DESC"); ?><br>
<br>
<span class='warn'><?php echo JText::_("COM_LITTLEHELPER_ROBOTS_IMAGES_REMINDER"); ?></span><br>

<?php
//COM_LITTLEHELPER_ROBOTS_SITEMAP_REMINDER="Make sure you include a sitemap in the robots.txt. 
//...  The syntax is:%sYou can use %sjSitemap</a>, %sxmap</a> or one of the %sother extensions</a> on the JED"
 $xmapUrl='http://extensions.joomla.org/extensions/structure-a-navigation/site-map/3066';
 $jSitemapUrl='http://extensions.joomla.org/extensions/structure-a-navigation/site-map/24063';
 $extUrl = 'http://extensions.joomla.org/extensions/structure-a-navigation/site-map';
 $jSitemapBtn = "<a href='$jSitemapUrl' target='_blank'>";
 $xmapBtn = "<a href='$xmapUrl' target='_blank'>";
 $extBtn = "<a href='$extUrl' target='_blank'>";
 echo sprintf(JText::_("COM_LITTLEHELPER_ROBOTS_SITEMAP_REMINDER"),
		$this->getSyntax(),
		$jSitemapBtn,
	 	$xmapBtn,
	 	$extBtn	
	);
?>

<br>

<br>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="robots-form">
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	
	<textarea name="robots" cols="130" rows="20"  class="textbox"><?php echo $this->robotstxt; ?></textarea>
<br><em><?php echo JText::_("COM_LITTLEHELPER_ROBOTS_FILELOC")?>: <?php echo $this->robotsfilename; ?></em>
</form>
</div>
