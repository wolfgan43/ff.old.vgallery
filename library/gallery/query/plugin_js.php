<?php
  	function query_plugin_js($standard = null, $area = null, $sSQL_field = "ID, name, type", $sSQL_where = null) {
		$globals = ffGlobals::getInstance("gallery");
		$db = ffDB_Sql::factory();
		
		if(!$area)
			$area = array("VIRTUAL_GALLERY", "GALLERY", "PUBLISHING");
  		
  		$content_type_default = "'content'";
  		switch($standard) {
  			case "extended_type":
  				$field_type = "extended_type.ID";
  				
  				$sSQL_join = "LEFT JOIN extended_type ON FIND_IN_SET(extended_type.ID, layout_type_plugin.limit_ext_type)";
  				break;
  			case "Number":
  				$content_type_default = "1";
				$field_type = " IF(layout_type_plugin.type = 'image'
				                    , 2
				                    , IF(layout_type_plugin.type = 'content'
				                        , 1
				                        , 0
				                    )
			                    )";
  				break;
  			default:
  				$field_type = "layout_type_plugin.type";
  		
  		}
		if(!$sSQL_where)
			$sSQL_where = "[WHERE]";
		else 
  			$sSQL_where = "WHERE " . $sSQL_where;
  	
  		$sSQL = "SELECT " . $sSQL_field . " FROM 
		        (
		        	SELECT 'ajaxcontent' AS ID
		        		, 'Ajax Content' AS name
		        		, $content_type_default AS type
					UNION		        		
		            SELECT DISTINCT
		                js.name AS ID
		                , js.name AS name
		                , " . $field_type . " AS type 
		            FROM layout_type_plugin
		                INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status > 0
		                INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND  layout_type.name IN('" . implode("','", $area) . "') 
		                $sSQL_join
		            WHERE layout_type_plugin.type <> ''
		        ) AS js
		        $sSQL_where
	            ORDER BY js.name";
  	
  		return $sSQL;
	}
?>
