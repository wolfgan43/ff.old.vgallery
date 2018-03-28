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
error_reporting((E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED) | E_STRICT);

if(!function_exists("file_post_contents")) {
	function file_post_contents($url, $data = null, $username = null, $password = null, $method = "POST", $timeout = 60) {
		$postdata = null;
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
if(!function_exists("ftp_purge_dir")) {
	function ftp_purge_dir($conn_id, $ftp_disk_path, $relative_path, $local_disk_path = null) {
		$absolute_path = $ftp_disk_path . $relative_path;

		$res = true;
		if (@ftp_chdir($conn_id, $absolute_path)) {
			$handle = @ftp_nlist($conn_id, "-la " . $absolute_path);
			if (is_array($handle) && count($handle)) {
				foreach($handle AS $file) {
					if(basename($file) != "." && basename($file) != "..") {
						if(strlen($ftp_disk_path))
							$real_file = substr($file, strlen($ftp_disk_path));
						else
							$real_file = $file;

						if (@ftp_chdir($conn_id, $file)) {
							$res = ($res && ftp_purge_dir($conn_id, $ftp_disk_path, $real_file, $local_disk_path));
							@ftp_rmdir($conn_id, $file);
							if($local_disk_path !== null)
								@rmdir($local_disk_path . $real_file);
						} else {
							if(!@ftp_delete($conn_id, $file)) {
								if($local_disk_path === null) {
									$res = false;
								} else {
									if(!@unlink($local_disk_path . $real_file)) {
										$res = false;
									}
								}
							}
						}
					}
				}
			}

			if(!@ftp_rmdir($conn_id, $absolute_path)) {
				if($local_disk_path === null) {
					$res = false;
				} else {
					if(!@rmdir($local_disk_path . $relative_path)) {
						$res = false;
					}
				}
			}
		} else {
			if(!@ftp_delete($conn_id, $absolute_path)) {
				if($local_disk_path === null) {
					$res = false;
				} else {
					if(!@unlink($local_disk_path . $relative_path)) {
						$res = false;
					}
				}
			}
		}
		return $res;
	}
}

function installer_check_FF_class() {
	$arrError = array();
	$path = installer_get_path();

	if (!defined("FF_ENABLE_MEM_TPL_CACHING"))    define("FF_ENABLE_MEM_TPL_CACHING", false);
	if (!defined("FF_ENABLE_MEM_PAGE_CACHING")) define("FF_ENABLE_MEM_PAGE_CACHING", false);
	if (!defined("FF_CACHE_ADAPTER")) define("FF_CACHE_ADAPTER", "");

	if(!class_exists("ffTemplate")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffTemplate.php"))
			require_once($path["disk_path"] . "/ff/classes/ffTemplate.php");
		else
			$arrError[] = "Missing ffTemplate";
	}
	if(!class_exists("ffCommon")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffCommon.php"))
			require_once($path["disk_path"] . "/ff/classes/ffCommon.php");
		else
			$arrError[] = "Missing ffCommon";
	}
	if(!class_exists("ffEvents")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffEvents/ffEvents.php"))
			require_once($path["disk_path"] . "/ff/classes/ffEvents/ffEvents.php");
		else
			$arrError[] = "Missing ffEvents";
	}
	if(!class_exists("ffMemCache")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffCache/ffCache.php")) {
			require_once($path["disk_path"] . "/ff/classes/ffCache/ffCache.php");
			if(file_exists($path["disk_path"] . "/ff/classes/ffCache/ffCacheAdapter.php"))
				require_once($path["disk_path"] . "/ff/classes/ffCache/ffCacheAdapter.php");
		} else
			$arrError[] = "Missing ffMemCache";
	}
	if(!class_exists("ffData")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffData/ffData.php"))
			require_once($path["disk_path"] . "/ff/classes/ffData/ffData.php");
		else
			$arrError[] = "Missing ffData";
	}
	if(!class_exists("ffDB_Sql")) {
		if(file_exists($path["disk_path"] . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php"))
			require_once($path["disk_path"] . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
		else
			$arrError[] = "Missing ffDb_Sql";
	}

	if(!function_exists("ffCommon_charset_encode")) {
		if(file_exists($path["disk_path"] . "/ff/common.php"))
			require_once($path["disk_path"] . "/ff/common.php");
		else
			$arrError[] = "Missing FF Library";
	}

	return implode("<br />", $arrError);
}
function installer_write($file_dest, $filename_source, $ftp, $vars = array(), $writable = false) {
	$strError = "";
	///////////////////////////////
	//Scrittura del file
	///////////////////////////////
	$root_path = ($ftp["path"] == "/" ? "" : $ftp["path"]);
	$dirname_file = ffCommon_dirname($file_dest);
	if($dirname_file  && $dirname_file != "/") {
		if($writable)
			@ftp_chmod($ftp["conn_id"], 0777, $root_path . $dirname_file);
		else
			@ftp_chmod($ftp["conn_id"], 0755, $root_path . $dirname_file);
	}

	$content = installer_tpl_parse($filename_source, $vars);

	$tempHandle = @tmpfile();
	@fwrite($tempHandle, $content);
	@rewind($tempHandle);

	if(@ftp_size($ftp["conn_id"], $root_path . $file_dest) >= 0) {
		@ftp_delete($ftp["conn_id"], $root_path . $file_dest);
	}

	$ret = @ftp_nb_fput($ftp["conn_id"]
		, $root_path . $file_dest
		, $tempHandle
		, FTP_BINARY
		, FTP_AUTORESUME
	);
	while ($ret == FTP_MOREDATA) {
		// Do whatever you want
		// Continue upload...
		$ret = @ftp_nb_continue($ftp["conn_id"]);
	}
	if ($ret != FTP_FINISHED) {
		$strError .= "Unable write file: " . $root_path . $file_dest . "<br />";
	} else {
		@ftp_chmod($ftp["conn_id"], 0644, $root_path . $file_dest);
	}

	@fclose($tempHandle);

	return $strError;
}
function installer_fs_need($ftp, $vars = array()) {
	$strError = "";
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

	$arrFileNeed = array(
		"/themes/site/css/root.css" 		=> "readable"
	);
	$path = installer_get_path();

	///////////////////////////////
	//Scrittura del file .robots.txt
	///////////////////////////////
	$strError .= installer_write("/robots.txt"
		, "robots.tpl"
		, $ftp
		, $vars
	);

	if(is_array($arrPathNeed) && count($arrPathNeed)) {
		foreach($arrPathNeed AS $arrPathNeed_key => $arrPathNeed_value) {
			if(!file_exists($path["disk_path"] . $arrPathNeed_key)) {
				@ftp_mkdir($ftp["conn_id"], $ftp["path"] . $arrPathNeed_key);
			}
			if($arrPathNeed_value == "writable") {
				///////////////////////////////
				//Scrittura del file .htaccess-noscript
				///////////////////////////////
				$strError .= installer_write($arrPathNeed_key . "/.htaccess"
					, ".htaccess-noscript.tpl"
					, $ftp
					, $vars
					, true
				);
			} elseif($arrPathNeed_value == "readable") {
				@ftp_chmod($ftp["conn_id"], 0755, $ftp["path"] . $arrPathNeed_key);
			}
		}
	}
	///////////////////////////////
	//Scrittura del file .htaccess-noscript in /themes/site
	///////////////////////////////
	$strError .= installer_write("/themes/site/.htaccess"
		, ".htaccess-noscript.tpl"
		, $ftp
		, $vars
	);

	if(is_array($arrFileNeed) && count($arrFileNeed)) {
		$handle = @tmpfile();
		foreach($arrFileNeed AS $arrFileNeed_key => $arrFileNeed_value) {
			if(!file_exists($path["disk_path"] . $arrFileNeed_key)) {
				if(@ftp_fput($ftp["conn_id"], $ftp["path"] . $arrFileNeed_key, $handle, FTP_ASCII)) {
					if($arrFileNeed_value == "writable") {
						if(@ftp_chmod($ftp["conn_id"], 0777, $ftp["path"] . $arrFileNeed_key) === false) {
							@chmod($path["disk_path"] . $arrFileNeed_key, 0777);
						}
					} elseif($arrFileNeed_value == "readable") {
						if(@ftp_chmod($ftp["conn_id"], 0644, $ftp["path"] . $arrFileNeed_key) === false) {
							@chmod($path["disk_path"] . $arrFileNeed_key, 0644);
						}
					}
				}
			}
		}
		@fclose($handle);
	}

	return $strError;
}
function installer_server_requirements($block_level = false) {
	$total_php_value_need = 0;
	$allow_rewite_php_value_on_htaccess = 0;
	$strCriticalError = "";
	$strWarningError = "";

	$total_php_value_need++;
	if(version_compare(phpversion(), "5.3.0", "<"))
		$strCriticalError .= "PHP Version >= 5.3.0 required\n";
	else
		$allow_rewite_php_value_on_htaccess++;

	if(function_exists("apache_get_modules")) {
		$PHP_fastCGI = false;
		if(!in_array('mod_rewrite', apache_get_modules()))
			die("Apachee Module Rewrite must be Loaded\n");
	} else
		$PHP_fastCGI = true;

	$total_php_value_need++;
	if(ini_get("safe_mode"))
		if(ini_set("safe_mode", "0") === false)
			$strCriticalError .= "safe_mode must be Disabled\n";
		else
			$allow_rewite_php_value_on_htaccess++;

	$total_php_value_need++;
	if(ini_get("register_globals"))
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

	$res = array(
		"error" => array(
			"critical" => $strCriticalError
		, "warning" => $strWarningError
		)
	, "count" => array(
			"need" => $total_php_value_need
		, "one" => $allow_rewite_php_value_on_htaccess
		)
	, "PHP_fastCGI" => $PHP_fastCGI
	);

	if($block_level && $res["error"][$block_level])
		die($res["error"][$block_level]);

	return $res;
}
function installer_get_domain() {
	/**
	 *  Define DOMAIN Vars
	 */
	static $res = null;
	if(!$res) {
		$res["host_name"] = $_SERVER["HTTP_HOST"];
		if (strpos($res["host_name"], "www.") === 0) {
			$res["name"] = substr($res["host_name"], 4);
		} else {
			$res["name"] = $res["host_name"];
		}

		$res["protocol"] = ($_SERVER["HTTPS"] ? "http" : "https");
		if (substr_count($res["name"], ".") == 1
			|| strpos($res["name"], "www") === 0
//			|| substr($res["name"], 0, strpos($res["name"], ".")) > substr($res["name"], strpos($res["name"], ".") + 1)
		) {
			$res["sub"] = "";
			$res["primary"] = substr($res["name"], 0, strpos($res["name"], "."));
			$res["ext"] = substr($res["name"], strpos($res["name"], ".") + 1);

			$res["sig"] = $res["primary"] . "." . $res["ext"];
			$res["unique"] = $res["primary"];
		} else {
			$res["sub"] = substr($res["name"], 0, strpos($res["name"], "."));
			$arrDomain = explode(".", substr($res["name"], strpos($res["name"], ".") + 1), 2);
			$res["primary"] = $arrDomain[0];
			$res["ext"] = $arrDomain[1];

			$res["sig"] = $res["primary"] . "." . $res["ext"];
			$res["unique"] = $res["sub"] . "." . $res["primary"];
		}
	}
	return $res;
}
function installer_get_path() {
	/**
	 *  Define PATH Vars
	 */
	static $res = null;
	if(!$res) {
		if (strpos(php_uname(), "Windows") !== false)
			$tmp_file = str_replace("\\", "/", __FILE__);
		else
			$tmp_file = __FILE__;

		if (strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
			$res["document_root"] = $_SERVER["DOCUMENT_ROOT"];
			if (substr($res["document_root"], -1) == "/")
				$res["document_root"] = substr($res["document_root"], 0, -1);

			$res["site_path"] = str_replace($res["document_root"], "", str_replace("/conf/gallery/install/common.php", "", $tmp_file));
			$res["disk_path"] = $res["document_root"] . $res["site_path"];
		} elseif (strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
			$res["document_root"] = $_SERVER["PHP_DOCUMENT_ROOT"];
			if (substr($res["document_root"], -1) == "/")
				$res["document_root"] = substr($res["document_root"], 0, -1);

			$res["site_path"] = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/common/index.php", "", $_SERVER["SCRIPT_FILENAME"]));
			$res["disk_path"] = $res["document_root"] . str_replace($res["document_root"], "", str_replace("/conf/gallery/install/common.php", "", $tmp_file));
		} else {
			$res["disk_path"] = str_replace("/conf/gallery/install/common.php", "", $tmp_file);
			$res["site_path"] = str_replace("/conf/gallery/install/common.php", "", $_SERVER["SCRIPT_NAME"]);
		}

		$res["site_updir"] = $res["site_path"] . "/uploads";
		$res["disk_updir"] = $res["disk_path"] . "/uploads";
	}
	return $res;
}
function installer_tpl_vars($custom = array()) {
	static $res = null;
	if(!$res) {
		$domain = installer_get_domain();
		$path = installer_get_path();

		$res = array(
			"[DOMAIN]"									=> $domain["host_name"]
		, "[DOMAIN_PROTOCOL]"						=> $domain["protocol"]
		, "[DOMAIN_NAME]"							=> $domain["primary"]
		, "[DOMAIN_SUB]"							=> ($domain["sub"] ? $domain["sub"] : "www")
		, "[DOMAIN_EXT]"							=> $domain["ext"]
		, "[FF_SITE_PATH]"							=> $path["site_path"]
		, "[FF_SITE_UPDIR]"							=> $path["site_updir"]
		);
	}

	if(is_array($custom) && count($custom)) {
		$res = array_replace($res, $custom);
	}
	return $res;
}
function installer_tpl_parse($name, $vars = array()) {
	$path = (strpos($name, FF_DISK_PATH) === 0
		? $name
		: __DIR__ . "/" . $name
	);

	$vars = installer_tpl_vars($vars);
	$content = file_get_contents($path);

	return str_replace(array_keys($vars), array_values($vars), $content);
}
function installler_ftp_connection($callback = null, $params = null, $ftp_username = null, $ftp_password = null) {
	$strError = "";
	$ftp_path = null;
	$real_ftp_path = NULL;

	if(!$ftp_username)
		$ftp_username = FTP_USERNAME;
	if(!$ftp_password)
		$ftp_password = FTP_PASSWORD;

	if(strlen($ftp_username)) {
		$conn_id = @ftp_connect("localhost");
		if($conn_id === false)
			$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false)
			$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

		if($conn_id !== false)
		{
			$path = installer_get_path();
			// login with username and password
			if(@ftp_login($conn_id, $ftp_username, $ftp_password)) {
				$local_path = $path["disk_path"];
				$part_path = "";

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
				if($callback) {
					$strError .= call_user_func_array($callback, array(
						$conn_id
					, $real_ftp_path
					, $params
					));
				}
			}
		} else {
			$strError .= 'FTP Function are Disabled on Server. <a href="http://php.net/manual/en/ftp.installation.php" target="_blank">See Php Manual</a>';
		}
		@ftp_close($conn_id);
	}

	return array(
		"error" => $strError
	, "path" => $real_ftp_path
	);
}
function installer_updater($service, $exec = false) {
	$res 				= "";
	$services 			= array(
		"updater" => array(
			"name" 				=> "Updater"
		, "path" 			=> "/files.php/updater"
		, "ftp" 			=> true
		)
	, "file" => array(
			"name" 				=> "File"
		, "path" 			=> "/files.php"
		, "ftp" 			=> true
		)

	, "structure" => array(
			"name" 				=> "DB Structure"
		, "path" 			=> "/structure.php"
		, "ftp" 			=> false
		)
	, "indexes" => array(
			"name" 				=> "DB Index"
		, "path" 			=> "/indexes.php"
		, "ftp" 			=> false
		)
	, "data" => array(
			"name" 				=> "DB Data"
		, "path" 			=> "/data.php/basic"
		, "ftp" 			=> false
		)
	, "international" => array(
			"name" 				=> "DB International"
		, "path" 			=> "/data.php/international"
		, "ftp" 			=> false
		)
	);
	$domain 			= installer_get_domain();
	$path				= installer_get_path();
	$service_path 		= $services[$service]["path"];

	if($service_path) {
		$restore_file = file_post_contents(
			"http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $domain["host_name"] . $path["site_path"] . "/conf/gallery/updater" . $service_path
			. ($services[$service]["ftp"]
				? "?mc=" . urlencode(MASTER_SITE)
				. "&apuser=" . urlencode(FTP_USERNAME)
				. "&appw=" . urlencode(FTP_PASSWORD)
				: "?nowarning=1"
				. "&lo=-1"
			)
			. (strlen(AUTH_USERNAME) && strlen(AUTH_PASSWORD)
				? "&abpuser=" . urlencode(AUTH_USERNAME)
				. "&abppw=" . urlencode(AUTH_PASSWORD)
				: ""
			)
			. "&json=1"
			. ($exec
				? "&exec=1"
				: ""
			)
			, null
			, AUTH_USERNAME
			, AUTH_PASSWORD
			, "GET"
			, "120"
		);

		if (!$restore_file) {
			$res = "Critical Error... Please reload all " . ucfirst($service);
		} else {
			if (strlen($restore_file))
				$arr_restore_file = json_decode($restore_file, true);

			if (is_array($arr_restore_file)) {
				if (array_key_exists("error", $arr_restore_file)) {
					$res = "Error: " . $arr_restore_file["error"];
				} elseif (array_key_exists("result", $arr_restore_file)) {
					$res = array("result" => $arr_restore_file["result"]);
				} elseif (count($arr_restore_file["record"])) {
					$res = array("result" => count($arr_restore_file["record"]));
				}
			} else {
				$res = "Error: " . $restore_file;
			}
		}
	} else {
		$res = $service . " Not Found";
	}
	return $res;
}
function updater_resetDB($db = null) {
	$truncate_db = array();
	$strError = "";
	if(!$db)
		$db = new ffDB_Sql();

	$sSQL = "SHOW TABLES";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$truncate_db[$db->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = true;
		} while($db->nextRecord());
	}

	if(is_array($truncate_db) && count($truncate_db)) {
		foreach($truncate_db AS $truncate_db_key => $truncate_db_value) {
			if($truncate_db_value) {
				$sSQL = "TRUNCATE `" . $db->toSql($truncate_db_key, "Text", false) . "`";
				$db->execute($sSQL);
			}
		}
	}
	/*
			//Creazione e Scrittura della struttura del database
			// Aggiorna il database utilizzando il file locale che descrive la struttura di base
			$filename = __DIR__ . "/structure.sql";
			if(is_file($filename)) {
				$SQLs = file_get_contents($filename);
				if($SQLs) {
					$structure = explode(";", trim($SQLs));
					foreach($structure AS $sql) {
						if(strlen($sql)) {
							$db->execute($sql . ";");
						}
					}
				}
			} else {
				$strError = "Unable read file: " . "/conf/gallery/install/data.sql" . "<br />";
			}

			//Scrittura dei dati preliminari del database
			// Aggiorna il database utilizzando il file locale che descrive i dati di base
			$filename = __DIR__ . "/data.sql";
			if(is_file($filename)) {
				$SQLs = file_get_contents($filename);
				if($SQLs) {
					$SQLs = str_replace(
						array("[SUPERADMIN_NAME]", "[SUPERADMIN_PASSWORD]")
						, array(SUPERADMIN_USERNAME, $db->mysqlPassword(SUPERADMIN_PASSWORD))
						, $SQLs
					);

					$structure = explode(");", trim($SQLs));
					foreach($structure AS $sql) {
						if(strlen($sql)) {
							$db->execute($sql . ");");
						}
					}
				}
			} else {
				$strError = "Unable read file: " . "/conf/gallery/install/data.sql" . "<br />";
			}*/




	/*$sSQL = "CREATE TABLE IF NOT EXISTS `cm_mod_security_users_rel_groups` (
				  `uid` int(4) NOT NULL default '0',
				  `gid` int(4) NOT NULL default '0',
				  UNIQUE KEY `uid` (`uid`,`gid`)
				)";
	$db->execute($sSQL);*/

	/*$sSQL = "INSERT INTO `cm_mod_security_users` (`ID`, `ID_domains`, `level`, `expiration`, `status`, `active_sid`, `username`, `password`, `primary_gid`, `email`, `shippingaddress`, `shippingcap`, `shippingtown`, `shippingprovince`, `shippingstate`, `enable_ecommerce_data`, `enable_manage`, `ID_module_register`) VALUES
				(2, 0, '0', '0000-00-00 00:00:00', '', '2cdffa246995e64e686fa557918f0c3d', 'guest', 'guest', 2, '0', '', '', '0', '', 0, '', '', 0),
				(1, 0, '0', '0000-00-00 00:00:00', '1', '2cdffa246995e64e686fa557918f0c3d', " . $db->toSql(SUPERADMIN_USERNAME) . ", " . $db->toSql($db->mysqlPassword(SUPERADMIN_PASSWORD)) . ", 1, '0', '', '', '0', '', 0, '', '1', 0)
			";
	$db->execute($sSQL);*/

	/*$sSQL = "INSERT INTO `vgallery_nodes` (`ID`, `ID_vgallery`, `name`, `order`, `parent`, `ID_type`, `last_update`) VALUES
				(1, 0, '', '0', '/', 0, 0)
			";
	$db->execute($sSQL);
	$sSQL = "UPDATE `vgallery_nodes` SET `ID` = '0' WHERE (`vgallery_nodes`.`ID` = 1)";
	$db->execute($sSQL);*/

	return $strError;
}