<?php
if(!mod_security_check_session(false) || get_session("UserNID") == MOD_SEC_GUEST_USER_ID) {
	prompt_login();
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
	"success" => cm_getClassByFrameworkCss("check-circle", "icon-tag")
	, "warning" => cm_getClassByFrameworkCss("exclamation-circle", "icon-tag")
	, "error" => cm_getClassByFrameworkCss("times-circle", "icon-tag")
	, "loading" => cm_getClassByFrameworkCss("spinner", "icon-tag", "spin")
	, "standby" => ""

);
$tpl->set_var("icon_mini_menu", cm_getClassByFrameworkCss("chevron-left", "icon-tag", "2x"));
$tpl->set_var("icon_helper", cm_getClassByFrameworkCss("help", "icon-tag"));

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
							$tpl->set_var("icon_importance", cm_getClassByFrameworkCss("dot-circle-o", "icon-tag"));
						else
							$tpl->set_var("icon_importance", cm_getClassByFrameworkCss("circle-o", "icon-tag"));
						
						$tpl->parse("SezFieldImportance", true);
					}
				}
				if(isset($field_value["resolution"])) {
					for($i = 1; $i<=3; $i++) {
						if($field_value["resolution"] >= $i)
							$tpl->set_var("icon_resolution", cm_getClassByFrameworkCss("gear", "icon-tag"));
						else
							$tpl->set_var("icon_resolution", cm_getClassByFrameworkCss("circle-o", "icon-tag"));
						
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
?>
