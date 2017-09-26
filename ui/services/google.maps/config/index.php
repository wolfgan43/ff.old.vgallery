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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
    
    if (!AREA_SERVICES_SHOW_MODIFY) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 
   	
    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["key"] = "String";
	$type_field["region"] = "String";
	$type_field["scroll"] = "Boolean";
	
	$type_field["marker.limit"] = "Number";
	$type_field["marker.icon"] = "String";
	$type_field["zoom.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	$type_field["zoom.style"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(SMALL), new ffData("SMALL"))
			, array(new ffData(LARGE), new ffData("LARGE"))
			//, array(new ffData(DEFAULT), new ffData("DEFAULT"))
		)
	);
	$type_field["zoom.max"] = "Number";
	$type_field["zoom.min"] = "Number";

	$type_field["control.options"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(HORIZONTAL_BAR), new ffData("HORIZONTAL_BAR"))
			, array(new ffData(DROPDOWN_MENU), new ffData("DROPDOWN_MENU"))
			//, array(new ffData(DEFAULT), new ffData("DEFAULT"))
		)
	);
	$type_field["control.style"] = "String";

	$type_field["pan.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["scale.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["streetview.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["style"] = "TextSimple";
   
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(ffCommon_dirname(__FILE__))), $type_field);