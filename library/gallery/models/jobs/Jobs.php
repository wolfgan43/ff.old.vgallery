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

class Jobs extends vgCommon
{
	const SOCKET											= 10;	//Number soket
	const TIMEOUT											= 60; //Timeout Script (second)
	const ANTIFLOOD											= 4; //Block execution jobs Run (second)
	const REPEAT											= "1week";

	static $singleton                   					= null;

	protected $controllers              					= null;
	protected $services										= array(
																"nosql" 					=> null
																//, "sql"						=> null
																//, "fs" 						=> null
															);
	private $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "TRACE_DATABASE_"
																	, "table"               => "trace_spooler"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "TRACE_MONGO_DATABASE_"
																	, "table"               => "cache_spooler"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                  => "/cache/spooler"
																	, "name"                => array("source", "params")
																	, "var"					=> null
																	)
															);
    private $struct											= array(
    															"kid"						=> "number"
																, "pid"						=> "number"
																, "type"					=> "string" //request or script
																, "domain"					=> "string" //origin domain
																, "source"					=> "string"	//url request or abs path script
																, "referer"					=> "string"	//who call the service
																, "params"					=> "array"	//params
																, "request"					=> "array"	//params
																, "schedule"				=> "number" //schedule start job
																, "delay"					=> "number" //time in sec to repeat job
																, "repeat"					=> "string"	//range of time (1week, 1day, 1month ecc)
																, "priority"				=> "number" //number 0 to n where 0 is max priority

																, "created"					=> "number"	//timestamp creation
																, "last_update"				=> "number"	//timestamp last_update
																, "status"					=> "string"	//idle, running, completed
																, "server"					=> "array"
																, "session"					=> "array"
																, "runned"					=> "number" //how many times is runned
																, "logs"					=> "array"
																, "called"					=> "number" //how many times is called from user
															);
    private	$delay											= 10; //second
	private	$max_delay										= 3600;//3600; //second (1 hour)

	public static function getInstance($service)
	{
		if (self::$singleton === null)
			self::$singleton = new Jobs($service);
		else {
			self::$singleton->service = $service;
		}
		return self::$singleton;
	}
	public function __construct($service)
	{
		$this->service = $service;
        $this->setConfig($this->connectors, $this->services);

      //  $this->setConfig();
	}
	public static function api($url, $params = null, $session = null)
	{
		if(DEBUG_PROFILING === true)
			$start 								= Stats::stopwatch();

		check_function("get_schema_def");

		if($session) {
			@session_unset();
			@session_destroy();
			@session_start();

			self::setSession($session);
		}
		//set session session_start
		//$cm 									= cm::getInstance();
		$get 									= $_GET;
		$request 								= $_REQUEST;

		$_GET 									= $params;
		$_REQUEST 								= $_GET;
		//parse_str($params						, $_GET);

		$schema_def 							= get_schema_def();

		$arrPath = explode("/", ltrim($url, "/api/"), 2);
		$include 								= resolve_include_api("/" . $arrPath[1], "/api/" . $arrPath[0], $schema_def, "include");
		if($include) {
			$return["result"] 					= self::getInclude($include);
		} else {
			$return["error"] 					= "missing include path: ". "/" . $arrPath[1];
		}

		$_GET 									= $get;
		$_REQUEST 								= $request;

		if(DEBUG_PROFILING === true) {
			$return["exTime"] = Stats::stopwatch($start);
		}

		return $return;
	}
	public static function srv($url, $params = null, $session = null)
	{
		if(DEBUG_PROFILING === true)
			$start 								= Stats::stopwatch();

		check_function("get_schema_def");

		if($session) {
			@session_unset();
			@session_destroy();
			session_start();

			self::setSession($session);
		}

		$cm 									= cm::getInstance();
		$post 									= $_POST;
		$request 								= $_REQUEST;

		$_POST 									= $params;
		$_REQUEST 								= $_POST;
		//parse_str($params						, $_GET);

		$schema_def 							= get_schema_def();

		if(strpos(ltrim($url, "/"), "srv/") === 0)
			$url 								= substr($url, 4);

		$include 								= resolve_include_service("/" . $url, $schema_def);
		if($include) {
			$return 							= self::getInclude($include, $cm);
		} else {
			$return["error"] 					= "missing include path: " . "/" . $url;
		}

		$_POST 									= $post;
		$_REQUEST 								= $request;

		if(DEBUG_PROFILING === true) {
			$return["exTime"] = Stats::stopwatch($start);
		}

		return $return;
	}

	public static function req($url, $params = null, $method = "POST", $response = null, $server = null, $session = null) {
		if(DEBUG_PROFILING === true)
			$start 								= Stats::stopwatch();

		check_function("get_locale");
		check_function("file_post_contents");

		if(!$server)
			$server 							= self::getServer();
		if(!$session)
			$session 							= self::getSession();

		if(strpos($url, "://") !== false) {
			$username 							= false;
			$password 							= false;
			if(!$method)
				$method 						= "GET";
			$data 								= $params;
		} else {
			$url 								= "http" . ($server["HTTPS"] ? "s" : "") . "://" . $server["HTTP_HOST"] . $url;
			$data["params"]             		= $params;
			$data["user_permission"]    		= $session["user_permission"];
			$data["user_path"]         			= $server["HTTP_REFERER"];
			$data["locale"]             		= get_locale();
		}


		$res = file_post_contents_with_headers($url, $data, $username, $password, $method);
		if($res["headers"]["response_code"] == "200") {
			$return 							= json_decode($res["content"], true);
			if(json_last_error()) {
				$return["html"] 				= $res["content"];
			}
		} else {
			$return["responseCode"] 			= $res["headers"]["response_code"];
		}
		if(is_array($response)) {
			$return = $return + $response;
		}
		if(DEBUG_PROFILING === true) {
			$return["exTime"] 					= Stats::stopwatch($start);
			if(strpos($res["content"], "Fatal error") !== false) {
				$return["error"] 				= strip_tags($res["content"]);
			} elseif(!$res["content"] && $res["headers"]["response_code"] == "200")
				$return["error"] 				= "Possible Max Execution Time";
		}

		return $return;
	}

	public static function async($url, $params = array(), $server = null, $session = null) {
	   // require_once(FF_DISK_PATH . "/modules/security/common.php");
       // require_once(FF_DISK_PATH ."/library/gallery/common/get_locale.php"); //todo: da togliere e metterlo in classe statica

		if(!$server)
			$server 							= self::getServer();
		if(!$session)
			$session 							= self::getSession();

		if(strpos($url, "://") === false)
			$url 								= "http" . ($server["HTTPS"] ? "s" : "") . "://" . $server["HTTP_HOST"] . $url;

		$data             						= $params;
		$data["user_permission"]    			= $session["user_permission"];
		$data["user_path"]          			= $server["HTTP_REFERER"];
		$data["locale"]             			= (function_exists("get_locale")
                                                    ? get_locale()
                                                    : null
                                                );

		$postdata 								= http_build_query(
													$data
												);

		$url_info 								= parse_url($url);
		switch ($url_info['scheme']) {
			case 'https':
				$scheme 						= 'ssl://';
				$port 							= 443;
				break;
			case 'http':
			default:
				$scheme 						= '';
				$port 							= 80;
		}

		try {
			$fp 								= fsockopen($scheme . $url_info['host']
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
			Cache::log("URL: " . $scheme . $url_info['host'] . "  Error: " . $errstr, "request_async");
		}

		return ($errstr ? $errstr : false);
	}
    public static function setScript($callback, $schedule = null, $repeat = null) {
	    static $source              = null;

	    if(!$source) {
	        if(strpos($callback, "/job/") === 0) {
                $source             = $callback;
            }
        } else {
            $params                = (is_callable($callback)
                                        ? call_user_func($callback)
                                        : (is_array($callback)
                                            ? $callback
                                            : array()
                                        )
                                    );

            Jobs::getInstance()->add($source, $params, $schedule, $repeat);

            $source                 = null;
        }
    }
    public static function runScript($callback = null) {
	    static $return              = null;
        static $params              = null;

        if(is_callable($callback)) {
            $return                 = call_user_func($callback, $params);
            $params                 = null;
        } elseif($callback) {
            $params                 = $callback;
        } elseif($callback === false) {
            $res                    = $return;
            $return                 = null;
        }

        return $res;
    }
	/**
	 * @param null $source
	 * @param null $params
	 * @param null $delay
	 * @param null $repeat
	 * @param null $schedule
	 * @param bool $session
	 * @param null $priority
	 * @return null
	 */
	public function add($source = null, $params = null, $schedule = null, $repeat = null, $session = false, $priority = null) {
		if(strpos($source, "/api") === 0) {
			$type 						= "api";
		} elseif(strpos($source, "srv/") === 0) {
			$type 						= "service";
		} elseif(strpos($source, "/srv/") === 0) {
			$type 						= "service";
			$source 					= ltrim($source, "/");
        } elseif(strpos($source, "/job/") === 0) {
            $type 						= "script";
		} elseif(substr($source, 0, 1) === "/") {
			$type 						= "request";
		} elseif(strpos($source, "://") !== false) {
			$type 						= "request";
		} elseif(strpos($source, "::") !== false) {
			$type 						= "obj";
		} else {
			$type 						= "func";
		}

        $status                         = "idle";
        $created 						= time();
		$delay 							= $this->delay;
		$server 						= $this->getServer();

        if($schedule && !is_numeric($schedule))
            $schedule                   = strtotime($schedule);

		if(!$schedule)
			$schedule 					= $created + $delay;
        elseif($schedule > time())
            $status                     = "completed";

		$insert = array(
			"kid"						=> ceil($created * microtime())
			, "type"					=> $type
			, "domain"					=> vgCommon::DOMAIN
			, "source"					=> $source
			, "referer"					=> $server["HTTP_REFERER"]
			, "params"					=> ($params ? $params : array())
			, "request"					=> ($params ? $params : array())
			, "schedule"				=> $schedule
			, "delay"					=> $delay
			, "repeat"					=> ($repeat === true
											? self::REPEAT
											: $repeat
										)
			, "priority"				=> $priority

			, "created"					=> $created
			, "last_update"				=> $created

			, "server"					=> $server
			, "session"					=> ($session
											? $this->getSession()
											: array()
										)

			, "status"					=> $status
			, "runned"					=> 0
			, "logs"					=> array()
			, "called"					=> 0
			, "pid"						=> 0
		);

		$storage                        = $this->getStorage();
		$job = $storage->read(array(
			"source" 					=> $insert["source"]
			, "params"					=> $insert["params"]
			, "session"					=> $insert["session"]
		), array(
			"kid"						=> true
			, "status"					=> true
			, "repeat"					=> true
		));

		if(is_array($job["result"]) && count($job["result"])) {
			if(!$job["result"][0]["repeat"] && $job["result"][0]["status"] != "running") {
				$storage->update(array(
						"called"		=> "++"
						, "last_update"	=> $created
						, "status"		=> "idle"
					)
					, array(
						"kid" 		    => $job["result"][0]["kid"]
					)
				);
			}
		} else {
			$storage->insert($insert);
		}

		return $this->getResult();
	}

	public function run($exec = false) {
		if((filemtime(__FILE__) + $this::ANTIFLOOD) >= time()) {
			return false;
		}
		touch(__FILE__, time());

		set_time_limit(self::TIMEOUT);

		$this->getStorage()->update(array(
			"status"						=> "idle"
		), array(
			"status"						=> "completed"
			, "schedule<="					=> time()
            , "domain"                      => vgCommon::DOMAIN
		));

		$storage 							= $this->getStorage();

		$jobs = $storage->read(array(
			"status" 						=> "running"
            , "domain"                      => vgCommon::DOMAIN
		), array(
			"kid"							=> true
			, "pid" 						=> true
			, "last_update"					=> true
		));
		$running 							= 0;
		if(is_array($jobs["result"]) && count($jobs["result"])) {
			$kids 							= null;
			foreach($jobs["result"] AS $job) {
				if(($job["last_update"] + (self::TIMEOUT * 2)) < time()) {
					@posix_kill($job["pid"], SIGKILL);
					$this->getStorage()->update(array(
						"status"			=> "error"
						, "last_update" 	=> time()
						, "pid"				=> "0"
					), array(
						"kid" 				=> $job["kid"]
					));
				} else {
					$running++;
				}
			}

		}
		$socket 							= $this::SOCKET - $running;
		if($socket > 0) {
			$jobs = $storage->read(array(
				"status" 				    => "idle"
                , "domain"                  => vgCommon::DOMAIN
			), array(
				"kid"						=> true
				, "type" 					=> true
				, "source" 					=> true
				, "params" 					=> true
				, "request" 				=> true
				, "session" 				=> true
				, "repeat"					=> true
				, "delay"					=> true
				, "logs"					=> true
				, "last_update"				=> true
			), array(
				"schedule"					=> "1"
				, "called"					=> "-1"
			), $socket);

			if($exec && is_array(jobs["result"]) && count(jobs["result"]))
				$this->exec(array_shift($jobs["result"]));

			return $jobs["result"];
		} else {
			Cache::log("no socket available", "jobs");
		}

		return $this->getResult();
	}

	public function exec($job) {
		$storage = $this->getStorage();

		if($job && !is_array($job)) {
			$jobs = $storage->read(array(
				"kid" 						=> $job
			), array(
				"kid"						=> true
				, "type" 					=> true
				, "source" 					=> true
				, "params" 					=> true
				, "request" 				=> true
				, "session" 				=> true
				, "repeat"					=> true
				, "delay"					=> true
				, "logs"					=> true
				, "last_update"				=> true
			), null, 1);

			if(is_array($jobs["result"]) && count($jobs["result"])) {
				$job 						= array_shift($jobs["result"]);
			}
		}

		if(is_array($job)) {
			$this->getStorage()->update(array(
				"status"					=> "running"
				, "pid"						=> getmypid()
				, "last_update" 			=> time()
			), array(
				"kid" 						=> $job["kid"]
			));

			$funcController 				= "controller_" . $job["type"];

			$log 							= $this->$funcController($job);

			//after process
			$request 						= array();
			$status  						= "completed";
			$repeat 						= $job["repeat"];
			$now 							= time();

			if($log) {
				$log["result"] 				= json_encode($log["result"]);
				$log["created"] 			= $now;
				if($log["request"] != "null" && is_array($log["request"]) && count($log["request"])) {
					$request 				= $log["request"];
					$status 				= "idle";
				}

				if($log["repeat"] === true)
					$repeat 				= self::REPEAT;
				else
					$repeat 				= (string) $log["repeat"];

				//} elseif($log === false) {
				//	$status 		= "idle";
			} elseif($log === null) {
			    Cache::log($job, "job_response_empty");
			}

			if($repeat && !count($request)) {
				//$status 					= "idle";
				$delay 						= $this->delay;
				$request					= $job["params"];
				if(is_numeric($repeat)) {
					$schedule 				= $now + $repeat;
				} else {
					$date 					= DateTime::createFromFormat('U', $now);
					$date->modify("+" . $repeat);

					$schedule 				= $date->getTimestamp();
				}
			} else {
				$delay 						= $job["delay"] + ceil($log["exTime"]);
				if($delay > $this->max_delay)
					$delay 					= $this->max_delay;

				$schedule 					= $now + $delay;
			}

			$logs 							= $job["logs"];
			if($log)
			    $logs[] 				    = $log;

			$this->getStorage()->update(array(
				"last_update" 				=> $now
				, "status"					=> $status
				, "pid"						=> "0"
				, "logs" 					=> $logs
				, "delay"					=> $delay
				, "schedule"				=> $schedule
				, "repeat"					=> $repeat
				, "request"					=> $request
				, "runned"					=> "++"
			), array(
				"kid" 						=> $job["kid"]
			));
		} else {
			$this->isError("Invalid Job");
		}
	}
	public function dump($schedule) {
		return $this->getResult();
	}

	public function getConfig($type, $config = null) {
		if(!$config)
			$config = $this->services[$type]["connector"];

		if(is_array($config))
			$config = array_replace($this->connectors[$type], array_filter($config));
		else
			$config = $this->connectors[$type];

		return $config;
	}

	public function getScripts() {
        $it = new FilesystemIterator($this->getDiskPath() . vgCommon::ASSETS_PATH . "/jobs");
        foreach ($it as $fileinfo) {
            if($fileinfo->getATime() > $fileinfo->getMTime() )
                continue;

            $filename                               = $fileinfo->getFilename();

            self::setScript("/job/" . $filename);
            require($this->getDiskPath() . vgCommon::JOBS_PATH . "/" . $filename);
        }
    }
	private static function getInclude($include, $cm = null) {
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

	private static function getServer() {
		$server = array();

		if(is_array($_SERVER) && count($_SERVER)) {
			foreach($_SERVER AS $key => $value) {
				switch ($key) {
					case "HTTP_COOKIE";
					case "REMOTE_ADDR";
					case "REMOTE_PORT";
					case "REQUEST_TIME_FLOAT";
					case "REQUEST_TIME";

					break;
					default:
						$server[$key] = $value;
				}
			}
		}

		return $server;
	}

	private static function getSession($appid = APPID) {
		$session = array();

		if(is_array($_SESSION) && count($_SESSION)) {
			foreach($_SESSION AS $key => $value) {
				$name = str_replace($appid, "", $key);
				switch($name) {
					case "__FF_SESSION__":
					case "cache":
						break;
					default:
						$session[$name] = $value;
				}

			}
		}
		return $session;
	}

	private function setSession($session, $appid = APPID) {
		$_SESSION = array();
		if(is_array($session) && count($session)) {
			foreach($session AS $key => $value) {
				$_SESSION[$appid . $key] = $value;
			}
		}
	}

	private function controller_api($job) {
		return self::api($job["source"], $job["request"], $job["session"]);
	}
	private function controller_service($job) {
		return self::srv($job["source"], $job["request"], $job["session"]);
	}
	private function controller_request($job) {
		return self::req($job["source"], $job["request"], "POST", null, $job["server"], $job["session"]);
	}
    private function controller_script($job) {
        $return                     = null;
	    $params                     = $job["request"];
        $script                     = $this->getDiskPath() . vgCommon::JOBS_PATH . "/" . basename($job["source"]);
        $output                     = exec("php -l " . addslashes($script));

        if(strpos($output, "No syntax errors") === 0) {
            Jobs::runScript($params);
            require($script);
            if(!$return)
                $return = Jobs::runScript(false);
        } else {
            $this->isError("syntax errors into script");
            Cache::log($output, "job_error");
        }
        return $return;
    }
	private function controller_func($job) {
//todo: da fare la call di una funzione
	}
	private function controller_obj($job) {
//todo: da fare la call di un oggetto
	}

	private function getStorage()
	{
		return Storage::getInstance($this->services, array(
			"struct" => $this->struct
		));
	}
	/**
	 * @param null $service
	 */
	/*private function controller()
	{
		$type                                                           	= $this->service;

		if(!$this->driver[$type]) {
			$controller                                                 	= "jobs" . ucfirst($type);
			require_once($this->getAbsPathPHP("/jobs/services/" . $type, true));

			$driver                                                     	= new $controller($this);
			//$db                                                         	= $driver->getDevice();

			$this->driver[$type] 											= $driver;
		}

		return $this->driver[$type];
	}*/
	/*private function setConfig()
	{
		foreach($this->connectors AS $name => $connector) {
			if(!$connector["name"]) {
				$prefix = ($connector["prefix"] && defined($connector["prefix"] . "NAME") && constant($connector["prefix"] . "NAME")
					? $connector["prefix"]
					: vgCommon::getPrefix($name)
				);

				if (is_file($this->getAbsPathPHP("/config")))
				{
					require_once($this->getAbsPathPHP("/config"));

					$this->connectors[$name]["host"] = (defined($prefix . "HOST")
						? constant($prefix . "HOST")
						: "localhost"
					);
					$this->connectors[$name]["name"] = (defined($prefix . "NAME")
						? constant($prefix . "NAME")
						:  ""
					);
					$this->connectors[$name]["username"] = (defined($prefix . "USER")
						? constant($prefix . "USER")
						: ""
					);
					$this->connectors[$name]["password"] = (defined($prefix . "PASSWORD")
						? constant($prefix . "PASSWORD")
						: ""
					);

				}
			}
		}

		foreach($this->services AS $type => $data)
		{
			if(!$data)
			{
				$this->services[$type] = array(
					"service" => $this->connectors[$type]["service"]
					, "connector" => $this->connectors[$type]
				);
			}
		}


	}*/

	private function getResult()
	{
		return ($this->isError()
			? $this->isError()
			: false
		);
	}
}

