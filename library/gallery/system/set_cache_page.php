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
function system_set_cache_page($content) {
    $globals = ffGlobals::getInstance("gallery");
    $cache_file = $globals->cache["file"];

	if(is_object($content)) {
		switch(get_class($content)) {
			case "ffPage_html":
				$buffer = $content->tpl[0]->rpparse("main", false);
				break;
			case "ffTemplate":
				$buffer = $content->rpparse("main", false);
				break;
			default:
				$buffer = $content;
		}
	} else {
		$buffer = $content;
	}

	$is_error_document = !($buffer && http_response_code() == 200);
	/*if(!$is_error_document) {
		Stats::getInstance("page")->write();
		if (TRACE_VISITOR === true) {
            Stats::getInstance("trace")->write("pageview", "nocache");
		}
	}*/

    if(!defined("DISABLE_CACHE") && $globals->cache["enabled"] !== false) {
        $expires = time() + (60 * 60 * 24 * 1);



        if(!$is_error_document) {
            if(!is_dir($cache_file["cache_path"]))
                @mkdir($cache_file["cache_path"], 0777, true);


            if ($cache_file["primary"] != $cache_file["gzip"])
                cm_filecache_write($cache_file["cache_path"], $cache_file["primary"], $buffer, $expires);

            cm_filecache_write($cache_file["cache_path"], $cache_file["gzip"], gzencode($buffer), $expires);

			Cache::log($cache_file["cache_path"] . "/" . $cache_file["primary"], "log_saved");
        } else {
            system_write_cache_error_document($cache_file);
            if ($cache_file["noexistfileerror"]) {
                if ($cache_file["primary"] != $cache_file["gzip"])
                    cm_filecache_write($cache_file["error_path"], $cache_file["primary"], $buffer, $expires);

                cm_filecache_write($cache_file["error_path"], $cache_file["gzip"], gzencode($buffer), $expires);
            }
        }
    } elseif($globals->cache["enabled"] === false) {
    	$cache = check_static_cache_page($globals->page["strip_path"] . $globals->page["user_path"], 200);

        if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["primary"]))
            @unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["primary"]);
        if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["gzip"]))
            @unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["gzip"]);
        if(strpos($cache["file"]["cache_path"], FF_DISK_PATH) === 0 && is_file($cache["file"]["cache_path"] . "/" . $cache["file"]["filename"] . "." . $cache["file"]["type"]))
            @unlink($cache["file"]["cache_path"] . "/" . $cache["file"]["filename"] . "." . $cache["file"]["type"]);
    }

    cache_sem_release($globals->cache["sem"]);

    if(defined("DISABLE_CACHE"))
        cache_send_header_content(false, false, false, false);
    else
        cache_send_header_content(false, false);

	if(DEBUG_PROFILING === true)
		Stats::benchmark((defined("DISABLE_CACHE")
        	? "Cache lvl 2 (no cache) "
        	: "Cache lvl 3 (gen cache)"
        ));

        //ffErrorHandler::raise("DEBUG CM Process End", E_USER_WARNING, null, get_defined_vars());

}

function system_write_cache_page($user_path, $contents) {
    $globals = ffGlobals::getInstance("gallery");

    $http_status_code = http_response_code();
    if($http_status_code == 200)
        $use_in_sitemap = 1;

    //if(!$skip_strip_path)
        $user_path = $globals->page["strip_path"] . $user_path;

    $cache = check_static_cache_page($user_path, $http_status_code);

    $globals->cache = array_replace($globals->cache, $cache);
    //$globals->cache["sem"] = &$cache["sem"];

    if($globals->cache["enabled"] === false)
        return false;

    if(!$cache)
        return false;

    if($cache["request"]["post"])
        return false;

    if(!$contents && !$cache["ff_count"])
        return false;

    $db = ffDB_Sql::factory();
    $last_update = time();
    $arrFrequency = array("always" 	=> 10
                    , "hourly" 		=> 9
                    , "daily" 		=> 8
                    , "weekly" 		=> 7
                    , "monthly" 	=> 6
                    , "yearly" 		=> 5
                    , "never" 		=> 4
                );
    $frequency = "never";
    if(is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"])) {
        foreach($globals->cache["layout_blocks"] AS $layout_blocks_key => $layout_blocks_value) {
            if($arrFrequency[$layout_blocks_value["frequency"]] > $arrFrequency[$frequency])
                $frequency = $layout_blocks_value["frequency"];
        }
    }
    if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
        $cache_xhr = "1";

    $cache_ext = $globals->cache["params"]["type"];
    if($cache_ext == "mixed") {
        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
            $cache_ext = "json";
        else
            $cache_ext = "html";
    }

	//Section Block
 	if(is_array($globals->cache["section_blocks"]) && count($globals->cache["section_blocks"]))
    	$query["insert"]["section_blocks"] = implode(",", $globals->cache["section_blocks"]);

	//Layout Block
 	if(is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"]))
    	$query["insert"]["layout_blocks"] = implode(",", array_keys($globals->cache["layout_blocks"]));

	//FF Block
 	if(is_array($globals->cache["ff_blocks"]) && count($globals->cache["ff_blocks"]))
    	$query["insert"]["ff_blocks"] = implode(",", $globals->cache["ff_blocks"]);

	//Data V Block
 	if(is_array($globals->cache["data_blocks"]["V"]) && count($globals->cache["data_blocks"]["V"]))
    	$query["insert"]["data_v_block"] = implode(",", $globals->cache["data_blocks"]["V"]);

	//Data G Block
 	if(is_array($globals->cache["data_blocks"]["G"]) && count($globals->cache["data_blocks"]["G"]))
    	$query["insert"]["data_g_block"] = implode(",", $globals->cache["data_blocks"]["G"]);

	//Data S Block
 	if(is_array($globals->cache["data_blocks"]["S"]) && count($globals->cache["data_blocks"]["v"]))
    	$query["insert"]["data_s_block"] = implode(",", $globals->cache["data_blocks"]["S"]);

	//Data D Block
 	if(is_array($globals->cache["data_blocks"]["D"]) && count($globals->cache["data_blocks"]["D"]))
    	$query["insert"]["data_d_block"] = implode(",", $globals->cache["data_blocks"]["D"]);

	//Data T Block
 	if(is_array($globals->cache["data_blocks"]["T"]) && count($globals->cache["data_blocks"]["T"]))
    	$query["insert"]["data_t_block"] = implode(",", $globals->cache["data_blocks"]["T"]);

	//Data M Block
 	if(is_array($globals->cache["data_blocks"]["M"]) && count($globals->cache["data_blocks"]["M"]))
    	$query["insert"]["data_m_block"] = implode(",", $globals->cache["data_blocks"]["M"]);


    $sSQL = "SELECT ID 
                , section_blocks
                , layout_blocks
                , data_v_block
                , data_g_block
                , data_s_block
                , data_d_block
                , data_t_block
                , data_m_block
                , ff_blocks
	            , http_status_code
            FROM cache_page
            WHERE `cache_page`.`user_path` = " . $db->toSql($user_path) . "
                AND `cache_page`.`disk_path` = " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                AND `cache_page`.`filename` = " . $db->toSql($globals->cache["file"]["filename"]) . "
                AND `cache_page`.`XHR` = " . $db->toSql($cache_xhr);
    $db->query($sSQL);
    if($db->nextRecord()) {
		//httpstatus
	    if($http_status_code != $db->getField("http_status_code", "Text", true))
	        $query["update"]["http_status_code"]   = "`http_status_code` = " . $db->toSql($http_status_code);

		//Section Block
	    if($query["insert"]["section_blocks"] != $db->getField("section_blocks", "Text", true))
	        $query["update"]["section_blocks"]   = "`section_blocks` = " . $db->toSql($query["insert"]["section_blocks"], "Text", true);

		//Layout Block
	    if($query["insert"]["layout_blocks"] != $db->getField("layout_blocks", "Text", true))
	        $query["update"]["layout_blocks"]   = "`layout_blocks` = " . $db->toSql($query["insert"]["layout_blocks"], "Text", true);

		//FF Block
	    if($query["insert"]["ff_blocks"] != $db->getField("ff_blocks", "Text", true))
	        $query["update"]["ff_blocks"]   = "`ff_blocks` = " . $db->toSql($query["insert"]["ff_blocks"], "Text", true);

		//Data V Block
	    if($query["insert"]["data_v_block"] != $db->getField("data_v_block", "Text", true))
	        $query["update"]["data_v_block"]   = "`data_v_block` = " . $db->toSql($query["insert"]["data_v_block"], "Text", true);

		//Data G Block
	    if($query["insert"]["data_g_block"] != $db->getField("data_g_block", "Text", true))
	        $query["update"]["data_g_block"]   = "`data_g_block` = " . $db->toSql($query["insert"]["data_g_block"], "Text", true);

		//Data S Block
	    if($query["insert"]["data_s_block"] != $db->getField("data_s_block", "Text", true))
	        $query["update"]["data_s_block"]   = "`data_s_block` = " . $db->toSql($query["insert"]["data_s_block"], "Text", true);

		//Data D Block
	    if($query["insert"]["data_d_block"] != $db->getField("data_d_block", "Text", true))
	        $query["update"]["data_d_block"]   = "`data_d_block` = " . $db->toSql($query["insert"]["data_d_block"], "Text", true);

		//Data T Block
	    if($query["insert"]["data_t_block"] != $db->getField("data_t_block", "Text", true))
	        $query["update"]["data_t_block"]   = "`data_t_block` = " . $db->toSql($query["insert"]["data_t_block"], "Text", true);

		//Data M Block
	    if($query["insert"]["data_m_block"] != $db->getField("data_m_block", "Text", true))
	        $query["update"]["data_m_block"]   = "`data_m_block` = " . $db->toSql($query["insert"]["data_m_block"], "Text", true);

		if($query["update"]) {
			$sSQL  = "UPDATE `cache_page` SET 
                     " . (implode(", " , $query["update"])). "
                     , `last_update` = " . $db->toSql($last_update, "Number") . "
                WHERE `cache_page`.`user_path` = " . $db->toSql($user_path) . "
                    AND `cache_page`.`disk_path` = " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                    AND `cache_page`.`filename` = " . $db->toSql($globals->cache["file"]["filename"]);
            $db->execute($sSQL);
		}
    } else {
        $sSQL = "INSERT INTO `cache_page` 
                (
                    ID
                    , `user_path`
                    , `disk_path`
                    , `filename`
                    , `ext`
                    , `lang`
                    , `get`
                    , `http_status_code`
                    , `section_blocks`
                    , `layout_blocks`
                    , `ff_blocks`
					, `data_v_block`
	                , `data_g_block`
	                , `data_s_block`
	                , `data_d_block`
	                , `data_t_block`
	                , `data_m_block`
                    , `last_update`
                    , `frequency`
                    , `use_in_sitemap`
                    , `XHR`
                    , `ID_domain`
                )
                VALUES 
                (
                    NULL
                    , " . $db->toSql($user_path) . " 
                    , " . $db->toSql($globals->cache["file"]["cache_path"]) . " 
                    , " . $db->toSql($globals->cache["file"]["filename"]) . " 
                    , " . $db->toSql($cache_ext) . " 
                    , " . $db->toSql(strtolower($globals->cache["params"]["lang"])) . " 
                    , " . $db->toSql($globals->cache["request"]["get"] && is_array($globals->cache["request"]["get"]["query"]) && count($globals->cache["request"]["get"]["query"])
                                        ? implode("&", $globals->cache["request"]["get"]["query"])
                                        : ""
                                    ) . " 
                    , " . $db->toSql($http_status_code) . " 
                    , " . $db->toSql($query["insert"]["section_block"]) . " 
                    , " . $db->toSql($query["insert"]["layout_blocks"]) . " 
                    , " . $db->toSql($query["insert"]["ff_blocks"]) . " 
                    , " . $db->toSql($query["insert"]["data_v_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_g_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_s_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_d_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_t_block"]) . " 
                    , " . $db->toSql($query["insert"]["data_m_block"]) . " 
                    , " . $db->toSql($last_update, "Number") . " 
                    , " . $db->toSql($frequency) . "
                    , " . $db->toSql($use_in_sitemap, "Number") . "
                    , " . $db->toSql($cache_xhr) . "
                    , " . $db->toSql($globals->ID_domain, "Number") . "
                )";
        $db->execute($sSQL);
    }

}

function system_write_cache_error_document($cache_file = null, $expires = null)
{
   // $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

    if(!$cache_file)
        $cache_file = $globals->cache["file"];

    $arrUserPath = explode("/", $globals->user_path);

    $errorDocumentFile = $cache_file["error_path"] . "/" . $arrUserPath[1];
    $user_path = str_replace(CM_CACHE_DISK_PATH, "", $cache_file["cache_path"]);

    check_function("Filemanager");

    $fs = new Filemanager("php", $errorDocumentFile);
    $fs->update(array(
        $user_path . "/" . $cache_file["filename"] => $globals->user_path
    ));
}

/*
function system_write_cache_stats_old($buffer, $page = null, $expires = null) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

    if(!$page)
        $page = $globals->cache["user_path"] . "/" . str_replace("_XHR", "", $globals->cache["file"]["filename"]);

    if(!$expires)
        $expires = time() + (60 * 60 * 24 * 1);

    $s = array();

	check_function("Filemanager");

	$fs = new Filemanager("php", $globals->cache["file"]["cache_path"] . "/stats.php", "s");

    $s[$page]["tags"] = array();
    if(is_array($globals->seo) && count($globals->seo)) {
        foreach($globals->seo AS $seo_type => $seo_data)
        {
            if($seo_type == "current")
                continue;

            if($seo_data["tags"]["primary"])
                $s[$page]["tags"]["primary"] .= ($s[$page]["tags"]["primary"] ? "," : "") . $seo_data["tags"]["primary"];
            if($seo_data["tags"]["secondary"])
                $s[$page]["tags"]["secondary"] .= ($s[$page]["tags"]["secondary"] ? "," : "") . $seo_data["tags"]["secondary"];
            if($seo_data["tags"]["rel"])
                $s[$page]["tags"]["rel"] .= ($s[$page]["tags"]["rel"] ? "," : "") . $seo_data["tags"]["rel"];

        }
    }
    $s[$page]["keywords"] = array();
    $s[$page]["blocks"] = array(
        "section" => $globals->cache["section_blocks"]
        , "layout" => $globals->cache["layout_blocks"]
        , "data" => $globals->cache["data_blocks"]
        , "ff_blocks" => $globals->cache["ff_blocks"]
    );
    $s[$page]["hits"] = $s[$page]["hits"] + 1;

    if(!$cm->isXHR()) {
        $s[$page]["meta"] = $cm->oPage->page_meta;
        $s[$page]["title"] = $cm->oPage->title;
        $s[$page]["js"] = $cm->oPage->page_defer["js"];
        $s[$page]["css"] = $cm->oPage->page_defer["css"];

        $link_path = $globals->cache["file"]["cache_path"] . "/" . basename($globals->cache["file"]["cache_path"]);
        $res_path = str_replace(CM_CACHE_DISK_PATH, "/asset", $link_path);
        if(is_array($cm->oPage->page_defer["css"]) && count($cm->oPage->page_defer["css"]) == 1) {
            $real_file = str_replace("/asset", CM_CACHE_DISK_PATH, $cm->oPage->page_defer["css"][0]);

            //symlink($real_file, $link_path . ".css");
            //symlink(str_replace(".css", ".css.gz", $real_file), $link_path . ".css.gz");

           // $buffer = str_replace($cm->oPage->page_defer["css"][0], $res_path . ".css", $buffer);
        }
        if(is_array($cm->oPage->page_defer["js"]) && count($cm->oPage->page_defer["js"]) == 1) {
            $real_file = str_replace("/asset", CM_CACHE_DISK_PATH, $cm->oPage->page_defer["js"][0]);

            //symlink($real_file, $link_path . ".js");
            //symlink(str_replace(".js", ".js.gz", $cm->oPage->page_defer["js"][0]), $link_path . ".js.gz");

           // $buffer = str_replace($cm->oPage->page_defer["js"][0], $res_path . ".js", $buffer);
        }

    }
	$fs->write($s);

    cm_filecache_write($globals->cache["file"]["cache_path"], "stats.php", "<?php\n" . '$s = ' . var_export($s, true) . ";", $expires);

    return $buffer;
}
*/

function system_destroy_cache_token_session() {
	//$precision = 8;
    //$cookie_name = "_ut";
    $file_token = CM_CACHE_DISK_PATH . "/token.php";

	$token = cache_token_get_session_cookie();
	if($token) {
		$objToken = cache_token_resolve($token);

		$file_token = CM_CACHE_DISK_PATH . "/token/" . $objToken["public"] . ".php";
		if(is_file($file_token)) {
			require($file_token);

            /** @var include $u */
            if($u["uniqid"] == $objToken["private"])
				@unlink($file_token);
			
		}	
		/*
		$stoken = bin2hex(openssl_random_pseudo_bytes($precision));
		$new_private = uniqid($stoken);
		
		$public = substr($token, 0, strlen($token) - strlen($new_private));
		$private = substr($token, strlen($public));

		$file_token_dir = CM_CACHE_DISK_PATH . "/token";
	    $file_token = $file_token_dir . "/" . $public . ".php";

		if(is_file($file_token)) {
			require($file_token);

			if($u["uniqid"] == $private) 
				@unlink($file_token);
			
		}	*/
		cache_token_destroy_session_cookie();
	}

//	$sessionCookie = session_get_cookie_params();
//	setcookie($cookie_name, false, null, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
}
