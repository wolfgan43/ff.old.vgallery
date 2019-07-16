<?php
/**
 *   VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @link https://bitbucket.org/cmsff/vgallery
 */
define("TOKEN", "[TOKEN]");
define("MASTER", "[DOMAIN]");

function installer_fpc($url) {
	$headers = array();
	$headers[] 				= "Referer: " . $_SERVER["HTTP_HOST"];
	$headers[] 				= "User-agent: " . TOKEN;

	$opts = array(
		'ssl' => array(
			"verify_peer" 		=> false,
			"verify_peer_name" 	=> false
		),
		'http' => array(
			'header'  			=> implode("\r\n", $headers),
		)
	);
	$context = stream_context_create($opts);

	$res = @file_get_contents($url, false, $context);

	return $res;
}

$res = array();
if(!is_file(__DIR__ . "/conf/gallery/install/index.php")) {
	$res = installer_fpc(MASTER ."/api/updater/installer");
	$res = json_decode($res, true);
}

if(!$res["error"]) {
    if(is_file(__DIR__ . "/conf/gallery/install/index.php")) {
        require(__DIR__ . "/conf/gallery/install/index.php");
    } else {
        echo "Installer Error. Try to upload file Manually";
    }
}

