<?php
/**
 * @package    LittleHelper
 * @author     Riccardo Zorn <code@fasterjoomla.com>
 * @copyright  2011 Riccardo Zorn
 * @license    GNU/GPL v2
 * @link       https://www.fasterjoomla.com/littlehelper
 */
defined('_JEXEC') or die();
die();
/**
 *
 * @package Little Helper
 * @copyright Copyright (C) 2013-2014 Riccardo Zorn. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @author Riccardo Zorn <support@fasterjoomla.com> -
 *         https://www.fasterjoomla.com/littlehelper
 */
?>

Little Helper for Joomla Admin
Homepage: https://www.fasterjoomla.com/littlehelper

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
		/images/icons/resized	contains the final resized images
			and cropped selections
			Do not place any files here, the contents will be overwritten

	2. Scenarios.
		2.a. No icons basepath set: shows button "set default path";
		2.b. Icons basepath set: all folders are created; go to 3;

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

2.0.14 (2014/05/12)
	Correct styles of the statusbar on Joomla 3

2.0.16 (2014/06/20)
	Added preflight for migration of 1.x versions

2.1.1 (2014/06/28)
	Copy a sample image to the newly created folder source
	Fix size of jCrop
	Add extra upload field in the toolbox, moved toolbox to the right;
	Removed cropped folder (just using resized now)
	Fixed robots dist

2.1.2 (2014/06/28)
	First v.2 released
	Added Drag and drop support, Crop, Paste for favicons
	All image editing with Ajax for quick view
	Support for uppercase image extensions

2.1.3 (2014/07/12)
	Fixed some notices and errors

2.1.4 (2014/07/19)
	Styles improved for Joomla 3

2.1.5 (2014/08/04)
	Fixed issue with K2

2.1.6 (2014/08/28)
	Support for Joomla installed in a subfolder of the root
	Prevented a few js error cases

2.2.0 (2014/11/01)
	Support for custom admin login logo;
	Support for administrator favicons; (same as frontend)
	Fix the sitemap syntax in robots.txt
	Fixed status icons on J3;

2.2.1 (2014/11/13)
	Added plugin configuration button to the intro page
	Clean Admin Cache on plugin configuration!!
	Added the clear admin cache button to the main component's interface
	Can now change the interface icon as well

2.2.2 (2014/11/16)
	Fixed robots.txt as per http://joomla-seo.net/Blog/robots-txt-do-not-block-css-and-javascript
	  and added a link to the page in the configuration.
	Added support for jSitemap
	More responsive interface

2.3.1 (2015/02/06)
	Added support for the tags recycle bin

	Added support for slighly modified administrator icons
	(sketchy filter + red rectangle): this way it will be easier to
	discriminate admin from frontend icon

	Bugfix: custom admin icons are not overwritten by the "save configuration" button

2.3.2 (2015/02/10)
	Moved the shell functions to a dedicated helper. This allows to reduce the necessity
	of changing such file, so it can be submitted to Antivirus sites to be added as
	an exclusion.

	Added error handling for the sketchy filter
2.3.3 (2015/02/10)
	Fixed a few error messages

2.3.4 (2015/03/11)
  Corrected minor issue on Joomla 3.4/3.5

2.3.5 (2015/04/07)
  New Send Test Mail feature

2.4.0 (2015/12/18)
  New Exploit and Vulnerability search

2.4.1 (2015/12/21)
  Added JAMSS and more tests

2.4.2 (2015/12/22)
  Removed hundreds of false positives

2.4.3 (2015/12/23)
  Restored PHP 5.3. compatibility

2.4.4 (2016/01/13)
  New round of tests on Joomla 3.5

2.5.1 (2016/04/18)
*   <b>Restored legacy PHP compatibility</b>
	Now compatible with 5.3.10+

2.5.2 (2017/04/30)
   Fine tuned JAMSS to prevent some antivirus false positives

2.5.9 (2018/05/03) 
   We chose to remove the JEDChecker libraries, mostly because 
   we were tired of the random false positive alerts
   from antivirus software.

   This is the Last version with JEDChecker bundled.
   The JEDChecker is in the folder 
   administrator/components/com_littlehelper/admin/libraries/jedchecker/
   administrator/components/com_littlehelper/admin/libraries/rules/

   If you still wish to run the full jedchecker tests 
   using a later version, it will be necessary to install 
   version 2.5.9, then reinstall the current version on top.

   To remove jedchecker from a previous version, you need
   to manually remove the aforementioned folder.

2.6.1 (2018/05/04)
   Added new html5boilerplate .htaccess updated and tested on apache 2.4

2.6.2 (2019/03/10)
	Added an exclusion for compatibility with CForms
	
2.6.3 (2020/07/14)
	Added Fields to the trash cleaning function
	
2.7.0 (2021/05/14) 
	Module in statusbar: 	
		Removed the page refresh after closing the popup
		Made the popup more visible
	Fixed a few graphic glitches with Joomla 3.9.20+
	Improved the Favicons drag and drop 

