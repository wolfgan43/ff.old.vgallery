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

class cmsDebug
{
    const TYPE                                              = "debug";

    private $cms                                        	= null;
    protected $controllers                                  = array(
    );
    protected $controllers_rev                              = null;

    /**
     * cmsSchemaorg constructor.
     * @param $cms
     */
    public function __construct($cms, $params = null)
    {
        $this->cms = $cms;

        //$this->stats->setConfig($this->connectors, $this->services);
    }

    public function registerErrors() {
        declare(ticks=1);

        register_tick_function(function() {
            $GLOBALS["backtrace"]                           = debug_backtrace();
        });

        register_shutdown_function(function() {
            $error = error_get_last();

            switch ($error['type']) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    Cache::log($error, "notice");
                    break;
                case E_WARNING:
                case E_DEPRECATED:
                    header("Content-Type: text/html");

                    echo "<br /><br /><b>Warning</b>: " . $error["message"] . " in <b>" . $error["file"] . "</b> on line <b>" . $error["line"] . "</b>";

                case E_ERROR:
                case E_RECOVERABLE_ERROR:

                case E_PARSE:
                case E_STRICT:


                case E_CORE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_ERROR:

                case E_COMPILE_WARNING:
                case E_USER_ERROR:
                case E_USER_WARNING:

                case E_USER_DEPRECATED:


                    $this->dump($GLOBALS["backtrace"]);

                    if(function_exists("cache_sem_remove"))
                        cache_sem_remove($_SERVER["PATH_INFO"]);

                    break;
                default:
            }
        });



        //exit;
    }
    public function dumpLog($data, $filename = null) {
        $debug_backtrace = debug_backtrace();
        $disk_path = $this->cms->getDiskPath();

        foreach($debug_backtrace AS $i => $value) {
            if ($i) {
                if (basename($value["file"]) == "vgCommon.php") {
                    continue;
                }
                if (basename($value["file"]) == "cm.php") {
                    break;
                }

                $trace = $value;
            }
        }

        /*krsort($debug_backtrace);
        foreach($debug_backtrace AS $i => $trace) {
            if($trace["file"] == $disk_path . "/index.php")         continue;
            if($trace["file"] == $disk_path . "/cm/main.php")       continue;
            if($trace["file"] == $disk_path . "/cm/cm.php")         continue;
            break;
        }*/
//$this->dump();
        unset($trace["object"]);
        if (is_array($trace["args"]) && count($trace["args"])) {
            foreach ($trace["args"] AS $key => $value) {
                if (is_object($value)) {
                    $trace["args"][$key] = "Object: " . get_class($value);
                } elseif(is_array($value)) {
                    foreach($value AS $subkey => $subvalue) {
                        if(is_object($subvalue)) {
                            $trace["args"][$key][$subkey] = "Object: " . get_class($subvalue);
                        } elseif(is_array($subvalue)) {
                            $trace["args"][$key][$subkey] = $subvalue;
                        } else {
                            $trace["args"][$key][$subkey] = $subvalue;
                        }
                    }

                }
            }
        }

        $data["source"] = $trace;
        Cache::log($data, $filename);
    }

    public function dump($backtrace = null) {
        $html                               = "";
        $disk_path                          = (defined("FF_DISK_PATH")
            ? FF_DISK_PATH
            : str_replace(vgCommon::BASE_PATH, "", __DIR__)
        );
        $debug_backtrace                    = (is_array($backtrace)
                                                ? $backtrace
                                                : debug_backtrace()
                                            );

        //die(self::_getDiskPath());
        foreach($debug_backtrace AS $i => $trace) {
            if($i) {
                if(basename($trace["file"]) == "vgCommon.php") {
                    continue;
                }
                if(basename($trace["file"]) == "cm.php") {
                    break;
                }

                unset($trace["object"]);
                if (is_array($trace["args"]) && count($trace["args"])) {
                    foreach ($trace["args"] AS $key => $value) {
                        if (is_object($value)) {
                            $trace["args"][$key] = "Object: " . get_class($value);
                        } elseif(is_array($value)) {
                            foreach($value AS $subkey => $subvalue) {
                                if(is_object($subvalue)) {
                                    $trace["args"][$key][$subkey] = "Object: " . get_class($subvalue);
                                } elseif(is_array($subvalue)) {
                                    $trace["args"][$key][$subkey] = $subvalue;
                                } else {
                                    $trace["args"][$key][$subkey] = $subvalue;
                                }
                            }

                        }
                    }
                    if($trace["file"]) {
                        $label = 'Line in: ' . '<b>' . str_replace($disk_path, "", $trace["file"])  . '</b>';
                        $list_start = '<ol start="' . $trace["line"] . '">';
                        $list_end = '</ol>';
                    } else {
                        $label = 'Func: ' . '<b>' .  $trace["function"] . '</b>';
                        $list_start = '<ul>';
                        $list_end = '</ul>';

                    }

                    $html .=  $list_start . '<li><a style="text-decoration: none;" href="javascript:void(0);" onclick="this.nextSibling.style = \'display:none\';">' . $label . '</a><pre>' . print_r($trace, true). '</pre></li>' . $list_end;
                }
                $res[] = $trace;
            }
        }

        if(is_string($backtrace) && $backtrace) echo "<b>" . $backtrace . "</b>";
        echo "<hr />";
        echo "<center>BACKTRACE</center>";
        echo "<hr />";
        echo $html;
    }

    /**
     * @param bool $end
     * @param bool $isXHR
     * @return mixed
     */
    public function benchmark($end = false, $isXHR = false) {
        static $res;

        if(function_exists("getrusage"))
        {
            $ru = getrusage();
            if ($end) {
                $res["mem"] 			= number_format(memory_get_usage(true) - $res["mem"], 0, ',', '.');
                $res["mem_peak"] 		= number_format(memory_get_peak_usage(true) - $res["mem_peak"], 0, ',', '.');
                $res["cpu"] 			= number_format(abs(($ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec']) - $res["cpu"]), 0, ',', '.');
                $res["includes"] 		= get_included_files();
                $res["classes"] 		= get_declared_classes();
                $res["db"] 				= Storage::$cache;
                $res["exTime"] 			= microtime(true) - $res["exTime"];

                if (extension_loaded('xhprof') && is_dir(FF_DISK_PATH . "/xhprof_lib") && class_exists("XHProfRuns_Default")) {
                    $profiler_namespace = str_replace(array(".", "&", "?", "__nocache__"), array(",", "", "", ""), "[" . round($res["exTime"], 2) . "s] "
                            . ($isXHR
                                ? str_replace("/", "_", trim(parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH), "/")) . " (" . str_replace("/", "_", trim($_SERVER["REQUEST_URI"], "/")) . ")"
                                : str_replace("/", "_", trim($_SERVER["REQUEST_URI"], "/"))
                            )) . " - " . $end;

                    $xhprof_data = xhprof_disable();
                    $xhprof_runs = new XHProfRuns_Default();
                    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
                    $profiler_url = sprintf('https://www.paginemediche.info/xhprof_html/index.php?run=%s&source=%s', $run_id, $profiler_namespace);

                    //  printf('nbsp;<a href="%s" target="_blank">Profiler output</a><br>', $profiler_url);
                }

                Cache::log("URL: " . $_SERVER["REQUEST_URI"] . " (" . $end . ") Benchmark: " . print_r($res, true) . "Profiler: " . $profiler_url, "benchmark" .  ($isXHR ? "_xhr" : ""));
                return $res;
            } else {
                $res["mem"]             = memory_get_usage(true);
                $res["mem_peak"]        = memory_get_peak_usage(true);
                $res["cpu"]             = $ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec'];
                $res["exTime"] 			= microtime(true);

                if (extension_loaded('xhprof') && is_dir(FF_DISK_PATH . "/xhprof_lib")) {
                    include_once FF_DISK_PATH . '/xhprof_lib/utils/xhprof_lib.php';
                    include_once FF_DISK_PATH . '/xhprof_lib/utils/xhprof_runs.php';

                    xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
                }
            }
        }

    }

    /**
     * @param null $start
     * @return mixed|string
     */
    public function stopwatch($start = null) {
        if(!$start)
            return microtime(true);

        $duration = microtime(true) - $start;
        return number_format($duration, 2, '.', '');
    }
}