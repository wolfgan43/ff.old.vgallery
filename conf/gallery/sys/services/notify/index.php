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
 * @subpackage services
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if(!mod_security_check_session(false) 
	|| get_session("UserNID") == MOD_SEC_GUEST_USER_ID
	|| !(AREA_NOTIFY_SHOW_MODIFY || AREA_SCHEDULE_SHOW_MODIFY)
) {
	prompt_login();
}

$cm->oPage->addContent(null, true, "rel"); 

if(isset($_REQUEST["frmAction"])) {
    $db = ffDB_Sql::factory();
    
    if($_REQUEST["frmAction"] == "hideall") {
        $sSQL = "UPDATE notify_message SET visible = '0'";
        $db->execute($sSQL);
        ffRedirect($_REQUEST["ret_url"]);
    } elseif($_REQUEST["frmAction"] == "clearall") {
        $sSQL = "TRUNCATE TABLE notify_message";
        $db->execute($sSQL);
        ffRedirect($_REQUEST["ret_url"]);
    } elseif($_REQUEST["frmAction"] == "hide" && isset($_REQUEST["title"]) && strlen($_REQUEST["title"])) {
        $sSQL = "UPDATE notify_message SET visible = '0' WHERE title = " . $db->toSql($_REQUEST["title"]);
        $db->execute($sSQL);
    }
}

exit;
