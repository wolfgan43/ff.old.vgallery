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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
	require_once(__DIR__ . "/common.php");

	$server_report = installer_server_requirements(true);

	$domain = installer_get_domain();
	$path = installer_get_path();
    $master_token = time();

	/*
	 * CHECK INTEGRITY
	 */
	if(!is_file($path["disk_path"] . "/themes/site/conf/config.remote.php")) {
		if(is_file($path["disk_path"] . "/themes/site/conf/config.updater.php"))
			require_once($path["disk_path"] . "/themes/site/conf/config.updater.php");

		$install_status = "base";
		if(!(defined("FTP_USERNAME")
			&& defined("FTP_PASSWORD")
			&& defined("FTP_PATH")
			&& defined("AUTH_USERNAME")
			&& defined("AUTH_PASSWORD")
			&& defined("MASTER_SITE")
		)) {
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"
				&& $_REQUEST["name"]
				&& $_REQUEST["value"]
				&& isset($_REQUEST["auth_name"])
				&& isset($_REQUEST["auth_value"])
				&& $_REQUEST["domain"]
			) {
				$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params) {
					///////////////////////////////
					//Scrittura del file config.updater.php installer
					///////////////////////////////
					$strError = installer_write("/themes/site/conf/config.updater.php"
						, "config-updater.tpl"
						, array(
							"conn_id" 								=> $conn_id
							, "path" 								=> $ftp_path
						)
						, array(
							"[FTP_USERNAME]"						=> $_REQUEST["name"]
							, "[FTP_PASSWORD]"						=> $_REQUEST["value"]
							, "[FTP_PATH]"							=> ($ftp_path ? $ftp_path : "/")
							, "[AUTH_USERNAME]"						=> $_REQUEST["auth_name"]
							, "[AUTH_PASSWORD]"						=> $_REQUEST["auth_value"]
							, "[MASTER_SITE]"						=> $_REQUEST["domain"]
							, "[MASTER_TOKEN]"						=> (defined("MASTER_TOKEN") && MASTER_TOKEN ? MASTER_TOKEN : "")
						)
					);

					return $strError;
				}, null, $_REQUEST["name"], $_REQUEST["value"]);

				if(!$ftp_report["error"] && is_file($path["disk_path"] . "/themes/site/conf/config.updater.php")) {
					$install_status = "updater";
					require_once($path["disk_path"] . "/themes/site/conf/config.updater.php");
				} else {
					$server_report["error"]["critical"] .= $ftp_report["error"];
				}
				echo json_encode($server_report["error"]["critical"]);
				exit;
			}
		} else {
			$ftp_report = installler_ftp_connection();
			if(!$ftp_report["error"]) {
				$install_status = "updater";
			} else {
				$server_report["error"]["critical"] .= $ftp_report["error"];
			}
		}

		/*
		 * Parse Template Basic Configuration
		 */
		if($install_status == "base") {
			if($_SERVER["PATH_INFO"] == "/setup" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
				header("Location: " . dirname($_SERVER["REQUEST_URI"]));
				exit;
			}
			echo installer_tpl_parse("install-base.html", array(
				"[CRITICAL]"								=> $server_report["error"]["critical"]
				, "[WARNING]"								=> $server_report["error"]["warning"]
			));
			exit;
		}


		/***
		 * htaccess install And fs Need
		 */
		if(!is_file($path["disk_path"] . "/.htaccess")) {
			$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params)
			{
				///////////////////////////////
				//Scrittura del file .htaccess installer
				///////////////////////////////
				$strError = installer_write("/.htaccess"
					, "install-htaccess.tpl"
					, array(
						"conn_id" 									=> $conn_id
						, "path" 									=> $ftp_path
					)
				);

				return $strError;
			});
			if($ftp_report["error"])
				$server_report["error"]["critical"] .= $ftp_report["error"];

			$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params)
			{
				///////////////////////////////
				//Scrittura FS Need
				///////////////////////////////
				$strError = installer_fs_need(array(
						"conn_id" 									=> $conn_id
						, "path" 									=> $ftp_path
					)
				);

				return $strError;
			});
			if($ftp_report["error"])
				$server_report["error"]["critical"] .= $ftp_report["error"];

			if(!$server_report["error"]["critical"] && is_file($path["disk_path"] . "/index.php"))
				@unlink($path["disk_path"] . "/index.php");
		}
	}

	/**
	 * IF Primary Page Check file Updater and Check Files. If not Redirect Progress-bar
	 */
	if(1) {
		/***
		 * Updater file Updater
		 */
		$res = installer_updater("updater", true);
		if($res && !is_array($res)) {
			echo $res;
			exit;
		}



		/**
		 * Check Updater files
		 */
		$res = installer_updater("file");
		if(!$res) {
			$install_status = "setup";
		} else {
			$install_status = "updater";
			if($_SERVER["PATH_INFO"] == "/setup" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
				header("Location: " . dirname($_SERVER["REQUEST_URI"]));
				exit;
			}

			if(!is_array($res))
				$server_report["error"]["critical"] .= $res;
		}

		/*
		 * Parse Template Progress Bar
		 */
		if($install_status == "updater") {
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
				$res = installer_updater("file", true);

				if(is_array($res)) {
					echo json_encode($res);
				} else {
					echo $res;
				}
				exit;
			}

			echo installer_tpl_parse("install-base-progress.html", array(
				"[CRITICAL]"								=> $server_report["error"]["critical"]
				, "[WARNING]"								=> $server_report["error"]["warning"]
				, "[MASTER_SITE]"							=> MASTER_SITE
				, "[TOTAL]"									=> $res["result"]
			));
			exit;
		}

		if(0 && $install_status == "setup") {
			if($_SERVER["PATH_INFO"] != "/setup" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
				header("Location: " . $path["site_path"] . "/setup");
				exit;
			}
		}
	}

	$server_report["error"]["critical"] .= installer_check_FF_class();
	if($server_report["error"]["critical"]) {
		echo $server_report["error"]["critical"];
		exit;
	}

	/**
	 * SETUP
	 */
	if(is_file($path["disk_path"] . "/themes/site/conf/config.remote.php")) {
		if(is_file($path["disk_path"] . "/themes/site/conf/config.updater.php"))
			@unlink($path["disk_path"] . "/themes/site/conf/config.updater.php");

		require_once($path["disk_path"] . "/themes/site/conf/config.remote.php");
	}


	/**
	 *  INSTALLATION: COMPLETE
	 */
	//if(defined("BLOCK_INSTALL") || isset($_REQUEST["complete"])) {
	if(isset($_REQUEST["complete"])
		&& defined("FF_DISK_PATH")
		&& defined("SESSION_NAME")
		&& defined("FF_DATABASE_NAME")
		&& defined("SUPERADMIN_PASSWORD")
		&& defined("MASTER_SITE")
		&& defined("FTP_PASSWORD")
		&& defined("APPID")
		&& defined("LANGUAGE_DEFAULT")
		&& defined("ADMIN_THEME")
		&& defined("FRAMEWORK_CSS")
		&& defined("FONT_ICON")
	) {
		echo installer_tpl_parse("install-complete.html");
		exit;
	}


	/**
	 *  Define SESSION Vars
	 */
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

	/**
	 *  Define OTHER Vars
	 */

	$st_appid 					= (defined("MASTER_TOKEN") && MASTER_TOKEN ? MASTER_TOKEN : $master_token) . "-" ;
	$st_session_name 			= str_replace(array("a", "e", "i", "o", "u", "-"), "", strtolower($domain["primary"]));
	//$st_session_name 			= "PHPSESS_" . substr($st_appid, 1, 8);
	$st_memory_limit 			= "96M";
	$st_service_time_limit		= false;
	$st_session_permanent 		= true;
	$st_cdn_static 				= false;
	$st_cdn_services 			= false;

	$st_character_set 			= "utf8";
	$st_collation 				= "utf8_unicode_ci";

	$st_framework_css 			= "bootstrap-fluid";
	$st_font_icon 				= "fontawesome";

	$st_timezone 				= "Europe/Rome";
	$st_security_shield 		= false;
	$st_admin_theme 			= "admin";

	$arrLang 					= array(
									"ENG" => 2
									, "ITA" => 1
									, "ESP" => 4
									, "FRA" => 3
									, "DEU" => 5
								);

	if(function_exists("get_loaded_extensions"))
		$php_ext_loaded 		= get_loaded_extensions();
	else
		$php_ext_loaded 		= array();

	if(function_exists("apache_get_modules"))
		$apache_module_loaded 	= apache_get_modules();
	else
		$apache_module_loaded 	= array();

	/**
	 *  INSTALLATION: INSTALL (config, .htaccess, other)
	 */
	$frmAction = $_REQUEST["frmAction"];
    if($frmAction == "install")
    {
        $disk_path              = $_REQUEST["FF_DISK_PATH"];
        $site_path              = $_REQUEST["FF_SITE_PATH"];

        $disk_updir             = $_REQUEST["DISK_UPDIR"];
        $site_updir             = $_REQUEST["SITE_UPDIR"];

        $session_save_path      = $_REQUEST["SESSION_SAVE_PATH"];
        $session_name           = $_REQUEST["SESSION_NAME"];
		$session_permanent      = (isset($_REQUEST["MOD_SECURITY_SESSION_PERMANENT"])
									? $_REQUEST["MOD_SECURITY_SESSION_PERMANENT"]
									: null
								);

		$character_set          = $_REQUEST["CHARACTER_SET"];
		$collation		        = $_REQUEST["COLLATION"];

        $database_host          = $_REQUEST["FF_DATABASE_HOST"];
		$database_name          = $_REQUEST["FF_DATABASE_NAME"];
        $database_username      = $_REQUEST["FF_DATABASE_USER"];
        $database_password      = $_REQUEST["FF_DATABASE_PASSWORD"];
        $database_conf_password = $_REQUEST["FF_DATABASE_CONF_PASSWORD"];

        //mongo
		$mongo_database_host          			= $_REQUEST["MONGO_DATABASE_HOST"];
		$mongo_database_name          			= $_REQUEST["MONGO_DATABASE_NAME"];
		$mongo_database_username      			= $_REQUEST["MONGO_DATABASE_USER"];
		$mongo_database_password      			= $_REQUEST["MONGO_DATABASE_PASSWORD"];
		$mongo_database_conf_password 			= $_REQUEST["MONGO_DATABASE_CONF_PASSWORD"];

		//trace
		$trace_table_name          				= $_REQUEST["TRACE_TABLE_NAME"];
		$trace_onesignal_app_id          		= $_REQUEST["TRACE_ONESIGNAL_APP_ID"];
		$trace_onesignal_api_key      			= $_REQUEST["TRACE_ONESIGNAL_API_KEY"];

		$trace_database_host          			= $_REQUEST["TRACE_DATABASE_HOST"];
		$trace_database_name          			= $_REQUEST["TRACE_DATABASE_NAME"];
		$trace_database_username      			= $_REQUEST["TRACE_DATABASE_USER"];
		$trace_database_password      			= $_REQUEST["TRACE_DATABASE_PASSWORD"];
		$trace_database_conf_password 			= $_REQUEST["TRACE_DATABASE_CONF_PASSWORD"];

		$trace_mongo_database_host          	= $_REQUEST["TRACE_MONGO_DATABASE_HOST"];
		$trace_mongo_database_name          	= $_REQUEST["TRACE_MONGO_DATABASE_NAME"];
		$trace_mongo_database_username      	= $_REQUEST["TRACE_MONGO_DATABASE_USER"];
		$trace_mongo_database_password      	= $_REQUEST["TRACE_MONGO_DATABASE_PASSWORD"];
		$trace_mongo_database_conf_password 	= $_REQUEST["TRACE_MONGO_DATABASE_CONF_PASSWORD"];


		//notify
		$notify_table_name          			= $_REQUEST["NOTIFY_TABLE_NAME"];
		$notify_table_key          				= $_REQUEST["NOTIFY_TABLE_KEY"];
		$notify_onesignal_app_id      			= $_REQUEST["NOTIFY_ONESIGNAL_APP_ID"];
		$notify_onesignal_api_key      			= $_REQUEST["NOTIFY_ONESIGNAL_API_KEY"];


		$notify_database_host          			= $_REQUEST["NOTIFY_DATABASE_HOST"];
		$notify_database_name          			= $_REQUEST["NOTIFY_DATABASE_NAME"];
		$notify_database_username      			= $_REQUEST["NOTIFY_DATABASE_USER"];
		$notify_database_password      			= $_REQUEST["NOTIFY_DATABASE_PASSWORD"];
		$notify_database_conf_password 			= $_REQUEST["NOTIFY_DATABASE_CONF_PASSWORD"];

		$notify_mongo_database_host          	= $_REQUEST["NOTIFY_MONGO_DATABASE_HOST"];
		$notify_mongo_database_name          	= $_REQUEST["NOTIFY_MONGO_DATABASE_NAME"];
		$notify_mongo_database_username      	= $_REQUEST["NOTIFY_MONGO_DATABASE_USER"];
		$notify_mongo_database_password      	= $_REQUEST["NOTIFY_MONGO_DATABASE_PASSWORD"];
		$notify_mongo_database_conf_password 	= $_REQUEST["NOTIFY_MONGO_DATABASE_CONF_PASSWORD"];

		//gdpr
		$gdpr_compliance          									= $_REQUEST["GDPR_COMPLIANCE"];

		$anagraph_identification_database_host          			= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_HOST"];
		$anagraph_identification_database_name          			= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_NAME"];
		$anagraph_identification_database_username      			= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_USER"];
		$anagraph_identification_database_password      			= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_PASSWORD"];
		$anagraph_identification_database_conf_password 			= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_CONF_PASSWORD"];
		$anagraph_identification_database_crypt 					= $_REQUEST["ANAGRAPH_IDENTIFICATION_DATABASE_CRYPT"];

		$anagraph_access_database_host          					= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_HOST"];
		$anagraph_access_database_name          					= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_NAME"];
		$anagraph_access_database_username      					= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_USER"];
		$anagraph_access_database_password      					= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_PASSWORD"];
		$anagraph_access_database_conf_password 					= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_CONF_PASSWORD"];
		$anagraph_access_database_crypt 							= $_REQUEST["ANAGRAPH_ACCESS_DATABASE_CRYPT"];

		$anagraph_general_database_host          					= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_HOST"];
		$anagraph_general_database_name          					= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_NAME"];
		$anagraph_general_database_username      					= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_USER"];
		$anagraph_general_database_password      					= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_PASSWORD"];
		$anagraph_general_database_conf_password 					= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_CONF_PASSWORD"];
		$anagraph_general_database_crypt 							= $_REQUEST["ANAGRAPH_GENERAL_DATABASE_CRYPT"];

		$anagraph_sensivity_database_host          					= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_HOST"];
		$anagraph_sensivity_database_name          					= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_NAME"];
		$anagraph_sensivity_database_username      					= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_USER"];
		$anagraph_sensivity_database_password      					= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_PASSWORD"];
		$anagraph_sensivity_database_conf_password 					= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_CONF_PASSWORD"];
		$anagraph_sensivity_database_crypt 							= $_REQUEST["ANAGRAPH_SENSIVITY_DATABASE_CRYPT"];

		$anagraph_rel_database_host          						= $_REQUEST["ANAGRAPH_REL_DATABASE_HOST"];
		$anagraph_rel_database_name          						= $_REQUEST["ANAGRAPH_REL_DATABASE_NAME"];
		$anagraph_rel_database_username      						= $_REQUEST["ANAGRAPH_REL_DATABASE_USER"];
		$anagraph_rel_database_password      						= $_REQUEST["ANAGRAPH_REL_DATABASE_PASSWORD"];
		$anagraph_rel_database_conf_password 						= $_REQUEST["ANAGRAPH_REL_DATABASE_CONF_PASSWORD"];
		$anagraph_rel_database_crypt 								= $_REQUEST["ANAGRAPH_REL_DATABASE_CRYPT"];



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

		$username               = $_REQUEST["Username"];
		$password               = $_REQUEST["Password"];
		$conf_password          = $_REQUEST["Conf_Password"];

		$master_site            = $_REQUEST["MASTER_SITE"];
		$production_site        = $_REQUEST["PRODUCTION_SITE"];
		$development_site		= $_REQUEST["DEVELOPMENT_SITE"];

		$auth_username           = $_REQUEST["AUTH_USERNAME"];
		$auth_password           = $_REQUEST["AUTH_PASSWORD"];

		$ftp_username           = $_REQUEST["FTP_USERNAME"];
		$ftp_password           = $_REQUEST["FTP_PASSWORD"];
		$ftp_confirm_password   = $_REQUEST["FTP_CONFIRM_PASSWORD"];

        $debug_mode   			= $_REQUEST["DEBUG_MODE"];
        $debug_profiling   		= $_REQUEST["DEBUG_PROFILING"];
        $debug_log   			= $_REQUEST["DEBUG_LOG"];

        $disable_cache			= (isset($_REQUEST["DISABLE_CACHE"])
									? $_REQUEST["DISABLE_CACHE"]
									: null
								);
        $cache_last_version		= $_REQUEST["CACHE_LAST_VERSION"];

		$site_title             = $_REQUEST["SITE_TITLE"];
		$site_ssl				= $_REQUEST["SITE_SSL"];
		$appid                  = $_REQUEST["APPID"];

		$trace_visitor 			= $_REQUEST["TRACE_VISITOR"];

		$framework_css       	= $_REQUEST["FRAMEWORK_CSS"];
		$font_icon       		= $_REQUEST["FONT_ICON"];
		$language_default       = $_REQUEST["LANGUAGE_DEFAULT"];
		$language_default_id	= $arrLang[$language_default];
		$logo_favicon			= $_REQUEST["LOGO_FAVICON"];
		$logo_email				= $_REQUEST["LOGO_EMAIL"];

		$admin_theme						= $_REQUEST["ADMIN_THEME"];
		$language_restricted_default       	= $_REQUEST["LANGUAGE_RESTRICTED_DEFAULT"];
		$language_restricted_default_id		= $arrLang[$language_restricted_default];
		$framework_css_restricted       	= $_REQUEST["FRAMEWORK_CSS_RESTRICTED"];
		$font_icon_restricted       		= $_REQUEST["FONT_ICON_RESTRICTED"];
		$logo_brand							= $_REQUEST["LOGO_BRAND"];
		$logo_docs							= $_REQUEST["LOGO_DOCS"];

		$cdn_static           	= (isset($_REQUEST["CDN_STATIC"])
									? $_REQUEST["CDN_STATIC"]
									: null
								);
		$cdn_services           =  (isset($_REQUEST["CDN_SERVICES"])
									? $_REQUEST["CDN_SERVICES"]
									: null
								);

		$memory_limit           = $_REQUEST["MEMORY_LIMIT"];
		$service_time_limit     = $_REQUEST["SERVICE_TIME_LIMIT"];
		$timezone 				= $_REQUEST["TIMEZONE"];
		$security_shield 		= $_REQUEST["SECURITY_SHIELD"];


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

        //mongo
		if($mongo_database_password != $mongo_database_conf_password) {
			$mongo_database_password = "";
			$mongo_database_conf_password = "";
			$strError .= "Mongo Database password no match<br />";
		}
		//trace
		if($trace_database_password != $trace_database_conf_password) {
			$trace_database_password = "";
			$trace_database_conf_password = "";
			$strError .= "Trace Database password no match<br />";
		}
		if($trace_mongo_database_password != $trace_mongo_database_conf_password) {
			$trace_mongo_database_password = "";
			$trace_mongo_database_conf_password = "";
			$strError .= "Trace Mongo Database password no match<br />";
		}

		//notify
		if($notify_database_password != $notify_database_conf_password) {
			$notify_database_password = "";
			$notify_database_conf_password = "";
			$strError .= "Notify Database password no match<br />";
		}
		if($notify_mongo_database_password != $notify_mongo_database_conf_password) {
			$notify_mongo_database_password = "";
			$notify_mongo_database_conf_password = "";
			$strError .= "Notify Mongo Database password no match<br />";
		}

		//gdpr
		if($anagraph_identification_database_password != $anagraph_identification_database_conf_password) {
			$anagraph_identification_database_password = "";
			$anagraph_identification_database_conf_password = "";
			$strError .= "GDPR identification Database password no match<br />";
		}
		if($anagraph_access_database_password != $anagraph_access_database_conf_password) {
			$anagraph_access_database_password = "";
			$anagraph_access_database_conf_password = "";
			$strError .= "GDPR access Database password no match<br />";
		}
		if($anagraph_general_database_password != $anagraph_general_database_conf_password) {
			$anagraph_general_database_password = "";
			$anagraph_general_database_conf_password = "";
			$strError .= "GDPR general Database password no match<br />";
		}
		if($anagraph_sensivity_database_password != $anagraph_sensivity_database_conf_password) {
			$anagraph_sensivity_database_password = "";
			$anagraph_sensivity_database_conf_password = "";
			$strError .= "GDPR sensivity Database password no match<br />";
		}
		if($anagraph_rel_database_password != $anagraph_rel_database_conf_password) {
			$anagraph_rel_database_password = "";
			$anagraph_rel_database_conf_password = "";
			$strError .= "GDPR relationship Database password no match<br />";
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

		/**
		 * Load db Struct and data from Dump
		 */
        $db_install = new ffDB_Sql();
        $db_install->halt_on_connect_error = false;
        $db_res = @$db_install->connect($database_name, $database_host, $database_username, $database_password);

        if(!$db_res)
            $strError .= "Connection failed to database " . $database_name . "<br />";

        if(!is_readable($disk_path . "/conf/gallery/install/structure.sql") || filesize($disk_path . "/conf/gallery/install/structure.sql") <= 0)
            $strError .= "Unable read file: " . $disk_path . "/conf/gallery/install/structure.sql" . "<br />";

        if(!is_readable($disk_path . "/conf/gallery/install/data.sql") || filesize($disk_path . "/conf/gallery/install/data.sql") <= 0)
            $strError .= "Unable read file: " . $disk_path . "/install/data.sql" . "<br />";

        if(!$strError) {
			$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params) {
				///////////////////////////////
				//Scrittura del file config.remote.php
				///////////////////////////////
				$strError = installer_write("/themes/site/conf/config.remote.php"
					, "config.tpl"
					, array(
						"conn_id" 									=> $conn_id
						, "path" 									=> $ftp_path
					)
					, $params
				);

				return $strError;
			}, array(
				"[FF_DISK_PATH]"							=> $disk_path
				, "[FF_SITE_PATH]"							=> $site_path
				, "[SITE_UPDIR]"							=> $site_updir
				, "[DISK_UPDIR]"							=> $disk_updir

				, "[SESSION_SAVE_PATH]"						=> $session_save_path
				, "[SESSION_NAME]"							=> $session_name
				, "[MOD_SECURITY_SESSION_PERMANENT]"		=> ($session_permanent ? "true" : "false") //$session_permanent

				, "[DB_CHARACTER_SET]"						=> $character_set
				, "[DB_COLLATION]"							=> $collation

				, "[FF_DATABASE_HOST]"						=> $database_host
				, "[FF_DATABASE_NAME]"						=> $database_name
				, "[FF_DATABASE_USER]"						=> $database_username
				, "[FF_DATABASE_PASSWORD]"					=> $database_password

				, "[MONGO_DATABASE_HOST]"					=> $mongo_database_host
				, "[MONGO_DATABASE_NAME]"					=> $mongo_database_name
				, "[MONGO_DATABASE_USER]"					=> $mongo_database_username
				, "[MONGO_DATABASE_PASSWORD]"				=> $mongo_database_password

				, "[TRACE_TABLE_NAME]"						=> $trace_table_name
				, "[TRACE_ONESIGNAL_APP_ID]"				=> $trace_onesignal_app_id
				, "[TRACE_ONESIGNAL_API_KEY]"				=> $trace_onesignal_api_key

				, "[TRACE_DATABASE_HOST]"					=> $trace_database_host
				, "[TRACE_DATABASE_NAME]"					=> $trace_database_name
				, "[TRACE_DATABASE_USER]"					=> $trace_database_username
				, "[TRACE_DATABASE_PASSWORD]"				=> $trace_database_password

				, "[TRACE_MONGO_DATABASE_HOST]"				=> $trace_mongo_database_host
				, "[TRACE_MONGO_DATABASE_NAME]"				=> $trace_mongo_database_name
				, "[TRACE_MONGO_DATABASE_USER]"				=> $trace_mongo_database_username
				, "[TRACE_MONGO_DATABASE_PASSWORD]"			=> $trace_mongo_database_password

				, "[NOTIFY_TABLE_NAME]"						=> $notify_table_name
				, "[NOTIFY_TABLE_KEY]"						=> $notify_table_key
				, "[NOTIFY_ONESIGNAL_APP_ID]"				=> $notify_onesignal_app_id
				, "[NOTIFY_ONESIGNAL_API_KEY]"				=> $notify_onesignal_api_key

				, "[NOTIFY_DATABASE_HOST]"					=> $notify_database_host
				, "[NOTIFY_DATABASE_NAME]"					=> $notify_database_name
				, "[NOTIFY_DATABASE_USER]"					=> $notify_database_username
				, "[NOTIFY_DATABASE_PASSWORD]"				=> $notify_database_password

				, "[NOTIFY_MONGO_DATABASE_HOST]"			=> $notify_mongo_database_host
				, "[NOTIFY_MONGO_DATABASE_NAME]"			=> $notify_mongo_database_name
				, "[NOTIFY_MONGO_DATABASE_USER]"			=> $notify_mongo_database_username
				, "[NOTIFY_MONGO_DATABASE_PASSWORD]"		=> $notify_mongo_database_password

				, "[GDPR_COMPLIANCE]"								=> ($gdpr_compliance ? "true" : "false")

				, "[ANAGRAPH_IDENTIFICATION_DATABASE_HOST]"			=> $anagraph_identification_database_host
				, "[ANAGRAPH_IDENTIFICATION_DATABASE_NAME]"			=> $anagraph_identification_database_name
				, "[ANAGRAPH_IDENTIFICATION_DATABASE_USER]"			=> $anagraph_identification_database_username
				, "[ANAGRAPH_IDENTIFICATION_DATABASE_PASSWORD]"		=> $anagraph_identification_database_password
				, "[ANAGRAPH_IDENTIFICATION_DATABASE_CRYPT]"		=> ($anagraph_identification_database_crypt ? "true" : "false")

				, "[ANAGRAPH_ACCESS_DATABASE_HOST]"					=> $anagraph_access_database_host
				, "[ANAGRAPH_ACCESS_DATABASE_NAME]"					=> $anagraph_access_database_name
				, "[ANAGRAPH_ACCESS_DATABASE_USER]"					=> $anagraph_access_database_username
				, "[ANAGRAPH_ACCESS_DATABASE_PASSWORD]"				=> $anagraph_access_database_password
				, "[ANAGRAPH_ACCESS_DATABASE_CRYPT]"				=> ($anagraph_access_database_crypt ? "true" : "false")

				, "[ANAGRAPH_GENERAL_DATABASE_HOST]"				=> $anagraph_general_database_host
				, "[ANAGRAPH_GENERAL_DATABASE_NAME]"				=> $anagraph_general_database_name
				, "[ANAGRAPH_GENERAL_DATABASE_USER]"				=> $anagraph_general_database_username
				, "[ANAGRAPH_GENERAL_DATABASE_PASSWORD]"			=> $anagraph_general_database_password
				, "[ANAGRAPH_GENERAL_DATABASE_CRYPT]"				=> ($anagraph_general_database_crypt ? "true" : "false")

				, "[ANAGRAPH_SENSIVITY_DATABASE_HOST]"				=> $anagraph_sensivity_database_host
				, "[ANAGRAPH_SENSIVITY_DATABASE_NAME]"				=> $anagraph_sensivity_database_name
				, "[ANAGRAPH_SENSIVITY_DATABASE_USER]"				=> $anagraph_sensivity_database_username
				, "[ANAGRAPH_SENSIVITY_DATABASE_PASSWORD]"			=> $anagraph_sensivity_database_password
				, "[ANAGRAPH_SENSIVITY_DATABASE_CRYPT]"				=> ($anagraph_sensivity_database_crypt ? "true" : "false")

				, "[ANAGRAPH_REL_DATABASE_HOST]"					=> $anagraph_rel_database_host
				, "[ANAGRAPH_REL_DATABASE_NAME]"					=> $anagraph_rel_database_name
				, "[ANAGRAPH_REL_DATABASE_USER]"					=> $anagraph_rel_database_username
				, "[ANAGRAPH_REL_DATABASE_PASSWORD]"				=> $anagraph_rel_database_password
				, "[ANAGRAPH_REL_DATABASE_CRYPT]"					=> ($anagraph_rel_database_crypt ? "true" : "false")


				, "[A_SMTP_HOST]"							=> $smtp_host
				, "[SMTP_AUTH]"								=> ($smtp_auth ? "true" : "false")
				, "[A_SMTP_USER]"							=> $smtp_username
				, "[A_SMTP_PASSWORD]"						=> $smtp_password
				, "[A_SMTP_PORT]"							=> $smtp_port
				, "[A_SMTP_SECURE]"							=> $smtp_secure

				, "[A_FROM_EMAIL]"							=> $email_address
				, "[A_FROM_NAME]"							=> $email_name
				, "[CC_FROM_EMAIL]"							=> $cc_address
				, "[CC_FROM_NAME]"							=> $cc_name
				, "[BCC_FROM_EMAIL]"						=> $bcc_address
				, "[BCC_FROM_NAME]"							=> $bcc_name

				, "[SUPERADMIN_USERNAME]"					=> $username
				, "[SUPERADMIN_PASSWORD]"					=> $password

				, "[MASTER_SITE]"							=> $master_site
				, "[MASTER_TOKEN]"							=> (defined("MASTER_TOKEN") && MASTER_TOKEN ? MASTER_TOKEN : $master_token)
				, "[PRODUCTION_SITE]"						=> $production_site
				, "[DEVELOPMENT_SITE]"						=> $development_site

				, "[AUTH_USERNAME]"							=> $auth_username
				, "[AUTH_PASSWORD]"							=> $auth_password

				, "[FTP_USERNAME]"							=> $ftp_username
				, "[FTP_PASSWORD]"							=> $ftp_password
				, "[FTP_PATH]"								=> ($ftp_path ? $ftp_path : "/")

				, "[DEBUG_MODE]"							=> ($debug_mode ? "true" : "false")
				, "[DEBUG_PROFILING]"						=> ($debug_profiling ? "true" : "false")
				, "[DEBUG_LOG]"								=> ($debug_log ? "true" : "false")

				, "[DISABLE_CACHE_ESCAPE]"					=> (!$disable_cache
						? '//'
						: ''
					) //
				, "[CACHE_LAST_VERSION]"					=> $cache_last_version //

				, "[CM_LOCAL_APP_NAME]"						=> $site_title
				, "[APPID]"									=> $appid

				, "[TRACE_VISITOR]"							=> ($trace_visitor ? "true" : "false")

				, "[FRAMEWORK_CSS]"							=> $framework_css
				, "[FONT_ICON]"								=> $font_icon
				, "[LANGUAGE_DEFAULT]"						=> $language_default
				, "[LANGUAGE_DEFAULT_ID]"					=> $language_default_id
				, "[LOGO_FAVICON]"							=> $logo_favicon //
				, "[LOGO_EMAIL]"							=> $logo_email //

				, "[ADMIN_THEME]"							=> $admin_theme
				, "[FRAMEWORK_CSS_RESTRICTED]"				=> $framework_css_restricted
				, "[FONT_ICON_RESTRICTED]"					=> $font_icon_restricted
				, "[LANGUAGE_RESTRICTED_DEFAULT]"			=> $language_restricted_default
				, "[LANGUAGE_RESTRICTED_DEFAULT_ID]"		=> $language_restricted_default_id
				, "[LOGO_BRAND]"							=> $logo_brand //
				, "[LOGO_DOCS]"								=> $logo_docs //

				, "[CDN_STATIC_ESCAPE]"						=> (!$cdn_static
						? '//'
						: ''
					)
				, "[CDN_SERVICES_ESCAPE]"					=> (!$cdn_services
						? '//'
						: ''
					)

				, "[MEMORY_LIMIT]"							=> $memory_limit
				, "[SERVICE_TIME_LIMIT]"					=> $service_time_limit
				, "[TIMEZONE]"								=> $timezone
				, "[SECURITY_SHIELD]"						=> ($security_shield ? "true" : "false")

				, "[PHP_EXT_MEMCACHE]"						=> (array_search("memcached", $php_ext_loaded) === false
						? "false"
						: "true"
					)
				, "[PHP_EXT_APC]"							=> (array_search("apc", $php_ext_loaded) === false
						? "false"
						: "true"
					)
				, "[PHP_EXT_JSON]"							=> (array_search("json", $php_ext_loaded) === false
						? "false"
						: "true"
					)
				, "[PHP_EXT_GD]"							=> (array_search("gd", $php_ext_loaded) === false
						? "false"
						: "true"
					)
				, "[APACHE_MODULE_EXPIRES]"					=> (array_search("mod_expires", $php_ext_loaded) === false
						? "false"
						: "true"
					)
				, "[MYSQLI_EXTENSIONS]"						=> (function_exists("mysqli_init") && function_exists('mysqli_fetch_all')
						? "true"
						: "false"
					)
				, "[DOMAIN_PROTOCOL]"						=> ($site_ssl ? "https" : "http")
			));
			///////////////////////////////////

            if($master_site) {

				if(!$strError && $_SERVER["REQUEST_URI"] == $site_path . "/setup") {
					$strError = updater_resetDB($db_install);
                }

				// CERCA di sincronizzare il database con il MASTER SITE in merito alla STRUTTURA
				if(!$strError) {
					// Tenta di sincronizzare la STRUTTURA DB con il MASTER SITE
					$count_limit = 0;
					do {
						$count_limit++;
						$strError .= installer_updater("structure", true);
					} while(!$strError && $count_limit <= 10);
				}

				if(!$strError) {
					// Tenta di sincronizzare la INDICI DB con il MASTER SITE
					 $count_limit = 0;
					do {
						$count_limit++;
						$strError .= installer_updater("indexes", true);
					} while(!$strError && $count_limit <= 10);
				}

				if(!$strError) {
					// Tenta di sincronizzare la DATI BASE DB con il MASTER SITE
					 $count_limit = 0;
					do {
						$count_limit++;
						$strError .= installer_updater("data", true);
					} while(!$strError && $count_limit <= 10);
				}
				if(!$strError) {
					// Tenta di sincronizzare i DATI INTERNATIONAL DB con il MASTER SITE
					$count_limit = 0;
					do {
						$count_limit++;
						$strError .= installer_updater("international", true);
					} while(!$strError && $count_limit <= 10);
				}

            }                
        }

		if(!$strError) {
			$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params) {
				///////////////////////////////
				//Scrittura del file .htaccess
				///////////////////////////////
				$strError = installer_write("/.htaccess"
					, ".htaccess-cms.tpl"
					, array(
						"conn_id" 									=> $conn_id
						, "path" 									=> $ftp_path
					)
					, $params
				);

				return $strError;
			}, array(
				"[FF_SITE_PATH]"									=> $site_path
				, "[DOMAIN_PROTOCOL]"								=> ($site_ssl ? "https" : "http")
			));

			require_once($disk_path . "/library/gallery/common/convert_db.php");

			$strError .= convert_db($collation, $character_set, $database_name, $db_install);
		}
		if(!$strError) {
			$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params)
			{
				///////////////////////////////
				//Scrittura FS Need
				///////////////////////////////
				$strError = installer_fs_need(array(
						"conn_id" 									=> $conn_id
						, "path" 									=> $ftp_path
					)
					, $params
				);

				return $strError;
			}, array(
				"[FF_SITE_PATH]"							=> $site_path
			));
			if($ftp_report["error"])
				$strError .= $ftp_report["error"];

			if(!$strError) {
				header("Location: " . $site_path . "/install?complete");
				exit;
			}
		}
    } 
    else 
    {
        $disk_path          = (defined("FF_DISK_PATH") ? FF_DISK_PATH : "");
        $site_path          = (defined("FF_SITE_PATH") ? FF_SITE_PATH : "");
        $disk_updir         = (defined("DISK_UPDIR") ? DISK_UPDIR : "");
        $site_updir         = (defined("SITE_UPDIR") ? SITE_UPDIR : "");

        $session_save_path  = (defined("SESSION_SAVE_PATH") ? SESSION_SAVE_PATH : "");
        $session_name       = (defined("SESSION_NAME") ? SESSION_NAME : "");
        $session_permanent  = (defined("MOD_SECURITY_SESSION_PERMANENT") ? MOD_SECURITY_SESSION_PERMANENT : "");

		$character_set      = (defined("CHARACTER_SET") ? CHARACTER_SET : "");
		$collation       	= (defined("COLLATION") ? COLLATION : "");

		$database_host      = (defined("FF_DATABASE_HOST") ? FF_DATABASE_HOST : "");
        $database_name      = (defined("FF_DATABASE_NAME") ? FF_DATABASE_NAME : "");
        $database_username  = (defined("FF_DATABASE_USER") ? FF_DATABASE_USER : "");
        $database_password  = (defined("FF_DATABASE_PASSWORD") ? FF_DATABASE_PASSWORD : "");

        //mongo
		$mongo_database_host      			= (defined("MONGO_DATABASE_HOST") ? MONGO_DATABASE_HOST : "");
        $mongo_database_name      			= (defined("MONGO_DATABASE_NAME") ? MONGO_DATABASE_NAME : "");
		$mongo_database_username  			= (defined("MONGO_DATABASE_USER") ? MONGO_DATABASE_USER : "");
		$mongo_database_password  			= (defined("MONGO_DATABASE_PASSWORD") ? MONGO_DATABASE_PASSWORD : "");

		/**
		 * TRACE
		 */
		$trace_table_name      				= (defined("TRACE_TABLE_NAME") ? TRACE_TABLE_NAME : "");
		$trace_onesignal_app_id      		= (defined("TRACE_ONESIGNAL_APP_ID") ? TRACE_ONESIGNAL_APP_ID : "");
		$trace_onesignal_api_key  			= (defined("TRACE_ONESIGNAL_API_KEY") ? TRACE_ONESIGNAL_API_KEY : "");

		$trace_database_host      			= (defined("TRACE_DATABASE_HOST") ? TRACE_DATABASE_HOST : "");
		$trace_database_name      			= (defined("TRACE_DATABASE_NAME") ? TRACE_DATABASE_NAME : "");
		$trace_database_username  			= (defined("TRACE_DATABASE_USER") ? TRACE_DATABASE_USER : "");
		$trace_database_password  			= (defined("TRACE_DATABASE_PASSWORD") ? TRACE_DATABASE_PASSWORD : "");

		$trace_mongo_database_host      	= (defined("TRACE_MONGO_DATABASE_HOST") ? TRACE_MONGO_DATABASE_HOST : "");
		$trace_mongo_database_name      	= (defined("TRACE_MONGO_DATABASE_NAME") ? TRACE_MONGO_DATABASE_NAME : "");
		$trace_mongo_database_username  	= (defined("TRACE_MONGO_DATABASE_USER") ? TRACE_MONGO_DATABASE_USER : "");
		$trace_mongo_database_password  	= (defined("TRACE_MONGO_DATABASE_PASSWORD") ? TRACE_MONGO_DATABASE_PASSWORD : "");

		/**
		 * NOTIFY
		 */
		$notify_table_name      			= (defined("NOTIFY_TABLE_NAME") ? NOTIFY_TABLE_NAME : "");
		$notify_table_key      				= (defined("NOTIFY_TABLE_KEY") ? NOTIFY_TABLE_KEY : "");
		$notify_onesignal_app_id      		= (defined("NOTIFY_ONESIGNAL_APP_ID") ? NOTIFY_ONESIGNAL_APP_ID : "");
		$notify_onesignal_api_key  			= (defined("NOTIFY_ONESIGNAL_API_KEY") ? NOTIFY_ONESIGNAL_API_KEY : "");

		$notify_database_host      			= (defined("NOTIFY_DATABASE_HOST") ? NOTIFY_DATABASE_HOST : "");
		$notify_database_name      			= (defined("NOTIFY_DATABASE_NAME") ? NOTIFY_DATABASE_NAME : "");
		$notify_database_username  			= (defined("NOTIFY_DATABASE_USER") ? NOTIFY_DATABASE_USER : "");
		$notify_database_password  			= (defined("NOTIFY_DATABASE_PASSWORD") ? NOTIFY_DATABASE_PASSWORD : "");

		$notify_mongo_database_host      	= (defined("NOTIFY_MONGO_DATABASE_HOST") ? NOTIFY_MONGO_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("NOTIFY_MONGO_DATABASE_NAME") ? NOTIFY_MONGO_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("NOTIFY_MONGO_DATABASE_USER") ? NOTIFY_MONGO_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("NOTIFY_MONGO_DATABASE_PASSWORD") ? NOTIFY_MONGO_DATABASE_PASSWORD : "");

		//GDPR
		$gdpr_compliance      				= (defined("GDPR_COMPLIANCE") ? GDPR_COMPLIANCE : "");

		$notify_mongo_database_host      	= (defined("ANAGRAPH_IDENTIFICATION_DATABASE_HOST") ? ANAGRAPH_IDENTIFICATION_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("ANAGRAPH_IDENTIFICATION_DATABASE_NAME") ? ANAGRAPH_IDENTIFICATION_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("ANAGRAPH_IDENTIFICATION_DATABASE_USER") ? ANAGRAPH_IDENTIFICATION_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_IDENTIFICATION_DATABASE_PASSWORD") ? ANAGRAPH_IDENTIFICATION_DATABASE_PASSWORD : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_IDENTIFICATION_DATABASE_CRYPT") ? ANAGRAPH_IDENTIFICATION_DATABASE_CRYPT : "");

		$notify_mongo_database_host      	= (defined("ANAGRAPH_ACCESS_DATABASE_HOST") ? ANAGRAPH_ACCESS_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("ANAGRAPH_ACCESS_DATABASE_NAME") ? ANAGRAPH_ACCESS_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("ANAGRAPH_ACCESS_DATABASE_USER") ? ANAGRAPH_ACCESS_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_ACCESS_DATABASE_PASSWORD") ? ANAGRAPH_ACCESS_DATABASE_PASSWORD : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_ACCESS_DATABASE_CRYPT") ? ANAGRAPH_ACCESS_DATABASE_CRYPT : "");

		$notify_mongo_database_host      	= (defined("ANAGRAPH_GENERAL_DATABASE_HOST") ? ANAGRAPH_GENERAL_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("ANAGRAPH_GENERAL_DATABASE_NAME") ? ANAGRAPH_GENERAL_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("ANAGRAPH_GENERAL_DATABASE_USER") ? ANAGRAPH_GENERAL_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_GENERAL_DATABASE_PASSWORD") ? ANAGRAPH_GENERAL_DATABASE_PASSWORD : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_GENERAL_DATABASE_CRYPT") ? ANAGRAPH_GENERAL_DATABASE_CRYPT : "");

		$notify_mongo_database_host      	= (defined("ANAGRAPH_SENSIVITY_DATABASE_HOST") ? ANAGRAPH_SENSIVITY_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("ANAGRAPH_SENSIVITY_DATABASE_NAME") ? ANAGRAPH_SENSIVITY_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("ANAGRAPH_SENSIVITY_DATABASE_USER") ? ANAGRAPH_SENSIVITY_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_SENSIVITY_DATABASE_PASSWORD") ? ANAGRAPH_SENSIVITY_DATABASE_PASSWORD : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_SENSIVITY_DATABASE_CRYPT") ? ANAGRAPH_SENSIVITY_DATABASE_CRYPT : "");

		$notify_mongo_database_host      	= (defined("ANAGRAPH_REL_DATABASE_HOST") ? ANAGRAPH_REL_DATABASE_HOST : "");
		$notify_mongo_database_name      	= (defined("ANAGRAPH_REL_DATABASE_NAME") ? ANAGRAPH_REL_DATABASE_NAME : "");
		$notify_mongo_database_username  	= (defined("ANAGRAPH_REL_DATABASE_USER") ? ANAGRAPH_REL_DATABASE_USER : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_REL_DATABASE_PASSWORD") ? ANAGRAPH_REL_DATABASE_PASSWORD : "");
		$notify_mongo_database_password  	= (defined("ANAGRAPH_REL_DATABASE_CRYPT") ? ANAGRAPH_REL_DATABASE_CRYPT : "");

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

		$username           = (defined("SUPERADMIN_USERNAME") ? SUPERADMIN_USERNAME : "");
		$password           = (defined("SUPERADMIN_PASSWORD") ? SUPERADMIN_PASSWORD : "");

		$master_site        = (defined("MASTER_SITE") ? MASTER_SITE : "");
		$production_site    = (defined("PRODUCTION_SITE") ? PRODUCTION_SITE : "");
		$development_site   = (defined("DEVELOPMENT_SITE") ? DEVELOPMENT_SITE : "");

		$auth_username       = (defined("AUTH_USERNAME") ? AUTH_USERNAME : "");
		$auth_password       = (defined("AUTH_PASSWORD") ? AUTH_PASSWORD : "");

		$ftp_username       = (defined("FTP_USERNAME") ? FTP_USERNAME : "");
		$ftp_password       = (defined("FTP_PASSWORD") ? FTP_PASSWORD : "");

		$debug_mode   		= (defined("DEBUG_MODE") ? DEBUG_MODE : "");
		$debug_profiling   	= (defined("DEBUG_PROFILING") ? DEBUG_PROFILING : "");
		$debug_log   		= (defined("DEBUG_LOG") ? DEBUG_LOG : "");

		$disable_cache 		= (defined("DISABLE_CACHE") ? DISABLE_CACHE : "");
		$cache_last_version	= (defined("CACHE_LAST_VERSION") ? CACHE_LAST_VERSION : "");

		$site_title         = (defined("CM_LOCAL_APP_NAME") ? CM_LOCAL_APP_NAME : "");
		$site_ssl         	= (defined("SITE_SSL") ? SITE_SSL : "");
		$appid              = (defined("APPID") ? APPID : "");

		$trace_visitor 		= (defined("TRACE_VISITOR") ? TRACE_VISITOR : "");

		$framework_css		= (defined("FRAMEWORK_CSS") ? FRAMEWORK_CSS : "");
		$font_icon			= (defined("FONT_ICON") ? FONT_ICON : "");
		$language_default   = (defined("LANGUAGE_DEFAULT") ? LANGUAGE_DEFAULT : "");
        $language_default_id= (defined("LANGUAGE_DEFAULT_ID") ? LANGUAGE_DEFAULT_ID : "");
		$logo_favicon		= (defined("LOGO_FAVICON") ? LOGO_FAVICON : "");
		$logo_email			= (defined("LOGO_EMAIL") ? LOGO_EMAIL : "");

		$admin_theme   					= (defined("ADMIN_THEME") ? ADMIN_THEME : "");
		$framework_css_restricted		= (defined("FRAMEWORK_CSS_RESTRICTED") ? FRAMEWORK_CSS_RESTRICTED : "");
		$font_icon_restricted			= (defined("FONT_ICON_RESTRICTED") ? FONT_ICON_RESTRICTED : "");
		$language_restricted_default   	= (defined("LANGUAGE_RESTRICTED_DEFAULT") ? LANGUAGE_RESTRICTED_DEFAULT : "");
		$language_restricted_default_id	= (defined("LANGUAGE_RESTRICTED_DEFAULT_ID") ? LANGUAGE_RESTRICTED_DEFAULT_ID : "");
		$logo_brand						= (defined("LOGO_BRAND") ? LOGO_BRAND : "");
		$logo_docs						= (defined("LOGO_DOCS") ? LOGO_DOCS : "");

		$cdn_static       	= (defined("CM_SHOWFILES") || defined("CM_MEDIACACHE_SHOWPATH") ? true : null);
		$cdn_services      	= (defined("CMS_API_PATH") || defined("CMS_REQUEST_PATH") ? true : null);

		$memory_limit       = (defined("MEMORY_LIMIT") ? MEMORY_LIMIT : "");
		$service_time_limit = (defined("SERVICE_TIME_LIMIT") ? SERVICE_TIME_LIMIT : "");
		$timezone   		= (defined("TIMEZONE") ? TIMEZONE : "");
		$security_shield 	= (defined("SECURITY_SHIELD") ? SECURITY_SHIELD : "");


        
        if(!strlen($disk_path))
            $disk_path = $path["disk_path"];
        if(!strlen($site_path))
            $site_path = $path["site_path"];
        if(!strlen($disk_updir))
            $disk_updir = $path["disk_updir"];
        if(!strlen($site_updir))
            $site_updir = $path["site_updir"];

        if(!strlen($session_save_path))
            $session_save_path = $st_session_save_path;
        if(!strlen($session_name))
            $session_name = $st_session_name;
		if($session_permanent === null)
			$session_permanent = $st_session_permanent;

		if(!strlen($character_set))
			$character_set = $st_character_set;
		if(!strlen($collation))
			$collation = $st_collation;

		if(!strlen($database_host))
			$database_host = "localhost";
        if(!strlen($database_name))
            $database_name = substr(str_replace(".", "_", $domain["name"]), 0 , 64);
        if(!strlen($database_username))
            $database_username = substr(str_replace(".", "_", $domain["unique"]), 0 , 16);
        if(!strlen($database_password))
            $database_password = "";

        //mongo
		if(!strlen($mongo_database_host))
			$mongo_database_host = "localhost";
		if(!strlen($mongo_database_password))
			$mongo_database_password = "";
		//trace
		if(!strlen($trace_database_host))
			$trace_database_host = "localhost";
		if(!strlen($trace_database_password))
			$trace_database_password = "";
		if(!strlen($trace_mongo_database_host))
			$trace_mongo_database_host = "localhost";
		if(!strlen($trace_mongo_database_password))
			$trace_mongo_database_password = "";
		//notify
		if(!strlen($notify_database_host))
			$notify_database_host = "localhost";
		if(!strlen($notify_database_password))
			$notify_database_password = "";
		if(!strlen($notify_mongo_database_host))
			$notify_mongo_database_host = "localhost";
		if(!strlen($notify_mongo_database_password))
			$notify_mongo_database_password = "";

		//gdpr

            
        if(!strlen($smtp_host))
            $smtp_host = "localhost";
        if(!strlen($smtp_auth))
            $smtp_auth = false;
        if(!strlen($smtp_username))
            $smtp_username = "info@" . $domain["name"];
        if(!strlen($smtp_password))
            $smtp_password = "";
        if(!strlen($smtp_port))
            $smtp_port = "25";
            
        if(!strlen($email_address))
            $email_address = "info@" . $domain["name"];
        if(!strlen($email_name))
            $email_name = ucfirst($domain["primary"]);
        if(!strlen($cc_address))
            $cc_address = "";
        if(!strlen($cc_name))
            $cc_name = "";
		if(!strlen($bcc_address))
			$bcc_address = "";
		if(!strlen($bcc_name))
			$bcc_name = "";

        if(!strlen($username))
            $username = "admin";
        if(!strlen($password))
            $password = "";

        if(!strlen($debug_mode))
	        $debug_mode   		= false;
        if(!strlen($debug_profiling))
	        $debug_profiling   	= false;
        if(!strlen($debug_log))
	        $debug_log   		= false;


        if(!strlen($cache_last_version))
        	$cache_last_version = 0;

		if(!strlen($appid))
			$appid = $st_appid;

		if(!strlen($trace_visitor))
			$trace_visitor 		= false;


		if(!strlen($framework_css)) {
			$framework_css = $st_framework_css;
		}
		if(!strlen($font_icon)) {
			$font_icon = $st_font_icon;
		}
		if(!strlen($language_default)) {
			$language_default = "ITA";
			$language_default_id = $arrLang[$language_default];
		}

		if(!strlen($admin_theme)) {
			$admin_theme = $st_admin_theme;
		}
		if(!strlen($framework_css_restricted)) {
			$framework_css_restricted = $framework_css;
		}
		if(!strlen($font_icon_restricted)) {
			$font_icon_restricted = $font_icon;
		}
		if(!strlen($language_restricted_default)) {
			$language_restricted_default = "ITA";
			$language_restricted_default_id = $arrLang[$language_restricted_default];
		}

		if($cdn_static === null)
			$cdn_static = $st_cdn_static;
		if($cdn_services === null)
			$cdn_services = $st_cdn_services;

		if(!strlen($memory_limit))
			$memory_limit = $st_memory_limit;
		if(!strlen($service_time_limit))
			$service_time_limit = $st_service_time_limit;
		if(!strlen($timezone))
			$timezone = $st_timezone;
		if(!strlen($security_shield))
			$security_shield = $st_security_shield;
    }


	/**
	 * INSTALLATION: TEMPLATE PARSE
	 */
    $tpl = ffTemplate::factory($disk_path . "/conf/gallery/install");
    $tpl->load_file("install.html", "Main");

    $tpl->set_var("disk_path", $disk_path);
    $tpl->set_var("site_path", $site_path);
    $tpl->set_var("disk_updir", $disk_updir);
    $tpl->set_var("site_updir", $site_updir);

	$tpl->set_var("domain", $domain["host_name"]);
	$tpl->set_var("domain_name", $domain["name"]);
    $tpl->set_var("domain_protocol", $domain["protocol"]);
	$tpl->set_var("domain_primary", $domain["primary"]);
	$tpl->set_var("domain_sub", $domain["sub"]);
	$tpl->set_var("domain_ext", $domain["ext"]);
	$tpl->set_var("domain_unique", $domain["unique"]);

	if(!function_exists("cm_getClassByFrameworkCss") && is_file($disk_path . "/library/FF/common/framework-css.php")) {
		require_once($disk_path . "/library/FF/common/framework-css.php");
	}
    if(function_exists("cm_getClassByFrameworkCss")) {
		$arrFrameworkCss  = cm_getFrameworkCss_settings();
		$arrFontIcon  = cm_getFontIcon_settings();

		$tpl->set_var("row_class", cm_getClassByFrameworkCss("", "row-default"), null, "bootstrap");
		$tpl->set_var("button_class", cm_getClassByFrameworkCss("", "link", null, "bootstrap"));

		if($arrFrameworkCss) {
			foreach($arrFrameworkCss AS $framework_name => $framework_params) {
				//normal
				$tpl->set_var("frameworkcss", $framework_name);
				$tpl->set_var("frameworkcss_label", ucfirst($framework_name));

				if($framework_name == $framework_css)
					$tpl->set_var("frameworkcss_current", 'selected="selected"');
				else
					$tpl->set_var("frameworkcss_current", '');
				$tpl->parse("SezFrameworkCssItem", true);

				if($framework_name == $framework_css_restricted)
					$tpl->set_var("frameworkcss_current", 'selected="selected"');
				else
					$tpl->set_var("frameworkcss_current", '');
				$tpl->parse("SezFrameworkCssItemRestricted", true);

				//fluid
				$tpl->set_var("frameworkcss", $framework_name . "-fluid");
				$tpl->set_var("frameworkcss_label", ucfirst($framework_name) . " Fluid");

				if($framework_name . "-fluid" == $framework_css)
					$tpl->set_var("frameworkcss_current", 'selected="selected"');
				else
					$tpl->set_var("frameworkcss_current", '');
				$tpl->parse("SezFrameworkCssItem", true);

				if($framework_name . "-fluid" == $framework_css_restricted)
					$tpl->set_var("frameworkcss_current", 'selected="selected"');
				else
					$tpl->set_var("frameworkcss_current", '');
				$tpl->parse("SezFrameworkCssItemRestricted", true);

			}

			$tpl->parse("SezFrameworkCss", false);
			$tpl->parse("SezFrameworkCssRestricted", false);
		}
		if($arrFontIcon) {
			foreach($arrFontIcon AS $fonticon_name => $fonticon_params) {
				//normal
				$tpl->set_var("fonticon", $fonticon_name);
				$tpl->set_var("fonticon_label", ucfirst($fonticon_name));

				if($fonticon_name == $font_icon)
					$tpl->set_var("fonticon_current", 'selected="selected"');
				else
					$tpl->set_var("fonticon_current", '');
				$tpl->parse("SezFontIconItem", true);

				if($fonticon_name == $font_icon_restricted)
					$tpl->set_var("fonticon_current", 'selected="selected"');
				else
					$tpl->set_var("fonticon_current", '');
				$tpl->parse("SezFontIconItemRestricted", true);
			}

			$tpl->parse("SezFontIcon", false);
			$tpl->parse("SezFontIconRestricted", false);
		}
	}
    $tpl->set_var("session_save_path", $session_save_path);
    $tpl->set_var("session_name", $session_name);
    $tpl->set_var("appid", $appid);
    $tpl->set_var("memory_limit", $memory_limit);
	if(array_search("memcached", $php_ext_loaded) === false) {
		$tpl->set_var("php_ext_memcache", "");
		$tpl->set_var("php_ext_memcache_label", "Off");
		$tpl->set_var("php_ext_memcache_class", "red");
	} else {
		$tpl->set_var("php_ext_memcache", "1");
		$tpl->set_var("php_ext_memcache_label", "On");
		$tpl->set_var("php_ext_memcache_class", "green");

	}
	if(array_search("apc", $php_ext_loaded) === false) {
		$tpl->set_var("php_ext_apc", "");
		$tpl->set_var("php_ext_apc_label", "Off");
		$tpl->set_var("php_ext_apc_class", "red");
	} else {
		$tpl->set_var("php_ext_apc", "1");
		$tpl->set_var("php_ext_apc_label", "On");
		$tpl->set_var("php_ext_apc_class", "green");

	}
	if(array_search("json", $php_ext_loaded) === false) {
		$tpl->set_var("php_ext_json", "");
		$tpl->set_var("php_ext_json_label", "Off");
		$tpl->set_var("php_ext_json_class", "red");
	} else {
		$tpl->set_var("php_ext_json", "1");
		$tpl->set_var("php_ext_json_label", "On");
		$tpl->set_var("php_ext_json_class", "green");

	}
	if(array_search("gd", $php_ext_loaded) === false) {
		$tpl->set_var("php_ext_gd", "");
		$tpl->set_var("php_ext_gd_label", "Off");
		$tpl->set_var("php_ext_gd_class", "red");
	} else {
		$tpl->set_var("php_ext_gd", "1");
		$tpl->set_var("php_ext_gd_label", "On");
		$tpl->set_var("php_ext_gd_class", "green");

	}
	if(array_search("mod_expires", $php_ext_loaded) === false) {
		$tpl->set_var("apache_module_expires", "");
		$tpl->set_var("apache_module_expires_label", "Off");
		$tpl->set_var("apache_module_expires_class", "red");
	} else {
		$tpl->set_var("apache_module_expires", "1");
		$tpl->set_var("apache_module_expires_label", "On");
		$tpl->set_var("apache_module_expires_class", "green");

	}
	if(function_exists("mysqli_init")) {
		$tpl->set_var("mysqli_extensions", "1");
		$tpl->set_var("mysqli_extensions_label", "On");
		$tpl->set_var("mysqli_extensions_class", "green");
	} else {
		$tpl->set_var("mysqli_extensions", "");
		$tpl->set_var("mysqli_extensions_label", "Off");
		$tpl->set_var("mysqli_extensions_class", "red");
	}


    if($session_permanent)
		$tpl->set_var("session_permanent", "checked=\"checked\"");
	else
		$tpl->set_var("session_permanent", "");

    if($cdn_static)
        $tpl->set_var("cdn_static", "checked=\"checked\"");
    else
        $tpl->set_var("cdn_static", "");

	if($cdn_services)
		$tpl->set_var("cdn_services", "checked=\"checked\"");
	else
		$tpl->set_var("cdn_services", "");

    $tpl->set_var("database_host", $database_host);
	$tpl->set_var("database_name", $database_name);
    $tpl->set_var("database_user", $database_username);
    $tpl->set_var("database_password", $database_password);

	//mongo
	$tpl->set_var("mongo_database_host", $mongo_database_host);
	$tpl->set_var("mongo_database_name", $mongo_database_name);
	$tpl->set_var("mongo_database_user", $mongo_database_username);
	$tpl->set_var("mongo_database_password", $mongo_database_password);


	//trace
	$tpl->set_var("trace_table_name", $trace_table_name);
	$tpl->set_var("trace_onesignal_app_id", $trace_onesignal_app_id);
	$tpl->set_var("trace_onesignal_api_key", $trace_onesignal_api_key);

	$tpl->set_var("trace_database_host", $trace_database_host);
	$tpl->set_var("trace_database_name", $trace_database_name);
	$tpl->set_var("trace_database_user", $trace_database_username);
	$tpl->set_var("trace_database_password", $trace_database_password);

	$tpl->set_var("trace_mongo_database_host", $trace_mongo_database_host);
	$tpl->set_var("trace_mongo_database_name", $trace_mongo_database_name);
	$tpl->set_var("trace_mongo_database_user", $trace_mongo_database_username);
	$tpl->set_var("trace_mongo_database_password", $trace_mongo_database_password);


	//notify
	$tpl->set_var("notify_table_name", $notify_table_name);
	$tpl->set_var("notify_table_key", $notify_table_key);
	$tpl->set_var("notify_onesignal_app_id", $notify_onesignal_app_id);
	$tpl->set_var("notify_onesignal_api_key", $notify_onesignal_api_key);

	$tpl->set_var("notify_database_host", $notify_database_host);
	$tpl->set_var("notify_database_name", $notify_database_name);
	$tpl->set_var("notify_database_user", $notify_database_username);
	$tpl->set_var("notify_database_password", $notify_database_password);

	$tpl->set_var("notify_mongo_database_host", $notify_mongo_database_host);
	$tpl->set_var("notify_mongo_database_name", $notify_mongo_database_name);
	$tpl->set_var("notify_mongo_database_user", $notify_mongo_database_username);
	$tpl->set_var("notify_mongo_database_password", $notify_mongo_database_password);


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
	if($site_ssl)
		$tpl->set_var("site_ssl", "checked=\"checked\"");
	else
		$tpl->set_var("site_ssl", "");

    $tpl->set_var("language_default_" . strtolower($language_default), 'selected="selected"');
	$tpl->set_var("language_restricted_default_" . strtolower($language_restricted_default), 'selected="selected"');

	$theme_dir = glob($disk_path . "/themes/*", GLOB_ONLYDIR);
	if(is_array($theme_dir) && count($theme_dir)) {
		foreach($theme_dir AS $dir_path) {
			$dir_name = basename($dir_path);
			if($dir_name == "restricted"
				|| $dir_name == "responsive"
				|| $dir_name == "default"
				|| $dir_name == "library"
				|| $dir_name == "gallery"
				|| $dir_name == "site"
			) {
				continue;
			}

			$tpl->set_var("admintheme", $dir_name);
			$tpl->set_var("admintheme_label", ucfirst($dir_name));
			if($dir_name == $admin_theme)
				$tpl->set_var("admintheme_current", 'selected="selected"');
			else
				$tpl->set_var("admintheme_current", "");

			$tpl->parse("SezAdminTheme", true);
		}
	}


	$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	if(is_array($tzlist) && count($tzlist)) {
		foreach($tzlist AS $zone) {
			$tpl->set_var("timezone", $zone);
			$tpl->set_var("timezone_label", $zone);
			if($zone == $timezone)
				$tpl->set_var("timezone_current", 'selected="selected"');
			else
				$tpl->set_var("timezone_current", "");

			$tpl->parse("SezTimezoneItem", true);
		}
	}

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
            header("Location: " . $site_path . "/setup");
            exit;
        }
    }