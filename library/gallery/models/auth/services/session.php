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

if(!defined("SESSION_NAME"))                                define("SESSION_NAME", false);
if(!defined("SESSION_SAVE_PATH"))                           define("SESSION_SAVE_PATH", false);

if(!defined("MOD_SECURITY_SESSION_PERMANENT"))             define("MOD_SECURITY_SESSION_PERMANENT", true);
if(!defined("MOD_SECURITY_COOKIE_SHARE"))                  define("MOD_SECURITY_COOKIE_SHARE", true);

if(!defined("MOD_SEC_GUEST_GROUP_ID"))                     define("MOD_SEC_GUEST_GROUP_ID", "2");
if(!defined("MOD_SEC_GUEST_GROUP_NAME"))                   define("MOD_SEC_GUEST_GROUP_NAME", "guests");

class authSession
{
    const SESSION_NAME                                          = SESSION_NAME;
    const SESSION_PATH                                          = SESSION_SAVE_PATH;
    const COOKIE_PERMANENT                                      = MOD_SECURITY_SESSION_PERMANENT;
    const COOKIE_SHARE                                          = MOD_SECURITY_COOKIE_SHARE;
//todo: da implementare CSRF_PROTECTION
    const APPID                                                 = APPID;

    const GUEST_GROUP_ID                                        = MOD_SEC_GUEST_GROUP_ID;
    const GUEST_GROUP_NAME                                      = MOD_SEC_GUEST_GROUP_NAME;

    const DATA_USER                                             = "auth";
    const DATA_CSRF                                             = "__CSRF__";



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
        if(Auth::isLogged()) {
            $session_valid                                      = true;
        } else {
            if($this->checkSession()) {
                if(@session_start()) {
                    $session_valid                              = true;
                }
            } else {
                $session_valid                                  = null;
            }
        }

        $csrf                                                   = $this->env(authSession::DATA_CSRF);

        if($session_valid && $csrf) {
            if(!Auth::password(null, $csrf)) {
                $session_valid                                  = false;
            }
        }

        if($session_valid) {
            $anagraph                                           = $this->env(authSession::DATA_USER);

            /**
             * Set islogged
             */
            if(Auth::isLogged($anagraph["user"]["acl"])) {
                if($opt["user"])                                    { $res["user"] = $anagraph["user"]; }

                $res["status"]                                      = "0";
                $res["error"]                                       = "";
            } else {
                $this->destroy();

                $res["status"]                                      = "401";
                $res["error"]                                       = "Insufficent Permission";
            }
        } elseif($session_valid === false) {
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
    public function create($ID, $domain = null, $opt = null) {
        $this->sessionPath();
        $this->sessionName();
//print_r($opt);

        if($opt["csrf"]) {
            if(!Auth::password(null, $opt["csrf"])) {
                return "Invalid Session";
            }
        }

        $permanent                                              = ($opt["refresh"] === null
                                                                    ? authSession::COOKIE_PERMANENT
                                                                    : $opt["refresh"]
                                                                );
        /**
         * Purge header and remove old cookie
         */
        $this->destroy();
        session_regenerate_id(true);
        session_start();
        $session_id                                             = session_id();



        $select                                                 = array(
                                                                    "anagraph.*"
                                                                    , "access.users.*"
                                                                    , "access.groups.*"
                                                                    , "access.tokens.token" => "name"
                                                                    , "access.tokens.expire"
                                                                    , "access.tokens.type"
                                                                );

        $anagraph                                               = Auth::getAnagraphByUser($ID, $opt["model"], $select);
        if($domain)                                             { $anagraph["domain"] = $domain; }

        /**
         * Set islogged
         */
        if(Auth::isLogged($anagraph["user"]["acl"])) {
            /*
             * Set Session Data
             */
            $this->env(authSession::DATA_USER, $anagraph);
            if($opt["csrf"]) {
                $this->env(authSession::DATA_CSRF, $opt["csrf"]);
            }

            /*
             * Set Cookie
             */
            if($permanent) {
                $this->cookie_create($this->sessionName(), $session_id, $permanent);
            }
            if($domain["name"]) {
                $this->cookie_create("Domain", $domain["name"], $permanent);
            }
            $this->cookie_create("group", $anagraph["user"]["acl_primary"], $permanent);


            $res = array(
                "session"   => array(
                    "name"  => $this->sessionName()
                    , "id"  => $this->sessionId()
                )
                , "status"  => "0"
                , "error"   => ""
            );
        } else {
            $this->destroy();
            $res = array(
                "session"   => array(
                    "name"  => $this->sessionName()
                , "id"  => $this->sessionId()
                )
            , "status"  => "401"
            , "error"   => "Insufficent Permission"
            );
        }
        return $res;
    }

    public function env($name, $value = null) {
        if($value) {
            $_SESSION[$name] = $value;
        } else {
            return $_SESSION[$name];
        }
    }
    public function envIsset($name) {
        return isset($_SESSION[$name]);
    }
    public function envUnset($name) {
        $res = $_SESSION[$name];
        unset($_SESSION[$name]);

        return $res;
    }
    public function userInfo($set = null) {
        $anagraph = $this->env(authSession::DATA_USER);
        if(is_array($set) && count($set)) {
            $anagraph = array_replace_recursive($anagraph, $set);
            $this->env(authSession::DATA_USER, $anagraph);
        }

        return $anagraph;
    }
    /**
     * @param bool $cookie
     */
    public function destroy($cookie = true) {
        @session_unset();
        @session_destroy();

        $session_name = $this->sessionName();
        if($cookie) {
            header_remove("Set-Cookie");
            $this->cookie_destroy($session_name);
            $this->cookie_destroy("Domain");
            $this->cookie_destroy("group");

        }
        unset($_GET[$session_name], $_POST[$session_name], $_COOKIE[$session_name], $_REQUEST[$session_name]);
        unset($_GET["Domain"], $_POST["Domain"], $_COOKIE["Domain"], $_REQUEST["Domain"]);
        unset($_GET["group"], $_POST["group"], $_COOKIE["group"], $_REQUEST["group"]);
    }

    /**
     * @param null $name
     * @return null|string
     */
    private function sessionName($name = null) {
        static $isset                                           = null;

        if(!$name)                                              $name = (authSession::SESSION_NAME
                                                                    ? authSession::SESSION_NAME
                                                                    : session_name()
                                                                );
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
    private function sessionPath($path = null) {
        static $isset                                           = null;

        if(!$path)                                              $path = (authSession::SESSION_PATH
                                                                    ? authSession::SESSION_PATH
                                                                    : session_save_path()
                                                                );
        if($isset != $path) {
            session_save_path($path);
            $isset                                              = $path;
        }

        return $path;
    }

    /**
     * @param null $id
     * @param null $path
     * @return bool
     */
    private function checkSession($id = null, $path = null) {
        if(!$id)                                                $id     = $this->sessionId();
        if(!$path)                                              $path   = $this->sessionPath();

        return file_exists(rtrim($path, "/") . "/sess_" . $id);
    }

    /**
     * @return mixed
     */
    private function sessionId() {
        $session_name                                           = $this->sessionName();
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
    private function cookie_create($name, $value, $permanent = null) { //_ut
        if(!$permanent)                                         $permanent = authSession::COOKIE_PERMANENT;
        $sessionCookie                                          = session_get_cookie_params();
        $lifetime                                               = ($permanent
                                                                    ? time() + (60 * 60 * 24 * 365)
                                                                    : $sessionCookie["lifetime"]
                                                                );

        //setcookie($name, $value, $lifetime, $sessionCookie['path'], $_SERVER["HTTP_HOST"], $sessionCookie['secure'], $sessionCookie["httponly"]);

        $sessionCookie                                          = $this->cookie_share_in_subdomains();
        setcookie($name, $value, $lifetime, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);
        $_COOKIE[$name] = $value;
    }

    /**
     * @param string $name
     */
    private function cookie_destroy($name) { //_ut
        $sessionCookie                                          = session_get_cookie_params();
        setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $_SERVER["HTTP_HOST"], $sessionCookie['secure'], $sessionCookie["httponly"]);

        $sessionCookie                                          = $this->cookie_share_in_subdomains();
        setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);

        unset($_COOKIE[$name]);
    }

    /**
     * @return bool
     */
    private function cookie_share_in_subdomains($share = authSession::COOKIE_SHARE) {
        if($share)                                              session_set_cookie_params(0, '/', '.' . $this->getPrimaryDomain());

        return session_get_cookie_params();
    }

}