<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_MODULES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(!isset($_REQUEST["keys"]["formcnf-ID"])) {
    if(!strlen(basename($cm->real_path_info)) && isset($_REQUEST["name"]))
    $cm->real_path_info = "/" . $_REQUEST["name"];

    $db_gallery->query("SELECT module_form.*
                            FROM module_form
                            WHERE module_form.name = " . $db_gallery->toSql(new ffData( basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["formcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } else {
        if($_REQUEST["keys"]["ID"] > 0) {
            $db_gallery->execute("DELETE
                                    FROM modules
                                    WHERE modules.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
                                );
            if($_REQUEST["XHR_DIALOG_ID"]) {
                die(ffCommon_jsonenc(array("resources" => array("modules"), "close" => true, "refresh" => true), true));
            } else {
                ffRedirect($_REQUEST["ret_url"]);
            } 
        }
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("form_modify");
$oRecord->src_table = "module_form";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_form.*
									, IF(module_form.display_name = ''
										, REPLACE(module_form.name, '-', ' ')
										, module_form.display_name
									) AS display_name
								FROM module_form 
								WHERE module_form.ID =" . $db_gallery->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
                               
$oRecord->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "FormExtraFieldModify_on_done_action");

$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "formcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["keys"]["formcnf-ID"])) {
    $module_form_title = ffTemplate::_get_word_by_code("modify_module_form");
    $sSQL = "SELECT module_form.name
                FROM module_form
                WHERE module_form.ID = " . $db_gallery->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord())
    {
        $module_form_title .= ": " . $db_gallery->getField("name", "Text", true);
    }
    $oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "form")) . $module_form_title . '</h1>';

	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("form_config_name");
	$oField->required = true;
	$oRecord->addContent($oField);

	$sSQL = "SELECT module_form.*
				, " . (Cms::env("AREA_SHOW_ECOMMERCE")
					? "IF(field_enable_pricelist
						, (SELECT COUNT(module_form_fields.ID) AS count_pricelist
			                FROM module_form_fields
			                WHERE module_form_fields.ID_module = module_form.ID
	                    		AND module_form_fields.`type` = 'pricelist'
						)
						, 0
					)"
					: "0"
				) . " AS enable_pricelist    				 
	        FROM module_form
	        WHERE module_form.ID = " . $db_gallery->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
	    $field_enable_dep = $db_gallery->getField("field_enable_dep", "Number", true);
	    $enable_pricelist = $db_gallery->getField("enable_pricelist", "Number", true);
	    $enable_ecommerce = $db_gallery->getField("enable_ecommerce", "Number", true);
	}
	if(Cms::env("AREA_SHOW_ECOMMERCE") && $enable_ecommerce) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "skip_vat_by_anagraph_type";
		$oField->label = ffTemplate::_get_word_by_code("form_config_skip_vat_by_anagraph_type");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData("0", "Number");
		$oRecord->addContent($oField);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "skip_shipping_calc";
		$oField->label = ffTemplate::_get_word_by_code("form_config_skip_shipping_calc");
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData("1", "Number");
		$oRecord->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "discount_perc";
		$oField->label = ffTemplate::_get_word_by_code("form_config_discount_perc");
		$oField->base_type = "Number";
		$oRecord->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "discount_val";
		$oField->label = ffTemplate::_get_word_by_code("form_config_discount_val");
		$oField->base_type = "Number";
		$oField->app_type = "Currency";
		$oRecord->addContent($oField);
	}	

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->dialog_action_button = true;
    //$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
    $oGrid->id = "FormConfigField";
    $oGrid->source_SQL = "SELECT module_form_fields.* 
                                , module_form_fields_group.name AS group_name
                                , module_form_fields_group.`order` AS group_order
                                FROM module_form_fields
                                LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                            WHERE module_form_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
                                [AND] [WHERE] 
                            [HAVING] 
                            [ORDER]";
    $oGrid->order_default = "formcnfield-ID";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->use_paging = false;
    $oGrid->record_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/modules/form/extra/modify";
    $oGrid->record_id = "FormExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "form_fields"
                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
            )
            , "formcnfield-ID"
        )
    );
    $oGrid->addEvent("on_before_parse_row", "FormConfigField_on_before_parse_row");
    //$oGrid->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "formcnfield-ID";
    $oField->base_type = "Number";
    $oField->data_source = "ID";
    $oField->order_SQL = " `group_order`, `order`, name";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "group_name";
    $oField->container_class = "group";
    $oField->label = ffTemplate::_get_word_by_code("form_field_group");
    $oField->base_type = "Text";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->container_class = "name";
    $oField->label = ffTemplate::_get_word_by_code("form_field_name");
    $oField->base_type = "Text";
    $oGrid->addContent($oField); 
	
	$oField = ffField::factory($cm->oPage);
    $oField->id = "type";
    $oField->container_class = "field-type";
    $oField->label = ffTemplate::_get_word_by_code("form_field_type");
    $oField->base_type = "Text";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array(
								array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("form_fields_type_simple")))
								, array(new ffData("price"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_price")))
								, array(new ffData("multiplier"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_multiplier")))
								, array(new ffData("pricelist"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_pricelist")))
							);
	$oField->multi_select_one = false;
    $oGrid->addContent($oField); 
/*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "aspect";
    $oField->container_class = "aspect";
    $oField->label = ffTemplate::_get_word_by_code("form_field_aspect");
    $oField->base_type = "Text";
    $oField->data_type = "";
    $oGrid->addContent($oField); 
*/

     /*   
    if($cm->oPage->isXHR()) {
        if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
            if(strlen($_REQUEST["XHR_DIALOG_ID"])) {
                    $oButton = ffButton::factory($cm->oPage);
                    $oButton->id = "update";
                    $oButton->action_type = "submit";
                    $oButton->jsaction = "ff.ffPage.dialog.doAction('" . $_REQUEST["XHR_DIALOG_ID"] . "', 'close');";     //da sostemare facendo update dell'activecombo
                    $oButton->label = ffTemplate::_get_word_by_code("bt_update");
                    $oButton->aspect = "link";
                    $oGrid->addActionButton($oButton);
            }
        } else {
            $oButton = ffButton::factory($cm->oPage);
            $oButton->id = "update";
            $oButton->action_type = "submit";
            $oButton->jsaction = "window.location.reload();";
            $oButton->aspect = "link";
            $oButton->label = ffTemplate::_get_word_by_code("bt_update");
            $oGrid->addActionButton($oButton);
			
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $oButton = ffButton::factory($cm->oPage);
                $oButton->id = "close";
                $oButton->action_type = "submit";
                $oButton->jsaction = "ff.ffPage.dialog.doAction('" . $_REQUEST["XHR_DIALOG_ID"] . "', 'close');";
                $oButton->aspect = "link";
                $oButton->label = ffTemplate::_get_word_by_code("bt_close");
                $oGrid->addActionButton($oButton);
			}	            
        }
    }*/
    
    if($field_enable_dep) {
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "module_form_dep";
        $oButton->aspect = "link";
		$oButton->display_label = false;
        $oGrid->addGridButton($oButton);

        //ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "module_form_dep_menu";
        $oButton->aspect = "link"; 
        $oButton->label = ffTemplate::_get_word_by_code("module_form_dep_rule_title");
            
        if($_REQUEST["XHR_DIALOG_ID"])
        {
            $cm->oPage->widgetLoad("dialog");
            $cm->oPage->widgets["dialog"]->process(
                "module_form_dep_rule"
                , array(
                    "title" => ffTemplate::_get_word_by_code("module_form_dep_rule")
                    , "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/dep?keys[formcnf-ID]=" . $_REQUEST["keys"]["formcnf-ID"]
                )
                , $cm->oPage
            );
            $oButton->jsaction = "ff.ffPage.dialog.doOpen('module_form_dep_rule')";
        } else {
            $oButton->action_type = "gotourl";
            $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/dep?[KEYS]";
        }
        $oGrid->addActionButtonHeader($oButton);
            
            
    }
    
    if(Cms::env("AREA_SHOW_ECOMMERCE") && $enable_pricelist)
    {
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "module_form_pricelist";
        $oButton->aspect = "link"; 
        $oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_title");


        if($_REQUEST["XHR_DIALOG_ID"])
        {
            $cm->oPage->widgetLoad("dialog");
            $cm->oPage->widgets["dialog"]->process(
                "module_form_pricelist"
                , array(
                    "title" => ffTemplate::_get_word_by_code("module_form_pricelist")
                    , "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/pricelist?keys[formcnf-ID]=" . $_REQUEST["keys"]["formcnf-ID"]
                )
                , $cm->oPage
            );
            $oButton->jsaction = "ff.ffPage.dialog.doOpen('module_form_pricelist')";
        } else {
            $oButton->action_type = "gotourl";
            $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/pricelist?[KEYS]";
        }
        $oGrid->addActionButtonHeader($oButton);
    }    
    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);  
} else {  
    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("form_copy");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT module_form.ID
        						, IF(module_form.display_name = ''
									, REPLACE(module_form.name, '-', ' ')
									, module_form.display_name
								) AS name
        					FROM module_form
        					WHERE 1
        					ORDER BY module_form.name";
    $oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);  
}

$cm->oPage->addContent($oRecord);

function FormConfigField_on_before_parse_row($component) {
    if(isset($component->grid_buttons["module_form_dep"])) {
        $component->grid_buttons["module_form_dep"]->class = Cms::getInstance("frameworkcss")->get("chain", "icon");
        $component->grid_buttons["module_form_dep"]->action_type = "submit"; 
        $component->grid_buttons["module_form_dep"]->label = ffTemplate::_get_word_by_code("module_form_dep");
        $component->grid_buttons["module_form_dep"]->form_action_url = $component->grid_buttons["module_form_dep"]->parent[0]->page_path . "/dep?[KEYS]" . $component->grid_buttons["module_form_dep"]->parent[0]->addit_record_param . "setcv=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $component->grid_buttons["module_form_dep"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        } else {
            $component->grid_buttons["module_form_dep"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        }
    }
    if(isset($component->grid_fields["aspect"])) 
    {
        
    }
}

function FormExtraFieldModify_on_do_action($component, $action) {
    switch($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];
            if(isset($component->form_fields["copy-from"])) {
                if(check_function("MD_form_on_done_action")) {
                    $res = MD_form_clone($component->form_fields["copy-from"]->getValue(), $_REQUEST["clonename"]);
                    if($res["ID"] > 0) {
    //, "callback" => "ff.ffField.activecomboex.dialog_success('VGalleryNodesModifyDetail_recordset[0][46]', 'FormExtraFieldModify')"
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[formcnf-ID]=" . $res["ID"] . "&noredirect&ret_url=" . urlencode($ret_url) , "close" => false, "refresh" => true, "insert_id" => $res["name"], "resources" => array("FormExtraFieldModify")), true));
                        //ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[formcnf-ID]=" . $ID_form . "&noredirect&ret_url=" . urlencode($ret_url));                
                    }
                }
            }
        break;
		default:
    }
    
}


function FormExtraFieldModify_on_done_action($component, $action) {
    switch($action) {
        case "update":
        	if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}
        	
        	break;
        case "confirmdelete":
        	if(check_function("MD_form_delete"))
        		MD_form_delete($component->key_fields["formcnf-ID"]->getValue());

        	if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
        		die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}
        	break;
        default:
    }
    return true;
}
?>