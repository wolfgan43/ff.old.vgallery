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

class statsPage
{
	const TYPE                                              = "page";

	private $device                                         = null;
	private $stats                                        	= null;
	private $services										= array(
		"nosql" 					=> null
		//, "fs" 					=> null
	);
	private $connectors										= array(
		"sql"                       => array(
			"host"          		=> null
		, "username"    		=> null
		, "password"   			=> null
		, "name"       			=> null
		, "prefix"				=> "CACHE_DATABASE_"
		, "table"               => "cache_pages"
		, "key"                 => "ID"
		)
	, "nosql"                   => array(
			"host"          		=> null
		, "username"    		=> null
		, "password"    		=> null
		, "name"       			 => null
		, "prefix"				=> "CACHE_MONGO_DATABASE_"
		, "table"               => "cache_pages"
		, "key"                 => "ID"
		)
	, "fs"                      => array(
			"path"                  => "/cache/pages"
		, "name"                => "title"
		, "var"					=> "s"
		)
	);
	private $struct											= array(
		"url"						=> "string"
	, "get"						=> "array"
	, "domain"					=> "string"
	, "type"					=> "string"
	, "event"					=> "string"
		/*, "action"					=> array(
			"name"					=> "string"
			, "value"				=> "string"
		)*/
	, "title" 					=> "string"
	, "description" 			=> "string"
	, "cover"					=> array(
			"url" 					=> "string:toImage"
		, "width" 				=> "number"
		, "height" 				=> "number"
		)
	, "author" 					=> array(
			"id" 					=> "number"
		, "avatar" 				=> "string:toImage"
		, "name" 				=> "string"
		, "url" 				=> "string"
		, "tags"				=> array(
				"primary" 			=> "arrayOfNumber"
			, "secondary" 		=> "arrayOfNumber"
			)
		, "uid"					=> "number"
		)
	, "tags"					=> array(
			"primary" 				=> "arrayOfNumber"
		, "secondary" 			=> "arrayOfNumber"
		, "rel" 				=> "arrayOfNumber"
		)
	, "meta"					=> "array"
	, "links"					=> "array"
	, "microdata"				=> "array"
	, "js"						=> array(
			"url" 					=> "string"
		, "keys" 				=> "array"
		)
	, "css"						=> array(
			"url" 					=> "string"
		, "keys" 				=> "array"
		)
	, "international"			=> "array"
	, "settings"				=> "array" 	//$globals->page
	, "template_layers"			=> "array"	//$globals->cache["layer_blocks"]
	, "template_sections"		=> "array"	//$globals->cache["section_blocks"]
	, "template_blocks"			=> "array"	//$globals->cache["layout_blocks"]
	, "template_ff"				=> "array"
	, "keys_D"					=> "arrayOfNumber"
	, "keys_G"					=> "array"
	, "keys_M"					=> "array"
	, "keys_S"					=> "array"
	, "keys_T"					=> "array"
	, "keys_V"					=> "arrayOfNumber"
	, "http_status"				=> "number"
	, "created"					=> "number"
	, "last_update"				=> "number"
	, "cache_last_update"		=> "number"
	, "cache"					=> "array"
	, "user_vars"				=> "array"
	);

	public function __construct($stats)
	{
		$this->stats = $stats;
		$this->setConfig();
	}

	public function getDevice()
	{
		return $this->device;
	}




}


define("TRACE_DISK_PATH", FF_DISK_PATH); // dirname(dirname(dirname(__DIR__))));

function system_trace($action, $url = null, $get = null, $visitor = null) {
	if(!$visitor)
		$visitor = system_trace_get_visitor();

	if($visitor)
	{
		if(!$url) {
			$url = $_SERVER["PATH_INFO"];
			if($url == "/index")
				$url = "/";
		}

		if(!$get) {
			$res = cache_get_request($_GET);
			$get = $res["request"];
		}

		$referer = $_SERVER["HTTP_REFERER"];

		if(!is_array($action)) {
			$action = array(
				"name" => $action
			, "value" => null
			);
		}

		switch($action["name"]) {
			case "search":
				break;
			case "redirect":
				$referer = $_SERVER["PATH_INFO"];
				if($referer == "/index")
					$referer = "/";

				if(!$action["value"])
					$action["value"] = $_SERVER["HTTP_REFERER"];

			default:
				$user_permission = $_SESSION[APPID . "user_permission"];
				if(is_file(TRACE_DISK_PATH . "/library/mobiledetect/class.mobiledetect.php"))
				{
					require_once(TRACE_DISK_PATH . "/library/mobiledetect/class.mobiledetect.php");
					$device = new mobileDetect();

					$detect["device"]["name"] = $device->isMobile();
					if($detect["device"]["name"]) {
						$detect["device"]["type"] = "Mobile";
					} else {
						$detect["device"]["name"] = $device->isTablet();
						if($detect["device"]["name"]) {
							$detect["device"]["type"] = "Tablet";
						} else {
							$detect["device"]["type"] = "Desktop";
						}
					}
				}

				if(is_file(TRACE_DISK_PATH . "/library/browser/class.browser.php"))
				{
					require_once(TRACE_DISK_PATH . "/library/browser/class.browser.php");
					$browser = new Browser();

					$detect["browser"]["name"] = $browser->getBrowser();
					$detect["browser"]["ver"] = $browser->getVersion();
					$detect["platform"] = $browser->getPlatform();
				}

				$hit = time();

				$pages = Stats::getInstance("page")->set(array(
					"hits" => "++"
				, "hits-" . date("Y", $hit) 			=> "++"
				, "hits-" . date("Y-m", $hit) 		=> "++"
				, "hits-" . date("Y-m-d", $hit) 		=> "++"
				),
					array(
						"url" 										=> $url
					, "get" 									=> $get
					, "domain" 									=> DOMAIN_INSET
					)
				);

				if(is_array($pages)) {
					$page = $pages["result"][0];
					if($pages["result"][0]["author"]["id"]) {
						Stats::getInstance("user")->set(array(
							"hits" => "++"
						, "hits-" . date("Y", $hit) 		=> "++"
						, "hits-" . date("Y-m", $hit) 	=> "++"
						, "hits-" . date("Y-m-d", $hit)	=> "++"
						), $page["author"]["id"]);
					}
				}

				$trace = array(
					"visitor" 										=> $visitor["unique"]
				, "url" 										=> $url
				, "get" 										=> $get
				, "domain" 										=> $_SERVER["HTTP_HOST"]
				, "action" 										=> $action
				, "referer" 									=> $referer
				, "user_agent" 									=> $_SERVER["HTTP_USER_AGENT"]
				, "device" 										=> $detect["device"]
				, "browser" 									=> $detect["browser"]
				, "platform" 									=> $detect["platform"]
				, "page" 										=> array(
						"title" 									=> $page["title"]
					, "description" 							=> $page["description"]
					, "tags" 									=> (is_array($page["tags"])
							? $page["tags"]
							: array()
						)
					, "author" 									=> $page["author"]
					)
				, "user" 										=> array(
						"id" 										=> $user_permission["ID"]
					, "name" 									=> $user_permission["name"]
					, "surname" 								=> $user_permission["surname"]
					, "avatar" 									=> $user_permission["avatar"]
					, "email" 									=> $user_permission["email"]
					)
				, "created" => time()
				);

				if(is_file(TRACE_DISK_PATH . "/conf/gallery/config/trace.php")) {
					require_once(TRACE_DISK_PATH . "/conf/gallery/config/trace.php");

					if(defined("TRACE_MONGO_DATABASE_NAME"))
					{
						if(!class_exists("ffDB_MongoDB"))
							require_once(TRACE_DISK_PATH . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");

						$db = new ffDB_MongoDB();
						$db->on_error = "ignore";

						$db->connect(TRACE_MONGO_DATABASE_NAME, TRACE_MONGO_DATABASE_HOST, TRACE_MONGO_DATABASE_USER, TRACE_MONGO_DATABASE_PASSWORD);
						$db->insert($trace, TRACE_TABLE_NAME);
					}

					if(defined("TRACE_DATABASE_NAME"))
					{
						$db = new ffDB_Sql();
						$db->on_error = "ignore";

						if($db->connect(TRACE_DATABASE_NAME, TRACE_DATABASE_HOST, TRACE_DATABASE_USER, TRACE_DATABASE_PASSWORD))
						{
							$sSQL = "INSERT INTO `" . TRACE_TABLE_NAME . "`
                                    (
                                        `ID`
                                        , `visitor`
                                        , `url`
                                        , `get`
                                        , `domain`
                                        , `action`
                                        , `action_value`
                                        , `referer`
                                        , `user_agent`
                                        , `device_type`
                                        , `device_name`
                                        , `browser_name`
                                        , `browser_ver`
                                        , `platform`
                                        , `page_title`
                                        , `page_description`
                                        , `page_tags`
                                        , `page_keywords`
                                        , `user_id`
                                        , `user_name`
                                        , `user_surname`
                                        , `user_email`
                                        , `created`
                                    )
                                    VALUES
                                    (
                                        null
                                        , " . $db->toSql($trace["visitor"]) . "
                                        , " . $db->toSql($trace["url"]) . "
                                        , " . $db->toSql(json_encode($trace["get"])) . "
                                        , " . $db->toSql($trace["domain"]) . "
                                        , " . $db->toSql($trace["action"]["name"]) . "
                                        , " . $db->toSql($trace["action"]["value"]) . "
                                        , " . $db->toSql($trace["referer"]) . "
                                        , " . $db->toSql($trace["user_agent"]) . "
                                        , " . $db->toSql($trace["device"]["type"]) . "
                                        , " . $db->toSql($trace["device"]["name"]) . "
                                        , " . $db->toSql($trace["browser"]["name"]) . "
                                        , " . $db->toSql($trace["browser"]["ver"]) . "
                                        , " . $db->toSql($trace["platform"]) . "
                                        , " . $db->toSql($trace["page"]["title"]) . "
                                        , " . $db->toSql($trace["page"]["description"]) . "
                                        , " . $db->toSql(implode(",", $trace["page"]["tags"])) . "
                                        , " . $db->toSql(implode(",", $trace["page"]["keywords"])) . "
                                        , " . $db->toSql($trace["user"]["id"]) . "
                                        , " . $db->toSql($trace["user"]["name"]) . "
                                        , " . $db->toSql($trace["user"]["surname"]) . "
                                        , " . $db->toSql($trace["user"]["email"]) . "
                                        , " . $db->toSql($trace["created"], "Number") . "
                                    )";
							$db->execute($sSQL);
						}
					}
				} else {
					system_write_trace($trace);
				}
		}
	}
}


function system_write_trace($trace, $filename = "index") {
	if(!is_dir(TRACE_DISK_PATH . "/cache/trace"))
		mkdir(TRACE_DISK_PATH . "/cache/trace", 0777, true);

	$file = TRACE_DISK_PATH . '/cache/trace/' . $filename . '.php';
	if(!is_file($file)) {
		$set_mod = true;
	}
	if($handle = @fopen($file, 'a'))
	{
		if(@fwrite($handle, '$t = ' . var_export($trace, true) . ";\n") === FALSE)
		{
			$i18n_error = true;
		}
		@fclose($handle);

		if($set_mod)
			chmod($file, 0777);
	}
}

function system_trace_isCrawler($user_agent)
{
	$isCrawler = true;
	$crawlers = array(
		'Google'=>'Google',
		'MSN' => 'msnbot',
		'Rambler'=>'Rambler',
		'Yahoo'=> 'Yahoo',
		'AbachoBOT'=> 'AbachoBOT',
		'accoona'=> 'Accoona',
		'AcoiRobot'=> 'AcoiRobot',
		'ASPSeek'=> 'ASPSeek',
		'CrocCrawler'=> 'CrocCrawler',
		'Dumbot'=> 'Dumbot',
		'FAST-WebCrawler'=> 'FAST-WebCrawler',
		'GeonaBot'=> 'GeonaBot',
		'Gigabot'=> 'Gigabot',
		'Lycos spider'=> 'Lycos',
		'MSRBOT'=> 'MSRBOT',
		'Altavista robot'=> 'Scooter',
		'AltaVista robot'=> 'Altavista',
		'ID-Search Bot'=> 'IDBot',
		'eStyle Bot'=> 'eStyle',
		'Scrubby robot'=> 'Scrubby',

		'GenericBot' => 'bot',
		'GenericCrawler' => 'crawler'
	);

	if($user_agent === null)
		$user_agent = $_SERVER["HTTP_USER_AGENT"];

	if($user_agent) {
		$crawlers_agents = implode("|", $crawlers);
		$isCrawler = (preg_match("/" . $crawlers_agents . "/i", $user_agent) > 0);
	}


	return $isCrawler;
}

function system_trace_get_visitor($user_agent = null) {
	if($user_agent === null)
		$user_agent = $_SERVER["HTTP_USER_AGENT"];

	if(!system_trace_isCrawler($user_agent)) {
		$long_time = time() + (60 * 60 * 24 * 365 * 30);

		if($_COOKIE["_ga"]) {
			$ga = explode(".", $_COOKIE["_ga"]);

			$visitor = array(
				"unique" => $ga[2]
			, "created" => $ga[3]
			, "last_update" => $ga[3]
			);
		} elseif($_COOKIE["__utma"]) {
			$utma = explode(".", $_COOKIE["__utma"]);

			$visitor = array(
				"unique" => $utma[1]
			, "created" => $utma[2]
			, "last_update" => $utma[4]
			);
		} elseif($_COOKIE["_uv"]) {
			$uv = explode(".", $_COOKIE["_uv"]);

			$visitor = array(
				"unique" => $uv[0]
			, "created" => $uv[1]
			, "last_update" => $uv[2]
			);
			if($visitor["last_update"] + (60 * 60 * 24) < time()) {
				$visitor["last_update"] = time();

				//$_COOKIE["_uv"] = implode(".", $visitor);
				setcookie("_uv", implode(".", $visitor), $long_time);
			}
		} else {
			$access = explode("E", hexdec(md5(
				$_SERVER["REMOTE_ADDR"]
				. $_SERVER["HTTP_USER_AGENT"]
			)));

			$offset = (strlen($access[0]) - 9);
			$visitor = array(
				"unique" => substr($access[0], $offset, 9)
			, "created" => time()
			, "last_update" => time()
			);
			//$_COOKIE["_uv"] = implode(".", $visitor);
			setcookie("_uv", implode(".", $visitor), $long_time);
		}
	} else {
		$visitor = false;
	}

	return $visitor;
}