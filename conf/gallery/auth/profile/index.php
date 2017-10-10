<?php
use_cache(false);

require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);


$UserNID = get_session("UserNID");
//$user_path = $cm->real_path_info;
$UserID = get_session("UserID");

$ret_url = urldecode($_REQUEST["ret_url"]);
if(!strlen($ret_url))
    $ret_url = FF_SITE_PATH . "/";

/*
if(check_function("check_user_request"))
	$additionaldata = check_user_form_request(array("ID" => $UserNID));
if($additionaldata)
    ffRedirect(FF_SITE_PATH . USER_RESTRICTED_PATH . "/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($ret_url));
*/
//Controllo permessi e settaggio per la parte di modifica

if($UserID == MOD_SEC_GUEST_USER_NAME && !strlen(basename($cm->real_path_info))) { 
    ffRedirect(FF_SITE_PATH . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
} 

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register
            FROM " . CM_TABLE_PREFIX . "mod_security_users
            WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_gallery->toSql($UserNID, "Number");
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $ID_type = $db_gallery->getField("ID_module_register", "Number", true);
}




if($ID_type) {
    $sSQL = "SELECT ID, username, email
            FROM anagraph
            WHERE ID_type = " . $db_gallery->toSql($ID_type, "Number") . "
                AND uid = " . $db_gallery->toSql($UserNID, "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
        $username = $db_gallery->getField("username", "Text", true);
        $email = $db_gallery->getField("email", "Text", true);
    }
    $sSQL = "SELECT ID
                    , name
                    , default_grid
                    , grid_md
                    , grid_sm
                    , grid_xs
                FROM anagraph_type_group";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        do {
            $arrAnagraphGroup[$db_gallery->getField("ID", "Number", true)] = array(
                "name" => $db_gallery->getField("name", "Text", true)
                , "smart_url" => ffcommon_url_rewrite($db_gallery->getField("name", "Text", true))
            );
            if(is_array($cm->oPage->framework_css))
            {
                $arrAnagraphGroup[$db_gallery->getField("ID", "Number", true)]["class"] = cm_getClassByFrameworkCss(array(
                        (int) $db_gallery->getField("grid_xs", "Number", true)
                        , (int) $db_gallery->getField("grid_sm", "Number", true)
                        , (int) $db_gallery->getField("grid_md", "Number", true)
                        , (int) $db_gallery->getField("default_grid", "Number", true)
                ), "col"); 
            }
        } while ($db_gallery->nextRecord());
    }
                
    $sSQL = "SELECT module_register.name AS register_name
                    , module_register.ID AS ID_register
                    , module_register.display_view_mode AS display_view_mode
                    , module_register.ID_anagraph_type AS ID_anagraph_type
                    , module_register.simple_registration
                    , module_register.disable_account_registration
                FROM module_register
                WHERE module_register.ID = " . $db_gallery->toSql($ID_type, "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $ID_register = $db_gallery->getField("ID_register", "Number", true);
        $register_name = $db_gallery->getField("register_name")->getValue();    
        $display_view_mode =  $db_gallery->getField("display_view_mode", "Text", true);
        $anagraph_type = $db_gallery->getField("ID_anagraph_type", "Number", true);
        $simple_registration = $db_gallery->getField("simple_registration", "Number", true);
        $disable_account_registration  		= $db_gallery->getField("disable_account_registration")->getValue();
    }

    $sSQL = "SELECT anagraph_fields.ID
                    , anagraph_fields.name
                    , anagraph_fields.ID_data_type
                    , anagraph_fields.ID_extended_type
                    , anagraph_fields.ID_selection
                    , anagraph_fields.data_source
                    , anagraph_fields.data_limit
                    , anagraph_fields.ID_group_backoffice
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
            $name = $db_gallery->getField("name", "Text", true);
            $arrAnagraphFields[$name] = array(
                "ID_extended_type" => $db_gallery->getField("ID_extended_type", "Number", true)
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
                , "ID_group" => $db_gallery->getField("ID_group_backoffice", "Number", true)
                
            );
            if(($db_gallery->getField("default_grid", "Number", true)
                    + $db_gallery->getField("grid_md", "Number", true)
                    + $db_gallery->getField("grid_sm", "Number", true)
                    + $db_gallery->getField("grid_xs", "Number", true)) > 0) {
                $arrAnagraphFields[$name]["grid"] = array(
                    $db_gallery->getField("default_grid", "Number", true)
                    , $db_gallery->getField("grid_md", "Number", true)
                    , $db_gallery->getField("grid_sm", "Number", true)
                    , $db_gallery->getField("grid_xs", "Number", true)
                );
            } else {
                $arrAnagraphFields[$name]["grid"] = array(12,12,12,12);
            }
            
            
        } while($db_gallery->nextRecord());
    }
    
    
    
}
if($_REQUEST["keys"]["ID"])
{
    $oRecord = ffRecord::factory($cm->oPage);
    $oRecord->id = "UserAccount";
    $oRecord->class = "UserAccount";
    $oRecord->src_table = "anagraph"; 
    $oRecord->title = ffTemplate::_get_word_by_code("user_account_title");

    $oRecord->addEvent("on_process_field", "UserAccount_on_process_field");
    if(check_function("MD_register_on_check_after"))
        $oRecord->addEvent("on_check_after", "MD_register_on_check_after");

    //$oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = false;
    $oRecord->buttons_options["delete"]["display"] = false;
    $oRecord->buttons_options["update"]["display"] = false;

    $oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("UserAccount_insert");
    $oRecord->buttons_options["delete"]["label"] = ffTemplate::_get_word_by_code("UserAccount_delete");
    $oRecord->buttons_options["update"]["label"] = ffTemplate::_get_word_by_code("UserAccount_update");
    $oRecord->buttons_options["cancel"]["label"] = ffTemplate::_get_word_by_code("UserAccount_cancel");
    
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "ID";
    $obj_page_field->base_type = "Number";
    $oRecord->addKeyField($obj_page_field);
    
    if(!$simple_registration) {
        if ($arrAnagraphFields["username"]["ID_group"] && !isset($oRecord->groups[$arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]])) 
        { 
            $oRecord->addContent(null, true, $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]); 
            if($use_tab) {
                $oRecord->addTab($arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]);
                $oRecord->setTabTitle($arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"], ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]));
            } else {
                $gridGroup[$arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]] = $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"];
            }
            $oRecord->groups[$arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]] = array(
                "title" => ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"])
                , "cols" => 1
                , "class" => $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["class"]
                , "tab" => ($use_tab ? $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"] : null)
            );
        }

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = "username";
        $obj_page_field->container_class = "profile-username";
        $obj_page_field->label = ffTemplate::_get_word_by_code("user_account_username");
        $obj_page_field->setWidthComponent($arrAnagraphFields["username"]["grid"]);
        $obj_page_field->control_type = "label";
        $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["username"]["ID_group"]]["smart_url"]);

        unset($arrAnagraphFields["username"]);
    }

    if ($arrAnagraphFields["email"]["ID_group"] && !isset($oRecord->groups[$arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]])) 
    { 
        $oRecord->addContent(null, true, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]); 
        if($use_tab) {
            $oRecord->addTab($arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);
            $oRecord->setTabTitle($arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"], ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]));
        } else {
            $gridGroup[$arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]] = $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"];
        }
        $oRecord->groups[$arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]] = array(
            "title" => ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"])
            , "cols" => 1
            , "class" => $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["class"]
            , "tab" => ($use_tab ? $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"] : null)
        );
    }
    
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "email";
    $obj_page_field->container_class = "profile-email";
    $obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_email_tip");   
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->addValidator("email");
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $obj_page_field->control_type = "label";
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);
    
    unset($arrAnagraphFields["email"]);
    
    if(is_array($arrAnagraphFields) && count($arrAnagraphFields)) {
        foreach($arrAnagraphFields AS $field_name => $field_value) 
        {
            if ($field_value["ID_group"] && !isset($oRecord->groups[$arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]])) 
            { 
                $oRecord->addContent(null, true, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]); 
                if($use_tab) {
                    $oRecord->addTab($arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    $oRecord->setTabTitle($arrAnagraphGroup[$field_value["ID_group"]]["smart_url"], ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]));
                } else {
                    $gridGroup[$arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]] = $db_gallery->toSql($arrAnagraphGroup[$field_value["ID_group"]]["smart_url"], "Text");
                }
                $oRecord->groups[$arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]] = array(
                    "title" => ffTemplate::_get_word_by_code("register_" . $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"])
                    , "cols" => 1
                    , "class" => $arrAnagraphGroup[$field_value["ID_group"]]["class"]
                    , "tab" => ($use_tab ? $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"] : null)
                );
            }
            switch ($field_name) 
            {
                case "avatar":
                    if($enable_general_data) 
                    {
                        if(ENABLE_AVATAR_SYSTEM && $show_avatar) 
                        {                                          
                            $obj_page_field = ffField::factory($cm->oPage);
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
                            $obj_page_field->control_type = "label";
                            $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]); 
                        }
                    }
                    break;
                case "name" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "name";
                    $obj_page_field->container_class = $field_class;

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
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "surname" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "surname";
                    $obj_page_field->container_class = $field_class;

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
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "tel" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "tel";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_tel");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "tel") . "_tip"); 
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "reference" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billreference";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_reference");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billreference") . "_tip");
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "cf":
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billcf";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_cf");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcf") . "_tip");   
                    }
                    $obj_page_field->addValidator("cf");
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "piva" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billpiva";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("bill_piva");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billpiva") . "_tip");   
                    }
                    $obj_page_field->addValidator("piva");
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]); 
                    break;
                case "address" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billaddress";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_address");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billaddress") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "cap" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billcap";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_cap");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billcap") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "town":
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billtown";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_town");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billtown") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "province" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "billprovince";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_bill_province");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "billprovince") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "state":
                    if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
                        $oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
                    } else {
                        $obj_page_field = ffField::factory($cm->oPage);
                        $obj_page_field->id = "billstate";
                        $obj_page_field->container_class = $field_class;

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
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                        $obj_page_field->setWidthComponent($field_value["grid"]);
                        $obj_page_field->control_type = "label";
                        $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    }
                    break;
                case "shippingreference" : 
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = "billtoshipping";
                    $oField->label = ffTemplate::_get_word_by_code("bill_to_shipping");
                    $oField->base_type = "Number";
                    $oField->extended_type = "Boolean";
                    $oField->control_type = "checkbox";
                    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
                    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
                    $oField->store_in_db = false;
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($oField, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);

                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "shippingreference";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_reference");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingreference") . "_tip");
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "shippingaddress" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "shippingaddress";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_address");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingaddress") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "shippingcap" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "shippingcap";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_cap");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingcap") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "shippingtown" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "shippingtown";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_town");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingtown") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "shippingprovince" :
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "shippingprovince";
                    $obj_page_field->container_class = $field_class;

                    $obj_page_field->setWidthLabel($field_value["framework_css"]["label"]);
                    if($field_value["placeholder"])
                        $obj_page_field->placeholder = $field_value["placeholder"];
                    else
                        $obj_page_field->placeholder = !$field_value["display_label"];

                    $obj_page_field->label = ffTemplate::_get_word_by_code("register_shipping_province");
                    if($enable_default_tip) {
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . "shippingprovince") . "_tip");   
                    }
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                case "shippingstate" :
                    if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
                        $oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
                    } else {
                        $obj_page_field = ffField::factory($cm->oPage);
                        $obj_page_field->id = "shippingstate";
                        $obj_page_field->container_class = $field_class;

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
                        $obj_page_field->setWidthComponent($field_value["grid"]);
                        $obj_page_field->control_type = "label";
                        $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    }
                    break;
                case "degree":
                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->id = "degree";
                    $obj_page_field->container_class = $field_class;

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
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);
                    break;
                default:
                    $field_id = $field_value["ID"];

                    $obj_page_field = ffField::factory($cm->oPage);
                    $obj_page_field->store_in_db = false;
                    $obj_page_field->id = $field_id;
                    $obj_page_field->user_vars["group"]["field"] = $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"];
                    $obj_page_field->user_vars["name"] = ffCommon_url_rewrite($field_value["name"]); 
                    $obj_page_field->user_vars["enable_in_mail"] = $field_value["enable_in_mail"];
                    $obj_page_field->user_vars["unic_value"] = $field_value["unic_value"]; 
                    $obj_page_field->data_type = "";

                    if(check_function("get_field_by_extension"))
                        $js .= get_field_by_extension($obj_page_field, $field_value, "register");
                    if(isset($_GET[$field_value["name"]]) && strlen($_GET[$field_value["name"]])) {
                        $obj_page_field->default_value = new ffData($_REQUEST[$field_value["name"]], $field_value["ff_extended_type"]);
                    } 
                    $obj_page_field->setWidthComponent($field_value["grid"]);
                    if($enable_default_tip)
                        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $field_value["name"]) . "_tip");   
                    $obj_page_field->control_type = "label";
                    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);

                    break;
            }
        }

    }

    $cm->oPage->addContent($oRecord);  
}
if(0) {
if($display_account) {
	$db_gallery->query("SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
	                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_general_data AS enable_general_data
	                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_bill_data AS enable_bill_data
	                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_ecommerce_data AS enable_ecommerce_data
	                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_setting_data AS enable_setting_data
	                            , " . CM_TABLE_PREFIX . "mod_security_users.public AS public
	                            , module_register.name AS register_name
                                , module_register.ID AS ID_register
                                , module_register.ID_anagraph_type AS ID_anagraph_type
	                        FROM 
	                            " . CM_TABLE_PREFIX . "mod_security_users
	                            LEFT JOIN module_register ON module_register.ID = " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register
	                        WHERE 
	                            " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_gallery->toSql($actual_uid, "Number"));
	if($db_gallery->nextRecord()) {
		if($db_gallery->getField("public", "Number", true) || $allow_edit) {
		    $_REQUEST["keys"]["register-ID"] = $db_gallery->getField("ID")->getValue();
		    $ID_register = $db_gallery->getField("ID_register", "Number", true);
		    $enable_ecommerce_data = $db_gallery->getField("enable_ecommerce_data", "Number", true);
		    $enable_bill_data = $db_gallery->getField("enable_bill_data", "Number", true);
		    $enable_general_data = $db_gallery->getField("enable_general_data", "Number", true);
		    $enable_setting_data = $db_gallery->getField("enable_setting_data", "Number", true);
	        $anagraph_type = $db_gallery->getField("ID_anagraph_type")->getValue();
		    $register_name = $db_gallery->getField("register_name")->getValue();
			$avatar_model = "vertical";
			$avatar_type = "advanced";
			
			$bill_type = "";

			if($anagraph_type > 0) {
				$sSQL = "SELECT anagraph_type.*  
    					FROM anagraph_type 
    					WHERE anagraph_type.ID = " . $db_gallery->toSql($anagraph_type, "Number");
				$db_gallery->query($sSQL);
				if($db_gallery->nextRecord()) {
					$enable_bill_data 		= $db_gallery->getField("show_bill_group", "Number", true);
					$enable_ecommerce_data	= $db_gallery->getField("show_shipping_group", "Number", true);
					$enable_general_data	= $db_gallery->getField("show_general_group", "Number", true);
					$enable_setting_data 	= $db_gallery->getField("show_setting_group", "Number", true);
					
					$bill_type 			= $db_gallery->getField("bill_type", "Text", true);
					
					$show_avatar 		= $db_gallery->getField("show_avatar", "Number", true);
					$show_avatar_group	= $db_gallery->getField("show_avatar_group", "Number", true);
					$avatar_type 		= $db_gallery->getField("avatar_type", "Number", true);
					$avatar_model 		= $db_gallery->getField("avatar_model", "Number", true);

					$show_custom_group 	= $db_gallery->getField("show_custom_group", "Number", true);
					$bill_required 		= $db_gallery->getField("bill_required", "Number", true);
					$show_categories	= $db_gallery->getField("show_categories", "Number", true);
					
					$show_gmap 			= $db_gallery->getField("show_gmap", "Number", true);
					$show_user 			= $db_gallery->getField("show_user", "Number", true);
					$show_user_group	= $db_gallery->getField("show_user_group", "Number", true);
					$force_user_edit	= $db_gallery->getField("force_user_edit", "Number", true);
					$show_vcard			= $db_gallery->getField("show_vcard", "Number", true);
					$show_qrcode		= $db_gallery->getField("show_qrcode", "Number", true);
					$show_report_group	= $db_gallery->getField("show_report_group", "Number", true);
					$use_tab 			= $db_gallery->getField("use_tab", "Number", true);
					$force_custom_email	= $db_gallery->getField("force_custom_email", "Number", true);
					$force_user_edit	= $db_gallery->getField("force_user_edit", "Number", true);
				}	
			} 
		    
		    
	        
		    $obj_page_field = ffField::factory($cm->oPage);
		    $obj_page_field->id = "register-ID";
		    $obj_page_field->base_type = "Number";
		    $obj_page_field->data_source = "ID";
		    $oRecord->addKeyField($obj_page_field);

		    if(ENABLE_AVATAR_SYSTEM && $show_avatar) {  
    			if($show_avatar_group) { 
					if($use_tab) {
						$oRecord->addTab("avatar");
						$oRecord->setTabTitle("avatar", ffTemplate::_get_word_by_code("profile_avatar"));
					}

			        $oRecord->addContent(null, true, "avatar"); 
			        $oRecord->groups["avatar"] = array(
			                                                 "title" => ffTemplate::_get_word_by_code("profile_avatar")
			                                                 , "cols" => 1
			                                                 , "tab" => ($use_tab ? "avatar" : null)
			                                              );
				}
			}

			if($show_custom_group) {  
				if($use_tab) {
					$oRecord->addTab("additfields");
					$oRecord->setTabTitle("additfields", ffTemplate::_get_word_by_code("profile_additfields"));
				}

			    $oRecord->addContent(null, true, "additfields"); 
			    $oRecord->groups["additfields"] = array(
			                                             "title" => ffTemplate::_get_word_by_code("profile_additfields")
			                                             , "cols" => 1
			                                             , "tab" => ($use_tab ? "additfields" : null)
			                                          );
			} else {
				if($enable_general_data 
					|| ENABLE_AVATAR_SYSTEM 
					|| USE_PUBLIC_ACCOUNT
				) {
					$oRecord->addContent(null, true, "accountinfo");
				    $oRecord->groups["accountinfo"] = array(
				                                             "title" => ffTemplate::_get_word_by_code("profile_accountinfo")
				                                             , "cols" => 1
				                                          );
				}
			}	

	        if(ENABLE_AVATAR_SYSTEM && $show_avatar) {
	            $obj_page_field = ffField::factory($cm->oPage);
	            $obj_page_field->id = "avatar";
		        $obj_page_field->container_class = "profile-avatar";
			    if($avatar_model == "default") {
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_avatar");
				}
	            $obj_page_field->base_type = "Text";
	            $obj_page_field->extended_type = "File";
	            $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $actual_uid;
	            $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
	            $obj_page_field->file_max_size = MAX_UPLOAD;
	            $obj_page_field->file_show_filename = false; 
	            $obj_page_field->file_full_path = true;
	            $obj_page_field->file_check_exist = true;
	            $obj_page_field->file_normalize = true;
	            $obj_page_field->file_show_preview = true;

		        $obj_page_field->uploadify_model = $avatar_model;
		        $obj_page_field->uploadify_model_thumb = ($avatar_model == "default" ? "profileavatar" : "avatar" . $avatar_model);

	            $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
	            $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $obj_page_field->uploadify_model_thumb . "/[_FILENAME_]";
	//            $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
	//            $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $obj_page_field->uploadify_model_thumb . "/[_FILENAME_]";

	            $obj_page_field->control_type = "file";
	            $obj_page_field->file_show_delete = false;
	            $obj_page_field->file_writable = false;
	            $obj_page_field->encode_entities = false;
	            $obj_page_field->control_type = "picture_no_link";
	            $oRecord->addContent($obj_page_field, ($show_avatar_group ? "avatar" : ($show_custom_group ? "additfields" : "accountinfo"))); 
	        }

			if($enable_general_data) {
				if(!$enable_bill_data) {
					$obj_page_field = ffField::factory($cm->oPage);
					$obj_page_field->id = "name";
					$obj_page_field->container_class = "profile_name";
					$obj_page_field->label = ffTemplate::_get_word_by_code("profile_name");
					$obj_page_field->control_type = "label";
					$oRecord->addContent($obj_page_field, ($show_custom_group ? "additfields" : "accountinfo"));

					$obj_page_field = ffField::factory($cm->oPage);
					$obj_page_field->id = "surname";
					$obj_page_field->container_class = "profile_surname";
					$obj_page_field->label = ffTemplate::_get_word_by_code("profile_surname");
					$obj_page_field->control_type = "label";
					$oRecord->addContent($obj_page_field, ($show_custom_group ? "additfields" : "accountinfo"));
				}

			    $obj_page_field = ffField::factory($cm->oPage);
			    $obj_page_field->id = "real_email";
			    $obj_page_field->data_source = "email";
			    $obj_page_field->container_class = "profile_email";
			    $obj_page_field->label = ffTemplate::_get_word_by_code("profile_email");
			    $obj_page_field->store_in_db = false;
			    $obj_page_field->control_type = "label";
			    $oRecord->addContent($obj_page_field, ($show_custom_group ? "additfields" : "accountinfo"));
			} else {
                if(!$show_custom_group) {
                    $oRecord->addContent(null, true, "account");
                    $oRecord->groups["account"] = array(
                                                             "title" => ffTemplate::_get_word_by_code("profile_account")
                                                             , "cols" => 1
                                                          );
                }
                $obj_page_field = ffField::factory($cm->oPage);
                $obj_page_field->id = "username";
                $obj_page_field->container_class = "profile_username";
                $obj_page_field->label = ffTemplate::_get_word_by_code("profile_username");
                $obj_page_field->control_type = "label";
                $oRecord->addContent($obj_page_field, ($show_custom_group ? "additfields" : "account"));

			    $obj_page_field = ffField::factory($cm->oPage);
			    $obj_page_field->id = "real_email";
			    $obj_page_field->data_source = "email";
			    $obj_page_field->container_class = "profile_email";
			    $obj_page_field->label = ffTemplate::_get_word_by_code("profile_email");
			    $obj_page_field->store_in_db = false;
			    $obj_page_field->control_type = "label";
			    $oRecord->addContent($obj_page_field, ($show_custom_group ? "additfields" : "account"));
			}

 			if($enable_bill_data || (AREA_SHOW_ECOMMERCE && ENABLE_CART_BILLDATA)) {
				//bill data
			    $oRecord->addContent(null, true, "bill"); 
			    $oRecord->groups["bill"] = array(
			                                             "title" => ffTemplate::_get_word_by_code("profile_bill")
			                                             , "cols" => 1
			                                             , "tab" => "bill"
			                                          );
			}

            $sSQL = "SELECT anagraph_fields.* 
                    FROM anagraph_fields 
                    WHERE anagraph_fields.ID_type = " . $db_gallery->toSql($anagraph_type, "Number") . "
                        AND NOT(anagraph_fields.hide > 0)";
            $db_gallery->query($sSQL);
            if($db_gallery->nextRecord()) {
                $count_anagraph_field = $db_gallery->numRows();
            }
            if($anagraph_type > 0 && $count_anagraph_field > 0) {
                $db_gallery->query("SELECT anagraph_fields.*
                                        , extended_type.name AS extended_type
                                        , check_control.ff_name AS check_control
                                        , anagraph_type_group.name AS `group_field`
                                        , anagraph_fields_selection.ID_vgallery_fields AS ID_field
                                        , ( SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.value 
                                            FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
                                            WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db_gallery->toSql($actual_uid, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field = anagraph_fields.name 
                                        ) AS value
                                    FROM 
                                        anagraph_fields
                                        LEFT JOIN extended_type ON extended_type.ID = anagraph_fields.ID_extended_type
                                        LEFT JOIN check_control ON check_control.ID = anagraph_fields.ID_check_control
                                        LEFT JOIN anagraph_type_group ON anagraph_type_group.ID = anagraph_fields.ID_group_backoffice
                                        LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields.ID_selection
                                    WHERE 1
                                        AND anagraph_fields.ID_type = " . $db_gallery->toSql($anagraph_type, "Number") . "
                                        AND NOT(anagraph_fields.hide > 0)
                                    ORDER BY anagraph_fields.`order_detail`, anagraph_fields.name
                                    ");
            } else {
	            $db_gallery->query("SELECT module_register_fields.*
	                                    , extended_type.name AS extended_type
	                                    , check_control.ff_name AS check_control
	                                    , module_form_fields_group.name AS `group_field`
	                                    , module_form_fields_selection.ID_vgallery_fields AS ID_field
	                                    , ( SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.value 
	                                        FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
	                                        WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db_gallery->toSql($actual_uid, "Number") . "
	                                            AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field = module_register_fields.name 
	                                    ) AS value
	                                FROM 
	                                    module_register_fields
	                                    LEFT JOIN extended_type ON extended_type.ID = module_register_fields.ID_extended_type
	                                    LEFT JOIN check_control ON check_control.ID = module_register_fields.ID_check_control
	                                    LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_register_fields.ID_form_fields_group
	                                    LEFT JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_register_fields.ID_selection
	                                WHERE 
	                                    module_register_fields.ID_module = " . $db_gallery->toSql($ID_register, "Number") . "
	                                    AND NOT(module_register_fields.hide > 0)
	                                ORDER BY module_register_fields.`order`, module_register_fields.name
	                                ");
            }           
	        if($db_gallery->nextRecord()) {
	        	$db_selection = ffDB_Sql::factory();
	            do {
		            $field_name = $db_gallery->getField("name")->getValue();
		            $field_id = $db_gallery->getField("ID")->getValue();
				    $group_field = $db_gallery->getField("group_field")->getValue() 
						                ? preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("group_field")->getValue()) 
						                : ($show_custom_group ? "additfields" : "accountinfo");

				    if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
					    $oRecord->addContent(null, true, $group_field); 
						if($use_tab) {
							$oRecord->addTab($group_field);
							$oRecord->setTabTitle($group_field, ffTemplate::_get_word_by_code("profile_" . $group_field));
						}
					    $oRecord->groups[$group_field] = array(
					                                             "title" => ffTemplate::_get_word_by_code("profile_" . $group_field)
					                                             , "cols" => 1
					                                             , "tab" => ($use_tab ? $group_field : null)
					                                          );
				    }

	                $obj_page_field = ffField::factory($cm->oPage);
	                $obj_page_field->id = $field_id;
	                $obj_page_field->container_class = "profile_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name);
	                $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
	                $obj_page_field->user_vars["group_field"] = $group_field;
	                $obj_page_field->user_vars["name"] = $field_name;
	                $obj_page_field->user_vars["enable_in_mail"] = $db_gallery->getField("enable_in_mail", "Number", true);
	                $obj_page_field->user_vars["unic_value"] = $db_gallery->getField("unic_value")->getValue(); 
	                
	                $obj_page_field->data_type = "";
	                $obj_page_field->store_in_db = false;  
	                
	                $writable = false;

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
	                                                        module_form_fields_selection_value.name AS nameID
	                                                        , module_form_fields_selection_value.name AS name
	                                                        , module_form_fields_selection_value.`order` AS `order`
	                                                       FROM module_form_fields_selection_value 
	                                                            INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
	                                                       WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
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
	                                                        module_form_fields_selection_value.name AS nameID
	                                                        , module_form_fields_selection_value.name AS name
	                                                        , module_form_fields_selection_value.`order` AS `order`
	                                                       FROM module_form_fields_selection_value 
	                                                            INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
	                                                       WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
	                                               )
	                                            ) AS tbl_src
	                                            ORDER BY tbl_src.`order`, tbl_src.name");
	                        if($db_selection->nextRecord()) {
	                            do {
	                                $selection_value[] = array(new ffData($db_selection->getField("name")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
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
                              $obj_page_field->control_type = "label";
	                          /*$obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("form_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name) . "_text_" . $oRecord->user_vars["MD_chk"]["params"][0]), "Text");
	                          $obj_page_field->properties["readonly"] = "readonly";*/
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

	                        $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $actual_uid;
	                        $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
	                        $obj_page_field->file_max_size = MAX_UPLOAD;

	                        $obj_page_field->file_show_filename = true; 
	                        $obj_page_field->file_full_path = false;
	                        $obj_page_field->file_check_exist = false;
	                        $obj_page_field->file_normalize = true;
	                         
	                        $obj_page_field->file_show_preview = true;
	                        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
	                        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";

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

	                        $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $actual_uid;
	                        $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
	                        $obj_page_field->file_max_size = MAX_UPLOAD;

	                        $obj_page_field->file_show_filename = true; 
	                        $obj_page_field->file_full_path = false;
	                        $obj_page_field->file_check_exist = false;
	                        $obj_page_field->file_normalize = true;
	                         
	                        $obj_page_field->file_show_preview = true;
	                        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $actual_uid . "/[_FILENAME_]";
	                        
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

	                        $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $actual_uid;
	                        $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
	                        $obj_page_field->file_max_size = MAX_UPLOAD;

	                        $obj_page_field->file_show_filename = true; 
	                        $obj_page_field->file_full_path = false;
	                        $obj_page_field->file_check_exist = false;
	                        $obj_page_field->file_normalize = true;
	                         
	                        $obj_page_field->file_show_preview = true;
	                        $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $actual_uid . "/[_FILENAME_]";
	                        $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
	                        $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";
	                        
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
					$obj_page_field->encode_entities = false;

					if(check_function("transmute_inlink"))
						$obj_page_field->default_value = new ffData(transmute_inlink($db_gallery->getField("value", $type_value)->getValue()), $type_value);
	                
	                $oRecord->addContent($obj_page_field, $group_field);
	            } while($db_gallery->nextRecord());
	        }

 			if($enable_bill_data || (AREA_SHOW_ECOMMERCE && ENABLE_CART_BILLDATA)) {
				//bill data
			    if(!strlen($bill_type) || strpos($bill_type, "namesurname") !== false) {
					$obj_page_field = ffField::factory($cm->oPage);
					$obj_page_field->id = "name";
					$obj_page_field->container_class = "name";
					$obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_name");
					$obj_page_field->control_type = "label";
					$oRecord->addContent($obj_page_field, "bill");

					$obj_page_field = ffField::factory($cm->oPage);
					$obj_page_field->id = "surname";
					$obj_page_field->container_class = "surname";
					$obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_surname");
					$obj_page_field->control_type = "label";
					$oRecord->addContent($obj_page_field, "bill");
				}
				
				if(!strlen($bill_type) || strpos($bill_type, "reference") !== false) {
		            $obj_page_field = ffField::factory($cm->oPage);
		            $obj_page_field->id = "billreference";
		            $obj_page_field->container_class = "profile_bill_reference";
		            $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_reference");
		            $obj_page_field->extended_type = "Text";
		            $obj_page_field->control_type = "label";
		            $oRecord->addContent($obj_page_field, "bill");
				}

				if(!strlen($bill_type) || strpos($bill_type, "cf") !== false) {				
		            $obj_page_field = ffField::factory($cm->oPage); 
				    $obj_page_field->id = "billcf";
			        $obj_page_field->container_class = "profile_bill_piva";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_cf");
			        $obj_page_field->control_type = "label";
				    $oRecord->addContent($obj_page_field, "bill");
				}

				if(!strlen($bill_type) || strpos($bill_type, "piva") !== false) {				
				    $obj_page_field = ffField::factory($cm->oPage);
				    $obj_page_field->id = "billpiva";
			        $obj_page_field->container_class = "profile_bill_piva";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_piva");
			        $obj_page_field->control_type = "label";
				    $oRecord->addContent($obj_page_field, "bill");
				}

				if(!strlen($bill_type) || strpos($bill_type, "address") !== false) {				
		            $obj_page_field = ffField::factory($cm->oPage);
			        $obj_page_field->id = "billaddress";
			        $obj_page_field->container_class = "profile_bill_address";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_address");
			        $obj_page_field->extended_type = "Text";
			        $obj_page_field->control_type = "label";
			        $oRecord->addContent($obj_page_field, "bill");

			        $obj_page_field = ffField::factory($cm->oPage);
			        $obj_page_field->id = "billcap";
			        $obj_page_field->container_class = "profile_bill_cap";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_cap");
			        $obj_page_field->control_type = "label";
			        $oRecord->addContent($obj_page_field, "bill");

			        $obj_page_field = ffField::factory($cm->oPage);
			        $obj_page_field->id = "billtown";
			        $obj_page_field->container_class = "profile_bill_town";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_town");
			        $obj_page_field->control_type = "label";
			        $oRecord->addContent($obj_page_field, "bill");

			        $obj_page_field = ffField::factory($cm->oPage);
			        $obj_page_field->id = "billprovince";
			        $obj_page_field->container_class = "profile_bill_province";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_province");
			        $obj_page_field->control_type = "label";
			        $oRecord->addContent($obj_page_field, "bill");

			        if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
        				$oRecord->additional_fields["billstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
					} else {
				        $obj_page_field = ffField::factory($cm->oPage);
				        $obj_page_field->id = "billstate";
				        $obj_page_field->container_class = "profile_bill_state";
				        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_bill_state");
				        $obj_page_field->base_type = "Number";
				        //$obj_page_field->widget = "activecomboex";
				        $obj_page_field->extended_type = "Selection";
				        $obj_page_field->control_type = "label";
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
				        $obj_page_field->multi_limit_select = TRUE;
				        $obj_page_field->multi_select_one_label = "";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
				        $oRecord->addContent($obj_page_field, "bill");
					}
				}
			}
			        
		    if($enable_ecommerce_data) {
    			$oRecord->addContent(null, true, "ecommerce");
		        $oRecord->groups["ecommerce"] = array(
		                                                 "title" => ffTemplate::_get_word_by_code("profile_shipping")
		                                                 , "cols" => 1
		                                              );

	            $obj_page_field = ffField::factory($cm->oPage);
	            $obj_page_field->id = "shippingreference";
	            $obj_page_field->container_class = "profile_shipping_reference";
	            $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_reference");
	            $obj_page_field->extended_type = "Text";
	            $obj_page_field->control_type = "label";
	            $oRecord->addContent($obj_page_field, "ecommerce");
            		        
	            $obj_page_field = ffField::factory($cm->oPage);
		        $obj_page_field->id = "shippingaddress";
		        $obj_page_field->container_class = "profile_shipping_address";
		        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_address");
		        $obj_page_field->extended_type = "Text";
		        $obj_page_field->control_type = "label";
		        $oRecord->addContent($obj_page_field, "ecommerce");

		        $obj_page_field = ffField::factory($cm->oPage);
		        $obj_page_field->id = "shippingcap";
		        $obj_page_field->container_class = "profile_shipping_cap";
		        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_cap");
		        $obj_page_field->control_type = "label";
		        $oRecord->addContent($obj_page_field, "ecommerce");

		        $obj_page_field = ffField::factory($cm->oPage);
		        $obj_page_field->id = "shippingtown";
		        $obj_page_field->container_class = "profile_shipping_town";
		        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_town");
		        $obj_page_field->control_type = "label";
		        $oRecord->addContent($obj_page_field, "ecommerce");

		        $obj_page_field = ffField::factory($cm->oPage);
		        $obj_page_field->id = "shippingprovince";
		        $obj_page_field->container_class = "profile_shipping_province";
		        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_province");
		        $obj_page_field->control_type = "label";
		        $oRecord->addContent($obj_page_field, "ecommerce");

		        if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
        			$oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
				} else {
			        $obj_page_field = ffField::factory($cm->oPage);
			        $obj_page_field->id = "shippingstate";
			        $obj_page_field->container_class = "profile_shipping_state";
			        $obj_page_field->label = ffTemplate::_get_word_by_code("profile_shipping_state");
			        $obj_page_field->base_type = "Number";
			        //$obj_page_field->widget = "activecomboex";
			        $obj_page_field->extended_type = "Selection";
			        $obj_page_field->control_type = "label";
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
			        $obj_page_field->multi_limit_select = TRUE;
			        $obj_page_field->multi_select_one_label = "";
                    $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
			        $oRecord->addContent($obj_page_field, "ecommerce");
				}
		    }
			
			if($allow_edit) {
			    $oButton = ffButton::factory($cm->oPage);
			    $oButton->id = "edit";
			    $oButton->action_type = "gotourl";
			    $oButton->url = FF_SITE_PATH . USER_RESTRICTED_PATH . "/account" . "/" . $UserID . "?ret_url=[ENCODED_THIS_URL]";
			    $oButton->aspect = "link";
                            $oButton->label = ffTemplate::_get_word_by_code("edit");
			    $oRecord->addActionButton($oButton);
			}
		   /* $oButton = ffButton::factory($cm->oPage);
		    $oButton->id = "back";
		    $oButton->action_type = "gotourl";
		    $oButton->url = "[RET_URL]";
		    $oButton->label = ffTemplate::_get_word_by_code("Back");
		    $oRecord->addActionButton($oButton, TRUE);   */
			$cm->doEvent("vg_on_user_profile_process", array(&$cm, &$oRecord, $_REQUEST["keys"]["register-ID"]));
		    $cm->oPage->addContent($oRecord);  
		    $cm->doEvent("vg_on_user_profile_processed", array(&$cm, &$oRecord, $_REQUEST["keys"]["register-ID"]));
			
		   //additionaldata 
		    $sSQL = "SELECT module_form.ID AS ID_form
    				, module_form.name AS form_name
    				, users_rel_module_form.ID_form_node AS ID_form_node
    				, users_rel_module_form.public AS public
		        FROM " . CM_TABLE_PREFIX . "mod_security_users 
		                LEFT JOIN users_rel_module_form ON users_rel_module_form.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
		                LEFT JOIN module_form ON module_form.ID = users_rel_module_form.ID_module
		        WHERE
					" . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_gallery->toSql($actual_uid, "Number") . "
		            AND ISNULL(module_form.name) = 0 
		        ORDER BY users_rel_module_form.request DESC, users_rel_module_form.`order`, module_form.ID";
		    $db_gallery->query($sSQL);
		    if($db_gallery->nextRecord()) {
		        $db_selection = ffDB_Sql::factory();
		        $db_form = ffDB_Sql::factory();
		        do {
	        		if(!($allow_edit || $db_gallery->getField("public", "Number", true)))
	        			continue;
	        		
		            $ID_form = $db_gallery->getField("ID_form")->getValue();
		            $form_name = $db_gallery->getField("form_name")->getValue();
		            $ID_form_node = $db_gallery->getField("ID_form_node")->getValue();

		            $db_form->query("SELECT module_form_fields.*
            								, extended_type.name AS extended_type
            								, check_control.ff_name AS check_control
            								, module_form_rel_nodes_fields.value AS data_value
            								, module_form_fields_group.name AS `group_field`
		                                    FROM 
		                                        module_form_fields
		                                        LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
		                                        LEFT JOIN check_control ON check_control.ID = module_form_fields.ID_check_control
		                                        LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
		                                        LEFT JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_form_fields = module_form_fields.ID AND module_form_rel_nodes_fields.ID_form_nodes = " . $db_form->toSql($ID_form_node, "Number") . "
		                                    WHERE 
		                                        module_form_fields.ID_module = " . $db_form->toSql(new ffData($ID_form, "Number")) . "
		                                    ORDER BY module_form_fields.`order`, module_form_fields.name
		                                    ");
		            if($db_form->nextRecord()) {
		                $oRecord = ffRecord::factory($cm->oPage);
		                
		                $oRecord->id = "UserData-" . $form_name;
		                $oRecord->class = "report-" . $form_name;
		                $oRecord->src_table = ""; 
		                $oRecord->title =  ffTemplate::_get_word_by_code("profile_" . $form_name . "_title");
		                //$oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"]; 
		                $oRecord->skip_action = true;
		                $oRecord->buttons_options["cancel"]["display"] = false;
		                $oRecord->buttons_options["insert"]["display"] = false;
		                $oRecord->buttons_options["update"]["display"] = false;
		                $oRecord->buttons_options["delete"]["display"] = false;
		                $oRecord->display_required_note = false;

		                $oRecord->allow_insert = false;
		                $oRecord->allow_update = false;
		                $oRecord->allow_delete = false;

		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "report-ID";
		                $oField->base_type = "Number";
		                $oField->default_value = new ffData($ID_form, "Number");
		                $oRecord->addKeyField($oField);


		                do {
		                    $field_name = $db_form->getField("name")->getValue();
		                    $field_id = $db_gallery->getField("ID")->getValue();
				            $group_field = $db_gallery->getField("group_field")->getValue() 
						                        ? preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("group_field")->getValue()) 
						                        : ($show_custom_group ? "additfields" : null);

				            if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
					            $oRecord->addContent(null, true, $group_field); 
								if($use_tab) {
									$oRecord->addTab($group_field);
									$oRecord->setTabTitle($group_field, ffTemplate::_get_word_by_code("profile_" . $group_field));
								}
					            $oRecord->groups[$group_field] = array(
					                                                     "title" => ffTemplate::_get_word_by_code("profile_" . $group_field)
					                                                     , "cols" => 1
					                                                     , "tab" => ($use_tab ? $group_field : null)
					                                                  );
				            }
				            		                    
		                    $obj_page_field = ffField::factory($cm->oPage);
		                    $obj_page_field->id = $field_name;
		                    $obj_page_field->container_class = "profile-" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name);
		                    $obj_page_field->label = ffTemplate::_get_word_by_code("profile_" . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $field_name)));
		                    
		                    $selection_value = array();

		                    switch($db_form->getField("extended_type")->getValue())
		                    {
					            case "Selection":
					            case "Option":
					                $obj_page_field->base_type = "Text";

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
					                
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = "";

		                            $db_selection->query("SELECT 
		                                                    module_form_fields_selection_value.name
		                                                    , module_form_fields_selection.name AS father_name
		                                                   FROM module_form_fields_selection_value 
		                                                        INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
		                                                   WHERE module_form_fields_selection_value.ID_selection = " . $db_form->toSql($db_form->getField("ID_selection", "Number")) . "
		                                                   ORDER BY module_form_fields_selection_value.`order`, module_form_fields_selection_value.name
		                                                   ");
		                            if($db_selection->nextRecord()) {
		                                do {
		                                    $selection_value[] = array(new ffData($db_selection->getField("name")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("father_name")->getValue() . "_" . $db_selection->getField("name")->getValue())));
		                                } while($db_selection->nextRecord());
		                            }

		                            $obj_page_field->multi_pairs = $selection_value;
		                            $obj_page_field->encode_entities = false;
									$obj_page_field->multi_select_one = !$db_form->getField("disable_select_one", "Number", true);
									
		                            $type_value = "Text";
		                            break;
		                        case "Group":
		                            $obj_page_field->base_type = "Text";
		                            $obj_page_field->extended_type = "Selection";
		                            $obj_page_field->control_type = "input";
		                            $obj_page_field->properties["disabled"] = "disabled";
		                            $obj_page_field->widget = "checkgroup";
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = ";";

		                            $db_selection->query("SELECT 
		                                                    module_form_fields_selection_value.name
		                                                    , module_form_fields_selection.name AS father_name
		                                                   FROM module_form_fields_selection_value 
		                                                        INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
		                                                   WHERE module_form_fields_selection_value.ID_selection = " . $db_form->toSql($db_form->getField("ID_selection", "Number")) . "
		                                                   ORDER BY module_form_fields_selection_value.`order`, module_form_fields_selection_value.name
		                                                   ");
		                            if($db_selection->nextRecord()) {
		                                do {
		                                    $selection_value[] = array(new ffData($db_selection->getField("name")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("father_name")->getValue() . "_" . $db_selection->getField("name")->getValue())));
		                                } while($db_selection->nextRecord());
		                            }

		                            $obj_page_field->multi_pairs = $selection_value;
		                            $obj_page_field->encode_entities = false;

		                            $type_value = "Text";
		                            break;
		                        case "Text":
		                            $obj_page_field->base_type = "Text";
		                            $obj_page_field->extended_type = "Text";
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
		                            $obj_page_field->control_type = "label";
		                            $obj_page_field->widget = "";
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = "";
		                            
		                            $type_value = "Text";
		                            break;

		                        case "TextCK":
		                            $obj_page_field->base_type = "Text";
		                            $obj_page_field->extended_type = "Text";
		                            $obj_page_field->control_type = "label";
		                            $obj_page_field->widget = "";
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = "";
		                            
		                            $type_value = "Text";
		                            break;
		                            
		                        case "Boolean":
		                            $obj_page_field->base_type = "Number";
		                            $obj_page_field->extended_type = "Boolean";
		                            $obj_page_field->control_type = "checkbox";
		                            $obj_page_field->properties["disabled"] = "disabled";
		                            $obj_page_field->widget = "";
		                            $obj_page_field->unchecked_value = new ffData("0", "Number");
		                            $obj_page_field->checked_value = new ffData("1", "Number");
		                            $obj_page_field->grouping_separator = "";
		                            
		                            $type_value = "Number";
		                            break;

		                        case "Date":
		                        case "DateCombo":
		                            $obj_page_field->base_type = "Date";
		                            $obj_page_field->extended_type = "Date";
		                            $obj_page_field->control_type = "label";
		                            $obj_page_field->widget = "";
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = "";
		                            
		                            $type_value = "Date";
		                            break;

		                        case "Image":
		                        case "Upload":
		                        case "UploadImage":
					                $obj_page_field->base_type = "Text";
					                $obj_page_field->extended_type = "File";
	                                
	                                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $actual_uid . "/" . $form_name;
	                                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
						            $obj_page_field->file_max_size = MAX_UPLOAD;

									$obj_page_field->file_show_filename = true; 
								    $obj_page_field->file_full_path = false;
	                                $obj_page_field->file_check_exist = false;
								    $obj_page_field->file_normalize = true;
								     
								    $obj_page_field->file_show_preview = true;
						            $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $actual_uid . "/" . $form_name . "/[_FILENAME_]";
	                                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $actual_uid . "/" . $form_name . "/[_FILENAME_]";
						            $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
	                                $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";
	                        
							        $obj_page_field->control_type = "picture_no_link";

									$obj_page_field->file_show_delete = false;
									$obj_page_field->file_writable = false;

							        $obj_page_field->widget = "";

			                        $obj_page_field->unchecked_value = new ffData("");
			                        $obj_page_field->checked_value = new ffData("");
			                        $obj_page_field->grouping_separator = "";
			                        
			                        $type_value = "Text";
		                            break;

		                        case "Number":
		                            $obj_page_field->base_type = "Number";
		                            $obj_page_field->extended_type = "";
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
		                            $obj_page_field->control_type = "label";
		                            $obj_page_field->widget = "";
		                            $obj_page_field->unchecked_value = new ffData("");
		                            $obj_page_field->checked_value = new ffData("");
		                            $obj_page_field->grouping_separator = "";
		                            
		                            $type_value = "Text";
		                    }

		                    $obj_page_field->default_value = $db_form->getField("data_value", $type_value);
		                    $oRecord->addContent($obj_page_field, $group_field);
		                } while($db_form->nextRecord());

		                if($allow_edit) {
			                $oButton = ffButton::factory($cm->oPage);
			                $oButton->id = "edit";
			                $oButton->action_type = "gotourl";
			                $oButton->url = FF_SITE_PATH . USER_RESTRICTED_PATH . "/additionaldata" . "/" . $UserID  . "/" . $form_name . "?ret_url=[ENCODED_THIS_URL]";
			                $oButton->aspect = "link";
                                        $oButton->label = ffTemplate::_get_word_by_code("edit");
			                $oRecord->addActionButton($oButton);
						}
		                /*
		                $oButton = ffButton::factory($cm->oPage);
		                $oButton->id = "back";
		                $oButton->action_type = "gotourl";
		                $oButton->url = "[RET_URL]";
                                $oButton->aspect = "link";
		                $oButton->label = ffTemplate::_get_word_by_code("Back");
		                $oRecord->addActionButton($oButton, TRUE);
		                */
		                $cm->doEvent("vg_on_user_profile_addit_process", array(&$cm, &$oRecord, $_REQUEST["keys"]["register-ID"]));
		                $cm->oPage->addContent($oRecord);
		                $cm->doEvent("vg_on_user_profile_addit_processed", array(&$cm, &$oRecord, $_REQUEST["keys"]["register-ID"]));
		            } 
		        } while($db_gallery->nextRecord());
		    } else {
		       //$strError = ffTemplate::_get_word_by_code("user_additionaldata_not_found");
		    }    
		} else {
			$strError = ffTemplate::_get_word_by_code("user_profile_access_denied");
		}
	} else {
		$strError = ffTemplate::_get_word_by_code("user_profile_not_found");
	}

	if(strlen($strError))
		$cm->oPage->addContent($strError, null, "error");

	if(strlen($ret_url) && stripslash($ret_url) != FF_SITE_PATH) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "back";
		$oButton->action_type = "gotourl";
	//	if(strlen($ret_url) && stripslash($ret_url) != FF_SITE_PATH) {
			$oButton->url = $ret_url;
                        $oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("back");
	//	} else {
	//		$oButton->url = FF_SITE_PATH . "/";
	//		$oButton->label = ffTemplate::_get_word_by_code("back_home");
	//	}
		$oButton->parent_page = array(&$cm->oPage);

		$cm->oPage->addContent($oButton->process(), null, "back");
	}
} else {
	if(check_function("process_html_page_error")) {
    	$cm->oPage->addContent(process_html_page_error(404, false, $cm->path_info));
    }
}
}




function UserAccount_on_process_field($component, $field_key) {
	if($field_key == "avatar") {
		if(check_function("get_user_avatar")) {
			$component->form_fields["avatar"]->setValue(get_user_avatar($component->form_fields["avatar"]->getValue(), false, $component->form_fields["real_email"]->getValue(), ""));
		}
	}
}
?>
