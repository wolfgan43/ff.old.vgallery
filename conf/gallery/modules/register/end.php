<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if(check_function("analytics"))
    analytics_set_event('/registrazione/end', 'Step 2 - complete ' . $cm->path_info);


//use_cache(false); 

if(isset($_REQUEST["XHR_COMPONENT"]) && strlen($_REQUEST["XHR_COMPONENT"])) {
	$record_id = $_REQUEST["XHR_COMPONENT"];
} else {
	$record_id = "notify-register";
}

$oRecord = ffRecord::factory($cm->oPage);
if(!strlen($_REQUEST["ret_url"])) 
    $_REQUEST["ret_url"] = stripslash(FF_SITE_PATH) . "/";
 /*   
$mc = urldecode($_REQUEST["mc"]);
$ma = urldecode($_REQUEST["ma"]);

$db_selection = ffDB_Sql::factory();

$UserNID = get_session("temp_UserNID");
if(is_numeric($UserNID) && $UserNID > 0) {
    $register_type = "user";
} else {
    $UserNID = get_session("temp_VGalleryNID");
    if(is_numeric($UserNID) && $UserNID > 0) {
            $register_type = "vgallery";
    } else {
            ffRedirect(FF_SITE_PATH . "/");
    }
}*/


$oRecord->id = $record_id;
$oRecord->class = $record_id;
if($register_type == "user") {
    $oRecord->src_table = CM_TABLE_PREFIX . "mod_security_users"; 
    } elseif($register_type == "vgallery") {
            $oRecord->src_table = "vgallery_nodes"; 
    }

$oRecord->title =  ffTemplate::_get_word_by_code("notify_register_end_title"); 
$oRecord->skip_action = true;
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;
//$oRecord->buttons_options["print"]["display"] = false;
$oRecord->display_required_note = false;
$oRecord->allow_insert = false;
$oRecord->allow_update = false;
$oRecord->allow_delete = false;
$oRecord->framework_css["grid"]["col"] = array(12);
$oRecord->framework_css["actions"]["col"] = array(12); 
$oRecord->disable_mod_notifier_on_error = true;

$obj_page_field = ffField::factory($cm->oPage);
$obj_page_field->id = "register-ID";
$obj_page_field->base_type = "Number";
$obj_page_field->data_source = "ID";
$oRecord->addKeyField($obj_page_field);

$mail_account = null;

if($ma) {
    $mail_attivation = ffTemplate::_get_word_by_code("attivation_mail_failed") . " " . $ma; 
} elseif(strlen($ma)) {
    $mail_attivation = ffTemplate::_get_word_by_code("attivation_mail_success");
} else {
        if($mc) {
            $mail_account = ffTemplate::_get_word_by_code("registration_mail_failed") . " " . $mc;
        } else {
            $mail_account = ffTemplate::_get_word_by_code("registration_mail_success");
        }
    $mail_attivation = NULL;
}

$oRecord->addContent(null, true, "send_mail");  
$oRecord->groups["send_mail"] = array(
                                         "title" => ffTemplate::_get_word_by_code("register_email_report")
                                         , "cols" => 1
                                      );

if($mail_account!== NULL) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "mail_account";
        $oField->base_type = "Text";
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->encode_entities = false;
        $oField->default_value = new ffData($mail_account);
        $oRecord->addContent($oField, "send_mail");
    }

if($mail_attivation !== NULL) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "mail_attivation";
    $oField->base_type = "Text";
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->encode_entities = false;
    $oField->default_value = new ffData($mail_attivation);
    $oRecord->addContent($oField, "send_mail");
}

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "login";
$oButton->action_type = "gotourl";
$oButton->url = FF_SITE_PATH . "/login";
$oButton->label = ffTemplate::_get_word_by_code("goto_login");
$oButton->aspect = "link";
$oRecord->addActionButton($oButton);

$cm->oPage->addContent($oRecord);

/*
if(check_function("process_html_page_error")) {
	$params = array();
	if(basename($cm->real_path_info))
		$params["template"] = "registration_" . basename($cm->real_path_info) . "_end.html";
	
	$cm->oPage->addContent(process_html_notify("success", ffTemplate::_get_word_by_code("registration_title"), ffTemplate::_get_word_by_code("registration_description"), $params));
}
*/