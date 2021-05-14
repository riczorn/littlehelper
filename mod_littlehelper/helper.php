<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL v2
 */

defined('_JEXEC') or die;

abstract class modLittleHelperHelper
{
	
	public static function getButtons($view_mode)
	{
		if ($view_mode=='cpanel') {
			
			$iconsize = 32;
			$showtext = true;
		} else {
			// presumibilmente status
			$iconsize=16;
			$showtext = false;
		}
		
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('
				div#littlehelpermoduletoolbar.toolbar-list span {height:'.$iconsize.'px;width:'.$iconsize.'px;}
				div#littlehelpermoduletoolbar-cpanel.toolbar-list span {height:32px;width:32px;}
				div#littlehelpermoduletoolbar-status.toolbar-list span {height:16px;width:16px;}
				#module-status {white-space:nowrap;}
				#module-status form {display:inline-block;float:right;}
				#module-status > span {display:inline-block;float:none;}
				#littlehelpermoduletoolbar-status {float:right;display:inline-block;}
				div#littlehelpermoduletoolbar-status.toolbar-list li {height:16px;}
				div#littlehelpermoduletoolbar-status.toolbar-list li a {padding:0}
				.icon-16-ccjoomla {background-image:url(components/com_littlehelper/assets/images/cachejoomla16.png)}
				.icon-32-ccjoomla {background-image:url(components/com_littlehelper/assets/images/cachejoomla32.png)}
				.icon-16-ccfs {background-image:url(components/com_littlehelper/assets/images/cachefs16.png)}
				.icon-32-ccfs {background-image:url(components/com_littlehelper/assets/images/cachefs32.png)}
				.icon-16-trash {background-image:url(components/com_littlehelper/assets/images/bin16.png)}
				.icon-32-trash {background-image:url(components/com_littlehelper/assets/images/bin32.png)}
				.icon-48-littlehelper {background-image:url(components/com_littlehelper/assets/images/logo48.png)}
				'.
				// joomla 30
				'
				#littlehelpermoduletoolbar-status button .icon-cog:before,
				#littlehelpermoduletoolbar-cpanel button .icon-cog:before {content:""}
				#littlehelpermoduletoolbar-status-popup-ccfs button .icon-cog {background-image:url(components/com_littlehelper/assets/images/cachefs16.png);}
				#littlehelpermoduletoolbar-cpanel-popup-ccfs button .icon-cog {background-image:url(components/com_littlehelper/assets/images/cachefs16.png);}
				#littlehelpermoduletoolbar-status-popup-ccjoomla button .icon-cog {background-image:url(components/com_littlehelper/assets/images/cachecc16.png);}
				#littlehelpermoduletoolbar-cpanel-popup-ccjoomla button .icon-cog {background-image:url(components/com_littlehelper/assets/images/cachecc16.png);}
				#littlehelpermoduletoolbar-status-popup-trash button .icon-cog {background-image:url(components/com_littlehelper/assets/images/bin16.png);}
				#littlehelpermoduletoolbar-cpanel-popup-trash button .icon-cog {background-image:url(components/com_littlehelper/assets/images/bin16.png);} 					

				#trashncacheForm {
					float:right;
					margin:0;
				}
				.navbar #trashncacheForm .btn-toolbar .btn-wrapper {
					margin:0 5px 0 0 ;
				}
				.navbar #trashncacheForm  .btn {
					margin-top:0;
				}
				
				.navbar #trashncacheForm .btn-small {
					padding: 0 7px;
					line-height:12px;
				}
				/*j3.3/isis*/
				
				#trashncacheForm i,#trashncacheForm span {
					text-indent:-9000px;overflow:hidden;
				}
				
				#trashncacheForm .icon-trash {
					background-image:url(components/com_littlehelper/assets/images/bin16.png);
				}
				#trashncacheForm .icon-ccfs {
					background-image:url(components/com_littlehelper/assets/images/cachefs16.png);
				}

				.modal-backdrop, .modal-backdrop.fade.in {
					z-index:1015;
				}
				
				','text/css');
		if (version_compare(JVERSION, '3.0.0', 'lt')) {
			$document->addStyleDeclaration(
			'#module-status form {
			    display: inline-block;
			    float: none;
			    height: 16px;
			    padding: 4px 5px 0;
			}');
		}
		//load the JToolBar library and create a toolbar
		jimport('joomla.html.toolbar');
		JHTML::_( 'behavior.modal', 'input#your_hidden_button_id' );
		$bar = new JToolBar( 'littlehelpermoduletoolbar-'.$view_mode );
		
		//and make whatever calls you require
		// $bar->set('title',JText::_("COM_LITTLEHELPER"));
		
		$height = '350'; $width = '550';
		$top = NULL; $left = NULL;
		$path = ''; $onClose = '';
		
		$params = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $params->get('params');
				
		if (empty($params->button_recycle) || (int)($params->button_recycle)==1) {
			$bar->appendButton('Popup', 'trash', $showtext?JText::_("COM_LITTLEHELPER_CMD_RECYCLE_SHORT"):"", 'index.php?option=com_littlehelper&amp;task=trash_n_cache.emptyrecyclebin&amp;tmpl=component&amp;view=modal', $width, $height, $top, $left, $onClose,$showtext?"":JText::_("COM_LITTLEHELPER_CMD_RECYCLE_SHORT"));	
					}
		if (empty($params->button_cache) || (int)($params->button_cache)==1) {

			if (!isset($params->button_cache_usefs) || (int)($params->button_cache_usefs)==1) {
				$bar->appendButton('Popup', 'ccfs', 	  $showtext?JText::_("COM_LITTLEHELPER_CMD_FS_SHORT"):"", 'index.php?option=com_littlehelper&amp;task=trash_n_cache.cleanfscache&amp;tmpl=component&amp;view=modal', $width, $height, $top, $left, $onClose,$showtext?"":JText::_("COM_LITTLEHELPER_CMD_FS_SHORT"));
			}else{
				$bar->appendButton('Popup', 'ccjoomla', 	  $showtext?JText::_("COM_LITTLEHELPER_CMD_JOOMLA_SHORT"):"", 'index.php?option=com_littlehelper&amp;task=trash_n_cache.cleanjoomlacache&amp;tmpl=component&amp;view=modal', $width, $height, $top, $left, $onClose,$showtext?"":JText::_("COM_LITTLEHELPER_CMD_JOOMLA_SHORT"));
			}
		}		
		
		// Joomla 30: administrator/components/com_modules/views/modules/view.html.php non usa popup?
		// semplice: popup non si può chiudere e funziona da culo dalla posizione status.
		
		//generate the html and return
		$result = $bar->render();
		$result = str_replace('icon-32-','icon-'.$iconsize.'-',$result);
		return $result;
	}	
	
	/**
	 * K2 throws an ugly error "item must have a title" on its Item view.
	 * so we'll just prevent execution there for now. 
	 * 
	 * Cforms has an incompatibility as well:
	 */
	public static function excludeComps() {
		$input = JFactory::getApplication()->input;
		if ($input->get('option')=='com_k2') {
			if ($input->get('view')=='item') {
				return true;
			}
		}
		if ($input->get('option')=='com_cforms') {
			if ($input->get('view')=='form') {
				return true;
			}
		}
		return false;
	}
}
