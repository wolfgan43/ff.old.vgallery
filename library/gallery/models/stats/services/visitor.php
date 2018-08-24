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

class statsVisitor
{
	const TYPE                                              = "visitor";

	private $device                                         = null;
	private $stats                                        	= null;
	private $services										= array(
																"nosql" 					=> null
																//, "sql"						=> null
																//, "fs" 						=> null
															);
    protected $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "TRACE_DATABASE_"
																	, "table"               => "trace_visitors"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "TRACE_MONGO_DATABASE_"
																	, "table"               => "cache_visitors"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                => "/cache/visitors"
																	, "name"                => array("src")
                                                                )
															);
	private $struct											= array(
                                                                "uid"						=> "number"
                                                                , "id_domain"               => "number"
                                                                , "id_anagraph"             => "number"
                                                                , "acl"                     => "number"
																, "avatar"					=> "string"
																, "username"				=> "string"
																, "smart_url"				=> "string"
																, "email"					=> "string"
																, "tel"						=> "string"

                                                                , "token"					=> "array"
                                                                , "devices"				    => "array"

                                                                , "tags"					=> "array"
                                                                , "tagsByEvents"			=> "array"

                                                                , "search"                  => "array"
                                                                , "trace"                   => "arrayIncremental"

																, "created"					=> "number"
																, "last_update"				=> "number"
																, "last_login"				=> "number"
																, "user_vars"				=> "array"
															);
    private $relationship									= array();
    private $indexes										= array();
    private $tables											= array();
    private $alias											= array();
    /**
     * statsUser constructor.
     * @param $stats
     */
    public function __construct($stats)
	{
		$this->stats = $stats;

        $this->stats->setConfig($this->connectors, $this->services);
        //$this->setConfig();
	}

    /**
     * @return null
     */
    public function getDevice()
	{
		return $this->device;
	}

    /**
     * @param null $where
     * @param null $set
     * @param null $fields
     * @return null
     */
    public function get_stats($where = null, $set = null, $fields = null)
	{
		$arrWhere = $this->normalize_params($where);
		$arrFields = $this->getVisitorFields($fields);
		$storage = $this->getStorage();

		$res = $storage->read($arrWhere, $arrFields);
		if($set && is_array($res["result"]) && count($res["result"]) == 1) {
			$update = $this->set_vars($set, $arrWhere, $res["result"][0]["user_vars"]);
		}

		return $res;
	}

    /**
     * @param null $where
     * @param null $rules
     * @return array
     */
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

    /**
     * @param null $where
     * @param null $fields
     * @return null
     */
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
	/*public function get_vars($where = null, $fields = null) {
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$users = $stats["result"];
			foreach($users AS $user) {
				$key = implode("|", array_intersect_key($user, $where));

				if (is_array($fields) && count($fields)) {
					foreach ($fields AS $field) {
						if (array_key_exists($field, $user["user_vars"])) {
							$res[$key][$field] = $user["user_vars"][$field];
						}
					}
				} elseif (strlen($fields)) {
					$res[$key] = (array_key_exists($fields, $user["user_vars"])
						? $user["user_vars"][$fields]
						: null
					);
				} else {
					$res[$key] = $user["user_vars"];
				}
			}
		}

		return (count($res) > 1
			? $res
			: $res[$key]
		);
	}*/

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

    /**
     * @param null $insert
     * @param null $update
     */
    public function write_stats($page = null, $user = null) {
		if(!$user)                          $user = cache_get_session();

		/*$anagraph = Auth::getAnagraph(); todo: da finire
print_r($anagraph);
die();*/
		if($user["ID"]) {
            $visitor                        = $this->getVisitorStats($user, $page);

            if($visitor) {
                $storage                    = $this->getStorage();

                $res                        = $storage->read($visitor["where"]);
                if(is_array($res)) {
                    $update                 = $storage->update(
                                                $this->stats->normalize_fields($visitor["set"], array_intersect_key($res["result"][0], $visitor["set"]))
                                                , $visitor["where"]
                                            );
                } else {
                    $insert                 = $storage->insert($this->stats->normalize_fields($visitor["insert"]));
                }
            }
        }
        return $res;

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

    /**
     * @param null $fields
     * @return array|null
     */
    private function getVisitorFields($fields = null) {
		if(!is_array($fields))              $fields = array_fill_keys(array_keys($this->struct), true);

		return $fields;
	}

    /**
     * @return mixed
     */
    private function getVisitorStats($user, $page = null)
	{
        $devices                            = array();
        $token                              = array();

        $tags                               = array();
        $tagsByEvents                       = array();
        $trace                              = array();

        $user_vars                          = (is_array($user["user_vars"]) && count($user["user_vars"])
                                                ? $user["user_vars"]
                                                : array()
                                            );
        //codice operativo
		$created 							= time();

        if(is_array($user["token"]) && count($user["token"])) {
		    $token                          = $user["token"];
        }

        $visitor                            = system_trace_get_visitor();
        if($visitor) {
            $devices[$visitor["unique"]]     = "++";
        }

        if($page) {
            if(is_array($page["tags"]["primary"]) && count($page["tags"]["primary"])) {
                foreach($page["tags"]["primary"] AS $tag) {
                    $tags[$tag]                 = "++";
                    $tagsByEvents[date("Y", $created)][$tag]       = "++";
                    $tagsByEvents[date("Y-m", $created)][$tag]     = "++";
                }
            }

            $trace                          = array(
                                                "event"             => "pageview"
                                                , "type"            => $page["type"]
                                                , "url"             => $page["url"]
                                                , "domain"          => $page["domain"]
                                                , "title"           => $page["title"]
                                                , "description"     => $page["description"]
                                                , "cover"           => $page["cover"]
                                                , "owner"           => $page["author"]["id"]
                                                , "tags"            => $page["tags"]
                                                , "created"         => $created
                                            );
        }

		$res["insert"] = array(
			"uid" 					        => $user["ID"]
            , "id_domain"				    => $user["ID_domain"]
            , "id_anagraph"                 => $user["anagraph"]["ID"]
            , "acl"						    => ($user["acl"]
                                                ? $user["acl"]
                                                : $user["primary_gid"]
                                            )
            , "avatar"						=> $user["avatar"]
			, "username" 				    => $user["username"]
			, "smart_url" 					=> $user["username_slug"]
			, "email"						=> $user["email"]
			, "tel"							=> $user["tel"]

            , "token"						=> $token
            , "devices"						=> $devices

            , "tags" 						=> $tags
            , "tagsByEvents"                => $tagsByEvents

            , "search"                      => array()
            , "trace"                       => $trace

			, "created"						=> $created
			, "last_update"					=> $created
			, "last_login"					=> strtotime($user["lastlogin"])
			, "user_vars"					=> $user_vars

		);

        $res["set"] = array(
            "token"						    => $token
            , "devices"						=> $devices
            , "tags" 						=> $tags
            , "tagsByEvents"                => $tagsByEvents
            , "trace"                       => $trace
            , "last_update"					=> $created
            , "last_login"					=> strtotime($user["lastlogin"])
		);

        $res["where"] = array(
			"uid" 					        => $user["ID"]
		);

		return $res;
	}


    /**
     * @return null|Storage
     */
    private function getStorage()
	{
		$storage = Storage::getInstance($this->services, $this->getStruct());

		return $storage;
	}

    /**
     * @param null $params
     * @return array|null
     */
    private function normalize_params($params = null) {
		if(is_array($params)) {
			$where 							= $params;
		} elseif(strlen($params)) {
			$where                          = array(
                                                "uid" => $params
                                            );
		} else {
            $user 				            = cache_get_session();
			$where                          = array(
                                                "uid" => $user["ID"]
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