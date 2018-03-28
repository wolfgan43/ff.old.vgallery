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
/*	local_common.php
	----------------
	In this file take place all extra stuff defined by user, site relative.
	Forms not define any code in this file, only manage it.
*/

function check_function($name, $module = null) {
	if(function_exists($name)) {
		return true;
	} else {
		if(strpos($name, "process_addon_") === 0) { 
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/process/addon/" . substr($name, strlen("process_addon_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/process/addon/" . substr($name, strlen("process_addon_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/process/addon/" . substr($name, strlen("process_addon_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/process/addon/" . substr($name, strlen("process_addon_")) . "." . FF_PHP_EXT);
			}
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "process_") === 0) {
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/process/" . substr($name, strlen("process_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/process/" . substr($name, strlen("process_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/process/" . substr($name, strlen("process_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/process/" . substr($name, strlen("process_")) . "." . FF_PHP_EXT);
			}

			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "MD_general_") === 0) {
			require_once(FF_DISK_PATH . "/conf/gallery/modules/common" . "." . FF_PHP_EXT);
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "MD_") === 0) {
			require_once(FF_DISK_PATH . "/conf/gallery/modules/" . substr($name, 3, strpos($name, "_", 4) - 3) . "/common" . "." . FF_PHP_EXT);
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "ecommerce_cart_") === 0) {
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/ecommerce/cart/" . substr($name, strlen("ecommerce_cart_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/ecommerce/cart/" . substr($name, strlen("ecommerce_cart_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/ecommerce/cart/" . substr($name, strlen("ecommerce_cart_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/ecommerce/cart/" . substr($name, strlen("ecommerce_cart_")) . "." . FF_PHP_EXT);
			}
			
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
			
		} elseif(strpos($name, "ecommerce_") === 0) {
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/ecommerce/" . substr($name, strlen("ecommerce_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/ecommerce/" . substr($name, strlen("ecommerce_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/ecommerce/" . substr($name, strlen("ecommerce_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/ecommerce/" . substr($name, strlen("ecommerce_")) . "." . FF_PHP_EXT);
			}
			
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "query_") === 0) {
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/query/" . substr($name, strlen("query_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/query/" . substr($name, strlen("query_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/query/" . substr($name, strlen("query_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/query/" . substr($name, strlen("query_")) . "." . FF_PHP_EXT);
			}
			
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "class.") === 0) {
			$autoload = strrpos($name, ".", strlen("class."));
			if($autoload === false)
				$dir_class = substr($name, strlen("class."));
			else
				$dir_class = substr($name, strlen("class."), $autoload);

        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/" . $dir_class . "/" . $name . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/" . $dir_class. "/" . $name . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/" . $dir_class . "/" . $name . "." . FF_PHP_EXT)) {
		        require_once(FF_DISK_PATH . "/library/" . $dir_class . "/" . $name . "." . FF_PHP_EXT);
			}

	        if(class_exists($dir_class)) {
	            return true;
	        } else {
	            return false;
	        }
		} elseif(strpos($name, "mod_") === 0) {
			if(file_exists(FF_DISK_PATH . "/modules/" . substr($name, 4, strpos($name, "_", 4) - 4) . "/events." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/modules/" . substr($name, 4, strpos($name, "_", 4) - 4) . "/events." . FF_PHP_EXT);
			}
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "job_") === 0) {
        	if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/job/" . substr($name, strlen("job_")) . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" . $module . "/library/job/" . substr($name, strlen("job_")) . "." . FF_PHP_EXT);
        	} elseif(file_exists(FF_DISK_PATH . "/library/gallery/job/" . substr($name, strlen("job_")) . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/job/" . substr($name, strlen("job_")) . "." . FF_PHP_EXT);
			}

			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
	    } elseif(strpos($name, "system_") === 0) {
	        if(strlen($module) && file_exists(FF_DISK_PATH . "/modules/" . $module . "/library/system/" . substr($name, strlen("system_")) . "." . FF_PHP_EXT)) {
	            require_once(FF_DISK_PATH . "/modules/" . $module . "/library/system/" . substr($name, strlen("system_")) . "." . FF_PHP_EXT);
	        } elseif(file_exists(FF_DISK_PATH . "/library/gallery/system/" . substr($name, strlen("system_")) . "." . FF_PHP_EXT)) {
	            require_once(FF_DISK_PATH . "/library/gallery/system/" . substr($name, strlen("system_")) . "." . FF_PHP_EXT);
	        }

	        if(function_exists($name)) {
	            return true;
	        } else {
	            return false;
	        }
        } else {
			if(strlen($name) && file_exists(FF_DISK_PATH . "/library/gallery/common/" . $name . "." . FF_PHP_EXT)) {
				require_once(FF_DISK_PATH . "/library/gallery/common/" . $name . "." . FF_PHP_EXT);
			}
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		}
	}	
}

function get_pathinfo($strip_virtual_path = true) {
    $cm = cm::getInstance();
//ffErrorHandler::raise("as", E_USER_ERROR, null,get_defined_vars());
    if($strip_virtual_path) {
        if(!strlen($cm->real_path_info) && basename($cm->path_info) != "index")
            $path = $cm->path_info;
        else                
            $path = $cm->real_path_info;
        
        if($path == "")
            $path = "/";
    } else {
        $path = $cm->path_info;
        if($path == "/index")
            $path = "/";
    }

    return $path;
}


function get_global($type, $key = null) {
	$globals = ffGlobals::getInstance("gallery");
	
	if(isset($globals->{$type})) {
		if(strlen($key)) {
			if(array_key_exists($key, $globals->{$type})) {
				$res = $globals->{$type}[$key];
			} else {
				$res = false;
			}
		} else {
			$res = $globals->{$type};
		}
	} else {
		$res = null;
	}
	
	return $res;
}

function global_settings($key = null, $settings_path = null) {
	$globals = ffGlobals::getInstance("gallery");
	
	if(!is_array($globals->settings) && !count($globals->settings) && check_function("system_init_settings")) 
	{
		system_init_settings($settings_path === null ? $globals->settings_path : $settings_path);
	}
	
	return get_global("settings", $key);
}

//per rendere i percorsi senza doppi / dove serve
function stripslash($temp) {
	if (substr($temp,-1) == "/")
		$temp = substr($temp,0,-1);
	return $temp;
}



function check_mod($file_permission, $mod, $enable_lang = false, $skip_owner = false) {
    if(!ENABLE_STD_PERMISSION)
        return true;

	$user_permission = get_session("user_permission");
	$return_val = false;

	if(!$skip_owner && $file_permission["owner"] > 0 && $file_permission["owner"] === $user_permission["ID"]) {
		return true;
	}

	if (is_array($file_permission["groups"])) {
		if(ENABLE_ADV_PERMISSION
			&& strlen($user_permission["primary_gid_default_name"]) 
			&& array_key_exists($user_permission["primary_gid_default_name"], $file_permission["groups"])
			&& $file_permission["groups"][$user_permission["primary_gid_default_name"]] > 0 
		) {
			if ($file_permission["groups"][$user_permission["primary_gid_default_name"]] & $mod) {
				$return_val = true;
			} else {
				$return_val = false;
			}
			if ($file_permission["groups"][$user_permission["primary_gid_default_name"]] & 4) {
				$return_val = !$return_val;
			}
		} else {
			foreach ($file_permission["groups"] as $key => $value) {
				if (isset($user_permission["groups"][$key])) {
					if ($file_permission["groups"][$key] & $mod) {
						$return_val = true;
						break;
					}
				}
			} 
		}
	}

	if($enable_lang && $return_val && array_key_exists("visible", $file_permission)) {
		if(is_array($file_permission["visible"]) && count($file_permission["visible"])) {
			if(isset($file_permission["visible"][($enable_lang === true ? LANGUAGE_INSET : $enable_lang)])) { 
				$return_val = $file_permission["visible"][($enable_lang === true ? LANGUAGE_INSET : $enable_lang)];
			} else {
				$return_val = false;
			}
		} else {
			$return_val = $file_permission["visible"];
		}
	}
	return $return_val;
}



function get_vgallery_is_dir($name, $parent, $ID_category = null) {
    $db = ffDB_Sql::factory();        

    if(strpos($parent, "/anagraph") === 0) {
        if(!strlen($name)) {
            if($ID_category > 0) 
            {
                $sSQL = "SELECT anagraph_categories.ID
                            , anagraph_categories.name
                            , anagraph_categories.smart_url
                        FROM anagraph_categories
                        WHERE anagraph_categories.ID = " . $db->toSql($ID_category, "Number");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $res = array("ID" => $db->getField("ID", "Number", true)
                                , "name" => $db->getField("name", "Text", true)
                                , "smart_url" => $db->getField("smart_url", "Text", true)
                    ); 
                } else {
                    $res = true;
                }
            } else 
                $res = true;
        } else {
            $sSQL = "SELECT anagraph_categories.ID
                        , anagraph_categories.name
                        , anagraph_categories.smart_url
                    FROM anagraph_categories
                    WHERE anagraph_categories.smart_url = " . $db->toSql($name);                
            $db->query($sSQL);
            if($db->nextRecord()) {
                $res = array("ID" => $db->getField("ID", "Number", true)
                            , "name" => $db->getField("name", "Text", true)
                            , "smart_url" => $db->getField("smart_url", "Text", true)
                ); 
            } else {
                $res = false;
            }
        }
    } else {
        $sSQL = "SELECT 
                    vgallery_nodes.is_dir AS is_dir
                FROM vgallery_nodes
                WHERE vgallery_nodes.name = " . $db->toSql($name) . "
                    AND vgallery_nodes.parent = " . $db->toSql($parent);
        $db->query($sSQL);
        if($db->nextRecord()) {
            if($db->getField("is_dir", "Number", true)) {
                $res = true;
            } else {
                $res = false;
            }
        } else {
            $res = NULL;   
        }
    }    
    return $res;
}






function escape_string_x_regexp($value) {
    $value = str_replace("/", "", $value);
    $value = str_replace("(", "", $value);
    $value = str_replace(")", "", $value);

    return $value;
}

function use_cache($value = NULL) {
    $globals = ffGlobals::getInstance("gallery");
        
    if($value !== NULL) {
		$globals->cache["enabled"] = $value;
	}
    return $globals->cache["enabled"];
}

function set_sid($new_sid, $unic_key = null, $reset = false, $smart_url = null) {
	$db = ffDB_Sql::factory();

    if($unic_key === null) { 	
	    $key = md5($new_sid);   
    } else {
        $key = md5($unic_key);
    }

    if(strlen($smart_url)) {
		$smart_url = ffCommon_url_rewrite($smart_url);
    } else {
		$smart_url = "";
    }

    $cache = get_session("cache");
    if(isset($cache["sid"][$key])) {
    	if(!$reset) {
			if($cache["sid"][$key] === true) {
				return $key;	
			} else {
				return $cache["sid"][$key];
			}
		}
	}

	$uid = get_session("UserNID");
    if($reset) {
		unset($cache["sid"][$key]);
		
		if(is_file(CM_CACHE_PATH . "/sid/" . strtolower(get_session("UserID")) . "/" . $key . ".html")) {
			@unlink(CM_CACHE_PATH . "/sid/" . strtolower(get_session("UserID")) . "/" . $key . ".html");
		}
		if(is_file(CM_CACHE_PATH . "/sid/" . strtolower(get_session("UserID")) . "/gzip/" . $key . ".html.gz")) {
			@unlink(CM_CACHE_PATH . "/sid/" . strtolower(get_session("UserID")) . "/gzip/" . $key . ".html.gz");
		}
	    $sSQL = "DELETE FROM cache_sid WHERE sid = " . $db->toSql($key) . " AND uid = " . $db->toSql($uid);
		$db->query($sSQL);
	}
    
	$sSQL = "SELECT sid 
				, smart_url
			FROM cache_sid 
			WHERE sid = " . $db->toSql($key) . 
				(strlen($smart_url)
					? " AND smart_url = " . $db->toSql($smart_url)
					: ""
				) . "
				AND uid = " . $db->toSql($uid);
	$db->query($sSQL);
	if($db->nextRecord()) {
	
        $cache["sid"][$db->getField("sid", "Text", true)] = (strlen($smart_url) ? $smart_url : true);
        set_session("cache", $cache);
        
		if($cache["sid"][$db->getField("sid", "Text", true)] === true) {
			return $db->getField("sid", "Text", true);	
		} else {
			return $cache["sid"][$db->getField("sid", "Text", true)];
		}
	} else {
		$sSQL = "INSERT INTO cache_sid 
				(
					sid
					, value
					, unic_key
					, smart_url
					, uid
				) VALUES ( 
					" . $db->toSql($key) . "
					, " . $db->toSql($new_sid) . "
					, " . $db->toSql($unic_key) . " 
					, " . $db->toSql($smart_url) . " 
					, " . $db->toSql($uid) . " 
				)";
		$db->execute($sSQL);
		if($db->affectedRows()) {
            $cache["sid"][$key] = (strlen($smart_url) ? $smart_url : true);
            set_session("cache", $cache);

			if($cache["sid"][$key] === true) {
				return $key;
			} else {
				return $cache["sid"][$key];
			}

            return $key;
		} else {
			return $new_sid;
		}
	}
	
	
	
/*	
	$file = CM_CACHE_PATH . "/sid.php";
    $sid_error = false;

    clearstatcache();
    if(@filesize($file))
        $sid_content = "";
    else
        $sid_content = "<?php\n";
     
	 if(is_file($file)) {
    	require($file);	
		$key = md5($new_sid);
		
		if(!isset($sid[$key])) {
			if($handle = @fopen($file, 'a')) {
				$sid_content .= '$sid["' . $key . '"] = "' . addslashes($new_sid) . '";' . "\n";
				if(@fwrite($handle, $sid_content) === FALSE) {
					$sid_error = true;
				}
				@fclose($handle);
			} else {
				$sid_error = true;
			}
		}
	 } else {
	    $sid_error = true;	 
	 }
	 
    if($sid_error)
    	return $new_sid;
    else
    	return $key;
    	
*/
}
function get_sid($id, $smart_url = null, $return_key = false) {
	$db = ffDB_Sql::factory();
	
	$uid = get_session("UserNID");
	if(strlen($smart_url)) {
		$sSQL = "SELECT sid, value FROM cache_sid WHERE smart_url = " . $db->toSql($smart_url) . " AND uid = " . $db->toSql($uid);
	} else {
		$sSQL = "SELECT sid, value FROM cache_sid WHERE sid = " . $db->toSql($id) . " AND uid = " . $db->toSql($uid);
	}
	$db->query($sSQL);
	if($db->nextRecord()) {
		$sid = $db->getField("sid", "Text", true);
		$res = $db->getField("value", "Text", true);
	} else {
		$res = "";
	}

	if(!strlen($res)) {
		$cache = get_session("cache");
		if(is_array($cache) 
			&& array_key_exists("sid", $cache) 
			&& is_array($cache["sid"]) 
			&& array_key_exists($id, $cache["sid"])
		) { 
		    unset($cache["sid"][$id]);

			set_session("cache", $cache);
		}
		
		return $id;	
	} else {
		if($return_key) {
			return array("key" => $sid, "value" => $res);
		} else {
			return $res;
		}
	}
	
	
	
	
	/*
	$file = CM_CACHE_PATH . "/sid.php";
	
	if(is_file($file)) {
    	require($file);	
    	
    	if(isset($sid[$id])) {
    		return $sid[$id];
		} else {
    		return $id;
		}
	} else {
		return $id;   	
	}
	
	*/
}












if (!function_exists('is_binary')) {
	/**
	* Determine if a file is binary. Useful for doing file content
	editing
	*
	* @access public
	* @param mixed $link Complete path to file (/path/to/file)
	* @return boolean
	* @link http://us3.php.net/filesystem#30152
	* @see link user notes regarding this created function
	*/
	function is_binary($link) {
		$tmpStr = '';
		$fp = @fopen($link, 'rb');
		$tmpStr = @fread($fp, 256);
		@fclose($fp);
		
		if ($tmpStr) {
			$tmpStr = preg_replace('/\s+/', '', $tmpStr);
			
			$tmpInt = 0;

			for ($i = 0; $i < strlen($tmpStr); $i++) {
				if (extension_loaded('ctype')) {
					if(!ctype_print($tmpStr[$i])) {
						$tmpInt++;
					}
				} elseif (!preg_match('/[[:print:]]/i', $tmpStr[$i])) {
					$tmpInt++;
				}
			}

			if (floor($tmpInt * 100 / 256) > 20) {
				return true; 
			} else {
				return false;
			}
		} else {
			return null;
		}
	}
}
