<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

use_cache(false);

$framework_css = mod_sec_get_framework_css();
$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));

$mod_sec_login = $cm->router->getRuleById("mod_sec_login");
$mod_sec_dashboard = $cm->router->getRuleById("mod_sec_dashboard");
if($mod_sec_dashboard)
	$dashboard_ret_url = $mod_sec_dashboard->reverse;

if(basename($cm->real_path_info) == "username") {
    $mod_sec_recover = ($cm->router->getRuleById("mod_sec_recover_username_" . $tiny_lang_code) 
                            ? $cm->router->getRuleById("mod_sec_recover_username_" . $tiny_lang_code)
                            : $cm->router->getRuleById("mod_sec_recover_username")
                        );
} else {
    $mod_sec_recover = ($cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code) 
                            ? $cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code)
                            : $cm->router->getRuleById("mod_sec_recover")
                        );
}
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
    
$tpl = ffTemplate::factory(get_template_cascading($user_path, "lostpassword.html", "/login"));
$tpl->load_file("lostpassword.html", "main");
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

if (!strlen($ret_url) || strpos($ret_url, $mod_sec_recover->reverse) !== false)
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
$tpl->set_var("recover_button_class", cm_getClassByDef($framework_css["actions"]["recover"]));
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
                , 'formName'    : 'ffRecover'
				, 'injectid'    : 'ffRecover'
                , 'url'            : '" . $_SERVER["REQUEST_URI"] . "'
			}); 
        });");
}*/
$tpl->set_var("login_class", cm_getClassByDef($framework_css["login"]["def"]));

$options = mod_security_get_settings($cm->path_info);
switch($frmAction) {
    case "request":
        // CHECK FIELDS
        if (!strlen($username))
            {
                $sError = ffTemplate::_get_word_by_code("error_lostpassword_fieldempty");
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
                                " . $options["table_name"] . ".*
                            FROM
                                " . $options["table_name"] . "
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
			            $username = $db->getField("username")->getValue();
			            $email = $db->getField("email")->getValue();
			            $uid = $db->getField("ID")->getValue();
			            $active_sid = $db->getField("active_sid")->getValue();
			            $status = $db->getField("status")->getValue();

			            $to_lostpassword[0]["name"] = $username;
			            $to_lostpassword[0]["mail"] = $email;

		                if(is_array($to_lostpassword) && count($to_lostpassword) > 0) {
		                	$rnd_active = mod_sec_createRandomPassword();
			                $sSQL = "UPDATE " . $options["table_name"] . " SET 
			                			active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") 
			                		WHERE ID = " . $db->toSql($uid, "Number");
			                $db->execute($sSQL);

		                    $fields_lostpassword["lostpasswordrequest"]["username"] = $username;
		                    $fields_lostpassword["lostpasswordrequest"]["email"] = $email;
		                    $fields_lostpassword["lostpasswordrequest"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . $mod_sec_recover->reverse . "?frmAction=activation&sid=" . urlencode($rnd_active);

		                    if(check_function("process_mail")) {
                                //$rc_from_lostpassword = process_mail(email_system("account lostpassword request"), $to_lostpassword, NULL, NULL, $fields_lostpassword, null, null, null, false, null, true);
		                	    $rc_lostpassword = process_mail(email_system("account lostpassword request"), $to_lostpassword, NULL, NULL, $fields_lostpassword);
                            }

		                    if(!$rc_lostpassword) {
		                        ffRedirect(FF_SITE_PATH . $mod_sec_recover->reverse . "?frmAction=active&desc=sendmail&ret_url=" . urlencode(FF_SITE_PATH . "/"));
		                    } else {
		                        $sError = $rc_lostpassword;
		                    }
		                }
                    }
                    else
                        $sError = ffTemplate::_get_word_by_code("error_lostpassword_fieldwrong");
                }
            }
            $display_activation = true;
            break;
    case "activation":
        $db = mod_security_get_main_db();

    	$sid = $_REQUEST["sid"];
        $db->query("SELECT * FROM " . $options["table_name"] . " WHERE active_sid = PASSWORD(" . $db->toSql(new ffData($sid)) . ")");
        if ($db->nextRecord())
            {   
                $username = $db->getField("username")->getValue();
                $email = $db->getField("email")->getValue();
                $uid = $db->getField("ID")->getValue();

                $to_lostpassword[0]["name"] = $username;
                $to_lostpassword[0]["mail"] = $email;
                
                $password_generated_at = strtotime($db->getField("password_generated_at", "Date", true));
				if($password_generated_at < (time() - 3600)) {
	                if(is_array($to_lostpassword) && count($to_lostpassword) > 0) {
	                    $rnd_password = mod_sec_createRandomPassword();
	                    
	                    $sSQL = "UPDATE " . $options["table_name"] . " SET 
                    				`password` = PASSWORD(" . $db->toSql($rnd_password, "Text") . ") 
                    				, `status` = '1'
                    				, password_generated_at = " . $db->toSql(date("Y-m-d H:i:s", time()), "DateTime") . "
                    			WHERE ID = " . $db->toSql($uid, "Number");
	                    $db->execute($sSQL);
	                    
	                    /**                                               
	                    * TODO:Da togliere gestione utente e fonderla con anagraph
	                    */  
		                if($options["table_name"] != "anagraph") {
		                    $sSQL = "UPDATE anagraph SET 
	                    				`password` = PASSWORD(" . $db->toSql($rnd_password, "Text") . ") 
	                    				, `status` = '1'
	                    				, password_generated_at = " . $db->toSql(date("Y-m-d H:i:s", time()), "DateTime") . "
	                    			WHERE uid = " . $db->toSql($uid, "Number");
		                    $db->execute($sSQL);
						}                    
						
						$cm->doEvent("on_done_recover", array($ID));
						$sSQL = "SELECT token
									FROM cm_mod_security_token
									WHERE ID_user = " . $db->toSql($uid, "Number");
						$db->query($sSQL);
						if($db->nextRecord()) {
							$token = $db->getField("token", "Text", true);
						}
									
	                    $fields_lostpassword["lostpassword"]["username"] = $username;
	                    $fields_lostpassword["lostpassword"]["password"] = $rnd_password;
	                    $fields_lostpassword["lostpassword"]["email"] = $email;
						if(strlen($token)) {
							$fields_lostpassword["lostpasswordlogin"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . $dashboard_ret_url . "?t=" . $token . "&lost-password";
						} else {
							$fields_lostpassword["lostpasswordlogin"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . $mod_sec_login->reverse . "?username=" . urlencode($fields_lostpassword["lostpassword"]["username"]) . "&ret_url=" . urlencode(FF_SITE_PATH . $mod_sec_dashboard->reverse . "?lost-password");
						}

	                    if(check_function("process_mail")) {
	                        $rc_from_lostpassword = process_mail(email_system("account lostpassword"), $to_lostpassword, NULL, NULL, $fields_lostpassword, null, null, null, false, null, true);
                    		$rc_lostpassword = process_mail(email_system("account lostpassword"), $to_lostpassword, NULL, NULL, $fields_lostpassword);
	                    }
	                    if(!$rc_lostpassword) {
	                        ffRedirect(FF_SITE_PATH . $mod_sec_recover->reverse . "?frmAction=active&desc=success&ret_url=" . urlencode(FF_SITE_PATH . "/"));
	                    } else {
	                        $sError = $rc_lostpassword;
	                    }
	                }
				} else {
					ffRedirect(FF_SITE_PATH . $mod_sec_recover->reverse . "?frmAction=active&desc=success&ret_url=" . urlencode(FF_SITE_PATH . "/"));
				}
			}
        else 
            {
              $sError = ffTemplate::_get_word_by_code("error_lostpassword_sid_wrong");
            }
        $display_activation = true;
		break;
    case "active":
    	$display_activation = false;
    	break;
    default:
		$display_activation = true;
}

if ($layout_settings["AREA_LOGIN_LOSTPASSWORD_SHOW_TITLE"])
    $tpl->parse("SezTitle", false);

$tpl->set_var("back_class", cm_getClassByDef($framework_css["links"]["back"]));
$tpl->set_var("back_url", $ret_url); 

if($display_activation) {
    $tpl->parse("SezLostPassword", false);
    $tpl->set_var("SezActive", "");
} else {
	if(strlen($_REQUEST["desc"])) {
		$tpl->set_var("lostpassword_description", ffTemplate::_get_word_by_code("lostpassword_" . $_REQUEST["desc"])); 
	}
    $tpl->set_var("SezLostPassword", "");
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
						"action" => "recover request"
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
$cm->oPage->addContent($tpl->rpparse("main", false), null, "LostPassword");
?>