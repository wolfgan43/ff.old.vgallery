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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
  function system_ffcomponent_set_title($title = null, $icon = true, $smart_url = null, $goto_url = false, $component = null) {
  	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

	if(!$title)
		$title = (basename($cm->real_path_info) == "add"
			? ffTemplate::_get_word_by_code("addnew")
			: (basename($cm->real_path_info) && basename($cm->real_path_info) != basename(ffCommon_dirname($globals->page["user_path"]))
				? ucwords(str_replace("-", " ", basename($cm->real_path_info)))
				: ucwords(str_replace("-", " ", basename($globals->page["user_path"])))
			)
		);

	if($icon === true && $globals->page["icon"])
		$icon = $globals->page["icon"];

  	if(is_array($icon)) {
  		if(!$icon["type"])
  			$icon["type"] = "content";
  		if(!$icon["size"])
  			$icon["size"] = "lg";
  			
  		$res["icon"] = Cms::getInstance("frameworkcss")->get($icon["name"], "icon-tag", array($icon["size"], $icon["type"]));
	}
	
	if($smart_url)
		$res["smart_url"] = '<span class="smart-url">' . $smart_url . '</span>';
	
	if($goto_url)
		$res["goto_url"] = '<a class="slug-gotourl ' . Cms::getInstance("frameworkcss")->get("external-link", "icon") . '" href="' . ($goto_url !== true ? $goto_url : "javascript:void(0);") . '" target="_blank"></a>';
	
	$page_title = $res["icon"] . $title . $res["smart_url"] . $res["goto_url"];

	if($cm->isXHR()) {
		if($component && isset($_REQUEST["XHR_CTX_TYPE"]))
			$component->setTitle($page_title, 'admin-title vg-' . $icon["type"]);
	} else {
		$cm->oPage->title = $page_title . " - " . cm_getAppName();
	}

	return $page_title;
  }
  
  
  function system_ffcomponent_switch_by_path($path, $limit = 1) {
  	$cm = cm::getInstance();
  	
  	if(is_array($limit))
  	{
  		if(is_array($limit["request"]) && count($limit["request"])) {
  			foreach($limit["request"] AS $request_name) {
  				if(isset($_REQUEST[$request_name]))
  					$count_req++;
  			}
			if($count_req == count($limit["request"])) {
				require($path . "/modify." . FF_PHP_EXT);

				return false;
			}
  		}
  	
  		$count_slash = substr_count($cm->real_path_info, "/");
  		if($count_slash >= count($limit)) {
  			if(is_file($path . substr($cm->real_path_info, strpos($cm->real_path_info, "/", count($limit))) . "." . FF_PHP_EXT)) {
				$file = substr($cm->real_path_info, strpos($cm->real_path_info, "/", count($limit)));

				$cm->real_path_info = substr($cm->real_path_info, 0, strpos($cm->real_path_info, $file));

  				require($path . $file. "." . FF_PHP_EXT);

  				return false;
  			}
  			$is_modify = $limit[count($limit) - 1];
  		} else {
  			$is_modify = $limit[$count_slash - 1];
		}
  	} 
  	else 
  	{
  		$is_modify = ($limit && substr_count($cm->real_path_info, "/") >= $limit
  			? "modify"
  			: false
  		);
  		
	}

  	if(basename($cm->real_path_info) == "add")
  	{
  		if(is_file($path . "/" . basename(ffCommon_dirname($cm->real_path_info)) . "." . FF_PHP_EXT)) 
		{
			$file = basename(ffCommon_dirname($cm->real_path_info));
			$cm->real_path_info = ffCommon_dirname(ffCommon_dirname($cm->real_path_info)) . "/add";
			require($path . "/" . $file . "." . FF_PHP_EXT);
		} 
		elseif($cm->real_path_info != "/add" && is_file($path . "/" . basename(ffCommon_dirname($cm->real_path_info)) . "/index." . FF_PHP_EXT)) 
		{

			$file = basename(ffCommon_dirname($cm->real_path_info));
			$cm->real_path_info = ffCommon_dirname(ffCommon_dirname($cm->real_path_info)) . "/add";
			require($path . "/" . $file . "/index." . FF_PHP_EXT);
		} 
		else
		{
            if(!$is_modify)
              $is_modify = "modify";

			require($path . "/" . $is_modify . "." . FF_PHP_EXT);
		}
  	} 
  	elseif($_REQUEST["keys"]["ID"])
  	{
  		if(!$is_modify)
  			$is_modify = "modify";

		if(is_file($path . "/" . basename($cm->real_path_info) . "." . FF_PHP_EXT)) 
		{
			$file = basename($cm->real_path_info);
			$cm->real_path_info = ffCommon_dirname($cm->real_path_info);

			require($path . "/" . $file . "." . FF_PHP_EXT);
		} 
		else 
		{
			require($path . "/" . $is_modify . "." . FF_PHP_EXT);
		}
	} 
	elseif($is_modify) 
	{
		if(is_file($path . "/" . basename($cm->real_path_info) . "." . FF_PHP_EXT)) {
			$file = basename($cm->real_path_info);
			$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
			require($path . "/" . $file . "." . FF_PHP_EXT);
		} elseif(basename($cm->real_path_info) && is_file($path . "/" . basename($cm->real_path_info) . "/index." . FF_PHP_EXT)) {
			$file = basename($cm->real_path_info);
			$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
			require($path . "/" . $file . "/index." . FF_PHP_EXT);
		} else {
			require($path . "/" . $is_modify ."." . FF_PHP_EXT);
		}
	} 
	else 
	{
		if(is_file($path . "/" . basename($cm->real_path_info) . "." . FF_PHP_EXT)) {
			$file = basename($cm->real_path_info);
			$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
			require($path . "/" . $file . "." . FF_PHP_EXT);
		/*} elseif(basename($cm->real_path_info) && is_file($path . "/" . basename($cm->real_path_info) . "/index." . FF_PHP_EXT)) {
			$file = basename($cm->real_path_info);
			$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
			require($path . "/" . $file . "/index." . FF_PHP_EXT);*/
		} else
			return true;
	}
  }

  function system_ffcomponent_resolve_by_path($target = null) {
  	$cm = cm::getInstance();
  	if($cm->real_path_info) {
		if(basename($cm->real_path_info) == "add") {
			if(!$_REQUEST["path"])
				$_REQUEST["path"] = ffCommon_dirname($cm->real_path_info);
		} else {
			if($target && !isset($_REQUEST[$target]) && strpos($cm->real_path_info, "-") !== false) {
				$_REQUEST[$target] = substr(basename($cm->real_path_info), strrpos(basename($cm->real_path_info), "-") + 1);
				$cm->real_path_info = substr($cm->real_path_info, 0, strrpos($cm->real_path_info, "-"));
			}
			$_REQUEST["keys"]["permalink"] = $cm->real_path_info;
		}
		$ret_url = ffCommon_dirname($cm->path_info);
		if(!$_REQUEST["ret_url"])
  			$_REQUEST["ret_url"] =  $ret_url;
	}  
  }
  
  function system_ffComponent_resolve_record($tbl, $fields = null, $target = null, $sWhere = null) {
  	$db = ffDB_Sql::factory();
  	$res = array();
  	
	if(is_array($tbl)) {
		$table 								= $tbl["table"];
		$key 								= $tbl["key"];
		$primary 							= $tbl["primary"];	
		$if_request							= $tbl["if_request"];	
		$type								= $tbl["type"];
	} else {
		$table 								= $tbl;
		$key 								= "ID";
		$primary 							= null;
		$if_request							= null;
		$type								= false;
	}

  	system_ffcomponent_resolve_by_path($target);
	if(is_array($_REQUEST["keys"])) {
		switch($type) {
			case "custom":
  				$sSQL = $sWhere; 
				break;
			default:
  				$sSQL = "SELECT ID , name"
  							. system_ffComponent_get_sql_select_by_fields($fields) . "
  						FROM " . $table . "
  						WHERE " . ($_REQUEST["keys"]["ID"] /*&& !$_REQUEST["keys"]["permalink"]*/ //se abilitato in pages modify non si prende bene il record
					        ? "ID = " . $db->tosql($_REQUEST["keys"]["ID"], "Number")
					        : "name = " . $db->toSql(basename($_REQUEST["keys"]["permalink"]))
				        ) . $sWhere;

		}
		$db->query($sSQL);
		if($db->nextRecord()) {
			$res["ID"] = $db->getField("ID", "Number", true);
			$res["name"] = ucwords(str_replace("-", " " , $db->getField("name", "Text", true)));
			$res = $db->record;
			
			if($key)
				$_REQUEST["keys"][$key] = $res["ID"];
		} else {
			if(!$_REQUEST["path"])
				$_REQUEST["path"] = ffCommon_dirname($_REQUEST["keys"]["permalink"]);

			unset($_REQUEST["keys"]["ID"]);
			unset($_REQUEST["keys"]["permalink"]);
		}
	} 

	if(is_array($if_request) && count($if_request))	
	{
		foreach($if_request AS $request_name => $request) 
		{
			if(isset($_REQUEST[$request_name])) {
				$res["ID_" . $request_name] = true;
				$sSQL = "SELECT ID AS ID_" . $request_name
  							. system_ffComponent_get_sql_select_by_fields($request["fields"]) . "
  						FROM " . $request["table"] . "
  						WHERE " . $request["key"] . " = " . $db->toSql($_REQUEST[$request_name]);
				$db->query($sSQL);
				if($db->nextRecord()) {
					$res = array_replace($res, $db->record);
				}
			}
		}
	}
	if(!count($res))
		$res["noentry"] = true;
		
	if($_REQUEST["path"] && $primary) {
		$sSQL = "SELECT ID AS ID_primary, name AS name_primary"
  					. system_ffComponent_get_sql_select_by_fields($primary["fields"]) . "
  				FROM " . $primary["table"] . "
  				WHERE name = " . $db->toSql(basename($_REQUEST["path"]))
		        . $sWhere;
		$db->query($sSQL);
		if($db->nextRecord()) {
			$res = array_replace($res, $db->record);
		}
	}
	if(!$res["name"])	
		$res["name"] = ffTemplate::_get_word_by_code("addnew_" . $table);

	return $res;
  }
  
  function system_ffComponent_get_sql_select_by_fields($fields) {
  	if(is_array($fields) && count($fields)) {
  		foreach($fields AS $field_name => $field_sql) {
  			$sSQL_Select .= ", " . ($field_sql
  				? $field_sql . " AS " . $field_name
  				: $field_name
  			);
  		
  		}
  	}

  	return $sSQL_Select;
  }