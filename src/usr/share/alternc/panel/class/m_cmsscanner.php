<?php
/*
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2000-2024 by the AlternC Development Team.
 https://alternc.org/
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
 Purpose of file: the CMSScanner scans for known php programs and tell their version.
 ----------------------------------------------------------------------
*/

class m_cmsscanner {


    const LAST_SCAN_FILE="/var/lib/alternc/panel/cmsscanner-last-scan-time";
    const USER_SCAN_FILE="/var/lib/alternc/panel/cmsscanner-user-scan.json";
    
    /* ----------------------------------------------------------------- */
    /**
     * hook called by the menu class
     * to add menu to the left panel
     */
    function hook_menu() {
        $obj = array(
            'title' => _("Software Scanner"),
            'link' => 'cmsscanner_user.php',
            'pos' => 135,
        );

        return $obj;
    }


    /* ----------------------------------------------------------------- */
    /**
     * list the software found during the last scan for one (or all) user)
     */
    function get_list($user=0) {
        global $db;
        $sql=""; $fields=""; $order="";
        if ($user==0) {
            $fields=",m.login";
            $sql=", membres m  WHERE m.uid=c.uid ";
            $order="m.login, ";
        } else {
            $sql=" WHERE c.uid=".intval($user)." ";            
        }
        $db->query("SELECT c.* $fields FROM cmsscanner c $sql ORDER BY $order c.folder;");
        $cms=[];
        while ($db->next_record()) $cms[]=$db->Record;
        return $cms;
    }

    
    /* ----------------------------------------------------------------- */
    /**
     * list the history of changes for one (or all) users in the last 6 months.
     */
    function get_history($user=0) {
        global $db;
        $sql=""; $fields=""; $order="";
        if ($user==0) {
            $fields=",m.login";
            $sql=", membres m  WHERE m.uid=c.uid ";
            $order="m.login, ";
        } else {
            $sql=" WHERE c.uid=".intval($user)." ";            
        }
        $db->query("SELECT c.* $fields FROM cmsscanner_history c $sql ORDER BY $order c.cdate DESC;");
        $cms=[];
        while ($db->next_record()) $cms[]=$db->Record;
        return $cms;
    }

    
    /* ----------------------------------------------------------------- */
    /**
     * function called when a user want to rescan its folder.
     */
    function please_scan() {
        global $cuid;
        $scan=[];
        if (is_file(self::USER_SCAN_FILE)) {
            $scan=@json_decode(@file_get_contents(self::USER_SCAN_FILE),true);
            if (!is_array($scan)) $scan=[];
        }
        if (!in_array($cuid,$scan)) {
            $scan[]=$cuid;
            file_put_contents(self::USER_SCAN_FILE,json_encode($scan));
        } else {
            return false;
        }
        return true;
    }

    
    /* ----------------------------------------------------------------- */
    /**
     * function called by a crontab every 5 minutes.
     * if one or more accounts requested a scan of its html folder
     * or if the admin requested a daily(1), weekly(2) or monthly(3) scan
     * shall be launched as alterncpanel user.
     */
    function cron_update() {
        global $db;
        // first : shall we scan because cron? 
        $cron = intval(variable_get("cmsscanner_cron"));
        if ($cron!=0 /*&& date("G")==4*/) { // scan by cron done at 4am.
            $acron=[1=>86400, 2=>86400*7, 3=>86400*30];
            if (!is_file(self::LAST_SCAN_FILE) || (time()-filemtime(self::LAST_SCAN_FILE))>$acron[$cron]) {
                $db->query("SELECT uid FROM membres ORDER BY uid;");
                $uids=[];
                while($db->next_record()) $uids[]=$db->Record["uid"];
                foreach($uids as $uid) {
                    $this->do_scan($uid);
                }
                touch(self::LAST_SCAN_FILE);
                // since we scanned **everything** no need to scan any user ;)
                @unlink(self::USER_SCAN_FILE);
                return; 
            }
        }
        // no cron? shall we scan because a user asked for it?
        if (is_file(self::USER_SCAN_FILE) && filesize(self::USER_SCAN_FILE)) {
            // scan requested, do them
            $users=@json_decode(file_get_contents(self::USER_SCAN_FILE),true);
            foreach($users as $one) {
                $this->do_scan($one);
            }
            unlink(self::USER_SCAN_FILE);
        }
    } // cron_scan


    /* ----------------------------------------------------------------- */
    /**
     * scan one user home
     * and store the report in the database.
     * should be called as alterncpanel by the cron function.
     */
    function do_scan($user) {
        global $db;
        $user=intval($user);
        // scan from ALTERNC_HTML/u/user/
        $db->query("SELECT login FROM membres WHERE uid=$user;");
        $db->next_record();
        $login=$db->Record["login"];
        if (!$login) return false;
        $root=realpath(ALTERNC_HTML."/".substr($login,0,1)."/".$login);
        if (!is_dir($root)) return false;
        $out=[];
        exec("cmsscanner ".escapeshellarg($root),$out,$res);
        if ($res) return false;
        $cms=[];
        // fill an array with the CMS found:
        foreach($out as $line) {
            $line=rtrim($line,"\r\n"); 
            if(substr($line,0,6)=="ERROR:") continue;
            if(substr($line,0,6)=="FATAL:") return false;
            // pattern: "cms <space> version <space> path"
            if (preg_match('#^([^ ]+) ([^ ]+) (.*)$#',$line,$mat)) {
                if (substr($mat[3],0,strlen($root))==$root) {
                    // we can have more than one CMS in a SINGLE FOLDER !!
                    // but only 1 VERSION
                    $cms[substr($mat[3],strlen($root))][]=[$mat[1],$mat[2]];
                }
            }
        }

        // list the CMS from the DB TOO
        $db->query("SELECT * FROM cmsscanner WHERE uid=$user;");
        $cur=[];
        while ($db->next_record()) {
            $cur[$db->Record["folder"]][]=[$db->Record["cms"],$db->Record["version"]];
        }

        // here $cms contains the current list & $cur the list in the DB
        // we check both ways to fill history AND to update the DB with minimal update queries
        // because we don't want to overwrite everything all the time...

        foreach($cms as $folder => $l) {
            // does it exist in the DB?
            foreach($l as $cmsi) {
                $found=false; $isupdate=false;
                if (isset($cur[$folder])) {
                    foreach($cur[$folder] as $curi) {
                        if ($cmsi[0]==$curi[0]) {
                            if ($cmsi[1]==$curi[1]) {
                                $found=true; break;
                            } else {
                                // will fill history with a VERSION UPDATE event.
                                $isupdate=true;
                            }
                        }
                    }
                }
                if (!$found) {
                    $db->query("INSERT INTO cmsscanner SET uid=$user, folder='".addslashes($folder)."', cms='".addslashes($cmsi[0])."', version='".addslashes($cmsi[1])."';");
                    
}
            }
        } // array compare from cms to cur (insert)


        foreach($cur as $folder => $l) {
            // does it exist in the DB?
            foreach($l as $curi) {
                $found=false;
                if (isset($cms[$folder])) {
                    foreach($cms[$folder] as $cmsi) {
                        if ($cmsi[0]==$curi[0]) {
                            if ($cmsi[1]==$curi[1]) {
                                $found=true; break;
                            }
                        }
                    }
                }
                if (!$found)
                    $db->query("DELETE FROM cmsscanner WHERE uid=$user AND folder='".addslashes($folder)."' AND cms='".addslashes($curi[0])."' AND version='".addslashes($curi[1])."';");
            }
        } // array compare from cur to cms (delete)
        
        return true;
        // we will update the vhosts pointing there in ANOTHER function (because that's complicated)
    } // do_scan

    


    
} /* Class m_cmsscanner */

