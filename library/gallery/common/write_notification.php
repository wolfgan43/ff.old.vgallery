<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function write_notification($title, $message, $type, $area = "", $url = "", $visible = true, $owner = "-1", $last_update = null, $controls = null, $send_mail = true) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT ID
                FROM `notify_message` 
                WHERE `area` = " . $db->toSql($area) . "
                    AND `title` = " . $db->toSql($title) . "
                    AND `message` = " . $db->toSql($message) . "
                    AND `type` = " . $db->toSql($type) . "
                    AND `url` = " . $db->toSql($url);
    $db->query($sSQL);
    if($db->nextRecord()) {
        $sSQL = "UPDATE 
                    `notify_message` 
                SET 
                    `visible` = IF(`visible` = 1, 1, " . $db->toSql($visible, "Number") . ")
                    , `owner` = " . $db->toSql($owner, "Number") . " 
                    , `last_update` = " . $db->toSql(($last_update === null ? time() : $last_update)) . " 
                    , `count` = `count` + 1
                    , `controls` = " . ($controls === null 
                                            ? "''"
                                            : $db->toSql($controls) 
                                        ) . "
                WHERE 
                    `area` = " . $db->toSql($area) . "
                    AND `title` = " . $db->toSql($title) . "
                    AND `message` = " . $db->toSql($message) . "
                    AND `type` = " . $db->toSql($type) . "
                    AND `url` = " . $db->toSql($url);
        $db->execute($sSQL);
    } else {
        $sSQL = "INSERT INTO  
                    `notify_message` 
                ( 
                    `ID` , 
                    `area` , 
                    `title` , 
                    `message` , 
                    `type` , 
                    `url` ,
                    `visible` ,
                    `owner` ,
                    `last_update` , 
                    `count`, 
                    `controls`
                )
                VALUES
                (
                    ''
                    , " . $db->toSql($area) . " 
                    , " . $db->toSql($title) . " 
                    , " . $db->toSql($message) . " 
                    , " . $db->toSql($type) . " 
                    , " . $db->toSql($url) . " 
                    , " . $db->toSql($visible, "Number") . " 
                    , " . $db->toSql($owner, "Number") . " 
                    , " . $db->toSql(($last_update === null ? time() : $last_update)) . " 
                    , " . $db->toSql("1", "Number") . " 
                    , " . ($controls === null 
                            ? "''"
                            : $db->toSql($controls) 
                    ) . "
                )";
        $db->execute($sSQL);
    }
    
    if($send_mail && defined("ENABLE_EMAIL_NOTIFICATIONS") && strlen(ENABLE_EMAIL_NOTIFICATIONS) && check_function("process_mail")) {
        if((!strlen($area) && strpos(ENABLE_EMAIL_NOTIFICATIONS, "general") !== false) 
            || (strlen($area) && strpos(ENABLE_EMAIL_NOTIFICATIONS, $area) !== false)
        ) {
            if(substr($title, 0, 1) == "_") {
                $real_title = ffTemplate::_get_word_by_code(substr($title, 1));
            } else {
                $real_title = $title;
            }

            $fields["general"]["area"] = (strlen($area) ? $area : ffTemplate::_get_word_by_code("generic"));
            $fields["general"]["title"] = $real_title;
            $fields["general"]["message"] = $message;
            $fields["general"]["url"] = "http://" . DOMAIN_INSET . $url;
            
            if(strlen(EMAIL_DEBUG)) {
                $from["name"] = EMAIL_DEBUG;
                $from["email"] = EMAIL_DEBUG;
            } else {
                $from = null;
            }
            
            process_mail(email_system("system notification"), "", "CMS " . ucfirst($type) . " " . $real_title, null, $fields, $from, null, null, false, false, true);
        }
    }
}
