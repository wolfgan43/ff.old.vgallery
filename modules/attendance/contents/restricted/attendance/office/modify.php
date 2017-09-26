<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$anagraph_params_office = "ag=1&ct=0&bg=0&sg=0&cg=1&cf=0&cnf=1&gmap=0&user=1&rg=0&am=vertical&fu=1&cef=1&ug=" . MOD_ATTENDANCE_GROUP_OFFICE;
$anagraph_params_employee = "ag=1&ct=0&bg=0&sg=0&cg=1&cf=0&cnf=1&gmap=0&user=1&rg=0&am=vertical&fu=1&cef=1&ug=" . MOD_ATTENDANCE_GROUP_EMPLOYEE;

if(check_function("get_user_data"))
	$Fname_sql = get_user_data("Fname", "anagraph", null, false);


$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "OfficeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("office_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_office";
$oRecord->addEvent("on_done_action", "OfficeModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("office_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "name_director";
$oField->label = ffTemplate::_get_word_by_code("office_modify_name_director");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "email_director";
$oField->label = ffTemplate::_get_word_by_code("office_modify_email_director");
$oField->required = true;
$oField->addValidator("email");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "email_manager";
$oField->label = ffTemplate::_get_word_by_code("office_modify_email_manager");
//$oField->addValidator("email");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_owner";
$oField->label = ffTemplate::_get_word_by_code("office_modify_owner");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
				    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_OFFICE) . "
					    				)
				    GROUP BY anagraph.ID
				    ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;

$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_office;
$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => $oRecord->id . "_" . $oField->id);
$oField->resources[] = "AnagraphModify";
//$oField->required = true;
$oRecord->addContent($oField);

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "OfficeModifyInterval";
$oDetail->title = ffTemplate::_get_word_by_code("office_modify_interval_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_office_interval";
$oDetail->order_default = "ID";
//$oDetail->starting_rows = 2;
$oDetail->min_rows = 2;
$oDetail->force_min_rows = true;
$oDetail->fields_relationship = array ("ID_office" => "ID");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_interval.*
									FROM " . CM_TABLE_PREFIX . "mod_attendance_office_interval
									WHERE " . CM_TABLE_PREFIX . "mod_attendance_office_interval.ID_office = [ID_office_VALUE]
									ORDER BY time_from ";
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "time_from";
$oField->container_class = "time-from";
$oField->label = ffTemplate::_get_word_by_code("office_modify_interval_time_from");
$oField->extended_type = "Time";
$oField->widget = "timepicker";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_to";
$oField->container_class = "time-to";
$oField->label = ffTemplate::_get_word_by_code("office_modify_interval_time_to");
$oField->extended_type = "Time";
$oField->widget = "timepicker";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->container_class = "type";
$oField->label = ffTemplate::_get_word_by_code("office_modify_interval_type");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_attendance_type
						WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.default > 0 
						ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.name";
$oField->required = true;
$oField->multi_select_one = false;
$oDetail->addContent($oField);


$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "OfficeModifyEmployee";
$oDetail->title = ffTemplate::_get_word_by_code("office_modify_employee_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_office_employee";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_office" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_user";
$oField->label = ffTemplate::_get_word_by_code("sheet_modify_user");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
				    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_EMPLOYEE) . "
					    				)
				    GROUP BY anagraph.ID
				    ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;

$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_employee;
$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
$oField->resources[] = "AnagraphModify";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "role";
$oField->label = "Ruolo";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("SIS Manager"), new ffData("SIS Manager"))
								, array(new ffData("SIS Assistant"), new ffData("SIS Assistant"))
							);
$oField->required = true;
//$oField->multi_select_one = false;
$oDetail->addContent($oField);


$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);



$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function OfficeModify_on_done_action($component, $action) {
    
   
}
?>
