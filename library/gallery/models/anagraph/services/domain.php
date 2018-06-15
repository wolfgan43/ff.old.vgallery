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
class anagraphDomain
{
	const TYPE                                              = "domain";
	const MAIN_TABLE                                        = "domains";

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
																	, "prefix"				    => "ANAGRAPH_DOMAIN_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																)
																, "nosql"                       => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"    		    => null
																	, "name"       			    => null
																	, "prefix"				    => "ANAGRAPH_DOMAIN_MONGO_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																	)
																, "fs"                          => array(
																	"service"				    => "php"
																	, "path"                    => "/cache/anagraph/domains"
																	, "name"                    => array("name")
                                                                )
															);
    private static $struct								    = array(
	                                                            "domains" => array(
	                                                                "ID"                        => "primary"
                                                                    , "name"                    => "string"
                                                                    , "expire"                  => "number"
                                                                    , "status"                  => "number"
                                                                    , "created"                 => "number"
                                                                    , "ip"                      => "string"
                                                                    , "langs"                   => "string"
                                                                    , "version"                 => "string"
                                                                    , "last_update"             => "number"
                                                                    , "owner"                   => "number"
                                                                    , "scopes"                  => "string"
                                                                    , "secret"                  => "string"
                                                                    , "company_name"            => "string"
                                                                    , "company_description"     => "string"
                                                                    , "company_state"           => "string"
                                                                    , "company_province"        => "string"
                                                                    , "company_city"            => "string"
                                                                    , "company_email"           => "string"
                                                                    , "company_ID_place"        => "number" //todo: da fare la relationship con gmap coords

                                                                    //todo: da togliere
                                                                    , "server_csr_url"          => "string"
                                                                    , "server_csr_ip"           => "string"
                                                                    , "server_csr_protocol"     => "string"
                                                                    , "server_pkey_url"         => "string"
                                                                    , "server_pkey_ip"          => "string"
                                                                    , "server_pkey_protocol"    => "string"
                                                                    , "cert_expire"             => "number"
                                                                    , "cert_alg"                => "string"
                                                                    , "cert_id_length"          => "number"
                                                                    , "cert_key_length"         => "number"
                                                                    , "cert_precision"          => "number"
                                                                )
                                                                , "security" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "csr_url"                 => "string"
                                                                    , "csr_ip"                  => "string"
                                                                    , "csr_protocol"            => "string"
                                                                    , "pkey_url"                => "string"
                                                                    , "pkey_ip"                 => "string"
                                                                    , "pkey_protocol"           => "string"
                                                                    , "cert_expire"             => "number"
                                                                    , "cert_alg"                => "string"
                                                                    , "cert_id_length"          => "number"
                                                                    , "cert_key_length"         => "number"
                                                                    , "cert_precision"          => "number"
                                                                    , "token_expire"            => "number"
                                                                    , "token_type"              => "string"
                                                                )
                                                                , "access" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "name"                    => "string"
                                                                    , "host"                    => "string"
                                                                    , "user"                    => "string"
                                                                    , "password"                => "string"
                                                                    , "type"                    => "string"
                                                                    , "tables"                  => "string"
                                                                )
                                                                , "scopes" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "scope"                   => "string"
                                                                    , "is_default"              => "number"
                                                                    , "description"             => "text"
                                                                    , "special"                 => "number"
                                                                )
                                                                , "clients" => array(
                                                                    "client_id"                 => "string"
                                                                    , "client_secret"           => "string"
                                                                    , "redirect_uri"            => "string"
                                                                    , "grant_types"             => "string"
                                                                    , "ID_grant_type"           => "number"
                                                                    , "scope"                   => "string"
                                                                    , "description"             => "text"
                                                                    , "disable_csrf"            => "number"
                                                                    , "sso"                     => "number"
                                                                    , "url_site"                => "string"
                                                                    , "url_privacy"             => "string"
                                                                    , "json_only"               => "number"
                                                                    , "domains"                 => "string"
                                                                )
                                                                , "registration" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "ID_group"                => "number"
                                                                    , "anagraph_type"           => "string"
                                                                    , "token"                   => "number"
                                                                    , "activation"              => "string"
                                                                )
                                                                , "policy" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "ID_group"                => "number"
                                                                    , "groups"                  => "string"
                                                                    , "scopes"                  => "string"
                                                                )
                                                                , "policy_granted" => array(
                                                                    "ID"                        => "primary"
                                                                    , "ID_domain"               => "number"
                                                                    , "ID_user_trusted"         => "number"
                                                                    , "ID_user_shared"          => "number"
                                                                    , "client_id"               => "string"
                                                                    , "ID_device"               => "number"
                                                                    , "scopes"                  => "string"
                                                                    , "expire"                  => "number"
                                                                    , "created"                 => "number"
                                                                    , "last_update"             => "number"
                                                                )
															);
    private static $relationship							= array(
                                                                "domains"                       => array(
                                                                    "security"                  => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "registration"            => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "access"                    => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "scopes"                  => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "policy"                  => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                    , "policy_granted"          => array(
                                                                        "external"                  => "ID_domain"
                                                                    , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "security"                    => array(
                                                                    "domains"                   => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "registration"                => array(
                                                                    "domains"                   => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "access"                      => array(
                                                                    "domains"                   => array(
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
                                                                , "policy"                      => array(
                                                                    "domains"                   => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                                , "policy_granted"              => array(
                                                                    "domains"                   => array(
                                                                        "external"                  => "ID_domain"
                                                                        , "primary"                 => "ID"
                                                                    )
                                                                )
                                                            );
    private static $indexes                                 = array(
                                                                "security"                      => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "registration"                => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "access"                      => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "scopes"                      => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "policy"                      => array(
                                                                    "ID_domain"                 => "hardindex"
                                                                )
                                                                , "policy_granted"              => array(
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
                                                                , "security"                     => array(
                                                                    "name"                      => "cm_mod_security_domains_settings"
                                                                    , "alias"                   => "security"
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
                                                                , "registration"                => array(
                                                                    "name"                      => "cm_mod_security_domains_registration"
                                                                    , "alias"                   => "registration"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "policy"                      => array(
                                                                    "name"                      => "cm_mod_security_domains_policy"
                                                                    , "alias"                   => "policy"
                                                                    , "engine"                  => "InnoDB"
                                                                    , "crypt"                   => false
                                                                    , "pairing"                 => false
                                                                    , "transfert"               => false
                                                                    , "charset"                 => "utf8"
                                                                )
                                                                , "policy_granted"              => array(
                                                                    "name"                      => "cm_mod_security_domains_policy_granted"
                                                                    , "alias"                   => "permission"
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
                                                                    , "expiration_date"         => "expire"
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

