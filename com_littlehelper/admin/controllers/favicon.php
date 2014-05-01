<?php
/**
 * Create, copy, manage favicons and apple precomposed icons for your site.
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerFavicon extends JControllerForm
{

	protected $default_view = 'favicon';

	function __construct() {
		parent::__construct();

	}

	/**
	 * This is the main view
	 * @see JController::display()
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * create default folders (icons temporary folder...
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function createdefault($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->createDefault()) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
					$message);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();
	}

	/**
	 * Generate the favicons
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function publish($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->publish()) {
			$message .= "; " . $this->getModel()->saveConfiguration();
			$this->getModel()->setPluginState(true);
				
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
					$message);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();
	}

	/**
	 * This is invoked after an upload. It defaults to showing the default favicon view.
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function clearResized($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->clearResized()) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
					$message);
		} else {
				
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();
	}

	/**
	 * Disables the Little Helper plugin.
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function disablePlugin($cachable = false, $urlparams = false) {
		$this->getModel()->setPluginState(false);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
	}

	/**
	 * This is sent from the favicon view when a user saves an image or a crop:
	 * if it contains an image, the image is uploaded to "images/icons/source"
	 * if a crop area is set, a cropped version is saved to images/icons/cropped;
	 * then the resized images are deleted.
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function saveImageCrop($cachable = false, $urlparams = false) {
		error_log('saveImageCrop');
		if (! $this->testUploadErrors() ) { // which handles writing the response by itself
			if ($sourceImage = $this->saveImage()) {
				
				$croppedImage = $this->saveCrop($sourceImage);
				$imageRelativePath = str_replace(JPATH_SITE,'',$croppedImage);
				$this->respond("Image uploaded successfully",
					0,
					'<img class="previewimg" src="'.
					$imageRelativePath ."?random=".rand(1000,1000000).
					'" />');
			}
		} else {
			error_log('some errors found!');
		}
		exit;
	}

	private function file_upload_error_message($error_code) {
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk';
			case UPLOAD_ERR_EXTENSION:
				return 'File upload stopped by extension';
			default:
				return 'Unknown upload error';
		}
	}
	
	/**
	 * Will return true if an error is found!
	 * @return boolean
	 */
	private function testUploadErrors() {
		$input = JFactory::getApplication()->input;
		if (empty($_FILES) && empty($_POST)) {
			// this happens when POST_MAX_SIZE (from php.ini) is exceeded
			// a file too large is pasted from the clipboard;
			$this->respond("Error POST_MAX_SIZE exceeded ",25007);
			return true;
		}
		else if (isset($_FILES['image'])) {
			if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
				// a standard php error, which is returned;
				//throw new Exception(file_upload_error_message($_FILES['file']['error']));
				//trigger_error(file_upload_error_message($_FILES['file']['error']),E_USER_ERROR);
				//throw new UploadException($_FILES['file']['error']);
				$this->respond($this->file_upload_error_message($_FILES['image']['error']),$_FILES['image']['error']);
				return true;
			} else {
				// the upload was successful;
				return false;
					
			}
		} else {
			// no file was uploaded
			if ($input->getBool('noimage',false) && ($src = $input->getString('imagesrc'))) {
				// no image uploaded purposefully, but an image was selected for cropping:
				error_log('No image was uploaded, only crop requested;');
				return false;
			} else {
				$this->respond('Error: I could not retrieve the uploaded file',0);
				return true;
			}
		}
	}

	/**
	 * Uploads an image (which could have been dropped or pasted to the box) and then try to open it;
	 * The save the local path.
	 * @return boolean|string
	 */
	private function saveImage() {
		error_log('saveImage');
		$input = JFactory::getApplication()->input;
		$maxFileSize = 15*1024*1024;
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		LittleHelperHelperFavicon::initPaths();
		$destinationFolder = LittleHelperHelperFavicon::$sourcePath;
		$input = JFactory::getApplication()->input;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_FILES) {
			
				// if no errors and size less than 250kb
				if ($_FILES['image']['error']) { // this shouldn't happen as it's handled before this function is
					// invoked
					error_log('error uploading: '.$_FILES['image']['error']);
					return false;
				}
				//if  $_FILES['image']['size'] < 1 * 1024
				//error_log(var_export($_FILES['image'],true));
				if (is_uploaded_file($_FILES['image']['tmp_name']) 
						&& $_FILES['image']['size'] < $maxFileSize) {
			
					// new unique filename
					$sourceFileName = JPATH_SITE.$destinationFolder .pathinfo( $_FILES['image']['name'],
						PATHINFO_FILENAME). rand(100,1000) .pathinfo( $_FILES['image']['name'],
						PATHINFO_EXTENSION);
			
					// move uploaded file into cache folder
					move_uploaded_file($_FILES['image']['tmp_name'], $sourceFileName);
			
					// change file permission to 644
					
					@chmod($sourceFileName, 0644);
					
					if (file_exists($sourceFileName) && filesize($sourceFileName) > 0) {
						$aSize = getimagesize($sourceFileName); // try to obtain image info
						if (!$aSize) {
							error_log('Cannot read image: '. ini_get('upload_tmp_dir') .$sourceFileName);
							//@unlink($sourceFileName);
							$this->respond('Cannot read uploaded image');
							return false;
						}
			
						// check for image type
						$ext = "";
						switch($aSize[2]) {
							case IMAGETYPE_JPEG:
								$ext = ".jpg";
								break;
							case IMAGETYPE_GIF:
								$ext = ".gif";
								break;
							case IMAGETYPE_PNG:
								$ext = ".png";
								break;
							default:
								@unlink($sourceFileName);
								error_log('Invalid file: '. $sourceFileName);
								$this->respond('Invalid file deleted: '. $sourceFileName);
								return false;
						}
						if (strpos($sourceFileName,$ext)===false) {
							// the image does not contain an extension!
							if (rename($sourceFileName, $sourceFileName . $ext)) {
								$sourceFileName = $sourceFileName . $ext;
							}
						}
						return $sourceFileName;
					}
				} else {
					$this->respond('File too large per script settings',25008);
					return false;
				}
			
			} else {
				//error_log('no files found but if I\'m here then imagesrc is set');
				if ($input->getBool('noimage',false) && ($src = $input->getString('imagesrc'))) {
					// no image uploaded, but an image was selected for cropping:
					if (stripos($src,'http://')===0 || stripos($src,'https://')===0) {
						
						$src = JPATH_SITE.'/'.ltrim(str_ireplace(JUri::root(false), "", urldecode($src)),"/");
					}
					return $src;
				} else {
					$this->respond('No files found');
					return false;
				}
			}
		}
		error_log('ERROR favicon::saveImage no conditions matched');
		return false;
	}
	
	/**
	 * 
	 * @param unknown_type $imageFile
	 * @return string
	 */
	private function saveCrop($imageFile) {
		$input = JFactory::getApplication()->input;
		$targetSize = $input->getCmd('targetSize',144);
		
		
		$targetBelow = $input->getCmd('targetBelow',"true")=="true";
		
		if ($targetSize == "all") {
			$targetSize = 144;
			$targetBelow = true;
		}
		//error_log('NOW DELETE THUMBS below targetSize?'.var_export($targetBelow,true));
		if ($targetSize<16)
			$targetSize = 144;
		error_log('$targetSize '. $targetSize);
		if ($targetBelow) {
			$this->deleteFilesBelow($targetSize,$imageFile);
		}
		
		$iWidth = $iHeight = $targetSize; // desired image result dimensions
		$filename =  pathinfo($imageFile, PATHINFO_FILENAME) . "." . pathinfo($imageFile, PATHINFO_EXTENSION);
		$filepath = rtrim(dirname($imageFile),"/")."/";
		$filepath = str_replace(JPATH_SITE,"",$filepath);
		LittleHelperHelperFavicon::$master = LittleHelperHelperFavicon::getImageInfo(
			$filepath,$filename);
		
		
		$scaleWidth = $input->getInt('scalewidth');
		
		$x1 = $input->getInt('x1');
		$y1 = $input->getInt('y1');
// 		$x2 = (int)($_POST['x2']*$scale);
// 		$y2 = (int)($_POST['y2']*$scale);
		$w = $input->getInt('w');
		$h = $input->getInt('h');
		
		$cropped = LittleHelperHelperFavicon::createThumb(
				$targetSize,
				LittleHelperHelperFavicon::$croppedPath,
				$x1,$y1,$w,$h,$scaleWidth);
		// we can assume the $imageFile exists and is an image; this was all tested in saveImage!
		return $cropped->path . $cropped->name;
	}
	
	/**
	 * Delete all the cropped files at the resolution provided and below
	 * @param unknown_type $size
	 * @param unknown_type $excludedFile: if the source is one of the files, we need to keep it!
	 */
	private function deleteFilesBelow($size,$excludedFile) {
		error_log('12');
		require_once(JPATH_COMPONENT."/helpers/favicon.php");
		foreach(LittleHelperHelperFavicon::$favicons as $key=>$favicon) {
			error_log('key '.$key . "; size:$size; ");
			if ($key<=$size) {
				if (file_exists($file = JPATH_SITE . LittleHelperHelperFavicon::$croppedPath . $key . "x" . $key . ".png")) {
					if ($file==$excludedFile) {
						error_log('File '.$file.' not deleted as it\'s the original to crop');
					} else {
						if (unlink($file))
							error_log('13 deleted '.$file);
						else 
							error_log('13 Error, File NOT deleted '.$file);
					}
				}
			}
		}
	}

	private function respond($message,$error=25007,$content="") {
		$respondObj = new stdClass();
		$respondObj->error = $error;
		$respondObj->message = $message;
		$respondObj->content = $content;
		echo json_encode($respondObj);
		error_log('R:'.$message.'; err:'.$error);
	}
}
