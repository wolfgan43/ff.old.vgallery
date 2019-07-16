<?php
function process_addon_comment_view($user_path, $ret_url, $tbl_src, $ID_node, $ID_form, $uid = null, $disable_control = true, $layout = null) {
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

	$db = ffDB_Sql::factory();
	$db_field = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
	
	//$tpl_data["custom"] = "comment.html";
	$tpl_data["base"] = "comment.html";
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");
	
//	$tpl = ffTemplate::factory(get_template_cascading($user_path, "comment.html", "", null, $layout["location"]));
//	$tpl->load_file("comment.html", "main");
    
    /**
    * Admin Father Bar
    */
    if(Auth::env("AREA_COMMENT_SHOW_MODIFY") && !$disable_control) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
            $admin_menu["admin"]["setting"] = "";
        }
        
        $admin_menu["sys"]["path"] = $user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
        $admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
    }

	/**
	* Process Block Header
	*/		    
    if(check_function("set_template_var"))
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl);


    $tpl->set_var("page", ffCommon_specialchars($_REQUEST[$unic_id . "_page"]));
    $tpl->set_var("rec_per_page", ffCommon_specialchars($_REQUEST[$unic_id . "_records_per_page"]));
    
    if($layout_settings["AREA_COMMENT_USE_PRIMARY_GROUP_PERMISSION"]) {
        $user = Auth::get("user");

        $str_allowed_groups = $user->acl;


        if(strlen($layout_settings["AREA_COMMENT_EXCLUDE_GROUP_PERMISSION"])) {
            $str_exclude_groups = "";
            $arrExcludeGroup = explode(",", $layout_settings["AREA_COMMENT_EXCLUDE_GROUP_PERMISSION"]);
            if(is_array($arrExcludeGroup) && count($arrExcludeGroup)) {
                foreach($arrExcludeGroup AS $arrExcludeGroup_value) {
                    if(strlen($str_exclude_groups))
                        $str_exclude_groups .= ",";
                        
                    $str_exclude_groups .= "'" . trim($arrExcludeGroup_value) . "'";
                }
            }
            
            if(strlen($str_exclude_groups)) {
                $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.*
                        FROM " . CM_TABLE_PREFIX . "mod_security_groups
                        WHERE " . CM_TABLE_PREFIX . "mod_security_groups.gid IN (" . $db->toSql($str_allowed_groups, "Text", false) . ")
                            AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN (" . $str_exclude_groups . ")";
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $str_allowed_groups = "";
                }
            }
        }
    }
     
    if(!$layout_settings["AREA_HIDE_COMMENT"]) {
	    $sSQL = "SELECT module_form_nodes.ID AS ID_form_nodes
                    , module_form_nodes.hide AS hide
				    , module_form_nodes.name AS lastupdate
				    , comment_rel_module_form.ID_nodes AS ID_node
				    , comment_rel_module_form.ID_module AS ID_form
				    , comment_rel_module_form.path AS path
				    , comment_rel_module_form.nick AS nick
				    , comment_rel_module_form.email AS email
				    , comment_rel_module_form.website AS website
				    , " . CM_TABLE_PREFIX . "mod_security_users.ID AS uid
				    , " . CM_TABLE_PREFIX . "mod_security_users.username AS username
				    , " . CM_TABLE_PREFIX . "mod_security_users.email AS user_email
				    , " . CM_TABLE_PREFIX . "mod_security_users.public AS public
                    , " . CM_TABLE_PREFIX . "mod_security_users.avatar AS avatar
			    FROM comment_rel_module_form 
				    INNER JOIN module_form_nodes ON module_form_nodes.ID = comment_rel_module_form.ID_form_node
				    INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = comment_rel_module_form.uid
			    WHERE " . ($uid === null
						    ? "	comment_rel_module_form.tbl_src = " . $db->toSql($tbl_src, "Text") . "
							    AND comment_rel_module_form.ID_nodes = " . $db->toSql($ID_node, "Number") . "
							    AND comment_rel_module_form.ID_module = " . $db->toSql($ID_form, "Number")
						    : " comment_rel_module_form.uid = " . $db->toSql($uid, "Number")
					    ) . "
                        " . ($layout_settings["AREA_COMMENT_USE_PRIMARY_GROUP_PERMISSION"] && strlen($str_allowed_groups)
                            ? " AND module_form_nodes.uid IN (
                                    SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_users.ID
                                    FROM " . CM_TABLE_PREFIX . "mod_security_users
                                        INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
                                        INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups.gid IN (" . $db->toSql($str_allowed_groups, "Text", false) . ")
                                )"
                            : ""
                        ). "
                        AND (NOT(module_form_nodes.hide > 0)
                            " . (Auth::isGuest()
                                    ? " OR " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql(Auth::get("user")->id, "Number")
                                    : ""
                                ) . "
                        )
			    GROUP BY ID_form_nodes
			    ORDER BY lastupdate " . ($layout_settings["AREA_COMMENT_ORDER_ASC"] ? " ASC " : " DESC ");
	    $db->query($sSQL);
	    if($db->nextRecord()) { 
            if($tbl_src == "vgallery") {
                if(check_function("get_file_properties"))
                    $file_properties = get_file_properties($user_path, $tbl_src, "thumb", $layout["ID"]);
                    
                $real_rec_per_page = $file_properties["rec_per_page"];
                $real_rec_per_page_all = $file_properties["rec_per_page_all"];
                $real_npage_per_frame = $file_properties["npage_per_frame"];
                $real_direction_arrow = $file_properties["direction_arrow"];
                $real_frame_arrow = $file_properties["frame_arrow"];
                $real_custom_page = $file_properties["custom_page"];
                $real_tot_elem = $file_properties["tot_elem"];
                $real_frame_per_page = $file_properties["frame_per_page"];
                $real_pagenav_location = $file_properties["pagenav_location"];
            } else {
                $real_rec_per_page = $layout_settings["AREA_COMMENT_REC_PER_PAGE"];
                $real_rec_per_page_all = false;
                $real_npage_per_frame = 9;
                $real_direction_arrow = true;
                $real_frame_arrow = true;
                $real_custom_page = false;
                $real_tot_elem = false;
                $real_frame_per_page = false;
                $real_pagenav_location = "bottom";
            } 

	        $page_nav = ffPageNavigator::factory($cm->oPage, FF_DISK_PATH, FF_SITE_PATH, null, $cm->oPage->theme);
		    $page_nav->id = $unic_id;
		    if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
	            $page_nav->doAjax = true;
	        else
	            $page_nav->doAjax = false;
	            
            $page_nav->PagePerFrame = $real_npage_per_frame;
            
            $page_nav->nav_selector_elements = array(floor($real_rec_per_page / 2), $real_rec_per_page, $real_rec_per_page * 2);
            $page_nav->nav_selector_elements_all = $real_rec_per_page_all;

            $page_nav->display_prev = $real_direction_arrow;
            $page_nav->display_next = $real_direction_arrow;
            $page_nav->display_first = $real_direction_arrow;
            $page_nav->display_last = $real_direction_arrow;
            
            $page_nav->with_frames = $real_frame_arrow;
            $page_nav->with_choice = $real_custom_page;
            $page_nav->with_totelem = $real_tot_elem;
            $page_nav->nav_display_selector = $real_frame_per_page;

   	        $page_nav->prefix = $unic_id . "_";
	        $page_nav->page             = intval($_REQUEST[$page_nav->prefix . "page"]) > 0
	                                        ? $_REQUEST[$page_nav->prefix . "page"]
	                                        : 1;
	        $page_nav->records_per_page = intval($_REQUEST[$page_nav->prefix . "records_per_page"]) > 0
	                                        ? $_REQUEST[$page_nav->prefix . "records_per_page"]
	                                        : $real_rec_per_page;
	        
	        $tot_page = ceil($db->numRows() / $page_nav->records_per_page);

	        if ($page_nav->page >= $tot_page)
	            $page_nav->page = $tot_page;
		    
		    $db->jumpToPage($page_nav->page, $page_nav->records_per_page);
		    
		    $page_nav->num_rows = $db->numRows();  
		    
		    $count_items = 0;
		    $switch_style = false;
		    do {
			    $count_items++;

                if($db->getField("hide", "Number", true)) {
                    $tpl->set_var("strNotify", ffTemplate::_get_word_by_code("comment_is_hidden"));
                } else {
                    $tpl->set_var("strNotify", "");
                }
                
			    $ID_node = $db->getField("ID_node", "Number", true);
			    $ID_form = $db->getField("ID_form", "Number", true);
			    $ID_form_node = $db->getField("ID_form_nodes", "Number", true);
			    if($uid === null) {
				    if($db->getField("username", "Text", true) == Cms::env("MOD_AUTH_GUEST_USER_NAME")) {
					    $tpl->set_var("username", $db->getField("nick", "Text", true));
					    if($db->getField("website", "Text", true)) {
						    $tpl->set_var("url", $db->getField("website", "Text", true));
						    $tpl->parse("SezUsernameLink", false);
						    $tpl->set_var("SezUsernameNoLink", "");
					    } else {
						    $tpl->set_var("SezUsernameLink", "");
						    $tpl->parse("SezUsernameNoLink", false);
					    }
					    $tpl->set_var("SezAvatar", "");
				    } else {
                        $username = $db->getField("username", "Text", true);
					    if($layout_settings["AREA_COMMENT_SHOW_AVATAR"]) {
                            $tpl->set_var("show_thumb", Auth::getUserAvatar(null, $db->getField("avatar", "Text", true)));
                            $tpl->set_var("alt_name", $username);
                            $tpl->parse("SezAvatar", false);


					    } else {
						    $tpl->set_var("SezAvatar", "");
					    }
					    
					    $tpl->set_var("username", $username);
					    if($db->getField("public", "Number", true) || Auth::get("user")->id == $db->getField("uid", "Number", true)) {
						    $tpl->set_var("url", FF_SITE_PATH .  USER_RESTRICTED_PATH . "/" . $username . "?ret_url=" . urlencode($ret_url));
						    $tpl->parse("SezUsernameLink", false);
						    $tpl->set_var("SezUsernameNoLink", "");
					    } else {
						    $tpl->set_var("SezUsernameLink", "");
						    $tpl->parse("SezUsernameNoLink", false);
					    }
				    }
				    $tpl->parse("SezUser", false);
				    $tpl->set_var("SezPath", "");
			    } else {
				    if(strlen($db->getField("path", "Text", true))) {
					    $tpl->set_var("path", $db->getField("path", "Text", true));
					    
					    if(check_function("normalize_url")) {
					    	$tmp_meta_title = normalize_url($db->getField("path", "Text", true), HIDE_EXT, false, LANGUAGE_INSET, false, "meta_title", false);
						}
					    if(!strlen($tmp_meta_title)) {
						    $tmp_meta_title = explode("/", ltrim($db->getField("path", "Text", true), "/"));
						    krsort($tmp_meta_title);
						    $tmp_meta_title = implode(ffTemplate::_get_word_by_code("1separator_meta_title"), $tmp_meta_title);
					    
					    }
					    $tpl->set_var("meta_path", $tmp_meta_title);
					    $tpl->parse("SezPath", false);
				    } else {
					    $tpl->set_var("SezPath", "");
				    }
				    $tpl->set_var("SezAvatar", "");
				    $tpl->set_var("SezUser", "");
			    }
			    
			    $date = new ffData($db->getField("lastupdate", "Text", true), "Timestamp");
			    
			    $tpl->set_var("date", $date->getValue("DateTime", LANGUAGE_INSET));
			    
			    $tpl->set_var("SezCommentField", "");
			    $sSQL = "SELECT module_form_fields.*
									    , extended_type.name AS extended_type
                                        , extended_type.ff_name AS ff_extended_type
                                        , check_control.ff_name AS check_control
                                        , module_form_rel_nodes_fields.value AS data_value
                                        , module_form_fields_group.name AS `group_field`
                                        , module_form_fields_selection.ID_vgallery_fields AS ID_field
                                        , module_form_rel_nodes_fields.value AS value
                                    FROM 
                                        module_form_fields
                                        INNER JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_form_fields = module_form_fields.ID 
                                    	    AND module_form_rel_nodes_fields.ID_form_nodes = " . $db_field->toSql($ID_form_node, "Number") . "
                                        LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                                        LEFT JOIN check_control ON check_control.ID = module_form_fields.ID_check_control
                                        LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                                        LEFT JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields.ID_selection
                                    WHERE 
                                        module_form_fields.ID_module = " . $db_field->toSql($ID_form, "Number") . "
                                        AND NOT(module_form_fields.hide > 0)
                                    ORDER BY module_form_fields.`order`, module_form_fields.name";
			    $db_field->query($sSQL);
			    if($db_field->nextRecord()) {
				    do {
					    $exthended_type = $db_field->getField("extended_type", "Text", true);
					    $ff_exthended_type = $db_field->getField("ff_extended_type", "Text", true);
					    if($layout_settings["AREA_COMMENT_SHOW_LABEL"]) {
						    $tpl->set_var("label", ffTemplate::_get_word_by_code("view_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($db_field->getField("name", "Text", true)))));
						    $tpl->parse("SezLabel", false);
					    } else {
						    $tpl->set_var("SezLabel", "");
					    }
					    
					    switch($exthended_type) {
						    case "Link":	
							    $tpl->set_var("value", $db_field->getField("value", $ff_exthended_type)->getValue($ff_exthended_type, LANGUAGE_INSET));
							    $tpl->parse("SezLink", false);
							    $tpl->set_var("SezImage", "");
							    $tpl->set_var("SezText", "");
							    break;
						    case "Image":	
						    case "Upload":	
						    case "UploadImage":	
							    if(is_file(FF_DISK_UPDIR . $db_field->getField("value", $ff_exthended_type)->getValue($ff_exthended_type, LANGUAGE_INSET))) {
                                    $tpl->set_var("preview_value", CM_SHOWFILES . "/comment" . $db_field->getField("value", $ff_exthended_type)->getValue($ff_exthended_type, LANGUAGE_INSET));
						            $tpl->set_var("view_value", CM_SHOWFILES . $db_field->getField("value", $ff_exthended_type)->getValue($ff_exthended_type, LANGUAGE_INSET));
						            $tpl->parse("SezImage", false);
                                } else {
                                    $tpl->set_var("SezImage", "");
                                }
							    $tpl->set_var("SezLink", "");
                                $tpl->set_var("SezText", "");
							    break;
						    default:
							    $tpl->set_var("value", $db_field->getField("value", $ff_exthended_type)->getValue($ff_exthended_type, LANGUAGE_INSET));
							    $tpl->set_var("SezLink", "");
							    $tpl->set_var("SezImage", "");
							    $tpl->parse("SezText", false);
					    }

					    $tpl->parse("SezCommentField", true);
				    } while($db_field->nextRecord());	
			    }

		        $tpl->set_var("switch_style", ($switch_style
		                                            ? "positive"
		                                            : "negative"
		                                        )
		                                    );
		        if($switch_style)
		            $switch_style = false;
		        else 
		            $switch_style = true;

			    $tpl->parse("SezCommentRow", true);
		    } while($db->nextRecord() && ($count_items < $page_nav->records_per_page));
		    
	        if ($tot_page > 1) {
                $tpl->set_var("PageNavigator", $page_nav->process(false));
                if(strtolower($real_pagenav_location) == "top" || strtolower($real_pagenav_location) == "both") {
                    $tpl->parse("SezPageNavigatorTop", false);
                }
                if($real_pagenav_location == "" || strtolower($real_pagenav_location) == "bottom" || strtolower($real_pagenav_location) == "both") {
                    $tpl->parse("SezPageNavigatorBottom", false);
                }
	            $tpl->parse("SezPageNavigatorControl", false);
	            $tpl->parse("SezPageNavigator", false);
	        } else {
	            $tpl->set_var("SezPageNavigatorControl", "");
	            $tpl->set_var("SezPageNavigator", "");
	        }
	    } else {
            $strError = ffTemplate::_get_word_by_code("comment_not_found");
        }
        $tpl->parse("SezComment", false);
    } else {
        $tpl->set_var("SezComment", "");
    }
    
    if($layout_settings["AREA_SHOW_FB_COMMENT"]) {
        $cm->oPage->tplAddJs("FB", "all.js", "http://connect.facebook.net/" . strtolower(substr(FF_LOCALE, 0, 2)) . "_" . strtoupper(substr(FF_LOCALE, 0, 2)), false, false, null, true);

        $tpl->set_var("fb_app_id", Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_ID"));
        $tpl->set_var("domain_inset", DOMAIN_INSET);
        $tpl->set_var("user_path", $user_path);
        $tpl->parse("SezFBComment", false);
    } else {
        $tpl->set_var("SezFBComment", "");
    }
    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    } else {
        $tpl->set_var("SezError", "");
    }

	return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "content" 	=> $tpl->rpparse("main", false)
		, "post" 		=> $block["tpl"]["post"]
	);
}