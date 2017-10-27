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
function get_thumb($path, $params = array(), $res = null) 
{
    /*     * *
     * Logica di creazione dei percorsi immagine
     * Percorsi possibili:
     * 
     * $fake_name . "-" . $prefix . "-" . $ext . "-" . $source_filename
     * $fake_name . "-" . $prefix . "-" . $source_filename
     * 
     * $fake_name . "-" . $mode . "-" . $ext . "-" . $source_filename
     * $fake_name . "-" . $mode . "-" . $source_filename
     * 
     * $source_filename . "-" . $ext . "-" . $mode
     * $source_filename . "-" . $mode
     * 
     * $source_filename . "-" . $ext . "." . $ext 
     * $source_filename . "." . $ext
     * 
     * @var mixed
     */
    
    static $loaded_path = array();   

    if(!$path)
    	return null;
    elseif($path === true)
    	return $loaded_path["keys"];
    /*elseif (strpos($path, "http") === 0) {
		return array(
			    "src" => $path
			    , "width" => $params["thumb"]["default"]["width"]
			    , "height" => $params["thumb"]["default"]["height"]
			);
    }*/

    $params["base_path"] 	= ($params["base_path"]
								? $params["base_path"]
								: FF_DISK_PATH . FF_UPDIR 
						    );
    
/*
    $mode 					= $params["mode"];
    $extension 				= $params["format"];
    $width 					= $params["width"];
    $height 				= $params["height"];*/
    $thumb_settings	 																						= array();
    if(is_array($params["thumb"]["default"])) {
    	$thumb_settings 																					= $params["thumb"];

	    if(is_array($params["highlight"]))
			$thumb_settings																					= array_replace($thumb_settings, $params["highlight"]);    
    } else {
	    if(is_array($params["thumb"])) {
    		if($params["thumb"]["width"] > 0 && $params["thumb"]["height"] > 0) 
    			$thumb_settings["thumb"] 																	= $params["thumb"];
	    } elseif(strlen($params["thumb"])) {
			if(strpos($params["thumb"], "-") !== false) {
    			$mode 																						= explode("-", $params["thumb"]);
    			$operation 																					= "-";
			} else {
				$mode 																						= explode("x", $params["thumb"]);
				$operation 																					= "x";
			}
			
			if(count($mode) == 1 && is_numeric($mode[0])) {
				$thumb_settings["thumb"]["width"] 															= $mode[0];
				$thumb_settings["thumb"]["height"] 															= $mode[0];
			} elseif(count($mode) == 2 && is_numeric($mode[0]) && is_numeric($mode[1])) {
				$thumb_settings["thumb"] 																	= array_combine(array("width", "height"), $mode);
			}    
	    
	    }

	    if($thumb_settings["thumb"] && !$thumb_settings["thumb"]["name"])
    		$thumb_settings["thumb"]["name"] 																= $thumb_settings["thumb"]["width"] . $operation . $thumb_settings["thumb"]["height"];
	}
    $skip_fs_check = false;
   /* if (defined("CDN_STATIC") && CDN_STATIC
		&& defined("CDN_STATIC_IMAGE") && strlen(CDN_STATIC_IMAGE) && CDN_STATIC_IMAGE != $params["showfiles_path"]
    ) {
    
		$params["showfiles_path"] = CDN_STATIC_IMAGE;
		$skip_fs_check = true;
    }*/

    if(substr(CM_SHOWFILES, 0, 7) == "http://" || substr(CM_SHOWFILES, 0, 8) == "https://" || substr(CM_SHOWFILES, 0, 2) == "//")
    	$skip_fs_check = true;
    
    
    //if (!$skip_fs_check && !is_file($params["base_path"] . $path) && !is_dir($params["base_path"] . $path)) {
	//	return array("src" => get_thumb_by_placehold($params["thumb"]["default"]["width"], $params["thumb"]["default"]["height"]));
    //}

/*
	if (strlen($mode)) {
		$fake_mode = $mode;
	} else {
		$fake_mode = "empty";
	}
*/
	$fake_mode = ($thumb_settings["key"]
		? $thumb_settings["key"]
		: "empty"
	);

	if (strpos($path, "http") === 0) {
    
		$loaded_path[$fake_mode][$path]["default"]["src"] = $path;
		$loaded_path[$fake_mode][$path]["default"]["width"] = ($params["thumb"]["default"]["width"]
			? $params["thumb"]["default"]["width"]
			: "auto"
		);
		$loaded_path[$fake_mode][$path]["default"]["height"] = ($params["thumb"]["default"]["height"]
			? $params["thumb"]["default"]["height"]
			: "auto"
		);
		//return $loaded_path[$fake_mode][$path]["default"];
	} elseif (!$skip_fs_check && !is_file($params["base_path"] . $path) && !is_dir($params["base_path"] . $path)) {
		$loaded_path[$fake_mode][$path]["default"]["src"] = get_thumb_by_placehold($params["thumb"]["default"]["width"], $params["thumb"]["default"]["height"]);
		$loaded_path[$fake_mode][$path]["default"]["width"] = $params["thumb"]["default"]["width"];
		$loaded_path[$fake_mode][$path]["default"]["height"] = $params["thumb"]["default"]["height"];
	}
	
	if (!isset($loaded_path[$fake_mode][$path])) {
	    //if (strpos($params["showfiles_path"], "http") !== 0)
			//$params["showfiles_path"] =  $params["showfiles_path"];		
				
	    $source["name"] = ffGetFilename($path);
	    $source["ext"] = ffGetFilename($path, false);

	    if (!$params["fake_name"])
			//$params["fake_name"] = $source["name"];
			$params["fake_name"] = basename($source["path"]);


	    $params["fake_name"] = ffCommon_url_rewrite(normalize_code_word($params["fake_name"]));

		if(is_array($thumb_settings) && count($thumb_settings)) {
			$ID = "";
		    foreach($thumb_settings AS $thumb_key => $thumb_params) {
				if($thumb_key == "key")
					continue;
	    		if($ID && $thumb_params["ID"] == $ID)
	    			continue;
	 
    			$loaded_path[$fake_mode][$path][$thumb_key] 	= get_thumb_by_grid_system($path, $params, $thumb_params, $source);
    			
    			$ID = $thumb_params["ID"];
		    }
		} else {
			$loaded_path[$fake_mode][$path]["default"] 			= get_thumb_by_grid_system($path, $params);
		}

		//if(CM_CACHE_IMG_LAZY_LOAD)
		//	$loaded_path[$fake_mode][$path]["default"]["class"] = $params["class"];
	}	
	if(is_array($res)) {
		$res["src"] 											= $loaded_path[$fake_mode][$path]["default"]["src"];
		$res["width"] 											= $loaded_path[$fake_mode][$path]["default"]["width"];
		$res["height"] 											= $loaded_path[$fake_mode][$path]["default"]["height"];

		$res["picture"] 										= $loaded_path[$fake_mode][$path];
	} elseif(strlen($res)) {
		$res 													= $loaded_path[$fake_mode][$path][$res];
	} else {
		$res 													= $loaded_path[$fake_mode][$path];
	}
	
	$loaded_path["keys"][$path] 								= $loaded_path[$fake_mode][$path]["default"];
	$loaded_path["keys"][$path]["placehold"] 					= $params["placehold"];

//print_r($loaded_path);
    return $res;
}

function get_thumb_by_grid_system($path, $params, $thumb_params = array(), $source = array()) 
{
	$src = null;
	//print_r($params);
	$size = get_thumb_size($path, $thumb_params["width"], $thumb_params["height"], $params["mime"], $src, $params["base_path"]);
	     
    if (!$size["skip_mode"] && strlen($thumb_params["name"])) {
		$mode = "/" . ffCommon_url_rewrite($thumb_params["name"]);

		$extension = ($thumb_params["format"]
			? $thumb_params["format"]
			: THUMB_ICO_EXTENSION
		);

	    if ($mode && !$params["preserve_orig"]) {
			if ($source["ext"] != $extension)
			    $ext = "-" . $source["ext"];
	    } else {
			$extension = $source["ext"];
			$ext = "";
	    }

		/** //nn funziona da debuggare bene https://www.paginemediche.it/diabete?__nocache__&__debug__
		* Add fake name to original name. /realpath/fakename/fakename-realname.ext
		*/ 
		if (0 && !$params["preserve_orig"] && basename(ffCommon_dirname($path)) != $params["fake_name"] && strpos($source["name"], $params["fake_name"]) === false) {
			if(strrpos($params["fake_name"], "-" . $source["name"]) == strlen($params["fake_name"]) - strlen("-" . $source["name"]))
				$fake_name = substr($params["fake_name"], 0, strrpos($params["fake_name"], "-" . $source["name"]));
			else
				$fake_name = $params["fake_name"];

			$fake_path = $fake_name . "/" . $fake_name . "-";
		}

		$name_cache_thumb = $fake_path . $source["name"];

/*	    if ($params["preserve_orig"]) {
			$name_cache_thumb = $source["name"];
	    } else {

			if ($source["name"] == $params["fake_name"]) {
			    $name_cache_thumb = $source["name"];
			} else {
			    if (basename(ffCommon_dirname($path)) != $params["fake_name"])
					$fake_path = $params["fake_name"] . "/";

			    $name_cache_thumb = $fake_path . $params["fake_name"] . "-" . $source["name"];
			}
	    }
*/

	    $res = array(
			"src" => (CM_MEDIACACHE_SHOWPATH
				? CM_MEDIACACHE_SHOWPATH . stripslash(ffCommon_dirname($path)) . "/" . $name_cache_thumb . $ext . "-" . basename($mode) . "." . $extension
				: CM_SHOWFILES . $mode . $ext . $params["showfiles_path"] . stripslash(ffCommon_dirname($path)) . "/" . $name_cache_thumb . "." . $extension
			)
			, "width" => $size["width"]
			, "height" => $size["height"]
	    );
	} else {
		$res = array(
			"src" => (CM_MEDIACACHE_SHOWPATH
				? CM_MEDIACACHE_SHOWPATH . $path
				: CM_SHOWFILES . $params["showfiles_path"] . $path
			)
			, "width" => $size["width"]
			, "height" => $size["height"]
	    );	
	}

	return $res;
}


function get_thumb_by_media_queries($media, $params, $type = "picture") 
{
	$resolution = cm_getResolution("media", false);
	if(CM_CACHE_IMG_LAZY_LOAD
		&& is_numeric($params["default"]["width"]) && $params["default"]["width"] > 0 
		&& is_numeric($params["default"]["height"]) && $params["default"]["height"] > 0	
	) {
		$fake_img = '<svg class="lazyloader"'
				. ' width="' . $params["default"]["width"] . '"'
				. ' height="' . $params["default"]["height"] . '"'
			. ' ></svg>';
	}
	if($type && is_array($resolution) && count($resolution) && count($params) > 1) {
		$arrRes = array();
		if($media === null)
			$media = ($fake_img ? ' class="lazy" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-' : " ") . 'src="' . $params["default"]["src"] . '" width="' . $params["default"]["width"] . '" height="' . $params["default"]["height"] . '"';

		foreach($resolution AS $resolution_key => $resolution_value) {
			if($params[$resolution_key]["src"])
				$src = $params[$resolution_key]["src"];
			
			if($src)
				$arrRes[$src] = '<source ' . ($fake_img ? 'srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-' : "") . 'srcset="' . str_replace(" ", "+", $src) . '" media="' . $resolution_value . '" />';
		}

		if($fake_img)
			$res = $fake_img . '<' . $type . '>' . implode("", $arrRes) . '<img ' . (0 ? 'data-src="' . str_replace(" ", "+", $src) . '" class="lazy"' : $media) . ' />' . '</' . $type . '>';
		else
			$res = '<' . $type . '>' . implode("", $arrRes) . '<img ' . (0 ? 'src="' . str_replace(" ", "+", $src) . '"' : $media) . ' />' . '</' . $type . '>'; 

	} elseif($params["default"]["width"] && $params["default"]["height"]) {
		$res = $fake_img . '<img ' . $media
		. ' width="' . $params["default"]["width"] . '"'
		. ' height="' . $params["default"]["height"] . '"'
		. ' />';
	} else {
		$res = '<img ' . $media
		. ($params["width"] ? ' width="' . $params["width"] . '"' : '') 
		. ($params["height"] ? ' height="' . $params["height"] . '"' : '') 
		. ' />';
	}

	return $res;
}

function check_thumb_format($img_path, $width = null, $height = null, $mime = null, $base_path = DISK_UPDIR, $skip_base_path = null) 
{
	static $img_checked = array();
	
	if(!array_key_exists($img_path, $img_checked)) {
		$img_checked[$img_path] = false;

		$showfiles_path = "";
		$path 			= $img_path;

		if((substr(CM_SHOWFILES, 0, 7) == "http://" || substr(CM_SHOWFILES, 0, 8) == "https://" || substr(CM_SHOWFILES, 0, 2) == "//")) {
			$base_path = true;
			$mime = ffMimeTypeByFilename($img_path);
			switch($mime) {
				case "image/svg+xml":
					$img_checked[$img_path]["skip_mode"] = true;
					break;
				case "image/jpeg":
				case "image/png":
				case "image/gif":					
				default:
			}
			if(strpos($img_path, FF_THEME_DIR . "/" . FRONTEND_THEME . "/images") === 0) {
				$path = substr($img_path, strlen(FF_THEME_DIR));
			}
		} else {
			$abs_path 		= $base_path . $img_path;
			if(!is_file($abs_path)) {
				$showfiles_path 		= "/" . FRONTEND_THEME . "/images";
				$base_path = $base_path = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images";
				$path 					= substr($img_path, strlen(FF_THEME_DIR . "/" . FRONTEND_THEME . "/images"));
				$abs_path 				= FF_DISK_PATH . $img_path;

				if(!is_file($abs_path)) {
					$showfiles_path 	= null;
					$base_path 			= null;
					$abs_path 			= false;
				} else {
					$img_checked[$img_path]["preserve_orig"] = true;
				}
			}
		}

	    $img_checked[$img_path]["base_path"] 		= $base_path;
	    $img_checked[$img_path]["path"] 			= $path;
	    $img_checked[$img_path]["showfiles_path"] 	= $showfiles_path;

		if($abs_path && $skip_base_path != $base_path) {
			if(!$mime)
				$mime = ffMimeTypeByFilename($abs_path);

			switch($mime) {
				case "image/svg+xml":
					$skip_mode = true;
					//if(!$width || !$height) {
						$xml = @simplexml_load_file($abs_path);
						if($xml) {
							$attrs = $xml->attributes();
							
							$file_width 	= round(str_ireplace("px", "", (string) $attrs->width));
							$file_height 	= round(str_ireplace("px", "", (string) $attrs->height));
						}
					//}
					break;
				case "image/jpeg":
				case "image/png":
				case "image/gif":
				default:
					//if(!$width || !$height) {
						$attrs = @getimagesize($abs_path);
						if (is_array($attrs) && $attrs[0] > 0 && $attrs[1] > 0) {
							$file_width 	= $attrs[0];
							$file_height 	= $attrs[1];
						}
					//}
			}
			
			if(!$file_width)
				$file_width 	= "auto";
			if(!$file_height)
				$file_height 	= "auto";
				
	    	$img_checked[$img_path]["width"] 		= $file_width;
	    	$img_checked[$img_path]["height"] 		= $file_height;
	    	$img_checked[$img_path]["skip_mode"] 	= $skip_mode;
	    	$img_checked[$img_path]["mime"] 		= $mime;
		}
	}	
	$res = $img_checked[$img_path];

	if($width)
		$res["width"] = $width;
	if($height)
		$res["height"] = $height;
	
	return $res;
}

function get_thumb_size($img_path, $width = null, $height = null, $mime = null, &$src = null, $base_path = DISK_UPDIR) 
{
	$res = false;
	if(!$width || !$height) {
		if (isset($src["thumb"]) && strpos($src["thumb"], "x") !== false) {
			$attrs = explode("x", $src["thumb"]);
			if (is_array($attrs) && $attrs[0] > 0 && $attrs[1] > 0) {
				$width = $attrs[0];
				$height = $attrs[1];
			}				
		}	
	}
	
	$img_info = check_thumb_format($img_path, $width, $height, $mime, $base_path);
	if($img_info["base_path"]) {
		$res = array(
	    	"width" => $img_info["width"]
	    	, "height" => $img_info["height"]
	    	, "skip_mode" => $img_info["skip_mode"]
		);	

	} else {
		$src["src"] = false;	
	}
	
	if($width)
		$res["width"] = $width;
	if($height)
		$res["height"] = $height;
		
	if($res["skip_mode"])
		$src["thumb"] = false;

	return $res;
}

function normalize_code_word($tmp) 
{
    if(strlen($tmp) > 100) {
        $ret = substr($tmp, 0, 100) . ffTemplate::_get_word_by_code("characters_limitator");
    } else {
        $ret = $tmp;
    }

    return strip_tags($ret);
}

function get_thumb_by_placehold($width, $height, $bgcolor = null, $color = null) 
{
	if(get_session("UserID") == SUPERADMIN_USERNAME && $width > 0 && $height > 0) {
		if(ctype_xdigit($bgcolor))
			$bgcolor = "/" . $bgcolor;
		else 
			$bgcolor = "";

		if(ctype_xdigit($color)) {
			if($bgcolor)
				$color = "/" . $color;
			else
				$color = "//" . $color;
		} else 
			$color = "";

		$src = "https://placehold.it/" . $width . "x" . $height . $bgcolor . $color;
	} else {
		$src = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
	}
	
	return $src;
}