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

    $js = '
    	jQuery(function() {
    		if(document.cookie.indexOf("cb-enabled=accepted") < 0) {
    			ff.load("jquery.plugins.cookiebar", function() {
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
    		}
    	});';
//    $oPage->tplAddCss("cookiebar", "jquery.cookiebar.css", "/themes/library/plugins/jquery.cookiebar");
//    $oPage->tplAddJs("jquery.cookieBar", "jquery.cookiebar.js", "/themes/library/plugins/jquery.cookiebar"); 
//    $oPage->tplAddJs("cookieBar", null, null, false, false, $js);
    
     $oPage->tplAddJs("jquery.plugins.cookiebar.init", array(
		"embed" => $js
	));
