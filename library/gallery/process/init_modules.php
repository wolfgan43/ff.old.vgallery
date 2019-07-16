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
function process_init_modules($oPage, $ajax = null, $layouts_limit = "", $custom_module = array()) 
{
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    $user_path = $globals->user_path;
    $selected_lang = $globals->selected_lang;
	//$oPage->layer = "empty";
    
    $db_modules = ffDB_Sql::factory();
	if(is_array($custom_module) && count($custom_module)) {
		foreach($custom_module AS $custom_module_key => $custom_module_value) {
			if(check_function("process_addon_" . strtolower($custom_module_key)))
				$rc = call_user_func_array("process_addon_" . strtolower($custom_module_key), $custom_module_value);
		}
	} else {
	    if($ajax !== null) {
	        if($ajax) {
	            $sSQL_ajax = " AND layout.use_ajax > 0 AND ajax_on_ready NOT IN ('preload')";
	            
	        } else {
	            $sSQL_ajax = " AND (NOT(layout.use_ajax > 0) OR ajax_on_ready IN('preload'))";
	        }
	    } else {
	        $sSQL_ajax = "";
	    }
	    
	    if(is_array($layouts_limit) && count($layouts_limit)) {
    		foreach($layouts_limit AS $layout_name) {
    			if(strlen($layout_name)) {
    				if(strlen($sSQL_layouts))
    					$sSQL_layouts .= ",";
    				
    				$sSQL_layouts .= $db_modules->toSql(preg_replace('/[a-zA-Z]/', '', $layout_name), "Number");
				}
			}
    		$sSQL_layouts = " AND layout.ID IN(" . $sSQL_layouts . ")";
		} elseif(strlen($layouts_limit)) {
				$sSQL_layouts = $db_modules->toSql(preg_replace('/[a-zA-Z]/', '', $layouts_limit), "Number"); 
				$sSQL_layouts = " AND layout.ID IN(" . $sSQL_layouts . ")";
		} else {
			$sSQL_layouts = "";
		}

	    $sSQL = "
	            SELECT *
	            FROM 
	            (
	                SELECT
	                    layout.ID AS ID
	                    , layout.name AS layout_name
	                    , layout.smart_url AS smart_url
	                    , layout.ID_type AS ID_type
	                    , layout.value AS module_name
	                    , layout.params AS module_params
	                    , layout_type.name AS type 
	                    , layout_location.name AS layout_location
	                    , layout.`order` AS `order`
	                    , layout.`use_ajax` AS `use_ajax`
	                    , IF(layout_path.path = " . $db_modules->toSql($settings_path, "Text") . "
	                        , IF(layout_path.visible = 0
	                            , 0
	                            , 1
	                        )
	                        , IF(LOCATE(layout_path.path, " . $db_modules->toSql($settings_path, "Text") . ") > 0
	                            , IF((layout_path.visible - layout_path.cascading) = 0
	                                , 1 
	                                , 0
	                            )
	                            , IF(ISNULL(layout_path.path)
	                                , 1
	                                , 0
	                            ) 
	                        )
	                    ) AS visible
	                    , IF(layout_path.path = " . $db_modules->toSql($settings_path, "Text") . "
	                        , 0
	                        , IF(LOCATE(layout_path.path, " . $db_modules->toSql($settings_path, "Text") . ") > 0
	                            , " . strlen($settings_path) . " - LENGTH(layout_path.path)
	                            , IF(ISNULL(layout_path.path)
	                                , 1
	                                , 255
	                            ) 
	                        )
	                    ) AS sort
	                FROM layout
	                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
	                    INNER JOIN layout_location ON layout_location.ID = layout.ID_location 
	                    LEFT JOIN layout_path ON layout_path.ID_layout = layout.ID 
	                WHERE
	                    (layout_type.name = 'MODULE'
	                    	OR
	                    	layout_type.name = 'COMMENT'
	                    )
	                    $sSQL_ajax
	                    $sSQL_layouts
	                ORDER BY layout.ID, sort 
	            ) AS tbl_src
	            GROUP BY ID
	            ORDER BY `order`";
	   $db_modules->query($sSQL);
	//OR   layout_type.name = 'ECOMMERCE'
	   if($db_modules->nextRecord()) {
	        do {
				if(!$db_modules->getField("visible")->getValue())
					continue;

				$layout_location_value = $db_modules->getField("layout_location")->getValue();
	        	switch($db_modules->getField("type", "Text", true)) {
					case "MODULE":	        		
			           //ffErrorHandler::raise("2222", E_USER_ERROR, null, get_defined_vars());
			            $res = get_module($layout_location_value
			            			, $db_modules->getField("module_name")->getValue()
			            			, $db_modules->getField("module_params")->getValue()
			            			, array(
			            				"own_location" => ($_REQUEST["out"] == "html"
			            					? false
			            					: true
			            				)
			            				, "ajax" => $db_modules->getField("use_ajax")->getValue()
			            			)
			            		);
			            
		       			break;
		       	   case "COMMENT":
		       	   		$layout_comment["ajax"] = $db_modules->getField("use_ajax", "Number", true);
		       	   		
						$layout_comment["prefix"] = "MD-" . $layout_location_value . "-" . "comment" . "-" . str_replace("/", "", $db_modules->getField("module_name", "Text", true) . "-" . $db_modules->getField("module_params", "Text", true));
						$layout_comment["ID"] = $db_modules->getField("ID", "Number", true);
						$layout_comment["smart_url"] = $db_modules->getField("smart_url", "Text", true);
						$layout_comment["title"] = $db_modules->getField("layout_name", "Text", true) . " [" . $db_modules->getField("type", "Text", true) . "]";
						$layout_comment["type"] = $db_modules->getField("type", "Text", true);
						$layout_comment["location"] = $layout_location_value;
						$layout_comment["visible"] = NULL;
						//if(check_function("get_layout_settings"))
							$layout_comment["settings"] = Cms::getPackage($layout_comment["smart_url"]);
							if(!$layout_comment["settings"]) {
                                $layout_comment["settings"] = Cms::getPackage($layout_comment["type"]);
                            }
                            //get_layout_settings($layout_comment["ID"], $layout_comment["type"]);

		       	   		if(check_function("process_addon_comment")) 
		       	   			process_addon_comment($layout_comment["ID"], $db_modules->getField("module_params")->getValue(), null, $settings_path, $settings_path, "layout", true, $layout_comment);
		       	   		break;
		       	   default:
				}
	        } while ($db_modules->nextRecord());
	   }
	}
}

function get_path_parts($content_root, $path_info) {
	$return_values = array();
	$tmp = $path_info; 
	do
	{  
        if (is_dir($content_root . $tmp)) {
            $tmp = $tmp . "/index";
        }
		if (is_file($content_root . $tmp . "." . FF_PHP_EXT))
		{   
			$return_values["path_info"] = ffCommon_dirname($tmp);
			$return_values["script_name"] = basename($tmp);
			return $return_values;
		}
		if ($tmp == "/index")
			return NULL;
		if ($tmp != "/index")
			$tmp = ffCommon_dirname($tmp);
		if ($tmp == "/")
		{
			$tmp = "/index";
			$path_info = "/index" . $path_info;
		}
        if(!$tmp) {
            return null;
        }
	} while (true);	
}

function get_module($location, $module_name, $module_params, $MD_chk = array()) 
{
    $cm = cm::getInstance();
    $oPage = $cm->oPage;
    $db_modules = ffDB_Sql::factory(); 
    
    $module_vars = array();
    
    $registry = ffGlobals::getInstance("gallery");

    $settings_path = $registry->settings_path;
    $user_path = $registry->user_path;
    $selected_lang = $registry->selected_lang;
    $db_gallery = ffDB_Sql::factory();
 

    if (!isset($registry->MD_chk))
        $registry->MD_chk = array();

    if(!array_key_exists("ajax", $MD_chk))
    	$MD_chk["ajax"] = true;
    
    if(!array_key_exists("own_location", $MD_chk))
    	$MD_chk["own_location"] = false;
    
    $mod_file = "/" . $module_name;

    $module_vars = get_path_parts(FF_DISK_PATH . "/conf" . GALLERY_PATH_MODULE . $mod_file, "");

    if(is_file(realpath(FF_DISK_PATH . "/conf" . GALLERY_PATH_MODULE . $mod_file . stripslash($module_vars["path_info"]) . "/" . $module_vars["script_name"] . "." . FF_PHP_EXT)) && strpos((FF_DISK_PATH . GALLERY_PATH_MODULE . $mod_file . $module_vars["path_info"] . $module_vars["script_name"] . "." . FF_PHP_EXT), FF_DISK_PATH . GALLERY_PATH_MODULE) !== false) {
        $MD_chk["tag"] = str_replace("/", "", $module_name . "-" . $module_params);
        $MD_chk["inc"] = FF_DISK_PATH . "/conf" . GALLERY_PATH_MODULE . $mod_file . stripslash($module_vars["path_info"]) . "/" . $module_vars["script_name"] . "." . FF_PHP_EXT;
    } else {
        $strError = ffTemplate::_get_word_by_code("dialog_description_invalidpath");
    }

    if(!$strError) {
        $MD_chk["id"] = "MD-" . $location . "-" . $MD_chk["tag"];
        $MD_chk["params"] = explode(";", $module_params);
		$MD_chk["ret_url"] = $registry->user_path;

        $registry->MD_chk = $MD_chk;

        include($MD_chk["inc"]);
        
        return "MD-" . $location . "-" . $MD_chk["tag"];
    } else {
        return $strError;
    }
} 
