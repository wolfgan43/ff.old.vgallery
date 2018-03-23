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
    const SQL_PREFIX					= "FF_DATABASE_";
	const NOSQL_PREFIX					= "MONGO_DATABASE_";
	const BASE_PATH                     = "/library/gallery/classes";
	const CONFIG_PATH                   = "/themes/site/config";

	const ASSETS_PATH                   = "/themes/site";

	const DOMAIN                        = DOMAIN_INSET;

	protected $services 				= null;
	protected $controllers 				= null;

    private $error                      = null;
    private $debug                      = array();
    static $disk_path                  = null;
    private $theme                      = array(
                                            "cms"           => "gallery"
                                            , "frontend"    => "site"
                                        );

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
	protected static function _getDiskPath() {
		if(!self::$disk_path) {
			self::$disk_path = (defined("FF_DISK_PATH")
				? FF_DISK_PATH
				: str_replace(self::BASE_PATH, "", __DIR__)
			);
		}
		return self::$disk_path;
	}

	public function getDiskPath() {
		return $this::_getDiskPath();
	}
    public function getTheme($name)
    {
        return $this->theme[$name];
    }
    public function getAbsPathPHP($path, $use_class_path = false)
    {
        return $this->getAbsPath($path . "." . $this::PHP_EXT, $use_class_path);
    }

    public function getAbsPath($path, $use_class_path = false)
    {
        return ($use_class_path
            ? __DIR__
            : $this::_getDiskPath()
        ) . $path;
    }    
    public function addService($controller, $service = null) 
    {
        if($this->controllers[$controller])
        {
            $this->services[$controller] = $service;
        }
    }
    public function debug($note = null, $params = null)
    {
        if($note !== null)
            $this->debug[$note] = $params;

        if($this->debug)
            return $this->debug;
    }
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
    public function isError($error = null) 
    {
        if($error !== null)
        {
            $this->error = $error;
            $this->debug["error"][] 			= $error;
        }
        if($this->error)
            return $this->error;
    }
    public function isAssocArray(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
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

	public function setController($name, $default) {
		if(is_array($default)) {
			$this->controllers[$name]["storage"] = $default;
		} else {
			$this->controllers[$name]["default"] = $default;
		}
	}
	public function getController($service, $param = null) {
		return ($param
			? $this->controllers[$service][$param]
			: $this->controllers[$service]
		);
	}
	public function getConnector($connector) {
		$this->connectors[$connector];
	}
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
    public function setParam($name, $value) 
    {
        $this->$name = $value;
    }
    public function getParam($name)
    {
        return $this->$name;
    }

    public function loadControllers($script_path)
    {
        if(is_dir($script_path . "/services"))
        {
            $services = glob($script_path . "/services/*");
            if(is_array($services) && count($services)) {
                foreach($services AS $service) {
                    $arrService = explode("_", basename($service, "." . $this::PHP_EXT), 2);

                    if(isset($this->controllers[$arrService[0]]) && $this->controllers[$arrService[0]]["services"] !== false)
                    {
                        if(!is_array($this->controllers[$arrService[0]]["services"]))
                            $this->controllers[$arrService[0]]["default"] = $arrService[1];

                        $this->controllers[$arrService[0]]["services"][] = $arrService[1];
                        
                        $this->controllers_rev[$arrService[1]] = $arrService[0];
                    } else {
						$this->controllers[$arrService[0]] = array(
																"default"                   => $arrService[1]
																, "services"                => null
																, "storage"                 => null
																, "struct"					=> null
															);
					}
                }
            }            
        }
    }

    public function setConfig(&$connectors, &$services, $ext = null)
    {
        require_once($this->getAbsPathPHP("/config"));
        if ($ext && is_file($this->getAbsPathPHP(self::CONFIG_PATH . "/config." . get_called_class() . "." . $ext))) {
            require_once($this->getAbsPathPHP(self::CONFIG_PATH . "/config." . get_called_class() . "." . $ext));
        }

        foreach($connectors AS $name => $connector) {
            if(!$connector["name"]) {
                $prefix = ($connector["prefix"] && defined($connector["prefix"] . "NAME") && constant($connector["prefix"] . "NAME")
                    ? $connector["prefix"]
                    : vgCommon::getPrefix($name)
                );

                $connectors[$name]["host"] = (defined($prefix . "HOST")
                    ? constant($prefix . "HOST")
                    : "localhost"
                );
                $connectors[$name]["name"] = (defined($prefix . "NAME")
                    ? constant($prefix . "NAME")
                    :  ""
                );
                $connectors[$name]["username"] = (defined($prefix . "USER")
                    ? constant($prefix . "USER")
                    : ""
                );
                $connectors[$name]["password"] = (defined($prefix . "PASSWORD")
                    ? constant($prefix . "PASSWORD")
                    : ""
                );
            }
        }

        foreach($services AS $type => $data)
        {
            if(!$data)
            {
                $services[$type] = array(
                    "service" 			=> $connectors[$type]["service"]
                    , "connector" 		=> $connectors[$type]
                );
            }
        }
    }
}