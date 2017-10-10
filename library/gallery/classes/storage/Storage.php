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
if(!defined("FF_PHP_EXT"))
    define("FF_PHP_EXT", "php");

require_once(__DIR__ . "/../vgCommon." . FF_PHP_EXT);

class Storage extends vgCommon
{
    static $singleton                   = null;
    
    private $disk_path                  = null;
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
                                                , "table"       => null
                                                , "key"         => null
                                            )
                                        ); 
    private $action                     = null;
    private $data                       = array();
    private $update                     = array();
    private $where                      = null;
    private $table                      = null;
    private $result                     = null;

    public static function getInstance($services = null, $connectors = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Storage($services, $connectors);
        else {
            if($services)
                self::$singleton->setParam("services", $services);

            if($connectors)
                self::$singleton->setParam("connectors", $connectors);            
        }
		return self::$singleton;
	} 
    
    public function __construct($services = null, $connectors = null) {
        if($services)
            $this->setParam("services", $services);

        if($connectors)
            $this->setParam("connectors", $connectors);

        $this->loadControllers(__DIR__, $services);
    }

    public function lookup($where = null, $table = null, $fields = null)
    {
        $res = array();
        $this->read($where, $table, $fields);
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

    public function read($where = null, $table = null, $fields = null)
    {
        $this->clearResult();
        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "read";
            $this->where                = $where;
            $this->table                = $table;
            $this->setData($fields);

            $this->controller();
        }

        return $this->getResult();
    }
    
    public function update($update = null, $where = null, $table = null, $data = null)
    {
        $this->clearResult();
        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "update";
            $this->where                = $where;
            $this->table                = $table;
            $this->setData($data);
            $this->setUpdate($update);

            $this->controller();
        }

        return $this->getResult();
    }
    
    public function write($data = null, $where = null, $table = null)
    {
        $this->clearResult();

        if($this->isError()) 
            return $this->isError();
        else {
            $this->action               = "write";
            $this->where                = $where;
            $this->table                = $table;
            $this->setData($data);

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
    public function setUpdate($data) 
    {
        $this->setData($data, "update");
    }
    public function convertData($source, $dest) 
    {
        $this->convertParam($source, $dest, "data");
    }
    public function convertWhere($source, $dest) 
    {
        $this->convertParam($source, $dest, "where");
    }

    private function convertParam($source, $dest, $param) 
    {
        if($this->$param[$source]) {
            $this->$param[$dest] = $this->$param[$source];
            unset($this->$param[$source]);
        }
    }
    
    private function controller()
    {
        foreach($this->services AS $controller => $services)
        {
            $this->isError("");

            $funcController = "controller_" . $controller;
            $this->$funcController((is_array($services)
                ? $services["service"]
                : $services
            ));

            if($this->action == "read" && $this->result)
                break;
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
            require_once($this->getAbsPath("/storage/services/" . $type . "_" . $service . "." . FF_PHP_EXT, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            $config                                                     = $driver->getConfig();

            $query["from"]                                              = ($this->table
                                                                            ? $this->table
                                                                            : $config["table"]
                                                                        );
            if(!$config["name"])
                $this->isError($type . "_database_connection_failed");

            if(! $query["from"])
                $this->isError($type . "_table_missing");

            if(!$db)
                $this->isError($type. "_no_DB");

            if($this->isError()) {
                $this->result[$service] = $this->isError();
            } else {
                $query["select"]                                        = $this->fields2string($this->data, $db);
                $query["where"]                                         = $this->fields2string($this->where, $db, " AND ");

                switch($this->action)
                {
                    case "read":
                        if(! $query["select"]["head"])
                             $query["select"]["head"] = "*";

                        if(!$query["where"]["complete"])
                            $query["where"]["complete"] = " 1 ";

                        $sSQL = "SELECT " . $query["select"]["head"] . " 
                                FROM " .  $query["from"] . "
                                WHERE " . $query["where"]["complete"];
                        $db->query($sSQL);
                        if($db->nextRecord())
                        {
                            do {
                                $this->result[$service][] = $db->record;
                            } while($db->nextRecord());
                        }
                        break;
                    case "update":
                        $sSQL = "SELECT " . $config["key"] . " 
                                FROM " .  $query["from"] . "
                                WHERE " . $query["where"]["complete"];
                        $db->query($sSQL);
                        if($db->nextRecord())
                            $res = $db->record;

                        if($res)
                        {
                           $sSQL = "UPDATE " .  $query["from"] . " SET 
                                        " . $query["select"]["complete"] . "
                                    WHERE " . $config["key"] . " = " . $db->toSql($res[$config["key"]]);
                           $db->execute($sSQL);

                           $this->result[$service] = true;
                        } elseif($query["select"]) {
                            $sSQL = "INSERT INTO " .  $query["from"] . "
                                (
                                    " . $query["select"]["head"] . "
                                ) VALUES (
                                    " . $query["select"]["body"] . "
                                )";
                            $db->execute($sSQL);
                            $this->result[$service] = array(
                                $config["key"] => $db->getInsertID(true)
                            );
                        }

                        break;
                    case "write":
                        if($query["where"])
                        {
                           $sSQL = "UPDATE " .  $query["from"] . " SET 
                                        " . $query["select"]["complete"] . "
                                    WHERE " . $query["where"]["complete"];
                           $db->execute($sSQL);

                           $this->result[$service] = true;
                        }
                        else
                        {
                            $sSQL = "INSERT INTO " .  $query["from"] . "
                                (
                                    " . $query["select"]["head"] . "
                                ) VALUES (
                                    " . $query["select"]["body"] . "
                                )";
                            $db->execute($sSQL);
                            $this->result[$service] = array(
                                $config["key"] => $db->getInsertID(true)
                            );
                        }
                        break;
                    case "delete":
                        if($query["where"])
                        {
                            $sSQL = "DELETE FROM " .  $query["from"] . "  
                                    WHERE " . $query["where"]["complete"];
                            $db->execute($sSQL);

                            $this->result[$service] = true;
                        }
                        break;
                    default:
                }
            }
        }
    }
    private function controller_nosql($service = null)
    {
        $type                                                           = "nosql";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "storage" . ucfirst($service);
            require_once($this->getAbsPath("/storage/services/" . $type . "_" . $service . "." . FF_PHP_EXT, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            $config                                                     = $driver->getConfig();

            if(!$config["name"])
                $this->isError($type . "_database_connection_failed");

            if(!$config["table"])
                $this->isError($type . "_table_missing");

            if(!$db)
                $this->isError($type . "_no_DB");

            if($this->isError()) {
                $this->result[$service] = $this->isError();
            } else {
                //da normalizzare i campi in ingresso esempio:
                //gestire calcoli hit = hit +1
                //gestire doppi in un set field array(22,22,22) users

                switch($this->action)
                {
                    case "read":
                        $db->query(array(
                            "select" => $this->data
                            , "from" => $config["table"]
                            , "where" => $this->where
                        ));
                        if($db->nextRecord())
                        {
                            do {
                                $this->result[$service][] = $db->record;
                            } while($db->nextRecord());
                        }
                        break;
                    case "update":
                        $db->query(array(
                            "select" => array($config["key"] => 1)
                            , "from" => $config["table"]
                            , "where" => $this->where
                        ));
                        if($db->nextRecord())
                            $res = $db->record;

                        if($res)
                        {
                            $db->update(array(
                                "set" => $this->data
                                , "where" => array($config["key"] => $res[$config["key"]])
                            ), $config["table"]);

                           $this->result[$service] = true;
                        } elseif($this->data) {
                            $db->insert($this->data, $config["table"]);
                            $this->result[$service] = array(
                                $config["key"] => $db->getInsertID(true)
                            );
                        }
                        break;
                    case "write":
                        if($this->where)
                        {
                            $db->update(array(
                                "set" => $this->data
                                , "where" => $this->where
                            ), $config["table"]);
                           $this->result[$service] = true;
                        }
                        else
                        {
                            $db->insert($this->data, $config["table"]);
                            $this->result[$service] = true;
                        }
                        break;
                    case "delete":
                        if($this->where)
                        {
                            $db->delete($this->where, $config["table"]);
                            $this->result[$service] = true;
                        }
                        break;
                    default:
                }
            }
        }
    }
    private function controller_fs($service = null)
    {
        $type                                                           = "fs";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller = "storage" . ucfirst($service);
            require_once($this->getAbsPath("/storage/services/" . $type . "_" . $service . "." . FF_PHP_EXT, true));

            $this->device = new Filemanager();

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            $config                                                     = $driver->getConfig();

            if($this->isError()) {
                $this->result[$service] = $this->isError();
            } else {
                switch($this->action)
                {
                    case "read":
                        $db->get();
                        break;
                    case "update":
                        $db->set();
                        break;
                    case "write":
                        $db->set();
                        break;
                    case "delete":

                        break;
                    default:
                }
            }
        }

    }
    private function clearResult() 
    {
        $this->data                                                     = array();
        $this->update                                                   = array();
        $this->action                                                   = null;
        $this->where                                                    = null;
        $this->table                                                    = null;
        $this->result                                                   = null;

        $this->isError("");
    }

    private function getResult()
    {
        return ($this->isError()
            ? $this->isError()
            : $this->result
        );
    }

    private function fields2string($fields, $db, $sep_complete = ", ", $sep_head = ", ", $sep_body = ", ", $op = "=")
    {
        if(is_array($fields) && count($fields))
        {
            foreach($fields AS $name => $value)
            {
                if(is_array($value))
                    $plain_value = $db->toSql(implode(",",  array_unique($value)));
                elseif(strpos($value, "~") === 0)
                    $plain_value = substr($value, 1);
                else
                    $plain_value = $db->toSql($value);
                
                $res["complete"][$name]     = $name . " " . $op ." " . $plain_value;
                $res["head"][$name]         = $name;
                $res["body"][$name]         = $plain_value;
            }
            return array(
                "head" => implode($sep_head, $res["head"])
                , "body" => implode($sep_body, $res["body"])
                , "complete" => implode($sep_complete, $res["complete"])
            );
        }
    }
}
