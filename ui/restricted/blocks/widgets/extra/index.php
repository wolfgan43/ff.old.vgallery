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

if (!AREA_PUBLISHING_SHOW_DETAIL) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

$ID_publishing = $_REQUEST["keys"]["ID"];
$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        break;
    default:
        $src_table = $src_type;
}

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->dialog_action_button = true;
	//$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
	$oGrid->id = "PublishingModifyFields";
	$oGrid->source_SQL = "SELECT publishing_fields.* 
								, CONCAT(" . $src_type . "_type.name, ' - ', " . $src_type . "_fields.name) AS name
	                        FROM publishing_fields
                                INNER JOIN " . $src_type . "_fields ON " . $src_type . "_fields.ID = publishing_fields.ID_fields
                                INNER JOIN " . $src_type . "_type ON " . $src_type . "_type.ID = " . $src_type . "_fields.ID_type
	                        WHERE publishing_fields.ID_publishing = " . $db->toSql($ID_publishing, "Number") . "
	                            [AND] [WHERE] 
	                        [HAVING] 
	                        [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = false;
	$oGrid->use_order = false;
	$oGrid->use_paging = false;
	$oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/publishing/extra/modify";
    $oGrid->addit_insert_record_param = "src=" . $src_type . "&publishing=" . $ID_publishing . "&";
    $oGrid->addit_record_param = "src=" . $src_type . "&publishing=" . $ID_publishing . "&";
	$oGrid->record_id = "PublishingExtraFieldModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->buttons_options["export"]["display"] = false;
	$oGrid->widget_deps[] = array(
	    "name" => "dragsort"
	    , "options" => array(
	          &$oGrid
	        , array(
	            "resource_id" => "publishing_fields"
	            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	        )
	        , "ID"
	    )
	);
	//$oGrid->addEvent("on_before_parse_row", "PublishingModifyFields_on_before_parse_row");


	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_SQL = " `parent_thumb`, `order_thumb`, ID";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_name");
	$oGrid->addContent($oField);	
	
	$cm->oPage->addContent($oGrid);
