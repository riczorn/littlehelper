<?php
use Joomla\Application\Cli\Output\Stdout;
/**
 * LittleHelper component helper.
 * Favicon helper: bridge to the thumnbail and php-ico libraries, expose the needed functionalities
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

class LittleHelperHelperFavicon
{
	public static $extension = 'com_littlehelper';
	public static $favicons = array(
			144=>false,114=>false,72=>false,57=>false,48=>false,32=>false,24=>false,16=>false);
	public static $master = false;
	public static $imagesPath = "";
	public static $thumbsPath = "";
	public static $sourcePath = "";
	public static $templatePath = "";
	public static $templatePathAdmin = "";
	public static $imagesPathIsSet = false;
	public static $params = null;
	
	/**
	 * Initialize all paths: source, cropped, resized;
	 * Create the folders if necessary;
	 * Handle 1.8.x->2.x upgrade
	 */
	public static function initPaths() {
		$mparams = JComponentHelper::getParams( 'com_littlehelper' );
		$params = $mparams->get('params');
		if ($params == null) {
			$params = new stdClass();
		}
		if (empty($params->favicons_sourcepath))
			$params->favicons_sourcepath='';
		else
			self::$imagesPathIsSet = true;
		if (empty($params->favicons_forcepreview))
			$params->favicons_forcepreview=true;
		self::$params = $params;
		$basepath = "/images/".$params->favicons_sourcepath.'/';
		self::$imagesPath = $basepath;
		self::$thumbsPath = $basepath.'resized/';
		self::$sourcePath = $basepath.'source/';
		
		$templatePathAdmin = "/administrator/templates/".JFactory::getApplication()->getTemplate()."/";
		self::$templatePathAdmin = $templatePathAdmin;
	
		$db	= JFactory::getDBO();
		$sql = 'SELECT template FROM #__template_styles WHERE client_id=0 AND home=1';
		$db->setQuery($sql);
		$template = $db->loadResult();
	
		self::$templatePath ="/templates/$template/";
		
		if (!self::$imagesPathIsSet) return; // otherwise we'd create folders under /images !
		
		if (!file_exists(JPATH_SITE . self::$imagesPath)) {
			mkdir(JPATH_SITE . self::$imagesPath,0755);
		}
		
		if (!file_exists(JPATH_SITE . self::$thumbsPath)) {
			mkdir(JPATH_SITE . self::$thumbsPath,0755);
		}
		
		if (!file_exists(JPATH_SITE . self::$sourcePath)) {
			mkdir(JPATH_SITE . self::$sourcePath,0755);
		}	
		// let's see if there are any images in the source folder, just to be on the safe side:
		jimport('joomla.filesystem.folder');
		$files = JFolder::files(JPATH_SITE.self::$sourcePath,'.',false,false,array('^.+','~$','.html$'));
		if (!count($files)) {
			// no images, copy a sample there:
			copy(JPATH_ADMINISTRATOR.'/components/com_littlehelper/assets/images/fasterjoomla.png',JPATH_SITE . self::$sourcePath.'/sample.png');
			JError::raiseWarning(1040,JText::_("COM_LITTLEHELPER_FILE_ERROR_COPIED_SAMPLE"));
		}
	}	
	
	/**
	 * Main initialization: get images, find their sizes, build the list of destination 
	 * images with all the sizes, create missing thumbnails.
	 * @return boolean|multitype:boolean
	 */
	public static function getImages() {
		self::initPaths();
		if (!self::$imagesPathIsSet)
			return array();
		$images = array();
		$basepath = self::$thumbsPath;
		jimport('joomla.filesystem.folder');
		$files = JFolder::files(JPATH_SITE.$basepath,'.',false,false);
		// $files could be empty, but findSizeBestMatches will add an original if necessary
		foreach ($files as $image) {
			$newImage = self::getImageInfo($basepath,$image);
			$images[$newImage->size] = $newImage;
		}
		if (!self::findSizeBestMatches($images)) {
			JError::raiseWarning(107, JText::_("COM_LITTLEHELPER_FAVICON_NOIMAGES"));
			return false;
		}
		// now we have self::$favicons with original images and possibly some gaps.
		self::createMissingThumbnails();
		return self::$favicons;
	}
	
	/**
	 * Return all the headers that should be added to the template <HEAD> section.
	 */
	public static function getHead($removeComments = false) {
		$headDeclarations = array();
		$random = "";
		if (self::$params->favicons_forcepreview)
			$random = "?random=".rand(1000,1000000);
		$headDeclarations[] = '<link href="/templates/template_path/favicon.ico'.$random.'" rel="shortcut icon" type="image/vnd.microsoft.icon" />';
		if (!$removeComments)
			$headDeclarations[] = '<!-- For third-generation iPad with high-resolution Retina display: -->';
		$headDeclarations[] = '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/templates/template_path/apple-touch-icon-144x144-precomposed.png" />';
		if (!$removeComments)
			$headDeclarations[] = '<!-- For iPhone with high-resolution Retina display: -->';
		$headDeclarations[] = '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/templates/template_path/apple-touch-icon-114x114-precomposed.png" />';
		if (!$removeComments)
			$headDeclarations[] = '<!-- For first- and second-generation iPad: -->';
		$headDeclarations[] = '<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/templates/template_path/apple-touch-icon-72x72-precomposed.png" />';
		if (!$removeComments)
			$headDeclarations[] = '<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->';
		$headDeclarations[] = '<link rel="apple-touch-icon-precomposed" href="/templates/template_path/apple-touch-icon-precomposed.png" />';

		$template = LittleHelperHelperFavicon::$templatePath;
		
		$head = str_replace("/templates/template_path/", $template, join("\n", $headDeclarations));
		return $head;
	}

	
	/**
	 * Create the multiresolution favicon using PHP-ICO
	 * @param unknown_type $sideAdmin
	 * @return string|boolean
	 */
	public static function createFavicon($sideAdmin=false) {
		require( dirname(dirname( __FILE__ )) . '/libraries/php-ico-master/class-php-ico.php' );
		$faviconNameAdmin = JPATH_SITE.self::$templatePathAdmin."favicon.ico";
		$faviconNameSite = JPATH_SITE.self::$templatePath."favicon.ico";
		$ico_lib = new PHP_ICO();
		foreach(array(16,24,32,48) as $size) {
			$image = self::$favicons[$size];
			$ico_lib->add_image( $image->fullpath, array( array( $size, $size ) ) );
		}
		if ($result = $ico_lib->save_ico( $faviconNameSite )) {
			return JText::_("COM_LITTLEHELPER_FAVICON_FAVICON_SAVEDTO")." " .$faviconNameSite;
		} else {
			JError::raiseWarning(110,JText::_("COM_LITTLEHELPER_FAVICON_FAVICON_ERROR_SAVE")." $faviconNameSite");
			return false;
		}
	}
	
	/**
	 * Gather some image info with GD: 
	 * $imagePath is relative to the root;
	 * @param unknown_type $image
	 */
	public static function getImageInfo($imagePath, $image) {
		if (!file_exists(JPATH_SITE.$imagePath.$image)) {
			//JError::raiseWarning(100, JText::_("COM_LITTLEHELPER_FILE_ERROR_MISSING_SOURCE") . $imagePath.$image);
			error_log("COM_LITTLEHELPER_FILE_ERROR_MISSING_SOURCE:" . $imagePath.$image);
			return false;
		}
		require_once(dirname(dirname(__FILE__)).'/libraries/thumbnails.php');
		$info = gimmeImage::getImageInfo($imagePath, $image);
		$new = new stdClass();
		$new->name = $image;
		$new->path = $imagePath;
		$new->fullpath = JPATH_SITE.$imagePath.$image;
		$new->size = $info['width'];
		$new->height = $info['height'];
		$new->width = $info['width'];
		$new->resized = false;
		$icon_desc = 'MULTI';
		if ($info['width']>50) $icon_desc = $info['width'];
		$desc = JText::_("COM_LITTLEHELPER_FAVICON_DESC_".$icon_desc);
		if ($desc == "COM_LITTLEHELPER_FAVICON_DESC_".$icon_desc )
			$desc = JText::_("COM_LITTLEHELPER_FAVICON_DESC_CUSTOM");
		$new->description =$desc;
		return $new;
	}
	
	/**
	 * Iterate through the currently available source images, and find the best 
	 * matches.  If more than one match is found, the other is ignored and a warning is issued.
	 * 
	 * @param unknown_type $images
	 */
	private static function findSizeBestMatches($images) {
		// determine which are the best candidates for our favicon sizes.
		if (empty($images)) {
			// so there were no cropped images. Let's see if we can use the last uploaded file:
			// 
			if (! self::$master = self::getLastSourceUploaded())
				return false;// and getImages returns "No images found" error.
			else {
				// an image was found
				// error_log(var_export(self::$master,true));
			}
		}
		$widestImageKey = 0;
		
		foreach($images as $key=>$image) {
			if (array_key_exists($image->size, self::$favicons)) {
				self::$favicons[$image->size] = $image;
			}
			if ($widestImageKey<$image->size) { 
				// $key is also the image width ($image->size)
				$widestImageKey = $key;
			}
		}
		
		// now $widestImageKey must contain a valid index:
		if ($widestImageKey>0) {
			self::$master = $images[$widestImageKey];
		} else {
			if (!self::$master)
				JError::raiseWarning(108, JText::_("COM_LITTLEHELPER_FAVICON_ERROR_READ_IMAGEINFO"));
		}
		return true;
	}
	
	/**
	 * Returns the name of the last file uploaded. This is useful when we have no crop information and 
	 * we need to arbitrarily choose one of the sources.
	 * @return boolean|Ambigous <boolean, stdClass>
	 */
	private static function getLastSourceUploaded() {
		$files = scandir($source = JPATH_SITE . self::$sourcePath);
		if (count($files)==0) return false;
		$arr = array();
		foreach($files as $filename) {
			if (is_file($source.$filename)) {
				if (filemtime($source.$filename) === false) continue;
				$dat = date("YmdHis", filemtime($source.$filename));
				$arr[$dat] = $filename;
			}
		}
		ksort($arr);
		$last = array_pop($arr);
		
		return self::getImageInfo(self::$sourcePath,$last);
	}
	
	/**
	 * All relevant images are in self::$favicons.
	 * The largest has the index 'master';
	 * Let's see which ones we're missing, and create a thumbnail
	 */
	 private static function createMissingThumbnails() {
	 	// do we have a master image?
	 	if (!self::$master) {
	 		JError::raiseWarning(109, JText::_("COM_LITTLEHELPER_FAVICON_ERROR_NO_MASTER_IMAGE"));
	 		return false;
	 	}
	 	 
	 	if (self::$master->size<144) {
	 		JError::raiseWarning(110,JText::_("COM_LITTLEHELPER_FAVICON_WARN_MASTER_LOWRES"));
	 	}
	 	
	 	// the actual images resize:
		foreach(self::$favicons as $key=>$favicon) {
			if (!$favicon) {
				/* this item wasn't assigned yet. so we have to create
				 * one using $widestImageKey and $key to determine the desired size */
				self::$favicons[$key] = self::createThumb($key); // $key == $size
				self::$favicons[$key]->resized = true;
			}
		}
	}
	
	/**
	 * Wrapper for external thumbnail library
	 * New in v.2.0: Now supports explicit cropping
	 * @param unknown_type $size
	 * @return boolean|Ambigous <boolean, stdClass>
	 */
	public static function createThumb($size,$folder=false,$x1=0,$y1=0,$w=0,$h=0,$scaleWidth=0) {
		$source = JPATH_SITE. self::$master->path . self::$master->name;
		require_once(dirname(dirname(__FILE__)).'/libraries/thumbnails.php');
		if (!$folder)
			$folder = self::$thumbsPath;
		$thumbfilename = JPATH_SITE. $folder . $size . "x". $size . ".png";
			
		if ($source==$thumbfilename) {
			$tempSource = JPATH_SITE.'/cache/tempsource.png';
			if (file_exists($tempSource)) {
				@unlink($tempSource);
			}
			if (copy($source,$tempSource)) {
				$source = $tempSource;
			}
			
		}
		if (!self::testFilesPermissions($source, $thumbfilename, true)) {
			return false;
		}
		$resized = gimmeImage::createThumb(
				$source,
				$thumbfilename,
				$size, $size,'png',$x1,$y1,$w,$h,$scaleWidth);
		// return a imageInfo record for this new image:
		return self::getImageInfo($folder, $size . "x". $size . ".png");
	}
	
	/**
	 *  Common routine checks files existance and permissions before create/copy,
	 *  invokes it in htaccess model.
	 * @param unknown_type $source
	 * @param unknown_type $destination
	 */
	private static function testFilesPermissions($source,$destination,$removeDest=false) {
		require_once(JPATH_COMPONENT."/helpers/htaccess.php");
		return LittleHelperHelperHtaccess::testFilesPermissions($source,$destination,$removeDest);
	}
}