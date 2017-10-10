<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_EMAIL_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------
if($_REQUEST["keys"]["email-ID"] > 0) {
	$sSQL = "SELECT email.* FROM email WHERE email.ID = " . $db_gallery->toSql($_REQUEST["keys"]["email-ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$email_name = $db_gallery->getField("name", "Text", true);
	}
}
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "EmailModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("email");
$oRecord->src_table = "email";
$oRecord->addEvent("on_do_action", "EmailModify_on_do_action");


$oField = ffField::factory($cm->oPage);
$oField->id = "email-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("email_edit_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "subject";
$oField->label = ffTemplate::_get_word_by_code("email_edit_subject");
$oRecord->addContent($oField);

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "theme";
$oField->label = ffTemplate::_get_word_by_code("email_edit_theme");
$oField->widget = "activecomboex";
//$oField->actex_update_from_db = true;
$oField->actex_child = "tpl_email_path";
$oRecord->addContent($oField);*/

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_email_path";
$oField->label = ffTemplate::_get_word_by_code("email_edit_tpl_email_path");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
//$oField->actex_related_field = "type";
//$oField->actex_father = "theme";
//$oField->actex_dialog_show_add = false; 
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/template/modify?keys[email-ID]=" . $_REQUEST["keys"]["email-ID"] . "&name=" . urlencode($email_name);
$oField->actex_dialog_edit_params = array("keys[path]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "&frmAction=EmailTemplateModify_confirmdelete";
//$oField->resources[] = "EmailTemplateModify"; 
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("email_template_default");


$tpl_email = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email/*");
if(is_array($tpl_email) && count($tpl_email)) {
	foreach($tpl_email AS $real_file) {
		if(is_dir($real_file) && is_file($real_file . "/email.tpl")) {
        	$tpl_email_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file) . "/email.tpl";
			
			if(strlen($sSQL_part)) {
		        $sSQL_part .= " UNION ";
			}
			$sSQL_part .= "(
		            		SELECT " . $db_gallery->toSql($tpl_email_path) . " AS ID
		            			, " . $db_gallery->toSql($tpl_email_path) . " AS name
		            		)";

		   // $oField->multi_pairs[] = array(new ffData($tpl_email_path), new ffData($tpl_email_path), new ffData($arrThemeDir_value));
		}
	}
	
	if(strlen($sSQL_part)) {
		$oField->source_SQL = "SELECT tbl_src.* FROM (" . $sSQL_part . ") AS tbl_src [WHERE] ORDER BY name";
	} else {
		$oField->source_SQL = "SELECT tbl_src.* FROM (SELECT '' AS ID, '' AS name, null AS type) AS tbl_src [WHERE] ORDER BY name";
	}	
} 

$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "from_name";
$oField->label = ffTemplate::_get_word_by_code("email_edit_from_name");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "from_email";
$oField->label = ffTemplate::_get_word_by_code("email_edit_from_email");
$oField->addValidator("email");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_notify";
$oField->label = ffTemplate::_get_word_by_code("email_edit_enable_notify");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "email_debug";
$oField->label = ffTemplate::_get_word_by_code("email_edit_email_debug");
$oField->default_value = new ffData(EMAIL_DEBUG);
$oField->addValidator("email");
$oRecord->addContent($oField);



$cm->oPage->addContent($oRecord);

$oDetail_address = ffDetails::factory($cm->oPage);
$oDetail_address->id = "EmailAddress";
$oDetail_address->title = ffTemplate::_get_word_by_code("address");
$oDetail_address->src_table = "email_rel_address";
$oDetail_address->order_default = "ID";
$oDetail_address->fields_relationship = array ("ID_email" => "email-ID");
//$oDetail_address->tab = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_address->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_address";
$oField->label = ffTemplate::_get_word_by_code("email_edit_address_name");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT email_address.ID
							, CONCAT(
								email_address.name
								, ' ('
								, email_address.email
								, ')'
							)
						FROM email_address 
						WHERE 1 
						ORDER BY email_address.name";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/address/modify";
$oField->actex_dialog_edit_params = array("keys[email-ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailAddressModify_confirmdelete";
$oField->resources[] = "EmailAddressModify"; 

$oField->required = true;
$oDetail_address->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("email_edit_address_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("cc"), new ffData(ffTemplate::_get_word_by_code("email_cc"))),
                            array(new ffData("bcc"), new ffData(ffTemplate::_get_word_by_code("email_bcc")))
                       );
$oField->required = true;
$oDetail_address->addContent($oField);

$oRecord->addContent($oDetail_address);
$cm->oPage->addContent($oDetail_address);
 


// -------------------------
//          EVENTI
// -------------------------
function EmailModify_on_do_action($component, $action) {
    $theme = $component->parent[0]->theme;
    $form_path = "/email/" . $component->form_fields["name"]->getValue();
    $res = true; 
         
    /*if(SMTP_AUTH && A_SMTP_USER == $component->form_fields["from_email"]->getValue()) {
        $component->tplDisplayError(ffTemplate::_get_word_by_code("SMTP_and_FROM_must_be_different"));
        return false;
    }*/
    
    switch ($action) {
        case "insert":
            if (check_function("process_mail")) {
                $component->form_fields["tpl_email_path"]->setValue(clone_template_mail($component->form_fields["name"]->getValue()));
            }
            break;
        case "update":
        
            break;
        case "confirmdelete":
        	if(check_function("fs_operation"))
            	xpurge_dir(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $form_path);
            break;
            
        default:            
    }
    
    if($res) {
        return false;
    } else {
        $component->tplDisplayError(ffTemplate::_get_word_by_code("email_copy_template_failed"));
        return true;
    }
    

}

?>
