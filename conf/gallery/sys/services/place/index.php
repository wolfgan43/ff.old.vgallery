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
 
// impedisce a google d'indicizzare il servizio
if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false)
{
    die('<html>
			<head>
				<title>no resource</title>
				<meta name="robots" content="noindex,nofollow" />
				<meta name="googlebot" content="noindex,nofollow" />
			</head>
		</html>');
}

// impedisce l'accesso diretto ai browser
if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{    
   // http_response_code(404);
   // exit;
}

//require_once("../../../../../../ff/main.php");
//require_once("../../../../../../modules/security/common.php");

//if ($plgCfg_ActiveComboEX_UseOwnSession)


check_function("get_locale");

$arrResult = array();

$schema_field = array(
    "city" => array(
        "id" => "ID"
        , "name" => "name"
        , "permalink" => "permalink"
        , "smarturl" => "smart_url"
        , "sigle" => "name"
        , "province" => "province_smart_url"
        , "default-key" => "ID"
        , "default-name" => "name"
    )
    , "province" => array(
        "id" => "ID"
        , "name" => "name"
        , "permalink" => "permalink"
        , "smarturl" => "smart_url"
        , "sigle" => "sigle"
        , "default-key" => "ID"
        , "default-name" => "name"
    )
    , "region" => array(
        "id" => "ID"
        , "name" => "name"
        , "permalink" => "permalink"
        , "smarturl" => "smart_url"
        , "sigle" => "smart_url"
        , "default-key" => "ID"
        , "default-name" => "name"
    )
    , "state" => array(
        "id" => "ID"
        , "name" => "name"
        , "permalink" => "permalink"
        , "smarturl" => "smart_url"
        , "sigle" => "sigle"
        , "default-key" => "ID"
        , "default-name" => "name"
    )
    , "filter" => array(
        "id" => "ID"
        , "name" => "description"
        , "permalink" => null
        , "description" => "description"
        , "default-key" => "description"
        , "default-name" => "description"
    )
);

$field = array(
    "ID" 													=> null
    , "name" 												=> null
    , "group"												=> "'' AS `group`"
    , "ID_city" 											=> false
    , "ID_province" 										=> false
    , "ID_region" 											=> false
    , "ID_state" 											=> false
);

$shcema_table = array(
    "city"                                                  => FF_SUPPORT_PREFIX . "city"
    , "province"                                            => FF_SUPPORT_PREFIX . "province"
    , "region"                                              => FF_SUPPORT_PREFIX . "region"
    , "state"                                               => FF_SUPPORT_PREFIX . "state"
);

$params["table_prefix"] 									= FF_SUPPORT_PREFIX;
$params["type"]												= $_GET["type"];
$params["out"]												= ($_GET["out"]
																? $_GET["out"]
																: false
															);
$params["filter"] 											= (isset($_GET["limit"])
																? (empty($_GET["limit"])
																	? true
																	: $_GET["limit"]
																)
																: false
															);

$params["name"] 											= "name";

$params["key"]												= ($_GET["voce"]
																? ffCommon_url_rewrite($_GET["voce"])
																: "id"
															);	

$params["term"]												= ($_GET["term"]
																? strtolower(str_replace(array("%", " ", "*", "_", "-"), array("\%", "%", "%", "%", "%"), $_GET["term"]))
																: false
															);
$params["val"]												= $_GET["sel_val"];
$params["current"]											= $_GET["current"];

$params["limit"] 											= (isset($_GET["count"])
 																? (is_numeric($_GET["count"]) && $_GET["count"] > 0
																	? $_GET["count"]
																	: ""
																)
																: false
															);   
															
$params["father"]											= ($_GET["father"] && strlen($_GET["father_value"]) && $_GET["father_value"] !== "null"
																? array(
																	"key" => $_GET["father"]
																	, "value" => $_GET["father_value"]
																)
																: null
															);
$params["data_src"]											= $_GET["data_src"];
$params["group"]											= $_GET["group"];
$params["callback"]											= (isset($_GET["callback"])
                                                                ? (strlen($_GET["callback"])
																	? $_GET["callback"]
																	: true
																)
                                                                : null
                                                            );

$params["place"] 											= basename($cm->real_path_info);
//if(!$params["place"] && is_bool($params["filter"]))
//	$params["place"] 										= "state";

$params["table"] 											= ($params["place"]
																? $params["table_prefix"] . $params["place"]
																: "vgallery_rel_nodes_fields"
															);

if(!$params["place"]) {
    foreach($shcema_table AS $place => $table) {
        $params["place"] = $place;
        $params["table"] = $table;

        $arrResult = array_merge($arrResult, vg_place_search($params, $schema_field, $field));

//        $buffer = vg_place_search($params, $schema_field, $field);
//        if($buffer)
//            $arrResult[ffTemplate::_get_word_by_code($place)] = $buffer;
    }
//    $params["type"] = "ul-tree";     
} else {
    $arrResult = array_replace($arrResult, vg_place_search($params, $schema_field, $field));
}
    




switch($params["type"]) {
	case "array":
		$res = $arrResult;
		break;
	case "actex" :
		cm::jsonParse(array(
			"success" => true
			, "widget" => array(
				"actex" => array(
					"D" . $params["data_src"] => ($params["father"]["value"] ? array("F" . $params["father"]["value"] => $arrResult) : $arrResult)
				)
			)
		));		
		break;
	case "ul-tree":
		if(count($arrResult)) {
			foreach($arrResult AS $cat => $items) {
				$res .= '<li>' . $cat . '<ul>' . implode("", $items) . '</ul></li>';
			}
			$res = '<ul>' . $res . '</ul>';
		} else {
			$res = ffTemplate::_get_word_by_code("search_not_found_match");
		}
		break;
	case "ul":
		if(count($arrResult)) {
			$res = '<ul>' . implode("", $arrResult) . '</ul>';
		} else {
			$res = ffTemplate::_get_word_by_code("search_not_found_match");
		}
		break;
	default:
    	$res = ffCommon_jsonenc($arrResult, true);
}

if(!$params["out"]) {
	if($res)
		echo $res;

	exit;
}

function vg_place_search($params, $schema_field, $field) {
    $arrResult = array();
    $db = ffDB_Sql::factory();

    if($params["father"] && $params["place"] && $params["father"]["key"] != $params["place"]) {
        switch($params["father"]["key"]) {
            case "billtown":
            case "shippingtown":
            case "town":
            case "city":
                break;
            case "billprovince":
            case "shippingprovince":
            case "province":
                if($params["place"] == "city") {
                    $params["father"]["table"] = $params["table_prefix"] . "province";		

                    $field["ID_province"] = "`" . $params["table_prefix"] . "province`.`ID` AS ID_province";
                    $field["ID_region"] = "`" . $params["table_prefix"] . "province`.`ID_region` AS ID_region";
                    $field["ID_state"] = "`" . $params["table_prefix"] . "province`.`ID_state` AS ID_state";

                    $join["province"] = "`" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
                    $where[] = "AND `" . $params["table_prefix"] . "province`.`ID` = " . $db->toSql($params["father"]["value"]);
                }
                break;
            case "billregion":
            case "shippingregion":
            case "region":
                if($params["place"] != "state") {
                    $params["father"]["table"] = $params["table_prefix"] . "region";		

                    $field["ID_province"] = "`" . $params["table_prefix"] . "province`.`ID` AS ID_province";
                    $field["ID_region"] = "`" . $params["table_prefix"] . "province`.`ID_region` AS ID_region";
                    $field["ID_state"] = "`" . $params["table_prefix"] . "province`.`ID_state` AS ID_state";

                    if($params["place"] == "city") 
                        $join["province"] = "`" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";

                    $where[] = "AND `" . $params["table_prefix"] . "province`.`ID_region` = " . $db->toSql($params["father"]["value"]);
                }
                break;
            case "billstate":
            case "shippingstate":
            case "state":
                $params["father"]["table"] = $params["table_prefix"] . "state";		
                if($params["place"] == "region") {
                    $field["ID_region"] = "`" . $params["table_prefix"] . "region`.`ID` AS ID_region";
                    $field["ID_state"] = "`" . $params["table_prefix"] . "region`.`ID_state` AS ID_state";

                    $where[] = "AND `" . $params["table_prefix"] . "region`.`ID_state` = " . $db->toSql($params["father"]["value"]);
                } else {
                    $field["ID_province"] = "`" . $params["table_prefix"] . "province`.`ID`";
                    $field["ID_region"] = "`" . $params["table_prefix"] . "province`.`ID_region`";
                    $field["ID_state"] = "`" . $params["table_prefix"] . "province`.`ID_state`";

                    $join["province"] = "`" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
                    $where[] = "AND `" . $params["table_prefix"] . "province`.`ID_state` = " . $db->toSql($params["father"]["value"]);
                }
                break;
            default:	
        }
    }

    if($params["filter"]) {
        if($params["place"]) {
            if($params["filter"] === true) {
                $join[] = "vgallery_rel_nodes_fields ON vgallery_rel_nodes_fields.description = `" . $params["table"] . "`.`name`";
                $join[] = "vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields";
                $where[] = "vgallery_fields.selection_data_source = " . $db->toSql($params["table_prefix"] . $params["place"]);
                $where[] = "vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
            } elseif(is_array($params["filter"])) {
                foreach($params["filter"] AS $filter_key => $filter_value) {
                    switch($params["place"]) {
                        case "city":
                            if($filter_key == "city")
                                continue;

                            if(strpos($filter_key, ",") !== false) {
                                $where_part = array();
                                $arrFilterKey = explode(",", $filter_key);
                                foreach($arrFilterKey AS $filter_key_part) {
                                    if(is_numeric($filter_value)) {
                                        $where_part[] = "`" . $params["table_prefix"] . $params["place"] . "`.ID_" . $filter_key_part . " = " . $db->toSql($filter_value);
                                    } else {
                                        $where_part[] = "`" . $params["table_prefix"] . $params["place"] . "`.ID_" . $filter_key_part . " IN(
                                                                SELECT `" . $params["table_prefix"] . $filter_key_part . "`.ID 
                                                                FROM `" . $params["table_prefix"] . $filter_key_part . "`
                                                                WHERE `" . $params["table_prefix"] . $filter_key_part . "`.permalink = " . $db->toSql($filter_value) . "
                                                        )";
                                    }                                    
                                }
                                if($where_part) {
                                    $where[] = "(" . implode(" OR ", $where_part) . ")";
                                }
                            } else {

                                if(is_numeric($filter_value)) {
                                    $where[] = "`" . $params["table_prefix"] . $params["place"] . "`.ID_" . $filter_key . " = " . $db->toSql($filter_value);
                                } else {
                                    $join[$filter_key] = "`" . $params["table_prefix"] . $filter_key . "` ON  `" . $params["table_prefix"] . $filter_key . "`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_" . $filter_key . "`";
                                    $where[] = "`" . $params["table_prefix"] . $filter_key . "`.`permalink` = " . $db->toSql($filter_value);
                                }
                            }
                            break;
                        case "province":
                            break;
                        case "region":
                            break;
                        case "state":
                            break;
                        default:
                    }
                }
            } elseif($params["filter"] == "rel") {
               // $join[] = "vgallery_nodes ON vgallery_nodes.ID_place = `" . $params["table_prefix"] . "city`.ID";
               // $where[] = "`" . $params["table_prefix"] . "city`.permalink != ''";
            } else {
                if(LANGUAGE_INSET == LANGUAGE_DEFAULT) {
                    $sSQL = "SELECT vgallery_nodes.ID
                                , vgallery.name AS vgallery_name 
                            FROM vgallery_nodes 
                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                            WHERE vgallery_nodes.permalink = " . $db->toSql($params["filter"]);
                } else {
                    $sSQL = "SELECT vgallery_nodes.ID
                                , vgallery.name AS vgallery_name 
                            FROM vgallery_nodes 
                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
                            WHERE vgallery_nodes_rel_languages.permalink = " . $db->toSql($params["filter"]);
                }
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $ID_node = $db->getField("ID", "Number", true);
                    $vgallery_name = $db->getField("vgallery_name", "Text", true);
					switch($params["place"]) {
			            case "province":
			            	$join["city"] = "`" . $params["table_prefix"] . "city` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
			                break;	
			            case "region":
			            	$join["city"] = "`" . $params["table_prefix"] . "city` ON  `" . $params["table_prefix"] . "region`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_region`";
			                break;	
			            case "state":
			            	$join["city"] = "`" . $params["table_prefix"] . "city` ON  `" . $params["table_prefix"] . "state`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_state`";
			                break;	
			            default:
					}
                    $join[] = "vgallery_nodes ON vgallery_nodes.ID_place = `" . $params["table_prefix"] . "city`.`ID` AND vgallery_nodes.visible > 0";
                    $join[] = "rel_nodes ON 
                                (
                                    rel_nodes.ID_node_src = " . $db->toSql($ID_node, "Number") . " 
                                    AND rel_nodes.contest_src = " . $db->toSql($vgallery_name) . " 
                                    AND rel_nodes.ID_node_dst = vgallery_nodes.ID
                                ) OR (
                                    rel_nodes.ID_node_dst = " . $db->toSql($ID_node, "Number") . " 
                                    AND rel_nodes.contest_dst = " . $db->toSql($vgallery_name) . "
                                    AND rel_nodes.ID_node_src = vgallery_nodes.ID
                                )";
                }
            }
        } elseif($params["filter"] !== true) {
            $join[] = "vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields";
            $where[] = "vgallery_fields.name = " . $db->toSql($params["filter"]);
            $where[] = "vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
        } else {
            $where[] = "0";
        }
    }

    if($params["group"] && $params["place"] && $params["group"] != $params["place"]) { 
        switch($params["group"]) {
            case "province":
                if($params["place"] == "city") {
                    $field["group"] = "`" . $params["table_prefix"] . "province`.name AS `group`";
                    $join["province"] = "INNER JOIN `" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
                }
                break;	
            case "region":
                if($params["place"] == "city") {
                    $field["group"] = "`" . $params["table_prefix"] . "region`.name AS `group`";
                    $join["province"] = "`" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
                    $join["region"] = "`" . $params["table_prefix"] . "region` ON  `" . $params["table_prefix"] . "province`.`ID_region` =  `" . $params["table_prefix"] . "region`.`ID`";
                } elseif($params["place"] == "province") {
                    $field["group"] = "`" . $params["table_prefix"] . "region`.name AS `group`";
                    $join["region"] = "`" . $params["table_prefix"] . "region` ON  `" . $params["table_prefix"] . "province`.`ID_region` =  `" . $params["table_prefix"] . "region`.`ID`";
                }
                break;	
            case "state":
                if($params["place"] == "city") {
                    $field["group"] = "`" . $params["table_prefix"] . "state`.name AS `group`";
                    $join["province"] = "`" . $params["table_prefix"] . "province` ON  `" . $params["table_prefix"] . "province`.`ID` =  `" . $params["table_prefix"] . "city`.`ID_province`";
                    $join["state"] = "`" . $params["table_prefix"] . "state` ON  `" . $params["table_prefix"] . "province`.`ID_state` =  `" . $params["table_prefix"] . "state`.`ID`";
                } elseif($params["place"] == "province") {
                    $field["group"] = "`" . $params["table_prefix"] . "state`.name AS `group`";
                    $join["state"] = "`" . $params["table_prefix"] . "state` ON  `" . $params["table_prefix"] . "province`.`ID_state` =  `" . $params["table_prefix"] . "state`.`ID`";
                } elseif($params["place"] == "region") {
                    $field["group"] = "`" . $params["table_prefix"] . "state`.name AS `group`";
                    $join["state"] = "`" . $params["table_prefix"] . "state` ON  `" . $params["table_prefix"] . "region`.`ID_state` =  `" . $params["table_prefix"] . "state`.`ID`";
                }

                break;	
            default:
                $field["group"] = "`" . $params["table"] . "region`.name AS `group`";
        }
    }

    $type = ($params["place"]
        ? $params["place"]
        : ($params["filter"]
            ? "filter"
            : false
        )
    );

    if($type) {
        if($schema_field[$type][$params["key"]])
            $key = $schema_field[$type][$params["key"]];

        if(!$key)
            $key = $schema_field[$type]["default-key"];

        if($schema_field[$type][$params["name"]])
            $name = $schema_field[$type][$params["name"]];

        if(!$name)
            $name = $schema_field[$type]["default-name"];

//print_r($schema_field);
        $field["ID"] = "`" . $params["table"] . "`.`" . $key . "` AS ID";
        $field["name"] = "`" . $params["table"] . "`.`" . $name . "` AS name";

        $where[] =  "`" . $params["table"] . "`.`" . $db->toSql($key, "Text", false) . "` != ''";

        $compare[$name] = $name;
        if($params["term"]) {
            $relevance_search = explode("%", $params["term"]);
            if(is_array($compare)&& count($compare)) {
                if(LANGUAGE_INSET != LANGUAGE_DEFAULT) {
                    check_function("get_webservices");
                    $webservices    = get_webservices(); //todo:da sistemare il webservices e fare servizio di translation per il cms
                    if($webservices["translate.google"] && $webservices["translate.google"]["enable"]) {
                        $translator = ffTranslator::getInstance("google", $webservices["translate.google"]["code"]);
                        $term_translated = $translator->translate($params["term"]);
                        if ($term_translated == $params["term"])
                            $term_translated = "";
                    }
                }
                foreach($compare AS $field_key => $field_name) {
                    if($term_translated) {
                        $where[] = "(`" . $params["table"] . "`.`" . $field_key . "` LIKE '%" . $db->toSql($params["term"], "Text", false) . "%'
                                        OR `" . $params["table"] . "`.`" . $field_key . "` LIKE '%" . $db->toSql($term_translated, "Text", false) . "%'
                            )";
                    } else {
                        $where[] = "`" . $params["table"] . "`.`" . $field_key . "` LIKE '%" . $db->toSql($params["term"], "Text", false) . "%'";
                    }
                    foreach($relevance_search AS $relevance_term) {
                        $relevance[] = "MATCH(" . "`" . $params["table"] . "`.`" . $field_name . "`" . ") AGAINST (" . $db->toSql($relevance_term). ") DESC";
                        $relevance[] = "LOCATE(" . $db->toSql($relevance_term) . ", " . "`" . $params["table"] . "`.`" . $field_name . "`" . ")";
                    }
                    $relevance[] = "LENGTH(" . "`" . $params["table"] . "`.`" . $field_name . "`" . ")";
                }
            }
        } elseif($params["val"]) {
            $where[] = "`" . $params["table"] . "`.`" . $key . "` = " . $db->toSql($params["val"]);
        }

        //print_r($relevance);
        $sSQL = "SELECT DISTINCT 
                    " . (is_array($field) && count($field)
                        ? implode(", ", array_filter($field))
                        : ""
                    ) . "
                FROM " . $params["table"] . " 
                    " . (is_array($join) && count($field)
                        ? " INNER JOIN " . implode(" INNER JOIN ", $join)
                        : ""
                    ) . "
                WHERE 1
                    " . (is_array($where) && count($where)
                        ? " AND " . implode(" AND ", $where)
                        : ""
                    ) . "
                ORDER BY 
                    " . (is_array($relevance) && count($relevance) 
                        ? implode(", ", $relevance) . ", "
                        : ""
                    ) 
                    . "`" . $params["table"] . "`.`" . $name . "`"
                . ($params["limit"]
                    ? " LIMIT " . $params["limit"]
                    : ""
                );
        $db->query($sSQL);
        if ($db->nextRecord()) {
            do {
                $arrResult = vg_place_add_result($db, $params, $arrResult);
            } while($db->nextRecord());
        }
    }
    return $arrResult;
}

function vg_place_add_result($db, $params, $res = array()) {
	$ID_node 																			= $db->getField("ID", "Text", true);
	$group 																				= $db->getField("group", "Text", true);
	if($group)
		$cat 																			= $params["prefix"] . $group . $params["postfix"];

	$arrDesc 																			= explode(" - ", $db->getField("name", "Text", true));
    $desc = $arrDesc[0];
    if(LANGUAGE_INSET != LANGUAGE_DEFAULT) {
        check_function("get_webservices");
        $webservices    = get_webservices(); //todo:da sistemare il webservices e fare servizio di translation per il cms
        if($webservices["translate.google"] && $webservices["translate.google"]["enable"]) {
            $translator = ffTranslator::getInstance("google", $webservices["translate.google"]["code"]);
            $desc = $translator->translate($desc);
        }
    }

	$html_title 																		= ffCommon_charset_encode($desc);
	if($params["term"])
		$html_title 																	= preg_replace("/(" . preg_quote(str_replace("%", " ", $params["term"])) . ")/i", "<mark>\${1}</mark>", $html_title);

     if($params["callback"] === true)
        $html_title 																	= '<a href="' . $ID_node . '">' . $html_title . '</a>';    
    elseif(substr($params["callback"], 0, 1) == "/" && strpos($params["callback"], "[" . $params["key"] . "]"))
    	$html_title 																	= '<a href="' . str_replace("[" . $params["key"] . "]", ffCommon_url_rewrite($ID_node), $params["callback"]) . '">' . $html_title . '</a>';    
    elseif(strlen($params["callback"]))
		$html_title 																	= '<a href="javascript:void(0);" onclick="' . $params["callback"] . '(\'' . addslashes($ID_node) . '\',\'' . addslashes($desc) . '\');">' . $html_title . '</a>';

    switch($params["type"]) {
		case "array":
		case "tree":
			$res[ffCommon_charset_encode($group)][] 									= array(
																							"value" => ffCommon_charset_encode($ID_node)
																							, "desc" => ffCommon_charset_encode($desc)
																						);
			break;
		case "ul-tree":
			if(!$cat)
				$cat 																	= ffTemplate::_get_word_by_code($params["place"]);

			$res[$cat][] 																= '<li' . ($params["current"] == ffCommon_url_rewrite($ID_node) ? ' class=' . Cms::getInstance("frameworkcss")->get("current", "util") : '') . '>' . $html_title . '</li>';
			break;
		case "ul":
			$res[] 																		= '<li' . ($params["current"] == ffCommon_url_rewrite($ID_node) ? ' class=' . Cms::getInstance("frameworkcss")->get("current", "util") : '') . '>' . $html_title . ($cat ? " - " . $cat : "") . '</li>';
			break;
		default:
			$res[] 																		= array(
																							"value" => ffCommon_charset_encode($ID_node)
																							, ($params["type"] == "actex" ? "desc" : "label") => ffCommon_charset_encode($desc)
																						);
			if($group)
				$res[count($res) - 1]["group"] 											= ffCommon_charset_encode($group);

	}
	
	return $res;
}