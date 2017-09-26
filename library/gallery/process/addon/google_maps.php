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
function process_addon_google_maps($vg_father, $ID_node, $layout) {
    $cm = cm::getInstance();

    $db = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"] . "GM";
    $buffer = "";
	$tbl_src = ($vg_father["src"]["type"] != "vgallery"
		? $vg_father["src"]["table"]
		: ""
	);
    if(check_function("set_field_gmap")) { 
        $gmap_params = set_field_gmap(null, true);
        if($gmap_params)
        {
			$json_gmap_params[] = "mapTypeId: google.maps.MapTypeId.ROADMAP";
			if($gmap_params["scroll"])
				$json_gmap_params[] = "scrollwheel: true";
			else
				$json_gmap_params[] = "scrollwheel: false";
				
        	if($gmap_params["zoom.position"]) {
				$json_gmap_params[] = "zoomControl	: true";
				$json_gmap_params[] = "zoomControlOptions: {
            							style: google.maps.ZoomControlStyle." . $gmap_params["zoom.style"] . ",
	                                    position: google.maps.ControlPosition." . $gmap_params["zoom.position"] . "
	                                }";
			}
            if($gmap_params["control.options"]) {
	            $json_gmap_params[] = "mapTypeControl: true";
	            $json_gmap_params[] = "mapTypeControlOptions: {
	                                    style: google.maps.MapTypeControlStyle." . $gmap_params["control.style"] . "
	                                }";
			}
			if($gmap_params["pan.position"]) {
	            $json_gmap_params[] = "panControl: true";
	            $json_gmap_params[] = "panControlOptions: {
	                                    position: google.maps.ControlPosition." . $gmap_params["pan.position"] . "
	                                }";
			}			
			if($gmap_params["scale.position"]) {
				$json_gmap_params[] = "scaleControl: true";
				$json_gmap_params[] = "scaleControlOptions: {
            							position: google.maps.ControlPosition." . $gmap_params["scale.position"] . "
            						}";
			}
			if($gmap_params["scale.position"]) {
				$json_gmap_params[] = "streetViewControl: true";
				$json_gmap_params[] = "streetViewControlOptions: {
            							position: google.maps.ControlPosition." . $gmap_params["scale.position"] . "
	                                }";
			}
			
			if($gmap_params["style"]) 
				$json_gmap_params[] = "styles: " . $gmap_params["style"];

	        
            $sSQL = "SELECT module_maps_marker.* 
                    FROM module_maps_marker 
                    WHERE module_maps_marker.ID_node = " . $db->toSql($ID_node, "Number") . " 
                    	" . ($tbl_src
                    		? " AND module_maps_marker.tbl_src = " . $db->toSql($tbl_src)
                    		: ""
                    	) . "
                    ORDER BY IF(module_maps_marker.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . " 
                    	, 0
                    	, module_maps_marker.ID_lang
                    )";
            $db->query($sSQL);
            if($db->nextRecord()) {
            	check_function("get_vgallery_card");
                $arrMarker = array();
                do {
                    $latitude = $db->getField("coords_lat", "Text", true);
                    $longitude = $db->getField("coords_lng", "Text", true);
                    if(strlen($latitude) && strlen($longitude) && !$arrMarker[$latitude . "-" . $longitude]) {
                    	$title = $db->getField("coords_title", "Text", true);
                    	$description = $db->getField("description", "Text", true);
                        $zoom = $db->getField("coords_zoom", "Number", true);
                        if(!$zoom)
                            $zoom = 11;

	                    $arrMarker[$latitude . "-" . $longitude] = array(
	                        "title" => $title
	                        , "lat" => $latitude
	                        , "lng" =>  $longitude
	                        , "zoom" => $zoom
	                        , "description" => get_vgallery_card(
	                        	$title
	                        	, $vg_father["nodes"][$ID_node]["permalink"]
	                        	, $description
	                        	, null
	                        	, array("link" => true
	                        		, "noqrcode" => true
	                        		, "type" => "marker"
	                        		, "icons" => array(
	                        			"street-view" => "lg"
	                        		)
	                        	)
	                        )
	                    );
	                    $map_latitude = $latitude;
	                    $map_longitude = $longitude;
	                    $map_zoom = $zoom;
                    }
                } while($db->nextRecord());

                if(is_array($arrMarker) && count($arrMarker)) 
                {
                    if($gmap_params["is_gmap3"])
                        $tpl_data["base"] = "google.maps3.html";
                    else
                        $tpl_data["base"] = "google.maps.html";
                	
					//$tpl_data["custom"] = "map.html";

					$tpl_data["result"] = get_template_cascading($vg_father["user_path"], $tpl_data, "/tpl/addon");

					$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
					$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   
                
                   // $tpl = ffTemplate::factory(get_template_cascading($user_path, $filename, "/vgallery"));
                   // $tpl->load_file($filename, "main");

                    $tpl->set_var("site_path", FF_SITE_PATH);
                    $tpl->set_var("theme_inset", THEME_INSET);
                    $tpl->set_var("frontend_theme", FRONTEND_THEME);
                    $tpl->set_var("domain_inset", DOMAIN_INSET);
                    $tpl->set_var("language_inset", LANGUAGE_INSET);

                    $tpl->set_var("gmap_key", $gmap_params["key"]);
                    $tpl->set_var("gmap_region", ($gmap_params["region"] ? "&region=" . $gmap_params["region"] : ""));
                    $tpl->set_var("gmap_lang", ($gmap_params["lang"] ? "&language=" . $gmap_params["lang"] : ""));				

                    $tpl->set_var("real_name", $unic_id);
                    
                    $tpl->set_var("latitude", $map_latitude);
                    $tpl->set_var("longitude", $map_longitude);
                    $tpl->set_var("zoom", $map_zoom);
                    
                    if(is_array($json_gmap_params) && count($json_gmap_params)) 
						$tpl->set_var("gmap_params", implode(",", $json_gmap_params));


					
					$i = 0;
                    foreach($arrMarker AS $ID_marker => $value) {
                        $tpl->set_var("id_marker", $ID_marker);
                        $tpl->set_var("marker_latitude", $value["lat"]);
                        $tpl->set_var("marker_longitude", $value["lng"]);

                        $tpl->set_var("marker_title", $value["title"]);
                        if(strlen($value["description"])) {
                        	$tpl->set_var("count_marker", $i);
                            $tpl->set_var("marker_description", preg_replace(array("/\r(\s*)/", "/\n(\s*)/"), "", nl2br(htmlspecialchars_decode(htmlentities($value["description"], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES))));
                            $tpl->parse("SezMarkerDescription", false);
                            $tpl->parse("SezMarkerDesc", true);
                        }
                        $tpl->parse("SezMarker", true);
                        $tpl->set_var("SezMarkerDescription", "");
                        $i++;
                    }
                    $buffer = $tpl->rpparse("main", false);	
                }
            }
        }
    }
    return $buffer;	
}
