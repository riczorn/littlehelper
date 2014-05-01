<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleDeclaration('
	body,* {color:#00DDDD;background-color:black}
	button {padding:20px 60px;font-size:135%; color:blue}
	div.container {width:100%}
');

$document->addScriptDeclaration('
	
	function countDown(countDownValue) {
			if (countDownValue>500) {
				countDownValue -= 201;
				document.getElementById("closemodalbutton").innerHTML = "Close in " + (parseInt(countDownValue / 1000)+1);
				setTimeout("countDown("+countDownValue+")",201);
			} else {
				window.parent.SqueezeBox.close();
				window.parent.location.reload();
			}
		}
	(function() {
		
		function startCountDown() {
			countDown(3000);
		}
		if (window.addEventListener) {
	        window.addEventListener("DOMContentLoaded", startCountDown, false);
	    } else {
	        window.attachEvent("onload", startCountDown);
	    }
		
	})();
');
?>

<div class="container">
	<button id="closemodalbutton" class="closemodal" onclick="window.parent.SqueezeBox.close();window.parent.location.reload();" type="button">
				Close</button>
</div>