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
$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_form");

$sSQL = "SELECT module_form_pricelist_detail.*
                , module_form_fields.name
                , extended_type.ff_name AS ff_extended_type
                , module_form.enable_ecommerce_weight AS enable_ecommerce_weight
				, module_form.name AS form_name
            FROM module_form_pricelist_detail 
                INNER JOIN module_form_pricelist ON module_form_pricelist.ID = module_form_pricelist_detail.ID_form_pricelist
                INNER JOIN module_form_fields ON module_form_fields.ID = module_form_pricelist_detail.ID_form_fields
                INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
                INNER JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
            WHERE module_form_pricelist.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
            GROUP BY module_form_pricelist_detail.ID_form_fields";
$db->query($sSQL);
if($db->nextRecord())
{
    $sSQL_field = "";
    $enable_ecommerce_weight = $db->getField("enable_ecommerce_weight", "Number", true);
	$module_form_title = ffTemplate::_get_word_by_code("modify_module_form_pricelist") . ": " . $db->getField("form_name", "Text", true);
    do {
        $ID_pricelist = $db->getField("ID_form_pricelist", "Number", true);
        $ID = $db->getField("ID", "Number", true);
        $ID_field = $db->getField("ID_form_fields", "Number", true);
        $field_name = $db->getField("name", "Text", true);
        $field_name_smart_url = ffCommon_url_rewrite($field_name);
        $ID_form_pricelist = $db->getField("ID_form_pricelist", "Number", true);
        $arrPricelist[$ID_field]["smart_url"] = $field_name_smart_url;
        $arrPricelist[$ID_field]["name"] = $field_name;
        $arrPricelist[$ID_field]["ff_extended_type"] = $db->getField("ff_extended_type", "Number", true);
        
        $sSQL_field .= ", (
                            SELECT module_form_pricelist_detail.value
                            FROM module_form_pricelist_detail
                            WHERE module_form_pricelist_detail.ID_form_pricelist = module_form_pricelist.ID
                            	AND module_form_pricelist_detail.ID_form_fields = " . $db->toSql($ID_field, "Number") . "
                            LIMIT 1
                        ) AS `" . $field_name_smart_url ."`";
    } while($db->nextRecord());
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "FormPricelist"; 
$oGrid->source_SQL = "SELECT module_form_pricelist.*
                        $sSQL_field
                        FROM module_form_pricelist
                        WHERE module_form_pricelist.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                            [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->full_ajax = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/pricelist/modify";
$oGrid->record_id = "FormPricelistModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->addEvent("on_do_action", "FormPricelist_on_do_action");
$oGrid->addEvent("on_before_parse_row", "FormPricelist_on_before_parse_row");

/**
* Title
*/
system_ffcomponent_set_title(
    ffTemplate::_get_word_by_code("form_pricelist_title")
    , true
    , false
    , false
    , $oGrid
);


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "checkbox";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
$oField->data_type = "";
$oField->control_type = "checkbox";
$oField->default_value = new ffData("0");
$oGrid->addContent($oField);

if(is_array($arrPricelist) && count($arrPricelist)) {
    foreach($arrPricelist AS $arrPricelist_key => $arrPricelist_value) {
		$oField = ffField::factory($cm->oPage);
		$oField->base_type = $arrPricelist_value["ff_extended_type"];
        $oField->id = $arrPricelist_value["smart_url"];
        $oField->label = $arrPricelist_value["name"]; //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
        $oGrid->addContent($oField); 
    } 
}

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_price");
$oGrid->addContent($oField);

if($enable_ecommerce_weight) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "weight";
	$oField->label = ffTemplate::_get_word_by_code("module_form_pricelist_weight");
	$oGrid->addContent($oField);
}

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "import";
$oButton->ajax = $oGrid->record_id;
$oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_import");
$oButton->aspect = "link";
$oButton->action_type = "gotourl"; 
$oButton->url = $cm->oPage->site_path . STAGE_ADMIN . "/utility/import/form/pricelist?keys[ID]=" . $_REQUEST["keys"]["ID"];
$oGrid->addActionButtonHeader($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "modify_selected";
$oButton->ajax = $oGrid->record_id;
$oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_modify_all");
$oButton->aspect = "link";
$oButton->class = "modify-selected";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/pricelist/modify-selected?keys[formcnf-ID]=" . $_REQUEST["keys"]["ID"];
$oGrid->addActionButtonHeader($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "clone_selected";
$oButton->ajax = $oGrid->record_id;
$oButton->class = "prl-btn";
$oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_clone_selected");
$oButton->action_type = "submit";
$oButton->frmAction = "clone";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/pricelist";
$oButton->aspect = "link";
$oGrid->addActionButtonHeader($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "delete_selected";
$oButton->ajax = $oGrid->record_id;
$oButton->class = "prl-btn";
$oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_delete_selected");
$oButton->action_type = "submit";
$oButton->frmAction = "multidelete";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/pricelist";
$oButton->aspect = "link";
$oGrid->addActionButtonHeader($oButton);

if($cm->oPage->isXHR()) {
	if(strlen($_REQUEST["XHR_CTX_ID"])) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "ActionButtonCancel";
		$oButton->action_type = "submit";
		$oButton->jsaction = "ff.ffPage.dialog.doAction('" . $_REQUEST["XHR_CTX_ID"] . "', 'close');";
		$oButton->label = ffTemplate::_get_word_by_code("bt_close");
		$oButton->aspect = "link";
		$oGrid->addActionButton($oButton);
	}
    
}
			
$js_control =  
'<script type="text/javascript">
	controlCheckbox();
	jQuery(document).on("click", "#' . $oGrid->id . ' input[type=checkbox]", function() {
		controlCheckbox();
		modifyUrl();
	});
	
	function controlCheckbox() {
		jQuery(".prl-btn").hide();
		if(jQuery("#FormPricelist_discl_sect input[type=checkbox]:checked").length){
			jQuery(".prl-btn").show();
			jQuery(".modify-selected").text("' . ffTemplate::_get_word_by_code("FormPricelist_modify_selected") . '");
		} else {
			jQuery(".modify-selected").text("' . ffTemplate::_get_word_by_code("FormPricelist_modify_all") . '");
		}
	};
	
	function modifyUrl() {
		var url = ff.ffPage.dialog.dialog_params.get("modify_selected");
		if(url["url"].indexOf("list-ID")>0) {
			var params = url["url"].substr(url["url"].indexOf("keys[ID]"));
			url["url"] = url["url"].substr(0,url["url"].indexOf("list-ID")) + params;
		}
		
		stringID = "";
		jQuery("#FormPricelist_discl_sect input[type=checkbox]:checked").each(function() {
			if(jQuery(this).attr("data-rel") !== undefined)
			{
				if(stringID.length > 0)
					stringID = stringID + "-";
				stringID = stringID+jQuery(this).attr("data-rel");
			}
		});
		
		url["url"] = ff.urlAddParam(url["url"],"list-ID",stringID);
		ff.ffPage.dialog.dialog_params.set("modify_selected",url);
	};
</script>';
$cm->oPage->addContent($js_control);
$cm->oPage->addContent($oGrid);

function FormPricelist_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	$cm = cm::getInstance();
	if($action)
	{
		switch ($action) {
			case "clone" : 
				if(is_array($component->recordset_keys) && count($component->recordset_keys))
				{
					foreach($component->recordset_keys AS $ID_field_pricelist => $value_pricelist)
					{
						$sSQL = "INSERT INTO module_form_pricelist
								(
									`ID`
									, `ID_form`
									, `price`
									, `weight`
									, `cloned`
								)
								SELECT 
									null
									, `ID_form`
									, `price`
									, `weight`
									, 1
								FROM module_form_pricelist
								WHERE module_form_pricelist.ID = " . $db->toSql($value_pricelist["ID"], "Number");
						$db->execute($sSQL);
						$arrFormPricelistClone = $db->getInsertID(true);
						
						$sSQL = "INSERT INTO module_form_pricelist_detail
									(
										`ID`
										, `ID_form_pricelist`
										, `ID_form_fields`
										, `value`
									)
									SELECT 
										null
										, " . $db->toSql($arrFormPricelistClone, "Number") . "
										, `ID_form_fields`
										, `value`
									FROM module_form_pricelist_detail
									WHERE module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($value_pricelist["ID"], "Number");
						$db->execute($sSQL);
					}
				}
				break;
			case "multidelete": 
				if(is_array($component->recordset_keys) && count($component->recordset_keys))
				{
					foreach($component->recordset_keys AS $ID_field_pricelist => $value_pricelist)
					{
						$sSQL = "DELETE FROM module_form_pricelist_detail
								WHERE module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($value_pricelist["ID"], "Number");
						$db->execute($sSQL);

						$sSQL = "DELETE FROM module_form_pricelist
									WHERE module_form_pricelist.ID = " . $db->toSql($value_pricelist["ID"], "Number");
						$db->execute($sSQL);
					}
				}
				break;
			
			default:
				break;
		}
	}
	
}

function FormPricelist_on_before_parse_row($component) {
	$component->grid_fields["checkbox"]->properties["data-rel"] = $component->key_fields["ID"]->getValue();
	if($component->db[0]->record["cloned"])
	{
		$component->row_class = "cloned";
	}
}