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
 * @subpackage services
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if(!Auth::isLogged()) {
	exit;
}

$db = ffDB_Sql::factory();

$target = basename($cm->real_path_info);

if(strpos($target, "mod_") === 0) {
	$arrModule = explode("_", $target);
	if(is_dir(FF_DISK_PATH . "/modules/" . $arrModule[1])) {
		$tbl_name = CM_TABLE_PREFIX . $target;
		$target = "module";
	}
}

$arrSpecialCategories = array("all" => " AND 1 "
							, "nocategory" => " AND anagraph.categories = '' "
							, "users" => " AND anagraph.uid > 0 "
						);
$resources = array(				
                        "vgallery_node_fields" => array(
												"tbl_name" => "vgallery_fields"
                                                , "field_check" => "enable_in_grid_check"
                                                , "field_value" => "enable_in_grid"
                                                , "field_name" => "name"
                                                , "field_key" => "ID_type"
                                                , "SQL" => "SELECT DISTINCT
												            vgallery_fields.ID 
												            , CONCAT(vgallery_type.name, ' ', vgallery_fields.name) AS name
												            , vgallery_fields.enable_in_grid AS enable_in_grid
												            , IF(vgallery_fields.enable_in_grid > 0
												            	, 1
												            	, 0
												            ) AS enable_in_grid_check
												        FROM vgallery_fields
												            INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type
												            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
												            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
												            INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
												            INNER JOIN extended_type on extended_type.ID = vgallery_fields.ID_extended_type
												        WHERE
												            (vgallery_nodes.parent = " . $db->toSql($_REQUEST["params"]["real_path_info"])  . " 
												                OR vgallery_nodes.parent LIKE '" . $db->toSql($_REQUEST["params"]["real_path_info"] . "/", "Text", false)  . "%'
												            )
															" . (strpos($_REQUEST["params"]["path_info"], VG_WS_ECOMMERCE) === 0
            													? " AND IF(vgallery.enable_ecommerce_all_level > 0 
												                        , 1
												                        , vgallery.limit_level >= (LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')))
                        													AND NOT(vgallery_nodes.is_dir > 0)
												                    )"
            													: " AND NOT(vgallery_nodes.is_dir > 0) "
												            ) . "
												            AND vgallery_nodes.name <> ''
													        [AND] [WHERE]
												        ORDER BY enable_in_grid, name [COLON] [ORDER]"
                        					)
                        , "vgallery_dir_fields" => array(
												"tbl_name" => "vgallery_fields"
                                                , "field_check" => "enable_in_grid_check"
                                                , "field_value" => "enable_in_grid"
                                                , "field_name" => "name"
                                                , "field_key" => "ID_type"
                                                , "SQL" => "SELECT DISTINCT
												            vgallery_fields.ID 
												            , CONCAT(vgallery_type.name, ' ', vgallery_fields.name) AS name
												            , vgallery_fields.enable_in_grid AS enable_in_grid
												            , IF(vgallery_fields.enable_in_grid > 0
												            	, 1
												            	, 0
												            ) AS enable_in_grid_check
												        FROM vgallery_fields
												            INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type
												            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
												            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
												            INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
												            INNER JOIN extended_type on extended_type.ID = vgallery_fields.ID_extended_type
												        WHERE
												            (vgallery_nodes.parent = " . $db->toSql($_REQUEST["params"]["real_path_info"])  . " 
												                OR vgallery_nodes.parent LIKE '" . $db->toSql($_REQUEST["params"]["real_path_info"] . "/", "Text", false)  . "%'
												            )
															AND vgallery_nodes.is_dir > 0
												            AND vgallery_nodes.name <> ''
												            [AND] [WHERE]
												        ORDER BY enable_in_grid, name [COLON] [ORDER]"
                        					)
                        , "anagraph" => array(
												"tbl_name" => "anagraph_fields"
												, "field_check" => "enable_in_grid_check"
                                                , "field_value" => "enable_in_grid"
                                                , "field_name" => "name"
                                                , "field_key" => "ID_type"
                        						, "SQL" => "SELECT DISTINCT
													            anagraph_fields.ID 
													            , CONCAT(anagraph_type.name, ' ', anagraph_fields.name) AS name
													            , anagraph_fields.enable_in_grid AS enable_in_grid
													            , IF(anagraph_fields.enable_in_grid > 0
												            		, 1
												            		, 0
													            ) AS enable_in_grid_check
													        FROM anagraph_fields
													            INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
													            INNER JOIN anagraph ON anagraph.ID_type = anagraph_fields.ID_type
													            INNER JOIN extended_type on extended_type.ID = anagraph_fields.ID_extended_type
													        WHERE 1
        														" . (strlen($_REQUEST["params"]["category"])
													                ? (array_key_exists($_REQUEST["params"]["category"], $arrSpecialCategories)
													                    ? $arrSpecialCategories[$_REQUEST["params"]["category"]]
													                    : " AND FIND_IN_SET(" . $db->tosql($_REQUEST["params"]["ID_category"], "Number") . ", anagraph.categories) "
													                )
													                : ""
													            ) . "
													            AND anagraph_fields.name NOT IN('name', 'surname', 'tel', 'email')
													            [AND] [WHERE]
													        ORDER BY enable_in_grid, name [COLON] [ORDER]"
                        )
					);

if(array_key_exists($target, $resources)) {
	$resource = $resources[$target];
} else {
	if(isset($_REQUEST["field"])) {
		$resource["tbl_name"] = ($target == "module" ? $tbl_name : $target);
		$resource["field_value"] = $_REQUEST["field"]["value"];
		$resource["field_name"] = $_REQUEST["field"]["name"];
		if(strlen($_REQUEST["field"]["key"]))
			$resource["field_key"] = $_REQUEST["field"]["key"];

	}
}

if (!is_array($resource)
	&& (!isset($_REQUEST["ID"])
		|| !isset($_REQUEST["params"])
	)
) {
	http_response_code(400);
	exit;
}

if(strlen($resource["SQL"])) {
	$sSQL = $resource["SQL"];
	
	if(strlen($resource["field_name"])) {
		$field_name = $resource["field_name"];
	} elseif(strlen($resource["field_key"])) {
		$field_name = $resource["field_key"];
	} else {
		$field_name = "name";
	}
} else {
	if(is_array($resource["field_name"])) {
		foreach($resource["field_name"] AS $field_key => $field_value) {
			$sSQL_field_name .= ", (" . $field_value .") AS `" . $field_key . "`";	
		}
		
		$field_name = $field_key;
	} elseif(strlen($resource["field_name"])) {
		$field_name = $resource["field_name"];
	} elseif(strlen($resource["field_key"])) {
		$field_name = $resource["field_key"];
	} else {
		$field_name = "name";
	}

	if(strlen($resource["field_value"])) {
		$sSQL_field_name .= ", IF(" . $resource["tbl_name"]  . "." . $resource["field_value"] . " > 0
									, 1
									, 0
								) AS " . $resource["field_value"] . "_check";
		
		$resource["field_check"] = $resource["field_value"] . "_check";
	}
	
	$sSQL = "
			SELECT
					  `" . $resource["tbl_name"]  . "`.*
					  $sSQL_field_name
				FROM
					`" . $resource["tbl_name"]  . "`
				WHERE 1
					" . (strlen($resource["field_key"])
						? " AND " . $resource["tbl_name"]  . "." . $resource["field_key"]  . " = " . $db->toSql($_REQUEST["ID"])
						: ""
					) . "
					[AND] [WHERE] 
				ORDER BY " . $resource["field_value"] . " [COLON] [ORDER]";
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "Customize";
$oGrid->title = ffTemplate::_get_word_by_code("customize_fields");
$oGrid->source_SQL = $sSQL;
$oGrid->order_default = "ID";
$oGrid->record_url = "";
$oGrid->record_id = "Customize";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->use_search = false;
$oGrid->use_paging = false;
$oGrid->use_order = false;
$oGrid->full_ajax = true;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_new = false;
$oGrid->buttons_options["export"]["display"] = false;

$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
 			"resource_id" => "Customize_update"
                , "service_path" => ""
        )
        , "ID"
    )
);
$oGrid->addEvent("on_do_action", "Customize_on_do_action");
$oGrid->user_vars["resource"] = $resource;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = $resource["field_check"];
$oField->label = ffTemplate::_get_word_by_code("customize_fields_check");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("customize_fields_name");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "ActionButtonUpdate";
$oButton->label = ffTemplate::_get_word_by_code("ffRecord_update");//Definita nell'evento
$oButton->action_type = "submit";
$oButton->frmAction = "update";
$oButton->aspect = "link";
$oButton->jsaction = "updateCustomize('" . $_REQUEST["XHR_CTX_ID"] . "');";
$oGrid->addActionButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "ActionButtonCancel";
$oButton->label = ffTemplate::_get_word_by_code("ffRecord_close");//Definita nell'evento
if($_REQUEST["XHR_CTX_ID"]) {
    $oButton->action_type     = "submit";
    $oButton->frmAction        = "close";
} else {
    $oButton->action_type = "gotourl";
    $oButton->url = "[RET_URL]";
}
$oButton->aspect = "link";
$oGrid->addActionButton($oButton);


$cm->oPage->addContent($oGrid);

$cm->oPage->tplAddJs("ff.cms.admin.display-field");

//$cm->oPage->addContent($js);
/*
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "Customize";
$oRecord->title = ffTemplate::_get_word_by_code("customize_fields");
$oRecord->addEvent("on_done_action", "Customize_on_done_action");
$oRecord->resources = explode(",", $_REQUEST["resources"]);
$oRecord->allow_delete = false;
$oRecord->allow_update = false;
$oRecord->skip_action = true;
$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("bt_update");
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->user_vars["resource"] = $resource;

//campo chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


if(strlen($resource["SQL"])) {
	$sSQL = $resource["SQL"];
	
	if(strlen($resource["field_name"])) {
		$field_name = $resource["field_name"];
	} elseif(strlen($resource["field_key"])) {
		$field_name = $resource["field_key"];
	} else {
		$field_name = "name";
	}
} else {
	if(is_array($resource["field_name"])) {
		foreach($resource["field_name"] AS $field_key => $field_value) {
			$sSQL_field_name .= ", (" . $field_value .") AS `" . $field_key . "`";	
		}
		
		$field_name = $field_key;
	} elseif(strlen($resource["field_name"])) {
		$field_name = $resource["field_name"];
	} elseif(strlen($resource["field_key"])) {
		$field_name = $resource["field_key"];
	} else {
		$field_name = "name";
	}

	$sSQL = "
			SELECT
					  `" . $resource["tbl_name"]  . "`.*
					  $sSQL_field_name
				FROM
					`" . $resource["tbl_name"]  . "`
				WHERE 1
					" . (strlen($resource["field_key"])
						? " AND " . $resource["tbl_name"]  . "." . $resource["field_key"]  . " = " . $db->toSql($_REQUEST["ID"])
						: ""
					);
}
$db->query($sSQL);
if ($db->nextRecord())
{
	$i = 0;
	do
	{
		$i++;

		$oField = ffField::factory($cm->oPage);
		$oField->id = "field" . $db->getField("ID", "Text", true);
		$oField->label = $db->getField($field_name, "Text", true);
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->base_type = "Text";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1");
		$oField->unchecked_value = new ffData("0");
		$oField->value = $db->getField($resource["field_check"]);
		$oField->default_value = $db->getField($resource["field_check"]);
		$oField->user_vars["ID"] =  $db->getField("ID", "Text", true);
		$oField->user_vars["order"] = $i;
		$oRecord->addContent($oField);
	} while ($db->nextRecord());

	$cm->oPage->addContent($oRecord);
} else {
	die(ffCommon_jsonenc(array("resources" => explode(",", $_REQUEST["resources"]), "close" => true, "refresh" => true), true));
}*/






function Customize_on_do_action($component, $frmAction)
{
	$db = ffDB_Sql::factory();
	$cm = cm::getInstance();

	if(isset($_REQUEST["pos"])) {
		$pos = explode("," , $_REQUEST["pos"]);
	}
	if(isset($_REQUEST["nopos"])) {
		$nopos = $_REQUEST["nopos"];
	}
	
	
	if ($frmAction == "update")
	{
		if(is_array($pos) && count($pos)) {
			foreach($pos AS $key => $value) {
				$sSQL = "UPDATE " . $component->user_vars["resource"]["tbl_name"] . " SET 
							`" . $component->user_vars["resource"]["tbl_name"] . "`.`" . $component->user_vars["resource"]["field_value"] . "` = " . $db->toSql($key + 1) . "
						WHERE `" . $component->user_vars["resource"]["tbl_name"] . "`.`ID` = " . $db->toSql($value, "Number");
				$db->execute($sSQL);
				//echo $sSQL . "<br>";
			}
		}
		if(strlen($nopos)) {
			$sSQL = "UPDATE " . $component->user_vars["resource"]["tbl_name"] . " SET 
						`" . $component->user_vars["resource"]["tbl_name"] . "`.`" . $component->user_vars["resource"]["field_value"] . "` = 0
					WHERE `" . $component->user_vars["resource"]["tbl_name"] . "`.`ID` IN (" . $db->toSql($nopos, "Text", false) . ")";
			$db->execute($sSQL);
			//echo $sSQL . "<br>";
		}
		die(ffCommon_jsonenc(array("update_all" => true, "resources" => explode(",", $_REQUEST["resources"]), "close" => true, "refresh" => true), true));
	}
	return FALSE;
}

