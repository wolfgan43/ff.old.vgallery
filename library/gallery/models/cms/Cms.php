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

class Cms extends vgCommon
{
    static $singleton                   = null;

    protected $services                 = array(
                                        );
    protected $controllers              = array(
                                        );
    protected $controllers_rev          = null;
    protected $connectors               = array(
                                        );
    protected $struct					= array();
    private $action                     = null;
    private $data                       = array();
    private $set                     	= array();
    private $where                      = null;
	private $sort                      	= null;
	private $limit                     	= null;
    private $table                      = null;
    private $reader						= null;
    private $result                     = null;

    /**
     * @param null $services
     * @param null $params
     * @return Cms|null
     */
    public static function getInstance($services = null, $params = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Cms($services, $params);
        else {
            if($services)
                self::$singleton->setServices($services);

			self::$singleton->setParams($params);
        }
		return self::$singleton;
	}

    /**
     * Cms constructor.
     * @param null $services
     * @param null $params
     */
    public function __construct($services = null, $params = null) {
		$this->loadControllers(__DIR__);

		$this->setServices($services);
		$this->setParams($params);
    }

    /**
     * @param $path
     * @param bool $abs
     * @return string
     */
    public static function getUrl($path, $abs = true)
	{
		$http 										= "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://";
		$host 										= $_SERVER["HTTP_HOST"];
		$query 										= "";

		if($path && substr($path, 0, 1) != "/") {
			$url 									= parse_url((strpos($path, "://") === false
				? $http
				: ""
			) . $path);

			if($url["path"] && strpos($url["host"], ".") !== false) {
				$http 									= $url["scheme"] . ($url["scheme"]
															? "://"
															: ""
														);
				$host 									= $url["host"];

				$path 									= $url["path"];
				$query 									= ($url["query"]
															? "?"
															: ""
														) . $url["query"];
			} else {
				$path 									= "/" . $path;
			}
		}

		$alias = self::schema("alias");

		//strippa il path di base per la cache
		if(is_array($alias) && count($alias)) {
			if($alias[$host]) {
				$resAlias["alias"] = $alias[$host];
				if(strpos($path, $alias[$host] . "/") === 0
					|| $path == $alias[$host]
				) {
					$path = substr($path, strlen($alias[$host]));
				}
			}
			if(strpos($host, "www.") === 0) {
				foreach($alias AS $domain => $rule) {
					if(strpos($path, $rule) === 0) {
						$host = $domain;
						$path = substr($path, strlen($rule));
						break;
					}
				}
			}

		}

		if(!$path)
			$path = "/";

		return ($abs
				? $http . $host
				: ""
			) . $path . $query;
	}

    /**
     * @param null $type
     * @param null $name
     * @param null $default
     * @return array|null
     */
    private static function schema($type = null, $name = null, $default = null) {
		static $schema = null;

		if(!$schema) {
			require(self::_getDiskPath() . "/library/gallery/settings.php");
			if(is_file(self::_getDiskPath() . "/themes/site/settings.php")) {
				require(self::_getDiskPath() . "/themes/site/settings.php");
			}
			if(is_file(self::_getDiskPath("cache") . "/locale.php")) {
				require(self::_getDiskPath("cache") . "/locale.php");

				/** @var include $locale */
				$schema["locale"] = $locale;
			}
		}

		if(is_array($default) && count($default)) {
			if($type && $name && is_array($schema[$type][$name]) && count($schema[$type][$name]) && is_array($default)) {
				return array_replace_recursive($default, $schema[$type][$name]);
			} elseif($type && is_array($schema[$type]) && count($schema[$type]) && is_array($default)) {
				return array_replace_recursive($default, $schema[$type]);
			} else {
				return array_replace_recursive($default, $schema);
			}
		}

		if($type)
			return $schema[$type];
		else
			return $schema;
	}
}
