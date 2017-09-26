<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_cloudocs_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_cloudocs_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_coudocs_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);

define("MOD_CLOUDOCS_PATH", $cm->router->named_rules["cloudocs"]->reverse);

if(!function_exists("global_settings")) {
	function global_settings($key = null) {
		static $global_settings = false;

		if($global_settings === false) {
			$global_settings = mod_restricted_get_all_setting();
		}

		if(array_key_exists($key, $global_settings)) {
			return $global_settings[$key];
		} else {
			return null;
		}
	}	
}

function mod_cloudocs_on_before_page_process($cm) {
	if(strpos($cm->path_info, MOD_CLOUDOCS_PATH) !== false) {
        if(strlen(MOD_CLOUDOCS_THEME) && is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . MOD_CLOUDOCS_THEME)) {
		    $cm->layout_vars["theme"] = MOD_CLOUDOCS_THEME;
        }
	}
}

function mod_cloudocs_on_before_rounting($cm) {
	$permission = check_coudocs_permission();
	if($permission !== true
		&&
		(!(is_array($permission) && count($permission)
			&& ($permission[MOD_CLOUDOCS_GROUP_OWNER]
				|| $permission[MOD_CLOUDOCS_GROUP_CUSTOMER]
			)
		))
	) {
    	$cm->modules["restricted"]["menu"]["cloudocs"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["docs"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["category"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["customer"]["hide"] = true;
	} else {
		if(strpos($cm->path_info, MOD_CLOUDOCS_PATH) !== false) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . MOD_CLOUDOCS_JQUERYUI_THEME)) {
    		    $cm->oPage->jquery_ui_force_theme = MOD_CLOUDOCS_JQUERYUI_THEME;
            }
		

	    //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
		    
			if(!$permission["owner"]) {
				$cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["docs"]["hide"] = true;
			    $cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["category"]["hide"] = true;
			    $cm->modules["restricted"]["menu"]["cloudocs"]["elements"]["customer"]["hide"] = true;
			}
			
			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/cloudocs/themes/javascript", true);
			}
		}
	}
}

function check_coudocs_permission() {
	if(!MOD_SEC_GROUPS) 
		return true;

    $db = ffDB_Sql::factory();

    $user_permission = get_session("user_permission");
    $userID = get_session("UserID");
    
    if(is_array($user_permission) && count($user_permission) 
    	&& is_array($user_permission["groups"]) && count($user_permission["groups"])
    	&& $userID != MOD_SEC_GUEST_USER_NAME
    ) {
    	if(!array_key_exists("permissions_custom", $user_permission))
            $user_permission["permissions_custom"] = array();

	    if(!(array_key_exists("cloudocs", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["cloudocs"]))) {
	    	$user_permission["permissions_custom"]["cloudocs"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);

		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_CLOUDOCS_GROUP_OWNER);
		    $db->query($sSQL);
		    if($db->nextRecord()) {
		        $user_permission["permissions_custom"]["cloudocs"]["anagraph"] = $db->getField("anagraph", "Number", true);
		        $user_permission["permissions_custom"]["cloudocs"]["owner"] = true;
		    }
		    
		    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_groups.*
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_CLOUDOCS_GROUP_CUSTOMER);
		    $db->query($sSQL);
		    if($db->nextRecord()) {
		        $user_permission["permissions_custom"]["cloudocs"]["anagraph"] = $db->getField("anagraph", "Number", true);
		        $user_permission["permissions_custom"]["cloudocs"]["owner"] = false;
		    }

	        set_session("user_permission", $user_permission);
		}
		
	    return $user_permission["permissions_custom"]["cloudocs"];
	}    
    return null;
}

function mod_coudocs_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");
	
	if(strtolower($user_permission["primary_gid_name"]) == "coudocs"
		|| $user_permission["primary_gid_name"] == MOD_CLOUDOCS_GROUP_OWNER
		|| $user_permission["primary_gid_name"] == MOD_CLOUDOCS_GROUP_CUSTOMER
	) {
		if(strlen(MOD_CLOUDOCS_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_CLOUDOCS_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_CLOUDOCS_PATH);
			}
		}
	}
}
?>
