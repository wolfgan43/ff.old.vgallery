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
			require_once(FF_DISK_PATH . "/conf/gallery/addons." . FF_PHP_EXT);
			if(function_exists($name)) {
				return true;
			} else {
				return false;
			}
		} elseif(strpos($name, "MD_") === 0) {
			require_once(FF_DISK_PATH . "/" . VG_UI_PATH . "/addons/" . substr($name, 3, strpos($name, "_", 4) - 3) . "/common" . "." . FF_PHP_EXT);
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


function get_path_by_rule($name, $area = "admin") 
{
	$prefix = constant("VG_WS_" . strtoupper($area));
	switch($name) {
		case "pages":
			$res = $prefix . "/pages";
			break;
		case "pages-structure":
			$res = $prefix . "/pages/structure";
			break;
		case "blocks":
			$res = $prefix . "/pages/blocks";
			break;
		case "blocks-appearance":
			$res = $prefix . "/pages/appearance";
			break;			
		case "contents":
			$res = $prefix . "/contents";
			break;
		case "contents-structure":
			$res = $prefix . "/contents/structure";
			break;
		case "seo":
			$res = $prefix . "/seo";
			break;
		case "widgets":
			$res = $prefix . "/widgets";
			break;			
		case "landing-pages":
			$res = $prefix . "/landing-pages";
			break;			
		case "addons":
			$res = $prefix . "/addons";
			break;
		case "services":
			$res = ($area == "restricted"
					? $prefix
					: ""
				) . "/srv";
			break;
		default:
			$cm = cm::getInstance();
			switch($area) {
				case "restricted":
					$prefix = "mod_sec_";
				default:
			}

			$res = (string) $cm->router->named_rules[$prefix . $name]->reverse;
			if(!$res)
				$res = VG_WS_ADMIN . "/" . $name;
	}
	
	
	return FF_SITE_PATH . $res;
}

/*
function get_path_by_rule($name, $area = "restricted") 
{
	switch($name) {
		case "pages":
			$res = VG_WS_ADMIN . "/pages";
			break;
		case "pages-structure":
			$res = VG_WS_ADMIN . "/pages/structure";
			break;
		case "blocks":
			$res = VG_WS_ADMIN . "/pages/blocks";
			break;
		case "contents-structure":
			$res = VG_WS_ADMIN . "/contents/structure";
			break;
		case "seo":
			$res = VG_WS_ADMIN . "/seo";
			break;
		case "addons":
			$res = VG_WS_ADMIN . "/addons";
			break;
		case "services":
			$res = "/srv";
			break;
		default:
			$cm = cm::getInstance();
			switch($area) {
				case "restricted":
					$prefix = "mod_sec_";
				default:
			}

			$res = (string) $cm->router->named_rules[$prefix . $name]->reverse;
			if(!$res)
				$res = VG_WS_ADMIN . "/" . $name;
	}
	
	
	return FF_SITE_PATH . $res;
}
*/

//per rendere i percorsi senza doppi / dove serve
function stripslash($temp) {
	if (substr($temp,-1) == "/")
		$temp = substr($temp,0,-1);
	return $temp;
}



function check_mod($file_permission, $mod, $enable_lang = false, $skip_owner = false)
{
    if (!Cms::env("ENABLE_STD_PERMISSION")) {
        $res = true;
    } else {
        $user = Auth::get("user");
        if (!$skip_owner && $file_permission["owner"] > 0 && $file_permission["owner"] === $user->id) {
            $res = true;
        } else {
            if (!is_array($file_permission["groups"])) {
                $res = true;
            } else {
                if (Cms::env("ENABLE_ADV_PERMISSION")) {
                    $groups = Auth::get("group", array("toArray" => true));
                    foreach ($groups AS $group) {
                        if (isset($file_permission["groups"][$group["name"]]) && $file_permission["groups"][$group["name"]] & $mod) {
                            $res = ($file_permission["groups"][$group["name"]] & 4 ? false : true);
                            break;
                        }
                    }
                } else if ($file_permission["groups"][$user->acl_primary]) {
                    if ($file_permission["groups"][$user->acl_primary] & $mod) {
                        $res = ($file_permission["groups"][$user->acl_primary] & 4 ? false : true);

                    }
                } else {
                    $res = false;
                }
            }
        }

        if($enable_lang && $res && array_key_exists("visible", $file_permission)) {
            if(is_array($file_permission["visible"]) && count($file_permission["visible"])) {
                if(isset($file_permission["visible"][($enable_lang === true ? LANGUAGE_INSET : $enable_lang)])) {
                    $res = $file_permission["visible"][($enable_lang === true ? LANGUAGE_INSET : $enable_lang)];
                } else {
                    $res = false;
                }
            } else {
                $res = $file_permission["visible"];
            }
        }
    }
    return $res;
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



function use_cache($value = NULL) {
    $globals = ffGlobals::getInstance("gallery");
        
    if($value !== NULL) {
        $globals->cache["enabled"] = $value;
        if (defined("DEBUG_MODE")) {
            Cms::getInstance("debug")->dumpLog("use_cache");
        }
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

	$uid = Auth::get("user")->id;
    if($reset) {
		unset($cache["sid"][$key]);

		$UserID = strtolower(Auth::get("user")->username);

		if(is_file(CM_CACHE_DISK_PATH . "/sid/" . $UserID . "/" . $key . ".html")) {
			@unlink(CM_CACHE_DISK_PATH . "/sid/" . $UserID . "/" . $key . ".html");
		}
		if(is_file(CM_CACHE_DISK_PATH . "/sid/" . $UserID . "/gzip/" . $key . ".html.gz")) {
			@unlink(CM_CACHE_DISK_PATH . "/sid/" . $UserID . "/gzip/" . $key . ".html.gz");
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
			return false;
		}
	}
	
	
	
/*	
	$file = CM_CACHE_DISK_PATH . "/sid.php";
    $sid_error = false;

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
	
	$uid = Auth::get("user")->id;
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
