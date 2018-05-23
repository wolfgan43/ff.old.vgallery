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
class anagraphDoctor
{
	const TYPE                                              = "doctor";
	const MAIN_TABLE                                        = "doctors";

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
																	, "prefix"				    => "ANAGRAPH_DOCTOR_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																)
																, "nosql"                       => array(
																	"host"          		    => null
																	, "username"    		    => null
																	, "password"    		    => null
																	, "name"       			    => null
																	, "prefix"				    => "ANAGRAPH_DOCTOR_MONGO_DATABASE_"
																	, "table"                   => null
																	, "key"                     => "ID"
																	)
																, "fs"                          => array(
																	"service"				    => "php"
																	, "path"                    => "/cache/anagraph/doctor"
																	, "name"                    => array("name")
																	, "var"					    => null
																	)
															);
    private static $struct								    = array(
	                                                            "doctors" => array(
	                                                                "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "anno_laurea"                     => "number"
                                                                    , "n_iscrizione_albo"               => "string"
                                                                    , "provincia_iscrizione_albo"       => "number"
                                                                    , "activity"                        => "number"
                                                                    , "verificato"                      => "number"
                                                                    , "short_description"               => "text"
                                                                    , "curriculum"                      => "text"
                                                                    , "espertoin"                       => "string"
                                                                    , "question_tags"                   => "string"
                                                                    , "mol_tel"                         => "string"
                                                                    , "mol_cell"                        => "string"
                                                                    , "mol_city"                        => "number"
                                                                    , "mol_province"                    => "number"
                                                                    , "pro_subject"                     => "string"
                                                                    , "medsurf_dec"                     => "string"
                                                                    , "premium"                         => "number"
                                                                    , "data_premium"                    => "date"
                                                                    , "nome_pubmed"                     => "string"
                                                                    , "premium_sponsor"                 => "string"
                                                                    , "mail_er"                         => "number"
                                                                )
                                                                , "studi" => array(
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
                                                                , "publishing" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "title"                           => "string"
                                                                    , "description"                     => "text"
                                                                    , "link"                            => "string"
                                                                    , "type"                            => "string"
                                                                )
                                                                , "request" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "categories"                      => "string"
                                                                    , "created"                         => "number"
                                                                )
                                                                , "request_confirmed" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "categories"                      => "string"
                                                                    , "created"                         => "number"
                                                                )
															);
    private static $relationship							= array(
                                                                "doctors" => array(
                                                                    "studi"                     => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "publishing"              => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "request"                 => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "request_confirmed"       => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "studi" => array(
                                                                    "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                    , "place"                   => array(
                                                                        "external"                      => "ID_place"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "publishing" => array(
                                                                    "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "request" => array(
                                                                    "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "request_confirmed" => array(
                                                                    "doctors"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                            );
    private static $indexes                                 = array(
                                                                "doctors" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "studi" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                    , "ID_place"                        => "hardindex"
                                                                )
                                                                , "publishing" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "request" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "request_confirmed" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                            );
    private static $tables                                  = array(
                                                                "doctors" => array(
                                                                    "name"                              => "cm_mod_pm_doctor"
                                                                    , "alias"                           => "doctor"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "studi" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_studi"
                                                                    , "alias"                           => "studi"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "publishing" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_publishing"
                                                                    , "alias"                           => "publishing"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "request" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_request"
                                                                    , "alias"                           => "request"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "request_confirmed" => array(
                                                                    "name"                              => "cm_mod_pm_doctor_request_confirm"
                                                                    , "alias"                           => "request_confirmed"
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

