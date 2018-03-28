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
	// $globals : globals settings
    // $actual_srv = params defined by system

	$force_company_data = false;
	if($actual_srv["force_compilation"] && get_session("UserID") != MOD_SEC_GUEST_USER_NAME) {
		if(is_array($actual_srv) && count($actual_srv)) {
			foreach($actual_srv AS $actual_srv_key => $actual_srv_value) {
				if($actual_srv_key == "enable"
					|| $actual_srv_key == "enable_international"
					|| $actual_srv_key == "force_compilation"
				) {
					continue;
				}
				
				if(!strlen($actual_srv_value)) {
					$count_empty_data++;
				}
			}
			if(count($actual_srv) - $count_empty_data <= 3) {
				$force_company_data = true;
			}
		}
	}
	
	if($force_company_data) {
		require_once(ffCommon_dirname(__FILE__) . "/config/index." . FF_PHP_EXT);
	} else {
	    if($actual_srv["enable_international"]) {
			$globals = ffGlobals::getInstance("gallery");
			if(!is_array($globals->template["vars"]))
				$globals->template["vars"] = array();
			
			if(is_array($actual_srv) && count($actual_srv)) {
				foreach($actual_srv AS $actual_srv_key => $actual_srv_value) {
					$globals->template["vars"]["companydata_" . $actual_srv_key] = $actual_srv_value;
				}
			}
	    } else {
		    if(
    			(
			        (isset($actual_srv["force_tpl"]) && strlen($actual_srv["force_tpl"]))
		        )
		        && (isset($actual_srv["company_name"]) && strlen($actual_srv["company_name"]))
		        && (isset($actual_srv["address"]) && strlen($actual_srv["address"]))
		        && (isset($actual_srv["cap"]) && strlen($actual_srv["cap"]))
		        && (isset($actual_srv["city"]) && strlen($actual_srv["city"]))
		        && (isset($actual_srv["tel"]) && strlen($actual_srv["tel"]))
		    ) {
				if(isset($actual_srv["force_tpl"]) && strlen($actual_srv["force_tpl"])) {
					$template = "_" . $actual_srv["force_tpl"];
				} elseif(isset($actual_srv["tpl"]) && strlen($actual_srv["tpl"])) {
    				$template = "_" . $actual_srv["tpl"];
				}
				
		        $tpl = ffTemplate::factory(__DIR__);
		        $tpl->load_file("company.data" . $template . ".html", "main");
		        

		        $tpl->set_var("company_name", $actual_srv["company_name"]);
		        if(isset($actual_srv["label"]) && $actual_srv["label"])
        			$tpl->set_var("label_company_name", ffTemplate::_get_word_by_code("services_label_company_name"));
		        
		        if(isset($actual_srv["cf"])) {
			        if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_cf", ffTemplate::_get_word_by_code("services_label_cf"));

		            $tpl->set_var("cf", $actual_srv["cf"]);
				}
		        
		        
		        if(isset($actual_srv["piva"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_piva", ffTemplate::_get_word_by_code("services_label_piva"));

		            $tpl->set_var("piva", $actual_srv["piva"]);
				}
		        
		        $tpl->set_var("address", $actual_srv["address"]);
			    if(isset($actual_srv["label"]) && $actual_srv["label"])
        			$tpl->set_var("label_address", ffTemplate::_get_word_by_code("services_label_address"));

		        $tpl->set_var("cap", $actual_srv["cap"]);
			    if(isset($actual_srv["label"]) && $actual_srv["label"])
        			$tpl->set_var("label_cap", ffTemplate::_get_word_by_code("services_label_cap"));

		        $tpl->set_var("city", $actual_srv["city"]);
			    if(isset($actual_srv["label"]) && $actual_srv["label"])
        			$tpl->set_var("label_city", ffTemplate::_get_word_by_code("services_label_city"));

		        if(isset($actual_srv["prov"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_prov", ffTemplate::_get_word_by_code("services_label_prov"));

		            $tpl->set_var("prov", $actual_srv["prov"]);
				}
		        if(isset($actual_srv["state"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_state", ffTemplate::_get_word_by_code("services_label_state"));

		            $tpl->set_var("state", $actual_srv["state"]);
				}
		        $tpl->set_var("tel", $actual_srv["tel"]);
			    if(isset($actual_srv["label"]) && $actual_srv["label"])
        			$tpl->set_var("label_tel", ffTemplate::_get_word_by_code("services_label_tel"));

		        if(isset($actual_srv["fax"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_fax", ffTemplate::_get_word_by_code("services_label_fax"));

		            $tpl->set_var("fax", $actual_srv["fax"]);
				}
		        if(isset($actual_srv["email"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_email", ffTemplate::_get_word_by_code("services_label_email"));

		            $tpl->set_var("email", $actual_srv["email"]);
				}
		        if(isset($actual_srv["info"])) {
				    if(isset($actual_srv["label"]) && $actual_srv["label"])
        				$tpl->set_var("label_info", ffTemplate::_get_word_by_code("services_label_info"));

		            $tpl->set_var("info", $actual_srv["info"]);
				}

				if(isset($actual_srv["force_tpl"]) && strlen($actual_srv["force_tpl"])) {
					$template_res = $tpl->rpparse("main", false);
				} else {
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["prefix"] = "VGS";
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["ID"] = "company.data";
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["title"] = "Company Data" . " [" . "VG SERVICES" . "]";
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["type"] = "VG_SERVICES";
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["location"] = $actual_srv["location"];
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["width"] = $sections[$actual_srv["location"]]["width"];
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["visible"] = NULL;
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["settings"] = "";
			        $sections[$actual_srv["location"]]["blocks"]["VGS"]["content"] = $tpl->rpparse("main", false);
				}
			}
		}
    }