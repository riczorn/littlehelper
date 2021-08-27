<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

?>
<img src="components/com_littlehelper/assets/images/logo.png" class="leftimage"/>
<div class="right" style="text-align:right">
	<?php 
	// link to modules page with filter set
	echo "<a class='fancybutton moduleconfig large' href='$this->adminModulesUrl'>"
	 		.JText::_("COM_LITTLEHELPER_MODULE_OPEN_CONFIG")."</a><br>";
	
	// link to plugin page with filter set
	echo "<a class='fancybutton pluginconfig large' href='$this->adminPluginsUrl'>"
	 		.JText::_("COM_LITTLEHELPER_PLUGIN_OPEN_CONFIG")."</a><br>";
	
	echo $this->faqlink;
	?>
	<br>
	<span class="news" style="font-weight:bold">News from fasterjoomla</span><br>
	<iframe style="width:250px;height:300px;border:0" noborder src="https://www.fasterjoomla.com/files/extensions/littlehelper/littlehelper_news.php"></iframe>
</div>
<div class="left">
	<h2><?php echo JText::_("COM_LITTLEHELPER_INTRO_TITLE"); ?></h2>
	<p><?php echo JText::_("COM_LITTLEHELPER_INTRO_DESC");  ?></p>
	
	<p><span class="warn">
	<?php echo JText::_("COM_LITTLEHELPER_INTRO_WARN"); ?>
	</span></p>
	<p><?php 
	echo JText::_("COM_LITTLEHELPER_INTRO_RELAX"); ?></p>
</div>
<div class='availablemodules'>
<?php 
	echo $this->getModuleInfo('status');
	echo $this->getModuleInfo('cpanel');
?>
</div>