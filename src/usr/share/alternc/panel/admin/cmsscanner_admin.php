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

if (!$admin->enabled) {
    include("head.php");
    $msg->raise("ERROR", "admin", _("This page is restricted to authorized staff"));
    echo $msg->msg_html_all();
    include('foot.php');
    exit();
}

$updated=(isset($_GET["updated"]) && $_GET["updated"]==1);
if (isset($_POST["action"]) && $_POST["action"]=="rescan") {
    if ($cmsscanner->please_scan(true)) {
        header("Location: /cmsscanner_admin.php?updated=1");
        exit();
    }
}

include("head.php");

$cmsfilter="";
if (isset($_GET["filtercms"]) && $_GET["filtercms"]) {
    $cmsfilter=trim($_GET["filtercms"]);
}

$list = $cmsscanner->get_list(0,$cmsfilter);

function vhost2url($v) {
    $s="";
    $v=explode("\n",$v);
    foreach($v as $u) $s.="<a href=\"http://".$u."\">$u</a><br>";
    return $s;
}

?>
<h3><?php __("Software Scanner"); ?></h3>
<?php
    if ($updated) {
        ?><p class="alert alert-info"><?php __("Your software list will be updated soon. Please come back in a few minutes."); ?></p>
<?php
    }
?>
    <p><?php __("This page show the hosted software that we detected on your server, along with their versions, to help your users to clean their web space and update software that would need it. If you want to rescan your entire server, click the 'rescan now' button. The rescan will take place 5 minutes later and may take a few minutes."); ?></p>

   <p>
     <form method="post" action="cmsscanner_admin.php">
<?php csrf_get(); ?>
       <input type="hidden" name="action" value="rescan">
     <input class="inb" type="submit" value="<?php __("Rescan now"); ?>"/> &nbsp;
<a href="cmsscanner_admin_history.php" class="inb"><?php __("View software history"); ?></a>
     </form>
   </p>

    <p>
    <form method="get" action="cmsscanner_admin.php" id="filter" name="filter">
<select class="inl" name="filtercms" id="filtercms" onchange="document.forms['filter'].submit();">
       <option value=""><?php __("-- Filter on Software Name --"); ?></option>
<?php
           foreach($cmsscanner->cmslist as $cms=>$count) {
               echo "<option value=\"".htmlentities($cms)."\"";
               if ($cmsfilter==$cms) echo " selected=\"selected\"";
               echo ">".$cms." (".$count.")</option>";
           }
?></select><input class="inb" type="submit" name="go" value="<?php __("Filter"); ?>"/></form>
       </p>
       
    
<table class="tlist" id="dom_list_table">
<thead>
    <tr>
        <th><?php __("Account"); ?></th>
        <th><?php __("Software"); ?></th>
        <th><?php __("Version"); ?></th>
        <th><?php __("Path"); ?></th>
        <th><?php __("Hosted at"); ?></th>
        <th><?php __("Scanned date"); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach($list as $one) { ?>
        <tr class="lst">
            <td>
                <a href="/adm_login.php?id=<?php echo $one['uid']; ?>" title="<?php __("Connect as"); ?>"><?php echo $one['login']; ?></a>
            </td>
            <td>
                <?php echo $one['cms']; ?>
            </td>
            <td>
                <?php echo $one['version']; ?>
            </td>
            <td>
                <?php ehe(ALTERNC_HTML."/".substr($one['login'],0,1)."/".$one['login'].$one['folder']); ?>
            </td>
            <td>
                 <?php echo vhost2url($one['vhosts']);   ?>
            </td>
            <td>
<?php echo format_date(_('%3$d-%2$d-%1$d %4$d:%5$d'),$one['sdate']); ?>
            </td>
        </tr>
    <?php } ?>

<?php
                     include_once("foot.php");
?>
