<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$db = ffDB_Sql::factory();

$is_owner = false;
if (!Auth::env("AREA_DRAFT_SHOW_MODIFY")) {
	$owner = $_REQUEST["owner"];
	if($owner == Auth::get("user")->id) {
    	use_cache(false);
    	$is_owner = true;
	} else {
	    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
}

if(strpos($cm->oPage->page_path, VG_SITE_DRAFT) === 0
	|| strpos($cm->oPage->page_path, VG_SITE_RESTRICTED) === 0
) {
	$simple_interface = true;
} else {
	$simple_interface = false;
}

if(isset($_REQUEST["keys"]["ID"]))
{
	$title_draft = ffTemplate::_get_word_by_code("modify_draft");
	$sSQL = "SELECT drafts.name
				FROM drafts
				WHERE drafts.ID = " . $db->tosql($_REQUEST["keys"]["ID"], "Number");
	$db->query($sSQL);
	if($db->nextRecord())
	{
		$title_draft .= ": " . $db->getField("name", "Text", true);
	}
} else
{
	$title_draft = ffTemplate::_get_word_by_code("addnew_draft");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "DraftModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("drafts_modify_title");
$oRecord->src_table = "drafts";
$oRecord->buttons_options["delete"]["display"] = Auth::env("AREA_DRAFT_SHOW_DELETE");
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->addEvent("on_done_action", "DraftModify_on_done_action");
/* Title Block */
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . Cms::getInstance("frameworkcss")->get("vg-draft", "icon-tag", array("2x", "content")) . $title_draft . '</h1>';
	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      /*
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("drafts_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  */

if($globals->ID_domain > 0) {
	$oRecord->additional_fields["ID_domain"] =  new ffData($globals->ID_domain, "Number");
} else {
	$sSQL = "SELECT cache_page_alias.* FROM cache_page_alias WHERE `status` > 0";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_domain";
		$oField->label = ffTemplate::_get_word_by_code("drafts_modify_domain");
		$oField->widget = "activecomboex"; 
		$oField->source_SQL = "SELECT cache_page_alias.`ID`
									, cache_page_alias.`host`
								FROM cache_page_alias
								WHERE cache_page_alias.`status` > 0
								ORDER BY cache_page_alias.`host`";
		$oField->actex_update_from_db = true;
		$oRecord->addContent($oField);
	}	
}

$oRecord->insert_additional_fields["owner"] =  new ffData(Auth::get("user")->id, "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);   

$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $count_lang = $db_gallery->getField("count_lang", "Number", true);
}
$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}

$oDetail->id = "DraftsDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = "drafts_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_drafts" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "DraftsDetail_on_do_action");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language 
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'
                                ORDER BY " . FF_PREFIX . "languages.description";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    drafts_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , drafts_rel_languages.title AS title
                                    , drafts_rel_languages.value AS value
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN drafts_rel_languages ON  drafts_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND drafts_rel_languages.ID_drafts = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                ORDER BY " . FF_PREFIX . "languages.description
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_ID_languages");
$oField->base_type = "Number";
$oField->required = true;
$oDetail->addHiddenField($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);*/

if(!$simple_interface) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "title";
	$oField->label = ffTemplate::_get_word_by_code("drafts_detail_title");
	$oField->required = true;
	$oDetail->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_value");
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
          
function DraftsDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
            if($component->recordset[$rst_key]["ID_languages"]->getValue() != LANGUAGE_DEFAULT_ID)
                continue;
                
			if(isset($component->recordset[$rst_key]["title"])) {
	            if(strlen($component->recordset[$rst_key]["title"]->getValue())) {
	                 $sSQL = "UPDATE 
	                        `drafts` 
	                    SET 
	                        `name` = " . $db->toSql(ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue())) . " 
	                    WHERE `drafts`.`ID` = " . $db->toSql($ID_node, "Number");
	                $db->execute($sSQL);
	            } else {
	                $component->displayError(ffTemplate::_get_word_by_code("smart_url_empty"));
	                return true;
	            }
			}
        }
    }
}          

function DraftModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    	if(check_function("refresh_cache")) {
    		refresh_cache("D", $component->key_fields["ID"]->getValue(), "update");
		}
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