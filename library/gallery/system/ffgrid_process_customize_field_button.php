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
function system_ffgrid_process_customize_field_button($component, $area, $params = null, $field = array()) {
	$cm = cm::getInstance();
	
	$cm->oPage->widgetLoad("dialog");	
	
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "customize";
	$oButton->label = ffTemplate::_get_word_by_code("customize_fields");
    $oButton->action_type = "submit";
    $oButton->url = "";
    $oButton->aspect = "link";
   	if(is_array($field)) {
		foreach($field AS $field_key => $field_value) {
			$str_field .= "&field[" . $field_key . "]=" . urlencode($field_value);

		}
   	}
   	if(is_array($params)) {
		foreach($params AS $params_key => $params_value) {
			$str_params .= "&params[" . $params_key . "]=" . urlencode($params_value);

		}
   	} elseif(strlen($params)) {
		$str_params = "ID=" . urlencode($params);
   	}
   
	$cm->oPage->widgets["dialog"]->process(
	     $component->id . "_customize_fields_" . $oButton->id
	     , array(
	        "tpl_id" => $component->id
	        //"name" => "myTitle"
	        , "url" => get_path_by_rule("services", "restricted") . "/display-field/" . $area
	                . "?resources=" . urlencode(implode(",", $component->resources))
	                . $str_field
					. $str_params
	                . "&" . $component->addit_record_param
	                . "ret_url=" . urlencode($cm->oPage->getRequestUri())
	        , "title" => ffTemplate::_get_word_by_code("customize_fields")
	        , "callback" => ""
	        , "class" => ""
	        , "params" => array()
	    )
	    , $cm->oPage
	);

	$oButton->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_customize_fields_" . $oButton->id . "')";
	$component->addActionButtonHeader($oButton);
}
