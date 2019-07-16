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

if (!Auth::env("AREA_VGALLERY_TYPE_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

$ID_type = $_REQUEST["ID_type"];
if($ID_type > 0)
{
	$sSQL_string = " AND vgallery_type_group.ID_type = " . $db->toSql($ID_type, "Number");
    if(check_function("get_vgallery_type_group")) {
		get_vgallery_type_group($ID_type, "backoffice");
    }
}

$cm->oPage->addContent(null, true, "grptype"); 

$oGrid_thumb = ffGrid::factory($cm->oPage);
$oGrid_thumb->full_ajax = true;
$oGrid_thumb->id = "vgalleryTypeGroupThumb";
$oGrid_thumb->source_SQL = "SELECT * 
					FROM vgallery_type_group 
					WHERE vgallery_type_group.`type` = 'thumb'
						" . $sSQL_string . "
	                [AND] [WHERE] [HAVING] [ORDER]";
$oGrid_thumb->order_default = "ID";
$oGrid_thumb->use_search = FALSE;
$oGrid_thumb->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid_thumb->addit_insert_record_param = "type=thumb&" . ($ID_type > 0 ? "ID_type=" . $ID_type . "&" : "");
$oGrid_thumb->addit_record_param = "type=thumb&";
$oGrid_thumb->record_id = "vgalleryTypeGroupModify";
$oGrid_thumb->resources[] = $oGrid_thumb->record_id;
$oGrid_thumb->addEvent("on_before_parse_row", "vgalleryTypeGroup_on_before_parse_row");
$oGrid_thumb->widget_deps[] = array(
        "name" => "labelsort"
        , "options" => array(
              &$oGrid_thumb
            , array(
                "resource_id" => "vgallery_type_group_backoffice"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
        )
    );

$oGrid_thumb->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid_thumb
        , array(
            "resource_id" => "vgallery_type_group_backoffice"
            , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
        )
        , "ID"
    )
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`order`, ID";
$oGrid_thumb->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_name");
$oGrid_thumb->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = "";
$oButton->display_label = false;
$oGrid_thumb->addGridButton($oButton);
                        
$cm->oPage->addContent($oGrid_thumb, "grptype", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type_group_thumb"))); 


$oGrid_detail = ffGrid::factory($cm->oPage);
$oGrid_detail->full_ajax = true;
$oGrid_detail->id = "vgalleryTypeGroupDetail";
$oGrid_detail->source_SQL = "SELECT * 
					FROM vgallery_type_group 
					WHERE vgallery_type_group.`type` = 'detail'
						" . $sSQL_string . "
	                [AND] [WHERE] [HAVING] [ORDER]";
$oGrid_detail->order_default = "ID";
$oGrid_detail->use_search = FALSE;
$oGrid_detail->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid_detail->addit_insert_record_param = "type=detail&" . ($ID_type > 0 ? "ID_type=" . $ID_type . "&" : "");
$oGrid_detail->addit_record_param = "type=detail&";
$oGrid_detail->record_id = "vgalleryTypeGroupModify";
$oGrid_detail->resources[] = $oGrid_detail->record_id;
$oGrid_detail->addEvent("on_before_parse_row", "vgalleryTypeGroup_on_before_parse_row");
$oGrid_detail->widget_deps[] = array(
        "name" => "labelsort"
        , "options" => array(
              &$oGrid_detail
            , array(
                "resource_id" => "vgallery_type_group_detail"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
        )
    );

$oGrid_detail->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid_detail
        , array(
            "resource_id" => "vgallery_type_group_detail"
            , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
        )
        , "ID"
    )
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`order`, ID";
$oGrid_detail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_name");
$oGrid_detail->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = "";
$oButton->display_label = false;
$oGrid_detail->addGridButton($oButton);
                        
$cm->oPage->addContent($oGrid_detail, "grptype", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type_group_detail"))); 

$oGrid_backoffice = ffGrid::factory($cm->oPage);
$oGrid_backoffice->full_ajax = true;
$oGrid_backoffice->id = "vgalleryTypeGroupbackOffice";
$oGrid_backoffice->source_SQL = "SELECT * 
					FROM vgallery_type_group 
					WHERE vgallery_type_group.`type` = 'backoffice'
						" . $sSQL_string . "
	                [AND] [WHERE] [HAVING] [ORDER]";
$oGrid_backoffice->order_default = "ID";
$oGrid_backoffice->use_search = FALSE;
$oGrid_backoffice->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid_backoffice->addit_insert_record_param = "type=backoffice&" . ($ID_type > 0 ? "ID_type=" . $ID_type . "&" : "");
$oGrid_backoffice->addit_record_param = "type=backoffice&";
$oGrid_backoffice->record_id = "vgalleryTypeGroupModify";
$oGrid_backoffice->resources[] = $oGrid_backoffice->record_id;
$oGrid_backoffice->addEvent("on_before_parse_row", "vgalleryTypeGroup_on_before_parse_row");
$oGrid_backoffice->user_vars["group"] = $arrGroup;
$oGrid_backoffice->widget_deps[] = array(
        "name" => "labelsort"
        , "options" => array(
              &$oGrid_backoffice
            , array(
                "resource_id" => "vgallery_type_group_backoffice"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
        )
    );

$oGrid_backoffice->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid_backoffice
        , array(
            "resource_id" => "vgallery_type_group_backoffice"
            , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
        )
        , "ID"
    )
);
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`order`, ID";
$oGrid_backoffice->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_name");
$oGrid_backoffice->addContent($oField); 
                        
$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = "";
$oButton->display_label = false;
$oGrid_backoffice->addGridButton($oButton);

$cm->oPage->addContent($oGrid_backoffice, "grptype", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type_group_backoffice"))); 

function vgalleryTypeGroup_on_before_parse_row($component) {
	$display_delete = true;
	if(isset($component->user_vars["group"])) {
		if(is_array($component->user_vars["group"]) && array_key_exists(ffCommon_url_rewrite($component->db[0]->getField("name", "Text", true)), $component->user_vars["group"])) {
			$display_delete = !$component->user_vars["group"][ffCommon_url_rewrite($component->db[0]->getField("name", "Text", true))]["is_system"];
		}
	}

	$component->display_delete_bt = $display_delete;

	if(isset($component->grid_buttons["visible"])) {
		if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
	        $component->grid_buttons["visible"]->action_type = "submit"; 
	        $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . "setvisible=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	        if($_REQUEST["XHR_CTX_ID"]) {
	            $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	            $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	        }   
		} else {
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
	        $component->grid_buttons["visible"]->action_type = "submit";     
	        $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . "setvisible=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	        if($_REQUEST["XHR_CTX_ID"]) {
	            $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	            $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	        }    
		}
	}
}
