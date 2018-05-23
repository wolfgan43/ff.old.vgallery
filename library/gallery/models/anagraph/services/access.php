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
class anagraphAccess
{
	const TYPE                                              = "access";
	const MAIN_TABLE                                        = "users";

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
																	, "prefix"				    => "ANAGRAPH_ACCESS_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																)
																, "nosql"                       => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"    		    => null
																	, "name"       			    => null
																	, "prefix"				    => "ANAGRAPH_ACCESS_MONGO_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																	)
																, "fs"                          => array(
																	"service"				    => "php"
																	, "path"                    => "/cache/anagraph/access"
																	, "name"                    => array("url")
																	, "var"					    => null
																	)
															);
    private static $struct								    = array(
	                                                            "users" => array(
	                                                                "ID"                        => "primary"
                                                                    , "acl"                     => "number"
                                                                    , "expire"                  => "number"
                                                                    , "status"                  => "number"
                                                                    , "username"                => "string"
                                                                    , "username_slug"           => "string"
                                                                    , "email"                   => "string"
                                                                    , "tel"                     => "string"
                                                                    , "password"                => "string:inPassword"
                                                                    , "password_old"            => "string:inPassword"
                                                                    , "password_last_update"    => "number"
                                                                    , "avatar"                  => "string"
                                                                    , "created"                 => "number"
                                                                    , "last_update"             => "number"
                                                                    , "last_login"              => "number"
                                                                    , "ID_lang"                 => "number"
                                                                    , "SID"                     => "string"
                                                                    , "SID_expire"              => "number"
                                                                )
                                                                , "groups" => array(
                                                                    "ID"                        => "primary"
                                                                    , "name"                    => "string"
                                                                    , "level"                   => "number"
                                                                )
                                                                , "devices" => array(
                                                                    "client_id"                 => "string"
                                                                    , "name"                    => "string"
                                                                    , "type"                    => "string"
                                                                    , "ID_user"                 => "number"
                                                                )
                                                                , "tokens" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_user"                 => "number"
                                                                    , "type"                    => "string"
                                                                    , "token"                   => "string"
                                                                    , "expire"                  => "number"
                                                                    , "refresh_token"           => "number"
                                                                    , "ID_remote"               => "number"

                                                                )
															);
    private static $relationship							= array(
                                                                "users"                         => array(
                                                                    "acl"                       => array(
                                                                        "tbl"                       => "groups"
                                                                        , "key"                     => "ID"
                                                                    )
                                                                    , Anagraph::TYPE              => array(
                                                                        "external"                  => "ID_user"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "tokens"                  => array(
                                                                        "external"                  => "ID_user"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "devices"                     => array(
                                                                    "users"                     => array(
                                                                        "external"                  => "ID_user"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "tokens"                      => array(
                                                                    "users"                     => array(
                                                                        "external"                  => "ID_user"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "groups"                      => array(
                                                                    anagraphAccess::MAIN_TABLE  => array(
                                                                        "external"                  => "acl"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                            );
    private static $indexes                                 = array(
                                                                "users"                         => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "devices"                     => array(
                                                                    "ID_user"                   => "hardindex"
                                                                )
                                                                , "tokens"                      => array(
                                                                    "ID_user"                   => "hardindex"
                                                                )
                                                            );
    private static $tables                                  = array(
                                                                "users"                         => array(
                                                                    "name"                      => "cm_mod_security_users"
                                                                    , "alias"                   => "user"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "groups"                      => array(
                                                                    "name"                      => "cm_mod_security_groups"
                                                                    , "alias"                   => "group"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "devices"                     => array(
                                                                    "name"                      => "devices"
                                                                    , "alias"                   => "device"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "tokens"                      => array(
                                                                    "name"                      => "cm_mod_security_token"
                                                                    , "alias"                   => "token"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                            );
    private static $alias                                   = array(
                                                                "users"                         => array(
                                                                    "ID_languages"              => "ID_lang"
                                                                    , "ID_domains"              => "ID_domain"
                                                                    , "ID_primary_gid"          => "acl"
                                                                    , "expiration"              => "expire"
                                                                )
                                                                , "groups"                      => array(
                                                                    "gid"                       => "ID"
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

