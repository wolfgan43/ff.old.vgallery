<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db_selection = ffDB_Sql::factory();

$db_gallery->query("SELECT module_form.*
                        FROM 
                            module_form
                        WHERE 
                            module_form.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info))) . "
                            ");
if($db_gallery->nextRecord()) {
    $ID_form = $db_gallery->getField("ID")->getValue();
    $form_name = $db_gallery->getField("name")->getValue();
    $send_mail = $db_gallery->getField("send_mail")->getValue();
    
    $force_redirect = $db_gallery->getField("force_redirect")->getValue();
    $privacy = $db_gallery->getField("privacy")->getValue();
    $require_note = $db_gallery->getField("require_note")->getValue();
    $tpl_form_path = $db_gallery->getField("tpl_form_path")->getValue();

    $ID_email = $db_gallery->getField("ID_email", "Number")->getValue();
    
    $db_gallery->query("SELECT module_form_fields.*
                                , extended_type.name AS extended_type
                                , check_control.ff_name AS check_control
                                , module_form_fields_group.name AS `group_field`
                            FROM 
                                module_form_fields
                                LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                                LEFT JOIN check_control ON check_control.ID = module_form_fields.ID_check_control
                                LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                            WHERE 
                                module_form_fields.ID_module = " . $db_gallery->toSql(new ffData($ID_form, "Number")) . "
                            ORDER BY module_form_fields.`order`, module_form_fields.name
                            ");
    if($db_gallery->nextRecord()) {
        do {
            
            if($db_gallery->getField("enable_in_mail")->getValue()) {
                $fields[$db_gallery->getField("group_field")->getValue()][$db_gallery->getField("name")->getValue()] = $db_gallery->getField("name")->getValue() . "_example";
            }
            if($db_gallery->getField("send_mail")->getValue()) {
                $to[] = $db_gallery->getField("name")->getValue() . "@example.ex";
            }
        } while ($db_gallery->nextRecord());
    }
	
	$tpl_email_path = null;
	if($send_mail && $ID_email > 0) {
	    $sSQL = "SELECT * FROM email
	                WHERE email.ID = " . $db_gallery->toSql(new ffData($ID_email, "Number", FF_SYSTEM_LOCALE));
	    $db_gallery->query($sSQL);
	    if ($db_gallery->nextRecord()) {
	        $tpl_email_path = $db_gallery->getField("tpl_email_path")->getValue();
	    }

	    if(!$tpl_email_path || !file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_email_path))
	        $tpl_email_path = null;
	}

	if(!strlen(EMAIL_DEBUG)) {
		$cm->oPage->addContent(ffTemplate::_get_word_by_code("email_debug_empty"), null, "EmailDebugEmpty");
	}
    if($_REQUEST["frmAction"] == "send" && strlen(EMAIL_DEBUG)) {
        //Caricamento del template di base html
        $to[] = EMAIL_DEBUG;  
        $from[] = "noreply@" . DOMAIN_NAME;
        if(check_function("process_mail"))
        	$res = process_mail($ID_email, $to, NULL, $tpl_email_path, $fields, $from, false, false);
        if($rc)
            $cm->oPage->addContent($form_name . " " . $res, null, "sendMail");
        else
            ffRedirect($_REQUEST["ret_url"]);
	} else {  
	    if($send_mail && $ID_email > 0) {
	        //Caricamento del template di base html
			if(check_function("process_mail")) {
				$cm->oPage->addContent(process_mail($ID_email, $to, ffTemplate::_get_word_by_code("form_" . $form_name), $tpl_email_path, $fields, NULL, NULL, NULL, true, false, true));
		        $cm->oPage->addContent(process_mail($ID_email, $to, ffTemplate::_get_word_by_code("form_" . $form_name), $tpl_email_path, $fields, NULL, NULL, NULL, true, false));
			}
	        if(strlen(EMAIL_DEBUG)) {
		        $oButton_send = ffButton::factory($cm->oPage);
		        $oButton_send->id = "send";
		        $oButton_send->action_type = "gotourl";
		        $oButton_send->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_email" . $cm->real_path_info . "?frmAction=send&ret_url=" . urlencode($cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "?ret_url=" . urlencode($_REQUEST["ret_url"]));
		        $oButton_send->aspect = "link";
                        $oButton_send->label = ffTemplate::_get_word_by_code("email_test_send");
		        $oButton_send->parent_page = array(&$cm->oPage);
			}
		}
	}
} 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = urldecode($_REQUEST["ret_url"]);
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);

//$cm->oPage->process_params();

 $cm->oPage->addContent("<div class=\"prev_button\" >" . (isset($oButton_send) ? $oButton_send->process() : "") . (isset($oButton_customize) ? $oButton_customize->process() : "") . $oButton->process() . "</div>");

?>