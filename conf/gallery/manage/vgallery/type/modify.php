<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_TYPE_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_vgallery_type = $_REQUEST["keys"]["ID"];
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
		die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
	} else {
		die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("VGalleryTypeModify")), true));
		//ffRedirect($_REQUEST["ret_url"]);
	}
}    

if($ID_vgallery_type > 0) {
	$sSQL = "SELECT " . $src_type . "_type.* FROM " . $src_type . "_type WHERE " . $src_type . "_type.ID = " . $db_gallery->toSql($ID_vgallery_type);
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$is_public = $db_gallery->getField("public", "Number", true);
	}
} else {
	$is_public = false;
}
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryTypeModify";
$oRecord->resources[] = $oRecord->id;

$oRecord->src_table = $src_type . "_type";
$oRecord->display_required_note = FALSE;
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$oRecord->addEvent("on_done_action", "VGalleryTypeModify_on_done_action");

$oRecord->user_vars["src_type"] = $src_type;
$oRecord->user_vars["ID_vgallery_type"] = $ID_vgallery_type;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_name");
if(!MASTER_CONTROL && $is_public) {
	$oField->control_type = "label";
}
$oRecord->addContent($oField);
/*
if($ID_vgallery_type > 0) {
	$oField = ffField::factory($cm->oPage);
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort");
	$oField->id = "sort_default";
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT NULL, ID, name FROM " . $src_type . "_fields WHERE " . $src_type . "_fields.ID_type = " . $db_gallery->toSql($ID_vgallery_type, "Number");
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sort_method_default";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_method");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
	                            array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ascending"))),
	                            array(new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("discending")))
	                       );
	$oRecord->addContent($oField);	
}*/

$oField = ffField::factory($cm->oPage);
$oField->id = "is_dir_default";
$oField->container_class = "is_dir_default";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_is_dir");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oRecord->addContent($oField); 

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "field-detail";
$oDetail->title = ffTemplate::_get_word_by_code("admin_vgallery_type_fields");
$oDetail->src_table = $src_type . "_fields";
$oDetail->order_default = "order_thumb";
$oDetail->fields_relationship = array ("ID_type" => "ID");
if(!MASTER_CONTROL && $is_public) {
	$oDetail->display_new = false;
	$oDetail->display_delete = false;
} else {
	$oDetail->display_new = true;
	$oDetail->display_delete = true;
}
$oDetail->tab = true;
$oDetail->tab_label = "name";

$oDetail->addEvent("on_do_action", "VGalleryTypeModifyDetail_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_name");
$oField->required = true;
if(!MASTER_CONTROL && $is_public) {
	$oField->control_type = "label";
}
$oDetail->addContent($oField);   

$sSQL = "SELECT ID FROM vgallery_fields_data_type WHERE name = 'data'";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord())
	$ID_data_type = $db_gallery->getField("ID", "Number");

$oField = ffField::factory($cm->oPage);
$oField->id = "tblsrc";
$oField->data_source = "ID_data_type";
$oField->container_class = "data_type";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_type");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT 
                            ID
                            , name 
                        FROM vgallery_fields_data_type
                        WHERE 1
                        	AND status > 0
                        ORDER BY name";
$oField->actex_child = array("items", "ID_extended_type");
$oField->default_value = $ID_data_type;
$oField->required = true;
$oField->actex_update_from_db = true;
$oDetail->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "schemaorg";
$oField->container_class = "schemaorg-field";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_schemaorg_field");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "strip_stop_words";
$oField->container_class = "stopwords-field";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_strip_stop_words");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "tags_in_keywords";
$oField->container_class = "tags-in-keywords-field";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_tags_in_keywords");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "rule_meta_title";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_rule_meta_title");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "rule_meta_description";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_rule_meta_description");
$oRecord->addContent($oField);	


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
	                                " . $db_gallery->toSql($relative_path, "Text") . " AS nameID
	                                , " . $db_gallery->toSql(basename($real_file), "Text") . " AS name
	                                , " . $db_gallery->toSql($ID_data_type_static, "Number") . " AS type
	                            )
	                            ";
	        }
	    }
	}
}

if(check_function("get_schema_def")) {
	$schema = get_schema_def(false);
	if(is_array($schema["db"]["data_source"]) && count($schema["db"]["data_source"])) {
		foreach($schema["db"]["data_source"] AS $table_alt => $table_def) {
            if(!isset($table_def["limit"]) || $table_def[$src_type]) {
			    $sSQL_file .= " UNION
	            				(
	                            SELECT 
	                                " . $db_gallery->toSql($table_alt) . " AS nameID
	                                , " . $db_gallery->toSql($table_def["label"] ? $table_def["label"] : $table_alt) . " AS name
	                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'table.alt') AS type 
	                            )";			
            }
		}
	}
}

$oField = ffField::factory($cm->oPage);
$oField->id = "items";
$oField->data_source = "data_source";
$oField->container_class = "data_source";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_source");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                SELECT nameID, name, type FROM
                (
                        (
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
                        ) UNION (
                            SELECT DISTINCT
                                vgallery_fields_selection_value.name AS nameID
                                , vgallery_fields_selection_value.name AS name
                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'selection') AS type 
                            FROM vgallery_fields_selection_value
                        ) UNION (
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
                        ) UNION (
                            SELECT 
                                name AS nameID
                                , name AS name
                                , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'checkout') AS type 
                            FROM ecommerce_mpay
                            WHERE ecommerce_mpay.ecommerce > 0
                        ) UNION (
	                        SELECT 
	                        	" . $db_gallery->toSql($src_type == "vgallery" ? "vgallery_nodes" : $src_type) . " AS nameID
	                            , " . $db_gallery->toSql(ffTemplate::_get_word_by_code("primary_table")). " AS name
	                            , (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name = 'table.alt') AS type 
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
$oDetail->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "data_limit";
$oField->container_class = "data_limit";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_limit");
$oField->widget = "activecomboex";
$oField->control_type = "checkbox";
$oField->actex_father = "items";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
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
	                        CONVERT(COLUMN_NAME USING utf8) COLLATE utf8_general_ci AS nameID
	                        , CONVERT(COLUMN_NAME USING utf8) COLLATE utf8_general_ci AS name
	                        , CONVERT(TABLE_NAME USING utf8) COLLATE utf8_general_ci AS type 
	                    FROM information_schema.COLUMNS 
	                    WHERE  TABLE_SCHEMA = '" . FF_DATABASE_NAME . "'
	                    	AND COLUMN_NAME NOT LIKE 'ID_%'
	                    	AND COLUMN_NAME NOT LIKE 'IS_%'
	                    	AND COLUMN_NAME NOT LIKE 'USE_%'
                    )
                ) AS datalimit_tbl
                [WHERE]
                ORDER BY name";
$oDetail->addContent($oField, "datatype"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "data_sort";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_sort");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
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
$oField->actex_related_field = "vgallery.name";
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_default");
$oDetail->addContent($oField, "datatype");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_extended_type";
$oField->container_class = "extended_type";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_extended_type");
if(check_function("set_field_extended_type"))
	$oField = set_field_extended_type($oField);
$oField->actex_father = "tblsrc";
$oField->actex_related_field = "limit_by_data_type";
$oField->actex_operation_field = "FIND_IN_SET";
$oField->actex_child = array("settings_type_thumb", "settings_type_detail", "display_view_mode_detail");
$oField->required = false;
$oField->multi_select_one = false;
$oField->multi_select_noone = true;
$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("extended_type_undefined");
$oDetail->addContent($oField, "datatype");  

$oField = ffField::factory($cm->oPage);
$oField->id = "data_sort_method";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_sort_method");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ascending"))),
                            array(new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("discending")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("admin_vgallery_type_sort_method_default");
$oDetail->addContent($oField, "datatype");   

$oField = ffField::factory($cm->oPage);
$oField->id = "require";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_require");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField, "properties");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_check_control";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_check_control");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT check_control.ID, check_control.name FROM check_control ORDER BY check_control.name";
$oDetail->addContent($oField, "properties");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_smart_url";
$oField->container_class = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_smart_url");
$oField->base_type = "Number";
$oDetail->addContent($oField, "dataseo");

$oField = ffField::factory($cm->oPage);
$oField->id = "disable_multilang";
$oField->container_class = "disable_multilang";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_disable_multilang");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oDetail->addContent($oField, "settings"); 
                              
$oField = ffField::factory($cm->oPage);
$oField->id = "enable_in_menu";
$oField->container_class = "enable-in-menu";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_menu");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oDetail->addContent($oField, "settings"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_in_grid";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_grid");
$oField->base_type = "Number";
$oDetail->addContent($oField, "settings");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_in_mail";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_mail");
$oField->base_type = "Number";
$oDetail->addContent($oField, "settings");

if(AREA_SHOW_ECOMMERCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_in_cart";
	$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_cart");
	$oField->base_type = "Number";
	$oDetail->addContent($oField, "settings");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_in_document";
$oField->container_class = "enable_in_document";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_in_document");
$oField->base_type = "Number";
$oDetail->addContent($oField, "settings");   

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_tip";
$oField->container_class = "enable_tip";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_tip");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oDetail->addContent($oField, "settings");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb";
$oField->container_class = "enable_thumb";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parent_thumb";
$oField->container_class = "parent-thumb";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_parent_thumb");
$oDetail->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "order_thumb";
$oField->container_class = "order";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_order");
$oDetail->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_thumb_htmltag";
$oField->container_class = "thumb_htmltag";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_thumb_htmltag");
$oField->widget = "activecomboex";
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
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "meta_description";
$oField->container_class = "meta_description";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_meta_description");
$oDetail->addContent($oField, "dataseo"); */

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_lastlevel";
$oField->container_class = "enable_lastlevel";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_lastlevel");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
//$oField->actex_update_from_db = true;
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("to_detail_content"))),
                            array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("to_large_image")))
                       );
$oField->actex_child = "display_view_mode_thumb";
$oField->default_value = new ffData($arrFieldData["default"]["enable_lastlevel"], "Number");
$oField->multi_select_one = false;
$oDetail->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode_thumb";
$oField->container_class = "display_view_mode";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode");
$oField->widget = "activecomboex";
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
$oDetail->addContent($oField, "datatype"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "settings_type_thumb";
$oField->base_type = "Number";
$oField->container_class = "settings_type";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_settings_type");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name, type FROM 
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
                    ORDER BY tbl_src.name";
$oField->actex_father = "ID_extended_type";
$oField->actex_related_field = "type";
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
$oField->resources[] = "ExtrasImageModify";
$oField->actex_update_from_db = true;
$oDetail->addContent($oField, "datathumb"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_label";
$oField->container_class = "enable_thumb_label";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_label");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_label_thumb_htmltag";
$oField->container_class = "label_thumb_htmltag";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_label_thumb_htmltag");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
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
$oDetail->addContent($oField, "datathumb"); 
    
$oField = ffField::factory($cm->oPage);
$oField->id = "thumb_limit";
$oField->container_class = "thumb_limit";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_thumb_limit");
$oDetail->addContent($oField, "datathumb"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_empty";
$oField->container_class = "enable_thumb_empty";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_empty");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField, "datathumb"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sort";
$oField->container_class = "enable_sort";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_sort");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField, "datathumb"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_cascading";
$oField->container_class = "enable_thumb_cascading";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_thumb_cascading");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oField->default_value = new ffData("0");
$oDetail->addContent($oField, "datathumb"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "custom_thumb_field";
$oField->container_class = "custom_thumb_field";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_custom_thumb_field");
$oField->description = "class_name, real_name, value_link, rel_plugin, class_plugin, target_link, value, class_name_label, real_name_label, value_label, alt_name";
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datathumb");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_pre_content_thumb";
$oField->container_class = "fixed_pre_content";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_pre_content_thumb");
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datathumb");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_post_content_thumb";
$oField->container_class = "fixed_post_content";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_post_content_thumb");
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datathumb");


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
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail";
$oField->container_class = "enable_detail";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField, "datadetail"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "parent_detail";
$oField->container_class = "parent-detail";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_parent_detail");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order_detail";
$oField->container_class = "order-detail";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_order_detail");
$oDetail->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_label";
$oField->container_class = "enable_detail_label";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_label");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField, "datadetail"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_label_detail_htmltag";
$oField->container_class = "label_detail_htmltag";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_label_detail_htmltag");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
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
$oDetail->addContent($oField, "datadetail"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "settings_type_detail";
$oField->base_type = "Number";
$oField->container_class = "settings_type";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_settings_type_detail");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name, type FROM 
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
                    ORDER BY tbl_src.name";
$oField->actex_father = "ID_extended_type";
$oField->actex_related_field = "type";
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
$oField->resources[] = "ExtrasImageModify";
$oField->actex_update_from_db = true;
$oField->multi_select_noone = true;
$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("settings_type_detail_fullsize"); 
$oField->multi_select_noone_val = new ffData("0", "Text");
$oDetail->addContent($oField, "datadetail"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode_detail";
$oField->container_class = "display_view_mode_detail";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode_detail");
$oField->widget = "activecomboex";
if(check_function("query_plugin_js"))
	$oField->source_SQL = query_plugin_js(null, array("VIRTUAL_GALLERY"));	
/*
$oField->source_SQL = " SELECT ID, name, type FROM
						(
							SELECT DISTINCT js.name AS ID
								, js.name AS name
								, extended_type.ID AS type
							FROM layout_type_plugin
		                        INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status > 0
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
$oDetail->addContent($oField, "datatype"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_detail_htmltag";
$oField->container_class = "detail_htmltag";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_detail_htmltag");
$oField->widget = "activecomboex";
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
$oDetail->addContent($oField, "datadetail");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_empty";
$oField->container_class = "enable_detail_empty";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_empty");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField, "datadetail");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_cascading";
$oField->container_class = "enable_detail_cascading";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_enable_detail_cascading");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oField->default_value = new ffData("0"); 
$oDetail->addContent($oField, "datadetail");

$oField = ffField::factory($cm->oPage);
$oField->id = "custom_detail_field";
$oField->container_class = "custom_detail_field";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_custom_detail_field");
$oField->description = "class_name, real_name, value_link, rel_plugin, class_plugin, target_link, value, class_name_label, real_name_label, value_label, alt_name";
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datadetail");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_pre_content_detail";
$oField->container_class = "fixed_pre_content";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_pre_content_detail");
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datadetail");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_post_content_detail";
$oField->container_class = "fixed_post_content";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_fixed_post_content_detail");
$oField->extended_type = "Text";
$oDetail->addContent($oField, "datadetail");

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
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_by_groups";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_groups");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
$oField->control_type = "input";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_by_groups_frontend";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_limit_groups_frontend");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
$oField->control_type = "input";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

function VGalleryTypeModifyDetail_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();

	switch ($action) {
		case "insert":
		case "update":
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
    
    if(strlen($action)) {
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
