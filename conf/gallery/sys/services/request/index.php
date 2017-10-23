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
 * @subpackage services
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

$response = array();
$user_path = $_SERVER["HTTP_REFERER"];
if($_POST["params"])
    $req = json_decode($_POST["params"], true);

if(is_array($req) && count($req)) {
    foreach($req AS $service_name => $service) {
		switch($service_name) {
			case "notify":
				check_function("system_trace");
				$response["notify"] = system_trace_get_notify($user_path, $service["response"]);
				break;
			default:
    			if($service["opt"]["async"]) {
    				$response[$service_name] = get_response_by_service_async($service_name, $service["params"]);
				} else {
        			$response[$service_name] = get_response_by_service($service_name, $service["params"]);
				}
		}
    }
}

if(!$req["notify"] && check_function("system_trace")) {
    $response["notify"] = system_trace_get_notify($user_path);
}


echo ffCommon_jsonenc($response, true);
exit;

function get_response_by_service($service, $params = array()) {
    $cm = cm::getInstance();
    
    check_function("get_locale");
    $data["params"]             = $params;
    $data["user_permission"]    = get_session("user_permission");
    $data["user_path"]          = $_SERVER["HTTP_REFERER"];
    $data["locale"]             = get_locale();
    $data["ip"]                 = $_SERVER["REMOTE_ADDR"];

	check_function("file_post_contents");
	$res = file_post_contents("http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/srv/" . $service, $data);
	$return = json_decode($res, true);
    return (!$return
    			? $res
    			: $return
			);
}

function get_response_by_service_async($service, $params = array())
{
    check_function("get_locale");
	$url = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/srv/" . $service;
	
    $data["params"]             = $params;
    $data["user_permission"]    = get_session("user_permission");
    $data["user_path"]          = $_SERVER["HTTP_REFERER"];
    $data["locale"]             = get_locale();
    $data["ip"]                 = $_SERVER["REMOTE_ADDR"];
    
    $postdata = http_build_query(
        $data
    );
    
    $url_info = parse_url($url);
 	switch ($url_info['scheme']) {
        case 'https':
            $scheme = 'ssl://';
            $port = 443;
            break;
        case 'http':
        default:
            $scheme = '';
            $port = 80;    
    }	
	try {
	    $fp = fsockopen($scheme . $url_info['host']
		        , $port
		        , $errno
		        , $errstr
		        , 30
	        );

		if($fp) {
		    $out = "POST ".$url_info['path']." HTTP/1.1\r\n";
		    $out.= "Host: ".$url_info['host']."\r\n";
		    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
			if(defined("AUTH_USERNAME") && AUTH_USERNAME)
				$out.= "Authorization: Basic " . base64_encode(AUTH_USERNAME . ":" . AUTH_PASSWORD) . "\r\n";

		    $out.= "Content-Length: ".strlen($postdata)."\r\n";
		    $out.= "Connection: Close\r\n\r\n";
		    if (isset($postdata)) $out.= $postdata;

		    fwrite($fp, $out);
		    fclose($fp);
		}    
 	} catch (Exception $e) {
    	$errstr = $e;
    }
	
    return ($errstr ? $errstr : false);
}