<?php
/**
 * Frontend view for the component Little Helper
 * This file is used by the backend for testing purposes.
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

/*
 * Please note the strange syntax here is used so that this file,  
 * is compliant with Joomla Security Guidelines, will be able to 
 * notify its invoker that php is running.
 */

defined('_JEXEC') or die("<html><head>
		<script>
			if (window.parent)
				if (window.parent.onTestSuccess)
					window.parent.onTestSuccess();
		</script>
		<style>
			.success {color:green;}
		</style>
		</head><body><div class='success'>Success: PHP is running</div></body></html>");

$document = JFactory::getDocument();
$document->setMetaData('robots', 'noindex, nofollow');
?>
<h1>LittleHelper Knife for Joomla Admin</h1>
You may or may not be here.
<?php exit; ?>