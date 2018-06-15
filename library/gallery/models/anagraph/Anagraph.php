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
    const DEBUG                                             = DEBUG_MODE;
    const TYPE                                              = "anagraph";
    const MAIN_TABLE                                        = "anagraph";

    public $strict                                          = false;

    protected $service                                      = null;
    protected $controllers                                  = array(
    );
    protected $controllers_rev                              = null;
	private $connectors										= array(
                                                                "sql"                   => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"   		=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "ANAGRAPH_DATABASE_"
                                                                    , "table"           => null
                                                                    , "key"             => "ID"
                                                                )
                                                                , "nosql"               => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"    	=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "ANAGRAPH_MONGO_DATABASE_"
                                                                    , "table"           => null
                                                                    , "key"             => "ID"
                                                                )
                                                                , "fs"                  => array(
                                                                    "service"			=> "php"
                                                                    , "path"            => "/cache/anagraph"
                                                                    , "name"            => array("name", "email", "tel")
                                                                    , "key"				=> null
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
                                                                    , "avatar"              => "string"
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
                                                                    , "custom1"             => "string"
                                                                    , "custom2"             => "string"
                                                                    , "custom3"             => "string"
                                                                    , "custom4"             => "string"
                                                                    , "custom5"             => "string"
                                                                    , "custom6"             => "string"
                                                                    , "custom7"             => "string"
                                                                    , "custom8"             => "string"
                                                                    , "custom9"             => "string"
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
                                                                    , "name"                => "string"
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
                                                                , "anagraph_person" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "name"                => "string"
                                                                    , "surname"             => "string"
                                                                    , "cell"                => "string"
                                                                    , "gender"              => "char"
                                                                    , "birthday"            => "date"
                                                                    , "cv"                  => "text"
                                                                    , "abstract"            => "text"
                                                                    , "biography"           => "text"
                                                                )
                                                                , "anagraph_company" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "ragsoc"              => "string"
                                                                    , "address"             => "string"
                                                                    , "cap"                 => "string"
                                                                    , "city"                => "string"
                                                                    , "prov"                => "string"
                                                                    , "ID_place"            => "number"
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
                                                                    , "anagraph_person"     => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                    , "anagraph_company"    => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
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
                                                                , "anagraph_type"           => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"              => "ID_type"
                                                                        , "primary"             => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_person"         => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"              => "ID_anagraph"
                                                                        , "primary"             => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_company"         => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"              => "ID_anagraph"
                                                                        , "primary"             => "ID"
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
                                                                    "name"                  => "anagraph"
                                                                    , "alias"               => "anagraph"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_type"           => array(
                                                                    "name"                  => "anagraph_type"
                                                                    , "alias"               => "type"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_person"           => array(
                                                                    "name"                  => "anagraph_person"
                                                                    , "alias"               => "person"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_company"           => array(
                                                                    "name"                  => "anagraph_company"
                                                                    , "alias"               => "company"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                            );
    private $alias                                          = array(
                                                                "anagraph"                  => array(
                                                                    "uid"                   => "ID_user"
                                                                    , "ID_domains"          => "ID_domain"
                                                                    , "ID_languages"        => "ID_lang"
                                                                )
                                                            );

    protected $query                                        = "";
    protected $users                                        = null;
    protected $groups                                       = null;
    protected $fields                                       = array();

    private $data                                           = null;

    private $debug                                          = array();
    private $result                                         = array();

    /**
     * @param null $service
     * @return Anagraph|null
     */
    public static function getInstance($service = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Anagraph($service);
		else {
            self::$singleton->service = $service;
		}
		return self::$singleton;
	}

    /**
     * Anagraph constructor.
     * @param null $service
     */
    public function __construct($service = null)
	{
        $this->loadControllers(__DIR__);

 		$this->service = $service;

        $this->setConfig($this->connectors, $this->services);

		//da aggiungere inizializzazioni classe necessarie come anagraph
	}

    /**
     * @param null $fields
     * @param null $where
     * @param null $sort
     * @param null $limit
     * @return array
     */
    public function read($fields = null, $where = null, $sort = null, $limit = null) {
        if(self::DEBUG)                                                                     $start = Stats::stopwatch();

        if(!$where && !$sort && !$limit) {
            $where                                                                          = $fields;
            $fields                                                                         = null;
        }

        $res                                                                                = $this->get($where, $fields, $sort, $limit);

        if(self::DEBUG)                                                                     $this->debug("read", array(
                                                                                                "data" => $this->data
                                                                                                , "exTime" => Stats::stopwatch($start)
                                                                                            ));

        //print_r($this->data);
        return $res;
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function insert($data) {
	    if(self::DEBUG)                                                                     $start = Stats::stopwatch();

        $res                                                                                = $this->set($data);

        if(self::DEBUG)                                                                     $this->debug("insert", array(
                                                                                                "data" => $this->data
                                                                                                , "exTime" => Stats::stopwatch($start)
                                                                                            ));

	    return $res;
    }

    /**
     * @param $set
     * @param $where
     * @return array|mixed|null
     */
    public function update($set, $where) {
        if(self::DEBUG)                                                                     $start = Stats::stopwatch();

        $res                                                                                = $this->set($where, $set);

        if(self::DEBUG)                                                                     $this->debug("update", array(
                                                                                                "data" => $this->data
                                                                                                , "exTime" => Stats::stopwatch($start)
                                                                                            ));

        return $res;
    }

    /**
     * @param $where
     * @param null $set
     * @param null $insert
     * @return array|mixed|null
     */
    public function write($where, $set = null, $insert = null) {
        if(self::DEBUG)                                                                     $start = Stats::stopwatch();

        $res                                                                                = $this->set($where, $set, $insert);

        if(self::DEBUG)                                                                     $this->debug("write", array(
                                                                                                "data" => $this->data
                                                                                                , "exTime" => Stats::stopwatch($start)
                                                                                            ));

        return $res;
    }

    /**
     * @param $where
     *
     * @todo: da fare
     */
    public function delete($where) {

    }

    /**
     * @param null $service
     * @return string
     */
    private function getMainTable($service = null) {
	    if($service) {
            $controller                                                                     = $this->getControllerName($service);
            $table                                                                          = $controller::MAIN_TABLE;
        } else {
	        $table                                                                          = Anagraph::MAIN_TABLE;
        }

        return $table;
    }

    /**
     * @param string $type
     * @param null $service
     * @return array
     */
    private function getStruct($type = Anagraph::TYPE, $service = null)
    {
        static $controllers                                                                 = null;

        if($service) {
            $controller                                                                     = $this->getControllerName($service);
            if(!$controllers[$controller][$type])
                $controllers[$controller][$type]                                            = ($this->controllers_rev[$controller]
                                                                                                ? $controller::getStruct($type)
                                                                                                : false
                                                                                            );
            return $controllers[$controller][$type];
        } else {
            $table                                                                          = $this->tables[$type];
            $table["name"]                                                                  = $type;
            return array(
                "struct"                                                                    => $this->struct[$type]
                , "indexes"                                                                 => $this->indexes[$type]
                , "relationship"                                                            => $this->relationship[$type]
                , "table"                                                                   => $table
                , "alias"                                                                   => $this->alias[$type]
                , "connectors"                                                              => false
                , "mainTable"                                                               => Anagraph::MAIN_TABLE
            );
        }
    }

    /**
     * @param null $service
     * @param null $struct
     * @return null|Storage
     */
    private function getStorage($service = null, $struct = null)
    {
        if($service) {
            $controller                                                                     = $this->getControllerName($service);
            $connectors                                                                     = ($this->controllers_rev[$controller]
                                                                                                ? $controller::getConfig($this)
                                                                                                : false
                                                                                            );
        } else {
            $connectors                                                                     = $this->connectors;
        }
        if(!$struct)
            $struct                                                                         = $this->getStruct();

        $struct["exts"]                                                                     = true; //!$service;


        return Storage::getInstance($connectors, $struct);
    }

    /**
     * @param null $where
     * @param null $fields
     * @return array
     */
    private function get($where = null, $fields = null, $sort = null, $limit = null)
    {
        $this->clearResult();
        $opt                                                                                = array(
                                                                                                "sort"      => $sort
                                                                                                , "limit"   => $limit
                                                                                            );
        $this->resolveFieldsByScopes(array(
            "select"                                                                        => $fields
            , "where"                                                                       => $where
        ));

        if($where === true)                                                                 $this->data["main"]["where"] = true;

        $this->getData(null, null, $opt);       //try main table

        if(is_array($this->data["sub"]) && count($this->data["sub"])) {
            foreach($this->data["sub"] AS $controller => $tables) {
                foreach($tables AS $table => $params) {
                    $this->getData($controller, $table, $opt);
                }
            }
        }

        return $this->getResult();
    }

    /**
     * @param null $controller
     * @param null $table
     * @param null $opt
     */
    private function getData($controller = null, $table = null, $opt = null) {
        $table_rel                                                                          = false;
        $data                                                                               = (!$controller && !$table
                                                                                                ? $this->data["main"]
                                                                                                : $this->data["sub"][$controller][($table
                                                                                                    ? $table
                                                                                                    : $this->getMainTable($controller)
                                                                                                )]
                                                                                            );
        $where                                                                              = ($data["where"] === true
                                                                                                ? true
                                                                                                : $this->getFields($data["where"], $data["def"]["alias"])
                                                                                            );
        $table_main                                                                         = ($data["def"]["relationship"][$this->data["main"]["def"]["mainTable"]]
                                                                                                ? $this->data["main"]["def"]["mainTable"]
                                                                                                : $data["def"]["mainTable"]
                                                                                            );

        if($data["def"]["relationship"][$table_main] && $this->data["exts"]) {
            $field_ext                                                                      = $data["def"]["relationship"][$table_main]["external"];
            $field_key                                                                      = $data["def"]["relationship"][$table_main]["primary"];


            if($data["def"]["struct"][$field_ext]) { //imposta la tabella di relazione se la chiave è esterna es:   mol.studi.def.struct.ID_anagraph o doctors.def.struct.ID_anagraph
//echo "tbl: " . $table . "\n";
//echo "tbl main: " . $table_main . "\n";
//echo "pre External: " . $field_ext . "\n";
//echo "post External: " . $field_key . "\n";
                //if(!$data["def"]["relationship"][$field_ext]) {
                $field_ext                                                                  = $data["def"]["relationship"][$table_main]["primary"];
                $field_key                                                                  = $data["def"]["relationship"][$table_main]["external"];
                //}
                $table_rel                                                                  = ($this->data["exts"][$table][$field_ext]
                                                                                                ? $table
                                                                                                : $table_main
                                                                                            );


//echo "tbl rel: " . $table_rel . "." . $field_ext . "\n";
//echo "--------------------------\n";
            }

            $ids                                                                            = (is_array($this->data["exts"][$table_main][$field_ext])
                                                                                                ? array_keys($this->data["exts"][$table_main][$field_ext])
                                                                                                : null
                                                                                            );
            if($ids) {
                $where[$this->getFieldAlias($field_key, $data["def"]["alias"])]             = (count($ids) == 1
                                                                                                ? $ids[0]
                                                                                                : $ids
                                                                                            );

                $this->data["sub"][$controller][$table]["where"]                            = $where; //for debug
            }

        }

        if($where) {
            $sub_ids                                                                        = null;
            $indexes                                                                        = $data["def"]["indexes"];
            $select                                                                         = $this->getFields(
                                                                                                $data["select"]
                                                                                                , $data["def"]["alias"]
                                                                                                , $indexes
                                                                                                , array_search("primary", $data["def"]["struct"])
                                                                                            );

            $regs                                                                           = $this->getStorage($data["service"], $data["def"])->read(
                                                                                                ($where === true
                                                                                                    ? null
                                                                                                    : $where
                                                                                                )
                                                                                                , $select
                                                                                                , $opt["sort"]
                                                                                                , $opt["limit"]
                                                                                            );
            if(is_array($regs)) {
                if($regs["exts"]) {
                    $this->data["exts"][$data["def"]["mainTable"]]                          = (array) $this->data["exts"][$data["def"]["mainTable"]] + $regs["exts"];

                    if($this->data["main"]["select"] && !$this->data["main"]["where"]) {
                        $field_ext                                                          = $data["def"]["relationship"][$table_main]["external"];
                        $field_key                                                          = $data["def"]["relationship"][$table_main]["primary"];

                        $ids                                                                = array_keys($regs["exts"][$field_ext]);
                        if($ids) {
                            $this->data["main"]["where"][$field_key]                        = (count($ids) == 1
                                                                                                ? $ids[0]
                                                                                                : $ids
                                                                                            );

                            $this->getData(); //try main table by sub
                            //$sub_ids                                                      = array_keys($this->data["exts"][$this->data["main"]["def"]["mainTable"]][$field_ext]);
                            $sub_ids                                                        = $ids;
                        }
                    }
                }


                if(is_array($regs["keys"]) && count($regs["keys"])) {
                    $table_name                                                             = $data["def"]["table"]["alias"];

                    if(!$table_rel)         //se è una maintable ma non anagraph reimposta l'external base es: doctors -> anagraph -> external
                        $field_ext                                                              = $data["def"]["relationship"][$table_main]["external"];

                    foreach($regs["keys"] AS $i => $id) {
                            $result                                                         = ($indexes
                                                                                                ? array_diff_key($regs["result"][$i], $indexes)
                                                                                                : $regs["result"][$i]
                                                                                            );

                            $keys                                                           = ($table_rel
                                                                                                ? array_keys($this->data["exts"][$table_rel][$field_ext])
                                                                                                : $this->data["exts"][$table_main][$field_ext][$id]
                                                                                            );

                            $ids                                                            = ($sub_ids
                                                                                                ? $sub_ids
                                                                                                : $keys
                                                                                            );
                        if(is_array($ids) && count($ids)) {
                            foreach($ids AS $id) {
                                /*if(0 && $opt["limit"] == 1)
                                    $this->setResult($this->result[$id], $result);
                                else*/
                                if($table_rel) { //discende fino ad anagraph per fondere i risultati annidati esempio anagraph -> users -> tokens
                                    $root_ids = $this->data["exts"][anagraph::MAIN_TABLE][$field_key][$id];
                                    if(is_array($root_ids) && count($root_ids) == 1) {
                                        $id = $root_ids[0];
                                    }
                                }
                                    $this->setResult($this->result[$id][$table_name], $result);

                                /*if($this->result[$id][$table_name]) {
                                    if($this->isAssocArray($this->result[$id][$table_name]))
                                        $this->result[$id][$table_name]                     = array("0" => $this->result[$id][$table_name]);

                                    $this->result[$id][$table_name][]                       = $result;
                                } else {
                                    $this->result[$id][$table_name]                         = $result;
                                }*/
                            }
                        } else {
                            $this->setResult($this->result[$id], $result);
                            //$this->result[$id]                                            = $result;
                        }
                    }
                }
            } else {
                $this->isError($regs);
            }
        }
    }

    /**
     * @param $where
     * @param null $set
     * @param null $data
     * @return array|mixed|null
     */
    private function set($where, $set = null, $data = null)
    {
        $this->clearResult();

        if(!$set && !$data) {
            $data                                                                           = $where;
            $where                                                                          = null;
        }

        $this->resolveFieldsByScopes(array(
            "insert"                                                                        => $data
            , "set"                                                                         => $set
            , "where"                                                                       => $where
        ));


        if(is_array($this->data["sub"]) && count($this->data["sub"])) {
            foreach($this->data["sub"] AS $controller => $tables) {
                $setMainTableDefer                                                          = null;
                $setSubTableDefer                                                           = null;

                foreach($tables AS $table => $params) {
                    if($params["def"]["struct"][$params["def"]["relationship"][$params["def"]["mainTable"]]["external"]]
                        || $params["def"]["struct"][$params["def"]["relationship"][$this->data["main"]["mainTable"]]["external"]]
                    ) {
                        if($controller)
                            $setSubTableDefer[$table]                                       = $controller;
                        else
                            $setMainTableDefer[$table]                                      = $controller;
                    } else {
                        $this->setData($controller, $table);
                    }
                }

                if(is_array($setSubTableDefer) && count($setSubTableDefer)) {
                    foreach($setSubTableDefer AS $table => $controller) {
                        $this->setData($controller, $table);
                    }
                }
            }
        }

        //main table
        $this->setData();

        if(is_array($setMainTableDefer) && count($setMainTableDefer)) {
            foreach($setMainTableDefer AS $table => $controller) {
                $this->setData($controller, $table);
            }
        }

        return $this->getResult();
    }

    /**
     * @param null $controller
     * @param null $table
     *
     */
    private function setData($controller = null, $table = null) {
        $data                                                                               = (!$controller && !$table
                                                                                                ? $this->data["main"]
                                                                                                : $this->data["sub"][$controller][($table
                                                                                                    ? $table
                                                                                                    : $this->getMainTable($controller)
                                                                                                )]
                                                                                            );

        $storage                                                                            = $this->getStorage($data["service"], $data["def"]);
        $key_name                                                                           = $this->getFieldAlias(array_search("primary", $data["def"]["struct"]), $data["def"]["alias"]);

        if($data["insert"]) {
            if(!$data["where"])                                                             $data["where"] = $data["insert"];

            $regs                                                                           = $storage->read($data["where"], array($key_name => true));
            if(is_array($regs))
                $key                                                                        = $regs["keys"][0];
            else
                $this->isError($regs);

            if(!$key && !$this->isError()) {
                $regs                                                                       = $storage->insert($data["insert"]);
                if(is_array($regs)) {
                    $regs                                                                   = array_values($regs);
                    $key                                                                    = $regs[0]["keys"];
                } else {
                    $this->isError($regs);
                }



            }
        } elseif($data["set"] && $data["where"]) {
            $regs                                                                           = $storage->update($data["set"], $data["where"]);
            $key                                                                            = $regs["keys"][0];
        } elseif($data["where"] && !$data["insert"] && !$data["set"]) {
            $regs                                                                           = $storage->delete($data["where"]);
            $key                                                                            = $regs["keys"][0];
        } elseif($data["insert"] && $data["set"] && $data["where"]) {
            $regs                                                                           = $storage->write(
                                                                                                $data["insert"]
                                                                                                , array(
                                                                                                    "set" => $data["set"]
                                                                                                    , "where" => $data["where"]
                                                                                                )
                                                                                            );
            $key                                                                            = $regs["keys"][0];
        }

        if(is_array($data["def"]["relationship"]) && count($data["def"]["relationship"])) {
            foreach ($data["def"]["relationship"] AS $tbl => $rel) {
                $field_ext                                                                  = $rel["external"];
                $field_key                                                                  = $rel["primary"];

                if($data["def"]["struct"][$field_ext]) {
                    $field_ext                                                              = $rel["primary"];
                    $field_key                                                              = $rel["external"];

                    //$rel_rev                                                                = true;
                }
                if($field_ext && $field_ext != $key_name) {
                    if ($tbl != $this->data["main"]["def"]["mainTable"]) {
                        $field_alias                                                        = $this->getFieldAlias($field_ext, $this->data["sub"][$controller][$tbl]["def"]["alias"]);
                        if ($this->data["sub"][$controller][$tbl]["insert"]) {
                            $this->data["sub"][$controller][$tbl]["insert"][$field_alias]   = $key;
                            $this->data["sub"][$controller][$tbl]["where"][$field_alias]    = $key;
                        }
                        if ($this->data["sub"][$controller][$tbl]["update"]) {
                            $this->data["sub"][$controller][$tbl]["update"][$field_alias]   = $key;
                            $this->data["sub"][$controller][$tbl]["where"][$field_alias]    = $key;
                        }
                        if ($this->data["sub"][$controller][$tbl]["delete"]) {

                        }
                    } else {
                        $field_alias                                                         = $this->getFieldAlias($field_ext, $this->data["main"]["def"]["alias"]);
                        if ($this->data["main"]["insert"]) {
                            $this->data["main"]["insert"][$field_alias]                      = $key;
                        }
                        if ($this->data["main"]["update"]) {
                            $this->data["main"]["update"][$field_alias]                      = $key;
                        }
                        if ($this->data["main"]["delete"]) {

                        }
                    }
                }
            }
        }

        if($key)                                                                                $this->result["keys"][$data["def"]["table"]["alias"]] = $key;
    }

    /**
     * @param $result
     * @param $entry
     */
    private function setResult(&$result, $entry) {
        if($result) {
            if($this->isAssocArray($result))
                $result                                                                     = array("0" => $result);

            $result[]                                                                       = $entry;
        } else {
            $result                                                                         = $entry;
        }
    }

    /**
     * @param $data
     * @return null
     */
    private function resolveFieldsByScopes($data) {
        foreach ($data as $scope => $fields) {
            $this->resolveFields($fields, $scope);
        }

        if(!($this->data["main"]["where"] || $this->data["main"]["select"] || $this->data["main"]["insert"]) && count($this->services) == 1) {
            $subService                                                                     = key($this->services);
            $subTable                                                                       = $this->getMainTable($subService);
            $this->data["main"]                                                             = $this->data["sub"][$subService][$subTable];
            $this->data["main"]["service"]                                                  = $subService;
            unset($this->data["sub"][$subService][$subTable]);
        } else {
            $mainService                                                                    = $this->service;
            $mainTable                                                                      = $this->getMainTable($mainService);

            $this->data["main"]["def"]                                                      = $this->getStruct($mainTable, $mainService);
            $this->data["main"]["service"]                                                  = $mainService;
        }

        if(!$this->data["main"]["select"] && $data["select"]) {
            $key_name                                                                       = array_search("primary", $this->data["main"]["def"]["struct"]);
            $this->data["main"]["select"][$key_name]                                        = "primary_key";
        }

        return $this->data;
    }

    /**
     * @param $fields
     * @param string $scope
     * @return null
     */
    private function resolveFields($fields, $scope = "fields") {
        $mainService                                                                        = ($this->service ? $this->service : Anagraph::TYPE);
        $mainTable                                                                          = ($this->service ? $this->getMainTable($mainService) : Anagraph::MAIN_TABLE);

        if(is_array($fields) && count($fields)) {
            foreach($fields AS $key => $alias) {
                $service                                                                    = $this->service;
                if(is_numeric($key)) {
                    $key                                                                    = $alias;
                    $alias                                                                  = true;
                } elseif(is_null($alias)) { //todo: possibile errore prima era !alias ma da problemi con i valori = 0
                    $alias                                                                  = true;
                }

                $parts                                                                      = explode(".", $key);
                switch(count($parts)) {
                    case "3":
                        $service                                                            = $parts[0];
                        $table                                                              = $parts[1];
                        $fIndex                                                             = ($service == $mainService && $table == $mainTable
                                                                                                ? -2
                                                                                                : 2
                                                                                            );
                        $this->services[$service]                                           = null;
                        break;
                    case "2":
                        $table                                                              = $parts[0];
                        $fIndex                                                             = ($table == $mainTable
                                                                                                ? -1
                                                                                                : 1
                                                                                            );
                        break;
                    case "1":
                        $table                                                              = $mainTable;
                        $fIndex                                                             = null;

                    default:
                }

                if($fIndex === null || $fIndex < 0) {
                    $this->data["main"][$scope][$parts[abs($fIndex)]]                       = $alias;
                    continue;
                }

                if(!$this->data["sub"][$service][$table]["def"])
                    $this->data["sub"][$service][$table]["def"]                             = $this->getStruct($table, $service);

                if(!$this->data["sub"][$service][$table]["def"]["struct"][$parts[$fIndex]])
                    continue;

                if($scope == "insert") {
                    $this->data["sub"][$service][$table]["insert"][$parts[$fIndex]]         = $alias;
                    $this->data["sub"][$service][$table]["where"][$parts[$fIndex]]          = $alias;
                } else {
                    $this->data["sub"][$service][$table][$scope][$parts[$fIndex]]           = $alias;
                }
            }
        } elseif($fields === true) {
            $this->data["main"][$scope] = $fields;
        }

        return $this->data;
    }

    /**
     * @param array $fields
     * @param null $alias
     * @param null $indexes
     * @return array
     */
    private function getFields($fields = array(), $alias = null, &$indexes = null) {
	    if(is_array($fields) && count($fields)) {
            $res                                                                            = $fields;

            if(is_array($indexes) && count($indexes)) {
                $res                                                                        = $res + array_fill_keys(array_keys($indexes), true);

                if (is_array($alias) && count($alias))
                    $indexes                                                                = array_diff_key($indexes, $alias);

                foreach ($fields AS $field_key => $field_ext) {
                    if ($indexes[$field_key])                                               unset($indexes[$field_key]);
                    if ($indexes[$field_ext])                                               unset($indexes[$field_ext]);
                }
            }

            if (is_array($alias) && count($alias)) {
                foreach ($alias AS $old => $new) {
                    if ($res[$new]) {
                        $res[$old]                                                          = $res[$new];
                                                                                            unset($res[$new]);
                    }

                    if($fields[$old] && $indexes[$new])                                     unset($indexes[$new]);
                }
            }

            $indexes["primary_key"]                                                         = true;
        }

        return $res;
    }

    private function getFieldAlias($field, $alias) {
        if(is_array($alias) && count($alias)) {
            $alias_rev = array_flip($alias);
            return($alias_rev[$field]
                ? $alias_rev[$field]
                : $field
            );
        } else {
            return $field;
        }
    }

    /**
     * @param $service
     * @return string
     */
    private function getControllerName($service) {
	    return Anagraph::TYPE . ucfirst($service);
    }

    /**
     * @param $service
     */
    private function controller($service) {
        $controller                                                         = $this->getControllerName($service);
        if($this->controllers_rev[$controller]) {
            $driver                                                         = new $controller($this);
           // $db                                                            = $driver->getDevice();
        }
    }

    /**
     * @param $fields
     */
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

    /**
     * @param $name
     * @param null $type
     */
    private function addField($name, $type = null)
    {
        $this->fields[$name] = $type;
    }

    /**
     *
     */
    private function clearResult()
    {
        $this->data                                                         = array();
        $this->debug                                                        = array();
        $this->result                                                       = array();
        $this->isError("");
    }

    private function resolveResult() {
        if($this->strict) {
            $res                                                            = $this->result;
        } elseif(is_array($this->result)) {
            if(count($this->result) > 1) {
                $res                                                        = array_values($this->result);
            } else {
                $res                                                        = current($this->result);
                if (count($this->data["sub"][$this->data["main"]["service"]]) == 1 && count($res) == 1) {
                    $res                                                    = current($res);
                }
            }
        } else {
            $res                                                            = $this->result; // non deve mai entrare qui
        }

        return $res;
    }
    /**
     * @return array|mixed|null
     */
    private function getResult()
    {
        return ($this->isError()
            ? $this->isError()
            : $this->resolveResult()
        );
    }
}