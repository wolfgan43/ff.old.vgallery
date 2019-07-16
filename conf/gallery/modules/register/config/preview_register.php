<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

$db_gallery->query("SELECT module_register.*
                        FROM 
                            module_register
                        WHERE 
                            module_register.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                    );
if($db_gallery->nextRecord()) {
    $ID_register = $db_gallery->getField("ID")->getValue();
    $register_name = $db_gallery->getField("name")->getValue();
    $force_redirect = $db_gallery->getField("force_redirect")->getValue();
    $enable_privacy = $db_gallery->getField("enable_privacy")->getValue();
    $enable_require_note = $db_gallery->getField("enable_require_note")->getValue();
    $enable_newsletter = $db_gallery->getField("enable_newsletter")->getValue();
    $enable_general_data = $db_gallery->getField("enable_general_data")->getValue();
    $enable_bill_data = $db_gallery->getField("enable_bill_data")->getValue();
    $enable_ecommerce_data = $db_gallery->getField("enable_ecommerce_data")->getValue();
    $enable_manage_account = $db_gallery->getField("enable_manage_account")->getValue();
    $primary_gid = $db_gallery->getField("primary_gid")->getValue();
    $activation = $db_gallery->getField("activation")->getValue();
    if($activation) {
        $active = 0;        
    } else {
        $active = 1;
    }

    $generate_password = $db_gallery->getField("generate_password")->getValue();
    
    //$display_view_mode = $db_gallery->getField("display_view_mode")->getValue();

    $oRecord->id = "RegisterConfigPreview";
    $oRecord->class = "RegisterConfigPreview";
    /*if($display_view_mode) {
    	$oRecord->class .= " " . $display_view_mode;
    	
        if(!isset($globals))
             $globals = ffGlobals::getInstance("gallery");
        
        $cm->oPage->tplAddJs("jquery." . $display_view_mode, "jquery." . $display_view_mode . ".js", FF_THEME_DIR . "/library/plugins/jquery." . $display_view_mode);
        if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/javascript/" . "jquery." . $display_view_mode .  ".observe.js")) 
            $tmp_js_path = FF_THEME_DIR . "/" . $cm->oPage->theme . "/javascript" . "/" . "jquery." . $display_view_mode .  ".observe.js";
        else
            $tmp_js_path = FF_THEME_DIR . "/library/plugins/jquery." . $display_view_mode . "/" . "jquery." . $display_view_mode .  ".observe.js";
            
        $real_name = "";
        $real_path = "";
        $tmp_user_path = $globals->settings_path;
        do {
            if(strlen($tmp_user_path) && $tmp_user_path != "/")
                $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
            else
                $real_name = basename($tmp_js_path);

            if(file_exists($cm->oPage->disk_path . FF_THEME_DIR . "/" . $cm->oPage->theme . "/javascript/" . $real_name)) {
                $real_path = FF_THEME_DIR . "/" . $cm->oPage->theme . "/javascript";
                break;
            }
        } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
        if(!strlen($real_path)) {
            $real_name = basename($tmp_js_path);
            $real_path = ffCommon_dirname($tmp_js_path);
        }
        if(file_exists($cm->oPage->disk_path . $real_path . "/" . $real_name)) {
            $cm->oPage->tplAddJs(ffGetFilename($tmp_js_path)
                                        , $real_name
                                        , $real_path
                                        , false
                                        , false
                            );
        }
	}*/
    $oRecord->src_table = CM_TABLE_PREFIX . "mod_security_users"; 
    
    $oRecord->user_vars["ID_register"] = $ID_register;
    $oRecord->user_vars["activation"] = $activation;
    
    $oRecord->user_vars["enable_bill_data"] = $enable_bill_data;
    $oRecord->user_vars["enable_ecommerce_data"] = $enable_ecommerce_data;

    if(check_function("MD_register_on_done_action")) { //	    if(check_function("MD_register_on_check_after"))
    	$oRecord->addEvent("on_check_after", "MD_register_on_check_after");
    	$oRecord->addEvent("on_done_action", "MD_register_on_done_action");
	}
    /*
    if($force_redirect)
        $oRecord->ret_url = $force_redirect;
    else
        $oRecord->ret_url = stripslash($oRecord->user_vars["MD_chk"]["page_url"]) . "/end";
     */
    if($require_note)
        $oRecord->display_required_note = true;
    else 
        $oRecord->display_required_note = false;
        
    
    //$oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = true;
    $oRecord->buttons_options["insert"]["display"] = false;
    $oRecord->buttons_options["print"]["display"] = false;
     
    $oRecord->additional_fields["primary_gid"] = new ffData($primary_gid, "Number");
    $oRecord->additional_fields["status"] = new ffData($active, "Number");
    $oRecord->additional_fields["enable_bill_data"] = new ffData($enable_bill_data, "Number");
    $oRecord->additional_fields["enable_ecommerce_data"] = new ffData($enable_ecommerce_data, "Number");
    $oRecord->additional_fields["enable_manage"] = new ffData($enable_manage_account, "Number");
    $oRecord->additional_fields["ID_module_register"] = new ffData($ID_register, "Number");    

    $oRecord->addContent(null, true, "account");
    $oRecord->groups["account"] = array(
                                             "title" => ffTemplate::_get_word_by_code("register_account")
                                             , "cols" => 1
                                          );
    
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "register-ID";
    $obj_page_field->base_type = "Number";
    $obj_page_field->data_source = "uid";
    $oRecord->addKeyField($obj_page_field);
    
    if(!$disable_account_registration) {
	    $oRecord->addContent(null, true, "account");
	    $oRecord->groups["account"] = array(
	                                             "title" => ffTemplate::_get_word_by_code("register_account")
	                                             , "cols" => 1
	                                          );

	    $obj_page_field = ffField::factory($cm->oPage);
	    $obj_page_field->id = "username";
	    $obj_page_field->container_class = "register_username";
	    $obj_page_field->label = ffTemplate::_get_word_by_code("register_username");
	    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "username") . "_tip");   
	    $obj_page_field->required = true;
	    $oRecord->addContent($obj_page_field, "account");
	        
	    if($generate_password) {
	        $rnd_password = Auth::password();

	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "password";
	        $obj_page_field->container_class = "register_generate_password";
	        $obj_page_field->label = ffTemplate::_get_word_by_code("register_generate_password");
	        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmpassword") . "_tip");   
	        $obj_page_field->extended_type = "Password";
	        $obj_page_field->crypt_method = "mysql_password";
			if(Cms::env("ENABLE_PASSWORD_VALIDATOR")) {
			    $obj_page_field->addValidator("password");
			}
	        $obj_page_field->required = true;
	        $obj_page_field->control_type = "label";
	        $obj_page_field->default_value = new ffData($rnd_password, "Text");
	        $oRecord->addContent($obj_page_field, "account");
	    } else {
	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "password";
	        $obj_page_field->container_class = "register_password";
	        $obj_page_field->label = ffTemplate::_get_word_by_code("register_password");
	        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "password") . "_tip");   
	        $obj_page_field->extended_type = "Password";
	        $obj_page_field->crypt_method = "mysql_password";
			if(Cms::env("ENABLE_PASSWORD_VALIDATOR")) {
			    $obj_page_field->addValidator("password");
			}
	        $obj_page_field->required = true;
	        $oRecord->addContent($obj_page_field, "account");

	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "confirmpassword";
	        $obj_page_field->container_class = "register_confirm_password";
	        $obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_password");
	        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmpassword") . "_tip");   
	        $obj_page_field->extended_type = "Password";
	        $obj_page_field->compare = "password";
	        $obj_page_field->required = true;
	        $oRecord->addContent($obj_page_field, "account");    
	    }
	    $user_upload_path = "/[register-ID_VALUE]";
	} else {
		$user_upload_path = "";
	}

	if($enable_general_data) {
		$oRecord->addContent(null, true, "accountinfo");
		$oRecord->groups["accountinfo"] = array(
		                                         "title" => ffTemplate::_get_word_by_code("register_accountinfo")
		                                         , "cols" => 1
		                                      );

	    if(Cms::env("ENABLE_AVATAR_SYSTEM")) {
	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "avatar";
	        $obj_page_field->container_class = "register_avatar";
	        //$obj_page_field->label = ffTemplate::_get_word_by_code("user_account_avatar");
	        $obj_page_field->base_type = "Text";
	        $obj_page_field->extended_type = "File";
	        $obj_page_field->file_storing_path = FF_DISK_UPDIR . "/user/[register-ID_VALUE]";
	        $obj_page_field->file_temp_path = FF_DISK_UPDIR . "/user";
	        $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");
	        $obj_page_field->file_full_path = true;
	        $obj_page_field->file_check_exist = true;
	        $obj_page_field->file_normalize = true;
	        $obj_page_field->file_show_preview = true;

		    $obj_page_field->uploadify_model = "vertical";
		    $obj_page_field->uploadify_model_thumb = "avatar" . "vertical";

	        $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
	        $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/" . $obj_page_field->uploadify_model_thumb . "/[_FILENAME_]";
	//        $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
	//        $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/" . $obj_page_field->uploadify_model_thumb . "/[_FILENAME_]";

	        $obj_page_field->control_type = "file";
	        $obj_page_field->file_show_delete = true;
	        $obj_page_field->widget = "uploadify"; 
			if(check_function("set_field_uploader")) { 
				$obj_page_field = set_field_uploader($obj_page_field);
			}
			$obj_page_field->file_writable = false;	
			$obj_page_field->file_show_filename = false; 

	        $oRecord->addContent($obj_page_field, "accountinfo"); 
	    }
		                                      
		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "name";
		$obj_page_field->container_class = "register_name";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_name");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "name") . "_tip");   
		$obj_page_field->required = true;
		$oRecord->addContent($obj_page_field, "accountinfo");

		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "surname";
		$obj_page_field->container_class = "register_surname";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_surname");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "surname") . "_tip");   
		$obj_page_field->required = true;
		$oRecord->addContent($obj_page_field, "accountinfo");
		
		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "email";
		$obj_page_field->container_class = "register_email";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "email") . "_tip");   
		$obj_page_field->required = true;
		$oRecord->addContent($obj_page_field, "accountinfo");

		if(!$disable_account_registration) {
			$obj_page_field = ffField::factory($cm->oPage);
			$obj_page_field->id = "confirmemail";
			$obj_page_field->container_class = "register_confirm_email";
			$obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_email");
			$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmemail") . "_tip");   
			$obj_page_field->compare = "email";
			$obj_page_field->required = true;
			$oRecord->addContent($obj_page_field, "accountinfo"); 
		}

		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "tel";
		$obj_page_field->container_class = "register_tel";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_tel");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "tel") . "_tip");   
		$oRecord->addContent($obj_page_field, "accountinfo");
	} elseif(!$disable_account_registration) {
		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "email";
		$obj_page_field->container_class = "register_email";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "email") . "_tip");   
		$obj_page_field->required = true;
		$oRecord->addContent($obj_page_field, "account");

		$obj_page_field = ffField::factory($cm->oPage);
		$obj_page_field->id = "confirmemail";
		$obj_page_field->container_class = "register_confirm_email";
		$obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_email");
		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmemail") . "_tip");   
		$obj_page_field->compare = "email";
		$obj_page_field->required = true;
		$oRecord->addContent($obj_page_field, "account"); 
	}	    

	if($enable_bill_data) {
    	$oRecord->addContent(null, true, "bill");
        $oRecord->groups["bill"] = array(
                                                 "title" => ffTemplate::_get_word_by_code("register_bill")
                                                 , "cols" => 1
                                              );
	}
	
    $db_gallery->query("SELECT module_register_fields.*
                            , extended_type.name AS extended_type
                            , check_control.ff_name AS check_control
                            , module_form_fields_group.name AS `group_field`
                            , anagraph_fields_selection.ID_vgallery_fields AS ID_field
                        FROM 
                            module_register_fields
                            LEFT JOIN extended_type ON extended_type.ID = module_register_fields.ID_extended_type
                            LEFT JOIN check_control ON check_control.ID = module_register_fields.ID_check_control
                            LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_register_fields.ID_form_fields_group
                            LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = module_register_fields.ID_selection
                        WHERE 
                            module_register_fields.ID_module = " . $db_gallery->toSql($ID_register, "Number") . "
                            AND NOT(module_register_fields.hide > 0)
                        ORDER BY module_register_fields.`order`, module_register_fields.name
                        ");
    if($db_gallery->nextRecord()) {
    	$db_selection = ffDB_Sql::factory();
         do {
            $field_name = $db_gallery->getField("name")->getValue();
            $field_id = $db_gallery->getField("ID")->getValue();
            $group_field = $db_gallery->getField("group_field")->getValue() 
                                ? preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("group_field")->getValue()) 
                                : null;
            
            if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
                $oRecord->addContent(null, true, $group_field); 
                $oRecord->groups[$group_field] = array(
                                                         "title" => ffTemplate::_get_word_by_code("register_" . $group_field)
                                                         , "cols" => 1
                                                      );
            }
			if($db_gallery->getField("hide")->getValue()) {
				$hide_class = " hide";
			} else {
				$hide_class = "";
			}
            
            $obj_page_field = ffField::factory($cm->oPage);
            $obj_page_field->id = $field_id;
            $obj_page_field->container_class = "register_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name) . $hide_class;
            $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
            $obj_page_field->user_vars["group_field"] = $group_field;
            $obj_page_field->user_vars["name"] = $field_name;
            $obj_page_field->user_vars["enable_in_mail"] = $db_gallery->getField("enable_in_mail", "Number", true);
            $obj_page_field->user_vars["unic_value"] = $db_gallery->getField("unic_value", "Number", true);
            
            $obj_page_field->data_type = "";
            $obj_page_field->store_in_db = false;
            
            $writable = $db_gallery->getField("writable", "Number", true);

	        if($db_gallery->getField("enable_tip", "Number", true))
        		$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . $field_name) . "_tip");
	        else
				unset($obj_page_field->properties["title"]);

            $selection_value = array();        
            
            switch($db_gallery->getField("extended_type")->getValue())
            {
                case "Selection":
                case "Option":
                    $obj_page_field->base_type = "Text";

                    if($writable) {
                        if($db_gallery->getField("extended_type")->getValue() == "Option") {
                            $obj_page_field->control_type = "radio";
                            $obj_page_field->extended_type = "Selection";
                            $obj_page_field->widget = "";
                        } else {
                            $obj_page_field->control_type = "combo";
                            $obj_page_field->extended_type = "Selection";
                            //$obj_page_field->widget = "activecomboex";
                            //$obj_page_field->actex_update_from_db = true;
                        }
                    } else {
                        $obj_page_field->extended_type = "String";
                        $obj_page_field->control_type = "label";
                    }
                    
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $db_selection->query("
                                        SELECT DISTINCT nameID, name
                                        FROM 
                                        (
                                            (
                                                SELECT 
                                                    vgallery_rel_nodes_fields.description AS nameID
                                                    , vgallery_rel_nodes_fields.description  AS name
                                                    , vgallery_fields.`order_backoffice` AS `order`
                                               FROM vgallery_rel_nodes_fields
                                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                    INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                               WHERE 
                                                    vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                    AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                           ) UNION (
                                               SELECT
                                                    anagraph_fields_selection_value.name AS nameID
                                                    , anagraph_fields_selection_value.name AS name
                                                    , anagraph_fields_selection_value.`order` AS `order`
                                                   FROM anagraph_fields_selection_value 
                                                        INNER JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields_selection_value.ID_selection
                                                   WHERE anagraph_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
                                           )
                                        ) AS tbl_src
                                        ORDER BY tbl_src.`order`, tbl_src.name");
					if($db_selection->nextRecord()) {
						do {
							$selection_value[] = array(new ffData($db_selection->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
						} while($db_selection->nextRecord());
					}

	                $obj_page_field->multi_pairs = $selection_value;
	                $obj_page_field->encode_entities = false;
	                $obj_page_field->multi_select_one = !$db_gallery->getField("disable_select_one", "Number", true);
					
                    $type_value = "Text";
                    break;
                case "Group":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->control_type = "input";

                    if(!$writable)
                        $obj_page_field->properties["disabled"] = "disabled";
                        
                    $obj_page_field->widget = "checkgroup";
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = ";";

                    $db_selection->query("
                                        SELECT DISTINCT nameID, name
                                        FROM 
                                        (
                                            (
                                                SELECT 
                                                    vgallery_rel_nodes_fields.description AS nameID
                                                    , vgallery_rel_nodes_fields.description  AS name
                                                    , vgallery_fields.`order_backoffice` AS `order`
                                               FROM vgallery_rel_nodes_fields
                                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                    INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                               WHERE 
                                                    vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                    AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                           ) UNION (
                                               SELECT
                                                    anagraph_fields_selection_value.name AS nameID
                                                    , anagraph_fields_selection_value.name AS name
                                                    , anagraph_fields_selection_value.`order` AS `order`
                                                   FROM anagraph_fields_selection_value 
                                                        INNER JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields_selection_value.ID_selection
                                                   WHERE anagraph_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
                                           )
                                        ) AS tbl_src
                                        ORDER BY tbl_src.`order`, tbl_src.name");
                    if($db_selection->nextRecord()) {
                        do {
                            $selection_value[] = array(new ffData($db_selection->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
                        } while($db_selection->nextRecord());
                    }

                    $obj_page_field->multi_pairs = $selection_value;
                    $obj_page_field->encode_entities = false;

                    $type_value = "Text";
                    break;
                case "Text":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";
                    
                    if(!$writable) {
                      $obj_page_field->default_value = new ffData(get_word_by_code("form_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name) . "_text_" . $oRecord->user_vars["MD_chk"]["params"][0]), "Text");
                      $obj_page_field->properties["readonly"] = "readonly";
                    }
                        
                    $obj_page_field->widget = "";
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;

                case "TextBB":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";

                    if($writable) {
                        $obj_page_field->control_type = "textarea";
                        if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/tiny_mce/tiny_mce.js")) {
                            $obj_page_field->widget = "tiny_mce";
                        } else {
                            $obj_page_field->widget = "";
                        }
                    } else {
                        $obj_page_field->control_type = "label";
                        $obj_page_field->widget = "";
                    }

                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;

                case "TextCK":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";

                    if($writable) {
                        $obj_page_field->control_type = "textarea";
                        if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                            $obj_page_field->widget = "ckeditor";
                        } else {
                            $obj_page_field->widget = "";
                        }
                        $obj_page_field->ckeditor_group_by_auth = true;
                    } else {
                        $obj_page_field->control_type = "label";
                        $obj_page_field->widget = "";
                    }

                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;
                    
                case "Boolean":
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->extended_type = "Boolean";
                    $obj_page_field->control_type = "checkbox";

                    if(!$writable)
                        $obj_page_field->properties["disabled"] = "disabled";

                    $obj_page_field->widget = "";
                    $obj_page_field->unchecked_value = new ffData("0", "Number");
                    $obj_page_field->checked_value = new ffData("1", "Number");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Number";
                    break;

                case "Date":
                    $obj_page_field->base_type = "Date";
                    $obj_page_field->extended_type = "Date";
                    
                    if($writable) {
                        $obj_page_field->control_type = "input";
                        $obj_page_field->widget = "datepicker";
                    } else {
                        $obj_page_field->control_type = "label";
                        $obj_page_field->widget = "";
                    }
                    
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Date";
                    break;

                case "DateCombo":
                    $obj_page_field->base_type = "Date";
                    $obj_page_field->extended_type = "Date";
                    
                    if($writable) {
                        $obj_page_field->control_type = "input";
                        $obj_page_field->widget = "datechooser";
                    } else {
                        $obj_page_field->control_type = "label";
                        $obj_page_field->widget = "";
                    }
                    
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Date";
                    break;

                case "Image":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "File";

                    $obj_page_field->file_storing_path = FF_DISK_UPDIR . "/users/[register-ID_VALUE]";
                    $obj_page_field->file_temp_path = FF_DISK_UPDIR . "/users";
                    $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

                    $obj_page_field->file_show_filename = true; 
                    $obj_page_field->file_full_path = false;
                    $obj_page_field->file_check_exist = false;
                    $obj_page_field->file_normalize = true;
                     
                    $obj_page_field->file_show_preview = true;
                    $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/users/[_FILENAME_]";
                    $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/users/[_FILENAME_]";

                    if($writable) {
                        $obj_page_field->control_type = "file";

                        $obj_page_field->file_show_delete = true;
                        $obj_page_field->file_writable = false;
                        
                        $obj_page_field->widget = "kcfinder"; 
						if(check_function("set_field_uploader")) { 
							$obj_page_field = set_field_uploader($obj_page_field);
						}
                    } else {
                        $obj_page_field->control_type = "picture_no_link";

                        $obj_page_field->file_show_delete = false;
                        $obj_page_field->file_writable = false;

                        $obj_page_field->widget = "";
                    }

                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;

                case "Upload":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "File";

                    $obj_page_field->file_storing_path = FF_DISK_UPDIR . "/users/[register-ID_VALUE]";
                    $obj_page_field->file_temp_path = FF_DISK_UPDIR . "/users";
                    $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

                    $obj_page_field->file_show_filename = true; 
                    $obj_page_field->file_full_path = false;
                    $obj_page_field->file_check_exist = false;
                    $obj_page_field->file_normalize = true;
                     
                    $obj_page_field->file_show_preview = true;
                    $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/users/[_FILENAME_]";
                    $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/users/[_FILENAME_]";

                    if($writable) {
                        $obj_page_field->control_type = "file";

                        $obj_page_field->file_show_delete = true;
                        $obj_page_field->file_writable = false;
                        
                        $obj_page_field->widget = "uploadify";
						if(check_function("set_field_uploader")) { 
							$obj_page_field = set_field_uploader($obj_page_field);
						}
                    } else {
                        $obj_page_field->control_type = "picture_no_link";

                        $obj_page_field->file_show_delete = false;
                        $obj_page_field->file_writable = false;

                        $obj_page_field->widget = "";
                    }

                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;

                case "UploadImage":
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "File";

                    $obj_page_field->file_storing_path = FF_DISK_UPDIR . "/users/[register-ID_VALUE]";
                    $obj_page_field->file_temp_path = FF_DISK_UPDIR . "/users";
                    $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

                    $obj_page_field->file_show_filename = true; 
                    $obj_page_field->file_full_path = false;
                    $obj_page_field->file_check_exist = false;
                    $obj_page_field->file_normalize = true;
                     
                    $obj_page_field->file_show_preview = true;
                    $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/users/[register-ID_VALUE]/[_FILENAME_]";
                    $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/users/[_FILENAME_]";
                    $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/users/[_FILENAME_]";

                    if($writable) {
                        $obj_page_field->control_type = "file";

                        $obj_page_field->file_show_delete = true;
                        $obj_page_field->file_writable = false;
                        
                        $obj_page_field->widget = "kcuploadify"; 
						if(check_function("set_field_uploader")) { 
							$obj_page_field = set_field_uploader($obj_page_field);
						}
                    } else {
                        $obj_page_field->control_type = "picture_no_link";

                        $obj_page_field->file_show_delete = false;
                        $obj_page_field->file_writable = false;

                        $obj_page_field->widget = "";
                    }

                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
                    break;

                case "Number":
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->extended_type = "";

                    if($writable) 
                        $obj_page_field->control_type = "input";
                    else
                        $obj_page_field->control_type = "label";

                    $obj_page_field->widget = "";
                    $obj_page_field->unchecked_value = new ffData(""); 
                    $obj_page_field->checked_value = new ffData(""); 
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Number";
                    break;

                    
                default: // String
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";

                    if($writable) 
                        $obj_page_field->control_type = "input";
                    else
                        $obj_page_field->control_type = "label";

                    $obj_page_field->widget = "";
                    $obj_page_field->unchecked_value = new ffData("");
                    $obj_page_field->checked_value = new ffData("");
                    $obj_page_field->grouping_separator = "";
                    $type_value = "Text";
            }

            if(isset($_GET[$field_name]) && strlen($_GET[$field_name])) {
                $obj_page_field->default_value = new ffData($_REQUEST[$field_name], $type_value);
                
            }
            if($db_gallery->getField("require", "Number", true)) {
                $obj_page_field->required = true;
            }

            if(strlen($db_gallery->getField("check_control")->getValue()))
                $obj_page_field->addValidator($db_gallery->getField("check_control")->getValue());
            
            $oRecord->addContent($obj_page_field, $group_field);
        } while($db_gallery->nextRecord());
    }

	if($enable_bill_data) {

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "billreference";
        $obj_page_field->container_class = "register_bill_reference";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_reference");
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billreference") . "_tip");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "bill");

	    $obj_page_field = ffField::factory($cm->oPage);
	    $obj_page_field->id = "billcf";
	    $obj_page_field->container_class = "register_bill_cf";
	    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_cf");
	    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcf") . "_tip");   
	    $obj_page_field->addValidator("cf");
	    $oRecord->addContent($obj_page_field, "bill");

	    $obj_page_field = ffField::factory($cm->oPage);
	    $obj_page_field->id = "billpiva";
	    $obj_page_field->container_class = "register_bill_piva";
	    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_piva");
	    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billpiva") . "_tip");   
	    //$oField->required = true;
	    $obj_page_field->addValidator("piva");
	    $oRecord->addContent($obj_page_field, "bill");  

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "billaddress";
        $obj_page_field->container_class = "register_bill_address";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_address");
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billaddress") . "_tip");   
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "bill");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "billcap";
        $obj_page_field->container_class = "register_bill_cap";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_cap");
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcap") . "_tip");   
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "bill");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "billtown";
        $obj_page_field->container_class = "register_bill_town";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_town");
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billtown") . "_tip");   
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "bill");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "billprovince";
        $obj_page_field->container_class = "register_bill_province";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_province");
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billprovince") . "_tip");   
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "bill");

        if(Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE") > 0) {
        	$oRecord->additional_fields["shippingstate"] = new ffData(Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE"), "Number");
		} else {
	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "billstate";
	        $obj_page_field->container_class = "register_bill_state";
	        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_state");
	        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billstate") . "_tip");   
	        $obj_page_field->base_type = "Number";
	        $obj_page_field->widget = "activecomboex";
	        $obj_page_field->actex_update_from_db = true;
            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
	        $obj_page_field->source_SQL = "SELECT
	                                " . FF_SUPPORT_PREFIX . "state.ID
									, IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
	                            FROM
	                                " . FF_SUPPORT_PREFIX . "state
	                            ORDER BY description";
	        $obj_page_field->required = true;
	        $oRecord->addContent($obj_page_field, "bill");
		}
	}

    if($enable_ecommerce_data) {
    	$oRecord->addContent(null, true, "ecommerce");
        $oRecord->groups["ecommerce"] = array(
                                                 "title" => ffTemplate::_get_word_by_code("register_ecommerce")
                                                 , "cols" => 1
                                              );

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "shippingreference";
        $obj_page_field->container_class = "register_shipping_reference";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_reference");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "ecommerce");
                                                      
        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "shippingaddress";
        $obj_page_field->container_class = "register_shipping_address";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_address");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "ecommerce");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "shippingcap";
        $obj_page_field->container_class = "register_shipping_cap";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_cap");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "ecommerce");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "shippingtown";
        $obj_page_field->container_class = "register_shipping_town";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_town");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "ecommerce");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "shippingprovince";
        $obj_page_field->container_class = "register_shipping_province";
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_province");
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "ecommerce");

        if(Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE") > 0) {
        	$oRecord->additional_fields["shippingstate"] = new ffData(Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE"), "Number");
		} else {
	        $obj_page_field = ffField::factory($cm->oPage);
	        $obj_page_field->id = "shippingstate";
	        $obj_page_field->container_class = "register_shipping_state";
	        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_state");
	        $obj_page_field->base_type = "Number";
	        $obj_page_field->widget = "activecomboex";
	        $obj_page_field->actex_update_from_db = true;
            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
	        $obj_page_field->source_SQL = "SELECT
	                                " . FF_SUPPORT_PREFIX . "state.ID
									, IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
	                            FROM
	                                " . FF_SUPPORT_PREFIX . "state
	                            ORDER BY description";
	        $obj_page_field->required = true;
	        $oRecord->addContent($obj_page_field, "ecommerce");
		}
    }

    if($enable_privacy) {
    	$oRecord->addContent(null, true, "privacy");
        $oRecord->groups["privacy"] = array(
                                                 "title" => ffTemplate::_get_word_by_code("register_privacy")
                                                 , "cols" => 1
                                              );

        
        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "privacy_text";
        $obj_page_field->container_class = "register_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_text");
        $obj_page_field->label = "";
        //$obj_page_field->display_label = false;
        $obj_page_field->base_type = "Text";
        $obj_page_field->extended_type = "Text";
        $obj_page_field->control_type = "textarea";
        $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("register_privacy_text_" . basename($cm->real_path_info)), "Text");
        $obj_page_field->properties["readonly"] = "readonly";
        $obj_page_field->data_type = "";
        $obj_page_field->store_in_db = false;
        $oRecord->addContent($obj_page_field, "privacy");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "privacy_check";
        $obj_page_field->container_class = "register_" . preg_replace('/[^a-zA-Z0-9]/', '', "register_check");
        $obj_page_field->label = ffTemplate::_get_word_by_code("register_privacy_check");
        $obj_page_field->base_type = "Number";
        $obj_page_field->control_type = "checkbox";
        $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $obj_page_field->required = true;
        $obj_page_field->data_type = "";
        $obj_page_field->store_in_db = false;
        $oRecord->addContent($obj_page_field, "privacy");

        if($enable_newsletter) {
            $obj_page_field = ffField::factory($cm->oPage);
            $obj_page_field->id = "newsletter";
            $obj_page_field->container_class = "register_" . preg_replace('/[^a-zA-Z0-9]/', '', "newsletter");
            $obj_page_field->label = ffTemplate::_get_word_by_code("register_newsletter");
            $obj_page_field->base_type = "Number";
            $obj_page_field->control_type = "checkbox";
            $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
            $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
            $obj_page_field->required = true;
            $obj_page_field->data_type = "";
            $oRecord->addContent($obj_page_field, "privacy");
        }
    }

    $cm->oPage->addContent($oRecord);  
}
?>
