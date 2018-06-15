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
    const TYPE                                                  = "live";

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
                                                                    : $this::TYPE
                                                                );
       /*$select                                                 = ($opt["fields"]
                                                                    ? $opt["fields"]
                                                                    : array()
                                                                );*/

        $select                                                 = array(
                                                                    "tokens.token"                  => "name"
                                                                    , "tokens.expire"
                                                                );
        if($opt["fields"])                                      $select = $select + $opt["fields"];
        if($opt["user"])                                        $select[] = "users.*";
        $return                                                 = Anagraph::getInstance("access")->read($select
                                                                    , array(
                                                                        "tokens.token"              => $token
                                                                        , "tokens.type"             => $type
                                                                    ), null, 1
                                                                );

        if(is_array($return)) {
            $t                                                  = ($return["token"]
                                                                    ? $return["token"]
                                                                    : $return
                                                                );
            if($t["expire"] <= 0  || $t["expire"] >= time()) {
                //$res                                        = array_intersect_key($return, array_fill_keys($opt["fields"], true));

                if($t["expire"] < 0 && $opt["refresh"] === null)
                    $opt["refresh"]                             = "-1";

                if($opt["refresh"] !== null) {
                    $res                                        = $this->refresh($token, $opt["refresh"], $type);
                } else {
                    $res                                        = array("token" => $t);
                    $res["status"]                              = "0";
                    $res["error"]                               = "";
                }

                if($opt["user"])                                $res["user"] = $return["user"];

                if($opt["fields"] && is_array($res) && $res["status"] === "0") {
                    foreach ($opt["fields"] AS $name => $asName) {
                        if($return[$asName])
                            $res[$asName] = $return[$asName];
                    }
                }
            } else {
                $res["status"]                                  = "401";
                $res["error"]                                   = "Token Expired";
            }
        } elseif(!$return) {
            $res["status"]                                      = "404";
            $res["error"]                                       = "Token Not Found";
        } else {
            $res["status"]                                      = "500";
            $res["error"]                                       = $return;
        }

        return $res;
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
    public function create($key = null, $app_id = Auth::APPID)
    {
        if(!$key)                                               $key = time();

        return sha1($app_id . $key);
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
        $type                                                   = ($opt["token"] && $opt["token"] !== true
                                                                    ? $opt["token"]
                                                                    : $this::TYPE
                                                                );
       /* $select                                                 = ($opt["fields"]
                                                                    ? $opt["fields"]
                                                                    : array()
                                                                );*/
        $select                                                 = array(
                                                                    "tokens.token"              => "name"
                                                                    , "tokens.expire"
                                                                );

        $token                                                  = Anagraph::getInstance("access")->read($select
                                                                    , array(
                                                                        "tokens.ID_user"        => $ID_user
                                                                        , "tokens.type"         => $type
                                                                    )

                                                                );

        if(is_array($token)) {
            if ($token["expire"] <= 0 || $token["expire"] >= time()) {
                if($token["expire"] < 0 && !$opt["refresh"])
                    $opt["refresh"]                             = "-1";

                $res["status"]                                  = "0";
                $res["error"]                                   = "";

                $res                                            = $res + ($opt["refresh"] !== null
                                                                    ? $this->refresh($token["name"], $opt["refresh"], $type)
                                                                    : array("token" => $token)
                                                                );
            } else {
                $res                                            = array("token" => $token);
                $res["status"]                                  = "401";
                $res["error"]                                   = "Token Expired";
            }
        } elseif(!$token) {
            if($opt["token"]) {
                $insert                                         = array(
                                                                    "tokens.ID_user"    => $ID_user
                                                                    , "tokens.type"     => $type
                                                                    , "tokens.token"    => $this->create(Auth::APPID . "-" . $ID_user . "-" . $type)
                                                                    , "tokens.expire"   => $this::EXPIRE
                                                                );
                $result                                         = Anagraph::getInstance("access")->insert($insert);
                if(is_array($result)) {
                    $res["token"]                               = array(
                                                                    "name"              => $insert["tokens.token"]
                                                                    , "expire"          => $insert["tokens.expire"]
                                                                );
                    $res["status"]                              = "0";
                    $res["error"]                               = "";
                } else {
                    $res["status"]                              = "500";
                    $res["error"]                               = $result;
                }
            } else {
                $res["status"]                                  = "403";
                $res["error"]                                   = "Token not Found. Unable to Create.";
            }
        } else {
            $res["status"]                                      = "500";
            $res["error"]                                       = $token;
        }

        /*if($opt["fields"] && $ID_user && is_array($res) && $res["status"] === "0") {
            $user                                               = Anagraph::getInstance()->read(
                                                                    $opt["fields"]
                                                                    , array(
                                                                        "ID_user"        => $ID_user
                                                                    )

                                                                );
            if(is_array($user))                                 $res = array_replace($user, $res);
        }*/

        return $res;
    }

    public function refresh($token, $expire = authToken::EXPIRE, $type = AuthToken::TYPE) {
        $where                                                  = array(
                                                                    "tokens.token"                  => $token
                                                                    , "tokens.type"                 => $type
                                                                );

        if($expire < 0) {
            $set                                                = array(
                                                                    "tokens.token"              => $this->create()
                                                                    , "tokens.expire"           => "-1"
                                                                );
        } elseif($expire === true) {
            $set                                                = array(
                                                                    "tokens.token"              => $this->create()
                                                                    , "tokens.expire"           => time() + authToken::EXPIRE
                                                                );
        } elseif(is_numeric($expire)) {
            $set                                                = array(
                                                                    "tokens.expire"             => $expire
                                                                );
        } elseif($expire && is_string($expire)) {
            $date 					                            = DateTime::createFromFormat('U', $expire);
            $date->modify("+" . ltrim($expire, "+"));
            $set                                                = array(
                                                                    "tokens.expire"             => $date->getTimestamp()
                                                                );
        }

        $result                                                 = Anagraph::getInstance("access")->update(
                                                                    $set
                                                                    , $where
                                                                );

        if(!$result) {
            $res["token"]                                       = array(
                                                                    "name" => ($set["tokens.token"]
                                                                        ? $set["tokens.token"]
                                                                        : $token
                                                                    )
                                                                    , "expire" => ($set["tokens.expire"]
                                                                        ? $set["tokens.expire"]
                                                                        : $expire
                                                                    )
                                                                );
            $res["status"]                                      = "0";
            $res["error"]                                       = "";
        } else {
            $res["status"]                                      = "500";
            $res["error"]                                       = $result;
        }

        return $res;
    }
}