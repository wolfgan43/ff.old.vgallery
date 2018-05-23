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

class Cache extends vgCommon
{
	const BASE_PATH 					= "/cache";

    static $singleton                 	= null;

    private $Sem                        = array();

    protected $save_path                = null;
    protected $status_code              = null;
    protected $media                    = array(
                                        	"url" 							=> "/media"
                                        	, "path" 						=> "/cache/_thumb"
                                        	, "showfiles" 					=> "/cm/showfiles.php"
                                        );
    private $schema                     = array();

    private $protocol                   = "HTTP/1.0";
    private $response_code              = 200;
	private $request_uri                = null;
	private $query_string               = null;
	private $path_info                  = null;

	private $get                        = null;
	private $post                       = null;

    private $result                     = array();

    /**
     * @param null $save_path
     * @param null $status_code
     * @return Cache|null
     */
    public static function getInstance($services = null, $save_path = null)
    {
        if (self::$singleton === null) {
            self::$singleton                                            = new Cache($services, $save_path);
        } else {
			self::$singleton->setServices($services);
        }

        return self::$singleton;
    }

    /**
     * Cache constructor.
     * @param null $save_path
     * @param null $status_code
     */
    public function __construct($services = null)
    {
        $this->loadControllers(__DIR__);

        $this->setServices($services);

        $this->protocol                                                 = (isset($_SERVER['SERVER_PROTOCOL'])
                                                                            ? $_SERVER['SERVER_PROTOCOL']
                                                                            : $this->protocol
                                                                        );

        //Previene il blocco della pagina in caso di errori o di interruzione non gestita
        register_shutdown_function(function() {
            $this->sem_release("shutdown");
        });

        $this->initRequest();
	}


    public function run($user_path = null)
    {
		$path_info = $this->user_path_allowed($user_path);

        $cache_params = $this->get_page($path_info);
        if(is_array($cache_params)) { //da verificare il nocache
            $schema = $this->get_page_settings();
            $rule["path"] = $cache_params["user_path"];

            if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                $rule["path"] = $cache_params["strip_path"] . $rule["path"];

            $request_path = rtrim($cache_params["alias"] . $rule["path"], "/");
            if(!$request_path)
                $request_path = "/";

            $rule["split_path"] = explode("/", $request_path);
            if(isset($schema["request"][$request_path])) {
                $rule["match"] = $schema["request"][$request_path];
                //} elseif(0 && isset($schema["request"]["/" . $rule["split_path"][0]])) {
                //    $rule["match"] = $schema["request"]["/" . $rule["split_path"][0]];
            } elseif(isset($schema["request"][$rule["split_path"][count($rule["split_path"]) - 1]])) {
                $rule["match"] = $schema["request"][$rule["split_path"][count($rule["split_path"]) - 1]];
            } else {
                do {
                    $request_path = dirname($request_path);
                    if(isset($schema["request"][$request_path])) {
                        $rule["match"] = $schema["request"][$request_path];
                        break;
                    }
                } while($request_path != DIRECTORY_SEPARATOR); //todo: DS check
            }

            if($rule["match"]["ext"] && is_array($schema["request"][$rule["match"]["ext"]]))
                $rule["match"] = array_merge_recursive($schema["request"][$rule["match"]["ext"]], $rule["match"]);

            $request = $this->get_request($rule["match"]);
            if($request["nocache"]) {
                $cache_params = false;
            } elseif(isset($request["valid"])) {
                $this->do_redirect($_SERVER["HTTP_HOST"] . $path_info . $request["valid"]);
            }
            //necessario XHR perche le request a servizi esterni path del domain alias non triggerano piu
            if(!$this->save_path && $cache_params["redirect"] && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {// Evita pagine duplicate quando i link vengono gestiti dagli alias o altro
                $this->do_redirect($cache_params["redirect"] . $request["valid"]);
            }
        }

        //Gestisce gli errori delle pagine provenienti da apachee con errorDocument nell'.htaccess
        if($cache_params === true) {
            $this->send_header_content(null, false, false, false);

            if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                $this->http_response_code(500);
            } else {
                if(strpos($_SERVER["REQUEST_URI"], $this->media["url"]) === 0) {
                    $this->do_redirect(str_replace($this->media["url"], $_SERVER["HTTP_HOST"] . $this->media["showfiles"], $_SERVER["REQUEST_URI"]), 307);
                }

                $this->http_response_code(404);
            }

            Cache::log(" URL: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_error_apache");
            exit;
        }

        if(!$cache_params) {
            if($cache_params === false && !$this->save_path) {
                define("DISABLE_CACHE", true);
            }
            return false;
        }

        $cache_params["settings"] = $schema;

        $this->check_lang($cache_params);
        $cache_file = $this->get_page_filename($cache_params, $request);

        if(!$this->save_path && $cache_file["invalid"]) {
            if($cache_file["is_error_document"] && !$cache_file["noexistfileerror"] && $cache_file["noexistfile"]) {
                $cache_file["invalid"] = false;
                $cache_file["filename"] = "index";
                if($cache_file["compress"])
                    $cache_file["primary"] = "index.gz";
                else
                    $cache_file["primary"] = "index.html";

                $cache_file["gzip"] = "index.gz";
            } elseif($cache_file["noexistfile"]) {
                $this->Sem[] = $this->sem($cache_file["cache_path"]);
                if (is_file($cache_file["cache_path"] . "/" . $cache_file["primary"])) {
                    $cache_file["invalid"] = false;
                } else {
                    $this->Sem[] = $this->sem("create");
                }
            } elseif($cache_file["noexistfileerror"]) {
                $this->Sem[] = $this->sem($cache_file["error_path"]);
            } elseif(!defined("DISABLE_CACHE")) {
                if(is_array($schema["priority"]) && array_search($path_info, $schema["priority"]) !== false) {
                    @touch($cache_file["cache_path"] . "/" . $cache_file["primary"], time() + 10); //evita il multi loading di pagine in cache
					Cache::log($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update_primary");
                } else {
                    $sem = $this->sem("update", true);
                    if($sem["acquired"]) {
                        @touch($cache_file["cache_path"] . "/" . $cache_file["primary"], time() + 10); //evita il multi loading di pagine in cache
						Cache::log($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update");
                    } else {
                        $cache_file["invalid"] = false;
						Cache::log($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update_queue");
                    }
                    $this->Sem[] = $sem;
                }
            }
        }

        if($cache_file["invalid"]) {
            //define("DISABLE_CACHE", true);
            $cache_exit = true;
        } else {
            $ff_contents = $this->check_ff_contents($path_info, $cache_file["last_update"]);
            if($ff_contents["cache_invalid"]) {
                //define("DISABLE_CACHE", true);
                $cache_exit = true;
            }
        }
		/*
        if($this->save_path) {
            // $this->sem_release();
            return array(
                "file" => $cache_file
                , "user_path" => $path_info
                , "params" => $cache_params
                , "request" => $request
                , "ff_count" => $ff_contents["count"]
                , "sem" => &$this->Sem
            );
        }*/

        if(defined("DEBUG_MODE") && isset($_REQUEST["__nocache__"])) {
            $_REQUEST["__CLEARCACHE__"] = true;
            define("DISABLE_CACHE", true);

            $this->sem_remove($cache_file["cache_path"]);
            $this->send_header_content(null, false, false, false);

            if($cache_file["error_path"])
                $this->set_error_document($cache_file["error_path"], $cache_params);

            if(is_file($cache_file["cache_path"] . "/" . $cache_file["primary"])) {
                if($cache_file["primary"] != $cache_file["gzip"])
                    @unlink($cache_file["cache_path"] . "/" . $cache_file["primary"]);

                @unlink($cache_file["cache_path"] . "/" . $cache_file["gzip"]);
            }
        }

        if($cache_exit) {
            $load = sys_getloadavg();
            if ($load[0] > 80) {
                $this->sem_release();

                $this->send_header_content(null, false, false, false);
                $this->http_response_code(503);

                readfile($this->getAbsPath("/themes/" . $this->getTheme("cms") . "/contents/error_cache.html"));
                exit;
            } else {
                if(!count($this->Sem))
                    $this->Sem[] = $this->sem();

                return false;
            }
        }

        $this->sem_release();

        if(!defined("DISABLE_CACHE"))
        {
            if($cache_file["is_error_document"])
            {
                //redirect
                require_once($this->getAbsPathPHP("/library/gallery/system/gallery_redirect"));

                system_gallery_redirect($path_info, $request["valid"]);
                if($this->status_code === null)
                {
                    $this->http_response_code($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"
                        ? 500
                        : 404
                    );
                }
            }

            $this->parse($cache_file, $cache_params["lang"], $cache_params["auth"], $request["get"]);
        }
    } //check_static_cache_page

    //todo: da trovare e sistemare
    /**
     * @param null $type
     * @param null $name
     * @param null $default
     * @return array|null
     */
    public function get_page_settings($type = null, $name = null, $default = null) {
        if(!$this->schema) {
            require($this->getAbsPathPHP("/library/" . $this->getTheme("cms") . "/settings"));

            $settings_path = $this->getAbsPathPHP("/themes/" . $this->getTheme("cms")   . "/settings");
            if(is_file($settings_path))
                require($settings_path);

            $locale_path = $this->getAbsPathCachePHP("/locale");
            if(is_file($locale_path)) {
                require($locale_path);

                /** @var include $locale */
                $schema["locale"] = $locale;
            }

        }

        if(is_array($default) && count($default)) {
            if($type && $name && is_array($schema[$type][$name]) && count($schema[$type][$name]) && is_array($default)) {
                return array_replace_recursive($default, $schema[$type][$name]);
            } elseif($type && is_array($schema[$type]) && count($schema[$type]) && is_array($default)) {
                return array_replace_recursive($default, $schema[$type]);
            } else {
                return array_replace_recursive($default, $schema);
            }
        }

        if($type)
            return $schema[$type];
        else
            return $schema;
    }

    //todo: da trovare e sistemare
    /**
     * @param $get
     * @param null $post
     * @param null $rule
     * @return null
     */
    public function get_request($rule = null) { //cache_get_request
        $res 				= null;
        $arrRuleGet 		= array();
        $filter = array(
            "ffl" 			=> true
			, "pci" 		=> true
			, "ppi" 		=> true
			, "pri" 		=> true
			, "psi" 		=> true
			, "pcn" 		=> true
			, "ppn" 		=> true
			, "prn" 		=> true
			, "psn" 		=> true
			, "pps" 		=> true
			, "pss" 		=> true
        );

        $get 				= $this->get;
		$post 				= $this->post;

        /**
         * COOKIE
         */
        if(is_array($_COOKIE) && count($_COOKIE)) {
            $cookie_match = array_intersect_key($_COOKIE, $filter);
            foreach($cookie_match AS $cookie_key => $cookie_value) {
                if(!$get[$cookie_key])
                    $get[$cookie_key] = $cookie_value;
            }
        }
        /**
         * GET
         */
        if(is_array($get) && count($get)) {
            if($rule["get"] === true) {
                $res["get"]["query"]["all"] = http_build_query($get);
            } elseif($rule["get"] === "xhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                $res["get"]["query"]["all"] = http_build_query($get);
            } elseif($rule["get"] === "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
                $res["get"]["query"]["all"] = http_build_query($get);
            } else {
                if(is_array($rule["get"]))
                    $arrRuleGet = array_flip($rule["get"]);

                foreach($get AS $req_key => $req_value) {
                    if(is_array($get[$req_key]))
                        continue;

                    if(is_array($req_value))
                        continue;

                    /* assrde condizioni
                                        if(!$arrRuleGet && !strlen($req_value))
                                            continue;

                                        if(!$arrRuleGet && is_numeric($req_key) && !$req_value)
                                            continue;
                    */
                    switch($req_key) {
                        case "_ffq_":
                            break;
                        case "gclid": //params di google adwords e adsense
                        case "utm_source":
                        case "utm_medium":
                        case "utm_campaign":
                        case "utm_term":
                        case "utm_content":
                            $res["get"]["gparams"][$req_key] = $req_value;
                            break;
                        case "q":
                            $res["get"]["search"]["term"] = $req_value;
                            $res["get"]["search"]["params"]["q"] = "q=" . urlencode($req_value);

                            $res["get"]["query"]["q"] = $res["get"]["search"]["params"]["q"]; //forse da aggiungere "q=" come negli altri sotto
                            break;
                        case "page":
                            if(is_numeric($req_value) && $req_value > 0) {
                                $res["get"]["navigation"]["page"] = $req_value;
                                if($req_value > 1)
                                    $res["get"]["query"]["page"] = "page=" . urlencode($res["get"]["navigation"]["page"]);
                            }
                            break;
                        case "count":
                            if(is_numeric($req_value) && $req_value > 0) {
                                $res["get"]["navigation"]["rec_per_page"] = $req_value;

                                $res["get"]["query"]["count"] = "count=" . urlencode($res["get"]["navigation"]["rec_per_page"]);
                            }
                            break;
                        case "sort":
                            $res["get"]["sort"]["name"] = $req_value;

                            $res["get"]["query"]["sort"] = "sort=" . urlencode($res["get"]["sort"]["name"]);
                            break;
                        case "dir":
                            $res["get"]["sort"]["dir"] = $req_value;

                            $res["get"]["query"]["dir"] = "dir=" . urlencode($res["get"]["sort"]["dir"]);
                            break;
                        case "ffl": //Filter By Letter //gestire meglio i filtri. troppo macchinoso
                            $res["get"]["filter"]["first_letter"] = $req_value;

                            $res["get"]["query"]["ffl"] = "ffl=" . urlencode($res["get"]["filter"]["first_letter"]);
                            break;
                        case "pci": //Filter By Place City ID
                            $res["get"]["filter"]["place"]["city"]["ID"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["pci"] = "pci=" . urlencode($res["get"]["filter"]["place"]["city"]["ID"]);
                            break;
                        case "ppi": //Filter By Place Province ID
                            $res["get"]["filter"]["place"]["city"]["ID_province"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["ppi"] = "ppi=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_province"]);
                            break;
                        case "pri": //Filter By Place Region ID
                            $res["get"]["filter"]["place"]["city"]["ID_region"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["pri"] = "pri=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_region"]);
                            break;
                        case "psi": //Filter By Place State ID
                            $res["get"]["filter"]["place"]["city"]["ID_state"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["psi"] = "psi=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_state"]);
                            break;
                        case "pcn": //Filter By Place City Smarturl
                            $res["get"]["filter"]["place"]["city"]["smart_url"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["pcn"] = "pcn=" . urlencode($res["get"]["filter"]["place"]["city"]["smart_url"]);
                            break;
                        case "ppn": //Filter By Place Province Smarturl
                            $res["get"]["filter"]["place"]["city"]["province_smart_url"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["ppn"] = "ppn=" . urlencode($res["get"]["filter"]["place"]["city"]["province_smart_url"]);
                            break;
                        case "prn": //Filter By Place Region Smarturl
                            $res["get"]["filter"]["place"]["region"]["smart_url"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["prn"] = "prn=" . urlencode($res["get"]["filter"]["place"]["region"]["smart_url"]);
                            break;
                        case "psn": //Filter By Place State Smarturl
                            $res["get"]["filter"]["place"]["state"]["smart_url"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["psn"] = "psn=" . urlencode($res["get"]["filter"]["place"]["state"]["smart_url"]);
                            break;
                        case "pps": //Filter By Place Province Sigle
                            $res["get"]["filter"]["place"]["city"]["province_sigle"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["pps"] = "pps=" . urlencode($res["get"]["filter"]["place"]["city"]["province_sigle"]);
                            break;
                        case "pss": //Filter By Place State Sigle
                            $res["get"]["filter"]["place"]["state"]["sigle"] = $this->nomalize_request($req_value);
                            $res["get"]["query"]["pss"] = "pss=" . urlencode($res["get"]["filter"]["place"]["state"]["sigle"]);
                            break;
                        case "ret_url":
                        case "lang":
                            break;
                        default:
                            if(isset($arrRuleGet[$req_key])) {
                                $res["get"]["search"]["available_terms"][$req_key] = $req_value;
                                $res["get"]["query"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
                            } elseif($arrRuleGet[$req_key] === false) {
                                $res["get"]["invalid"][$req_key] = $req_key . "=" . urlencode($req_value);
                            } elseif(!preg_match('/[^a-z\-0-9_\+]/i', $req_key)) {
                                $res["get"]["search"]["available_terms"][$req_key] = $req_value;
                                //$res["get"]["query"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
                                $res["get"]["invalid"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
                            }
                    }
                }

                if(is_array($res["get"]["query"]) && count($res["get"]["query"]) != count($get))
                    $rule["get"] = false;
            }

            if(is_array($rule["nocache"])) {
                foreach($rule["nocache"] AS $req_key) {
                    if(array_key_exists($req_key, $get)) {
                        $res["nocache"] = true;
                        break;
                    }
                }
            } elseif($rule["nocache"] === true) {
                if(is_array($res["get"]["query"]) && count($res["get"]["query"]))
                    $res["nocache"] = true;
            }


            //ricava la query valida
            if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
                if(!(defined("DEBUG_MODE") && (isset($get["__nocache__"]) || isset($get["__debug__"]) || isset($get["__query__"])))) {
                    if(is_array($res["get"]["invalid"]) && count($res["get"]["invalid"])
                        || ($rule["get"] === false && count($get) != count($res["get"]["query"]))
                    ) {
                        $res["valid"] = (is_array($res["get"]["query"]) && count($res["get"]["query"])
                            ? "?" . implode("&", $res["get"]["query"])
                            : ""
                        );
                    }
                }
            }


            if($rule["log"]) {
                if(is_array($res["get"]["invalid"]))
					Cache::log("URL: " . $_SERVER["PATH_INFO"] . (is_array($res["get"]["query"]) ? " GET: " . implode("&", $res["get"]["query"]) : "") . " GET INVALID: " . implode("&", $res["get"]["invalid"]) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
                elseif($rule["get"] === false)
					Cache::log("URL: " . $_SERVER["PATH_INFO"] . " GET: " . http_build_query($get) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
            }
        }

        /**
         * POST
         */
        if(is_array($post) && count($post)) {
            if($rule["post"]) {
                if(is_array($rule["post"])) {
                    foreach($rule["post"] AS $req_key) {
                        if(array_key_exists($req_key, $post)) {
                            $res["post"]["search"]["available_terms"][$req_key] = $post[$req_key];
                            if(is_array($res["post"]["search"]["available_terms"][$req_key]))
                                $res["post"]["query"][$req_key] = $req_key . "=" . http_build_query($res["post"]["search"]["available_terms"][$req_key]);
                            else
                                $res["post"]["query"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                        }
                    }

                    foreach($post AS $req_key => $req_value) {
                        if(array_search($req_key, $rule["post"]) === false) {
                            if(is_array($req_value))
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . http_build_query($req_value);
                            else
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . urlencode($req_value);
                        }
                    }
                } elseif($rule["post"] === true) {
                    $res["post"]["query"]["all"] = http_build_query($post);
                } elseif($rule["post"] === "xhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                    $res["post"]["query"]["all"] = http_build_query($post);
                } elseif($rule["post"] === "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
                    $res["post"]["query"]["all"] = http_build_query($post);
                }
            } else {
                if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
                    $post = array();

                foreach($post AS $req_key => $req_value) {
                    if(is_array($post[$req_key]))
                        continue;

                    switch($req_key) {
                        case "ret_url":
                        case "lang":
                            break;
                        default:
                            if($rule["post"] === true || !preg_match('/[^a-z\-0-9]/i', $req_key)) {
                                $res["post"]["search"]["available_terms"][$req_key] = $req_value;
                                $res["post"]["query"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                            } else {
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                            }
                    }
                }

                if($rule["post"] === false)
                    $res["post"] = null;

            }

            if(is_array($res["post"]["query"]) && count($res["post"]["query"]))
                $res["nocache"] = true;

            if($rule["log"]) {
                if(is_array($res["post"]["invalid"]))
					Cache::log("URL: " . $_SERVER["PATH_INFO"] . (is_array($res["post"]["query"]) ? " POST: " . implode("&", $res["post"]["query"]) : "") . " POST INVALID: " . implode("&", $res["post"]["invalid"]) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
                elseif($rule["post"] === false)
					Cache::log("URL: " . $_SERVER["PATH_INFO"] . " GET: " . http_build_query($post) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
            }
        }

        return $res;
    }

    //todo: da trovare e sistemare
    /**
     * @param $page
     * @param null $domain_name
     * @return array|null
     */
    public function get_locale(&$page, $domain_name = null) {
        static $localeLoaded = null;

        if($localeLoaded) {
            $locale = $localeLoaded;
        } else {
            $locale = $this->get_locale_settings($page["session"]);

            //recupero della lingua dai cookie
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" && strlen($_COOKIE["lang"]))
                $lang = strtoupper($_COOKIE["lang"]);

            //recupero della lingua se forzata per le aree riservate
            if(!$lang && defined("LANGUAGE_RESTRICTED_DEFAULT") && $page["restricted"]) {
                $lang = LANGUAGE_RESTRICTED_DEFAULT;

                if(!defined("LANGUAGE_DEFAULT_TINY"))
                    define("LANGUAGE_DEFAULT_TINY", strtolower(substr(LANGUAGE_RESTRICTED_DEFAULT, 0, 2)));
            }

            //recupero della lingua dal percorso base del sito es: /en/[path_info]
            if(!$lang)
                $lang = $page["lang"];

            //recupero della lingua dal nome dominio del sito es: .it .ru
            if(!$lang && $domain_name !== false)
            {
                if($domain_name === null)
                {
                    if(strpos(strtolower($_SERVER["HTTP_HOST"]), "www.") === 0) {
                        $domain_name = substr($_SERVER["HTTP_HOST"], strpos($_SERVER["HTTP_HOST"], ".") + 1);
                    } else {
                        $domain_name = $_SERVER["HTTP_HOST"];
                    }

                    if(strpos($domain_name, ":") !== false)
                        $domain_name = substr($domain_name, 0, strpos($domain_name, ":"));
                }
                //$arrState = cache_get_state_available();
                $lang = $locale["rev"]["country"][strtolower(substr($domain_name, strrpos($domain_name, ".") + 1))];
            }

            if(!$lang)
                $lang = $locale["lang"]["current"]["code"];

            if($locale["lang"][$lang]["ID"]) {
                $ID_lang = $locale["lang"][$lang]["ID"];
            } else {
                $lang = LANGUAGE_DEFAULT;
                $ID_lang = LANGUAGE_DEFAULT_ID;
            }

            if(!defined("LANGUAGE_DEFAULT_TINY"))
                define("LANGUAGE_DEFAULT_TINY", strtolower(substr(LANGUAGE_DEFAULT, 0, 2)));

            if(!defined("FF_LOCALE"))
            {
                define("LANGUAGE_INSET_TINY", strtolower(substr($lang, 0, 2)));
                define("LANGUAGE_INSET", $lang);
                define("LANGUAGE_INSET_ID", $ID_lang);
                define("FF_LOCALE", $lang);
            }

            $locale["prefix"] = ($lang == LANGUAGE_DEFAULT
                ? ""
                : "/" . $locale["lang"][$lang]["tiny_code"]
            );
            $locale["lang"]["current"] =  $locale["lang"][$lang];

            $localeLoaded = $locale;
        }

        if($locale["prefix"])
        {
            if($page["user_path"] == $locale["prefix"])
                $page["user_path"] = "/";
            elseif(strpos($page["user_path"], $locale["prefix"]) === 0)
                $page["user_path"] = substr($page["user_path"], strlen($locale["prefix"]));
        }

        return $locale;
    }

    /**
     * @param $path
     * @return string
     */
    public function getAbsPathCache($path)
    {
        return $this->getAbsPath($this::BASE_PATH . $path);
    }

    /**
     * @param $path
     * @return string
     */
    public function getAbsPathCachePHP($path)
    {
        return $this->getAbsPathCache($path . "." . $this::PHP_EXT);
    }

    /*****************************************************************
     * OUTPUT: HEADER
     *****************************************************************/
    /**
     * @param $file
     * @param null $type
     * @param bool $compress
     * @param null $max_age
     * @param null $expires
     * @param bool $enable_etag
     */
    private function send_header($file, $type = null, $compress = false, $max_age = null, $expires = null, $enable_etag = false) {
        //if (strlen($_SERVER["HTTP_IF_NONE_MATCH"]) && substr($_SERVER["HTTP_IF_NONE_MATCH"], 0, strlen($etag)) == $etag)
        //    $compress = false;
        $this->send_header_content($type, $compress, $max_age, $expires, filesize($file));

        if($enable_etag) {
            $etag = md5($file . filemtime($file) . ($enable_etag === true ? "" : $enable_etag));
            header("ETag: " . $etag);

            if (strlen($_SERVER["HTTP_IF_NONE_MATCH"]) && substr($_SERVER["HTTP_IF_NONE_MATCH"], 0, strlen($etag)) == $etag)
            {
                /*if($max_age !== false) {
                    if($max_age === null)
                        $max_age = 60;
                    $max_age_multiplier = ceil((time() - strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) / $max_age) * $max_age;

                    header("Cache-Control: public, max-age=" . $max_age_multiplier);
                }*/

                $this->http_response_code(304);
                exit;
            }
        }
    }
    /**
     * @param null $type
     * @param null $compress
     * @param null $max_age
     * @param null $expires
     * @param null $length
     * @param string $pragma
     */
    private function send_header_content($type = null, $compress = null, $max_age = null, $expires = null, $length = null, $pragma = "!invalid")
    {
        //if(!defined("CM_PAGECACHE_KEEP_ALIVE"))
        //    define("CM_PAGECACHE_KEEP_ALIVE", true);

        //if(CM_PAGECACHE_KEEP_ALIVE)
        header("Connection: Keep-Alive");

        if($compress !== false && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false) {
            header("Content-Encoding: gzip");
        }

        switch($type)
        {
            case "xml":
                header("Content-type: text/xml");
            case "json":
                header("Content-type: text/javascript");
                break;
            case "html":
            default:
                header("Content-type: text/html; charset=UTF-8");

        }

        if ($expires !== false)
        {
            if ($expires === null)
            { //da abilitare il max age per vedere se gli piace a webpagetest
                $expires = time() + (60 * 60 * 24 * 7);
                //$expires = time() + 7;
            }
            elseif ($expires < 0)
            {
                $expires = time() - $expires;
            }
            $exp_gmt = gmdate("D, d M Y H:i:s", $expires) . " GMT";
            header("Expires: $exp_gmt");
        }

        if ($max_age !== false)
        {
            if ($max_age === null)
            {
                $max_age = 3;
                header("Cache-Control: public, max-age=$max_age");
            }
            else
            {
                header("Cache-Control: public, max-age=$max_age");
            }
        }

        if($expires === false && $max_age === false)
        {
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

            $expires = time() - 1;
            $exp_gmt = gmdate("D, d M Y H:i:s", $expires) . " GMT";
            header("Expires: $exp_gmt");

            $pragma = "no-cache";
        } else {
            $mod_gmt = gmdate("D, d M Y H:i:s", time()) . " GMT";
            header("Last-Modified: $mod_gmt");
        }

        if($pragma)
            header("Pragma: " . $pragma);

        if($length)
            header("Content-Length: " . $length);

        header("Vary: Accept-Encoding");
    }

    /*****************************************************************
     * OUTPUT : PARSE
     *****************************************************************/
    private function parse($cache_file, $lang, $group = "guests") {
        $target_file = "";
        $compress = false;
        $cache_path = ($cache_file["is_error_document"]
            ? $cache_file["error_path"]
            : $cache_file["cache_path"]
        );

        //$cache_file["compress"] = true;
        if(strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") === false) {
            $target_file = $cache_path . "/" . $cache_file["primary"];
        } elseif($cache_file["compress"] && $cache_file["gzip"]) {
            $compress = true;
            $target_file = $cache_path . "/" . $cache_file["gzip"];
        } else {
            $target_file = $cache_path . "/" . $cache_file["primary"];
        }

        if(strlen($target_file)) {
            // header_remove();
            //clearstatcache();

            /* if($cache_file["last_update"]) {
                 if(filemtime($target_file) != $cache_file["last_update"])
                     return false;
             } else {
                 if(filemtime($target_file) >= filectime($target_file))
                     return false;
             }*/
            //define("CACHE_PAGE_STORING_PATH", $cache_file["cache_path"] . "/" . $cache_file["filename"]);

            if(!$cache_file["is_error_document"] && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" && TRACE_VISITOR === true) {
                require_once($this->getAbsPathPHP("/library/" . $this->getTheme("cms") . "/system/trace.php"));
                system_trace("pageview");
            }

            $enable_etag = $lang . "_". $group;


            if($cache_file["client"] === false
                || ($cache_file["client"] == "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                || ($cache_file["client"] == "nohttp" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
            ) {
                $expires = false;
                $max_age = false;
                $enable_etag = false;
            } elseif(defined("IS_LOGGED")) {
                $expires = null;
                $max_age = 3;
            } else {
                $expires = null;
                $max_age = 7;
            }
            /*
         if($_COOKIE["nocache"]) {
             $max_age = 0;
             $expires = -1;

             unset($_COOKIE["nocache"]);
             setcookie("nocache", null, -1, '/', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
         } else {
             //da gestire in funzione dei blocchi dinamici
              //   l'expire solo per le risorse statiche deve essere di 1 settimana.
              //   Per le risorse dinamiche di solito e a 0
              //   Ma per fare le cose corrette bisogna creare le pagine expire in funzione dei blochhi inseriti nel front end.
              //   Percui se ci sono blocchi che variano in funzione delle scelte come la lingua o il login ad esempio e necessario
              //   impostare un expire bassisimo (3 sec). Se invece nn ci sono questi blocchi percui il sito risulta un po piu statico
              //   allora e possibile mettere anche un 30 sec o 60.
              //   Tutto questo e da calcolare in cache page in creazione della cache

             $max_age = 0;
             if(isset($_COOKIE["language_inset"]))
                 $expires = -1;
             elseif($group == "guests")
                 $expires = null;
             else
                 $expires = -1;
         }*/
            if($_SESSION[APPID . "UserID"] == "debug")
                return false;

            $this->send_header($target_file, $cache_file["type"], $compress, $max_age, $expires, $enable_etag);
            readfile($target_file);

			if(DEBUG_PROFILING === true)
				Stats::benchmark("Cache lvl 1 (in cache)");

            exit;
        }

        return false;
    }

    /*****************************************************************
     * PAGE
     *****************************************************************/

    /**
     * @param $user_path
     * @param null $page_type
     * @param bool $skip_locale
     * @return mixed
     */
    private function get_page_properties($user_path = null, $skip_locale = false) {
        $schema = $this->get_page_settings();
		if(!$user_path)
			$user_path = $this->path_info;

        //strippa il path di base per la cache
        if(is_array($schema["alias"]) && count($schema["alias"])) {
            if($schema["alias"][$_SERVER["HTTP_HOST"]]) {
                $resAlias["alias"] = $schema["alias"][$_SERVER["HTTP_HOST"]];
                if(strpos($user_path, $schema["alias"][$_SERVER["HTTP_HOST"]] . "/") === 0
                    || $user_path == $schema["alias"][$_SERVER["HTTP_HOST"]]
                ) {
                    $user_path = substr($user_path, strlen($schema["alias"][$_SERVER["HTTP_HOST"]]));
                    $resAlias["redirect"] = $_SERVER["HTTP_HOST"] . $user_path;
                    if(!$user_path)
                        $user_path = "/";
                }

                if($resAlias["alias"] != "/") {
                    $arrLocale = $this->get_page_settings("locale");
                    if($arrLocale["rev"]["lang"][basename($resAlias["alias"])]) {
                        $lang = $arrLocale["rev"]["lang"][basename($resAlias["alias"])];
                    } else {
                        $settings_user_path = $resAlias["alias"];
                    }
                }
            } else {
                $resAlias["redirect"] = false;
            }
        }

        $settings_user_path                                     .= $user_path;
        $arrSettings_path                                       = explode("/", trim($user_path, "/"));

        if(!$lang && $arrLocale["rev"]["lang"][$arrSettings_path[0]]) {
            $lang                                               = $arrLocale["rev"]["lang"][$arrSettings_path[0]];
        }

        if(isset($schema["page"][$settings_user_path])) {
            $res                                                = $schema["page"][$settings_user_path];
            $page_key                                           = $settings_user_path;
        } elseif(isset($schema["page"]["/" . $arrSettings_path[0]] )) {
            $res                                                = $schema["page"]["/" . $arrSettings_path[0]];
            $page_key                                           = "/" . $arrSettings_path[0];
        } elseif(isset($schema["page"][$arrSettings_path[count($arrSettings_path) - 1]])) {
            $res                                                = $schema["page"][$arrSettings_path[count($arrSettings_path) - 1]];
        } else { //todo: da testare
			$arrPageMatch = array_filter($schema["page"], function($page) use ($settings_user_path) {
				return ($page["router"]
					? preg_match("#" . preg_quote($page["router"]["source"], "#") . "#i", $settings_user_path)
					: false
				);
			});

			if(is_array($arrPageMatch) && count($arrPageMatch)) {
				ksort($arrPageMatch);

				$res = reset($arrPageMatch);
				$page_key = key($arrPageMatch);
			} else {
				//$tmp_user_path = $user_path;
				do {

					if (isset($schema["page"][$settings_user_path])) {
						$res = $schema["page"][$settings_user_path];
						$page_key = $settings_user_path;
						break;
					}
				} while ($settings_user_path != DIRECTORY_SEPARATOR && ($settings_user_path = dirname($settings_user_path))); //todo: DS check
			}
        }

        if(strpos($user_path, $res["strip_path"]) === 0) {
            $user_path                                          = substr($user_path, strlen($res["strip_path"]));
	        if(!$user_path)
	            $user_path                                      = "/";
		}

		if($resAlias) {
			$res["alias"]                                       = $resAlias["alias"];
			if($resAlias["redirect"] === false && $_SERVER["SERVER_ADDR"] != $_SERVER["REMOTE_ADDR"] && strpos($_SERVER["HTTP_HOST"], "www.") === 0) {
				$alias_flip                                     = array_flip($schema["alias"]); //fa redirect al dominio alias se il percorso e riservato ad un dominio alias
				if($alias_flip["/" . $arrSettings_path[0]]) {
					$resAlias["redirect"]                       = $alias_flip["/" . $arrSettings_path[0]] . substr($user_path, strlen("/" . $arrSettings_path[0]));
				}
			}

			$res["redirect"]                                    = $resAlias["redirect"];
		}

        $res["user_path"]                                       = $user_path;

        if($lang)
            $res["lang"]                                        = $lang;

        if($res["db_path"] && $page_key && strpos($user_path, $page_key) === 0) {
            $res["db_path"]                                     .= substr($user_path, strlen($page_key));
        } else {
            $res["db_path"]                                     = $user_path;
        }

        $arrUserPath = pathinfo($user_path);
        if($arrUserPath["extension"])
            $res["type"]                                        = $arrUserPath["extension"];

        if(!$res["framework_css"])
            $res["framework_css"]                               = $schema["page"]["/"]["framework_css"];
        if(!$res["font_icon"])
            $res["font_icon"]                                   = $schema["page"]["/"]["font_icon"];

        if(!$res["layer"])
            $res["layer"]                                       = $schema["page"]["/" . $arrSettings_path[0]]["layer"];

        if(!$res["group"])
            $res["group"]                                       = $res["name"];

        if(!$skip_locale)
            $res["locale"]                                      = $this->get_locale($res);

        return $res;
    }
    /**
     * @param $page
     * @param null $user_permission
     * @return array
     */
    private function get_page($user_path = null, $user_permission = null) {
        $cache_path = false;

		$properties = $this->get_page_properties($user_path);
        if($properties["cache"]) {
            if($user_permission === null && $properties["session"] !== false)
                $user_permission = $this->get_session();

            if($properties["cache"] === "guest") {
                $gid = ($user_permission["primary_gid_name"]
                    ? $user_permission["primary_gid_name"]
                    : $user_permission["primary_gid_default_name"]
                );
                if(!$gid)
                    $gid = "guests";

                if($_COOKIE["group"] != $gid) {
                    $this->session_share_for_subdomains();

                    $sessionCookie = session_get_cookie_params();
                    setcookie("group", $gid, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
                }

                $cache_path = "/global";
            } else {
                if(defined("IS_LOGGED") && is_array($user_permission) && count($user_permission)) {
                    if($properties["cache"] === "user") {
                        $auth_path = ($user_permission["username_slug"] ? $user_permission["username_slug"] : preg_replace("/[^a-z\-0-9]/i", "", $user_permission["username"]));
                        $cache_path = "/private";
                    } else {
                        $auth_path = ($user_permission["primary_gid_name"]
                            ? $user_permission["primary_gid_name"]
                            : $user_permission["primary_gid_default_name"]
                        );
                    }
                }

                if(!$auth_path)
                    $auth_path = "guests";

                if(!$cache_path)
                    $cache_path = "/public";
            }

            $cache_path .= $properties["cache_path"] . (!$auth_path || ($auth_path == "guests" && $properties["cache_path"])
                    ? ""
                    : "/" . $auth_path
                );

            /*
            switch($properties["cache"]) {
                case "guest":
                case "user":
                case true:
                case "group":

                    break;
                default:
                    $cache_path .= $properties["cache"];
            }  */

            if(strpos(strtolower($_SERVER["HTTP_HOST"]), "www.") === 0) {
                $domain_name = substr($_SERVER["HTTP_HOST"], strpos($_SERVER["HTTP_HOST"], ".") + 1);
            } else {
                $domain_name = $_SERVER["HTTP_HOST"];
            }

            if(strpos($domain_name, ":") !== false)
                $domain_name = substr($domain_name, 0, strpos($domain_name, ":"));

            //$this->get_locale($properties["user_path"], $domain_name, $user_permission);

            if($cache_path)
                $cache_base = "/cache" . "/" . $domain_name . "/" . strtolower(FF_LOCALE) . $cache_path;

            return array(
                "base" => $cache_base
				, "group" =>  $properties["group"]
				, "path" => $cache_base . ($properties["user_path"] == "/" ? "" : $properties["user_path"])
				, "user_path" => $properties["user_path"]
				, "redirect" => $properties["redirect"]
				, "alias" => $properties["alias"]
				, "auth" => $auth_path
				, "lang" => FF_LOCALE
				, "type" => $properties["type"]
				, "compress" => $properties["compress"]
				, "locale" => $properties["locale"]
				, "client" => $properties["cache_client"]
            );
        }

        return $properties["exit"];
    }
    /**
     * @param $user_path
     * @param null $last_update
     * @return array
     */
    private function check_ff_contents($user_path, $last_update = null) {
        $res = array();
        $full_path = $this->getAbsPath("/contents" . $user_path);

        if(is_file($full_path)) {
            $res["count"]++;
            if($last_update && filemtime($full_path) > $last_update)
                $res["cache_invalid"] = true;
        } elseif(is_dir($full_path)) {
            $fs_contents = glob($full_path . "/*");
            if(is_array($fs_contents) && count($fs_contents)) {
                foreach($fs_contents AS $file) {
                    $file_name = pathinfo($file, PATHINFO_BASENAME);
                    if(strtolower($file_name) == "index") {
                        $res["count"]++;
                        if($last_update && filemtime($file) > $last_update)
                            $res["cache_invalid"] = true;

                        break;
                    }
                }
            }
        }

        return $res;
    }
    /**
     * @param $params
     * @param array $request
     * @return array
     */
    private function get_page_filename($params, $request = array()) {
        $cache_valid = false;
        $cache_base_path = $this->getAbsPath($params["path"]);
        $cache_filename = "index";

        $random = $params["settings"]["page"][$params["user_path"]]["rnd"];
        if($random)
            $cache_filename .= rand(1, $random);

        $cache_file_type = $params["type"];
        if($cache_file_type == "mixed")
        {
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                $cache_file_type = "json";
            else
                $cache_file_type = "html";
        }

        $accept_compress = (isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") === false
            ? false
            : true
        );

        if($params["compress"] && $accept_compress) {
            $cache_ext = "gz";
        } else {
            $cache_ext = $cache_file_type;
        }

        if($request["get"] && is_array($request["get"]["query"]))
            $cache_filename .= "_" . str_replace(array("&", "="), array("_", "-"), implode("&", $request["get"]["query"]));

        $cache_filename = preg_replace("/[^A-Za-z0-9\-_]/", '', $cache_filename);
        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
            $cache_filename .= "_XHR";

        $cache_storing_path = $cache_base_path;
        if(is_array($params["settings"]["rule"]) && count($params["settings"]["rule"])) {
            foreach($params["settings"]["rule"] AS $compare_path => $precision) {
                if(strpos($cache_base_path, $compare_path) !== false) {
                    $arrCacheSplit = explode($compare_path, $cache_base_path);
                    if(count($arrCacheSplit) > 1 && $arrCacheSplit[1]) {
                        $cache_storing_path = $arrCacheSplit[0] . $compare_path . "/" . substr(ltrim($arrCacheSplit[1], "/"), 0, $precision);
                        unset($arrCacheSplit[0]);
                        $cache_storing_path .= implode($compare_path, $arrCacheSplit);
                        break;
                    }
                }
            }
        }
        if(is_file($cache_storing_path . "/" . $cache_filename . "." . $cache_ext)) {
            $cache_file_exist = true;
            $last_update = filemtime($cache_storing_path . "/" . $cache_filename . "." . $cache_ext);
            $cache_last_version = filectime($cache_storing_path . "/" . $cache_filename . "." . $cache_ext);
            if(defined("CACHE_LAST_VERSION") && CACHE_LAST_VERSION > $cache_last_version)
                $cache_last_version = CACHE_LAST_VERSION;

            if($last_update >= $cache_last_version)
                $cache_valid = true;
            //} else {
            //    $cache_noexist = !is_dir($cache_storing_path);
        }

        if(!$cache_valid) {
            // $cache_error_path = $params["user_path"];
            if(!$cache_file_exist) {
                $arrUserPath = explode("/", $params["user_path"]);
                $cache_error_path = $this->getAbsPath($params["base"] . $params["settings"]["page"]["/error"]["cache_path"]);
                $cache_file_error_exist = is_file($cache_error_path . "/". $arrUserPath[1] . ".php");
                $is_error_document = $this->get_error_document($cache_error_path, $cache_filename, $params);
            }
        }

        return array(
            "path"                  => $cache_base_path
        , "cache_path"          => $cache_storing_path
        , "error_path"          => $cache_error_path
        , "filename"            => $cache_filename
        , "primary"             => $cache_filename . "." . $cache_ext
        , "gzip"                => $cache_filename . ".gz"
        , "last_update"         => $last_update
        , "noexistfile"         => !$cache_file_exist
        , "noexistfileerror"    => !$cache_file_error_exist
        , "is_error_document"   => $is_error_document
        , "invalid"             => !$cache_valid
        , "compress"            => $accept_compress
        , "client"              => $params["client"]
        , "type"                => $cache_file_type
        );
    }

    /*****************************************************************
     * INTERNATIONAL
     *****************************************************************/
    /**
     * @param null $user_permission
     * @return array|null
     */
    private function get_locale_settings($user_permission = null) {
        static $arrLocale = null;

        if(!is_array($arrLocale)) {
            if($user_permission === null)
                $user_permission = $this->get_session();

            if(is_array($user_permission) && count($user_permission) && is_array($user_permission["lang"]) && count($user_permission["lang"])) {
                $arrLocale["lang"]         = $user_permission["lang"];
                $arrLocale["country"]     = $user_permission["country"];
                $arrLocale["rev"]         = $user_permission["rev"];
            } else {
                $arrLocale = $this->get_page_settings("locale");
            }

            if(is_array($arrLocale["lang"][LANGUAGE_DEFAULT])) {
                $arrLocale["lang"]["current"] = $arrLocale["lang"][LANGUAGE_DEFAULT];
                $arrLocale["lang"]["current"]["code"] = LANGUAGE_DEFAULT;
            }
        }

        return $arrLocale;
    }
    /**
     * @param $page
     */
    private function check_lang($page) {
        if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
            $lang = strtoupper($_GET["lang"]);

            if($lang) {
                //if(!defined("FF_DEFAULT_CHARSET"))
                //    define("FF_DEFAULT_CHARSET", "UTF-8");
                require_once($this->getAbsPathPHP("/ff/main"));

                $path_info = $_SERVER["PATH_INFO"];
                if($path_info == "/index")
                    $path_info = "/";

                $prefix = ($lang == LANGUAGE_DEFAULT
                    ? ""
                    : "/" . $page["locale"]["lang"][$lang]["tiny_code"]
                );

                require_once($this->getAbsPathPHP("/library/gallery/common/get_international_settings_path"));
                require_once($this->getAbsPathPHP("/library/gallery/common/normalize_url"));
                require_once($this->getAbsPathPHP("/library/gallery/common/write_notification"));
                require_once($this->getAbsPathPHP("/library/gallery/process/html_page_error"));

                $res = get_international_settings_path($page["user_path"], FF_LOCALE);

                $this->do_redirect(normalize_url($res["url"], HIDE_EXT, true, $lang, $prefix));
                //$this->send_header_content(null, false, false, false);
                //header('Location:' . normalize_url($res["url"], HIDE_EXT, true, $lang, $prefix), true, 301);
                //exit;

            } elseif(FF_LOCALE == LANGUAGE_DEFAULT) {
                if(isset($_COOKIE["lang"])) {
                    unset($_COOKIE['lang']);
                    setcookie('lang', null, -1, '', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
                }
            } elseif($_COOKIE["lang"] != FF_LOCALE) {
                $_COOKIE['lang'] = FF_LOCALE;
                setcookie("lang", FF_LOCALE, 0, '', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
            }
        }

    }

    /*****************************************************************
     * TOKEN
     *****************************************************************/
    /**
     * @param $u
     * @param null $objToken
     * @param null $type
     */
    private function token_write($u, $objToken = null, $type = null) {
        if(is_array($objToken)) {
            $this->token_set_session_cookie($objToken);

            $file_token_name = $objToken["public"];
        } else {
            $file_token_name = $objToken;
        }

        if($file_token_name) {
            //populate logs
            $u["logs"][$_SERVER["REMOTE_ADDR"]]++;


            $fsToken = $this->token_get_file_token($file_token_name, $type, true);

            $content = "<?php\n";
            $content .= '$u = ' . var_export($u, true) . ";";
            if($handle = @fopen($fsToken["file"], 'w')) {
                @fwrite($handle, $content);
                @fclose($handle);
            }
        }
    }
    /**
     * @param null $account
     * @param int $precision
     * @param null $expire
     * @param bool $renew
     * @return mixed
     */
    private function token_generate($account = null, $precision = 8, $expire = null, $renew = true) {
        if(!$expire)
            $expire = time() + (60 * 60 * 24 * 365);

        $res["expire"] 		= $expire;
        $res["renew"] 		= $renew;
        $res["stoken"] 		= bin2hex(openssl_random_pseudo_bytes($precision));
        $res["private"] 	= uniqid($res["stoken"]);

        if($account) {
            $hash 			= sha1($account . $res["stoken"]);
            $res["public"] 	= substr($hash, 0, strlen($hash) - strlen($res["private"]));
            $res["token"] 	= $res["public"] . $res["private"];
        }

        return $res;
    }
    /**
     * @param $token
     * @param null $account
     * @param int $precision
     * @return mixed
     */
    private function token_resolve($token, $account = null, $precision = 8) {
        $res["new"]			= $this->token_generate($account, $precision);

        $res["public"] 		= substr($token, 0, strlen($token) - strlen($res["new"]["private"]));
        $res["private"] 	= substr($token, strlen($res["public"]));
        $res["token"] 		= $token;

        return $res;
    }
    //todo: verificare che sia questo quello che genera i token.
    /**
     * @param null $user_permission
     * @param null $expire
     * @param bool $renew
     * @param int $precision
     * @return mixed
     */
    public function token_create($user_permission = null, $expire = null, $renew = true, $precision = 8) {
        $u = array();
        $token_user = "t";

        if(!$user_permission)
            $user_permission = $_SESSION[APPID . "user_permission"];

        $uid = $user_permission["ID"];
        $account = ($user_permission["username_slug"]
            ? $user_permission["username_slug"]
            : ($user_permission["username"]
                ? $this->url_rewrite($user_permission["username"])
                : $this->url_rewrite($user_permission["email"])
            )
        );
        $gid = ($user_permission["primary_gid_name"]
            ? $user_permission["primary_gid_name"]
            : $user_permission["primary_gid_default_name"]
        );

        $objToken = $this->token_generate($account, $precision, $expire, $renew);

        $u = array(
            "account" => $account
        , "uid" => $uid
        , "group" => $gid
        , "uniqid" => $objToken["private"]
        , "expire" => $objToken["expire"]
        , "renew" => $objToken["renew"]
        , "addr" => $_SERVER["REMOTE_ADDR"]
        , "agent" => $_SERVER["HTTP_USER_AGENT"]
        );

        $this->token_write($u, $objToken, $token_user);

        return $objToken["token"];
    }

	/**
	 * @param $token
	 * @param null $user_permission
	 * @param null $expire
	 * @param bool $renew
	 * @param int $precision
	 * @return array
	 */
	function cache_token_repair($token, $user_permission = null, $expire = null, $renew= true, $precision = 8) {
		$u = array();
		$token_user = "t";
		if(!$user_permission) {
			$user_permission = $_SESSION[APPID . "user_permission"];
		} elseif(!is_array($user_permission)) {
			//todo: da fare con l'anagraph class
			$user_permission = mod_security_get_user_data($user_permission, array("groups" => true));
		}
		$uid = $user_permission["ID"];
		$account = ($user_permission["username_slug"]
			? $user_permission["username_slug"]
			: ($user_permission["username"]
				? cache_url_rewrite($user_permission["username"])
				: cache_url_rewrite($user_permission["email"])
			)
		);
		$gid = ($user_permission["primary_gid_name"]
			? $user_permission["primary_gid_name"]
			: $user_permission["primary_gid_default_name"]
		);

		if(!$expire)
			$expire = time() + (60 * 60 * 24 * 365);

		$sep = ($precision == 8
			? 11
			: 4
		);

		$public = substr($token, 0, $sep);
		$private = substr($token, $sep);

		$objToken = array(
			"expire" 		=> $expire
		, "renew" 		=> $renew
		, "stoken" 		=> null //irrecuperabile
		, "private" 	=> $private
		, "public"		=> $public
		, "token"		=> $token
		);

		$u = array(
			"account" 		=> $account
		, "uid" 		=> $uid
		, "group" 		=> $gid
		, "uniqid" 		=> $objToken["private"]
		, "expire" 		=> $objToken["expire"]
		, "renew" 		=> $objToken["renew"]
		, "addr"		=> $_SERVER["REMOTE_ADDR"]
		, "agent" 		=> $_SERVER["HTTP_USER_AGENT"]
		);

		cache_token_write($u, $objToken, $token_user);

		return $objToken;
	}
    /**
     * @param $account
     * @param null $objToken
     * @return mixed|null
     */
    private function token_renew($account, $objToken = null) {
        if(!$objToken) {
            $objToken = $this->token_generate($account);
        } else  {
            $hash = sha1($account . $objToken["stoken"]);

            $objToken["public"] = substr($hash, 0, strlen($hash) - strlen($objToken["private"]));
            $objToken["token"] = $objToken["public"] . $objToken["private"];
        }

        return $objToken;
    }
    /**
     * @param $objToken
     * @param string $name
     */
    private function token_set_session_cookie($objToken, $name = "_ut") {
        $this->session_share_for_subdomains();

        $sessionCookie = session_get_cookie_params();

        setcookie($name,  $objToken["token"],  $objToken["expire"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);

        //Cache::log("SET COOKIE: " . $_COOKIE["_ut"]  . " = " . $objToken["token"] . " exp: " . $objToken["expire"], "mio");
    }
    /**
     * @param string $name
     * @return mixed
     */
    private function token_get_session_cookie($name = "_ut") {
        return $_COOKIE[$name];
    }
    /**
     * @param string $name
     */
    private function token_destroy_session_cookie($name = "_ut") {
        $sessionCookie = session_get_cookie_params();
        setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);

        $this->session_share_for_subdomains();

        $sessionCookie = session_get_cookie_params();
        setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);
    }
    /**
     * @param $public_token
     * @param null $type
     * @param bool $create_dir
     * @return array
     */
    private function token_get_file_token($public_token, $type = null, $create_dir = false) {
        $dir_token = $this->getAbsPathCache("/token");
        switch($type) {
            case "t":
                $step = "/" . substr($public_token, 0, 3);
                break;
            default:
        }

        if($create_dir && !is_dir($dir_token . $step))
            @mkdir($dir_token . $step, 0777, true);

        return array(
            "file" => $dir_token . $step . "/". $public_token . ".php"
        , "dir" => $dir_token
        );
    }
    //todo: da trovare e sistemare
    /**
     * @return null
     */
    public function session_share_for_subdomains() {
        static $domain = null;

        if(!$domain) {
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $_SERVER["HTTP_HOST"], $regs)) {
                $domain = $regs['domain'];
            } else {
                $domain = $_SERVER["HTTP_HOST"];
            }

            session_set_cookie_params(0, '/', '.' . $domain);
        }

        return $domain;
    }
    //todo: da trovare e sistemare
    /**
     * @param $path_info
     * @param null $query
     * @param null $hostname
     */
    public function redirect($path_info, $query = null, $hostname = null) //cache_check_redirect
    {
        if($hostname === null)
            $hostname = $_SERVER["HTTP_HOST"];

        if($query === null)
            $query = $_SERVER["QUERY_STRING"];

        $request_uri = $path_info;
        if(strlen($query))
            $request_uri .= "?" . $query;

        $redirect_path = $this->getAbsPathCachePHP("/redirect/" . $hostname);
        if(is_file($redirect_path)) {
            require($redirect_path);

            /** @var include $r */
            if($r[$request_uri]) {
                $this->do_redirect($r[$request_uri]["dst"], $r[$request_uri]["code"]);
            }
        }
    }

    /**
     * @param null $token
     * @return include|null
     */
    private function check_session_by_token($token = null) {
        static $user = null;

        $token_user = "t";

        if(!$user) {
            //$precision = 8;
            //$cookie_name = "_ut";
            if($token === null)
                $token = $this->token_get_session_cookie();

//			Cache::log("entro: " . $cc . "  " . $token . print_r($_SERVER, true) , "mio");

            if($token) {
                if(strpos($token, $token_user . "-") === 0) {
                    $token = substr($token, 2);
                    $type_token = $token_user;

                    $this->token_destroy_session_cookie();
                }
                $objToken = $this->token_resolve($token);

                //$stoken = bin2hex(openssl_random_pseudo_bytes($precision));
                //$new_private = uniqid($stoken);

                //$objToken["public"] = substr($token, 0, strlen($token) - strlen($new_private));
                //$objToken["private"] = substr($token, strlen($objToken["public"]));

                $fsToken = $this->token_get_file_token($objToken["public"], $type_token);

                //$file_token = $dir_token . "/" . $prefix_token . $objToken["public"] . ".php";
//	Cache::log("COOKIE: " . $cc . "  " . $_COOKIE["_ut"]  . " = " . $token, "mio");
                if(is_file($fsToken["file"])) {
                    require($fsToken["file"]);
//	Cache::log("FOUND: " . $cc . "  " . $file_token . " = " . $token, "mio");

                    /** @var include $u */
                    if($u["uniqid"] == $objToken["private"]) {
                        if($u["expire"] >= time()) {
                            $user = $u;

                            if($u["renew"] === 1) {
                                $u["renew"] = false;
                                @unlink($fsToken["file"]);
                                $this->token_destroy_session_cookie();
                            }

                            if($u["renew"]) {
                                $objToken["new"] = $this->token_renew($u["account"], $objToken["new"]);

                                //$this->token_set_session_cookie($objToken["new"]);
                                //$u["logs"][$_SERVER["REMOTE_ADDR"]]++;
                                $u = array(
                                    "account" => $u["account"]
                                , "uid" => $u["uid"]
                                , "group" => $u["group"]
                                , "uniqid" => $objToken["new"]["private"]
                                , "expire" => $objToken["new"]["expire"]
                                , "renew" => ($u["renew"] === true
                                        ? $u["renew"]
                                        : $u["renew"] - 1
                                    )
                                , "addr" => $_SERVER["REMOTE_ADDR"]
                                , "agent" => $_SERVER["HTTP_USER_AGENT"]
                                , "logs" => $u["logs"]
                                );

                                $this->token_write($u, $objToken["new"]);

                                /*$new_file_token = $dir_token . "/" .  $objToken["new"]["public"] . ".php";

                                $content = "<?php\n";
                                $content .= '$u = ' . var_export($u, true) . ";";
                                 if($handle = @fopen($new_file_token, 'w')) {
                                     @fwrite($handle, $content);
                                     @fclose($handle);
                                }*/

                                @unlink($fsToken["file"]);

//Cache::log("REGEN: " . $cc . "  " . $objToken["new"]["token"] . "=old> " . $objToken["token"] . " = " . $token, "mio");
                            } else {
                                $this->token_write($u, $objToken["public"]);
                            }
                        } else {
                            @unlink($fsToken["file"]);
                            $this->token_destroy_session_cookie();
//Cache::log("Destroy: " .  $cc . "  " .  $_COOKIE["_ut"] . "=old> " . $objToken["token"] . " = " . $token, "mio");
                        }
                    }
                } else {
                    $this->token_destroy_session_cookie();
                }
            } elseif($_GET[$token_user]) {
                $token = $_GET[$token_user];
                $objToken = $this->token_resolve($token);
                $objToken["token"] = $token_user . "-" . $objToken["token"];

                $fsToken = $this->token_get_file_token($objToken["public"], $token_user);

                if(is_file($fsToken["file"])) {
                    // require($file_token);
                    //copy($dir_token . "/" . $token_user . "-" . $objToken["public"] . ".php", $dir_token . "/" . $objToken["public"] . ".php");
                    $this->token_set_session_cookie($objToken);
                }

                $this->do_redirect($_SERVER["HTTP_HOST"] . trim(preg_replace('/([?&])'.$token_user.'=[^&]+(&|$)/','$1',$_SERVER["REQUEST_URI"]), "?"));
            }




            //		if($token && !$cookie_valid) {
            //		$this->token_destroy_session_cookie();


            /*	echo $token . "   sssssssssssssssssssssssssss" ;
                print_r($_COOKIE);
                    print_r($u);
                    print_r($objToken);
                    echo("ASDASD");*/


            //			$sessionCookie = session_get_cookie_params();
            //			setcookie($cookie_name, false, null, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
            //		} else {

            //	}
        }

        return $user;
    }

    /**
     * @param null $token
     * @param bool $start_session
     * @return array|include|mixed
     */
    private function create_session_by_token($token = null, $start_session = true) {
        $user_permission = array();

        $user = $this->check_session_by_token($token);
        if($user) {
            $user_permission = $this->get_permission($user["account"]);
            if(!is_array($user_permission)) {
                $user_permission = array(
                    "ID" => $user["uid"]
                , "username_slug" => $user["account"]
                , "primary_gid_name" => $user["group"]
                , "permissions" => $this->get_permission($user["group"], "gid")
                , "must-revalidate" => true
                );
                define("DISABLE_CACHE", true);
            }

            if(is_array($user_permission) && count($user_permission)) {
                define("IS_LOGGED", $user_permission["ID"]);

                if($start_session) {
                    @session_unset();
                    @session_destroy();

                    session_save_path(SESSION_SAVE_PATH);
                    session_name(SESSION_NAME);

                    session_regenerate_id(true);
                    session_start();

                    $sessionName = session_name();

                    $this->session_share_for_subdomains();

                    $sessionCookie = session_get_cookie_params();

                    if(defined("SESSION_PERMANENT")) {
                        $long_time = time() + (60 * 60 * 24 * 365);
                        setcookie($sessionName, session_id(), $long_time, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
                    } else {
                        setcookie($sessionName, session_id(), $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
                    }

                    $_REQUEST[$sessionName] = session_id();

                    $_SESSION[APPID . "__FF_SESSION__"] 	= uniqid(APPID, true);
                    $_SESSION[APPID . "Domain"] 			= $user_permission["domain"];
                    $_SESSION[APPID . "DomainID"] 			= $user_permission["ID_domain"];
                    $_SESSION[APPID . "UserNID"]			= $user_permission["ID"];
                    $_SESSION[APPID . "UserID"]				= $user_permission["username"];
                    $_SESSION[APPID . "UserLevel"] 			= $user_permission["level"];
                    $_SESSION[APPID . "UserEmail"] 			= $user_permission["email"];

                    $_SESSION[APPID . "user_permission"] 	= $user_permission;
                }
            }
        }

        return $user_permission;
    }

    /*****************************************************************
     * SESSION
     *****************************************************************/
    /**
     * @param $username
     * @param string $type
     * @return include
     */
    private function get_permission($username, $type = "perm") {
        $file_permission = $this->getAbsPathCachePHP("/cfg/" . $type . "/" . $username);

        if(is_file($file_permission)) {
            require($file_permission);
        }
        switch($type) {
            case "perm":
                /** @var include $user_permission */
                $res = $user_permission;
                break;
            case "gid":
                /** @var include $permissions */
                $res = $permissions;
                break;
            default:

        }

        return $res;
    }
    /**
     * @param null $superadmin_user
     * @return array|include|mixed|null
     */
    private function get_session($superadmin_user = null) {
        static $user_permission = null;

        if($user_permission === null) {
            $user_permission = array();

            if($_REQUEST[SESSION_NAME])
                $sessid = $_REQUEST[SESSION_NAME];
            if(!$sessid)
                $sessid = $_COOKIE[SESSION_NAME];

            if($sessid)
            {
                $tmp_path = SESSION_SAVE_PATH;
                if (substr($tmp_path, -1) !== "/")
                    $tmp_path .= "/";

                if(file_exists($tmp_path . "sess_" . $sessid))
                {
                    @session_unset();
                    @session_destroy();

                    session_save_path(SESSION_SAVE_PATH);
                    session_name(SESSION_NAME);

                    @session_start();


                    $user_permission = $_SESSION[APPID . "user_permission"];

                } else {
                    $this->token_destroy_session_cookie(SESSION_NAME);
                }
            }

            if(!count($user_permission)) {
                $user_permission = $this->create_session_by_token();
            }

            if(count($user_permission)) {
                if($_SESSION[APPID . "UserNID"] != 2)
                    define("IS_LOGGED", $_SESSION[APPID . "UserNID"]);

                if($superadmin_user === null) {
                    $superadmin_user = SUPERADMIN_USERNAME;
                }

                if($_SESSION[APPID . "UserID"] == $superadmin_user
                    || $user_permission["primary_gid_name"] == "data entry" //TODO: da togliere quando i sid non esisteranno piu
                ) {
                    define("DISABLE_CACHE", true);
                }

            }
        }

        return $user_permission;
    }

    /*****************************************************************
     * SEM
     *****************************************************************/
    /**
     * @param null $namespace
     * @param null $xhr
     * @return array
     */
    private function sem_get_params($namespace = null, $xhr = null) {
        if(!defined("APPID_SEM"))
            define("APPID_SEM", substr(preg_replace("/[^0-9 ]/", '', APPID), 0, 4));

        $max = 1;
        $remove = false;
        if($xhr === null)
            $xhr = $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";

        if(strlen($namespace) && is_numeric($namespace)) {
            $key = $namespace;
            $max = 10;
            $remove = true;
        } elseif($namespace == "create") {
            $key = 1;
            $max = 4;
        } elseif($namespace == "update") {
            $key = 2;
            $max = 3;
        } elseif(strlen($namespace) && is_string($namespace)) {
            $key = preg_replace("/[^0-9 ]/", '', hexdec(md5($namespace)));
            $remove = true;
        } else {
            if($xhr) {
                $key = 4;
                $max = 25;
            } else {
                $key = 0;
                $max = 15;
            }
        }

        return array(
            "key" => APPID_SEM . $key
        , "max" => $max
        , "remove" => $remove
        );
    }
    /**
     * @param null $namespace
     * @param bool $nowait
     * @param null $max
     * @return array
     */
    private function sem($namespace = null, $nowait = false, $max = null) {
        $acquired = true;
        if(defined("DISABLE_CACHE"))
            return array("acquired" => true); //nn funziona

        if(function_exists("sem_get")) {
            if(/*defined("DEBUG_MODE") &&*/ isset($_REQUEST["__nocache__"])) {
                $this->sem_remove($namespace);
            } else {
                $params = $this->sem_get_params($namespace);
                if($max === null)
                    $max = $params["max"];

                $sem = @sem_get($params["key"], $max, 0666, false);
                if($sem !== false) {
                    if(version_compare(phpversion(), "5.6.1", "<"))
                        $acquired = @sem_acquire($sem);
                    else
                        $acquired = @sem_acquire($sem, $nowait);

					Cache::log("GET:" . print_r(array(
                            "res" => $sem
                        , "acquired" => $acquired
                        , "namespace" => $namespace
                        , "max" => $max
                        , "key" => $params["key"]
                        , "remove" => $params["remove"]
                        ), true), "log_sem");
                } else {
					Cache::log($namespace . " ERROR: " . print_r(error_get_last(), true), "log_error_sem");
                }
            }
        }

        return array(
            "res" => $sem
        , "acquired" => $acquired
        , "namespace" => $namespace
        , "key" => $params["key"]
        , "remove" => $params["remove"]
        );
    }
    /**
     * @param null $message
     */
    private function sem_release($message = null) {
        if(function_exists("sem_release")) {
            if(is_array($this->Sem) && count($this->Sem)) {
                foreach($this->Sem AS $key => $sem) {
                    if($sem["res"] && $sem["acquired"]) {
                        $released = @sem_release($sem["res"]);
                        if($sem["remove"] && $released !== false)
                            $removed = @sem_remove($sem["res"]);

						Cache::log("Release:" . $released . " " . ($sem["remove"] && $released !== false ? "Removed: " . $removed . " " : "") . $message . " of: " . print_r($sem, true) . ($released === false ? " ERROR: " . print_r(error_get_last(), true) : ""), "log_sem");

                        unset($this->Sem[$key]);
                    }
                }
            }
        }
    }
    /**
     * @param null $namespace
     */
    private function sem_remove($namespace = null) {
        //return;
        if(function_exists("sem_get")) {
            $params = $this->sem_get_params($namespace);
            $sem = @sem_get($params["key"]);
            if($sem) {
                $is_removed = @sem_remove($sem);
				Cache::log("ID: " . $params["key"] . " namespace: " . $namespace . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
            }
            if($namespace != "create") {
                $params = $this->sem_get_params("create");
                $sem = @sem_get($params["key"]);
                if($sem) {
                    $is_removed = @sem_remove($sem);
					Cache::log("ID: " . $params["key"] . " namespace: " . "create" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
                }
            }
            if($namespace != "update") {
                $params = $this->sem_get_params("update");
                $sem = @sem_get($params["key"]);
                if($sem) {
                    $is_removed = @sem_remove($sem);
					Cache::log("ID: " . $params["key"] . " namespace: " . "update" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
                }
            }
            if($namespace) {
                $params = $this->sem_get_params();
                $sem = @sem_get($params["key"]);
                if($sem) {
                    $is_removed = @sem_remove($sem);
					Cache::log("ID: " . $params["key"] . " namespace: " . "default" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
                }

                $params = $this->sem_get_params(null, true);
                $sem = @sem_get($params["key"]);
                if($sem) {
                    $is_removed = @sem_remove($sem);
					Cache::log("ID: " . $params["key"] . " namespace: " . "default XHR" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
                }
            }
        }
    }

    /*****************************************************************
     * OVERLOADING
     *****************************************************************/

    /**
     * @param bool $windows
     * @return array|bool
     */
    private function sys_getloadavg($windows = false)
    {
        return (function_exists("sys_getloadavg")
            ? sys_getloadavg()
            : $this->provision_getloadavg($windows)
        );
    }
    /**
     * @param bool $windows
     * @return array|bool
     */
    private function provision_getloadavg($windows = false){
        $os=strtolower(PHP_OS);
        if(strpos($os, 'win') === false){
            if(file_exists('/proc/loadavg')){
                $load = file_get_contents('/proc/loadavg');
                $load = explode(' ', $load, 1);
                $load = $load[0];
            }elseif(function_exists('shell_exec')){
                $load = explode(' ', `uptime`);
                $load = $load[count($load)-1];
            }else{
                return false;
            }

            if(function_exists('shell_exec'))
                $cpu_count = shell_exec('cat /proc/cpuinfo | grep processor | wc -l');

            return array('load'=>$load, 'procs'=>$cpu_count);
        }elseif($windows){
            if(class_exists('COM')){
                $wmi=new COM('WinMgmts:\\\\.');
                $cpus=$wmi->InstancesOf('Win32_Processor');
                $load=0;
                $cpu_count=0;
                if(version_compare('4.50.0', PHP_VERSION) == 1){
                    while($cpu = $cpus->Next()){
                        $load += $cpu->LoadPercentage;
                        $cpu_count++;
                    }
                }else{
                    foreach($cpus as $cpu){
                        $load += $cpu->LoadPercentage;
                        $cpu_count++;
                    }
                }
                return array('load'=>$load, 'procs'=>$cpu_count);
            }
            return false;
        }
        return false;
    }

    /*****************************************************************
     * ERROR
     *****************************************************************/

    /**
     * @param $cache_error_path
     * @param $cache_filename
     * @param $params
     * @return array|bool|mixed|null
     */
    private function get_error_document($cache_error_path, $cache_filename, $params)
    {
        $arrUserPath = explode("/", $params["user_path"]);
        $errorDocumentFile = $cache_error_path . "/" . $arrUserPath[1] . ".php";
        $key = str_replace("/cache", "", $params["path"]) . "/" . $cache_filename;

        $fs = new Filemanager("php");

        $page = $fs->read($key, $errorDocumentFile);

        return $page;
    }
    /**
     * @param $cache_error_path
     * @param $params
     * @return array|bool|mixed|null
     */
    private function set_error_document($cache_error_path, $params)
    {
        $arrUserPath = explode("/", $params["user_path"]);
        $errorDocumentFile = $cache_error_path . "/" . $arrUserPath[1] . ".php";
        $key = $params["user_path"];

        $fs = new Filemanager("php");

        return $fs->delete($key, $errorDocumentFile, Filemanager::SEARCH_IN_VALUE);
    }

    /*****************************************************************
     * REDIRECT
     *****************************************************************/
    /**
     * @param $destination
     * @param null $http_response_code
     * @param null $request_uri
     */
    private function do_redirect($destination, $http_response_code = null, $request_uri = null)
    {
        if($http_response_code === null)
            $http_response_code = 301;
        if($request_uri === null)
            $request_uri = $_SERVER["REQUEST_URI"];

        //system_trace_url_referer($_SERVER["HTTP_HOST"] . $request_uri, $arrDestination["dst"]);
        if(defined("DEBUG_MODE")) {
			Cache::log(" REDIRECT: " . $destination . " FROM: " . $request_uri . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_redirect");
        }

        $this->send_header_content(false, false, false, false);

        if(strpos($destination, "/") !== 0)
            $destination = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $destination;

        header("Location: " . $destination, true, $http_response_code);
        exit;
    }

    /*****************************************************************
     * UTILITY
     *****************************************************************/
    private function http_response_code($code = null)
    {
        return (function_exists("http_response_code")
            ? http_response_code($code)
            : $this->provision_http_response_code($code)
        );
    }
    /**
     * @param null $code
     * @return int|null
     */
    private function provision_http_response_code($code = null)
    {
        if ($code !== null)
        {
            $this->response_code = $code;
            header($this->ffGetHTTPStatus($code));
        }
        else
        {
            $code = $this->response_code;
        }
        return $code;
    }

	/**
	 * @param null $path_info
	 * @param bool $do_redirect
	 * @return null
	 */
	private function user_path_allowed($path_info = null, $do_redirect = true)
    {
        $schema = $this->get_page_settings();
		if(!$path_info)
			$path_info = $this->path_info;

        if(is_array($schema["error"]["rules"]) && count($schema["error"]["rules"]))
        {
            foreach($schema["error"]["rules"] AS $rule => $action)
            {
                $src = (strpos($rule, "[") === false
                    ? str_replace("\*", "(.*)", preg_quote($rule, "#"))
                    : $rule
                );
                if(preg_match("#" . $src . "#i", $path_info, $matches)) {
                    if(is_numeric($action)) {
                        $this->http_response_code($action);

						Cache::log(" RULE: " . $rule . " ACTION: " . $action . " URL: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_error_badpath");
                        exit;
                    } elseif($do_redirect && $action) {
                        $redirect = $action;
                        if(strpos($src, "(") !== false && strpos($action, "$") !== false)
                            $redirect = preg_replace("#" . $src . "#i", $action, $path_info);

                        $this->do_redirect($_SERVER["HTTP_HOST"] . $redirect);
                    }
                }
            }
        }

        return $path_info;
    }

	/**
	 * @param $request
	 * @param string $sep
	 * @return array|mixed|string
	 */
	private function nomalize_request($request, $sep = ",") {
        if($sep && strpos($request, $sep) !== false) {
            $arrRequest = explode($sep, $request);
            foreach($arrRequest AS $value) {
                $res[] = $this->url_rewrite($value);
            }
        } else {
            $res = $this->url_rewrite($request);
        }
        return $res;
    }

	/**
	 * @param $testo
	 * @param string $char_sep
	 * @return mixed|string
	 */
	private function url_rewrite($testo, $char_sep = '-')
    {
        $testo = $this->remove_accents($testo);
        $testo = strtolower($testo);

        //$testo = preg_replace('([^a-z0-9\-]+)', ' ', $testo);
        $testo = preg_replace('/[^\p{L}0-9\-]+/u', ' ', $testo);
        $testo = trim($testo);
        $testo = preg_replace('/ +/', $char_sep, $testo);
        $testo = preg_replace('/-+/', $char_sep, $testo);
        /*do {
            $testo = str_replace("--", "-", $testo, $count);
        } while ($count > 0);*/
        return $testo;
    }

    /*****************************************************************
     * UTILITY: Encoding
     *****************************************************************/
    private function seems_utf8($str) {
        $length = strlen($str);
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; # 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }
    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @since 1.2.1
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    private function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if ($this->seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
                chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
                chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
                chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
                chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
                chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
                chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
                chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
                chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
                chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
                chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
                chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
                chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
                chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
                chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
                chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
                chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
                chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
                chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
                chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
                // Decompositions for Latin Extended-A
                chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
                // Decompositions for Latin Extended-B
                chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
                chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
                // Euro Sign
                chr(226).chr(130).chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194).chr(163) => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
                chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
                // grave accent
                chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
                chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
                chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
                chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
                chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
                chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
                chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
                // hook
                chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
                chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
                chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
                chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
                chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
                chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
                chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
                chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
                chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
                chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
                chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
                chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
                // tilde
                chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
                chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
                chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
                chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
                chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
                chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
                chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
                chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
                // acute accent
                chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
                chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
                chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
                chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
                chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
                chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
                // dot below
                chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
                chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
                chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
                chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
                chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
                chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
                chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
                chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
                chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
                chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
                chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
                chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                chr(201).chr(145) => 'a',
                // macron
                chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
                // acute accent
                chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
                // caron
                chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
                chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
                chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
                chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
                chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
                // grave accent
                chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
            );

            // Used for locale-specific rules
            /*$locale = get_locale();

            if ( 'de_DE' == $locale ) {
                $chars[ chr(195).chr(132) ] = 'Ae';
                $chars[ chr(195).chr(164) ] = 'ae';
                $chars[ chr(195).chr(150) ] = 'Oe';
                $chars[ chr(195).chr(182) ] = 'oe';
                $chars[ chr(195).chr(156) ] = 'Ue';
                $chars[ chr(195).chr(188) ] = 'ue';
                $chars[ chr(195).chr(159) ] = 'ss';
            } elseif ( 'da_DK' === $locale ) {
                $chars[ chr(195).chr(134) ] = 'Ae';
                 $chars[ chr(195).chr(166) ] = 'ae';
                $chars[ chr(195).chr(152) ] = 'Oe';
                $chars[ chr(195).chr(184) ] = 'oe';
                $chars[ chr(195).chr(133) ] = 'Aa';
                $chars[ chr(195).chr(165) ] = 'aa';
            }*/

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

	/**
	 *
	 */
	private function initRequest() {
		if (isset($_REQUEST["_ffq_"])) // used to manage .htaccess [QSA] option, this overwhelm other options
			$this->path_info = $_REQUEST["_ffq_"];
		else if (isset($_SERVER["PATH_INFO"]))
			$this->path_info = $_SERVER["PATH_INFO"];
		else if (isset($_SERVER["ORIG_PATH_INFO"]))
			$this->path_info = $_SERVER["ORIG_PATH_INFO"];

		if($this->path_info == "/index")
			$this->path_info = "";

		if (strlen($_SERVER["QUERY_STRING"]))
		{
			$queryString = "";
			$arrQueryString = explode("&", rtrim($_SERVER["QUERY_STRING"], "&"));
			foreach ($arrQueryString as $query)
			{
				$arrQuery = explode("=", $query, 2);
				if ($arrQuery[0] == "_ffq_")
					continue;

				$queryString .= $arrQuery[0] . (count($arrQuery) == 2 ? "=" . $arrQuery[1] : "") . "&";
			}
			$this->query_string = trim($queryString, "&");

			$this->get = $_GET;
			$this->post = $_POST;

			unset($this->get["_ffq_"]);
			unset($this->post["_ffq_"]);
		}

		if (strpos($_SERVER["REQUEST_URI"], "?") !== false)
		{
			$arrRequestUri = explode("?", $_SERVER["REQUEST_URI"]);

			$this->request_uri = $arrRequestUri[0] . ($this->query_string ? "?" . $this->query_string : "");
		}
	}

    /*****************************************************************
     * UTILITY: Log
     *****************************************************************/
    /**
     * @param $string
     * @param string $filename
     */
    public static function log($data, $filename = "log") //writeLog
    {
		if(DEBUG_LOG === true) {
            $set_mod = false;
			$log_path = self::_getDiskPath() . self::BASE_PATH . "/logs";
			if(!is_dir($log_path))
				mkdir($log_path, 0777, true);

			$file = $log_path . '/' . date("Y-m-d") . "_" . $filename . '.txt';
			if(!is_file($file)) {
				$set_mod = true;
			}

			if($handle = @fopen($file, 'a'))
			{
			    if(is_array($data)) {
			        $string = print_r($data, true);
                } else {
			        $string = $data;
                }

				if(@fwrite($handle, date("Y-m-d H:i:s", time()) . " " . $string . "\n") === FALSE)
				{
					$i18n_error = true;
				}
				@fclose($handle);

				if($set_mod && !$i18n_error)
					chmod($file, 0777);
			}
		}
    }


	/*****************************************************************
	 * Write
	 *****************************************************************/
	/**
	 * @param $content
	 */
	function system_set_cache_page($content) {
		$globals = ffGlobals::getInstance("gallery");
		$cache_file = $globals->cache["file"];

		if(!defined("DISABLE_CACHE") && $globals->cache["enabled"] !== false) {
			$expires = time() + (60 * 60 * 24 * 1);

			if(is_object($content)) {
				switch(get_class($content)) {
					case "ffPage_html":
						$buffer = $content->tpl[0]->rpparse("main", false);
						break;
					case "ffTemplate":
						$buffer = $content->rpparse("main", false);
						break;
					default:
						$buffer = $content;
				}
			} else {
				$buffer = $content;
			}

			if($buffer && http_response_code() == 200) {
				if(!is_dir($cache_file["cache_path"]))
					@mkdir($cache_file["cache_path"], 0777, true);

				system_write_cache_stats();
				if ($cache_file["primary"] != $cache_file["gzip"])
					cm_filecache_write($cache_file["cache_path"], $cache_file["primary"], $buffer, $expires);

				cm_filecache_write($cache_file["cache_path"], $cache_file["gzip"], gzencode($buffer), $expires);

				Cache::log($cache_file["cache_path"] . "/" . $cache_file["primary"], "log_saved");
			} else {
				system_write_cache_error_document($cache_file);
				if ($cache_file["noexistfileerror"]) {
					if ($cache_file["primary"] != $cache_file["gzip"])
						cm_filecache_write($cache_file["error_path"], $cache_file["primary"], $buffer, $expires);

					cm_filecache_write($cache_file["error_path"], $cache_file["gzip"], gzencode($buffer), $expires);
				}
			}
		} elseif($globals->cache["enabled"] === false) {
			$cache = check_static_cache_page($globals->page["strip_path"] . $globals->page["user_path"], 200);

			if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["primary"]))
				@unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["primary"]);
			if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["gzip"]))
				@unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["gzip"]);
			if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["filename"] . "." . $cache["file"]["type"]))
				@unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["filename"] . "." . $cache["file"]["type"]);
		}

		cache_sem_release($globals->cache["sem"]);

		if(defined("DISABLE_CACHE"))
			cache_send_header_content(false, false, false, false);
		else
			cache_send_header_content(false, false);

		if(DEBUG_PROFILING === true)
			Stats::benchmark((defined("DISABLE_CACHE")
				? "Cache lvl 2 (no cache) "
				: "Cache lvl 3 (gen cache)"
			));

		//ffErrorHandler::raise("DEBUG CM Process End", E_USER_WARNING, null, get_defined_vars());

	}

	/**
	 * @param $user_path
	 * @param $contents
	 * @return bool
	 */
	function system_write_cache_page($user_path, $contents) {
		$globals = ffGlobals::getInstance("gallery");

		$http_status_code = http_response_code();
		if($http_status_code == 200)
			$use_in_sitemap = 1;

		//if(!$skip_strip_path)
		$user_path = $globals->page["strip_path"] . $user_path;

		$cache = check_static_cache_page($user_path, $http_status_code);

		$globals->cache = array_replace($globals->cache, $cache);
		//$globals->cache["sem"] = &$cache["sem"];

		if($globals->cache["enabled"] === false)
			return false;

		if(!$cache)
			return false;

		if($cache["request"]["post"])
			return false;

		if(!$contents && !$cache["ff_count"])
			return false;

		$db = ffDB_Sql::factory();
		$last_update = time();
		$arrFrequency = array("always" 	=> 10
		, "hourly" 		=> 9
		, "daily" 		=> 8
		, "weekly" 		=> 7
		, "monthly" 	=> 6
		, "yearly" 		=> 5
		, "never" 		=> 4
		);
		$frequency = "never";
		if(is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"])) {
			foreach($globals->cache["layout_blocks"] AS $layout_blocks_key => $layout_blocks_value) {
				if($arrFrequency[$layout_blocks_value["frequency"]] > $arrFrequency[$frequency])
					$frequency = $layout_blocks_value["frequency"];
			}
		}
		if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
			$cache_xhr = "1";

		$cache_ext = $globals->cache["params"]["type"];
		if($cache_ext == "mixed") {
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
				$cache_ext = "json";
			else
				$cache_ext = "html";
		}

		//Section Block
		if(is_array($globals->cache["section_blocks"]) && count($globals->cache["section_blocks"]))
			$query["insert"]["section_blocks"] = implode(",", $globals->cache["section_blocks"]);

		//Layout Block
		if(is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"]))
			$query["insert"]["layout_blocks"] = implode(",", array_keys($globals->cache["layout_blocks"]));

		//FF Block
		if(is_array($globals->cache["ff_blocks"]) && count($globals->cache["ff_blocks"]))
			$query["insert"]["ff_blocks"] = implode(",", $globals->cache["ff_blocks"]);

		//Data V Block
		if(is_array($globals->cache["data_blocks"]["V"]) && count($globals->cache["data_blocks"]["V"]))
			$query["insert"]["data_v_block"] = implode(",", $globals->cache["data_blocks"]["V"]);

		//Data G Block
		if(is_array($globals->cache["data_blocks"]["G"]) && count($globals->cache["data_blocks"]["G"]))
			$query["insert"]["data_g_block"] = implode(",", $globals->cache["data_blocks"]["G"]);

		//Data S Block
		if(is_array($globals->cache["data_blocks"]["S"]) && count($globals->cache["data_blocks"]["v"]))
			$query["insert"]["data_s_block"] = implode(",", $globals->cache["data_blocks"]["S"]);

		//Data D Block
		if(is_array($globals->cache["data_blocks"]["D"]) && count($globals->cache["data_blocks"]["D"]))
			$query["insert"]["data_d_block"] = implode(",", $globals->cache["data_blocks"]["D"]);

		//Data T Block
		if(is_array($globals->cache["data_blocks"]["T"]) && count($globals->cache["data_blocks"]["T"]))
			$query["insert"]["data_t_block"] = implode(",", $globals->cache["data_blocks"]["T"]);

		//Data M Block
		if(is_array($globals->cache["data_blocks"]["M"]) && count($globals->cache["data_blocks"]["M"]))
			$query["insert"]["data_m_block"] = implode(",", $globals->cache["data_blocks"]["M"]);


		$sSQL = "SELECT ID 
                , section_blocks
                , layout_blocks
                , data_v_block
                , data_g_block
                , data_s_block
                , data_d_block
                , data_t_block
                , data_m_block
                , ff_blocks
	            , http_status_code
            FROM cache_page
            WHERE `cache_page`.`user_path` = " . $db->toSql($user_path) . "
                AND `cache_page`.`disk_path` = " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                AND `cache_page`.`filename` = " . $db->toSql($globals->cache["file"]["filename"]) . "
                AND `cache_page`.`XHR` = " . $db->toSql($cache_xhr);
		$db->query($sSQL);
		if($db->nextRecord()) {
			//httpstatus
			if($http_status_code != $db->getField("http_status_code", "Text", true))
				$query["update"]["http_status_code"]   = "`http_status_code` = " . $db->toSql($http_status_code);

			//Section Block
			if($query["insert"]["section_blocks"] != $db->getField("section_blocks", "Text", true))
				$query["update"]["section_blocks"]   = "`section_blocks` = " . $db->toSql($query["insert"]["section_blocks"], "Text", true);

			//Layout Block
			if($query["insert"]["layout_blocks"] != $db->getField("layout_blocks", "Text", true))
				$query["update"]["layout_blocks"]   = "`layout_blocks` = " . $db->toSql($query["insert"]["layout_blocks"], "Text", true);

			//FF Block
			if($query["insert"]["ff_blocks"] != $db->getField("ff_blocks", "Text", true))
				$query["update"]["ff_blocks"]   = "`ff_blocks` = " . $db->toSql($query["insert"]["ff_blocks"], "Text", true);

			//Data V Block
			if($query["insert"]["data_v_block"] != $db->getField("data_v_block", "Text", true))
				$query["update"]["data_v_block"]   = "`data_v_block` = " . $db->toSql($query["insert"]["data_v_block"], "Text", true);

			//Data G Block
			if($query["insert"]["data_g_block"] != $db->getField("data_g_block", "Text", true))
				$query["update"]["data_g_block"]   = "`data_g_block` = " . $db->toSql($query["insert"]["data_g_block"], "Text", true);

			//Data S Block
			if($query["insert"]["data_s_block"] != $db->getField("data_s_block", "Text", true))
				$query["update"]["data_s_block"]   = "`data_s_block` = " . $db->toSql($query["insert"]["data_s_block"], "Text", true);

			//Data D Block
			if($query["insert"]["data_d_block"] != $db->getField("data_d_block", "Text", true))
				$query["update"]["data_d_block"]   = "`data_d_block` = " . $db->toSql($query["insert"]["data_d_block"], "Text", true);

			//Data T Block
			if($query["insert"]["data_t_block"] != $db->getField("data_t_block", "Text", true))
				$query["update"]["data_t_block"]   = "`data_t_block` = " . $db->toSql($query["insert"]["data_t_block"], "Text", true);

			//Data M Block
			if($query["insert"]["data_m_block"] != $db->getField("data_m_block", "Text", true))
				$query["update"]["data_m_block"]   = "`data_m_block` = " . $db->toSql($query["insert"]["data_m_block"], "Text", true);

			if($query["update"]) {
				$sSQL  = "UPDATE `cache_page` SET 
                     " . (implode(", " , $query["update"])). "
                     , `last_update` = " . $db->toSql($last_update, "Number") . "
                WHERE `cache_page`.`user_path` = " . $db->toSql($user_path) . "
                    AND `cache_page`.`disk_path` = " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                    AND `cache_page`.`filename` = " . $db->toSql($globals->cache["file"]["filename"]);
				$db->execute($sSQL);
			}
		} else {
			$sSQL = "INSERT INTO `cache_page` 
                (
                    ID
                    , `user_path`
                    , `disk_path`
                    , `filename`
                    , `ext`
                    , `lang`
                    , `get`
                    , `http_status_code`
                    , `section_blocks`
                    , `layout_blocks`
                    , `ff_blocks`
					, `data_v_block`
	                , `data_g_block`
	                , `data_s_block`
	                , `data_d_block`
	                , `data_t_block`
	                , `data_m_block`
                    , `last_update`
                    , `frequency`
                    , `use_in_sitemap`
                    , `XHR`
                    , `ID_domain`
                )
                VALUES 
                (
                    NULL
                    , " . $db->toSql($user_path) . " 
                    , " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                    , " . $db->toSql($globals->cache["file"]["filename"]) . " 
                    , " . $db->toSql($cache_ext) . " 
                    , " . $db->toSql(strtolower($globals->cache["params"]["lang"])) . " 
                    , " . $db->toSql($globals->cache["request"]["get"] && is_array($globals->cache["request"]["get"]["query"]) && count($globals->cache["request"]["get"]["query"])
					? implode("&", $globals->cache["request"]["get"]["query"])
					: ""
				) . " 
                    , " . $db->toSql($http_status_code) . " 
                    , " . $db->toSql($query["insert"]["section_block"]) . " 
                    , " . $db->toSql($query["insert"]["layout_blocks"]) . " 
                    , " . $db->toSql($query["insert"]["ff_blocks"]) . " 
                    , " . $db->toSql($query["insert"]["data_v_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_g_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_s_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_d_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_t_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_m_block"]) . " 
                    , " . $db->toSql($last_update, "Number") . " 
                    , " . $db->toSql($frequency) . "
                    , " . $db->toSql($use_in_sitemap, "Number") . "
                    , " . $db->toSql($cache_xhr) . "
                    , " . $db->toSql($globals->ID_domain, "Number") . "
                )";
			$db->execute($sSQL);
		}

	}

	/**
	 * @param null $cache_file
	 * @param null $expires
	 */
	function system_write_cache_error_document($cache_file = null, $expires = null)
	{
		$cm = cm::getInstance();
		$globals = ffGlobals::getInstance("gallery");

		if(!$cache_file)
			$cache_file = $globals->cache["file"];

		$arrUserPath = explode("/", $globals->user_path);

		$errorDocumentFile = $cache_file["error_path"] . "/" . $arrUserPath[1];
		$user_path = str_replace(CM_CACHE_PATH, "", $cache_file["cache_path"]);

		check_function("Filemanager");

		$fs = new Filemanager("php", $errorDocumentFile, "p");
		$fs->update(array(
			$user_path . "/" . $cache_file["filename"] => $globals->user_path
		));
	}

	/**
	 * @param $buffer
	 * @param null $page
	 * @param null $expires
	 * @return mixed
	 */
function system_write_cache_stats() {
		$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

	//todo: Impostazioni di base da fare come oggetto
	$service = "server";
	$this_controllers = array(
		"server" => array(
			"default" => false
			, "services" => null
			, "storage" => array(
				"nosql" => null
				//, "fs" => null
			)
		)
	);
	$this_struct = array(
		"connectors" => array(
			"sql"                       => array(
				"prefix"				=> "CACHE_DATABASE_"
				, "table"               => "cache_pages"
				, "key"                 => "ID"
			)
			, "nosql"                   => array(
				"prefix"				=> "CACHE_MONGO_DATABASE_"
				, "table"               => "cache_pages"
				, "key"                 => "ID"
			)
			, "fs"                      => array(
				"path"                  => "/cache/notify"
				, "name"                => "title"
				, "var"					=> "s"
			)
		)
		, "table" => array(
			"db" => array(
				"title" 				=> "title"
			)
		)
		, "type" => array(
			"url"						=> "string"
			, "get"						=> "array"
			, "domain"					=> "string"
			, "action"					=> array(
				"name"					=> "string"
				, "value"				=> "string"
			)
			, "title" 					=> "string"
			, "description" 			=> "string"
			, "cover"					=> array(
				"url" 					=> "string:toImage"
				, "width" 				=> "number"
				, "height" 				=> "number"
			)
			, "author" 					=> array(
				"id" 					=> "number"
				, "avatar" 				=> "string:toImage"
				, "name" 				=> "string"
				, "url" 				=> "string"
				, "tags"				=> array(
					"primary" 			=> "arrayOfNumber"
					, "secondary" 		=> "arrayOfNumber"
				)
				, "uid"					=> "number"
			)
			, "tags"					=> array(
				"primary" 				=> "arrayOfNumber"
				, "secondary" 			=> "arrayOfNumber"
				, "rel" 				=> "arrayOfNumber"
			)
			, "meta"					=> "array"
			, "links"					=> "array"
			, "microdata"				=> "array"
			, "js"						=> array(
				"url" 					=> "string"
				, "keys" 				=> "array"
			)
			, "css"						=> array(
				"url" 					=> "string"
				, "keys" 				=> "array"
			)
			, "international"			=> "array"
			, "settings"				=> "array" 	//$globals->page
			, "template_layers"			=> "array"	//$globals->cache["layer_blocks"]
			, "template_sections"		=> "array"	//$globals->cache["section_blocks"]
			, "template_blocks"			=> "array"	//$globals->cache["layout_blocks"]
			, "template_ff"				=> "array"
			, "keys_D"					=> "arrayOfNumber"
			, "keys_G"					=> "array"
			, "keys_M"					=> "array"
			, "keys_S"					=> "array"
			, "keys_T"					=> "array"
			, "keys_V"					=> "arrayOfNumber"
			, "http_status"				=> "number"
			, "created"					=> "number"
			, "last_update"				=> "number"
			, "cache_last_update"		=> "number"
			, "cache"					=> "array"
			, "user_vars"				=> "array"
		)
	);
	$struct = $this_controllers[$service]["struct"];

	$connectors = $this_controllers[$service]["storage"];
	foreach($connectors AS $type => $data)
	{
		if(!$data)
		{
			$connectors[$type] = array(
				"service" => null
				, "connector" => $this_struct["connectors"][$type]
			);
		}
	}
	$storage = Storage::getInstance($connectors, array(
		"struct" => $this_struct["type"]
	));

	//codice operativo
	$created 							= time();

	$tags = array(
		"primary" 					=> array()
		, "secondary" 				=> array()
		, "rel" 					=> array()
	);

	if(is_array($cm->oPage->page_js) && count($cm->oPage->page_js)) {
		$page_js 					= $cm->oPage->page_js;
	}

	if(is_array($cm->oPage->page_css) && count($cm->oPage->page_css)) {
		$page_css 					= array_diff($cm->oPage->page_css, $globals->links);
	}

	$res = cache_get_request($_GET);
	$get = $res["request"];

	$s = array(
		"url"						=> $globals->cache["user_path"]
		, "get"						=> $get /* (is_array($globals->request) && count($globals->request)
										? $globals->request
										: array()
									) da approfondire*/
		, "domain"					=> vgCommon::DOMAIN
		, "action"					=> array(
			"name"					=> $globals->seo["current"]
			, "value"				=> null
		)
		, "title" 					=> $cm->oPage->title
		, "description" 			=> $cm->oPage->page_meta["description"]["content"]
		, "cover"					=> array_filter($globals->cover)
		, "author" 					=> $globals->author
		, "tags"					=> $globals->tags
		, "meta"					=> $cm->oPage->page_meta
		, "links"					=> $globals->links
		, "microdata"				=> $globals->microdata
		, "js"						=> array(
			"url" 					=> (is_array($cm->oPage->page_defer["js"]) && count($cm->oPage->page_defer["js"])
										? $cm->oPage->page_defer["js"][0]
										: ""
									)
			, "keys" 				=> $page_js
		)
		, "css"						=> array(
			"url" 					=> (is_array($cm->oPage->page_defer["css"]) && count($cm->oPage->page_defer["css"])
										? $cm->oPage->page_defer["css"][0]
										: ""
									)
			, "keys" 				=> $page_css
		)
		, "international"			=> ffTemplate::_get_word_by_code("", null, null, true)
		, "settings"				=> $globals->page
		, "template_layers"			=> $globals->cache["layer_blocks"]
		, "template_sections"		=> $globals->cache["section_blocks"]
		, "template_blocks"			=> (is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"])
										? array_keys($globals->cache["layout_blocks"])
										: array()
									)
		, "template_ff"				=> $globals->cache["ff_blocks"]
		, "keys_D"					=> (is_array($globals->cache["data_blocks"]["D"]) && count($globals->cache["data_blocks"]["D"])
										? array_keys($globals->cache["data_blocks"]["D"])
										: array()
									)
		, "keys_G"					=> (is_array($globals->cache["data_blocks"]["G"]) && count($globals->cache["data_blocks"]["G"])
										? array_keys($globals->cache["data_blocks"]["G"])
										: array()
									)
		, "keys_M"					=> (is_array($globals->cache["data_blocks"]["M"]) && count($globals->cache["data_blocks"]["M"])
										? array_keys($globals->cache["data_blocks"]["M"])
										: array()
									)
		, "keys_S"					=> (is_array($globals->cache["data_blocks"]["S"]) && count($globals->cache["data_blocks"]["S"])
										? array_keys($globals->cache["data_blocks"]["S"])
										: array()
									)
		, "keys_T"					=> (is_array($globals->cache["data_blocks"]["T"]) && count($globals->cache["data_blocks"]["T"])
										? array_keys($globals->cache["data_blocks"]["T"])
										: array()
									)
		, "keys_V"					=> (is_array($globals->cache["data_blocks"]["V"]) && count($globals->cache["data_blocks"]["V"])
										? array_keys($globals->cache["data_blocks"]["V"])
										: array()
									)
		, "http_status"				=> $globals->http_status
		, "created"					=> $created
		, "last_update"				=> $created
		, "cache_last_update"		=> $created
		, "cache"					=> str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
		, "user_vars"				=> $globals->user_vars
	);

	//print_r($s);
//	die();


	$res = $storage->write(
		$s
		, array(
			"set" => array(
				"title" 				=> $cm->oPage->title
				, "description" 		=> $cm->oPage->page_meta["description"]["content"]

				, "keys_D"				=> $s["keys_D"]
				, "keys_G"				=> $s["keys_G"]
				, "keys_M"				=> $s["keys_M"]
				, "keys_S"				=> $s["keys_S"]
				, "keys_T"				=> $s["keys_T"]
				, "keys_V"				=> $s["keys_V"]
				, "http_status"			=> $s["http_status"]
				, "last_update"	        => $created
				, "cache"				=> "+" . str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
			)
			, "where" => array(
				"url" 					=> $globals->cache["user_path"]
				, "domain" 				=> vgCommon::DOMAIN
				, "get" 				=> $get
			)
		)
	);

	//return $buffer;
}

	/**
	 * @param $user
	 * @param $old_session_id
	 * @param bool $permanent_session
	 * @return bool
	 */
	function system_write_cache_token_session($user, $old_session_id, $permanent_session = MOD_SECURITY_SESSION_PERMANENT) {
		if($user["username"] == MOD_SEC_GUEST_USER_ID)
			return false;
		if(!$permanent_session)
			return false;

		$u = array();
		$user_permission = get_session("user_permission");
		$account = ($user_permission["username_slug"]
			? $user_permission["username_slug"]
			: ($user_permission["username"]
				? ffCommon_url_rewrite($user_permission["username"])
				: ffCommon_url_rewrite($user_permission["email"])
			)
		);
		$gid = ($user_permission["primary_gid_name"]
					? $user_permission["primary_gid_name"]
					: $user_permission["primary_gid_default_name"]
				);


		//$precision = 8;
		$file_token_dir = CM_CACHE_PATH . "/token";

		$token = cache_token_get_session_cookie();
		if($token) {
			$objToken = cache_token_resolve($token, $account);
			if(is_file($file_token_dir . "/" . $objToken["public"] . ".php")) {
				require($file_token_dir . "/" . $objToken["public"] . ".php");
				if($u["uniqid"] == $objToken["private"]) {
					if($u["expire"] >= time()) {
						$token_valid = true;
					}
				}

				if(!$token_valid)
					unlink($file_token_dir . "/" . $objToken["public"] . ".php");
			}
		} else {
			$objToken["new"] = cache_token_generate($account);
		}

		if(!$token_valid) {
			//cache_token_set_session_cookie($objToken["new"]);

			//$u["logs"][$_SERVER["REMOTE_ADDR"]]++;
			$u = array(
				"account" => $user["username"]
				, "uid" => $user["ID"]
				, "group" => $gid
				, "uniqid" => $objToken["new"]["private"]
				, "expire" => $objToken["new"]["expire"]
				, "renew" => true
				, "addr" => $_SERVER["REMOTE_ADDR"]
				, "agent" => $_SERVER["HTTP_USER_AGENT"]
			);

			cache_token_write($u, $objToken["new"]);

	/*
			$file_token = $file_token_dir . "/" . $objToken["new"]["public"] . ".php";
			if(!is_dir($file_token_dir))
				@mkdir($file_token_dir, 0777, true);

			$content = "<?php\n";
			$content .= '$u = ' . var_export($u, true) . ";";
			if($handle = @fopen($file_token, 'w')) {
				@fwrite($handle, $content);
				@fclose($handle);
			}*/
		}
	}

	/**
	 *
	 */
	function system_destroy_cache_token_session() {
		//$precision = 8;
		//$cookie_name = "_ut";
		$file_token = CM_CACHE_PATH . "/token.php";

		$token = $this->token_get_session_cookie();
		if($token) {
			$objToken = $this->token_resolve($token);

			$file_token = CM_CACHE_PATH . "/token/" . $objToken["public"] . ".php";
			if(is_file($file_token)) {
				require($file_token);

				/** @var include $u */
				if($u["uniqid"] == $objToken["private"])
					@unlink($file_token);

			}
			/*
			$stoken = bin2hex(openssl_random_pseudo_bytes($precision));
			$new_private = uniqid($stoken);

			$public = substr($token, 0, strlen($token) - strlen($new_private));
			$private = substr($token, strlen($public));

			$file_token_dir = CM_CACHE_PATH . "/token";
			$file_token = $file_token_dir . "/" . $public . ".php";

			if(is_file($file_token)) {
				require($file_token);

				if($u["uniqid"] == $private)
					@unlink($file_token);

			}	*/
			$this->token_destroy_session_cookie();
		}

//	$sessionCookie = session_get_cookie_params();
//	setcookie($cookie_name, false, null, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
	}


    /**
     * ffGetHTTPStatus
     * @param type $code null to get array, code to get the http status'string
     * @return array
     * @author Alessandro Stucchi
     */
    private function ffGetHTTPStatus($code = null)
    {
        switch($code)
        {
            case "100":
                $res = "100 Continue";
                break;
            case "101":
                $res = "101 Switching Protocols";
                break;
            case "200":
                $res = "200 OK";
                break;
            case "201":
                $res = "201 Created";
                break;
            case "202":
                $res = "202 Accepted";
                break;
            case "203":
                $res = "203 Non-Authoritative Information";
                break;
            case "204":
                $res = "204 No Content";
                break;
            case "205":
                $res = "205 Reset Content";
                break;
            case "206":
                $res = "206 Partial Content";
                break;
            case "300":
                $res = "300 Multiple Choices";
                break;
            case "301":
                $res = "301 Moved Permanently";
                break;
            case "302":
                $res = "302 Found";
                break;
            case "303":
                $res = "303 See Other";
                break;
            case "304":
                $res = "304 Not Modified";
                break;
            case "305":
                $res = "305 Use Proxy";
                break;
            case "307":
                $res = "307 Temporary Redirect";
                break;
            case "400":
                $res = "400 Bad Request";
                break;
            case "401":
                $res = "401 Unauthorized";
                break;
            case "402":
                $res = "402 Payment Required";
                break;
            case "403":
                $res = "403 Forbidden";
                break;
            case "404":
                $res = "404 Not Found";
                break;
            case "405":
                $res = "405 Method Not Allowed";
                break;
            case "406":
                $res = "406 Not Acceptable";
                break;
            case "407":
                $res = "407 Proxy Authentication Required";
                break;
            case "408":
                $res = "408 Request Time-out";
                break;
            case "409":
                $res = "409 Conflict";
                break;
            case "410":
                $res = "410 Gone";
                break;
            case "411":
                $res = "411 Length Required";
                break;
            case "412":
                $res = "412 Precondition Failed";
                break;
            case "413":
                $res = "413 Request Entity Too Large";
                break;
            case "414":
                $res = "414 Request-URI Too Large";
                break;
            case "415":
                $res = "415 Unsupported Media Type";
                break;
            case "416":
                $res = "416 Requested range not satisfiable";
                break;
            case "417":
                $res = "417 Expectation Failed";
                break;
            case "500":
                $res = "500 Internal Server Error";
                break;
            case "501":
                $res = "501 Not Implemented";
                break;
            case "502":
                $res = "502 Bad Gateway";
                break;
            case "503":
                $res = "503 Service Unavailable";
                break;
            case "504":
                $res = "504 Gateway Time-out";
                break;
            case "505":
                $res = "505 HTTP Version not supported";
                break;
            default:
                $res = "Unknown";
        }

        return $this->protocol . " " . $res;
    }

    private function clearResult()
    {
        $this->keys                                                     = null;
        $this->data                                                     = null;
        $this->expires                                                  = null;
        $this->result                                                   = array();

        $this->isError("");
    }

    /**
     * @return array|bool|mixed|null
     */
    private function getResult()
    {
        return ($this->isError()
            ? false
            : ($this->result
                ? (is_array($this->keys) || count($this->result) > 1
                    ? $this->result
                    : array_shift($this->result)
                )
                : null
            )
        );
    }



	//todo: da trovare e sistemare. Non usato nella classe
	/**
	 * @param $id
	 * @return string
	 */
	public function get_page_by_id($id) {
		$page = $this->get_settings("page");

		if(isset($page["/" . $id]))
			return "/" . $id;
		else
			return "/error";
	}
}



