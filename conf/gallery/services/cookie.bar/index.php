<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    $js = '
    	jQuery(function() {
    		if(document.cookie.indexOf("cb-enabled=accepted") < 0) {
    			ff.pluginLoad("jquery.cookieBar", "/themes/library/plugins/jquery.cookiebar/jquery.cookiebar.js", function() {
    				ff.injectCSS("cookiebar", "/themes/library/plugins/jquery.cookiebar/jquery.cookiebar.css", function() {
						jQuery.cookieBar({
							message : "' . ffCommon_specialchars(str_replace(array("\n", "\n\r"), "", ffTemplate::_get_word_by_code("coockie_bar_message"))) . '"
							, acceptText : "' . ffTemplate::_get_word_by_code("coockie_bar_label") . '"
							, policyButton : ' . ($actual_srv["policy_page_link"] ? 'true' : 'false') . '
							, policyText: "' . ffTemplate::_get_word_by_code("coockie_policy_label") . '" 
							, policyURL: "' . $actual_srv["policy_page_link"] . '"
							, fixed : ' . ($actual_srv["fixed"] ? 'true' : 'false') . '
						    , bottom : ' . ($actual_srv["bottom"] ? 'true' : 'false') . ' 
						    , zindex: 10000
						});    
    				});
    			});
    		}
    	});';
    $oPage->tplAddCss("cookiebar", "jquery.cookiebar.css", "/themes/library/plugins/jquery.cookiebar");
    $oPage->tplAddJs("jquery.cookieBar", "jquery.cookiebar.js", "/themes/library/plugins/jquery.cookiebar", false, false, null, false, "bottom"); 
    $oPage->tplAddJs("cookieBar", null, null, false, false, $js, false, "bottom");
