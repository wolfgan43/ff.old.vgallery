<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_webdir_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_webdir_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_webdir_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);

define("MOD_WEBDIR_PATH", $cm->router->named_rules["webdir"]->reverse);
define("MOD_WEBDIR_USER_PATH", $cm->router->named_rules["user_webdir"]->reverse);

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

function mod_webdir_on_before_page_process($cm) {
	if(strpos($cm->path_info, MOD_WEBDIR_PATH) !== false) {
        if(strlen(MOD_WEBDIR_THEME) && is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . MOD_WEBDIR_THEME)) {
		    $cm->layout_vars["theme"] = MOD_WEBDIR_THEME;
        }
	}
}

function mod_webdir_on_before_rounting($cm) {
	$permission = check_webdir_permission();
	if($permission !== true
		&&
		(!(is_array($permission) && count($permission)
			&& ($permission[MOD_WEBDIR_GROUP_ADMIN]
				|| $permission[MOD_WEBDIR_GROUP_USER]
			)
		))
	) {
    	$cm->modules["restricted"]["menu"]["webdir"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["webdir"]["elements"]["company"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["webdir"]["elements"]["category"]["hide"] = true;
	} else {
		if(strpos($cm->path_info, MOD_WEBDIR_PATH) !== false) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . MOD_WEBDIR_JQUERYUI_THEME)) {
    		    $cm->oPage->jquery_ui_force_theme = MOD_WEBDIR_JQUERYUI_THEME;
            }
			
			$cm->modules["restricted"]["menu"]["webdir"]["elements"]["company"]["hide"] = false;
			if($permission["primary_group"] != MOD_WEBDIR_GROUP_ADMIN) {
				$cm->modules["restricted"]["menu"]["webdir"]["elements"]["category"]["hide"] = false; 
			}

			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/webdir/themes/javascript", true);
			}
		}
	}
}

function check_webdir_permission($check_group = null) {
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

	    if(!(array_key_exists("webdir", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["webdir"]))) {
	    	$user_permission["permissions_custom"]["webdir"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);
			$strPermission = $db->toSql(MOD_WEBDIR_GROUP_ADMIN, "Text") 
							. "," . $db->toSql(MOD_WEBDIR_GROUP_USER, "Text");

		    $user_permission["permissions_custom"]["webdir"][MOD_WEBDIR_GROUP_ADMIN] = false;
		    $user_permission["permissions_custom"]["webdir"][MOD_WEBDIR_GROUP_USER] = false;
		    $user_permission["permissions_custom"]["webdir"]["primary_group"] = "";
		    
		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				do {
			        $user_permission["permissions_custom"]["webdir"][$db->getField("name", "Text", true)] = true;
			        $user_permission["permissions_custom"]["webdir"]["primary_group"] = $db->getField("name", "Text", true);
				} while($db->nextRecord());
		    }
		    
	        set_session("user_permission", $user_permission);
		}
		if($check_group === null) { 
	    	return $user_permission["permissions_custom"]["webdir"];
		} else {
			return $user_permission["permissions_custom"]["webdir"]["primary_group"];
		}
	}    
    return null;
}

function mod_webdir_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");

	if($user_permission["primary_gid_name"] == MOD_WEBDIR_GROUP_ADMIN) {
		if(strlen(MOD_WEBDIR_PATH)) {
			ffRedirect(FF_SITE_PATH . MOD_WEBDIR_PATH);
		}
	}

	if(strtolower($user_permission["primary_gid_name"]) == "webdir"
		|| $user_permission["primary_gid_name"] == MOD_WEBDIR_GROUP_USER
	) {
		if(strlen(MOD_WEBDIR_USER_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_WEBDIR_USER_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_WEBDIR_USER_PATH);
			}
		}
	}
}
?>