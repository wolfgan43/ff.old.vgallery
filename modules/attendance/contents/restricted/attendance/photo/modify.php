<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$avatar_model = "horizzontal";

$sSQL = "SELECT 
			anagraph.ID AS ID_anagraph
			, " . CM_TABLE_PREFIX . "mod_attendance_office.ID AS ID_office
			, " . CM_TABLE_PREFIX . "mod_attendance_office.name
 		FROM anagraph
			INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
			INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = anagraph.ID
			INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
		WHERE " . CM_TABLE_PREFIX . "mod_security_users.status > 0
			AND " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($UserNID, "Number") . "
		ORDER BY " . CM_TABLE_PREFIX . "mod_security_users.ID DESC";
$db->query($sSQL);
if($db->nextRecord()) {
	$ID_anagraph = $db->getField("ID_anagraph", "Number", true);
	$ID_office = $db->getField("ID_office", "Number", true);
	do{
		$array_office[$db->getField("name", "Number", true)] = array(new ffData($db->getField("ID_office", "Number", true), "Number"), new ffData($db->getField("name", "Text", true)));
	} while ($db->nextRecord());
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PhotoModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("photo_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_photo";
$oRecord->addEvent("on_done_action", "PhotoModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
    
$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_date");
$oField->base_type = "date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->default_value = new ffData(date("d-m-Y", time()), "Date", FF_LOCALE);
$oField->widget = "datepicker";
$oField->required = true;
$oRecord->addContent($oField);  

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oRecord->additional_fields["ID_user"] = new ffData($ID_anagraph, "Number");
	$oRecord->insert_additional_fields["ID_user"] = new ffData($ID_anagraph, "Number");
	
	if(is_array($array_office) && count($array_office) < 2)
	{
		$oRecord->additional_fields["ID_office"] = new ffData($ID_office, "Number");
		$oRecord->insert_additional_fields["ID_office"] = new ffData($ID_office, "Number");
	} else
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_office";
		$oField->container_class = "office";
		$oField->base_type = "Number";
		$oField->extended_type = "Selection";
		$oField->label = ffTemplate::_get_word_by_code("photo_modify_office");
		$oField->multi_pairs = $array_office;
		$oField->multi_select_one = false;
		$oRecord->addContent($oField);
	}
} else {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_office";
	$oField->container_class = "office";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_modify_office");
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_office ORDER BY name";
	if(isset($_REQUEST["keys"]["ID"])) {
		$oField->control_type = "label";
		$oField->extended_type = "Selection";
	} else {
		$oField->required = true;
		$oField->widget = "activecomboex";
		$oField->actex_child = "ID_user";
		$oField->actex_update_from_db = true;
		$oField->resources[] = "OfficeModify";
		if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
			$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/office/modify";
			$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		}
	}
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user";
	$oField->label = ffTemplate::_get_word_by_code("photo_modify_user");
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT
						    anagraph.ID
                            , " . (check_function("get_user_data")
                                ? get_user_data("Fname", "anagraph", null, false)
                                : "''"
                            ) . " AS Fname
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
	if(isset($_REQUEST["keys"]["ID"])) {
		$oField->control_type = "label";
		$oField->extended_type = "Selection";
	} else {
		$oField->widget = "activecomboex";
		$oField->actex_father = "ID_office";
		$oField->actex_related_field = "ID_office";
		$oField->actex_update_from_db = true;
		$oField->actex_dialog_show_add = false;
		if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
			$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_employee;
			$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
		}
		$oField->resources[] = "AnagraphModify";
		$oField->required = true;
	}
	$oRecord->addContent($oField);
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_event";
$oField->container_class = "event";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_event");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_event ORDER BY name";
if(0 && isset($_REQUEST["keys"]["ID"])) {
	$oField->control_type = "label";
	$oField->extended_type = "Selection";
} else {
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->resources[] = "PhotoEventModify";
	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path  . "/event/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	}
}
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_event_not_set");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_argument";
$oField->container_class = "argument";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_argument");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument ORDER BY name";
if(0 && isset($_REQUEST["keys"]["ID"])) {
	$oField->control_type = "label";
	$oField->extended_type = "Selection";
} else {
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->resources[] = "PhotoArgumentModify";
	$oField->actex_child = "ID_detail";
	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path  . "/argument/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	}
}
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_argument_not_set");
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_detail";
$oField->container_class = "detail";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_detail");
$oField->source_SQL = "SELECT ID, name, ID_argument 
						FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail 
						[WHERE]
						ORDER BY name";
if(0 && isset($_REQUEST["keys"]["ID"])) {
	$oField->control_type = "label";
	$oField->extended_type = "Selection";
} else {
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->resources[] = "PhotoArgumentDetailModify";
	$oField->actex_father = "ID_argument";
	$oField->actex_related_field = "ID_argument";
}
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_detail_not_set");
$oRecord->addContent($oField);	

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "PhotoModifyDetail";
$oDetail->title = ffTemplate::_get_word_by_code("photo_modify_detail_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_photo_detail";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_photo" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->container_class = "photo";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_detail_path");
$oField->base_type = "Text";
$oField->extended_type = "File";
if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oField->file_storing_path = DISK_UPDIR . "/attendance/" . $ID_office . "/" . $ID_anagraph . "/[ID_FATHER]";
	$oField->file_temp_path = DISK_UPDIR . "/attendance/" . $ID_office . "/" . $ID_anagraph;
} else {
	$oField->file_storing_path = DISK_UPDIR . "/attendance/[ID_office_FATHER]/[ID_user_FATHER]/[ID_FATHER]";
	$oField->file_temp_path = DISK_UPDIR . "/attendance/[ID_office_FATHER]/[ID_user_FATHER]";
}
$oField->file_max_size = MAX_UPLOAD;
$oField->file_show_filename = true; 
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_max_size = MAX_UPLOAD;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = false;
$oField->widget = "uploadify"; 
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}

//da ripristinare
//$oField->uploadify_model = $avatar_model; 
//$oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("photo_modify_detail_description");
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function PhotoModify_on_done_action($component, $action) {
    
   
}
?>
