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

    private $strict                                         = true;

    protected $service                                      = null;
    protected $controllers                                  = array(
    );
    protected $controllers_rev                              = null;
	protected $connectors										= array(
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
                                                                    , "path"            => "/cache/[TABLE]"
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
                                                                    , "referer"             => "string"
                                                                    , "valid_email"         => "number"
                                                                    , "valid_tel"           => "number"
                                                                    , "password_alg"        => "number"
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
                                                                , "anagraph_categories"     => array(
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
                                                                    , "cf"                  => "string"
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
                                                                , "anagraph_newsletter" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "name"                => "string"
                                                                )
                                                                , "anagraph_seo" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_lang"             => "string"
                                                                    , "ID_src"              => "string"
                                                                    , "visible"             => "string"
                                                                    , "permalink"           => "string"
                                                                    , "parent"              => "string"
                                                                    , "smart_url"           => "string"
                                                                    , "meta_title"          => "string"
                                                                    , "meta_description"    => "string"
                                                                    , "meta_robots"         => "string"
                                                                    , "h1"                  => "string"
                                                                    , "meta_canonical"      => "string"
                                                                    , "meta"                => "string"
                                                                    , "httpstatus"          => "number"
                                                                    , "keywords"            => "string"
                                                                    , "pre_content"         => "string"
                                                                    , "post_content"        => "string"
                                                                )
                                                                , "anagraph_place" => array(
                                                                    "ID"                    => "primary"
                                                                    , "ID_anagraph"         => "number"
                                                                    , "billprovince"        => "string"
                                                                    , "billtown"            => "string"
                                                                    , "billcap"             => "string"
                                                                    , "billaddress"         => "string"
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
                                                                    , "anagraph_rel_categories" => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                    , "anagraph_seo"      => array(
                                                                        "external"          => "ID_src"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                    , "anagraph_place"      => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_rel_categories" => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_type"           => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_type"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_person"         => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_company"        => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_newsletter"     => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_anagraph"
                                                                    , "primary"             => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_seo"            => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_src"
                                                                        , "primary"         => "ID"
                                                                    )
                                                                )
                                                                , "anagraph_place"          => array(
                                                                    Anagraph::MAIN_TABLE    => array(
                                                                        "external"          => "ID_anagraph"
                                                                        , "primary"         => "ID"
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
                                                                , "anagraph_person"         => array(
                                                                    "ID_anagraph"           => "hardindex"
                                                                )

                                                                , "anagraph_newsletter"     => array(
                                                                    "ID_anagraph"           => "hardindex"
                                                                )
                                                                , "anagraph_rel_categories" => array(
                                                                    "ID_anagraph"           => "hardindex"
                                                                )
                                                                , "anagraph_place"          => array(
                                                                    "ID_anagraph"           => "hardindex"
                                                                )
                                                                , "anagraph_seo"            => array(
                                                                    "ID_src"                => "hardindex"
                                                                    , "permalink"           => "unique"
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
                                                                , "anagraph_person"         => array(
                                                                    "name"                  => "anagraph_person"
                                                                    , "alias"               => "person"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_company"        => array(
                                                                    "name"                  => "anagraph_company"
                                                                    , "alias"               => "company"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_newsletter"     => array(
                                                                    "name"                  => "anagraph_newsletter"
                                                                    , "alias"               => "newsletter"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_seo"            => array(
                                                                    "name"                  => "seo_anagraph"
                                                                    , "alias"               => "seo"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_categories" => array(
                                                                    "name"                  => "anagraph_categories"
                                                                    , "alias"               => "categories"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_rel_categories" => array(
                                                                    "name"                  => "anagraph_rel_categories"
                                                                    , "alias"               => "rel_categories"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                                , "anagraph_place" => array(
                                                                    "name"                  => "anagraph_place"
                                                                    , "alias"               => "place"
                                                                    , "engine"              => "InnoDB"
                                                                    , "crypt"               => false
                                                                    , "pairing"             => false
                                                                    , "transfert"           => false
                                                                    , "charset"             => "utf8"
                                                                )
                                                            );
    private $alias                                          = array(/*
                                                                "anagraph"                  => array(
                                                                    "uid"                   => "ID_user"
                                                                    , "ID_domains"          => "ID_domain"
                                                                    , "ID_languages"        => "ID_lang"
                                                                )
                                                            */);

    protected $query                                        = "";
    protected $users                                        = null;
    protected $groups                                       = null;
    protected $fields                                       = array();

    private $services_by_data                               = array();

    private $data                                           = null;

    private $debug                                          = array();
    private $result                                         = array();

    /**
     * @param null $service
     * @return Anagraph|null
     */
    public static function getInstance($service = null)
	{
        return self::setSingleton($service);
	}
    /**
     * @param null $service
     * @return Anagraph|null
     */
    public static function getInstanceNoStrict($service = null)
    {
        return self::setSingleton($service, false);
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

    private static function setSingleton($service, $strict = true) {
        if (self::$singleton === null)
            self::$singleton                                                                = new Anagraph($service);
        else
            self::$singleton->service                                                       = $service;

        self::$singleton->strict                                                            = $strict;

        return self::$singleton;
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
            if(!$table["name"])                                                             $table["name"] = $type;

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
//echo "slot----------------------------<br>\n";
        $this->resolveFieldsByScopes(array(
            "select"                                                                        => $fields
            , "where"                                                                       => $where
            , "sort"                                                                        => $sort
        ));
//print_r($fields);
        //print_r($this->data);
        if($where === true)                                                                 $this->data["main"]["where"] = true;

        if(is_array($this->data["sub"]) && count($this->data["sub"])) {
            foreach($this->data["sub"] AS $controller => $tables) {
                foreach($tables AS $table => $params) {
                    $keys_unique                                                            = array_keys($params["def"]["indexes"], "unique");
                    if(count($keys_unique)) {
                        if (array_intersect($keys_unique, array_keys($params["where"])) == $keys_unique) {
                            $this->data["sub"][$controller][$table]["runned"]               = true;
                            $counter                                                        = $this->getData($controller, $table);
                            if($counter === false)                                          { return $this->getResult(); }

                            unset($this->data["exts"]);
                        };
                    }
                }
            }
        }

        $counter = $this->getData(null, null, $limit);       //try main table
        if($counter === false)                                                              { return $this->getResult(); }

        if(is_array($this->data["sub"]) && count($this->data["sub"])) {
            foreach($this->data["sub"] AS $controller => $tables) {
                foreach($tables AS $table => $params) {
                    if(!$params["runned"])                                                  $counter = $this->getData($controller, $table);
                    if($counter === false)                                                  { return $this->getResult(); }

                }
            }
        }
//print_r($this->result);
        return $this->getResult();
    }

    /**
     * @param null $controller
     * @param null $table
     * @param null $limit
     */
    private function getData($controller = null, $table = null, $limit = null) {
        $counter                                                                            = false;
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
        $sort                                                                               = $this->getFields($data["sort"], $data["def"]["alias"]);
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

            $regs                                                                           = $this->getStorage(($data["service"] ? $data["service"] : $controller), $data["def"])->read(
                                                                                                ($where === true
                                                                                                    ? null
                                                                                                    : $where
                                                                                                )
                                                                                                , $select
                                                                                                , $sort
                                                                                                , $limit
                                                                                            );

            if(is_array($regs)) {
                if($regs["rawdata"]) {
                    $this->result = $regs["rawdata"];
                    $regs["keys"] = array_keys($regs["rawdata"]);
                }


                if($regs["exts"]) {
                    $this->data["exts"][$data["def"]["mainTable"]]                          = (array) $this->data["exts"][$data["def"]["mainTable"]] + $regs["exts"];
                    if($this->data["main"]["select"] /*&& !$this->data["main"]["where"]*/) {
                        $field_ext                                                          = $data["def"]["relationship"][$table_main]["external"];
                        $field_key                                                          = $data["def"]["relationship"][$table_main]["primary"];
                        if($field_key) {
                            $ids                                                                = array_keys($regs["exts"][$field_ext]);
                            if($ids) {
                                $this->data["main"]["where"][$field_key]                        = (count($ids) == 1
                                                                                                    ? $ids[0]
                                                                                                    : $ids
                                                                                                );
                                //if(!$data["runned"])                                            $this->getData(); //try main table by sub
                                //$sub_ids                                                      = array_keys($this->data["exts"][$this->data["main"]["def"]["mainTable"]][$field_ext]);
                                $sub_ids                                                        = $ids;
                            } elseif($regs === false) {
                                $this->data["main"]["where"][$field_key]                        = "0";
                            }
                        }
                    }
                }


                if(is_array($regs["keys"]) && count($regs["keys"])) {
                    $counter                                                                = count($regs["keys"]);
                    $table_name                                                             = $data["def"]["table"]["alias"];

                    if(!$table_rel)         //se è una maintable ma non anagraph reimposta l'external base es: doctors -> anagraph -> external
                        $field_ext                                                          = $data["def"]["relationship"][$table_main]["external"];

                    foreach($regs["keys"] AS $i => $id) {
                        $keys                                                               = null;
                        if($data["select"]["*"]) {
                            $result                                                         = (!$controller && !$table
                                                                                                ? $regs["result"][$i][$table_name]
                                                                                                : $regs["result"][$i]
                                                                                            );
                        } elseif($data["select_is_empty"]) {
                            $result                                                         = array();
                        } else {
                            $result                                                         = ($indexes
                                                                                                ? array_intersect_key($regs["result"][$i], $data["select"])
                                                                                                : $regs["result"][$i]
                                                                                            );
                        }


                        if($result) {
                            //triggera quando avviene la seguente casistica: anagraph --> anagraph_person dove anagraph_person.ID_anagraph = anagraph.ID
                            if($table_main && $data["def"]["relationship"][$table_main]["external"]) {
                                $field_ext                                                  = $data["def"]["relationship"][$table_main]["external"];

                                if($regs["exts"][$field_ext][$regs["result"][$i][$field_ext]]) {;
                                    $keys                                                   = array($regs["result"][$i][$field_ext]);
                                    $table_rel                                              = null;
                                }
                            }

                            if(!$keys) {
                                $keys                                                       = ($table_rel
                                                                                                ? array_keys($this->data["exts"][$table_rel][$field_ext])
                                                                                                : $this->data["exts"][$table_main][$field_ext][$id]
                                                                                            );
                            }

                            $ids                                                            = ($sub_ids
                                                                                                ? $sub_ids
                                                                                                : $keys
                                                                                            );

                            if(is_array($ids) && count($ids)) {
                                foreach($ids AS $id_primary) {
                                    $id_primary                                             = $this->ids_traversing($id_primary, $id);

                                    /*if(0 && $opt["limit"] == 1)
                                        $this->setResult($this->result[$id], $result);
                                    else*/
                                    if($table_rel) { //discende fino ad anagraph per fondere i risultati annidati esempio anagraph -> users -> tokens
                                        $root_ids = $this->data["exts"][anagraph::MAIN_TABLE][$field_key][$id_primary];
                                        if(is_array($root_ids) && count($root_ids) == 1) {
                                            $id_primary                                     = $root_ids[0];
                                        }
                                    }

                                    $this->setResult($this->result[$id_primary][$table_name], $result, (!$controller && !$table /* is main */));

                                    /*if($this->result[$id][$table_name]) {
                                        if($this->isAssocArray($this->result[$id][$table_name]))
                                            $this->result[$id][$table_name]                     = array("0" => $this->result[$id][$table_name]);

                                        $this->result[$id][$table_name][]                       = $result;
                                    } else {
                                        $this->result[$id][$table_name]                         = $result;
                                    }*/
                                }
                            } else {
                                $this->setResult($this->result[$id], $result, (!$controller && !$table /* is main */));
                                //$this->result[$id]                                            = $result;
                            }
                        }
                    }
                }
            } else {
                $this->isError($regs);
            }
        }

        return $counter;
    }

    function ids_traversing($id_primary, $id) {
        if($this->data["traversing"][$id_primary]) {
            $res                                                                            = $this->data["traversing"][$id_primary];
        } elseif(!$this->data["traversing"][$id]) {
            $this->data["traversing"][$id]                                                  = $id_primary;
            $res = $this->data["traversing"][$id];
        } else {
            $res                                                                            = $id_primary;
        }

        return $res;
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
        $setMainTableDefer                                                                  = null;

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
        $key                                                                                = null;
        $data                                                                               = (!$controller && !$table
                                                                                                ? $this->data["main"]
                                                                                                : $this->data["sub"][$controller][($table
                                                                                                    ? $table
                                                                                                    : $this->getMainTable($controller)
                                                                                                )]
                                                                                            );
        $storage                                                                            = $this->getStorage($controller, $data["def"]);
        $key_name                                                                           = $this->getFieldAlias(array_search("primary", $data["def"]["struct"]), $data["def"]["alias"]);

        if($data["insert"]) {
            $data["insert"]                                                                 = $this->getFields($data["insert"], $data["def"]["alias"]);
            if($data["where"])                                                              $data["where"] = $this->getFields($data["where"], $data["def"]["alias"]);
            if(!$data["where"])                                                             $data["where"] = $data["insert"];

            $regs                                                                           = $storage->read($data["where"], array($key_name => true));
            if(is_array($regs))
                $key                                                                        = $regs["keys"][0];
            else
                $this->isError($regs);

            if(!$key && !$this->isError()) {
                $regs                                                                       = $storage->insert($data["insert"], $data["def"]["table"]["name"]);
                if(is_array($regs)) {
                    $regs                                                                   = array_values($regs);
                    $key                                                                    = $regs[0]["keys"];
                } else {
                    $this->isError($regs);
                }



            }
        } elseif($data["set"] && !$data["where"]) {
            if($this->data["main"]["where"][$data["def"]["relationship"][$this->data["main"]["def"]["mainTable"]]["primary"]])  {
                $external_name = $data["def"]["relationship"][$this->data["main"]["def"]["mainTable"]]["external"];
                $primary_name = $data["def"]["relationship"][$this->data["main"]["def"]["mainTable"]]["primary"];
                if(!$data["def"]["struct"][$external_name]) {
                    if(!$this->data["main"]["where"][$external_name])                       $this->setMainIndexes();

                    $data["where"][$primary_name]                                           = $this->data["main"]["where"][$external_name];
                } else {
                    $data["where"][$external_name]                                          = $this->data["main"]["where"][$primary_name];
                }
            }

            if($data["where"]) {
                $regs                                                                       = $storage->update($data["set"], $data["where"], $data["def"]["table"]["name"]);
                if(is_array($regs)) {
                    $key                                                                    = $regs["keys"][0];
                } else {
                    $this->isError($regs);
                }
            }
        } elseif($data["set"] && $data["where"]) {
            $regs                                                                           = $storage->update($data["set"], $data["where"], $data["def"]["table"]["name"]);
            if(is_array($regs)) {
                $key                                                                        = $regs["keys"][0];
            } else {
                $this->isError($regs);
            }
        } elseif($data["where"] && !$data["insert"] && !$data["set"]) {
            $regs                                                                           = $storage->delete($data["where"], $data["def"]["table"]["name"]);
            if(is_array($regs)) {
                $key                                                                        = $regs["keys"][0];
            } else {
                $this->isError($regs);
            }
        } elseif($data["insert"] && $data["set"] && $data["where"]) {
            $regs                                                                           = $storage->write(
                                                                                                $data["insert"]
                                                                                                , array(
                                                                                                    "set" => $data["set"]
                                                                                                    , "where" => $data["where"]
                                                                                                )
                                                                                                , $data["def"]["table"]["name"]
                                                                                            );
            if(is_array($regs)) {
                $key                                                                        = $regs["keys"][0];
            } else {
                $this->isError($regs);
            }
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

        if($key)                                                                            $this->result["keys"][$data["def"]["table"]["alias"]] = $key;
    }

    private function setMainIndexes() {
        $anagraph                                                                           = new Anagraph();
        $anagraph->strict                                                                   = false;

        $res                                                                                = $anagraph->read(array_keys($this->data["main"]["def"]["indexes"]), $this->data["main"]["where"]);
        if(is_array($res))                                                                  $this->data["main"]["where"] = array_replace($this->data["main"]["where"], $res);
    }

    /**
     * @param $result
     * @param $entry
     */
    private function setResult(&$result, $entry, $replace = false) {
        if($result) {
            if($replace) {
                $result                                                                         = array_replace($result, $entry);
            } else {
                if($this->isAssocArray($result))
                    $result                                                                     = array("0" => $result);

                $result[]                                                                       = $entry;
            }
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

        //$this->service = "mol";
        if($this->services_by_data["last"] && count($this->services_by_data["services"]) == 1)
            $this->service                                                                  = $this->services_by_data["last"];

        //cambia il service se nella query non viene usata anagraph
        /*if($this->services_by_data["last"]) {
            if(count($this->services_by_data["services"]) == 1) {
                $this->service                                                              = $this->services_by_data["last"];
            } else if(count((array) $this->services_by_data["services"] > 1)
                        && count((array) $this->services_by_data["select"]) == 1
                        && count((array) $this->services_by_data["where"]) == 1
            ) {


                $subService                                                                 = $this->services_by_data["last"];
                $subTable                                                                   = $this->getMainTable($subService);
                $key_external                                                               = $this->data["sub"][$subService][$subTable]["def"]["relationship"][Anagraph::MAIN_TABLE]["external"];
                $key_primary                                                                = $this->data["sub"][$subService][$subTable]["def"]["relationship"][Anagraph::MAIN_TABLE]["primary"];
                if($this->data["sub"][$subService][$subTable]["def"]["struct"][$key_external]
                    && $this->data["main"]["where"][$key_primary]
                ) {
                    if(count($this->data["main"]["where"]) == 1) {
                           $this->service                                                      = $this->services_by_data["last"];
                           $this->data["sub"][$subService][$subTable]["where"][$key_external]  = $this->data["main"]["where"][$key_primary];
                        unset($this->data["main"]["where"]);
                    } else {
                        $this->isError("Read: " . "unexpected Double Relationship in primary => secondary table");
                    }
                }
            }
        }*/

        if(!($this->data["main"]["where"] || $this->data["main"]["select"] || $this->data["main"]["insert"]) && count($this->services) == 1) {
            $subService                                                                     = key($this->services);
            $subTable                                                                       = $this->getMainTable($subService);

            if($this->data["sub"][$subService][$subTable])
                $this->data["main"]                                                         = $this->data["sub"][$subService][$subTable];
            else
                $this->data["main"]["def"]                                                  = $this->getStruct($subTable, $subService);

            $this->data["main"]["service"]                                                  = $subService;
            unset($this->data["sub"][$subService][$subTable]);
            if(!count($this->data["sub"][$subService]))
                unset($this->data["sub"][$subService]);
            if(!count($this->data["sub"]))
                unset($this->data["sub"]);

        } else {
            $mainService                                                                    = $this->service;
            $mainTable                                                                      = $this->getMainTable($mainService);

            $this->data["main"]["def"]                                                      = $this->getStruct($mainTable, $mainService);
            $this->data["main"]["service"]                                                  = $mainService;
        }

        if(!$this->data["main"]["select"] && $data["select"]) {
            $key_name                                                                       = array_search("primary", $this->data["main"]["def"]["struct"]);
            $this->data["main"]["select"][$key_name]                                        = true;
            $this->data["main"]["select_is_empty"]                                          = true;
        }

        if($this->data["main"]["select"]["*"]) {
            $this->data["main"]["select"] = array_fill_keys(array_keys($this->data["main"]["def"]["struct"]), true);
        }

//Cms::getInstance("debug")->dump();

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
                $table                                                                      = null;
                $fIndex                                                                     = null;
                $service                                                                    = $this->service;
                if(is_numeric($key)) {
                    $key                                                                    = $alias;
                    if($scope != "insert" && $scope != "set")                               $alias = true;

                } elseif(is_null($alias)) {
                    $alias                                                                  = ($scope == "insert" || $scope == "set"
                                                                                                ? false
                                                                                                : true
                                                                                            );
                }

                $parts                                                                      = explode(".", $key);
                switch(count($parts)) {
                    case "4":
                        if($this::DEBUG) {
                            Cms::getInstance("debug")->dump("Wrong Format: " . $key);
                            exit;
                        }
                        break;
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

                $this->services_by_data["services"][$service]                               = true;
                $this->services_by_data[$scope][$service]                                   = true;
                if($scope == "select") {
                    $this->services_by_data["last"]                                         = $service;
                }
                if($fIndex === null || $fIndex < 0) {
                    $this->data["main"][$scope][$parts[abs($fIndex)]]                       = $alias;
                    continue;
                }

                if(!$this->data["sub"][$service][$table]["def"]) {
                    $this->data["sub"][$service][$table]["def"]                             = $this->getStruct($table, $service);
                }

                if(!$this->data["sub"][$service][$table]["def"]["struct"][$parts[$fIndex]]) {
                    if($scope == "select" && $parts[$fIndex] == "*") {
                        $this->data["sub"][$service][$table][$scope] = array_fill_keys(array_keys($this->data["sub"][$service][$table]["def"]["struct"]), true);
                    }
                    continue;
                }

                if($scope == "insert") {
                    $this->data["sub"][$service][$table]["insert"][$parts[$fIndex]]         = $alias;
                    $this->data["sub"][$service][$table]["where"][$parts[$fIndex]]          = $alias;
                } else {
                    $this->data["sub"][$service][$table][$scope][$parts[$fIndex]]           = $alias;
                }
            }

            //forza la discesa del modello se la query riguarda solo sub elements.


        } elseif($fields === true) {
            $this->data["main"][$scope] = $fields;
        }

        return true;
    }

    /**
     * @param array $fields
     * @param null $alias
     * @param null $indexes
     * @return array
     */
    private function getFields($fields = array(), $alias = null, &$indexes = null, $primary_key = null) {
        $res                                                                                = null;
	    if(is_array($fields) && count($fields)) {
            $res                                                                            = $fields;
            if(!$res["*"]) {
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
                        if (array_key_exists($new, $res)) {
                            $res[$old]                                                          = $res[$new];
                                                                                                unset($res[$new]);

                        }

                        if($fields[$old] && $indexes[$new])                                     unset($indexes[$new]);
                    }
                }
            }
        }

        if(!$res) {
            $res                                                                            = array_fill_keys(array_keys($indexes), true);
            if($primary_key)                                                                $res[$primary_key] = true;
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
    /*
    private function controller($service) {
        $controller                                                         = $this->getControllerName($service);
        if($this->controllers_rev[$controller]) {
            $driver                                                         = new $controller($this);
           // $db                                                            = $driver->getDevice();
        }
    }*/

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
        $this->services_by_data                                             = array();

        $this->isError("");
    }

    private function resolveResult() {
        if(is_array($this->result)) {
            if($this->strict || count($this->result) > 1) {
                $res                                                        = array_values($this->result);
            } else {
                $res                                                        = current($this->result);
                if (count($this->data["sub"][$this->data["main"]["service"]]) == 1 && count($res) == 1) {
                    $res                                                    = current($res);
                }
                if(count($res) == 1 && $res[$this->service]) {
                    $res                                                    = $res[$this->service];
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