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
//print_r($cm->oPage->page_js);
//print_r($cm->oPage->page_css);
if(is_array($cm->oPage->page_js) && count($cm->oPage->page_js))
	cache_writeLog(print_r($cm->oPage->page_js, true), "request_error_js");
if(is_array($cm->oPage->page_css) && count($cm->oPage->page_css))
	cache_writeLog(print_r($cm->oPage->page_css, true), "request_error_css");

$cm->oPage->page_js = array();
$cm->oPage->page_css = array();

$response = array();
if($_POST["params"])
    $req = json_decode($_POST["params"], true);

unset($_POST["params"]);
$_SERVER["REQUEST_METHOD"] = "GET";

$globals = ffGlobals::getInstance("gallery");

check_function("get_schema_def");
check_function("system_layer_shards");
check_function("Notifier");

cm_getFrameworkCss(FF_THEME_FRAMEWORK_CSS);
cm_getFontIcon(FF_THEME_FONT_ICON);

system_layer_shards_page_by_referer();

//da gestire la priority: 2 e normal
// da gestire i socket
if(is_array($req) && count($req)) {
	$stack 											= array(
														"top" 			=> array(
															"func" 			=> "get_response_by_service_async"
															, "actions" 	=> null
														)
														, "shards" 		=> array(
															"func" 			=> "system_layer_shards"
															, "actions" 	=> null
															, "type" 		=> "mono"
														)
														, "api" 		=> array(
															"func" 			=> "get_response_by_service_api"
															, "actions" 	=> null
														)
														, "services" 	=> array(
															"func" 			=> "get_response_by_service_srv"
															, "actions" 	=> null
														)
														, "externals" 	=> array(
															"func" 			=> "get_response_by_service_http"
															, "actions" 	=> null
															, "type"		=> "ext"
														)
														, "lazy" 		=> array(
															"func" 			=> ""
															, "actions" 	=> null
														)
														, "bottom" 		=> array(
															"func" 			=> ""
															, "actions" 	=> null
														)
													);
	$schema = cache_get_settings();
	$schema_def = get_schema_def();

	/*$req["/api/1.0/get-tag-list"] = array();
	$req["/srv/share/check-article-like"] = array();
	$req["/applet/services/get-new-menu-www"] = array();
	$req["/applet/espertorisponde/new-question"] = array();
	$req["/anagraph/medici-online"] = array();
	$req["/benessere"] = array();
	$req["/benessere/alimentazione-e-dieta"] = array();
	$req["/publish/news-homepage"] = array();
	$req["/publish/homepage-strip-pdv"] = array();
	$req["/gallery/medici-online/aifa"] = array();
	$req["google.pagespeed"] = array(
		"url" => "https://www.googleapis.com/pagespeedonline/v2/runPagespeed"
		, "params" => array("url" => str_replace("dev.", "www.", $_SERVER["HTTP_REFERER"]))
	);*/
	/*$req["senato.it"] = array(
		"url" => "https://www.senato.it/1025?articolo_numero_articolo=55&sezione=126"
		, "response" => array(
			"target" => "#calendar_widget"
			, "source" => "#container_7167"
		)
	);
	$req["google.it"] = array(
		"url" => "https://www.google.it"
	, "response" => array(
			"target" => "#calendar_widget"
		, "source" => "#sbtc"
		)
	);*/


    foreach($req AS $service_name => $service) {
		$arrShard 					= explode("/", ltrim($service_name, "/"), 2);
		$service["opt"]["type"]		= $arrShard[0];
		$service["opt"]["path"] 	= $arrShard[1];
		//$service["opt"]["schema"] 	= $schema_def;
		$service_path				 = ($service["opt"]["url"]
										? $service["opt"]["url"]
										: $service_name
									);
		if($schema["page"]["/" . $service["opt"]["type"]]["group"] == "shard") {
			$stack["shards"]["actions"][$schema["page"]["/" . $service["opt"]["type"]]["name"]][$service["opt"]["path"]] = "/" . $service["opt"]["type"];
		} elseif($schema["page"]["/" . $service["opt"]["type"]]["group"] == "service") {
			if($service["opt"]["async"]) {
				$stack["top"]["actions"][$service_path] = array(
					"url" => $service_path
					, "data" => $service["params"]
				);
			} else {
				switch($service["opt"]["type"]) {
					case "srv":
						$stack["services"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
						);
						break;
					case "api":
						$stack["api"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
						);
						break;
					default:
						$stack["externals"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
							, "method" => $service["opt"]["method"]
							, "response" => $service["response"]
						);
				}
			}
		} elseif(substr($service_path, 0, 1) === "/") {
			$stack["shards"]["unknown"]["actions"]["/" . $service["opt"]["path"]] = "/" . $service["opt"]["type"];
		} elseif(strpos($service_path, "http") === 0) {
			$stack["externals"]["actions"][$service_name] = array(
				"url" => $service_path
				, "data" => $service["params"]
				, "method" => $service["opt"]["method"]
				, "response" => $service["response"]
			);
		} else {
			switch ($service_path) {
				case "admin":
					if($service["params"]["sid"]) {
						$sid = get_sid($service["params"]["sid"], null, true);
					}

					if(is_array($sid)) {
						if(array_key_exists("value", $sid)) {
							$params = json_decode($sid["value"], true);

							if(check_function("process_admin_toolbar"))
								$stack["lazy"]["response"]["admin"]["html"] = process_admin_toolbar(
									$params["path"]
									, $params["admin"]["theme"]
									, $params["admin"]["sections"]
									, $params["admin"]["css"]
									, $params["admin"]["js"]
									, $params["admin"]["international"]
									, $params["admin"]["seo"]
								);
						}
					}
					break;
				case "cache":
					check_function("refresh_cache");
					$stack["lazy"]["actions"][$service_path] = true; // da fare
					break;
				case "notify":
					$notifier = Notifier::getInstance();

					if($service["params"]["delivered"] && is_array($service["keys"]) && count($service["keys"])) {
						$stack["api"]["response"]["notify"] = null;

						$notifier->update(array(
							"delivered" => true
						), array(
							"key" => $service["keys"]
							, "delivered" => false
						));
					} else {
						check_function("get_user_data");
						//todo: da implementare la classe anagraph
						$anagraph = user2anagraph();
						$stack["api"]["response"]["notify"] = $notifier->read(array(
							"uid" 				=> $anagraph["ID"]
							, "display_in" 		=> array("/", $globals->page["user_path"])
							, "!key" 			=> $service["keys"]
						), array(
							"title" 			=> true
							, "message" 		=> true
							, "cover"			=> "media.cover"
							, "video"			=> "media.video"
							, "class"			=> "media.class"
							, "type"			=> true
							, "created" 		=> true
							, "delivered" 		=> ":toString"
							)
						, array(
							"last_update" 		=> "DESC"
							, "created" 		=> "DESC"
						));
					}

					//$stack["api"]["response"]["notify"]["timer"] = false;
					//print_r($stack["api"]["response"]["notify"]);
					//die();
					break;
				default:
					$stack["services"]["actions"][$service_path] = $service["params"];
			}
		}
    }


	if(is_array($stack) && count($stack)) {
		foreach($stack AS $priority => $services) {
			if(is_array($services["response"]) && count($services["response"])) {
				$response = $response + $services["response"];
			}
			if(is_array($services["actions"]) && count($services["actions"])) {
				if($services["type"] == "mono") {
					$response = $response + call_user_func($services["func"], $services["actions"]);
				} else {
					foreach ($services["actions"] AS $service_name => $service_params) {
						$response[$service_name] = call_user_func_array($services["func"], array($service_name, $service_params));
					}
				}
			}
		}
	}
}

if(check_function("system_set_media")) {
	if($_REQUEST["css"])
		$css = json_decode($_REQUEST["css"]);

	if(is_array($css))
		$globals->media_exception["css"] = array_fill_keys($css, true);

	if($_REQUEST["js"])
		$js = json_decode($_REQUEST["js"]);

	if(is_array($js))
		$globals->media_exception["js"] = array_fill_keys($js, true);

	$media = system_set_media_cascading(true);

	if($media) {
		$response["assets"] = $media;
	}
}

echo ffCommon_jsonenc($response, true);
exit;




function get_response_by_service($service, $params = null, $opt = null) {
	if ($opt["async"]) {
		$response = get_response_by_service_async($service, $params);
	} else {
		switch($opt["type"]) {
			case "srv":
				$response 								= get_response_by_service_srv($service, $params, $opt["schema"]);
				break;
			case "api":
				$response 								= get_response_by_service_api($service, $params, $opt["schema"]);
				break;
			default:
				$response 								= get_response_by_service_http($service, $params);
		}
	}

	return $response;
}
function get_response_include($include, $cm = null) {
	if($cm) {
		$cm->oPage->output_buffer = "";
		require($include);

		if (is_array($cm->oPage->output_buffer))
			$return = $cm->oPage->output_buffer;
		elseif (strlen($cm->oPage->output_buffer)) {
			$return["html"] = $cm->oPage->output_buffer;
		}
	} else {
		require($include);
	}

	return $return;
}
function get_response_by_service_srv($service, $params = array(), $schema_def = null) {
	$start 									= profiling_stopwatch();

	$cm 									= cm::getInstance();
	$post 									= $_POST;
	$request 								= $_REQUEST;

	$_POST 									= $params["data"];
	$_REQUEST 								= $_POST;
	//parse_str($params						, $_GET);

	$service_path 							= ltrim(($params["url"]
												? $params["url"]
												: $service
											), "/");

	if(strpos($service_path, "srv/") === 0)
		$service_path = substr($service_path, 4);

	$include = resolve_include_service("/" . $service_path, $schema_def);
	if($include) {
		$return = get_response_include($include, $cm);
	} else {
		$return["error"] = "missing include path: " . "/" . $service_path;
	}

	if(DEBUG_PROFILING === true) {
		$return["exTime"] = profiling_stopwatch($start);
	}

	$_POST 									= $post;
	$_REQUEST 								= $request;

	return $return;
}
function get_response_by_service_api($service, $params = array(), $schema_def = null) {
	$start 									= profiling_stopwatch();

	//$cm 									= cm::getInstance();
	$get 									= $_GET;
	$request 								= $_REQUEST;

	$_GET 									= $params["data"];
	$_REQUEST 								= $_GET;
	//parse_str($params						, $_GET);
	$service_path 							= ($params["url"]
												? $params["url"]
												: $service
											);

	$arrPath = explode("/", ltrim($service_path, "/api/"), 2);
	$include = resolve_include_api("/" . $arrPath[1], "/api/" . $arrPath[0], $schema_def, "include");
	if($include) {
		$return["result"] = get_response_include($include);
	} else {
		$return["error"] = "missing include path: ". "/" . $arrPath[1];
	}

	if(DEBUG_PROFILING === true) {
		$return["exTime"] = profiling_stopwatch($start);
	}

	$_GET 									= $get;
	$_REQUEST 								= $request;

	return $return;
}
function get_response_by_service_http($service, $params = array()) {
	$start = profiling_stopwatch();

    check_function("get_locale");
	check_function("file_post_contents");

    $url 							= $params["url"];
	$method 						= $params["method"];
	if(strpos($url, "http") === 0) {
		$username 					= false;
		$password 					= false;
		if(!$method)
			$method 				= "GET";
		$data 						= $params["data"];
	} else {
		$url 						= "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . $url;
		$data["params"]             = $params["data"];
		$data["user_permission"]    = get_session("user_permission");
		$data["user_path"]          = $_SERVER["HTTP_REFERER"];
		$data["locale"]             = get_locale();
		$data["ip"]                 = $_SERVER["REMOTE_ADDR"];
	}


	$res = file_post_contents_with_headers($url, $data, $username, $password, $method);
	if($res["headers"]["response_code"] == "200") {
		$return 					= json_decode($res["content"], true);
		if(json_last_error()) {
			$return["html"] 		= $res["content"];
		}
	} else {
		$return["responseCode"] 	= $res["headers"]["response_code"];
	}
	if(is_array($params["response"])) {
		$return = $return + $params["response"];
	}
	if(DEBUG_PROFILING === true) {
		$return["exTime"] 			= profiling_stopwatch($start);
		if(strpos($res["content"], "Fatal error") !== false) {
			$return["error"] 		= strip_tags($res["content"]);
		} elseif(!$res["content"] && $res["headers"]["response_code"] == "200")
			$return["error"] 		= "Possible Max Execution Time";
	}
    return $return;
}

function get_response_by_service_async($service, $params = array())
{
    check_function("get_locale");

	$service_path 							= ltrim(($params["url"]
												? $params["url"]
												: $service
											), "/");

	$url = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/" . $service_path;

    $data["params"]             = $params["data"];
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
