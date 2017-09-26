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
function system_gallery_redirect($path_info, $query = null, $hostname = null, $write_cache = true, $skip_db = false) {
     if($hostname === null)
        $hostname = $_SERVER["HTTP_HOST"];
        
    if($query === null)
    	$query = $_SERVER["QUERY_STRING"];
	
	$request_uri = $path_info;
    if(strlen($query))
        $request_uri .= "?" . $query;
    
    $arrDestination = system_redirect_get_destination($hostname, $request_uri);		
	if($write_cache && is_array($arrDestination))
		$write_cache = false;

    if($write_cache && !is_array($arrDestination)) {
	    $redir = system_redirect_get_rule($hostname, $write_cache, $skip_db);
	    $arrRedirect = array_filter($redir, function($redirect) use ($request_uri) { 
    		return preg_match("#" . str_replace("\*", "(.*)", preg_quote($redirect["src"], "#")) . "#i", $request_uri); 
	    });
		 
	    if(is_array($arrRedirect))
	    {
	        if(count($arrRedirect) == 1)
	        {
	            $arrDestination = current($arrRedirect);
	        } elseif(count($arrRedirect) > 1)
	        {
	            $base_pathinfo = basename($path_info);
	            foreach($arrRedirect AS $redirect) {
	                $arrSubString = explode("%", $redirect["src"]);
	                $difference = $base_pathinfo;
	                if(is_array($arrSubString) && count($arrSubString)) {
	                    foreach($arrSubString AS $sub_string) {
	                        $difference = str_replace($sub_string, "", $difference);
	                    }
	                }

	                $count[]  = strlen($difference);
	                $slash[] = substr_count($redirect["src"], "/");
	            }

	            array_multisort($count, SORT_ASC, $slash, SORT_DESC, $arrRedirect);
	            
	            $arrDestination = current($arrRedirect);
	        }
	    }
	}
    
    $src = str_replace("\*", "(.*)", preg_quote($arrDestination["src"], "#"));
    if(strpos($src, "(") !== false && strpos($arrDestination["dst"], "$") !== false)
        $arrDestination["dst"] = preg_replace("#" .  $src . "#i", $arrDestination["dst"], $request_uri);
    
    return system_redirect_goto_destination($arrDestination, $hostname, $request_uri, $write_cache);
}


function system_redirect_get_destination($hostname, $request_uri) {
	if(is_file(FF_DISK_PATH . "/cache/redirect/" . $hostname . ".php")) {
		$filesize = floor(filesize(FF_DISK_PATH . "/cache/redirect/" . $hostname . ".php") / 1000000);
		
		$memory_limit = ($filesize * 8);
		if($memory_limit > MEMORY_LIMIT)
			@ini_set("memory_limit", $memory_limit . 'M');
	
		require(FF_DISK_PATH . "/cache/redirect/" . $hostname . ".php");
		
		if(array_key_exists($request_uri, $r)) {
			return $r[$request_uri];
		}
	}
}

function system_redirect_goto_destination($arrDestination, $hostname = null, $request_uri = null, $write_cache = false) {
    if($hostname === null)
        $hostname = $_SERVER["HTTP_HOST"];

    if($request_uri === null) {
		$request_uri = $_SERVER["REQUEST_URI"];
    }
    if(is_array($arrDestination) && count($arrDestination) && $arrDestination["dst"])
    {
        $http_response_code = $arrDestination["code"];
        if(!$http_response_code)
            $http_response_code = 301;

        if($write_cache)            
            system_redirect_set_destination($hostname, $request_uri, $arrDestination["dst"], $arrDestination["code"]);

        if(defined("TRACE_VISITOR")) {
            require_once(FF_DISK_PATH . "/library/gallery/system/trace.php");
            system_trace("redirect", "routing"); 
        }   

        cache_send_header_content(false, false, false, false); 
        
        header("Location: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $arrDestination["dst"], true, $http_response_code);
        exit;
    }
    
    return false;
}

function system_redirect_set_destination($hostname, $request_uri, $destination, $code = '') {
	static $loaded_redir = array();
	
	$r = $loaded_redir[$hostname];
	
	$file = FF_DISK_PATH . "/cache/redirect/" . $hostname . ".php";
	if(!$loaded_redir[$hostname]) {	
		$loaded_redir[$hostname] = array();
		if(is_file($file)) {
			require($file);

		} elseif(!is_dir(dirname($file))) {
			mkdir(dirname($file), 0777, true);
		}
	}

	$r[str_replace(array('\\', '"'), array('\\\\', '\"'), $request_uri)]["dst"] = $destination;
	if($code)
		$r[str_replace(array('\\', '"'), array('\\\\', '\"'), $request_uri)]["code"] = $code;
	
	$loaded_redir[$hostname] = $r;	
	
	@file_put_contents($file, "<?php\n\n" . '$r = ' . var_export($r, true) . ";\n\n", LOCK_EX);
	
	//return $loaded_redir[$hostname];
}

function system_redirect_get_rule($hostname, $write_cache = false, $skip_db = false) {
	static $loaded_redir = array();
	
	$file = FF_DISK_PATH . "/cache/redirect/" . $hostname . ".rule.php";
	if(!$loaded_redir[$hostname]) {
		if(is_file($file)) {
			require($file);
		} else {
			$redir = array();
			if($write_cache && !is_dir(dirname($file)))
				mkdir(dirname($file), 0777, true);
			
			if(!$skip_db) {
	            $db = new ffDB_Sql();
	            $db->on_error = "ignore";

	            if($db->connect(FF_DATABASE_NAME, FF_DATABASE_HOST, FF_DATABASE_USER, FF_DATABASE_PASSWORD)) 
	            {			
					$sSQL = "SELECT cache_page_redirect.*
						    FROM cache_page_redirect
						    WHERE cache_page_redirect.status > 0 
				    			AND (cache_page_redirect.src_host = " . $db->toSql($hostname)
				    			. ($hostname == $_SERVER["HTTP_HOST"] || $hostname == "www." . $_SERVER["HTTP_HOST"]
            						? " OR cache_page_redirect.src_host = " . $db->toSql("")
            						: ""
						        ) . ")";
					$db->query($sSQL);
					if($db->nextRecord()) {
						$i = 0;
						do {
						    $redir[$i]["src"] = str_replace(array("%"/*, "*"*/), "*",  $db->getField("src_path", "Text", true));
						    $redir[$i]["dst"] = $db->getField("destination", "Text", true);
						    if($db->getField("header", "Number", true) != 301)
				    			$redir[$i]["code"] = $db->getField("header", "Number", true);

							$i++;
						} while ($db->nextRecord());
					}
				}
			}
		 }   

		 $loaded_redir[$hostname] = $redir;
	}

	if($write_cache && $redir)
		@file_put_contents($file, "<?php\n\n" . '$redir = ' . var_export($redir, true) . ";\n\n", LOCK_EX);			
	
	return $loaded_redir[$hostname];
}



function system_redirect_set_destination_old($hostname, $request_uri, $destination, $code = '') {
	clearstatcache();
	$file = FF_DISK_PATH . "/cache/redirect/" . $hostname . ".php";

	if(!is_dir(dirname($file))) {
		mkdir(dirname($file), 0777, true);
	}

	if(@filesize($file))
		$content = "";
	else
		$content = "<?php\n";

 	if($handle = @fopen($file, 'a')) {
		$content .= '$r["' .  str_replace(array('\\', '"'), array('\\\\', '\"'), $request_uri) . '"] = array("dst" => "' . $destination . '"' . ($code 
														? ' "code" => "' . $code . '"'
														: ''
													). ');' . "\n";
     	@fwrite($handle, $content); 
     	
     	@fclose($handle);
	}
}

function system_redirect_get_rule_old($hostname, $write_cache = false) {
	static $loaded_redirect = null;

	if(!is_array($loaded_redirect[$hostname])) {
		$loaded_redirect[$hostname] = array();
		if(is_file(FF_DISK_PATH . "/cache/redirect/" . $hostname . ".rule.php")) {
			require_once(FF_DISK_PATH . "/cache/redirect/" . $hostname . ".rule.php");
			
			$loaded_redirect[$hostname] = $redir;
		} else {
			 $db = ffDB_Sql::factory();
		
			 clearstatcache();
			 
			 $file = FF_DISK_PATH . "/cache/redirect/" . $hostname . ".rule.php";
			 if($write_cache) {
			 	if(!is_dir(dirname($file)))
			 		mkdir(dirname($file), 0777, true);
 				
 				if($handle = @fopen($file, 'w')) {
     				@fwrite($handle,"<?php\n"
     					. '$redir = array();' . "\n"
     				);
				}
			 }
			 $sSQL = "SELECT cache_page_redirect.*
				    FROM cache_page_redirect
				    WHERE cache_page_redirect.src_host = " . $db->toSql($hostname)
				        . ($hostname == "www." . DOMAIN_NAME || $hostname == DOMAIN_NAME
            				? " OR cache_page_redirect.src_host = " . $db->toSql("")
            				: ""
				        );
			$db->query($sSQL);
			if($db->nextRecord()) {
				$i = 0;
				do {
				    $loaded_redirect[$hostname][$i]["src"] = str_replace(array("%", "\*"), "(.*)", preg_quote($db->getField("src_path", "Text", true), "#"));
				    $loaded_redirect[$hostname][$i]["dst"] = $db->getField("destination", "Text", true);
				    if($db->getField("header", "Number", true) != 301)
				    	$loaded_redirect[$hostname][$i]["code"] = $db->getField("header", "Number", true);

					if($write_cache) {
					    $content = '$redir[' .  str_replace(array('\\', '"'), array('\\\\', '\"'), $i) . ']["src"] = "' . $loaded_redirect[$hostname][$i]["src"] . '";' . "\n"
					    		 . '$redir[' .  str_replace(array('\\', '"'), array('\\\\', '\"'), $i) . ']["dst"] = "' . $loaded_redirect[$hostname][$i]["dst"] . '";' . "\n"
					    		 . ($loaded_redirect[$hostname][$i]["code"] 
					    		 	? '$redir[' .  str_replace(array('\\', '"'), array('\\\\', '\"'), $i) . ']["code"] = "' . $loaded_redirect[$hostname][$i]["code"] . '";' . "\n" 
					    		 	: ""
					    		 );
					    @fwrite($handle, $content);
					}
					$i++;
				} while ($db->nextRecord());
			}
 			
 			if($write_cache)
 				@fclose($handle);
		 }   
	}
	
	return $loaded_redirect[$hostname];
}

function system_redirect_set_rule($hostname, $request_uri, $destination, $code = "301") {
	$db = ffDB_Sql::factory();
	
	$rule["src"]["host"] = ($hostname == DOMAIN_NAME
							? ""
							: $hostname
						);	
	$rule["src"]["path"] = $request_uri;
	$rule["hostname"] = (strpos($hostname, "www.") === 0
						? $hostname
						: (substr_count($hostname, ".") > 1
							? $hostname
							: "www." . $hostname
						)
					);
	$rule["destination"] = $destination;
	$rule["http_status"] = $code;
	
	$sSQL = "SELECT cache_page_redirect.*
			FROM cache_page_redirect
			WHERE cache_page_redirect.src_host = " . $db->toSql($rule["src"]["host"])
				. ($rule["src"]["host"] == "www." . DOMAIN_NAME || $rule["src"]["host"] == DOMAIN_NAME
            		? " OR cache_page_redirect.src_host = " . $db->toSql("")
            		: ""
				) . " AND cache_page_redirect.src_path = " . $db->toSql($rule["src"]["path"]);
	$db->query($sSQL);
	if($db->nextRecord()) {
		if($db->getField("destination", "Text", true) != $rule["destination"]) {
			$rule["ID"] = $db->getField("ID", "Number", true);
			$sSQL = "UPDATE cache_page_redirect SET 
						cache_page_redirect.destination = "	. $db->toSql($rule["destination"]) . " 
						, cache_page_redirect.last_update = " . $db->toSql(time(), "Number") . " 
					WHERE cache_page_redirect.ID = " . $db->toSql($rule["ID"], "Number");
			$db->execute($sSQL);
		}
	} else {
		$sSQL = "INSERT INTO cache_page_redirect
				(
					ID
					, header
					, last_update
					, src_host
					, src_path
					, destination
					, status
				)
				VALUES
				(
					null
					, " . $db->toSql($rule["http_status"]) . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql($rule["src"]["host"]) . "
					, " . $db->toSql($rule["src"]["path"]) . "
					, " . $db->toSql($rule["destination"]) . "
					, " . $db->toSql("1", "Number") . "
				)";
		$db->execute($sSQL);
		$rule["ID"] = $db->getInsertID(true);
	}
	
	if($rule["ID"]) {
		@unlink(FF_DISK_PATH . "/cache/redirect/" . $rule["hostname"] . ".php");
    	@unlink(FF_DISK_PATH . "/cache/redirect/" . $rule["hostname"] . ".rule.php");
	}
	
	return $rule;
}