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
function system_init_permission($user_permission = null) {
    $globals = ffGlobals::getInstance("gallery");
    $store_in_session = 0;

	if($user_permission === null)
		$user_permission = get_session("user_permission");

	if($user_permission["must-revalidate"] && $user_permission["username_slug"] && $user_permission["ID"] > 0 && $user_permission["permissions"]) {
		cache_session_share_for_subdomains();

		$permissioins = $user_permission["permissions"];
		$user_permission = mod_security_create_session($user_permission["username_slug"], $user_permission["ID"], MOD_SECURITY_SESSION_PERMANENT, true);
		$user_permission["permissions"] = $permissioins;
		
		$store_in_session++;
	}
	
	if(!is_array($user_permission["permissions"])) {
		$gid = ($user_permission["primary_gid"]
			? $user_permission["primary_gid"]
			: $user_permission["primary_gid_default"]
		);	
		if(!$gid) {
			$user_permission["primary_gid"] = MOD_SEC_GUEST_GROUP_ID;
			$user_permission["primary_gid_name"] = MOD_SEC_GUEST_GROUP_NAME;

			$user_permission["primary_gid_default"] = MOD_SEC_GUEST_GROUP_ID;
			$user_permission["primary_gid_default_name"] = MOD_SEC_GUEST_GROUP_NAME;
			
			set_session("UserID", MOD_SEC_GUEST_USER_NAME);
			set_session("UserNID", MOD_SEC_GUEST_USER_ID);
			//set_session("UserLevel", 0);
			//set_session("__FF_SESSION__", session_id());

		}
		if(ENABLE_ADV_PERMISSION) {
			$user_permission["permissions"] = system_get_permission_advanced();
		} else {
			$user_permission["permissions"] = system_get_permission_by_group($gid);
		}
		$store_in_session++;
	}
	
    if(AREA_SHOW_ECOMMERCE) {
    	if(!is_array($user_permission["ecommerce"])) {
    		$user_permission["ecommerce"] = system_get_ecommerce_by_group($user_permission["ID"]);

			$store_in_session++;
		}
		if(is_array($globals->ecommerce) && count($globals->ecommerce))
			$globals->ecommerce = array_replace($globals->ecommerce, $user_permission["ecommerce"]);
		else
			$globals->ecommerce = $user_permission["ecommerce"];
	} 	

	//	$globals->permission = $user_permission["permissions"];	
	if(is_array($user_permission["permissions"]) && count($user_permission["permissions"])) {
		foreach($user_permission["permissions"] AS $key => $value) {
			if(is_array($value))
				define($key, $value["value"]);
			else
				define($key, $value);
		}
	}
	if($store_in_session) {
		set_session("user_permission", $user_permission);

		system_write_cache_permission($user_permission);
	}
}

function system_write_cache_permission($user_permission) {
	$account = ($user_permission["username_slug"]
		? $user_permission["username_slug"]
		: ($user_permission["username"]
			? ffCommon_url_rewrite($user_permission["username"])
			: ffCommon_url_rewrite($user_permission["email"])
		)
	);	
	
	if($account) {
		$file_permission = CM_CACHE_PATH . "/cfg/perm/" . $account . ".php";
		
	    if(!is_dir(ffCommon_dirname($file_permission)))
	        @mkdir(ffCommon_dirname($file_permission), 0777, true);
		

		$content = "<?php\n";
		$content .= '$user_permission = ' . var_export($user_permission, true) . ";";
 		if($handle = @fopen($file_permission, 'w')) {
     		@fwrite($handle, $content); 
     		@fclose($handle);
		}
	}
}
function system_write_cache_permission_group($group, $permissions) {
	if(strlen($group) && is_array($permissions) && count($permissions)) {
		$file_permission = CM_CACHE_PATH . "/cfg/gid/" . $group . ".php";
		
	    if(!is_dir(ffCommon_dirname($file_permission)))
	        @mkdir(ffCommon_dirname($file_permission), 0777, true);
		

		$content = "<?php\n";
		$content .= '$permissions = ' . var_export($permissions, true) . ";";
 		if($handle = @fopen($file_permission, 'w')) {
     		@fwrite($handle, $content); 
     		@fclose($handle);
		}
	}
}

function system_get_permission_by_group($gid = null) {
	$db = ffDB_Sql::factory();

	$options = mod_security_get_settings("/");

	if(!$gid) {
		$gid = MOD_SEC_GUEST_GROUP_ID;
	}
 	$sSQL = "SELECT settings_rel_path_settings.`value`
 				, `settings`.`description`
 				, " . $options["table_groups_name"] . ".name AS group_name
 			FROM settings_rel_path_settings 
				INNER JOIN settings ON settings_rel_path_settings.ID_settings = settings.ID
				INNER JOIN " . $options["table_groups_name"] . " ON " . $options["table_groups_name"] . ".gid = settings_rel_path_settings.ID_rel_path
 			WHERE settings_rel_path_settings.ID_rel_path = " . $db->toSql($gid, "Number");
	$db->query($sSQL);
	if ($db->nextRecord()) {
		do {
			$permissions[$db->getField("description", "Text", true)] = $db->getField("value", "Text", true);
		} while($db->nextRecord());	
		
		system_write_cache_permission_group($db->getField("group_name", "Text", true), $permissions);
	} else {
		$permissions = system_get_permission_by_guest();
	}	

	return $permissions;
}

function system_get_permission_by_guest() {
	$db = ffDB_Sql::factory();


 	$sSQL = "SELECT settings_rel_path_settings.`value`
 				, `settings`.`description`
 			FROM settings_rel_path_settings 
				INNER JOIN settings ON settings_rel_path_settings.ID_settings = settings.ID
 			WHERE settings_rel_path_settings.ID_rel_path = " . $db->toSql(MOD_SEC_GUEST_GROUP_ID, "Number");
	$db->query($sSQL);
	if ($db->nextRecord()) {
		do {
			$permissions[$db->getField("description", "Text", true)] = $db->getField("value", "Text", true);
		} while($db->nextRecord());	

		system_write_cache_permission_group(MOD_SEC_GUEST_GROUP_NAME, $permissions);
	}	

	return $permissions;
}


function system_get_permission_advanced($uid = NULL, $gid = NULL, $settings = array(), $areas = array()) {
	$db = ffDB_Sql::factory();
	$permissions = array();

	if ($uid === NULL && $gid === NULL)
	{
		$uid = get_session("UserNID");
		$user_permission = get_session("user_permission");
        if(is_array($user_permission) && is_array($user_permission["groups"])) {
		    $user_groups = implode(", ", $user_permission["groups"]);
		    if (array_search(MOD_SEC_GUEST_GROUP_ID, $user_permission["groups"]) === FALSE)
		    {
			    if (strlen($user_groups))
				    $user_groups .= ", ";
				    
			    $user_groups .= MOD_SEC_GUEST_GROUP_ID;
		    }
        } else {
            mod_security_destroy_session(false);
            $strError = "User Permission Failed: Broken session by connection failed to database. <br /> All autentication will be reset by server. <br /> Refresh the page.";
            $user_groups = MOD_SEC_GUEST_USER_ID;
        }
	}
	elseif ($gid === NULL)
	{
		$sSQL = "SELECT 
					" . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid AS rel_gid 
				 FROM 
				 	" . CM_TABLE_PREFIX . "mod_security_users_rel_groups
				 WHERE  
				 	" . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . $db->toSql($uid, "Number");
		$db->query($sSQL);
		if ($db->nextRecord())
		{
            $user_groups = "";
			do
			{
				$user_permission["groups"][] = $db->getField("rel_gid", "Number", true);
				if (strlen($user_groups))
					$user_groups .= ", ";
					
				$user_groups .= $db->getField("rel_gid", "Number", true);
			}
			while($db->nextRecord());
			
			if (array_search(2, $user_permission["groups"]) === FALSE)
			{
				if (strlen($user_groups))
					$user_groups .= ", ";
					
				$user_groups .= MOD_SEC_GUEST_GROUP_ID;
			}
		}
		else
			ffErrorHandler::raise(ffTemplate::_get_word_by_code("db_description_corrupted"), E_USER_ERROR, NULL, get_defined_vars());
	}
	else
	{
		$user_groups = MOD_SEC_GUEST_GROUP_ID;
		if ($gid != MOD_SEC_GUEST_GROUP_ID)
			$user_groups .= ", " . $gid;
	}
	
	if ($uid !== NULL)
	{
		$add_cond_1 = " OR settings_rel_path.uid = " . $db->toSql($uid, "Number") . " ";
		$add_cond_2 = " , settings_rel_path.uid DESC ";
	}

	if(is_array($settings) && count($settings)) {
        $sWhere_settings = "";
		foreach ($settings as $settings_key => $settings_value) {
			if (strlen($sWhere_settings))
				$sWhere_settings .= " OR ";

			$sWhere_settings .= " `settings`.`description` = " . $db->toSql($settings_value, "Text");
			
		}	
		$sWhere_settings = " AND (" . $sWhere_settings . ") ";
	} else {
		$sWhere_settings = "";
	}

    if(is_array($areas) && count($areas)) {
        $sWhere_areas = "";
        foreach ($areas as $areas_key => $areas_value) {
            if (strlen($sWhere_areas))
                $sWhere_areas .= " OR ";

            $sWhere_areas .= " `settings`.`area` = " . $db->toSql($areas_value, "Text");
            
        }    
        $sWhere_areas = " AND (" . $sWhere_areas . ") ";
    } else {
        $sWhere_areas = "";
    }
	
	$sWhere = " settings_rel_path.path = " . $db->toSql("/", "Text");

	$sSQL = "
			SELECT 
				`ID_settings`
				, `path`
				, `description`
				, `area`
				, `type`
				, `value_type`
				, `criteria`
				, `dependence`
				, `info`
				, `uid`
				, `level`
				, `value` 
				, `ID_rel_path_settings`
				, `gid`
			FROM (

				SELECT
					`settings_rel_path_settings`.`ID_settings`
					, `settings_rel_path`.`path`
					, `settings`.`description`
					, `settings`.`area`
					, `settings`.`type`
					, `settings`.`value_type`
					, `settings`.`criteria`
					, `settings`.`dependence`
					, `settings`.`info`
					, `settings_rel_path`.`uid`
					, `" . CM_TABLE_PREFIX . "mod_security_groups`.`level`
					, `settings_rel_path_settings`.`value`
					, `settings_rel_path_settings`.`ID` AS `ID_rel_path_settings`
					, `settings_rel_path`.`gid`
				FROM 
					settings_rel_path_settings 
				INNER JOIN settings_rel_path ON 
					settings_rel_path_settings.ID_rel_path = settings_rel_path.ID
				LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON 
					settings_rel_path.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
				INNER JOIN settings ON
					settings_rel_path_settings.ID_settings = settings.ID
				WHERE 
					(
						settings_rel_path.gid IN (" . $user_groups . ")
						" . $add_cond_1 . "
					)
					AND (
						" . $sWhere . "
					)
					" . $sWhere_settings . "
                    " . $sWhere_areas . "
                    
				ORDER BY 
					settings_rel_path_settings.ID_settings
					" . $add_cond_2 . "
					, " . CM_TABLE_PREFIX . "mod_security_groups.level DESC
					, LENGTH(path) DESC

			) AS tbl_src
			GROUP BY
				ID_settings
		";
	$db->query($sSQL);
	if ($db->nextRecord()) {
		do {
			$permissions[$db->getField("description", "Text", true)] = $db->getField("value", "Text", true);
		} while($db->nextRecord());	
	}
        
    if($strError)
        ffErrorHandler::raise($strError, E_USER_ERROR, NULL, get_defined_vars());

	return $permissions;
}

function system_get_ecommerce_by_group($uid) {
	$db = ffDB_Sql::factory();

	$ecommerce = array();
	$sSQL = "SELECT anagraph_type.* 
	    	FROM anagraph_type 
    			INNER JOIN anagraph ON anagraph.ID_type = anagraph_type.ID
    		WHERE anagraph.uid = " . $db->toSql($uid, "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
    	$ecommerce["vat"] 					= $db->getField("ecommerce_vat", "Number", true);
    	$ecommerce["discount_perc"] 		= $db->getField("ecommerce_discount_perc", "Number", true);
    	$ecommerce["discount_val"] 			= $db->getField("ecommerce_discount_val", "Number", true);
    	$ecommerce["fee_perc"] 				= $db->getField("ecommerce_fee_perc", "Number", true);
    	$ecommerce["fee_val"] 				= $db->getField("ecommerce_fee_val", "Number", true);
	}


	return $ecommerce;
}

/**
* OLD
*/
function get_configuration_by_user($user_permission, $selected_lang = "", $user_path = "/", $settings = array(), $areas = array()) {
	$db = ffDB_Sql::factory();

	$user_uid = $user_permission["ID"];
	$user_gid = $user_permission["primary_gid"];
	
	$sSQL = "SELECT DISTINCT 
				path 
			FROM settings_rel_path 
			WHERE 
				" . $db->toSql($user_path) . " LIKE CONCAT(path, '%')
				AND
				(
					uid = " . $db->toSql($user_uid, "Number") . " 
				OR 
					gid = " . $db->toSql($user_gid, "Number") . "
				)
			ORDER BY LENGTH(path) DESC
			";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$setting_path = $db->getField("path", "Text", true);
	} else {
		$setting_path = "/";
	}
	
	$settings = get_configuration_by_path($setting_path, $user_uid, $user_gid, $settings, $areas);
	foreach ($settings as $key => $value) {
		$enable_this_setting = true;
		$depencence = array();

		if($value["dependence"]) {
			$depencence = explode(";", $value["dependence"]);
			foreach ($depencence as $dep_key => $dep_value) {
				if(!$settings[$dep_value]) {
					$enable_this_setting = false;
					break;
				}
			
			}
		}  

		if($enable_this_setting) {
			switch ($value["value_type"]) {
				case "Boolean":
					if($value["value"] !== "0" && $value["value"] !== "1") {
						ffErrorHandler::raise($key . " Not Boolean: " . $value["value"], E_USER_NOTICE, NULL, get_defined_vars());
					}
					break;
				case "String":
					if(strlen($value["criteria"])) {
						$criteria = explode(";", $value["criteria"]);
						if(array_search($value["value"], $criteria) === false) {
							ffErrorHandler::raise($key . " No Match with criteria (" . $value["criteria"] . "): " . $value["value"], E_USER_NOTICE, NULL, get_defined_vars());
						}
					}
					break;
				case "Integer":
					if(!is_numeric($value["value"]) || (abs($value["value"]) != $value["value"])) {
						ffErrorHandler::raise($key . " Not Integer: " . $value["value"], E_USER_NOTICE, NULL, get_defined_vars());
					}
					break;
				case "%":
					if(!is_numeric($value["value"]) || (abs($value["value"]) != $value["value"]) || ($value["value"] > 100)) {
						ffErrorHandler::raise($key . " Not %: " . $value["value"], E_USER_NOTICE, NULL, get_defined_vars());
					}
					break;
				case "Hex":
					if((strlen($value["value"]) !== 6)) {
						ffErrorHandler::raise($key . " Not Hex: " . $value["value"], E_USER_NOTICE, NULL, get_defined_vars());
					}
					break;
				default:
			}
		}			
		
		if($enable_this_setting && $value["area"] != "SYSTEM") {
			define($key, $value["value"]);
		}
        
	}
		
	define("MAX_UPLOAD", $settings["MAX_UPLOAD"]["value"]);

	return true;
}

//recupero dei permessi del percorso e memorizzazione dei permessi in un array associativo
function get_configuration_by_path($user_path, $uid = NULL, $gid = NULL, $settings = array(), $areas = array()) {
	$db = ffDB_Sql::factory();
	$db2 = ffDB_Sql::factory();
	
	if($user_path != "/")
		$user_path = stripslash($user_path);

	$user_permission = get_session("user_permission");
	if(is_array($user_permission["permissions"]) && count($user_permission["permissions"]))
		$settings_path = $user_permission["permissions"];
	
    //$settings_path = get_session("vgs_" . preg_replace('/[^a-zA-Z0-9]/', '', ($user_path == "/" ? "root" : $user_path)));
//	$settings_path = get_session("vgs_" . "main");
    if(!is_array($settings_path) || count($settings_path) == 0) {
	    if ($uid === NULL && $gid === NULL)
	    {
		    $uid = get_session("UserNID");
		    $user_permission = get_session("user_permission");
            if(is_array($user_permission) && is_array($user_permission["groups"])) {
		        $user_groups = implode(", ", $user_permission["groups"]);
		        if (array_search(2, $user_permission["groups"]) === FALSE)
		        {
			        if (strlen($user_groups))
				        $user_groups .= ", ";
				        
			        $user_groups .= "2";
		        }
            } else {
                mod_security_destroy_session(false);
                $strError = "User Permission Failed: Broken session by connection failed to database. <br /> All autentication will be reset by server. <br /> Refresh the page.";
                $user_groups = "2";
            }
	    }
	    elseif ($gid === NULL)
	    {
		    $sSQL = "SELECT 
					    " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid AS rel_gid 
				     FROM 
				 	    " . CM_TABLE_PREFIX . "mod_security_users_rel_groups
				     WHERE  
				 	    " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . $db->toSql(new ffData($uid, "Number"));
		    $db->query($sSQL);
		    if ($db->nextRecord())
		    {
                $user_groups = "";
			    do
			    {
				    $user_permission["groups"][] = $db->getField("rel_gid")->getValue();
				    if (strlen($user_groups))
					    $user_groups .= ", ";
					    
				    $user_groups .= $db->getField("rel_gid")->getValue();
			    }
			    while($db->nextRecord());
			    
			    if (array_search(2, $user_permission["groups"]) === FALSE)
			    {
				    if (strlen($user_groups))
					    $user_groups .= ", ";
					    
				    $user_groups .= "2";
			    }
		    }
		    else
			    ffErrorHandler::raise(ffTemplate::_get_word_by_code("db_description_corrupted"), E_USER_ERROR, NULL, get_defined_vars());
	    }
	    else
	    {
		    $user_groups = "2";
		    if ($gid != 2)
			    $user_groups .= ", " . $gid;
	    }
	    
	    if ($uid !== NULL)
	    {
		    $add_cond_1 = " OR settings_rel_path.uid = " . $db->toSql(new ffData($uid, "Number")) . " ";
		    $add_cond_2 = " , settings_rel_path.uid DESC ";
	    }

	    if(is_array($settings) && count($settings)) {
            $sWhere_settings = "";
		    foreach ($settings as $settings_key => $settings_value) {
			    if (strlen($sWhere_settings))
				    $sWhere_settings .= " OR ";

			    $sWhere_settings .= " `settings`.`description` = " . $db->toSql($settings_value, "Text");
			    
		    }	
		    $sWhere_settings = " AND (" . $sWhere_settings . ") ";
	    } else {
		    $sWhere_settings = "";
	    }

        if(is_array($areas) && count($areas)) {
            $sWhere_areas = "";
            foreach ($areas as $areas_key => $areas_value) {
                if (strlen($sWhere_areas))
                    $sWhere_areas .= " OR ";

                $sWhere_areas .= " `settings`.`area` = " . $db->toSql($areas_value, "Text");
                
            }    
            $sWhere_areas = " AND (" . $sWhere_areas . ") ";
        } else {
            $sWhere_areas = "";
        }
	    
	    $sWhere = "";
	    $src_path = $user_path;
	    
	    do {
		    if (strlen($sWhere))
			    $sWhere .= " OR ";
			    
		    $sWhere .= " (settings_rel_path.path = " . $db->toSql($src_path, "Text");
		    
		    if ($src_path != $user_path)
			    $sWhere .= " AND settings_rel_path.`mod` <> 1";
			    
		    $sWhere .= ")";
	    } while($src_path != ffCommon_dirname($src_path) && $src_path = ffCommon_dirname($src_path));
	    
	    // recupera l'elenco per i gruppi
	    $sSQL = "
			    SELECT 
				    `ID_settings`
				    , `path`
				    , `description`
				    , `area`
				    , `type`
				    , `value_type`
				    , `criteria`
				    , `dependence`
				    , `info`
				    , `uid`
				    , `level`
				    , `value` 
				    , `ID_rel_path_settings`
				    , `gid`
			    FROM (

				    SELECT
					    `settings_rel_path_settings`.`ID_settings`
					    , `settings_rel_path`.`path`
					    , `settings`.`description`
					    , `settings`.`area`
					    , `settings`.`type`
					    , `settings`.`value_type`
					    , `settings`.`criteria`
					    , `settings`.`dependence`
					    , `settings`.`info`
					    , `settings_rel_path`.`uid`
					    , `" . CM_TABLE_PREFIX . "mod_security_groups`.`level`
					    , `settings_rel_path_settings`.`value`
					    , `settings_rel_path_settings`.`ID` AS `ID_rel_path_settings`
					    , `settings_rel_path`.`gid`
				    FROM 
					    settings_rel_path_settings 
				    INNER JOIN settings_rel_path ON 
					    settings_rel_path_settings.ID_rel_path = settings_rel_path.ID
				    LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON 
					    settings_rel_path.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
				    INNER JOIN settings ON
					    settings_rel_path_settings.ID_settings = settings.ID
				    WHERE 
					    (
						    settings_rel_path.gid IN (" . $user_groups . ")
						    " . $add_cond_1 . "
					    )
					    AND (
						    " . $sWhere . "
					    )
					    " . $sWhere_settings . "
                        " . $sWhere_areas . "
                        
				    ORDER BY 
					    settings_rel_path_settings.ID_settings
					    " . $add_cond_2 . "
					    , " . CM_TABLE_PREFIX . "mod_security_groups.level DESC
					    , LENGTH(path) DESC

			    ) AS tbl_src
			    GROUP BY
				    ID_settings
		    ";
	    $db->query($sSQL);

	    $settings_path = array();
	    
	    if ($db->nextRecord()) {
		    do {
			    $settings_path[$db->getField("description")->getValue()] = array();
			    $settings_path[$db->getField("description")->getValue()]["area"] = $db->getField("area")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["type"] = $db->getField("type")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["value_type"] = $db->getField("value_type")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["criteria"] = $db->getField("criteria")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["dependence"] = $db->getField("dependence")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["info"] = $db->getField("info")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["value"] = $db->getField("value")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["path"] = $db->getField("path")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["gid"] = $db->getField("gid")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["uid"] = $db->getField("uid")->getValue();
			    $settings_path[$db->getField("description")->getValue()]["ID_rel_path_settings"] = $db->getField("ID_rel_path_settings")->getValue();
		    } while($db->nextRecord());	
	    }
            
        if($strError)
            ffErrorHandler::raise($strError, E_USER_ERROR, NULL, get_defined_vars());

        $user_permission["permissions"] = $settings_path;
        set_session("user_permission", $user_permission);
//        set_session("vgs_" . preg_replace('/[^a-zA-Z0-9]/', '', ($user_path == "/" ? "root" : $user_path)), $settings_path);
    }

	return $settings_path;
}