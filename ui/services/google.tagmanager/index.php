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
    
    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        /*
        $tpl = ffTemplate::factory(__DIR__);
        $tpl->load_file("google.tagmanager.html", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $globals->fixed_pre["body"][] = $tpl->rpparse("main", false);
        */

        $js = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $actual_srv["code"] . "');
			" . ($actual_srv["datalayer"]
				? $actual_srv["datalayer"]
				: ""
			);
		
		$oPage->tplAddJs("gtm", array(
				"embed" => $js
				, "priority" => cm::LAYOUT_PRIORITY_HIGH
			)
		); 
    }