<?php
use_cache(false);

require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if(strpos($globals->user_path, VG_SITE_USER) === 0) {
	$public_area = true;
} else {
	if(!AREA_USERS_SHOW_MODIFY) {
	    ffRedirect(FF_SITE_PATH . substr($globals->user_path, 0, strpos($globals->user_path . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	} else {
		$public_area = false;
	}
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*  
							, IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
								, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname)
									, " . CM_TABLE_PREFIX . "mod_security_users.username
									, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname)
								)
								, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.billreference)
							) AS reference
						FROM " . CM_TABLE_PREFIX . "mod_security_users
						WHERE " . ($public_area 
							? CM_TABLE_PREFIX . "mod_security_users.public > 0"
							: "1"
						) . "
                        LIMIT 1"; 
$db_gallery->query($sSQL);
if($db_gallery->nextRecord())
{
	$layout["prefix"] = "UL";
	$layout["ID"] = 0;
	$layout["title"] = "Users";
	$layout["type"] = "ADMIN";
	$layout["location"] = "Content";
	$layout["width"] = $sections["Content"];
	$layout["visible"] = NULL;
	if(check_function("get_layout_settings"))
		$layout["settings"] = get_layout_settings(NULL, "ADMIN");   


	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "Users";
	$oGrid->title = ffTemplate::_get_word_by_code("users");
	$oGrid->user_vars["public"] = $public_area;
	$oGrid->addEvent("on_before_parse_row", "Users_on_before_parse_row");
         
	if(AREA_USER_SHOW_CUSTOM_FIELD) {
           $sSQL = "SELECT DISTINCT anagraph_fields.ID  
				FROM anagraph_fields 
					INNER JOIN module_register ON module_register.ID_anagraph_type = anagraph_fields.ID_type
					INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register = module_register.ID
				WHERE 1";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$count_anagraph_field = $db_gallery->numRows();
		}
                if($count_anagraph_field) {
			$db_gallery->query("SELECT DISTINCT anagraph_fields.*
									, anagraph_fields.name AS name
		                            , extended_type.name AS extended_type
		                            , extended_type.ff_name AS ff_extended_type 
		                        FROM 
		                            anagraph_fields
									INNER JOIN module_register ON module_register.ID_anagraph_type = anagraph_fields.ID_type
									INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register = module_register.ID
				                    LEFT JOIN extended_type ON extended_type.ID = anagraph_fields.ID_extended_type
		                        WHERE anagraph_fields.enable_in_grid > 0
		                        GROUP BY anagraph_fields.ID
		                        ORDER BY anagraph_fields.`order_thumb`, anagraph_fields.name
		                        ");
		} else {
			$db_gallery->query("SELECT DISTINCT module_register_fields.*
									, module_register_fields.name AS name
		                            , extended_type.name AS extended_type
		                            , extended_type.ff_name AS ff_extended_type 		
				                FROM 
				                    module_register_fields
									INNER JOIN module_register ON module_register.ID = module_register_fields.ID_module
									INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register = module_register.ID
				                    LEFT JOIN extended_type ON extended_type.ID = module_register_fields.ID_extended_type
				                WHERE module_register_fields.enable_in_grid > 0
				                GROUP BY module_register_fields.ID
				                ORDER BY module_register_fields.`order`, module_register_fields.name
				                ");
		}
                if($db_gallery->nextRecord()) {
		    $arrFormField = array();
		    $sSQL_field = "";
		    do {
		        $key_field = md5($db_gallery->getField("name", "Text")->getValue());
		        
		        if(strlen($arrFormField[$key_field]["ID"]))
		            $arrFormField[$key_field]["ID"] .=", ";

		        $arrFormField[$key_field]["ID"] .= "'" . $db_gallery->getField("name", "Text", true) . "'";
		        $arrFormField[$key_field]["name"] =  preg_replace('/[^a-zA-Z0-9]/', '', strtolower($db_gallery->getField("name", "Text")->getValue()));
		        $arrFormField[$key_field]["extended_type"] = $db_gallery->getField("extended_type", "Text")->getValue();
		        $arrFormField[$key_field]["ff_extended_type"] = $db_gallery->getField("ff_extended_type", "Text")->getValue();
		    } while($db_gallery->nextRecord());
                    
		    $sSQL_field = "";
		    if(is_array($arrFormField) && count($arrFormField)) {
		        foreach($arrFormField AS $$arrFormField_key => $arrFormField_value) {
		            $sSQL_field .= ", (SELECT 
			                                GROUP_CONCAT(" . CM_TABLE_PREFIX . "mod_security_users_fields.value SEPARATOR '')
			                            FROM
			                                " . CM_TABLE_PREFIX . "mod_security_users_fields
			                            WHERE
			                                " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . CM_TABLE_PREFIX . "mod_security_users.ID
			                                AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field IN (" . $arrFormField_value["ID"] . ")
		                            ) AS " . $db_gallery->tosql($arrFormField_value["name"]);
		        }
		    }
		}
	}
        
	if($public_area) {
		if(AREA_USERS_SHOW_MODIFY) {
			$tpl = ffTemplate::factory(get_template_cascading(VG_SITE_CART, "admin_toolbar.html"));
			$tpl->load_file("admin_toolbar.html", "main");
			
			$admin_menu["admin"]["unic_name"] = $layout["prefix"] . $layout["ID"];
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
			
			$admin_menu["sys"]["path"] = VG_SITE_USER;
			$admin_menu["sys"]["location"] = $layout["location"];
			$admin_menu["sys"]["type"] = "admin_toolbar";
			$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];

			if(strlen($layout["settings"]["ADMIN_TOOLBAR_MENU_PLUGIN"])) {
			    setJsRequest($layout["settings"]["ADMIN_TOOLBAR_MENU_PLUGIN"]);
			    $tpl->set_var("class_plugin", $layout["settings"]["ADMIN_TOOLBAR_MENU_PLUGIN"]);

			    $serial_admin_menu = json_encode($admin_menu);
			    if(check_function("normalize_url")) {
		    		$tpl->set_var("admin_menu", normalize_url(FF_SITE_PATH . VG_SITE_FRAME . VG_SITE_USER . "?sid=" . set_sid($serial_admin_menu, $admin_menu["admin"]["unic_name"])));
				}
			    $tpl->parse("SezAdminAjax", false); 
			    $tpl->set_var("SezAdminNoAjax", ""); 
			} else {
		        $tpl->set_var("class_plugin", "admin_toolbar");
			    if(check_function("process_admin_menu"))
		    		$tpl->set_var("admin_menu", process_admin_menu($admin_menu["admin"], "toolbar"));
			    $tpl->set_var("SezAdminAjax", ""); 
			    $tpl->parse("SezAdminNoAjax", false); 
			}
			$cm->oPage->addContent($tpl->rpparse("main", false), null, "adminUserList");
		} 
		
		$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.* 
								, IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
									, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname = '')
										, " . CM_TABLE_PREFIX . "mod_security_users.username
										, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname)
									)
									, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.billreference)
								) AS reference
							FROM " . CM_TABLE_PREFIX . "mod_security_users
							WHERE " . CM_TABLE_PREFIX . "mod_security_users.public > 0
							[WHERE]
		                    [HAVING]
		                    [ORDER]";
		if($public_area) {
			$oGrid->order_default = "reference";
		} else {
			$oGrid->order_default = "register-ID";
		}
		$oGrid->display_edit_bt = true;
		$oGrid->display_edit_url = false;
		$oGrid->display_delete_bt = false;
		$oGrid->display_new = false;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path;
		$oGrid->record_id = "UserModify";
		//$oGrid->use_search = false;
		$oGrid->ajax_search = true;
	} else {
		$user_permission = get_session("user_permission"); 
		$str_ID_groups = implode(",", $user_permission["groups"]);
                
		$oGrid->full_ajax = true;
		/*
		$oGrid->source_SQL = "
							SELECT tbl_src.avatar AS users_avatar
								, tbl_src.* 
								$sSQL_field
							FROM
							(
								(
									SELECT * 
				                    FROM
				                        (
				                            SELECT 
											    " . CM_TABLE_PREFIX . "mod_security_users.*
												, IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
													, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname = '')
													    , " . CM_TABLE_PREFIX . "mod_security_users.username
													    , CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
													)
													, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.billreference, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
												) AS reference
											    , " . CM_TABLE_PREFIX . "mod_security_groups.name AS real_primary_gid
				                                , MAX(" . CM_TABLE_PREFIX . "mod_security_groups.level) AS max_level
				                                , '0' AS priority
										    FROM
				                                " . CM_TABLE_PREFIX . "mod_security_users_rel_groups 
				                                INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
											    INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
				                            WHERE " . CM_TABLE_PREFIX . "mod_security_users.username <> '" . MOD_SEC_GUEST_USER_NAME . "'
				                            GROUP BY " . CM_TABLE_PREFIX . "mod_security_users.ID, " . CM_TABLE_PREFIX . "mod_security_groups.gid
				                            ORDER BY max_level DESC, " . CM_TABLE_PREFIX . "mod_security_users.username
				                        ) AS tbl_src
				                    WHERE
				                        (   
				                            SELECT MAX(" . CM_TABLE_PREFIX . "mod_security_groups.level) AS user_max_level 
				                            FROM " . CM_TABLE_PREFIX . "mod_security_groups
				                            WHERE " . CM_TABLE_PREFIX . "mod_security_groups.gid IN (" . $db_gallery->toSql($str_ID_groups, "Text", false) . ")
				                            GROUP BY " . CM_TABLE_PREFIX . "mod_security_groups.gid
	                                        ORDER BY user_max_level DESC
	                                        LIMIT 1
				                        ) >= max_level
			                    )
			                    UNION
			                    (
				                    SELECT 
										" . CM_TABLE_PREFIX . "mod_security_users.*
										, IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
											, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname = '')
												, " . CM_TABLE_PREFIX . "mod_security_users.username
												, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
											)
											, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.billreference, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
										) AS reference
										, '" . ffTemplate::_get_word_by_code("group_no_set") . "' AS real_primary_gid
				                        , 0 AS max_level
				                        , '1' AS priority
									FROM
										" . CM_TABLE_PREFIX . "mod_security_users
									WHERE
										" . CM_TABLE_PREFIX . "mod_security_users.ID NOT IN ( SELECT DISTINCT uid FROM " . CM_TABLE_PREFIX . "mod_security_users_rel_groups )
			                    )
		                    ) AS tbl_src
		                    [AND][WHERE]
		                    GROUP BY ID
		                    [HAVING]
		                    ORDER BY priority DESC [COLON] 
		                    [ORDER]";
		 * 
		 */
		                
		$oGrid->source_SQL = "
			SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
				$sSQL_field
				, IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
					, IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname = '')
						, " . CM_TABLE_PREFIX . "mod_security_users.username
						, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
					)
					, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.billreference, ' (', " . CM_TABLE_PREFIX . "mod_security_users.username, ')')
				) AS reference
				, " . CM_TABLE_PREFIX . "mod_security_groups.name AS real_primary_gid
			FROM " . CM_TABLE_PREFIX . "mod_security_users
                            INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_users.primary_gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
			WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID <> " . $db_gallery->toSql(MOD_SEC_GUEST_USER_ID, "Number") . "
			[AND][WHERE]
			[HAVING]
			[ORDER]
		";
		if($public_area) {
			$oGrid->order_default = "reference";
		} else {
			$oGrid->order_default = "register-ID";
		}
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
		$oGrid->record_id = "UserModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_edit_bt = false;
		$oGrid->display_edit_url = AREA_USERS_SHOW_MODIFY;
		$oGrid->display_delete_bt = AREA_USERS_SHOW_MODIFY;
		$oGrid->display_new = AREA_USERS_SHOW_MODIFY;
		
	}


        
	// Ricerca

	// Visualizzazione
	if($public_area) {
	    if(ENABLE_AVATAR_SYSTEM) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "avatar";
	        $oField->label = ffTemplate::_get_word_by_code("users_avatar");
	        $oField->encode_entities = false;
	        $oGrid->addContent($oField);
	    }
	        
		$oField = ffField::factory($cm->oPage);
		$oField->id = "reference";
		$oField->label = ffTemplate::_get_word_by_code("users_reference");
		$oField->encode_entities = false;
		$oGrid->addContent($oField);

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "profile";
		$oButton->action_type = "gotourl";
		$oButton->url = "";
		$oButton->aspect = "link";
		$oButton->image = "informations.png";
		$oButton->label = ffTemplate::_get_word_by_code("users_profile");
		$oButton->template_file = "ffButton_link_image.html";
		$oGrid->addGridButton($oButton);
			
		if($layout["settings"]["AREA_USER_SHOW_COMMENT"]) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "comment";
			$oButton->action_type = "gotourl";
			$oButton->url = "";
			$oButton->aspect = "link";
			$oButton->image = "informations.png";
			$oButton->label = ffTemplate::_get_word_by_code("users_comment");
			$oButton->template_file = "ffButton_link_image.html";
			$oGrid->addGridButton($oButton);
		}
		if(ENABLE_MULTICART && $layout["settings"]["AREA_USER_SHOW_WISHLIST"]) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "wishlist";
			$oButton->action_type = "gotourl";
			$oButton->url = "";
			$oButton->aspect = "link";
			$oButton->image = "informations.png";
			$oButton->label = ffTemplate::_get_word_by_code("users_wishlist");
			$oButton->template_file = "ffButton_link_image.html";
			$oGrid->addGridButton($oButton);
		}
	} else {
		// Chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "register-ID";
		$oField->data_source = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->label = ffTemplate::_get_word_by_code("users_id");
		$oGrid->addContent($oField);
	    
	    if(ENABLE_AVATAR_SYSTEM && AREA_USER_SHOW_AVATAR) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "avatar";
	        $oField->label = ffTemplate::_get_word_by_code("users_avatar");
	        $oField->encode_entities = false;
	        $oGrid->addContent($oField);
	    }
	    
	    if(AREA_USER_SHOW_REFERENCE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "reference";
			$oField->label = ffTemplate::_get_word_by_code("users_username");
			$oField->encode_entities = false;
			$oGrid->addContent($oField);
		}

		if(AREA_GROUPS_SHOW_MODIFY) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "real_primary_gid";
			$oField->label = ffTemplate::_get_word_by_code("users_real_primary_gid");
			$oGrid->addContent($oField, true, "last");
		}
		if(AREA_USER_SHOW_EMAIL) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "email";
			$oField->label = ffTemplate::_get_word_by_code("users_email");
			$oGrid->addContent($oField);
		}
		
		if(AREA_USER_SHOW_TEL) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "tel";
			$oField->label = ffTemplate::_get_word_by_code("users_tel");
			$oGrid->addContent($oField);
		}


		
		if(AREA_USER_SHOW_BILL_CF) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billcf";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_cf");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_BILL_PIVA) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billpiva";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_piva");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_BILL_ADDRESS) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billaddress";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_address");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_BILL_CAP) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billcap";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_cap");
			$oGrid->addContent($oField);
		}
		
		if(AREA_USER_SHOW_BILL_TOWN) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billtown";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_town");
			$oGrid->addContent($oField);
		}
		
		if(AREA_USER_SHOW_BILL_PROVINCE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billprovince";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_province");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_BILL_STATE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "billstate";
			$oField->label = ffTemplate::_get_word_by_code("users_bill_state");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_REFERENCE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingreference";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_reference");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_ADDRESS) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingaddress";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_address");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_CAP) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingcap";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_cap");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_TOWN) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingtown";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_town");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_PROVINCE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingprovince";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_province");
			$oGrid->addContent($oField);
		}

		if(AREA_USER_SHOW_SHIPPING_STATE) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingstate";
			$oField->label = ffTemplate::_get_word_by_code("users_shipping_state");
			$oGrid->addContent($oField);
		}


		
		if(is_array($arrFormField) && count($arrFormField)) { 
		    foreach($arrFormField AS $field_key => $field_value) {
		        $field_name = $field_value["name"];

		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $field_name;
		        $oField->container_class = $field_name;
		        $oField->label = ffTemplate::_get_word_by_code("user_" . $field_name);
			
		        if($field_value["extended_type"] == "Image"
		            || $field_value["extended_type"] == "Upload"
		            || $field_value["extended_type"] == "UploadImage"
		        ) {
		            $oField->control_type = "picture_no_link";
		            $oField->extended_type = "File";
		            $oField->file_storing_path = DISK_UPDIR;
		            $oField->file_show_delete = false;
		            $oField->file_full_path = true;
		            $oField->file_check_exist = false;
		            $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
		            $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
		//            $oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
		//            $oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
		        }
		        $oField->base_type = $field_value["ff_extended_type"];
		        $oField->encode_entities = false;
		        $oField->src_having = true;

		        $oGrid->addContent($oField); 
		    } 
		}
		
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "status";
		//$oButton->class = "ico-visible";
		//$oButton->action_type = "gotourl";
		//$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/additionaldata?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
		$oButton->aspect = "link";
		//$oButton->image = "informations.png";
		//$oButton->label = ffTemplate::_get_word_by_code("users_status");
		$oButton->display_label = false;
		$oGrid->addGridButton($oButton);

		if(AREA_SHOW_ECOMMERCE) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "anagraph";
			$oButton->class = "icon ico-anagraph";
			$oButton->action_type = "gotourl";
			$oButton->aspect = "link";
			//$oButton->image = "informations.png";
			$oButton->label = ffTemplate::_get_word_by_code("users_anagraph");
			$oButton->display_label = false;
			$oGrid->addGridButton($oButton);
		}

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "additionaldata";
		$oButton->class = "icon ico-customdata";
		$oButton->action_type = "gotourl";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/additionaldata?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
		$oButton->aspect = "link";
		//$oButton->image = "informations.png";
		$oButton->label = ffTemplate::_get_word_by_code("users_additionaldata");
		$oButton->display_label = false;
		$oGrid->addGridButton($oButton);

		if(AREA_SETTINGS_SHOW_MODIFY) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "permissions";
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . VG_SITE_PERMISSION . "/modify?uid=[register-ID_VALUE]&ret_url=" . urlencode($cm->oPage->getRequestUri());
			$oButton->aspect = "link";
			//$oButton->image = "permissions.png";
			$oButton->label = ffTemplate::_get_word_by_code("users_permission");
			$oButton->display_label = false;
			$oGrid->addGridButton($oButton);
		}
	}
	$cm->oPage->addContent($oGrid);
} else {
	if(check_function("process_html_notfound"))
		process_html_notfound($globals->user_path);
}

function Users_on_before_parse_row($component) {
    global $site_path;
    global $page_path;
    global $disk_path;
    
    $db = ffDB_Sql::factory();

	if($component->user_vars["public"]) {
		if(isset($component->grid_buttons["profile"])) {
			$component->grid_buttons["profile"]->url = FF_SITE_PATH . VG_SITE_PROFILE . "/" . $component->db[0]->getField("username", "Text", true) . "?ret_url=" . urlencode($component->parent[0]->getRequestUri());
		}

		if(isset($component->grid_buttons["comment"])) {
			$component->grid_buttons["comment"]->url = FF_SITE_PATH . VG_SITE_PROFILE . "/comment/" . $component->db[0]->getField("username", "Text", true) . "?ret_url=" . urlencode($component->parent[0]->getRequestUri());
		}
		if(isset($component->grid_buttons["wishlist"])) {
			$component->grid_buttons["wishlist"]->url = FF_SITE_PATH . VG_SITE_WISHLIST . "/" . $component->db[0]->getField("username", "Text", true) . "?ret_url=" . urlencode($component->parent[0]->getRequestUri());
		}
	} else {
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID AS uid, module_form.name AS form_name 
	            FROM " . CM_TABLE_PREFIX . "mod_security_users 
	                LEFT JOIN users_rel_module_form ON users_rel_module_form.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
	                LEFT JOIN module_form ON module_form.ID = users_rel_module_form.ID_module
	            WHERE
	                " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($component->key_fields["register-ID"]->value) . "
	                AND ISNULL(module_form.name) = 0";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
			$component->grid_buttons["additionaldata"]->url = $component->parent[0]->site_path . $component->parent[0]->page_path . "/additionaldata?keys[register-ID]=" . $component->key_fields["register-ID"]->getValue() . "&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            $component->grid_buttons["additionaldata"]->class = "icon ico-customdata";
		} else {
			$component->grid_buttons["additionaldata"]->url = "javascript:void(0);";
            $component->grid_buttons["additionaldata"]->class = "icon ico-no-customdata";
		}
	    if(AREA_SHOW_ECOMMERCE) {
		    $sSQL = "SELECT anagraph.ID AS ID
		            FROM anagraph
	            		INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
		            WHERE
		                " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($component->key_fields["register-ID"]->value);
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				$component->grid_buttons["anagraph"]->url = $component->parent[0]->site_path . "/manage/anagraph/all/modify?keys[anagraph-ID]=" . $db->getField("ID", "Number", true) . "&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            $component->grid_buttons["anagraph"]->class = "icon ico-anagraph";
			} else {
				$component->grid_buttons["anagraph"]->url = "javascript:void(0);";
	            $component->grid_buttons["anagraph"]->class = "icon ico-no-anagraph";
			}
		}
	    if(isset($component->grid_buttons["status"])) {
		    if($component->db[0]->getField("status", "Number", true)) {
	    		$component->row_class = "on";
		        $component->grid_buttons["status"]->label = ffTemplate::_get_word_by_code("users_status_visible");
                $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye", "icon");
                $component->grid_buttons["status"]->icon = null;
	            $component->grid_buttons["status"]->action_type = "submit"; 
	            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . "setstatus=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields : [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields : [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["visible"]->action_type = "gotourl";
	                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setstatus=0&frmAction=setstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            }   
		    } else {
	    		$component->row_class = "off";
	            $component->grid_buttons["status"]->label = ffTemplate::_get_word_by_code("users_status_not_visible");
                $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
                $component->grid_buttons["status"]->icon = null;
	            $component->grid_buttons["status"]->action_type = "submit";     
	            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . "setstatus=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields : [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields : [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["visible"]->action_type = "gotourl";
	                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setstatus=1&frmAction=setstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            }    
		    }
		}
	}
	
    if(isset($component->grid_fields["avatar"])) { 
    	if(check_function("get_user_avatar"))
    		$component->grid_fields["avatar"]->setValue(get_user_avatar($component->db[0]->getField("avatar", "Text", true), true, $component->db[0]->getField("email", "Text", true)));
		/*
        $avatar = $component->db[0]->getField("users_avatar", "Text", true);
        if(strlen($avatar)
            && (substr(strtolower($avatar), 0, 7) == "http://"
                || substr(strtolower($avatar), 0, 8) == "https://"
            )
        ) {
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . $avatar . '" />');
        } else if(strlen($avatar) && file_exists(DISK_UPDIR . $avatar) && is_file(DISK_UPDIR . $avatar)) { 
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar" . $avatar . '" />');
        } else {
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif" . '" />');
        }
        */
    }
}
?>
