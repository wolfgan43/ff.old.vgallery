<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
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
										, " . $db_gallery->toSql("anagraph") . " AS type
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
										, " . $db_gallery->toSql("user") . " AS type
								FROM cm_mod_security_users_fields
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db_gallery->toSql("city") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "city") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db_gallery->toSql("province") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "province") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db_gallery->toSql("region") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "region") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db_gallery->toSql("state") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db_gallery->toSql(FF_SUPPORT_PREFIX . "state") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION
							(
								SELECT vgallery_fields.ID
									, vgallery_fields.name AS name
									, vgallery_type.name AS group_name
									, " . $db_gallery->toSql("vgallery") . " AS type
								FROM vgallery_fields 
									INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
								ORDER BY LOWER(vgallery_type.name),LOWER(vgallery_fields.name)
							)
						) AS tbl_src 
						ORDER BY type, name
					) AS macro_tbl
				[WHERE]
				ORDER BY macro_tbl.type, macro_tbl.name";

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormConfigSelectionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("form_selection_modify");
$oRecord->src_table = "module_form_fields_selection";

if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "formsel-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name");
$oField->required = true;
$oRecord->addContent($oField);

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "vgalleryType";
$oField->data_source = "ID_vgallery_type";
$oField->label = ffTemplate::_get_word_by_code("form_selection_type_vgallery");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT 
                        vgallery_type.ID
                        , vgallery_type.name
                    FROM vgallery_type
                    ORDER BY LOWER(vgallery_type.name)
                ";
//$oField->required = true;
$oField->actex_update_from_db = true;
$oField->actex_child = "vgalleryField";
$oRecord->addContent($oField);
  
$oField = ffField::factory($cm->oPage);
$oField->id = "vgalleryField";
$oField->data_source = "ID_vgallery_fields";
$oField->label = ffTemplate::_get_word_by_code("form_selection_field_vgallery");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT 
                        vgallery_fields.ID
                        , vgallery_fields.name
                        , vgallery_fields.ID_type
                    FROM vgallery_fields 
                    [WHERE]
                    ORDER BY LOWER(vgallery_fields.name)";   
$oField->actex_father = "vgalleryType";
$oField->actex_related_field = "ID_type";
$oField->actex_update_from_db = true;
//$oField->required = true;
$oRecord->addContent($oField);
*/

$oField = ffField::factory($cm->oPage);
$oField->id = "selectionSource";
$oField->label = ffTemplate::_get_word_by_code("form_selection_source");
$oField->widget = "activecomboex";
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
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("addCustomSource");
$oField->actex_on_update_bt = 'function(obj, old_value) {
								if(obj["value"]) {
									jQuery("#form-config-selection-value").parents(".row").hide();
								} else {
									jQuery("#form-config-selection-value").parents(".row").show();
								}
							}';
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "field";
$oField->label = ffTemplate::_get_word_by_code("form_selection_field"); 
$oField->widget = "activecomboex";
$oField->source_SQL = $query_string;
$oField->actex_father = "selectionSource";
$oField->actex_related_field = "type";
$oField->actex_group = "group_name";
$oField->actex_hide_empty = true; 
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_form_fields_father";
$oField->label = ffTemplate::_get_word_by_code("register_config_fields_father");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, name FROM module_form_fields_selection WHERE ID <> " . $db_gallery->toSql($_REQUEST["keys"]["formsel-ID"], "Number") . " ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_form_fields_child";
$oField->label = ffTemplate::_get_word_by_code("register_config_fields_child");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, name FROM module_form_fields_selection WHERE ID <> " . $db_gallery->toSql($_REQUEST["keys"]["formsel-ID"], "Number") . " ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oDetail_fields = ffDetails::factory($cm->oPage);
$oDetail_fields->id = "form-config-selection-value";
$oDetail_fields->title = ffTemplate::_get_word_by_code("form_selection_name_value");
$oDetail_fields->src_table = "module_form_fields_selection_value";
$oDetail_fields->order_default = "order";
$oDetail_fields->fields_relationship = array ("ID_selection" => "formsel-ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_fields->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_name");
$oField->required = true;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_order");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "qta";
$oField->label = ffTemplate::_get_word_by_code("form_fields_qta");
$oField->base_type = "Number";
$oField->default_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_price");
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "vat";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_vat");
$oField->base_type = "Number";
$oField->default_value = new ffData("20", "Number");
$oDetail_fields->addContent($oField);

$oRecord->addContent($oDetail_fields);
$cm->oPage->addContent($oDetail_fields);

?>
