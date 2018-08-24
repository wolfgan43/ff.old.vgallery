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
class anagraphMol
{
	const TYPE                                              = "mol";
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
                                                                    , "cell"                            => "string"
                                                                    , "n_iscrizione_albo"               => "string"
                                                                    , "provincia_iscrizione_albo"       => "number"
                                                                    , "activity"                        => "string"
                                                                    , "verificato"                      => "number"
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
                                                                    , "newsletter"                      => "number"
                                                                    , "expert"                          => "number"
                                                                    , "bachelor_year"                   => "string"
                                                                    , "sesso"                           => "string"
                                                                    , "data_nascita"                    => "date"
                                                                    , "website"                         => "string"
                                                                    , "short_descrption_mol"            => "text"
                                                                    , "curriculum_mol"                  => "text"
                                                                    , "facebook"                        => "string"
                                                                    , "google_plus"                     => "string"
                                                                    , "twitter"                         => "string"
                                                                    , "linkedin"                        => "string"
                                                                    , "last_request"                    => "number"
                                                                    , "complete"                        => "number"
                                                                )
                                                                , "studi" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_anagraph"                     => "number"
                                                                    , "ID_type"                         => "number"
                                                                    , "name"                            => "string"
                                                                    , "ID_place"                        => "number"
                                                                    , "note_aggiuntive"                 => "text"
                                                                    , "website"                         => "string"
                                                                    , "prenotazioni_online"             => "boolean"
                                                                    , "struttura"                       => "boolean"
                                                                    , "address"                         => "string"
                                                                    , "cap"                             => "string"
                                                                    , "city"                            => "string"
                                                                    , "province"                        => "string"
                                                                    , "telephone"                       => "string"
                                                                    , "fax"                             => "string"
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
                                                                , "article" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_type"                         => "number"
                                                                    , "ID_vgallery"                     => "number"
                                                                    , "is_dir"                          => "number"
                                                                    , "last_update"                     => "number"
                                                                    , "name"                            => "string"
                                                                    , "owner"                           => "number"
                                                                    , "parent"                          => "string"
                                                                    , "visible"                         => "number"
                                                                    , "tags"                            => "string"
                                                                    , "cats"                            => "string"
                                                                    , "meta_description"                => "number"
                                                                    , "meta_title"                      => "string"
                                                                    , "meta_robots"                     => "string"
                                                                    , "meta_canonical"                  => "string"
                                                                    , "meta"                            => "text"
                                                                    , "permalink"                       => "string"
                                                                    , "created"                         => "number"
                                                                    , "published_at"                    => "number"
                                                                )
                                                                , "question" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_doctor"                       => "number"
                                                                    , "doctor_dest"                     => "number"
                                                                    , "ID_user"                         => "number"
                                                                    , "title"                           => "string"
                                                                    , "question"                        => "text"
                                                                    , "categories"                      => "string"
                                                                    , "automatic_categories"            => "string"
                                                                    , "answered"                        => "number"
                                                                    , "last_update"                     => "number"
                                                                    , "insert_date"                     => "number"
                                                                    , "date_publishing"                 => "number"
                                                                    , "smart_url"                       => "string"
                                                                    , "public"                          => "number"
                                                                    , "abstract"                        => "string"
                                                                    , "approfondimento"                 => "text"
                                                                    , "approfondimento_top"             => "text"
                                                                    , "ready"                           => "number"
                                                                    , "meta_robots"                     => "string"
                                                                    , "referef"                         => "string"
                                                                    , "premium"                         => "number"
                                                                    , "main_content"                    => "string"
                                                                    , "hidden_question"                 => "number"
                                                                )
                                                                , "blog" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_doctor"                       => "number"
                                                                    , "title"                           => "string"
                                                                    , "smart_url"                       => "string"
                                                                    , "abstract"                        => "text"
                                                                    , "text"                            => "text"
                                                                    , "status"                          => "string"
                                                                    , "last_update"                     => "number"
                                                                    , "date_publishing"                 => "number"
                                                                    , "tags"                            => "string"
                                                                    , "date_insert"                     => "number"
                                                                    , "cover"                           => "string"
                                                                    , "meta_robots"                     => "string"
                                                                    , "canonical"                       => "string"
                                                                ), "roche" => array(
                                                                    "ID"                                => "primary"
                                                                    , "ID_pm"                           => "number"
                                                                    , "uci_roche"                       => "string"
                                                                    , "tvf_roche"                       => "string"
                                                                    , "from_roche"                      => "number"
                                                                    , "from_pm"                         => "number"
                                                                    , "roche_term_condition"            => "string"
                                                                    , "pm_term_condition"               => "number"
                                                                    , "created"                         => "number"
                                                                    , "term_condition_time"             => "number"
                                                                    , "box_view"                        => "number"
                                                                    , "first_export"                    => "number"
                                                                    , "indirizzo_nome"                  => "string"
                                                                    , "indirizzo_citta"                 => "string"
                                                                    , "indirizzo_province"              => "string"
                                                                    , "indirizzo_citta_slug"            => "string"
                                                                    , "indirizzo_province_slug"         => "string"
                                                                    , "indirizzo_region"                => "string"
                                                                    , "indirizzo_region_slug"           => "string"
                                                                    , "address"                         => "string"
                                                                    , "cap"                             => "string"
                                                                    , "lat"                             => "string"
                                                                    , "lng"                             => "string"
                                                                )
															);
    private static $relationship							= array(
                                                                "doctors" => array(
                                                                    "ID_anagraph"               => array(
                                                                        "tbl"                           => "anagraph"
                                                                        , "key"                         => "ID"
                                                                    )
                                                                    , "studi"                   => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                    , "publishing"              => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                    , "request"                 => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                    , "request_confirmed"       => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                    , Anagraph::MAIN_TABLE      => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "studi" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                    , "place"                   => array(
                                                                        "external"                      => "ID_place"
                                                                        , "primary"                     => "ID"
                                                                    )
                                                                )
                                                                , "publishing" => array(
                                                                   "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                )
                                                                , "request" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                )
                                                                , "request_confirmed" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                )
                                                                , "article" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "owner"
                                                                    )
                                                                )
                                                                , "question" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                )
                                                                , "blog" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_anagraph"
                                                                    )
                                                                )
                                                                , "roche" => array(
                                                                    "doctors"        => array(
                                                                        "external"                      => "ID_anagraph"
                                                                        , "primary"                     => "ID_pm"
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
                                                                , "article" => array(
                                                                    "owner"                             => "hardindex"
                                                                )
                                                                , "question" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "blog" => array(
                                                                    "ID_anagraph"                       => "hardindex"
                                                                )
                                                                , "roche" => array(
                                                                    "ID_pm"                       => "hardindex"
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
                                                                , "article" => array(
                                                                    "name"                              => "vgallery_nodes"
                                                                    , "alias"                           => "article"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "question" => array(
                                                                    "name"                              => "cm_mod_pm_question"
                                                                    , "alias"                           => "question"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "blog" => array(
                                                                    "name"                              => "cm_mod_blog"
                                                                    , "alias"                           => "blog"
                                                                    , "engine"                          => "InnoDB"
                                                                    , "crypt"                           => false
                                                                    , "pairing"                         => false
                                                                    , "transfert"                       => false
                                                                    , "charset"                         => "utf8"
                                                                )
                                                                , "roche" => array(
                                                                    "name"                              => "cm_mod_pm_roche_campaign"
                                                                    , "alias"                           => "roche"
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

