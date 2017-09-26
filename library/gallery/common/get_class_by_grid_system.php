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
function get_class_by_grid_system($data, $mode, $res = null) {
	$cm = cm::getInstance();
	
	$framework_css = cm_getFrameworkCss();
	switch($mode) {
		case "wrap":
			switch ($data) {
			    case -1:
					$res = true;
				break;
			    case 1:
					$res = cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : ""));
				break;
			    case 2:
					$res = cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid"));
				break;
			    default:
					$res = false;
			}		
			break;
		case "extra":
			if($data && is_array($data["grid"]) && count($data["grid"])) {
				foreach ($data["grid"] AS $label_col_key => $label_col_value) {
					$arrColumnControl[$label_col_key] = 12 - ($label_col_value == 12 ? 0 : $label_col_value);
				}

				$res["img"] = cm_getClassByFrameworkCss($data["grid"], "col", $data["class"]["left"]);
				$res["desc"] = cm_getClassByFrameworkCss($arrColumnControl, "col", $data["class"]["right"]);		
			}
			$res["location"] = ($data && $data["location"] ? "Bottom" : "Top");			
			break;
		default:
		    if (strlen($data["class"]))
			    $res["custom"] = $data["class"];

			if(isset($data["fluid"])) {
			    switch ($data["fluid"]) {
					case -1:
						$fluid = ($framework_css["is_fluid"] ? "-fluid" : "");
					    $res["grid"] = false;
					    $res["grid_alt"] = cm_getClassByFrameworkCss("", "row" . $fluid, "col");
					    break;
					case -2:
					    $fluid = ($framework_css["is_fluid"] ? "" : "-fluid");
					    $res = false;
					    $res["grid_alt"] = cm_getClassByFrameworkCss("", "row" . $fluid, "col");
					    break;
					case -3:
					    $fluid = false;
					    //$res["hide"] = true;
					    break;
					case 1:
					    $fluid = ($framework_css["is_fluid"] ? "-fluid" : "");
					    $res["grid"] = false;
					    $res["grid_alt"] = false;
					    break;
					case 2:
					    $fluid = ($framework_css["is_fluid"] ? "-fluid" : "");
					    $res["grid"] = cm_getClassByFrameworkCss($data["grid"], "col", array("skip-prepost" => true));
					    $res["grid_alt"] = false;
					    break;
					default:
					    $fluid = ($framework_css["is_fluid"] ? "-fluid" : "");
					    if($data["grid"])
							$res["grid"] = cm_getClassByFrameworkCss($data["grid"], "col");
						else
							$res["grid"] = false;
						$res["grid_alt"] = false;
			    }

			    if(!is_array($res))
			    	$res = array();
			}
	}
	return $res;
}  

function get_class_by_grid_system_def($def, $res = array(), $output = null) {
	if(is_array($def)) {
		$res["class"] = get_class_by_grid_system($def["items"], "grid");
		if($output === true && is_array($res["class"]))
			$res["class"] = implode(" ", array_filter($res["class"]));

		if (is_array($def["wrap"])) {  
			$res["wrap"]["container"] = get_class_by_grid_system($def["wrap"][0], "wrap");
			$res["wrap"]["row"] = get_class_by_grid_system($def["wrap"][1], "wrap");
		} elseif(is_numeric($def["wrap"])) {
			$res["wrap"] = get_class_by_grid_system($def["wrap"], "wrap");
		}
		if(array_key_exists("extra", $def))
			$res["field"] = get_class_by_grid_system($def["extra"], "extra");

		if (is_array($def["container"])) {
			$res["container_class"] = get_class_by_grid_system($def["container"], "grid");
			if($output === true && is_array($res["container_class"]))
				$res["container_class"] = implode(" ", array_filter($res["container_class"]));
		}
	}	
	return $res;
}


function get_class_layout_by_grid_system($type = null, $class = null, $fluid = null, $col = null, $wrap = null, $width = null, $res = array()) {
    $cm = cm::getInstance();

    if($type)
    	$res["class"]["type"] = $type;
    	
    if($res["name"])
        $res["class"]["default"] = $res["name"];
    
    if($class)
        $res["class"]["custom"] = $class;

/*
    if($class)
        $res["class"]["default"] = $class;
    elseif($res["name"])
        $res["class"]["default"] = $res["name"];
*/
        
    if($sections["C" . $ID_section]["layouts"][$unic_id]["fluid"] === 0
        && is_array($col)
        && (
            $col[0]
            + $col[1]
            + $col[2]
            + $col[3]
        ) == 0
    ) {
        $col = null;
        $fluid = 1;
    }        
        
    $framework_css = cm_getFrameworkCss();
    if(is_array($framework_css)) {
        $res["grid_isset"] = true;                    
        $res["fluid_params"] = array();
        switch($fluid) {
            case -1:
                $res["fluid"] = ($framework_css["is_fluid"] ? "-fluid" : "");
                $res["grid_isset"] = false;
                break;
            case -2:        
                $res["fluid"] = ($framework_css["is_fluid"] ? "" : "-fluid");
                $res["grid_isset"] = false;
                break; 
            case -3:
                $res["fluid"] = ($framework_css["is_fluid"] ? "" : "-fluid");
                $res["hide"] = true;
                break;                 
            case 1:
                $res["fluid"] = ($framework_css["is_fluid"] ? "-fluid" : "");
                $res["grid_isset"] = null;
                break;
            case 2:
                $res["fluid"] = ($framework_css["is_fluid"] ? "-fluid" : "");
                $res["fluid_params"]["skip-prepost"] = true;
                break;
            default:
                $res["fluid"] = ($framework_css["is_fluid"] ? "-fluid" : "");
        }

        switch($wrap) {
            case -1:
                $res["wrap"] = true;
                break;
            case 1:
                $res["wrap"] = "";
                break;
            case 2:
                $res["wrap"] = "-fluid";
                break;
            default:
                $res["wrap"] = false;
        }
        //$res["fluid"] = $layer_value["fluid"];
        
        //if($sections["C" . $section_key]["count_block_visible"] > 1 && $framework_css["is_fluid"] && strlen($layer_fluid))
        //    $res["fluid"] = "";

        if($res["grid_isset"]) {
            if($col) {
                $res["class"]["grid"] = cm_getClassByFrameworkCss(
                            $col
                            , "col" . $res["fluid"] 
                            , $res["fluid_params"]
                        );
            } else {
                $res["grid_isset"] = false;
                
                $row = cm_getClassByFrameworkCss("", "row" . $res["fluid"]);
            }
        } elseif($res["grid_isset"] === false) {
            $row = cm_getClassByFrameworkCss("", "row" . $res["fluid"]);
        }                

		if($row) {
			$res["class"]["grid_alt"] = $row;		
			unset($res["class"]["grid"]);
		} else {
			unset($res["class"]["grid_alt"]);
		}        

        if($res["wrap"] !== false) {
        	$wrap = array("wrap");
            if($res["wrap"] !== true)
            	$wrap[] = cm_getClassByFrameworkCss("", "wrap" . $res["wrap"]);

            $res["wrap"] = implode(" ", $wrap);    
        }
    } elseif($width) {
        if(strpos($width, "%") === false) {
            if(strpos($width, "px") === false) {
                $sign = "%"; 
            } else {
                $width = str_replace("px", "", $width);
                $sign = "px";
            }
        } else {
            $width = str_replace("%", "", $width);
            $sign = "%";
        }

        $res["width"] = $width;
        $res["sign"] = $sign;
        $res["wrap"] = false;
    }
    
    return $res;
}
