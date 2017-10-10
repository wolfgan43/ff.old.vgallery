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


class Anagraph extends vgCommon
{
    static $singleton                           = null;

    protected $storage                          = "mysql";
    protected $services                         = array();
    protected $struct                           = array(
                                                    "access" => array(
                                                        "table" => CM_TABLE_PREFIX . "mod_security_users"
                                                        , "key" => "ID"
                                                    )
                                                    , "data" => array(
                                                        "table" => "anagraph"
                                                        , "key" => "ID"
                                                        , "rel" => "uid"
                                                    )
                                                );
    protected $struct_default                   = "access";
    
    protected $query                            = null;
    protected $users                            = null;
    protected $groups                           = null;
    protected $fields                           = array();

    public static function getInstance($params)
	{
		if (self::$singleton === null)
			self::$singleton = new Anagraph($params);

		return self::$singleton;
	} 
    
    public function __construct($params) 
    {
        $this->setParams($params);
    }
    
    
    public function get()
    {
        foreach($this->services AS $name => $params) {
            if($params["field"]) {
                $this->addField($params["field"], $name);
            }

            $this->query["join"][] = $params["tbl"] . " ON " . $params["tbl"] . "." . $params["rel"] . " = " . $this->struct[$this->struct_default]["table"] . "." . $this->struct[$this->struct_default]["key"];        
        }

        $this->makeSelect();
        
        
       // $query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID IN(" . $dest["uid"] . ")";
    }


    
    public function addFields($fields) 
    {
        if(is_array($fields))
        {
            foreach($fields AS $name => $type)
            {
                $this->addField($name, $type);
            }
        } else {
            $this->addField($fields);
        }
    }
    
    
    public function get_user($dest = array(), $to, $fields = null, $service = null) {
        $db = ffDB_Sql::factory();

        if(is_array($fields) && count($fields)) {
            foreach($fields AS $field_name) {
                $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $field_name . "`";
            }
        } elseif($fields) {
            $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $fields . "`";        
        }

        if($service) {
            $query["select"][] = CM_TABLE_PREFIX . "mod_security_token.`token`";
            $query["join"][] = CM_TABLE_PREFIX . "mod_security_token.`token` ON " . CM_TABLE_PREFIX . "mod_security_token.`token`.ID_user = " . CM_TABLE_PREFIX . "mod_security_users.ID";        
        }

        if(is_array($to)) {
            if(is_array($to["uid"])) {
                $dest["uid"] = implode(",", $to["uid"]);
                $query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID IN(" . $dest["uid"] . ")";
            } elseif(is_numeric($to["uid"])) {
                $dest["uid"] = $to["uid"];
                $query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID = " . $dest["uid"];
            }
        }

        if($query) {
            $dest = array();
            $sSQL = "SELECT DISTINCT " . implode(", ", $query["select"]) . "
                    FROM " . CM_TABLE_PREFIX . "mod_security_users
                        " . (is_array($query["join"])
                            ? " INNER JOIN " . implode(" INNER JOIN ", $query["join"])
                            : ""
                        ) . "
                    WHERE " . implode(" AND ", $query["where"]);
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $dest["uid"][] = $db->record;
                } while($db->nextRecord());
            }
        }

        return $dest;
    }    
    
    private function addField($name, $type = null) 
    {
        $this->fields[$name] = $type;
    }
    
    private function makeSelect() 
    {
        if(is_array($this->fields))
        {
            foreach($this->fields AS $name => $type) 
            {
                if(!$this->struct[$type])
                    $type = $this->struct_default;
                
                $this->query["select"][] = $this->struct[$type]["table"] . "." . $name;
            }
        }
    }
    private function addWhere($value, $field = null, $type = null) 
    {
        if(!$field)
            $field = $value;

        $this->fields[$field] = $value;
    }
}