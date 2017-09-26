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

 if (!AREA_VGALLERY_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "UPDATE vgallery
            SET vgallery.status = " . $db->toSql($_REQUEST["setvisible"], "Number") . "
            WHERE vgallery.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);

    $sSQL = "SELECT vgallery_nodes.ID 
            FROM vgallery_nodes 
                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
            WHERE vgallery_nodes.name = vgallery.name
                AND vgallery.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                AND vgallery_nodes.parent = '/'";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $ID_node = $db->getField("ID", "Number", true);
        
        if(check_function("get_locale")) {
			$arrLang = get_locale("lang", true);
		}        
        
        if(is_array($arrLang) && count($arrLang)) { 
            check_function("update_vgallery_seo");
            foreach($arrLang AS $lang_code => $lang) {                    
                update_vgallery_seo(
                	null
                	, $ID_node
                	, $lang["ID"]
                	, null
                	, null
                	, null
                	, $_REQUEST["setvisible"]
                	, null
                	, null
                	, array(
                        "lang" => "ID_lang"
                        , "permalink" => "permalink"
                        , "smart_url" => "name"
                        , "title" => "meta_title"
                        , "header" => "meta_title_alt"
                        , "description" => "meta_description"
                        , "keywords" => "keywords"
                        , "permalink_parent" => "parent"
                        , "visible" => "visible"
                        , "parent" => "parent"
                        , "name" => "name"
                    )
                    , ($lang["ID"] == LANGUAGE_DEFAULT_ID ? "primary" : null)
                );
            }
        }
    }
       
    //if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
    //} else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    //}
} 

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setpublic"]) && $_REQUEST["keys"]["ID"] > 0) {
    if($_REQUEST["setpublic"]) {
	    $sSQL = "SELECT vgallery.* 
    			FROM vgallery 
    			WHERE vgallery.name = (SELECT vgallery.name 
    									FROM vgallery 
    									WHERE vgallery.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
    								)
    				AND vgallery.public > 0";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
			$denied_update = true;	    
		}
	}

    if(!$denied_update) {
		$sSQL = "UPDATE vgallery
		        SET vgallery.public = " . $db->toSql($_REQUEST["setpublic"], "Number") . "
		        WHERE vgallery.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
		$db->execute($sSQL);
	}

    //if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
    //} else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    //}
} 

check_function("system_ffcomponent_set_title");
$record = system_ffComponent_resolve_record("vgallery", array(
	"public" => null
));

if(check_function("get_update_by_service") && !set_interface_for_copy_by_service("vgallery", "VGalleryModify")) {
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "VGalleryModify";
	$oRecord->resources[] = $oRecord->id;
	$oRecord->addEvent("on_do_action", "VGalleryModify_on_do_action");
	$oRecord->addEvent("on_done_action", "VGalleryModify_on_done_action");
	$oRecord->src_table = "vgallery";
	$oRecord->auto_populate_edit = true;
	$oRecord->populate_edit_SQL = "SELECT vgallery.* 
										, IF(display_name = ''
											, REPLACE(name, '-', ' ')
											, display_name
										) AS display_name
										, (SELECT GROUP_CONCAT(vgallery_type.ID) 
											FROM vgallery_type 
											WHERE vgallery_type.is_dir_default = 0 
												AND vgallery_type.public = " . $db->toSql($record["public"], "Number") . "
												AND FIND_IN_SET(vgallery_type.ID, vgallery.limit_type)
										) AS limit_type_node
										, (SELECT GROUP_CONCAT(vgallery_type.ID) 
											FROM vgallery_type 
											WHERE vgallery_type.is_dir_default > 0 
												AND vgallery_type.public = " . $db->toSql($record["public"], "Number") . "
												AND FIND_IN_SET(vgallery_type.ID, vgallery.limit_type)
										) AS limit_type_dir
									FROM vgallery 
									WHERE vgallery.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$oRecord->additional_fields["last_update"] =  new ffData(time(), "Number");
	$oRecord->insert_additional_fields["status"] = new ffData("1", "Number");	
	$oRecord->user_vars["public"] = $record["public"];
	$oRecord->tab = true;
	
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

    $group_general = "vg-general";
    $oRecord->addContent(null, true, $group_general); 
    $oRecord->groups[$group_general] = array(
												"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_general)
                                                 , "tab" => $group_general
                                              );


	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->widget = "slug";
	$oField->slug_title_field = "display_name";
	$oField->container_class = "hidden";
	$oRecord->addContent($oField, $group_general);
		
	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_name");
	$oField->class = "input title-page";
	$oField->required = true;
	$oRecord->addContent($oField, $group_general);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "limit_level";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_limit_level");
	$oField->required = true;
	$oField->default_value = new ffData("1", "Number");
	$oRecord->addContent($oField, $group_general);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "limit_type_node";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_limit_type");
	$oField->widget = "actex";
	$oField->source_SQL = "SELECT ID
								, CONCAT(
                            		IF(vgallery_type.public > 0
                            			, " . $db->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            			, ''
                            		)
                            		, vgallery_type.name
	                            ) AS name
		                    FROM vgallery_type 
		                    WHERE " . (OLD_VGALLERY 
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                                ) . "
                        		AND vgallery_type.public = " . $db->toSql($record["public"], "Number") . "
                        		AND vgallery_type.is_dir_default = 0 
		                    [AND] [WHERE]
		                    [HAVING]
		                    ORDER BY name";
	$oField->actex_dialog_url = get_path_by_rule("contents-structure") . "/add?dir=0";
	$oField->actex_dialog_edit_url = get_path_by_rule("contents-structure") . "?dir=0";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryTypeModify_confirmdelete";
	$oField->resources[] = "VGalleryTypeModify";
	$oField->actex_update_from_db = true;
	$oField->actex_child = "data_ext";
	$oField->actex_hide_empty = "all";
	$oField->actex_on_update_bt = 'function(obj) {
		ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());
	}';
	$oField->required = true;
	$oField->store_in_db = false;
	$oRecord->addContent($oField, $group_general);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "limit_type_dir";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_limit_dir");
	$oField->container_class = "type-dir-selection";
	$oField->widget = "actex";
	$oField->source_SQL = "SELECT ID
								, CONCAT(
                            		IF(vgallery_type.public > 0
                            			, " . $db->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            			, ''
                            		)
                            		, vgallery_type.name
	                            ) AS name
		                    FROM vgallery_type 
		                    WHERE " . (OLD_VGALLERY 
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                                ) . "
                        		AND (vgallery_type.public = " . $db->toSql($record["public"], "Number") . "
                        				AND vgallery_type.is_dir_default > 0 
                        		)
                        		OR vgallery_type.name = 'Directory'
		                    [AND] [WHERE]
		                    [HAVING]
		                    ORDER BY name";
	$oField->actex_dialog_url = get_path_by_rule("contents-structure") . "/add?dir=1";
	$oField->actex_dialog_edit_url = get_path_by_rule("contents-structure") . "?dir=1";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryTypeModify_confirmdelete";
	$oField->resources[] = "VGalleryTypeModify";
	$oField->actex_on_refill = "function(obj){ ff.cms.admin.checkLimitLevel('#VGalleryModify_limit_level'); }";
	
	$oField->actex_update_from_db = true;
	$oField->actex_hide_empty = "all";
	$oField->multi_select_one = false;
	$oField->store_in_db = false;
	$oRecord->addContent($oField, $group_general);

	/*******************************
	* Settings
	*/
	$group_settings = "vg-settings";
	$oRecord->addContent(null, true, $group_settings); 
	$oRecord->groups[$group_settings] = array(
										"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_settings)
										, "tab" => $group_settings
									 );	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "insert_on_lastlevel";
	$oField->container_class = "general";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_insert_on_lastlevel");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->default_value = new ffData("0", "Number");
	$oRecord->addContent($oField, $group_settings);

	
	if(check_function("get_locale"))
		$arrLang = get_locale("lang", true);

	if(count($arrLang) > 1) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_multilang_visible";
		$oField->container_class = "general";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_multilang_visible");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData("1", "Number");
		$oRecord->addContent($oField, $group_settings);
	} else {
		$oRecord->insert_additional_fields["enable_multilang_visible"] = new ffData("1", "Number");
	}

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_tag";
    $oField->container_class = "general";
    $oField->base_type = "Number";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_tag");
    $oField->extended_type = "Selection";
    $oField->source_SQL = "SELECT search_tags_categories.ID
                            , search_tags_categories.name
                        FROM search_tags_categories
                        WHERE 1
                        ORDER BY search_tags_categories.name";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
    $oField->multi_select_noone = true;
    $oField->multi_select_noone_val = new ffData("-1", "Number");
    $oField->multi_select_noone_label = ffTemplate::_get_word_by_code("all");
    $oRecord->addContent($oField, $group_settings);    


	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_multi_cat";
	$oField->container_class = "general";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_multi_cat");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_settings);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_place";
	$oField->container_class = "general";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_place");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_referer";
	$oField->container_class = "general";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_referer");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_settings);	
		
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_priority";
	$oField->container_class = "general";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_priority");
	$oField->extended_type = "Selection";
    $oField->widget = "actex";
    $oField->actex_autocomp = true;	
    $oField->actex_multi = true;
//    $oField->autocompletetoken_combo = true;
 //			   $oField->autocompletetoken_minLength = 0;
	//$oField->autocompletetoken_multi = true;
    $oField->actex_update_from_db = true;
	$oField->source_SQL = "
						(
	    					SELECT '-1' AS ID
    						, '" . ffTemplate::_get_word_by_code("all") . "' AS name
    					) UNION (
    						SELECT '1' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_priority_bottom") . "' AS name
    					) UNION (
    						SELECT '2' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_very_low") . "' AS name
    					) UNION (
    						SELECT '3' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_low") . "' AS name
    					) UNION (
    						SELECT '4' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_normal") . "' AS name
    					) UNION (
    						SELECT '5' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_hight") . "' AS name
    					) UNION (
    						SELECT '6' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_very_hight") . "' AS name
    					) UNION (
    						SELECT '7' AS ID
    						, '" . ffTemplate::_get_word_by_code("vgallery_top") . "' AS name
                        )";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
    /*$oField->multi_select_noone = true;
    $oField->multi_select_noone_val = new ffData("-1", "Number");
    $oField->multi_select_noone_label = ffTemplate::_get_word_by_code("all");*/
	$oRecord->addContent($oField, $group_settings);	 
			
	/*******************************
	* Highlight
	*/

	/*
	$oRecord->addContent(null, true, "Highlight"); 
	$oRecord->groups["Highlight"] = array(
										"title" => ffTemplate::_get_word_by_code("admin_vgallery_Highlight")
										, "cols" => 1
										, "class" => cm_getClassByFrameworkCss(array(12, 12, 12, 6), "col")
									 );	*/
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_highlight";
	$oField->container_class = "highlight";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_highlight");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_settings);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "highlight_image_thumb";
	$oField->container_class = "highlight";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_highlight_image_thumb");
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_url = get_path_by_rule("utility") . "/image/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
	$oField->resources[] = "ExtrasImageModify";
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "highlight_image_detail";
	$oField->container_class = "highlight";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_highlight_image_detail");
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_url = get_path_by_rule("utility") . "/image/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
	$oField->resources[] = "ExtrasImageModify";
	$oRecord->addContent($oField, $group_settings);*/


	/*******************************
	* Notice
	*/
	$group_notice = "vg-notice";
	$oRecord->addContent(null, true, $group_notice); 
	$oRecord->groups[$group_notice] = array(
										"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_notice)
										, "tab" => $group_settings
									 );	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_email_notify_on_insert";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_email_notify_on_insert");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_notice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_email_notify_on_update";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_email_notify_on_update");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_notice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "email_notify_show_detail";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_email_notify_show_detail");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_notice);

	/*******************************
	* BackOffice
	*/
	$group_backoffice = "vg-backoffice";
	$oRecord->addContent(null, true, $group_backoffice); 
	$oRecord->groups[$group_backoffice] = array(
										"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_backoffice)
										, "tab" => $group_settings
									 );	

	$oField = ffField::factory($cm->oPage);
	$oField->id = "force_picture_link";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_force_picture_link");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "force_picture_ico_spacer";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_force_picture_ico_spacer");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "disable_dialog_in_edit";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_disable_dialog_in_edit");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "drag_sort_node_enabled";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_drag_sort_node_enabled");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "drag_sort_dir_enabled";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_drag_sort_dir_enabled");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "use_user_as_prefix_in_fs";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_user_as_prefix_in_fs");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "show_owner_in_grid";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_owner_in_grid");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_backoffice);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "show_ID";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_ID");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_backoffice);	

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_tab";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_tab");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_backoffice);	

    $oField = ffField::factory($cm->oPage);
    $oField->id = "show_owner_by_categories";
    $oField->container_class = "backoffice";
    $oField->extended_type = "Selection";
    //$oField->base_type = "Number";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_owner_by_categories");
    $oField->widget = "actex";
    $oField->actex_autocomp = true;	
    $oField->actex_multi = true;
    //$oField->widget = "autocompletetoken";
    //$oField->autocompletetoken_combo = true;
    //$oField->autocompletetoken_minLength = 0;
	//$oField->autocompletetoken_multi = true;
    $oField->actex_update_from_db = true;
    $oField->source_SQL = "
    					(
    						SELECT '-1' AS ID
    						, '" . ffTemplate::_get_word_by_code("all") . "' AS name
    					) UNION (
    						SELECT anagraph_categories.ID
	                            , anagraph_categories.name
	                        FROM anagraph_categories
	                        WHERE 1
	                        ORDER BY anagraph_categories.name
                        )";
    //$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
    //$oField->multi_select_noone = true;
    //$oField->multi_select_noone_val = new ffData("-1", "Number");
    //$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("all");
    $oRecord->addContent($oField, $group_backoffice);    
    

	$oField = ffField::factory($cm->oPage);
	$oField->id = "show_isbn";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_isbn");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_backoffice);	
    
	$oField = ffField::factory($cm->oPage);
	$oField->id = "back_orderby";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_orderby");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array(
		array(new ffData("frontend"), new ffData(ffTemplate::_get_word_by_code("order_by_frontend"))) 
		, array(new ffData("title"), new ffData(ffTemplate::_get_word_by_code("order_by_title")))
	);
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
	$oRecord->addContent($oField, $group_settings);		
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "back_filteraz";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_filteraz");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField, $group_settings);		
	
	if(check_function("get_table_support"))
		$tbl_data_type = get_table_support("data_type");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "data_ext";
	$oField->container_class = "backoffice";	
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_data_ext");
	$oField->widget = "actex";
	$oField->source_SQL = "SELECT ID
								, name
								, ID_type
		                    FROM vgallery_fields 
		                    WHERE 1
                        		AND vgallery_fields.ID_data_type = " .  $db->toSql($tbl_data_type["smart_url"]["relationship"]["ID"], "Number") . "
		                    [AND] [WHERE]
		                    [HAVING]
		                    ORDER BY name";
	$oField->actex_update_from_db = true;
	$oField->actex_father = "limit_type_node";
	$oField->actex_related_field = "ID_type";
	$oField->actex_autocomp = true;
	$oField->actex_multi = true;
	$oRecord->addContent($oField, $group_settings);		
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "permalink_rule";
	$oField->container_class = "backoffice";	
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_permalink_rule");
	$oField->default_value = new ffData("/[PARENT]/[SMART_URL]");
	$oRecord->addContent($oField, $group_settings);		
	
	if(MASTER_CONTROL && $record["public"]) {
	    /***********
	    *  Group Public
	    */

	    $group_public = "public";
	    $oRecord->addContent(null, true, $group_public); 
	    $oRecord->groups[$group_public] = array(
													"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_public)
	                                                 , "tab" => true
	                                              );	
		//cover e description da inserire
		$oField = ffField::factory($cm->oPage);
		$oField->id = "public_cover";
		$oField->label = ffTemplate::_get_word_by_code("public_cover");
		$oField->base_type = "Text";
		$oField->control_type = "file";
		$oField->extended_type = "File";
		$oField->file_storing_path = DISK_UPDIR . "/vgallery/[name_VALUE]";
		$oField->file_temp_path = DISK_UPDIR . "/vgallery";
		$oField->file_allowed_mime = array();	                
		$oField->file_full_path = true;
		$oField->file_check_exist = true;
		$oField->file_show_filename = true; 
		$oField->file_show_delete = true;
		$oField->file_writable = false;
		$oField->file_normalize = true;
		$oField->file_show_preview = true;
		$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/vgallery/[name_VALUE]/[_FILENAME_]";
		$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/100x100/vgallery/[name_VALUE]/[_FILENAME_]";
		$oField->widget = "uploadify";
		if(check_function("set_field_uploader")) { 
			$oField = set_field_uploader($oField);
		}
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
	
	
	if(AREA_SHOW_ECOMMERCE) {
		/*******************************
		* Ecommerce
		*/
		$group_ecommerce = "vg-ecommerce";
		$oRecord->addContent(null, true, $group_ecommerce); 
		$oRecord->groups["Ecommerce"] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_ecommerce)
											, "tab" => $group_ecommerce
										 );	

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_ecommerce";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_ecommerce");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
		$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
		$oRecord->addContent($oField, $group_ecommerce);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_ecommerce_all_level";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_ecommerce_all_level");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
		$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
		$oRecord->addContent($oField, $group_ecommerce);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "use_pricelist_as_item_thumb";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_pricelist_as_item_thumb");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oRecord->addContent($oField, $group_ecommerce);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "use_pricelist_as_item_detail";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_pricelist_as_item_detail");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oRecord->addContent($oField, $group_ecommerce);
	}
	
	$cm->oPage->tplAddJs("ff.cms.admin.vgallery-modify");
	
	$cm->oPage->addContent($oRecord);
}





// -------------------------
//          EVENTI
// -------------------------
function VGalleryModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    if(strlen($action)) {
	    switch($action) {
	        case "insert":
	        	if(isset($component->form_fields["name"])) {
		            $db->query("SELECT * 
		                        FROM vgallery 
		                        WHERE vgallery.name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
		                        	AND vgallery.public = " . $db->toSql($component->user_vars["public"], "Number")
		                    );
		            if($db->nextRecord()) {
		                $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
		                return true;
		            } else {
		                $component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
		            }
				}
	            break;
	        case "update":
	                $db->query("SELECT * 
	                            FROM vgallery
	                            WHERE vgallery.name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
	                                AND vgallery.ID <> " . $db->toSql($component->key_fields["ID"]->value) . "
	                                AND vgallery.public = " . $db->toSql($component->user_vars["public"], "Number")
	                        );
	                if($db->nextRecord()) {
	                    $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
	                    return true;
	                } else {
	                    $component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
	                    
	                    /*
	                    $old_parent = stripslash("/" . $component->form_fields["name"]->value_ori->getValue());
	                    $new_parent = stripslash("/" . $component->form_fields["name"]->value->getValue());

	                    $db->execute("UPDATE vgallery_nodes 
	                                SET vgallery_nodes.name = " . $db->toSql($component->form_fields["name"]->value)  . "
	                                WHERE
	                                    vgallery_nodes.name = " . $db->toSql($component->form_fields["name"]->value_ori)  . "
	                                    AND vgallery_nodes.parent = " . $db->toSql("/")
	                            );

	                    $db->execute("UPDATE vgallery_nodes 
	                                SET vgallery_nodes.parent = REPLACE(vgallery_nodes.parent, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
	                                WHERE
							            (vgallery_nodes.parent = " . $db->toSql($old_parent)  . " 
							                OR vgallery_nodes.parent LIKE '" . $db->toSql($old_parent, "Text", false)  . "/%'
							            )"
	                            );
                         */
	                }
	            break;
	        case "delete":
	            break;
	        case "confirmdelete":
	               // if(check_function("delete_vgallery"))
	               //     delete_vgallery("/", $component->form_fields["name"]->getValue(), $component->form_fields["name"]->getValue());
	            break;
	    }
	}
    return false;
}


function VGalleryModify_on_done_action ($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
        $ID_vgallery = $component->key_fields["ID"]->getValue();
        $old_permalink = "/" . $component->form_fields["name"]->value_ori->getValue();
        $permalink = "/" . $component->form_fields["name"]->value->getValue();
        
    	if($action == "insert" || $action == "update") {
    		$limit_type = $component->form_fields["limit_type_node"]->getValue() 
    						. (strlen($component->form_fields["limit_type_dir"]->getValue())
    							? "," . $component->form_fields["limit_type_dir"]->getValue()
    							: ""
    						);
			$sSQL = "UPDATE vgallery SET vgallery.limit_type = " . $db->toSql($limit_type) . " WHERE vgallery.ID = " . $db->toSql($ID_vgallery, "Number");
			$db->execute($sSQL);    
		}    

        if(check_function("get_locale"))
            $arrLang = get_locale("lang", true);        

        $ID_type_dir = 0;
        $sSQL = "SELECT vgallery_type.ID 
        		FROM vgallery_type 
        		WHERE vgallery_type.name = " . $db->toSql("Directory");
        $db->query($sSQL);
        if($db->nextRecord()) {
        	$ID_type_dir = $db->getField("ID", "Number", true);
        }
        
	    switch ($action) {
	        case "insert":
                $visible = 1;
	            $sSQL = "INSERT INTO `vgallery_nodes` 
	                        (   `ID` 
	                            , `ID_vgallery` 
	                            , `name` 
	                            , `order` 
	                            , `parent` 
	                            , `ID_type`
	                            , `is_dir` 
	                            , `last_update` 
	                            , `visible` 
	                            , `owner`
	                        )
	                    VALUES 
	                        (
	                            NULL 
	                            , " . $db->toSql($ID_vgallery, "Number") . "
	                            , " . $db->toSql(basename($permalink)) . "
	                            , '0'
	                            , " . $db->toSql("/") . "
	                            , " . $db->toSql($ID_type_dir, "Number") . "
	                            , '1'
	                            , " . $db->toSql(new ffData(time(), "Number")) . "
	                            , " . $db->toSql($visible, "Number") . "
		                        , " . $db->toSql(get_session("UserNID"), "Number") . "
	                        )";
	            $db->execute($sSQL);
                $ID_node = $db->getInsertID(true);

                if(is_array($arrLang) && count($arrLang)) { 
                    check_function("update_vgallery_seo");
                    foreach($arrLang AS $lang_code => $lang) {                    
                        update_vgallery_seo(basename($permalink), $ID_vgallery, $lang["ID"], null, null, null, $visible, null, null, array(
                                "lang" => "ID_lang"
                                , "permalink" => "permalink"
                                , "smart_url" => "name"
                                , "title" => "meta_title"
                                , "header" => "meta_title_alt"
                                , "description" => "meta_description"
                                , "keywords" => "keywords"
                                , "permalink_parent" => "parent"
                                , "visible" => "visible"
                                , "parent" => "parent"
                                , "name" => "name"
                            )
                        );
                    }
                }

	            if(check_function("set_field_permalink"))
                    $arrPermalink = set_field_permalink("vgallery_nodes", $ID_node, false, false, $component->form_fields["permalink_rule"]->getValue());

	            break;
	        case "update":  
	            $sSQL = "SELECT vgallery_nodes.ID 
                            , vgallery.status AS status
	            		FROM vgallery_nodes 
                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	            		WHERE vgallery_nodes.name = " . $db->toSql(basename($old_permalink)) . " 
	            			AND vgallery_nodes.parent = '/'
	            			AND vgallery_nodes.public = 0
	            		ORDER BY vgallery_nodes.ID";
	            $db->query($sSQL);
	            if(!$db->nextRecord()) {
		            $sSQL = "INSERT INTO `vgallery_nodes` 
		                        (   `ID` 
		                            , `ID_vgallery` 
		                            , `name` 
		                            , `order` 
		                            , `parent` 
		                            , `ID_type`
		                            , `is_dir` 
		                            , `last_update`
		                            , `visible` 
		                            , `owner`
		                        )
		                    VALUES 
		                        (
		                            NULL 
		                            , " . $db->toSql($ID_vgallery, "Number") . "
		                            , " . $db->toSql(basename($permalink)) . "
		                            , '0'
		                            , " . $db->toSql("/") . "
		                            , " . $db->toSql($ID_type_dir, "Number") . "
		                            , '1'
		                            , " . $db->toSql(new ffData(time(), "Number")) . "
		                            , '1'
		                            , " . $db->toSql(get_session("UserNID"), "Number") . "
		                        )";
		            $db->execute($sSQL);
		            $ID_node = $db->getInsertID(true);
                    $visible = 1;

				} else {
                    $ID_node = $db->getField("ID", "Number", true);
                    $visible = $db->getField("status", "Number", true);
		            if($permalink != $old_permalink) {
			            $sSQL = "UPDATE vgallery_nodes 
			                    SET vgallery_nodes.name = " . $db->toSql(basename($permalink), "Text") . " 
			                    WHERE vgallery_nodes.name = " . $db->toSql(basename($old_permalink), "Text");
			            $db->execute($sSQL);

			            $sSQL = "UPDATE `vgallery_nodes`
			                        SET vgallery_nodes.`parent`= (REPLACE(vgallery_nodes.parent, " . $db->toSql($old_permalink)  . ", " . $db->toSql($permalink)  . "))
			                    WHERE                            
						            (vgallery_nodes.parent = " . $db->toSql($old_permalink)  . " 
						                OR vgallery_nodes.parent LIKE '" . $db->toSql($old_permalink, "Text", false)  . "/%'
						            )";
			            $db->execute($sSQL);

					}
				}

                if(is_array($arrLang) && count($arrLang)) {
                    check_function("update_vgallery_seo");
                    foreach($arrLang AS $lang_code => $lang) {  
                        update_vgallery_seo(basename($permalink), $ID_node, $lang["ID"], null, null, null, $visible, null, null, array(
                                "lang" => "ID_lang"
                                , "permalink" => "permalink"
                                , "smart_url" => "name"
                                , "title" => "meta_title"
                                , "header" => "meta_title_alt"
                                , "description" => "meta_description"
                                , "keywords" => "keywords"
                                , "permalink_parent" => "parent"
                                , "visible" => "visible"
                                , "parent" => "parent"
                                , "name" => "name"
                            )
                        );
                    }
                }

                if(check_function("set_field_permalink"))
                    $arrPermalink = set_field_permalink("vgallery_nodes", $ID_node, false, false, $component->form_fields["permalink_rule"]->getValue());
                
		        //remove cache of relationship
		        if($ID_node > 0) {
			        $sSQL = "UPDATE vgallery_rel_nodes_fields SET 
		            			`nodes` = ''
		            		WHERE vgallery_rel_nodes_fields.`nodes` <> ''
		            			AND FIND_IN_SET(" . $db->toSql($ID_node, "Number") . ", vgallery_rel_nodes_fields.`nodes`)";
			        $db->execute($sSQL);
				}
	            break;    
	        case "confirmdelete":
				$sSQL = "SELECT vgallery_nodes.ID
			    		FROM vgallery_nodes
				        WHERE vgallery_nodes.name = " . $db->toSql(basename($permalink));
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_node = $db->getField("ID", "Number", true);
				}

				if(check_function("delete_vgallery"))
                    delete_vgallery("/", basename($permalink), basename($permalink));

		        //remove cache of relationship
		        $sSQL = "UPDATE vgallery_rel_nodes_fields SET 
		            		`nodes` = ''
		            	WHERE vgallery_rel_nodes_fields.`nodes` <> ''
		            		AND FIND_IN_SET(" . $db->toSql($ID_node, "Number") . ", vgallery_rel_nodes_fields.`nodes`)";
		        $db->execute($sSQL);	            
	            break;
	        
	    }
	    
		if(check_function("refresh_cache")) {
			if(is_array($arrPermalink) && count($arrPermalink)) {
                refresh_cache("V", $ID_node, $action, $arrPermalink);
			} else {
				refresh_cache("V", $ID_node, $action, $permalink);				
			}	    
		}
	}    
}
