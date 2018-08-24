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

class cmsKernel
{
    const TYPE                                              = "kernel";
    //const LANG_DEFAULT_ID                                   = "1";
    const LANG_DEFAULT_CODE                                 = "ITA";
    //const LANG_DEFAULT_TINY_CODE                            = "it";

    private $router                                         = array(
                                                                '/static*'                  => '/cm/static.php$1'
                                                                , '/showfiles*'             => '/cm/showfiles.php$1'
                                                                , '*'                       => array(
                                                                                                "destination"   => '/cm/main.php'
                                                                                                , "priority"    => cmsRouter::PRIORITY_BOTTOM
                                                                                            )
                                                                , '/cm/error*'              => '/cm/error.php'
                                                                , '/asset*'                 => '/cm/error.php' //da gestire
                                                                , '/themes*'                => '/cm/error.php' //da gestire
                                                                , '/uploads*'               => '/cm/error.php' //da gestire
                                                                , '/cache*'                 => '/cm/error.php' //da gestire
                                                                //, '/login*'                 => '' //da gestire

                                                            );
    private $bad_path                                       = array(
                                                                '*/index*'                  => '$1'
                                                                , 'wp-login*'               => '401'
                                                                , 'wp-*'                    => '403'
                                                                , '*.shtml'                 => '403'
                                                                , '[[^a-z\-0-9/\+\.\_]+]'   => '400'
                                                            );
    private $path_info                                      = null;
    private $page                                           = null;
    private $request                                        = null;
    private $lang                                           = null;
    private $locale                                         = null;
    private $root_path                                      = null;

    public function __construct($cms, $params = null)
    {
        $this->loadSettings();

        $schema                                             = $cms->getSchema();
        $this->path_info                                    = $this->rewritePathInfo($schema);

        //$_SERVER["PATH_INFO"] = request di base. Sei servizi asincroni e la request del servizio ad esempio /srv/request
        //$this->path_info = request relativa alla pagina visualizzata. Sei servizi asincroni e la request è il referer.
        //                    Nei servizi non asincroni e la pagina visualizzata (uguale a $_SERVER["PATH_INFO"]

        $this->checkAllowedPath($schema["error"]["rules"]);

        $this->page                                         = $this->get_page_properties($schema, $_SERVER["PATH_INFO"]);

        if($this->page["router"])                           $this->router = array_replace($this->router , $this->page["router"]);

        /**
         * Resolve Request
         */
        $request_rules                                      = $this->get_request_rules($schema["request"]);
        $request                                            = $cms->requestCapture($request_rules);

        if($request_rules["log"]) {
            Cache::log(array(
                    "URL"               => $_SERVER["PATH_INFO"]
                    , "REFERER"         => $_SERVER["HTTP_REFERER"]
                ) + $request, "request");
        }

        //necessario XHR perche le request a servizi esterni path del domain alias non triggerano piu
        if($_SERVER["REQUEST_METHOD"] == "GET" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
            if(count($request["valid"]))                    $query = "?" . http_build_query($request["valid"]);

            // Evita pagine duplicate quando i link vengono gestiti dagli alias o altro
            if($this->page["redirect"])                     Cms::redirect($this->page["redirect"] . $query); //$this->page["redirect"] comprende http_host


            if(count($request["unknown"]) && !(defined("DEBUG_MODE") && (isset($get["__nocache__"]) || isset($get["__debug__"]) || isset($get["__query__"])))) {
                Cms::redirect($_SERVER["HTTP_HOST"] . $this->path_info . $query);
            }
        }

        if($request_rules["nocache"])                       define("DISABLE_CACHE", true);
    }

    private function get_page_properties($schema, $user_path = null) {
        $user_path                                          = ($user_path
                                                                ? $user_path
                                                                : $this->path_info
                                                            );

        $settings_user_path                                 = $user_path;
        if(isset($schema["page"][$settings_user_path])) {
            $res                                            = $schema["page"][$settings_user_path];
            $res["source"]                                  = $settings_user_path;
        } else {
            foreach($schema["page"] AS $key => $page) {
                if($page["router"] && preg_match("#" . $page["router"]["source"] . "#i", $settings_user_path)) {
                    $res                                    = $page;
                    $res["source"]                          = $key;
                    break;
                }
            }
            if(!$res) {
                $arrSettings_path                           = explode("/", trim($settings_user_path, "/"));
                if(isset($schema["page"]["/" . $arrSettings_path[0]] )) {
                    $res                                    = $schema["page"]["/" . $arrSettings_path[0]];
                    $res["source"]                          = "/" . $arrSettings_path[0];
                } elseif(isset($schema["page"][$arrSettings_path[count($arrSettings_path) - 1]])) {
                    $res                                    = $schema["page"][$arrSettings_path[count($arrSettings_path) - 1]];
                } else {
                    do {
                        if (isset($schema["page"][$settings_user_path])) {
                            $res                            = $schema["page"][$settings_user_path];
                            $res["source"]                  = $settings_user_path;
                            break;
                        }
                    } while ($settings_user_path != DIRECTORY_SEPARATOR && ($settings_user_path = dirname($settings_user_path))); //todo: DS check
                }
            }
        }

/*
        if(strpos($user_path, $res["strip_path"]) === 0) {
            $user_path                                      = substr($user_path, strlen($res["strip_path"]));
            if(!$user_path)
                $user_path                                  = "/";
        }*/

        /*if($resAlias) {
            $res["alias"]                                   = $resAlias["alias"];
            if($resAlias["redirect"] === false && $_SERVER["SERVER_ADDR"] != $_SERVER["REMOTE_ADDR"] && strpos($_SERVER["HTTP_HOST"], "www.") === 0) {
                $alias_flip                                 = array_flip($schema["alias"]); //fa redirect al dominio alias se il percorso e riservato ad un dominio alias
                if($alias_flip["/" . $arrSettings_path[0]]) {
                    $resAlias["redirect"]                   = $alias_flip["/" . $arrSettings_path[0]] . substr($user_path, strlen("/" . $arrSettings_path[0]));
                }
            }

            $res["redirect"]                                = $resAlias["redirect"];
        }*/

        $res["user_path"]                                   = (strpos($this->path_info, $res["strip_path"]) === 0
                                                                ? substr($this->path_info, strlen($res["strip_path"]))
                                                                : $this->path_info
                                                            );
        if(!$res["user_path"])                              $res["user_path"] = "/";

        $res["db_path"]                                     = $res["user_path"];
        $res["lang"]                                        = $this->lang["code"];
        $res["type"]                                        = pathinfo($res["user_path"], PATHINFO_EXTENSION);

        if(!$res["framework_css"])
            $res["framework_css"]                           = $schema["page"]["/"]["framework_css"];
        if(!$res["font_icon"])
            $res["font_icon"]                               = $schema["page"]["/"]["font_icon"];

        if(!$res["layer"])
            $res["layer"]                                   = $schema["page"]["/" . $arrSettings_path[0]]["layer"];

        if(!$res["group"])
            $res["group"]                                   = $res["name"];

        return $res;
    }

    private function get_request_rules($rules, $page = null) {
        $matches                                            = null;
        $page                                               = ($page
                                                                ? $page
                                                                : $this->page
                                                            );

        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
            $page["user_path"]                              = $page["strip_path"] . $page["user_path"];

        $request_path                                       = rtrim($page["alias"] . $page["user_path"], "/");
        if(!$request_path)                                  $request_path = "/";

        $last_split_path                                    = basename($request_path);
        if(isset($rules[$request_path])) {
            $matches                                        = $rules[$request_path];
        } elseif(isset($rules[$last_split_path])) {
            $matches                                        = $rules[$last_split_path];
        } else {
            do {
                $request_path                               = dirname($request_path);
                if(isset($rules[$request_path])) {
                    $matches                                = $rules[$request_path];
                    break;
                }
            } while($request_path != DIRECTORY_SEPARATOR);
        }

        if($matches["ext"] && is_array($rules[$matches["ext"]]))
            $matches                                        = array_merge_recursive($rules[$matches["ext"]], $matches);

        $matches["request_method"]                          = $_SERVER["REQUEST_METHOD"];
        if($page["primary"] && !$page["restricted"] && !$page["api"]) {
            $matches["exts"]                                = array(
                                                                "ffl"   => '["filter"]["first_letter"]'
                                                                , "pci" => '["filter"]["place"]["city"]["ID"]'
                                                                , "ppi" => '["filter"]["place"]["city"]["ID_province"]'
                                                                , "pri" => '["filter"]["place"]["city"]["ID_region"]'
                                                                , "psi" => '["filter"]["place"]["city"]["ID_state"]'
                                                                , "pcn" => '["filter"]["place"]["city"]["smart_url"]'
                                                                , "ppn" => '["filter"]["place"]["city"]["province_smart_url"]'
                                                                , "prn" => '["filter"]["place"]["region"]["smart_url"]'
                                                                , "psn" => '["filter"]["place"]["state"]["smart_url"]'
                                                                , "pps" => '["filter"]["place"]["city"]["province_sigle"]'
                                                                , "pss" => '["filter"]["place"]["state"]["sigle"]'
                                                            );
        }



        return $matches;
    }


    private function rewritePathInfo($schema = null) {
        $aliasname                                          = $schema["alias"][$_SERVER["HTTP_HOST"]];
        $orig_path_info                                     = ($_SERVER["QUERY_STRING"]
                                                                ? rtrim(rtrim($_SERVER["REQUEST_URI"],  $_SERVER["QUERY_STRING"]), "?")
                                                                : $_SERVER["REQUEST_URI"]
                                                            );
        $path_info                                          = ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" && $_SERVER["HTTP_REFERER"]
                                                                ? parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH)
                                                                : $orig_path_info
                                                            );

        $arrPathInfo                                        = explode("/", trim($path_info, "/"), "2");
        if($schema["locale"]["rev"]["lang"][$arrPathInfo[0]]) {
            $path_info                                      = "/" . $arrPathInfo[1];
            $lang_code                                      = $schema["locale"]["rev"]["lang"][$arrPathInfo[0]];
        }

        $this->setLang($schema, $lang_code);

        if($aliasname) {
            if(strpos($path_info, $aliasname . "/") === 0
                || $path_info == $aliasname
            ) {
                Cms::redirect($_SERVER["HTTP_HOST"] . substr($path_info, strlen($aliasname)));
            }

            $this->root_path                                = $aliasname;
        }

        $path_info                                          = $this->root_path . ($path_info == "/index" || $path_info == "/"
                                                                ? ""
                                                                : $path_info
                                                            );
        if(!$path_info)                                     $path_info = "/";


        $_SERVER["PATH_INFO"]                               = ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"
                                                                ? $orig_path_info
                                                                : $path_info
                                                            );

        return $path_info;
    }

    private function loadSettings() {
        /**
         * Performance Profiling
         */
        if(DEBUG_PROFILING === true) {
            define("FF_DB_MYSQLI_PROFILE", true);
            Cms::getInstance("debug")->benchmark();
        }
        if(DEBUG_MODE === true) {
//          Cms::getInstance("debug")->registerErrors();
        }

    }

    private function checkAllowedPath($rules = null, $path_info = null, $do_redirect = true)
    {
        $rules                                              = (is_array($rules)
                                                                ? array_replace($this->bad_path, $rules)
                                                                : $this->bad_path
                                                            );
        $path_info                                          = ($path_info
                                                                ? $path_info
                                                                : $this->path_info
                                                            );

        if(is_array($rules) && count($rules)) {
            foreach($rules AS $rule => $action) {
                $src                                        = $this->regexp($rule);
                if(preg_match($src, $path_info, $matches)) {
                    if(is_numeric($action)) {
                        http_response_code($action);

						Cache::log(array(
						    "RULE"          => $rule
                            , "ACTION"      => $action
                            , "URL"         => $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]
                            , "REFERER"     => $_SERVER["HTTP_REFERER"]
                        ), "error_badpath");
                        exit;
                    } elseif($do_redirect && $action) {
                        $redirect                           = $action;
                        if(strpos($src, "(") !== false && strpos($action, "$") !== false)
                            $redirect                       = preg_replace($src, $action, $path_info);

                        Cms::redirect($_SERVER["HTTP_HOST"] . $redirect);
                    }
                }
            }
        }

        return $path_info;
    }

    private function regexp($rule) {
        return "#" . (strpos($rule, "[") === false
            ? str_replace("\*", "(.*)", preg_quote($rule, "#"))
            : $rule
        ) . "#i";
    }
    private function setLang($schema, $code = null) {
        if(!$code)                                          $code = cmsKernel::LANG_DEFAULT_CODE;
        $this->lang                                         = $schema["locale"]["lang"][$code];
        $this->lang["code"]                                 = $code;

        $this->setLocale($schema["locale"]);

        //todo: trovare alternativa (tipo Cms::lang) per semplificare la programmazione
        define("LANGUAGE_INSET_TINY", $this->lang["tiny_code"]);
        define("LANGUAGE_INSET", $this->lang["code"]);
        define("LANGUAGE_INSET_ID", $this->lang["ID"]);
        define("FF_LOCALE", $this->lang["code"]);
        define("FF_LOCALE_ID", $this->lang["ID"]);
    }
    private function setLocale($locale) {
        $this->locale = $locale;
        $this->locale["lang"]["current"] = $this->lang;

        $this->locale["country"]["current"] = $this->locale["country"][$this->lang["country"]];
        $this->locale["country"]["current"]["code"] = $this->lang["country"];
    }
    public function run() {
        $router = Cms::getInstance("router");
        $router->addRules($this->router);

        $router->run($this->path_info);
    }

    public function getRequest() {
        return $this->request;
    }

    public function getPage() {
        return $this->page;
    }
    public function getLang() {
        return $this->lang;
    }
    public function getLocale() {
        return $this->locale;
    }
}