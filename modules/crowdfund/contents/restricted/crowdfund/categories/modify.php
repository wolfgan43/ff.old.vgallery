<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "CategoriesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_categories_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_categories";
$oRecord->addEvent("on_done_action", "CategoriesModify_on_done_action");

$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("static_page_parent");
//$oField->extended_type = "Selection";
$oField->source_SQL = "(SELECT '/', 'Home'
						) UNION (
							SELECT
								IF(" . CM_TABLE_PREFIX . "mod_crowdfund_categories.parent = '/'
									, CONCAT( " . CM_TABLE_PREFIX . "mod_crowdfund_categories.parent, " . CM_TABLE_PREFIX . "mod_crowdfund_categories.name )
									, CONCAT( " . CM_TABLE_PREFIX . "mod_crowdfund_categories.parent, '/', " . CM_TABLE_PREFIX . "mod_crowdfund_categories.name )
								) AS ID
								, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_categories.name = ''
									, 'Home'
									, CONCAT( 'Home', IF(" . CM_TABLE_PREFIX . "mod_crowdfund_categories.parent = '/', '', " . CM_TABLE_PREFIX . "mod_crowdfund_categories.parent), '/', " . CM_TABLE_PREFIX . "mod_crowdfund_categories.name )
								) AS name
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories 
	                        WHERE 1
							[AND] [WHERE]
		                    [HAVING]
	                        ORDER BY name
	                    )";
/*$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare_having = "ID"; 
$oField->autocompletetoken_limit = 1;*/
                        
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true; 
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);   

$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
$db->query($sSQL);
if($db->nextRecord()) {
    $count_lang = $db->getField("count_lang", "Number", true);
}

$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}
$oDetail->id = "CategoriesDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_categories" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "CategoriesDetail_on_do_action");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language 
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.smart_url AS smart_url
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.title AS title
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.description AS description
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.image AS image
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID 
                                    	AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_categories = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_smart_url");
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_title");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "image";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_image");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/categories/[ID_FATHER]/[CODE_LANG_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/categories";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->widget = "uploadify"; 
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
//$oField->uploadify_model = "horizzontal";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_categories_detail_value");
$oField->display_label = false;
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
          
function CategoriesDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
			$smart_url = ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue());
			$ID_languages = $component->recordset[$rst_key]["ID_languages"]->getValue();
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.smart_url
       			 FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages
       			 WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_categories <> " . $db->toSql($ID_node, "Number") . "
       			 	AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.smart_url = " . $db->toSql($smart_url) . "
       			 	AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_languages = " . $db->toSql($ID_languages, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				$component->displayError(ffTemplate::_get_word_by_code("crowdfund_cateogory_smart_url_not_unic") . "(" . $component->recordset[$rst_key]["language"]->getValue() . ")");
				return true;
			}

			$component->recordset[$rst_key]["smart_url"]->setValue($smart_url);
			if($component->recordset[$rst_key]["code_lang"]->getValue() == LANGUAGE_DEFAULT) {
				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_categories SET 
							" . CM_TABLE_PREFIX . "mod_crowdfund_categories.name = " . $db->toSql($component->recordset[$rst_key]["smart_url"]->getValue()) . " 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID = " . $db->toSql($ID_node);
				$db->execute($sSQL);
			}
        }
    }
}          

function CategoriesModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
        //UPDATE CACHE
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `drafts`.last_update FROM drafts WHERE drafts.ID = " . $db->toSql($component->key_fields["ID"]->value) . ") 
                WHERE 
                    (
                        layout.value = " . $db->toSql($component->key_fields["ID"]->value) . "
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("STATIC_PAGE_BY_DB") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    }

    
}

  
?>
