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
check_function("system_ffcomponent_set_title");
    
system_ffcomponent_resolve_by_path("src");

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
); 
 
if(check_function("get_schema_fields_by_type")) {
    $src = get_schema_fields_by_type($src_type, "vgallery");

	//Override Pathinfo
	if($src_type != $src["type"]) {
		$_REQUEST["keys"]["permalink"] .= "-" . $src_type;
		
		$cm->real_path_info = $_REQUEST["keys"]["permalink"];
	}
}

if(isset($_REQUEST["repair"])) {
	$sSQL = "UPDATE vgallery_fields SET
				display_name = REPLACE(name, '-', ' ')
			WHERE display_name = '' AND name <> ''";
	$db->execute($sSQL);

	$sSQL = "SELECT vgallery_fields.*
			FROM vgallery_fields
			WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrVgalleryField[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		} while($db->nextRecord());

		if(is_array($arrVgalleryField) && count($arrVgalleryField)) {
			foreach($arrVgalleryField AS $ID_field => $smart_url) {
				$sSQL = "UPDATE vgallery_fields SET
							name = " . $db->toSql($smart_url) . "
						WHERE vgallery_fields.ID = " . $db->toSql($ID_field, "Number");
				$db->execute($sSQL);				
			}
		}
	}
	
	$sSQL = "UPDATE anagraph_fields SET
				display_name = REPLACE(name, '-', ' ')
			WHERE display_name = '' AND name <> ''";
	$db->execute($sSQL);

	$sSQL = "SELECT anagraph_fields.*
			FROM anagraph_fields
			WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrAnagraphField[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		} while($db->nextRecord());

		if(is_array($arrAnagraphField) && count($arrAnagraphField)) {
			foreach($arrAnagraphField AS $ID_field => $smart_url) {
				$sSQL = "UPDATE anagraph_fields SET
							name = " . $db->toSql($smart_url) . "
						WHERE anagraph_fields.ID = " . $db->toSql($ID_field, "Number");
				$db->execute($sSQL);				
			}
		}
	}
}


$record = system_ffComponent_resolve_record($src["type"] . "_type", array(
	"name" => "IF(" . $src["type"] . "_type.display_name = ''
				, REPLACE(" . $src["type"] . "_type.name, '-', ' ')
				, " . $src["type"] . "_type.display_name
			)"
	, "public" => null
));

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setpublic"]) && $_REQUEST["keys"]["ID"] > 0) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "UPDATE " . $src["type"] . "_type 
            SET " . $src["type"] . "_type.public = " . $db->toSql($_REQUEST["setpublic"], "Number") . "
            WHERE " . $src["type"] . "_type.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);

    //if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
    //} else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    //}
}   

 if($_REQUEST["frmAction"] == "clone" && $_REQUEST["keys"]["ID"] > 0) {
	$clone_schema = array(
					$src["type"] => array(
							$src["type"] . "_type" => array("compare_key" => "ID", "return_ID" => "")
							, $src["type"] . "_fields" => array("compare_key" => "ID_type", "use_return_ID" => "ID_type") 
						)
					);

	if(check_function("clone_by_schema"))
		clone_by_schema($_REQUEST["keys"]["ID"], $clone_schema, "vgtype");

	//if($_REQUEST["XHR_CTX_ID"]) {
		die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
	//} else {
	//	die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
		//ffRedirect($_REQUEST["ret_url"]);
	//}
}    
 
 
 

if(check_function("get_update_by_service") && !set_interface_for_copy_by_service($src["type"] . "_type", "VGalleryTypeModify", (isset($_REQUEST["dir"]) ? array("is_dir_default" => $_REQUEST["dir"]) : null))) {
	$sSQL = "SELECT ID FROM vgallery_fields_data_type WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrDataType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
			$arrDataTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
		} while($db->nextRecord());
	}

	$sSQL = "SELECT ID, name FROM check_control WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrControlType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
			$arrControlTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
		} while($db->nextRecord());
	}

	$sSQL = "SELECT ID, name FROM extended_type WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrExtType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
			$arrExtTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
		} while($db->nextRecord());
	}

	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "VGalleryTypeModify";
	$oRecord->resources[] = $oRecord->id;
	$oRecord->src_table = $src["type"] . "_type";
	$oRecord->auto_populate_edit = true;
	$oRecord->populate_edit_SQL = "SELECT " . $src["type"] . "_type.*
										, IF(display_name = ''
											, REPLACE(name, '-', ' ')
											, display_name
										) AS display_name
									FROM " . $src["type"] . "_type
									WHERE " . $src["type"] . "_type.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("next");
	//$oRecord->buttons_options["cancel"]["display"] = false;
	$oRecord->display_required_note = FALSE;
	$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
	$oRecord->insert_additional_fields["tags_in_keywords"] = new ffData("1", "Number");
	$oRecord->addEvent("on_done_action", "VGalleryTypeModify_on_done_action");
    $oRecord->addEvent("on_do_action", "VGalleryTypeModify_on_do_action");
	$oRecord->user_vars["src"] = $src;
    
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

	/***********
	*  Group General
	*/

	if(!$_REQUEST["keys"]["ID"]) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->widget = "slug";
		$oField->slug_title_field = "display_name";
		$oField->container_class = "hidden";
		$oRecord->addContent($oField);	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_name";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_name");
		$oField->class = "title-page";
		$oField->required = true;
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "schemaorg";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_schemaorg");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		);
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "is_dir_default";
		$oField->container_class = "is_dir_default";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_is_dir");
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1");
		$oField->unchecked_value = new ffData("0");
		$oRecord->addContent($oField); 
	} else {
		$oRecord->tab = true;
	    $label_width = 8;
	    /**
	    * Group Thumb
	    */
		$group_field = "thumb";
		$group_thumb = "list-thumb";
		$oRecord->addContent(null, true, $group_thumb); 
	        
   		$oRecord->groups[$group_thumb] = array(
											"title" => Cms::getInstance("frameworkcss")->get("th-large", "icon-tag") . ffTemplate::_get_word_by_code("vgallery_type_" . $group_field)
											, "tab" => $group_thumb
										 );    
	    
	    $oGrid_thumb = ffGrid::factory($cm->oPage);
	    $oGrid_thumb->ajax_addnew = !$disable_dialog;
	    $oGrid_thumb->ajax_edit = !$disable_dialog;
	    $oGrid_thumb->ajax_delete = true;
	    $oGrid_thumb->ajax_search = true; 
	    $oGrid_thumb->id = "VGalleryFieldsThumb";
	    $oGrid_thumb->source_SQL = "SELECT " . $src["type"] . "_fields.* 
    							, " . $src["type"] . "_fields.enable_" . $group_field . " AS `visible`
	                            , " . $src["type"] . "_fields.order_" . $group_field . " AS `order`
	                            , " . $src["type"] . "_fields.parent_" . $group_field . " AS `parent`
								, " . $src["type"] . "_fields.display_view_mode_" . $group_field . " AS `display_view_mode`
								, " . $src["type"] . "_fields.enable_" . $group_field . "_empty AS `enable_empty`
	                            , " . $src["type"] . "_fields.enable_" . $group_field . "_label AS `enable_label`
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src["type"] . "_fields.ID_" . $group_field . "_htmltag
			                        ), " . $db->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src["type"] . "_fields.ID_label_" . $group_field . "_htmltag
			                        ), " . $db->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag_label
	                            , IFNULL(
                            		(SELECT DISTINCT
		                                " . CM_TABLE_PREFIX . "showfiles_modes.name
		                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
		                            WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . $src["type"] . "_fields.settings_type_" . $group_field . "
									), " . $db->toSql(ffTemplate::_get_word_by_code("file_fullsize")) . "
	                            ) AS settings_type
	                            , IF(" . $src["type"] . "_fields.custom_" . $group_field . "_field
                            		, 1
                            		, 0
	                            ) AS `custom_field`
								, '' AS grid_label
								, '' AS grid_field
	                            , IF(" . $src["type"] . "_fields.fixed_pre_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `pre_content`
	                            , IF(" . $src["type"] . "_fields.fixed_post_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `post_content`
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                        FROM " . $src["type"] . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src["type"] . "_fields.ID_extended_type
	                        WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                        [AND] [WHERE] 
	                        [ORDER]";	
	    $oGrid_thumb->order_default = "ID";

	    $oGrid_thumb->record_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]";
	    $oGrid_thumb->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]?sel=thumb";
	    $oGrid_thumb->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/add";
	    $oGrid_thumb->record_id = "FieldModify";
	    $oGrid_thumb->resources[] = $oGrid_thumb->record_id;
	    $oGrid_thumb->resources[] = "fields";
	    $oGrid_thumb->resources[] = "fields" . $group_field;
	    $oGrid_thumb->use_search = false;
	    $oGrid_thumb->display_labels = false;
	    $oGrid_thumb->use_paging = false;
	    $oGrid_thumb->widget_deps[] = array(
	        "name" => "dragsort"
	        , "options" => array(
	              &$oGrid_thumb
	            , array(
	                "resource_id" => $src["type"] . "_fields_" . $group_field
	                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
	            )
	            , "ID"
	        )
	    );
	    $oGrid_thumb->display_edit_bt = true;
	    $oGrid_thumb->display_edit_url = false;
	    $oGrid_thumb->buttons_options["export"]["display"] = false;
	    $oGrid_thumb->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_thumb->user_vars["src"] = $src;
	    $oGrid_thumb->user_vars["limit"] = $group_field;

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "vgalleryTypeGroup";
		$oButton->aspect = "link"; 
		$oButton->class = "showall thumb";
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_show_all");
		$oButton->icon = Cms::getInstance("frameworkcss")->get("plus-square-o", "icon-tag", "lg"); 
		$oButton->jsaction = "javascript:void(0);";
	    $oGrid_thumb->addActionButtonHeader($oButton);
	    
	    // Campi chiave
   		$oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oField->order_SQL = "`order`, ID";
	    $oGrid_thumb->addKeyField($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "display_name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $oGrid_thumb->record_url . "?limit=source";
	    $oField->url_ajax = true; 
	    $oGrid_thumb->addContent($oField, false);
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "parent";
	    $oField->label = ffTemplate::_get_word_by_code("vgallery_field_group");
	    $oField->setWidthLabel($label_width);
	    $oGrid_thumb->addContent($oField, false, 1, 2);
	    
	    $oField = ffField::factory($cm->oPage);
		$oField->id = "enable_lastlevel";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_lastlevel");
	    $oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("to_detail_content"))),
	                                array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("to_large_image")))
	                           );
	    $oField->multi_select_one = false;
	    $oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 1, 2);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_view_mode";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_display_view_mode");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 1, 2); 
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_empty";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_empty");
	    $oField->encode_entities = false;
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 1, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_limit";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_thumb_limit");
		$oField->base_type = "Number";
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 1, 2); 
		    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_label");
	    $oField->encode_entities = false;
	    $oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 2, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "htmltag_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_htmltag_label");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 2, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_grid_label");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 2, 2); 
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "settings_type";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_settings_type");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2); 
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "htmltag";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_htmltag");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2); 
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_field";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_grid_field");
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2); 
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "custom_field";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_custom_field");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
	    $oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2);     
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "pre_content";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_pre_content");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
   		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2); 
		    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "post_content";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_post_content");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
		$oField->setWidthLabel($label_width);
		$oGrid_thumb->addContent($oField, false, 3, 2); 
	    
	    $oRecord->addContent($oGrid_thumb, $group_thumb);
	    $cm->oPage->addContent($oGrid_thumb);
	    
	    /**
	    * Group Detail
	    */
		$group_field = "detail";
		$group_detail = "list-detail";
		$oRecord->addContent(null, true, $group_detail); 

   		$oRecord->groups[$group_detail] = array(
											"title" => Cms::getInstance("frameworkcss")->get("file", "icon-tag") . ffTemplate::_get_word_by_code("vgallery_type_" . $group_field)
											, "tab" => $group_detail
										 );    
	    
	    $oGrid_detail = ffGrid::factory($cm->oPage);
	    $oGrid_detail->ajax_addnew = !$disable_dialog;
	    $oGrid_detail->ajax_edit = !$disable_dialog;
	    $oGrid_detail->ajax_delete = true;
	    $oGrid_detail->ajax_search = true; 
	    $oGrid_detail->id = "VGalleryFieldsDetail";
	    $oGrid_detail->source_SQL = "SELECT " . $src["type"] . "_fields.* 
    							, " . $src["type"] . "_fields.enable_" . $group_field . " AS `visible`
	                            , " . $src["type"] . "_fields.order_" . $group_field . " AS `order`
	                            , " . $src["type"] . "_fields.parent_" . $group_field . " AS `parent`
								, " . $src["type"] . "_fields.display_view_mode_" . $group_field . " AS `display_view_mode`
	                            , " . $src["type"] . "_fields.enable_" . $group_field . "_empty AS `enable_empty`
	                            , " . $src["type"] . "_fields.enable_" . $group_field . "_label AS `enable_label`
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src["type"] . "_fields.ID_" . $group_field . "_htmltag
			                        ), " . $db->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src["type"] . "_fields.ID_label_" . $group_field . "_htmltag
			                        ), " . $db->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag_label
	                            , IFNULL(
                            		(SELECT DISTINCT
		                                " . CM_TABLE_PREFIX . "showfiles_modes.name
		                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
		                            WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . $src["type"] . "_fields.settings_type_" . $group_field . "
									), " . $db->toSql(ffTemplate::_get_word_by_code("file_fullsize")) . "
	                            ) AS settings_type
	                            , IF(" . $src["type"] . "_fields.custom_" . $group_field . "_field
                            		, 1
                            		, 0
	                            ) AS `custom_field`
								, '' AS grid_label
								, '' AS grid_field
	                            , IF(" . $src["type"] . "_fields.fixed_pre_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `pre_content`
	                            , IF(" . $src["type"] . "_fields.fixed_post_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `post_content`
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                        FROM " . $src["type"] . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src["type"] . "_fields.ID_extended_type
	                        WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                        [AND] [WHERE] 
	                        [ORDER]";	
	    $oGrid_detail->order_default = "ID";
	    $oGrid_detail->record_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]";
	    $oGrid_detail->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]?sel=detail";
	    $oGrid_detail->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/add";
	    $oGrid_detail->record_id = "FieldModify";
	    $oGrid_detail->resources[] = $oGrid_detail->record_id;
	    $oGrid_detail->resources[] = "fields";
	    $oGrid_detail->resources[] = "fields" . $group_field;
	    $oGrid_detail->use_search = false;
	    $oGrid_detail->display_labels = false;
	    $oGrid_detail->use_paging = false;
	    $oGrid_detail->widget_deps[] = array(
	        "name" => "dragsort"
	        , "options" => array(
	              &$oGrid_detail
	            , array(
	                "resource_id" => $src["type"] . "_fields_" . $group_field
	                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
	            )
	            , "ID"
	        )
	    );
	    $oGrid_detail->display_edit_bt = true;
	    $oGrid_detail->display_edit_url = false;
	    $oGrid_detail->buttons_options["export"]["display"] = false;
	    $oGrid_detail->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_detail->user_vars["src"] = $src;
	    $oGrid_detail->user_vars["limit"] = $group_field;

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "vgalleryTypeGroup";
		$oButton->aspect = "link"; 
		$oButton->class = "showall detail";
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_show_all");
		$oButton->icon = Cms::getInstance("frameworkcss")->get("plus-square-o", "icon-tag", "lg"); 
		$oButton->jsaction = "javascript:void(0);";
	    $oGrid_detail->addActionButtonHeader($oButton);
	    	    
	    // Campi chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oField->order_SQL = "`order`, ID";
	    $oGrid_detail->addKeyField($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "display_name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $oGrid_detail->record_url . "?limit=source";
	    $oField->url_ajax = true; 
	    $oGrid_detail->addContent($oField, false);
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "parent";
	    $oField->label = ffTemplate::_get_word_by_code("vgallery_field_group");
	    $oField->setWidthLabel($label_width);
	    $oGrid_detail->addContent($oField, false, 1, 2);
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_view_mode";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_display_view_mode");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 1, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_empty";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_empty");
	    $oField->encode_entities = false;
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 1, 2); 

	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_label");
	    $oField->encode_entities = false;
	    $oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 2, 2);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "htmltag_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_htmltag_label");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 2, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_label";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_grid_label");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 2, 2); 	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "settings_type";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_settings_type");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "htmltag";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_htmltag");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_field";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_grid_field");
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2); 	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "custom_field";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_custom_field");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
	    $oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2);     

		$oField = ffField::factory($cm->oPage);
		$oField->id = "pre_content";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_pre_content");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2); 
		    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "post_content";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_post_content");
		$oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->multi_pairs = array (
	                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
	                           );
	    $oField->multi_select_one = false;
		$oField->setWidthLabel($label_width);
		$oGrid_detail->addContent($oField, false, 3, 2); 	
		
	    $oRecord->addContent($oGrid_detail, $group_detail);
	    $cm->oPage->addContent($oGrid_detail);
	    
	    
		/**
	    * Group BackOffice
	    */
		$group_field = "backoffice";
		$group_backoffice = "list-" . $group_field;
		$oRecord->addContent(null, true, $group_backoffice); 
   		$oRecord->groups[$group_backoffice] = array(
											"title" => Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src["type"] . "_fields_" . $group_field)
											, "tab" => $group_backoffice
										 );  

 		$oGrid_backoffice = ffGrid::factory($cm->oPage);
	    $oGrid_backoffice->ajax_addnew = !$disable_dialog;
	    $oGrid_backoffice->ajax_edit = !$disable_dialog;
	    $oGrid_backoffice->ajax_delete = true;
	    $oGrid_backoffice->ajax_search = true; 
	    $oGrid_backoffice->id = "VGalleryFieldsBackOffice";
	    $oGrid_backoffice->source_SQL = "SELECT " . $src["type"] . "_fields.* 
	                            , " . $src["type"] . "_fields.order_" . $group_field . " AS `order`
	                            , IFNULL(
                            		(SELECT DISTINCT vgallery_type_group.name
                            			FROM vgallery_type_group
                            			WHERE vgallery_type_group.ID = " . $src["type"] . "_fields.ID_group_" . $group_field . "
                            		), " . $db->toSql(ffTemplate::_get_word_by_code("nothing")) . "
	                            ) AS `parent`
								, IFNULL(
									(SELECT DISTINCT check_control.name 
									FROM check_control 
									WHERE check_control.ID = " . $src["type"] . "_fields.ID_check_control
									), " . $db->toSql(ffTemplate::_get_word_by_code("no")) . " 
								) AS check_control
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                            , IF(vgallery_fields_data_type.name = 'table.alt'
									, CONCAT(" . $src["type"] . "_fields.data_source, " . $src["type"] . "_fields.data_limit)
									, " . $src["type"] . "_fields.ID
								) AS group_data
	                        FROM " . $src["type"] . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src["type"] . "_fields.ID_extended_type
                        		INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = " . $src["type"] . "_fields.ID_data_type
	                        WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                        [AND] [WHERE] 
							GROUP BY group_data
	                        [ORDER]";	
	    $oGrid_backoffice->order_default = "ID";
	    $oGrid_backoffice->record_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]";
	    $oGrid_backoffice->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/[name_VALUE]?sel=backoffice";
	    $oGrid_backoffice->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . ($cm->real_path_info ? $cm->real_path_info : "/field") . ($src["type"] != "vgallery" ? "-" . $src["type"] : "") . "/add";
	    $oGrid_backoffice->record_id = "FieldModify";
	    $oGrid_backoffice->resources[] = $oGrid_backoffice->record_id;
	    $oGrid_backoffice->resources[] = "fields";
	    $oGrid_backoffice->resources[] = "fields" . $group_field;
	    $oGrid_backoffice->use_search = false;
	    $oGrid_backoffice->display_labels = false;
	    $oGrid_backoffice->use_paging = false;
	    $oGrid_backoffice->widget_deps[] = array(
	        "name" => "dragsort"
	        , "options" => array(
	              &$oGrid_backoffice
	            , array(
	                "resource_id" => $src["type"] . "_fields_" . $group_field
	                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
	            )
	            , "ID"
	        )
	    );
	    $oGrid_backoffice->display_edit_bt = true;
	    $oGrid_backoffice->display_edit_url = false;
	    $oGrid_backoffice->buttons_options["export"]["display"] = false;
	    $oGrid_backoffice->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_backoffice->user_vars["src"] = $src;
	    $oGrid_backoffice->user_vars["limit"] = $group_field;

	    // Campi chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oField->order_SQL = "`order`, ID";
	    $oGrid_backoffice->addKeyField($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "display_name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $oGrid_thumb->record_url . "?limit=source";
	    $oField->url_ajax = true; 
	    $oGrid_backoffice->addContent($oField, false);
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "parent";
	    $oField->label = ffTemplate::_get_word_by_code("vgallery_field_group");
	    $oField->setWidthLabel($label_width);
	    $oGrid_backoffice->addContent($oField, false, 1, 2);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "require";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_required");
		$oField->encode_entities = false;
		$oGrid_backoffice->addContent($oField, false, 1, 2);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "check_control";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_check_control");
		$oField->setWidthLabel($label_width);
		$oGrid_backoffice->addContent($oField, false, 1, 2);   
		
		if($src["field"]["lang"])
		{
			$sSQL = "SELECT * 
					FROM " . FF_PREFIX . "languages
					WHERE " . FF_PREFIX . "languages.status > 0";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$oField = ffField::factory($cm->oPage);
				$oField->id = "disable_multilang";
				$oField->label = ffTemplate::_get_word_by_code("vgallery_field_disable_multilang");
				$oField->encode_entities = false;
			    $oField->setWidthLabel($label_width);
				$oGrid_backoffice->addContent($oField, false, 2, 2);   
			}	
		}
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_tip";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_enable_tip");
		$oField->encode_entities = false;
		$oField->setWidthLabel($label_width);
		$oGrid_backoffice->addContent($oField, false, 2, 2);   
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "permission";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_permission");
		$oField->setWidthLabel($label_width);
		$oGrid_backoffice->addContent($oField, false, 2, 2);
		
	    $oRecord->addContent($oGrid_backoffice, $group_backoffice);
	    $cm->oPage->addContent($oGrid_backoffice);


	
		/***********
		*  Group SEO
		*/	
		$group_field = "seo";
		$group_seo = "list-" . $group_field;
		$oRecord->addContent(null, true, $group_seo); 
   		$oRecord->groups[$group_seo] = array(
											"title" => Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src["type"] . "_fields_" . $group_field)
											, "tab" => $group_seo
										 );  

		$oField = ffField::factory($cm->oPage);
		$oField->id = "smart_url";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_smart_url");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->required = true;
		/*$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;			
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";		
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.enable_smart_url > 0
				ORDER BY " . $src["type"] . "_fields.enable_smart_url, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_seo);	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "meta_description";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_meta_description");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->required = true;
		/*$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;			
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.meta_description > 0
				ORDER BY " . $src["type"] . "_fields.meta_description, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_seo);	
		
		if($src["type"] == "vgallery") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "strip_stop_words";
			$oField->container_class = "stopwords-field";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_strip_stop_words");
			$oField->base_type = "Number";
			$oField->control_type = "checkbox";
			$oField->checked_value = new ffData("1", "Number");
			$oField->unchecked_value = new ffData("0", "Number");
			$oField->default_value = new ffData("0", "Number");
			$oRecord->addContent($oField, $group_seo);	

			$oField = ffField::factory($cm->oPage);
			$oField->id = "tags_in_keywords";
			$oField->container_class = "tags-in-keywords-field";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_tags_in_keywords");
			$oField->base_type = "Number";
			$oField->control_type = "checkbox";
			$oField->checked_value = new ffData("1", "Number");
			$oField->unchecked_value = new ffData("0", "Number");
			$oField->default_value = new ffData("1", "Number");
			$oRecord->addContent($oField, $group_seo);	

			$oField = ffField::factory($cm->oPage);
			$oField->id = "rule_meta_title";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_rule_meta_title");
			$oRecord->addContent($oField, $group_seo);	

			$oField = ffField::factory($cm->oPage);
			$oField->id = "rule_meta_description";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_rule_meta_description");
			$oRecord->addContent($oField, $group_seo);				
		}
		
		/***********
		*  Group DisplayRule
		*/	
		$group_field = "displayrule";
		$group_displayrule = "list-" . $group_field;
		$oRecord->addContent(null, true, $group_displayrule); 
   		$oRecord->groups[$group_displayrule] = array(
											"title" => Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src["type"] . "_fields_" . $group_field)
											, "tab" => $group_seo
										 );  

		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->widget = "slug";
		$oField->slug_title_field = "display_name";
		$oField->container_class = "hidden";
		$oRecord->addContent($oField, $group_displayrule);	
												 
		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_name";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_name");
		$oField->class = "title-page";
		$oField->required = true;
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_displayrule);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "schemaorg";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_schemaorg");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		);
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_displayrule);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "is_dir_default";
		$oField->container_class = "is_dir_default";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_is_dir");
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1");
		$oField->unchecked_value = new ffData("0");
		//$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_displayrule); 
		/*
		$oField = ffField::factory($cm->oPage);
		$oField->id = "advanced_group";
		$oField->container_class = "advanced_group";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_advanced_group");
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1");
		$oField->unchecked_value = new ffData("0");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_displayrule);  */   
	
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_menu";
		$oField->container_class = "enable-in-menu";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_menu");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
		/*$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;			
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.enable_in_menu > 0
				ORDER BY " . $src["type"] . "_fields.enable_in_menu, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_grid";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_grid");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
/*		$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;			
		
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.enable_in_grid > 0
				ORDER BY " . $src["type"] . "_fields.enable_in_grid, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_mail";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_mail");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
		/*
		$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;			
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.enable_in_mail > 0
				ORDER BY " . $src["type"] . "_fields.enable_in_mail, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		if(Cms::env("AREA_SHOW_ECOMMERCE") && $src["type"] == "vgallery") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "enable_in_cart";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_cart");
			$oField->extended_type = "Selection";
			$oField->data_type = "";
			$oField->store_in_db = false;
			/*
			$oField->widget = "autocompletetoken";
			$oField->autocompletetoken_minLength = 0;
			$oField->autocompletetoken_theme = "";
			$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
			$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
			$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
			$oField->autocompletetoken_label = "";
			$oField->autocompletetoken_combo = true;
			$oField->autocompletetoken_compare = "name";*/
			$oField->widget = "actex";
			$oField->actex_autocomp = true;	
			$oField->actex_multi = true;
			$oField->actex_update_from_db = true;			
			$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
										, " . $src["type"] . "_fields.name
									FROM " . $src["type"] . "_fields
									WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
									[AND][WHERE]
									[HAVING]
									[ORDER] [COLON] name
									[LIMIT]";
			$default_value = "";
			$sSQL = "SELECT " . $src["type"] . "_fields.* 
					FROM " . $src["type"] . "_fields
					WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
						AND " . $src["type"] . "_fields.enable_in_cart > 0
					ORDER BY " . $src["type"] . "_fields.enable_in_cart, " . $src["type"] . "_fields.`order_thumb`";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$arrDefaultField = array();
				do {
					$arrDefaultField[] = $db->getField("ID", "Number", true);
				} while($db->nextRecord());
				$default_value = implode(",", $arrDefaultField);
				$oField->default_value = new ffData($default_value);
			}
			$oField->user_vars["default_value"] = $default_value;
			$oRecord->addContent($oField, $group_displayrule);
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_document";
		$oField->container_class = "enable_in_document";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_document");
		$oField->extended_type = "Selection";
		$oField->data_type = "";
		$oField->store_in_db = false;
		/*$oField->widget = "autocompletetoken";
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;	
		$oField->source_SQL = "SELECT " . $src["type"] . "_fields.ID
									, " . $src["type"] . "_fields.name
								FROM " . $src["type"] . "_fields
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src["type"] . "_fields.* 
				FROM " . $src["type"] . "_fields
				WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
					AND " . $src["type"] . "_fields.enable_in_document > 0
				ORDER BY " . $src["type"] . "_fields.enable_in_document, " . $src["type"] . "_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db->getField("ID", "Number", true);
			} while($db->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		
		
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "vgalleryTypeGroup";
		$oButton->aspect = "link"; 
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_group");
		if($_REQUEST["XHR_CTX_ID"])
		{ 
			$cm->oPage->widgetLoad("dialog");
			$cm->oPage->widgets["dialog"]->process(
				"vgalleryTypeGroup"
				, array(
					"url" => $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/group?ID_type=" . $_REQUEST["keys"]["ID"]
				)
				, $cm->oPage
			);
			$oButton->jsaction = "ff.ffPage.dialog.doOpen('vgalleryTypeGroup')";
		} else {
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/group?ID_type=" . $_REQUEST["keys"]["ID"];
		}
		$oRecord->addActionButton($oButton);		



		if(MASTER_CONTROL && $record["public"]) {
		    /***********
		    *  Group Public
		    */

			$group_field = "public";
			$group_public = "list-" . $group_field;
			$oRecord->addContent(null, true, $group_public); 
   			$oRecord->groups[$group_public] = array(
												"title" => Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src["type"] . "_fields_" . $group_field)
												, "tab" => $group_public
											 );  
		
			//cover e description da inserire
			$oField = ffField::factory($cm->oPage);
			$oField->id = "public_cover";
			$oField->label = ffTemplate::_get_word_by_code("public_cover");
	        $oField->base_type = "Text";
	        $oField->control_type = "file";
		    $oField->extended_type = "File";
		    $oField->file_storing_path = FF_DISK_UPDIR . "/" . $src["type"] . "-type/[name_FATHER]";
		    $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/" . $src["type"] . "-type";
			$oField->file_allowed_mime = array();	                
		    $oField->file_full_path = true;
	        $oField->file_check_exist = true;
		    $oField->file_show_filename = true; 
		    $oField->file_show_delete = true;
		    $oField->file_writable = false;
		    $oField->file_normalize = true;
	        $oField->file_show_preview = true;
		    $oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
		    $oField->file_saved_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";
			
			$oField->widget = "uploadify";
			if(check_function("set_field_uploader")) { 
				$oField = set_field_uploader($oField);
			}
			//$obj_page_field->widget = "uploadifive";
			$oRecord->addContent($oField, $group_public);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "public_description";
			$oField->label = ffTemplate::_get_word_by_code("public_description");
			$oField->extended_type = "Text";
			$oRecord->addContent($oField, $group_public);
			
			$oField = ffField::factory($cm->oPage);
			$oField->id = "public_link_doc";
			$oField->label = ffTemplate::_get_word_by_code("public_link_doc");
			$oField->addValidator("url");
			$oRecord->addContent($oField, $group_public);		
		
		}

	}

	$cm->oPage->addContent($oRecord);
	
	$cm->oPage->tplAddJs("ff.cms.admin.vgallery-type-extra");
}


function VGalleryFields_on_before_parse_row($component)
{
    $check_enabled = Cms::getInstance("frameworkcss")->get("check-square-o", "icon");
    $check_disabled = Cms::getInstance("frameworkcss")->get("square-o", "icon");
    $enable_field = "";
    if(isset($component->db[0]->fields["visible"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setvisible', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("visible", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
    	if($component->db[0]->getField("visible", "Number", true)) {
			  $component->grid_disposition_elem["rows"][1] = array();
			  $enable_field = '<a href="' . $url_action . '" class="' . Cms::getInstance("frameworkcss")->get("eye", "icon") . '"></a>';
			  $component->row_class = "";
    	} else {
    		$component->grid_disposition_elem["rows"][1]["class"] = "hidden";
    		$enable_field = '<a href="' . $url_action . '" class="' . Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent") . '"></a>';
    		$component->row_class = "hideable hidden";
    	}
    }
    
    if(isset($component->grid_fields["name"])) {
    	$component->grid_fields["name"]->setValue(
			$enable_field
			. '<a href="' . $component->grid_fields["name"]->url_parsed . '">'
    		. Cms::getInstance("frameworkcss")->get("vg-" . ffCommon_url_rewrite($component->db[0]->getField("extended_type", "Text", true)), "icon-tag", array("2x"))
    		. " [" . $component->db[0]->getField("extended_type", "Text", true) . "] "
    		. $component->db[0]->getField("name", "Text", true)
    		. '</a>'
    	);
    
    }
    
    if(isset($component->grid_fields["enable_empty"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setempty', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("enable_empty", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
    	if($component->db[0]->getField("enable_empty", "Number", true)) {
    		$component->grid_fields["enable_empty"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["enable_empty"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }
    if(isset($component->grid_fields["enable_label"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setlabel', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("enable_label", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
    	if($component->db[0]->getField("enable_label", "Number", true)) {
    		$component->grid_fields["enable_label"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["enable_label"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }    	
    if(isset($component->grid_fields["require"])) {
		$url_action = "javascript:ff.ajax.doRequest({'action': 'setrequire', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("require", "Number", true) . "&limit=" . '' . "'});";
    	if($component->db[0]->getField("require", "Number", true)) {
    		$component->grid_fields["require"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["require"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    } 
    if(isset($component->grid_fields["disable_multilang"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setmultilang', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("disable_multilang", "Number", true) . "&limit=" . '' . "'});";
    	if($component->db[0]->getField("disable_multilang", "Number", true)) {
    		$component->grid_fields["disable_multilang"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["disable_multilang"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }  
    if(isset($component->grid_fields["enable_tip"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'settip', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src"]["type"] . "&value=" . !$component->db[0]->getField("enable_tip", "Number", true) . "&limit=" . '' . "'});";
    	if($component->db[0]->getField("enable_tip", "Number", true)) {
    		$component->grid_fields["enable_tip"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["enable_tip"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    } 
    if(isset($component->grid_fields["settings_type"])) {
    	if($component->db[0]->getField("extended_group", "Text", true) == "upload")
    		$component->grid_fields["settings_type"]->display = true;
    	else
    		$component->grid_fields["settings_type"]->display = false;
	}
	
    if(isset($component->grid_fields["custom_field"])) {
    	if($component->db[0]->getField("custom_field", "Number", true)) {
    		$component->grid_fields["htmltag"]->display = false;
    		$component->grid_fields["grid_field"]->display = false;
    		$component->grid_fields["custom_field"]->display = true;
		} else {
			$component->grid_fields["htmltag"]->display = true;
			$component->grid_fields["grid_field"]->display = true;
    		$component->grid_fields["custom_field"]->display = false;
		}
	}
} 


function VGalleryFields_on_before_process_row($component, $record) {
	$cm = cm::getInstance();
//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	if(isset($component->detail_buttons["editrow"])) {  
	    if($component->detail_buttons["editrow"]["obj"]->action_type == "submit") { 
	        $cm->oPage->widgetLoad("dialog");
	        $cm->oPage->widgets["dialog"]->process(
	             "vgFieldModify_" . $record["ID"]->getValue()
	             , array(
	                "tpl_id" => $component->id
	                //"name" => "myTitle"
	                , "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/modify"
	                        . "?keys[ID]=" . $record["ID"]->getValue()
                                . "&ret_url=" . urlencode($component->parent[0]->getRequestUri()) 
	                , "callback" => ""
	                , "class" => ""
	                , "params" => array()
	            )
	            , $cm->oPage
	        );
	        $component->detail_buttons["editrow"]["obj"]->jsaction = "ff.ffPage.dialog.doOpen('" . "vgFieldModify_" . $record["ID"]->getValue() . "')";
	    }
	}
}

function VGalleryTypeModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	$src =  $component->user_vars["src"];
	
    switch ($action) {
        case "insert":
        case "update":
            if($_REQUEST["keys"]["ID"])
            {
                if(0)
                {
                    $sSQL = "SELECT " . $src["type"] . "_type_group.*
                                FROM " . $src["type"] . "_type_group
                                WHERE " . $src["type"] . "_type_group.ID_type = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            $arrGroupType[$db->getField("ID", "number", true)] = $db->getField("name", "Text", true);
                        } while ($db->nextRecord());
                    }
                    foreach($component->detail["VGalleryFields"]->recordset AS $key => $value)
                    {
                        $value["parent_thumb"]->setValue($arrGroupType[$value["ID_group_thumb"]->getValue()]);
                        $value["parent_detail"]->setValue($arrGroupType[$value["ID_group_detail"]->getValue()]);
                     }
                }
            }
        	break;
        default:
    }
}
	
function VGalleryTypeModifyDetail_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
    
                
	switch ($action) {
		case "insert":
		case "update":
			$component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
                   // ffErrorHandler::raise("ass", E_USER_ERROR, null, get_defined_vars());
			$smart_url = 0;
			if(is_array($component->recordset) && count($component->recordset)) {
		        foreach($component->recordset AS $rst_key => $rst_value) {
	        		if($component->recordset[$rst_key]["enable_smart_url"]->getValue() > 0) {
	        			$smart_url++;
					}
				}
			}

			if(!($smart_url > 0)) {
				$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty"));
				return true;
			}
		break;
		default:
	}
}


function VGalleryTypeModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
   $src =  $component->user_vars["src"];
    
    if(strlen($action)) {
    	switch($action) {
    		case "insert":
    		case "update":
				if(1)
				{
					if(check_function("get_vgallery_type_group"))
						get_vgallery_type_group($component->key_fields["ID"]->getValue(), "backoffice");
				}
    			
    		
    			if(isset($component->form_fields["smart_url"])) {
    				$smart_url_old = $component->form_fields["smart_url"]->user_vars["default_value"];
    				$smart_url_new = $component->form_fields["smart_url"]->getValue();

					$arrField = explode(",", $smart_url_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_smart_url = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.enable_smart_url = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["meta_description"])) {
    				$meta_description_old = $component->form_fields["meta_description"]->user_vars["default_value"];
    				$meta_description_new = $component->form_fields["meta_description"]->getValue();

					$arrField = explode(",", $meta_description_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.meta_description = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.meta_description = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}    

				if(isset($component->form_fields["enable_in_menu"])) {
					$field_old = $component->form_fields["enable_in_menu"]->user_vars["default_value"];
					$field_new = $component->form_fields["enable_in_menu"]->getValue();
					
					if(!strlen($field_new) || ($smart_url_old == $field_old && $smart_url_new != $field_new && $field_new == $field_old)) {
						$field_new = $smart_url_new;
					}
					$arrField = explode(",", $field_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_menu = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.enable_in_menu = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["enable_in_grid"])) {
					$grid_old = $component->form_fields["enable_in_grid"]->user_vars["default_value"];
					$grid_new = $component->form_fields["enable_in_grid"]->getValue();
					
					if(!strlen($grid_new) || ($smart_url_old == $grid_old && $smart_url_new != $grid_new && $grid_new == $grid_old)) {
						$grid_new = $smart_url_new;
					}
					$arrField = explode(",", $grid_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_grid = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.enable_in_grid = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["enable_in_mail"])) {
					$field_old = $component->form_fields["enable_in_mail"]->user_vars["default_value"];
					$field_new = $component->form_fields["enable_in_mail"]->getValue();
					
					if(!strlen($field_new)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_mail = 1 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					} else {
						$arrField = explode(",", $field_new);
						if(is_array($arrField) && count($arrField)) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_mail = 0 
									WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						
							foreach($arrField AS $arrField_key => $arrField_value) {
								$sSQL = "UPDATE " . $src["type"] . "_fields SET 
											" . $src["type"] . "_fields.enable_in_mail = " . $db->toSql($arrField_key + 1, "Number") . "
										WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
											AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
								$db->execute($sSQL);
							}
						}
					}
				}
				if(isset($component->form_fields["enable_in_cart"])) {
					$field_old = $component->form_fields["enable_in_cart"]->user_vars["default_value"];
					$field_new = $component->form_fields["enable_in_cart"]->getValue();
					
					if(!strlen($field_new) || ($grid_old == $field_old && $grid_new != $field_new && $field_new == $field_old)) {
						$field_new = $grid_new;
					}
					$arrField = explode(",", $field_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_cart = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.enable_in_cart = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["enable_in_document"])) {
					$field_old = $component->form_fields["enable_in_document"]->user_vars["default_value"];
					$field_new = $component->form_fields["enable_in_document"]->getValue();
					
					if(!strlen($field_new) || ($grid_old == $field_old && $grid_new != $field_new && $field_new == $field_old)) {
						$field_new = $grid_new;
					}
					$arrField = explode(",", $field_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src["type"] . "_fields SET " . $src["type"] . "_fields.enable_in_document = 0 
								WHERE " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src["type"] . "_fields SET 
										" . $src["type"] . "_fields.enable_in_document = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src["type"] . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src["type"] . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				

    			break;
    		default:
    	}
    
	    if(check_function("system_get_sections"))
			$block_type = system_get_block_type();	
    
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `vgallery_type`.last_update FROM vgallery_type WHERE vgallery_type.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number") . ") 
                WHERE 
                    layout.ID_type IN (" . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . ", " . $db->toSql($block_type["virtual-menu"]["ID"], "Number") . ")
                    OR
                    (
                        layout.value LIKE '%" . $db->toSql("vgallery", "Text", false) . "%'
                        AND layout.ID_type = " . $db->toSql($block_type["publishing"]["ID"], "Number") . "
                    )
                    ";
        $db->execute($sSQL);
	}
}