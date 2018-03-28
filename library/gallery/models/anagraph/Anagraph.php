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


class Anagraph extends vgCommon
{
    static $singleton                                       = null;

    protected $services                                     = array(
                                                                "anagraph"                  => null
                                                            );
    protected $controllers                                  = array(
    );
	private $connectors										= array(
                                                                "sql"                   => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"   		=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "ANAGRAPH_DATABASE_"
                                                                    , "table"           => "anagraph"
                                                                    , "key"             => "ID"
                                                                )
                                                                , "nosql"               => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"    	=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "ANAGRAPH_MONGO_DATABASE_"
                                                                    , "table"           => "anagraph"
                                                                    , "key"             => "ID"
                                                                )
                                                                , "fs"                  => array(
                                                                    "service"			=> "php"
                                                                    , "path"            => "/cache/anagraph"
                                                                    , "name"            => array("name", "email", "tel")
                                                                    , "var"				=> null
                                                                )
															);
	private $struct											= array(
	                                                            "anagraph" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_domain"           => "number"
	                                                                , "ID_type"             => "number"
                                                                    , "ID_lang"             => "number"
                                                                    , "ID_role"             => "number"
                                                                    , "ID_user"             => "number"
                                                                    , "avtar"               => "string"
                                                                    , "name"                => "string"
                                                                    , "email"               => "string"
                                                                    , "tel"                 => "string"
                                                                    , "tags"                => "string"
                                                                    , "status"              => "number"
                                                                    , "created"             => "number"
                                                                    , "last_update"         => "number"
                                                                    , "parent"              => "string"
                                                                    , "smart_url"           => "string"
                                                                    , "referer"             => "string"

                                                                )
                                                                , "anagraph_type" => array(
                                                                    "ID"                    => "primary"
                                                                    , "name"                => "string"
                                                                )
                                                                , "anagraph_role" => array(
                                                                    "ID"                    => "primary"
                                                                    , "name"                => "string"
                                                                )
                                                                , "anagraph_categories" => array(
                                                                    "ID"                    => "primary"
                                                                    , "name"                  => "string"
                                                                )
                                                                , "anagraph_rel_categories" => array(
                                                                    "ID_anagraph"           => "string"
                                                                    , "ID_categories"       => "string"
                                                                )
                                                                , "anagraph_email" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "type"                => "string"
                                                                    , "email"               => "string"
                                                                )
                                                                , "anagraph_tel" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "type"                => "string"
                                                                    , "tel"                 => "string"
                                                                )
                                                                , "anagraph_social" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "type"                => "string"
                                                                    , "url"                 => "string"
                                                                    , "text"                => "text"
                                                                )
															);
	private $relationship                                   = array(
	                                                            "anagraph" => array(
	                                                                "ID_type"               => array(
	                                                                    "tbl"               => "anagraph_type"
                                                                        , "key"             => "ID"
                                                                    )
	                                                                , "ID_domain"           => array(
	                                                                    "tbl"               => "domains"
                                                                        , "key"             => "ID"
                                                                    )
                                                                    , "ID_lang"             => array(
                                                                        "tbl"               => "lang"
                                                                        , "key"             => "ID"
                                                                    )
                                                                    , "ID_user"             => array(
                                                                        "tbl"               => "users"
                                                                        , "key"             => "ID"
                                                                    )
                                                                    , "categories"          => array(
                                                                        "tbl"               => "anagraph_rel_categories"
                                                                        , "key"             => "ID_anagraph"
                                                                        , "type"            => "n/n"
                                                                    )
                                                                )
                                                                , "anagraph_categories"     => array(
                                                                    "anagraph"              => array(
                                                                        "tbl"                   => "anagraph_rel_categories"
                                                                        , "key"                 => "ID_category"
                                                                        , "type"                => "n/n"
                                                                    )
                                                                )
                                                            );
    private $indexes                                        = array(
                                                                "anagraph" => array(
                                                                    "ID_domain"             => "hardindex"
                                                                    , "ID_type"             => "hardindex"
                                                                    , "ID_lang"             => "hardindex"
                                                                    , "ID_user"             => "hardindex"

                                                                )
                                                            );
    private $tables                                         = array(
                                                                "anagraph"                  => array(
                                                                    "engine"                => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_type"           => array(
                                                                    "engine"                => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                            );


    protected $query                                        = "";
    protected $users                                        = null;
    protected $groups                                       = null;
    protected $fields                                       = array();
    private $session							            = null;

	public static function getInstance($services = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Anagraph($services);
		else {
            self::$singleton->setServices($services);
		}
		return self::$singleton;
	}

	public function __construct($services = null)
	{
        $this->loadControllers(__DIR__);

		$this->setServices($services);

        $this->setConfig($this->connectors, $this->services);


		$this->loadSession();

		//da aggiungere inizializzazioni classe necessarie come anagraph
	}

    private function loadSession()
	{
		if(!$this->session) {
			$this->session = get_session("user_permissions");
		}
	}
	private function getAnagraphByUser($uid = null) {
    	if(!$uid)
			$uid = $this->session["ID"];

	}

    private function getStruct($type = "anagraph")
    {
        return $this->struct[$type];
    }
    private function getStorage($service = "anagraph")
    {
        if($service == "anagraph" && !$this->services["anagraph"]) {
            foreach($this->connectors AS $type => $data)
            {
                $this->services["anagraph"][$type] = array(
                    "service" 			=> $this->connectors[$type]["service"]
                    , "connector" 		=> $this->connectors[$type]
                );
            }
        }



        $storage = Storage::getInstance($this->services[$service], array(
            "struct" => $this->getStruct($service)
        ));

        return $storage;
    }

    public function get($where = null, $fields = null)
    {
        $struct = array();
        $storage = $this->getStorage();

        if(is_array($fields) && count($fields)) {
            foreach($fields AS $key ) {
                $parts = explode(".", $key);
                switch(count($parts)) {
                    case "3":
                        $this->services[$parts[0]] = null;
                        /* fa cose remote per recuperare le info */
                        $table = $parts[1];
                        $field_index = 2;
                        break;
                    case "2":
                        //$storage->setTable($parts[0]);
                        $table = $parts[0];
                        $field_index = 1;
                        break;
                    case "1":
                        $table = "anagraph";
                        $field_index = 0;
                    default:
                }

                if(!$struct[$table])
                    $struct[$table] = $this->getStruct($table);


                if($struct[$table][$parts[$field_index]]) {
                    $data[$table][$parts[$field_index]] = true;
                } else {
                    $error[$table][$parts[$field_index]] = true;
                }
            }
        }
        print_r($data);
        print_r($error);

        $res = $storage->read($where, $data["anagraph"]);
		print_r($res);
		die();

		$this->result = $storage->read($where, $fields);
		$res = ($this->result
			? $this->result
			: array(
				"result" => array()
			)
		);

		print_r($this->result);
        die();


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