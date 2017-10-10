<?php
//require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oRecord = ffRecord::factory($oPage);
 
$db_gallery->query("SELECT * FROM module_swf WHERE name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
if($db_gallery->nextRecord()) {
    $oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
    $oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
    $oRecord->src_table = "";

    $oRecord->template_dir = get_template_cascading($user_path, "tpl_swf.html", "/modules/swf", ffCommon_dirname(__FILE__));
    $oRecord->template_file = "tpl_swf.html";
    if(check_function("MD_swf_on_load_template"))
    	$oRecord->addEvent("on_process_template", "MD_swf_on_load_template");
    	
    $oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"];
    
    $oRecord->user_vars["swf_id"] = $db_gallery->getField("ID")->getValue();
    $oRecord->user_vars["swf_url"] = $db_gallery->getField("swf_url")->getValue();
    if($db_gallery->getField("enable_xml")->getValue()) {
        $oRecord->user_vars["xml_url"] = $db_gallery->getField("xml_url")->getValue();
        $oRecord->user_vars["xml_varname"] = $db_gallery->getField("xml_varname")->getValue();
	}
    $oRecord->user_vars["width"] = $db_gallery->getField("width")->getValue();
    $oRecord->user_vars["height"] = $db_gallery->getField("height")->getValue();
    
    $oRecord->user_vars["play"] = $db_gallery->getField("play")->getValue();
    $oRecord->user_vars["loop"] = $db_gallery->getField("loop")->getValue();
    $oRecord->user_vars["menu"] = $db_gallery->getField("menu")->getValue();
    $oRecord->user_vars["quality"] = $db_gallery->getField("quality")->getValue();
    $oRecord->user_vars["scale"] = $db_gallery->getField("scale")->getValue();
    $oRecord->user_vars["salign"] = $db_gallery->getField("salign")->getValue();
    $oRecord->user_vars["wmode"] = $db_gallery->getField("wmode")->getValue();
    $oRecord->user_vars["bgcolor"] = $db_gallery->getField("bgcolor")->getValue();
    $oRecord->user_vars["base"] = $db_gallery->getField("base")->getValue();
    $oRecord->user_vars["swliveconnect"] = $db_gallery->getField("swliveconnect")->getValue();
    $oRecord->user_vars["devicefont"] = $db_gallery->getField("devicefont")->getValue();
    $oRecord->user_vars["allowscriptaccess"] = $db_gallery->getField("allowscriptaccess")->getValue();
    $oRecord->user_vars["seamlesstabbing"] = $db_gallery->getField("seamlesstabbing")->getValue();
    $oRecord->user_vars["allowfullscreen"] = $db_gallery->getField("allowfullscreen")->getValue();
    $oRecord->user_vars["allownetworking"] = $db_gallery->getField("allownetworking")->getValue();
    $oRecord->user_vars["align"] = $db_gallery->getField("align")->getValue();
    $oRecord->user_vars["version"] = $db_gallery->getField("version")->getValue();
    
    $flashVars = $db_gallery->getField("flashvars")->getValue();
    if(strlen($flashVars)) {
    	$arrflashVars = explode("&", $flashVars);
    	foreach($arrflashVars AS $flashVars_params) {
    		if(strlen($flashVars_params)) {
    			$flashVars_values = explode("=", $flashVars_params);
    			
    			$oRecord->user_vars["flashvars"][$flashVars_values[0]] = $flashVars_values[1];
			}
		}
	}
        
    
    
    if($db_gallery->getField("show_sez_title")->getValue()) {
        $oRecord->title = $db_gallery->getField("name")->getValue();
    } else {
        $oRecord->title = "";
    }
    
    $oPage->addContent($oRecord);
}
  
?>
