<?php
/**
 * Custom form element to automatically clear the administration cache on show.
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die('Error');

jimport('joomla.form.formfield');

class JFormFieldClearCache extends JFormField
{
	
    protected $type = 'clearcache';
    /**
     * Invoke the admin cache cleaning method and display status
     * Note: there is no saving, this is a display-only custom form element.
     * @see JFormField::getInput()
     */
    protected function getInput() {
		require_once(JPATH_ADMINISTRATOR.'/components/com_littlehelper/models/trash_n_cache.php');
    	$model = JModelLegacy::getInstance('trash_n_cache','LittleHelperModel');
    	
    	if ($model->clearAdministratorCache()) { 
    		return "Cache cleared";
    	}
    	else {
    		return "Error clearing cache";
    	}  
    	//require_once JPATH_ADMINISTRATOR.'/components/com_littlehelper/helpers/trash_n_cache.php';
		// $res = LittleHelperHelperTrash_n_Cache::removeFolderPHP($folder);
		
		//return "Clearing admin cache...";
	}
}
