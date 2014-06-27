<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 26-Jun-2014
 * @license    GNU/GPL v2
 */

error_reporting(E_ALL);
error_log('loading installer');

defined('_JEXEC') or die;
// http://docs.joomla.org/J2.5:Managing_Component_Updates_%28Script.php%29

class pkg_littlehelperInstallerScript
{
	/**
	 * Littlehelper 1.x uses the folder /icons by default (or a user-set folder) to 
	 * store its images;  This structure changed (see the README.php) so we'll take a step 
	 * here to copy the images to the new folders;
	 */
	function preflight( $type, $parent ) {
		error_log('installing littlehelper '. $type);
		// interesting to note, in Joomla 2.5, with the extension already installed, 
		// $type will be == 'install' and getParam() is undefined.
		// hence more checks are run:
		if ( ($type == 'update') || ($type == 'install') ) {
			error_log('updating littlehelper');
			// let's see if littlehelper is already installed:
			$manifest = JPATH_ADMINISTRATOR.'/components/com_littlehelper/littlehelper.xml';
			if (file_exists($manifest)) {
				error_log('manifest exists');
				// the manifest contains the string:
				// <version>1.8.6</version>
				$manifestContents = file_get_contents($manifest);
				$matches = array();
				$version = preg_match('@<version>([0-9\.]+)</version>@',$manifestContents,$matches);
				if (count($matches)>0) {
					error_log("matches\n\n".var_export($matches,true));
				}

				// this fails to load... no idea why.
				/*$reg = new JRegistry();
				$reg->loadFile($manifest, 'xml');
				error_log(var_export($reg,true));
				error_log('found version2 '.$reg->get('extension.version'));
				*/
	
			} else {
				error_log('apparently a fresh installation, proceed');
			}
			return true;
			$oldRelease = $this->getParam('version');
			$rel = $oldRelease . ' to ' . $this->release;
			error_log($rel);
			error_log('end reading version info');
			if ( version_compare( $oldRelease, '1.9', '<' ) ) {
				// update the folder structure:
				error_log('updating');
				$mparams = JComponentHelper::getParams( 'com_littlehelper' );
				$params = $mparams->get('params');
				if (empty($params->favicons_sourcepath)) {
					// no favicons path set, nothing to move!
					error_log('favicons_sourcepath is not set, nothing to move');
					return true;
				} else {
					return $this->moveFolders( $params->favicons_sourcepath);
				}
			} else {error_log('already 2');}
		}
	}
	
	/**
	 * if basepath is not set or it doesn't exist, exit;
	 * else copy the images to the source folder (cannot move: the images could have been used elsewhere on the site)
	 * @param unknown $basepath
	 * @return boolean
	 */
	private function moveFolders($imagesPath) {
		if (empty($imagesPath) || !file_exists(JPATH_SITE . $imagesPath)) {
			return true;
		}
		
		$croppedPath = $imagesPath.'cropped/';
		$resizedPath = $imagesPath.'resized/';
		$sourcePath = $imagesPath.'source/';
		
		if (!file_exists(JPATH_SITE . $resizedPath)) {
			mkdir(JPATH_SITE . $resizedPath,0755);
			$resizedCreated = true; 
		}
				
		if (!file_exists(JPATH_SITE . $croppedPath)) {
			mkdir(JPATH_SITE . $croppedPath,0755);
		}

		if (!file_exists(JPATH_SITE . $sourcePath)) {
			mkdir(JPATH_SITE . $sourcePath,0755);
			/* Up until version 1.8.6 the inner workings were simpler, with uploaded images in the
			 * /images/icons and resized images in /images/resized.
			 * With the advent of version 2.0 and its fancy upload functionality, a new folder is necessary:
			 * /images/icons/source where users upload the files.
			 * We can assume that any images present in the /images/icons folder now was uploaded
			 * by the user during her pre-2.0 usage, and should be copied to source:
			 */
			
			$files = scandir($source = JPATH_SITE . $imagesPath);
			$destination = JPATH_SITE . $sourcePath;
			$copiedFiles = 0;
			foreach ($files as $file) {
				if (is_file($source.$file))
				if (copy($source.$file, $destination.$file)) {
					$copiedFiles++;
				}
			}
			$application = JFactory::getApplication();
			if ($copiedFiles) {
				$application->enqueueMessage(sprintf("%s files where copied to %s",$copiedFiles,self::$sourcePath));
			}
		}
	}
	
}