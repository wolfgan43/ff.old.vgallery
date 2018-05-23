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

class statsPage
{
	const TYPE                                              = "page";

	private $device                                         = null;
	private $stats                                        	= null;
	private $services										= array(
																"nosql" 					=> null
																, "sql"						=> null
																, "fs" 						=> null
															);
	private $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "TRACE_DATABASE_"
																	, "table"               => "trace_pages"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "TRACE_MONGO_DATABASE_"
																	, "table"               => "cache_pages"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                  => "/cache/pages"
																	, "name"                => array("url")
																	, "var"					=> null
																	)
															);
	private $struct											= array(
																"url"						=> "string"
																, "get"						=> "array"
																, "domain"					=> "string"
																, "type"					=> "string"
																, "event"					=> "string"
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
																, "owner"					=> "number"
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

    /**
     * statsPage constructor.
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
		$arrFields = $this->getPageFields($fields);
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
    public function sum_vars($where = null, $rules = null) {
		$res = array();
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$pages = $stats["result"];

			foreach ($pages AS $page) {
				$user_vars = $page["user_vars"];
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
    public function get_vars($where = null, $fields = null) {
		$res = null;
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$pages = $stats["result"];
			$key = 0;
//todo: da creare gli aggregati
			if(!is_array($fields) && strlen($fields))
				$fields = array($fields);

			foreach($pages AS $page) {
				if (is_array($fields) && count($fields)) {
					foreach ($fields AS $field) {
						if (array_key_exists($field, $page["user_vars"])) {
							$res[$key][$field] = $page["user_vars"][$field];
						}
					}
				} else {
					$res[$key] = $page["user_vars"];
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
     * @param null $old
     * @return null
     */
    public function set_vars($set, $where = null, $old = null) {
		$arrWhere 							= $this->normalize_params($where);
		if(is_array($set) && count($set)) {
			$storage 						= $this->getStorage();

			if(!$old) {
				$res = $storage->read($arrWhere
					, array(
						"user_vars"			=> true
					));

				$old 						= $res["result"][0]["user_vars"];
			}

			if(is_array($old))
				$user_vars 					= $this->stats->normalize_fields($set, $old);
		}

		if($user_vars && $where) {
			$res = $storage->update(array(
				"user_vars" 				=> $user_vars
				, "last_update"				=> time()
			), $arrWhere);
		}

		return $res;
	}

    /**
     * @param null $insert
     * @param null $update
     */
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
     * @param null $fields
     * @return array|null
     */
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

    /**
     * @return mixed
     */
    private function getPageStats()
	{
		$cm = cm::getInstance();
		$globals = ffGlobals::getInstance("gallery");

		$created 							= time();
		$res = cache_get_request($_GET);
		$get = $res["request"];

		if($globals->author) {
			$author = $globals->author;
			unset($author["token"]);
			unset($author["user_vars"]);
		}
		if(is_array($cm->oPage->page_js) && count($cm->oPage->page_js)) {
			$page_js 					= $cm->oPage->page_js;
			foreach ($page_js AS $key => $js) {
				if($js["embed"]) {
					$page_js[$key]["embed"] = true;
				}
			}
		}

		if(is_array($cm->oPage->page_css) && count($cm->oPage->page_css)) {
			$page_css 					= array_diff_key($cm->oPage->page_css, $globals->links);
			foreach ($page_css AS $key => $css) {
				if($css["embed"]) {
					$page_css[$key]["embed"] = true;
				}
			}
		}

		$page["insert"] = array(
			"url"						=> $globals->user_path
			, "get"						=> $get
			, "domain"					=> vgCommon::DOMAIN
			, "type"					=> $globals->seo["current"]
			, "event"					=> null
			, "title" 					=> $cm->oPage->title
			, "description" 			=> $cm->oPage->page_meta["description"]["content"]
			, "cover"					=> array_filter($globals->cover)
			, "author" 					=> $author
			, "tags"					=> $globals->tags
			, "owner"					=> $globals->author["id"]
			, "meta"					=> $cm->oPage->page_meta
			, "links"					=> $globals->links
			, "microdata"				=> $globals->microdata
			, "js"						=> array(
					                        "url" => (is_array($cm->oPage->page_defer["js"]) && count($cm->oPage->page_defer["js"])
                                                ? $cm->oPage->page_defer["js"][0]
                                                : ""
                                            )
				                            , "keys" => $page_js
				                        )
			, "css"						=> array(
                                            "url" => (is_array($cm->oPage->page_defer["css"]) && count($cm->oPage->page_defer["css"])
                                                ? $cm->oPage->page_defer["css"][0]
                                                : ""
                                            )
                                            , "keys" => $page_css
                                        )
			, "international"			=> ffTemplate::_get_word_by_code("", null, null, true)
			, "settings"				=> $globals->page
			, "template_layers"			=> $globals->cache["layer_blocks"]
			, "template_sections"		=> $globals->cache["section_blocks"]
			, "template_blocks"			=> (is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"])
                                            ? array_keys($globals->cache["layout_blocks"])
                                            : array()
                                        )
			, "template_ff"				=> $globals->cache["ff_blocks"]
			, "keys_D"					=> (is_array($globals->cache["data_blocks"]["D"]) && count($globals->cache["data_blocks"]["D"])
                                            ? array_keys($globals->cache["data_blocks"]["D"])
                                            : array()
                                        )
			, "keys_G"					=> (is_array($globals->cache["data_blocks"]["G"]) && count($globals->cache["data_blocks"]["G"])
                                            ? array_keys($globals->cache["data_blocks"]["G"])
                                            : array()
                                        )
			, "keys_M"					=> (is_array($globals->cache["data_blocks"]["M"]) && count($globals->cache["data_blocks"]["M"])
                                            ? array_keys($globals->cache["data_blocks"]["M"])
                                            : array()
                                        )
			, "keys_S"					=> (is_array($globals->cache["data_blocks"]["S"]) && count($globals->cache["data_blocks"]["S"])
                                            ? array_keys($globals->cache["data_blocks"]["S"])
                                            : array()
                                        )
			, "keys_T"					=> (is_array($globals->cache["data_blocks"]["T"]) && count($globals->cache["data_blocks"]["T"])
                                            ? array_keys($globals->cache["data_blocks"]["T"])
                                            : array()
                                        )
			, "keys_V"					=> (is_array($globals->cache["data_blocks"]["V"]) && count($globals->cache["data_blocks"]["V"])
                                            ? array_keys($globals->cache["data_blocks"]["V"])
                                            : array()
                                        )
			, "http_status"				=> $globals->http_status
			, "created"					=> $created
			, "last_update"				=> $created
			, "cache_last_update"		=> $created
			, "cache"					=> ($globals->cache["user_path"]
                                            ? str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
                                            : array()
                                        )
			, "user_vars"				=> $globals->user_vars
		);

		$page["update"]["set"] = array(
			"title" 					=> $cm->oPage->title
			, "description" 			=> $cm->oPage->page_meta["description"]["content"]
			, "owner"					=> $globals->author["id"]
			, "keys_D"					=> $page["insert"]["keys_D"]
			, "keys_G"					=> $page["insert"]["keys_G"]
			, "keys_M"					=> $page["insert"]["keys_M"]
			, "keys_S"					=> $page["insert"]["keys_S"]
			, "keys_T"					=> $page["insert"]["keys_T"]
			, "keys_V"					=> $page["insert"]["keys_V"]
			, "http_status"				=> $page["insert"]["http_status"]
			, "last_update"	        	=> $created
			, "cache"					=> ($globals->cache["user_path"]
                                            ? "+" . str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
                                            : array()
                                        )
		);

		$page["update"]["where"] = array(
			"url" 						=> $globals->user_path
			, "domain" 					=> vgCommon::DOMAIN
			, "get" 					=> $get
		);

		return $page;
	}

	/**
	 * Page Stats
	 */
	private function getStorage()
	{
		$storage = Storage::getInstance($this->services, array(
			"struct" => $this->struct
		));

		return $storage;
	}

    /**
     * @param null $params
     * @return array|null
     */
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