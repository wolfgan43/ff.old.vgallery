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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_share");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ShareConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_share";
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
    $record["name"]
    , true
    , false
    , false
    , $oRecord
);  

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("share_config_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "service_type";
$oField->label = ffTemplate::_get_word_by_code("share_config_service_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("addthis"), new ffData("Add This"))
                            , array(new ffData("sharethis"), new ffData("Share This")) 
                       );
$oField->required = true;  
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "service_account";
$oField->label = ffTemplate::_get_word_by_code("share_config_service_account");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "active";
$oField->label = ffTemplate::_get_word_by_code("share_config_active");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("simple"), new ffData(ffTemplate::_get_word_by_code("share_simple")))
                            , array(new ffData("advanced"), new ffData(ffTemplate::_get_word_by_code("share_advanced"))) 
                       );
$oField->default_value = new ffData("simple");
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addTab("simple");
$oRecord->setTabTitle("simple", ffTemplate::_get_word_by_code("share_config_simple"));

$oRecord->addContent(null, true, "simple"); 
$oRecord->groups["simple"] = array(
                                 "title" => ffTemplate::_get_word_by_code("share_config_simple")
                                 , "cols" => 1
                                 , "tab" => "simple"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "simple_share";
$oField->label = ffTemplate::_get_word_by_code("share_config_simple_share");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "simple");


$oRecord->addTab("advanced");
$oRecord->setTabTitle("advanced", ffTemplate::_get_word_by_code("share_config_advanced"));

$oRecord->addContent(null, true, "advanced"); 
$oRecord->groups["advanced"] = array(
                                 "title" => ffTemplate::_get_word_by_code("share_config_advanced")
                                 , "cols" => 1
                                 , "tab" => "advanced"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_force_absolute";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_force_absolute");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_css";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_css");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_html";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_html");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_jsmain";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_jsmain");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_jsdep";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_jsdep");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");
                              

$cm->oPage->addContent($oRecord);