<?php
$db = ffDB_Sql::factory();

$sSQL = "SELECT module_form.enable_ecommerce_weight AS enable_ecommerce_weight, module_form.name AS form_name
            FROM module_form
            WHERE ID = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
$db->query($sSQL);
if($db->nextRecord())
{
    $enable_ecommerce_weight = $db->getField("enable_ecommerce_weight", "Number", true);
    $module_form_title = ffTemplate::_get_word_by_code("modify_multiple_module_form_pricelist") . ": " . $db->getField("form_name", "Text", true);
}

if(isset($_REQUEST["list-ID"]) && strlen($_REQUEST["list-ID"]))
{
    if(strpos($_REQUEST["list-ID"], "-")) {
        $ID_list = str_replace("-", ",", $_REQUEST["list-ID"]);
    } else {
        $ID_list = $_REQUEST["list-ID"];
    }
    $sSQL_Where = " AND module_form_pricelist.ID IN (" . $db->toSql($ID_list, "Number") . ")";
} else {
    $sSQL_Where = " AND module_form_pricelist.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number");
}
    $sSQL = "SELECT module_form_fields.* 
                , module_form_fields_selection_value.ID AS ID_selection
                , extended_type.name AS extended_type_name
                , extended_type.ff_name AS ff_extended_type
                FROM module_form_fields
                    LEFT JOIN module_form_pricelist ON module_form_pricelist.ID_module = module_form_fields.ID_module
                    LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                    LEFT JOIN module_form_fields_selection_value ON module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
               WHERE 1
                    $sSQL_Where
                    AND module_form_fields.type = " . $db->toSql("pricelist") . "
                GROUP BY `ID`
                ORDER BY module_form_fields.`orders`, module_form_fields.name";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $ID = $db->getField("ID", "Number", true);
            $arrPricelist[$ID]["field"]["ID"] = $ID;
            $arrPricelist[$ID]["field"]["name"] = $db->getField("name", "Text", true);
            $arrPricelist[$ID]["field"]["form"]["type"] = $db->getField("type", "Text", true);
            $arrPricelist[$ID]["field"]["extended_type"] = $db->getField("extended_type_name", "Text", true);
            $arrPricelist[$ID]["ff_extended_type"] = $db->getField("ff_extended_type", "Text", true);
            if($db->getField("extended_type_name", "Text", true) == "Boolean") {
                $arrPricelist[$ID]["field"]["extended_type"] = "Selection";
                $arrPricelist[$ID]["field"]["multi_pairs"] = array (
                    array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
                    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
                ); 
                $arrPricelist[$ID]["ff_extended_type"] = "Text";
            }

        } while($db->nextRecord());
    }

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormPricelistModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "";
$oRecord->skip_action = true; 
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->user_vars["formcnf-ID"] = $_REQUEST["keys"]["formcnf-ID"];

//$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "form")) . $module_form_title . '</h1>';


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["list-ID"]) && strlen($_REQUEST["list-ID"]))
{
    $oRecord->addEvent("on_do_action", "FormExtraMultipleModifyPricelist_on_do_action");
    $oRecord->user_vars["ID_list"] = $ID_list;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oRecord->addKeyField($oField);

    if(is_array($arrPricelist) && count($arrPricelist)) 
    {
        if(check_function("get_field_by_extension"))
        {
            foreach($arrPricelist AS $arrPricelist_key => $arrPricelist_value) 
            {
                $oField = ffField::factory($cm->oPage);
                $field_ext = get_field_by_extension($oField, $arrPricelist_value["field"], "form");
                $oField = $field_ext["obj"];
                $oField->id = $arrPricelist_key;
                $oField->store_in_db = false;
                $oField->data_type = "";
                $oField->label = ffTemplate::_get_word_by_code($arrPricelist_value["field"]["name"]); //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
                $oField->default_value = new ffData($arrPricelist_value["value"], $arrPricelist_value["ff_extended_type"]);
                $oRecord->addContent($oField);
            } 
        }
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "price";
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price");
    $oField->base_type = "Number";
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "weight";
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight");
    $oField->base_type = "Number";
    $oRecord->addContent($oField);
} else 
{
    $oRecord->addEvent("on_do_action", "FormExtraMultipleModifySelectedPricelist_on_done_action");
    
    if(isset($_REQUEST[$oRecord->id . "_ID_fields"])) {
        $ID_fields = $_REQUEST[$oRecord->id . "_ID_fields"];
    }  
    
    $field_is_boolean = false;
    
    if($ID_fields > 0) {
        $sSQL = "SELECT extended_type.name,
                    module_form_fields_selection_value.ID AS ID_subvalue
                    FROM module_form_fields
                        INNER JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                        LEFT JOIN module_form_fields_selection_value ON module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
                    WHERE module_form_fields.ID = " . $db->toSql($ID_fields, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $type_element = $db->getField("name", "Text", true);
            if($type_element == "Boolean")
            {
                $field_is_boolean = true;
            } elseif($db->getField("ID_subvalue", "Number", true))
            {
                $field_is_selection = true;
            }
        }
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_checkbox";
    $oField->container_class = "field-form";
    $oField->store_in_db = false;
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_field");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->default_value = $oField->unchecked_value;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_fields";
    $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_fields");
    $oField->container_class = "modify-field";
    $oField->store_in_db = false;
    $oField->base_type = "Number";
    $oField->widget = "activecomboex"; 
    $oField->source_SQL = "SELECT module_form_fields.ID 
                                    , module_form_fields.name AS name
                                    , module_form_fields_group.name AS group_name
                                FROM module_form_fields
                                    LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                                WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
                                    AND module_form_fields.ID <> " . $db->toSql($ID_field, "Number") . "
                                    AND module_form_fields.type = " .  $db->toSql("pricelist", "Text") . "   
                                ORDER BY module_form_fields.`orders`, module_form_fields_group.name, module_form_fields.name";
    if($field_is_selection)
    {
        $oField->actex_child = array("ID_selection_value","ID_selection_value_new");
    }
    $oField->actex_update_from_db = true;
    $oField->actex_group = "group_name";

    if($_REQUEST["XHR_DIALOG_ID"])
        $oField->actex_on_change = "function(obj, old_value, action) {
            if(action == 'change') {
	            jQuery('#" . $oRecord->id . "_ID_selection_value').val('');
	            jQuery('#" . $oRecord->id . "_value').val('');

	            ff.ffPage.dialog.doRequest('" . $_REQUEST["XHR_DIALOG_ID"] . "', {'action' : 'refresh'}); 
	            return true; 
            }
        }";
    else
        $oField->actex_on_change = "function(obj, old_value, action) { 
        	if(action == 'change') {
	            jQuery('#" . $oRecord->id . "_ID_selection_value').val('');
	            jQuery('#" . $oRecord->id . "_value').val('');

	            ff.ajax.doRequest({'action' : 'refresh'}); return true; 
	        }
	    }";
    $oRecord->addContent($oField);

    if($ID_fields > 0) 
    {
        if(!$field_is_boolean)
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "operator";
            $oField->container_class = "modify-field";
            $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_operator");
            $oField->store_in_db = false;
            $oField->extended_type = "Selection";
            $oField->encode_entities = false;
            $oField->multi_pairs = array (
                                        array(new ffData("="), new ffData(ffTemplate::_get_word_by_code("e_uguale"))),
                                        array(new ffData("!="), new ffData(ffTemplate::_get_word_by_code("e_diverso")))
                                   );
            if(!$field_is_selection)
            {
                $oField->multi_pairs[] = array(new ffData("<"), new ffData(ffTemplate::_get_word_by_code("e_minore")));
                $oField->multi_pairs[] = array(new ffData(">"), new ffData(ffTemplate::_get_word_by_code("e_maggiore")));
                $oField->multi_pairs[] = array(new ffData("<="), new ffData(ffTemplate::_get_word_by_code("e_minore_o_uguale")));
                $oField->multi_pairs[] = array(new ffData(">="), new ffData(ffTemplate::_get_word_by_code("e_maggiore_o_uguale")));
            }
            $oField->multi_select_one = false;
            $oRecord->addContent($oField);
        }

        if($field_is_selection)
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_selection_value";
            $oField->container_class = "modify-field";
            $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value");
            $oField->store_in_db = false;
            $oField->base_type = "Number";
            $oField->widget = "activecomboex";
            $oField->source_SQL = "SELECT module_form_fields_selection_value.name
                                            , module_form_fields_selection_value.name
                                            , module_form_fields_selection_value.ID_form_fields
                                        FROM module_form_fields_selection_value
                                        [WHERE] ";
            $oField->actex_father = "ID_fields";
            $oField->actex_hide_empty = true;
            $oField->display_label = false;
            $oField->actex_update_from_db = true;
            $oField->actex_related_field = "ID_form_fields";
            $oRecord->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_selection_value_new";
            $oField->container_class = "modify-field";
            $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value_become");
            $oField->store_in_db = false;
            $oField->base_type = "Number";
            $oField->widget = "activecomboex";
            $oField->source_SQL = "SELECT module_form_fields_selection_value.name
                                            , module_form_fields_selection_value.name
                                            , module_form_fields_selection_value.ID_form_fields
                                        FROM module_form_fields_selection_value
                                        [WHERE] ";
            $oField->actex_father = "ID_fields";
            $oField->actex_hide_empty = true;
            $oField->actex_update_from_db = true;
            $oField->actex_related_field = "ID_form_fields";
            $oRecord->addContent($oField);
        } else
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "value";
            $oField->container_class = "modify-field";
            $oField->store_in_db = false;
            if($field_is_boolean)
            {
                $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_operator");
                $oField->extended_type = "Selection";
                $oField->multi_pairs = array (
                                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("not_selected"))),
                                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("selected")))
                                       );
            } else {
                $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value");
            }
            $oRecord->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "new_value";
            $oField->container_class = "modify-field";
            $oField->store_in_db = false;
            if($field_is_boolean)
            {
                $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value_become");
                $oField->extended_type = "Selection";
                $oField->multi_pairs = array (
                                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("not_selected"))),
                                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("selected")))
                                       );
            } else {
                $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value_become");
            }
            $oRecord->addContent($oField);
        }
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "price_checkbox";
    $oField->container_class = "price";
    $oField->store_in_db = false;
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price_checkbox");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->default_value = $oField->unchecked_value;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "choose_price";
    $oField->store_in_db = false;
    $oField->container_class = "modify-price";
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price_choose");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT module_form_pricelist.price,
                                module_form_pricelist.price
                                FROM module_form_pricelist
                                WHERE module_form_pricelist.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "[AND] [WHERE]
                            [HAVING]
                                ORDER BY price";
    $oField->actex_update_from_db = true;
    $oField->multi_select_one_label = "all";
    $oRecord->addContent($oField);

    if(is_array($arrPricelist) && count($arrPricelist)) 
    {
        if(check_function("get_field_by_extension"))
        {
            //print_r($arrPricelist);
            foreach($arrPricelist AS $arrPricelist_key => $arrPricelist_value) 
            {
                $oField = ffField::factory($cm->oPage);
                $field_ext = get_field_by_extension($oField, $arrPricelist_value["field"], "form");
                $oField = $field_ext["obj"];
                $oField->id = "choose_price_" . $arrPricelist_key;
                $oField->container_class = "modify-price"; 
                $oField->store_in_db = false;
                $oField->data_type = "";
                $oField->label = ffTemplate::_get_word_by_code($arrPricelist_value["field"]["name"]); //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
                $oField->default_value = new ffData($arrPricelist_value["value"], $arrPricelist_value["ff_extended_type"]);
                $oField->user_vars = $arrPricelist_value;
                $oRecord->addContent($oField);
            } 
        }
    }
    
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "how_modify_price";
    $oField->container_class = "modify-price";
    $oField->store_in_db = false;
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price_how_modify");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
                                array(new ffData("add_percent"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_add_percent"))),
                                array(new ffData("subtrack_percent"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_subtrack_percent"))),
                                array(new ffData("add_value"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_add_value"))),
                                array(new ffData("subtrack_value"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_subtrack_value")))
                           );
    $oField->multi_select_one = false;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "value_modify_price";
    $oField->store_in_db = false;
    $oField->container_class = "modify-price";
    $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price_value");
    $oRecord->addContent($oField);

    if($enable_ecommerce_weight)
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "weight_checkbox";
        $oField->container_class = "weight";
        $oField->store_in_db = false;
        $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight_checkbox");
        $oField->base_type = "Number";
        $oField->extended_type = "Boolean";
        $oField->control_type = "checkbox";
        $oField->unchecked_value = new ffData("0", "Number");
        $oField->checked_value = new ffData("1", "Number");
        $oField->default_value = $oField->unchecked_value;
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "choose_weight";
        $oField->container_class = "modify-weight";
        $oField->store_in_db = false;
        $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight_choose");
        $oField->widget = "activecomboex";
        $oField->source_SQL = "SELECT DISTINCT module_form_pricelist.weight,
                                    module_form_pricelist.weight
                                    FROM module_form_pricelist
                                    WHERE module_form_pricelist.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "[AND] [WHERE]
                                [HAVING]
                                    ORDER BY weight";
        $oField->actex_update_from_db = true;
        $oRecord->addContent($oField);
        
        if(is_array($arrPricelist) && count($arrPricelist)) 
        {
            if(check_function("get_field_by_extension"))
            {
                //print_r($arrPricelist);
                foreach($arrPricelist AS $arrPricelist_key => $arrPricelist_value) 
                {
                    $oField = ffField::factory($cm->oPage);
                    $field_ext = get_field_by_extension($oField, $arrPricelist_value["field"], "form");
                    $oField = $field_ext["obj"];
                    $oField->id = "choose_weight" . $arrPricelist_key;
                    $oField->container_class = "modify-weight"; 
                    $oField->store_in_db = false;
                    $oField->data_type = "";
                    $oField->label = ffTemplate::_get_word_by_code($arrPricelist_value["field"]["name"]); //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
                    $oField->default_value = new ffData($arrPricelist_value["value"], $arrPricelist_value["ff_extended_type"]);
                    $oField->user_vars = $arrPricelist_value;
                    $oRecord->addContent($oField);
                } 
            }
        }
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "how_modify_weight";
        $oField->container_class = "modify-weight";
        $oField->store_in_db = false;
        $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight_how_modify");
        $oField->extended_type = "Selection";
        $oField->multi_pairs = array (
                                    array(new ffData("add_percent"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_add_percent"))),
                                    array(new ffData("subtrack_percent"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_subtrack_percent"))),
                                    array(new ffData("add_value"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_add_value"))),
                                    array(new ffData("subtrack_value"), new ffData(ffTemplate::_get_word_by_code("modify_pricelist_subtrack_value")))
                               );
        $oField->multi_select_one = false;
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "value_modify_weight";
        $oField->container_class = "modify-weight";
        $oField->store_in_db = false;
        $oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight_value");
        $oRecord->addContent($oField);
    }
}

$js_control =  
'<script type="text/javascript">
    controlCheckbox(); 
    jQuery(document).on("click", "INPUT[type=checkbox]", function() {
        controlCheckbox(); 
    });
    
    function controlCheckbox() {
        jQuery(".modify-field").hide();
        jQuery(".modify-price").hide();
        jQuery(".modify-weight").hide();
        if(jQuery(".field-form input[type=checkbox]").is(":checked"))
            jQuery(".modify-field").show();
        if(jQuery(".price input[type=checkbox]").is(":checked"))
            jQuery(".modify-price").show();
        if(jQuery(".weight input[type=checkbox]").is(":checked"))
            jQuery(".modify-weight").show();
        
    };
</script>';
$cm->oPage->addContent($js_control);
$cm->oPage->addContent($oRecord);

function FormExtraMultipleModifyPricelist_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    if(strlen($action))
    {
        $arrID = explode(",", $component->user_vars["ID_list"]);
        switch ($action) 
        {
            case "insert":
            case "update": 
                $sSQL = "SELECT module_form_pricelist_detail.*
                            FROM module_form_pricelist_detail
                            WHERE module_form_pricelist_detail.ID_form_pricelist IN (" . $db->toSql($component->user_vars["ID_list"],"Text", false) . ")";
                $db->query($sSQL);
                if($db->nextRecord())
                {
                    do {
                        $arrField[$db->getField("ID_form_pricelist", "Number", true)][$db->getField("ID_form_fields", "Number", true)] = $db->getField("ID", "Number", true);
                    } while($db->nextRecord());
                }
                foreach($arrID AS $key => $ID_pricelist)
                {
                    if($component->form_fields["price"]->getValue() || $component->form_fields["weight"]->getValue())
                    {
                        $sSQL = "UPDATE module_form_pricelist
                                    SET " 
                                    . ($component->form_fields["price"]->getValue()
                                        ? " module_form_pricelist.price = " . $db->toSql($component->form_fields["price"]->getValue(), "text")
                                        : ""
                                    )
                                    . ($component->form_fields["weight"]->getValue()
                                        ? ($component->form_fields["price"]->getValue()
                                            ? " , "
                                            : ""
                                        ) . " module_form_pricelist.weight = " . $db->toSql($component->form_fields["weight"]->getValue(), "Number")
                                        : ""
                                    ) . "
                                    WHERE module_form_pricelist.ID = " . $db->toSql($ID_pricelist, "Number"); 
                        $db->execute($sSQL);
                    }
                    
                    foreach($component->form_fields AS $ID_form => $value_form)
                    {
                        if($ID_form != "price" && $ID_form != "weight")
                        {
                            if(strlen($value_form->getValue()) && !($value_form->base_type == "Number" && !$value_form->getValue()))
                            {
                                if(isset($arrField[$ID_pricelist][$ID_form]))
                                {
                                    $sSQL = "UPDATE module_form_pricelist_detail
                                                SET module_form_pricelist_detail.value = " . $db->toSql($value_form->getValue(), "text") . "
                                                WHERE module_form_pricelist_detail.ID = " . $db->toSql($arrField[$ID_pricelist][$ID_form], "Number"); 
                                    $db->execute($sSQL);
                                } else {
                                    $sSQL = "INSERT INTO module_form_pricelist_detail
                                                    (
                                                            ID
                                                            , ID_form_pricelist
                                                            , ID_form_fields
                                                            , value
                                                    ) VALUES (
                                                            null
                                                            , " . $db->toSql($ID_pricelist, "Number") . "
                                                            , " . $db->toSql($ID_form, "Number") . "
                                                            , " . $db->toSql($value_form->getValue(), "text") . "
                                                    )";
                                    $db->execute($sSQL);
                                }
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
}

function FormExtraMultipleModifySelectedPricelist_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    if(strlen($action))
    {
        switch ($action) {
            case "insert":
            case "update":
                if(isset($component->user_vars["formcnf-ID"]) && $component->user_vars["formcnf-ID"] > 0)
                {
                    if($component->form_fields["field_checkbox"]->getValue())
                    {
                        if(isset($component->form_fields["ID_selection_value_new"]) && strlen($component->form_fields["ID_selection_value_new"]->getValue()))
                        {
                            $sSQL_select = " AND module_form_pricelist_detail.value " . $component->form_fields["operator"]->getValue() . " " . $db->toSql($component->form_fields["ID_selection_value"]->getValue(), "Text");
                            $sSQL_update = $db->toSql($component->form_fields["ID_selection_value_new"]->getValue(), "Text");
                        } elseif(isset($component->form_fields["new_value"]) && strlen($component->form_fields["new_value"]->getValue()))
                        {
                            if(isset($component->form_fields["operator"]) && strlen($component->form_fields["operator"]->getValue()))
                            {
                                $sSQL_select = " AND module_form_pricelist_detail.value " . $db->toSql($component->form_fields["operator"]->getValue(), "Text", false) . " " . $db->toSql($component->form_fields["value"]->getValue(), "Text");
                                $sSQL_update = $db->toSql($component->form_fields["new_value"]->getValue(), "Text");
                            } else {
                                $sSQL_select = " AND module_form_pricelist_detail.value = " . $db->toSql($component->form_fields["value"]->getValue(), "Text");
                                $sSQL_update = $db->toSql($component->form_fields["new_value"]->getValue(), "Text");
                            }
                        }

                        if(strlen($sSQL_select))
                        {
                            $sSQL = "SELECT module_form_pricelist_detail.ID
                                        FROM module_form_pricelist_detail
                                            INNER JOIN module_form_pricelist ON module_form_pricelist.ID = module_form_pricelist_detail.ID_form_pricelist
                                        WHERE module_form_pricelist.ID_module = " . $db->toSql($component->user_vars["formcnf-ID"], "Number") . "
                                            AND module_form_pricelist_detail.ID_form_fields = " . $db->toSql($component->form_fields["ID_fields"]->getValue(), "Number") . 
                                            $sSQL_select;
                            $db->query($sSQL);
                            if($db->nextRecord())
                            {
                                do {
                                    $arrID[$db->getField("ID", "Number", true)] = 0;
                                } while($db->nextRecord());
                            }
                            if(is_array($arrID) && count($arrID))
                            {
                                foreach($arrID AS $key => $value)
                                {
                                    $sSQL = "UPDATE module_form_pricelist_detail SET
                                                    value = " . $sSQL_update . "
                                                WHERE module_form_pricelist_detail.ID = " . $db->toSql($key, "Number");
                                    $db->execute($sSQL);
                                }
                            }
                        }
                    }

                    if($component->form_fields["price_checkbox"]->getValue())
                    {
                        if(isset($component->form_fields["value_modify_price"]) && $component->form_fields["value_modify_price"]->getValue() > 0)
                        {
                            if(is_array($component->form_fields) && count($component->form_fields)) {
                                $total_field = 0;
                                foreach($component->form_fields AS $key => $value) {
                                    if($key == "choose_price") {
                                        if($component->form_fields["choose_price"]->getValue() > 0)
                                            $sSQL_price_where[] = " AND module_form_pricelist.price LIKE " . $db->toSql($component->form_fields["choose_price"]->getValue(), "Number");
                                    } elseif(strpos($key, "choose_price") === 0) {
                                        if(strlen($component->form_fields[$key]->getValue())) {
                                            $total_field++;
                                            if(strlen($sSQL_pricelist_where))
                                                $sSQL_pricelist_where .= " OR ";
                                            $sSQL_pricelist_where .= "
                                                (
                                                        module_form_pricelist_detail.ID_form_fields = " . $db->toSql($component->form_fields[$key]->user_vars["field"]["ID"], "Number") . "
                                                        AND module_form_pricelist_detail.`value` = " . $db->toSql($component->form_fields[$key]->value) . "
                                                )
                                            ";
                                        }
                                    }
                                    
                                }
                                if(strlen($sSQL_pricelist_where)) {
                                    $sSQL = "SELECT module_form_pricelist.ID, COUNT(module_form_pricelist.ID) AS count_id
                                                FROM module_form_pricelist_detail
                                                    INNER JOIN module_form_pricelist ON module_form_pricelist.ID = module_form_pricelist_detail.ID_form_pricelist
                                               WHERE (" . $sSQL_pricelist_where . ")
                                                    AND module_form_pricelist.ID_module = " . $db->toSql($component->user_vars["formcnf-ID"], "Number") . "
                                               GROUP BY module_form_pricelist.ID
                                               HAVING count_id = " . $total_field ."
                                               ORDER BY module_form_pricelist.ID";
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $arrFilterPricelist = array();
                                        do {
                                            $arrFilterPricelist[$db->getField("ID", "Number", true)] = $db->getField("ID", "Number", true);
                                        } while($db->nextRecord());

                                        $sSQL_price_where[] = " AND module_form_pricelist.ID IN (" . $db->toSql(implode(",", $arrFilterPricelist), "Text", false) . ")";
                                    }                                
                                }                        
                            }

                            if(isset($component->form_fields["how_modify_price"]))
                            {
                                switch ($component->form_fields["how_modify_price"]->getValue()) {
                                    case "add_percent":
                                        $sSQL_price_update = " IF(module_form_pricelist.price + module_form_pricelist.price/100*" . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . " > 0, module_form_pricelist.price + module_form_pricelist.price/100*" . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "subtrack_percent":
                                        $sSQL_price_update = " IF(module_form_pricelist.price - module_form_pricelist.price/100*" . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . " > 0, module_form_pricelist.price - module_form_pricelist.price/100*" . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "add_value":
                                        $sSQL_price_update = " IF(module_form_pricelist.price + " . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . " > 0, module_form_pricelist.price + " . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "subtrack_value":
                                        $sSQL_price_update = " IF(module_form_pricelist.price - " . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . " > 0, module_form_pricelist.price - " . $db->toSql($component->form_fields["value_modify_price"]->getValue(), "Number") . ", 0)";
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }

                        $sSQL = "UPDATE module_form_pricelist SET
                                        price = " . $sSQL_price_update . "
                                    WHERE module_form_pricelist.ID_module = " . $db->toSql($component->user_vars["formcnf-ID"], "Number") . 
                                        (is_array($sSQL_price_where) ? implode(" " , $sSQL_price_where) : "");
                        $db->execute($sSQL);
                    }
                    
                    if(isset($component->form_fields["weight_checkbox"]) && $component->form_fields["weight_checkbox"]->getValue())
                    {
                        if(isset($component->form_fields["value_modify_weight"]) && $component->form_fields["value_modify_weight"]->getValue() > 0)
                        {
                            if(is_array($component->form_fields) && count($component->form_fields)) {
                                $total_field = 0;
                                foreach($component->form_fields AS $key => $value) {
                                    if($key == "choose_weight") {
                                        if($component->form_fields["choose_weight"]->getValue() > 0)
                                            $sSQL_weight_where[] = " AND module_form_pricelist.weight LIKE " . $db->toSql($component->form_fields["choose_weight"]->getValue(), "Number");
                                    } elseif(strpos($key, "choose_weight") === 0) {
                                        if(strlen($component->form_fields[$key]->getValue())) {
                                            $total_field++;
                                            if(strlen($sSQL_pricelist_where))
                                                $sSQL_pricelist_where .= " OR ";
                                            $sSQL_pricelist_where .= "
                                                (
                                                        module_form_pricelist_detail.ID_form_fields = " . $db->toSql($component->form_fields[$key]->user_vars["field"]["ID"], "Number") . "
                                                        AND module_form_pricelist_detail.`value` = " . $db->toSql($component->form_fields[$key]->value) . "
                                                )
                                            ";
                                        }
                                    }
                                    
                                }
                                if(strlen($sSQL_pricelist_where)) {
                                    $sSQL = "SELECT module_form_pricelist.ID, COUNT(module_form_pricelist.ID) AS count_id
                                                FROM module_form_pricelist_detail
                                                    INNER JOIN module_form_pricelist ON module_form_pricelist.ID = module_form_pricelist_detail.ID_form_pricelist
                                               WHERE (" . $sSQL_pricelist_where . ")
                                                    AND module_form_pricelist.ID_module = " . $db->toSql($component->user_vars["formcnf-ID"], "Number") . "
                                               GROUP BY module_form_pricelist.ID
                                               HAVING count_id = " . $total_field ."
                                               ORDER BY module_form_pricelist.ID";
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $arrFilterPricelist = array();
                                        do {
                                            $arrFilterPricelist[$db->getField("ID", "Number", true)] = $db->getField("ID", "Number", true);
                                        } while($db->nextRecord());

                                        $sSQL_weight_where[] = " AND module_form_pricelist.ID IN (" . $db->toSql(implode(",", $arrFilterPricelist), "Text", false) . ")";
                                    }                                
                                }                        
                            }
                            
                            if(isset($component->form_fields["how_modify_weight"]))
                            {
                                switch ($component->form_fields["how_modify_weight"]->getValue()) {
                                    case "add_percent":
                                        $sSQL_weight_update = " IF(module_form_pricelist.weight + module_form_pricelist.weight/100*" . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . " > 0, module_form_pricelist.weight + module_form_pricelist.weight/100*" . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "subtrack_percent":
                                        $sSQL_weight_update = " IF(module_form_pricelist.weight - module_form_pricelist.weight/100*" . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . " > 0, module_form_pricelist.weight - module_form_pricelist.weight/100*" . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "add_value":
                                        $sSQL_weight_update = " IF(module_form_pricelist.weight + " . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . " > 0, module_form_pricelist.weight + " . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . ", 0)";
                                        break;
                                    case "subtrack_value":
                                        $sSQL_weight_update = " IF(module_form_pricelist.weight - " . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . " > 0, module_form_pricelist.weight - " . $db->toSql($component->form_fields["value_modify_weight"]->getValue(), "Number") . ", 0)";
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }

                        $sSQL = "UPDATE module_form_pricelist SET
                                        weight = " . $sSQL_weight_update . "
                                    WHERE module_form_pricelist.ID_module = " . $db->toSql($component->user_vars["formcnf-ID"], "Number") . 
                                        (is_array($sSQL_weight_where) ? implode(" " , $sSQL_weight_where) : "");
                        $db->execute($sSQL);
                    }
                }
                break;
            default:
                break;
        }
    }
    
}