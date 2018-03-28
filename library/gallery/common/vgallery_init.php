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

check_function("get_file_properties");
check_function("get_thumb");
check_function("normalize_url");

function vgallery_init($params, $layout, $data_storage = null) 
{
	$is_dir 											= null;
	$vgallery_group 									= null;
	$vgallery_name 										= $layout["db"]["value"];
	$virtual_path 										= ($layout["db"]["real_path"] && $layout["db"]["real_path"] != "/" && strpos($params["settings_path"], $layout["db"]["real_path"]) === 0
															? substr($params["settings_path"], strlen($layout["db"]["real_path"]))
															: $params["settings_path"]
														);
	switch($vgallery_name) {
		case "anagraph":
			$vgallery_type = "anagraph";
			if($layout["db"]["params"] > 0) {
				$arrVgalleryGroup = array(
					"ID" => $layout["db"]["params"]
				);
			}	
			if($virtual_path)
				$is_dir = false;
			else
				$is_dir = true;
	
			break;
		case "files":
			$vgallery_type = "files";
			break;
		default:
			$vgallery_type = "vgallery";
			$virtual_path = "/" . $vgallery_name . stripslash($layout["db"]["params"]) . stripslash($virtual_path);
	}

	if($data_storage[$vgallery_type][$virtual_path]) {
		$is_dir = $data_storage[$vgallery_type][$virtual_path]["is_dir"];
	} elseif($is_dir === null) {
		$is_dir = get_vgallery_is_dir(basename($virtual_path), ffCommon_dirname($virtual_path));                	
	}

	$buffer_vg_view = "";
	$buffer_vg_thumb["content"] = "";
	$buffer_vg_parent["content"] = "";
	if($is_dir) {
		//parent vgallery
		if($layout["settings"]["AREA_VGALLERY_SHOW_THUMB_PARENT"] && $virtual_path != "/" . $vgallery_name) {
		    if(check_function("process_vgallery_view"))
		        $buffer_vg_view = process_vgallery_view(
		                $virtual_path . $params["user_path_shard"]
		                , $vgallery_name
		                , array(
		                    "search" => $params["search"]
	                        , "navigation" => $params["navigation"]
                            //, "source_user_path" => $source_user_path
		                )
		                , $layout
		            );
		}

		//vgallery_thumb
		if(check_function("process_vgallery_thumb"))
		    $buffer_vg_thumb = process_vgallery_thumb(
		            $virtual_path
		            , $vgallery_type
		            , array(
		                "user_path" => $params["user_path"]
		                , "group" => $arrVgalleryGroup
		                , "search" => $params["search"]
	                    , "navigation" => $params["navigation"]
	                    , "vgallery_name" => $vgallery_name
	                    , "template_skip_hide" => $params["xhr"]
                        //, "source_user_path" => $source_user_path
		            )                    
		            , $layout
		        );
	    if($layout["settings"]["AREA_VGALLERY_SHOW_THUMB_TOP"]) {
	        $res["content"] = $buffer_vg_thumb["content"] . (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "");
	    } else {
	        $res["content"] = (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "") . $buffer_vg_thumb["content"];
	    }
	    
        $res["pre"] = $buffer_vg_thumb["pre"];
        $res["post"] = $buffer_vg_thumb["post"];	    
	} else {
		/**
		*  Check Adv Group Params  
		*/
 		if(ffCommon_dirname($virtual_path) != "/") {
			if(check_function("get_vgallery_group"))
			    $arrVgalleryGroup = get_vgallery_group(basename($virtual_path), "ID", ffcommon_dirname($virtual_path));

			if(is_array($arrVgalleryGroup)) {
			    $virtual_path = ffCommon_dirname($virtual_path);
			    $vgallery_group_name = $arrVgalleryGroup["name"];
			    $enable_child = $arrVgalleryGroup["enable_child"];
			    if($arrVgalleryGroup["count"]) {
			        $vgallery_group["ID"] = $arrVgalleryGroup["field"];
			        $vgallery_group["name"] = $vgallery_group_name;
			    } 
			}
		}	

		//vgallery view
		if(check_function("process_vgallery_view")) {
		    $buffer_vg_view = process_vgallery_view(
		            $virtual_path . $params["user_path_shard"]
		            , $vgallery_name
		            , array(
		                "group" => $vgallery_group
		                , "search" => $params["search"]
	                    , "navigation" => $params["navigation"]
                        //, "source_user_path" => $source_user_path
		            )
		            , $layout
		        );
		    if($buffer_vg_view === false) //da sostituire con il primary del data_storage
		        $res["page_invalid"] = true;
		    else		                
		       	$res["page_invalid"] = false;
		}

		//parent vgallery
		if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW_PARENT"] && ffCommon_dirname($virtual_path) != "/") {
		    if(check_function("process_vgallery_thumb")) {
		        $buffer_vg_parent = process_vgallery_thumb(
		                ffCommon_dirname($virtual_path)
		                , $vgallery_name
		                , array(
		                    "user_path" => $params["user_path"]
		                    , "group" => $arrVgalleryGroup
		                    , "search" => $params["search"]
	                        , "navigation" => $params["navigation"]
	                        , "vgallery_name" => $vgallery_name
	                        , "template_skip_hide" => $params["xhr"]
                            //, "source_user_path" => ffCommon_dirname($source_user_path)
		                )
		                , $layout
		            );
			}
		}
		
	    if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW_TOP"]) {
	        $res["content"] = (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "") . $buffer_vg_parent["content"];
	    } else {
	        $res["content"] = $buffer_vg_parent["content"] . (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "");
	    }    
	    
	    $res["pre"] = $buffer_vg_view["pre"];
        $res["post"] = $buffer_vg_view["post"];
	}
	
    return $res;
}

function process_vgallery_fields_system($limit = null, $rev = false) {
	static $fields_system_loaded = null;
	
	if($rev)
		$type = "rev";
	else
		$type = "orig";
	
	if(!$fields_system_loaded) {
		$db = ffDB_Sql::factory();
		$sSQL = "SELECT vgallery_fields.*
				FROM vgallery_fields
					INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
				WHERE vgallery_type.name = 'system'";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$fields_system_loaded["orig"][$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
				$fields_system_loaded["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
			} while($db->nextRecord());
		}
	}

	if(is_array($limit)) {
		$res = array_intersect_key($fields_system_loaded[$type], array_flip($limit));
	} elseif(strlen($limit)) {
		$res = $fields_system_loaded[$type][$limit];
	} else {
		$res = $fields_system_loaded[$type];
	}
	
	return $res;
}


function process_vgallery_father($params, $mode = "thumb") {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $vg_father = array();

    $default_params = array(
	"name" => null
	, "title" => null
	, "group" => null
	, "search" => null
	, "learnmore" => null
	, "publishing" => null
	, "wishlist" => null
	, "unic_id" => null
	//, "unic_id_lower" => null
	, "vgallery_name" => null
	, "user_path" => null
	, "source_user_path" => null
	, "is_dir" => null
	, "settings_path" => null
	, "settings_type" => null
	, "settings_prefix" => null
	, "settings_layout" => null
	, "enable_title" => null
	, "enable_sub_title" => null
	, "enable_user_sort" => false
	, "enable_sort" => true
	, "sort_default" => null
	, "sort_method" => null
	, "allow_insert" => null
	, "allow_insert_dir" => null
	, "allow_edit" => null
	, "template_suffix" => ""
	, "framework_css" => false
	, "nodes" => array()
	, "is_wishlisted" => false
	, "available" => true
	, "ref" => null
	, "cart_detail" => null
    );

    $params = array_replace($default_params, (is_array($params) ? $params : array()));

	$vg_father["seo"] = array();
	$vg_father["seo"]["meta"] = array();
    $vg_father["seo"]["mode"] = $mode;

    $vg_father["mode"] = $mode;
    $vg_father["is_dir"] = $params["is_dir"];
    $vg_father["permission"] = array("owner" => 0
	, "visible" => true
    );
    $vg_father["available"] = $params["available"];
    $vg_father["source_user_path"] = $params["source_user_path"];

    $vg_father["enable_title"] = $params["enable_title"];
    $vg_father["enable_sub_title"] = $params["enable_sub_title"];

    if ($mode != "thumb") {
		$vg_father["disable_addtocart"] = false;

		if ($params["ref"] === null) {
		    if (isset($_REQUEST["ref"]) && $_REQUEST["ref"] > 0) {
			$vg_father["cart_ref"] = $_REQUEST["ref"];
		    }
		}

		if ($params["ID_cart_detail"] === null) {
		    if (isset($_REQUEST["datc"]) && $_REQUEST["datc"] > 0) {
			$vg_father["disable_addtocart"] = $_REQUEST["datc"];
		    }
		}
    }

	$vg_father["allow_insert"] = $params["allow_insert"];
    $vg_father["allow_insert_dir"] = $params["allow_insert_dir"];
    $vg_father["allow_edit"] = $params["allow_edit"];
    
    /* init custom var */
    $vg_father["vgallery_type"] = "";
    $vg_father["limit_level"] = 0;
    $vg_father["limit_type"] = array();
    $vg_father["enable_ecommerce"] = AREA_SHOW_ECOMMERCE;
    $vg_father["use_pricelist_as_item"] = false;
    $vg_father["enable_multilang_visible"] = true;
    $vg_father["enable_multi_cat"] = false;
    $vg_father["random"] = false;
    $vg_father["automatic_selection"] = true;
    $vg_father["limit"] = $params["limit"];

    $vg_father["settings_path"] 	= $params["settings_path"];
    $vg_father["settings_type"] 	= $params["settings_type"];
    $vg_father["settings_prefix"] 	= $params["settings_prefix"];
    $vg_father["settings_layout"] 	= $params["settings_layout"];
	$vg_father["user_path"] 		= $params["user_path"];
    $vg_father["permalink"] 		= $params["permalink"];
/*
    $vg_father["permalink"] 		= ($params["permalink"]
                                        ? $params["permalink"]
                                        : $params["parent"]
                                    );*/
                                    
    $vg_father["enable_sort"] = $params["enable_sort"];
    $vg_father["template"]["custom_name"] = ($params["template_name"] 
    											? $params["template_name"] 
    											: ($params["vgallery_name"] 
    												? $params["vgallery_name"] . "_" . $mode 
    												: ""
											    )
										    );
    if ($params["name"] == "learnmore") {
		$vg_father = array_replace($vg_father, $params["learnmore"]);
		$vg_father["template"]["custom_name"] 	= ($params["template_name"] 
													? $params["template_name"] . "_" . $vg_father["src_field_name"]
													: $vg_father["src_layout"]["value"] . "_" . $vg_father["src_mode"] . "_" . $vg_father["src_field_name"]
												);
		//$vg_father["template"]["custom_name"] 	= $params["vgallery_name"] . "_in_" . $vg_father["src_layout"]["value"];

		$vg_father["vgallery_class"] 														= ffCommon_url_rewrite($vg_father["template"]["custom"]);

		
		//non e impostato il titolo
		//$vg_father["title"] 					= ffTemplate::_get_word_by_code($vg_father["template"]["custom_name"]);
				
/*		$vg_father["vgallery_name"] = $params["vgallery_name"];
		$vg_father["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9\_]/', '', $params["unic_id"] . (strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_name"] : ""
					) . ((count(explode("/", $vg_father["user_path"])) - 1) > 1 && !strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_type"] : ""
				)) . "_title"
		);*/
		//print_r($params["learnmore"]);
		//$vg_father["limit"]["fields"] = $params["learnmore"]["dst_field"];
		$vg_father["enable_title"] = false;
		$vg_father["enable_sub_title"] = false;
		//$vg_father["enable_date"] 	= false;

		$vg_father["enable_user_sort"] = false;
		$vg_father["enable_sort"] = true;
		
    } elseif ($params["name"] == "anagraph") {
        $vg_father["seo"]["mode"] .= "-anagraph";
		$vg_father["vgallery_name"] = $params["vgallery_name"];
		if($params["title"]) {
			$vg_father["title"] = $params["title"];
		} else {
			$vg_father["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9\_]/', '', $params["unic_id"] . (strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_name"] : ""
						) . ((count(explode("/", $vg_father["user_path"])) - 1) > 1 && !strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_type"] : ""
					)) . "_title"
			);
		}
		$vg_father["src"]["type"] = "anagraph";
		$vg_father["src"]["table"] = "anagraph";
		$vg_father["src"]["block"]["path"] = cache_get_page_by_id("anagraph");
		$vg_father["src"]["permalink_only"] = true;
		$vg_father["skip_lang"] = true;

		$vg_father["vgallery_class"] = $params["vgallery_name"] . ($params["group"]["smart_url"] ? " " . $params["group"]["smart_url"] : "");
		$vg_father["enable_user_sort"] = $params["enable_user_sort"];

		$vg_father["enable_multilang_visible"] = false;

		if ($mode != "thumb") {
			if(!$globals->data_storage["anagraph"][$vg_father["user_path"]]) {
		    	$sSQL = "SELECT 
							anagraph.ID 						AS ID
	                        , anagraph.ID_type 					AS `ID_type`
	                        , anagraph_type.name 				AS `type`
	                        , anagraph.created 					AS `created`
	                        , anagraph.last_update 				AS `last_update`
	                        , anagraph.published_at 			AS `published_at`
				            , anagraph.`class` 				    AS `class`
	                        , anagraph.owner 					AS `owner`
	                        , anagraph.visible 					AS `visible`
	                        , anagraph.`parent`          		AS `parent`
	                        , anagraph.`permalink`          	AS `permalink`
	                        , anagraph.`smart_url`              AS `smart_url`
	                        , anagraph.`meta_title`             AS `meta_title`
	                        , anagraph.`meta_description`       AS `meta_description`
                            , anagraph.`meta_robots`            AS `robots`
                            , anagraph.`meta_canonical`         AS `canonical`
                            , anagraph.`meta`                   AS `meta`
                            , anagraph.`httpstatus`             AS `httpstatus`
	                        , anagraph.`keywords`               AS `keywords`
	                        , anagraph.`avatar`                 AS `avatar`
	                        , anagraph.`billreference`          AS `billreference`
	                        , anagraph.`billcf`                 AS `billcf`
	                        , anagraph.`billpiva`               AS `billpiva`
	                        , anagraph.`billaddress`            AS `billaddress`
	                        , anagraph.`billcap`                AS `billcap`
	                        , anagraph.`billtown`               AS `billtown`
	                        , anagraph.`billprovince`           AS `billprovince`
	                        , anagraph.`billstate`              AS `billstate`
	                        , anagraph.`name`                   AS `name`
	                        , anagraph.`surname`                AS `surname`
	                        , anagraph.`tel`                    AS `tel`
	                        , anagraph.`email`                  AS `email`
	                        , anagraph.`shippingreference`      AS `shippingreference`
	                        , anagraph.`shippingaddress`        AS `shippingaddress`
	                        , anagraph.`shippingcap`            AS `shippingcap`
	                        , anagraph.`shippingtown`           AS `shippingtown`
	                        , anagraph.`shippingprovince`       AS `shippingprovince`
	                        , anagraph.`shippingstate`          AS `shippingstate`
	                        , anagraph.`tags`          			AS `tags`
                            , anagraph.`referer`                AS `referer`
        				FROM anagraph
        					INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type
                        WHERE
                        " . (OLD_VGALLERY
                            ? " anagraph.smart_url = " . $db->toSql(basename($vg_father["user_path"]))
                            : ($vg_father["permalink"]
                            	? " anagraph.permalink = " . $db->toSql($vg_father["permalink"])
                            	: " anagraph.smart_url = " . $db->toSql(basename($vg_father["user_path"]))
                            )
                        )
                        . " ORDER BY anagraph.visible DESC, anagraph.ID";
			    $db->query($sSQL);
			    if ($db->nextRecord()) {
			    	do {
			    		$globals->data_storage["anagraph"]["/" . $db->record["smart_url"]] = $db->record;
					} while($db->nextRecord());
				}
			}
			
			$father = $globals->data_storage["anagraph"][$vg_father["user_path"]];
			if($father) { ///da fare tuttoi tutto
				$unic_id_node 																	= $father["ID"]; //$db->getField("ID", "Number", true);
				$ID_owner																		= $father["owner"]; //$db->getField("owner", "Number", true);

				$vg_father["ID_node"] 															= $father["ID"]; //$db->getField("ID", "Number", true);

				$vg_father["nodes"][$unic_id_node]["pricelist"] 								= null;
				$vg_father["nodes"][$unic_id_node]["ID_cart_detail"] 							= null;

				$vg_father["nodes"][$unic_id_node]["preload"][$vg_father["src"]["table"]] 		= $father; //$db->record;

				$vg_father["nodes"][$unic_id_node]["ID"] 										= $unic_id_node;
				$vg_father["nodes"][$unic_id_node]["parent"] 									= $vg_father["source_user_path"]; //$db->getField("parent", "Text", true);
				$vg_father["nodes"][$unic_id_node]["name"] 										= $father["smart_url"]; //$db->getField("smart_url", "Text", true);
				$vg_father["nodes"][$unic_id_node]["ID_type"] 									= $father["ID_type"]; //$db->getField("ID_type", "Number", true);
				$vg_father["nodes"][$unic_id_node]["type"] 										= $father["type"]; //$db->getField("type", "Text", true);											******t
				$vg_father["nodes"][$unic_id_node]["is_dir"] 									= 0;
				//$vg_father["nodes"][$unic_id_node]["data_type_publish"] 						= "";
				//$vg_father["nodes"][$unic_id_node]["vgallery_name"] 							= $vg_father["vgallery_name"];
				$vg_father["nodes"][$unic_id_node]["vgallery_class"]							= "anagraph";
				$vg_father["nodes"][$unic_id_node]["created"] 									= $father["created"]; 
				$vg_father["nodes"][$unic_id_node]["last_update"] 								= $father["last_update"]; 
				$vg_father["nodes"][$unic_id_node]["published"] 								= $father["published_at"]; 
				$vg_father["nodes"][$unic_id_node]["owner"] 									= $ID_owner;
				$vg_father["seo"]["owner"] 														= $ID_owner;

				$vg_father["nodes"][$unic_id_node]["class"] 									= $father["class"]; //$db->getField("class", "Text", true);
				$vg_father["nodes"][$unic_id_node]["highlight"] 								= null;
				$vg_father["nodes"][$unic_id_node]["is_wishlisted"] 							= false;


				$vg_father["nodes"][$unic_id_node]["tags"] 										= $father["tags"]; //$db->getField("tags", "Text", true);
                $vg_father["seo"]["tags"]["primary"]                                            = $father["tags"];
                $vg_father["nodes"][$unic_id_node]["referer"]                                   = $father["referer"];

				if (array_key_exists("permalink", $father)) {
					$vg_father["nodes"][$unic_id_node]["permalink"] 							= $father["permalink"];
					$vg_father["nodes"][$unic_id_node]["smart_url"] 							= basename($father["permalink"]);
					$vg_father["nodes"][$unic_id_node]["permalink_parent"] 						= ffcommon_dirname($father["permalink"]);
					$father["permalink_parent"] 												= $vg_father["nodes"][$unic_id_node]["permalink_parent"];
				} else {
					if (array_key_exists("smart_url", $father))
					    $vg_father["nodes"][$unic_id_node]["smart_url"] 						= $father["smart_url"];
					if (array_key_exists("parent", $father))
						$vg_father["nodes"][$unic_id_node]["permalink_parent"] 					= $father["parent"];
				}

				if (array_key_exists("meta_title", $father)) {
				    $vg_father["nodes"][$unic_id_node]["meta"]["title"] 						= $father["meta_title"];
				    $vg_father["seo"]["title"] 													= $father["meta_title"];
				    $vg_father["seo"]["title_header"] 											= $father["meta_title"];
				}
				if (array_key_exists("meta_title_alt", $father)) {
				    $vg_father["nodes"][$unic_id_node]["meta"]["title_h1"] 						= $father["meta_title_alt"];
				    if (!$vg_father["seo"]["title"])
					    $vg_father["seo"]["title"] 												= $father["meta_title_alt"];

				    $vg_father["seo"]["title_header"] 											= $father["meta_title_alt"];
				}
				if (array_key_exists("meta_description", $father)) {
				    $vg_father["nodes"][$unic_id_node]["meta"]["description"] 					= $father["meta_description"];
				    $vg_father["seo"]["meta"]["description"][] 									= $father["meta_description"];
				}
				if (array_key_exists("keywords", $father)) {
				    $vg_father["nodes"][$unic_id_node]["meta"]["keywords"] 						= $father["keywords"];
				    $vg_father["seo"]["meta"]["keywords"][] 									= $father["keywords"];
				}
                
                if ($father["meta"]) {
                    $arrMeta = explode("\n", $father["meta"]);
                    foreach($arrMeta AS $meta_data) {
                        $arrMetaData = explode("=", $meta_data);
                        $vg_father["seo"]["meta"][$arrMetaData[0]]                              = $arrMetaData[1];
                    }
                }
                if ($father["robots"])
                    $seo["meta"]["robots"]                                                      = $father["robots"];
                if ($father["canonical"])
                    $vg_father["seo"]["canonical"]                                              = $father["canonical"];

                if ($father["httpstatus"])
                    $vg_father["seo"]["httpstatus"]                                             = $father["httpstatus"];
                
				$vg_father["permission"]["visible"] 											= $father["visible"]; //$db->getField("visible", "Number", true);
				$vg_father["permission"]["owner"] 												= $ID_owner;
		    } else {
	    		return null;
		    }
		}
    } elseif ($params["name"] == "publishing") {
    	if(!$globals->data_storage["publishing"][$params["publishing"]["ID"]]) {
			if(is_array($globals->tpl["blocks_by_type"]["publishing"]) && count($globals->tpl["blocks_by_type"]["publishing"])) {
	        	$publishing_keys = implode(",", $globals->tpl["blocks_by_type"]["publishing"]);
	        } else {
	        	$publishing_keys = $params["publishing"]["ID"];
	        }    	
			$sSQL = "SELECT publishing.`ID`                                   	AS `ID`
				        , publishing.`name`                                     AS `title`
				        , publishing.`limit`                                    AS `limit` 
				        , publishing.`area`                                     AS `src_type`
				        , publishing.`contest`                                  AS `contest`
				        , publishing.`relative_path`                            AS `relative_path`
				        , publishing.`random`                                   AS `random`
				        , publishing.`full_selection`                           AS `automatic_selection`
				        , (GROUP_CONCAT(publishing_fields.field_hash SEPARATOR '|')) AS limit_fields 
				    FROM publishing 
		    			INNER JOIN publishing_fields ON publishing_fields.ID_publishing = publishing.ID
				    WHERE publishing.ID IN(" . $db->toSql($publishing_keys, "Text", false) . ")
				    GROUP BY publishing.ID";
			$db->query($sSQL);
			if ($db->nextRecord()) {
				do {
					$globals->data_storage["publishing"][$db->record["ID"]] = $db->record;
				} while($db->nextRecord());
			}
		}
		
		$father = $globals->data_storage["publishing"][$params["publishing"]["ID"]];
		if($father) {
			$vg_father["seo"]["mode"] 															= "publishing-" . $father["ID"];
		    $params["publishing"]["limit_fields"] 												= $father["limit_fields"]; //$db->getField("limit_fields", "Text", true);

		    $vg_father["limit"]["elem"] 														= $father["limit"]; //$db->getField("limit", "Number", true);

		    $vg_father["ID_vgallery"] 															= 0;
		    $vg_father["ID_node"] 																= 0;

		    $vg_father["src"]["type"] 															= $father["src_type"]; // $db->getField("src_type", "Text", true);
		    $vg_father["enable_sub_title"]														= false;
		    switch ($vg_father["src"]["type"]) {
			case "vgallery":
			    $vg_father["src"]["table"] 														= "vgallery_nodes";
			    $vg_father["vgallery_name"] 													= $father["contest"]; //$db->getField("contest", "Text", true);
				$vg_father["src"]["block"]["path"] 												= "";
			    break;
			case "gallery":
			    $vg_father["src"]["table"] 														= "files";
			    $vg_father["vgallery_name"] 													= "media";
			    $vg_father["src"]["block"]["path"] 												= cache_get_page_by_id("gallery");
			    break;
			default:
			    $vg_father["src"]["table"] 														= $vg_father["src"]["type"];
			    $vg_father["src"]["block"]["path"] 												= cache_get_page_by_id($vg_father["src"]["type"]);
			    $vg_father["src"]["permalink_only"] 											= true;
			    $vg_father["skip_lang"] 														= true;
			    $vg_father["vgallery_name"] 													= $vg_father["src"]["type"];
			    $params["group"]["ID"] 															= $father["contest"]; //$db->getField("contest", "Number", true);
		    }

		    $vg_father["vgallery_class"] 														= ffCommon_url_rewrite($father["title"]);
		    if($vg_father["enable_title"])
		    	$vg_father["title"] 															= ffTemplate::_get_word_by_code($father["title"]);

		    $vg_father["template"]["custom_name"] 												= $vg_father["vgallery_class"];
		    $vg_father["is_dir"] 																= false;

		    /*
		      $vg_father["vgallery_type"] 														= $father["limit_level"]; //$db->getField("limit_level", "Text", true);
		      $vg_father["limit_level"] 														= $father["limit_level"]; //$db->getField("limit_level", "Number", true);
		      $vg_father["limit_type"] 															= explode(",", $father["limit_type"]); //explode(",", $db->getField("limit_type", "Text", true));
		      $vg_father["enable_ecommerce"] 													= $father["enable_ecommerce"]; //$db->getField("enable_ecommerce", "Number", true);
		      $vg_father["use_pricelist_as_item"] 												= $father["use_pricelist_as_item"]; //$db->getField("use_pricelist_as_item", "Number", true);
		      $vg_father["enable_multilang_visible"]											= $father["enable_multilang_visible"]; //$db->getField("enable_multilang_visible", "Number", true);
		      $vg_father["highlight_image"] 													= $father["highlight_image"]; // $db->getField("highlight_image", "Number", true);
		     */


		    /* opzionali in base alla tipologia e il mode */
		    $vg_father["random"] 																= $father["random"]; //$db->getField("random", "Number", true);
		    $vg_father["automatic_selection"] 													= $father["automatic_selection"]; //$db->getField("automatic_selection", "Number", true);

		    $vg_father["settings_path"] 														= "/" . $vg_father["vgallery_class"];
		    $vg_father["settings_type"] 														= $params["settings_type"];
		    $vg_father["settings_prefix"] 														= $params["settings_prefix"] . $vg_father["ID_vgallery"];

		    $vg_father["user_path"] 															= "/" . stripslash($father["contest"] . $father["relative_path"]); // "/" . stripslash($db->getField("contest", "Text", true) . $db->getField("relative_path", "Text", true));
		    $vg_father["permalink"] 															= "/" . stripslash($father["contest"] . $father["relative_path"]);

		    $vg_father["enable_user_sort"] 														= $params["enable_user_sort"];
		    if ($vg_father["enable_user_sort"]) {
				$vg_father["sort_default"] 														= $params["sort_default"];
				$vg_father["sort_method"] 														= $params["sort_method"];
		    }

		    $vg_father["allow_insert"] 															= !$vg_father["automatic_selection"];
		    $vg_father["allow_insert_dir"] 														= false;

		    $vg_father["permission"]["visible"] 												= true;
		    $vg_father["permission"]["owner"] 													= false;
		    
		    $vg_father["enable_multilang_visible"] 												= !($params["publishing"]["src"] == "anagraph");
		} else {
			return null;
		}
    } else {
		$vg_father["src"]["type"] = "vgallery";

		switch ($vg_father["src"]["type"]) {
		    case "vgallery":
			$vg_father["src"]["table"] = "vgallery_nodes";
			$vg_father["src"]["block"]["path"] = "";
			break;
		    case "gallery":
			$vg_father["src"]["table"] = "files";
			$vg_father["src"]["block"]["path"] = cache_get_page_by_id("gallery");
			break;
		    default:
			$vg_father["src"]["table"] = $vg_father["src"]["table"];
			$vg_father["src"]["block"]["path"] = cache_get_page_by_id($vg_father["src"]["type"]);
		}
		
		if ($params["vgallery_name"]) {
			if(!$globals->data_storage["vgallery"][$vg_father["user_path"]]) {
		    	$sSQL = "SELECT 
							vgallery_nodes.`ID` 											AS `ID`
							, vgallery_nodes.`name`											AS `name`
							, vgallery_nodes.`parent`										AS `parent`
							, vgallery_nodes.`ID_vgallery` 									AS `ID_vgallery`
							, vgallery_nodes.`ID_type` 										AS `ID_type`
							, vgallery.`name` 												AS `vgallery_name`
							, vgallery_type.`name` 											AS `vgallery_type`
							, " . ($params["is_dir"] !== NULL 
								? $db->toSql($params["is_dir"]) 
								: "vgallery_nodes.is_dir"
		    				) . "															AS `is_dir`
				            , vgallery_nodes.owner 											AS `owner`
							, vgallery.`limit_level`										AS `limit_level`
							, vgallery.`limit_type`											AS `limit_type`
							, vgallery.`enable_ecommerce`									AS `enable_ecommerce`
							, vgallery.`use_pricelist_as_item_thumb`						AS `use_pricelist_as_item_thumb`
							, vgallery.`use_pricelist_as_item_detail`						AS `use_pricelist_as_item_detail`
				            , vgallery.`enable_multilang_visible` 							AS `enable_multilang_visible`
				            , vgallery.`enable_multi_cat` 									AS `enable_multi_cat`
							, vgallery.drag_sort_node_enabled								AS `sort_fixed`
							, vgallery_nodes.`class`										AS `class`
							, vgallery_nodes.`highlight`									AS `highlight_container`
							, vgallery_nodes.`highlight_ID_image`							AS `highlight_image`
							, vgallery_nodes.`highlight_ID_image_md`						AS `highlight_image_md`
							, vgallery_nodes.`highlight_ID_image_sm`						AS `highlight_image_sm`
							, vgallery_nodes.`highlight_ID_image_xs`						AS `highlight_image_xs`
							, vgallery_nodes.tags 											AS `tags`
                            , vgallery_nodes.`referer`                                      AS `referer`
							" . (OLD_VGALLERY
								? "
									, vgallery_nodes.visible 								AS `visible`
									, (SELECT GROUP_CONCAT(CONCAT(vgallery_fields.name, ':::', vgallery_rel_nodes_fields.description) ORDER BY vgallery_fields.name SEPARATOR '|@|')
									        FROM  vgallery_rel_nodes_fields
									            INNER JOIN  vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
									        WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
									            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
									            AND  vgallery_fields.name IN(" . implode(",", process_vgallery_fields_system(array('keywords', 'meta_description', 'meta_title', 'meta_title_alt', 'permalink_parent', 'smart_url'))) . ")
									        ORDER BY vgallery_fields.name
									) 														AS `meta` 
								"
								: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
									? "
										, vgallery_nodes.permalink							AS permalink
										, vgallery_nodes.keywords							AS keywords
										, vgallery_nodes.meta_description					AS meta_description
										, vgallery_nodes.meta_title							AS meta_title
										, vgallery_nodes.meta_title_alt						AS meta_title_alt

                                        , vgallery_nodes.`meta_robots`                      AS `robots`
                                        , vgallery_nodes.`meta_canonical`                   AS `canonical`
                                        , vgallery_nodes.`meta`                             AS `meta`
                                        , vgallery_nodes.`httpstatus`                       AS `httpstatus`

										, vgallery_nodes.`parent`							AS permalink_parent
										, vgallery_nodes.name								AS smart_url
										, vgallery_nodes.visible 							AS `visible`			    

									"
									: "
										, vgallery_nodes_rel_languages.permalink			AS permalink
										, vgallery_nodes_rel_languages.keywords				AS keywords
										, vgallery_nodes_rel_languages.meta_description		AS meta_description
										, vgallery_nodes_rel_languages.meta_title			AS meta_title
										, vgallery_nodes_rel_languages.meta_title_alt		AS meta_title_alt

                                        , vgallery_nodes_rel_languages.`meta_robots`        AS `robots`
                                        , vgallery_nodes_rel_languages.`meta_canonical`     AS `canonical`
                                        , vgallery_nodes_rel_languages.`meta`               AS `meta`
                                        , vgallery_nodes_rel_languages.`httpstatus`         AS `httpstatus`

										, vgallery_nodes_rel_languages.permalink_parent		AS permalink_parent
										, vgallery_nodes_rel_languages.smart_url			AS smart_url
										, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
											? " vgallery_nodes_rel_languages.visible "
											: " vgallery_nodes.visible "
										) . "												AS `visible`
									"
								)
							) . "
							, " . ($mode != "thumb" && !AREA_SHOW_ECOMMERCE && USE_CART_PUBLIC_MONO 
								? "( SELECT ecommerce_order_detail.ID
							        FROM ecommerce_order_detail
			        					INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
							        WHERE ecommerce_order_detail.ID_items = vgallery_nodes.ID
			        					AND ecommerce_order_detail.tbl_src = 'vgallery_nodes'
			        					AND ecommerce_order.ID_user_cart = " . $db->toSql(get_session("UserNID"), "Number") . "
										AND ecommerce_order.cart_name = " . $db->toSql(ffCommon_url_rewrite(get_session("UserID"))) . " AND ecommerce_order.wishlist_archived = 0
										AND ecommerce_order.is_cart > 0 )" 
								: "''"
							) . "															AS `is_wishlisted`
							, " . ($mode != "thumb" && AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
								? "IF(vgallery.enable_ecommerce > 0
                    					, IF(vgallery.use_pricelist_as_item_detail > 0
                    						, IFNULL( 
                    							, (SELECT ecommerce_pricelist.actual_qta
                    								FROM ecommerce_settings
														INNER JOIN ecommerce_pricelist ON ecommerce_settings.ID = ecommerce_pricelist.ID_ecommerce_settings
                    								WHERE ecommerce_settings.ID_items = vgallery_nodes.ID	
                    							)
                    							, 1
                    						)
                    						, IFNULL( 
                    							(SELECT ecommerce_settings.actual_qta
                    								FROM ecommerce_settings
                    								WHERE ecommerce_settings.ID_items = vgallery_nodes.ID	
                    							)
                    							, 1
                    						)
                    					)
                    					, 1
					                )" 
					            : "1"
					        ) . "														AS `available`
							, vgallery_nodes.`created`									AS `created`
							, vgallery_nodes.`last_update`								AS `last_update`
							, vgallery_nodes.`published_at`								AS `published_at`
						FROM vgallery_nodes
							INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
							INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_nodes.ID_type
							" . (OLD_VGALLERY
								? ""
								: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
									? ""
									: " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
											AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
								)
							) . "
						WHERE vgallery_nodes.public = 0
							AND vgallery.public = 0 "
 							. ($vg_father["permalink"] && $vg_father["permalink"] != "/" //alla root il permalink nn e valorizzato
			                    ? (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                    				? " AND vgallery_nodes.permalink = " . $db->toSql($vg_father["permalink"])
                    				: " AND vgallery_nodes_rel_languages.permalink = " . $db->toSql($vg_father["permalink"])
			                    )
			                    : " AND vgallery_nodes.parent = " . $db->toSql(ffCommon_dirname($vg_father["user_path"])) . "
									AND vgallery_nodes.name = " . $db->toSql(basename($vg_father["user_path"]))
			                ) 					
							. (OLD_VGALLERY
									? " GROUP BY vgallery_nodes.ID"
									: ""
							)
						. " ORDER BY vgallery_nodes.visible DESC, vgallery_nodes.ID";

					/*
	" . (OLD_VGALLERY 
							? " AND vgallery_nodes.parent = " . $db->toSql(ffCommon_dirname($vg_father["user_path"])) . "
								AND vgallery_nodes.name = " . $db->toSql(basename($vg_father["user_path"]))
							: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
								? " AND vgallery_nodes.permalink = " . $db->toSql($vg_father["permalink"])
								: " AND vgallery_nodes_rel_languages.permalink = " . $db->toSql($vg_father["permalink"]) 
							)
						) . "
						*/				
					
					/*
 						AND IF(vgallery_rel_nodes_fields.ID_fields = " . process_vgallery_fields_system("permalink_parent") . "
 							, vgallery_rel_nodes_fields.description = " . $db->toSql(ffCommon_dirname($params["user_path"])) . "
 							, 0	
 						)
						AND IF(vgallery_rel_nodes_fields.ID_fields = " . process_vgallery_fields_system("smart_url") . "
 							, vgallery_rel_nodes_fields.description = " . $db->toSql(basename($params["user_path"])) . "
 							, 0	
 						) 				
					
					*/
					//echo $sSQL;
			    $db->query($sSQL);
			    if ($db->nextRecord()) {
			    	do {
			    		$globals->data_storage["vgallery"][stripslash($db->record["parent"]) . "/" . $db->record["name"]] = $db->record;
					} while($db->nextRecord());
				}
			}
			
			$father = $globals->data_storage["vgallery"][$vg_father["user_path"]];
			if($father) {
				$ID_node 																	= $father["ID"]; //$db->getField("ID_node", "Number", true);										*****n

				$vg_father["ID_vgallery"] 													= $father["ID_vgallery"]; //$db->getField("ID_vgallery", "Number", true);
				$vg_father["ID_node"] 														= $ID_node; //$db->getField("ID_node", "Number", true);    									
				$vg_father["vgallery_name"] 												= $father["vgallery_name"]; //$db->getField("vgallery_name", "Text", true);								*****v
				$vg_father["vgallery_class"] 												= ffCommon_url_rewrite($father["vgallery_name"]); //ffCommon_url_rewrite($db->getField("vgallery_class", "Text", true));		*****v
				$vg_father["vgallery_type"] 												= $father["vgallery_type"]; //$db->getField("vgallery_type", "Text", true);								*****v

				$vg_father["is_dir"] 														= $father["is_dir"]; //$db->getField("is_dir", "Number", true);
				$vg_father["limit_level"] 													= $father["limit_level"]; //$db->getField("limit_level", "Number", true);
				$vg_father["limit_type"] 													= explode(",", $father["limit_type"]); //explode(",", $db->getField("limit_type", "Text", true));
				$vg_father["enable_ecommerce"] 												= $father["enable_ecommerce"]; //$db->getField("enable_ecommerce", "Number", true);						*****v
				$vg_father["use_pricelist_as_item"] 										= $father["use_pricelist_as_item_" . $mode]; //$db->getField("use_pricelist_as_item", "Number", true);			*****v
				$vg_father["enable_multilang_visible"] 										= $father["enable_multilang_visible"]; //$db->getField("enable_multilang_visible", "Number", true);		*****v
				$vg_father["enable_multi_cat"] 												= $father["enable_multi_cat"]; //$db->getField("enable_multi_cat", "Number", true);						*****v
				$vg_father["enable_user_sort"] 												= $params["enable_user_sort"];
				if ($vg_father["enable_user_sort"]) {
				    $vg_father["sort_default"] 												= $params["sort_default"];
				    $vg_father["sort_method"] 												= $params["sort_method"];
				} else {
					$vg_father["sort_fixed"] 												= $father["sort_fixed"]; //$db->getField("sort_fixed", "Number", true);									*****v
				}

				$ID_owner 																	= $father["owner"]; //$db->getField("owner", "Number", true);
				$is_vgallery_main 															= basename($vg_father["user_path"]) == $vg_father["vgallery_name"];

				if(OLD_VGALLERY) {
					$meta 																	= $father["meta"]; //$db->getField("meta", "Text", true);		//nn gestito
					if (strlen($meta)) {
						$arrMeta = explode("|@|", $meta);
						if (is_array($arrMeta) && count($arrMeta)) {
							$arrFieldsIDtoName = process_vgallery_fields_system(null, true);
							foreach ($arrMeta AS $arrMeta_value) {
								$tmpMeta = explode(":::", $arrMeta_value);

								switch ($arrFieldsIDtoName[$tmpMeta[0]]) {
									case "smart_url":
										$father["smart_url"] = $tmpMeta[1];
									break;
									case "permalink_parent":
										$father["permalink_parent"] = $tmpMeta[1];
									break;
									case "keywords":
										$father["keywords"] = $tmpMeta[1];						
										$meta_keywords = $tmpMeta[1];
									break;
									case "meta_description":
										$father["meta_description"] = $tmpMeta[1];						
										$meta_description = $tmpMeta[1];
									break;
									case "meta_title":
										$father["meta_title"] = $tmpMeta[1];
										$meta_title = $tmpMeta[1];
									break;
									case "meta_title_alt":
										$father["meta_title_alt"] = $tmpMeta[1];
										$meta_title_alt = $tmpMeta[1];
									break;
								}
							}

							$vg_father["seo"]["title"] = ($meta_title ? $meta_title : $meta_title_alt);
							$vg_father["seo"]["title_header"] = ($meta_title_alt ? $meta_title_alt : $meta_title);

							if (strlen($meta_description)) {
								$vg_father["seo"]["meta"]["description"][] = $meta_description;
							}
							if (strlen($meta_keywords)) {
								$vg_father["seo"]["meta"]["keywords"][] = $meta_keywords;
							}
						}
					}
				} else {
					$vg_father["seo"]["title"] 																	= ($father["meta_title"] 
																													? $father["meta_title"] 
																													: $father["meta_title_alt"]
																												);
					$vg_father["seo"]["title_header"] 															= ($father["meta_title_alt"] 
																													? $father["meta_title_alt"] 
																													: $father["meta_title"]
																												);
					if (strlen($father["meta_description"])) {
						$vg_father["seo"]["meta"]["description"][] 												= $father["meta_description"];
					}
					if (strlen($father["keywords"])) {
						$vg_father["seo"]["meta"]["keywords"][] 												= $father["keywords"];
					}
                    
                    if ($father["meta"]) {
                        $arrMeta = explode("\n", $father["meta"]);
                        foreach($arrMeta AS $meta_data) {
                            $arrMetaData = explode("=", $meta_data);
                            $vg_father["seo"]["meta"][$arrMetaData[0]]                                          = $arrMetaData[1];
                        }
                    }
                    if ($father["robots"])
                        $seo["meta"]["robots"]                                                                  = $father["robots"];
                    if ($father["canonical"])
                        $vg_father["seo"]["canonical"]                                                          = $father["canonical"];

                    if ($father["httpstatus"])
                        $vg_father["seo"]["httpstatus"]                                                         = $father["httpstatus"];                    
				}

				if (strlen($vg_father["seo"]["title"]) && !$is_vgallery_main && $params["enable_title_seo"]) {
				    $vg_father["title"] 																		= $vg_father["seo"]["title"];
				} elseif($vg_father["enable_title"]) {
				    $vg_father["title"] 																		= ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9\_]/', '', $params["unic_id"] . (strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_name"] : "") . ((count(explode("/", $vg_father["user_path"])) - 1) > 1 && !strlen($vg_father["vgallery_name"]) ? "_" . $vg_father["vgallery_type"] : "")) . "_title");
				} elseif($params["title"]) {
					$vg_father["title"] 																		= $params["title"];
				}

				if($vg_father["enable_sub_title"]) {
					if(isset($params["description"]))
						$vg_father["description"] 																= $params["description"];
					elseif(!$is_vgallery_main)
						$vg_father["description"] 																= $father["meta_description"];
				}
					
				if ((count(explode("/", $vg_father["settings_path"])) - 1) <= $vg_father["limit_level"]) {
					$vg_father["allow_insert"] 																	= true;
					if ($vg_father["is_dir"] && (count(explode("/", $vg_father["settings_path"])) <= $vg_father["limit_level"]))
						$vg_father["allow_insert_dir"] 															= true;
					else 
						$vg_father["allow_insert_dir"] 															= false;
				} else {
				    $vg_father["allow_insert"] 																	= false;
				    $vg_father["allow_insert_dir"] 																= false;
				}

				if ($mode != "thumb") {
				    if ($params["wishlist"] !== null && $params["ID_cart_detail"] > 0) {
						$unic_id_node 																			= $ID_node . "-" . $params["ID_cart_detail"];

						$vg_father["wishlist"]["nodes"][$ID_node] 												= $ID_node;

						$vg_father["nodes"][$unic_id_node]["ID_cart_detail"] 									= $params["ID_cart_detail"];
						$vg_father["nodes"][$unic_id_node]["pricelist"] 										= null;
				    } else {
						if ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"]) {
						    $ID_pricelist 																		= $father["ID_pricelist"]; //$db->getField("ID_pricelist", "Number", true); //da arricchire le query
						    $unic_id_node 																		= $ID_node . "-" . $ID_pricelist;

						    //da verificare se serve
						    //$pricelist["nodes"][$ID_node] = $ID_node;

						    $vg_father["nodes"][$unic_id_node]["pricelist"]["ID"] 								= $ID_pricelist;
						    $vg_father["nodes"][$unic_id_node]["pricelist"]["range"]["since"] 					= $father["pricelist_since"]; //$db->getField("pricelist_since", "Number", true);
						    $vg_father["nodes"][$unic_id_node]["pricelist"]["range"]["to"] 						= $father["pricelist_to"]; //$db->getField("pricelist_to", "Number", true);
						} else {
						    $unic_id_node = $ID_node;

						    $vg_father["nodes"][$unic_id_node]["pricelist"] 									= null;
						}

						$vg_father["nodes"][$unic_id_node]["ID_cart_detail"] 									= null;
				    }

				    $vg_father["nodes"][$unic_id_node]["preload"][$vg_father["src"]["table"]] 					= $father;

				    $vg_father["nodes"][$unic_id_node]["ID"] 													= $ID_node;
				    $vg_father["nodes"][$unic_id_node]["parent"] 												= $father["parent"]; //$db->getField("parent", "Text", true);
				    $vg_father["nodes"][$unic_id_node]["name"] 													= $father["name"]; //$db->getField("name", "Text", true);
				    $vg_father["nodes"][$unic_id_node]["ID_type"] 												= $father["ID_type"]; //$db->getField("ID_type", "Number", true);
				    $vg_father["nodes"][$unic_id_node]["type"] 													= $father["vgallery_type"]; //$db->getField("vgallery_type", "Text", true);						******t
				    $vg_father["nodes"][$unic_id_node]["is_dir"] 												= $father["is_dir"]; //$db->getField("is_dir", "Number", true);
				    //$vg_father["nodes"][$unic_id_node]["data_type_publish"] 									= "";
				    //$vg_father["nodes"][$unic_id_node]["vgallery_name"] 										= $db->getField("vgallery_name", "Text", true);
					$vg_father["nodes"][$unic_id_node]["created"] 												= $father["created"]; 
					$vg_father["nodes"][$unic_id_node]["last_update"] 											= $father["last_update"]; 
					$vg_father["nodes"][$unic_id_node]["published"] 											= $father["published_at"]; 
					$vg_father["nodes"][$unic_id_node]["owner"] 												= $ID_owner;
					$vg_father["seo"]["owner"] 																	= $ID_owner;
				    $vg_father["nodes"][$unic_id_node]["class"]													= $father["class"]; //$db->getField("class", "Text", true);
				    $vg_father["nodes"][$unic_id_node]["highlight"]["container"] 								= $father["highlight_container"]; //$db->getField("highlight_container", "Text", true);

				    $vg_father["nodes"][$unic_id_node]["highlight"]["image"]["src"] 							= get_image_properties_by_grid_system(
				    																								$father["highlight_image"] //$db->getField("highlight_image", "Number", true)
				    																								, $father["highlight_image_md"] //$db->getField("highlight_image_md", "Number", true)
				    																								, $father["highlight_image_sm"] //$db->getField("highlight_image_sm", "Number", true)
				    																								, $father["highlight_image_xs"] //$db->getField("highlight_image_xs", "Number", true)
																											    );
																											    
				    $vg_father["nodes"][$unic_id_node]["is_wishlisted"] 										= $father["is_wishlisted"]; //$db->getField("is_wishlisted", "Number", true);					******e
				    $vg_father["nodes"][$unic_id_node]["available"] 											= $father["available"]; //$db->getField("available", "Number", true);							******e
				    //$vg_father["nodes"][$unic_id_node]["enable_multilang_visible"] 							= $db->getField("enable_multilang_visible", "Number", true);

				    $vg_father["nodes"][$unic_id_node]["tags"] 													= $father["tags"]; //$db->getField("tags", "Text", true);
                    $vg_father["seo"]["tags"]["primary"]                                         				= $father["tags"];
                    $vg_father["nodes"][$unic_id_node]["referer"]                                               = $father["referer"];
				    
					if (array_key_exists("permalink", $father)) {
						$vg_father["nodes"][$unic_id_node]["permalink"] 										= $father["permalink"];
						$vg_father["nodes"][$unic_id_node]["smart_url"] 										= basename($father["permalink"]);
						$vg_father["nodes"][$unic_id_node]["permalink_parent"] 									= ffcommon_dirname($father["permalink"]);
						$father["permalink_parent"] 															= $vg_father["nodes"][$unic_id_node]["permalink_parent"];
					} else {
						if (array_key_exists("smart_url", $father))
						    $vg_father["nodes"][$unic_id_node]["smart_url"] 									= $father["smart_url"];
						if (array_key_exists("parent", $father))
							$vg_father["nodes"][$unic_id_node]["permalink_parent"] 								= $father["parent"];
					}
				    
				    if (array_key_exists("meta_title", $father))
						$vg_father["nodes"][$unic_id_node]["meta"]["title"] 									= $father["meta_title"];
				    if (array_key_exists("meta_title_alt", $father))
						$vg_father["nodes"][$unic_id_node]["meta"]["title_h1"] 									= $father["meta_title_alt"];
				    if (array_key_exists("meta_description", $father))
						$vg_father["nodes"][$unic_id_node]["meta"]["description"] 								= $father["meta_description"];
				    if (array_key_exists("keywords", $father))
						$vg_father["nodes"][$unic_id_node]["meta"]["keywords"] 									= $father["keywords"];
				}

				$vg_father["permission"]["visible"] 															= $father["visible"]; //$db->getField("visible", "Number", true);
				$vg_father["permission"]["owner"] 																= $ID_owner;
		    } else {
	    		return null;
		    }
		}
    }

	if($vg_father["allow_insert_dir"] === null)
		$vg_father["allow_insert_dir"] = true;
	if($vg_father["allow_insert"] === null)
		$vg_father["allow_insert"] = true;
	if($vg_father["allow_edit"] === null)
		$vg_father["allow_edit"] = true;
	
	$vg_father["ID_layout"] = $params["ID_layout"];
    $vg_father["type"] = $params["name"];
    $vg_father["unic_id"] = $params["unic_id"];
    //$vg_father["unic_id_lower"] = $params["unic_id_lower"];
    $vg_father["group"] = $params["group"];

    if ($params["search"]) { 
		if (is_array($params["search"])) {
		    $vg_father["search"] = $params["search"];
		} else {
		    $vg_father["search"]["markable"] = true;
		    $vg_father["search"]["limit"] = true;
		    $vg_father["search"]["settings_path"] = false; //per ricordare che esiste
		    $vg_father["search"]["term"] = $params["search"];
		}
    }

    $vg_father["publishing"] = $params["publishing"];
    $vg_father["wishlist"] = $params["wishlist"];

    //echo $vg_father["settings_path"] . " as " . $vg_father["settings_layout"];
	$file_properties = get_file_properties($vg_father["settings_path"], $vg_father["settings_type"], $mode, $vg_father["settings_layout"]);

	if(!$params["template_skip_hide"] && $file_properties["hide"]) {
		$vg_father["hide_template"] = true;
        $skip_navigation = true;
        $skip_social = true;
	} 
	
	if(isset($vg_father["limit"]["elem"]) && count($vg_father["limit"]["elem"]) <= $file_properties["rec_per_page"])
	    $skip_navigation = true;

	$vg_father["sort_default"] = $file_properties["sort"];
	$vg_father["sort_method"] = $file_properties["sort_method"];
	if(!$vg_father["sort_method"])
		$vg_father["sort_method"] = "DESC";

	/**
	 * Seo Social
	 */
	if (!$skip_social /*&& !isset($globals->seo[$seo_mode])*/ && is_array($file_properties["social"]) && count($file_properties["social"])) {
		$vg_father["seo"]["meta"] = array_replace($vg_father["seo"]["meta"], $file_properties["social"]["done"]);
		$vg_father["seo"]["meta_todo"] = $file_properties["social"]["todo"];
	}
	$vg_father["properties"]["ID_layout"] = $file_properties["ID_layout"];
	
	$vg_father["properties"]["image"] = $file_properties["image"];

/*
	$vg_father["properties"]["ID_image"] = $file_properties["ID_image"];
	$vg_father["properties"]["image_size"] = $file_properties["image_size"];
	$vg_father["properties"]["image_detail"] = $file_properties["image_detail"];
	$vg_father["properties"]["plugin_js"] = $file_properties["display_view_mode"];

		$vg_father["properties"]["image_name"] = $file_properties["image_name"];
		$vg_father["properties"]["image_width"] = $file_properties["fix_x"];
		$vg_father["properties"]["image_height"] = $file_properties["fix_y"];

*/
	$vg_father["properties"]["plugin"] = $file_properties["plugin"];

	$vg_father["properties"]["thumb_per_row"] = ($file_properties["item_size"] ? $file_properties["item_size"][count($file_properties["item_size"]) - 1] : 1);
	if (!$vg_father["properties"]["thumb_per_row"])
		$vg_father["properties"]["thumb_per_row"] = 1;

	
	$vg_father["template"]["path"] = $params["tpl_path"];
	if(!$vg_father["template"]["path"])
		$vg_father["template"]["path"] = "/tpl/vgallery";

	if($params["template_default_name"]) {
		$vg_father["template"]["name"] = $params["template_default_name"];
		$vg_father["template"]["suffix"] = "";
	} else {
		$vg_father["template"]["name"] = $file_properties["container_mode"];
		$vg_father["template"]["suffix"] = $params["template_suffix"];
	}
	$vg_father["template"]["layout"] = $file_properties["ID_layout"];

	if ($params["framework_css"] !== false) {
		$framework_type = ($mode == "thumb"
			? "items"
			: "container"
		);
	
		$vg_father["template"]["framework"] = array(
		    $framework_type => array(
				"class" => $file_properties["default_class"]
				, "fluid" => $file_properties["fluid"]
				, "grid" => $file_properties["default_grid"]
			)
			, "wrap" => $file_properties["wrap"]
			, "extra" => array(
				"grid" => $file_properties["default_extra"]
				, "location" => $file_properties["default_extra_location"]
				, "class" => $file_properties["default_extra_class"]				
			)
		);

		if(is_array($params["framework_css"]))
			$vg_father["template"]["framework"] = array_replace($vg_father["template"]["framework"], $params["framework_css"]);
		
		if(!is_array($vg_father["properties"]["image"]["fields"]))
			unset($vg_father["template"]["framework"]["extra"]);
		
		if(check_function("get_class_by_grid_system"))
			$vg_father["template"] = get_class_by_grid_system_def($vg_father["template"]["framework"], $vg_father["template"]);

		//$vg_father["template"]["path"] = $vg_father["template"]["path"] . "/new";
		if (strpos($vg_father["template"]["name"], "_") !== false)
			$vg_father["template"]["name"] = substr($vg_father["template"]["name"], 0, strpos($vg_father["template"]["name"], "_"));
	}

	if ($mode == "thumb") 
    {
	    if($vg_father["search"]) {
		    $nav_params["search"] 	= ($vg_father["search"]["available_terms"] 
            							? $vg_father["search"]["available_terms"] 
            							: $vg_father["search"]["terms"]
            						); 
			if($vg_father["search"]["filter"] && $globals->filter["first_letter"]) {
            	$nav_params["ffl"] = $globals->filter["first_letter"];
	        }            						
		}
	    if($vg_father["sort"])
	        $nav_params["sort"] 	= $vg_father["sort"]; 

    	 if(!$skip_navigation)
		 {
			/**
			 * Page Navigator
			 */
			$vg_father["navigation"]["location"] = $file_properties["pagenav_location"];
	        $vg_father["navigation"]["infinite"] = $file_properties["infinite"]; 
			
			$vg_father["navigation"]["obj"] = ffPageNavigator::factory($cm->oPage, FF_DISK_PATH, FF_SITE_PATH, null, $cm->oPage->theme);
			$vg_father["navigation"]["obj"]->id = $params["unic_id"];
	        $vg_father["navigation"]["obj"]->prefix = "";
	        $vg_father["navigation"]["obj"]->infinite = $vg_father["navigation"]["infinite"]; //next or prev
	        
		    if($params["navigation"]["page"]) {
		        $vg_father["navigation"]["page"] = intval($params["navigation"]["page"]);
		    } else {
		        $vg_father["navigation"]["page"] = 1;
		    }

		    if($params["navigation"]["rec_per_page"]) {
		        $vg_father["navigation"]["rec_per_page"] = intval($params["navigation"]["rec_per_page"]);
		    } else {
		        $vg_father["navigation"]["rec_per_page"] = $file_properties["rec_per_page"];
		    }

			$vg_father["navigation"]["obj"]->doAjax = true; 
	        $vg_father["navigation"]["obj"]->callback = "ff.cms.getBlock";

	        if($nav_params)
        		$vg_father["navigation"]["obj"]->callback_params = json_encode($nav_params);
		    
			$vg_father["navigation"]["obj"]->PagePerFrame = $file_properties["npage_per_frame"];
			$vg_father["navigation"]["obj"]->nav_selector_elements = array(floor($vg_father["navigation"]["rec_per_page"] / 2), $vg_father["navigation"]["rec_per_page"], $vg_father["navigation"]["rec_per_page"] * 2);
			$vg_father["navigation"]["obj"]->nav_selector_elements_all = $file_properties["rec_per_page_all"];
			$vg_father["navigation"]["obj"]->display_prev = $file_properties["direction_arrow"];
			$vg_father["navigation"]["obj"]->display_next = $file_properties["direction_arrow"];
			$vg_father["navigation"]["obj"]->display_first = $file_properties["direction_arrow"];
			$vg_father["navigation"]["obj"]->display_last = $file_properties["direction_arrow"];
			$vg_father["navigation"]["obj"]->with_frames = $file_properties["frame_arrow"];
			$vg_father["navigation"]["obj"]->with_choice = $file_properties["custom_page"];
			$vg_father["navigation"]["obj"]->with_totelem = $file_properties["tot_elem"];
			$vg_father["navigation"]["obj"]->nav_display_selector = $file_properties["frame_per_page"];
			$vg_father["navigation"]["obj"]->page = $vg_father["navigation"]["page"];
			$vg_father["navigation"]["obj"]->records_per_page = $vg_father["navigation"]["rec_per_page"];
			$vg_father["navigation"]["obj"]->num_rows = null;
			$vg_father["navigation"]["tot_page"] = null;
			//}
		}

		if(!$params["navigation"]["skip_alphanum"] && $params["name"] != "learnmore" && $file_properties["alphanum"]) 
    	{
    		$vg_father["navigation"]["alphanum"]["location"] = $file_properties["alphanum"]; 
    		$vg_father["navigation"]["alphanum"]["obj"] = ffTemplate::factory(get_template_cascading($vg_father["user_path"], "alphanum.html", "/tpl/addon"));
			$vg_father["navigation"]["alphanum"]["obj"]->load_file("alphanum.html", "main");   	    	 
			$vg_father["navigation"]["alphanum"]["obj"]->set_var("menu_class", cm_getClassByFrameworkCss("navbar", "bar"));  
			$vg_father["navigation"]["alphanum"]["obj"]->set_var("active_class" . ($nav_params["ffl"] ? "_" . $nav_params["ffl"] : ""), ' class="' . cm_getClassByFrameworkCss("active", "menu") . '"');
			$vg_father["navigation"]["alphanum"]["obj"]->set_var("user_path", FF_SITE_PATH . $vg_father["user_path"]);
		}

	}

    $vg_father["enable_found_rows"] = !ENABLE_STD_PERMISSION && !$skip_navigation && !$vg_father["limit"]["elem"] && $vg_father["navigation"]["location"];

    /* Check Permission */
    $vg_father["permission"]["is_owner"] = false;
    if (ENABLE_STD_PERMISSION && $vg_father["type"] == "vgallery") {
		if (check_function("get_file_permission"))
		    $file_permission = get_file_permission($vg_father["user_path"], "vgallery_nodes");
	//print_r($file_permission);
		$vg_father["permission"]["groups"] = $file_permission["groups"];

		$vg_father["permission"]["owner"] = $file_permission["owner"];
		if ($vg_father["permission"]["owner"] > 0 && ($vg_father["permission"]["owner"] === get_session("UserNID"))) {
		    $vgallery_user_path = get_session("vgallery_user_path");
		    if (is_array($vgallery_user_path) && array_key_exists($vg_father["vgallery_name"], $vgallery_user_path))
			$vg_father["permission"]["is_owner"] = true;
		}

		if ($is_vgallery_main) {
		    $enable_multilang = false;
		} else {
		    $enable_multilang = ($vg_father["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT);
		}

		$vg_father["permission"]["visible"] = ($params["template_skip_hide"]
			? true
			: check_mod($file_permission, 1, $enable_multilang, AREA_VGALLERY_SHOW_MODIFY)
		);
    } else {
		if ($vg_father["permission"]["owner"] == get_session("UserNID"))
		    $vg_father["permission"]["is_owner"] = true;

		if (!$vg_father["permission"]["visible"] && AREA_VGALLERY_SHOW_MODIFY) {
		    $vg_father["permission"]["visible"] = AREA_VGALLERY_SHOW_MODIFY;
		    $vg_father["warning"][] = ffTemplate::_get_word_by_code("vgallery_" . $vg_father["vgallery_name"] . "_not_visible");
		}
    }

    if ($vg_father["type"] != "learnmore" && $vg_father["enable_title"] && strlen($vg_father["title"])) {
	    if ($vg_father["wishlist"] !== NULL) {
	        $vg_father["wishlist"]["expire"] = ($vg_father["wishlist"]["expire"] + (60 * 60 * 24)) - (time() + (60 * 60 * 24 * CART_PUBLIC_MONO_RUSH_EXPIRE_DAY));
	        if ($vg_father["wishlist"]["expire"] > 0) {
		    $vg_father["wishlist"]["date_info"] = '<div class="wishlist-expire">' . ffTemplate::_get_word_by_code("wishlist_expire_to_pre") . " " . (ceil($vg_father["wishlist"]["expire"] / (60 * 60 * 24))) . " " . ffTemplate::_get_word_by_code("wishlist_expire_to_post") . '</div>';
		    $vg_father["wishlist"]["disable_addtocart"] = false;
	        } else {
		    $vg_father["wishlist"]["date_info"] = '<div class="wishlist-expire">' . ffTemplate::_get_word_by_code("wishlist_expired") . '</div>';
		    $vg_father["wishlist"]["disable_addtocart"] = true;
	        }
	    }
    }
    //need for research data in rel nodes fields
    if (isset($_REQUEST["frmAction"]) && strpos($_REQUEST["frmAction"], $vg_father["unic_id"]) !== false) {
	    $req_params = array("page" => $vg_father["navigation"]["page"] 
	        , "rec_per_page" => $vg_father["navigation"]["rec_per_page"]
	        , "sort" => $vg_father["sort_default"]
	        , "sort_type" => $vg_father["sort_method"]
	        , "search" => $vg_father["search"]["encoded_params"]
	    );
	    if ($req_params["page"] > 1 || strlen($req_params["sort"]) || strlen($req_params["search"])) {
	        $vg_father["request_params"] = implode(",", $req_params);
	    }
    }
    
    if($vg_father["ID_node"])
	    $vg_father["seo"]["ID"] = $vg_father["ID_node"];
    
    return $vg_father;
}

function process_vgallery_admin_bar($vg_father, $layout) {
    $globals = ffGlobals::getInstance("gallery");
    $admin_menu = null;

	$searched = ($vg_father["search"] !== null
		? true   
		: false
	); 
    if ($vg_father["publishing"] === null) {
		if ($vg_father["is_dir"]) {
		    $allow_edit = AREA_VGALLERY_DIR_SHOW_MODIFY;
		    $allow_delete = AREA_VGALLERY_DIR_SHOW_DELETE;
		} else {
		    $allow_edit = AREA_VGALLERY_SHOW_MODIFY;
		    $allow_delete = AREA_VGALLERY_SHOW_DELETE;
		}

		if (
			(
			( $vg_father["wishlist"] === null && check_mod($vg_father["permission"], 2)
			) || $vg_father["wishlist"] !== null
			) && (
			(AREA_VGALLERY_DIR_SHOW_ADDNEW && $vg_father["allow_insert_dir"]) || (AREA_VGALLERY_SHOW_ADDNEW && $vg_father["allow_insert"]) || ($allow_edit && !$searched && $vg_father["type"] != "learnmore") || ($allow_delete && !$searched && $vg_father["type"] != "learnmore") || (AREA_PROPERTIES_SHOW_MODIFY && strlen($vg_father["vgallery_name"])) || (AREA_ECOMMERCE_SHOW_MODIFY && $vg_father["enable_ecommerce"] && !$searched && $vg_father["wishlist"] === null) || (AREA_LAYOUT_SHOW_MODIFY && $vg_father["type"] != "learnmore") || (AREA_SETTINGS_SHOW_MODIFY && !$searched && $vg_father["wishlist"] === null)
			)
		/* && ($vg_father["is_dir"] || $vg_father["type"] == "learnmore") */
		) {
		    if ($vg_father["wishlist"] === null && !$searched) {
				if (AREA_VGALLERY_DIR_SHOW_ADDNEW && $vg_father["allow_insert_dir"]) {
				    $admin_menu["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?type=dir" . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_father["settings_path"]);
				}

				if (AREA_VGALLERY_SHOW_ADDNEW && $vg_father["is_dir"] && $vg_father["allow_insert"]) {

				    $admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?type=node" . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_father["settings_path"]) . "&extype=" . $vg_father["settings_type"];
				}


				if ($vg_father["allow_edit"] && strtolower($vg_father["user_path"]) != "/" . strtolower($vg_father["vgallery_name"]) && $vg_father["type"] != "learnmore") {
				    if ($allow_edit && !$searched) {
						$admin_menu["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_father["ID_node"] . "&type=" . ($vg_father["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_father["user_path"]) . "&extype=" . $vg_father["settings_type"];
				    }
				    if ($allow_delete && !$searched) {
						$admin_menu["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_father["ID_node"] . "&type=" . ($vg_father["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_father["settings_path"]) . "&extype=" . $vg_father["settings_type"] . "&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/dialog");
				    }
				}
		    }

		    if (AREA_PROPERTIES_SHOW_MODIFY && !$searched) {
				$admin_menu["admin"]["fields"] = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/vgallery/type";
				if (is_array($vg_father["limit_type"]) && count($vg_father["limit_type"])) {
				    if (count($vg_father["limit_type"]) > 1)
						$admin_menu["admin"]["fields"] .= "?limit=" . urlencode(implode(",", $vg_father["limit_type"]));
				    else
						$admin_menu["admin"]["fields"] .= "/extra?keys[ID]=" . implode(",", $vg_father["limit_type"]);
				} else {
				    $admin_menu["admin"]["fields"] .= "?path=" . urlencode($vg_father["settings_path"]);
				}

				if ($vg_father["type"])
				    $admin_menu["admin"]["fields"] .= "&src=" . urlencode($vg_father["src"]["type"]);
				    
		    }

		    if (AREA_PROPERTIES_SHOW_MODIFY) {
		    	if($vg_father["settings_type"] === false)
		    		$admin_menu["admin"]["extra"] = $vg_father["settings_path"];
		    	else
					$admin_menu["admin"]["extra"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . ($vg_father["vgallery_name"] ? "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) : "") . "/properties?path=" . urlencode($vg_father["settings_path"]) . "&extype=" . $vg_father["settings_type"] . "&layout=" . $vg_father["settings_layout"] . "&src=" . urlencode($vg_father["src"]["type"]) . ($vg_father["type"] == "learnmore" ? "&skipd=1" : "");
		    }

		    if (AREA_ECOMMERCE_SHOW_MODIFY && $vg_father["enable_ecommerce"] && !$searched && $vg_father["wishlist"] === null) {
			$admin_menu["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/ecommerce/all?keys[ID]=" . $vg_father["ID_node"] . "&type=" . ($vg_father["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&path=" . urlencode($vg_father["settings_path"]) . "&extype=" . $vg_father["settings_type"];
		    }
		    if (AREA_LAYOUT_SHOW_MODIFY && !$searched && $vg_father["type"] != "learnmore") {
			$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
			$admin_menu["admin"]["layout"]["type"] = $layout["type"];
		    }
		    if (AREA_SETTINGS_SHOW_MODIFY && !$searched && $vg_father["wishlist"] === null) {
			$admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
		    }
		}
    } else {
	if ((AREA_PUBLISHING_SHOW_MODIFY || AREA_PUBLISHING_SHOW_DETAIL || AREA_PUBLISHING_SHOW_DELETE)) {
	    if (AREA_PUBLISHING_SHOW_DETAIL && $vg_father["allow_insert"]) {
		$admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/detail/" . ffCommon_url_rewrite(basename($vg_father["settings_path"])) . "?keys[ID]=" . $vg_father["publishing"]["ID"] . "&extype=" . $vg_father["settings_type"];
		/*
		  if($vg_father["automatic_selection"]) {
		  if(strlen($vg_father["vgallery_name"])) {
		  $admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?type=node" . "&vname=" . $vg_father["vgallery_name"] . "&path=" . urlencode($vg_father["settings_path"]) . "&extype=" . $vg_father["settings_type"];
		  }
		  } else {
		  $admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/detail/" . ffCommon_url_rewrite(basename($vg_father["settings_path"])) . "?keys[ID]=" . $vg_father["publishing"]["ID"] . "&extype=" . $vg_father["settings_type"];
		  }
		 */
	    }
	    if (AREA_PUBLISHING_SHOW_MODIFY) {
		$admin_menu["admin"]["modify"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/modify?keys[ID]=" . $vg_father["publishing"]["ID"] . "&extype=" . $vg_father["settings_type"];
	    }
	    if (AREA_PUBLISHING_SHOW_DELETE) {
		$admin_menu["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/modify?keys[ID]=" . $vg_father["publishing"]["ID"] . "&extype=" . $vg_father["settings_type"] . "&ret_url=" . "--encodereturl--" . "&PublishingModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/dialog");
	    }

	    if (AREA_PROPERTIES_SHOW_MODIFY) {
			$admin_menu["admin"]["extra"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/properties?path=" . $vg_father["settings_path"] . "&extype=" . $vg_father["settings_type"] . "&layout=" . $vg_father["settings_layout"] . ($vg_father["type"] == "learnmore" ? "&skipd=1" : "");
			$admin_menu["admin"]["fields"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/publishing/extra?keys[ID]=" . $vg_father["publishing"]["ID"] . "&src=" . urlencode($vg_father["src"]["type"]);
	    }


	    if (AREA_ECOMMERCE_SHOW_MODIFY) {
		$admin_menu["admin"]["ecommerce"] = "";
	    }
	    if (AREA_LAYOUT_SHOW_MODIFY) {
		$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
		$admin_menu["admin"]["layout"]["type"] = $layout["type"];
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY) {
		$admin_menu["admin"]["setting"] = ""; //"PUBLISHING";
	    }
	}
    }

    if (is_array($admin_menu)) {
		$admin_menu["admin"]["title"] = ($vg_father["mode"] == "thumb" ? $layout["title"] : ucfirst($vg_father["vgallery_name"]) . ": " . $vg_father["title"]
			);
		$admin_menu["admin"]["unic_name"] = $vg_father["unic_id"] . "-" . $vg_father["src"]["type"] . "-" . $vg_father["ID_node"] . "-" . $vg_father["allow_insert"];
		$admin_menu["admin"]["class"] = $layout["type_class"];
		$admin_menu["admin"]["group"] = $layout["type_group"];
		$admin_menu["sys"]["path"] = $vg_father["settings_path"];
		$admin_menu["sys"]["type"] = "admin_toolbar";
		//$admin_menu["sys"]["ret_url"] = $ret_url;
    }
    
    return $admin_menu;
}

function process_vgallery_admin_popup($vg_father, $vg_node, $layout) {
    $popup = null;
	$searched = ($vg_father["search"] !== null
		? true   
		: false
	);
	
    if ($vg_node["is_dir"]) {
		$allow_edit = AREA_VGALLERY_DIR_SHOW_MODIFY;
		$allow_delete = AREA_VGALLERY_DIR_SHOW_DELETE;
    } else {
		$allow_edit = AREA_VGALLERY_SHOW_MODIFY;
		$allow_delete = AREA_VGALLERY_SHOW_DELETE;
    }
    if ($vg_father["publishing"] === NULL && !$searched) {
	if ($allow_edit) {
	    $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    if ($allow_delete) {
		$popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/dialog");
	    }
	    if (AREA_PROPERTIES_SHOW_MODIFY) {
		$popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/properties?type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes" . "&layout=" . $vg_father["settings_layout"];
	    }
	    if (AREA_ECOMMERCE_SHOW_MODIFY && $vg_father["enable_ecommerce"]) {
		$popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/ecommerce/all?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY) {
		$popup["admin"]["setting"] = "";
	    }
	}
    } elseif (is_array($vg_father["publishing"])) {

	if ($allow_edit) {
	    $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    if ($allow_delete) {
		$popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/dialog");
	    }
	    if (AREA_PROPERTIES_SHOW_MODIFY) {
		$popup["admin"]["extra"] = "";
	    }
	    if (AREA_ECOMMERCE_SHOW_MODIFY && $vg_father["enable_ecommerce"]) {
		$popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/ecommerce/all?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY) {
		$popup["admin"]["setting"] = "";
	    }
	}
    } elseif ($vg_father["search"] !== NULL) {
	if ($allow_edit) {
	    $popup["admin"]["addnew"] = "";
	    $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    if ($allow_delete) {
		$popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/modify?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&src=" . urlencode($vg_father["src"]["type"]) . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/dialog");
	    }
	    if (AREA_PROPERTIES_SHOW_MODIFY) {
		$popup["admin"]["extra"] = "";
	    }
	    if (AREA_ECOMMERCE_SHOW_MODIFY && $vg_father["enable_ecommerce"]) {
		$popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_father["vgallery_name"]) . "/ecommerce/all?keys[ID]=" . $vg_node["ID"] . "&type=" . ($vg_node["is_dir"] ? "dir" : "node") . "&vname=" . $vg_father["vgallery_name"] . "&path=" . urlencode($vg_node["parent"]) . "&extype=vgallery_nodes";
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY) {
		$popup["admin"]["setting"] = "";
	    }
	}
    }

    if (is_array($popup)) {
	$popup["admin"]["unic_name"] = $vg_father["src"]["type"] . "-" . $vg_node["ID"];
	$popup["admin"]["title"] = ($vg_father["mode"] == "thumb" ? $layout["title"] : ": " . stripslash($vg_node["parent"]) . "/" . $vg_node["name"]
		);
	$popup["admin"]["class"] = $layout["type_class"];
	$popup["admin"]["group"] = $layout["type_group"];
	$popup["sys"]["path"] = $vg_father["settings_path"];
	$popup["sys"]["type"] = "admin_popup";
    }

    return $popup;
}

function process_vgallery_fields($vg_father, &$arrFieldLimit = null) {
    static $loaded_fields = array();

    $db = ffDB_Sql::factory();
    $schema = process_vgallery_schema();

    $table_source = $vg_father["src"]["type"] . "_fields";

    if (!isset($loaded_fields[$table_source])) {
	$loaded_fields[$table_source] = array(
	    "mode" => $vg_father["mode"]
	    , "search" => null
	    , "params" => array()
	);

	$sSQL = "SELECT 
			" . $table_source . ".ID                                                        		AS ID 
			, " . $table_source . ".ID_type                                                 		AS ID_type
			, " . $table_source . ".name                                                    		AS name
			, " . $table_source . ".data_source                                             		AS data_source
			, " . $table_source . ".data_limit                                              		AS data_limit
			, " . $table_source . ".selection_data_source                               			AS selection_data_source
			, " . $table_source . ".selection_data_limit                                			AS selection_data_limit
			, " . $table_source . ".data_sort                                               		AS data_sort
			, " . $table_source . ".data_sort_method                                        		AS data_sort_method
			, " . $table_source . ".disable_multilang                                        		AS disable_multilang
			, " . $table_source . ".enable_lastlevel                                        		AS enable_lastlevel
			, " . $table_source . ".enable_thumb_label         										AS enable_label_thumb
			, " . $table_source . ".enable_detail_label         									AS enable_label_detail
		    , vgallery_label_htmltag_thumb.tag                                                		AS htmltag_label_tag_thumb
		    , vgallery_label_htmltag_thumb.attr                                               		AS htmltag_label_attr_thumb
		    , vgallery_label_htmltag_detail.tag                                                		AS htmltag_label_tag_detail
		    , vgallery_label_htmltag_detail.attr                                               		AS htmltag_label_attr_detail
			, " . $table_source . ".enable_thumb_empty                 								AS enable_empty_thumb
			, " . $table_source . ".enable_detail_empty                 							AS enable_empty_detail
			, " . $table_source . ".thumb_limit														AS limit_char
			, " . $table_source . ".limit_thumb_by_layouts                       					AS limit_by_layouts_thumb
			, " . $table_source . ".limit_detail_by_layouts                       					AS limit_by_layouts_detail
			, " . $table_source . ".enable_thumb                       								AS enable_thumb
			, " . $table_source . ".enable_detail                       							AS enable_detail
			, " . $table_source . ".order_thumb                       								AS order_thumb
			, " . $table_source . ".order_detail                       								AS order_detail
			, " . $table_source . ".parent_thumb                       								AS parent_thumb
			, " . $table_source . ".parent_detail                       							AS parent_detail
			, " . $table_source . ".enable_thumb_cascading             								AS enable_cascading_thumb
			, " . $table_source . ".enable_detail_cascading             							AS enable_cascading_detail
			, " . $table_source . ".display_view_mode_thumb            								AS display_view_mode_thumb
			, " . $table_source . ".display_view_mode_detail            							AS display_view_mode_detail
			, " . $table_source . ".enable_sort                                             		AS enable_sort
			, " . $table_source . ".settings_type_thumb                								AS settings_type_thumb
			, " . $table_source . ".settings_type_thumb_md                							AS settings_type_thumb_md
			, " . $table_source . ".settings_type_thumb_sm                							AS settings_type_thumb_sm
			, " . $table_source . ".settings_type_thumb_xs                							AS settings_type_thumb_xs
			, " . $table_source . ".settings_type_detail                							AS settings_type_detail
			, " . $table_source . ".settings_type_detail_md                							AS settings_type_detail_md
			, " . $table_source . ".settings_type_detail_sm                							AS settings_type_detail_sm
			, " . $table_source . ".settings_type_detail_xs                							AS settings_type_detail_xs
			, vgallery_fields_htmltag_thumb.tag                                                     AS htmltag_tag_thumb
			, vgallery_fields_htmltag_thumb.attr                                                    AS htmltag_attr_thumb
			, vgallery_fields_htmltag_detail.tag                                                    AS htmltag_tag_detail
			, vgallery_fields_htmltag_detail.attr                                                   AS htmltag_attr_detail
			, " . $table_source . ".custom_thumb_field                 								AS custom_field_thumb
			, " . $table_source . ".custom_detail_field                 							AS custom_field_detail
			, " . $vg_father["src"]["type"] . "_type.name                                           AS type
			, vgallery_fields_data_type.name                                                        AS data_type 
			, extended_type.name                                                           			AS extended_type
			, extended_type.ff_name                                                    				AS ff_extended_type
			, extended_type.`group`                                                                 AS extended_type_group
			, " . $table_source . ".limit_by_groups_frontend                                		AS limit_by_groups_frontend
			, " . $table_source . ".enable_smart_url                                        		AS smart_url
		    , " . $table_source . ".fixed_pre_content_thumb            								AS fixed_pre_content_thumb
		    , " . $table_source . ".fixed_post_content_thumb           								AS fixed_post_content_thumb
		    , " . $table_source . ".fixed_pre_content_detail            							AS fixed_pre_content_detail
		    , " . $table_source . ".fixed_post_content_detail           							AS fixed_post_content_detail
			, " . $table_source . ".field_fluid_thumb												AS field_fluid_thumb
			, " . $table_source . ".field_grid_thumb												AS field_grid_thumb
			, " . $table_source . ".field_class_thumb												AS field_class_thumb
			, " . $table_source . ".label_fluid_thumb												AS label_fluid_thumb
			, " . $table_source . ".label_grid_thumb												AS label_grid_thumb
			, " . $table_source . ".field_fluid_detail												AS field_fluid_detail
			, " . $table_source . ".field_grid_detail												AS field_grid_detail
			, " . $table_source . ".field_class_detail												AS field_class_detail
			, " . $table_source . ".label_fluid_detail												AS label_fluid_detail
			, " . $table_source . ".label_grid_detail												AS label_grid_detail
		FROM " . $table_source . "
			INNER JOIN " . $vg_father["src"]["type"] . "_type				ON " . $table_source . ".ID_type = " . $vg_father["src"]["type"] . "_type.ID
			INNER JOIN vgallery_fields_data_type 							ON vgallery_fields_data_type.ID = " . $table_source . ".ID_data_type
			INNER JOIN extended_type 										ON extended_type.ID = " . $table_source . ".ID_extended_type
			LEFT JOIN vgallery_fields_htmltag AS vgallery_fields_htmltag_thumb			ON vgallery_fields_htmltag_thumb.ID = " . $table_source . ".ID_thumb_htmltag 
			LEFT JOIN vgallery_fields_htmltag AS vgallery_fields_htmltag_detail			ON vgallery_fields_htmltag_detail.ID = " . $table_source . ".ID_detail_htmltag 
		    LEFT JOIN vgallery_fields_htmltag AS vgallery_label_htmltag_thumb 			ON vgallery_label_htmltag_thumb.ID = " . $table_source . ".ID_label_thumb_htmltag 
		    LEFT JOIN vgallery_fields_htmltag AS vgallery_label_htmltag_detail 			ON vgallery_label_htmltag_detail.ID = " . $table_source . ".ID_label_detail_htmltag 
		WHERE 1
		ORDER BY " . $table_source . ".parent_" . $vg_father["mode"] . ", " . $table_source . ".`order_" . $vg_father["mode"] . "`";
	$db->query($sSQL);
	if ($db->nextRecord()) {
	    do {
		$ID_type = $db->getField("ID_type", "Number", true);
		$field_key = $db->getField("ID", "Number", true);
		$data_type = $db->getField("data_type", "Text", true);
		$data_source = $db->getField("data_source", "Text", true);
		$data_limit = $db->getField("data_limit", "Text", true);
		$selection_data_source = $db->getField("selection_data_source", "Text", true);
		$selection_data_limit = $db->getField("selection_data_limit", "Text", true);
		$data_sort_default = $db->getField("data_sort", "Number", true);
		$data_sort_method = $db->getField("data_sort_method", "Text", true);

		$extended_type = $db->getField("extended_type", "Text", true);
		$ff_extended_type = $db->getField("ff_extended_type", "Text", true);
		$extended_type_group = $db->getField("extended_type_group", "Text", true);

		$parent_thumb = $db->getField("parent_thumb", "Text", true);
		$order_thumb = $db->getField("order_thumb", "Number", true);
		$parent_detail = $db->getField("parent_detail", "Text", true);
		$order_detail = $db->getField("order_detail", "Number", true);

		if (strpos($parent_thumb, "-") === false) {
		    $order_thumb_real = $order_thumb;
		    $parent_thumb_real = $parent_thumb;
		} else {
		    $arrParent = explode("-", $parent_thumb);
		    $order_thumb_real = ((int) $arrParent[0] * 1000) + $order_thumb;

		    unset($arrParent[0]);
		    $parent_thumb_real = implode("-", $arrParent);
		}
		if (strpos($parent_detail, "-") === false) {
		    $order_detail_real = $order_detail;
		    $parent_detail_real = $parent_detail;
		} else {
		    $arrParent = explode("-", $parent_detail);
		    $order_detail_real = ((int) $arrParent[0] * 1000) + $order_detail;

		    unset($arrParent[0]);
		    $parent_detail_real = implode("-", $arrParent);
		}

		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["ID"] = $field_key;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["ID_type"] = $ID_type;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["thumb"]["visible"] = $db->getField("enable_thumb", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["thumb"]["limit_by_layouts"] = ($db->getField("limit_by_layouts_thumb", "Text", true) ? array($db->getField("limit_by_layouts_thumb", "Text", true)) : null
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["thumb"]["parent"] = $parent_thumb_real;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["thumb"]["order"] = $order_thumb_real;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["detail"]["visible"] = $db->getField("enable_detail", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["detail"]["limit_by_layouts"] = ($db->getField("limit_by_layouts_detail", "Text", true) ? array($db->getField("limit_by_layouts_detail", "Text", true)) : null
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["detail"]["parent"] = $parent_detail_real;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["system"]["detail"]["order"] = $order_detail_real;

		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["ID"] = $field_key;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["name"] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["data_type"] = $data_type;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["data_source"] = ($schema["db"]["data_source"][$data_source]["table"] ? $schema["db"]["data_source"][$data_source]["table"] : $data_source);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["data_limit"] = $data_limit;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"] = ($schema["db"]["selection_data_source"][$selection_data_source]["table"] ? $schema["db"]["selection_data_source"][$selection_data_source]["table"] : $selection_data_source);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"] = $selection_data_limit;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["disable_multilang"] = $db->getField("disable_multilang", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["data_sort"] = $data_sort_default;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["data_sort_method"] = $data_sort_method;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["type"] = $db->getField("type", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["extended_type"] = ($extended_type ? $extended_type : "String"
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["ff_extended_type"] = ($ff_extended_type ? $ff_extended_type : "Text"
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["extended_type_group"] = $extended_type_group;
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["limit_by_groups_frontend"] = $db->getField("limit_by_groups_frontend", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["smart_url"] = $db->getField("smart_url", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["enable_label"] = $db->getField("enable_label_thumb", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["htmltag_label_tag"] = $db->getField("htmltag_label_tag_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["htmltag_label_attr"] = ($db->getField("htmltag_label_attr_thumb", "Text", true) ? array($db->getField("htmltag_label_attr_thumb", "Text", true)) : array()
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["enable_empty"] = $db->getField("enable_empty_thumb", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["enable_cascading"] = $db->getField("enable_cascading_thumb", "Number", true);

		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["plugin"]["name"] = $db->getField("display_view_mode_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["plugin"]["class"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["plugin"]["name"]);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["image"]["src"] = get_image_properties_by_grid_system(
				    	$db->getField("settings_type_thumb", "Number", true)
				    	, $db->getField("settings_type_thumb_md", "Number", true)
				    	, $db->getField("settings_type_thumb_sm", "Number", true)
				    	, $db->getField("settings_type_thumb_xs", "Number", true)
				    );		
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["htmltag_tag"] = $db->getField("htmltag_tag_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["htmltag_attr"] = $db->getField("htmltag_attr_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["custom_field"] = $db->getField("custom_field_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["fixed_pre_content"] = $db->getField("fixed_pre_content_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["fixed_post_content"] = $db->getField("fixed_post_content_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["field_fluid"] = $db->getField("field_fluid_thumb", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["field_grid"] = $db->getField("field_grid_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["field_class"] = $db->getField("field_class_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["label_fluid"] = $db->getField("label_fluid_thumb", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["label_grid"] = $db->getField("label_grid_thumb", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["enable_lastlevel"] = $db->getField("enable_lastlevel", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["limit_char"] = $db->getField("limit_char", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["thumb"]["enable_sort"] = $db->getField("enable_sort", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["enable_label"] = $db->getField("enable_label_detail", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["htmltag_label_tag"] = $db->getField("htmltag_label_tag_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["htmltag_label_attr"] = ($db->getField("htmltag_label_attr_detail", "Text", true) ? array($db->getField("htmltag_label_attr_detail", "Text", true)) : array()
			);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["enable_empty"] = $db->getField("enable_empty_detail", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["enable_cascading"] = $db->getField("enable_cascading_detail", "Number", true);

		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["plugin"]["name"] = $db->getField("display_view_mode_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["plugin"]["class"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["plugin"]["name"]);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["image"]["src"] = get_image_properties_by_grid_system(
				    	$db->getField("settings_type_detail", "Number", true)
				    	, $db->getField("settings_type_detail_md", "Number", true)
				    	, $db->getField("settings_type_detail_sm", "Number", true)
				    	, $db->getField("settings_type_detail_xs", "Number", true)
				    );		
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["htmltag_tag"] = $db->getField("htmltag_tag_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["htmltag_attr"] = $db->getField("htmltag_attr_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["custom_field"] = $db->getField("custom_field_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["fixed_pre_content"] = $db->getField("fixed_pre_content_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["fixed_post_content"] = $db->getField("fixed_post_content_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["field_fluid"] = $db->getField("field_fluid_detail", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["field_grid"] = $db->getField("field_grid_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["field_class"] = $db->getField("field_class_detail", "Text", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["label_fluid"] = $db->getField("label_fluid_detail", "Number", true);
		$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["detail"]["label_grid"] = $db->getField("label_grid_detail", "Text", true);

		//$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["vgallery_group"] 			= $vgallery_force_group;
		switch ($data_type) {
		    case "relationship":
			/* $vg_field["rel"][$field_key] = array(
			  "vgallery_name" => $data_source
			  , "limit_fields" => $data_limit
			  , "sort_default" => $data_sort_default
			  , "sort_default_method" => $data_sort_method
			  ); */
//		                echo $selection_data_source . "ASD";
//		                echo $selection_data_limit . "ASD";
			if (strlen($data_source)) {
			    $arrDataLimit = array();
			    if (strlen($data_limit))
				$arrDataLimit = explode(",", $data_limit);

			    switch ($data_source) {
				case "anagraph":
				    $rel_type = "anagraph";
				    $rel_tbl = "anagraph";

				    //$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type][$rel_tbl]["src_field"] = $field_key;
				    //$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type][$rel_tbl]["dst_field"] = $arrDataLimit;
				    break;
				case "files":
				    $rel_type = "files";
				    $rel_tbl = "files";

				    //$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type][$rel_tbl]["src_field"] = $field_key;
				   // $loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type][$rel_tbl]["dst_field"] = $arrDataLimit;
				    break;
				default:
				    $rel_type = "vgallery_nodes";
				    $rel_tbl = $data_source;
//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
				    break;
			    }

				$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["fullpath"][$rel_tbl] = "/" . $rel_tbl; 
				$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["src"][$field_key] = $rel_tbl;
				
				$loaded_fields[$table_source]["params"][$field_key]["keys"][$field_key] = $field_key;
				
				//$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["tbl"][$rel_tbl]["src_field"] = $field_key;
				//$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["tbl"][$rel_tbl]["dst_field"] = $arrDataLimit;
				foreach($arrDataLimit AS $arrDataLimit_value) {
					if(is_numeric($arrDataLimit_value)) {
						$loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["fields"][$arrDataLimit_value] = $arrDataLimit_value;
					}
				}
				if($selection_data_source && $selection_data_limit && $selection_data_limit != "null") {
				    $loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["rel"][$vg_father["src"]["table"]]["tbl"][$rel_tbl] = $rel_tbl;
				    $loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["rel"][$vg_father["src"]["table"]]["field"] = $selection_data_limit;
				} else {
				    $loaded_fields[$table_source]["params"][$field_key]["relationship"][$rel_type]["rel"]["default"]["tbl"][$rel_tbl] = $rel_tbl;
				}
			}
			break;
		    case "table.alt":
			$arrDataLimit = array_filter(explode(",", $data_limit));
			if (count($arrDataLimit) > 1) {
			    $str_data_limit = "CONCAT(`" . $data_source . "`.`" . implode("`, ' ',`" . $data_source . "`.`", $arrDataLimit) . "`)";
			} else {
			    $str_data_limit = "`" . $data_source . "`.`" . $data_limit . "`";
			}

			$loaded_fields[$table_source]["params"][$field_key]["preload"][$data_source]["db"] = array_combine($arrDataLimit, $arrDataLimit);
			$loaded_fields[$table_source]["params"][$field_key]["preload"][$data_source]["fields"][$data_limit] = $str_data_limit;

			//Necessario per ricercare in tutti i campi
			if (is_array($loaded_fields[$table_source]["search"]["preload"][$data_source]["db"]))
			    $loaded_fields[$table_source]["search"]["preload"][$data_source]["db"] = array_replace($loaded_fields[$table_source]["search"]["preload"][$data_source]["db"], array_combine($arrDataLimit, $arrDataLimit));
			else
			    $loaded_fields[$table_source]["search"]["preload"][$data_source]["db"] = array_combine($arrDataLimit, $arrDataLimit);

			$loaded_fields[$table_source]["search"]["preload"][$data_source]["fields"][$data_limit] = $str_data_limit;
			break;
		    case "selection":
			break;
		    default:
			$loaded_fields[$table_source]["params"][$field_key]["keys"][$field_key] = $field_key;
		}

		switch ($extended_type_group) {
		    case "upload":
			$loaded_fields[$table_source]["params"][$field_key]["resources"][$field_key] = $field_key;
			break;
		    case "special":
			if (strlen($data_limit) && $data_limit !== "null") {
			    if (isset($schema["db"]["data_source"][$data_source])) {
			    	$target_field = "";
					$arrDataLimit = explode(",", $data_limit);
					$target_tbl = ($schema["db"]["data_source"][$data_source]["table"]
						? $schema["db"]["data_source"][$data_source]["table"]
						: $data_source
					);
					
					if(is_array($arrDataLimit) && count($arrDataLimit)) {
						$arrTargetField = array();
						foreach($arrDataLimit AS $arrDataLimit_value) {
							if(is_array($schema["db"]["data_source"][$data_source]["fields"][$arrDataLimit_value]["multi"])) {
								$arrTargetField = array_merge($schema["db"]["data_source"][$data_source]["fields"][$arrDataLimit_value]["multi"], $arrTargetField);
							} else {
								$arrTargetField[] = $arrDataLimit_value;
							}
						}

						if (count($arrTargetField) > 1)
						    $target_field = "CONCAT(`" . implode("`,'|',`", $arrTargetField) . "`)";
						else
						    $target_field = "`" . $arrTargetField[0] . "`";
					}

					$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"] = $target_tbl;
					$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"] = $data_limit;

					$field_selection_key = "field_" . ffCommon_url_rewrite($target_field);

					$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["field"][$data_limit] = $target_field . " AS `" . $field_selection_key . "`";
					$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["params"]["selection-" . $field_key] = array(
					    "ID" => $field_key
					    , "type" => "selection"
					    , "key" => $field_selection_key
					    , "src" => $arrDataLimit
					    , "primary_key" => $schema["db"]["data_source"][$data_source]["key"]
					    , "fields" => $schema["db"]["data_source"][$data_source]["fields"]
					    /*, "compare" => (is_array($schema["db"]["data_source"][$data_source]["record_params"]) 
					    	? array_filter($schema["db"]["data_source"][$data_source]["record_params"]) 
					    	: null
					    )*/
					);

					if ($vg_father["ID_node"]) {
					    $loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["where"][$schema["db"]["data_source"][$data_source]["key"]][] = $vg_father["ID_node"];
					}
			    }
			}
			//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
			break;
		    case "select":
			if (!strlen($loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"]) || is_numeric($loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"])) {
			    $data_limit_key = "name";
			    $target_tbl = $vg_father["src"]["type"] . "_fields_selection_value";
			    //$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["sWhere"] = " AND " . $vg_father["src"]["type"] . "_fields_selection_value.ID_selection = " . $db->toSql($loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"], "Number");
			    $target_field = "name";
			} else {
			    $data_limit_key = "name";
			    //$loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["sWhere"] = "";
			    $target_tbl = $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_source"];
			    if (strlen($loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"]) && $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"] !== "null") {
				$data_limit_key = $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"];
				if (strpos($loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"], ",") !== false)
				    $target_field = "CONCAT(`" . str_replace(",", "`,'|',`", $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"]) . "`)";
				else
				    $target_field = "`" . $loaded_fields[$table_source]["fields"][$ID_type][$field_key]["base"]["select"]["data_limit"] . "`";
			    } else {
				$target_field = "name";
			    }
			}
			$field_selection_key = "field_" . ffCommon_url_rewrite($target_field);


			$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["field"][$data_limit_key] = $target_field . " AS `" . $field_selection_key . "`";

			$arrShard = array();
			if ($data_type == "table.alt") {
			    foreach ($arrDataLimit AS $arrDataLimit_value) {
				$arrFieldName = explode(",", $data_limit_key);

				$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["params"]["preload-" . $arrDataLimit_value] = array(
				    "ID" => $arrDataLimit_value
				    , "type" => "preload"
				    , "key" => $field_selection_key
				    , "tbl" => $data_source
				    , "src" => $arrFieldName
				);

				$loaded_fields[$table_source]["params"][$field_key]["selection"]["preload"][$data_source]["fields"][$arrDataLimit_value] = array(
				    "table" => $target_tbl
				    , "field" => $field_selection_key
				    , "src" => $arrFieldName
				);

				if (isset($schema["schema"][$target_tbl]["field"])) {
				    foreach ($arrFieldName AS $field_name) {
					if (isset($schema["schema"][$target_tbl]["field"][$field_name]["data_source"])
					//&& isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$schema[$target_tbl]["field"][$field_name]["data_source"]["tbl"]])
					) {
					    $arrShard[$field_name] = $schema["schema"][$target_tbl]["field"][$field_name]["data_source"];
					    $shard_selection_field = implode(",", $arrShard[$field_name]["fields"]);
					    $shard_selection_value = "CONCAT(`" . str_replace(",", "`,'|',`", $shard_selection_field) . "`)";
					    $shard_selection_key = "field_" . ffCommon_url_rewrite($shard_selection_value);
					    if (!isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["field"][$shard_selection_field])) {
						$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["field"][$shard_selection_field] = $shard_selection_value . " AS `" . $shard_selection_key . "`";
					    }
					    $arrShard[$field_name]["ID"] = "shard-preload-" . $arrDataLimit_value;
					    if (!isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["params"]["shard-preload-" . $arrDataLimit_value])) {
						$loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["params"]["shard-preload-" . $arrDataLimit_value] = array(
						    "type" => "shard"
						    , "key" => $shard_selection_key
						    , "src" => $arrShard[$field_name]["fields"]
						);
					    }
					}
				    }
				    if (count($arrShard))
					$loaded_fields[$table_source]["params"][$field_key]["selection"]["preload"][$data_source]["fields"][$arrDataLimit_value]["shard"] = $arrShard;
				}
			    }
			} else {
			    $arrFieldName = explode(",", $data_limit_key);
			    $loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$target_tbl]["params"]["data-" . $field_key] = array(
				"ID" => $field_key
				, "type" => "data"
				, "key" => $field_selection_key
				, "src" => $arrFieldName
			    );

			    $loaded_fields[$table_source]["params"][$field_key]["selection"]["data"][$field_key] = array(
				"table" => $target_tbl
				, "field" => $field_selection_key
				, "src" => $arrFieldName
			    );

			    if (isset($schema["schema"][$target_tbl]["field"])) {
				foreach ($arrFieldName AS $field_name) {
				    if (isset($schema["schema"][$target_tbl]["field"][$field_name]["data_source"])
				    //&& isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$schema[$target_tbl]["field"][$field_name]["data_source"]["tbl"]])
				    ) {
					$arrShard[$field_name] = $schema["schema"][$target_tbl]["field"][$field_name]["data_source"];
					$shard_selection_field = implode(",", $arrShard[$field_name]["fields"]);
					$shard_selection_value = "CONCAT(`" . str_replace(",", "`,'|',`", $shard_selection_field) . "`)";
					$shard_selection_key = "field_" . ffCommon_url_rewrite($shard_selection_value);
					if (!isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["field"][$shard_selection_field])) {
					    $loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["field"][$shard_selection_field] = $shard_selection_value . " AS `" . $shard_selection_key . "`";
					}
					$arrShard[$field_name]["ID"] = "shard-data-" . $field_key;
					if (!isset($loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["params"]["shard-data-" . $field_key])) {
					    $loaded_fields[$table_source]["params"][$field_key]["selection"]["tbl"][$arrShard[$field_name]["tbl"]]["params"]["shard-data-" . $field_key] = array(
						"type" => "shard"
						, "key" => $shard_selection_key
						, "src" => $arrShard[$field_name]["fields"]
					    );
					}
				    }
				}
				if (count($arrShard))
				    $loaded_fields[$table_source]["params"][$field_key]["selection"]["data"][$field_key]["shard"] = $arrShard;
			    }
			}

			//Necessario per ricercare in tutti i campi
			$loaded_fields[$table_source]["search"]["selection"]["tbl"][$target_tbl]["field"][$data_limit_key] = $target_field . " AS `" . $field_selection_key . "`";
			if ($data_type == "table.alt") {
			    foreach ($arrDataLimit AS $arrDataLimit_value) {
				$loaded_fields[$table_source]["search"]["selection"]["preload"][$data_source]["fields"][$arrDataLimit_value] = array(
				    "table" => $target_tbl
				    , "field" => $field_selection_key
				);
			    }
			} else {
			    $loaded_fields[$table_source]["search"]["selection"]["data"][$field_key] = array(
				"table" => $target_tbl
				, "field" => $field_selection_key
			    );
			}

			break;
		    default:
		}
	    } while ($db->nextRecord());
	}
    }
//print_r($loaded_fields);
    $params = $loaded_fields[$table_source]["params"];
    if (is_array($vg_father["limit_type"]) && count($vg_father["limit_type"]))
	$res["fields"] = array_intersect_key($loaded_fields[$table_source]["fields"], array_flip($vg_father["limit_type"]));
    else
	$res["fields"] = $loaded_fields[$table_source]["fields"];
    if (is_array($arrFieldLimit) && count($arrFieldLimit)) {
		foreach ($arrFieldLimit AS $field_type => $arrFieldLimitData) {
		    if (is_array($arrFieldLimitData) && count($arrFieldLimitData)) {
			switch ($field_type) {
			    case "filters":
				foreach ($res["fields"] AS $ID_type => $arrFields) {
				    $res["fields"][$ID_type] = array_filter($arrFields, function($field) use ($arrFieldLimitData, &$arrFieldLimit) {
					foreach ($arrFieldLimitData AS $field_name => $arrField_value) {
					    $res = array_search(ffCommon_url_rewrite($field["base"][$field_name]), $arrField_value);
					    if ($res !== false) {
							$arrFieldLimit["ID"][] = $field["system"]["ID"];
							return true;
					    }
					}
				    });

				    if (!count($res["fields"][$ID_type]))
					unset($res["fields"][$ID_type]);
				}
				break;
			    case "keys":
				$arrFieldFlip = array_flip($arrFieldLimitData);
				foreach ($res["fields"] AS $ID_type => $arrFields) {
				    if (is_array($res["fields"][$ID_type]))
					$res["fields"][$ID_type] = array_replace($res["fields"][$ID_type], array_intersect_key($arrFields, $arrFieldFlip));
				    else
					$res["fields"][$ID_type] = array_intersect_key($arrFields, $arrFieldFlip);

				    if (!count($res["fields"][$ID_type]))
					unset($res["fields"][$ID_type]);
				}
				break;
			    default:
			}
		    }
		}

		if(is_array($arrFieldLimit["ID"]))
			$params = array_intersect_key($params, array_flip($arrFieldLimit["ID"])); 
    }

    $res["search"] = $loaded_fields[$table_source]["search"];
    //$res["relationship"] = $loaded_fields[$table_source]["relationship"];
    $res["params"] = $params;

    return $res;
}

function process_vgallery_type(&$vg_father, $tpl_data, $layout) {
    static $loaded_fields = array();

    $db = ffDB_Sql::factory();

    $actual_field_schema = array();
    $vg_field = null;
    $sSQL = "";

//se rimesso non funzionano i templare learnmore
    if (/*!is_array($vg_father["limit"]["fields"]) &&*/ $tpl_data["result"]["type"] == "custom" && is_array($tpl_data["obj"]->DVars) && count($tpl_data["obj"]->DVars)) {
		foreach ($tpl_data["obj"]->DVars AS $tpl_var => $tpl_ignore) {
		    $arrTplField = array();

		    //$tpl_var = strtolower($tpl_var);
		    if (strpos($tpl_var, ":") !== false) {
			$arrTplField["properties"] = explode(":", $tpl_var);
			$tmp_field = $arrTplField["properties"][0];
			unset($arrTplField["properties"][0]);
		    } else {
			$tmp_field = $tpl_var;
		    }
		    switch ($tmp_field) {
			case "class":
			case "properties":
			case "item":
			case "admin":
			case "pagination":
			    break;
			default:
			    $property_data = "";
			    if (count($arrTplField["properties"])) {
					$main_property = $arrTplField["properties"][1];
					unset($arrTplField["properties"][1]);
					$property_data = array($main_property => implode(":", $arrTplField["properties"]));
			    }

			    if (strpos($tmp_field, ".") === false) {
				$field_key = ffCommon_url_rewrite($tmp_field);
				$vg_father["limit"]["tpl"]["vars"][$field_key] = $tmp_field;

				if (is_array($vg_father["limit"]["tpl"]["fields"][$field_key]) && is_array($property_data))
				    $vg_father["limit"]["tpl"]["fields"][$field_key] = array_merge($vg_father["limit"]["tpl"]["fields"][$field_key], $property_data);
				else
				    $vg_father["limit"]["tpl"]["fields"][$field_key] = $property_data;
			    } else {
				$arrTplField["shard"] = explode(".", $tmp_field);
				$field_key = ffCommon_url_rewrite($arrTplField["shard"][0]);

				$vg_father["limit"]["tpl"]["vars"][$field_key . "." . $arrTplField["shard"][1]] = $tmp_field;
				$vg_father["limit"]["tpl"]["fields"][$field_key] = "";
				if (is_array($vg_father["limit"]["tpl"]["shard"][$field_key][$arrTplField["shard"][1]]) && is_array($property_data))
				    $vg_father["limit"]["tpl"]["shard"][$field_key][$arrTplField["shard"][1]] = array_merge($vg_father["limit"]["tpl"]["shard"][$field_key][$arrTplField["shard"][1]], $property_data);
				else
				    $vg_father["limit"]["tpl"]["shard"][$field_key][$arrTplField["shard"][1]] = $property_data;
			    }
		    }
		}

		if(is_array($vg_father["limit"]["tpl"]["fields"]))
			$vg_father["limit"]["fields"] = implode(",", array_keys($vg_father["limit"]["tpl"]["fields"]));

		$vg_father["limit"]["compare_key"] = "name";
		$vg_father["is_custom_template"] = true;
    }

    if (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["fields"])) {
		$actual_field_schema = array(
		    "macro" => "limited"
		    , "sub" => implode(",", $vg_father["limit"]["fields"])
		    , "tbl_field" => $vg_father["src"]["type"] . "_fields"
		);
    } elseif (is_array($tpl_data["field"]) && count($tpl_data["field"])) {
		$actual_field_schema = array(
		    "macro" => "tpl"
		    , "sub" => $tpl_data["field"]["source"]
		    , "tbl_field" => $vg_father["src"]["type"] . "_fields"
		);
    } elseif (is_array($vg_father["publishing"]) && count($vg_father["publishing"]) > 0) {
		$actual_field_schema = array(
		    "macro" => "publishing"
		    , "sub" => $vg_father["publishing"]["ID"] . "-" . $vg_father["publishing"]["limit_fields"] . "-" . $tpl_data["result"]["type"] . $vg_father["limit"]["fields"]
		    , "tbl_field" => $vg_father["src"]["type"] . "_fields"
		);
    } elseif ($vg_father["vgallery_name"] == "anagraph") {
		$actual_field_schema = array(
		    "macro" => "anagraph"
		    , "sub" => $vg_father["mode"] . "-" . $tpl_data["result"]["type"] . "-" . implode("-", $vg_father["limit_type"]) . "-" . $vg_father["limit"]["fields"]
		    , "tbl_field" => $vg_father["src"]["type"] . "_fields"
		);
    } else {
		$actual_field_schema = array(
		    "macro" => "standard"
		    , "sub" => $vg_father["mode"] . "-" . $tpl_data["result"]["type"] . "-" . implode("-", $vg_father["limit_type"]) . "-" . $vg_father["limit"]["fields"]
		    , "tbl_field" => $vg_father["src"]["type"] . "_fields"
		);
    }

    if ($actual_field_schema["sub"] === null)
		$vg_field = $loaded_fields[$actual_field_schema["macro"]];
    else
		$vg_field = $loaded_fields[$actual_field_schema["macro"]][$actual_field_schema["sub"]];

    /*
      if(is_array($arrType)) {
      if(count($arrType)) {
      if(is_array($vg_field))
      $arrTypeToLoad = array_keys(array_diff_key($arrType, $vg_field));
      else
      $arrTypeToLoad = array_keys($arrType);
      if(!count($arrTypeToLoad))
      $field_is_loaded = true;


      } else {
      if(is_array($vg_field)) {
      $field_is_loaded = true;
      }
      }
      } elseif(strlen($arrType)) {
      if(is_array($vg_field)) {
      $field_is_loaded = true;
      } else {
      $arrTypeToLoad = array($arrType);
      }
      } */

    if (!is_array($vg_field)) 
    {
		$vg_field = array();
		$arrFieldOverride = array();
		$field_key = ($vg_father["limit"]["compare_key"] ? $vg_father["limit"]["compare_key"] : "ID"
			);

		if (is_array($vg_father["limit"]["fields"]))
		    $field_limit = $vg_father["limit"]["fields"];
		elseif (strlen($vg_father["limit"]["fields"]))
		    $field_limit = array_unique(explode(",", $vg_father["limit"]["fields"]));

		if ($field_limit) {
		    if ($field_key == "ID") {
				$arrFieldLimit[$field_key] = $field_limit;
			} else {
				$arrFieldLimit["filters"][$field_key] = $field_limit;
				if(is_array($vg_father["properties"]["image"]["fields"]))
					$arrFieldLimit["filters"]["ID"] = $vg_father["properties"]["image"]["fields"];
			}
		}

		switch ($actual_field_schema["macro"]) {
		    case "publishing":
			$sSQL = "SELECT 
		                    publishing_fields.ID_fields																		AS ID
		                    , publishing_fields.enable_lastlevel                                      						AS enable_lastlevel
		                    , publishing_fields.enable_thumb_label                                    						AS enable_label
		                    , vgallery_label_htmltag.tag                                                					AS htmltag_label_tag
		                    , vgallery_label_htmltag.attr                                               					AS htmltag_label_attr
		                    , publishing_fields.enable_thumb_empty                                    						AS enable_empty
		                    , publishing_fields.thumb_limit                                           						AS limit_char
		                    , publishing_fields.parent_thumb                                          						AS parent
		                    , publishing_fields.enable_thumb_cascading                                						AS enable_cascading
		                    , publishing_fields.display_view_mode_thumb                               						AS display_view_mode
		                    , publishing_fields.enable_sort                                           						AS enable_sort
							, publishing_fields.settings_type_thumb                											AS settings_type
							, publishing_fields.settings_type_thumb_md                										AS settings_type_md
							, publishing_fields.settings_type_thumb_sm                										AS settings_type_sm
							, publishing_fields.settings_type_thumb_xs                										AS settings_type_xs
		                    , vgallery_fields_htmltag.tag                                               					AS htmltag_tag
		                    , vgallery_fields_htmltag.attr                                              					AS htmltag_attr
		                    , publishing_fields.custom_thumb_field                                    						AS custom_field
						    , publishing_fields.fixed_pre_content_thumb            											AS fixed_pre_content
						    , publishing_fields.fixed_post_content_thumb           											AS fixed_post_content
							, publishing_fields.field_fluid_thumb															AS field_fluid
							, publishing_fields.field_grid_thumb															AS field_grid
							, publishing_fields.field_class_thumb															AS field_class
							, publishing_fields.label_fluid_thumb															AS label_fluid
							, publishing_fields.label_grid_thumb															AS label_grid
		                 FROM publishing_fields
		                    LEFT JOIN vgallery_fields_htmltag 							ON vgallery_fields_htmltag.ID = publishing_fields.ID_thumb_htmltag 
		                    LEFT JOIN vgallery_fields_htmltag AS vgallery_label_htmltag ON vgallery_label_htmltag.ID = publishing_fields.ID_label_thumb_htmltag 
		                 WHERE publishing_fields.ID_publishing = " . $db->toSql($vg_father["publishing"]["ID"], "Number") . "
		                 ORDER BY publishing_fields.parent_thumb, publishing_fields.`order_thumb`";
			$db->query($sSQL);
			if ($db->nextRecord()) {
			    do {
					$field_key = $db->getField("ID", "Number", true);
					$arrFieldOverride[$field_key]["enable_lastlevel"] = $db->getField("enable_lastlevel", "Number", true);
					$arrFieldOverride[$field_key]["enable_label"] = $db->getField("enable_label", "Number", true);
					$arrFieldOverride[$field_key]["htmltag_label_tag"] = $db->getField("htmltag_label_tag", "Text", true);
					$arrFieldOverride[$field_key]["htmltag_label_attr"] = ($db->getField("htmltag_label_attr", "Text", true) 
						? array($db->getField("htmltag_label_attr", "Text", true)) 
						: array()
					);
					$arrFieldOverride[$field_key]["enable_empty"] = $db->getField("enable_empty", "Number", true);
					$arrFieldOverride[$field_key]["limit_char"] = $db->getField("limit_char", "Number", true);
					$arrFieldOverride[$field_key]["parent"] = $db->getField("parent", "Text", true);
					$arrFieldOverride[$field_key]["enable_cascading"] = $db->getField("enable_cascading", "Number", true);
					$arrFieldOverride[$field_key]["enable_sort"] = $db->getField("enable_sort", "Number", true);

					$arrFieldOverride[$field_key]["plugin"]["name"] = $db->getField("display_view_mode", "Text", true);
					$arrFieldOverride[$field_key]["plugin"]["class"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $arrFieldOverride[$field_key]["plugin"]["name"]);
					$arrFieldOverride[$field_key]["image"]["src"] = get_image_properties_by_grid_system(
				    				$db->getField("settings_type", "Number", true)
				    				, $db->getField("settings_type_md", "Number", true)
				    				, $db->getField("settings_type_sm", "Number", true)
				    				, $db->getField("settings_type_xs", "Number", true)
							    );		
					$arrFieldOverride[$field_key]["htmltag_tag"] = $db->getField("htmltag_tag", "Text", true);
					$arrFieldOverride[$field_key]["htmltag_attr"] = $db->getField("htmltag_attr", "Text", true);
					$arrFieldOverride[$field_key]["custom_field"] = $db->getField("custom_field", "Text", true);
					$arrFieldOverride[$field_key]["fixed_pre_content"] = $db->getField("fixed_pre_content", "Text", true);
					$arrFieldOverride[$field_key]["fixed_post_content"] = $db->getField("fixed_post_content", "Text", true);
					$arrFieldOverride[$field_key]["field_fluid"] = $db->getField("field_fluid", "Number", true);
					$arrFieldOverride[$field_key]["field_grid"] = $db->getField("field_grid", "Text", true);
					$arrFieldOverride[$field_key]["field_class"] = $db->getField("field_class", "Text", true);
					$arrFieldOverride[$field_key]["label_fluid"] = $db->getField("label_fluid", "Number", true);
					$arrFieldOverride[$field_key]["label_grid"] = $db->getField("label_grid", "Text", true);
			    } while ($db->nextRecord());

			    if (!$vg_father["is_custom_template"] && !$arrFieldLimit)
					$arrFieldLimit["ID"] = array_keys($arrFieldOverride);
			}
		    case "tpl":
		    case "learnmore":
			$mode = "thumb";
			break;
		    case "group":
			$sSQL = "SELECT 
		                    vgallery_groups_fields.ID_fields																AS ID
		                    , vgallery_groups_fields.enable_detail_label                                    				AS enable_label
		                    , vgallery_label_htmltag.tag                                                					AS htmltag_label_tag
		                    , vgallery_label_htmltag.attr                                               					AS htmltag_label_attr
		                    , vgallery_groups_fields.enable_detail_empty                                    				AS enable_empty
		                    , vgallery_groups_fields.parent_detail                                          				AS parent
		                    , vgallery_groups_fields.enable_detail_cascading                                				AS enable_cascading
		                    , vgallery_groups_fields.display_view_mode_detail                               				AS display_view_mode
		                    , vgallery_groups_fields.settings_type_detail                                                	AS settings_type
		                    , vgallery_groups_fields.settings_type_detail_md                                               	AS settings_type_md
		                    , vgallery_groups_fields.settings_type_detail_sm                                               	AS settings_type_sm
		                    , vgallery_groups_fields.settings_type_detail_xs                                               	AS settings_type_xs
		                    , vgallery_fields_htmltag.tag                                               					AS htmltag_tag
		                    , vgallery_fields_htmltag.attr                                              					AS htmltag_attr
		                    , vgallery_groups_fields.custom_detail_field                                    				AS custom_field
						    , vgallery_groups_fields.fixed_pre_content_detail            									AS fixed_pre_content
						    , vgallery_groups_fields.fixed_post_content_detail           									AS fixed_post_content
							, vgallery_groups_fields.field_fluid_detail														AS field_fluid
							, vgallery_groups_fields.field_grid_detail														AS field_grid
							, vgallery_groups_fields.field_class_detail														AS field_class
							, vgallery_groups_fields.label_fluid_detail														AS label_fluid
							, vgallery_groups_fields.label_grid_detail														AS label_grid
					     FROM vgallery_groups_fields
		                    LEFT JOIN vgallery_fields_htmltag 							ON vgallery_fields_htmltag.ID = vgallery_groups_fields.ID_detail_htmltag 
		                    LEFT JOIN vgallery_fields_htmltag AS vgallery_label_htmltag ON vgallery_label_htmltag.ID = vgallery_groups_fields.ID_label_detail_htmltag 
		                 WHERE vgallery_groups_fields.ID_group = " . $db->toSql($vg_father["group"]["ID"], "Number") . "
		                 ORDER BY vgallery_groups_fields.parent_detail, publishing_fields.`order_detail`";
			$db->query($sSQL);
			if ($db->nextRecord()) {
			    do {
					$field_key = $db->getField("ID", "Number", true);
					$arrFieldOverride[$field_key]["enable_lastlevel"] = $db->getField("enable_lastlevel", "Number", true);
					$arrFieldOverride[$field_key]["enable_label"] = $db->getField("enable_label", "Number", true);
					$arrFieldOverride[$field_key]["htmltag_label_tag"] = $db->getField("htmltag_label_tag", "Text", true);
					$arrFieldOverride[$field_key]["htmltag_label_attr"] = ($db->getField("htmltag_label_attr", "Text", true) 
						? array($db->getField("htmltag_label_attr", "Text", true)) 
						: array()
					);
					$arrFieldOverride[$field_key]["enable_empty"] = $db->getField("enable_empty", "Number", true);
					$arrFieldOverride[$field_key]["limit_char"] = $db->getField("limit_char", "Number", true);
					$arrFieldOverride[$field_key]["parent"] = $db->getField("parent", "Text", true);
					$arrFieldOverride[$field_key]["enable_cascading"] = $db->getField("enable_cascading", "Number", true);
					$arrFieldOverride[$field_key]["enable_sort"] = $db->getField("enable_sort", "Number", true);

					$arrFieldOverride[$field_key]["plugin"]["name"] = $db->getField("display_view_mode", "Text", true);
					$arrFieldOverride[$field_key]["plugin"]["class"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $arrFieldOverride[$field_key]["plugin"]["name"]);
					$arrFieldOverride[$field_key]["image"]["src"] = get_image_properties_by_grid_system(
				    				$db->getField("settings_type", "Number", true)
				    				, $db->getField("settings_type_md", "Number", true)
				    				, $db->getField("settings_type_sm", "Number", true)
				    				, $db->getField("settings_type_xs", "Number", true)
							    );		
					$arrFieldOverride[$field_key]["htmltag_tag"] = $db->getField("htmltag_tag", "Text", true);
					$arrFieldOverride[$field_key]["htmltag_attr"] = $db->getField("htmltag_attr", "Text", true);
					$arrFieldOverride[$field_key]["custom_field"] = $db->getField("custom_field", "Text", true);
					$arrFieldOverride[$field_key]["fixed_pre_content"] = $db->getField("fixed_pre_content", "Text", true);
					$arrFieldOverride[$field_key]["fixed_post_content"] = $db->getField("fixed_post_content", "Text", true);
					$arrFieldOverride[$field_key]["field_fluid"] = $db->getField("field_fluid", "Number", true);
					$arrFieldOverride[$field_key]["field_grid"] = $db->getField("field_grid", "Text", true);
					$arrFieldOverride[$field_key]["field_class"] = $db->getField("field_class", "Text", true);
					$arrFieldOverride[$field_key]["label_fluid"] = $db->getField("label_fluid", "Number", true);
					$arrFieldOverride[$field_key]["label_grid"] = $db->getField("label_grid", "Text", true);
			    } while ($db->nextRecord());
			}
			$mode = "detail";
			break;
		    case "limited":
		    case "anagraph":
		    default:
			$mode = $vg_father["mode"];
		}

		if (is_array($arrFieldLimit)) {
		    $arrFieldLimit["keys"] = (is_array($arrFieldLimit["ID"]) 
		    	? $arrFieldLimit["ID"] 
		    	: array()
			);

		    if ($vg_father["sort_default"] && array_search($vg_father["sort_default"], $arrFieldLimit["keys"]) === false) {
				$arrFieldLimit["keys"][] = $vg_father["sort_default"];
		    }
		}

		$loaded_default_fields = process_vgallery_fields($vg_father, $arrFieldLimit);
		if (is_array($loaded_default_fields["fields"]) && count($loaded_default_fields["fields"])) {
		    foreach ($loaded_default_fields["fields"] AS $ID_type => $arrFields) {
				if (isset($arrFields[$vg_father["sort_default"]]))
				    $vg_field["sort"] = array_merge($arrFields[$vg_father["sort_default"]]["base"], $arrFields[$vg_father["sort_default"]][$mode]);

				if (is_array($arrFieldLimit["ID"])) {
				    foreach ($arrFieldLimit["ID"] AS $field_key) {
						if (!isset($arrFields[$field_key]))
						    continue;

						$arrField = $arrFields[$field_key];

                                                //Search By Selective Field
						if($vg_father["search"]["available_terms"][$arrField["base"]["name"]]) {
							if($arrField["base"]["data_type"] == "table.alt") {
								$vg_father["search"]["term_by_fields"]["preload"][$arrField["base"]["data_source"]][$arrField["base"]["data_limit"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
						    } else {
								$vg_father["search"]["term_by_fields"]["data"][$arrField["system"]["ID"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
						    }
						    if($arrField["base"]["select"]["data_source"]) {
								$vg_father["search"]["term_by_fields"]["selection"][$arrField["base"]["select"]["data_source"]][$arrField["base"]["select"]["data_limit"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
							} 

						   	$vg_father["search"]["params"][$arrField["base"]["name"]] = $arrField["base"]["name"] . "=" . urlencode($vg_father["search"]["available_terms"][$arrField["base"]["name"]]);
						}                                                
                                                
						$field_key = $arrField["system"]["ID"];
						$field_parent = (isset($arrFieldOverride[$field_key]["parent"])
							? $arrFieldOverride[$field_key]["parent"]
							: $arrField["system"][$mode]["parent"]
						);

						$vg_field["fields"][$ID_type][$field_parent][$field_key] = array_merge($arrField["base"], $arrField[$mode]);
						if (isset($arrFieldOverride[$field_key]))
						    $vg_field["fields"][$ID_type][$field_parent][$field_key] = array_replace($vg_field["fields"][$ID_type][$field_parent][$field_key], $arrFieldOverride[$field_key]);

						if (strlen($vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"])) {
						    parse_str(str_replace('"', '', $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]), $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]);

						    if (isset($vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"]))
							$vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"] = array("default" => $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"]);
						}

						$vg_field["fields"][$ID_type][$field_parent][$field_key]["group"] = (is_array($vg_father["properties"]["image"]["fields"]) && array_search($field_key, $vg_father["properties"]["image"]["fields"]) !== false 
							? "img" 
							: "desc"
						);
                                                
						if (is_array($loaded_default_fields["params"][$field_key]))
						    $vg_field = array_replace_recursive($vg_field, $loaded_default_fields["params"][$field_key]);
						
						
				    }
				} else {
				    if ($loaded_default_fields["mode"] != $mode) {
					usort($arrFields, function($a, $b) use ($mode) {
					    return $a["system"][$mode]['order'] - $b["system"][$mode]['order'];
					});
				    }

				    foreach ($arrFields AS $arrField) {
                                                //Search By Selective Field
						if($vg_father["search"]["available_terms"][$arrField["base"]["name"]]) {
						    if($arrField["base"]["data_type"] == "table.alt") {
								$vg_father["search"]["term_by_fields"]["preload"][$arrField["base"]["data_source"]][$arrField["base"]["data_limit"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
						    } else {
								$vg_father["search"]["term_by_fields"]["data"][$arrField["system"]["ID"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
						    }
						    if($arrField["base"]["select"]["data_source"]) {
								$vg_father["search"]["term_by_fields"]["selection"][$arrField["base"]["select"]["data_source"]][$arrField["base"]["select"]["data_limit"]] = $vg_father["search"]["available_terms"][$arrField["base"]["name"]];
						    }
						    
						    $vg_father["search"]["params"][$arrField["base"]["name"]] = $arrField["base"]["name"] . "=" . urlencode($vg_father["search"]["available_terms"][$arrField["base"]["name"]]);
						} 
                                                
						if (!$vg_father["is_custom_template"] && !$arrField["system"][$mode]["visible"])
						    continue;
						if (!$vg_father["is_custom_template"] && is_array($arrField["system"][$mode]["limit_by_layouts"]) && array_search($layout["ID"], $arrField["system"][$mode]["limit_by_layouts"]) === false)
						    continue;
                                        
						$field_key = $arrField["system"]["ID"];
						$field_parent = (isset($arrFieldOverride[$field_key]["parent"])
							? $arrFieldOverride[$field_key]["parent"]
							: $arrField["system"][$mode]["parent"]
						);

						$vg_field["fields"][$ID_type][$field_parent][$field_key] = array_merge($arrField["base"], $arrField[$mode]);

						if (isset($arrFieldOverride[$field_key]))
						    $vg_field["fields"][$ID_type][$field_parent][$field_key] = array_replace($vg_field["fields"][$ID_type][$field_parent][$field_key], $arrFieldOverride[$field_key]);

						if (strlen($vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"])) {
						    parse_str(str_replace('"', '', $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]), $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]);

						    if (isset($vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"]))
							$vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"] = array("default" => $vg_field["fields"][$ID_type][$field_parent][$field_key]["htmltag_attr"]["class"]);
						}

						$vg_field["fields"][$ID_type][$field_parent][$field_key]["group"] = (is_array($vg_father["properties"]["image"]["fields"]) && array_search($field_key, $vg_father["properties"]["image"]["fields"]) !== false 
							? "img" 
							: "desc"
						);

						if (is_array($loaded_default_fields["params"][$field_key]))
						    $vg_field = array_replace_recursive($vg_field, $loaded_default_fields["params"][$field_key]);

						
				    }
				}
		    }
                    
		    if ($vg_father["search"]) {
				if ($vg_father["search"]["limit"]) {
				    $vg_field["search"]["preload"] = $vg_field["preload"];
				    $vg_field["search"]["selection"] = $vg_field["selection"];
				} else {
				    $vg_field["search"] = $loaded_default_fields["search"];
				}
                                
				if(is_array($vg_father["search"]["params"]) && count($vg_father["search"]["params"]))
					$vg_father["search"]["encoded_params"] = implode("&", $vg_father["search"]["params"]);
		    }

		    //$vg_field["relationship"] = $loaded_default_fields["relationship"];
		    if(is_array($vg_field["fields"]))
		    	$vg_field["type"] = array_keys($vg_field["fields"]);

		    if ($actual_field_schema["sub"] === null) {
				$loaded_fields[$actual_field_schema["macro"]] = (is_array($loaded_fields[$actual_field_schema["macro"]]) 
					? array_replace_recursive($loaded_fields[$actual_field_schema["macro"]], $vg_field) 
					: $vg_field
				);
		    } else {
				$loaded_fields[$actual_field_schema["macro"]][$actual_field_schema["sub"]] = (is_array($loaded_fields[$actual_field_schema["macro"]][$actual_field_schema["sub"]]) 
					? array_replace_recursive($loaded_fields[$actual_field_schema["macro"]][$actual_field_schema["sub"]], $vg_field) 
					: $vg_field
				);
		    }
		}
    }
	
    if (is_array($vg_field["type"])) {
	if (is_array($vg_father["limit_type"]))
	    $vg_father["limit_type"] = array_replace($vg_father["limit_type"], array_values($vg_field["type"]));
	else
	    $vg_father["limit_type"] = $vg_field["type"];
    }
    //print_r($vg_father);
    //print_r($vg_field);

    return $vg_field;
}

function process_vgallery_node_relationship($vg_father, $arrKey, $arrRelVGallery) {
    $res = array();
    if (is_array($arrRelVGallery) && count($arrRelVGallery) && check_function("get_layout_settings")) {
		foreach ($arrRelVGallery AS $src_table => $src_data) {
			$layouts = get_layout_by_block($src_table, $src_data["fullpath"], "layouts");

		    switch ($src_table) {
			case "anagraph": //TODO: da finire
			    $tmp = process_vgallery_node_relationship_anagraph($vg_father, $arrKey, $src_data, $layouts);
			    if($tmp) {
					$res[$src_table] = $tmp;
					$res[$src_table]["src"]["type"] = "anagraph";
					$res[$src_table]["src"]["table"] = "anagraph";
					$res[$src_table]["enable_lang"] = false;
			    }
			    break;
			case "files":
			    $tmp = process_vgallery_node_relationship_files($vg_father, $arrKey, $src_data, $layouts);
			    if($tmp) {
					$res[$src_table] = $tmp;
					$res[$src_table]["src"]["type"] = "files";
					$res[$src_table]["src"]["table"] = "files";
					$res[$src_table]["enable_lang"] = true;
			    }
			    break;
			default:
			    $tmp = process_vgallery_node_relationship_vgallery($vg_father, $arrKey, $src_data, $layouts);
			    if($tmp) {
					$res[$src_table] = $tmp;
					$res[$src_table]["src"]["type"] = "vgallery";
					$res[$src_table]["src"]["nodes"] = "vgallery_nodes";
					$res[$src_table]["enable_lang"] = $vg_father["enable_multilang_visible"];
			    }
		    }
		    
		    if($res[$src_table]) {
				$res[$src_table]["fields"] = $src_data["fields"];

				//print_r($arrLayout);
				//$res[$src_table]["layout"]
				/*if (!is_array($res[$src_table]["fields"]))
				    $res[$src_table]["fields"] = array();

				foreach($src_data AS $src_ctx => $src_params) {
				    $res[$src_table]["fields"] = array_merge($res[$src_table]["fields"], $src_params['dst_field']);
				}*/
		    }
		}
    }

    return $res;
}
function process_vgallery_node_relationship_data($src_table, $sSQL, $vg_father, $layouts) 
{
    $db = ffDB_Sql::factory();
    $res = null;
    //$vg_data_rel = array();
     // print_r($arrRel);
    if(strlen($sSQL)) {
	$db->query($sSQL);
	if ($db->nextRecord()) {
	    do {   
            $vg_node_rel = array();
		
            $ID_source_node = $db->getField("source_node", "Number", true);
		    $ID_node = $db->getField("ID", "Number", true);
		    $ID_type = $db->getField("ID_type", "Number", true);
		    $vgallery_type = $db->getField("vgallery_type", "Text", true);
		    $ID_cart_detail = $db->getField("ID_cart_detail", "Number", true);
		    $ID_vgallery = $db->getField("ID_vgallery", "Number", true);
		    $vgallery_name = $db->getField("vgallery_name", "Text", true);
            //$ID_source_field = $arrRel[$vgallery_name]["src_field"]; 
            //TODO: alterando l'id field si possono aggregare i dati
                       // $ID_source_field = 73;

           // if(!$ID_source_field)
            //    continue;

		    if ($db->getField("enable_ecommerce", "Number", true) && AREA_SHOW_ECOMMERCE && $db->getField("use_pricelist_as_item", "Number", true)) {
		        $ID_pricelist = $db->getField("ID_pricelist", "Number", true);
		        $unic_id_node = $ID_node . "-" . $ID_pricelist;

		        //da verificare se serve
		        //$pricelist["nodes"][$ID_node] = $ID_node;

		        $vg_node_rel["pricelist"]["ID"] = $ID_pricelist;
		        $vg_node_rel["pricelist"]["range"]["since"] = $db->getField("pricelist_since", "Number", true);
		        $vg_node_rel["pricelist"]["range"]["to"] = $db->getField("pricelist_to", "Number", true);
		    } else {
		        $unic_id_node = $ID_node;

		        $vg_node_rel["pricelist"] = null;
		    }

		    //$vg_key["keys"][$ID_source_field][$ID_node] = $ID_source_node;
		    $vg_key["keys"][$vgallery_name][$ID_node] = $ID_source_node;
		    $vg_key["nodes"][$ID_node] = $ID_node; //serve per la query delle relationship 
            $vg_key["type"][$ID_type] = $ID_type;
            
            //$vg_key["src_nodes"][$unic_id_node] = $ID_source_field;

		    //$vg_node_rel["ID_cart_detail"] = null;

		    /**
		     * Preload data if exists... 
		     * prefix_field: 'data_'
		     */
		    $vg_node_rel["ID"] = $ID_node;
		    $vg_node_rel["parent"] = $db->getField("parent", "Text", true);
		    $vg_node_rel["name"] = $db->getField("name", "Text", true);
		    $vg_node_rel["ID_type"] = $ID_type;
		    $vg_node_rel["type"] = $vgallery_type;
		    $vg_node_rel["is_dir"] = $db->getField("is_dir", "Number", true);
		    //$vg_node_rel["data_type_publish"] = "";
		    //$vg_node_rel["vgallery_name"] = $db->getField("vgallery_name", "Text", true);	        

			$vg_node_rel["created"] 									= $db->getField("created", "Number", true);
			$vg_node_rel["last_update"] 								= $db->getField("last_update", "Number", true);
			$vg_node_rel["published"] 									= $db->getField("published", "Number", true);
			$vg_node_rel["owner"] 										= $db->getField("owner", "Number", true);

		    $vg_node_rel["class"] = $db->getField("class", "Text", true);
		    $vg_node_rel["priority"] = str_pad($db->getField("rel_order", "Number", true), 4, "0", STR_PAD_LEFT) . "-" . (9 - $db->getField("priority", "Number", true));

			//Highlight System
			$vg_node_rel["highlight"]["container"] 	= $db->getField("highlight_container", "Text", true);
			$vg_node_rel["highlight"]["image"]["src"] = get_image_properties_by_grid_system(
				$db->getField("highlight_image", "Number", true)
				, $db->getField("highlight_image_md", "Number", true)
				, $db->getField("highlight_image_sm", "Number", true)
				, $db->getField("highlight_image_xs", "Number", true)
			);
		    
		    //$vg_node_rel["enable_multilang_visible"] = $db->getField("enable_multilang_visible", "Number", true);
		    //$vg_node_rel["is_wishlisted"] = false;
		    $vg_node_rel["tags"]                            = $db->getField("tags", "Text", true);
            if($vg_node_rel["tags"])
                $vg_father["seo"]["tags"]["rel"]            .= ($vg_father["seo"]["tags"]["rel"] ? "," : "") . $vg_node_rel["tags"];

		    if (array_key_exists("permalink", $db->record)) {
			    $vg_node_rel["permalink"] 					= $db->record["permalink"];
			    $vg_node_rel["smart_url"] 					= basename($db->record["permalink"]);
			    $vg_node_rel["permalink_parent"] 			= ffcommon_dirname($db->record["permalink"]);
			    $db->record["permalink_parent"] 			= $vg_node_rel["permalink_parent"];
		    } else {
			    if (array_key_exists("smart_url", $db->record))
				    $vg_node_rel["smart_url"] 				= $db->record["smart_url"];
			    if (array_key_exists("parent", $db->record))
				    $vg_node_rel["permalink_parent"] 		= $db->record["parent"];
		    }

		    if(array_key_exists("meta_title", $db->record))
			    $vg_node_rel["meta"]["title"] 				= $db->record["meta_title"];
		    if(array_key_exists("meta_title_alt", $db->record))
			    $vg_node_rel["meta"]["title_h1"] 			= $db->record["meta_title_alt"];
		    if(array_key_exists("meta_description", $db->record))
			    $vg_node_rel["meta"]["description"]			= $db->record["meta_description"];
		    if(array_key_exists("keywords", $db->record))
			    $vg_node_rel["meta"]["keywords"] 			= $db->record["keywords"];

			if($db->record["geo.position"])
				$vg_node_rel["meta"]["geo.position"] = $db->record["geo.position"];
			if($db->record["geo.placename"])
				$vg_node_rel["meta"]["geo.placename"] = $db->record["geo.placename"];
			if($db->record["geo.region"])
				$vg_node_rel["meta"]["geo.region"] = $db->record["geo.region"];

		  //  $vg_node_rel["src_field"] = $ID_source_field;
		    if (!isset($vg_father_rel[$vgallery_name])) { 
                $vg_father_rel[$vgallery_name]["ID_vgallery"] = $ID_vgallery;
		        $vg_father_rel[$vgallery_name]["parent"] = "/" . ffCommon_url_rewrite($vgallery_name);
		        $vg_father_rel[$vgallery_name]["ID_node"] = 0;
		        $vg_father_rel[$vgallery_name]["vgallery_name"] = $vgallery_name;
		        $vg_father_rel[$vgallery_name]["vgallery_class"] = ffCommon_url_rewrite($vgallery_name);
		        $vg_father_rel[$vgallery_name]["vgallery_type"] = "Directory";
		        $vg_father_rel[$vgallery_name]["is_dir"] = true;
		        $vg_father_rel[$vgallery_name]["limit_level"] = $db->getField("limit_level", "Text", true);
		        $vg_father_rel[$vgallery_name]["enable_ecommerce"] = $db->getField("enable_ecommerce", "Number", true);
		        $vg_father_rel[$vgallery_name]["use_pricelist_as_item"] = $db->getField("use_pricelist_as_item", "Number", true);
		        $vg_father_rel[$vgallery_name]["enable_multilang_visible"] = $db->getField("enable_multilang_visible", "Number", true);


		        /* if(!$vg_data_rel[$ID_source_node]["father"]["source_user_path"] && $db->getField("source_user_path", "Text", true)) {
		          if(LANGUAGE_INSET != LANGUAGE_DEFAULT && check_function("normalize_url"))
		          $vg_data_rel[$ID_source_node]["father"]["source_user_path"]			= normalize_url($db->getField("source_user_path", "Text", true), HIDE_EXT, true, LANGUAGE_INSET);
		          else
		          $vg_data_rel[$ID_source_node]["father"]["source_user_path"] 		= $db->getField("source_user_path", "Text", true);
		          } */

		        //$vg_father_rel[$vgallery_name]["limit"]["fields"] = $arrRel[$vgallery_name]["dst_field"];
		        $vg_father_rel[$vgallery_name]["available"] = true;
		        $vg_father_rel[$vgallery_name]["permission"]["visible"] = true;
		        $vg_father_rel[$vgallery_name]["permission"]["owner"] = $db->getField("owner", "Number", true);
		        
		        $vg_father_rel[$vgallery_name]["layout"] = $layouts[$vg_father_rel[$vgallery_name]["parent"]];
		    } elseif($vg_father_rel[$vgallery_name]["parent"] != "/" && $vg_father_rel[$vgallery_name]["parent"] != "/" . ffCommon_url_rewrite($vgallery_name)) { //todo: da fixare
		    	$vg_father_rel[$vgallery_name]["parent"] = "/";
		    	$vg_father_rel[$vgallery_name]["layout"] = $layouts[$vg_father_rel[$vgallery_name]["parent"]];
		    }

		    //$vg_data_rel[$unic_id_node]["preload"]["vgallery_nodes"] = $db->record; 
            
            //$vg_data_rel[$ID_source_node]["nodes"][$unic_id_node] = $vg_node_rel;
            
            $vg_father_rel[$vgallery_name]["nodes"][$unic_id_node] = $vg_node_rel;
		    $vg_father_rel[$vgallery_name]["nodes"][$unic_id_node]["preload"][$src_table] = $db->record;
		
	    } while ($db->nextRecord());

	    $res = array(
		/*"data" => $vg_data_rel
        ,*/ "father" => $vg_father_rel
		, "keys" => $vg_key["keys"]
        //, "src_nodes" => $vg_key["src_nodes"]
		, "nodes" => $vg_key["nodes"]
		, "type" => $vg_key["type"]
	    );
	}
    }
    return $res;    
}

function process_vgallery_node_relationship_files($vg_father, $arrKey, $arrRel, $layouts) 
{
    $db = ffDB_Sql::factory();
    $str_rel_vgallery = "'" . implode("', '", array_keys($arrRel)) . "'";
    //da fare
    $sSQL = "";
    
    $res = process_vgallery_node_relationship_data("files", $sSQL, $vg_father, $layouts);
   
    return $res; 
}

function process_vgallery_node_relationship_anagraph($vg_father, $arrKey, $arrRel, $layouts) 
{
    $db = ffDB_Sql::factory();
    
//    $str_rel_vgallery = "'" . implode("', '", array_keys($arrRel)) . "'";
	foreach($arrRel["rel"] AS $ctx => $arrRel_params) {
    	$str_rel_vgallery = "'" . implode("', '", $arrRel_params["tbl"]) . "'";
        $query_filter = process_query_filter_node("vgallery_nodes");
		if($ctx == "default") {
            $query[$ctx . "-src"] = $query_filter;
			$query[$ctx . "-src"]["select"]["source"] = "rel_nodes.ID_node_dst								AS `source_node`";
			$query[$ctx . "-src"]["select"]["order"] = "rel_nodes.`order`									AS `rel_order`";
			$query[$ctx . "-src"]["from"]["rel"] = " 
				INNER JOIN rel_nodes ON 
					rel_nodes.ID_node_src = anagraph.ID 
			        AND rel_nodes.contest_src = 'anagraph'
			        AND rel_nodes.contest_dst = " . $db->toSql($vg_father["vgallery_name"]) . " 
			        AND rel_nodes.ID_node_dst IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";
            
            $query[$ctx . "-dst"] = $query_filter;
			$query[$ctx . "-dst"]["select"]["source"] = "rel_nodes.ID_node_src								AS `source_node`";			        
			$query[$ctx . "-dst"]["select"]["order"] = "rel_nodes.`order`									AS `rel_order`";
			$query[$ctx . "-dst"]["from"]["rel"] = " 
				INNER JOIN rel_nodes ON 
					rel_nodes.ID_node_dst = anagraph.ID 
				    AND rel_nodes.contest_dst = 'anagraph'
				    AND rel_nodes.contest_src = " . $db->toSql($vg_father["vgallery_name"]) . " 
				    AND rel_nodes.ID_node_src IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";
		} else {
            $query[$ctx] = $query_filter;
			$query[$ctx]["select"]["source"] = "rel_" . $ctx . ".ID                                       	AS `source_node`";		
			$query[$ctx]["select"]["order"] = "rel_" . $ctx . ".`order`										AS `rel_order`";
			$query[$ctx]["from"]["rel"] = " 
				INNER JOIN `" . $ctx . "` AS rel_" . $ctx . " ON 
					vgallery_nodes.`" . $arrRel_params["field"] . "` = rel_" . $ctx . ".ID
			        AND rel_" . $ctx . ".ID IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";
		}
	}

    //da arricchire con tutti i campi utili anche per l'ecommerce eventuale
    /*
      , IF(LOCATE(CONCAT('/', layout.value), vgallery_nodes.parent) = 1
      , CONCAT(
      IF(ISNULL(layout_path.path)
      , ''
      , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
      , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))
      , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) > 1
      , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(layout.params, layout_path.path)))
      , layout_path.path
      )
      )
      )
      , SUBSTRING(vgallery_nodes.parent, LENGTH(CONCAT('/', layout.value)) + 1)
      )
      , vgallery_nodes.parent
      ) AS source_user_path
      LEFT JOIN layout ON layout.value = vgallery.name
      LEFT JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0


     */

	 if(is_array($query) && count($query)) {
	 	foreach($query AS $query_value) {
	    	$arrSQL[] = "SELECT 
					anagraph.ID											AS ID 
	                , anagraph.parent									AS parent 
	                , anagraph.name 									AS name 
	                , anagraph.ID_type									AS ID_type 
	                , 0													AS is_dir 
					, anagraph.`created`								AS `created`
					, anagraph.`last_update`							AS `last_update`
					, anagraph.`published_at`							AS `published_at`
					, anagraph.class									AS class 
					, anagraph.`highlight`								AS `highlight_container`
					, anagraph.`highlight_ID_image`						AS `highlight_image`
					, anagraph.`highlight_ID_image_md`					AS `highlight_image_md`
					, anagraph.`highlight_ID_image_sm`					AS `highlight_image_sm`
					, anagraph.`highlight_ID_image_xs`					AS `highlight_image_xs`
	                , anagraph.owner 									AS owner
		            , anagraph.visible 									AS `visible`
		            , anagraph.`permalink`          					AS `permalink`
		            , anagraph.`smart_url`              				AS `smart_url`
		            , anagraph.`meta_title`             				AS `meta_title`
		            , anagraph.`meta_description`       				AS `meta_description`
		            , anagraph.`keywords`               				AS `keywords`
	                , anagraph.tags										AS tags
	                , 0													AS ID_vgallery
	                , anagraph.priority 								AS `priority`
					, 'anagraph'										AS `vgallery_name`
					, anagraph_type.`name` 								AS `vgallery_type`
					, 1													AS `limit_level`
					, 0													AS `enable_ecommerce`
					, 0													AS `use_pricelist_as_item`
					, 0													AS `enable_multilang_visible`
	                " . (is_array($query_value["select"]) 
                        ? ", " . implode(", ", $query_value["select"]) 
                        : ""
                    ) . "
	            FROM anagraph
	            	INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type
					" . (is_array($query_value["from"]) 
                        ? " " . implode(" ", $query_value["from"]) 
                        : ""
                    ) . "
	                WHERE 
						" . (is_array($query_value["where"]) 
                            ? implode(" AND ", $query_value["where"])
                            : " 1 "
                        ) . "
	                ORDER BY "
                        . (is_array($query_value["order"]) 
                            ? implode(", ", $query_value["order"])
                            : "ID"
                        );
		}
	 }
	 
	 if(is_array($arrSQL) && count($arrSQL)) {
	 	$sSQL = "(" . implode(") UNION (", $arrSQL) . ")";	 
   	 	
   	 	$res = process_vgallery_node_relationship_data("anagraph", $sSQL, $vg_father, $layouts);
	 }
    return $res;
}

function process_vgallery_node_relationship_vgallery($vg_father, $arrKey, $arrRel, $layouts) 
{
    $db = ffDB_Sql::factory();
    //print_r($arrRel);
    //$str_rel_vgallery = "'" . implode("', '", array_keys($arrRel)) . "'";
	foreach($arrRel["rel"] AS $ctx => $arrRel_params) {
    	$str_rel_vgallery = "'" . implode("', '", $arrRel_params["tbl"]) . "'";
        $query_filter = process_query_filter_node("vgallery_nodes");
        
		if($ctx == "default") {
            $query[$ctx . "-src"] = $query_filter;
			$query[$ctx . "-src"]["select"]["source"] = "rel_nodes.ID_node_dst								AS `source_node`";
			$query[$ctx . "-src"]["select"]["order"] = "rel_nodes.`order`									AS `rel_order`";
			$query[$ctx . "-src"]["from"]["rel"] = " 
				INNER JOIN rel_nodes ON 
					rel_nodes.ID_node_src = vgallery_nodes.ID 
			        AND rel_nodes.contest_src IN (" . $str_rel_vgallery . ")
			        AND rel_nodes.contest_dst = " . $db->toSql($vg_father["vgallery_name"]) . " 
			        AND rel_nodes.ID_node_dst IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";

            $query[$ctx . "-dst"] = $query_filter;
			$query[$ctx . "-dst"]["select"]["source"] = "rel_nodes.ID_node_src								AS `source_node`";		
			$query[$ctx . "-dst"]["select"]["order"] = "rel_nodes.`order`									AS `rel_order`";
			$query[$ctx . "-dst"]["from"]["rel"] = " 
				INNER JOIN rel_nodes ON 
					rel_nodes.ID_node_dst = vgallery_nodes.ID 
				    AND rel_nodes.contest_dst IN (" . $str_rel_vgallery . ")
				    AND rel_nodes.contest_src = " . $db->toSql($vg_father["vgallery_name"]) . " 
				    AND rel_nodes.ID_node_src IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";
		} else {
            $query[$ctx] = $query_filter;
			$query[$ctx]["select"]["source"] = "rel_" . $ctx . ".ID											AS `source_node`";		
			$query[$ctx]["select"]["order"] = "rel_" . $ctx . ".`order`										AS `rel_order`";
			$query[$ctx]["from"]["rel"] = " 
				INNER JOIN `" . $ctx . "` AS rel_" . $ctx . " ON 
					vgallery_nodes.`" . $arrRel_params["field"] . "` = rel_" . $ctx . ".ID
			        AND rel_" . $ctx . ".ID IN ( " . $db->toSql(implode(",", $arrKey), "Text", false) . ")";
		    $query[$ctx]["where"]["rel"] = "vgallery.name IN (" . $str_rel_vgallery . ")";
		}
	}

    //$str_rel_vgallery = "'" . implode("', '", array_keys($arrRel)) . "'";

    //da arricchire con tutti i campi utili anche per l'ecommerce eventuale
    /*
      , IF(LOCATE(CONCAT('/', layout.value), vgallery_nodes.parent) = 1
      , CONCAT(
      IF(ISNULL(layout_path.path)
      , ''
      , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) = 1
      , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, LOCATE(layout.params, layout_path.path) + LENGTH(layout.params)))
      , IF(LENGTH(layout.params) > 1 AND LOCATE(layout.params, layout_path.path) > 1
      , TRIM(TRAILING '/' FROM SUBSTRING(layout_path.path, 1, LOCATE(layout.params, layout_path.path)))
      , layout_path.path
      )
      )
      )
      , SUBSTRING(vgallery_nodes.parent, LENGTH(CONCAT('/', layout.value)) + 1)
      )
      , vgallery_nodes.parent
      ) AS source_user_path
      LEFT JOIN layout ON layout.value = vgallery.name
      LEFT JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.visible > 0


     */
	 if(is_array($query) && count($query)) {
	 	foreach($query AS $query_value) {
     		$arrSQL[] = " SELECT 
				vgallery_nodes.ID 									AS ID 
                , vgallery_nodes.name 								AS name 
                , vgallery_nodes.parent 							AS parent 
                , vgallery_nodes.ID_type 							AS ID_type 
                , vgallery_nodes.is_dir 							AS is_dir 
				, vgallery_nodes.`created`							AS `created`
				, vgallery_nodes.`last_update`						AS `last_update`
				, vgallery_nodes.`published_at`						AS `published_at`
				, vgallery_nodes.class								AS class 
				, vgallery_nodes.`highlight`						AS `highlight_container`
				, vgallery_nodes.`highlight_ID_image`				AS `highlight_image`
				, vgallery_nodes.`highlight_ID_image_md`			AS `highlight_image_md`
				, vgallery_nodes.`highlight_ID_image_sm`			AS `highlight_image_sm`
				, vgallery_nodes.`highlight_ID_image_xs`			AS `highlight_image_xs`
                , vgallery_nodes.owner 								AS owner
                , vgallery_nodes.tags								AS tags
                , vgallery_nodes.ID_vgallery						AS ID_vgallery
				, vgallery_nodes.`priority`							AS `priority`
				, vgallery.`name` 									AS `vgallery_name`
				, vgallery_type.`name` 								AS `vgallery_type`
				, vgallery.`limit_level`							AS `limit_level`
				, vgallery.`enable_ecommerce`						AS `enable_ecommerce`
				, vgallery.`use_pricelist_as_item_thumb`			AS `use_pricelist_as_item`
				, vgallery.`enable_multilang_visible`				AS `enable_multilang_visible`
				" . (OLD_VGALLERY
					? "
						, vgallery_nodes.visible 							AS `visible`			    
					"
					: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
						? "
							, vgallery_nodes.permalink							AS permalink
							, vgallery_nodes.keywords							AS keywords
							, vgallery_nodes.meta_description					AS meta_description
							, vgallery_nodes.meta_title							AS meta_title
							, vgallery_nodes.meta_title_alt						AS meta_title_alt
							, vgallery_nodes.parent								AS permalink_parent
							, vgallery_nodes.name								AS smart_url
							, vgallery_nodes.visible 							AS `visible`			    

						"
						: "
							, vgallery_nodes_rel_languages.permalink			AS permalink
							, vgallery_nodes_rel_languages.keywords				AS keywords
							, vgallery_nodes_rel_languages.meta_description		AS meta_description
							, vgallery_nodes_rel_languages.meta_title			AS meta_title
							, vgallery_nodes_rel_languages.meta_title_alt		AS meta_title_alt
							, vgallery_nodes_rel_languages.permalink_parent		AS permalink_parent
							, vgallery_nodes_rel_languages.smart_url			AS smart_url
					        , " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
								? " vgallery_nodes_rel_languages.visible "
								: " vgallery_nodes.visible "
							) . "												AS `visible`
						"
					)
				) . "				
                " . (0 && $vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] ? "
                        , ecommerce_pricelist.ID AS ID_pricelist
                        , IF(ecommerce_settings.`type` = 'bytime'
                            , ecommerce_pricelist.date_since
                            , ecommerce_pricelist.qta_since
                        ) AS pricelist_since
                        , IF(ecommerce_settings.`type` = 'bytime'
                            , ecommerce_pricelist.date_to
                            , ecommerce_pricelist.qta_to
                        ) AS pricelist_to
                    " : "
                        , 0 AS ID_pricelist
                        , 0 AS pricelist_since
                        , 0 AS pricelist_to
                    "
	    		) . "
                " . (is_array($query_value["select"]) 
                    ? ", " . implode(", ", $query_value["select"]) 
                    : ""
                ) . "
            FROM
                vgallery_nodes
                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_nodes.ID_type
				" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
					? ""
					: " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
							AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
				)
                . (is_array($query_value["from"]) 
                    ? " " . implode(" ", $query_value["from"]) 
                    : ""
                )
                . (0 && $vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && ($vg_father["use_pricelist_as_item"] || AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK) ? "    INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID 
		            " . ($vg_father["use_pricelist_as_item"] 
		            	? " INNER JOIN ecommerce_pricelist ON ecommerce_pricelist.ID_ecommerce_settings = ecommerce_settings.ID
		                    " . (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
		                    	? " AND ecommerce_pricelist.actual_qta > 0 " 
		                    	: ""
					    	) 
					    : (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
					    	? " AND ecommerce_settings.actual_qta > 0 " 
					    	: ""
					    )
				    ) : ""
			    ) . " 
            WHERE 
	            " . (is_array($query_value["where"]) 
                    ? implode(" AND ", $query_value["where"])
                    : " 1 "
                ) 
	            . (0 && $vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] 
		    		? " AND (
		                        (
		                            ecommerce_settings.`type` = 'bytime'
		                            AND ecommerce_pricelist.date_since > 0
		                            AND ecommerce_pricelist.date_to > 0
		                            AND ecommerce_pricelist.qta_since = 0
		                            AND ecommerce_pricelist.qta_to = 0
		                            AND FROM_UNIXTIME(ecommerce_pricelist.date_since, '%Y') = YEAR(CURDATE())
		                            AND FROM_UNIXTIME(ecommerce_pricelist.date_to, '%Y') = YEAR(CURDATE())
		                        )
		                        OR 
		                        (
		                            ecommerce_settings.`type` = 'byqta'
		                            AND ecommerce_pricelist.date_since = 0
		                            AND ecommerce_pricelist.date_to = 0
		                            AND ecommerce_pricelist.qta_since > 0
		                            AND ecommerce_pricelist.qta_to > 0
		                        )
		                    )
		            " 
		            : ""
				) . "
	            AND vgallery_nodes.name <> ''	
            ORDER BY " 
                . (is_array($query_value["order"]) 
                    ? implode(", ", $query_value["order"])
                    : "ID"
                );
		}	 
	 } 

	 if(is_array($arrSQL) && count($arrSQL)) {
	 	$sSQL = "(" . implode(") UNION (", $arrSQL) . ")";

    //print_r($sSQL);
   		$res = process_vgallery_node_relationship_data("vgallery_nodes", $sSQL, $vg_father, $layouts);
   		
   		//print_r($res);
    }
    return $res;
}

function process_vgallery_node_data($vg_father, $vg_field, &$vg, $arrData = array()) {
    $db = ffDB_Sql::factory();

    if (isset($arrData[$vg_father["src"]["table"]])) {
		$arrData[$vg_father["src"]["table"]] = array_merge_recursive($arrData[$vg_father["src"]["table"]], array(
		    "nodes" => $vg["key"]
		    , "fields" => $vg_field["keys"]
		    , "params" => $vg_father["request_params"]
		));
    } else {
		$arrData[$vg_father["src"]["table"]] = array(
		    "nodes" => $vg["key"]
		    , "fields" => $vg_field["keys"]
		    , "params" => $vg_father["request_params"]
		);
    }

    if ($vg_father["src"]["type"] == "anagraph") {
		$arrData[$vg_father["src"]["table"]]["src"]["type"] = "anagraph";
		$arrData[$vg_father["src"]["table"]]["src"]["table"] = "anagraph";
		$arrData[$vg_father["src"]["table"]]["enable_lang"] = false;
    } else {
		$arrData[$vg_father["src"]["table"]]["src"]["type"] = "vgallery";
		$arrData[$vg_father["src"]["table"]]["src"]["table"] = "vgallery_nodes";
		$arrData[$vg_father["src"]["table"]]["enable_lang"] = $vg_father["enable_multilang_visible"];

		if ($vg_father["type"] == "wishlist") {
		    $arrData[$vg_father["src"]["table"]]["join"] = "
		            INNER JOIN ecommerce_order_detail ON ecommerce_order_detail.ID_order = " . $db->toSql($vg_father["wishlist"]["ID"], "Number") . "
		                AND ecommerce_order_detail.ID_items = vgallery_rel_nodes_fields.ID_nodes
				";
		} elseif ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"]) {
		    $arrData[$vg_father["src"]["table"]]["join"] = "
	                INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_rel_nodes_fields.ID_nodes
	                INNER JOIN ecommerce_pricelist ON ecommerce_pricelist.ID_ecommerce_settings = ecommerce_settings.ID
				";
		}
    }

    //echo "---------------------------------------";
    foreach ($arrData AS $tbl => $tbl_data) {
    	if(!$tbl_data["fields"])
    		continue;
    		//TODO: DA FINIRE LA GESTION DELLE ANAGRAPH
    		//medici-online/punti-di-vista/aborto-terapeutico?__nocache__
    		//autore pdv2 non  si vede. Dovrebbe apparire come autore pdv che si vede bene
       /* if(is_array($tbl_data["father"]) && count($tbl_data["father"])) { 
            foreach($tbl_data["father"] AS $ID_source_field => $father_data) {
            	if(is_array($tbl_data["keys"][$ID_source_field])) {
            		foreach($tbl_data["keys"][$ID_source_field] AS $ID_node => $ID_source_node) {
            			$arrNodeRelSrcField[$ID_node]["ID_source_field"] = $ID_source_field;
            			$arrNodeRelSrcField[$ID_node]["nodes"][$ID_source_node] = $ID_source_node;
		                if (!is_array($vg["data"][$ID_source_node]["data"][$ID_source_field])) {
		                    $vg["data"][$ID_source_node]["data"][$ID_source_field] = $father_data;
		                    $vg["data"][$ID_source_node]["data"][$ID_source_field]["src"] = $tbl_data["src"];
		                   // $vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"] = array();
		                }
					}
				}
            }
        }*/

        //print_r($tbl_data);
       // print_r($vg);
		$sSQL = "SELECT 
		        " . $tbl_data["src"]["type"] . "_rel_nodes_fields.`ID` 									AS `ID`
		        , " . $tbl_data["src"]["type"] . "_rel_nodes_fields.`description` 						AS `description`
		        , " . $tbl_data["src"]["type"] . "_rel_nodes_fields.`ID_fields` 						AS `ID_fields`
		        , " . $tbl_data["src"]["type"] . "_rel_nodes_fields.`ID_nodes` 							AS `ID_nodes`
		        , " . $tbl_data["src"]["type"] . "_rel_nodes_fields.`limit` 							AS `limit`
		    FROM " . $tbl_data["src"]["type"] . "_rel_nodes_fields 
		        INNER JOIN " . $tbl_data["src"]["type"] . "_fields ON " . $tbl_data["src"]["type"] . "_fields.ID = " . $tbl_data["src"]["type"] . "_rel_nodes_fields.ID_fields
		        INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = " . $tbl_data["src"]["type"] . "_fields.ID_data_type
		        " . ($tbl_data["join"] ? $tbl_data["join"] : ""
			) . "
		    WHERE ID_nodes IN (" . $db->toSql(implode(",", $tbl_data["nodes"]), "Text", false) . ") 
	        	AND " . $tbl_data["src"]["type"] . "_rel_nodes_fields.ID_fields IN (" . $db->toSql(implode(",", array_filter($tbl_data["fields"])), "Text", false) . ")
				" . ($tbl_data["enable_lang"] 
                    ? " AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_lang = " . ($vg_father["enable_multilang_visible"] 
							? "IF(" . $tbl_data["src"]["type"] . "_fields.disable_multilang > 0 
									OR " . $tbl_data["src"]["type"] . "_fields_data_type.name IN('relationship', 'media') 
                                		, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
                                		, " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                            )" 
                            : $db->toSql(LANGUAGE_DEFAULT_ID, "Number")
					)
					: ""
				) . "
		    ORDER BY " . (is_array($vg_field["relationship"][$tbl]["fields"]) && count($vg_field["relationship"][$tbl]["fields"])
		    		? " FIELD(" . $tbl_data["src"]["type"] . "_rel_nodes_fields.ID_fields, " . $db->toSql(implode(",", $vg_field["relationship"][$tbl]["fields"]), "Text", false) . ") DESC, "
		    		: ""
		    	) . $tbl_data["src"]["type"] . "_rel_nodes_fields.ID_nodes";
		$db->query($sSQL);
		if ($db->nextRecord()) {
            $rel_data = array();
			$count_field_by_rel_tbl = array_count_values($vg_field["relationship"][$tbl]["src"]);
		
		    do {
				$ID_actual_node = $db->getField("ID_nodes", "Number", true);
				$actual_field_key = $db->getField("ID_fields", "Number", true);
				$actual_value = $db->getField("description", "Text", true);
				
				$arrFields[$actual_field_key] = $actual_field_key;
				if (array_key_exists($ID_actual_node, $vg["data"])) {
					if($vg_field["relationship"][$tbl]["src"][$actual_field_key]) {
						$rel_tbl = $vg_field["relationship"][$tbl]["src"][$actual_field_key];
						$rel_nodes = array_keys($tbl_data["keys"][$rel_tbl], $ID_actual_node);

						if($count_field_by_rel_tbl[$rel_tbl] > 1) {
							$rel_limit = explode(",", $db->getField("limit", "Text", true));
							if(is_array($rel_limit) && count($rel_limit)) {
								$rel_nodes = array_intersect($rel_nodes, $rel_limit);
							}
						}

					    if (!is_array($vg["data"][$ID_actual_node]["data"][$actual_field_key])) {
					    	$vg["data"][$ID_actual_node]["data"][$actual_field_key] = $tbl_data["father"][$rel_tbl];
							$vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"] = array_intersect_key($tbl_data["father"][$rel_tbl]["nodes"], array_fill_keys($rel_nodes, array()));
							if(is_array($rel_data) && count($rel_data)) {
								$rel_data_field = array_intersect_key($rel_data, $vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"]);
								$vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"] = array_replace_recursive($vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"], $rel_data_field);
							}
						}
						
					} else {
						if (!isset($vg["data"][$ID_actual_node]["data"][$actual_field_key]))
							$vg["data"][$ID_actual_node]["data"][$actual_field_key] = $actual_value;
//						if (!isset($vg["data"][$ID_actual_node]["limit"][$actual_field_key]) && strlen($db->getField("limit", "Text", true)))
//							$vg["data"][$ID_actual_node]["limit"][$actual_field_key] = $db->getField("limit", "Text", true);
//						if (!isset($vg["data"][$ID_actual_node]["js_request"][$actual_field_key]) && strlen($db->getField("js_request", "Text", true)))
//							$vg["data"][$ID_actual_node]["js_request"][$actual_field_key] = $db->getField("js_request", "Text", true);
						if (!isset($vg["data"][$ID_actual_node]["ID_data_node"][$actual_field_key]))
							$vg["data"][$ID_actual_node]["ID_data_node"][$actual_field_key] = $db->getField("ID", "Number", true);
//						if (!isset($vg["data"][$ID_actual_node]["check_nodes"][$actual_field_key]))
//							$vg["data"][$ID_actual_node]["check_nodes"][$actual_field_key] = $db->getField("check_nodes", "Number", true);
					}
				} elseif($vg_field["relationship"][$tbl]["fields"][$actual_field_key]) {
					$rel_data[$ID_actual_node]["data"][$actual_field_key] = $actual_value;
				
				//echo $ID_actual_node
				
				
				
				/*} elseif (is_array($arrNodeRelSrcField[$ID_actual_node])) {
					$ID_source_field = $arrNodeRelSrcField[$ID_actual_node]["ID_source_field"];
					foreach($arrNodeRelSrcField[$ID_actual_node]["nodes"] AS $ID_source_node) {
					    //$ID_source_node = $tbl_data["keys"][$arrNodeRelSrcField[$ID_actual_node]];
	                    //$ID_source_field = $arrNodeRelSrcField[$ID_actual_node];
					    //$ID_source_field = $tbl_data["data"][$ID_source_node]["nodes"][$ID_actual_node]["src_field"];
					    $ID_node = $db->getField("ID_nodes", "Number", true);
	 
					    //if (!is_array($vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node]))
							//$vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node] = $tbl_data["data"][$ID_source_node]["nodes"][$ID_node];

						if(OLD_VGALLERY) {
						    switch ($actual_field_name) {
							case "visible":
							case "meta_title":
							    $vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node]["data"][$actual_field_name] = $actual_value;
							    break;
							case "alt_url":
							case "smart_url":
							case "permalink_parent":
							    $vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node][$actual_field_name] = $actual_value;
							    break;
							default:
							    $vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node]["data"][$actual_field_key] = $actual_value;
						    }
						} else {
							$vg["data"][$ID_source_node]["data"][$ID_source_field]["nodes"][$ID_node]["data"][$actual_field_key] = $actual_value;
						}
					}*/
				}

				//print_r($vg_field["selection"]);  

				if (strlen($actual_value) && is_array($vg_field["selection"]["data"]) && array_key_exists($actual_field_key, $vg_field["selection"]["data"])) {
				    //$key_node = $vg_field["selection"]["data"][$actual_field_key]["field"];
				    /* if(!isset($vg_field["selection"]["tbl"][$vg_field["selection"]["data"][$actual_field_key]["table"]]["params"]["data-" . $actual_field_key])) {
				      $vg_field["selection"]["tbl"][$vg_field["selection"]["data"][$actual_field_key]["table"]]["params"]["data-" . $actual_field_key] = array(
				      "ID" => $actual_field_key
				      , "type" => "data"
				      , "key" => $key_node
				      , "src" => $vg_field["selection"]["data"][$actual_field_key]["src"]
				      );
				      } */
					if (is_numeric($actual_value) && $actual_value > 0) {
					    $vg_field["selection"]["tbl"][$vg_field["selection"]["data"][$actual_field_key]["table"]]["nodes"][$actual_value] = $actual_value;
					    $vg_field["selection"]["tbl"][$vg_field["selection"]["data"][$actual_field_key]["table"]]["params"]["data-" . $actual_field_key]["values"][$actual_value][] = $ID_source_node;

						$vg["data"][$ID_actual_node]["data"][$actual_field_key] = ""; //prima era sotto alla graffa. Estato spostato perche se ce data_type "data" e selection_data_type valorizzato nn elimina i dati
					}
				}
		    } while ($db->nextRecord());
		    
            $arrRelFields = array_diff_key($vg_field["relationship"][$tbl]["src"], $arrFields); 
            if(is_array($arrRelFields) && count($arrRelFields) && is_array($vg["data"]) && count($vg["data"])) {
                foreach($arrRelFields AS $actual_field_key => $rel_tbl)
                {
                    if($count_field_by_rel_tbl[$rel_tbl] <= 1) {
                        foreach($vg["data"] AS $ID_actual_node => $data) 
                        {
                            $rel_nodes = array_keys($tbl_data["keys"][$rel_tbl], $ID_actual_node);

                            if (!is_array($vg["data"][$ID_actual_node]["data"][$actual_field_key])) {
                                $vg["data"][$ID_actual_node]["data"][$actual_field_key] = $tbl_data["father"][$rel_tbl];
                                $vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"] = array_intersect_key($tbl_data["father"][$rel_tbl]["nodes"], array_fill_keys($rel_nodes, array()));
                                if(is_array($rel_data) && count($rel_data)) {
                                    $rel_data_field = array_intersect_key($rel_data, $vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"]);
                                    $vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"] = array_replace_recursive($vg["data"][$ID_actual_node]["data"][$actual_field_key]["nodes"], $rel_data_field);
                                }
                            }
                        }		    	
                    }
                }
            }
		}
    }

    if (is_array($vg_field["preload"]) && count($vg_field["preload"])) {
		$schema = process_vgallery_schema("db");

		foreach ($vg_field["preload"] AS $table_alt => $table_fields) {
		    $key = "ID";
		    if (array_key_exists($table_alt, $schema["data_source"])) {
				$key = ($schema["data_source"][$table_alt]["key"] 
						? $schema["data_source"][$table_alt]["key"] 
						: $key
					);
		    }
		    $sSQL = "SELECT `" . $table_alt . "`.*
	                    FROM `" . $table_alt . "`
	                    WHERE `" . $table_alt . "`.`" . $key . "` IN(" . $db->toSql(implode(",", $vg["key"]), "Text", false) . ")";
		    $db->query($sSQL);
		    if ($db->nextRecord()) {
				do {
				    $vg["data"][$db->getField($key, "Number", true)]["preload"][$table_alt] = $db->record;
				    if (is_array($vg_field["selection"]["preload"][$table_alt]["fields"])) {
						foreach ($vg_field["selection"]["preload"][$table_alt]["fields"] AS $selection_field_key => $selection_field_data) {
						    if (array_key_exists($selection_field_key, $db->record)) {
								if (is_numeric($db->record[$selection_field_key])) {
								    if ($db->record[$selection_field_key] > 0) {
										/* if(!isset($vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key])) {
										  $vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key] = array(
										  "ID" => $selection_field_key
										  , "type" => "preload"
										  , "key" => $selection_field_data["field"]
										  , "tbl" => $table_alt
										  , "src" => $selection_field_data["src"]
										  );
										  } */
										$vg_field["selection"]["tbl"][$selection_field_data["table"]]["nodes"][$db->record[$selection_field_key]] = $db->record[$selection_field_key];
										$vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key]["values"][$db->record[$selection_field_key]][] = $db->getField($key, "Number", true);
								    }

								    $vg["data"][$db->getField($key, "Number", true)]["preload"][$table_alt][$selection_field_key] = "";
								} elseif (strpos($db->record[$selection_field_key], ",") !== false) {
								    $arrFieldValue = explode(",", $db->record[$selection_field_key]);
								    foreach ($arrFieldValue AS $field_value) {
										if (is_numeric($field_value) && $field_value > 0) {
										    /* if(!isset($vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key])) {
										      $vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key] = array(
										      "ID" => $selection_field_key
										      , "type" => "preload"
										      , "key" => $selection_field_data["field"]
										      , "tbl" => $table_alt
										      , "src" => $selection_field_data["src"]
										      );
										      } */
										    $vg_field["selection"]["tbl"][$selection_field_data["table"]]["nodes"][$field_value] = $field_value;
										    $vg_field["selection"]["tbl"][$selection_field_data["table"]]["params"]["preload-" . $selection_field_key]["values"][$field_value][] = $db->getField($key, "Number", true);
										}
								    }
								    
								    $vg["data"][$db->getField($key, "Number", true)]["preload"][$table_alt][$selection_field_key] = ""; 
								}
						    }
						}
				    }
				} while ($db->nextRecord());
		    }
		}
    }

    if (is_array($vg_field["selection"]["tbl"]) && count($vg_field["selection"]["tbl"])) {
        // e cmq non va
        ksort($vg_field["selection"]["tbl"]);        ///TODO: da trovare discriminante : $vg_father["src"]["table"] non ha senso  servirebbe qualcosa tipo data source 

		foreach ($vg_field["selection"]["tbl"] AS $tbl_selection => $selection_data) {
		    if (is_array($selection_data["params"]) && count($selection_data["params"])) {
				$arrWhere = array();
				if (is_array($vg_field["selection"]["tbl"][$tbl_selection]["nodes"]) && count($vg_field["selection"]["tbl"][$tbl_selection]["nodes"]))
				    $arrWhere[] = "`" . $tbl_selection . "`.ID IN(" . $db->toSql(implode(", ", $vg_field["selection"]["tbl"][$tbl_selection]["nodes"]), "Text", false) . ")";

				if (is_array($vg_field["selection"]["tbl"][$tbl_selection]["where"]) && count($vg_field["selection"]["tbl"][$tbl_selection]["where"])) {
				    foreach ($vg_field["selection"]["tbl"][$tbl_selection]["where"] AS $key => $nodes) {
						$arrWhere[] = "`" . $key . "` IN(" . $db->toSql(implode(", ", $nodes), "Text", false) . ")";
				    }
				}
				if (!count($arrWhere))
				    continue;

				$sSQL = "SELECT `" . $tbl_selection . "`.*
		        					, " . implode(", ", $selection_data["field"]) . "
				                FROM `" . $tbl_selection . "`
				                WHERE " . implode(" OR ", $arrWhere);
				$db->query($sSQL);
				if ($db->nextRecord()) {
				    do {
						foreach ($selection_data["params"] AS $field_name => $field_params) {
							if (count($field_params["src"]) > 1)
								$tmp_data = array_combine($field_params["src"], explode("|", $db->getField($field_params["key"], "Text", true)));
						    elseif(is_array($field_params["fields"][$field_params["src"][0]]["multi"]))
						    	$tmp_data = array_combine($field_params["fields"][$field_params["src"][0]]["multi"], explode("|", $db->getField($field_params["key"], "Text", true)));
						    else
								$tmp_data = $db->getField($field_params["key"], "Text", true);

							//Prima era cosi prob ce un bug nella visualizazione. sopra su Dr. marco baroni
                          	//$tmp_data = array($field_params["src"][0] => $db->getField($field_params["key"], "Text", true));
                                  
						    switch ($field_params["type"]) {
							case "shard":
								//$shard_nodes = $vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["rel"];
								$ID_shard_value = $db->getField("ID", "Number", true);
//echo $field_name . "\n";
//echo $tmp_data . "  " . $db->getField("ID", "Number", true);
//							print_r($field_params);
//print_r($shard_nodes);
								if(is_array($vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["values"][$ID_shard_value]) && count($vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["values"][$ID_shard_value])) {
									foreach($vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["values"][$ID_shard_value] AS $ID_actual_node) {
										 if($vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["rel"][$ID_actual_node]) {
										 	$shard_rel = $vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["rel"][$ID_actual_node];

										 	if ($shard_rel["type"] == "preload") {
												if($shard_rel["index"]) {
													if($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]]) {
														if(!is_array($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]]))
															$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]] = array($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]]);

														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]][] = $tmp_data;
													} elseif(is_array($tmp_data)) {
														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]][] = $tmp_data;
													} else {
														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["index"]][$shard_rel["shard"]] = $tmp_data; //done: da togliere perche e sbagliato ma sono da togliere anche le shard
													}
												} else {
													if($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]]) {
														if(!is_array($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]]))
															$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]] = array($vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]]);

														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]][] = $tmp_data;
													} elseif(is_array($tmp_data)) {
														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]][] = $tmp_data;
													} else {
														$vg["data"][$ID_actual_node]["preload"][$shard_rel["tbl"]][$shard_rel["ID"]][$shard_rel["shard"]] = $tmp_data; //done: da togliere perche e sbagliato ma sono da togliere anche le shard
													}
												}
											} else {
												if($vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]]) {
													if(!is_array($vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]]))
														$vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]] = array($vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]]);

													$vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]][] = $tmp_data;
												} elseif(is_array($tmp_data)) {
													$vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]][] = $tmp_data;
												} else {
													$vg["data"][$ID_actual_node]["data"][$shard_rel["ID"]][$shard_rel["shard"]] = $tmp_data; //done: da togliere perche e sbagliato ma sono da togliere anche le shard
												}
											}
										 }
									}
								}

//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
							    break;
							case "data":
								$arrActualNode = $vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["values"][$db->getField("ID", "Number", true)];
								if(is_array($arrActualNode) && count($arrActualNode)) {
									foreach($arrActualNode AS $ID_actual_node) {
										$tmp_data_node = $tmp_data;
										//Relative to Case "Shard" see above
										//ECHO $ID_actual_node . "\n";
										//print_r($tmp_data);
										if (is_array($vg_field["selection"]["data"][$field_params["ID"]]["shard"]) && count($vg_field["selection"]["data"][$field_params["ID"]]["shard"])) {
											foreach ($vg_field["selection"]["data"][$field_params["ID"]]["shard"] AS $shard_key => $shard_params) {
												if ($tmp_data_node[$shard_key]) {
													$arrShardValue = explode(",", $tmp_data_node[$shard_key]);
													foreach($arrShardValue AS $arrShardValue_ID) {
														if(is_numeric($arrShardValue_ID) && $arrShardValue_ID > 0) {
															$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"][$arrShardValue_ID] = $arrShardValue_ID;
															$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["values"][$arrShardValue_ID][$ID_actual_node] = $ID_actual_node;
														}
													}
													
													/*if (is_array($vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"])) {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"] = array_replace($vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"], array_combine($arrShardValue, $arrShardValue));
													} else {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"] = array_combine($arrShardValue, $arrShardValue);
													}*/

													if(!isset($vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"][$ID_actual_node])) {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"][$ID_actual_node] = array(
															"ID" => $field_params["ID"]
															, "tbl" => $field_params["tbl"]
															, "type" => $field_params["type"]
															, "shard" => $shard_key
														);
													}
													//$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"]["values"] = array_merge_recursive($vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"]["values"], array_fill_keys($arrShardValue, $ID_actual_node));
													$tmp_data_node[$shard_key] = null;
												}
											} 
										}

										if($vg["data"][$ID_actual_node]["data"][$field_params["ID"]]) {
											if(!is_array($vg["data"][$ID_actual_node]["data"][$field_params["ID"]]) || !array_key_exists("0", $vg["data"][$ID_actual_node]["data"][$field_params["ID"]]))
												$vg["data"][$ID_actual_node]["data"][$field_params["ID"]] = array($vg["data"][$ID_actual_node]["data"][$field_params["ID"]]);

											$vg["data"][$ID_actual_node]["data"][$field_params["ID"]][] = $tmp_data_node;
										} elseif(is_array($tmp_data_node[$shard_key])) {
											$vg["data"][$ID_actual_node]["data"][$field_params["ID"]][] = $tmp_data_node;
										} else {
											$vg["data"][$ID_actual_node]["data"][$field_params["ID"]] = $tmp_data_node;
										}
									}
								}
							    break;
							case "preload":
								$arrActualNode = $vg_field["selection"]["tbl"][$tbl_selection]["params"][$field_name]["values"][$db->getField("ID", "Number", true)];
								if(is_array($arrActualNode) && count($arrActualNode)) {
									foreach($arrActualNode AS $ID_actual_node) {
										$tmp_data_node = $tmp_data;
										//Relative to Case "Shard" see above
										if (is_array($vg_field["selection"]["preload"][$field_params["tbl"]]["fields"][$field_params["ID"]]["shard"]) && count($vg_field["selection"]["preload"][$field_params["tbl"]]["fields"][$field_params["ID"]]["shard"])) {
											foreach ($vg_field["selection"]["preload"][$field_params["tbl"]]["fields"][$field_params["ID"]]["shard"] AS $shard_key => $shard_params) {
												if ($tmp_data_node[$shard_key]) {
													$arrShardValue = explode(",", $tmp_data_node[$shard_key]);
													foreach($arrShardValue AS $arrShardValue_ID) {
														if(is_numeric($arrShardValue_ID) && $arrShardValue_ID > 0) {
															$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"][$arrShardValue_ID] = $arrShardValue_ID;
															$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["values"][$arrShardValue_ID][$ID_actual_node] = $ID_actual_node;
														}
													}
													
													
													/*if (is_array($vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"])) {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"] = array_replace($vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"], array_combine($arrShardValue, $arrShardValue));
													} else {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["nodes"] = array_combine($arrShardValue, $arrShardValue);
													}*/

													if(!isset($vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"][$ID_actual_node])) {
														$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"][$ID_actual_node] = array(
															"ID" => $field_params["ID"]
															, "tbl" => $field_params["tbl"]
															, "type" => $field_params["type"]
															, "shard" => $shard_key
															, "index" => (is_array($vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]])
																? count($vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]])
																: null
															)
														);
													}
													//$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["values"][$arrShardValue][] = $ID_actual_node;
													
													//$vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"]["values"] = array_merge_recursive($vg_field["selection"]["tbl"][$shard_params["tbl"]]["params"][$shard_params["ID"]]["rel"]["values"], array_fill_keys($arrShardValue, $ID_actual_node));
													$tmp_data_node[$shard_key] = null;
												}
											}
										}
										
										if($vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]]) {
											if(!is_array($vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]]) || !array_key_exists("0", $vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]]))
												$vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]] = array($vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]]);

											$vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]][] = $tmp_data_node;
										} elseif(is_array($tmp_data_node[$shard_key])) {
											$vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]][] = $tmp_data_node;
										} else {
											$vg["data"][$ID_actual_node]["preload"][$field_params["tbl"]][$field_params["ID"]] = $tmp_data_node;
										}
									}
								}
							    break;
							case "selection":
							    /*if (is_array($field_params["compare"])) {
									$invalid_data = false;
									foreach ($field_params["compare"] AS $compare_key => $compare_value) {
										if ($db->getField($compare_key, "Text", true) != $compare_value) {
										$invalid_data = true;
										break;
										}
									}
							    }

							    if (!$invalid_data) {
									$vg["data"][$db->getField(($field_params["primary_key"] ? $field_params["primary_key"] : "ID"), "Number", true)]["data"][$field_params["ID"]][] = $tmp_data;
							    }*/

								$vg["data"][$db->getField(($field_params["primary_key"] ? $field_params["primary_key"] : "ID"), "Number", true)]["data"][$field_params["ID"]][] = $tmp_data;

							    break;
							default:
						    }
						}
				    } while ($db->nextRecord());
				}
		    }
		}
	//print_r($vg); 
	//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());             
	//print_r($arrSelection); 
	//  die();
    }
//print_r($vg_field["selection"]);
    //   print_r($vg_field);    
}
function process_query_filter_node($tbl, $params = null, $query = array()) {
    $db = ffDB_Sql::factory();

    if(!$params) {
        $globals = ffGlobals::getInstance("gallery");
    	foreach($globals->filter AS $filter_key => $filter_value) {
    		switch($filter_key) {
    			case "first_letter":
    				if($filter_value == "0-9") {
    					$params["filter"]["meta_title_alt"] = " REGEXP '^[0-9]' ";    			
    				} else {
						$params["filter"]["meta_title_alt"] = $filter_value . "%";    			
					}
    				break;
                case "place":
                    $params["place"] = $filter_value;    			
    			default:
    		}
    	}
    }

    //search By Tags or Filter (letter ffl)
    if (is_array($params["filter"]) && count($params["filter"])) {
        foreach ($params["filter"] AS $search_key => $search_terms) {
            if (is_array($search_terms) && count($search_terms)) {
                foreach ($search_terms AS $search_term) {
                    $query["where"][$search_key][] = "FIND_IN_SET(" . $search_term . ", `" . $tbl . "`.`" . $search_key . "`)";
                }
            } elseif(strpos($search_terms, "%") !== false) {
                $query["where"][$search_key] = "`" . $tbl . "`.`" . $search_key . "` LIKE " . $db->toSql($search_terms);
            } else {
            	$query["where"][$search_key] = "`" . $tbl . "`.`" . $search_key . "` " . $search_terms;
            }

            if(is_array($query["where"][$search_key]) && count($query["where"][$search_key]))
                $query["where"][$search_key] = " (" . implode(" OR ", $query["where"][$search_key]) . " ) ";
        }  
    }

    //search By Place
    if($params["place"]) {
        $query["from"]["place_city"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "city` ON `" . FF_SUPPORT_PREFIX . "city`.`ID` = `" . $tbl . "`.`ID_place`";
        if (is_array($params["place"]["city"]) && count($params["place"]["city"])) {
			$query["select"]["geo.position"] = "CONCAT(`" . FF_SUPPORT_PREFIX . "city`.`coord_lat`, ',', `" . FF_SUPPORT_PREFIX . "city`.`coord_lng`) AS `geo.position`";
			$query["select"]["geo.placename"] = "CONCAT(
					`" . FF_SUPPORT_PREFIX . "city`.`name`
					, IF(`" . FF_SUPPORT_PREFIX . "city`.`name` = `" . FF_SUPPORT_PREFIX . "city`.`province_name`
						, ''
						, CONCAT(', ', `" . FF_SUPPORT_PREFIX . "city`.`province_name`)
					)
				) AS `geo.placename`";

            foreach($params["place"]["city"] AS $place_field => $place_keys) {
                $query["where"]["city_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "city`.`" . $place_field . "`" . (is_array($place_keys)
                    ? " IN('" . implode("','", $place_keys) . "')"
                    : " = " . $db->toSql($place_keys)
                );
            }
        }
        if (is_array($params["place"]["region"]) && count($params["place"]["region"])) {
			$query["select"]["geo.position"] = "CONCAT(`" . FF_SUPPORT_PREFIX . "region`.`coord_lat`, ',', `" . FF_SUPPORT_PREFIX . "region`.`coord_lng`) AS `geo.position`";
			$query["select"]["geo.placename"] = "`" . FF_SUPPORT_PREFIX . "region`.`name` AS `geo.placename`";

        	//$query["select"]["geo.region"] = "`" . FF_SUPPORT_PREFIX . "region`.`name` AS `geo.region`";
            $query["from"]["place_region"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "region` ON `" . FF_SUPPORT_PREFIX . "region`.`ID` = `" . FF_SUPPORT_PREFIX . "city`.`ID_region`";
            foreach($params["place"]["region"] AS $place_field => $place_keys) {
                $query["where"]["region_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "region`.`" . $place_field . "`" . (is_array($place_keys)
                    ? " IN('" . implode("','", $place_keys) . "')"
                    : " = " . $db->toSql($place_keys)
                );
            }
        }
        if (is_array($params["place"]["state"]) && count($params["place"]["state"])) {
			$query["select"]["geo.position"] = "CONCAT(`" . FF_SUPPORT_PREFIX . "state`.`coord_lat`, ',', `" . FF_SUPPORT_PREFIX . "state`.`coord_lng`) AS `geo.position`";
			$query["select"]["geo.placename"] = "`" . FF_SUPPORT_PREFIX . "state`.`name` AS `geo.placename`";
        	$query["select"]["geo.region"] = "`" . FF_SUPPORT_PREFIX . "state`.`sigle` AS `geo.region`";

            $query["from"]["place_state"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "state` ON `" . FF_SUPPORT_PREFIX . "state`.`ID` = `" . FF_SUPPORT_PREFIX . "city`.`ID_state`";
            foreach($params["place"]["state"] AS $place_field => $place_keys) {
                $query["where"]["state_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "state`.`" . $place_field . "`" . (is_array($place_keys)
                    ? " IN('" . implode("','", $place_keys) . "')"
                    : " = " . $db->toSql($place_keys)
                );
            }
        }
    }    
    return $query;
}
function process_vgallery_node(&$vg_father, $settings, $vg_field = null) {
	$globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $vg_data = array();
    $vg_key = array();

	//Manage Sorting
    if (!$vg_father["random"]) {
    	if($vg_father["sort_default"] > 0 ) {
			if (is_array($vg_field["sort"])) {
			    $compare_sort_table = "tbl_sort_field";
			    switch ($vg_field["sort"]["data_type"]) {
				case "table.alt":
				    if ($vg_field["sort"]["data_source"] == $vg_father["src"]["table"])
						$compare_sort_table = $vg_father["src"]["table"];

				    $arrSortSelect = array();
				    $arrDataLimit = explode(",", $vg_field["sort"]["data_limit"]);
				    if (is_array($arrDataLimit) && count($arrDataLimit)) {
						foreach ($arrDataLimit AS $arrDataLimit_key) {
						    $arrSortSelect[] = "`" . $compare_sort_table . "`.`" . $arrDataLimit_key . "`";
						}
				    }
				    $query["select"]["order"] = implode(",", $arrSortSelect);
				    if (count($arrSortSelect) > 1)
						$query["select"]["order"] = "CONCAT(" . $query["select"]["order"] . ")";
				    else
						$query["select"]["order"] = $query["select"]["order"];

				    if ($vg_field["sort"]["data_source"] != $vg_father["src"]["table"]) {
						$query["from"]["order"] = "INNER JOIN `" . $vg_field["sort"]["data_source"] . "` AS tbl_sort_field
														ON `tbl_sort_field`.`ID_" . $vg_father["src"]["type"] . "` = `" . $vg_father["src"]["table"] . "`.`ID`";
				    }
				    break;
				case "selection": //da fare questo e codice di prova
				    $arrSortSelect = array();
				    $arrDataLimit = explode(",", $vg_field["sort"]["data_limit"]);
				    if (is_array($arrDataLimit) && count($arrDataLimit)) {
						foreach ($arrDataLimit AS $arrDataLimit_key) {
						    $arrSortSelect[] = "`" . $vg_field["sort"]["data_source"] . "`.`" . $arrDataLimit_key . "`";
						}
				    }
				    $query["select"]["order"]["order"] = implode(",", $arrSortSelect);
				    if (count($arrSortSelect) > 1)
						$query["select"]["order"] = "CONCAT(" . $query["select"]["order"] . ")";
				    else
						$query["select"]["order"] = $query["select"]["order"];

				    $query["from"]["order"] = "INNER JOIN `" . $vg_father["src"]["type"] . "_rel_nodes_fields` AS tbl_sort_field 
													ON `tbl_sort_field`.`ID_nodes` = `" . $vg_father["src"]["table"] . "`.`ID` 
														AND `tbl_sort_field`.`ID_fields` = " . $db->toSql($vg_father["sort_default"], "Number")
					    . ($vg_father["skip_lang"] ? "" : " AND `tbl_sort_field`.`ID_lang` = " . $db->toSql(($vg_field["sort"]["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number")
					    );

				    $query["from"]["order"] .= " INNER JOIN `" . $vg_field["sort"]["data_source"] . "` 
													ON `" . $vg_field["sort"]["data_source"] . "`.`ID` = `tbl_sort_field`.`description`";

				    break;
				default:
				    if (is_numeric($vg_father["sort_default"])) {
						$query["select"]["order"] = "IF(`tbl_sort_field`.`description_text` = ''
															, `tbl_sort_field`.`description` 
															, `tbl_sort_field`.`description_text` 
														)";

					$query["from"]["order"] = "INNER JOIN `" . $vg_father["src"]["type"] . "_rel_nodes_fields` AS tbl_sort_field
														ON `tbl_sort_field`.`ID_nodes` = `" . $vg_father["src"]["table"] . "`.`ID` 
															AND `tbl_sort_field`.`ID_fields` = " . $db->toSql($vg_father["sort_default"], "Number")
						. ($vg_father["skip_lang"] ? "" : " AND `tbl_sort_field`.`ID_lang` = " . $db->toSql(($vg_field["sort"]["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number")
						);
				    } else {
						if (array_key_exists($vg_father["sort_default"], $vg_father["preload"])) {
						    $query["select"]["order"] = "`" . $vg_father["src"]["table"] . "`.`" . $vg_father["sort_default"] . "`";
						}
				    }
			    }

			    if ($vg_field["sort"]["extended_type_group"] = "select" && strlen($vg_field["sort"]["select"]["data_source"])) {
					if (is_numeric($vg_field["sort"]["select"]["data_source"])) {
					    $query["from"]["order"] .= " INNER JOIN `" . $vg_father["src"]["type"] . "_fields_selection_value` AS tbl_sort_field_selection ON
																tbl_sort_field_selection.ID_selection = " . $db->toSql($vg_field["sort"]["select"]["data_source"], "Number") . "
																AND FIND_IN_SET(`tbl_sort_field_selection`.`ID`, " . $query["select"]["order"] . ")";
					    $query["select"]["order"] = "`tbl_sort_field_selection`.`name`";
					} else {
					    $query["from"]["order"] .= " INNER JOIN `" . $vg_field["sort"]["select"]["data_source"] . "` AS tbl_sort_field_selection
																ON FIND_IN_SET(`tbl_sort_field_selection`.`ID`, " . $query["select"]["order"] . ")";

					    $arrSortSelect = array();
					    $arrDataLimit = explode(",", $vg_field["sort"]["select"]["data_limit"]);
					    if (is_array($arrDataLimit) && count($arrDataLimit)) {
							foreach ($arrDataLimit AS $arrDataLimit_key) {
							    $arrSortSelect[] = "`tbl_sort_field_selection`.`" . $arrDataLimit_key . "`";
							}
					    }
					    $query["select"]["order"] = implode(",", $arrSortSelect);
					    if (count($arrSortSelect) > 1)
							$query["select"]["order"] = "CONCAT(" . $query["select"]["order"] . ")";
					    else
							$query["select"]["order"] = $query["select"]["order"];
					}
			    }
			} elseif (strlen($vg_field["sort"])) {
			    
			}
		} elseif($vg_father["sort_default"] == 0) {
			$query["select"]["order"] = $vg_father["src"]["table"] . ".`meta_title_alt`";
			//$query["select"]["order"] = $vg_father["src"]["table"] . ".last_update";
		} elseif($vg_father["sort_default"] == -1) {
			$query["select"]["order"] = $vg_father["src"]["table"] . ".created";
		} elseif($vg_father["sort_default"] == -2) {
			$query["select"]["order"] = $vg_father["src"]["table"] . ".published_at";
		} elseif($vg_father["sort_default"] == -3) {
			$query["select"]["order"] = $vg_father["src"]["table"] . ".`order`";
		} elseif($vg_father["sort_default"] == -4) {
			//$query["select"]["order"] = $vg_father["src"]["table"] . ".`meta_title_alt`";
		}
    }
   // print_r($vg_father["search"]);
    //$vg_father["search"]["term"] = "Roma"; 
//print_r($vg_field); 

	//Search Engine
	if ($vg_father["type"] != "publishing" && $vg_father["search"]) {
		//search By Tags or Filter (letter ffl)
        $query = process_query_filter_node($vg_father["src"]["table"], $vg_father["search"], $query);
		/*if (is_array($vg_father["search"]["filter"]) && count($vg_father["search"]["filter"])) {
		    foreach ($vg_father["search"]["filter"] AS $search_key => $search_terms) {
				if (is_array($search_terms) && count($search_terms)) {
				    foreach ($search_terms AS $search_term) {
						$query["where"][$search_key][] = "FIND_IN_SET(" . $search_term . ", `" . $vg_father["src"]["table"] . "`.`" . $search_key . "`)";
				    }
				} else {
				    $query["where"][$search_key] = "`" . $vg_father["src"]["table"] . "`.`" . $search_key . "` LIKE " . $db->toSql($search_terms);
				}

			    if(is_array($query["where"][$search_key]) && count($query["where"][$search_key]))
		    		$query["where"][$search_key] = " (" . implode(" OR ", $query["where"][$search_key]) . " ) ";
		    }  
		}
		
		//search By Place
        if($vg_father["search"]["place"]) {
            $query["from"]["place_city"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "city` ON `" . FF_SUPPORT_PREFIX . "city`.`ID` = `" . $vg_father["src"]["table"] . "`.`ID_place`";
            if (is_array($vg_father["search"]["place"]["city"]) && count($vg_father["search"]["place"]["city"])) {
                foreach($vg_father["search"]["place"]["city"] AS $place_field => $place_keys) {
                    $query["where"]["city_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "city`.`" . $place_field . "`" . (is_array($place_keys)
                        ? " IN(" . $db->toSql(implode("','", $place_keys)) . ")"
                        : " = " . $db->toSql($place_keys)
                    );
                }
            }
            if (is_array($vg_father["search"]["place"]["region"]) && count($vg_father["search"]["place"]["region"])) {
                $query["from"]["place_region"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "region` ON `" . FF_SUPPORT_PREFIX . "region`.`ID` = `" . FF_SUPPORT_PREFIX . "city`.`ID_region`";
                foreach($vg_father["search"]["place"]["region"] AS $place_field => $place_keys) {
                    $query["where"]["region_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "region`.`" . $place_field . "`" . (is_array($place_keys)
                        ? " IN(" . $db->toSql(implode("','", $place_keys)) . ")"
                        : " = " . $db->toSql($place_keys)
                    );
                }
            }
            if (is_array($vg_father["search"]["place"]["state"]) && count($vg_father["search"]["place"]["state"])) {
                $query["from"]["place_state"] = "INNER JOIN `" . FF_SUPPORT_PREFIX . "state` ON `" . FF_SUPPORT_PREFIX . "state`.`ID` = `" . FF_SUPPORT_PREFIX . "city`.`ID_state`";
                foreach($vg_father["search"]["place"]["state"] AS $place_field => $place_keys) {
                    $query["where"]["state_" . $place_field] = "`" . FF_SUPPORT_PREFIX . "state`.`" . $place_field . "`" . (is_array($place_keys)
                        ? " IN(" . $db->toSql(implode("','", $place_keys)) . ")"
                        : " = " . $db->toSql($place_keys)
                    );
                }
            }
        }*/

        //General Search
		$search_term = trim(str_replace(array(" ", "-", "_"), "%", $vg_father["search"]["term"]), "%");
		if (strlen($search_term)) {
		    if ($vg_father["search"]["limit"]) {
				if (is_array($vg_father["search"]["limit"])) {
				    $search_limit = array_keys($vg_father["search"]["limit"]);
				} else {
				    $search_limit = array_keys($vg_field["keys"]);
				}
		    }
		    $query["from"]["search"] = "LEFT JOIN `" . $vg_father["src"]["type"] . "_rel_nodes_fields` AS tbl_search 
											    ON tbl_search.ID_nodes = `" . $vg_father["src"]["table"] . "`.ID";

		    $query["where"]["search"]["term"][] = "(`tbl_search`.`description` LIKE '%" . $db->toSql($search_term, "Text", false) . "%'"
			. ($vg_father["skip_lang"] ? "" : " AND tbl_search.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			)
			. (is_array($search_limit) && count($search_limit) ? " AND `tbl_search`.`ID_fields` IN(" . $db->toSql(implode(",", $search_limit), "Text", false) . ")" : ""
			)
			. ")";
		    
		    if (is_array($vg_field["search"]["preload"]) && count($vg_field["search"]["preload"])) {
				foreach ($vg_field["search"]["preload"] AS $tbl_preload => $preload_data) {
				    if ($tbl_preload != $vg_father["src"]["table"])
						$preload_from[$tbl_preload] = " LEFT JOIN `" . $tbl_preload . "` ON `" . $tbl_preload . "`.`ID_" . $vg_father["src"]["type"] . "` = `" . $vg_father["src"]["table"] . "`.`ID`";

				    if (is_array($preload_data["fields"]) && count($preload_data["fields"])) {
						foreach ($preload_data["fields"] AS $preload_field_name) {
						    $query["where"]["search"]["term"][] = "" . $preload_field_name . " LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";
						}
				    }
				}
		    }	    

			$vg_father["search"]["relevance"][] = str_replace("%", " ", $search_term);
		}

        if(is_array($vg_father["search"]["relevance"]) && count($vg_father["search"]["relevance"])) {
			$search_relevance = implode(" " , $vg_father["search"]["relevance"]);
			if(LANGUAGE_DEFAULT_ID == LANGUAGE_INSET_ID) { //todo E TOFIX. da un errore quando joini 2 tabelle che hanno lo stesso nome meta_title
				$query["order"]["search"] = "
				    MATCH(meta_title) AGAINST (" . $db->toSql($search_relevance). ") DESC
					, MATCH(meta_description) AGAINST (" . $db->toSql($search_relevance) . ") DESC
				";
			}
		}	
		//print_r($vg_field["search"]["preload"]);
		
		//Specific Search in general data (rel_node_fields)
		if(is_array($vg_father["search"]["term_by_fields"]["data"]) && count($vg_father["search"]["term_by_fields"]["data"])) {
			foreach($vg_father["search"]["term_by_fields"]["data"] AS $search_key => $search_params) {
		    	$arrSearchSpecific["where"][] = "(`" . $vg_father["src"]["type"] . "_rel_nodes_fields`.`description` LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $search_params), "Text", false) . "%'"
								    . " AND `" . $vg_father["src"]["type"] . "_rel_nodes_fields`.`ID_fields` = " . $db->toSql($search_key, "Text", false)
								    . ")";		    
		    }
		    $arrSearchSpecific["limit"] = ($vg_father["limit"]["elem"] > 0 
							? $vg_father["limit"]["elem"] 
							: $vg_father["navigation"]["rec_per_page"]
						);
			if($arrSearchSpecific["limit"])
				$arrSearchSpecific["limit"] = " LIMIT " . $arrSearchSpecific["limit"];

			$sSQL = "SELECT `" . $vg_father["src"]["type"] . "_rel_nodes_fields`.ID_nodes AS node
						, COUNT(`" . $vg_father["src"]["type"] . "_rel_nodes_fields`.ID_nodes) AS cont
				      FROM `" . $vg_father["src"]["type"] . "_rel_nodes_fields`
				      WHERE " . ($vg_father["skip_lang"] 
				      	? " 1 " 
				      	: "`" . $vg_father["src"]["type"] . "_rel_nodes_fields`.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			    	  	) . "
            			AND (" . implode(" OR ", $arrSearchSpecific["where"]) . ")
            		  GROUP BY `" . $vg_father["src"]["type"] . "_rel_nodes_fields`.ID_nodes
                      HAVING cont = " . (count($vg_father["search"]["term_by_fields"]["data"])) . "
            		  " . $arrSearchSpecific["limit"];
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$arrSearchSpecific["node"][] = $db->getField("node", "Number", true);
				} while($db->nextRecord());
				$query["where"]["search"]["specific"][] = "`" . $vg_father["src"]["table"] . "`.ID IN (" . implode(", ", $arrSearchSpecific["node"]) . ")";
			} else {
				$query["where"]["search"]["specific"][] = "`" . $vg_father["src"]["table"] . "`.ID = 0";
			}
/*
		    $query["from"]["search"] = "LEFT JOIN `" . $vg_father["src"]["type"] . "_rel_nodes_fields` AS tbl_search 
											    ON tbl_search.ID_nodes = `" . $vg_father["src"]["table"] . "`.ID";

		    foreach($vg_father["search"]["term_by_fields"]["data"] AS $search_key => $search_params) {
		    	$query["where"]["search"]["specific"][] = "(`tbl_search`.`description` LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $search_params), "Text", false) . "%'"
			    . ($vg_father["skip_lang"] ? "" : " AND tbl_search.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			    )
			    . " AND `tbl_search`.`ID_fields` = " . $db->toSql($search_key, "Text", false)
			    . ")";		    
		    }
*/
		}
		//print_r($vg_father["search"]["term_by_fields"]);
		//print_r($vg_field);
		//da gestire ricercha per field
		
		//Search in External Table not Primary
		if (is_array($vg_field["search"]["selection"]["tbl"]) && count($vg_field["search"]["selection"]["tbl"])) {
		    foreach ($vg_field["search"]["selection"]["tbl"] AS $tbl_selection => $selection_data) {
				$arrSearchSelect = array();
				if (is_array($selection_data["field"]) && count($selection_data["field"])) {
				    foreach ($selection_data["field"] AS $field_key => $field_value) {
						if (strpos($field_key, ",") !== false) {
						    $arrFieldKey = array_filter(explode(",", $field_key));
                                                    
                                                    if($search_term)
                                                        $arrSearchSelect[$field_key] = "CONCAT(" . implode(",", $arrFieldKey). ") LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";

                                                    //Search By Selective Field
                                                    if($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key] && is_string($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]) && !is_numeric($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key])) {
                                                        $arrSearchSelect["search-" . $field_key] = "CONCAT(" . implode(",", $arrFieldKey). ") LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]), "Text", false) . "%'";
                                                    }
                                                    /*
						    foreach ($arrFieldKey AS $arrFieldKey_value) {
								if(strlen($search_term))
								    $arrSearchSelect[$arrFieldKey_value] = "`" . $arrFieldKey_value . "` LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";

								//Search By Selective Field
								if($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]) {
								    $arrSearchSelect["search-" . $field_key] = "`" . $arrFieldKey_value . "` LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]), "Text", false) . "%'";
                                                                }
						    }*/
						} else {
						    if($search_term)
								$arrSearchSelect[$field_key] = "`" . $field_key . "` LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";
						    
						    //Search By Selective Field
						    if($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key] && is_string($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]) && !is_numeric($vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]))
								$arrSearchSelect["search-" . $field_key] = "`" . $field_key . "` LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $vg_father["search"]["term_by_fields"]["selection"][$tbl_selection][$field_key]), "Text", false) . "%'";
						}
				    }
				}
				$vg_field["search"]["selection"]["tbl"][$tbl_selection]["searched"] = array();
			    if(count($arrSearchSelect)) {
				    $sSQL = "SELECT DISTINCT `" . $tbl_selection . "`.ID AS ID
							FROM `" . $tbl_selection . "`
							WHERE " . implode(" OR ", $arrSearchSelect);
				    $db->query($sSQL);
				    if ($db->nextRecord()) {
						do {
						    $vg_field["search"]["selection"]["tbl"][$tbl_selection]["searched"][] = $db->getField("ID", "Number", true);
						} while ($db->nextRecord());
				    }
				}
		    }
		}
		//Search in Primary Table
		if (is_array($vg_field["search"]["selection"]["preload"]) && count($vg_field["search"]["selection"]["preload"])) {
		    foreach ($vg_field["search"]["selection"]["preload"] AS $tbl_preload => $preload_data) {
				if (strlen($search_term) && $tbl_preload != $vg_father["src"]["table"])
				    $preload_from[$tbl_preload] = " LEFT JOIN `" . $tbl_preload . "` ON `" . $tbl_preload . "`.`ID_" . $vg_father["src"]["type"] . "` = `" . $vg_father["src"]["table"] . "`.`ID`";

				if (is_array($preload_data["fields"]) && count($preload_data["fields"])) {
				    foreach ($preload_data["fields"] AS $preload_field_name => $preload_field_data) {
						if (isset($vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"])) {   
						    if (count($vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"])) {
								if (strpos($preload_field_name, ",") !== false) {
								    $arrPreloadFieldName = explode(",", $preload_field_name);
								    foreach ($arrPreloadFieldName AS $arrPreloadFieldName_value) {
							    		if(isset($vg_father["search"]["term_by_fields"]["preload"][$tbl_preload][$arrPreloadFieldName_value]))	
											$query["where"]["search"]["specific"][] = "`" . $tbl_preload . "`.`" . $arrPreloadFieldName_value . "` IN(" . $db->toSql(implode(",", $vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"]), "Text", false) . ")";
										elseif(strlen($search_term))
											$query["where"]["search"]["term"][] = "`" . $tbl_preload . "`.`" . $arrPreloadFieldName_value . "` IN(" . $db->toSql(implode(",", $vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"]), "Text", false) . ")";								    
								    }
								} else {
							    	if(isset($vg_father["search"]["term_by_fields"]["preload"][$tbl_preload][$preload_field_name]))	
							    		$query["where"]["search"]["specific"][] = "`" . $tbl_preload . "`.`" . $preload_field_name . "` IN(" . $db->toSql(implode(",", $vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"]), "Text", false) . ")";
							    	elseif(strlen($search_term))
							    		$query["where"]["search"]["term"][] = "`" . $tbl_preload . "`.`" . $preload_field_name . "` IN(" . $db->toSql(implode(",", $vg_field["search"]["selection"]["tbl"][$preload_field_data["table"]]["searched"]), "Text", false) . ")";
								}
						    }
						} else {
						    if (strpos($preload_field_name, ",") !== false) {
								$preload_field_name_sql = "CONCAT(`" . str_replace(",", "`,' ',`" . $tbl_preload . "`.`", $preload_field_name) . "`)";
						    } else {
								$preload_field_name_sql = "`" . $tbl_preload . "`.`" . $preload_field_name . "`";

								if(isset($vg_father["search"]["term_by_fields"]["preload"][$tbl_preload][$preload_field_name]))	
									$query["where"]["search"]["specific"][] = "" . $preload_field_name_sql . " LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";
						    }


						    if(strlen($search_term))
						    	$query["where"]["search"]["term"][] = "" . $preload_field_name_sql . " LIKE '%" . $db->toSql($search_term, "Text", false) . "%'";
						}
				    }
				}
		    }
		}
                		

		if (is_array($vg_father["search"]["term_by_fields"]["preload"]) && count($vg_father["search"]["term_by_fields"]["preload"])) {
		    foreach ($vg_father["search"]["term_by_fields"]["preload"] AS $tbl_preload => $preload_data) {
				if ($tbl_preload != $vg_father["src"]["table"])
				    $preload_from[$tbl_preload] = " LEFT JOIN `" . $tbl_preload . "` ON `" . $tbl_preload . "`.`ID_" . $vg_father["src"]["type"] . "` = `" . $vg_father["src"]["table"] . "`.`ID`";

				foreach ($preload_data AS $preload_field_name  => $preload_field_term) {
                                    if(is_numeric($preload_field_term))
                                        $query["where"]["search"]["alt"][] = "" . $vg_field["search"]["preload"][$tbl_preload]["fields"][$preload_field_name] . " = " . $db->toSql($preload_field_term, "Number");
                                    else
                                        $query["where"]["search"]["alt"][] = "" . $vg_field["search"]["preload"][$tbl_preload]["fields"][$preload_field_name] . " LIKE '%" . $db->toSql(str_replace(array(" ", "-", "_"), "%", $preload_field_term), "Text", false) . "%'";
				}
		    }
		}	
		
		//print_r($query["where"]["search"]);


		if (is_array($preload_from))
		    $query["from"]["search"] .= implode(" ", $preload_from);
                
		if (is_array($query["where"]["search"]["specific"]) && count($query["where"]["search"]["specific"])) {
		    $query_search = " (" . implode(" AND ", $query["where"]["search"]["specific"]) . ")";
		}
		if (is_array($query["where"]["search"]["alt"]) && count($query["where"]["search"]["alt"])) {
		    $query_search .= ($query_search ? " OR " : "") . " (". implode(" AND ", $query["where"]["search"]["alt"]) . ")";
		}

		if (is_array($query["where"]["search"]["term"]) && count($query["where"]["search"]["term"])) {
		    $query_search .= ($query_search ? " OR " : "") . implode(" OR ", $query["where"]["search"]["term"]);
		}
			
		if($query_search)
			$query["where"]["search"] = " ( " . $query_search . ")";
		else
			unset($query["where"]["search"]);
		
    }

	if ($query["select"]["order"]) {
		$query["select"]["order"] .= " AS actual_sort";
		$query["order"][] = "actual_sort " . $vg_father["sort_method"];
	}

    $sSQL = "";
    if ($vg_father["mode"] == "thumb") {
		if(is_array($vg_father["limit_type"]) && count($vg_father["limit_type"])) {
    		$query["where"]["type"] = "ID_type IN(" . $db->toSql(implode(",", $vg_father["limit_type"]), "Text", false) . ")";
    	}
		switch ($vg_father["type"]) {
		    case "anagraph":
				$sSQL = process_vgallery_anagraph_sql($vg_father, $settings, $query);
			break;
		    case "learnmore":
				$vg_father["enable_found_rows"] = false;
			//$sSQL = process_vgallery_learnmore_sql($vg_father, $settings, $sort);
			break;
		    case "publishing":
				switch ($vg_father["publishing"]["src"]) {
				    case "anagraph":
						$sSQL = process_anagraph_publishing_sql($vg_father, $settings, $query);
					break;
				    case "gallery":
					break;
				    default:
						$sSQL = process_vgallery_publishing_sql($vg_father, $settings, $query);
				}
			break;
		    case "wishlist":
				$sSQL = process_vgallery_wishlist_sql($vg_father, $settings, $query);
			break;
		    default:
				$sSQL = process_vgallery_node_sql($vg_father, $settings, $query);
		}
    }
   // print_r($vg_father);
    if (strlen($sSQL)) {
		$db->query($sSQL);
		if ($db->nextRecord()) {
		    $pricelist = null;
		    $count_files = 0;

		    do {
				/* if($vg_father["limit"]) { //CORREZIONE NUMERO ELEMENTI PUBBLICAZIONE
				  if($count_files >= $vg_father["limit"]) {
				  break;
				  }
				  } */

				$ID_node = $db->getField("ID", "Number", true);
				$ID_type = $db->getField("ID_type", "Number", true);
				$ID_cart_detail = $db->getField("ID_cart_detail", "Number", true);

				$vg_key["nodes"][$ID_node] = $ID_node;
				$vg_key["type"][$ID_type] = $ID_type;

				if ($vg_father["wishlist"] !== null && $ID_cart_detail > 0) {
				    $unic_id_node = $ID_node . "-" . $ID_cart_detail;

				    $vg_father["wishlist"]["nodes"][$ID_node] = $ID_node;

				    $vg_data[$unic_id_node]["ID_cart_detail"] = $ID_cart_detail;
				    $vg_data[$unic_id_node]["pricelist"] = null;
				} else {
				    if ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"]) {
					$ID_pricelist = $db->getField("ID_pricelist", "Number", true);
					$unic_id_node = $ID_node . "-" . $ID_pricelist;

					$pricelist["nodes"][$ID_node] = $ID_node;

					$vg_data[$unic_id_node]["pricelist"]["ID"] = $ID_pricelist;
					$vg_data[$unic_id_node]["pricelist"]["range"]["since"] = $db->getField("pricelist_since", "Number", true);
					$vg_data[$unic_id_node]["pricelist"]["range"]["to"] = $db->getField("pricelist_to", "Number", true);
				    } else {
					$unic_id_node = $ID_node;

					$vg_data[$unic_id_node]["pricelist"] = null;
				    }

				    $vg_data[$unic_id_node]["ID_cart_detail"] = null;
				}
				/**
				 * Preload data if exists... 
				 * prefix_field: 'data_'
				 */
				$vg_data[$unic_id_node]["preload"][$vg_father["src"]["table"]] = $db->record;

				$vg_data[$unic_id_node]["ID"] 										= $ID_node;
				$vg_data[$unic_id_node]["parent"] 									= $db->getField("parent", "Text", true);
				$vg_data[$unic_id_node]["name"] 									= $db->getField("name", "Text", true);
				$vg_data[$unic_id_node]["ID_type"] 									= $ID_type;
				$vg_data[$unic_id_node]["type"] 									= $db->getField("type", "Text", true);
				$vg_data[$unic_id_node]["is_dir"] 									= $db->getField("is_dir", "Number", true);
				//$vg_data[$unic_id_node]["data_type_publish"] = $db->getField("data_type_publish", "Text", true);
				//$vg_data[$unic_id_node]["vgallery_name"]                                      = $db->getField("vgallery_name", "Text", true);
				$vg_data[$unic_id_node]["created"] 									= $db->getField("created", "Number", true);
				$vg_data[$unic_id_node]["last_update"] 								= $db->getField("last_update", "Number", true);
				$vg_data[$unic_id_node]["published"] 								= $db->getField("published_at", "Number", true);
				$vg_data[$unic_id_node]["owner"] 									= $db->getField("owner", "Number", true);
				$vg_data[$unic_id_node]["class"] 									= $db->getField("class", "Text", true);
				$vg_data[$unic_id_node]["highlight"]["container"] 					= $db->getField("highlight_container", "Text", true);
				$vg_data[$unic_id_node]["highlight"]["image"]["src"] 				= get_image_properties_by_grid_system(
																						$db->getField("highlight_image", "Number", true)
																						, $db->getField("highlight_image_md", "Number", true)
																						, $db->getField("highlight_image_sm", "Number", true)
																						, $db->getField("highlight_image_xs", "Number", true)
																					);				
				$vg_data[$unic_id_node]["is_wishlisted"] 							= $db->getField("is_wishlisted", "Number", true);
				//$vg_data[$unic_id_node]["enable_multilang_visible"]                           = $db->getField("enable_multilang_visible", "Number", true);
				//$ID_owner = $db->getField("owner", "Number", true); 

				$vg_data[$unic_id_node]["tags"]                                         = $db->getField("tags", "Text", true);
                if($vg_data[$unic_id_node]["tags"])
                    $vg_father["seo"]["tags"]["secondary"]                              .= ($vg_father["seo"]["tags"]["secondary"] ? "," : "") . $vg_data[$unic_id_node]["tags"];

				if (array_key_exists("permalink", $db->record)) {
					$vg_data[$unic_id_node]["permalink"]								= $db->record["permalink"];
					$vg_data[$unic_id_node]["smart_url"] 								= basename($db->record["permalink"]);
					$vg_data[$unic_id_node]["permalink_parent"] 						= ffcommon_dirname($db->record["permalink"]);
					$db->record["permalink_parent"] 									= $vg_data[$unic_id_node]["permalink_parent"];
				} else {
					if (array_key_exists("smart_url", $db->record))
						$vg_data[$unic_id_node]["smart_url"] 							= $db->record["smart_url"];
					if (array_key_exists("parent", $db->record))
						$vg_data[$unic_id_node]["permalink_parent"] 					= $db->record["parent"];
				}

				if (array_key_exists("meta_title", $db->record))
				    $vg_data[$unic_id_node]["meta"]["title"] 							= $db->record["meta_title"];
				if (array_key_exists("meta_title_alt", $db->record))
				    $vg_data[$unic_id_node]["meta"]["title_h1"] 						= $db->record["meta_title_alt"];
				if (array_key_exists("meta_description", $db->record))
				    $vg_data[$unic_id_node]["meta"]["description"] 						= $db->record["meta_description"];
				if (array_key_exists("keywords", $db->record))
				    $vg_data[$unic_id_node]["meta"]["keywords"] 						= $db->record["keywords"];
				if($db->record["geo.position"])
					$globals->meta["geo.position"] 										= $db->record["geo.position"];
				if($db->record["geo.placename"])
					$globals->meta["geo.placename"] 									= $db->record["geo.placename"];
				if($db->record["geo.region"])
					$globals->meta["geo.region"] 										= $db->record["geo.region"];

				$count_files++;
		    } while ($db->nextRecord());
		}
	} else {
		//$vg_data = $vg_father["nodes"];
		//print_r($vg_data);
		if (is_array($vg_father["nodes"]) && count($vg_father["nodes"])) {
			$nodes = $vg_father["nodes"];
			if($vg_father["sort_default"] && count($nodes) > 1) {
				usort($nodes, function($prev, $next) use ($vg_father) {
					$prev = $prev["priority"] . "-" . $prev["data"][$vg_father["sort_default"]];
					$next = $next["priority"] . "-" . $next["data"][$vg_father["sort_default"]];
					$tmp = array($prev, $next);
					sort($tmp);

					return ($tmp[0] == $next);
				});
			}

		    foreach ($nodes AS $node) 
		    {
		    	$unic_id_node = $node["ID"];
				$ID_node = $node["ID"];
				if ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"]) {
				    $pricelist["nodes"][$ID_node] = $ID_node;

				    $vg_data[$unic_id_node]["pricelist"] = $vg_father["pricelist"];
				} else {
				    $vg_data[$unic_id_node]["pricelist"] = null;
				}

				$vg_data[$unic_id_node]["ID_cart_detail"] = null;

				$vg_data[$unic_id_node]["preload"] = $node["preload"];

				$vg_key["nodes"][$ID_node] = $ID_node;
				$vg_key["type"][$node["ID_type"]] = $vg_father["limit"]["fields"];

				$vg_data[$unic_id_node]["ID"] 										= $ID_node;
				$vg_data[$unic_id_node]["parent"]									= $node["parent"];
				$vg_data[$unic_id_node]["name"] 									= $node["name"];
				$vg_data[$unic_id_node]["ID_type"] 									= $node["ID_type"];
				$vg_data[$unic_id_node]["type"] 									= $node["type"];
				$vg_data[$unic_id_node]["is_dir"] 									= $node["is_dir"];
				//$vg_data[$unic_id_node]["data_type_publish"] = "";
				//$vg_data[$unic_id_node]["vgallery_name"]                                      = $node["vgallery_name"];
				$vg_data[$unic_id_node]["created"] 									= $node["created"];
				$vg_data[$unic_id_node]["last_update"] 								= $node["last_update"];
				$vg_data[$unic_id_node]["published"] 								= $node["published"];
				$vg_data[$unic_id_node]["owner"] 									= $node["owner"];
				$vg_data[$unic_id_node]["class"] 									= $node["class"];
				$vg_data[$unic_id_node]["highlight"]["container"] 					= $node["highlight_container"];
				$vg_data[$unic_id_node]["highlight"]["image"]["src"] 				= get_image_properties_by_grid_system(
																						$node["highlight_image"]
																						, $node["highlight_image_md"]
																						, $node["highlight_image_sm"]
																						, $node["highlight_image_xs"]
																					);				

				$vg_data[$unic_id_node]["is_wishlisted"] = false;
				$vg_data[$unic_id_node]["tags"] = $node["tags"];
                //if($vg_data[$unic_id_node]["tags"])
                //    $vg_father["seo"]["tags"]["secondary"]                              .= ($vg_father["seo"]["tags"]["secondary"] ? "," : "") . $vg_data[$unic_id_node]["tags"];
				//$vg_data[$unic_id_node]["enable_multilang_visible"]                           = $node["enable_multilang_visible"];
				$vg_data[$unic_id_node]["data"] = $node["data"];


				if (array_key_exists("permalink", $node)) {
					$vg_data[$unic_id_node]["smart_url"] 								= basename($node["permalink"]);
					$vg_data[$unic_id_node]["permalink_parent"] 						= ffcommon_dirname($node["permalink"]);
				} else {
					if (array_key_exists("smart_url", $node))
						$vg_data[$unic_id_node]["smart_url"] 							= $node["smart_url"];
					if (array_key_exists("parent", $node))
						$vg_data[$unic_id_node]["permalink_parent"] 					= $node["parent"];
				}

				if (array_key_exists("meta_title", $node))
				    $vg_data[$unic_id_node]["meta"]["title"] 							= $node["meta_title"];
				if (array_key_exists("meta_title_alt", $node))
				    $vg_data[$unic_id_node]["meta"]["title_h1"] 						= $node["meta_title_alt"];
				if (array_key_exists("meta_description", $node))
				    $vg_data[$unic_id_node]["meta"]["description"] 						= $node["meta_description"];
				if (array_key_exists("keywords", $node))
				    $vg_data[$unic_id_node]["meta"]["keywords"] 						= $node["keywords"];
				if($node["meta"]["geo.position"])
					$globals->meta["geo.position"] 										= $node["meta"]["geo.position"];
				if($node["meta"]["geo.placename"])
					$globals->meta["geo.placename"] 									= $node["meta"]["geo.placename"];
				if($node["meta"]["geo.region"])
					$globals->meta["geo.region"] 										= $node["meta"]["geo.region"];

		    }
		}
    }

    if (count($vg_data)) {
	    if (ENABLE_STD_PERMISSION && check_function("get_file_permission")) {
	        get_file_permission(null, "vgallery_nodes", array_keys($vg_key["nodes"]), null, $vg_data);
	    }

	    if (!$vg_father["enable_found_rows"])
	        $total_count = count($vg_data);

	    if ($vg_father["navigation"]["location"] && count($vg_data) > $vg_father["navigation"]["rec_per_page"]) {
	        $vg_data = array_slice($vg_data, ($vg_father["navigation"]["page"] - 1) * $vg_father["navigation"]["rec_per_page"], $vg_father["navigation"]["rec_per_page"], true);
	    }

	    if ($vg_father["wishlist"] !== null) {
	        $vg_key["actual_nodes"] = array_keys($vg_father["wishlist"]["nodes"]);
	    } elseif ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"]) {
	        $vg_key["actual_nodes"] = array_keys($pricelist["nodes"]);
	    } else {
	        $vg_key["actual_nodes"] = array_keys($vg_data);
	    }
    }

    if ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]) {
	    $db->query("SELECT FOUND_ROWS() AS tot_row");
	    if ($db->nextRecord()) {
	        $total_count = $db->getField("tot_row", "Number", true);
	    }
    }

    if (is_array($vg_key["type"])) {
	/*
	  if(is_array($vg_father["limit_type"]))
	  $vg_father["limit_type"] = array_replace($vg_father["limit_type"], array_values($vg_key["type"]));
	  else
	  $vg_father["limit_type"] = $vg_key["type"];
	 */

	if (is_array($vg_father["limit_type"])) {
	    if ($vg_father["mode"] == "thumb")
		$vg_father["limit_type"] = array_replace($vg_father["limit_type"], array_keys($vg_key["type"]));
	    else
		$vg_father["limit_type"] = array_intersect_assoc($vg_father["limit_type"], array_keys($vg_key["type"]));
	} else
	    $vg_father["limit_type"] = array_keys($vg_key["type"]);
    }

    //Set Pages in PageNavigator
    if (array_key_exists("navigation", $vg_father) && !$vg_father["navigation"]["infinite"]) {
		$vg_father["navigation"]["obj"]->num_rows = $total_count;
		$vg_father["navigation"]["tot_page"] = ceil($total_count / $vg_father["navigation"]["rec_per_page"]);
    }

    return array("data" => $vg_data
		, "key" => $vg_key["actual_nodes"]
		, "type" => $vg_key["type"]
		, "count" => $total_count
    );
}

function process_vgallery_anagraph_sql(&$vg_father, $settings, $query = null) {
    $db = ffDB_Sql::factory();
    /* DA TOGLIERE IN QUALCHE MODO
      , (" . ($vg_father["enable_user_sort"]
      ? ($vg_father["sort_default"] == "lastupdate"
      ? "vgallery_nodes.last_update"
      : "SELECT IF(anagraph_rel_nodes_fields.`description_text` = ''
      , anagraph_rel_nodes_fields.`description`
      , anagraph_rel_nodes_fields.`description_text`
      )
      FROM anagraph_rel_nodes_fields
      INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
      WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
      AND anagraph_fields.name = " .  $db->toSql($vg_father["sort_default"], "Text") . "
      AND anagraph_fields.ID_type = anagraph.ID_type
      LIMIT 1"
      )
      : "SELECT IF(anagraph_rel_nodes_fields.`description_text` = ''
      , anagraph_rel_nodes_fields.`description`
      , anagraph_rel_nodes_fields.`description_text`
      )
      FROM anagraph_rel_nodes_fields
      INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
      WHERE anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
      AND anagraph_fields.name = " .  $db->toSql($vg_father["sort_default"], "Text") . "
      AND anagraph_fields.ID_type = anagraph.ID_type
      LIMIT 1"
      ) . ") AS actual_sort

     */


	 if(!is_array($query["order"]))
		$query["order"] = array();

	if($vg_father["sort_fixed"])
	 	$query["order"] = array("user" => "anagraph.`order`") + $query["order"];
    if(1)
		$query["order"] = array("priority" => "anagraph.`priority` DESC") + $query["order"];

	$query["order"]["last_update"] = "anagraph.`last_update` DESC";

    $sSQL_nodes = "SELECT DISTINCT " .
	    ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]
	    	? "SQL_CALC_FOUND_ROWS" 
	    	: ""
	    ) . " 
	    anagraph.ID			    			AS ID
	    , 0				    				AS ID_cart_detail
	    , 0				    				AS ID_pricelist
	    , ''				   	 			AS pricelist_since
	    , ''				    			AS pricelist_to
	    , anagraph.parent		    		AS parent
	    , anagraph.ID_type		    		AS `ID_type`
	    , anagraph_type.name		    	AS `type`
	    , 0				    				AS is_dir
		, anagraph.`created`				AS `created`
		, anagraph.`last_update`			AS `last_update`
		, anagraph.`published_at`			AS `published_at`
		, anagraph.`class`		    		AS `class`			
	    , anagraph.`highlight`		    	AS `highlight_container`
		, anagraph.`highlight_ID_image`		AS `highlight_image`
		, anagraph.`highlight_ID_image_md`	AS `highlight_image_md`
		, anagraph.`highlight_ID_image_sm`	AS `highlight_image_sm`
		, anagraph.`highlight_ID_image_xs`	AS `highlight_image_xs`
	    , 0				    				AS is_wishlisted
	    , anagraph.owner		    		AS `owner`
		, anagraph.tags			    		AS `tags`
	    " . (is_array($query["select"]) 
	        ? ", " . implode(", ", $query["select"]) 
	        : ""
	    ) . "
	    , anagraph.`permalink`          	AS `permalink`
	    , anagraph.`smart_url`              AS `smart_url`
	    , anagraph.`meta_title`             AS `meta_title`
	    , anagraph.`meta_description`       AS `meta_description`
	    , anagraph.`keywords`               AS `keywords`
	    , anagraph.`avatar`                 AS `avatar`
	    , anagraph.`billreference`          AS `billreference`
	    , anagraph.`billcf`                 AS `billcf`
	    , anagraph.`billpiva`               AS `billpiva`
	    , anagraph.`billaddress`            AS `billaddress`
	    , anagraph.`billcap`                AS `billcap`
	    , anagraph.`billtown`               AS `billtown`
	    , anagraph.`billprovince`           AS `billprovince`
	    , anagraph.`billstate`              AS `billstate`
	    , anagraph.`name`                   AS `name`
	    , anagraph.`surname`                AS `surname`
	    , anagraph.`tel`                    AS `tel`
	    , anagraph.`email`                  AS `email`
	    , anagraph.`shippingreference`      AS `shippingreference`
	    , anagraph.`shippingaddress`        AS `shippingaddress`
	    , anagraph.`shippingcap`            AS `shippingcap`
	    , anagraph.`shippingtown`           AS `shippingtown`
	    , anagraph.`shippingprovince`       AS `shippingprovince`
	    , anagraph.`shippingstate`          AS `shippingstate`
	FROM anagraph
	    INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type
	    " . ($vg_father["group"]["smart_url"] 
	        ? " INNER JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories) "
	        : ""
	    ) . "
	    " . (is_array($query["from"]) 
	    	? implode(" ", $query["from"]) 
	    	: ""
	    ) . "
    WHERE anagraph.visible > 0
        AND anagraph.`permalink` != ''
    	" . ($vg_father["group"]["ID"] 
    		? " AND FIND_IN_SET(" . $db->toSql($vg_father["group"]["ID"], "Number") . ", anagraph.categories) "
		    : ""
	    ) . "
		" . (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["nodes"]) && count($vg_father["limit"]["nodes"]) 
			? " AND anagraph.ID IN(" . $db->toSql(implode(", ", $vg_father["limit"]["nodes"]), "Text", false) . ") " 
			: ""
	    ) . "
		" . (is_array($query["where"]) 
			? " AND " . implode(" AND ", $query["where"])
			: ""
	    ) . "
    GROUP BY anagraph.ID
    ORDER BY "
	    . (is_array($query["order"]) 
	    	? implode(", ", $query["order"])
	    	: ""
	    )
	    . ($vg_father["enable_found_rows"] 
	    	? " LIMIT " . (($vg_father["navigation"]["page"] - 1) * ($vg_father["limit"]["elem"] > 0 
	    			? $vg_father["limit"]["elem"] 
	    			: $vg_father["navigation"]["rec_per_page"]
		    	)) 
		    	. ", " .
			    ($vg_father["limit"]["elem"] > 0 
		    		? $vg_father["limit"]["elem"] 
		    		: $vg_father["navigation"]["rec_per_page"]
			    ) 
		    : ($vg_father["limit"]["elem"] > 0 
		    	? " LIMIT " . $vg_father["limit"]["elem"] 
		    	: ""
		    )
	    );

    return $sSQL_nodes;
}

function process_anagraph_publishing_sql($vg_father, $settings, $query = null) {
    $db = ffDB_Sql::factory();

    $SQL_criteria = "";
    $sSQL = "SELECT publishing_criteria.* 
            FROM publishing_criteria 
            WHERE publishing_criteria.ID_publishing = " . $db->toSql($vg_father["publishing"]["ID"], "Number");
    $db->query($sSQL);
    if ($db->nextRecord()) {
	do {
	    if (substr($db->getField("value")->getValue(), 0, 1) === "[" && substr($db->getField("value")->getValue(), -1, 1) === "]") {
			$critetia_value = substr($db->getField("value")->getValue(), 1, -1);
			$critetia_value_encloser = false;
	    } else {
			$critetia_value = $db->getField("value")->getValue();
			$critetia_value_encloser = true;
	    }

	    $SQL_criteria .= " AND ";
	    $SQL_criteria .= "
                                " . $vg_father["src"]["table"] . ".ID
                                IN (
                                    SELECT " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_nodes AS ID
                                    FROM " . $vg_father["src"]["type"] . "_rel_nodes_fields
                                        INNER JOIN " . $vg_father["src"]["type"] . "_fields ON " . $vg_father["src"]["type"] . "_fields.ID = " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_fields 
                                    WHERE " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $vg_father["src"]["table"] . ".ID
                                        AND " . $vg_father["src"]["type"] . "_fields.name = " . $db->toSql($db->getField("src_fields")->getValue(), "Text") . "
                                        AND " . $vg_father["src"]["type"] . "_fields.ID_type = " . $vg_father["src"]["table"] . ".ID_type
                                        AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.uid = IF(" . $vg_father["src"]["type"] . "_rel_nodes_fields.`nodes` = ''
                                            , 0
                                            , " . $db->toSql((get_session("UserID") == MOD_SEC_GUEST_USER_NAME ? "0" : get_session("UserNID")), "Number") . "
                                        )
                                        AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.description " . $db->getField("operator")->getValue() . " " . ($critetia_value == "''" 
		                                    ? "''"
		                                    : $db->toSql($critetia_value, "Text", $critetia_value_encloser) 
		                                ) . "
                                ) 
                            ";
	} while ($db->nextRecord());
    }
	
	if(!is_array($query["order"]))
		$query["order"] = array();

	if($vg_father["sort_fixed"] )
		$query["order"] = array("fixed" => $vg_father["src"]["table"] . ".`order`") + $query["order"];
	if($settings["AREA_VGALLERY_LIST_SHOW_GROUP"])
		$query["order"] = array("group" => $vg_father["src"]["table"] . ".parent") + $query["order"];
	if(!$vg_father["automatic_selection"])
		$query["order"] = array("rel" => "rel_nodes.`order`") + $query["order"];    
    
	$query["order"]["last_update"] = $vg_father["src"]["table"] . ".`last_update` DESC";    

    $sSQL_nodes = "SELECT DISTINCT " .
	    ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]
            ? "SQL_CALC_FOUND_ROWS" 
            : ""
	    ) . "
        " . $vg_father["src"]["table"] . ".ID                           			AS ID
        , 0                                                             			AS ID_cart_detail
        , 0                                                             			AS ID_pricelist
        , ''                                                            			AS pricelist_since
        , ''                                                            			AS pricelist_to
        , " . $vg_father["src"]["table"] . ".parent                     			AS parent
        , " . $vg_father["src"]["table"] . ".ID_type                    			AS `ID_type`
        , " . $vg_father["src"]["type"] . "_type.name                   			AS `type`
        , 0                                 										AS is_dir
		, " . $vg_father["src"]["table"] . ".`created`								AS `created`
		, " . $vg_father["src"]["table"] . ".`last_update`							AS `last_update`
		, " . $vg_father["src"]["table"] . ".`published_at`							AS `published_at`
        " . ($vg_father["automatic_selection"] 
        	? 	", " . $vg_father["src"]["table"] . ".`class` 						AS `class`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight` 				AS `highlight_container`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image` 		AS `highlight_image`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_md`		AS `highlight_image_md`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_sm` 	AS `highlight_image_sm`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_xs` 	AS `highlight_image_xs`" 
        	: 	", rel_nodes.`class` 												AS `class`" 
        		. ", rel_nodes.`highlight` 											AS `highlight_container`" 
        		. ", rel_nodes.`highlight_ID_image` 								AS `highlight_image`" 
        		. ", rel_nodes.`highlight_ID_image_md`								AS `highlight_image_md`" 
        		. ", rel_nodes.`highlight_ID_image_sm` 								AS `highlight_image_sm`" 
        		. ", rel_nodes.`highlight_ID_image_xs` 								AS `highlight_image_xs`" 
        ) . "
        , 0                                                             			AS is_wishlisted
        , " . $vg_father["src"]["table"] . ".owner                      			AS `owner`
		, " . $vg_father["src"]["table"] . ".tags									AS `tags`
        " . (is_array($query["select"]) 
        	? ", " . implode(", ", $query["select"]) 
        	: ""
	    ) . "
        , " . $vg_father["src"]["table"] . ".`permalink`                			AS `permalink`
        , " . $vg_father["src"]["table"] . ".`smart_url`                			AS `smart_url`
        , " . $vg_father["src"]["table"] . ".`meta_title`               			AS `meta_title`
        , " . $vg_father["src"]["table"] . ".`meta_description`         			AS `meta_description`
        , " . $vg_father["src"]["table"] . ".`keywords`                 			AS `keywords`
        , " . $vg_father["src"]["table"] . ".`avatar`                   			AS `avatar`
        , " . $vg_father["src"]["table"] . ".`billreference`            			AS `billreference`
        , " . $vg_father["src"]["table"] . ".`billcf`                   			AS `billcf`
        , " . $vg_father["src"]["table"] . ".`billpiva`                 			AS `billpiva`
        , " . $vg_father["src"]["table"] . ".`billaddress`              			AS `billaddress`
        , " . $vg_father["src"]["table"] . ".`billcap`                  			AS `billcap`
        , " . $vg_father["src"]["table"] . ".`billtown`                 			AS `billtown`
        , " . $vg_father["src"]["table"] . ".`billprovince`             			AS `billprovince`
        , " . $vg_father["src"]["table"] . ".`billstate`                			AS `billstate`
        , " . $vg_father["src"]["table"] . ".`name`                     			AS `name`
        , " . $vg_father["src"]["table"] . ".`surname`                  			AS `surname`
        , " . $vg_father["src"]["table"] . ".`tel`                      			AS `tel`
        , " . $vg_father["src"]["table"] . ".`email`                    			AS `email`
        , " . $vg_father["src"]["table"] . ".`shippingreference`        			AS `shippingreference`
        , " . $vg_father["src"]["table"] . ".`shippingaddress`          			AS `shippingaddress`
        , " . $vg_father["src"]["table"] . ".`shippingcap`              			AS `shippingcap`
        , " . $vg_father["src"]["table"] . ".`shippingtown`             			AS `shippingtown`
        , " . $vg_father["src"]["table"] . ".`shippingprovince`         			AS `shippingprovince`
        , " . $vg_father["src"]["table"] . ".`shippingstate`            			AS `shippingstate`
    FROM " . $vg_father["src"]["table"] . "
        INNER JOIN " . $vg_father["src"]["type"] . "_type ON " . $vg_father["src"]["type"] . "_type.ID = " . $vg_father["src"]["table"] . ".ID_type
        " . ($vg_father["automatic_selection"] ? "" : "    INNER JOIN rel_nodes  
                    ON 
                    (
                        (
                            rel_nodes.ID_node_src = " . $vg_father["src"]["table"] . ".ID 
                            " . (strlen($vg_father["vgallery_name"]) ? " AND rel_nodes.contest_src = " . $db->toSql($vg_father["vgallery_name"], "Text") : ""
		    ) . "
                            AND rel_nodes.ID_node_dst = " . $db->toSql($vg_father["publishing"]["ID"], "Number") . "
                            AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . "
                        ) 
                    OR 
                        (
                            rel_nodes.ID_node_dst = " . $vg_father["src"]["table"] . ".ID 
                            " . (strlen($vg_father["vgallery_name"]) ? " AND rel_nodes.contest_dst = " . $db->toSql($vg_father["vgallery_name"], "Text") : ""
		    ) . "
                            AND rel_nodes.ID_node_src = " . $db->toSql($vg_father["publishing"]["ID"], "Number") . "
                            AND rel_nodes.contest_src = " . $db->toSql("publishing", "Text") . "
                        )
                    )"
	    ) . "
        " . (is_array($query["from"]) ? implode(" ", $query["from"]) : ""
	    ) . "        
    WHERE " . $vg_father["src"]["table"] . ".visible > 0
    	AND " . $vg_father["src"]["table"] . ".`permalink` != ''
        " . ($vg_father["group"]["ID"] 
        	? " AND FIND_IN_SET(" . $db->toSql($vg_father["group"]["ID"], "Number") . ", " . $vg_father["src"]["table"] . ".categories) "
		    : ""
	    ) .
	    ($vg_father["automatic_selection"] 
	    	? "" 
	    	: " AND
                (
                    1 
                    AND (
                            (   
                                rel_nodes.date_begin = '0000-00-00'
                                OR  rel_nodes.date_begin <= CURDATE() 
                            )
                        AND 
                            (
                                rel_nodes.date_end = '0000-00-00'
                                OR  rel_nodes.date_end >= CURDATE() 
                            )
                        )
                )"
	    ) . "
        " . ($SQL_criteria 
        	? $SQL_criteria 
        	: ""
	    ) . "
		" . (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["nodes"]) && count($vg_father["limit"]["nodes"]) 
			? " AND " . $vg_father["src"]["table"] . ".ID IN(" . $db->toSql(implode(", ", $vg_father["limit"]["nodes"]), "Text", false) . ") " 
			: ""
	    ) . "
        " . (is_array($query["where"]) 
        	? " AND " . implode(" AND ", $query["where"]) 
        	: ""
	    ) . "        
    ORDER BY "
	    . ($vg_father["random"] 
	    	? " RAND() " 
	    	: (is_array($query["order"]) 
		    	? implode(", ", $query["order"])
		    	: ""
			)
	    )
	    . ($vg_father["enable_found_rows"] 
	    	? " LIMIT " . (($vg_father["navigation"]["page"] - 1) * ($vg_father["limit"]["elem"] > 0 
	    		? $vg_father["limit"]["elem"] 
	    		: $vg_father["navigation"]["rec_per_page"]
		    	) )
		    	. ", " .
			    ($vg_father["limit"]["elem"] > 0 
		    		? $vg_father["limit"]["elem"] 
		    		: $vg_father["navigation"]["rec_per_page"]
			    ) 
		    : ($vg_father["limit"]["elem"] > 0 
		    	? " LIMIT " . $vg_father["limit"]["elem"] 
		    	: ""
		    )
	    );

    //echo $sSQL_nodes . "<br><br>"; 
    return $sSQL_nodes;
}

function process_vgallery_publishing_sql($vg_father, $settings, $query = null) {
    $db = ffDB_Sql::factory();

    $SQL_criteria = "";
    $sSQL = "SELECT publishing_criteria.* 
    		FROM publishing_criteria 
    		WHERE publishing_criteria.ID_publishing = " . $db->toSql($vg_father["publishing"]["ID"], "Number");
    $db->query($sSQL);
    if ($db->nextRecord()) {
	do {
	    if (substr($db->getField("value")->getValue(), 0, 1) === "[" && substr($db->getField("value")->getValue(), -1, 1) === "]") {
			$critetia_value = substr($db->getField("value")->getValue(), 1, -1);
			$critetia_value_encloser = false;
	    } else {
			$critetia_value = $db->getField("value")->getValue();
			$critetia_value_encloser = true;
	    }


	    $SQL_criteria .= " AND ";
	    $SQL_criteria .= "
                                " . $vg_father["src"]["table"] . ".ID
                                IN (
									SELECT " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_nodes AS ID
						            FROM " . $vg_father["src"]["type"] . "_rel_nodes_fields
						                INNER JOIN " . $vg_father["src"]["type"] . "_fields ON " . $vg_father["src"]["type"] . "_fields.ID = " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_fields 
								        INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = " . $vg_father["src"]["type"] . "_fields.ID_data_type
						            WHERE " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $vg_father["src"]["table"] . ".ID
                                        AND " . $vg_father["src"]["type"] . "_fields.name = " . $db->toSql($db->getField("src_fields")->getValue(), "Text") . "
                                        AND " . $vg_father["src"]["type"] . "_fields.ID_type = " . $vg_father["src"]["table"] . ".ID_type
						                AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.uid = IF(" . $vg_father["src"]["type"] . "_rel_nodes_fields.`nodes` = ''
						                    , 0
						                    , " . $db->toSql((get_session("UserID") == MOD_SEC_GUEST_USER_NAME ? "0" : get_session("UserNID")), "Number") . "
						                )
                                        AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_lang = " . ($vg_father["enable_multilang_visible"] 
									    	? "IF(" . $vg_father["src"]["type"] . "_fields.disable_multilang > 0 
													OR " . $vg_father["src"]["type"] . "_fields_data_type.name IN('relationship', 'media') 
                                						, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
                                						, " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                			)" 
                                			: $db->toSql(LANGUAGE_DEFAULT_ID, "Number")
									    ) . "
                                        AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.description " . $db->getField("operator")->getValue() . " " . ($critetia_value == "''" 
		                                    ? "''"
		                                    : $db->toSql($critetia_value, "Text", $critetia_value_encloser) 
		                                ) . "
                                ) 
                            ";
	} while ($db->nextRecord());
    }

    if(!is_array($query["order"]))
		$query["order"] = array();

	if($vg_father["sort_fixed"] )
		$query["order"] = array("fixed" => $vg_father["src"]["table"] . ".`order`") + $query["order"];
	if($settings["AREA_VGALLERY_LIST_SHOW_GROUP"])
		$query["order"] = array("group" => $vg_father["src"]["table"] . ".parent") + $query["order"];
	if(!$vg_father["automatic_selection"])
		$query["order"] = array("rel" => "rel_nodes.`order`") + $query["order"];    
    
	if($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"])
		$query["order"]["ecommerce"] = "pricelist_since " . $vg_father["sort_method"] . ", pricelist_to " . $vg_father["sort_method"];

	$query["order"]["last_update"] = $vg_father["src"]["table"] . ".`last_update` DESC";        
    
    $sSQL_nodes = "SELECT DISTINCT " .
	    ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]
	    	? "SQL_CALC_FOUND_ROWS" 
	    	: ""
	    ) . "
		" . $vg_father["src"]["table"] . ".ID                           					AS ID 
		, " . $vg_father["src"]["table"] . ".name                       					AS name 
		, " . $vg_father["src"]["table"] . ".`parent`										AS `parent`
		, " . $vg_father["src"]["table"] . ".`order`                    					AS `order` 
		, " . $vg_father["src"]["table"] . ".ID_type                    					AS ID_type 
		, " . $vg_father["src"]["table"] . ".is_dir                     					AS is_dir 
		, " . $vg_father["src"]["table"] . ".`created`										AS `created`
		, " . $vg_father["src"]["table"] . ".`last_update`									AS `last_update`
		, " . $vg_father["src"]["table"] . ".`published_at`									AS `published_at`
		" . ($vg_father["automatic_selection"] 
        	? 	", " . $vg_father["src"]["table"] . ".`class` 								AS `class`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight` 						AS `highlight_container`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image` 				AS `highlight_image`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_md`				AS `highlight_image_md`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_sm` 			AS `highlight_image_sm`" 
        		. ", " . $vg_father["src"]["table"] . ".`highlight_ID_image_xs` 			AS `highlight_image_xs`" 
        	: 	", rel_nodes.`class` 														AS `class`" 
        		. ", rel_nodes.`highlight` 													AS `highlight_container`" 
        		. ", rel_nodes.`highlight_ID_image` 										AS `highlight_image`" 
        		. ", rel_nodes.`highlight_ID_image_md`										AS `highlight_image_md`" 
        		. ", rel_nodes.`highlight_ID_image_sm` 										AS `highlight_image_sm`" 
        		. ", rel_nodes.`highlight_ID_image_xs` 										AS `highlight_image_xs`" 
        ) . "		
		, " . $vg_father["src"]["table"] . ".owner                      					AS owner
		, " . $vg_father["src"]["table"] . ".tags                      						AS tags
	    , " . $vg_father["src"]["type"] . "_type.name                   					AS type
	    , 0                                                             					AS ID_cart_detail
		" . (OLD_VGALLERY
			? "
				, " . $vg_father["src"]["table"] . ".visible								AS `visible`
			"
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? "
					, " . $vg_father["src"]["table"] . ".permalink							AS permalink
					, " . $vg_father["src"]["table"] . ".keywords							AS keywords
					, " . $vg_father["src"]["table"] . ".meta_description					AS meta_description
					, " . $vg_father["src"]["table"] . ".meta_title							AS meta_title
					, " . $vg_father["src"]["table"] . ".meta_title_alt						AS meta_title_alt
					, " . $vg_father["src"]["table"] . ".`parent`							AS permalink_parent
					, " . $vg_father["src"]["table"] . ".name								AS smart_url
					, " . $vg_father["src"]["table"] . ".visible							AS `visible`

				"
				: "
					, " . $vg_father["src"]["table"] . "_rel_languages.permalink			AS permalink
					, " . $vg_father["src"]["table"] . "_rel_languages.keywords				AS keywords
					, " . $vg_father["src"]["table"] . "_rel_languages.meta_description		AS meta_description
					, " . $vg_father["src"]["table"] . "_rel_languages.meta_title			AS meta_title
					, " . $vg_father["src"]["table"] . "_rel_languages.meta_title_alt		AS meta_title_alt
					, " . $vg_father["src"]["table"] . "_rel_languages.permalink_parent		AS permalink_parent
					, " . $vg_father["src"]["table"] . "_rel_languages.smart_url			AS smart_url
					, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
						? " " . $vg_father["src"]["table"] . "_rel_languages.visible "
						: " " . $vg_father["src"]["table"] . ".visible "
					) . "																	AS `visible`
					
				"
			)
		) . "	    
	    " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] 
	    	? "
	            , ecommerce_pricelist.ID AS ID_pricelist
	            , IF(ecommerce_settings.`type` = 'bytime'
	                , ecommerce_pricelist.date_since
	                , ecommerce_pricelist.qta_since
	            )                                                       					AS pricelist_since
	            , IF(ecommerce_settings.`type` = 'bytime'
	                , ecommerce_pricelist.date_to
	                , ecommerce_pricelist.qta_to
	            )                                                       					AS pricelist_to
	        " 
	        : "
	            , 0 AS ID_pricelist
	            , 0 AS pricelist_since
	            , 0 AS pricelist_to
	        "
	    ) . "
		" . (is_array($query["select"]) ? ", " . implode(", ", $query["select"]) : ""
	    ) . " 
	    , (" . (!($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE) && USE_CART_PUBLIC_MONO ? "(SELECT ecommerce_order_detail.ID
	            FROM ecommerce_order_detail
	                INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
	            WHERE ecommerce_order_detail.ID_items = " . $vg_father["src"]["table"] . ".ID
	                AND ecommerce_order_detail.tbl_src = '" . $vg_father["src"]["table"] . "'
	                AND ecommerce_order.ID_user_cart = " . $db->toSql(get_session("UserNID"), "Number") . "
	                AND ecommerce_order.cart_name = " . $db->toSql(ffCommon_url_rewrite(get_session("UserID"))) . " AND ecommerce_order.wishlist_archived = 0
	                AND ecommerce_order.is_cart > 0
	        )" : "''"
	    ) . ")                                                          					AS is_wishlisted
	FROM " . $vg_father["src"]["table"] . "
	    INNER JOIN " . $vg_father["src"]["type"] . "_type ON " . $vg_father["src"]["type"] . "_type.ID = " . $vg_father["src"]["table"] . ".ID_type
		" . (OLD_VGALLERY
			? ""
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? ""
				: " INNER JOIN " . $vg_father["src"]["table"] . "_rel_languages ON " . $vg_father["src"]["table"] . "_rel_languages.ID_nodes = " . $vg_father["src"]["table"] . ".ID
						AND " . $vg_father["src"]["table"] . "_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			)
		) . "	    
	    " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && ($vg_father["use_pricelist_as_item"] || AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK) ? "    INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = " . $vg_father["src"]["table"] . ".ID 
	                    " . ($vg_father["use_pricelist_as_item"] ? " INNER JOIN ecommerce_pricelist ON ecommerce_pricelist.ID_ecommerce_settings = ecommerce_settings.ID
	                            " . (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK ? " AND ecommerce_pricelist.actual_qta > 0 " : ""
			    ) : (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK ? " AND ecommerce_settings.actual_qta > 0 " : ""
			    )
		    ) : ""
	    )
	    . ($vg_father["automatic_selection"] 
	    	? ""
	    	: "	INNER JOIN rel_nodes  
	                ON 
	                (
	                    (
	                        rel_nodes.ID_node_src = " . $vg_father["src"]["table"] . ".ID 
	                        " . (strlen($vg_father["vgallery_name"]) 
	                        	? " AND rel_nodes.contest_src = " . $db->toSql($vg_father["vgallery_name"], "Text") 
	                        	: ""
		    				) . "
	                        AND rel_nodes.ID_node_dst = " . $db->toSql($vg_father["publishing"]["ID"], "Number") . "
	                        AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . "
	                    ) 
	                OR 
	                    (
	                        rel_nodes.ID_node_dst = " . $vg_father["src"]["table"] . ".ID 
	                        " . (strlen($vg_father["vgallery_name"]) 
	                        	? " AND rel_nodes.contest_dst = " . $db->toSql($vg_father["vgallery_name"], "Text") 
	                        	: ""
		    				) . "
	                        AND rel_nodes.ID_node_src = " . $db->toSql($vg_father["publishing"]["ID"], "Number") . "
	                        AND rel_nodes.contest_src = " . $db->toSql("publishing", "Text") . "
	                    )
	                )"
	    ) . "
		" . (is_array($query["from"]) ? implode(" ", $query["from"]) : "") . " 
	WHERE 1
	    " . ($vg_father["enable_multi_cat"] || $settings["HIDE_DIR"]
	    	? " AND " . $vg_father["src"]["table"] . ".is_dir = 0 " 
	    	: ""
	    ) . "
	    " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] ? " AND (
	            (
	                ecommerce_settings.`type` = 'bytime'
	                AND ecommerce_pricelist.date_since > 0
	                AND ecommerce_pricelist.date_to > 0
	                AND ecommerce_pricelist.qta_since = 0
	                AND ecommerce_pricelist.qta_to = 0
	                AND FROM_UNIXTIME(ecommerce_pricelist.date_since, '%Y') = YEAR(CURDATE())
	                AND FROM_UNIXTIME(ecommerce_pricelist.date_to, '%Y') = YEAR(CURDATE())
	            )
	            OR 
	            (
	                ecommerce_settings.`type` = 'byqta'
	                AND ecommerce_pricelist.date_since = 0
	                AND ecommerce_pricelist.date_to = 0
	                AND ecommerce_pricelist.qta_since > 0
	                AND ecommerce_pricelist.qta_to > 0
	            )
	        )
	        " : ""
	    ) .
	    ($vg_father["automatic_selection"] 
	    	? " AND " . $vg_father["src"]["table"] . ".parent LIKE '" . $db->toSql($vg_father["user_path"], "Text", false) . "%'"
	    	: "	AND
                (
                    1 
                    AND (
                            (   
                                rel_nodes.date_begin = '0000-00-00'
                                OR  rel_nodes.date_begin <= CURDATE() 
                            )
                        AND 
                            (
                                rel_nodes.date_end = '0000-00-00'
                                OR  rel_nodes.date_end >= CURDATE() 
                            )
                        )
                )"
	    ) . "
		" . (ENABLE_STD_PERMISSION 
			? ""
			: (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION && !OLD_VGALLERY
				? " AND " . $vg_father["src"]["table"] . "_rel_languages.visible > 0 " 
				: " AND " . $vg_father["src"]["table"] . ".visible > 0 "
			)
		)
		. (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID 
			? " AND " . $vg_father["src"]["table"] . "_rel_languages.permalink != ''" 
			: " AND " . $vg_father["src"]["table"] . ".permalink != ''"
		)
		. ($SQL_criteria ? $SQL_criteria : "") . "
		" . (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["nodes"]) && count($vg_father["limit"]["nodes"]) 
			? " AND " . $vg_father["src"]["table"] . ".ID IN(" . $db->toSql(implode(", ", $vg_father["limit"]["nodes"]), "Text", false) . ") " 
			: ""
		) . "        
	    " . /* (check_mod($vg_father["permission"], 2)
	      ? ""
	      : " AND " . $vg_father["src"]["table"] . ".ID
	      NOT IN (
	      SELECT " . $vg_father["src"]["table"] . ".ID
	      FROM " . $vg_father["src"]["type"] . "_rel_nodes_fields
	      INNER JOIN " . $vg_father["src"]["type"] . "_fields ON " . $vg_father["src"]["type"] . "_fields.ID = " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_fields
	      INNER JOIN " . $vg_father["src"]["table"] . " ON " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $vg_father["src"]["table"] . ".ID
	      INNER JOIN " . $vg_father["src"]["type"] . "_type ON " . $vg_father["src"]["type"] . "_type.ID = " . $vg_father["src"]["type"] . "_fields.ID_type
	      WHERE
	      " . $vg_father["src"]["type"] . "_fields.name = " .  $db->toSql("visible", "Text") . "
	      AND " . $vg_father["src"]["type"] . "_type.name = " .  $db->toSql("System", "Text") . "
	      AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.ID_lang = "
	      . ($vg_father["enable_multilang_visible"]
	      ? "IF(" . $vg_father["src"]["type"] . "_fields.disable_multilang > 0
	      , " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
	      , " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
	      )"
	      : $db->toSql(LANGUAGE_DEFAULT_ID, "Number")
	      ) . "
	      AND " . $vg_father["src"]["type"] . "_rel_nodes_fields.description = " . $db->toSql("0", "Text") . "
	      )"
	      ) . */"
		" . (is_array($query["where"]) 
			? " AND " . implode(" AND ", $query["where"]) 
			: ""
	    ) . " 	    
	ORDER BY "
	    . ($vg_father["random"] 
	    	? " RAND() " 
	    	: (is_array($query["order"]) 
		    	? implode(", ", $query["order"])
		    	: ""
			)
	    )
	    . ($vg_father["enable_found_rows"] ? " LIMIT " . (($vg_father["navigation"]["page"] - 1) * ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) ) . ", " .
		    ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) : ($vg_father["limit"]["elem"] > 0 ? " LIMIT " . $vg_father["limit"]["elem"] : ""
		    )
	    );

    return $sSQL_nodes;
}

function process_vgallery_wishlist_sql($vg_father, $settings, $query = null) {
    $db = ffDB_Sql::factory();
	
	if(!is_array($query["order"]))
		$query["order"] = array();

	if($vg_father["sort_fixed"] )
		$query["order"] = array("fixed" => $vg_father["src"]["table"] . ".`order`") + $query["order"];
	if($settings["AREA_VGALLERY_LIST_SHOW_GROUP"])
		$query["order"] = array("group" => $vg_father["src"]["table"] . ".parent") + $query["order"];
    if(1)
		$query["order"] = array("priority" => $vg_father["src"]["table"] . ".`priority` DESC") + $query["order"];
	if(!$vg_father["automatic_selection"])
		$query["order"] = array("rel" => "rel_nodes.`order`") + $query["order"];    

	if($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"])
		$query["order"]["ecommerce"] = "pricelist_since " . $vg_father["sort_method"] . ", pricelist_to " . $vg_father["sort_method"];
    
	$query["order"]["last_update"] = $vg_father["src"]["table"] . ".`last_update` DESC";        

    $sSQL_nodes = "SELECT DISTINCT " .
	    ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]
            ? "SQL_CALC_FOUND_ROWS" 
            : ""
	    ) . " 
		vgallery_nodes.ID 												AS ID 
		, vgallery_nodes.name 											AS name 
		, vgallery_nodes.`parent`										AS `parent`
		, vgallery_nodes.`order` 										AS `order` 
		, vgallery_nodes.ID_type 										AS ID_type 
		, vgallery_nodes.is_dir 										AS is_dir 
		, vgallery_nodes.`created`										AS `created`
		, vgallery_nodes.`last_update`									AS `last_update`
		, vgallery_nodes.`published_at`									AS `published_at`
		, vgallery_nodes.class 											AS class 
		, vgallery_nodes.`highlight`									AS `highlight_container`
		, vgallery_nodes.`highlight_ID_image`							AS `highlight_image`
		, vgallery_nodes.`highlight_ID_image_md`						AS `highlight_image_md`
		, vgallery_nodes.`highlight_ID_image_sm`						AS `highlight_image_sm`
		, vgallery_nodes.`highlight_ID_image_xs`						AS `highlight_image_xs`
		, vgallery_nodes.owner 											AS owner
		, vgallery_nodes.tags 											AS tags
        , vgallery_type.name 											AS type 
        , ecommerce_order_detail.ID 									AS ID_cart_detail
		" . (OLD_VGALLERY
			? "
				, vgallery_nodes.visible 								AS `visible`
			"
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? "
					, vgallery_nodes.permalink							AS permalink
					, vgallery_nodes.keywords							AS keywords
					, vgallery_nodes.meta_description					AS meta_description
					, vgallery_nodes.meta_title							AS meta_title
					, vgallery_nodes.meta_title_alt						AS meta_title_alt
					, vgallery_nodes.`parent`							AS permalink_parent
					, vgallery_nodes.name								AS smart_url
					, vgallery_nodes.visible 							AS `visible`			    
				"
				: "
					, vgallery_nodes_rel_languages.permalink			AS permalink
					, vgallery_nodes_rel_languages.keywords				AS keywords
					, vgallery_nodes_rel_languages.meta_description		AS meta_description
					, vgallery_nodes_rel_languages.meta_title			AS meta_title
					, vgallery_nodes_rel_languages.meta_title_alt		AS meta_title_alt
					, vgallery_nodes_rel_languages.permalink_parent		AS permalink_parent
					, vgallery_nodes_rel_languages.smart_url			AS smart_url
					, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
						? " vgallery_nodes_rel_languages.visible "
						: " vgallery_nodes.visible "
					) . "												AS `visible`
				"
			)
		) . "
        
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] ? "
                , ecommerce_pricelist.ID AS ID_pricelist
                , IF(ecommerce_settings.`type` = 'bytime'
                    , ecommerce_pricelist.date_since
                    , ecommerce_pricelist.qta_since
                ) AS pricelist_since
                , IF(ecommerce_settings.`type` = 'bytime'
                    , ecommerce_pricelist.date_to
                    , ecommerce_pricelist.qta_to
                ) AS pricelist_to
            " : "
                , 0 AS ID_pricelist
                , 0 AS pricelist_since
                , 0 AS pricelist_to
            "
	    ) . "
		" . (is_array($query["select"]) ? ", " . implode(", ", $query["select"]) : ""
	    ) . "
        , '' AS is_wishlisted
    FROM ecommerce_order_detail
        INNER JOIN vgallery_nodes ON ecommerce_order_detail.ID_items = vgallery_nodes.ID
        INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_nodes.ID_type
		" . (OLD_VGALLERY
			? ""
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? ""
				: " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
						AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			)
		) . "        
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && ($vg_father["use_pricelist_as_item"] || AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK) 
            ? "    INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID 
                " . ($vg_father["use_pricelist_as_item"] 
                    ? " INNER JOIN ecommerce_pricelist ON ecommerce_pricelist.ID_ecommerce_settings = ecommerce_settings.ID
		                " . (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
                    		? " AND ecommerce_pricelist.actual_qta > 0 " 
                    		: ""
			    		) 
					: (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
			    		? " AND ecommerce_settings.actual_qta > 0 " 
			    		: ""
					)
			) 
			: ""
	    ) . "
		" . (is_array($query["from"]) 
			? implode(" ", $query["from"]) 
			: ""
	    ) . "
    WHERE
        ecommerce_order_detail.ID_order = " . $db->toSql($vg_father["wishlist"]["ID"], "Text") . "
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] ? " AND (
                (
                    ecommerce_settings.`type` = 'bytime'
                    AND ecommerce_pricelist.date_since > 0
                    AND ecommerce_pricelist.date_to > 0
                    AND ecommerce_pricelist.qta_since = 0
                    AND ecommerce_pricelist.qta_to = 0
                    AND FROM_UNIXTIME(ecommerce_pricelist.date_since, '%Y') = YEAR(CURDATE())
                    AND FROM_UNIXTIME(ecommerce_pricelist.date_to, '%Y') = YEAR(CURDATE())
                )
                OR 
                (
                    ecommerce_settings.`type` = 'byqta'
                    AND ecommerce_pricelist.date_since = 0
                    AND ecommerce_pricelist.date_to = 0
                    AND ecommerce_pricelist.qta_since > 0
                    AND ecommerce_pricelist.qta_to > 0
                )
            )
            " : ""
	    ) . "
		" . (ENABLE_STD_PERMISSION 
			? ""
			: (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION && !OLD_VGALLERY
				? " AND vgallery_nodes_rel_languages.visible > 0 " 
				: " AND vgallery_nodes.visible > 0 "
			)
		)
		. (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID 
			? " AND vgallery_nodes_rel_languages.permalink != ''" 
			: " AND vgallery_nodes.permalink != ''"
		)
		. (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["nodes"]) && count($vg_father["limit"]["nodes"]) 
			? " AND vgallery_nodes.ID IN(" . $db->toSql(implode(", ", $vg_father["limit"]["nodes"]), "Text", false) . ") " 
			: ""
	    ) . "  
		" . (is_array($query["where"]) 
			? " AND " . implode(" AND ", $query["where"]) 
			: ""
	    ) . "
    ORDER BY "
	    . (is_array($query["order"]) 
		    ? implode(", ", $query["order"])
		    : ""
		)
	    . ($vg_father["enable_found_rows"] ? " LIMIT " . (($vg_father["navigation"]["page"] - 1) * ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) ) . ", " .
		    ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) : ($vg_father["limit"]["elem"] > 0 ? " LIMIT " . $vg_father["limit"]["elem"] : ""
		    )
	    );

    return $sSQL_nodes;
}

function process_vgallery_node_sql($vg_father, $settings, $query = null) {
    $db = ffDB_Sql::factory();
    
	if(!is_array($query["order"]))
		$query["order"] = array();

	if($vg_father["sort_fixed"] )
		$query["order"] = array("fixed" => $vg_father["src"]["table"] . ".`order`") + $query["order"];
	if($settings["AREA_VGALLERY_LIST_SHOW_GROUP"])
		$query["order"] = array("group" => $vg_father["src"]["table"] . ".parent") + $query["order"];
    if(1)
		$query["order"] = array("priority" => $vg_father["src"]["table"] . ".`priority` DESC") + $query["order"];
	if(!$vg_father["automatic_selection"])
		$query["order"] = array("rel" => "rel_nodes.`order`") + $query["order"];    
    
	if($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"])
		$query["order"]["ecommerce"] = "pricelist_since " . $vg_father["sort_method"] . ", pricelist_to " . $vg_father["sort_method"];
	    
	$query["order"]["last_update"] = $vg_father["src"]["table"] . ".`last_update` DESC";    

    $sSQL_nodes = "SELECT DISTINCT " .
	    ($vg_father["enable_found_rows"] && !$vg_father["navigation"]["infinite"]
	    	? "SQL_CALC_FOUND_ROWS" 
	    	: ""
	    ) . " 
		vgallery_nodes.ID 												AS ID 
		, vgallery_nodes.name 											AS name 
		, vgallery_nodes.`parent`										AS `parent`
		, vgallery_nodes.`order` 										AS `order` 
		, vgallery_nodes.ID_type 										AS ID_type 
		, vgallery_nodes.is_dir 										AS is_dir 
		, vgallery_nodes.`created`										AS `created`
		, vgallery_nodes.`last_update`									AS `last_update`
		, vgallery_nodes.`published_at`									AS `published_at`
		, vgallery_nodes.class 											AS class 
		, vgallery_nodes.`highlight`									AS `highlight_container`
		, vgallery_nodes.`highlight_ID_image`							AS `highlight_image`
		, vgallery_nodes.`highlight_ID_image_md`						AS `highlight_image_md`
		, vgallery_nodes.`highlight_ID_image_sm`						AS `highlight_image_sm`
		, vgallery_nodes.`highlight_ID_image_xs`						AS `highlight_image_xs`
		, vgallery_nodes.owner 											AS owner
		, vgallery_nodes.tags 											AS tags
        , vgallery_type.name 											AS type
        , 0 															AS ID_cart_detail
		" . (OLD_VGALLERY
			? "
				, vgallery_nodes.visible 								AS `visible`			    
			"
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? "
					, vgallery_nodes.permalink							AS permalink
					, vgallery_nodes.keywords							AS keywords
					, vgallery_nodes.meta_description					AS meta_description
					, vgallery_nodes.meta_title							AS meta_title
					, vgallery_nodes.meta_title_alt						AS meta_title_alt
					, vgallery_nodes.`parent`							AS permalink_parent
					, vgallery_nodes.name								AS smart_url
					, vgallery_nodes.visible 							AS `visible`			    

				"
				: "
					, vgallery_nodes_rel_languages.permalink			AS permalink
					, vgallery_nodes_rel_languages.keywords				AS keywords
					, vgallery_nodes_rel_languages.meta_description		AS meta_description
					, vgallery_nodes_rel_languages.meta_title			AS meta_title
					, vgallery_nodes_rel_languages.meta_title_alt		AS meta_title_alt
					, vgallery_nodes_rel_languages.permalink_parent		AS permalink_parent
					, vgallery_nodes_rel_languages.smart_url			AS smart_url
					, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
						? " vgallery_nodes_rel_languages.visible "
						: " vgallery_nodes.visible "
					) . "												AS `visible`
				"
			)
		) . "
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] 
        	? "
                , ecommerce_pricelist.ID AS ID_pricelist
                , IF(ecommerce_settings.`type` = 'bytime'
                    , ecommerce_pricelist.date_since
                    , ecommerce_pricelist.qta_since
                ) AS pricelist_since
                , IF(ecommerce_settings.`type` = 'bytime'
                    , ecommerce_pricelist.date_to
                    , ecommerce_pricelist.qta_to
                ) AS pricelist_to
            " 
            : "
                , 0 AS ID_pricelist
                , 0 AS pricelist_since
                , 0 AS pricelist_to
            "
	    ) . "
		" . (is_array($query["select"]) ? ", " . implode(", ", $query["select"]) : ""
	    ) . "
        , (" . (!($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE) && USE_CART_PUBLIC_MONO 
        	? "(SELECT ecommerce_order_detail.ID
                FROM ecommerce_order_detail
                    INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
                WHERE ecommerce_order_detail.ID_items = vgallery_nodes.ID
                    AND ecommerce_order_detail.tbl_src = 'vgallery_nodes'
                    AND ecommerce_order.ID_user_cart = " . $db->toSql(get_session("UserNID"), "Number") . "
                    AND ecommerce_order.cart_name = " . $db->toSql(ffCommon_url_rewrite(get_session("UserID"))) . " AND ecommerce_order.wishlist_archived = 0 
                    AND ecommerce_order.is_cart > 0
            )" 
            : "''"
	    ) . ") 															AS is_wishlisted
    FROM vgallery_nodes
        INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_nodes.ID_type
		" . (OLD_VGALLERY
			? ""
			: (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				? ""
				: " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
						AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			)
		) . "        
        " . (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["vgallery_name"]) && count($vg_father["limit"]["vgallery_name"]) 
            ? " INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery AND vgallery.name IN('" . implode("','", $vg_father["limit"]["vgallery_name"]) . "') " 
            : ""
		) . "
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && ($vg_father["use_pricelist_as_item"] || AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK) 
            ? " INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID 
                " . ($vg_father["use_pricelist_as_item"] 
                	? " INNER JOIN ecommerce_pricelist ON ecommerce_pricelist.ID_ecommerce_settings = ecommerce_settings.ID
	                    " . (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
                        	? " AND ecommerce_pricelist.actual_qta > 0 " 
                        	: ""
			    		) 
			    	: (AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
			    		? " AND ecommerce_settings.actual_qta > 0 " 
			    		: ""
			    	)
		    	) 
		    : ""
	    ) . "
		" . (is_array($query["from"]) ? implode(" ", $query["from"]) : "") . "
    WHERE 1
        " . ($vg_father["enable_multi_cat"] || $settings["HIDE_DIR"] ? " AND vgallery_nodes.is_dir = 0 " : "") . "
        " . ($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE && $vg_father["use_pricelist_as_item"] 
        	? " AND (
                (
                    ecommerce_settings.`type` = 'bytime'
                    AND ecommerce_pricelist.date_since > 0
                    AND ecommerce_pricelist.date_to > 0
                    AND ecommerce_pricelist.qta_since = 0
                    AND ecommerce_pricelist.qta_to = 0
                    AND FROM_UNIXTIME(ecommerce_pricelist.date_since, '%Y') = YEAR(CURDATE())
                    AND FROM_UNIXTIME(ecommerce_pricelist.date_to, '%Y') = YEAR(CURDATE())
                )
                OR 
                (
                    ecommerce_settings.`type` = 'byqta'
                    AND ecommerce_pricelist.date_since = 0
                    AND ecommerce_pricelist.date_to = 0
                    AND ecommerce_pricelist.qta_since > 0 
                    AND ecommerce_pricelist.qta_to > 0
                )
            )
            " : ""
	    ) 
        . /*(is_array($vg_father["limit"]) && count($vg_father["limit"]) 
        	? ($vg_father["vgallery_name"]
	    		? " AND CONCAT(vgallery_nodes.parent, vgallery_nodes.name) <> " . $db->toSql("/" . $vg_father["vgallery_name"])
	    		: ""
			)
    		: */($vg_father["enable_multi_cat"] && $vg_father["settings_type"] != "search"
	    		? ((!isset($settings["SHOW_ALL_ITEM"]) || $settings["SHOW_ALL_ITEM"]) && $vg_father["user_path"] == "/" . $vg_father["vgallery_name"]
    				? " AND (vgallery_nodes.parent LIKE '" . $db->toSql($vg_father["user_path"], "Text", false) . "/%' OR vgallery_nodes.parent = " . $db->toSql($vg_father["user_path"]) . ")" 
	    			: " AND FIND_IN_SET(" . $vg_father["ID_node"] . ", vgallery_nodes.cats)"
	    		)
	    		: (OLD_VGALLERY
					? (!isset($settings["SHOW_ALL_ITEM"]) || $settings["SHOW_ALL_ITEM"]
    						? ($vg_father["user_path"] == "/"
    							? " AND vgallery_nodes.parent LIKE '/%'" 
    							: " AND (vgallery_nodes.parent LIKE '" . $db->toSql($vg_father["user_path"], "Text", false) . "/%' OR vgallery_nodes.parent = " . $db->toSql($vg_father["user_path"]) . ")" 
    						)
    						: " AND vgallery_nodes.parent = " . $db->toSql($vg_father["user_path"], "Text")
						)
					: (!isset($settings["SHOW_ALL_ITEM"]) || $settings["SHOW_ALL_ITEM"]
						? ($vg_father["user_path"] != $vg_father["permalink"]
							? ($vg_father["user_path"] == "/"
    							? " AND vgallery_nodes.parent LIKE '/%'" 
    							: " AND (vgallery_nodes.parent LIKE '" . $db->toSql($vg_father["user_path"], "Text", false) . "/%' OR vgallery_nodes.parent = " . $db->toSql($vg_father["user_path"]) . ")" 
    						)
    						: ($vg_father["permalink"]
								? (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
									? ($vg_father["permalink"] == "/"
    									? " AND vgallery_nodes.permalink LIKE '/%'" 
    									: " AND (vgallery_nodes.permalink LIKE '" . $db->toSql($vg_father["permalink"], "Text", false) . "/%' OR vgallery_nodes.permalink = " . $db->toSql($vg_father["permalink"]) . ")" 
    								)
									: ($vg_father["permalink"] == "/"
    									? " AND vgallery_nodes_rel_languages.permalink LIKE '/%'" 
    									: " AND (vgallery_nodes_rel_languages.permalink LIKE '" . $db->toSql($vg_father["permalink"], "Text", false) . "/%' OR vgallery_nodes_rel_languages.permalink = " . $db->toSql($vg_father["permalink"]) . ")" 
    								)					
								)
								: ""
							) 
						)
						: " AND vgallery_nodes.parent = " . $db->toSql($vg_father["user_path"])
					)
					/*
					. ($vg_father["permalink"]
						? (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
							? (!isset($settings["SHOW_ALL_ITEM"]) || $settings["SHOW_ALL_ITEM"]
								? ($vg_father["permalink"] == "/"
    								? " AND vgallery_nodes.permalink LIKE '/%'" 
    								: " AND (vgallery_nodes.permalink LIKE '" . $db->toSql($vg_father["permalink"], "Text", false) . "/%' OR vgallery_nodes.permalink = " . $db->toSql($vg_father["permalink"]) . ")" 
    							)
    							: " AND vgallery_nodes.permalink = " . $db->toSql($vg_father["permalink"])
								)
							: (!isset($settings["SHOW_ALL_ITEM"]) || $settings["SHOW_ALL_ITEM"]
									? ($vg_father["permalink"] == "/"
    									? " AND vgallery_nodes_rel_languages.permalink LIKE '/%'" 
    									: " AND (vgallery_nodes_rel_languages.permalink LIKE '" . $db->toSql($vg_father["permalink"], "Text", false) . "/%' OR vgallery_nodes_rel_languages.permalink = " . $db->toSql($vg_father["permalink"]) . ")" 
    								)					
    								: " AND vgallery_nodes_rel_languages.permalink = " . $db->toSql($vg_father["permalink"])
								)
						)
						: ""
					)*/
				)
			)
	   // ) 
		. (ENABLE_STD_PERMISSION 
			? ""
			: (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION && !OLD_VGALLERY
				? " AND vgallery_nodes_rel_languages.visible > 0 " 
				: " AND vgallery_nodes.visible > 0 "
			)
		) 
		. (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID 
			? " AND vgallery_nodes_rel_languages.permalink != ''" 
			: " AND vgallery_nodes.permalink != ''"
		)		
		. ($vg_father["ID_vgallery"] > 0 
        	? " AND vgallery_nodes.ID_vgallery = " . $db->toSql($vg_father["ID_vgallery"], "Number") 
        	: ""
	    ) 
	    . (is_array($vg_father["limit"]) && is_array($vg_father["limit"]["nodes"]) && count($vg_father["limit"]["nodes"]) 
        	? " AND vgallery_nodes.ID IN(" . $db->toSql(implode(", ", $vg_father["limit"]["nodes"]), "Text", false) . ") " 
        	: ""
	    )
	    . (is_array($query["where"]) 
			? " AND " . implode(" AND ", $query["where"]) 
			: ""
	    ) . "
    ORDER BY "
		. (is_array($query["order"]) 
		    ? implode(", ", $query["order"])
		    : ""
	    )
	    . ($vg_father["enable_found_rows"] ? " LIMIT " . (($vg_father["navigation"]["page"] - 1) * ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) ) . ", " .
		    ($vg_father["limit"]["elem"] > 0 ? $vg_father["limit"]["elem"] : $vg_father["navigation"]["rec_per_page"]
		    ) : ($vg_father["limit"]["elem"] > 0 ? " LIMIT " . $vg_father["limit"]["elem"] : ""
		    )
	    );

    return $sSQL_nodes;
}

function pre_process_vgallery_tpl_fields(&$tpl, $vg_father, $vg_field, $vg_data_value, $layout_settings) 
{
    $cover  = array();
    $vgallery_item_target = "";
    $vgallery_item_show_file = "";

    if ((is_array($vg_field["resources"]) && count($vg_field["resources"])) || $vg_father["is_custom_template"]) {
		if ($layout_settings["AREA_VGALLERY_ENABLE_NOIMG_PATH"] && is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_NOIMG_PATH"], "/"))) {
			$noimg["showfiles"] = "/" . FRONTEND_THEME . "/images";
			$noimg["base_path"] = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images";
			$mime = ffMimeType(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_NOIMG_PATH"], "/"));
			switch($mime) {
			    case "image/svg+xml":
			    	$noimg["mode"] = true;
			    	$noimg["path"] = "/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_NOIMG_PATH"], "/");
			    	$noimg["src"] = CM_SHOWFILES . $noimg["showfiles"] . $noimg["path"];
			    	break;
				case "image/jpeg":
				case "image/png":
				case "image/gif":
				    $noimg["mode"] = false;
					$noimg["path"] = "/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_NOIMG_PATH"], "/"); 
					break;
				default:
			}
			if($noimg["path"]) {
				$noimg["src"] = get_thumb(
					$noimg["path"] 
					, array(
						"base_path" => $noimg["base_path"]
						, "showfiles_path" => $noimg["showfiles"]
						, "thumb" => ($noimg["mode"]
							? null
							: $vg_father["properties"]["image"]["src"]
						)
						, "preserve_orig" => true
						, "placehold" => true
					)
				);	
			}
		}    

		if($layout_settings["AREA_VGALLERY_ENABLE_NOIMG"]) {
        	$noimg["placehold"] = true;
            //$noimg["mode"] = true;
            //if($vg_father["properties"]["image"]["src"]["default"]["width"] > 0 && $vg_father["properties"]["image"]["src"]["default"]["height"] > 0)
            //	$noimg["path"] = get_thumb_by_placehold($vg_father["properties"]["image"]["src"]["default"]["width"], $vg_father["properties"]["image"]["src"]["default"]["height"]);
        }
        
		if ($layout_settings["AREA_VGALLERY_ENABLE_COVER"]) {
			$placehold = false;
    		if(count($vg_field["resources"]) == 1)
    			$unique_resource = true;

		    if (strlen($layout_settings["AREA_VGALLERY_ENABLE_COVER_NAME"])) {
				if (is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_COVER_NAME"], "/"))) {
					$placehold = true;

					$cover["showfiles"] = "/" . FRONTEND_THEME . "/images";
					$cover["base_path"] = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images";
					$mime = ffMimeType(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_COVER_NAME"], "/"));
					switch($mime) {
			    		case "image/svg+xml":
			    			$cover["mode"] = true;
			    			$cover["path"] = "/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_COVER_NAME"], "/");
			    			break;
						case "image/jpeg":
						case "image/png":
						case "image/gif":
				    		$cover["mode"] = false;
							$cover["path"] = "/" . ltrim($layout_settings["AREA_VGALLERY_ENABLE_COVER_NAME"], "/"); 
							break;
						default:
					}
			    }
		    } else {
				$cover["showfiles"] = "";
				$cover["base_path"] = DISK_UPDIR;
		    	$full_path = stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"];
				if(is_dir(DISK_UPDIR . $full_path)) { 
					$directory = new RecursiveDirectoryIterator(DISK_UPDIR . $full_path);
					$flattened = new RecursiveIteratorIterator($directory);

					$files = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:jpg|png|gif|svg)$#Di');
					$files = array_keys(iterator_to_array($files));
					sort($files);

					foreach($files as $file) {
						if(ffCommon_dirname($file) == DISK_UPDIR . $full_path /*&& end($files) !== $file*/)
							continue;

						$mime = ffMimeType($file);
						$file_path = str_replace(DISK_UPDIR, "", $file);
						switch($mime) {
			    			case "image/svg+xml": 
			    				$cover["mode"] = true; 
			    				$cover["path"] = $file_path;
			    				break;
							case "image/jpeg":
							case "image/png":
							case "image/gif":
				    			$cover["mode"] = false;
								$cover["path"] = $file_path;
								break;
							default:
						}

						if($cover["path"])
							break;
					}		 
				}
		    }

		    if($cover["path"]) {
				$cover["src"] = get_thumb(
					$cover["path"]
					, array(
						"base_path" => $cover["base_path"]
						, "showfiles_path" => $cover["showfiles"]
						, "thumb" => ($cover["mode"]
							? null
							: $vg_father["properties"]["image"]["src"]
						)
						, "preserve_orig" => true
						, "placehold" => $placehold
					)
				);	
		    } else {
		    	$cover = $noimg;
			}
		    //if(!$cover["path"]) //todo: nn so perche e stato messo. Questo rompe tutto alla riga 6661 con le cover 
		    //	$cover["path"] = true;
		}
    }
    //link
    if ($vg_father["src"]["permalink_only"]) {
		$vgallery_item_path = FF_SITE_PATH . ($vg_father["src"]["permalink_only"] || strpos($vg_data_value["permalink_parent"], "/" . $vg_father["vgallery_name"]) === 0 
			? "" 
			: stripslash("/" . $vg_father["vgallery_name"])
		) . stripslash($vg_data_value["permalink_parent"]);
    } else {
		if ($vg_father["source_user_path"] === NULL) { //i permalink devono essere con i percorsi completi
		    $vgallery_item_path = FF_SITE_PATH . (strpos(stripslash($vg_data_value["permalink_parent"]), "/" . $vg_father["vgallery_name"]) !== false 
		    	? stripslash($vg_data_value["permalink_parent"]) 
		    	: stripslash("/" . $vg_father["vgallery_name"]) . stripslash($vg_data_value["permalink_parent"])
			);
		} elseif (stripslash($vg_father["source_user_path"])) {
		    $vgallery_item_path = FF_SITE_PATH . (strpos(stripslash($vg_data_value["permalink_parent"]), stripslash($vg_father["source_user_path"])) === 0 
		    	? stripslash($vg_data_value["permalink_parent"]) 
		    	: stripslash($vg_father["source_user_path"]) . stripslash($vg_data_value["permalink_parent"])
			);
		} else {
		    $vgallery_item_path = stripslash($vg_data_value["permalink_parent"]);
		}
    }
	///TEST DEL PERMALINK
	$vgallery_item_path = normalize_url_by_current_lang(stripslash($vg_data_value["permalink_parent"]), true);

    if (strlen($vg_data_value["alt_url"])) {
		if (
			substr($vg_data_value["alt_url"], 0, 1) != "/"
		) {
		    $vgallery_item_show_file = $vg_data_value["alt_url"];
		    if(substr($vg_data_value["alt_url"], 0, 1) != "#")
			$vgallery_item_target = "_blank";
		    /*if (
			    substr(strtolower($vg_data_value["alt_url"]), 0, 7) == "http://" || substr(strtolower($vg_data_value["alt_url"]), 0, 8) == "https://" || substr($vg_data_value["alt_url"], 0, 2) == "//"
		    ) {
			$vgallery_item_show_file = $vg_data_value["alt_url"];
			$vgallery_item_target = "_blank";
		    }*/
		} else {
		    if (strpos($vg_data_value["alt_url"], "#") !== false) {
				$part_alternative_hash = substr($vg_data_value["alt_url"], strpos($vg_data_value["alt_url"], "#"));
				$alternative_path = substr($vg_data_value["alt_url"], 0, strpos($vg_data_value["alt_url"], "#"));
		    }

		    if (strpos($vg_data_value["alt_url"], "?") !== false) {
				$part_alternative_path = substr($vg_data_value["alt_url"], 0, strpos($vg_data_value["alt_url"], "?"));
				$part_alternative_url = substr($vg_data_value["alt_url"], strpos($vg_data_value["alt_url"], "?")); //. ($vg_father["search"]["param"] ? "&ret_url=" . urlencode($ret_url) : "");
		    } else {
				$part_alternative_path = $vg_data_value["alt_url"];
				$part_alternative_url = ""; //($vg_father["search"]["param"] ? "?ret_url=" . urlencode($ret_url) : "");
		    }
		    if (check_function("get_international_settings_path")) {
				$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
				$vgallery_item_show_file = normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash;
		    }

		    if (!$vgallery_item_show_file)
				$vgallery_item_show_file = $vg_data_value["alt_url"];
		}

		$vgallery_item_permalink = $vgallery_item_show_file;
    } elseif($vg_data_value["preload"][$vg_father["src"]["table"]]["visible"]) {
		if ($vg_father["source_user_path"] === NULL) {
		    if ($vg_father["type"] != "learnmore" && $vg_father["publishing"] === NULL && $vg_father["search"] === NULL) {
			$vgallery_item_show_file = $vgallery_item_path . "/" . $vg_data_value["smart_url"] . $vg_field[$vg_data_value["ID_type"]]["vgallery_group"]
				. ($vg_father["search"] !== NULL 
					? ""
					: ($vg_father["wishlist"] !== NULL 
						? "?ref=" . $vg_father["wishlist"]["ID"] . "&detail=" . $vg_data_value["ID_cart_detail"] . "&datc=" . $vg_father["wishlist"]["disable_addtocart"]
						: ""
					)
				);
		    } else {
			$vgallery_item_show_file = $vgallery_item_path . "/" . $vg_data_value["smart_url"] . $vg_field[$vg_data_value["ID_type"]]["vgallery_group"]
				. ($vg_father["search"]["encoded_params"] 
					? "?" . $vg_father["search"]["encoded_params"] 
					: ($vg_father["wishlist"] !== NULL 
						? "?ref=" . $vg_father["wishlist"]["ID"] . "&detail=" . $vg_data_value["ID_cart_detail"] . "&datc=" . $vg_father["wishlist"]["disable_addtocart"]
						: ""
					)
				);
		    }
		} else {
		    //parte nuova da debuggare 
		    $vgallery_item_show_file = stripslash($vgallery_item_path) . "/" . $vg_data_value["smart_url"] . $vg_field[$vg_data_value["ID_type"]]["vgallery_group"]
			    . ($vg_father["search"]["encoded_params"] 
		    		? "?" . $vg_father["search"]["encoded_params"] 
				    : ($vg_father["wishlist"] !== NULL 
			    		? "?ref=" . $vg_father["wishlist"]["ID"] . "&detail=" . $vg_data_value["ID_cart_detail"] . "&datc=" . $vg_father["wishlist"]["disable_addtocart"] 
					    : ""
				    )
			    );
		}

		$vgallery_item_permalink = stripslash($vg_data_value["permalink_parent"]) . "/" . $vg_data_value["smart_url"];
    } else {
    	$vgallery_item_show_file = "javascript:void(0);";
    	$vgallery_item_permalink = false;
	}    
    //link

    $htmltag = process_vgallery_special($vg_father["src"]["table"], "scope");

    if ($vg_father["is_custom_template"] && is_array($tpl->DVars)) 
 	{
		foreach($tpl->DVars AS $tpl_var => $tpl_ignore) {
			if($vg_data_value[$tpl_var])
			$tpl->set_var($tpl_var, $vg_data_value[$tpl_var]);	
		}
	}
//da aggiungere published created last update e url completo
    return array(
    	"permalink" 						=> $vgallery_item_permalink
		, "smart_url" 						=> $vg_data_value["smart_url"]
    	, "title" 							=> $vg_data_value["title"]
    	, "description" 					=> $vg_data_value["description"]
    	, "header_title" 					=> $vg_data_value["header_title"]
    	, "keywords" 						=> $vg_data_value["keywords"]
    	, "tags" 							=> $vg_data_value["tags"]
		, "parent" 							=> $vg_data_value["permalink_parent"]
		, "parent_title" 					=> str_replace("-", " ", basename($vg_data_value["permalink_parent"]))
		, "parent_smart_url" 				=> basename($vg_data_value["permalink_parent"])
		, "url" 							=> $vgallery_item_show_file
		, "target" 							=> $vgallery_item_target
		, "cover" 							=> $cover
		, "noimg" 							=> $noimg
		, "placeholder" 					=> $layout_settings["AREA_VGALLERY_ENABLE_NOIMG"]
		, "htmltag" 						=> $htmltag
		, "created"							=> $vg_data_value["created"]
		, "lastupdate"						=> $vg_data_value["last_update"]
		, "published"						=> ($vg_data_value["published"]
												? $vg_data_value["published"]
												: $vg_data_value["created"]
											)
		, "owner"							=> $vg_data_value["owner"]
		, "type"							=> $vg_father["type"]
    );
}

function process_vgallery_tpl_fields(&$tpl, &$vg_father, $vg_fields, $vg_data_value, $layout, $params = array()) {
    static $reset_field = array();

    $cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
    $user = get_session("user_permission");

    $count["img"] = 0;
    $count["desc"] = 0;
    $vg_field_sort = array();
    $arrJsRequest = array();

    $layout_settings = $layout["settings"];

    //$tpl->set_var("SezVGalleryImageNode", ""); //essendo globale per ogni item non e possibile pulirlo qui poiche i gruppi lo annullerebbero
    $tpl->set_var("SezVGalleryDescriptionNode", ""); //e fondamentale pulirlo qui
    if ($params["reset"] && count($reset_field)) {
		foreach ($reset_field AS $reset_field_key => $reset_field_value) {
		    $tpl->set_var($reset_field_key, "");
		}
    }
    //print_r($vg_fields);
    //print_r($vg_data_value);
    if (is_array($vg_fields) && count($vg_fields)) {
		if (!isset($params["meta"])) {
		    $params["meta"]["title"] = $vg_data_value["meta"]["title"];
		    $params["meta"]["description"] = $vg_data_value["meta"]["description"];
		}

 		$params["highlight"] = $vg_data_value["highlight"];
 		
		foreach ($vg_fields AS $field_key => $field_value) {
		    $tmp_data_field = array();

		    $limit_by_groups_frontend = $field_value["limit_by_groups_frontend"];
		    if (strlen($limit_by_groups_frontend)) {
			$limit_by_groups_frontend = explode(",", $limit_by_groups_frontend);

			if (!count(array_intersect($user["groups"], $limit_by_groups_frontend))) {
			    continue;
			}
		    }

		    if ($params["enable_sort"]) {
			$vg_field_sort["desc"][$field_key]["name"] = $field_value["name"];
			$vg_field_sort["desc"][$field_key]["type"] = $field_value["type"];
			$vg_field_sort["desc"][$field_key]["enable"] = $field_value["enable_sort"];
		    }
		    //$tpl->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"] . $vg_data_value["name"] . $field_value["name"]) . "_description");
		    //$tpl->set_var("class_name", preg_replace('/[^a-zA-Z0-9]/', '', $field_value["type"] . $field_value["name"]));



		    /*
		      if(isset($_REQUEST["__nocache__"])) {
		      $more_detail_check_nodes = false;
		      } else {
		      $more_detail_check_nodes = $vg_data_value["check_nodes"][$field_key];
		      } */

			$more_detail = $vg_data_value["data"][$field_key];

			$cm->doEvent("vg_on_vgallery_field_before_parse", array(&$more_detail, $field_value, $vg_father));

		    switch ($field_value["data_type"]) {
			case "applet":
			    if (check_function("process_forms_framework"))
				$more_detail = process_forms_framework($field_value["data_source"], $field_value["data_limit"], stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"]);

			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "relationship":
			    $more_detail_limit = $vg_data_value["limit"][$field_key];
			    $more_detail_js_request = $vg_data_value["js_request"][$field_key];
			    $more_detail_key = $vg_data_value["ID_data_node"][$field_key];
			    $more_detail_content = "";
	                   // echo "entro "  . $field_key . "<br>";
						
						
			    if (is_array($more_detail)) { 
		    		//if(is_array($more_detail["src"])) {
		    			$learnmore_layout 									= array();
		    			$more_detail["src"] 								= $vg_father["src"];
					//die("ASDASASDDASDASSDAASDASD");
						$more_detail["src_layout"] 							= $layout;
						$more_detail["src_field_name"] 						= $field_value["name"];
						$more_detail["src_mode"] 							= $vg_father["mode"];
						if (check_function("process_vgallery_thumb")) {
						    $buffer = process_vgallery_thumb(
							    stripslash($more_detail["parent"])
							    , "learnmore"
							    , array(
									"source_user_path" 						=> (strlen($more_detail["source_user_path"]) 
															                    ? $more_detail["source_user_path"] 
															                    : null
																			)
					                , "vgallery_name" 						=> $more_detail["vgallery_name"]
									, "ID_layout" 							=> $vg_father["ID_layout"]
									, "learnmore" 							=> $more_detail
									, "template_name" 						=> ($vg_father["type"] == "publishing"
																				? $vg_father["vgallery_class"]
																				: null
																			)
									, "template_default_name" 				=> ($field_value["extended_type"] == "MonoRelation"
																				? "empty"
																				: null
																			)
									, "allow_insert" 						=> false
									, "search" 								=> $globals->search
                                    , "navigation" 							=> $globals->navigation
                                    , "settings" 							=> array("/" => $layout_settings)
									, "output" 								=> true
							    )
							    , $learnmore_layout
						    );

						    $more_detail_content = $buffer["content"];
						    if (is_array($buffer["js_request"]) && count($buffer["js_request"])) {
								foreach ($buffer["js_request"] AS $arrMoreDetailJsRequest_value) {
								    if (strlen($arrMoreDetailJsRequest_value)) {
										//Load JS Plugin
										$arrJsRequest[$arrMoreDetailJsRequest_value] = true;
								    }
								}
						    }
						}
					//}
			    }

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail_content;
			    break;
			case "media":
			    $rst_rel_dir = array();
			    $real_path = stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"] . "/" . ffCommon_url_rewrite($field_value["name"]);
				if(strpos(CM_SHOWFILES, "://") !== false) {
					$arrMedia = explode(",", $more_detail);
					if (is_array($arrMedia) && count($arrMedia) && check_function("get_file_permission")) {
						foreach ($arrMedia as $file) {
							$rst_rel_dir[$file]["permission"][LANGUAGE_INSET] = true;
						}
					}
				} else {
					//da migliorare il process dei thumb eliminando le query e i permessi multipli
					$abs_real_path = realpath(DISK_UPDIR . $real_path);

					if (strlen($abs_real_path) && is_dir($abs_real_path))
						$arrMedia = glob($abs_real_path . "/*");

					if (is_array($arrMedia) && count($arrMedia) && check_function("get_file_permission")) {
						foreach ($arrMedia as $real_file) {
							$file = str_replace(DISK_UPDIR, "", $real_file);
							if (
								(
									(is_dir($real_file) && basename($real_file) != CM_SHOWFILES_THUMB_PATH)
									|| (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false)
								)
								&& strpos(basename($real_file), ".") !== 0
							) {
								if (ENABLE_STD_PERMISSION)
									$file_permission = get_file_permission($file);
								if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
									$rst_rel_dir[$file]["permission"] = $file_permission;
								}
							}
						}
					}
				}

			    $rel_layout["prefix"] = $layout["prefix"] . "RD";
			    $rel_layout["ID"] = $layout["ID"];
			    $rel_layout["title"] = $layout["title"] . " dir";
			    $rel_layout["type"] = "GALLERY";
			    $rel_layout["location"] = $layout["location"];
			    $rel_layout["visible"] = $layout["visible"];
			    $rel_layout["is_rel"] = true;
			    $rel_layout["settings"] = $layout["settings"];

			    if (check_function("process_gallery_thumb")) {
				//da fre debug nn esce la galleria
				$tmp_buffer = process_gallery_thumb($rst_rel_dir, $real_path, NULL, NULL, NULL, $rel_layout, null, ($vg_father["permission"]["owner"] > 0 ? ($vg_father["permission"]["owner"] == $user["ID"] ? true : false) : null));
				if (strlen($tmp_buffer["content"])) {
				    //$more_detail = '<div class="block gal">' . $tmp_buffer["content"] . '</div>';
				    $more_detail = $tmp_buffer["content"];
				}
			    }

			    if (!$more_detail && $params["enable_error"]) {
				    $more_detail = '<span class="' . preg_replace('/[^a-zA-Z0-9]/', '', $field_value["type"] . $field_value["name"]) . '">' . ffTemplate::_get_word_by_code($vg_father["vgallery_name"] . "_" . $field_value["name"] . "_notfound") . '</span>';
			    }

				    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "static":
			    if (check_function("process_addon_static"))
				$more_detail = process_addon_static($params["url"], $vg_data_value["ID"], $vg_father["vgallery_name"], $field_value["data_source"], $layout);
			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "ecommerce.price":
			    //if(!$field_permission)
			    //    continue;
			    if (check_function("ecommerce_cart_pricing"))
					$more_detail = ecommerce_cart_pricing($layout, stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"], "vgallery_nodes", $vg_father["mode"], $vg_father["enable_ecommerce"], $vg_data_value["pricelist"], $vg_father["user_path"], $vg_data_value["ID_data_node"], $vg_father["wishlist"]["ID"], $vg_data_value["ID_cart_detail"], $vg_father["wishlist"]["disable_addtocart"], $vg_data_value["is_wishlisted"]);

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "google.docs":
			    if (check_function("process_addon_google_docs"))
				$more_detail = process_addon_google_docs($vg_father["user_path"], $field_value["data_source"], $more_detail, $layout);

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "google.maps":
			    if (check_function("process_addon_google_maps"))
				$more_detail = process_addon_google_maps($vg_father, $vg_data_value["ID"], $layout);

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "ecommerce.variant":
			    if (check_function("process_addon_variant"))
				$more_detail = process_addon_variant(stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"], $vg_data_value["ID"], $vg_father["vgallery_name"], $vg_father["enable_multilang_visible"], ffCommon_dirname($params["url"]), $layout);

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "sender":
			    if (check_function("process_addon_sender"))
				$more_detail = process_addon_sender(stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"], $vg_data_value["ID"], $vg_father["vgallery_name"], $vg_data_value["meta_title"], $field_value["data_source"], $field_value["data_limit"], $layout);

			    /* Set Field Content */
			    $tmp_data_field["content"] = $more_detail;
			    break;
			case "comment":
			    if ($params["tpl"]["is_html"]) {
				$layout_comment["prefix"] = $layout["prefix"] . $layout["ID"] . "K" . $vg_data_value["ID"];
				$layout_comment["ID"] = 0;
				$layout_comment["title"] = $layout["title"] . " comment";
				$layout_comment["type"] = "COMMENT";
				$layout_comment["location"] = $layout["location"];
				$layout_comment["visible"] = NULL;
				$layout_comment["settings"] = $layout_settings;

				$frame["sys"]["module"]["COMMENT"] = array(
				    "ID_vgallery_node" => $vg_data_value["ID"]
				    , "ID_module" => $field_value["data_source"]
				    , "uid" => null
				    , "user_path" => $vg_father["settings_path"]
				    //, "ret_url" => $ret_url
				    , "tbl_src" => "vgallery"
				    , "disable_control" => true
				    , "layout" => $layout_comment
				);
				$frame["sys"]["location"] = $layout["location"];
				//$frame["sys"]["ret_url"] = $ret_url;
				$serial_frame = json_encode($frame);
				$serial_frame_url = FF_SITE_PATH . VG_SITE_FRAME . stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"] . "?sid=" . set_sid($serial_frame);

				$cm->oPage->tplAddJs("ff.ajax", "ajax.js", FF_THEME_DIR . "/library/ff");

				//$more_detail = "";
				//$field_value["enable_empty"] = true; 

				/* Set Field Content */
				$tmp_data_field["content"] = "<input class=\"ajaxcontent\" type=\"hidden\" data-ename=\"" . preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["vgallery_name"] . "Form") . "\" value=\"" . $serial_frame_url . "\" />";
			    }
			    break;
			case "form":
			    if ($params["tpl"]["is_html"]) {
				$layout_form["prefix"] = "MD-" . $layout["location"] . "-form-";
				$layout_form["ID"] = $more_detail;
				$layout_form["title"] = $layout["title"] . " form";
				$layout_form["type"] = "MODULE";
				$layout_form["location"] = $layout["location"];
				$layout_form["visible"] = NULL;
				$layout_form["settings"] = $layout_settings;

				$frame["sys"]["module"]["FORM"] = array(
				    "ID_vgallery_node" => $vg_father["ID_node"]
				    , "form_name" => $more_detail
				    , "form_params" => array(
					"title" => $vg_data_value["meta_title"]
					, "button_label" => ffTemplate::_get_word_by_code($vg_father["vgallery_name"] . "_insert")
				    )
				    , "layout" => $layout_form
				);

				$frame["sys"]["location"] = $layout["location"];
				//$frame["sys"]["ret_url"] = $ret_url;
				$serial_frame = json_encode($frame);
				$serial_frame_url = FF_SITE_PATH . VG_SITE_FRAME . stripslash($vg_father["user_path"]) . "?sid=" . set_sid($serial_frame);

				$cm->oPage->tplAddJs("ff.ajax", "ajax.js", FF_THEME_DIR . "/library/ff");

				//$more_detail = "";
				//$field_value["enable_empty"] = true;

				/* Set Field Content */
				$tmp_data_field["content"] = "<input class=\"ajaxcontent blockui\" type=\"hidden\" data-ename=\"" . preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["vgallery_name"] . "Form") . "\" value=\"" . $serial_frame_url . "\" />";
			    }
			    break;
			case "ecommerce.checkout":
			    if (strlen($more_detail)) {
				if (check_function("process_addon_checkout"))
				    $more_detail = process_addon_checkout(stripslash($vg_data_value["parent"]) . "/" . $vg_data_value["name"], $vg_data_value["meta_title"], $more_detail, $field_value["data_source"], $layout);

				/* Set Field Content */
				$tmp_data_field["content"] = $more_detail;
			    }
			    break;
			case "table.alt":
			    if (is_array($vg_data_value["preload"][$field_value["data_source"]])) {
				    if (strpos($field_value["data_limit"], ",") === false) {
				        if (is_array($vg_data_value["preload"][$field_value["data_source"]][$field_value["data_limit"]])) {
					        //specialista
					        $more_detail = process_tpl_by_schema(
						        $vg_data_value["preload"][$field_value["data_source"]][$field_value["data_limit"]]
						        , $tmp_data_field
						        , $field_value["name"]
						        , array(
					            "tbl" => $field_value["select"]["data_source"]
					            , "field" => $field_value["select"]["data_limit"]
						        )
						        , array(
					            "tbl" => $field_value["data_source"]
					            , "field" => $field_value["data_limit"]
						        )
						        , $vg_father["limit"]["tpl"]["shard"][$field_value["name"]]
					        );
				        } else {
					        $more_detail = $vg_data_value["preload"][$field_value["data_source"]][$field_value["data_limit"]];    
				        }

				        $tmp_data_field = process_vgallery_field_by_extended_type($vg_father, $more_detail, $field_value, $params, $tmp_data_field);
				    } else {
				        $arrDataSourceMain = array_filter(explode(",", $field_value["data_limit"]));
				        if (count($arrDataSourceMain)) {
					    foreach ($arrDataSourceMain AS $arrDataSourceMain_value) {
					        if (is_array($vg_data_value["preload"][$field_value["data_source"]][$arrDataSourceMain_value])) {
						    $more_detail[$arrDataSourceMain_value] = process_tpl_by_schema(
							    $vg_data_value["preload"][$field_value["data_source"]][$arrDataSourceMain_value]
							    , $tmp_data_field
							    , $field_value["name"]
							    , array(
						        "tbl" => $field_value["select"]["data_source"]
						        , "field" => $field_value["select"]["data_limit"]
							    )
							    , array(
						        "tbl" => $field_value["data_source"]
						        , "field" => $arrDataSourceMain_value
							    )
							    , $vg_father["limit"]["tpl"]["shard"][$field_value["name"]]
						    );
					        } else {
						    $more_detail[$arrDataSourceMain_value] = $vg_data_value["preload"][$field_value["data_source"]][$arrDataSourceMain_value];
					        }
					    }

					    $tmp_data_field = process_vgallery_field_by_extended_type($vg_father, $more_detail, $field_value, $params, $tmp_data_field);
				        }
				    }
			    }
			    break;
			case "selection":
			    if (is_array($more_detail)) {
				    $res_schema = process_vgallery_schema_table(
					    array(
					        "tbl" => $field_value["select"]["data_source"]
					        , "field" => $field_value["select"]["data_limit"]
					    )
				    );

				    foreach ($more_detail AS $shard_key => $shard_data) {
				        $tmp_data_field = process_shard_by_schema($shard_data
					        , $res_schema["tbl"]
					        , $tmp_data_field
					        , array(
							    "key" => $shard_key
							    , "container" => "div"
							    , "class" => $field_value["name"]
							    , "total" => count($more_detail)
							    , "shard_allowed" => $vg_father["limit"]["tpl"]["shard"][$field_value["name"]]
					        )
				        );
				    }
			    }

			    $tmp_data_field = process_vgallery_field_by_extended_type($vg_father, $more_detail, $field_value, $params, $tmp_data_field);
			    break;
			default:
			    if (is_array($more_detail)) {
				    $more_detail = process_tpl_by_schema(
					    $more_detail
					    , $tmp_data_field
					    , $field_value["name"]
					    , array(
				        "tbl" => $field_value["select"]["data_source"]
				        , "field" => $field_value["select"]["data_limit"]
					    )
					    , array(
				        "tbl" => $field_value["data_source"]
				        , "field" => $field_value["data_limit"]
					    )
					    , $vg_father["limit"]["tpl"]["shard"][$field_value["name"]]
				    );  
			    }
			    $tmp_data_field = process_vgallery_field_by_extended_type($vg_father, $more_detail, $field_value, $params, $tmp_data_field);

	//print_r($tmp_data_field);
			    //servono????
			    $more_detail_limit = $vg_data_value["limit"][$field_key];
			    $more_detail_js_request = $vg_data_value["js_request"][$field_key];
			    $more_detail_key = $vg_data_value["ID_data_node"][$field_key];

		    }
	//print_r($tmp_data_field);
		    //print_r($tmp_data_field);
		    if (is_array($tmp_data_field) && count($tmp_data_field)) {
			if (!isset($tmp_data_field["default"])) {
			    $tmp_data_field["default"] = $tmp_data_field["content"];
			    $tmp_data_field["htmltag"] = false;
			}

			if (!$params["tpl"]["is_html"]) {
			    if (isset($params["tpl"]["tag" . $vg_father["template"]["suffix"]]["vgallery_row_field"]) && strlen($params["tpl"]["tag" . $vg_father["template"]["suffix"]]["vgallery_row_field"])) {
				$field_value["htmltag_tag"] = $params["tpl"]["tag" . $vg_father["template"]["suffix"]]["vgallery_row_field"];
			    } else {
				$field_value["htmltag_tag"] = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($field_value["name"]));
			    }

			    $field_value["htmltag_attr"] = "";
			}
	//print_r($tmp_data_field);
			if ($vg_father["is_custom_template"]) {
			    if (!isset($tmp_data_field["href"]) && $tmp_data_field["show_file"]) {
				$tmp_data_field["target"] = $params["target"];
				$tmp_data_field["show_file"] = $params["url"];
				$tmp_data_field["link"] = '<a' . ($tmp_data_field["show_file"] ? ' href="' . $tmp_data_field["show_file"] . '"' : ''
					) . ($tmp_data_field["target"] ? ' target="' . $tmp_data_field["target"] . '"' : ''
					) . '>' . $tmp_data_field["default"] . '</a>';
			    }

			    $field_isset = false;
			    $field_key = $field_value["name"];
			    if ($vg_father["limit"]["tpl"]["fields"] && array_key_exists($field_key, $vg_father["limit"]["tpl"]["fields"])) {
					if (isset($tmp_data_field["shard"]) && is_array($vg_father["limit"]["tpl"]["shard"][$field_key]) && count($vg_father["limit"]["tpl"]["shard"][$field_key])) {
					    $field_sect_key = str_replace(" ", "", ucwords(str_replace("-", " ", $field_key)));

					    if (isset($tpl->DBlocks[$field_sect_key])) {
							$count_shard_field = 0;

							$tpl->set_var($field_sect_key, "");
							foreach ($tmp_data_field["shard"] AS $shard_data) {
							    foreach ($vg_father["limit"]["tpl"]["shard"][$field_key] AS $shard_key => $shard_properties) {
									if (is_array($shard_properties)) {
									    $arrShardFound = array_intersect_key($shard_data["fields"][$shard_key], $shard_properties);
									    if (is_array($arrShardFound) && count($arrShardFound)) {
											foreach ($arrShardFound AS $shard_properties_key => $shard_properties_value) {
											    $field_shard_attr = array();
											    $shard_prop_attr = $shard_properties_key . ($shard_properties[$shard_properties_key] ? ":" . $shard_properties[$shard_properties_key] : "");
											    if (is_array($shard_properties_value)) {
												if (strlen($shard_properties[$shard_properties_key])) {
												    $arrShardPropTag = explode(":", $shard_properties[$shard_properties_key]);
												    foreach ($arrShardPropTag AS $arrShardPropTag_value) {
													$field_shard_attr[$shard_prop_attr][] = $shard_properties_value[$arrShardPropTag_value];
												    }
												    $field_shard_attr[$shard_prop_attr] = implode(" ", $field_shard_attr[$shard_prop_attr]);
												} elseif (array_key_exists("default", $shard_properties_value)) {
												    $field_shard_attr[$shard_prop_attr] = $shard_properties_value["default"];
												} else {
												    if (isset($shard_data["struct"][$shard_key][$shard_prop_attr]))
													$field_shard_attr[$shard_prop_attr] = $shard_data["struct"][$shard_key][$shard_prop_attr];
												    else
													$field_shard_attr[$shard_prop_attr] = implode(" ", $shard_properties_value);
												}
											    } else
												$field_shard_attr[$shard_prop_attr] = $shard_properties_value;
											}

											$field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key . "." . $shard_key] . ":" . $shard_prop_attr;
											if ($tpl->isset_var($field_attr_key)) {
											    //echo "SA " . $field_attr_key . "   => " . implode(" ", $field_shard_attr) . "<br>\n";
											    $field_isset = $tpl->set_var($field_attr_key, implode(" ", $field_shard_attr));
                                                $reset_field[$field_attr_key] = true;
											}

											if (count($arrShardFound) > 1) {
											    $field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key . "." . $shard_key] . ":" . implode(":", array_keys($field_shard_attr));
											    if ($tpl->isset_var($field_attr_key)) {
													$field_isset = $tpl->set_var($field_attr_key, implode(" ", $field_shard_attr));
                                                    $reset_field[$field_attr_key] = true;
													//echo "SB " . $field_attr_key . "   => " . implode(" ", $field_shard_attr) . "<br>\n";
											    }
											}
									    }
									}

									$field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key . "." . $shard_key];
									if ($tpl->isset_var($field_attr_key)) {
									    //echo "SC " . $field_attr_key . "   => " . $arrShardFound[$shard_key]["content"] . "<br>\n";
									    $field_isset = $tpl->set_var($field_attr_key, $shard_data["fields"][$shard_key]["content"]);
                                        $reset_field[$field_attr_key] = true;
									}

									$field_attr_container = $field_sect_key . ucfirst($shard_key);
									if($tpl->isset_var($field_attr_container)) {
										if($shard_data["fields"][$shard_key]["content"]) 
											$tpl->parse($field_attr_container, false);
										else
											$tpl->set_var($field_attr_container, "");
									}

									if($shard_data["fields"][$shard_key]["content"])
										$count_shard_field++;
							    }

							    $tpl->parse($field_sect_key, true);
							}
							
							if ($count_shard_field && $tpl->isset_var(ucfirst($field_sect_key) . "Container"))
								$field_isset = $tpl->parse(ucfirst($field_sect_key) . "Container", false);
					    }
					}

					if (is_array($vg_father["limit"]["tpl"]["fields"][$field_key]) && count($vg_father["limit"]["tpl"]["fields"][$field_key])) {
					    foreach ($vg_father["limit"]["tpl"]["fields"][$field_key] AS $prop_name => $prop_tag) {
							$field_attr = array();
							$prop_attr = $prop_name . ($prop_tag ? ":" . $prop_tag : "");
							if (is_array($tmp_data_field[$prop_name])) {
							    if (strlen($prop_tag)) {
									$arrPropTag = explode(":", $prop_tag);
									foreach ($arrPropTag AS $arrPropTag_value) {
									    $field_attr[$prop_attr][] = $tmp_data_field[$prop_name][$arrPropTag_value];
									}
									$field_attr[$prop_attr] = implode(" ", $field_attr[$prop_attr]);
							    } elseif (array_key_exists("default", $tmp_data_field[$prop_name])) {
									$field_attr[$prop_attr] = $tmp_data_field[$prop_name]["default"];
							    } else {
									$field_attr[$prop_attr] = implode(" ", $tmp_data_field[$prop_name]);
							    }
							} elseif($prop_name == "properties") {
                                $field_attr[] = $tmp_data_field["htmltag"]["processed"];
                            } else {
							    $field_attr[$prop_attr] = $tmp_data_field[$prop_name];
							}

							$field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key] . ":" . $prop_attr;
							if ($tpl->isset_var($field_attr_key)) {
							    //echo "A " . $field_attr_key . "   => " . implode(" ", $field_attr) . "<br>\n";
							    $field_isset = $tpl->set_var($field_attr_key, implode(" ", $field_attr));
                                $reset_field[$field_attr_key] = true;
							}
					    }
					    if (count($vg_father["limit"]["tpl"]["fields"][$field_key]) > 1) {
							$field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key] . ":" . implode(":", array_keys($field_attr));
							if ($tpl->isset_var($field_attr_key)) {
							    //echo "B " . $field_attr_key . "   => " . implode(" ", $field_attr) . "<br>\n";
							    $field_isset = $tpl->set_var($field_attr_key, implode(" ", $field_attr)); 
                                $reset_field[$field_attr_key] = true;
							}
					    }
					}

					$field_attr_key = $vg_father["limit"]["tpl"]["vars"][$field_key];
					if ($tmp_data_field["content"] && isset($tpl->DBlocks[ucfirst($field_attr_key)])) {
						$field_isset = $tpl->parse(ucfirst($field_attr_key), false);
					} elseif ($tpl->isset_var($field_attr_key)) {
					    //echo "C " . $field_attr_key . "   => " . $tmp_data_field["default"] . "<br>\n";
					    $field_isset = $tpl->set_var($field_attr_key, $tmp_data_field["default"]);
                        $reset_field[$field_attr_key] = true;
					}

					if ($field_isset) {
					    $count[$field_value["group"]]++;
					}
			    }
			} else {
			    $tpl->set_var("content", $tmp_data_field["prefix"] . $tmp_data_field["default"] . $tmp_data_field["postfix"]);
			    $tpl->parse("SezVGallery" . ($field_value["group"] == "img" ? "Image" . $vg_father["template"]["field"]["location"] : "Description") . "Node", true);
			    $count[$field_value["group"]]++;
			}

			if ($tmp_data_field["js_request"])
			    $arrJsRequest[$tmp_data_field["js_request"]] = true;

			if ($more_detail_js_request) {
			    $arrMoreDetailJsRequest = array_filter(explode(",", $more_detail_js_request));
			    $arrJsRequest = array_replace($arrJsRequest, array_combine($arrMoreDetailJsRequest, $arrMoreDetailJsRequest));
			}

			// print_r($tmp_data_field);
		    }
		    //print_r($tmp_data_field);
		}
    }

    return array(
	"sort" => $vg_field_sort
	, "count" => $count["desc"] + $count["img"]
	, "count_desc" => $count["desc"]
	, "count_img" => $count["img"] 
	, "js_request" => $arrJsRequest
    );
}

function process_vgallery_schema($selective = null) {
    static $schema = null;

    if ($schema === null) {
	if (check_function("get_schema_def")) {
	    $schema = get_schema_def();
	}
    }
    if ($selective)
	return $schema[$selective];
    else
	return $schema;
}

function process_shard_by_schema($field_shard, $schema_tbl, $tmp_data_field = array(), $params = array()) {
    static $shard_properties = array();
    $globals = ffGlobals::getInstance("gallery");

    $stopwords = $globals->locale["lang"][LANGUAGE_INSET]["stopwords"];
    $key = (int) $params["key"];
    
    $field_name = $params["type"];
    $field_htmltag = $params["htmltag"];
    $default_key = ($params["default_key"] 
	? $params["default_key"] 
	: "default"
    );

    foreach ($field_shard AS $field_key => $field_value) {
	$shard = array();

	if (!is_numeric($field_key))
	    $field_name = $field_key;

	if (!isset($params["htmltag"]))
	    $field_htmltag = $field_name;

	if (is_array($field_value)) {
		if(isset($field_value["html"])) {
			$tmp_data_field["shard"][$key]["fields"][$field_name] = $field_value;
			$params["processed_html"] .= $field_value["html"];
		} else {
//print_r($field_value);
		//da finire vedi /medici-online/punti-di-vista/aborto-terapeutico
			
/*		print_r(process_shard_by_schema($field_value
				, $schema_tbl
			));*/
			//$tmp_data_field["shard"][$key]["fields"][$field_name] = process_shard_by_schema($field_value
			//	, $schema_tbl
			//);
		}
	} else {
	    switch ($schema_tbl["field"][$field_name]["type"]) {
		case "image":
		    $htmltag = process_vgallery_struct_data_to_htmltag($schema_tbl
			    , "prop"
			    , array(
			"field" => $field_htmltag
			, "class" => ffCommon_url_rewrite($field_name) . (CM_CACHE_IMG_LAZY_LOAD ? " lazy" : "")
			    )
		    );

		    $tmp_data_field["shard"][$key]["fields"][$field_name] = $htmltag["struct"];
		    $tmp_data_field["shard"][$key]["struct"][$field_name]["properties"] = $htmltag["properties"];
		    $tmp_data_field["shard"][$key]["struct"][$field_name]["class"] = $htmltag["class"];

		    $tmp_data_field["shard"][$key]["fields"][$field_name]["title"] = ucwords(ffCommon_url_rewrite(ffGetFilename($field_value), " "));
		    if (!$field_value) {
				if ($schema_tbl["field"][$field_name]["noimg"]) {
				    if($schema_tbl["field"][$field_name]["noimg"]["icon"]) {
						$tmp_data_field["shard"][$key]["fields"][$field_name]["content"] = cm_getClassByFrameworkCss($schema_tbl["field"][$field_name]["noimg"]["icon"], "icon-tag", $schema_tbl["field"][$field_name]["noimg"]["thumb"]);
						$tmp_data_field["shard"][$key]["fields"][$field_name]["html"] = cm_getClassByFrameworkCss($schema_tbl["field"][$field_name]["noimg"]["icon"], "icon-tag", $schema_tbl["field"][$field_name]["noimg"]["thumb"]);
				    } elseif($schema_tbl["field"][$field_name]["noimg"]["url"]) {
						$tmp_data_field["shard"][$key]["fields"][$field_name]["src"] = $schema_tbl["field"][$field_name]["noimg"]["url"];
						$tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] = ffCommon_url_rewrite_strip_word(ffGetFilename($field_value), $stopwords, " ");
				    }
				    $tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] = $schema_tbl["field"][$field_name]["noimg"]["thumb"];
				    $tmp_data_field["shard"][$key]["fields"][$field_name]["width"] = $schema_tbl["field"][$field_name]["noimg"]["width"];
				    $tmp_data_field["shard"][$key]["fields"][$field_name]["height"] = $schema_tbl["field"][$field_name]["noimg"]["height"];

				    $base_disk_path = FF_DISK_PATH;
				}
		    } else {
				$tmp_data_field["shard"][$key]["fields"][$field_name]["src"] = $field_value;
				$tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] = ffCommon_url_rewrite_strip_word(ffGetFilename($field_value), $stopwords, " ");
				$tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] = $schema_tbl["field"][$field_name]["thumb"];
				$tmp_data_field["shard"][$key]["fields"][$field_name]["width"] = $schema_tbl["field"][$field_name]["width"];
				$tmp_data_field["shard"][$key]["fields"][$field_name]["height"] = $schema_tbl["field"][$field_name]["height"];

				$base_disk_path = DISK_UPDIR;
		    }

		    if ($tmp_data_field["shard"][$key]["fields"][$field_name]["src"]) {
				$tmp_data_field["shard"][$key]["fields"][$field_name]["picture"] = get_thumb_size($tmp_data_field["shard"][$key]["fields"][$field_name]["src"]
					, $schema_tbl["field"][$field_name]["width"]
					, $schema_tbl["field"][$field_name]["height"]
					, null
					, $tmp_data_field["shard"][$key]["fields"][$field_name]
					, $base_disk_path
				);

				/*
				$tmp_data_field["shard"][$key]["fields"][$field_name]["html"] = '<img '
					. $htmltag["processed"]
					. ($tmp_data_field["shard"][$key]["fields"][$field_name]["src"] 
					    ? ' src="' . ($tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] 
						    ? CM_SHOWFILES . "/" . $tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] . $tmp_data_field["shard"][$key]["fields"][$field_name]["src"] 
						    : $tmp_data_field["shard"][$key]["fields"][$field_name]["src"]
						) . '"' 
					    : ''
					) . ($tmp_data_field["shard"][$key]["fields"][$field_name]["width"] ? ' width="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["width"] . '"' : ''
					) . ($tmp_data_field["shard"][$key]["fields"][$field_name]["height"] ? ' height="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["height"] . '"' : ''
					) . ($tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] ? ' alt="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] . '"' : ''
					) . ($tmp_data_field["shard"][$key]["fields"][$field_name]["title"] ? ' title="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["title"] . '"' : ''
					) . '/>';*/

					$tmp_data_field["shard"][$key]["fields"][$field_name]["html"] = get_thumb_by_media_queries(
						$htmltag["processed"]
						. ($tmp_data_field["shard"][$key]["fields"][$field_name]["src"] 
						    ? ' ' . (CM_CACHE_IMG_LAZY_LOAD ? 'src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="' . " data-" : "") .'src="' . ($tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] 
							    ? (CM_MEDIACACHE_SHOWPATH
							    	? CM_MEDIACACHE_SHOWPATH . str_replace(".", "-" . $tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] . ".", $tmp_data_field["shard"][$key]["fields"][$field_name]["src"])
							    	: CM_SHOWFILES . "/" . $tmp_data_field["shard"][$key]["fields"][$field_name]["thumb"] . $tmp_data_field["shard"][$key]["fields"][$field_name]["src"] 
							    )
							    : $tmp_data_field["shard"][$key]["fields"][$field_name]["src"]
							) . '"' 
						    : ''
						) 
						. ($tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] ? ' alt="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["alt"] . '"' : '') 
						. ($tmp_data_field["shard"][$key]["fields"][$field_name]["title"] ? ' title="' . $tmp_data_field["shard"][$key]["fields"][$field_name]["title"] . '"' : '')
						, $tmp_data_field["shard"][$key]["fields"][$field_name]["picture"]
						, false
					);

				$tmp_data_field["shard"][$key]["fields"][$field_name]["content"] = $tmp_data_field["shard"][$key]["fields"][$field_name]["html"];
		    }
		    
		    if ($tmp_data_field["shard"][$key]["fields"][$field_name]["content"]) {
				$params["content"][] = $tmp_data_field["shard"][$key]["fields"][$field_name]["content"];
				$params["data"][] = $tmp_data_field["shard"][$key]["fields"][$field_name]["content"];
				$params["primary_class"][] = ffCommon_url_rewrite($field_name);
		    }
		    break;
		case "link":
		    $tmp_data_field["href"] = false;
		    if (!$field_value)
			continue;

		    $params["link"] = $field_htmltag;
		    $tmp_data_field["shard"][$key]["href"] = $field_value;
			
            $first_char_link = substr($field_value, 0, 1);
            if($first_char_link != "/" && $first_char_link != "#")
                $tmp_data_field["shard"][$key]["target"] = "_blank";

			if (strpos($field_value, "www.") !== false) 
				$tmp_data_field["shard"][$key]["text"] = substr($field_value, strpos($field_value, "www.") + 4);
			else if (substr($field_value, 0, 7) == "http://" || substr($field_value, 0, 8) == "https://" || substr($field_value, 0, 2) == "//") 
				$tmp_data_field["shard"][$key]["text"] = substr($field_value, strpos($field_value, "://") + 3);
			else
				$tmp_data_field["shard"][$key]["text"] = $field_value;                
  
  			//$tmp_data_field["shard"][$key]["content"] = $field_value;
			$tmp_data_field["shard"][$key]["slug"] = ffCommon_url_rewrite(strip_tags($tmp_data_field["shard"][$key]["content"]));
		    break;
		default:
		    if (!$field_value)
			continue;
//echo "bla: " . $params["class"] . "-" .  $field_htmltag . "<br>\n";

		    $htmltag = process_vgallery_struct_data_to_htmltag($schema_tbl
			    , "prop"
			    , array(
			    	  "field" => $field_htmltag
					, "class" => ffCommon_url_rewrite($field_name)
			    )
		    );
		    $tmp_data_field["shard"][$key]["fields"][$field_name] = $htmltag["struct"];
		    //ffErrorHandler::raise("ASD", E_USER_WARNING, null, get_defined_vars());
		    $tmp_data_field["shard"][$key]["struct"][$field_name]["properties"] = $htmltag["properties"];
		    $tmp_data_field["shard"][$key]["struct"][$field_name]["class"] = $htmltag["class"];
		    $tmp_data_field["shard"][$key]["fields"][$field_name]["html"] = '<span '
			    . $htmltag["processed"]
			    . '>' . $field_value . '</span>'; 

		    if (isset($schema_tbl["field"][$field_name]["struct_data"])) {
			$tmp_data_field["shard"][$key]["fields"][$field_name]["content"] = $field_value;
			//$tmp_data_field["shard"][$key][$field_name]["content"] = $tmp_data_field["shard"][$key][$field_name]["default"];
			$params["content"][] = $tmp_data_field["shard"][$key]["fields"][$field_name]["content"];
		    } else {
			$tmp_data_field["shard"][$key]["fields"][$field_name]["content"] = $field_value;
			$params["content"]["base"] .= ($params["content"]["base"] ? " " : "") . $tmp_data_field["shard"][$key]["fields"][$field_name]["content"];
		    }

		    $params["data"][] = $field_value;
		    $params["primary_class"][] = ffCommon_url_rewrite($field_name);
	    }
	}
	if($tmp_data_field["shard"][$key]["fields"][$field_name]["html"])
		$params["default"][] = $tmp_data_field["shard"][$key]["fields"][$field_name]["html"];

	if (!isset($key))
	    $key++;
    }

    if ($params["link"]) {
		$microclass = ffCommon_url_rewrite($params["link"]);

		if (isset($schema_tbl["field"][$params["link"]]["struct_data"])) {
		    if (count($params["content"]) > 1) {
			$htmltag = process_vgallery_struct_data_to_htmltag($schema_tbl
				, "prop"
				, array(
		    		"field" => $params["link"]
				)
			);

			foreach ($params["content"] AS $content_key => $content_data) {
			    if ($content_key === "base") {
				$tmp_data_field["shard"][$key]["content"] .= '<span '
					. $htmltag["processed"]
					. '>' . $content_data . '</span>';
			    } else {
				$tmp_data_field["shard"][$key]["content"] .= $content_data;
			    }
			}
		    } else {
			$microclass = $params["primary_class"][0];
			$tmp_data_field["shard"][$key]["content"] = implode(" ", $params["data"]);
		    }
		} else {
		    $tmp_data_field["shard"][$key]["content"] = implode(" ", $params["content"]);
		}

		$htmltag_link = process_vgallery_struct_data_to_htmltag($schema_tbl
			, "proplink"
			, array(
		    "field" => $params["link"]
		    , "class" => $microclass
		    , "key" => "link-" . $params["class"] . "-". $params["link"]
			)
		);

		$tmp_data_field["shard"][$key]["link"] = '<a '
			. $htmltag_link["processed"]
			. ($tmp_data_field["shard"][$key]["href"] ? ' href="' . $tmp_data_field["shard"][$key]["href"] . '"' : ''
			) . ($tmp_data_field["shard"][$key]["target"] ? ' target="' . $tmp_data_field["shard"][$key]["target"] . '"' : ''
			) . '>' . $tmp_data_field["shard"][$key]["content"] . '</a>';

		$tmp_data_field["shard"][$key]["default"] = $tmp_data_field["shard"][$key]["link"];

		if (is_array($params["shard_allowed"]) && count($params["shard_allowed"])) {
		    foreach ($tmp_data_field["shard"][$key]["fields"] AS $shard_key => $shard_data) {
			if (array_key_exists($shard_key, $params["shard_allowed"])) {
			    $htmltag = process_vgallery_struct_data_to_htmltag($schema_tbl
				    , null
				    , array(
				"merge" => array(
				    "key" => $shard_key
				    , "struct" => $htmltag_link["struct"]
				)
				, "field" => $params["link"]
				, "key" => $params["link"] . "-" . $shard_key
				    )
			    );
			    $tmp_data_field["shard"][$key]["fields"][$shard_key]["html"] = '<a '
				    . $htmltag["processed"]
				    . ($tmp_data_field["shard"][$key]["href"] ? ' href="' . $tmp_data_field["shard"][$key]["href"] . '"' : ''
				    ) . ($tmp_data_field["shard"][$key]["target"] ? ' target="' . $tmp_data_field["shard"][$key]["target"] . '"' : ''
				    ) . '>' . $shard_data["content"] . '</a>';
			}
		    }
		}

		$params["processed_default"] = $tmp_data_field["shard"][$key]["default"] . $params["processed_html"];
		$params["processed_content"] = $tmp_data_field["shard"][$key]["content"];
    } else/*if (!$params["processed_html"])*/ {
		$tmp_data_field["shard"][$key]["default"] = implode(" ", array_filter($params["default"]));

		if (is_array($params["data"]))
		    $tmp_data_field["shard"][$key]["content"] = implode(" ", array_filter($params["data"]));
		else
		    $tmp_data_field["shard"][$key]["content"] = $tmp_data_field["shard"][$key]["default"];

		$params["processed_default"] = $tmp_data_field["shard"][$key]["default"];
		$params["processed_content"] = $tmp_data_field["shard"][$key]["content"];
    }

    if ($tmp_data_field["content"])
	$tmp_data_field["content"] .= " ";

    $tmp_data_field["content"] .= $params["processed_content"] . $params["processed_html"];

    if ($tmp_data_field[$default_key])
	$tmp_data_field[$default_key] .= " ";

    if ($params["container"])
	$tmp_data_field[$default_key] .= '<' . $params["container"] . '>' . $params["processed_default"] /*. $params["processed_html"]*/ . '</' . $params["container"] . '>';
    else
	$tmp_data_field[$default_key] .= $params["processed_default"] /*. $params["processed_html"]*/;

    if ($default_key == "default")
	$tmp_data_field["htmltag"]["tag"] = "span";

    /*
      if($params["total"] <= 1) {
      if(!$params["processed_html"]) {
      if($tmp_data_field["default"])
      $tmp_data_field["default"] .= " ";

      $tmp_data_field["default"] .= $params["processed_default"];
      }
      }
     */
    // print_r($tmp_data_field);

    return $tmp_data_field;
}

function process_vgallery_schema_table($sourceData, $storageData = null, $field_name = null, $exclude = null) {
    $schema = process_vgallery_schema("schema");
    $res = null;

    $tbl = $schema[$sourceData["tbl"]];
    $field = $sourceData["field"];
    if ($field == "null")
	$field = $field_name;

    if (is_array($tbl)) {
	if ($storageData && is_array($schema[$storageData["tbl"]]["field"][$storageData["field"]]["struct_data"]["override"])) {
	    if (is_array($schema[$storageData["tbl"]]["struct_data"]))
		$tbl["struct_data"] = $schema[$storageData["tbl"]]["struct_data"];

	    foreach ($schema[$storageData["tbl"]]["field"][$storageData["field"]]["struct_data"]["override"] AS $field_key) {
		if (array_key_exists($field_key, $tbl["field"])) {
		    $tbl["field"][$field_key]["struct_data"] = $schema[$storageData["tbl"]]["field"][$storageData["field"]]["struct_data"];
		}
	    }
	}

	if ($tbl["struct_data"]["type"] != $exclude) {
	    $res = array(
		"tbl" => $tbl
		, "field" => $field
	    );
	}
    }

    return $res;
}

function process_tpl_by_schema($data, &$tmp_data_field, $field_name, $sourceData, $storageData = null, $shard_allowed = array()) {
    $data_html = null;
    $count_data = 0;

    $res_schema = process_vgallery_schema_table($sourceData, $storageData, $field_name);
    if (is_array($data)) {
		if($res_schema) {
			foreach ($data AS $field_key => $field_data) {
                $tmp_field[$field_key] = "";
				if (is_array($field_data)) {
					if ($res_schema["tbl"]["field"][$field_key]["data_source"]) {
						//print_r($res_schema["tbl"]);
						//echo $field_key . "ASD";
						foreach ($field_data AS $field_data_key => $field_data_shard) {
							if(is_array($field_data_shard)) {
								$tmp_field[$field_key] = process_shard_by_schema($field_data_shard
									, $res_schema["tbl"]
									, $tmp_field[$field_key]
									, array(
									"key" => $field_data_key
									, "total" => count($field_data)
									, "htmltag" => $field_key
									, "default_key" => "html"
									)
								);
							} else {
								$tmp_field[$field_key] = $field_data_shard;
							}
						}
						//print_r($tmp_field);
						//$tmp_data_field = "SAD";
					} else {
						$tmp_data_field = process_shard_by_schema($field_data
							, $res_schema["tbl"]
							, $tmp_data_field
							, array(
						"key" => $field_key
						, "class" => $field_name
						, "total" => count($data)
						, "shard_allowed" => $shard_allowed
							)
						);
			//print_r($tmp_data_field);
						/*
						  if(isset($schema[$tbl])) {
						  $tmp_data_field = process_shard_by_schema($field_key, $field_data, $schema[$tbl], $tmp_data_field);
						  //$data_html .= $tmp_data_field["shard"][$field_key]["default"];
						  } else {
						  //$data_html .= " " . implode(" " , $field_data);
						  }
						 */
					}
				} else {
				    $tmp_field[$field_key] = $field_data;
				    if (is_numeric($field_key))
					    $count_data++;
				}
			}

			if (is_array($tmp_field) && count($tmp_field)) {
				$tmp_data_field = process_shard_by_schema($tmp_field
					, $res_schema["tbl"]
					, $tmp_data_field
					, array(
				        "class" => $field_name
				        , "total" => $count_data
				        , "type" => $res_schema["field"]
				        , "shard_allowed" => $shard_allowed
					)
				);
				//print_r( $tmp_data_field );
				// if(count($tmp_data_field["content"]) > 1)
				// 	$tmp_data_field["htmltag"] = false;
			}
		} else {
			if(count($data) > 1) {
				$data_html = '<span>' . implode("</span><span>", $data) . '</span>';
			} else {
				$data_html = implode("", $data);
			}
		}
	} else {
		$data_html = $data;
    }

    // if(!isset($tmp_data_field["href"]) && count($tmp_data_field["content"]) == 1)
    // 	$tmp_data_field["default"] = implode("", $tmp_data_field["content"]);
//print_r($tmp_data_field);
    //da gestire la customizzazione dei campi in base allo schema	
    return $data_html;
}

function process_vgallery_struct_data_to_htmltag($schema_tbl, $microtype = null, $params = null, $out = null) {
    static $loaded_htmltag = array();
    $structured_data = array();
    $htmltag = array(
	"struct" => array()
	, "html" => array()
	, "processed" => ""
	, "class" => ""
	, "properties" => ""
    );
    if (is_array($schema_tbl))
	$src_table = $schema_tbl["struct_data"]["type"];
    else
	$src_table = $schema_tbl;

    $key = $params["key"];
    if (!$key)
	$key = $params["field"];

    if (isset($loaded_htmltag[$src_table][$key])) {
	$htmltag = $loaded_htmltag[$src_table][$key];
    } else {
	if (is_array($schema_tbl)) {
	    if (is_array($params["merge"])) {
		$htmltag = $loaded_htmltag[$src_table][$params["merge"]["key"]];
		$htmltag["struct"] = array_merge_recursive($htmltag["struct"], $params["merge"]["struct"]);
	    } else {
		if (is_array($schema_tbl["struct_data"]))
		    $structured_data = $schema_tbl["struct_data"];

		if ($params["field"] && is_array($schema_tbl["field"][$params["field"]]["struct_data"]))
		    $structured_data = array_replace_recursive($structured_data, $schema_tbl["field"][$params["field"]]["struct_data"]);

		$htmltag["html"]["type"] = $src_table;
		if ($structured_data["microdata"]) {
		    if ($microtype === null || $microtype == "scope" || $microtype == "scopeonly") {
			$htmltag["struct"]["properties"]["itemscope"] = true;
			$htmltag["html"]["properties"]["struct"] = "itemscope";
			if ($structured_data["microdata"]["scope"]) {
			    $htmltag["struct"]["properties"]["itemtype"] = $structured_data["microdata"]["scope"];
			    $htmltag["html"]["properties"]["struct"] .= ' itemtype="' . $structured_data["microdata"]["scope"] . '"';
			}
		    }

		    if ($microtype === null || $microtype == "proplink") {
			if ($structured_data["microdata"]["proplink"]) {
			    $htmltag["struct"]["properties"]["itemprop"] = $structured_data["microdata"]["proplink"];
			    $htmltag["html"]["properties"]["struct"] = ' itemprop="' . $structured_data["microdata"]["proplink"] . '"';
			}
		    }

		    if ($microtype === null || $microtype == "prop" || !isset($structured_data["microdata"]["proplink"])) {
			if ($structured_data["microdata"]["prop"]) {
			    $htmltag["struct"]["properties"]["itemprop"] = $structured_data["microdata"]["prop"];
			    $htmltag["html"]["properties"]["struct"] = ' itemprop="' . $structured_data["microdata"]["prop"] . '"';
			}
		    }
		}
		if ($structured_data["microformat"]) {
		    if ($microtype === null || $microtype == "scope" || $microtype == "scopeonly") {
			$htmltag["struct"]["class"]["vcard"] = "vcard";
			$htmltag["html"]["class"]["vcard"] = "vcard";
		    }

		    if ($microtype === null || $microtype == "scope" || $microtype == "scopestrict") {
			if ($structured_data["microformat"]["scope"]["class"]) {
			    $htmltag["struct"]["class"]["vcardscope"] = $structured_data["microformat"]["scope"]["class"];
			    $htmltag["html"]["class"]["vcardscope"] = $structured_data["microformat"]["scope"]["class"];
			}
			if ($structured_data["microformat"]["scope"]["rel"]) {
			    $htmltag["struct"]["rel"] = $structured_data["microformat"]["scope"]["rel"];
			    $htmltag["html"]["rel"] = $structured_data["microformat"]["scope"]["rel"];
			}
		    }

		    if ($microtype === null || $microtype == "proplink") {
			if ($structured_data["microformat"]["proplink"]["class"]) {
			    $htmltag["struct"]["class"]["vcardprop"] = $structured_data["microformat"]["proplink"]["class"];
			    $htmltag["html"]["class"]["vcardprop"] = $structured_data["microformat"]["proplink"]["class"];
			}
		    }

		    if ($microtype === null || $microtype == "prop" || !isset($structured_data["microformat"]["proplink"])) {
			if ($structured_data["microformat"]["prop"]["class"]) {
			    $htmltag["struct"]["class"]["vcardprop"] = $structured_data["microformat"]["prop"]["class"];
			    $htmltag["html"]["class"]["vcardprop"] = $structured_data["microformat"]["prop"]["class"];
			}
		    }
		}
	    }
	}

	if (is_array($params["replace"])) {
	    $htmltag["struct"] = array_replace_recursive($htmltag["struct"], $params["replace"]);
	}

	if ($params["class"] === false) {
	    $htmltag["struct"]["class"] = false;
	    $htmltag["html"]["class"] = false;
	} elseif (strlen($params["class"]) && $params["class"] !== true) {
	    $htmltag["struct"]["class"]["custom"] = $params["class"];
	    $htmltag["html"]["class"]["custom"] = $params["class"];
	}

	if (is_array($htmltag["html"]["properties"]))
	    $htmltag["properties"] = implode(" ", $htmltag["html"]["properties"]);

	if (is_array($htmltag["struct"]["class"]))
	    $htmltag["class"] = 'class="' . implode(" ", array_filter($htmltag["struct"]["class"])) . '"';

	$htmltag["processed"] = process_vgallery_htmltag_attr($htmltag);

	$loaded_htmltag[$src_table][$key] = $htmltag;
    }
//print_r($loaded_htmltag);
    if ($out)
	$res = $htmltag[$out];
    else
	$res = $htmltag;

    return $res;
}

function process_vgallery_htmltag_attr($htmltag = array(), $alt_class = null) {
    $htmltag_attr = array();

    if ($alt_class)
	$htmltag["struct"]["class"]["custom"] = $alt_class;

    if (is_array($htmltag["struct"]) && count($htmltag["struct"])) {
	foreach ($htmltag["struct"] AS $attr_key => $attr_value) {
	    if (is_array($attr_value) && isset($htmltag[$attr_key]))
		$htmltag_attr[] = $htmltag[$attr_key];
	    elseif ($attr_value === true)
		$htmltag_attr[] = $attr_key;
	    elseif (strlen($attr_value))
		$htmltag_attr[] = $attr_key . '="' . $attr_value . '"';
	}
    }

    return implode(" ", $htmltag_attr);
}

function process_vgallery_field_by_extended_type(&$vg_father, $data, $field_params, $vg_params, $tmp_data_field, $output = null) {
	$cm = cm::getInstance();
    if (is_array($data))
		$data =  implode(" ", $data);
	//$data = '<span>' . implode("</span><span>", $data) . '</span>';		

	$disable_label = false;
	$field_params["label_data"] = ffTemplate::_get_word_by_code($field_params["name"] . "_" . $vg_father["mode"]);
	if ($field_params["label_data"] == "{" . $field_params["name"] . "_" . $vg_father["mode"] . "}" || $field_params["label_data"] == $field_params["name"] . "_" . $vg_father["mode"])
	    $field_params["label_data"] = ffTemplate::_get_word_by_code($field_params["name"]);
	
    if (strlen($data) || $field_params["extended_type_group"] == "upload") {
		if (check_function("set_generic_tags")) {
		    $data = set_generic_tags($data);
		}

		if ($field_params["enable_lastlevel"] && !isset($tmp_data_field["href"])) {
		    $tmp_data_field["target"] = $vg_params["target"];
		    $tmp_data_field["href"] = $vg_params["url"];
		}

		$tmp_data_field = process_vgallery_data_by_type($vg_father, $data, $field_params, $vg_params, $tmp_data_field);
		if(!$tmp_data_field["content"] && !$field_params["enable_empty"])
			$tmp_data_field = array();		

    } elseif($field_params["enable_empty"]) {
    	$tmp_data_field["content"] = "";
    }

    $cm->doEvent("vg_on_vgallery_field_process_extended_type", array($vg_father, $data, &$field_params, $vg_params, &$tmp_data_field, $output));
    
    if (count($tmp_data_field)) {
		if ($tmp_data_field["htmltag"] !== false) {
		    $htmltag_params = array(
			"exclude" => $vg_params["htmltag"]["type"]
			, "microtype" => "scope"
			, "replace" => $field_params["htmltag_attr"]
		    );
		}
		$tmp_data_field = process_vgallery_htmltag_field($vg_father
			, $field_params
			, $htmltag_params
			, $tmp_data_field
		);
		/*
		  if($tmp_data_field["htmltag"] !== false) {
		  if(is_array($field_params["htmltag_attr"])) {
		  if(is_array($tmp_data_field["htmltag"]["attr"])) {
		  $tmp_data_field["htmltag"]["attr"] = array_replace($tmp_data_field["htmltag"]["attr"], $field_params["htmltag_attr"]);
		  } else {
		  $tmp_data_field["htmltag"]["attr"] = $field_params["htmltag_attr"];
		  }
		  }
		  } */
		//print_r($tmp_data_field);
		//if(!isset($tmp_data_field["htmltag"])) {
		//}    
	//print_r($tmp_data_field);

		/**
		 * Label
		 */
		if ($field_params["enable_label"]
			|| ($field_params["extended_type_group"] == "upload"  && $field_params["display_view_mode"] && !$field_params["enable_lastlevel"]) 
		) {
		    if (!isset($tmp_data_field["label"]))
				$tmp_data_field["label"]["content"] = $field_params["label_data"];

		   // if ($field_params["display_view_mode"] && !$field_params["enable_lastlevel"]) {
			//Load JS Plugin
				//$tmp_data_field["js_request"] = $field_params["display_view_mode"];

			//$tmp_data_field["rel"] = $field_params["display_view_mode"];
			//$tmp_data_field["class"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $field_params["display_view_mode"]);
		   // }
		}

		/* Set Field Content */
		if (!isset($tmp_data_field["prefix"]))
		    $tmp_data_field["prefix"] = $field_params["fixed_pre_content"];
		if (!isset($tmp_data_field["postfix"]))
		    $tmp_data_field["postfix"] = $field_params["fixed_post_content"];
		if (!isset($tmp_data_field["class"])) {
		    $tmp_data_field["class"]["default"] = ffCommon_url_rewrite($field_params["name"]);
		}

		if (isset($tmp_data_field["src"])) {
		    if ($tmp_data_field["src"] === true) {
		    	$tmp_data_field["src"] = get_thumb_by_placehold($vg_father["properties"]["image"]["src"]["default"]["width"], $vg_father["properties"]["image"]["src"]["default"]["height"]);
				$tmp_data_field["img"] = "";
		    } else {
				$htmltag = process_vgallery_special_field($field_params
					, "prop"
					, array(
				    "class" => (strlen($tmp_data_field["htmltag"]["tag"]) ? false : true)
				    , "src" => $vg_father["src"]
				    , "key" => "img-" . $field_params["name"]
					)
				);

				/*
				$tmp_data_field["img"] = '<img '
					. ($tmp_data_field["href"] && is_array($tmp_data_field["class"]) && count($tmp_data_field["class"]) ? ' class="' . implode(" ", array_filter($tmp_data_field["class"])) . '"' : $htmltag["processed"]
					)
					. (!isset($tmp_data_field["href"]) && $tmp_data_field["rel"] ? ' rel="' . $tmp_data_field["rel"] . '"' : ''
					) . ($tmp_data_field["src"] ? ' src="' . $tmp_data_field["src"] . '"' : ''
					) . ($tmp_data_field["width"] ? ' width="' . $tmp_data_field["width"] . '"' : ''
					) . ($tmp_data_field["height"] ? ' height="' . $tmp_data_field["height"] . '"' : ''
					) . ($tmp_data_field["alt"] ? ' alt="' . $tmp_data_field["alt"] . '"' : ''
					) . ($tmp_data_field["title"] ? ' title="' . $tmp_data_field["title"] . '"' : ''
					) . '/>';*/

				$arrImgClass = array();
				if(!$tmp_data_field["href"] && is_array($tmp_data_field["class"]) && count($tmp_data_field["class"]))
					$arrImgClass = $tmp_data_field["class"];
				if(CM_CACHE_IMG_LAZY_LOAD)
					$arrImgClass["lazy"] = "lazy";

				$tmp_data_field["img"] = get_thumb_by_media_queries(
					(is_array($arrImgClass) && count($arrImgClass)
						? ' class="' . implode(" ", array_filter($arrImgClass)) . '"' 
						: $htmltag["processed"]
					)
					. (!isset($tmp_data_field["href"]) && $tmp_data_field["rel"] ? ' rel="' . $tmp_data_field["rel"] . '"' : '') 
					. ($tmp_data_field["src"] ? ' ' . (CM_CACHE_IMG_LAZY_LOAD ? 'src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="' . " data-" : "") . 'src="' . $tmp_data_field["src"] . '"' : '') 
					. ($tmp_data_field["alt"] ? ' alt="' . $tmp_data_field["alt"] . '"' : '') 
					. ($tmp_data_field["title"] ? ' title="' . $tmp_data_field["title"] . '"' : '') 
					, $tmp_data_field["picture"]
				);

				//if(!$vg_father["seo"]["image"] || count($vg_father["seo"]["image"]) < 10)
			   // 	$vg_father["seo"]["image"][] = $tmp_data_field["picture"];

		    }
		}

		if (strlen($field_params["custom_field"])) {
		    $tmp_data_field["htmltag"] = false;

		    //if (is_array($field_params["custom_field"]) && count($field_params["custom_field"])) {
			if (is_array($tmp_data_field) && count($tmp_data_field)) {
			    foreach ($tmp_data_field AS $tmp_data_field_key => $tmp_data_field_value) {
					$field_params["custom_field"] = str_replace("{" . $tmp_data_field_key . "}", $tmp_data_field_value, $field_params["custom_field"]);
			    }
			}
		    //}

		    $tmp_data_field["default"] = $field_params["custom_field"];
		}

		if (isset($tmp_data_field["label"])) {
		    if (!strlen($field_params["htmltag_label_tag"]))
			$field_params["htmltag_label_tag"] = "label";

		    $tmp_data_field["label"]["default"] = '<' . $field_params["htmltag_label_tag"]
			    . (is_array($field_params["htmltag_label_attr"]) ? ' ' . implode(" ", array_filter($field_params["htmltag_label_attr"])) : ''
			    )
			    . '>'
			    . $tmp_data_field["label"]["content"]
			    . '</' . $field_params["htmltag_label_tag"] . '>';
		}

		if (!isset($tmp_data_field["default"])) {
		    if ($tmp_data_field["href"]) {
				if (!isset($tmp_data_field["link"])) {
				    $htmltag = process_vgallery_special_field($field_params
					    , "prop"
					    , array(
							"class" => (strlen($tmp_data_field["htmltag"]["tag"]) ? $tmp_data_field["js_request"] : implode(" ", $tmp_data_field["class"]))
							, "src" => $vg_father["src"]
							, "key" => "link-" . $field_params["name"]
					    )
				    );
				    $tmp_data_field["link"] = '<a '
					    . $htmltag["processed"]
					    . ($tmp_data_field["rel"] ? ' rel="' . $tmp_data_field["rel"] . '"' : '') 
					    . ($tmp_data_field["id"] ? ' id="' . $tmp_data_field["id"] . '"' : '') 
					    . ($tmp_data_field["href"] ? ' href="' . $tmp_data_field["href"] . '"' : '') 
					    . ($tmp_data_field["target"] ? ' target="' . $tmp_data_field["target"] . '"' : '') 
					. '>' . (isset($tmp_data_field["img"]) ? $tmp_data_field["img"] : $tmp_data_field["content"]) . '</a>';
				}
				$tmp_data_field["default"] = $tmp_data_field["link"];
		    } elseif (isset($tmp_data_field["img"])) {
				$tmp_data_field["default"] = $tmp_data_field["img"];
				if ($tmp_data_field["label"] && !$tmp_data_field["htmltag"]["tag"])
				    $tmp_data_field["htmltag"]["tag"] = "span";
				
		    } else {
				$tmp_data_field["default"] = $tmp_data_field["content"];
				if (!$tmp_data_field["htmltag"]["tag"])
				    $tmp_data_field["htmltag"]["tag"] = "span";
		    }
		}

		$tmp_data_field["html"] = $tmp_data_field["default"];

		if ($tmp_data_field["label"]["default"])
		    $tmp_data_field["default"] = $tmp_data_field["label"]["default"] . $tmp_data_field["default"];

		if (strlen($tmp_data_field["htmltag"]["tag"])) {
		    $tmp_data_field["default"] = '<' . $tmp_data_field["htmltag"]["tag"] . ' '
			    . $tmp_data_field["htmltag"]["processed"]
			    . ($tmp_data_field["id"] ? ' id="' . $tmp_data_field["id"] . '"' : ''
			    )
			    . '>' . $tmp_data_field["default"] . '</' . $tmp_data_field["htmltag"]["tag"] . '>';
		}

		if ($output)
		    $res = $tmp_data_field[$output];
		else
		    $res = $tmp_data_field;
    }
//print_r($res);
    return $res;
}

function process_vgallery_special($tbl, $microtype) {
    $schema = process_vgallery_schema("schema");
    $htmltag = array();

    if (is_array($schema[$tbl])) {
	$htmltag = process_vgallery_struct_data_to_htmltag(
		$schema[$tbl]
		, $microtype
		, null
		, "html"
	);
    }

    return $htmltag;
}

function process_vgallery_special_field($field_params, $microtype, $params = null, $exclude = null, $replace = null) {
    $htmltag = array();

    $res_schema = process_vgallery_schema_table(
	    array(
	"tbl" => ($field_params["select"]["data_source"] ? $field_params["select"]["data_source"] : $params["src"]["table"])
	, "field" => $field_params["select"]["data_limit"]
	    )
	    , array(
	"tbl" => $field_params["data_source"]
	, "field" => $field_params["data_limit"]
	    )
	    , null
	    , $exclude
    );

    $htmltag = process_vgallery_struct_data_to_htmltag(
	    ($res_schema ? $res_schema["tbl"] : $params["src"]["table"])
	    , $microtype
	    , array(
	"key" => ($params["key"] ? $params["key"] : $field_params["name"])
	, "field" => $field_params["name"]
	, "class" => $params["class"]
	, "replace" => $replace
	    )
    );

    return $htmltag;
}

function process_vgallery_htmltag_field($vg_father, $field_params, $struct_data = null, $tmp_data_field = array()) {
    $cm = cm::getInstance();

    if (is_array($struct_data)) {
		$microtype = $struct_data["microtype"];
		$exclude = $struct_data["exclude"];
		$replace = $struct_data["replace"];
    } else {
		$microtype = $struct_data;
    }
    
    if(check_function("get_class_by_grid_system")) {
		$replace["class"] = get_class_by_grid_system(array(
			"fluid" => $field_params["field_fluid"]
			, "grid" => (strlen($field_params["field_grid"]) ? explode(",", $field_params["field_grid"]) : "")
		), "grid", (isset($replace["class"]) ? $replace["class"] : null)); 
	}    

    if (!$tmp_data_field["htmltag"]["tag"])
	$tmp_data_field["htmltag"]["tag"] = (global_settings("ENABLE_VGALLERY_HTMLTAG_AUTOMATIC") && $field_params["smart_url"] ? "h" . ($field_params["smart_url"] + $vg_father["enable_title"] + ($vg_father["mode"] == "thumb" ? ($vg_father["type"] == "publishing" ? 2 : 1
				) : 0
			)
			) : null
		);

    $replace["class"]["default"] = ffCommon_url_rewrite($field_params["name"]);
    if ($vg_father["mode"] != "thumb" && $field_params["smart_url"]) {
	$replace["class"]["vcard"] = "entry-title";
    }


    if ($struct_data) {
	$htmltag = process_vgallery_special_field($field_params
		, $microtype
		, array(
	    "class" => $field_params["field_class"]
	    , "src" => $vg_father["src"]
	    , "key" => ($vg_father["type"] == "publishing" ? $vg_father["publishing"]["limit_fields"] . "-" : "") . $vg_father["type"] . "-" . $field_params["name"]
		)
		, $exclude
		, $replace
	);

	$tmp_data_field["htmltag"]["attr"] = $htmltag["struct"];
	$tmp_data_field["htmltag"]["processed"] = $htmltag["processed"];

	if ($field_params["htmltag_tag"])
	    $tmp_data_field["htmltag"]["tag"] = $field_params["htmltag_tag"];
    }

    return $tmp_data_field;
}

function process_vgallery_data_by_type(&$vg_father, $data, $field_params, $vg_params, $tmp_data_field = array()) {
	//static $imgStatic = array();
    $globals = ffGlobals::getInstance("gallery");

    $stopwords = $globals->locale["lang"][LANGUAGE_INSET]["stopwords"];

    if (ffCommon_charset_decode($data))
		$data = html_entity_decode($data, ENT_QUOTES, "UTF-8");
    $data = htmlspecialchars_decode($data);

	if ($field_params["plugin"]["name"]) {
		//Load JS Plugin
		$tmp_data_field["js_request"] = $field_params["plugin"]["name"];
		//$tmp_data_field["rel"] = $field_params["plugin"]["name"];
		$tmp_data_field["class"]["plugin"] = $field_params["plugin"]["class"];
	}
	
    switch (strtolower($field_params["extended_type"])) {
	case "image":
	case "upload":
	case "uploadimage":
	    if (isset($tmp_data_field["src"])) {
			if ($tmp_data_field["src"]) {
				$tmp_data_field = get_thumb_size($tmp_data_field["src"], $tmp_data_field["width"], $tmp_data_field["height"], null, $tmp_data_field);
/*
			    if (!isset($tmp_data_field["width"]) || !isset($tmp_data_field["height"])) {
					$attrs = @getimagesize($tmp_data_field["src"]);
					if (is_array($attrs) && $attrs[0] > 0 && $attrs[1] > 0) {
					    $tmp_data_field["width"] = ($tmp_data_field["width"] ? $tmp_data_field["width"] : $attrs[0]);
					    $tmp_data_field["height"] = ($tmp_data_field["height"] ? $tmp_data_field["height"] : $attrs[1]);
					}
			    }
*/
			} else {
			    $tmp_data_field["src"] = true;
			}
	    } else {
			$image_preserve_orig = false;
			$base_path = DISK_UPDIR;
			$showfiles_path = "";
			$skip_mode = false;

			if($data) {
	            if(substr(CM_SHOWFILES, 0, 7) == "http://" || substr(CM_SHOWFILES, 0, 8) == "https://" || substr(CM_SHOWFILES, 0, 2) == "//") {
	                $img_static = check_thumb_format($data);
	                $base_path                  = $img_static["base_path"];
	                $showfiles_path             = $img_static["showfiles_path"];
	                $skip_mode                  = $img_static["skip_mode"];

	                $data                       = $img_static["path"];
	            } else {
	                $img_static = check_thumb_format($data, null, null, null, DISK_UPDIR, DISK_UPDIR);
	                if($img_static["base_path"]) {
	                    $base_path              = $img_static["base_path"];
	                    $showfiles_path         = $img_static["showfiles_path"];
	                    $skip_mode              = $img_static["skip_mode"];
	                    $image_preserve_orig    = $img_static["preserve_orig"];
	                    
	                    $data                   = $img_static["path"];
	                }
	            }
			}			
			if (!$data && ($vg_params["placeholder"] || $field_params["enable_empty"])) {
				if($field_params["group"] == "img") {
					if(strlen($vg_params["cover"]["path"])) {
						$base_path 				= $vg_params["cover"]["base_path"];
						$showfiles_path 		= $vg_params["cover"]["showfiles"];
						$data 					= $vg_params["cover"]["path"];
						$skip_mode 				= $vg_params["cover"]["mode"];
						$image_preserve_orig 	= true;			
					} elseif($vg_params["noimg"]["placehold"]) {
						$data 					= get_thumb_by_placehold($vg_father["properties"]["image"]["src"]["default"]["width"], $vg_father["properties"]["image"]["src"]["default"]["height"]);
						$skip_mode 				= $vg_father["properties"]["image"]["src"];
					}
				} else {
					if(strlen($vg_params["noimg"]["path"])) {
						$base_path 				= $vg_params["noimg"]["base_path"];
						$showfiles_path 		= $vg_params["noimg"]["showfiles"];
						$data 					= $vg_params["noimg"]["path"];
						$skip_mode 				= $vg_params["noimg"]["mode"];
						$image_preserve_orig 	= true;			
					} elseif($vg_params["noimg"]["placehold"] && $field_params["enable_empty"]) {
						$data 					= get_thumb_by_placehold($field_params["image"]["src"]["default"]["width"], $field_params["image"]["src"]["default"]["height"]);
						$skip_mode 				= $field_params["image"]["src"];
					}
				}
			}

			//if(!(substr($data, 0, 1) == "/" || substr($data, 0, 2) == "//" || substr($data, 0, 5) == "http")) {
			//	$data = "";
			//}
			
			//print_r($vg_father["properties"]["image"]["src"]);
			//print_r($img_static);
			//da sostituire con questo   ---^:
			/*
			if ($data && !is_file(DISK_UPDIR . $data)) {
			    $image_preserve_orig = true;
			    if(strpos($data, FF_THEME_DIR . "/" . FRONTEND_THEME . "/images") === 0 && is_file(FF_DISK_PATH . $data)) {
			    	if(!isset($imgStatic[$data])) {
						$imgStatic[$data]["base_path"] = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images";
						$imgStatic[$data]["showfiles_path"] = "/" . FRONTEND_THEME . "/images";
			    		$imgStatic[$data]["path"] = substr($data, strlen(FF_THEME_DIR . "/" . FRONTEND_THEME . "/images"));
						$imgStatic[$data]["mime"] = ffMimeType(FF_DISK_PATH . $data);

						switch($imgStatic[$data]["mime"]) { 
			    			case "image/svg+xml":
			    				$imgStatic[$data]["skip_mode"] = true;
			    				break;
							case "image/jpeg":
							case "image/png":
							case "image/gif":
				    			$imgStatic[$data]["skip_mode"] = false; 
								break;
							default:
						} 
					}

					$base_path 		= $imgStatic[$data]["base_path"];
					$showfiles_path = $imgStatic[$data]["showfiles_path"];
					$skip_mode 		= $imgStatic[$data]["skip_mode"];
					$data 			= $imgStatic[$data]["path"];
				} elseif (strlen($vg_params["cover"]["path"])) {
					$base_path = $vg_params["cover"]["base_path"];
					$showfiles_path = $vg_params["cover"]["showfiles"];
					$data = $vg_params["cover"]["path"];
					$skip_mode = $vg_params["cover"]["mode"];
			    } else {
					$data = "";
			    }
			}*/

			if ($data && strlen($data) <= 512) {
			    if ($field_params["group"] == "img") {
					$tmp_data_field = get_thumb(
						$data
						, array(
							"base_path" => $base_path
							, "showfiles_path" => $showfiles_path
							, "fake_name" => $vg_params["meta"]["title"]
							, "preserve_orig" => $image_preserve_orig 
							, "thumb" => ($skip_mode
								? null
								: $vg_father["properties"]["image"]["src"]
							)
							, "highlight" => ($skip_mode
								? null
								: $vg_params["highlight"]["image"]["src"]
							)
						)
						, $tmp_data_field
					);

					if (!strlen($field_params["alt_url"])) {
						if ($vg_father["properties"]["plugin"]["name"]) {
							//$tmp_data_field["rel"] = ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"]));
							$tmp_data_field["class"]["plugin"] = $vg_father["properties"]["plugin"]["class"];
							$tmp_data_field["jsrequest"] = $vg_father["properties"]["plugin"]["name"];
						}
					}

					if ($vg_father["properties"]["image"]["link_to"] == "image") {
					    $tmp_data_field["href"] = SITE_UPDIR . /*$showfiles_path .*/ $data;
					    /*if ($vg_father["properties"]["plugin"]["name"]) {
							//$tmp_data_field["rel"] = "gallery[" . strtolower(ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"] . basename($vg_father["user_path"])))) . "]";
							$tmp_data_field["class"]["plugin"] = $vg_father["properties"]["plugin"]["class"];
					    }*/
					} elseif ($vg_father["properties"]["image"]["link_to"] == "content") {
					    $tmp_data_field["target"] = $vg_params["target"];
					    $tmp_data_field["href"] = $vg_params["url"];
					    /*if (!strlen($field_params["alt_url"])) {
							if ($vg_father["properties"]["plugin"]["name"]) {
							    //$tmp_data_field["rel"] = ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"]));
							    $tmp_data_field["class"]["plugin"] = $vg_father["properties"]["plugin"]["class"];
							}
					    }*/
					/*} else {
					    $tmp_data_field["class"]["plugin"] = $vg_father["properties"]["plugin"]["class"];*/
					}
			    } else {
					if ($field_params["image"]["src"]) {
					    if ($field_params["enable_lastlevel"] == "2" || (!$field_params["enable_lastlevel"] && $field_params["plugin"]["name"])) {
							if ($field_params["plugin"]["name"]) {
							    //Load JS Plugin
							   /* $tmp_data_field["js_request"] = $field_params["plugin"]["name"];

							    $tmp_data_field["rel"] = $field_params["plugin"]["name"];
							    $tmp_data_field["class"]["plugin"] = $field_params["plugin"]["class"];*/
							} else {
							    $tmp_data_field["target"] = "_blank";
							}

							$tmp_data_field["href"] = SITE_UPDIR . /*$showfiles_path .*/ $data;
					    }

					    if (ffGetFilename($field_params["image"]["src"]["default"]["force_icon"], false) == "gif") {
							if (basename($field_params["image"]["src"]["default"]["force_icon"]) == "spacer.gif") {
							    $tmp_data_field["src"] = true;
							} else {
							    $tmp_data_field["src"] = CM_SHOWFILES . "/" . THEME_INSET . "/images/icons/" . THEME_ICO . "/thumb/" . basename($field_params["image"]["src"]["default"]["force_icon"]);
								$tmp_data_field = get_thumb_size(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/images/icons/" . THEME_ICO . "/thumb/" . basename($field_params["image"]["src"]["default"]["force_icon"]), null, null, null, $tmp_data_field, FF_DISK_PATH);
							}
					    } else {
							$tmp_data_field = get_thumb(
								$data
								, array(
									"base_path" => $base_path
									, "showfiles_path" => $showfiles_path
									, "fake_name" => ""
									, "preserve_orig" => $image_preserve_orig 
									, "thumb" => ($skip_mode
										? null
										: $field_params["image"]["src"]
									)
									, "highlight" => ($skip_mode
										? null
										: $vg_params["highlight"]["image"]["src"]
									)
								)
								, $tmp_data_field
							);					    
							/*
							if ($field_params["settings_type"] > 0 || ($vg_father["highlight_image"] > 0 && $field_params["highlight"] > 0)) {
							    $tmp_data_field = array_replace($tmp_data_field, get_thumb(
									$data
									, array(
									    "base_path" => $base_path
									    , "showfiles_path" => $showfiles_path
									    , "fake_name" => ""
									    , "mode" => ($skip_mode 
										? ""
										: ($vg_father["highlight_image"] > 0 && $field_params["highlight"] > 0 
										    ? $vg_father["highlight_image_name"] 
										    : $field_params["settings_type_name"]
										)
									    )
									    , "format" => $field_params["settings_type_extension"]
									    , "preserve_orig" => $image_preserve_orig 
									    , "width" => ($vg_father["highlight_image"] > 0 && $field_params["highlight"] > 0
										? $vg_father["highlight_image_width"]
										: $field_params["settings_type_width"]
									    )
									    , "height" => ($vg_father["highlight_image"] > 0 && $field_params["highlight"] > 0
										? $vg_father["highlight_image_height"]
										: $field_params["settings_type_height"]
									    )
									)
							    ));
							} else {
							    $tmp_data_field = array_replace($tmp_data_field, get_thumb(
									$data
									, array(
									    "base_path" => $base_path
									    , "showfiles_path" => $showfiles_path
									)
							    ));
							}*/
					    }
					} else {
					   /* if ($field_params["enable_lastlevel"] && $field_params["display_view_mode"]) {
							//Load JS Plugin
							$tmp_data_field["js_request"] = $field_params["display_view_mode"];

							$tmp_data_field["rel"] = $field_params["display_view_mode"];
							$tmp_data_field["class"]["plugin"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $field_params["display_view_mode"]);
					    }*/

						$tmp_data_field = get_thumb(
							$data
							, array(
								"base_path" => $base_path
								, "showfiles_path" => $showfiles_path
								, "fake_name" => ""
								, "preserve_orig" => $image_preserve_orig 
								, "thumb" => null
								, "highlight" => ($skip_mode
									? null
									: $vg_params["highlight"]["image"]["src"]
								)
							)
							, $tmp_data_field
						);	
						/*				    
					    if ($vg_father["highlight_image"] > 0 && $field_params["highlight"] > 0) {
							$tmp_data_field = array_replace($tmp_data_field, get_thumb(
								$data
							    , array(
								"base_path" => $base_path
								, "showfiles_path" => $showfiles_path
								, "fake_name" => ""
								, "mode" => ($skip_mode 
								    ? ""
								    : $vg_father["highlight_image_name"]
								)
								, "format" => $field_params["settings_type_extension"]
								, "preserve_orig" => $image_preserve_orig 
								, "width" => $vg_father["highlight_image_width"]
								, "height" => $vg_father["highlight_image_height"]
							    )
							));
					    } else {
							$tmp_data_field = array_replace($tmp_data_field, get_thumb(
							    $data
							    , array(
								"base_path" => $base_path
								, "showfiles_path" => $showfiles_path
							    )
							));				
					    }*/
					}
			    } 

			    if (!$tmp_data_field["alt"] && $vg_params["meta"]["title"])
					$tmp_data_field["alt"] = trim(ffCommon_url_rewrite_strip_word($vg_params["meta"]["title"], $stopwords, " "));
			    if (!$tmp_data_field["alt"] && $vg_father["seo"]["title"])
					$tmp_data_field["alt"] = trim(ffCommon_url_rewrite_strip_word($vg_father["seo"]["title"], $stopwords, " "));
			    if (!$tmp_data_field["alt"])
			    	$tmp_data_field["alt"] = ffCommon_url_rewrite_strip_word(ffGetFilename($data), $stopwords, " ");

			    if (!$tmp_data_field["title"] && $vg_params["meta"]["description"])
			    	$tmp_data_field["title"] = trim(ffCommon_specialchars($vg_params["meta"]["description"]));
			    if (!$tmp_data_field["title"] && is_array($vg_father["seo"]["meta"]["description"]) && count($vg_father["seo"]["meta"]["description"]))
					$tmp_data_field["title"] = trim(ffCommon_specialchars(implode(" ", $vg_father["seo"]["meta"]["description"])));
				if (!$tmp_data_field["title"])
			    	$tmp_data_field["title"] = ucwords(ffCommon_url_rewrite(ffGetFilename($data), " "));
			    	
			    if (!$tmp_data_field["slug"])
					$tmp_data_field["slug"] = ffCommon_url_rewrite($tmp_data_field["alt"]);

			    if (!isset($tmp_data_field["content"]))
					$tmp_data_field["content"] = $data;
			} else {
			    $tmp_data_field["src"] = true;
			}
	    }
	    break;
	case "textbb":
	case "textck":
	    if (check_function("get_short_description"))
			$res = get_short_description($data, $field_params["limit_char"], $field_params["extended_type"], $vg_params["url"]);

		$data = $res["content"];
		$tmp_data_field["text"] = $res["text"];
		
	    $skip_html_schema = false;
	    if (!isset($tmp_data_field["content"]))
		$tmp_data_field["content"] = $data;

	    $count_p = substr_count($tmp_data_field["content"], '</p>');
	    if (!$count_p)
		$tmp_data_field["htmltag"] = array("tag" => "p");
	    elseif ($count_p == 1)
		$tmp_data_field["htmltag"] = array("tag" => false);

	    /* 			
	      if(isset($tmp_data_field["content"])) {
	      if(substr($tmp_data_field["content"], 0, 3) == "<p>")
	      {
	      if(substr(preg_replace( '/\s+/', '', $tmp_data_field["content"]), -4) == "</p>") {
	      $tmp_data_field["content"] = substr($tmp_data_field["content"], 3, strrpos($tmp_data_field["content"], "</p>") - 3);
	      } else {
	      $skip_html_schema = true;
	      }
	      }
	      } else {
	      if(substr($data, 0, 3) == "<p>")
	      {
	      if(substr(preg_replace( '/\s+/', '', $data), -4) == "</p>") {
	      $tmp_data_field["content"] = substr($data, 3, strrpos($data, "</p>") - 3);
	      } else {
	      $tmp_data_field["content"] = $data;
	      $skip_html_schema = true;
	      }
	      } else {
	      $tmp_data_field["content"] = $data;
	      }
	      }
	      if(!$skip_html_schema)
	      $tmp_data_field["htmltag"] = array("tag" => "p");
	     */
	    break;
	case "link":
		$tmp_link = trim(strtolower($data));
		if (
			substr($tmp_link, 0, 7) == "http://" || substr($tmp_link, 0, 8) == "https://" || substr($tmp_link, 0, 2) == "//"
		) {
		    $tmp_data_field["href"] = $data;
		} elseif (strpos($data, "@") !== false) {
		    $tmp_data_field["href"] = "mailto:" . $data;
		} elseif (substr($tmp_link, 0, 4) == "www.") {
		    $tmp_data_field["href"] = "http://" . $data;
		} elseif (substr($tmp_link, 0, 1) == "#") {
		    $tmp_data_field["href"] = trim($data);
		} else {
		    $tmp_data_field["href"] = $field_params["name"] . ":" . trim($data);
		}

        $first_char_link = substr($tmp_link, 0, 1);
        if($first_char_link != "/" && $first_char_link != "#")
		    $tmp_data_field["target"] = "_blank";

		if (strpos($tmp_link, "www.") !== false) 
			$tmp_data_field["text"] = substr($data, strpos($data, "www.") + 4);
		else if (substr($tmp_link, 0, 7) == "http://" || substr($tmp_link, 0, 8) == "https://" || substr($tmp_link, 0, 2) == "//") 
			$tmp_data_field["text"] = substr($data, strpos($data, "://") + 3);
		else
			$tmp_data_field["text"] = $data;
			
	    if (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/vgallery/" . $vg_father["vgallery_name"] . "/" . $field_params["name"] . "." . THUMB_ICO_EXTENSION)) {
			$tmp_data_field["src"] = FF_SITE_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/vgallery/" . $vg_father["vgallery_name"] . "/" . $field_params["name"] . "." . THUMB_ICO_EXTENSION;
	    } else {
			if ($field_params["enable_label"]) {
			    $tmp_data_field["content"] = $data;
			} else {
			    $tmp_data_field["content"] = $field_params["label_data"];
			}

			$tmp_data_field["slug"] = ffCommon_url_rewrite(strip_tags($tmp_data_field["content"]));
	    }
	    break;
	case "selection":
		$tmp_data_field["text"] = $data;
		$data = ffTemplate::_get_word_by_code($data);	

		$tmp_data_field["content"] = nl2br($data);
	    if (!isset($tmp_data_field["slug"]))
			$tmp_data_field["slug"] = ffCommon_url_rewrite(strip_tags($data));

		break;	    
	case "date":
	case "datecombo":
	case "datecomboage":
	case "datecombobooking":
	case "dateinv":
	    if (is_numeric($data) && $data > 0) {
		$cdata_more_detail = new ffData($data, "Timestamp", FF_SYSTEM_LOCALE);
	    } elseif (strlen($data) && $data != '0000-00-00' && $data != '0000-00-00 00:00:00') {
		$cdata_more_detail = new ffData($data, $field_params["ff_extended_type"], FF_SYSTEM_LOCALE);
	    }
	    if ($cdata_more_detail)
		$data = $cdata_more_detail->getValue($field_params["ff_extended_type"], LANGUAGE_INSET);
	default:
		$tmp_data_field["text"] = $data;
	    if (check_function("transmute_inlink")) {
			if (is_array($vg_father["search"]) && array_key_exists("markable", $vg_father["search"]) && array_key_exists("term", $vg_father["search"])) {
			    $data_transmuted = transmute_inlink($data, strtolower($vg_father["vgallery_name"] . "-" . $field_params["name"]));
			    if ($data == $data_transmuted) {
					$data = preg_replace("/(" . preg_quote($vg_father["search"]["term"]) . ")/i", "<mark>\${1}</mark>", $data);
			    } else {
					$data = $data_transmuted;
			    }
			} else {
			    $data_transmuted = transmute_inlink($data, $vg_father["vgallery_name"] . "-" . $field_params["name"]);
				$data = $data_transmuted;
			}
	    }
	    if (check_function("get_short_description"))
			$res = get_short_description($data, $field_params["limit_char"], $field_params["extended_type"], $vg_params["url"]);
		
		$data = $res["content"];
		if(!$tmp_data_field["text"])
			$tmp_data_field["text"] = $res["text"];
		
	    if (!isset($tmp_data_field["content"]))
			$tmp_data_field["content"] = nl2br($data);
	    if (!isset($tmp_data_field["slug"]))
			$tmp_data_field["slug"] = ffCommon_url_rewrite(strip_tags($data));

	/* 			
	  if(!isset($tmp_data_field["htmltag"])) {
	  $tmp_data_field["htmltag"] = array("tag" => (global_settings("ENABLE_VGALLERY_HTMLTAG_AUTOMATIC") && $field_params["smart_url"]
	  ? "h" . ($field_params["smart_url"] + $vg_father["enable_title"] + ($vg_father["mode"] == "thumb"
	  ? ($vg_father["type"] == "publishing"
	  ? 2
	  : 1
	  )
	  : 0
	  )
	  )
	  : "span"
	  )
	  , "attr" => array(
	  "class" => array(
	  "default" => ffCommon_url_rewrite($field_params["name"])
	  , "vcard" => ($vg_father["mode"] != "thumb" && $field_params["smart_url"]
	  ? "entry-title"
	  : ""
	  )
	  )
	  )
	  );
	  }
	 */
    }

    return $tmp_data_field;
}

function convert_vgallery_field_data($data, $from, $to) {
	$ffData = new ffData($data, $from);

	$locale = (strpos($to, "ISO") === false
				? FF_LOCALE 
				: FF_SYSTEM_LOCALE
			);
	switch($to) {
		case "Date":
		case "DateISO":
			$res = $ffData->getValue("Date", $locale);
			break;
		case "DateDD":
			$res = $ffData->value_date_day;
			break;
		case "DateMM":
			$res = $ffData->value_date_month;
			break;
		case "DateYYYY":
			$res = $ffData->value_date_year;
			break;
		case "Time":
		case "TimeISO":
			$res = $ffData->getValue("Time", $locale);
			break;
		case "TimeHH":
			$res = $ffData->value_date_hours;
			break;
		case "TimeMM":
			$res = $ffData->value_date_minutes;
			break;
		case "DateTime":
		case "DateTimeISO":
			$res = $ffData->getValue("DateTime", $locale);
			break;
		default:
	}
	
	return $res;
}

function parse_vgallery_tpl_custom_vars(&$tpl, $vars, $id = null, $class = null, $properties = null)
{
    static $class_reset                                                                     = array();
    static $properties_reset                                                                = array();

    if($properties)
    {
        $str_properties = "";
        if(is_array($properties_reset) && count($properties_reset)) {
            foreach($properties_reset AS $properties_reset_key) {
                $tpl->set_var("properties:" . $properties_reset_key, "");
            }
        }

        if(is_array($properties) && count($properties)) {
            foreach($properties AS $item_properties_key => $item_properties_value) {
                $str_properties                                                     .= " " . $item_properties_value;
                if($tpl->isset_var("properties:" . $item_properties_key)) {
                    $tpl->set_var("properties:" . $item_properties_key              , $item_properties_value);

                    $properties_reset[]                                             = $item_properties_key;
                }
            }
        }
        $str_properties                                                             = trim($str_properties);
        $tpl->set_var("properties"                                                  , $str_properties);
        $tpl->set_var("item:properties"										        , $str_properties);
    }
    if($class)
    {
        $str_class = "";
        if(is_array($class_reset) && count($class_reset)) {
            foreach($class_reset AS $class_reset_key) {
                $tpl->set_var("class:" . $class_reset_key, "");
            }
        }

        if(is_array($class) && count($class)) {
            foreach($class AS $item_class_key => $item_class_value) {
                $str_class                                                          .= " " . $item_class_value;
                if($tpl->isset_var("class:" . $item_class_key)) {
                    $tpl->set_var("class:" . $item_class_key                        , $item_class_value);

                    $class_reset[]                                                  = $item_class_key;
                }
            }
        }
        $str_class                                                                  = trim($str_class);
        $tpl->set_var("class"                                                       , 'class="' . $str_class . '"');
        $tpl->set_var("item:class"													, $str_class);
    }

    $tpl->set_var("id"                                                              , ffCommon_url_rewrite($id));

	$tpl->set_var("item:permalink"													, $vars["permalink"]);
	$tpl->set_var("item:slug"														, $vars["smart_url"]);
	$tpl->set_var("item:title"														, $vars["title"]);
	$tpl->set_var("item:description"												, $vars["description"]);
	$tpl->set_var("item:h1"															, $vars["header_title"]);
	$tpl->set_var("item:keywords"													, $vars["keywords"]);
	$tpl->set_var("item:tags"														, $vars["tags"]);
	$tpl->set_var("item:parent"														, $vars["parent"]);
	$tpl->set_var("item:parentname"													, $vars["parent_title"]);
	$tpl->set_var("item:parentslug"													, $vars["parent_smart_url"]);
	
	//created
	$tpl->set_var("item:created"													, $vars["created"]);
	if($tpl->isset_var("item:created:date"))
	    $tpl->set_var("item:created:date"											, convert_vgallery_field_data($vars["created"], "Timestamp", "Date"));
	if($tpl->isset_var("item:created:time"))
	    $tpl->set_var("item:created:time"											, convert_vgallery_field_data($vars["created"], "Timestamp", "Time"));
	if($tpl->isset_var("item:created:datetime"))
	    $tpl->set_var("item:created:datetime"										, convert_vgallery_field_data($vars["created"], "Timestamp", "DateTime"));

	if($tpl->isset_var("item:created:date:iso"))
	    $tpl->set_var("item:created:date:iso"										, convert_vgallery_field_data($vars["created"], "Timestamp", "DateISO"));
	if($tpl->isset_var("item:created:time:iso"))
	    $tpl->set_var("item:created:time:iso"										, convert_vgallery_field_data($vars["created"], "Timestamp", "TimeISO"));
	if($tpl->isset_var("item:created:datetime:iso"))
	    $tpl->set_var("item:created:datetime:iso"									, convert_vgallery_field_data($vars["created"], "Timestamp", "DateTimeISO"));

	if($tpl->isset_var("item:created:date:dd"))
	    $tpl->set_var("item:created:date:dd"										, convert_vgallery_field_data($vars["created"], "Timestamp", "DateDay"));
	if($tpl->isset_var("item:created:date:mm"))
	    $tpl->set_var("item:created:date:mm"										, convert_vgallery_field_data($vars["created"], "Timestamp", "DateMonth"));
	if($tpl->isset_var("item:created:date:yyyy"))
	    $tpl->set_var("item:created:date:yyyy"										, convert_vgallery_field_data($vars["created"], "Timestamp", "DateYear"));
	if($tpl->isset_var("item:created:time:hh"))
	    $tpl->set_var("item:created:time:hh"										, convert_vgallery_field_data($vars["created"], "Timestamp", "TimeHour"));
	if($tpl->isset_var("item:created:time:mm"))
	    $tpl->set_var("item:created:time:mm"										, convert_vgallery_field_data($vars["created"], "Timestamp", "TimeMinute"));
	    
	//lastupdate	
	$tpl->set_var("item:lastupdate"													, $vars["lastupdate"]);
	if($tpl->isset_var("item:lastupdate:date"))
	    $tpl->set_var("item:lastupdate:date"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "Date"));
	if($tpl->isset_var("item:lastupdate:time"))
	    $tpl->set_var("item:lastupdate:time"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "Time"));
	if($tpl->isset_var("item:lastupdate:datetime"))
	    $tpl->set_var("item:lastupdate:datetime"									, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateTime"));

	if($tpl->isset_var("item:lastupdate:date:dd"))
	    $tpl->set_var("item:lastupdate:date:dd"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateDay"));
	if($tpl->isset_var("item:lastupdate:date:mm"))
	    $tpl->set_var("item:lastupdate:date:mm"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateMonth"));
	if($tpl->isset_var("item:lastupdate:date:yyyy"))
	    $tpl->set_var("item:lastupdate:date:yyyy"									, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateYear"));
	if($tpl->isset_var("item:lastupdate:time:hh"))
	    $tpl->set_var("item:lastupdate:time:hh"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "TimeHour"));
	if($tpl->isset_var("item:lastupdate:time:mm"))
	    $tpl->set_var("item:lastupdate:time:mm"										, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "TimeMinute"));

	if($tpl->isset_var("item:lastupdate:date:iso"))
	    $tpl->set_var("item:lastupdate:date:iso"									, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateISO"));
	if($tpl->isset_var("item:lastupdate:time:iso"))
	    $tpl->set_var("item:lastupdate:time:iso"									, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "TimeISO"));
	if($tpl->isset_var("item:lastupdate:datetime:iso"))
	    $tpl->set_var("item:lastupdate:datetime:iso"								, convert_vgallery_field_data($vars["lastupdate"], "Timestamp", "DateTimeISO"));

	//published
	$tpl->set_var("item:published"													, $vars["published"]);
	if($tpl->isset_var("item:published:date"))
	    $tpl->set_var("item:published:date"											, convert_vgallery_field_data($vars["published"], "Timestamp", "Date"));
	if($tpl->isset_var("item:published:time"))
	    $tpl->set_var("item:published:time"											, convert_vgallery_field_data($vars["published"], "Timestamp", "Time"));
	if($tpl->isset_var("item:published:datetime"))
	    $tpl->set_var("item:published:datetime"										, convert_vgallery_field_data($vars["published"], "Timestamp", "DateTime"));

	if($tpl->isset_var("item:published:date:dd"))
	    $tpl->set_var("item:published:date:dd"										, convert_vgallery_field_data($vars["published"], "Timestamp", "DateDay"));
	if($tpl->isset_var("item:published:date:mm"))
	    $tpl->set_var("item:published:date:mm"										, convert_vgallery_field_data($vars["published"], "Timestamp", "DateMonth"));
	if($tpl->isset_var("item:published:date:yyyy"))
	    $tpl->set_var("item:published:date:yyyy"									, convert_vgallery_field_data($vars["published"], "Timestamp", "DateYear"));
	if($tpl->isset_var("item:published:time:hh"))
	    $tpl->set_var("item:published:time:hh"										, convert_vgallery_field_data($vars["published"], "Timestamp", "TimeHour"));
	if($tpl->isset_var("item:published:time:mm"))
	    $tpl->set_var("item:published:time:mm"										, convert_vgallery_field_data($vars["published"], "Timestamp", "TimeMinute"));
	    
	if($tpl->isset_var("item:published:date:iso"))
	    $tpl->set_var("item:published:date:iso"										, convert_vgallery_field_data($vars["published"], "Timestamp", "DateISO"));
	if($tpl->isset_var("item:published:time:iso"))
	    $tpl->set_var("item:published:time:iso"										, convert_vgallery_field_data($vars["published"], "Timestamp", "TimeISO"));
	if($tpl->isset_var("item:published:datetime:iso"))
	    $tpl->set_var("item:published:datetime:iso"									, convert_vgallery_field_data($vars["published"], "Timestamp", "DateTimeISO"));

	$tpl->set_var("item:owner"														, $vars["owner"]);
	$tpl->set_var("item:type"														, $vars["type"]);
	
	$tpl->set_var("item:url"														, $vars["url"]);
	$tpl->set_var("item:target"														, $vars["target"]);
	
	$tpl->set_var("item:cover:content"												, $vars["cover"]["src"]["default"]["src"]);
	if($tpl->isset_var("item:cover")) {
		if(count($vars["cover"]["src"]) > 1) {
			$tpl->set_var("item:cover"												, get_thumb_by_media_queries(null, $vars["cover"]["src"]));
		} else {
			$tpl->set_var("item:cover"												, '<img src="' . $vars["cover"]["src"]["default"]["src"] . '" width="' . $vars["cover"]["src"]["default"]["width"] . '" height="' . $vars["cover"]["src"]["default"]["height"] . '" />');
		}
	}	                    

	$tpl->set_var("item:placeholder:content"										, $vars["noimg"]["src"]["default"]["src"]);
	if($tpl->isset_var("item:placeholder")) {
		if(count($vars["noimg"]["src"]) > 1) {
			$tpl->set_var("item:placeholder"										, get_thumb_by_media_queries(null, $vars["noimg"]["src"]));
		} else {
			$tpl->set_var("item:placeholder"										, '<img src="' . $vars["noimg"]["src"]["default"]["src"] . '" width="' . $vars["noimg"]["src"]["default"]["width"] . '" height="' . $vars["noimg"]["src"]["default"]["height"] . '" />');
		}
	}	

	
	
}