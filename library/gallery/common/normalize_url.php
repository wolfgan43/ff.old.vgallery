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
function normalize_url_by_current_lang_old($url, $prefix = true) {
	$globals = ffGlobals::getInstance("gallery");
	
	if($globals->page["alias"]) {
		if(strpos($url, $globals->page["alias"]) === 0) {
			$url = substr($url, strlen($globals->page["alias"]));
		} else {
			$prefix_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://www." . substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1);
		}
	}
	
	if($prefix === true) {
		$prefix = $globals->locale["prefix"];
	}
	$res = FF_SITE_PATH . $prefix . $url;
	if ($res != "/" && substr($res,-1) == "/")
		$res = substr($res,0,-1);

	return $prefix_url . $res;
}

function normalize_url_by_current_lang($user_path, $prefix = true, $abs_url = false) {
	$globals = ffGlobals::getInstance("gallery");
	$schema = cache_get_settings();

	if($prefix === true) {
		$prefix = $globals->locale["prefix"];
	}
	$res = FF_SITE_PATH . $prefix . $user_path;
	if ($res != "/" && substr($res,-1) == "/")
		$res = substr($res,0,-1);
		
    $arrSettings_path = explode("/", trim($res, "/"));
	if(is_array($schema["alias"]) && count($schema["alias"])) {
		$alias_flip = array_flip($schema["alias"]); 
		if($alias_flip["/" . $arrSettings_path[0]]) {
			$res = substr($res, strlen("/" . $arrSettings_path[0]));
			$prefix_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $alias_flip["/" . $arrSettings_path[0]];
		}
	}

	if($abs_url && !$prefix_url) {
		$domain = (defined("DOMAIN_DEFAULT")
			? DOMAIN_DEFAULT
			: (substr_count(DOMAIN_NAME, ".") > 1
				? DOMAIN_NAME
				: "www." . DOMAIN_NAME
			)
		);
		
		
		//substr($domain_name, 0, strpos($domain_name, "."));
		$prefix_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $domain;
	}
		
	return $prefix_url . $res;
}

function normalize_url($url, $hide_ext = HIDE_EXT, $encode_url = true, $international = false, $url_by_lang = true, $concat_field = "smart_url", $basename = false, $sep = null, $force_vgallery_group = null) {
	$globals = ffGlobals::getInstance("gallery");
   	// static $cache_url;
    static $arrLayout = null;
    
    $db = ffDB_Sql::factory();

    $home_set = false;
    $part_url = @parse_url($url);
    //$url = url_rewrite($url);

	$part_url["path"] = rtrim($part_url["path"], "/");
	
    if($sep === null) {
        if($concat_field == "smart_url") {
            $sep = "/";
        } else {
            if(is_array($concat_field))
                $sep = ffTemplate::_get_word_by_code("separator_multi");
            else
                $sep = ffTemplate::_get_word_by_code("separator_" . $concat_field);
        }
    } 

    if($international
    	&& !($international == LANGUAGE_DEFAULT  && $concat_field == "smart_url")
    ) {
    	$cache_url = $url;
    	if(strlen(FF_SITE_PATH) && strpos($cache_url, FF_SITE_PATH) === 0)
    		$cache_url = substr($cache_url, strlen(FF_SITE_PATH));
    	
    	if(strpos($cache_url, "?") !== false)
    		$cache_url = substr($cache_url, 0, strpos($cache_url, "?"));
    	
        $strCompare = md5("compare" 
	                    . ($hide_ext ? "1" : "0")
	                    . ($encode_url ? "1" : "0")
	                    . $international
	                    . (is_array($concat_field) ? implode(",", $concat_field) : $concat_field)
	                    . ($basename ? "1" : "0")
	                    . $sep
	                    . $url
                    );

        if(!$arrLayout) {
        	$arrLayout = array();
			$sSQL = "SELECT layout.ID
			            , layout.value
			            , layout.params
			            , layout_type.name AS `type`
			            , layout_path.path AS layout_path
			        FROM layout 
			            INNER JOIN layout_type ON layout_type.ID = layout.ID_type
			            INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
			        WHERE 1
						AND (layout_type.name = " . $db->toSql("GALLERY", "Text") . "
                        	OR layout_type.name = " . $db->toSql("VIRTUAL_GALLERY", "Text") . "
                        )
			            AND layout.ID_domain = " . $db->toSql($globals->ID_domain, "Number");
			 $db->query($sSQL);
             if($db->nextRecord()) {
				do {
					$ID_layout = $db->getField("ID", "Number", true);
					$layout_path = $db->getField("layout_path", "Text", true);
					if(!strlen($layout_path))
						$layout_path = "/";
						
					$arrLayout[$layout_path][$ID_layout]["value"] = $db->getField("value", "Text", true);
					$arrLayout[$layout_path][$ID_layout]["params"] = $db->getField("params", "Text", true);
					$arrLayout[$layout_path][$ID_layout]["type"] = $db->getField("type", "Text", true);
				} while($db->nextRecord());
             }
        }		
		
        $arr_part_url = explode("/", $part_url["path"]);

        if(is_array($arr_part_url) && count($arr_part_url)) {
            foreach($arr_part_url AS $arr_part_url_key => $arr_part_url_value) {
                $part_arr_part_url = "";
                $real_arr_part_url = "";
                
				$count_part++;
				if(!strlen($arr_part_url_value)) {
                    if(!(!strlen($part_url["path"]) && $concat_field != "smart_url")) {
					    continue;
                    }
                }
                //if(strlen($arr_part_url_value) ||  (!strlen($part_url["path"]) && $concat_field != "smart_url")) {
                	$first_static = false;
                	$skip_static = false;
                    //if(is_array($concat_field) || $concat_field == "meta_title" || $concat_field == "meta_title_alt" || $concat_field == "keywords" || $concat_field == "meta_description" || $concat_field == "smart_url" || $concat_field == "title") {
                    if($concat_field) {
						if($international == LANGUAGE_DEFAULT) {
							$sSQL = "SELECT static_pages.*
		                            FROM static_pages 
		                            WHERE 
		                                static_pages.name = " . $db->toSql($arr_part_url_value, "Text") . 
		                                (strlen($static_part_url)
		                                    ? " AND static_pages.parent = " . $db->toSql($static_part_url, "Text")
		                                    : " "
		                                );
                        } else {
	                        $sSQL = "SELECT static_pages_rel_languages.*
	                                FROM static_pages_rel_languages 
	                                    INNER JOIN static_pages ON static_pages.ID = static_pages_rel_languages.ID_static_pages
	                                    INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = static_pages_rel_languages.ID_languages
	                                WHERE 
	                                    static_pages.name = " . $db->toSql($arr_part_url_value, "Text") . 
	                                    (strlen($static_part_url)
	                                        ? " AND static_pages.parent = " . $db->toSql($static_part_url, "Text")
	                                        : " "
	                                    ) . "
	                                    AND " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text");
						}
	                    $db->query($sSQL);
	                    if($db->nextRecord()) {
							if(is_array($concat_field)) {
			                    foreach($concat_field AS $concat_field_value) {
			                    	$part_arr_part_url = $db->getField($concat_field_value, "Text", true, false);
			                    	if($part_arr_part_url)
			                    		break;
			                    }
							} else {
		                    	$part_arr_part_url = $db->getField($concat_field, "Text", true, false);
							
							}		                    

	                        $real_arr_part_url = $part_arr_part_url;
	                        $static_part_url .= "/" . $arr_part_url_value;
						}

                        
						if(strlen($static_part_url))
							$tmp_static_part_url = $static_part_url;
						else
							$tmp_static_part_url = "/";

                        if(!$layout_found && array_key_exists($tmp_static_part_url, $arrLayout)) {
							$tmp_vgallery_part_url = "";
							$tmp_gallery_part_url = "";

							$next_value = $arr_part_url[$arr_part_url_key + 1];
							foreach($arrLayout[$tmp_static_part_url] AS $arrLayout_key => $arrLayout_value) {
						 		if($arrLayout_value["type"] == "VIRTUAL_GALLERY") {
									$tmp_vgallery_part_url = "/" . rtrim($arrLayout_value["value"] . rtrim($arrLayout_value["params"], "/"), "/");
									if(count($arrLayout[$tmp_static_part_url]) > 1) {
						                $sSQL = "SELECT vgallery_nodes.*
						                        FROM vgallery_nodes
						                        WHERE 1
						                            AND vgallery_nodes.name = " . $db->toSql($next_value, "Text") . "
													AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
	                                                    OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
                                                	)
						                        ";
										$db->query($sSQL);
										if($db->numRows()) {
											$layout_found = true;
											break;
										}
									}
								} elseif($arrLayout_value["type"] == "GALLERY") {
									$tmp_gallery_part_url = rtrim($arrLayout_value["value"] . rtrim($arrLayout_value["params"], "/"), "/");
									if(count($arrLayout[$tmp_static_part_url]) > 1) {
						                $sSQL = "SELECT files.*
						                        FROM files
						                        WHERE 1 
						                            AND files.name = " . $db->toSql($next_value, "Text") . "
													AND (files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "' 
	                                                    OR files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "/%'
                                                	)
						                        ";
										$db->query($sSQL);
										if($db->numRows()) {
											$layout_found = true;
											break;
										}
									}
								}
							} reset($arrLayout[$tmp_static_part_url]);
/*
							if(strlen($tmp_vgallery_part_url))
								$vgallery_part_url = $tmp_vgallery_part_url;
							if(strlen($tmp_gallery_part_url))
								$gallery_part_url = $tmp_gallery_part_url;*/

						} 

                    }

                    if(!strlen($part_arr_part_url)) {
                    	if(!strlen($tmp_vgallery_part_url))
                    		$tmp_vgallery_part_url = $real_part_url . "/" . $arr_part_url_value;

                        if($force_vgallery_group === null) {
	                        if(count($arr_part_url) == $count_part && function_exists("check_function") && check_function("get_vgallery_group")) {
	                            if(is_array($concat_field)) {
	                                foreach($concat_field AS $concat_field_value) {
	                                    $vgallery_group = get_vgallery_group($arr_part_url_value, $concat_field_value);
	                                    if(!strlen($vgallery_group)) 
	                                        $vgallery_group = get_vgallery_group($arr_part_url_value, "name");
	                                    break;
	                                }
	                            } else {
	                                $vgallery_group = get_vgallery_group($arr_part_url_value, "name");
	                            }
	                        } else {
	                            $vgallery_group = "";
	                        }
						}
                        if($vgallery_group) {
                        	if(is_array($concat_field)) {
                        		$tmp_concat_field = implode("|", $concat_field);
							} else {
								$tmp_concat_field = $concat_field;
							}
                            if(strpos($tmp_concat_field, "smart_url") !== false) {
                                $part_arr_part_url = ffCommon_url_rewrite($vgallery_group);
                            } elseif(is_array($concat_field) && strpos($tmp_concat_field, "meta_title") !== false) {
                                if(strpos($real_part_url, $sep) !== false)
                                    $part_arr_part_url = $real_part_url . " " . $sep . " " . $vgallery_group;
                                else 
                                    $part_arr_part_url = $last_part_arr_part_url . " " . $sep . " " . $vgallery_group;
                            } elseif(!is_array($concat_field) && strpos($tmp_concat_field, "meta_title") !== false) {
                                if(strpos($real_part_url, $sep) !== false)
                                    $part_arr_part_url = substr($real_part_url, 0, strpos($real_part_url, $sep)) . " " . $vgallery_group . " " . substr($real_part_url, strpos($real_part_url, $sep));
                                else {
                            		if(!strpos($last_part_arr_part_url, $sep) === false)
										$part_arr_part_url = substr($last_part_arr_part_url, 0, strpos($last_part_arr_part_url, $sep)) . " " . $vgallery_group . " " . substr($last_part_arr_part_url, strpos($last_part_arr_part_url, $sep));                            		
                            		else
                            			$part_arr_part_url = $last_part_arr_part_url . " " . $vgallery_group;
								}
							} elseif(strpos($tmp_concat_field, "meta_description") !== false) {
                                if(strpos($real_part_url, $sep) !== false)
                                    $part_arr_part_url = $real_part_url;
                                else 
                                    $part_arr_part_url = $last_part_arr_part_url;
                            } elseif(strpos($tmp_concat_field, "keywords") !== false) {
                                if(strpos($real_part_url, $sep) !== false) {
                                    $part_arr_part_url = $real_part_url;
								} else {
									if(strlen($last_part_arr_part_url))
                                    	$part_arr_part_url = $last_part_arr_part_url;
                                    else
                                    	$part_arr_part_url = $vgallery_group;
								}
							} else {
                                if(strpos($real_part_url, $sep) !== false)
                                    $part_arr_part_url = $vgallery_group . " " . $real_part_url;
                                else 
                                    $part_arr_part_url = $vgallery_group . " " . $last_part_arr_part_url;
                            }
                        } else {
                        	if($international == LANGUAGE_DEFAULT) {
								$sSQL = "SELECT DISTINCT vgallery_nodes.*
			                                , vgallery_nodes.parent AS actual_parent
			                                , vgallery_nodes.ID_type AS ID_type
			                            FROM vgallery_nodes
	                                    WHERE
	                                        vgallery_nodes.name = " . $db->toSql($arr_part_url_value, "Text") . "
	                                        AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
	                                                OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
	                                        )
	                                    ORDER BY LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url, "Text") . ", '' ) )";
                        	} else {
								$sSQL = "SELECT DISTINCT vgallery_nodes_rel_languages.*
			                                , vgallery_nodes.parent AS actual_parent
			                                , vgallery_nodes.ID_type AS ID_type
			                            FROM vgallery_nodes_rel_languages
			                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_nodes_rel_languages.ID_lang
			                                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_nodes_rel_languages.ID_nodes
	                                    WHERE
	                                        " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text") . "
	                                        AND vgallery_nodes.name = " . $db->toSql($arr_part_url_value, "Text") . "
	                                        AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
	                                                OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
	                                        )
	                                    ORDER BY LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url, "Text") . ", '' ) )";
							}

		                    $db->query($sSQL); 
		                    if($db->nextRecord()) {
			                    if(is_array($concat_field)) {
			                    	foreach($concat_field AS $concat_field_value) {
			                    		$part_arr_part_url = $db->getField($concat_field_value, "Text", true, false);
			                    		if($part_arr_part_url)
			                    			break;
			                    	}
								} else {
		                    		$part_arr_part_url = $db->getField($concat_field, "Text", true, false);
								
								}		                    
	                            $actual_parent = $db->getField("actual_parent")->getValue();
	                            $vgallery_ID_type = $db->getField("ID_type", "Number", true);
	                            $tmp_vgallery_part_url .= "/" . $arr_part_url_value;
		                    }
		                    
		                    /*                             
                            if(is_array($concat_field)) {
                                foreach($concat_field AS $concat_field_key => $concat_field_value) {
                                    $sSQL = "SELECT vgallery_rel_nodes_fields.description AS name
                                    			, vgallery_nodes.parent AS actual_parent
                                    			, vgallery_nodes.ID_type AS ID_type
                                            FROM vgallery_rel_nodes_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                                            WHERE
                                                " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text") . "
                                                AND vgallery_fields.name = " . $db->toSql($concat_field_value, "Text") . "
                                                AND vgallery_nodes.name = " . $db->toSql($arr_part_url_value, "Text") . "
                                                AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
                                                        OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
                                                )
                                            ORDER BY LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url, "Text") . ", '' ) )";
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $last_part_arr_part_url = $db->getField("name")->getValue();
                                        $last_actual_parent = $db->getField("actual_parent")->getValue();
                                        $last_vgallery_ID_type = $db->getField("ID_type", "Number", true);
                                    }
                                    $sSQL = "SELECT vgallery_rel_nodes_fields.description AS name
                                    			, vgallery_nodes.parent AS actual_parent
                                    			, vgallery_nodes.ID_type AS ID_type
                                            FROM vgallery_rel_nodes_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                                            WHERE
                                                " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text") . "
                                                AND vgallery_fields.name = " . $db->toSql($concat_field_key, "Text") . "
                                                AND vgallery_nodes.name = " . $db->toSql($arr_part_url_value, "Text") . "
                                                AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
                                                        OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
                                                )
                                                AND vgallery_rel_nodes_fields.description <> ''
                                            ORDER BY LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url, "Text") . ", '' ) )";
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $part_arr_part_url = $db->getField("name")->getValue();
                                        $actual_parent = $db->getField("actual_parent")->getValue();
                                        $vgallery_ID_type = $db->getField("ID_type", "Number", true);
                                        $tmp_vgallery_part_url .= "/" . $arr_part_url_value;
                                    } else {
                                        $part_arr_part_url = $last_part_arr_part_url;
                                        $actual_parent = $last_actual_parent;
                                        $vgallery_ID_type = $last_vgallery_ID_type;
                                    }
                                    break;
                                }
                            } else { 
                                $sSQL = "SELECT vgallery_rel_nodes_fields.description AS name
                                			, vgallery_nodes.parent AS actual_parent
                                			, vgallery_nodes.ID_type AS ID_type
                                        FROM vgallery_rel_nodes_fields
                                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                            INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                                        WHERE
                                            " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text") . "
                                            AND vgallery_fields.name = " . $db->toSql($concat_field, "Text") . "
                                            AND vgallery_nodes.name = " . $db->toSql($arr_part_url_value, "Text") . "
                                            AND (vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "' 
                                                    OR vgallery_nodes.parent LIKE '%" . $db->toSql($tmp_vgallery_part_url, "Text", false) . "/%'
                                            )
                                        ORDER BY LENGTH( REPLACE( vgallery_nodes.parent, " . $db->toSql($tmp_vgallery_part_url, "Text") . ", '' ) )";
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $part_arr_part_url = $db->getField("name")->getValue();
                                    $actual_parent = $db->getField("actual_parent")->getValue();
                                    $vgallery_ID_type = $db->getField("ID_type", "Number", true);
                                    $tmp_vgallery_part_url .= "/" . $arr_part_url_value;
                                }
                            }*/
                        }
                        if(strlen($force_vgallery_group) && strlen($part_arr_part_url)) {
                            if(function_exists("check_function") && check_function("get_vgallery_group"))
                        	    $arrVgalleryGroup = get_vgallery_group($force_vgallery_group, "name", $actual_parent);
                            if(is_array($arrVgalleryGroup)) {
            					if(strlen($arrVgalleryGroup["limit_type"]))
            						$tmp_group_limit_type  = explode(",", $arrVgalleryGroup["limit_type"]);
            					else
            						$tmp_group_limit_type = array();
            						
								if(!count($tmp_group_limit_type) || in_array($vgallery_ID_type, $tmp_group_limit_type))
                            		$part_arr_part_url = $part_arr_part_url . "_" . $force_vgallery_group;
							}
						}
                    }

                    if(!strlen($part_arr_part_url)) {
                    	if(!strlen($tmp_gallery_part_url))
                    		$tmp_gallery_part_url = $real_part_url . "/" . $arr_part_url_value;

	                    $sSQL = "SELECT files_rel_languages.*
	                            FROM files_rel_languages
	                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = files_rel_languages.ID_languages
	                                INNER JOIN files ON files.ID = files_rel_languages.ID_files
	                            WHERE
	                                " . FF_PREFIX . "languages.code = " . $db->toSql($international, "Text") . "
	                                AND files.name = " . $db->toSql($arr_part_url_value, "Text") . "
	                                AND (files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "' 
	                                        OR files.parent LIKE '%" . $db->toSql($tmp_gallery_part_url, "Text", false) . "/%'
	                                )
	                                AND files.is_dir > 0
	                            ORDER BY LENGTH( REPLACE( files.parent, " . $db->toSql($tmp_gallery_part_url, "Text") . ", '' ) )";
                        $db->query($sSQL); 
                        if($db->nextRecord()) {
							if(is_array($concat_field)) {
			                    foreach($concat_field AS $concat_field_value) {
			                    	$part_arr_part_url = $db->getField($concat_field_value, "Text", true, false);
			                    	if($part_arr_part_url)
			                    		break;
			                    }
							} else {
		                    	$part_arr_part_url = $db->getField($concat_field, "Text", true, false);
							
							}	                        

                            $tmp_gallery_part_url .= "/" . $arr_part_url_value;
                        }
                    }

                    if(!strlen($part_arr_part_url)) {
                    	if($concat_field == "smart_url") {
                    		$part_arr_part_url = $arr_part_url_value;
						} else {
                            if(strlen($real_arr_part_url)) {
                                $part_arr_part_url = $real_arr_part_url;
                            } elseif(strlen($arr_part_url_value)) {
                            	if(($concat_field == "meta_title" || $concat_field == "title")
                            		&& count($arr_part_url) == $count_part
                            		&& $basename
                            	) {
									$part_arr_part_url = "[META_TITLE]";
                            	} else {
                                	$part_arr_part_url = ffTemplate::_get_word_by_code($arr_part_url_value);
								}
                            }
						}
					}
                        
                    if($concat_field == "smart_url") {
                        if(strlen($part_arr_part_url)) {
                            $real_part_url .= $sep . $part_arr_part_url;
                        } else {
                            $real_part_url .= $sep . $arr_part_url_value;
                        }
                    } elseif($concat_field == "meta_title") {
                        if(strlen($part_arr_part_url)) {
                            if(strlen($real_part_url))
                                $real_part_url = $sep . $real_part_url;

                            $real_part_url = $part_arr_part_url . $real_part_url;
                            if(!strlen($arr_part_url_value))
                            	$home_set = true;

                            $count_meta++;
                        } 
                    } else {
						 if(strlen($part_arr_part_url)) {
                            $real_part_url = $part_arr_part_url;
						 }
                    }
					$reverse_part_url .= "/" . $arr_part_url_value;     
				//}
            }
        }

		if($concat_field == "meta_title" && count($arr_part_url) > 1 && $count_meta == 1 && $home_set == true)
			$real_part_url = "";
		

        $part_url["path"] = $real_part_url;
		$url_normalizzed = normalize_url_by_current_lang($url_normalizzed, $prefix);
        
        if($url_by_lang)
        	$part_url["path"] = normalize_url_by_current_lang($part_url["path"], "/" . $globals->locale["lang"][$international]["tiny_code"]);
    }

    if($hide_ext) {
        $part_url["path"] = preg_replace('/\.' . FF_PHP_EXT . '$/', '', $part_url["path"]);
    }

    if($encode_url)
        $part_url["path"] = str_replace("%2F", "/", urlencode(str_replace("%", "", $part_url["path"])));

    if(!strlen($part_url["path"]) && $concat_field == "smart_url") {
        $part_url["path"] = "/";
	}

    if(strlen($part_url["query"]))
        $url_normalizzed = $part_url["path"] . "?" . $part_url["query"];
    else 
        $url_normalizzed = $part_url["path"];

    if(strlen($part_url["fragment"]))
        $url_normalizzed .= "#" . $part_url["fragment"];

    if(!$basename) {
    	if($encode_url)
    		$url_normalizzed = ffCommon_specialchars($url_normalizzed);
	} else {
        if($concat_field == "smart_url") {
            $url_normalizzed = basename($url_normalizzed);
        } elseif($concat_field == "meta_title") {
            if(strpos($url_normalizzed, $sep) !== false)
                $url_normalizzed =  substr($url_normalizzed, 0, strpos($url_normalizzed, $sep));
            else 
                $url_normalizzed =  $url_normalizzed;
        } else {   
            $url_normalizzed = $url_normalizzed;
        }
    }

	$globals = ffGlobals::getInstance("gallery");
	if(isset($globals->strip_user_path) && strlen($globals->strip_user_path)) {
		if(strpos($url_normalizzed, FF_SITE_PATH . $globals->strip_user_path) === 0) {
			if(strlen($url_normalizzed) == strlen(FF_SITE_PATH . $globals->strip_user_path)) {
				$url_normalizzed = "/";
			} else {
				$url_normalizzed = substr($url_normalizzed, strlen(FF_SITE_PATH . $globals->strip_user_path));
			}
		}
	}

    return $url_normalizzed;
}   