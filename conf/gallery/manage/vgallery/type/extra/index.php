<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_VGALLERY_TYPE_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        break;
    case "gallery":
        $src_table =  "files";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        break;
    default:
        $src_table = $src_type;
}

if(isset($_REQUEST["keys"]["ID"])) {
	$ID_vgallery_type = $_REQUEST["keys"]["ID"];
} else if(basename($cm->real_path_info)) {
	$sSQL = "SELECT " . $src_type . "_type.ID 
			FROM " . $src_type . "_type
			WHERE " . $src_type . "_type.name = " . $db_gallery->toSql(basename($cm->real_path_info));
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);

		$ID_vgallery_type = $_REQUEST["keys"]["ID"];
	}
} 



if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setpublic"]) && $ID_vgallery_type > 0) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "UPDATE " . $src_type . "_type 
            SET " . $src_type . "_type.public = " . $db_gallery->toSql($_REQUEST["setpublic"], "Number") . "
            WHERE " . $src_type . "_type.ID = " . $db_gallery->toSql($ID_vgallery_type, "Number");
    $db_gallery->execute($sSQL);

    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
}   

 if($ID_vgallery_type > 0 && $_REQUEST["frmAction"] == "clone") {
	$clone_schema = array(
					$src_type => array(
							$src_type . "_type" => array("compare_key" => "ID", "return_ID" => "")
							, $src_type . "_fields" => array("compare_key" => "ID_type", "use_return_ID" => "ID_type") 
						)
					);

	if(check_function("clone_by_schema"))
		clone_by_schema($ID_vgallery_type, $clone_schema, "vgtype");

	if($_REQUEST["XHR_DIALOG_ID"]) {
		die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => true, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
	} else {
		die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => true, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
		//ffRedirect($_REQUEST["ret_url"]);
	}
}    
 
 
 
 
if(check_function("get_update_by_service") && !set_interface_for_copy_by_service($src_type . "_type", "VGalleryTypeModify", (isset($_REQUEST["dir"]) ? array("is_dir_default" => $_REQUEST["dir"]) : null))) {
	$sSQL = "SELECT ID FROM vgallery_fields_data_type WHERE 1";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		do {
			$arrDataType[$db_gallery->getField("name", "Text", true)] = $db_gallery->getField("ID", "Number", true);
			$arrDataTypeRev[$db_gallery->getField("ID", "Number", true)] = $db_gallery->getField("name", "Text", true);
		} while($db_gallery->nextRecord());
	}

	$sSQL = "SELECT ID, name FROM check_control WHERE 1";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		do {
			$arrControlType[$db_gallery->getField("name", "Text", true)] = $db_gallery->getField("ID", "Number", true);
			$arrControlTypeRev[$db_gallery->getField("ID", "Number", true)] = $db_gallery->getField("name", "Text", true);
		} while($db_gallery->nextRecord());
	}

	$sSQL = "SELECT ID, name FROM extended_type WHERE 1";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		do {
			$arrExtType[$db_gallery->getField("name", "Text", true)] = $db_gallery->getField("ID", "Number", true);
			$arrExtTypeRev[$db_gallery->getField("ID", "Number", true)] = $db_gallery->getField("name", "Text", true);
		} while($db_gallery->nextRecord());
	}

	$is_public = false; 
	if($ID_vgallery_type > 0) {
		$sSQL_string = " AND " . $src_type . "_type_group.ID_type = " . $db_gallery->toSql($ID_vgallery_type);

			if(check_function("get_vgallery_type_group")) {
                $arrGroup = get_vgallery_type_group($ID_vgallery_type, "backoffice");

                if(is_array($arrGroup) && count($arrGroup)) {
                	foreach($arrGroup AS $group_key => $group_value) {
                		if($group_value["is_system"])
                			$arrGroupSystem[] = $group_value["ID"];
                	}
                }
                
                if(is_array($arrGroupSystem) && count($arrGroupSystem)) {
                	$sSQL_string .= " AND " . $src_type . "_type_group.ID NOT IN(" . $db_gallery->toSql(implode(",", array_filter($arrGroupSystem)), "Text", false) . ")";
                
                }
			}
	    $vgallery_type_title = ffTemplate::_get_word_by_code("modify_" . $src_type . "_type"); 
		
	    
    	$sSQL = "SELECT " . $src_type . "_type.* FROM " . $src_type . "_type WHERE " . $src_type . "_type.ID = " . $db_gallery->toSql($ID_vgallery_type);
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$is_public = $db_gallery->getField("public", "Number", true);
			$vgallery_type_name = $db_gallery->getField("name", "Text", true);    
		}
	} else {
    	$vgallery_type_title = ffTemplate::_get_word_by_code("addnew_" . $src_type . "_type"); 
	}

	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "VGalleryTypeModify";
	$oRecord->resources[] = $oRecord->id;
	//$oRecord->title = ffTemplate::_get_word_by_code("vgallery_type_modify");

	$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . Cms::getInstance("frameworkcss")->get("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $vgallery_type_title . '<span class="smart-url">' . $vgallery_type_name . '</span>' .'</h1>';
	
	$oRecord->src_table = $src_type . "_type";
	$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("next");
	$oRecord->display_required_note = FALSE;
	$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
	$oRecord->insert_additional_fields["tags_in_keywords"] = new ffData("1", "Number");

	$oRecord->addEvent("on_done_action", "VGalleryTypeModify_on_done_action");
    $oRecord->addEvent("on_do_action", "VGalleryTypeModify_on_do_action");
    
	$oRecord->user_vars["src_type"] = $src_type;
    $oRecord->user_vars["ID_vgallery_type"] = $ID_vgallery_type;
    
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oRecord->addKeyField($oField);

	/***********
	*  Group General
	*/

        
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort");
	$oField->id = "sort_default";
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT NULL, ID, name FROM vgallery_fields WHERE vgallery_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number");
	$oRecord->addContent($oField, $group_general);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sort_method_default";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_method");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
		                        array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ascending"))),
		                        array(new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("discending")))
		                   );
	$oRecord->addContent($oField, $group_general);	
*/


	if(!$ID_vgallery_type) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
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
		/***********
		*  Group Field
		*/	
		//$group_field = "fields";
		//$oRecord->addContent(null, true, $group_field); 
	        
	        
	        
	        
	//    $oRecord->addTab($group_field);
	//    $oRecord->setTabTitle($group_field, ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_field));
/*
   		$oRecord->groups[$group_field] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_type_" . $group_field)
											//, "title_class" => "dialogSubTitleTab dep-fields"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											//, "tab" => $group_field
										 );	*/
	
	    $label_width = 8;
	    /**
	    * Field Thumb
	    */
		$group_field = "thumb";
		$oRecord->addContent(null, true, $group_field); 
	        
	   // $oRecord->addTab($group_field);
	    //$oRecord->setTabTitle($group_field, Cms::getInstance("frameworkcss")->get("th-large", "icon-tag") . ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_field));

   		$oRecord->groups[$group_field] = array(
											"title" => Cms::getInstance("frameworkcss")->get("th-large", "icon-tag") . ffTemplate::_get_word_by_code("vgallery_type_" . $group_field)
											//"title_class" => "dialogSubTitleTab dep-fields notab"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											, "tab" => $group_field
										 );    
	    
	    $oGrid_thumb = ffGrid::factory($cm->oPage);
	    $oGrid_thumb->full_ajax = true;
	    $oGrid_thumb->id = "VGalleryFieldsThumb";
	    //$oGrid_thumb->title = ffTemplate::_get_word_by_code("section_title");
		
	    $oGrid_thumb->source_SQL = "SELECT " . $src_type . "_fields.* 
    							, " . $src_type . "_fields.enable_" . $group_field . " AS `visible`
	                            , " . $src_type . "_fields.order_" . $group_field . " AS `order`
	                            , " . $src_type . "_fields.parent_" . $group_field . " AS `parent`
								, " . $src_type . "_fields.display_view_mode_" . $group_field . " AS `display_view_mode`
								, " . $src_type . "_fields.enable_" . $group_field . "_empty AS `enable_empty`
	                            , " . $src_type . "_fields.enable_" . $group_field . "_label AS `enable_label`
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src_type . "_fields.ID_" . $group_field . "_htmltag
			                        ), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src_type . "_fields.ID_label_" . $group_field . "_htmltag
			                        ), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag_label
	                            , IFNULL(
                            		(SELECT DISTINCT
		                                " . CM_TABLE_PREFIX . "showfiles_modes.name
		                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
		                            WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . $src_type . "_fields.settings_type_" . $group_field . "
									), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("file_fullsize")) . "
	                            ) AS settings_type
	                            , IF(" . $src_type . "_fields.custom_" . $group_field . "_field
                            		, 1
                            		, 0
	                            ) AS `custom_field`
								, '' AS grid_label
								, '' AS grid_field
	                            , IF(" . $src_type . "_fields.fixed_pre_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `pre_content`
	                            , IF(" . $src_type . "_fields.fixed_post_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `post_content`
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                        FROM " . $src_type . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src_type . "_fields.ID_extended_type
	                        WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
	                        [AND] [WHERE] 
	                        [ORDER]";	
	    $oGrid_thumb->order_default = "ID";
	    $oGrid_thumb->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
		$oGrid_thumb->addit_record_param = "src=" . $src_type . "&sel=thumb&";
		$oGrid_thumb->addit_insert_record_param = "type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=thumb&";
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
	                "resource_id" => $src_type . "_fields_" . $group_field
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	            )
	            , "ID"
	        )
	    );
	    $oGrid_thumb->display_edit_bt = true;
	    $oGrid_thumb->display_edit_url = false;
	    $oGrid_thumb->buttons_options["export"]["display"] = false;
	    $oGrid_thumb->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_thumb->user_vars["src_type"] = $src_type;
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
	    $oField->id = "name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?[KEYS]limit=source&type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=thumb";
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
	    
	    $oRecord->addContent($oGrid_thumb, $group_field);
	    $cm->oPage->addContent($oGrid_thumb);
	    
	    /**
	    * Field Detail
	    */
		$group_field = "detail";
		$oRecord->addContent(null, true, $group_field); 
	        
	    //$oRecord->addTab($group_field);
	    //$oRecord->setTabTitle($group_field, Cms::getInstance("frameworkcss")->get("file", "icon-tag") . ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_field));

   		$oRecord->groups[$group_field] = array(
											"title" => Cms::getInstance("frameworkcss")->get("file", "icon-tag") . ffTemplate::_get_word_by_code("vgallery_type_" . $group_field)
											//"title_class" => "dialogSubTitleTab dep-fields notab"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											, "tab" => $group_field
										 );    
	    
	    $oGrid_detail = ffGrid::factory($cm->oPage);
	    $oGrid_detail->full_ajax = true;
	    $oGrid_detail->id = "VGalleryFieldsDetail";
	    //$oGrid_detail->title = ffTemplate::_get_word_by_code("section_title");
			
	    $oGrid_detail->source_SQL = "SELECT " . $src_type . "_fields.* 
    							, " . $src_type . "_fields.enable_" . $group_field . " AS `visible`
	                            , " . $src_type . "_fields.order_" . $group_field . " AS `order`
	                            , " . $src_type . "_fields.parent_" . $group_field . " AS `parent`
								, " . $src_type . "_fields.display_view_mode_" . $group_field . " AS `display_view_mode`
	                            , " . $src_type . "_fields.enable_" . $group_field . "_empty AS `enable_empty`
	                            , " . $src_type . "_fields.enable_" . $group_field . "_label AS `enable_label`
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src_type . "_fields.ID_" . $group_field . "_htmltag
			                        ), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag
	                            , IFNULL(
                            		(SELECT DISTINCT
										IF(vgallery_fields_htmltag.attr = ''
											, vgallery_fields_htmltag.tag
											, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
										) AS name
			                        FROM 
			                            vgallery_fields_htmltag
			                        WHERE vgallery_fields_htmltag.ID = " . $src_type . "_fields.ID_label_" . $group_field . "_htmltag
			                        ), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("default_htmltag")) . "
		                        ) AS htmltag_label
	                            , IFNULL(
                            		(SELECT DISTINCT
		                                " . CM_TABLE_PREFIX . "showfiles_modes.name
		                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
		                            WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . $src_type . "_fields.settings_type_" . $group_field . "
									), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("file_fullsize")) . "
	                            ) AS settings_type
	                            , IF(" . $src_type . "_fields.custom_" . $group_field . "_field
                            		, 1
                            		, 0
	                            ) AS `custom_field`
								, '' AS grid_label
								, '' AS grid_field
	                            , IF(" . $src_type . "_fields.fixed_pre_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `pre_content`
	                            , IF(" . $src_type . "_fields.fixed_post_content_" . $group_field . "
                            		, 1
                            		, 0
	                            ) AS `post_content`
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                        FROM " . $src_type . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src_type . "_fields.ID_extended_type
	                        WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
	                        [AND] [WHERE] 
	                        [ORDER]";	
	    $oGrid_detail->order_default = "ID";
	    $oGrid_detail->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
		$oGrid_detail->addit_record_param = "src=" . $src_type . "&sel=detail&";
		$oGrid_detail->addit_insert_record_param = "type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=detail&";
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
	                "resource_id" => $src_type . "_fields_" . $group_field
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	            )
	            , "ID"
	        )
	    );

	    $oGrid_detail->display_edit_bt = true;
	    $oGrid_detail->display_edit_url = false;
	    $oGrid_detail->buttons_options["export"]["display"] = false;
	    $oGrid_detail->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_detail->user_vars["src_type"] = $src_type;
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
	    $oField->id = "name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?[KEYS]limit=source&type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=detail";
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
		
	    $oRecord->addContent($oGrid_detail, $group_field);
	    $cm->oPage->addContent($oGrid_detail);
	    
	    
		/**
	    * Field BackOffice
	    */
		$group_field = "backoffice";
		$oRecord->addContent(null, true, $group_field); 
	        
	    //$oRecord->addTab($group_field);
	    //$oRecord->setTabTitle($group_field, Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_field));

   		$oRecord->groups[$group_field] = array(
											"title" => Cms::getInstance("frameworkcss")->get("edit", "icon-tag") . ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_field)
											//"title_class" => "dialogSubTitleTab dep-fields notab"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											, "tab" => $group_field
										 );  

 		$oGrid_backoffice = ffGrid::factory($cm->oPage);
	    $oGrid_backoffice->full_ajax = true;
	    $oGrid_backoffice->id = "VGalleryFieldsBackOffice";
	    //$oGrid_backoffice->title = ffTemplate::_get_word_by_code("section_title");

	    $oGrid_backoffice->source_SQL = "SELECT " . $src_type . "_fields.* 
	                            , " . $src_type . "_fields.order_" . $group_field . " AS `order`
	                            , IFNULL(
                            		(SELECT DISTINCT vgallery_type_group.name
                            			FROM vgallery_type_group
                            			WHERE vgallery_type_group.ID = " . $src_type . "_fields.ID_group_" . $group_field . "
                            		), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("nothing")) . "
	                            ) AS `parent`
								, IFNULL(
									(SELECT DISTINCT check_control.name 
									FROM check_control 
									WHERE check_control.ID = " . $src_type . "_fields.ID_check_control
									), " . $db_gallery->toSql(ffTemplate::_get_word_by_code("no")) . " 
								) AS check_control
	                            , extended_type.name AS extended_type
	                            , extended_type.ff_name AS ff_extended_type
	                            , extended_type.`group` AS extended_group
	                            , IF(vgallery_fields_data_type.name = 'table.alt'
									, CONCAT(" . $src_type . "_fields.data_source, " . $src_type . "_fields.data_limit)
									, " . $src_type . "_fields.ID
								) AS group_data
	                        FROM " . $src_type . "_fields
                        		INNER JOIN extended_type ON extended_type.ID = " . $src_type . "_fields.ID_extended_type
                        		INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = " . $src_type . "_fields.ID_data_type
	                        WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
	                        [AND] [WHERE] 
							GROUP BY group_data
	                        [ORDER]";	
	    $oGrid_backoffice->order_default = "ID";
	    $oGrid_backoffice->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
		$oGrid_backoffice->addit_record_param = "src=" . $src_type . "&sel=backoffice&";
		$oGrid_backoffice->addit_insert_record_param = "type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=backoffice&";
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
	                "resource_id" => $src_type . "_fields_" . $group_field
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	            )
	            , "ID"
	        )
	    );
	    $oGrid_backoffice->display_edit_bt = true;
	    $oGrid_backoffice->display_edit_url = false;
	    $oGrid_backoffice->buttons_options["export"]["display"] = false;
	    $oGrid_backoffice->addEvent("on_before_parse_row", "VGalleryFields_on_before_parse_row");
	    $oGrid_backoffice->user_vars["src_type"] = $src_type;
	    $oGrid_backoffice->user_vars["limit"] = "";

	    // Campi chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oField->order_SQL = "`order`, ID";
	    $oGrid_backoffice->addKeyField($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "name";
	    $oField->display_label = false;
	    $oField->encode_entities = false;
	    $oField->url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?[KEYS]limit=source&type=" . $ID_vgallery_type . "&src=" . $src_type . "&sel=backoffice";
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

		$sSQL = "SELECT * 
				FROM " . FF_PREFIX . "languages
				WHERE " . FF_PREFIX . "languages.status > 0";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "disable_multilang";
			$oField->label = ffTemplate::_get_word_by_code("vgallery_field_disable_multilang");
			$oField->encode_entities = false;
		    $oField->setWidthLabel($label_width);
			$oGrid_backoffice->addContent($oField, false, 2, 2);   
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
		
	    $oRecord->addContent($oGrid_backoffice, $group_field);
	    $cm->oPage->addContent($oGrid_backoffice);


	
		/***********
		*  Group SEO
		*/	
		$group_seo = "seo";
		$oRecord->addContent(null, true, $group_seo); 
	//    $oRecord->addTab($group_seo);
	//    $oRecord->setTabTitle($group_seo, ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_seo));

   		$oRecord->groups[$group_seo] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_type_" . $group_seo)
											//, "title_class" => "dialogSubTitleTab dep-seo"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											//, "tab" => $group_seo
										 );    

		$oField = ffField::factory($cm->oPage);
		$oField->id = "smart_url";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_smart_url");
		$oField->extended_type = "Selection";
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->required = true;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";		
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.enable_smart_url > 0
				ORDER BY " . $src_type . "_fields.enable_smart_url, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_seo);	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "meta_description";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_meta_description");
		$oField->extended_type = "Selection";
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->required = true;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.meta_description > 0
				ORDER BY " . $src_type . "_fields.meta_description, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_seo);	
		
		if($src_type == "vgallery") {
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
		$group_displayrule = "displayrule";
		$oRecord->addContent(null, true, $group_displayrule); 
	//    $oRecord->addTab($group_displayrule);
	//    $oRecord->setTabTitle($group_displayrule, ffTemplate::_get_word_by_code($src_type . "_fields_" . $group_displayrule));

   		$oRecord->groups[$group_displayrule] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_type_" . $group_displayrule)
											//, "title_class" => "dialogSubTitleTab dep-rule"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
											//, "tab" => $group_seo
										 );  

		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
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
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.enable_in_menu > 0
				ORDER BY " . $src_type . "_fields.enable_in_menu, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_grid";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_grid");
		$oField->extended_type = "Selection";
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.enable_in_grid > 0
				ORDER BY " . $src_type . "_fields.enable_in_grid, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_in_mail";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_mail");
		$oField->extended_type = "Selection";
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.enable_in_mail > 0
				ORDER BY " . $src_type . "_fields.enable_in_mail, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		if(Cms::env("AREA_SHOW_ECOMMERCE") && $src_type == "vgallery") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "enable_in_cart";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_cart");
			$oField->extended_type = "Selection";
			$oField->widget = "autocompletetoken";
			$oField->data_type = "";
			$oField->store_in_db = false;
			$oField->autocompletetoken_minLength = 0;
			$oField->autocompletetoken_theme = "";
			$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
			$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
			$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
			$oField->autocompletetoken_label = "";
			$oField->autocompletetoken_combo = true;
			$oField->autocompletetoken_compare = "name";
			$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
										, " . $src_type . "_fields.name
									FROM " . $src_type . "_fields
									WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
									[AND][WHERE]
									[HAVING]
									[ORDER] [COLON] name
									[LIMIT]";
			$default_value = "";
			$sSQL = "SELECT " . $src_type . "_fields.* 
					FROM " . $src_type . "_fields
					WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
						AND " . $src_type . "_fields.enable_in_cart > 0
					ORDER BY " . $src_type . "_fields.enable_in_cart, " . $src_type . "_fields.`order_thumb`";
			$db_gallery->query($sSQL);
			if($db_gallery->nextRecord()) {
				$arrDefaultField = array();
				do {
					$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
				} while($db_gallery->nextRecord());
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
		$oField->widget = "autocompletetoken";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->autocompletetoken_minLength = 0;
		$oField->autocompletetoken_theme = "";
		$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
		$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
		$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
		$oField->autocompletetoken_label = "";
		$oField->autocompletetoken_combo = true;
		$oField->autocompletetoken_compare = "name";
		$oField->source_SQL = "SELECT " . $src_type . "_fields.ID
									, " . $src_type . "_fields.name
								FROM " . $src_type . "_fields
								WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
								[AND][WHERE]
								[HAVING]
								[ORDER] [COLON] name
								[LIMIT]";
		$default_value = "";
		$sSQL = "SELECT " . $src_type . "_fields.* 
				FROM " . $src_type . "_fields
				WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number") . "
					AND " . $src_type . "_fields.enable_in_document > 0
				ORDER BY " . $src_type . "_fields.enable_in_document, " . $src_type . "_fields.`order_thumb`";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$arrDefaultField = array();
			do {
				$arrDefaultField[] = $db_gallery->getField("ID", "Number", true);
			} while($db_gallery->nextRecord());
			$default_value = implode(",", $arrDefaultField);
			$oField->default_value = new ffData($default_value);
		}
		$oField->user_vars["default_value"] = $default_value;
		$oRecord->addContent($oField, $group_displayrule);

		
		
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "vgalleryTypeGroup";
		$oButton->aspect = "link"; 
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_group");
		if($_REQUEST["XHR_DIALOG_ID"])
		{ 
			$cm->oPage->widgetLoad("dialog");
			$cm->oPage->widgets["dialog"]->process(
				"vgalleryTypeGroup"
				, array(
					"url" => $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/group?ID_type=" . $ID_vgallery_type
				)
				, $cm->oPage
			);
			$oButton->jsaction = "ff.ffPage.dialog.doOpen('vgalleryTypeGroup')";
		} else {
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/group?ID_type=" . $_REQUEST["keys"]["ID"];
		}
		$oRecord->addActionButton($oButton);		



		if(MASTER_CONTROL && $is_public) {
		    /***********
		    *  Group Public
		    */

		    $group_public = "public";
		    $oRecord->addContent(null, true, $group_public); 

		//        $oRecord->addTab($group_general);
		//        $oRecord->setTabTitle($group_general, ffTemplate::_get_word_by_code("layout_" . $group_general));
		    $oRecord->groups[$group_public] = array(
														"title" => ffTemplate::_get_word_by_code("vgallery_type_" . $group_public)
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
		    $oField->file_storing_path = FF_DISK_UPDIR . "/" . $src_type . "-type/[name_FATHER]";
		    $oField->file_temp_path = FF_DISK_UPDIR . "/tmp/" . $src_type . "-type";
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
	
	$cm->oPage->tplAddJs("ff.cms.admin", "ff.cms.admin.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools", false, $cm->isXHR());
	$js = '
		$(function() {
			/*displayGroupButton();
			jQuery(document).on("click", "#VGalleryTypeModify_advanced_group[type=checkbox]", function() {
				displayGroupButton();
			});
			
			function displayGroupButton() {
				jQuery("#VGalleryTypeModify_vgalleryTypeGroup").hide();
				if(jQuery("#VGalleryTypeModify_advanced_group[type=checkbox]").is(":checked")) {
					jQuery("#VGalleryTypeModify_vgalleryTypeGroup").show();
				}
			}*/
			jQuery("#VGalleryTypeModify .showall.thumb").click(function() {
				var iconClass = jQuery("i", this).attr("class");
				if(iconClass.indexOf("minus") >= 0) {
					iconClass = iconClass.replace("minus", "plus");
				} else {
					iconClass = iconClass.replace("plus", "minus");
				}
				jQuery("i", this).attr("class", iconClass);
				jQuery(this).closest("FIELDSET.thumb").find("TR.hideable").toggleClass("hidden");
			});
			jQuery("#VGalleryTypeModify .showall.detail").click(function() {
				var iconClass = jQuery("i", this).attr("class");
				if(iconClass.indexOf("minus") >= 0) {
					iconClass = iconClass.replace("minus", "plus");
				} else {
					iconClass = iconClass.replace("plus", "minus");
				}
				jQuery("i", this).attr("class", iconClass);
				jQuery(this).closest("FIELDSET.detail").find("TR.hideable").toggleClass("hidden");
			});
			
			ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . '/javascript/tools/ff.cms.admin.js", function() {
				ff.cms.admin.makeNewUrl();
			});
			
			jQuery("#VGalleryTypeModify_name").keyup(function(){
				ff.cms.admin.makeNewUrl();
			});
                    
                    
            });';
    $js = '<script type="text/javascript">' . $js . '</script>';

    $cm->oPage->addContent($js);		
}


function VGalleryFields_on_before_parse_row($component)
{
    $check_enabled = Cms::getInstance("frameworkcss")->get("check-square-o", "icon");
    $check_disabled = Cms::getInstance("frameworkcss")->get("square-o", "icon");
    $enable_field = "";
    if(isset($component->db[0]->fields["visible"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setvisible', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("visible", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
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
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setempty', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("enable_empty", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
    	if($component->db[0]->getField("enable_empty", "Number", true)) {
    		$component->grid_fields["enable_empty"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["enable_empty"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }
    if(isset($component->grid_fields["enable_label"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setlabel', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("enable_label", "Number", true) . "&limit=" . $component->user_vars["limit"] . "'});";
    	if($component->db[0]->getField("enable_label", "Number", true)) {
    		$component->grid_fields["enable_label"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["enable_label"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }    	
    if(isset($component->grid_fields["require"])) {
		$url_action = "javascript:ff.ajax.doRequest({'action': 'setrequire', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("require", "Number", true) . "&limit=" . '' . "'});";
    	if($component->db[0]->getField("require", "Number", true)) {
    		$component->grid_fields["require"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["require"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    } 
    if(isset($component->grid_fields["disable_multilang"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'setmultilang', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("disable_multilang", "Number", true) . "&limit=" . '' . "'});";
    	if($component->db[0]->getField("disable_multilang", "Number", true)) {
    		$component->grid_fields["disable_multilang"]->setValue('<a href="' . $url_action . '" class="' . $check_enabled . '"></a>');
    	} else {
    		$component->grid_fields["disable_multilang"]->setValue('<a href="' . $url_action . '" class="' . $check_disabled . '"></a>');
    	}
    }  
    if(isset($component->grid_fields["enable_tip"])) {
    	$url_action = "javascript:ff.ajax.doRequest({'action': 'settip', 'fields': [], 'url': '" . ffCommon_specialchars($component->record_url) . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . "&src=" . $component->user_vars["src_type"] . "&value=" . !$component->db[0]->getField("enable_tip", "Number", true) . "&limit=" . '' . "'});";
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
	$src_type =  $component->user_vars["src_type"];
	
    switch ($action) {
        case "insert":
        case "update":
            if(isset($component->user_vars["ID_vgallery_type"]) && $component->user_vars["ID_vgallery_type"] > 0)
            {
                if(0)
                {
                    $sSQL = "SELECT " . $src_type . "_type_group.*
                                FROM " . $src_type . "_type_group
                                WHERE " . $src_type . "_type_group.ID_type = " . $db->toSql($component->user_vars["ID_vgallery_type"], "Number");
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
    
   $src_type =  $component->user_vars["src_type"];
    
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
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_smart_url = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.enable_smart_url = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["meta_description"])) {
    				$meta_description_old = $component->form_fields["meta_description"]->user_vars["default_value"];
    				$meta_description_new = $component->form_fields["meta_description"]->getValue();

					$arrField = explode(",", $meta_description_new);
					if(is_array($arrField) && count($arrField)) {
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.meta_description = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.meta_description = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
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
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_menu = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.enable_in_menu = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
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
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_grid = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.enable_in_grid = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(isset($component->form_fields["enable_in_mail"])) {
					$field_old = $component->form_fields["enable_in_mail"]->user_vars["default_value"];
					$field_new = $component->form_fields["enable_in_mail"]->getValue();
					
					if(!strlen($field_new)) {
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_mail = 1 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					} else {
						$arrField = explode(",", $field_new);
						if(is_array($arrField) && count($arrField)) {
							$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_mail = 0 
									WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						
							foreach($arrField AS $arrField_key => $arrField_value) {
								$sSQL = "UPDATE " . $src_type . "_fields SET 
											" . $src_type . "_fields.enable_in_mail = " . $db->toSql($arrField_key + 1, "Number") . "
										WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
											AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
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
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_cart = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.enable_in_cart = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
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
						$sSQL = "UPDATE " . $src_type . "_fields SET " . $src_type . "_fields.enable_in_document = 0 
								WHERE " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
					
						foreach($arrField AS $arrField_key => $arrField_value) {
							$sSQL = "UPDATE " . $src_type . "_fields SET 
										" . $src_type . "_fields.enable_in_document = " . $db->toSql($arrField_key + 1, "Number") . "
									WHERE " . $src_type . "_fields.ID = " . $db->toSql($arrField_value, "Number") . "
										AND " . $src_type . "_fields.ID_type = " . $db->toSql($component->key_fields["ID"]->value, "Number");
							$db->execute($sSQL);
						}
					}
				}
				

    			break;
    		default:
    	}
    
    
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `vgallery_type`.last_update FROM vgallery_type WHERE vgallery_type.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number") . ") 
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
                        layout.value LIKE '%" . $db->toSql("vgallery", "Text", false) . "%'
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);
	}
}
?>