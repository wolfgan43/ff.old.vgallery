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
	define("CACHE_DISK_PATH", dirname(dirname(dirname(__DIR__))));
	define("CACHE_LAST_VERSION", 0);

    if(!function_exists("sys_getloadavg"))
    {
        function sys_getloadavg($windows = false){
            $os=strtolower(PHP_OS);
            if(strpos($os, 'win') === false){
                if(file_exists('/proc/loadavg')){
                    $load = file_get_contents('/proc/loadavg');
                    $load = explode(' ', $load, 1);
                    $load = $load[0];
                }elseif(function_exists('shell_exec')){
                    $load = explode(' ', `uptime`);
                    $load = $load[count($load)-1];
                }else{
                    return false;
                }

                if(function_exists('shell_exec'))
                    $cpu_count = shell_exec('cat /proc/cpuinfo | grep processor | wc -l');

                return array('load'=>$load, 'procs'=>$cpu_count);
            }elseif($windows){
                if(class_exists('COM')){
                    $wmi=new COM('WinMgmts:\\\\.');
                    $cpus=$wmi->InstancesOf('Win32_Processor');
                    $load=0;
                    $cpu_count=0;
                    if(version_compare('4.50.0', PHP_VERSION) == 1){
                        while($cpu = $cpus->Next()){
                            $load += $cpu->LoadPercentage;
                            $cpu_count++;
                        }
                    }else{
                        foreach($cpus as $cpu){
                            $load += $cpu->LoadPercentage;
                            $cpu_count++;
                        }
                    }
                    return array('load'=>$load, 'procs'=>$cpu_count);
                }
                return false;
            }
            return false;
        }
    }

    function cache_get_page_by_id($id) {
    	$page = cache_get_settings("page");
    	
    	if(isset($page["/" . $id]))
            return "/" . $id;
        else
        	return "/error";
    }

    function cache_get_settings($type = null, $name = null, $default = null) {
    	static $schema = null;
    	
    	if(!$schema) {
    		require(CACHE_DISK_PATH . "/library/gallery/settings.php");
    		
    		if(is_file(CACHE_DISK_PATH . "/themes/site/settings.php")) {
    			require(CACHE_DISK_PATH . "/themes/site/settings.php");
    		}
    		if(is_file(CACHE_DISK_PATH . "/cache/locale.php")) {
    			require(CACHE_DISK_PATH . "/cache/locale.php");

                /** @var include $locale */
                $schema["locale"] = $locale;
    		}
    		
    	}
    
    	if(is_array($default) && count($default)) {
			if($type && $name && is_array($schema[$type][$name]) && count($schema[$type][$name]) && is_array($default)) {
				return array_replace_recursive($default, $schema[$type][$name]);
			} elseif($type && is_array($schema[$type]) && count($schema[$type]) && is_array($default)) {
				return array_replace_recursive($default, $schema[$type]);
			} else {
				return array_replace_recursive($default, $schema);
			}
    	}
    
        if($type)
            return $schema[$type];
        else
            return $schema;
    }    
    
    function cache_get_page_properties($user_path, $page_type = null, $skip_locale = false) {
        //require(CACHE_DISK_PATH . "/library/gallery/settings.php");
        $schema = cache_get_settings();

		//strippa il path di base per la cache
		if(is_array($schema["alias"]) && count($schema["alias"])) {
			if($schema["alias"][$_SERVER["HTTP_HOST"]]) {
				$resAlias["alias"] = $schema["alias"][$_SERVER["HTTP_HOST"]];
				if(strpos($user_path, $schema["alias"][$_SERVER["HTTP_HOST"]] . "/") === 0
                    || $user_path == $schema["alias"][$_SERVER["HTTP_HOST"]]
                ) {
					$user_path = substr($user_path, strlen($schema["alias"][$_SERVER["HTTP_HOST"]]));
					$resAlias["redirect"] = $_SERVER["HTTP_HOST"] . $user_path;
					if(!$user_path)
	            		$user_path = "/";					
				}

				if($resAlias["alias"] != "/") {
					$arrLocale = cache_get_settings("locale");
					if($arrLocale["rev"]["lang"][basename($resAlias["alias"])]) {
						$lang = $arrLocale["rev"]["lang"][basename($resAlias["alias"])];
					} else {
						$settings_user_path = $resAlias["alias"];
					}
				}
			} else {
				$resAlias["redirect"] = false;
			}
		}
        
		$settings_user_path                                     .= $user_path;
        $arrSettings_path                                       = explode("/", trim($user_path, "/"));

        if(!$lang && $arrLocale["rev"]["lang"][$arrSettings_path[0]]) {
            $lang                                               = $arrLocale["rev"]["lang"][$arrSettings_path[0]];
        }

        if($page_type && isset($schema["page"][$page_type])) {
            $res                                                = $schema["page"][$page_type];
        } elseif(isset($schema["page"][$settings_user_path])) {
            $res                                                = $schema["page"][$settings_user_path];
            $page_key                                           = $settings_user_path;
        } elseif(isset($schema["page"]["/" . $arrSettings_path[0]] )) {
            $res                                                = $schema["page"]["/" . $arrSettings_path[0]];
            $page_key                                           = "/" . $arrSettings_path[0];
        } elseif(isset($schema["page"][$arrSettings_path[count($arrSettings_path) - 1]])) {
            $res                                                = $schema["page"][$arrSettings_path[count($arrSettings_path) - 1]];
        } else {
            //$tmp_user_path = $user_path;
            do {

                if(isset($schema["page"][$settings_user_path])) {
                    $res                                        = $schema["page"][$settings_user_path];
                    $page_key                                   = $settings_user_path;
                    break;
                }
            } while($settings_user_path != DIRECTORY_SEPARATOR && ($settings_user_path = dirname($settings_user_path)));
        }

        if(strpos($user_path, $res["strip_path"]) === 0) {
            $user_path                                          = substr($user_path, strlen($res["strip_path"]));
	        if(!$user_path)
	            $user_path                                      = "/";
		}
		
		if($resAlias) {
			$res["alias"]                                       = $resAlias["alias"];
			if($resAlias["redirect"] === false) {
				$alias_flip                                     = array_flip($schema["alias"]); //fa redirect al dominio alias se il percorso e riservato ad un dominio alias
				if($alias_flip["/" . $arrSettings_path[0]]) {
					$resAlias["redirect"]                       = $alias_flip["/" . $arrSettings_path[0]] . substr($user_path, strlen("/" . $arrSettings_path[0]));
				}		
			}
			
			$res["redirect"]                                    = $resAlias["redirect"];
		}

        $res["user_path"]                                       = $user_path;

        if($lang)
            $res["lang"]                                        = $lang;

        if($res["db_path"] && $page_key && strpos($user_path, $page_key) === 0) {
        	$res["db_path"]                                     .= substr($user_path, strlen($page_key));
		} else {
			$res["db_path"]                                     = $user_path;
		}

		$arrUserPath = pathinfo($user_path);
        if($arrUserPath["extension"])
        	$res["type"]                                        = $arrUserPath["extension"];

		if(!$res["framework_css"])
			$res["framework_css"]                               = $schema["page"]["/"]["framework_css"];
		if(!$res["font_icon"])
			$res["font_icon"]                                   = $schema["page"]["/"]["font_icon"];

		if(!$res["layer"])
			$res["layer"]                                       = $schema["page"]["/" . $arrSettings_path[0]]["layer"];
		
        if(!$res["group"])
            $res["group"]                                       = $res["name"];

        if(!$skip_locale)
            $res["locale"]                                      = cache_get_locale($res);

        return $res;        
    }
    
	function cache_token_write($u, $objToken = null, $type = null) {
		//$file_token_dir = CACHE_DISK_PATH . "/cache/token";

		if(is_array($objToken)) {
			cache_token_set_session_cookie($objToken);
			
			$file_token_name = $objToken["public"];
		} else {
			$file_token_name = $objToken;
		}

		if($file_token_name) {
			//populate logs
			$u["logs"][$_SERVER["REMOTE_ADDR"]]++;


			$fsToken = cache_token_get_file_token($file_token_name, $type, true);

			$content = "<?php\n";
			$content .= '$u = ' . var_export($u, true) . ";";
			if($handle = @fopen($fsToken["file"], 'w')) {
				@fwrite($handle, $content); 
				@fclose($handle);
			} 		
		}
	}
	
    function cache_token_generate($account = null, $precision = 8, $expire = null, $renew = true) {
    	if(!$expire)
    		$expire = time() + (60 * 60 * 24 * 365);

		$res["expire"] 		= $expire;
		$res["renew"] 		= $renew;
	    $res["stoken"] 		= bin2hex(openssl_random_pseudo_bytes($precision));
		$res["private"] 	= uniqid($res["stoken"]);

		if($account) {
			$hash 			= sha1($account . $res["stoken"]);
			$res["public"] 	= substr($hash, 0, strlen($hash) - strlen($res["private"]));
			$res["token"] 	= $res["public"] . $res["private"];
		}

    	return $res;
    }
    
    function cache_token_resolve($token, $account = null, $precision = 8) {
	    $res["new"]			= cache_token_generate($account, $precision);

		$res["public"] 		= substr($token, 0, strlen($token) - strlen($res["new"]["private"]));
		$res["private"] 	= substr($token, strlen($res["public"]));	    
		$res["token"] 		= $token;

    	return $res;
    }

	function cache_token_create($user_permission = null, $expire = null, $renew = true, $precision = 8) {
		$u = array();
		$token_user = "t";
		
		if(!$user_permission) {
            require_once(CACHE_DISK_PATH . "/conf/gallery/config/session.php");

			$user_permission = $_SESSION[APPID . "user_permission"];
		}
		$uid = $user_permission["ID"];
		$account = ($user_permission["username_slug"]
			? $user_permission["username_slug"]
			: ($user_permission["username"]
				? cache_url_rewrite($user_permission["username"])
				: cache_url_rewrite($user_permission["email"])
			)
		);
		$gid = ($user_permission["primary_gid_name"]
					? $user_permission["primary_gid_name"]
					: $user_permission["primary_gid_default_name"]
				);	

		$objToken = cache_token_generate($account, $precision, $expire, $renew);

		$u = array(
			"account" => $account
			, "uid" => $uid
			, "group" => $gid
			, "uniqid" => $objToken["private"]
			, "expire" => $objToken["expire"]
			, "renew" => $objToken["renew"]
			, "addr" => $_SERVER["REMOTE_ADDR"]
			, "agent" => $_SERVER["HTTP_USER_AGENT"] 
		);

		cache_token_write($u, $objToken, $token_user);

		return $objToken["token"];
	}
 
	function cache_token_renew($account, $objToken = null) {
    	if(!$objToken) {
    		$objToken = cache_token_generate($account);
		} else  {
			$hash = sha1($account . $objToken["stoken"]);

			$objToken["public"] = substr($hash, 0, strlen($hash) - strlen($objToken["private"]));
			$objToken["token"] = $objToken["public"] . $objToken["private"];						
		}

    	return $objToken;
    }
	
    function cache_session_share_for_subdomains() {
		static $domain = null;
    	
    	if(!$domain) {
			if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $_SERVER["HTTP_HOST"], $regs)) {
				$domain = $regs['domain'];
			} else {
				$domain = $_SERVER["HTTP_HOST"];
			}
			
			session_set_cookie_params(0, '/', '.' . $domain);    
		}    

    	return $domain;
    }
    
    function cache_token_set_session_cookie($objToken, $name = "_ut") {
    	cache_session_share_for_subdomains();

		$sessionCookie = session_get_cookie_params();

		setcookie($name,  $objToken["token"],  $objToken["expire"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);	

		//cache_writeLog("SET COOKIE: " . $_COOKIE["_ut"]  . " = " . $objToken["token"] . " exp: " . $objToken["expire"], "mio");		
    }

    function cache_token_get_session_cookie($name = "_ut") {
    	return $_COOKIE[$name];
    }
    function cache_token_destroy_session_cookie($name = "_ut") {
		$sessionCookie = session_get_cookie_params();
		setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);

        cache_session_share_for_subdomains();

		$sessionCookie = session_get_cookie_params();
		setcookie($name, false, $sessionCookie["lifetime"], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], $sessionCookie["httponly"]);
    }
    
	function cache_token_get_file_token($public_token, $type = null, $create_dir = false) {
		$dir_token = CACHE_DISK_PATH . "/cache/token";
		switch($type) {
			case "t":
				$step = "/" . substr($public_token, 0, 3);
				break;
			default:
		}
		
		if($create_dir && !is_dir($dir_token . $step))
			@mkdir($dir_token . $step, 0777, true);

		return array(
			"file" => $dir_token . $step . "/". $public_token . ".php"
			, "dir" => $dir_token 
		);
	}
	
	function cache_check_session_by_token($token = null) {
		static $user = null;
		//$dir_token = CACHE_DISK_PATH . "/cache/token";
        $token_user = "t";
		
		if(!$user) {
			//$precision = 8;
			//$cookie_name = "_ut";
			if($token === null)
				$token = cache_token_get_session_cookie();

//			cache_writeLog("entro: " . $cc . "  " . $token . print_r($_SERVER, true) , "mio");

			if($token) {
				if(strpos($token, $token_user . "-") === 0) {
					$token = substr($token, 2);
					$type_token = $token_user;
					
					cache_token_destroy_session_cookie();
				}
				$objToken = cache_token_resolve($token);

				//$stoken = bin2hex(openssl_random_pseudo_bytes($precision));
				//$new_private = uniqid($stoken);
				
				//$objToken["public"] = substr($token, 0, strlen($token) - strlen($new_private));
				//$objToken["private"] = substr($token, strlen($objToken["public"]));
				
				$fsToken = cache_token_get_file_token($objToken["public"], $type_token);

				//$file_token = $dir_token . "/" . $prefix_token . $objToken["public"] . ".php";
//	cache_writeLog("COOKIE: " . $cc . "  " . $_COOKIE["_ut"]  . " = " . $token, "mio");		
				if(is_file($fsToken["file"])) {
					require($fsToken["file"]);
//	cache_writeLog("FOUND: " . $cc . "  " . $file_token . " = " . $token, "mio");

                    /** @var include $u */
                    if($u["uniqid"] == $objToken["private"]) {
						if($u["expire"] >= time()) {
							$user = $u;												

							if($u["renew"] === 1) {
								$u["renew"] = false;
								@unlink($fsToken["file"]);
								cache_token_destroy_session_cookie();
							}

							if($u["renew"]) {
								$objToken["new"] = cache_token_renew($u["account"], $objToken["new"]);

								//cache_token_set_session_cookie($objToken["new"]);
								//$u["logs"][$_SERVER["REMOTE_ADDR"]]++;
								$u = array(
									"account" => $u["account"]
									, "uid" => $u["uid"]
									, "group" => $u["group"]
									, "uniqid" => $objToken["new"]["private"]
									, "expire" => $objToken["new"]["expire"]
									, "renew" => ($u["renew"] === true
										? $u["renew"]
										: $u["renew"] - 1 
									)
									, "addr" => $_SERVER["REMOTE_ADDR"]
									, "agent" => $_SERVER["HTTP_USER_AGENT"] 
									, "logs" => $u["logs"]
								);	

								cache_token_write($u, $objToken["new"]);

								/*$new_file_token = $dir_token . "/" .  $objToken["new"]["public"] . ".php";
								
								$content = "<?php\n";
								$content .= '$u = ' . var_export($u, true) . ";";
 								if($handle = @fopen($new_file_token, 'w')) {
     								@fwrite($handle, $content); 
     								@fclose($handle);
								}*/

								@unlink($fsToken["file"]);
								
//cache_writeLog("REGEN: " . $cc . "  " . $objToken["new"]["token"] . "=old> " . $objToken["token"] . " = " . $token, "mio");
							} else {
								cache_token_write($u, $objToken["public"]);
							}
						} else {
							@unlink($fsToken["file"]);
							cache_token_destroy_session_cookie();
//cache_writeLog("Destroy: " .  $cc . "  " .  $_COOKIE["_ut"] . "=old> " . $objToken["token"] . " = " . $token, "mio");
						}
					}
				} else {
					cache_token_destroy_session_cookie();
				}
            } elseif($_GET[$token_user]) {
                $token = $_GET[$token_user];
				$objToken = cache_token_resolve($token);
				$objToken["token"] = $token_user . "-" . $objToken["token"];

				$fsToken = cache_token_get_file_token($objToken["public"], $token_user);

				if(is_file($fsToken["file"])) {
                   // require($file_token);
					//copy($dir_token . "/" . $token_user . "-" . $objToken["public"] . ".php", $dir_token . "/" . $objToken["public"] . ".php");
                    cache_token_set_session_cookie($objToken);
                }

				do_redirect($_SERVER["HTTP_HOST"] . trim(preg_replace('/([?&])'.$token_user.'=[^&]+(&|$)/','$1',$_SERVER["REQUEST_URI"]), "?"));
            }
			
			
			
			
	//		if($token && !$cookie_valid) {
		//		cache_token_destroy_session_cookie();


		/*	echo $token . "   sssssssssssssssssssssssssss" ;
			print_r($_COOKIE);
				print_r($u);
				print_r($objToken);
				echo("ASDASD");*/

				
	//			$sessionCookie = session_get_cookie_params();
	//			setcookie($cookie_name, false, null, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
	//		} else {
				
		//	}
		}

		return $user;
	} 
	
	function cache_get_permission($username, $type = "perm") {
		$file_permission = CACHE_DISK_PATH . "/cache/cfg/" . $type . "/" . $username . ".php";

		if(is_file($file_permission)) {
			require($file_permission);
		}
		switch($type) {
			case "perm":
                /** @var include $user_permission */
                $res = $user_permission;
				break;
			case "gid":
                /** @var include $permissions */
                $res = $permissions;
				break;
			default:
			
		}
		
		return $res;
	}
	
	function cache_create_session_by_token($token = null, $start_session = true) {
		$user_permission = array();

		$user = cache_check_session_by_token($token);
		if($user) {
			$user_permission = cache_get_permission($user["account"]);
			if(!is_array($user_permission)) {
				$user_permission = array(
					"ID" => $user["uid"]
					, "username_slug" => $user["account"]
					, "primary_gid_name" => $user["group"]
					, "permissions" => cache_get_permission($user["group"], "gid")
					, "must-revalidate" => true
				);
				define("DISABLE_CACHE", true);
			}

			if(is_array($user_permission) && count($user_permission)) {
				define("IS_LOGGED", $user_permission["ID"]);

				if($start_session) {
					require_once(CACHE_DISK_PATH . "/conf/gallery/config/session.php");
					@session_unset();
	                @session_destroy();				
					
					session_save_path(SESSION_SAVE_PATH);
	                session_name(SESSION_NAME);

					session_regenerate_id(true);
					session_start();
		
					$sessionName = session_name(); 
					
					cache_session_share_for_subdomains();
					
					$sessionCookie = session_get_cookie_params();

					if(defined("SESSION_PERMANENT")) {
						$long_time = time() + (60 * 60 * 24 * 365);
						setcookie($sessionName, session_id(), $long_time, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
					} else {
						setcookie($sessionName, session_id(), $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
					}					

					$_REQUEST[$sessionName] = session_id();
					
					$_SESSION[APPID . "__FF_SESSION__"] 	= uniqid(APPID, true);
					$_SESSION[APPID . "Domain"] 			= $user_permission["domain"];
					$_SESSION[APPID . "DomainID"] 			= $user_permission["ID_domain"];
					$_SESSION[APPID . "UserNID"]			= $user_permission["ID"];
					$_SESSION[APPID . "UserID"]				= $user_permission["username"];
					$_SESSION[APPID . "UserLevel"] 			= $user_permission["level"];
					$_SESSION[APPID . "UserEmail"] 			= $user_permission["email"];

					$_SESSION[APPID . "user_permission"] 	= $user_permission;
				}
			}
		}
		
		return $user_permission;
	}   
    function cache_get_session($superadmin_user = null) {
        static $user_permission = null;

        if($user_permission === null) {
            $user_permission = array();

			require_once(CACHE_DISK_PATH . "/conf/gallery/config/session.php");
            if($_REQUEST[SESSION_NAME])
                $sessid = $_REQUEST[SESSION_NAME];
            if(!$sessid)
            	$sessid = $_COOKIE[SESSION_NAME];
            	
			if($sessid)
			{
                $tmp_path = SESSION_SAVE_PATH;
                    if (substr($tmp_path, -1) !== "/")
                            $tmp_path .= "/";

                if(file_exists($tmp_path . "sess_" . $sessid))
                {
                    @session_unset();
                    @session_destroy();

                    session_save_path(SESSION_SAVE_PATH);
                    session_name(SESSION_NAME);

                    @session_start();


                    $user_permission = $_SESSION[APPID . "user_permission"];
                    
                } else {
                	cache_token_destroy_session_cookie(SESSION_NAME);
                }
            }

            if(!count($user_permission)) {
	            $user_permission = cache_create_session_by_token();
            }
							
			if(count($user_permission)) {
				if($_SESSION[APPID . "UserNID"] != 2)
					define("IS_LOGGED", $_SESSION[APPID . "UserNID"]);

				if($superadmin_user === null) {
					require_once(CACHE_DISK_PATH . "/conf/gallery/config/admin.php");

					$superadmin_user = SUPERADMIN_USERNAME;
				}

				if($_SESSION[APPID . "UserID"] == $superadmin_user
					|| $user_permission["primary_gid_name"] == "data entry" //TODO: da togliere quando i sid non esisteranno piu
				) {
					define("DISABLE_CACHE", true);
				}				
				
			}
        }

        return $user_permission;
    }
      
    function cache_get_locale_settings($user_permission = null) {
        static $arrLocale = null;

        if(!is_array($arrLocale)) {
            if($user_permission === null)
                $user_permission = cache_get_session();

            if(is_array($user_permission) && count($user_permission) && is_array($user_permission["lang"]) && count($user_permission["lang"])) {
                $arrLocale["lang"]         = $user_permission["lang"];
                $arrLocale["country"]     = $user_permission["country"];
                $arrLocale["rev"]         = $user_permission["rev"];
            } else {
            	$arrLocale = cache_get_settings("locale");
			}
			
			if(is_array($arrLocale["lang"][LANGUAGE_DEFAULT])) {
				$arrLocale["lang"]["current"] = $arrLocale["lang"][LANGUAGE_DEFAULT];
				$arrLocale["lang"]["current"]["code"] = LANGUAGE_DEFAULT;
			}
        }

        return $arrLocale;
    }
      
    function cache_get_locale(&$page, $domain_name = null) {
        static $localeLoaded = null;
        
        if($localeLoaded) {
            $locale = $localeLoaded;
        } else {
            require_once(CACHE_DISK_PATH . "/conf/gallery/config/other.php");

            $locale = cache_get_locale_settings($page["session"]);

            //recupero della lingua dai cookie
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" && strlen($_COOKIE["lang"]))
                $lang = strtoupper($_COOKIE["lang"]);

            //recupero della lingua se forzata per le aree riservate
            if(!$lang && defined("LANGUAGE_RESTRICTED_DEFAULT") && $page["restricted"]) {
                $lang = LANGUAGE_RESTRICTED_DEFAULT;

                if(!defined("LANGUAGE_DEFAULT_TINY"))
                    define("LANGUAGE_DEFAULT_TINY", strtolower(substr(LANGUAGE_RESTRICTED_DEFAULT, 0, 2)));
            }

            //recupero della lingua dal percorso base del sito es: /en/[path_info]
            if(!$lang)
                $lang = $page["lang"];

            //recupero della lingua dal nome dominio del sito es: .it .ru
            if(!$lang && $domain_name !== false)
            {
            	if($domain_name === null)
            	{
	                if(strpos(strtolower($_SERVER["HTTP_HOST"]), "www.") === 0) {
	                    $domain_name = substr($_SERVER["HTTP_HOST"], strpos($_SERVER["HTTP_HOST"], ".") + 1);    
	                } else {
	                    $domain_name = $_SERVER["HTTP_HOST"];
	                }

                    if(strpos($domain_name, ":") !== false)
                        $domain_name = substr($domain_name, 0, strpos($domain_name, ":"));
	            }
                //$arrState = cache_get_state_available();
                $lang = $locale["rev"]["country"][strtolower(substr($domain_name, strrpos($domain_name, ".") + 1))];
            }

            if(!$lang)
                $lang = $locale["lang"]["current"]["code"];

            if($locale["lang"][$lang]["ID"]) {
                $ID_lang = $locale["lang"][$lang]["ID"];
            } else {
                $lang = LANGUAGE_DEFAULT;
                $ID_lang = LANGUAGE_DEFAULT_ID;
            }

            if(!defined("LANGUAGE_DEFAULT_TINY"))   
                define("LANGUAGE_DEFAULT_TINY", strtolower(substr(LANGUAGE_DEFAULT, 0, 2)));

            if(!defined("FF_LOCALE"))
            {
                define("LANGUAGE_INSET_TINY", strtolower(substr($lang, 0, 2)));
                define("LANGUAGE_INSET", $lang);
                define("LANGUAGE_INSET_ID", $ID_lang);
                define("FF_LOCALE", $lang);        
            }            

            $locale["prefix"] = ($lang == LANGUAGE_DEFAULT
                                    ? ""
                                    : "/" . $locale["lang"][$lang]["tiny_code"]
                                );
            $locale["lang"]["current"] =  $locale["lang"][$lang];
            
            $localeLoaded = $locale;
        }
        
        if($locale["prefix"])
        {
            if($page["user_path"] == $locale["prefix"])
                $page["user_path"] = "/";
            elseif(strpos($page["user_path"], $locale["prefix"]) === 0)
                $page["user_path"] = substr($page["user_path"], strlen($locale["prefix"]));
        }

        return $locale;
    }
    
    function cache_check_lang($page) {
        if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
            $lang = strtoupper($_GET["lang"]);

            if($lang) {
                //if(!defined("FF_DEFAULT_CHARSET"))
                //    define("FF_DEFAULT_CHARSET", "UTF-8");
                require_once(CACHE_DISK_PATH . "/ff/main.php");
                require_once(CACHE_DISK_PATH . "/conf/gallery/init.php");        

                $path_info = $_SERVER["PATH_INFO"];
                if($path_info == "/index")
                    $path_info = "/";
                
                $prefix = ($lang == LANGUAGE_DEFAULT
                            ? ""
                            : "/" . $page["locale"]["lang"][$lang]["tiny_code"]
                );

                require_once(CACHE_DISK_PATH . "/library/gallery/common/get_international_settings_path.php");
                require_once(CACHE_DISK_PATH . "/library/gallery/common/normalize_url.php");
                require_once(CACHE_DISK_PATH . "/library/gallery/common/write_notification.php");
                require_once(CACHE_DISK_PATH . "/library/gallery/process/html_page_error.php");
                
                $res = get_international_settings_path($page["user_path"], FF_LOCALE);
                
				do_redirect(normalize_url($res["url"], HIDE_EXT, true, $lang, $prefix));
                //cache_send_header_content(null, false, false, false);
                //header('Location:' . normalize_url($res["url"], HIDE_EXT, true, $lang, $prefix), true, 301);
                //exit;        
        
            } elseif(FF_LOCALE == LANGUAGE_DEFAULT) {
                if(isset($_COOKIE["lang"])) {
                    unset($_COOKIE['lang']);
                    setcookie('lang', null, -1, '', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
                }
            } elseif($_COOKIE["lang"] != FF_LOCALE) {
                $_COOKIE['lang'] = FF_LOCALE;
                setcookie("lang", FF_LOCALE, 0, '', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
            }  
        }    
    
    }
        
    function cache_check_ff_contents($user_path, $last_update = null) {
        $res = array();
        if(is_file(CACHE_DISK_PATH . "/contents" . $user_path)) {
             $res["count"]++;
             if($last_update && filemtime(CACHE_DISK_PATH . "/contents" . $user_path) > $last_update)
                 $res["cache_invalid"] = true;
        } elseif(is_dir(CACHE_DISK_PATH . "/contents" . $user_path)) {
            $fs_contents = glob(CACHE_DISK_PATH . "/contents" . $user_path . "/*");
            if(is_array($fs_contents) && count($fs_contents)) {
                foreach($fs_contents AS $file) {
                    $file_name = pathinfo($file, PATHINFO_BASENAME);
                    if(strtolower($file_name) == "index") {
                         $res["count"]++;
                         if($last_update && filemtime($file) > $last_update)
                             $res["cache_invalid"] = true;

                        break;
                    }
                }
            }
        }

        return $res;
    }
    
    function cache_get_request($get, $post = null, $rule = null) {
        $res = null;
        $arrRuleGet = array();
        $filter = array(
            "ffl" => true
            , "pci" => true
            , "ppi" => true
            , "pri" => true
            , "psi" => true
            , "pcn" => true
            , "ppn" => true
            , "prn" => true
            , "psn" => true
            , "pps" => true
            , "pss" => true
        );
        

        /**
         * COOKIE
         */
        if(is_array($_COOKIE) && count($_COOKIE)) {
            $cookie_match = array_intersect_key($_COOKIE, $filter);
            foreach($cookie_match AS $cookie_key => $cookie_value) {
                if(!$get[$cookie_key])
                    $get[$cookie_key] = $cookie_value;
            }
        }
        /**
        * GET
        */  
        if(is_array($get) && count($get)) {
            if($rule["get"] === true) {
		        $res["get"]["query"]["all"] = http_build_query($get);
		    } elseif($rule["get"] === "xhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
		        $res["get"]["query"]["all"] = http_build_query($get);
		    } elseif($rule["get"] === "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
		        $res["get"]["query"]["all"] = http_build_query($get);
            } else {
            	if(is_array($rule["get"]))
            		$arrRuleGet = array_flip($rule["get"]);

                foreach($get AS $req_key => $req_value) {
                    if(is_array($get[$req_key]))
                        continue;

                    if(is_array($req_value)) 
                        continue;

/* assrde condizioni
					if(!$arrRuleGet && !strlen($req_value))
						continue;
						
                    if(!$arrRuleGet && is_numeric($req_key) && !$req_value)
                        continue;
*/
                    switch($req_key) {
                    	case "_ffq_":
                    		break;
                        case "gclid": //params di google adwords e adsense
                        case "utm_source":
                        case "utm_medium":
                        case "utm_campaign": 
                        case "utm_term": 
                        case "utm_content": 
                            $res["get"]["gparams"][$req_key] = $req_value;
                            break;
                        case "q": 
                            $res["get"]["search"]["term"] = $req_value;
                            $res["get"]["search"]["params"]["q"] = "q=" . urlencode($req_value);
                            
                            $res["get"]["query"]["q"] = $res["get"]["search"]["params"]["q"]; //forse da aggiungere "q=" come negli altri sotto
                            break;
                        case "page":
                            if(is_numeric($req_value) && $req_value > 0) {
                                $res["get"]["navigation"]["page"] = $req_value;  
                                if($req_value > 1)
                                    $res["get"]["query"]["page"] = "page=" . urlencode($res["get"]["navigation"]["page"]);
                            }
                            break;
                        case "count":
                            if(is_numeric($req_value) && $req_value > 0) {
                                $res["get"]["navigation"]["rec_per_page"] = $req_value;
                                
                                $res["get"]["query"]["count"] = "count=" . urlencode($res["get"]["navigation"]["rec_per_page"]);
                            }
                            break;
                        case "sort":
                            $res["get"]["sort"]["name"] = $req_value;
                            
                            $res["get"]["query"]["sort"] = "sort=" . urlencode($res["get"]["sort"]["name"]);
                            break;
                        case "dir":
                            $res["get"]["sort"]["dir"] = $req_value;
                            
                            $res["get"]["query"]["dir"] = "dir=" . urlencode($res["get"]["sort"]["dir"]);
                            break;
                        case "ffl": //Filter By Letter //gestire meglio i filtri. troppo macchinoso 
                            $res["get"]["filter"]["first_letter"] = $req_value;
                            
                            $res["get"]["query"]["ffl"] = "ffl=" . urlencode($res["get"]["filter"]["first_letter"]);
                            break;
                        case "pci": //Filter By Place City ID
                            $res["get"]["filter"]["place"]["city"]["ID"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["pci"] = "pci=" . urlencode($res["get"]["filter"]["place"]["city"]["ID"]);                        
                            break;
                        case "ppi": //Filter By Place Province ID
                            $res["get"]["filter"]["place"]["city"]["ID_province"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["ppi"] = "ppi=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_province"]);                        
                            break;
                        case "pri": //Filter By Place Region ID
                            $res["get"]["filter"]["place"]["city"]["ID_region"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["pri"] = "pri=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_region"]);                        
                            break;
                        case "psi": //Filter By Place State ID
                            $res["get"]["filter"]["place"]["city"]["ID_state"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["psi"] = "psi=" . urlencode($res["get"]["filter"]["place"]["city"]["ID_state"]);                        
                            break;
                        case "pcn": //Filter By Place City Smarturl
                            $res["get"]["filter"]["place"]["city"]["smart_url"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["pcn"] = "pcn=" . urlencode($res["get"]["filter"]["place"]["city"]["smart_url"]);
                            break;
                        case "ppn": //Filter By Place Province Smarturl
                            $res["get"]["filter"]["place"]["city"]["province_smart_url"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["ppn"] = "ppn=" . urlencode($res["get"]["filter"]["place"]["city"]["province_smart_url"]);
                            break;
                        case "prn": //Filter By Place Region Smarturl
                            $res["get"]["filter"]["place"]["region"]["smart_url"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["prn"] = "prn=" . urlencode($res["get"]["filter"]["place"]["region"]["smart_url"]);
                            break;
                        case "psn": //Filter By Place State Smarturl
                            $res["get"]["filter"]["place"]["state"]["smart_url"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["psn"] = "psn=" . urlencode($res["get"]["filter"]["place"]["state"]["smart_url"]);                        
                            break;
                        case "pps": //Filter By Place Province Sigle
                            $res["get"]["filter"]["place"]["city"]["province_sigle"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["pps"] = "pps=" . urlencode($res["get"]["filter"]["place"]["city"]["province_sigle"]);                        
                            break;                            
                        case "pss": //Filter By Place State Sigle
                            $res["get"]["filter"]["place"]["state"]["sigle"] = cache_nomalize_request($req_value);
                            $res["get"]["query"]["pss"] = "pss=" . urlencode($res["get"]["filter"]["place"]["state"]["sigle"]);                        
                            break;                            
                        case "ret_url":
                        case "lang":
                            break;
                        default:
			                if(isset($arrRuleGet[$req_key])) {
								$res["get"]["search"]["available_terms"][$req_key] = $req_value;
			                    $res["get"]["query"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
							} elseif($arrRuleGet[$req_key] === false) {
								$res["get"]["invalid"][$req_key] = $req_key . "=" . urlencode($req_value);
							} elseif(!preg_match('/[^a-z\-0-9]/i', $req_key)) {
	                            $res["get"]["search"]["available_terms"][$req_key] = $req_value;
	                            //$res["get"]["query"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
	                            $res["get"]["invalid"][$req_key] = $req_key . "=" . urlencode($res["get"]["search"]["available_terms"][$req_key]);
	                        }
                    }
                }

                if(is_array($res["get"]["query"]) && count($res["get"]["query"]) != count($get))
                    $rule["get"] = false;    
            }

            if(is_array($rule["nocache"])) {
                foreach($rule["nocache"] AS $req_key) {
                    if(array_key_exists($req_key, $get)) {
                        $res["nocache"] = true;
                        break;
                    }
                }
            } elseif($rule["nocache"] === true) {
                if(is_array($res["get"]["query"]) && count($res["get"]["query"]))
                    $res["nocache"] = true;
            }            
            
            
            //ricava la query valida
            if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
                if(!(defined("DEBUG_MODE") && (isset($get["__nocache__"]) || isset($get["__debug__"]) || isset($get["__query__"])))) {
                    if(is_array($res["get"]["invalid"]) && count($res["get"]["invalid"])
                        || ($rule["get"] === false && count($get) != count($res["get"]["query"]))
                    ) {
                        $res["valid"] = (is_array($res["get"]["query"]) && count($res["get"]["query"])
                            ? "?" . implode("&", $res["get"]["query"])
                            : ""
                        );
                    }
                }
            }
            
            
            if($rule["log"]) {
                if(is_array($res["get"]["invalid"])) 
                    cache_writeLog("URL: " . $_SERVER["PATH_INFO"] . (is_array($res["get"]["query"]) ? " GET: " . implode("&", $res["get"]["query"]) : "") . " GET INVALID: " . implode("&", $res["get"]["invalid"]) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
                elseif($rule["get"] === false) 
                    cache_writeLog("URL: " . $_SERVER["PATH_INFO"] . " GET: " . http_build_query($get) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
            }            
        }

        /**
        * POST
        */
        if(is_array($post) && count($post)) {
            if($rule["post"]) {
                if(is_array($rule["post"])) {
                    foreach($rule["post"] AS $req_key) {
                        if(array_key_exists($req_key, $post)) {
                            $res["post"]["search"]["available_terms"][$req_key] = $post[$req_key];
                            if(is_array($res["post"]["search"]["available_terms"][$req_key]))
                                $res["post"]["query"][$req_key] = $req_key . "=" . http_build_query($res["post"]["search"]["available_terms"][$req_key]);
                            else
                                $res["post"]["query"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                        }
                    }
                    
                    foreach($post AS $req_key => $req_value) {
                        if(array_search($req_key, $rule["post"]) === false) { 
                            if(is_array($req_value))
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . http_build_query($req_value);
                            else
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . urlencode($req_value);
                        }
                    }
                } elseif($rule["post"] === true) {
                    $res["post"]["query"]["all"] = http_build_query($post);
                } elseif($rule["post"] === "xhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                    $res["post"]["query"]["all"] = http_build_query($post);
                } elseif($rule["post"] === "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {
                    $res["post"]["query"]["all"] = http_build_query($post);
                }
            } else {
                if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
                    $post = array();

                foreach($post AS $req_key => $req_value) {
                    if(is_array($post[$req_key]))
                        continue;
                                        
                    switch($req_key) {
                        case "ret_url":
                        case "lang":
                            break;
                        default:
                            if($rule["post"] === true || !preg_match('/[^a-z\-0-9]/i', $req_key)) {
                                $res["post"]["search"]["available_terms"][$req_key] = $req_value;
                                $res["post"]["query"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                            } else {
                                $res["post"]["invalid"][$req_key] = $req_key . "=" . urlencode($res["post"]["search"]["available_terms"][$req_key]);
                            }
                    }
                }

                if($rule["post"] === false)                
                    $res["post"] = null;

            }
            
            if(is_array($res["post"]["query"]) && count($res["post"]["query"]))
                $res["nocache"] = true;
                        
            if($rule["log"]) {
                if(is_array($res["post"]["invalid"])) 
                    cache_writeLog("URL: " . $_SERVER["PATH_INFO"] . (is_array($res["post"]["query"]) ? " POST: " . implode("&", $res["post"]["query"]) : "") . " POST INVALID: " . implode("&", $res["post"]["invalid"]) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
                elseif($rule["post"] === false) 
                    cache_writeLog("URL: " . $_SERVER["PATH_INFO"] . " GET: " . http_build_query($post) . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_request");
            }            
        }
        
        return $res;
    }
    function cache_nomalize_request($request, $sep = ",") {
        if($sep && strpos($request, $sep) !== false) {
            $arrRequest = explode($sep, $request);
            foreach($arrRequest AS $value) {
                $res[] = cache_url_rewrite($value);
            }
        } else {
            $res = cache_url_rewrite($request);
        }
        return $res;
    }
	function cache_url_rewrite($testo, $char_sep = '-')
	{
		//$testo = cache_toggle_hypens($testo);
		$testo = cache_remove_accents($testo);

		$testo = strtolower($testo); 

		//$testo = preg_replace('([^a-z0-9\-]+)', ' ', $testo);
		$testo = preg_replace('/[^\p{L}0-9\-]+/u', ' ', $testo); 
		$testo = trim($testo);
		$testo = preg_replace('/ +/', $char_sep, $testo);
		$testo = preg_replace('/-+/', $char_sep, $testo);
		/*do {
		    $testo = str_replace("--", "-", $testo, $count);
		} while ($count > 0);*/
		return $testo;
	}

	function cache_seems_utf8($str) {
		$length = strlen($str);
		for ($i=0; $i < $length; $i++) {
			$c = ord($str[$i]);
			if ($c < 0x80) $n = 0; # 0bbbbbbb
			elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
			elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
			elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
			elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
			elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
			else return false; # Does not match any model
			for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
				if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
					return false;
			}
		}
		return true;
	}

	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * @since 1.2.1
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	function cache_remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
			return $string;

		if (cache_seems_utf8($string)) {
			$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
			chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
			chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
			chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
			chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
			chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
			chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
			chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
			chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
			chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
			chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
			chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
			chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
			chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
			chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
			chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Decompositions for Latin Extended-B
			chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
			chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '',
			// Vowels with diacritic (Vietnamese)
			// unmarked
			chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
			chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
			// grave accent
			chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
			chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
			chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
			chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
			chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
			chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
			chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
			// hook
			chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
			chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
			chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
			chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
			chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
			chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
			chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
			chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
			chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
			chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
			chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
			chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
			// tilde
			chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
			chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
			chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
			chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
			chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
			chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
			chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
			chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
			// acute accent
			chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
			chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
			chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
			chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
			chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
			chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
			// dot below
			chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
			chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
			chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
			chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
			chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
			chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
			chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
			chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
			chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
			chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
			chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
			chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
			// Vowels with diacritic (Chinese, Hanyu Pinyin)
			chr(201).chr(145) => 'a',
			// macron
			chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
			// acute accent
			chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
			// caron
			chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
			chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
			chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
			chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
			chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
			// grave accent
			chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
			);

			// Used for locale-specific rules
			/*$locale = get_locale();

			if ( 'de_DE' == $locale ) {
				$chars[ chr(195).chr(132) ] = 'Ae';
				$chars[ chr(195).chr(164) ] = 'ae';
				$chars[ chr(195).chr(150) ] = 'Oe';
				$chars[ chr(195).chr(182) ] = 'oe';
				$chars[ chr(195).chr(156) ] = 'Ue';
				$chars[ chr(195).chr(188) ] = 'ue';
				$chars[ chr(195).chr(159) ] = 'ss';
			} elseif ( 'da_DK' === $locale ) {
				$chars[ chr(195).chr(134) ] = 'Ae';
 				$chars[ chr(195).chr(166) ] = 'ae';
				$chars[ chr(195).chr(152) ] = 'Oe';
				$chars[ chr(195).chr(184) ] = 'oe';
				$chars[ chr(195).chr(133) ] = 'Aa';
				$chars[ chr(195).chr(165) ] = 'aa';
			}*/

			$string = strtr($string, $chars);
		} else {
			// Assume ISO-8859-1 if not UTF-8
			$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
				.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
				.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
				.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
				.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
				.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
				.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
				.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
				.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
				.chr(252).chr(253).chr(255);

			$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

			$string = strtr($string, $chars['in'], $chars['out']);
			$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
			$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
			$string = str_replace($double_chars['in'], $double_chars['out'], $string);
		}

		return $string;
	}

    function cache_get_error_document($cache_error_path, $cache_filename, $params)
    {
        $arrUserPath = explode("/", $params["user_path"]);
        $errorDocumentFile = $cache_error_path . "/" . $arrUserPath[1] . ".php";
        $key = str_replace("/cache", "", $params["path"]) . "/" . $cache_filename;

        require_once (CACHE_DISK_PATH . "/library/gallery/classes/filemanager/Filemanager.php");
        $fs = new Filemanager("php");

        $page = $fs->read($key, $errorDocumentFile);

        return $page;
    }

    function cache_set_error_document($cache_error_path, $params)
    {
        $arrUserPath = explode("/", $params["user_path"]);
        $errorDocumentFile = $cache_error_path . "/" . $arrUserPath[1] . ".php";
        $key = $params["user_path"];

        require_once (CACHE_DISK_PATH . "/library/gallery/classes/filemanager/Filemanager.php");
        $fs = new Filemanager("php");

        return $fs->delete($key, $errorDocumentFile, Filemanager::SEARCH_IN_VALUE);
    }

    function cache_get_page_stats($page_cache_path)
    {
        require_once (CACHE_DISK_PATH . "/library/gallery/classes/filemanager/Filemanager.php");
        $fs = new Filemanager("php", $page_cache_path . "/stats.php");

        return $fs->read();
    }

    function cache_get_filename($params, $request = array()) {
        //require_once(CACHE_DISK_PATH . "/conf/gallery/config/path.php");

        $cache_valid = false;
        $cache_base_path = CACHE_DISK_PATH . $params["path"];
        $cache_filename = "index";

        $random = $params["settings"]["page"][$params["user_path"]]["rnd"];
        if($random)
            $cache_filename .= rand(1, $random);

        $cache_file_type = $params["type"];
        if($cache_file_type == "mixed")
        {
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                $cache_file_type = "json";
            else
                $cache_file_type = "html";
        }

        $accept_compress = (isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") === false
            ? false
            : true
        );

        if($params["compress"] && $accept_compress) {
            $cache_ext = "gz";
        } else {
            $cache_ext = $cache_file_type;
        }

        if($request["get"] && is_array($request["get"]["query"]))
            $cache_filename .= "_" . str_replace(array("&", "="), array("_", "-"), implode("&", $request["get"]["query"]));

        $cache_filename = preg_replace("/[^A-Za-z0-9\-_]/", '', $cache_filename);
        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
            $cache_filename .= "_XHR";

        $cache_storing_path = $cache_base_path;
        if(is_array($params["settings"]["rule"]) && count($params["settings"]["rule"])) {
            foreach($params["settings"]["rule"] AS $compare_path => $precision) {
                if(strpos($cache_base_path, $compare_path) !== false) {
                    $arrCacheSplit = explode($compare_path, $cache_base_path);
                    if(count($arrCacheSplit) > 1 && $arrCacheSplit[1]) {
                        $cache_storing_path = $arrCacheSplit[0] . $compare_path . "/" . substr(ltrim($arrCacheSplit[1], "/"), 0, $precision);
                        unset($arrCacheSplit[0]);
                        $cache_storing_path .= implode($compare_path, $arrCacheSplit);
                        break;
                    }
                }
            }
        }
        if(is_file($cache_storing_path . "/" . $cache_filename . "." . $cache_ext)) {
            $cache_file_exist = true;
            $last_update = filemtime($cache_storing_path . "/" . $cache_filename . "." . $cache_ext);
            $cache_last_version = filectime($cache_storing_path . "/" . $cache_filename . "." . $cache_ext);
            if(defined("CACHE_LAST_VERSION") && CACHE_LAST_VERSION > $cache_last_version)
                $cache_last_version = CACHE_LAST_VERSION;

            if($last_update >= $cache_last_version)
                $cache_valid = true;
            //} else {
            //    $cache_noexist = !is_dir($cache_storing_path);
        }

        if(!$cache_valid) {
            // $cache_error_path = $params["user_path"];
            if(!$cache_file_exist) {
                $arrUserPath = explode("/", $params["user_path"]);
                $cache_error_path = CACHE_DISK_PATH . $params["base"] . $params["settings"]["page"]["/error"]["cache_path"];
                $cache_file_error_exist = is_file($cache_error_path . "/". $arrUserPath[1] . ".php");
                $is_error_document = cache_get_error_document($cache_error_path, $cache_filename, $params);
            }
        }

        return array(
            "path"                  => $cache_base_path
            , "cache_path"          => $cache_storing_path
            , "error_path"          => $cache_error_path
            , "filename"            => $cache_filename
            , "primary"             => $cache_filename . "." . $cache_ext
            , "gzip"                => $cache_filename . ".gz"
            , "last_update"         => $last_update
            , "noexistfile"         => !$cache_file_exist
            , "noexistfileerror"    => !$cache_file_error_exist
            , "is_error_document"   => $is_error_document
            , "invalid"             => !$cache_valid
            , "compress"            => $accept_compress
            , "client"              => $params["client"]
            , "type"                => $cache_file_type
        );
    }


    function cache_send_header_content($type = null, $compress = null, $max_age = null, $expires = null, $length = null, $pragma = "!invalid")
    {
        //if(!defined("CM_PAGECACHE_KEEP_ALIVE"))
        //    define("CM_PAGECACHE_KEEP_ALIVE", true);

        //if(CM_PAGECACHE_KEEP_ALIVE)
        header("Connection: Keep-Alive");
        
        if($compress !== false && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false) {
            header("Content-Encoding: gzip");
        }

        switch($type)
        {
            case "xml":
                header("Content-type: text/xml");
            case "json":
                header("Content-type: text/javascript");
                break;
            case "html":
            default:
                header("Content-type: text/html; charset=UTF-8");

        }

        if ($expires !== false)
        {
            if ($expires === null)
            { //da abilitare il max age per vedere se gli piace a webpagetest
                $expires = time() + (60 * 60 * 24 * 7); 
                //$expires = time() + 7;  
            }
            elseif ($expires < 0)
            {
                $expires = time() - $expires;
            }
            $exp_gmt = gmdate("D, d M Y H:i:s", $expires) . " GMT";
            header("Expires: $exp_gmt");
        }
        
        if ($max_age !== false)
        {
            if ($max_age === null)
            {
                $max_age = 3;          
                header("Cache-Control: public, max-age=$max_age");
            }
            else
            {
                header("Cache-Control: public, max-age=$max_age");
            }
        }

        if($expires === false && $max_age === false)
        {
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

            $expires = time() - 1;
            $exp_gmt = gmdate("D, d M Y H:i:s", $expires) . " GMT";
            header("Expires: $exp_gmt");        
            
            $pragma = "no-cache";        
        } else {
            $mod_gmt = gmdate("D, d M Y H:i:s", time()) . " GMT";
            header("Last-Modified: $mod_gmt");        
        }
        
        if($pragma)
            header("Pragma: " . $pragma);
            
        if($length)
            header("Content-Length: " . $length);       
        
        header("Vary: Accept-Encoding"); 
    }
 
    function cache_send_header($file, $type = null, $compress = false, $max_age = null, $expires = null, $enable_etag = false) {
        //if (strlen($_SERVER["HTTP_IF_NONE_MATCH"]) && substr($_SERVER["HTTP_IF_NONE_MATCH"], 0, strlen($etag)) == $etag)
        //    $compress = false;
        cache_send_header_content($type, $compress, $max_age, $expires, filesize($file));

        if($enable_etag) {
            $etag = md5($file . filemtime($file) . ($enable_etag === true ? "" : $enable_etag));
            header("ETag: " . $etag);

            if (strlen($_SERVER["HTTP_IF_NONE_MATCH"]) && substr($_SERVER["HTTP_IF_NONE_MATCH"], 0, strlen($etag)) == $etag)
            {  
                /*if($max_age !== false) {
                    if($max_age === null)
                        $max_age = 60; 
                    $max_age_multiplier = ceil((time() - strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) / $max_age) * $max_age;
                    
                    header("Cache-Control: public, max-age=" . $max_age_multiplier);
                }*/

                cache_http_response_code(304);
                exit;
            }
        }
    }

    function cache_parse($cache_file, $lang, $group = "guests") {
        $target_file = "";
        $compress = false;
        $cache_path = ($cache_file["is_error_document"]
            ? $cache_file["error_path"]
            : $cache_file["cache_path"]
        );

        //$cache_file["compress"] = true;
        if(strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") === false) {
            $target_file = $cache_path . "/" . $cache_file["primary"];
        } elseif($cache_file["compress"] && $cache_file["gzip"]) {
            $compress = true;
            $target_file = $cache_path . "/" . $cache_file["gzip"];
        } else {
            $target_file = $cache_path . "/" . $cache_file["primary"];
        }

        if(strlen($target_file)) {
            // header_remove();
            //clearstatcache();

            /* if($cache_file["last_update"]) {
                 if(filemtime($target_file) != $cache_file["last_update"])
                     return false;
             } else {
                 if(filemtime($target_file) >= filectime($target_file))
                     return false;
             }*/
            //define("CACHE_PAGE_STORING_PATH", $cache_file["cache_path"] . "/" . $cache_file["filename"]);

            if($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" && defined("TRACE_VISITOR")) {
                require_once(CACHE_DISK_PATH . "/library/gallery/system/trace.php");
                system_trace("pageview");
            }

            $enable_etag = $lang . "_". $group;


            if($cache_file["client"] === false
                || ($cache_file["client"] == "noxhr" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                || ($cache_file["client"] == "nohttp" && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")
            ) {
                $expires = false;
                $max_age = false;
                $enable_etag = false;
            } elseif(defined("IS_LOGGED")) {
                $expires = null;
                $max_age = 3;
            } else {
                $expires = null;
                $max_age = 7;
            }
            /*
         if($_COOKIE["nocache"]) {
             $max_age = 0;
             $expires = -1;

             unset($_COOKIE["nocache"]);
             setcookie("nocache", null, -1, '/', $_SERVER["HTTP_HOST"], $_SERVER["HTTPS"], true);
         } else {
             //da gestire in funzione dei blocchi dinamici
              //   l'expire solo per le risorse statiche deve essere di 1 settimana.
              //   Per le risorse dinamiche di solito e a 0
              //   Ma per fare le cose corrette bisogna creare le pagine expire in funzione dei blochhi inseriti nel front end.
              //   Percui se ci sono blocchi che variano in funzione delle scelte come la lingua o il login ad esempio e necessario
              //   impostare un expire bassisimo (3 sec). Se invece nn ci sono questi blocchi percui il sito risulta un po piu statico
              //   allora e possibile mettere anche un 30 sec o 60.
              //   Tutto questo e da calcolare in cache page in creazione della cache

             $max_age = 0;
             if(isset($_COOKIE["language_inset"]))
                 $expires = -1;
             elseif($group == "guests")
                 $expires = null;
             else
                 $expires = -1;
         }*/
            if($_SESSION[APPID . "UserID"] == "debug")
                return false;

            cache_send_header($target_file, $cache_file["type"], $compress, $max_age, $expires, $enable_etag);
            readfile($target_file);

            if(defined("DEBUG_PROFILING"))
                profiling_stats("Cache lvl 1 (in cache) ");

            exit;
        }

        return false;
    }
    function cache_get_path($page, $user_permission = null) {
        $cache_path = false;

        if($page["cache"]) {
            if($user_permission === null && $page["session"] !== false)
                $user_permission = cache_get_session();

            if($page["cache"] === "guest") {
				$gid = ($user_permission["primary_gid_name"]
                            ? $user_permission["primary_gid_name"]
                            : $user_permission["primary_gid_default_name"]
                        );
				if(!$gid)
					$gid = "guests";

				if($_COOKIE["group"] != $gid) {
                    cache_session_share_for_subdomains();
                    
					$sessionCookie = session_get_cookie_params();
					setcookie("group", $gid, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
				}
                
                $cache_path = "/global";
			} else {
                if(defined("IS_LOGGED") && is_array($user_permission) && count($user_permission)) {
                    if($page["cache"] === "user") {
                        $auth_path = ($user_permission["username_slug"] ? $user_permission["username_slug"] : preg_replace("/[^a-z\-0-9]/i", "", $user_permission["username"]));
                        $cache_path = "/private";
                    } else {
                        $auth_path = ($user_permission["primary_gid_name"]
                            ? $user_permission["primary_gid_name"]
                            : $user_permission["primary_gid_default_name"]
                        );
                    }
                }
                                
                if(!$auth_path)
                    $auth_path = "guests";

                if(!$cache_path)                
                    $cache_path = "/public";
            }

            $cache_path .= $page["cache_path"] . (!$auth_path || ($auth_path == "guests" && $page["cache_path"])
                ? ""
                : "/" . $auth_path
            );
            
            /*
            switch($page["cache"]) {
                case "guest":    
                case "user":
                case true:
                case "group":

                    break;
                default:
                    $cache_path .= $page["cache"];
            }  */

            if(strpos(strtolower($_SERVER["HTTP_HOST"]), "www.") === 0) {
                $domain_name = substr($_SERVER["HTTP_HOST"], strpos($_SERVER["HTTP_HOST"], ".") + 1);    
            } else {
                $domain_name = $_SERVER["HTTP_HOST"];
            } 

            if(strpos($domain_name, ":") !== false)
            	$domain_name = substr($domain_name, 0, strpos($domain_name, ":"));
            
            //cache_get_locale($page["user_path"], $domain_name, $user_permission);

            if($cache_path)
                $cache_base = "/cache" . "/" . $domain_name . "/" . strtolower(FF_LOCALE) . $cache_path;

            return array(
                "base" => $cache_base
                , "group" =>  $page["group"]
                , "path" => $cache_base . ($page["user_path"] == "/" ? "" : $page["user_path"])
                , "user_path" => $page["user_path"]
                , "redirect" => $page["redirect"]
                , "alias" => $page["alias"]
                , "auth" => $auth_path
                , "lang" => FF_LOCALE
                , "type" => $page["type"]
                , "compress" => $page["compress"]
                , "locale" => $page["locale"]
                , "client" => $page["cache_client"]
            );
        }

        return $page["exit"];
    }
    
    function cache_sem_get_params($namespace = null, $xhr = null) {
    	require_once(CACHE_DISK_PATH . "/conf/gallery/config/session.php");
    	if(!defined("APPID_SEM")) 
    		define("APPID_SEM", substr(preg_replace("/[^0-9 ]/", '', APPID), 0, 4));
		
		$max = 1;
		$remove = false;
		if($xhr === null)
			$xhr = $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";

    	if(strlen($namespace) && is_numeric($namespace)) {
    		$key = $namespace;
    		$max = 10;
    		$remove = true;
		} elseif($namespace == "create") {
    		$key = 1;
    		$max = 4;
		} elseif($namespace == "update") {
    		$key = 2;
    		$max = 3;
    	} elseif(strlen($namespace) && is_string($namespace)) {
    		$key = preg_replace("/[^0-9 ]/", '', hexdec(md5($namespace)));
    		$remove = true;
		} else {
			if($xhr) {
    			$key = 4;
    			$max = 25;
			} else {
    			$key = 0; 
    			$max = 15;
			}
		}
		
    	return array(
    			"key" => APPID_SEM . $key
    			, "max" => $max
    			, "remove" => $remove
    		);    
    }
    
     function cache_sem($namespace = null, $nowait = false, $max = null) {
     	$acquired = true;
     	if(defined("DISABLE_CACHE")) 
			return array("acquired" => true); //nn funziona    

		if(function_exists("sem_get")) {
			if(defined("DEBUG_MODE") && isset($_REQUEST["__nocache__"])) {
				cache_sem_remove($namespace);
			} else {
				$params = cache_sem_get_params($namespace);
				if($max === null)
					$max = $params["max"];

				$sem = @sem_get($params["key"], $max, 0666, false);
				if($sem !== false) {
					if(version_compare(phpversion(), "5.6.1", "<"))
						$acquired = @sem_acquire($sem);
					else
						$acquired = @sem_acquire($sem, $nowait);
					
					cache_writeLog("GET:" . print_r(array(
						"res" => $sem
						, "acquired" => $acquired
						, "namespace" => $namespace
						, "max" => $max
						, "key" => $params["key"]
						, "remove" => $params["remove"]
					), true), "log_sem");						
				} else {
					cache_writeLog($namespace . " ERROR: " . print_r(error_get_last(), true), "log_error_sem");
				}
			}
		}

		return array(
			"res" => $sem
			, "acquired" => $acquired
			, "namespace" => $namespace
			, "key" => $params["key"]
			, "remove" => $params["remove"]
		);
    }
    function cache_sem_release(&$arrSem, $message = null) {
    	if(function_exists("sem_release")) {
    		if(is_array($arrSem) && count($arrSem)) {
    			foreach($arrSem AS $key => $sem) {
    				if($sem["res"] && $sem["acquired"]) {
					    $released = @sem_release($sem["res"]);
    					if($sem["remove"] && $released !== false) 
					    	$removed = @sem_remove($sem["res"]);    				
						
					    cache_writeLog("Release:" . $released . " " . ($sem["remove"] && $released !== false ? "Removed: " . $removed . " " : "") . $message . " of: " . print_r($sem, true) . ($released === false ? " ERROR: " . print_r(error_get_last(), true) : ""), "log_sem");

					    unset($arrSem[$key]);
					}
    			}
    		}
		}
    }
    function cache_sem_remove($namespace = null) {
    	//return;
		if(function_exists("sem_get")) {
    		$params = cache_sem_get_params($namespace);	
			$sem = @sem_get($params["key"]);
			if($sem) {
				$is_removed = @sem_remove($sem);
				cache_writeLog("ID: " . $params["key"] . " namespace: " . $namespace . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
			}
			if($namespace != "create") {
				$params = cache_sem_get_params("create");
				$sem = @sem_get($params["key"]);	
				if($sem) {
					$is_removed = @sem_remove($sem);
					cache_writeLog("ID: " . $params["key"] . " namespace: " . "create" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
				}
			}
			if($namespace != "update") {
				$params = cache_sem_get_params("update");	
				$sem = @sem_get($params["key"]);
				if($sem) {
					$is_removed = @sem_remove($sem);
					cache_writeLog("ID: " . $params["key"] . " namespace: " . "update" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
				}
			}
			if($namespace) {
				$params = cache_sem_get_params();	
				$sem = @sem_get($params["key"]);
				if($sem) {
					$is_removed = @sem_remove($sem);
					cache_writeLog("ID: " . $params["key"] . " namespace: " . "default" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
				}

				$params = cache_sem_get_params(null, true);	
				$sem = @sem_get($params["key"]);
				if($sem) {
					$is_removed = @sem_remove($sem);
					cache_writeLog("ID: " . $params["key"] . " namespace: " . "default XHR" . " " . ($is_removed ? "REMOVED" : "NO EXIST"), "log_sem");
				}
			}
		}
    }

    function cache_http_response_code($code = null)
    {
        if (!function_exists("http_response_code"))
        {
            if (!defined("FF_DISK_PATH"))
                define("FF_DISK_PATH", CACHE_DISK_PATH);

            require_once(FF_DISK_PATH . "/ff/common.php");
        }

        return http_response_code($code);
    }

    function cache_check_allowed_path($path_info, $do_redirect = true)
    {
        $schema = cache_get_settings();

        if(is_array($schema["error"]["rules"]) && count($schema["error"]["rules"]))
        {
            foreach($schema["error"]["rules"] AS $rule => $action)
            {
                $src = (strpos($rule, "[") === false
                    ? str_replace("\*", "(.*)", preg_quote($rule, "#"))
                    : $rule
                );
                if(preg_match("#" . $src . "#i", $path_info, $matches)) {
                    if(is_numeric($action)) {
                        cache_http_response_code($action);

                        cache_writeLog(" RULE: " . $rule . " ACTION: " . $action . " URL: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_error_badpath");
                        exit;
                    } elseif($do_redirect && $action) {
                        $redirect = $action;
                        if(strpos($src, "(") !== false && strpos($action, "$") !== false)
                            $redirect = preg_replace("#" . $src . "#i", $action, $path_info);

                        do_redirect($_SERVER["HTTP_HOST"] . $redirect);
                    }
                }
            }
        }

        return $path_info;
    }

    function check_static_cache_page($save_path = null, $status_code = null) {
    	static $arrSem = array();
        static $init = false;

        if(!$init) {
			//clearstatcache(); //nn so
            //Previene il blocco della pagina in caso di errori o di interruzione non gestita
            register_shutdown_function(function() use(&$arrSem) { 
                    cache_sem_release($arrSem, "shutdown");
            });        
			
			$init = true;
        }
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


        if($save_path) {
            $path_info = $save_path;
        } else {
            $path_info = cache_check_allowed_path($_SERVER["PATH_INFO"]);
        }

        $cache_params = cache_get_path(cache_get_page_properties($path_info));
		if(is_array($cache_params)) { //da verificare il nocache
            $schema = cache_get_settings();

            $rule["path"] = $cache_params["user_path"];

	        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") 
	            $rule["path"] = $cache_params["strip_path"] . $rule["path"];

	        $request_path = rtrim($cache_params["alias"] . $rule["path"], "/");
	        if(!$request_path)
	        	$request_path = "/";

			$rule["split_path"] = explode("/", $request_path);
	        if(isset($schema["request"][$request_path])) {
            	$rule["match"] = $schema["request"][$request_path];
			//} elseif(0 && isset($schema["request"]["/" . $rule["split_path"][0]])) {
		    //    $rule["match"] = $schema["request"]["/" . $rule["split_path"][0]];
		    } elseif(isset($schema["request"][$rule["split_path"][count($rule["split_path"]) - 1]])) {
		        $rule["match"] = $schema["request"][$rule["split_path"][count($rule["split_path"]) - 1]];
	        } else {

			    do {
			    	$request_path = dirname($request_path);
			        if(isset($schema["request"][$request_path])) {
			            $rule["match"] = $schema["request"][$request_path];
			            break;
			        }
			    } while($request_path != DIRECTORY_SEPARATOR);
			}

			if($rule["match"]["ext"] && is_array($schema["request"][$rule["match"]["ext"]]))
				$rule["match"] = array_merge_recursive($schema["request"][$rule["match"]["ext"]], $rule["match"]);
			
	        $request = cache_get_request($_GET, $_POST, $rule["match"]);
	        if($request["nocache"]) {
	            $cache_params = false;
	        } elseif(isset($request["valid"])) { 
	            $path_info = $_SERVER["PATH_INFO"];
	            if($path_info == "/index")
	                $path_info = "";           

	            do_redirect($_SERVER["HTTP_HOST"] . $path_info . $request["valid"]);
	        }
			//necessario XHR perche le request a servizi esterni path del domain alias non triggerano piu			
	        if(!$save_path && $cache_params["redirect"] && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest") {// Evita pagine duplicate quando i link vengono gestiti dagli alias o altro
        		do_redirect($cache_params["redirect"] . $request["valid"]);
			}
		}

		//Gestisce gli errori delle pagine provenienti da apachee con errorDocument nell'.htaccess
		if($cache_params === true) {
			cache_send_header_content(null, false, false, false);
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                cache_http_response_code(500);
			} else {
				$cache_media["url"] = "/media";
				$cache_media["path"] = "/cache/_thumb";
				$cache_media["theme_dir"] = "/themes";
				$cache_media["theme"] = "site";

				if(strpos($_SERVER["REQUEST_URI"], $cache_media["url"]) === 0) {
					do_redirect(str_replace("/media", $_SERVER["HTTP_HOST"] . "/cm/showfiles.php", $_SERVER["REQUEST_URI"]), 307);
				}

                cache_http_response_code(404);
			}

            cache_writeLog(" URL: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"], "log_error_apache");
	        exit;
		}  

		if(!$cache_params) {
            if($cache_params === false && !$save_path) {
                 define("DISABLE_CACHE", true);
			}
            return false;
        }

        $cache_params["settings"] = $schema;

        cache_check_lang($cache_params);
        $cache_file = cache_get_filename($cache_params, $request);

        if(!$save_path && $cache_file["invalid"]) {
            if($cache_file["is_error_document"] && !$cache_file["noexistfileerror"] && $cache_file["noexistfile"]) {
                $cache_file["invalid"] = false;
                $cache_file["filename"] = "index";
                if($cache_file["compress"])
                    $cache_file["primary"] = "index.gz";
                else
                    $cache_file["primary"] = "index.html";

                $cache_file["gzip"] = "index.gz";
            } elseif($cache_file["noexistfile"]) {
                $arrSem[] = cache_sem($cache_file["cache_path"]);
                if (is_file($cache_file["cache_path"] . "/" . $cache_file["primary"])) {
                    $cache_file["invalid"] = false;
                } else {
                    $arrSem[] = cache_sem("create");
                }
            } elseif($cache_file["noexistfileerror"]) {
                $arrSem[] = cache_sem($cache_file["error_path"]);
            } elseif(!defined("DISABLE_CACHE")) {
                if(is_array($schema["priority"]) && array_search($path_info, $schema["priority"]) !== false) {
                    @touch($cache_file["cache_path"] . "/" . $cache_file["primary"], time() + 10); //evita il multi loading di pagine in cache
                    cache_writeLog($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update_primary");
                } else {
                    $sem = cache_sem("update", true);
                    if($sem["acquired"]) {
                        @touch($cache_file["cache_path"] . "/" . $cache_file["primary"], time() + 10); //evita il multi loading di pagine in cache
                        cache_writeLog($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update");
                    } else {
                        $cache_file["invalid"] = false;
                        cache_writeLog($cache_file["cache_path"] . "/" . $cache_file["primary"] . "  " . filemtime($cache_file["cache_path"] . "/" . $cache_file["primary"]) . " => " . (time() + 10), "update_queue");
                    }
                    $arrSem[] = $sem;
                }
            }
        }

        if($cache_file["invalid"]) {
            //define("DISABLE_CACHE", true);
            $cache_exit = true;
        } else {
            $ff_contents = cache_check_ff_contents($path_info, $cache_file["last_update"]);
            if($ff_contents["cache_invalid"]) {
                //define("DISABLE_CACHE", true);
                $cache_exit = true;
            }
        }

        if($save_path) {
            // cache_sem_release($arrSem);
            return array(
                "file" => $cache_file
            , "user_path" => $path_info
            , "params" => $cache_params
            , "request" => $request
            , "ff_count" => $ff_contents["count"]
            , "sem" => &$arrSem
            );
        }

        if(defined("DEBUG_MODE") && isset($_REQUEST["__nocache__"])) {
            $_REQUEST["__CLEARCACHE__"] = true;
            define("DISABLE_CACHE", true);

            cache_sem_remove($cache_file["cache_path"]);
            cache_send_header_content(null, false, false, false);

            if($cache_file["error_path"])
                cache_set_error_document($cache_file["error_path"], $cache_params);

            if(is_file($cache_file["cache_path"] . "/" . $cache_file["primary"])) {
                if($cache_file["primary"] != $cache_file["gzip"])
                    @unlink($cache_file["cache_path"] . "/" . $cache_file["primary"]);

                @unlink($cache_file["cache_path"] . "/" . $cache_file["gzip"]);
            }
        }

        if($cache_exit) {
            $load = sys_getloadavg();
            if ($load[0] > 80) {
                cache_sem_release($arrSem);

                cache_send_header_content(null, false, false, false);
                cache_http_response_code(503);

                readfile(CACHE_DISK_PATH . "/themes/gallery/contents/error_cache.html");
                exit;
            } else {
                if(!count($arrSem))
                    $arrSem[] = cache_sem();

                return false;
            }
        }

        cache_sem_release($arrSem);


        if(!defined("DISABLE_CACHE"))
        {
            if($cache_file["is_error_document"])
            {
                //redirect
                if(!defined("FF_DISK_PATH"))
                    define("FF_DISK_PATH", CACHE_DISK_PATH);

                require_once(FF_DISK_PATH . "/conf/gallery/config/db.php");
                require_once(FF_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
                require_once(FF_DISK_PATH . "/library/gallery/system/gallery_redirect.php");

                system_gallery_redirect($path_info, $request["valid"]);
                if($status_code === null)
                {
                    cache_http_response_code($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"
                        ? 500
                        : 404
                    );
                }
            }

            cache_parse($cache_file, $cache_params["lang"], $cache_params["auth"], $request["get"]);
        }
    }