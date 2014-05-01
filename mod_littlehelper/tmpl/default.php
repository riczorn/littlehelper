<?php
/**
 * @version		$Id: default.php 21482 2011-06-08 00:40:30Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	mod_littlehelper
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>
<form class="littlehelper" action="index.php" method="post" id="trashncacheForm" name="trashncacheForm">
<?php echo $toolbar;?>

<input type = "hidden" name = "task" value = "" />
<input type="hidden" name="controller" value="recyclebin" />
<input type = "hidden" name = "option" value = "com_littlehelper" />
</form>
