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
$module_name = basename($cm->real_path_info);

$db_xml = ffDB_Sql::factory();
$db_module = ffDB_Sql::factory();

$db_xml->query("SELECT * FROM module_swf WHERE name = " . $db_xml->toSql($module_name) . " AND enable_xml = " . $db_xml->toSql("1", "Number"));
if($db_xml->nextRecord()) {
	$tpl = ffTemplate::factory(get_template_cascading($user_path, "tpl_xml.xml", "/modules/swf", __DIR__));
    $tpl->load_file("tpl_xml.xml", "main");
    
	$md_swf_ID = $db_xml->getField("ID")->getValue();
    $md_swf_url = $db_xml->getField("swf_url")->getValue();
    $md_swf_xml_url = $db_xml->getField("xml_url")->getValue();
    $md_swf_tbl_src = $db_xml->getField("tbl_src")->getValue();
    $md_swf_items = $db_xml->getField("items")->getValue();
    $md_swf_show_title = $db_xml->getField("show_title")->getValue();
    $md_swf_show_link = $db_xml->getField("show_link")->getValue();
    $md_swf_show_image = $db_xml->getField("show_image")->getValue();
    $md_swf_show_description = $db_xml->getField("show_description")->getValue();
    $md_swf_show_date = $db_xml->getField("show_date", "Date")->getValue("Date", LANGUAGE_INSET);
    $md_swf_ID_publishing = $db_xml->getField("ID_publishing")->getValue();
    $md_swf_limit = $db_xml->getField("limit", "Number")->getValue();

    $md_swf_tpl_title_tag = $db_xml->getField("tpl_title_tag", "Text", true);
    $md_swf_tpl_main_tag = $db_xml->getField("tpl_main_tag", "Text", true);
    $md_swf_tpl_parent_tag = $db_xml->getField("tpl_parent_tag", "Text", true);
    $md_swf_tpl_row_tag = $db_xml->getField("tpl_row_tag", "Text", true);
    $md_swf_tpl_row_image_tag = $db_xml->getField("tpl_row_image_tag", "Text", true);
    $md_swf_tpl_row_field_tag = $db_xml->getField("tpl_row_field_tag", "Text", true);
    $md_swf_tpl_sub_parent_tag = $db_xml->getField("tpl_sub_parent_tag", "Text", true);
    $md_swf_tpl_sub_row_tag = $db_xml->getField("tpl_sub_row_tag", "Text", true);
    $md_swf_tpl_sub_row_image_tag = $db_xml->getField("tpl_sub_row_image_tag", "Text", true);
    $md_swf_tpl_sub_row_field_tag = $db_xml->getField("tpl_sub_row_field_tag", "Text", true);
    
    $count_item = 0;

	$tpl_data["type"] = "xml";
	$tpl_data["field_source"] = "module_swf_vgallery";
    
	switch($md_swf_tbl_src) {
		case "files":
			$sSQL = "SELECT CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
					FROM files 
					WHERE files.ID = " . $db_module->toSql($md_swf_items, "Number");
			$db_module->query($sSQL);
			if($db_module->nextRecord()) {
				$layout_type = "GALLERY";

				$gallery_path = $db_module->getField("full_path", "Text", true);
				if(!strlen($gallery_path))
					$gallery_path = "/";

				$sSQL = "SELECT 
							layout_path.path AS real_path 
							, layout.value AS layout_value
						FROM layout 
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
						WHERE
							layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db_module->toSql($layout_type) . ")
							AND '" . $db_module->toSql($gallery_path, "Text", false) . "' LIKE CONCAT(layout.value , '%')
						";
				$db_module->query($sSQL);
				if($db_module->nextRecord()) {
					$source_user_path = $db_module->getField("real_path", "Text", true);
					$layout_value = $db_module->getField("layout_value", "Text", true);
					
                    $available_path = $layout_value;
                    $real_path = realpath(FF_DISK_UPDIR . stripslash($layout_value));

                    if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
                    	$file_permission = get_file_permission($layout_value, "files", true);

                    //File permessi Cartella (controllo se l'utente ha diritti di lettura)
                    if (check_mod($file_permission, 1, true, Auth::env("AREA_GALLERY_SHOW_MODIFY"))) {
                        if(is_dir($real_path)) {
                            $rst_file = array();
                            $rst_dir = array();
                            $arr_real_path = glob($real_path . "/*");
                            if(is_array($arr_real_path) && count($arr_real_path)) {
                                foreach ($arr_real_path AS $real_file) { 
                                    $file = str_replace(FF_DISK_UPDIR, "", $real_file);
                                    $description = "";
									if ((is_dir($real_file) /*&& basename($real_file) != ffMedia::STORING_BASE_NAME && basename($real_file) != GALLERY_TPL_PATH*/) || (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false) && strpos(basename($real_file), ".") !== 0) {
                                        if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
                                        	$file_permission = get_file_permission($file);
                                        if (check_mod($file_permission, 1, true, Auth::env("AREA_GALLERY_SHOW_MODIFY"))) {
                                            $rst_dir[$file]["permission"] = $file_permission;
                                        }
                                    }
                                }
                            }
                            $rst_item = array_merge($rst_dir, $rst_file);
                            
							$unic_id = "X" . "0";
							$layout["prefix"] = "x";
							$layout["ID"] = 0;
							$layout["title"] = ffTemplate::_get_word_by_code("xml_title");
							$layout["type"] = $layout_type;
							$layout["location"] = "Content";
							$layout["visible"] = NULL;
							//if(check_function("get_layout_settings"))
								$layout["settings"] = Cms::getPackage($layout_type); //get_layout_settings(NULL, $layout_type);
							
							$tpl_data["tag"]["title"] = $md_swf_tpl_title_tag;
							$tpl_data["tag"]["gallery"] = $md_swf_tpl_main_tag;
							$tpl_data["tag"]["gallerys"] = $md_swf_tpl_parent_tag;
							$tpl_data["tag"]["gallery_row"] = $md_swf_tpl_row_tag;
							$tpl_data["tag"]["gallery_row_image"] = $md_swf_tpl_row_image_tag;
							$tpl_data["tag"]["gallery_row_field"] = $md_swf_tpl_row_field_tag;
							$tpl_data["tag_rel"]["gallerys"] = $md_swf_tpl_sub_parent_tag;
							$tpl_data["tag_rel"]["gallery_row"] = $md_swf_tpl_sub_row_tag;
							$tpl_data["tag_rel"]["gallery_row_image"] = $md_swf_tpl_sub_row_image_tag;
							$tpl_data["tag_rel"]["gallery_row_field"] = $md_swf_tpl_sub_row_field_tag;                            

							if(check_function("process_gallery_thumb"))
                            	$res = process_gallery_thumb($rst_item, $layout_value, NULL, $source_user_path, NULL, $layout, $tpl_data);
                        }
                    } else {
                        $res["content"] = ffTemplate::_get_word_by_code("error_access_denied");
                    }
					
				}
			}		
		
			break;
		case "vgallery_nodes":
			$sSQL = "SELECT CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
						, vgallery.name AS vgallery_name
					FROM vgallery_nodes 
						INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
					WHERE vgallery_nodes.ID = " . $db_module->toSql($md_swf_items, "Number");
			$db_module->query($sSQL);
			if($db_module->nextRecord()) {
				$layout_type = "VIRTUAL_GALLERY";
				
				$vgallery_name = $db_module->getField("vgallery_name", "Text", true);
				$vgallery_path = substr($db_module->getField("full_path", "Text", true), strlen("/" . $vgallery_name));
				if(!strlen($vgallery_path))
					$vgallery_path = "/";

				$sSQL = "SELECT 
							layout_path.path AS real_path 
						FROM layout 
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
						WHERE
							layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db_module->toSql($layout_type) . ")
							AND layout.value = " . $db_module->toSql($vgallery_name) . "
							AND '" . $db_module->toSql($vgallery_path, "Text", false) . "' LIKE CONCAT(layout.params , '%')
						";
				$db_module->query($sSQL);
				if($db_module->nextRecord()) {
					$real_path = $db_module->getField("real_path", "Text", true);

					$tpl_data["field"]["source"] = "module_swf_vgallery";
					$tpl_data["field"]["rel"] = "ID_module_swf";
					$tpl_data["field"]["rel_value"] = $md_swf_ID;
					$tpl_data["tag"]["title"] = $md_swf_tpl_title_tag;
					$tpl_data["tag"]["vgallery"] = $md_swf_tpl_main_tag;
					$tpl_data["tag"]["vgallerys"] = $md_swf_tpl_parent_tag;
					$tpl_data["tag"]["vgallery_row"] = $md_swf_tpl_row_tag;
					$tpl_data["tag"]["vgallery_row_image"] = $md_swf_tpl_row_image_tag;
					$tpl_data["tag"]["vgallery_row_field"] = $md_swf_tpl_row_field_tag;
					$tpl_data["tag_rel"]["vgallerys"] = $md_swf_tpl_sub_parent_tag;
					$tpl_data["tag_rel"]["vgallery_row"] = $md_swf_tpl_sub_row_tag;
					$tpl_data["tag_rel"]["vgallery_row_image"] = $md_swf_tpl_sub_row_image_tag;
					$tpl_data["tag_rel"]["vgallery_row_field"] = $md_swf_tpl_sub_row_field_tag;
					
					
					$unic_id = "X" . "0";
					$layout["prefix"] = "x";
					$layout["ID"] = 0;
					$layout["title"] = ffTemplate::_get_word_by_code("xml_title");
					$layout["type"] = $layout_type;
					$layout["location"] = "Content";
					$layout["visible"] = NULL;
					//if(check_function("get_layout_settings"))
						$layout["settings"] = Cms::getPackage($layout_type); //get_layout_settings(NULL, $layout_type);
				    
					if(check_function("process_vgallery_thumb"))
						$res = process_vgallery_thumb(
								stripslash("/" . $vgallery_name . $vgallery_path)
								, "vgallery"
								, array(
									"source_user_path" => $real_path
									, "tpl_data" => $tpl_data
                                    , "vgallery_name" => $vgallery_name
								)
								, $layout
							);
				}
			}
			break;
		case "publishing":
			$sSQL = "SELECT publishing.* FROM publishing WHERE publishing.ID = " . $db_module->toSql($md_swf_items, "Number");
			$db_module->query($sSQL);
			if($db_module->nextRecord()) {
				$layout_type = "PUBLISHING";
				
                $publishing = array();
                $publishing["ID"] = $db_module->getField("ID", "Number", true);
                
                $publish_type = $db_module->getField("area", "Text", true);
                
				$sSQL = "SELECT 
							layout_path.path AS real_path
							, layout.params AS params
						FROM layout 
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
						WHERE
							layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db_module->toSql($layout_type) . ")
							AND layout.value = " . $db_module->toSql($publish_type . "_" . $publishing["ID"]) . "
						";
				$db_module->query($sSQL);
				if($db_module->nextRecord()) {
					$params = $db_module->getField("params", "Text", true);
					$real_path = $db_module->getField("real_path", "Text", true);
	                $source_user_path = $params
	                                        ? $params
	                                        : (strlen($real_path) &&  $real_path != "/"
	                                            ? $real_path
	                                            : NULL
	                                        ); 

					$unic_id = "X" . "0";
					$layout["prefix"] = "x";
					$layout["ID"] = 0;
					$layout["title"] = ffTemplate::_get_word_by_code("xml_title");
					$layout["type"] = $layout_type;
					$layout["location"] = "Content";
					$layout["visible"] = NULL;
					//if(check_function("get_layout_settings"))
						$layout["settings"] = Cms::getPackage($layout_type); //get_layout_settings(NULL, $layout_type);
					
	                if($publish_type == "gallery") {
						$tpl_data["tag"]["title"] = $md_swf_tpl_title_tag;
						$tpl_data["tag"]["gallery"] = $md_swf_tpl_main_tag;
						$tpl_data["tag"]["gallerys"] = $md_swf_tpl_parent_tag;
						$tpl_data["tag"]["gallery_row"] = $md_swf_tpl_row_tag;
						$tpl_data["tag"]["gallery_row_image"] = $md_swf_tpl_row_image_tag;
						$tpl_data["tag"]["gallery_row_field"] = $md_swf_tpl_row_field_tag;
						$tpl_data["tag_rel"]["gallerys"] = $md_swf_tpl_sub_parent_tag;
						$tpl_data["tag_rel"]["gallery_row"] = $md_swf_tpl_sub_row_tag;
						$tpl_data["tag_rel"]["gallery_row_image"] = $md_swf_tpl_sub_row_image_tag;
						$tpl_data["tag_rel"]["gallery_row_field"] = $md_swf_tpl_sub_row_field_tag;

						if(check_function("process_gallery_thumb"))
	                    	$res = process_gallery_thumb(NULL, NULL, NULL, $source_user_path, $publishing, $layout, $tpl_data);
	                } elseif($publish_type == "vgallery") {
						$tpl_data["field"]["source"] = "module_swf_vgallery";
						$tpl_data["field"]["rel"] = "ID_module_swf";
						$tpl_data["field"]["rel_value"] = $md_swf_ID;
						$tpl_data["tag"]["title"] = $md_swf_tpl_title_tag;
						$tpl_data["tag"]["vgallery"] = $md_swf_tpl_main_tag;
						$tpl_data["tag"]["vgallerys"] = $md_swf_tpl_parent_tag;
						$tpl_data["tag"]["vgallery_row"] = $md_swf_tpl_row_tag;
						$tpl_data["tag"]["vgallery_row_image"] = $md_swf_tpl_row_image_tag;
						$tpl_data["tag"]["vgallery_row_field"] = $md_swf_tpl_row_field_tag;
						$tpl_data["tag_rel"]["vgallerys"] = $md_swf_tpl_sub_parent_tag;
						$tpl_data["tag_rel"]["vgallery_row"] = $md_swf_tpl_sub_row_tag;
						$tpl_data["tag_rel"]["vgallery_row_image"] = $md_swf_tpl_sub_row_image_tag;
						$tpl_data["tag_rel"]["vgallery_row_field"] = $md_swf_tpl_sub_row_field_tag;

	                    if(check_function("process_vgallery_thumb"))
	                    	$res = process_vgallery_thumb(
	                    			NULL
	                    			, "publishing"
	                    			, array(
	                    				"source_user_path" => $source_user_path
	                    				, "allow_insert" => false
	                    				, "publishing" => $publishing
	                    				, "tpl_data" => $tpl_data
	                    			)
	                    			, $layout
	                    		);
	                }                                               
				}                
			}
			break;
		default:
		
	}
	header ("content-type: text/xml");

    if(strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== false) {
        $encoding = 'gzip';
    } else{
        $encoding = false;
    }
    
	if($encoding && 0) {
		$res["content"] = gzencode($res["content"]);
	} else {
		$res["content"] = preg_replace("/\n\s*/", "\n", $res["content"]);	
	}
	
	echo $res["content"];
}
exit;