<?php
function get_table_support($tbl = null) {
	$db = ffDB_Sql::factory();
	static $support_table = null;
	
	if(!$support_table) {
	    $sSQL = "SELECT check_control.* FROM check_control WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["check_control"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["check_control"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());
	    }

	    $sSQL = "SELECT extended_type.* FROM extended_type WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["extended_type"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["extended_type"]["group"][$db->getField("group", "Text", true)][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	            $support_table["extended_type"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());
	    }        

	    $sSQL = "SELECT vgallery_fields_data_type.* FROM vgallery_fields_data_type WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["data_type"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["data_type"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());
	    }
 		
 		$sSQL = "SELECT vgallery_type_group.* FROM vgallery_type_group WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["data_group"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["data_group"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());
	    }
	    	    
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "showfiles_modes.* FROM " . CM_TABLE_PREFIX . "showfiles_modes WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["showfiles"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["showfiles"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());
	    }	    

 		$sSQL = "SELECT anagraph_type.* FROM anagraph_type WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
			$ID_type = $db->getField("ID", "Number", true);
        	$type_is_dir = $db->getField("is_dir_default", "Number", true);
        	$type_name = $db->getField("name", "Text", true);
            if($type_is_dir)
	            $support_table["anagraph_type"]["dir"][$type_name] = $db->record;
            else            
	            $support_table["anagraph_type"]["node"][$type_name] = $db->record;

	            $support_table["anagraph_type"]["smart_url"][$type_name] = $db->record;
	            $support_table["anagraph_type"]["rev"][$ID_type] = $type_name;
	        } while($db->nextRecord());
	    }
 		
 		$sSQL = "SELECT vgallery_type.* FROM vgallery_type WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
			$ID_type = $db->getField("ID", "Number", true);
        	$type_is_dir = $db->getField("is_dir_default", "Number", true);
        	$type_name = $db->getField("name", "Text", true);
            if($type_is_dir)
	            $support_table["vgallery_type"]["dir"][$type_name] = $db->record;
            else            
	            $support_table["vgallery_type"]["node"][$type_name] = $db->record;

	            $support_table["vgallery_type"]["smart_url"][$type_name] = $db->record;
	            $support_table["vgallery_type"]["rev"][$ID_type] = $type_name;
	        } while($db->nextRecord());
	    }

 		$sSQL = "SELECT vgallery.* FROM vgallery WHERE 1";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	        do {
	            $support_table["vgallery"]["smart_url"][$db->getField("name", "Text", true)] = $db->record;
	            $support_table["vgallery"]["rev"][$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);

	            $arrLimitType = array();
	            if($db->record["limit_type"]) {
    				$arrLimitType = explode(",", $db->record["limit_type"]);	
					foreach($arrLimitType AS $ID_type) {
						$type_name = $support_table["vgallery_type"]["rev"][$ID_type];
						$type = $support_table["vgallery_type"]["smart_url"][$type_name];
						
						$support_table["vgallery"]["smart_url"][$db->getField("name", "Text", true)]["type"][($type["is_dir_default"] ? "dir" : "node")][] = $type_name;
					}
				}
	        } while($db->nextRecord());
	    }
	}
	
	return ($tbl) ? $support_table[$tbl] : $support_table;
}
