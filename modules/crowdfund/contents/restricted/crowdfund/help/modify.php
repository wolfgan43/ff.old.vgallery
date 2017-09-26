<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
$db = ffDB_Sql::factory();

$already_hinted = array();
$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help.*
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help
			WHERE 1" . (isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"]>0
							? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help.ID !=" . $db->tosql($_REQUEST["keys"]["ID"], "Number") 
							: "");
$db->query($sSQL); 
if ($db->nextRecord())
{
	do
	{
		$field_name = $db->getField("field_name", "text", true);
		$already_hinted[$field_name] = $field_name;
	} while ($db->nextRecord());
}

//$idea_help = mod_crowdfund_get_help_array();
$idea = mod_crowdfund_get_idea_structure("field");
foreach ($idea AS $idea_key => $idea_value)
{
	if (is_array($already_hinted) && !(array_key_exists($idea_key, $already_hinted)))
	{
		$multi_pairs[strip_tags(ffTemplate::_get_word_by_code("crowdfund_" . $idea_value["prefix"] . $idea_key))] = array (new ffData($idea_key), new ffData(strip_tags(ffTemplate::_get_word_by_code("crowdfund_" . $idea_value["prefix"] . $idea_key))));
	}
			
}

/*
ffErrorHandler::raise("ASD", E_USER_ERROR, $db, get_defined_vars()); 
*/  
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "HelpModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_help_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_help";
$oRecord->addEvent("on_done_action", "HelpModify_on_done_action");

$oField = ffField::factory($cm->oPage); 
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "field_name";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_name");
$oField->multi_pairs = $multi_pairs;
$oField->base_type = "Text"; 
$oField->extended_type = "Selection";
$oField->required = true;
$oRecord->addContent($oField);

$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang 
			FROM " . FF_PREFIX . "languages 
			WHERE " . FF_PREFIX . "languages.status = '1'";
$db->query($sSQL);
if($db->nextRecord()) 
{
    $count_lang = $db->getField("count_lang", "Number", true);
}

$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}
$oDetail->id = "HelpDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("crowdfund_pledge_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_help" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "HelpDetail_on_do_action");
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
									" . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.ID AS ID                                    
									, " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.description AS description
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID
									AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.ID_help = [ID_FATHER]
								WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_description");
$oField->base_type = "Text";
$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);
          
function HelpDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
			//$component->recordset[$rst_key]["smart_url"]->setValue(ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue()));
        }
    }
}       
         

function HelpModify_on_done_action($component, $action) {
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