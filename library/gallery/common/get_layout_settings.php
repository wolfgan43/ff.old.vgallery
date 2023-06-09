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
function get_layout_settings($ID_layout = NULL, $layout_type) {
    $db = ffDB_Sql::factory();

    static $res = array();
	$actual_res = array();

	if($ID_layout === null)
		$ID_layout = 0;

    if(is_array($layout_type)) {
		$sql_layout_WHERE = "";
    	$require_loading = false;
        $actual_res["sections"] = array();

    	foreach($layout_type AS $layout_type_key => $layout_type_value) {
    		if(array_key_exists($layout_type[$layout_type_key] . "-" . $ID_layout, $res)) {
    			$actual_res[$layout_type[$layout_type_key] . "-" . $ID_layout] = $res[$layout_type[$layout_type_key] . "-" . $ID_layout];
			} else {
				$require_loading = true;

			    if(is_numeric($layout_type[$layout_type_key]) && $layout_type[$layout_type_key] > 0) {
			        $sql_layout_type = " AND layout_type.ID = " . $db->toSql($layout_type[$layout_type_key], "Number");
			    } else {
			        $sql_layout_type = " AND layout_type.name = " . $db->toSql($layout_type[$layout_type_key], "Text");
			    }

    			if(is_array($ID_layout)) {
    				if(!array_key_exists($layout_type[$layout_type_key] . "-" . $ID_layout[$layout_type_key], $res)) {
						if(strlen($sql_layout_WHERE))
							$sql_layout_WHERE .= " OR ";

    					$sql_layout_WHERE .= " (layout_settings_rel.ID_layout = " . $db->toSql($ID_layout[$layout_type_key], "Number") . "
    												$sql_layout_type
    											)";
					}
				} else {
    				if(!array_key_exists($layout_type[$layout_type_key] . "-" . $ID_layout, $res)) {
						if(strlen($sql_layout_WHERE))
							$sql_layout_WHERE .= " OR ";

    					$sql_layout_WHERE .= " (layout_settings_rel.ID_layout = " . $db->toSql($ID_layout, "Number") . "
    												$sql_layout_type
    											)";
					}
				}

				if(strlen($sql_layout_WHERE))
					$sql_layout_WHERE .= " OR ";

    			$sql_layout_WHERE .= " (layout_settings_rel.ID_layout = 0
    										$sql_layout_type
    									)";
			}
		}
		if($require_loading) {
		    $sSQL = "SELECT layout_settings.name
		                , layout_settings_rel.value
		                , layout_type.name AS layout_type
		                , layout_settings_rel.ID_layout AS ID_layout
                        , layout.ID_location AS ID_location
		            FROM layout_settings_rel
		                LEFT JOIN layout ON layout.ID = layout_settings_rel.ID_layout
		                INNER JOIN layout_settings ON layout_settings.ID = layout_settings_rel.ID_layout_settings
		                INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type
		            WHERE
		                $sql_layout_WHERE
		                ";
		    $db->query($sSQL);
            $recordset = $db->getRecordset();
            foreach ($recordset AS $record) {
                $actual_res[$record["layout_type"] . "-" . $record["ID_layout"]][$record["name"]] = $record["value"];

                $res[$record["layout_type"] . "-" . $record["ID_layout"]][$record["name"]] = $record["value"];
            }
		}
		return $actual_res;
	} else {
	//print_r($ID_layout);
		if(is_array($ID_layout)) {
			foreach($ID_layout AS $layout_ctx => $layout_key) {
				if(array_key_exists($layout_type . "-" . $layout_key, $res)) {
					$actual_res[$layout_ctx] = $res[$layout_type . "-" . $layout_key];
				} else {
                    $arrLayoutRev[$layout_key] = $layout_ctx;
					$arrLayoutNeed[] = $layout_key;
				}
			}
		} elseif(array_key_exists($layout_type . "-" . $ID_layout, $res)) {
			$actual_res[$layout_type . "-" . $ID_layout] = $res[$layout_type . "-" . $ID_layout];
		} elseif($ID_layout > 0) {
			$arrLayoutNeed[] = $ID_layout;
		}

	    if(is_numeric($layout_type) && $layout_type > 0) {
			$sql_layout_type = " AND layout_type.ID = " . $db->toSql($layout_type, "Number");
		} else {
			$sql_layout_type = " AND layout_type.name = " . $db->toSql($layout_type, "Text");
		}

		$sql_layout_WHERE = array();
	    if(is_array($arrLayoutNeed) && count($arrLayoutNeed)) {
    		$sql_layout_WHERE[] = " (layout_settings_rel.ID_layout IN (" . $db->toSql(implode(", ", $arrLayoutNeed), "Number", false) . ")
    									$sql_layout_type
    								)";
		}

		if(!array_key_exists($layout_type . "-" . "0", $res)) {
    		$sql_layout_WHERE[] = " (layout_settings_rel.ID_layout = 0
    									$sql_layout_type
    								)";
		} else {
			$actual_res["/"] = $res[$layout_type . "-" . 0];
		}

		if(count($sql_layout_WHERE)) {
            if(count($arrLayoutRev))
                $arrLayoutRev[0] = "/";

		    $sSQL = "SELECT layout_settings.name
			            , layout_settings_rel.value
			            , layout_type.name AS layout_type
			            , layout_settings_rel.ID_layout AS ID_layout
	                    , layout.ID_location AS ID_location
		            FROM layout_settings_rel
		                LEFT JOIN layout ON layout.ID = layout_settings_rel.ID_layout
		                INNER JOIN layout_settings ON layout_settings.ID = layout_settings_rel.ID_layout_settings
		                INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type
		            WHERE " . implode(" OR ", $sql_layout_WHERE);
		    $db->query($sSQL);
            $recordset = $db->getRecordset();
            foreach ($recordset AS $record) {
                if(is_array($arrLayoutRev) && $arrLayoutRev[$record["ID_layout"]])
                    $actual_key = $arrLayoutRev[$record["ID_layout"]];
                else
                    $actual_key = $record["layout_type"] . "-" . $record["ID_layout"];

                $actual_res[$actual_key][$record["name"]] = $record["value"];
                $res[$record["layout_type"] . "-" . $record["ID_layout"]][$record["name"]] = $record["value"];
            }
		}

		if(is_array($ID_layout))
			return $actual_res;
		else
			return $res[$layout_type . "-" . $ID_layout];
	}
}

function get_layout_by_block($type, $ctx = null, $out = null) {
	$db = ffDB_Sql::factory();
	$layout = null;

	switch($type) {
		case "anagraph":
			$setting_type = "VIRTUAL_GALLERY";
			if(is_array($ctx)) {
				foreach($ctx AS $ctx_value) {
					$arrWhere[] = "anagraph_categories.permalink = " . $db->toSql($ctx_value);
				}
			} elseif(strlen($ctx)) {
				$arrWhere[] = "anagraph_categories.permalink = " . $db->toSql($ctx);
			}
			$sSQL = "SELECT layout.*
						, (" . (is_array($arrWhere) && count($arrWhere)
							? "anagraph_categories.permalink"
							: "''"
						) . ") 																AS ctx
						, layout_path.path 													AS real_path

	                    , layout_path.class 												AS block_class
	                    , layout_path.default_grid 											AS block_default_grid
	                    , layout_path.grid_md 												AS block_grid_md
	                    , layout_path.grid_sm 												AS block_grid_sm
	                    , layout_path.grid_xs 												AS block_grid_xs
	                    , layout_path.fluid 												AS block_fluid
	                    , layout_path.wrap 													AS block_wrap
					FROM layout
						" . (is_array($arrWhere) && count($arrWhere)
							? "INNER JOIN anagraph_categories ON anagraph_categories.ID = layout.params"
							: ""
						) . "
						INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
					WHERE layout.value = 'anagraph'
						" . (is_array($arrWhere) && count($arrWhere)
							? " AND (" . implode(" OR ", $arrWhere) . ")"
							: ""
						) . " 
					GROUP BY layout.ID";
			break;
		case "files":
			$setting_type = "GALLERY";
        	if($layout["ID"] /*&& check_function("get_layout_settings")*/) {
                //$layout["settings"] = get_layout_settings($layout["ID"], "GALLERY");
                $layout["settings"] = Cms::getPackage("gallery"); //get_layout_settings($layout["ID"], "GALLERY");
            }
			break;
		case "publishing":
			$setting_type = "PUBLISHING";
			if(is_array($ctx)) {
				foreach($ctx AS $ctx_value) {
					$arrWhere[] = "publishing.name = " . $db->toSql($ctx_value);
				}
			} elseif(strlen($ctx)) {
				$arrWhere[] = "publishing.name = " . $db->toSql($ctx);
			}
			$sSQL = "SELECT layout.*
						, (" . (is_array($arrWhere) && count($arrWhere)
							? "publishing.name"
							: "''"
						) . ") 																AS ctx
						, layout_path.path 													AS real_path

	                    , layout_path.class 												AS block_class
	                    , layout_path.default_grid 											AS block_default_grid
	                    , layout_path.grid_md 												AS block_grid_md
	                    , layout_path.grid_sm 												AS block_grid_sm
	                    , layout_path.grid_xs 												AS block_grid_xs
	                    , layout_path.fluid 												AS block_fluid
	                    , layout_path.wrap 													AS block_wrap
					FROM layout
						" . (is_array($arrWhere) && count($arrWhere)
							? "INNER JOIN publishing ON CONCAT(publishing.area, '_', publishing.ID) = layout.value"
							: ""
						) . "
						INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
					WHERE 1
						" . (is_array($arrWhere) && count($arrWhere)
							? " AND (" . implode(" OR ", $arrWhere) . ")"
							: ""
						) . " 
					GROUP BY layout.ID";
			break;
		default:
			$setting_type = "VIRTUAL_GALLERY";
			if(is_array($ctx)) {
				foreach($ctx AS $ctx_value) {
					$arrWhere[] = "CONCAT('/', IF(layout.value = '/', '', layout.value), IF(layout.params = '/', '', layout.params)) = " . $db->toSql($ctx_value);
				}
			} elseif(strlen($ctx)) {
				$arrWhere[] = "CONCAT('/', IF(layout.value = '/', '', layout.value), IF(layout.params = '/', '', layout.params)) = " . $db->toSql($ctx);
			}
			$sSQL = "SELECT layout.*
						, (" . (is_array($arrWhere) && count($arrWhere)
							? "CONCAT('/', IF(layout.value = '/', '', layout.value), IF(layout.params = '/', '', layout.params))"
							: "''"
						) . ") 																AS ctx
						, layout_path.path 													AS real_path

	                    , layout_path.class 												AS block_class
	                    , layout_path.default_grid 											AS block_default_grid
	                    , layout_path.grid_md 												AS block_grid_md
	                    , layout_path.grid_sm 												AS block_grid_sm
	                    , layout_path.grid_xs 												AS block_grid_xs
	                    , layout_path.fluid 												AS block_fluid
	                    , layout_path.wrap 													AS block_wrap
					FROM layout
						INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
					WHERE 1
						" . (is_array($arrWhere) && count($arrWhere)
							? " AND (" . implode(" OR ", $arrWhere) . ")"
							: ""
						) . " 
					GROUP BY layout.ID";
	}

	if($sSQL) {
		$db->query($sSQL);
		$recordset                                                                          = $db->getRecordset();
        check_function("get_class_by_grid_system");
        check_function("system_get_sections");

        if(count($recordset) > 1) {
            $block_type                                                                     = system_get_block_type();

            foreach ($recordset AS $record) {
                $ID_layout                                                                  = $record["ID"];

                $arrLayout[$record["ctx"]]                                                  = $ID_layout;
                $layout["layouts"][$record["ctx"]]                                          = array(
                                                                                                "ID" => $ID_layout
                                                                                                , "prefix" => "L"
                                                                                                , "title" => $record["name"]
                                                                                                , "settings" => array()
                                                                                                , "base_path" => $record["real_path"]
                                                                                            );

                $layout["layouts"][$record["ctx"]]                                          = get_class_layout_by_grid_system(
                                                                                                false
                                                                                                , $record["block_class"]
                                                                                                , $record["block_fluid"]
                                                                                                , array(
                                                                                                    $record["block_grid_xs"]
                                                                                                    , $record["block_grid_sm"]
                                                                                                    , $record["block_grid_md"]
                                                                                                    , $record["block_default_grid"]
                                                                                                )
                                                                                                , $record["block_wrap"]
                                                                                                , false
                                                                                                , $layout["layouts"][$record["ctx"]]
                                                                                            );

                $layout["layouts"][$record["ctx"]]["settings"]                              = Cms::getPackage($record["smart_url"]);
                if(!$layout["layouts"][$record["ctx"]]["settings"]) {
                    $layout["layouts"][$record["ctx"]]["settings"]                          = Cms::getPackage($block_type["rev"][$record["ID_type"]]);
                }
            }

            if(is_array($arrLayout) /*&& check_function("get_layout_settings")*/) {
                /*$layout["settings"]                                                         = get_layout_settings($arrLayout, $setting_type);
                if($out == "layouts") {
                    $layout["layouts"]["/"]                                                 = array(
                                                                                                "ID" => null
                                                                                                , "prefix" => "L"
                                                                                                , "settings" => $layout["settings"][$setting_type . "-0"]
                                                                                            );
                    foreach($layout["settings"] AS $ctx => $settings) {
                        if(isset($layout["layouts"][$ctx]))
                            $layout["layouts"][$ctx]["settings"]                            = $settings;
                    }
                }*/

                if($out)
                    $res                                                                    = $layout[$out];
                else
                    $res                                                                    = $layout;
            }
        } else {
            $ID_layout                                                                      = $recordset[0]["ID"];
            $res                                                                            = array(
                                                                                                "ID" => $ID_layout
                                                                                                , "prefix" => "L"
                                                                                                , "title" => $recordset[0]["name"]
                                                                                                , "settings" => (check_function("get_layout_settings")
                                                                                                    ? get_layout_settings($ID_layout, $setting_type)
                                                                                                    : array()
                                                                                                )
                                                                                                , "base_path" => $recordset[0]["real_path"]
                                                                                            );

            $res                                                                            = get_class_layout_by_grid_system(
                                                                                                false
                                                                                                , $recordset[0]["block_class"]
                                                                                                , $recordset[0]["block_fluid"]
                                                                                                , array(
                                                                                                    $recordset[0]["block_grid_xs"]
                                                                                                    , $recordset[0]["block_grid_sm"]
                                                                                                    , $recordset[0]["block_grid_md"]
                                                                                                    , $recordset[0]["block_default_grid"]
                                                                                                )
                                                                                                , $recordset[0]["block_wrap"]
                                                                                                , false
                                                                                                , $res
                                                                                            );
        }

		if($db->nextRecord()) {
			check_function("get_class_by_grid_system");
			if($db->numRows() > 1) {
				do {
					$ID_layout = $db->getField("ID", "Number", true);

					$arrLayout[$db->getField("ctx", "Text", true)] = $ID_layout;
					$layout["layouts"][$db->getField("ctx", "Text", true)] = array(
						"ID" => $ID_layout
						, "prefix" => "L"
						, "title" => $db->getField("name", "Text", true)
						, "settings" => array()
						, "base_path" => $db->getField("real_path", "Text", true)
					);

					$layout["layouts"][$db->getField("ctx", "Text", true)] = get_class_layout_by_grid_system(
						false
	                    , $db->getField("block_class", "Text", true)
	                    , $db->getField("block_fluid", "Number", true)
	                    , array(
                    		$db->getField("block_grid_xs", "Number", true)
                    		, $db->getField("block_grid_sm", "Number", true)
                    		, $db->getField("block_grid_md", "Number", true)
                    		, $db->getField("block_default_grid", "Number", true)
	                    )
	                    , $db->getField("block_wrap", "Number", true)
	                    , false
	                    , $layout["layouts"][$db->getField("ctx", "Text", true)]
	                );
				} while($db->nextRecord());

				if(is_array($arrLayout) && check_function("get_layout_settings")) {
        			$layout["settings"] = get_layout_settings($arrLayout, $setting_type);
        			if($out == "layouts") {
						$layout["layouts"]["/"] = array(
							"ID" => null
							, "prefix" => "L"
							, "settings" => $layout["settings"][$setting_type . "-0"]
						);
        				foreach($layout["settings"] AS $ctx => $settings) {
        					if(isset($layout["layouts"][$ctx]))
        						$layout["layouts"][$ctx]["settings"] = $settings;
        				}
					}

					if($out)
						$res = $layout[$out];
					else
						$res = $layout;
				}
			} else {
				$ID_layout = $db->getField("ID", "Number", true);
				$res = array(
					"ID" => $ID_layout
					, "prefix" => "L"
					, "title" => $db->getField("name", "Text", true)
					, "settings" => (check_function("get_layout_settings")
						? get_layout_settings($ID_layout, $setting_type)
						: array()
					)
					, "base_path" => $db->getField("real_path", "Text", true)
				);

				$res = get_class_layout_by_grid_system(
					false
	                , $db->getField("block_class", "Text", true)
	                , $db->getField("block_fluid", "Number", true)
	                , array(
                    	$db->getField("block_grid_xs", "Number", true)
                    	, $db->getField("block_grid_sm", "Number", true)
                    	, $db->getField("block_grid_md", "Number", true)
                    	, $db->getField("block_default_grid", "Number", true)
	                )
	                , $db->getField("block_wrap", "Number", true)
	                , false
	                , $res
	            );
			}
		}
	}

	return $res;
}
