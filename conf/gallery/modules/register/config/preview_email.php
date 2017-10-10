<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db_gallery->query("SELECT module_register.*
                        FROM 
                            module_register
                        WHERE 
                            module_register.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info))) . "
                            ");
if($db_gallery->nextRecord()) {
    $ID_register = $db_gallery->getField("ID")->getValue();
    $register_name = $db_gallery->getField("name")->getValue();
    $force_redirect = $db_gallery->getField("force_redirect")->getValue();
    $enable_privacy = $db_gallery->getField("enable_privacy")->getValue();
    $enable_require_note = $db_gallery->getField("enable_require_note")->getValue();
    $enable_newsletter = $db_gallery->getField("enable_newsletter")->getValue();
    $enable_general_data = $db_gallery->getField("enable_general_data")->getValue();
    $enable_bill_data = $db_gallery->getField("enable_bill_data")->getValue();
    $enable_ecommerce_data = $db_gallery->getField("enable_ecommerce_data")->getValue();
    $enable_manage_account = $db_gallery->getField("enable_manage_account")->getValue();
    $primary_gid = $db_gallery->getField("primary_gid")->getValue();
    $activation = $db_gallery->getField("activation")->getValue();
    $anagraph_type = $db_gallery->getField("ID_anagraph_type")->getValue();
    $require_note = $db_gallery->getField("require_note")->getValue();
    $ID_email = $db_gallery->getField("ID_email", "Number", true);
	if(!$ID_email > 0) {
        $struct_email = email_system("account registration");
        $ID_email = $struct_email["ID"];
		$sSQL = "UPDATE module_register SET 
					module_register.ID_email_activation = " . $db->toSql($ID_email, "Number") . " 
				WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
		$db->execute($sSQL);
	}		            	

    $ID_email_activation = $db_gallery->getField("ID_email_activation", "Number", true);
	if(!$ID_email_activation > 0) {
        $struct_email = email_system("account activation");
        $ID_email_activation = $struct_email["ID"];

		$sSQL = "UPDATE module_register SET 
					module_register.ID_email_activation = " . $db->toSql($ID_email_activation, "Number") . " 
				WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
		$db->execute($sSQL);
	}		            	
    
    if($activation) {
        $active = 0;        
    } else {
        $active = 1;
    }

    $generate_password = $db_gallery->getField("generate_password")->getValue();
    $display_view_mode = $db_gallery->getField("display_view_mode")->getValue();
 
 
 	$fields_activation["activation"]["username"] = "username_example";
    $fields_activation["activation"]["email"] = "email@example.ex";
    $fields_activation["activation"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH .  VG_SITE_MOD_SEC_ACTIVATION . "?frmAction=activation&sid=" . urlencode($rnd_active);

	$fields["account"]["username"] = "username_example";
	$fields["account"]["password"] = "password_example";
	
    
	if($enable_general_data) {
	    if(ENABLE_AVATAR_SYSTEM) {
			$fields["account"]["avatar"] = 'avatar_example';
		}
		$fields["account"]["name"] = "name_example";
		$fields["account"]["surname"] = "surname_example";
		$fields["account"]["email"] = "email@example.ex";
		$fields["account"]["tel"] = "tel_example";
	} else {
		$fields["account"]["email"] = "email@example.ex";
	} 
	if($enable_bill_data) {
        $fields["account"]["billreference"] = "billreference_example";
        $fields["account"]["billcf"] = "billcf_example"; 
        $fields["account"]["billpiva"] = "billpiva_example";
        $fields["account"]["billaddress"] = "billaddress_example";
        $fields["account"]["billcap"] = "billcap_example";
        $fields["account"]["billtown"] = "billtown_example";
        $fields["account"]["billprovince"] = "billprovince_example";
        if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
            $fields["account"]["billstate"] = "billstate_example";
        }
    }
	if($enable_ecommerce_data) {
	    $fields["account"]["shippingreference"] = "shippingreference_example";
	    $fields["account"]["shippingaddress"] = "shippingaddress_example";
	    $fields["account"]["shippingcap"] = "shippingcap_example";
	    $fields["account"]["shippingtown"] = "shippingtown_example";
	    $fields["account"]["shippingprovince"] = "shippingprovince_example";
        if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
	        $fields["account"]["shippingstate"] = "shippingstate_example";
        }
	}

	if(!strlen(EMAIL_DEBUG)) {
		$cm->oPage->addContent(ffTemplate::_get_word_by_code("email_debug_empty"), null, "EmailDebugEmpty");
	}
	if($_REQUEST["frmAction"] == "send" && strlen(EMAIL_DEBUG)) {
        //Caricamento del template di base html
        $to[] = EMAIL_DEBUG;  
        $from[] = "noreply@" . DOMAIN_NAME;

        if(check_function("process_mail")) {
	        $res_activation = process_mail($ID_email_activation, $to, NULL, NULL, $fields_activation, $from, false, false, false, false);
	        $res_registration = process_mail($ID_email, $to, NULL, NULL, $fields, $from, false, false, false, false);
		}
        if($rc) {
            $cm->oPage->addContent($register_name . " " . $res_activation, null, "AccountActivation");
            $cm->oPage->addContent($register_name . " " . $res_registration, null, "AccountRegistration");
		} else {
            ffRedirect($_REQUEST["ret_url"]);
		}
    } else {  
	    $to[0]["name"] = "username_example";
	    $to[0]["mail"] = "email@example.ex";        
        
        $rnd_active = mod_sec_createRandomPassword();
        
		if(check_function("process_mail")) {
			$cm->oPage->addContent(process_mail($ID_email_activation, $to, null, null, $fields_activation, NULL, NULL, NULL, true, false), null , "AccountActivation");
		    $cm->oPage->addContent(process_mail($ID_email, $to, null, null, $fields, NULL, NULL, NULL, true, false), null, "AccountRegistration");
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

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = urldecode($_REQUEST["ret_url"]);
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);


$cm->oPage->addContent("<div class=\"prev_button\" >" . (isset($oButton_send) ? $oButton_send->process() : "") . (isset($oButton_customize) ? $oButton_customize->process() : "") . $oButton->process() . "</div>");
?>
