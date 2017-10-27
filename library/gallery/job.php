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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
//checker general
function get_check_fs($absolute_path, $fs_exclude = NULL, $reset = true, $perm = "777") {
    static $fs;
    
    if($reset)
        $fs = array();
    
    $relative_path = str_replace(FF_DISK_PATH, "", $absolute_path);
    
    if (is_dir($absolute_path)) {
        if ($handle = @opendir($absolute_path)) {
            while (false !== ($file = readdir($handle))) { 
                if ($file != "." && $file != ".." && $file != ".svn" && !isset($fs_exclude[$relative_path . "/" . $file])) { 
                    if (is_dir($absolute_path . "/" . $file)) {
                        clearstatcache();
                        if(substr(decoct( @fileperms($absolute_path . "/" . $file)), 2) == (is_array($perm) ? $perm["dir"] : $perm)) {
                            $fs[$relative_path . "/" . $file] = 1;
                        } else {
                            $fs[$relative_path . "/" . $file] = 0;
                        }
                        get_check_fs($absolute_path . "/" . $file, $fs_exclude, false);
                    } else {
                        if(is_file($absolute_path . "/" . $file)) {
                            clearstatcache();

                            if($perm == "777") {
                                if(substr(decoct( @fileperms($absolute_path . "/" . $file)), 3) == "666") {
                                    $addit_perm = true;
                                } else {
                                    $addit_perm = false;
                                }
                            } else {
                                $addit_perm = false;
                            }

                            if($addit_perm || substr(decoct( @fileperms($absolute_path . "/" . $file)), 3) == (is_array($perm) ? $perm["file"] : $perm)) {
                                $fs[$relative_path . "/" . $file] = filesize($absolute_path . "/" . $file);
                            } else {
                                $fs[$relative_path . "/" . $file] = 0;
                            }

                        }
                    }
                }
            }
        }
        clearstatcache();
        if(substr(decoct( @fileperms($absolute_path)), 2) == (is_array($perm) ? $perm["dir"] : $perm)) {
            $fs[$relative_path] = 1;
        } else {
            $fs[$relative_path] = 0;
        }
    } else {
        clearstatcache();

        if($perm == "777") {
            if(substr(decoct( @fileperms($absolute_path)), 3) == "666") {
                $addit_perm = true;
            } else {
                $addit_perm = false;
            }
        } else {
            $addit_perm = false;
        }

        if($addit_perm || substr(decoct( @fileperms($absolute_path)), 3) == (is_array($perm) ? $perm["file"] : $perm)) {
            $fs[$relative_path . "/" . $file] = filesize($relative_path . "/" . $file);
        } else {
            $fs[$relative_path . "/" . $file] = 0;
        }
    }
    return $fs;
}

function set_fs_by_ftp($conn_id, $real_ftp_path, $relative_path, $operation, $fs_exclude = NULL, $perm = "0777") {
    if (is_dir(FF_DISK_PATH . $relative_path)) {
        if ($handle = ftp_nlist($conn_id, $real_ftp_path . $relative_path)) {
            foreach($handle AS $file) {
                if (basename($file) != "." && basename($file) != ".." && basename($file) != ".svn" && !isset($fs_exclude[$relative_path . "/" . basename($file)])) { 
                    if (is_dir(FF_DISK_PATH . $relative_path . "/" . basename($file))) {
                        set_fs_by_ftp($conn_id, $real_ftp_path, $relative_path . "/" . basename($file), $operation, $fs_exclude, $perm);
                        if(!isset($fs_exclude[$relative_path . "/" . basename($file)])) {
                            if($operation == "chmod") {
                                $file_owner = @fileowner(FF_DISK_PATH . $relative_path . "/" . basename($file));
                                if($file_owner !== false) {
                                    //$arrInfo = posix_getpwuid($file_owner);
                                    if(0 /*&& $arrInfo["name"] !== FTP_USERNAME*/) {
                                        @ftp_chmod($conn_id,  0777, $file);
                                        @chmod(FF_DISK_PATH . $relative_path . "/" . basename($file), 0777);
                                    } else {
                                        @ftp_chmod($conn_id,  octdec((is_array($perm) ? $perm["dir"] : $perm)), $file);
                                        @chmod(FF_DISK_PATH . $relative_path . "/" . basename($file), octdec((is_array($perm) ? $perm["dir"] : $perm)));
                                    }
                                }
                            } elseif($operation == "delete") {
                                @ftp_rmdir($conn_id, $file);
                                @rmdir(FF_DISK_PATH . $relative_path . "/" . basename($file) );
                            }
                        }
                    } else {
                        if($operation == "chmod") {
                            $file_owner = @fileowner(FF_DISK_PATH . $relative_path . "/" . basename($file));
                            if($file_owner !== false) {
                                //$arrInfo = posix_getpwuid($file_owner);
                                if(0 /*&& $arrInfo["name"] !== FTP_USERNAME*/) {
                                    @ftp_chmod($conn_id, 0777, $file);
                                    @chmod(FF_DISK_PATH . $relative_path . "/" . basename($file), 0777);
                                } else {
                                    @ftp_chmod($conn_id, octdec((is_array($perm) ? $perm["file"] : $perm)), $file);
                                    @chmod(FF_DISK_PATH . $relative_path . "/" . basename($file), octdec((is_array($perm) ? $perm["file"] : $perm)));
                                }
                            }
                        } elseif($operation == "delete") {
                            @ftp_delete($conn_id, $file);
                            @unlink(FF_DISK_PATH . $relative_path . "/" . basename($file));
                        }
                    }
                }
            }
        } 
        if(!isset($fs_exclude[$relative_path])) {
            if($operation == "chmod") {
                $file_owner = @fileowner(FF_DISK_PATH . $relative_path);
                if($file_owner !== false) {
                    //$arrInfo = posix_getpwuid($file_owner);
                    if(0 /*&& $arrInfo["name"] !== FTP_USERNAME*/) {
                        @ftp_chmod($conn_id, 0777, $real_ftp_path . $relative_path);
                        @chmod(FF_DISK_PATH . $relative_path, 0777);
                    } else {
                        @ftp_chmod($conn_id, octdec((is_array($perm) ? $perm["dir"] : $perm)), $real_ftp_path . $relative_path);
                        @chmod(FF_DISK_PATH . $relative_path, octdec((is_array($perm) ? $perm["dir"] : $perm)));
                    }
                }
            } elseif($operation == "delete") {
                @ftp_rmdir($conn_id, $real_ftp_path . $relative_path);
                @rmdir(FF_DISK_PATH . $relative_path);
            }
        }
    } else {
        if(!isset($fs_exclude[$relative_path])) {
            if($operation == "chmod") {
                $file_owner = @fileowner(FF_DISK_PATH . $relative_path);
                if($file_owner !== false) {
                    //$arrInfo = posix_getpwuid($file_owner);
                    if(0 /*&& $arrInfo["name"] !== FTP_USERNAME*/) {
                        @ftp_chmod($conn_id, 0777, $real_ftp_path . $relative_path);
                        @chmod(FF_DISK_PATH . $relative_path, 0777);
                    } else {
                        @ftp_chmod($conn_id, octdec((is_array($perm) ? $perm["file"] : $perm)), $real_ftp_path . $relative_path);
                        @chmod(FF_DISK_PATH . $relative_path, octdec((is_array($perm) ? $perm["file"] : $perm)));
                    }
                }
            } elseif($operation == "delete") {
                @ftp_delete($conn_id, $real_ftp_path . $relative_path);
                @unlink(FF_DISK_PATH . $relative_path);
            }
        }
    }
    return false;
}

function set_fs($relative_path, $operation, $fs_exclude = NULL, $make_dir = NULL, $perm = "0777") {
    $strError = false;

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
                
                if($make_dir !== NULL) {
                	if(is_array($make_dir) && count($make_dir)) {
                		foreach($make_dir AS $make_dir_path) {
                			@ftp_mkdir($conn_id, $real_ftp_path . $make_dir_path);
						}
					}
				}
                    
                if($real_ftp_path !== NULL) {
                    $strError = set_fs_by_ftp($conn_id, $real_ftp_path, $relative_path, $operation, $fs_exclude, $perm);
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

    return $strError;
}

//cheker specific
function check_cache($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;
    
    check_function("get_literal_size");
    
	if($show_info) {
    	$db = ffDB_Sql::factory();
    	
		$sSQL = "SELECT ( SELECT count(*) FROM cache_page) AS cache_page, ( SELECT count(*) FROM cache_sid) AS cache_sid";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$check["info"] .= ffTemplate::_get_word_by_code("count_cache_page") . $db->getField("cache_page", "Number", true) . "<br>";
			$check["info"] .= ffTemplate::_get_word_by_code("count_cache_sid") . $db->getField("cache_sid", "Number", true) . "<br>";
		}
	}
   	if(is_dir(CM_CACHE_PATH . "")) {
		$fs = get_check_fs(CM_CACHE_PATH, array(ffTemplate::$_MultiLang_cache_path => true));
		
		if($show_info) {
			$arrDir = array("d" => 1);
			$tot_dir = count(array_intersect($fs, $arrDir));
			$tot_size = array_sum(array_diff($fs, $arrDir));
			
			$check["info"] .= "<br>";
			$check["info"] .= ffTemplate::_get_word_by_code("count_dir") . $tot_dir . "<br>";
			$check["info"] .= ffTemplate::_get_word_by_code("tot_cache_page_size") . get_literal_size($tot_size);
		}
		$files = array_search("0", $fs);
		if($files !== false) {
		    $check["status"] = ffTemplate::_get_word_by_code("permission_corrupted");
		}
	} else {
		$check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " /cache";
	}
    

	return $check;    	
}
function check_international($show_info = true) {
    $db = ffDB_Sql::factory();
    
    $check["info"] = "";
    $check["status"] = false;

	if(is_dir(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path)) {
		$db->query("SELECT * FROM " . FF_PREFIX . "languages ORDER BY code");
		if($db->nextRecord()) {
		    do {
		        if(!is_file(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT) 
		            || !is_readable(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT) 
		            || !is_writable(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT)) {
		        
		            $check["status"] .= ffTemplate::_get_word_by_code("wrong_file")  . " " . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT . "<br>"; 
		        
		        } else {
		            if($show_info) {
	            		$i18n = array();
	            		require(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT);
	            		$check["info"] .= ffTemplate::_get_word_by_code("count_code_" . strtolower($db->getField("code")->getValue())) . count($i18n) . "<br>";
					}
				} 
		    } while($db->nextRecord());
		}
	} else {
		$check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " " . ffTemplate::$_MultiLang_cache_path;
	}
	
	return $check; 
}
function check_config($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;

    check_function("get_literal_size");
	
	if(is_dir(FF_DISK_PATH . "/conf/gallery/config")) {
		$fs = get_check_fs(FF_DISK_PATH . "/conf/gallery/config", null, true, array("file" => '0644', "dir" => "0755"));
		
		if($show_info) {
			$arrDir = array("d" => 1);
			$config_size = array_diff($fs, $arrDir);
			ksort($config_size);
			foreach($config_size AS $config_size_key => $config_size_value) {	    
	    		$check["info"] .= ffGetFilename($config_size_key) . " => " . get_literal_size($config_size_value) . "<br>";
			}
		}
		$files = array_search("0", $fs);
		if($files !== false) {
		    $check["status"] = ffTemplate::_get_word_by_code("permission_corrupted");
		} 
	} else {
		$check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " /conf/gallery/config";
	}
	
	return $check;
}
function check_uploads($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;

    check_function("get_literal_size");
    
	if(is_dir(DISK_UPDIR)) {
		$fs = get_check_fs(DISK_UPDIR, array("/.htaccess" => true)); //, array("/" . basename(DISK_UPDIR) . "/" . GALLERY_TPL_PATH => true)
		
		if($show_info) {
			$arrDir = array("d" => 1);
			$tot_dir = count(array_intersect($fs, $arrDir));
			$tot_size = array_sum(array_diff($fs, $arrDir));
			
			$check["info"] .= ffTemplate::_get_word_by_code("count_dir") . $tot_dir . "<br>";
			$check["info"] .= ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size);
		}
		$files = array_search("0", $fs);
		if($files !== false) {
		    $check["status"] = ffTemplate::_get_word_by_code("permission_corrupted") . " " . $files;
		}
	} else {
		$check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " /" . basename(DISK_UPDIR);
	}

	return $check;	
}

function check_thumb($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;
	
	return $check;	
}
function check_trash($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;
	
	return $check;	
}
function check_database($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;
	
	return $check;	
}





function set_cache_clear_all() {
	$db = ffDB_Sql::factory();

	$arrFtpMkDir = NULL;
	
    if(!@is_dir(CM_CACHE_PATH)) {
        if(@mkdir(CM_CACHE_PATH)) {
        	@chmod(CM_CACHE_PATH, 0777);
		} else {
			$arrFtpMkDir[] = "/cache";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /cache";
		}
	} else {
		@chmod(CM_CACHE_PATH, 0777);
	}

	$strError = set_fs("/cache", "delete", array(ffTemplate::$_MultiLang_cache_path => true), $arrFtpMkDir);
		
	$sSQL = "TRUNCATE TABLE `cache_sid`";
	$db->execute($sSQL);
	//UPDATE CACHE
	$sSQL = "UPDATE 
		        `layout` 
		    SET 
		        `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
		    ";
	$db->execute($sSQL);
	
	$cache_change = 0; 
	$cache = get_session("cache");
	if(isset($cache["sid"])) { 
		unset($cache["sid"]);
		$cache_change++;
	}
	if(isset($cache["auth"])) {
		$cache_change++;
		unset($cache["auth"]);
	}
	if($cache_change)    
		set_session("cache", $cache);
	
	return $strError;
}
function set_cache_repair() {
	$arrFtpMkDir = NULL;

    if(!@is_dir(CM_CACHE_PATH)) {
        if(@mkdir(CM_CACHE_PATH)) {
        	@chmod(CM_CACHE_PATH, 0777);
		} else {
			$arrFtpMkDir[] = "/cache";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /cache";
		}
	} else {
		@chmod(CM_CACHE_PATH, 0777);
	}
	
	$strError = set_fs("/cache", "chmod", array(ffTemplate::$_MultiLang_cache_path => true), $arrFtpMkDir);

	return $strError;
}

function set_cache_clear() {
	$arrFtpMkDir = NULL;
	
    if(!@is_dir(CM_CACHE_PATH)) {
        if(@mkdir(CM_CACHE_PATH)) {
        	@chmod(CM_CACHE_PATH, 0777);
		} else {
			$arrFtpMkDir[] = "/cache";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /cache";
		}
	} else {
		@chmod(CM_CACHE_PATH, 0777);
	}

	$strError = set_fs("/cache", "delete", array(ffTemplate::$_MultiLang_cache_path => true), $arrFtpMkDir);
		
	$cache_change = 0; 
	$cache = get_session("cache");
	if(isset($cache["sid"])) { 
		unset($cache["sid"]);
		$cache_change++;
	}
	if(isset($cache["auth"])) {
		$cache_change++;
		unset($cache["auth"]);
	}
	if($cache_change)    
		set_session("cache", $cache);

	return $strError;
}
function set_cache_clear_db() {
	$db = ffDB_Sql::factory();

	$sSQL = "DELETE FROM `cache_page` WHERE force_visualization = ''";
	$db->execute($sSQL);

	$cache_change = 0; 
	$cache = get_session("cache");
	if(isset($cache["sid"])) { 
		unset($cache["sid"]);
		$cache_change++;
	}
	if($cache_change)    
		set_session("cache", $cache);
	
	return $strError;
}
function set_cache_clear_sid() {
	$db = ffDB_Sql::factory();

	$arrFtpMkDir = NULL;
	
    if(!@is_dir(CM_CACHE_PATH . "/sid")) {
        if(@mkdir(CM_CACHE_PATH . "/sid")) {
        	@chmod(CM_CACHE_PATH . "/sid", 0777);
		} else {
			$arrFtpMkDir[] = "/cache/sid";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /cache/sid";
		}
	} else {
		@chmod(CM_CACHE_PATH . "/sid", 0777);
	}

	$strError = set_fs("/cache/sid", "delete", array(ffTemplate::$_MultiLang_cache_path => true), $arrFtpMkDir);
	
	$sSQL = "TRUNCATE TABLE `cache_sid`";
	$db->execute($sSQL);
	//UPDATE CACHE
	$sSQL = "UPDATE 
		        `layout` 
		    SET 
		        `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
		    ";
	$db->execute($sSQL);
	$cache = get_session("cache");
	if(isset($cache["sid"])) {
		unset($cache["sid"]);
		set_session("cache", $cache);
	}
	return $strError;
}
function set_international_repair() {
	$db = ffDB_Sql::factory();

	$arrFtpMkDir = NULL;
	
	$sSQL = "DELETE FROM ff_international WHERE ID NOT IN 
				( 
					SELECT tbl_src.ID AS ID 
					FROM ( 
						SELECT ff_international.* 
						FROM ff_international 
						WHERE 1 
						ORDER BY `is_new` ASC, `word_code` DESC
					) AS tbl_src 
					GROUP BY `ID_lang`, `word_code`
				)";
	//$db->execute($sSQL);
	
    if(!@is_dir(CM_CACHE_PATH)) {
        if(@mkdir(CM_CACHE_PATH)) {
        	@chmod(CM_CACHE_PATH, 0777);
		} else {
			$arrFtpMkDir[] = "/cache";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /cache" . "<br>";
		}
	} else {
		@chmod(CM_CACHE_PATH, 0777);
	}

    if(!@is_dir(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path)) {
        if(@mkdir(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path)) {
        	@chmod(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path, 0777);
		} else {
			$arrFtpMkDir[] = ffTemplate::$_MultiLang_cache_path;
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . ffTemplate::$_MultiLang_cache_path . "<br>";
		}
	} else {
		@chmod(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path, 0777);
	}

	$strError .= set_fs(ffTemplate::$_MultiLang_cache_path, "chmod", NULL, $arrFtpMkDir);

	return $strError;
}
function set_international_reset() {
	$db = ffDB_Sql::factory();

	$sSQL = "UPDATE 
		        `layout` 
		    SET 
		        `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
		    ";
	$db->execute($sSQL);

	$sSQL = "DELETE FROM ff_international WHERE ID NOT IN 
				( 
					SELECT tbl_src.ID AS ID 
					FROM ( 
						SELECT ff_international.* 
						FROM ff_international 
						WHERE 1 
						ORDER BY `is_new` ASC, `word_code` DESC
					) AS tbl_src 
					GROUP BY `ID_lang`, `word_code`
				)";

	
	//UPDATE CACHE 
	$db->query("SELECT * FROM " . FF_PREFIX . "languages WHERE 1");
	if($db->nextRecord()) {
		$i18n_error = false;
		do {
		    if(is_file(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT)) {
		        if($handle = @fopen(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT, 'w')) {
		            $i18n_content = "";
		            if(@fwrite($handle, $i18n_content) === FALSE) {
		                $i18n_error = true;
		            }
		            @fclose($handle);
		        }
		    } else {
		        $handle = @fopen(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT, 'w');
		        @fclose($handle);
		        @chmod(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT, 0777);
		    }
		} while($db->nextRecord());
	}
	
	return $strError;
}
function set_config_repair() {
	$arrFtpMkDir = NULL;
	
    if(!@is_dir(FF_DISK_PATH . "/conf/gallery/config")) {
        if(@mkdir(FF_DISK_PATH . "/conf/gallery/config")) {
        	@chmod(FF_DISK_PATH . "/conf/gallery/config", 0644);
		} else {
			$arrFtpMkDir[] = "/conf/gallery/config";
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /conf/gallery/config" . "<br>";
		}
	} else {
		@chmod(FF_DISK_PATH . "/conf/gallery/config", 0644);
	}
	$strError .= set_fs("/conf/gallery/config", "chmod", NULL, $arrFtpMkDir, array("file" => '0644', "dir" => "0755"));
	
	return $strError;
}
function set_uploads_repair() {
	$arrFtpMkDir = NULL;

	if(!@is_dir(DISK_UPDIR)) {
	    /*if(@mkdir(DISK_UPDIR)) {
	        @chmod(DISK_UPDIR, 0777);
		} else {  */
			$arrFtpMkDir[] = "/" . basename(DISK_UPDIR);
			$strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /" . basename(DISK_UPDIR) . "<br>";
		//}
	} else {
		@chmod(DISK_UPDIR, 0777);
	}

	$strError .= set_fs("/" . basename(DISK_UPDIR), "chmod", NULL, $arrFtpMkDir);  //array("/" . basename(DISK_UPDIR) . "/" . GALLERY_TPL_PATH => true)

	return $strError;
}



