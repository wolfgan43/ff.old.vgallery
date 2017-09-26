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
    if($actual_srv["enable"]) 
    { 
		$js_content = "
		        var e = document.createElement('script');
		        e.type = 'text/javascript'; 
		        e.src = '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
		        document.head.appendChild(e);";
		
		if($actual_srv["code"])
		{
			$js_content .= "
				(adsbygoogle = window.adsbygoogle || []).push({
				    google_ad_client: '" . $actual_srv["code"] . "'
				    , enable_page_level_ads: true
				  });";
		} 		        
		
		$oPage->tplAddJs("adsbygoogle", array("embed" => $js_content, "exclude_compact" => true));
    }