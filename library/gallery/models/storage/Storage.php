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
    protected $controllers_rev          = null;
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
    protected $struct					= null;
    protected $relationship			    = null;
    protected $indexes					= null;
    protected $table                    = null;
    protected $alias                    = null;

    protected $exts                     = false;

    private $action                     = null;
    private $data                       = array();
    private $set                     	= array();
    private $where                      = null;
	private $sort                      	= null;
	private $limit                     	= null;
    private $reader						= null;
    private $result                     = null;

    /**
     * I services sono le tipologie di database che si vogliono utilizzare simultaneamente.
     * Le tipologie di database implementate sono:
     * nosql (mongoDB)
     * sql (MySql)
     * fs (storing su filesytem tramite file: php sotto forma di array, xml, json, csv, log)
     *
     * @example Storage::getInstance("nosql");
     *
     * @example Storage::getInstance(array("nosql" => "myController(mongodb, cassandra, ecc)"));
     *
     * @example Storage::getInstance(array(
     *                                  "nosql" => "myTable"
     *                                  , "sql" => array(
     *                                      "host"          => "myDB Host"
     *                                      , "username"    => "myDB User"
     *                                      , "password"    => "myDB Password"
     *                                      , "database"    => "myDB Name"
     *                                      , "table"       => "myDB Table"
     *                                      , "key"         => "myDB Key"
     *                                      , "controller"  => "mongodb"
     *                                  )
     *                              ));
     *
     * @example Storage::getInstance(array(
     *                                  "nosql" => "myTable"
     *                                  , "sql" => array(
     *                                      "prefix"		=> "myDB Prefix"
     *                                      , "table"       => "myDB Table"
     *                                      , "key"         => "myDB Key"
     *                                      , "controller"  => "mysql"
     *                                  )
     *                              ));
     *
     *
     *
     * @param null $services
     * @param null $params
     * @return null|Storage
     */
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

    /**
     * Storage constructor.
     * @param null $services
     * @param null $params
     */
    public function __construct($services = null, $params = null) {
		$this->loadControllers(__DIR__, $services);

		$this->setServices($services);
		$this->setParams($params);
    }

    /**
     * @param $table
     * @param null $where
     * @param null $fields
     * @param null $sort
     * @param null $limit
     * @return array|mixed
     */
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
    public function find($fields = null, $where = null, $sort = null, $limit = null, $table = null)
    {
        if(!$where && !$sort && !$limit) {
            $where                                                                          = $fields;
            $fields                                                                         = null;
        }

        return $this->read($where, $fields, $sort, $limit, $table);
    }
    /**
     * @param null $where
     * @param null $fields
     * @param null $sort
     * @param null $limit
     * @param null $table
     * @return null
     */
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
            $this->setTable($table);
            $this->setData($fields);

            $this->controller();
        }

        return $this->getResult($this->reader);
    }

    /**
     * @param null $data
     * @param null $table
     * @return null
     */
    public function insert($data = null, $table = null)
	{
		$this->clearResult();
		if($this->isError())
			return $this->isError();
		else {
			$this->action               = "insert";
			$this->setTable($table);
			$this->setData($data);

			$this->controller();
		}

		return $this->getResult();
	}

    /**
     * @param null $set
     * @param null $where
     * @param null $table
     * @return null
     */
    public function update($set = null, $where = null, $table = null)
    {
        $this->clearResult();
        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "update";
            $this->setTable($table);
            $this->setData($set, "set");
			$this->setData($where, "where");


			$this->controller();
        }

        return $this->getResult();
    }

    /**
     * @param null $insert
     * @param null $update
     * @param null $table
     * @return null
     */
    public function write($insert = null, $update = null, $table = null)
    {
        $this->clearResult();

        if($this->isError())
            return $this->isError();
        else {
            $this->action               = "write";
            $this->setTable($table);
            $this->setData($insert);
			$this->setData($update["set"], "set");
			$this->setData($update["where"], "where");

            $this->controller();
        }

        return $this->getResult();
    }

    /**
     * @param null $where
     * @param null $table
     * @return null
     */
    public function delete($where = null, $table = null)
    {
        $this->clearResult();

        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "delete";
            $this->where                = $where;
            $this->setTable($table);

            $this->controller();
        }

        return $this->getResult();
    }

    /**
     * @param $type
     * @param null $config
     * @return array|mixed|null
     */
    public function getConfig($type, $config = null)
    {
        if(!$config) {
            if ($this->services[$type]["connector"]) {
                $config = $this->services[$type]["connector"];
            } else {
                $config = $this->services[$type];
                unset($config["service"]);
            }
        }

        if($this->connectors[$type]) {
            if (is_array($config))
                $config = array_replace($this->connectors[$type], array_filter($config));
            else
                $config = $this->connectors[$type];
        }
        return $config;
    }
    public function setConnector($name, $service = null)
    {
        if(!$service)                  $service = $this->services[$name];

        return parent::setConnector($name, $service);
    }

    /**
     * @param string $params
     * @param null $data
     * @return array|null
     */
    public function getData($params = "data", $data = null)
    {
        if(is_array($data))
            $data = array_replace($this->$params, array_filter($data));
        else
            $data = $this->$params;

        return $data;
    }

    /**
     * @param $data
     * @param string $param
     */
    public function setData($data, $param = "data")
    {
        if($data) {
            if(!is_array($data))
                $data = array($data);

            //$data = array_fill_keys($data, true);
        }        
        $this->$param = $data;
    }

    /**
     * @param $table
     * @param null $params
     */
    public function setTable($table, $params = null) {
        if($params)
            $this->table = array_replace($this->table, $params);

        if(is_array($table)) {
            $this->setParams($table);
        } elseif($table !== null) {
            $this->table["name"] = $table;
        }
    }

    /**
     * @param null $default
     * @return null
     */
    public function getTable($default = null) {
        return ($this->table["name"]
            ? $this->table["name"]
            : $default
        );
    }

    /**
     * @param $source
     * @param $dest
     */
    public function convertData($source, $dest)
    {
        $this->convertParam($source, $dest, "data");
    }

    /**
     * @param $source
     * @param $dest
     */
    public function convertWhere($source, $dest)
    {
        $this->convertParam($source, $dest, "where");
    }
    public function getFieldAlias($field) {
        if(is_array($this->alias) && count($this->alias)) {
            $alias_rev = array_flip($this->alias);
            return($alias_rev[$field]
                ? $alias_rev[$field]
                : $field
            );
        } else {
            return $field;
        }
    }
    /**
     * @param null $index
     * @return array|mixed
     */
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

    /**
     * @param $source
     * @param $dest
     * @param $param
     */
    private function convertParam($source, $dest, $param)
    {
        if($this->$param[$source]) {
            $this->$param[$dest] = $this->$param[$source];
            unset($this->$param[$source]);
        }
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getQuery($driver)
	{
		$config                                                         = $driver->getConfig();

		$query["key"] 											        = $config["key"];
		$query["from"]                                                  = $this->getTable($config["table"]);
        if($query["from"]) {
            if($this->action == "read") {
                $query 												    = $query + $driver->convertFields($this->data, "select");
            }
            if($this->action == "insert" || $this->action == "write") {
                if (!is_array($this->data)) {
                    $this->isError("data is empty");
                    return array();
                }
                $query 												    = $query + $driver->convertFields($this->data, "insert");
            }
            if($this->action == "update" || $this->action == "write") {
                $query 												    = $query + $driver->convertFields($this->set, "update");
            }
            if($this->action == "read" || $this->action == "update" || $this->action == "write") {
                $query 												    = $query + $driver->convertFields($this->where, "where");
            }
            if($this->action == "read" && $this->sort) {
                $query 												    = $query + $driver->convertFields($this->sort, "sort");
            }
            if($this->action == "read" && $this->limit) {
                $query["limit"] 									    = $this->limit;
            }

            if(!$config["name"]) {
                $this->isError($driver::TYPE . "_database_connection_failed");
            }
            if(!$query["key"])
                $this->isError($driver::TYPE . "_key_missing");

            if(!$query["from"])
                $this->isError($driver::TYPE . "_table_missing");

        } else {
            $query = "Table not Set";
        }

        return $query;
    }

    /**
     *
     */
    private function controller()
    {
        foreach($this->services AS $controller => $services)
        {
            //$this->isError("");

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

    /**
     * @param null $service
     * @return null
     *
     * @todo da togliere e metterlo nel services
     */
    private function controller_sql($service = null)
    {
        $type                                                           = "sql";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "storage" . ucfirst($service);
            if($this->controllers_rev[$controller]) {
                // require_once($this->getAbsPathPHP("/storage/services/" . $type . "_" . $service, true));

                $driver                                                     = new $controller($this);
                $db                                                         = $driver->getDevice();
                //$config                                                     = $driver->getConfig();

                if($db) {
                    $query = $this->getQuery($driver);

                    if(is_array($query)) {
                        switch($this->action)
                        {
                            case "read":
                                $exts = array();
                                $this->result[$service] = array();
                                $sSQL = "SELECT " . $query["select"] . "  
                                        FROM " .  $query["from"] . "
                                        WHERE " . $query["where"]
                                        . ($query["sort"]
                                            ? " ORDER BY " . $query["sort"]
                                            : ""
                                        )
                                        . ($query["limit"]
                                            ? " LIMIT " . (is_array($query["limit"])
                                                ? $query["limit"]["skip"] . ", " . $query["limit"]["limit"]
                                                : $query["limit"]
                                            )
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
                                    if($this->exts /*&& $query["select"] != "*"*/) {
                                        if (is_array($db->fields_names) && count($db->fields_names)) {
                                            foreach ($db->fields_names AS $name) {
                                                if($name == $query["key"])
                                                    $exts[$name] = null;
                                                elseif (strpos($name, "ID_") === 0)
                                                    $exts[$name] = null;
                                                elseif ($this->relationship[$name]) {
                                                    $exts[$name] = null;
                                                } elseif ($this->relationship[$this->alias[$name]]) {
                                                    $exts[$name] = $this->alias[$name];
                                                }
                                            }
                                        }
                                        //if($exts)
                                            //$this->result[$service]["exts"] = array();
                                    }

                                    if($db->nextRecord())
                                    {
                                        $key = $this->getFieldAlias($query["key"]);
                                        do {
                                            $this->result[$service]["keys"][] = $db->record[$key];
                                            if($exts) {
                                                foreach($exts AS $field_name => $field_alias) {
                                                    if($db->record[$field_name]) {
                                                        /*if(strpos(",") === false) {
                                                            $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)][$db->record[$field_name]] = $db->record[$field_name];
                                                        } else {*/
                                                            $ids = explode(",", $db->record[$field_name]);
                                                            foreach ($ids AS $id) {
                                                                $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)][$id][] = $db->record[$key];
                                                            }
                                                            //print_r($ids);
                                                            //$this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)] = (array) $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)] + array_fill_keys($ids, $db->record[$query["key"]]);
                                                        //}
                                                    }
                                                }
                                            }

                                            $this->result[$service]["result"][] = $this->fields2output($db->record, $this->data);
                                        } while($db->nextRecord());
                                    }

                                } else {
                                    $this->isError("Read - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
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
                                    $res = $db->execute($sSQL);
                                    if($res) {
                                        $this->result[$service] = array(
                                            "keys" => $db->getInsertID(true)
                                        );
                                    } else {
                                        $this->isError("Insert - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                    }
                                }
                                break;
                            case "update":
                                if($query["update"] && $query["where"])
                                {
                                    $sSQL = "SELECT " . $query["key"] . " 
                                            FROM " .  $query["from"] . "
                                            WHERE " . $query["where"];
                                    $res = $db->query($sSQL);
                                    if($res) {
                                        $res = $this->extractKeys($db->getRecordset(), $query["key"]);

                                        if (is_array($res)) {
                                            $sSQL = "UPDATE " . $query["from"] . " SET 
                                                        " . $query["update"] . "
                                                    WHERE " . $query["key"] . " IN(" . $db->toSql(implode(",", $res), "Text", false) . ")";
                                            $db->execute($sSQL);

                                            $this->result[$service] = array(
                                                "keys" => $res
                                            );
                                        } else {
                                            $this->result[$service] = false;
                                        }
                                    } else {
                                        $this->isError("Update - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                    }
                                }
                                break;
                            case "write":
                                $keys                                       = null;
                                if($query["update"] && $query["where"])
                                {
                                    $sSQL = "SELECT " . $query["key"] . " 
                                            FROM " .  $query["from"] . "
                                            WHERE " . $query["where"];
                                    $res = $db->query($sSQL);
                                    if($res) {
                                        $keys                               = $this->extractKeys($db->getRecordset(), $query["key"]);
                                    } else {
                                        $this->isError("Read - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                    }
                                }

                                if(!$this->isError()) {
                                    if(is_array($keys))
                                    {
                                        $sSQL = "UPDATE " .  $query["from"] . " SET 
                                                    " . $query["update"] . "
                                                WHERE " . $query["key"] . " IN(" . $db->toSql(implode("," , $keys), "Text", false) . ")";
                                        $res = $db->execute($sSQL);
                                        if($res) {
                                            $this->result[$service] = array(
                                                "keys"                      => $keys
                                                , "action"                  => "update"
                                            );
                                        } else {
                                            $this->isError("Update - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                        }
                                    }
                                    elseif($query["insert"])
                                    {
                                        $sSQL = "INSERT INTO " .  $query["from"] . "
                                            (
                                                " . $query["insert"]["head"] . "
                                            ) VALUES (
                                                " . $query["insert"]["body"] . "
                                            )";
                                        $res = $db->execute($sSQL);
                                        if($res) {
                                            $this->result[$service] = array(
                                                "keys"                      => $db->getInsertID(true)
                                                , "action"                  => "insert"
                                            );
                                        } else {
                                            $this->isError("Insert - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                        }
                                    }
                                }
                                break;
                            case "delete":
                                if($query["where"])
                                {
                                    $sSQL = "DELETE FROM " .  $query["from"] . "  
                                            WHERE " . $query["where"];
                                    $res = $db->execute($sSQL);
                                    if($res) {
                                        $this->result[$service] = true;
                                    } else {
                                        $this->isError("Delete - N°: " . $db->errno . " Msg: " . $db->error . " SQL: " . $sSQL);
                                    }
                                }
                                break;
                            default:
                        }
                    } else {
                        $this->isError("Query: " . $query);
                    }
                } else {
                    $this->isError($type . "_no_DB");
                }
            } else {
                $this->isError("Controller not Found");
            }
        } else {
            $this->isError("Controller Empty");
        }

        return $service;
    }

    /**
     * @param null $service
     * @return null
     *
     * @todo da togliere e metterlo nel services
     */
    private function controller_nosql($service = null)
    {
        $type                                                           = "nosql";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "storage" . ucfirst($service);
            if($this->controllers_rev[$controller]) {
                //require_once($this->getAbsPathPHP("/storage/services/" . $type . "_" . $service, true));

                $driver                                                     = new $controller($this);
                $db                                                         = $driver->getDevice();
               // $config                                                     = $driver->getConfig();

                if($db) {
                    //da normalizzare i campi in ingresso esempio:
                    //gestire calcoli hit = hit +1
                    //gestire doppi in un set field array(22,22,22) users
                    $query = $this->getQuery($driver);
                    if(is_array($query)) {
                        switch($this->action)
                        {
                            case "read":
                                $exts = null;
                                $res = $db->query(array(
                                    "select" 	=> $query["select"]
                                    , "from" 	=> $query["from"]
                                    , "where" 	=> $query["where"]
                                    , "sort" 	=> $query["sort"]
                                    , "limit"	=> $query["limit"]
                                ));
                                if($res) {
                                    if($this->exts && count($query["select"]) > 0) {
                                        if (is_array($db->fields_names) && count($db->fields_names)) {
                                            foreach ($db->fields_names AS $name) {
                                                if($name == $query["key"])
                                                    $exts[$name] = null;
                                                elseif (strpos($name, "ID_") === 0)
                                                    $exts[$name] = null;
                                                elseif ($this->relationship[$name]) {
                                                    $exts[$name] = null;
                                                } elseif ($this->relationship[$this->alias[$name]]) {
                                                    $exts[$name] = $this->alias[$name];
                                                }
                                            }
                                        }
                                        //if($exts)
                                        //    $this->result[$service]["exts"] = array();
                                    }
                                    if($db->nextRecord())
                                    {
                                        do {
                                            $this->result[$service]["keys"][] = $db->record[$query["key"]];
                                            if($exts) {
                                                foreach($exts AS $field_name => $field_alias) {
                                                    if($db->record[$field_name]) {
                                                        /*if(strpos(",") === false) {
                                                            $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)][$db->record[$field_name]] = $db->record[$field_name];
                                                        } else {*/
                                                        $ids = explode(",", $db->record[$field_name]);
                                                        foreach ($ids AS $id) {
                                                            $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)][$id][] = $db->record[$query["key"]];
                                                        }
                                                        //$this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)] = (array) $this->result[$service]["exts"][($field_alias ? $field_alias : $field_name)] + array_fill_keys($ids, $db->record[$query["key"]]);
                                                        //}
                                                    }
                                                }
                                            }
                                            $this->result[$service]["result"][] = $this->fields2output($db->record, $this->data);
                                        } while($db->nextRecord());
                                    }
                                } else {
                                    $this->isError("unable to read: " . print_r($query, true));
                                }
                                break;
                            case "insert":
                                if($query["insert"])
                                {
                                    $res                            = $db->insert($query["insert"], $query["from"]);
                                    if($res) {
                                        $this->result[$service] = array(
                                            "keys"                  => $db->getInsertID(true)
                                        );
                                    } else {
                                        $this->isError("unable to insert: " . print_r($query, true));
                                    }
                                }
                                break;
                            case "update":
                                if($query["update"] && $query["where"])
                                {
                                    $res = $db->update(array(
                                        "set" 				        => $query["update"]
                                        , "where" 			        => $query["where"]
                                    ), $query["from"]);
                                    if($res) {
                                        $this->result[$service] = array(
                                            "keys"                  => $db->getInsertID(true)
                                        );
                                    } else {
                                        $this->isError("unable to update: " . print_r($query, true));
                                    }
                                }
                                break;
                            case "write":
                                $keys                               = null;
                                if($query["update"] && $query["where"])
                                {
                                    $res = $db->query(array(
                                        "select"			        => array($query["key"] => 1)
                                        , "from" 			        => $query["from"]
                                        , "where" 			        => $query["where"]
                                    ));
                                    if($res) {
                                        $keys                       = $this->extractKeys($db->getRecordset(), $query["key"]);
                                    } else {
                                        $this->isError("unable to read: " . print_r($query, true));
                                    }
                                }
                                if(!$this->isError()) {
                                    if(is_array($keys)) {
                                        $update 				    = $driver->convertFields(array($query["key"] => $res), "where");
                                        $update["set"] 			    = $query["update"];
                                        $update["from"] 		    = $query["from"];

                                        $res                        = $db->update($update, $update["from"]);
                                        if($res) {
                                            $this->result[$service] = array(
                                                "keys" 				=> $keys
                                                , "action" 			=> "update"
                                            );
                                        } else {
                                            $this->isError("unable to update: " . print_r($query, true));
                                        }
                                    }
                                    elseif($query["insert"])
                                    {
                                        $res                        = $db->insert($query["insert"], $query["from"]);
                                        if($res) {
                                            $this->result[$service] = array(
                                                "keys" 				=> $db->getInsertID(true)
                                                , "action" 			=> "insert"
                                            );
                                        } else {
                                            $this->isError("unable to insert: " . print_r($query, true));
                                        }
                                    }
                                }
                                break;
                            case "delete":
                                if($query["where"])
                                {
                                    $res                            = $db->delete($query["where"], $query["from"]);
                                    if($res) {
                                        $this->result[$service]     = true;
                                    } else {
                                        $this->isError("unable to delete: " . print_r($query, true));
                                    }
                                }
                                break;
                            default:
                        }
                    } else {
                        $this->isError("Query: " . $query);
                    }
                } else {
                    $this->isError($type . "_no_DB");
                }
            } else {
                $this->isError("Controller not Found");
            }
        } else {
            $this->isError("Controller Empty");
        }

		return $service;
    }

    /**
     * @param null $service
     * @return null
     *
     * @todo da togliere e metterlo nel services
     */
    private function controller_fs($service = null)
    {

        $type                                                           = "fs";
        $config                                                         = $this->getConfig($type);

        if(!$service)
            $service                                                    = $config["service"];

        if($service)
        {
            if($config["name"]) {
                if(!is_array($config["name"]))
                    $config["name"] = array($config["name"]);

                $arrFilename                                            = ($this->action == "insert" || $this->action == "write"
                                                                            ? array_intersect_key($this->data  , array_flip($config["name"]))
                                                                            : array_intersect_key($this->where , array_flip($config["name"]))
                                                                        );
                $filename =(is_array($arrFilename) && count($arrFilename)
                    ? implode("-", $arrFilename)
                    : false
                );

            }

			if($filename) {
				$file                                                   = $this->getDiskPath() . $config["path"] . $filename;

				$fs                                                     = new Filemanager($service, $file);

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
			} elseif($filename !== false) {
			    $this->isError("File not Found: " . $filename);
            }
        } else {
            $this->isError("Controller Empty");
        }

		return $service;

    }

    /**
     *
     */
    private function clearResult()
    {
        $this->data                                                     = array();
        $this->action                                                   = null;
        $this->where                                                    = null;
		$this->sort                                                    	= null;

		$this->reader 													= null;
        $this->result                                                   = null;

        $this->isError("");
    }

    /**
     * @param null $service
     * @return null
     */
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

    /**
     * @param $record
     * @param null $prototype
     * @return array
     */
    private function fields2output($record, $prototype = null) {
    	if($prototype) {
			$res                                                        = array_fill_keys(array_keys(array_filter($prototype)), "");

    		if(is_array($prototype)) {
    			foreach ($prototype AS $name => $value) {
					$arrValue                                           = null;
					$field                                              = null;
                    $toField                                            = null;

                    if($name == "*") {
                        $res[$this->table["alias"]]                     = $record;
                        unset($res["*"]);
                        continue;
                    }

                    if(!is_bool($value)) {
                        $arrType                                        = $this->convert($value);
                        $field                                          = $arrType["field"];
                        $toField                                        = $arrType["to"];

/*                        $arrType                                        = explode(":", $value, 2);
                        $field                                          = $arrType[0];
                        $toField                                        = $arrType[1];
*/
                        $key                                            = $field;
                        unset($res[$name]);
                    } elseif($this->alias[$name]) {
    				    $key                                            = $this->alias[$name];
    				    unset($res[$name]);
                    } else {
                        $key                                            = $name;
                    }
//echo $key . "\n";
    				if(strpos($field, ".") > 0) {
    					$arrValue                                       = explode(".", $field);
    					if(is_array($record[$arrValue[0]])) {
							$res[$key]                                  = $record[$arrValue[0]][$arrValue[1]];
						} elseif($record[$arrValue[0]]) {
    						$subvalue                                   = json_decode($record[$arrValue[0]]);
    						if($subvalue)
								$res[$key]                              = $subvalue[$arrValue[1]];
						}
                    } elseif(is_array($record[$name])) {
                        $res[$key]                                      = $record[$name];
                    } else {
						$res[$key]                                      = $this->decode($record[$name]);
					}

					if(!$toField) {
						$struct                                         = ($arrValue && is_array($this->struct[$arrValue[0]])
                                                                            ? ($this->struct[$arrValue[0]][$arrValue[1]]
                                                                                ? $this->struct[$arrValue[0]][$arrValue[1]]
                                                                                : $this->struct[$arrValue[0]]["default"]
                                                                            )
                                                                            : (is_array($this->struct[$name])
                                                                                ? $this->struct[$name]["default"]
                                                                                : $this->struct[$name]
                                                                            )
                                                                        );
                        $toField                                        = $this->convert($struct, "to");
						/*$arrStruct                                      = explode(":", $struct, 2);
						$toField                                        = $arrStruct[1];
						*/
					}

					if($toField) {
                        $toField["name"]                                = $name;
						$res[$key]                                      = $this->to($res[$key], $toField);
					}
				}
			} else {
				$res[$prototype]                                        = $record[$prototype];
			}
		} else {
    		$res                                                        = $record;
		}

		return $res;
	}

	private function convert($def, $key = null) {
        $arrStruct                                                      = explode(":", $def);
        $res["field"]                                                   = $arrStruct[0];
        unset($arrStruct[0]);
        if(count($arrStruct)) {
            foreach ($arrStruct AS $value) {
                $func                                                   = substr($value, 2);
                $op                                                     = substr($value, 0, 2);

                if(strpos("", "(") !== false) {
                    $arrFunc = explode("(", $func);
                    $func                                               = array(
                                                                            "name"      => strtoupper($arrFunc[0])
                                                                            , "params"  => explode(",", rtrim($arrFunc[1], ")"))
                                                                        );
                } else {
                    $func                                               = array(
                                                                            "name"      => strtoupper($func)
                                                                            , "params"  => array()
                                                                        );
                }
                $res[$op]                                               = $func;
            }
        }
        return ($key
            ? $res[$key]
            : $res
        );
    }

    /**
     * @param $string
     * @return array|mixed|object
     */
    private function decode($string) {
        if(substr($string, 0, 1) == "{") {
            $json                                                       = json_decode($string, true);
            if(json_last_error() == JSON_ERROR_NONE) {
                $string                                                 = $json;
            }
        }

        return $string;
    }

    /**
     * @param $source
     * @param $conversion
     * @param $name
     * @return array|string
     */
    private function to($source, $convert) {
        $method                                                             = $convert["name"];
        $params                                                             = $convert["params"];
    	switch($method) {
			case "IMAGE":
				if($source === true) {
					$res                                                    = '<i></i>';
				} elseif(strpos($source, "/") === 0) {
					if(is_file(DISK_UPDIR . $source)) {
						$res                                                = '<img src="' . CM_SHOWFILES . $source . '" />';
					} elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images" . $source)) {
						$res                                                = '<img src="' . CM_SHOWFILES . "/" . FRONTEND_THEME . "/images" . $source . '" />';
					}
				} elseif(strpos($source, "<") === 0) {
					$res                                                    = $source;
				} elseif(strpos($source, "#") !== false) {
					$arrSource                                              = explode("#", $source);
					$hex                                                    = ($arrSource[1]
                                                                                ? $arrSource[1]
                                                                                : $this->getColorPalette(rand(0,14))
                                                                            );
					$res                                                    = '<span style="background-color: #' . $hex . ';">' . $arrSource[0] . '</span>';
				} elseif($source && function_exists("cm_getClassByFrameworkCss")) {
					$res                                                    = cm_getClassByFrameworkCss($source, "icon-tag");
				}
				break;
			case "TIMEELAPSED":
				$time                                                       = time() - $source; // to get the time since that moment
				$time                                                       = ($time < 1) ? 1 : $time;
				$day                                                        = 86400;
				$min                                                        = 60;
				if($time < 2 * $day) {
					if($time < $min ) {
						$res                                                = ffTemplate::_get_word_by_code("about") . " " . fftemplate::_get_word_by_code("a") . " " . fftemplate::_get_word_by_code("minute") . " " . ffTemplate::_get_word_by_code("ago");
					} else if($time > $day ) {
						$res                                                = ffTemplate::_get_word_by_code("yesterday") . " " . fftemplate::_get_word_by_code("at") . " " . date("G:i", $source);
					} else {
						$tokens                                             = array(
                                                                                31536000 	=> 'year',
                                                                                2592000 	=> 'month',
                                                                                604800 		=> 'week',
                                                                                86400 		=> 'day',
                                                                                3600 		=> 'hour',
                                                                                60 			=> 'minute',
                                                                                1 			=> 'second'
                                                                            );

						foreach ($tokens as $unit => $text) {
							if ($time < $unit)                              continue;
							$res                                            = floor($time / $unit);
							$res                                            .= ' ' . fftemplate::_get_word_by_code($text . (($res > 1) ? 's' : '')) . " " . ffTemplate::_get_word_by_code("ago");
							break;
						}
					}
					break;
				}


			case "DATETIME":
				$lang                                                       = FF_LOCALE;
				$ffRes                                                      = new ffData($source, "Timestamp");
				$res                                                        = $ffRes->getValue("Date", $lang);

				if($lang == "ENG") {
					$prefix                                                 = "+";
					$res                                                    = "+" . $res;
				} else {
					$prefix                                                 = "/";
				}

				$conv                                                       = array(
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
				$res                                                        = str_replace(array_keys($conv), array_values($conv), $res);
				if($prefix)
					$res                                                    = str_replace("/", ", ", $res);
				$res                                                        .= " " . fftemplate::_get_word_by_code("at") . " " . fftemplate::_get_word_by_code("hours") . " " . $ffRes->getValue("Time", FF_LOCALE);

				break;
			case "DATE":
				$ffRes                                                      = new ffData($source, "Timestamp");
				$res                                                        = $ffRes->getValue("Date", FF_LOCALE);
				break;
			case "TIME":
				$ffRes                                                      = new ffData($source, "Timestamp");
				$res                                                        = $ffRes->getValue("Time", FF_LOCALE);
				break;
			case "STRING":
				if($source) {
					if(is_string($source)) {
						$res                                                = $source;
					} else {
						$res                                                = $params["name"];
					}
				} else {
					$res                                                    = "";
				}
				break;
            case "DESCRYPT":
                $res                                                        = $this->decrypt($source, $params[0], $params[1]);
                break;
            case "AES128":
            case "AES192":
            case "AES256":
            case "BF":
            case "CAST":
            case "IDEA":
                $res                                                        = $this->decrypt($source, $params[0], $method);
                break;
			default:
				$res                                                        = $source;
		}

		return $res;
	}
    /**
     * @param $source
     * @param $conversion
     * @param $name
     * @return array|string
     */
    private function convertWith($data, $method, $params = null) {
        switch($method) {
            case "ASCII":
                $res                                                        = ord($data);
                break;
            case "CHAR_LENGTH":
            case "CHARACTER_LENGTH":
            case "LENGTH":
                $res                                                        = strlen($data);
                break;
            case "LCASE":
            case "LOWER":
                $res                                                        = strtolower($data);
                break;
            case "LTRIM":
                $res                                                        = ltrim($data, $params[0]);
                break;
            case "RTRIM":
                $res                                                        = rtrim($data, $params[0]);
                break;
            case "TRIM":
                $res                                                        = trim($data, $params[0]);
                break;
            case "UCASE":
            case "UPPER":
                $res                                                        = strtoupper($data);
                break;
            case "REVERSE":
                $res                                                        = strrev($data);
                break;
            case "MD5":
                $res                                                        = md5($data);
                break;
            case "OLDPASSWORD":
            case "PASSWORD":
                $res                                                        = "*" . strtoupper(sha1(sha1($data, TRUE)));
                break;
            case "REPLACE";
                $res                                                        = str_replace($params[0], $params[1], $data);
            case "CONCAT";
                $res                                                        = $data . " " . implode(" ", $params);
                break;
            case "ENCRYPT":
                $res                                                        = $this->encrypt($data, $params[0], $params[1]);
                break;
            case "AES128":
            case "AES192":
            case "AES256":
            case "BF":
            case "CAST":
            case "IDEA":
                $res                                                        = $this->encrypt($data, $params[0], $method);
                break;
            default:
                $res                                                        = $data;
        }

        return $res;
    }

    private function in($source, $convert) {
        $res                                                                = $source;
        $method                                                             = $convert["name"];
        $params                                                             = $convert["params"];

        if(is_array($source)) {
            if(count($source)) {
                foreach ($source AS $i => $v) {
                    $res[$i]                                                = $this->convertWith($v, $method, $params);
                }
            }
        } elseif($source) {
            $res                                                            = $this->convertWith($source, $method, $params);
        }

        return $res;
    }

    private function getEncryptParams($password, $algorithm, $cost = 12) {
        if($password && $algorithm) {
            switch($algorithm) {
                case "AES128":
                    $method                                                 = "aes-128-cbc";
                    break;
                case "AES192":
                    $method                                                 = "aes-192-cbc";
                    break;
                case "AES256":
                    $method                                                 = "aes-256-cbc";
                    break;
                case "BF":
                    $method                                                 = "bf-cbc";
                    break;
                case "CAST":
                    $method                                                 = "cast5-cbc";
                    break;
                case "IDEA":
                    $method                                                 = "idea-cbc";
                    break;
                default:
            }

            // IV must be exact 16 chars (128 bit)
            if($method) {
                return array(
                    "key"       => password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost])
                    , "method"  => $method
                    , "iv"      => chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0)
                );
            }
        }
    }
    private function encrypt($data, $password, $algorithm = "AES256", $cost = 12) {
        $params                                                                 = $this->getEncryptParams($password, $algorithm, $cost);
        if($params)  // av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
            return base64_encode(openssl_encrypt($data, $params["method"], $params["key"], OPENSSL_RAW_DATA, $params["iv"]));
    }

    private function decrypt($encrypted, $password, $algorithm = "AES256", $cost = 12) {
        $params                                                                 = $this->getEncryptParams($password, $algorithm, $cost);
        if($params) // My secret message 1234
            return openssl_decrypt(base64_decode($encrypted), $params["method"], $params["key"], OPENSSL_RAW_DATA, $params["key"]);
    }
    /**
     * @param $name
     * @param $value
     * @return array
     */
    public function normalizeField($name, $value) {
    	static $fields = array();

    	if(1 || !$fields[$name]) {
			$not = false;
			if (strpos($name, "!") === 0) {
				$name = substr($name, 1);
				$not = true;
			}
			if (strpos($name, ">") === strlen($name) - 1) {
				$name = substr($name, 0, -1);
				$op = ">";
			}
			if (strpos($name, ">=") === strlen($name) - 2) {
				$name = substr($name, 0, - 2);
				$op = ">=";
			}

			if (strpos($name, "<") === strlen($name) - 1) {
				$name = substr($name, 0, -1);
				$op = "<";
			}

			if (strpos($name, "<=") === strlen($name) - 2) {
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
			    $arrType = $this->convert($this->struct[$name]);
                $struct_type = $arrType["field"];
                $toField = $arrType["in"];
				/*
			    $arrStructType = explode(":", $this->struct[$name], 2);
				$struct_type = $arrStructType[0];
				*/
			}

			switch($struct_type) {
                case "arrayIncremental":																//array
			    case "arrayOfNumber":	    															//array
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
					} elseif(is_numeric($value) || $struct_type == "arrayOfNumber" || $struct_type == "arrayIncremental") {
						if (strpos($value, ".") !== false || strpos($value, ",") !== false)    //double to array
							$fields[$name] = array((double)$value);
						else                                                                                //int to array
							$fields[$name] = array((int)$value);
					} elseif(strtotime($value)) {															//date to array
						$fields[$name] = array($value);
					} elseif($value == "empty" || !$value) {                                                  //empty to array
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
                case "number":                                                                          //number
				case "primary":
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
                case "char":
                case "text":
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
                    } elseif(substr($name, 0, 1) == "_") {                                                            //empty seq to string
                        $fields[$name] = $value;
					} else {                                                                                //other to string
                        $fields[$name] = (string)$value;
					}
			}
		}

    	return array(
    		"value"     => $this->in($fields[$name], $toField)
			, "name"    => $name
			, "not"     => $not
			, "op"      => $op
			, "res"     => $res
		);
	}


    /**
     * @param $recordset
     * @param $key
     * @return array
     */
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
