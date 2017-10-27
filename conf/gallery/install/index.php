<?php

     if(!function_exists("check_primary_class")) {
        function check_primary_class($disk_path, $site_path, $redirect = true) {
            $arrError = array();

            if(!defined("FF_PHP_EXT")) define("FF_PHP_EXT", "php");
            if (!defined("FF_ENABLE_MEM_TPL_CACHING"))    define("FF_ENABLE_MEM_TPL_CACHING", false);
            if (!defined("FF_ENABLE_MEM_PAGE_CACHING")) define("FF_ENABLE_MEM_PAGE_CACHING", false);
            if (!defined("FF_CACHE_ADAPTER")) define("FF_CACHE_ADAPTER", "");

            if(!class_exists("ffTemplate")) {
            	if(file_exists($disk_path . "/ff/classes/ffTemplate.php"))
                	require_once($disk_path . "/ff/classes/ffTemplate.php");
            	else 
                	$arrError[] = "Missing ffTemplate";
            }
            if(!class_exists("ffCommon")) {
            	if(file_exists($disk_path . "/ff/classes/ffCommon.php"))
                	require_once($disk_path . "/ff/classes/ffCommon.php");
	            else
	                $arrError[] = "Missing ffCommon";
            }
            if(!class_exists("ffEvents")) {
            	if(file_exists($disk_path . "/ff/classes/ffEvents/ffEvents.php"))
                	require_once($disk_path . "/ff/classes/ffEvents/ffEvents.php");
	            else
	                $arrError[] = "Missing ffEvents";
	        }
            if(!class_exists("ffMemCache")) {
                if(file_exists($disk_path . "/ff/classes/ffCache/ffCache.php")) {
                	require_once($disk_path . "/ff/classes/ffCache/ffCache.php");
            		if(file_exists($disk_path . "/ff/classes/ffCache/ffCacheAdapter.php"))
                		require_once($disk_path . "/ff/classes/ffCache/ffCacheAdapter.php");
				} else
	                $arrError[] = "Missing ffMemCache";
            }
            if(!class_exists("ffData")) {
            	if(file_exists($disk_path . "/ff/classes/ffData/ffData.php"))
                	require_once($disk_path . "/ff/classes/ffData/ffData.php");
	            else
	                $arrError[] = "Missing ffData";
            }        
            if(!class_exists("ffDb_Sql")) {
            	if(file_exists($disk_path . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php"))
                	require_once($disk_path . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
	            else
	                $arrError[] = "Missing ffDb_Sql";
            }        

            if(!function_exists("ffCommon_charset_encode")) {
            	if(file_exists($disk_path . "/ff/common.php"))
                	require_once($disk_path . "/ff/common.php");
	            else
	                $arrError[] = "Missing FF Library";
            }     
          
            if(count($arrError)) {
                if($redirect) {
                    header("Location: " . $site_path . "/conf/gallery/install?init=1&error=" . urlencode(implode(", ", $arrError)));
                    exit;          
                } else 
                    return implode(", ", $arrError);
            }
        }
    }

	if(!function_exists("file_post_contents")) {
		function file_post_contents($url, $data = null, $username = null, $password = null, $method = "POST", $timeout = 60) {
			if(!$username && defined("AUTH_USERNAME"))
				$username 				= AUTH_USERNAME;
			if(!$password && defined("AUTH_PASSWORD"))
				$password 				= AUTH_PASSWORD;

			if($data)
				$postdata 				= http_build_query($data);

			$headers = array();
			if($method == "POST")
				$headers[] 				= "Content-type: application/x-www-form-urlencoded";
			if($username)
				$headers[] 				= "Authorization: Basic " . base64_encode($username . ":" . $password);

			$opts = array(
				'ssl' => array(
					"verify_peer" 		=> false,
					"verify_peer_name" 	=> false
				),
				'http' => array(
					'method'  			=> $method,
					'timeout'  			=> $timeout,
					'header'  			=> implode("\r\n", $headers),
					'content' 			=> $postdata
				)
			);

			$context = stream_context_create($opts);
			return @file_get_contents($url, false, $context);
		}
	}

	if(defined("BLOCK_INSTALL") || isset($_REQUEST["complete"])) {
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
			echo '<html>';
			echo '<head>';
                        echo '<style>
                                h1 {margin-top: 30%; font-family: verdana;font-weight:lighter;}

                                h1 a{
                                     padding: 10px 5px;
                                     border-width: 1px 1px 4px;
                                     border-color: #2F68CD;
                                     border-style: solid;
                                     background: none repeat scroll 0% 0% #4081F5;
                                     color: #FFF;
                                     text-transform: uppercase;
                                     font-size: 0.7em;
                                     width: 170px;
                                     margin: 25% auto 0px;
                                     text-decoration: none;
                                     font-weight:lighter;
                                     transition:0.5;
                                     }
                                     
                                h1 a:hover{background: none repeat scroll 0% 0% #2F68CD;}
                              </style>';
			echo '<title>Gallery Installation</title>';
			echo '<meta http-equiv="Cache-Control" content="no-cache">';
			echo '<meta http-equiv="Pragma" content="no-cache">';
			echo '</head>';
			echo '<body>';
			echo '<div style="margin: 0 auto; width:300px; text-align:center;"><h1>Installation complete.<a style="display:block;" href="javascript:void(0);" onclick="if(window != window.top) { window.open(\'http' . ($_SERVER["HTTPS"] ? "s": "") . "://" . $_SERVER["HTTP_HOST"] . '/admin/configuration/updater\', \'_blank\' ); } else { window.location.href = \'/admin/configuration/updater\'; }">Go to Site</a></h1></div>';
			echo '</body>';
			echo '</html>';
			exit;
	}

	error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);

   // require("../sources/ffDb_Sql_mysql.php");
  //  require("../sources/ffTemplate.php");

/*creazione interfaccia
  DATABASE
   Richiesta  = > DATABASE_NAME
                  DATABASE_HOST
                  DATABASE_USER
                  DATABASE_PASSWORD
  USER SUPERADMIN
   Richiesta  = > Username SuperAdmin
                  Password SuperAdmin    
  SMTP SETTINGS
   Richiesta  = > A_SMTP_HOST
                  SMTP_AUTH
                  A_SMTP_USER
                  A_SMTP_PASSWORD
                  A_SMTP_PORT
                  A_SMTP_SECURE

  EMAIL SETTINGS
   Richiesta  = > A_FROM_EMAIL
                  A_FROM_NAME
                  CC_FROM_EMAIL
                  CC_FROM_NAME
                  BCC_FROM_EMAIL
                  BCC_FROM_NAME
  
  * Verifica della validita dei campi  
  Creazione del database
  * Verifica della creazione del database
  Importazione della struttura delle tabelle del database da file `Structure.sql`
  * Verifica della importazione della struttura delle tabelle
  Importazione dei dati preliminari delle tabelle del database da file `data.sql`
  * Verifica della importazione dei dati preliminari delle tabelle    

  Creazione del file config_db.php
    **
    * define("DATABASE_NAME", "www_redexport_it");
    * define("DATABASE_HOST", "localhost");
    * define("DATABASE_USER", "redexport");
    * define("DATABASE_PASSWORD", "pippopluto");
    *
    * $config_check["db"] = true;
    **

  * Verifica della creazione del file config_db.php

  Creazione del file other.php
    **
    * define("A_SMTP_HOST", "localhost");
    * define("SMTP_AUTH", true);
    * define("A_SMTP_USER", "forms@b2arts.com");
    * define("A_SMTP_PASSWORD", "Spatural2008");
    * define("A_SMTP_PORT", '25');  
    * define("A_SMTP_SECURE", '');                    
    * 
    * $domain_name = DOMAIN_NAME;
    * $domain_sig = substr($domain_name, 0, strpos($domain_name, "."));
    *
    * define("A_FROM_EMAIL", "info@" . $domain_name);
    * define("A_FROM_NAME", $domain_sig);
    * define("CC_FROM_EMAIL", "");
    * define("CC_FROM_NAME", "");
    * define("BCC_FROM_EMAIL", "forms@b2arts.com");
    * define("BCC_FROM_NAME", "test[" . DOMAIN_INSET . "]");
    * 
    * $config_check["email"] = true;
    **

  * Verifica della creazione del file other.php

  Redirect alla home Page
*/



	if(function_exists("apache_get_modules")) {
		$PHP_fastCGI = false;
		if(!in_array('mod_rewrite', apache_get_modules()))
			die("Apachee Module Rewrite must be Loaded");
	} else 
		$PHP_fastCGI = true;

	$total_php_value_need++;
	if(version_compare(phpversion(), "5.3.0", "<") && ini_get("safe_mode"))
		if(ini_set("safe_mode", "0") === false)
	    	$strCriticalError .= "safe_mode must be Disabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;
	
	$total_php_value_need++;
	if(version_compare(phpversion(), "5.3.0", "<") && ini_get("register_globals"))
		if(ini_set("register_globals", "0") === false)
	    	$strCriticalError .= "register_globals must be Disabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("mysql.max_persistent") != "-1")
		if(ini_set("mysql.max_persistent", "-1") === false)
	    	$strCriticalError .= "mysql.max_persistent must be Unlimited\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(!ini_get("display_errors"))
		if(ini_set("display_errors", "1") === false)
	    	$strWarningError .= "display_errors must be Enabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;

/*	$total_php_value_need++;
	if(ini_get("request_order") != "EGPCS")
		if(ini_set("request_order", "EGPCS") === false)
	    	$strWarningError .= "request_order must be EGPCS\n";
	    else
			$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("max_input_time") < 1000)
		if(ini_set("max_input_time", "1000") === false)
	    	$strWarningError .= "max_input_time must be 1000\n";
	    else
			$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("post_max_size") != "100M")
		if(ini_set("post_max_size", "100M") === false)
	    	$strWarningError .= "post_max_size must be 100M\n";
	    else
			$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("upload_max_filesize") != "100M")
		if(ini_set("upload_max_filesize", "100M") === false)
	    	$strWarningError .= "upload_max_filesize must be 100M\n";
	    else
			$allow_rewite_php_value_on_htaccess++;
*/
	$total_php_value_need++;
	if(ini_get("magic_quotes_gpc"))
		if(ini_set("magic_quotes_gpc", "0") === false)
	    	$strWarningError .= "magic_quotes_gpc must be Disabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("magic_quotes_runtime"))
		if(ini_set("magic_quotes_runtime", "0") === false)
	    	$strWarningError .= "magic_quotes_runtime must be Disabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;
	    	
	$total_php_value_need++;
	if(ini_get("magic_quotes_sybase"))
		if(ini_set("magic_quotes_sybase", "0") === false)
	    	$strWarningError .= "magic_quotes_sybase must be Disabled\n";
	    else
	    	$allow_rewite_php_value_on_htaccess++;

	if(ini_get("memory_limit"))
		if(ini_set("memory_limit", "96M") === false)
	    	$strWarningError .= "unable set memory_limit\n";

    if(strlen($strCriticalError))
        die($strCriticalError);

    $strCriticalError = $_REQUEST["error"];

    if(!function_exists("ffCommon_dirname")) {
        function ffCommon_dirname($path) 
        {
            $res = dirname($path);
            if(dirname("/") == "\\")
                $res = str_replace("\\", "/", $res);
            
            if($res == ".")
                $res = "";
                
            return $res;
        }
    }    
    
	/**
	*  Define default Vars
	*/
    $st_host_name = $_SERVER["HTTP_HOST"];
	if(strpos($st_host_name, "www.") === 0) {
    	$st_domain_name = substr($st_host_name, 4);
	} else {
        $st_domain_name = $st_host_name;
	}
	$st_domain_sig = substr($st_domain_name, 0, strpos($st_domain_name, "."));
    
    if (strpos(php_uname(), "Windows") !== false)
        $tmp_file = str_replace("\\", "/", __FILE__);
    else
        $tmp_file = __FILE__;

    if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($st_document_root, "", str_replace("/conf/gallery/install/index.php", "", $tmp_file));
		$st_disk_path = $st_document_root . $st_site_path;
	} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/install/index.php", "", $_SERVER["SCRIPT_FILENAME"]));
		$st_disk_path = $st_document_root . str_replace($st_document_root, "", str_replace("/conf/gallery/install/index.php", "", $tmp_file));
	} else {
		$st_disk_path = str_replace("/conf/gallery/install/index.php", "", $tmp_file);
		$st_site_path = str_replace("/conf/gallery/install/index.php", "", $_SERVER["SCRIPT_NAME"]);
	}

	$st_site_updir = $st_site_path . "/uploads";
	$st_disk_updir = $st_disk_path . "/uploads";

    $st_session_save_path = session_save_path();
    if(!(@is_dir($st_session_save_path) && @is_writable($st_session_save_path))) {
        $open_basedir = explode(":", ini_get("open_basedir"));
        if(is_array($open_basedir) && count($open_basedir) > 0) {
            foreach ($open_basedir AS $open_basedir_key => $open_basedir_value) {
                if(strlen($open_basedir_value)) {
                    if(@is_dir($open_basedir_value) && @is_writable($open_basedir_value)) {
                        $st_session_save_path = $open_basedir_value;
                        break;
                    }
                }
            }
            if(!strlen($st_session_save_path)) {
                $st_session_save_path = ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/sessions";
                if(!(@is_dir($st_session_save_path) && @is_writable($st_session_save_path))) {
                    $st_session_save_path = "";
                } 
            }
        }
    }   

    $st_appid = md5(ffCommon_dirname(__FILE__)) . "-" . md5($st_domain_name);
    $st_session_name = "PHPSESS_" . substr($st_appid, 1, 8);
    $st_memory_limit = "96M";
    $st_cdn_static = "";
    
    $st_character_set = "utf8";
    $st_collation = "utf8_unicode_ci";
        
	$arrLang = array(
		"ENG" => 2
		, "ITA" => 1
		, "ESP" => 4
		, "FRA" => 3
		, "DEU" => 5
	);

	$frmAction = $_REQUEST["frmAction"];
    if($frmAction == "install") 
    {
        $disk_path              = $_REQUEST["FF_DISK_PATH"];
        $site_path              = $_REQUEST["FF_SITE_PATH"];

		if(strlen($site_path))
        	$site_path_rev 		= ltrim($site_path, "/") . "/";
		else
			$site_path_rev		= "";       

        $disk_updir             = $_REQUEST["DISK_UPDIR"];
        $site_updir             = $_REQUEST["SITE_UPDIR"];

        $session_save_path      = $_REQUEST["SESSION_SAVE_PATH"];
        $session_name           = $_REQUEST["SESSION_NAME"];
        $appid                  = $_REQUEST["APPID"];
		$memory_limit           = $_REQUEST["MEMORY_LIMIT"];
		$cdn_static           	= $_REQUEST["CDN_STATIC"];
        
        $database_name          = $_REQUEST["FF_DATABASE_NAME"];
        $database_host          = $_REQUEST["FF_DATABASE_HOST"];
        $database_username      = $_REQUEST["FF_DATABASE_USER"];
        $database_password      = $_REQUEST["FF_DATABASE_PASSWORD"];
        $database_conf_password = $_REQUEST["FF_DATABASE_CONF_PASSWORD"];
        $reset_database         = $_REQUEST["RESET_DATABASE"];

		$character_set          = $_REQUEST["CHARACTER_SET"];
		$collation		        = $_REQUEST["COLLATION"];
        
        $smtp_host              = $_REQUEST["A_SMTP_HOST"];
        $smtp_auth              = $_REQUEST["SMTP_AUTH"];
        $smtp_username          = $_REQUEST["A_SMTP_USER"];
        $smtp_password          = $_REQUEST["A_SMTP_PASSWORD"];
        $smtp_conf_password     = $_REQUEST["A_SMTP_CONF_PASSWORD"];
		$smtp_port              = $_REQUEST["A_SMTP_PORT"];
		$smtp_secure            = $_REQUEST["A_SMTP_SECURE"];
        
        $email_address          = $_REQUEST["A_FROM_EMAIL"];
        $email_name             = $_REQUEST["A_FROM_NAME"];
        $cc_address             = $_REQUEST["CC_FROM_EMAIL"];
        $cc_name                = $_REQUEST["CC_FROM_NAME"];
        $bcc_address            = $_REQUEST["BCC_FROM_EMAIL"];
        $bcc_name               = $_REQUEST["BCC_FROM_NAME"];
        
        $site_title             = $_REQUEST["SITE_TITLE"];
        $language_default       = $_REQUEST["LANGUAGE_DEFAULT"];
        $language_default_id	= $arrLang[$language_default];

//        $trace_path			    = $_REQUEST["TRACE_PATH"];

        $debug_mode   			= $_REQUEST["DEBUG_MODE"];
        $debug_profiling   		= $_REQUEST["DEBUG_PROFILING"];
        $debug_log   			= $_REQUEST["DEBUG_LOG"];
        $trace_visitor 			= $_REQUEST["TRACE_VISITOR"];
        
        $username               = $_REQUEST["Username"];
        $password               = $_REQUEST["Password"];
        $conf_password          = $_REQUEST["Conf_Password"];

        $master_site            = $_REQUEST["MASTER_SITE"];
        $production_site        = $_REQUEST["PRODUCTION_SITE"];
        $development_site		= $_REQUEST["DEVELOPMENT_SITE"];
        
        $ftp_username           = $_REQUEST["FTP_USERNAME"];
        $ftp_password           = $_REQUEST["FTP_PASSWORD"];
        $ftp_confirm_password   = $_REQUEST["FTP_CONFIRM_PASSWORD"];

        $auth_username           = $_REQUEST["AUTH_USERNAME"];
        $auth_password           = $_REQUEST["AUTH_PASSWORD"];

        if($cdn_static) {
			if(strlen($production_site)) {
				$arrDomain 					= explode(".", $production_site);
			} else {
				$arrDomain 					= explode(".", $st_host_name);
			}

			$str_static_cdn_domain_name 	= 'define("CM_SHOWFILES", \'' . addslashes('http://static.' . $arrDomain[count($arrDomain) - 2] . "." . $arrDomain[count($arrDomain) - 1]) . '\');';
			$str_media_cdn_domain_name 		= 'define("CM_MEDIACACHE_SHOWPATH", \'' . addslashes('http://media.' . $arrDomain[count($arrDomain) - 2] . "." . $arrDomain[count($arrDomain) - 1]) . '\');';
		} else {
			$str_static_cdn_domain_name 	= 'define("CM_SHOWFILES", \'' . addslashes('/cm/showfiles.php') . '\');';
			$str_media_cdn_domain_name 		= 'define("CM_MEDIACACHE_SHOWPATH", \'' . addslashes('/' . $site_path_rev . 'media') . '\');';
		}

        if(!strlen($disk_path))
            $strError .= "Disk Path empty <br />";

        if(!strlen($disk_updir))
            $strError .= "Disk UpDir empty <br />";

        if(!strlen($site_updir))
            $strError .= "Site UpDir empty <br />";

        if(!(@is_dir($session_save_path) && @is_writable($session_save_path)))
            $strError .= "Session save path wrong <br />";

        if(!strlen($session_name))
            $strError .= "Session name empty <br />";

        if(!strlen($appid))
            $strError .= "Application ID empty <br />";

        if(!strlen($memory_limit))
            $strError .= "Memory limit  empty <br />";

        if(!strlen($database_name))
            $strError .= "Database name empty <br />";

        if(!strlen($database_host))
            $strError .= "Database host empty <br />";

        if(!strlen($database_username))
            $strError .= "Database username empty <br />";

        if(!strlen($database_password) || $database_password != $database_conf_password) {
            $database_password = "";
            $database_conf_password = "";
            $strError .= "Database password no match<br />";
        }    

        if(!strlen($character_set))
            $strError .= "Character set  empty <br />";

        if(!strlen($collation))
            $strError .= "Collation  empty <br />";

        if(!strlen($smtp_host))
            $strError .= "SMTP host empty <br />";

        if($smtp_auth) {
            if(!strlen($smtp_username))
                $strError .= "SMTP username empty <br />";

            if(!strlen($smtp_password) || $smtp_password != $smtp_conf_password) {
                $smtp_password = "";
                $smtp_conf_password = "";
                $strError .= "SMTP password no match<br />";
            }
        }

        if(!strlen($email_address))
            $strError .= "Site Email-address empty <br />";
        
        if(!strlen($email_name))
            $strError .= "Site Email-name empty <br />";

        if(!strlen($username))
            $strError .= "Admin Username empty <br />";

        if(!strlen($password) || $password != $conf_password) {
            $password = "";
            $conf_password = "";
            $strError .= "Admin Password no match<br />";
        }
		
		$ftp_path = null;
        if(strlen($ftp_username)) {
	        if($ftp_password != $ftp_confirm_password) {
	            $password = "";
	            $conf_password = "";
	            $strError .= "FTP Password no match<br />";
	        } else {
				$conn_id = @ftp_connect("localhost");
		        if($conn_id === false)
        			$conn_id = @ftp_connect("127.0.0.1");
				if($conn_id === false)
        			$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
				
				if($conn_id !== false) {
					// login with username and password
					if(@ftp_login($conn_id, $ftp_username, $ftp_password)) {
						$local_path = $disk_path;
						$part_path = "";
						$real_ftp_path = NULL;
						
						foreach(explode("/", $local_path) AS $curr_path) {
						    if(strlen($curr_path)) {
						        $ftp_path = str_replace($part_path, "", $local_path);
						        if(@ftp_chdir($conn_id, $ftp_path)) {
						            $real_ftp_path = $ftp_path;
						            break;
						        } 

						        $part_path .= "/" . $curr_path;
						    }
						}
						
						if($real_ftp_path === null) {
							if(@ftp_chdir($conn_id, "/conf/gallery/install")) {
								$real_ftp_path = "/";
							}
						}
					} else {
						$strError .= "FTP Login Incorrect<br />";
					}
				} else {
					$strError .= "FTP Connection Failure<br />";
				} 
				//@ftp_close($conn_id);
				if($real_ftp_path === null) {
					$strError .= "FTP Path unavailable<br />";
				} else {
					$ftp_path = $real_ftp_path;
				}
			}
		}

        check_primary_class($disk_path, $site_path);
		
        $db_install = new ffDB_Sql;
        $db_install->halt_on_connect_error = false;
        $db_res = @$db_install->connect($database_name, $database_host, $database_username, $database_password);

        if(!$db_res)
            $strError .= "Connection failed to database " . $database_name . "<br />";
        
        if(!is_readable($disk_path . "/conf/gallery/install/structure.sql") || filesize($disk_path . "/conf/gallery/install/structure.sql") <= 0)
            $strError .= "Unable read file: " . $disk_path . "/conf/gallery/install/structure.sql" . "<br />";

        if(!is_readable($disk_path . "/conf/gallery/install/data.sql") || filesize($disk_path . "/conf/gallery/install/data.sql") <= 0)
            $strError .= "Unable read file: " . $disk_path . "/install/data.sql" . "<br />";

		if(version_compare(phpversion(), "5.3.0", "<") && ini_get("safe_mode"))
			if(ini_set("safe_mode", "0") === false)
	    		$safe_mode = true;
		    else
	    		$safe_mode = false;

        if(!$strError) {
            $dirname_relative = "/conf/gallery/config";
            @ftp_chmod($conn_id, 0755, $ftp_path . $dirname_relative);
            
/*            $filename = $disk_path . $dirname_relative;
            
            $perm_dir_change = false;
            if(!is_writable($filename) && $ftp_path !== null) {
                $perm_dir_change = true;
                @ftp_chmod($conn_id, 0777, $ftp_path . $dirname_relative);
            }
*/            

            ///////////////////////////////
            //Scrittura del file db.php
            ///////////////////////////////
            $filename_relative = $dirname_relative . "/db." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            $config_content = '<?php
    define("FF_DATABASE_NAME", \'' . addslashes($database_name) . '\');
    define("FF_DATABASE_HOST", \'' . addslashes($database_host) . '\');
    define("FF_DATABASE_USER", \'' . addslashes($database_username) . '\');
    define("FF_DATABASE_PASSWORD", \'' . addslashes($database_password) . '\');

    define("DB_CHARACTER_SET", \'' . addslashes($character_set) . '\');
    define("DB_COLLATION", \'' . addslashes($collation) . '\');
    
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            ///////////////////////////////

            
            ///////////////////////////////
            //Scrittura del file path.php
            ///////////////////////////////
            $filename_relative = $dirname_relative . "/path." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            $config_content = '<?php
    define("FF_DISK_PATH", \'' . addslashes($disk_path) . '\');
    define("FF_SITE_PATH", \'' . addslashes($site_path) . '\');

    define("DISK_UPDIR", \'' . addslashes($disk_updir) . '\');
    define("SITE_UPDIR", \'' . addslashes($site_updir) . '\');
    
    $config_check["path"] = true;
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            ///////////////////////////////
            
            
            ///////////////////////////////
            //Scrittura del file session.php
            ///////////////////////////////
            $filename_relative = $dirname_relative . "/session." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            
            $config_content = '<?php
    define("SESSION_SAVE_PATH", \'' . addslashes($session_save_path) . '\');
    define("SESSION_NAME", \'' . addslashes($session_name) . '\');
    define("SESSION_PERMANENT", true);
    define("APPID", \'' . addslashes($appid) . '\');
    define("MEMORY_LIMIT", \'' . addslashes($memory_limit) . '\');
    
    $config_check["session"] = true;
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            ///////////////////////////////


            ///////////////////////////////
            //Scrittura del file other.php
            ///////////////////////////////
			if(function_exists("get_loaded_extensions"))
				$php_ext_loaded = get_loaded_extensions();
			else
				$php_ext_loaded = array();
			
			if(function_exists("apache_get_modules"))
				$apache_module_loaded = apache_get_modules();
			else
				$apache_module_loaded = array();
            
            $filename_relative = $dirname_relative . "/other." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            $config_content = '<?php
    define("A_SMTP_HOST", \'' . addslashes($smtp_host) . '\');
    define("SMTP_AUTH", ' . ($smtp_auth ? 'true' : 'false') . ');
    define("A_SMTP_USER", \'' . addslashes($smtp_username) . '\');
    define("A_SMTP_PASSWORD", \'' . addslashes($smtp_password) . '\');
    define("A_SMTP_PORT", \'' . addslashes($smtp_port) . '\');
    define("A_SMTP_SECURE", \'' . addslashes($smtp_secure) . '\');

    define("A_FROM_EMAIL", \'' . addslashes($email_address) . '\');
    define("A_FROM_NAME", \'' . addslashes($email_name) . '\');
    define("CC_FROM_EMAIL", \'' . addslashes($cc_address) . '\');
    define("CC_FROM_NAME", \'' . addslashes($cc_name) . '\');
    define("BCC_FROM_EMAIL", \'' . addslashes($bcc_address) . '\');
    define("BCC_FROM_NAME", \'' . addslashes($bcc_name) . '\');
    
    define("CM_LOCAL_APP_NAME", \'' . addslashes($site_title) . '\');
    define("LANGUAGE_DEFAULT", \'' . addslashes($language_default) . '\');
    define("LANGUAGE_DEFAULT_ID", \'' . addslashes($language_default_id) . '\');
    define("LANGUAGE_RESTRICTED_DEFAULT", \'' . addslashes($language_default) . '\');

    ' . ($debug_mode ? ' define("DEBUG_MODE", true);' : '') . '
    ' . ($debug_profiling ? ' define("DEBUG_PROFILING", true);' : '') . '
    ' . ($debug_log ? ' define("DEBUG_LOG", true);' : '') . '
    ' . ($trace_visitor ? ' define("TRACE_VISITOR", true);' : '') . '
    
 	' . $str_static_cdn_domain_name . '
 	' . $str_media_cdn_domain_name . '
    define("CDN_STATIC", \'' . addslashes($cdn_static) . '\');
    ' . (count($php_ext_loaded)
    	? '
    define("PHP_EXT_MEMCACHE", \'' . (array_search("memcached", $php_ext_loaded) === false
											? false
											: true
									) . '\');
	define("PHP_EXT_APC", \'' . (array_search("apc", $php_ext_loaded) === false
											? false
											: true
									) . '\');
	define("PHP_EXT_JSON", \'' . (array_search("json", $php_ext_loaded) === false
											? false
											: true
									) . '\');
	define("PHP_EXT_GD", \'' . (array_search("gd", $php_ext_loaded) === false
											? false
											: true
									) . '\');'
    	: ''
    ) 
    . (count($apache_module_loaded)
    	? '
    define("APACHE_MODULE_EXPIRES", \'' . (array_search("mod_expires", $apache_module_loaded) === false
											? false
											: true
									) . '\');'
		: ''
	) . '
	define("MYSQLI_EXTENSIONS", \'' . (function_exists("mysqli_init") && function_exists('mysqli_fetch_all')
											? true
											: false
									) . '\');

    $config_check["other"] = true;
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            /////////////////////////////// 

            
            ///////////////////////////////
            //Scrittura del file admin.php
            ///////////////////////////////
            $filename_relative = $dirname_relative . "/admin." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            $config_content = '<?php
    define("SUPERADMIN_USERNAME", \'' . addslashes($username) . '\');
    define("SUPERADMIN_PASSWORD", \'' . addslashes($password) . '\');

    $config_check["admin"] = true;
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            ///////////////////////////////
            
            
            ///////////////////////////////
            //Scrittura del file updater.php
            ///////////////////////////////
            $filename_relative = $dirname_relative . "/updater." . FF_PHP_EXT;
            $filename = $disk_path . $filename_relative;
            $config_content = '<?php
    define("MASTER_SITE", \'' . addslashes($master_site) . '\');
    define("PRODUCTION_SITE", \'' . addslashes($production_site) . '\');
    define("DEVELOPMENT_SITE", \'' . addslashes($development_site) . '\');

    define("FTP_USERNAME", \'' . addslashes($ftp_username) . '\');
    define("FTP_PASSWORD", \'' . addslashes($ftp_password) . '\');
    define("FTP_PATH", \'' . addslashes($ftp_path) . '\'); 

    define("AUTH_USERNAME", \'' . addslashes($auth_username) . '\');
    define("AUTH_PASSWORD", \'' . addslashes($auth_password) . '\');
    
    $config_check["updater"] = true;
';
           
            $tempHandle = @tmpfile();
            @fwrite($tempHandle, $config_content);
            @rewind($tempHandle);

            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
                @ftp_delete($conn_id, $ftp_path . $filename_relative);
            }
            
            $ret = @ftp_nb_fput($conn_id
                                , $ftp_path . $filename_relative
                                , $tempHandle
                                , FTP_BINARY
                                , FTP_AUTORESUME
                            );
            while ($ret == FTP_MOREDATA) {
               // Do whatever you want
               // Continue upload...
               $ret = @ftp_nb_continue($conn_id);
            }
            if ($ret != FTP_FINISHED) {
               $strError .= "Unable write file: " . $filename_relative . "<br />";
            } else {
                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
            }
            ///////////////////////////////
			if(!$strError) {
				$sSQL = "SHOW TABLES";
	            $db_install->query($sSQL);
	            if(!$db_install->nextRecord()) {            
            		$reset_database = true;
				}   
	            if(($safe_mode || !$master_site) && $reset_database) {
                //Creazione e Scrittura della struttura del database
                // Aggiorna il database utilizzando il file locale che descrive la struttura di base
                    $filename = $disk_path . "/conf/gallery/install/structure.sql";
                    if (!$handle = @fopen($filename, "r")) {
                        $strError .= "Unable read file: " . "/conf/gallery/install/structure.sql" . "<br />";
                        $sql_structure = "";
                    } else {
                        $sql_structure = @fread($handle, @filesize($filename));
                        @fclose($handle);
                        
                        $structure = explode(";", trim($sql_structure));
                        foreach($structure AS $structure_value) {
                            if(strlen($structure_value)) {
                                $db_install->execute($structure_value . ";");
                            }
                        }
                	}
                //Scrittura dei dati preliminari del database
                // Aggiorna il database utilizzando il file locale che descrive i dati di base
                    $filename = $disk_path . "/conf/gallery/install/data.sql";
                    if (!$handle = @fopen($filename, "r")) {
                        $strError .= "Unable read file: " . "/conf/gallery/install/data.sql" . "<br />";
                        $sql_data = "";
                    } else {
                        $sql_data = @fread($handle, @filesize($filename));
                        @fclose($handle);

                        $sql_data = str_replace("[SUPERADMIN_NAME]", $username, $sql_data);
                        $sql_data = str_replace("[SUPERADMIN_PASSWORD]", $db_install->mysqlPassword($password), $sql_data);
                        
                        $data = explode(");", trim($sql_data));
                        foreach($data AS $data_value) {
                            if(strlen($data_value)) {
                                $db_install->execute($data_value . ");");
                            }
                        }  
                    }
				}
            }

            if($master_site) {
                if(!$strError && $reset_database) {
                    $truncate_db = array();
                    
                    $sSQL = "SHOW TABLES";
                    $db_install->query($sSQL);
                    if($db_install->nextRecord()) {
                        do {
                            $truncate_db[$db_install->getField("Tables_in_" . $database_name, "Text", true)] = true;
                        } while($db_install->nextRecord());
                    }
                   
                    if(is_array($truncate_db) && count($truncate_db)) {
                        foreach($truncate_db AS $truncate_db_key => $truncate_db_value) {
                            if($truncate_db_value) {
                                $sSQL = "TRUNCATE `" . $db_install->toSql($truncate_db_key, "Text", false) . "`";
                                $db_install->execute($sSQL);
                            }
                        }
                    }

                    $sSQL = "CREATE TABLE IF NOT EXISTS `cm_mod_security_users_rel_groups` (
                              `uid` int(4) NOT NULL default '0',
                              `gid` int(4) NOT NULL default '0',
                              UNIQUE KEY `uid` (`uid`,`gid`)
                            )";
                    $db_install->execute($sSQL);
                } 
                
                if(!$safe_mode) {
                    // CERCA di sincronizzare il database con il MASTER SITE in merito alla STRUTTURA
                    if(!$strError) {
                        // Tenta di sincronizzare la STRUTTURA DB con il MASTER SITE
                        $count_limit = 0;
                        do {
                            
                            $count_limit++;

							$json = file_post_contents(
								"http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/structure.php?json=1&exec=1&nowarning=1&lo=" . urlencode("-1")
								, null
								, $auth_username
								, $auth_password
								, "GET"
								, "120"
							);

                            $arr_json = json_decode($json, true);
                            if(is_array($arr_json)) {
                                if(count($arr_json)) {
                                    if(array_key_exists("error", $arr_json)) {
                                        $strError = "Structure: " . ($arr_json["error"] ? $arr_json["error"] : "Not Found. Update Installer!");
                                        break;
                                    }
                                } else {
                                    break;
                                }
                            } else {
                                $strError = "Structure: " . (print_r($arr_json, true) ? print_r($arr_json, true) : "Not Found. Update Installer!");
                            }
                        } while(!strlen($strError) && $count_limit <= 10);
                    }
                    
                    if(!$strError) {
						///////////////////////////////
			            //Scrittura del file db.php (verificando l'installazione delle tabelle di base)
			            ///////////////////////////////
			            $filename_relative = $dirname_relative . "/db." . FF_PHP_EXT;
			            $filename = $disk_path . $filename_relative;
			            $config_content = '<?php
    define("FF_DATABASE_NAME", \'' . addslashes($database_name) . '\');
    define("FF_DATABASE_HOST", \'' . addslashes($database_host) . '\');
    define("FF_DATABASE_USER", \'' . addslashes($database_username) . '\');
    define("FF_DATABASE_PASSWORD", \'' . addslashes($database_password) . '\');

    define("DB_CHARACTER_SET", \'' . addslashes($character_set) . '\');
    define("DB_COLLATION", \'' . addslashes($collation) . '\');
    
    $config_check["db"] = true;
';
           
			            $tempHandle = @tmpfile();
			            @fwrite($tempHandle, $config_content);
			            @rewind($tempHandle);

			            if(@ftp_size($conn_id, $ftp_path . $filename_relative) >= 0) {
			                @ftp_delete($conn_id, $ftp_path . $filename_relative);
			            }
			            
			            $ret = @ftp_nb_fput($conn_id
			                                , $ftp_path . $filename_relative
			                                , $tempHandle
			                                , FTP_BINARY
			                                , FTP_AUTORESUME
			                            );
			            while ($ret == FTP_MOREDATA) {
			               // Do whatever you want
			               // Continue upload...
			               $ret = @ftp_nb_continue($conn_id);
			            }
			            if ($ret != FTP_FINISHED) {
			               $strError .= "Unable write file: " . $filename_relative . "<br />";
			            } else {
			                @ftp_chmod($conn_id, 0644, $ftp_path . $filename_relative);
			            }
            ///////////////////////////////                    
					}
				}

				if(!$strError) {
/****
* htaccess				
*/
					$htaccess_path = "/.htaccess";
					$htaccess_content = 'RewriteEngine on

# ----------------------------------------------
#                 Production

Options -Indexes

AddDefaultCharset UTF-8

# Apache mimetype configuration
    AddType text/cache-manifest .manifest
';

                    if($PHP_fastCGI || $total_php_value_need == $allow_rewite_php_value_on_htaccess) {
                        $htaccess_content .='
                                 
#php_value display_errors 1
#php_value register_globals 0
#php_value safe_mode 0

#php_value request_order "EGPCS" 

#php_value magic_quotes_gpc 0
#php_value magic_quotes_runtime 0
#php_value magic_quotes_sybase 0

#php_value max_execution_time 1000
#php_value max_input_time 1000
#php_value post_max_size 100M
#php_value upload_max_filesize 100M
';
                    } else {
                        $htaccess_content .=' 
                                
php_value display_errors 1
php_value register_globals 0
php_value safe_mode 0

php_value request_order "EGPCS"

php_value magic_quotes_gpc 0
php_value magic_quotes_runtime 0
php_value magic_quotes_sybase 0

php_value max_execution_time 1000
php_value max_input_time 1000
php_value post_max_size 100M
php_value upload_max_filesize 100M
';
    
                    }
                    $htaccess_content .=' 
ErrorDocument 404 /' . $site_path_rev . 'error-document
ErrorDocument 403 /' . $site_path_rev . 'error-document
';

					if(substr_count($st_domain_name, ".") > 1) {
						$primary_domain = str_replace('.', '\\.', $st_domain_name);
					} else {
						$primary_domain = 'www\.' . str_replace('.', '\\.', $st_domain_name);
 						$htaccess_content .='
RewriteCond %{HTTP_HOST} ^' . str_replace('.', '\\.', $st_domain_name) . '$
RewriteRule (.*)$ http://www.' . $st_domain_name . '/$1 [R=301,L,QSA]';
					}

					if($cdn_static) {
						$htaccess_content .='

RewriteCond   %{HTTP_HOST}      ^media\.' . str_replace('.', '\\.', $st_domain_name) . '$
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'cache/_thumb
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'error-document
RewriteRule   ^(.*)    /cache/_thumb/$0 [L,QSA]

RewriteCond %{HTTP_HOST}      ^media\.' . str_replace('.', '\\.', $st_domain_name) . '$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^cache/_thumb/(.*) http://static.' . $st_domain_name . '/$1 [L,R=301]

RewriteCond   %{HTTP_HOST}      ^static\.' . str_replace('.', '\\.', $st_domain_name) . '$
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'cm/showfiles\.php
RewriteRule   ^(.*)    /cm/showfiles\.php/$0 [L,QSA]
';
					} else {
						$htaccess_content .='
RewriteCond   %{HTTP_HOST}  	^' . $primary_domain . '$
RewriteCond   %{REQUEST_URI}  	^/' . $site_path_rev . 'media 
RewriteRule   ^media/(.*)    /' . $site_path_rev . 'cache/_thumb/$1 [L]';
					}
					
					$htaccess_content .='
RewriteCond   %{HTTP_HOST}  	^' . $primary_domain . '$
RewriteCond   %{REQUEST_URI}  	^/' . $site_path_rev . '/asset
RewriteRule   ^asset/(.*)    /' . $site_path_rev . 'cache/$1 [L]					

RewriteCond   %{HTTP_HOST}  	^' . $primary_domain . '$
RewriteCond   %{REQUEST_URI}	^/' . $site_path_rev . 'modules
RewriteCond   %{REQUEST_URI}	!^/' . $site_path_rev . 'modules/([^/]+)/themes(.+)
RewriteRule  ^modules/([^/]+)(.+)  /' . $site_path_rev . 'modules/$1/themes$2 [L,QSA]
					
RewriteCond   %{HTTP_HOST}  	^' . $primary_domain . '$
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'index\.php
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'cm/main\.php
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'cm/showfiles\.php
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'themes
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'applets/.*/?themes
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'modules/.*/?themes
RewriteCond   %{REQUEST_URI}    !^/' . $site_path_rev . 'uploads
RewriteCond   %{REQUEST_URI}    !^/' . $site_path_rev . 'cache
RewriteCond   %{REQUEST_URI}    !^/' . $site_path_rev . 'asset
RewriteCond   %{REQUEST_URI}    !^/' . $site_path_rev . 'media
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'robots\.txt
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'favicon
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'conf/gallery/install
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'conf/gallery/updater
RewriteCond   %{REQUEST_URI}  	!^/' . $site_path_rev . 'router\.php';
                    if($PHP_fastCGI) {
                        $htaccess_content .=' 
RewriteRule   ^(.*)    /' . $site_path_rev . 'index\.php?_ffq_=/$0 [L,QSA]';
                    } else {
                        $htaccess_content .=' 
RewriteRule   ^(.*)    /' . $site_path_rev . 'index\.php/$0 [L,QSA]';
                    }
					
					if($cdn_static) {
						$htaccess_content .= '

RewriteCond   %{HTTP_HOST}      !^(' . $primary_domain . '|static\.' . str_replace('.', '\\.', $st_domain_name) . '|media\.' . str_replace('.', '\\.', $st_domain_name) . ')$';
					} else {
						$htaccess_content .= '

RewriteCond   %{HTTP_HOST}      !^' . $primary_domain . '$';
					}
					$htaccess_content .= '
RewriteCond   %{REQUEST_URI}      !^/' . $site_path_rev . 'router\.php
RewriteRule   ^(.*)    /' . $site_path_rev . 'router\.php/$0 [L,QSA]					

RewriteCond %{HTTP_USER_AGENT} libwww-perl.* 
RewriteRule .* – [F,L]

<IfModule mod_deflate.c>
# force deflate for mangled headers 
# developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping/
    <IfModule mod_setenvif.c>
      <IfModule mod_headers.c>
      	SetEnvIf Authorization .+ Authorization=$0
        SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
        RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
      </IfModule>
    </IfModule>

  # Legacy versions of Apache
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/json
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE text/xml application/xml text/x-component
  AddOutputFilterByType DEFLATE application/xhtml+xml application/rss+xml application/atom+xml
  AddOutputFilterByType DEFLATE application/vnd.ms-fontobject application/x-font-ttf font/opentype
  AddOutputFilterByType DEFLATE image/svg+xml image/png image/jpeg image/gif image/x-icon
</IfModule>


<IfModule mod_expires.c>
  ExpiresActive on

# Perhaps better to whitelist expires rules? Perhaps.
  ExpiresDefault                          "access plus 1 month"

# cache.appcache needs re-requests in FF 3.6 (thx Remy ~Introducing HTML5)
  ExpiresByType text/cache-manifest       "access plus 0 seconds"



# Your document html
  ExpiresByType text/html                 "access plus 1 week"

# Data
  ExpiresByType text/xml                  "access plus 0 seconds"
  ExpiresByType application/xml           "access plus 0 seconds"
  ExpiresByType application/json          "access plus 0 seconds"

# RSS feed
  ExpiresByType application/rss+xml       "access plus 1 hour"

# Favicon (cannot be renamed)
  ExpiresByType image/x-icon              "access plus 1 month" 

# Media: images, video, audio
  ExpiresByType image/gif                 "access plus 1 month"
  ExpiresByType image/png                 "access plus 1 month"
  ExpiresByType image/jpg                 "access plus 1 month"
  ExpiresByType image/jpeg                "access plus 1 month"
  ExpiresByType video/ogg                 "access plus 1 month"
  ExpiresByType audio/ogg                 "access plus 1 month"
  ExpiresByType video/mp4                 "access plus 1 month"
  ExpiresByType video/webm                "access plus 1 month"

# HTC files  (css3pie)
  ExpiresByType text/x-component          "access plus 1 month"

# Webfonts
  ExpiresByType font/truetype             "access plus 1 month"
  ExpiresByType font/opentype             "access plus 1 month"
  ExpiresByType application/x-font-woff   "access plus 1 month"
  ExpiresByType image/svg+xml             "access plus 1 month"
  ExpiresByType application/vnd.ms-fontobject "access plus 1 month"

# CSS and JavaScript
  ExpiresByType text/css                  "access plus 1 year"
  ExpiresByType application/javascript    "access plus 1 year"
  ExpiresByType text/javascript           "access plus 1 year"

</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(js|css|xml|gz|svg)$"> 
        Header set Cache-Control: public
    </FilesMatch>
	# FileETag None is not enough for every server.
	#  Header unset ETag
	
	Header always append X-Frame-Options SAMEORIGIN
	
  <FilesMatch "\.(html|js|css|xml|gz|svg)$">
      Header append Vary: Accept-Encoding
  </FilesMatch>     
</IfModule>

# Since we`re sending far-future expires, we dont need ETags for static content.
# developer.yahoo.com/performance/rules.html#etags
FileETag None';
							$handle = @tmpfile();
							@fwrite($handle, $htaccess_content);
							@fseek($handle, 0);
							if(!@ftp_fput($conn_id, $real_ftp_path . $htaccess_path, $handle, FTP_ASCII)) {
								$strError = "Unable to write .htaccess";
							} else {
								if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $htaccess_path) === false) {
									@chmod(FF_DISK_PATH . $htaccess_path, 0644);
								}
							} 
				
					require_once($disk_path . "/library/gallery/common/convert_db.php");

					$strError .= convert_db($collation, $character_set, $database_name, $db_install);
				}				

				if(!$safe_mode) {
                    if(!$strError) {
                        // Tenta di sincronizzare la INDICI DB con il MASTER SITE
                         $count_limit = 0;
                        do {
                            $count_limit++;

							$json = file_post_contents(
								"http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/indexes.php?json=1&exec=1&nowarning=1&lo=" . urlencode("-1")
								, null
								, $auth_username
								, $auth_password
								, "GET"
								, "120"
							);

                            $arr_json = json_decode($json, true);
                            if(is_array($arr_json)) {
                                if(count($arr_json)) {
                                    if(array_key_exists("error", $arr_json)) {
                                        $strError = "Indexes: " . $arr_json["error"];
                                        break;
                                    }
                                } else {
                                    break;
                                }
                            } else {
                                $strError = "Indexes: " . print_r($arr_json, true);
                            }
                        } while(!strlen($strError) && $count_limit <= 10);
                    }
                }

                if(!$strError && $reset_database) {
                    $sSQL = "INSERT INTO `cm_mod_security_users` (`ID`, `ID_domains`, `level`, `expiration`, `status`, `active_sid`, `username`, `password`, `primary_gid`, `email`, `shippingaddress`, `shippingcap`, `shippingtown`, `shippingprovince`, `shippingstate`, `enable_ecommerce_data`, `enable_manage`, `ID_module_register`) VALUES
                                (2, 0, '0', '0000-00-00 00:00:00', '', '2cdffa246995e64e686fa557918f0c3d', 'guest', 'guest', 2, '0', '', '', '0', '', 0, '', '', 0),
                                (1, 0, '0', '0000-00-00 00:00:00', '1', '2cdffa246995e64e686fa557918f0c3d', " . $db_install->toSql($username) . ", " . $db_install->toSql($db_install->mysqlPassword($password)) . ", 1, '0', '', '', '0', '', 0, '', '1', 0)
                            ";
                    $db_install->execute($sSQL);
                    
                    $sSQL = "INSERT INTO `vgallery_nodes` (`ID`, `ID_vgallery`, `name`, `order`, `parent`, `ID_type`, `last_update`) VALUES
                                (1, 0, '', '0', '/', 0, 0)
                            ";
                    $db_install->execute($sSQL);
                    $sSQL = "UPDATE `vgallery_nodes` SET `ID` = '0' WHERE (`vgallery_nodes`.`ID` = 1)";
                    $db_install->execute($sSQL);
                }
                
                if(!$safe_mode) {
                    if(!$strError) {
                        // Tenta di sincronizzare la DATI BASE DB con il MASTER SITE
                         $count_limit = 0;
                        do {
                            $count_limit++;

							$json = file_post_contents(
								"http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/data.php/basic?json=1&exec=1&nowarning=1&lo=" . urlencode("-1")
								, null
								, $auth_username
								, $auth_password
								, "GET"
								, "120"
							);

                            $arr_json = json_decode($json, true);
                            if(is_array($arr_json)) {
                                if(count($arr_json)) {
                                    if(array_key_exists("error", $arr_json)) {
                                        $strError = "Data Basic: " . $arr_json["error"];
                                        break;
                                    }
                                } else {
                                    break;
                                }
                            } else {
                                $strError = "Data Basic: " . print_r($arr_json, true);
                            }
                        } while(!strlen($strError) && $count_limit <= 10);
                    }
                    if(!$strError) {
                        $count_limit = 0;
                        do {
                            $count_limit++;
                            // Tenta di sincronizzare i DATI INTERNATIONAL DB con il MASTER SITE
							$json = file_post_contents(
								"http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/data.php/international?json=1&exec=1&nowarning=1&lo=" . urlencode("-1")
								, null
								, $auth_username
								, $auth_password
								, "GET"
								, "120"
							);

                            $arr_json = json_decode($json, true);
                            if(is_array($arr_json)) {
                                if(count($arr_json)) {
                                    if(array_key_exists("error", $arr_json)) {
                                        $strError = "Data International: " . $arr_json["error"];
                                        break;
                                    }
                                } else {
                                    break;
                                }
                            } else {
                                $strError = "Data International: " . print_r($arr_json, true);
                            }
                        } while(!strlen($strError) && $count_limit <= 10);
                    }
                }
            }                
        }

		$arrPathNeed = array(
			"/applets" 							=> "readable"
			, "/cache" 							=> "writable"
			, "/cache/international" 			=> "writable"
			, "/cache/css" 						=> "writable"
			, "/cache/js" 						=> "writable"
			, "/contents" 						=> "readable"
			, $site_updir 						=> "writable"
			, "/themes/site/conf" 				=> "readable"
			, "/themes/site/contents" 			=> "readable"
			, "/themes/site/css" 				=> "readable"
			, "/themes/site/fonts" 				=> "readable"
			, "/themes/site/images" 			=> "readable"
			, "/themes/site/javascript" 		=> "readable"
			, "/themes/site/swf" 				=> "readable"
			, "/themes/site/xml" 				=> "readable"
		);
        
        if(is_array($arrPathNeed) && count($arrPathNeed)) {
            foreach($arrPathNeed AS $arrPathNeed_key => $arrPathNeed_value) {
                if(!file_exists($disk_path . $arrPathNeed_key)) {
                    @ftp_mkdir($conn_id, $ftp_path . $arrPathNeed_key);
                }
                if($arrPathNeed_value == "writable") {
                    @ftp_chmod($conn_id, 0777, $ftp_path . $arrPathNeed_key);
                } elseif($arrPathNeed_value == "readable") {
                    @ftp_chmod($conn_id, 0755, $ftp_path . $arrPathNeed_key);
                }
            }
        }

        $arrFileNeed = array("/themes/site/css/root.css" => "readable"
                        );

        if(is_array($arrFileNeed) && count($arrFileNeed)) {
            $handle = @tmpfile();
            foreach($arrFileNeed AS $arrFileNeed_key => $arrFileNeed_value) {
                if(!file_exists($disk_path . $arrFileNeed_key)) {
                    if(@ftp_fput($conn_id, $ftp_path . $arrFileNeed_key, $handle, FTP_ASCII)) {
                        if($arrFileNeed_value == "writable") {
                            if(@ftp_chmod($conn_id, 0777, $ftp_path . $arrFileNeed_key) === false) {
                                @chmod($disk_path . $arrFileNeed_key, 0777);
                            }
                        } elseif($arrFileNeed_value == "readable") {
                            if(@ftp_chmod($conn_id, 0644, $ftp_path . $arrFileNeed_key) === false) {
                                @chmod($disk_path . $arrFileNeed_key, 0644);
                            }
                        }
                    }
                }
            }
            @fclose($handle);
        }

		@ftp_close($conn_id);
        
        if(!$strError) {
			header("Location: " . $site_path . "/install?complete"); 
	
			//header("Location: " . $site_path . "/admin/configuration/updater"); 
            exit;
        }
    } 
    else 
    {    
    	if(is_file($st_disk_path . "/conf/gallery/config/admin.php"))
    		require_once($st_disk_path . "/conf/gallery/config/admin.php");
    	if(is_file($st_disk_path . "/conf/gallery/config/db.php"))
    		require_once($st_disk_path . "/conf/gallery/config/db.php");
    	if(is_file($st_disk_path . "/conf/gallery/config/other.php"))
    		require_once($st_disk_path . "/conf/gallery/config/other.php");
    	if(is_file($st_disk_path . "/conf/gallery/config/path.php"))
    		require_once($st_disk_path . "/conf/gallery/config/path.php");
    	if(is_file($st_disk_path . "/conf/gallery/config/session.php"))
    		require_once($st_disk_path . "/conf/gallery/config/session.php");
    	if(is_file($st_disk_path . "/conf/gallery/config/updater.php"))
    		require_once($st_disk_path . "/conf/gallery/config/updater.php");

                
        $disk_path          = (defined("FF_DISK_PATH") ? FF_DISK_PATH : "");
        $site_path          = (defined("FF_SITE_PATH") ? FF_SITE_PATH : "");
        $disk_updir         = (defined("DISK_UPDIR") ? DISK_UPDIR : "");
        $site_updir         = (defined("SITE_UPDIR") ? SITE_UPDIR : "");

        $session_save_path  = (defined("SESSION_SAVE_PATH") ? SESSION_SAVE_PATH : "");
        $session_name       = (defined("SESSION_NAME") ? SESSION_NAME : "");
        $appid              = (defined("APPID") ? APPID : "");
        $memory_limit       = (defined("MEMORY_LIMIT") ? MEMORY_LIMIT : "");
        $cdn_static       	= (defined("CDN_STATIC") ? CDN_STATIC : "");
        
        $database_name      = (defined("FF_DATABASE_NAME") ? FF_DATABASE_NAME : "");
        $database_host      = (defined("FF_DATABASE_HOST") ? FF_DATABASE_HOST : "");
        $database_username  = (defined("FF_DATABASE_USER") ? FF_DATABASE_USER : "");
        $database_password  = (defined("FF_DATABASE_PASSWORD") ? FF_DATABASE_PASSWORD : "");

        $character_set      = (defined("CHARACTER_SET") ? CHARACTER_SET : "");
        $collation       	= (defined("COLLATION") ? COLLATION : "");

        $smtp_host          = (defined("A_SMTP_HOST") ? A_SMTP_HOST : "");
        $smtp_auth          = (defined("SMTP_AUTH") ? SMTP_AUTH : "");
        $smtp_username      = (defined("A_SMTP_USER") ? A_SMTP_USER : "");
        $smtp_password      = (defined("A_SMTP_PASSWORD") ? A_SMTP_PASSWORD : "");
        $smtp_port      	= (defined("A_SMTP_PORT") ? A_SMTP_PORT : "");
        $smtp_secure      	= (defined("A_SMTP_SECURE") ? A_SMTP_SECURE : "");
        
        $email_address      = (defined("A_FROM_EMAIL") ? A_FROM_EMAIL : "");
        $email_name         = (defined("A_FROM_NAME") ? A_FROM_NAME : "");
        $cc_address         = (defined("CC_FROM_EMAIL") ? CC_FROM_EMAIL : "");
        $cc_name            = (defined("CC_FROM_NAME") ? CC_FROM_NAME : "");
        $bcc_address        = (defined("BCC_FROM_EMAIL") ? BCC_FROM_EMAIL : "");
        $bcc_name           = (defined("BCC_FROM_NAME") ? BCC_FROM_NAME : "");

        $site_title         = (defined("CM_LOCAL_APP_NAME") ? CM_LOCAL_APP_NAME : "");
        $language_default   = (defined("LANGUAGE_DEFAULT") ? LANGUAGE_DEFAULT : "");
        $language_default_id= (defined("LANGUAGE_DEFAULT_ID") ? LANGUAGE_DEFAULT_ID : "");

        $debug_mode   		= (defined("DEBUG_MODE") ? DEBUG_MODE : "");
        $debug_profiling   	= (defined("DEBUG_PROFILING") ? DEBUG_PROFILING : "");
        $debug_log   		= (defined("DEBUG_LOG") ? DEBUG_LOG : "");
        $trace_visitor 		= (defined("TRACE_VISITOR") ? TRACE_VISITOR : "");


        $username           = (defined("SUPERADMIN_USERNAME") ? SUPERADMIN_USERNAME : "");
        $password           = (defined("SUPERADMIN_PASSWORD") ? SUPERADMIN_PASSWORD : "");

        $master_site        = (defined("MASTER_SITE") ? MASTER_SITE : "");
        $production_site    = (defined("PRODUCTION_SITE") ? PRODUCTION_SITE : "");
        $development_site   = (defined("DEVELOPMENT_SITE") ? DEVELOPMENT_SITE : "");
        
        $ftp_username       = (defined("FTP_USERNAME") ? FTP_USERNAME : "");
        $ftp_password       = (defined("FTP_PASSWORD") ? FTP_PASSWORD : "");

        $auth_username       = (defined("AUTH_USERNAME") ? AUTH_USERNAME : "");
        $auth_password       = (defined("AUTH_PASSWORD") ? AUTH_PASSWORD : "");
        

        
        if(!strlen($disk_path))
            $disk_path = $st_disk_path;
        if(!strlen($site_path))
            $site_path = $st_site_path;
        if(!strlen($disk_updir))
            $disk_updir = $st_disk_updir;
        if(!strlen($site_updir))
            $site_updir = $st_site_updir;

        if(!strlen($session_save_path))
            $session_save_path = $st_session_save_path;
        if(!strlen($session_name))
            $session_name = $st_session_name;
        if(!strlen($appid))
            $appid = $st_appid;
        if(!strlen($memory_limit))
            $memory_limit = $st_memory_limit;
        if(!strlen($cdn_static))
            $cdn_static = $st_cdn_static;

        if(!strlen($database_name))
            $database_name = substr(str_replace(".", "_", $st_domain_name), 0 , 64);
        if(!strlen($database_host))
            $database_host = "localhost";
        if(!strlen($database_username))
            $database_username = substr(str_replace(".", "_", substr($st_domain_name, 0, strrpos($st_domain_name, "."))), 0 , 16);
        if(!strlen($database_password))
            $database_password = "";

        if(!strlen($character_set))
            $character_set = $st_character_set;
        if(!strlen($collation))
            $collation = $st_collation;
        
        if($_SERVER["PATH_INFO"] == "/setup" &&
			!defined("FF_DATABASE_HOST") &&
			!defined("FF_DATABASE_NAME") &&
			!defined("FF_DATABASE_PASSWORD")
		) {
            $reset_database = true;
        } else {
            $reset_database = false;
        }
            
        if(!strlen($smtp_host))
            $smtp_host = "localhost";
        if(!strlen($smtp_auth))
            $smtp_auth = false;
        if(!strlen($smtp_username))
            $smtp_username = "info@" . $st_domain_name;
        if(!strlen($smtp_password))
            $smtp_password = "";
        if(!strlen($smtp_port))
            $smtp_port = "25";
            
        if(!strlen($email_address))
            $email_address = "info@" . $st_domain_name;
        if(!strlen($email_name))
            $email_name = $st_domain_sig;
        if(!strlen($cc_address))
            $cc_address = "";
        if(!strlen($cc_name))
            $cc_name = "";
        if(!strlen($bcc_address))
            $bcc_address = "debug@" . $st_domain_name;
        if(!strlen($bcc_name))
            $bcc_name = "test[" . $st_domain_name . "]";
            
        if(!strlen($username))
            $username = "admin";
        if(!strlen($password))
            $password = "";
            
        if(!strlen($language_default)) {
            $language_default = "ITA";
            $language_default_id = $arrLang[$language_default];
		}
       // if(!strlen($trace_path))
       //     $trace_path 		= false;

        if(!strlen($debug_mode))
	        $debug_mode   		= false;
        if(!strlen($debug_profiling))
	        $debug_profiling   	= false;
        if(!strlen($debug_log))
	        $debug_log   		= false;
        if(!strlen($trace_visitor))
	        $trace_visitor 		= false;
    }
    


    if(!$strError && isset($_REQUEST["installer"]) || strpos($_SERVER["REQUEST_URI"], "/conf/gallery/install") !== false) {
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/path.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/path.php");
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/session.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/session.php");
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/db.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/db.php");
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/other.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/other.php");
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/admin.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/admin.php");
        if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php"))
            require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php");
        
        if(isset($config_check["path"]) && isset($config_check["session"]) && isset($config_check["db"]) && isset($config_check["other"]) && isset($config_check["admin"]) && isset($config_check["updater"])) {
			header("Location: " . $site_path . "/install?complete");
            exit;
        }

        if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" && basename($_SERVER["REQUEST_URI"]) != "setup" && !isset($_REQUEST["setup"]))
            $_REQUEST["init"] = true;
            
    	$check_file_config = false;
		if(file_exists(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php")) {
			if(defined("MASTER_SITE") && constant("MASTER_SITE")
				&& defined("FTP_USERNAME") && constant("FTP_USERNAME")
				&& defined("FTP_PASSWORD") && constant("FTP_PASSWORD")
			) {
				$master_site = MASTER_SITE;
				$production_site = (defined("PRODUCTION_SITE") ? PRODUCTION_SITE : "");
				$development_site = (defined("DEVELOPMENT_SITE") ? DEVELOPMENT_SITE : "");
				
				$ftp_username = FTP_USERNAME;
				$ftp_password = FTP_PASSWORD;
				
                $auth_username = (defined("AUTH_USERNAME") ? AUTH_USERNAME : ""); 
                $auth_password = (defined("AUTH_PASSWORD") ? AUTH_PASSWORD : "");

				$check_file_config = true;
			}
		} else {
			if(isset($_REQUEST["domain"]) && strlen($_REQUEST["domain"]) 
				&& isset($_REQUEST["name"]) && strlen($_REQUEST["name"]) 
				&& isset($_REQUEST["value"]) && strlen($_REQUEST["value"])
			) {
				$master_site = $_REQUEST["domain"];
				$production_site = "";
				$development_site = "";

				$ftp_username = $_REQUEST["name"];
				$ftp_password = $_REQUEST["value"];

				$auth_username = $_REQUEST["auth_name"];
				$auth_password = $_REQUEST["auth_value"];
				
				$check_file_config = true;
				//$_REQUEST["init"] = true;
			}
		}

		/***
		* htaccess install
		*/		
        if(!is_file($disk_path . "/.htaccess")) {
			$ftp_path = null;
			if(strlen($ftp_username)) {
				$conn_id = @ftp_connect("localhost");
				if($conn_id === false)
        			$conn_id = @ftp_connect("127.0.0.1");
				if($conn_id === false)
        			$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
				
				if($conn_id !== false) 
				{
					// login with username and password
					if(@ftp_login($conn_id, $ftp_username, $ftp_password)) {
						$local_path = $disk_path;
						$part_path = "";
						$real_ftp_path = NULL;
						
						foreach(explode("/", $local_path) AS $curr_path) {
							if(strlen($curr_path)) {
								$ftp_path = str_replace($part_path, "", $local_path);
								if(@ftp_chdir($conn_id, $ftp_path)) {
									$real_ftp_path = $ftp_path;
									break;
								} 

								$part_path .= "/" . $curr_path;
							}
						}

						if($real_ftp_path === null) {
							if(@ftp_chdir($conn_id, "/conf/gallery/install")) {
								$real_ftp_path = "/";
							}
						}
					}
					if($real_ftp_path === null) 
					{
						$strError .= "FTP Path unavailable<br />";
					} 
					else 
					{
						if(strlen($site_path))
        					$site_path_rev = ltrim($site_path, "/") . "/";
						else
							$site_path_rev = "";

						$htaccess_path = "/.htaccess";
						$htaccess_content = 'RewriteEngine on

	AddDefaultCharset UTF-8

	RewriteCond   %{REQUEST_URI}  	!^/?' . $site_path_rev . 'conf/gallery/install
	RewriteCond   %{REQUEST_URI}  	!^/?' . $site_path_rev . 'conf/gallery/updater
	RewriteRule   ^(.*)    ' . $site_path . '/conf/gallery/install/index\.php?installer [L,QSA]';

						$handle = @tmpfile();
						@fwrite($handle, $htaccess_content);
						@fseek($handle, 0);
						if(!@ftp_fput($conn_id, $real_ftp_path . $htaccess_path, $handle, FTP_ASCII)) {
							$strError = "Unable to write .htaccess";
						} else {
							if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $htaccess_path) === false) {
								@chmod(FF_DISK_PATH . $htaccess_path, 0644);
							}
						}
						@fclose($handle); 
					}
				} else {
					$strError = 'FTP Function are Disabled on Server. <a href="http://php.net/manual/en/ftp.installation.php" target="_blank">See Php Manual</a>';
				}
				@ftp_close($conn_id);
			}        
		}

		if($_REQUEST["init"]) 
		{
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
			echo '<html>';
			echo '<head>';
			echo '<title>Gallery Installation</title>';
			echo '<meta http-equiv="Cache-Control" content="no-cache">';
			echo '<meta http-equiv="Pragma" content="no-cache">';
			echo '<script src="' . $site_path . '/conf/gallery/install/jquery.min.js" type="text/javascript"></script>';
                        echo '<script src="' . $site_path . '/conf/gallery/install/base.js" type="text/javascript"></script>';
                        echo '<link href="' . $site_path . '/conf/gallery/install/base.css" rel="stylesheet" type="text/css" />';
			echo '</head>';
			echo '<body>';
			if(!$check_file_config) {
				echo '<div id="container-params">';
		        echo '<fieldset title="su" class="default" id="fieldset_updater_conf">';
		        echo '<legend class="subtitle">Updater configuration</legend>';
		 		echo '	  <div class="row" id="field_master_site">';
	            echo '    	  <label class="spanlabel">Master site</label>';
	            echo '		  <input id="master_site" class="input" type="text" name="MASTER_SITE" value="" />';
	            echo '	  </div>';
		        echo '    <div class="row" id="field_ftp_user">';
		        echo '        <label class="spanlabel">FTP username locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="ftp_user" class="input" type="text" name="FTP_USERNAME" value="" />';
		        echo '    </div>';
		        echo '    <div class="row" id="field_ftp_pwd">';
		        echo '        <label class="spanlabel">FTP password locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="ftp_pwd" class="password" type="password" name="FTP_PASSWORD" value="" />';
		        echo '    </div>';
		        echo '    <div class="row" id="field_ftp_conf_pwd">';
		        echo '        <label class="spanlabel">Confirm password locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="ftp_conf_pwd" class="password" type="password" name="FTP_CONFIRM_PASSWORD" value="" />';
		        echo '    </div>';
		        echo '    <div class="row" id="field_auth_user">';
		        echo '        <label class="spanlabel">AUTH Basic username locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="auth_user" class="input" type="text" name="AUTH_USERNAME" value="" />';
		        echo '    </div>';
		        echo '    <div class="row" id="field_auth_pwd">';
		        echo '        <label class="spanlabel">AUTH Basic password locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="auth_pwd" class="password" type="password" name="AUTH_PASSWORD" value="" />';
		        echo '    </div>';
		        echo '    <div class="row" id="field_auth_conf_pwd">';
		        echo '        <label class="spanlabel">Confirm password locale (' . $st_domain_name . ')</label>';
		        echo '        <input id="auth_conf_pwd" class="password" type="password" name="AUTH_CONFIRM_PASSWORD" value="" />';
		        echo '    </div>';
		        echo '</fieldset>';
				echo '<div class="actions">';
			    echo '	<a href="javascript:void(0);" class="link" id="install">Install</a>';
			    echo '</div>';
			    echo '</div>';
			} 
				
			echo '<h1 style="text-align:center;" id="check">Check Update File... Please Wait</h1>';
            if($strWarningError) {
                echo '<h2 style="text-align:center; font-size:12px;" id="warning">' . $strWarningError . '</h2>';
            }
			echo '<div class="error" style="display:none;">';
			echo '	<div class="actions">';
			echo '		<a class="detail link" href="javascript:jQuery(\'#error\').toggle();">Detail</a>';
			echo '		<a class="retry link" href="javascript:location.reload()">Retry</a>';
			echo '	</div>';
			echo '	<ul id="error" style="display:none;">';
			echo '	</ul>';
			echo '</div>';
			echo '<div class="content" style="display:block;">';
			//echo '<iframe border="0" src="http://' . $master_site . '" width="300" height="200" />';
			echo '<div class="sk-folding-cube">
				  <div class="sk-cube1 sk-cube"></div>
				  <div class="sk-cube2 sk-cube"></div>
				  <div class="sk-cube4 sk-cube"></div>
				  <div class="sk-cube3 sk-cube"></div>
				</div>';
			echo '<input type="hidden" id="site_path" value="' . $site_path . '"/>';
			echo '<input type="hidden" id="total" value="0" />';
			echo '<input type="hidden" id="result" value="0" />';
			echo '<div style="width:400px; position:absolute; bottom:0; left:50%; margin-left:-200px;" id="update-progress">';
			echo '	<label id="progress-label"></label>';
			echo '	<div style="width:400px; height: 32px;" class="pace pace-active"><div style="width:0px; transition:0.5s; height:32px;"  class="progress"></div><div class="pace-activity"></div></div>';
			echo '</div>';
			echo '</body>';
			echo '</html>';
			exit;
		}


  

	    if($check_file_config) {
			$allow_load_file = true;
		    $force_load = (isset($_REQUEST["force"])
	    					? $_REQUEST["force"]
	    					: false
	    				);

		    if($force_load) 
		    {
			} 
			elseif($_REQUEST["check"]) 
			{
				$total_file = 0;
				$allow_load_file = false;
				if(!ini_get("allow_url_fopen")) {
					die("allow_url_fopen must be enable. Load file Manually or enable this");
				}

				$ftp_path = null;
			    if(strlen($ftp_username)) {
					$conn_id = @ftp_connect("localhost");
			        if($conn_id === false)
        				$conn_id = @ftp_connect("127.0.0.1");
					if($conn_id === false)
        				$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
					
					if($conn_id !== false) 
					{
						// login with username and password
						if(@ftp_login($conn_id, $ftp_username, $ftp_password)) {
							$local_path = $disk_path;
							$part_path = "";
							$real_ftp_path = NULL;
							
							foreach(explode("/", $local_path) AS $curr_path) {
								if(strlen($curr_path)) {
									$ftp_path = str_replace($part_path, "", $local_path);
									if(@ftp_chdir($conn_id, $ftp_path)) {
									    $real_ftp_path = $ftp_path;
									    break;
									} 

									$part_path .= "/" . $curr_path;
								}
							}

							if($real_ftp_path === null) {
								if(@ftp_chdir($conn_id, "/conf/gallery/install")) {
									$real_ftp_path = "/";
								}
							}
						}
						if($real_ftp_path === null) 
						{
							$strError .= "FTP Path unavailable<br />";
						} 
						else 
						{
//robots.txt                            
                            $robots_path = "/robots.txt"; 
                            $robots_content = 'User-agent: *
Allow: /
Disallow: /admin/
Disallow: /restricted/
Disallow: /manage/

Sitemap: http://' . $st_domain_name . $site_path . '/sitemap.xml
';
                            $handle = @tmpfile();
                            @fwrite($handle, $robots_content);
                            @fseek($handle, 0);
                            if(!@ftp_fput($conn_id, $real_ftp_path . $robots_path, $handle, FTP_ASCII)) {
                                $strError = "Unable to write robots.txt";
                            } else {
                                if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $robots_path) === false) {
                                    @chmod(FF_DISK_PATH . $robots_path, 0644);
                                }
                            } 

							//htaccess no script
                            $htaccess_noscript_file = ".htaccess"; 
                            $htaccess_noscript_content = '<IfModule mod_php4.c>
  php_value engine off
</IfModule>
<IfModule mod_php5.c>
  php_value engine off
</IfModule>

Options -Indexes
Options -ExecCGI
AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi';

							$arrPathNeed = array(
								"/applets" 							=> "readable"
								, "/cache"							=> "writable"
								, "/contents" 						=> "readable"
								, "/themes" 						=> "readable"
								, "/themes/site" 					=> "readable"
								, "/themes/site/conf" 				=> "readable"
								, "/themes/site/contents" 			=> "readable"
								, "/themes/site/css" 				=> "readable"
								, "/themes/site/fonts" 				=> "readable"
								, "/themes/site/images" 			=> "readable"
								, "/themes/site/javascript" 		=> "readable"
								, "/themes/site/swf" 				=> "readable"
								, "/themes/site/xml" 				=> "readable"
								, "/uploads" 						=> "writable"
							);
		                    
		                    if(is_array($arrPathNeed) && count($arrPathNeed)) {
		                        foreach($arrPathNeed AS $arrPathNeed_key => $arrPathNeed_value) {
		                            if(!file_exists($disk_path . $arrPathNeed_key)) {
		                                @ftp_mkdir($conn_id, $real_ftp_path . $arrPathNeed_key);
		                            }
		                            if($arrPathNeed_value == "writable") {
		                                @ftp_chmod($conn_id, 0777, $real_ftp_path . $arrPathNeed_key);

			                            $handle = @tmpfile();
			                            @fwrite($handle, $htaccess_noscript_content);
			                            @fseek($handle, 0);
										// Write .htaccess no script		                                
			                            if(!@ftp_fput($conn_id, $real_ftp_path . $arrPathNeed_key . "/" . $htaccess_noscript_file, $handle, FTP_ASCII)) {
			                                $strError = "Unable to write htaccess no script " . $arrPathNeed_key;
			                            } else {
			                                if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $arrPathNeed_key . "/" . $htaccess_noscript_file) === false) {
			                                    @chmod(FF_DISK_PATH . $arrPathNeed_key . "/" . $htaccess_noscript_file, 0644);
			                                }
			                            } 
		                            } elseif($arrPathNeed_value == "readable") {
		                                @ftp_chmod($conn_id, 0755, $real_ftp_path . $arrPathNeed_key);
		                            }
		                        }
		                    }

							// Write .htaccess no script in /themes/site
                            $handle = @tmpfile();
                            @fwrite($handle, $htaccess_noscript_content);
                            @fseek($handle, 0);
			                if(!@ftp_fput($conn_id, $real_ftp_path . "/themes/site/" . $htaccess_noscript_file, $handle, FTP_ASCII)) {
			                    $strError = "Unable to write htaccess no script site";
			                } else {
			                    if(@ftp_chmod($conn_id, 0644, $real_ftp_path . "/themes/site/" . $htaccess_noscript_file) === false) {
			                        @chmod(FF_DISK_PATH . "/themes/site/" . $htaccess_noscript_file, 0644);
			                    }
			                } 
							@fclose($handle);
		                    
		                    $arrFileNeed = array("/themes/site/css/main.css" => "readable"
		                                    );

		                    if(is_array($arrFileNeed) && count($arrFileNeed)) {
		                        $handle = @tmpfile();
		                        foreach($arrFileNeed AS $arrFileNeed_key => $arrFileNeed_value) {
		                            if(!file_exists($disk_path . $arrFileNeed_key)) {
		                                if(@ftp_fput($conn_id, $real_ftp_path . $arrFileNeed_key, $handle, FTP_ASCII)) {
		                                    if($arrFileNeed_value == "writable") {
		                                        if(@ftp_chmod($conn_id, 0777, $real_ftp_path . $arrFileNeed_key) === false) {
		                                            @chmod($disk_path . $arrFileNeed_key, 0777);
		                                        }
		                                    } elseif($arrFileNeed_value == "readable") {
		                                        if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $arrFileNeed_key) === false) {
		                                            @chmod($disk_path . $arrFileNeed_key, 0644);
		                                        }
		                                    }
		                                }
		                            }
		                        }
		                        @fclose($handle);
		                    }							
						}
					} 
                                        
					@ftp_close($conn_id);
				}
				if($strError)
					die($strError);

				$restore_file = file_post_contents(
					(strlen($auth_username) && strlen($auth_password)
						? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php/updater?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&abpuser=" . urlencode($auth_username) . "&abppw=" . urlencode($auth_password) . "&json=1"
						: "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php/updater?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&json=1"
					)
					, null
					, $auth_username
					, $auth_password
					, "GET"
				);

				if($restore_file === false) {
					die("Missing File... Please reload all Updater file");
				} else {
					if(strlen($restore_file))
				        $arr_restore_file = json_decode($restore_file, true);

				    if(is_array($arr_restore_file)) {
				        if(count($arr_restore_file["record"])) {
                            if(array_key_exists("error", $arr_restore_file))
                                die("Error: " . $arr_restore_file["error"]);

				        	$total_file = $total_file + count($arr_restore_file["record"]);
							$allow_load_file = true;
						} else {
							$allow_load_file = false;
						}
					} else {
						die("Error: " . $restore_file);
					}
				}
				$restore_file = file_post_contents(
					(strlen($auth_username) && strlen($auth_password)
						? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path. "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&abpuser=" . urlencode($auth_username) . "&abppw=" . urlencode($auth_password) . "&json=1"
						: "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path. "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&json=1"
					)
					, null
					, $auth_username
					, $auth_password
					, "GET"
				);

				if($restore_file === false) {
					die("Missing File... Please reload all CMS file");
				} else {
					if(strlen($restore_file))
					    $arr_restore_file = json_decode($restore_file, true);

					if(is_array($arr_restore_file)) {
					    if(count($arr_restore_file["record"])) {
                            if(array_key_exists("error", $arr_restore_file))
                                die("Error: " . $arr_restore_file["error"]);

					        $total_file = $total_file + count($arr_restore_file["record"]);
							$allow_load_file = true;
						} else {
							$allow_load_file = false;
						}
					} else {
						die("Error: " . $restore_file);
					}
				}
				if($_REQUEST["check"]) {
					if($total_file > 0) {
						echo json_encode(array("total" => $total_file));
					} else {
						echo json_encode(array());
					}
					exit;
				}
			}
		}

	    if($allow_load_file) {
			//$check_files = false;
			//do {
				$restore_file = file_post_contents(
					(strlen($auth_username) && strlen($auth_password)
						? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php/updater?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&abpuser=" . urlencode($auth_username) . "&abppw=" . urlencode($auth_password) . "&json=1&exec=1"
						: "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php/updater?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&json=1&exec=1"
					)
					, null
					, $auth_username
					, $auth_password
					, "GET"
					, "120"
				);

				if(!$restore_file) {
					die("Critical Error... Please reload all Updater file");
				} else {
					if(strlen($restore_file))
                		$arr_restore_file = json_decode($restore_file, true);

            		if(is_array($arr_restore_file)) {
						if(array_key_exists("error", $arr_restore_file)) {
                            die("Error: " . $arr_restore_file["error"]);
                        } elseif(array_key_exists("result", $arr_restore_file)) {
            				echo json_encode(array("result" => $arr_restore_file["result"]));
            				exit;
						} elseif(count($arr_restore_file["record"])) {
            				    //header("Location: " . (strpos($_SERVER["REQUEST_URI"], "?") === false ? $_SERVER["REQUEST_URI"] : substr($_SERVER["REQUEST_URI"], 0,  strpos($_SERVER["REQUEST_URI"], "?"))) . "?force=" . filemtime(__FILE__));
            				echo json_encode(array("result" => count($arr_restore_file["record"])));
            				exit;
	//					} else {
	//						$check_files = false;
						}            		
					} else {
						die("Error: " . $restore_file);
					}
				}
			//} while($check_files);
			
			//$check_files = false;
			//do {
				$restore_file = file_post_contents(
					(strlen($auth_username) && strlen($auth_password)
						? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&abpuser=" . urlencode($auth_username) . "&abppw=" . urlencode($auth_password) . "&json=1&exec=1"
						: "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&json=1&exec=1"
					)
					, null
					, $auth_username
					, $auth_password
					, "GET"
					, "120"
				);
				if($restore_file !== false && !strlen($restore_file)) {
					$restore_file = file_post_contents(
						(strlen($auth_username) && strlen($auth_password)
							? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&abpuser=" . urlencode($auth_username) . "&abppw=" . urlencode($auth_password) . "&json=1"
							: "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $st_host_name . $site_path . "/conf/gallery/updater/files.php?mc=" . urlencode($master_site) . "&apuser=" . urlencode($ftp_username) . "&appw=" . urlencode($ftp_password) . "&json=1"
						)
						, null
						, $auth_username
						, $auth_password
						, "GET"
						, "120"
					);
				}

				if(!$restore_file) {
					echo "Transfert Data is too low";
					//header("Location: " . $_SERVER["REQUEST_URI"]);
					exit;
					//die("Critical Error... Please reload all CMS file");
				} else {
					if(strlen($restore_file))
                		$arr_restore_file = json_decode($restore_file, true);

            		if(is_array($arr_restore_file)) {
                        if(array_key_exists("error", $arr_restore_file)) {
                            die("Error: " . $arr_restore_file["error"]);
                        } elseif(array_key_exists("result", $arr_restore_file)) {
            				echo json_encode(array("result" => $arr_restore_file["result"]));
            				exit;
						} elseif(count($arr_restore_file["record"])) {
            				echo json_encode(array("result" => count($arr_restore_file["record"])));
            				exit;
						} else {
            				//echo json_encode(array());
            				//exit;
						}
            			
					} else {
						die("Error: " . $restore_file);
					}
				}
			//} while($check_files);
		}

        if(check_primary_class($disk_path, $site_path, $strCriticalError))
            die($strError . "... Please Reload File Manually");
            
		if($_REQUEST["json"]) {
			echo json_encode(array());
			exit;
		}
	}

    $tpl = ffTemplate::factory($disk_path . "/conf/gallery/install");
    $tpl->load_file("install.html", "Main");

    $tpl->set_var("disk_path", $disk_path);
    $tpl->set_var("site_path", $site_path);
    $tpl->set_var("disk_updir", $disk_updir);
    $tpl->set_var("site_updir", $site_updir);
    if(function_exists("cm_getClassByFrameworkCss")) {
		$tpl->set_var("row_class", cm_getClassByFrameworkCss("", "row-default"));
		$tpl->set_var("button_class", cm_getClassByFrameworkCss("", "link"));
	}
    $tpl->set_var("session_save_path", $session_save_path);
    $tpl->set_var("session_name", $session_name);
    $tpl->set_var("appid", $appid);
    $tpl->set_var("memory_limit", $memory_limit);
    if($cdn_static)
        $tpl->set_var("cdn_static", "checked=\"checked\"");
    else
        $tpl->set_var("cdn_static", "");	

    $tpl->set_var("database_name", $database_name);
    $tpl->set_var("database_host", $database_host);
    $tpl->set_var("database_user", $database_username);
    $tpl->set_var("database_password", $database_password);
    if($reset_database)
        $tpl->set_var("reset_database_check", "checked=\"checked\"");
    else
        $tpl->set_var("reset_database_check", "");

    $tpl->set_var("character_set", $character_set);
    $tpl->set_var("collation", $collation);    
    
    $tpl->set_var("smtp_host", $smtp_host);
    
    if($smtp_auth)
        $tpl->set_var("smtp_auth_check", "checked=\"checked\"");
    else
        $tpl->set_var("smtp_auth_check", "");
        
    $tpl->set_var("smtp_username", $smtp_username);
    $tpl->set_var("smtp_password", $smtp_password);
	$tpl->set_var("smtp_port", $smtp_port);
	$tpl->set_var("smtp_secure", $smtp_secure);
	
    $tpl->set_var("email_address", $email_address);
    $tpl->set_var("email_name", $email_name);

    $tpl->set_var("cc_address", $cc_address);
    $tpl->set_var("cc_name", $cc_name);
    
    $tpl->set_var("bcc_address", $bcc_address);
    $tpl->set_var("bcc_name", $bcc_name);

    $tpl->set_var("site_title", $site_title);
    $tpl->set_var("language_default_" . strtolower($language_default), "selected=\"selected\"");

    /*if($trace_path)
        $tpl->set_var("trace_path", "checked=\"checked\"");
    else
        $tpl->set_var("trace_path", "");	*/

    if($debug_mode)
        $tpl->set_var("debug_mode", "checked=\"checked\"");
    else
        $tpl->set_var("debug_mode", "");	

    if($debug_profiling)
        $tpl->set_var("debug_profiling", "checked=\"checked\"");
    else
        $tpl->set_var("debug_profiling", "");	

    if($debug_log)
        $tpl->set_var("debug_log", "checked=\"checked\"");
    else
        $tpl->set_var("debug_log", "");	

    if($trace_visitor)
        $tpl->set_var("trace_visitor", "checked=\"checked\"");
    else
        $tpl->set_var("trace_visitor", "");	

    $tpl->set_var("username", $username);
    $tpl->set_var("password", $password);

    $tpl->set_var("master_site", $master_site);
    $tpl->set_var("production_site", $production_site);
    $tpl->set_var("development_site", $development_site);
    
    $tpl->set_var("ftp_username", $ftp_username);
    $tpl->set_var("ftp_password", $ftp_password);

    $tpl->set_var("auth_username", $auth_username);
    $tpl->set_var("auth_password", $auth_password);
    
    if($strError) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    } else {
        $tpl->set_var("SezError", "");
    }

    if($_SERVER["PATH_INFO"] == VG_SITE_ADMININSTALL) {
        if (!AREA_INSTALLATION_SHOW_MODIFY) {
            ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
        }

        require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);
        
        $tpl->set_var("SezHeader", "");
        $tpl->set_var("SezFooter", "");

        $cm = cm::getInstance();
//        $cm->oPage->form_id = "frmInstall";
//        $cm->oPage->form_name = "frmInstall";
        $cm->oPage->form_method = "POST";

		if(is_file($disk_path . "/conf/gallery/install/install.js"))
        	$cm->oPage->tplAddJs("install"
                , array(
                    "embed" => file_get_contents($disk_path . "/conf/gallery/install/install.js")
            ));
		if(is_file($disk_path . "/conf/gallery/install/install.css"))
        	$cm->oPage->tplAddCss("install"
                , array(
                    "embed" => file_get_contents($disk_path . "/conf/gallery/install/install.css")
            ));

        
        if(1) {
        	$cm->oPage->addContent(null, true, "rel"); 
        	$cm->oPage->addContent($tpl->rpparse("Main", false), "rel", null, array("title" => ffTemplate::_get_word_by_code("install_title"))); 
        	
        	//$cm->oPage->addContent("sdd", "rel", null, array("title" => ffTemplate::_get_word_by_code("ecommerce_title"))); 

		    $tpl_diagnostic = ffTemplate::factory($disk_path . "/themes/gallery/contents");
		    $tpl_diagnostic->load_file("diagnostic.html", "Main");

		    //da estendere introducendo una tabella con i valori impostati ==> valori reali sul server
		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("PHP_EXT_MEMCACHE"));
		    $tpl_diagnostic->set_var("ext_name", "PHP EXT MEMCACHE");
		    $tpl_diagnostic->set_var("ext_status", (defined("PHP_EXT_MEMCACHE")
		    											? PHP_EXT_MEMCACHE ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);
		    
		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("PHP_EXT_APC"));
		    $tpl_diagnostic->set_var("ext_name", "PHP EXT APC");
		    $tpl_diagnostic->set_var("ext_status", (defined("PHP_EXT_APC")
		    											? PHP_EXT_APC ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);

		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("PHP_EXT_JSON"));
		    $tpl_diagnostic->set_var("ext_name", "PHP EXT JSON");
		    $tpl_diagnostic->set_var("ext_status", (defined("PHP_EXT_JSON")
		    											? PHP_EXT_JSON ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);

		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("PHP_EXT_GD"));
		    $tpl_diagnostic->set_var("ext_name", "PHP EXT GD");
		    $tpl_diagnostic->set_var("ext_status", (defined("PHP_EXT_GD")
		    											? PHP_EXT_GD ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);

		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("APACHE_MODULE_EXPIRES"));
		    $tpl_diagnostic->set_var("ext_name", "APACHE MODULE EXPIRES");
		    $tpl_diagnostic->set_var("ext_status", (defined("APACHE_MODULE_EXPIRES")
		    											? APACHE_MODULE_EXPIRES ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);
			
		    $tpl_diagnostic->set_var("ext_class", ffCommon_url_rewrite("MYSQLI_EXTENSIONS"));
		    $tpl_diagnostic->set_var("ext_name", "MySQLnd active driver");
		    $tpl_diagnostic->set_var("ext_status", (defined("MYSQLI_EXTENSIONS")
		    											? MYSQLI_EXTENSIONS ? ffTemplate::_get_word_by_code("on") : ffTemplate::_get_word_by_code("off")
		    											: ffTemplate::_get_word_by_code("na")		
		    									)
		    								);
		    $tpl_diagnostic->parse("SezExtension", true);
		    
			if(!defined("PHP_EXT_MEMCACHE")
				|| !defined("PHP_EXT_APC")
				|| !defined("APACHE_MODULE_EXPIRES")
				|| !defined("PHP_EXT_JSON")
				|| !defined("PHP_EXT_GD")
			) {
				mod_notifier_add_message_to_queue(ffTemplate::_get_word_by_code("server_extention_not_set"));
			}
        	
        	$cm->oPage->addContent($tpl_diagnostic->rpparse("Main", false), "rel", null, array("title" => ffTemplate::_get_word_by_code("server_diagnostic_title"))); 
		} else {
			$cm->oPage->addContent($tpl->rpparse("Main", false), null, "Install");	
		}
        
        
    } elseif(strpos($_SERVER["REQUEST_URI"], $site_path . "/setup") === 0 || isset($_REQUEST["setup"])) {
		if(is_file($disk_path . "/conf/gallery/install/install.js"))
        	$tpl->set_var("script", file_get_contents($disk_path . "/conf/gallery/install/install.js"));
		if(is_file($disk_path . "/conf/gallery/install/install.css"))
        	$tpl->set_var("style", file_get_contents($disk_path . "/conf/gallery/install/install.css"));
        
        $tpl->parse("SezHeader", false);
        $tpl->parse("SezFooter", false);
        $tpl->pparse("Main", false);
        exit;
    } else {
        if($strCriticalError) {
            die("Unable to run installation process");
        } else {
            header("Location: " . $site_path . "/conf/gallery/install?setup");
            exit;
        }
    }