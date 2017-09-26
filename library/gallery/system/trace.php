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
  
define("TRACE_DISK_PATH", dirname(dirname(dirname(__DIR__))));
  
function system_trace($action, $url = null, $get = null, $action_value = null, $visitor = null) {
    if(!$visitor)
        $visitor = system_trace_get_visitor();
    
    if($visitor)
    {
        if(!$url) {
            $url = $_SERVER["REQUEST_URI"];
		}
		if(!$get) {
			$get = $_GET;
		}
        switch($action) {
            case "redirect":
                system_trace_redirect($action_value, $url, $visitor);
                break;
            case "search":
                break;
            default:
                $user_permission = $_SESSION[APPID . "user_permission"];
                if(is_file(TRACE_DISK_PATH . "/library/mobiledetect/class.mobiledetect.php"))
                {
                    require_once(TRACE_DISK_PATH . "/library/mobiledetect/class.mobiledetect.php");
                    $device = new mobileDetect();

                    $detect["device"]["name"] = $device->isMobile();
                    if($detect["device"]["name"]) {
                        $detect["device"]["type"] = "Mobile";
                    } else {
                        $detect["device"]["name"] = $device->isTablet();
                        if($detect["device"]["name"]) {
                            $detect["device"]["type"] = "Tablet";
                        } else {
                            $detect["device"]["type"] = "Desktop";
                        }            
                    }
                }

                if(is_file(TRACE_DISK_PATH . "/library/browser/class.browser.php"))
                {
                    require_once(TRACE_DISK_PATH . "/library/browser/class.browser.php");
                    $browser = new Browser();

                    $detect["browser"]["name"] = $browser->getBrowser();
                    $detect["browser"]["ver"] = $browser->getVersion();
                    $detect["platform"] = $browser->getPlatform();
                }

                $page = cache_get_page_stats();

                $trace = array(
                    "visitor" => $visitor["unique"]
                    , "url" => $url
                    , "get" => $get
                    , "domain" => $_SERVER["HTTP_HOST"]
                    , "action" => array(
                        "name" => $action
                        , "value" => $action_value
                    )
                    , "action_value" => $action_value
                    , "referer" => $_SERVER["HTTP_REFERER"]
                    , "user_agent" => $_SERVER["HTTP_USER_AGENT"]
                    , "device" => $detect["device"]
                    , "browser" => $detect["browser"]
                    , "platform" => $detect["platform"]
                    , "page" => array(
                        "title" => $page["title"]
                        , "description" => $page["meta"]["description"]["content"]
                        , "tags" => (is_array($page["tags"]) ? $page["tags"] : array())
                        , "keywords" => (is_array($page["keywords"]) ? $page["keywords"] : array())
                    )
                    , "user" => array(
                        "id" => $user_permission["ID"]
                        , "name" => $user_permission["name"]
                        , "surname" => $user_permission["surname"]
                        , "email" => $user_permission["email"]
                    )
                    , "created" => time()
                );
				
                if(is_file(TRACE_DISK_PATH . "/conf/gallery/config/trace.php")) {
                    require_once(TRACE_DISK_PATH . "/conf/gallery/config/trace.php");

                    if(defined("TRACE_MONGO_DATABASE_NAME")) 
                    {
            			require_once(TRACE_DISK_PATH . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");
                        $db = new ffDB_MongoDB();
                        $db->on_error = "ignore";
                        
                        $db->connect(TRACE_MONGO_DATABASE_NAME, TRACE_MONGO_DATABASE_HOST, TRACE_MONGO_DATABASE_USER, TRACE_MONGO_DATABASE_PASSWORD);
                        $db->insert($trace, TRACE_TABLE_NAME);
                    }
                    
                    if(defined("TRACE_DATABASE_NAME"))
                    {
                        if(!class_exists("ffDB_Sql"))
                            require_once(TRACE_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");

                        $db = new ffDB_Sql();
                        $db->on_error = "ignore";

                        if($db->connect(TRACE_DATABASE_NAME, TRACE_DATABASE_HOST, TRACE_DATABASE_USER, TRACE_DATABASE_PASSWORD)) 
                        {
                            $sSQL = "INSERT INTO `" . TRACE_TABLE_NAME . "`
                                    (
                                        `ID`
                                        , `visitor`
                                        , `url`
                                        , `get`
                                        , `domain`
                                        , `action`
                                        , `action_value`
                                        , `referer`
                                        , `user_agent`
                                        , `device_type`
                                        , `device_name`
                                        , `browser_name`
                                        , `browser_ver`
                                        , `platform`
                                        , `page_title`
                                        , `page_description`
                                        , `page_tags`
                                        , `page_keywords`
                                        , `user_id`
                                        , `user_name`
                                        , `user_surname`
                                        , `user_email`
                                        , `created`
                                    )
                                    VALUES
                                    (
                                        null
                                        , " . $db->toSql($trace["visitor"]) . "
                                        , " . $db->toSql($trace["url"]) . "
                                        , " . $db->toSql(json_encode($trace["get"])) . "
                                        , " . $db->toSql($trace["domain"]) . "
                                        , " . $db->toSql($trace["action"]["name"]) . "
                                        , " . $db->toSql($trace["action"]["value"]) . "
                                        , " . $db->toSql($trace["referer"]) . "
                                        , " . $db->toSql($trace["user_agent"]) . "
                                        , " . $db->toSql($trace["device"]["type"]) . "
                                        , " . $db->toSql($trace["device"]["name"]) . "
                                        , " . $db->toSql($trace["browser"]["name"]) . "
                                        , " . $db->toSql($trace["browser"]["ver"]) . "
                                        , " . $db->toSql($trace["platform"]) . "
                                        , " . $db->toSql($trace["page"]["title"]) . "
                                        , " . $db->toSql($trace["page"]["description"]) . "
                                        , " . $db->toSql(implode(",", $trace["page"]["tags"])) . "
                                        , " . $db->toSql(implode(",", $trace["page"]["keywords"])) . "
                                        , " . $db->toSql($trace["user"]["id"]) . "
                                        , " . $db->toSql($trace["user"]["name"]) . "
                                        , " . $db->toSql($trace["user"]["surname"]) . "
                                        , " . $db->toSql($trace["user"]["email"]) . "
                                        , " . $db->toSql($trace["created"], "Number") . "
                                    )";
                            $db->execute($sSQL);
                        }
                    }
                } else {
                    system_write_trace($trace);
                }                
        }
    }
}
function system_trace_redirect($url = null, $visitor = null) {


}
  
function system_write_trace($trace, $filename = "index") {
    if(!is_dir(TRACE_DISK_PATH . "/cache/trace"))
        mkdir(TRACE_DISK_PATH . "/cache/trace", 0777, true);

    $file = TRACE_DISK_PATH . '/cache/trace/' . $filename . '.php';  
    if(!is_file($file)) {
        $set_mod = true;
    }
    if($handle = @fopen($file, 'a')) 
    {
        if(@fwrite($handle, '$t = ' . var_export($trace, true) . ";\n") === FALSE)
        {
            $i18n_error = true;
        }
        @fclose($handle);

        if($set_mod)
            chmod($file, 0777);            
    }      
}
  
function system_trace_isCrawler($user_agent)
{
  	$isCrawler = true;
	$crawlers = array(
		'Google'=>'Google',
		'MSN' => 'msnbot',
		'Rambler'=>'Rambler',
		'Yahoo'=> 'Yahoo',
		'AbachoBOT'=> 'AbachoBOT',
		'accoona'=> 'Accoona',
		'AcoiRobot'=> 'AcoiRobot',
		'ASPSeek'=> 'ASPSeek',
		'CrocCrawler'=> 'CrocCrawler',
		'Dumbot'=> 'Dumbot',
		'FAST-WebCrawler'=> 'FAST-WebCrawler',
		'GeonaBot'=> 'GeonaBot',
		'Gigabot'=> 'Gigabot',
		'Lycos spider'=> 'Lycos',
		'MSRBOT'=> 'MSRBOT',
		'Altavista robot'=> 'Scooter',
		'AltaVista robot'=> 'Altavista',
		'ID-Search Bot'=> 'IDBot',
		'eStyle Bot'=> 'eStyle',
		'Scrubby robot'=> 'Scrubby',
		
		'GenericBot' => 'bot',
		'GenericCrawler' => 'crawler'
	);
 
 	if($user_agent === null)
 		$user_agent = $_SERVER["HTTP_USER_AGENT"];
 	
 	if($user_agent) {
 		$crawlers_agents = implode("|", $crawlers);
 		$isCrawler = (preg_match("/" . $crawlers_agents . "/i", $user_agent) > 0);
	}


	return $isCrawler;
}
 
function system_trace_get_visitor($user_agent = null) {
  	if($user_agent === null)
  		$user_agent = $_SERVER["HTTP_USER_AGENT"];

    if(!system_trace_isCrawler($user_agent)) {
        $long_time = time() + (60 * 60 * 24 * 365 * 30);

        if($_COOKIE["_ga"]) {
            $ga = explode(".", $_COOKIE["_ga"]);
              
            $visitor = array(
                "unique" => $ga[2]
                , "created" => $ga[3]
                , "last_update" => $ga[3]
            );
          } elseif($_COOKIE["__utma"]) {
            $utma = explode(".", $_COOKIE["__utma"]);
              
            $visitor = array(
                "unique" => $utma[1]
                , "created" => $utma[2]
                , "last_update" => $utma[4]
            );
        } elseif($_COOKIE["_uv"]) {
            $uv = explode(".", $_COOKIE["_uv"]);

            $visitor = array(
                "unique" => $uv[0]
                , "created" => $uv[1]
                , "last_update" => $uv[2]
            );
            if($visitor["last_update"] + (60 * 60 * 24) < time()) {
                $visitor["last_update"] = time();

				//$_COOKIE["_uv"] = implode(".", $visitor);
                setcookie("_uv", implode(".", $visitor), $long_time);
            }
          } else {
            $access = explode("E", hexdec(md5(
                $_SERVER["REMOTE_ADDR"]
                . $_SERVER["HTTP_USER_AGENT"]
            )));

			$offset = (strlen($access[0]) - 9);
            $visitor = array(
                "unique" => substr($access[0], $offset, 9)
                , "created" => time()
                , "last_update" => time()
            );
			//$_COOKIE["_uv"] = implode(".", $visitor);
            setcookie("_uv", implode(".", $visitor), $long_time);
        }
    } else {
        $visitor = false;
    }
         
    return $visitor;      
}

function system_trace_notify($message, $to, $params = array()) {
	$default = array(
		"single"                => false    //notifica unica. Incrementa l'hit ad ogni insert
		, "expire"              => 0        //scadenza della notifica
		, "actions"             => null     //action collegate alla notifica
        , "schedule"            => ""       //pospone l'invio della notifica
		, "referer"             => ""       //Referral di cosa ha generato la notifica (se un int e una notifica altrimenti e un servizio esterno)
	);
	$params = array_merge($default, $params);
	
    switch($params["type"]) {
        case "pool":
            if(!$params["action"])
                $error = ffTemplate::_get_word_by_code("trace_notify_action_required");
            break;
        default:
    }
    if(!$error) {
		$reader = (is_array($params["reader"])
                    ? $params["reader"]
                    : array($params["reader"])
                );
		
        foreach($reader AS $reader_name) {
			switch($reader_name) {
                case "push":
                    system_trace_notify_via_push($message, $to, $params);
                    break;
                case "mail":
                    system_trace_notify_via_mail($message, $to, $params);
                    break;
                default:
					system_trace_notify_via_server($message, $to, $params);
            }
        }
    }    

	return $error;
}

function system_trace_notify_get_user($dest = array(), $to, $fields = null, $service = null) {
    $db = ffDB_Sql::factory();
    
    if(is_array($fields) && count($fields)) {
        foreach($fields AS $field_name) {
            $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $field_name . "`";
        }
    } elseif($fields) {
        $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $fields . "`";        
    }
    
    if($service) {
        $query["select"][] = CM_TABLE_PREFIX . "mod_security_token.`token`";
        $query["join"][] = CM_TABLE_PREFIX . "mod_security_token.`token` ON " . CM_TABLE_PREFIX . "mod_security_token.`token`.ID_user = " . CM_TABLE_PREFIX . "mod_security_users.ID";        
    }
    
    if(is_array($to)) {
		if(is_array($to["uid"])) {
			$dest["uid"] = implode(",", $to["uid"]);
			$query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID IN(" . $dest["uid"] . ")";
        } elseif(is_numeric($to["uid"])) {
			$dest["uid"] = $to["uid"];
			$query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID = " . $dest["uid"];
		}
    }
    
    if($query) {
        $dest = array();
        $sSQL = "SELECT DISTINCT " . implode(", ", $query["select"]) . "
                FROM " . CM_TABLE_PREFIX . "mod_security_users
                    " . (is_array($query["join"])
                        ? " INNER JOIN " . implode(" INNER JOIN ", $query["join"])
                        : ""
                    ) . "
                WHERE " . implode(" AND ", $query["where"]);
        $db->query($sSQL);
		if($db->nextRecord()) {
            do {
                $dest["uid"][] = $db->record;
            } while($db->nextRecord());
        }
    }
    
    return $dest;
}

function system_trace_notify_get_group($dest = array(), $to, $fields = null, $service = null) {
	if(is_array($to["gid"])) {
		$dest["gid"] = implode(",", $to["gid"]);
	} elseif(is_numeric($to["gid"])) {
		$dest["gid"] = $to["gid"];
	}
	
	return $dest;
}

function system_trace_notify_via_push($message, $to, $params) {
    $dest = system_trace_notify_get_user($to, "token", "onesignal");
    
    $app_id = TRACE_ONESIGNAL_APP_ID;

    if(is_array($message) && count($message)) {
        $title = $message["title"];
        $text = $message["text"];
        $media = $message["media"];
    } else {
        $title = $message;
    }            

    $content = array(
        "it" => $title
        , "en" =>  $title
    );

    $heading = array(
        "it" => $message
        , "en" =>  $message
    );

    $fields = array(
        'app_id' => $app_id,
        'include_player_ids' => $arrSend,
        'headings' => $heading,
        'contents' => $content
    );
    if(isset($params["actions"])) {
        $fields["url"] = $params["actions"];
    }
    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                               'Authorization: Basic ' . TRACE_ONESIGNAL_API_KEY));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    
	if(is_file(TRACE_DISK_PATH . "/conf/gallery/config/trace.php")) {
        require_once(TRACE_DISK_PATH . "/conf/gallery/config/trace.php");
        
        $notify = array(
            "uid"       => $to["uid"]
            , "gid"         => $to["gid"]
            , "type"        => $params["type"]
            , "title"       => $title
            , "message"     => $text
            , "media"       => $media
            , "reader"      => "push"
            , "actions"     => $params["actions"]
            , "referer"     => $params["referer"]
            , "expire"      => $params["expire"]
            , "schedule"   => $params["schedule"]
            , "hit"         => "1"
        );

        if(defined("TRACE_MONGO_DATABASE_NAME")) 
        {
            require_once(TRACE_DISK_PATH . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");
            $db = new ffDB_MongoDB();
            $db->on_error = "ignore";

            $db->connect(TRACE_MONGO_DATABASE_NAME, TRACE_MONGO_DATABASE_HOST, TRACE_MONGO_DATABASE_USER, TRACE_MONGO_DATABASE_PASSWORD);
            $db->insert($notify, TRACE_NOTIFY_TABLE_NAME);
        }
        
        if(defined("TRACE_DATABASE_NAME")) 
        {
            if(!class_exists("ffDB_Sql"))
                require_once(TRACE_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");

            $db = new ffDB_Sql();
            $db->on_error = "ignore";

            if($db->connect(TRACE_DATABASE_NAME, TRACE_DATABASE_HOST, TRACE_DATABASE_USER, TRACE_DATABASE_PASSWORD)) 
            {
                $sSQL = "INSERT INTO `" . TRACE_NOTIFY_TABLE_NAME . "`
                            (
                                `ID`
                                , ID_dest
                                , `gid`
                                , `type`
                                , `title`
                                , `message`
                                , `media`
                                , `reader`
                                , `actions`
                                , `referer`
                                , `expire`
                                , `time_from`
                                , `hit`
                            )
                            VALUES
                            (
                                null
                                , " . $db->toSql($notify["uid"], "Text") . "
                                , " . $db->toSql($notify["gid"], "Text") . "
                                , " . $db->toSql($notify["type"], "Text") . "
                                , " . $db->toSql($notify["title"], "Text") . "
                                , " . $db->toSql($notify["message"], "Text") . "
                                , " . $db->toSql($notify["media"], "Text") . "
                                , " . $db->toSql($notify["reader"], "Text") . "
                                , " . $db->toSql($notify["actions"], "Text") . "
                                , " . $db->toSql($notify["referer"], "Number") . "
                                , " . $db->toSql($notify["expire"], "Number") . "
                                , " . $db->toSql($notify["schedule"], "Number") . "
                                , " . $db->toSql($notify["hit"], "Number") . "
                            )";
            }
		}
	}
}

function system_trace_notify_via_mail($message, $to) {
    $dest = system_trace_notify_get_user($to, array("name", "surname", "email"));
    
}

function system_trace_notify_via_server($message, $to, $params) {
	if(is_file(TRACE_DISK_PATH . "/conf/gallery/config/trace.php")) {
        require_once(TRACE_DISK_PATH . "/conf/gallery/config/trace.php");

        if(isset($to["uid"])) {
            $dest = system_trace_notify_get_user($dest, $to, array("ID"));
        }

        if(isset($to["uid"])) {
            $dest = system_trace_notify_get_group($dest, $to);
        }

        if(is_array($message) && count($message)) {
            $title = $message["title"];
            $text = $message["text"];
            $media = $message["media"];
        } else {
            $title = $message;
        }

        if(is_array($dest["uid"]) && count($dest["uid"])) {
            foreach($dest["uid"] AS $index => $value) {
                if(strlen($uid_list))
                    $uid_list .= ",";
                $uid_list = $value["ID"];
            }
        }
        $notify = array(
            "uid" => $uid_list
            , "gid" => $dest["gid"]
            , "type" => $params["type"]
            , "title" => $title
            , "message" => $text
            , "media" => $media
            , "reader" => $params["reader"]
            , "actions" => $params["actions"]
            , "referer" => $params["referer"]
            , "expire" => $params["expire"]
            , "schedule" => $params["schedule"]
            , "hit" => "1"
        );        

        if(defined("TRACE_MONGO_DATABASE_NAME")) 
        {
            require_once(TRACE_DISK_PATH . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");
            $db = new ffDB_MongoDB();
            $db->on_error = "ignore";

            $db->connect(TRACE_MONGO_DATABASE_NAME, TRACE_MONGO_DATABASE_HOST, TRACE_MONGO_DATABASE_USER, TRACE_MONGO_DATABASE_PASSWORD);
            if(!$params["single"]) {
                $db->query(array(
                    "select" => array("ID" => 1)
                    , "from" => TRACE_NOTIFY_TABLE_NAME
                    , "where" => array(
                        "uid" => $notify["uid"]
                        , "gid" => $notify["gid"]
                        , "message" => $notify["message"]
                        , "reader" => $notify["reader"]
                        , "actions" => $notify["actions"]
                    )
                ));
                if($db->nextRecord()) {
                    $ID = $db->getField("ID", "Number", true);
                    $db->update(array(
                            "set" => array("hit" => "+1")
                            , "where" => array("ID" => $ID)
                        ), TRACE_NOTIFY_TABLE_NAME);
                }
            }
            if(!$ID) {
                $db->insert($notify, TRACE_NOTIFY_TABLE_NAME);
            }
        }
        
        if(defined("TRACE_DATABASE_NAME")) 
        {
            if(!class_exists("ffDB_Sql"))
                require_once(TRACE_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");

            $db = new ffDB_Sql();
            $db->on_error = "ignore";

            if($db->connect(TRACE_DATABASE_NAME, TRACE_DATABASE_HOST, TRACE_DATABASE_USER, TRACE_DATABASE_PASSWORD)) 
            {
                if(!$params["single"]) {
                    $db->query("SELECT ID
                                FROM `" . TRACE_NOTIFY_TABLE_NAME . "`
                                WHERE ID_dest = " . $db->toSql($notify["uid"], "Text") . "
                                    AND gid = " . $db->toSql($notify["gid"], "Text") . "
                                    AND message = " . $db->toSql($notify["message"], "Text") . "
                                    AND reader = " . $db->toSql($notify["reader"], "Text") . "
                                    AND actions = " . $db->toSql($notify["actions"], "Text"));
                    if($db->nextRecord()) {
                        $ID = $db->getField("ID", "Number", true);

                        $sSQL = "UPDATE `" . TRACE_NOTIFY_TABLE_NAME . "` SET
                                    hit = (hit + 1)
                                WHERE ID = " . $db->toSql($ID, "Number");

                    }
                }

                if(!strlen($sSQL)) {
                    $sSQL = "INSERT INTO `" . TRACE_NOTIFY_TABLE_NAME . "`
                        (
                            `ID`
                            , ID_dest
                            , `gid`
                            , `type`
                            , `title`
                            , `message`
                            , `media`
                            , `reader`
                            , `actions`
                            , `referer`
                            , `expire`
                            , `time_from`
                            , `hit`
                        )
                        VALUES
                        (
                            null
                            , " . $db->toSql($notify["uid"], "Text") . "
                            , " . $db->toSql($notify["gid"], "Text") . "
                            , " . $db->toSql($notify["type"], "Text") . "
                            , " . $db->toSql($notify["title"], "Text") . "
                            , " . $db->toSql($notify["message"], "Text") . "
                            , " . $db->toSql($notify["media"], "Text") . "
                            , " . $db->toSql($notify["reader"], "Text") . "
                            , " . $db->toSql($notify["actions"], "Text") . "
                            , " . $db->toSql($notify["referer"], "Number") . "
                            , " . $db->toSql($notify["expire"], "Number") . "
                            , " . $db->toSql($notify["schedule"], "Number") . "
                            , " . $db->toSql($notify["hit"], "Number") . "
                        )";
                }
                $db->execute($sSQL);
            }
        }
	}    
}

function system_trace_get_notify($path = "", $exclude = array()) {
	if(is_file(TRACE_DISK_PATH . "/conf/gallery/config/trace.php")) {
		require_once(TRACE_DISK_PATH . "/conf/gallery/config/trace.php");
		if(!class_exists("ffDB_Sql"))
			require_once(TRACE_DISK_PATH . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");

		$db = new ffDB_Sql();
		$db->on_error = "ignore";
		
		if($db->connect(TRACE_DATABASE_NAME, TRACE_DATABASE_HOST, TRACE_DATABASE_USER, TRACE_DATABASE_PASSWORD)) 
		{
			$user_permission = get_session("user_permission");
			$sSQL_where .= " AND (FIND_IN_SET(" . $db->toSql(get_session("UserNID"), "Number") . ", ID_dest) OR FIND_IN_SET( " . $db->toSql($user_permission["primary_gid"], "Number") . ", gid) OR (ID_dest = '' AND gid = ''))";
			
			if(is_array($exclude) && count($exclude)) {
				foreach($exclude AS $notify) {
					$arrID[] = $notify["ID"];
					
				}
				$sSQL_where .= " AND trace_notify.ID NOT IN(" . $db->toSql(implode(",", $arrID), "Text", false). ")";
			}
			
			if(0 && strlen($path)) {
				$sSQL_where .= " AND (actions = " . $db->toSql($path) . " OR actions = '')";
			}
			
			$sSQL = "SELECT trace_notify.ID
							, trace_notify.type
							, trace_notify.title
							, trace_notify.message
							, trace_notify.media
							, trace_notify.actions
							, trace_notify.notify_by
							, trace_notify.expire
						FROM trace_notify
						WHERE FIND_IN_SET('server', trace_notify.reader) " . $sSQL_where . "
							AND (expire >= " . $db->toSql(time(), "Number") . " OR expire = 0)
							AND time_from <= " . $db->toSql(time(), "Number") . "
						ORDER BY ID";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$arrNotify[$db->getField("ID", "Number", true)] = array(
						"ID" => $db->getField("ID", "Number", true)
						, "type" => $db->getField("type", "Text", true)
						, "title" => $db->getField("title", "Text", true)
						, "message" => $db->getField("message", "Text", true)
						, "media" => $db->getField("media", "Text", true)
						, "actions" => $db->getField("actions", "Text", true)
						, "notify_by" => $db->getField("notify_by", "Text", true)
					);
					if($db->getField("expire", "Number", true))
						$arrNotify[$db->getField("ID", "Number", true)]["expire"] = gmdate('D, d M Y H:i:s \G\M\T',$db->getField("expire", "Number", true));
				} while ($db->nextRecord());
			}
		}
	}
	
	return array(
		"result" => $arrNotify
		, "timer" => (is_array($arrNotify) 
			? 10000 
			: false
		)
	);
}
