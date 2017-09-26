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
if (!AREA_USERS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$globals = ffGlobals::getInstance("gallery");
$db = ffDB_Sql::factory();

$uid = $_REQUEST["keys"]["register-ID"];

$db->query("SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_ecommerce_data AS enable_ecommerce_data
                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_bill_data AS enable_bill_data
                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_general_data AS enable_general_data
                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_setting_data AS enable_setting_data
                            , module_register.name AS register_name
                            , module_register.ID AS ID_register
                            , module_register.display_view_mode AS display_view_mode
                            , module_register.ID_anagraph_type AS ID_anagraph_type

							, " . CM_TABLE_PREFIX . "mod_security_users.username AS username
							, " . CM_TABLE_PREFIX . "mod_security_users.email AS email
                        FROM 
                            " . CM_TABLE_PREFIX . "mod_security_users
                            LEFT JOIN module_register ON module_register.ID = " . CM_TABLE_PREFIX . "mod_security_users.ID_module_register
                        WHERE 
                            " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($uid, "Number"));
if($db->nextRecord()) {
    $enable_ecommerce_data = $db->getField("enable_ecommerce_data", "Number", true);
    $enable_bill_data = $db->getField("enable_bill_data", "Number", true);
    $enable_general_data = $db->getField("enable_general_data", "Number", true);
    $enable_setting_data = $db->getField("enable_setting_data", "Number", true);

    $ID_register = $db->getField("ID_register", "Number", true);
    $register_name = $db->getField("register_name")->getValue();
    $display_view_mode = $db->getField("display_view_mode")->getValue();
    $anagraph_type = $db->getField("ID_anagraph_type")->getValue();
    $username = $db->getField("username", "Text", true);
    $email = $db->getField("email", "Text", true);
}

$default_bill_type = "";

$default_show_avatar = AREA_USER_SHOW_AVATAR;
$default_show_avatar_group = false;
$default_avatar_type = "simple";
$default_avatar_model = "default";
$default_show_custom_group = AREA_USER_SHOW_CUSTOM_FIELD;
$default_bill_required = false;
$default_show_categories = true;
$default_show_gmap = true;
$default_show_user = true;
$default_show_user_group = null;
$default_show_vcard = true;
$default_show_qrcode = true;
$default_show_report_group = true;
$default_use_tab = true;


if($anagraph_type > 0) {
	$sSQL = "SELECT anagraph_type.* 
    		FROM anagraph_type 
    		WHERE anagraph_type.ID = " . $db->toSql($anagraph_type, "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$enable_bill_data 			= $db->getField("show_bill_group", "Number", true);
		$enable_ecommerce_data		= $db->getField("show_shipping_group", "Number", true);
		$enable_general_data		= $db->getField("show_general_group", "Number", true);
		$enable_setting_data 		= $db->getField("show_setting_group", "Number", true);

		$default_bill_type 			= $db->getField("bill_type", "Text", true);

		$default_show_avatar 		= $db->getField("show_avatar", "Number", true);
		$default_show_avatar_group	= $db->getField("show_avatar_group", "Number", true);
		$default_avatar_type 		= $db->getField("avatar_type", "Number", true);
		$default_avatar_model 		= $db->getField("avatar_model", "Number", true);

		$default_show_custom_group 	= $db->getField("show_custom_group", "Number", true);
		$default_bill_required 		= $db->getField("bill_required", "Number", true);
		$default_show_categories	= $db->getField("show_categories", "Number", true);
		
		$default_show_gmap 			= $db->getField("show_gmap", "Number", true);
		$default_show_user 			= $db->getField("show_user", "Number", true);
		$default_show_user_group	= $db->getField("show_user_group", "Number", true);
		$default_force_user_edit	= $db->getField("force_user_edit", "Number", true);
		$default_show_vcard			= $db->getField("show_vcard", "Number", true);
		$default_show_qrcode		= $db->getField("show_qrcode", "Number", true);
		$default_show_report_group	= $db->getField("show_report_group", "Number", true);
		$default_use_tab 			= $db->getField("use_tab", "Number", true);
		$default_force_custom_email	= $db->getField("force_custom_email", "Number", true);
		$default_force_user_edit	= $db->getField("force_user_edit", "Number", true);
	}	
}

if(strpos($cm->path_info, VG_WS_ADMIN) === 0) {
	$is_admin_area = true;
} else {
	$is_admin_area = false;
}

$show_avatar 		= (isset($_REQUEST["af"])
						? $_REQUEST["af"]
						: $default_show_avatar
					);
$show_avatar_group 	= (isset($_REQUEST["ag"])
						? $_REQUEST["ag"]
						: $default_show_avatar_group
					);
$avatar_type		= (isset($_REQUEST["at"])
						? $_REQUEST["at"]
						: $default_avatar_type
					);
$avatar_model		= (isset($_REQUEST["am"])
						? $_REQUEST["am"]
						: $default_avatar_model
					);
$show_custom_type 	= (isset($_REQUEST["ct"])
						? $_REQUEST["ct"]
						: true
					);
$show_general_group 	= (isset($_REQUEST["gg"])
						? $_REQUEST["gg"]
						: $enable_general_data
					);
$show_custom_group 	= (isset($_REQUEST["cg"])
						? $_REQUEST["cg"]
						: $default_show_custom_group
					);
$show_bill_group	= (isset($_REQUEST["bg"])
						? $_REQUEST["bg"]
						: $enable_bill_data
					);
$bill_type			= (isset($_REQUEST["bt"])
						? $_REQUEST["bt"]
						: $default_bill_type
					);
$bill_required		= (isset($_REQUEST["br"])
						? $_REQUEST["br"]
						: $default_bill_required
					);
$show_shipping_group= (isset($_REQUEST["sg"])
						? $_REQUEST["sg"]
						: $ecommerce_data
					);
$show_categories	= (isset($_REQUEST["cf"])
						? $_REQUEST["cf"]
						: $default_show_categories
					);
$show_setting_group	= (isset($_REQUEST["cnf"])
						? $_REQUEST["cnf"]
						: ($is_admin_area
							? true
							: $enable_setting_data
						)
					);
$show_gmap			= (isset($_REQUEST["gmap"])
						? $_REQUEST["gmap"]
						: $default_show_gmap
					);
$show_user			= (isset($_REQUEST["user"])
						? $_REQUEST["user"]
						: $default_show_user
					);
$show_user_group	= (isset($_REQUEST["ug"])
						? $_REQUEST["ug"]
						: ($is_admin_area
							? ""
							: $default_show_user_group
						)
					);

$show_vcard			= (isset($_REQUEST["vcard"])
						? $_REQUEST["vcard"]
						: $default_show_vcard
					);
$show_qrcode		= (isset($_REQUEST["qrcode"])
						? $_REQUEST["qrcode"]
						: $default_show_qrcode
					);

$show_report_group	= (isset($_REQUEST["rg"])
						? $_REQUEST["rg"]
						: $default_show_report_group
					);

$show_additform_group		= (isset($_REQUEST["afg"])
								? $_REQUEST["afg"]
								: ($is_admin_area
									? true
									: false
								)
							);
$show_additvgallery_group	= (isset($_REQUEST["avg"])
								? $_REQUEST["avg"]
								: ($is_admin_area
									? true
									: false
								)
							);

$use_tab					= (isset($_REQUEST["tab"])
								? $_REQUEST["tab"]
								: ($is_admin_area
									? true
									: $default_use_tab
								)
							);
$hide_rel_group				= (isset($_REQUEST["hrg"])
								? $_REQUEST["hrg"]
								: ($is_admin_area
									? false
									: true
								)
							);

$anagraph_params = "af=" . $show_avatar 
				. "&ag=" . $show_avatar_group 
				. "&at=" . $avatar_type 
				. "&am=" . $avatar_model 
				. "&ct=" . $show_custom_type 
				. "&gg=" . $show_general_group 
				. "&cg=" . $show_custom_group 
				. "&bg=" . $show_bill_group 
				. "&bt=" . $bill_type
				. "&sg=" . $show_shipping_group 
				. "&cf=" . $show_categories 
				. "&cnf=" . $show_setting_group 
				. "&gmap=" . $show_gmap 
				. "&user=" . $show_user 
				. "&ug=" . $show_user_group 
				. "&vcard=" . $show_vcard 
				. "&qrcode=" . $show_qrcode 
				. "&rg=" . $show_report_group
				. "&tab=" . $use_tab;	
	
	
if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setstatus"]) && $uid > 0) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users 
            SET " . CM_TABLE_PREFIX . "mod_security_users.status = " . $db->toSql($_REQUEST["setstatus"], "Number") . "
            WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($uid, "Number");
    $db->execute($sSQL);

	$to[0]["name"] = $username;
	$to[0]["mail"] = $email;

	$fields["account"]["username"] = $username;
	$fields["account"]["email"] = $email;

	if(check_function("process_mail")) {
		if($_REQUEST["setstatus"] > 0) {
			$fields["status"]["description"] = ffTemplate::_get_word_by_code("account_actived");
			
			$rc = process_mail(email_system("account activated"), $to, NULL, NULL, $fields);
		} else {
			$fields["status"]["description"] = ffTemplate::_get_word_by_code("account_suspended");
			
			$rc = process_mail(email_system("account suspended"), $to, NULL, NULL, $fields);
		}
    }
    
    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("UserModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("UserModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
}  

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "UserModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("user_account");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_users";

$oRecord->user_vars["anagraph_type"] = $anagraph_type;

if(check_function("MD_register_on_check_after"))
	$oRecord->addEvent("on_check_after", "MD_register_on_check_after");
$oRecord->addEvent("on_loaded_data", "UserModify_on_loaded_data");
$oRecord->addEvent("on_do_action", "UserModify_on_do_action");
$oRecord->addEvent("on_done_action", "UserModify_on_done_action");

if($display_view_mode) {
	if($oRecord->class)
		$oRecord->class .= " ";
	
	$oRecord->class .= $display_view_mode;

    if(!isset($globals))
         $globals = ffGlobals::getInstance("gallery");
    
    $cm->oPage->tplAddJs("jquery." . $display_view_mode
        , array(
            "file" => "jquery." . $display_view_mode . ".js"
            , "path" => FF_THEME_DIR . "/library/plugins/jquery." . $display_view_mode
    ));
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
            , array(
                "file" => $real_name
                , "path" => $real_path
                , "async" => false
        ));
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "register-ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(ENABLE_AVATAR_SYSTEM && $show_avatar) {                                          
	if($show_avatar_group) { 
		if($use_tab) {
			$oRecord->addTab("accountinfo");
			$oRecord->setTabTitle("accountinfo", ffTemplate::_get_word_by_code("user_accountinfo"));
		}
		$oRecord->addContent(null, true, "accountinfo"); 
		$oRecord->groups["accountinfo"] = array(
		                                         "title" => ffTemplate::_get_word_by_code("user_accountinfo")
		                                         , "cols" => 1
		                                         , "tab" => ($use_tab ? "accountinfo" : null)
		                                      );
	}
}


if($show_custom_group) {
	if($use_tab) {
		$oRecord->addTab("account");
		$oRecord->setTabTitle("account", ffTemplate::_get_word_by_code("user_account"));
	}
	$oRecord->addContent(null, true, "account"); 
	$oRecord->groups["account"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("user_account")
	                                         , "cols" => 1
	                                         , "tab" => ($use_tab ? "account" : null)
	                                      );
}
if(ENABLE_AVATAR_SYSTEM && $show_avatar) {                                          
    $oField = ffField::factory($cm->oPage);
    $oField->id = "avatar";
    $oField->label = ffTemplate::_get_word_by_code("user_avatar");
    $oField->base_type = "Text";
    $oField->extended_type = "File";

    $oField->file_storing_path = DISK_UPDIR . "/users/" . $uid;
    $oField->file_temp_path = DISK_UPDIR . "/users";
    $oField->file_max_size = MAX_UPLOAD;
    $oField->file_show_filename = true; 
    $oField->file_full_path = true;
    $oField->file_check_exist = false;
    $oField->file_normalize = true;
    $oField->file_show_preview = true;

    $oField->uploadify_model = $avatar_model;
    $oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);

    $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
    $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";
//    $oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
//    $oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";

    $oField->control_type = "file";
    $oField->file_show_delete = true;
    if($avatar_type == "advanced") {
	    $oField->file_writable = true;
	    $oField->widget = "kcuploadify"; 
	} else {
		$oField->widget = "uploadify"; 
	}
	if(check_function("set_field_uploader")) { 
		$oField = set_field_uploader($oField);
	}	
    $oRecord->addContent($oField, ($show_avatar_group ? "accountinfo" : ($show_custom_group ? "account" : null))); 
} 
                                      
$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("user_username");
$oField->required = true;
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));
    
$oField = ffField::factory($cm->oPage);
$oField->id = "password";
$oField->label = ffTemplate::_get_word_by_code("user_password");
$oField->extended_type = "Password";
$oField->crypt_method = "mysql_password";
if(ENABLE_PASSWORD_VALIDATOR) {
    $oField->addValidator("password");
}
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));

$oField = ffField::factory($cm->oPage);
$oField->id = "confirmpassword";
$oField->label = ffTemplate::_get_word_by_code("user_confirm_password");
$oField->extended_type = "Password";
$oField->compare = "password";
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));    

if($show_general_group) {
	if(!$show_bill_group) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->container_class = "name";
		$oField->label = ffTemplate::_get_word_by_code("user_name");
		$oRecord->addContent($oField, ($show_custom_group ? "account" : null));

		$oField = ffField::factory($cm->oPage);
		$oField->id = "surname";
		$oField->container_class = "surname";
		$oField->label = ffTemplate::_get_word_by_code("user_surname");
		$oRecord->addContent($oField, ($show_custom_group ? "account" : null));
	}	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "tel";
	$oField->container_class = "tel";
	$oField->label = ffTemplate::_get_word_by_code("user_tel");
	$oRecord->addContent($oField, ($show_custom_group ? "account" : null));
}

$oField = ffField::factory($cm->oPage);
$oField->id = "real_email";
$oField->data_source = "email";
$oField->label = ffTemplate::_get_word_by_code("user_email");
$oField->store_in_db = false;
$oField->control_type = "label";
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->data_type = "";
$oField->label = ffTemplate::_get_word_by_code("user_email");
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));

$oField = ffField::factory($cm->oPage);
$oField->id = "confirmemail";
$oField->label = ffTemplate::_get_word_by_code("user_confirm_email");
$oField->compare = "email";
$oRecord->addContent($oField, ($show_custom_group ? "account" : null));   

if($show_custom_type) {
	if($ID_register > 0) {
		$sSQL = "SELECT anagraph_fields.* 
				FROM anagraph_fields 
				WHERE anagraph_fields.ID_type = " . $db->toSql($anagraph_type, "Number") . "
					AND NOT(anagraph_fields.hide > 0)";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$count_anagraph_field = $db->numRows();
		}
		if($anagraph_type > 0 && $count_anagraph_field > 0) {
		    $db->query("SELECT anagraph_fields.*
	                                , extended_type.name AS extended_type
	                                , check_control.ff_name AS check_control
	                                , anagraph_type_group.name AS `group_field`
	                                , anagraph_fields_selection.selectionSource AS selectionSource
									, anagraph_fields_selection.field AS field
									, ( SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.value 
		                                FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
		                                WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($uid, "Number") . "
		                                    AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field = anagraph_fields.name 
		                            ) AS value
	                            FROM 
	                                anagraph_fields
			                        LEFT JOIN extended_type ON extended_type.ID = anagraph_fields.ID_extended_type
			                        LEFT JOIN check_control ON check_control.ID = anagraph_fields.ID_check_control
			                        LEFT JOIN anagraph_type_group ON anagraph_type_group.ID = anagraph_fields.ID_group_backoffice
			                        LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields.ID_selection
	                            WHERE 1
									AND anagraph_fields.ID_type = " . $db->toSql($anagraph_type, "Number") . "
									AND NOT(anagraph_fields.hide > 0)
	                            ORDER BY anagraph_fields.`order_detail`, anagraph_fields.name
	                        	");
		} else {
			$db->query("SELECT module_register_fields.*
			                        , extended_type.name AS extended_type
			                        , check_control.ff_name AS check_control
			                        , module_form_fields_group.name AS `group_field`
			                        , anagraph_fields_selection.selectionSource AS selectionSource
									, anagraph_fields_selection.field AS field
									, ( SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.value 
			                            FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
			                            WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($uid, "Number") . "
			                                AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field = module_register_fields.name 
			                        ) AS value
			                    FROM 
			                        module_register_fields
			                        LEFT JOIN extended_type ON extended_type.ID = module_register_fields.ID_extended_type
			                        LEFT JOIN check_control ON check_control.ID = module_register_fields.ID_check_control
			                        LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_register_fields.ID_form_fields_group
			                        LEFT JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = module_register_fields.ID_selection
			                    WHERE 
			                        module_register_fields.ID_module = " . $db->toSql($ID_register, "Number") . "
			                    ORDER BY module_register_fields.`order`, module_register_fields.name
			                    ");
		}
		if($db->nextRecord()) {
		    if(check_function("MD_register_on_done_action"))
    			$oRecord->addEvent("on_done_action", "MD_register_on_done_action");
		    
		    $oRecord->user_vars["disable_ret_url"] = true; 
			
			$db_selection = ffDB_Sql::factory();
		     do {
                $oField = ffField::factory($cm->oPage);
				
				$field_name = $db->getField("name")->getValue();

				$arrField[$field_name]["ID"] = $db->getField("ID", "Number", true);
				$arrField[$field_name]["name"] = $db->getField("name")->getValue();
				$arrField[$field_name]["group_field"] = $db->getField("group_field")->getValue() 
														   ? preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("group_field")->getValue()) 
														   : ($show_custom_group ? "account" : null);
				$arrField[$field_name]["hide"] = $db->getField("hide", "Number", true);
				$arrField[$field_name]["enable_in_mail"] = $db->getField("enable_in_mail", "Number", true);
				$arrField[$field_name]["unic_value"] = $db->getField("unic_value", "Number", true);
				$arrField[$field_name]["writable"] = $db->getField("writable", "Number", true);
				//$arrField[$field_name]["ID_field"] = $db->getField("ID_field", "Number", true);
				$arrField[$field_name]["ID_selection"] = $db->getField("ID_selection", "Number", true);
				$arrField[$field_name]["disable_select_one"] = $db->getField("disable_select_one", "Number", true);
				$arrField[$field_name]["require"] = $db->getField("require", "Number", true);
				$arrField[$field_name]["check_control"] = $db->getField("check_control")->getValue();
				$arrField[$field_name]["extended_type"] = $db->getField("extended_type")->getValue();
				$arrField[$field_name]["selectionSource"] = $db->getField("selectionSource")->getValue();
				$arrField[$field_name]["field"] = $db->getField("field")->getValue();
				$arrField[$field_name]["default_value"] = $db->getField("value")->getValue();
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
		} while($db->nextRecord());
		
		if(is_array($arrField) && count($arrField)) {
		 foreach($arrField AS $field_key => $field_value) {
	        if (strlen($field_value["group_field"]) && !isset($oRecord->groups[$field_value["group_field"]])) { 
			    $oRecord->addContent(null, true, $field_value["group_field"]); 
				if($use_tab) {
					$oRecord->addTab($field_value["group_field"]);
					$oRecord->setTabTitle($field_value["group_field"], ffTemplate::_get_word_by_code("register_" . $field_value["group_field"]));
				}
			    $oRecord->groups[$field_value["group_field"]] = array(
			                                             "title" => ffTemplate::_get_word_by_code("register_" . $field_value["group_field"])
			                                             , "cols" => 1
			                                             , "tab" => ($use_tab ? $field_value["group_field"] : null)
			                                          );
	        }
			if($field_value["hide"]) {
				$hide_class = " hide";
			} else {
				$hide_class = "";
			}
	        
	        $oField = ffField::factory($cm->oPage);

			if($field_key == "name"
				|| $field_key == "surname"	
				|| $field_key == "tel"	
			) {
				$field_id = $field_value["name"];
				$oField->store_in_db = true;
			} else {
				$field_id = $field_value["ID"];
				$oField->store_in_db = false;
			}
			
	        $oField->id = $field_id;
	        $oField->container_class = "register_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_value["name"]) . $hide_class;
	        $oField->label = ffTemplate::_get_word_by_code($field_value["name"]);
	        $oField->user_vars["group_field"] = $field_value["group_field"];
	        $oField->user_vars["name"] = $field_value["name"];
	        $oField->user_vars["enable_in_mail"] = $field_value["enable_in_mail"];
	        $oField->user_vars["unic_value"] = $field_value["unic_value"]; 
	        
	        $oField->data_type = "";
	        
		    if($field_value["enable_tip"])
        		$oField->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . $field_value["name"]) . "_tip");
		    else
				unset($oField->properties["title"]);

	        $selection_value = array();   
			
			if(check_function("get_field_by_extension"))
				$js .= get_field_by_extension($oField, $field_value, "register");

			$oField->actex_hide_empty = true;
	        if(isset($_GET[$field_value["name"]]) && strlen($_GET[$field_value["name"]])) {
	            $oField->default_value = new ffData($_REQUEST[$field_value["name"]], $type_value);
	            
	        }
	        if($field_value["require"]) {
	            $oField->required = true;
	        }
			
	        if(strlen($field_value["check_control"]))
	            $oField->addValidator($field_value["check_control"]);
	        //print_r($oField);
	        $oRecord->addContent($oField, $field_value["group_field"]);
		 }
    }
			
			/*
                if($field_name == "name"
                    || $field_name == "surname"    
                    || $field_name == "tel"    
                ) {
                    $field_id = $field_name;
                    $obj_page_field->data_type = "db";
                    $obj_page_field->store_in_db = true;
                } else {
                    $field_id = $db->getField("ID")->getValue();
                    $obj_page_field->data_type = "";
                    $obj_page_field->store_in_db = false;
                }

		        $group_field = $db->getField("group_field")->getValue() 
		                            ? preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("group_field")->getValue()) 
		                            : ($show_custom_group ? "account" : null);

		        if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
		            $oRecord->addContent(null, true, $group_field); 
					if($use_tab) {
						$oRecord->addTab($group_field);
						$oRecord->setTabTitle($group_field, ffTemplate::_get_word_by_code("register_" . $group_field));
					}
		            $oRecord->groups[$group_field] = array(
		                                                     "title" => ffTemplate::_get_word_by_code("register_" . $group_field)
		                                                     , "cols" => 1
		                                                     , "tab" => ($use_tab ? $group_field : null)
		                                                  );
		        }
		        
		        $obj_page_field->id = $field_id;
		        $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
		        $obj_page_field->user_vars["group_field"] = $group_field;
		        $obj_page_field->user_vars["name"] = $field_name;
		        $obj_page_field->user_vars["enable_in_mail"] = $db->getField("enable_in_mail", "Number", true);
		        $obj_page_field->user_vars["unic_value"] = $db->getField("unic_value", "Number", true);
		        
		        $writable = $db->getField("writable", "Number", true);

		        if($db->getField("enable_tip", "Number", true))
        			$obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $register_name . $field_name) . "_tip");
			    else
					unset($obj_page_field->properties["title"]);

		        $selection_value = array();        
		        
				/*
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
		                                                vgallery_fields.ID = " . $db->toSql($db->getField("ID_field")) . " 
		                                                AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
		                                       ) UNION (
													SELECT
														 anagraph_fields_selection_value.name AS nameID
														 , anagraph_fields_selection_value.name AS name
														 , anagraph_fields_selection_value.`order` AS `order`
														FROM anagraph_fields_selection_value 
															 INNER JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields_selection_value.ID_selection
														WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($db->getField("ID_selection", "Number")) . "
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
		                                                , vgallery_fields.`order_backoffice` AS `order`
		                                           FROM vgallery_rel_nodes_fields
		                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
		                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
		                                           WHERE 
		                                                vgallery_fields.ID = " . $db->toSql($db->getField("ID_field")) . " 
		                                                AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
		                                       ) UNION (
													SELECT
														 anagraph_fields_selection_value.name AS nameID
														 , anagraph_fields_selection_value.name AS name
														 , anagraph_fields_selection_value.`order` AS `order`
														FROM anagraph_fields_selection_value 
															 INNER JOIN anagraph_fields_selection ON anagraph_fields_selection.ID = anagraph_fields_selection_value.ID_selection
														WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($db->getField("ID_selection", "Number")) . "
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
		                        $obj_page_field->widget = "";
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

		                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $uid;
		                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
		                $obj_page_field->file_max_size = MAX_UPLOAD;

		                $obj_page_field->file_show_filename = true; 
		                $obj_page_field->file_full_path = false;
		                $obj_page_field->file_check_exist = false;
		                $obj_page_field->file_normalize = true;
		                 
		                $obj_page_field->file_show_preview = true;
		                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $uid . "/[_FILENAME_]";
		                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $uid . "/[_FILENAME_]";
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

		                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $uid;
		                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
		                $obj_page_field->file_max_size = MAX_UPLOAD;

		                $obj_page_field->file_show_filename = true; 
		                $obj_page_field->file_full_path = false;
		                $obj_page_field->file_check_exist = false;
		                $obj_page_field->file_normalize = true;
		                 
		                $obj_page_field->file_show_preview = true;
		                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $uid . "/[_FILENAME_]";
		                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $uid . "/[_FILENAME_]";
		                $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
		                $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";

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

		                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/" . $uid;
		                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
		                $obj_page_field->file_max_size = MAX_UPLOAD;

		                $obj_page_field->file_show_filename = true; 
		                $obj_page_field->file_full_path = false;
		                $obj_page_field->file_check_exist = false;
		                $obj_page_field->file_normalize = true;
		                 
		                $obj_page_field->file_show_preview = true;
		                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/" . $uid . "/[_FILENAME_]";
		                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/" . $uid . "/[_FILENAME_]";
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
				 
				if(check_function("get_field_by_extension"))
					$js .= get_field_by_extension($obj_page_field, $field_value, "register");
				$obj_page_field->default_value = $db->getField("value", $type_value);
		       /* if($db->getField("require", "Number", true)) {
		            $obj_page_field->required = true;
		        } 

		        if(strlen($db->getField("check_control")->getValue()))
		            $obj_page_field->addValidator($db->getField("check_control")->getValue());
		      
		        $oRecord->addContent($obj_page_field, $group_field);
		      */
		}
	}
}

//if(AREA_SHOW_ECOMMERCE) {
	//bill data
	if($show_bill_group) {
		if($use_tab) {
		    $oRecord->addTab("bill");
			$oRecord->setTabTitle("bill", ffTemplate::_get_word_by_code("user_bill"));
		}

	    $oRecord->addContent(null, true, "bill"); 
	    $oRecord->groups["bill"] = array(
	                                             "title" => ffTemplate::_get_word_by_code("user_bill")
	                                             , "cols" => 1
	                                             , "tab" => ($use_tab ? "bill" : null)
	                                          );

	    if(!strlen($bill_type) || strpos($bill_type, "namesurname") !== false) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "name";
			$oField->container_class = "name";
			$oField->label = ffTemplate::_get_word_by_code("bill_name");
			$oRecord->addContent($oField, "bill");

			$oField = ffField::factory($cm->oPage);
			$oField->id = "surname";
			$oField->container_class = "surname";
			$oField->label = ffTemplate::_get_word_by_code("bill_surname");
			$oRecord->addContent($oField, "bill");
		}

	    if(!strlen($bill_type) || strpos($bill_type, "reference") !== false) {
		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billreference";
		    $oField->label = ffTemplate::_get_word_by_code("bill_reference");
		    $oField->required = $bill_required;
		    $oRecord->addContent($oField, "bill");
		}

		if(!strlen($bill_type) || strpos($bill_type, "cf") !== false) {
		    $oField = ffField::factory($cm->oPage); 
		    $oField->id = "billcf";
		    $oField->label = ffTemplate::_get_word_by_code("bill_cf");
			if(strpos($bill_type, "cf-validator") !== false) {
			    $oField->addValidator("cf");
			}
		    $oRecord->addContent($oField, "bill");
		}
		
		if(!strlen($bill_type) || strpos($bill_type, "piva") !== false) {
		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billpiva";
		    $oField->label = ffTemplate::_get_word_by_code("bill_piva");
		    $oField->required = $bill_required;
			if(strpos($bill_type, "piva-validator") !== false) {
			    $oField->addValidator("piva");
			}
		    $oRecord->addContent($oField, "bill");
		}

		if(!strlen($bill_type) || strpos($bill_type, "address") !== false) {
		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billaddress";
		    $oField->label = ffTemplate::_get_word_by_code("bill_address");
		    $oField->required = $bill_required;
		    $oRecord->addContent($oField, "bill");

		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billcap";
		    $oField->label = ffTemplate::_get_word_by_code("bill_cap");
		    $oField->required = $bill_required;
		    $oRecord->addContent($oField, "bill");

		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billtown";
		    $oField->label = ffTemplate::_get_word_by_code("bill_town");
		    $oField->required = $bill_required;
		    $oRecord->addContent($oField, "bill");

		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "billprovince";
		    $oField->label = ffTemplate::_get_word_by_code("bill_province");
		    $oField->required = $bill_required;
		    $oRecord->addContent($oField, "bill");

		    if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
		        $oRecord->additional_fields["billstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
			} else {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "billstate";
			    $oField->label = ffTemplate::_get_word_by_code("bill_state");
			    $oField->base_type = "Number";
			    $oField->widget = "actex";
			    //$oField->widget = "activecomboex";
			    $oField->source_SQL = "SELECT
				                                " . FF_SUPPORT_PREFIX . "state.ID
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
				                            FROM
				                                " . FF_SUPPORT_PREFIX . "state
				                            ORDER BY description";
			    //$oField->actex_child = "billprovince";
			    //$oField->properties["disabled"] = "disabled";
			    $oField->required = $bill_required;
			    $oField->actex_update_from_db = true;
                $oField->actex_service = FF_SITE_PATH . "/srv/place/state";
			    $oRecord->addContent($oField, "bill");
			}
		}
	}
	if(AREA_ECOMMERCE_USE_SHIPPING && AREA_ECOMMERCE_SHIPPINGPRICE_SHOW_MODIFY && $show_shipping_group) {
		if($use_tab) {
			$oRecord->addTab("shipping");
			$oRecord->setTabTitle("shipping", ffTemplate::_get_word_by_code("user_shipping"));
		}

		$oRecord->addContent(null, true, "shipping"); 
		$oRecord->groups["shipping"] = array(
		                                         "title" => ffTemplate::_get_word_by_code("user_shipping")
		                                         , "cols" => 1
		                                         , "tab" => ($use_tab ? "shipping" : null)
		                                      );

		                                      
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "billtoshipping";
	    $oField->label = ffTemplate::_get_word_by_code("bill_to_shipping");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->data_type = "";
		$oField->store_in_db = false;
	    $oRecord->addContent($oField, "shipping");
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "shippingreference";
		$oField->label = ffTemplate::_get_word_by_code("user_shipping_reference");
		$oRecord->addContent($oField, "shipping");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "shippingaddress";
		$oField->label = ffTemplate::_get_word_by_code("user_shipping_address");
		$oRecord->addContent($oField, "shipping");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "shippingcap";
		$oField->label = ffTemplate::_get_word_by_code("user_shipping_cap");
		$oRecord->addContent($oField, "shipping");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "shippingtown";
		$oField->label = ffTemplate::_get_word_by_code("user_shipping_town");
		$oRecord->addContent($oField, "shipping");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "shippingprovince";
		$oField->label = ffTemplate::_get_word_by_code("user_shipping_province");
		$oRecord->addContent($oField, "shipping");

		if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
		    $oRecord->additional_fields["shippingstate"] = new ffData(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE, "Number");
		} else {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "shippingstate";
			$oField->label = ffTemplate::_get_word_by_code("user_shipping_state");
			$oField->base_type = "Number";
			$oField->widget = "actex";
			//$oField->widget = "activecomboex";
			$oField->source_SQL = "SELECT
		                                " . FF_SUPPORT_PREFIX . "state.ID
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
		                            FROM
		                                " . FF_SUPPORT_PREFIX . "state
		                            ORDER BY description";
			$oField->actex_update_from_db = true;
            $oField->actex_service = FF_SITE_PATH . "/srv/place/state";
			$oRecord->addContent($oField, "shipping");
		}
	}
//} 

if(AREA_USERS_SHOW_SETTINGS && $show_setting_group) {
	if($use_tab) {
		$oRecord->addTab("settings");
		$oRecord->setTabTitle("settings", ffTemplate::_get_word_by_code("user_settings"));
	}

	$oRecord->addContent(null, true, "settings"); 
	$oRecord->groups["settings"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("user_settings")
	                                         , "cols" => 1
	                                         , "tab" => ($use_tab ? "settings" : null)
	                                      );

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_ecommerce_data";
	$oField->label = ffTemplate::_get_word_by_code("user_enable_ecommerce_data");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_bill_data";
	$oField->label = ffTemplate::_get_word_by_code("user_enable_bill_data");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_general_data";
	$oField->label = ffTemplate::_get_word_by_code("user_enable_general_data");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oRecord->addContent($oField, "settings");
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_setting_data";
	$oField->label = ffTemplate::_get_word_by_code("user_enable_setting_data");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oRecord->addContent($oField, "settings");
		
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_manage";
	$oField->label = ffTemplate::_get_word_by_code("user_enable_manage");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "newsletter";
	$oField->label = ffTemplate::_get_word_by_code("user_newsletter");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "public";
	$oField->label = ffTemplate::_get_word_by_code("user_public");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_languages";
	$oField->label = ffTemplate::_get_word_by_code("user_languages");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT
	                           " . FF_PREFIX . "languages.ID,
	                           " . FF_PREFIX . "languages.description
	                       FROM
	                           " . FF_PREFIX . "languages
	                       WHERE " . FF_PREFIX . "languages.status > 0
	                       ORDER BY " . FF_PREFIX . "languages.description";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("user_languages_default");
	if(!$_REQUEST["keys"]["register-ID"]) {
	    $db->query("SELECT ID FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1' AND code = " . $db->toSql(LANGUAGE_INSET, "Text"));
	    if($db->nextRecord()) {
	        $oField->default_value = $db->getField("ID", "Number");
	    }
	}
	$oRecord->addContent($oField, "settings");
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_module_register";
	$oField->label = ffTemplate::_get_word_by_code("user_ID_module_register");
	$oField->base_type = "Number";
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;

	$oField->actex_dialog_url = get_path_by_rule("addons") . "/register";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=RegisterConfigModify_confirmdelete";
	$oField->resources[] = "RegisterConfigModify";

	$oField->source_SQL = "SELECT
	                        module_register.ID
	                        , module_register.name
	                    FROM
	                        module_register
	                    ORDER BY module_register.name";
	$oRecord->addContent($oField, "settings");
}

$cm->oPage->addContent($oRecord);

if(AREA_USERS_SHOW_ADDIT_FORM && $show_additform_group) {
	if($use_tab) {
		$oRecord->addTab("additionaldata");
		$oRecord->setTabTitle("additionaldata", ffTemplate::_get_word_by_code("user_additionaldata"));
	}
	
	$oRecord->addContent(null, true, "additionaldata"); 
	$oRecord->groups["additionaldata"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("user_additionaldata")
	                                         , "cols" => 1
	                                         , "tab" => ($use_tab ? "additionaldata" : null)
	                                      );

	$oDetail = ffDetails::factory($cm->oPage);
	$oDetail->id = "UsersAdditionaldata";
	$oDetail->title = ffTemplate::_get_word_by_code("users_additionaldata_title");
	$oDetail->src_table = "users_rel_module_form";
	$oDetail->order_default = "ID";
	$oDetail->fields_relationship = array ("uid" => "register-ID");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_module";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("users_additionaldata_ID_form");
	$oField->extended_type = "Selection";
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT
	                        module_form.ID
	                        , module_form.name
	                    FROM
	                        module_form
	                    ORDER BY module_form.name";
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "request";
	$oField->label = ffTemplate::_get_word_by_code("users_additionaldata_request");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "public";
	$oField->label = ffTemplate::_get_word_by_code("users_additionaldata_public");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "order";
	$oField->label = ffTemplate::_get_word_by_code("users_additionaldata_order");
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail, "additionaldata");
	$cm->oPage->addContent($oDetail);
}

if(AREA_USERS_SHOW_ADDIT_VGALLERY && $show_additvgallery_group) {
	if($use_tab) {
		$oRecord->addTab("additvgallery");
		$oRecord->setTabTitle("additvgallery", ffTemplate::_get_word_by_code("user_additvgallery"));
	}
	$oRecord->addContent(null, true, "additvgallery"); 
	$oRecord->groups["additvgallery"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("user_additvgallery")
	                                         , "cols" => 1
	                                         , "tab" => ($use_tab ? "additvgallery" : null)
	                                      );

	$oDetail = ffDetails::factory($cm->oPage);
	$oDetail->id = "UsersAdditvgallery";
	$oDetail->title = ffTemplate::_get_word_by_code("users_additvgallery_title");
	$oDetail->src_table = "users_rel_vgallery";
	$oDetail->order_default = "ID";
	$oDetail->fields_relationship = array ("uid" => "register-ID");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_vgallery_nodes";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("users_additvgallery_ID_vgallery_nodes");
	$oField->base_type = "Number";
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->source_SQL = "SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM vgallery_nodes WHERE vgallery_nodes.is_dir > 0 ORDER BY path";
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "cascading";
	$oField->label = ffTemplate::_get_word_by_code("users_additvgallery_cascading");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oDetail->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "visible";
    $oField->label = ffTemplate::_get_word_by_code("users_additvgallery_visible");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->extended_type = "Boolean";
    $oField->checked_value = new ffData("1", "Number");
    $oField->unchecked_value = new ffData("0", "Number");
    $oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "request";
	$oField->label = ffTemplate::_get_word_by_code("users_additvgallery_request");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "order";
	$oField->label = ffTemplate::_get_word_by_code("users_additvgallery_order");
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail, "additvgallery");
	$cm->oPage->addContent($oDetail);
}

if(AREA_GROUPS_SHOW_MODIFY) {
	$user_permission = get_session("user_permission");
	if($use_tab) {
		$oRecord->addTab("members");
		$oRecord->setTabTitle("members", ffTemplate::_get_word_by_code("user_members"));
	}
	$oRecord->addContent(null, true, "members"); 
	$oRecord->groups["members"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("user_groups")
	                                         , "cols" => 1
	                                         , "tab" => ($use_tab ? "members" : null)
	                                      );
	$oField = ffField::factory($cm->oPage);
	$oField->id = "status";
	$oField->label = ffTemplate::_get_word_by_code("user_status");
	$oField->control_type = "checkbox";
	$oField->extended_type = "Boolean";
	$oField->checked_value = new ffData("1");
	$oField->unchecked_value = new ffData("0");
	$oField->default_value = new ffData("1");
	$oRecord->addContent($oField, "members");

	$strGroups = " AND " . CM_TABLE_PREFIX . "mod_security_groups.level <= (SELECT " . CM_TABLE_PREFIX . "mod_security_groups.level FROM " . CM_TABLE_PREFIX . "mod_security_groups WHERE " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . $db->toSql($user_permission["primary_gid"], "Number") . ")";
	if($show_user_group) {
		$strGroups = "";
		if(strpos($show_user_group, "|") !== false) {
			$arrUserGroup = explode("|", $show_user_group);
			if(is_array($arrUserGroup) && count($arrUserGroup)) {
				foreach($arrUserGroup AS $arrUserGroup_key => $arrUserGroup_value) {
					if($arrUserGroup_value) {
						if(strlen($strGroups))
							$strGroups .= ",";

						$strGroups .= $db->toSql($arrUserGroup_value);
					}
				}
			}
		} else {
			$strGroups = $db->toSql($show_user_group);
		}
		if(strlen($strGroups)) {
			$sSQL_group = " AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN (" . $strGroups . ")";
		}
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "primary_gid";
	$oField->label = ffTemplate::_get_word_by_code("user_primary_gid");
	$oField->extended_type = "Selection";
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT
	                        " . CM_TABLE_PREFIX . "mod_security_groups.gid
							, IFNULL(
								(SELECT " . FF_PREFIX . "international.description
									FROM " . FF_PREFIX . "international
									WHERE " . FF_PREFIX . "international.word_code = " . CM_TABLE_PREFIX . "mod_security_groups.name
										AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
										AND " . FF_PREFIX . "international.is_new = 0
                                    ORDER BY " . FF_PREFIX . "international.description
                                    LIMIT 1
								)
								, " . CM_TABLE_PREFIX . "mod_security_groups.name
							) AS description
	                    FROM
	                        " . CM_TABLE_PREFIX . "mod_security_groups
	                    WHERE 1 
	                    	$sSQL_group
	                    ORDER BY " . CM_TABLE_PREFIX . "mod_security_groups.name";
	$oField->required = true;
	$oRecord->addContent($oField, "members");
	
	if(!$hide_rel_group) {
		$oDetail = ffDetails::factory($cm->oPage);
		$oDetail->id = "UsersGroups";
		$oDetail->title = ffTemplate::_get_word_by_code("users_groups_title");
		$oDetail->src_table = CM_TABLE_PREFIX . "mod_security_users_rel_groups";
		$oDetail->order_default = "gid";
		$oDetail->fields_relationship = array ("uid" => "register-ID");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->data_source = "gid";
		$oField->base_type = "Number";
		$oDetail->addKeyField($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "gid";
		$oField->label = ffTemplate::_get_word_by_code("user_rel_gid");
		$oField->extended_type = "Selection";
		$oField->base_type = "Number";
		$oField->source_SQL = "SELECT
			                    " . CM_TABLE_PREFIX . "mod_security_groups.gid
			                    , " . CM_TABLE_PREFIX . "mod_security_groups.name
			                FROM
			                    " . CM_TABLE_PREFIX . "mod_security_groups
			                    WHERE 1
		                    		$sSQL_group
			                ORDER BY " . CM_TABLE_PREFIX . "mod_security_groups.name";
		$oField->required = true;
		$oDetail->addContent($oField);

		$oRecord->addContent($oDetail, "members");
		$cm->oPage->addContent($oDetail);
	}
}
function UserModify_on_loaded_data($component) {
	if($component->first_access 
		&& AREA_SHOW_ECOMMERCE 
		&& AREA_ECOMMERCE_USE_SHIPPING 
		&& AREA_ECOMMERCE_SHIPPINGPRICE_SHOW_MODIFY 
		&& isset($component->form_fields["billreference"]) 
		&& isset($component->form_fields["shippingreference"])
	) {
		if(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0) {
			$check_shipping = true;
		} else {
			if(strlen($component->form_fields["billstate"]->getValue()) && $component->form_fields["billstate"]->getValue() == $component->form_fields["shippingstate"]->getValue()) {
				$check_shipping = true;
			} else {
				$check_shipping = false;
			}
		}
		if((strlen($component->form_fields["billreference"]->getValue()) && $component->form_fields["billreference"]->getValue() == $component->form_fields["shippingreference"]->getValue())
			&& (strlen($component->form_fields["billaddress"]->getValue()) && $component->form_fields["billaddress"]->getValue() == $component->form_fields["shippingaddress"]->getValue())
			&& (strlen($component->form_fields["billcap"]->getValue()) && $component->form_fields["billcap"]->getValue() == $component->form_fields["shippingcap"]->getValue())
			&& (strlen($component->form_fields["billtown"]->getValue()) && $component->form_fields["billtown"]->getValue() == $component->form_fields["shippingtown"]->getValue())
			&& (strlen($component->form_fields["billprovince"]->getValue()) && $component->form_fields["billprovince"]->getValue() == $component->form_fields["shippingprovince"]->getValue())
			&& ($check_shipping)
		) {
			$component->form_fields["billtoshipping"]->value->setValue("1");
		} else {
			$component->form_fields["billtoshipping"]->value->setValue("0");	
		}
	}		
	
}

function UserModify_on_do_action($component, $action) {
    switch ($action) {
        case "insert":
        case "update":
//ffErrorHandler::raise("as", E_USER_ERROR, null,get_defined_vars());
        	if(AREA_SHOW_ECOMMERCE 
        		&& AREA_ECOMMERCE_USE_SHIPPING 
        		&& AREA_ECOMMERCE_SHIPPINGPRICE_SHOW_MODIFY 
				&& isset($component->form_fields["billreference"]) 
				&& isset($component->form_fields["shippingreference"])
        		&& isset($component->form_fields["billtoshipping"]) 
        		&& $component->form_fields["billtoshipping"]->value->getValue()
        	) {
				$component->form_fields["shippingreference"]->setValue($component->form_fields["billreference"]->getValue());
				$component->form_fields["shippingaddress"]->setValue($component->form_fields["billaddress"]->getValue());
				$component->form_fields["shippingcap"]->setValue($component->form_fields["billcap"]->getValue());
				$component->form_fields["shippingtown"]->setValue($component->form_fields["billtown"]->getValue());
				$component->form_fields["shippingprovince"]->setValue($component->form_fields["billprovince"]->getValue());
        		if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0)) {
					$component->form_fields["shippingstate"]->setValue($component->form_fields["billstate"]->getValue());
				}
			}
            break;
        case "confirmdelete": 
            break;
    }
}

function UserModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
	$ID_form_node = array();
	$str_vgallery_nodes = "";
	switch($action) {
		case "insert":
		case "update":
			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users 
					SET username_slug = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["username"]->getValue())) . "
					WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($component->key_fields["register-ID"]->value->getValue(), "Number");
			$db->execute($sSQL);

			
			if($component->detail === null) {
				if(isset($component->form_fields["primary_gid"])) {
					$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_rel_groups WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . $db->toSql($component->key_fields["register-ID"]->value->getValue(), "Number");
					$db->execute($sSQL);
					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_rel_groups 
							(
								uid
								, gid
							) VALUES (
								" . $db->toSql($component->key_fields["register-ID"]->value->getValue(), "Number") . "
								, " . $db->toSql($component->form_fields["primary_gid"]->value->getValue(), "Number") . "
							)";
					$db->execute($sSQL);
				}
			}
			
	        if(check_function("ecommerce_set_anagraph_unic_by_user"))
	            ecommerce_set_anagraph_unic_by_user($component->key_fields["register-ID"]->value->getValue(), $component->user_vars["anagraph_type"], null, null);

	        if(isset($component->form_fields["avatar"])) {
	            $user_permission = get_session("user_permission");
	            if(array_key_exists("avatar", $user_permission)) {
	                $user_permission["avatar"] = $component->form_fields["avatar"]->getValue();
	                set_session("user_permission", $user_permission);
	            }
	        }
			break;
	    case "confirmdelete":
	    	$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_rel_groups WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->execute($sSQL);
	    				
			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_fields WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->execute($sSQL);

			$sSQL = "SELECT users_rel_module_form.* FROM users_rel_module_form WHERE users_rel_module_form.uid = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$ID_form_node[] = $db->getField("ID_form_node", "Text", true);
				} while($db->nextRecord());	
			}
			
			if(is_array($ID_form_node) && count($ID_form_node)) {
				$sSQL = "DELETE FROM module_form_rel_nodes_fields WHERE ID_form_nodes IN (" . $db->toSql(implode(",", $ID_form_node), "Text", false) . ")";
				$db->execute($sSQL);

				$sSQL = "DELETE FROM module_form_nodes WHERE ID IN (" . $db->toSql(implode(",", $ID_form_node), "Text", false) . ")";
				$db->execute($sSQL);
			}

			$sSQL = "DELETE FROM users_rel_module_form WHERE users_rel_module_form.uid = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->execute($sSQL);

			
			$sSQL = "SELECT users_rel_vgallery.* 
						, CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
					FROM users_rel_vgallery 
						INNER JOIN vgallery_nodes ON vgallery_nodes.ID = users_rel_vgallery.ID_nodes
					WHERE users_rel_vgallery.uid = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					if(strlen($str_vgallery_nodes))
						$str_vgallery_nodes .= " OR ";
						
					$str_vgallery_nodes .= " vgallery_nodes.parent LIKE '" . $db->getField("full_path", "Text", true) . "%'";
				} while($db->nextRecord());	
			}
			
			$sSQL = "DELETE FROM users_rel_vgallery WHERE users_rel_vgallery.uid = " . $db->toSql($component->key_fields["register-ID"]->value);
			$db->execute($sSQL);

			if(strlen($str_vgallery_nodes)) {
		        $db->query("SELECT vgallery.name AS vgallery_name
		                        , vgallery_nodes.name AS name
		                        , vgallery_nodes.parent AS parent
		                    FROM vgallery_nodes 
		                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
		                    WHERE ( $str_vgallery_nodes )
		                        AND vgallery_nodes.owner = " . $db->toSql($component->key_fields["register-ID"]->value)
		            );
		        if($db->nextRecord()) {
		            do {
			            $vgallery_name = $db->getField("vgallery_name", "Text", true);
                        if(check_function("delete_vgallery"))
			                delete_vgallery($db->getField("parent", "Text", true), $db->getField("name", "Text", true), $vgallery_name);
					} while($db->nextRecord());
		        }
			}
	    break;
	    default:
	}    
}
