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
    if($actual_srv["enable"] && strlen($actual_srv["params"])) 
    { 
		$js_content = "
			var JADV_DFP_SEM='';
            var JADV_JSON_SEM='';
            
            var e = document.createElement('script');
            e.type = 'text/javascript'; 
            e.src = 'https://sem.juiceadv.com/?refUrl='+encodeURIComponent(window.top.location.href);
            document.head.appendChild(e);
            
            var googletag = googletag || {};
            googletag.cmd = googletag.cmd || [];
            (function() {
            var gads = document.createElement('script');
            gads.async = true;
            gads.type = 'text/javascript';
            var useSSL = 'https:' == document.location.protocol;
            gads.src = (useSSL ? 'https:' : 'http:') + 
            '//www.googletagservices.com/tag/js/gpt.js';
            var node = document.getElementsByTagName('script')[0];
            node.parentNode.insertBefore(gads, node);
            })();
            var gptAdSlots = [];
            var mapping = {};
            var mapping1 = {};
            googletag.cmd.push(function() {
			"   . ($_SERVER["ORIG_PATH_INFO"] == "/" 
					? $actual_srv["params_home"]
					: $actual_srv["params"]			
				) . "
				googletag.pubads().enableSingleRequest();
				googletag.pubads().disableInitialLoad();
            
			" . $actual_srv["criteo_post_tag"] . " 

            JADV_JSON_SEM && Object.keys(JADV_JSON_SEM).map(function(el){googletag.pubads().setTargeting(el,JADV_JSON_SEM[el]);});
            googletag.pubads().setTargeting('key_topic','');

            googletag.enableServices();
            });
            ";
        /*
        $js_content = "
            var ord = window.ord || Math.floor(Math.random() * 1e16);
            var JADV_DFP_SEM=\"\";
            document.write('<scr'+'ipt src=\"http://sem.juiceadv.com/\"></scr'+'ipt>');
        ";
        if($actual_srv["skin-masthead-interstiziali"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=1;sz=970x250,1x3;dcopt=ist;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
        if($actual_srv["728x90"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=2;sz=728x90;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
        if($actual_srv["300x250-300x600-primoscroll"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=3;sz=300x250,300x600;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
		
        */
        //$oPage->fixed_pre_content .= '<script defer="defer" charset="utf-8" type="text/javascript">' . $js_content . '</script>';
        $oPage->tplAddJs("Leonardo"
            , array(
                "embed" => $js_content
        ));
		
        $oPage->tplAddJs("LeonardoJs"
            , array(
                "file" => "AsyncBungeeBanner.js"
                , "path" => "https://cdn.juiceadv.com/js/dfp"
                , "exclude_compact" => true
        ));
    }