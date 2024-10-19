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


$list = $cmsscanner->get_history(0);

$changes=[0=>_("Detected"), 1=>_("Updated"), 2=>_("Removed"), 3=>_("Vhosts change")];

?>
<h3><?php __("Software Scanner History"); ?></h3>
    <p><?php __("This page show the 6-months history of changes we saw in the hosted software detected on your server, along with their version."); ?></p>

        <p>
        <a href="cmsscanner_admin.php" class="inb"><?php __("View current software list"); ?></a>
</p>
            
<table class="tlist" id="dom_list_table">
<thead>
    <tr>
        <th><?php __("Account"); ?></th>
        <th><?php __("Date of change"); ?></th>
        <th><?php __("Change"); ?></th>
        <th><?php __("Software"); ?></th>
        <th><?php __("Version"); ?></th>
        <th><?php __("Path"); ?></th>
        <th colspan="2"><?php __("Hosted at"); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach($list as $one) { ?>
        <tr class="lst">
            <td>
                <a href="/adm_login.php?id=<?php echo $one['uid']; ?>" title="<?php __("Connect as"); ?>"><?php echo $one['login']; ?></a>
            </td>
            <td>
<?php echo format_date(_('%3$d-%2$d-%1$d %4$d:%5$d'),$one['sdate']); ?>
            </td>
            <td>
                <?php echo $changes[$one['action']]; ?>
            </td>
            <td>
                <?php echo $one['cms']; ?>
            </td>
            <td>
                <?php
                 if ($one['action']==$cmsscanner::ACTION_UPDATE && $one['oldversion']) echo _("Old version: ").$one['oldversion']."<br/>"._("New version: ");
                 echo $one['version']; ?>
            </td>
            <td>
                 <?php ehe($one['folder']); ?>
            </td>
<?php
                  if ($one['action']==$cmsscanner::ACTION_UPDATE && $one['oldvhosts']) {
                      echo "<td>"._("Old vhosts: ")."<br>".nl2br($one['oldvhosts'])."</td>";
                      echo "<td>"._("New vhosts: ")."<br>".nl2br($one['vhosts'])."</td>";
                  } else {
                      echo "<td colspan=\"2\">".$one['vhosts']."&nbsp;</td>";
                  }
    ?>
        </tr>
    <?php } ?>

<?php
                     include_once("foot.php");
?>
