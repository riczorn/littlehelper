<?php
/**
 * LittleHelper external library.
 *
 * adapted for use in pressimages/littlehelper
 *	http://icant.co.uk/articles/phpthumbnails/
 *
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link https://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
	/**	Restituisce il probabile nome del thumbnail o dell'immagine in base alle dimensioni
	 * */

class gimmeImage
{
	public static function getImageInfo($imagePath,$image) {
		$originalImageFilename  = JPATH_SITE.$imagePath.$image;
		if (!file_exists($originalImageFilename)) {
			return NULL;
		}
		if (self::testLibraries()) {

			try {
				if (preg_match("/\.jpg|\.jpeg/i",$image)){$src_img=imagecreatefromjpeg($originalImageFilename);}
				elseif (preg_match("/\.gif/i", $image)){$src_img=imagecreatefromgif($originalImageFilename);}
				elseif (preg_match("/\.png/i", $image)){$src_img=imagecreatefrompng($originalImageFilename);}
				else
					return NULL;
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage('Error decoding file '.$e,'error');
				return NULL;
			}
		}

		if (!isset($src_img)) {
			//error_log('cannot open image '.$originalImageFilename);
			return NULL;
		}
		$res = array();
		$res['width'] =imageSX($src_img) ;
		$res['height'] =imageSY($src_img) ;
		return $res;
	}

/**
 * See that we at least have a image...
 */
	public static function testLibraries() {
		 if(!function_exists('imagecreatefrompng')) {
			if (!DEFINED('MISSINGFUNCTIONERROR')) {
				DEFINE('MISSINGFUNCTIONERROR',1);
				JFactory::getApplication()->enqueueMessage('imagecreatefrompng is not available! This could be an issue with the PHP GD library installation','error');
			}
			return false;
		}
		return true;
	}

/*
	 Function createthumb($name,$filename,$new_w,$new_h)
	 creates a resized image
	 variables:
	 $name		Original filename
	 $filename	Filename of the resized image
	 $new_w		width of resized image
	 $new_h		height of resized image
	 $dest_extension can be: jpg, png: force extension; 'xyxyx': originalImageFilename extension.
	 if $filename (destination filename) is passed as a parameter, the $dest_extension is ignored
	 and $filename's extension is used instead.
	 */
	public static function createThumb($originalImageFilename,$filename,$new_w,$new_h,$dest_extension='png',$x1=0,$y1=0,$w=0,$h=0,$scaleWidth=1)
	{
		if (!self::testLibraries()) {
			return NULL;
		}
		$source_extension = pathinfo($originalImageFilename, PATHINFO_EXTENSION);
		// in caso il nome del file di destinazione non venga passato come parametro, lo genero.
		//$dest_extension = $source_extension;
		if (!empty($filename))
			$dest_extension = pathinfo($filename, PATHINFO_EXTENSION);

		if (empty($dest_extension)) {
				$dest_extension = $source_extension;
		}
		//echo "<br><b>Creo thumbnail</b> da $originalImageFilename <br> a $filename:  $new_w x $new_h <br>";

		if (!file_exists($originalImageFilename)) {
			//echo "il file originale $originalImageFilename non esiste<br>";
			return NULL;
		}
		if (file_exists($filename)) {
			return $filename;
		}

		if (preg_match("/jpg|jpeg/i",$source_extension)){$src_img=imagecreatefromjpeg($originalImageFilename);}
		elseif (preg_match("/gif/i", $source_extension)){$src_img=imagecreatefromgif($originalImageFilename);}
		elseif (preg_match("/png/i", $source_extension)){$src_img=imagecreatefrompng($originalImageFilename);}
		else
		return NULL;

		if (!$src_img) {
			//error_log('gimmeImage: Cannot open image '.$originalImageFilename);
			return false;
		}

		// Dimensioni della thumbnail
		$thumb_h_target = $new_h;
		$thumb_w_target = $new_w;
		$ratio_new = $new_w / $new_h;

		// creo l'immagine per la thumnbail con sfondo trasparente:
		$crop = TRUE;
		$transp = TRUE;

		$dst_img=imagecreatetruecolor($thumb_w_target,$thumb_h_target);
		if ($transp) {
			imagealphablending($dst_img, true);
			$transparent = imagecolorallocatealpha($dst_img,255, 255, 255, 127);
			//imagefilledrectangle($dst_img,0,0,$thumb_w_target,$thumb_h_target,$transparent);
			imagefill($dst_img,1,1,$transparent);
		} else $transparent = imagecolorallocate($dst_img,255,0,0);

		$old_w=imageSX($src_img);
		$old_h=imageSY($src_img);
		if ($x1+$y1+$w+$h==0) { // only if no crop selection was made:

			$ratio_old = $old_w/$old_h;


			if ($crop) {
				// Calcolo le dimensioni effettive dell'immagine ridimensionata che poi andr� applicata alla thumbnail.
				if ($ratio_old>$ratio_new) {
					// l'immagine � landscape
					$thumb_h = $new_h;
					$thumb_w = $new_w * ($ratio_old/$ratio_new);
				} else
				if ($ratio_old<=$ratio_new) {
					$thumb_w = $new_w;
					$thumb_h = $new_h * ($ratio_new/$ratio_old);
				}

				// mi calcolo la posizione x e y per incollare l'immagine ridimensionata
				$dst_img_x_offset = floor(($thumb_w_target - $thumb_w) / 2);
				$dst_img_y_offset = floor(($thumb_h_target - $thumb_h) / 2);
				self::ImageCopyResampledBicubic(
				//imagecopyresampled(
					$dst_img,$src_img,
					$dst_img_x_offset,
					$dst_img_y_offset,
					0,0,
					$thumb_w,$thumb_h,$old_w,$old_h);
			}
			else {
				// Calcolo le dimensioni effettive dell'immagine ridimensionata che poi andr� applicata alla thumbnail.
				if ($old_w > $old_h)
				{
					$thumb_w=$new_w;
					$thumb_h=floor($thumb_w * $old_h / $old_w);
				}
				if ($old_w < $old_h)
				{
					$thumb_h=$new_h;
					$thumb_w=floor($thumb_h * $old_w / $old_h);
				}
				if ($old_w == $old_h)
				{
					if ($new_w>$new_h) {
						$thumb_w=$new_w;
						$thumb_h=floor($thumb_w * $old_h / $old_w);
					} else {
						$thumb_h=$new_h;
						$thumb_w=floor($thumb_h * $old_w / $old_h);
					}
				}

				// mi calcolo la posizione x e y per incollare l'immagine ridimensionata
				$dst_img_x_offset = floor(($thumb_w_target - $thumb_w) / 2);
				$dst_img_y_offset = floor(($thumb_h_target - $thumb_h) / 2);



				self::ImageCopyResampledBicubic(
				//imagecopyresampled(
					$dst_img,$src_img,
					$dst_img_x_offset,
					$dst_img_y_offset,
					0,0,
					$thumb_w,$thumb_h,$old_w,$old_h);
			}
		} else {
			// crop sizes are passed frrom the user:
			$iWidth = $new_w;
			$iHeight = $new_h;
			$thumb_w = $new_w;
			//error_log("x1 $x1, y1 $y1, w $w, h $h, iWidth $iWidth, iHeight $iHeight");
			$scale = $old_w/$scaleWidth;

			$x1 = (int)($x1*$scale);
			$y1 = (int)($y1*$scale);
			$w = (int)($w*$scale);
			$h = (int)($h*$scale);
			// copy and resize part of an image with resampling
			imagecopyresampled($dst_img, $src_img, 0, 0, $x1, $y1, $iWidth, $iHeight, $w, $h);

		}
		if ($thumb_w<$old_w) {
			// the image is smaller, let's sharpen it:
			$matrix = array(array(-1, -1, -1), array(-1, 24, -1), array(-1, -1, -1));
			if (!$transp)
				imageconvolution($dst_img, $matrix, 16, 0);
		} else {
			// the image is larger,

		}
		if ($transp) {
			// this is required by savealpha
			imagealphablending($dst_img, false);
			// save the alpha
			imagesavealpha($dst_img,true);
		}
		// ora tutte le immagini sono convertite in png.
		if (preg_match("/png/",$dest_extension))
		{
			imagepng($dst_img,$filename);
		}
		elseif (preg_match("/gif/",$dest_extension))
		{
			imagegif($dst_img,$filename);
		} else {
			imagejpeg($dst_img,$filename,90);
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);

		return $filename;
	}

	private static function ImageCopyResampledBicubic(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y,
		$dst_w, $dst_h, $src_w, $src_h)  {
		if ($dst_w>$src_w)
			return imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		else
			return imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

}
