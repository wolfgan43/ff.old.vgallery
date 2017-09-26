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
	function fs_operation($func, $params) {
		call_user_func_array($func, $params);
	}
  
	function delete_file_from_db($strPath, $strFile, $exclude = null) {
	    $db = ffDB_Sql::factory();
	    
	    if($exclude !== null && strlen($exclude)) {
	    $sSQL_addit = " AND files.ID NOT IN (" . $db->toSql($exclude, "Text", false) . ")";
	    } else {
	        $sSQL_addit = "";
	    }
	    $sSQL = "SELECT ID from files WHERE parent = " . $db->toSql($strPath, "Text") . " AND name = " . $db->toSql($strFile, "Text") . $sSQL_addit;
	    $db->query($sSQL);
	    if ($db->nextRecord()) {
	        $ID = $db->getField("ID")->getValue();
	        $sSQL = "DELETE FROM files_rel_groups WHERE ID_files = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);
	    
	        $sSQL = "DELETE FROM files_rel_languages WHERE ID_files = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);
	    
	        $sSQL = "DELETE FROM files_description WHERE ID_files = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);

	        $sSQL = "DELETE FROM rel_nodes WHERE contest_src = " . $db->toSql("files", "Text") . " AND ID_node_src = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);

	        $sSQL = "DELETE FROM rel_nodes WHERE contest_dst = " . $db->toSql("files", "Text") . " AND ID_node_dst = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);
	        
	        $sSQL = "DELETE FROM files WHERE ID = " . $db->toSql($ID, "Number");
	        $db->query($sSQL);
	    }
	}  

	function full_copy( $source, $target, $delete_source = false ) {
		if(!$source || !$target || stripslash($source) == DISK_UPDIR || stripslash($target) == DISK_UPDIR || $source == $target)    
			return;

	    if (file_exists($source) && is_dir( $source ) ) {
	        $disable_rmdir = false;

	        @mkdir( $target, 0777, true );
	        $d = dir( $source );
	        while ( FALSE !== ( $entry = $d->read() ) ) {
	            if ( $entry == '.' || $entry == '..' && $entry != '.svn' ) {
	                continue;
	            }

	            if($source . '/' . $entry == $target) {
	                $disable_rmdir = true;
	                continue;
	            }
	            if ( is_dir( $source . '/' . $entry )) {
	                full_copy( $source . '/' . $entry, $target . '/' . $entry, $delete_source );
	                //if($delete_source)
	                    //rmdir($source . '/' . $entry);
	                continue;
	            }

	            @copy( $source . '/' . $entry, $target . '/' . $entry );
	            @chmod( $target . '/' . $entry, 0777);
	            if($delete_source)
	                @unlink($source . '/' . $entry);
	        }
	 
	        $d->close();
	        if($delete_source && !$disable_rmdir)
	            @rmdir($source);
	    } elseif(file_exists($source) && is_file($source)) {
	        @mkdir( ffcommon_dirname($target), 0777, true );

	        @copy( $source, $target );
	        @chmod( $target, 0777);
	        if($delete_source)
	            @unlink($source);
	    }
	}
	//Procedura per cancellare i file/cartelle e le correlazioni nel db
	function purge_dir($absolute_path, $relative_path, $delete_db = true, $exclude_dir = false) {
		if (file_exists($absolute_path) && is_dir($absolute_path)) {
			if ($handle = opendir($absolute_path)) {
				while (false !== ($file = readdir($handle))) { 
					if ($file != "." && $file != "..") { 
						if (is_dir($absolute_path . "/" . $file)) {
							purge_dir($absolute_path . "/" . $file, $relative_path . "/" . $file);
						} else {
	                        if(is_file($absolute_path . "/" . $file))
							    @unlink($absolute_path . "/" . $file);
	                        if($delete_db)
								delete_file_from_db($relative_path, $file);
						}
					}
				}
				if(!$exclude_dir)
					@rmdir ($absolute_path);
	            if($delete_db)
					delete_file_from_db(ffCommon_dirname($relative_path), basename($relative_path));
			}
		} else {
	        if(file_exists($absolute_path) && is_file($absolute_path))
			    @unlink($absolute_path);

	        if($delete_db)
				delete_file_from_db(ffCommon_dirname($relative_path), basename($relative_path));
		}
	}

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

	function xpurge_dir($relative_path) {
		$res = false;
		
		$ftp = ftp_xconnect();
		if($ftp) {
			$res = ftp_purge_dir($ftp["conn"], $ftp["path"], $relative_path, FF_DISK_PATH);
			
			@ftp_close($ftp["conn"]);
		}
		
		if(!$res) {
			$res = purge_dir(FF_DISK_PATH . $relative_path, $relative_path);
		}
		
		return $res;
	}


	/* da finire la multi copia
	function xcopy_dir($relative_source, $relative_dest) {
		$res = false;
		
		$ftp = ftp_xconnect();
		if($ftp) {
	        $handle = @ftp_nlist($ftp["conn"], "-la " . $ftp["path"] . $relative_source);
	        if (is_array($handle) && count($handle)) {
	            foreach($handle AS $file) {
	                if(basename($file) != "." && basename($file) != "..") {
	                    if(strlen($ftp["path"]))
	                        $real_file = substr($file, strlen($ftp["path"]));
	                    else
	                        $real_file = $file;

		                if (@ftp_chdir($conn_id, $file)) {
							$res = $res && ftp_copy($ftp["conn"], $ftp["path"], $relative_source, $relative_dest, FF_DISK_PATH);			
						}        
					}
				}
				
			}	

			@ftp_close($ftp["conn"]);
		}
		
		if(!$res) {
			$res = @mkdir(ffCommon_dirname(FF_DISK_PATH . $relative_dest), 0777, true);
			if($res) {
				$res = @copy(FF_DISK_PATH . $relative_source, FF_DISK_PATH . $relative_dest);
	    		$res = $res && @chmod(FF_DISK_PATH . $relative_dest, 0777);		
			}
		}
		
		return $res;
	}*/


	function xcopy($relative_source, $relative_dest) {
		$res = false;
		$ftp = ftp_xconnect();
	        
		if($ftp) {
			$res = ftp_copy($ftp["conn"], $ftp["path"], $relative_source, $relative_dest, FF_DISK_PATH);
			@ftp_close($ftp["conn"]);
		}

		if(!$res)
	        full_copy(FF_DISK_PATH . $relative_source, FF_DISK_PATH . $relative_dest);

		return $res;
	}

	function ftp_xconnect() {
		
		if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
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
		            if($real_ftp_path === NULL) {
		                if(@ftp_chdir($conn_id, "/")) {
		                    $real_ftp_path = "";
		                } 
					}

					if($real_ftp_path) {
						$res = array(
							"conn" => $conn_id
							, "path" => $real_ftp_path
						);
					} else {
						@ftp_close($conn_id);
					}
				}
			}
		}

		return $res;
	}

	function ftp_copy($conn_id, $ftp_disk_path, $source, $dest, $local_disk_path = null) {
	    $absolute_path = ffCommon_dirname($ftp_disk_path . $dest);  

	    $res = true;
		if (!@ftp_chdir($conn_id, $absolute_path)) {
			$parts = explode('/', trim(ffCommon_dirname($dest), "/")); 
			@ftp_chdir($conn_id, $ftp_disk_path);
			foreach($parts as $part) {
				if(!@ftp_chdir($conn_id, $part)) {
					$res = $res && @ftp_mkdir($conn_id, $part);
					$res = $res && @ftp_chmod($conn_id, 0755, $part);

					@ftp_chdir($conn_id, $part); 
				}
			}

			if(!$res && $local_disk_path && !is_dir(ffCommon_dirname($local_disk_path . $dest))) {
				$res = @mkdir(ffCommon_dirname($local_disk_path . $dest), 0777, true);
			}
	    }
	    
	    if($res) {
	        
	        if(!is_dir(DISK_UPDIR . "/tmp"))
	            $res = @mkdir(DISK_UPDIR . "/tmp", 0777);
	        elseif(substr(sprintf('%o', fileperms(DISK_UPDIR . "/tmp")), -4) != "0777")
	            $res = @chmod(DISK_UPDIR . "/tmp", 0777);

	        if($res) {
		        $res = ftp_get($conn_id, DISK_UPDIR . "/tmp/" . basename($dest), $ftp_disk_path . $source, FTP_BINARY); 
		        if($res) {
	    		    $res = $res && ftp_put($conn_id, $ftp_disk_path . $dest, DISK_UPDIR . "/tmp/" . basename($dest), FTP_BINARY);  

	    		    $res = $res && @ftp_chmod($conn_id, 0644, $ftp_disk_path . $dest);

	    		    @unlink(DISK_UPDIR . "/tmp/" . basename($dest));
			    }
	        }
		    if(!$res && $local_disk_path && !is_file($local_disk_path . $dest)) {
	    		$res = @copy($local_disk_path . $source, $local_disk_path . $dest);
	    		$res = $res && @chmod($local_disk_path . $dest, 0666);
			}
	    }

	    return $res;	
	}
