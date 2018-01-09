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
function file_post_contents($url, $data = null, $username = null, $password = null, $method = "POST", $timeout = 60, $head = false) {
	if($username === null && defined("AUTH_USERNAME"))
		$username 				= AUTH_USERNAME;
	if($password === null && defined("AUTH_PASSWORD"))
		$password 				= AUTH_PASSWORD;

	$headers = array();
	if($method == "POST")
		$headers[] 				= "Content-type: application/x-www-form-urlencoded";
	if($username)
		$headers[] 				= "Authorization: Basic " . base64_encode($username . ":" . $password);

	$opts = array(
		'ssl' => array(
			"verify_peer" 		=> false,
			"verify_peer_name" 	=> false
		),
		'http' => array(
			'method'  			=> $method,
			'timeout'  			=> $timeout,
			'header'  			=> implode("\r\n", $headers),
		)
	);
	if($data) {
		$postdata 						= http_build_query($data);
		if ($method == "POST")
			$opts["http"]["content"] 	= $postdata;
		else
			$url 						= $url . "?" . $postdata;
	}
	$context = stream_context_create($opts);

	$res = @file_get_contents($url, false, $context);

	if($head) {
		$res = array(
			"headers" => parseHeaders($http_response_header)
			, "content" => $res
		);
	}

	return $res;
}

function file_post_contents_with_headers($url, $data = null, $username = null, $password = null, $method = "POST", $timeout = 60) {
	return file_post_contents($url, $data, $username, $password, $method, $timeout, true);
}

function parseHeaders( $headers )
{
	$head = array();
	foreach( $headers as $k=>$v )
	{
		$t = explode( ':', $v, 2 );
		if( isset( $t[1] ) )
			$head[ trim($t[0]) ] = trim( $t[1] );
		else
		{
			$head[] = $v;
			if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
				$head['response_code'] = intval($out[1]);
		}
	}

	return $head;
}