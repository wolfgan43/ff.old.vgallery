<?php
$permission = check_attendance_permission();
if($permission !== true &&  (!is_array($permission) || (is_array($permission) && $permission["primary_group"] = MOD_ATTENDANCE_GROUP_OFFICE && $permission[MOD_ATTENDANCE_GROUP_OFFICE]))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$anagraph_params = "ag=1&ct=0&bg=0&sg=0&cg=1&cf=0&cnf=1&gmap=0&user=1&rg=0&am=vertical&fu=1&cef=1&ug=" . MOD_ATTENDANCE_GROUP_EMPLOYEE;

if(isset($_REQUEST["sheet_page"]))
	$query_string .= "&sheet_page=" . $_REQUEST["sheet_page"];

if(isset($_REQUEST["records_per_page"]))
	$query_string .= "&records_per_page=" . $_REQUEST["records_per_page"];
if(((isset($_REQUEST["frmAction"]) && $_REQUEST["frmAction"] == "badgein") || (isset($_REQUEST["badgein"])))) {
	$db = ffDB_Sql::factory();
	if($_REQUEST["keys"]["ID"] > 0) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet 
				SET " . CM_TABLE_PREFIX . "mod_attendance_sheet.badgein = " . $db->toSql(time(), "Number") . "
					, " . CM_TABLE_PREFIX . "mod_attendance_sheet.badgein_ip = " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
		$db->execute($sSQL);
	}

	if($_REQUEST["interval"] > 0) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
				SET " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgein = " . $db->toSql(time(), "Number") . "
					, " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgein_ip = " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID = " . $db->toSql($_REQUEST["interval"], "Number");
		$db->execute($sSQL);
	}
	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array("url" => $_REQUEST["ret_url"] . $query_string, "close" => false, "refresh" => true), true));
	} else {
	    die(ffCommon_jsonenc(array("url" => $_REQUEST["ret_url"] . $query_string, "close" => false, "refresh" => true), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	}
}

if(((isset($_REQUEST["frmAction"]) && $_REQUEST["frmAction"] == "badgeout") || (isset($_REQUEST["badgeout"])))) {
	$db = ffDB_Sql::factory();
	if($_REQUEST["keys"]["ID"] > 0) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet 
				SET " . CM_TABLE_PREFIX . "mod_attendance_sheet.badgeout = " . $db->toSql(time(), "Number") . "
					, " . CM_TABLE_PREFIX . "mod_attendance_sheet.badgeout_ip = " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
		$db->execute($sSQL);
	}	
	if($_REQUEST["interval"] > 0) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
				SET " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgeout = " . $db->toSql(time(), "Number") . "
					, " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgeout_ip = " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID = " . $db->toSql($_REQUEST["interval"], "Number");
		$db->execute($sSQL);
	}
	
	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array("url" => $_REQUEST["ret_url"] . $query_string, "close" => false, "refresh" => true), true));
	} else {
	    die(ffCommon_jsonenc(array("url" => $_REQUEST["ret_url"] . $query_string, "close" => false, "refresh" => true), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	}
}


$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.* 
		FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
		WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$db->query($sSQL);
if($db->numRows()) {
	$sheet_interval_set = true;
} else {
	$sheet_interval_set = false;
}

if($_REQUEST["keys"]["ID"] > 0 && check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE)
{
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
				FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$day = $db->getField("day", "Date")->getValue("Timestamp", FF_SYSTEM_LOCALE);
	}
	
} else
{
	$day = 0;
}

$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "SheetModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("sheet_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet";
$oRecord->addEvent("on_do_action", "SheetModify_on_do_action");
if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oRecord->buttons_options["delete"]["display"] = true;
} else {
	$oRecord->buttons_options["delete"]["display"] = false;
}
$oRecord->user_vars["day"] = $day;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "day";
$oField->label = ffTemplate::_get_word_by_code("sheet_modify_day");
$oField->base_type = "date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->default_value = new ffData(date("d-m-Y", time()), "Date", FF_LOCALE);
if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField->widget = "datepicker";
	$oField->required = true;
} else {
	$oField->control_type = "label";
}
$oRecord->addContent($oField);  

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_office";
	$oField->container_class = "office";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_office");
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_office ORDER BY name";
	$oField->required = true;
	$oField->widget = "activecomboex";
	$oField->actex_child = "ID_user";
	$oField->actex_update_from_db = true;
	$oField->resources[] = "OfficeModify";
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/office/modify";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oRecord->addContent($oField);

	if(check_function("get_user_data"))
		$Fname_sql = get_user_data("Fname", "anagraph", null, false);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_user");
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
	$oField->widget = "activecomboex";
	$oField->actex_father = "ID_office";
	$oField->actex_related_field = "ID_office";
	$oField->actex_update_from_db = true;
	$oField->actex_dialog_show_add = false;
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
	$oField->resources[] = "AnagraphModify";
	$oField->required = true;
	$oRecord->addContent($oField);
}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
	$oDetail->id = "SheetModifyIntervalDefault";
	$oDetail->title = ffTemplate::_get_word_by_code("sheet_modify_interval_default_title");
	$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default";
	$oDetail->order_default = "ID";
	$oDetail->min_rows = 1;
	$oDetail->force_min_rows = true;
	$oDetail->fields_relationship = array ("ID_sheet" => "ID");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "time_from";
	$oField->container_class = "time-from";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_default_time_from");
	$oField->extended_type = "Time";
	$oField->widget = "timepicker";
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "time_to";
	$oField->container_class = "time-to";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_default_time_to");
	$oField->extended_type = "Time";
	$oField->widget = "timepicker";
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_default_type");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT ID, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_type
							WHERE 1
							ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
								, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
	$oField->required = true;
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_modify_type_not_set");
	//$oField->multi_select_one = false;
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail);
	$cm->oPage->addContent($oDetail);
}

if((check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE )
	|| (check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE && ($day - time() > -86400 && $day - time() < 86400))
) {
	$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
	$oDetail->id = "SheetModifyInterval";
	$oDetail->title = ffTemplate::_get_word_by_code("sheet_modify_interval_title");
	$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet_interval";
	$oDetail->order_default = "ID";
	//$oDetail->starting_rows = 2;
	if(check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oDetail->min_rows = 1;
		$oDetail->force_min_rows = true;
	}

	$oDetail->fields_relationship = array ("ID_sheet" => "ID");
	$oDetail->addEvent("on_before_process_field", "SheetModifyInterval_on_before_process_field");
	$oDetail->addEvent("on_after_process_row", "SheetModifyInterval_on_after_process_row");

	if(!$sheet_interval_set) {
		$oDetail->auto_populate_edit = true;
		$oDetail->populate_edit_SQL = "SELECT 
												DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '%H:%i') AS time_from
												, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '%H:%i') AS time_to
												, " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_type AS ID_type
												, " . $db->toSql($UserNID, "Number") . " AS ID_owner
											FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
											WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = [ID_FATHER]
											ORDER BY time_from 
											";
	}
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_owner";
	$oField->base_type = "Number";
	$oField->store_in_db = false;
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "time_from";
	$oField->container_class = "time-from";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_time_from");
	$oField->extended_type = "Time";
	$oField->widget = "timepicker";
	$oField->required = true;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "time_to";
	$oField->container_class = "time-to";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_time_to");
	$oField->extended_type = "Time";
	$oField->widget = "timepicker";
	$oField->required = true;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_type");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT ID, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_type
							WHERE " . (check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE
								? " 1 "
								: CM_TABLE_PREFIX . "mod_attendance_type.enable_sheet > 0 "
							) . "
							ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
								, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
	$oField->required = true;
	$oField->multi_select_one = false;
	$oDetail->addContent($oField);

	//$oDetail->update_additional_fields["ID_owner"] = new ffData(get_session("UserNID"), "Number");
	$oDetail->insert_additional_fields["ID_owner"] = new ffData(get_session("UserNID"), "Number");

	$oRecord->addContent($oDetail);
	$cm->oPage->addContent($oDetail);
}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "description";
	$oField->container_class = "description";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_description");
	$oField->extended_type = "Text";
	$oRecord->addContent($oField); 
}
if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "note";
	$oField->container_class = "note";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_note_admin");
	$oField->extended_type = "Text";
	$oRecord->addContent($oField); 
}

$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function SheetModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	
	switch($action) {
		case "insert":
		case "update":
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.day 
					FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($component->form_fields["day"]->value) . "
						AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($component->form_fields["ID_office"]->value) . "
						AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($component->form_fields["ID_user"]->value) . "
						AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID <> " . $db->toSql($component->key_fields["ID"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$component->tplDisplayError(ffTemplate::_get_word_by_code("mod_attendance_sheet_date_not_unic"));
				return true;
			}
			break;
		default:
	}
    
   
}

function SheetModifyInterval_on_before_process_field($component, $rst_val, $field) {
	
	if($rst_val["ID_owner"]->getValue() > 0
		&& check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_ATTENDANCE
		&& $rst_val["ID_owner"]->getValue() != get_session("UserNID")
	) {
		$allow_edit = false;
	} else {
		$allow_edit = true;
	}

	if($allow_edit) {
		if($field->extended_type == "Time") {
			$field->control_type = "input";
			$field->widget = "timepicker";
		}
		if($field->extended_type == "Selection") {
			$field->control_type = "combo";
			$field->source_SQL = "SELECT ID, name 
									FROM " . CM_TABLE_PREFIX . "mod_attendance_type
									WHERE " . (check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE
										? " 1 "
										: CM_TABLE_PREFIX . "mod_attendance_type.enable_sheet > 0 "
									) . "
									ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
										, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
		}
	} else {
		if($field->extended_type == "Selection") {
			$field->source_SQL = "SELECT ID, name 
									FROM " . CM_TABLE_PREFIX . "mod_attendance_type
									WHERE 1
									ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
										, " . CM_TABLE_PREFIX . "mod_attendance_type.name";

		}
		if($field->extended_type == "Time") {
			$field->setValue(str_replace("0:00", "0", $field->getValue()));
		}
		$field->control_type = "label";
		$field->widget = "";
	}
	$field->pre_process(true);
}

function SheetModifyInterval_on_after_process_row($component, $rst_key) 
{
	if($component->recordset[$rst_key]["ID_owner"]->getValue() > 0
		&& check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_ATTENDANCE
		&& $component->recordset[$rst_key]["ID_owner"]->getValue() != get_session("UserNID")
	) {
		$allow_edit = false;
	} else 
	{
		$allow_edit = true;
	}

	$component->display_delete = $allow_edit;
	$component->detail_buttons["detail_delete"]["obj"]->display = $allow_edit;
}
?>