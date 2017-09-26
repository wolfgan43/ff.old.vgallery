<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

use_cache(false);
	
$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$ID_idea = 0;

if($_REQUEST["keys"]["ID"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
}

if(!strlen($_REQUEST["ret_url"])) {
	$_REQUEST["ret_url"] = ffCommon_dirname($cm->oPage->page_path);
} 

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
			, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
	            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
	            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
	                AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
	        ) AS name
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
		WHERE 1 
			AND " . $sSQL_Where;
$db->query($sSQL);
if($db->nextRecord()) {
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);
	$idea_name = $db->getField("name", "Text", true);
	
	if(!$permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) {
		if($db->getField("owner", "Number", true) != $UserNID) {
			if(global_settings("MOD_CROWDFUND_ENABLE_TEAM")) {
				$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.*
								, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission AS role_permission
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
							INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_role
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph = (SELECT anagraph.ID 
																									FROM anagraph 
																									WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " 
																									ORDER BY anagraph.ID 
																									LIMIT 1)
							AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission = ''
								OR FIND_IN_SET(" . $db->toSql(basename($cm->oPage->page_path)) . ", " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission))";
				$db->query($sSQL);
				if($db->nextRecord()) {
					$allow_edit = true;
					if(strlen($db->getField("role_permission", "Text", true))) {
						$arrLimit = explode(",", $db->getField("role_permission", "Text", true));
					}
				} else {
					$allow_edit = false;
				}
			} else {
				$allow_edit = false;
			}
		} else {
			$allow_edit = true;
		}
	} else {
		$allow_edit = true;
	}
			
	if($allow_edit) {
		$_REQUEST["keys"]["ID"] = $ID_idea;
	}
}


if(!$_REQUEST["keys"]["ID"] > 0) {
	if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) {
		ffRedirect(FF_SITE_PATH . ffcommon_dirname($cm->oPage->page_path) . "/modify?ret_url=" . urlencode(FF_SITE_PATH . ffcommon_dirname($cm->oPage->page_path)));
	} else {
	    ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	}
}

if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
	$simple_interface = false;
} else {
	$simple_interface = true;
	if(!$_REQUEST["keys"]["ID"] > 0) {
		ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	}
}

//set Header
if(check_function("set_header_page"))
	set_header_page($idea_name);


//menu
$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);
$tpl_detail_empty = mod_crowdfund_get_idea_required($ID_idea);

$cm->oPage->tplAddJs("idea_modify"
	, array(
		"file" => "user_idea_modify.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_businessplan") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->buttons_options["delete"]["display"] = false;


$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->fixed_pre_content = $tpl_detail_empty;

//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);



$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang
		FROM " . FF_PREFIX . "languages 
		WHERE FIND_IN_SET(" . FF_PREFIX . "languages.ID, " . $db->toSql($languages) . ")";
$db->query($sSQL);
if($db->nextRecord()) {
	$count_lang = $db->getField("count_lang", "Number", true);
}

$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}
$oDetail->id = "BusinessPlanDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_idea" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "BusinessPlanDetail_on_do_action");
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
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.description_product AS description_product
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.target_market AS target_market
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.commercial_strategy AS commercial_strategy
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.sell_goal AS sell_goal
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.investment_description AS investment_description
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.weakness_strength AS weakness_strength
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID_idea = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
	                                AND FIND_IN_SET(" . FF_PREFIX . "languages.ID, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
	                                    												FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
	                                    												WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                                    											)
			                                )											
			                        ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description_product";
$oField->container_class = "description-product";
$oField->class="text business-text";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_description_product");
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_DESCRIPTION_PRODUCT");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "target_market";
$oField->container_class = "target-market";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_target_market");// . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_TARGET_MARKET");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "commercial_strategy";
$oField->container_class = "commercial-strategy";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_commercial_strategy");// . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COMMERCIAL_STRATEGY");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sell_goal";
$oField->container_class = "sell-goal";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_sell_goal");// . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_SELL_GOAL");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "investment_description";
$oField->container_class = "investment-description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_investment_description");// . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INVESTMENT_DESCRIPTION");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "weakness_strength"; 
$oField->container_class = "weakness-strength";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_businessplan_detail_weakness_strength");// . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->fixed_pre_content = mod_crowdfund_process_help_hint($oField->id);
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_WEAKNESS_STRENGTH");
$oDetail->addContent($oField);


$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}




function BusinessPlanDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
        }
    }
}  

function IdeaModify_on_done_action($component, $action) {

	
}
?>
