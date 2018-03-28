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
																	, "prefix"				=> "ANAGRAPH_ACCESS_DATABASE_"
																	, "table"               => "cm_mod_security_users"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "ANAGRAPH_ACCESS_MONGO_DATABASE_"
																	, "table"               => "cm_mod_security_users"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"service"				=> "php"
																	, "path"                  => "/cache/anagraph/access"
																	, "name"                => array("url")
																	, "var"					=> null
																	)
															);
	private $struct											= array(
	                                                            "users" => array(
                                                                    "acl"                       => "number"
                                                                    , "expiration"              => "number"
                                                                    , "status"                  => "number"
                                                                    , "username"                => "string"
                                                                    , "username_slug"           => "string"
                                                                    , "email"                   => "string"
                                                                    , "tel"                     => "string"
                                                                    , "password"                => "password"
                                                                    , "password_old"            => "password"
                                                                    , "password_last_update"    => "number"
                                                                    , "avatar"                  => "string"
                                                                    , "created"                 => "number"
                                                                    , "last_update"             => "number"
                                                                    , "last_login"              => "number"
                                                                    , "ID_lang"                 => "number"
                                                                    , "SID"                     => "string"
                                                                    , "SID_expiration"          => "number"
                                                                )
                                                                , "groups" => array(
                                                                    "name"                      => "string"
                                                                    , "level"                   => "number"
                                                                )
                                                                , "device" => array(
                                                                    "client_id"                 => "string"
                                                                    , "name"                    => "string"
                                                                    , "type"                    => "string"
                                                                    , "ID_user"                 => "number"
                                                                )
															);
    private $relationship									= array();
    private $indexes                                        = array();
    private $tables                                         = array();

	public function __construct($anagraph)
	{
		$this->anagraph = $anagraph;
        $this->anagraph->setConfig($this->connectors, $this->services, $this::TYPE);
	}
}