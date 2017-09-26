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

define("REAL_PATH", "/conf/gallery");

$limit_operation = (isset($_REQUEST["lo"]) && strlen($_REQUEST["lo"])
						? $_REQUEST["lo"]
						: 200
					);
$nowarning = (isset($_REQUEST["nowarning"]) && strlen($_REQUEST["nowarning"])
						? $_REQUEST["nowarning"]
						: false
					);
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
                            $res = ($res and ftp_purge_dir($conn_id, $ftp_disk_path, $real_file, $local_disk_path));
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

if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH"))
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/path.php");

if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
    if (strpos(php_uname(), "Windows") !== false)
        $tmp_file = str_replace("\\", "/", __FILE__);
    else
        $tmp_file = __FILE__;

    if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($st_document_root, "", str_replace("/conf/gallery/updater/externals.php", "", $tmp_file));
		$st_disk_path = $st_document_root . $st_site_path;
	} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/updater/externals.php", "", $_SERVER["SCRIPT_FILENAME"]));
		$st_disk_path = $st_document_root . str_replace($st_document_root, "", str_replace("/conf/gallery/updater/externals.php", "", $tmp_file));
	} else {
		$st_disk_path = str_replace("/conf/gallery/updater/externals.php", "", $tmp_file);
		$st_site_path = str_replace("/conf/gallery/updater/externals.php", "", $_SERVER["SCRIPT_NAME"]);
	}

    define("FF_SITE_PATH", $st_site_path);
    define("FF_DISK_PATH", $st_disk_path);
}

if(is_object($cm)) {
	$pathInfo = $cm->path_info;
	$realPathInfo = $cm->real_path_info;

	if($pathInfo == VG_RULE_UPDATER) { 
		if (!AREA_UPDATER_SHOW_MODIFY) {
			ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
		}
	}
	
	$cm->oPage->form_method = "post";
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
	//$page_path = "/conf/gallery/updater/check";
}

if(strpos($realPathInfo, $_SERVER["SCRIPT_NAME"]) === 0)
	$realPathInfo = substr($realPathInfo, strlen($_SERVER["SCRIPT_NAME"]));

	
if(!defined("MASTER_SITE"))
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php");

if(!defined("FF_DATABASE_NAME")) {
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/db.php");
	require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__)))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
	$db =  new ffDB_Sql;
} else {
	$db = ffDB_Sql::factory();	
}

if(!defined("DOMAIN_INSET"))
	define("DOMAIN_INSET", $_SERVER["HTTP_HOST"]);

//require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php");		

if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	$json = true;
} else {
	$json = $_REQUEST["json"];
}
$execute = $_REQUEST["exec"];

$file_get_contents_master_failed = array();
$file_get_contents_slave_failed = array();

if(class_exists("ffTemplate")) {
	$delete_label = ffTemplate::_get_word_by_code("delete_file");
	$update_label = ffTemplate::_get_word_by_code("update_file");
	$addnew_label = ffTemplate::_get_word_by_code("addnew_file");
	
	$creation_dir_label = ffTemplate::_get_word_by_code("creation_failure_directory");
	$upload_file_label = ffTemplate::_get_word_by_code("upload_failure_file");
	$delete_file_label =  ffTemplate::_get_word_by_code("delete_failure_file");
	$delete_dir_label =  ffTemplate::_get_word_by_code("delete_failure_dir");
	$ftp_unavaible_label = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
	$ftp_access_label = ffTemplate::_get_word_by_code("ftp_access_denied");
	$ftp_connection_label = ffTemplate::_get_word_by_code("ftp_connection_failure");
	$ftp_configuration_label = ffTemplate::_get_word_by_code("ftp_not_configutated");
	
	$wrong_source_data_label = ffTemplate::_get_word_by_code("wrong_source_data");
	$server_is_repository_label = ffTemplate::_get_word_by_code("server_is_repository");
    $external_no_available_label = ffTemplate::_get_word_by_code("external_no_available");

    $file_get_contents_master_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_master_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    $file_get_contents_master_failed["external_no_available"] = $external_no_available_label;

    $file_get_contents_slave_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_slave_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    $file_get_contents_slave_failed["external_no_available"] = $external_no_available_label;

    $unknown = ffTemplate::_get_word_by_code("unknown");
    $restrictions_in_effect_master = ffTemplate::_get_word_by_code("restrictions_in_effect_master");
    $restrictions_in_effect_slave = ffTemplate::_get_word_by_code("restrictions_in_effect_slave");
} else {
	$delete_label = "Delete file";
	$update_label = "Update file";
	$addnew_label = "Addnew file";
	
	$creation_dir_label = "Creation failure directory";
	$upload_file_label = "Upload failure file";
	$delete_file_label = "Delete failure file";
	$delete_dir_label =  "Delete failure dir";
	$ftp_unavaible_label = "Ftp unavaible root dir";
	$ftp_access_label = "Ftp access denied";
	$ftp_connection_label = "Ftp connection failure";
	$ftp_configuration_label = "Ftp not configutated";
	
	$wrong_source_data_label = "Wrong source_data";
	$server_is_repository_label = "Server is repository";
    $external_no_available_label = "External no available";

    $file_get_contents_master_failed["expire_date"] = "Expire Date";
    $file_get_contents_master_failed["different_host"] = "Different Host";
    $file_get_contents_master_failed["external_no_available"] = $external_no_available_label;

    $file_get_contents_slave_failed["expire_date"] = "Expire Date";
    $file_get_contents_slave_failed["different_host"] = "Different Host";
    $file_get_contents_slave_failed["external_no_available"] = $external_no_available_label;

    $unknown = "Unknown";
    $restrictions_in_effect_master = "restrictions_in_effect_master";
    $restrictions_in_effect_slave = "restrictions_in_effect_slave";
}
@set_time_limit(0);

$json = $_REQUEST["json"];
$execute = $_REQUEST["exec"];

$count_operation = 0;
define("LIMIT_OPERATION", $limit_operation);

$external = check_external($realPathInfo, $db);
if(is_array($external) && count($external)) {
    $master_site = $external["domain"];
    $repository_path = $external["path"];
    $repository_status = $external["status"];

	if($master_site != DOMAIN_INSET) {
		$external_path = $realPathInfo;
		
	    $json_master = @file_get_contents("http://" . $master_site . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET));
        if($json_master === false && strpos($master_site, "www.") === false) {
            $json_master = @file_get_contents("http://www." . $master_site . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET));
        }

	    if(strlen($json_master))
	        $arr_master = json_decode($json_master, true);

	    if(is_array($arr_master) && count($arr_master)) {
			if(defined("AUTH_USERNAME") && strlen(AUTH_USERNAME) && defined("AUTH_PASSWORD") && strlen(AUTH_PASSWORD)) {
				$context = stream_context_create(array(
					"ssl"=>array(
						"verify_peer" => false,
						"verify_peer_name" => false,
					)
					, 'http' => array(
						'header'  => "Authorization: Basic " . base64_encode(AUTH_USERNAME . ":" . AUTH_PASSWORD)
						, 'method' => 'GET'
						, 'timeout' => 120 //<---- Here (That is in seconds)						
					)
				));

		        $json_slave = @file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET), false, $context);
	            if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
	                $json_slave = @file_get_contents("http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode("www." . DOMAIN_INSET), false, $context);
	            }
			} else {	
				$context = stream_context_create(array(
					"ssl"=>array(
						"verify_peer" => false,
						"verify_peer_name" => false,
					)
					, 'http' => array(
						'method' => 'GET'
						, 'timeout' => 120 //<---- Here (That is in seconds)
					)
				));				    
		        $json_slave = @file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET), false, $context);
	            if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
	                $json_slave = @file_get_contents("http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode("www." . DOMAIN_INSET), false, $context);
	            }
			}
	        if(strlen($json_slave))
	            $arr_slave = json_decode($json_slave, true);

	        if(is_array($arr_slave)) {
	            $operation = array();
	            
	            krsort($arr_slave);
	            foreach($arr_slave AS $file_key => $file_value) {
	                if(!isset($arr_master[$file_key])) {
	                    $strAction = $file_key;
	                    $operation[] = array("data" => $delete_label
	                                                , "action" => "delete"
	                                                , "value" => $strAction
	                                                , "size" => $file_value
	                                            ); 
	                } else {
	                    if($arr_master[$file_key] != $file_value) {
	                        $strAction = $file_key;
	                        $operation[] = array("data" => $update_label
	                                                    , "action" => "update"
	                                                    , "value" => $strAction
	                                                    , "size" => $file_value
	                                                ); 
	                    }
	                }
	            }
	            reset($arr_slave);
	            
	            ksort($arr_master);
	            foreach($arr_master AS $file_key => $file_value) {
	                if(!isset($arr_slave[$file_key])) {
	                    $strAction = $file_key;
	                    $operation[] = array("data" => $addnew_label
	                                                , "action" => "addnew"
	                                                , "value" => $strAction
	                                                , "size" => $file_value
	                                            ); 
	                } else {
	                    if($arr_master[$file_key] != $file_value) {
	                        $strAction = $file_key;
	                        $operation[] = array("data" => $update_label
	                                                    , "action" => "update"
	                                                    , "value" => $strAction
	                                                    , "size" => $file_value
	                                                ); 
	                    }
	                }
	            }
	            reset($arr_master);
	            
	            if($json) {
	                if($execute) {
	                    if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	                        // set up basic connection
	                        /*$conn_id = @ftp_connect(DOMAIN_INSET);
                            if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
                                $conn_id = @ftp_connect("www." . DOMAIN_INSET);
                            }*/
							$conn_id = @ftp_connect("localhost");
					        if($conn_id === false)
        						$conn_id = @ftp_connect("127.0.0.1");
							if($conn_id === false)
        						$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
							
	                        if($conn_id !== false) {
	                            // login with username and password
	                            if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
	                                $local_path = FF_DISK_PATH;
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
                                    	
	                                if($real_ftp_path !== NULL) {
                                    	$criticalError = false;
                                    	$arrOperationError = array();
	                                    foreach($operation AS $key => $value) {
	                                        if(LIMIT_OPERATION > 0 && $count_operation >= LIMIT_OPERATION) {
	                                            break;
	                                        }
	                                        
	                                        switch($operation[$key]["action"]) {
	                                            case "addnew":
	                                                $part_path = "";
	                                                foreach(explode("/", ffCommon_dirname($operation[$key]["value"])) AS $tmp_path) {
	                                                    if(strlen($tmp_path)) {
	                                                        $part_path .= "/" . $tmp_path;
	                                                        
	                                                        if(!is_dir(FF_DISK_PATH . $part_path)) {
	                                                            if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
	                                                                $arrOperationError[] = array("data" => $creation_dir_label, "value" => $real_ftp_path . $part_path);
	                                                        }
	                                                    }
	                                                }

	                                                if($operation[$key]["size"] < 0) {
	                                                    if(!@ftp_mkdir($conn_id, $real_ftp_path . $operation[$key]["value"]))
	                                                        $arrOperationError[] = array("data" => $creation_dir_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                } else {
	                                                    $ret = @ftp_nb_put($conn_id
	                                                                        , $real_ftp_path . $operation[$key]["value"]
	                                                                        , "http://" . $master_site . REAL_PATH . "/updater/check/external.php" . str_replace("%2F", "/", rawurlencode($operation[$key]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
	                                                                        , FTP_BINARY
	                                                                        , FTP_AUTORESUME
	                                                                    );

	                                                    while ($ret == FTP_MOREDATA) {
	                                                       
	                                                       // Do whatever you want
	                                                       // Continue uploading...
	                                                       $ret = @ftp_nb_continue($conn_id);
	                                                    }
	                                                    if ($ret != FTP_FINISHED) {
	                                                       $arrOperationError[] = array("data" => $upload_file_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                    }
	                                                }
	                                                break;
	                                            case "update":
	                                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operation[$key]["value"], FF_DISK_PATH)) {
	                                                    $arrOperationError[] = array("data" => $delete_file_label, "value"  => $real_ftp_path . $operation[$key]["value"]);
														$criticalError = true;
														break;
													}
	                                                $ret = @ftp_nb_put($conn_id
	                                                                    , $real_ftp_path . $operation[$key]["value"]
	                                                                    , "http://" . $master_site . REAL_PATH . "/updater/check/external.php" . str_replace("%2F", "/", rawurlencode($operation[$key]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
	                                                                    , FTP_BINARY
	                                                                    , FTP_AUTORESUME
	                                                                );

	                                                while ($ret == FTP_MOREDATA) {
	                                                   
	                                                   // Do whatever you want
	                                                   // Continue uploading...
	                                                   $ret = @ftp_nb_continue($conn_id);
	                                                }
	                                                if ($ret != FTP_FINISHED) {
	                                                   $arrOperationError[] = array("data" => $upload_file_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                }
	                                                break;
	                                            case "delete":
	                                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operation[$key]["value"], FF_DISK_PATH))
	                                                    $arrOperationError[] = array("data" => $delete_dir_label, "value" => $real_ftp_path . $operation[$key]["value"]);

	                                                break;
	                                            default:            
	                                        }

											if($criticalError) {
                                            	if($count_operation) {
                                                    $strError = "";
                                                    $strInfo = "Max Connections: " . $count_operation;
                                                    $limit_operation = $count_operation;
                                                    $arrOperationError = array_values($operation);
												}
                                            	break;
                                            } 

	                                        unset($operation[$key]);
	                                        
	                                        $count_operation++; 
	                                    }
	                                    reset($operation);
	                                } else {
	                                    $strError = $ftp_unavaible_label;
	                                }
	                            } else {
	                                $strError = $ftp_access_label;
	                            }
	                        } else {
	                            $strError = $ftp_connection_label;
	                        }
	                        // close the connection and the file handler
	                        @ftp_close($conn_id);
	                    } else {
	                        $strError = $ftp_configuration_label;
	                    }

	                    if(strlen($strError)) {
                            echo json_encode(array("record" => $arrOperationError, "error" => $strError, "limit" => $limit_operation));
                        } else {
                            echo json_encode(array("record" => array_values($operation), "info" => $strInfo, "limit" => $limit_operation));
	                    }
	                } else {
	                    echo json_encode(array("record" => $operation));
	                }
	                exit;
	            }

	            $sSQL = "";
	            if(is_array($operation) && count($operation)) {
	                foreach($operation AS $operation_key => $operation_value) {
	                    if(strlen($sSQL)) 
	                        $sSQL .= " UNION ";
	                    $sSQL .= " ( SELECT '0' AS `check`
	                            , " . $db->toSql(new ffData($operation_key, "Number")) . " AS `ID`
	                            , " . $db->toSql(new ffData($operation_value["data"], "Text")) . " AS `operation`
	                            , " . $db->toSql(new ffData($operation_value["value"], "Text")) . " AS `subject` ) ";
	                }
	                reset($operation);
					if(class_exists("ffGrid")) {
		                $oGrid = ffGrid::factory($cm->oPage);
		                $oGrid->id = "UpdaterCheck";
		                $oGrid->title = ffTemplate::_get_word_by_code("updater_title");
		                $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER]";
		                $oGrid->order_default = "ID";
		                $oGrid->display_edit_bt = false;
		                $oGrid->display_edit_url = false;
		                $oGrid->display_delete_bt = false;
		                $oGrid->display_new = false;
		                $oGrid->addEvent("on_do_action", "UpdaterCheck_on_do_action");
		                $oGrid->use_paging = true;
		                $oGrid->default_records_per_page = 200;
		                $oGrid->user_vars["operations"] = $operation;

		                // Campi chiave
		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "ID";
		                $oField->base_type = "Number";
		                $oGrid->addKeyField($oField);

		                // Campi visualizzati
		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "check";
		                $oField->label = ffTemplate::_get_word_by_code("updater_check");
		                $oField->control_type = "checkbox";
		                $oField->extended_type = "Boolean";
		                $oField->checked_value = new ffData("1");
		                $oField->unchecked_value = new ffData("0");
		                $oGrid->addContent($oField);

		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "operation";
		                $oField->label = ffTemplate::_get_word_by_code("updater_operation");
		                $oGrid->addContent($oField);

		                $oField = ffField::factory($cm->oPage);
		                $oField->id = "subject";
		                $oField->label = ffTemplate::_get_word_by_code("updater_subject");
		                $oGrid->addContent($oField);

		                $oButton = ffButton::factory($cm->oPage);
		                $oButton->id = "check_all";
		                $oButton->action_type = "gotourl";
		                $oButton->url = "#";
	                        $oButton->aspect = "link";
		                $oButton->label = ffTemplate::_get_word_by_code("updater_check_all");
		                $oButton->properties["onclick"] = 'if(jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\') == false) { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'checked\'); } else { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'\'); }';
		                $oGrid->addActionButton($oButton);

		                $oButton = ffButton::factory($cm->oPage);
		                $oButton->id = "update";
		                $oButton->action_type = "submit";
		                $oButton->frmAction = "update";
	                        $oButton->aspect = "link";
		                $oButton->label = ffTemplate::_get_word_by_code("updater_execute");
		                $oGrid->addActionButton($oButton);

		                $oButton = ffButton::factory($cm->oPage);
		                $oButton->id = "cancel";
		                $oButton->action_type = "gotourl";
		                $oButton->url = "[RET_URL]";
	                        $oButton->aspect = "link";
		                $oButton->label = ffTemplate::_get_word_by_code("updater_cancel");
		                $oGrid->addActionButton($oButton);

		                $cm->oPage->addContent($oGrid);
					} else {
						print_r($operation);
						exit;
					}
	            } else {
                    if(function_exists("ffRedirect")) {
                        ffRedirect(urldecode($_REQUEST["ret_url"]));
					} else {
                        header("Location: " . urldecode($_REQUEST["ret_url"]));
                        exit;
					}
	            }
	        } else {
	        	$strError = $wrong_source_data_label . " (" . (!is_array($arr_slave) && isset($file_get_contents_slave_failed[$json_slave]) ? $file_get_contents_slave_failed[$json_slave] : (strlen($json_slave) ? $json_slave : $restrictions_in_effect_slave)) . ")";
	        }
	    } else {
	        $strError = $wrong_source_data_label . " (" . (!is_array($arr_master) && isset($file_get_contents_master_failed[$json_master]) ? $file_get_contents_master_failed[$json_master] : (strlen($json_master) ? $json_master : $restrictions_in_effect_master)) . ")";
	    }
	} else {
		if(!$nowarning)
			$strError = $server_is_repository_label;
	}
} else {
	if(!$nowarning)
		$strError = $external_no_available_label;
}


if($strError) {
    if($json) {
        echo json_encode(array("record" => array(), "error" => $strError));
        exit;
    } elseif(is_object($cm)) {
        $cm->oPage->fixed_pre_content = $strError;
    } else {
    	echo $strError;
    	exit;
    }
} else {
	echo json_encode(array("record" => array()));
	exit;	
}
 
function UpdaterCheck_on_do_action($component, $action) {
    $operations = $component->user_vars["operations"];
    
    if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
        // set up basic connection
        /*$conn_id = @ftp_connect(DOMAIN_INSET);
        if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
            $conn_id = @ftp_connect("www." . DOMAIN_INSET);
        }*/
		$conn_id = @ftp_connect("localhost");
	    if($conn_id === false)
        	$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false)
	        $conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

        if($conn_id !== false) {
            // login with username and password
            if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
                $local_path = FF_DISK_PATH;
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
                    
                if($real_ftp_path !== NULL) {
                    foreach($component->recordset_keys AS $key => $value) {
                        switch($operations[$value["ID"]]["action"]) {
                            case "addnew":
                                $part_path = "";
                                foreach(explode("/", ffCommon_dirname($operations[$value["ID"]]["value"])) AS $tmp_path) {
                                    if(strlen($tmp_path)) {
                                        $part_path .= "/" . $tmp_path;
                                        
                                        if(!is_dir(FF_DISK_PATH . $part_path)) {
                                            if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
                                                $strError .= $creation_dir_label . " (" . $real_ftp_path . $part_path . ")" . "<br>";
                                        }
                                    }
                                }

                                if($operations[$value["ID"]]["size"] < 0) {
                                    if(!@ftp_mkdir($conn_id, $real_ftp_path . $operations[$value["ID"]]["value"]))
                                        $strError .= $creation_dir_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                } else {
                                    $ret = @ftp_nb_put($conn_id
                                                        , $real_ftp_path . $operations[$value["ID"]]["value"]
                                                        , "http://" . $master_site . "/updater/check/external" . str_replace("%2F", "/", rawurlencode($operations[$value["ID"]]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
                                                        , FTP_BINARY
                                                        , FTP_AUTORESUME
                                                    );

                                    while ($ret == FTP_MOREDATA) {
                                       
                                       // Do whatever you want
                                       // Continue uploading...
                                       $ret = @ftp_nb_continue($conn_id);
                                    }
                                    if ($ret != FTP_FINISHED) {
                                       $strError .= $upload_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                    }
                                }
                                break;
                            case "update":
                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operations[$value["ID"]]["value"], FF_DISK_PATH))
                                    $strError .= $delete_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                
                                $ret = @ftp_nb_put($conn_id
                                                    , $real_ftp_path . $operations[$value["ID"]]["value"]
                                                    , "http://" . $master_site . "/updater/check/external" . str_replace("%2F", "/", rawurlencode($operations[$value["ID"]]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
                                                    , FTP_BINARY
                                                    , FTP_AUTORESUME
                                                );

                                while ($ret == FTP_MOREDATA) {
                                   
                                   // Do whatever you want
                                   // Continue uploading...
                                   $ret = @ftp_nb_continue($conn_id);
                                }
                                if ($ret != FTP_FINISHED) {
                                   $strError .= $upload_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                }
                                break;
                            case "delete":
                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operations[$value["ID"]]["value"], FF_DISK_PATH))
                                    $strError .= ffTemplate::_get_word_by_code("delete_failure") . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";

                                break;
                            default:            
                        }
                    }
                } else {
                    $strError = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
                }
            } else {
                $strError = ffTemplate::_get_word_by_code("ftp_access_denied");
            }
        } else {
            $strError = ffTemplate::_get_word_by_code("ftp_connection_failure");
        }
        // close the connection and the file handler
        @ftp_close($conn_id);
    } else {
        $strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
    }
    if(strlen($strError)) {
        $component->displayError($strError);
    } else {
        if(is_array($operations) && count($operations))
            ffRedirect($_SERVER["REQUEST_URI"]);
        else 
            ffRedirect($component->parent[0]->ret_url);
    }
}

function check_external($path, $db) {
	$sSQL = "SELECT updater_externals.domain, updater_externals.path, updater_externals.status FROM updater_externals WHERE LOCATE(updater_externals.path, " . $db->toSql($path, "Text") . ") > 0  AND updater_externals.status = " . $db->toSql("1", "Text");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$external["domain"] = $db->getField("domain", "Text", true);
		$external["path"] = $db->getField("path", "Text", true);
		$external["status"] = $db->getField("status", "Text", true);
		
		return $external;
	}
	return false;
}
