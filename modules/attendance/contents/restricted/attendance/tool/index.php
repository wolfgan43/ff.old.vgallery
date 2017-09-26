<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

/*$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));

if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffGrid.html")) {
	$oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
}*/
$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oGrid = ffGrid::factory($cm->oPage);

$oGrid->full_ajax = true;
$oGrid->id = "tool";
$oGrid->title = ffTemplate::_get_word_by_code("tool_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_attendance_tool.*
	                        , (IF(anagraph.uid > 0
	                            , IF(anagraph.billreference = ''
	                                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                		, IF(CONCAT(anagraph.name, ' ', anagraph.surname) = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                			, CONCAT(anagraph.name, ' ', anagraph.surname)
                                			, CONCAT(CONCAT(anagraph.name, ' ', anagraph.surname), ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
                                		)
                                		, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
	                                )
	                                , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                		, CONCAT(anagraph.name, ' ', anagraph.surname)
                                		, CONCAT(anagraph.billreference, ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
	                                )
	                            )
	                            , IF(anagraph.billreference = ''
                            		, CONCAT(anagraph.name, ' ', anagraph.surname)
                            		, anagraph.billreference
	                            )
	                        )) AS anagraph
	                        , IF(limit_day_of_week = ''
	                        	, " . $db->toSql(ffTemplate::_get_word_by_code("attendance_tool_day_of_week_all")) . "
	                        	, REPLACE( 
	                        		REPLACE(
	                        			REPLACE(
	                        				REPLACE(
	                        					REPLACE(
	                        						REPLACE(
	                        							REPLACE(
	                        								limit_day_of_week
	                        								, '1'
	                        								, " . $db->toSql(ffTemplate::_get_word_by_code("monday")) . "
	                        							)
	                        							, '2'
	                        							, " . $db->toSql(ffTemplate::_get_word_by_code("tuesday")) . "
	                        						)
	                        						, '3'
	                        						, " . $db->toSql(ffTemplate::_get_word_by_code("wednesday")) . "
	                        					)
	                        					, '4'
	                        					, " . $db->toSql(ffTemplate::_get_word_by_code("thursday")) . "
	                        				)
	                        				, '5'
	                        				, " . $db->toSql(ffTemplate::_get_word_by_code("friday")) . "
	                        			)
	                        			, '6'
	                        			, " . $db->toSql(ffTemplate::_get_word_by_code("saturday")) . "
	                        		)
	                        		, '7'
	                        		, " . $db->toSql(ffTemplate::_get_word_by_code("sunday")) . "
	                        	)
	                        ) AS limit_day_of_week
	                        , GROUP_CONCAT(DISTINCT CONCAT(
	                        		(SELECT " . CM_TABLE_PREFIX . "mod_attendance_type.name FROM " . CM_TABLE_PREFIX . "mod_attendance_type WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.ID_type )
									, '###'
	                        		, IF(" . CM_TABLE_PREFIX . "mod_attendance_tool_interval.time_from = '00:00:00' 
	                        			AND " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.time_to = '00:00:00' 
	                        			, ''
	                        			, CONCAT(
	                        				DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_tool_interval.time_from, '%H:%i')
	                        				, ' / '
	                        				, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_tool_interval.time_to, '%H:%i')
	                        			)
	                        		)
	                        	)
								ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.time_from
	                        	SEPARATOR '@@@'
	                        ) AS `interval`
                        FROM
                            " . CM_TABLE_PREFIX . "mod_attendance_tool
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_tool.ID_user
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_tool_interval ON " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.ID_tool = " . CM_TABLE_PREFIX . "mod_attendance_tool.ID
                        WHERE 1
                        [AND] [WHERE] 
                        GROUP BY " . CM_TABLE_PREFIX . "mod_attendance_tool.ID
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "date_since";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "ToolModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "tool_on_before_parse_row"); 


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "date_since";
$oField->label = ffTemplate::_get_word_by_code("tool_date_since");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date_to";
$oField->label = ffTemplate::_get_word_by_code("tool_date_to");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anagraph";
$oField->container_class = "user";
$oField->label = ffTemplate::_get_word_by_code("tool_user");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_day_of_week";
$oField->label = ffTemplate::_get_word_by_code("tool_limit_day_of_week");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "interval";
$oField->container_class = "interval";
$oField->label = ffTemplate::_get_word_by_code("tool_interval");
$oField->encode_entities = false;
$oGrid->addContent($oField);



$cm->oPage->addContent($oGrid);


function tool_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
   
    if(isset($component->grid_fields["interval"])) {
    	$interval = array();
		$tmp_interval = $component->db[0]->getField("interval", "Text", true);
		if(strlen($tmp_interval)) {
			$arrInterval = explode("@@@", $tmp_interval);
			if(is_array($arrInterval) && count($arrInterval)) {
				foreach($arrInterval AS $arrInterval_key => $arrInterval_value) {
					if(strlen($arrInterval_value)) {
						$arrIntervalDetail = explode("###", $arrInterval_value);
						if(is_array($arrIntervalDetail) && count($arrIntervalDetail)) {
							$interval[$arrIntervalDetail[0]][] = $arrIntervalDetail[1]; 
						}
					}
				}
			}
		}
		$str_interval = "";
		$first_time_interval = "";
		if(is_array($interval) && count($interval)) {
			foreach($interval AS $interval_key => $interval_value) {
				if(is_array($interval_value) && count($interval_value)) {
					$interval_detail = "";
					foreach($interval_value AS $interval_value_detail) {
						if(strlen($interval_value_detail)) {
							if(!strlen($first_time_interval)) {
								$arrFirstTime = explode(" / ", $interval_value_detail);
								if(strlen($arrFirstTime[0]))
									$first_time_interval = $arrFirstTime[0];
							}
							$interval_detail .= '<div class="interval-row">' . $interval_value_detail . '</div>'; 
						}
					}
				}
				
				$str_interval = '<label "' . ffCommon_url_rewrite($interval_key) .'">' . $interval_key . '</label>' . $interval_detail;
			}
		}
		$component->grid_fields["interval"]->setValue($str_interval);
    }
}


?>