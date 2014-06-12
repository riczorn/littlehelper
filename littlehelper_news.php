<?php
  // store the access so we don't have to use any third-party libraries
	try {
		date_default_timezone_set('Europe/Rome');
		$now = date("Y-m-d H:i:s"); 
		$addr = $_SERVER['REMOTE_ADDR'];
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$ref = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'noreferrer';
		$script = $_SERVER['SCRIPT_NAME'];
		$scripta = explode('/',$script);
		foreach ($scripta as $script) {
			;
		}
		$script = str_replace('_news.php','',$script);
		$logData = "$now\t$addr\t$script\t$ref\t$agent\n";
		file_put_contents('../../../../logs/extensions.log',$logData,FILE_APPEND | LOCK_EX );
	} catch(Exception $e) {
		error_log('ERROR LOGGING '.$logData);
		error_log('  >> '.$e);
	}
	error_log(__FILE__.'../../../../logs/extensions.log');
	error_log($logData);


?><!DOCTYPE html>
<html class="no-js" lang="en" >
<head>
	<meta charset="utf-8" />
    <base href="http://www.fasterjoomla.com/" />
    <title>Little Helper 2.0.14</title>
	<style>
		body {
			background:linear-gradient(120deg, #EBF1F6 0%, #ABD3EE 50%, #89C3EB 51%, #D5EBFB 100%) no-repeat scroll 0 0 #D5EBFB;
			font-family:Arial,consolas,Courier New, monotype;

			font-size:0.8em;
			margin:5px 5px 20px;
		}
		a {text-decoration:none;outline:none}
		a,a:visited {color:#ff6600;}
		a:hover {
			text-shadow:0 0 20px red,0 0 15px red,0 0 10px red,0 0 5px red;
			color:white;
			transition: 0.3s all ease-in-out;
		}
		a:active {outline:none}
		h1,h2,h3 {
			font-size:1.5em;
			font-weight:100;
			border-bottom: 1px solid #CFCFCF;
			text-shadow:0 0 20px white,0 0 15px white,0 0 10px white,0 0 5px white;
			text-align:right;
			}
		h2 {font-size:1.3em;}
		* {margin:0;padding:0}
		.fasterlogo {bottom:0; margin-top:30px;text-align:center}
	</style>
</head>
<body>
<h1>Current Version: 2.0.14</h1>
<p><a href="http://www.fasterjoomla.com/en/download/little-helper" target="_blank">Download</a> ★ <a href="http://www.fasterjoomla.com/en/extensions/joomla-little-helper" target="_blank">Docs</a>
<h2>Info on Little Helper</h2>
<p>
<br>09/05/2014 2.0.13 Crop feature fully working<br>12/05/2014 2.0.14 Statusbar on J3 smaller<br>
</p>
<h2>News from <a href="http://www.fasterjoomla.com/" target="_blank">FasterJoomla!</a></h2>
<p>
24/05/2014 <a href="http://www.fasterjoomla.com/en/extensions/stripe-joomla-payment-plugin" target="_blank">Stripe Payment plugin</a> released<br>28/05/2014 <a href="http://www.fasterjoomla.com/en/extensions/spritz-your-text-with-faster-content" target="_blank">Faster Content with Spritz</a> released<br>
</p>

<div class="fasterlogo">
<a href="http://extensions.joomla.org/extensions/administration/admin-performance/24016" target="_blank">Show us your ♥: Review on the JED</a><br>
<a href="http://www.fasterjoomla.com/" target="_blank"><img src="http://www.fasterjoomla.com/images/fasterjoomla/headerlogo.png" style="max-width:230px" /></a></div>
</body></html>