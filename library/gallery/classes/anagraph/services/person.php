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
class anagraphPerson
{
	const TYPE                                              = "person";

	private $device                                         = null;
	private $anagraph                                       = null;
	private $services										= array(
																"nosql" 					=> null
																, "sql"						=> null
																, "fs" 						=> null
															);
	private $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "ANAGRAPH_PERSON_DATABASE_"
																	, "table"               => "anagraph_person"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			=> null
																	, "prefix"				=> "ANAGRAPH_PERSON_MONGO_DATABASE_"
																	, "table"               => "anagraph_person"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                => "/cache/anagraph/person"
																	, "name"                => array("url")
																	, "var"					=> null
																	)
															);
	private $struct											= array(
                                                                "name"                      => "string"
                                                                , "surname"                 => "string"
                                                                , "degree"                  => "string"
                                                                , "cell"                    => "string"
                                                                , "gender"                  => "string"
                                                                , "birthday"                => "date"
                                                                , "cf"                      => "string"
                                                                , "image"                   => "string"
                                                                , "short_desc"              => "string"
                                                                , "biography"               => "text"
                                                                , "cv"                      => "string"
															);
    private $relationship								    = array();
    private $indexes                                        = array();
    private $tables                                         = array();

	public function __construct($anagraph)
	{
		$this->anagraph = $anagraph;
        $this->anagraph->setConfig($this->connectors, $this->services, $this::TYPE);
	}
}