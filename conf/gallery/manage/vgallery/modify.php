<?php

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_VGALLERY_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$_REQUEST["createnew"] = true;

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setstatus"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "UPDATE vgallery
            SET vgallery.status = " . $db_gallery->toSql($_REQUEST["setstatus"], "Number") . "
            WHERE vgallery.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
    $db_gallery->execute($sSQL);

    $sSQL = "SELECT vgallery_nodes.ID 
            FROM vgallery_nodes 
                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
            WHERE vgallery_nodes.name = vgallery.name
                AND vgallery.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") . "
                AND vgallery_nodes.parent = '/'";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $ID_node = $db_gallery->getField("ID", "Number", true);

        if(check_function("get_locale"))
            $arrLang = get_locale("lang", true);

        if(is_array($arrLang) && count($arrLang)) { 
            check_function("update_vgallery_seo");
            foreach($arrLang AS $lang_code => $lang) {
                update_vgallery_seo(null, $ID_node, $lang["ID"], null, null, null, $_REQUEST["setstatus"], null, null, array(
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
    } 
       
    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
} 

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setpublic"]) && $_REQUEST["keys"]["ID"] > 0) {
    if($_REQUEST["setpublic"]) {
	    $sSQL = "SELECT vgallery.* 
    			FROM vgallery 
    			WHERE vgallery.name = (SELECT vgallery.name 
    									FROM vgallery 
    									WHERE vgallery.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") . "
    								)
    				AND vgallery.public > 0";
	    $db_gallery->query($sSQL);
	    if($db_gallery->nextRecord()) {
			$denied_update = true;	    
		}
	}

    if(!$denied_update) {
		$sSQL = "UPDATE vgallery
		        SET vgallery.public = " . $db_gallery->toSql($_REQUEST["setpublic"], "Number") . "
		        WHERE vgallery.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
		$db_gallery->execute($sSQL);
	}

    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
} 

if(!isset($_REQUEST["keys"]["ID"]) && strlen(basename($cm->real_path_info))) {
    $db_gallery->query("SELECT vgallery.*
                            FROM 
                                vgallery
                            WHERE 
                                vgallery.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info))) . "
                            ORDER BY vgallery.public 
                            "
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number")->getValue();
	}
}

$ID_vgallery = $_REQUEST["keys"]["ID"];
$is_public = false; 
if($ID_vgallery > 0) {
    $vgallery_title = ffTemplate::_get_word_by_code("modify_vgallery"); 
    
    $db_gallery->query("SELECT vgallery.*
                            FROM 
                                vgallery
                            WHERE 
                                vgallery.ID = " . $db_gallery->toSql($ID_vgallery, "Number")
                        );
    if($db_gallery->nextRecord()) {
    	$is_public = $db_gallery->getField("public", "Number", true);
        $vgallery_name = $db_gallery->getField("name", "Text", true);    
	}
} else {
    $vgallery_title = ffTemplate::_get_word_by_code("addnew_vgallery"); 
}

if(check_function("get_update_by_service") && !set_interface_for_copy_by_service("vgallery", "VGalleryModify")) {
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "VGalleryModify";
	$oRecord->resources[] = $oRecord->id;
	//$oRecord->title = ffTemplate::_get_word_by_code("admin_vgallery_modify_title");
    
    $oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . Cms::getInstance("frameworkcss")->get("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $vgallery_title . '<span class="smart-url">' . $vgallery_name . '</span>' .'</h1>';

	$oRecord->addEvent("on_do_action", "VGalleryModify_on_do_action");
	$oRecord->addEvent("on_done_action", "VGalleryModify_on_done_action");
	$oRecord->src_table = "vgallery";
	$oRecord->auto_populate_edit = true;
	$oRecord->populate_edit_SQL = "SELECT vgallery.* 
										, (SELECT GROUP_CONCAT(vgallery_type.ID) 
											FROM vgallery_type 
											WHERE vgallery_type.is_dir_default = 0 
												AND vgallery_type.public = " . $db_gallery->toSql($is_public, "Number") . "
												AND FIND_IN_SET(vgallery_type.ID, vgallery.limit_type)
										) AS limit_type_node
										, (SELECT GROUP_CONCAT(vgallery_type.ID) 
											FROM vgallery_type 
											WHERE vgallery_type.is_dir_default > 0 
												AND vgallery_type.public = " . $db_gallery->toSql($is_public, "Number") . "
												AND FIND_IN_SET(vgallery_type.ID, vgallery.limit_type)
										) AS limit_type_dir
									FROM vgallery 
									WHERE vgallery.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
	$oRecord->additional_fields["last_update"] =  new ffData(time(), "Number");
	$oRecord->insert_additional_fields["status"] = new ffData("1", "Number");	
	$oRecord->user_vars["public"] = $is_public;
	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oRecord->addKeyField($oField);

	
    /***********
    *  Group General
    */

    $group_general = "general";
    $oRecord->addContent(null, true, $group_general); 

//        $oRecord->addTab($group_general);
//        $oRecord->setTabTitle($group_general, ffTemplate::_get_word_by_code("layout_" . $group_general));
    $oRecord->groups[$group_general] = array(
												"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_general)
												//, "title_class" => "dialogSubTitleTab dep-general"
												, "tab_dialog" => true
												, "cols" => 1
												, "class" => ""
                                              //   , "tab" => $group_general
                                              );


	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
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
                            			, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            			, ''
                            		)
                            		, vgallery_type.name
	                            ) AS name
		                    FROM vgallery_type 
		                    WHERE " . (OLD_VGALLERY 
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                                ) . "
                        		AND vgallery_type.public = " . $db_gallery->toSql($is_public, "Number") . "
                        		AND vgallery_type.is_dir_default = 0 
		                    [AND] [WHERE]
		                    [HAVING]
		                    ORDER BY name";
	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/type/extra?dir=0";
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
                            			, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            			, ''
                            		)
                            		, vgallery_type.name
	                            ) AS name
		                    FROM vgallery_type 
		                    WHERE " . (OLD_VGALLERY 
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                                ) . "
                        		AND (vgallery_type.public = " . $db_gallery->toSql($is_public, "Number") . "
                        				AND vgallery_type.is_dir_default > 0 
                        		)
                        		OR vgallery_type.name = 'Directory'
		                    [AND] [WHERE]
		                    [HAVING]
		                    ORDER BY name";
	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/type/extra?dir=1";
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
	
	$arrGroupSettings = array(
		"general" => "General"
	);

	$group_settings = "settings";
	
	$oRecord->addContent(null, true, $group_settings); 
	$oRecord->groups[$group_settings] = array(
										"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_settings)
										//, "title_class" => "dialogSubTitleTab dep-settings"
										, "tab_dialog" => true
										, "cols" => 1
										, "class" => ""
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

	$sSQL = "SELECT * 
			FROM " . FF_PREFIX . "languages
			WHERE " . FF_PREFIX . "languages.status > 0";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
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

	$arrGroupSettings["highlight"] = "Highlight";
	/*
	$oRecord->addContent(null, true, "Highlight"); 
	$oRecord->groups["Highlight"] = array(
										"title" => ffTemplate::_get_word_by_code("admin_vgallery_Highlight")
										, "cols" => 1
										, "class" => Cms::getInstance("frameworkcss")->get(array(12, 12, 12, 6), "col")
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
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/image/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
	$oField->resources[] = "ExtrasImageModify";
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "highlight_image_detail";
	$oField->container_class = "highlight";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_highlight_image_detail");
	$oField->widget = "activecomboex"; 
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/image/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
	$oField->resources[] = "ExtrasImageModify";
	$oRecord->addContent($oField, $group_settings);*/


	if(Cms::env("AREA_SHOW_ECOMMERCE")) {
		/*******************************
		* Ecommerce
		*/
		$arrGroupSettings["ecommerce"] = "Ecommerce";

/*		$oRecord->addContent(null, true, "Ecommerce"); 
		$oRecord->groups["Ecommerce"] = array(
											"title" => ffTemplate::_get_word_by_code("admin_vgallery_ecommerce")
											, "cols" => 1
											, "class" => ""
										 );	
*/
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_ecommerce";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_ecommerce");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
		$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
		$oRecord->addContent($oField, $group_settings);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_ecommerce_all_level";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_ecommerce_all_level");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
		$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
		$oRecord->addContent($oField, $group_settings);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "use_pricelist_as_item_thumb";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_pricelist_as_item_thumb");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oRecord->addContent($oField, $group_settings);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "use_pricelist_as_item_detail";
		$oField->container_class = "ecommerce";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_pricelist_as_item_detail");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oRecord->addContent($oField, $group_settings);
	}



	/*******************************
	* Notice
	*/
	$arrGroupSettings["notice"] = "Notice";	
	/*$oRecord->addContent(null, true, "Notice"); 
	$oRecord->groups["Notice"] = array(
										"title" => ffTemplate::_get_word_by_code("admin_vgallery_notice")
										, "cols" => 1
										, "class" => Cms::getInstance("frameworkcss")->get(array(12, 12, 12, 6), "col")
									 );	*/
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_email_notify_on_insert";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_email_notify_on_insert");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_email_notify_on_update";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_enable_email_notify_on_update");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "email_notify_show_detail";
	$oField->container_class = "notice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_email_notify_show_detail");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	/*******************************
	* BackOffice
	*/
	$arrGroupSettings["backoffice"] = "BackOffice";		
	/*$oRecord->addContent(null, true, "BackOffice"); 
	$oRecord->groups["BackOffice"] = array(
										"title" => ffTemplate::_get_word_by_code("admin_vgallery_backoffice")
										, "cols" => 1
										, "class" => Cms::getInstance("frameworkcss")->get(array(12, 12, 12, 6), "col")
									 );	*/

	$oField = ffField::factory($cm->oPage);
	$oField->id = "force_picture_link";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_force_picture_link");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "force_picture_ico_spacer";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_force_picture_ico_spacer");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "disable_dialog_in_edit";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_disable_dialog_in_edit");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "drag_sort_node_enabled";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_drag_sort_node_enabled");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "drag_sort_dir_enabled";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_drag_sort_dir_enabled");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "use_user_as_prefix_in_fs";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_use_user_as_prefix_in_fs");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "show_owner_in_grid";
	$oField->container_class = "backoffice";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_owner_in_grid");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oRecord->addContent($oField, $group_settings);

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
	$oRecord->addContent($oField, $group_settings);	

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
	$oRecord->addContent($oField, $group_settings);	

    $oField = ffField::factory($cm->oPage);
    $oField->id = "show_owner_by_categories";
    $oField->container_class = "backoffice";
    $oField->extended_type = "Selection";
    //$oField->base_type = "Number";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_show_owner_by_categories");
    $oField->widget = "autocompletetoken";
    $oField->autocompletetoken_combo = true;
    $oField->autocompletetoken_minLength = 0;
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
    $oRecord->addContent($oField, $group_settings);    
    
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
	$oRecord->addContent($oField, $group_settings);	

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
                        		AND vgallery_fields.ID_data_type = " .  $db_gallery->toSql($tbl_data_type["smart_url"]["relationship"]["ID"], "Number") . "
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
	$oField->default_value = new ffData("[PARENT]/[SMART_URL]");
	$oRecord->addContent($oField, $group_settings);	
	
	if($arrGroupSettings > 1) {
		foreach($arrGroupSettings AS $arrGroupSettings_key => $arrGroupSettings_value) {
			$str_group_settings .= '<li><a href="javascript:void(0);" rel="' . $arrGroupSettings_key . '">' . $arrGroupSettings_value . '</a></li>';
		}
		if(strlen($str_group_settings))
			$oRecord->groups[$group_settings]["fixed_pre_content"] .= '<ul class="menu-settings">' . $str_group_settings . '</ul><div class="settings-data"><h3 class="settings-title"></h3>';
			$oRecord->groups[$group_settings]["fixed_post_content"] = '</div>';
	}




/* DEPRECATO IN quanto le relazioni delle vgallery si fanno direttametne da vgallery nodes modify
	$oRecord->addContent(null, true, "AutoRel"); 
	$oRecord->groups["AutoRel"] = array(
										"title" => ffTemplate::_get_word_by_code("admin_vgallery_auto_relationship")
										, "cols" => 1
										, "class" => Cms::getInstance("frameworkcss")->get(array(12), "col")
									 );	
*/


/*
	$oDetail_rel = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
	$oDetail_rel->id = "VGalleryModifyRel";
	$oDetail_rel->title = ffTemplate::_get_word_by_code("admin_vgallery_modify_rel_title");
	$oDetail_rel->class = "ffDetails dep-vg-general";
	$oDetail_rel->src_table = "vgallery_rel";
	$oDetail_rel->order_default = "ID";
	$oDetail_rel->fields_relationship = array ("ID_vgallery" => "ID");
	$oDetail_rel->display_new_location = "Footer";
	$oDetail_rel->display_grid_location = "Footer";
	$oDetail_rel->min_rows = 1;
	$oDetail_rel->display_rowstoadd = false;
	//$oDetail_rel->addEvent("on_done_action", "LayoutModifyPath_on_done_action"); 

	$oDetail_rel->auto_populate_insert = true;
	$oDetail_rel->populate_insert_SQL = "SELECT 
		                                    'files' AS `tblsrc`
		                                    , (SELECT files.ID FROM files WHERE files.name = '' AND files.parent = '/') AS `items`
		                                    , '1' AS `cascading`";
	if($ID_vgallery) {
		$sSQL = "SELECT vgallery_rel.* FROM vgallery_rel WHERE vgallery_rel.ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number");
		$db_gallery->query($sSQL);
		if(!$db_gallery->nextRecord()) {
			$oDetail_rel->auto_populate_edit = true;
			$oDetail_rel->populate_edit_SQL = "SELECT 
				                                    'files' AS `tblsrc`
				                                    , (SELECT files.ID FROM files WHERE files.name = '' AND files.parent = '/') AS `items`
				                                    , '1' AS `cascading`";
		}
	}
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oDetail_rel->addKeyField($oField);


	$oField = ffField::factory($cm->oPage);
	$oField->id = "tblsrc";
	$oField->data_source = "contest_src";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_modify_rel_contest");
	$oField->widget = "activecomboex";
	$oField->multi_pairs = array (
		                        array(new ffData("files"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
		                        array(new ffData("vgallery_nodes"), new ffData(ffTemplate::_get_word_by_code("vgallery")))
		                   );      
	$oField->required = true;
	$oField->default_value = new ffData("files");
	$oField->actex_child = "items";
	$oDetail_rel->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "items";
	$oField->data_source = "ID_node_src";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_modify_rel_node");
	$oField->base_type = "Number";
	$oField->widget = "activecomboex"; 
	$oField->source_SQL = "
		                SELECT ID, path, type FROM 
		                (
		                    SELECT ID, path, type FROM
		                    (
		                        (
		                            SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'files' AS type
		                            FROM files
		                            WHERE files.is_dir > 0
		                        ) UNION (
		                            SELECT ID, vgallery_nodes.name AS path, 'vgallery_nodes' AS type
		                            FROM vgallery_nodes
		                            WHERE vgallery_nodes.parent = '/'
                                		AND vgallery_nodes.name <> ''
		                            " . ($ID_vgallery
		                                    ? "AND vgallery_nodes.ID_vgallery <> " . $db_gallery->toSql(new ffData($ID_vgallery, "Number")) 
		                                    : ""
		                            ) . "
		                        )
		                    ) AS sub_tbl_src
		                    ORDER BY type, path
		                ) AS tbl_src
		                [WHERE]";
	$oField->actex_father = "tblsrc";
	$oField->actex_related_field = "type";
	$oField->actex_update_from_db = true;
	$oField->required = true;

	$sSQL = "SELECT files.* FROM files WHERE files.name = '' AND files.parent = '/'";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$oField->default_value = $db_gallery->getField("ID", "Number");
	}
	$oDetail_rel->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "cascading";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_modify_rel_cascading");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number", LANGUAGE_INSET);
	$oField->unchecked_value = new ffData("0", "Number", LANGUAGE_INSET);
	$oField->default_value = $oField->checked_value;
	$oDetail_rel->addContent($oField);

	$oRecord->addContent($oDetail_rel, $group_general);
	$cm->oPage->addContent($oDetail_rel);
*/
	
	if(MASTER_CONTROL && $is_public) {
	    /***********
	    *  Group Public
	    */

	    $group_public = "public";
	    $oRecord->addContent(null, true, $group_public); 

	//        $oRecord->addTab($group_general);
	//        $oRecord->setTabTitle($group_general, ffTemplate::_get_word_by_code("layout_" . $group_general));
	    $oRecord->groups[$group_public] = array(
													"title" => ffTemplate::_get_word_by_code("vgallery_" . $group_public)
													//, "title_class" => "dialogSubTitleTab dep-public"
													, "tab_dialog" => true
													, "cols" => 1
													, "class" => ""
	                                              //   , "tab" => $group_general
	                                              );	
		//cover e description da inserire
		$oField = ffField::factory($cm->oPage);
		$oField->id = "public_cover";
		$oField->label = ffTemplate::_get_word_by_code("public_cover");
		$oField->base_type = "Text";
		$oField->control_type = "file";
		$oField->extended_type = "File";
		$oField->file_storing_path = FF_DISK_UPDIR . "/vgallery/[name_VALUE]";
		$oField->file_temp_path = FF_DISK_UPDIR . "/vgallery";
		$oField->file_allowed_mime = array();	                
		$oField->file_full_path = true;
		$oField->file_check_exist = true;
		$oField->file_show_filename = true; 
		$oField->file_show_delete = true;
		$oField->file_writable = false;
		$oField->file_normalize = true;
		$oField->file_show_preview = true;
		$oField->file_saved_view_url = CM_SHOWFILES . "/vgallery/[name_VALUE]/[_FILENAME_]";
		$oField->file_saved_preview_url = CM_SHOWFILES . "/100x100/vgallery/[name_VALUE]/[_FILENAME_]";
		
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
	
	
	$cm->oPage->addContent($oRecord);

	$cm->oPage->tplAddJs("ff.cms.admin", "ff.cms.admin.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools", false, $cm->isXHR());
	$js = '
		$(function() {
			ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . '/javascript/tools/ff.cms.admin.js", function() {
				ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());
				ff.cms.admin.makeNewUrl();
			});
			jQuery("#VGalleryModify_name").keyup(function(){
				ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());

				ff.cms.admin.makeNewUrl();
			});
			jQuery("#VGalleryModify_limit_level").keyup(function(){
				ff.cms.admin.checkLimitLevel(this);
				
				ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());
			});

			jQuery("#VGalleryModify .menu-settings A").click(function() {
				jQuery("#VGalleryModify FIELDSET.settings > DIV.settings-data > DIV").hide();
				jQuery("#VGalleryModify FIELDSET.settings > DIV.settings-data > DIV." + jQuery(this).attr("rel")).fadeIn();

				jQuery("#VGalleryModify .menu-settings A").removeClass("selected");
				jQuery(this).addClass("selected");
				
				jQuery("#VGalleryModify FIELDSET.settings .settings-title").text(jQuery(this).text());
			}); 
			jQuery("#VGalleryModify .menu-settings A:first").click();

		});';

    $js = '<script type="text/javascript">' . $js . '</script>';

    $cm->oPage->addContent($js);	
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
                $limit_type_ori = $component->form_fields["limit_type_node"]->value_ori->getValue()
                    . (strlen($component->form_fields["limit_type_dir"]->value_ori->getValue())
                        ? "," . $component->form_fields["limit_type_dir"]->value_ori->getValue()
                        : ""
                    );

                $limit_type = $component->form_fields["limit_type_node"]->getValue()
                    . (strlen($component->form_fields["limit_type_dir"]->getValue())
                        ? "," . $component->form_fields["limit_type_dir"]->getValue()
                        : ""
                    );
                if($limit_type != $limit_type_ori) {
                    $sSQL = "SELECT vgallery_nodes.ID FROM vgallery_nodes WHERE vgallery_nodes.ID_vgallery = " . $db->toSql($component->key_fields["ID"]->value);
                    $db->query($sSQL);
                    if ($db->nextRecord()) {
                        $component->tplDisplayError("Sono già presenti degli elementi in questa vgallery. Prima di cambiare la struttura è necessario eliminare tutti gli elementi");
                        return true;
                    }
                }

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
	                    
	                    
	                    $old_parent = stripslash("/" . $component->form_fields["name"]->value_ori->getValue());
	                    $new_parent = stripslash("/" . $component->form_fields["name"]->value->getValue());

			            $cache = get_session("cache");
			            foreach($cache["url"] AS $cache_url_key => $cache_url_value) {
            				if(strpos($cache_url_key, $old_parent) !== false) {
            					unset($cache["url"][$cache_url_key]);
							}
						}
			            set_session("cache", $cache);                    
	                    
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
	                }
	            break;
	        case "delete":
	            break;
	        case "confirmdelete":
	                if(check_function("delete_vgallery"))
	                    delete_vgallery("/", $component->form_fields["name"]->getValue(), $component->form_fields["name"]->getValue());
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
			$sSQL = "UPDATE vgallery SET vgallery.limit_type = " . $db->toSql($limit_type) . " WHERE vgallery.ID = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);    
		}    
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `vgallery`.last_update FROM vgallery WHERE vgallery.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number") . ") 
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . ")
                    )
                    OR
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("VGALLERY_MENU") . ")
                    )
                    OR
                    (
                        REPLACE(layout.value, " . $db->toSql("vgallery_") . ", '') = (SELECT publishing.ID FROM publishing WHERE publishing.contest  = " . $db->toSql($component->form_fields["name"]->value) . ") > 0 
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);*/
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
	                            , " . $db->toSql($component->key_fields["ID"]->value) . "
	                            , " . $db->toSql($component->form_fields["name"]->value) . "
	                            , '0'
	                            , " . $db->toSql(new ffData("/")) . "
	                            , " . $db->toSql($ID_type_dir, "Number") . "
	                            , '1'
	                            , " . $db->toSql(new ffData(time(), "Number")) . "
	                            , " . $db->toSql($visible, "Number") . "
		                        , " . $db->toSql(Auth::get("user")->id, "Number") . "
	                        )";
	            $db->execute($sSQL);
                $ID_node = $db->getInsertID(true);
                
                if(is_array($arrLang) && count($arrLang)) { 
                    check_function("update_vgallery_seo");
                    foreach($arrLang AS $lang_code => $lang) {
                        update_vgallery_seo($component->form_fields["name"]->getValue(), $component->key_fields["ID"]->getValue(), $lang["ID"], null, null, null, $visible, null, null, array(
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
                    $arrPermalink = set_field_permalink("vgallery_nodes", $component->key_fields["ID"]->getValue(), false, false, $component->form_fields["permalink_rule"]->getValue());
                

	            break;
	        case "update":  
	            $sSQL = "SELECT vgallery_nodes.ID 
                            , vgallery.status AS status
	            		FROM vgallery_nodes 
                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	            		WHERE vgallery_nodes.name = " . $db->toSql($component->form_fields["name"]->value_ori) . " 
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
		                            , " . $db->toSql($component->key_fields["ID"]->value) . "
		                            , " . $db->toSql($component->form_fields["name"]->value) . "
		                            , '0'
		                            , " . $db->toSql(new ffData("/")) . "
		                            , " . $db->toSql($ID_type_dir, "Number") . "
		                            , '1'
		                            , " . $db->toSql(new ffData(time(), "Number")) . "
		                            , '1'
		                            , " . $db->toSql(Auth::get("user")->id, "Number") . "
		                        )";
		            $db->execute($sSQL);
		            $ID_node = $db->getInsertID(true);
                    $actual_name = $component->form_fields["name"]->getValue();
                    $visible = 1;

				} else {
		            $actual_name = $component->form_fields["name"]->value->getValue();
		            $old_name = $component->form_fields["name"]->value_ori->getValue();
                    $ID_node = $db->getField("ID", "Number", true);
                    $visible = $db->getField("status", "Number", true);
		            if($actual_name != $old_name) {
			            $sSQL = "UPDATE vgallery_nodes 
			                    SET vgallery_nodes.name = " . $db->toSql($actual_name, "Text") . " 
			                    WHERE vgallery_nodes.name = " . $db->toSql($old_name, "Text");
			            $db->execute($sSQL);

			            $sSQL = "UPDATE `vgallery_nodes`
			                        SET vgallery_nodes.`parent`= (REPLACE(vgallery_nodes.parent, " . $db->toSql("/" . $old_name, "Text")  . ", " . $db->toSql("/" . $actual_name, "Text")  . "))
			                    WHERE                            
						            (vgallery_nodes.parent = " . $db->toSql("/" . $old_name)  . " 
						                OR vgallery_nodes.parent LIKE '" . $db->toSql("/" . $old_name, "Text", false)  . "/%'
						            )";
			            $db->execute($sSQL);

					}
				}

                if(is_array($arrLang) && count($arrLang)) {
                    check_function("update_vgallery_seo");
                    foreach($arrLang AS $lang_code => $lang) {
                        update_vgallery_seo($actual_name, $ID_node, $lang["ID"], null, null, null, $visible, null, null, array(
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
	            $name = $component->form_fields["name"]->getValue();

				$sSQL = "SELECT vgallery_nodes.ID
			    		FROM vgallery_nodes
				        WHERE vgallery_nodes.name = " . $db->toSql($name, "Text");
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_node = $db->getField("ID", "Number", true);
				}

				if(check_function("delete_vgallery"))
                    delete_vgallery("/", $name, $name);

		        //remove cache of relationship
		        $sSQL = "UPDATE vgallery_rel_nodes_fields SET 
		            		`nodes` = ''
		            	WHERE vgallery_rel_nodes_fields.`nodes` <> ''
		            		AND FIND_IN_SET(" . $db->toSql($ID_node, "Number") . ", vgallery_rel_nodes_fields.`nodes`)";
		        $db->execute($sSQL);	            
	           /* $sSQL = "DELETE FROM vgallery_rel_nodes_fields
	                    WHERE vgallery_rel_nodes_fields.ID_nodes IN 
	                        ( 
	                            SELECT vgallery_nodes.ID 
	                            FROM vgallery_nodes
	                            WHERE 
	                                vgallery_nodes.name = " . $db->toSql($name, "Text") . "
	                            OR
					            (vgallery_nodes.parent = " . $db->toSql("/" . $name)  . " 
					                OR vgallery_nodes.parent LIKE '" . $db->toSql("/" . $name, "Text", false)  . "/%'
					            )
					        )";
	            $db->execute($sSQL);  
	            
	            $sSQL = "DELETE FROM vgallery_nodes 
	                    WHERE 
	                        vgallery_nodes.name = " . $db->toSql($name, "Text") . "
	                    OR
				            (vgallery_nodes.parent = " . $db->toSql("/" . $name)  . " 
				                OR vgallery_nodes.parent LIKE '" . $db->toSql("/" . $name, "Text", false)  . "/%'
				            )";
	            $db->execute($sSQL); */
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