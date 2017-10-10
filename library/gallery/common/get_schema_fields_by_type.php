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
  function get_schema_fields_by_type($type, $default = null) 
  {
    require(FF_DISK_PATH . "/library/gallery/struct." . FF_PHP_EXT);
  	$def = $def + system_get_schema_module("fields", $def);
	
	if(is_array($type)) {
		$src = array_replace_recursive($def["default"], $type);
	} else {
		switch($type) {
	        case "thumb-anagraph":
	        case "detail-anagraph":
		    case "anagraph":
	        case "/thumb-anagraph":
	        case "/detail-anagraph":
		    case "/anagraph":
                $src = $def["anagraph"];
		        break;
			case "media":	        
		    case "gallery":
			case "/media":	        
		    case "/gallery":
		        $src = $def["files"];
		        break;
		    case "page":
            case "/page":                
				$src = $def["page"];
	    		break;
		    case "tag":
            case "/tag":
				$src = $def["tag"];
	    		break;
		    case "thumb":
		    case "detail":
		    case "vgallery":
		    case "/thumb":
		    case "/detail":
		    case "/vgallery":
	    		$src = $def["vgallery"];
		        break;
		    default:
	    		if($def[$type])
	    			$src = $def[$type];
	    		elseif($def[$default]) {
	    			$src = $def[$default];
	    		} else {
				    $src =  $def["default"];
				}
		}  
	}
    
    if(!$src["table"]) {
        if($src["seo"]["primary_table"]) {
            $src["table"]                   = $src["seo"]["primary_table"];
            $src["seo"]["table"]            = $src["seo"]["primary_table"];
            $src["seo"]["rel_lang"]         = false;
            $src["field"]                   = array_replace($src["field"], $src["seo"]);
        }
    }
    return $src;
}