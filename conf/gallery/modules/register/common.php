<?php
function MD_register_on_check_after($component, $action) {
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    if($action) {
		$cm->doEvent("md_on_register_check_after", array($component, $action));

	    switch ($action) {
	        case "insert":
	            if (isset($component->form_fields["billcf"]) && isset($component->form_fields["billpiva"])) {
	                if (!$component->form_fields["billcf"]->getValue() && !$component->form_fields["billpiva"]->getValue())
	                    return ffTemplate::_get_word_by_code("piva_or_cf_need");
	            }
	
	            if (!$component->user_vars["disable_account_registration"]) {
	                if(isset($component->form_fields["username"]))
	                {
	                    if (strlen($component->form_fields["username"]->getValue())) {
	                        $sSQL = "SELECT ID FROM anagraph WHERE username = " . $db->toSql($component->form_fields["username"]->value);
	                        $db->query($sSQL);
	                        if ($db->numRows() > 0) {
	                            return ffTemplate::_get_word_by_code("username_not_unic_value");
	                        }
	                    }
	                }
	                if (strlen($component->form_fields["email"]->getValue())) {
	                    $sSQL = "SELECT ID FROM anagraph WHERE email = " . $db->toSql($component->form_fields["email"]->value);
	                    $db->query($sSQL);
	                    if ($db->numRows() > 0) {
	                        return ffTemplate::_get_word_by_code("email_not_unic_value");
	                    }
	                }
	            }
	            break;
        	case "update":
            	if (!$component->user_vars["disable_account_registration"]) {
                	if(isset($component->form_fields["username"]))
                	{
                    	if (strlen($component->form_fields["username"]->getValue())) {
                    	    $sSQL = "SELECT ID FROM anagraph WHERE username = " . $db->toSql($component->form_fields["username"]->value) . " AND ID <> " . $db->toSql($component->key_fields["register-ID"]->value);
                	        $db->query($sSQL);
            	            if ($db->numRows() > 0) {
        	                    return ffTemplate::_get_word_by_code("username_not_unic_value");
    	                    }
	                    }
	                }

	                if (strlen($component->form_fields["email"]->getValue())) {
	                    $sSQL = "SELECT ID FROM anagraph WHERE email = " . $db->toSql($component->form_fields["email"]->value) . " AND ID <> " . $db->toSql($component->key_fields["register-ID"]->value);
	                    $db->query($sSQL);
	                    if ($db->numRows() > 0) {
	                        return ffTemplate::_get_word_by_code("email_not_unic_value");
                    	}
                	}
            	}
           	break;
        	default:
    	} 

	}
    return NULL;
}

function MD_register_on_done_action($component, $action) {
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    if ($action == "insert" || $action == "update") {
        $arrAnagraphUnic = array();
        $arrAnagraphEmail = array();
        $save_data = array();
        $arrFieldReps = $component->user_vars["save_info"];
        $ID_anagraph = $component->key_fields["register-ID"]->getValue();
        if (!$component->user_vars["disable_account_registration"]) {
            $user_field = array();

            if (isset($component->form_fields["username"])) {
                $username = $component->form_fields["username"]->getValue();
                $sSQL = "UPDATE anagraph
                            SET username = " . $db->toSql($username) . "
                            , username_slug = " . $db->toSql(ffCommon_url_rewrite($username)) . "
                            WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                $db->execute($sSQL);
                
            } else {
                $arrUsername = explode("@", $component->form_fields["email"]->getValue());

                $username = $arrUsername[0] . " " . substr($arrUsername[1], 0, strpos($arrUsername[1], "."));
                $username = ucwords(str_replace(array(".", "-", "_"), array(" "), $username));

                $sSQL = "SELECT anagraph.ID
                            FROM anagraph
                            WHERE username_slug = " . $db->toSql(ffCommon_url_rewrite($username));
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $username = $username . "-" . $ID_anagraph;
                }
                $sSQL = "UPDATE anagraph
                            SET username = " . $db->toSql($username) . "
                            , username_slug = " . $db->toSql(ffCommon_url_rewrite($username)) . "
                            WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                $db->execute($sSQL);
            }  
            
            $sSQL = "SELECT anagraph_fields.*
                        FROM anagraph_fields
                        WHERE ID_type = " . $db->toSql($component->user_vars["anagraph_type"], "Number");
            $db->query($sSQL);
            if ($db->nextRecord()) {
                do {
                    $anagraph_field[ffCommon_url_rewrite($db->getField("name", "Text", true))] = array(
                        "data_type" => $db->getField("ID_data_type", "Number", true)
                        , "table" => $db->getField("data_source", "Text", true)
                        , "field" => $db->getField("data_limit", "Text", true)
                        , "ID" => $db->getField("ID", "Number", true)
                    );
                    
                } while ($db->nextRecord());
            }
            
            foreach ($component->form_fields AS $field_key => $field_value) {
                if (isset($field_value->user_vars["name"]) && strlen($field_value->user_vars["name"]) && $field_value->store_in_db == false) {
                    if (array_key_exists(ffCommon_url_rewrite($field_value->user_vars["name"]), $anagraph_field)) {
                        $field_name = ffCommon_url_rewrite($field_value->user_vars["name"]);
                        if($field_value->value->data_type == "Date")
                            $saved_value = $field_value->getValue("Date", FF_SYSTEM_LOCALE);
                        else
                            $saved_value = $field_value->getValue();
                            
                        if ($anagraph_field[$field_name]["data_type"] == 4) {
                            if($arrFieldReps["anagraph_rel_nodes_fields"][$anagraph_field[$field_name]["ID"]] < 2) {
                                $sSQL = "SELECT anagraph_rel_nodes_fields.description
                                            FROM anagraph_rel_nodes_fields
                                            WHERE ID_fields = " . $db->toSql($anagraph_field[$field_name]["ID"], "Number") . "
                                                AND ID_nodes = " . $db->toSql($ID_anagraph, "Number");
                                $db->query($sSQL);
                                if ($db->nextRecord()) {
                                 /*   $string_description = $db->getField("description", "Text", true);
                                    if(strlen($string_description))
                                        $saved_value = $string_description . "," . $saved_value;*/
                                    $sSQL = "UPDATE anagraph_rel_nodes_fields SET
                                                    description = " . $db->toSql($saved_value) . "
                                                WHERE ID_fields = " . $db->toSql($anagraph_field[$field_name]["ID"], "Number") . "
                                                AND ID_nodes = " . $db->toSql($ID_anagraph, "Number");
                                    $db->execute($sSQL);
                                } else {
                                    $sSQL = "INSERT INTO anagraph_rel_nodes_fields
                                                (
                                                    ID
                                                    , ID_nodes
                                                    , ID_fields
                                                    , description
                                                ) VALUES
                                                (
                                                    null
                                                    , " . $db->toSql($ID_anagraph, "Number") . "
                                                    , " . $db->toSql($anagraph_field[$field_name]["ID"]) . "
                                                    , " . $db->toSql($saved_value) . "
                                                )";
                                    $db->execute($sSQL);
                                }
                            } else {
                                if(strlen($save_data["anagraph_rel_nodes_fields"][$anagraph_field[$field_name]["ID"]]))
                                    $save_data["anagraph_rel_nodes_fields"][$anagraph_field[$field_name]["ID"]] .= ",";
                                $save_data["anagraph_rel_nodes_fields"][$anagraph_field[$field_name]["ID"]] .= $saved_value;
                            }  
                        } elseif ($anagraph_field[$field_name]["data_type"] == 16) {
                            if($component->user_vars["save_info"][$anagraph_field[$field_name]["table"]][$anagraph_field[$field_name]["field"]] < 2) {
                            
                                if ($anagraph_field[$field_name]["table"] == "anagraph") {
                                    $sSQL = "SELECT " . $db->toSql($anagraph_field[$field_name]["field"], "Text", false) . "
                                                FROM " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . "
                                                WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                                    $db->query($sSQL);
                                    if ($db->nextRecord()) {
                                       /* $string_description = $db->getField($anagraph_field[$field_name]["field"], "Text", true);
                                        if(strlen($string_description))
                                            $saved_value = $string_description . "," . $saved_value;*/
                                        $sSQL = "UPDATE " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . " SET
                                                         " . $db->toSql($anagraph_field[$field_name]["field"], "Text", false) . " = " . $db->toSql($saved_value) . "
                                                    WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                                        $db->execute($sSQL);

                                    } else {
                                        $sSQL = "INSERT INTO " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . "
                                                    (
                                                        ID
                                                        , ID_anagraph
                                                        , " . $db->toSql($anagraph_field[$field_name]["field"], "Text", false) . "
                                                    ) VALUES
                                                    (
                                                        null
                                                        , " . $db->toSql($ID_anagraph, "Number") . "
                                                        , " . $db->toSql($saved_value) . "
                                                    )";
                                        $db->execute($sSQL);
                                    }
                                } else {
                                    $sSQL = "SELECT ID
                                                FROM " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . "
                                                WHERE ID_anagraph = " . $db->toSql($ID_anagraph, "Number");
                                    $db->query($sSQL);

                                    if ($db->nextRecord()) {
                                        $sSQL = "UPDATE " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . " SET
                                                         " . $db->toSql($anagraph_field[$field_name]["field"], "Text", false) . " = " . $db->toSql($saved_value) . "
                                                    WHERE ID_anagraph = " . $db->toSql($ID_anagraph, "Number");
                                        $db->execute($sSQL);
                                    } else {
                                        $sSQL = "INSERT INTO " . $db->toSql($anagraph_field[$field_name]["table"], "Text", false) . "
                                                    (
                                                        ID
                                                        , ID_anagraph
                                                        , " . $db->toSql($anagraph_field[$field_name]["field"], "Text", false) . "
                                                    ) VALUES
                                                    (
                                                        null
                                                        , " . $db->toSql($ID_anagraph, "Number") . "
                                                        , " . $db->toSql($saved_value) . "
                                                    )";
                                        $db->execute($sSQL);
                                    }
                                }
                            } else {
                                if(strlen($save_data[$anagraph_field[$field_name]["table"]][$anagraph_field[$field_name]["field"]]))
                                    $save_data[$anagraph_field[$field_name]["table"]][$anagraph_field[$field_name]["field"]] .= ",";
                                $save_data[$anagraph_field[$field_name]["table"]][$anagraph_field[$field_name]["field"]] .= $saved_value;
                            }
                        }
        
                        $arrAnagraphUnic[ffCommon_url_rewrite($field_name)] = $saved_value;
                    }
                    

                    if ($field_value->user_vars["enable_in_mail"])
                        $arrAnagraphEmail[ffCommon_url_rewrite($field_name)] = $saved_value;
                }
            }
            foreach($save_data AS $table => $field) {
                foreach($field AS $subkey => $subvalue) {
                    $sSQL = "SELECT " . $db->toSql($subkey, "Text", false) . "
                                FROM " . $db->toSql($table, "Text", false) . "
                                WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                    $db->query($sSQL);
                    if ($db->nextRecord()) {
                        $sSQL = "UPDATE " . $db->toSql($table, "Text", false) . " SET
                                         " . $db->toSql($subkey, "Text", false) . " = " . $db->toSql($subvalue, "Text") . "
                                    WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                        $db->execute($sSQL);

                    } else {
                        $sSQL = "INSERT INTO " . $db->toSql($table, "Text", false) . "
                                    (
                                        ID
                                        , ID_anagraph
                                        , " . $db->toSql($subkey, "Text", false) . "
                                    ) VALUES
                                    (
                                        null
                                        , " . $db->toSql($ID_anagraph, "Number") . "
                                        , " . $db->toSql($subvalue) . "
                                    )";
                        $db->execute($sSQL);
                    }
                        
                
                }
            }
            
            if (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
                $check_shipping = true;
            } else {
                if (isset($component->form_fields["billstate"]) && strlen($component->form_fields["billstate"]->getValue())) {
                    $check_shipping = true;
                } else {
                    $check_shipping = false;
                }
            }

            if ($check_shipping) {
                if (!isset($component->form_fields["shippingreference"]) && isset($component->form_fields["billreference"]) && strlen($component->form_fields["billreference"]))
                    $shippingData[] = " shippingreference = " . $db->toSql($component->form_fields["billreference"]->value);
                if (!isset($component->form_fields["shippingaddress"]) && isset($component->form_fields["billaddress"]) && strlen($component->form_fields["billaddress"]))
                    $shippingData[] = " shippingaddress = " . $db->toSql($component->form_fields["billaddress"]->value);
                if (!isset($component->form_fields["shippingcap"]) && isset($component->form_fields["billcap"]) && strlen($component->form_fields["billcap"]))
                    $shippingData[] = " shippingcap = " . $db->toSql($component->form_fields["billcap"]->value);
                if (!isset($component->form_fields["shippingtown"]) && isset($component->form_fields["billtown"]) && strlen($component->form_fields["billtown"]))
                    $shippingData[] = " shippingtown = " . $db->toSql($component->form_fields["billtown"]->value);
                if (!isset($component->form_fields["shippingprovince"]) && isset($component->form_fields["billprovince"]) && strlen($component->form_fields["billprovince"]))
                    $shippingData[] = " shippingprovince = " . $db->toSql($component->form_fields["billprovince"]->value);
                if (!isset($component->form_fields["shippingstate"]) && isset($component->form_fields["billstate"]) && strlen($component->form_fields["billstate"]))
                    $shippingData[] = " shippingstate = " . $db->toSql($component->form_fields["billstate"]->value);

                if (is_array($shippingData) && count($shippingData)) {
                    $sSQL_update = implode(",", $shippingData);
                    $sSQL = "UPDATE anagraph
                                SET " . $sSQL_update . "
                                WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                    $db->execute($sSQL);
                }
            }
            
            $res = $cm->doEvent("vg_on_register_action_done", array(&$component, $action));
            
            if (check_function("ecommerce_set_user_by_anagraph")) {
                $uid = ecommerce_set_user_by_anagraph($ID_anagraph, $component->user_vars["anagraph_type"], $arrAnagraphUnic); 
            }
        }
    }
    
    switch ($action) {
        case "insert":
            $ID_register = $component->user_vars["ID_register"];
            if (isset($component->user_vars["smart_url"])) {
                $smart_url_string = "";
                foreach ($component->user_vars["smart_url"] AS $key => $value) {
					if(isset($component->form_fields[$value])) {
	                    if (strlen($smart_url_string))
	                        $smart_url_string .= " ";
	                    $smart_url_string .= $component->form_fields[$value]->getValue();
					}
                }
                $parent = "/";
                if (strlen($smart_url_string)) {
                    $sSQL = "SELECT ID
                                FROM anagraph
                                WHERE smart_url = " . $db->toSql(ffCommon_url_rewrite($smart_url_string));
                    if($db->nextRecord()) {
                        $smart_url_string .= " " . $db->getField("ID", "Number", true);
                    }
                    if($parent == "/")
                        $permalink = $parent . ffCommon_url_rewrite($smart_url_string);
                    else
                        $permalink = $parent . "/" . ffCommon_url_rewrite($smart_url_string);
                    $sSQL = "UPDATE anagraph SET
                                meta_title = " . $db->toSql($smart_url_string, "Text") . "
                                , smart_url = " . $db->toSql(ffCommon_url_rewrite($smart_url_string), "Text") . "
                                , parent = " . $db->toSql($parent, "Text") . "
                                , permalink = " . $db->toSql($permalink, "Text") . "
                                WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                    $db->execute($sSQL);
                }
            }

            if (isset($component->user_vars["meta_description"])) {
                $meta_description_string = "";
                foreach ($component->user_vars["meta_description"] AS $key => $value) {
                    if (strlen($meta_description_string))
                        $meta_description_string .= " ";
                    $meta_description_string .= $component->form_fields[$value]->getValue();
                }
                if (strlen($meta_description_string)) { 
                    $sSQL = "UPDATE anagraph SET
                                meta_description = " . $db->toSql($meta_description_string, "Text") . "
                                WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                    $db->execute($sSQL);
                }
            }

            if (!$component->user_vars["disable_account_registration"]) {
                $primary_gid = $component->additional_fields["primary_gid"]->getValue();
                $email = $component->form_fields["email"]->getValue();
                
                
                $activation = $component->user_vars["activation"];

                //$enable_ecommerce_data = $component->user_vars["enable_ecommerce_data"];
                //$enable_bill_data = $component->user_vars["enable_bill_data"];
                $enable_general_data = $component->user_vars["enable_general_data"];

                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_rel_groups 
                            ( 
                                SELECT DISTINCT uid, gid
                                FROM 
                                (                        
                                    (
                                        SELECT " . $db->toSql($uid, "Number") . " AS uid, gid 
                                        FROM module_register_rel_gid 
                                        WHERE module_register_rel_gid.ID_module_register = " . $db->toSql($ID_register, "Number") . " 
                                            AND module_register_rel_gid.value = '1'
                                    )
                                    UNION 
                                    (
                                        SELECT " . $db->toSql($uid, "Number") . " AS uid, " . $db->toSql($primary_gid, "Number") . " AS gid 
                                    )
                                ) AS tbl_src
                            )";
                $db->execute($sSQL);

                $sSQL = "INSERT INTO users_rel_module_form 
                            ( 
                                SELECT 
                                    '' AS ID
                                    , " . $db->toSql($uid, "Number") . " AS uid
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
                            (ID, uid, ID_nodes, visible, cascading, request, `order`)
                            ( 
                                SELECT 
                                    '' AS ID
                                    , " . $db->toSql($uid, "Number") . " AS uid
                                    , module_register_rel_vgallery.ID_vgallery_nodes  AS ID_nodes
                                    , module_register_rel_vgallery.visible  AS visible
                                    , module_register_rel_vgallery.cascading  AS cascading
                                    , module_register_rel_vgallery.request  AS request
                                    , module_register_rel_vgallery.`order`  AS `order`
                                FROM module_register_rel_vgallery
                                WHERE 
                                    module_register_rel_vgallery.ID_module_register = " . $db->toSql($ID_register, "Number") . " 
                                ORDER BY `order`
                            )";
                $db->execute($sSQL);

                $to[0]["name"] = $username;
                $to[0]["mail"] = $email;

                /* BASE INFORMATION */
                if ($enable_general_data) {
                    if (ENABLE_AVATAR_SYSTEM && isset($component->form_fields["avatar"]) && check_function("get_user_avatar")) {
                        $fields_registration["account"]["avatar"] = get_user_avatar($component->form_fields["avatar"]->getValue(), true, $component->form_fields["email"]->getValue(), cm_showfiles_get_abs_url('/avatar'));
                    }
                    if (isset($component->form_fields["name"]))
                        $fields_registration["account"]["name"] = $component->form_fields["name"]->getValue();
                    if (isset($component->form_fields["surname"]))
                        $fields_registration["account"]["surname"] = $component->form_fields["surname"]->getValue();
                    if (isset($component->form_fields["email"]))
                        $fields_registration["account"]["email"] = $component->form_fields["email"]->getValue();
                    if (isset($component->form_fields["tel"]))
                        $fields_registration["account"]["tel"] = $component->form_fields["tel"]->getValue();
                } else {
                    if (isset($component->form_fields["email"]))
                        $fields_registration["account"]["email"] = $component->form_fields["email"]->getValue();
                }

                $fields_registration["account"] = array_merge($fields_registration["account"], $arrAnagraphEmail);

                /* BILL INFORMATION */
                if (isset($component->form_fields["billpiva"]) && strlen($component->form_fields["billpiva"]->getValue()))
                    $fields_registration["account"]["billpiva"] = $component->form_fields["billpiva"]->getValue();
                if (isset($component->form_fields["billcf"]) && strlen($component->form_fields["billcf"]->getValue()))
                    $fields_registration["account"]["billcf"] = $component->form_fields["billcf"]->getValue();
                if (isset($component->form_fields["billreference"]) && strlen($component->form_fields["billreference"]->getValue()))
                    $fields_registration["account"]["billreference"] = $component->form_fields["billreference"]->getValue();
                if (isset($component->form_fields["billaddress"]) && strlen($component->form_fields["billaddress"]->getValue()))
                    $fields_registration["account"]["billaddress"] = $component->form_fields["billaddress"]->getValue();
                if (isset($component->form_fields["billcap"]) && strlen($component->form_fields["billcap"]->getValue()))
                    $fields_registration["account"]["billcap"] = $component->form_fields["billcap"]->getValue();
                if (isset($component->form_fields["billtown"]) && strlen($component->form_fields["billtown"]->getValue()))
                    $fields_registration["account"]["billtown"] = $component->form_fields["billtown"]->getValue();
                if (isset($component->form_fields["billprovince"]) && strlen($component->form_fields["billprovince"]->getValue()))
                    $fields_registration["account"]["billprovince"] = $component->form_fields["billprovince"]->getValue();
                if (isset($component->form_fields["billstate"]) && strlen($component->form_fields["billstate"]->getValue())) {
                    if (!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
                        $sSQL = "SELECT 
                                    IFNULL(
                                        (SELECT " . FF_PREFIX . "international.description
                                            FROM " . FF_PREFIX . "international
                                            WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
                                                AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
                                        )
                                        , " . FF_SUPPORT_PREFIX . "state.name
                                    ) AS description
                                FROM " . FF_SUPPORT_PREFIX . "state 
                                WHERE 
                                    " . FF_SUPPORT_PREFIX . "state.ID = " . (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0 ? AREA_ECOMMERCE_SHIPPING_LIMIT_STATE : $db->toSql($component->form_fields["billstate"]->value)) . "
                                ORDER BY description";
                        $db->query($sSQL);
                        if ($db->nextRecord()) {
                            $fields_registration["account"]["billstate"] = $db->getField("description")->getValue();
                        }
                    }
                }

                /* SHIPPING INFORMATION */
                if (isset($component->form_fields["shippingreference"]) && strlen($component->form_fields["shippingreference"]->getValue()))
                    $fields_registration["account"]["shippingreference"] = $component->form_fields["shippingreference"]->getValue();
                if (isset($component->form_fields["shippingaddress"]) && strlen($component->form_fields["shippingaddress"]->getValue()))
                    $fields_registration["account"]["shippingaddress"] = $component->form_fields["shippingaddress"]->getValue();
                if (isset($component->form_fields["shippingcap"]) && strlen($component->form_fields["shippingcap"]->getValue()))
                    $fields_registration["account"]["shippingcap"] = $component->form_fields["shippingcap"]->getValue();
                if (isset($component->form_fields["shippingtown"]) && strlen($component->form_fields["shippingtown"]->getValue()))
                    $fields_registration["account"]["shippingtown"] = $component->form_fields["shippingtown"]->getValue();
                if (isset($component->form_fields["shippingprovince"]) && strlen($component->form_fields["shippingprovince"]->getValue()))
                    $fields_registration["account"]["shippingprovince"] = $component->form_fields["shippingprovince"]->getValue();
                if (isset($component->form_fields["shippingstate"]) && strlen($component->form_fields["shippingstate"]->getValue())) {
                    if (!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
                        $sSQL = "SELECT IFNULL(
                                            (SELECT " . FF_PREFIX . "international.description
                                                    FROM " . FF_PREFIX . "international
                                                    WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
                                                            AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                            AND " . FF_PREFIX . "international.is_new = 0
                                                    ORDER BY " . FF_PREFIX . "international.description
                                                    LIMIT 1
                                            )
                                            , " . FF_SUPPORT_PREFIX . "state.name
                                        ) AS description
                                    FROM " . FF_SUPPORT_PREFIX . "state 
                                    WHERE " . FF_SUPPORT_PREFIX . "state.ID = " . (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0 ? AREA_ECOMMERCE_SHIPPING_LIMIT_STATE : $db->toSql($component->form_fields["shippingstate"]->value)) . "
                                    ORDER BY description";
                        $db->query($sSQL);
                        if ($db->nextRecord()) {
                            $fields_registration["account"]["shippingstate"] = $db->getField("description")->getValue();
                        }
                    }
                }

                $to_active[0]["name"] = $username;
                $to_active[0]["mail"] = $email;
                
                if (is_array($to_active) && count($to_active) > 0) {
                    $rnd_active = mod_sec_createRandomPassword();

                    $sSQL = "UPDATE anagraph SET active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") WHERE ID = " . $db->toSql($ID_anagraph, "Number");
                    $db->execute($sSQL);
                    
                    /**                                               
                    * TODO:Da togliere gestione utente e fonderla con anagraph
                    */ 
                    if(1) {
                        $sSQL = "UPDATE cm_mod_security_users SET active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") WHERE ID = " . $db->toSql($uid, "Number");
                        $db->execute($sSQL);
                    }

                    $fields_activation["activation"]["username"] = $username;
                    $fields_activation = array_merge($fields_activation, $fields_registration);
                    
                    $fields_activation["activation"]["link"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH . VG_SITE_MOD_SEC_ACTIVATION . "?frmAction=activation&sid=" . urlencode($rnd_active);
                    
                    
                    if (check_function("process_mail")) {
                        if (!$component->user_vars["ID_email_activation"] > 0) {
                            $struct_email = email_system("account activation");
                            $component->user_vars["ID_email_activation"] = $struct_email["ID"];

                            $sSQL = "UPDATE module_register SET 
                                                    module_register.ID_email_activation = " . $db->toSql($component->user_vars["ID_email_activation"], "Number") . " 
                                            WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
                            $db->execute($sSQL);
                        }

                        switch ($activation) {
                            case 1:
                                $rc_activation = process_mail($component->user_vars["ID_email_activation"], $to_active, NULL, NULL, $fields_activation);
                                break;
                            case 2:
                                $to_active_waiting[0]["name"] = $username;
                                $to_active_waiting[0]["mail"] = $email;

                                $rc_activation = process_mail($component->user_vars["ID_email_activation"], $to_active, NULL, NULL, $fields_activation, null, null, null, false, true, true);
                                break;
                            case 4:
                                $rc_activation = process_mail($component->user_vars["ID_email_activation"], $to_active, NULL, NULL, $fields_activation);
                                $rc_activation = process_mail($component->user_vars["ID_email_activation"], $to_active, NULL, NULL, $fields_activation, null, null, null, false, true, true);
                                break;
                            default:
                        }
                        
                        if (!$rc_activation)
                            $rc_activation = 0;

                        if (is_array($to_active_waiting) && count($to_active_waiting)) {
                            $fields_activation_waiting["activation"]["username"] = $username;
                            $fields_activation_waiting = array_merge($fields_activation_waiting, $fields_registration);
                            $fields_activation_waiting["activation"]["waitingfor"] = ffTemplate::_get_word_by_code("activation_waitingfor_description");

                            $rc_activation = process_mail($component->user_vars["ID_email_activation"], $to_active_waiting, NULL, NULL, $fields_activation_waiting);
                            if (!$rc_activation)
                                $rc_activation = 0;
                        }
                    }

                    $strMailNotify = "ma=" . urlencode($rc_activation);
                } else {
                    $to[0]["name"] = $username;
                    $to[0]["mail"] = $email;

                    $fields["account"]["username"] = $username;
                    $fields["account"]["password"] = $component->form_fields["password"]->getValue();

                    $fields = array_merge($fields, $fields_registration);

                    if (check_function("process_mail")) {
                        if (!$component->user_vars["ID_email"] > 0) {
                            $component->user_vars["ID_email"] = email_system("account registration");
                            $sSQL = "UPDATE module_register SET 
                                                    module_register.ID_email_activation = " . $db->toSql($component->user_vars["ID_email"], "Number") . " 
                                            WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
                            $db->execute($sSQL);
                        }
                        $rc_from_account = process_mail($component->user_vars["ID_email"], $to, NULL, NULL, $fields, null, null, null, false, true, true);
                        $rc_account = process_mail($component->user_vars["ID_email"], $to, NULL, NULL, $fields);
                    }

                    if (!$rc_account)
                        $rc_account = 0;

                    $strMailNotify = "mc=" . urlencode($rc_account);
                }

                //set_session("temp_UserNID", $uid);
            } else {
                $sSQL = "SELECT vgallery_nodes.* 
                                        , module_register_rel_vgallery.visible AS register_visible
                                        , module_register_rel_vgallery.cascading AS register_is_dir
                                        , module_register_rel_vgallery.order AS register_order
                                        , vgallery.limit_type AS limit_type
                                        , vgallery.name AS vgallery_name
                                FROM module_register_rel_vgallery
                                        INNER JOIN vgallery_nodes ON vgallery_nodes.ID = module_register_rel_vgallery.ID_vgallery_nodes
                                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                WHERE module_register_rel_vgallery.ID_module_register = " . $db->toSql($ID_register, "Number") . "
                                        AND module_register_rel_vgallery.request > 0";
                $db->query($sSQL);
                if ($db->nextRecord()) {
                    do {
                        $ID_vgallery_parent = $db->getField("ID", "Number", true);

                        $vgallery[$ID_vgallery_parent]["limit_type"] = $db->getField("limit_type", "Text", true);
                        $vgallery[$ID_vgallery_parent]["visible"] = $db->getField("register_visible", "Number", true);
                        $vgallery[$ID_vgallery_parent]["is_dir"] = $db->getField("register_is_dir", "Number", true);
                        $vgallery[$ID_vgallery_parent]["order"] = $db->getField("register_order", "Number", true);
                        $vgallery[$ID_vgallery_parent]["ID_vgallery"] = $db->getField("ID_vgallery", "Number", true);
                        $vgallery[$ID_vgallery_parent]["vgallery_name"] = $db->getField("vgallery_name", "Text", true);
                        $vgallery[$ID_vgallery_parent]["parent"] = stripslash($db->getField("parent", "Text", true)) . "/" . $db->getField("name", "Text", true);
                        $vgallery[$ID_vgallery_parent]["ID_vgallery"] = $db->getField("ID_vgallery", "Number", true);
                        $vgallery[$ID_vgallery_parent]["ID_domain"] = $db->getField("ID_domain", "Number", true);
                    } while ($db->nextRecord());

                    if (is_array($vgallery) && count($vgallery)) {
                        $sSQL = "SELECT " . FF_PREFIX . "languages.ID
                                        , " . FF_PREFIX . "languages.code 
                                    FROM " . FF_PREFIX . "languages 
                                    WHERE " . FF_PREFIX . "languages.status = '1'
                                        AND " . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_INSET);
                        $db->query($sSQL);
                        if ($db->nextRecord()) {
                            $arrLang["ID"] = $db->getField("ID", "Number", true);
                            $arrLang["code"] = $db->getField("code", "Text", true);
                        }

                        foreach ($vgallery AS $vgallery_key => $vgallery_value) {
                            $vgallery_type = ($vgallery_value["is_dir"] ? "dir" : "node");
                            $default_field_type = 0;

                            $sSQL = "SELECT vgallery_type.* 
                                    FROM vgallery_type 
                                    WHERE " . (strlen($vgallery_value["limit_type"]) ? " vgallery_type.ID IN (" . $db->tosql($vgallery_value["limit_type"], "Text", false) . ") " : " 1 ") . "
                                    ORDER BY vgallery_type.ID";
                            $db->query($sSQL);
                            if ($db->nextRecord()) {
                                do {
                                    if ($db->getField("is_dir_default", "Number", true))
                                        $arrAllowedType["dir"][$db->getField("name", "Number", true)] = $db->getField("ID", "Number", true);
                                    else
                                        $arrAllowedType["node"][$db->getField("name", "Number", true)] = $db->getField("ID", "Number", true);
                                } while ($db->nextRecord());

                                if (is_array($arrAllowedType[$vgallery_type]) && count($arrAllowedType[$vgallery_type]) == 1) {
                                    $default_field_type = current($arrAllowedType[$vgallery_type]);
                                }
                            }

                            if ($default_field_type > 0) {
                                $sSQL = "SELECT vgallery_fields.* 
                                                , extended_type.name AS extended_type
                                            FROM vgallery_fields
                                                INNER JOIN extended_type ON extended_type.ID = vgallery_fields.ID_extended_type
                                            WHERE vgallery_fields.ID_type = " . $db->toSql($default_field_type);
                                $db->query($sSQL);
                                if ($db->nextRecord()) {
                                    do {
                                        $field = ffCommon_url_rewrite($db->getField("name", "Text", true));

                                        $vgallery[$vgallery_key]["fields"][$field]["ID"] = $db->getField("ID", "Number", true);
                                        $vgallery[$vgallery_key]["fields"][$field]["name"] = $db->getField("name", "Text", true);
                                        $vgallery[$vgallery_key]["fields"][$field]["extended_type"] = $db->getField("extended_type", "Text", true);
                                        $vgallery[$vgallery_key]["fields"][$field]["smart_url"] = $db->getField("enable_smart_url", "Number", true);
                                        $vgallery[$vgallery_key]["fields"][$field]["meta_description"] = $db->getField("meta_description", "Number", true);
                                    } while ($db->nextRecord());

                                    if (is_array($vgallery[$vgallery_key]["fields"]) && count($vgallery[$vgallery_key]["fields"])) {
                                        $arrSmartUrl = array();
                                        $arrMetaDescription = array();

                                        foreach ($component->form_fields AS $field_key => $field_value) {
                                            if ($field_value->store_in_db == false && isset($field_value->user_vars["name"]) && strlen($field_value->user_vars["name"]) && array_key_exists(ffCommon_url_rewrite($field_value->user_vars["name"]), $vgallery[$vgallery_key]["fields"])
                                            ) {
                                                $real_field_key = ffCommon_url_rewrite($field_value->user_vars["name"]);
                                                $vgallery[$vgallery_key]["match_fields"][$real_field_key] = $vgallery[$vgallery_key]["fields"][$real_field_key];
                                                $vgallery[$vgallery_key]["match_fields"][$real_field_key]["value"] = $component->form_fields[$field_key]->getValue();

                                                if (strlen($vgallery[$vgallery_key]["match_fields"][$real_field_key]["value"])) {
                                                    if ($vgallery[$vgallery_key]["match_fields"][$real_field_key]["meta_description"] > 0) {
                                                        if (strlen($arrMetaDescription[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["meta_description"]])) {
                                                            $arrMetaDescription[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["meta_description"]] .= " ";
                                                        }
                                                        $arrMetaDescription[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["meta_description"]] .= $vgallery[$vgallery_key]["match_fields"][$real_field_key]["value"];
                                                    }

                                                    if ($vgallery[$vgallery_key]["match_fields"][$real_field_key]["smart_url"] > 0) {
                                                        if (strlen($arrSmartUrl[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["smart_url"]])) {
                                                            $arrSmartUrl[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["smart_url"]] .= " ";
                                                        }
                                                        $arrSmartUrl[$vgallery[$vgallery_key]["match_fields"][$real_field_key]["smart_url"]] .= $vgallery[$vgallery_key]["match_fields"][$real_field_key]["value"];
                                                    }
                                                }
                                            }
                                        }

                                        if (is_array($vgallery[$vgallery_key]["match_fields"]) && count($vgallery[$vgallery_key]["match_fields"])) {
                                            $smart_url = "";
                                            if (count($arrSmartUrl)) {
                                                ksort($arrSmartUrl);
                                                $smart_url = implode(" ", $arrSmartUrl);
                                            }
                                            $meta_description = null;
                                            if (count($arrMetaDescription)) {
                                                ksort($arrMetaDescription);
                                                $meta_description["new"] = implode(" ", $arrMetaDescription);
                                                $meta_description["ori"] = implode(" ", $arrMetaDescription);
                                            }
                                            if (strlen($smart_url)) {
                                                $sSQL = "SELECT vgallery_nodes.* 
                                                            FROM vgallery_nodes 
                                                            WHERE vgallery_nodes.name = " . $db->toSql(ffCommon_url_rewrite($smart_url)) . "
                                                                    AND vgallery_nodes.parent = " . $db->toSql($vgallery_value["parent"]);
                                                $db->query($sSQL);
                                                if ($db->nextRecord()) {
                                                    $component->tplDisplayError(ffTemplate::_get_word_by_code("register_" . $vgallery_value["vgallery_name"] . "_not_unic"));
                                                    return true;
                                                } else {
                                                    $sSQL = "INSERT INTO vgallery_nodes
                                                                (
                                                                        `ID`
                                                                        , `ID_vgallery`
                                                                        , `name`
                                                                        , `order`
                                                                        , `parent`
                                                                        , `ID_type`
                                                                        , `is_dir`
                                                                        , `last_update`
                                                                        , `owner`
                                                                        , `visible`
                                                                        , `ID_domain`
                                                                        , `ID_module_register`
                                                                )
                                                                VALUES
                                                                (
                                                                        null
                                                                        , " . $db->toSql($vgallery_value["ID_vgallery"], "Number") . "
                                                                        , " . $db->toSql(ffCommon_url_rewrite($smart_url)) . "
                                                                        , " . $db->toSql($vgallery_value["order"]) . "
                                                                        , " . $db->toSql($vgallery_value["parent"]) . "
                                                                        , " . $db->toSql($default_field_type, "Number") . "
                                                                        , " . $db->toSql($vgallery_value["is_dir"]) . "
                                                                        , " . $db->toSql(time()) . "
                                                                        , " . $db->toSql("-1") . "
                                                                        , " . $db->toSql($vgallery_value["visible"]) . "
                                                                        , " . $db->toSql($vgallery_value["ID_domain"]) . "
                                                                        , " . $db->toSql($ID_register, "Number") . "
                                                                )";
                                                    $db->execute($sSQL);
                                                    $ID_node = $db->getInsertID();
                                                    foreach ($vgallery[$vgallery_key]["match_fields"] AS $field_key => $field_value) {
                                                        if ($field_value["extended_type"] == "Image" || $field_value["extended_type"] == "Upload" || $field_value["extended_type"] == "UploadImage"
                                                        ) {
                                                            if (is_file(DISK_UPDIR . "/users/" . $field_value["value"]) && check_function("fs_operation")) {
                                                                full_copy(DISK_UPDIR . "/users/" . $field_value["value"], DISK_UPDIR . stripslash($vgallery_value["parent"]) . "/" . ffCommon_url_rewrite($smart_url) . "/" . $field_value["value"], true);
                                                                $field_value["value"] = stripslash($vgallery_value["parent"]) . "/" . ffCommon_url_rewrite($smart_url) . "/" . $field_value["value"];
                                                            }
                                                        }

                                                        $sSQL = "INSERT INTO vgallery_rel_nodes_fields
                                                                    (
                                                                            ID
                                                                            , description
                                                                            , ID_fields
                                                                            , ID_nodes
                                                                            , ID_lang
                                                                    )
                                                                    VALUES
                                                                    (
                                                                            null
                                                                            , " . $db->toSql($field_value["value"]) . "
                                                                            , " . $db->toSql($field_value["ID"]) . "
                                                                            , " . $db->toSql($ID_node) . "
                                                                            , " . $db->toSql($arrLang["ID"], "Number") . "
                                                                    )";
                                                        $db->execute($sSQL);
                                                    }


                                                    $sSQL = "INSERT INTO  
                                                            `vgallery_rel_nodes_fields` 
                                                        ( 
                                                            `ID` , 
                                                            `description` , 
                                                            `ID_fields` , 
                                                            `ID_nodes` , 
                                                            `ID_lang`
                                                        )
                                                        VALUES
                                                        (
                                                            ''
                                                            , " . $db->toSql($vgallery_value["visible"]) . "
                                                            , (SELECT ID FROM vgallery_fields WHERE vgallery_fields.name = " . $db->toSql("visible") . ")  
                                                            , " . $db->toSql($ID_node) . "
                                                            , " . $db->toSql($arrLang["ID"], "Number") . "
                                                        )";
                                                    $db->execute($sSQL);

                                                    if (check_function("update_vgallery_seo"))
                                                        $seo_update = update_vgallery_seo($smart_url, $ID_node->getValue(), $arrLang["ID"], $meta_description, $vgallery_value["parent"]);
                                                    if (check_function("refresh_cache")) {
                                                        refresh_cache("V", $ID_node->getValue(), $action, stripslash($vgallery_value["parent"]) . "/" . ffCommon_url_rewrite($smart_url));
                                                    }

                                                    //remove cache of relationship
                                                    if ($ID_node->getValue() > 0) {
                                                        $sSQL = "UPDATE vgallery_rel_nodes_fields SET 
                                                            `nodes` = ''
                                                                    WHERE vgallery_rel_nodes_fields.`nodes` <> ''
                                                            AND FIND_IN_SET(" . $db->toSql($ID_node->getValue(), "Number") . ", vgallery_rel_nodes_fields.`nodes`)";
                                                        $db->execute($sSQL);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //set_session("temp_VGalleryNID", $ID_node->getValue());
            }

            $res = $cm->doEvent("vg_on_register_done", array($component));

            if (!isset($component->user_vars["disable_ret_url"])) {
                if (!$component->user_vars["disable_account_registration"]) {
                    if (check_function("check_user_request"))
                        $additionaldata = check_user_form_request(array("ID" => $uid));
                }

                if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                    if ($additionaldata) {
                        $component->json_result["url"] = FF_SITE_PATH . VG_SITE_NOTIFY . "/register/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($component->ret_url);
                    } else {
                        $component->json_result["url"] = $component->ret_url;
                    }
                    $component->json_result["close"] = false;
                    die(ffCommon_jsonenc($component->json_result, true));
                } else {
                    if ($additionaldata) {
                        $component->redirect(FF_SITE_PATH . VG_SITE_NOTIFY . "/register/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($component->ret_url));
                    } else {
                        $component->redirect($component->ret_url . "?ret_url=" . urlencode($component->parent[0]->getRequestUri()));
                    }
                }
            }
            break;
            case "update":
                if (!isset($component->user_vars["disable_ret_url"])) {
                    if (!$component->user_vars["disable_account_registration"]) {
                        if (check_function("check_user_request"))
                            $additionaldata = check_user_form_request(array("ID" => $uid));
                    }

                    if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                        if ($additionaldata) {
                            $component->json_result["url"] = FF_SITE_PATH . VG_SITE_NOTIFY . "/register/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($component->ret_url);
                        } else {
                            $component->json_result["url"] = $component->ret_url;
                        }
                        $component->json_result["close"] = false;
                        die(ffCommon_jsonenc($component->json_result, true));
                    } else {
                        if ($additionaldata) {
                            $component->redirect(FF_SITE_PATH . VG_SITE_NOTIFY . "/register/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($component->ret_url));
                        } else {
                            $component->redirect($component->ret_url);
                        }
                    }
                }
                break;
            default:
    }
}

function MD_register_clone($ID_register, $register_new_name = "") {
    $db = ffDB_Sql::factory();

    $addit_name = ffTemplate::_get_word_by_code("register_clone");
    $addit_smart_url = ffCommon_url_rewrite($addit_name);

    if (strlen($register_new_name)) {
        $register_new_smart_url = ffCommon_url_rewrite($register_new_name);
        $sSQL = "SELECT module_register.*
                    , module_register.name AS display_name
                FROM module_register
                WHERE module_register.name LIKE " . $db->toSql($register_new_smart_url . "%") . "
                ORDER BY LENGTH(module_register.name) DESC";
        $db->query($sSQL);
        if ($db->nextRecord()) {
            if (strpos($db->getField("name", "Text", true), "-" . $addit_smart_url) !== false) {
                $tmpCountCloneSmartUrl = explode("-" . $addit_smart_url, ffCommon_url_rewrite($db->getField("name", "Text", true)));
                $tmpCountCloneName = explode(" " . $addit_name, $db->getField("display_name", "Text", true));

                $countClone = $db->numRows();

                $register_new_smart_url = $tmpCountCloneSmartUrl[0] . "-" . $addit_smart_url . $countClone;
                $register_new_name = $tmpCountCloneName[0] . " " . $addit_name . $countClone;
            } else {
                $register_new_smart_url = $register_new_smart_url . "-" . $addit_smart_url;
                $register_new_name = $register_new_name . " " . $addit_name;
            }
        }
    }

    $sSQL = "SELECT module_register.*
                 , module_register.name AS display_name
            FROM module_register
            WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
    $db->query($sSQL);
    if ($db->nextRecord()) {
        $register_old_name = $db->getField("display_name", "Text", true);
        $register_old_smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));

        if ($register_new_smart_url == $register_old_smart_url) {
            $register_new_smart_url = $register_new_smart_url . "-" . $addit_smart_url;
            $register_new_name = $register_new_name . " " . $addit_name;
        } elseif (strpos($register_old_smart_url, $register_new_smart_url . "-" . $addit_smart_url) === 0) {
            $countClone = str_replace($register_new_smart_url . "-" . $addit_smart_url, "", $register_old_smart_url);

            if (!$countClone)
                $countClone = 2;
            else
                $countClone = ((int) $countClone) + 1;

            $register_new_smart_url = $register_new_smart_url . "-" . $addit_smart_url . $countClone;
            $register_new_name = $register_new_name . " " . $addit_name . $countClone;
        }

        if (!strlen($register_new_smart_url)) {
            if (strpos($register_old_smart_url, "-" . $addit_smart_url) !== false) {
                $tmpCountCloneSmartUrl = explode("-" . $addit_smart_url, $register_old_smart_url);
                $tmpCountCloneName = explode(" " . $addit_name, $register_old_name);

                if (!$tmpCountCloneSmartUrl[1])
                    $countClone = 2;
                else
                    $countClone = ((int) $tmpCountCloneSmartUrl[1]) + 1;

                $register_new_smart_url = $tmpCountCloneSmartUrl[0] . "-" . $addit_smart_url . $countClone;
                $register_new_name = $tmpCountCloneName[0] . " " . $addit_name . $countClone;
            } else {
                $register_new_smart_url = $register_old_smart_url . "-" . $addit_smart_url;
                $register_new_name = $register_old_name . " " . $addit_name;
            }
        }

        $sSQL = "INSERT INTO module_register
                (
                    `ID`
                    , `name`
                    , `ID_anagraph_type`
                    , `ID_email`
                    , `ID_email_activation`
                    , `activation`
                    , `default`
                    , `disable_account_registration`
                    , `display_view_mode`
                    , `enable_bill_data`
                    , `enable_default_tip`
                    , `enable_ecommerce_data`
                    , `enable_general_data`
                    , `enable_manage_account`
                    , `enable_newsletter`
                    , `enable_privacy`
                    , `enable_require_note`
                    , `enable_setting_data`
                    , `enable_user_menu`
                    , `fixed_post_content`
                    , `fixed_pre_content`
                    , `force_redirect`
                    , `generate_password`
                    , `primary_gid`
                    , `public`
                    , `show_title`
                    , `simple_registration`
                    , `default_placeholder`
                    , `default_show_label`
                )
                SELECT 
                    null
                    , " . $db->toSql($register_new_smart_url) . "
                    , `ID_anagraph_type`
                    , `ID_email`
                    , `ID_email_activation`
                    , `activation`
                    , `default`
                    , `disable_account_registration`
                    , `display_view_mode`
                    , `enable_bill_data`
                    , `enable_default_tip`
                    , `enable_ecommerce_data`
                    , `enable_general_data`
                    , `enable_manage_account`
                    , `enable_newsletter`
                    , `enable_privacy`
                    , `enable_require_note`
                    , `enable_setting_data`
                    , `enable_user_menu`
                    , `fixed_post_content`
                    , `fixed_pre_content`
                    , `force_redirect`
                    , `generate_password`
                    , `primary_gid`
                    , `public`
                    , `show_title`
                    , `simple_registration`
                    , `default_placeholder`
                    , `default_show_label`
                FROM module_register
                WHERE module_register.ID = " . $db->toSql($ID_register, "Number");
        $db->execute($sSQL);
        $ID_new_register = $db->getInsertID(true);
        if ($ID_new_register > 0) {
            $sSQL = "SELECT module_register_fields.*
                    FROM module_register_fields
                    WHERE module_register_fields.ID_module = " . $db->toSql($ID_register, "Number") . "
                    ORDER BY module_register_fields.ID";
            $db->query($sSQL);
            if ($db->nextRecord()) {
                do {
                    $arrRegisterField[] = $db->getField("ID", "Number", true);
                } while ($db->nextRecord());
            }

            if (is_array($arrRegisterField) && count($arrRegisterField)) {
                $arrRegisterFieldSelectionValue = array();
                foreach ($arrRegisterField AS $ID_register_field) {
                    $sSQL = "INSERT INTO module_register_fields
                            (
                                `ID`
                                , `ID_module`
                                , `name`
                                , `ID_check_control`
                                , `ID_extended_type`
                                , `ID_form_fields_group`
                                , `ID_selection`
                                , `disable_select_one`
                                , `enable_in_grid`
                                , `enable_in_menu`
                                , `enable_tip`
                                , `hide`
                                , `hide_register`
                                , `unic_value`
                                , `writable`
                                , `custom_placeholder`
                                , `default_grid`
                                , `grid_md`
                                , `grid_sm`
                                , `grid_xs`
                                , `placeholder`
                                , `label_grid_md`
                                , `label_grid_sm`
                                , `label_grid_xs`
                                , `label_default_grid`
                                , `hide_label`
                                , `enable_in_document`
                                , `enable_in_mail`
                                , `order`
                            )
                            SELECT 
                                null
                                , " . $db->toSql($ID_new_register, "Number") . "
                                , `name`
                                , `ID_check_control`
                                , `ID_extended_type`
                                , `ID_form_fields_group`
                                , `ID_selection`
                                , `disable_select_one`
                                , `enable_in_grid`
                                , `enable_in_menu`
                                , `enable_tip`
                                , `hide`
                                , `hide_register`
                                , `unic_value`
                                , `writable`
                                , `custom_placeholder`
                                , `default_grid`
                                , `grid_md`
                                , `grid_sm`
                                , `grid_xs`
                                , `placeholder`
                                , `label_grid_md`
                                , `label_grid_sm`
                                , `label_grid_xs`
                                , `label_default_grid`
                                , `hide_label`
                                , `enable_in_document`
                                , `enable_in_mail`
                                , `order`
                            FROM module_register_fields
                            WHERE module_register_fields.ID = " . $db->toSql($ID_register_field, "Number");
                    $db->execute($sSQL);

                    $arrRegisterField[$ID_register_field] = $db->getInsertID(true);

                    if (check_function("MD_form_on_done_action"))
                        $arrRegisterFieldSelectionValue = array_replace($arrRegisterFieldSelectionValue, MD_form_clone_field_selection_value($ID_register_field, $arrRegisterField[$ID_register_field]));
                }
            }
            if (check_function("MD_form_on_done_action"))
                MD_form_clone_field_selection($arrRegisterField, $register_new_smart_url);
        }
    }

    return array("ID" => $ID_new_register
        , "name" => $register_new_smart_url
        , "display_name" => $register_new_name);
}

function MD_register_delete($ID_register) {
    $db = ffDB_Sql::factory();

    $sSQL = "DELETE FROM module_form_fields_selection_value
                WHERE module_form_fields_selection_value.ID_form_fields IN (SELECT module_register_fields.ID
                                                                            FROM module_register_fields
                                                                            WHERE module_register_fields.ID_module = " . $db->toSql($ID_register, "Number") .
            ")";
    $db->execute($sSQL);

    $sSQL = "DELETE FROM module_register_fields
                WHERE module_register_fields.ID_module = " . $db->toSql($ID_register, "Number");
    $db->execute($sSQL);
}