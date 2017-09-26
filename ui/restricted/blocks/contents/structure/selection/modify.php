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
if (!AREA_VGALLERY_SELECTION_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

system_ffcomponent_resolve_by_path();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGallerySelectionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "vgallery_fields_selection";
$oRecord->display_required_note = FALSE;

/* Title Block */
system_ffcomponent_set_title(
	null
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
$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "field-detail";
//$oDetail->title = ffTemplate::_get_word_by_code("vgallery_selection_modify_fields");
$oDetail->src_table = "vgallery_fields_selection_value";
$oDetail->order_default = "order";
$oDetail->fields_relationship = array ("ID_selection" => "ID");
$oDetail->display_new = true;
$oDetail->display_delete = true;
$oDetail->min_rows = 1;
//$oDetail->tab = true;
//$oDetail->tab_label = "name";
$oDetail->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oDetail
            , array(
                "resource_id" =>  "vgallery_selection"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_modify_fields_value");
$oField->required = true;
$oDetail->addContent($oField);   

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
