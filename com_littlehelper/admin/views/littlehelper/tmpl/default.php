<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

?>
<img src="components/com_littlehelper/assets/images/logo.png" class="leftimage"/>
<div class="right" style="text-align:right">
<?php 
echo "<a class='fancybutton moduleconfig' href='$this->adminModulesUrl'>"
 		.JText::_("COM_LITTLEHELPER_MODULE_OPEN_CONFIG")."</a><br>";
echo $this->faqlink;
?>
<br>
<span class="news" style="font-weight:bold">News from fasterjoomla</span><br>
<iframe style="width:250px;height:300px;border:0" noborder src="http://www.fasterjoomla.com/files/extensions/littlehelper/littlehelper_news.php"></iframe>
</div>
<h2><?php echo JText::_("COM_LITTLEHELPER_INTRO_TITLE"); ?></h2>
<p><?php echo JText::_("COM_LITTLEHELPER_INTRO_DESC");  ?></p>

<p><span class="warn">
<?php echo JText::_("COM_LITTLEHELPER_INTRO_WARN"); ?>
</span></p>
<p><?php 
echo JText::_("COM_LITTLEHELPER_INTRO_RELAX"); ?></p>

<div class='availablemodules'>
<?php 
	echo $this->getModuleInfo('status');
	echo $this->getModuleInfo('cpanel');
?>
</div>