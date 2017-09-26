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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

//if(is_array($_POST) && count($_POST))
//	use_cache(false);
$db = ffDB_Sql::factory();

if(check_function("ecommerce_get_schema"))
	$schema_ecommerce = ecommerce_get_schema();	   



$db->query("SELECT module_form.*
                        FROM 
                            module_form
                        WHERE 
                            module_form.name = " . $db->toSql($MD_chk["params"][0]));
if($db->nextRecord()) {
	$framework_css = cm_getFrameworkCss();	

    $ID_form = $db->getField("ID")->getValue();
    $form_name = $db->getField("name")->getValue();
    $form_display_name = $db->getField("display_name")->getValue();
    if(!$form_display_name)
    	$form_display_name = ucwords(str_replace("-", " ", $form_name));

    $force_redirect = $db->getField("force_redirect")->getValue();
    $fixed_pre_content = $db->getField("fixed_pre_content", "Text", true);  
    $fixed_post_content = $db->getField("fixed_post_content", "Text", true); 
    $privacy = $db->getField("privacy")->getValue();
    $require_note = $db->getField("require_note")->getValue();
    $tpl_form_path = $db->getField("tpl_form_path")->getValue();
    $send_mail = $db->getField("send_mail")->getValue();
    $report = $db->getField("report")->getValue();
    $enable_ecommerce = $db->getField("enable_ecommerce")->getValue();
    $enable_ecommerce_weight = $db->getField("enable_ecommerce_weight")->getValue();
    $enable_dynamic_cart = $db->getField("enable_dynamic_cart")->getValue();
    $enable_dynamic_cart_advanced = $db->getField("enable_dynamic_cart_advanced")->getValue();
    $skip_form_cart = $db->getField("skip_form_cart")->getValue();
    $skip_shipping_calc = $db->getField("skip_shipping_calc")->getValue();
    $skip_vat_by_anagraph_type = $db->getField("skip_vat_by_anagraph_type")->getValue();
    $discount_perc = $db->getField("discount_perc", "Number", true);
    $discount_val = $db->getField("discount_val", "Number", true);
    $enable_sum_quantity = $db->getField("enable_sum_quantity")->getValue();
    $reset_cart = $db->getField("reset_cart")->getValue();
    $restore_default_by_cart = $db->getField("restore_default_by_cart", "Number", true);
    $hide_vat = $db->getField("hide_vat", "Number", true);
    $hide_weight = $db->getField("hide_weight", "Number", true);
    
    $fixed_cart_qta = $db->getField("fixed_cart_qta", "Number", true);
    $fixed_cart_price = $db->getField("fixed_cart_price", "Number", true);
    $fixed_cart_vat = $db->getField("fixed_cart_vat", "Number", true);
    $fixed_cart_weight = $db->getField("fixed_cart_weight", "Number", true);
    $decumulation = $db->getField("decumulation", "Text", true);

    $show_title = $db->getField("show_title")->getValue();
    $enable_revision = $db->getField("enable_revision", "Number", true);

    $display_view_mode = $db->getField("display_view_mode")->getValue();

    $enable_dep = $db->getField("field_enable_dep", "Number", true);
    $enable_pricelist = $db->getField("field_enable_pricelist", "Number", true);

    $limit_by_groups = $db->getField("limit_by_groups")->getValue();
    if(strlen($limit_by_groups)) 
    {
        $limit_by_groups = explode(",", $limit_by_groups);
        $user_permission = get_session("user_permission"); 			

        if(count(array_intersect($user_permission["groups"], $limit_by_groups))) {
                $allow_form = true;
        } else {
                $allow_form = false;
                $strErrorForm = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_unable_to_write");
        }
    } else {
        $allow_form = true;
    }
	
    if($send_mail) {
        $ID_email = $db->getField("ID_email", "Number")->getValue();
        $force_to_with_user = $db->getField("force_to_with_user")->getValue();
        $send_copy_to_guest = $db->getField("send_copy_to_guest")->getValue();
        $force_from_with_domclass = $db->getField("force_from_with_domclass")->getValue();
    }
	
	
    $field_default = array(
        "ID_form_fields_group" => $db->getField("field_default_ID_form_fields_group", "Text", true)
        , "ID_extended_type" => $db->getField("field_default_ID_extended_type", "Text", true)
        , "disable_select_one" => $db->getField("field_default_disable_select_one", "Text", true)
        , "disable_free_input" => $db->getField("field_default_disable_free_input", "Text", true)
        , "require" => $db->getField("field_default_require", "Text", true)
        , "hide_label" => $db->getField("field_default_hide_label", "Text", true)
        , "placeholder" => $db->getField("field_default_placeholder", "Text", true)
        , "ID_check_control" => $db->getField("field_default_ID_check_control", "Text", true)
        , "unic_value" => $db->getField("field_default_unic_value", "Text", true)
        , "send_mail" => $db->getField("field_default_send_mail", "Text", true)
        , "enable_in_mail" => $db->getField("field_default_enable_in_mail", "Text", true)
        , "enable_in_grid" => $db->getField("field_default_enable_in_grid", "Text", true)
        , "enable_in_menu" => $db->getField("field_default_enable_in_menu", "Text", true)
        , "enable_in_document" => $db->getField("field_default_enable_in_document", "Text", true)
        , "enable_tip" => $db->getField("field_default_enable_tip", "Text", true)
        , "writable" => $db->getField("field_default_writable", "Text", true)
        , "hide" => $db->getField("field_default_hide", "Text", true)
        , "preload_by_domclass" => $db->getField("field_default_preload_by_domclass", "Text", true)
        , "fixed_pre_content" => $db->getField("field_default_fixed_pre_content", "Text", true)
        , "fixed_post_content" => $db->getField("field_default_fixed_post_content", "Text", true)
        , "preload_by_db" => $db->getField("field_default_preload_by_db", "Text", true)
        , "vgallery_field" => $db->getField("field_default_vgallery_field", "Text", true)
        , "domclass" => $db->getField("field_default_domclass", "Text", true)
        , "custom" => $db->getField("field_default_custom", "Text", true)
        , "val_min" => $db->getField("field_default_val_min", "Text", true)
        , "val_max" => $db->getField("field_default_val_max", "Text", true)
        , "val_step" => $db->getField("field_default_val_step", "Text", true)
        , "show_price_in_label" => $db->getField("field_default_show_price_in_label", "Text", true)
    );
	
    foreach($field_default AS $field_default_key => $field_default_value) {
        $field_default[$field_default_key] = (strpos($field_default_value, "show_no") === false
                                                    ? null
                                                    : (strpos($field_default_value, "default") === false
                                                            ? false
                                                            : str_replace("show_no_default_", "", $field_default_value)
                                                    )
                                            );
    }
}

$db->query("SELECT module_form_fields.*
                        , extended_type.name AS extended_type
                        , extended_type.ff_name AS ff_extended_type
                        , check_control.ff_name AS check_control
                        , module_form_fields_group.name AS `group_field`
                        , module_form_fields_group.cover AS `group_cover`
                        , " . CM_TABLE_PREFIX . "showfiles_modes.name AS `group_cover_mode`
                        , IF(module_form_fields.`type` = ''
                            , sum_price_from
                            , ''
                        ) AS sum_from
                        , IF(module_form_fields.`type` = ''
                            , ''
                            , sum_price_from
                        ) AS sum_pfrom
                    FROM 
                        module_form_fields
                        LEFT JOIN extended_type ON extended_type.ID = " . ($field_default["ID_extended_type"] === null ? " module_form_fields.ID_extended_type " : $db->toSql($field_default["ID_extended_type"], "Number")) . "
                        LEFT JOIN check_control ON check_control.ID = " . ($field_default["ID_extended_type"] === null ? " module_form_fields.ID_check_control " : $db->toSql($field_default["ID_check_control"], "Number")) . " 
                        LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = " . ($field_default["ID_extended_type"] === null ? " module_form_fields.ID_form_fields_group " : $db->toSql($field_default["ID_form_fields_group"], "Number")) . " 
                        LEFT JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields.ID_selection
                        LEFT JOIN " . CM_TABLE_PREFIX . "showfiles_modes ON " . CM_TABLE_PREFIX . "showfiles_modes.ID = module_form_fields_group.cover_mode
                    WHERE module_form_fields.ID_module = " . $db->toSql(new ffData($ID_form, "Number")) . "
                    ORDER BY module_form_fields.`order`, module_form_fields.name");
if($db->nextRecord()) {
    if($tpl_form_path && file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_form_path)) {
        $oRecord->template_dir = ffCommon_dirname(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_form_path);
        $oRecord->template_file = basename($tpl_form_path);
    }
	$oRecord = ffRecord::factory($cm->oPage);
    $oRecord->id = $MD_chk["id"];
    $oRecord->class = $MD_chk["id"];
    $oRecord->src_table = ""; 
    $oRecord->use_own_location = $MD_chk["own_location"]; 
    
    if($show_title) {
        $oRecord->title =  ffTemplate::_get_word_by_code($MD_chk["tag"] . "_title"); 
    }
	
    if(check_function("MD_form_on_done_action")) { //if(check_function("MD_form_on_check_after")) if(check_function("MD_form_on_do_action"))
        $oRecord->addEvent("on_check_after", "MD_form_on_check_after");
        $oRecord->addEvent("on_do_action", "MD_form_on_do_action");
        $oRecord->addEvent("on_done_action", "MD_form_on_done_action");
    }

    if($force_redirect)
        $oRecord->ret_url = $force_redirect;
    else 
        $oRecord->ret_url = FF_SITE_PATH . VG_SITE_NOTIFY . "/form/end/" . $form_name;

    if($require_note)
        $oRecord->display_required_note = true;
    else 
        $oRecord->display_required_note = false;
        
    if($strErrorForm) {
    	$oRecord->fixed_pre_content = $strErrorForm;
    } else 
    {
        if(strlen($fixed_pre_content)>1)
            $oRecord->fixed_pre_content = $fixed_pre_content;

        if(strlen($fixed_post_content)>1)
            $oRecord->fixed_post_content = $fixed_post_content;

        if(isset($_REQUEST["success"]) && !isset($_REQUEST["frmAction"])) {
            $oRecord->fixed_pre_content = $oRecord->fixed_pre_content . ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_success"); 
        }
    }
    $oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = false;
    $oRecord->buttons_options["update"]["display"] = false;
    $oRecord->buttons_options["print"]["display"] = false;
    $oRecord->disable_mod_notifier_on_error = true;

    // nuove variabili 
    $oRecord->user_vars["send_mail"] = $send_mail;
    $oRecord->user_vars["ID_email"] = $ID_email;
    $oRecord->user_vars["force_to_with_user"] = $force_to_with_user;
    $oRecord->user_vars["send_copy_to_guest"] = $send_copy_to_guest;
    $oRecord->user_vars["force_from_with_domclass"] = $force_from_with_domclass;

    $oRecord->user_vars["form_name"] = $form_name;
    $oRecord->user_vars["form_display_name"] = $form_display_name;
    $oRecord->user_vars["form_title"] = $MD_chk["title"];
    $oRecord->user_vars["report"] = $report;
    
    if(AREA_SHOW_ECOMMERCE) {
        $oRecord->user_vars["enable_ecommerce"] = $enable_ecommerce;
        $oRecord->user_vars["enable_ecommerce_weight"] = $enable_ecommerce_weight;
        $oRecord->user_vars["enable_dynamic_cart"] = $enable_dynamic_cart;
        $oRecord->user_vars["enable_dynamic_cart_advanced"] = $enable_dynamic_cart_advanced;
        $oRecord->user_vars["skip_form_cart"] = $skip_form_cart;
        $oRecord->user_vars["skip_shipping_calc"] = $skip_shipping_calc;
        $oRecord->user_vars["skip_vat_by_anagraph_type"] = $skip_vat_by_anagraph_type;
        
        $oRecord->user_vars["discount"]["perc"] = $discount_perc;
        $oRecord->user_vars["discount"]["val"] = $discount_val;
        $oRecord->user_vars["enable_sum_quantity"] = $enable_sum_quantity;
        $oRecord->user_vars["reset_cart"] = $reset_cart;
        $oRecord->user_vars["restore_default_by_cart"] = $restore_default_by_cart;

        $oRecord->user_vars["hide_vat"] = $hide_vat;
        $oRecord->user_vars["hide_weight"] = $hide_weight;
        
        $oRecord->user_vars["fixed_cart"]["qta"] = ($fixed_cart_qta > 0 ? $fixed_cart_qta : 1);
        $oRecord->user_vars["fixed_cart"]["price"] = $fixed_cart_price;
        $oRecord->user_vars["fixed_cart"]["vat"] = $fixed_cart_vat;
        $oRecord->user_vars["fixed_cart"]["weight"] = $fixed_cart_weight;
        $oRecord->user_vars["fixed_cart"]["decumulation"] = $decumulation;
	    
    }
    $oRecord->user_vars["enable_revision"] = $enable_revision;
     
    $oField = ffField::factory($cm->oPage);
    $oField->id = "form-ID";
    $oField->base_type = "Number";
    $oField->auto_key = false;
    $oField->default_value = new ffData($ID_form, "Number");
    $oRecord->addKeyField($oField);
    
    if(!$allow_form) {
    	$oRecord->buttons_options["insert"]["display"] = false;
    } else {
       /* if(strlen($force_from_with_domclass)) {
            $oRecord->addHiddenField("ffm", new ffData(""));

            $addit_js = "jQuery('#" . $MD_chk["id"] . "_ffm').val(jQuery('." . $force_from_with_domclass . "').text()); ";
            ///DA COMPLETARE E PROPAGARE PER OGNI FILE CHE USA QUESTO
        } else {
            $addit_js = "";
        }*/
           
       // if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" || $MD_chk["ajax"]) {
                $oButton = ffButton::factory($cm->oPage);
                $oButton->id = "insert";
                //$oButton->class = "noactivebuttons";
                $oButton->action_type = "gotourl";
                $oButton->frmAction = "insert";
                //$oButton->url = $_REQUEST["ret_url"];
                $oButton->aspect = "link";
                //$oButton->image = "preview.png";
				$oButton->url = "javascript:void(0)";
				$oButton->properties["onclick"] =  "ff.cms.form.insert('" . $MD_chk["id"] . "');";
				$oButton->label = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_insert");
                $oRecord->buttons_options["insert"]["obj"] = $oButton;
       /* } else {
            $oButton = ffButton::factory($cm->oPage);
            $oButton->id = "insert";
            $oButton->action_type = "submit";
            $oButton->frmAction = "insert";
            //$oButton->url = $_REQUEST["ret_url"];
            $oButton->aspect = "link";
            //$oButton->image = "preview.png";
            $oButton->jsaction = $addit_js . " document.getElementById('frmAction').value = '" . $MD_chk["id"] . "_insert'; jQuery(this).closest('form').submit();"; 
            if($MD_chk["button_label"])
                $oButton->label = $MD_chk["button_label"];
            else
                $oButton->label = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_insert");

            $oRecord->buttons_options["insert"]["obj"] = $oButton;
//            $oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $form_name) . "_insert");
        }*/



        $enable_dynamic_label = false;
        $sum_pfrom = array();
        $sum_from = array();
        $arrDep = array(
                "source" => array()
                , "str" => ""
                , "js" => array()
        );
        $arrPricelist = array();
        $arrPricelistField = array();
        $arrJsPricelist = array();
        if($discount_perc || $discount_val) {
                $arrDiscount = array(
                        "perc" => $discount_perc
                        , "val" => $discount_val
                );
        }
        do {
            $ID_field = $db->getField("ID", "Number", true);
            $is_hidden = ($field_default["hide"] === null ? $db->getField("hide")->getValue() : $field_default["hide"]);
            if($is_hidden)
                continue;

            if($enable_dep) {
                $arrDep["source"][$ID_field]["ID_field"] = $ID_field;
                $arrDep["source"][$ID_field]["name"] = $db->getField("name", "Text", true);
                $arrDep["source"][$ID_field]["form_name"] = $MD_chk["id"];

                if(strlen($arrDep["str"]))
                    $arrDep["str"] .= ",";

                $arrDep["str"] .= $db->toSql($ID_field, "Number");
            }
            $field[$ID_field]["form"]["name"] = $form_name;
            $field[$ID_field]["form"]["ID"] = $MD_chk["id"];
            $field[$ID_field]["form"]["params"] = $MD_chk["params"];
            $field[$ID_field]["form"]["enable_dynamic_cart"] = $enable_dynamic_cart;
            $field[$ID_field]["form"]["enable_dynamic_cart_advanced"] = $enable_dynamic_cart_advanced;
            $field[$ID_field]["form"]["enable_ecommerce"] = $enable_ecommerce;
            $field[$ID_field]["form"]["enable_ecommerce_weight"] = $enable_ecommerce_weight;
            $field[$ID_field]["type"] = $db->getField("type")->getValue();
            $field[$ID_field]["file_allowed_ext"] = $db->getField("file_allowed_ext")->getValue();
            $field[$ID_field]["ID"] = $db->getField("ID")->getValue();
            $field[$ID_field]["name"] = $db->getField("name")->getValue();
            if($db->getField("group_field")->getValue()) {
                $field[$ID_field]["group_cover"] = "";
                $field[$ID_field]["group"] = preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("group_field")->getValue());
                if(strlen($db->getField("group_cover")->getValue())) {
                    if(strlen($db->getField("group_cover_mode")->getValue())) {
                        $field[$ID_field]["group_cover"] = CM_SHOWFILES . "/" . $db->getField("group_cover_mode")->getValue() . $db->getField("group_cover")->getValue();
                    } else {
                        $field[$ID_field]["group_cover"] = SITE_UPDIR . $db->getField("group_cover")->getValue();
                    }

                    $field[$ID_field]["group_cover"] = '<img src="' . $field[$ID_field]["group_cover"] . '" />';
                }
            } else {
                $field[$ID_field]["group"] = null;
            }

            $field[$ID_field]["custom"]["name"] = ($field_default["custom"] === null ? $db->getField("custom")->getValue() : $field_default["custom"]);
            $field[$ID_field]["custom"]["class"] = ($field_default["domclass"] === null ? $db->getField("domclass")->getValue() : $field_default["domclass"]);
            $field[$ID_field]["ID_selection"] = $db->getField("ID_selection", "Number", true);
            $field[$ID_field]["ID_vgallery_field"] = $db->getField("ID_vgallery_field", "Number", true);

            if(strlen($field[$ID_field]["custom"]["name"])) {
                $field[$ID_field]["extended_type"] = "Text";
                $field[$ID_field]["ff_extended_type"] = "Text";
            } else {
                $field[$ID_field]["extended_type"] = $db->getField("extended_type")->getValue();
                $field[$ID_field]["ff_extended_type"] = $db->getField("ff_extended_type", "Text", true);
            }
            $field[$ID_field]["send_mail"] = ($field_default["send_mail"] === null 
            	? ($db->getField("send_mail")->getValue() && $db->getField("check_control")->getValue() == "email" 
            		? true
            		: false
            	)
            	: ($field_default["send_mail"] && $db->getField("check_control")->getValue() == "email" 
            		? true 
            		: false
            	)
            );

            $field[$ID_field]["enable_in_mail"] = ($field_default["enable_in_mail"] === null ? $db->getField("enable_in_mail", "Number", true) : $field_default["enable_in_mail"]);
            $field[$ID_field]["unic_value"] = ($field_default["unic_value"] === null ? $db->getField("unic_value", "Number", true) : $field_default["unic_value"]);
            $field[$ID_field]["enable_tip"] = ($field_default["enable_tip"] === null ? $db->getField("enable_tip", "Number", true) : $field_default["enable_tip"]);
            $field[$ID_field]["writable"] = ($field_default["writable"] === null ? $db->getField("writable", "Number", true) : $field_default["writable"]);
            $field[$ID_field]["preload_by_db"] = ($field_default["preload_by_db"] === null ? $db->getField("preload_by_db")->getValue() : $field_default["preload_by_db"]);
            $field[$ID_field]["require"] = ($field_default["require"] === null ? $db->getField("require", "Number", true) : $field_default["require"]);
            $field[$ID_field]["display_label"] = ($field_default["hide_label"] === null ? !$db->getField("hide_label", "Number", true) : !$field_default["hide_label"]);
            if(is_array($framework_css))
            {
                $field[$ID_field]["framework_css"]["component"] = array(
                        $db->getField("default_grid", "Number", true)
                        , $db->getField("grid_md", "Number", true)
                        , $db->getField("grid_sm", "Number", true)
                        , $db->getField("grid_xs", "Number", true)
                );

                if($field[$ID_field]["display_label"]) {
                        $field[$ID_field]["framework_css"]["label"] = array(
                                $db->getField("label_default_grid", "Number", true)
                                , $db->getField("label_grid_md", "Number", true)
                                , $db->getField("label_grid_sm", "Number", true)
                                , $db->getField("label_grid_xs", "Number", true)
                        );
                }

            }

            $field[$ID_field]["placeholder"] = ($field_default["placeholder"] === null ? $db->getField("placeholder", "Number", true) : $field_default["placeholder"]);
            if(strlen($db->getField("custom_placeholder", "Text", true)))
                $field[$ID_field]["placeholder"] = ffTemplate::_get_word_by_code($db->getField("custom_placeholder", "Text", true));

            $field[$ID_field]["check_control"] = $db->getField("check_control")->getValue();
            $field[$ID_field]["preload_by_domclass"] = ($field_default["preload_by_domclass"] === null ? $db->getField("preload_by_domclass")->getValue() : $field_default["preload_by_domclass"]);
            $field[$ID_field]["fixed_pre_content"] = ($field_default["fixed_pre_content"] === null ? $db->getField("fixed_pre_content")->getValue() : $field_default["fixed_pre_content"]);
            $field[$ID_field]["fixed_post_content"] = ($field_default["fixed_post_content"] === null ? $db->getField("fixed_post_content")->getValue() : $field_default["fixed_post_content"]);
            $field[$ID_field]["disable_select_one"] = ($field_default["disable_select_one"] === null ? $db->getField("disable_select_one")->getValue() : $field_default["disable_select_one"]);
            $field[$ID_field]["disable_free_input"] = ($field_default["disable_free_input"] === null ? $db->getField("disable_free_input")->getValue() : $field_default["disable_free_input"]);
            $field[$ID_field]["show_price_in_label"] = ($field_default["show_price_in_label"] === null ? $db->getField("show_price_in_label")->getValue() : $field_default["show_price_in_label"]);

            $field[$ID_field]["val_min"] = ($field_default["val_min"] === null ? $db->getField("val_min")->getValue() : $field_default["val_min"]);
            $field[$ID_field]["val_max"] = ($field_default["val_max"] === null ? $db->getField("val_max")->getValue() : $field_default["val_max"]);
            $field[$ID_field]["val_step"] = ($field_default["val_step"] === null ? $db->getField("val_step")->getValue() : $field_default["val_step"]);
            $field[$ID_field]["properties"] = array();

            $field[$ID_field]["sum_from"] = $db->getField("sum_from", "Text", true);
            $field[$ID_field]["sum_pfrom"] = $db->getField("sum_pfrom", "Text", true);

            $field[$ID_field]["label_ecommerce"] = "";
            $field[$ID_field]["ecommerce_class"] = "";
            $field[$ID_field]["price_isset"] = false;
            if(AREA_SHOW_ECOMMERCE 
                && $field[$ID_field]["form"]["enable_ecommerce"]
            ) {
                if($field[$ID_field]["type"] == "price")
                {
                    if($field[$ID_field]["extended_type"] != "Selection" 
                        && $field[$ID_field]["extended_type"] != "Group" 
                        && $field[$ID_field]["extended_type"] != "Option"
                    ) {
                        $field[$ID_field]["price_isset"] = true;
                        //$obj_page_field->user_vars["qta"] = ($db->getField("qta", "Number", true) > 0 ? $db->getField("qta", "Number", true) : 1); 
                        $field[$ID_field]["price"] = $db->getField("price", "Number", true); 
                        $field[$ID_field]["vat"] = $db->getField("vat", "Number", true); 
                        $field[$ID_field]["weight"] = $db->getField("weight", "Number", true); 
                        $field[$ID_field]["qta"] = $db->getField("qta", "Number", true);

                        if($field[$ID_field]["show_price_in_label"]) {
                            $field[$ID_field]["label_ecommerce"] = ' <span class="form-price">' . $db->getField("price", "Number")->getValue("Currency", LANGUAGE_INSET) . "</span> " . $schema_ecommerce["symbol"];
                            $field[$ID_field]["ecommerce_class"] = 'dynamic-label';
                            $enable_dynamic_label = true;
                        }
                        if($field[$ID_field]["form"]["enable_dynamic_cart"]) {
                            $field[$ID_field]["properties"]["data-price"] = $db->getField("price", "Number", true);
                            if($enable_ecommerce_weight)
                                $field[$ID_field]["properties"]["data-weight"] = $db->getField("weight", "Number", true);
                        }					
                    } 

                    if($field[$ID_field]["form"]["enable_dynamic_cart"]) {
                            if($enable_dynamic_label)
                                    $field[$ID_field]["ecommerce_class"] .= ' ';

                            $field[$ID_field]["ecommerce_class"] .= 'dynamic-price';
                    }	
                }
                elseif($field[$ID_field]["type"] == "multiplier") 
                {
                    if($field[$ID_field]["disable_free_input"]) {
                        $field[$ID_field]["extended_type"] = "Selection";
                        $field[$ID_field]["ff_extended_type"] = "Text";
                    } else {
                        $field[$ID_field]["extended_type"] = "Number";
                        $field[$ID_field]["ff_extended_type"] = "Number";
                    }

                    if($field[$ID_field]["form"]["enable_dynamic_cart"]) {
                        $field[$ID_field]["ecommerce_class"] .= 'dynamic-qta';
                    }            		
                }
                elseif($field[$ID_field]["type"] == "pricelist") 
                {

                    if($field[$ID_field]["form"]["enable_dynamic_cart"]) {
                        $field[$ID_field]["ecommerce_class"] .= 'dynamic-pricelist';
                    }
                    $arrPricelistField[$ID_field] = $field[$ID_field];
                                //echo "2-" . $field[$ID_field]["ecommerce_class"] . " ";
                } elseif(!strlen($field[$ID_field]["type"]) && $enable_dep) {
                        $field[$ID_field]["ecommerce_class"] .= 'dynamic-dep';
                } 

                if($field[$ID_field]["sum_from"]) {
                    $sum_from = array_merge($sum_from, explode(",", $field[$ID_field]["sum_from"]));
                }

                if($field[$ID_field]["sum_pfrom"]) {
                    $sum_pfrom = array_merge($sum_pfrom, explode(",", $field[$ID_field]["sum_pfrom"]));
                }
            }       
        } while($db->nextRecord());

        if($restore_default_by_cart) {
            use_cache(false);
            $add_cond = " AND ecommerce_order.owner = " . $db->toSql(get_session("UserNID"), "Number");

            if (get_session("UserID") == MOD_SEC_GUEST_USER_NAME && check_function("ecommerce_cart_get_sid")) {
                $add_cond .= " AND ecommerce_order.SID = " . $db->toSql(ecommerce_cart_get_sid());
            } else {
                if(ENABLE_MULTICART) {
                    $cart_name = get_session("cart_name");
                    $add_cond .= " AND ecommerce_order.cart_name = " . $db->toSql($cart_name);
                }
            }		
            $db->query("SELECT module_form_rel_nodes_fields.*
                                                    FROM module_form_rel_nodes_fields
                        INNER JOIN module_form_nodes ON module_form_nodes.ID = module_form_rel_nodes_fields.ID_form_nodes
                                                    WHERE module_form_nodes.ID_module = " . $db->toSql($ID_form, "Number") . "
                        AND module_form_rel_nodes_fields.ID_form_nodes IN (
                                                            SELECT ecommerce_order_detail.ID_items
                                    FROM ecommerce_order_detail
                                        INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
                                    WHERE 
                                        ecommerce_order_detail.source_url = " . $db->toSql($MD_chk["ret_url"]) . "
                                        AND ecommerce_order_detail.tbl_src = ''
                                        AND ecommerce_order_detail.ID_items > 0
                                        AND ecommerce_order.is_cart > 0
                                        $add_cond
                                )");
            if($db->nextRecord()) {  
                $oRecord->user_vars["ID_form_node"] =  $db->getField("ID_form_nodes", "Number", true); 
                do {
                        $ID_field = $db->getField("ID_form_fields", "Number", true);
                        if(array_key_exists($ID_field, $field)) {
                                $field[$ID_field]["default_value"] = $db->getField("value", "Text", true);
                        }
                } while($db->nextRecord());				
            }		
        }

        if($enable_dep && is_array($arrDep["source"]) && count($arrDep["source"]))
        {
            $sSQL = "SELECT module_form_dep.*
                        , module_form_fields.name AS module_field_name
                        , tbl_dep.name AS module_dep_field_selection_name
                        , tbl_src.name AS module_src_field_selection_name
                        FROM module_form_dep
                            LEFT JOIN module_form_fields ON module_form_fields.ID = module_form_dep.dep_fields
                            LEFT JOIN module_form_fields_selection_value AS tbl_dep ON tbl_dep.ID = module_form_dep.dep_selection_value
                            LEFT JOIN module_form_fields_selection_value AS tbl_src ON tbl_src.ID = module_form_dep.ID_selection_value
                        WHERE module_form_dep.ID_form_fields IN (" . $arrDep["str"] . ")
                                AND module_form_dep.ID_module = " . $db->toSql($ID_form, "Number") . "
                        ORDER BY module_field_name, module_form_dep.ID";
            $db->query($sSQL); 
            if($db->nextRecord()) { 
                do {
                    $ID_form_field = $db->getField("ID_form_fields", "Number", true);
                    $form_field_name = "form-" . $arrDep["source"][$ID_form_field]["name"];
                    $ID_form_name = $arrDep["source"][$ID_form_field]["form_name"];
                    $ID_selection_value = $db->getField("ID_selection_value", "Number", true);
                    if(strlen($ID_form_name))
                    {
                        $ID_dep_fields = $db->getField("dep_fields", "Number", true);
                        $ID_dep_fields_name = $db->getField("module_field_name", "Text", true);
                        $ID_dep_fields_selection_value = $db->getField("dep_selection_value", "Number", true);
                        $ID_dep_fields_selection_value_name = $db->getField("module_dep_field_selection_name", "Text", true);
                        $ID_src_fields_selection_value_name = $db->getField("module_src_field_selection_name", "Text", true);
                        $operator = (strlen($db->getField("operator", "Text", true)) ? $db->getField("operator", "Text", true) : "==");
                        $value = $db->getField("value", "Text", true);
                        if($ID_selection_value)
                        {
                            $ID_form_field = $ID_form_field . ":" .  $ID_src_fields_selection_value_name;
                        }
                        if($ID_dep_fields_selection_value) {
                            if(strlen($ID_dep_fields_selection_value_name)) {
                                $arrDep["js"][$ID_form_name][$ID_form_field][$ID_dep_fields]["val"][] = array("op" => $operator , "limit" => $ID_src_fields_selection_value_name, "data" => $ID_dep_fields_selection_value_name);
                            } else {
                                $arrDep["js"][$ID_form_name][$ID_form_field][$ID_dep_fields]["val"][] = false;
                            }
                        } else {
                            if(strlen($value)) {
                                $arrDep["js"][$ID_form_name][$ID_form_field][$ID_dep_fields]["val"][] = array("op" => $operator , "data" => $value);
                            } else {
                                $arrDep["js"][$ID_form_name][$ID_form_field][$ID_dep_fields]["val"][] = false;
                            }
                        }
                    }
                } while ($db->nextRecord());
            }
                    //print_r($arrDep["js"]);

        }

        if($enable_pricelist) {
            $sSQL_pricelist_where = "";
            $arrPricelistWhere = array();
            $count_empty_hc = 0;
            $count_empty_hca = 0;
            if(isset($_REQUEST["ph"])) {
                $ph = json_decode($_REQUEST["ph"], true);

                if(is_array($ph["hc"]) && count($ph["hc"])) {
                    foreach($ph["hc"] AS $ID_form_field => $value) {
                        if(!$value)
                            $count_empty_hc++;

                        $arrPricelistWhere["hc"][] = " ( module_form_fields.ID = " . $db->toSql($ID_form_field, "Number") . " AND module_form_pricelist_detail.value = " . $db->toSql($value) . " ) ";
                    }
                    $sSQL_pricelist_where = "( " . implode("OR", $arrPricelistWhere["hc"]) . " )";
                }
                if(is_array($ph["hca"]) && count($ph["hca"])) {
                    foreach($ph["hca"] AS $ID_form_field => $value) {
                        if(!$value)
                            $count_empty_hca++;

                        $arrPricelistWhere["hca"][] = " ( module_form_fields.ID = " . $db->toSql($ID_form_field, "Number") . " AND module_form_pricelist_detail.value = " . $db->toSql($value) . " ) ";
                    }
                    $sSQL_pricelist_where = $sSQL_pricelist_where . " OR ( " . implode("OR", $arrPricelistWhere["hca"]) . " )";
                }
                if(strlen($sSQL_pricelist_where))
                    $sSQL_pricelist_where = " AND ( " . $sSQL_pricelist_where . " )";
            } 

            $sSQL = "SELECT module_form_pricelist_detail.*
            			, module_form_pricelist.price AS price
            			, module_form_pricelist.weight AS weight
            			, module_form_fields.enable_in_document AS enable_in_documents
            			, (IF(module_form_pricelist_detail.value <> '' AND module_form_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'select')
            				, IFNULL(
            					(SELECT COUNT(module_form_fields_selection_value.ID) 
            					FROM module_form_fields_selection_value
            					WHERE module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
            						AND module_form_fields_selection_value.name = module_form_pricelist_detail.value
            				), 0)
            				, 1
            			)) AS `check`
                    FROM module_form_pricelist_detail
                        INNER JOIN module_form_pricelist ON module_form_pricelist.ID = module_form_pricelist_detail.ID_form_pricelist
                        INNER JOIN module_form_fields ON module_form_fields.ID = module_form_pricelist_detail.ID_form_fields
                    WHERE module_form_pricelist.ID_module = " . $db->toSql($ID_form, "Number") . "
                        $sSQL_pricelist_where
                    ORDER BY module_form_fields.`order`, module_form_fields.name";
            $db->query($sSQL); 
            if($db->nextRecord()) { 
                $arrPricelistBanned = array();
                $arrTmpPricelist = array();
                do {
                    $ID_pricelist = $db->getField("ID_form_pricelist", "Number", true);
                    if($db->getField("check", "Number", true) && array_search($ID_pricelist, $arrPricelistBanned) === false) {
                        if(strlen($db->getField("value", "Text", true)) && strpos(";", $db->getField("value", "Text", true)) !== false) {
                            $arrTmpValue = explode(";", $db->getField("value", "Text", true));
                            foreach($arrTmpValue AS $real_value) {
                                if(isset($arrTmpPricelist[$ID_pricelist]["count_key"]))
                                    $arrTmpPricelist[$ID_pricelist]["count_key"]++;
                                else
                                    $arrTmpPricelist[$ID_pricelist]["count_key"] = 0;

                				$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["fields"]["f-" . $db->getField("ID_form_fields", "Number", true)] = $real_value;
                				$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["price"] = $db->getField("price", "Number", true);
                				$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["weight"] = $db->getField("weight", "Number", true);
                				if($db->getField("enable_in_documents", "Number", true) > 0)
                					$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["qta"] = $db->getField("value", "Text", true);
                				
                				$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["key"][] = $db->getField("ID_form_fields", "Number", true) . "=" . $real_value;
                				if(!strlen($real_value))
                					$arrTmpPricelist[$ID_pricelist . "-" . $arrTmpPricelist[$ID_pricelist]["count_key"]]["count_empty"]++;
                				//$reversePricelistKey[$db->getField("ID_form_fields", "Number", true) . "=" . $real_value] = $ID_pricelist;
							}
						} else {
                				$arrTmpPricelist[$ID_pricelist]["fields"]["f-" . $db->getField("ID_form_fields", "Number", true)] = $db->getField("value", "Text", true);
                				$arrTmpPricelist[$ID_pricelist]["price"] = $db->getField("price", "Number", true);
                				$arrTmpPricelist[$ID_pricelist]["weight"] = $db->getField("weight", "Number", true);
                				if($db->getField("enable_in_documents", "Number", true) > 0)
                					$arrTmpPricelist[$ID_pricelist]["qta"] = $db->getField("value", "Text", true);
                				
                            $arrTmpPricelist[$ID_pricelist]["key"][] = $db->getField("ID_form_fields", "Number", true) . "=" . $db->getField("value", "Text", true);
                            if(!strlen($db->getField("value", "Text", true)))
                                    $arrTmpPricelist[$ID_pricelist]["count_empty"]++;
                        }
                    } else {
                        $arrPricelistBanned[] = $ID_pricelist;
                        if(array_key_exists($ID_pricelist, $arrTmpPricelist)) {
                            if(array_key_exists("count_key", $arrTmpPricelist[$ID_pricelist])) {
                                for($i=0; $i<= $arrTmpPricelist[$ID_pricelist]["count_key"]; $i++) {
                                    if(array_key_exists($ID_pricelist . "-" . $i, $arrTmpPricelist))
                                        unset($arrTmpPricelist[$ID_pricelist . "-" . $i]);
                                }
                            }
                            unset($arrTmpPricelist[$ID_pricelist]);
                        }
                    }
                } while($db->nextRecord());

                if(is_array($arrTmpPricelist) && count($arrTmpPricelist))	{
                    foreach($arrTmpPricelist AS $tmp_pricelist_key => $pricelist_rule) {
                        if(isset($pricelist_rule["key"])) {
                            if(count($pricelist_rule["fields"]) == $pricelist_rule["count_empty"])
                                continue;

                            $pricelist_key = implode(":", $pricelist_rule["key"]);

                            if(!array_key_exists($pricelist_key, $arrJsPricelist)) {
                                if(isset($_REQUEST["ph"])
                                    && (count($arrPricelistField) - $count_empty_hc) != (count($pricelist_rule["fields"]) )
                                    && (count($arrPricelistField) - $count_empty_hca) != (count($pricelist_rule["fields"]) )
                                    && (count($arrPricelistField)) != (count($pricelist_rule["fields"]) )
                                    && (count($arrPricelistField)) != (count($pricelist_rule["fields"]) )
                                    && (count($arrPricelistField) ) != (count($pricelist_rule["fields"]) + $pricelist_rule["count_empty"])
                                    && (count($arrPricelistField) ) != (count($pricelist_rule["fields"]) + $pricelist_rule["count_empty"])

//                                    && (count($arrPricelistField) - $count_empty_hc) != count($pricelist_rule["fields"])
//                                    && (count($arrPricelistField) - $count_empty_hca) != count($pricelist_rule["fields"])
                                ) { 
                                    continue;
                                }
                                    

                                $arrJsPricelist[$pricelist_key]["fields"] = $pricelist_rule["fields"];
                                $arrJsPricelist[$pricelist_key]["price"] = $pricelist_rule["price"];

                                if($enable_ecommerce_weight)
                                    $arrJsPricelist[$pricelist_key]["weight"] = $pricelist_rule["weight"];

                                $arrPricelist[$pricelist_key] = $arrJsPricelist[$pricelist_key];
                                $arrPricelist[$pricelist_key]["ID"] = $tmp_pricelist_key;
                            }
                        }
                    }
                }

                $oRecord->user_vars["pricelist"] = $arrPricelist;
            }
            if(isset($_REQUEST["ph"])) {
                echo ffCommon_jsonenc($arrJsPricelist, true);
                exit;
            }
        }

        if(is_array($field) && count($field)) {
            check_function("get_field_by_extension");
            foreach($field AS $field_key => $field_value) {
                if (strlen($field_value["group"]) && !isset($oRecord->groups[$field_value["group"]])) { 
                    $oRecord->addContent(null, true, $field_value["group"]); 
                    $oRecord->groups[$field_value["group"]] = array(
                                                                 "title" => $field_value["group_cover"] . ffTemplate::_get_word_by_code("form_" . $field_value["group"])
                                                                 , "cols" => 1
                                                              );
                }

                $arrSumPriceFrom = array();
                if($field_value["sum_pfrom"]) {
                    $arrSumPriceFrom = explode(",", $field_value["sum_pfrom"]);
                    if(is_array($arrSumPriceFrom) && count($arrSumPriceFrom)) {
                        foreach($arrSumPriceFrom AS $arrSumPriceFrom_key => $arrSumPriceFrom_value) {
                            if(array_key_exists($arrSumPriceFrom_value, $field)) {
                                if(strlen($field_value["properties"]["data-pfrom"]))
                                    $field_value["properties"]["data-pfrom"] .= "|";

                                    $field_value["properties"]["data-pfrom"] .= $arrSumPriceFrom_value;
                            }
                        }
                    }
                }	
                $arrSumFrom = array();
                if($field_value["sum_from"]) { 
                    $arrSumFrom = explode(",", $field_value["sum_from"]);
                    if(is_array($arrSumFrom) && count($arrSumFrom)) {
                        foreach($arrSumFrom AS $arrSumFrom_key => $arrSumFrom_value) {
                            if(array_key_exists($arrSumFrom_value, $field)) {
                                if(strlen($field_value["properties"]["data-from"]))
                                    $field_value["properties"]["data-from"] .= "|";

                                $field_value["properties"]["data-from"] .= $arrSumFrom_value;
                            }
                        }
                    }
                }
//$obj_page_field->class = ($field_value["ecommerce_class"] == "dynamic-price" && array_search($field_value["ID"], $sum_pfrom) !== false ? "dynamic-pfrom" : $field_value["ecommerce_class"]);

                $obj_page_field = ffField::factory($cm->oPage);

                    /*
                            $obj_page_field->class = $field_value["ecommerce_class"];
                    if($field_value["ecommerce_class"] == "dynamic-price") {

                            if(array_search($field_value["ID"], $sum_pfrom) !== false) {
                                    $obj_page_field->class = "dynamic-pfrom";
                            } else {
                                    $obj_page_field->class = $field_value["ecommerce_class"];
                            }
                    } elseif(strlen($field_value["sum_from"])) {
                            $obj_page_field->class = "dynamic-value";

                            }	
                             */
                $obj_page_field->encode_label = false; 
                $obj_page_field->user_vars["sum_price_from"] = $arrSumPriceFrom;

                $js .= get_field_by_extension($obj_page_field, $field_value, "form");

                if(strlen($field_value["preload_by_domclass"])) {
                    $js .= '
                        jQuery("#' . $field_value["form"]["ID"]  . '_' . $field_value["ID"] . '").val(ff.decodeEntities(jQuery(".' . $field_value["preload_by_domclass"] . '").text())); 
                        ' . ($field_value["writable"]
                                ? ''
                                : ' jQuery("#' . $field_value["form"]["ID"]  . '_' . $field_value["ID"] . '_label").text(ff.decodeEntities(jQuery(".' . $field_value["preload_by_domclass"] . '").text()));'
                        );
                }

                $oRecord->addContent($obj_page_field, $field_value["group"]);
            }
        } 

        if($privacy) {
            $oRecord->addContent(null, true, "privacy"); 
            $oRecord->groups["privacy"] = array(
                                            "title" => ffTemplate::_get_word_by_code("form_privacy")
                                            , "cols" => 1
                                         );

            $obj_page_field = ffField::factory($cm->oPage);
            $obj_page_field->id = "privacy_text";
            $obj_page_field->container_class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_text");
            $obj_page_field->label = "";
            $obj_page_field->display_label = false;
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "textarea";
            $obj_page_field->default_value = new ffData(ffTemplate::_get_word_by_code("form_privacy_text_" . $MD_chk["params"][0]), "Text");
            $obj_page_field->properties["readonly"] = "readonly";
            $oRecord->addContent($obj_page_field, "privacy");

            $obj_page_field = ffField::factory($cm->oPage);
            $obj_page_field->id = "privacy_check";
            $obj_page_field->container_class = "form_" . preg_replace('/[^a-zA-Z0-9]/', '', "privacy_check");
            $obj_page_field->label = "";
            $obj_page_field->display_label = false;
            $obj_page_field->base_type = "Number";
            $obj_page_field->control_type = "radio";
            $obj_page_field->extended_type = "Selection";
            $obj_page_field->encode_entities = false;
            $obj_page_field->multi_pairs = array( 
                                                array( new ffData("1", "Number"),  new ffData(ffTemplate::_get_word_by_code("privacy_check_yes"))),
                                                array( new ffData("0", "Number"),  new ffData(ffTemplate::_get_word_by_code("privacy_check_no")))
                                            );
            //$obj_page_field->min_val = 1;
            //$obj_page_field->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
            //$obj_page_field->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
            //$obj_page_field->required = true;
            $oRecord->addContent($obj_page_field, "privacy");
        }

        if($enable_dynamic_cart) {
            if($enable_ecommerce == "onegood") {
                $tpl = ffTemplate::factory(get_template_cascading($user_path, "dynamic_cart.html", "/ecommerce/cart"));
                $tpl->load_file("dynamic_cart.html", "main");
                $tpl->set_var("symbol", $schema_ecommerce["symbol"]);
                if($enable_ecommerce_weight)
                    $tpl->parse("SezWeight", false);
                if($skip_shipping_calc)
                    $tpl->parse("SezShippingCalc", false);

                $oRecord->fixed_post_content .= $tpl->rpparse("main", false);
            }

            $js .= '
                ff.load("ff.cms.form", function() {
                        ff.cms.form.init("' . $field_value["form"]["ID"]  . '", {"cart" : ' . ($enable_ecommerce == "onegood" ? "true" : "false") . ', "label" : ' . ($enable_dynamic_label ? "true" : "false") . '}, '  . json_encode($arrDep["js"]) .  ',' . (count($arrJsPricelist) > 1000 ? "'" . $_REQUEST["sid"] . "'" : json_encode($arrJsPricelist)) . (is_array($arrDiscount) ? ',' . json_encode($arrDiscount) : "") . ');
                });';
        }

        if(strlen($js)) {
            $js = '
                jQuery(function() {
                        ' . $js . '
                });';

            $cm->oPage->tplAddJs("ff.cms.form.init", array(
            	"embed" => $js
            ));
        } else {
        	$cm->oPage->tplAddJs("ff.cms.form");
        }
    }
    $cm->oPage->addContent($oRecord);
}