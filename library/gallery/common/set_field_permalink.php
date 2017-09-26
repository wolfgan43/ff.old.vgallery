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
function set_field_permalink($tbl_src, $ID_node, $exclude_category = false, $update_only_empty = false, $permalink_rule = null) {
  	$db = ffDB_Sql::factory();
  	$arrLang = array();
  	$arrPermalinkParent = array();
  	$arrPermalink = array();
  	$oldPermalink = array();

  	if(!$permalink_rule)
  		$permalink_rule = "[PARENT]/[SMART_URL]";
  	
	switch($tbl_src) {
  		case "anagraph":
  			$arrTable = array(
  				"name" => null
  				, "primary" => "anagraph"
                , "strip_parent" => 1
  				, "field_lang" => false
  				, "field_node" => "ID"
  				, "field_permalink" => "permalink"
  				, "field_smart_url" => "smart_url"
  				, "field_permalink_parent" => "permalink_parent"
  				, "default_smart_url" => "smart_url"
  				, "default_permalink_parent" => "parent"
  				, "default_permalink" => "permalink"
                , "field_source_user_path" => null
  				, "sSQL_source_user_path" => "SELECT IF(anagraph_categories.smart_url
  													, IF(LOCATE(CONCAT('/', anagraph_categories.smart_url), layout_path.path) = 1
												        , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(CONCAT('/', anagraph_categories.smart_url), layout_path.path) + LENGTH(CONCAT('/', anagraph_categories.smart_url))))
												        , IF(LOCATE(CONCAT('/', anagraph_categories.smart_url), layout_path.path) > 1
												            , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(CONCAT('/', anagraph_categories.smart_url), layout_path.path)))
												            , layout_path.path
												        )
												    )
												    , layout_path.path
				                                ) AS source_user_path
												, layout.params AS start_path
											FROM layout
 												INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
												LEFT JOIN anagraph_categories ON anagraph_categories.ID = layout.params
											WHERE layout.value = 'anagraph'"
  			);
  			break;
  		case "static_pages":
  			$arrTable = array(
  				"name" => "static_pages_rel_languages"
  				, "primary" => "static_pages"
  				, "field_lang" => "ID_languages"
  				, "field_node" => "ID_static_pages"
  				, "field_permalink" => "permalink"
  				, "field_smart_url" => "smart_url"
  				, "field_permalink_parent" => "permalink_parent"
  				, "default_smart_url" => "name"
  				, "default_permalink_parent" => "parent"
  				, "default_permalink" => "permalink" 
  			);
  			break;
  		case "vgallery_nodes":
  			if(OLD_VGALLERY) {
  				$arrTable = array(
  					"name" => "vgallery_rel_nodes_fields"
  					, "strip_parent" => 1
  					, "primary" => "vgallery_nodes"
  					, "field_lang" => "ID_lang"
  					, "field_node" => "ID_nodes"
					, "field_permalink" => false
  					, "field_smart_url" => array(
  						"name" => "description"
  						, "where" => " AND ID_fields = ( SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = 'smart_url')"
  					)
  					, "field_permalink_parent" => array(
  						"name" => "description"
  						, "where" => " AND ID_fields = ( SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = 'permalink_parent')"
  						, "insert" => array(
  							"head" => ", ID_fields "
  							, "body" => ", ( SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = 'permalink_parent') "
  						)
  					)
  					, "default_smart_url" => "name"
  					, "default_permalink_parent" => "parent"
  					, "default_permalink" => "permalink"
  					, "where" => array(
  						"vgallery_nodes.visible" => 1
  					)
  					, "update" => array(
  						"uid" => get_session("UserNID")
  						, "last_update" => time()
  					) /*, SUBSTRING(vgallery_nodes.parent, LENGTH(CONCAT('/', layout.value)) + 1)*/
                    , "field_source_user_path" => "ID"
  					, "sSQL_source_user_path" => "SELECT IF(CONCAT('/', layout.value) = CONCAT(vgallery_nodes.parent, vgallery_nodes.name)
  														, layout_path.path
  														, IF(LOCATE(CONCAT('/', layout.value), vgallery_nodes.parent) = 1
													        , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
													            , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))
													            , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) > 1
													                , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(layout.params, layout_path.path)))
													                , layout_path.path
													            )
													        )
															, vgallery_nodes.parent
														)
													) AS source_user_path
													, CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params)) AS start_path
												FROM layout
 													INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
													INNER JOIN vgallery ON vgallery.name = layout.value
													INNER JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID
												WHERE 1"
  				);  
			} else {
				$arrTable = array(
  					"name" => "vgallery_nodes_rel_languages"
  					, "strip_parent" => 1
  					, "primary" => "vgallery_nodes"
  					, "field_lang" => "ID_lang"
  					, "field_node" => "ID_nodes"
  					, "field_permalink" => "permalink"
  					, "field_smart_url" => "smart_url"
  					, "field_permalink_parent" => "permalink_parent"
  					, "default_smart_url" => "name"
  					, "default_permalink_parent" => "parent"
  					, "default_permalink" => "permalink"
                    , "field_source_user_path" => "ID" 
  					, "sSQL_source_user_path" => "SELECT IF(CONCAT('/', layout.value) = CONCAT(vgallery_nodes.parent, vgallery_nodes.name)
  														, layout_path.path
  														, IF(LOCATE(CONCAT('/', layout.value), vgallery_nodes.parent) = 1
													        , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
													            , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))
													            , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) > 1
													                , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(layout.params, layout_path.path)))
													                , layout_path.path
													            )
													        )
															, vgallery_nodes.parent
														)
													) AS source_user_path
													, CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params)) AS start_path
												FROM layout
 													INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0
													INNER JOIN vgallery ON vgallery.name = layout.value
													INNER JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID
												WHERE 1"
  				); 			
			
			}
  			break;
  		case "files":
  			$arrTable = array(
  				"name" => "files_rel_languages"
  				, "primary" => "files"
  				, "field_lang" => "ID_languages"
  				, "field_node" => "ID_files"
  				, "field_permalink" => "permalink"
  				, "field_smart_url" => "smart_url"
  				, "field_permalink_parent" => "permalink_parent"
  				, "default_smart_url" => "name"
  				, "default_permalink_parent" => "parent"
  				, "default_permalink" => false
  			);  		

  			break;
  		case "search_tags_page":
  			$arrTable = array(
  				"name" => null
  				, "primary" => "search_tags_page"
  				, "field_lang" => false
  				, "field_node" => "ID"
  				, "field_permalink" => "permalink"
  				, "field_smart_url" => "smart_url"
  				, "field_permalink_parent" => "parent"
  				, "default_smart_url" => "smart_url"
  				, "default_permalink_parent" => "parent"
  				, "default_permalink" => false
  			);
  			break;  			
  		default:
  			return false;
  	}
  	
  	if($arrTable["field_lang"] && check_function("get_locale")) {
		$arrLang = get_locale("lang", true);
		
		if(is_array($arrLang) && count($arrLang)) {
			foreach($arrLang AS $lang_code => $lang) {
				$arrLangRev[$lang["ID"]] = $lang_code;
			}
		}
	}

	$default_parent = "/";
	$sSQL = "SELECT " . $arrTable["primary"] . "." . $arrTable["default_permalink_parent"] . " AS parent
                , " . $arrTable["primary"] . "." . $arrTable["default_smart_url"] . " AS name
                " . ($arrTable["default_permalink"]
                	? ", " . $arrTable["primary"] . "." . $arrTable["default_permalink"] . " AS permalink"
                	: ""
                ) . "
  			FROM " . $arrTable["primary"] . "
  			WHERE " . $arrTable["primary"] . ".ID = " . $db->toSql($ID_node, "Number");
  	$db->query($sSQL);
  	if($db->nextRecord()) {
  		$default_parent = $db->getField("parent", "Text", true);
  		$default_smart_url = $db->getField("name", "Text", true);

  		$oldPermalink[LANGUAGE_DEFAULT] = ($arrTable["default_permalink"]
  			? $db->getField("permalink", "Text", true)
  			: stripslash($default_parent) . "/" . $default_smart_url
  		);
  		
        if($default_parent == "/" && $default_smart_url == "")
            $is_home = true;

    	if($exclude_category) {
			$parent = "/";
		} else {
	        $parent = $default_parent;
  			if(isset($arrTable["strip_parent"])) {
  				$arrParent = explode("/", trim($parent, "/"));
  				
  				for($i=0;$i<$arrTable["strip_parent"];$i++) {
  					unset($arrParent[$i]);
  				}

  				$parent = "/" . implode("/", $arrParent);
			}
		}
  	}

  	if(is_array($arrTable["where"]) && count($arrTable["where"])) {
  		foreach($arrTable["where"] AS $field_name => $field_value) {
  			$sSQL_where .= " AND " . $field_name . " = " . $field_value;
  		}
  	}
  	if(is_array($arrTable["update"]) && count($arrTable["update"])) {
  		foreach($arrTable["update"] AS $field_name => $field_value) {
  			$sSQL_update .= ", " . $field_name . " = " . $field_value;
  			
  			$sSQL_inset_head .= ", " . $field_name;
  			$sSQL_inset_body .= ", " . $field_value;
  		}
  	}
  	  	
 	/**
 	* Source User Path
 	*/
  	$arrSourceUserPath = array();
	if($arrTable["sSQL_source_user_path"]) {
		$sSQL = $arrTable["sSQL_source_user_path"] 
            . ($arrTable["field_source_user_path"]
                ? " AND " . $arrTable["primary"] . "." . $arrTable["field_source_user_path"] . " = " . $db->toSql($ID_node, "Number")
                : ""
            ) 
            . ($arrTable["primary"] == "anagraph"
                ? ""
                : " ORDER BY LOCATE( layout.params, " . $arrTable["primary"] . "." . $arrTable["default_permalink_parent"] . ") DESC"
            );
 		$db->query($sSQL);
 		if($db->nextRecord()) {
 			$source_user_path = $db->getField("source_user_path", "Text", true);
 			if($source_user_path == "/")
 				$source_user_path = "";

 			$start_path = $db->getField("start_path", "Text", true);
 			if($start_path == "/")
 				$start_path = "";
 		} else {
 			$source_user_path = $default_parent;
 			$start_path = "";
 			
 			$skip_redirect = true;
 		}

		if($source_user_path) {
 			/**
 			* Resolve Source User Path By Lang
 			*/
			if(is_array($arrLang) && count($arrLang) > 1) {
				$tmp_parent = $source_user_path;
				do {
					foreach($arrLang AS $lang_code => $lang) {
						$arrSourceUserPathSQL[$lang["ID"]] = $arrSourceUserPathSQL[$lang["ID"]] 
							. (strlen($arrSourceUserPathSQL[$lang["ID"]]) ? " UNION " : "")
							. ($tmp_parent == '/'
								? "(SELECT '' AS smart_url)"
								: ($lang_code == LANGUAGE_DEFAULT
									? " (SELECT static_pages.name AS smart_url
  										FROM static_pages
  										WHERE static_pages.parent = " . $db->toSql(ffCommon_dirname($tmp_parent)) . "
  											AND static_pages.name = " . $db->toSql(basename($tmp_parent)) . "
  										LIMIT 1)"
									: " (SELECT static_pages_rel_languages.smart_url AS smart_url
  										FROM static_pages_rel_languages
  											INNER JOIN static_pages ON static_pages.ID = static_pages_rel_languages.ID_static_pages
  										WHERE static_pages.parent = " . $db->toSql(ffCommon_dirname($tmp_parent)) . "
  											AND static_pages.name = " . $db->toSql(basename($tmp_parent)) . "
  											AND static_pages_rel_languages.ID_languages = " . $db->toSql($lang["ID"], "Number") . "
  										LIMIT 1)"
  								)
  							);
  						
					}
					$tmp_parent = ffCommon_dirname($tmp_parent);
				} while($tmp_parent && $tmp_parent != "/");
			}  	

			if(is_array($arrSourceUserPathSQL) && count($arrSourceUserPathSQL)) {
				foreach($arrSourceUserPathSQL AS $ID_lang => $sSQL) {
					$db->query($sSQL);
					if($db->nextRecord()) {
						do {
							if(strlen($db->getField("smart_url", "Text", true))) {
								$arrSourceUserPath[$ID_lang] = "/" . $db->getField("smart_url", "Text", true) . $arrSourceUserPath[$ID_lang];
							} else {
								$arrSourceUserPath[$ID_lang] = $source_user_path;
								break;
							}
						} while($db->nextRecord());
					} else {
						$arrSourceUserPath[$ID_lang] = $source_user_path;
					}
				}
			}  	
		}
	}

	if($arrTable["name"] && is_array($arrLang) && count($arrLang) > 1) {
		$tmp_parent = $default_parent;
		do {
			foreach($arrLang AS $lang_code => $lang) {
				$arrSql[$lang["ID"]] = $arrSql[$lang["ID"]] 
					. (strlen($arrSql[$lang["ID"]]) ? " UNION " : "")
					. ($tmp_parent == '/'
						? "(SELECT '' AS smart_url)"
						: ($lang_code == LANGUAGE_DEFAULT
							? " (SELECT " . $arrTable["primary"] . "." . (is_array($arrTable["default_smart_url"]) ? $arrTable["default_smart_url"]["name"] : $arrTable["default_smart_url"]) . " AS smart_url
  								FROM " . $arrTable["primary"] . "
  								WHERE " . $arrTable["primary"] . ".parent = " . $db->toSql(ffCommon_dirname($tmp_parent)) . "
  									AND " . $arrTable["primary"] . ".name = " . $db->toSql(basename($tmp_parent)) . "
  									$sSQL_where
  									" . (is_array($arrTable["default_smart_url"]) 
  											? $arrTable["default_smart_url"]["where"] 
  											: ""
  									) . "
  								LIMIT 1)"
							: " (SELECT " . $arrTable["name"] . "." . (is_array($arrTable["field_smart_url"]) ? $arrTable["field_smart_url"]["name"] : $arrTable["field_smart_url"]) . " AS smart_url
  								FROM " . $arrTable["name"] . "
  									INNER JOIN " . $arrTable["primary"] . " ON " . $arrTable["primary"] . ".ID = " . $arrTable["name"] . "." . $arrTable["field_node"] . "
  								WHERE " . $arrTable["primary"] . ".parent = " . $db->toSql(ffCommon_dirname($tmp_parent)) . "
  									AND " . $arrTable["primary"] . ".name = " . $db->toSql(basename($tmp_parent)) . "
  									AND " . $arrTable["name"] . "." . $arrTable["field_lang"] . " = " . $db->toSql($lang["ID"], "Number") . "
  									$sSQL_where
  									" . (is_array($arrTable["field_smart_url"]) 
  											? $arrTable["field_smart_url"]["where"] 
  											: ""
  									) . "
  								LIMIT 1)"
						)
  					);
			}				
			$tmp_parent = ffCommon_dirname($tmp_parent);
		} while($tmp_parent && $tmp_parent != "/" && $tmp_parent != $start_path);
	}

  	if($start_path && strpos($parent, $start_path) === 0)
  		$parent = substr($parent, strlen($start_path));

	if(!$parent) 
		$parent = "/";	

	if(is_array($arrLang) && count($arrLang) == 1) {
		$arrPermalinkParent[LANGUAGE_INSET_ID] = stripslash($source_user_path . $parent);
 	} elseif(is_array($arrSql) && count($arrSql)) {
		foreach($arrSql AS $ID_lang => $sSQL) {
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					/*if(strlen($db->getField("smart_url", "Text", true))) {
						$arrPermalinkParent[$ID_lang] = "/" . $db->getField("smart_url", "Text", true) . $arrPermalinkParent[$ID_lang];
					} else {
						$arrPermalinkParent[$ID_lang] = $parent;
						break;
					}*/				
				
					if(strlen($db->getField("smart_url", "Text", true))) {
						$arrPermalinkParent[$ID_lang] = ($parent == "/" && $start_path && strpos("/" . $db->getField("smart_url", "Text", true), $start_path) === 0
							? ""
							: "/" . $db->getField("smart_url", "Text", true)
						) . $arrPermalinkParent[$ID_lang];
					} else {
						$arrPermalinkParent[$ID_lang] = $parent;
						break;
					}
				} while($db->nextRecord());
			} else {
				$arrPermalinkParent[$ID_lang] = $parent;
			}

			if($arrSourceUserPath[$ID_lang])
				$arrPermalinkParent[$ID_lang] = stripslash($arrSourceUserPath[$ID_lang] . $arrPermalinkParent[$ID_lang]);
		}
	} else {
		$arrPermalinkParent[LANGUAGE_DEFAULT_ID] = stripslash($source_user_path . $parent);
	}

	if(is_array($arrPermalinkParent) && count($arrPermalinkParent)) {
		foreach($arrPermalinkParent AS $ID_lang => $permalink_parent) {
			$permalink = "";
            if($ID_lang != LANGUAGE_DEFAULT_ID && $arrTable["name"]) {
/*
						    , " . $arrTable["name"] . "." . (is_array($arrTable["field_permalink_parent"]) 
							    ? $arrTable["field_permalink_parent"]["name"] 
							    : $arrTable["field_permalink_parent"]
						    ) . " AS permalink_parent 
*/
			    $sSQL = "SELECT  " . $arrTable["name"] . ".ID AS ID
			    			, " . $arrTable["name"] . "." . (is_array($arrTable["field_permalink"]) 
							    ? $arrTable["field_permalink"]["name"] 
							    : $arrTable["field_permalink"]
						    ) . " AS permalink
						    , " . $arrTable["name"] . "." . (is_array($arrTable["field_smart_url"]) 
							    ? $arrTable["field_smart_url"]["name"] 
							    : $arrTable["field_smart_url"]
						    ) . " AS smart_url 
						     , " . $arrTable["primary"] . "." . (is_array($arrTable["default_smart_url"]) 
							    ? $arrTable["default_smart_url"]["name"] 
							    : $arrTable["default_smart_url"]
						    ) . " AS default_smart_url 
						     , " . $arrTable["primary"] . "." . (is_array($arrTable["default_permalink_parent"]) 
							    ? $arrTable["default_permalink_parent"]["name"] 
							    : $arrTable["default_permalink_parent"]
						    ) . " AS default_permalink_parent 
					    FROM " . $arrTable["name"] 
						    . ($arrTable["name"] != $arrTable["primary"]
							    ? " INNER JOIN " . $arrTable["primary"] . " ON " . $arrTable["primary"] . ".ID = " . $arrTable["name"] . "." . $arrTable["field_node"]
							    : ""
						    ) . "
					    WHERE " . $arrTable["name"] . "." . $arrTable["field_node"] . " = " . $db->toSql($ID_node, "Number") . "
						    " . ($arrTable["field_lang"]
							    ? " AND " . $arrTable["name"] . "." . $arrTable["field_lang"] . " = " . $db->toSql($ID_lang, "Number")
							    : ""						
						    ) . "
						    $sSQL_where
  						    " . (is_array($arrTable["field_permalink_parent"]) 
  								    ? $arrTable["field_permalink_parent"]["where"] 
  								    : ""
  							    );
			    $db->query($sSQL);
			    if($db->nextRecord()) {
					$ID = $db->getField("ID", "Number", true);   
					$oldPermalink[$arrLangRev[$ID_lang]] = $db->getField("permalink", "Text", true);

                    if($is_home) {
                        $smart_url = "";   
                        $permalink = "/";
                        $permalink_parent = "/";
                    } else {
					    $smart_url = $db->getField("smart_url", "Text", true);
					    if($smart_url) {   
						    /*$permalink = stripslash($permalink_parent) . ($db->getField("default_permalink_parent", "Text", true) . $db->getField("default_smart_url", "Text", true) == $start_path
					    		? ""
					    		: "/" . $smart_url
						    );*/
						    
						    $permalink = str_replace(
						    	array("[PARENT]", "/[SMART_URL]")
						    	, array(
						    		stripslash($permalink_parent)
						    		, ($db->getField("default_permalink_parent", "Text", true) . $db->getField("default_smart_url", "Text", true) == $start_path
					    				? ""
					    				: "/" . $smart_url
						    		)
						    	), $permalink_rule
						    );
						}
                    }

				    if(!($update_only_empty && strlen($db->getField("permalink_parent", "Text", true)))) {
					    $sSQL = "UPDATE " . $arrTable["name"] . " SET 
								    " . (is_array($arrTable["field_permalink_parent"]) 
									    ? $arrTable["field_permalink_parent"]["name"] 
									    : $arrTable["field_permalink_parent"]
								    ) . " = " . $db->toSql($permalink_parent)
								    . ($arrTable["field_permalink"]
									    ? ", " . (is_array($arrTable["field_permalink"]) 
										    ? $arrTable["field_permalink"]["name"] 
										    : $arrTable["field_permalink"]
									    ) . " = " . $db->toSql($permalink)
									    : ""
								    )
                                    . ($is_home && $arrTable["field_smart_url"]
                                        ? ", " . (is_array($arrTable["field_smart_url"]) 
                                            ? $arrTable["field_smart_url"]["name"] 
                                            : $arrTable["field_smart_url"]
                                        ) . " = " . $db->toSql($smart_url)
                                        : ""
                                    ) . "
								    $sSQL_update
							    WHERE " . $arrTable["name"] . ".ID = " . $db->toSql($ID, "Number");
					    $db->execute($sSQL);
				    }
			    } else {
                    if($is_home) {  
                        $permalink = "/";
                        $permalink_parent = "/";
                    } else {
				        //$permalink = stripslash($permalink_parent) . "/" . $default_smart_url;
				        $permalink = str_replace(
							array("[PARENT]", "/[SMART_URL]")
							, array(
								stripslash($permalink_parent)
								, "/" . $default_smart_url
							)
							, $permalink_rule
						);
                    }
                    if($ID_lang != LANGUAGE_DEFAULT_ID) {
					    $sSQL = "INSERT INTO " . $arrTable["name"] . "
							    (
								    ID
								    , " . $arrTable["field_node"] . "
								    " . ($arrTable["field_lang"]
									    ? ", " . $arrTable["field_lang"]
									    : ""						
								    ) . "
								    , " . (is_array($arrTable["field_permalink_parent"]) 
									    ? $arrTable["field_permalink_parent"]["name"] 
									    : $arrTable["field_permalink_parent"]
								    ) . "
								    " . ($arrTable["field_permalink"]
									    ? ", " . (is_array($arrTable["field_permalink"]) 
											    ? $arrTable["field_permalink"]["name"] 
											    : $arrTable["field_permalink"]
										    )
									    : ""						
								    ) 
	                                . ($is_home && $arrTable["field_smart_url"]
	                                    ? ", " . (is_array($arrTable["field_smart_url"]) 
	                                            ? $arrTable["field_smart_url"]["name"] 
	                                            : $arrTable["field_smart_url"]
	                                        )
	                                    : ""                        
	                                ) . "
								    $sSQL_inset_head
								    " . (is_array($arrTable["field_permalink_parent"]) && is_array($arrTable["field_permalink_parent"]["insert"])
  									    ? $arrTable["field_permalink_parent"]["insert"]["head"] 
  									    : ""
  								    ) . "
							    )
							    VALUES 
							    (
								    null
								    , " . $db->toSql($ID_node, "Number") . "
								    " . ($arrTable["field_lang"]
									    ? ", " . $db->toSql($ID_lang, "Number")
									    : ""						
								    ) . "
								    , " . $db->toSql($permalink_parent) . "
								    " . ($arrTable["field_permalink"]
									    ? ", " . $db->toSql($permalink)
									    : ""						
								    ) 
	                                . ($is_home && $arrTable["field_smart_url"]
	                                    ? ", " . $db->toSql($default_smart_url)
	                                    : ""                        
	                                ) . "
								    $sSQL_inset_body
								    " . (is_array($arrTable["field_permalink_parent"]) && is_array($arrTable["field_permalink_parent"]["insert"]) 
  									    ? $arrTable["field_permalink_parent"]["insert"]["body"] 
  									    : ""
  								    ) . "
							    )";

					    $db->execute($sSQL);
					}
			    }
            } else {
                if($is_home) {  
                    $permalink = "/";
                    $permalink_parent = "/";
                } else {
 					/*$permalink = stripslash($permalink_parent) . ($default_parent . $default_smart_url == $start_path
					    	? ""
					    	: "/" . $default_smart_url
					    ); */ 

				    $permalink = str_replace(
						array("[PARENT]", "/[SMART_URL]")
						, array(
							stripslash($permalink_parent)
							, ($default_parent . $default_smart_url == $start_path
					    		? ""
					    		: "/" . $default_smart_url
							)
						), $permalink_rule
					);                
                }            
            }

			if($arrTable["default_permalink"] && $ID_lang == LANGUAGE_DEFAULT_ID) {
				$sSQL = "UPDATE `" . $arrTable["primary"] . "` SET "
                            . ($arrTable["default_permalink_parent"]
                                ? "`" .(is_array($arrTable["default_permalink_parent"]) 
                                        ? $arrTable["default_permalink_parent"]["name"] 
                                        : $arrTable["default_permalink_parent"]
                                    ) . "` = " . $db->toSql($default_parent)
                                    . ", `" .(is_array($arrTable["default_permalink"]) 
                                        ? $arrTable["default_permalink"]["name"] 
                                        : $arrTable["default_permalink"]
                                    ) . "` = " . $db->toSql($permalink)    /*
                                    CONCAT(" . $db->toSql($permalink_parent == "/" ? "" : $permalink_parent) 
                                                . ", '/', " 
                                                . "`" . (is_array($arrTable["default_smart_url"]) 
                                                    ? $arrTable["default_smart_url"]["name"] 
                                                    : $arrTable["default_smart_url"]
                                                ) . "`" 
                                            . ")"
                                    
                                    */
                                : "`" .(is_array($arrTable["default_permalink"]) 
                                        ? $arrTable["default_permalink"]["name"] 
                                        : $arrTable["default_permalink"]
                                    ) . "` = " . $db->toSql($permalink)
                            )
                            . ($is_home && $arrTable["default_smart_url"]
                                ? ", " . (is_array($arrTable["default_smart_url"]) 
                                    ? $arrTable["default_smart_url"]["name"] 
                                    : $arrTable["default_smart_url"]
                                ) . " = " . $db->toSql($default_smart_url)
                                : ""
                            ) . "
						WHERE `" . $arrTable["primary"] . "`.ID = " . $db->toSql($ID_node, "Number");
				$db->execute($sSQL);
			}
			
			$arrPermalink[$arrLangRev[$ID_lang]] = $permalink;
		}
	}
	
	if(!$skip_redirect && is_array($oldPermalink) && count($oldPermalink) && check_function("system_gallery_redirect")) {
		foreach($oldPermalink AS $lang_code => $old_permalink) {
			if($old_permalink && $arrPermalink[$lang_code] && $arrPermalink[$lang_code] != $old_permalink)
				system_redirect_set_rule(DOMAIN_NAME, $old_permalink, DOMAIN_NAME . $arrPermalink[$lang_code]);
		}
	}
	
	return $arrPermalink;
}