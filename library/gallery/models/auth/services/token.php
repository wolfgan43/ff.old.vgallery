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

class authToken
{
    const EXPIRE                                                = "31536000"; //1year

    private $auth                                               = null;


    /**
     * authToken constructor.
     * @param $auth
     */
    public function __construct($auth)
    {
        $this->auth                                             = $auth;
    }

    /**
     * @param $token
     * @param null $opt
     * @return array
     */
    public function check($token, $opt = null) {
        $type                                                   = ($opt["token"] && $opt["token"] !== true
                                                                    ? $opt["token"]
                                                                    : Auth::TOKEN_TYPE
                                                                );
        $select                                                 = ($opt["fields"]
                                                                    ? $opt["fields"]
                                                                    : array()
                                                                );
        $select[]                                               = "tokens.expire";

        $user                                                   = Anagraph::getInstance("access");
        $res                                                    = $user->read($select
                                                                , array(
                                                                    "tokens.token"                  => $token
                                                                    , "tokens.type"                 => $type
                                                                )
                                                                , null, 1);
        if($res) {
            if(is_array($res)) {
                if (!$res["token"]["expire"] || $res["token"]["expire"] >= time()) {
                    if($res["token"]["expire"] < 0) {
                        $setToken                                   = array(
                                                                        "tokens.token"              => $this->auth->createHash()
                                                                        , "tokens.expire"           => "-1"
                                                                    );
                    }
                    if($opt["refresh"]) {
                        if($opt["refresh"] === true) {
                            //todo: da fare procedura rigenerazione token
                            $setToken                               = array(
                                                                        "tokens.token"              => $this->auth->createHash()
                                                                        , "tokens.expire"           => authToken::EXPIRE
                                                                    );
                        } elseif(is_numeric($opt["refresh"])) {
                            $setToken                               = array(
                                                                        "tokens.expire"             => $opt["refresh"]
                                                                    );
                        } else {
                            $date 					                = DateTime::createFromFormat('U', ($res["token"]["expire"]
                                                                        ? $res["token"]["expire"]
                                                                        : time()
                                                                    ));
                            $date->modify("+" . ltrim($opt["refresh"], "+"));
                            $setToken                               = array(
                                                                        "tokens.expire"             => $date->getTimestamp()
                                                                    );
                        }
                    }

                    $return                                         = array_intersect_key($res, array_fill_keys($opt["fields"], true));

                    if($setToken) {
                        $user->update(
                            $setToken
                        , array(
                           "tokens.token"                           => $token
                            , "tokens.type"                         => $type
                        ));

                       if($setToken["tokens.token"]) {
                           $return["token"]                         = array(
                                                                       "name" => $setToken["tokens.token"]
                                                                       , "expire" => $setToken["tokens.expire"]
                                                                   );
                       }
                    }


                    $return["status"]                               = "0";
                    $return["error"]                                = "";
                } else {
                    $return["status"]                               = "401";
                    $return["error"]                                = "Wrong Fields";
                }
            } else {
                $return["status"]                                   = "400";
                $return["error"]                                    = "Wrong Fields";
            }
        } else {
            $return["status"]                                       = "404";
            $return["error"]                                        = "Token Not Found";
        }

        return $return;
    }


    /**
     * @param null $token
     * @param null $fields
     * @return null
     */
    public function getUser($token = null, $fields = null) {
        $user = null;


        return $user;
    }

    /**
     * @param $user
     * @param string $type
     */
    public function create($user, $type = "live")
    {

    }

    /**
     * @todo: da fare
     */
    public function destroy()
    {

    }

    /**
     * @param $ID_user
     * @param null $opt
     * @return array
     */
    public function get($ID_user, $opt = null) {
        $token                                                  = null;
        $user                                                   = Anagraph::getInstance("access");

        $select                                                 = ($opt["fields"]
                                                                    ? $opt["fields"]
                                                                    : array()
                                                                );
        $select["tokens.token"]                                 = "name";
        $select[]                                               = "tokens.expire";
        $auth                                                   = $user->read($select
                                                                    , array(
                                                                        "tokens.ID_user"        => $ID_user
                                                                        , "tokens.type"         => $opt["type"]
                                                                    )
                                                                    , $opt["sort"]
                                                                    , $opt["limit"]
                                                                );

        if($auth) {
            if (!$auth["expire"] || $auth["expire"] >= time()) {
                $res                                            = $auth;
            }
        } else {
            $res                                                = $this->create(array(
                                                                        "ID"                    => $ID_user
                                                                    )
                                                                    , $opt["type"]
                                                                );
        }

        return $res;
    }
}