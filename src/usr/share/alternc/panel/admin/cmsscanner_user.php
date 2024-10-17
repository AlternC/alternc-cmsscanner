<?php
/*
 $Id$
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2002 by the AlternC Development Team.
 http://alternc.org/
 ----------------------------------------------------------------------
 Based on:
 Valentin Lacambre's web hosting softwares: http://altern.org/
 ----------------------------------------------------------------------
 LICENSE

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License (GPL)
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 To read the license please visit http://www.gnu.org/copyleft/gpl.html
 ----------------------------------------------------------------------
 Original Author of file: 
 Purpose of file: the CMSScanner scans for known php programs and tell their version.
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include("head.php");

$updated=false;
if (isset($_POST["action"]) && $_POST["action"]=="rescan") {
    if ($cmsscanner->please_scan()) {
        $updated=true;
    }
}

$list = $cmsscanner->get_list($cuid);

?>
<h3><?php __("Software Scanner"); ?></h3>
<?php
    if ($updated) {
        ?><p class="alert alert-info"><?php __("Your software list will be updated soon. Please come back in a few minutes."); ?></p>
<?php
    }
?>
    <p><?php __("This page show the hosted software that we detected on your account, along with their versions. If you want to rescan your account, click the 'rescan now' button. The rescan will take place 5 minutes later."); ?></p>

   <p>
     <form method="post" action="cmsscanner_user.php">
<?php csrf_get(); ?>
       <input type="hidden" name="action" value="rescan">
       <input class="inb" type="submit" value="<?php __("Rescan now"); ?>"/>
     </form>
   </p>
     
<table class="tlist" id="dom_list_table">
<thead>
    <tr>
        <th><?php __("Software"); ?></th>
        <th><?php __("Version"); ?></th>
        <th><?php __("Path"); ?></th>
        <th><?php __("Scanned date"); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach($list as $one) { ?>
        <tr class="lst">
            <td>
                <?php echo $one['cms']; ?>
            </td>
            <td>
                <?php echo $one['version']; ?>
            </td>
            <td>
                 <a href="bro_main.php?R=<?php echo urlencode($one['folder']); ?>"/><?php ehe($one['folder']); ?></a>
            </td>
            <td>
<?php echo format_date(_('%3$d-%2$d-%1$d %4$d:%5$d'),$one['sdate']); ?>
            </td>
        </tr>
    <?php } ?>

<?php
                     include_once("foot.php");
?>
