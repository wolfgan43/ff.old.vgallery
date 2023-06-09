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

if (!AREA_ROUTING_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0) {
	$start_path = substr($cm->real_path_info, 0, strpos($cm->real_path_info, "/params"));
	if($start_path == "")
		$start_path = "/";
		
	$params = str_replace($start_path . "/params", "", $cm->real_path_info);

    $sSQL = "UPDATE `cache_page_alias`
                    SET `status` = " . $db->toSql($_REQUEST["setvisible"]) . "
                    	, `last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
                    WHERE `cache_page_alias`. `ID` = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                    ";
    $db->execute($sSQL);
   
    if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("AliasModify")), true));
    } else {
        ffRedirect($_REQUEST["ret_url"]);
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "AliasModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("alias_modify_title");
$oRecord->src_table = "cache_page_alias";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "host";
$oField->label = ffTemplate::_get_word_by_code("alias_modify_host");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "destination";
$oField->label = ffTemplate::_get_word_by_code("alias_modify_destination");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("alias_modify_status");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "force_primary_domain";
$oField->label = ffTemplate::_get_word_by_code("alias_modify_force_primary_domain");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);   
          
