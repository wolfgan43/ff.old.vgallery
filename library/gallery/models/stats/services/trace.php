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

class statsTrace
{
	const TYPE                                              = "trace";

	private $device                                         = null;
	private $stats                                        	= null;
	private $services										= array(
																"nosql" 					=> null
																, "sql"						=> null
																, "fs" 						=> null
															);
    protected $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "TRACE_DATABASE_"
																	, "table"               => "trace"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "TRACE_MONGO_DATABASE_"
																	, "table"               => "cache"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                => "/cache/trace"
																	, "name"                => array("visitor")
                                                                )
															);
	private $struct											= array(
																"visitor"					=> "number"
																, "url"						=> "number"
                                                                , "get"					    => "array"
																, "domain"					=> "string"
																, "action"					=> array(
																    "name"                  => "string"
                                                                    , "value"               => "string"
                                                                )
																, "referer" 			    => "string"
																, "user_agent" 			    => "array"
                                                                , "device"                  => "string"
                                                                , "browser"                 => "string"
                                                                , "platform"                => "string"
                                                                , "page" 					=> array(
                                                                    "title" 				=> "string"
                                                                    , "description" 		=> "string"
                                                                    , "tags" 				=> "arrayOfNumber"
                                                                    , "author" 				=> "number"
                                                                )
                                                                , "user" 					=> array(
                                                                    "id" 					=> "number"
                                                                    , "username" 		    => "string"
                                                                    , "avatar" 				=> "string"
                                                                    , "email" 				=> "string"
                                                                )
                                                                , "created"                 => "number"
																, "user_vars"				=> "array"
															);
    private $relationship									= array();
    private $indexes										= array();
    private $tables											= array();
    private $alias											= array();

	public function __construct($stats)
	{
		$this->stats = $stats;

		$this->stats->setConfig($this->connectors, $this->services);
		//$this->setConfig();
	}

	public function getDevice()
	{
		return $this->device;
	}

	public function get_stats($where = null, $set = null, $fields = null)
	{
		$arrWhere = $this->normalize_params($where);
		$arrFields = $this->getPageFields($fields);
		$storage = $this->getStorage();

		$res = $storage->read($arrWhere, $arrFields);

		if($set && is_array($res["result"]) && count($res["result"]) == 1) {
			$update = $this->set_vars($set, $arrWhere, $res["result"][0]["user_vars"]);
		}

		return $res;
	}

	public function sum_vars($where = null, $rules = null, $table = "user_vars") {
		$res = array();
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$results = $stats["result"];

			foreach ($results AS $result) {
				$user_vars = $result[$table];
				if (is_array($user_vars) && count($user_vars)) {
					foreach ($user_vars AS $key => $value) {
						foreach ($rules AS $rule) {
                            if ($key == $rule || preg_match("/^" . str_replace(array("\*", "\?"), array("(.+)", "(.?)"), preg_quote($rule)) . "$/i", $key)) {
								$res[$key] += $value;
							}
						}
					}
				}
			}
		}

		return $res;
	}

	public function get_vars($where = null, $fields = null, $table = "user_vars") {
		$res = null;
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$results = $stats["result"];
			$key = 0;
//todo: da creare gli aggregati
			if(!is_array($fields) && strlen($fields))
				$fields = array($fields);

			foreach($results AS $result) {
				if (is_array($fields) && count($fields)) {
					foreach ($fields AS $field) {
						if (array_key_exists($field, $result[$table])) {
							$res[$key][$field] = $result[$table][$field];
						}
					}
				} else {
					$res[$key] = $result[$table];
				}

                if($res[$key])
				    $key++;
			}
		}

		return (count($res) > 1
			? $res
			: $res[0]
		);
	}

    /**
     * @param $set
     * @param null $where
     * @param string $table
     * @return null
     */
    public function set_vars($set, $where = null, $table = "user_vars") {
        $arrWhere 							= $this->normalize_params($where);
        if(is_array($set) && count($set)) {
            $storage 						= $this->getStorage();

            $res                            = $storage->read($arrWhere);
            $old 						    = $res["result"][0];

            if(is_array($old)) {
                $set                        = array($table => $set);
                $user_vars                  = $this->stats->normalize_fields($set, array_intersect_key($old, $set));
            }
        }

        if($user_vars && $where) {
            $user_vars["last_update"]       = time();
            $update                         = $storage->update($user_vars, $arrWhere);
        }

        return $res;
    }

	public function write_stats($insert = null, $update = null) {
		Stats::getInstance("user")->write($insert["author"]);

		$page = $this->getPageStats();

		$this->getStorage()->write(
			(is_array($insert)
				? array_replace_recursive($page["insert"], $insert)
				: $page["insert"]
			)
			, (is_array($update)
				? array_replace_recursive($page["update"], $update)
				: $page["update"]
			)
		);
	}

    /**
     * @param $type
     * @return array
     */
    public function getStruct() {
        return array(
            "struct"                                        => $this->struct
            , "indexes"                                     => $this->indexes
            , "relationship"                                => $this->relationship
            , "table"                                       => $this->tables
            , "alias"                                       => $this->alias
            , "connectors"                                  => false
        );
    }
	private function getPageFields($fields = null) {
		if(!is_array($fields)) {
			$fields = array(
				"title"						=> true
				, "description"				=> true
				, "tags"					=> true
				, "author"					=> true
				, "owner"					=> true
				, "user_vars"				=> true
			);
		}

		return $fields;
	}

	private function getPageStats()
	{


		return ;
	}

	/**
	 * Page Stats
	 */
	private function getStorage()
	{
		$storage = Storage::getInstance($this->services, $this->getStruct());

		return $storage;
	}

	private function normalize_params($params = null) {
		if(is_array($params)) {
			$where 						= $params;
		} elseif(strlen($params)) {
			$request				    = array();
			if(substr($params, 0, 1) == "/") {
				$url["path"] 			= $params;
				$url["host"] 			= vgCommon::DOMAIN;
			} else {
				$url = parse_url($params);
				if ($url["query"])
					parse_str($url["query"], $request);
			}
			$where = array(
				"url" 					=> $url["path"]
                , "domain"				=> $url["host"]
                , "get"					=> $request
			);

		} else {
			$res 						= cache_get_request($_GET);
			$request 					= $res["request"];

			$where = array(
				"url" 					=> $_SERVER["PATH_INFO"]
                , "domain"				=> vgCommon::DOMAIN
                , "get"					=> $request
			);
		}

		return $where;
	}
/*
	private function setConfig()
	{
		foreach($this->connectors AS $name => $connector) {
			if(!$connector["name"]) {
				$prefix = ($connector["prefix"] && defined($connector["prefix"] . "NAME") && constant($connector["prefix"] . "NAME")
					? $connector["prefix"]
					: vgCommon::getPrefix($name)
				);

				if (is_file($this->stats->getAbsPathPHP("/config")))
				{
					require_once($this->stats->getAbsPathPHP("/config"));

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
					"service" 			=> $this->connectors[$type]["service"]
					, "connector" 		=> $this->connectors[$type]
				);
			}
		}


	}*/
}