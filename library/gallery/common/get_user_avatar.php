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

function get_user_avatar($avatar, $img = false, $gravatar = false, $prefix = null, $out_img = true, $size = null) { 
	$res = null;
    if ($prefix === null) {
        $prefix = CM_SHOWFILES . "/avatar";
    }
	if(!$size) {
		$arrSize = explode("x", MOD_SEC_USER_AVATAR_MODE);
		if(count($arrSize) == 2 && is_numeric($arrSize[0]) && is_numeric($arrSize[1]) && $arrSize[0] > 0) 
			$size = $arrSize[0];
		else
			$size = 80;
	}
    if (strlen($avatar)) {
		$mime = null;
		if(!(substr(strtolower($avatar), 0, 7) == "http://" || substr(strtolower($avatar), 0, 8) == "https://" || substr($avatar, 0, 2) == "//")) {
			if(is_file(DISK_UPDIR . $avatar))
				$mime = ffMimeType(DISK_UPDIR . $avatar);
			elseif(is_file(FF_DISK_PATH . $avatar))
				$mime = ffMimeType(FF_DISK_PATH . $avatar);
			else 
				$mime = false;
		}
		switch($mime) {
			case false:
				break;
			case "image/jpeg":
			case "image/png":
			case "image/gif":
				$res = $prefix . $avatar;
				break;
			case "image/svg+xml":
			default:
				$res = $avatar;
		}
    } elseif (ENABLE_GRAVATAR && $gravatar) {
        $res = get_gravatar($gravatar, $size, get_no_avatar($size, true, true), 'g', $out_img); 
    }
    
    if(!$res)
		$res = get_no_avatar($size);

    if ($img && $out_img) {
        $res = '<img class="avatar" src="' . $res . '" ' . ($size ? 'width="' . $size . '" height="' . $size . '" ' : '') . '/>';
    }
    return $res;
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array()) {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

function get_no_avatar($size = 80, $abs_url = false, $no_svg = false) {
	if($size)
		$mode = $size . "x" . $size;

	$res = mod_sec_get_avatar("", $mode, FRONTEND_THEME, $no_svg);
    if($abs_url) {
        $res = "http://" . DOMAIN_INSET . FF_SITE_PATH . $res; 
    }
    
    return $res;
}
