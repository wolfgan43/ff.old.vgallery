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
function system_lib_facebook($selected_lang, $ignore_referer = false) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));

	$mod_auth_login = $cm->router->getRuleById("mod_auth_login");
	$mod_auth_activation = ($cm->router->getRuleById("mod_auth_activation_" . $tiny_lang_code)
	                        ? $cm->router->getRuleById("mod_auth_activation_" . $tiny_lang_code)
	                        : $cm->router->getRuleById("mod_auth_activation")
	                    ); 

    if(!defined("LIB_FACEBOOK")) {
        define('FACEBOOK_SDK_V4_SRC_DIR', FF_DISK_PATH . '/library/facebook-php-sdk/src/Facebook/');
        require_once FF_DISK_PATH . '/library/facebook-php-sdk/autoload.' . FF_PHP_EXT;    
    }
	if(strpos($_SERVER["HTTP_REFERER"], "https://apps.facebook.com") === 0) {
		$inner_facebook = true;
	} else {
		$inner_facebook = false;
	}
	
	if(!$ignore_referer && !$inner_facebook) {
		return;
	}	
	
	$globals->services["facebook"] = new Facebook(array(
		"appId" => Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_ID")
		, "secret" => Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_SECRET")
		, 'scope' => Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_SCOPE")
		, "fileUpload" => false
	));	
	$app_token = $globals->services["facebook"]->getAccessToken();

    $tokens = Auth::get("token", array("toArray" => true));
    if(is_array($tokens) && count($tokens)) {
        foreach($tokens AS $token) {
            if($token["type"] == "Facebook") {
                $token_facebook = $token["name"];
                break;
            }
        }

        if($token_facebook) {
            $is_valid_token = true;
            $globals->services["facebook"]->setAccessToken($token_facebook);
        }
    }

	// Get User ID
	$user = $globals->services["facebook"]->getUser();
	
	// We may or may not have this data based on whether the user is logged in.
	//
	// If we have a $user id here, it means we know the user is logged into
	// Facebook, but we don't know if the access token is valid. An access
	// token is invalid if the user logged out of Facebook.
	if ($user) {
		$is_valid_permission = true;

		try {
			// Proceed knowing you have a logged in user who's authenticated.
            $user_perm = $globals->services["facebook"]->api('/me/permissions');
			$app_permission = explode(",", Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_SCOPE"));
			if(is_array($app_permission) && count($app_permission)) {
				foreach($app_permission AS $permission_value) {
					if (!array_key_exists($permission_value, $user_perm['data'][0])) {
						$is_valid_permission = false;
						break;
					}
				}
			}
		} catch (FacebookApiException $e) {
			$is_valid_token = false;
		}
	}

	if($user && $is_valid_permission) {
		if(!$is_valid_token) {
			try {
				$user_profile = $globals->services["facebook"]->api('/me');
			} catch (FacebookApiException $e) {
				return ffTemplate::_get_word_by_code("facebook_app_invalid_user_profile") . " " . $e->getMessage();
			}
			$arrUserParams["username"] = $user_profile["name"];
			$arrUserParams["name"] = $user_profile["first_name"];
			$arrUserParams["surname"] = $user_profile["last_name"];
			$arrUserParams["avatar"] = "https://graph.facebook.com/" . $user_profile["id"] . "/picture";
			$arrUserParams["email"] = $user_profile["email"];
			$arrUserParams["status"] = $user_profile["verified"];
			
			$arrUserField["facebook"] = "https://www.facebook.com/" . $user_profile["id"];	

			$globals->services["facebook"]->setExtendedAccessToken();
			$arrUserToken = array("type" => "Facebook"
								, "token" => $globals->services["facebook"]->getAccessToken()
							);
							
			$res = mod_security_set_user_by_social("fb", $arrUserParams, $arrUserField, $arrUserToken, 0, false, true);
			$ffUserParams = $res["user"];
			$sError = $res["error"];

			if($sError) {
				if($inner_facebook) {
					$to_active[0]["name"] = $arrUserParams["name"] . " " . $arrUserParams["surname"];
	                $to_active[0]["mail"] = $arrUserParams["email"];

		            $rnd_active = Auth::password();
		            
		            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users SET active_sid = PASSWORD(" . $db->toSql($rnd_active, "Text") . ") WHERE ID = " . $db->toSql($arrUserParams["ID"], "Number");
		            $db->execute($sSQL);
		            
		            $fields_activation["activation"]["username"] = $arrUserParams["name"] . " " . $arrUserParams["surname"];
		            $fields_activation["activation"]["email"] = $arrUserParams["email"];
	                $fields_activation["activation"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH .  $mod_auth_activation . "?frmAction=activation&sid=" . urlencode($rnd_active);
		            
		            if(check_function("process_mail")) {
                		$rc_activation = process_mail(email_system("account activation"), $to_active, NULL, NULL, $fields_activation);
		            }
		            if(!$rc_activation)
		                $rc_activation = ffTemplate::_get_word_by_code("attivation_mail_success"); 

					$tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "request_user_activation.html", "/social/facebook"));
					$tpl->load_file("request_user_activation.html", "main");
					$tpl->set_var("domain_inset", DOMAIN_INSET);
					$tpl->set_var("site_path", FF_SITE_PATH);
					$tpl->set_var("theme_inset", THEME_INSET);
					$tpl->set_var("theme", FRONTEND_THEME);

					$tpl->set_var("name", $arrUserParams["name"]);
					$tpl->set_var("surname", $arrUserParams["surname"]);
					$tpl->set_var("email", $arrUserParams["email"]);
					$tpl->set_var("error", $sError);
					$tpl->set_var("status_send_mail", $rc_activation);
					
					echo $tpl->rpparse("main", false);;
					exit;
				}
			}
		}

		if(!$sError) {
			if($inner_facebook) {
				if(strlen(Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGENAME"))) {
					$current_page_fan = "";
					$arrPageFan = explode(",", Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_NAME"));
					if(is_array($arrPageFan) && count($arrPageFan)) {
						foreach($arrPageFan AS $arrPageFan_value) {
							if(strpos($_SERVER["HTTP_REFERER"], "https://apps.facebook.com/" . $arrPageFan_value) === 0) {
								$current_page_fan = $arrPageFan_value;
								break;
							}
						}
					}

					if($current_page_fan) {
						if(Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_REQUIRE_LIKE")) {
							try {
								$page_fan_like = $globals->services["facebook"]->api(array(
									 'method' => 'fql.query',
									 'query' =>'SELECT created_time FROM page_fan WHERE uid = ' . $user . ' AND page_id = ' . $current_page_fan
								 ));
							} catch (FacebookApiException $e) {
								return ffTemplate::_get_word_by_code("facebook_app_error_page_fan_like") . " " . $e->getMessage();
							}

							if(is_array($page_fan_like) && count($page_fan_like)) {
								$tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "page_fan_like.html", "/social/facebook"));
								$tpl->load_file("page_fan_like.html", "main");
								$tpl->set_var("domain_inset", DOMAIN_INSET);
								$tpl->set_var("site_path", FF_SITE_PATH);
								$tpl->set_var("theme_inset", THEME_INSET);
								$tpl->set_var("theme", FRONTEND_THEME);
								
								$globals->fixed_pre["content"][] = $tpl->rpparse("main", false);
							}
						}
					} else {
						if(Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_DISPLAY_ONLY_IN")) {
							$tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "redirect.html", "/social/facebook"));
							$tpl->load_file("redirect.html", "main");
							$tpl->set_var("domain_inset", DOMAIN_INSET);
							$tpl->set_var("site_path", FF_SITE_PATH);
							$tpl->set_var("theme_inset", THEME_INSET);
							$tpl->set_var("theme", FRONTEND_THEME);
							$tpl->set_var("url", "https://www.facebook.com/" . $arrPageFan[0] . "/app_" . $globals->services["facebook"]->getAppId());
							$tpl->parse("SezPage", false);

							echo $tpl->rpparse("main", false);
							exit;
						}
					}
				}

				$tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "init.html", "/social/facebook"));
				$tpl->load_file("init.html", "main");
				$tpl->set_var("domain_inset", DOMAIN_INSET);
				$tpl->set_var("site_path", FF_SITE_PATH);
				$tpl->set_var("theme_inset", THEME_INSET);
				$tpl->set_var("theme", FRONTEND_THEME);
				$tpl->set_var("fb_app_id", Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_ID"));
				$tpl->set_var("fb_lang", strtolower(substr(FF_LOCALE, 0, 2)) . "_" . strtoupper(substr(FF_LOCALE, 0, 2)));

				$globals->media_exception["css"]["normalize"] = false;
				
				$globals->fixed_pre["content"][] = $tpl->rpparse("main", false);
			}

			return false;
		} else {
			return $sError;
		}
	} else {
		if($is_valid_token) {
			mod_security_set_accesstoken(Auth::get("user")->id, "", "Facebook");
			$globals->services["facebook"]->setAccessToken($app_token);
		}

		if($inner_facebook) {	
			try {			
				$app = $globals->services["facebook"]->api('/'. $globals->services["facebook"]->getAppId());
			} catch (FacebookApiException $e) {
				return ffTemplate::_get_word_by_code("facebook_app_invalid_app") . " " . $e->getMessage();
			}

			$loginConfig["display"] = "page"; 

			if(strlen(Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_NAME")) && strpos($_SERVER["HTTP_REFERER"], "https://apps.facebook.com/" . Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_NAME")) === 0) {
				$loginConfig["redirect_uri"] = "https://www.facebook.com/" . Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_NAME") . "/app_" . $globals->services["facebook"]->getAppId();
			} else {
				$loginConfig["redirect_uri"] = "https://apps.facebook.com/" . $app["namespace"];
			}
			if(Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_SCOPE")) {
				$loginConfig["scope"] = Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_SCOPE");
			}
		} else {
			$loginConfig["redirect_uri"] = "http://" . DOMAIN_INSET;
		}

		$loginUrl = $globals->services["facebook"]->getLoginUrl($loginConfig);
		if(strlen($loginUrl)) {
			if($inner_facebook) {
				$tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "redirect.html", "/social/facebook"));
				$tpl->load_file("redirect.html", "main");
				$tpl->set_var("domain_inset", DOMAIN_INSET);
				$tpl->set_var("site_path", FF_SITE_PATH);
				$tpl->set_var("theme_inset", THEME_INSET);
				$tpl->set_var("theme", FRONTEND_THEME);
				
				switch ($display) {
					case "popup":
						$tpl->set_var("url", $loginUrl);
						$tpl->set_var("app_name", $app["name"]);
						$tpl->parse("SezDialog", false);
						break;
					case "page":
					default:
						$tpl->set_var("url", $loginUrl);
						$tpl->parse("SezPage", false);
				}
				
				echo $tpl->rpparse("main", false);
				exit;
			}
		}
	}
}

function facebook_api($type, $params, $target = null) {
	$globals = ffGlobals::getInstance("gallery");
	if($user === null) {
		$target = "me";
	}
	$sError = system_lib_facebook(true);
	if(!$sError) {
		try {
			switch($type) {
				case "feed":
					$ret_obj = $globals->services["facebook"]->api('/' . $target. '/' . $type, 'POST', $params);

					break;
				default:
			}

			return false;
		} catch (FacebookApiException $e) {
			return ffTemplate::_get_word_by_code("facebook_app_invalid_api_call") . " " . $e->getMessage();
		}
	}
	
	return $sError;
}

function facebook_publish($message, $link, $picture = "", $name = "", $caption = "", $description = "", $actions = array(), $place = "", $tags = "", $privacy = "", $object_attachment = "", $target = null) {
	if(!$place)
		$place = Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_PAGE_NAME");
	
	$params['message'] = $message; //Post message required
	$params['link'] = $link; //Post URL required
	
	if(strlen($picture))	
		$params['picture'] = $picture; //Post thumbnail image (can only be used if link is specified)
	if(strlen($name))	
		$params['name'] = $name;	//Post name (can only be used if link is specified)
	if(strlen($caption))	
		$params['caption'] = $caption; //Post caption (can only be used if link is specified)
	if(strlen($description))	
		$params['description'] = $description; //Post description (can only be used if link is specified)
	if(is_array($actions) && count($actions))	
		$params['actions'] = $actions; //Post actions array(name => "", link => "")
	if(strlen($place))	
		$params['place'] = $place; //Facebook Page ID of the location associated with this Post
	if(strlen($tags))	
		$params['tags'] = $tags; //Comma-separated list of Facebook IDs of people tagged in this Post
	if(strlen($privacy))	
		$params['privacy'] = $privacy; //Post privacy settings (can only be specified if the Timeline being posted on belongs to the User creating the Post)
	if(strlen($object_attachment))	
		$params['object_attachment'] = $object_attachment; //Facebook ID for an existing picture in the User's photo albums to use as the thumbnail image. The User must be the owner of the photo, and the photo cannot be part of a message attachment.
	


	return facebook_api("feed", $params, $target);
}

