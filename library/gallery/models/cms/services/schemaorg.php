<?php
/**
 *   VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @link https://bitbucket.org/cmsff/vgallery
 */

class cmsSchemaorg
{
	const TYPE                                              = "schemaorg";
    const JSON_TYPE                                         = "application/ld+json";

	private $device                                         = null;
	private $cms                                        	= null;
    protected $services                                     = array(
                                                                "fs"                    => null
                                                            );
    protected $controllers                                  = array(
    );
    protected $controllers_rev                              = null;
    protected $connectors										= array(
                                                                "sql"                   => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"   		=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "SCHEMAORG_DATABASE_"
                                                                    , "table"           => "schemaorg"
                                                                    , "key"             => "ID"
                                                                )
                                                                , "nosql"               => array(
                                                                    "host"          	=> null
                                                                    , "username"    	=> null
                                                                    , "password"    	=> null
                                                                    , "name"       		=> null
                                                                    , "prefix"			=> "SCHEMAORG_MONGO_DATABASE_"
                                                                    , "table"           => "schemaorg"
                                                                    , "key"             => "ID"
                                                                )
                                                                , "fs"                  => array(
                                                                    "service"			=> "php"
                                                                    , "path"            => "/cache/schemaorg"
                                                                    , "name"            => array("name")
                                                                )
															);
	private $struct											= array(
                                                               "type"                   => "string"
                                                                , "model"               => "array"
															);
	private $relationship                                   = array(
                                                            );
    private $indexes                                        = array(
                                                                    "type"              => "index"
                                                            );
    private $tables                                         = array(
                                                                "name"                  => "schemaorg"
                                                                , "engine"              => "InnoDB"
                                                                , "crypt"               => false
                                                                , "pairing"             => false
                                                                , "transfert"           => false
                                                                , "charset"             => "utf8"
                                                            );
    private $alias                                          = array(
                                                            );

    /**
     * cmsSchemaorg constructor.
     * @param $cms
     */
    public function __construct($cms)
	{
		$this->cms = $cms;

        $this->stats->setConfig($this->connectors, $this->services);
    }

    /**
     * @return null
     */
    public function getDevice()
	{
		return $this->device;
	}

    /**
     * @param null $type
     */
    public function get($type = null)
	{
		return;
	}

    /**
     * @param null $schema
     * @param null $type
     * @return mixed
     */
    public function set($schema = null, $type = null) {
	    if(is_array($schema)) {
            if(!$schema["@context"])
                $schema["@context"]                         = "http://schema.org";

            if($type)
                $schema["@type"]                            = $type;


            if($schema["@type"]) {
                $storage                                    = $this->getStorage();
                $model                                      = $storage->read(array("type" => $schema["@type"]));

                if(is_array($model) && count(array_intersect_key($model, $schema)) != count($model)) {
                    $this->cms->isError("Schema not Math che model");
                } else {

                    $json = json_encode($schema);
                    $this->cms->return                      = '<script defer="defer" type="' . cmsSchemaorg::JSON_TYPE . '">'
                                                                . $json
                                                                . '<script>';

                    if(!$model) {
                        foreach($schema AS $key => $value) {
                            $model[$key] = true;
                        }
                        $storage->insert(array("type" => $schema["@type"], "model" => $model));
                    }
                }
            } else {
                $this->cms->isError("Type Missing");
            }
        } else {
            $this->cms->isError("Schema must be Array");
        }

		return $this->cms->getResult();
	}

    /**
     * @return array
     */
    private function getStruct()
    {
        return array(
            "struct"                                    => $this->struct
            , "indexes"                                 => $this->indexes
            , "relationship"                            => $this->relationship
            , "table"                                   => $this->tables
            , "alias"                                   => $this->alias
            , "connectors"                              => false
        );
    }

    /**
     * @param null $service
     * @param null $struct
     * @return null|Storage
     */
    private function getStorage($service = null, $struct = null)
    {
        if($service) {
           /* $controller                                     = $this->getControllerName($service);
            $connectors                                     = ($this->controllers_rev[$controller]
                ? $controller::getConfig($this)
                : false
            );*/
        } else {
            $connectors                                     = $this->connectors;
        }
        if(!$struct)
            $struct                                         = $this->getStruct();

        return Storage::getInstance($connectors, $struct);
    }
}