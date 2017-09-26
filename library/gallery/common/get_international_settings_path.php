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
function get_international_settings_path($settings_path, $language = NULL, $block_redirect = true) {
	static $arrLayout = null;

    $globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	$ID_domain = $globals->ID_domain;

	$invalid_path = 0;
	$processed_path = 0;
    
    $arr_settings_path = explode("/", $settings_path);
    if($language === NULL)
        $language = LANGUAGE_DEFAULT;

	$tmp_vgallery_part_url = "";
	$tmp_gallery_part_url = "";
    $arrLangAlt = array();	
	if(check_function("get_locale"))
		$arrLang = get_locale("lang");

    if(check_function("system_get_sections"))
        $block_type = system_get_block_type();	 	
	
    if(!$arrLayout) {
        $arrLayout = array();
		$sSQL = "SELECT layout.ID
			        , layout.value
			        , layout.params
			        , layout_path.path AS layout_path
			    FROM layout 
			        INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
			    WHERE layout.ID_type IN(" . $db->toSql($block_type["gallery"]["ID"], "Number") . "," . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . ")
			        AND layout.ID_domain = " . $db->toSql($ID_domain, "Number");
		 $db->query($sSQL);
         if($db->nextRecord()) {
			do {
				$ID_layout = $db->getField("ID", "Number", true);
				$layout_path = $db->getField("layout_path", "Text", true);
				if(!strlen($layout_path))
					$layout_path = "/";							 	

				$arrLayout[$layout_path][$ID_layout]["value"] 	= $db->getField("value", "Text", true);
				$arrLayout[$layout_path][$ID_layout]["params"] 	= $db->getField("params", "Text", true);
				$arrLayout[$layout_path][$ID_layout]["type"] 	= $block_type["rev"][$db->getField("ID_type", "Number", true)];
			} while($db->nextRecord());
         }
	}
	
	
    if(is_array($arr_settings_path) && count($arr_settings_path) > 1) {
        foreach($arr_settings_path AS $arr_settings_path_key => $arr_settings_path_value) {
            $part_settings_path = "";

            if(strlen($arr_settings_path_value)) {
                $sSQL = "SELECT DISTINCT
                            static_pages.name
                            , static_pages.parent
                            , " . FF_PREFIX . "languages.code AS language_code
                            , static_pages_rel_languages.smart_url
                        FROM static_pages_rel_languages 
                            INNER JOIN static_pages ON static_pages.ID = static_pages_rel_languages.ID_static_pages
                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = static_pages_rel_languages.ID_languages
                        WHERE 
                            static_pages_rel_languages.smart_url = " . $db->toSql($arr_settings_path_value, "Text") . "
                            AND static_pages.ID_domain = " . $db->toSql($ID_domain, "Number") . "
                        ORDER BY
                        		LENGTH( REPLACE( static_pages.parent, " . $db->toSql($static_part_url, "Text") . ", '' ) ) 
                        		, IF(" . FF_PREFIX . "languages.code = " . $db->toSql($language) . "
                        			, 0
                        			, IF(" . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_DEFAULT) . "
                        				, 1
                        				, language_code
                        			)
                        		) ASC";
                $db->query($sSQL);
                if($db->nextRecord()) {  
                    $part_settings_path = $db->getField("name", "Text", true);

  					do {
                        if($db->getField("parent", "Text", true) == ($static_part_url ? $static_part_url : "/")) {
                            $arrLangAlt[$db->getField("language_code", "Text", true)]++;
                            if($db->getField("smart_url", "Text", true) == $arr_settings_path_value) {
                                $arrRealLangAlt[$db->getField("language_code", "Text", true)]++;
                            }
                        }

					} while($db->nextRecord());
				}

				if(strlen($part_settings_path)) {
                    //$arrLayout = array();
                   
                    $static_part_url .= "/" . $part_settings_path;
                    //$static_part_url .= "/" . $arr_settings_path_value;
                    
					if(!strlen($tmp_vgallery_part_url) && !strlen($tmp_gallery_part_url)) {
						if(!$layout_found && array_key_exists($static_part_url, $arrLayout)) {
	                        $next_value = $arr_settings_path[$arr_settings_path_key + 1];
	                        foreach($arrLayout[$static_part_url] AS $arrLayout_key => $arrLayout_value) {
	                             if($arrLayout_value["type"] == "virtual-gallery") {
	                                $tmp_vgallery_part_url = "/" . rtrim($arrLayout_value["value"] . rtrim($arrLayout_value["params"], "/"), "/");
	                                if(count($arrLayout[$static_part_url]) > 1) {
		                                $sSQL = "SELECT vgallery_nodes.*
		                                        FROM vgallery_nodes
													" . ($language != LANGUAGE_DEFAULT
		                                            	? "INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID 
		                                            		AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql($arrLang[$language]["ID"], "Number")
		                                            	: ""
		                                            ) . "
		                                        WHERE 1
		                                            AND " . ($language != LANGUAGE_DEFAULT
		                                            		? "vgallery_nodes_rel_languages.smart_url = " . $db->toSql($next_value, "Text")
		                                            		: "vgallery_nodes.name = " . $db->toSql($next_value, "Text")
			                                            ) . "
		                                            AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
		                                                OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
		                                            )
		                                            AND vgallery_nodes.ID_domain = " . $db->toSql($ID_domain, "Number");
		                                $db->query($sSQL);
	                                    if($db->numRows()) {
	                                    	$layout_found = true;
	                                    	$arrRealLangAlt[$language]++;
	                                        break;
										}
	                                }
	                            } elseif($arrLayout_value["type"] == "gallery") {
	                                $tmp_gallery_part_url = rtrim($arrLayout_value["value"] . rtrim($arrLayout_value["params"], "/"), "/");
	                                if(count($arrLayout[$static_part_url]) > 1) {
	                                    $sSQL = "SELECT files.*
	                                            FROM files
	                                            	" . ($language != LANGUAGE_DEFAULT
		                                            	? "INNER JOIN files_rel_languages ON files_rel_languages.ID_files = files.ID 
		                                            		AND files_rel_languages.ID_languages = " . $db->toSql($arrLang[$language]["ID"], "Number")
		                                            	: ""
		                                            ) . "
	                                            WHERE 1 
	                                                AND " . ($language != LANGUAGE_DEFAULT
		                                            		? "files_rel_languages.smart_url = " . $db->toSql($next_value, "Text")
		                                            		: "files.name = " . $db->toSql($next_value, "Text")
			                                            ) . "
	                                                AND (files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "' 
	                                                    OR files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "/%'
	                                                )
	                                                AND files.ID_domain = " . $db->toSql($ID_domain, "Number");
	                                    $db->query($sSQL);
	                                    if($db->numRows()) {
	                                    	$layout_found = true;
	                                    	$arrRealLangAlt[$language]++;
	                                        break;
										}
	                                }
	                            }
	                        } reset($arrLayout);
	                     } 
					} else {
                        if(strlen($tmp_vgallery_part_url)) 
                            $tmp_vgallery_part_url = $tmp_vgallery_part_url . "/" . $db->getField("name", "Text", true);
			
                        if(strlen($tmp_gallery_part_url)) 
                            $tmp_gallery_part_url = $tmp_gallery_part_url . "/" . $db->getField("name", "Text", true);
					}
                }

                if(!strlen($part_settings_path)) {
					$sSQL = "SELECT DISTINCT vgallery_nodes.name AS name
                                , vgallery_nodes.parent AS parent
                    			, " . FF_PREFIX . "languages.code AS language_code
                                , vgallery_nodes_rel_languages.smart_url AS smart_url
                            FROM vgallery_nodes_rel_languages
                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_nodes_rel_languages.ID_lang
                                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_nodes_rel_languages.ID_nodes
                            WHERE
                                vgallery_nodes_rel_languages.smart_url = " . $db->toSql($arr_settings_path_value, "Text") . "
                                AND vgallery_nodes.ID_domain = " . $db->toSql($ID_domain, "Number") . "
                            ORDER BY 
                            	LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url . $vgallery_part_url, "Text") . ", '' ) )
                            	, IF(" . FF_PREFIX . "languages.code = " . $db->toSql($language) . "
                        			, 0
                        			, IF(" . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_DEFAULT) . "
                        				, 1
                        				, 2
                        			)
                        		) ASC
                        		, language_code";
                    $db->query($sSQL); 
                   /* $sSQL = "SELECT DISTINCT vgallery_nodes.name AS name
                                , vgallery_nodes.parent AS parent
                    			, " . FF_PREFIX . "languages.code AS language_code
                                , vgallery_rel_nodes_fields.description AS smart_url
                            FROM vgallery_rel_nodes_fields
                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                            WHERE
                                vgallery_fields.name = " . $db->toSql("smart_url", "Text") . "
                                AND vgallery_rel_nodes_fields.description = " . $db->toSql($arr_settings_path_value, "Text") . "
                                AND vgallery_nodes.ID_domain = " . $db->toSql($ID_domain, "Number") . "
                            ORDER BY 
                            	LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url . $vgallery_part_url, "Text") . ", '' ) )
                            	, IF(" . FF_PREFIX . "languages.code = " . $db->toSql($language) . "
                        			, 0
                        			, IF(" . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_DEFAULT) . "
                        				, 1
                        				, 2
                        			)
                        		) ASC
                        		, language_code";
                    $db->query($sSQL);*/
                    if($db->nextRecord()) {
                        $part_settings_path = $db->getField("name", "Text", true);

						do {
                            if($db->getField("parent", "Text", true) == ($tmp_vgallery_part_url . $vgallery_part_url ? $tmp_vgallery_part_url . $vgallery_part_url : "/")) {
                                $arrLangAlt[$db->getField("language_code", "Text", true)]++;
                                if($db->getField("smart_url", "Text", true) == $arr_settings_path_value) {
                                    $arrRealLangAlt[$db->getField("language_code", "Text", true)]++;

                                }
                            }
 						} while($db->nextRecord());

                        $vgallery_part_url .= "/" . $part_settings_path;
                    }
                }

                if(0 && !strlen($part_settings_path)) {  //da seri problemi con lo switch della lingua. Necessario per la visualizzazione delle dir gallery
                    $sSQL = "SELECT DISTINCT files.name
                                , files.parent
                    			, " . FF_PREFIX . "languages.code AS language_code
                                , files_rel_languages.smart_url
                            FROM files_rel_languages
                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = files_rel_languages.ID_languages
                                INNER JOIN files ON files.ID = files_rel_languages.ID_files
                            WHERE
                                ((files_rel_languages.smart_url = '' AND files.name = " . $db->toSql($arr_settings_path_value, "Text") . ")
                                	OR files_rel_languages.smart_url = " . $db->toSql($arr_settings_path_value, "Text") . "
                                )
                                AND files.ID_domain = " . $db->toSql($ID_domain, "Number") . "
                                AND files.is_dir > 0
                            ORDER BY 
                            	LENGTH( REPLACE( files.parent, " . $db->toSql($tmp_gallery_part_url . $gallery_part_url, "Text") . ", '' ) )
                            	, IF(" . FF_PREFIX . "languages.code = " . $db->toSql($language) . "
                        			, 0
                        			, IF(" . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_DEFAULT) . "
                        				, 1
                        				, 2
                        			)
                        		) ASC
                        		, language_code";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $part_settings_path = $db->getField("name", "Text", true);

						do {
                            if($db->getField("parent", "Text", true) == ($tmp_gallery_part_url . $gallery_part_url ? $tmp_gallery_part_url . $gallery_part_url : "/")) {
                                $arrLangAlt[$db->getField("language_code", "Text", true)]++;
                                if($db->getField("smart_url", "Text", true) == $arr_settings_path_value) {
                                    $arrRealLangAlt[$db->getField("language_code", "Text", true)]++;
                                }
                            }
						} while($db->nextRecord());
                        
                        $gallery_part_url .= "/" . $part_settings_path;                        
                    }
                }
                
                if(strlen($part_settings_path)) {
                    $real_settings_path .= "/" . $part_settings_path;
				} else {
                    $real_settings_path .= "/" . $arr_settings_path_value;
                    //$block_redirect = true;
                    $invalid_path++;
                }
                
                $source_settings_path .= "/" . $arr_settings_path_value;

                $processed_path++;
            }
        }
    }

    if(!strlen($real_settings_path))
        $real_settings_path = "/";
/*
    if(is_array($arrLangAlt) && count($arrLangAlt)) {
		$max_lang = max($arrLangAlt);
    	if($max_lang >= $processed_path) {
    		foreach($arrLangAlt AS $arrLangAlt_key => $arrLangAlt_value) {
				if($arrLangAlt_value == $max_lang) {
				    $target_lang = $arrLangAlt_key;
					if($arrLangAlt_key == $language) {
						break;
					}
				}
    		}
    	}
	}
    if(is_array($arrRealLangAlt) && count($arrRealLangAlt)) {
        $max_lang = max($arrRealLangAlt);
        if($max_lang >= $processed_path) {
            foreach($arrRealLangAlt AS $arrRealLangAlt_key => $arrRealLangAlt_value) {
                if($arrRealLangAlt_value == $max_lang) {
                    if($arrRealLangAlt_key != $language) {
                        if(strlen($str_lang_alt))
                            $str_lang_alt .= ",";

                        $str_lang_alt .= $arrRealLangAlt_key;
                    }
                }
            }
        }
    }*/

	//if(!$target_lang)
	//	$target_lang = $language;        

	if(!$block_redirect) {
	    if($arrRealLangAlt[$language] < $processed_path && check_function("normalize_url")) {   
	        $new_settings_path = normalize_url($real_settings_path, HIDE_EXT, false, $language, false);

	        if($new_settings_path != $settings_path) {
	            if(!function_exists("write_notification") && check_function("write_notification"))
	                write_notification("_error_invalid_path_redirect", $real_settings_path, "warning", "", $settings_path, true, -1, null, "url");

	                ffRedirect($new_settings_path, 301);
	        }
	    }
	}   
	
	return array("source" => $source_settings_path
		, "url" => $real_settings_path
		, "lang" => $arrRealLangAlt
		, "lang_alt" => $arrLangAlt
	);	

	/*     
    if($invalid_path && $enable_redirect) {
    	if($invalid_path == $processed_path) {
			return array("source" => $source_settings_path
						, "url" => $real_settings_path
						, "lang" => $target_lang
						, "lang_alt" => $str_lang_alt
					);
    	} else {
    		if($target_lang == $language && $real_settings_path != $settings_path && !$block_redirect) {
				if(check_function("normalize_url")) {
					$new_settings_path = normalize_url($real_settings_path, HIDE_EXT, false, $language);
					if($new_settings_path == $settings_path) {
						
						if(!function_exists("process_html_page_error") && check_function("process_html_page_error"))
							process_html_page_error(404);
					} else {
                        if(!function_exists("write_notification") && check_function("write_notification"))
						    write_notification("_error_invalid_path_redirect", $real_settings_path, "warning", "", $settings_path, true, -1, null, "url");
                            
                            ffRedirect($new_settings_path, 301);
					}
				}
			} else {
				return array("source" => $source_settings_path
							, "url" => ($max_lang >= $processed_path ?  $source_settings_path : $real_settings_path)
							, "lang" => $target_lang
							, "lang_alt" => $str_lang_alt
						);			
			}
		}
    } else {
    	if($target_lang == $language) {
			return array(
				"url" => $real_settings_path
				, "lang_alt" => $str_lang_alt
			);
		} else {
		//echo $source_settings_path . "   " . $real_settings_path . "   " . $processed_path;
    		return array("source" => $source_settings_path
						, "url" => $real_settings_path
						, "lang" => $target_lang
						, "lang_alt" => $str_lang_alt
					);		
		}
	}*/
}