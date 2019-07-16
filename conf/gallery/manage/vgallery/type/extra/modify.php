<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_VGALLERY_TYPE_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$sSQL = "SELECT cm_layout.* 
			FROM cm_layout 
			WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if ($db_gallery->nextRecord()) {
    $framework_css = Cms::getInstance("frameworkcss")->getFramework($db_gallery->getField("framework_css", "Text", true));
    $framework_css_name = $framework_css["name"];
}

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

$group_sel = $_REQUEST["sel"];

switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        $skip_lang = true;

        $src_data_source_mono =  "anagraph";
        $src_data_source_multi =  "vgallery_nodes";
        break;
    case "gallery":
        $src_table =  "files";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        
        $src_data_source_mono =  "vgallery_nodes";
        $src_data_source_multi =  "anagraph";
        break;
    default:
        $src_table = $src_type;
}

switch($_REQUEST["limit"]) {
	case "thumb":
		$field_type = "thumb";
		break;
	case "detail":
		$field_type = "detail";
		break;
	case "source":
		$group_limit[] = "source";
		break;
	default:
}

$ID_type = $_REQUEST["type"];
if(!isset($_REQUEST["keys"]["ID"])) {
    if(!strlen(basename($cm->real_path_info)) && isset($_REQUEST["name"]))
    	$cm->real_path_info = "/" . $_REQUEST["name"];

    $db_gallery->query("SELECT " . $src_type . "_fields.*
                            FROM " . $src_type . "_fields
                            WHERE " . $src_type . "_fields.name = " . $db_gallery->toSql(basename($cm->real_path_info))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
        $ID_type = $db_gallery->getField("ID_type", "Number", true);
    }
}

/**
* Actions Service
*/
if(isset($_REQUEST["keys"]["ID"]) > 0 && isset($_REQUEST["frmAction"]) && isset($_REQUEST["value"])) {
	switch($_REQUEST["frmAction"]) {
		case "setvisible":
			if($field_type)
				$field_name = "enable_" . $field_type;
			break;
		case "setempty":
			if($field_type)
				$field_name = "enable_" . $field_type . "_empty";
			break;
		case "setlabel":
			if($field_type)
				$field_name = "enable_" . $field_type . "_label";
			break;
		case "setrequire":
			$field_name = "require";
			break;
		case "setmultilang":
			$field_name = "disable_multilang";
			break;
		case "settip":
			$field_name = "enable_tip";
			break;
		default:
	}
	
	if($field_name) {
		$sSQL = "UPDATE " . $src_type . "_fields 
		                SET `" . $db_gallery->toSql($field_name, "Text", false) . "` = " . $db_gallery->toSql($_REQUEST["value"]) . "
		                WHERE " . $src_type . "_fields.ID = " . $db_gallery->tosql($_REQUEST["keys"]["ID"], "Number");
		$db_gallery->execute($sSQL);
	}

	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("fields" . $field_type)), true));
	} else {
	    die(ffCommon_jsonenc(array( "close" => false, "refresh" => true, "resources" => array("fields" . $field_type)), true));
	}
} 


$display_addnew = false;
if(!isset($_REQUEST["keys"]["ID"])) {
	if(isset($_REQUEST["field"])) {
		$copy_field = $_REQUEST["field"];
	} else {
		$display_addnew = true;
	}
}

if($display_addnew) {
	$vgallery_field_title = ffTemplate::_get_word_by_code("create_vgallery_field_from");
} else {
	if($_REQUEST["keys"]["ID"]) {
		$vgallery_field_title = ffTemplate::_get_word_by_code("modify_vgallery_field");
    	
    	$sSQL = "SELECT " . $src_type . "_fields.* FROM " . $src_type . "_fields WHERE " . $src_type . "_fields.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$vgallery_field_name = $db_gallery->getField("name", "Text", true); 
        }		
	} elseif($copy_field) {
		$vgallery_field_title = ffTemplate::_get_word_by_code("cloned_vgallery_field");

    	$sSQL = "SELECT " . $src_type . "_fields.* FROM " . $src_type . "_fields WHERE " . $src_type . "_fields.ID = " . $db_gallery->toSql($copy_field, "Number");
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$vgallery_field_name = $db_gallery->getField("name", "Text", true);    
		}		
	} else {
		$vgallery_field_title = ffTemplate::_get_word_by_code("addnew_vgallery_field");
	}
}


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FieldModify";
/*
if(!$display_addnew) {
	$oRecord->template_file = "ffRecord_extra_modify.html";
	if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents/admin/vgallery/vgallery-type/ffRecord_extra_modify.html")) { 
		$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents/admin/vgallery/vgallery-type";
	} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/contents/admin/vgallery/vgallery-type/ffRecord_extra_modify.html")) {
		$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/contents/admin/vgallery/vgallery-type";
	}
}*/
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("vgallery_type_modify");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . Cms::getInstance("frameworkcss")->get("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $vgallery_field_title . '<span class="smart-url">' . $vgallery_field_name . '</span>' .'</h1>';
$oRecord->src_table = $src_type . "_fields";
$oRecord->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");
$oRecord->user_vars["src_type"] = $src_type;
$oRecord->user_vars["ID_type"] = $ID_type;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($display_addnew) {
	$oRecord->buttons_options["delete"]["display"] = false;

	$oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("vgallery_field_copy");
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT vgallery_fields.ID
									, vgallery_fields.name 
									, vgallery_type.name AS grp_name
								FROM vgallery_fields 
									INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
								WHERE 1
								ORDER BY vgallery_fields.name";
	$oField->widget = "actex";
	$oField->actex_update_from_db = true;
	$oField->actex_group = "grp_name";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("vgallery_field_addnew");
	$oField->store_in_db = false;
    $oRecord->addContent($oField);
} else {
	$oRecord->addEvent("on_process_template", "VgalleryModify_on_process_template");
	$oRecord->addEvent("on_done_action", "FormExtraFieldModify_on_done_action");
	$oRecord->insert_additional_fields["ID_type"] = new ffData($ID_type, "Number");

	/************
	* LOAD Default
	*/
    $sSQL = "SELECT " . $src_type . "_type.advanced_group
                FROM " . $src_type . "_type
                WHERE ID = " . $db_gallery->toSql($ID_type, "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord())
    {
        $field_default["advanced_group"] = $db_gallery->getField("advanced_group", "Number", true);
    }
    
    $oRecord->user_vars["advanced_group"] = $field_default["advanced_group"];
	
	$sSQL = "SELECT ID, name FROM vgallery_fields_data_type WHERE 1";
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

	$field_default["parent_detail"]				= "";
	$field_default["parent_thumb"] 				= "";
	$field_default["ID_type"] 					= $_REQUEST["ID_type"];
	$field_default["ID_group_thumb"]			= 0;
	$field_default["ID_group_detail"]			= 0;
	$field_default["ID_group_backoffice"] 		= 0;
	$field_default["order_thumb"] 				= 0;
	$field_default["order_detail"] 				= 0;
	$field_default["order_backoffice"] 			= 0;
	$field_default["ID_extended_type"] 			= $arrExtType["String"];
	$field_default["settings_type_thumb"]		= "";
	$field_default["settings_type_thumb_md"]	= "";
	$field_default["settings_type_thumb_sm"]	= "";
	$field_default["settings_type_thumb_xs"]	= "";
	$field_default["settings_type_detail"] 		= "";
	$field_default["settings_type_detail_md"] 	= "";
	$field_default["settings_type_detail_sm"] 	= "";
	$field_default["settings_type_detail_xs"] 	= "";
	$field_default["ID_data_type"]				= $arrDataType["data"];
	$field_default["data_source"] 				= "";
	$field_default["data_limit"] 				= "";
	$field_default["data_sort"]					= 0;
	$field_default["data_sort_method"]			= "";
	$field_default["enable_in_document"] 		= 1;
	$field_default["enable_thumb"]				= 0;
	$field_default["enable_thumb_label"] 		= 0;
    $field_default["ID_label_thumb_htmltag"]    = 0;
	$field_default["enable_thumb_empty"] 		= 0;
	$field_default["enable_lastlevel"] 			= 0;
	$field_default["enable_detail"] 			= 1;
	$field_default["enable_detail_label"] 		= 0;
    $field_default["ID_label_detail_htmltag"]   = 0;
	$field_default["enable_detail_empty"] 		= 0;
	$field_default["enable_sort"] 				= 1;
	$field_default["thumb_limit"] 				= "";
	$field_default["enable_thumb_cascading"] 	= 0;
	$field_default["enable_detail_cascading"] 	= 0;
	$field_default["display_view_mode_thumb"]	= "";
	$field_default["display_view_mode_detail"] 	= "";
	$field_default["enable_smart_url"] 			= 0;
	$field_default["enable_in_menu"] 			= 0;
	$field_default["meta_description"] 			= 0;
	$field_default["enable_in_grid"] 			= 0;
	$field_default["enable_in_mail"] 			= 1;
	$field_default["enable_in_cart"] 			= 0;
	$field_default["require"] 					= 0;
	$field_default["ID_check_control"] 			= 0;
	$field_default["limit_by_groups"] 			= "";
	$field_default["limit_by_groups_frontend"]  = "";
	$field_default["limit_thumb_by_layouts"] 	= "";
	$field_default["limit_detail_by_layouts"] 	= "";
	$field_default["disable_multilang"] 		= 0;
	$field_default["enable_tip"] 				= 0;
	$field_default["ID_thumb_htmltag"] 			= 0;
	$field_default["ID_detail_htmltag"] 		= 0;
	$field_default["custom_thumb_field"] 		= "";
	$field_default["custom_detail_field"] 		= "";
	$field_default["schemaorg"] 				= "";
    $field_default["advanced_group"] 			= 0;
    $field_default["fixed_pre_content_thumb"]   = "";
    $field_default["fixed_post_content_thumb"]  = "";
    $field_default["fixed_pre_content_detail"]  = "";
    $field_default["fixed_post_content_detail"] = "";
        
	if($copy_field > 0) 
	{
		$sSQL = "SELECT " . $src_type . "_fields.* 
					FROM " . $src_type . "_fields
					WHERE " . $src_type . "_fields.ID = " . $db_gallery->toSql($copy_field, "Number");
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {	
			$field_default["parent_detail"]				= $db_gallery->getField("parent_detail", "Text", true);
			$field_default["parent_thumb"] 				= $db_gallery->getField("parent_thumb", "Text", true);
			$field_default["ID_type"] 					= $db_gallery->getField("ID_type", "Number", true);
			$field_default["ID_group_thumb"]			= $db_gallery->getField("ID_group_thumb", "Number", true);
			$field_default["ID_group_detail"]			= $db_gallery->getField("ID_group_detail", "Number", true);
			$field_default["ID_group_backoffice"] 		= $db_gallery->getField("ID_group_backoffice", "Number", true);
			$field_default["order_thumb"] 				= $db_gallery->getField("order_thumb", "Number", true);
			$field_default["order_detail"] 				= $db_gallery->getField("order_detail", "Number", true);
			$field_default["order_backoffice"] 			= $db_gallery->getField("order_backoffice", "Number", true);
			$field_default["ID_extended_type"] 			= $db_gallery->getField("ID_extended_type", "Number", true);
			$field_default["settings_type_thumb"]		= $db_gallery->getField("settings_type_thumb", "Text", true);
			$field_default["settings_type_thumb_md"]	= $db_gallery->getField("settings_type_thumb_md", "Text", true);
			$field_default["settings_type_thumb_sm"]	= $db_gallery->getField("settings_type_thumb_sm", "Text", true);
			$field_default["settings_type_thumb_xs"]	= $db_gallery->getField("settings_type_thumb_xs", "Text", true);
			$field_default["settings_type_detail"] 		= $db_gallery->getField("settings_type_detail", "Text", true);
			$field_default["settings_type_detail_md"] 	= $db_gallery->getField("settings_type_detail_md", "Text", true);
			$field_default["settings_type_detail_sm"] 	= $db_gallery->getField("settings_type_detail_sm", "Text", true);
			$field_default["settings_type_detail_xs"] 	= $db_gallery->getField("settings_type_detail_xs", "Text", true);
			$field_default["ID_data_type"]				= $db_gallery->getField("ID_data_type", "Number", true);
			$field_default["data_source"] 				= $db_gallery->getField("data_source", "Text", true);
			$field_default["data_limit"] 				= $db_gallery->getField("data_limit", "Text", true);
			$field_default["data_sort"]					= $db_gallery->getField("data_sort", "Number", true);
			$field_default["data_sort_method"]			= $db_gallery->getField("data_sort_method", "Text", true);
			$field_default["enable_in_document"] 		= $db_gallery->getField("enable_in_document", "Number", true);
			$field_default["enable_thumb"]				= $db_gallery->getField("enable_thumb", "Number", true);
			$field_default["enable_thumb_label"] 		= $db_gallery->getField("enable_thumb_label", "Number", true);
            $field_default["ID_label_thumb_htmltag"]    = $db_gallery->getField("ID_label_thumb_htmltag", "Number", true);
			$field_default["enable_thumb_empty"] 		= $db_gallery->getField("enable_thumb_empty", "Number", true);
			$field_default["enable_lastlevel"] 			= $db_gallery->getField("enable_lastlevel", "Number", true);
			$field_default["enable_detail"] 			= $db_gallery->getField("enable_detail", "Number", true);
			$field_default["enable_detail_label"] 		= $db_gallery->getField("enable_detail_label", "Number", true);
            $field_default["ID_label_detail_htmltag"]   = $db_gallery->getField("ID_label_detail_htmltag", "Number", true);
			$field_default["enable_detail_empty"] 		= $db_gallery->getField("enable_detail_empty", "Number", true);
			$field_default["enable_sort"] 				= $db_gallery->getField("enable_sort", "Number", true);
			$field_default["thumb_limit"] 				= $db_gallery->getField("thumb_limit", "Text", true);
			$field_default["enable_thumb_cascading"] 	= $db_gallery->getField("enable_thumb_cascading", "Number", true);
			$field_default["enable_detail_cascading"] 	= $db_gallery->getField("enable_detail_cascading", "Number", true);
			$field_default["display_view_mode_thumb"] 	= $db_gallery->getField("display_view_mode_thumb", "Text", true);
			$field_default["display_view_mode_detail"] 	= $db_gallery->getField("display_view_mode_detail", "Text", true);
			$field_default["enable_smart_url"] 			= $db_gallery->getField("enable_smart_url", "Number", true);   
			$field_default["enable_in_menu"] 			= $db_gallery->getField("enable_in_menu", "Number", true);   
			$field_default["meta_description"] 			= $db_gallery->getField("meta_description", "Number", true);   
			$field_default["enable_in_grid"] 			= $db_gallery->getField("enable_in_grid", "Number", true);   
			$field_default["enable_in_mail"] 			= $db_gallery->getField("enable_in_mail", "Number", true);   
			$field_default["enable_in_cart"] 			= $db_gallery->getField("enable_in_cart", "Number", true);   
			$field_default["require"] 					= $db_gallery->getField("require", "Number", true);   
			$field_default["ID_check_control"] 			= $db_gallery->getField("ID_check_control", "Number", true);   
			$field_default["limit_by_groups"] 			= $db_gallery->getField("limit_by_groups", "Text", true);   
			$field_default["limit_by_groups_frontend"]  = $db_gallery->getField("limit_by_groups_frontend", "Text", true);   
			$field_default["limit_thumb_by_layouts"] 	= $db_gallery->getField("limit_thumb_by_layouts", "Text", true);   
			$field_default["limit_detail_by_layouts"] 	= $db_gallery->getField("limit_detail_by_layouts", "Text", true);   
			$field_default["disable_multilang"] 		= $db_gallery->getField("disable_multilang", "Number", true);   
			$field_default["enable_tip"] 				= $db_gallery->getField("enable_tip", "Number", true);   
			$field_default["ID_thumb_htmltag"] 			= $db_gallery->getField("ID_thumb_htmltag", "Number", true);   
			$field_default["ID_detail_htmltag"] 		= $db_gallery->getField("ID_detail_htmltag", "Number", true);   
			$field_default["custom_thumb_field"] 		= $db_gallery->getField("custom_thumb_field", "Text", true);   
			$field_default["custom_detail_field"] 		= $db_gallery->getField("custom_detail_field", "Text", true);   
			$field_default["schemaorg"] 				= $db_gallery->getField("schemaorg", "Text", true);   
            $field_default["fixed_pre_content_thumb"]   = $db_gallery->getField("fixed_pre_content_thumb", "Text", true);   
            $field_default["fixed_post_content_thumb"]  = $db_gallery->getField("fixed_post_content_thumb", "Text", true);   
            $field_default["fixed_pre_content_detail"]  = $db_gallery->getField("fixed_pre_content_detail", "Text", true);   
            $field_default["fixed_post_content_detail"] = $db_gallery->getField("fixed_post_content_detail", "Text", true);   
            
			
			
			/*
			$actual_control_type = $arrControlTypeRev[$field_default["ID_check_control"]];
			$actual_ext_type = $arrExtTypeRev[$field_default["ID_extended_type"]];
			$actual_type = $field_default["type"];
			$actual_disable_free_input = $field_default["disable_free_input"];*/
		}
	}

	/****
	* General & RelationShip Group
	*/
	$group_general = "general";
	if(!$group_limit || array_search($group_general, $group_limit) !== false) {
		$oRecord->addContent(null, true, $group_general); 
   		$oRecord->groups[$group_general] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_general)
											//, "title_class" => "dialogSubTitleTab dep-general"
											, "tab_dialog" => true
											, "cols" => 1
											, "class" => ""
										 );	


		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_field_name");
		$oField->class = "input title-page";
		$oField->required = true;
		$oRecord->addContent($oField, $group_general);

 		$oField = ffField::factory($cm->oPage);
		$oField->id = "schemaorg";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_schemaorg_field");
		$oField->default_value = new ffData($field_default["schemaorg"]);
		$oRecord->addContent($oField, $group_general);	
	}

	$group_source = "source";
	if(!$group_limit || array_search($group_source, $group_limit) !== false) {
		$sSQL_file = "";

		if(Cms::env("AREA_SHOW_ECOMMERCE")) {
			$sSQL_file = "
				) UNION ( 
					SELECT 
						name AS nameID
						, name AS name
						, (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'ecommerce.checkout') AS type 
					FROM ecommerce_mpay
					WHERE ecommerce_mpay.ecommerce > 0";
		}

		$oRecord->addContent(null, true, $group_source); 
   		$oRecord->groups[$group_source] = array(
											"title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_source)
											//, "title_class" => "dialogSubTitleTab dep-general"
											, "tab_dialog" => (!$group_limit || array_search($group_general, $group_limit) !== false
												? $group_general
												: $group_source
											)
											, "cols" => 1
											, "class" => ""
										 );		
		
		$sSQL = "SELECT vgallery_fields_data_type.ID 
				FROM vgallery_fields_data_type 
				WHERE vgallery_fields_data_type.name = 'static'";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$ID_data_type_static = $db_gallery->getField("ID", "Number", true);

			$static_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/*");
			if(is_array($static_file) && count($static_file)) {
			    foreach($static_file AS $real_file) {
			        if(is_file($real_file)) {
			            $relative_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file);
			            $sSQL_file .= " UNION
	            						(
			                            SELECT 
			                                " . $db_gallery->toSql($relative_path) . " AS nameID
			                                , " . $db_gallery->toSql(basename($real_file)) . " AS name
			                                , " . $db_gallery->toSql($ID_data_type_static, "Number") . " AS type
			                            )
			                            ";
			        }
			    }
			}
		}
		
		$ff_applets = glob(FF_DISK_PATH . "/applets/*");
		if(is_array($ff_applets) && count($ff_applets)) {
		    foreach($ff_applets AS $real_dir) {
		        if(is_dir($real_dir) && file_exists($real_dir . "/index." . FF_PHP_EXT)) {
		            $relative_path = str_replace(FF_DISK_PATH, "", $real_dir);
		            $sSQL_file .= " UNION
		            				(
		                            SELECT 
		                                " . $db_gallery->toSql($relative_path) . " AS nameID
		                                , " . $db_gallery->toSql(basename($relative_path)) . " AS name
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'applet') AS type 
		                            )
		                            ";
		        }
		        
				if(is_file(FF_DISK_PATH . "/applets/" . basename($relative_path) . "/schema." . FF_PHP_EXT)) {
					require FF_DISK_PATH . "/applets/" . basename($relative_path) . "/schema." . FF_PHP_EXT;

					if(is_array($schema) && count($schema)
						&& array_key_exists("applets", $schema) 
						&& is_array($schema["applets"]) && count($schema["applets"])
						&& array_key_exists(basename($relative_path), $schema["applets"]) 
						&& is_array($schema["applets"][basename($relative_path)]) && count($schema["applets"][basename($relative_path)])
						&& array_key_exists("params", $schema["applets"][basename($relative_path)])
						&& is_array($schema["applets"][basename($relative_path)]["params"]) && count($schema["applets"][basename($relative_path)]["params"])
					) {
						foreach($schema["applets"][basename($relative_path)]["params"] AS $applets_params_key => $applets_params_value) {
							if(array_key_exists("table", $applets_params_value)
								&& strlen($applets_params_value["table"])
								&& array_key_exists("field", $applets_params_value)
								&& strlen($applets_params_value["field"])
							) {
								$sSQL_data_limit .= " UNION
													(
														SELECT 
																	CONCAT(" . $db_gallery->toSql($applets_params_key . ":") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . ") AS nameID
													                , REPLACE(CONCAT(" . $db_gallery->toSql($applets_params_key . ": ") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . "), '-', ' ') AS name
													                , " . $db_gallery->toSql($relative_path) . " AS type
														
														FROM " . $applets_params_value["table"] . "
														WHERE 1 
															" . (array_key_exists("where", $applets_params_value) && strlen($applets_params_value["where"]) 
																?  " AND " . $applets_params_value["where"]
																: ""
														) . "
													)
													";
							}
							if(array_key_exists("value", $applets_params_value)
								&& strlen($applets_params_value["value"])
							) {
								if(is_array($applets_params_value["value"]) && count($applets_params_value["value"])) {
									foreach($applets_params_value["value"] AS $applets_params_data) {
										$sSQL_data_limit .= " UNION
															(
												            SELECT 
												                " . $db_gallery->toSql($applets_params_key . ":"  . $applets_params_data, "Text") . " AS nameID
												                , " . $db_gallery->toSql($applets_params_key . ":"  . $applets_params_data) . " AS name
												                , " . $db_gallery->toSql($relative_path) . " AS type
											            	)
															";
									}
								}
							}
						}
					}
				}
		    }
		}
		$ff_modules = glob(FF_DISK_PATH . "/modules/*");
		if(is_array($ff_modules) && count($ff_modules)) {
		    foreach($ff_modules AS $real_module_dir) {
		        if(is_dir($real_module_dir)) {
					$ff_applets = glob(FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/applets/*");
					if(is_array($ff_applets) && count($ff_applets)) {
					    foreach($ff_applets AS $real_applet_dir) {
					        if(is_dir($real_applet_dir) && file_exists($real_applet_dir . "/index." . FF_PHP_EXT)) {
					            $relative_path = str_replace(FF_DISK_PATH, "", $real_applet_dir);
					            $sSQL_file .= " UNION
					            				(
					                            SELECT 
					                                " . $db_gallery->toSql($relative_path) . " AS nameID
					                                , " . $db_gallery->toSql(basename($real_module_dir) . ": " . basename($relative_path), "Text") . " AS name
					                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'applet') AS type 
					                            )
					                            ";
					                            
					            if(is_file(FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/conf/schema." . FF_PHP_EXT)) {
									require FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/conf/schema." . FF_PHP_EXT;

									if(is_array($schema) && count($schema)
										&& array_key_exists("applets", $schema) 
										&& is_array($schema["applets"]) && count($schema["applets"])
										&& array_key_exists(basename($relative_path), $schema["applets"]) 
										&& is_array($schema["applets"][basename($relative_path)]) && count($schema["applets"][basename($relative_path)])
										&& array_key_exists("params", $schema["applets"][basename($relative_path)])
										&& is_array($schema["applets"][basename($relative_path)]["params"]) && count($schema["applets"][basename($relative_path)]["params"])
									) {
										foreach($schema["applets"][basename($relative_path)]["params"] AS $applets_params_key => $applets_params_value) {
											if(array_key_exists("table", $applets_params_value)
												&& strlen($applets_params_value["table"])
												&& array_key_exists("field", $applets_params_value)
												&& strlen($applets_params_value["field"])
											) {
												$sSQL_data_limit .= " UNION
																	(
																		SELECT 
																					CONCAT(" . $db_gallery->toSql($applets_params_key . ":") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . ") AS nameID
													                                , REPLACE(CONCAT(" . $db_gallery->toSql($applets_params_key . ": ") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . "), '-', ' ') AS name
													                                , " . $db_gallery->toSql($relative_path) . " AS type
																		
																		FROM " . $applets_params_value["table"] . "
																		WHERE 1 
																			" . (array_key_exists("where", $applets_params_value) && strlen($applets_params_value["where"]) 
																				?  " AND " . $applets_params_value["where"]
																				: ""
																		) . "
																	)
																	";
											}
											if(array_key_exists("value", $applets_params_value)
												&& strlen($applets_params_value["value"])
											) {
												if(is_array($applets_params_value["value"]) && count($applets_params_value["value"])) {
													foreach($applets_params_value["value"] AS $applets_params_data) {
											            $sSQL_data_limit .= " UNION
											            					(
												                            SELECT 
												                                " . $db_gallery->toSql($applets_params_key . ":"  . $applets_params_data, "Text") . " AS nameID
												                                , " . $db_gallery->toSql($applets_params_key . ":"  . $applets_params_data) . " AS name
												                                , " . $db_gallery->toSql($relative_path, "Text") . " AS type
												                            )
												                            ";
													}
												}
											}
										}
									}
					            }
					        }
					    }
					}
				}
		    }
		}		
		$sSQL_data_source = " UNION (
				                SELECT 
			                        " . $db_gallery->toSql($src_table) . " AS nameID
				                    , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("primary_table")). " AS name
				                    , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'table.alt') AS type 
							)";
	    if(check_function("get_schema_def")) {
	        $schema = get_schema_def(false);

			if(is_array($schema["db"]["data_source"]) && count($schema["db"]["data_source"])) {
	            foreach($schema["db"]["data_source"] AS $data_source => $data_source_def) {
	                if(!isset($data_source_def["limit"]) || array_search($src_type, $data_source_def["limit"]) !== false) {
	                	if(!isset($data_source_def["data_type"]) || (is_array($data_source_def["data_type"]) && array_search("table.alt", $data_source_def["data_type"]) !== false)) {
	                		$sSQL_data_source .= " UNION
	                                    (
	                                    SELECT 
	                                        " . $db_gallery->toSql($data_source) . " AS nameID
	                                        , " . $db_gallery->toSql($data_source_def["label"] ? $data_source_def["label"] : $data_source) . " AS name
	                                        , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'table.alt') AS type 
	                                    )"; 
	                	}
	                	if(!isset($data_source_def["data_type"]) || array_search("selection", $data_source_def["data_type"]) !== false) {
		                    $sSQL_data_source .= " UNION
		                                    (
		                                    SELECT 
		                                        " . $db_gallery->toSql($data_source) . " AS nameID
		                                        , " . $db_gallery->toSql($data_source_def["label"] ? $data_source_def["label"] : $data_source) . " AS name
		                                        , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'selection') AS type 
		                                    )";    
							if($data_source != $data_source_def["table"]) {
								$sSQL_data_limit .= " UNION
													(
														SELECT 
									                        CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        							, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1) 
			                        							, COLUMN_NAME
									                        ) USING utf8) COLLATE utf8_general_ci AS nameID
									                        , CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        							, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1)
			                        							, COLUMN_NAME
									                        ) USING utf8) COLLATE utf8_general_ci AS name
									                        , " .  $db_gallery->toSql($data_source) . " AS type 
									                    FROM information_schema.COLUMNS 
									                    WHERE  TABLE_SCHEMA = " . $db_gallery->toSql(FF_DATABASE_NAME) . "
								                    		AND TABLE_NAME = " . $db_gallery->toSql($data_source_def["table"]) . "
	                    									AND COLUMN_NAME NOT LIKE 'ID\_%'
	                    									AND COLUMN_NAME NOT LIKE 'IS\_%'
	                    									AND COLUMN_NAME NOT LIKE 'USE\_%'
	                    									AND COLUMN_NAME NOT LIKE '%\_lng'
	                    									AND COLUMN_NAME NOT LIKE '%\_zoom'
	                    									AND COLUMN_NAME NOT LIKE '%\_title'
								                    )";
							}
						}
	                }
	            }
	        }	        
			if(is_array($schema["db"]["selection_data_source"]) && count($schema["db"]["selection_data_source"])) {
	            foreach($schema["db"]["selection_data_source"] AS $selection_data_source => $selection_data_source_def) {
	                if(!isset($selection_data_source_def["limit"]) || (is_array($selection_data_source_def["limit"]) && array_search($src_type, $selection_data_source_def["limit"]) !== false)) {
	                    $sSQL_selection_data_source .= " UNION
	                                    (
	                                    SELECT 
	                                        " . $db_gallery->toSql($selection_data_source) . " AS nameID
	                                        , " . $db_gallery->toSql($selection_data_source_def["label"] ? $selection_data_source_def["label"] : $selection_data_source) . " AS name
	                                        , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
	                                    )";  
						if($selection_data_source != $selection_data_source_def["table"]) {
							$sSQL_selection_data_limit .= " UNION
												(
													SELECT 
								                        CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        						, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1) 
			                        						, COLUMN_NAME
								                        ) USING utf8) COLLATE utf8_general_ci AS nameID
								                        , CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        						, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1)
			                        						, COLUMN_NAME
								                        ) USING utf8) COLLATE utf8_general_ci AS name
								                        , " .  $db_gallery->toSql($selection_data_source) . " AS type 
								                    FROM information_schema.COLUMNS 
								                    WHERE  TABLE_SCHEMA = " . $db_gallery->toSql(FF_DATABASE_NAME) . "
								                    	AND TABLE_NAME = " . $db_gallery->toSql($selection_data_source_def["table"]) . "
	                    								AND COLUMN_NAME NOT LIKE 'ID\_%'
	                    								AND COLUMN_NAME NOT LIKE 'IS\_%'
	                    								AND COLUMN_NAME NOT LIKE 'USE\_%'
	                    								AND COLUMN_NAME NOT LIKE '%\_lng'
	                    								AND COLUMN_NAME NOT LIKE '%\_zoom'
	                    								AND COLUMN_NAME NOT LIKE '%\_title'
							                    )";
						}
	                }
	            }
	        }
	    }

		$oField = ffField::factory($cm->oPage);
		$oField->id = "tblsrc";
		$oField->data_source = "ID_data_type";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_type");
		$oField->widget = "actex";
		//$oField->actex_autocomp = true;
		$oField->source_SQL = "SELECT 
		                            ID
		                            , name 
		                        FROM vgallery_fields_data_type
		                        WHERE 1
                        			AND status > 0
                      				AND ID <> " . $db_gallery->toSql($field_default["ID_data_type"], "Number") . "
                      				" . (strlen($sSQL_data_source) 
                        				? "" 
                        				: "AND name <> 'selection'" 
                        			) . "
		                        [ORDER] [COLON] name
		                        [LIMIT]";
		$oField->actex_child = array("items", "ID_extended_type");
		$oField->required = true;
		$oField->actex_update_from_db = true;
		$oField->multi_select_one = false;
		$oField->multi_select_noone = true;
		$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("vgallery_field_type_default");
		$oField->multi_select_noone_val = new ffData($field_default["ID_data_type"], "Number");
		$oField->default_value = new ffData($field_default["ID_data_type"], "Number");
		$oRecord->addContent($oField, $group_source);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "items";
		$oField->data_source = "data_source";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_source");
		$oField->widget = "actex";
		$oField->source_SQL = "
		                SELECT nameID, name, type FROM
		                (
		                        (
		                            SELECT 
		                                'anagraph' AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_anagraph")) . " AS name 
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'relationship') AS type 
		                            FROM vgallery
		                        ) UNION (
                                    SELECT 
                                        name AS nameID
                                        , name AS name
                                        , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'relationship') AS type 
                                    FROM vgallery
                                ) UNION (
		                            SELECT 
		                                'files' AS nameID
		                                , 'files' AS name
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'media') AS type 
		                        )  
								$sSQL_data_source
		                        UNION (
		                            SELECT 
		                                'wise' AS nameID
		                                , 'SpreadSheet' AS name
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'google.docs') AS type 
		                        ) UNION (
		                            SELECT 
		                                ID AS nameID
		                                , name AS name
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'comment') AS type 
		                            FROM module_form
		                        ) UNION (
		                            SELECT 
		                                name AS nameID
		                                , name AS name
		                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'sender') AS type 
		                            FROM vgallery
		                        ) 
		                        $sSQL_file
		                ) AS macro_tbl
		                [WHERE]
		                ORDER BY name
		                ";   
		$oField->actex_father = "tblsrc";
		$oField->actex_child = array("data_limit", "data_sort");
		$oField->actex_related_field = "type";
		$oField->actex_update_from_db = true;
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
		$oField->default_value = new ffData($field_default["data_source"]);
		$oRecord->addContent($oField, $group_source);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "data_limit";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_limit");
		$oField->widget = "actex";
		$oField->control_type = "checkbox";
		$oField->actex_father = "items";
		$oField->actex_related_field = "type";
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
		$oField->actex_update_from_db = true;

		//$oField->extended_type = "Selection";
		//$oField->widget = "checkgroup";
		$oField->grouping_separator = ",";  
		$oField->source_SQL = "
		                SELECT nameID, name, type FROM
		                (
                			(
			                    SELECT DISTINCT
			                        vgallery_fields.ID AS nameID
			                        , CONCAT(vgallery_type.name, ' - ',vgallery_fields.name) AS name
			                        , vgallery.name AS type 
			                    FROM vgallery_fields
			                        INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
			                        INNER JOIN vgallery ON FIND_IN_SET(vgallery_type.ID, vgallery.limit_type) 
			                    WHERE " . (OLD_VGALLERY 
                                        ? "vgallery_type.name <> 'System'"
                                        : "1"
                                    ) . "
		                    )
		                    UNION
		                    (
			                    SELECT 
			                        CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        	, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1) 
			                        	, COLUMN_NAME
			                        ) USING utf8) COLLATE utf8_general_ci AS nameID
			                        , CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
			                        	, SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1)
			                        	, COLUMN_NAME
			                        ) USING utf8) COLLATE utf8_general_ci AS name
			                        , CONVERT(TABLE_NAME USING utf8) COLLATE utf8_general_ci AS type 
			                    FROM information_schema.COLUMNS 
			                    WHERE  TABLE_SCHEMA = " . $db_gallery->toSql(FF_DATABASE_NAME) . "
	                    			AND COLUMN_NAME NOT LIKE 'ID\_%'
	                    			AND COLUMN_NAME NOT LIKE 'IS\_%'
	                    			AND COLUMN_NAME NOT LIKE 'USE\_%'
	                    			AND COLUMN_NAME NOT LIKE '%\_lng'
	                    			AND COLUMN_NAME NOT LIKE '%\_zoom'
	                    			AND COLUMN_NAME NOT LIKE '%\_title'
		                    )
		                    $sSQL_data_limit
		                ) AS datalimit_tbl
		                [WHERE]
		                ORDER BY name";
		$oField->default_value = new ffData($field_default["data_limit"]);
		$oRecord->addContent($oField, $group_source);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_extended_type";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_extended_type");
		if(check_function("set_field_extended_type"))
			$oField = set_field_extended_type($oField, "", "activecomboex");

		$oField->actex_father = "tblsrc";
		$oField->actex_related_field = "limit_by_data_type";
		$oField->actex_operation_field = "FIND_IN_SET";
		$oField->actex_child[] = "selection_data_source";

		if(!$group_limit || array_search("thumb", $group_limit) !== false) {
			$oField->actex_child[] = "settings_type_thumb";
			if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") 
            {
				$oField->actex_child[] = "settings_type_thumb_md";
				$oField->actex_child[] = "settings_type_thumb_sm";
				if($framework_css_name == "bootstrap") 
					$oField->actex_child[] = "settings_type_thumb_xs";
			}
		}
		if(!$group_limit || array_search("detail", $group_limit) !== false) {
			$oField->actex_child[] = "settings_type_detail";
			if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") 
            {
				$oField->actex_child[] = "settings_type_detail_md";
				$oField->actex_child[] = "settings_type_detail_sm";
				if($framework_css_name == "bootstrap") 
					$oField->actex_child[] = "settings_type_detail_xs";
			}
			$oField->actex_child[] = "display_view_mode_detail";
		}
		$oField->required = true;
		$oField->multi_select_one = false;
		//$oField->multi_select_noone = true;
		//$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("extended_type_undefined");
		$oField->default_value = new ffData($field_default["ID_extended_type"], "Number");
		$oRecord->addContent($oField, $group_source);   
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "selection_data_source";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_selection_data_source");
		$oField->widget = "actex";
		$oField->source_SQL = "
		                SELECT nameID, name, type FROM
		                (
								(
		                            SELECT DISTINCT
		                                '-1' AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("this_field")) . " AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                                " . $src_type . "_fields_selection.ID AS nameID
		                                , " . $src_type . "_fields_selection.name AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                            FROM " . $src_type . "_fields_selection
		                        ) UNION (
		                            SELECT DISTINCT
		                                'anagraph' AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_anagraph")) . " AS name 
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                                'search_tags' AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_search_tags")) . " AS name 
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                               " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "city") . " AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_support_city")) . " AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                                " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "province") . " AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_support_province")) . " AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                                " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "region") . " AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_support_region")) . " AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
		                        ) UNION (
		                            SELECT DISTINCT
		                                " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "state") . " AS nameID
		                                , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_support_state")) . " AS name
		                                , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`group` = 'select') AS type 
                                " . ($src_data_source_mono
                                    ? ") UNION (
                                        SELECT DISTINCT
                                            " . $db_gallery->toSql($src_data_source_mono) . " AS nameID
                                            , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_" . $src_data_source_mono)) . " AS name
                                            , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`name` = 'MonoRelation') AS type "
                                    : ""
                                ) 
                                . ($src_data_source_multi
                                    ? ") UNION (
                                        SELECT DISTINCT
                                            " . $db_gallery->toSql($src_data_source_multi) . " AS nameID
                                            , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("tbl_" . $src_data_source_multi)) . " AS name
                                            , (SELECT GROUP_CONCAT(extended_type.ID) FROM extended_type WHERE extended_type.`name` = 'MultiRelation') AS type "
                                    : ""
                                ) . "
		                        ) 
		                        $sSQL_selection_data_source
		                ) AS macro_tbl
		                [WHERE]
		                ORDER BY name"; 
		$oField->actex_father = "ID_extended_type";
		$oField->actex_child = array("selection_data_limit");
		$oField->actex_related_field = "type";
		$oField->actex_update_from_db = true;
		$oField->actex_operation_field = "IN";
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
		$oField->default_value = new ffData($field_default["data_source"]);
		$oRecord->addContent($oField, $group_source);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "selection_data_limit";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_selection_data_limit");
		$oField->widget = "actex";
		$oField->control_type = "checkbox";
		$oField->actex_father = "selection_data_source";
		$oField->actex_related_field = "type";
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
		$oField->actex_update_from_db = true;

		//$oField->extended_type = "Selection";
		//$oField->widget = "checkgroup";
		$oField->grouping_separator = ",";  
		$oField->source_SQL = "
					SELECT nameID, name, type FROM
		        	(
		        		(
				            SELECT 
				                CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
				                    , SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1) 
				                    , COLUMN_NAME
				                ) USING utf8) COLLATE utf8_general_ci AS nameID
				                , CONVERT(IF(LOCATE('_lat', COLUMN_NAME)
				                    , SUBSTRING(COLUMN_NAME, 1, LOCATE('_lat', COLUMN_NAME) -1)
				                    , COLUMN_NAME
				                ) USING utf8) COLLATE utf8_general_ci AS name
				                , CONVERT(TABLE_NAME USING utf8) COLLATE utf8_general_ci AS type 
				            FROM information_schema.COLUMNS 
				            WHERE  TABLE_SCHEMA = " . $db_gallery->toSql(FF_DATABASE_NAME) . "
	                    		AND COLUMN_NAME NOT LIKE 'ID\_%'
	                    		AND COLUMN_NAME NOT LIKE 'IS\_%'
	                    		AND COLUMN_NAME NOT LIKE 'USE\_%'
	                    		AND COLUMN_NAME NOT LIKE '%\_lng'
	                    		AND COLUMN_NAME NOT LIKE '%\_zoom'
	                    		AND COLUMN_NAME NOT LIKE '%\_title'
	                    )
	                    $sSQL_selection_data_limit
	                ) AS tbl_src
					[WHERE]
	                ORDER BY name";
		$oField->default_value = new ffData($field_default["data_limit"]);
		$oRecord->addContent($oField, $group_source); 		
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "data_sort";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_sort");
		$oField->base_type = "Number";
		$oField->widget = "actex";
		$oField->source_SQL = "SELECT vgallery_fields.ID
									, CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
								FROM vgallery_fields
									INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
								WHERE FIND_IN_SET(vgallery_fields.ID_type,
									(
										SELECT vgallery.limit_type 
										FROM vgallery
										[WHERE]
									)
								)
								ORDER BY name";
		$oField->actex_father = "items";
		//$oField->actex_child = "data_sort_method";
		$oField->actex_related_field = "vgallery.name";
		$oField->actex_update_from_db = true;
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_default");
        $oField->multi_select_one_val = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["data_sort"], "Number");
		$oRecord->addContent($oField, $group_source);   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "data_sort_method";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_sort_method");
		//$oField->base_type = "Number";
		$oField->extended_type = "Selection";
        $oField->multi_pairs = array(
            array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ascending")))
            , array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("discending")))
        
        );
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_method_default");
		$oField->default_value = new ffData($field_default["data_sort_method"]);
		$oRecord->addContent($oField, $group_source);  
	}
	 
    
  
	                             
	/***********
	*  Group Thumb
	*/
    $group_thumb = "thumb";
	if(!$group_limit || array_search($group_thumb, $group_limit) !== false) {
	    $oRecord->addContent(null, true, $group_thumb); 

	    $oRecord->groups[$group_thumb] = array(
													"title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_thumb)
													//, "title_class" => "dialogSubTitleTab dep-thumb"
													//, "title_field" => "enable_thumb"
													, "primary_field" => "enable_thumb"
													, "tab_dialog" => true
													, "tab_dialog_selected" => ($group_sel == $group_thumb)
													, "cols" => 1
													, "class" => ""
	                                              //   , "tab" => $group_thumb
	                                              );	

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_thumb";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_thumb"], "Number");
		$oRecord->addContent($oField, $group_thumb); 
	        
	    if($field_default["advanced_group"])
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "ID_group_thumb";
	        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_group_thumb");
	        $oField->widget = "actex";
	        $oField->source_SQL = "SELECT 
	                                    vgallery_type_group.ID
	                                    , vgallery_type_group.name
	                                FROM 
	                                    vgallery_type_group
	                                WHERE vgallery_type_group.`type` = 'thumb'
	                                [AND] [WHERE]
	                                [HAVING]
	                                [ORDER] [COLON] vgallery_type_group.name
	                                [LIMIT]";
	        $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery/type/group/modify";
	        $oField->actex_dialog_edit_params = array("keys[ID]" => null);
	        $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=vgalleryTypeGroupModify_confirmdelete";
	        $oField->resources[] = "vgalleryTypeGroupModify";
	        $oField->actex_update_from_db = true;
	        //$oField->actex_autocomp = true;
	        $oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
	        $oField->default_value = new ffData($field_default["ID_group_thumb"], "Number");
	        $oRecord->addContent($oField, $group_thumb);
	    } else
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "parent_thumb";
	        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_group_thumb");
	        $oField->default_value = new ffData($field_default["parent_thumb"], "Number");
	        $oRecord->addContent($oField, $group_thumb);
		}

	    $img_setting_columns = array(4,4,4,4);
		if (strlen($framework_css_name)) {
			if (check_function("set_fields_grid_system")) {
				set_fields_grid_system($oRecord, array(
						"group" => $group_thumb
						, "fluid" => false
						, "class" => false
						, "wrap" => false
						, "extra" => false
						, "image" => array(
							"prefix" => "settings_type_thumb"
							, "source_SQL" => "SELECT ID, name, type FROM 
								                (
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 8 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                    UNION
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 15 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                    UNION
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 16 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                ) AS tbl_src
								                [WHERE]
								                ORDER BY tbl_src.name"
							, "father" => "ID_extended_type"
							, "related_field" => "type"
							, "default_value" => array(
								$field_default["settings_type_thumb"]
								, $field_default["settings_type_thumb_md"]
								, $field_default["settings_type_thumb_sm"]
								, $field_default["settings_type_thumb_xs"]
							)
						)
					), $framework_css
				);
			}
			
			if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") {
			    $img_setting_columns = array(6,6,6,6);
			}		
		}		
		
		$oField = ffField::factory($cm->oPage);
	    $oField->id = "enable_lastlevel"; 
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_lastlevel");
	    $oField->base_type = "Number";
	    $oField->widget = "actex";
	    $oField->multi_pairs = array(
		    array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
		    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("to_detail_content"))),
		    array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("to_large_image")))
	    );
	    //$oField->actex_update_from_db = true;
	    $oField->actex_child = "display_view_mode_thumb";
	    $oField->default_value = new ffData($arrFieldData["default"]["enable_lastlevel"], "Number");
	    $oField->multi_select_one = false;
	    $oField->setWidthComponent($img_setting_columns);
		$oRecord->addContent($oField, $group_thumb); 

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "display_view_mode_thumb";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode");
	    $oField->widget = "actex";
	    if(check_function("query_plugin_js"))
			$oField->source_SQL = query_plugin_js("Number");	
/*
	    $oField->source_SQL = "SELECT ID, name, type FROM 
	                    (
	                        SELECT DISTINCT
	                            js.name AS ID
	                            , js.name AS name
	                            , IF(layout_type_plugin.type = 'image'
	                                , 2
	                                , IF(layout_type_plugin.type = 'content'
	                                    , 1
	                                    , 0
	                                )
	                            ) AS type 
	                        FROM layout_type_plugin
	                            INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status > 0
	                            INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND (layout_type.name = 'VIRTUAL_GALLERY' OR layout_type.name = 'GALLERY' OR layout_type.name = 'PUBLISHING')
	                        WHERE layout_type_plugin.type <> ''
	                    ) AS tbl_src
	                    [WHERE]
	                    ORDER BY name";
*/
	    $oField->actex_father = "enable_lastlevel";
	    $oField->actex_related_field = "type";
	    //$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
	    //$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	    //$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
	    //$oField->resources[] = "ExtrasImageModify";
	    $oField->actex_update_from_db = true;
	    $oField->actex_hide_empty = "all";
	    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oField->setWidthComponent($img_setting_columns);
	    $oRecord->addContent($oField, $group_thumb);

		/**
	    *  Thumb Field Container 
	    */
		$oRecord->addContent(null, true, "ThumbFieldContainer"); 
		$oRecord->groups["ThumbFieldContainer"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_thumb . "_container")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 //, "primary_field" => "field_fluid_thumb"
										 , "tab_dialog" => $group_thumb
		                                 , "cols" => 1
		                              );
		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array(
		            "group" => "ThumbFieldContainer"
		            , "fluid" => array(
		                "name" => "field_fluid_thumb"
		                , "prefix" => "field_grid_thumb"
		                , "one_field" => true
		                , "hide" => false
		                , "full_row" => true
                        , "default_value" => new ffData("1", "Number")
		            )
		            , "class" => array(
                		"name" => "field_class_thumb"
		            )
		            , "wrap" => false
		        ), $framework_css
		    );
		}    	    
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_thumb_htmltag";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_thumb_htmltag");
		$oField->base_type = "Number";
		$oField->widget = "actex";
		$oField->source_SQL = "SELECT 
									vgallery_fields_htmltag.ID
									, IF(vgallery_fields_htmltag.attr = ''
										, vgallery_fields_htmltag.tag
										, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
									) AS name
		                        FROM 
		                            vgallery_fields_htmltag
		                        [WHERE]
		                        [HAVING]
		                        ORDER BY vgallery_fields_htmltag.tag";
		$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
		$oField->resources[] = "VGalleryHtmlTagModify";
		$oField->actex_update_from_db = true;
		$oField->actex_hide_empty = "all";
		$oField->multi_select_noone = true;
		$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
		$oField->multi_select_noone_val = new ffData("-1", "Number");
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
		$oField->default_value = new ffData($field_default["ID_thumb_htmltag"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbFieldContainer"); 	    
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "custom_thumb_field";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_custom_thumb_field");
		$oField->extended_type = "Text";
		$oField->default_value = new ffData($field_default["custom_thumb_field"]);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbFieldContainer");	
	    
	    
	    /**
	    *  Thumb Field Settings 
	    */
		$oRecord->addContent(null, true, "FieldSettings"); 
		$oRecord->groups["FieldSettings"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_thumb . "_settings")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 //, "primary_field" => "field_fluid_thumb"
										 , "tab_dialog" => $group_thumb
		                                 , "cols" => 1
		                              );	    

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_thumb_empty";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_empty");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_thumb_empty"], "Number");
		$oRecord->addContent($oField, "FieldSettings"); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_sort";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_sort");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_sort"], "Number");
		$oRecord->addContent($oField, "FieldSettings"); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_thumb_cascading";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_cascading");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_thumb_cascading"], "Number");
		$oRecord->addContent($oField, "FieldSettings"); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_limit";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_thumb_limit");
		$oField->base_type = "Number";
		$oField->default_value = new ffData($field_default["thumb_limit"], "Number");
		$oRecord->addContent($oField, "FieldSettings"); 			

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "fixed_pre_content_thumb";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_pre_content");
	    $oField->extended_type = "Text";
	    $oField->default_value = new ffData($field_default["fixed_pre_content_thumb"], "Number");
	    $oField->setWidthComponent(6);
	    $oRecord->addContent($oField, "FieldSettings");	
			
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "fixed_post_content_thumb";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_post_content");
	    $oField->extended_type = "Text";
	    $oField->default_value = new ffData($field_default["fixed_post_content_thumb"], "Number");
	    $oField->setWidthComponent(6);
	    $oRecord->addContent($oField, "FieldSettings");   
	    
		if($src_type == "vgallery") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "limit_thumb_by_layouts";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_thumb_layouts");
			$oField->base_type = "Text";
			$oField->extended_type = "Selection";
			$oField->source_SQL = "SELECT layout.ID, layout.name
			                        FROM layout
			                            INNER JOIN layout_type ON layout_type.ID = layout.ID_type
			                            INNER JOIN vgallery ON vgallery.name = layout.value	
			                        WHERE layout_type.name = 'VIRTUAL_GALLERY'
                        				AND FIND_IN_SET(" . $db_gallery->toSql($ID_vgallery_type, "Number") . ", vgallery.limit_type)
			                        ORDER BY layout.name ";
			$oField->control_type = "input";
			$oField->widget = "checkgroup";
			$oField->grouping_separator = ",";
			$oField->default_value = new ffData($field_default["limit_thumb_by_layouts"]);
			$oRecord->addContent($oField, "FieldSettings");
		}

		/**
	    *  Thumb Field Label 
	    */
		$oRecord->addContent(null, true, "ThumbFieldLabel"); 
		$oRecord->groups["ThumbFieldLabel"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_thumb . "_label")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 , "primary_field" => "enable_thumb_label"
										 , "tab_dialog" => $group_thumb
		                                 , "cols" => 1
		                              );
		                              
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_thumb_label";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_label");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_thumb_label"], "Number");
		$oRecord->addContent($oField, "ThumbFieldLabel"); 
		                              
		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array( 
		            "group" => "ThumbFieldLabel"
		            , "fluid" => array(
		                "name" => "label_fluid_thumb"
		                , "prefix" => "label_grid_thumb" 
		                , "one_field" => true
		                , "hide" => false
		                , "row" => false
		                , "full_row" => true
		                , "default_value" => new ffData("1", "Number")
		            )
		            , "class" => false
		            , "wrap" => false
		        ), $framework_css
		    );
		}
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID_label_thumb_htmltag";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_label_thumb_htmltag");
	    $oField->base_type = "Number";
	    $oField->widget = "actex";
	    $oField->source_SQL = "SELECT 
	                                vgallery_fields_htmltag.ID
	                                , IF(vgallery_fields_htmltag.attr = ''
	                                    , vgallery_fields_htmltag.tag
	                                    , CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
	                                ) AS name
	                            FROM 
	                                vgallery_fields_htmltag
	                            [WHERE]
	                            [HAVING]
	                            ORDER BY vgallery_fields_htmltag.tag";
	    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
	    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
	    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
	    $oField->resources[] = "VGalleryHtmlTagModify";
	    $oField->actex_update_from_db = true;
	    $oField->actex_hide_empty = "all";
		$oField->multi_select_noone = true;
		$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
		$oField->multi_select_noone_val = new ffData("-1", "Number");
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
	    $oField->default_value = new ffData($field_default["ID_label_thumb_htmltag"], "Number");
	    $oRecord->addContent($oField, "ThumbFieldLabel"); 	    
	     
	}
	/***********
	*  Group Detail
	*/
    $group_detail = "detail";
	if(!$group_limit || array_search($group_detail, $group_limit) !== false) {
	    $oRecord->addContent(null, true, $group_detail); 

	    $oRecord->groups[$group_detail] = array(
													"title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_detail)
													//, "title_class" => "dialogSubTitleTab dep-detail"
													//, "title_field" => "enable_detail"
													, "primary_field" => "enable_detail"
													, "tab_dialog" => true
													, "tab_dialog_selected" => ($group_sel == $group_detail)
													, "cols" => 1
													, "class" => ""
	                                              //   , "tab" => $group_detail
	                                              );	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_detail";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_detail"], "Number");
		$oRecord->addContent($oField, $group_detail);
	        
	    if($field_default["advanced_group"])
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "ID_group_detail";
	        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_group_detail");
	        $oField->widget = "actex";
	        $oField->source_SQL = "SELECT 
		                                vgallery_type_group.ID
		                                , vgallery_type_group.name
	                                FROM 
	                                    vgallery_type_group
	                                WHERE vgallery_type_group.`type` = 'detail'
	                                [AND] [WHERE]
	                                [HAVING]
	                                [ORDER] [COLON] vgallery_type_group.name
	                                [LIMIT]";
	        $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery/type/group/modify";
	        $oField->actex_dialog_edit_params = array("keys[ID]" => null);
	        $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=vgalleryTypeGroupModify_confirmdelete";
	        $oField->resources[] = "vgalleryTypeGroupModify";
	        $oField->actex_update_from_db = true;
	        //$oField->actex_autocomp = true;
	        $oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
	        $oField->default_value = new ffData($field_default["ID_group_detail"], "Number");
	        $oRecord->addContent($oField, $group_detail); 
	    } else
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "parent_detail";
	        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_group_preview");
	        $oField->default_value = new ffData($field_default["parent_detail"], "Number");
	        $oRecord->addContent($oField, $group_detail);
	
	    }

		$img_setting_columns = array(6,6,6,6);
		if (strlen($framework_css_name)) {
			if (check_function("set_fields_grid_system")) {
				set_fields_grid_system($oRecord, array(
						"group" => $group_detail
						, "fluid" => false
						, "class" => false
						, "wrap" => false
						, "extra" => false
						, "image" => array(
							"prefix" => "settings_type_detail"
							, "source_SQL" => "SELECT ID, name, type FROM 
								                (
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 8 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                    UNION
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 15 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                    UNION
								                    ( 
								                        SELECT 
								                            ID
								                            , name
								                            , 16 AS type 
								                        FROM " . CM_TABLE_PREFIX . "showfiles_modes
								                        ORDER BY name
								                    )
								                ) AS tbl_src
								                [WHERE]
								                ORDER BY tbl_src.name"
							, "father" => "ID_extended_type"
							, "related_field" => "type"
							, "default_value" => array(
								$field_default["settings_type_detail"]
								, $field_default["settings_type_detail_md"]
								, $field_default["settings_type_detail_sm"]
								, $field_default["settings_type_detail_xs"]
							)
						)
					), $framework_css
				);
			}
			
			if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") {
			    $img_setting_columns = array(12,12,12,12);
			}		
		}	    

		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_view_mode_detail";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode_detail");
		$oField->widget = "actex";
	    if(check_function("query_plugin_js"))
			$oField->source_SQL = query_plugin_js("extended_type", array("VIRTUAL_GALLERY"));	
		/*
		$oField->source_SQL = " SELECT ID, name, type FROM
								(
									SELECT DISTINCT js.name AS ID
										, js.name AS name
										, extended_type.ID AS type
									FROM layout_type_plugin
				                        INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status = 1
				                        INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND (layout_type.name = 'VIRTUAL_GALLERY')
				                        LEFT JOIN extended_type ON FIND_IN_SET(extended_type.ID, layout_type_plugin.limit_ext_type)
				                    WHERE extended_type.ID > 0
				                ) AS tbl_src
				                [WHERE]
				                ORDER BY tbl_src.name";
		*/
		$oField->actex_father = "ID_extended_type";
		$oField->actex_related_field = "type";
		//$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
		//$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		//$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
		//$oField->resources[] = "ExtrasImageModify";
		$oField->actex_update_from_db = true;
		$oField->actex_hide_empty = "all";
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oField->default_value = new ffData($field_default["display_view_mode_detail"]);
		$oField->setWidthComponent($img_setting_columns);
		$oRecord->addContent($oField, $group_detail);	    
	    
    
	    /**
	    *  Detail Field Container 
	    */
		$oRecord->addContent(null, true, "DetailFieldContainer"); 
		$oRecord->groups["DetailFieldContainer"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_detail . "_container")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 //, "primary_field" => "field_fluid_thumb"
										 , "tab_dialog" => $group_detail
		                                 , "cols" => 1
		                              );
		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array(
		            "group" => "DetailFieldContainer"
		            , "fluid" => array(
		                "name" => "field_fluid_detail"
		                , "prefix" => "field_grid_detail"
		                , "one_field" => true
		                , "hide" => false
		                , "full_row" => true
                        , "default_value" => new ffData("1", "Number")
		            )
		            , "class" => array(
                		"name" => "field_class_detail"
		            )
		            , "wrap" => false
		        ), $framework_css
		    );
		}  
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_detail_htmltag";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_detail_htmltag");
		$oField->base_type = "Number";
		$oField->widget = "actex";
		$oField->source_SQL = "SELECT 
									vgallery_fields_htmltag.ID
									, IF(vgallery_fields_htmltag.attr = ''
										, vgallery_fields_htmltag.tag
										, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
									) AS name
		                        FROM 
		                            vgallery_fields_htmltag
		                        [WHERE]
		                        [HAVING]
		                        ORDER BY vgallery_fields_htmltag.tag";
		$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
		$oField->resources[] = "VGalleryHtmlTagModify";
		$oField->actex_update_from_db = true;
		$oField->actex_hide_empty = "all";
		$oField->multi_select_noone = true;
		$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
		$oField->multi_select_noone_val = new ffData("-1", "Number");
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
		$oField->default_value = new ffData($field_default["ID_detail_htmltag"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "DetailFieldContainer");
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "custom_detail_field";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_custom_detail_field");
		$oField->extended_type = "Text";
		$oField->default_value = new ffData($field_default["custom_detail_field"]);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "DetailFieldContainer");	

		
		/**
	    *  Detail Field Settings 
	    */
		$oRecord->addContent(null, true, "DetailFieldSettings"); 
		$oRecord->groups["DetailFieldSettings"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_detail . "_settings")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 //, "primary_field" => "field_fluid_thumb"
										 , "tab_dialog" => $group_detail
		                                 , "cols" => 1
		                              );		

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_detail_empty";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_empty");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_detail_empty"], "Number");
		$oRecord->addContent($oField, "DetailFieldSettings"); 

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_detail_cascading";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_cascading");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_detail_cascading"], "Number");
		$oRecord->addContent($oField, "DetailFieldSettings"); 

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "fixed_pre_content_detail";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_pre_content");
	    $oField->extended_type = "Text";
	    $oField->default_value = new ffData($field_default["fixed_pre_content_detail"], "Number");
	    $oField->setWidthComponent(6);
	    $oRecord->addContent($oField, "DetailFieldSettings");    
	        
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "fixed_post_content_detail";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_post_content");
	    $oField->extended_type = "Text";
	    $oField->default_value = new ffData($field_default["fixed_post_content_detail"], "Number");
	    $oField->setWidthComponent(6);
	    $oRecord->addContent($oField, "DetailFieldSettings");  

		if($src_type == "vgallery") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "limit_detail_by_layouts";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_detail_layouts");
			$oField->base_type = "Text";
			$oField->extended_type = "Selection";
			$oField->source_SQL = "SELECT layout.ID, layout.name
			                        FROM layout
			                            INNER JOIN layout_type ON layout_type.ID = layout.ID_type
			                            INNER JOIN vgallery ON vgallery.name = layout.value	
			                        WHERE layout_type.name = 'VIRTUAL_GALLERY'
                        				AND FIND_IN_SET(" . $db_gallery->toSql($ID_vgallery_type, "Number") . ", vgallery.limit_type)
			                        ORDER BY layout.name ";
			$oField->control_type = "input";
			$oField->widget = "checkgroup";
			$oField->grouping_separator = ",";
			$oField->default_value = new ffData($field_default["limit_detail_by_layouts"]);
			$oRecord->addContent($oField, "DetailFieldSettings");	
		}

		/**
	    *  Detail Field Label 
	    */
		$oRecord->addContent(null, true, "DetailFieldLabel"); 
		$oRecord->groups["DetailFieldLabel"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_detail . "_label")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 , "primary_field" => "enable_detail_label"
										 , "tab_dialog" => $group_detail
		                                 , "cols" => 1
		                              );
		                              
		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_detail_label";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_label");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_detail_label"], "Number");
		$oRecord->addContent($oField, "DetailFieldLabel"); 
		                              
		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array( 
		            "group" => "DetailFieldLabel"
		            , "fluid" => array(
		                "name" => "label_fluid_detail"
		                , "prefix" => "label_grid_detail" 
		                , "one_field" => true
		                , "hide" => false
		                , "row" => false
		                , "full_row" => true
		                , "default_value" => new ffData("1", "Number") 
		            )
		            , "class" => false
		            , "wrap" => false
		        ), $framework_css
		    );
		}  

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID_label_detail_htmltag";
	    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_label_detail_htmltag");
	    $oField->base_type = "Number";
	    $oField->widget = "actex";
	    $oField->source_SQL = "SELECT 
	                                vgallery_fields_htmltag.ID
	                                , IF(vgallery_fields_htmltag.attr = ''
	                                    , vgallery_fields_htmltag.tag
	                                    , CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
	                                ) AS name
	                            FROM 
	                                vgallery_fields_htmltag
	                            [WHERE]
	                            [HAVING]
	                            ORDER BY vgallery_fields_htmltag.tag";
	    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
	    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
	    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
	    $oField->resources[] = "VGalleryHtmlTagModify";
	    $oField->actex_update_from_db = true;
	    $oField->actex_hide_empty = "all";
		$oField->multi_select_noone = true;
		$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
		$oField->multi_select_noone_val = new ffData("-1", "Number");
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
	    $oField->default_value = new ffData($field_default["ID_label_detail_htmltag"], "Number");
	    $oRecord->addContent($oField, "DetailFieldLabel"); 
	}
	/***********
	*  Group BackOffice
	*/
    $group_backoffice = "backoffice";
	if(!$group_limit || array_search($group_backoffice, $group_limit) !== false) {
	    $oRecord->addContent(null, true, $group_backoffice); 
	    $oRecord->groups[$group_backoffice] = array(
													"title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_backoffice)
													//, "title_class" => "dialogSubTitleTab dep-backoffice"
													, "tab_dialog" => true
													, "tab_dialog_selected" => ($group_sel == $group_backoffice)
													, "cols" => 1
													, "class" => ""
	                                              //   , "tab" => $group_backoffice
	                                              );   

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_group_backoffice";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_group_backoffice");
		$oField->base_type = "Number";
		$oField->widget = "actex";
		$oField->source_SQL = "SELECT 
									vgallery_type_group.ID
									, vgallery_type_group.name
		                        FROM 
		                            vgallery_type_group
		                        WHERE vgallery_type_group.`type` = 'backoffice'
		                        [AND] [WHERE]
		                        [HAVING]
		                        [ORDER] [COLON] vgallery_type_group.name
		                        [LIMIT]";
		$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery/type/group/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=vgalleryTypeGroupModify_confirmdelete";
		$oField->resources[] = "vgalleryTypeGroupModify";
		$oField->actex_update_from_db = true;
		//$oField->actex_autocomp = true;
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oField->default_value = new ffData($field_default["ID_group_backoffice"], "Number");
		$oRecord->addContent($oField, $group_backoffice); 
		    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "require";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_require");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["require"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_backoffice);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_check_control";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_check_control");
		$oField->base_type = "Number";
		$oField->extended_type = "Selection";
		$oField->source_SQL = "SELECT check_control.ID, check_control.name FROM check_control ORDER BY check_control.name";
		$oField->default_value = new ffData($field_default["ID_check_control"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, $group_backoffice);

		if(!$skip_lang) {
			$sSQL = "SELECT * 
					FROM " . FF_PREFIX . "languages
					WHERE " . FF_PREFIX . "languages.status > 0";
			$db_gallery->query($sSQL);
			if($db_gallery->nextRecord()) {
				$oField = ffField::factory($cm->oPage);
				$oField->id = "disable_multilang";
				$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_disable_multilang");
				$oField->base_type = "Number";
				$oField->control_type = "checkbox";
				$oField->checked_value = new ffData("1", "Number");
				$oField->unchecked_value = new ffData("0", "Number");
				$oField->default_value = new ffData($field_default["disable_multilang"], "Number");
				$oRecord->addContent($oField, $group_backoffice); 
			}	
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "enable_tip";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_tip");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData($field_default["enable_tip"], "Number");
		$oRecord->addContent($oField, $group_backoffice);
		
		
		$oRecord->addContent(null, true, "BackOfficeFieldContainer"); 
		$oRecord->groups["BackOfficeFieldContainer"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_backoffice . "_container")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 , "tab_dialog" => $group_backoffice
		                                 , "cols" => 1
		                              );				

		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array( 
		            "group" => "BackOfficeFieldContainer"
		            , "fluid" => array(
		                "name" => "field_fluid_backoffice"
		                , "prefix" => "field_grid_backoffice" 
		                , "one_field" => true
		                , "hide" => false
		                , "full_row" => true
		                , "skip-prepost" => false
                        , "default_value" => new ffData("1", "Number")
		            )
		            , "class" => array(
                		"name" => "field_class_backoffice" 
		            )
		            , "wrap" => false
		        ), $framework_css
		    );
		} 		
			
		$oRecord->addContent(null, true, "BackOfficeFieldLabel"); 
		$oRecord->groups["BackOfficeFieldLabel"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_backoffice . "_label")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 , "tab_dialog" => $group_backoffice
		                                 , "cols" => 1
		                              );				

		if(check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array( 
		            "group" => "BackOfficeFieldLabel"
		            , "fluid" => array(
		                "name" => "label_fluid_backoffice"
		                , "prefix" => "label_grid_backoffice" 
		                , "one_field" => true
		                , "hide" => false
		                , "row" => false
		                , "full_row" => true
		                , "skip-prepost" => false
		                , "default_value" => new ffData("1", "Number")
		            )
		            , "class" => false
		            , "wrap" => false
		        ), $framework_css
		    );
		} 
			
		$oRecord->addContent(null, true, "BackOfficeFieldPermissions"); 
		$oRecord->groups["BackOfficeFieldPermissions"] = array(
		                                 "title" => ffTemplate::_get_word_by_code("vgallery_field_" . $group_backoffice . "_permissions")
		                                 //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		                                 //, "title_field" => "thumb_fluid"
										 , "tab_dialog" => $group_backoffice
		                                 , "cols" => 1
		                              );		

		$oField = ffField::factory($cm->oPage);
		$oField->id = "limit_by_groups";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_groups");
		$oField->base_type = "Text";
		$oField->extended_type = "Selection";
		$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . Cms::env("MOD_AUTH_GUEST_GROUP_NAME") . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
		$oField->control_type = "input";
		$oField->widget = "checkgroup";
		$oField->grouping_separator = ",";
		$oField->default_value = new ffData($field_default["limit_by_groups"]);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "BackOfficeFieldPermissions");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "limit_by_groups_frontend";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_groups_frontend");
		$oField->base_type = "Text";
		$oField->extended_type = "Selection";
		$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . Cms::env("MOD_AUTH_GUEST_GROUP_NAME") . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
		$oField->control_type = "input";
		$oField->widget = "checkgroup";
		$oField->grouping_separator = ",";
		$oField->default_value = new ffData($field_default["limit_by_groups_frontend"]);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "BackOfficeFieldPermissions");   
	}
}

$cm->oPage->addContent($oRecord);


$cm->oPage->tplAddJs("ff.cms.admin", "ff.cms.admin.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools", false, $cm->isXHR());
$js = '
	$(function() {
		ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . '/javascript/tools/ff.cms.admin.js", function() {
			ff.cms.admin.makeNewUrl();
		});
		jQuery("#VGalleryFieldModify_name").keyup(function(){
			ff.cms.admin.makeNewUrl();
		});
		
		jQuery(".enable-field").each(function() {
			checkEnableField(jQuery(this));
		});

		jQuery("INPUT.enable-field").click(function() {
			
			checkEnableField(jQuery(this));
		});
		
		jQuery(".user-permission input[type=checkbox]").click(function() {
			var id = $(this).attr("class");
			setUserPermission(id);
		});
		
		jQuery(document).on("change keypress", "TD.disabled INPUT, TD.disabled SELECT", function(){
			enableField(jQuery(this));
		});
	});
	
	function enableField(field) {
		var eq = field.closest("TD").index();
		field.closest("TABLE").find("TH:eq(" + eq + ") .enable-field").trigger("click");
	}

	function checkEnableField(field) {
		
		var selected_class = field.parents("TH").attr("class");
		if(!jQuery(field).is(":checked")) {
			jQuery("TD." + selected_class).addClass("disabled");
		} else{
			jQuery("TD." + selected_class).removeClass("disabled");
		}
	}

	function setUserPermission(id) {
		var list = "";
		$("input[type=checkbox]." + id + ":checked").each(function(){
			if(list.length)
				list += ",";
			list += $(this).val();
		});
		jQuery("#" + id).val(list);
	}';

$js = '<script type="text/javascript">' . $js . '</script>';

$cm->oPage->addContent($js);	

function FormConfigField_on_before_parse_row($component) {
    if(isset($component->grid_buttons["module_form_dep"])) {
        $component->grid_buttons["module_form_dep"]->class = Cms::getInstance("frameworkcss")->get("chain", "icon");
        $component->grid_buttons["module_form_dep"]->action_type = "submit"; 
        $component->grid_buttons["module_form_dep"]->label = ffTemplate::_get_word_by_code("module_form_dep");
        $component->grid_buttons["module_form_dep"]->form_action_url = $component->grid_buttons["module_form_dep"]->parent[0]->page_path . "/dep?[KEYS]" . $component->grid_buttons["module_form_dep"]->parent[0]->addit_record_param . "setcv=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $component->grid_buttons["module_form_dep"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        } else {
            $component->grid_buttons["module_form_dep"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        }
    }
    if(isset($component->grid_fields["aspect"])) 
    {
        
    }
}

function FormExtraFieldModify_on_do_action($component, $action) {
    
    
    switch($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];

	        if(isset($component->form_fields["copy-from"])) {
                    ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?field=" . $component->form_fields["copy-from"]->getValue() . "&type=" . urlencode($component->user_vars["ID_type"]) . "&src=" . urlencode($component->user_vars["src_type"]) . "&ret_url=" . urlencode($ret_url));
                }
                break;
        default:
                    break;
    }
    
}


function FormExtraFieldModify_on_done_action($component, $action) {
   $src_type =  $component->user_vars["src_type"];

   switch($action) {
        case "update":
            $db = ffDB_Sql::factory();
            if($component->user_vars["advanced_group"])
            {
                if($component->user_vars["ID_type"])
                {
                    $sSQL = "SELECT vgallery_type_group.*
                                FROM vgallery_type_group
                                WHERE vgallery_type_group.ID_type = " . $db->toSql($component->user_vars["ID_type"], "Number");
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            $arrGroupType[$db->getField("ID", "number", true)] = $db->getField("name", "Text", true);
                        } while ($db->nextRecord());
                    }
                    $sSQL = "UPDATE " . $src_type . "_fields SET
                                    parent_thumb = " . $db->toSql($arrGroupType[$component->form_fields["ID_group_thumb"]->getValue()]) . "
                                    , parent_detail = " . $db->toSql($arrGroupType[$component->form_fields["ID_group_detail"]->getValue()]) . "
                                WHERE ID = " . $db->toSql($component->key_fields["ID"]->value);
                    $db->execute($sSQL);
                                    
                }
             }
        	/*if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}*/
        	
        	break;
        case "confirmdelete":
        	/*if(check_function("MD_form_delete"))
        		MD_form_delete($component->key_fields["formcnf-ID"]->getValue()); //da ricavare l'id del form se necessario

        	if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
        		die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}*/
        	break;
        default:
    }
    return true;
}

function VgalleryModify_on_process_template($component, $tpl) {
	$db = ffDB_Sql::factory();

   	$src_type =  $component->user_vars["src_type"];
	$limit_by_groups = array();
	$limit_by_groups_frontend = array();
	$custom_tag = array( 
		"class_name" => array("label" => ffTemplate::_get_word_by_code("class_name"))
		, "real_name" => array("label" => ffTemplate::_get_word_by_code("real_name"))
		, "value_link" => array("label" => ffTemplate::_get_word_by_code("value_link"))
		, "rel_plugin" => array("label" => ffTemplate::_get_word_by_code("rel_plugin"))
		, "class_plugin" => array("label" => ffTemplate::_get_word_by_code("class_plugin"))
		, "target_link" => array("label" => ffTemplate::_get_word_by_code("target_link"))
		, "value" => array("label" => ffTemplate::_get_word_by_code("value"))
		, "class_name_label" => array("label" => ffTemplate::_get_word_by_code("class_name_label"))
		, "real_name_label" => array("label" => ffTemplate::_get_word_by_code("real_name_label"))
		, "value_label" => array("label" => ffTemplate::_get_word_by_code("value_label"))
		, "alt_name" => array("label" => ffTemplate::_get_word_by_code("alt_name"))
	);
	
	$tpl->set_var("row_class", Cms::getInstance("frameworkcss")->get("", "row-default"));
	if(is_array($custom_tag) && count($custom_tag))
	{
		foreach($custom_tag AS $key => $label)
		{
			$tpl->set_var("admin_vgallery_tag_field_class", $key);
			$tpl->set_var("admin_vgallery_tag_field_label", $label["label"]);
			$tpl->parse("SezCustomFieldTagItem", true);
		}
		$tpl->parse("SezCustomFieldTag", false);
	}
	//class_name, real_name, value_link, rel_plugin, class_plugin, target_link, value, class_name_label, real_name_label, value_label, alt_name
	if(isset($_REQUEST["keys"]["ID"]))
	{
		$sSQL = "SELECT " . $src_type . "_fields.*
					FROM " . $src_type . "_fields
					WHERE " . $src_type . "_fields.ID = " . $db->toSql($_REQUEST["keys"]["ID"]);
		$db->query($sSQL);
		if($db->nextRecord())
		{
			$string_limit_by_groups = $db->getField("limit_by_groups", "Text", true);
			$string_limit_by_groups_frontend = $db->getField("limit_by_groups_frontend", "Text", true);
		}
		$limit_by_groups = explode(",", $string_limit_by_groups);
		$limit_by_groups_frontend = explode(",", $string_limit_by_groups_frontend);
	}
	
	$sSQL = "SELECT DISTINCT gid, IF(name='" . Cms::env("MOD_AUTH_GUEST_GROUP_NAME") . "', 'default', name) AS name
				FROM " . CM_TABLE_PREFIX . "mod_security_groups
				ORDER BY name";
	$db->query($sSQL);
	if($db->nextRecord())
	{
		do {
			$gid = $db->getField("gid", "Number", true);
			$tpl->set_var("value_limit_by_groups", $string_limit_by_groups);
			$tpl->set_var("value_limit_by_groups_frontend", $string_limit_by_groups_frontend);
			if(array_search($gid, $limit_by_groups) === false)
			{
				$tpl->set_var("properties", "");
			} else
			{
				$tpl->set_var("properties", 'checked="checked"');
			}
			if(array_search($gid, $limit_by_groups_frontend) === false)
				$tpl->set_var("properties_frontend", "");
			else
				$tpl->set_var("properties_frontend", 'checked="checked"');
			$tpl->set_var("user_type_name", $db->getField("name", "Text", true));
			$tpl->set_var("ID_user_type", $gid);
			$tpl->parse("SezUserTypeItem", true);
		} while ($db->nextRecord());
	}
}
?>
