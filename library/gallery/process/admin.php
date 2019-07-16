<?php
function process_admin($type, $block_key = null) {
	$globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	if($block_key) {
		check_function("query_layout");
		check_function("system_get_sections");
		
		$icon_size = "lg";		
		//print_r($globals);
		
		$sSQL = query_layout_by_smart_url($block_key);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$ID_block = $db->getField("ID", "Number", true);
			$ID_location = $db->getField("ID_location", "Number", true);
			$block_title = $db->getField("name", "Text", true);
			$block_type = system_get_block_type();
			$block_value = $db->getField("value", "Text", true);

//print_r($db->record);
			$block = $block_type[ffCommon_url_rewrite($db->getField("type", "Text", true))];
//print_r($block);
/*
SezAdminActions
SezAdminAction

SezBlock

SezRestrictedActions
SezRestrictedAction*/

		    $tpl_data["custom"] = "admin-" . $type . ".html";
		    $tpl_data["base"] = $type . ".html";
		    $tpl_data["path"] = "/tpl/admin";
		    
		    $tpl_data["result"] = get_template_cascading($globals->user_path, $tpl_data);
		    
		    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
			//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
            $tpl->load_file($tpl_data["result"]["name"], "main");
			
			$tpl->set_var("toggle_class", Cms::getInstance("frameworkcss")->get("ellipsis-v", "icon", $icon_size)); 
			/**
			* Block Actions
			*/


			/**
			* Block
			*/
			$tpl->set_var("block_path", $globals->user_path);

			$tpl->set_var("block_type_class", $block["group"]);
			$tpl->set_var("block_modify_path", get_path_by_rule("blocks") . "?keys[ID]=" . $ID_block);
			if($block["class"]) { 
           		// $tpl->set_var("block_class", Cms::getInstance("frameworkcss")->get("vg-" . $admin_menu["class"], "icon", array($admin_menu["group"], "2x")));
            	$tpl->set_var("block_icon", Cms::getInstance("frameworkcss")->get("vg-" . $block["class"], "icon-tag", array($block["group"], "2x")));
			}			

			$tpl->set_var("block_id", $ID_block);
			$tpl->set_var("block_location", $ID_location);
			$tpl->set_var("block_name", $block_title);
			$tpl->parse("SezBlock", false);
			
			/**
			* Item Actions
			*/
			
			$restricted_path = $block["url"] . "/" . $block["file_edit"];
			$restricted_params = $block["key_name"] . "=" . $block_value;
			
			$tpl->set_var("restricted_path", $restricted_path . ($restricted_params ? "?" . $restricted_params : ""));
			$tpl->set_var("restricted_icon", Cms::getInstance("frameworkcss")->get("editrow", "icon-tag", $icon_size));
			$tpl->set_var("restricted_class", "");
			$tpl->set_var("restricted_name", "");
			$tpl->parse("SezRestrictedAction", true);
			$tpl->parse("SezRestrictedActions", false);
			
			$res = $tpl->rpparse("main", false);
		}
		
	} else {

	}
	
	return $res;
}