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
	error_reporting((E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED) | E_STRICT);
	ini_set("memory_limit", "300M"); 
    
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

    if(!defined("CM_TABLE_PREFIX")) {
        if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/cm/conf/config.php")) {
            require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/cm/conf/config.php");
        }
    }
    if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
        if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/path.php")) {
            require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/path.php");
        }
    }
    if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
        if (strpos(php_uname(), "Windows") !== false)
            $tmp_file = str_replace("\\", "/", __FILE__);
        else
            $tmp_file = __FILE__;
        
        if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
            $st_document_root =  $_SERVER["DOCUMENT_ROOT"];
            if (substr($st_document_root,-1) == "/")
                $st_document_root = substr($st_document_root,0,-1);

            $st_site_path = str_replace($st_document_root, "", str_replace("/conf/gallery/updater/check/db.php", "", $tmp_file));
            $st_disk_path = $st_document_root . $st_site_path;
        } elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
            $st_document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
            if (substr($st_document_root,-1) == "/")
                $st_document_root = substr($st_document_root,0,-1);

            $st_site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/updater/check/db.php", "", $_SERVER["SCRIPT_FILENAME"]));
            $st_disk_path = $st_document_root . str_replace($st_document_root, "", str_replace("/conf/gallery/updater/check/db.php", "", $tmp_file));
		} else {
			$st_disk_path = str_replace("/conf/gallery/updater/check/db.php", "", $tmp_file);
			$st_site_path = str_replace("/conf/gallery/updater/check/db.php", "", $_SERVER["SCRIPT_NAME"]);
        }

        define("FF_SITE_PATH", $st_site_path);
        define("FF_DISK_PATH", $st_disk_path);
    }
		
    if(!defined("MASTER_SITE")) {
        if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php")) {
            require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php");
        } else {
            die();
        }
    }
	
	if(!defined("FF_DATABASE_NAME")) {
		require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/db.php");
		require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
		$db_updater =  new ffDB_Sql;
		$db_updater_detail =  new ffDB_Sql;
		$db_compare =  new ffDB_Sql;
	} else {
		$db_updater = ffDB_Sql::factory();
		$db_updater_detail = ffDB_Sql::factory();
		$db_compare = ffDB_Sql::factory();
	}
	
	if(!defined("DOMAIN_INSET"))
		define("DOMAIN_INSET", $_SERVER["HTTP_HOST"]);
/*
    if(substr_count(DOMAIN_INSET, ".") == 1) {
        define("DOMAIN_NAME"        , DOMAIN_INSET);
    } else {
        define("DOMAIN_NAME"        , substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1));
    }

    if(substr_count($_REQUEST["s"], ".") == 1) {
        $remote_host = $_REQUEST["s"];
    } else {
        $remote_host = substr(urldecode($_REQUEST["s"]), strpos(urldecode($_REQUEST["s"]), ".") + 1);
    }
*/

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

    if(strpos(strtolower(DOMAIN_INSET), "www.") === 0) {
        define("DOMAIN_NAME"        , substr(DOMAIN_INSET, 4));
    } else {
        define("DOMAIN_NAME"        , DOMAIN_INSET);
    }

    if(strpos($_REQUEST["s"], "www.") === 0) {
        $remote_host = substr($_REQUEST["s"], 4);
    } else {
        $remote_host = $_REQUEST["s"];
    }

	//require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php");
	
	if(is_object($cm)) {
		$realPathInfo = $cm->real_path_info;
	} else {
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

		if(DOMAIN_SYNC_NAME == $remote_host)
			$sync_rev = true;
    }
	
    if(strlen($remote_host)) {
        if(MASTER_SITE == DOMAIN_INSET || (MASTER_SITE != DOMAIN_INSET && is_dir(FF_DISK_PATH . "/conf/gallery/mc") && DOMAIN_NAME != $remote_host)) {
            $sSQL = "SELECT IF(expiration_date >= CURDATE() OR expiration_date = '0000-00-00', 1, 0) AS date_check
                        , " . CM_TABLE_PREFIX . "mod_security_domains.nome
                        , " . CM_TABLE_PREFIX . "mod_security_domains.ID
                    FROM " . CM_TABLE_PREFIX . "mod_security_domains 
                    WHERE FIND_IN_SET(" . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ", ip_address)";
            $db_updater->query($sSQL);
            if($db_updater->nextRecord()) {
            	$denied_check = "different_host";
                do {
                    $real_remote_host = $db_updater->getField("nome", "Text", true);
                    if($real_remote_host == $remote_host) {
                        if($db_updater->getField("date_check", "Number", true)) {
                            $ID_domain = $db_updater->getField("ID", "Number", true);
                            require_once(ffCommon_dirname(__FILE__) . "/manifesto.php");
                            
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
                                    if(is_array($manifesto_value["db"])) {
	                                    if($manifesto_value["enable"]) {
	                                        if(is_array($manifesto_value["db"]["data"])) {
                                        		foreach($manifesto_value["db"]["data"] AS $db_key => $db_value) {
                                                    if(strlen($db_value)) {
													    $db_master_include["basic"][$db_key] = $db_value;
                                                    }
												}
	                                        }
	                                        
	                                        if(is_array($manifesto_value["db"]["exclude"])) {
                                        		foreach($manifesto_value["db"]["exclude"] AS $db_value) {
                                                    if(strlen($db_value)) {
														$db_master_exclude[$db_value] = true;
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
                            
                            
                            $denied_check = false;
                        } else {
                            $denied_check = "expire_date";
                        }
                        break;
                    }
                } while($db_updater->nextRecord());

                if($denied_check == "different_host") {
                	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.*
                			FROM " . CM_TABLE_PREFIX . "mod_security_domains
                			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql($remote_host);
                	$db_updater->query($sSQL);
                	if($db_updater->numRows() == 1 && $db_updater->nextRecord()) {
                		$ID_domain = $db_updater->getField("ID", "Number", true);
                		
                		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains  
            						SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = IF(`ip_address` = ''
            									, " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
            									, CONCAT(`ip_address`, ',', " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ")
            							)
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
		                            " . $db_updater->toSql($remote_host, "Text") . ", 
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
	                $denied_check = false;
				}
            } else {
                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
            			FROM " . CM_TABLE_PREFIX . "mod_security_domains
            			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql($remote_host, "Text") . "
            				AND (expiration_date >= CURDATE() OR expiration_date = '0000-00-00')";
            	$db_updater->query($sSQL);
            	if($db_updater->nextRecord()) {
            		$ID_domain = $db_updater->getField("ID", "Number", true);
            		
            		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains
            					SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = IF(`ip_address` = ''
            								, " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
            								, CONCAT(`ip_address`, ',', " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ")
            						)
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
	                            " . $db_updater->toSql($remote_host, "Text") . ", 
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
                $denied_check = false;
            }
        } else {
            if(DOMAIN_NAME == $remote_host /*&& $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]*/) { 
                $denied_check = false;        
            } elseif($sync) {
            	$denied_check = false;
            } else {
                $denied_check = "different_host (" . DOMAIN_NAME . " => " . $remote_host . ", " . $_SERVER["SERVER_ADDR"] . " => " . $_SERVER["REMOTE_ADDR"] . ")";
            }
        }
    } else {
        $denied_check = "remote_host_empty";
    }

    if(!$denied_check) {
        @set_time_limit(0);
        
        $contest = $_REQUEST["contest"];
        if(isset($_REQUEST["t"]))
            $tbl["name"] = $_REQUEST["t"];
        if(isset($_REQUEST["l"]))
            $tbl["limit"] = $_REQUEST["l"];

        $mode = $_REQUEST["mode"];

        $db_res = array();
        $db_exclude = array();
        $db_exclude_prefix = array();
        
        if(is_array($db_master_exclude) && count($db_master_exclude)) {
            $db_exclude = array_merge($db_exclude, $db_master_exclude);
        }
        
        if(is_array($db_master_exclude_prefix) && count($db_master_exclude_prefix)) {
            $db_exclude_prefix = array_merge($db_exclude_prefix, $db_master_exclude_prefix);
        }
        
        $sSQL = "SHOW TABLES";   
        $db_updater->query($sSQL);
        if($db_updater->nextRecord()) {
            switch (basename($realPathInfo)) {
                case "structure":
                    do {
                        $skip_table = false;
                        if(array_key_exists($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $db_exclude)) {
                        	$skip_table = true;
                        }

                        if(!$skip_table && is_array($db_exclude_prefix) && count($db_exclude_prefix)) {
                            foreach($db_exclude_prefix AS $exclue_prefix_value) {
                                if(strlen($exclue_prefix_value) 
                                    && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $exclue_prefix_value) === 0
                                ) {
                                    $skip_table = true;
                                    break;
                                }
                            }
                        }
                        
                        if(!$skip_table && $real_remote_host == $remote_host && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "cm_mod_") === 0) {
                        	$arrTableModule = explode("_", substr($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), strlen("cm_mod_")));
                        	if(!is_dir(FF_DISK_PATH . "/modules/" . $arrTableModule[0])) {
								$skip_table = true;
                        	}
                        }
                        
                        if(!$skip_table) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            
                            $sSQL = "DESCRIBE `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Field", "Text", true)] = array(
                                        "Type" => $db_updater_detail->getField("Type", "Text", true)
                                        , "Null" => $db_updater_detail->getField("Null", "Text", true)
                                        , "Key" => ($db_updater_detail->getField("Key", "Text", true) != "PRI" ? "" : $db_updater_detail->getField("Key", "Text", true))
                                        , "Default" => ($db_updater_detail->getField("Default", "Text", true) === NULL ? "" : $db_updater_detail->getField("Default", "Text", true))
                                        , "Extra" => $db_updater_detail->getField("Extra", "Text", true)
                                    );
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    break;
                case "indexes":
                    do {
                        $skip_table = false;
                        if(array_key_exists($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $db_exclude)) {
                            $skip_table = true;
                        }

                        if(!$skip_table && is_array($db_exclude_prefix) && count($db_exclude_prefix)) {
                            foreach($db_exclude_prefix AS $exclue_prefix_value) {
                                if(strlen($exclue_prefix_value) 
                                    && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $exclue_prefix_value) === 0
                                ) {
                                    $skip_table = true;
                                    break;
                                }
                            }
                        }
                        
                        if(!$skip_table && $real_remote_host == $remote_host && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "cm_mod_") === 0) {
                        	$arrTableModule = explode("_", substr($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), strlen("cm_mod_")));
                        	if(!is_dir(FF_DISK_PATH . "/modules/" . $arrTableModule[0])) {
								$skip_table = true;
                        	}
                        }
                        
                        if(!$skip_table) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            
                            $sSQL = "SHOW INDEX FROM `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    if(isset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)])) {
                                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)]["Column_name"] .= ", " . "`" . $db_updater_detail->getField("Column_name", "Text", true) . "`";
                                    } else {
                                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)] = array(
                                            "Non_unique" => $db_updater_detail->getField("Non_unique", "Text", true)
                                            , "Key_name" => $db_updater_detail->getField("Key_name", "Text", true)
                                            , "Seq_in_index" => $db_updater_detail->getField("Seq_in_index", "Text", true)
                                            , "Column_name" => "`" . $db_updater_detail->getField("Column_name", "Text", true) . "`"
                                            , "Collation" => $db_updater_detail->getField("Collation", "Text", true)
                                            , "Sub_part" => $db_updater_detail->getField("Sub_part", "Text", true)
                                            , "Packed" => $db_updater_detail->getField("Packed", "Text", true)
                                            , "Null" => $db_updater_detail->getField("Null", "Text", true)
                                            , "Index_type" => $db_updater_detail->getField("Index_type", "Text", true)
                                            , "Comment" => $db_updater_detail->getField("Comment", "Text", true)
                                        );
                                    }
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    break;
                case "data":
                	require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/other.php");

                    require(ffCommon_dirname(__FILE__) . "/include_db.php"); 

		            if(is_array($db_master_include) && count($db_master_include)) {
		                $db_include = array_merge($db_include, $db_master_include);
		            }
                    
                    $db_rel = array();
                    do {
                        if(is_array($tbl) && count($tbl) && array_key_exists("name", $tbl) && strlen($tbl["name"])) {
                            if($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true) != $tbl["name"])
                                continue;
                        }
                        if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)])) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            if(is_array($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"]) && count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"])) {
								foreach($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"] AS $rel_key => $rel_value) {
									$db_rel[$rel_value] = true;
								}								
                            }
                            
                            $sSQL = "DESCRIBE `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                $column = array();
                                do {
                                    $column[] = $db_updater_detail->getField("Field", "Text", true);
                                } while($db_updater_detail->nextRecord());
                                sort($column);
                                reset($column);
                            }

                            $sSQL_table_sort = "";
                            $sSQL_table_limit = "";
                            if($sync) { 
                                if(is_array($tbl) && count($tbl) && array_key_exists("limit", $tbl) && $tbl["limit"] > 0) {
                                    $sSQL_table_limit = " LIMIT " . (($tbl["limit"] - 1) * 10000) . ", " . ($tbl["limit"] * 10000);
                                }
                                if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"])) {
                                    if(is_array($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"]) && count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"])) {
                                        foreach($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"] AS $compare_key => $compare_value) {
                                            if(strlen($sSQL_table_sort))
                                                $sSQL_table_sort .= ", "; 
                                            
                                            $sSQL_table_sort .= " `" . $compare_key . "` ";
                                        }
                                        reset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["compare"]);
                                    }
                                }                                    
                                if(!strlen($sSQL_table_sort)) {
                                    if(array_search("ID", $column) === false) {
                                        $sSQL_table_sort = "`" . $column[0] . "`";
                                    } else {
                                        $sSQL_table_sort = "`ID`";
                                    }
                                }
                                $sSQL_table_sort = " ORDER BY " . $sSQL_table_sort;
                            }  
                                                      
                            $sSQL = "SELECT * 
                                    FROM " . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false)  
                                    . (strlen($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"])
                                        ? " WHERE " .  $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"]
                                        : ""
                                    ) 
                                    . $sSQL_table_sort
                                    . $sSQL_table_limit;

                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    $record_key = "";
                                    $tmp_value = array();
                                    $tmp_res = array();
                                    
                                    foreach($column AS $column_value) {
                                        if($mode == "compact") {
                                            $tmp_value[$column_value] = "";
                                        } else {
                                            $tmp_value[$column_value] = $db_updater_detail->getField($column_value, "Text", true); 
                                        }
                                        
                                        if(
                                            (
                                                (
                                                    !count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"]) 
                                                &&
                                                    $column_value != "ID"
                                                )
                                            || 
                                                isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"][$column_value])
                                            )
                                            //&& !isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$db_updater_detail->getField($column_value, "Text", true)])
                                        ) {
                                        	if(strlen($record_key))
                                        		$record_key .= "-";
                                        		
                                            if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$column_value])) {
                                                $compare_tbl = $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$column_value];
                                                if(count($db_include[$contest][$compare_tbl]["key"])) {
                                                    $sSQL_compare = "";
                                                    $sSQL_compare_field = "";
                                                    foreach($db_include[$contest][$compare_tbl]["key"] AS $compare_key => $compare_value) {
                                                    	if(0 && is_array($db_include[$contest][$compare_tbl]["rel"]) && array_key_exists($compare_key, $db_include[$contest][$compare_tbl]["rel"])) {
															// da finire importante
	                                                        if(strlen($sSQL_compare_field))
	                                                            $sSQL_compare_field .= ", "; 

															$sSQL_compare_field .= resolve_rel_data($db_include[$contest][$compare_tbl]["rel"][$compare_key], $compare_key, $compare_value, $db_include[$contest], $db_compare);
                                                    	} else {
	                                                        if(strlen($sSQL_compare_field))
	                                                            $sSQL_compare_field .= ", "; 
	                                                        
	                                                        $sSQL_compare_field .= " `" . $compare_key . "` ";
                                                    	}
                                                    	
                                                    }
                                                    reset($db_include[$contest][$compare_tbl]["key"]);
                                                    
                                                    $sSQL_compare = "SELECT CONCAT(" . $sSQL_compare_field . ") AS compare FROM " . $compare_tbl . " WHERE `ID` = " . $db_compare->toSql($db_updater_detail->getField($column_value, "Text", true)); 
                                                    $db_compare->query($sSQL_compare);
                                                    if($db_compare->nextRecord())
                                                        $record_key .= $db_compare->getField("compare", "Text", true);
                                                } else {
                                                    $record_key .= $db_updater_detail->getField($column_value, "Text", true);
                                                }
                                            } else {
                                                $record_key .= $db_updater_detail->getField($column_value, "Text", true);
                                            }
                                        } 
                                    }
                                    //if(array_key_exists("record_" . md5($record_key), $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)])) {
									//	echo $tmp_value . "<br>";
                                    //}
                                    $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["R" . md5($record_key)] = $tmp_value;
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    
                    if(is_array($db_rel) && count($db_rel)) {
						foreach($db_res AS $db_res_key => $db_res_value) {
							if(array_key_exists($db_res_key, $db_rel)) {
								$db_rel[$db_res_key] = $db_res_value;
								unset($db_res[$db_res_key]);
							}
						}
						foreach($db_rel AS $db_rel_key => $db_rel_value) {
							if(!is_array($db_rel[$db_rel_key])) {
								unset($db_rel[$db_rel_key]);
							}
						}
						$db_res = array_merge($db_rel, $db_res); 
                    }
                    break; 
                default:
                    do {
                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();

                        $sSQL = "SELECT COUNT(*) AS count_record
                                FROM " . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false)  
                                . (strlen($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"])
                                    ? " WHERE " .  $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"]
                                    : ""
                                );

                        $db_updater_detail->query($sSQL);
                        if($db_updater_detail->nextRecord()) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["record"] = $db_updater_detail->getField("count_record", "Text", true);
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["column"] = count($column);
                            ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                        }

                    } while($db_updater->nextRecord());
            }
            //ksort($db_res);
            reset($db_res);
        }
      	
        if($mode == "compact") 
            echo md5(json_encode($db_res));
        else
            echo json_encode($db_res);
        
        exit; 
    } else {
        die($denied_check);
    }
    
function resolve_rel_data($table, $key, $value, $db_include, $db) {  //da finire importante
	$sSQL_compare_field = "";
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

