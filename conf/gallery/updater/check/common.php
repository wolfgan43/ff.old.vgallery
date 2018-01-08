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

//error_reporting((E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED) | E_STRICT); //sta dentro l'altro common
require_once(realpath(__DIR__ . "/../../install/common.php"));

ini_set("memory_limit", "300M");

$path = installer_get_path();
if(!defined("FF_DISK_PATH"))					define("FF_DISK_PATH", $path["disk_path"]);
if(!defined("FF_SITE_PATH"))					define("FF_SITE_PATH", $path["site_path"]);

if(!defined("FRONTEND_THEME")) 				define("FRONTEND_THEME", "site");
if(!defined("FF_PREFIX"))						define("FF_PREFIX", "ff_");
if(!defined("FF_PHP_EXT")) 					define("FF_PHP_EXT", "php");

define("REAL_PATH"									, "/conf/gallery");

if(substr($_SERVER["HTTP_HOST"], -6) == ".local")
	$config_file = realpath(FF_DISK_PATH ."/themes/" . FRONTEND_THEME . "/conf/config.local.php");
else
	$config_file = FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/conf/config.remote.php";

if(is_file($config_file)) {
	require_once($config_file);
}

if(!defined("FTP_USERNAME") && !defined("FTP_PASSWORD")) {
	if(isset($_REQUEST["mc"]) && strlen($_REQUEST["mc"])
		&& isset($_REQUEST["apuser"]) && strlen($_REQUEST["apuser"])
		&& isset($_REQUEST["appw"]) && strlen($_REQUEST["appw"])) {


		$ftp_report = installler_ftp_connection(function($conn_id, $ftp_path, $params) {
			///////////////////////////////
			//Scrittura del file config.updater.php installer
			///////////////////////////////
			$strError = installer_write("/themes/site/conf/config.updater.php"
				, FF_DISK_PATH . REAL_PATH . "/install/config-updater.tpl"
				, array(
					"conn_id" 								=> $conn_id
					, "path" 								=> $ftp_path
				)
				, array(
					"[FTP_USERNAME]"						=> $_REQUEST["apuser"]
					, "[FTP_PASSWORD]"						=> $_REQUEST["appw"]
					, "[FTP_PATH]"							=> $ftp_path
					, "[AUTH_USERNAME]"						=> $_REQUEST["abpuser"]
					, "[AUTH_PASSWORD]"						=> $_REQUEST["abppw"]
					, "[MASTER_SITE]"						=> $_REQUEST["mc"]
				)
			);

			return $strError;
		}, null, $_REQUEST["apuser"], $_REQUEST["appw"]);
	}

	if(is_file(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/conf/config.updater.php")) {
		require_once(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/conf/config.updater.php");
	}
}




/*
if(!defined("MASTER_SITE")) {
	if(file_exists(FF_DISK_PATH . "/conf/gallery/config/updater.php")) {
		require_once(FF_DISK_PATH . "/conf/gallery/config/updater.php");
	} else {
		die("Missing Config Updater");
	}
}*/

if(file_exists(FF_DISK_PATH . "/ff/common.php"))
	require_once(FF_DISK_PATH . "/ff/common.php");

if(!defined("DOMAIN_INSET"))
	define("DOMAIN_INSET", $_SERVER["HTTP_HOST"]);

if(strpos(strtolower(DOMAIN_INSET), "www.") === 0) {
	define("DOMAIN_NAME"		, substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1));
} else {
	define("DOMAIN_NAME"		, DOMAIN_INSET);
}

if(defined("PRODUCTION_SITE") && strlen(PRODUCTION_SITE)) {
	if(strpos(PRODUCTION_SITE, "www.") === 0) {
		define("DOMAIN_SYNC_NAME"        , substr(PRODUCTION_SITE, 4));
	} else {
		define("DOMAIN_SYNC_NAME"        , PRODUCTION_SITE);
	}
} elseif(defined("DEVELOPMENT_SITE") && strlen(DEVELOPMENT_SITE)) {
	if(strpos(DEVELOPMENT_SITE, "www.") === 0) {
		define("DOMAIN_SYNC_NAME"        , substr(DEVELOPMENT_SITE, 4));
	} else {
		define("DOMAIN_SYNC_NAME"        , DEVELOPMENT_SITE);
	}
}

define("REMOTE_HOST", (strpos($_REQUEST["s"], "www.") === 0
	? substr($_REQUEST["s"], 4)
	:  $_REQUEST["s"]
));


if(!class_exists("ffDB_Sql") && file_exists(FF_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php")) {
	require_once(FF_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
}

if(MASTER_SITE == DOMAIN_INSET && !defined("FF_DATABASE_NAME")) {
	die("Missing Config DB");
}
if(MASTER_SITE == DOMAIN_INSET && !class_exists("ffDB_Sql")) {
	die("Missing DB Class");
}

function updater_get_params($cm, $externals = false) {
	if(is_object($cm)) {
		$db = ffDB_Sql::factory();

		$pathInfo = $cm->path_info;
		$realPathInfo = $cm->real_path_info;
		if($pathInfo == VG_RULE_UPDATER) {
			if (!AREA_UPDATER_SHOW_MODIFY) {
				ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
			}
		}

		$cm->oPage->form_method = "post";
	} else {
		if(class_exists("ffDB_Sql"))
			$db =  new ffDB_Sql;

		$fftmp_ffq = false;
		if (isset($_REQUEST["_ffq_"])) // used to manage .htaccess [QSA] option, this overwhelm other options
		{
			$fftmp_ffq = true;
			$_SERVER["PATH_INFO"] = $_REQUEST["_ffq_"];
			$_SERVER["ORIG_PATH_INFO"] = $_REQUEST["_ffq_"];
		}
		else if (isset($_SERVER["ORIG_PATH_INFO"]))
			$_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];

		if (strlen($_SERVER["QUERY_STRING"]))
		{
			$fftmp_new_querystring = "";
			$fftmp_parts = explode("&", rtrim($_SERVER["QUERY_STRING"], "&"));
			foreach ($fftmp_parts as $fftmp_value)
			{
				$fftmp_subparts = explode("=", $fftmp_value);
				if ($fftmp_subparts[0] == "_ffq_")
					continue;
				if (!isset($_REQUEST[$fftmp_subparts[0]]))
					$_REQUEST[$fftmp_subparts[0]] = (count($fftmp_subparts) == 2 ? rawurldecode($fftmp_subparts[1]) : "");
				$fftmp_new_querystring .= $fftmp_subparts[0] . (count($fftmp_subparts) == 2 ? "=" . $fftmp_subparts[1] : "") . "&";
			}
			if ($fftmp_ffq)
			{
				$_SERVER["QUERY_STRING"] = $fftmp_new_querystring;
				unset($_REQUEST["_ffq_"]);
				unset($_GET["_ffq_"]);
			}
			unset($fftmp_new_querystring);
			unset($fftmp_parts);
			unset($fftmp_value);
			unset($fftmp_subparts);
		}

		// fix request_uri. can't use code above due to multiple redirects (es.: R=401 and ErrorDocument in .htaccess)
		if (strpos($_SERVER["REQUEST_URI"], "?") !== false)
		{
			$fftmp_requri_parts = explode("?", $_SERVER["REQUEST_URI"]);
			if (strlen($fftmp_requri_parts[1]))
			{
				$fftmp_new_querystring = "";
				$fftmp_parts = explode("&", rtrim($fftmp_requri_parts[1], "&"));
				foreach ($fftmp_parts as $fftmp_value)
				{
					$fftmp_subparts = explode("=", $fftmp_value);
					if ($fftmp_subparts[0] == "_ffq_")
						continue;
					$fftmp_new_querystring .= $fftmp_subparts[0] . (count($fftmp_subparts) == 2 ? "=" . $fftmp_subparts[1] : "") . "&";
				}

				$_SERVER["REQUEST_URI"] = $fftmp_requri_parts[0] . "?" . $fftmp_new_querystring;

				unset($fftmp_new_querystring);
				unset($fftmp_parts);
				unset($fftmp_value);
				unset($fftmp_subparts);
			}
			unset($fftmp_requri_parts);
		}

		$realPathInfo = $_SERVER['PATH_INFO'];

		if(substr($realPathInfo, 0, 1) !== "/"
			&& array_key_exists('REDIRECT_URL', $_SERVER)
			&& FF_DISK_PATH . $_SERVER['REDIRECT_URL'] != __FILE__
		) {
			if(strpos(FF_DISK_PATH . $_SERVER['REDIRECT_URL'], __FILE__) === 0) {
				$realPathInfo    = substr(FF_DISK_PATH . $_SERVER['REDIRECT_URL'], strlen(__FILE__));
			} else {
				$realPathInfo    = $_SERVER['REDIRECT_URL'];
			}

			$arr_query_string = explode("&", $_SERVER['REDIRECT_QUERY_STRING']);
			if(is_array($arr_query_string) && count($arr_query_string)) {
				foreach($arr_query_string AS $arr_query_string_value) {
					$arr_query_string_data = explode("=", $arr_query_string_value);
					if(is_array($arr_query_string_data) && count($arr_query_string_data)) {
						$_REQUEST[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
						$_GET[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
					}
				}
			}
		}
	}

	if(strpos($realPathInfo, $_SERVER["SCRIPT_NAME"]) === 0)
		$realPathInfo = substr($realPathInfo, strlen($_SERVER["SCRIPT_NAME"]));

	$sync = false;
	$sync_rev = false;
	if(strpos($realPathInfo, "/sync") === 0) {
		$realPathInfo = substr($realPathInfo, strlen("/sync"));

		if(defined("DOMAIN_SYNC_NAME"))
			$sync = true;

		if(DOMAIN_SYNC_NAME == REMOTE_HOST)
			$sync_rev = true;
	}

	$exceptions = updater_check_permission($sync, ($externals ? $realPathInfo : false));
	return array(
		"sync" 				=> $sync
		, "sync_rev" 		=> $sync_rev
		, "user_path" 		=> $realPathInfo
		, "invalid" 		=> (is_array($exceptions) ? false : $exceptions)
		, "exclude" 		=> $exceptions["exclude"]
		, "include" 		=> $exceptions["include"]
		, "db"				=> $db
	);
}


function updater_check_domain_client() {
	$db_updater = new ffDB_Sql();

	if(!REMOTE_HOST) {
		$denied_check = "remote host Empty";
		return $denied_check;
	}

	if(!defined("CM_TABLE_PREFIX")) {
		define("CM_TABLE_PREFIX", "cm_");
	}

	$sSQL = "SELECT IF(expiration_date >= CURDATE() OR expiration_date = '0000-00-00', 1, 0) AS date_check
                        , " . CM_TABLE_PREFIX . "mod_security_domains.nome
                        , " . CM_TABLE_PREFIX . "mod_security_domains.ID
                    FROM " . CM_TABLE_PREFIX . "mod_security_domains 
                    WHERE FIND_IN_SET(" . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ", ip_address)";
	$db_updater->query($sSQL);
	if($db_updater->nextRecord()) {
		$denied_check = "different_host";
		do {
			define("REAL_REMOTE_HOST", $db_updater->getField("nome", "Text", true));
			if(REAL_REMOTE_HOST == REMOTE_HOST) {
				if($db_updater->getField("date_check", "Number", true)) {
					$manifesto 						= array();
					$fs_master_exclude 				= array();
					$db_master_exclude 				= array();
					$db_master_exclude_prefix 		= array();
					$db_master_include 				= array();

					$ID_domain 						= $db_updater->getField("ID", "Number", true);
					require_once("manifesto.php");

					if(is_array($manifesto) && count($manifesto)) {
						$str_manifesto = "";
						foreach($manifesto AS $manifesto_key => $manifesto_value) {
							if(strlen($manifesto_key)) {
								if(strlen($str_manifesto))
									$str_manifesto .= ",";

								$str_manifesto .= "'" . $manifesto_key . "'";
							}
						}
						if(strlen($str_manifesto)) {
							$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains_fields.*
                                            FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_updater->toSql($ID_domain, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field IN (" . $str_manifesto . ")";
							$db_updater->query($sSQL);
							if($db_updater->nextRecord()) {
								do {
									$manifesto[$db_updater->getField("field", "Text", true)]["enable"] = $db_updater->getField("value", "Number", true);
								} while($db_updater->nextRecord());
							}
						}

						foreach($manifesto AS $manifesto_key => $manifesto_value) {
							if(!$manifesto_value["enable"]) {
								if(is_array($manifesto_value["path"])) {
									foreach($manifesto_value["path"] AS $path_value) {
										$fs_master_exclude[$path_value] = true;
									}
								} else {
									if(strlen($manifesto_value["path"])) {
										$fs_master_exclude[$manifesto_value["path"]] = true;
									}
								}
							}
							if(is_array($manifesto_value["db"])) {
								if($manifesto_value["enable"]) {
									if(is_array($manifesto_value["db"]["data"])) {
										foreach($manifesto_value["db"]["data"] AS $db_key => $db_value) {
											if(strlen($db_value)) {
												$db_master_include["basic"][$db_key] = $db_value;
											}
										}
									}
								} else {
									if(is_array($manifesto_value["db"]["table_prefix"])) {
										foreach($manifesto_value["db"]["table_prefix"] AS $db_value) {
											if(strlen($db_value)) {
												$db_master_exclude_prefix[] = $db_value;
											}
										}
									} else {
										if(strlen($manifesto_value["db"]["table_prefix"])) {
											$db_master_exclude_prefix[] = $manifesto_value["db"]["table_prefix"];
										}
									}
									if(is_array($manifesto_value["db"]["tables"])) {
										foreach($manifesto_value["db"]["tables"] AS $db_value) {
											if(strlen($db_value)) {
												$db_master_exclude[$db_value] = true;
											}
										}
									} else {
										if(strlen($manifesto_value["db"]["tables"])) {
											$db_master_exclude[$manifesto_value["db"]["tables"]] = true;
										}
									}
								}
							}
						}
					}

					$denied_check = array(
						"exclude" => array(
							"fs" => $fs_master_exclude
							, "db" => $db_master_exclude
							, "db_prefix" => $db_master_exclude_prefix
						)
						, "include" => array(
							"db" => $db_master_include
						)
					);
				} else {
					$denied_check = "expire_date";
				}
				break;
			}
		} while($db_updater->nextRecord());

		if($denied_check == "different_host") {
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.*
                			FROM " . CM_TABLE_PREFIX . "mod_security_domains
                			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql(REMOTE_HOST);
			$db_updater->query($sSQL);
			if($db_updater->numRows() == 1 && $db_updater->nextRecord()) {
				$ID_domain = $db_updater->getField("ID", "Number", true);
				$ip_address = $db_updater->getField("ip_address", "Text", true);
				$arrAddress = array_flip(explode(",", $ip_address));
				$arrAddress[$_SERVER["REMOTE_ADDR"]] = true;
				$new_address = implode(",", array_filter(array_keys($arrAddress)));

				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains
						SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = " . $db_updater->toSql($new_address) . "
					WHERE " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . $db_updater->toSql($ID_domain, "Number");
				$db_updater->execute($sSQL);
			} else {
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
		                        (
		                            `ID` ,
		                            `nome` ,
		                            `owner` ,
		                            `company_name` ,
		                            `type` ,
		                            `creation_date` ,
		                            `expiration_date` ,
		                            `time_zone` ,
		                            `status` ,
		                            `billing_status` ,
		                            `ip_address`
		                        ) 
		                        VALUES 
		                        (
		                            NULL , 
		                            " . $db_updater->toSql(REMOTE_HOST, "Text") . ", 
		                            0, 
		                            '', 
		                            '0', 
		                            CURDATE(), 
		                            CURDATE(), 
		                            0, 
		                            0, 
		                            0, 
		                            " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
		                        )";
				$db_updater->execute($sSQL);
			}
			$denied_check = array();
		}
	} else {
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
            			FROM " . CM_TABLE_PREFIX . "mod_security_domains
            			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql(REMOTE_HOST, "Text") . "
            				AND (expiration_date >= CURDATE() OR expiration_date = '0000-00-00')";
		$db_updater->query($sSQL);
		if($db_updater->nextRecord()) {
			$ID_domain = $db_updater->getField("ID", "Number", true);
			$ip_address = $db_updater->getField("ip_address", "Text", true);
			$arrAddress = array_flip(explode(",", $ip_address));
			$arrAddress[$_SERVER["REMOTE_ADDR"]] = true;
			$new_address = implode(",", array_filter(array_keys($arrAddress)));

			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains
						SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = " . $db_updater->toSql($new_address) . "
					WHERE " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . $db_updater->toSql($ID_domain, "Number");
			$db_updater->execute($sSQL);
		} else {
			$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
	                        (
	                            `ID` ,
	                            `nome` ,
	                            `owner` ,
	                            `company_name` ,
	                            `type` ,
	                            `creation_date` ,
	                            `expiration_date` ,
	                            `time_zone` ,
	                            `status` ,
	                            `billing_status` ,
	                            `ip_address`
	                        ) 
	                        VALUES 
	                        (
	                            NULL , 
	                            " . $db_updater->toSql(REMOTE_HOST, "Text") . ", 
	                            0, 
	                            '', 
	                            '0', 
	                            CURDATE(), 
	                            CURDATE(), 
	                            0, 
	                            0, 
	                            0, 
	                            " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
	                        )";
			$db_updater->execute($sSQL);
		}
		$denied_check = array();
	}

	return $denied_check;
}


function updater_check_permission($sync, $user_path = false) {
	$res = null;
	if(defined("REMOTE_HOST")) {
		if($user_path) {
			$external = updater_check_external($user_path);
			if (is_array($external) && count($external)) {
				$master_site = $external["domain"];
				$repository_path = $external["path"];
				$repository_status = $external["status"];
				$is_master = false;
			} else {
				$res = "external_no_available";
			}
		} else {
			$master_site = MASTER_SITE;
			$is_master = (REMOTE_HOST && is_dir(FF_DISK_PATH . "/conf/gallery/mc") && DOMAIN_NAME != REMOTE_HOST);
		}

		if(!$res) {
			if ($master_site == DOMAIN_INSET
				|| $is_master
			) {
				define("MASTER_CONTROL", true);

				$res = updater_check_domain_client();
			} else {
				if (DOMAIN_NAME == REMOTE_HOST /*&& $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]*/) {
					$res = false;
				} elseif ($sync) {
					$res = false;
				} else {
					$res = "different_host (" . DOMAIN_NAME . " => " . REMOTE_HOST . ", " . $_SERVER["SERVER_ADDR"] . " => " . $_SERVER["REMOTE_ADDR"] . ")";
				}
			}
		}
	} else {
		$res = "remote_host_empty";
	}

	return $res;
}

function updater_get_fs($absolute_path, $fs_exclude = NULL) {
	static $fs = array();

	$relative_path = str_replace(FF_DISK_PATH, "", $absolute_path);

	if (is_dir($absolute_path)) {
		$handle = opendir($absolute_path);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != ".svn" && $file != ".git" && $file != CM_SHOWFILES_THUMB_PATH && $file != ".thumbs") {
					if(array_key_exists($relative_path . "/" . $file, $fs_exclude)
						&& !is_array($fs_exclude[$relative_path . "/" . $file])
						&& $fs_exclude[$relative_path . "/" . $file] == true
					) {
						continue;
					}
					if(array_key_exists($relative_path, $fs_exclude)
						&& is_array($fs_exclude[$relative_path])
						&& (
							(
								array_key_exists($file, $fs_exclude[$relative_path])
								&& (!is_array($fs_exclude[$relative_path][$file]) && $fs_exclude[$relative_path][$file] == true)
							)
							||
							!array_key_exists($file, $fs_exclude[$relative_path])
						)
					) {
						continue;
					}
					if (is_dir($absolute_path . "/" . $file)) {
						$fs[$relative_path . "/" . $file] = "-1";
						updater_get_fs($absolute_path . "/" . $file, $fs_exclude);
					} else {
						if(is_file($absolute_path . "/" . $file)) {
							$fs[$relative_path . "/" . $file] = filesize($absolute_path . "/" . $file);
						}
					}
				}
			}
		}
	} else {
		if(is_file($absolute_path)) {
			$fs[$relative_path] = filesize($absolute_path);
		}
	}
	return $fs;
}

function updater_resolve_rel_data($table, $key, $value, $db_include, $db) {  //da finire importante
	$sSQL_compare_field = "";
	$sSQL_compare = "";
	if(is_array($db_include[$table]["key"]) && count($db_include[$table]["key"])) {
		foreach($db_include[$table]["key"] AS $compare_key => $compare_value) {
			if(is_array($db_include[$table]["rel"]) && array_key_exists($compare_key, $db_include[$table]["rel"])) {
				if(strlen($sSQL_compare_field))
					$sSQL_compare_field .= " AND ";

				$sSQL_compare_field .= resolve_rel_data($db_include[$table]["rel"][$compare_key], $compare_key, $compare_value, $db_include, $db);
			} else {
				if(strlen($sSQL_compare_field))
					$sSQL_compare_field .= " AND ";

				$sSQL_compare_field .= " `" . $compare_key . "` = " . $db->toSql($compare_value);
			}
		}
		reset($db_include[$table]["key"]);
	}

	$sSQL_compare .= " `" . $key . "` = " . " 
	                        IFNULL(
						        ( 
							        SELECT IFNULL(`" . $table . "`.`ID`, 0) AS ID 
							        FROM `" . $table . "`
							        WHERE " . (strlen($sSQL_compare_field) ? $sSQL_compare_field : " ID = " . $value) . "
							        LIMIT 1
							     ) 
							     , 0
							)";

	return $sSQL_compare;
}

function updater_check_external($path) {
	$db = new ffDB_Sql();

	$sSQL = "SELECT updater_externals.domain, updater_externals.path, updater_externals.status FROM updater_externals WHERE LOCATE(updater_externals.path, " . $db->toSql($path, "Text") . ") > 0  AND updater_externals.status = " . $db->toSql("1", "Text");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$external["domain"] = $db->getField("domain", "Text", true);
		$external["path"] = $db->getField("path", "Text", true);
		$external["status"] = $db->getField("status", "Text", true);
		if($external["domain"] != DOMAIN_INSET) {
			return $external;
		} else {
			if(file_exists(FF_DISK_PATH . $external["path"]))
				return $external;
			else
				return false;
		}
	}
	return false;
}