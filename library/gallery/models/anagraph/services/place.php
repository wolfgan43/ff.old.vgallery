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
class place
{
	const TYPE                                              = "place";
	const MAIN_TABLE                                        = "place";

	private $device                                         = null;
	private $anagraph                                       = null;
    private static $services								= array(
																"sql" 					        => null
																, "nosql"						=> null
																, "fs" 						    => null
															);
    private static $connectors								= array(
																"sql"                           => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"   			    => null
																	, "name"       			    => null
																	, "prefix"				    => "PLACE_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																)
																, "nosql"                       => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"    		    => null
																	, "name"       			    => null
																	, "prefix"				    => "PLACE_MONGO_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																	)
																, "fs"                          => array(
																	"service"				    => "php"
																	, "path"                    => "/cache/place"
																	, "name"                    => array("name")
																	, "var"					    => null
																	)
															);
    private static $struct								    = array(
	                                                            "place" => array(
	                                                                "ID"                            => "primary"
                                                                    , "ID_state"                    => "number"
                                                                    , "ID_region"                   => "number"
                                                                    , "ID_province"                 => "number"
                                                                    , "ID_city"                     => "number"
                                                                    , "coord_title"                 => "string"
                                                                    , "coord_lat"                   => "string"
                                                                    , "coord_lng"                   => "string"
                                                                    , "coord_zoom"                  => "number"
                                                                    , "address"                     => "string"
                                                                    , "cap"                         => "string"
                                                                    , "title"                       => "string"
                                                                    , "description"                 => "string"
                                                                    , "cover"                       => "string"
                                                                    , "media"                       => "string"
                                                                )
                                                                , "city" => array(
                                                                    "ID"                            => "primary"
                                                                    , "ID_state"                    => "number"
                                                                    , "ID_region"                   => "number"
                                                                    , "ID_province"                 => "number"
                                                                    , "name"                        => "string"
                                                                    , "smart_url"                   => "string"
                                                                    , "istat_code"                  => "string"
                                                                    , "chief_town"                  => "number"
                                                                    , "cap"                         => "string"
                                                                    , "coord_title"                 => "string"
                                                                    , "coord_lat"                   => "string"
                                                                    , "coord_lng"                   => "string"
                                                                    , "coord_zoom"                  => "number"
                                                                )
                                                                , "province" => array(
                                                                    "ID"                            => "primary"
                                                                    , "ID_state"                    => "number"
                                                                    , "ID_region"                   => "number"
                                                                    , "name"                        => "string"
                                                                    , "smart_url"                   => "string"
                                                                    , "sigla"                       => "string"
                                                                    , "zone"                        => "char"
                                                                    , "coord_title"                 => "string"
                                                                    , "coord_lat"                   => "string"
                                                                    , "coord_lng"                   => "string"
                                                                    , "coord_zoom"                  => "number"
                                                                )
                                                                , "region" => array(
                                                                    "ID"                            => "primary"
                                                                    , "ID_state"                    => "number"
                                                                    , "name"                        => "string"
                                                                    , "smart_url"                   => "string"
                                                                    , "zone"                        => "char"
                                                                    , "coord_title"                 => "string"
                                                                    , "coord_lat"                   => "string"
                                                                    , "coord_lng"                   => "string"
                                                                    , "coord_zoom"                  => "number"
                                                                )
                                                                , "state" => array(
                                                                    "ID"                            => "primary"
                                                                    , "ID_currency"                 => "number"
                                                                    , "ID_lang"                     => "number"
                                                                    , "ID_zone"                     => "number"
                                                                    , "name"                        => "string"
                                                                    , "abbrevation"                 => "string"
                                                                    , "coord_title"                 => "string"
                                                                    , "coord_lat"                   => "string"
                                                                    , "coord_lng"                   => "string"
                                                                    , "coord_zoom"                  => "number"
                                                                    , "vat_enable"                  => "number"
                                                                    , "vat"                         => "number"
                                                                )
															);
    private static $relationship							= array(
                                                                "domains"                       => array(
                                                                    "access"                    => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "scopes"                  => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "access"                     => array(
                                                                    "domains"                  => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "scopes"                      => array(
                                                                    "domains"                   => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                            );
    private static $indexes                                 = array(
                                                                "access"                        => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "scopes"                      => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                            );
    private static $tables                                  = array(
                                                                "domains"                       => array(
                                                                    "name"                      => "cm_mod_security_domains"
                                                                    , "alias"                   => "domain"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "access"                     => array(
                                                                    "name"                      => "cm_mod_security_domains_access"
                                                                    , "alias"                   => "access"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "scopes"                      => array(
                                                                    "name"                      => "oauth_scopes"
                                                                    , "alias"                   => "scope"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "clients"                      => array(
                                                                    "name"                      => "oauth_clients"
                                                                    , "alias"                   => "client"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                            );
    private static $alias                                   = array(
                                                                "domains"                       => array(
                                                                    "nome"                      => "name"
                                                                    , "ip_address"              => "ip"
                                                                    , "creation_date"           => "created"
                                                                    , "expiration_date"         => "expiration"
                                                                    , "update_date"             => "last_update"
                                                                )
                                                                , "clients"                      => array(
                                                                    "client_id"                  => "ID"
                                                                )
                                                            );

    /**
     * anagraphAccess constructor.
     * @param $anagraph
     */
    public function __construct($anagraph)
	{
		//$this->anagraph                                     = $anagraph;
        $anagraph->setConfig($this->connectors, $this->services, $this::TYPE);
	}

    /**
     * @param $type
     * @return array
     */
    public static function getStruct($type) {
        return array(
            "struct"                                        => self::$struct[$type]
            , "indexes"                                     => self::$indexes[$type]
            , "relationship"                                => self::$relationship[$type]
            , "table"                                       => self::$tables[$type]
            , "alias"                                       => self::$alias[$type]
            , "connectors"                                  => false
            , "mainTable"                                   => self::MAIN_TABLE
        );
    }

    /**
     * @param $anagraph
     * @return array
     */
    public static function getConfig($anagraph) {
        $connectors                                         = self::$connectors;
        $services                                           = self::$services;

        $anagraph->setConfig($connectors, $services, self::TYPE);

        return $services;
    }
}

