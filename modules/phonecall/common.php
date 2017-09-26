<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_phonecall_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_phonecall_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_phonecall_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);

define("MOD_PHONECALL_PATH", $cm->router->named_rules["phonecall"]->reverse);

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

function mod_phonecall_on_before_page_process($cm) {
	if(strpos($cm->path_info, MOD_PHONECALL_PATH) !== false) {
        if(strlen(global_settings("MOD_PHONECALL_THEME")) && is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . MOD_PHONECALL_THEME)) {
		    $cm->layout_vars["theme"] = MOD_PHONECALL_THEME;
        }
	}
}

function mod_phonecall_on_before_rounting($cm) {
	$permission = check_phonecall_permission();
	if($permission !== true
		&&
		(!(is_array($permission) && count($permission)
			&& ($permission[MOD_PHONECALL_GROUP_ADMIN]
				|| $permission[MOD_PHONECALL_GROUP_USER]
			)
		))
	) {
    	$cm->modules["restricted"]["menu"]["phonecall"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["pending"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["archived"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["users"]["hide"] = true;
	} else {
		if(strpos($cm->path_info, MOD_PHONECALL_PATH) !== false) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . MOD_PHONECALL_JQUERYUI_THEME)) {
    		    $cm->oPage->jquery_ui_force_theme = MOD_PHONECALL_JQUERYUI_THEME;
            }
			
			$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["pending"]["hide"] = false;
			$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["archived"]["hide"] = false; 
			if($permission["primary_group"] != MOD_PHONECALL_GROUP_ADMIN) {
				$cm->modules["restricted"]["menu"]["phonecall"]["elements"]["users"]["hide"] = true;
			}
			
			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/phonecall/themes/javascript", true);
			}
		}
	}
}

function check_phonecall_permission($check_group = null) {
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

	    if(!(array_key_exists("phonecall", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["phonecall"]))) {
	    	$user_permission["permissions_custom"]["phonecall"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);
			$strPermission = $db->toSql(MOD_PHONECALL_GROUP_ADMIN, "Text") 
							. "," . $db->toSql(MOD_PHONECALL_GROUP_USER, "Text");

		    $user_permission["permissions_custom"]["phonecall"][MOD_PHONECALL_GROUP_ADMIN] = false;
		    $user_permission["permissions_custom"]["phonecall"][MOD_PHONECALL_GROUP_USER] = false;
		    $user_permission["permissions_custom"]["phonecall"]["primary_group"] = "";
		    
		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				do {
			        $user_permission["permissions_custom"]["phonecall"][$db->getField("name", "Text", true)] = true;
			        $user_permission["permissions_custom"]["phonecall"]["primary_group"] = $db->getField("name", "Text", true);
				} while($db->nextRecord());
		    }
		    
	        set_session("user_permission", $user_permission);
		}
		if($check_group === null) { 
	    	return $user_permission["permissions_custom"]["phonecall"];
		} else {
			return $user_permission["permissions_custom"]["phonecall"]["primary_group"];
		}
	}    
    return null;
}

function mod_phonecall_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");
	
	if(strtolower($user_permission["primary_gid_name"]) == "phonecall"
		|| $user_permission["primary_gid_name"] == MOD_PHONECALL_GROUP_ADMIN
		|| $user_permission["primary_gid_name"] == MOD_PHONECALL_GROUP_USER
	) {
		if(strlen(MOD_PHONECALL_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_PHONECALL_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_PHONECALL_PATH);
			}
		}
	}
}
?>
