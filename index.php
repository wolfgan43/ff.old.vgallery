<?php

    function dd($array) {
        echo "<pre>";
        print_r($array);
        die("END");
    }
    //dd($_SERVER);

	require_once(__DIR__ . "/conf/gallery/config/other.php");
	
	function cache_writeLog($string, $filename = "log") {
		//require_once(__DIR__ . "/conf/gallery/config/other.php");

        if(defined("DEBUG_LOG")) {
	        if(!is_dir(__DIR__ . '/cache/logs'))
	            mkdir(__DIR__ . '/cache/logs', 0777, true);
	        
	        $file = __DIR__ . '/cache/logs/' . date("Y-m-d") . "_" . $filename . '.txt';  
	        if(!is_file($file)) {
	            $set_mod = true;
	        }
	        if($handle = @fopen($file, 'a')) 
	        {
	            if(@fwrite($handle, date("Y-m-d H:i:s", time()) . " " . $string . "\n") === FALSE)
	            {
	                $i18n_error = true;
	            }
	            @fclose($handle);

 				if($set_mod)
            		chmod($file, 0777);            
	        }
		}
    }
   
    function profiling_stats($end = false) {
         static $res;
     
        $ru = getrusage();
         if(!$end) {
             $res["mem"] = memory_get_usage(true); 
             $res["mem_peak"]= memory_get_peak_usage(true);
             $res["cpu"] = $ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec'];
         } else {
             $res["mem"] = number_format(memory_get_usage(true) - $res["mem"], 0, ',', '.');
             $res["mem_peak"] = number_format(memory_get_peak_usage(true) - $res["mem_peak"], 0, ',', '.');
             $res["cpu"] = number_format(abs(($ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec']) - $res["cpu"]), 0, ',', '.'); 
            
            cache_writeLog($end . " MEM: " . $res["mem"] . " MEM PEAK: " . $res["mem_peak"] . " CPU: " . $res["cpu"] .  " FROM: " . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"], "profiling");
         }
    }
     
    if(defined("DEBUG_PROFILING"))
        profiling_stats();
    
    error_reporting((E_ALL ^ E_NOTICE ^ E_USER_WARNING ^ E_DEPRECATED) | E_STRICT);
    @ini_set("display_errors", true);

    function check_redirect($path_info, $query = null, $hostname = null) {
        if($hostname === null)
            $hostname = $_SERVER["HTTP_HOST"];
            
        if($query === null)
            $query = $_SERVER["QUERY_STRING"];
        
        $request_uri = $path_info;
        if(strlen($query))
            $request_uri .= "?" . $query;        
        
        if(is_file(__DIR__ . "/cache/redirect/" . $hostname . ".php")) {
            require(__DIR__ . "/cache/redirect/" . $hostname . ".php");

            /** @var include $r */
            if($r[$request_uri]) {
                do_redirect($r[$request_uri]["dst"], $r[$request_uri]["code"]);
            }
        }
    }
    function do_redirect($destination, $http_response_code = null, $request_uri = null) {
        if($http_response_code === null)
            $http_response_code = 301;
        if($request_uri === null)
            $request_uri = $_SERVER["REQUEST_URI"];
    	
    	if($_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] == $destination) {
    	    cache_writeLog("REDIRECT: " . $destination . " FROM: " . $request_uri . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_redirect_error");
    		return;
		}
        
        if(defined("TRACE_VISITOR")) {
            require_once(__DIR__ . "/library/gallery/system/trace.php");
            system_trace("redirect");
        }   

        cache_writeLog("REDIRECT: " . $destination . " FROM: " . $request_uri . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_redirect");

        cache_send_header_content(false, false, false, false); 

        header("Location: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $destination, true, $http_response_code);
        exit;        
    }

    //if(!defined("DISABLE_CACHE"))
    //if(!(DEBUG_MODE && isset($_REQUEST["__nocache1__"])))
	require_once(__DIR__ . "/library/gallery/system/cache.php");
    check_static_cache_page(); 

    $path_info = $_SERVER["PATH_INFO"];
    if($path_info == "/index")
        $path_info = "";

    if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
        check_redirect($path_info);     

   /****
   * Log File Without Cache
   */
    cache_writeLog($path_info);             
  // die();    
    if(!defined("DISABLE_CACHE")) {
        define("FF_ERROR_HANDLER_HIDE", true);
        define("FF_ERROR_HANDLER_CUSTOM_TPL", "/themes/gallery/contents/error_handler.html");
        define("FF_ERROR_HANDLER_MINIMAL", "/themes/gallery/contents/error_handler.html");
    }
    if (!empty($_SERVER["FF_TOP_DIR"])) {
        define("__TOP_DIR__", $_SERVER["FF_TOP_DIR"]);
    } else {
        define("__TOP_DIR__", __DIR__);
    }

    require_once(__TOP_DIR__ . "/cm/main.php");