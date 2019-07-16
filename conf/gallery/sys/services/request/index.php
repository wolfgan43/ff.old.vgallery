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
	Cache::log(print_r($cm->oPage->page_js, true), "request_error_js");
if(is_array($cm->oPage->page_css) && count($cm->oPage->page_css))
	Cache::log(print_r($cm->oPage->page_css, true), "request_error_css");

$cm->oPage->page_js = array();
$cm->oPage->page_css = array();

$response = array();
if($_POST["params"])
    $req = json_decode($_POST["params"], true);

unset($_POST["params"]);
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["PATH_INFO"] = $_SERVER["XHR_PATH_INFO"];

$globals = ffGlobals::getInstance("gallery");
$globals->user_path = $_SERVER["PATH_INFO"];

check_function("get_schema_def");
check_function("system_layer_shards");
//check_function("Notifier");

Cms::getInstance("frameworkcss")->getFramework(FF_THEME_FRAMEWORK_CSS);
Cms::getInstance("frameworkcss")->getFontIcon(FF_THEME_FONT_ICON);

//system_layer_shards_page_by_referer();

//da gestire la priority: 2 e normal
// da gestire i socket
if(is_array($req) && count($req)) {
	/*$stack 											= array(
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
													);*/
	$schema = Cms::getSchema();

	//$schema_def = get_schema_def();

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

	$shards = null;

    foreach($req AS $service_name => $service) {
		$arrShard 					= explode("/", ltrim($service_name, "/"), 2);
		$service["opt"]["type"]		= $arrShard[0];
		$service["opt"]["path"] 	= $arrShard[1];
		//$service["opt"]["schema"] 	= $schema_def;
		$service_path				= ($service["opt"]["url"]
										? $service["opt"]["url"]
										: $service_name
									);
		if($schema["page"]["/" . $service["opt"]["type"]]["group"] == "shard") {
			$shards[$schema["page"]["/" . $service["opt"]["type"]]["name"]][$service["opt"]["path"]] = "/" . $service["opt"]["type"];

			//$stack["shards"]["actions"][$schema["page"]["/" . $service["opt"]["type"]]["name"]][$service["opt"]["path"]] = "/" . $service["opt"]["type"];
		} elseif($schema["page"]["/" . $service["opt"]["type"]]["group"] == "service") {
			if($service["opt"]["async"]) {
				$response[$service_name] = Jobs::getInstance()->add($service_path, $service["params"]);
				//Jobs::async("/api/job");
                Jobs::async("/api/jobs/tools/run");

				/*$stack["top"]["actions"][$service_path] = array(
					"url" => $service_path
					, "data" => $service["params"]
				);*/
			} else {
				switch($service["opt"]["type"]) {
					case "srv":
						$response[$service_name] = Jobs::srv($service_path, $service["params"]);

						/*$stack["services"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
						);*/
						break;
					case "api":
						$response[$service_name] = Jobs::api($service_path, $service["params"]);

						/*$stack["api"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
						);*/
						break;
					default:
						$response[$service_name] = Jobs::req($service_path, $service["params"], $service["opt"]["method"], $service["response"]);
						/*$stack["externals"]["actions"][$service_name] = array(
							"url" => $service_path
							, "data" => $service["params"]
							, "method" => $service["opt"]["method"]
							, "response" => $service["response"]
						);*/
				}
			}
		} elseif(substr($service_path, 0, 1) === "/") {
			$shards["unknown"]["/" . $service["opt"]["path"]] = "/" . $service["opt"]["type"];
			//$stack["shards"]["unknown"]["actions"]["/" . $service["opt"]["path"]] = "/" . $service["opt"]["type"];
		} elseif(strpos($service_path, "http") === 0) {
			$response[$service_name] = Jobs::req($service_path, $service["params"], $service["opt"]["method"], $service["response"]);

			/*$stack["externals"]["actions"][$service_name] = array(
				"url" => $service_path
				, "data" => $service["params"]
				, "method" => $service["opt"]["method"]
				, "response" => $service["response"]
			);*/
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
                                $response["admin"]["html"] = process_admin_toolbar(
                                    $globals->user_path
                                    , $params["admin"]["theme"]
                                    , $params["admin"]["sections"]
                                    , $params["admin"]["css"]
                                    , $params["admin"]["js"]
                                    , $params["admin"]["international"]
                                    , $params["admin"]["seo"]
                                );
                                /*$stack["lazy"]["response"]["admin"]["html"] = process_admin_toolbar(
									$params["path"]
									, $params["admin"]["theme"]
									, $params["admin"]["sections"]
									, $params["admin"]["css"]
									, $params["admin"]["js"]
									, $params["admin"]["international"]
									, $params["admin"]["seo"]
								);*/
						}
					}
					break;
				case "cache": //todo: da verificare se serve e se e implementato bene
					Jobs::async("/api/cache/refresh", array("url" => $_SERVER["HTTP_REFERER"]));
					//check_function("refresh_cache");
					//$stack["lazy"]["actions"][$service_path] = true; // da fare
					break;
				case "notify":
					$response["notify"] = Notifier::response($service["keys"]);

					//$stack["api"]["response"]["notify"]["timer"] = false;
					//print_r($stack["api"]["response"]["notify"]);
					//die();
					break;
                case "notifyDelivered":
                    Notifier::delivered();
                    break;
				default:
					$response[$service_name] = Jobs::srv($service_path, $service["params"]);

					//$stack["services"]["actions"][$service_path] = $service["params"];
			}
		}
    }

    if(is_array($shards) && count($shards) && check_function("system_layer_shards")) {
		$response = $response + system_layer_shards($shards);
	}


	/*if(is_array($stack) && count($stack)) {
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
	}*/
}

if(check_function("system_set_media")) {
	$media = system_set_media_cascading(true);
	if($media) {
		$response["assets"] = $media;
	}
}

//if(DEBUG_PROFILING === true)
//	Cms::getInstance("debug")->benchmark("Request", true);

echo ffCommon_jsonenc($response, true);
exit;


