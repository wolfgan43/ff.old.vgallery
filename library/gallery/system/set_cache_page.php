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

    if(!defined("DISABLE_CACHE") && $globals->cache["enabled"] !== false) {
        $expires = time() + (60 * 60 * 24 * 1);

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

        if($buffer && http_response_code() == 200) {
            if(!is_dir($cache_file["cache_path"]))
                @mkdir($cache_file["cache_path"], 0777, true);

            $buffer = system_write_cache_stats($buffer);
            if ($cache_file["primary"] != $cache_file["gzip"])
                cm_filecache_write($cache_file["cache_path"], $cache_file["primary"], $buffer, $expires);

            cm_filecache_write($cache_file["cache_path"], $cache_file["gzip"], gzencode($buffer), $expires);

            cache_writeLog($cache_file["cache_path"] . "/" . $cache_file["primary"], "log_saved");
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
        profiling_stats((defined("DISABLE_CACHE")
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
    $user_path = str_replace(CM_CACHE_PATH, "", $cache_file["cache_path"]);

    check_function("Filemanager");

    $fs = new Filemanager("php", $errorDocumentFile);
    $fs->update(array(
        $user_path . "/" . $cache_file["filename"] => $globals->user_path
    ), "p");
}

function system_write_cache_stats($buffer, $page = null, $expires = null) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

	check_function("Storage");

	//todo: Impostazioni di base da fare come oggetto
	$service = "server";
	$this_controllers = array(
		"server" => array(
			"default" => false
			, "services" => null
			, "storage" => array(
				"nosql" => null
				//, "fs" => null
			)
		)
	);
	$this_struct = array(
		"connectors" => array(
			"sql"                       => array(
				"prefix"				=> "CACHE_DATABASE_"
				, "table"               => "cache_pages"
				, "key"                 => "ID"
			)
			, "nosql"                   => array(
				"prefix"				=> "CACHE_MONGO_DATABASE_"
				, "table"               => "cache_pages"
				, "key"                 => "ID"
			)
			, "fs"                      => array(
				"path"                  => "/cache/notify"
				, "name"                => "title"
				, "var"					=> "s"
			)
		)
		, "table" => array(
			"db" => array(
				"title" 				=> "title"
			)
		)
		, "type" => array(
			"url"						=> "string"
			, "get"						=> "array"
			, "domain"					=> "string"
			, "type"					=> "string"

			, "title" 					=> "string"
			, "description" 			=> "string"
			, "cover"					=> array(
				"url" 					=> "string:toImage"
				, "width" 				=> "number"
				, "height" 				=> "number"
			)
			, "author" 					=> array(
				"id" 					=> "number"
				, "avatar" 				=> "string:toImage"
				, "name" 				=> "string"
				, "url" 				=> "string"
				, "tags"				=> array(
					"primary" 			=> "arrayOfNumber"
					, "secondary" 		=> "arrayOfNumber"
				)
				, "uid"					=> "number"
			)
			, "tags"					=> array(
				"primary" 				=> "arrayOfNumber"
				, "secondary" 			=> "arrayOfNumber"
				, "rel" 				=> "arrayOfNumber"
			)
			, "meta"					=> "array"
			, "links"					=> "array"
			, "microdata"				=> "array"
			, "js"						=> array(
				"url" 					=> "string"
				, "keys" 				=> "array"
			)
			, "css"						=> array(
				"url" 					=> "string"
				, "keys" 				=> "array"
			)
			, "international"			=> "array"
			, "settings"				=> "array" 	//$globals->page
			, "template_layers"			=> "array"	//$globals->cache["layer_blocks"]
			, "template_sections"		=> "array"	//$globals->cache["section_blocks"]
			, "template_blocks"			=> "array"	//$globals->cache["layout_blocks"]
			, "template_ff"				=> "array"
			, "keys_D"					=> "arrayOfNumber"
			, "keys_G"					=> "array"
			, "keys_M"					=> "array"
			, "keys_S"					=> "array"
			, "keys_T"					=> "array"
			, "keys_V"					=> "arrayOfNumber"
			, "http_status"				=> "number"
			, "created"					=> "number"
			, "last_update"				=> "number"
			, "cache_last_update"		=> "number"
			, "cache"					=> "array"
		)
	);
	$struct = $this_controllers[$service]["struct"];

	$connectors = $this_controllers[$service]["storage"];
	foreach($connectors AS $type => $data)
	{
		if(!$data)
		{
			$connectors[$type] = array(
				"service" => null
				, "connector" => $this_struct["connectors"][$type]
			);
		}
	}
	$storage = Storage::getInstance($connectors, array(
		"struct" => $this_struct["type"]
	));

	//codice operativo
	$created 							= time();

	$tags = array(
		"primary" 					=> array()
		, "secondary" 				=> array()
		, "rel" 					=> array()
	);

	if(is_array($globals->seo) && count($globals->seo)) {
		foreach($globals->seo AS $seo_type => $seo_data)
		{
			if($seo_type == "current")
				continue;

			if($seo_data["tags"]["primary"])
				$tags["primary"] 	= array_replace($tags["primary"], array_fill_keys(explode(",", $seo_data["tags"]["primary"]), true));
			if($seo_data["tags"]["secondary"])
				$tags["secondary"] 	= array_replace($tags["secondary"], array_fill_keys(explode(",", $seo_data["tags"]["secondary"]), true));
			if($seo_data["tags"]["rel"])
				$tags["rel"] 		= array_replace($tags["rel"], array_fill_keys(explode(",", $seo_data["tags"]["rel"]), true));
		}
	}

	if(is_array($cm->oPage->page_js) && count($cm->oPage->page_js)) {
		$page_js 					= $cm->oPage->page_js;
	}

	if(is_array($cm->oPage->page_css) && count($cm->oPage->page_css)) {
		$page_css 					= array_diff($cm->oPage->page_css, $globals->links);
	}

	$s = array(
		"url"						=> $globals->cache["user_path"]
		, "get"						=> $_GET /* (is_array($globals->request) && count($globals->request)
										? $globals->request
										: array()
									) da approfondire*/
		, "domain"					=> DOMAIN_INSET
		, "type"					=> $globals->seo["current"]

		, "title" 					=> $cm->oPage->title
		, "description" 			=> $cm->oPage->page_meta["description"]["content"]
		, "cover"					=> array_filter($globals->cover)
		, "author" 					=> $globals->author
		, "tags"					=> array(
			"primary" 				=> (is_array($tags["primary"]) && count($tags["primary"])
										? array_keys($tags["primary"])
										: array()
									)
			, "secondary" 			=> (is_array($tags["secondary"]) && count($tags["secondary"])
										? array_keys($tags["secondary"])
										: array()
									)
			, "rel" 				=> (is_array($tags["rel"]) && count($tags["rel"])
										? array_keys($tags["rel"])
										: array()
									)
		)
		, "meta"					=> $cm->oPage->page_meta
		, "links"					=> $globals->links
		, "microdata"				=> $globals->microdata
		, "js"						=> array(
			"url" 					=> (is_array($cm->oPage->page_defer["js"]) && count($cm->oPage->page_defer["js"])
										? $cm->oPage->page_defer["js"][0]
										: ""
									)
			, "keys" 				=> $page_js
		)
		, "css"						=> array(
			"url" 					=> (is_array($cm->oPage->page_defer["css"]) && count($cm->oPage->page_defer["css"])
										? $cm->oPage->page_defer["css"][0]
										: ""
									)
			, "keys" 				=> $page_css
		)
		, "international"			=> ffTemplate::_get_word_by_code("", null, null, true)
		, "settings"				=> $globals->page
		, "template_layers"			=> $globals->cache["layer_blocks"]
		, "template_sections"		=> $globals->cache["section_blocks"]
		, "template_blocks"			=> (is_array($globals->cache["layout_blocks"]) && count($globals->cache["layout_blocks"])
										? array_keys($globals->cache["layout_blocks"])
										: array()
									)
		, "template_ff"				=> $globals->cache["ff_blocks"]
		, "keys_D"					=> (is_array($globals->cache["data_blocks"]["D"]) && count($globals->cache["data_blocks"]["D"])
										? array_keys($globals->cache["data_blocks"]["D"])
										: array()
									)
		, "keys_G"					=> (is_array($globals->cache["data_blocks"]["G"]) && count($globals->cache["data_blocks"]["G"])
										? array_keys($globals->cache["data_blocks"]["G"])
										: array()
									)
		, "keys_M"					=> (is_array($globals->cache["data_blocks"]["M"]) && count($globals->cache["data_blocks"]["M"])
										? array_keys($globals->cache["data_blocks"]["M"])
										: array()
									)
		, "keys_S"					=> (is_array($globals->cache["data_blocks"]["S"]) && count($globals->cache["data_blocks"]["S"])
										? array_keys($globals->cache["data_blocks"]["S"])
										: array()
									)
		, "keys_T"					=> (is_array($globals->cache["data_blocks"]["T"]) && count($globals->cache["data_blocks"]["T"])
										? array_keys($globals->cache["data_blocks"]["T"])
										: array()
									)
		, "keys_V"					=> (is_array($globals->cache["data_blocks"]["V"]) && count($globals->cache["data_blocks"]["V"])
										? array_keys($globals->cache["data_blocks"]["V"])
										: array()
									)
		, "http_status"				=> $globals->http_status
		, "created"					=> $created
		, "last_update"				=> $created
		, "cache_last_update"		=> $created
		, "cache"					=> str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
	);

	//print_r($s);
//	die();


	$res = $storage->write(
		$s
		, array(
			"set" => array(
				"title" 				=> $cm->oPage->title
				, "description" 		=> $cm->oPage->page_meta["description"]["content"]

				, "keys_D"				=> $s["keys_D"]
				, "keys_G"				=> $s["keys_G"]
				, "keys_M"				=> $s["keys_M"]
				, "keys_S"				=> $s["keys_S"]
				, "keys_T"				=> $s["keys_T"]
				, "keys_V"				=> $s["keys_V"]
				, "http_status"			=> $s["http_status"]
				, "last_update"	        => $created
				, "cache"				=> "+" . str_replace(CM_CACHE_PATH, "", $globals->cache["file"]["cache_path"]) . "/" . $globals->cache["file"]["primary"]
			)
			, "where" => array(
				"url" 					=> $globals->cache["user_path"]
				, "domain" 				=> DOMAIN_INSET
				, "get" 				=> $_GET
			)
		)
	);

	return $buffer;
}

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
        $res_path = str_replace(CM_CACHE_PATH, "/asset", $link_path);
        if(is_array($cm->oPage->page_defer["css"]) && count($cm->oPage->page_defer["css"]) == 1) {
            $real_file = str_replace("/asset", CM_CACHE_PATH, $cm->oPage->page_defer["css"][0]);
            
            //symlink($real_file, $link_path . ".css");
            //symlink(str_replace(".css", ".css.gz", $real_file), $link_path . ".css.gz");
            
           // $buffer = str_replace($cm->oPage->page_defer["css"][0], $res_path . ".css", $buffer);
        }
        if(is_array($cm->oPage->page_defer["js"]) && count($cm->oPage->page_defer["js"]) == 1) {
            $real_file = str_replace("/asset", CM_CACHE_PATH, $cm->oPage->page_defer["js"][0]);
            
            //symlink($real_file, $link_path . ".js");
            //symlink(str_replace(".js", ".js.gz", $cm->oPage->page_defer["js"][0]), $link_path . ".js.gz");
            
           // $buffer = str_replace($cm->oPage->page_defer["js"][0], $res_path . ".js", $buffer);
        }
        
    }
	$fs->write($s);

    cm_filecache_write($globals->cache["file"]["cache_path"], "stats.php", "<?php\n" . '$s = ' . var_export($s, true) . ";", $expires);
    
    return $buffer;
}

function system_write_cache_token_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id, $permanent_session) {
	if($UserID == MOD_SEC_GUEST_USER_ID)
		return false;
	if(!$permanent_session)
		return false;

	$u = array();
	$user_permission = get_session("user_permission");
	$account = ($user_permission["username_slug"]
		? $user_permission["username_slug"]
		: ($user_permission["username"]
			? ffCommon_url_rewrite($user_permission["username"])
			: ffCommon_url_rewrite($user_permission["email"])
		)
	);
	$gid = ($user_permission["primary_gid_name"]
                ? $user_permission["primary_gid_name"]
                : $user_permission["primary_gid_default_name"]
            );

	
	//$precision = 8;
	$file_token_dir = CM_CACHE_PATH . "/token";

    $token = cache_token_get_session_cookie();
    if($token) {
    	$objToken = cache_token_resolve($token, $account);
		if(is_file($file_token_dir . "/" . $objToken["public"] . ".php")) {
			require($file_token_dir . "/" . $objToken["public"] . ".php");
			if($u["uniqid"] == $objToken["private"]) {
				if($u["expire"] >= time()) {    
					$token_valid = true;
				}
			}

			if(!$token_valid)
				unlink($file_token_dir . "/" . $objToken["public"] . ".php");
		}    	
    } else {
    	$objToken["new"] = cache_token_generate($account);
    }

	if(!$token_valid) {
		//cache_token_set_session_cookie($objToken["new"]);

	    //$u["logs"][$_SERVER["REMOTE_ADDR"]]++;
	    $u = array( 
			"account" => $UserID
			, "uid" => $UserNID
			, "group" => $gid
			, "uniqid" => $objToken["new"]["private"]
			, "expire" => $objToken["new"]["expire"]
			, "renew" => true
			, "addr" => $_SERVER["REMOTE_ADDR"]
			, "agent" => $_SERVER["HTTP_USER_AGENT"] 
		);
				
		cache_token_write($u, $objToken["new"]);

/*
	    $file_token = $file_token_dir . "/" . $objToken["new"]["public"] . ".php";
		if(!is_dir($file_token_dir))
			@mkdir($file_token_dir, 0777, true);
		
		$content = "<?php\n";
		$content .= '$u = ' . var_export($u, true) . ";";
 		if($handle = @fopen($file_token, 'w')) {
     		@fwrite($handle, $content); 
     		@fclose($handle);
		}*/              
	}
}

function system_destroy_cache_token_session() {
	//$precision = 8;
    //$cookie_name = "_ut";
    $file_token = CM_CACHE_PATH . "/token.php";

	$token = cache_token_get_session_cookie();
	if($token) {
		$objToken = cache_token_resolve($token);

		$file_token = CM_CACHE_PATH . "/token/" . $objToken["public"] . ".php";
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

		$file_token_dir = CM_CACHE_PATH . "/token";
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
