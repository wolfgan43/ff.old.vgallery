<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

/*$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));

if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffGrid.html")) {
	$oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
}*/
$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oGrid = ffGrid::factory($cm->oPage);

$oGrid->full_ajax = true;
$oGrid->id = "type";
$oGrid->title = ffTemplate::_get_word_by_code("type_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_attendance_type.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_attendance_type
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "TypeModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("type_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "approval";
$oField->label = ffTemplate::_get_word_by_code("type_approval");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sheet";
$oField->label = ffTemplate::_get_word_by_code("type_enable_sheet");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sheet_grid";
$oField->label = ffTemplate::_get_word_by_code("type_enable_sheet_grid");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_tool";
$oField->label = ffTemplate::_get_word_by_code("type_enable_tool");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "default";
$oField->label = ffTemplate::_get_word_by_code("type_default");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_request";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_request");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_employee";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_employee");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_customer";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_customer");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_office";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_office");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oGrid->addContent($oField); 


$cm->oPage->addContent($oGrid);


?>