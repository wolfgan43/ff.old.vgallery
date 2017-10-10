<?php
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
        							AND layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db->toSql("MODULE") . ") 
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
        //UPDATE CACHE
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("MODULE") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    }
}
?>
