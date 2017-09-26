<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

use_cache(false);

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
	$simple_interface = false;
} else {
	$simple_interface = true;
}

if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])
{
    $cm->oPage->widgetLoad("dialog");
    $cm->oPage->widgets["dialog"]->process(
             "InsertIban"
             , array(
                    "tpl_id" => null
                    //"name" => "myTitle"
                    , "url" => ""
                    , "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_set_iban")
                    , "callback" => ""
                    , "class" => ""
                    , "params" => array(
                    )
                    , "resizable" => true
                    , "position" => "center"
                    , "draggable" => true
                    , "doredirects" => false
            )
            , $cm->oPage
    );	
}
//set Header
if(check_function("set_header_page"))
	set_header_page(ffTemplate::_get_word_by_code("crowdfund_ideas_title"));

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->ajax_search = true;
$oGrid->ajax_delete  = true;
$oGrid->id = "Idea";
$oGrid->title = ffTemplate::_get_word_by_code("crowdfund_ideas_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
                            , (SELECT CONCAT('<h2>', " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title, '</h2>'
                            				, '<p>', " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.teaser, '</p>')
                            ) AS name
                            , (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID) AS backers
                            	FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers 
                            	WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
									AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph <> 
																					(
																					SELECT anagraph.ID 
																					FROM anagraph
																					WHERE anagraph.uid = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
																					ORDER BY anagraph.ID 
																					LIMIT 1
																					)
									AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private = 0
                            ) AS backers
							
							, (SELECT 
								IF(ISNULL(anagraph.ID)
									,CONCAT('<h2>'
										, " . CM_TABLE_PREFIX . "mod_security_users.username
										, '</h2>'
										
									)
									,CONCAT('<h2>'
										, IF(anagraph.billreference = ''
											, IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
												, CONCAT(anagraph.name, ' ', anagraph.surname)
												, " . CM_TABLE_PREFIX . "mod_security_users.username
											)
											, anagraph.billreference
										)
										, '</h2>'
										, '<p>'
										, IF(anagraph.email = ''
											, " . CM_TABLE_PREFIX . "mod_security_users.email
											, anagraph.email
										)
										, '</p>'
									)
                            	) AS name
								FROM " . CM_TABLE_PREFIX . "mod_security_users
									LEFT JOIN anagraph ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
								WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner 
								GROUP BY " . CM_TABLE_PREFIX . "mod_security_users.ID
							) AS owner_data
                            , (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID) AS followers
                            	FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
                            	WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
                            ) AS followers
                            , '' AS progress_goal
                            , IF  (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire > 0
								, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated > 0
									, (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0))
									, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire
								)
								, (" . global_settings("MOD_CROWDFUND_EXPIRATION") . " - " . global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY") . " - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0)) 
							) AS expire
                            , '' AS status
                            , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status AS published
                        FROM 
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title <> ''
                        WHERE 1
                        	" . ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]
                        		? ""
                        		: " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql($UserNID, "Number") .
									" OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID IN (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea
																							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
																							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph = 
																															(SELECT anagraph.ID
																																FROM anagraph
																																WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . "
																																ORDER BY anagraph.ID
																																LIMIT 1)
																							)"
                        	). "
						[AND] [WHERE] 
						[HAVING]
                        [ORDER]";
//errore ideatore perchÃ¨ piÃ¹ utenti con uid = 4
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
if($simple_interface) {
	$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/basic/[smart_url_VALUE]?ret_url=" . urlencode(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?ret_url=" . urlencode(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	//$oGrid->bt_delete_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
}
$oGrid->record_id = "IdeaModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->user_vars["permission"] = $permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")];
if($permission[global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")])
{
	$oGrid->display_new = false;
} else
{
	$oGrid->display_new = true;
}
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "Idea_on_before_parse_row");
$oGrid->navigator_display_selector = false;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati

$oField = ffField::factory($cm->oPage);
$oField->id = "cover";
$oField->display_label = false;
$oField->container_class = "cover";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/idea/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/idea";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" .  global_settings("MOD_CROWDFUND_RESTRICTED_ICO") . "/[_FILENAME_]";
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->control_type = "picture_no_link";
//$oField->uploadify_model = "horizzontal";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_name");
$oField->encode_entities = false;
$oGrid->addContent($oField);

if (!$simple_interface)
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "owner_data";
    $oField->container_class = "owner_data";
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_owner_data");
    $oField->encode_entities = false;
    $oGrid->addContent($oField);
}

if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "backers";
    $oField->container_class = "backers";
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_backers");
    $oGrid->addContent($oField);
}
if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_FOLLOWER"))
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "followers";
    $oField->container_class = "followers";
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_followers");
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "progress_goal";
$oField->container_class = "progress-goal idea";
$oField->class = "input idea";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_progress_goal");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->container_class = "status";  
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_progress_status");
$oField->encode_entities = false;
$oGrid->addContent($oField);
/*
if(global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT")) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("crowdfund_idea_progress_active");
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
}
*/
if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")] && global_settings("MOD_CROWDFUND_IDEA_ACTIVE_PROJECT_BY_ADMIN")) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible_by_admin";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link"; 
	$oButton->label = ffTemplate::_get_word_by_code("crowdfund_idea_progress_active_by_admin");
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
}


$cm->oPage->addContent($oGrid);

function Idea_on_before_parse_row($component) {
    //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
    /* barra completamento ricompense*/
    $goal = $component->db[0]->getField("goal", "Number")->getValue("Currency", FF_LOCALE);
    $goal_current = $component->db[0]->getField("goal_current", "Number")->getValue("Currency", FF_LOCALE);
    if($component->db[0]->getField("goal", "Number", true) > 0) {
            $percentage_value = round($component->db[0]->getField("goal_current", "Number", true) * 100  / $component->db[0]->getField("goal", "Number", true), 0);
    } else {
            $percentage_value = 0;
    }
    $str_progress_goal = 
        '<div class="percentage-container"> 
            <div class="percentage-data">
                <label> ' . $goal_current . ' / ' . $goal . ' </label>
                <span class="percentage-value" style="display:none;">' . $percentage_value . '</span>
            </div>
        </div>';
						
    $component->grid_fields["progress_goal"]->setValue($str_progress_goal);	
	
    /*stato*/
    if($component->db[0]->getField("activated", "Number", true) > 0 
        && $component->db[0]->getField("status_by_admin", "Number", true)
        && $component->db[0]->getField("status_by_polihub", "Number", true)
        && strlen($component->db[0]->getField("iban", "Text", true))) 
    {
        $expire = $component->db[0]->getField("expire", "Number", true);
        if($expire < 0)
            $expire = 0;
        $status_description = $expire . '<span class="day-left">' . ffTemplate::_get_word_by_code("crowdfund_idea_day_left") . "</span>";
/*	if(global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT") && !$component->db[0]->getField("status_visible_decision", "Number", true)) {
            $status_description = '<span class="required">(' . ffTemplate::_get_word_by_code("crowdfund_idea_activation_by_user_required") . ')</span>';
	} elseif(global_settings("MOD_CROWDFUND_IDEA_ACTIVE_PROJECT_BY_ADMIN") && !$component->db[0]->getField("status_by_admin", "Number", true)) {
            $status_description = '<span class="required">(' . ffTemplate::_get_word_by_code("crowdfund_idea_activation_by_admin_required") . ')</span>';
	}
*/	$component->grid_fields["status"]->setValue($status_description);	
    } else 
    {
        if(mod_crowdfund_get_idea_required($component->key_fields["ID"]->getValue(), true))
        {
            $component->grid_fields["status"]->setValue(mod_crowdfund_get_idea_required($component->key_fields["ID"]->getValue()));	
        } else{
            $is_admin = $component->user_vars["permission"];
            if($component->db[0]->getField("status_visible_decision", "Number", true)) {
                $user_approves = true;
                $user_approves_values = 0;
            } else
            {
                $user_approves_values = 1;
            }
            if($component->db[0]->getField("status_by_admin", "Number", true)) {
                $admin_approves = true;
                $admin_approves_values = 0;
            } else
            {
                $admin_approves_values = 1;
            }
            if($component->db[0]->getField("status_by_polihub", "Number", true)) {
                $polihub_approves = true;
                $polihub_approves_values = 0;
            } else
            {
                $polihub_approves_values = 1;
            }
            if(strlen($component->db[0]->getField("iban", "Text", true))) {
                $iban_insert = true;
            }
            $component->grid_fields["status"]->setValue(
                '</a><div class="status div-table">
                    <div class="status-step">' . ffTemplate::_get_word_by_code("crowdfund_idea_step") . '</div>
                    <div class="div-table-row">
                        <span class="left cell">' . ffTemplate::_get_word_by_code("crowdfund_idea_user_approves") . '</span><span class="cell right"><a class=' . ($user_approves ? "done" : "missing") . ' href="' . ($is_admin ? $component->page_path . "/modify/" . $component->key_fields["smart_url"]->getValue() . "?setactivated=" . $user_approves_values . "&ret_url=" . urlencode($component->parent[0]->getRequestUri()) : "javascript:void(0)" ) . '">prova</a></span>
                    </div>
                    <div class="div-table-row">
                       <span class="left cell">' . ffTemplate::_get_word_by_code("crowdfund_idea_admin_approves") . '</span><span class="cell right"><a class=' . ($admin_approves ? "done" : "missing") . ' href="' . ($is_admin ? $component->page_path . "/modify/" . $component->key_fields["smart_url"]->getValue() . "?setstatusbyadmin=" . $admin_approves_values . "&ret_url=" . urlencode($component->parent[0]->getRequestUri()) : "javascript:void(0)" ) . '">prova</a></span>
                    </div>
                    <div class="div-table-row">
                       <span class="left cell">' . ffTemplate::_get_word_by_code("crowdfund_idea_polihub_approves") . '</span><span class="cell right"><a class=' . ($polihub_approves ? "done" : "missing") . ' href="' . ($is_admin ? $component->page_path . "/modify/" . $component->key_fields["smart_url"]->getValue() . "?setstatusbypolihub=" . $polihub_approves_values . "&ret_url=" . urlencode($component->parent[0]->getRequestUri()) : "javascript:void(0)" ) . '">prova</a></span>
                    </div>
                    <div class="div-table-row">
                       <span class="left cell">' . ffTemplate::_get_word_by_code("crowdfund_idea_iban_insert") . '</span><span class="cell right"><a class=' . ($iban_insert ? "done" : "missing") . ' href=javascript:void(0); ' . ($is_admin ? 'onclick="ff.ffPage.dialog.doOpen(\'InsertIban\',\'' . $component->page_path . "/modify/" . $component->key_fields["smart_url"]->getValue() . "?setiban&ret_url=" . urlencode($component->parent[0]->getRequestUri()) . '\');" ' : '') . '>prova</a></span>
                    </div>
                </div>'
            );
        }
    }
    /*
    $show_visible_by_admin = false;
    if(isset($component->grid_buttons["visible"])) 
    {
        if($component->db[0]->getField("published", "Number", true) > 0) 
        {
            if($component->db[0]->getField("status_visible_decision", "Number", true)) 
            {
                $show_visible_by_admin = true;
			    
                $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
                $component->grid_buttons["visible"]->icon = null;
                $component->grid_buttons["visible"]->action_type = "submit"; 
                $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setactivated=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                if($_REQUEST["XHR_DIALOG_ID"]) 
                {
                    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
                } else {
                    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
                }   
            } else 
            {
                $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
                $component->grid_buttons["visible"]->icon = null;
                $component->grid_buttons["visible"]->action_type = "submit";  
                $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setactivated=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                if($_REQUEST["XHR_DIALOG_ID"]) 
                {
                    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
                } else 
                {
                    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
                }    
            }
            $component->grid_buttons["visible"]->visible = true;
        } else 
        {
            $component->grid_buttons["visible"]->visible = false;
        }
    }
    */
    if(isset($component->grid_buttons["visible_by_admin"])) 
    {
        if($component->db[0]->getField("activated", "Number", true) > 0) 
        {
            if($component->db[0]->getField("status_by_admin", "Number", true)) 
            {
                $component->grid_buttons["visible_by_admin"]->class = cm_getClassByFrameworkCss("eye", "icon");
                $component->grid_buttons["visible_by_admin"]->icon = null;
                $component->grid_buttons["visible_by_admin"]->action_type = "submit"; 
                $component->grid_buttons["visible_by_admin"]->form_action_url = $component->grid_buttons["visible_by_admin"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible_by_admin"]->parent[0]->addit_record_param . "setstatusbyadmin=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                if($_REQUEST["XHR_DIALOG_ID"]) 
                {
                    $component->grid_buttons["visible_by_admin"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
                } else 
                {
                    $component->grid_buttons["visible_by_admin"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
		}   
            } else 
            {
                $component->grid_buttons["visible_by_admin"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
                $component->grid_buttons["visible_by_admin"]->icon = null;
                $component->grid_buttons["visible_by_admin"]->action_type = "submit";     
                $component->grid_buttons["visible_by_admin"]->form_action_url = $component->grid_buttons["visible_by_admin"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible_by_admin"]->parent[0]->addit_record_param . "setstatusbyadmin=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                if($_REQUEST["XHR_DIALOG_ID"]) {
                    $component->grid_buttons["visible_by_admin"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
                } else {
                    $component->grid_buttons["visible_by_admin"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
                }    
            }
            $component->grid_buttons["visible_by_admin"]->display = true;
        } else 
        {
            $component->grid_buttons["visible_by_admin"]->display = false;
        }
    }
}

?>