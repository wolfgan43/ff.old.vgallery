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
function process_addon_google_docs($user_path, $google_type, $google_key, $layout) {
	switch($google_type) {
	    case "wise":
	        $google_docs_service = "spreadsheets.google.com";
	        break;
	    default:
	        
	}

	$google_docs_mode = "/ccc";
	$google_docs_lang = strtolower(substr(LANGUAGE_INSET, 0, -1));
						
	if(strlen($google_docs_service) && strlen($google_key)) {
		//$tpl_data["custom"] = "google.docs.html";
		$tpl_data["base"] = "google.docs.view.html";

		$tpl_data["result"] = get_template_cascading($user_path, $tpl_data, "/tpl/addon");

		$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
		//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
        $tpl->load_file($tpl_data["result"]["name"], "main");

		//$tpl = ffTemplate::factory(get_template_cascading($user_path, "google.docs.view.html", "/vgallery"));
		//$tpl->load_file("google.docs.view.html", "main");

	    $tpl->set_var("site_path", FF_SITE_PATH);
	    $tpl->set_var("domain_inset", DOMAIN_INSET);
	    $tpl->set_var("theme_inset", THEME_INSET);

		$tpl->set_var("service", $google_docs_service);
		$tpl->set_var("mode", $google_docs_mode);
		$tpl->set_var("key", $google_key);
		$tpl->set_var("lang", $google_docs_lang);
		
	    $buffer = $tpl->rpparse("main", false);
	} else {
		$buffer = "";	
	}
	
	return $buffer;	
}
