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


abstract class vgCommon 
{
    const PHP_EXT                       = "php";
    const MAIN_MODEL                    = "cms";

    const SQL_PREFIX					= "FF_DATABASE_";
	const NOSQL_PREFIX					= "MONGO_DATABASE_";
	const LIBS_MODELS_PATH              = "/library/gallery/models";
	const LIBS_CMS_PATH                 = "/library/gallery";
	const CONFIG_PATH                   = "/themes/site/conf";
    const JOBS_PATH                     = "/themes/site/jobs";
    const EMAIL_PATH                    = "/themes/site/contents/email";
    const TPL_PATH                      = "/themes/site/contents";
    const CACHE_PATH                    = "/cache";

	const ASSETS_PATH                   = "/themes/site";

	const DOMAIN                        = DOMAIN_INSET;

	protected $services 				= null;
	protected $controllers 				= null;

    private $error                      = null;
    private $debug                      = array();

    private static $env                 = array();
    private static $settings            = array();
    private static $request             = null;
    static $disk_path                   = null;
    private $theme                      = array(
                                            "cms"           => "gallery"
                                            , "frontend"    => "site"
                                        );

    /**
     * @param $type
     * @return string
     */
    public static function getPrefix($type)
	{
		switch ($type) {
			case "sql":
				return self::SQL_PREFIX;
				break;
			case "nosql":
				return self::NOSQL_PREFIX;
				break;
			default;
		}
	}
    public static function widget($name, $config = null) {
        $user_path                      = self::getPathInfo();
        $schema                         = self::schema(null, "widgets");

        if(is_array($schema[$user_path])) {
            $config                     = array_replace_recursive($config, $schema[$user_path]);
        } elseif(is_array($schema[$name])) {
            $config                     = array_replace_recursive($config, $schema[$name]);
        }

        return require(__DIR__ . "/" . strtolower(get_called_class()) . "/widgets/" . $name . "/index." . self::PHP_EXT);
    }

    public static function env($name, $value = null) {
        $class_name                     = strtolower(get_called_class());

        if($value) {
            self::$env[$class_name][$name]  = $value;
        }

        return self::$env[$class_name][$name];
    }
    /**
     * @return bool
     */
    protected static function _isXHR() {
        return $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }
    /**
     * @param null $what
     * @return string
     */
    protected static function _getDiskPath($what = null) {
        $path                           = "";
		if(!self::$disk_path) {
			self::$disk_path            = (defined("FF_DISK_PATH")
                                            ? FF_DISK_PATH
                                            : str_replace(self::LIBS_MODELS_PATH, "", __DIR__)
                                        );
		}

		switch ($what) {
            case "asset":
                $path                   = self::ASSETS_PATH;
                break;
            case "cache":
                $path                   = self::CACHE_PATH;
                break;
            case "model":
                $path                   = self::LIBS_MODELS_PATH;
                break;
            case "cms":
                $path                   = self::LIBS_CMS_PATH;
                break;
            case "config":
                $path                   = self::CONFIG_PATH;
                break;
            case "job":
                $path                   = self::JOBS_PATH;
                break;
            case "tpl":
                $path                   = self::TPL_PATH;
                break;
            case "":
            case null:
                break;
            default:
                $path                   = $what;

                $path_parts             = pathinfo($what);
                if(!$path_parts['extension']) {
                    $path               .= "/index." . self::PHP_EXT;
                }
        }

        return self::$disk_path . $path;
	}

	protected static function schema($arrSettings = null, $key = null) {
        $class_name                                             = strtolower(get_called_class());
        if(!self::$settings[$class_name]) {
            self::$settings[$class_name]                        = array();

            if($class_name == self::MAIN_MODEL) {
                $params                                         = array(
                                                                    "default_path"  => self::MAIN_MODEL
                                                                    , "ext_path"    => "asset"
                                                                    , "ext_name"    => "settings" . "." . self::PHP_EXT
                                                                );
            } else {
                $params                                         = array(
                                                                    "default_path"  => "model"
                                                                    , "ext_path"    => "config"
                                                                    , "ext_name"    => "settings" . "." . $class_name . "." . self::PHP_EXT
                                                                );

            }

            $settings_path                                      = self::_getDiskPath($params["default_path"])
                                                                    . "/settings" . "." . self::PHP_EXT;

            if (is_file($settings_path)) {
                $schema                                         = array();
                require($settings_path);

                self::$settings[$class_name]                    = $schema;
            }

            $settings_path                                      = self::_getDiskPath($params["ext_path"])
                                                                    . "/" . $params["ext_name"] ;

            if (is_file($settings_path)) {
                $schema                                         = array();
                require($settings_path);

                self::$settings[$class_name]                    = array_replace_recursive(self::$settings[$class_name], $schema);
            }

            if(is_array($arrSettings) && count($arrSettings)) {
                foreach($arrSettings AS $var => $path) {
                    if(is_file($path)) {
                        ${$var}                                 = array();
                        require($path);
                        self::$settings[$class_name][$var]      = ${$var};
                    }
                }
            }
        }

        return ($key
            ? self::$settings[$class_name][$key]
            : self::$settings[$class_name]
        );
    }
    protected static function getPathInfo($user_path = null) {
        $path_info = $_SERVER["PATH_INFO"];

        return ($user_path
            ? (strpos($path_info, $user_path) === 0
                ? substr($path_info, strlen($user_path))
                : false
            )
            : $path_info
        );
    }

    protected static function getReq($method = null) {
        switch(strtolower($method)) {
            case "post":
            case "patch":
            case "delete":
                $req                                                                    = $_POST;
                break;
            case "get":
                $req                                                                    = $_GET;
                break;
            case "cookie":
                $req                                                                    = $_COOKIE;
                break;
            case "session":
                $req                                                                    = $_SESSION;
                break;
            default:
                $req                                                                    = $_REQUEST;

        }

        return $req;
    }

    protected static function getRequest($rules = null, $key = null) {
        $count_max                                                                      = 1000;
        $count_default                                                                  = 200;

        if(!self::$request) {
            self::$request                                                              = array(
                                                                                            "rawdata" => array()
                                                                                            , "unknown" => array()
                                                                                        );
            $request                                                                    = self::getReq($rules["request_method"]);
            if(is_array($request) && count($request)) {
                self::$request["rawdata"]                                               = $request;
                $arrRuleGet                                                             = (is_array($rules["get"])
                                                                                            ? array_flip($rules["get"])
                                                                                            : array()
                                                                                        );
                foreach($request AS $req_key => $req_value) {
                    //if(is_array($request[$req_key]))                                    continue;
                    //if(is_array($req_value))                                            continue;

                    $rkf                                                                = str_replace("?", "", $req_key);
                    switch($rkf) {
                        case "_ffq_":
                        case "__nocache__":
                        case "__debug__":
                        case "__query__":
                            unset(self::$request["rawdata"][$req_key]);
                            break;
                        case "gclid": //params di google adwords e adsense
                        case "utm_source":
                        case "utm_medium":
                        case "utm_campaign":
                        case "utm_term":
                        case "utm_content":
                            self::$request["gparams"][$rkf]                             = $req_value;
                            unset(self::$request["rawdata"][$req_key]);
                            break;
                        case "q":
                            self::$request["search"]                                    = $req_value;
                            break;
                        case "page":
                            if(is_numeric($req_value) && $req_value > 0) {
                                self::$request["navigation"]["page"]                    = $req_value;
                                //if($req_value > 1)
                                //    self::$request["query"]["page"]                     = "page=" . urlencode(self::$request["navigation"]["page"]);
                            }
                            break;
                        case "count":
                            if(is_numeric($req_value) && $req_value > 0) {
                                self::$request["navigation"]["count"]                   = $req_value;

                                //self::$request["query"]["count"]                        = "count=" . urlencode(self::$request["navigation"]["rec_per_page"]);
                            }
                            break;
                        case "sort":
                            self::$request["sort"]                                      = $req_value;

                            //self::$request["query"]["sort"]                             = "sort=" . urlencode(self::$request["sort"]["name"]);
                            break;
                        case "dir":
                            self::$request["dir"]                                       = $req_value;

                            //self::$request["query"]["dir"]                              = "dir=" . urlencode(self::$request["sort"]["dir"]);
                            break;
                        case "ret_url":
                        case "lang":
                            break;
                        default:
                            if($req_key != $rkf) {
                                self::$request["invalid"][$req_key]                     = $req_key . "=" . urlencode($req_value);
                                unset(self::$request["rawdata"][$req_key]);
                            } elseif(isset($arrRuleGet[$rkf])) {
                                //$res["get"]["search"]["available_terms"][$rkf] = $req_value;
                                //$res["get"]["query"][$rkf] = $rkf . "=" . urlencode($res["get"]["search"]["available_terms"][$rkf]);
                            } elseif($rules["exts"][$rkf]) {
                                eval('self::$request' . $rules["exts"][$rkf] . ' = ' . $req_value . ";");
                            } elseif(is_numeric($rkf) && !$req_value) {
                                self::$request["invalid"][$rkf]                         = $rkf . "=" . urlencode($req_value);
                                unset(self::$request["rawdata"][$req_key]);
                            } elseif(!preg_match('/[^a-z\-0-9_\+]/i', $rkf)) {
                                if(is_array($req_value)) {
                                    self::$request["unknown"]                               = array_replace((array) self::$request["unknown"], $req_value);
                                }   else {
                                    self::$request["unknown"][$rkf]                         = $req_value;
                                }
                                /*if(is_array($req_value)) {
                                    self::$request["search"]["terms"]                   = array_replace((array) self::$request["search"]["terms"], $req_value);
                                } else {
                                    self::$request["search"]["terms"][$rkf]             = $req_value;
                                }*/
                               // self::$request["invalid"][$rkf]                         = $rkf . "=" . urlencode($req_value);
                            } else {
                                self::$request["invalid"][$rkf]                         = $rkf . "=" . urlencode($req_value);
                            }
                    }
                }
            }

            if (self::$request["navigation"]
                && self::$request["navigation"]["count"] > $count_max)                  self::$request["navigation"]["count"] = $count_max;

            self::$request["dir"]                                                       = (self::$request["dir"] === "-1" || self::$request["dir"] === "DESC"
                                                                                            ? "-1"
                                                                                            : "1"
                                                                                        );
        }

        $res                                                                            = self::$request;

        if(!$res["navigation"]["count"]) {
            $res["navigation"]["count"]                                                 = ($rules["navigation"]["count"]
                                                                                            ? $rules["navigation"]["count"]
                                                                                            : $count_default
                                                                                        );
        }
        if(!$res["navigation"]["page"]) {
            $res["navigation"]["page"]                                                 = ($rules["navigation"]["page"]
                                                                                            ? $rules["navigation"]["page"]
                                                                                            : "1"
                                                                                        );
        }

        //Mapping Request by Rules
        if(is_array($rules["mapping"]) && count($rules["mapping"])) {
            foreach($rules["mapping"] AS $rule_key => $rule_value) {
                if(!is_array($rule_value))                                              $rule_value = array($rule_value);

                foreach($rule_value AS $rule_type) {
                    $rKey                                                               = (is_numeric($rule_key)
                        ? $rule_type
                        : $rule_key
                    );

                    if($res["unknown"][$rKey]) {
                        $res[$rule_type][$rKey]                                         = $res["unknown"][$rKey];
                        unset( $res["unknown"][$rKey]);
                    }
                }
            }
        }

        if($key == "query") {
            //Creation query
            $res["query"]["select"]                                                     = (array)$rules["select"];

            if(!count($res["unknown"]))                                                 $res["unknown"] = array_combine((array) $rules["default"], (array) $rules["default"]);
            foreach($res["unknown"] AS $unknown_key => $unknown_value) {
                if($rules["fields"][$unknown_key])                                      $res["query"]["select"][$rules["fields"][$unknown_key]] = $unknown_value;
            }

            //da togliere reqallowed


            //where calc
            $res["query"]["where"]                                                      = (array)$rules["where"];
            if (is_array($res["search"])) {
                foreach ($res["search"] AS $search_key => $search_value) {
                    if ($rules["fields"][$search_key] && !$res["query"]["where"][$rules["fields"][$search_key]])
                        $res["query"]["where"][$rules["fields"][$search_key]]           = $search_value;
                }
            } elseif ($res["search"]) {
                foreach ($rules["fields"] AS $field_key => $field_value) {
                    $res["query"]["where"]['$or'][$field_value]                         = $res["search"];
                    //$res["query"]["where"]['$or'][] = array($field_key => $res["search"]);
                }
            }
            if(!count($res["query"]["where"]))                                          $res["query"]["where"] = true;

            //order calc
            if (is_array($res["sort"])) {
                foreach ($res["sort"] AS $sort_key => $sort_value) {
                    if ($rules["fields"][$sort_key] && !$res["query"]["order"][$rules["fields"][$sort_key]])
                        $res["query"]["order"][$rules["fields"][$sort_key]]             = ($sort_value === "-1" || $sort_value === "DESC"
                                                                                            ? "-1"
                                                                                            : ($sort_value === "1" || $sort_value === "ASC"
                                                                                                ? "1"
                                                                                                : $res["dir"]
                                                                                            )
                                                                                        );
                }
            }
            $res["query"]["order"]                                                      = array_replace((array)$rules["order"], (array)$res["query"]["order"]);
            if(!count( $res["query"]["order"]))                                         $res["query"]["order"] = null;

            //limit calc
            if ($res["navigation"]["page"] > 1 && $res["navigation"]["count"]) {
                $res["query"]["limit"]["skip"]                                          = ($res["navigation"]["page"] - 1) * $res["navigation"]["count"];
                $res["query"]["limit"]["limit"]                                         = $res["navigation"]["count"];
            } elseif($res["navigation"]["count"]) {
                $res["query"]["limit"]                                                  = $res["navigation"]["count"];
            } else {
                $res["query"]["limit"]                                                  = null;
            }
        }


        $res["valid"]                                                                   = array_diff_key($res["rawdata"], (array) $res["unknown"]);

        return ($key
            ? $res[$key]
            : $res
        );
    }

    /*
    public function tpl_parse($name, $vars = array()) {
        $path = (strpos($name, FF_DISK_PATH) === 0
            ? $name
            : __DIR__ . "/" . $name
        );

        $content = file_get_contents($path);

        return str_replace(array_keys($vars), array_values($vars), $content);
    }*/


    protected function dirname($path) {
        return dirname($path);
    }
    /**
     * @param null $path
     * @return string
     */
    public function isXHR() {
        return $this::_isXHR();
    }
    /**
     * @param null $path
     * @return string
     */
    public function getDiskPath($path = null) {
		return $this::_getDiskPath($path);
	}

    /**
     * @param $name
     * @return mixed
     */
    public function getTheme($name)
    {
        return $this->theme[$name];
    }

    /**
     * @param $path
     * @param bool $use_class_path
     * @return string
     */
    public function getAbsPathPHP($path, $use_class_path = false)
    {
        return $this->getAbsPath($path . "." . $this::PHP_EXT, $use_class_path);
    }

    /**
     * @param $path
     * @param bool $use_class_path
     * @return string
     */
    public function getAbsPath($path, $use_class_path = false)
    {
        return ($use_class_path
            ? __DIR__
            : $this::_getDiskPath()
        ) . $path;
    }

    /**
     * @param $controller
     * @param null $service
     */
    public function addService($controller, $service = null)
    {
        if($this->controllers[$controller])
        {
            $this->services[$controller] = (is_array($service)
                ? $service
                : ($service
                    ? array_replace($this->controllers[$controller], array("default" => $service))
                    : $service
                )
            );
        }
    }

    /**
     * @param null $note
     * @param null $params
     * @return array
     */
    public function debug($note = null, $params = null, $dump = false)
    {
        if($note !== null) {
            $source                     = get_called_class();
            $params["when"][]           = time();
            $this->debug[][$note]       = $params;
            Cache::log($note, $source);
        }
        if($dump) {
            Cms::getInstance("debug")->dump($note);
            exit;
        }
    }

    /**
     * @param null $exclude_file
     * @return mixed
     */
    public function debug_backtrace($exclude_file = null)
	{
		$stack 								= debug_backtrace();
		foreach($stack AS $script) {
			if($script["file"] != __FILE__
				&& $script["file"] != $exclude_file
				&& basename($script["file"]) != "common.php"
				&& basename($script["file"]) != "config.php"
			) {
				$res = str_replace(array($this->getDiskPath(), "/index.php"), "", $script['file']);
				break;
			}
		}
		return $res;
	}

    /**
     * @param null $error
     * @return null
     */
    public function isError($error = null)
    {
        if ($error === "") {
            $this->error = null;
        } elseif($error !== null)
        {
            $this->error = $error;
            $this->debug($error, null, true);
        }
        if($this->error)
            return $this->error;
    }

    /**
     * @param array $arr
     * @return bool
     */
    public function isAssocArray(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param $services
     */
    public function setServices($services) {
		if($services) {
			$this->services 					= null;

            if(!is_array($services))
                $services = array($services);

			if (is_array($services)) {
                if($this->isAssocArray($services)) {
                    foreach ($services AS $service => $controller) {
                        $this->addService($service, $controller);
                    }
                } else {
                    foreach ($services AS $service) {
                        $this->addService($service);
                    }
                }
			}/* elseif (strlen($services)) {
				reset($this->controllers);
				$controller 					= key($this->controllers);
				$this->controllers[$controller]["default"] = $services;

				$this->addService($controller, $this->controllers[$controller]);
			}*/
		}
	}

    /**
     * @param $name
     * @param $default
     */
    public function setController($name, $default) {
		if(is_array($default)) {
			$this->controllers[$name]["storage"] = $default;
		} else {
			$this->controllers[$name]["default"] = $default;
		}
	}

    /**
     * @param $service
     * @param null $param
     * @return mixed
     */
    public function getController($service, $param = null) {
		return ($param
			? $this->controllers[$service][$param]
			: $this->controllers[$service]
		);
	}

	public function getConfig($name) {
        return $this->services[$name]["connector"];
    }

    /**
     * @param $connector
     */
    public function getConnector($connector) {
		return $this->connectors[$connector];
	}

    /**
     * @param $params
     */
    public function setParams($params)
    {
        if(is_array($params) && count($params))
        {
            foreach($params AS $name => $value)
            {
                $this->setParam($name, $value);
            }
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->$name;
    }

    /**
     * @param $script_path
     */
    public function loadControllers($script_path, $controllers = null)
    {
        static $spl_loaded                                                                  = null;

        if(!$this->controllers_rev && is_dir($script_path . "/services"))
        {
            $services                                                                       = glob($script_path . "/services/*");
            if(is_array($services) && count($services)) {
                $class                                                                      = strtolower(get_called_class());
                foreach($services AS $service) {
                    $arrService                                                             = explode("_", basename($service, "." . $this::PHP_EXT), 2);
                    $controller_default                                                     = ($controllers[$arrService[0]]["controller"]
                                                                                                ? $controllers[$arrService[0]]["controller"]
                                                                                                : $arrService[1]
                                                                                            );
                    if( $this->controllers[$arrService[0]]["services"] !== false)
                    {
                        $this->controllers[$arrService[0]] = array();

                        if(!is_array($this->controllers[$arrService[0]]["services"]) && $controller_default)
                            $this->controllers[$arrService[0]]["default"]                   = $controller_default;
                        if($arrService[1])
                            $this->controllers[$arrService[0]]["services"][]                = $arrService[1];

                        $this->controllers_rev[$class
                                                . ucfirst($arrService[1]
                                                    ? $arrService[1]
                                                    : $arrService[0]
                                                )]                                          = array(
                                                                                                "type"          => $arrService[0]
                                                                                                , "path"        => $service
                                                                                            );
                    } else {
						$this->controllers[$arrService[0]]                                  = array(
                                                                                                "default"       => $controller_default
                                                                                                , "services"    => null
                                                                                                , "storage"     => null
                                                                                                , "struct"		=> null
                                                                                            );
					}
                }

                if(!$spl_loaded[$script_path]) {
                    $controllers_rev                                                        = $this->controllers_rev;
                    spl_autoload_register(function ($name) use ($controllers_rev) {
                        if ($controllers_rev[$name])
                            require_once($controllers_rev[$name]["path"]);
                    });
                    $spl_loaded[$script_path]                                               = true;
                }
            }
        }
    }
    public function setConnector($name, $service = null)
    {
        $prefix                                                                             = ($service["prefix"] && defined($service["prefix"] . "NAME") && constant($service["prefix"] . "NAME")
                                                                                                ? $service["prefix"]
                                                                                                : false
                                                                                            );
        if(!$prefix) {
            $connector                                                                      = $this->getConnector($name);
            if(!$connector["name"])                                                         $prefix = vgCommon::getPrefix($name);

        }
        if($prefix) {
            $connector["host"]                                                              = (defined($prefix . "HOST")
                                                                                                ? constant($prefix . "HOST")
                                                                                                : "localhost"
                                                                                            );
            $connector["username"]                                                          = (defined($prefix . "USER")
                                                                                                ? constant($prefix . "USER")
                                                                                                : ""
                                                                                            );
            $connector["password"]                                                          = (defined($prefix . "PASSWORD")
                                                                                                ? constant($prefix . "PASSWORD")
                                                                                                : ""
                                                                                            );
            $connector["name"]                                                              = ($service["database"]
                                                                                                ? $service["database"]
                                                                                                : (defined($prefix . "NAME")
                                                                                                    ? constant($prefix . "NAME")
                                                                                                    :  ""
                                                                                                )
                                                                                            );
            $connector["table"]                                                             = ($service["table"]
                                                                                                ? $service["table"]
                                                                                                : ""
                                                                                            );
            $connector["key"]                                                               = ($service["key"]
                                                                                                ? $service["key"]
                                                                                                : ""
                                                                                            );
        }

        return $connector;
    }
    /**
     * @param $connectors
     * @param $services
     * @param null $ext
     */
    public function setConfig(&$connectors, &$services, $ext = null)
    {
        require_once($this->getAbsPathPHP("/config"));
        $class_path                                                                         = self::CONFIG_PATH . "/config." . strtolower(get_called_class() . ($ext ? "." . $ext : ""));
        if (is_file($this->getAbsPathPHP($class_path))) {
            require_once($this->getAbsPathPHP($class_path));
        }

        if(is_array($connectors) && count($connectors)) {
            foreach($connectors AS $name => $connector) {
                if(!$connector["name"]) { //todo: da verificare se serve
                    $connectors[$name]                                                      = $this->setConnector($name, array(
                                                                                                "prefix"            => $connector["prefix"]
                                                                                                , "database"        => ($services[$name]["database"]
                                                                                                                        ? $services[$name]["database"]
                                                                                                                        : $connector["name"]
                                                                                                                    )
                                                                                                , "table"           => ($services[$name]["table"]
                                                                                                                        ? $services[$name]["table"]
                                                                                                                        : $connector["table"]
                                                                                                                    )
                                                                                                , "key"           => ($services[$name]["key"]
                                                                                                                        ? $services[$name]["key"]
                                                                                                                        : $connector["key"]
                                                                                                                    )
                                                                                                ));
                }

                if(!$connectors[$name]["name"]) //todo: da verificare se serve
                    unset($connectors[$name]);
            }
        }
        if(is_array($services) && count($services)) {
            foreach($services AS $type => $data)
            {
                if(!$data && $connectors[$type])
                {
                    $services[$type] = array(
                        "service" 			=> $connectors[$type]["service"]
                        , "connector" 		=> $connectors[$type]
                    );
                }
            }
        }
    }
}