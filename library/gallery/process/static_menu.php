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
function process_static_menu($settings_path, $user_path, $search_param = null, &$layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    //$settings_path = $globals->settings_path;
    check_function("normalize_url");

	$block = array();

    $db = ffDB_Sql::factory();

    $user_permission = get_session("user_permission");
    
    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
	$follow_framework = false;
	$advanced_setting = array();

    if(strlen($layout_settings["AREA_STATIC_PLUGIN"]))
	    setJsRequest($layout_settings["AREA_STATIC_PLUGIN"]);

    //if(!strlen($user_path))
     //   $settings_path = "/";
        
	//$tmp_user_path = $settings_path;

    //$show_home = $layout_settings["AREA_STATIC_SHOW_HOME"];

    if(check_function("get_grid_system_params"))
    	$menu_params = get_grid_system_menu($layout["template"], $layout_settings["AREA_STATIC_MENU_FOLLOW_FRAMEWORK_CSS"]);
    
	$tpl_data["custom"] = "menu.html";
	$tpl_data["base"] = $menu_params["tpl_name"];
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
	
	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   
	
    if ($layout_settings["AREA_STATIC_SHOW_TITLE"]) {
        $tpl->set_var("title" , ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id)));
        $tpl->parse("SezTitle", false);
    } else
        $tpl->set_var("SezTitle", "");

	$menu_item = array();
	$menu_special = array();
	$menu_key = array();
	$menu_vgallery_key = array();
    $sSQL_Where = "";

    if($search_param) {
        $sSQL = "SELECT DISTINCT
        				static_pages.ID
						, static_pages.use_ajax
						, static_pages.ajax_on_event
						" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
							? "
								, static_pages.permalink						AS permalink
								, static_pages.keywords							AS keywords
								, static_pages.meta_description					AS meta_description
								, static_pages.meta_title						AS meta_title
								, static_pages.meta_title_alt					AS meta_title_alt
								, static_pages.`parent`							AS permalink_parent
								, static_pages.name								AS smart_url
								, static_pages.alt_url							AS alt_url
								, static_pages.visible 							AS `visible`
							"
							: "
								, static_pages_rel_languages.permalink			AS permalink
								, static_pages_rel_languages.keywords			AS keywords
								, static_pages_rel_languages.meta_description	AS meta_description
								, static_pages_rel_languages.meta_title			AS meta_title
								, static_pages_rel_languages.meta_title_alt		AS meta_title_alt
								, static_pages_rel_languages.permalink_parent	AS permalink_parent
								, static_pages_rel_languages.smart_url			AS smart_url
								, static_pages_rel_languages.alt_url			AS alt_url
								, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
									? " static_pages_rel_languages.visible "
									: " static_pages.visible "
								) . "											AS `visible`
							"
						) . "
    					, static_pages.name
    					, static_pages.parent
    					, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
                        , static_pages.sort
                        , static_pages.owner
                 FROM static_pages
				    " . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
				        ? ""
				        : " INNER JOIN static_pages_rel_languages ON static_pages.ID = static_pages_rel_languages.ID_static_pages 
                				AND static_pages_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
				    ) . "
				    LEFT JOIN drafts_rel_languages ON static_pages.ID_drafts = drafts_rel_languages.ID_drafts 
				    	AND drafts_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID
                 WHERE 
                    (
						" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
							? "
		                        static_pages.meta_title LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
		                        OR static_pages.meta_title_alt LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
		                        OR static_pages.meta_description LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
							"
							: "
		                        static_pages_rel_languages.meta_title LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
		                        OR static_pages_rel_languages.meta_title_alt LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
		                        OR static_pages_rel_languages.meta_description LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
							"
						) . "
                        OR drafts_rel_languages.title LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
                        OR drafts_rel_languages.value LIKE '%" . $db->toSql($search_param, "Text", false) . "%'
                    )
                    AND static_pages.name <> '' 
					" . (!ENABLE_STD_PERMISSION && !ENABLE_ADV_PERMISSION
					    	? " AND (static_pages.permission = '' OR FIND_IN_SET(" . $db->toSql($user_permission["primary_gid"]) . ", static_pages.permission))"
					    	: ""
					)                    
					. (ENABLE_STD_PERMISSION 
				        ? ""
				        : (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION
				            ? " AND static_pages_rel_languages.visible > 0 " 
				            : " AND static_pages.visible > 0 "
				        )
				    ) . "                     
                    AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
                 ORDER BY parent, `sort`, name";
        $db->query($sSQL);
    } else {
		do {
			$sSQL = "
					SELECT 
						static_pages.ID
						, static_pages.use_ajax
						, static_pages.ajax_on_event
						" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
							? "
								, static_pages.permalink						AS permalink
								, static_pages.keywords							AS keywords
								, static_pages.meta_description					AS meta_description
								, static_pages.meta_title						AS meta_title
								, static_pages.meta_title_alt					AS meta_title_alt
								, static_pages.`parent`							AS permalink_parent
								, static_pages.name								AS smart_url
								, static_pages.alt_url							AS alt_url
								, static_pages.visible 							AS `visible`
							"
							: "
								, static_pages_rel_languages.permalink			AS permalink
								, static_pages_rel_languages.keywords			AS keywords
								, static_pages_rel_languages.meta_description	AS meta_description
								, static_pages_rel_languages.meta_title			AS meta_title
								, static_pages_rel_languages.meta_title_alt		AS meta_title_alt
								, static_pages_rel_languages.permalink_parent	AS permalink_parent
								, static_pages_rel_languages.smart_url			AS smart_url
								, static_pages_rel_languages.alt_url			AS alt_url
								, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
									? " static_pages_rel_languages.visible "
									: " static_pages.visible "
								) . "											AS `visible`
							"
						) . "
    					, static_pages.name
    					, static_pages.parent
    					, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
                        , static_pages.sort
                        , static_pages.owner
			         FROM static_pages
			         	" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
			         		? ""
			         		: " INNER JOIN static_pages_rel_languages ON static_pages.ID = static_pages_rel_languages.ID_static_pages 
                					AND static_pages_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			         	) . "
			         WHERE
					    static_pages.parent LIKE '" . $db->toSql($settings_path, "Text", false) . "%'
					    AND FIND_IN_SET(" . $db->toSql($layout["location"]) . ", static_pages.location)
					    " . (!ENABLE_STD_PERMISSION && !ENABLE_ADV_PERMISSION
					    	? " AND (static_pages.permission = '' OR FIND_IN_SET(" . $db->toSql($user_permission["primary_gid"]) . ", static_pages.permission))"
					    	: ""
						)
					    . (ENABLE_STD_PERMISSION 
				            ? ""
				            : (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION
				                ? " AND static_pages_rel_languages.visible > 0 " 
				                : " AND static_pages.visible > 0 "
				            )
				        ) . " 
						AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "			
                     ORDER BY parent, `sort`, name";

/*
			if($show_home) {
				$sSQL = "
						SELECT *
                        FROM (
                            (
							    SELECT 
								    static_pages.ID
									, static_pages.use_ajax
									, static_pages.ajax_on_event
								    , (" . ($settings_path == "/"
								    	? "''"
								    	: " static_pages_rel_languages.smart_url "
								    ) . ") AS smart_url
								    , static_pages_rel_languages.permalink_parent
    							    , static_pages_rel_languages.title
    							    , static_pages_rel_languages.description
    							    , static_pages_rel_languages.alt_url
			                        , static_pages_rel_languages.meta_title
			                        , static_pages_rel_languages.meta_title_alt
			                        , static_pages_rel_languages.meta_description
			                        , static_pages_rel_languages.keywords
    							    , static_pages.name
    							    , static_pages.parent
    							    , CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
                                    , static_pages.sort
                                    , static_pages.owner
                                    , static_pages_rel_languages.visible AS visible
					             FROM static_pages
					                LEFT JOIN static_pages_rel_languages ON static_pages.ID = static_pages_rel_languages.ID_static_pages 
                						AND static_pages_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "  
					             WHERE
					                static_pages.parent = " . $db->toSql(ffCommon_dirname($settings_path), "Text") . "
					                AND static_pages.name = " . $db->toSql(basename($settings_path), "Text") . "
								    AND static_pages_rel_languages.visible <> " . $db->toSql("0", "Number") . "
								    AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
						    ) UNION (
							    " . $sSQL . "
						    )
                        ) AS tbl_src
                        ORDER BY tbl_src.parent, tbl_src.`sort`, tbl_src.name";
			}*/
            $db->query($sSQL); 
		} while(!$layout_settings["AREA_STATIC_SHOW_ONLYHOME"] && $db->numRows() <= 0 && $settings_path != ffCommon_dirname($settings_path) && $settings_path = ffCommon_dirname($settings_path));
    }

  	if ($db->nextRecord()) {
		$arrHomeFixItem = array();
		if(strlen($layout_settings["AREA_STATIC_HOME_FIX_ITEM"])) {
			$arrHomeFixItem = explode(",", $layout_settings["AREA_STATIC_HOME_FIX_ITEM"]);
		}

		do {
			$fullpath = $db->getField("full_path", "Text", true);
			if(
                count($arrHomeFixItem) 
                &&  
                (
                    (
                        $db->getField("parent", "Text", true) == $settings_path 
                        && array_search($db->getField("name", "Text", true), $arrHomeFixItem) === false
                    ) 
                    || 
                    (
                        array_key_exists($db->getField("parent", "Text", true), $menu_special)
                    )
                )
            ) {
				$menu_special[$fullpath]["ID"] 							= $db->getField("ID", "Number", true);
				$menu_special[$fullpath]["parent"] 						= $db->getField("parent", "Text", true);
				$menu_special[$fullpath]["name"] 						= $db->getField("name", "Text", true);

                $menu_special[$fullpath]["meta_title"]                	= $db->getField("meta_title", "Text", true);  
                $menu_special[$fullpath]["meta_title_alt"]              = $db->getField("meta_title_alt", "Text", true);  
                $menu_special[$fullpath]["meta_description"]            = $db->getField("meta_description", "Text", true);  
                $menu_special[$fullpath]["keywords"]                	= $db->getField("keywords", "Text", true);  
            
                $menu_special[$fullpath]["smart_url"]              		= ($menu_special[$fullpath]["name"] && $fullpath != "/home"
                    														? $db->getField("smart_url", "Text", true)
                    														: ""
                    													);
                $menu_special[$fullpath]["title"]                  		= ($menu_special[$fullpath]["meta_title_alt"]
                    														? $menu_special[$fullpath]["meta_title_alt"]
                    														: $menu_special[$fullpath]["meta_title"]
                    													);  
				$menu_special[$fullpath]["description"] 				= $menu_special[$fullpath]["meta_description"];
                $menu_special[$fullpath]["permalink_parent"]       		= $db->getField("permalink_parent", "Text", true);  
                $menu_special[$fullpath]["alt_url"]                		= $db->getField("alt_url", "Text", true);  

				$menu_special[$fullpath]["cover"] 						= "";
				$menu_special[$fullpath]["owner"] 						= $db->getField("owner", "Number", true);
				$menu_special[$fullpath]["visible"] 					= $db->getField("visible", "Number", true);
				$menu_special[$fullpath]["ajax"] 						= $db->getField("use_ajax", "Number", true);
				$menu_special[$fullpath]["ajax_on_event"] 				= $db->getField("ajax_on_event", "Text", true);
				$menu_special[$fullpath]["type"] 						= "static_pages";
				
				if(!$menu_special[$fullpath]["permalink_parent"])
					$menu_special[$fullpath]["permalink_parent"] 		= $menu_special[$fullpath]["parent"];

				$menu_key[] = $menu_special[$fullpath]["ID"];
			} else {
				$menu_item[$fullpath]["ID"] 							= $db->getField("ID", "Number", true);
				$menu_item[$fullpath]["parent"] 						= $db->getField("parent", "Text", true);
				$menu_item[$fullpath]["name"] 							= $db->getField("name", "Text", true);

                $menu_item[$fullpath]["meta_title"]                		= $db->getField("meta_title", "Text", true);  
                $menu_item[$fullpath]["meta_title_alt"]                	= $db->getField("meta_title_alt", "Text", true);  
                $menu_item[$fullpath]["meta_description"]               = $db->getField("meta_description", "Text", true);  
                $menu_item[$fullpath]["keywords"]                		= $db->getField("keywords", "Text", true);  

                $menu_item[$fullpath]["smart_url"]              		= ($menu_item[$fullpath]["name"] && $fullpath != "/home"
                    														? $db->getField("smart_url", "Text", true)
                    														: ""
                    													);
                $menu_item[$fullpath]["title"]                  		= ($menu_item[$fullpath]["meta_title_alt"]
                    														? $menu_item[$fullpath]["meta_title_alt"]
                    														: $menu_item[$fullpath]["meta_title"]
                    													);  
				$menu_item[$fullpath]["description"] 					= $menu_item[$fullpath]["meta_description"];
                $menu_item[$fullpath]["permalink_parent"]       		= $db->getField("permalink_parent", "Text", true);  
                $menu_item[$fullpath]["alt_url"]                		= $db->getField("alt_url", "Text", true);  

				$menu_item[$fullpath]["cover"] 							= "";
				$menu_item[$fullpath]["owner"]							= $db->getField("owner", "Number", true);
                $menu_item[$fullpath]["visible"] 						= $db->getField("visible", "Number", true);
                $menu_item[$fullpath]["ajax"] 							= $db->getField("use_ajax", "Number", true);
				$menu_item[$fullpath]["ajax_on_event"] 					= $db->getField("ajax_on_event", "Text", true);
				$menu_item[$fullpath]["type"] 							= "static_pages";

				if(!$menu_item[$fullpath]["permalink_parent"])
					$menu_item[$fullpath]["permalink_parent"] 			= $menu_item[$fullpath]["parent"];
				
                $menu_key[] = $menu_item[$fullpath]["ID"];
				
				if($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"] == "/") {
	                if(strlen($sSQL_Where))
                		$sSQL_Where .= " OR ";
					if(ffCommon_dirname($fullpath) == "/") {
						$sSQL_Where .= "
							(vgallery_nodes.parent = " . $db->toSql($fullpath) . " 
								OR vgallery_nodes.parent LIKE '" . $db->toSql($fullpath, "Text", false) . "%'
							)";
					} else {
						$sSQL_Where .= "
								(vgallery_nodes.parent = " . $db->toSql(ffCommon_dirname($fullpath)) . " 
                					OR vgallery_nodes.parent LIKE '" . $db->toSql(ffCommon_dirname($fullpath), "Text", false) . "/%'
                				)
		                    ";
					}
				}                
			}
		} while($db->nextRecord());
	}

	if($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"]) {
		if(!strlen($sSQL_Where)) {
			/*if(ffCommon_dirname($settings_path) == "/")
				$source_user_path = stripslash($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"]) . $settings_path;
			else
				$source_user_path = stripslash($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"]) . ffCommon_dirname($settings_path);
			*/
			$source_user_path = stripslash($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"]);
			$sSQL_Where .= "
				(vgallery_nodes.parent = " . $db->toSql($source_user_path) . " 
					OR vgallery_nodes.parent LIKE '" . $db->toSql($source_user_path, "Text", false) . "%'
				)";

		}  

		$sSQL = "SELECT DISTINCT 
                    vgallery.ID AS ID_vgallery
                    , vgallery_type.name AS type
                    , vgallery_nodes.*
	                " . (OLD_VGALLERY
                        ? "
                            , (SELECT GROUP_CONCAT(CONCAT(vgallery_fields.name, ':::', vgallery_rel_nodes_fields.description) ORDER BY vgallery_fields.name SEPARATOR '|@|')
                                    FROM  vgallery_rel_nodes_fields
                                        INNER JOIN  vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                    WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                        AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                        AND  vgallery_fields.name IN('alt_url', 'meta_title', 'smart_url', 'permalink_parent')
                                    ORDER BY vgallery_fields.name
                            )                                                       AS `meta` 
                        "
                        : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                            ? "
                                , vgallery_nodes.permalink                          AS permalink
                                , vgallery_nodes.keywords                           AS keywords
                                , vgallery_nodes.meta_description                   AS meta_description
                                , vgallery_nodes.meta_title                         AS meta_title
                                , vgallery_nodes.meta_title_alt                     AS meta_title_alt
                                , vgallery_nodes.`parent`                           AS permalink_parent
                                , vgallery_nodes.name                               AS smart_url
                                , vgallery_nodes.alt_url                            AS alt_url
                                , vgallery_nodes.visible 							AS `visible`
                            "
                            : "
                                , vgallery_nodes_rel_languages.permalink            AS permalink
                                , vgallery_nodes_rel_languages.keywords             AS keywords
                                , vgallery_nodes_rel_languages.meta_description     AS meta_description
                                , vgallery_nodes_rel_languages.meta_title           AS meta_title
                                , vgallery_nodes_rel_languages.meta_title_alt       AS meta_title_alt
                                , vgallery_nodes_rel_languages.permalink_parent     AS permalink_parent
                                , vgallery_nodes_rel_languages.smart_url            AS smart_url
                                , vgallery_nodes_rel_languages.alt_url              AS alt_url
								, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
									? " vgallery_nodes_rel_languages.visible "
									: " vgallery_nodes.visible "
								) . "												AS `visible`
                            "
                        )
                    ) . "
                    , (" . ($layout_settings["AREA_VGALLERY_SHOW_COVER"] //da togliere extras e far popolare la cover con l'id del field
                    		? "(
								SELECT GROUP_CONCAT(DISTINCT vgallery_rel_nodes_fields.description ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' ') AS name
							    FROM vgallery_rel_nodes_fields
		                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
							    WHERE 
									vgallery_rel_nodes_fields.ID_fields IN ( SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.settings_type = 'extras' )
							        AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
									AND vgallery_rel_nodes_fields.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "                    		
                    		)"
                    		: "''"
                    	) . "
                    ) AS cover
                    , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                FROM vgallery_nodes
                    " . (OLD_VGALLERY
                        ? "LEFT JOIN vgallery_rel_nodes_fields
	                        ON (
	                            vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
	                            AND vgallery_rel_nodes_fields.ID_fields = ( SELECT sort_default FROM vgallery_type WHERE vgallery_type.ID = vgallery_nodes.ID_type)
	                            AND vgallery_rel_nodes_fields.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
	                        )"
                        : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                            ? ""
                            : " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
                                    AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
                        )
                    ) . "
                    INNER JOIN vgallery_type ON vgallery_nodes.ID_type = vgallery_type.ID
                    INNER JOIN vgallery ON vgallery_nodes.ID_vgallery = vgallery.ID
                WHERE 1 " . ($sSQL_Where
                		? " AND ($sSQL_Where)"
                		: ""
                	) . "
                    AND vgallery_nodes.name <> ''
                    AND vgallery_nodes.is_dir > 0
					" . (ENABLE_STD_PERMISSION 
				        ? ""
				        : (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION && !OLD_VGALLERY
				            ? " AND vgallery_nodes_rel_languages.visible > 0 " 
				            : " AND vgallery_nodes.visible > 0 "
				        )
				    ) . " 
                    ORDER BY (LENGTH(full_path) - LENGTH(REPLACE(full_path, '/', '')))
                    	, vgallery_nodes.`order`
                    	, vgallery_nodes.name ";
        $db->query($sSQL);
        
		if ($db->nextRecord()) {
			do {
				$fullpath = substr($db->getField("full_path", "Text", true), strlen(stripslash($layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"])));
				//$fullpath = $db->getField("full_path", "Text", true);
				$static_parent = $layout_settings["AREA_STATIC_DESCEND_INTO_VGALLERY"];
                if(OLD_VGALLERY) {
                    $meta = $db->getField("meta", "Text", true);            
                    if(strlen($meta)) {
                         $arrMeta = explode("|@|", $meta);
                        if(is_array($arrMeta) && count($arrMeta)) {
                            foreach($arrMeta AS $arrMeta_value) {
                                $tmpMeta = explode(":::", $arrMeta_value);
                                
                                switch($tmpMeta[0]) {
                                    case "smart_url":
                                        $menu_vgallery[$static_parent][$fullpath]["smart_url"] = $tmpMeta[1];
                                        break;
                                    case "meta_title":
                                        $menu_vgallery[$static_parent][$fullpath]["title"] = $tmpMeta[1];
                                        break;
                                    case "meta_description":
	                                    $menu_vgallery[$static_parent][$fullpath]["description"] = $tmpMeta[1];
                                        break;
                                    case "permalink_parent":
                                        $menu_vgallery[$static_parent][$fullpath]["permalink_parent"] = $tmpMeta[1];
                                        break;
                                    case "alt_url":
                                        $menu_vgallery[$static_parent][$fullpath]["alt_url"] = $tmpMeta[1];
                                        break;
                                }
                            }
                        }
                    }            
                } else {
	                $menu_vgallery[$static_parent][$fullpath]["meta_title"]             = $db->getField("meta_title", "Text", true);  
	                $menu_vgallery[$static_parent][$fullpath]["meta_title_alt"]         = $db->getField("meta_title_alt", "Text", true);  
	                $menu_vgallery[$static_parent][$fullpath]["meta_description"]       = $db->getField("meta_description", "Text", true);  
	                $menu_vgallery[$static_parent][$fullpath]["keywords"]               = $db->getField("keywords", "Text", true);  
                
                    $menu_vgallery[$static_parent][$fullpath]["smart_url"]              = $db->getField("smart_url", "Text", true);  
                    $menu_vgallery[$static_parent][$fullpath]["title"]                  = ($menu_vgallery[$static_parent][$fullpath]["meta_title_alt"]
                    																		? $menu_vgallery[$static_parent][$fullpath]["meta_title_alt"]
                    																		: $menu_vgallery[$static_parent][$fullpath]["meta_title"]
                    																	);  
					$menu_vgallery[$static_parent][$fullpath]["description"] 			= $menu_vgallery[$static_parent][$fullpath]["meta_description"];
                    $menu_vgallery[$static_parent][$fullpath]["permalink_parent"]       = $db->getField("permalink_parent", "Text", true);  
                    $menu_vgallery[$static_parent][$fullpath]["alt_url"]                = $db->getField("alt_url", "Text", true);  
                }
				
				$menu_vgallery[$static_parent][$fullpath]["ID"] 						= $db->getField("ID", "Number", true);
				$menu_vgallery[$static_parent][$fullpath]["parent"] 					= $db->getField("parent", "Text", true);
				$menu_vgallery[$static_parent][$fullpath]["name"] 						= $db->getField("name", "Text", true);
				$menu_vgallery[$static_parent][$fullpath]["cover"] 						= $db->getField("cover", "Text", true);
				$menu_vgallery[$static_parent][$fullpath]["owner"]						= $db->getField("owner", "Number", true);
	            $menu_vgallery[$static_parent][$fullpath]["visible"] 					= $db->getField("visible", "Number", true);
	            $menu_vgallery[$static_parent][$fullpath]["ajax"] 						= $db->getField("use_ajax", "Number", true);
				$menu_vgallery[$static_parent][$fullpath]["ajax_on_event"] 				= $db->getField("ajax_on_event", "Text", true);
				$menu_vgallery[$static_parent][$fullpath]["type"] 						= "vgallery";
				
				if(!$menu_vgallery[$static_parent][$fullpath]["permalink_parent"])
					$menu_vgallery[$static_parent][$fullpath]["permalink_parent"] 		= $menu_vgallery[$static_parent][$fullpath]["parent"];
				if(!$menu_vgallery[$static_parent][$fullpath]["smart_url"])
					$menu_vgallery[$static_parent][$fullpath]["smart_url"] 				= $menu_vgallery[$static_parent][$fullpath]["name"];

	            $menu_vgallery_key[] 													= $menu_vgallery[$static_parent][$fullpath]["ID"];
			} while($db->nextRecord());
			
			
			if(is_array($menu_vgallery) && count($menu_vgallery)) {
				foreach($menu_vgallery AS $static_parent => $menu) {
					if(array_key_exists($static_parent, $menu_item))
						array_splice_assoc($menu_item, $static_parent, 1, $menu);
				}
			}
		}
	}	

	if(!$search_param && !isset($globals->seo["page"])) {
		if(array_key_exists($globals->settings_path, $menu_item)) {
			$arrCurrentPage = $menu_item[$globals->settings_path];
		} elseif(array_key_exists($globals->settings_path, $menu_special)) {
			$arrCurrentPage = $menu_special[$globals->settings_path];
		}

		if(is_array($arrCurrentPage)) {
			$globals->seo["page"]["ID"] = $arrCurrentPage["ID"];
			$globals->seo["page"]["title"] = $arrCurrentPage["meta_title"];
			$globals->seo["page"]["title_header"] = $arrCurrentPage["meta_title_ori"];
			if(strlen($arrCurrentPage["meta_description"]))
				$globals->seo["page"]["meta"]["description"][] = $arrCurrentPage["meta_description"];
			if(strlen($arrCurrentPage["keywords"]))
				$globals->seo["page"]["meta"]["keywords"][] = $arrCurrentPage["keywords"];
		}
	}	
	
	$is_owner = false;
	if(is_array($menu_item) && count($menu_item)) {
		$part_item = array();

		if($settings_path == "/") 
			$compare_path = $settings_path;
		else
			$compare_path = $settings_path . "/";

		if(ENABLE_STD_PERMISSION && check_function("get_file_permission")) {
			if(is_array($menu_key) && count($menu_key))
				get_file_permission(null, "static_pages", $menu_key);
			if(is_array($menu_vgallery_key) && count($menu_vgallery_key))
				get_file_permission(null, "vgallery", $menu_vgallery_key);

			foreach($menu_item AS $full_path => $item) {
	            if(check_function("get_file_permission"))
					$file_permission = get_file_permission($full_path, $item["type"]);

		        if (!check_mod($file_permission, 1, true, AREA_STATIC_SHOW_MODIFY)) {  
		            unset($menu_item[$full_path]);
		        }
	        }
		}

		if(!$search_param) {
			foreach($menu_item AS $full_path => $item) {
            	if(substr_count($full_path, "/") == substr_count($compare_path, "/")) {
					$part_item[$full_path] = $item;
				}
			}
		} else {
			$part_item = $menu_item;
		}

	    if(array_key_exists($settings_path, $menu_item) && $menu_item[$settings_path]["owner"] == get_session("UserNID")) {
			$is_owner = true;
	    }		
	}

    /**
    * Admin Father Bar
    */
	if (
	    (AREA_STATIC_SHOW_ADDNEW && !$search_param)
	    /*|| AREA_PROPERTIES_SHOW_MODIFY 
	    || (AREA_ECOMMERCE_SHOW_MODIFY && !$search_param)*/
	    || AREA_LAYOUT_SHOW_MODIFY 
	    || (AREA_SETTINGS_SHOW_MODIFY && !$search_param)
	    || $is_owner
	) {
        $admin_menu["admin"]["unic_name"] = $unic_id . $settings_path . "-" . $is_owner;

		if($is_owner && !AREA_SHOW_NAVBAR_ADMIN)
        	$admin_menu["admin"]["title"] = ffTemplate::_get_word_by_code("static_menu_owner");
		else
	        $admin_menu["admin"]["title"] = $layout["title"];
		
		$admin_menu["admin"]["class"] = $layout["type_class"];
		$admin_menu["admin"]["group"] = $layout["type_group"];
        
        if($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
        	$admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_MENU . "/modify?parent=" . urlencode($settings_path) . "&owner=" . $menu_item[$settings_path]["owner"];
		} else {
	        if(AREA_STATIC_SHOW_ADDNEW && !$search_param) {
	            $admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/static/modify?parent=" . urlencode($settings_path);
	        } else {
	            $admin_menu["admin"]["addnew"] = "";
	        }
		}        
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(AREA_ECOMMERCE_SHOW_MODIFY && !$search_param) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(AREA_LAYOUT_SHOW_MODIFY) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(AREA_SETTINGS_SHOW_MODIFY && !$search_param) {
            $admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
        }
        
        $admin_menu["sys"]["path"] = $globals->user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
    }
    
	/**
	* Process Block Header
	*/	
    if(check_function("set_template_var")) {
    	$block["class"]["nav"] = $menu_params["class"]["nav"];
		if($layout_settings["AREA_STATIC_MENU_STICK"]) {
			$block["class"]["sticky"] = $menu_params["class"]["sticky"];
		}

		if($follow_framework && $layout_settings["AREA_STATIC_MENU_OFFCANVAS"]) {
			$block["class"]["offcanvas"] = $menu_params["class"]["side"];
		}
    
        $tpl = set_template_var($tpl); 
        if(!$layout_settings["AREA_STATIC_MENU_OFFCANVAS"])
            $block = get_template_header($settings_path, $admin_menu, $layout, $tpl, $block);
    }    

    if(is_array($part_item) && count($part_item)) {
		$tpl->set_var("child", "");

		$count_item = 1;
		foreach($part_item AS $full_path => $item) {
			$child = "";
			$item_link_class = array();
			$item_class = array();
			$item_properties = array();

			$tpl->set_var("SezCaret", "");
	        $tpl->set_var("class_elem", '');
			$tpl->set_var("SezItemDescription", "");	        
			
            set_cache_data("S", $item["ID"]);
			//$globals->cache["data_blocks"]["S" . "" . "-" . $item["ID"]] = $item["ID"];
			
            //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"])));

            if($search_param && !$layout_settings["AREA_STATIC_NAME_SHOW_IMAGE"])
                $tpl->set_var("item", preg_replace("/(" . escape_string_x_regexp($search_param) . ")/i" , "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars($item["title"])));
            else
                $tpl->set_var("item", ffCommon_specialchars($item["title"]));
			
			if($item["ajax"]) 
			{
				$tpl->set_var("menu_properties", ' event="' . $item["ajax_on_event"] . '"');
			} else 
			{
				$tpl->set_var("menu_properties", '');
			}

            if($user_path == stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) {
                $is_here = true;

                if($layout_settings["AREA_STATIC_FORCE_ACTUAL_LINK"]) {
                    $tpl->set_var("show_file", normalize_url_by_current_lang(stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]));    
                } else {
                    //$tpl->set_var("show_file",  "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"]));
                    $tpl->set_var("show_file",  "javascript:void(0);");
                }
            } else {
                $is_here = false;
                
                $tpl->set_var("SezTarget", "");
				
				
				if($item["alt_url"]) { 
					if (
						substr($item["alt_url"], 0, 1) != "/"
					) {
						$tpl->set_var("show_file", $item["alt_url"]);
						if(
							substr(strtolower($item["alt_url"]), 0, 7) == "http://"
							|| substr(strtolower($item["alt_url"]), 0, 8) == "https://"
                            || substr($item["alt_url"], 0, 2) == "//"
						) {
                        	$tpl->parse("SezTarget", false);
						} else {
							$tpl->set_var("SezTarget", "");
						}
					} else {
						if(strpos($item["alt_url"], "#") !== false) {
							$part_alternative_hash = substr($item["alt_url"], strpos($item["alt_url"], "#"));
							$item["alt_url"] = substr($item["alt_url"], 0, strpos($item["alt_url"], "#"));
						}

						if(strpos($item["alt_url"], "?") !== false) {
							$part_alternative_path = substr($item["alt_url"], 0, strpos($item["alt_url"], "?"));
							$part_alternative_url = substr($item["alt_url"], strpos($item["alt_url"], "?"));
						} else {
							$part_alternative_path = $item["alt_url"];
							$part_alternative_url = "";
						}
						if(check_function("get_international_settings_path")) {
							$arrAltUrl = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
							$tpl->set_var("show_file", normalize_url($arrAltUrl["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
						}
                    }
				} else {
                   	$tpl->set_var("show_file", normalize_url_by_current_lang(stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]));
				}
            }
            if($layout_settings["AREA_STATIC_SHOW_FULLTREE_ITEM"]
                && array_key_exists($full_path, $menu_item)
            ) {
            	if(check_function("process_static_menu_child"))
                    $child = process_static_menu_child($menu_item, $full_path, $user_path, $search_param, $layout);

                if(strlen($child) && $menu_params["icon"]) {
                    $tpl->set_var("caret_class", $menu_params["icon"]);
                    $tpl->parse("SezCaret", false);
                }
               
            }

			if($layout_settings["AREA_STATIC_NAME_SHOW_IMAGE"]) {
                if($is_here) {
                	$item_link_class["current"] = $menu_params["class"]["current"];
					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

					$tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');
					$tpl->parse("SezItemImgHere", false);
                    $tpl->set_var("SezItemImgNoHere", "");
                } else {
                    if($item["smart_url"] && strpos($user_path, stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) !== FALSE ) {
                        $item_class["current"] = $menu_params["class"]["current"];
                    }

					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

                    if(is_array($item_link_class) && count($item_link_class))
                        $tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');

					$tpl->set_var("SezItemImgHere", "");
                    $tpl->parse("SezItemImgNoHere", false);
                }
                $tpl->parse("SezItemImg", false);
                $tpl->set_var("SezItemNoImg", "");
			} else {
                if($is_here) {
                	$item_link_class["current"] = $menu_params["class"]["current"];
                   
					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";
                        
					$tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');
					$tpl->parse("SezItemNoImgHere", false);
                    $tpl->set_var("SezItemNoImgNoHere", "");
                } else {
                    if($item["smart_url"] && strpos($user_path, stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) !== FALSE ) {
                		$item_class["current"] = $menu_params["class"]["current"];
                    }
                    
                    if($item["ajax"])
                        $item_link_class["ajax"] = "ajaxcontent";

                    if(is_array($item_link_class) && count($item_link_class))
                        $tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');

                    $tpl->set_var("SezItemNoImgHere", "");
                    $tpl->parse("SezItemNoImgNoHere", false);
                }
                $tpl->set_var("SezItemImg", "");
                $tpl->parse("SezItemNoImg", false);
			}

            if ($layout_settings["AREA_STATIC_SHOW_DESCRIPTION"] && (strlen(trim(strip_tags($item["description"]))) || strpos($item["description"], "<img") !== false)) {
                if($search_param)
                    $tpl->set_var("description", preg_replace("/(" . escape_string_x_regexp($search_param) . ")/i" , "<strong class=\"theone\">\${1}</strong>", $item["description"]));
                else
                    $tpl->set_var("description", $item["description"]);

                $tpl->parse("SezItemDescription", false);
            }

        	if($item["owner"] == get_session("UserNID")) {
				$is_owner = true;
        	} else {
				$is_owner = false;
        	}

	        if (
	            AREA_STATIC_SHOW_MODIFY
	            || AREA_STATIC_SHOW_ADDNEW
	            || AREA_STATIC_SHOW_DELETE 
	            || AREA_PROPERTIES_SHOW_MODIFY 
	            || AREA_ECOMMERCE_SHOW_MODIFY 
	            || AREA_SETTINGS_SHOW_MODIFY
	            || $is_owner
	        ) {
                $popup["admin"]["unic_name"] = $unic_id . stripslash($item["parent"]) . "/" . $item["name"] . "-" . $is_owner;

				if($is_owner && !AREA_SHOW_NAVBAR_ADMIN)
        			$popup["admin"]["title"] = ffTemplate::_get_word_by_code("static_menu_owner") . ": " . $item["title"];
				else
	                $popup["admin"]["title"] = $layout["title"] . ": " . stripslash($item["parent"]) . "/" . $item["name"];
                
                $popup["admin"]["class"] = $layout["type_class"];
                $popup["admin"]["group"] = $layout["type_group"];
                
                $full_path = stripslash($item["parent"]) . "/" . $item["name"];
                if($full_path == "/")
                	$full_path = "/home";
                
                if($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
	                $popup["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_MENU . "/modify?parent=" . urlencode(stripslash($item["parent"]) . "/" . $item["name"]) . "&owner=" . $item["owner"];
                	$popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_MENU . "/modify" . $full_path . "?owner=" . $item["owner"];
		            $popup["admin"]["delete"] = ffDialog(TRUE,
	                                                "yesno",
	                                                ffTemplate::_get_word_by_code("vgallery_erase_title"),
	                                                ffTemplate::_get_word_by_code("vgallery_erase_description"),
	                                                "--returl--",
	                                                FF_SITE_PATH . VG_SITE_MENU . "/modify" . $full_path . "?ret_url=" . "--encodereturl--" . "&frmAction=StaticModify_confirmdelete" . "&owner=" . $item["owner"], 
	                                                FF_SITE_PATH . VG_SITE_MENU . "/dialog");
				} else {
	                if(AREA_STATIC_SHOW_ADDNEW) {
	                    $popup["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/static/modify?parent=" . urlencode(stripslash($item["parent"]) . "/" . $item["name"]);
	                } else {
	                    $popup["admin"]["addnew"] = "";
	                }
	                
	                if(AREA_STATIC_SHOW_MODIFY) {
                		$popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/static/modify" . $full_path;
					}
	                if(AREA_STATIC_SHOW_DELETE) {
	                    $popup["admin"]["delete"] = ffDialog(TRUE,
	                                                    "yesno",
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_title"),
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_description"),
	                                                    "--returl--",
	                                                    FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/static/modify" . $full_path . "?ret_url=" . "--encodereturl--" . "&frmAction=StaticModify_confirmdelete", 
	                                                    FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/static" . "/dialog");
	                }
				}
                if(AREA_PROPERTIES_SHOW_MODIFY) {
                    $popup["admin"]["extra"] = "";
                }
                if(AREA_ECOMMERCE_SHOW_MODIFY) {
                    $popup["admin"]["ecommerce"] = "";
                }
                if(AREA_SETTINGS_SHOW_MODIFY) {
                    $popup["admin"]["setting"] = "";
                }
                
                $popup["sys"]["path"] = $globals->user_path;
                $popup["sys"]["type"] = "admin_popup";

				if(strlen($block["admin"]["popup"])) {
	                $serial_popup = json_encode($popup);
	                
	                $item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME  . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';
	                $item_class["admin"] = "admin-bar";
				}                
            }
			
            $tpl->set_var("child", $child);
            if(strlen($child))
            	$item_class["child"] = $menu_params["class"]["has_child"];

            //$item_class["default"] = $item["smart_url"];
            
			if(count($item_class))
				$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';

			$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
            $tpl->parse("SezItem", true);
            $tpl->set_var("SezError", "");
            
            $count_item++;
		}

        if(strlen($layout_settings["AREA_STATIC_PLUGIN"])) 
        	$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["AREA_STATIC_PLUGIN"]));
        else
        	$tpl->set_var("class_plugin", "smenu");
		
        $tpl->parse("SezMenu", false);
	} else {
        $tpl->set_var("SezMenu", "");
        $strError = ffTemplate::_get_word_by_code("static_no_item");
	}
	
    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);        
    } else {
        $tpl->set_var("SezError", "");
    }
    
    if(is_array($menu_params["template"]) && count($menu_params["template"])) {
    	$res["template"] = $menu_params["template"];
    	$res["template"]["offcanvas"] = $tpl->rpparse("main", false);
    	$res["content"] = $res["template"]["content"];
    } else { 
		$res["content"] = $block["tpl"]["header"] . $tpl->rpparse("main", false) . $block["tpl"]["footer"];
    }

/*    if($layout_settings["AREA_STATIC_MENU_OFFCANVAS"])
    {
        $template["offcanvas"] = $block["tpl"]["header"] . $tpl->rpparse("main", false) . $block["tpl"]["footer"];
        $res["content"] = '<nav class="tab-bar hide-for-large-up"> 
                                <a class="menu-icon ' . $menu_params["class"]["class_menu_toggle"] . '">
                                    <span></span>
                                </a>
                            </nav>';
    } else { 
		$res["content"] = $block["tpl"]["header"] . $tpl->rpparse("main", false) . $block["tpl"]["footer"];
    }
	if(is_array($template) && count($template))
		$res["template"] = $template;
     */   
	return $res;
}


function array_splice_assoc(&$input, $offset, $length, $replacement) {
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));
        if (isset($input[$offset]) && is_string($offset)) {
                $offset = $key_indices[$offset];
        }
        if (isset($input[$length]) && is_string($length)) {
                $length = $key_indices[$length] - $offset;
        }

        $input = array_slice($input, 0, $offset, TRUE)
                + $replacement
                + array_slice($input, $offset + $length, NULL, TRUE);
}