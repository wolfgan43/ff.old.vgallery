<?php
$permission = check_coudocs_permission();
if($permission !== true && !(is_array($permission) && $permission["owner"])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "CategoryModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("category_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_cloudocs_category";
$oRecord->addEvent("on_done_action", "CategoryModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("category_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  

$oRecord->insert_additional_fields = array("ID_owner" =>  new ffData($UserNID, "Number")
									);

$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function CategoryModify_on_done_action($component, $action) {
    
   
}
?>
