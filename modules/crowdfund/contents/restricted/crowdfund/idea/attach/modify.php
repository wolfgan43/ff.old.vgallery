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
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title	
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID  
		WHERE 1 
			AND " . $sSQL_Where;

$db->query($sSQL);
if($db->nextRecord()) 
{
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);     
	
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);
	$title = $db->getField("title", "Text", true);
	$owner = $db->getField("owner", "Number", true); 
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
$oRecord->id = "AttachModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_attach_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_attach";
$oRecord->addEvent("on_done_action", "AttachModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["UserNID"] = $UserNID;
$oRecord->user_vars["title"] = $title;
$oRecord->user_vars["owner"] = $owner;


$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

if(!$cm->oPage->isXHR()) {
	//$oRecord->fixed_post_content = $tpl_menu;
}

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
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_attach_modify_ID_idea");
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
$oField->id = "title";
$oField->container_class = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_attach_modify_title");
$oField->required = true;
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_attach_modify_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/attach/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/attach";
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
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);
  
if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

          
function AttachModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) 
	{
		$idea_title = $component->user_vars["title"];
		$attach_title = $component->form_fields["title"]->getValue();
		$ID_idea = $component->user_vars["ID_idea"];
		$owner = $component->user_vars["owner"];
		$file = $component->form_fields["file"]->getValue("Text");
		$time = time();
		
		switch($action) 
		{
			case "insert":
				$follow = array();
				$sSQL = "SELECT anagraph.* 
							, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
								INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . $db->toSql($component->user_vars["ID_idea"], "Number");
				$db->query($sSQL); 
				if($db->nextRecord())  
				{
					$ID_follow = $db->getField("ID_user_anagraph", "Number", true);
					$follow[$ID_follow]["name"] = $db->getField("name", "Text", true);
					$follow[$ID_follow]["surname"] = $db->getField("surname", "Text", true);
					$follow[$ID_follow]["email"] = $db->getField("email", "Text", true);
				}
				$i=0;
				foreach ($follow as $follow_key => $follow_value) 
				{
					
					$to[$i]["name"] = $follow_value["name"] . " " . $follow_value["surname"];
					$to[$i]["mail"] = $follow_value["email"];
					$i++;
				}
				$fields["idea"]["title"] = $idea_title;
				$fields["attach"]["name"] = $attach_title; 
				$fields["attach"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . CM_SHOWFILES . $file;

				if(check_function("process_mail")) 
				{ 
					$rc = process_mail(email_system("attach"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
				}
				
				
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline 
						(
							ID
							, ID_idea
							, created
							, file
							, last_update
							, title
						)
						VALUES
						(
							null 
							, " . $db->toSql($ID_idea, "Number") . "
							, " . $db->toSql($time, "Number") . "
							, " . $db->toSql($file, "Text") . "
							, " . $db->toSql($time, "Number") . "
							, " . $db->toSql($attach_title, "Text") . "
						)";
				//$db->execute($sSQL);
				
			break;
			
			case "confirmdelete":
				$sSQL = "DELETE 		
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach 
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach.ID = " . $db->toSql($component->key_fields["ID"]->value);
				$db->execute($sSQL);

				$sSQL = "DELETE 		
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline 
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($ID_idea, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.title = " . $db->toSql($attach_title, "Text");
				//$db->execute($sSQL);
				break;
		
			default:
				break;
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