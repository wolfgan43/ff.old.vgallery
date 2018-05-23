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
    const SECURITY_LEVEL                                            = "7"; //APP_SECURITY_LEVEL;
    const AUTHOR                                                    = "VGallery Auth";
    const DEBUG                                                     = DEBUG_MODE;

    const CERTIFICATE_KL                                            = "002010008005";
    const CERTIFICATE_KD                                            = "846315270";
    const CERTIFICATE_KP                                            = "2";

    const TYPE                                                      = "auth";
    const TOKEN_TYPE                                                = "live";
    const TOKEN_EXPIRE                                              = "0";
    const METHOD                                                    = "session";
    const REQUEST_METHOD                                            = "POST";


    static $singleton                                               = null;
    static $request                                                 = array(
                                                                        "token"             => "t"
                                                                        , "username"        => "username"
                                                                        , "password"        => "password"
                                                                        , "domain"          => "domain"
                                                                        , "scopes"           => "scopes"
                                                                    );
    static $headers                                                 = array(
                                                                        "client_id"         => "CLIENT_ID"
                                                                        , "client_secret"   => "CLIENT_SECRET"
                                                                        , "anagraph_type"   => "TYPE"               //person, company, custom | Default: null or from DB
                                                                        , "token"           => "TOKEN"              //true, custom | Default: live
                                                                        , "activation"      => "ACTIVATION"         //false, true, 2FA | Default: true or from DB
                                                                        , "domain"          => "DOMAIN"
                                                                    );
    static $opt                                                     = array(
                                                                        "type"              => "person"
                                                                        , "token"           => true
                                                                        , "activation"      => true
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
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        if(is_array($username) && !$password && !$opt) {
            $opt                                                    = $username;
            $username                                               = null;
        }
        $opt                                                        = self::getOpt($opt);

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $username                                               = ($username
                                                                        ? $username
                                                                        : self::getReq("username")
                                                                    );
            $password                                               = ($password
                                                                        ? $password
                                                                        : self::getReq("password")
                                                                    );
            if($username && $password) {
                if(!$opt["domain"])                                  $opt["domain"] = self::getReq("domain");

                $security                                           = self::security($opt);
                if(isset($security["status"]) && $security["status"] === "0") {
                    /*$user = Anagraph::getInstance("access")->read(
                        array(
                             "tokens.token"                             => "name"
                            , "tokens.expire"                           => true
                            , "tokens.type"                             => true
                            , "users.ID"                                => true
                        )
                        , array(
                            "users.username"                            => $username
                            , "users.password"                          => $password
                           // , "tokens.type"                           => Auth::TOKEN_TYPE

                            //, "domains.name"                          => $domain
                        )
                    );*/
//print_r($security); //todo: i secret sono identici da verificare il perche
//die();
                    $domain                                         = $security["domain"]["name"];
                    $user                                           = Anagraph::getInstance("access")->read(
                                                                        array(
                                                                            "users.ID"
                                                                            , "users.tel"
                                                                            , "users.email"
                                                                        )
                                                                        , array(
                                                                            "users.username"    => $username
                                                                            , "users.password"  => $password
                                                                            , "domains.name"    => $domain
                                                                        )
                                                                    );

                    if($user["ID"]) {
                        if(!$opt["fields"])                         $opt["fields"] = self::getData();
                        $method                                     = ($opt["type"]
                                                                        ? $opt["type"]
                                                                        : Auth::METHOD
                                                                    );

                        switch ($method) {
                            case "token":
                                $auth                               = Auth::getInstance($method)->get(
                                                                        $user["ID"]
                                                                        , array(
                                                                            "limit"     => "1"
                                                                            , "type"    => ($security["domain"]["security"]["token_type"]
                                                                                            ? $security["domain"]["security"]["token_type"]
                                                                                            : Auth::TOKEN_TYPE
                                                                                        )
                                                                            , "fields"  => $opt["fields"]
                                                                        )
                                                                    );
                                break;
                            case "session":
                                $auth                               = Auth::getInstance($method)->create($user["ID"], array("fields" => $opt["fields"]));
                                break;
                            default:
                        }

                        if(is_array($auth)) {
                            $res                                    = $auth;
                            $res["status"]                          = "0";

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
                                    $code                           = rand(100000, 999999);

                                    Anagraph::getInstance("access")->write(
                                        array(
                                            "device.user_agent"     => $_SERVER["USER_AGENT"]
                                            , "device.token"        => $auth["SID"]
                                            , "device.code"         => $code
                                            , "device.created"      => time()
                                            , "device.ID_user"      => $user["ID"]
                                            , "device.IP"           => $_SERVER["REMOTE_ADDR"]
                                        )
                                        , array(
                                            "set"                   => array(
                                                "device.code"       => $code
                                            )
                                            , "where"               => array(
                                                "device.user_agent" => $_SERVER["USER_AGENT"]
                                                , "device.token"    => $auth["SID"]
                                                , "device.ID_user"  => $user["ID"]
                                                , "device.IP"       => $_SERVER["REMOTE_ADDR"]
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
                            $res["status"]                          = "400";
                            $res["error"]                           = "Wrong Fields";
                        }
                    } else {
                        $res["status"]                              = "401";
                        $res["error"]                               = "Wrong Username or Password";
                    }
                } else {
                    $res                                            = $security;
                }
            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = "Username or Password Empty";
            }

            if(is_array($res) && $res["status"] !== "0" && $opt["exit"])
                self::endScript($res);
        }

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
	}

    /**
     * Invalida l'autenticazione
     *
     * @param null $opt
     * @return mixed
     */
    public static function logout($token = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        if(is_array($token) && !$opt) {
            $opt                                                    = $token;
            $token                                                  = null;
        }

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $res                                                    = Auth::getInstance("session")->destroy();
            if(is_array($res) && $res["status"] !== "0" && $opt["exit"])
                self::endScript($res);

        }

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }


    /**
     * @param null $token
     * @param null $opt
     * @return mixed
     */
    public static function check($token = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();
//da raffinare con il client id e secret e il domain name
        if(is_array($token) && !$opt) {
            $opt                                                    = $token;
            $token                                                  = null;
        }
        $opt                                                        = self::getOpt($opt);

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            if(!$opt["fields"])                                     $opt["fields"] = self::getData();
            $token                                                  = ($token
                                                                        ? $token
                                                                        : self::getReq("token")
                                                                    );

            $res                                                    = ($token
                                                                        ? Auth::getInstance("token")->check($token, $opt)
                                                                        : Auth::getInstance("session")->check($opt)
                                                                    );
            if(is_array($res) && $res["status"] !== "0" && $opt["exit"])
                self::endScript($res);

        }

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    /**
     * @param $code
     * @param null $token
     * @param null $opt
     * @return mixed
     */
    public static function activation($code, $token = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            $error                                                  = "";
            $token                                                  = ($token
                                                                        ? $token
                                                                        : self::getReq("token")
                                                                    );

            if(is_array($res) && $res["status"] !== "0" && $opt["exit"])
                self::endScript($res);
        }
        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function recover($opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {

        }
        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);
    }

    public static function refresh() {


    }

    /**
     * @param null $request
     * @param null $opt
     * @return array|mixed|null
     */
    public static function registration($request = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        if(is_array($request) && !$opt) {
            $opt                                                    = $request;
            $request                                                = null;
        }
        $opt                                                        = self::getOpt($opt);

        $res                                                        = self::isInvalidReqMethod($opt["exit"]);
        if(!$res) {
            if(!$request)                                           $request = self::getData($request);

            $username                                               = self::getReq("username");
            $password                                               = self::getReq("password");

            if($username && $password) {
                if(!$opt["domain"])                                 $opt["domain"] = self::getReq("domain");

                $security                                           = self::security($opt);
                if(isset($security["status"]) && $security["status"] === "0") {
                    $request["username"]                            = $username;
                    $request["password"]                            = $password;

                    $req                                            = Auth::getReqBySchema($request, $opt["type"]);

                    $req["acess.users.ID_domain"]                   = $security["domain"]["ID"];

                    if(!$opt["activation"]) {
                        $req["access.users.status"]                 = true;
                    }
                    if($opt["token"]) {
                        $req["access.tokens.token"]                 = self::createHash($username . "-" . $password);
                        $req["access.tokens.type"]                  = ($security["domain"]["security"]["token_type"]
                                                                        ? $security["domain"]["security"]["token_type"]
                                                                        : Auth::TOKEN_TYPE
                                                                    );
                        $req["access.tokens.expire"]                = ($security["domain"]["security"]["token_expire"]
                                                                        ? $security["domain"]["security"]["token_expire"]
                                                                        : Auth::TOKEN_EXPIRE
                                                                    );
                    }

                    /* $req = array(
                         "name" => "pippo"
                         , "surname" => "pluto"
                         , "anagraph.email" => "asd@as.it"
                         , "anagraph.tel" => "asd@as.it"
                         , "anagraph_type.name" => "Medico"
                         //, "access.groups.name" => "Medico"

                         , "access.users.username" => "ASD"
                         , "access.users.password" => "password"
                         , "access.tokens.token" => self::createHash($user_key)
                         , "access.tokens.type" => "live"
                         , "access.tokens.expire" => "0"

                     );*/



                    $user                                           = Anagraph::getInstance();
                    $return                                         = $user->insert($req);

                    if(is_array($return)) {
                        $res["status"]                              = "0";
                        $res["error"]                               = "";
                        if($opt["token"])                           $res["token"] = $req["access.tokens.token"];
                    } else {
                        $res["status"]                              = "410";
                        $res["error"]                               = $return;
                    }
                } else {
                    $res                                            = $security;
                }
            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = "username and password Required";
            }
        }

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
	}

	public static function share($master, $slave = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        $res = array();

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function join($master, $slave, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        $res = array();

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

	public static function key($scopes = null, $token = null, $opt = null) {
        if(DEBUG_PROFILING === true)                                $start = Stats::stopwatch();

        $opt                                                        = self::getOpt($opt);
        $return                                                     = self::check(
                                                                        $token
                                                                        , array(
                                                                            "exit"                  => $opt["exit"]
                                                                            , "fields"              => array(
                                                                                "ID"                => "key"
                                                                            )
                                                                        )
                                                                    );
        if(isset($return["status"]) && $return["status"] === "0") {
            if(!$scopes)                                            $scopes = self::getReq("scopes");
            if($scopes) {
                $security                                           = self::security($opt, $scopes);
                if(isset($security["status"]) && $security["status"] === "0") {
                    $select                                         = ($opt["fields"]
                                                                        ? $opt["fields"]
                                                                        : self::getData()
                                                                    );
                    $select["ID"]                                   = "key";

                    //status 1 e 0 nella where se funziona correttamnte
                    //fare un read partendo da anagraph e discendendo nei sotto elementi access e domain
                    // verificare in insert expire che venga scritto correttamente

                    //usare per il certificato questo openssl_csr_new
                    //trovare sistema per la get con le chiavi con i punti per la registrazione
                    $anagraph                                       = Anagraph::getInstance()->read(
                                                                        $select
                                                                        , array(
                                                                            "ID_user"       => $return["key"]
                                                                        )
                                                                    );

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
                            $res                                    = $mc;
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
                    $res                                            = $security;
                }
            } else {
                $res["status"]                                      = "400";
                $res["error"]                                       = "Scope not Set";
            }
        }

        if(DEBUG_PROFILING === true && is_array($res))              $res["exTime"] = Stats::stopwatch($start);

        return $res;
    }

    public static function createCertificate($secret = null, $opt = null) {
        $opt                                                        = self::getOpt($opt);
        $secret                                                     = ($secret
                                                                        ? $secret
                                                                        : self::getReq("password")
                                                                    );

        $domain                                                     = self::getDomain(
                                                                        $opt["domain"]
                                                                        , $opt["client_id"]
                                                                        , $opt["client_secret"]
                                                                    );

        if(isset($domain["status"]) && $domain["status"] === "0") {
            require_once __DIR__ . "/AuthCertificate.php";

            $certificate                                            = new AuthCertificate(self::domain4certificate($domain));

            $res                                                    = $certificate->createCertificate($secret);

            unset($secret);
        } else {
            $res                                                    = $domain;
        }

        return $res;
    }

    private static function security($opt, $scopes = null) {
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



        $domain                                                     = self::getDomain(
                                                                        $opt["domain"]
                                                                        , $opt["client_id"]
                                                                        , $opt["client_secret"]
                                                                        , $scopes
                                                                    );

        if(isset($domain["status"]) && $domain["status"] === "0") {
            $res                                                    = self::getCertificate(self::domain4certificate($domain), $domain["secret"]);
            if(isset($res["status"]) && $res["status"] === "0")
                $res["domain"]                                      = $domain;
        } else {
            $res                                                    = $domain;
        }

        unset($domain);

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
    private static function schema($type) {
        $schema = array();
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
                                                                    );

        switch($type) {
            case "person":
                $schema                                             = array_merge($def, array(
                                                                        "name"              => array(
                                                                                                "anagraph_person.name"
                                                                                            )
                                                                        , "surname"         => array(
                                                                                                "anagraph_person.surname"
                                                                                            )
                                                                    ));
                break;
            case "company":
                break;
            default:
                $schema                                             = $def;
        }





        return $schema;
    }

    private static function getReqBySchema($request, $ext = null) {
        $req                                                        = array();
        $schema                                                     = self::schema($ext);
        foreach($request AS $key => $value) {
            if($schema[$key]) {
                if(is_array($schema[$key])) {
                    foreach($schema[$key] AS $subkey) {
                        $req[$subkey]                               = $value;
                    }
                } else {
                    $req[$schema[$key]]                             = $value;
                }
            }
        }

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
                                                                        : Auth::CERTIFICATE_KL
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
                                                                        : Auth::CERTIFICATE_KD
                                                                    );
        $p                                                          = ($certificate
                                                                        ? $certificate->index("kp")
                                                                        : Auth::CERTIFICATE_KP
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
                        $arrC[] = array_search($n, $arrKD);
                        $n--;
                    }
                    $arrC = array_merge($arrC, $arrC, $arrC, $arrC, $arrC);
                    for ($i = 0; $i < $collision; $i++) {
                        $c = $auk[$arrC[$i]] + strlen($bnp);
                        if($c > 9)
                            $c = $c - 10;

                        $auk[$arrC[$i]] = $c;
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
            if(count($arrK) == $i)
                $i                                                  = 0;
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
                $opt                                                    = self::getOpt();
                $client_id                                              = $opt["client_id"];
                $client_secret                                          = $opt["client_secret"];
            }
            if($client_id && $client_secret) {
                $client                                                 = Anagraph::getInstance("domain")->read(array(
                                                                            "clients.client_id"         => "client_id"
                                                                            , "clients.domains"         => "domains"
                                                                            , "clients.disable_csrf"    => "disable_csrf"
                                                                            , "clients.grant_types"     => "grant_types"
                                                                        ), array(
                                                                            "clients.client_id"         => $client_id
                                                                            , "clients.client_secret"   => $client_secret
                                                                        ));
                if(is_array($client)) {
                    $res                                                = $client;
                    $res["status"]                                      = "0";
                    $res["error"]                                       = "";
                } else {
                    $res["status"]                                      = "410";
                    $res["error"]                                       = "Client not Found";
                }
            } else {
                $res["status"]                                          = "400";
                $res["error"]                                           = "missing client_id or client_secret";
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
                                                                            , "expiration"
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
                                    $valid_scope                    = true;
                                    if($scopes && $domain["scopes"]) {
                                        $arrScopes                  = explode("," , $scopes);
                                        $arrDomainScopes            = explode(",", $domain["scopes"]);

                                        $arrDiffScopes              = array_diff($arrScopes, $arrDomainScopes);
                                        $valid_scope                = (count($arrDiffScopes)
                                                                        ? false
                                                                        : true
                                                                    );
                                    }

                                    if($valid_scope) {
                                        unset($domain["expiration"]);
                                        unset($domain["status"]);

                                        $res                        = $domain;
                                        $res["client"]              = $client;
                                        $res["status"]              = "0";
                                        $res["error"]               = "";
                                    } else {
                                        $res["status"]              = "403";
                                        $res["error"]               = "Scope not Permitted" . (Auth::DEBUG
                                                                        ? ": " . implode($arrDiffScopes, ", ")
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


    private static function createHash($key = null) {
        if(!$key)                                           $key = time();

        return sha1(APPID . $key);
    }
    /**
     * @param null $service
     * @return mixed
     */
    private function getService($service = null) {
        $controller = $this->getControllerName($service ? $service : $this->service);

        return new $controller($this);
    }

    /**
     * @param null $request
     * @param string $method
     * @return array
     */
    private static function getData($request = null, $method = Auth::REQUEST_METHOD) {
        if(!$request)                                       $request = self::getReq(null, $method);
        $return                                             = array_diff_key($request, array_fill_keys(self::$request, true));

        foreach($return AS $key => $value) {
            $real_key                                       = str_replace("_", ".", $key);
            if($value)
                $res[$real_key]                             = $value;
            else
                $res[]                                      = $real_key;

        }
        return $res;
    }

    /**
     * @param null $key
     * @param null $method
     * @return mixed
     */
    private static function getReq($key = null, $method = Auth::REQUEST_METHOD) {
        switch(strtolower($method)) {
            case "post":
            case "patch":
            case "delete":
                $req                                        = $_POST;
                break;
            case "get":
                $req                                        = $_GET;
                break;
            case "cookie":
                $req                                        = $_COOKIE;
                break;
            case "session":
                $req                                        = $_SESSION;
                break;
            default:
                $req                                        = $_REQUEST;

        }

        return ($key
            ? $req[self::$request[$key]]
            : $req
        );
    }

    /**
     * @param $method
     * @param bool $exit
     * @return mixed
     */
    private static function isInvalidReqMethod($exit = false, $method = Auth::REQUEST_METHOD) {
        if($_SERVER["REQUEST_METHOD"] != $method) {
            $res["status"]                                  = "405";
            $res["error"]                                   = "Request Method Must Be " . $method;

            if($exit)
                self::endScript($res);
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
        return Auth::TYPE . ucfirst($service);
    }

}
