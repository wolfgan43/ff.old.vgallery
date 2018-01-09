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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

/**
 * @param bool $start
 * @return mixed
 */
function profiling_stats($start = true) {
	static $res;

	if(function_exists("getrusage"))
	{
		$ru = getrusage();
		if ($start) {
			$res["mem"] = memory_get_usage(true);
			$res["mem_peak"] = memory_get_peak_usage(true);
			$res["cpu"] = $ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec'];
		} else {
			$res["mem"] = number_format(memory_get_usage(true) - $res["mem"], 0, ',', '.');
			$res["mem_peak"] = number_format(memory_get_peak_usage(true) - $res["mem_peak"], 0, ',', '.');
			$res["cpu"] = number_format(abs(($ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec']) - $res["cpu"]), 0, ',', '.');
			$res["includes"] = get_included_files();
			$res["classes"] = get_declared_classes();

			return $res;
		}
	}
}
function profiling_stopwatch($start)
{
	if(!$start)
		return microtime(true);

	$duration = microtime(true) - $start;
	return number_format($duration, 2, '.', '');
}
/**
 * Config
 */
require_once("config.php");

/**
 * Performance Profiling
 */
if(DEBUG_PROFILING === true)
	profiling_stats();

/**
 * Cache System
 */
require_once(__DIR__ . "/library/gallery/system/cache.php");
$cache = check_static_cache_page();



$path_info = $_SERVER["PATH_INFO"];
if($path_info == "/index")
	$path_info = "";

/**
 * Check Redirect
 */
if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
	cache_check_redirect($path_info);

/**
 * Log File Without Cache
 */
cache_writeLog($path_info);

/**
 * Run page Without Cache
 */
require_once("cm/main.php");