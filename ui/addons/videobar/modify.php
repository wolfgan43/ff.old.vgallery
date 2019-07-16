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

$record = system_ffComponent_resolve_record("module_videobar");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VideobarConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_videobar";
$oRecord->addEvent("on_do_action", "VideobarConfigModify_on_do_action");
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

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("module_videobar_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_videobar_general")
                                 , "cols" => 1
                                 , "tab" => "general"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("videobar_name");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "quantity";
$oField->label = ffTemplate::_get_word_by_code("videobar_quantity");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->multi_pairs =  array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("small"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("large")))
                       ); 
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "mode";
$oField->label = ffTemplate::_get_word_by_code("videobar_mode");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->multi_pairs =  array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("horizzontal"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("vertical")))
                       ); 
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "std_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_std_list");
$oField->extended_type = "Selection";
$oField->multi_pairs =  array (
                            array(new ffData("ytfeed:most_viewed.this_week"), new ffData(ffTemplate::_get_word_by_code("youtube_most_viewed"))),
                            array(new ffData("ytfeed:top_rated.this_week"), new ffData(ffTemplate::_get_word_by_code("youtube_top_rated"))),
                            array(new ffData("ytfeed:google_news"), new ffData(ffTemplate::_get_word_by_code("youtube_google_news"))),
                            array(new ffData("ytfeed:recently_featured"), new ffData(ffTemplate::_get_word_by_code("youtube_recently_featured")))
                       ); 
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "channel_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_channel_list");
$oField->widget = "listgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "search_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_search_list");
$oField->widget = "listgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$cm->oPage->addContent($oRecord);

function VideobarConfigModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
	}	
}
