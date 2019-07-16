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
function process_addon_sender($user_path, $ID_node, $vgallery_name, $title, $data_source, $data_limit, $layout) {
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();
	
	$buffer = "";
	
	$arrDataLimit = array();
	
	$data = array();
	$data["template"] = "";
	$data["subject"] = "";
	$data["to"] = null;
	$data["from"] = null;
	$data["bcc"] = null;
	$data["cc"] = null;
	$data["fields"] = array();
	$data["body"] = null;
	$data["attach"] = array();
	$data["settings"]["display_form"] = false;
	
	
	
	$sSQL = "
	        SELECT vgallery_fields.*
		        , vgallery_type.name AS type
		        , vgallery_fields_data_type.name AS data_type
		        , extended_type.name AS extended_type
		        , extended_type.ff_name AS ff_extended_type
		        , vgallery_fields.limit_by_groups AS limit_by_groups
		        , (SELECT vgallery_rel_nodes_fields.description
	                FROM vgallery_rel_nodes_fields 
	                WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                    	AND vgallery_rel_nodes_fields.ID_fields = vgallery_fields.ID 
	                    AND vgallery_rel_nodes_fields.ID_lang = IF(vgallery_fields.disable_multilang > 0, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . ", " . $db->toSql(LANGUAGE_INSET_ID, "Number") . ")
            	) AS data_node_field
	         FROM vgallery_fields
	            LEFT JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
	            INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type
	            INNER JOIN vgallery_type ON vgallery_nodes.ID_type = vgallery_type.ID
	            INNER JOIN extended_type ON extended_type.ID = vgallery_fields.ID_extended_type
	         WHERE 1
	         	" . (strlen($data_limit)
	         		? " AND vgallery_fields.ID IN(" . $db->toSql($data_limit, "Text", false) . ") "
	         		: ""
	         	) . "
	            AND vgallery_nodes.ID = " . $db->toSql($ID_node, "Number") . "
	         ORDER BY vgallery_fields.parent, vgallery_fields.`order_backoffice`";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$field_extended_type = $db->getField("extended_type", "Text", true);
			$field_parent = $db->getField("parent", "Text", true);
			$field_name = $db->getField("name", "Text", true);
			$field_data = $db->getField("data_node_field", "Text", true);
			
			$file_data = array();
			$file_size = "";
			$total_file_size = 0;
			
	        if($field_extended_type == "Image"
	            || $field_extended_type == "Upload"
	            || $field_extended_type == "UploadImage"
	        ) {
	        	if(is_file(FF_DISK_UPDIR . $field_data)) {
	        		$file_size = filesize(FF_DISK_UPDIR . $field_data);
	        		if($file_size < 10000000) {
						$total_file_size = $total_file_size + $file_size;

	        			$file_data[$field_name]["path"] = $field_data;
	        			$file_data[$field_name]["size"] = $file_size;

						if(strlen($field_parent)) {
	        				$file_data[$field_name]["fields"][$field_parent][$field_name] = $field_data;
						} else {
							$file_data[$field_name]["fields"][$field_name] = $field_data;
						}
						
						continue;
					}
				} else {
					continue;
				}
			}

			if(strlen($field_parent)) {
				$data["fields"][$field_parent][$field_name] = $field_data;
			} else {
				$data["fields"][$field_name] = $field_data;
			}
		} while($db->nextRecord());
		
		if(is_array($file_data) && count($file_data)) {
			if($total_file_size > 10000000) {
				foreach($file_data AS $file_data_key => $file_data_value) {
					$data["fields"] = array_merge($data["fields"], $file_data_value["fields"]);
				}
			} else {
				if(count($file_data) > 1) {
					foreach($file_data AS $file_data_key => $file_data_value) {
						if(check_function("get_gallery_information_by_lang")) {
							$file_name = get_gallery_information_by_lang($file_data_value["path"]);
						}
						if(!strlen($file_name)) {
							$file_name = basename($file_data_value["path"]);
						} else {
							$file_name = $file_name . "." . ffGetFilename($file_data_value["path"], false);
						}
						
						$data["attach"][$file_name] = $file_data_value["path"]; 
					}
				} else {
					foreach($file_data AS $file_data_key => $file_data_value) {
						$data["attach"][$title . "." . ffGetFilename($file_data_value["path"], false)] = $file_data_value["path"];  
					}
				}
			}
		}
	}
	
	//$tpl_data["custom"] = "sender.html";
	$tpl_data["base"] = "sender.html";

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data, "/tpl/addon");

	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");

    //$tpl = ffTemplate::factory(get_template_cascading($user_path, "sender.html", "/vgallery"));
    //$tpl->load_file("sender.html", "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme_inset", THEME_INSET);
	
	if($vgallery_name == $data_source) {
		$template = $vgallery_name;
	} else {
		$template = $vgallery_name . " " . $data_source;
	}
	
	$data["template"] = "Sender " . $template;
	
    $serial_data = json_encode($data);

	$tpl->set_var("sender_reference", "/" . set_sid($serial_data, $user_path . " sender"));
	
	if(Auth::isGuest()) {
		$tpl->set_var("sender_name", "");	
		$tpl->set_var("sender_mail", "");	
		$tpl->set_var("class_hidden", "");
	} else {
		$tpl->set_var("sender_name", Auth::get("user")->username);
		$tpl->set_var("sender_mail", Auth::get("user")->email);
		$tpl->set_var("class_hidden", " hide");
	}
	
	$cm->oPage->tplAddJs("ff.cms.addon.sender", array(
		"path" => FF_THEME_DISK_PATH . "/" . THEME_INSET . "/javascript/addon"
		, "file" => "sender.js"
	));
	
	$buffer = $tpl->rpparse("main", false);
	
	return $buffer;
}
