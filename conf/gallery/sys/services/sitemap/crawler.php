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
if(!Auth::isLogged()) {
    exit;
}

$db = ffDB_Sql::factory();

$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents/wizard");
$tpl->load_file("seo.html","main");

$arrSeoAnalysis = array(
	"seo" => array(
		"title" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "description" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => ""
			, "external" => false
		)
		, "headings" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "keywords-cloud" => array(
			"score" => 0
			, "external" => false
		)
		, "keywords-consistency" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "images" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => true
		)
		, "text-html-ratio" => array(
			"importance" => 2
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "google-publisher" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "in-page-links" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => true
		)
		, "broken-links" => array(
			"importance" => 3
			, "resolution" => 1
			, "score" => 0
			, "external" => true
		)
		, "www-resolve" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "ip-canonicalization" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "robots-txt" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "xml-sitemap" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "url-rewrite" => array(
			"importance" => 1
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "underscores-in-the-urls" => array(
			"importance" => 1
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "flash" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "frames" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "domain-expiration" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "blog" => array(
			"importance" => 2
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		
	)
	, "Mobile" => array(
		"mobile-rendering" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)	
		, "mobile-load-time" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)	
		, "mobile-optimization" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
	)
	, "Usability" => array(
		"url" => array(
			"score" => 0
			, "external" => false
		)	
		, "favicon" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)	
		, "custom-404-page" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "conversion-form" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "page-size" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)
		, "load-time" => array(
			"importance" => 3
			, "resolution" => 2
			, "score" => 0
			, "external" => "MainResourceServerResponseTime"
		)
		, "language" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "printability" => array(
			"importance" => 1
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "microformats" => array(
			"importance" => 2
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "dublin-core" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "domain-availability" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)
		, "typo-availability" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)
		, "email-privacy" => array(
			"score" => 0
			, "external" => false
		)
		, "spam-block" => array(
			"importance" => 2
			, "resolution" => 3
			, "score" => 0
			, "external" => false
		)
		, "safe-browsing" => array(
			"importance" => 2
			, "resolution" => 3
			, "score" => 0
			, "external" => false
		)
	)
	, "Technologies" => array(
		"server-ip" => array(
			"score" => 0
			, "external" => false
		)	
		, "technologies" => array(
			"score" => 0
			, "external" => false
		)	
		, "speed-tips" => array(
			"importance" => 2
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "analisys" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "w3c-validity" => array(
			"importance" => 1
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)
		, "doctype" => array(
			"score" => 0
			, "external" => false
		)
		, "encoding" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "directory-browsing" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "server-signature" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
	)
	, "Social" => array(
		"social-shareability" => array(
			"importance" => 3
			, "resolution" => 2
			, "score" => 0
			, "external" => false
		)	
		, "twitter-account" => array(
			"importance" => 1
			, "score" => 0
			, "external" => false
		)	
		, "facebook-page" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
		, "google-page" => array(
			"importance" => 1
			, "resolution" => 1
			, "score" => 0
			, "external" => false
		)
	)
	, "Local" => array(
		"local-directories" => array(
			"score" => 0
			, "external" => false
		)	
	)
);

$arrStatus = array(
	"success" => Cms::getInstance("frameworkcss")->get("check-circle", "icon-tag")
	, "warning" => Cms::getInstance("frameworkcss")->get("exclamation-circle", "icon-tag")
	, "error" => Cms::getInstance("frameworkcss")->get("times-circle", "icon-tag")
	, "loading" => Cms::getInstance("frameworkcss")->get("spinner", "icon-tag", "spin")
	, "standby" => ""

);
$tpl->set_var("icon_mini_menu", Cms::getInstance("frameworkcss")->get("chevron-left", "icon-tag", "2x"));
$tpl->set_var("icon_helper", Cms::getInstance("frameworkcss")->get("help", "icon-tag"));

if(is_array($arrSeoAnalysis) && count($arrSeoAnalysis)) {
	foreach($arrSeoAnalysis AS $panel => $arrFields) {
		if(is_array($arrFields) && count($arrFields)) {
			foreach($arrFields AS $field_name => $field_value) {
				$tpl->set_var("smart_url", ffCommon_url_rewrite($field_name));
				$tpl->set_var("title", ffTemplate::_get_word_by_code("seo_analysis_" . $field_name . "_name"));
				switch($field_value["score"]) {
					case "0":
						$icon_status = $arrStatus["error"]; 
						break;
					case "1":
						$icon_status = $arrStatus["warning"]; 
						break;
					case "2":
						$icon_status = $arrStatus["success"]; 
						break;
					default:
						$icon_status = $arrStatus["standby"]; 
				}
				//$tpl->set_var("icon_status", $icon_status);
				if(isset($field_value["importance"])) {
					for($i = 1; $i<=3; $i++) {
						if($field_value["importance"] >= $i)
							$tpl->set_var("icon_importance", Cms::getInstance("frameworkcss")->get("dot-circle-o", "icon-tag"));
						else
							$tpl->set_var("icon_importance", Cms::getInstance("frameworkcss")->get("circle-o", "icon-tag"));
						
						$tpl->parse("SezFieldImportance", true);
					}
				}
				if(isset($field_value["resolution"])) {
					for($i = 1; $i<=3; $i++) {
						if($field_value["resolution"] >= $i)
							$tpl->set_var("icon_resolution", Cms::getInstance("frameworkcss")->get("gear", "icon-tag"));
						else
							$tpl->set_var("icon_resolution", Cms::getInstance("frameworkcss")->get("circle-o", "icon-tag"));
						
						$tpl->parse("SezFieldResolution", true);
					}
				}
				$tpl->parse("SezSeoPanelField", true);
				$tpl->set_var("SezFieldResolution", "");
				$tpl->set_var("SezFieldImportance", "");
			}
		}
		
		$tpl->set_var("category_name", $panel);
		$tpl->set_var("category_smart_url", ffCommon_url_rewrite($panel));
		
		$tpl->parse("SezSeoMenu", true);
		
		$tpl->parse("SezSeoPanel", true);
		$tpl->set_var("SezSeoPanelField", "");
	}
}

if(check_function("get_webservices")) {
	$services_params = get_webservices("google.maps");
}

http_response_code(200);
echo ffCommon_jsonenc(array(
    "score" => ""
    , "icons" => $arrStatus
    , "analysis" => $arrSeoAnalysis
	, "key" => ($services_params["enable"] && strlen($services_params["key"]) ? $services_params["key"] : "")
	, "cache" => false
    , "tpl" => $tpl->rpparse("main", false)
), true);

exit;
