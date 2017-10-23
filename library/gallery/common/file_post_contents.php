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
function file_post_contents($url, $data, $username = null, $password = null) {
	if(!$username && defined("AUTH_USERNAME"))
		$username = AUTH_USERNAME;
	if(!$password && defined("AUTH_PASSWORD"))
		$password = AUTH_PASSWORD;

	$postdata = http_build_query($data);

	$opts = array(
		'ssl' => array(
			"verify_peer" 		=> false,
			"verify_peer_name" 	=> false
		),
		'http' => array(
			'method'  			=> 'POST',
			'header'  			=> "Content-type: application/x-www-form-urlencoded"
				. ($username
					? "\r\nAuthorization: Basic " . base64_encode(AUTH_USERNAME . ":" . AUTH_PASSWORD)
					: ""
				),
			'content' 			=> $postdata
		)
	);

	$context = stream_context_create($opts);
	return file_get_contents($url, false, $context);
}