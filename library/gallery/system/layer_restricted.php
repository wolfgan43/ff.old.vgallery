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

function system_load_menu($type = "admin") 
{
	if(is_file(FF_DISK_PATH . "/cache/menu/" . $type . "." . FF_PHP_EXT))
		require FF_DISK_PATH . "/cache/menu/" . $type . "." . FF_PHP_EXT;

    /** @var include $menu */
    if(!is_array($menu))
	{
	    $db = ffDB_Sql::factory();  
	    $globals = ffGlobals::getInstance("gallery");
	    if(check_function("system_get_sections"))
	        $block_type = system_get_block_type();	

		$menu 											= array();
		$actual_page 									= "/" . $type . "/pages";
		
		if(strpos($globals->page["user_path"], $actual_page) === 0 && basename($globals->page["user_path"]) == "builder") {
			$user_path = str_replace($actual_page, "", ffCommon_dirname($globals->page["user_path"]));
			if($user_path == "/home")
				$user_path = "/";
		}
		
		if(strpos($globals->page["user_path"], "/" . $type . "/pages") === 0) {
			$actual_page = (basename($globals->page["user_path"]) == "builder" 
							? ffCommon_dirname($globals->page["user_path"])
							: $globals->page["user_path"]
						);
		}
				
		switch($type) {
			case "builder":
				$def = array(
					"pages" 							=> true
					, "block" 							=> true
					, "widgets"							=> true
					, "util"							=> true
				);
				
				$menu_override["pages"]["menu"]			= array(
					"collapse" => false
					, "actions" => array(
						"modify" => array("class" => "-floating"
							, "icon" => "sitemap"
						)
					)
				);
				break;
			case "restricted":
				$def = array(
					"contents" 							=> true
					, "util" 							=> true
				);
				$menu_override["wysiwyg"]				= false;
				$menu_override["html"]					= false;
				$menu_override["vgallery"]				= array(
					"menu" => array(
						"actions" => null	
					)
				);
				$menu_override["addons"]				= array(
					"menu" => array(
						"actions" => null
					)
					, "piece" => array(
						"actions" => null
					)
					, "limit" => array(
						"readonly" => false
					)
				);
				break;
			case "contents":
				$def = array(
					"contents" 							=> true
				);
				$menu_override["publishing"]["nodes"]	= false;
				$menu_override["wysiwyg"]["nodes"]		= false;
				$menu_override["html"]["nodes"]			= false;
				$menu_override["albums"]["nodes"]		= false;
				$menu_override["vgallery"]["nodes"]		= false;
				$menu_override["addons"]["menu"]		= false;
				$menu_override["applets"] 				= false;
				break;
			case "admin":
			default:
				$def = array(
					"pages"								=> true
					, "block"							=> true
					, "content"							=> true
					, "widgets"							=> true
					, "ecommerce"						=> true
					, "landing"							=> true
					, "util"							=> true
					, "auth"							=> true
				);
				
				$menu_override["utility"]["menu"]			= array(
					"actions" => array(
						"modify" => array("class" => "-floating"
							, "icon" => "cube"
						)
					)
				);				
		}

	    $block["dialog"] 								= true;
		$extend["block"] = array(
			"section"									=> "rightcol"
			, "piece" 									=> array(
				"key" 										=> "block"
				, "icon" 									=> ""
				, "actions" 								=> null
				, "location" 								=> "rightcol"
				, "dialog" 									=> true
				, "class" 									=> "-draggable"
				, "actions" 								=> array(
																"modify" => array(
															        "path" => "/" . $type . "/pages/blocks/[rel]?path=" . $user_path
															        , "icon" => "object-group transparent"
															        , "label" => $user_path
																)
				)
			)
			, "contents" 								=> array()
		);
		$extend["content"] = array(
			"section"									=> null
			, "menu" 									=> array(
				"key" 										=> "content"
				, "icon" 									=> $block_type["virtual-gallery"]["icon"]
				, "actions" 								=> array(
																"modify" => array(
															        "path" => "/" . $type . "/contents/setting/add"
															        , "icon" => "plus"
																)
				)
			)
			, "piece" 									=> array(
				"key" 									=> "content"
				, "icon"								=> ""
			)
			, "contents" 								=> array()
		);		    

		if($def["pages"])
		{
		//print_r($block_type);
			if(strpos($globals->page["user_path"], "/" . $type . "/pages") === 0
				&& basename($globals->page["user_path"]) == "builder"
			) {
				$globals->page["icon"] = array(
					"name" => $block_type["static-pages-menu"]["icon"]
					, "type" => $block_type["static-pages-menu"]["group"]
				);
				$sSQL = "SELECT layout.* 
						FROM layout
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
						WHERE 1 AND " . $db->toSql($user_path) . " LIKE CONCAT(layout_path.ereg_path, IF(layout_path.cascading, '%', ''))
						ORDER BY layout.ID";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$block_key = ($db->getField("smart_url", "Text", true)
							? $db->getField("smart_url", "Text", true)
							: "L" . $db->getField("ID", "Number", true)
						);
						$block_in_page[$block_key] = array(
							"value" => $db->getField("value", "Text", true)
							, "params" => $db->getField("params", "Text", true)
							, "type" => $block_type["rev"][$db->getField("ID_type", "Number", true)] 
							
						);
					} while($db->nextRecord());
				}
				//print_r($block_in_page);
			//	die();
			} else {
				$def["block"] = false;
				$def["widgets"] = false;
			}
			/*********************
			* Pages
			*/
		    $sSQL = "SELECT static_pages.ID
		                , static_pages.parent
		                , static_pages.name
		                , static_pages.meta_title AS title
		                , (SELECT GROUP_CONCAT(DISTINCT CONCAT(IF(layout.ID_type = '" . $block_type["gallery"]["ID"] . "', 'Album: ', ''), layout.value) SEPARATOR ' - ')
	                		FROM layout
	                			INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
	                		WHERE layout_path.visible > 0
	                			AND layout.ID_type IN(" . $db->toSql($block_type["gallery"]["ID"], "Number") . "," . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . ")
	                			AND CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) LIKE layout_path.ereg_path
		                ) AS aspect
		                , CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
		            FROM static_pages
		            WHERE 1
		            ORDER BY full_path, sort";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
	    		//if(is_array($arrLayout) && count($arrLayout))
	    		//	$arrLayoutPath 							= array_keys($arrLayout);

				if($menu_override["pages"]["menu"] !== false) 
				{
					$menu["pages"]["menu"] = array(
						"key" 									=> "pages"
						, "path" 								=> "/" . $type . "/pages"
						, "label" 								=> $block_type["static-pages-menu"]["description"]
						, "icon"  								=> $block_type["static-pages-menu"]["icon"]
						, "actions" 							=> array(
																	"structure" => array(
																		"path" => $actual_page . "/structure"
                														, "icon" => "cog"
																	)
																	, "modify" => array(
																        "path" => $actual_page . "/add"
																        , "icon" => "plus"
																	)
																)
						, "class" 								=> "-tree -sortable"	
						
					);
					if(is_array($menu_override["pages"]["menu"]))
						$menu["pages"]["menu"] = array_replace_recursive($menu["pages"]["menu"], $menu_override["pages"]["menu"]);
				}
				
				if($def["widgets"])
				{
					$widgets["menu"]["default"] = array(
						"key" 									=> "block"
						, "subkey" 								=> "menu-default"
						//, "path" 								=> "javascript:void(0);"
						, "rel"									=> $block_type["static-pages-menu"]["smart_url"] . "-default"
						, "readonly" 							=> true
						, "label" 								=> "Default"
						, "icon" 								=> "list-ul"
						, "location" 							=> $extend["block"]["section"]
						, "class" => "-draggable"
        			);			
				}
				
				if($menu_override["pages"]["nodes"] !== false) 
				{
			        do {
        				$ID_page 								= $db->getField("ID", "Number", true);
        				$parent 								= $db->getField("parent", "Text", true);
        				$smart_url 								= $db->getField("name", "Text", true);
			            $full_path 								= stripslash($parent) . "/" . $smart_url;
			            $aspect 								= $db->getField("aspect", "Text", true);
			            $acitons 								= null;
						$description                            = "";	            

			            if($full_path == "/") {
			                $full_path 							= "/home";
			                $class 								= "";
						} else {
				            $class 								= '-pad' . substr_count($full_path, "/");
						}

			            if($aspect) {
				            $acitons["aspect"] 					= array(
															        "path" => "/" . $type . "/pages" . $full_path . "/appearance"
                													, "icon" => "object-group"
															    );
						    $description 						= ucwords($aspect);
						}
			            
        				$title 									= $db->getField("title", "Text", true);
        				if(!$title)
        					$title 								= ucwords(str_replace("-", " ", $smart_url));
						
						$menu["pages"]["nodes"][] = array(
							"key" 								=> "pages"
			                , "subkey" 							=>  "S-" . $db->getField("ID", "Number", true)
			                , "path" 							=> "/" . $type . "/pages" . $full_path . "/builder"
			                , "label" 							=> $title
                            , "description"                     => $description
			                , "actions" 						=> $acitons
							, "dialog" 							=> false	
							, "class" 							=>  $class
						);

						if(is_array($menu_override["pages"]["piece"]))
							$menu["pages"]["nodes"][count($menu["pages"]["nodes"]) - 1] = array_replace($menu["pages"]["nodes"][count($menu["pages"]["nodes"]) - 1], $menu_override["pages"]["piece"]);
			        } while($db->nextRecord());
				}
		    }
		}

		if($def["contents"] || $def["block"] || $def["content"])
		{
			/*******
			* Publishing
			*/
			if($menu_override["publishing"] !== false)
			{
				$sSQL = "SELECT publishing.ID
			                , publishing.name
			                , publishing.display_name
			                , publishing.full_selection
			            FROM publishing
			            WHERE 1
			            ORDER BY publishing.name";
			    $db->query($sSQL);
			    if($db->nextRecord()) {
					$extend["block"]["contents"]["publishing"]		= true;
					$extend["content"]["contents"]["publishing"]	= true;

					if(strpos($globals->page["user_path"], "/" . $type . "/widgets") === 0)
						$globals->page["icon"] = array(
							"name" => $block_type["publishing"]["icon"]
							, "type" => $block_type["publishing"]["group"]
						);
						
					if($menu_override["publishing"]["menu"] !== false) {
	    				$contents["publishing"]["menu"] = array(
							"key" 									=> "publishing"
						    , "path" 								=> "/" . $type . "/widgets"
						    , "label" 								=> $block_type["publishing"]["description"]
						    , "icon" 								=> $block_type["publishing"]["icon"]
						    , "actions" 							=> array("modify" => array(
																            "path" => "/" . $type . "/widgets/add"
																            , "icon" => "plus"
																	    )
																    )	
						);	 
						if(is_array($menu_override["publishing"]["menu"]))
							$contents["publishing"]["menu"] = array_replace_recursive($contents["publishing"]["menu"], $menu_override["publishing"]["menu"]);
					}	   				
	   				if($menu_override["publishing"]["nodes"] !== false) {
				        do { 
	        				$publish_name = $db->getField("name", "Text", true);
	        				$publish_title = $db->getField("display_name", "Text", true);
	        				$publish_auto = $db->getField("full_selection", "Number", true);
	        				if(!$publish_title)
	        					$publish_title = ucwords(str_replace("-", " ", $publish_name));

	        				$contents["publishing"]["nodes"][] = array(
								"key" 								=> "publishing"
								, "subkey" 							=> $publish_name
								, "path" 							=> "/" . $type . "/widgets/" . $publish_name . "/contents"
								, "rel"								=> $block_type["publishing"]["smart_url"] . "-" . $publish_name
								, "label" 							=> $publish_title
								, "actions" 						=> array(
                														"fields" => array(
                															"path" => "/" . $type . "/widgets/" . $publish_name . "/structure"
                															, "icon" => "table"
                														)
															            , "modify" => array(
			            													"path" => "/" . $type . "/widgets/" . $publish_name
                														)
															        ) 																									//actions
								, "dialog" 							=> $block["dialog"]							//dialog: false        			
								, "readonly" 						=> (bool) $publish_auto
        					);

							if(is_array($menu_override["publishing"]["piece"]))
								$contents["publishing"]["nodes"][count($contents["publishing"]["nodes"]) - 1] = array_replace($contents["publishing"]["nodes"][count($contents["publishing"]["nodes"]) - 1], $menu_override["publishing"]["piece"]);
				        } while($db->nextRecord());
					}
			    }
			}
			
			/*********************
			* Draft
			*/
			if($menu_override["wysiwyg"] !== false)
			{
				$sSQL = "SELECT drafts.ID
			                , drafts.name
			            FROM drafts
			            WHERE 1
			            ORDER BY drafts.name";
			    $db->query($sSQL);
			    if($db->nextRecord()) {
	    			$extend["block"]["contents"]["wysiwyg"]		= true;
	    			$extend["content"]["contents"]["wysiwyg"]	= true;
	    			
					if(strpos($globals->page["user_path"], "/" . $type . "/wysiwyg") === 0)
						$globals->page["icon"] = array(
							"name" => $block_type["static-page-by-db"]["icon"]
							, "type" => $block_type["static-page-by-db"]["group"]
						);
					
					if($menu_override["wysiwyg"]["menu"] !== false) {
	    				$contents["wysiwyg"]["menu"] = array(
							"key" 									=> "wysiwyg"
						    , "path" 								=> "/" . $type . "/wysiwyg"
						    , "label" 								=> $block_type["static-page-by-db"]["description"]
						    , "icon" 								=> $block_type["static-page-by-db"]["icon"]
						    , "actions" 							=> array("modify" => array(
																            "path" => "/" . $type . "/wysiwyg/add"
																            , "icon" => "plus"
																	    )
																    )	
						);
						if(is_array($menu_override["wysiwyg"]["menu"]))
							$contents["wysiwyg"]["menu"] = array_replace_recursive($contents["wysiwyg"]["menu"], $menu_override["wysiwyg"]["menu"]);
					}					
					if($menu_override["wysiwyg"]["nodes"] !== false) {
				        do { 
	        				$draft_name = $db->getField("name", "Text", true);
							$contents["wysiwyg"]["nodes"][] = array(
			 					"key" 								=> "wysiwyg"
								, "subkey" 							=> $draft_name
								, "path" 							=> "/" . $type . "/wysiwyg/" . $draft_name
								, "rel"								=> $block_type["static-page-by-db"]["smart_url"] . "-" . $draft_name
								, "label" 							=> $draft_name 
								, "actions" 						=> array("preview" => array(
																        "path" => "/" . $type . "/wysiwyg/preview/" . $draft_name
																        , "icon" => "preview"
																	))	
								, "dialog" 							=> $block["dialog"]																					//dialog: false      
			 				);

							if(is_array($menu_override["wysiwyg"]["piece"]))
								$contents["wysiwyg"]["nodes"][count($contents["wysiwyg"]["nodes"]) - 1] = array_replace($contents["wysiwyg"]["nodes"][count($contents["wysiwyg"]["nodes"]) - 1], $menu_override["wysiwyg"]["piece"]);
				        } while($db->nextRecord());
					}
			    }
			}
			/*********************
			* Html
			*/
			if($menu_override["html"] !== false)
			{
				$html = glob(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/template/*");
				if(is_array($html) && count($html)) {
					$extend["block"]["contents"]["html"]		= true;
					$extend["content"]["contents"]["html"]		= true;
					
					if(strpos($globals->page["user_path"], "/" . $type . "/html") === 0)
						$globals->page["icon"] = array(
							"name" => $block_type["static-page-by-file"]["icon"]
							, "type" => $block_type["static-page-by-file"]["group"]
						);
					
					if($menu_override["html"]["menu"] !== false) {
						$contents["html"]["menu"] = array(
							"key" 									=> "html"
						    , "path" 								=> "/" . $type . "/html"
						    , "label" 								=> $block_type["static-page-by-file"]["description"]
						    , "icon" 								=> $block_type["static-page-by-file"]["icon"]
						    , "actions" 							=> array("modify" => array(
															            "path" => "/" . $type . "/html/add"
															            , "icon" => "plus"
																    ))	
						);
						if(is_array($menu_override["html"]["menu"]))
							$contents["html"]["menu"] = array_replace_recursive($contents["html"]["menu"], $menu_override["html"]["menu"]);
					}
					
					if($menu_override["html"]["nodes"] !== false) {
						foreach($html AS $full_path) {
		 					if(is_file($full_path)) {
		 						$html_name = basename($full_path);

			 					$contents["html"]["nodes"][] = array(
			 						"key" 							=> "html"
									, "subkey" 						=> $html_name
									, "path" 						=> "/" . $type . "/html/" . $html_name
									, "rel"							=> $block_type["static-page-by-file"]["smart_url"] . "-" . $html_name
									, "label" 						=> $html_name 
									, "actions" 					=> array("preview" => array(
															            "path" => "/" . $type . "/html/preview/" . $html_name
															            , "icon" => "preview"
																    ))	
									, "dialog" 						=> $block["dialog"]																					//dialog: false      
			 					);

								if(is_array($menu_override["html"]["piece"]))
									$contents["html"]["nodes"][count($contents["html"]["nodes"]) - 1] = array_replace($contents["html"]["nodes"][count($contents["html"]["nodes"]) - 1], $menu_override["html"]["piece"]);
							}
						}
					}
				}
			}
			
			/*********************
			* VGallery
			*/	    
			if($menu_override["vgallery"] !== false)
			{
                $sSQL = "SELECT vgallery_type.ID
                            , vgallery_type.name
                            , vgallery_type.is_dir_default
                        FROM vgallery_type
                        WHERE 1
                        ORDER BY vgallery_type.name";
                $db->query($sSQL);
                if($db->nextRecord()) {
                    do {
                        $arrVgalleryType[$db->getField("ID", "Number", true)] = array(
                            "name" => $db->getField("name", "Text", true)
                            , "is_dir" => $db->getField("is_dir_default", "Number", true)
                        );
                    } while($db->nextRecord());
                }
                
 				$sSQL = "SELECT vgallery.ID AS ID_vgallery
 							, vgallery.limit_type AS limit_type
			                , vgallery.name AS vgallery_name
			                , vgallery_nodes.ID AS ID_node
			                , vgallery_nodes.parent
			                , vgallery_nodes.name
			                , vgallery_nodes.meta_title
			                , (SELECT COUNT(count_node.ID) FROM vgallery_nodes AS count_node WHERE count_node.parent LIKE CONCAT(vgallery_nodes.parent, '/', vgallery_nodes.name, '%') AND count_node.is_dir = '0' )  AS count_node 
			            FROM vgallery
            				LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID 
            					AND vgallery_nodes.parent = CONCAT('/', vgallery.name)
            					AND vgallery_nodes.is_dir > 0
            					AND vgallery_nodes.name <> ''
			            WHERE vgallery.public = 0
			            ORDER BY vgallery.name, vgallery_nodes.parent, vgallery_nodes.name";
			    $db->query($sSQL);
			    if($db->nextRecord()) {
					if(strpos($globals->page["user_path"], "/" . $type . "/contents") === 0)
						$globals->page["icon"] = array(
							"name" => $block_type["virtual-gallery"]["icon"]
							, "type" => $block_type["virtual-gallery"]["group"]
						);	

			        do { 
						$ID_vgallery 							= $db->getField("ID_vgallery", "Number", true);
        				$vgallery_name 							= $db->getField("vgallery_name", "Text", true);
        				$limit_type 							= $db->getField("limit_type", "Text", true);
        				$ID_node 								= $db->getField("ID_node", "Number", true);
        				$parent 								= $db->getField("parent", "Text", true);
        				$smart_url 								= $db->getField("name", "Text", true);
        				$count_node 							= $db->getField("count_node", "Number", true);
						$meta_title 							= $db->getField("meta_title", "Text", true);
						$full_path 								= stripslash($parent) . "/" . $smart_url;
						if(!$meta_title)
							$meta_title 						= ucwords(str_replace("-", " " , $smart_url));
							
						if(!$contents[$vgallery_name]) {
	        				$extend["block"]["contents"][$vgallery_name] = array(
	        					"menu" => array(
	        						 "key" 						=> "block"																								//key
									, "subkey" 					=> "contents"																						//subkey
									, "label" 					=> $block_type["virtual-gallery"]["description"] /*. '<span class="sub-title">' . ucwords($module_type) .'</span>'	*/					//label: ''
									, "icon" 					=> $block_type["virtual-gallery"]["icon"]
									, "location" 				=> $extend["block"]["section"]																		//location: null
									, "readonly" 				=> "h4"
									, "actions"					=> array("modify" => array(
															            "path" => "/" . $type . "/contents/setting/add"
															            , "icon" => "plus"
																    )
																)
	        					)
	        					, "skip_node" 					=> true
	        					
	        					, "limit" => array(
	        						//"action2path" 				=> "struct"
	        					)
	        				);				
                            $extend["content"]["contents"][$vgallery_name]		= array(
	        					"menu" => array(
	        						 "key" 						=> "content"																								//key
									, "subkey" 					=> "contents"																						//subkey
									, "label" 					=> $block_type["virtual-gallery"]["description"] /*. '<span class="sub-title">' . ucwords($module_type) .'</span>'	*/					//label: ''
									, "icon" 					=> $block_type["virtual-gallery"]["icon"]
									, "readonly" 				=> "h4"
									, "actions"					=> array("modify" => array(
															            "path" => "/" . $type . "/contents/setting/add"
															            , "icon" => "plus"
																    )
																    ,"struct" => array(
															            "path" => "/" . $type . "/contents/structure"
															            , "icon" => "cog"
															            , "dialog" => false
																    )
																)	
	        					)
	        					, "skip_node" 					=> true
	        					
	        				);	
                            $actions = array();
                            if($limit_type) {
                                $arrLimitType = explode(",", $limit_type);
                                foreach($arrLimitType AS $ID_type) {
                                    if(!$arrVgalleryType[$ID_type])
                                        continue;

                                    $actions["struct-" . $ID_type] = array(
                                        "path" => "/" . $type . "/contents/structure/" . $arrVgalleryType[$ID_type]["name"]
                                        , "icon" => ($arrVgalleryType[$ID_type]["is_dir"] 
                                            ? "folder"
                                            : "table"
                                        )
                                    );
                                }
                            }

                            $actions["modify"] = array(
                                "path" => "/" . $type . "/contents/" . $vgallery_name . "/setting?keys[ID]=" . $ID_vgallery
                            );
                            
                            if($menu_override["vgallery"]["menu"] !== false) {
        						$contents[$vgallery_name]["menu"] = array(
 									"key" 							=> $vgallery_name
						            , "path" 						=> "/" . $type . "/contents/" . $vgallery_name
						            , "rel"							=> $block_type["virtual-gallery"]["smart_url"] . "-" . $vgallery_name
						            , "label" 						=> ucwords(str_replace("-", " " , $vgallery_name))
						            , "icon" 						=> $block_type["virtual-gallery"]["icon"]
						            , "actions" 					=> $actions
									, "dialog" 						=> false
        						);
								if(is_array($menu_override["vgallery"]["menu"]))
									$contents[$vgallery_name]["menu"] = array_replace_recursive($contents[$vgallery_name]["menu"], $menu_override["vgallery"]["menu"]);
							}        					
							
							if($def["widgets"])
							{
        						$widgets["menu"][$vgallery_name] = array(
									"key" 							=> "block"
									, "subkey" 						=> "menu-" . $vgallery_name
									//, "path" 						=> "javascript:void(0);"
									, "rel"							=> $block_type["vgallery-menu"]["smart_url"] . "-" . $vgallery_name
									, "readonly" 					=> true
									, "label" 						=> ucwords(str_replace("-", " " , $vgallery_name))
									, "icon" 						=> $block_type["virtual-gallery"]["icon"]
									, "location" 					=> $extend["block"]["section"]
									, "class" 						=> "-draggable"
        						);
							}
							if($ID_node && $menu_override[$vgallery_name]["nodes"] !== false) {
								$contents[$vgallery_name]["nodes"][] = array(
 									"key" 						=> $vgallery_name
							        , "subkey" 					=> "V-" . $vgallery_name
							        , "path" 					=> "/" . $type . "/contents/" . $vgallery_name
							        , "label" 					=> ffTemplate::_get_word_by_code("all")   
									, "dialog" 					=> false
        						);  

								if(is_array($menu_override["vgallery"]["piece"]))
									$contents[$vgallery_name]["nodes"][count($contents[$vgallery_name]["nodes"]) - 1] = array_replace($contents[$vgallery_name]["nodes"][count($contents[$vgallery_name]["nodes"]) - 1], $menu_override["vgallery"]["piece"]);
							}      			
						}
						if($ID_node && $menu_override["vgallery"]["nodes"] !== false) {
        					$contents[$vgallery_name]["nodes"][] = array(
        						"key" 							=> $vgallery_name
							    , "subkey" 						=> "V-" . $ID_node
							    , "path" 						=> "/" . $type . "/contents" . $full_path
							    , "label" 						=> $meta_title . '<span class="nav-label">' . $count_node . '</span>'
								, "dialog" 						=> false
        					);

							if(is_array($menu_override["vgallery"]["piece"]))
								$contents[$vgallery_name]["nodes"][count($contents[$vgallery_name]["nodes"]) - 1] = array_replace($contents[$vgallery_name]["nodes"][count($contents[$vgallery_name]["nodes"]) - 1], $menu_override["vgallery"]["piece"]);
						}
			        } while($db->nextRecord());
			    }
			}

			/*********************
			* Album
			*/
			if($menu_override["albums"] !== false)
			{
				$album = glob(DISK_UPDIR . "/*", GLOB_ONLYDIR);
				if(is_array($album) && count($album)) {
					$extend["block"]["contents"]["albums"] 		= true;
					$extend["content"]["contents"]["albums"]	= true;
					
					if(strpos($globals->page["user_path"], "/" . $type . "/albums") === 0)
						$globals->page["icon"] = array(
							"name" => $block_type["gallery"]["icon"]
							, "type" => $block_type["gallery"]["group"]
						);	

					if($menu_override["albums"]["menu"] !== false) {
						$contents["albums"]["menu"] = array(
							"key" 									=> "albums"
						    , "path" 								=> "/" . $type . "/albums"
						    , "label" 								=> $block_type["gallery"]["description"]
						    , "icon" 								=> $block_type["gallery"]["icon"]
						    , "actions" 							=> array("modify" => array(
															            "path" => "/" . $type . "/albums/add"
															            , "icon" => "plus"
																    ))	
						);
						if(is_array($menu_override["albums"]["menu"]))
							$contents["albums"]["menu"] = array_replace_recursive($contents["albums"]["menu"], $menu_override["albums"]["menu"]);
					}
					foreach($album AS $full_path) {
		 				$album_name = basename($full_path);
		 	 			if($menu_override["albums"]["nodes"] !== false) {
		 	 				$contents["albums"]["nodes"][] = array(
		 	 					"key" 								=> "albums"
								, "subkey" 							=> $album_name 
								, "path" 							=> "/" . $type . "/albums/" . $album_name
								, "rel"								=> $block_type["gallery"]["smart_url"] . "-" . $album_name
								, "label" 							=> ucwords(str_replace("-", " " , $album_name)) . '<span class="sub-title">' . SITE_UPDIR . "/" . $album_name .'</span>'
								, "dialog" 							=> $block["dialog"]	
		 	 				);

							if(is_array($menu_override["albums"]["piece"]))
								$contents["albums"]["nodes"][count($contents["albums"]["nodes"]) - 1] = array_replace($contents["albums"]["nodes"][count($contents["albums"]["nodes"]) - 1], $menu_override["albums"]["piece"]);
						}		 	 			
        				if($def["widgets"])
        				{
        					$widgets["menu"][$album_name] = array(
								"key" 								=> "block"
								, "subkey" 							=> "menu-" . $album_name
								//, "path" 							=> "javascript:void(0);"
								, "rel"								=> $block_type["gallery-menu"]["smart_url"] . "-" . $album_name
								, "readonly" 						=> true
								, "label" 							=> ucwords(str_replace("-", " " , $album_name)) 
								, "icon" 							=> $block_type["gallery"]["icon"]
								, "location" 						=> $extend["block"]["section"]
								, "class" 							=> "-draggable"
        					);		 	 	
						}
					}
				}			    
			}
			
			/*******************
			* Modules
			*/
			if($menu_override["addons"] !== false)
			{
				$sSQL = "SELECT modules.module_name AS type
			                , modules.module_params AS name
			            FROM modules
			            WHERE 1
			            ORDER BY modules.module_name, modules.module_params";
			    $db->query($sSQL);
			    if($db->nextRecord()) {
                    $arrModule = array();
			        do { 
	        			$module_type 							= $db->getField("type", "Text", true);
	        			$module_name 							= $db->getField("name", "Text", true);
						$module_readonly						= !is_dir(FF_DISK_PATH . "/" . VG_UI_PATH . "/addons/" . $module_type . "/contents");

						if(isset($menu_override["addons"]["limit"]["readonly"]) && $menu_override["addons"]["limit"]["readonly"] !== $module_readonly) {
							continue;					
						}	        		
						
	        			if(!$arrModule[$module_type]) {
	        				$module_icon 						= $block_type["module"]["icon"];
							if($block_type["module"]["child"][$module_type]["icon"])
								$module_icon 					= $block_type["module"]["child"][$module_type]["icon"];

	        				$extend["block"]["contents"][$module_type] = array(
	        					"menu" => array(
	        						 "key" 						=> "block"																								//key
									, "subkey" 					=> "addons"																						//subkey
									, "label" 					=> $block_type["module"]["description"] /*. '<span class="sub-title">' . ucwords($module_type) .'</span>'	*/					//label: ''
									, "location" 				=> $extend["block"]["section"]																		//location: null
									, "readonly" 				=> "h4"
									, "actions"					=> array("modify" => array(
															            "path" => "/" . $type . "/pages/blocks/add"
															            , "icon" => "plus"
															            , "class" => "-floating"
																    )
																)
	        					)
	        					, "skip_node" 					=> false
	        					, "piece" => array(
	        						"icon" 						=> $module_icon
	        						, "actions" 				=> null
	        						, "readonly"				=> false
	        					)
	        					, "limit" => array(
	        						"action2path" 				=> "modify"
	        					)
	        				);
	        				$extend["content"]["contents"][$module_type]	= array(
	        					"menu" => array(
	        						 "key" 						=> "content"																								//key
									, "subkey" 					=> "addons"																						//subkey
									, "label" 					=> $block_type["module"]["description"] /*. '<span class="sub-title">' . ucwords($module_type) .'</span>'	*/					//label: ''
									, "readonly" 				=> "h4"
	        					)
	        					, "skip_node" 					=> true
	        					, "piece" => array(
	        						"icon" 						=> $module_icon
	        						, "actions" 				=> null
	        						, "readonly"				=> false
	        					)
	        				);
	        				
							if(strpos($globals->page["user_path"], "/" . $type . "/addons/" . $module_type) === 0)
								$globals->page["icon"] = array(
									"name" => $module_icon
									, "type" => $block_type["module"]["group"]
								);	

							if($menu_override["addons"]["menu"] !== false) {
	    						$contents[$module_type]["menu"] = array(
									"key" 							=> $module_type
								    , "path" 						=> "/" . $type . "/addons/" . $module_type
								    , "label" 						=> ucwords($module_type)
								    , "icon" 						=> $module_icon
								    , "actions" 					=> array("modify" => array(
															            "path" => "/" . $type . "/addons/" . $module_type . "/add"
															            , "icon" => "plus"
																    ))	
								);

								if(is_array($menu_override["addons"]["menu"]))
									$contents[$module_type]["menu"] = array_replace_recursive($contents[$module_type]["menu"], $menu_override["addons"]["menu"]);
							}
							$arrModule[$module_type]["readonly"] = $module_readonly;
	        			}

	        			if(is_dir(FF_DISK_PATH . "/" . VG_UI_PATH . "/addons/" . $module_type . "/fields")) {
	        				$arrModule[$module_type]["actions"]["field"] = array(
							    "path" 							=> "/" . $type . "/addons/" . $module_type . "/" . $module_name . "/fields"
							    , "icon" 						=> "table"
							);
						}
	        			$arrModule[$module_type]["actions"]["modify"] = array(
							"path" 								=> "/" . $type . "/addons/" . $module_type . "/" . $module_name
						);

						if($menu_override["addons"]["nodes"] !== false) {
							$contents[$module_type]["nodes"][] = array(
			 					"key" 								=> $module_type
								, "subkey" 							=> $module_name	
								, "path" 							=> "/" . $type . "/addons/" . $module_type . "/contents/" . $module_name	
								, "rel"								=> $block_type["module"]["smart_url"] . $module_type . "-" . $module_name
								, "label" 							=> ucwords($module_name) /*. '<span class="sub-title">' . ucwords($module_type) .'</span>'	*/
								, "actions" 						=> $arrModule[$module_type]["actions"]
								, "readonly"						=> $arrModule[$module_type]["readonly"]
			 				);
						
							if(is_array($menu_override["addons"]["piece"]))
								$contents[$module_type]["nodes"][count($contents[$module_type]["nodes"]) - 1] = array_replace($contents[$module_type]["nodes"][count($contents[$module_type]["nodes"]) - 1], $menu_override["addons"]["piece"]);
						}
			        } while($db->nextRecord());
			    }
			}
			
		    /*******************
			* Applets
			*/
			if($menu_override["applets"] !== false)
			{
				$extend["block"]["contents"]["applets"] = array(
					"piece" 									=> array(
						"hide" 									=> false
						//, "path" 								=> "javascript:void(0);"
						, "readonly" 							=> true
					)
				);

				if(strpos($globals->page["user_path"], "/" . $type . "/applets") === 0)
					$globals->page["icon"] = array(
						"name" => $block_type["forms-framework"]["icon"]
						, "type" => $block_type["forms-framework"]["group"]
					);	
				if($menu_override["applets"]["menu"] !== false) {
					$contents["applets"]["menu"] = array(
						"key" 										=> "applets"
						, "path" 									=> "/" . $type . "/applets"
						, "label" 									=> $block_type["forms-framework"]["description"]
						, "icon" 									=> $block_type["forms-framework"]["icon"]
					);
					if(is_array($menu_override["applets"]["menu"]))
						$contents["applets"]["menu"] = array_replace_recursive($contents["applets"]["menu"], $menu_override["applets"]["menu"]);
				}

				if($menu_override["applets"]["nodes"] !== false) {
					$applets = glob(FF_DISK_PATH . "/applets/*", GLOB_ONLYDIR);
					if(is_array($applets) && count($applets)) {
						foreach($applets AS $applet_path) {
							$applet_name = basename($applet_path);
							$contents["applets"]["nodes"][] = array(
								"key" 								=> "applets"
								, "subkey" 							=> $applet_name
								, "path" 							=> "/" . $type . "/applets/" . $applet_name
								, "rel"								=> $block_type["forms-framework"]["group"] . "-" . $applet_name
								, "label" 							=> ucwords(str_replace("-" , " ", $applet_name)) . '<span class="sub-title">' . "FrontEnd" .'</span>'
								, "icon" 							=> $block_type["forms-framework"]["icon"]
								, "hide" 							=> !is_dir(FF_DISK_PATH . "/contents/restricted/" . $applet_name)
								, "dialog" 							=> $block["dialog"]
							);

							if(is_array($menu_override["applets"]["piece"]))
								$contents["applets"]["nodes"][count($contents["applets"]["nodes"]) - 1] = array_replace($contents["applets"]["nodes"][count($contents["applets"]["nodes"]) - 1], $menu_override["applets"]["piece"]);
						}
					}
					
					$applets = glob(FF_DISK_PATH . "/modules/*/applets/*", GLOB_ONLYDIR);
					if(is_array($applets) && count($applets)) {
						foreach($applets AS $applet_path) {
							$applet_name = basename($applet_path);
							$applet_type = basename(ffCommon_dirname(ffCommon_dirname($applet_path)));
							$contents["applets"]["nodes"][] = array(
								"key" 								=> "applets"
								, "subkey" 							=> $applet_type . "-" . $applet_name
								, "path" 							=> "/" . $type . "/applets/" . $applet_type . "/" . $applet_name
								, "rel"								=> $block_type["forms-framework"]["group"] . "-" . $applet_type . "-" . $applet_name
								, "label" 							=> ucwords(str_replace("-" , " ", $applet_name)) . '<span class="sub-title">' . $applet_type .'</span>'
								, "icon" 							=> $block_type["forms-framework"]["icon"]
								, "hide" 							=> !is_dir(FF_DISK_PATH . "/modules/" . $applet_type . "/contents/restricted/" . $applet_type . "/" . $applet_name)
								, "dialog" 							=> $block["dialog"]
							);

							if(is_array($menu_override["applets"]["piece"]))
								$contents["applets"]["nodes"][count($contents["applets"]["nodes"]) - 1] = array_replace($contents["applets"]["nodes"][count($contents["applets"]["nodes"]) - 1], $menu_override["applets"]["piece"]);
						}
					}	 
				}
			}		
		}
		
		if(is_array($extend) && count($extend)) 
		{
			foreach($extend AS $ext_name => $ext_data)
			{
				if(!$def[$ext_name])
					continue;

				if($ext_data["menu"])
					$menu[$ext_name]["menu"] = $ext_data["menu"];

				$menu[$ext_name]["nodes"] = array();
				foreach($ext_data["contents"] AS $key => $settings) 
				{
					$piece_override 				= $ext_data["piece"];
					$piece_limit 					= null;
					$skip_node						= false;					
					if(is_array($settings)) {
						if(!$menu[$ext_name]["nodes"][$settings["menu"]["subkey"]]) {
							if(is_array($settings["menu"]))
								$menu[$ext_name]["nodes"][$settings["menu"]["subkey"]] = $settings["menu"];
							else {
								$menu[$ext_name]["nodes"][$settings["menu"]["subkey"]] = array(
									"key" 			=> $ext_name
									, "subkey" 		=> $contents[$key]["menu"]["key"]
									, "label" 		=> $contents[$key]["menu"]["label"]
									, "icon" 		=> $contents[$key]["menu"]["icon"]
									, "actions" 	=> $contents[$key]["menu"]["actions"]
									, "location" 	=> $ext_data["section"]
									, "readonly" 	=> "h4"
								);								
							}
						}

						if(is_array($settings["piece"]))
							$piece_override 		= array_replace($ext_data["piece"], $settings["piece"]);

						if(is_array($settings["limit"]))
							$piece_limit 			= $settings["limit"];

						$skip_node 					= $settings["skip_node"];
						
						if($skip_node) {
							$nodes 					= (array) $contents[$key]["menu"];
							$nodes["subkey"] 		= $nodes["key"];
							$nodes["key"] 			= $settings["menu"]["subkey"];
							
							$menu[$ext_name]["nodes"][] = system_override_menu_piece($nodes, $piece_override, $piece_limit);
						}
					} else {
						$menu[$ext_name]["nodes"][] = array(
							"key" 					=> $ext_name
							, "subkey" 				=> $contents[$key]["menu"]["key"]
							, "label" 				=> $contents[$key]["menu"]["label"]
							, "icon" 				=> $contents[$key]["menu"]["icon"]
							, "actions" 			=> $contents[$key]["menu"]["actions"]
							, "location" 			=> $ext_data["section"]
							, "readonly" 			=> "h4"
						);
					}
					
					if(!$skip_node && is_array($contents[$key]["nodes"]) && count($contents[$key]["nodes"])) {
						$nodes = (array) $contents[$key]["nodes"];
						$menu[$ext_name]["nodes"] = array_merge($menu[$ext_name]["nodes"], array_filter($nodes, function(&$piece) use($piece_override, $piece_limit) {
							$piece = system_override_menu_piece($piece, $piece_override, $piece_limit);
							
							return $piece;
						}));
					}
				} 
			}
		}

		if($def["widgets"]) 
		{
			/*******************
			* Widgets
			*/
			$widgets["default"] = array(
				"orinav" 								=> $block_type["orinav"]["icon"]
				, "languages" 							=> $block_type["languages"]["icon"]
				, "login" 								=> $block_type["login"]["icon"]
				, "search" 								=> $block_type["search"]["icon"]
			);			

			foreach($widgets AS $widget_group => $widgets_data) {
				if($widget_group != "default") {
					$menu[$widget_group]["nodes"] 		= $widgets_data;
					array_unshift($menu[$widget_group]["nodes"], array(
						"key" 							=> "block"
						, "subkey" 						=> $widget_group
						, "label" 						=> ffTemplate::_get_word_by_code($widget_group)
						, "icon" 						=> $widgets_data["default"]["icon"]
						, "location" 					=> $extend["block"]["section"]
						, "readonly" 					=> "h4"
					));

				} elseif(is_array($widgets_data) && count($widgets_data)) {
					$menu["widgets"]["nodes"][] = array(
						"key" 							=> "block"
						, "subkey" 						=> "widgets"
						, "label" 						=> "Widgets"
						, "icon" 						=> "cubes"
						, "location" 					=> $extend["block"]["section"]
						, "readonly" 					=> "h4"
					);
					foreach($widgets_data AS $widget_key => $widget_icon) {
						$menu["widgets"]["nodes"][] = array(
							"key" 						=> "block"
							, "subkey" 					=> "widgets-" . $widget_key
							, "path" 					=> "javascript:void(0);"
							, "rel"						=> $block_type[$widget_key]["smart_url"]
							, "label" 					=> $block_type[$widget_key]["description"]
							, "icon" 					=> $widget_icon
							, "location" 				=> $extend["block"]["section"]
							, "class" 					=> "-draggable"
						);
					}
				}
			}				
		}

		if($def["contents"])
			$menu = $contents + $menu;
		
		
		if($def["ecommerce"])
		{
			/****
			* Ecommerce
			*/
			$menu["ec"]["menu"] = array(
				"key" 										=> "ec"
				, "label" 									=> "Ecommerce"
				, "icon" 									=> $block_type["ecommerce"]["icon"]
			);		

			$arrEcommerce = glob(FF_DISK_PATH . VG_SYS_PATH . "/ecommerce/*", GLOB_ONLYDIR);
		    if(is_array($arrEcommerce) && count($arrEcommerce)) {
		        foreach($arrEcommerce AS $real_file) {
		        	$file = basename($real_file);

			        $menu["ec"]["nodes"][] = array(
						"key"										=> "ec"
						, "subkey" 									=> "ec-" . $file
						, "path" 									=> VG_RULE_ECOMMERCE . "/" . $file
						, "label" 									=> ucwords(str_replace("-", " ", $file))
					);
				}
			}		
		}
		
		
		if($def["landing"])
		{
			/****
			* Landing Page
			*/
			$menu["landing-pages"]["menu"] = array(
				"key" 										=> "landing-pages"
				, "label" 									=> "Landing Page"
				, "icon" 									=> $block_type["tags-menu"]["icon"]
				, "actions" 								=> array("modify" => array(
														        "path" => "/" . $type . "/landing-pages/config"
															))	
			);
			

			if(strpos($globals->page["user_path"], "/" . $type . "/landing-pages/place") === 0)
				$globals->page["icon"] = array(
					"name" => "map-marker"
					, "type" => "content"
				);	
				
			$menu["landing-pages"]["nodes"][] = array(
				"key" 										=> "landing-pages"
				, "subkey" 									=> "place"
				, "path" 									=> "/" . $type . "/landing-pages/place"
				, "label" 									=> "Place"
				, "icon" 									=> "map-marker"
			);
			
			if(strpos($globals->page["user_path"], "/" . $type . "/landing-pages/tag") === 0)
				$globals->page["icon"] = array(
					"name" => "tag"
					, "type" => "content"
				);	
			$menu["landing-pages"]["nodes"][] = array(
				"key" 										=> "landing-pages"
				, "subkey" 									=> "tags"
				, "path" 									=> "/" . $type . "/landing-pages/tag"
				, "label" 									=> "Tag"
				, "icon" 									=> "tag"
			);	
				
			if(strpos($globals->page["user_path"], "/" . $type . "/landing-pages/search") === 0)
				$globals->page["icon"] = array(
					"name" => "tag"
					, "type" => "content"
				);	
			$menu["landing-pages"]["nodes"][] = array(
				"key" 										=> "landing-pages"
				, "subkey" 									=> "search"
				, "path" 									=> "/" . $type . "/landing-pages/search"
				, "label" 									=> "Search"
				, "icon" 									=> "search"
			);				
		}	    

		/*****
		* Anagraph
		*/
		if($def["auth"])
		{
			if(strpos($globals->page["user_path"], "/" . $type . "/auth") === 0)
				$globals->page["icon"] = array(
					"name" => $block_type["user"]["icon"]
					, "type" => $block_type["user"]["group"]
				);			
		
			$menu["auth"]["menu"] = array(
				"key" 										=> "auth"
				, "path" 									=> "/" . $type . "/auth"
				, "label" 									=> ffTemplate::_get_word_by_code("auth")
				, "icon" 									=> $block_type["user"]["icon"]
				, "actions" 								=> array("add" => array(
															    "path" => "/" . $type . "/auth/users/add"
															    , "icon" => "user-plus"
															))	
			);	
			if(is_array($menu_override["auth"]["menu"]))
				$menu["auth"]["menu"] = array_replace_recursive($menu["auth"]["menu"], $menu_override["auth"]["menu"]);

			$menu["auth"]["nodes"][] = array(
 				"key" 										=> "auth"
				, "subkey" 									=> "auth-users"
				, "path" 									=> "/" . $type . "/auth/users"
				, "label" 									=> ffTemplate::_get_word_by_code("users")   
			); 		
			$menu["auth"]["nodes"][] = array(
 				"key" 										=> "auth"
				, "subkey" 									=> "auth-groups"
				, "path" 									=> "/" . $type . "/auth/groups"
				, "label" 									=> ffTemplate::_get_word_by_code("groups")   
			); 		
		    $menu["auth"]["nodes"][] = array(
 				"key" 										=> "auth"
				, "subkey" 									=> "auth-perm"
				, "path" 									=> "/" . $type . "/auth/settings"
				, "label" 									=> ffTemplate::_get_word_by_code("permissions")   
			); 	
		    $menu["auth"]["nodes"][] = array(
 				"key" 										=> "auth"
				, "subkey" 									=> "oauth-apps"
				, "path" 									=> "/" . $type . "/auth/oauth-apps"
				, "label" 									=> ffTemplate::_get_word_by_code("oauth2_apps")   
			); 	
		    $menu["auth"]["nodes"][] = array(
 				"key" 										=> "auth"
				, "subkey" 									=> "oauth-scopes"
				, "path" 									=> "/" . $type . "/auth/oauth-scopes"
				, "label" 									=> ffTemplate::_get_word_by_code("oauth2_scopes")   
			); 				

		    /*
 			$sSQL = "SELECT anagraph_categories.name AS ID
					    , anagraph_categories.name
					    , (SELECT count(*) FROM anagraph WHERE FIND_IN_SET(anagraph_categories.ID, anagraph.categories)) AS count_node
					FROM anagraph_categories
					WHERE 1
					ORDER BY anagraph_categories.name";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$menu["users"]["nodes"][] = array(
 					"key" 									=> "auth"
					, "subkey" 								=> "users-all"
					, "path" 								=> "/" . $type . "/users"
					, "label" 								=> ffTemplate::_get_word_by_code("all")   
			    ); 		
				do {
					$cat_name 								= $db->getField("name", "Text", true);
					$count_node 							= $db->getField("count_node", "Number", true);
					$menu["users"]["nodes"][] = array(
 						"key" 								=> "users"
						, "subkey" 							=> "users-all"
						, "path"							=>"/" . $type . "/users/" . ffCommon_url_rewrite($cat_name) . '<span class="nav-label">' . $count_node . '</span>'
						, "label" 							=>  $cat_name  
				    ); 	

					if(is_array($menu_override["auth"]["piece"]))
						$menu["auth"]["nodes"][count($menu["auth"]["nodes"]) - 1] = array_replace($menu["auth"]["nodes"][count($menu["auth"]["nodes"]) - 1], $menu_override["auth"]["piece"]);
				} while($db->nextRecord());
			}*/
		}		

		/****
		* Utility
		*/
	    if($def["util"])
		{
			if(strpos($globals->page["user_path"], "/" . $type . "/utility") === 0)
				$globals->page["icon"] = array(
					"name" => "cog"
					, "type" => "content"
				);	
					
			$menu["utility"]["menu"] = array(
				"key" 										=> "utility"
				, "label" 									=> "Utility"
				, "icon" 									=> "cog"
				, "actions"									=> array(
																"modify" => array(
																	"path" => "/" . $type . "/pages/blocks/add"
																	, "icon" => "plus"
																)
															)	
			);

			if(is_array($menu_override["utility"]["menu"]))
				$menu["utility"]["menu"] = array_replace_recursive($menu["utility"]["menu"], $menu_override["utility"]["menu"]);
			
			$util = array(
				"icons" => array(
					"email" => "paper-plane-o"
					, "international" => "language"
					, "notify" => "bell-o"
				)
				, "favorite" => array(
					"international" => true
					, "notify" => true
				)
				, "actions" => null
			);
			$menu["utility"]["nodes"][] = array(
				"key"										=> "utility"
				, "subkey" 									=> "blocks"
				, "path" 									=> "/" . $type . "/pages/blocks"
				, "label" 									=> ffTemplate::_get_word_by_code("blocks")
				, "icon" 									=> "cubes"
			);
		    $arrUtility = glob(FF_DISK_PATH . "/" . VG_UI_PATH . "/restricted/utility/*", GLOB_ONLYDIR);
		    if(is_array($arrUtility) && count($arrUtility)) {
		        foreach($arrUtility AS $real_file) {
		        	$file = basename($real_file);
			        $menu["utility"]["nodes"][] = array(
						"key"										=> "utility"
						, "subkey" 									=> $file
						, "path" 									=> "/" . $type . "/utility/" . $file
						, "label" 									=> ucwords(str_replace("-", " ", $file))
						, "icon" 									=> $util["icons"][$file]
						, "favorite"								=> $util["favorite"][$file]
						, "actions"									=> $util["actions"][$file]
					);
				}
			}
		}		
	}

	return $menu;
}

function system_override_menu_piece($piece, $piece_override, $piece_limit = null) 
{
	if(isset($piece_limit["readonly"]) && $piece["readonly"] !== $piece_limit["readonly"])
		return false;

	$piece["subkey"] 		= $piece["key"] . "-" . $piece["subkey"];
	if($piece_limit["action2path"])
		$piece["path"] 		= $piece["actions"][$piece_limit["action2path"]]["path"];
	
	$piece = array_replace($piece, $piece_override);

	return $piece;
}

function system_load_menu_special($menu = array()) 
{

//da togliere stage_admin e tutti i vg_site_
	$menu["install"]["menu"] = array(
		"key"										=> "install"
		, "path" 									=> VG_RULE_INSTALL
		, "label" 									=> ffTemplate::_get_word_by_code("install")
		//, "favorite" 								=> true
		, "icon" 									=> "download"
		, "dialog" 									=> true
		, "location" 								=> "brand"
	);
	$menu["updater"]["menu"] = array(
		"key"										=> "updater"
		, "path" 									=> VG_RULE_UPDATER
		, "label" 									=> ffTemplate::_get_word_by_code("updater")
		//, "favorite" 								=> true
		, "icon" 									=> "refresh"
		, "dialog" 									=> true
		, "location" 								=> "brand"
	);
	$menu["webservices"]["menu"] = array(
		"key"										=> "webservices"
		, "path" 									=> VG_WEBSERVICES
		, "label" 									=> ffTemplate::_get_word_by_code("webservices")
		//, "favorite" 								=> true
		, "icon" 									=> "share-alt"
		, "dialog" 									=> false
		, "location" 								=> "brand"
	);	
	$menu["frontend"]["menu"] = array(
		"key"										=> "frontend"
		, "path" 									=> FF_SITE_PATH . "/"
		, "label" 									=> ffTemplate::_get_word_by_code("frontend")
		//, "favorite" 								=> true
		, "icon" 									=> "desktop"
		, "location" 								=> "admin"
	);
	$menu["admin"]["menu"] = array(
		"key"										=> "admin"
		, "path" 									=> VG_WS_ADMIN
		, "label" 									=> ffTemplate::_get_word_by_code("admin")
		//, "favorite" 								=> true
		, "icon" 									=> "cog"
		, "location" 								=> "admin"
	);
	$menu["builder"]["menu"] = array(
		"key"										=> "builder"
		, "path" 									=> VG_WS_BUILDER
		, "label" 									=> ffTemplate::_get_word_by_code("builder")
		//, "favorite" 								=> true
		, "icon" 									=> "industry"
		, "location" 								=> "admin"
	);

	$menu["restricted"]["menu"] = array(
		"key"										=> "restricted"
		, "path" 									=> VG_WS_RESTRICTED
		, "label" 									=> ffTemplate::_get_word_by_code("console")
		//, "favorite" 								=> true
		, "icon" 									=> "newspaper-o"
		, "location" 								=> "admin"
	);

	$menu["ecommerce"]["menu"] = array(
		"key"										=> "ecommerce"
		, "path" 									=> VG_WS_ECOMMERCE
		, "label" 									=> ffTemplate::_get_word_by_code("ecommerce")
		//, "favorite" 								=> true
		, "icon" 									=> "shopping-cart"
		, "location" 								=> "admin"
	);
	return $menu;
}

function system_layer_restricted($cm, $type) 
{
    $globals = ffGlobals::getInstance("gallery");
	
	$cm->oPage->tplAddJs("ff.cms.admin");
	$cm->oPage->minify = false; //previene l'eliminazione degli invii a capo all'interno delle TEXTAREA
    //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
    if($type == "restricted") 
    {
	    if(check_function("system_get_sections"))
	        $block_module = system_get_block_type("module");	
	        
	    if(is_array($cm->modules["restricted"]["menu"]) && count($cm->modules["restricted"]["menu"])) {
    		foreach($cm->modules["restricted"]["menu"] AS $key => $menu) {
    			if(!$cm->modules["restricted"]["menu"][$key]["icon"])
    				$cm->modules["restricted"]["menu"][$key]["icon"] = $block_module["icon"];
    		}
	    }
    } else {
    	$cm->modules["restricted"]["menu"] = array();
	}

    $menu = system_load_menu($type);
    $menu = system_load_menu_special($menu);

    if(is_array($menu) && count($menu)) {
		if(isset($menu["block"]))
    		$cm->oPage->class_body = "-rightview";

    	foreach($menu AS $key => $data) {
    		if(is_array($data["menu"]))
    			call_user_func("mod_restricted_add_menu_child", $data["menu"]);
    		
    		if(is_array($data["nodes"]) && count($data["nodes"])) {
    			foreach($data["nodes"] AS $submenu) {
    			    call_user_func("mod_restricted_add_menu_sub_element", $submenu);

    			}
    		}
    	}
    }
    
    if(check_function("system_set_media"))            
        system_set_media($cm->oPage, $globals->settings_path);  
		
}



// mm entra	da sistemare
function system_layer_manage($cm) 
{
    $globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	$cm->oPage->tplAddJs("ff.cms.admin");
	$cm->oPage->minify = false; //previene l'eliminazione degli invii a capo all'interno delle TEXTAREA

	//if(check_function("set_header_page")) {
		//set_header_page(null, false, false, false, false); 	
	//}

	check_function("set_generic_tags");
	//if(check_function("check_chron_job"))
	//    check_chron_job(ffGetFilename(VG_WS_ECOMMERCE));

	if (!AREA_ECOMMERCE_SHOW_MODIFY) {
	    prompt_login(null, FF_SITE_PATH . ($globals->page["alias"] ? "" : VG_WS_ECOMMERCE) . "/login");
	    //ffRedirect(FF_SITE_PATH . VG_WS_ECOMMERCE . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
	}

	if($cm->path_info == VG_WS_ECOMMERCE) 
	    ffRedirect(FF_SITE_PATH . VG_WS_ECOMMERCE . "/operations/" . date("Y", time()));


	$menu_structure = array(
	                            "settings" => "/settings"
	                            , "basic" => "/basic"
	                            , "pricelist_bytime" => "/time"
	                            , "pricelist_byqta" => "/qta"
	                            , "specialsupport" => "/specialsupport"
	                            , "addstock" => "/addstock"
	                        );
	//Anagraph
	if ((AREA_ANAGRAPH_SHOW_MODIFY || AREA_ANAGRAPH_SHOW_ADDNEW || AREA_ANAGRAPH_SHOW_DELETE)) {
        mod_restricted_add_menu_child(array(
            "key"       => "anagraph"
            , "path"    => VG_WS_ECOMMERCE . "/anagraph"
            , "label"   => ffTemplate::_get_word_by_code("anagraph")
            , "redir"   => VG_WS_ECOMMERCE . "/anagraph/all"
        ));

	    $db->query("
	                        SELECT 'all' AS ID, 
	                            " . $db->toSql("anagraph_all") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE 1) AS count_nodes 
	                        UNION
	                        SELECT anagraph_categories.name AS ID
	                            , anagraph_categories.name
	                            , (SELECT count(*) FROM anagraph WHERE FIND_IN_SET(anagraph_categories.ID, anagraph.categories)) AS count_nodes 
	                        FROM anagraph_categories
	                        UNION
	                        SELECT 'users' AS ID, 
	                            " . $db->toSql("anagraph_users") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE anagraph.uid > 0) AS count_nodes 
	                        UNION
	                        SELECT 'nocategory' AS ID, 
	                            " . $db->toSql("anagraph_nocategory") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE anagraph.categories = '') AS count_nodes 
	                        ");
	    if($db->nextRecord()) {
	        if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
	            || strpos($cm->path_info, VG_WS_ECOMMERCE . "/anagraph") === 0
	        ) {
	            if(strpos($cm->path_info, VG_WS_ECOMMERCE . "/anagraph") === 0 && strpos($cm->path_info, "modify") !== false) {
	                $hide_anagraph_categories = true;
	            } else {
	                $hide_anagraph_categories = false;
	            }

	            do {
                    mod_restricted_add_menu_sub_element(array(
                        "key"           => "anagraph"
                        , "subkey"      => $db->getField("ID")->getValue()
                        , "path"        => VG_WS_ECOMMERCE . "/anagraph/" . ffCommon_url_rewrite($db->getField("ID")->getValue())
                        , "label"       => " (" . $db->getField("count_nodes")->getValue() . ") " . ffTemplate::_get_word_by_code($db->getField("name")->getValue())
                        , "hide"        => $hide_anagraph_categories
                    ));
	            } while($db->nextRecord());
	        }
	    }
	}
	//Products 
	if((AREA_VGALLERY_SHOW_MODIFY || AREA_VGALLERY_SHOW_ADDNEW || AREA_VGALLERY_SHOW_DELETE || AREA_VGALLERY_SHOW_SEO || AREA_VGALLERY_SHOW_PERMISSION || AREA_ECOMMERCE_SHOW_MODIFY)) {
	    $db_manage_detail = ffDB_Sql::factory();
	    $db->query("SELECT vgallery.* 
	                           FROM vgallery
	                           WHERE
	                            vgallery.status = 1
	                            AND vgallery.enable_ecommerce = '1'
	                            ");
	    if($db->nextRecord()) {
	        $vg_data = array();
	        do {
	            $vg_data[$db->getField("ID", "Number", true)]["full_path"] = "/" . $db->getField("name", "Text", true);
	            $vg_data[$db->getField("ID", "Number", true)]["name"] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());    
	        
	        if(is_array($vg_data) && count($vg_data)) {
	            if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
	                get_file_permission(null, "vgallery_nodes", array_keys($vg_data));

	            foreach($vg_data AS $vg_data_key => $vg_data_value) {
	                if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
	                    $file_permission = get_file_permission($vg_data_value["full_path"], "vgallery_nodes");
	                if(!check_mod($file_permission, 1, false))
	                    continue;

                    mod_restricted_add_menu_child(array(
                        "key"       => $vg_data_value["name"]
                        , "path"    => VG_WS_ECOMMERCE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"])
                        , "label"   => ffTemplate::_get_word_by_code("vgallery_" . $vg_data_value["name"])
                    ));

	                if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
	                    || strpos($cm->path_info, VG_WS_ECOMMERCE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"])) === 0
	                ) {
	                    if(basename($cm->path_info) == "ecommerce") {
	                        foreach($menu_structure AS $structure_key => $structure_value) {
	                            if(constant("AREA_ECOMMERCE_" . strtoupper($structure_key) . "_SHOW_MODIFY")) {
	                                ffRedirect(VG_WS_ECOMMERCE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"]) . "/ecommerce" . $structure_value . "?". $cm->query_string);
	                            }
	                        }
	                    } elseif(isset($menu_structure[basename($cm->path_info)])) {
	                        $db_manage_detail->query("SELECT * FROM vgallery_nodes WHERE ID = " . $db_manage_detail->toSql($_REQUEST["keys"]["ID"], "Number"));
	                        if($db_manage_detail->nextRecord()) {
	                            if(check_function("ecommerce_get_file_properties_ecommerce"))
	                                $ecommerce_properties = ecommerce_get_file_properties_ecommerce(stripslash($db_manage_detail->getField("parent")->getValue()) . "/" . $db_manage_detail->getField("name")->getValue(), "vgallery_nodes");
	                        }
	                        if($ecommerce_properties["enable_ecommerce"]) {
	                            foreach($menu_structure AS $structure_key => $structure_value) {
	                                if(constant("AREA_ECOMMERCE_" . strtoupper($structure_key) . "_SHOW_MODIFY")) {
	                                    
	                                    if(strpos($structure_key, "_by") !== false && strlen($ecommerce_properties["type"]) && strpos($structure_key, $ecommerce_properties["type"]) === false)
	                                        continue;

                                        mod_restricted_add_menu_sub_element(array(
                                            "key"           => $vg_data_value["name"]
                                            , "subkey"      => "ecommerce_" . $structure_key
                                            , "path"        => ffCommon_dirname($cm->path_info) . $structure_value
                                            , "label"       => ffTemplate::_get_word_by_code("ecommerce_" . $structure_key)
                                            , "params"      => "[QUERY_STRING]"
                                        ));
	                                }
	                            }
	                        } elseif(constant("AREA_ECOMMERCE_SETTINGS_SHOW_MODIFY")) {
                                mod_restricted_add_menu_sub_element(array(
                                    "key"           => $vg_data_value["name"]
                                    , "subkey"      => "ecommerce_settings"
                                    , "path"        => ffCommon_dirname($cm->path_info) . "/settings"
                                    , "label"       => ffTemplate::_get_word_by_code("ecommerce_settings")
                                    , "params"      => "[QUERY_STRING]"
                                ));
	                        } else {
	                            ffRedirect($_REQUEST["ret_url"]);
	                        }
	                    } else {
	                        /*$actual_parent = str_replace(VG_WS_ECOMMERCE . "/vgallery/" . $vg_data_value["name"], "", $cm->path_info);
	                        if($actual_parent != ffcommon_dirname($actual_parent)) {
	                            mod_restricted_add_menu_sub_element($vg_data_value["name"]
	                            , "backto"
	                            , VG_WS_ECOMMERCE . "/vgallery" . ffcommon_dirname("/" . $vg_data_value["name"] . $actual_parent)
	                            , ffTemplate::_get_word_by_code("back_to") . " " . str_replace("-", " ", basename(ffcommon_dirname("/" . $vg_data_value["name"] . $actual_parent)))
	                            , ""
	                            , null
	                            , "[QUERY_STRING]");
	                        }*/

	                        if(AREA_ECOMMERCE_SHOW_LOCATION && ECOMMERCE_CHARGE_METHOD) {
	                            $location  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["location"] : null);
	                        }
	                        
	                        $show_available  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_available"] : true);
	                        $show_unavailable  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_unavailable"] : false);
	                        $show_stock = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_stock"] : true);
	                        $show_error = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_error"] : true);

	                        if($show_available && $show_unavailable && $show_stock && $show_error) {
	                            $sSQL_cond_count = "";
	                        } else {
                                $sSQL_cond = "";
	                            if($show_available) {
	                                $sSQL_cond .= " OR (qta > 0)";
	                            }
	                            if($show_unavailable) {
	                                $sSQL_cond .= " OR (qta = 0)";
	                            }
	                            if($show_stock) {
	                                $sSQL_cond .= " OR (NOT(useunic) AND usestock)";
	                            }
	                            if($show_error) {
	                                $sSQL_cond .= " OR (qta < 0 OR ISNULL(qta))";
	                            }

	                            $sSQL_cond_count = " 0 " . $sSQL_cond;
	                        }                            
	                        
	                        $db_manage_detail->query("SELECT vgallery_nodes.name AS name
	                                                        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                                                        , ( SELECT count(*) FROM
	                                                            ( SELECT DISTINCT vgallery_nodes.ID
	                                                                , ecommerce_settings.useunic AS useunic
	                                                                , ecommerce_settings.usestock AS usestock 
	                                                                , IF(ISNULL(ecommerce_settings.ID)
	                                                                    , null
	                                                                    , ecommerce_settings.actual_qta
	                                                                ) AS qta
	                                                                , vgallery_nodes.parent AS parent
	                                                            FROM vgallery_nodes
	                                                                  LEFT JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID"
	                                                                . (AREA_ECOMMERCE_SHOW_LOCATION && ECOMMERCE_CHARGE_METHOD
	                                                                    ? ($location > 0 
	                                                                        ? " INNER JOIN ecommerce_settings_location ON ecommerce_settings_location.ID_items = vgallery_nodes.ID AND ecommerce_settings_location.ID_location = " . $db_manage_detail->toSql($location, "Number") . "
	                                                                            INNER JOIN ecommerce_location ON ecommerce_location.ID = ecommerce_settings_location.ID_location"
	                                                                        : " LEFT JOIN ecommerce_settings_location ON ecommerce_settings_location.ID_items = vgallery_nodes.ID
	                                                                            LEFT JOIN ecommerce_location ON ecommerce_location.ID = ecommerce_settings_location.ID_location"
	                                                                    )
	                                                                    : ""
	                                                                ) . "
	                                                            WHERE 
	                                                                IF(ISNULL(ecommerce_settings.ID)
	                                                                    , IF(
	                                                                            (
	                                                                                SELECT IF(ISNULL(ecommerce_settings.ID), 0, ecommerce_settings.cascading) 
	                                                                                FROM vgallery_nodes AS parent_nodes
	                                                                                    INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = parent_nodes.ID
	                                                                                WHERE vgallery_nodes.parent LIKE CONCAT(IF(parent_nodes.parent = '/', '', parent_nodes.parent), '/', parent_nodes.name, '%')
	                                                                                ORDER BY LENGTH(CONCAT(IF(parent_nodes.parent = '/', '', parent_nodes.parent))) DESC
	                                                                                LIMIT 1
	                                                                            ) > 0
	                                                                        , 1
	                                                                        , 0
	                                                                    )
	                                                                    , 1
	                                                                )
	                                                                AND IF(NOT(vgallery_nodes.is_dir > 0) OR (NOT(ISNULL(ecommerce_settings.ID)) AND (ecommerce_settings.basic_price > 0 OR NOT(ecommerce_settings.cascading) > 0))
	                                                                    , 1
	                                                                    , 0
	                                                                )
	                                                                " . (AREA_ECOMMERCE_SHOW_LOCATION && ECOMMERCE_CHARGE_METHOD && $location === "0"
	                                                                    ? " AND ISNULL(ecommerce_settings_location.ID) "
	                                                                    : ""
	                                                                ) . " 
	                                                                " . (strlen($sSQL_cond_count)
	                                                                    ? " HAVING " . $sSQL_cond_count
	                                                                    : ""
	                                                                ) . "
	                                                            ) AS tbl_src
	                                                            WHERE (tbl_src.parent = full_path OR tbl_src.parent LIKE CONCAT(full_path, '/%'))
	                                                        ) AS count_tot_elem
	                                                      FROM vgallery_nodes
	                                                      WHERE 
	                                                        vgallery_nodes.ID_vgallery = " . $db_manage_detail->toSql($vg_data_key, "Number")  . "
	                                                        AND (vgallery_nodes.is_dir > 0)
	                                                        AND CONCAT(vgallery_nodes.parent, vgallery_nodes.name) <> " . $db_manage_detail->toSql("/" . $vg_data_value["name"])  . "
	                                                        AND vgallery_nodes.parent = " . $db_manage_detail->toSql("/" . $vg_data_value["name"] /*. $actual_parent*/) . "
	                                                      ORDER BY full_path
	                                                        ");
	                        if($db_manage_detail->nextRecord()) {
	                            do {
                                    mod_restricted_add_menu_sub_element(array(
                                        "key"           => $vg_data_value["name"]
                                        , "subkey"      => $db_manage_detail->getField("name")->getValue()
                                        , "path"        => VG_WS_ECOMMERCE . "/vgallery" . $db_manage_detail->getField("full_path")->getValue()
                                        , "label"       => "(" . $db_manage_detail->getField("count_tot_elem")->getValue() . ") " .  str_replace("-", " ", $db_manage_detail->getField("name")->getValue())
                                        , "params"      => "[QUERY_STRING]"
                                    ));
	                            } while($db_manage_detail->nextRecord());
	                        }
	                    }
	                }
	            } 
	        }
	    }
	}
	//Discount
	if(AREA_ECOMMERCE_USE_COUPON || AREA_ECOMMERCE_USE_PROMOTION) {
        mod_restricted_add_menu_child(array(
            "key"       => "discount"
            , "path"    => VG_WS_ECOMMERCE . "/discount"
            , "label"   => ffTemplate::_get_word_by_code("discount")
        ));

	    if(AREA_ECOMMERCE_USE_COUPON && AREA_COUPON_SHOW_MODIFY) {
            mod_restricted_add_menu_sub_element(array(
                "key"           => "discount"
                , "subkey"      => "coupon"
                , "path"        => VG_WS_ECOMMERCE . "/discount/coupon"
                , "label"       => ffTemplate::_get_word_by_code("discount_coupon")
            ));
	    }
	}
	//Operation
	$operation_menu[] = "all";
	$db->query("SELECT DISTINCT YEAR(ecommerce_order.date) AS archive 
	                   FROM ecommerce_order
	                   ORDER BY ecommerce_order.date DESC");
	if($db->nextRecord()) {
        mod_restricted_add_menu_child(array(
            "key"       => "operations"
            , "path"    => VG_WS_ECOMMERCE . "/operations"
            , "label"   => ffTemplate::_get_word_by_code("operations")
            , "redir"   => VG_WS_ECOMMERCE . "/operations/" . $db->getField("archive", "Text", true)
        ));

	    do {
	        $operation_menu[] = $db->getField("archive")->getValue();
	    } while($db->nextRecord());
	} else {
        mod_restricted_add_menu_child(array(
            "key"       => "operations"
            , "path"    => VG_WS_ECOMMERCE . "/operations"
            , "label"   => ffTemplate::_get_word_by_code("operations")
            , "redir"   => VG_WS_ECOMMERCE . "/operations/" . "all"
        ));
	}

	foreach($operation_menu AS $operation_menu_value) {
        mod_restricted_add_menu_sub_element(array(
            "key"           => "operations"
            , "subkey"      => $operation_menu_value
            , "path"        => VG_WS_ECOMMERCE . "/operations/" . ffCommon_url_rewrite($operation_menu_value)
            , "label"       => $operation_menu_value
            , "params"      => "[QUERY_STRING]"
        ));
	}
	//Documents
	if(AREA_ECOMMERCE_SHOW_DOCUMENT) {
        mod_restricted_add_menu_child(array(
            "key"       => "documents"
            , "path"    => VG_WS_ECOMMERCE . "/documents"
            , "label"   => ffTemplate::_get_word_by_code("documents")
        ));

	    if(AREA_BILL_SHOW_MODIFY) {
	        if(AREA_ECOMMERCE_SHOW_ACTIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "bill_sent"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/bill/sent"
                    , "label"       => ffTemplate::_get_word_by_code("bill_sent")
                ));

	        if(AREA_ECOMMERCE_SHOW_PASSIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "bill_received"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/bill/received"
                    , "label"       => ffTemplate::_get_word_by_code("bill_received")
                ));
	    }
	    if(AREA_PAYMENTS_SHOW_MODIFY) {
	        if(AREA_ECOMMERCE_SHOW_PASSIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "payments_sent"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/payments/sent"
                    , "label"       => ffTemplate::_get_word_by_code("payments_sent")
                ));

	        if(AREA_ECOMMERCE_SHOW_ACTIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "payments_received"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/payments/received"
                    , "label"       => ffTemplate::_get_word_by_code("payments_received")
                ));
	    }
	    if(1) {
	        if(AREA_ECOMMERCE_SHOW_ACTIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "contracts_sent"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/contracts/sent"
                    , "label"       => ffTemplate::_get_word_by_code("contracts_sent")
                ));

	        if(AREA_ECOMMERCE_SHOW_PASSIVITY)
                mod_restricted_add_menu_sub_element(array(
                    "key"           => "documents"
                    , "subkey"      => "contracts_received"
                    , "path"        => VG_WS_ECOMMERCE . "/documents/contracts/received"
                    , "label"       => ffTemplate::_get_word_by_code("contracts_received")
                ));
	    }
	}
	//Reports
	if(AREA_ECOMMERCE_SHOW_REPORT && AREA_REPORT_SHOW_MODIFY) {
        mod_restricted_add_menu_child(array(
            "key"       => "reports"
            , "path"    => VG_WS_ECOMMERCE . "/reports"
            , "label"   => ffTemplate::_get_word_by_code("reports")
        ));

	    if(is_dir(FF_DISK_PATH . "/conf" . GALLERY_PATH_ECOMMERCE . "/reports")) {
	        $reports = glob(FF_DISK_PATH . "/conf" . GALLERY_PATH_ECOMMERCE . "/reports/*", GLOB_ONLYDIR);
	        if(is_array($reports) && count($reports)) {
	            foreach($reports AS $reports_dir) {
	                $report_dirname = ffGetFilename($reports_dir);
                    mod_restricted_add_menu_sub_element(array(
                        "key"           => "reports"
                        , "subkey"      => $report_dirname
                        , "path"        => VG_WS_ECOMMERCE . "/reports/" . $report_dirname
                        , "label"       => ffTemplate::_get_word_by_code("reports_" . strtolower($report_dirname))
                    ));
	            }
	        }
	    }
	}
	//Shipping
	if(AREA_ECOMMERCE_USE_SHIPPING && AREA_ECOMMERCE_SHIPPINGPRICE_SHOW_MODIFY) {
        mod_restricted_add_menu_child(array(
            "key"       => "shipping"
            , "path"    => VG_WS_ECOMMERCE . "/shipping"
            , "label"   => ffTemplate::_get_word_by_code("shipping")
        ));
	}

	//mod_restricted_add_menu_child("back", FF_SITE_PATH . "/", ffTemplate::_get_word_by_code("backtosite"));
	//ffErrorHandler::raise("mod_security: User Not Found!!!", E_USER_ERROR, null, get_defined_vars());             
	//$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_administration");
	//$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_notify");
	//$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_quickpanel");
	//$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_languages");

	if(check_function("system_set_media"))            
    	system_set_media($cm->oPage, $globals->settings_path);  
}

