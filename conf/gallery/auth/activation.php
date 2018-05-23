<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

use_cache(false);
$framework_css = mod_sec_get_framework_css();
$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));

$mod_sec_login = $cm->router->getRuleById("mod_sec_login");
$mod_sec_activation = ($cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code) 
                        ? $cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)
                        : $cm->router->getRuleById("mod_sec_activation")
                    );

if(check_function("get_layout_settings"))
	$layout_settings = get_layout_settings(NULL, "LOGIN");

if(MOD_SEC_CSS_PATH !== false && isset($cm->router->matched_rules["mod_sec_login"])) {
    $css_name = "ff.modules.security.css";
    if(MOD_SEC_CSS_PATH)
        $filename = MOD_SEC_CSS_PATH;
    else
        $filename = cm_moduleCascadeFindTemplateByPath("security", "/css/" . $css_name, $cm->oPage->theme);

    $ret = cm_moduleGetCascadeAttrs($filename);
    $cm->oPage->tplAddCSS($css_name, $filename, $ret["path"]);
    //$cm->oPage->tplAddCSS("modules.security", "", cm_getModulesExternalPath() . "/security/restricted/css/ff.modules.security.css"); // useful for caching purpose
}
    
$tpl = ffTemplate::factory(get_template_cascading($user_path, "activation.html", "/login"));
$tpl->load_file("activation.html", "main");
$tpl->set_var("site_path", FF_SITE_PATH);
$tpl->set_var("theme_inset", THEME_INSET);

$component_class["base"] = $framework_css["component"]["class"];
if($framework_css["component"]["grid"]) {
    if(is_array($framework_css["component"]["grid"]))
        $component_class["grid"] = cm_getClassByFrameworkCss($framework_css["component"]["grid"], "col");
    else {
        $component_class["grid"] = cm_getClassByFrameworkCss("", $framework_css["component"]["grid"]);      
    }
}   

$sError = "";

// RECUPERA VALORI
$frmAction      = strtolower($_REQUEST["frmAction"]);
$username       = $_REQUEST["username"];
$ret_url        = ($_REQUEST["ret_url"]
                    ? $_REQUEST["ret_url"]
                    : $_SERVER["HTTP_REFERER"]
                );

if (!strlen($ret_url) || strpos($ret_url, $mod_sec_activation->reverse) !== false)
    $ret_url = FF_SITE_PATH . $mod_sec_login->reverse;

$tpl->set_var("ret_url", $ret_url);

$tpl->set_var("container_class", implode(" ", array_filter($component_class))); 

if(!MOD_SEC_LOGO) {  
    $framework_css["login"]["def"]["col"] = array( 
                                            "xs" => 12
                                            , "sm" => 12
                                            , "md" => 12
                                            , "lg" => 12 
                                        );
	$framework_css["logout"] = false;
    $framework_css["inner-wrap"]["col"] = array( 
                                            "xs" => 6
                                            , "sm" => 6
                                            , "md" => 6
                                            , "lg" => 6 
                                        );
    $framework_css["inner-wrap"]["push"] = array( 
                                            "xs" => 3
                                            , "sm" => 3
                                            , "md" => 3
                                            , "lg" => 3 
                                        );
}
$tpl->set_var("inner_wrap_class", cm_getClassByDef($framework_css["inner-wrap"]));

$tpl->set_var("row_class", cm_getClassByDef($framework_css["login"]["standard"]["record"]));
$tpl->set_var("activation_button_class", cm_getClassByDef($framework_css["actions"]["activation"]));
$tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));

if(MOD_SEC_LOGIN_LABEL) {
    $tpl->parse("SectUsernameLabel", false);
}
/*
if ($cm->oPage->getXHRDialog())
{
    $tpl->set_var("bt_confirm", "jQuery(this).closest('.login').css({'opacity': 0.5, 'pointer-events': 'none'}); ff.ffPage.dialog.doRequest('" . $_REQUEST["XHR_DIALOG_ID"] . "', {
                'action' : 'request'
        });");
}
else
{
    $tpl->set_var("bt_confirm", "jQuery(this).closest('.login').css({'opacity': 0.5, 'pointer-events': 'none'}); ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function(){ ff.ajax.doRequest({
                'action' : 'request'
                , 'formName'    : 'ffActivation'
				, 'injectid'    : 'ffActivation'
                , 'url'            : '" . $_SERVER["REQUEST_URI"] . "'
			}); 
		});");
}*/
$tpl->set_var("login_class", cm_getClassByDef($framework_css["login"]["def"]));

$options = mod_security_get_settings($cm->path_info);
switch($frmAction) {
    case "request":
        if (!strlen($username))
        {
            $sError = ffTemplate::_get_word_by_code("error_activation_fieldempty");
        }
        else
        {   
            if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
            {
                if (strlen($domain))
                {
                    $db = mod_security_get_main_db();

                    $db->query("SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db->toSql($domain));
                    if ($db->nextRecord())
                    {
                        $ID_domain = $db->getField("ID", "Number")->getValue();
                    }
                    else
                    {
                        $sError = ffTemplate::_get_word_by_code("login_domain_not_found");
                    }
                }
                else
                {
                    $ID_domain = 0;
                }
            }

            if (!strlen($sError))
            {
                if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
                    $db = mod_security_get_db_by_domain($ID_domain);
                else
                    $db = mod_security_get_main_db();
                                        
                $sSQL = "SELECT
                            " . $options["table_name"] . ".ID
                            , " . $options["table_name"] . ".username
                            , " . $options["table_name"] . ".email
                            , module_register.activation AS activation
                            , module_register.ID_email_activation AS ID_email_activation
                            , module_register.ID_email AS ID_email
                        FROM
                            " . $options["table_name"] . "
                            LEFT JOIN module_register ON module_register.ID = " . $options["table_name"] . ".ID_module_register
                        WHERE
                            (DATE(expiration) = '0000-00-00' OR DATE(expiration) > CURDATE())
                            AND (";
                if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
                     $sSQL .= $options["table_name"] . ".username = " . $db->toSql($username, "Text");
                if (MOD_SECURITY_LOGON_USERID == "both")
                     $sSQL .= " OR ";
                if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")
                     $sSQL .= $options["table_name"] . ".email = " . $db->toSql($username, "Text");
                $sSQL .= ")";

                if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
                    $sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);
                
                if (MOD_SEC_EXCLUDE_SQL)
                    $sSQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;
                
                $sSQL .= " ORDER BY ID DESC";

                $db->query($sSQL);   
                if ($db->nextRecord())
                {
                    if($db->getField("status")->getValue() > 0)
                    {
                        $sError = ffTemplate::_get_word_by_code("error_activation_already_active");
                    }
                    else 
                    {
                        $ID = $db->getField("ID", "Number", true);

                        $activation = $db->getField("activation")->getValue();
                        $username = $db->getField("username")->getValue();
                        $email = $db->getField("email")->getValue();
                        
                        $ID_email_activation = $db->getField("ID_email_activation", "Number", true);
						if(!$ID_email_activation) {
                            $struct_email = email_system("account activation");
                            $ID_email_activation = $struct_email["ID"];
						}
                        switch($activation) {
                            case 1:
                                $to_active[0]["name"] = $username;
                                $to_active[0]["mail"] = $email;
                                break;
                            case 2:
                                $to_active[0]["name"] = A_FROM_NAME;
                                $to_active[0]["mail"] = A_FROM_EMAIL;
                                break;
                            case 4:
                            default:
                                $to_active[0]["name"] = $username;
                                $to_active[0]["mail"] = $email;
                                $to_active[1]["name"] = A_FROM_NAME;
                                $to_active[1]["mail"] = A_FROM_EMAIL;
                        }

                        if(is_array($to_active) && count($to_active) > 0) {
                            $rnd_active = mod_sec_createRandomPassword();
                    
                            $sSQL = "UPDATE " . $options["table_name"] . " SET active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") WHERE ID = " . $db->toSql($ID, "Number");
                            $db->execute($sSQL);

                            /**                                               
                            * TODO:Da togliere gestione utente e fonderla con anagraph
                            */ 
                            if($options["table_name"] != "anagraph") {
	                            $sSQL = "UPDATE anagraph SET
	                                            active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ")
	                                        WHERE uid = " . $db->toSql($ID, "Number");
	                            $db->execute($sSQL);                                              
							}

                            $fields_activation["activation"]["username"] = $username;
                            $fields_activation["activation"]["email"] = $email;
                            $fields_activation["activation"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . $mod_sec_activation->reverse  . "?frmAction=activation&sid=" . urlencode($rnd_active);

                            if(check_function("process_mail")) {
                                //$rc_from_activation = process_mail(email_system("account activation"), $to_active, NULL, NULL, $fields_activation, null, null, null, false, null, true);
                        	    $rc_activation = process_mail($ID_email_activation, $to_active, NULL, NULL, $fields_activation, null, null, null, false, null, true);
                            }
                            
                            if(!$rc_activation) {
                                ffRedirect(FF_SITE_PATH . $mod_sec_activation->reverse . "?frmAction=active&desc=sendmail&ret_url=" . urlencode(FF_SITE_PATH . "/"));
                            } else {
                                $sError = $rc_activation;
                            }
                        }
                    }
                }
                else
                    $sError = ffTemplate::_get_word_by_code("error_activation_fieldwrong");
            }
        }
        $display_activation = true;
        break;
    case "activation":
        $db = mod_security_get_main_db();

        $sid = $_REQUEST["sid"];
        $db->query("SELECT " . $options["table_name"] . ".ID
                        , " . $options["table_name"] . ".username
                        , " . $options["table_name"] . ".email
                        , " . $options["table_name"] . ".name
                        , " . $options["table_name"] . ".surname
                        , module_register.activation AS activation
                        , module_register.ID_email AS ID_email
                    FROM
                        " . $options["table_name"] . "
                        LEFT JOIN module_register ON module_register.ID = " . $options["table_name"] . ".ID_module_register
        			WHERE active_sid = PASSWORD(" . $db->toSql(new ffData($sid)) . ")");
        if ($db->nextRecord())
        {   
            $ID = $db->getField("ID", "Number", true);
            
            if($db->getField("status")->getValue() > 0)
            {
                $sError = ffTemplate::_get_word_by_code("error_activation_already_active");
            }
            else 
            {
                $to[0]["name"] = $db->getField("username", "Text", true);
                $to[0]["mail"] = $db->getField("email", "Text", true);

                $fields["status"]["description"] = ffTemplate::_get_word_by_code("activation_success");

                $fields["account"]["username"] = $db->getField("username", "Text", true);
                $fields["account"]["email"] = $db->getField("email", "Text", true);
                $fields["account"]["name"] = $db->getField("name", "Text", true);
                $fields["account"]["surname"] = $db->getField("surname", "Text", true);
                $fields["activationlogin"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . $mod_sec_login->reverse . "?username=" . urlencode($fields["account"]["username"]);

                if(check_function("process_mail")) {
					$ID_email = $db->getField("ID_email", "Number", true);
					if(!$ID_email) {
                        $struct_email = email_system("account registration");
                        $ID_email = $struct_email["ID"];
					}                
                
                    //$rc_from_account = process_mail($ID_email, $to, NULL, NULL, $fields, null, null, null, false, null, true);
                    $rc_account = process_mail($ID_email, $to, NULL, NULL, $fields, null, null, null, false, null, true);
                }
                $sSQL = "UPDATE " . $options["table_name"] . " SET
                                status = 1 
                            WHERE ID = " . $db->toSql($ID, "Number");
                $db->execute($sSQL);
                    
                /**                                               
                * TODO:Da togliere gestione utente e fonderla con anagraph
                */ 
                if($options["table_name"] != "anagraph") {
	                $sSQL = "UPDATE anagraph SET
	                                status = 1 
	                            WHERE uid = " . $db->toSql($ID, "Number");
	                $db->execute($sSQL);
				}

                if(check_function("analytics"))
                    analytics_set_event('/registrazione/confirm-registration', "Confirm registration");
				
				$cm->doEvent("on_done_activation", array($ID));
				
                ffRedirect(FF_SITE_PATH . $mod_sec_activation->reverse . "?frmAction=active&desc=success&ret_url=" . urlencode(FF_SITE_PATH . "/"));
            }
        }
        else 
            {
              $sError = ffTemplate::_get_word_by_code("error_activation_sid_wrong");
            }
        $display_activation = true;
        break;
    case "active":
    	$display_activation = false;
    	break;
    default:
		$display_activation = true;
}

if ($layout_settings["AREA_LOGIN_ACTIVATION_SHOW_TITLE"])
    $tpl->parse("SezTitle", false);

$tpl->set_var("back_class", cm_getClassByDef($framework_css["links"]["back"]));
$tpl->set_var("back_url", $ret_url);

if($display_activation) {
    $tpl->parse("SezActivation", false);
    $tpl->set_var("SezActive", "");
} else {
	if(strlen($_REQUEST["desc"])) {
		$tpl->set_var("active_description", ffTemplate::_get_word_by_code("activation_" . $_REQUEST["desc"]));
	}
    $tpl->set_var("SezActivation", "");
    $tpl->parse("SezActive", false);
}

// MANAGE ERRORS
if (strlen($sError)) {

	if ($cm->isXHR())
    {
		$cm->jsonAddResponse(array(
				"success" => false 
				, "modules" => array(
					"security" => array(
						"action" => "activation request"
						, "error" => '<div class="' . cm_getClassByDef($framework_css["error"]) . '">' . $sError . '</div>'
					)
				)
			));
		cm::jsonParse($cm->json_response);
		exit;
    }
    else
    {
	    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));
	    $tpl->set_var("strError", $sError);
	    $tpl->parse("SezError", false);
	}
}

if(MOD_SEC_LOGO) {
    if(MOD_SEC_LOGO_PATH === false) {
        $tpl->set_var("SectLogoImg" . MOD_SEC_LOGO, "");
    } else {
        if(is_file(FF_DISK_PATH . MOD_SEC_LOGO_PATH))
            $logo_url = MOD_SEC_LOGO_PATH;
        elseif(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/images/logo-login.png")) 
            $logo_url = FF_THEME_DIR . "/" . $cm->oPage->getTheme() . "/images/logo-login.png";
        elseif(is_file(FF_THEME_DISK_PATH . "/" . cm_getMainTheme() . "/images/logo-login.gif"))
            $logo_url = FF_THEME_DIR . "/" . cm_getMainTheme() . "/images/logo-login.gif";

        $tpl->set_var("logo_login", $logo_url);
        $tpl->parse("SectLogoImg" . MOD_SEC_LOGO, false);
    }
    $tpl->set_var("logo_class", cm_getClassByDef($framework_css["logo"]));
    $tpl->parse("SectLogo" . MOD_SEC_LOGO, false);
}
$cm->oPage->addContent($tpl->rpparse("main", false), null, "Activation");