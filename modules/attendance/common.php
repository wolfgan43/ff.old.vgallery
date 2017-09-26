<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_attendance_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_attendance_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_attendance_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);

define("MOD_ATTENDANCE_PATH", $cm->router->named_rules["attendance"]->reverse);

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

function mod_attendance_on_before_page_process($cm) {
	if(strpos($cm->path_info, MOD_ATTENDANCE_PATH) !== false) {
        if(strlen(MOD_ATTENDANCE_THEME) && is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . MOD_ATTENDANCE_THEME)) {
		    $cm->layout_vars["theme"] = MOD_ATTENDANCE_THEME;
        }
	}
}

function mod_attendance_on_before_rounting($cm) {
	$permission = check_attendance_permission();
	if($permission !== true
		&&
		(!(is_array($permission) && count($permission)
			&& ($permission[MOD_ATTENDANCE_GROUP_ATTENDANCE]
				|| $permission[MOD_ATTENDANCE_GROUP_OFFICE]
				|| $permission[MOD_ATTENDANCE_GROUP_EMPLOYEE]
			)
		))
	) {
    	$cm->modules["restricted"]["menu"]["attendance"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["office"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["sheet"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["gdocs"]["hide"] = true;  //da splittare in un altro modulo (event)
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["users"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["type"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["tool"]["hide"] = true;

		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["photo"]["hide"] = true; //da splittare in un altro modulo (photo)
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["argument"]["hide"] = true; //da splittare in un altro modulo (photo)
		$cm->modules["restricted"]["menu"]["attendance"]["elements"]["event"]["hide"] = true; //da splittare in un altro modulo (photo)
	} else {
		if(strpos($cm->path_info, MOD_ATTENDANCE_PATH) !== false) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . MOD_ATTENDANCE_JQUERYUI_THEME)) {
    		    $cm->oPage->jquery_ui_force_theme = MOD_ATTENDANCE_JQUERYUI_THEME;
            }
			
			$cm->modules["restricted"]["menu"]["attendance"]["elements"]["sheet"]["hide"] = false;
			$cm->modules["restricted"]["menu"]["attendance"]["elements"]["photo"]["hide"] = false; //da splittare in un altro modulo (photo)
			$cm->modules["restricted"]["menu"]["attendance"]["elements"]["gdocs"]["hide"] = false; //da splittare in un altro modulo (event)
			if($permission["primary_group"] != MOD_ATTENDANCE_GROUP_ATTENDANCE) {
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["office"]["hide"] = true;
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["users"]["hide"] = true;
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["type"]["hide"] = true;
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["tool"]["hide"] = true;
				
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["argument"]["hide"] = true; //da splittare in un altro modulo (photo)
				$cm->modules["restricted"]["menu"]["attendance"]["elements"]["event"]["hide"] = true; //da splittare in un altro modulo (photo)
			}
			if($permission["primary_group"] != MOD_ATTENDANCE_GROUP_OFFICE) {
				
			}
			
			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/attendance/themes/javascript", true);
			}
		}
	}
}

function check_attendance_permission($check_group = null) {
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

	    if(!(array_key_exists("attendance", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["attendance"]))) {
	    	$user_permission["permissions_custom"]["attendance"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);
			$strPermission = $db->toSql(MOD_ATTENDANCE_GROUP_ATTENDANCE, "Text") 
							. "," . $db->toSql(MOD_ATTENDANCE_GROUP_OFFICE, "Text") 
							. "," . $db->toSql(MOD_ATTENDANCE_GROUP_EMPLOYEE, "Text");

		    $user_permission["permissions_custom"]["attendance"][MOD_ATTENDANCE_GROUP_ATTENDANCE] = false;
		    $user_permission["permissions_custom"]["attendance"][MOD_ATTENDANCE_GROUP_OFFICE] = false;
		    $user_permission["permissions_custom"]["attendance"][MOD_ATTENDANCE_GROUP_EMPLOYEE] = false;
		    $user_permission["permissions_custom"]["attendance"]["primary_group"] = "";
		    
		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				do {
			        $user_permission["permissions_custom"]["attendance"][$db->getField("name", "Text", true)] = true;
			        $user_permission["permissions_custom"]["attendance"]["primary_group"] = $db->getField("name", "Text", true);
				} while($db->nextRecord());
		    }
		    
	        set_session("user_permission", $user_permission);
		}
		if($check_group === null) { 
	    	return $user_permission["permissions_custom"]["attendance"];
		} else {
			return $user_permission["permissions_custom"]["attendance"]["primary_group"];
		}
	}    
    return null;
}

function mod_attendance_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");
	
	if(strtolower($user_permission["primary_gid_name"]) == "attendence"
		|| $user_permission["primary_gid_name"] == MOD_ATTENDANCE_GROUP_ATTENDANCE
		|| $user_permission["primary_gid_name"] == MOD_ATTENDANCE_GROUP_OFFICE
		|| $user_permission["primary_gid_name"] == MOD_ATTENDANCE_GROUP_EMPLOYEE
	) {
		if(strlen(MOD_ATTENDANCE_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_ATTENDANCE_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_ATTENDANCE_PATH);
			}
		}
	}
}
?>
