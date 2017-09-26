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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!AREA_EMAIL_ADDRESS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "EmailAddressModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("email_address");
$oRecord->src_table = "email_address";
$oRecord->addEvent("on_check_after", "EmailAddressModify_on_check_after");


$oField = ffField::factory($cm->oPage);
$oField->id = "email-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("email_address_edit_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("email_address_edit_email");
$oField->required = true;
 $oField->addValidator("email");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "uid";
$oField->label = ffTemplate::_get_word_by_code("email_address_edit_uid");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, username FROM " . CM_TABLE_PREFIX . "mod_security_users";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord); 


// -------------------------
//          EVENTI
// -------------------------
function EmailAddressModify_on_check_after($component, $action) {            
    $db_check = ffDB_Sql::factory();

    switch($action) {
        case "insert":
        
            $sSQL = "SELECT * 
                    FROM email_address 
                    WHERE email = " . $db_check->toSql($component->form_fields["email"]->value);
            $db_check->query($sSQL); 
            if($db_check->nextRecord()) {
                return ffTemplate::_get_word_by_code("email_address_not_unic_value");
                
            }
            break;
        case "update":
            $sSQL = "SELECT * 
                    FROM email_address 
                    WHERE email = " . $db_check->toSql($component->form_fields["email"]->value) . " AND email_address.ID <> " . $db_check->toSql($component->key_fields["email-ID"]->value);
            $db_check->query($sSQL); 
            if($db_check->nextRecord()) {
                return ffTemplate::_get_word_by_code("email_address_not_unic_value");
                
            }
            break;

            default:
    }

    return NULL;
}

