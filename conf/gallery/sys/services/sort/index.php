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
if(!mod_security_check_session(false) || get_session("UserNID") == MOD_SEC_GUEST_USER_ID) {
	prompt_login();
}

if(strpos($_REQUEST["resource"], "mod_") === 0) {
	$arrModule = explode("_", $_REQUEST["resource"]);
	if(is_dir(FF_DISK_PATH . "/modules/" . $arrModule[1])) {
		$tbl_name = CM_TABLE_PREFIX . $_REQUEST["resource"];
		$_REQUEST["resource"] = "module";
	}
}

$db = ffDB_Sql::factory();
$resources = array(				
	 "layout_layer" => array(
							"tbl_name" => "layout_layer"
                            , "sort" => "order"
                             , "normalize_sort" => "`order`, ID"
						)
	,"layout_location" => array(
							"tbl_name" => "layout_location"
                            , "sort" => "interface_level"
                            , "normalize_sort" => "`interface_level`, ID"
						)
	,"layout" => array(
							"tbl_name" => "layout"
                            , "sort" => "order"
                            , "addsql" => " AND ID_location = " . $db->toSql($_REQUEST["location"], "Number")
                            , "normalize_sort" => "`order`, ID"
						)
	,"javascript" => array(
							"tbl_name" => "js"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, ID"
                        )
    , "vgallery_nodes" => array(
							"tbl_name" => "vgallery_nodes"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, ID"
                            , "addsql" => " AND NOT(is_dir > 0) "
                        )
    , "vgallery_dir" => array(
							"tbl_name" => "vgallery_nodes"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, ID"
                            , "addsql" => " AND is_dir > 0 "
                        )
    , "static_dir" => array(
							"tbl_name" => "static_pages"
                            , "sort" => "sort"
                            , "normalize_sort" => "`sort`, ID"
                        )                        					
    , "module" => array(
							"tbl_name" => $tbl_name
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, ID"
                        )
	, "cart_field" => array(
							"tbl_name" => "ecommerce_order_addit_field"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, ID"
						)
    , "form_fields"	=> array(
                            "tbl_name" => "module_form_fields"
                            , "sort" => "order"
                            , "normalize_sort" => "IFNULL((SELECT module_form_fields_group.`order` FROM module_form_fields_group WHERE module_form_fields_group.ID = module_form_fields.ID_form_fields_group), 0), `order`, name"
                        ) 
    , "register_fields" => array(
                            "tbl_name" => "module_register_fields"
                            , "sort" => "order"
                            , "normalize_sort" => "IFNULL((SELECT module_form_fields_group.`order` FROM module_form_fields_group WHERE module_form_fields_group.ID = module_register_fields.ID_form_fields_group), 0), `order`, name"
                        ) 
    , "publishing_fields" => array(
                            "tbl_name" => "publishing_fields"
                            , "sort" => "order_thumb"
                            , "normalize_sort" => "`order_thumb`, `ID`"
                        )
	, "anagraph_fields_thumb" => array(
							"tbl_name" => "anagraph_fields"
                            , "sort" => "order_thumb"
                            , "normalize_sort" => "`order_thumb`, `ID`"
						)
	, "anagraph_fields_detail" => array(
							"tbl_name" => "anagraph_fields"
                            , "sort" => "order_detail"
                            , "normalize_sort" => "`order_detail`, `order_thumb`, `ID`"
						)
	, "anagraph_fields_backoffice" => array(
							"tbl_name" => "anagraph_fields"
                            , "sort" => "order_backoffice"
                            , "normalize_sort" => "`order_backoffice`, `order_thumb`, `ID`"
						)
	, "vgallery_fields_thumb" => array(
							"tbl_name" => "vgallery_fields"
                            , "sort" => "order_thumb"
                            , "normalize_sort" => "`order_thumb`, `ID`"
						)
	, "vgallery_fields_detail" => array(
							"tbl_name" => "vgallery_fields"
                            , "sort" => "order_detail"
                            , "normalize_sort" => "`order_detail`, `order_thumb`, `ID`"
						)
	, "vgallery_fields_backoffice" => array(
							"tbl_name" => "vgallery_fields"
                            , "sort" => "order_backoffice"
                            , "normalize_sort" => "`order_backoffice`, `order_thumb`, `ID`"
						)
	, "vgallery_type_group_thumb" => array(
							"tbl_name" => "vgallery_type_group"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                            , "addsql" => " AND `type` = 'thumb' "
						)
	, "vgallery_type_group_detail" => array(
							"tbl_name" => "vgallery_type_group"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                            , "addsql" => " AND `type` = 'detail' "
						)
	, "vgallery_type_group_backoffice" => array(
							"tbl_name" => "vgallery_type_group"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                            , "addsql" => " AND `type` = 'backoffice' "
						)
    , "publishing_node" => array(
                            "tbl_name" => "rel_nodes"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                            , "addsql" => " AND contest_dst = 'publishing'"
                        )
    , "vgallery_selection" => array(
                            "tbl_name" => "vgallery_fields_selection_value"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `name`"
                        )
                        
    , "search_tags_categories" => array(
                            "tbl_name" => "search_tags_categories"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                        )
    , "search_tags_group" => array(
                            "tbl_name" => "search_tags_group"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `ID`"
                        )
    , "search_tags_group_overview" => array( 
                            "tbl_name" => "search_tags_group"
                            , "sort" => "overview_order"
                            , "normalize_sort" => "`overview_order`, `ID`"
                        )
    , "uploadifive" => array(
                        	"tbl_name" => "files"
                            , "sort" => "order"
                            , "normalize_sort" => "`order`, `name`"
                            , "addsql" => " AND parent = " . $db->toSql($cm->real_path_info) . " AND is_dir = 0"
                            , "field" => "name"
                            , "insertMissing" => array(
                                "parent" => $cm->real_path_info
                                , "is_dir" => 0
                                , "owner" => get_session("UserNID")
                                , "created" => time()
                                , "last_update" => time()
                                , "ID_domain" => mod_security_get_domain()
                            )
    )
);

$resources = cache_get_settings("services", "sort", $resources);

$res = $cm->doEvent("vg_on_sort_grid", array(&$resources));        
					
if(!isset($_REQUEST["resource"]) && strlen(basename($cm->real_path_info))) {
	$_REQUEST["resource"] = basename($cm->real_path_info);
}

if (!isset($_REQUEST["resource"]) || !isset($_REQUEST["positions"]) || !count($_REQUEST["positions"]))
{
	http_response_code(400);
	exit;
}

$resource = $resources[$_REQUEST["resource"]];
$positions = $_REQUEST["positions"];
if(!is_array($positions)) {
    if(strpos($positions, ",") !== false) {
	    $positions = explode(",", $positions);
    } else {
	    $positions = array();
    }
}
$resources = $_REQUEST["resources"];
     /*
switch ($_REQUEST["resource"])
{
	case "todo_detail":
		if (isset($_REQUEST["keys"]["ID"]))
		{
			$addSql = " WHERE id_todo = " . $db->toSql($_REQUEST["keys"]["ID"]);
			$addPartSql = " AND id_todo = " . $db->toSql($_REQUEST["keys"]["ID"]);
		}
		else
		{
			$tmp = $positions[0];
			$IDPARENT = $db->lookup("todo_detail", "ID", new ffData($tmp), null, "id_todo");
			$addSql = " WHERE id_todo = " . $db->toSql($IDPARENT);
			$addPartSql = " AND id_todo = " . $db->toSql($IDPARENT);
		}
		break;
}   */


if(is_array($resource) && count(is_array($resource))) {
	switch (strtoupper($_SERVER['REQUEST_METHOD']))
	{
		case "POST":
		case "GET":
			$res = array();
			$bad_request = false;

			if(is_array($positions) && count($positions)) {
				if(!$resource["field"])
					$resource["field"] = "ID";
				if(!$resource["base_type"] && $resource["field"] == "ID")
					$resource["base_type"] = "Number";

				foreach ($positions as $key => $value)
				{
					if(strlen($value))
					{
						if(strlen($strPosition))
							$strPosition .= ",";

						$strPosition .= $db->toSql($value, $resource["base_type"]);							
					}
				} reset($positions);	
			}
			if(strlen($strPosition)) {
				if($resource["insertMissing"]) {
					$position_missing = array_flip($positions);

					$sSQL = "SELECT " . $resource["tbl_name"] . ".* 
							FROM " . $resource["tbl_name"] . "
							WHERE " . $resource["tbl_name"] . "." . $resource["field"] . " IN (" . $strPosition . ")
								" . $resource["addsql"] . "
							ORDER BY " . $resource["tbl_name"] . ".`" . $resource["sort"] . "`, " . $resource["tbl_name"] . ".ID";
					$db->query($sSQL);
					if($db->nextRecord()) {
						do {
							$position_key = $db->getField($resource["field"], $resource["base_type"], true);
							if(array_key_exists($position_key, $position_missing))
								unset($position_missing[$position_key]);

						} while($db->nextRecord());				
					}

					if(is_array($position_missing) && count($position_missing)) {
						$arrSQLField_key = array();
						$arrSQLField_value = array();
						if(is_array($resource["insertMissing"]) && count($resource["insertMissing"])) {
							foreach($resource["insertMissing"] AS $missing_key => $missing_value) {
								$arrSQLField_key[$missing_key] = "`" . $missing_key . "`";
								$arrSQLField_value[$missing_key] = $db->toSql($missing_value);
							}
						}
						$arrSQLField_key[$resource["field"]] = "`" . $resource["field"] . "`";
						foreach($position_missing AS $position_missing_key => $position_missing_value) {
							$arrSQLField_value[$resource["field"]] = $db->toSql($position_missing_key);

							$sSQL = "INSERT INTO " . $resource["tbl_name"] . "
									(
										" . implode(", ", $arrSQLField_key) . "
									)
									VALUES
									(
										" . implode(", ", $arrSQLField_value) . "
									)";
							$db->execute($sSQL);
						}
					}
				}	
			}		
			//normalize sorting
			if(array_key_exists("normalize_sort", $resource)) {
				$sSQL = "SELECT COUNT(" . $resource["tbl_name"] . ".`" . $resource["sort"] . "`) AS max_sort
						FROM " . $resource["tbl_name"] . "
						WHERE 1
							" . $resource["addsql"] . "
						GROUP BY " . $resource["tbl_name"] . ".`" . $resource["sort"] . "`
						HAVING max_sort > 1
						ORDER BY " . $resource["normalize_sort"];
				$db->query($sSQL);
				if($db->nextRecord()) {
					$sSQL = "UPDATE " . $resource["tbl_name"] . " c
							    JOIN (
							        SELECT ID, (@row:=@row+1) rowNum
							        FROM " . $resource["tbl_name"] . ", (SELECT @row:=1) dm
							        ORDER BY " . $resource["normalize_sort"] . "
							    ) rs ON c.ID = rs.ID
							SET c.`" . $resource["sort"] . "` = rs.rowNum
							WHERE 1 " . $resource["addsql"];
					$db->execute($sSQL);
				}
			}
			if(strlen($strPosition)) {
				$sSQL = "SELECT " . $resource["tbl_name"] . ".* 
						FROM " . $resource["tbl_name"] . "
						WHERE " . $resource["tbl_name"] . "." . $resource["field"] . " IN (" . $strPosition . ")
							" . $resource["addsql"] . "
						ORDER BY " . $resource["tbl_name"] . ".`" . $resource["sort"] . "`, " . $resource["tbl_name"] . ".ID";
				$db->query($sSQL);
				if($db->nextRecord()) {
					$old_positions = array();
					do {
						if(array_search($db->getField($resource["sort"], "Number", true), $old_positions) === false) {
							$old_positions[$db->getField($resource["field"], $resource["base_type"], true)] = $db->getField($resource["sort"], "Number", true);
						} else {
							$old_positions[$db->getField($resource["field"], $resource["base_type"], true)] = null;
						}
					} while($db->nextRecord());
//print_r($old_positions);
					if(is_array($old_positions) && count($old_positions)) {
						$key_old_positions = array_keys($old_positions);
						foreach ($positions as $key => $value)
						{
							if(array_key_exists($value, $old_positions)) {
								if($key_old_positions[$key] != $value) {
									$new_sort = $old_positions[$key_old_positions[$key]];
									$sSQL = "UPDATE " . $resource["tbl_name"] . " SET `" . $resource["sort"] . "` = " . $db->toSql($new_sort) . " WHERE `" . $resource["field"] . "` = " . $db->toSql($value) . $resource["addsql"];
									$db->execute($sSQL);
									//echo $sSQL . "<br>";
									$res[] = array("replace" => $key_old_positions[$key], "with" => $value);
								}
							} else {
								$sSQL = "UPDATE " . $resource["tbl_name"] . " SET `" . $resource["sort"] . "` = $key WHERE `" . $resource["field"] . "` = " . $db->toSql($value) . $resource["addsql"];
								$db->execute($sSQL);
							}
						} reset($positions);				
					}
				} else {
					$sSQL = "UPDATE " . $resource["tbl_name"] . " SET `" . $resource["sort"] . "` = " . $db->toSql(new ffData(count($positions))) . "WHERE 1 " . $resource["addsql"];
					$db->execute($sSQL);
				}
			}
			
			if ($bad_request)
				http_response_code(400);
			else
				http_response_code(200);

			echo ffCommon_jsonenc($res, true);
			exit;
			break;
			
		case "GET":
			$tpl = ffTemplate::factory($cm->oPage->getThemeDir() . "/contents/services");
			$tpl->load_file("resources.xml", "main");
			$tpl->set_var("nomerisorsa", $resource);
			
			$sSQL = "SELECT * FROM " . $resource["tbl_name"] . $addSql . " ORDER BY `order`";
			$db->query($sSQL);
			if ($db->nextRecord())
			{
				$i = 0;
				do
				{
					$tpl->set_var("id", htmlentities($db->getField("ID", "Number")->getValue()));
					$tpl->set_var("name", htmlentities($db->getField("name")->getValue()));
					$tpl->set_var("order", htmlentities($i));
					$tpl->parse("SectResource", true);
					$i++;
				} while ($db->nextRecord());
			}
			
			http_response_code(200);
			header("Content-type: text/xml");
			echo $tpl->rpparse("main", false);
			exit;
			break;
			
		default:
			http_response_code(400);
			exit;
	}
} else {
	http_response_code(500);
}
exit;