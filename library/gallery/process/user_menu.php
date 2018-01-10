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
function process_user_menu($UserNID = NULL, $source_user_path = NULL, $enable_ecommerce = true, $user_path = "/", $layout, $short_mode = true, $enable_edit = true) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

    $db_user_menu = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
    
    $buffer = "";
    $count_item = 0;

    if(strlen($layout_settings["AREA_USER_TEMPLATE"]))
    	$template = "_" . $layout_settings["AREA_USER_TEMPLATE"];
    else
    	$template = "";
    
	//$tpl_data["custom"] = "menu-user.html";
	$tpl_data["custom"] = $layout["smart_url"] . ".html";		
	$tpl_data["base"] = "user_menu" . $template . ".html";

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   
    
    //$tpl = ffTemplate::factory(get_template_cascading($user_path, "user_menu" . $template . ".html", "", null, $layout["location"]));
    //$tpl->load_file("user_menu" . $template . ".html", "main");

    if(!$template) {
		/**
		* Admin Father Bar
		*/
	    if(AREA_USERS_SHOW_MODIFY && (AREA_LAYOUT_SHOW_MODIFY ) && $enable_edit) {
	        $admin_menu["admin"]["unic_name"] = $unic_id;
	        $admin_menu["admin"]["title"] = $layout["title"];
	        $admin_menu["admin"]["class"] = $layout["type_class"];
	        $admin_menu["admin"]["group"] = $layout["type_group"];
	        $admin_menu["admin"]["modify"] = "";
	        $admin_menu["admin"]["delete"] = "";
	        if(AREA_PROPERTIES_SHOW_MODIFY) {
	            $admin_menu["admin"]["extra"] = "";
	        }
	        if(AREA_ECOMMERCE_SHOW_MODIFY) {
	            $admin_menu["admin"]["ecommerce"] = "";
	        }
	        if(AREA_LAYOUT_SHOW_MODIFY) {
	            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
	            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
	        }
	        if(AREA_SETTINGS_SHOW_MODIFY) {
	            $admin_menu["admin"]["setting"] = "";
	        }
	        
	        $admin_menu["sys"]["path"] = $user_path;
	        $admin_menu["sys"]["type"] = "admin_toolbar";
	       // $admin_menu["sys"]["ret_url"] = $ret_url;
	    }

		/**
		* Process Block Header
		*/		    
	    if(check_function("set_template_var"))
			$block = get_template_header($user_path, $admin_menu, $layout, $tpl);
	}

    if(strlen($layout_settings["USER_INTERFACE_MENU_PLUGIN"]))
    	$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["USER_INTERFACE_MENU_PLUGIN"]));
	else
    	$tpl->set_var("class_plugin", "user_menu");
    	
    setJsRequest($layout_settings["USER_INTERFACE_MENU_PLUGIN"]);
        

    // Visualizzazione o meno della sezione login
    if($UserNID === NULL)
        $UserNID  = get_session("UserNID");
    
    $UserID = NULL;  
    
    $sSQL = "
    		SELECT 
	            " . CM_TABLE_PREFIX . "mod_security_users.username AS username
	            , " . CM_TABLE_PREFIX . "mod_security_users.enable_manage AS enable_manage
	            , module_form.name AS form_name 
	        FROM " . CM_TABLE_PREFIX . "mod_security_users 
	            LEFT JOIN users_rel_module_form ON users_rel_module_form.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
	            LEFT JOIN module_form ON module_form.ID = users_rel_module_form.ID_module
	        WHERE
	            " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_user_menu->toSql($UserNID, "Number") . "
	        ORDER BY users_rel_module_form.request DESC, users_rel_module_form.`order`, module_form.ID
            ";
    $db_user_menu->query($sSQL);
    if($db_user_menu->nextRecord() && $db_user_menu->getField("username")->getValue() != MOD_SEC_GUEST_USER_NAME) {
    	$UserID = $db_user_menu->getField("username", "Text", true);
    	
       
        if ($layout_settings["AREA_USER_SHOW_MANAGE_ACCOUNT"] && $db_user_menu->getField("enable_manage")->getValue()) {
            $count_item++;      

			if($source_user_path === NULL) {
			    $tpl->set_var("item_path", FF_SITE_PATH . USER_RESTRICTED_PATH . (!$short_mode ? "" : "/account"));
			} else {
		    	$tpl->set_var("item_path", $source_user_path . (!$short_mode ? "" : "/account"));
			}
            $tpl->parse("SezManageAccount", false);
        } else
            $tpl->set_var("SezManageAccount", "");

        if ($layout_settings["AREA_USER_SHOW_ECOMMERCE_MANAGE"] && AREA_SHOW_ECOMMERCE && $enable_ecommerce) {
            $count_item++;
            
            $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_ECOMMERCE);
            $tpl->parse("SezEcommerceManage", false);
        } else {
            $tpl->set_var("SezEcommerceManage", "");
        }

        if ($layout_settings["AREA_USER_SHOW_ECOMMERCE_CART"] && AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_SHOW_CART && $enable_ecommerce) {
            $count_item++;
            
            $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_CART);
            if($globals->ecommerce["unic_id"]) {
            	$tpl->set_var("item_rel", $globals->ecommerce["unic_id"]);
            	$tpl->parse("SezEcommerceCartRel", false);
			} else {
				$tpl->set_var("SezEcommerceCartRel", "");
			}
            $tpl->parse("SezEcommerceCart", false);
        } else {
            $tpl->set_var("SezEcommerceCart", "");
        }

        if ($layout_settings["AREA_USER_SHOW_WISHLIST"] && AREA_SHOW_ECOMMERCE && USE_CART_PUBLIC_MONO && $enable_ecommerce) {
            $count_item++;
            
            $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_WISHLIST);
            $tpl->parse("SezEcommerceMonoCart", false);
        } else {
            $tpl->set_var("SezEcommerceMonoCart", "");
        }

        if(!$short_mode) {
            do {
                if(!is_null($db_user_menu->getField("form_name")->getValue())) {
                    $count_item++;
                    
			        if($source_user_path === NULL) {
			            $tpl->set_var("item_path", FF_SITE_PATH . USER_RESTRICTED_PATH . "/additionaldata");
				    } else {
			    	    $tpl->set_var("item_path", $source_user_path . "/additionaldata");
				    }

                    
                    $tpl->set_var("user_data", $db_user_menu->getField("form_name")->getValue());
                    $tpl->set_var("user_data_label", ffTemplate::_get_word_by_code("user_additionaldata_" . preg_replace('/[^a-zA-Z0-9]/', '', $db_user_menu->getField("form_name")->getValue())));
                    $tpl->parse("SezManageAdditionaldata", true);
                }
            } while($db_user_menu->nextRecord());
        }
        if($UserID !== null) {
			if(check_function("system_get_sections"))
        		$block_vgallery = system_get_block_type("virtual-gallery");		        
        
			$sSQL = "
    			SELECT 
    				" . CM_TABLE_PREFIX . "mod_security_users.username AS username
	                , vgallery.name AS vgallery_name
                    , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                , users_rel_vgallery.cascading AS cascading
	                , users_rel_vgallery.request AS request
			        , IF(NOT(users_rel_vgallery.cascading > 0) 
		                , (SELECT 
                                REPLACE(CONCAT(IF(vgallery_user.parent = '/', '', vgallery_user.parent), '/', vgallery_user.name) 
                                    , CONCAT('/', layout.value)
                                        , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
                                            , 'TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))'
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
                                            AND layout.ID_type = " . $db_user_menu->toSql($block_vgallery["ID"], "Number") . "
                                            AND vgallery_user.parent LIKE CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params), '%')
                                    INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
	        				    WHERE vgallery_user.parent LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%') 
                                    AND 
                                    (
                                        vgallery_user.name = " . $db_user_menu->toSql(ffCommon_url_rewrite($UserID)) . "
                                    OR    
                                        vgallery_user.owner = " . $db_user_menu->toSql($UserNID, "Number") . "
                                    )
                                LIMIT 1
	        			)
	        			, ''
			        ) AS real_request
	            FROM " . CM_TABLE_PREFIX . "mod_security_users 
	            	INNER JOIN users_rel_vgallery ON users_rel_vgallery.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
	                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = users_rel_vgallery.ID_nodes
	                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	            WHERE
	                 " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_user_menu->toSql($UserNID, "Number") . "
	            ORDER BY users_rel_vgallery.request DESC, users_rel_vgallery.`order`, vgallery_nodes.`order`";
			$db_user_menu->query($sSQL);
			if($db_user_menu->nextRecord()) {
				do {
	                $count_item++;
					if(strlen($db_user_menu->getField("real_request", "Text", true))) {
						$add_vgallery_path = $db_user_menu->getField("real_request", "Text", true);
						
						$tpl->set_var("item_path", FF_SITE_PATH . $add_vgallery_path);
						$tpl->set_var("user_data", "");
					} else {
						if(!$db_user_menu->getField("cascading", "Number", true)) {
							$add_vgallery_path = "/modify";
						} else {
							$add_vgallery_path = ""; //"/" . $db_user_menu->getField("username", "Text", true);
						}

						if($source_user_path === NULL) {  
						    $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_VGALLERY . $add_vgallery_path);
						} else {
			    			$tpl->set_var("item_path", $source_user_path . "/vgallery" . $add_vgallery_path);
						}
	                	$tpl->set_var("user_data", $db_user_menu->getField("full_path", "Text", true));
					}
                    
                    $tpl->set_var("vgallery_class", preg_replace('/[^a-zA-Z0-9]/', '', ffTemplate::_get_word_by_code($db_user_menu->getField("full_path", "Text", true))));
	                $tpl->set_var("user_data_label", ffTemplate::_get_word_by_code("user_vgallery_" . preg_replace('/[^a-zA-Z0-9]/', '', $db_user_menu->getField("full_path", "Text", true))));
	                $tpl->parse("SezManageAdditionaldata", true);
				} while($db_user_menu->nextRecord());
			}            
		}

        if(isset($globals->custom_data) && array_key_exists("user", $globals->custom_data) && count($globals->custom_data["user"])) {
            foreach($globals->custom_data["user"] AS $custom_data_path => $custom_data_name) {
                $count_item++;

                $tpl->set_var("custom_class", ffCommon_url_rewrite($custom_data_name));
                $tpl->set_var("custom_url", $custom_data_path);
                $tpl->set_var("custom_name", ffTemplate::_get_word_by_code("cu_" . $custom_data_name));
                $tpl->parse("SezCustomData", true); 
            }
        }
    } else {
        if ($layout_settings["AREA_USER_SHOW_ECOMMERCE_CART"] && AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_SHOW_CART && $enable_ecommerce) {
            $count_item++;
            
            $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_CART);
            if($globals->ecommerce["unic_id"]) {
            	$tpl->set_var("item_rel", $globals->ecommerce["unic_id"]);
            	$tpl->parse("SezEcommerceCartRel", false);
			} else {
				$tpl->set_var("SezEcommerceCartRel", "");
			}
            $tpl->parse("SezEcommerceCart", false);
        } else {
            $tpl->set_var("SezEcommerceCart", "");
        }
    }

    if($count_item)
        $buffer = $tpl->rpparse("main", false);
    
    return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "post" 		=> $block["tpl"]["post"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
	);
}
