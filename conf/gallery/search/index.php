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
 
 $globals = ffGlobals::getInstance("gallery");
//$adv_search = str_replace("_adv_param", "", $_REQUEST["frmAction"]);

//$cm->oPage->addBounceComponent($adv_search);

$user_path = str_replace($cm->router->getRuleById("search")->reverse, "", $globals->page["user_path"]);
$user_path = stripslash(ffCommon_dirname($user_path)) . "/" . ffGetFilename($user_path); //elimina le estensioni tutte html json ecc

$arrUserPath = explode("/", ltrim($user_path, "/"));  
if($cm->isXHR()) {
	if($_REQUEST["type"] != "autocomplete") 
	{
        if(count($arrUserPath) <= 2) {
            $term = $arrUserPath[0];
            $group = $arrUserPath[1];
            	
			if($term && check_function("process_landing_page")) {		
				$buffer =  process_landing_search("/" . $term, $group);
			}
		}
        if(!$buffer && check_function("process_html_page_error"))
            $buffer = '<div>' . process_html_page_error() . '</div>';	//TODO: da verificare. viene strippato il div contenitore quando viene caricato in ajax		

            
		if(!defined("DISABLE_CACHE") && $buffer && check_function("system_set_cache_page")) {
			system_write_cache_page($globals->page["user_path"], true);
			system_set_cache_page($buffer);  
		}

		echo $buffer;
		exit;
	} else {
		$db = ffDB_Sql::factory();
		$arrResult = array();
		check_function("preposition");

		$params["father"]							= ($_REQUEST["father"] && strlen($_REQUEST["father_value"]) && $_REQUEST["father_value"] !== "null"
														? array(
															"key" => $_REQUEST["father"]
															, "value" => $_REQUEST["father_value"]
														)
														: null
													);
		$params["data_src"]							= $_REQUEST["data_src"];

		$params["type"] 							= $_REQUEST["type"];
		$params["thumb_mode"] 						= "default";
		if($_REQUEST["thumb"]) {
			$params["thumb"] 						= $_REQUEST["thumb"];
			$params["thumb_mode"] 					= "thumb";
		}
		$params["noimg"] 							= isset($_REQUEST["noimg"]);
		if(isset($_REQUEST["term"])) {
			$params["term"] 						= $_REQUEST["term"];
			$params["cat"] 							= $arrUserPath[0];
		} else {
			if(count($arrUserPath) == 1) {
				$params["term"] 					= $arrUserPath[0];
			} elseif(count($arrUserPath) == 2) {
				$params["term"] 					= $arrUserPath[1];
				$params["cat"] 						= $arrUserPath[0];
			}
		}

		if($params["term"]) {
			if(LANGUAGE_DEFAULT_ID == LANGUAGE_INSET_ID) {
				$sSQL = "SELECT vgallery_nodes.ID
							, vgallery_nodes.meta_title_alt
						FROM vgallery_nodes
						WHERE vgallery_nodes.permalink = " . $db->toSql($user_path) . "
							AND vgallery_nodes.is_dir = 0";
			} else {
				$sSQL = "SELECT vgallery_nodes.ID
							, vgallery_nodes_rel_languages.meta_title_alt
						FROM vgallery_nodes_rel_languages
							INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_nodes_rel_languages.ID_nodes
						WHERE vgallery_nodes_rel_languages.permalink = " . $db->toSql($user_path) . "
							AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
							AND vgallery_nodes.is_dir = 0";
			}
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_primary_node = $db->getField("ID", "Number", true);
				$params["term"] = ffCommon_url_rewrite($db->getField("meta_title_alt", "Text", true));
				
				$arrNode["keys"][] = $ID_primary_node;
				$arrNode["name"] = $db->getField("meta_title_alt", "Text", true);
			}

			$compare["name"] = "meta_title_alt";

			$params["term"] = str_replace(array("%", " ", "*", "_", "-"), array("\%", "%", "%", "%", "%"), $params["term"]);
			
			$relevance_search = explode("%", $params["term"]);
			if(is_array($compare)&& count($compare)) {
				foreach($compare AS $field_key => $field_name) {
					$where[] = "`vgallery_nodes`.`" . $field_key . "` LIKE '%" . $db->toSql($params["term"], "Text", false) . "%'";

				    foreach($relevance_search AS $relevance_term) {
				        $relevance[] = "MATCH(" . "`vgallery_nodes`.`" . $field_name . "`" . ") AGAINST (" . $db->toSql($relevance_term). ") DESC";
				        $relevance[] = "LOCATE(" . $db->toSql($relevance_term) . ", " . "`vgallery_nodes`.`" . $field_name . "`" . ")";
				    }
				    $relevance[] = "LENGTH(" . "`vgallery_nodes`.`" . $field_name . "`" . ")";
				}
			}

			if($params["cat"]) {
				$where[] = "`vgallery`.`name` = " . $db->toSql($params["cat"]);
			}

			$fields = $globals->search["available_terms"];
			$filter_params = $params;
			if(is_array($fields) && count($fields)) {
				$sSQL = "SELECT DISTINCT vgallery_fields.ID		AS ID
							, vgallery_fields.name				AS name
							, vgallery_nodes.ID_vgallery 		AS ID_vgallery
						FROM vgallery_fields 
							INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type
						WHERE vgallery_fields.name IN('" . implode(array_keys($fields), "','"). "')";
				$db->query($sSQL);
				if ($db->nextRecord()) 
				{
					do 
					{
						$ID_vgallery = $db->getField("ID_vgallery", "Number", true);
						$ID_field = $db->getField("ID", "Number", true);
						$field_name = $db->getField("name", "Number", true);
						if($fields[$field_name]) {
							$arrVgallery[$ID_vgallery] = $ID_vgallery;
							$arrField_SQL[$field_name . " " . preposition("di", $fields[$field_name])] = "(
								vgallery_rel_nodes_fields.ID_fields = " . $db->toSql($ID_field, "Number") . "
								AND vgallery_rel_nodes_fields.description = " . $db->toSql($fields[$field_name]) . "
							)";
						}
					} while($db->nextRecord());

					if(is_array($arrField_SQL) && count($arrField_SQL)) {
						$filter_params["postfix"] = " - " . implode(" ", array_keys($arrField_SQL));

						$join[] = " vgallery_rel_nodes_fields ON vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID"
									. " AND (" . implode(" OR ", $arrField_SQL) . ")";
										
						$sSQL = "SELECT vgallery_nodes.ID			AS ID
									, vgallery_nodes.permalink		AS permalink
									, vgallery_nodes.meta_title_alt	AS name
									, vgallery.name					AS vgallery_name
								FROM vgallery_nodes
									INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
									INNER JOIN layout ON layout.value = vgallery.name
									" . (is_array($join) && count($join)
										? " INNER JOIN " . implode(" INNER JOIN ", $join)
										: ""
									) . "
								WHERE vgallery_nodes.visible > 0
									AND vgallery_nodes.is_dir = 0
									" . (is_array($where) && count($where)
										? " AND " . implode(" AND ", $where)
										: ""
									) . "
								ORDER BY vgallery.name
									" . (is_array($relevance) && count($relevance) 
										? ", " . implode(", ", $relevance)
										: ""
									) 
									. ", vgallery_nodes.name
								";
						$db->query($sSQL);
						if ($db->nextRecord()) 
						{
							do 
							{			
								//if(!$ID_primary_node)
								//	$keys[] = $db->getField("ID", "Number", true);

								$arrResult = vg_search_add_result($db, $filter_params, $arrResult);
							} while($db->nextRecord());
						}	

						if(is_array($arrVgallery) && count($arrVgallery))		
							$where["fields"] = " vgallery.ID NOT IN(" . $db->toSql(implode(",", $arrVgallery), "Text", false) . ")";
					}
				}

			}

			$sSQL = "SELECT vgallery_nodes.ID			AS ID
						, vgallery_nodes.permalink		AS permalink
						, vgallery_nodes.meta_title_alt	AS name
						, vgallery.name					AS vgallery_name
					FROM vgallery_nodes
						INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
						INNER JOIN layout ON layout.value = vgallery.name
					WHERE vgallery_nodes.visible > 0
						AND vgallery_nodes.is_dir = 0
						" . (is_array($where) && count($where)
							? " AND " . implode(" AND ", $where)
							: ""
						) . "
					ORDER BY vgallery.name
						" . (is_array($relevance) && count($relevance) 
							? ", " . implode(", ", $relevance)
							: ""
						) 
						. ", vgallery_nodes.name
					";
			$db->query($sSQL);
			if ($db->nextRecord()) 
			{
				do 
				{
					//if(!$ID_primary_node)
					//	$keys[] = $db->getField("ID", "Number", true);

					$arrResult = vg_search_add_result($db, $params, $arrResult);
				} while ($db->nextRecord());
			}
			
			if($params["cat"] && is_array($arrNode) && count($arrNode))
			{
				$filter_params = $params;
				$filter_params["postfix"] = " - " . $arrNode["name"];
				$sSQL = "SELECT vgallery_nodes.ID			AS ID
							, vgallery_nodes.permalink		AS permalink
							, vgallery_nodes.meta_title_alt	AS name
							, vgallery.name					AS vgallery_name
						FROM vgallery_nodes
							INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
							INNER JOIN layout ON layout.value = vgallery.name
							" . (!$params["cat"] && is_array($join) && count($join)
								? " INNER JOIN " . implode(" INNER JOIN ", $join)
								: ""
							) . "						
	    					INNER JOIN rel_nodes  
					            ON 
					            (
					                (
					                    rel_nodes.ID_node_src = vgallery_nodes.ID 
					                    AND rel_nodes.contest_src = vgallery.name
					                    AND rel_nodes.ID_node_dst IN(" . $db->toSql(implode(",", $arrNode["keys"]), "Text", false) . ")
					                ) 
					            OR 
					                (
					                    rel_nodes.ID_node_dst = vgallery_nodes.ID 
	                        			AND rel_nodes.contest_dst = vgallery.name
					                    AND rel_nodes.ID_node_src IN(" . $db->toSql(implode(",", $arrNode["keys"]), "Text", false) . ")
					                )
					            )
					    WHERE vgallery_nodes.visible > 0
					    	AND vgallery_nodes.is_dir = 0
					    	" . (!$params["cat"] && $where["fields"]
							? " AND " . $where["fields"]
							: ""
						) . "
						ORDER BY vgallery.name
							, vgallery_nodes.meta_title_alt 
						";	
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$arrResult = vg_search_add_result($db, $filter_params, $arrResult);
					} while ($db->nextRecord());
				}
			}		

			ksort($arrResult);
			
			if($params["cat"]) {
				foreach($arrResult AS $key => $value) {
					if(strpos($key, ffTemplate::_get_word_by_code($params["cat"])) === 0) {
						$primary_cat = $key;
						break;
					}
				}
				if($primary_cat) {
					$tmp_cat = array($primary_cat => $arrResult[$primary_cat]);
					unset($arrResult[$primary_cat]);
					
					$arrResult = $tmp_cat + $arrResult;
				}
			}
			
			
			/*
			$buffer = ffCommon_jsonenc($arrResult);

			if(!defined("DISABLE_CACHE") && $buffer && check_function("system_set_cache_page")) {
				system_write_cache_page($globals->page["user_path"], true);
				system_set_cache_page($buffer);  
			}*/

			switch($params["type"]) {
				case "actex":
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
						echo '<ul>' . $res . '</ul>';
					} else {
						echo ffTemplate::_get_word_by_code("search_not_found_match");
					}
					break;
				case "ul":
					if(count($arrResult)) {
						echo '<ul>' . implode("", $arrResult) . '</ul>';
					} else {
						echo ffTemplate::_get_word_by_code("search_not_found_match");
					}
					break;
				default:
					echo ffCommon_jsonenc($arrResult, true);
			}
		}
	}
	exit;
} else {
	if($user_path) {
	    $layout["prefix"] = "S"; 
	    $layout["ID"] = 0;
	    $layout["title"] = ffTemplate::_get_word_by_code("search_title");
	    $layout["type"] = "SEARCH";
	    $layout["location"] = "Content";
	    $layout["visible"] = NULL;
	    if(check_function("get_layout_settings"))
		    $layout["settings"] = get_layout_settings(NULL, "SEARCH");

        if(count($arrUserPath) <= 2) {
            $term = $arrUserPath[0];
            $group = $arrUserPath[1];

	        if($term && check_function("process_landing_page")) {
		        $cm->oPage->addContent(process_landing_search("/" . $term, $group), null, "SEARCH");
	        } else {
	        	http_response_code(404);
	        }
        }
	}
}

function vg_search_add_result($db, $params, $res = array()) {
	check_function("normalize_url");
	check_function("get_thumb");
	check_function("preposition");

	$cover 																										= null;
	$permalink																									= $db->getField("permalink", "Text", true);
	$ID_node 																									= $db->getField("ID", "Number", true);
	$group																										= $db->getField("vgallery_name", "Text", true);
	$cat 																										= $params["prefix"] . ffTemplate::_get_word_by_code($group) . $params["postfix"];
	$desc																										= $db->getField("name", "Text", true);
	$html_title 																								= $desc;
	if($params["term"])
		$html_title 																							= preg_replace("/(" . preg_quote(str_replace("%", " ", $params["term"])) . ")/i", "<mark>\${1}</mark>", $html_title);
	
	if(!$params["noimg"]) {
		if(is_dir(DISK_UPDIR . $permalink)) {
			foreach(new DirectoryIterator(DISK_UPDIR . $permalink) as $item)
			{
			   if (!$item->isDot() && $item->isFile())
			   {
				   $cover 																						= get_thumb($permalink . "/" . $item->getFilename(), $params, $params["thumb_mode"]);
				   break;
			   }
			}				
		}
	}
	
	switch($params["type"]) {
		case "tree":
			$res[ffCommon_charset_encode($group)][] 															= array(
																													"value" => normalize_url_by_current_lang($permalink)
																													, "label" => ffCommon_charset_encode($desc)
																													, "cover" => $cover
																												);
			break;
		case "ul-tree":
			$res[$cat][$ID_node] 																				= '<li><a href="' . normalize_url_by_current_lang($permalink) . '">' . ($cover ? '<img src="' . $cover["src"] . '" />' : "") . $html_title . '</a></li>';
			break;
		case "ul":
			$res[$ID_node] 																						= '<li><a href="' . normalize_url_by_current_lang($permalink) . '">' . ($cover ? '<img src="' . $cover["src"] . '" />' : "") . $html_title . ($cat ? " - " . $cat : "") . '</a></li>';
			break;
		default:
			$res[] 																								= array(
																													"value" => normalize_url_by_current_lang($permalink)
																													, "label" => ffCommon_charset_encode($desc)
																													, "cat" => ffCommon_charset_encode($group)
																													, "cover" => $cover
																												);
	}
	
	return $res;
}