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
function MD_general_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if(strlen($action)) {
//ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
        if($component->page_path == "/admin/content/modules/" . str_replace("module_", "", $component->src_table) . "/config"
        	|| $component->page_path == "/admin/content/modules/" . str_replace("module_", "", $component->src_table) . "/config/modify"
        ) {
        
        	$module_name = str_replace("module_", "", $component->src_table);
        	$module_params_old = $component->form_fields["name"]->value_ori->getValue();
        	$module_params_new = ffCommon_url_rewrite($component->form_fields["name"]->value->getValue());
            $key = current($component->key_fields);
            $sSQL = "UPDATE " . $component->src_table . " SET name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . " WHERE ID = " . $db->toSql($key->value); 
            $db->execute($sSQL);

	        switch($action) {
        		case "insert":
        			$sSQL = "INSERT INTO modules 
        					(
        						ID
        						, module_name
        						, module_params
        					)
        					VALUES
        					(
        						null
        						, " . $db->toSql($module_name) . "
        						, " . $db->toSql($module_params_new) . "
        					)";
        			$db->execute($sSQL);
        			break;
        		case "update":
        			if(check_function("system_get_sections"))
        				$block_module = system_get_block_type("module");
        		
        			$sSQL = "SELECT * 
        					FROM modules 
        					WHERE module_name = " . $db->toSql($module_name) . "
        						AND module_params = " . $db->toSql($module_params_new);
        			$db->query($sSQL);
					if(!$db->nextRecord()) {        		
        				$sSQL = "INSERT INTO modules 
        						(
        							ID
        							, module_name
        							, module_params
        						)
        						VALUES
        						(
        							null
        							, " . $db->toSql($module_name) . "
        							, " . $db->toSql($module_params_new) . "
        						)";
        				$db->execute($sSQL);
					}

        			if($module_params_old != $module_params_new) {
        				$sSQL = "UPDATE modules 
        						SET
        							module_name = " . $db->toSql($module_name) . " 
        							, module_params = " . $db->toSql($module_params_new) . " 
        						WHERE
        						(
        							module_name = " . $db->toSql($module_name) . " 
        							AND module_params = " . $db->toSql($module_params_old) . " 
        						)";
        				$db->execute($sSQL);
        				$sSQL = "UPDATE layout 
        						SET
        							layout.value = " . $db->toSql($module_name) . " 
        							, layout.params = " . $db->toSql($module_params_new) . "
        						WHERE
        						(
        							layout.value = " . $db->toSql($module_name) . " 
        							AND layout.params = " . $db->toSql($module_params_old) . " 
        							AND layout.ID_type = " . $db->toSql($block_module["ID"], "Number") . "
        						)";
        				$db->execute($sSQL);
					}
        			break;
        		case "confirmdelete":
        			$sSQL = "DELETE FROM modules WHERE module_name = " . $db->toSql($module_name) . " 
        							AND module_params = " . $db->toSql($module_params_old);
        			$db->execute($sSQL);
        			break;
        			
        		default:
        		
			}

	        if(check_function("refresh_cache")) {
        		refresh_cache("M", md5($module_name . "-" . $module_params_new), $action);
			}
		}        
    }
}

function MD_general_get_schema($addon = null) {
	static $service_schema = null;
	
	if(!$service_schema) {
		$arrFile = glob(FF_DISK_PATH . "/" . GALLERY_UI_PATH . "/addons/*/schema." . FF_PHP_EXT);
		if(is_array($arrFile) && count($arrFile)) {
			foreach($arrFile AS $real_file) {
				require($real_file);
			}
		}
	}
			
	if($addon)
		return $service_schema[$addon];
	else
		return $service_schema;
}