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

class Auth extends vgCommon
{
    const APPID                                                     = APPID;
    const API_PATH                                                  = "/api/user";
    const SECURITY_LEVEL                                            = "7"; //APP_SECURITY_LEVEL;
    const AUTHOR                                                    = "VGallery Auth";
    const DEBUG                                                     = DEBUG_MODE;

    const CERTIFICATE_KL                                            = "002010008005";
    const CERTIFICATE_KD                                            = "846315270";
    const CERTIFICATE_KP                                            = "2";

    const TYPE                                                      = "auth";
    const REQUEST_METHOD                                            = "POST";
    const ACTIVATION_CODE                                           = "random";

    static $singleton                                               = null;
    static $request                                                 = array(
                                                                        "token"             => "t"
                                                                        , "username"        => "username"
                                                                        , "password"        => "password"
                                                                        , "scopes"          => "scopes"
                                                                        , "domain"          => "domain"
                                                                        , "refresh"         => "refresh"
                                                                        , "grantor"         => "g"
                                                                    );
    static $headers                                                 = array(
                                                                        "client_id"         => "CLIENT_ID"
                                                                        , "client_secret"   => "CLIENT_SECRET"
                                                                        , "model"           => "TYPE"               //person, company, custom | Default: null or from DB
                                                                        , "token"           => "TOKEN"              //true, custom | Default: live
                                                                        , "activation"      => "ACTIVATION"         //false, true, 2FA | Default: true or from DB
                                                                        , "domain"          => "DOMAIN"
                                                                        , "refresh"         => "REFRESH"
                                                                    );
    static $opt                                                     = array(
                                                                        "model"             => "person"
                                                                        , "method"          => "session"
                                                                        , "fields"          => null
                                                                        , "scopes"          => null
                                                                        , "redirect"        => null
                                                                        , "refresh"         => null
                                                                        , "token"           => true
                                                                        , "activation"      => true
                                                                        , "security"        => false
                                                                        , "user"            => false
                                                                        , "exit"            => true
                                                                    );

    protected $service                                              = null;
    protected $controllers                                          = array(
                                                                    );
    protected $controllers_rev                                      = null;
    protected $connectors                                           = array(
                                                                    );
    protected $struct					                            = array();


    public $method                                                  = "token";

    public static function getInstance($service = null)
    {
        if(!self::$singleton[$service]) {
            $auth                                                   = (self::$singleton["auth"]
                                                                        ? self::$singleton["auth"]
                                                                        : new Auth($service)
                                                                    );
            self::$singleton[$service]                              = $auth->getService($service);
        }
        return self::$singleton[$service];
    }

    public function __construct($service = null)
    {
        $this->loadControllers(__DIR__);
        $this->service                                              = $service;

        require_once($this->getAbsPathPHP("/config"));

        //$this->setConfig($this->connectors, $this->services);
        //$this->loadSession();
    }

    /**
     * Autentica un utente passando le credenziali
     * questo metodo supporta le seguenti tipologie di autenticazione:
     *  - Sessione
     * @example Auth::login("[USERNAME]", "[PASSWORD]");
     * Se non viene specificato alcun parametro vengono recuperati username e password dalla $_REQUEST:
     * $_REQUEST["username"], $_REQUEST["password"]
     * @example Auth::login();

     *  - Token
     * @example Auth::login("[USERNAME]", "[PASSWORD]", array("type" => "token"));
     * Se non viene specificato alcun parametro vengono recuperati username e password dalla $_REQUEST:
     * $_REQUEST["username"], $_REQUEST["password"]
     * @example Auth::login(array("type" => "token"));
     *
     * Opt: il parametro ha i seguenti parametri
     * - type string: [session|token]
     *  Tipo di autenticazione
     *
     * - 2FA string: [sms|email]
     * Abilita la 2 factor Authentication via sms o email
     * NB: l'utente deve aver censito nella propria anagrafica tel o email
     *
     * - exit bool
     * Se true e il login non ha successo viene bloccata l'esecuzione dello script (exit)
     *
     * - domain string: [dominio specifico]
     * Specifica l'appartenenza o ambito dell'utente con il quale si vuole accedere
     *
     * - method string: [POST | PATCH | DELETE | GET | COOKIE | SESSION]
     * http request method usato per inviare le credenziali
     * NB: Per questo metodo l'unico metodo supportato è POST
     *
     * @api /api/user/login
     *
     * @param null $username
     * @param null $password
     * @param null $opt
     * @return array(status, error, token)
     *
     * @todo da finire e testare la 2FA
     *
     */
    public static function login($username = null, $password = null, $opt = null) { //aggiungere refresh token
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        if(is_array($username) && !$password && !$opt) {
            $opt                                                    = $username;
            $username                                               = null;
        }
        $opt                                                        = self::getOpt($opt);

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        //if(!$res) {
        $username                                                   = ($username
                                                                        ? $username
                                                                        : self::getReq("username")
                                                                    );
        $password                                                   = ($password
                                                                        ? $password
                                                                        : self::getReq("password")
                                                                    );
        if($username && $password) {
            if(!$opt["domain"])                                     $opt["domain"] = self::getReq("domain");

            $security                                               = self::security($opt);
            if(isset($security["status"]) && $security["status"] === "0") {
                $domain                                             = $security["domain"]["name"];
                $user                                               = Anagraph::getInstance("access")->read(
                                                                        array(
                                                                            "users.ID"
                                                                            , "users.tel"
                                                                            , "users.email"
                                                                            , "groups.name"
                                                                        )
                                                                        , array(
                                                                            "users.username"    => $username
                                                                            , "users.password"  => $password
                                                                            , "domains.name"    => $domain
                                                                        )
                                                                    );
                if($user["ID"]) {
                    switch ($opt["method"]) {
                        case "token":
                            $auth                                   = Auth::getInstance("token")->get(
                                                                        $user["ID"]
                                                                        , array(
                                                                            "limit"     => "1"
                                                                            , "token"    => ($security["domain"]["security"]["token_type"]
                                                                                            ? $security["domain"]["security"]["token_type"]
                                                                                            : null
                                                                                        )
                                                                            , "create"  => array(
                                                                                "key" => self::APPID . "-" . $domain . "-" . $username . "-" . $password
                                                                                , "expire" => ($security["domain"]["security"]["token_expire"]
                                                                                    ? $security["domain"]["security"]["token_expire"]
                                                                                    : null
                                                                                )
                                                                            )
                                                                        )
                                                                    );
                            break;
                        case "session":
                            $auth                                   = Auth::getInstance("session")->create($user["ID"]);
                            break;
                        default:
                            $auth                                   = "Authentication Method not Supported";
                    }

                    if(is_array($auth)) {
                        if(isset($auth["status"]) && $auth["status"] === "0") {
                            $opt["model"]                           = $user["group"]["name"];

                            $res                                    = $auth;
                            $anagraph                               = self::getAnagraphByUser($user["ID"], $opt["model"]);
                            if(is_array($anagraph)) {
                                $res["user"]                        = $anagraph;
                            } else {

                            }
                            // $res["status"]                          = "0";

                            if($opt["2FA"]) {
                                switch ($opt["2FA"]) {
                                    case "sms":
                                        $to                         = $user["tel"];
                                        $service2FA                 = "sms";
                                        break;
                                    case "email":
                                        $to                         = $user["email"];
                                        $service2FA                 = "email";
                                        break;
                                    default:
                                }

                                if($to && $service2FA) {
                                    $code                           = self::createCode();

                                    Anagraph::getInstance("access")->write(
                                        array(
                                            "devices.client_id"     => $opt["client_id"]
                                            , "devices.name"        => ""
                                            , "devices.type"        => ""
                                            , "devices.ID_user"     => $user["ID"]
                                            , "devices.serial"      => $opt["client_serial"]
                                            , "devices.last_update" => time()
                                        )
                                        , array(
                                            "set"                   => array(
                                                "devices.last_update"   => time()
                                            )
                                            , "where"               => array(
                                                "devices.client_id"     => $opt["client_id"]
                                                , "devices.name"        => ""
                                                , "devices.type"        => ""
                                                , "devices.ID_user"     => $user["ID"]
                                                , "devices.serial"      => $opt["client_serial"]
                                            )
                                        )
                                    );

                                    $res                            = Notifier::getInstance($service2FA)->send($code, $to);
                                } else {
                                    $res["status"]                  = "409";
                                    $res["error"]                   = "Email or Tel Empty for Sending AuthCode";
                                }
                            }
                        } else {
                            $res                                    = $auth;
                        }
                    } else {
                        $res["status"]                              = "500";
                        $res["error"]                               = $auth;
                    }
                } else {
                    $res["status"]                                  = "401";
                    $res["error"]                                   = "Wrong Username or Password";
                }
            } else {
                $res["status"]                                      = $security["status"];
                $res["error"]                                       = $security["error"];
            }
        } else {
            $res["status"]                                          = "400";
            $res["error"]                                           = "Username or Password Empty";
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);
        //}

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
	}

    /**
     * Invalida l'autenticazione
     *
     * @param null $opt
     * @return mixed
     */
    public static function logout($token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        if(is_array($token) && !$opt) {
            $opt                                                    = $token;
            $token                                                  = null;
        }

        $security                                                   = self::security($opt);
        if(isset($security["status"]) && $security["status"] === "0") {
            $res                                                    = Auth::getInstance("session")->destroy();

            $res["status"]                                          = "0"; //todo: da invalidare il token
            $res["error"]                                           = "";
        } else {
            $res["status"]                                          = $security["status"];
            $res["error"]                                           = $security["error"];
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    /**
     * Verifica se le l'autenticazione è valita.
     * questo metodo valida le seguenti tipologie di autenticazione:
     * - Token
     *
     *
     * - Sessione
     *
     *
     * @param null $token
     * @param null $opt
     * @return mixed
     */
    public static function check($token = null, $opt = null) {
        //non torna i dati utente per scelta

        if(self::DEBUG)                                             $start = Stats::stopwatch();
//da raffinare con il client id e secret e il domain name
        if(is_array($token) && !$opt) {
            $opt                                                    = $token;
            $token                                                  = null;
        }

        $opt                                                        = self::getOpt($opt);

        //$res                                                        = self::isInvalidReqMethod($opt["exit"]);
        //if(!$res) {
        $security                                                   = self::security($opt);
        if(isset($security["status"]) && $security["status"] === "0") {
            if($opt["method"] == "refresh") {
                $opt["refresh"]                                     = self::getReq("refresh");
                if($opt["refresh"] === null) {
                    $isInvalid                                      = "Refresh not Set";
                }
            }

            if(!$isInvalid) {
                $token                                              = ($token
                                                                        ? $token
                                                                        : self::getReq("token")
                                                                    );
                $res                                                = ($token
                                                                        ? Auth::getInstance("token")->check($token, $opt)
                                                                        : Auth::getInstance("session")->check($opt)
                                                                    );
                if(isset($res["status"]) && $res["status"] === "0" && $opt["security"]) {
                    $res                                            = array_replace($security, $res);
                }

            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = $isInvalid;
            }
        } else {
            $res["status"]                                          = $security["status"];
            $res["error"]                                           = $security["error"];
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        //}

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    /**
     * @param $code
     * @param null $token
     * @param null $opt
     * @return mixed
     */
    public static function activation($code, $token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $error                                                  = "";
            $token                                                  = ($token
                                                                        ? $token
                                                                        : self::getReq("token")
                                                                    );

        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function recover($opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {

        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);
    }

    /**
     * @param null $request
     * @param null $opt
     * @return array|mixed|null
     */
    public static function registration($request = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        if(is_array($request) && !$opt) {
            $opt                                                    = $request;
            $request                                                = null;
        }
        $opt                                                        = self::getOpt($opt);

        //$res                                                        = self::isInvalidReqMethod($opt["exit"]);
        //if(!$res) {
        $username                                                   = self::getReq("username");
        $password                                                   = self::getReq("password");

        if($username && $password) {
            if(!$opt["domain"])                                     $opt["domain"] = self::getReq("domain");

            $security                                               = self::security($opt);
            if(isset($security["status"]) && $security["status"] === "0") {
                $model                                              = self::getReqBySchema($opt["model"]);
                $req                                                = $model["select"];
                if($model["group"]) {
                    $registration                                   = Anagraph::getInstance("domain")->read(array(
                                                                        "registration.ID_group"
                                                                        , "registration.token"
                                                                        , "registration.activation"
                                                                    ), array(
                                                                        "registration.ID_domain"            => $security["domain"]["ID"]
                                                                        , "registration.anagraph_type"      => $model["group"]
                                                                    ));
                    if(is_array($registration)) {
                        $opt["activation"]                          = $registration["activation"];
                        $opt["token"]                               = $registration["token"];

                        $req["access.users.acl"]                    = $registration["ID_group"];
                    } elseif($registration) {
                        $res["status"]                              = "500";
                        $res["error"]                               = $registration;
                    } else {
                        //$req["access.users.acl"]                  = "7"; //todo: non funziona perche non converte l'alias

                        $req["access.groups.name"]                  = $model["group"]; //todo: problema di sequesta. se messo dopo non triggera la join
                    }
                }

                $req["access.users.username"]                       = $username;
                $req["access.users.password"]                       = $password;

                if(!$res) {
                    $req["access.users.ID_domain"]                  = $security["domain"]["ID"];

                    if(!$opt["activation"]) {
                        $req["access.users.status"]                 = true;
                    }
                    if($opt["token"]) {
                        $token                                      = Auth::getInstance("token");
                        $req["access.tokens.token"]                 = $token->create(self::APPID . "-" . $security["domain"]["name"] . "-" . $username . "-" . $password);
                        $req["access.tokens.type"]                  = ($security["domain"]["security"]["token_type"]
                                                                        ? $security["domain"]["security"]["token_type"]
                                                                        : $token::TYPE
                                                                    );
                        $req["access.tokens.expire"]                = (isset($security["domain"]["security"]["token_expire"])
                                                                        ? $security["domain"]["security"]["token_expire"]
                                                                        : time() + $token::EXPIRE
                                                                    );
                    }

                    $user                                           = Anagraph::getInstance();
                    $return                                         = $user->insert($req);

                    if(is_array($return)) {
                        $res["status"]                              = "0";
                        $res["error"]                               = "";
                        if($opt["token"])                           $res["token"] = array(
                                                                        "name"                                      => $req["access.tokens.token"]
                                                                        , "expire"                                  => $req["access.tokens.expire"]
                                                                    );
                    } else {
                        $res["status"]                              = "410";
                        $res["error"]                               = $return;
                    }
                }
            } else {
                $res["status"]                                      = $security["status"];
                $res["error"]                                       = $security["error"];
            }
        } else {
            $res["status"]                                          = "400";
            $res["error"]                                           = "username and password Required";
        }
        //}

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
	}

	public static function share($scopes = null, $token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $res = array();

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function join($grantor = null, $scopes = null, $token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $opt                                                        = self::getOpt($opt);
        $security                                                   = self::check(
                                                                        $token
                                                                        , array(
                                                                            "exit"                              => $opt["exit"]
                                                                            , "fields" => array(
                                                                                "users.ID"                      => "ID_user"
                                                                                , "users.acl"                   => "ID_group"
                                                                            )
                                                                            , "security"                        => true
                                                                        )
                                                                    );
        if(isset($security["status"]) && $security["status"] === "0") {
            //$security                                               = self::security($opt);
            //if(isset($security["status"]) && $security["status"] === "0") {
            $policy                                                 = Anagraph::getInstance("domain")->read(array(
                                                                        "policy.groups"
                                                                        , "policy.scopes"
                                                                    ), array(
                                                                        "policy.ID_domain"                      => $security["domain"]["ID"]
                                                                        , "policy.ID_group"                     => $security["ID_group"]
                                                                    ));
            if(is_array($policy)) {
                $grantor                                            = ($grantor
                                                                        ? $grantor
                                                                        : self::getReq("grantor")
                                                                    );
                $user                                               = Anagraph::getInstance("access")->read(array(
                                                                        "users.ID"
                                                                        , "users.acl"
                                                                    ), array(
                                                                        "tokens.token"                          => $grantor
                                                                    ));
                if(is_array($user)) {
                    if(self::checkScopes($user["acl"], $policy["groups"])) {
                        if(!$scopes)                                $scopes = self::getReq("scopes");
                        if(!$policy["scopes"])                      $policy["scopes"] = $security["domain"]["scopes"];

                        $arrScopesValid                             = self::checkScopes($scopes, $policy["scopes"]);
                        if($arrScopesValid) {
                            $policy_granted                         = Anagraph::getInstance("domain")->read(array(
                                                                        "policy_granted.scope"
                                                                        , "policy_granted.expire"
                                                                    ), array(
                                                                        "policy_granted.ID_domain"              => $security["domain"]["ID"]
                                                                        , "policy_granted.ID_user_trusted"      => $security["ID_user"]
                                                                        , "policy_granted.ID_user_shared"       => $user["ID"]
                                                                        , "policy_granted.client_id"            => $security["client"]["ID"]
                                                                    ));
                            if(is_array($policy_granted) || $policy_granted === false) {
                                if(is_array($policy_granted) && count($policy_granted)) {
                                    foreach($policy_granted AS $granted) {

                                    }
                                }



                                $res["status"]                      = "0";
                                $res["error"]                       = "";
                            } else {
                                $res["status"]                      = "500";
                                $res["error"]                       = $policy_granted;
                            }
                        } else {
                            $res["status"]                          = "403";
                            $res["error"]                           = "Scope not Permitted" . (self::DEBUG
                                                                        ? ": " . implode(self::diffScopes($scopes, $policy["scopes"]), ", ")
                                                                        : ""
                                                                    );
                        }
                    } else {
                        $res["status"]                              = "403";
                        $res["error"]                               = "Policy Group not Permitted" . (self::DEBUG
                                                                        ? ": " . implode(self::diffScopes($user["acl"], $policy["groups"]), ", ")
                                                                        : ""
                                                                    );
                    }
                } else {
                    $res["status"]                                  = "410";
                    $res["error"]                                   = "Grantor not Found";
                }
            } elseif($policy === false) {
                $res["status"]                                      = "401";
                $res["eror"]                                        = "Policy not Found";
            } else {
                $res["status"]                                      = "500";
                $res["error"]                                       = $policy;
            }
            //}
        } else {
            $res["status"]                                          = $security["status"];
            $res["error"]                                           = $security["error"];
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

	public static function key($scopes = null, $token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $opt                                                        = self::getOpt($opt);
        if(!$scopes)                                                $scopes = self::getReq("scopes");
        if($scopes) {
            $security                                               = self::check(
                                                                        $token
                                                                        , array(
                                                                            "exit"                  => $opt["exit"]
                                                                            , "fields"              => array(
                                                                                "users.ID"          => "ID_user"
                                                                            )
                                                                            , "scopes"              => $scopes
                                                                            , "security"            => true
                                                                        )
                                                                    );

            if(isset($security["status"]) && $security["status"] === "0") {
                //$security                                           = self::security($opt, $scopes);
                //if(isset($security["status"]) && $security["status"] === "0") {
                    /*$select                                         = ($opt["fields"]
                                                                        ? $opt["fields"]
                                                                        : self::getData()
                                                                    );*/
                $select["ID"]                                       = "key";
                $anagraph                                           = self::getAnagraphByUser($security["ID_user"], $opt["model"], $select);
                //status 1 e 0 nella where se funziona correttamnte
                //fare un read partendo da anagraph e discendendo nei sotto elementi access e domain
                // verificare in insert expire che venga scritto correttamente

                //usare per il certificato questo openssl_csr_new
                //trovare sistema per la get con le chiavi con i punti per la registrazione
                if(is_array($anagraph)) {
                    if(isset($anagraph["key"]) && $anagraph["key"]) {
                        $mc                                         = self::mergeKD(
                                                                        self::encipherKD(
                                                                            self::APPID
                                                                            , $anagraph["key"]
                                                                            , $security["certificate"]
                                                                        )
                                                                        , $scopes
                                                                        , $security["certificate"]
                                                                    );
                        if($mc) {
                            unset($anagraph["key"]);

                            $res                                    = $mc;
                            $res["user"]                            = $anagraph;
                            if($security["token"]["expire"] < 0)
                                $res["token"]                       = $security["token"];

                            $res["status"]                          = "0";
                            $res["error"]                           = "";
                        } else {
                            $res["status"]                          = "410";
                            $res["error"]                           = "Unknow Error";
                        }
                    } else {
                        $res["status"]                              = "404";
                        $res["error"]                               = "Unknow User";
                    }
                } else {
                    $res["status"]                                  = "500";
                    $res["error"]                                   = $anagraph;
                }
                //} else {
                //    $res                                            = $security;
                //}

            } else {
                $res["status"]                                      = $security["status"];
                $res["error"]                                       = $security["error"];
            }
        } else {
            $res["status"]                                          = "400";
            $res["error"]                                           = "Scope not Set";
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function users($token = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();
//todo: da gestire lista utenti discriminati per token
        $opt                                                        = self::getOpt($opt);

        //$res                                                        = self::isInvalidReqMethod($opt["exit"]);
        //if(!$res) {
        $security                                                   = self::security($opt);
        if(isset($security["status"]) && $security["status"] === "0") {
            $model                                                  = self::getReqBySchema($opt["model"]);

            $anagraph                                               = Anagraph::getInstance()->read(
                                                                        $model["select"]
                                                                        , $model["where"]
                                                                        , $model["order"]
                                                                        , $model["limit"]
                                                                    );

/*print_r($opt["fields"]);
print_r($anagraph);
die();*/
            if(is_array($anagraph)) {
                unset($anagraph["exTime"]);
                $res["users"]                                       = $anagraph;
                $res["status"]                                      = "0";
                $res["error"]                                       = "";
            } elseif($anagraph === false) {
                $res["users"]                                       = array();
                $res["status"]                                      = "0";
                $res["error"]                                       = "";
            } else {
                $res["status"]                                      = "500";
                $res["error"]                                       = $anagraph;
            }
        } else {
            $res["status"]                                          = $security["status"];
            $res["error"]                                           = $security["error"];
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);
        //}

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function createCertificate($secret = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $opt                                                        = self::getOpt($opt);

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $secret                                                 = ($secret
                                                                        ? $secret
                                                                        : self::getReq("password")
                                                                    );

            $domain                                                 = self::getDomain(
                                                                        $opt["domain"]
                                                                        , $opt["client_id"]
                                                                        , $opt["client_secret"]
                                                                    );

            if(isset($domain["status"]) && $domain["status"] === "0") {
                require_once __DIR__ . "/AuthCertificate.php";

                $certificate                                        = new AuthCertificate(self::domain4certificate($domain));

                $res                                                = $certificate->createCertificate($secret);

                unset($secret);

            } else {
                $res                                                = $domain;
            }
        }

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function getAnagraph($user = null, $opt = null) {
        if(self::DEBUG)                                             $start = Stats::stopwatch();

        $opt                                                        = self::getOpt($opt);

        //$res                                                        = self::isInvalidReqMethod($opt["exit"]);
        //if(!$res) {
        $security                                                   = self::security($opt);
        if(isset($security["status"]) && $security["status"] === "0") {
            if(!$user) {
                $session = Auth::getInstance("session")->check($opt);
                $anagraph = $session["user"]["anagraph"];

            }


            if(is_array($user))                                     $where["uid"]           = $user["ID"];
            elseif(is_numeric($user) && $user > 0)                  $where["uid"]           = $user["ID"];
            elseif(strpos($user, "@") !== false)              $where["email"]         = $user;
            elseif($user)                                           $where["smart_url"]     = $user;

            if($where) {
                $anagraph                                           = self::getAnagraphByUser($where, null, $opt["fields"]);
                if(is_array($anagraph)) {
                    $res["anagraph"]                                = $anagraph;
                    $res["status"]                                  = "0";
                    $res["error"]                                   = "";
                } else {
                    $res["status"]                                  = "404";
                    $res["error"]                                   = "User not Found";
                }
            } else {
                $res["status"]                                      = "401";
                $res["error"]                                       = "Missing Params";
            }
        } else {
            $res["status"]                                          = $security["status"];
            $res["error"]                                           = $security["error"];
        }
        //}

        if(is_array($res) && $res["status"] !== "0" && $opt["exit"])self::endScript($res);

        if(self::DEBUG && is_array($res))                           $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    private static function getAnagraphByUser($ID_user, $ext = null, $select = null) {
        $model                                                      = self::getReqBySchema($ext, $select);

        $anagraph                                                   = Anagraph::getInstance()->read(
            $model["select"]
            , array(
                "ID_user"       => $ID_user
            )
        );

        return $anagraph;
    }

    private static function security($opt) {
        switch(self::SECURITY_LEVEL) {
            case "0"; //no security
                break;
            case "1"; //no Client
                break;
            case "2"; //no Domain
                break;
            case "4"; //no Certificate
                break;
            case "7"; //max Security
            default:
        }
        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $domain                                                 = self::getDomain(
                                                                        $opt["domain"]
                                                                        , $opt["client_id"]
                                                                        , $opt["client_secret"]
                                                                        , $opt["scopes"]
                                                                    );

            if(isset($domain["status"]) && $domain["status"] === "0") {
                $res                                                = self::getCertificate(self::domain4certificate($domain), $domain["secret"]);
                if(isset($res["status"]) && $res["status"] === "0")
                    $res["domain"]                                  = $domain;
            } else {
                $res                                                = $domain;
            }
            unset($domain);
        }

        return $res;
    }
    private static function domain4certificate($domain) {
        $cDomain                                                    = $domain;
        $cDomain["secret"]                                          = md5($domain["secret"]
            ? $domain["secret"]
            : self::APPID
        );

        return $cDomain;
    }
    protected static function schema($type = null, $key = null) {
        $schema                                                     = parent::schema();
        $def                                                        = array(
                                                                        "username"          => "access.users.username"
                                                                        , "password"        => "access.users.password"
                                                                        , "status"          => "access.users.status"
                                                                        , "email"           => array(
                                                                                                "access.users.email"
                                                                                                , "anagraph.email"
                                                                                            )
                                                                        , "tel"             => array(
                                                                                                "access.users.tel"
                                                                                                , "anagraph.tel"
                                                                                            )
                                                                        , "type"            => array(
                                                                                                "anagraph_type.name"
                                                                                            )
                                                                        , "group"           => array(
                                                                                                "access.groups.name"
                                                                                            )
                                                                        , "name"            => array(
                                                                                                "anagraph.name"
                                                                                            )
                                                                       /* , "custom1"         => "anagraph.custom1"
                                                                        , "custom2"         => "anagraph.custom2"
                                                                        , "custom3"         => "anagraph.custom3"
                                                                        , "custom4"         => "anagraph.custom4"
                                                                        , "custom5"         => "anagraph.custom5"
                                                                        , "custom6"         => "anagraph.custom6"
                                                                        , "custom7"         => "anagraph.custom7"
                                                                        , "custom8"         => "anagraph.custom8"
                                                                        , "custom9"         => "anagraph.custom9"*/
                                                                    );

        $model                                                      = $schema["models"][$type];
        $model["group"]                                             = (is_array($schema["models"][$type])
                                                                        ? $type
                                                                        : null
                                                                    );
        $model["fields"]                                            = (is_array($schema["models"][$type]["fields"])
                                                                        ? array_replace($def, $schema["models"][$type]["fields"])
                                                                        : $def
                                                                    );
        return ($key
            ? $model[$key]
            : $model
        );
    }

    private static function getReqBySchema($ext = null, $select = null) {
        $rules                                                      = self::schema($ext);
        $rules["request_method"]                                    = self::REQUEST_METHOD;
        $rules["mapping"]                                           = array_fill_keys(self::$request, "security");

        $req                                                        = self::getRequest($rules, "query");
        $req["select"]                                              = array_replace($req["select"], (array) $select);
        $req["group"]                                               = $rules["group"];
        return $req;
    }
    private static function getCertificate($domain, $secret) {
        static $res                                                 = null;

        if($res === null) {
            if($domain) {
                if(!$domain["client"]["disable_csrf"]
                    && $domain["security"]["csr_url"]
                    && $domain["security"]["pkey_url"]
                ) {
                    require_once __DIR__ . "/AuthCertificate.php";

                    $certificate                                    = new AuthCertificate($domain);

                    $return                                         = $certificate->get($secret);
                    if(isset($return["status"]) && $return["status"] === "0") {
                        $res["certificate"]                         = $certificate;
                        $res["status"]                              = "0";
                        $res["error"]                               = "";
                    } else {
                        $res                                        = $return;
                    }

                    unset($tmp);
                } else {
                    $res["status"]                                  = "0";
                    $res["error"]                                   = "";
                }
            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = "Certicate Domain Missing";
            }
        }

        unset($secret);

        return $res;
    }
    private static function getOpt($opt = null) {
        if(!$opt)                                                   $opt = array();
        $opt                                                        = array_replace(self::$opt, $opt);

        foreach(self::$headers AS $key => $req) {
            if($_SERVER["HTTP_" . $req]) {
                $opt[$key]                                          = $_SERVER["HTTP_" . $req];
            }
        }

        return $opt;
    }

    private static function combineKL($a /*unique id */, $b /* suffix */, $certificate) {
        $kl                                                         = ($certificate
                                                                        ? $certificate->index("kl")
                                                                        : self::CERTIFICATE_KL
                                                                    );
        if($kl) {
            $dsk                                                    = $a;
            $bn                                                     = crc32($b);

            $kln                                                    = chunk_split($kl, 3 * 2, "|");
            $arrKL                                                  = explode("|", $kln);
            foreach($arrKL AS $coord) {
                $arrCoord                                           = explode("|", chunk_split($coord, 3, "|"));

                $dsk                                                = substr_replace(
                                                                        $dsk
                                                                        , substr(
                                                                            $bn
                                                                            , ($arrCoord[1] >= strlen($bn)
                                                                                ? round($arrCoord[1] / strlen($bn), 0)
                                                                                : intval($arrCoord[1])
                                                                            )
                                                                            , 1
                                                                        )
                                                                        , $arrCoord[0]
                                                                        , 0
                                                                    );
            }

            return $dsk;
        }
    }
    private static function mergeKD($ask, $d, $certificate) {
        if($ask) {
            $arrD                                                   = explode(",", $d);
            $s                                                      = ($certificate
                                                                        ? $certificate->getDomain("secret")
                                                                        : md5(APPID)
                                                                    );
            foreach($arrD AS $b) {
                $dask                                               = self::combineKL($ask, $b, $certificate);
                if($dask && $s) {
                    $res[$b]                                        = self::shuffle($dask, $s);
                }
            }
        }

        return $res;
    }
    private static function encipherKD($a /* prefix */, $b /*unique id*/, $certificate) {
        $kd                                                         = ($certificate
                                                                        ? $certificate->index("kd")
                                                                        : self::CERTIFICATE_KD
                                                                    );
        $p                                                          = ($certificate
                                                                        ? $certificate->index("kp")
                                                                        : self::CERTIFICATE_KP
                                                                    );
        if($kd) {
            $auk                                                    = array();
            $an                                                     = crc32($a);

            $arrAN                                                  = str_split($an);
            $arrKD                                                  = str_split($kd);
            $kdnP                                                   = strlen($kd) - $p;
            $bn                                                     = (strlen($b)> $kdnP
                                                                        ? chunk_split($b, $kdnP, "|")
                                                                        : $b
                                                                    );
            $arrBN                                                  = explode("|", $bn);
            foreach($arrBN AS $bnp) {
                $collision                                          = 0;
                $arrB                                               = str_split($bnp);
                foreach ($arrKD AS $index) {
                    if (isset($arrB[$index])) {
                        $auk[]                                      = $arrB[$index];
                    } elseif (isset($arrAN[$index])) {
                        $auk[]                                      = $arrAN[$index];
                    } else {
                        $auk[]                                      = $index;
                    }

                    if($arrB[$index] == $arrAN[$index] || $arrB[$index] == $index) {
                        $collision++;
                    }
                }
                if($collision) {
                    $arrC = array();
                    $n = 9;
                    for ($i = 1; $i <= $p; $i++) {
                        $arrC[]                                     = array_search($n, $arrKD);
                        $n--;
                    }
                    $arrC                                           = array_merge($arrC, $arrC, $arrC, $arrC, $arrC);
                    for ($i = 0; $i < $collision; $i++) {
                        $c                                          = $auk[$arrC[$i]] + strlen($bnp);
                        if($c > 9)                                  $c = $c - 10;
                        $auk[$arrC[$i]]                             = $c;
                    }
                }


            }

            return implode("", $auk);
        }
    }

    private static function shuffle($k /* base to shuffle */, $s /* scope */) {
        $i                                                          = 0;
        $sn                                                         = "";
        $arrS                                                       = str_split($s);
        foreach($arrS AS $char) {
            $sn                                                     .= ord($char);
        }

        $arrSN                                                      = str_split($sn);
        $arrK                                                       = str_split($k);
        foreach($arrSN AS $pos) {
            if($pos >= count($arrK)) {
                $pos                                                = $pos - count($arrK);
            }
            self::arrMove($arrK, $pos, $i);

            $i++;
            if(count($arrK) == $i)                                  $i = 0;
        }

        return implode("", $arrK);
    }

    private static function arrMove(&$array, $a, $b) {
        $out                                                        = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }

    private static function getClient($client_id = null, $client_secret = null) {
        if(self::SECURITY_LEVEL & 1) {
            if(!$client_id && !$client_secret) {
                $opt                                                = self::getOpt();
                $client_id                                          = $opt["client_id"];
                $client_secret                                      = $opt["client_secret"];
            }
            if($client_id && $client_secret) {
                $client                                             = Anagraph::getInstance("domain")->read(array(
                                                                        "clients.client_id"         => "client_id"
                                                                        , "clients.domains"         => "domains"
                                                                        , "clients.disable_csrf"    => "disable_csrf"
                                                                        , "clients.grant_types"     => "grant_types"
                                                                    ), array(
                                                                        "clients.client_id"         => $client_id
                                                                        , "clients.client_secret"   => $client_secret
                                                                    ));
                if(is_array($client)) {
                    $res                                            = $client;
                    $res["status"]                                  = "0";
                    $res["error"]                                   = "";
                } else {
                    $res["status"]                                  = "410";
                    $res["error"]                                   = "Client not Found";
                }
            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = "missing client_id or client_secret";
            }
        } else {
            $res["status"]                                          = "0";
            $res["error"]                                           = "";
        }

        return $res;
    }

    private static function getDomain($domain_name = null, $client_id = null, $client_secret = null, $scopes = null) {

        $client                                                     = self::getClient($client_id, $client_secret);
        if(isset($client["status"]) && $client["status"] === "0") {
            if(self::SECURITY_LEVEL & 2) {
                $domain_where                                       = array(
                                                                        "name" => $domain_name
                                                                    );
                if($domain_where) {
                    $domain                                         = Anagraph::getInstance("domain")->read(array(
                                                                            "ID"
                                                                            , "name"
                                                                            , "expire"
                                                                            , "status"
                                                                            , "scopes"
                                                                            , "secret"
                                                                            , "company_name"            //=> "company.name"
                                                                            , "company_description"     //=> "company.description"
                                                                            , "company_state"           //=> "company.state"
                                                                            , "company_province"        //=> "company.province"
                                                                            , "company_city"            //=> "company.city"
                                                                            , "company_email"           //=> "company.email"
                                                                            , "security.csr_url"       // => "pem.url"
                                                                            , "security.csr_ip"         //=> "pem.ip"
                                                                            , "security.csr_protocol"   //=> "pem.protocol"

                                                                            , "security.pkey_url"       //=> "key.url"
                                                                            , "security.pkey_ip"        //=> "key.ip"
                                                                            , "security.pkey_protocol"  //=> "key.protocol"

                                                                            , "security.cert_expire"    //=> "cert.expire"
                                                                            , "security.cert_alg"       //=> "cert.alg"
                                                                            , "security.cert_id_length" //=> "cert.id_length"
                                                                            , "security.cert_key_length"//=> "cert.key_length"
                                                                            , "security.cert_precision" //=> "cert.precision"

                                                                            , "security.token_expire"   //=> "token.expire"
                                                                            , "security.token_type"    // => "token.type"
                                                                        )
                                                                        , $domain_where
                                                                    );

                    if(is_array($domain)) {
                       // $domain["security"]["pkey_url"] = $domain["security"]["csr_url"];
                       // $domain["security"]["pkey_ip"] = $domain["security"]["csr_ip"];
                      //  $domain["security"]["pkey_protocol"] = $domain["security"]["csr_protocol"];

                        $valid_domain                               = true;
                        if($client["domains"]) {
                            $arrDomains                             = explode(",", $client["domains"]);
                            if(array_search($domain["ID"], $arrDomains) === false) {
                                $valid_domain                       = false;
                            }
                        }

                        if($valid_domain) {
                            if($domain["status"]) {
                                if(!$domain["expiration"] || $domain["expiration"] < time()) {
                                    if(self::checkScopes($scopes, $domain["scopes"])) {
                                        unset($domain["expiration"]);
                                        unset($domain["status"]);

                                        $res                        = $domain;
                                        $res["client"]              = $client;
                                        $res["status"]              = "0";
                                        $res["error"]               = "";
                                    } else {
                                        $res["status"]              = "403";
                                        $res["error"]               = "Scope not Permitted" . (self::DEBUG
                                                                        ? ": " . implode(self::diffScopes($scopes, $domain["scopes"]), ", ")
                                                                        : ""
                                                                    );
                                    }
                                } else {
                                    $res["status"]                  = "401";
                                    $res["error"]                   = "Domain Expire Date";
                                }
                            } else {
                                $res["status"]                      = "401";
                                $res["error"]                       = "Domain not Active";
                            }
                        } else {
                            $res["status"]                          = "401";
                            $res["error"]                           = "Domain not Active";
                        }
                    } else {
                        $res["status"]                              = "410";
                        $res["error"]                               = "Domain not Found";
                    }
                } else {
                    $res["status"]                                  = "400";
                    $res["error"]                                   = "Missing Domain Name";
                }
            } else {
                $res["status"]                                      = "0";
                $res["error"]                                       = "";
            }
        } else {
            $res                                                    = $client;
        }

        return $res;
    }

    private static function createCode($type = null) {
        switch ($type) {
            case "random":
                $res = rand(100000, 999999);
                break;
            default:
                $res = substr(time(), 4);
        }
        return $res;
    }
    private static function checkScopes($set, $collection) {
        if($set && $collection) {
            $arrSet                                                 = explode("," , $set);
            $arrCollection                                          = explode(",", $collection);

            $arrIntersect                                           = array_intersect($arrSet, $arrCollection);

            return (count($arrIntersect) == count($arrSet)
                ? $arrIntersect
                : false
            );
        } else {
            return true;
        }
    }

    private static function diffScopes($set, $collection) {

        $arrSet                                                     = explode("," , $set);
        $arrCollection                                              = explode(",", $collection);

        return array_diff($arrSet, $arrCollection);
    }
    /**
     * @param null $service
     * @return mixed
     */
    private function getService($service = null) {
        $controller = $this->getControllerName($service ? $service : $this->service);

        return new $controller($this);
    }
    /*private static function getRequestAllowed($flip = false) {
        static $request                                             = null;

        if(!$request)                                               $request = self::$request;
        return ($flip
            ? array_fill_keys($request, true)
            : $request
        );
    }*/
    /**
     * @param null $request
     * @param string $method
     * @return array
     */
    /*private static function getData($request = null) {

        if(!$request)                                               $request = self::getReq();
        $return                                                     = array_diff_key($request, self::getRequestAllowed(true));

        foreach($return AS $key => $value) {
            $real_key                                               = str_replace("_", ".", $key);
            if($value)
                $res[$real_key]                                     = $value;
            else
                $res[]                                              = $real_key;

        }
        return $res;
    }*/

    /**
     * @param null $key
     * @param null $method
     * @return mixed
     */
    protected static function getReq($key = null) {
        $req                                                        = parent::getReq(self::REQUEST_METHOD);

        return ($key
            ? $req[self::$request[$key]]
            : $req
        );
    }

    protected static function getRequest($rules = null, $key = null) {
        return parent::getRequest($rules, $key);
    }
    private static function isInvalidHTTPS($exit = false)
    {
        if(!$_SERVER["HTTPS"]) {
            $res["status"]                                          = "405";
            $res["error"]                                           = "Request Method Must Be In HTTPS";

            if($exit)                                               self::endScript($res);
        }

        return $res;
    }
    /**
     * @param $method
     * @param bool $exit
     * @return mixed
     */
    private static function isInvalidReqMethod($exit = false, $method = self::REQUEST_METHOD) {
        if(self::getPathInfo(self::API_PATH)) {
            $res                                                    = self::isInvalidHTTPS($exit);
            if(!$res) {
                if($_SERVER["REQUEST_METHOD"] != $method) {
                    $res["status"]                                  = "405";
                    $res["error"]                                   = "Request Method Must Be " . $method;

                    if($exit)                                       self::endScript($res);
                }
            }
        } else {
            $res["status"]                                          = "0";
            $res["error"]                                           = "Internal Called";
        }

        return $res;
    }

    /**
     * @param null $json
     */
    private static function endScript($json = null) {
        if($json) {
            header("Content-type: application/json");
            echo json_encode($json);
        }

        exit;
    }

    /**
     * @param $service
     * @return string
     */
    private function getControllerName($service) {
        return self::TYPE . ucfirst($service);
    }

}
