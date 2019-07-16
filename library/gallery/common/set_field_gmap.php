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
	function set_field_gmap($component = null, $only_maps = false) {
		$cm = cm::getInstance();

		if(check_function("get_webservices")) {
		
			$services_params = get_webservices("google.maps");
			 
			if($services_params["enable"] && strlen<($services_params["key"])) {
                if($only_maps) {

                } else {
                    $cm->oPage->tplAddJs("MarkerClusterer"
                        , array(
                            "file" => "markerclusterer.js"
                            , "path" => "/themes/library/plugins/gmap3.markerclusterer"
                    ));

                    if($services_params["region"])
                        $component->gmap_region											= $services_params["region"];

                    //marker
                    if($services_params["marker.limit"])
                        $component->gmap3_marker_limit									= $services_params["marker.limit"];
                    if($services_params["marker.icon"])
                        $component->gmap3_marker_icon									= $services_params["marker.icon"];

                    //zoom
                    if($services_params["zoom.position"]) {
                        $component->gmap3_zoom_control									= true;
                        $component->gmap3_zoom_control_position							= $services_params["zoom.position"];
                        if($services_params["zoom.style"])
                            $component->gmap3_zoom_control_style						= $services_params["zoom.style"];

                        if($services_params["zoom.max"])
                            $component->gmap3_max_zoom									= $services_params["zoom.max"];
                        if($services_params["zoom.min"])
                            $component->gmap3_min_zoom									= $services_params["zoom.min"];
                    }

                    //control
                    if($services_params["control.options"]) {
                        $component->gmap3_map_type_control								= true;
                        $component->gmap3_map_type_control_options						= $services_params["control.options"];
                    }
                    //control style
                    if($services_params["control.style"]) {
                        $component->gmap3_map_type_control_enable_your_style			= true;
                        $component->gmap3_map_type_control_your_style_name				= $services_params["control.style"];
                    }

                    //pan
                    if($services_params["pan.position"]) {
                        $component->gmap3_pan_control									= true;
                        $component->gmap3_pan_control_position							= $services_params["pan.position"];
                    }

                    //scale
                    if($services_params["scale.position"]) {
                        $component->gmap3_scale_control									= true;
                        $component->gmap3_scale_control_position						= $services_params["scale.position"];
                    }

                    //streetview
                    if($services_params["streetview.position"]) {
                        $component->gmap3_streetview_control							= true;
                        $component->gmap3_streetview_control_position					= $services_params["streetview.position"];
                    }

                    //style
                    if($services_params["style"]) {
                        $component->gmap3_personal_style								= true;
                        $component->gmap3_personal_style_text							= $services_params["style"];
                    }
                }
			}		

			if($component !== null)
			{
				switch($component->widget) {
					case "gmap":
                    case "gmap3":
                        $component->widget = "gmap3";
                        break;
					default:
				}
				$component->gmap_key = $services_params["key"];
			} 
		}
		
		if($component === null) {
			$component = $services_params;
			$component["key"] = $services_params["key"];
			$component["lang"] = strtolower(substr(LANGUAGE_INSET, 0, -1));
			$component["is_gmap3"] = true;
		}

		return $component;
	}