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
function get_file_properties($user_path, $table, $display, $ID_layout = null) {
    $db = ffDB_Sql::factory();
    $thumb_properties = null;
    if(!$ID_layout)
    	$ID_layout = 0;

    $loaded_properties = load_settings_thumb();
	$start_user_path = $user_path;
    do {
        if(isset($loaded_properties[$table][$user_path . "-" . $ID_layout][$display])) {
            $thumb_properties = $loaded_properties[$table][$user_path . "-" . $ID_layout][$display];
            break;
        } elseif($ID_layout && isset($loaded_properties[$table][$user_path . "-" . "0"][$display])) {
            $thumb_properties = $loaded_properties[$table][$user_path . "-" . "0"][$display];
            break;
		}
    } while($user_path != "/" && $user_path = ffCommon_dirname($user_path));
    if(!$thumb_properties && $table != "vgallery_nodes" && $table != "files" && strpos($start_user_path, "/" . $table) === 0) {
    	$arrUserPath = explode("/", $start_user_path);

    	if(isset($loaded_properties["vgallery_nodes"]["/" . $arrUserPath[2] . "-" . $ID_layout][$display]))
    	    $thumb_properties = $loaded_properties["vgallery_nodes"]["/" . $arrUserPath[2] . "-" . $ID_layout][$display];
		elseif($ID_layout && isset($loaded_properties["vgallery_nodes"]["/" . $arrUserPath[2] . "-" . "0"][$display]))
		 	$thumb_properties = $loaded_properties["vgallery_nodes"]["/" . $arrUserPath[2] . "-" . "0"][$display];

		if($thumb_properties)
			$user_path = "/" . $arrUserPath[2];
    }
    	
    if(!$thumb_properties)
        $thumb_properties = get_file_properties_default($table, $display);
            
    $thumb_properties["source"] = $user_path;

    return $thumb_properties;
}

function load_settings_social($ID = null) 
{
	static $loaded_properties = null;

	if(!is_array($loaded_properties)) {
        $loaded_properties = array();

        $db = ffDB_Sql::factory();

		$sSQL = "SELECT settings_thumb_social.*
				FROM settings_thumb_social
				WHERE 1
				ORDER BY ID";
		$db->query($sSQL);
		$recordset                                                                                      = $db->getRecordset();
		foreach($recordset AS $record) {
		    $ID_social                                                                                  = $record["ID"];
            $loaded_properties[$ID_social]["ID"] 													    = $ID_social;
            //Facebook Admin
            $social_tag 																			    = $record["fb:admins"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["fb:admins"]          						    = array("content" => $record[$social_tag], "type" => "property");

            //Facebook AppId
            $social_tag 																			    = $record["fb:app_id"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["fb:app_id"]           						    = array("content" => $social_tag, "type" => "property");

            //Facebook ProfileId
            $social_tag 																			    = $record["fb:profile_id"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["fb:profile_id"]       						    = array("content" => $social_tag, "type" => "property");

            //Facebook Type
            $social_tag 																			    = $record["og:type"];
            if($social_tag) {
                $loaded_properties[$ID_social]["done"]["og:type"]            						    = array("content" => $social_tag, "type" => "property");
                switch($social_tag) {
                    case "";
                        break;
                    default:
                }
            }
            //Facebook Image
            $social_tag 																			    = $record["og:image:mode"];
            if($social_tag) {
                $loaded_properties[$ID_social]["todo"]["og:image"]["fields"]    					    = $record["og:image"];
                $loaded_properties[$ID_social]["todo"]["og:image"]["mode"]      					    = $social_tag;
            }
            //Facebook Video
            $social_tag 																			    = $record["og:video"];
            if($social_tag) {
                $loaded_properties[$ID_social]["todo"]["og:video"]["fields"]    					    = $social_tag;
                $loaded_properties[$ID_social]["todo"]["og:video"]["child"]["og:video:height"]		    = array("content" => $record["og:video:height"], "type" => "property");
                $loaded_properties[$ID_social]["todo"]["og:video"]["child"]["og:video:width"]		    = array("content" => $record["og:video:width"], "type" => "property");
                $loaded_properties[$ID_social]["todo"]["og:video"]["child"]["og:video:type"]		    = array("content" => "video/mp4", "type" => "property");
            }
            //Facebook Audio
            $social_tag 																			    = $record["og:audio"];
            if($social_tag) {
                $loaded_properties[$ID_social]["todo"]["og:audio"]["fields"]           				    = $social_tag;
                $loaded_properties[$ID_social]["todo"]["og:audio"]["child"]["og:audio:type"]		    = array("content" => "audio/vnd.facebook.bridge", "type" => "property");
            }
            //Twitter Card
            $social_tag 																			    = $record["twitter:card"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["twitter:card"]     							    = array("content" => $social_tag, "type" => "name");

            //Twitter Site
            $social_tag 																			    = $record["twitter:site"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["twitter:site"]     							    = array("content" => "@" . $social_tag, "type" => "name");

            //Twitter Creator
            $social_tag 																			    = $record["twitter:creator"];
            if($social_tag)
                $loaded_properties[$ID_social]["done"]["twitter:creator"]     						    = array("content" => "@" . $social_tag, "type" => "name");

            //Twitter Image
            $social_tag 																			    = $record["twitter:image:mode"];
            if($social_tag) {
                $loaded_properties[$ID_social]["todo"]["twitter:image"]["fields"]					    = $record["twitter:image"];
                $loaded_properties[$ID_social]["todo"]["twitter:image"]["mode"] 					    = $social_tag;
            }
        }
	}

	if($ID)
		return $loaded_properties[$ID];
	else
		return $loaded_properties;
}

function load_settings_thumb()
{
	static $loaded_properties = null;

	if(!is_array($loaded_properties))
	{
        $cache = ffCache::getInstance();
        $loaded_properties = $cache->get("/vg/thumbs");

		if (!$loaded_properties)
		{
			$loaded_properties = array();

			$db = ffDB_Sql::factory();
			$sSQL = "SELECT settings_thumb.*
						, (
				            IF(settings_thumb.tbl_src = 'vgallery_nodes'
				                , ( SELECT 
				                        CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
				                    FROM vgallery_nodes
				                    WHERE vgallery_nodes.ID = settings_thumb.items
				                )
				                , IF(settings_thumb.tbl_src = 'publishing'
				                    , ( SELECT 
				                            CONCAT('/', publishing.name) AS full_path
				                        FROM publishing
				                        WHERE publishing.ID = settings_thumb.items
				                    )
				                    , IF(settings_thumb.tbl_src = 'files'
				                        , ( SELECT 
				                                CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
				                            FROM files
				                            WHERE files.ID = settings_thumb.items
				                        )
				                        , IF(settings_thumb.tbl_src = 'anagraph'
				                            , IF(settings_thumb.items > 0
				                                , (SELECT CONCAT('/', anagraph_categories.smart_url) FROM anagraph_categories WHERE anagraph_categories.ID = settings_thumb.items) 
				                                , '/'
				                            )
				                            , settings_thumb.items
				                        )
				                        
				                    )
				                )
				            )
				        ) 															AS items
						, IF(settings_thumb.thumb_display_view_mode = ''
				            , (SELECT settings_thumb_mode.name 
				                FROM settings_thumb_mode 
				                WHERE settings_thumb_mode.ID = settings_thumb.thumb_container_ID_mode
				            )
				            , (IFNULL(
				                (SELECT 
				                    IF(ISNULL(js.ID) OR NOT(js.ID_mode_thumb_v > 0)
				                        , settings_thumb_mode.name
				                        , IF(settings_thumb.tbl_src = 'vgallery'
		                        			, (SELECT settings_thumb_mode.name FROM settings_thumb_mode WHERE settings_thumb_mode.ID = js.ID_mode_thumb_v)
		                        			, (SELECT settings_thumb_mode.name FROM settings_thumb_mode WHERE settings_thumb_mode.ID = js.ID_mode_thumb_g)
				                        )
				                    ) AS ID_mode 
				                FROM js, settings_thumb_mode
				                WHERE 
				                    settings_thumb_mode.ID = settings_thumb.thumb_container_ID_mode
				                    AND js.name = settings_thumb.thumb_display_view_mode
				                )
				                , (SELECT settings_thumb_mode.name 
					                FROM settings_thumb_mode 
					                WHERE settings_thumb_mode.ID = settings_thumb.thumb_container_ID_mode)
				                )
				            )
				        ) 															AS thumb_container_mode
				        , IF(settings_thumb.preview_display_view_mode = ''
				            , (SELECT settings_thumb_mode.name 
				                FROM settings_thumb_mode 
				                WHERE settings_thumb_mode.ID = settings_thumb.preview_container_ID_mode
				            )
				            , (SELECT 
				                    IF(ISNULL(js.ID) OR NOT(js.ID_mode_preview > 0)
				                        , settings_thumb_mode.name
				                        , (SELECT settings_thumb_mode.name FROM settings_thumb_mode WHERE settings_thumb_mode.ID = js.ID_mode_preview)
				                    ) AS ID_mode 
				                FROM js, settings_thumb_mode
				                WHERE 
				                    settings_thumb_mode.ID = settings_thumb.preview_container_ID_mode
				                    AND js.name = settings_thumb.preview_display_view_mode
				            )
				        ) 															AS preview_container_mode
					FROM settings_thumb
					WHERE 1
					ORDER BY items";
			$db->query($sSQL);
			$recordset                                                                                          = $db->getRecordset();
			foreach ($recordset AS $record) {
                $tbl_src 																					    = $record["tbl_src"];
                $item 																						    = $record["items"];
                $ID_layout 																					    = $record["ID_layout"];
                $ID_thumb_social 																			    = $record["thumb_ID_social"];
                $ID_detail_social 																			    = $record["preview_ID_social"];

                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["ID"]                  		    = $record["ID"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["tblsrc"]                  	    = $tbl_src;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["item"]                  	    = $item;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["ID_layout"]                     = $ID_layout;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["sort"]          			    = $record["sort"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["sort_method"]          		    = $record["sort_method"];
                //$loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["last_update"]              	= $record["last_update"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["max_upload"]                    = $record["max_upload"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["max_items"]                     = $record["max_items"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["allow_insert_dir"]              = $record["allow_insert_dir"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["allow_insert_file"]             = $record["allow_insert_file"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"]["hide_dir"]                      = $record["hide_dir"];

                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]								    = $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["container_ID_mode"]            = $record["thumb_container_ID_mode"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["hide"]          			    = $record["thumb_hide"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["container_mode"]               = preg_replace('/[^a-zA-Z0-9\_]/', '', $record["thumb_container_mode"]);
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["item_size"]                    = (strlen($record["thumb_item"]) ? explode(",", $record["thumb_item"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["wrap"]						    = (strlen($record["thumb_wrap"]) ? explode(",", $record["thumb_wrap"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["fluid"]                        = $record["thumb_fluid"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["default_grid"]                 = (strlen($record["thumb_grid"]) ? explode(",", $record["thumb_grid"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["default_extra"]                = (strlen($record["thumb_extra"]) ? explode(",", $record["thumb_extra"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["default_extra_class"]   	    = array(
                                                                                                                    "left" => $record["thumb_extra_class_left"]
                                                                                                                    , "right" => $record["thumb_extra_class_right"]
                                                                                                                );
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["default_extra_location"]       = $record["thumb_extra_location"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["default_class"]			    = $record["thumb_class"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["rec_per_page"]                 = $record["thumb_rec_per_page"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["rec_per_page_all"]             = $record["thumb_rec_per_page_all"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["npage_per_frame"]              = $record["thumb_npage_per_frame"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["direction_arrow"]              = $record["thumb_direction_arrow"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["frame_arrow"]                  = $record["thumb_frame_arrow"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["custom_page"]                  = $record["thumb_custom_page"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["tot_elem"]                     = $record["thumb_tot_elem"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["frame_per_page"]               = $record["thumb_frame_per_page"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["pagenav_location"]             = $record["thumb_pagenav_location"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["infinite"]                     = $record["thumb_pagenav_infinite"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["alphanum"]                     = $record["thumb_pagenav_alphanum"];

                //Image Thumb Settings
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["image"]["fields"]       	    = (strlen($record["thumb_image"]) ? explode(",", $record["thumb_image"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["image"]["link_to"]             = $record["thumb_image_detail"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["plugin"]["name"]        	    = $record["thumb_display_view_mode"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["plugin"]["class"]    		    = preg_replace('/[^a-zA-Z0-9\-]/', '', $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["plugin"]["name"]);

                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["image"]["src"]                 = get_image_properties_by_grid_system(
                    $record["thumb_ID_image"]
                                                                                                                    , $record["thumb_ID_image_md"]
                                                                                                                    , $record["thumb_ID_image_sm"]
                                                                                                                    , $record["thumb_ID_image_xs"]
                                                                                                                );

                if($ID_thumb_social)
                    $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["thumb"]["social"] 				    = load_settings_social($ID_thumb_social);

                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]							    = $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["base"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["container_ID_mode"]    	    = $record["preview_container_ID_mode"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["hide"]          			    = $record["preview_hide"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["container_mode"]         	    = preg_replace('/[^a-zA-Z0-9\_]/', '', $record["preview_container_mode"]);
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["item_size"]              	    = 1;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["wrap"]					    = "";
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["fluid"]                  	    = $record["preview_fluid"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["default_grid"]           	    = (strlen($record["preview_grid"]) ? explode(",", $record["preview_grid"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["default_extra"]          	    = (strlen($record["preview_extra"]) ? explode(",", $record["preview_extra"]) : "");
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["default_extra_class"]   	    = array(
                                                                                                                    "left" => $record["preview_extra_class_left"]
                                                                                                                    , "right" => $record["preview_extra_class_right"]
                                                                                                                );
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["default_extra_location"] 	    = $record["preview_extra_location"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["default_class"]			    = $record["preview_class"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["rec_per_page"]           	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["rec_per_page_all"]       	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["npage_per_frame"]        	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["direction_arrow"]        	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["frame_arrow"]            	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["custom_page"]            	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["tot_elem"]               	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["frame_per_page"]         	    = 0;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["pagenav_location"]       	    = "";
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["infinite"]                    = false;
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["alphanum"]       			    = "";

                //Image Detail Settings
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["image"]["fields"]    		    = (strlen($record["preview_image"])
                                                                                                                    ? explode(",", $record["preview_image"])
                                                                                                                    : ""
                                                                                                                );
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["image"]["link_to"]       	    = $record["preview_image_detail"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["plugin"]["name"]        	    = $record["preview_display_view_mode"];
                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["plugin"]["class"]             = preg_replace('/[^a-zA-Z0-9\-]/', '', $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["plugin"]["name"]);

                $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["image"]["src"]                = get_image_properties_by_grid_system(
                                                                                                                    $record["preview_ID_image"]
                                                                                                                    , $record["preview_ID_image_md"]
                                                                                                                    , $record["preview_ID_image_sm"]
                                                                                                                    , $record["preview_ID_image_xs"]
                                                                                                                );

                if($ID_detail_social)
                    $loaded_properties[$tbl_src][$item . "-" . $ID_layout]["detail"]["social"] 			        = load_settings_social($ID_detail_social);

                if(!isset($loaded_properties[$tbl_src][$item . "-0"]))
                    $loaded_properties[$tbl_src][$item . "-0"]                                                  = $loaded_properties[$tbl_src][$item . "-" . $ID_layout];
            }

            $cache->set("/vg/thumbs", $loaded_properties);
		}
	}

	return $loaded_properties;
}

function get_file_properties_default($table, $mode) {
	$default_properties = array();
	switch($mode) {
		case "detail":
			$default_properties["ID"]                  		= 0;
	        $default_properties["tblsrc"]                  	= "";
	        $default_properties["item"]                  	= null;
	        $default_properties["ID_layout"]                = 0;
	        $default_properties["sort"]              		= 0;
	        $default_properties["sort_method"]              = "";
	        //$default_properties["container_ID_mode"]        = 3;
	        $default_properties["hide"]       				= false;
	        $default_properties["container_mode"]           = "View";
	        $default_properties["item_size"]                = array(1,1,1,1);
			$default_properties["wrap"]                     = array(0,0); 
	        $default_properties["fluid"]                    = 0; 
	        $default_properties["default_grid"]             = false; 
	        $default_properties["default_extra"]            = false; 
	        $default_properties["default_extra_class"]      = null; 
	        $default_properties["default_extra_location"]   = 0; 
			$default_properties["default_class"]			= "";
	        $default_properties["rec_per_page"]             = 0;
	        $default_properties["npage_per_frame"]          = 0;
	        $default_properties["direction_arrow"]          = 0;
	        $default_properties["frame_arrow"]              = 0;
	        $default_properties["custom_page"]              = 0;
	        $default_properties["tot_elem"]                 = 0;
	        $default_properties["frame_per_page"]           = 0;
	        $default_properties["pagenav_location"]         = "bottom";
            $default_properties["infinite"]                 = false;
            $default_properties["alphanum"]         		= "";
	        $default_properties["image"]                 	= array(
	        													"fields" 			=> ($table == "files" ? true : "")
	        													, "link_to"			=> "image"
	        													, "src" 			=> null
	        												);
	        $default_properties["plugin"]                 = array(
	        													"name" => ""
	        													, "class" => ""
	        												);
			$default_properties["social"]              		= null;
			break;
		case "thumb":
	        $default_properties["ID"]                  		= 0;
	        $default_properties["tblsrc"]                  	= "";
	        $default_properties["item"]                  	= null;
	        $default_properties["ID_layout"]                = 0;
	        $default_properties["sort"]              		= 0;
	        $default_properties["sort_method"]              = "";
	        //$default_properties["container_ID_mode"]        = 8;
	        $default_properties["hide"]        				= false;
	        $default_properties["container_mode"]           = "ListDiv";
	        $default_properties["item_size"]                = array(1,1,1,1);
			$default_properties["wrap"]						= array(0,0);
	        $default_properties["fluid"]                    = 0;
			$default_properties["default_grid"]             = false;
	        $default_properties["default_extra"]            = false;
	        $default_properties["default_extra_class"]      = null; 
	        $default_properties["default_extra_location"]   = 0;
			$default_properties["default_class"]			= "";
	        $default_properties["rec_per_page"]             = 12; 
	        $default_properties["npage_per_frame"]          = 9;
	        $default_properties["direction_arrow"]          = 1;
	        $default_properties["frame_arrow"]              = 0;
	        $default_properties["custom_page"]              = 0;
	        $default_properties["tot_elem"]                 = 0;
	        $default_properties["frame_per_page"]           = 4;
	        $default_properties["pagenav_location"]         = "bottom";
            $default_properties["infinite"]                 = false;
            $default_properties["alphanum"]         		= "";
	        $default_properties["image"]                 	= array(
	        													"fields" 			=> ($table == "files" ? true : "")
	        													, "link_to" 		=> ($table == "files" ? "image" : "content")
	        													, "src" 			=> null
	        												);
	        $default_properties["plugin"]                 	= array(
	        													"name" => ""
	        													, "class" => ""
	        												);
	        $default_properties["social"]              		= null;
	        break;
	    default:
    }

	return $default_properties;
}

function get_image_default() {
	$default_image = array();
	$default_image["ID"] 							= null;
	$default_image["name"] 							= "";
	$default_image["dim_x"] 						= 0;
	$default_image["dim_y"] 						= 0;
	$default_image["max_x"] 						= 0;
	$default_image["max_y"] 						= 0;
    $default_image["bgcolor"]               		= "FFFFFF";
	$default_image["format"]              			= "jpg";
    $default_image["transparent"]              		= false;
    $default_image["alpha"]                    		= 0;
    $default_image["alignment"]                    	= "center";
    $default_image["frame_size"]               		= 0;
    $default_image["frame_color"]              		= "FFFFFF";
    $default_image["resize"]                   		= true;
    $default_image["mode"]                     		= "proportional";
    $default_image["word_color"]               		= "000000";
    $default_image["word_size"]                		= 9;
    $default_image["word_type"]                		= "times.ttf";
    $default_image["word_align"]               		= "center";
    $default_image["enable_thumb_word_dir"]    		= true;
    $default_image["enable_thumb_word_file"]   		= false;
    $default_image["last_update"]              		= "";
    $default_image["max_upload"]               		= 1800000;
    $default_image["force_icon"]               		= "";
    $default_image["allowed_ext"]              		= "";
    $default_image["max_items"]                		= 0;
    $default_image["allow_insert_dir"]         		= "0"; 
    $default_image["allow_insert_file"]        		= "1";
    $default_image["hide_dir"]                 		= "1";
    $default_image["enable_thumb_image_dir"]   		= false;
    $default_image["enable_thumb_image_file"]  		= false;
    $default_image["wmk_image"]              		= "";
    $default_image["wmk_alignment"]              	= "center";
    $default_image["wmk_alpha"]              		= 127;
    $default_image["wmk_mode"]              		= "proportional";
	
	return $default_image;
}

function get_image_properties_by_grid_system($image_default, $image_md, $image_sm, $image_xs) {
	$resolution = Cms::getInstance("frameworkcss")->getResolution();
	$keys = array();

	if($image_default) {
		$arrImageMode = ffMedia::getModes($image_default);
		$res["default"]["ID"] 			= $image_default;
		$res["default"]["name"] 		= $arrImageMode["name"];
		$res["default"]["width"] 		= $arrImageMode["dim_x"];
		$res["default"]["height"]		= $arrImageMode["dim_y"];
		$res["default"]["format"]		= $arrImageMode["format"];
		$res["default"]["force_icon"]	= $arrImageMode["force_icon"];
		
		$keys[$image_default] = $image_default;
	}
	
	
	if(count($resolution)) {
		$image_md 		= ($image_default && !$image_md ? null : $image_md);
		$image_sm 		= ($image_default && !$image_sm ? null : $image_sm);
		$image_xs 		= ($image_default && !$image_xs ? null : $image_xs);

		if($image_md || $image_sm || $image_xs) {
			$count_resolution = 0;
			if(isset($resolution[$count_resolution])) {
				if($image_md) {
					$arrImageMode = ffMedia::getModes($image_md);
					$res[$resolution[$count_resolution]]["ID"] 			= $image_md;
					$res[$resolution[$count_resolution]]["name"] 		= $arrImageMode["name"];
					$res[$resolution[$count_resolution]]["width"] 		= $arrImageMode["dim_x"];
					$res[$resolution[$count_resolution]]["height"] 		= $arrImageMode["dim_y"];
					$res[$resolution[$count_resolution]]["format"]		= $arrImageMode["format"];
					$res[$resolution[$count_resolution]]["force_icon"]	= $arrImageMode["force_icon"];
					
					$keys[$image_md] = $image_md;
				} elseif($image_md === null)
					$res[$resolution[$count_resolution]] 				= $res["default"];
			}
						
			$count_resolution++;
			if(isset($resolution[$count_resolution])) {
				if($image_sm) {
					$arrImageMode = ffMedia::getModes($image_sm);
					$res[$resolution[$count_resolution]]["ID"] 			= $image_sm;
					$res[$resolution[$count_resolution]]["name"] 		= $arrImageMode["name"];
					$res[$resolution[$count_resolution]]["width"] 		= $arrImageMode["dim_x"];
					$res[$resolution[$count_resolution]]["height"] 		= $arrImageMode["dim_y"];
					$res[$resolution[$count_resolution]]["format"]		= $arrImageMode["format"];
					$res[$resolution[$count_resolution]]["force_icon"]	= $arrImageMode["force_icon"];
					
					$keys[$image_sm] = $image_sm;
				} elseif($image_sm === null)
					$res[$resolution[$count_resolution]] 				= $res["md"];
			}

			$count_resolution++;
			if(isset($resolution[$count_resolution])) {
				if($image_xs) {
					$arrImageMode = ffMedia::getModes($image_xs);
					$res[$resolution[$count_resolution]]["ID"] 			= $image_xs;
					$res[$resolution[$count_resolution]]["name"] 		= $arrImageMode["name"];
					$res[$resolution[$count_resolution]]["width"] 		= $arrImageMode["dim_x"];
					$res[$resolution[$count_resolution]]["height"] 		= $arrImageMode["dim_y"];
					$res[$resolution[$count_resolution]]["format"]		= $arrImageMode["format"];
					$res[$resolution[$count_resolution]]["force_icon"]	= $arrImageMode["force_icon"];
					
					$keys[$image_xs] = $image_xs;
				} elseif($image_xs === null)
					$res[$resolution[$count_resolution]] = $res["sm"];
			}
		}
	}

	if(count($keys))
		$res["key"] = implode("-", $keys);

	return $res;
}