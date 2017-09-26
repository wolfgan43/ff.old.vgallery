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

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.*
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
				INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
				AND " . $sSQL_Where;
$db->query($sSQL);
if($db->nextRecord()) 
{
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true); 
	$languages = $db->getField("languages", "Text", true);	    
	$activated = $db->getField("activated", "Number", true);
	$smart_url = $db->getField("smart_url", "Text", true);
	$owner = $db->getField("owner", "Text", true);
	$expiration = $db->getField("expire", "Number", true);
	if($expiration)
	{
		$end_idea = $activated + $expiration * 86400;
	} else
	{
		$end_idea = $activated + (global_settings("MOD_CROWDFUND_EXPIRATION") - global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY")) * 86400;
	}
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
$oRecord->id = "TimelineModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_timeline_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline";
$oRecord->addEvent("on_do_action", "TimelineModify_on_do_action");
$oRecord->addEvent("on_done_action", "TimelineModify_on_done_action");
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["owner"] = $owner;
$oRecord->user_vars["end_idea"] = $end_idea;


$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

if(!$cm->oPage->isXHR()) {
	//$oRecord->fixed_post_content = $tpl_menu;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($_REQUEST["keys"]["ID_idea"] > 0) 
{
		$oRecord->insert_additional_fields["ID_idea"] =  new ffData($_REQUEST["keys"]["ID_idea"], "Number");
} else 
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_idea";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_modify_ID_idea");
	$oField->base_type = "Number";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = 
												(SELECT " . FF_PREFIX . "languages.ID 
													FROM " . FF_PREFIX . "languages 
													WHERE " . FF_PREFIX . "languages.code = " . $db->toSql(FF_LOCALE) . ")
												) AS title
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql(get_session("UserNID"), "Number");
	$oField->required = true;
	$oRecord->addContent($oField);
}
 
$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_modify_date");
$oField->widget = "datepicker";
$oField->base_type = "Timestamp";
$oField->extended_type = "Date"; 
$oField->app_type = "Date";
$oField->default_value = new ffData($end_idea + 86400, "Timestamp");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_modify_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/timeline/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/timeline";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true; 
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oField->required = false;
$oRecord->addContent($oField);

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
$oDetail->id = "TimelineDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("crowdfund_pledge_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_timeline" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "TimelineDetail_on_do_action");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language 
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'
									AND FIND_IN_SET(" . FF_PREFIX . "languages.ID, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
	                                    												FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
	                                    												WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number") . "
	                                    											)
			                                )

                                ";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.title AS title
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.description AS description
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID 
                                    	AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
									AND FIND_IN_SET(" . FF_PREFIX . "languages.ID, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
	                                    												FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
	                                    												WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number") . "
	                                    											)
			                                )
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_detail_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->container_class = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_detail_title");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description"; 
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_detail_description");
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oField->required = false;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);

$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

function TimelineDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
			//$component->recordset[$rst_key]["smart_url"]->setValue(ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue()));
        }
    }
}	

          
function TimelineModify_on_done_action($component, $action) 
{
    $db = ffDB_Sql::factory();
	switch($action)
	{
		case "insert":
		case "update":
			$date = $component->form_fields["date"]->getValue();
			$ID_idea = $component->user_vars["ID_idea"];

			$time = time();

			list($dd, $mm, $yy) = explode("/", $date);
			$timestamp = mktime(0, 0, 0, $mm, $dd, $yy);

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.*
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.date = " . $db->toSql($timestamp, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$timestamp++;
			}


			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline SET
						date = " . $db->toSql($timestamp, "Number") . "
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.created = " . $db->toSql($time, "Number"); 
			$db->execute($sSQL); 
			break;
		case "confirmdelete":
/*			$sSQL = "DELETE 				
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_language
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_timeline = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
*/			break;
	}
	
}

function TimelineModify_on_do_action($component, $action) { 
    $db = ffDB_Sql::factory();
	
	$end_idea = $component->user_vars["end_idea"];
	$date = $component->form_fields["date"]->getValue();
	
	if(strlen($date))
	{
		list($dd, $mm, $yy) = explode("/", $date);
		$timestamp = mktime(0, 0, 0, $mm, $dd, $yy);
		
		$end_idea_date = date('d/m/Y', $end_idea + 86400);

		if($timestamp < $end_idea)
		{
			$component->tplDisplayerror((ffTemplate::_get_word_by_code("crowdfund_idea_date_toosoon_pre") . $end_idea_date));
			return true;
		}
	}
}
?>