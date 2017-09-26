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

if (!(AREA_EMAIL_SHOW_MODIFY || AREA_EMAIL_ADDRESS_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "email";
//$oGrid->title = ffTemplate::_get_word_by_code("email");
$oGrid->source_SQL = "SELECT
                            email.*
                        FROM
                            email
                        [WHERE]
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "EmailModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "email_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "email-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("email_name");
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_email_path";
$oField->label = ffTemplate::_get_word_by_code("email_tpl_email_path");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "send";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview?[KEYS]frmAction=send&ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("email_test_send");
//$oButton->image = "sendmail.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "customize";
$oButton->class = "icon ico-edit";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/customize?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("email_customize");
//$oButton->image = "edit.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);


$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("email_preview");
//$oButton->image = "preview.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("email"))); 

if (AREA_EMAIL_ADDRESS_SHOW_MODIFY) {
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "emailAddress";
    //$oGrid->title = ffTemplate::_get_word_by_code("email_address");
    $oGrid->source_SQL = "SELECT
                                email_address.*
                                , CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name
                                        , ' '
                                        , " . CM_TABLE_PREFIX . "mod_security_users.surname
                                        , ' ('
                                        , " . CM_TABLE_PREFIX . "mod_security_users.username
                                        , ')'
                                ) AS username
                            FROM email_address
                                INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = email_address.uid
                            [WHERE]
                            [HAVING]
                            [ORDER]";
    $oGrid->order_default = "name";
    $oGrid->use_search = true;
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/address/modify";
    $oGrid->record_id = "EmailAddressModify";
    $oGrid->resources[] = $oGrid->record_id;

    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "email-ID";
    $oField->base_type = "Number";
    $oField->data_source = "ID"; 
    $oGrid->addKeyField($oField);

    // Campi di ricerca

    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("email_address_name");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "email";
    $oField->label = ffTemplate::_get_word_by_code("email_address_email");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "username";
    $oField->label = ffTemplate::_get_word_by_code("email_address_username");
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
    $oGrid->addContent($oField);

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("email_address")));  
}


function email_on_before_parse_row($component) {
    $email_debug = $component->db[0]->getField("email_debug", "Text", true);
	if(!strlen($email_debug))
		$email_debug = EMAIL_DEBUG;
	
	if($email_debug) {
		$component->grid_buttons["send"]->display = true;
	} else {
		$component->grid_buttons["send"]->display = false;
	}
}
