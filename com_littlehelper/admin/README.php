<?php
die();
/**
 * @package     Little Helper
 * @copyright   Copyright (C) 2013-2014 Riccardo Zorn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Riccardo Zorn <support@tmg.it> - http://www.fasterjoomla.com/littlehelper
 */

?>

Little Helper for Joomla Admin
Homepage: http://www.fasterjoomla.com/littlehelper

--------------------------------------
Version 1.7.5 (2013/04/28) 
	First published on the JED
	
--------------------------------------
Version 1.7.6 (2013/06/05)
	Improved compatibility with Joomla 3.1
	
--------------------------------------
Version	1.8.1 (2013/06/22) 
	- Complete compatibility with Joomla 3.0 / 3.1
	- All update sequences are accounted for (even coming from Joomla 1.5)
	- Compatible with newer sh404 versions
	- Added preview of trash contents
--------------------------------------
Version 1.8.2 (2013/07/03)
    - Added toolbar images for Joomla 3.x
--------------------------------------
Version 1.8.3 (2013/08/06)
    - Added support for PHP 5.2
--------------------------------------
Version 1.8.4 (2013/09/18)
    - Fixed a bug preventing empty trash in J2.5.14
--------------------------------------
Version 1.8.5 (2013/09/29)
    - Fixed a bug throwing Layout default not found - 500 Error on Joomla 3.1.2 - 3.1.4

--------------------------------------
Version 2.0.0 (2013/10/2)
	- Changed the internal folder structure /images/icons to separately store source and cropped images
	- New super-fancy icons drop, paste, crop and upload feature
	- New option to immediately update the favicons in the browser
	
		1. Refactoring.
		In order to adjust for the new features, the folder structure has changed.
		/images/icons		used to contain the uploaded images
		/images/icons/resized	contains the final resized images

		In 2.0 we introduced two folders:
		/images/icons/source	where uploaded and uncut files are stored
			Any images in the /images/icons, should images/icons/source 
			not exist, are copied to images/icons/source.
		/images/icons/cropped	where temporary selections are stored; these are 
			generated as the user chooses crop regions;
			at least one must be filled in automatically during upload.
		/images/icons/resized	still contains the final resized images
			Do not place any files here, the contents will be overwritten

	2. Scenarios.
		2.a. No icons basepath set: shows button "set default path";
		2.b. Icons basepath set: all folders are created; go to 3;
		2.c. Icons present in /cropped: those are used for generating resized images; 
			go to 3;
		2.d. Icons NOT present in /cropped: the first in /source is used; go to 3;

	3. Manage (Crop/Upload)
		3.   Loads with the largest cropped image available OR with the latest uploaded;
		Screen:
			Top List (/images/icons/sources)
			Crop area (drop / crop / upload) + Old file upload button
			Icon thumbs

		3.a. Choose original from top list
		3.b. Drop / Upload / Paste
		3.c. Select (Crop)
		3.d. Choose destination size (after choosing the affected icons will be highlighted)
		ALL IMAGE INTERACTION IS AJAX

		3.e. The crop pane has two buttons: Save and Save for res.
		3.e.1.	Save: deletes all cropped images, and saves the image as 144x144 (largest); 
				This will be used to generate the others;
		3.e.2.  Save for res: selecting a resolution, a particular crop can be saved
				for a specific size only.  This allows to customize the images
				indiviudally based on their size;

		The original images are in /images/icons/sources;
		The cropped images are in /images/icons/cropped;
		The resized thumbnails in the bottom view are in /images/icons/resized;

		The button "Save and Enable" copies these images to the template's folder; thus
		any activity here is not reflected on the site untile Save and enable is pressed.

	4. Backend Ajax:
		4.a. Save, if it contains a new image then store in /cropped
		4.b. If no crop (From file upload box - old way): simply load as new source;
		4.c. If crop info is present: 
			4.c.1. crop and copy to /cropped
			4.c.2. in the DOM update the image.src of the cropper
					(ajax, no reload) so we go back to Crop:

	5. More new features:
		5.a. Ajax update of the thumbnails; This is invoked after any upload / image crop
			to allow the user to continue working on the new images immediately;
		5.b. Force preview option.  When saving the plugin data, it is possible to add
			a random parameter after the favicon source; this ensures that no reload
			is necessary to preview the icons.
	
	Refactoring:
		favicon.generate->favicon.publish

2.0.9 (2014/04/15)
	Styles fixed for Joomla 3 to reduce the space the take up on screen;
	Improved support for jQuery framework when other extensions are using it;
		
2.0.11 (2014/04/15)
	Folders are now created with 0755;
	
