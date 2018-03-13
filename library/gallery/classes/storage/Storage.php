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

class Storage extends vgCommon
{
    static $singleton                   = null;
    
    protected $services                 = array(
                                            "sql"               => null
                                        );     
    protected $controllers              = array(
                                            "nosql"             => array(
                                                "default"       => null
                                                , "services"    => null
                                                , "connector"   => "db"
                                            )
                                            , "fs"              => array(
                                                "default"       => null
                                                , "services"    => null
                                                , "connector"   => "fs"
                                            )
                                            , "sql"             => array(
                                                "default"       => null
                                                , "services"    => null
                                                , "connector"   => "db"
                                            )
                                        ); 
    protected $controllers_rev          = array();
    protected $connectors               = array(
                                            "nosql"             => array(
                                                "host"          => null
                                                , "username"    => null
                                                , "password"    => null
                                                , "name"        => null
												, "prefix"		=> null
                                                , "table"       => null
                                                , "key"         => null
                                            )
                                            , "fs"              => array(
                                                 "path"         => null
                                                , "name"        => null
                                            )
                                            , "sql"             => array(
                                                "host"          => null
                                                , "username"    => null
                                                , "password"    => null
                                                , "name"        => null
												, "prefix"		=> null
                                                , "table"       => null
                                                , "key"         => null
                                            )
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

    public static function getInstance($services = null, $params = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Storage($services, $params);
        else {
			self::$singleton->setServices($services);
			self::$singleton->setParams($params);
        }
		return self::$singleton;
	} 
    
    public function __construct($services = null, $params = null) {
		$this->loadControllers(__DIR__);

		$this->setServices($services);
		$this->setParams($params);
    }

    public function lookup($table, $where = null, $fields = null, $sort = null, $limit = null)
    {
        $res = array();
        $this->read($where, $fields, $sort, $limit, $table);
        if(is_array($this->result) && count($this->result))
        {
            foreach($this->result AS $service => $data)
            {
                $res = $res + $data;
            }
        }

        return count($res) > 1
            ? $res
            : $res[0];
    }

    public function read($where = null, $fields = null, $sort = null, $limit = null, $table = null)
    {

        $this->clearResult();
        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "read";
            $this->where                = $where;
			$this->sort                	= $sort;
			$this->limit                = $limit;
            $this->table                = $table;
            $this->setData($fields);

            $this->controller();
        }

        return $this->getResult($this->reader);
    }

    public function insert($data = null, $table = null)
	{
		$this->clearResult();
		if($this->isError())
			return $this->isError();
		else {
			$this->action               = "insert";
			$this->table                = $table;
			$this->setData($data);

			$this->controller();
		}

		return $this->getResult();
	}

	public function update($set = null, $where = null, $table = null)
    {
        $this->clearResult();
        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "update";
            $this->table                = $table;
            $this->setData($set, "set");
			$this->setData($where, "where");


			$this->controller();
        }

        return $this->getResult();
    }
    
    public function write($insert = null, $update = null, $table = null)
    {
        $this->clearResult();

        if($this->isError())
            return $this->isError();
        else {
            $this->action               = "write";
            $this->table                = $table;
            $this->setData($insert);
			$this->setData($update["set"], "set");
			$this->setData($update["where"], "where");

            $this->controller();
        }

        return $this->getResult();
    }
    
    public function delete($where = null, $table = null)
    {
        $this->clearResult();

        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "delete";
            $this->where                = $where;
            $this->table                = $table;

            $this->controller();
        }

        return $this->getResult();
    }
    public function getConfig($type, $config = null)
    {
        if(!$config)
            $config = $this->services[$type]["connector"];

        if(is_array($config))
            $config = array_replace($this->connectors[$type], array_filter($config));
        else
            $config = $this->connectors[$type];

        return $config;
    }
    public function getData($params = "data", $data = null)
    {
        if(is_array($data))
            $data = array_replace($this->$params, array_filter($data));
        else
            $data = $this->$params;

        return $data;
    }
    public function setData($data, $param = "data") 
    {
        if($data) {
            if(!is_array($data))
                $data = array($data);

            //$data = array_fill_keys($data, true);
        }        
        $this->$param = $data;
    }
    public function convertData($source, $dest)
    {
        $this->convertParam($source, $dest, "data");
    }
    public function convertWhere($source, $dest) 
    {
        $this->convertParam($source, $dest, "where");
    }
	private function getColorPalette($index = null) {
    	$colors = array(
			"EF5350"
			, "EC407A"
			, "AB47BC"
			, "7E57C2"
			, "5C6BC0"
			, "42A5F5"
			, "29B6F6"
			, "26C6DA"
			, "26A69A"
			, "66BB6A"
			, "9CCC65"
			, "D4E157"
			, "FFEE58"
			, "FFCA28"
			, "FFA726"
		);
    	return ($index === null
			? $colors
			: $colors[$index]
		);
	}
    private function convertParam($source, $dest, $param) 
    {
        if($this->$param[$source]) {
            $this->$param[$dest] = $this->$param[$source];
            unset($this->$param[$source]);
        }
    }
    private function getQuery($driver)
	{
		$config                                                 = $driver->getConfig();

		$query["key"] 											= $config["key"];
		$query["from"]                                          = ($this->table
			? $this->table
			: $config["table"]
		);

		if($this->action == "read") {
			$query 												= $query + $driver->convertFields($this->data, "select");
		}
		if($this->action == "insert" || $this->action == "write") {
			if (!is_array($this->data)) {
				$this->isError("data is empty");
				return array();
			}
			$query 												= $query + $driver->convertFields($this->data, "insert");
		}
		if($this->action == "update" || $this->action == "write") {
			$query 												= $query + $driver->convertFields($this->set, "update");
		}
		if($this->action == "read" || $this->action == "update" || $this->action == "write") {
			$query 												= $query + $driver->convertFields($this->where, "where");
		}
		if($this->action == "read" && $this->sort) {
			$query 												= $query + $driver->convertFields($this->sort, "sort");
		}
		if($this->action == "read" && $this->limit) {
			$query["limit"] 									= $this->limit;
		}

		if(!$config["name"])
			$this->isError($driver::TYPE . "_database_connection_failed");

		if(!$query["key"])
			$this->isError($driver::TYPE . "_key_missing");

		if(!$query["from"])
			$this->isError($driver::TYPE . "_table_missing");

		return $query;
	}
    private function controller()
    {
        foreach($this->services AS $controller => $services)
        {
            $this->isError("");

            $funcController = "controller_" . $controller;
            $service = $this->$funcController(is_array($services)
				? $services["service"]
				: $services
			);

            if($this->action == "read" && is_array($this->result[$service])) {
            	$this->reader = $service;
				break;
			}
        }
    }
    private function controller_sql($service = null)
    {
        $type                                                           = "sql";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "storage" . ucfirst($service);
            require_once($this->getAbsPathPHP("/storage/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            //$config                                                     = $driver->getConfig();

            if(!$db)
                $this->isError($type. "_no_DB");

            if($this->isError()) {
                $this->result[$service] = $this->isError();
            } else {
            	$query = $this->getQuery($driver);

                switch($this->action)
                {
                    case "read":
						$this->result[$service] = array();
                        if($query["select"])
							$query["select"] .= ", `" . $query["key"] . "`";
						else
                            $query["select"] = "*";

                        if(!$query["where"])
                            $query["where"] = " 1 ";

                        $sSQL = "SELECT " . $query["select"] . " 
                                FROM " .  $query["from"] . "
                                WHERE " . $query["where"]
								. ($query["sort"]
									? " ORDER BY " . $query["sort"]
									: ""
								);

                        $res = $db->query($sSQL);
                        if(!$res) {  //todo: da ristrutturare per gli up down
                        	switch ($db->errno) {
								case "1146":
									$driver->up();
									$res = $db->query($sSQL);
									break;
								default:
							}
						}
                        if($res) {
							if($db->nextRecord())
							{
								do {
									$this->result[$service]["keys"][] = $db->record[$query["key"]];
									//unset($db->record[$query["key"]]);
									$this->result[$service]["result"][] = $this->fields2output($db->record, $this->data);
								} while($db->nextRecord());
							}
						} else {
                        	$this->isError("N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
						}
                        break;
					case "insert":
						if($query["insert"])
						{
							$sSQL = "INSERT INTO " .  $query["from"] . "
                                (
                                    " . $query["insert"]["head"] . "
                                ) VALUES (
                                    " . $query["insert"]["body"] . "
                                )";
							$db->execute($sSQL);
							$this->result[$service] = array(
								"keys" => $db->getInsertID(true)
							);
						}
						break;
                    case "update":
						if($query["update"] && $query["where"])
						{
							$sSQL = "SELECT " . $query["key"] . " 
									FROM " .  $query["from"] . "
									WHERE " . $query["where"];
							$db->query($sSQL);
							$res = $this->extractKeys($db->getRecordset(), $query["key"]);

							if(is_array($res))
							{
							   $sSQL = "UPDATE " .  $query["from"] . " SET 
											" . $query["update"] . "
										WHERE " . $query["key"] . " IN(" . $db->toSql(implode("," , $res), "Text", false) . ")";
							   $db->execute($sSQL);

							   $this->result[$service] = array(
								   "keys" => $res
							   );
							} else {
								$this->result[$service] = false;
							}
						}
                        break;
                    case "write":
						if($query["update"] && $query["where"])
						{
							$sSQL = "SELECT " . $query["key"] . " 
									FROM " .  $query["from"] . "
									WHERE " . $query["where"];
							$db->query($sSQL);
							$res = $this->extractKeys($db->getRecordset(), $query["key"]);
						}

						if(is_array($res))
						{
                           	$sSQL = "UPDATE " .  $query["from"] . " SET 
                                        " . $query["update"] . "
                                    WHERE " . $query["key"] . " IN(" . $db->toSql(implode("," , $res), "Text", false) . ")";
                           	$db->execute($sSQL);
                           	$this->result[$service] = array(
								"keys" => $res
								, "action" => "update"
							);
                        }
                        elseif($query["insert"])
                        {
                            $sSQL = "INSERT INTO " .  $query["from"] . "
                                (
                                    " . $query["insert"]["head"] . "
                                ) VALUES (
                                    " . $query["insert"]["body"] . "
                                )";
                            $db->execute($sSQL);
                            $this->result[$service] = array(
								"keys" => $db->getInsertID(true)
								, "action" => "insert"
                            );
                        }
                        break;
                    case "delete":
                        if($query["where"])
                        {
                            $sSQL = "DELETE FROM " .  $query["from"] . "  
                                    WHERE " . $query["where"];
                            $db->execute($sSQL);

                            $this->result[$service] = true;
                        }
                        break;
                    default:
                }
            }
        }

        return $service;
    }
    private function controller_nosql($service = null)
    {
        $type                                                           = "nosql";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "storage" . ucfirst($service);
            require_once($this->getAbsPathPHP("/storage/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
           // $config                                                     = $driver->getConfig();

            if(!$db)
                $this->isError($type . "_no_DB");

            if($this->isError()) {
                $this->result[$service] = $this->isError();
            } else {
                //da normalizzare i campi in ingresso esempio:
                //gestire calcoli hit = hit +1
                //gestire doppi in un set field array(22,22,22) users
				$query = $this->getQuery($driver);
				switch($this->action)
                {
                    case "read":
                    	$select = $query["select"];
						$select[$query["key"]] = true;

                        $db->query(array(
                            "select" 	=> $select
                            , "from" 	=> $query["from"]
                            , "where" 	=> $query["where"]
							, "sort" 	=> $query["sort"]
							, "limit"	=> $query["limit"]
                        ));

                        if($db->nextRecord())
                        {
                            do {
								$this->result[$service]["keys"][] = $db->record[$query["key"]];
                                $this->result[$service]["result"][] = $this->fields2output($db->record, $this->data);
                            } while($db->nextRecord());
                        }
                        break;
					case "insert":
						if($query["insert"])
						{
							$db->insert($query["insert"], $query["from"]);
							$this->result[$service] = array(
								"keys" 				=> $db->getInsertID(true)
							);
						}
						break;
                    case "update":
						if($query["update"] && $query["where"])
						{
							$db->update(array(
								"set" 				=> $query["update"]
								, "where" 			=> $query["where"]
							), $query["from"]);

							$this->result[$service] = array(
								"keys" 				=> $db->getInsertID(true)
							);
						}
                        break;
                    case "write":
						if($query["update"] && $query["where"])
						{
							$db->query(array(
								"select"			=> array($query["key"] => 1)
								, "from" 			=> $query["from"]
								, "where" 			=> $query["where"]
							));
							$res = $this->extractKeys($db->getRecordset(), $query["key"]);
						}

						if(is_array($res)) {
							$update 				= $driver->convertFields(array($query["key"] => $res), "where");
							$update["set"] 			= $query["update"];
							$update["from"] 		= $query["from"];

							$db->update($update, $update["from"]);

							$this->result[$service] = array(
								"keys" 				=> $res
								, "action" 			=> "update"
							);
						}
                        elseif($query["insert"])
                        {
							$db->insert($query["insert"], $query["from"]);
							$this->result[$service] = array(
								"keys" 				=> $db->getInsertID(true)
								, "action" 			=> "insert"
							);
                        }
                        break;
                    case "delete":
                        if($query["where"])
                        {
                            $db->delete($query["where"], $query["from"]);
                            $this->result[$service] = true;
                        }
                        break;
                    default:
                }
            }
        }

		return $service;
    }
    private function controller_fs($service = null)
    {
        $type                                                           = "fs";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
			$config = $this->getConfig($type);

			if(is_array($config["name"])) {
				$filename = implode("/", array_intersect_key($this->where, array_flip($config["name"])));
			} elseif($config["name"]) {
				$filename = $this->where[$config["name"]];
			}
			if($filename) {
				$file = $this->getDiskPath() . $config["path"] . $filename;

				$fs = new Filemanager($service, $file);

				switch ($this->action) {
					case "read":
						$this->result[$service] = $fs->read();
						break;
					case "insert":
						if($this->data) {
							$this->result[$service] = $fs->write($this->data);
						}
						break;
					case "update":
						if($this->set) {
							$this->result[$service] = $fs->update($this->set);
						}
						break;
					case "write": ///todo: DA FINITRE
						if($this->set && is_file($fs->exist($service))) {
							$this->result[$service] = $fs->update($this->set);
						} elseif($this->data) {
							$this->result[$service] = $fs->write($this->data);
						}
						break;
					case "delete":
						if($this->where) {
							$this->result[$service] = $fs->delete($this->where);
						}
						break;
					default:
				}
			}
        }

		return $service;

    }
    private function clearResult() 
    {
        $this->data                                                     = array();
        $this->action                                                   = null;
        $this->where                                                    = null;
		$this->sort                                                    	= null;
        $this->table                                                    = null;
		$this->reader 													= null;
        $this->result                                                   = null;

        $this->isError("");
    }

    private function getResult($service = null)
    {
        return ($this->isError()
            ? $this->isError()
            : ($service
            	? $this->result[$service]
				: $this->result
			)
        );
    }
	private function fields2output($record, $prototype = null) {

    	if($prototype) {
			$res = array_fill_keys(array_keys(array_filter($prototype)), "");
    		if(is_array($prototype)) {
    			foreach ($prototype AS $name => $value) {
					$arrValue = null;
    				$arrType = explode(":", $value, 2);
    				$field = $arrType[0];
    				$toField = $arrType[1];

    				if(strpos($field, ".") > 0) {
    					$arrValue = explode(".", $field);
    					if(is_array($record[$arrValue[0]])) {
							$res[$name] = $record[$arrValue[0]][$arrValue[1]];
						} elseif($record[$arrValue[0]]) {
    						$subvalue = json_decode($record[$arrValue[0]]);
    						if($subvalue)
								$res[$name] = $subvalue[$arrValue[1]];
						}
					} else {
						$res[$name] = $record[$name];
					}

					if(!$toField) {
						$struct = ($arrValue && is_array($this->struct[$arrValue[0]])
							? ($this->struct[$arrValue[0]][$arrValue[1]]
								? $this->struct[$arrValue[0]][$arrValue[1]]
								: $this->struct[$arrValue[0]]["default"]
							)
							: (is_array($this->struct[$name])
								? $this->struct[$name]["default"]
								: $this->struct[$name]
							)
						);

						$arrStruct = explode(":", $struct, 2);
						$toField = $arrStruct[1];
					}

					if($toField) {
						$res[$name] = $this->to($res[$name], $toField, $name);
					}
				}
			} else {
				$res[$prototype] = $record[$prototype];
			}
		} else {
    		$res = $record;
		}

		return $res;
	}
	private function to($source, $conversion, $name) {
    	switch($conversion) {
			case "toImage":
				if($source === true) {
					$res = '<i></i>';
				} elseif(strpos($source, "/") === 0) {
					if(is_file(DISK_UPDIR . $source)) {
						$res = '<img src="' . CM_SHOWFILES . $source . '" />';
					} elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images" . $source)) {
						$res = '<img src="' . CM_SHOWFILES . "/" . FRONTEND_THEME . "/images" . $source . '" />';
					}
				} elseif(strpos($source, "<") === 0) {
					$res = $source;
				} elseif(strpos($source, "#") !== false) {
					$arrSource = explode("#", $source);
					$hex = ($arrSource[1]
						? $arrSource[1]
						: $this->getColorPalette(rand(0,14))
					);
					$res = '<span style="background-color: #' . $hex . ';">' . $arrSource[0] . '</span>';
				} elseif($source && function_exists("cm_getClassByFrameworkCss")) {
					$res = cm_getClassByFrameworkCss($source, "icon-tag");
				}
				break;
			case "toTimeElapsed":
				$time = time() - $source; // to get the time since that moment
				$time = ($time < 1) ? 1 : $time;
				$day = 86400;
				$min = 60;
				if($time < 2 * $day) {
					if($time < $min ) {
						$res = ffTemplate::_get_word_by_code("about") . " " . fftemplate::_get_word_by_code("a") . " " . fftemplate::_get_word_by_code("minute") . " " . ffTemplate::_get_word_by_code("ago");
					} else if($time > $day ) {
						$res = ffTemplate::_get_word_by_code("yesterday") . " " . fftemplate::_get_word_by_code("at") . " " . date("G:i", $source);
					} else {
						$tokens = array(
							31536000 	=> 'year',
							2592000 	=> 'month',
							604800 		=> 'week',
							86400 		=> 'day',
							3600 		=> 'hour',
							60 			=> 'minute',
							1 			=> 'second'
						);

						foreach ($tokens as $unit => $text) {
							if ($time < $unit) continue;
							$res = floor($time / $unit);
							$res .= ' ' . fftemplate::_get_word_by_code($text . (($res > 1) ? 's' : '')) . " " . ffTemplate::_get_word_by_code("ago");
							break;
						}
					}
					break;
				}


			case "toDateTime":
				$lang = FF_LOCALE;
				$ffRes = new ffData($source, "Timestamp");
				$res = $ffRes->getValue("Date", $lang);

				if($lang == "ENG") {
					$prefix = "+";
					$res = "+" . $res;
				} else {
					$prefix = "/";
				}

				$conv = array(
					$prefix . "01/" => " " . ffTemplate::_get_word_by_code("January") . " "
					, $prefix . "02/" => " " . ffTemplate::_get_word_by_code("February") . " "
					, $prefix . "03/" => " " . ffTemplate::_get_word_by_code("March") . " "
					, $prefix . "04/" => " " . ffTemplate::_get_word_by_code("April") . " "
					, $prefix . "05/" => " " . ffTemplate::_get_word_by_code("May") . " "
					, $prefix . "06/" => " " . ffTemplate::_get_word_by_code("June") . " "
					, $prefix . "07/" => " " . ffTemplate::_get_word_by_code("July") . " "
					, $prefix . "08/" => " " . ffTemplate::_get_word_by_code("August") . " "
					, $prefix . "09/" => " " . ffTemplate::_get_word_by_code("September") . " "
					, $prefix . "10/" => " " . ffTemplate::_get_word_by_code("October") . " "
					, $prefix . "11/" => " " . ffTemplate::_get_word_by_code("November") . " "
					, $prefix . "12/" => " " . ffTemplate::_get_word_by_code("December") . " "
				);
				$res = str_replace(array_keys($conv), array_values($conv), $res);
				if($prefix)
					$res = str_replace("/", ", ", $res);
				$res .= " " . fftemplate::_get_word_by_code("at") . " " . fftemplate::_get_word_by_code("hours") . " " . $ffRes->getValue("Time", FF_LOCALE);

				break;
			case "toDate":
				$ffRes = new ffData($source, "Timestamp");
				$res = $ffRes->getValue("Date", FF_LOCALE);
				break;
			case "toTime":
				$ffRes = new ffData($source, "Timestamp");
				$res = $ffRes->getValue("Time", FF_LOCALE);
				break;
			case "toString":
				if($source) {
					if(is_string($source)) {
						$res = $source;
					} else {
						$res = $name;
					}
				} else {
					$res = "";
				}
				break;
			default:
				$res = $source;
		}

		return $res;
	}
	public function isAssocArray(array $arr)
	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	public function normalizeField($name, $value) {
    	static $fields = array();

    	if(1 || !$fields[$name]) {
			$not = false;
			if (strpos($name, "!") === 0) {
				$name = substr($name, 1);
				$not = true;
			}
			if (strpos($name, ">") == strlen($name) - 1) {
				$name = substr($name, 0, -1);
				$op = ">";
			}
			if (strpos($name, ">=") == strlen($name) - 2) {
				$name = substr($name, 0, - 2);
				$op = ">=";
			}

			if (strpos($name, "<") == strlen($name) - 1) {
				$name = substr($name, 0, -1);
				$op = "<";
			}

			if (strpos($name, "<=") == strlen($name) - 2) {
				$name = substr($name, 0, -2);
				$op = "<=";
			}

			/*if(!is_array($value)) {
				$arrValue = explode(":", $value, 2);
				$value = $arrValue[0];
			}*/
			if(is_array($this->struct[$name])) {
				$struct_type = "array";
			} else {
				$arrStructType = explode(":", $this->struct[$name], 2);
				$struct_type = $arrStructType[0];
			}

			switch($struct_type) {
				case "arrayOfNumber":																			//array
				case "array":																			//array
					if(strrpos($value, "++") === strlen($value) -2) {								//++ to array
						//skip
					} elseif(strrpos($value, "--") === strlen($value) -2) {							//-- to array
						//skip
					} elseif(strpos($value, "+") === 0) {
						$res["update"]['$addToSet'][$name] = substr($value, 1);
					} elseif(is_array($value)) {
						if ($struct_type == "arrayOfNumber")                                            //array number to array
							$fields[$name] = array_map('intval', $value);
						else
							$fields[$name] = $value;                                                            //array to array
					} elseif(is_bool($value)) {                                                                //boolean to array
						$fields[$name] = array((int)$value);
					} elseif(is_numeric($value) || $struct_type == "arrayOfNumber") {
						if (strpos($value, ".") !== false || strpos($value, ",") !== false)    //double to array
							$fields[$name] = array((double)$value);
						else                                                                                //int to array
							$fields[$name] = array((int)$value);
					} elseif(strtotime($value)) {															//date to array
						$fields[$name] = array($value);
					} elseif($value == "empty") {                                                            //empty to array
						$fields[$name] = array();
					} else {                                                                                //other to array
						$fields[$name] = array((string)$value);
					}

					break;
				case "boolean":																			//boolean
					if(strrpos($value, "++") === strlen($value) -2) {                                //++ to boolean
						//skip
					} elseif(strrpos($value, "--") === strlen($value) -2) {                            //-- to boolean
						//skip
					} elseif(strpos($value, "+") === 0) {												//+ to boolean
						//skip
					} elseif(is_array($value)) {															//array to boolean
						//skip
					} elseif(is_bool($value)) {                                                            //boolean to boolean
						$fields[$name] = $value;
					} elseif(is_numeric($value)) {															//number to boolean
						$fields[$name] = (bool)$value;
					} elseif($value == "empty") {                                                            //empty seq to boolean
						$fields[$name] = false;
					} else {                                                                                    //other to boolean
						$fields[$name] = (bool)$value;
					}
					break;
				case "date":																			//date
					$fields[$name] = $value;
					break;
				case "number":																			//number
					if(strrpos($value, "++") === strlen($value) -2) {                                //++ to number
						$res["update"]['$inc'][$name] = 1;
					} elseif(strrpos($value, "--") === strlen($value) -2) {                                //-- to number
						$res["update"]['$inc'][$name] = -1;
					} elseif(strpos($value, "+") === 0) {                                            //+ to number
						$res["update"]['$concat'][$name] = array('$' . $name, ",", substr($value, 1));
					} elseif(is_array($value)) {																//array to number
						//skip
					} elseif(is_bool($value)) {                                                                //boolean to number
						$fields[$name] = (int)$value;
					} elseif(!is_numeric($value) && strtotime($value)) {                                     //date to number
						$fields[$name] = strtotime($value);
					} elseif (strpos($value, ".") !== false || strpos($value, ",") !== false) {    //double to number
						$fields[$name] = (double)$value;
					} elseif($value == "empty") {                                                                //empty to number
						$fields[$name] = 0;
					} else {
						$fields[$name] = (int)$value;                                                        //other to number
					}
					break;
				case "string":																			//string
				default:
					if(strrpos($value, "++") === strlen($value) -2) {                                //++ to string
						$res["update"]['$concat'][$name] = array('$' . $name, "+1");
					} elseif(strrpos($value, "--") === strlen($value) -2){                                //-- to string
						$res["update"]['$concat'][$name] = array('$' . $name, "-1");
					} elseif(strpos($value, "+") === 0){                                                //+ to string
						$res["update"]['$concat'][$name] = array('$' . $name, ",", substr($value, 1));
					} elseif(is_array($value)) {
						if($this->isAssocArray($value))														//array assoc to string
							$fields[$name] = json_encode($value);
						else																				//array seq to string
							$fields[$name] = implode(",", array_unique($value));
					} elseif(is_bool($value)) {                                                                //boolean to string
						$fields[$name] = (string)($value ? "1" : "0");
					} elseif(is_numeric($value)) {															//number to string
						$fields[$name] = (string)$value;
					} elseif($value == "empty") {                                                            //empty seq to string
						$fields[$name] = "";
					} else {                                                                                //other to string
						$fields[$name] = (string)$value;
					}
			}
		}

    	return array(
    		"value" => $fields[$name]
			, "name" => $name
			, "not" => $not
			, "op" => $op
			, "res" => $res
		);
	}


    private function extractKeys($recordset, $key) {
    	if(is_array($recordset) && count($recordset)) {
    		foreach($recordset AS $record) {
    			if($record[$key])
    				$res[] = $record[$key];
			}
		}

		return $res;
	}
}
