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

if (!AREA_MODULES_SHOW_MODIFY) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record(array(
	"table" => "module_form_fields"
	, "key" => "ID"
	, "primary" => array(
		"table" => "module_form"
		, "key" => "ID"
		, "fields" => array(
			"ID_module" => "ID"
		)
	)
	, "if_request" => array(
		"field" => array(
			"table" => "module_form_fields"
			, "key" => "ID"
			, "fields" => array(
				"copy_field" => "ID"
				, "name" => "IF(display_name = ''
										, REPLACE(name, '-', ' ')
										, display_name
									)"
			)
		)
	)
), array(
	"name" => "IF(module_form_fields.display_name = ''
					, REPLACE(module_form_fields.name, '-', ' ')
					, module_form_fields.display_name
				)"
	, "ID_module" => null
));




$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_form_fields";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_form_fields.*
									, IF(module_form_fields.display_name = ''
										, REPLACE(module_form_fields.name, '-', ' ')
										, module_form_fields.display_name
									) AS display_name
								FROM module_form_fields 
								WHERE module_form_fields.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->insert_additional_fields["ID_module"] = new ffData($record["ID_module"], "Number");
$oRecord->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "FormExtraFieldModify_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
	$record["name"]
	, true
	, false
	, false
	, $oRecord
);	 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($record["noentry"]) 
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "copy-from";
	$oField->label = ffTemplate::_get_word_by_code("form_fields_copy");
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT module_form_fields.ID
									, IFNULL(CONCAT(module_form_fields.name, ' (', module_form_fields_group.name, ')'), module_form_fields.name) AS name
									, IF(module_form.display_name = ''
										, REPLACE(module_form.name, '-', ' ')
										, module_form.display_name
									) AS grp_name
								FROM module_form_fields 
									INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
									LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
								ORDER BY module_form_fields.name";
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->actex_group = "grp_name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_addnew");
	$oField->store_in_db = false;
	$oRecord->addContent($oField);    
} else 
{
	/************
	* LOAD Default
	*/
	if(is_array($globals->ecommerce["preview"]["vatTime"]) && count($globals->ecommerce["preview"]["vatTime"])) {
		foreach($globals->ecommerce["preview"]["vatTime"] AS $arrVatTime_key => $arrVatTime_value) {
			if(time() > $arrVatTime_key) {
				$actual_vat = $arrVatTime_value;
				break;
			}
		}
	}
		
	$query_string = "SELECT nameID, name, type, group_name
					FROM
					(
						SELECT nameID, name, type, group_name FROM
						(
							(
								SELECT anagraph_fields.ID AS nameID
										, anagraph_fields.name AS name
										, anagraph_type.name AS group_name
										, " . $db->toSql("anagraph") . " AS type
									FROM anagraph_type
										INNER JOIN anagraph_fields ON anagraph_fields.ID_type = anagraph_type.ID
									WHERE 1
									ORDER BY anagraph_type.name,anagraph_fields.name
							) 
								UNION 
							(
								SELECT cm_mod_security_users_fields.ID AS nameID 
										, cm_mod_security_users_fields.field AS name
										, '' AS group_name
										, " . $db->toSql("user") . " AS type
								FROM cm_mod_security_users_fields
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("city") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "city") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("province") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "province") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("region") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "region") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("state") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME =" . $db->toSql(FF_SUPPORT_PREFIX . "state") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
								ORDER BY COLUMN_NAME
							)
								UNION
							(
								SELECT vgallery_fields.ID
									, vgallery_fields.name AS name
									, vgallery_type.name AS group_name
									, " . $db->toSql("vgallery") . " AS type
								FROM vgallery_fields 
									INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
								ORDER BY LOWER(vgallery_type.name),LOWER(vgallery_fields.name)
							)
						) AS tbl_src 
						ORDER BY type, name
					) AS macro_tbl
				[WHERE]
				ORDER BY macro_tbl.type, macro_tbl.name";
	    
	    $sSQL = "SELECT module_form.* 
	            FROM module_form
	            WHERE module_form.ID = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        $ecommerce_type = $db->getField("enable_ecommerce", "Text", true);
	        $enable_ecommerce_weight = $db->getField("enable_ecommerce_weight", "Number", true);
	        $enable_dynamic_cart_advanced = $db->getField("enable_dynamic_cart_advanced", "Number", true);
			$field_enable_dep = $db->getField("field_enable_dep", "Number", true);
			$field_enable_pricelist = $db->getField("field_enable_pricelist", "Number", true);

		$fields["group"] = $db->getField("field_default_ID_form_fields_group", "Text", true);
		$fields["extended_type"] = $db->getField("field_default_ID_extended_type", "Text", true);
		$fields["disable_free_input"] = $db->getField("field_default_disable_free_input", "Text", true);
		$fields["disable_select_one"] = $db->getField("field_default_disable_select_one", "Text", true);
		$fields["require"] = $db->getField("field_default_require", "Text", true);
		$fields["hide_label"] = $db->getField("field_default_hide_label", "Text", true);
		$fields["placeholder"] = $db->getField("field_default_placeholder", "Text", true);
		$fields["control"] = $db->getField("field_default_ID_check_control", "Text", true);
		$fields["unic_value"] = $db->getField("field_default_unic_value", "Text", true);
		$fields["send_mail"] = $db->getField("field_default_send_mail", "Text", true);
		$fields["enable_in_mail"] = $db->getField("field_default_enable_in_mail", "Text", true);
		$fields["enable_in_grid"] = $db->getField("field_default_enable_in_grid", "Text", true);
		$fields["enable_in_menu"] = $db->getField("field_default_enable_in_menu", "Text", true);
		$fields["enable_in_document"] = $db->getField("field_default_enable_in_document", "Text", true);
		$fields["enable_tip"] = $db->getField("field_default_enable_tip", "Text", true);
		$fields["writable"] = $db->getField("field_default_writable", "Text", true);
		$fields["hide"] = $db->getField("field_default_hide", "Text", true);
		$fields["preload_by_domclass"] = $db->getField("field_default_preload_by_domclass", "Text", true);
		$fields["fixed_pre_content"] = $db->getField("field_default_fixed_pre_content", "Text", true);
		$fields["fixed_post_content"] = $db->getField("field_default_fixed_post_content", "Text", true);
		$fields["vgallery_field"] = $db->getField("field_default_vgallery_field", "Text", true);
		$fields["preload_by_db"] = $db->getField("field_default_preload_by_db", "Text", true);
		$fields["domclass"] = $db->getField("field_default_domclass", "Text", true);
		$fields["custom"] = $db->getField("field_default_custom", "Text", true);

		$fields["val_min"] = $db->getField("field_default_val_min", "Text", true);        
		$fields["val_max"] = $db->getField("field_default_val_max", "Text", true);        
		$fields["val_step"] = $db->getField("field_default_val_step", "Text", true);

		$fields["qta"] = $db->getField("fixed_cart_qta", "Number", true);
		$fields["price"] = $db->getField("fixed_cart_price", "Number", true);
		$fields["vat"] = $db->getField("fixed_cart_vat", "Number", true);
		$fields["weight"] = $db->getField("fixed_cart_weight", "Number", true);
		$fields["hide_vat"] = $db->getField("hide_vat", "Number", true);
		$fields["hide_weight"] = $db->getField("hide_weight", "Number", true);

		$fields["show_price_in_label"] = $db->getField("field_default_show_price_in_label", "Text", true);

		if(strlen($fields["enable_in_mail"])
			&& strlen($fields["enable_in_grid"])
			&& strlen($fields["enable_in_menu"])
			&& strlen($fields["enable_in_document"])
		) {
			$disable_group = true;
		}
	}

	$field_default["ID_form_fields_group"] = 0;
	$field_default["type"] = "";
	//$field_default["ID_extended_type"] = $arrExtType["String"];
	$field_default["ID_vgallery_field"] = 0;
	$field_default["disable_free_input"] = 0;
	$field_default["disable_select_one"] = 0;
	$field_default["price"] = 0;
	$field_default["vat"] = $actual_vat;
	$field_default["weight"] = 0;
	$field_default["show_price_in_label"] = 0;
	$field_default["val_min"] = 0;
	$field_default["val_max"] = 0;
	$field_default["val_step"] = 0;
	$field_default["ID_check_control"] = 0;
	$field_default["send_mail"] = 0;
	$field_default["require"] = 0;
	$field_default["hide_label"] = 0;
	$field_default["placeholder"] = 0;
	$field_default["unic_value"] = 0;
	$field_default["enable_tip"] = 0;
	$field_default["writable"] = 1;
	$field_default["hide"] = 0;
	$field_default["preload_by_domclass"] = "";
	$field_default["fixed_pre_content"] = "";
	$field_default["fixed_post_content"] = "";
	$field_default["preload_by_db"] = "";
	$field_default["domclass"] = "";
	$field_default["custom"] = "";
	$field_default["enable_in_mail"] = 1;
	$field_default["enable_in_grid"] = 1;
	$field_default["enable_in_menu"] = 0;
	$field_default["enable_in_document"] = 1;

    if(check_function("get_field_default"))
        $arrFieldData = get_field_default("module_form_fields", $record["copy_field"], "module_form_fields", $_REQUEST["keys"]["ID"], $field_default);

	//$arrControlType = $arrFieldData["control_type"];
    $arrControlTypeRev = $arrFieldData["control_type_rev"];
    //$arrExtType = $arrFieldData["extended_type"];
    $arrExtTypeRev = $arrFieldData["extended_type_rev"];
    
    $field_default = $arrFieldData["default"];
    if($_REQUEST["keys"]["ID"] > 0) 
    { 
        $actual_control_type = $arrControlTypeRev[$arrFieldData["actual"]["ID_check_control"]];
        $actual_ext_type = $arrExtTypeRev[$arrFieldData["actual"]["ID_extended_type"]];
        $actual_type = $arrFieldData["actual"]["type"];
        $actual_disable_free_input = $arrFieldData["actual"]["disable_free_input"];
    }
    elseif($copy_field > 0)
    {
        $actual_control_type = $arrControlTypeRev[$arrFieldData["default"]["ID_check_control"]];
        $actual_ext_type = $arrExtTypeRev[$arrFieldData["default"]["ID_extended_type"]];
        $actual_type = $arrFieldData["default"]["type"];
        $actual_disable_free_input = $arrFieldData["default"]["disable_free_input"];
    }

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->widget = "slug";
	$oField->slug_title_field = "display_name";
	$oField->container_class = "hidden";
	$oRecord->addContent($oField);	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("form_fields_name");
	$oField->required = true;
	$oRecord->addContent($oField);
    
	if(!strlen($fields["group"])) 
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_form_fields_group";
		$oField->label = ffTemplate::_get_word_by_code("form_fields_group");
		$oField->base_type = "Number";
		$oField->source_SQL = "SELECT ID, name FROM module_form_fields_group ORDER BY name";
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
		$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify"; 
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigGroupModify_confirmdelete";
		$oField->resources[] = "FormConfigGroupModify";
		$oField->default_value = new ffData($field_default["ID_form_fields_group"], "Number");
		$oRecord->addContent($oField);
	}

	if(strlen($ecommerce_type))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "type";
		$oField->label = ffTemplate::_get_word_by_code("form_fields_type");
		$oField->extended_type = "Selection";
		$oField->control_type = "radio";
		$oField->multi_pairs = array(
									array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("form_fields_type_simple")))
									, array(new ffData("price"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_price")))
							);
		if($ecommerce_type == "onegood") 
			$oField->multi_pairs[] = array(new ffData("multiplier"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_multiplier")));

		if($field_enable_pricelist) {
			$oField->multi_pairs[] = array(new ffData("pricelist"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_pricelist")));
		}

		if($_REQUEST["XHR_CTX_ID"])
			$oField->properties["onchange"] = "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'});";
		else
			$oField->properties["onchange"] = "ff.ajax.doRequest({'action' : 'refresh'});";

		$oField->default_value = new ffData($field_default["type"]);
		$oRecord->addContent($oField); 
	        
	        if(isset($_REQUEST[$oRecord->id . "_type"])) {
	            $actual_type = $_REQUEST[$oRecord->id . "_type"];
			}     

	    }

	    if(!strlen($fields["extended_type"]) && $actual_type != "multiplier") 
	    {       
	        if(isset($_REQUEST[$oRecord->id . "_ID_extended_type"])) {
	            $actual_ext_type = (array_key_exists($_REQUEST[$oRecord->id . "_ID_extended_type"], $arrExtTypeRev)
	                            ? $arrExtTypeRev[$_REQUEST[$oRecord->id . "_ID_extended_type"]]
	                            : ""
	                        );
			}     
			
	        if($actual_type == "price" || $actual_type == "pricelist")
	            $sSQL_Where_ext = " AND extended_type.name IN('Selection', 'Option', 'Group', 'Number', 'Boolean')";
	        else 
	            $sSQL_Where_ext = " AND extended_type.name NOT IN('GMap')";
	            
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "ID_extended_type";
			$oField->base_type = "Number";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_extended_type");
			$oField->extended_type = "Selection";
	        if(check_function("set_field_extended_type"))
	        	$oField = set_field_extended_type($oField, $sSQL_Where_ext);

	        if($_REQUEST["XHR_CTX_ID"])
	            $oField->actex_on_change = "function(obj, old_value, action) { 
		            if(action == 'change') {
	            		ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); 
		            }
	            }";
	        else
	            $oField->actex_on_change = "function(obj, old_value, action) { 
		            if(action == 'change') {
	            		ff.ajax.doRequest({'action' : 'refresh'}); 
		            }
	            }";
                
	        $oField->default_value = new ffData($field_default["ID_extended_type"], "Number");
	        $oRecord->addContent($oField);
	    } else 
		{
	        $oRecord->insert_additional_fields["ID_extended_type"] = new ffData(str_replace("show_no_default_", "", $fields["extended_type"]), "Number");
	    }   

	    if($actual_ext_type == "Selection" || $actual_ext_type == "Group" || $actual_ext_type == "Option" || $actual_type == "multiplier")
	    {
			if(!strlen($actual_type)) 
	        {
	            if(!strlen($fields["vgallery_field"])) 
	            { 
					$oField = ffField::factory($cm->oPage);
					$oField->id = "selectionSource";
					$oField->label = ffTemplate::_get_word_by_code("form_selection_source");
					$oField->widget = "actex";
					//$oField->widget = "activecomboex";
					$oField->base_type = "Text";
					$oField->multi_pairs =  array (
												array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
												array(new ffData("user"), new ffData(ffTemplate::_get_word_by_code("user"))),
												array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
												array(new ffData("city"), new ffData(ffTemplate::_get_word_by_code("city"))),
												array(new ffData("province"), new ffData(ffTemplate::_get_word_by_code("province"))),
												array(new ffData("region"), new ffData(ffTemplate::_get_word_by_code("region"))),
												array(new ffData("state"), new ffData(ffTemplate::_get_word_by_code("state")))
										   );
					$oField->actex_child = "field";
					$oField->multi_select_one_label = ffTemplate::_get_word_by_code("addCustomSource");
					$oField->actex_update_from_db = true;
					$oField->actex_on_update_bt = 'function(obj) {
													if(obj["value"]) {
														jQuery("#FormConfigFieldSelectionModify").parents(".row").hide();
													} else {
														jQuery("#FormConfigFieldSelectionModify").parents(".row").show();
													}
												}';
					$oRecord->addContent($oField);

					$oField = ffField::factory($cm->oPage);
					$oField->id = "field";
					$oField->label = ffTemplate::_get_word_by_code("form_selection_field");     
					$oField->widget = "actex";
					//$oField->widget = "activecomboex";
					$oField->source_SQL = $query_string;
					$oField->actex_father = "selectionSource";
					$oField->actex_related_field = "type";
					$oField->actex_group = "group_name";
					$oField->actex_hide_empty = "all"; 
					$oField->actex_update_from_db = true;
					$oRecord->addContent($oField);
	            }
	        }
			
		    $oDetail_fields = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
		    $oDetail_fields->id = "FormConfigFieldSelectionModify";
		    $oDetail_fields->src_table = "module_form_fields_selection_value";
		    $oDetail_fields->order_default = "ID";
		    $oDetail_fields->fields_relationship = array("ID_form_fields" => "ID");
		    $oDetail_fields->starting_rows = 1;
		    $oDetail_fields->min_rows = 1;
		    $oDetail_fields->force_min_rows = true;
		    $oDetail_fields->display_new_location = "Footer";
		    $oDetail_fields->display_grid_location = "Footer";
		    $oDetail_fields->display_rowstoadd = false;
		    $oDetail_fields->addEvent("on_before_parse_row", "FormConfigFieldSelectionModify_on_before_parse_row");
			$oDetail_fields->addEvent("on_done_action", "FormConfigFieldSelectionModify_on_done_action");
			$oDetail_fields->addEvent("on_do_action", "FormConfigFieldSelectionModify_on_do_action");
			if($actual_type == "pricelist")
				$oDetail_fields->user_vars["type"] = $actual_type;

		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "ID";
		    $oField->data_source = "ID";
		    $oField->base_type = "Number";
		    $oDetail_fields->addKeyField($oField);

		    if($actual_type != "multiplier")
		    {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = "name";
		        $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_name");
		        //$oField->required = true;
		       // $oField->properties["onfocus"] = "if(!jQuery(this).closest('.row').next().length)  { jQuery(this).closest('.ffDetails').find('.detailActions .add').click(); }";
		        $oDetail_fields->addContent($oField);
		    }                        
			        
		    if(AREA_SHOW_ECOMMERCE && $ecommerce_type && $actual_type && $actual_type != "pricelist") {
		        if($actual_type == "multiplier")
		        {
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "qta";
		            $oField->label = ffTemplate::_get_word_by_code("form_fields_qta");
		            $oField->base_type = "Number";
		            $oField->default_value = new ffData("1", "Number");
		            $oField->required = true;
		            $oDetail_fields->addContent($oField);
		        }
						
		        if($actual_type != "multiplier" && $enable_dynamic_cart_advanced) 
		        {
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "sum_qta_from";
		            $oField->label = ffTemplate::_get_word_by_code("form_fields_sum_qta_from");
		            $oField->extended_type = "Selection";
		            /*$oField->widget = "autocompletetoken";
		            $oField->autocompletetoken_minLength = 0;
		            $oField->autocompletetoken_combo = true;
		            $oField->autocompletetoken_compare_having = "name";*/
					$oField->widget = "actex";
					$oField->actex_autocomp = true;	
					$oField->actex_multi = true;
					$oField->actex_update_from_db = true;	
					$oField->actex_having_field = "name";
		            $oField->source_SQL = "		
		                        SELECT module_form_fields.ID
		                        	, IFNULL(CONCAT(module_form_fields.name, ' (', module_form_fields_group.name, ')'), module_form_fields.name) AS name
		                        FROM module_form_fields
		                                LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
		                        WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
		                                AND module_form_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.name IN('Number'))
		                                AND module_form_fields.`type` = 'price'
		                        [AND] [WHERE]
		                        [HAVING]
		                        [ORDER] [COLON] module_form_fields_group.name, module_form_fields.name
		                        [LIMIT]";
		            $oField->multi_select_noone = true;
		            $oField->multi_select_noone_val = new ffData("");
		            $oField->multi_select_one = false;
		            $oDetail_fields->addContent($oField);
			                
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "price_basic";
		            $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_price_basic");
		            $oField->base_type = "Number";
		           // $oField->app_type = "Currency";
		            $oDetail_fields->addContent($oField);	
		            
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "price_nostep";
		            $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_price_nostep");
		            $oField->base_type = "Number";
		           // $oField->app_type = "Currency";
		            $oDetail_fields->addContent($oField);
		            
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "qta";
		            $oField->label = ffTemplate::_get_word_by_code("form_fields_qta_step");
		            $oField->base_type = "Number";
		            $oField->default_value = new ffData("1", "Number");
		            $oField->required = true;
		            $oDetail_fields->addContent($oField);
		        }
							            
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = "price";
		        $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_price");
		        $oField->base_type = "Number";
		       // $oField->app_type = "Currency";
		        $oDetail_fields->addContent($oField);

		        if($actual_type != "multiplier") 
		        {	 
		            if($fields["hide_vat"])
		            {
		                $oDetail_fields->insert_additional_fields["vat"] = new ffData($fields["vat"], "Number");
		                $oDetail_fields->update_additional_fields["vat"] = new ffData($fields["vat"], "Number");
		            } 
		            else 
		            {
		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "vat";
		                $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_vat");
		                $oField->base_type = "Number";
		                $oField->default_value = new ffData($fields["vat"], "Number");
		                $oDetail_fields->addContent($oField);
		            }

		            if($fields["hide_weight"])
		            {
		                $oDetail_fields->insert_additional_fields["weight"] = new ffData($fields["weight"], "Number");
		                $oDetail_fields->update_additional_fields["weight"] = new ffData($fields["weight"], "Number");
		            } 
		            else 
		            {
		            	if($enable_ecommerce_weight) {
			                $oField = ffField::factory($cm->oPage);
			                $oField->id = "weight";
			                $oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_weight");
			                $oField->base_type = "Number";
			                $oField->default_value = new ffData($fields["weight"], "Number");
			                $oDetail_fields->addContent($oField);
						}
		            }
		        }
		        
		    } else {
				$oDetail_fields->insert_additional_fields["qta"] = new ffData(0, "Number");
				$oDetail_fields->update_additional_fields["qta"] = new ffData(0, "Number");
				$oDetail_fields->insert_additional_fields["sum_qta_from"] = new ffData("", "Text");
				$oDetail_fields->update_additional_fields["sum_qta_from"] = new ffData("", "Text");
				$oDetail_fields->insert_additional_fields["price_basic"] = new ffData(0, "Number");
				$oDetail_fields->update_additional_fields["price_basic"] = new ffData(0, "Number");
                $oDetail_fields->insert_additional_fields["price_nostep"] = new ffData(0, "Number");
                $oDetail_fields->update_additional_fields["price_nostep"] = new ffData(0, "Number");
				$oDetail_fields->insert_additional_fields["vat"] = new ffData(0, "Number");
				$oDetail_fields->update_additional_fields["vat"] = new ffData(0, "Number");
				$oDetail_fields->insert_additional_fields["price"] = new ffData(0, "Number");
				$oDetail_fields->update_additional_fields["price"] = new ffData(0, "Number");
				$oDetail_fields->insert_additional_fields["weight"] = new ffData(0, "Number");
				$oDetail_fields->update_additional_fields["weight"] = new ffData(0, "Number");
			}     
		    if($field_enable_dep) {
			    $oButton = ffButton::factory($cm->oPage);
			    $oButton->id = "module_form_dep";
			    $oButton->aspect = "link";
			    $oButton->display_label = false;
			    $oDetail_fields->addContentButton($oButton);
			}

		    $oRecord->addContent($oDetail_fields);
		    $cm->oPage->addContent($oDetail_fields);
	        
	        if($actual_type == "multiplier") 
	        {
                    if(!strlen($fields["disable_free_input"])) 
                    { 
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = "disable_free_input";
                        $oField->label = ffTemplate::_get_word_by_code("form_fields_disable_free_input");
                        $oField->base_type = "Number";
                        $oField->extended_type = "Boolean";
                        $oField->control_type = "checkbox";
                        $oField->unchecked_value = new ffData("0", "Number");
                        $oField->checked_value = new ffData("1", "Number");
                            if($_REQUEST["XHR_CTX_ID"])
                                $oField->properties["onchange"] = "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'});";
                            else
                                $oField->properties["onchange"] = "ff.ajax.doRequest({'action' : 'refresh'});";

                                    $oField->default_value = new ffData($field_default["disable_free_input"], "Number");
                        $oRecord->addContent($oField);    
                    } else {
                        $oRecord->insert_additional_fields["disable_free_input"] = new ffData(str_replace("show_no_default_", "", $fields["disable_free_input"]), "Number");
                        $oRecord->update_additional_fields["disable_free_input"] = new ffData(str_replace("show_no_default_", "", $fields["disable_free_input"]), "Number");
                    }
			
                    if(isset($_REQUEST[$oRecord->id . "_disable_free_input_ori"]) && !isset($_REQUEST[$oRecord->id . "_disable_free_input"])) {
                        $actual_disable_free_input = false;
                    } elseif(isset($_REQUEST[$oRecord->id . "_disable_free_input"])) {
                            $actual_disable_free_input = true;
                    }
                }
			//die($actual_type . $actual_disable_free_input);
	        if($actual_type != "multiplier" || ($actual_type == "multiplier" && $actual_disable_free_input))
	        {
		        if(!strlen($fields["disable_select_one"])) 
		        { 
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "disable_select_one";
		            $oField->label = ffTemplate::_get_word_by_code("form_fields_disable_select_one");
		            $oField->base_type = "Number";
		            $oField->extended_type = "Boolean";
		            $oField->control_type = "checkbox";
		            $oField->unchecked_value = new ffData("0", "Number");
		            $oField->checked_value = new ffData("1", "Number");
		            $oField->default_value = new ffData($field_default["disable_select_one"], "Number");
		            $oRecord->addContent($oField);    
		        } else {
		            $oRecord->insert_additional_fields["disable_select_one"] = new ffData(str_replace("show_no_default_", "", $fields["disable_select_one"]), "Number");
		            $oRecord->update_additional_fields["disable_select_one"] = new ffData(str_replace("show_no_default_", "", $fields["disable_select_one"]), "Number");
		        }
			}
	    } elseif($actual_ext_type == "Upload")
            {
                $oField = ffField::factory($cm->oPage);
	        $oField->id = "file_allowed_ext";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_file_allowed_ext");
	        $oRecord->addContent($oField);
            } else 
		{
	        if(AREA_SHOW_ECOMMERCE && $ecommerce_type && $actual_type) 
			{  
	            /*$oField = ffField::factory($cm->oPage);
	            $oField->id = "qta";
	            $oField->label = ffTemplate::_get_word_by_code("form_fields_qta");
	            $oField->base_type = "Number";
	            $oField->default_value = new ffData("1", "Number");
	            $oRecord->addContent($oField);*/
				if($actual_type == "pricelist")
				{
					$oRecord->insert_additional_fields["price"] = new ffData(0, "Number");
					$oRecord->update_additional_fields["price"] = new ffData(0, "Number");
				} else 
				{
					$oField = ffField::factory($cm->oPage);
					$oField->id = "price";
					$oField->label = ffTemplate::_get_word_by_code("form_fields_price");
					$oField->base_type = "Number";
				   // $oField->app_type = "Currency";
					$oField->default_value = new ffData($field_default["price"], "Number");
					$oRecord->addContent($oField);
				}

	            if($fields["hide_vat"])
	            {
	                $oRecord->insert_additional_fields["vat"] = new ffData($fields["vat"], "Number");
	                $oRecord->update_additional_fields["vat"] = new ffData($fields["vat"], "Number");
	            } 
	            else 
	            {
	                $oField = ffField::factory($cm->oPage);
	                $oField->id = "vat";
	                $oField->display = false;
	                $oField->label = ffTemplate::_get_word_by_code("form_fields_vat");
	                $oField->base_type = "Number";
	                $oField->default_value = new ffData($field_default["vat"], "Number");
	                $oRecord->addContent($oField);
	            }
	            
                    if($actual_type == "pricelist")
                    {
                        $oRecord->insert_additional_fields["weight"] = new ffData(0, "Number");
                        $oRecord->update_additional_fields["weight"] = new ffData(0, "Number");
                    } else 
                    {
                        if($fields["hide_weight"])
                        {
                            $oRecord->insert_additional_fields["weight"] = new ffData($fields["weight"], "Number");
                            $oRecord->update_additional_fields["weight"] = new ffData($fields["weight"], "Number");
                        } 
                        else 
                        {       
                        	if($enable_ecommerce_weight) {
	                            $oField = ffField::factory($cm->oPage);
	                            $oField->id = "weight";
	                            $oField->label = ffTemplate::_get_word_by_code("form_fields_weight");
	                            $oField->base_type = "Number";
	                            $oField->default_value = new ffData($field_default["weight"], "Number");
	                            $oRecord->addContent($oField);
							}
                        }
                    }
                    
	        }
	        
			if($actual_ext_type == "Number" && $enable_dynamic_cart_advanced) {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = "sum_price_from";
		        $oField->label = ffTemplate::_get_word_by_code("form_fields_sum_price_from");
				$oField->extended_type = "Selection";
				/*$oField->widget = "autocompletetoken";
				$oField->autocompletetoken_minLength = 0;
				$oField->autocompletetoken_combo = true;
				$oField->autocompletetoken_compare_having = "name";*/
				$oField->widget = "actex";
				$oField->actex_autocomp = true;	
				$oField->actex_multi = true;
				$oField->actex_update_from_db = true;	
				$oField->actex_having_field = "name";
				$oField->source_SQL = "		
										SELECT module_form_fields.ID
											, IFNULL(CONCAT(module_form_fields.name, ' (', module_form_fields_group.name, ')'), module_form_fields.name) AS name
										FROM module_form_fields
											LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
										WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
											" . ($actual_type
												? " AND module_form_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.name IN('Group', 'Selection', 'Option', 'Boolean'))
													AND module_form_fields.`type` = 'price'
												"
												: " AND module_form_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.name IN('Number'))"
											) . "
											AND module_form_fields.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
										[AND] [WHERE]
										[HAVING]
										[ORDER] [COLON] module_form_fields.`order`, module_form_fields_group.name, module_form_fields.name
										[LIMIT]";
				$oField->multi_select_noone = true;
				$oField->multi_select_noone_val = new ffData("");
				$oField->multi_select_one = false;
		        $oRecord->addContent($oField);	        
			}
	    }

	    $group_name = null;
	    if(!$disable_group) 
	    {
	        $group_name = "Control";
	        
	        $oRecord->addTab($group_name);
	        $oRecord->setTabTitle($group_name, ffTemplate::_get_word_by_code("form_config_control"));

	        $oRecord->addContent(null, true, $group_name); 
	        $oRecord->groups[$group_name] = array(
	                                     "title" => ffTemplate::_get_word_by_code("form_config_control")
	                                     , "cols" => 1
	                                     , "tab" => $group_name
	                                  );
	    }
	    
	    if(AREA_SHOW_ECOMMERCE && $ecommerce_type && $actual_type)
	    {
	            if(!strlen($fields["show_price_in_label"])) 
	            {
	                $oField = ffField::factory($cm->oPage);
	                $oField->id = "show_price_in_label";
	                $oField->label = ffTemplate::_get_word_by_code("form_fields_show_price_in_label");
	                $oField->base_type = "Number";
	                $oField->extended_type = "Boolean";
	                $oField->control_type = "checkbox";
	                $oField->unchecked_value = new ffData("0", "Number");
	                $oField->checked_value = new ffData("1", "Number");
	                $oField->default_value = new ffData($field_default["show_price_in_label"], "Number");
	                $oRecord->addContent($oField); 
	            } else {
	                $oRecord->insert_additional_fields["show_price_in_label"] = new ffData(str_replace("show_no_default_", "", $fields["show_price_in_label"]), "Number");
	                $oRecord->update_additional_fields["show_price_in_label"] = new ffData(str_replace("show_no_default_", "", $fields["show_price_in_label"]), "Number");
	            }    
	    }

	    if($actual_ext_type == "Number" || ($actual_type == "multiplier" && !$actual_disable_free_input))
	    {
	    
	        if(!strlen($fields["val_min"])) 
	        {    
	            $oField = ffField::factory($cm->oPage);
	            $oField->id = "val_min";
	            $oField->label = ffTemplate::_get_word_by_code("form_fields_val_min");
	            $oField->default_value = new ffData($field_default["val_min"], "Number");
	            $oRecord->addContent($oField, $group_name);
	        }
	        if(!strlen($fields["val_max"])) 
	        {    
	            $oField = ffField::factory($cm->oPage);
	            $oField->id = "val_max";
	            $oField->label = ffTemplate::_get_word_by_code("form_fields_val_max");
	            $oField->default_value = new ffData($field_default["val_max"], "Number");
	            $oRecord->addContent($oField, $group_name);
	        }
	        if(!strlen($fields["val_step"])) 
	        {    
	            $oField = ffField::factory($cm->oPage);
	            $oField->id = "val_step";
	            $oField->label = ffTemplate::_get_word_by_code("form_fields_val_step");
	            $oField->default_value = new ffData($field_default["val_step"], "Number");
	            $oRecord->addContent($oField, $group_name);
	        }
	    
	    
	    }
		if($actual_type != "multiplier")
		{
		    if(!strlen($fields["control"])) 
		    {
		        if(isset($_REQUEST[$oRecord->id . "_ID_check_control"])) {
		            $actual_control_type = (array_key_exists($_REQUEST[$oRecord->id . "_ID_check_control"], $arrControlTypeRev)
		                            ? $arrControlTypeRev[$_REQUEST[$oRecord->id . "_ID_check_control"]]
		                            : ""
		                        );
		        }     

		        switch($actual_ext_type) 
		        {
		            case "Date":
		            case "DateCombo":
		                $sSQL_Where_control = " AND check_control.name IN('Date')";
		                break;
		            case "Text":
		            case "TextBB":
		            case "TextCK":
		                $sSQL_Where_control = " AND check_control.name IN('Text')";
		                break;
		            case "Link":
		                $sSQL_Where_control = " AND check_control.name LIKE 'Url%'";
		                break;
		            case "Number":
		                $sSQL_Where_control = " AND check_control.name IN('number')";
		                break;
		            case "String":
		                $sSQL_Where_control = "";
		                break;
		            default:
		                $hide_control = true;
		                
		        }
		            
		        if(!$hide_control) 
		        {
		            $oField = ffField::factory($cm->oPage);
		            $oField->id = "ID_check_control";
		            $oField->label = ffTemplate::_get_word_by_code("form_fields_check_control");
		            $oField->extended_type = "Selection";
		            $oField->source_SQL = "SELECT ID
		                                        , IFNULL(
		                                            (SELECT " . FF_PREFIX . "international.description
		                                                FROM " . FF_PREFIX . "international
		                                                WHERE " . FF_PREFIX . "international.word_code = CONCAT('control_field_', check_control.name)
		                                                    AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
		                                                    AND " . FF_PREFIX . "international.is_new = 0
		                                                ORDER BY " . FF_PREFIX . "international.description
		                                                LIMIT 1
		                                            )
		                                            , check_control.name 
		                                        ) AS name
		                                    FROM check_control 
		                                    WHERE 1 $sSQL_Where_control 
		                                    ORDER BY name";
		            if($_REQUEST["XHR_CTX_ID"])
		                $oField->properties["onchange"] = "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'});";
		            else
		                $oField->properties["onchange"] = "ff.ajax.doRequest({'action' : 'refresh'});";
		            
		            $oField->default_value = new ffData($field_default["ID_check_control"], "Number");
		            $oRecord->addContent($oField, $group_name);
		        }
		    } else {
		        $oRecord->insert_additional_fields["ID_check_control"] = new ffData(str_replace("show_no_default_", "", $fields["control"]), "Number");
		        $oRecord->update_additional_fields["ID_check_control"] = new ffData(str_replace("show_no_default_", "", $fields["control"]), "Number");
		    }
		}
		
	    if(!strlen($fields["hide_label"])) 
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "hide_label";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_hide_label");
	        $oField->base_type = "Number";
	        $oField->extended_type = "Boolean";
	        $oField->control_type = "checkbox";
	        $oField->unchecked_value = new ffData("0", "Number");
	        $oField->checked_value = new ffData("1", "Number");
	        $oField->default_value = new ffData($field_default["hide_label"], "Number");
	        $oRecord->addContent($oField, $group_name); 
	    } else {
	        $oRecord->insert_additional_fields["hide_label"] = new ffData(str_replace("show_no_default_", "", $fields["hide_label"]), "Number");
	        $oRecord->update_additional_fields["hide_label"] = new ffData(str_replace("show_no_default_", "", $fields["hide_label"]), "Number");
	    }		
	    
	    if(!strlen($fields["placeholder"])) 
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "placeholder";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_placeholder");
	        $oField->base_type = "Number";
	        $oField->extended_type = "Boolean";
	        $oField->control_type = "checkbox";
	        $oField->unchecked_value = new ffData("0", "Number");
	        $oField->checked_value = new ffData("1", "Number");
	        $oField->default_value = new ffData($field_default["placeholder"], "Number");
	        $oRecord->addContent($oField, $group_name); 
	    } else {
	        $oRecord->insert_additional_fields["placeholder"] = new ffData(str_replace("show_no_default_", "", $fields["placeholder"]), "Number");
	        $oRecord->update_additional_fields["placeholder"] = new ffData(str_replace("show_no_default_", "", $fields["placeholder"]), "Number");
	    }
	    			
	    if(!strlen($fields["send_mail"])) 
	    {
	        if($actual_control_type == "email")
	        {
	            $oField = ffField::factory($cm->oPage);
	            $oField->id = "send_mail";
	            $oField->label = ffTemplate::_get_word_by_code("form_fields_send_mail");
	            $oField->base_type = "Number";
	            $oField->control_type = "checkbox";
	            $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	            $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	            $oField->default_value = new ffData($field_default["send_mail"], "Number");
	            $oRecord->addContent($oField, $group_name);
	        }
	    } else {
	        $oRecord->insert_additional_fields["send_mail"] = new ffData(str_replace("show_no_default_", "", $fields["send_mail"]), "Number");
	        $oRecord->update_additional_fields["send_mail"] = new ffData(str_replace("show_no_default_", "", $fields["send_mail"]), "Number");
	    }
	        
	    if(!strlen($fields["require"])) 
	    {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "require";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_require");
	        $oField->base_type = "Number";
	        $oField->extended_type = "Boolean";
	        $oField->control_type = "checkbox";
	        $oField->unchecked_value = new ffData("0", "Number");
	        $oField->checked_value = new ffData("1", "Number");
	        $oField->default_value = new ffData($field_default["require"], "Number");
	        $oRecord->addContent($oField, $group_name); 
	    } else {
	        $oRecord->insert_additional_fields["require"] = new ffData(str_replace("show_no_default_", "", $fields["require"]), "Number");
	        $oRecord->update_additional_fields["require"] = new ffData(str_replace("show_no_default_", "", $fields["require"]), "Number");
	    }
	        
	    if(!strlen($fields["unic_value"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "unic_value";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_unic_value");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["unic_value"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["unic_value"] = new ffData(str_replace("show_no_default_", "", $fields["unic_value"]), "Number");
	        $oRecord->update_additional_fields["unic_value"] = new ffData(str_replace("show_no_default_", "", $fields["unic_value"]), "Number");
	    }
	    if(!strlen($fields["enable_tip"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "enable_tip";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_enable_tip");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["enable_tip"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["enable_tip"] = new ffData(str_replace("show_no_default_", "", $fields["enable_tip"]), "Number");
	        $oRecord->update_additional_fields["enable_tip"] = new ffData(str_replace("show_no_default_", "", $fields["enable_tip"]), "Number");
	    }
	    if(!strlen($fields["writable"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "writable";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_writable");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["writable"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["writable"] = new ffData(str_replace("show_no_default_", "", $fields["writable"]), "Number");
	        $oRecord->update_additional_fields["writable"] = new ffData(str_replace("show_no_default_", "", $fields["writable"]), "Number");
	    }
	    if(!strlen($fields["hide"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "hide";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_hide");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["hide"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["hide"] = new ffData(str_replace("show_no_default_", "", $fields["hide"]), "Number");
	        $oRecord->update_additional_fields["hide"] = new ffData(str_replace("show_no_default_", "", $fields["hide"]), "Number");
	    }
	    if(!strlen($fields["preload_by_domclass"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "preload_by_domclass";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_preload_by_domclass");
	        $oField->default_value = new ffData($field_default["preload_by_domclass"]);
	        $oRecord->addContent($oField, $group_name);
	    }
	    if(!strlen($fields["fixed_pre_content"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "fixed_pre_content";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_fixed_pre_content");
	        $oField->extended_type = "Text";
	        $oField->default_value = new ffData($field_default["fixed_pre_content"]);
	        $oRecord->addContent($oField, $group_name);
	    }
	    if(!strlen($fields["fixed_post_content"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "fixed_post_content";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_fixed_post_content");
	        $oField->extended_type = "Text";
	        $oField->default_value = new ffData($field_default["fixed_post_content"]);
	        $oRecord->addContent($oField, $group_name);
	    }
	    if(!strlen($fields["preload_by_db"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "preload_by_db";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_preload_by_db");
	        $oField->extended_type = "Selection";
	        $oField->multi_pairs = array(
	                                array(new ffData("reference"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_reference")))
	                                , array(new ffData("avatar"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_avatar")))
	                                , array(new ffData("name"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_name")))
	                                , array(new ffData("surname"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_surname")))
	                                , array(new ffData("email"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_email")))
	                                , array(new ffData("tel"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_tel")))
	                                , array(new ffData("billreference"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billreference")))
	                                , array(new ffData("billcf"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billcf")))
	                                , array(new ffData("billpiva"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billpiva")))
	                                , array(new ffData("billaddress"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billaddress")))
	                                , array(new ffData("billcap"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billcap")))
	                                , array(new ffData("billprovince"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billprovince")))
	                                , array(new ffData("billtown"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billtown")))
	                                , array(new ffData("billstate"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_billstate")))
	                                , array(new ffData("shippingreference"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingreference")))
	                                , array(new ffData("shippingaddress"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingaddress")))
	                                , array(new ffData("shippingcap"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingcap")))
	                                , array(new ffData("shippingprovince"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingprovince")))
	                                , array(new ffData("shippingtown"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingtown")))
	                                , array(new ffData("shippingstate"), new ffData(ffTemplate::_get_word_by_code("form_fields_preload_userdata_shippingstate")))

	                            );
	        $oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_preload_na");
	        $oField->default_value = new ffData($field_default["preload_by_db"]);
	        $oRecord->addContent($oField, $group_name);
	    }
	    if(!strlen($fields["domclass"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "domclass";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_domclass");
	        $oField->widget = "listgroup";
	        $oField->grouping_separator = " ";
	        $oField->default_value = new ffData($field_default["domclass"]);
	        $oRecord->addContent($oField, $group_name);
	    }
	    if(!strlen($fields["custom"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "custom";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_custom");
	        $oField->base_type = "Text";
	        $oField->extended_type = "Text";
	        $oField->default_value = new ffData($field_default["custom"]);
	        $oRecord->addContent($oField, $group_name);    
	    }

	    $group_name = null;
	    if(!$disable_group) 
	    {
	        $group_name = "Display";

	        $oRecord->addTab($group_name);
	        $oRecord->setTabTitle($group_name, ffTemplate::_get_word_by_code("form_config_display"));

	        $oRecord->addContent(null, true, $group_name); 
	        $oRecord->groups[$group_name] = array(
	                                     "title" => ffTemplate::_get_word_by_code("form_config_display")
	                                     , "cols" => 1
	                                     , "tab" => $group_name
	                                  );
	    }

	    if(!strlen($fields["enable_in_mail"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "enable_in_mail";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_enable_in_mail");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["enable_in_mail"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["enable_in_mail"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_mail"]), "Number");
	        $oRecord->update_additional_fields["enable_in_mail"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_mail"]), "Number");
	    }
	    if(!strlen($fields["enable_in_grid"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "enable_in_grid";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_enable_in_grid");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["enable_in_grid"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["enable_in_grid"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_grid"]), "Number");
	        $oRecord->update_additional_fields["enable_in_grid"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_grid"]), "Number");
	    }
	    if(!strlen($fields["enable_in_menu"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "enable_in_menu";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_enable_in_menu");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["enable_in_menu"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["enable_in_menu"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_menu"]), "Number");
	        $oRecord->update_additional_fields["enable_in_menu"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_menu"]), "Number");
	    }
	    if(!strlen($fields["enable_in_document"])) 
	    {    
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "enable_in_document";
	        $oField->label = ffTemplate::_get_word_by_code("form_fields_enable_in_document");
	        $oField->base_type = "Number";
	        $oField->control_type = "checkbox";
	        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	        $oField->default_value = new ffData($field_default["enable_in_document"], "Number");
	        $oRecord->addContent($oField, $group_name);
	    } else {
	        $oRecord->insert_additional_fields["enable_in_document"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_document"]), "Number");
	        $oRecord->update_additional_fields["enable_in_document"] = new ffData(str_replace("show_no_default_", "", $fields["enable_in_document"]), "Number");
	    }
            
            
    }
    $cm->oPage->addContent($oRecord);
    
function FormExtraFieldModify_on_do_action($component, $action) { 
	$cm = cm::getInstance();

	switch($action) {
	    case "insert":
	        if(isset($component->form_fields["copy-from"])) {
                ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . $cm->real_path_info . "?field=" . $component->form_fields["copy-from"]->getValue());
            }
            break;
        default:
	}
	return false;


}

function FormExtraFieldModify_on_done_action($component, $action) { 
    $db = ffDB_Sql::factory();
    if(strlen($action))
    {
		
        switch ($action) {
            case "update":
				
                foreach($component->form_fields AS $field_name => $field_value)
                {
					if($field_name == "type" && $field_value->value_ori->getValue() == "pricelist")
					{
						if($field_value->getValue() != "pricelist" )
						{
							$sSQL = "DELETE
										FROM module_form_pricelist_detail 
										WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
							$db->execute($sSQL);
						}
					}
                }

                break;
			case "confirmdelete":
				if(isset($_REQUEST["keys"]["ID"]) && isset($_REQUEST["keys"]["formcnf-ID"]))
				{
					if(check_function("MD_form_fields_delete"))
						MD_form_fields_delete($_REQUEST["keys"]["formcnf-ID"], $_REQUEST["keys"]["ID"]);
				}
				
				break;
            default:
                break;
        }
		
		if($_REQUEST["XHR_CTX_ID"]) {
			die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => $component->resources, "update_all" => true), true));
		}
    }

}

function FormConfigFieldSelectionModify_on_done_action($component, $action) { 
    $db = ffDB_Sql::factory();
    if(strlen($action))
    { 
		switch ($action) {
			case "update":
				if(isset($component->user_vars["type"]) && $component->user_vars["type"] == "pricelist")
				{
					foreach($component->recordset AS $field_selection_name => $field_selection_value)
					{
						if($field_selection_value["name"]->getValue() !== $component->recordset_ori[$field_selection_name]["name"]->getValue())
						{
							$sSQL = "UPDATE module_form_pricelist_detail
											SET module_form_pricelist_detail.value = " . $db->toSql($field_selection_value["name"]->getValue(), "Text") . "
										WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
											AND module_form_pricelist_detail.value = " . $db->toSql($component->recordset_ori[$field_selection_name]["name"]->getValue(), "Text");
							$db->execute($sSQL);
						}
					}
				}
				break;

            default:
                break;
        }
	}
}

function FormConfigFieldSelectionModify_on_before_parse_row($component)
{
    //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
    if(isset($component->detail_buttons["module_form_dep"])) {
        $component->detail_buttons["module_form_dep"]["obj"]->class = cm_getClassByFrameworkCss("chain", "icon");
        $component->detail_buttons["module_form_dep"]["obj"]->action_type = "submit"; 
        $component->detail_buttons["module_form_dep"]["obj"]->label = ffTemplate::_get_word_by_code("module_form_dep");
        $component->detail_buttons["module_form_dep"]["obj"]->form_action_url = $component->detail_buttons["module_form_dep"]["obj"]->parent[0]->page_path . "/dep?[KEYS]" . "keys[ID-subval]=" . $component->key_fields["ID"]->getValue() . "&ret_url=" . urlencode($component->parent[0]->getRequestUri());
        if($_REQUEST["XHR_CTX_ID"]) {
            $component->detail_buttons["module_form_dep"]["obj"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        } else {
            $component->detail_buttons["module_form_dep"]["obj"]->jsaction = "ff.ajax.doRequest({'action': 'set_dep', fields: [], 'url' : '[[frmAction_url]]'});";
        }
    }
}

function FormConfigFieldSelectionModify_on_do_action($component)
{
	//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	if(isset($component->main_record[0]->form_fields["selectionSource"]))
	{
		if(!strlen($component->main_record[0]->form_fields["selectionSource"]->getValue()))
		{
			foreach($component->recordset AS $key => $value)
			{
				if(!strlen($value["name"]->getValue()))
				{
					$component->displayError(ffTemplate::_get_word_by_code("selection_value_required"));
					return true;
				}
			}
		}
	}
}

function MD_form_fields_delete($ID_form, $fields) 
{ 
	$db = ffDB_Sql::factory();
						
	$sSQL = "SELECT  `ID_form_pricelist` , COUNT( module_form_pricelist_detail.ID ) AS number_row
				FROM module_form_pricelist_detail
				WHERE module_form_pricelist_detail.ID_form_pricelist IN (
					SELECT module_form_pricelist_detail.ID_form_pricelist
						FROM module_form_pricelist_detail
						WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($fields, "Number") . "
				)
				GROUP BY module_form_pricelist_detail.ID_form_pricelist";
	$db->query($sSQL);
	if($db->nextRecord())
	{
		do {
			$arrDeletePricelist[$db->getField("ID_form_pricelist", "Number", true)] = $db->getField("number_row", "Number", true);
		} while ($db->nextRecord());
	}
	
	if(is_array($arrDeletePricelist) && count($arrDeletePricelist))
	{
		$sSQL = "DELETE FROM module_form_pricelist_detail
						WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($fields, "Number");
		$db->execute($sSQL);
		
		foreach($arrDeletePricelist AS $ID_form_pricelist => $count_row)
		{
			if($count_row == 1)
			{
				$sSQL = "DELETE FROM module_form_pricelist
							WHERE module_form_pricelist.ID = " . $db->toSql($ID_form_pricelist, "Number");
				$db->execute($sSQL);
			}
		}
	}
	
	$sSQL = "DELETE FROM module_form_dep
				WHERE module_form_dep.ID_form_fields = " . $db->toSql($fields, "Number") . "
					AND module_form_dep.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);
	
	$sSQL = "DELETE FROM module_form_dep
				WHERE module_form_dep.dep_fields = " . $db->toSql($fields, "Number") . "
					AND module_form_dep.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);
}