<?php
$db = ffDB_Sql::factory();

$sSQL = "SELECT module_form_fields.* 
            , module_form_fields_selection_value.ID AS ID_selection
            , extended_type.name AS extended_type_name
            , extended_type.ff_name AS ff_extended_type
            , module_form_pricelist_detail.value AS pricelist_value
            , module_form.enable_ecommerce_weight AS enable_ecommerce_weight
			, module_form.name AS form_name
            FROM module_form_fields
				INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
                LEFT JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                LEFT JOIN module_form_fields_selection_value ON module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
                LEFT JOIN module_form_pricelist_detail ON module_form_pricelist_detail.ID_form_fields = module_form_fields.ID
                    AND module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
            WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
                AND module_form_fields.type = " . $db->toSql("pricelist") . "
            GROUP BY `ID`
            ORDER BY module_form_fields.`order`, module_form_fields.name";
$db->query($sSQL);
if($db->nextRecord()) {
	if(isset($_REQUEST["keys"]["ID"]))
		$module_form_title = ffTemplate::_get_word_by_code("modify_module_form_pricelist_record");
	else
		$module_form_title = ffTemplate::_get_word_by_code("addnew_module_form_pricelist_record");
    $enable_ecommerce_weight = $db->getField("enable_ecommerce_weight", "Number", true);
	
    do {
        $name = $db->getField("name", "Text", true);
        $smart_url_name = ffCommon_url_rewrite($name);
        $ID = $db->getField("ID", "Number", true);
		$arrPricelist[$ID]["name"] = $name;
        $arrPricelist[$ID]["value"] = $db->getField("pricelist_value", "Text", true);
        $arrPricelist[$ID]["field"]["ID"] = $ID;
		$arrPricelist[$ID]["field"]["name"] = $name;
        $arrPricelist[$ID]["field"]["form"]["type"] = $db->getField("type", "Text", true);
        $arrPricelist[$ID]["field"]["extended_type"] = $db->getField("extended_type_name", "Text", true);
        $arrPricelist[$ID]["ff_extended_type"] = $db->getField("ff_extended_type", "Text", true);
       
    } while($db->nextRecord());
}
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormPricelistModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_form_pricelist";
$oRecord->insert_additional_fields["ID_module"] = $_REQUEST["keys"]["formcnf-ID"];
$oRecord->addEvent("on_done_action", "FormExtraPricelist_on_done_action");
$oRecord->user_vars["ID_pricelist"] = $arrPricelist;
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "form")) . $module_form_title . '</h1>';


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


if(is_array($arrPricelist) && count($arrPricelist)) {
	if(check_function("get_field_by_extension"))
    {
        foreach($arrPricelist AS $arrPricelist_key => $arrPricelist_value) {
            $oField = ffField::factory($cm->oPage);

			$js .= get_field_by_extension($oField, $arrPricelist_value["field"], "form");

            $oField->id = $arrPricelist_key;
            $oField->store_in_db = false;
            $oField->data_type = "";
            $oField->label = ffTemplate::_get_word_by_code($arrPricelist_value["name"]); //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
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

if($enable_ecommerce_weight) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "weight";
	$oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight");
	$oField->base_type = "Number";
	$oRecord->addContent($oField);
}

$cm->oPage->addContent($oRecord);

function FormExtraPricelist_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    if(strlen($action))
    {
        switch ($action) {
            case "insert":
            case "update":
				$array_field = array();
                $sSQL = "SELECT module_form_pricelist_detail.*
                            FROM module_form_pricelist_detail
                            WHERE module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                $db->query($sSQL);
                if($db->nextRecord()) 
                {
                    do {
                        $ID = $db->getField("ID_form_fields", "Number", true);
                        $array_field[$ID] = $ID;
                    } while($db->nextRecord());
                }
				
                foreach($component->form_fields AS $ID_form_field => $value_form)
                {
					if(array_key_exists($ID_form_field, $component->user_vars["ID_pricelist"]))
					{
						if(array_key_exists($ID_form_field, $array_field))
						{
							$sSQL = "UPDATE module_form_pricelist_detail
										SET module_form_pricelist_detail.value = " . $db->toSql($value_form->getValue(), "text") . "
										WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($ID_form_field, "Number") . "
											AND module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number"); 
							$db->execute($sSQL);
						} else
						{
							$sSQL = "INSERT INTO module_form_pricelist_detail
											(
													ID
													, ID_form_pricelist
													, ID_form_fields
													, value
											) VALUES (
													null
													, " . $db->toSql($component->key_fields["ID"]->getValue(), "Number") . "
													, " . $db->toSql($ID_form_field, "Number") . "
													, " . $db->toSql($value_form->getValue(), "text") . "
											)";
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