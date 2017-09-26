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
$db = ffDB_Sql::factory();

use_cache(false);

$UserNID = get_session("UserNID");
//$user_path = $cm->real_path_info;
$UserID = get_session("UserID");

$arrAnagraphFields = array();
$framework_css = cm_getFrameworkCss();	

if(!$_REQUEST["ret_url"])
	$_REQUEST["ret_url"] = FF_SITE_PATH . "/";

$ret_url = urldecode($_REQUEST["ret_url"]);

//Controllo permessi e settaggio per la parte di modifica
if($UserID == MOD_SEC_GUEST_USER_NAME) { //in teoria da espandere con le impostazioni visible anche per le static page
    ffRedirect(FF_SITE_PATH . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
} 

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
				, " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register
            FROM " . CM_TABLE_PREFIX . "mod_security_users
            WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($UserNID, "Number");
$db->query($sSQL);
if($db->nextRecord()) {
    $ID_type = $db->getField("ID_module_register", "Number", true);
    $_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
}




if($ID_type) {
    $sSQL = "SELECT ID, username, email
            FROM anagraph
            WHERE ID_type = " . $db->toSql($ID_type, "Number") . "
                AND uid = " . $db->toSql($UserNID, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
        $username = $db->getField("username", "Text", true);
        $email = $db->getField("email", "Text", true);
    }
    $sSQL = "SELECT ID
                    , name
                    , default_grid
                    , grid_md
                    , grid_sm
                    , grid_xs
                FROM anagraph_type_group";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $arrAnagraphGroup[$db->getField("ID", "Number", true)] = array(
                "name" => $db->getField("name", "Text", true)
                , "smart_url" => ffcommon_url_rewrite($db->getField("name", "Text", true))
            );
            if(is_array($framework_css))
            {
                $arrAnagraphGroup[$db->getField("ID", "Number", true)]["class"] = cm_getClassByFrameworkCss(array(
                        (int) $db->getField("grid_xs", "Number", true)
                        , (int) $db->getField("grid_sm", "Number", true)
                        , (int) $db->getField("grid_md", "Number", true)
                        , (int) $db->getField("default_grid", "Number", true)
                ), "col"); 
            }
        } while ($db->nextRecord());
    }
                
    $sSQL = "SELECT module_register.name AS register_name
                    , module_register.ID AS ID_register
                    , module_register.display_view_mode AS display_view_mode
                    , module_register.ID_anagraph_type AS ID_anagraph_type
                    , module_register.simple_registration
                    , module_register.disable_account_registration
                FROM module_register
                WHERE module_register.ID = " . $db->toSql($ID_type, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $ID_register = $db->getField("ID_register", "Number", true);
        $register_name = $db->getField("register_name")->getValue();    
        $display_view_mode =  $db->getField("display_view_mode", "Text", true);
        $anagraph_type = $db->getField("ID_anagraph_type", "Number", true);
        $simple_registration = $db->getField("simple_registration", "Number", true);
        $disable_account_registration  		= $db->getField("disable_account_registration")->getValue();
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
    $db->query($sSQL);
    if($db->nextRecord()) { 
        do {
            $name = $db->getField("name", "Text", true);
            $arrAnagraphFields[$name] = array(
                "ID_extended_type" => $db->getField("ID_extended_type", "Number", true)
                , "ID_selection" => $db->getField("ID_selection", "Number", true)
                , "selectionSource" => $db->getField("selectionSource", "Text", true)
                , "selection_data_source" => $db->getField("selection_data_source", "Text", true)
                , "selection_data_limit" => $db->getField("selection_data_limit", "Text", true)
                , "field" => $db->getField("field", "Text", true)
                , "ID_fields_child" => $db->getField("ID_fields_child", "Number", true)
                , "ID_fields_father" => $db->getField("ID_fields_father", "Number", true)
                , "ID_data_type" => $db->getField("ID_data_type", "Number", true)
                , "data_source" => $db->getField("data_source", "Text", true)
                , "data_limit" => $db->getField("data_limit", "Text", true)
                , "ID_group" => $db->getField("ID_group_backoffice", "Number", true)
                
            );
            if(($db->getField("default_grid", "Number", true)
                    + $db->getField("grid_md", "Number", true)
                    + $db->getField("grid_sm", "Number", true)
                    + $db->getField("grid_xs", "Number", true)) > 0) {
                $arrAnagraphFields[$name]["grid"] = array(
                    $db->getField("default_grid", "Number", true)
                    , $db->getField("grid_md", "Number", true)
                    , $db->getField("grid_sm", "Number", true)
                    , $db->getField("grid_xs", "Number", true)
                );
            } else {
                $arrAnagraphFields[$name]["grid"] = array(12,12,12,12);
            }
            
            
        } while($db->nextRecord());
    }
    
}

if($_REQUEST["keys"]["ID"])
{
    if(is_array($framework_css))
    {
        $text_class = cm_getClassByFrameworkCss(array(12), "col");
    }
    $cm->oPage->addContent('<div class="' . $text_class . '"> ' . ffTemplate::_get_word_by_code("user_account_welcome") . 
            (isset($arrAnagraphFields["username"]) ? $username : $email) 
            . ffTemplate::_get_word_by_code("user_account_welcome_explain") . ' </div>'
    );  
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "UserAccount";
$oRecord->class = "UserAccountModify";
$oRecord->src_table = "anagraph";
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->addEvent("on_loaded_data", "UserAccount_on_loaded_data");
$oRecord->addEvent("on_do_action", "UserAccount_on_do_action");
$oRecord->addEvent("on_done_action", "UserAccount_on_done_action");

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
if($simple_registration) {
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "email";
    $obj_page_field->container_class = "profile-email";
    $obj_page_field->data_type = "";
    $obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_email_tip");   
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->addValidator("email");
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);
} else {
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "email";
    $obj_page_field->container_class = "profile-email";
    $obj_page_field->data_type = "";
    $obj_page_field->label = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_email_tip");   
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("register_email");
    $obj_page_field->addValidator("email");
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);

    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "confirmemail";
    $obj_page_field->container_class = "profile-confirmemail";
    $obj_page_field->label = ffTemplate::_get_word_by_code("register_confirm_email");
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("register_confirm_email");
    $obj_page_field->compare = "email";
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_confirmemail_tip");   
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);
} 


if(!$disable_account_registration) 
{
    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "password";
    $obj_page_field->container_class = "profile-password";
    $obj_page_field->label = ffTemplate::_get_word_by_code("user_account_password");
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("user_account_password");
    $obj_page_field->extended_type = "Password";
    $obj_page_field->crypt_method = "mysql_password";
    $obj_page_field->addValidator("password");
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_password_tip"); 
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);

    $obj_page_field = ffField::factory($cm->oPage);
    $obj_page_field->id = "confirmpassword";
    $obj_page_field->container_class = "profile-confirmpassword";
    $obj_page_field->label = ffTemplate::_get_word_by_code("user_account_confirm_password");
    $obj_page_field->placeholder = ffTemplate::_get_word_by_code("user_account_confirm_password");
    $obj_page_field->extended_type = "Password";
    $obj_page_field->compare = "password";
    $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code("profile_confirmm_password_tip");   
    $obj_page_field->setWidthComponent($arrAnagraphFields["email"]["grid"]);
    $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$arrAnagraphFields["email"]["ID_group"]]["smart_url"]);  
    
}
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
                $gridGroup[$arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]] = $db->toSql($arrAnagraphGroup[$field_value["ID_group"]]["smart_url"], "Text");
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
                    $obj_page_field->widget = "actex";
                    //$obj_page_field->widget = "activecomboex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                    $obj_page_field->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
                                                        , IFNULL(
                                                            (SELECT " . FF_PREFIX . "international.description
                                                                    FROM " . FF_PREFIX . "international
                                                                    WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
                                                                            AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
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
                    $obj_page_field->widget = "actex";
                    //$obj_page_field->widget = "activecomboex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state";
                    $obj_page_field->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
                                                        , IFNULL(
                                                            (SELECT " . FF_PREFIX . "international.description
                                                                FROM " . FF_PREFIX . "international
                                                                WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
                                                                                AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                                                AND " . FF_PREFIX . "international.is_new = 0
                                                                ORDER BY " . FF_PREFIX . "international.description
                                                                LIMIT 1
                                                            )
                                                            , " . FF_SUPPORT_PREFIX . "state.name
                                                        ) AS description
                                                    FROM " . FF_SUPPORT_PREFIX . "state
                                                    ORDER BY description";
                    $obj_page_field->setWidthComponent($field_value["grid"]);
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
                $obj_page_field->widget = "actex";
                //$obj_page_field->widget = "activecomboex";
                $obj_page_field->actex_update_from_db = true;
                $obj_page_field->actex_service = FF_SITE_PATH . "/srv/degree?type=selection";
                $obj_page_field->setWidthComponent($field_value["grid"]);
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

                $oRecord->addContent($obj_page_field, $arrAnagraphGroup[$field_value["ID_group"]]["smart_url"]);

                break;
        }
    }
    
}

$cm->oPage->addContent($oRecord);  
} else {
    ffRedirect("/");
}

function UserAccount_on_loaded_data($component) {
    if(isset($component->form_fields["billtoshipping"]))
    {
        
        if((!isset($component->form_fields["billreference"]) || (strlen($component->form_fields["billreference"]->getValue()) && $component->form_fields["billreference"]->getValue() == $component->form_fields["shippingreference"]->getValue()))
                && (!isset($component->form_fields["billaddress"]) || (strlen($component->form_fields["billaddress"]->getValue()) && $component->form_fields["billaddress"]->getValue() == $component->form_fields["shippingaddress"]->getValue()))
                && (!isset($component->form_fields["billcap"]) || (strlen($component->form_fields["billcap"]->getValue()) && $component->form_fields["billcap"]->getValue() == $component->form_fields["shippingcap"]->getValue()))
                && (!isset($component->form_fields["billtown"]) || (strlen($component->form_fields["billtown"]->getValue()) && $component->form_fields["billtown"]->getValue() == $component->form_fields["shippingtown"]->getValue()))
                && (!isset($component->form_fields["billprovince"]) || (strlen($component->form_fields["billprovince"]->getValue()) && $component->form_fields["billprovince"]->getValue() == $component->form_fields["shippingprovince"]->getValue()))
                
        ) {
                $component->form_fields["billtoshipping"]->value->setValue("1");
        }
    }	
}

function UserAccount_on_do_action($component, $action) {
    if (strlen($action)) {
        $db_check = ffDB_Sql::factory();
        switch ($action) { 
            case "update":  
                if (strlen($component->form_fields["email"]->getValue())) {
                    $sSQL = "SELECT ID FROM anagraph WHERE email = " . $db_check->toSql($component->form_fields["email"]->value) . " AND ID <> " . $db_check->toSql($component->key_fields["ID"]->value);
                    $db_check->query($sSQL);
                    if ($db_check->numRows() > 0) {
                        $component->tplDisplayError(ffTemplate::_get_word_by_code("email_not_unic_value"));
                    }
                }
                if(isset($component->form_fields["username"]))
                {
                    if (strlen($component->form_fields["username"]->getValue())) {
                        $sSQL = "SELECT ID FROM anagraph WHERE username_slug = " . $db_check->toSql(ffcommon_url_rewrite($component->form_fields["username"]->getValue())) . " AND ID <> " . $db_check->toSql($component->key_fields["ID"]->value);
                        $db_check->query($sSQL); 
                        if ($db_check->numRows() > 0) {
                            $component->tplDisplayError(ffTemplate::_get_word_by_code("username_not_unic_value"));
                        }
                    }
                }
                if(isset($component->form_fields["billtoshipping"]) && $component->form_fields["billtoshipping"]->value->getValue()) {
                    if($component->form_fields["billtoshipping"]->value->getValue()) {
                        if(isset($component->form_fields["billreference"]) && isset($component->form_fields["shippingreference"]))
                            $component->form_fields["shippingreference"]->setValue($component->form_fields["billreference"]->getValue());
                        if(isset($component->form_fields["billaddress"]) && isset($component->form_fields["shippingaddress"]))
                            $component->form_fields["shippingaddress"]->setValue($component->form_fields["billaddress"]->getValue());
                        if(isset($component->form_fields["billcap"]) && isset($component->form_fields["shippingcap"]))
                            $component->form_fields["shippingcap"]->setValue($component->form_fields["billcap"]->getValue());
                        if(isset($component->form_fields["billtown"]) && isset($component->form_fields["shippingtown"]))
                            $component->form_fields["shippingtown"]->setValue($component->form_fields["billtown"]->getValue());
                        if(isset($component->form_fields["billprovince"]) && isset($component->form_fields["shippingprovince"]))
                            $component->form_fields["shippingprovince"]->setValue($component->form_fields["billprovince"]->getValue());
                        if(isset($component->form_fields["billstate"]) && isset($component->form_fields["shippingstate"]) && !(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
                            $component->form_fields["shippingstate"]->setValue($component->form_fields["billstate"]->getValue());
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
}

function UserAccount_on_done_action($component, $action) {
    if (strlen($action)) {
        $db = ffDB_Sql::factory();
        
        switch ($action) {
            case "update":  
                $uid = get_session("UserNID");
                if(strlen($component->form_fields["password"]->getValue())) {
                    $sSQL = "UPDATE cm_mod_security_users SET
                                    password = PASSWORD(" . $db->toSql($component->form_fields["password"]->getValue()) . ")
                                WHERE ID = " . $db->toSql($uid, "Number");
                    $db->execute($sSQL);
                    
                    $sSQL = "UPDATE anagraph
                                SET password = PASSWORD(" . $db->toSql($component->form_fields["password"]->getValue()) . ")
                                WHERE ID = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                    $db->execute($sSQL);
                }
                
                
                if(isset($component->form_fields["email"]))
                {
                    if(strlen($component->form_fields["email"]->getValue())) {
                        $sSQL = "UPDATE cm_mod_security_users SET
                                        email = " . $db->toSql($component->form_fields["email"]->getValue()) . "
                                    WHERE ID = " . $db->toSql($uid, "Number");
                        $db->execute($sSQL);

                        $sSQL = "UPDATE anagraph
                                    SET email = " . $db->toSql($component->form_fields["email"]->getValue()) . "
                                    WHERE ID = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                        $db->execute($sSQL);
                    }
                }
                
                if(isset($component->form_fields["username"]))
                {
                    if(strlen($component->form_fields["username"]->getValue())) {
                        $sSQL = "UPDATE cm_mod_security_users SET
                                        username = " . $db->toSql($component->form_fields["username"]->getValue()) . "
                                        , username_slug = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["username"]->getValue())) . "
                                    WHERE ID = " . $db->toSql($uid, "Number");
                        $db->execute($sSQL);

                        $sSQL = "UPDATE anagraph
                                    SET username = " . $db->toSql($component->form_fields["username"]->getValue()) . "
                                    , username_slug = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["username"]->getValue())) . "
                                    WHERE ID = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                        $db->execute($sSQL);

                        $user_permission = get_session("user_permission");

                        if(array_key_exists("username", $user_permission)) {
                            $user_permission["username"] = $component->form_fields["username"]->getValue();
                            $user_permission["username_slug"] = ffCommon_url_rewrite($component->form_fields["username"]->getValue());
                            set_session("user_permission", $user_permission);
                            set_session("UserID", $component->form_fields["username"]->getValue());
                        }

                    }
                }
                
                $sSQL = "SELECT avatar
                                , name
                                , surname
                                , email
                                , billaddress
                                , billcap
                                , billcf
                                , billpiva
                                , billtown
                                , billprovince
                                , billreference
                                , billstate
                                , tel
                                , shippingaddress
                                , shippingcap
                                , shippingprovince
                                , shippingreference
                                , shippingstate
                                , shippingtown
                            FROM anagraph
                            WHERE ID = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $arrAnagraph = array(
                        "avatar" => $db->getField("avatar", "Text", true)
                        , "name" => $db->getField("name", "Text", true)
                        , "surname" => $db->getField("surname", "Text", true)
                        , "email" => $db->getField("email", "Text", true)
                        , "billaddress" => $db->getField("billaddress", "Text", true)
                        , "billcap" => $db->getField("billcap", "Text", true)
                        , "billcf" => $db->getField("billcf", "Text", true)
                        , "billpiva" => $db->getField("billpiva", "Text", true)
                        , "billtown" => $db->getField("billtown", "Text", true)
                        , "billprovince" => $db->getField("billprovince", "Text", true)
                        , "billreference" => $db->getField("billreference", "Text", true)
                        , "billstate" => $db->getField("billstate", "Text", true)
                        , "tel" => $db->getField("tel", "Text", true)
                        , "shippingaddress" => $db->getField("shippingaddress", "Text", true)
                        , "shippingcap" => $db->getField("shippingcap", "Text", true)
                        , "shippingprovince" => $db->getField("shippingprovince", "Text", true)
                        , "shippingreference" => $db->getField("shippingreference", "Text", true)
                        , "shippingstate" => $db->getField("shippingstate", "Text", true)
                        , "shippingtown" => $db->getField("shippingtown", "Text", true)
                    );
                }
                
                if(is_array($arrAnagraph) && count($arrAnagraph)) {
                    $sSQL = "UPDATE cm_mod_security_users SET
                                    avatar = " . $db->toSql($arrAnagraph["avatar"]) . "
                                    , name = " . $db->toSql($arrAnagraph["name"]) . "
                                    , surname = " . $db->toSql($arrAnagraph["surname"]) . "
                                    , email = " . $db->toSql($arrAnagraph["email"]) . "
                                    , billaddress = " . $db->toSql($arrAnagraph["billaddress"]) . "
                                    , billcap = " . $db->toSql($arrAnagraph["avatar"]) . "
                                    , billcf = " . $db->toSql($arrAnagraph["billcf"]) . "
                                    , billpiva = " . $db->toSql($arrAnagraph["billpiva"]) . "
                                    , billtown = " . $db->toSql($arrAnagraph["billtown"]) . "
                                    , billprovince = " . $db->toSql($arrAnagraph["billprovince"]) . "
                                    , billreference = " . $db->toSql($arrAnagraph["billreference"]) . "
                                    , billstate = " . $db->toSql($arrAnagraph["billstate"]) . "
                                    , tel = " . $db->toSql($arrAnagraph["tel"]) . "
                                    , shippingaddress = " . $db->toSql($arrAnagraph["shippingaddress"]) . "
                                    , shippingcap = " . $db->toSql($arrAnagraph["shippingcap"]) . "
                                    , shippingprovince = " . $db->toSql($arrAnagraph["shippingprovince"]) . "
                                    , shippingreference = " . $db->toSql($arrAnagraph["shippingreference"]) . "
                                    , shippingstate = " . $db->toSql($arrAnagraph["shippingstate"]) . "
                                    , shippingtown = " . $db->toSql($arrAnagraph["shippingtown"]) . "
                                WHERE ID = " . $db->toSql($uid, "Number");
                    $db->execute($sSQL);
                }
                break;

            default:
                break;
        }
    }
}