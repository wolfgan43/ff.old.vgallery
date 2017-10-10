<?php
function process_addon_comment($ID_vgallery_node, $form, $uid = null, $user_path, $ret_url, $tbl_src, $disable_control = true, $layout) {
	$cm = cm::getInstance();

	$db = ffDB_Sql::factory();	
	$db_selection = ffDB_Sql::factory();

    $location = $layout["location"];
    if(strpos($layout["prefix"], "MD-") === 0)
    	$unic_id = $layout["prefix"];
    else
    	$unic_id = $layout["prefix"] . $layout["ID"];
    	
    $layout_settings = $layout["settings"];

	$UserNID = get_session("UserNID");
	$UserID = get_session("UserID");	
	
	$buffer = "";

	if(is_numeric($form) && $form > 0)
		$sSQL_cond = "module_form.ID = " . $db->toSql($form, "Number");
	else
		$sSQL_cond = "module_form.name = " . $db->toSql($form);

	$db->query("SELECT module_form.*
		                    FROM 
		                        module_form
		                    WHERE 
		                       $sSQL_cond
				");
	if($db->nextRecord()) {
		$ID_form = $db->getField("ID", "Number")->getValue();
		$form_name = $db->getField("name", "Text", true);
		$privacy = $db->getField("privacy")->getValue();
		$require_note = $db->getField("require_note")->getValue();
		$tpl_form_path = $db->getField("tpl_form_path")->getValue();
		$send_mail = $db->getField("send_mail")->getValue();
		$report = $db->getField("report")->getValue();
		
		$display_view_mode = $db->getField("display_view_mode")->getValue();

		if($uid === null) {		
			$limit_by_groups = $db->getField("limit_by_groups")->getValue();
			if(strlen($limit_by_groups)) {
				$limit_by_groups = explode(",", $limit_by_groups);
				$user_permission = get_session("user_permission"); 			

				if(count(array_intersect($user_permission["groups"], $limit_by_groups))) {
					$allow_form = true;
					$strErrorForm = "";
				} else {
					$allow_form = false;
					$strErrorForm = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_unable_to_write");
				}
			} else {
				$allow_form = true;
				$strErrorForm = "";
			}
		} else {
			$allow_form = false;
			$strErrorForm = "";
		}

		if($send_mail) {
		    $ID_email = $db->getField("ID_email", "Number")->getValue();
		    $force_to_with_user = $db->getField("force_to_with_user")->getValue();
            $send_copy_to_guest = $db->getField("send_copy_to_guest")->getValue();
            $force_from_with_domclass = $db->getField("force_from_with_domclass")->getValue();
		}
	}

	$db->query("SELECT module_form_fields.*
			                    , extended_type.name AS extended_type
			                    , check_control.ff_name AS check_control
			                    , module_form_fields_group.name AS `group_field`
			                    , module_form_fields_selection.ID_vgallery_fields AS ID_field
			                    , module_form_rel_nodes_fields.value AS default_value
			                    , module_form_rel_nodes_fields.ID AS ID_node_field
			                FROM 
			                    module_form_fields
			                    LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
			                    LEFT JOIN check_control ON check_control.ID = module_form_fields.ID_check_control
			                    LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
			                    LEFT JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields.ID_selection
			                    LEFT JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_form_fields = module_form_fields.ID
			                        AND module_form_rel_nodes_fields.ID_form_nodes =  " . $db->toSql(new ffData($ID_form_node, "Number")) . "
			                WHERE 
			                    module_form_fields.ID_module = " . $db->toSql(new ffData($ID_form, "Number")) . "
			                ORDER BY module_form_fields.`order`, module_form_fields.name
			                ");
	if($db->nextRecord()) {
		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = $unic_id;
		$oRecord->src_table = "";
		if($layout_settings["AREA_COMMENT_SHOW_TITLE"])
            $oRecord->title =  ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', strtolower($form_name)) . "_title");
            
        $oRecord->class = $oRecord->class . " " . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($form_name)) . "_comment";
        if($display_view_mode) {
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
		}

		$oRecord->use_own_location = true;
        if(check_function("process_addon_comment_view")) {
            if(!$layout_settings["AREA_COMMENT_SHOW_FORM_TOP"]) {
                $oRecord->fixed_pre_content = process_addon_comment_view($user_path, $ret_url, $tbl_src, $ID_vgallery_node, $ID_form, $uid, $disable_control, $layout) . $strErrorForm;
            } else {
                $oRecord->fixed_post_content = $strErrorForm . process_addon_comment_view($user_path, $ret_url, $tbl_src, $ID_vgallery_node, $ID_form, $uid, $disable_control, $layout);
            }
        }		
		if(check_function("MD_form_on_done_action")) { //if(check_function("MD_form_on_check_after")) if(check_function("MD_form_on_do_action"))
			$oRecord->addEvent("on_check_after", "MD_form_on_check_after");
			$oRecord->addEvent("on_do_action", "MD_form_on_do_action");
			$oRecord->addEvent("on_done_action", "MD_form_on_done_action");
		}
		$oRecord->ret_url = "";//se stesso

		$oRecord->user_vars["node"] = array(
											"tbl_src" =>  $tbl_src
											, "ID" =>  $ID_vgallery_node
											, "path" => $user_path
										);
        $oRecord->user_vars["hide_on_insert"] = $layout_settings["AREA_COMMENT_HIDE_ON_INSERT"];

		if($require_note)
			$oRecord->display_required_note = true;
		else 
			$oRecord->display_required_note = false;
		
		$oRecord->skip_action = true;
		$oRecord->buttons_options["cancel"]["display"] = false;
		$oRecord->buttons_options["update"]["display"] = false;

		// nuove variabili
		$oRecord->user_vars["send_mail"] = $send_mail;
		$oRecord->user_vars["ID_email"] = $ID_email;
		$oRecord->user_vars["force_to_with_user"] = $force_to_with_user;
        $oRecord->user_vars["send_copy_to_guest"] = $send_copy_to_guest;
        $oRecord->user_vars["force_from_with_domclass"] = $force_from_with_domclass;

		$oRecord->user_vars["form_name"] = $form_name;
		$oRecord->user_vars["report"] = false;
	    $oRecord->user_vars["enable_cart"] = false;
	    $oRecord->user_vars["skip_form_cart"] = false;
	    $oRecord->user_vars["skip_shipping_calc"] = false;
	    $oRecord->user_vars["discount_perc"] = 0;
	    $oRecord->user_vars["discount_val"] = 0;
	    $oRecord->user_vars["enable_sum_quantity"] = false;
	    $oRecord->user_vars["reset_cart"] = false;

		$oField = ffField::factory($cm->oPage);
		$oField->id = "form-ID";
		$oField->base_type = "Number";
		$oField->auto_key = false;
		$oField->default_value = new ffData($ID_form, "Number");
		$oRecord->addKeyField($oField);

		if(!$allow_form) {
			$oRecord->buttons_options["insert"]["display"] = false;
		} else {
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" || $layout["ajax"]) {
 				$oButton = ffButton::factory($cm->oPage);
				$oButton->id = "insert";
				$oButton->action_type = "submit";
				$oButton->frmAction = "insert";
				//$oButton->url = $_REQUEST["ret_url"];
				$oButton->aspect = "button";
				//$oButton->image = "preview.png";
				$oButton->jsaction =  " ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function() { ff.ajax.doRequest({'component' : '" . $unic_id . "'}); });";
				$oButton->label = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_insert");
				$oRecord->buttons_options["insert"]["obj"] = $oButton;
			} else {
                $oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_insert");
            }

			if($UserID == MOD_SEC_GUEST_USER_NAME && $layout_settings["AREA_COMMENT_SHOW_FORM_ANONYMOUS"]) {
        		$oRecord->addContent(null, true, "anonymous");
				$oRecord->groups["anonymous"] = array(
				                                         "title" => ffTemplate::_get_word_by_code("form_anonymous")
				                                         , "cols" => 1
				                                      );

				$oField = ffField::factory($cm->oPage);
				$oField->id = "anonymous_nick";
				$oField->label = ffTemplate::_get_word_by_code("form_anonymous_nick");
				$oField->required = true;
				$oRecord->addContent($oField, "anonymous");

				$oField = ffField::factory($cm->oPage);
				$oField->id = "anonymous_email";
				$oField->label = ffTemplate::_get_word_by_code("form_anonymous_email");
				$oField->addValidator("email");
				$oRecord->addContent($oField, "anonymous");
				
				if($layout_settings["AREA_COMMENT_FORM_ANONYMOUS_SHOW_WEBSITE"]) {
					$oField = ffField::factory($cm->oPage);
					$oField->id = "anonymous_website";
					$oField->label = ffTemplate::_get_word_by_code("form_anonymous_website");
					$oField->addValidator("url");
					$oRecord->addContent($oField, "anonymous");
					
					$oRecord->user_vars["anonymous_show_website"] = true;
				}

				$oRecord->user_vars["anonymous"] = true;
				$default_group = "anonymous";
			} else {
				$default_group = null;
			}
			
			do {
				$field_name = $db->getField("name")->getValue();
				$field_id = $db->getField("ID")->getValue();
				$group_field = $db->getField("group_field")->getValue() 
				                    ? preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("group_field")->getValue()) 
				                    : $default_group;
				
				if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
        			$oRecord->addContent(null, true, $group_field);
				    $oRecord->groups[$group_field] = array(
				                                             "title" => ffTemplate::_get_word_by_code("form_" . $group_field)
				                                             , "cols" => 1
				                                          );
				}
				                    
				$obj_page_field = ffField::factory($cm->oPage);
				$obj_page_field->id = $field_id;

				$obj_page_field->container_class = "comment_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name);
				$obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
				$obj_page_field->user_vars["group_field"] = $group_field;
				$obj_page_field->user_vars["name"] = $field_name;
				$obj_page_field->user_vars["send_mail"] = $db->getField("send_mail", "Number", true);
				$obj_page_field->user_vars["enable_in_mail"] = $db->getField("enable_in_mail", "Number", true);
				$obj_page_field->user_vars["unic_value"] = $db->getField("unic_value", "Number", true);
				$writable = $db->getField("writable", "Number", true);

		        if($db->getField("enable_tip", "Number", true))
        			$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name . $field_name) . "_tip");
		        else
					unset($obj_page_field->properties["title"]);

				$selection_value = array();        
				
				switch($db->getField("extended_type")->getValue())
				{
				    case "Selection":
				    case "Option":
				        $obj_page_field->base_type = "Text";

				        if($writable) {
                			if($db->getField("extended_type")->getValue() == "Option") {
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
				                                        vgallery_fields.ID = " . $db->toSql($db->getField("ID_field")) . " 
				                                        AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
				                               ) UNION (
				                                   SELECT
				                                        module_form_fields_selection_value.name AS nameID
				                                        , module_form_fields_selection_value.name AS name
				                                       FROM module_form_fields_selection_value 
				                                            INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
				                                       WHERE module_form_fields_selection_value.ID_selection = " . $db->toSql($db->getField("ID_selection", "Number")) . "
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
		                $obj_page_field->multi_select_one = !$db->getField("disable_select_one", "Number", true);

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
				                                   FROM vgallery_rel_nodes_fields
				                                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
				                                        INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
				                                   WHERE 
				                                        vgallery_fields.ID = " . $db->toSql($db->getField("ID_field")) . " 
				                                        AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
				                               ) UNION (
				                                   SELECT
				                                        module_form_fields_selection_value.name AS nameID
				                                        , module_form_fields_selection_value.name AS name
				                                       FROM module_form_fields_selection_value 
				                                            INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
				                                       WHERE module_form_fields_selection_value.ID_selection = " . $db->toSql($db->getField("ID_selection", "Number")) . "
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
				        
				        if(!$writable) {
				          $obj_page_field->default_value = new ffData(get_word_by_code("form_" . $unic_id . "_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name) . "_text"), "Text");
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

				        $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment/[form-ID_VALUE]";
				        $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment";
				        $obj_page_field->file_max_size = MAX_UPLOAD;

						$obj_page_field->file_show_filename = true; 
						$obj_page_field->file_full_path = true;
                        $obj_page_field->file_check_exist = true;
						$obj_page_field->file_normalize = true;
						 
						$obj_page_field->file_show_preview = true;
				        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
				        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//				        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
//				        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";

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

				        $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment/[form-ID_VALUE]";
				        $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment";
				        $obj_page_field->file_max_size = MAX_UPLOAD;

						$obj_page_field->file_show_filename = true; 
						$obj_page_field->file_full_path = true;
                        $obj_page_field->file_check_exist = true;
						$obj_page_field->file_normalize = true;
						 
						$obj_page_field->file_show_preview = true;
				        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
				        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//				        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
//				        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";

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

				        $obj_page_field->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment/[form-ID_VALUE]";
				        $obj_page_field->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/form/" . $form_name . "/comment";
				        $obj_page_field->file_max_size = MAX_UPLOAD;

						$obj_page_field->file_show_filename = true; 
						$obj_page_field->file_full_path = true;
                        $obj_page_field->file_check_exist = true;
						$obj_page_field->file_normalize = true;
						 
						$obj_page_field->file_show_preview = true;
				        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
				        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//				        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
//				        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";

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
				
				if($db->getField("require", "Number", true)) {
				    $obj_page_field->required = true;
				}
				
				if(strlen($db->getField("check_control")->getValue()))
				    $obj_page_field->addValidator($db->getField("check_control")->getValue());
				
				$oRecord->addContent($obj_page_field, $group_field);
			} while($db->nextRecord());

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
				$obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("form_register_privacy_" . preg_replace('/[^a-zA-Z0-9]/', '', $form_name)), "Text");
				$obj_page_field->properties["readonly"] = "readonly";
				$oRecord->addContent($obj_page_field, "privacy");

				$obj_page_field = ffField::factory($cm->oPage);
				$obj_page_field->id = "privacy_check";
				$obj_page_field->class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_check");
				$obj_page_field->label = ffTemplate::_get_word_by_code("form_register_privacy_check");
				$obj_page_field->base_type = "Number";
				$obj_page_field->control_type = "checkbox";
				$obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
				$obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
				$obj_page_field->required = true;
				$oRecord->addContent($obj_page_field, "privacy");
			}
		}
		$cm->oPage->addContent($oRecord);
	} else {
        $oRecord = ffRecord::factory($cm->oPage);
        $oRecord->id = $unic_id;
        if($layout_settings["AREA_COMMENT_SHOW_TITLE"])
            $oRecord->title =  ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', strtolower($layout["title"])) . "_title");

        $oRecord->class = $oRecord->class . " " . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($layout["title"])) . "_comment";
        $oRecord->src_table = "";
        $oRecord->skip_action = true;
        $oRecord->buttons_options["cancel"]["display"] = false;
        $oRecord->buttons_options["update"]["display"] = false;
        $oRecord->buttons_options["insert"]["display"] = false;
        if(check_function("process_addon_comment_view")) { 
            $oRecord->fixed_pre_content = process_addon_comment_view($user_path, $ret_url, $tbl_src, $ID_vgallery_node, null, $uid, $disable_control, $layout);
        }

        $cm->oPage->addContent($oRecord);
    }
}
?>
