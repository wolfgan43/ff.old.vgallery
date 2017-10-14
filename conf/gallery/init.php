<?php

    define("DS"                     , DIRECTORY_SEPARATOR);
	define("HIDE_EXT"               , true);
	define("GALLERY_PATH"           , "/gallery");
	define("GALLERY_PATH_SYSTEM"    , GALLERY_PATH . "/sys");
	define("GALLERY_PATH_ECOMMERCE" , GALLERY_PATH . "/ecommerce");
	define("GALLERY_PATH_MANAGE"    , GALLERY_PATH . "/manage");
	define("GALLERY_PATH_MODULE"    , GALLERY_PATH . "/modules");
	define("GALLERY_PATH_JOB"    	, GALLERY_PATH . "/job");
	define("GALLERY_PATH_SERVICES" 	, GALLERY_PATH . "/services");
	define("GALLERY_PATH_AUTH"      , GALLERY_PATH . "/auth");

	define("GALLERY_TPL_PATH"       , "contents");  //_sys
	define("THEME_INSET"			, "gallery");
	define("ADMIN_THEME"			, "admin_v2");
	define("FRONTEND_THEME"			, "site");
	define("USER_RESTRICTED_PATH"	, "/user");

    define("VG_ADDONS_PATH" 									, "/conf/gallery/modules");

	define("DOMAIN_INSET"           , $_SERVER["HTTP_HOST"]);
   
	if(strpos(strtolower(DOMAIN_INSET), "www.") === 0) {
    	define("DOMAIN_NAME"		, substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1));	
	} else {
		define("DOMAIN_NAME"		, DOMAIN_INSET);
	}

	define("OLD_VGALLERY", false);

    if(file_exists(ffCommon_dirname(__FILE__) . "/config/path.php"))
    	require(ffCommon_dirname(__FILE__) . "/config/path.php");
    if(file_exists(ffCommon_dirname(__FILE__) . "/config/db.php"))
    	require(ffCommon_dirname(__FILE__) . "/config/db.php");
	if(file_exists(ffCommon_dirname(__FILE__) . "/config/other.php"))
    	require(ffCommon_dirname(__FILE__) . "/config/other.php");
	    if(file_exists(ffCommon_dirname(__FILE__) . "/config/session.php"))
    		require(ffCommon_dirname(__FILE__) . "/config/session.php");

    if(!defined("SHOWFILES_IS_RUNNING")) {
	    if(file_exists(ffCommon_dirname(__FILE__) . "/config/admin.php"))
    		require(ffCommon_dirname(__FILE__) . "/config/admin.php");
	    if(file_exists(ffCommon_dirname(__FILE__) . "/config/updater.php"))
    		require(ffCommon_dirname(__FILE__) . "/config/updater.php");

	    if(defined("MEMORY_LIMIT") && ini_get("memory_limit") != MEMORY_LIMIT)
	    	@ini_set("memory_limit", MEMORY_LIMIT);
	     //   die("unable set memory_limit: must be " . MEMORY_LIMIT . "\n");
	}

	define("CM_CSSCACHE_RENDER_THEME_PATH", (!defined("APACHE_MODULE_EXPIRES") || APACHE_MODULE_EXPIRES ? false : true));

	define("CM_CACHE_ADAPTER", defined("PHP_EXT_MEMCACHE") && PHP_EXT_MEMCACHE 
		? "memcached" 
		: (defined("PHP_EXT_APC") && PHP_EXT_APC
			? "apc"
			: ""
		)
	);

	define("CM_ENABLE_MEM_CACHING", false);			/* se abiliti CM_ENABLE_MEM_CACHING puoi usare __CLEARCACHE__ nell'url
														per resettare la cache */
	define("FF_ENABLE_MEM_TPL_CACHING", false);		/* questo fa la cache dei template */
	define("FF_ENABLE_MEM_PAGE_CACHING", false);	/* fa la cache dell'elaborazione dei template ed ? + invasiva e complicata 
														ma quando funziona, riduce i tempi di brutto*/
	define("FF_ENABLE_MEM_SHOWFILES_CACHING", CM_CACHE_ADAPTER == "memcached" ? true : false);		/* questo fa la cache dei template */        

	if(defined("MYSQLI_EXTENSIONS") && MYSQLI_EXTENSIONS) {
		define("FF_DB_INTERFACE", "mysqli");
		define("FF_ORM_ENABLE", true);
	} else {
		define("FF_DB_INTERFACE", "mysql");
		define("FF_ORM_ENABLE", false);
	}

	if(defined("SESSION_SAVE_PATH"))
    	session_save_path(SESSION_SAVE_PATH);
	if(defined("SESSION_NAME"))
		session_name(SESSION_NAME);
        
	if(!defined("SHOWFILES_IS_RUNNING")) {
	    if(!defined("LANGUAGE_DEFAULT"))
    		define("LANGUAGE_DEFAULT"       , "ITA");
	    if(!defined("LANGUAGE_DEFAULT_ID"))
    		define("LANGUAGE_DEFAULT_ID"       , "1");
    		
	    if(!isset($config_check["path"]) || !isset($config_check["session"]) || !isset($config_check["db"]) || !isset($config_check["other"])) {
	    //|| !isset($config_check["admin"]) || !isset($config_check["updater"])
	        $host_name = $_SERVER["HTTP_HOST"];
	        if (strpos(php_uname(), "Windows") !== false)
	            $tmp_file = str_replace("\\", "/", __FILE__);
	        else
	            $tmp_file = __FILE__;
	            
		    if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
			    $document_root =  $_SERVER["DOCUMENT_ROOT"];
		        if (substr($document_root,-1) == "/")
		            $document_root = substr($document_root,0,-1);

				$site_path = str_replace($document_root, "", str_replace("/conf/gallery/init.php", "", $tmp_file));
				$disk_path = $document_root . $site_path;
			} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
			    $document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
		        if (substr($document_root,-1) == "/")
		            $document_root = substr($document_root,0,-1);

				$site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/init.php", "", $_SERVER["SCRIPT_FILENAME"]));
				$disk_path = $document_root . str_replace($document_root, "", str_replace("/conf/gallery/init.php", "", $tmp_file));
			} else {
				$st_disk_path = str_replace("/conf/gallery/init.php", "", $tmp_file);
				$st_site_path = str_replace("/conf/gallery/init.php", "", $_SERVER["SCRIPT_NAME"]);
			}

	        if(basename($_SERVER["PATH_INFO"]) == "install") {
	            define("FF_DISK_PATH", $disk_path);
	            define("FF_SITE_PATH", $site_path);
	            define("CM_DONT_RUN_LAYOUT", true);
	            define("EVENT_DONT_RUN", true);
	            define("GALLERY_INSTALLATION_PHASE", true);
	        } elseif(basename($_SERVER["PATH_INFO"]) == "setup") {
	            header("Location: " . $site_path . "/conf/gallery/install?setup");
	            exit;
	        } else {
	        	header("Location: " . $site_path . "/conf/gallery/install");
	        	exit;
	        }
	    } else {
			define("FF_ERROR_HANDLER_LOG", true);
    		define("FF_ERROR_HANDLER_LOG_PATH", FF_DISK_PATH . "/cache/errors");

	        if(basename($_SERVER["PATH_INFO"]) == "install" 
				&& strpos($_SERVER["HTTP_REFERER"], "://" . MASTER_SITE . "/admin/configuration/domain") !== false
			) {
	        	define("BLOCK_INSTALL", true);
				require(FF_DISK_PATH . "/conf/gallery/install/index.php");     
				exit;
	        } 
			if($_SERVER["PATH_INFO"] == "/install") {
				if(isset($_REQUEST["complete"]))
					$complete = "?complete";
				
				header("Location: " . FF_SITE_PATH . "/admin/configuration/install" . $complete);
				exit;
			}         
	    }
	}
?>