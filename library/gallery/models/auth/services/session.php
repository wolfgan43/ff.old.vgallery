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

class authSession
{
    const SESSION_NAME                                          = SESSION_NAME;
    const SESSION_PATH                                          = SESSION_SAVE_PATH;
    const SESSION_STARTED                                       = "MOD_SECURITY_SESSION_STARTED";
    const COOKIE_SHARE                                          = MOD_SECURITY_SESSION_PERMANENT;

    const APPID                                                 = APPID;

    const DATA_USER                                             = "user_permission";
    const DATA_FF                                               = "__FF_SESSION__";



    private $auth                                               = null;
    private $domain                                             = null;

    /**
     * authSession constructor.
     * @param $auth
     */
    public function __construct($auth)
    {
        $this->auth                                             = $auth;
    }

    /**
     * @param null $opt
     * @return array
     */
    public function check($opt = null) {
        $session_valid                                          = false;
        if(defined(authSession::SESSION_STARTED)) {
            $session_valid                                      = true;
        } else {
            if($this->checkSession())
            {
                if(@session_start())
                    $session_valid                              = true;
            }
        }
        if($session_valid) {
            $data["FF"]                                         = $this->issetSession(authSession::DATA_FF);
            $data["user"]                                       = (array) $this->getSession(authSession::DATA_USER);

            if($opt["user"])                                    $res["user"] = $data["user"];

            if(is_array($opt["fields"]) && count($opt["fields"])) {
                foreach ($opt["fields"] AS $name => $asName) {
                    if($data["user"][$asName])                  $res[$asName] = $data["user"][$asName];
                }
            }

            $res["status"]                                      = "0";
            $res["error"]                                       = "";
        } else {
            $this->destroy();

            $res["status"]                                      = "404";
            $res["error"]                                       = "Invalid Session";

            if($opt["redirect"]) {
               // Cms::doRedirect("login");
            }

        }

        return $res;
    }

    /**
     * @param $ID
     * @param null $fields
     * @return string
     */
    public function create($ID, $opt = null) {
        $token = mod_security_create_session($ID); //$opt["fields"] da gestire
        $res = array(
            //... fields custom
            "token" => array(
                "name"      => ""
                , "expire"  => "-1"
            )
        );
        return $res;
    }

    /**
     * @param bool $cookie
     */
    public function destroy($cookie = true) {
        @session_unset();
        @session_destroy();

        if($cookie) {
            $this->cookie_destroy();
        }
    }

    /**
     * @param $name
     * @return bool
     */
    private function issetSession($name) {
        return isset($_SESSION[authSession::APPID . $name]);
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getSession($name) {
        return $_SESSION[authSession::APPID . $name];
    }

    /**
     * @param null $name
     * @return null|string
     */
    private function getSessionName($name = null) {
        static $isset                                           = null;

        if(!$name)                                              $name   = authSession::SESSION_NAME;
        if($isset != $name) {
            session_name($name);
            $isset                                              = $name;
        }

        return $name;
    }

    /**
     * @param null $path
     * @return array|false|null|string
     */
    private function getSessionPath($path = null) {
        static $isset                                           = null;

        if(!$path)                                              $path   = authSession::SESSION_PATH;
        if($isset != $path) {
            session_save_path($path);
            $isset                                              = $path;
        }

        return $path;
    }

    /**
     * @return bool
     */
    private function checkShareCookie() {
        return (authSession::COOKIE_SHARE === true
            ? true
            : false
        );
    }

    /**
     * @param null $id
     * @param null $path
     * @return bool
     */
    private function checkSession($id = null, $path = null) {
        if(!$id)                                                $id     = $this->getSessionId();
        if(!$path)                                              $path   = $this->getSessionPath();

        return file_exists(rtrim($path, "/") . "/sess_" . $id);
    }

    /**
     * @return mixed
     */
    private function getSessionId() {
        $session_name                                           = $this->getSessionName();
        return ($_REQUEST[$session_name]
            ? $_REQUEST[$session_name]
            : $_COOKIE[$session_name]
        );
    }

    /**
     * @return mixed|null
     */
    private function getPrimaryDomain() {
        if(!$this->domain) {
            $regs                                               = array();
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $_SERVER["HTTP_HOST"], $regs)) {
                $this->domain                                  = $regs['domain'];
            } else {
                $this->domain                                  = $_SERVER["HTTP_HOST"];
            }
        }

        return $this->domain;
    }

    /**
     * @param string $name
     */
    private function cookie_destroy($name = authSession::SESSION_NAME) { //_ut
        $sessionCookie                                          = session_get_cookie_params();
        setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $_SERVER["HTTP_HOST"], $sessionCookie['secure'], $sessionCookie["httponly"]);

        if($this->cookie_share_in_subdomains()) {
            $sessionCookie                                      = session_get_cookie_params();
            setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);
        }
    }

    /**
     * @return bool
     */
    private function cookie_share_in_subdomains() {
        $check_share                                            = $this->checkShareCookie();
        if($check_share)                                        session_set_cookie_params(0, '/', '.' . $this->getPrimaryDomain());

        return $check_share;
    }

}