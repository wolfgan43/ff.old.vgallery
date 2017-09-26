<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ToolModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("tool_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_tool";
$oRecord->addEvent("on_done_action", "ToolModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date_since";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_date_since");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->default_value = new ffData(date("d-m-Y", time() + 86400), "Date", FF_LOCALE);
if($_REQUEST["keys"]["ID"] > 0) {
	$oField->control_type = "label";
	$oField->store_in_db = false;
} else {
	$oField->widget = "datepicker";
	$oField->required = true;	
}
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "date_to";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_date_to");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->default_value = new ffData(date("d-m-Y", time() + 172800), "Date", FF_LOCALE);
if($_REQUEST["keys"]["ID"] > 0) {
	$oField->control_type = "label";
	$oField->store_in_db = false;
} else {
	$oField->widget = "datepicker";
	$oField->required = true;	
}
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_office";
$oField->container_class = "office";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_office");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_office ORDER BY name";
$oField->resources[] = "OfficeModify";
if($_REQUEST["keys"]["ID"] > 0) {
	$oField->extended_type = "Selection";
	$oField->control_type = "label";
	$oField->store_in_db = false;
} else {
	$oField->widget = "activecomboex";
	$oField->actex_child = "ID_user";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/office/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->required = true;
}
$oRecord->addContent($oField);

if(check_function("get_user_data"))
	$Fname_sql = get_user_data("Fname", "anagraph", null, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_user";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_user");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
					    anagraph.ID
					    , " . $Fname_sql . " AS Fname
		                , " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office AS ID_office
					FROM anagraph
        				INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = anagraph.ID
					WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_EMPLOYEE) . "
					    				)
					    [AND] [WHERE]
					GROUP BY anagraph.ID
					ORDER BY Fname";
$oField->resources[] = "AnagraphModify";
if($_REQUEST["keys"]["ID"] > 0) {
	$oField->extended_type = "Selection";
	$oField->control_type = "label";
	$oField->store_in_db = false;
} else {
	$oField->widget = "activecomboex";
	$oField->actex_father = "ID_office";
	$oField->actex_related_field = "ID_office";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_show_add = false;
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
	$oField->required = true;
}
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "limit_day_of_week";
$oField->label = ffTemplate::_get_word_by_code("tool_limit_day_of_week");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	                        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("monday"))),
	                        array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("tuesday"))),
	                        array(new ffData("3", "Number"), new ffData(ffTemplate::_get_word_by_code("wednesday"))),
	                        array(new ffData("4", "Number"), new ffData(ffTemplate::_get_word_by_code("thursday"))),
	                        array(new ffData("5", "Number"), new ffData(ffTemplate::_get_word_by_code("friday"))),
	                        array(new ffData("6", "Number"), new ffData(ffTemplate::_get_word_by_code("saturday"))),
	                        array(new ffData("7", "Number"), new ffData(ffTemplate::_get_word_by_code("sunday")))
	                   );
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("attendance_tool_day_of_week_all");
if($_REQUEST["keys"]["ID"] > 0) {
	$oField->properties["disabled"] = "disabled";
	$oField->store_in_db = false;
}
$oRecord->addContent($oField);

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "note";
	$oField->container_class = "note";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_note_admin");
	$oField->extended_type = "Text";
	$oRecord->addContent($oField); 
}



$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "ToolModifyInterval";
$oDetail->title = ffTemplate::_get_word_by_code("tool_modify_interval_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_tool_interval";
$oDetail->order_default = "ID";
$oDetail->starting_rows = 2;
$oDetail->min_rows = 1;
$oDetail->force_min_rows = true;
$oDetail->fields_relationship = array ("ID_tool" => "ID");
//$oDetail->addEvent("on_before_process_field", "SheetModifyInterval_on_before_process_field");
//$oDetail->addEvent("on_after_process_row", "SheetModifyInterval_on_after_process_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_from";
$oField->container_class = "time-from";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_interval_time_from");
$oField->extended_type = "Time";
$oField->widget = "timepicker";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_to";
$oField->container_class = "time-to";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_interval_time_to");
$oField->extended_type = "Time";
$oField->widget = "timepicker";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->container_class = "type";
$oField->label = ffTemplate::_get_word_by_code("tool_modify_interval_type"); 
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_attendance_type
						WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.enable_tool > 0
						ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
							, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
$oField->required = true;
//$oField->multi_select_one = false;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
						
$cm->oPage->addContent($oRecord);   

function ToolModify_on_done_action($component, $action) {
	$db = ffDB_Sql::factory();
	$UserNID = get_session("UserNID");
    switch($action) {
		case "insert":
		case "update":	
			$date_since = $component->form_fields["date_since"]->getValue("Timestamp");
			$date_to = $component->form_fields["date_to"]->getValue("Timestamp") + 86400; // aggiungo un giorno più due ore per evitare il cambio fuso
			$date_diff = $date_to - $date_since;
			$count_day = floor($date_diff / 86400); 
			$note = $component->form_fields["note"]->getValue("Text");
/*			if($UserNID == 1)
			{
				echo $count_day;  
			}
*/
			$arrDayOfWeek = array();
			$limit_day_of_week = $component->form_fields["limit_day_of_week"]->getValue();
			if(strlen($limit_day_of_week))
				$arrDayOfWeek = explode(",", $limit_day_of_week);
/*
			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);
*/
			for($i=0; $i < $count_day; $i++) {
				
				$data = date("N", $date_since + (86400 * $i) + 3601);
				if(count($arrDayOfWeek) && array_search($data, $arrDayOfWeek) === false) { 
					continue;
				}
				$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
						FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
						WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql(date("Y-m-d", $date_since + (86400 * $i) + 3601), "Date", FF_SYSTEM_LOCALE) . "
							AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($component->form_fields["ID_user"]->value) . "
							AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($component->form_fields["ID_office"]->value);
				$db->query($sSQL);
				if($db->nextRecord()) 
				{
					$ID_sheet = $db->getField("ID", "Number", true);
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET 
								" . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_tool = " . $db->toSql($component->key_fields["ID"]->value) . " 
								, " . CM_TABLE_PREFIX . "mod_attendance_sheet.last_update =  " . $db->toSql(time()) . "
								, " . CM_TABLE_PREFIX . "mod_attendance_sheet.note =  " . $db->toSql($note) . "
							WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet, "Number");
					$db->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet
							(
								ID 	
								, ID_user
								, ID_office
								, day
								, last_update
								, ID_tool
								, note
							) VALUES (
								null
								, " . $db->toSql($component->form_fields["ID_user"]->value) . "
								, " . $db->toSql($component->form_fields["ID_office"]->value) . "
								, " . $db->toSql(date("Y-m-d", $date_since + (86400 * $i) + 3601 )) . "
								, " . $db->toSql(time()) . "
								, " . $db->toSql($component->key_fields["ID"]->value) . "
								, " . $db->toSql($note) . "
							)";
					$db->execute($sSQL);
					$ID_sheet = $db->getInsertID(true);
				}

				$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
						WHERE 1
							AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet, "Number");
				$db->execute($sSQL);
				
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
						(
							ID_type
							, ID_sheet
							, time_from
							, time_to
							, ID_tool
						)
						(
							SELECT 
								ID_type
								, " . $db->toSql($ID_sheet, "Number") . "
								, time_from
								, time_to
								, " . $db->toSql($component->key_fields["ID"]->value) . "
							FROM " . CM_TABLE_PREFIX . "mod_attendance_tool_interval
							WHERE " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.ID_tool = " . $db->toSql($component->key_fields["ID"]->value) . "
						)";
				$db->execute($sSQL);
			}
			break;
		/*case "update":			
			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
						last_update =  " . $db->toSql(time()) . "
						
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);

			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.*
					FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$db_insert = ffDB_Sql::factory();
				do {
					$ID_sheet = $db->getField("ID", "Number", true);

					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
							(
								ID_type
								, ID_sheet
								, time_from
								, time_to
								, ID_tool
							)
							(
								SELECT 
									ID_type
									, " . $db_insert->toSql($ID_sheet, "Number") . "
									, time_from
									, time_to
									, " . $db_insert->toSql($component->key_fields["ID"]->value) . "
								FROM " . CM_TABLE_PREFIX . "mod_attendance_tool_interval
								WHERE " . CM_TABLE_PREFIX . "mod_attendance_tool_interval.ID_tool = " . $db_insert->toSql($component->key_fields["ID"]->value) . "
							)";
					$db_insert->execute($sSQL);
				} while($db->nextRecord());
			} else {
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet
						(
							ID 	
							, ID_user
							, ID_office
							, day
							, last_update
							, ID_tool
						) VALUES (
							null
							, " . $db->toSql($component->form_fields["ID_user"]->value) . "
							, " . $db->toSql($component->form_fields["ID_office"]->value) . "
							, " . $db->toSql(date("Y-m-d", $date_since + (86400 * $i) )) . "
							, " . $db->toSql(time()) . "
							, " . $db->toSql($component->key_fields["ID"]->value) . "
						)";
				$db->execute($sSQL);
				$ID_sheet = $db->getInsertID(true);
			}

			break;*/
		case "confirmdelete":
			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);
			
			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet IN(SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_tool = " . $db->toSql($component->key_fields["ID"]->value) . ")";
			$db->execute($sSQL);

			$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_tool = " . $db->toSql($component->key_fields["ID"]->value);
			$db->execute($sSQL);

			break;
    }
}
?>