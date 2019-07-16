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
function system_set_meta($oPage) 
{
    $db = ffDB_Sql::factory();

    $globals = ffGlobals::getInstance("gallery");

    if(!isset($globals->meta))
        $globals->meta = array();  

    if(!isset($globals->html))
        $globals->html = array();  

	if(check_function("set_header_page")) {
		set_header_page();
	}
        
    $standard_meta = $globals->meta;
    $html_attr = $globals->html["attr"];
    /*
    $languages = array();

    $sSQL = "SELECT * FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $languages[] = strtolower(substr($db->getField("code")->getValue(), 0, -1));
        } while($db->nextRecord());
    
    $standard_meta["language"] = implode(",", $languages);
    }*/



    $standard_meta["description"] = (is_array($globals->meta["description"]) ? implode(", ", $globals->meta["description"]) : "");
    if(strlen($standard_meta["description"]) > Cms::env("LIMIT_CHARACTER_SEO")) {
        $standard_meta["description"] = substr($standard_meta["description"], 0, Cms::env("LIMIT_CHARACTER_SEO"));
        $standard_meta["description"] = substr($standard_meta["description"], 0, strrpos($standard_meta["description"], " "));
    }
     
	 if(!Cms::env("AVOID_META_KEYWORDS"))
     	$standard_meta["keywords"] = (is_array($globals->meta["keywords"]) ? implode(", ", $globals->meta["keywords"]) : "");
	 else 
	 	$standard_meta["keywords"] = "";
	 	
	 if(is_array($html_attr) && count($html_attr)) {
	    foreach (array_filter($html_attr) AS $key => $value) {
            $oPage->tplAddHtmlAttr($key, $value);
		}
	 }
    foreach ($standard_meta AS $key => $value) {
        if(is_array($value)) {
            $params = array(
                "content" => $value["content"]
                , ($value["type"] ? $value["type"] : "name") => $key
            );

        } elseif(strlen($value)) {
            $params = array(
                "content" => $value["content"]
                , "name" => $key
            );
        }

        $oPage->tplAddMeta($params);
    }

    //$oPage->parse_meta();
}
