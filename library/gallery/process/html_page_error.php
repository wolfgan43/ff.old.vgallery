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
function process_html_page_error($http_response = null, $redirect = null, $error = null) {

	if($redirect && check_function("system_gallery_redirect"))
		system_gallery_redirect($redirect);

	$params["template"] = "error_document.html";

	$http_response_label = ($http_response
		? (is_numeric($http_response)
			? ffTemplate::_get_word_by_code("http_response_" . $http_response . "_title")
			: $http_response
		)
		: ffTemplate::_get_word_by_code("http_response_empty_title")
	);
	if($error) {
		Cache::log((is_array($error) ? implode(", ", $error) : $error), "error_block_notfound");
	}
	if($http_response && is_numeric($http_response)) {
		$params["icon"] = false;
	}

	return process_html_notify("info", $http_response_label, ffTemplate::_get_word_by_code("http_response_description"), $params);
}



function process_html_notify($type, $title = null, $message = null, $params = null) {
	$static_page = "notify.html";
	if(!$title)
		$title = ffTemplate::_get_word_by_code("http_notify_title");
	if(!$message)
		$message = ffTemplate::_get_word_by_code("http_notify_description");
		
	switch($type) {
		case "success":
			$container_class = "success";
			$callout = "success";
			$icon = "check";
			break;
		case "warning":
			$container_class = "warning";
			$callout = "danger";
			$icon = "times-circle";
			break;
		case "info":
		default:
			$container_class = "error";
			$callout = "info";
			$icon = "warning";
	}


	$framework_css = array(
		"template" => $static_page
		, "container" => array(
			"class" => $container_class . " nopadding"
			, "row" => true
		)
		, "inner" => array(
			"callout" => $callout
			, "util" => "clear"
		)
		, "image" => array(
			"col" => array(
				"xs" => 0
				, "sm" => 0
				, "md" => 4
				, "lg" => 4
			)
			, "util" => "align-right"
		)
		, "content" => array(
			"col" => array(
				"xs" => 12
				, "sm" => 12
				, "md" => 8
				, "lg" => 8
			)
		)
		, "icon" => array(
			"name" => $icon
			, "params" => "12x"
		)
	);
	
	if($params)
		$framework_css = array_replace_recursive($framework_css, $params);

    $tpl_data["custom"] = $framework_css["template"];
    $tpl_data["base"] = $static_page;

    $tpl_data["result"] = get_template_cascading("/", $tpl_data);		

 	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
    $tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   

/*    		
    $tpl_data = get_template_cascading("/", $framework_css["template"]);
	if(is_array($tpl_data)) {
		$tpl = ffTemplate::factory($tpl_data["path"]);
	    $tpl->load_file($framework_css["template"][$tpl_data["type"]], "main");    
	} else {
		$tpl = ffTemplate::factory($tpl_data);
		$tpl->load_file($framework_css["template"], "main");
	}
*/
	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", THEME_INSET); 
	$tpl->set_var("domain_inset", DOMAIN_INSET);
	$tpl->set_var("language_inset", LANGUAGE_INSET);

	$tpl->set_var("container_class", cm_getClassByDef($framework_css["container"]));
	$tpl->set_var("inner_class", cm_getClassByDef($framework_css["inner"]));
	
	if($framework_css["image"]) {
		$tpl->set_var("image_class", cm_getClassByDef($framework_css["image"]));
		if($framework_css["icon"]) {
			$image = cm_getClassByFrameworkCss($framework_css["icon"]["name"], "icon-tag", $framework_css["icon"]["params"]);
		} else {
			$image = '<img src="' . FF_SITE_PATH . '/themes/' . THEME_INSET . '/images/error-pages/' . http_response_code() . '.png" />';
		}
		$tpl->set_var("image", $image);
		$tpl->parse("SezImage", false);
	}

	if($framework_css["content"]) {
		$tpl->set_var("content_class", cm_getClassByDef($framework_css["content"]));
		$tpl->set_var("error_title", $title);
		$tpl->set_var("error_description", $message);

		$tpl->parse("SezContent", false);
	}

	return $tpl->rpparse("main", false);
}
