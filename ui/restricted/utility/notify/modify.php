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

if (!Auth::env("AREA_NOTIFY_SHOW_MODIFY")) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "NotifyModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("notify_modify_title");
$oRecord->src_table = "notify_message";

$oRecord->addEvent("on_do_action", "NotifyModify_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_visible");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->required = true;
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_area");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData(basename(VG_WS_ADMIN)), new ffData(basename(VG_WS_ADMIN))),
                            array(new ffData(basename(VG_WS_RESTRICTED)), new ffData(basename(VG_WS_RESTRICTED))), 
                            array(new ffData(basename(VG_WS_ECOMMERCE)), new ffData(basename(VG_WS_ECOMMERCE)))
                       );      
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("information"), new ffData(ffTemplate::_get_word_by_code("information"))),
                            array(new ffData("warning"), new ffData(ffTemplate::_get_word_by_code("warning")))
                       );      
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "count";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_count");
$oField->control_type = "label";
$oField->base_type = "Number";
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_title");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_message");
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->control_type = "textarea";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "url";
$oField->label = ffTemplate::_get_word_by_code("notify_modify_url");
$oRecord->addContent($oField);



$oRecord->additional_fields = array("owner" => new ffData(Auth::get("user")->id, "Number")
                                    , "last_update" =>  new ffData(time(), "Number")
                                    );

$cm->oPage->addContent($oRecord);

function NotifyModify_on_do_action($component, $action) {

	switch($action) {
		case "update":
			$component->form_fields["count"]->setValue(($component->form_fields["count"]->getValue() + 1) );
			break;
		case "hide":
			$db = ffDB_Sql::factory();
            if(isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0) {
                $sSQL = "UPDATE notify_message SET visible = '0' WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                $db->execute($sSQL);
            }
			ffRedirect($_REQUEST["ret_url"]);
		case "show":
			$db = ffDB_Sql::factory();
			$sSQL = "UPDATE notify_message SET visible = '1' WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
			$db->execute($sSQL);
			ffRedirect($_REQUEST["ret_url"]);
		default:
	}
	
	return false;	
	
}

