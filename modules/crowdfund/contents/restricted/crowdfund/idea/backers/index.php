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
																									LIMIT 1)							AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission = ''
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


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_backers") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;


$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Backers";
//$oGrid->title = ffTemplate::_get_word_by_code("backers_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.*
                            , anagraph.avatar
                            , anagraph.email
                            , " . (check_function("get_user_data")
                                ? get_user_data("reference", "anagraph", null, false)
                                : "''"
                            ) . " AS anagraph
					        , '' AS status
                        FROM
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number"). "
                        	AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private = 0
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "last_update";
$oGrid->use_search = !$simple_interface;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/" . $smart_url;
$oGrid->record_id = "BackersModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;

$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "backers_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);



// Campi visualizzati
if(ENABLE_AVATAR_SYSTEM) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "avatar";
	$oField->display_label = false;
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_avatar");
    $oField->encode_entities = false;
    $oGrid->addContent($oField, false);
}



$oField = ffField::factory($cm->oPage);
$oField->id = "anagraph";
$oField->container_class = "anagraph";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_anagraph");
$oGrid->addContent($oField);

// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->container_class = "last_update";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_last_update");
$oField->base_type = "Timestamp";
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->container_class = "type";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_type");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->container_class = "price";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_price");
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->fixed_post_content = $currency_symbol;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->container_class = "status";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_status");
$oGrid->addContent($oField);


$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

$cm->oPage->addContent($oRecord);  

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

function backers_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
    if(isset($component->grid_fields["avatar"])) { 
    	if(check_function("get_user_avatar")) 
    		$component->grid_fields["avatar"]->setValue(get_user_avatar($component->db[0]->getField("avatar", "Text", true), true, $component->db[0]->getField("email", "Text", true)));
	}
	
	$component->addit_record_param = "";
	if($component->db[0]->getField("ID_bill", "Number", true) > 0) {
		$sSQL = "SELECT ecommerce_order.ID AS ID_order
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					INNER JOIN ecommerce_documents_bill ON ecommerce_documents_bill.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_bill
					INNER JOIN ecommerce_order ON ecommerce_order.ID_bill = ecommerce_documents_bill.ID
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($component->key_fields["ID"]->value);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$ID_order = $db->getField("ID_order", "Number", true);
			$component->addit_record_param = "retry=" . $ID_order ."&frmAction=" . $component->record_id . "_retry&"; 
		}
	}
	if(isset($component->grid_fields["type"])
		&& strlen($component->grid_fields["type"]->getValue())
	) {
		$component->grid_fields["type"]->setValue(ffTemplate::_get_word_by_code("crowdfund_backer_type_" . $component->grid_fields["type"]->getValue()));
	}
	if(isset($component->grid_fields["status"])) {
		if($component->db[0]->getField("confirmed_price", "Number", true) >= $component->db[0]->getField("price", "Number", true)) {
			$component->grid_fields["status"]->setValue(ffTemplate::_get_word_by_code("crowdfund_backers_payment_confirmed"));
			
			$component->display_delete_bt = false;
			$component->display_edit_url = false;
		} else {
			if($component->db[0]->getField("ID_bill", "Number", true) > 0) {
				$component->grid_fields["status"]->setValue(ffTemplate::_get_word_by_code("crowdfund_backers_payment_waiting"));
			} else {
				$component->grid_fields["status"]->setValue(ffTemplate::_get_word_by_code("crowdfund_backers_payment_init"));
			}
			$component->display_delete_bt = true;
			$component->display_edit_url = global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT");
		}
	}
}
?>
