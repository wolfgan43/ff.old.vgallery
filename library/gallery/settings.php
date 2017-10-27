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
 	$schema["api"] = array();
    $schema["api"]["oAuth"]["/"] = array(
        "scopes" => array(
            "get" => false
        , "post" => "userinfo"
        , "patch" => "userinfo"
        , "delete" => "userinfo"
        )
    );
	 
	$schema["alias"] = array();
	
	$schema["rule"] = array();

	$schema["request"] = array();
	$schema["request"]["/"] = array(
		"get" => false
        , "post" => false
        , "log" => true
	);
	$schema["request"]["/login"] = array(
		"get" => array(
	        "sid"
	        , "frmAction"
	        , "username"
	        , "desc"
	        , "ret_url"
			, "state"
	    )
	    , "post" => true
	    , "nocache" => true
	);
	$schema["request"]["/login/social"] = array(
		"get" => true
	);		
	$schema["request"]["user"] = array(
		"get" => true
        , "post" => true
        , "nocache" => true
	);
	$schema["request"]["frame"] = array(
		"get" => array(
            "sid"
        )
	);	
    $schema["request"]["/api"] = array(
		"get" => true
	);	
    $schema["locale"] = array();
    $schema["locale"]["lang"] = array();
    $schema["locale"]["lang"] = array(
    	"ITA" => array(
    		"ID" => 1
	        , "tiny_code" => "it"
	        , "description" => "Italiano"
        )
    	, "ENG" => array(
    		"ID" => 2
        	, "tiny_code" => "en"
        	, "description" => "English"
        )
    );

    $schema["locale"]["rev"] = array();
    $schema["locale"]["rev"]["lang"] = array(
    	"it" => "ITA"
    	, "en" => "ENG"
    );
    $schema["locale"]["rev"]["country"] = array(
		"it" => "ITA"
		, "us" => "ENG"    
    );    
    $schema["locale"]["rev"]["key"] = array(
		"1" => "ITA"
		, "2" => "ENG"    
    );  
    
  $schema["priority"] = array();
  $schema["priority"][] = "/";
  

  $schema["page"] = array();

  
/**
* Reserved Path
*/  
/*
  $schema["page"]["login"] = array(
  	"name" => "login"
  	, "cache" => "user" 				//user, group, true, false, Path start with /
  	, "cache_client" => null			//null, false, noxhr, nohttp
  	, "cache_path" => null				//null, Path start with /
  	, "primary" => true 				//true, false
  	, "restricted" => false 			//true, false
  	, "xhr" => null 					//true, false, null
  	, "api" => false					//ture, false, private, public
  	, "type" => "mixed"					//mixed, html, json, xml, media
  	, "compress" => true				//null, true , false
  );*/

  
  
  $schema["page"]["/login"] = array(
  	"name" => "login"
  	, "cache" => "user" 				
  	, "cache_path" => null
  	, "primary" => true 				
  	, "restricted" => false 			
  	, "api" => false					
  	, "type" => "mixed"					
  );  
  $schema["page"]["/login/social"] = array(
  	"name" => "login social"
  	, "group" => "services"
  	, "cache" => false 					//user, group, true, false, Path start with /
  	, "cache_path" => null				//null, Path start with /
  	, "primary" => true 				//true, false
  	, "restricted" => false 			//true, false
  	, "api" => "login"					//ture, false, private, public, login
  	, "type" => "mixed"					//mixed, html, json, xml, media
  );  
  
  $schema["page"]["/admin/login"] = array(
  	"name" => "admin login"
  	, "group" => "login"
  	, "layer" => "empty"
  	, "cache" => "user" 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/builder/login"] = array(
  	"name" => "manage login"
  	, "group" => "login"
  	, "layer" => "empty"
  	, "cache" => "user" 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/restricted/login"] = array(
  	"name" => "restricted login"
  	, "group" => "login"
  	, "layer" => "empty"
  	, "cache" => "user" 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/ecommerce/login"] = array(
  	"name" => "manage login"
  	, "group" => "login"
  	, "layer" => "empty"
  	, "cache" => "user" 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  );

/**
*  Frontend Path
*/
  $schema["page"]["/user"] = array(
  	"name" => "user"
  	, "cache" => "user" 
  	, "cache_path" => null				
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "seo" => "/user"
    , "theme" => "site"
  ); 

  $schema["page"]["/search"] = array(
  	"name" => "search"
  	, "cache" => true 	
  	, "cache_client" => "noxhr"
  	, "cache_path" => "/search"
  	, "strip_path" => "/search"
  	, "primary" => true
  	, "restricted" => false 		
  	, "api" => false
  	, "type" => "html"
  ); 

  $schema["page"]["/error"] = array(
  	"name" => "error"
  	, "cache" => "guest" 	
  	, "cache_path" => "/error-document"
  	, "primary" => true
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  	, "status_code" => array(
  		"xhr" => 500
  		, "noxhr" => 404
  	)
  	, "seo" => true
  ); 

  $schema["page"]["/error-document"] = array(
  	"name" => "error-server"
  	, "cache" => false 	
  	, "primary" => true
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  	, "session" => false
  	, "exit" => true 
  );

  $schema["page"]["/dialog"] = array(
        "name" => "dialog"
        , "cache" => true
        , "cache_path" => null
        , "primary" => true
        , "restricted" => true
        , "api" => false
        , "type" => "html"
        , "seo" => false
        , "theme" => "site"
    );

$schema["page"]["/"] = array(
  	"name" => "public"
  	, "cache" => true 	
  	, "cache_client" => "noxhr"			
  	, "primary" => true
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
	, "rnd" => 0
	, "compress" => true
  ); 

/**
* System Services
*/  
  $schema["page"]["actexparse"] = array(
  	"name" => "actexparse"
  	, "group" => "actex"
  	, "cache" => false 		
  	, "cache_path" => null		
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );
  $schema["page"]["parsedata"] = array(
  	"name" => "parsedata"
  	, "group" => "actex"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );
  $schema["page"]["aparsedata"] = array(
  	"name" => "aparsedata"
  	, "group" => "actex"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );
  $schema["page"]["atparsedata"] = array(
  	"name" => "atparsedata"
  	, "group" => "actex"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );
  $schema["page"]["/srv"] = array(
  	"name" => "service"
  	, "group" => "services"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );  
  $schema["page"]["/api"] = array(
  	"name" => "api"
  	, "group" => "services"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "json"
  );  
  $schema["page"]["/cm/showfiles.php"] = array(
  	"name" => "resource"
  	, "cache" => false 		
  	, "cache_path" => null		
  	, "primary" => true
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "media"
  );  

/**
* Block Render XHR
*/
  $schema["page"]["/frame"] = array(
  	"name" => "frame"
  	, "cache" => "user" 
  	, "cache_path" => "/sid"
  	, "strip_path" => "/frame"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  ); 
  $schema["page"]["/block"] = array(
  	"name" => "block"
  	, "group" => "shard"
  	, "cache" => "user" 
  	, "cache_path" => "/shard"	
  	, "strip_path" => "/block"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  ); 
  
  $schema["page"]["/anagraph"] = array(
  	"name" => "anagraph"
  	, "group" => "shard"
  	, "cache" => "group" 
  	, "cache_path" => "/shard/anagraph"	
  	, "strip_path" => "/anagraph"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  ); 
  
  $schema["page"]["/gallery"] = array(
  	"name" => "gallery"
  	, "group" => "shard"
  	, "cache" => "group" 	
  	, "cache_path" => "/shard/gallery"
  	, "strip_path" => "/gallery"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  ); 
  
  $schema["page"]["/publish"] = array(
  	"name" => "publish"
  	, "group" => "shard"
  	, "cache" => "group" 
  	, "cache_path" => "/shard/publish"				
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  );   

  $schema["page"]["/marker"] = array(
  	"name" => "marker"
  	, "group" => "shard"
  	, "cache" => "group" 
  	, "cache_path" => "/shard/marker"
  	, "strip_path" => "/marker"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  );    
  
  $schema["page"]["/menu"] = array(
  	"name" => "menu"
  	, "group" => "shard"
  	, "cache" => "group" 
  	, "cache_path" => "/shard/menu"
  	, "strip_path" => "/menu"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  );  
  
  $schema["page"]["/album"] = array(
  	"name" => "album"
  	, "group" => "shard"
  	, "cache" => "group" 				
  	, "cache_path" => "/shard/album"
  	, "strip_path" => "/album"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  );    
  
  $schema["page"]["/tag"] = array(
  	"name" => "tag"
  	, "group" => "shard"
  	, "cache" => "group" 
  	, "cache_path" => "/shard/tag"
  	, "strip_path" => "/tag"
  	, "primary" => false
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  );    
  
/**
* Updater Area
*/
  $schema["page"]["/install"] = array(
  	"name" => "installer"
  	, "group" => "updater"
  	, "cache" => false 	
  	, "cache_path" => null
  	, "primary" => true
  	, "restricted" => false 			
  	, "api" => false
  	, "type" => "html"
  ); 
    
  $schema["page"]["/updater/check"] = array(
  	"name" => "updater"
  	, "group" => "updater"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => false
  	, "api" => false
  	, "type" => "html"
  );   
  
/**
* Restricted Area
*/
  $schema["page"]["/admin"] = array(
  	"name" => "admin"
  	, "group" => "console"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/builder"] = array(
  	"name" => "builder"
  	, "group" => "console"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/restricted"] = array(
  	"name" => "restricted"
  	, "group" => "console"
  	, "cache" => false 	
  	, "cache_path" => null			
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  ); 
  $schema["page"]["/ecommerce"] = array(
  	"name" => "ecommerce"
  	, "group" => "console"
  	, "cache" => false 		
  	, "cache_path" => null		
  	, "primary" => true
  	, "restricted" => true 			
  	, "api" => false
  	, "type" => "html"
  	, "theme" => "admin"
  );

$schema["error"] = array();
$schema["error"]["rules"] = array(
    "*/index*" => '$1'
, "wp-login*" => 401
, "wp-*" => 403
, "*.shtml" => 403
, "[^a-z\-0-9/\.\_]+" => 400
);
$schema["error"]["status"]["404"] = "/error";
$schema["error"]["status"]["500"] = "/error";