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
function process_addon_variant($user_path, $ID_node, $vgallery_name, $enable_multilang_visible, $source_user_path = null, $layout) {
	$db = ffDB_Sql::factory();
	
	$buffer = "";
	
	$arrUserPath = explode("-", basename($user_path));
	$sSQL_header = "";
	$limit_compare = 2; //prodotto-sub-variante
	if(is_array($arrUserPath) && count($arrUserPath)) {
		$count_compare = 0;
		krsort($arrUserPath);
		$strCompare = basename($user_path);
        $sSQL_where = "";
		foreach($arrUserPath AS $arrUserPath_key => $arrUserPath_value) {
			if($count_compare + $limit_compare == count($arrUserPath)) {
				break;
			}
			$strCompare = substr($strCompare, 0, "-" . strlen("-" . $arrUserPath_value));
			if(strlen($strCompare)) {
				
				if(strlen($sSQL_header))
					$sSQL_header .= ", ',' ";

				if(strlen($sSQL_where))
					$sSQL_where .= " OR ";
				
				$sSQL_header .= " IF(LOCATE(SOUNDEX(" . $db->toSql($strCompare) . "), SOUNDEX(variant.name)) > 0
									, LENGTH(REPLACE(SOUNDEX(variant.name), SOUNDEX(" . $db->toSql($strCompare) . "), ''))
									, 0
								)";
				$sSQL_where .= " LOCATE(" . $db->toSql($strCompare) . ", variant.name) > 0 ";
			
			}
			$count_compare++;
		}
	}
    if(strlen($sSQL_where)) {
        if(LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID) {
            $sSQL_select_meta = " ( variant.meta_title ) ";
            $sSQL_select_smart_url = " ( variant.name  ) ";
            
        } else {
            $sSQL_select_meta = " ( SELECT vgallery_nodes_rel_languages.meta_title AS name
                            FROM vgallery_nodes_rel_languages 
                            WHERE vgallery_nodes_rel_languages.ID_nodes = variant.ID 
                                AND vgallery_nodes_rel_languages.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
                    ) ";
            $sSQL_select_smart_url = " ( SELECT vgallery_nodes_rel_languages.smart_url AS name
                            FROM vgallery_nodes_rel_languages 
                            WHERE vgallery_nodes_rel_languages.ID_nodes = variant.ID 
                                AND vgallery_nodes_rel_languages.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
                    ) ";
            
        }
        $sSQL = "SELECT variant.name AS name
                    , variant.parent AS parent
                    , variant.ID AS ID_variant
                    , " . $sSQL_select_meta . " AS meta_title
                    , " . $sSQL_select_smart_url . "AS smart_url
                    , variant.visible AS visible
			    FROM vgallery_nodes AS variant
	            WHERE variant.ID <> " . $db->toSql($ID_node, "Number") . "
	        	    AND variant.parent = " . $db->toSql(ffCommon_dirname($user_path))  . "
		            AND
		            (
		                $sSQL_where
		            )";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
            $vg_data = array();
            
            do {
                $vg_data[$db->getField("ID_variant", "Number", true)]["name"] = $db->getField("name", "Text", true);
                $vg_data[$db->getField("ID_variant", "Number", true)]["parent"] = $db->getField("parent", "Text", true);
                $vg_data[$db->getField("ID_variant", "Number", true)]["smart_url"] = $db->getField("smart_url", "Text", true);
                $vg_data[$db->getField("ID_variant", "Number", true)]["meta_title"] = $db->getField("meta_title", "Text", true);
                $vg_data[$db->getField("ID_variant", "Number", true)]["visible"] = $db->getField("visible", "Number", true);
            } while($db->nextRecord());
        }
        
        if(is_array($vg_data) && count($vg_data)) {
            if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
                $file_permission = get_file_permission(null, "vgallery_nodes", array_keys($vg_data));

			//$tpl_data["custom"] = "variant.html";
			$tpl_data["base"] = "variant.html";

			$tpl_data["result"] = get_template_cascading($user_path, $tpl_data, "/tpl/addon");

			$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
			//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
            $tpl->load_file($tpl_data["result"]["name"], "main");
                
            //$tpl = ffTemplate::factory(get_template_cascading($user_path, "variant.html", "/vgallery"));
            //$tpl->load_file("variant.html", "main");

            $tpl->set_var("site_path", FF_SITE_PATH);
            $tpl->set_var("domain_inset", DOMAIN_INSET);
            $tpl->set_var("theme_inset", THEME_INSET);
            if($source_user_path !== null) {
                $tpl->set_var("user_path", $source_user_path);    
            } else {
                $tpl->set_var("user_path", ffCommon_dirname($user_path));
            }
            
            $tpl->set_var("variant_title", ffTemplate::_get_word_by_code($vgallery_name . "_variant"));
            $precision = 2; 
            $variant_set = false;

            foreach($vg_data AS $vg_data_key => $vg_data_value) {
                if(Cms::env("ENABLE_STD_PERMISSION")) {
                    if(check_function("get_file_permission"))
                        $file_permission = get_file_permission(stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"], "vgallery_nodes");

                    if (!check_mod($file_permission, 1, ($enable_multilang_visible ? true : LANGUAGE_DEFAULT), Auth::env("AREA_VGALLERY_SHOW_MODIFY")))
                        continue;
                } else {
                    if(!$vg_data_value["visible"] > 0) {
                        continue;
                    }
                }

			    $arrVariantPath = explode("-", $vg_data_value["smart_url"]);
			    krsort($arrVariantPath);

				if(count($arrUserPath) > count($arrVariantPath))
					$margin = count($arrUserPath) - count($arrVariantPath);
				else 
					$margin = 0;
			    
			    $arrIntersect = array_intersect_assoc($arrUserPath, $arrVariantPath);
			    if(count($arrUserPath) > count($arrIntersect) + $precision + $margin)
				    continue;
			    
			    $variant_set = true;
			    
			    $tpl->set_var("smart_url", $vg_data_value["smart_url"]);
			    $tpl->set_var("meta_title", $vg_data_value["meta_title"]);
			    
			    $tpl->parse("SezItem", true);
		    } 
		    if($variant_set) {
			    $tpl->parse("SezVariant", false);
			    
			    $buffer = $tpl->rpparse("main", false);
		    }
	    }
    }
	return $buffer;
    /*
$pippo ="SELECT 
	CONCAT(
		IF(LOCATE(SOUNDEX('tappeto-play-spots-marrone'), SOUNDEX(name)) > 0
			, LENGTH(REPLACE(SOUNDEX(name), SOUNDEX('tappeto-play-spots-marrone'), ''))
			, 0
		) 
		, ','
		, IF(LOCATE(SOUNDEX('tappeto-play-spots'), SOUNDEX(name)) > 0
			, LENGTH(REPLACE(SOUNDEX(name), SOUNDEX('tappeto-play-spots'), ''))
			, 0
		) 
		, ','
		, IF(LOCATE(SOUNDEX('tappeto-play'), SOUNDEX(name)) > 0
			, LENGTH(REPLACE(SOUNDEX(name), SOUNDEX('tappeto-play'), ''))
			, 0
		) 
		, ','
		, IF(LOCATE(SOUNDEX('tappeto'), SOUNDEX(name)) > 0
			, LENGTH(REPLACE(SOUNDEX(name), SOUNDEX('tappeto'), ''))
			, 0
		) 
	) AS compare 
	, name
FROM `vgallery_nodes` AS variant 
WHERE 1 
	AND
	(
		LOCATE(SOUNDEX('tappeto-play-spots-marrone'), SOUNDEX(name)) > 0
		OR
		LOCATE(SOUNDEX('tappeto-play-spots'), SOUNDEX(name)) > 0
		OR
		LOCATE(SOUNDEX('tappeto-play'), SOUNDEX(name)) > 0
		OR
		LOCATE(SOUNDEX('tappeto'), SOUNDEX(name)) > 0
	)
ORDER BY test DESC";	*/
	
}

