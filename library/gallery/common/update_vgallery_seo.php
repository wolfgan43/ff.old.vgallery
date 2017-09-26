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
function update_vgallery_seo($smart_url, $ID_item, $ID_lang, $meta_description = null, $limit_parent = null, $meta_keywords = null, $visible = null, $stop_words = null, $params = null, $default_params = null, $limit = null, $alt_url = null, $permalink_parent = null) {
    $db = ffDB_Sql::factory();

    if($params === null) {
    	if(OLD_VGALLERY) {
    		$params = array(
        		"table" => "vgallery_rel_nodes_fields"
        		, "primary_table" => "vgallery_nodes"
        		, "primary_parent" => "parent"
        		, "rel_key" => "ID_nodes"
        		, "rel_lang" => "ID_lang"
        		, "rel_field" => "ID_fields"
        		, "permalink" => false
        		, "smart_url" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'smart_url' AND vgallery_type.name = 'System')"
        		)
        		, "title" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_title' AND vgallery_type.name = 'System')"
        		)
        		, "header" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_title_alt' AND vgallery_type.name = 'System')"
        		)
        		, "description" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_description' AND vgallery_type.name = 'System')"
        		)
                
                , "robots"                  => false
                , "canonical"               => false
                , "meta"                    => false
                , "httpstatus"              => false   

        		, "keywords" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'keywords' AND vgallery_type.name = 'System')"
        		)
        		, "permalink_parent" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'permalink_parent' AND vgallery_type.name = 'System')"
        		)
				, "visible" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'visible' AND vgallery_type.name = 'System')"
        		)
        		, "alt_url" => array(
        			"field" => "description"
        			, "where" => "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'alt_url' AND vgallery_type.name = 'System')"
        		)         		
	        );
		} else {
    		$params = array(
        		"table" => "vgallery_nodes_rel_languages"
        		, "primary_table" => "vgallery_nodes"
        		, "primary_parent" => "parent"
        		, "rel_key" => "ID_nodes"
        		, "rel_lang" => "ID_lang"
        		, "rel_field" => false
        		, "permalink" => "permalink"
        		, "smart_url" => "smart_url"
        		, "title" => "meta_title"
        		, "header" => "meta_title_alt"
        		, "description" => "meta_description"

                , "robots"                  => "meta_robots"
                , "canonical"               => "meta_canonical"
                , "meta"                    => "meta"
                , "httpstatus"              => "httpstatus"
                
        		, "keywords" => "keywords"
        		, "permalink_parent" => "permalink_parent"
        		, "visible" => "visible"
        		, "alt_url" => "alt_url"
	        );
		}
    }
    
    if($ID_item > 0) {
    	if($default_params)
    		$params["skip_primary_lang"] = true;

    	if(is_array($smart_url)) 
    	{
    		if(!$smart_url["smart_url"]) {
    			$smart_url["smart_url"] = ffCommon_url_rewrite($smart_url["title"]);
			}
    	} 
    		
		if($params["rel_field"]) 
			$res = update_vgallery_seo_multi($smart_url, $ID_item, $ID_lang, $meta_description, $limit_parent, $meta_keywords, $visible, $stop_words, $params, $limit, $alt_url, $permalink_parent);
		else
			$res = update_vgallery_seo_mono($smart_url, $ID_item, $ID_lang, $meta_description, $limit_parent, $meta_keywords, $visible, $stop_words, $params, $limit, $alt_url, $permalink_parent);
	}
    
    
    if((!$limit || $limit == "primary") && $ID_lang == LANGUAGE_DEFAULT_ID && is_array($default_params) && is_array($res) && count($res)) {
    	$arrSqlField = array();

    	if($default_params["name"] && !is_array($smart_url) && $res["title"] && !$res["smart_url"]) 
    		$res["smart_url"] = ffCommon_url_rewrite($res["title"]);

    	foreach($res AS $res_key => $res_value) {
    		if(array_key_exists($res_key, $default_params) && $default_params[$res_key])
    			$arrSqlField[$res_key] = "`" . $default_params[$res_key] . "` = " . $db->toSql($res_value);
    	}

    	if(count($arrSqlField)) {
			$sSQL = "UPDATE `" . $params["primary_table"] . "` SET "
						. implode(",", $arrSqlField) ."
					WHERE `" . $params["primary_table"] . "`.ID = " . $db->toSql($ID_item, "Number");
			$db->execute($sSQL);
    	}
    }
    
    return $res;
}


function update_vgallery_seo_multi($primary_meta, $ID_item, $ID_lang, $meta_description = null, $limit_parent = null, $meta_keywords = null, $visible = null, $stop_words = null, $params = null, $limit = null, $alt_url = null, $permalink_parent = null) {
	$db = ffDB_Sql::factory();

	$arrPrimaryMeta = null;
	if(is_array($primary_meta))
		$arrPrimaryMeta = $primary_meta;
	elseif(strlen($primary_meta)) {
		$arrPrimaryMeta["smart_url"] = ($stop_words
			? ffCommon_url_rewrite_strip_word(strip_tags($primary_meta), explode(",", $stop_words))
			: ffCommon_url_rewrite(strip_tags($primary_meta))
		);
		$arrPrimaryMeta["title"] = strip_tags($primary_meta);
		$arrPrimaryMeta["header"] = $primary_meta;
	}
	
	if(is_array($arrPrimaryMeta)) {
	   /* if($stop_words) {
    		$real_smart_url = ffCommon_url_rewrite_strip_word(strip_tags($smart_url), explode(",", $stop_words));
	    } else {
    		$real_smart_url = ffCommon_url_rewrite(strip_tags($smart_url));
	    }*/
	    
	    if($params["smart_url"] && $arrPrimaryMeta["smart_url"]) {
			//Inserisce/Aggiorna lo Smart_url
			$sSQL = "SELECT `" . $params["table"] . "`.ID 
			        FROM `" . $params["table"] . "` 
                		" . ($params["skip_primary_lang"] && $params["table"] != $params["primary_table"] 
                			? " INNER JOIN `" . $params["primary_table"] . "` ON `" . $params["primary_table"] . "`.ID = `" . $params["table"] . "`.`" . $params["rel_key"] . "`"
                			: ""
                		) . "
			        WHERE 
			            " . (is_array($params["smart_url"])
                    		? "`" . $params["smart_url"]["field"] . "` = " . $db->toSql($arrPrimaryMeta["smart_url"])
		                		. ($params["rel_field"]
		                			? " AND `" . $params["rel_field"] . "` = " . $params["smart_url"]["where"]
		                			: ""
		                		)
							: $params["smart_url"] . " = " . $db->toSql($arrPrimaryMeta["smart_url"])
			            ) . "
			            AND `" . $params["rel_key"] . "` <> " . $db->toSql($ID_item, "Number") . "
			            " . ($params["rel_lang"]
                    		? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    		: ""
			            )
			            . ($limit_parent === null
                    		? ""
                    		: " AND (`" . $params["primary_table"] . "`.parent = " . $db->toSql($limit_parent, "Text") . "
                    			OR `" . $params["primary_table"] . "`.parent LIKE '" . $db->toSql($limit_parent, "Text", false) . "/%')");
			$db->query($sSQL);
			if($db->nextRecord() && strpos($arrPrimaryMeta["smart_url"], "-" . $ID_item) === false) {
        		$arrPrimaryMeta["smart_url"] = $arrPrimaryMeta["smart_url"] . "-" . $ID_item;
			}
			
			if(!$limit || $limit == "lang") {
				$sSQL = "SELECT `" . $params["table"] . "`.ID 
				        FROM `" . $params["table"] . "` 
                			" . ($params["skip_primary_lang"] && $params["table"] != $params["primary_table"] 
                				? " INNER JOIN `" . $params["primary_table"] . "` ON `" . $params["primary_table"] . "`.ID = `" . $params["table"] . "`.`" . $params["rel_key"] . "`"
                				: ""
                			) . "
				        WHERE 1
				           " . (is_array($params["smart_url"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["smart_url"]["where"]
								: ""
					        ) . "
				            AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
				            " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
				            );
				$db->query($sSQL);
				if($db->nextRecord()) {
				    $ID_node = $db->getField("ID", "Number", true);

					$sSQL = "UPDATE 
					    `" . $params["table"] . "` 
					SET 
						" . (is_array($params["smart_url"])
		                    ? "`" . $params["smart_url"]["field"] . "` = " . $db->toSql($arrPrimaryMeta["smart_url"]) 
							: $params["smart_url"] . " = " . $db->toSql($arrPrimaryMeta["smart_url"])
				        ) . "		        
					WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO  
					            `" . $params["table"] . "` 
					        ( 
					            `ID` 
			                    " . (is_array($params["smart_url"]) 
			                        ? ", `" . $params["smart_url"]["field"] . "`" 
			                            . ($params["rel_field"]
			                                ? ", `" . $params["rel_field"] . "`"
			                                : ""
			                            )
			                        : ", `" . $params["smart_url"] . "`"
			                    ) . "		                        
					            , `" . $params["rel_key"] . "`
			                    " . ($params["rel_lang"]
                    				? ", `" . $params["rel_lang"] . "`"
                    				: ""
						        ) . "
					        )
					        VALUES
					        (
					            null
					            , " . $db->toSql($arrPrimaryMeta["smart_url"]) . " 
					            " . (is_array($params["smart_url"]) && $params["rel_field"]
					                ? ", " . $params["smart_url"]["where"]
					                : ""
					            ) . "
					            , " . $db->toSql($ID_item, "Number") . " 
								" . ($params["rel_lang"]
                    				? ", " . $db->toSql($ID_lang, "Number") 
                    				: ""
						        ) . "
					            
					        )";
					$db->execute($sSQL);
				}
			}
			$res["smart_url"] = $arrPrimaryMeta["smart_url"];
		}

		if($params["title"] && isset($arrPrimaryMeta["title"])) {
    		//Inserisce / aggiorna il Meta_title
			if(!$limit || $limit == "lang") {    		
				$sSQL = "SELECT `" . $params["table"] . "`.*  
        				FROM `" . $params["table"] . "` 
        				WHERE 1 " . (is_array($params["title"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["title"]["where"]
								: ""
					        ) . "
						    AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
					        " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
					        );
				$db->query($sSQL);
				if($db->nextRecord()) {
				    $ID_node = $db->getField("ID", "Number", true);

					$sSQL = "UPDATE 
					    `" . $params["table"] . "`
					SET 
						" . (is_array($params["title"])
				            ? "`" . $params["title"]["field"] . "` = " . $db->toSql($arrPrimaryMeta["title"]) 
							: $params["title"] . " = " . $db->toSql($arrPrimaryMeta["title"])
					    ) . "		        
					WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO  
					            `" . $params["table"] . "` 
					        ( 
						        `ID` 
				                " . (is_array($params["title"]) 
				                    ? ", `" . $params["title"]["field"] . "`" 
				                        . ($params["rel_field"]
				                            ? ", `" . $params["rel_field"] . "`"
				                            : ""
				                        )
				                    : ", `" . $params["title"] . "`"
				                ) . "		                        
						        , `" . $params["rel_key"] . "`
				                " . ($params["rel_lang"]
                    				? ", `" . $params["rel_lang"] . "`"
                    				: ""
							    ) . "
						    )
						    VALUES
						    (
						        ''
						        , " . $db->toSql($arrPrimaryMeta["title"]) . " 
						        " . (is_array($params["title"]) && $params["rel_field"]
						            ? ", " . $params["title"]["where"]
						            : ""
						        ) . "
						        , " . $db->toSql($ID_item, "Number") . " 
								" . ($params["rel_lang"]
                    				? ", " . $db->toSql($ID_lang, "Number") 
                    				: ""
							    ) . "
						        
						    )";
					$db->execute($sSQL);
				}
			}
			$res["title"] = $arrPrimaryMeta["title"];
		}
		if($params["header"] && isset($arrPrimaryMeta["header"])) {
    		//Inserisce / aggiorna l'eventuale h1
			if(!$limit || $limit == "lang") {    		
				$sSQL = "SELECT `" . $params["table"] . "`.*  
        				FROM `" . $params["table"] . "` 
        				WHERE 1 " . (is_array($params["header"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["header"]["where"]
								: ""
					        ) . "
						    AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
					        " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
					        );
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_node = $db->getField("ID", "Number", true);

					$sSQL = "UPDATE 
					    `" . $params["table"] . "`
					SET 
						" . (is_array($params["header"])
				            ? "`" . $params["header"]["field"] . "` = " . $db->toSql($arrPrimaryMeta["header"]) 
							: $params["header"] . " = " . $db->toSql($arrPrimaryMeta["header"])
					    ) . "		        
					WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO  
					            `" . $params["table"] . "` 
					        ( 
						        `ID` 
				                " . (is_array($params["header"]) 
				                    ? ", `" . $params["header"]["field"] . "`" 
				                        . ($params["rel_field"]
				                            ? ", `" . $params["rel_field"] . "`"
				                            : ""
				                        )
				                    : ", `" . $params["header"] . "`"
				                ) . "		                        
						        , `" . $params["rel_key"] . "`
				                " . ($params["rel_lang"]
                    				? ", `" . $params["rel_lang"] . "`"
                    				: ""
							    ) . "
						    )
						    VALUES
						    (
						        ''
						        , " . $db->toSql($arrPrimaryMeta["header"]) . " 
						        " . (is_array($params["header"]) && $params["rel_field"]
						            ? ", " . $params["header"]["where"]
						            : ""
						        ) . "
						        , " . $db->toSql($ID_item, "Number") . " 
								" . ($params["rel_lang"]
                    				? ", " . $db->toSql($ID_lang, "Number") 
                    				: ""
							    ) . "
						        
						    )";
					$db->execute($sSQL);
				}
			}
			$res["header"] = $arrPrimaryMeta["header"];
		}		
	}

	if($visible !== null && $params["visible"]) {
	    //Inserisce / aggiorna l'eventuale visible
		if(!$limit || $limit == "lang") {	    
			$sSQL = "SELECT `" . $params["table"] . "`.*  
        			FROM `" . $params["table"] . "` 
        			WHERE 1 " . (is_array($params["visible"]) && $params["rel_field"]
		                    ? " AND `" . $params["rel_field"] . "` = " . $params["visible"]["where"]
							: ""
						) . "
						AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
						" . ($params["rel_lang"]
		                    ? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
		                    : ""
						);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_node = $db->getField("ID", "Number", true);

				$sSQL = "UPDATE 
					`" . $params["table"] . "`
				SET 
					" . (is_array($params["visible"])
					    ? "`" . $params["visible"]["field"] . "` = " . $db->toSql($visible) 
						: $params["visible"] . " = " . $db->toSql($visible)
					) . "		        
				WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
				$db->execute($sSQL);
			} else {
				$sSQL = "INSERT INTO  
						    `" . $params["table"] . "` 
						( 
							`ID` 
					        " . (is_array($params["visible"]) 
					            ? ", `" . $params["visible"]["field"] . "`" 
					                . ($params["rel_field"]
					                    ? ", `" . $params["rel_field"] . "`"
					                    : ""
					                )
					            : ", `" . $params["visible"] . "`"
					        ) . "		                        
							, `" . $params["rel_key"] . "`
					        " . ($params["rel_lang"]
                    			? ", `" . $params["rel_lang"] . "`"
                    			: ""
							) . "
						)
						VALUES
						(
							''
							, " . $db->toSql($visible) . " 
							" . (is_array($params["visible"]) && $params["rel_field"]
							    ? ", " . $params["visible"]["where"]
							    : ""
							) . "
							, " . $db->toSql($ID_item, "Number") . " 
							" . ($params["rel_lang"]
                    			? ", " . $db->toSql($ID_lang, "Number") 
                    			: ""
							) . "
							
						)";
				$db->execute($sSQL);
			}
		}
		$res["visible"] = $visible;
	}
	
	if($meta_description !== null) {
	    //Check Meta_description se aggiornabile
		$allow_edit_meta_description = false;
		$update_meta_description = false;
		if(is_array($meta_description)) {
		    if(array_key_exists("ori", $meta_description) && array_key_exists("new", $meta_description)) {
		        $sSQL = "SELECT `" . $params["table"] . "`.* 
		                FROM `" . $params["table"] . "`  
		                WHERE 1 " . (is_array($params["description"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["description"]["where"]
								: ""
					        ) . "
				            AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
				            " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
				            );
		        $db->query($sSQL);
		        if($db->nextRecord()) {
		            if(!strlen($db->getField(is_array($params["description"]) ? $params["description"]["field"] : $params["description"], "Text", true)) || $db->getField(is_array($params["description"]) ? $params["description"]["field"] : $params["description"], "Text", true) == $meta_description["ori"]) {
	                    $update_meta_description = $db->getField("ID", "Number", true);
		                $allow_edit_meta_description = true;
		            }
		        } else {
		            $allow_edit_meta_description = true;
		        }
		        $real_meta_description = $meta_description["new"];
		    }
		} else {
		    $allow_edit_meta_description = true;
		    
		    $real_meta_description = $meta_description;
		}

		//Inserisce /aggiorna il Meta_description
		if($allow_edit_meta_description) {
		    if(check_function("get_short_description"))
		        $arrShortDesc = get_short_description($real_meta_description);

		    $real_meta_description = $arrShortDesc["text"];
			            
			if(!$limit || $limit == "lang") {
			    if($update_meta_description) {
				    $sSQL = "UPDATE 
		            			`" . $params["table"] . "` 
							SET 
								" . (is_array($params["description"])
                    					? "`" . $params["description"]["field"] . "` = " . $db->toSql($real_meta_description) 
										: $params["description"] . " = " . $db->toSql($real_meta_description)
								    ) . "
							WHERE `" . $params["table"] . "`.ID = " . $db->toSql($update_meta_description, "Number", true);
					$db->execute($sSQL);
				} else {
				    $sSQL = "INSERT INTO  
				                `" . $params["table"] . "`  
				            ( 
						            `ID` 
				                    " . (is_array($params["description"]) 
				                        ? ", `" . $params["description"]["field"] . "`" 
				                            . ($params["rel_field"]
				                                ? ", `" . $params["rel_field"] . "`"
				                                : ""
				                            )
				                        : ", `" . $params["description"] . "`"
				                    ) . "		                        
						            , `" . $params["rel_key"] . "`
				                    " . ($params["rel_lang"]
                    					? ", `" . $params["rel_lang"] . "`"
                    					: ""
							        ) . "
						        )
						        VALUES
						        (
						            ''
						            , " . $db->toSql($real_meta_description) . " 
						            " . (is_array($params["description"]) && $params["rel_field"]
						                ? ", " . $params["description"]["where"]
						                : ""
						            ) . "
						            , " . $db->toSql($ID_item, "Number") . " 
									" . ($params["rel_lang"]
                    					? ", " . $db->toSql($ID_lang, "Number") 
                    					: ""
							        ) . "
						            
						        )";
				    $db->execute($sSQL);
				}
			}
			$res["description"] = $real_meta_description;
		}
	}

	if($meta_keywords !== null) {
		//Check Meta_keywords se aggiornabile
		$allow_edit_meta_keywords = false;
		$update_meta_keywords = false;
		if(is_array($meta_keywords)) {
		    if(array_key_exists("ori", $meta_keywords) && array_key_exists("new", $meta_keywords)) {
				$sSQL = "SELECT `" . $params["table"] . "`.*
			            FROM `" . $params["table"] . "` 
			            WHERE 1 " . (is_array($params["keywords"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["keywords"]["where"]
								: ""
					        ) . "
						    AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
					        " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
					        );
			    $db->query($sSQL);
			    if($db->nextRecord()) {
			        if(!strlen($db->getField(is_array($params["keywords"]) ? $params["keywords"]["field"] : $params["keywords"], "Text", true)) || $db->getField(is_array($params["keywords"]) ? $params["keywords"]["field"] : $params["keywords"], "Text", true) == $meta_keywords["ori"]) {
			            $update_meta_keywords = $db->getField("ID", "Number", true);
			            $allow_edit_meta_keywords = true;
			        }
			    } else {
			        $allow_edit_meta_keywords = true;
			    }	
			    $real_meta_keywords = $meta_keywords["new"];
			}
		} else {
		    $allow_edit_meta_keywords = true;
		    
		    $real_meta_keywords = $meta_keywords;
		}
	    //Inserisce /aggiorna il Meta_keywords
	    if($allow_edit_meta_keywords) {	
	    	if(!$limit || $limit == "lang") {	
				if($update_meta_keywords) {
					$sSQL = "UPDATE 
						        `" . $params["table"] . "` 
							SET 
							    " . (is_array($params["keywords"])
                    					? "`" . $params["keywords"]["field"] . "` = " . $db->toSql($real_meta_keywords) 
										: $params["keywords"] . " = " . $db->toSql($real_meta_keywords)
							        ) . "
							WHERE `" . $params["table"] . "`.ID = " . $db->toSql($update_meta_keywords, "Number", true);
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO  
					            `" . $params["table"] . "`  
			                ( 
					            `ID` 
			                    " . (is_array($params["keywords"]) 
			                        ? ", `" . $params["keywords"]["field"] . "`" 
			                            . ($params["rel_field"]
			                                ? ", `" . $params["rel_field"] . "`"
			                                : ""
			                            )
			                        : ", `" . $params["keywords"] . "`"
			                    ) . "		                        
					            , `" . $params["rel_key"] . "`
			                    " . ($params["rel_lang"]
                    				? ", `" . $params["rel_lang"] . "`"
                    				: ""
						        ) . "
					        )
					        VALUES
					        (
					            ''
					            , " . $db->toSql($real_meta_keywords) . " 
					            " . (is_array($params["keywords"]) && $params["rel_field"]
					                ? ", " . $params["keywords"]["where"]
					                : ""
					            ) . "
					            , " . $db->toSql($ID_item, "Number") . " 
								" . ($params["rel_lang"]
                    				? ", " . $db->toSql($ID_lang, "Number") 
                    				: ""
						        ) . "
					            
					        )";
					$db->execute($sSQL);
				}
			}
			$res["keywords"] = $real_meta_keywords;
		}
	}
	
	if($alt_url !== null && $params["alt_url"]) {
	    //Inserisce / aggiorna l'eventuale alt_url
		if(!$limit || $limit == "lang") {	    
			$sSQL = "SELECT `" . $params["table"] . "`.*  
        			FROM `" . $params["table"] . "` 
        			WHERE 1 " . (is_array($params["alt_url"]) && $params["rel_field"]
		                    ? " AND `" . $params["rel_field"] . "` = " . $params["alt_url"]["where"]
							: ""
						) . "
						AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
						" . ($params["rel_lang"]
		                    ? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
		                    : ""
						);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_node = $db->getField("ID", "Number", true);

				$sSQL = "UPDATE 
					`" . $params["table"] . "`
				SET 
					" . (is_array($params["alt_url"])
					    ? "`" . $params["alt_url"]["field"] . "` = " . $db->toSql($alt_url) 
						: $params["alt_url"] . " = " . $db->toSql($alt_url)
					) . "		        
				WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
				$db->execute($sSQL);
			} else {
				$sSQL = "INSERT INTO  
						    `" . $params["table"] . "` 
						( 
							`ID` 
					        " . (is_array($params["alt_url"]) 
					            ? ", `" . $params["alt_url"]["field"] . "`" 
					                . ($params["rel_field"]
					                    ? ", `" . $params["rel_field"] . "`"
					                    : ""
					                )
					            : ", `" . $params["alt_url"] . "`"
					        ) . "		                        
							, `" . $params["rel_key"] . "`
					        " . ($params["rel_lang"]
                    			? ", `" . $params["rel_lang"] . "`"
                    			: ""
							) . "
						)
						VALUES
						(
							''
							, " . $db->toSql($alt_url) . " 
							" . (is_array($params["alt_url"]) && $params["rel_field"]
							    ? ", " . $params["alt_url"]["where"]
							    : ""
							) . "
							, " . $db->toSql($ID_item, "Number") . " 
							" . ($params["rel_lang"]
                    			? ", " . $db->toSql($ID_lang, "Number") 
                    			: ""
							) . "
							
						)";
				$db->execute($sSQL);
			}
		}
		$res["alt_url"] = $alt_url;
	}
	
	
	if($permalink_parent !== null && $params["permalink_parent"]) {
		//Inserisce / aggiorna l'eventuale permalink_parent
		if(!$limit || $limit == "lang") {	    
			$sSQL = "SELECT `" . $params["table"] . "`.*  
        			FROM `" . $params["table"] . "` 
        			WHERE 1 " . (is_array($params["permalink_parent"]) && $params["rel_field"]
		                    ? " AND `" . $params["rel_field"] . "` = " . $params["permalink_parent"]["where"]
							: ""
						) . "
						AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
						" . ($params["rel_lang"]
		                    ? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
		                    : ""
						);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_node = $db->getField("ID", "Number", true);

				$sSQL = "UPDATE 
					`" . $params["table"] . "`
				SET 
					" . (is_array($params["permalink_parent"])
					    ? "`" . $params["permalink_parent"]["field"] . "` = " . $db->toSql($permalink_parent) 
						: $params["permalink_parent"] . " = " . $db->toSql($permalink_parent)
					) . "		        
				WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
				$db->execute($sSQL);
			} else {
				$sSQL = "INSERT INTO  
						    `" . $params["table"] . "` 
						( 
							`ID` 
					        " . (is_array($params["permalink_parent"]) 
					            ? ", `" . $params["permalink_parent"]["field"] . "`" 
					                . ($params["rel_field"]
					                    ? ", `" . $params["rel_field"] . "`"
					                    : ""
					                )
					            : ", `" . $params["permalink_parent"] . "`"
					        ) . "		                        
							, `" . $params["rel_key"] . "`
					        " . ($params["rel_lang"]
                    			? ", `" . $params["rel_lang"] . "`"
                    			: ""
							) . "
						)
						VALUES
						(
							''
							, " . $db->toSql($permalink_parent) . " 
							" . (is_array($params["permalink_parent"]) && $params["rel_field"]
							    ? ", " . $params["permalink_parent"]["where"]
							    : ""
							) . "
							, " . $db->toSql($ID_item, "Number") . " 
							" . ($params["rel_lang"]
                    			? ", " . $db->toSql($ID_lang, "Number") 
                    			: ""
							) . "
							
						)";
				$db->execute($sSQL);
			}
		}
		$res["permalink_parent"] = $permalink_parent;

		if($res["smart_url"] && $params["permalink"]) {
			if(!$params["skip_primary_lang"]
				|| ($ID_lang != LANGUAGE_DEFAULT_ID
					&& (!$limit || $limit == "lang")
				)
			) {		
//			if(!($params["skip_primary_lang"] && $ID_lang != LANGUAGE_DEFAULT_ID)) {			
//				if(!$limit || $limit == "lang") {	    
				$sSQL = "SELECT `" . $params["table"] . "`.*  
        				FROM `" . $params["table"] . "` 
        				WHERE 1 " . (is_array($params["permalink"]) && $params["rel_field"]
				                ? " AND `" . $params["rel_field"] . "` = " . $params["permalink"]["where"]
								: ""
							) . "
							AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
							" . ($params["rel_lang"]
				                ? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
				                : ""
							);
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_node = $db->getField("ID", "Number", true);

					$sSQL = "UPDATE 
						`" . $params["table"] . "`
					SET 
						" . (is_array($params["permalink"])
							? "`" . $params["permalink"]["field"] . "` = " . $db->toSql($permalink) 
							: $params["permalink"] . " = " . $db->toSql($permalink)
						) . "		        
					WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number", true);
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO  
								`" . $params["table"] . "` 
							( 
								`ID` 
							    " . (is_array($params["permalink"]) 
							        ? ", `" . $params["permalink"]["field"] . "`" 
							            . ($params["rel_field"]
							                ? ", `" . $params["rel_field"] . "`"
							                : ""
							            )
							        : ", `" . $params["permalink"] . "`"
							    ) . "		                        
								, `" . $params["rel_key"] . "`
							    " . ($params["rel_lang"]
                    				? ", `" . $params["rel_lang"] . "`"
                    				: ""
								) . "
							)
							VALUES
							(
								''
								, " . $db->toSql($permalink) . " 
								" . (is_array($params["permalink"]) && $params["rel_field"]
									? ", " . $params["permalink"]["where"]
									: ""
								) . "
								, " . $db->toSql($ID_item, "Number") . " 
								" . ($params["rel_lang"]
                    				? ", " . $db->toSql($ID_lang, "Number") 
                    				: ""
								) . "
								
							)";
					$db->execute($sSQL);
				}
//				}
			}
			$res["permalink"] = stripslash($res["permalink_parent"]) . "/" . $res["smart_url"];
		}
	}	
	
	return $res;
}

function update_vgallery_seo_mono($primary_meta, $ID_item, $ID_lang, $meta_description = null, $limit_parent = null, $meta_keywords = null, $visible = null, $stop_words = null, $params = null, $limit = null, $alt_url = null, $permalink_parent = null) {
	$db = ffDB_Sql::factory();

	$arrSqlField = array(
		"insert" => array()
		, "update" => array()
	);

	$arrPrimaryMeta = null;
	if(is_array($primary_meta))
		$arrPrimaryMeta = $primary_meta;
	elseif(strlen($primary_meta)) {
		$arrPrimaryMeta["smart_url"] = ($stop_words
			? ffCommon_url_rewrite_strip_word(strip_tags($primary_meta), explode(",", $stop_words))
			: ffCommon_url_rewrite(strip_tags($primary_meta))
		);
		$arrPrimaryMeta["title"] = strip_tags($primary_meta);
		$arrPrimaryMeta["header"] = $primary_meta;
	}

	$primary_key = ($params["primary_key"]
		? $params["primary_key"]
		: "ID"
	);
	
	$sSQL = "SELECT `" . $params["table"] . "`.ID 
			FROM `" . $params["table"] . "` 
                " . ($params["skip_primary_lang"] && $params["table"] != $params["primary_table"] 
                	? " INNER JOIN `" . $params["primary_table"] . "` ON `" . $params["primary_table"] . "`.`" . $primary_key  . "` = `" . $params["table"] . "`.`" . $params["rel_key"] . "`"
                	: ""
                ) . "
			WHERE 1
			    AND `" . $params["table"] . "`.`" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
			    " . ($params["rel_lang"]
                    ? " AND `" . $params["table"] . "`.`" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    : ""
			    );
	$db->query($sSQL);
	if($db->nextRecord()) {
		$ID_node = $db->getField("ID", "Number", true);	    
	}
//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	if(is_array($arrPrimaryMeta)) {
	   /* if($stop_words) {
    		$real_smart_url = ffCommon_url_rewrite_strip_word(strip_tags($smart_url), explode(",", $stop_words));
	    } else {
    		$real_smart_url = ffCommon_url_rewrite(strip_tags($smart_url));
	    }*/   
		if($params["smart_url"] && $arrPrimaryMeta["smart_url"]) { 
			//Inserisce/Aggiorna lo Smart_url
			$sSQL = "SELECT `" . $params["table"] . "`.ID 
				    FROM `" . $params["table"] . "` 
                		" . ($params["skip_primary_lang"] && $params["table"] != $params["primary_table"] 
                			? " INNER JOIN `" . $params["primary_table"] . "` ON `" . $params["primary_table"] . "`.`" . $primary_key  . "` = `" . $params["table"] . "`.`" . $params["rel_key"] . "`"
                			: ""
                		) . "
				    WHERE 
				        " .  $params["smart_url"] . " = " . $db->toSql($arrPrimaryMeta["smart_url"]) . "
				        AND `" . $params["rel_key"] . "` <> " . $db->toSql($ID_item, "Number") . "
				        " . ( $params["table"] != $params["primary_table"] 
				        	? " AND `" . $params["table"] . "`.`" . $params["permalink_parent"] . "` = (SELECT primary_tbl.`" . $params["primary_parent"] . "`
				        																				FROM " . $params["primary_table"] . " AS primary_tbl
				        																				WHERE primary_tbl.ID = " . $db->toSql($ID_item, "Number") . ")"
				        	: ""
				        )
				        . ($params["rel_lang"]
                    		? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    		: ""
				        )
				        . ($limit_parent === null
                    		? ""
                    		: " AND (`" . $params["primary_table"] . "`.parent = " . $db->toSql($limit_parent, "Text") . "
                    			OR `" . $params["primary_table"] . "`.parent LIKE '" . $db->toSql($limit_parent, "Text", false) . "/%')");
			$db->query($sSQL);
			if($db->nextRecord() && strpos($arrPrimaryMeta["smart_url"], "-" . $ID_item) === false) {
        		$arrPrimaryMeta["smart_url"] = $arrPrimaryMeta["smart_url"] . "-" . $ID_item;
			}

			$res["smart_url"] = $arrPrimaryMeta["smart_url"];

			$arrSqlField["update"]["smart_url"] = "`" . $params["smart_url"] . "` = " . $db->toSql($arrPrimaryMeta["smart_url"]);
			$arrSqlField["insert"]["field"]["smart_url"] = "`" . $params["smart_url"] . "`";
			$arrSqlField["insert"]["value"]["smart_url"] = $db->toSql($arrPrimaryMeta["smart_url"]);
		}	    
	    
		if($params["title"] && isset($arrPrimaryMeta["title"])) {
			$res["title"] = $arrPrimaryMeta["title"];

			$arrSqlField["update"]["meta_title"] = "`" . $params["title"] . "` = " . $db->toSql($arrPrimaryMeta["title"]);
			$arrSqlField["insert"]["field"]["meta_title"] = "`" . $params["title"] . "`";
			$arrSqlField["insert"]["value"]["meta_title"] = $db->toSql($arrPrimaryMeta["title"]);
		}
	    if($params["header"] && isset($arrPrimaryMeta["header"])) {
	    	$res["header"] = $arrPrimaryMeta["header"];

			$arrSqlField["update"]["meta_title_alt"] = "`" . $params["header"] . "` = " . $db->toSql($arrPrimaryMeta["header"]);
			$arrSqlField["insert"]["field"]["meta_title_alt"] = "`" . $params["header"] . "`";
			$arrSqlField["insert"]["value"]["meta_title_alt"] = $db->toSql($arrPrimaryMeta["header"]);
		}
        if($params["robots"] && isset($arrPrimaryMeta["robots"])) {
	    	$res["robots"] = $arrPrimaryMeta["robots"];

			$arrSqlField["update"]["meta_robots"] = "`" . $params["robots"] . "` = " . $db->toSql($arrPrimaryMeta["robots"]);
			$arrSqlField["insert"]["field"]["meta_robots"] = "`" . $params["robots"] . "`";
			$arrSqlField["insert"]["value"]["meta_robots"] = $db->toSql($arrPrimaryMeta["robots"]);
		}
	    if($params["meta"] && isset($arrPrimaryMeta["meta"])) {
	    	$res["meta"] = $arrPrimaryMeta["meta"];

			$arrSqlField["update"]["meta"] = "`" . $params["meta"] . "` = " . $db->toSql($arrPrimaryMeta["meta"]);
			$arrSqlField["insert"]["field"]["meta"] = "`" . $params["meta"] . "`";
			$arrSqlField["insert"]["value"]["meta"] = $db->toSql($arrPrimaryMeta["meta"]);
		}
	    if($params["httpstatus"] && isset($arrPrimaryMeta["httpstatus"])) {
	    	$res["httpstatus"] = $arrPrimaryMeta["httpstatus"];

			$arrSqlField["update"]["httpstatus"] = "`" . $params["httpstatus"] . "` = " . $db->toSql($arrPrimaryMeta["httpstatus"]);
			$arrSqlField["insert"]["field"]["httpstatus"] = "`" . $params["httpstatus"] . "`";
			$arrSqlField["insert"]["value"]["httpstatus"] = $db->toSql($arrPrimaryMeta["httpstatus"]);
		}
	    if($params["canonical"] && isset($arrPrimaryMeta["canonical"])) {
	    	$res["canonical"] = $arrPrimaryMeta["canonical"];

			$arrSqlField["update"]["meta_canonical"] = "`" . $params["canonical"] . "` = " . $db->toSql($arrPrimaryMeta["canonical"]);
			$arrSqlField["insert"]["field"]["meta_canonical"] = "`" . $params["canonical"] . "`";
			$arrSqlField["insert"]["value"]["meta_canonical"] = $db->toSql($arrPrimaryMeta["canonical"]);
		} 
	}
	
	if($visible !== null && $params["visible"]) {
		$res["visible"] = $visible;

		$arrSqlField["update"]["visible"] = "`" . $params["visible"] . "` = " . $db->toSql($visible);
		$arrSqlField["insert"]["field"]["visible"] = "`" . $params["visible"] . "`";
		$arrSqlField["insert"]["value"]["visible"] = $db->toSql($visible);
	}	
	
	if($meta_description !== null) {
	    //Check Meta_description se aggiornabile
		$allow_edit_meta_description = false;
		$update_meta_description = false;
		if(is_array($meta_description)) {
		    if(array_key_exists("ori", $meta_description) && array_key_exists("new", $meta_description)) {
		        $sSQL = "SELECT `" . $params["table"] . "`.* 
		                FROM `" . $params["table"] . "`  
		                WHERE 1 
				            AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
				            " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
				            );
		        $db->query($sSQL);
		        if($db->nextRecord()) {
		            if(!strlen($db->getField(is_array($params["description"]) ? $params["description"]["field"] : $params["description"], "Text", true)) || $db->getField(is_array($params["description"]) ? $params["description"]["field"] : $params["description"], "Text", true) == $meta_description["ori"]) {
	                    $update_meta_description = $db->getField("ID", "Number", true);
		                $allow_edit_meta_description = true;
		            }
		        } else {
		            $allow_edit_meta_description = true;
		        }
		        $real_meta_description = $meta_description["new"];
		    }
		} else {
		    $allow_edit_meta_description = true;
		    
		    $real_meta_description = $meta_description;
		}

		//Inserisce /aggiorna il Meta_description
		if($allow_edit_meta_description) {
		    if(check_function("get_short_description"))
		        $arrShortDesc = get_short_description($real_meta_description);

		    $real_meta_description = $arrShortDesc["text"];

		   	$res["description"] = $real_meta_description;   
		   
			$arrSqlField["update"]["meta_description"] = "`" . $params["description"] . "` = " . $db->toSql($real_meta_description);
			$arrSqlField["insert"]["field"]["meta_description"] = "`" . $params["description"] . "`";
			$arrSqlField["insert"]["value"]["meta_description"] = $db->toSql($real_meta_description);
		}
	}	
	
	if($meta_keywords !== null) {
		//Check Meta_keywords se aggiornabile
		$allow_edit_meta_keywords = false;
		$update_meta_keywords = false;
		if(is_array($meta_keywords)) {
		    if(array_key_exists("ori", $meta_keywords) && array_key_exists("new", $meta_keywords)) {
				$sSQL = "SELECT `" . $params["table"] . "`.*
			            FROM `" . $params["table"] . "` 
			            WHERE 1 " . (is_array($params["keywords"]) && $params["rel_field"]
                    			? " AND `" . $params["rel_field"] . "` = " . $params["keywords"]["where"]
								: ""
					        ) . "
						    AND `" . $params["rel_key"] . "` = " . $db->toSql($ID_item, "Number") . "
					        " . ($params["rel_lang"]
                    			? " AND `" . $params["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
                    			: ""
					        );
			    $db->query($sSQL);
			    if($db->nextRecord()) {
			        if(!strlen($db->getField(is_array($params["keywords"]) ? $params["keywords"]["field"] : $params["keywords"], "Text", true)) || $db->getField(is_array($params["keywords"]) ? $params["keywords"]["field"] : $params["keywords"], "Text", true) == $meta_keywords["ori"]) {
			            $update_meta_keywords = $db->getField("ID", "Number", true);
			            $allow_edit_meta_keywords = true;
			        }
			    } else {
			        $allow_edit_meta_keywords = true;
			    }	
			    $real_meta_keywords = $meta_keywords["new"];
			}
		} else {
		    $allow_edit_meta_keywords = true;
		    
		    $real_meta_keywords = $meta_keywords;
		}
	    //Inserisce /aggiorna il Meta_keywords
	    if($allow_edit_meta_keywords) {		
			if($update_meta_keywords) {
				$res["keywords"] = $real_meta_keywords;

				$arrSqlField["update"]["meta_keywords"] = "`" . $params["keywords"] . "` = " . $db->toSql($real_meta_keywords);
				$arrSqlField["insert"]["field"]["meta_keywords"] = "`" . $params["keywords"] . "`";
				$arrSqlField["insert"]["value"]["meta_keywords"] = $db->toSql($real_meta_keywords);
			}
		}
	}	
	
	if($alt_url !== null && $params["alt_url"]) {
		$res["alt_url"] = $alt_url;

		$arrSqlField["update"]["alt_url"] = "`" . $params["alt_url"] . "` = " . $db->toSql($alt_url);
		$arrSqlField["insert"]["field"]["alt_url"] = "`" . $params["alt_url"] . "`";
		$arrSqlField["insert"]["value"]["alt_url"] = $db->toSql($alt_url);
	}	
    if($params["smart_url"] && $res["title"] && !$res["smart_url"]) {
    	$res["smart_url"] = ffCommon_url_rewrite($res["title"]);

    	$arrSqlField["update"]["smart_url"] = "`" . $params["smart_url"] . "` = " . $db->toSql($res["smart_url"]);
		$arrSqlField["insert"]["field"]["smart_url"] = "`" . $params["smart_url"] . "`";
		$arrSqlField["insert"]["value"]["smart_url"] = $db->toSql($res["smart_url"]);
	}
    		
	if($permalink_parent !== null && $params["permalink_parent"]) {
		$res["permalink_parent"] = $permalink_parent;

		$arrSqlField["update"]["permalink_parent"] = "`" . $params["permalink_parent"] . "` = " . $db->toSql($permalink_parent);
		$arrSqlField["insert"]["field"]["permalink_parent"] = "`" . $params["permalink_parent"] . "`";
		$arrSqlField["insert"]["value"]["permalink_parent"] = $db->toSql($permalink_parent);
		
		
		if($res["smart_url"] && $params["permalink"]) {
			$res["permalink"] = stripslash($res["permalink_parent"]) . "/" . $res["smart_url"];

			$arrSqlField["update"]["permalink"] = "`" . $params["permalink"] . "` = " . $db->toSql($res["permalink"]);
			$arrSqlField["insert"]["field"]["permalink"] = "`" . $params["permalink"] . "`";
			$arrSqlField["insert"]["value"]["permalink"] = $db->toSql($res["permalink"]);
		}
	}
		
	if(!$params["skip_primary_lang"]
		|| ($ID_lang != LANGUAGE_DEFAULT_ID
			&& (!$limit || $limit == "lang")
		)
	) {
//	if(!($params["skip_primary_lang"] && $ID_lang != LANGUAGE_DEFAULT_ID)) {
//		if(!$limit || $limit == "lang") {
		if($ID_node) {
			if(count($arrSqlField["update"])) {
				$sSQL = "UPDATE `" . $params["table"] . "` SET
							" . implode(", ", $arrSqlField["update"]). "
						WHERE `" . $params["table"] . "`.ID = " . $db->toSql($ID_node, "Number") . "
				";
			}
		} else {
			if(count($arrSqlField["insert"])) {
				$sSQL = "INSERT INTO `" . $params["table"] . "`
						(
							ID
							, `" . $params["rel_key"] . "`
							" . ($params["rel_lang"]
				                ? ", `" . $params["rel_lang"] . "`"
				                : ""
							) . "
							, " . implode(", ", $arrSqlField["insert"]["field"]). "
						)
						VALUES
						(
							null
							, " . $db->toSql($ID_item, "Number") . "
							" . ($params["rel_lang"]
				                ? ", " . $db->toSql($ID_lang, "Number")
				                : ""
							) . "
							, " . implode(", ", $arrSqlField["insert"]["value"]). "
						)";
			}
		}

		if(strlen($sSQL))
			$db->execute($sSQL);
//		}
	}
	return $res;
}