<?php
/**
 * System Plugin LittleHelperKnife adds the necessary markup to support favicons / apple precomposed favicons etc.
 * All configuration is handled in the LittleHelper component.
 * 
 * @version	$Id
 * @package littlehelper.fixhead
 * @author  Riccardo Zorn support@fasterjoomla.com
 * @copyright (C) 2011-2013 - http://fasterjoomla.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/*
 * A technical note.
 * J 2.5 implements addFavicon() in libraries/joomla/document/html/html.php
 * 	which searches the current template and the site root for a favicon, and if found
 *  adds the necessary markup.
 *  
 *  This happens before any plugins; for the sake of speed (we already do preg_replace for touch icons)
 *  and to account for hardcoded favicons, 
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * This plugin has two events, 
 * onBeforeCompileHead, which is fired after component and modules output, and
 * onAfterRender, which has access to the final html just before it gets sent to the user.
 */
class plgSystemLittleHelper extends JPlugin
{
	/**
	 * This is the last event invoked where I can edit the page.
	 * Since I have removed all scripts from the JDocument Head in the onBeforeCompileHead method,
	 * I will now invoke my custom versions of renderHead and renderFoot (which only manage scripts and styles)
	 * to fill in the blanks.
	 * Insert the footer scripts at the end of the document just before the </body>
	 */
	public function onAfterRender() {
		$body = JResponse::getBody();
		if ($this->isAllowed()) {
			$body = JResponse::getBody();
 			
 			// Here I have the chance to pick up all leftover resources which never entered the JDocument Headers.
 			$body = $this->removeIcons($body);
 			 			
			$find = "</head>";
			$replace = $this->renderIcons()."</head>";
 			
 			$body = str_replace($find,$replace,$body);
			JResponse::setBody($body);
		}

	}

	/**
	 * We try to determine if it's appropriate for the plugin to modify headers:
	 * Exclude administrator, non-html views.
	 */
	private function isAllowed() {
		
		$document	= JFactory::getDocument();
		
		if (JPATH_BASE == JPATH_ADMINISTRATOR) {
			// do administrators deserve fancy touch icons? do they care? are they using a phone?
		 	return false;
		}

		if ( $document->getType() != 'html' ) { 
			return false; 
		}
		
		if (empty($this->params)) 
			return false;
		
		if ($this->params->get('markup','')=='') {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Remove all icons and favicons link tags 
	 * unless some markup has been defined (usually in the component, but optionally in the plugin's
	 * config, no code is removed.
	 * Sample markup to remove: 
	 *   <link href="/templates/h5bp4j_sass/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	 *   <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/templates/xxyt/apple-touch-icon-114x114-precomposed.png">
 	 *   <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/templates/xxyt/apple-touch-icon-72x72-precomposed.png">
 	 *   <link rel="apple-touch-icon-precomposed" href="/templates/xxyt/apple-touch-icon-precomposed.png">
 	 *   
 	 *   The regexpr are much wider and expect just the basics to discriminate against other link types.
 	 *   So they should catch also all future apple icons, even those we might not yet be replacing.
	 * @param unknown_type $body
	 */
	private function removeIcons($body) {
		$find = array(
				"/<link[^>]*image\/vnd.microsoft.icon[^>]*>/",
				"/<link[^>]*apple-touch-icon[^>]*>/");
		$body = preg_replace($find, "", $body);
		return $body;
	}
	
	/**
	 * Icons are pre-rendered by the component.
	 * In case no markup is set, then the above function removeIcons won't remove any markup as well.
	 */
	private function renderIcons() {
		return $this->params->get('markup');
	}
}
