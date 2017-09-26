<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

use_cache(false);

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$ID_idea = 0;

if($_REQUEST["keys"]["ID_idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
			, " . FF_PREFIX . "currency.name AS currency_name
			, (SELECT MAX(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price)
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
			) AS max_pledge
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
		WHERE 1 
			AND " . $sSQL_Where;

$db->query($sSQL);
if($db->nextRecord()) 
{
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);     
	
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);	
	$goal = $db->getField("goal", "Number", true);
	$goal_step = $db->getField("goal_step", "Number", true);
	$currency_name = $db->getField("currency_name", "Text", true);
	$max_pledge = $db->getField("max_pledge", "Number", true);
	
}

if(!$permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) 
{
	if($db->getField("owner", "Number", true) != $UserNID) 
	{
		if(global_settings("MOD_CROWDFUND_ENABLE_TEAM")) 
		{
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
							OR FIND_IN_SET(" . $db->toSql(basename(ffCommon_dirname($cm->oPage->page_path))) . ", " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission))";
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$allow_edit = true;
				if(strlen($db->getField("role_permission", "Text", true))) 
				{
					$arrLimit = explode(",", $db->getField("role_permission", "Text", true));
				}
			} else 
			{
				$allow_edit = false;
			}
		} else 
		{
			$allow_edit = false;
		}
	} else 
	{
		$allow_edit = true;
	}
} else 
{
	$allow_edit = true;
}			
if($allow_edit) {
	$_REQUEST["keys"]["ID_idea"] = $ID_idea; 
}

if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
	$simple_interface = false;
} else {
	$simple_interface = true;
}

//menu
$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PledgeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_pledge_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge";
$oRecord->addEvent("on_done_action", "PledgeModify_on_done_action");
$oRecord->addEvent("on_do_action", "PledgeModify_on_do_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


//if(!$_REQUEST["keys"]["ID"] > 0) {
	if($_REQUEST["keys"]["ID_idea"] > 0) {
		$oRecord->insert_additional_fields["ID_idea"] =  new ffData($_REQUEST["keys"]["ID_idea"], "Number");
	} else {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_idea";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_modify_ID_idea");
		$oField->base_type = "Number";
		$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
		$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
									, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
                            			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
                            			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
                            				AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
		                            ) AS title
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql(get_session("UserNID"), "Number") . "
								";
		$oField->required = true;
		$oRecord->addContent($oField);
	}
//}

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->container_class = "price";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_modify_price");
$oField->base_type = "Number";
$oField->required = true;
$oField->widget = "slider";
$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
$oField->min_val = 1;
$oField->max_val = ($goal_step > 0 && $goal_step <= global_settings("MOD_CROWDFUND_MAX_VALUE_PLEDGE") ? $goal_step : global_settings("MOD_CROWDFUND_MAX_VALUE_PLEDGE"));
$oField->default_value = new ffData($max_pledge + 1, "Number");
$oField->step = 1; 


$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->container_class = "limit";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_modify_limit");
$oField->base_type = "Number";
$oRecord->addContent($oField);


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
$oDetail->id = "PledgeDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("crowdfund_pledge_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_pledge" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "PledgeDetail_on_do_action");
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
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.title AS title
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.description AS description
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID 
                                    	AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_detail_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->container_class = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_detail_title");
$oField->required = true;
$oDetail->addContent($oField);*/

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_detail_value");
$oField->display_label = false;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

          
function PledgeDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
			//$component->recordset[$rst_key]["smart_url"]->setValue(ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue()));
        }
    }
}	

function PledgeModify_on_done_action($component, $action) {
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
function PledgeModify_on_do_action($component, $action) 
{
	$db = ffDB_Sql::factory();
	switch($action)
	{
		case "insert":
		case "update":
				$sSQL = " SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = ". $db->tosql($_REQUEST["keys"]["ID_idea"], "Number");
				$db->query($sSQL);
				if($db->nextRecord()) 
				{
					$goal_step = $db->getField("goal_step", "Number", true);
					if ($component->form_fields["price"]->getValue() >= $goal_step && $goal_step > 0)
					{
						$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_price_request_toohigh"));
						return true;
					}
				}
				break;
		case "confirmdelete":
				break;

		default:
	}
}
?>