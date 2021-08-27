<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 26-Jun-2014
 * @license    GNU/GPL v2
 */

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
		// interesting to note, in Joomla 2.5, with the extension already installed, 
		// $type will be == 'install' and getParam() is undefined.
		// hence more checks are run:
		if ( ($type == 'update') || ($type == 'install') ) {
			error_log('Updating LittleHelper '.$type);
			// let's see if littlehelper is already installed:
			$manifest = JPATH_ADMINISTRATOR.'/components/com_littlehelper/littlehelper.xml';
			if (file_exists($manifest)) {
				// the manifest contains the string:
				// <version>1.8.6</version>
				$manifestContents = file_get_contents($manifest);
				$matches = array();
				$version = preg_match('@<version>([0-9\.]+)</version>@',$manifestContents,$matches);
				if (count($matches)>1) {
					$oldRelease = $matches[1];
					error_log('  Identified currently installed version: ' . $oldRelease);

					if ( version_compare( $oldRelease, '1.9', '<' ) ) {
						return $this->moveFolders();
					} else {
						//error_log('  Already version 2+, nothing to do');
					}


				} else {
					error_log("  LittleHelper Install ERROR Could not find version in $manifest");
					// still return true, this is not really mandatory!
				}

				// this fails to load... no idea why.
				/*$reg = new JRegistry();
				$reg->loadFile($manifest, 'xml');
				$oldRelease = $reg->get('extension.version');
				*/
	
			} else {
				//error_log('apparently a fresh installation, proceed');
			}
			return true;
		}
	}

	/**
	 * if basepath is not set or it doesn't exist, exit;
	 * else copy the images to the source folder (cannot move: the images could have 
	 * been used elsewhere on the site)
	 * @param unknown $basepath
	 * @return boolean
	 */
	private function moveFolders() {
		$mparams = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $mparams->get('params');
		if (empty($params->favicons_sourcepath)) {
			// no favicons path set, nothing to move!
			// error_log('  favicons_sourcepath is not set yet, nothing to move');
			return true;
		} else {
			$imagesPath = $params->favicons_sourcepath;
		}


		if (empty($imagesPath) || !file_exists(JPATH_SITE . '/images/'. $imagesPath)) {
			return true;
		} 

		$imagesPath = '/images/'.$imagesPath.'/';
		
		$thumbsPath = $imagesPath.'resized/';
		$sourcePath = $imagesPath.'source/';
		
		if (!file_exists(JPATH_SITE . $thumbsPath)) {
			mkdir(JPATH_SITE . $thumbsPath,0755);
			$resizedCreated = true; 
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
			 error_log("  $sourcePath created, now copying files from the root");
			
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
				$application->enqueueMessage(sprintf("Little Helper update: %s files where copied to %s",$copiedFiles,$sourcePath));
			}
		}
	}
	
}
