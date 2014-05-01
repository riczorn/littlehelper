<?php
/**
 * Custom form element to show some fancy options
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die('Error');

jimport('joomla.form.formfield');

class JFormFieldZzinfo extends JFormField
{
	
    protected $type = 'zzinfo';
    /**
     * Build the layout of the custom field, both styles and markup.
     * Note: there is no saving, this is a display-only custom form element.
     * @see JFormField::getInput()
     */
    protected function getInput() {
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('
			.zzinfobox {border:1px solid gray;
						background:url(/administrator/components/com_littlehelper/assets/images/logo.png) top left no-repeat #EFEFEF;
    					border-radius:12px;
    					padding:0 10px 10px 128px;
    					min-height:128px;
    					position:relative;
    					margin-bottom:1em;
    					}
    		.zzinfobox > div {
    					font-size:110%
    					}
    		.zzinfobox > h3 {
    					border-bottom:1px solid #ABABAB;
    		}
    		.zzinfobox > div.zzheader {
    					font-weight:bold;
    		}
    		.zzinfobox > div.zzfooter {
    					text-align:center;
    					font-size:100%;
    					bottom:10px;
    					position:absolute;
    		}
    		div.current label, div.current span.faux-label {min-width:200px}
    		
		','text/css');
	
		$title = !empty($this->element['title'])?$this->element['title']:'a fasterjoomla component';
		$header = !empty($this->element['header'])?$this->element['header']:'';
		$body = !empty($this->element['body'])?$this->element['body']:'';
		$footer = !empty($this->element['footer'])?$this->element['footer']:'';
		$class = !empty($this->element['class'])?$this->element['class']:'';
		
		$title=JText::_($title);
		$header=JText::_($header);
		$body=JText::_($body);
		$footer=JText::_($footer);
		
		return "<br/><div class='zzinfobox $class'>
				<h3>$title</h3>
				<div class='zzheader'>$header</div>
				<div class='zzbody'>$body</div> 
				<div class='zzfooter'>Copyright (c) 2012 <a href='http://www.fasterjoomla.com' target='_blank'>www.fasterjoomla.com</a> $footer</div> 
			</div>";
	}
}
