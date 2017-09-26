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
    
    if($actual_srv["enable"] && strlen($actual_srv["code"])) 
	{
		$params[] = "idEditore=" . urlencode($actual_srv["code"]);
        if($actual_srv["mndArea"])
            $params[] = "mndArea=" . urlencode($actual_srv["mndArea"]);
        if($actual_srv["mndColor"])
            $params[] = "mndColor=" . urlencode($actual_srv["mndColor"]);
        if($actual_srv["mndFromColor"])
            $params[] = "mndFromColor=" . urlencode($actual_srv["mndFromColor"]);
        if($actual_srv["mndToColor"])
            $params[] = "mndToColor=" . urlencode($actual_srv["mndToColor"]);
        if($actual_srv["mndAlignColor"])
            $params[] = "mndAlignColor=" . urlencode($actual_srv["mndAlignColor"]);
        if($actual_srv["mndTime"])
            $params[] = "mndTime=" . urlencode($actual_srv["mndTime"]);
        if($actual_srv["mndImg"])
            $params[] = "mndImg=" . urlencode($actual_srv["mndImg"]);
        if($actual_srv["mndLnkTxt1"])
            $params[] = "mndLnkTxt1=" . urlencode($actual_srv["mndLnkTxt1"]);
        if($actual_srv["mndLnkUrl1"])
            $params[] = "mndLnkUrl1=" . urlencode($actual_srv["mndLnkUrl1"]);
        if($actual_srv["mndLnkTxt2"])
            $params[] = "mndLnkTxt2=" . urlencode($actual_srv["mndLnkTxt2"]);
        if($actual_srv["mndLnkUrl2"])
            $params[] = "mndLnkUrl2=" . urlencode($actual_srv["mndLnkUrl2"]);
        if($actual_srv["mndLnkTxt3"])
            $params[] = "mndLnkTxt3=" . urlencode($actual_srv["mndLnkTxt3"]);
        if($actual_srv["mndLnkUrl3"])
            $params[] = "mndLnkUrl3=" . urlencode($actual_srv["mndLnkUrl3"]);
        if($actual_srv["mndLnkTxt4"])
            $params[] = "mndLnkTxt4=" . urlencode($actual_srv["mndLnkTxt4"]);
        if($actual_srv["mndLnkUrl4"])
            $params[] = "mndLnkUrl4=" . urlencode($actual_srv["mndLnkUrl4"]);
        if($actual_srv["mndLnkTxt5"])
            $params[] = "mndLnkTxt5=" . urlencode($actual_srv["mndLnkTxt5"]);
        if($actual_srv["mndLnkUrl5"])
            $params[] = "mndLnkUrl5=" . urlencode($actual_srv["mndLnkUrl5"]);
        if($actual_srv["mndLnkColor"])
            $params[] = "mndLnkColor=" . urlencode($actual_srv["mndLnkColor"]);
        if($actual_srv["mndLnkColorOver"])
            $params[] = "mndLnkColorOver=" . urlencode($actual_srv["mndLnkColorOver"]);

        $globals->fixed_post["body"][] = '<script defer="defer" charset="utf-8" type="text/javascript" src="http://www.leonardo.it/script/Leonardo_Intruder.php?' . implode("&", $params) . '"></script>';
    }
