<?php
$permission = check_coudocs_permission();
if($permission !== true && !(is_array($permission) && $permission["owner"])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setstatus"]) && $_REQUEST["keys"]["ID"] > 0) {
	
	
	$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_cloudocs_docs 
			SET " . CM_TABLE_PREFIX . "mod_cloudocs_docs.status = " . $db->toSql($_REQUEST["setstatus"], "Number") . "
			WHERE " . CM_TABLE_PREFIX . "mod_cloudocs_docs.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$db->execute($sSQL);
	
	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("DocsModify")), true));
	} else {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("DocsModify")), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	}
}    


$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "DocsModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("docs_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_cloudocs_docs";
$oRecord->addEvent("on_done_action", "DocsModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("docs_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  

if(check_function("get_user_data"))
	$Fname_sql = get_user_data("Fname", "anagraph", null, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "customers";
$oField->label = ffTemplate::_get_word_by_code("docs_modify_customer");
//$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT 
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
                        INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
        				LEFT JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories) 
				    WHERE 1 " . 
                    (MOD_CLOUDOCS_LIMIT_CUSTOMER_BY_OWNER
                        ? " AND anagraph.owner = " . $db->toSql($UserNID, "Number")
                        : " " 
                    ) . 
                    " [AND] [WHERE] 
				    GROUP BY anagraph.ID
                    [HAVING] 
				    [ORDER] [COLON] Fname
				    [LIMIT]";
//$oField->autocompletetoken_compare_having = "Fname";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_delimiter = ",";
$oField->autocompletetoken_combo = true;
$oField->widget = "autocompletetoken";  
//$oField->required = true;	
//$oField->actex_update_from_db = true;


	//$oField->actex_dialog_url = $cm->oPage->site_path . "/manage/anagraph/all/modify";
	//$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
	//$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=AnagraphModify_confirmdelete";
	//$oField->resources[] = "AnagraphModify";

$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("docs_modify_category");
$oField->source_SQL = "SELECT ID, name 
                        FROM " . CM_TABLE_PREFIX . "mod_cloudocs_category 
                        WHERE " . CM_TABLE_PREFIX . "mod_cloudocs_category.ID_owner = " . $db->toSql($UserNID, "Number") . " 
                        [AND] [WHERE] 
                        ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path) . "/category/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=CategoryModify_confirmdelete";
$oField->resources[] = "CategoryModify"; 
//$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "file_path";
$oField->label = ffTemplate::_get_word_by_code("docs_modify_cover_path");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/cloudocs/" . $UserNID . "/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/cloudocs/" . $UserNID;
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
$oField->required = true;
//$oField->file_allowed_mime = array("jpg", "gif", "png");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("docs_modify_description");
$oField->base_type = "Text";
$oField->extended_type = "Text";	
/*if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}*/
$oRecord->addContent($oField);  

$oRecord->insert_additional_fields = array("ID_owner" =>  new ffData($UserNID, "Number")
										, "status" => new ffData("1", "Number")
									);

$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function DocsModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    switch($action) {
        case "insert":
        case "update":
            $sSQL = "SELECT 
                        " . (check_function("get_user_data")
                            ? get_user_data("reference", "anagraph", null, false)
                            : "''"
                        ) . " AS name
                        , IF(anagraph.email = ''
                            , " . CM_TABLE_PREFIX . "mod_security_users.email
                            , anagraph.email
                        ) AS email 
                    FROM anagraph 
                        INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                    WHERE anagraph.ID = " . $db->toSql($component->form_fields["customers"]->getValue());
            $db->query($sSQL);
            if($db->nextRecord()) {
                $username = $db->getField("name", "Text", true);
                $email = $db->getField("email", "Text", true);
                
                $to[0]["name"] = $username;
                $to[0]["mail"] = $email;

                $fields["docs"]["name"] = $component->form_fields["name"]->getValue(); 
                $fields["docs"]["category"] = $component->form_fields["name"]->getDisplayValue();
                $fields["docs"]["link"] = "http://" . DOMAIN_INSET . CM_SHOWFILES . $component->form_fields["file_path"]->getValue();
                
                if(check_function("process_mail")) {
                    $rc = process_mail(email_system("cloudocs", MOD_CLOUDOCS_THEME), $to, ffTemplate::_get_word_by_code($action . "_doc") . " " . $component->form_fields["name"]->getValue(), NULL, $fields);
                }        
            }    
            break;
        default:
    }
   
}
?>