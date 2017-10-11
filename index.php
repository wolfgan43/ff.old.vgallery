<?php
/**
 * CONFIG
 */

//require_once(__DIR__ . "/config.php");


/**
 * PROFILING
 */

/**
 * @param bool $start
 * @return mixed
 */
function profiling_stats($start = true) {
     static $res;

    $ru = getrusage();
     if($start) {
         $res["mem"] = memory_get_usage(true);
         $res["mem_peak"]= memory_get_peak_usage(true);
         $res["cpu"] = $ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec'];
     } else {
         $res["mem"] = number_format(memory_get_usage(true) - $res["mem"], 0, ',', '.');
         $res["mem_peak"] = number_format(memory_get_peak_usage(true) - $res["mem_peak"], 0, ',', '.');
        $res["cpu"] = number_format(abs(($ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec']) - $res["cpu"]), 0, ',', '.');

        return $res;
     }
}


if(defined("DEBUG_MODE") && isset($_REQUEST["__debug__"]))
    profiling_stats();


/**
 * ERROR
 */
error_reporting((E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED) | E_STRICT);
@ini_set("display_errors", true);

/**
 * TIMEZONE
 */
date_default_timezone_set("Europe/Rome");

// ***************
//  FILE HANDLING
// ***************
@umask(0);


/**
 * Load cache System
 */
require_once(__DIR__ . "/library/gallery/system/cache.php");
check_static_cache_page();


$path_info = $_SERVER["PATH_INFO"];
if($path_info == "/index")
    $path_info = "";

if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
    cache_check_redirect($path_info);

/**
* Log File Without Cache
*/
if(defined("DEBUG_MODE"))
    cache_writeLog($path_info);

if (isset($_ENV["FF_TOP_DIR"]))
    define("__TOP_DIR__", $_ENV["FF_TOP_DIR"]);
else
    define("__TOP_DIR__", __DIR__);

require_once(__TOP_DIR__ . "/cm/main.php");