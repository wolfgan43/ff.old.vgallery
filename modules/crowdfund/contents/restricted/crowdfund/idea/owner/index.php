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
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current AS goal_current
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal AS goal
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step AS goal_step
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.is_startup AS is_startup
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.equity AS equity
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.capital_funded AS capital_funded
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
	        , " . FF_PREFIX . "currency.name AS currency_name
			, " . FF_PREFIX . "currency.symbol AS currency_symbol
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.created	
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire	
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
		WHERE 1 
			AND " . $sSQL_Where;
$db->query($sSQL);
if($db->nextRecord()) {
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);
	$smart_url = $db->getField("smart_url", "Text", true);
	$idea_name = $db->getField("name", "Text", true);
	$goal = $db->getField("goal", "Number", true);
	$owner = $db->getField("owner", "Number", true);
	$min_offer = ceil ($goal/100);
	$max_offer = floor ($goal/10);
	$currency_symbol = $db->getField("currency_symbol", "Text", true);
	
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

$arrOffer = mod_crowdfund_get_backers($ID_idea, true);
if(is_array($arrOffer) && count($arrOffer)) {
	$arrOffer = current($arrOffer);
	$offer = $arrOffer["backer_price"];
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

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id; 
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_first_offer") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
$oRecord->addEvent("on_done_action", "FirstOfferModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->user_vars["owner"] = $owner;
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["goal"] = $goal;
//$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->fixed_pre_content = $tpl_detail_empty . "<br />" . ffTemplate::_get_word_by_code("crowdfund_idea_modify_owner_group_description");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "first_backer";
$oField->container_class = "first-backer"; 
$oField->label = ffTemplate::_get_word_by_code("crowdfund_first_offers_modify_price");
$oField->base_type = "Number";
$oField->required = true;
$oField->widget = "slider";
$oField->min_val = ($goal > 0 ? $min_offer : 1);
$oField->max_val = ($goal > 0 ? $max_offer : global_settings("MOD_CROWDFUND_MAX_GOAL"));
$oField->default_value = new ffData(($offer > 0 ? $offer :$min_offer), "Number");
$oField->step = 1;
$oField->data_type = "";
$oField->store_in_db = false;
$oField->fixed_post_content = '<span class="label">,00 ' . $currency_symbol . '</span>';
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);  

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

		
function FirstOfferModify_on_done_action($component, $action) 
{
	$db = ffDB_Sql::factory();
	$owner = $component->user_vars["owner"];
	$ID_idea = $component->user_vars["ID_idea"]; 
	$offer = $component->form_fields["first_backer"]->getValue();
	
	$time = time();
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->tosql($ID_idea, "Number");
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		$goal = $db->getField("goal", "Number", true);
		$goal_diff = $db->getField("goal_diff", "Number", true);
		$min_offer = ceil ($goal/100);
		$max_offer = floor ($goal/10);
		
		if ($offer < $min_offer && $goal > 0) 
		{
			$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_first_offer_toolow"));
			return true;
		}
		if ($offer > $max_offer && $goal > 0)
		{
			$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_first_offer_toohigh"));
			return true;
		}
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.*
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph = (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($owner, "Number") . " LIMIT 1)
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->tosql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0
						ORDER BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$old_offer = $db->getField("price", "Number", true);
			$total_now = $goal_diff + $old_offer - $offer;
			if ($total_now < 0)
			{
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_first_offer_toohigh"));
				return true;
			}
			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers SET 
							" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.price = " . $db->toSql($offer, "Number")."
							, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.last_update = " . $db->toSql($time, "Number")."
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph = (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($owner, "Number") . " LIMIT 1)
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->tosql($ID_idea, "Number") . " 
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0";
				$db->execute($sSQL);
		} else  
		{ 
			$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						(
							ID
							, ID_idea
							, ID_user_anagraph
							, price
							, created
							, last_update
							, is_private
						)
						VALUES
						(
							null 
							, " . $db->toSql($ID_idea, "Number") . "
							, (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($owner, "Number") . " LIMIT 1)
							, " . $db->toSql($offer, "Number") . "	
							, " . $db->toSql($time, "Number") . "
							, " . $db->toSql($time, "Number") . "
							, " . $db->toSql("1", "Number") . "
						)";
		$db->execute($sSQL);
		}
	}
}
?>