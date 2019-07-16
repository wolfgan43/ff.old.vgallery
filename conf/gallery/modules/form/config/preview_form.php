<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

$db_selection = ffDB_Sql::factory();

$db_gallery->query("SELECT module_form.*
                        FROM 
                            module_form
                        WHERE 
                            module_form.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                    );
if($db_gallery->nextRecord()) {
    $ID_form = $db_gallery->getField("ID")->getValue();
    $form_name = $db_gallery->getField("name")->getValue();
    $force_redirect = $db_gallery->getField("force_redirect")->getValue();
    $fixed_pre_content = $db_gallery->getField("fixed_pre_content")->getValue();  
    $fixed_post_content = $db_gallery->getField("fixed_post_content")->getValue();  
    $privacy = $db_gallery->getField("privacy")->getValue();
    $require_note = $db_gallery->getField("require_note")->getValue();
    $tpl_form_path = $db_gallery->getField("tpl_form_path")->getValue();
    $send_mail = $db_gallery->getField("send_mail")->getValue();

    //$display_view_mode = $db_gallery->getField("display_view_mode")->getValue();
    
	$limit_by_groups = $db_gallery->getField("limit_by_groups")->getValue();
	if(strlen($limit_by_groups)) {
		$limit_by_groups = explode(",", $limit_by_groups);
		$user = Auth::get("user");

		if(array_search($user->acl, $limit_by_groups) !== false) {
			$allow_form = true;
		} else {
			$allow_form = false;
			$strErrorForm = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_unable_to_write");
		}
	} else {
		$allow_form = true;
	}
	
    if($send_mail) {
        $ID_email = $db_gallery->getField("ID_email", "Number")->getValue();
        $force_to_with_user = $db_gallery->getField("force_to_with_user")->getValue();
        $send_copy_to_guest = $db_gallery->getField("send_copy_to_guest")->getValue();
        $force_from_with_domclass = $db_gallery->getField("force_from_with_domclass")->getValue();
	}
}

$db_gallery->query("SELECT module_form_fields.*
                            , extended_type.name AS extended_type
                            , check_control.ff_name AS check_control
                            , module_form_fields_group.name AS `group_field`
                            , module_form_fields_selection.ID_vgallery_fields AS ID_field
                        FROM 
                            module_form_fields
                            LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                            LEFT JOIN check_control ON check_control.ID = module_form_fields.ID_check_control
                            LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                            LEFT JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields.ID_selection
                        WHERE 
                            module_form_fields.ID_module = " . $db_gallery->toSql(new ffData($ID_form, "Number")) . "
                        ORDER BY module_form_fields.`order`, module_form_fields.name
                        ");
if($db_gallery->nextRecord()) {
    /*if($tpl_form_path && file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_form_path)) {
        $oRecord->template_dir = ffCommon_dirname(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_form_path);
        $oRecord->template_file = basename($tpl_form_path);
    }*/

    $oRecord->id = "FormConfigPreview";
    $oRecord->class = "FormConfigPreview";
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
    $oRecord->src_table = ""; 
    
	if(check_function("MD_form_on_done_action")) { //if(check_function("MD_form_on_check_after")) if(check_function("MD_form_on_do_action"))
		$oRecord->addEvent("on_check_after", "MD_form_on_check_after");
		$oRecord->addEvent("on_do_action", "MD_form_on_do_action");
		$oRecord->addEvent("on_done_action", "MD_form_on_done_action");
	}

       /*
    if($force_redirect)
        $oRecord->ret_url = $force_redirect;
    else
        $oRecord->ret_url = urldecode($_REQUEST["ret_url"]);
     */
    if($require_note)
        $oRecord->display_required_note = true;
    else 
        $oRecord->display_required_note = false;
        
    if($fixed_pre_content)
        $oRecord->fixed_pre_content = ffTemplate::_get_word_by_code("form_config_fixed_pre_content_" . basename($cm->real_path_info));
    
    if($fixed_post_content)
        $oRecord->fixed_post_content = ffTemplate::_get_word_by_code("form_config_fixed_post_content_" . basename($cm->real_path_info));
    
    $oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = true;
    $oRecord->buttons_options["insert"]["display"] = false;
    $oRecord->buttons_options["print"]["display"] = false;
    
    // nuove variabili
    $oRecord->user_vars["send_mail"] = $send_mail;
    $oRecord->user_vars["ID_email"] = $ID_email;
    $oRecord->user_vars["force_to_with_user"] = $force_to_with_user;
    $oRecord->user_vars["send_copy_to_guest"] = $send_copy_to_guest;
    $oRecord->user_vars["force_from_with_domclass"] = $force_from_with_domclass;
    $oRecord->user_vars["form_name"] = $form_name;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "form-ID";
    $oField->base_type = "Number";
    $oField->auto_key = false;
    $oField->default_value = new ffData($ID_form, "Number");
    $oRecord->addKeyField($oField);

    if(strlen($force_from_with_domclass)) {
        $oRecord->addHiddenField("ffm", new ffData(""));
        ///DA COMPLETARE E PROPAGARE PER OGNI FILE CHE USA QUESTO
    }

    do {
        $field_name = $db_gallery->getField("name")->getValue();
        $field_id = $db_gallery->getField("ID")->getValue();
        $group_field = $db_gallery->getField("group_field")->getValue() 
                            ? preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("group_field")->getValue()) 
                            : null;

        if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
            $oRecord->addContent(null, true, $group_field); 
            $oRecord->groups[$group_field] = array(
                                                     "title" => ffTemplate::_get_word_by_code("form_" . $group_field)
                                                     , "cols" => 1
                                                  );
        }
	    $custom_data = $db_gallery->getField("custom")->getValue();
	    if(strlen($custom_data)) {
	        $extended_type = "Text";
		} else {
	        $extended_type = $db_gallery->getField("extended_type")->getValue();
		}
		$custom_class = $db_gallery->getField("domclass")->getValue();

		if($db_gallery->getField("hide")->getValue()) {
			$hide_class = " hide";
		} else {
			$hide_class = "";
		}        

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = $field_id;
        $obj_page_field->container_class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name) . $hide_class . (strlen($custom_class) ? " " : "") . $custom_class;
        $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
        $obj_page_field->user_vars["group_field"] = $group_field;
        $obj_page_field->user_vars["send_mail"] = $db_gallery->getField("send_mail")->getValue(); 
        $obj_page_field->user_vars["unic_value"] = $db_gallery->getField("unic_value")->getValue(); 
        $writable = $db_gallery->getField("writable")->getValue();

        if($db_gallery->getField("enable_tip", "Number", true))
        	$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name . $field_name) . "_tip");
        else
			unset($obj_page_field->properties["title"]);
		
        $selection_value = array();        
        
        switch($extended_type)
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
                                           FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                           WHERE 
                                                vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                       ) UNION (
                                           SELECT
                                                module_form_fields_selection_value.name AS nameID
                                                , module_form_fields_selection_value.name AS name
                                               FROM module_form_fields_selection_value 
                                                    INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                               WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
                                       )
                                    ) AS tbl_src
                                    ORDER BY tbl_src.name");
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
                                                NULL
                                                , vgallery_rel_nodes_fields.description AS nameID
                                                , vgallery_rel_nodes_fields.description  AS name
                                           FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                           WHERE 
                                                vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                       ) UNION (
                                           SELECT
                                                NULL 
                                                , module_form_fields_selection_value.name AS nameID
                                                , module_form_fields_selection_value.name AS name
                                               FROM module_form_fields_selection_value 
                                                    INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                               WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
                                       )
                                    ) AS tbl_src
                                    ORDER BY tbl_src.name");
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
                
                if($writable)
                    $obj_page_field->control_type = "textarea";
                else
                    $obj_page_field->control_type = "label";
                    
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

	            $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/[form-ID_VALUE]";
	            $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name;
	            $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

				$obj_page_field->file_show_filename = true; 
			    $obj_page_field->file_full_path = true;
                $obj_page_field->file_check_exist = true;
			    $obj_page_field->file_normalize = true;
			     
			    $obj_page_field->file_show_preview = true;
	            $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
	            $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";
//	            $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
//	            $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";

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

	            $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/[form-ID_VALUE]";
	            $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name;
	            $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

				$obj_page_field->file_show_filename = true; 
			    $obj_page_field->file_full_path = true;
                $obj_page_field->file_check_exist = true;
			    $obj_page_field->file_normalize = true;
			     
			    $obj_page_field->file_show_preview = true;
	            $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
	            $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";
//	            $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
//	            $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";

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

	            $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/[form-ID_VALUE]";
	            $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name;
	            $obj_page_field->file_max_size = Auth::env("MAX_UPLOAD");

				$obj_page_field->file_show_filename = true; 
			    $obj_page_field->file_full_path = true;
                $obj_page_field->file_check_exist = true;
			    $obj_page_field->file_normalize = true;
			     
			    $obj_page_field->file_show_preview = true;
	            $obj_page_field->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
	            $obj_page_field->file_saved_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";
//	            $obj_page_field->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
//	            $obj_page_field->file_temp_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";

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

    if($privacy) {
    	$oRecord->addContent(null, true, "privacy"); 
        $oRecord->groups["privacy"] = array(
                                                         "title" => ffTemplate::_get_word_by_code("form_privacy")
                                                         , "cols" => 1
                                                      );

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "privacy_text";
        $obj_page_field->class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_text");
        $obj_page_field->label = "";
        //$obj_page_field->display_label = false;
        $obj_page_field->base_type = "Text";
        $obj_page_field->extended_type = "Text";
        $obj_page_field->control_type = "textarea";
        $obj_page_field->widget = "";
        $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("form_config_privacy_text_" . basename($cm->real_path_info)), "Text");
        $obj_page_field->properties["readonly"] = "readonly";
        $oRecord->addContent($obj_page_field, "privacy");

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "privacy_check";
        $obj_page_field->class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_check");
        $obj_page_field->label = ffTemplate::_get_word_by_code("form_config_privacy_check");
        $obj_page_field->base_type = "Number";
        $obj_page_field->control_type = "checkbox";
        $obj_page_field->widget = "";
        $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $obj_page_field->required = true;
        $oRecord->addContent($obj_page_field, "privacy");
    }

    $cm->oPage->addContent($oRecord);
}
?>
