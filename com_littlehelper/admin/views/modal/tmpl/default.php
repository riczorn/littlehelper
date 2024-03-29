<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleDeclaration('
	body,* {color:#33FF33;background-color:black; font-family:monospace;}
	button {padding:20px 60px;font-size:135%; color:blue}
	div.container {width:100%; font-family:monospace;}
	#closemodalbutton {color:#33FF33;font-family:monospace;}
');

$document->addScriptDeclaration('
	
	function countDown(countDownValue) {
			if (countDownValue>500) {
				countDownValue -= 201;
				document.getElementById("closemodalbutton").innerHTML = "Close in " + (parseInt(countDownValue / 1000)+1);
				setTimeout("countDown("+countDownValue+")",201);
			} else {
				// window.parent.SqueezeBox.close();
				// window.parent.document.getElementById( "sbox-window" ).close();
				if (window.parent && window.parent.jQuery) {
					window.parent.jQuery(".modal-backdrop").click();
				} else if (window.parent && window.parent.SqueezeBox) {
					window.parent.SqueezeBox.close();
				}
				// window.parent.location.reload();
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