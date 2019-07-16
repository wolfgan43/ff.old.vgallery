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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

$db = ffDB_Sql::factory();

$db->query("SELECT * FROM module_swf WHERE name = " . $db->toSql($MD_chk["params"][0]));
if($db->nextRecord()) {
	$oRecord = ffRecord::factory($cm->oPage);
    $oRecord->id = $MD_chk["id"];
    $oRecord->class = $MD_chk["id"];
    $oRecord->src_table = "";

    $oRecord->template_dir = get_template_cascading($user_path, "tpl_swf.html", "/modules/swf", __DIR__);
    $oRecord->template_file = "tpl_swf.html";
    if(check_function("MD_swf_on_load_template"))
    	$oRecord->addEvent("on_process_template", "MD_swf_on_load_template");
    	
    $oRecord->use_own_location = $MD_chk["own_location"];
    
    $oRecord->user_vars["swf_id"] = $db->getField("ID")->getValue();
    $oRecord->user_vars["swf_url"] = $db->getField("swf_url")->getValue();
    if($db->getField("enable_xml")->getValue()) {
        $oRecord->user_vars["xml_url"] = $db->getField("xml_url")->getValue();
        $oRecord->user_vars["xml_varname"] = $db->getField("xml_varname")->getValue();
	}
    $oRecord->user_vars["width"] = $db->getField("width")->getValue();
    $oRecord->user_vars["height"] = $db->getField("height")->getValue();
    
    $oRecord->user_vars["play"] = $db->getField("play")->getValue();
    $oRecord->user_vars["loop"] = $db->getField("loop")->getValue();
    $oRecord->user_vars["menu"] = $db->getField("menu")->getValue();
    $oRecord->user_vars["quality"] = $db->getField("quality")->getValue();
    $oRecord->user_vars["scale"] = $db->getField("scale")->getValue();
    $oRecord->user_vars["salign"] = $db->getField("salign")->getValue();
    $oRecord->user_vars["wmode"] = $db->getField("wmode")->getValue();
    $oRecord->user_vars["bgcolor"] = $db->getField("bgcolor")->getValue();
    $oRecord->user_vars["base"] = $db->getField("base")->getValue();
    $oRecord->user_vars["swliveconnect"] = $db->getField("swliveconnect")->getValue();
    $oRecord->user_vars["devicefont"] = $db->getField("devicefont")->getValue();
    $oRecord->user_vars["allowscriptaccess"] = $db->getField("allowscriptaccess")->getValue();
    $oRecord->user_vars["seamlesstabbing"] = $db->getField("seamlesstabbing")->getValue();
    $oRecord->user_vars["allowfullscreen"] = $db->getField("allowfullscreen")->getValue();
    $oRecord->user_vars["allownetworking"] = $db->getField("allownetworking")->getValue();
    $oRecord->user_vars["align"] = $db->getField("align")->getValue();
    $oRecord->user_vars["version"] = $db->getField("version")->getValue();
    
    $flashVars = $db->getField("flashvars")->getValue();
    if(strlen($flashVars)) {
    	$arrflashVars = explode("&", $flashVars);
    	foreach($arrflashVars AS $flashVars_params) {
    		if(strlen($flashVars_params)) {
    			$flashVars_values = explode("=", $flashVars_params);
    			
    			$oRecord->user_vars["flashvars"][$flashVars_values[0]] = $flashVars_values[1];
			}
		}
	}
        
    
    
    if($db->getField("show_sez_title")->getValue()) {
        $oRecord->title = $db->getField("name")->getValue();
    } else {
        $oRecord->title = "";
    }
    
    $cm->oPage->addContent($oRecord);
}
  
