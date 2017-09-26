<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function set_user_permission_by_settings($ID_user, $status = true, $skip_redirect = false) {
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();

	$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));

	$mod_sec_login = $cm->router->getRuleById("mod_sec_login");
	$mod_sec_activation = ($cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code) 
		                    ? $cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)
		                    : $cm->router->getRuleById("mod_sec_activation")
		                );
    $user_permission_isset = false;
    
    $sSQL = "SELECT module_register.* 
            FROM module_register 
            WHERE module_register.default = '1'";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $user_permission_isset = true;
        
        $ID_register = $db->getField("ID", "Number", true);
        $primary_gid = $db->getField("primary_gid", "Number", true);
        $activation = $db->getField("activation", "Number", true);

        $enable_general_data             = $db->getField("enable_general_data", "Number", true);
        $enable_bill_data                 = $db->getField("enable_bill_data", "Number", true);
        $enable_ecommerce_data             = $db->getField("enable_ecommerce_data", "Number", true);
        $enable_manage_account             = $db->getField("enable_manage_account", "Number", true);
        $enable_setting_data             = $db->getField("enable_setting_data", "Number", true);
        
        $enable_public                     = $db->getField("public", "Number", true);
        $anagraph_type                     = $db->getField("ID_anagraph_type", "Number", true);

        $force_redirect = $db->getField("force_redirect", "Text", true);

        $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users
                SET
                    " . CM_TABLE_PREFIX . "mod_security_users.primary_gid = " . $db->toSql($primary_gid, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.enable_general_data = " . $db->toSql($enable_general_data, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.enable_bill_data = " . $db->toSql($enable_bill_data, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.enable_ecommerce_data = " . $db->toSql($enable_ecommerce_data, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.enable_manage = " . $db->toSql($enable_manage_account, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.enable_setting_data = " . $db->toSql($enable_setting_data, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.public = " . $db->toSql($enable_public, "Number") . "
                    , " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register = " . $db->toSql($ID_register, "Number") . "
                WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID_user, "Number");
        $db->execute($sSQL);

        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_rel_groups 
                    ( 
                        SELECT DISTINCT uid, gid
                        FROM 
                        (                        
                            (
                                SELECT " . $db->toSql($ID_user, "Number") . " AS uid, gid 
                                FROM module_register_rel_gid 
                                WHERE module_register_rel_gid.ID_module_register = " . $db->toSql($ID_register, "Number") . " 
                                    AND module_register_rel_gid.value = '1'
                            )
                            UNION 
                            (
                                SELECT " . $db->toSql($ID_user, "Number") . " AS uid, " . $db->toSql($primary_gid, "Number") . " AS gid 
                            )
                        ) AS tbl_src
                    )";
        $db->execute($sSQL);
        
        
        $sSQL = "INSERT INTO users_rel_module_form 
                    ( 

                        SELECT 
                            '' AS ID
                            , " . $db->toSql($ID_user, "Number") . " AS uid
                            , module_register_rel_form.ID_module  AS ID_form
                            , 0                                 AS ID_form_node
                            , module_register_rel_form.request  AS request
                            , module_register_rel_form.`order`  AS `order`
                            , module_register_rel_form.`public`  AS `public`
                        FROM module_register_rel_form 
                        WHERE 
                            module_register_rel_form.ID_module_register = " . $db->toSql($ID_register, "Number") . " 
                        ORDER BY `order`
                    )";
        $db->execute($sSQL);
        
        
        $sSQL = "INSERT INTO users_rel_vgallery
                    (ID, uid, ID_nodes, cascading, request, `order`)
                    ( 

                        SELECT 
                            '' AS ID
                            , " . $db->toSql($ID_user, "Number") . " AS uid
                            , module_register_rel_vgallery.ID_vgallery_nodes  AS ID_nodes
                            , module_register_rel_vgallery.cascading  AS cascading
                            , module_register_rel_vgallery.request  AS request
                            , module_register_rel_vgallery.`order`  AS `order`
                        FROM module_register_rel_vgallery
                        WHERE 
                            module_register_rel_vgallery.ID_module_register = " . $db->toSql($ID_register, "Number") . " 
                        ORDER BY `order`
                    )";
        $db->execute($sSQL);         

        if($status) {
            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.* 
                    FROM " . CM_TABLE_PREFIX . "mod_security_users 
                    WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID_user, "Number") . "
                        AND " . CM_TABLE_PREFIX . "mod_security_users.`status` = 0";
            $db->query($sSQL);
            if($db->nextRecord()) {
                $status = $db->getField("status", "Number", true);
                $username = $db->getField("username", "Text", true);
                $email = $db->getField("email", "Text", true);
            
                switch($activation) {
                    case 1:
                        $to_active[0]["name"] = $username;
                        $to_active[0]["mail"] = $email;
                        break;
                    case 2:
                        $to_active[0]["name"] = A_FROM_NAME;
                        $to_active[0]["mail"] = A_FROM_EMAIL;
                        
                        $to_active_waiting[0]["name"] = $username;
                        $to_active_waiting[0]["mail"] = $email;
                        break;
                    case 4:
                        $to_active[0]["name"] = $username;
                        $to_active[0]["mail"] = $email;
                        $to_active[1]["name"] = A_FROM_NAME;
                        $to_active[1]["mail"] = A_FROM_EMAIL;
                        break;
                        
                    default:
                       $to_active = array();
                    
                }

                if(is_array($to_active) && count($to_active) > 0) {
                    $rnd_active = mod_sec_createRandomPassword();
                    
                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users SET active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") WHERE ID = " . $db->toSql($ID_user, "Number");
                    $db->execute($sSQL);
                    
                    $fields_activation["activation"]["username"] = $username;
                    $fields_activation["activation"]["email"] = $email;
                    $fields_activation["activation"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH .  $mod_sec_activation . "?frmAction=activation&sid=" . urlencode($rnd_active);

                    if(check_function("process_mail")) {
                        $rc_activation = process_mail(email_system("account activation"), $to_active, NULL, NULL, $fields_activation);
                    }
                    if(!$rc_activation)
                        $rc_activation = 0; 
                        
                    if(is_array($to_active_waiting) && count($to_active_waiting)) {
                        $fields_activation_waiting["activation"]["username"] = $username;
                        $fields_activation_waiting["activation"]["email"] = $email;
                        $fields_activation_waiting["activation"]["waitingfor"] = ffTemplate::_get_word_by_code("activation_waitingfor_description");
                        
                        if(check_function("process_mail"))
                            $rc_activation = process_mail(email_system("account activation"), $to_active_waiting, NULL, NULL, $fields_activation_waiting);
                        if(!$rc_activation)
                            $rc_activation = 0; 
                    }
                }
            }
        }        
//da finire se necessario
/*
        $sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_users WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID_user, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $username = $component->form_fields["username"]->getValue();
            $email = $component->form_fields["email"]->getValue();

            $to[0]["name"] = $username;
            $to[0]["mail"] = $email;
            
            $fields["account"]["username"] = $component->form_fields["username"]->getValue();
            $fields["account"]["password"] = $component->form_fields["password"]->getValue();
            $fields["account"]["email"] = $component->form_fields["email"]->getValue();
            
            if($enable_bill_data) {
                $fields["account"]["billpiva"] = $component->form_fields["billpiva"]->getValue();
                $fields["account"]["billcf"] = $component->form_fields["billcf"]->getValue();
                $fields["account"]["billreference"] = $component->form_fields["billreference"]->getValue();
                $fields["account"]["billaddress"] = $component->form_fields["billaddress"]->getValue();
                $fields["account"]["billcap"] = $component->form_fields["billcap"]->getValue();
                $fields["account"]["billtown"] = $component->form_fields["billtown"]->getValue();
                $fields["account"]["billprovince"] = $component->form_fields["billprovince"]->getValue();
                if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
                    $sSQL = "SELECT IF(ISNULL(" . FF_PREFIX . "international.description) OR " . FF_PREFIX . "international.description = ''
                                        , " . FF_SUPPORT_PREFIX . "state.name
                                        , " . FF_PREFIX . "international.description) AS description 
                            FROM " . FF_SUPPORT_PREFIX . "state 
                                LEFT JOIN " . FF_PREFIX . "international ON " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name 
                                    AND " . FF_PREFIX . "international.ID_lang = (    
                                            SELECT " . FF_PREFIX . "languages.ID 
                                            FROM " . FF_PREFIX . "languages 
                                            WHERE " . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_INSET) . "
                                        )
                            WHERE 
                                " . FF_SUPPORT_PREFIX . "state.ID = " . (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0 ? AREA_ECOMMERCE_SHIPPING_LIMIT_STATE : $db->toSql($component->form_fields["billstate"]->value)) . "
                            ORDER BY description";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $fields["account"]["billstate"] = $db->getField("description")->getValue();
                    }
                }
            }
            if($enable_ecommerce_data) {
                $fields["account"]["shippingreference"] = $component->form_fields["shippingreference"]->getValue();
                $fields["account"]["shippingaddress"] = $component->form_fields["shippingaddress"]->getValue();
                $fields["account"]["shippingcap"] = $component->form_fields["shippingcap"]->getValue();
                $fields["account"]["shippingtown"] = $component->form_fields["shippingtown"]->getValue();
                $fields["account"]["shippingprovince"] = $component->form_fields["shippingprovince"]->getValue();
                if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
                    $sSQL = "SELECT IF(ISNULL(" . FF_PREFIX . "international.description) OR " . FF_PREFIX . "international.description = ''
                                        , " . FF_SUPPORT_PREFIX . "state.name
                                        , " . FF_PREFIX . "international.description) AS description 
                            FROM " . FF_SUPPORT_PREFIX . "state 
                                LEFT JOIN " . FF_PREFIX . "international ON " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name 
                                    AND " . FF_PREFIX . "international.ID_lang = (    
                                                                            SELECT " . FF_PREFIX . "languages.ID 
                                                                            FROM " . FF_PREFIX . "languages 
                                                                            WHERE " . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_INSET) . "
                                                                        )

                            WHERE 
                                " . FF_SUPPORT_PREFIX . "state.ID = " . (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0 ? AREA_ECOMMERCE_SHIPPING_LIMIT_STATE : $db->toSql($component->form_fields["shippingstate"]->value)) . "
                            ORDER BY description";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $fields["account"]["shippingstate"] = $db->getField("description")->getValue();
                    }
                }
            }
            if(check_function("process_mail")) {
                $rc_from_account = process_mail(email_system("account registration"), $to, NULL, NULL, $fields, null, null, null, false, null, true);
                $rc_account = process_mail(email_system("account registration"), $to, NULL, NULL, $fields);
            }
            if(!$rc_account)
                $rc_account = 0;

            $strMailNotify = "mc=" . urlencode($rc_account);
        }
*/  
		if(!$skip_redirect) {
	        if(isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"])) {
	            $ret_url = $_REQUEST["ret_url"];
	        } else {
	            $ret_url = $_SERVER["REQUEST_URI"];
	        }
	        
	        if(strlen($force_redirect)) {
	            $_REQUEST["ret_url"] = FF_SITE_PATH . $force_redirect . "?ret_url=" . urlencode($ret_url);
	        } else {
	            $_REQUEST["ret_url"] = FF_SITE_PATH . USER_RESTRICTED_PATH . "/account" . "?ret_url=" . urlencode($ret_url);
	        }
	    }
	}
    if(!$user_permission_isset) {
        $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users
                SET
                    " . CM_TABLE_PREFIX . "mod_security_users.primary_gid = " . $db->toSql("2", "Number") . "
                WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID_user, "Number");
        $db->execute($sSQL);

        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_rel_groups 
                    ( 
                        uid
                        , gid
                    )
                    VALUES 
                    (
                        " . $db->toSql($ID_user, "Number") . "
                        , " . $db->toSql("2", "Number") . "
                    )";
        $db->execute($sSQL);
    }
    
//    if(check_function("ecommerce_set_anagraph_unic_by_user"))
//        $ID_anagraph = ecommerce_set_anagraph_unic_by_user($ID_user);

	user_to_anagraph($ID_user);
	
    return true;
}


function user_to_anagraph($ID) {
    $db = ffDB_Sql::factory();
   
    if(check_function("analytics"))
        analytics_set_event('/registrazione/social-registration', "Social registration");

    $field_composite = array(
        "smart_url" => array("username_slug")
        , "parent" => array("/")
        , "permalink" => array("/", "username_slug")
        , "meta_title" => array("name", " ", "surname")
        , "meta_description" => array("name", " ", "surname")
    );
    
    if(is_array($ID) && count($ID)) {
        $list_ID = $db->toSql(implode(",", $ID), "Text", false);
    } elseif(int($ID)) {
        $list_ID = $db->toSql($ID, "Number");
    }
    
    if(strlen($list_ID))
    {
        $sSQL = "SELECT *
                    FROM anagraph
                    WHERE 1
                    LIMIT 1";
        $db->query($sSQL);
        if($db->nextRecord()) {
            $arrField = array_fill_keys($db->fields_names, 0);
        }
        $sSQL = "SELECT module_register.ID
                        , module_register.ID_anagraph_type AS ID_anagraph_type
                    FROM module_register
                WHERE 1";
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                $arrAnagraphType[$db->getField("ID", "Number", true)] = $db->getField("ID_anagraph_type", "Number", true);
            } while ($db->nextRecord());
        }
        
        $sSQL = "SELECT *
                    FROM cm_mod_security_users
                    WHERE ID IN (" . $list_ID . " )";
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                $email = $db->getField("email", "Text", true);
                foreach($db->record AS $name => $value)
                {
                    if(array_key_exists($name, $arrField)) {
                        switch ($name) {
                            case "ID":
                                $arrUser[$email]["uid"] = $value;
                                $arrUserField["uid"] = "";
                                break; 
                            case "email":
                                continue;
                                break;
                            case "created":
                                $arrUser[$email][$name] = strtotime($value);
                                $arrUserField[$name] = "";
                                break;
                            case "ID_module_register":
                                $arrUser[$email][$name] = $value;
                                $arrUserField[$name] = "";
                                $arrUser[$email]["ID_type"] = $arrAnagraphType[$value];
                                $arrUserField["ID_type"] = "";
                                break;
                            default:
                                $arrUser[$email][$name] = $value;
                                $arrUserField[$name] = "";
                                break;
                        }
                    }
                }
            } while ($db->nextRecord());
        }
        
        
        
        if(is_array($arrUser) && count($arrUser)) {
            foreach($arrUser AS $email => $value) {
                $sSQL = "SELECT ID
                            FROM anagraph
                            WHERE email = " . $db->toSql($email, "Text");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $stringquery = "";
                    foreach($arrUser[$email] AS $field_name => $value) {
                        if(strlen($stringquery))
                            $stringquery .= ", ";
                        $stringquery .= $field_name . " = " . $db->toSql($value);
                    }
                    
                    if(is_array($field_composite) && count($field_composite)) {
                        foreach($field_composite AS $field_composite_name => $value) {
                            $substring = "";
                            if(is_array($value) && count($value))
                            {
                                foreach($value AS $index => $subvalue) {
                                    if(array_key_exists($subvalue, $arrUserField))
                                        $substring .= $arrUser[$email][$subvalue];
                                    else
                                        $substring .= $subvalue;
                                }
                            }
                            if(strlen($substring))
                            {
                                if(strlen($stringquery))
                                    $stringquery .= ", ";
                                $stringquery .= $field_composite_name . " = " . $db->toSql($substring);
                            }
                        }
                    }
                    if(strlen($stringquery)) {
                        $sSQL = "UPDATE anagraph SET " . 
                                        $stringquery . "
                                    WHERE anagraph.email = " . $db->toSql($email, "Text");
                        $db->execute($sSQL);
                    }
                } else {
                    $stringquery = "";
                    $stringvalue = "";
                    
                    if(is_array($arrUser[$email]) && count($arrUser[$email]))
                    {
                        foreach($arrUser[$email] AS $field_name => $value) {
                            $stringquery .= ", " . $field_name;
                            $stringvalue .= ", " . $db->toSql($value);
                        }
                    }
                    
                    if(is_array($field_composite) && count($field_composite)) {
                        foreach($field_composite AS $field_composite_name => $value) {
                            $substring = "";
                            if(is_array($value) && count($value))
                            {
                                foreach($value AS $index => $subvalue) {
                                    if(array_key_exists($subvalue, $arrUserField))
                                        $substring .= $arrUser[$email][$subvalue];
                                    else
                                        $substring .= $subvalue;
                                }
                            }
                            if(strlen($substring))
                            {
                                $stringquery .= ", " . $field_composite_name;
                                $stringvalue .= ", " . $db->toSql($substring);
                            }
                        }
                    }
                
                    $sSQL = "INSERT INTO anagraph 
                                (
                                    ID
                                    , email
                                    " . $stringquery . "
                                ) VALUES
                                (
                                    null
                                    , " . $db->toSql($email, "Text") . "
                                    " . $stringvalue . "
                                )";
                    $db->execute($sSQL);
                }
            }
        }
    }
}
