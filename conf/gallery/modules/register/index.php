<?php
if(is_array($_POST) && count($_POST)) {
    use_cache(false);
}

$arrMetaDescription = array();
$arrSmartUrl = array(); 
        
$oRecord = ffRecord::factory($oPage); 
 
if(!strlen($oRecord->user_vars["MD_chk"]["params"][0])) {
    $db_gallery->query("SELECT module_register.*
                            FROM module_register
                            WHERE module_register.default = '1'");
    if($db_gallery->nextRecord()) {
        $oRecord->user_vars["MD_chk"]["params"][0] = $db_gallery->getField("name")->getValue();
    }                                        
}

$db_gallery->query("SELECT module_register.*
                        FROM module_register
                        WHERE module_register.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
if($db_gallery->nextRecord()) 
{
    $ID_register                 		= $db_gallery->getField("ID")->getValue();
    $register_name                 		= $db_gallery->getField("name")->getValue();
    $force_redirect                 	= $db_gallery->getField("force_redirect")->getValue();
    $fixed_pre_content                 	= $db_gallery->getField("fixed_pre_content")->getValue();  
    $fixed_post_content             	= $db_gallery->getField("fixed_post_content")->getValue(); 
    $enable_default_tip             	= $db_gallery->getField("enable_default_tip")->getValue();
    $display_require_note             	= $db_gallery->getField("enable_require_note")->getValue();
    $default_enable_general_data     	= $db_gallery->getField("enable_general_data")->getValue();
    $enable_bill_data                 	= $db_gallery->getField("enable_bill_data")->getValue();
    $enable_ecommerce_data             	= $db_gallery->getField("enable_ecommerce_data")->getValue();
    $enable_manage_account            	= $db_gallery->getField("enable_manage_account")->getValue();
    $default_enable_setting_data     	= $db_gallery->getField("enable_setting_data")->getValue();
    $enable_public                		= $db_gallery->getField("public")->getValue();
    $primary_gid                 		= $db_gallery->getField("primary_gid")->getValue();
    $activation                 		= $db_gallery->getField("activation")->getValue();
    $anagraph_type                 		= $db_gallery->getField("ID_anagraph_type")->getValue();
    //$require_note                 		= $db_gallery->getField("require_note")->getValue();
    $show_title                 		= $db_gallery->getField("show_title")->getValue();
    $disable_account_registration  		= $db_gallery->getField("disable_account_registration")->getValue();
    $simple_registration         		= $db_gallery->getField("simple_registration")->getValue();
    $ID_email                     		= $db_gallery->getField("ID_email", "Number", true);
    $ID_email_activation            	= $db_gallery->getField("ID_email_activation", "Number", true);
    $display_view_mode                 	= $db_gallery->getField("display_view_mode")->getValue();
    $default_show_label                	= $db_gallery->getField("default_show_label", "Number", true);
    $smart_url      					= $db_gallery->getField("smart_url", "Text", true);
    $meta_description     				= $db_gallery->getField("meta_description", "Text", true);
                
    $bill_type = "";
    
    if($activation)
        $active = 0;        
    else
        $active = 1;
    
    $generate_password = $db_gallery->getField("generate_password")->getValue();
	
    $enable_general_data = $default_enable_general_data; 
    $enable_setting_data = $default_enable_setting_data;
	
    if($anagraph_type > 0) 
    {
        $sSQL = "SELECT anagraph_type.* 
                    FROM anagraph_type 
                    WHERE anagraph_type.ID = " . $db_gallery->toSql($anagraph_type, "Number");
        $db_gallery->query($sSQL);
        if($db_gallery->nextRecord()) 
        {
            $enable_general_data	= $db_gallery->getField("show_general_group", "Number", true);
            $enable_setting_data 	= $db_gallery->getField("show_setting_group", "Number", true);

            $bill_type			= $db_gallery->getField("bill_type", "Text", true);

            $show_avatar 		= $db_gallery->getField("show_avatar", "Number", true);
            $show_avatar_group          = $db_gallery->getField("show_avatar_group", "Number", true);
            $avatar_type 		= $db_gallery->getField("avatar_type", "Number", true);
            $avatar_model 		= $db_gallery->getField("avatar_model", "Number", true);

            $show_custom_group          = $db_gallery->getField("show_custom_group", "Number", true);
            $bill_required 		= $db_gallery->getField("bill_required", "Number", true);
            $show_categories            = $db_gallery->getField("show_categories", "Number", true);

            $show_gmap 			= $db_gallery->getField("show_gmap", "Number", true);
            $show_user 			= $db_gallery->getField("show_user", "Number", true);
            $show_user_group            = $db_gallery->getField("show_user_group", "Number", true);
            $force_user_edit            = $db_gallery->getField("force_user_edit", "Number", true);
            $show_vcard			= $db_gallery->getField("show_vcard", "Number", true);
            $show_qrcode		= $db_gallery->getField("show_qrcode", "Number", true);
            $show_report_group          = $db_gallery->getField("show_report_group", "Number", true);
            $use_tab 			= $db_gallery->getField("use_tab", "Number", true);
            $force_custom_email         = $db_gallery->getField("force_custom_email", "Number", true);
            $force_user_edit            = $db_gallery->getField("force_user_edit", "Number", true);
        }	
        $oRecord->additional_fields["ID_type"] = new ffData($anagraph_type, "Number");
    } 

    $oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
    $oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
    $oRecord->skip_action = $disable_account_registration;
    $oRecord->additional_fields["created"] = new ffData(time(), "Number");

    if($display_view_mode) 
    {
    	$oRecord->class .= " " . $display_view_mode;

        if(!isset($globals))
             $globals = ffGlobals::getInstance("gallery");
        
        $oPage->tplAddJs("jquery." . $display_view_mode, "jquery." . $display_view_mode . ".js", FF_THEME_DIR . "/library/plugins/jquery." . $display_view_mode);
        if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . "jquery." . $display_view_mode .  ".observe.js")) 
            $tmp_js_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript" . "/" . "jquery." . $display_view_mode .  ".observe.js";
        else
            $tmp_js_path = FF_THEME_DIR . "/library/plugins/jquery." . $display_view_mode . "/" . "jquery." . $display_view_mode .  ".observe.js";
            
        $real_name = "";
        $real_path = "";
        $tmp_user_path = $globals->settings_path;
        do 
        {
            if(strlen($tmp_user_path) && $tmp_user_path != "/")
                $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
            else
                $real_name = basename($tmp_js_path);

            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . $real_name)) {
                $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
                break;
            }
        } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
        
        if(!strlen($real_path)) {
            $real_name = basename($tmp_js_path);
            $real_path = ffCommon_dirname($tmp_js_path);
        }
		
        if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
            $oPage->tplAddJs(ffGetFilename($tmp_js_path)
                                        , $real_name
                                        , $real_path
                                        , false
                                        , false
                            );
        }
    }
	
	if(isset($_REQUEST["referral"]) && strlen($_REQUEST["referral"])) {
		$oRecord->insert_additional_fields["referer"] = new ffData($_REQUEST["referral"], "Text");
	}
	
    if($show_title) {
        $oRecord->title =  ffTemplate::_get_word_by_code($oRecord->user_vars["MD_chk"]["tag"] . "_title"); 
    }
	
    $oRecord->src_table = "anagraph"; 
    $oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"];
    $oRecord->disable_mod_notifier_on_error = true;
    $oRecord->user_vars["ID_register"] = $ID_register;
    $oRecord->user_vars["activation"] = $activation;
    $oRecord->user_vars["enable_general_data"] = $enable_general_data;
    $oRecord->user_vars["enable_bill_data"] = $enable_bill_data;
    $oRecord->user_vars["enable_ecommerce_data"] = $enable_ecommerce_data;
    $oRecord->user_vars["visible"] = $enable_public;
    $oRecord->user_vars["anagraph_type"] = $anagraph_type;
    $oRecord->user_vars["disable_account_registration"] = $disable_account_registration;
    $oRecord->user_vars["simple_registration"] = $simple_registration;
    $oRecord->user_vars["ID_email"] = $ID_email;
    $oRecord->user_vars["ID_email_activation"] = $ID_email_activation;
    $oRecord->user_vars["ID_anagraph_type"] = $ID_anagraph_type;
    $oRecord->framework_css["actions"]["col"] = array("12");
    
    setJsRequest("ff.cms.register", "tools");
    
    if(check_function("MD_register_on_done_action")) { //	    if(check_function("MD_register_on_check_after"))
    	$oRecord->addEvent("on_check_after", "MD_register_on_check_after");
    	$oRecord->addEvent("on_done_action", "MD_register_on_done_action");
    }
    
    if($force_redirect)
        $oRecord->ret_url = $force_redirect;
    else
        $oRecord->ret_url = FF_SITE_PATH . VG_SITE_NOTIFY . "/register/end/" . $register_name;

    if($display_require_note)
        $oRecord->display_required_note = true;
    else 
        $oRecord->display_required_note = false;

    if($fixed_pre_content)
        $oRecord->fixed_pre_content = ffTemplate::_get_word_by_code("register_fixed_pre_content_" . $oRecord->user_vars["MD_chk"]["params"][0]);

    if($fixed_post_content)
        $oRecord->fixed_post_content = ffTemplate::_get_word_by_code("register_fixed_post_content_" . $oRecord->user_vars["MD_chk"]["params"][0]);

    //$oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = false;
    $oRecord->buttons_options["print"]["display"] = false;

    //if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" || $oRecord->user_vars["MD_chk"]["ajax"]) {
        $oButton = ffButton::factory($oPage);
        $oButton->id = "insert";
        //$oButton->class = "noactivebuttons";
        $oButton->action_type = "gotourl";
        $oButton->frmAction = "insert";
        //$oButton->url = $_REQUEST["ret_url"];
        $oButton->aspect = "link"; 
        //$oButton->image = "preview.png";
        $oButton->url = "javascript:void(0)";
        $oButton->properties["onclick"] =  "ff.cms.register.insert('" . $oRecord->user_vars["MD_chk"]["id"] . "');";
        $oButton->label = ffTemplate::_get_word_by_code("register_" . preg_replace('/[^a-zA-Z0-9]/', '', $register_name) . "_insert");
        $oRecord->buttons_options["insert"]["obj"] = $oButton;
    //} else {
    //    $oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name) . "_insert");
    //}
    
    $oRecord->additional_fields["primary_gid"] = new ffData($primary_gid, "Number");
    $oRecord->additional_fields["visible"] = new ffData($active, "Number");
    $oRecord->additional_fields["enable_general_data"] = new ffData($default_enable_general_data, "Number");
    $oRecord->additional_fields["enable_bill_data"] = new ffData($enable_bill_data, "Number");
    $oRecord->additional_fields["enable_ecommerce_data"] = new ffData($enable_ecommerce_data, "Number");
    $oRecord->additional_fields["enable_setting_data"] = new ffData($default_enable_setting_data, "Number");
    $oRecord->additional_fields["enable_manage"] = new ffData($enable_manage_account, "Number");
    $oRecord->additional_fields["visible"] = new ffData($enable_public, "Number"); 
    $oRecord->additional_fields["ID_module_register"] = new ffData($ID_register, "Number");    
    
    
    $obj_page_field = ffField::factory($oPage);
    $obj_page_field->id = "register-ID";
    $obj_page_field->base_type = "Number";
    $obj_page_field->data_source = "ID";
    $oRecord->addKeyField($obj_page_field);
    
    $sSQL = "SELECT extended_type.*
                FROM extended_type";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        do {
            $arrExtendedType[$db_gallery->getField("ID", "Number", true)] = array(
                "name" => $db_gallery->getField("name", "Text", true)
                , "ff_extended_type" => $db_gallery->getField("ff_name", "Text", true)
            );
        } while($db_gallery->nextRecord());
    }
    
    $sSQL = "SELECT check_control.*
                FROM check_control";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        do {
            $arrCheckControl[$db_gallery->getField("ID", "Number", true)] = array(
                "check_control" => $db_gallery->getField("ff_name", "Text", true)
            );
        } while($db_gallery->nextRecord());
    }
    
    $sSQL = "SELECT anagraph_fields.ID
                    , anagraph_fields.name
                    , anagraph_fields.ID_data_type
                    , anagraph_fields.ID_extended_type
                    , anagraph_fields.ID_selection
                    , anagraph_fields.data_source
                    , anagraph_fields.data_limit
                    , anagraph_fields.selection_data_source
                    , anagraph_fields.selection_data_limit
                    , anagraph_fields_selection.selectionSource AS selectionSource
                    , anagraph_fields_selection.field AS field
                    , anagraph_fields_selection.where_condition AS where_condition
                    , anagraph_fields_selection.ID_fields_child AS ID_fields_child
                    , anagraph_fields_selection.ID_fields_father AS ID_fields_father
                FROM anagraph_fields
                    LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields.ID_selection";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        do {
            $arrRegisterFields[$db_gallery->getField("ID", "Number", true)] = array(
                "name" => $db_gallery->getField("name", "Text", true)
                , "ID_extended_type" => $db_gallery->getField("ID_extended_type", "Number", true)
                , "ID_selection" => $db_gallery->getField("ID_selection", "Number", true)
                , "selectionSource" => $db_gallery->getField("selectionSource", "Text", true)
                , "selection_data_source" => $db_gallery->getField("selection_data_source", "Text", true)
                , "selection_data_limit" => $db_gallery->getField("selection_data_limit", "Text", true)
                , "field" => $db_gallery->getField("field", "Text", true)
                , "ID_fields_child" => $db_gallery->getField("ID_fields_child", "Number", true)
                , "ID_fields_father" => $db_gallery->getField("ID_fields_father", "Number", true)
                , "ID_data_type" => $db_gallery->getField("ID_data_type", "Number", true)
                , "data_source" => $db_gallery->getField("data_source", "Text", true)
                , "data_limit" => $db_gallery->getField("data_limit", "Text", true)
            );
        } while($db_gallery->nextRecord());
    }
    
    $db_gallery->query("SELECT module_register_fields.ID
                                , module_register_fields.name
                                , module_register_fields.ID_extended_type
                                , module_register_fields.default_grid
                                , module_register_fields.grid_md
                                , module_register_fields.grid_sm
                                , module_register_fields.grid_xs
                                , module_register_fields.enable_in_mail
                                , module_register_fields.unic_value
                                , module_register_fields.writable
                                , module_register_fields.disable_select_one
                                , module_register_fields.require
                                , module_register_fields.hide
                                , module_register_fields.hide_label
                                , module_register_fields.custom_placeholder
                                , module_register_fields.ID_selection
                                , module_register_fields.ID_check_control
                                , module_register_fields.ID_anagraph_fields
                                , module_register_fields.label_default_grid
                                , module_register_fields.label_grid_md
                                , module_register_fields.label_grid_sm
                                , module_register_fields.label_grid_xs
                                
                                , module_form_fields_group.name AS `group_field`
                                , module_form_fields_group.default_grid AS group_default_grid
                                , module_form_fields_group.grid_md AS group_grid_md
                                , module_form_fields_group.grid_sm AS group_grid_sm
                                , module_form_fields_group.grid_xs AS group_grid_xs
                                
                                , anagraph_fields_selection.selectionSource AS selectionSource
                                , anagraph_fields_selection.field AS field
                                , anagraph_fields_selection.where_condition AS where_condition
                                , anagraph_fields_selection.ID_fields_child AS ID_fields_child
                                , anagraph_fields_selection.ID_fields_father AS ID_fields_father
                                
                            FROM module_register_fields
                                LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_register_fields.ID_form_fields_group
                                LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = module_register_fields.ID_selection
                            WHERE module_register_fields.ID_module = " . $db_gallery->toSql($ID_register, "Number") . "
                                AND NOT(module_register_fields.hide_register > 0)
                            ORDER BY IF(module_form_fields_group.`order`, module_form_fields_group.`order`, 999), module_register_fields.`order`, module_register_fields.name");
    if($db_gallery->nextRecord()) 
    {
		$framework_css = cm_getFrameworkCss();
		do
        {
            $ID_anagraph_fields = $db_gallery->getField("ID_anagraph_fields", "Number", true);
            $ID_check_control = $db_gallery->getField("ID_check_control", "Number", true);
            $custom_placeholder = $db_gallery->getField("custom_placeholder", "Text", true);
            $hide = $db_gallery->getField("hide", "Number", true);
            
            
            if($ID_anagraph_fields > 0) {
                $field_name = $arrRegisterFields[$ID_anagraph_fields]["name"];
                
                
                $arrField[$field_name]["name"]                              = $field_name;
                $arrField[$field_name]["extended_type"]                     = $arrExtendedType[$arrRegisterFields[$ID_anagraph_fields]["ID_extended_type"]]["name"];
                $arrField[$field_name]["ff_extended_type"]                  = $arrExtendedType[$arrRegisterFields[$ID_anagraph_fields]["ID_extended_type"]]["ff_extended_type"];
                $arrField[$field_name]["ID_selection"]                      = $arrRegisterFields[$ID_anagraph_fields]["ID_selection"];
                
                $arrField[$field_name]["selectionSource"]                   = $arrRegisterFields[$ID_anagraph_fields]["selectionSource"];
                $arrField[$field_name]["selection_data_source"]             = $arrRegisterFields[$ID_anagraph_fields]["selection_data_source"];
                $arrField[$field_name]["selection_data_limit"]              = $arrRegisterFields[$ID_anagraph_fields]["selection_data_limit"];
                $arrField[$field_name]["field"]                             = $arrRegisterFields[$ID_anagraph_fields]["field"];
                $arrField[$field_name]["where_condition"]                   = $arrRegisterFields[$ID_anagraph_fields]["where_condition"];
                $arrField[$field_name]["ID_fields_child"]                   = $arrRegisterFields[$ID_anagraph_fields]["ID_fields_child"];
                $arrField[$field_name]["ID_fields_father"]                  = $arrRegisterFields[$ID_anagraph_fields]["ID_fields_father"];
                
                $arrField[$field_name]["ID_data_type"]                   = $arrRegisterFields[$ID_anagraph_fields]["ID_data_type"];
                $arrField[$field_name]["data_source"]                  = $arrRegisterFields[$ID_anagraph_fields]["data_source"];
                $arrField[$field_name]["data_limit"]                  = $arrRegisterFields[$ID_anagraph_fields]["data_limit"];
                if($arrRegisterFields[$ID_anagraph_fields]["ID_data_type"] == 4)
                    $oRecord->user_vars["save_info"]["anagraph_rel_nodes_fields"][$field_name]++;
                elseif($arrRegisterFields[$ID_anagraph_fields]["ID_data_type"] == 16 && $arrRegisterFields[$ID_anagraph_fields]["data_source"])
                    $oRecord->user_vars["save_info"][$arrField[$field_name]["data_source"]][$arrField[$field_name]["data_limit"]]++;
            } else {
                $field_name = $db_gallery->getField("name")->getValue();
                
                $arrField[$field_name]["name"]                              = $db_gallery->getField("name")->getValue();
                $arrField[$field_name]["extended_type"]                     = $arrExtendedType[$db_gallery->getField("ID_extended_type")->getValue()]["name"];
                $arrField[$field_name]["ff_extended_type"]                  = $arrExtendedType[$db_gallery->getField("ID_extended_type")->getValue()]["ff_extended_type"];
                
                $arrField[$field_name]["selectionSource"]                   = $db_gallery->getField("selectionSource")->getValue();
                
                
                
                
            }
            $arrFieldList[$db_gallery->getField("ID", "Number", true)] = $field_name;
            
            if($db_gallery->getField("ID_selection")->getValue()) {
                $arrField[$field_name]["ID_selection"]                      = $db_gallery->getField("ID_selection")->getValue();
            }
            
            if($db_gallery->getField("selectionSource")->getValue()) {
                $arrField[$field_name]["selectionSource"]                   = $db_gallery->getField("selectionSource")->getValue();
                $arrField[$field_name]["selection_data_source"]             = "";
                $arrField[$field_name]["field"]                             = $db_gallery->getField("field")->getValue();
                $arrField[$field_name]["where_condition"]                   = $db_gallery->getField("where_condition")->getValue();
                $arrField[$field_name]["ID_fields_child"]                   = $db_gallery->getField("ID_fields_child")->getValue();
                $arrField[$field_name]["ID_fields_father"]                  = $db_gallery->getField("ID_fields_father")->getValue();
            }
            $arrField[$field_name]["ID"]                                = $db_gallery->getField("ID")->getValue();
            
            
            $arrField[$field_name]["group"]["field"]                    = ($db_gallery->getField("group_field")->getValue() 
                                                                                ? ffCommon_url_rewrite($db_gallery->getField("group_field")->getValue()) 
                                                                                : ($show_custom_group ? "additfields" : null));
            $arrField[$field_name]["group"]["class"]["default"]         = ffCommon_url_rewrite($db_gallery->getField("group_field")->getValue());
            $arrField[$field_name]["class"]["default"]                  = "register_" . ffCommon_url_rewrite($arrField[$field_name]["name"]);
            $arrField[$field_name]["enable_in_mail"]                    = $db_gallery->getField("enable_in_mail", "Number", true);
            $arrField[$field_name]["unic_value"]                        = $db_gallery->getField("unic_value", "Number", true);
            $arrField[$field_name]["writable"]                          = $db_gallery->getField("writable", "Number", true);
            $arrField[$field_name]["disable_select_one"]                = $db_gallery->getField("disable_select_one", "Number", true);
            $arrField[$field_name]["require"]                           = $db_gallery->getField("require", "Number", true);
            $arrField[$field_name]["display_label"]                     = !$db_gallery->getField("hide_label", "Number", true);
            
            if($ID_check_control > 0) {
                $arrField[$field_name]["check_control"]                     = $arrCheckControl[$ID_check_control];
            }
            if($hide) {
                $arrField[$field_name]["class"]["hide"] = "hide";
            }
            if(strlen($custom_placeholder)) {
                $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($db_gallery->getField("custom_placeholder", "Text", true));
            } else {
                $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($arrField[$field_name]["name"]);
            }
            if($arrField[$field_name]["require"]) {
                $arrField[$field_name]["placeholder"] .= " *";
            }
            
                
            
            if(is_array($framework_css))
            {
                if(!array_key_exists("grid", $arrField[$field_name]["group"]["class"])) {
                    $arrField[$field_name]["group"]["class"]["grid"] = cm_getClassByFrameworkCss(array(
                            (int) $db_gallery->getField("group_grid_xs", "Number", true)
                            , (int) $db_gallery->getField("group_grid_sm", "Number", true)
                            , (int) $db_gallery->getField("group_grid_md", "Number", true)
                            , (int) $db_gallery->getField("group_default_grid", "Number", true)
                    ), "col"); 
                }

                $arrField[$field_name]["framework_css"]["component"] = array(
                    $db_gallery->getField("default_grid", "Number", true)
                    , $db_gallery->getField("grid_md", "Number", true)
                    , $db_gallery->getField("grid_sm", "Number", true)
                    , $db_gallery->getField("grid_xs", "Number", true)
                );

                if($arrField[$field_name]["display_label"]) {
                    $arrField[$field_name]["framework_css"]["label"] = array(
                        $db_gallery->getField("label_default_grid", "Number", true)
                        , $db_gallery->getField("label_grid_md", "Number", true)
                        , $db_gallery->getField("label_grid_sm", "Number", true)
                        , $db_gallery->getField("label_grid_xs", "Number", true)
                    );
                }
            } 
            
            if($db_gallery->getField("ID_fields_child", "Number", true) || $db_gallery->getField("ID_fields_father", "Number", true))
            {
                if($arrField[$field_name]["selectionSource"] == "city")
                {
                    $arrRel["0city"] = array("name" => $field_name
                                                , "ID" => $arrField[$field_name]["ID"]
                                                , "selectionSource" => $arrField[$field_name]["selectionSource"]
                                            );
                }
                if($arrField[$field_name]["selectionSource"] == "province")
                {
                    $arrRel["1province"] = array("name" => $field_name
                                                    , "ID" => $arrField[$field_name]["ID"]
                                                    , "selectionSource" => $arrField[$field_name]["selectionSource"]
                                                 );
                }
                if($arrField[$field_name]["selectionSource"] == "region")
                {
                    $arrRel["2region"] = array("name" => $field_name
                                                    , "ID" => $arrField[$field_name]["ID"]
                                                    , "selectionSource" => $arrField[$field_name]["selectionSource"]
                                                 );
                }
                if($arrField[$field_name]["selectionSource"] == "state")
                {
                    $arrRel["3state"] = array("name" => $field_name
                                                , "ID" => $arrField[$field_name]["ID"]
                                                , "selectionSource" => $arrField[$field_name]["selectionSource"]
                                             );
                }
            }
        } while($db_gallery->nextRecord());
    }
    
    if(is_array($arrRel) && count($arrRel) > 1)
    {
        $i = 0;
        krsort($arrRel);
        foreach($arrRel AS $key => $value)
        {
            if($ID)
            {
                if($i > 1)
                {
                    $arrField[$name]["child"] = $type;
                }
                $arrField[$name]["child_name"] = $value["ID"];
                $arrField[$value["name"]]["father_name"] = $ID;
                $arrField[$value["name"]]["father"] = $type;
            }
            $ID = $value["ID"];
            $name = $value["name"];
            $type = $value["selectionSource"];
            $i++;
        }
    }
    
    if(strlen($smart_url)) {
        $arr_smart_url_field = explode(",", $smart_url);
        foreach($arr_smart_url_field AS $ID_field => $value) {
            $arrSmartUrl[] = $arrFieldList[$value];
        }
        if(is_array($arrSmartUrl) && count($arrSmartUrl)) {
            $oRecord->user_vars["smart_url"] = $arrSmartUrl;
        }
    }
    
    if(strlen($meta_description)) {
        $arr_meta_description_field = explode(",", $meta_description);
        foreach($arr_meta_description_field AS $ID_field => $value) {
            $arrMetaDescription[] = $arrFieldList[$value];
        }
        if(is_array($arrMetaDescription) && count($arrMetaDescription)) {
            $oRecord->user_vars["meta_description"] = $arrMetaDescription;
        }
    }

    if(is_array($arrField) && count($arrField)) 
    {
        if(!array_key_exists("email", $arrField)) 
        {
            $arrField["email"] = array("name" => "email"
                                            , "class" => array("default" => "register_email")
                                            , "display_label" => $default_show_label
            );
        }
        if($enable_general_data || $disable_account_registration) 
        {
            $arrName = array("name" => array("name" => "name"
                                                , "class" => array("default" => "register_name")
                                                , "display_label" => $default_show_label
                                            )
                                , "surname" => array("name" => "surname"
                                                        , "class" => array("default" => "register_surname")
                                                        , "display_label" => $default_show_label
                                            )
                            );
            $arrField = array_replace_recursive($arrField, $arrName);
        }
            
        if(!$disable_account_registration) {
            $arrUser["password"] = array("name" => "password"
                                            , "class" => array("default" => "register_password")
                                            , "display_label" => $default_show_label
                                        );
            if(!$simple_registration) {
                    $arrUser["username"] = array("name" => "username"
                                                    , "class" => array("default" => "register_username")
                                                    , "display_label" => $default_show_label
                                                );
            }
            $arrField = array_replace_recursive($arrField, $arrUser);
        }
        
        
        foreach($arrField AS $field_key => $field_value) 
        {
            if (strlen($field_value["group"]["field"]) && !isset($oRecord->groups[$field_value["group"]["field"]])) 
            { 
                $oRecord->addContent(null, true, $field_value["group"]["field"]); 
                if($use_tab) {
                    $oRecord->addTab($field_value["group"]["field"]);
                    $oRecord->setTabTitle($field_value["group"]["field"], ffTemplate::_get_word_by_code("register_" . $field_value["group"]["field"]));
                } else {
                    $gridGroup[$field_value["group"]["field"]] = $db_gallery->toSql($field_value["group"]["field"], "Text");
                }
                $oRecord->groups[$field_value["group"]["field"]] = array(
                    "title" => ffTemplate::_get_word_by_code("register_" . $field_value["group"]["field"])
                    , "cols" => 1
                    , "class" => implode(" ", array_filter($field_value["group"]["class"]))
                    , "tab" => ($use_tab ? $field_value["group"]["field"] : null)
                );
            }

            if(is_array($field_value["class"]) && count($field_value["class"]))
                $field_class = implode(" ", $field_value["class"]);
                
            switch ($field_value["name"]) 
            {
                case "password" :
                    if(!$disable_account_registration) 
                    {
                        if($generate_password) {
                            $rnd_password = mod_sec_createRandomPassword();
                            $oRecord->insert_additional_fields["password"] = new ffData($rnd_password);
                        } else 
                        {
                            $obj_page_field = ffField::factory($oPage);
                            $obj_page_field->id = "password";
                            $obj_page_field->container_class = $field_class;
                            $obj_page_field->display_label = $field_value["display_label"];
                            $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);

                            if($field_value["placeholder"])
                                $obj_page_field->placeholder = $field_value["placeholder"];
                            else
                                $obj_page_field->placeholder = !$field_value["display_label"];

                            $obj_page_field->label = ffTemplate::_get_word_by_code("register_password");
                            $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                               
                            if($enable_default_tip)
                                $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "password") . "_tip");   
                                
                            $obj_page_field->extended_type = "Password";
                            $obj_page_field->crypt_method = "mysql_password";
                                
                            if(ENABLE_PASSWORD_VALIDATOR) {
                                $obj_page_field->addValidator("password");
                            }
                                
                            $obj_page_field->required = true;
                            $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                                
                            $obj_page_field = ffField::factory($oPage);
                            $obj_page_field->id = "confirmpassword";
                            $obj_page_field->container_class = "confirm " . $field_class;
                            $obj_page_field->display_label = $field_value["display_label"];
                            $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                            $obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_password");
                            $obj_page_field->placeholder = !$field_value["display_label"];
                            if($enable_default_tip)
                                $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmpassword") . "_tip");   
                            $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                            $obj_page_field->extended_type = "Password";
                            $obj_page_field->compare = "password";
                            $obj_page_field->required = true;
                            $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);    
                        }
                    } 
                    break;
                case "email":
                    if($simple_registration) {
                        $obj_page_field = ffField::factory($oPage);
                        $obj_page_field->id = "email";
                        $obj_page_field->container_class = $field_class;
                        $obj_page_field->display_label = $field_value["display_label"];
                        $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                        if($field_value["placeholder"])
                            $obj_page_field->placeholder = $field_value["placeholder"];
                        else
                            $obj_page_field->placeholder = !$field_value["display_label"];

                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
                        if($enable_default_tip)
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "email") . "_tip");   
                        $obj_page_field->required = true;
                        $obj_page_field->addValidator("email");
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
						if(isset($_REQUEST["email"])) {
							$obj_page_field->default_value = new ffData($_REQUEST["email"], "Text");
						}
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    } else {
                        $obj_page_field = ffField::factory($oPage);
                        $obj_page_field->id = "email";
                        $obj_page_field->container_class = $field_class;
                        $obj_page_field->display_label = $field_value["display_label"];
                        $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                        if($field_value["placeholder"])
                            $obj_page_field->placeholder = $field_value["placeholder"];
                        else
                            $obj_page_field->placeholder = !$field_value["display_label"];
                        $obj_page_field->addValidator("email");
                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
                        if($enable_default_tip)
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "email") . "_tip");   
                        $obj_page_field->required = true;
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
						if(isset($_REQUEST["email"])) {
							$obj_page_field->default_value = new ffData($_REQUEST["email"], "Text");
						}
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);

                        $obj_page_field = ffField::factory($oPage);
                        $obj_page_field->id = "confirmemail";
                        $obj_page_field->container_class = "confirm " . $field_class;
                        $obj_page_field->display_label = $field_value["display_label"];
                        $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                        $obj_page_field->placeholder = !$field_value["display_label"];
                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_email");
                        if($enable_default_tip) {
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "confirmemail") . "_tip");   
                        } 
                        $obj_page_field->compare = "email";
                        $obj_page_field->required = true;
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
						if(isset($_REQUEST["email"])) {
							$obj_page_field->default_value = new ffData($_REQUEST["email"], "Text");
						}
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    } 
                    break;
                case "username":
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "username";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_username");
                        if($enable_default_tip) {
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "username") . "_tip");   
                        }
                        $obj_page_field->required = true;
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                        
                    break;
                case "avatar":
                    if($enable_general_data) 
                    {
                        if(ENABLE_AVATAR_SYSTEM && $show_avatar) 
                        {                                          
                            $obj_page_field = ffField::factory($oPage);
                            $obj_page_field->id = "avatar";
                            $obj_page_field->container_class = $field_class;
                            if($field_value["placeholder"])
                                $obj_page_field->placeholder = $field_value["placeholder"];
                            else
                                $obj_page_field->placeholder = !$field_value["display_label"];

                            $obj_page_field->label = ffTemplate::_get_word_by_code("user_account_avatar");
                            $obj_page_field->base_type = "Text";
                            $obj_page_field->extended_type = "File";
                            $obj_page_field->file_storing_path = DISK_UPDIR . "/user/[register-ID_VALUE]";
                            $obj_page_field->file_temp_path = DISK_UPDIR . "/user";
                            $obj_page_field->file_max_size = MAX_UPLOAD;
                            $obj_page_field->file_full_path = true;
                            $obj_page_field->file_check_exist = true;
                            $obj_page_field->file_normalize = true;
                            $obj_page_field->file_show_preview = true;

                            $obj_page_field->uploadify_model = $avatar_model;
                            $obj_page_field->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);

                            $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
                            $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $obj_page_field->uploadify_model_thumb . "/[_FILENAME_]";
            
                            $obj_page_field->control_type = "file";
                            $obj_page_field->file_show_delete = true;
                            $obj_page_field->widget = "uploadify";
                            if(check_function("set_field_uploader")) { 
                                $obj_page_field = set_field_uploader($obj_page_field);
                            }
                            $obj_page_field->file_show_filename = false; 
                            $oRecord->addContent($obj_page_field, $field_value["group"]["field"]); 
                        }
                    }
                    break;
                case "name" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "name";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_name");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "name") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "surname" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "surname";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_surname");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "surname") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "tel" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "tel";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_tel");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "tel") . "_tip"); 
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "reference" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billreference";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_reference");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billreference") . "_tip");
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "cf":
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billcf";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_cf");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcf") . "_tip");   
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->addValidator("cf");
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "piva" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billpiva";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_piva");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billpiva") . "_tip");   
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->addValidator("piva");
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]); 
                    break;
                case "address" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billaddress";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_address");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billaddress") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $obj_page_field->required = $field_value["require"];
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "cap" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billcap";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_cap");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcap") . "_tip");   
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "town":
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billtown";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_town");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billtown") . "_tip");   
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "province" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "billprovince";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_province");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billprovince") . "_tip");   
                    }
                    $obj_page_field->required = $field_value["require"];
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "state":
                    if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
                        $oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
                    } else {
                        $obj_page_field = ffField::factory($oPage);
                        $obj_page_field->id = "billstate";
                        $obj_page_field->container_class = $field_class;
                        $obj_page_field->display_label = $field_value["display_label"];
                        $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                        if($field_value["placeholder"])
                            $obj_page_field->multi_select_one_label = $field_value["placeholder"];
                        else
                            $obj_page_field->multi_select_one_label = ffTemplate::_get_word_by_code("register_bill_state");

                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_state");
                        if($enable_default_tip) {
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billstate") . "_tip");   
                        }
                        $obj_page_field->base_type = "Number";
                        $obj_page_field->widget = "activecomboex";
                        $obj_page_field->actex_update_from_db = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                        $obj_page_field->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
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
                                                        FROM " . FF_SUPPORT_PREFIX . "state
                                                        ORDER BY description";
                        $obj_page_field->required = $field_value["require"];
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    }
                    break;
                case "shippingreference" : 
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "shippingreference";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_reference");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingreference") . "_tip");
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "shippingaddress" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "shippingaddress";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_address");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingaddress") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "shippingcap" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "shippingcap";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];
                            
                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_cap");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingcap") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "shippingtown" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "shippingtown";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_town");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingtown") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "shippingprovince" :
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "shippingprovince";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_province");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingprovince") . "_tip");   
                    }
                    $obj_page_field->required = true;
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                case "shippingstate" :
                    if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
                        $oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
                    } else {
                        $obj_page_field = ffField::factory($oPage);
                        $obj_page_field->id = "shippingstate";
                        $obj_page_field->container_class = $field_class;
                        $obj_page_field->display_label = $field_value["display_label"];
                        $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                        if($field_value["placeholder"])
                            $obj_page_field->multi_select_one_label = $field_value["placeholder"];
                        else
                            $obj_page_field->multi_select_one_label = ffTemplate::_get_word_by_code("register_shipping_state");

                        $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_state");
                        if($enable_default_tip) {
                            $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingstate") . "_tip");   
                        }
                        $obj_page_field->base_type = "Number";
                        $obj_page_field->widget = "activecomboex";
                        $obj_page_field->actex_update_from_db = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                        $obj_page_field->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
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
                                                        FROM " . FF_SUPPORT_PREFIX . "state
                                                        ORDER BY description";
                        $obj_page_field->required = true;
                        $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                        $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    }
                    break;
                case "privacy" :
                
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_text";
                    $obj_page_field->container_class = $field_class;
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_text") . "_tip");   
                    }
                    $obj_page_field->label = "";
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";
                    $obj_page_field->control_type = "textarea";
                    $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("register_privacy_text_" . $oRecord->user_vars["MD_chk"]["params"][0]), "Text");
                    $obj_page_field->properties["readonly"] = "readonly";
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->setWidthComponent(12,12,12,12);
                    
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_check";
                    $obj_page_field->container_class = "check " . $field_class;
                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_privacy_check");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_check") . "_tip");   
                    }
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->control_type = "checkbox";
                    $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->required = true;
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->framework_css["container"]["row"] = false;
                    $oRecord->addContent($obj_page_field, "privacy");
                        
                    $oRecord->addContent($obj_page_field, "privacy");
                case "privacy-html" :
                    $obj_page_field = ffField::factory($oPage);
                    if($field_value["name"] === "privacy-html")
                        $obj_page_field->fixed_pre_content = ffTemplate::_get_word_by_code("register_privacy_html_" . $oRecord->user_vars["MD_chk"]["params"][0]);
                    $obj_page_field->id = "privacy_check";
                    $obj_page_field->container_class = "check " . $field_class;
                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_privacy_check");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_check") . "_tip");   
                    }
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->control_type = "checkbox";
                    $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->required = true;
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->framework_css["container"]["row"] = false;
                    $oRecord->addContent($obj_page_field, "privacy");
                    
                    break;
                case "privacy-complete":
                    $oRecord->addEvent("on_do_action", "check_privacy_on_do_action");
                    
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_text_1";
                    $obj_page_field->container_class = $field_class;
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_text") . "_part1_tip");   
                    }
                    $obj_page_field->fixed_pre_content = ffTemplate::_get_word_by_code("register_privacy_html_" . $oRecord->user_vars["MD_chk"]["params"][0] . "_part1");
                    $obj_page_field->label = "";
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";
                    $obj_page_field->control_type = "textarea";
                    $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("register_privacy_text_" . $oRecord->user_vars["MD_chk"]["params"][0] . "_part1"), "Text");
                    $obj_page_field->properties["readonly"] = "readonly";
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->setWidthComponent(12,12,12,12);
                    $oRecord->addContent($obj_page_field, "privacy");
                    
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_check_1";
                    $obj_page_field->container_class = "check " . $field_class;
                    $obj_page_field->label = false;
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_check") . "_tip");   
                    }
                    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("privacy_check_1");
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->control_type = "radio";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "";
                    $obj_page_field->multi_pairs = array (
                        array(new ffData("1", "Number"), new ffData('<label for="MD-Content-register-' . $oRecord->user_vars["MD_chk"]["params"][0] . '_privacy_check_1_0">' . ffTemplate::_get_word_by_code("Accetto") . '</label>'))
                        , array(new ffData("0", "Number"), new ffData('<label for="MD-Content-register-' . $oRecord->user_vars["MD_chk"]["params"][0] . '_privacy_check_1_1">' . ffTemplate::_get_word_by_code("Non accetto") . '</label>'))
                        
                    );
                    $obj_page_field->encode_entities = false;
                    $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->required = true;
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->framework_css["container"]["row"] = false;
                    $oRecord->addContent($obj_page_field, "privacy");
                        
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_text_2";
                    $obj_page_field->container_class = $field_class;
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_text") . "_part2_tip");   
                    }
                    $obj_page_field->fixed_pre_content = ffTemplate::_get_word_by_code("register_privacy_html_" . $oRecord->user_vars["MD_chk"]["params"][0] . "_part2");
                    $obj_page_field->label = "";
                    $obj_page_field->base_type = "Text";
                    $obj_page_field->extended_type = "Text";
                    $obj_page_field->control_type = "textarea";
                    $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("register_privacy_text_" . $oRecord->user_vars["MD_chk"]["params"][0] . "_part2"), "Text");
                    $obj_page_field->properties["readonly"] = "readonly";
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->setWidthComponent(12,12,12,12);
                    $oRecord->addContent($obj_page_field, "privacy");
                    
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "privacy_check_2";
                    $obj_page_field->container_class = "check " . $field_class;
                    $obj_page_field->label = false;
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "privacy_check") . "_tip");   
                    }
                    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("privacy_check_2");
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->control_type = "radio";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "";
                    $obj_page_field->multi_pairs = array (
                        array(new ffData("1", "Number"), new ffData('<label for="MD-Content-register-' . $oRecord->user_vars["MD_chk"]["params"][0] . '_privacy_check_2_0">' . ffTemplate::_get_word_by_code("Accetto") . '</label>')),
                        array(new ffData("0", "Number"), new ffData('<label for="MD-Content-register-' . $oRecord->user_vars["MD_chk"]["params"][0] . '_privacy_check_2_1">' . ffTemplate::_get_word_by_code("Non accetto") . '</label>'))
                    );
                    $obj_page_field->encode_entities = false;
                    $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->required = true;
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->framework_css["container"]["row"] = false;
                    $oRecord->addContent($obj_page_field, "privacy");
                case "newsletter":
					if(isset($_REQUEST["referral"]) && strlen($_REQUEST["referral"])) {
						$oRecord->insert_additional_fields["newsletter"] = new ffData(1, "Number");
					} else {
						$obj_page_field = ffField::factory($oPage);
						$obj_page_field->id = "newsletter";
						$obj_page_field->container_class = "newsletter" . $field_class;
						$obj_page_field->label = ffTemplate::_get_word_by_code("register_newsletter");
						if($enable_default_tip) {
							$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "newsletter") . "_tip");
						}
						$obj_page_field->fixed_post_content = ffTemplate::_get_word_by_code("newsletter_text_" . $oRecord->user_vars["MD_chk"]["params"][0]);
						$obj_page_field->base_type = "Number";
						$obj_page_field->control_type = "checkbox";
						$obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
						$obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
						$obj_page_field->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
						$obj_page_field->data_type = "";
						$obj_page_field->framework_css["container"]["row"] = false;
						$oRecord->addContent($obj_page_field, "privacy");
					}
                    break;
                case "newsletter_DEM":
                    $obj_page_field = ffField::factory($oPage); 
                    $obj_page_field->id = "newsletter_DEM";
                    $obj_page_field->container_class = "newsletter_DEM" . $field_class;
                    $obj_page_field->label = ffTemplate::_get_word_by_code("newsletter_DEM");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "newsletter_OEM") . "_tip");
                    }
                    $obj_page_field->fixed_post_content = ffTemplate::_get_word_by_code("newsletter_DEM_text_" . $oRecord->user_vars["MD_chk"]["params"][0]);
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->control_type = "checkbox";
                    $obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $obj_page_field->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE); 
                    $obj_page_field->data_type = "";
                    $obj_page_field->framework_css["container"]["row"] = false;
                    $oRecord->addContent($obj_page_field, "privacy");
                    break;
                case "degree":
                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->id = "degree";
                    $obj_page_field->container_class = $field_class;
                    $obj_page_field->display_label = $field_value["display_label"];
                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->multi_select_one_label = $field_value["placeholder"];
                    elseif(!$field_value["display_label"])
                        $obj_page_field->multi_select_one_label = ffTemplate::_get_word_by_code("register_degree");
                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_degree");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "degree") . "_tip");   
                    }
                    $obj_page_field->base_type = "Number";
                    $obj_page_field->widget = "activecomboex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_service = FF_SITE_PATH . "/srv/degree?type=selection";
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    break;
                default:
                    $field_id = $field_value["ID"];

                    $obj_page_field = ffField::factory($oPage);
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->id = $field_id;
                    $obj_page_field->user_vars["group"]["field"] = $field_value["group"]["field"];
                    $obj_page_field->user_vars["name"] = ffCommon_url_rewrite($field_value["name"]); 
                    $obj_page_field->user_vars["enable_in_mail"] = $field_value["enable_in_mail"];
                    $obj_page_field->user_vars["unic_value"] = $field_value["unic_value"]; 
                    $obj_page_field->data_type = "";
                
                    if(check_function("get_field_by_extension"))
                        $js .= get_field_by_extension($obj_page_field, $field_value, "register");
                    if(isset($_GET[$field_value["name"]]) && strlen($_GET[$field_value["name"]])) {
                        $obj_page_field->default_value = new ffData($_REQUEST[$field_value["name"]], $field_value["ff_extended_type"]);
                    } 
                    $obj_page_field->setWidthComponent($field_value["framework_css"]["component"]);
                    if($enable_default_tip)
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $field_value["name"]) . "_tip");   
                     
                    $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                    
                    break;
            }
        }
    }
    
    $oPage->addContent($oRecord);  
}

function check_privacy_on_do_action($component, $action) {
    if(strlen($action)) {
        if(isset($component->form_fields["privacy_check_1"]) && !$component->form_fields["privacy_check_1"]->getValue()) {
            $component->tplDisplayError(ffTemplate::_get_word_by_code("field_privacy_check_1_required"));
            return true;
        }
        
        if(isset($component->form_fields["privacy_check_2"]) && !$component->form_fields["privacy_check_2"]->getValue()) {
            $component->tplDisplayError(ffTemplate::_get_word_by_code("field_privacy_check_2_required"));
            return true;
        }
    }
}