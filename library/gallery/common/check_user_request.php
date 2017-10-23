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
  function check_user_request(&$user, $old_session_id = null, $permanent_session = MOD_SECURITY_SESSION_PERMANENT) {
  	$cm = cm::getInstance();
    //i globals qui nn sono valorizzati
	if(/*global_settings("ENABLE_ECOMMERCE") &&*/ check_function("ecommerce_cart_merge"))
		ecommerce_cart_merge($user);

  	check_user_form_request($user);
  	check_user_vgallery_request($user);
    $arrRetUrl = explode("?", $cm->oPage->ret_url);
  	if($arrRetUrl[0] == "/" || $arrRetUrl[0] == "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/") {  	
		$mod_sec_dashboard = $cm->router->getRuleById("mod_sec_dashboard");
		if($mod_sec_dashboard)
			$cm->oPage->ret_url = $mod_sec_dashboard->reverse;  	
	}  	  	

  }

function check_user_form_request($user, $exclude_form = null) {
    $db_data = ffDB_Sql::factory();
    
    $sSQL_exclude_form = "";
    if(is_array($exclude_form)) {
    	foreach($exclude_form AS $exclude_form_value) {
    		$sSQL_exclude_form .= " AND module_form.name <> "  . $db_data->toSql($exclude_form_value);
		}
	} else {
		$sSQL_exclude_form .= " AND module_form.name <> "  . $db_data->toSql($exclude_form);
	}
    $sSQL = "SELECT module_form.name AS form_name
    			, users_rel_module_form.request AS requested
            FROM users_rel_module_form 
            	INNER JOIN module_form ON module_form.ID = users_rel_module_form.ID_module
            WHERE 
                uid = " . $db_data->toSql($user["ID"], "Number") . "
                AND ID_form_node = 0
                $sSQL_exclude_form
            ORDER BY users_rel_module_form.`order`, module_form.ID";
    $db_data->query($sSQL);
    if($db_data->nextRecord()) {
        if($db_data->getField("requested", "Number")->getValue() > 0) {
            $request_info = $db_data->getField("form_name")->getValue();
        } else {
            $request_info = false;
        }
    } else {
        $request_info = false;
    }
    
    $uid = get_session("UserNID");
    if($uid == $UserNID)
        set_session("request_info", ($request_info == false ? false : USER_RESTRICTED_PATH . "/additionaldata"));

    return $request_info;
}


function check_user_vgallery_request($user, $return = "link", $exclude_vgallery = null) {
	$db_data = ffDB_Sql::factory();

	$redirect = null;
	
   	$sSQL_exclude_vgallery = "";
   	if($exclude_vgallery !== null) {
	    if(is_array($exclude_vgallery)) {
    		foreach($exclude_vgallery AS $exclude_vgallery_value) {
    			$sSQL_exclude_vgallery .= " AND vgallery.name <> "  . $db_data->toSql($exclude_vgallery_value);
			}
		} else {
			$sSQL_exclude_vgallery .= " AND vgallery.name <> "  . $db_data->toSql($exclude_vgallery);
		}
	}
    
	$sSQL = "
    	SELECT 
    		" . CM_TABLE_PREFIX . "mod_security_users.username
	        , vgallery.name AS vgallery_name
	        , users_rel_vgallery.cascading AS cascading
	        , users_rel_vgallery.request AS request
	        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)  AS full_path
	        , IF(NOT(users_rel_vgallery.cascading > 0)
                , " . ($return == "link"
                    ? "(SELECT 
                                REPLACE(CONCAT(IF(vgallery_user.parent = '/', '', vgallery_user.parent), '/', vgallery_user.name) 
                                    , CONCAT('/', layout.value)
                                        , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
                                            , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))
                                            , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) > 1
                                                , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(layout.params, layout_path.path)))
                                                , IF(layout_path.path = '/', '', layout_path.path) 
                                            ) 
                                        )
                                    )
                                FROM vgallery_nodes AS vgallery_user
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_user.ID_vgallery
                                    INNER JOIN layout 
                                        ON layout.value = vgallery.name 
                                            AND layout.ID_type = (SELECT ID FROM layout_type WHERE layout_type.name = " . $db_data->toSql("VIRTUAL_GALLERY", "Text") . " )  
                                            AND vgallery_user.parent LIKE CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params), '%')
                                    INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
                                WHERE vgallery_user.parent LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%') 
                                    AND 
                                    (
                                        vgallery_user.name = " . $db_data->toSql(ffCommon_url_rewrite($user["name"])) . "
                                    OR    
                                        vgallery_user.owner = " . $db_data->toSql($user["ID"], "Number") . "
                                    )
                                LIMIT 1
                        )"
                    : "(SELECT 
                        vgallery_user.ID
                        FROM vgallery_nodes AS vgallery_user
                            INNER JOIN vgallery ON vgallery.ID = vgallery_user.ID_vgallery
                        WHERE vgallery_user.parent LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%') 
                            AND 
                            (
                            	vgallery_user.name = " . $db_data->toSql(ffCommon_url_rewrite($user["name"])) . "
                            OR	
                            	vgallery_user.owner = " . $db_data->toSql($user["ID"], "Number") . "
                            )
                        ORDER BY LENGTH(REPLACE(vgallery_user.name, " . $db_data->toSql(ffCommon_url_rewrite($user["name"])) . ", ''))
                        LIMIT 1
                    )"
                ) . "
	        	, 0
	        ) AS real_request
	    FROM " . CM_TABLE_PREFIX . "mod_security_users 
	        INNER JOIN users_rel_vgallery ON users_rel_vgallery.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
	        INNER JOIN vgallery_nodes ON vgallery_nodes.ID = users_rel_vgallery.ID_nodes
	        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	    WHERE
	         " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_data->toSql($user["ID"], "Number") . "
	         AND NOT(users_rel_vgallery.cascading > 0)
	         $sSQL_exclude_vgallery
	    ORDER BY users_rel_vgallery.request DESC, users_rel_vgallery.`order`, vgallery_nodes.order";
	$db_data->query($sSQL);
	if($db_data->nextRecord()) {
        $count_item = 0;
        do {
		    if(strlen($db_data->getField("real_request", "Text", true))) {
                if($return == "link") {
                	if($redirect === null)
                    	$redirect = FF_SITE_PATH . $db_data->getField("real_request", "Text", true);
                    	
                    $request_vgallery = false;
                } else {
                    $request_vgallery[$count_item]["vgallery"] = $db_data->getField("vgallery_name", "Text", true);
                    $request_vgallery[$count_item]["ID"] = $db_data->getField("real_request", "Number", true);
                }
				$vgallery_user_path[$db_data->getField("vgallery_name", "Text", true)] = $db_data->getField("real_request", "Text", true);
		    } else {
		    	if($db_data->getField("request", "Number", true)) {
			    	$request_vgallery = VG_SITE_VGALLERY . "/modify" . $db_data->getField("full_path", "Text", true); /* . "?ret_url=" . urlencode($db_data->getField("full_path", "Text", true) . "/" . $user["name"]);*/
				} else {
					$request_vgallery = false;
				}
		    }
		    $count_item++;
        } while($db_data->nextRecord());
	} else {
		$request_vgallery = false;
	}

    $uid = get_session("UserNID");
    if($uid == $user["ID"]) {
        set_session("request_vgallery", ($request_vgallery == false || is_array($request_vgallery) ? false : $request_vgallery));
        if(is_array($vgallery_user_path) && count($vgallery_user_path)) {
        	set_session("vgallery_user_path", $vgallery_user_path);	
		} else {
			set_session("vgallery_user_path", "");
		}
	}
		
	if($redirect !== null) {
		ffRedirect($redirect);
	}
	
	return $request_vgallery;
}
