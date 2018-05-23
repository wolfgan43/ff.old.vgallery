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
class doctorStudi
{
	const TYPE                                              = "studi";
	const MAIN_TABLE                                        = "studi";

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
																	, "prefix"				    => "STUDI_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																)
																, "nosql"                       => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"    		    => null
																	, "name"       			    => null
																	, "prefix"				    => "STUDI_MONGO_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																	)
																, "fs"                          => array(
																	"service"				    => "php"
																	, "path"                    => "/cache/studi"
																	, "name"                    => array("name")
																	, "var"					    => null
																	)
															);
    private static $struct								    = array(
	                                                            "studi" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "ID_type"                         => "number"
                                                                    , "name"                            => "string"
                                                                    , "ID_place"                        => "number"
                                                                    , "note_aggiuntive"                 => "text"
                                                                    , "website"                         => "string"
                                                                    , "prenotazioni_online"             => "number"
                                                                    , "struttura"                       => "number"
                                                                    , "smart_url"                       => "string"
                                                                )
                                                                , "contatti" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_struttura"                    => "number"
                                                                    , "type"                            => "string"
                                                                    , "value"                           => "string"
                                                                )
                                                                , "doctor_studio" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_studio"                       => "number"
                                                                    , "ID_doctor"                       => "number"
                                                                    , "name"                            => "string"
                                                                    , "surname"                         => "string"
                                                                    , "email"                           => "string"
                                                                    , "confirmed"                       => "number"
                                                                    , "created"                         => "number"
                                                                    , "accepted"                        => "number"
                                                                    , "last_update"                     => "number"
                                                                    , "from_struttura"                  => "number"
                                                                )
                                                                , "documentazione" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_struttura"                    => "number"
                                                                    , "file"                            => "string"
                                                                )
                                                                , "macchinari" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_struttura"                    => "number"
                                                                    , "name"                            => "string"
                                                                )
                                                                , "studi_rel_nodes" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_studio"                       => "number"
                                                                    , "prestazioni"                     => "string"
                                                                    , "description"                     => "text"
                                                                    , "subtitle"                        => "string"
                                                                    , "logo"                            => "string"
                                                                    , "cover"                           => "string"
                                                                    , "experties"                       => "string"
                                                                    , "facebook"                        => "string"
                                                                    , "twitter"                         => "string"
                                                                    , "linkedin"                        => "string"
                                                                    , "gplus"                           => "string"
                                                                    , "gallery"                         => "string"
                                                                    , "approved"                        => "number"
                                                                    , "visible"                         => "number"
                                                                    , "wait_publishing"                 => "number"
                                                                )
                                                                , "orari" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_studio"                       => "number"
                                                                    , "timeStart"                       => "time"
                                                                    , "timeEnd"                         => "time"
                                                                )
															);
    private static $relationship							= array(
                                                                "studi" => array(
                                                                    "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "place"                   => array(
                                                                        "external"                      => "ID_place"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "contatti" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_struttura"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "doctor_studio" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_studio"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "documentazione" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_struttura"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "macchinari" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_struttura"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "studi_rel_nodes" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_studio"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "orari" => array(
                                                                    "studi"                   => array(
                                                                        "external"                      => "ID_studio"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                            );
    private static $indexes                                 = array(
                                                                "studi" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                    , "ID_place"                        => "hardindex"
                                                                )
                                                                , "contatti" => array(
                                                                    "ID_struttura"                       => "hardindex"
                                                                )
                                                                , "doctor_studio" => array(
                                                                    "ID_studio"                       => "hardindex"
                                                                    , "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "documentazione" => array(
                                                                    "ID_struttura"                       => "hardindex"
                                                                )
                                                                , "macchinari" => array(
                                                                    "ID_struttura"                       => "hardindex"
                                                                )
                                                                , "studi_rel_nodes" => array(
                                                                    "ID_studio"                       => "hardindex"
                                                                )
                                                                , "orari" => array(
                                                                    "ID_studio"                       => "hardindex"
                                                                )
                                                            );
    private static $tables                                  = array(
                                                                "studi" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi"
                                                                    , "alias"                           => "doctor"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "contatti" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_contatti"
                                                                    , "alias"                           => "contatti"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "doctor_studio" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_doctor"
                                                                    , "alias"                           => "doctor_studio"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "documentazione" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_documentazione"
                                                                    , "alias"                           => "documentazione"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "macchinari" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_macchinari"
                                                                    , "alias"                           => "macchinari"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "studi_rel_nodes" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_nodes"
                                                                    , "alias"                           => "studi_rel_nodes"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "orari" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi_rel_orari"
                                                                    , "alias"                           => "orari"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                            );
    private static $alias                                   = array();

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

