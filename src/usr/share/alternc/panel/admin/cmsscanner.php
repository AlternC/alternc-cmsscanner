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
 Purpose of file:
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include("head.php");

/** 
 * Returns the complete hosted domain list : 
 * Took from m_domp.php
 */
function get_domain_list($uid = -1)
{
    global $db;
    $uid = intval($uid);
    $res = [];

    $query = 'SELECT domaine FROM domaines WHERE true';
    $query_args = [];
    if (-1 != $uid) {
        $query .= ' AND compte= ? ';
        array_push($query_args, $uid);
    }
    $query .= ' ORDER BY domaine;';

    $db->query($query, $query_args);
    while ($db->next_record()) {
        $res[] = $db->f('domaine');
    }
    return $res;
}


$members = $admin->get_list();
$content = [];

foreach($members as $member) {
    $path = [];
    $content[$member['login']]=[];

    $mem->su($member['uid']);
    $domains = get_domain_list($member['uid']);
    foreach($domains as $domain) {
        $dom->lock();
        $domain_full = $dom->get_domain_all($domain);
        foreach($domain_full['sub'] as $subdomain) {
            if ('DIRECTORY' == $dom->domains_type_target_values($subdomain['type'])) {
                $path = getuserpath($member['login'])."/".$subdomain['valeur']. " ";

                $out = array();
                exec("/usr/bin/cmsscanner cmsscanner:detect --report=/tmp/cmsreport_".$member['login'].".json --versions ".$path, $out);
            
                $json = file_get_contents("/tmp/cmsreport_".$member['login'].".json");
                $cmsscanner_result = json_decode($json)[0];

                if(empty($cmsscanner_result)) {
                    $content[$member['login']][$subdomain['fqdn']] = [
                        'cms' => 'unknown',
                        'version' => 'unknown',
                        'path' => $path
                    ];
                } else {
                    $content[$member['login']][$subdomain['fqdn']] = [
                        'cms' => $cmsscanner_result->name,
                        'version' => $cmsscanner_result->version,
                        'path' => $cmsscanner_result->path
                    ];
                }

            }
        }
        $dom->unlock();
    }
    $mem->unsu();
}
?>


<h3><?php __("CMS hosted"); ?></h3>


<table class="tlist" id="dom_list_table">
<thead>
    <tr>
        <th><?php __("Member"); ?></th>
        <th><?php __("Fqdn"); ?></th>
        <th><?php __("CMS"); ?></th>
        <th><?php __("Version"); ?></th>
        <th><?php __("Path"); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach($content as $member => $sub_domains) { ?>
    <?php foreach($sub_domains as $fqdn => $sub_domain) { ?>
        <tr class="lst">
            <td>
                <?php echo $member; ?>
            </td>
            <td>
                <?php echo $fqdn; ?>
            </td>            
            <td>
                <?php echo $sub_domain['cms']; ?>
            </td>
            <td>
                <?php echo $sub_domain['version']; ?>
            </td>
            <td>
                <?php echo $sub_domain['path']; ?>
            </td>
        </tr>
    <?php } ?>
<?php } ?>