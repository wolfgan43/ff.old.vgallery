<?php
use_cache(false);

$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

switch(check_attendance_permission(true)) {
	case MOD_ATTENDANCE_GROUP_ATTENDANCE:
		$sSQL_permission = " ";
		$sSQL_permission_request = " ";
		$display_addnew = true;
		$display_delete = true;
		break;
	case MOD_ATTENDANCE_GROUP_OFFICE:
		$sSQL_permission = " AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office IN ( SELECT " . CM_TABLE_PREFIX . "mod_attendance_office.ID FROM " . CM_TABLE_PREFIX . "mod_attendance_office WHERE " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner = (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " ))";
		$sSQL_permission_request = " AND 0 ";
		
		$display_addnew = false;
		$display_delete = false;
		break;
	case MOD_ATTENDANCE_GROUP_EMPLOYEE:
		$sSQL_permission = " AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user IN ( SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " )";
		$sSQL_permission_request = " AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_user IN ( SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " )";
		$display_addnew = false;
		$display_delete = false;
		break;
	default:
		$sSQL_permission = " AND 0 ";
		$display_addnew = false;
		$display_delete = false;
	
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
		FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet 
		WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day < CURDATE()
			$sSQL_permission
		ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet.day ASC";
$db->query($sSQL);
if($db->nextRecord()) {
	$num_day = $db->numRows();
}
$rec_per_page = (isset($_REQUEST["sheet_records_per_page"])
				? $_REQUEST["sheet_records_per_page"]
				: 25
			);

$page = ceil($num_day / $rec_per_page);


$arrField = array();
$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_type.* 
		FROM " . CM_TABLE_PREFIX . "mod_attendance_type
		WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.enable_sheet_grid > 0
		ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.enable_sheet_grid
			, " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
			, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
$db->query($sSQL);
if($db->nextRecord()) {
	$sSQL_field = "";
	do {
		$ID_type = $db->getField("ID", "Number", true);
		$arrField[$ID_type]["smart_url"] = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("name", "Text", true)));
		$arrField[$ID_type]["label"] = $db->getField("name", "Text", true);
		$sSQL_field .= ", (
	                        SELECT
	                        	SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF( " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_to,  " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from ))))
	                        FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
	                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
	                        	AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_type = " . $db->toSql($db->getField("ID", "Number")) . "
                                        
	                    ) AS " . $arrField[$db->getField("ID", "Number", true)]["smart_url"];
	} while($db->nextRecord());
}

$sSQL = "SELECT anagraph_fields.* 
			FROM anagraph_fields
			WHERE anagraph_fields.enable_in_grid > 0
			ORDER BY anagraph_fields.enable_in_grid
				, anagraph_fields.name";
$db->query($sSQL);
if($db->nextRecord()) {
	$sSQL_field_anagraph = "";
	do {
		$arrField_anagraph[$db->getField("ID", "Number", true)]["smart_url"] = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("name", "Text", true)));
		$arrField_anagraph[$db->getField("ID", "Number", true)]["label"] = $db->getField("name", "Text", true);
		$sSQL_field_anagraph .= ", (
	                        SELECT IF(anagraph_rel_nodes_fields.description_text = ''
                                    , anagraph_rel_nodes_fields.description
                                    , anagraph_rel_nodes_fields.description_text
                                )
								FROM anagraph_rel_nodes_fields
								WHERE anagraph_rel_nodes_fields.ID_nodes = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user
									AND anagraph_rel_nodes_fields.ID_fields = " . $db->getField("ID", "Number", true) . "
	                    ) AS " . $arrField_anagraph[$db->getField("ID", "Number", true)]["smart_url"];
	} while($db->nextRecord());
}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
				, " . CM_TABLE_PREFIX . "mod_attendance_office.name
				, " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from
				, " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_to
			FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
				INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office
				LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID 
			WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = CURDATE()
				$sSQL_permission
			ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do{
			$badgein = $db->getField("badgein", "Number", true);
			$badgein_time = $db->getField("badgein", "Timestamp")->getValue("Time", FF_LOCALE);
			$badgeout = $db->getField("badgeout", "Number", true);
			$badgeout_time = $db->getField("badgeout", "Timestamp")->getValue("Time", FF_LOCALE);
			$ID_actual_sheet = $db->getField("ID", "Number", true);
			$office_name = $db->getField("name", "Text", true);
			$saved_ID[$ID_actual_sheet]["badgein"] = $badgein;
			$saved_ID[$ID_actual_sheet]["badgein_time"] = $badgein_time;
			$saved_ID[$ID_actual_sheet]["badgeout"] = $badgeout;
			$saved_ID[$ID_actual_sheet]["badgeout_time"] = $badgeout_time;
			$saved_ID[$ID_actual_sheet]["office"] = $office_name;
			$saved_ID[$ID_actual_sheet]["day"] = $db->getField("day", "Text", true);
			$saved_ID[$ID_actual_sheet]["time_from"] = $db->getField("time_from", "Text", true);
			
		} while($db->nextRecord());
	} else {
		$badgein = 0;
		$badgeout = 0;
		$db2 = ffDB_Sql::factory();
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
					, " . CM_TABLE_PREFIX . "mod_attendance_office.name
					FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
						INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
					WHERE  " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = (SELECT anagraph.ID 
																							FROM anagraph 
																							WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " 
																						)
					ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID DESC";
		$db->query($sSQL);
		if($db->nextRecord()){
			do{
				$ID_office = $db->getField("ID_office", "Number", true);
				$sSQL2 = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet
						(
							ID
							, ID_user
							, ID_office
							, day
							, last_update
						) VALUES (
							null
							, (SELECT anagraph.ID 
								FROM anagraph 
								WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " 
							)
							, " . $db->toSql($ID_office, "Number") . "
							, CURDATE()
							, " . $db->toSql(time(), "Number") . "
						)";
				$db2->execute($sSQL2);
				$ID_actual_sheet = $db2->getInsertID(true);
				$saved_ID[$ID_actual_sheet]["office"] = $db->getField("name", "Text", true);
			} while($db->nextRecord());
		} else {
			ffDialog(false, "okonly", ffTemplate::_get_word_by_code("access_denied"), ffTemplate::_get_word_by_code("missing_office"), FF_SITE_PATH . "/", FF_SITE_PATH . "/", $cm->oPage->site_path . $cm->oPage->page_path . "/dialog");
		}
	}

    $filename = cm_cascadeFindTemplate("/contents/sheet/today.html", "attendance");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/today.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/attendance/contents/sheet/today.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/sheet/today.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("today.html", "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("ret_url", urlencode($cm->oPage->getRequestUri()));
	
	foreach($saved_ID AS $ID_sheet_key => $ID_sheet_value)
	{
		$tpl->set_var("SezInterval", "");

		$arrBadge = array();
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.*
					, " . CM_TABLE_PREFIX . "mod_attendance_type.`default` AS type_default
				FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
					LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_type ON " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_type
				WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_key, "Number") . "
				ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$first_badge[$ID_sheet_key] = $db->getField("ID", "Number", true);
			do {
				if($db->getField("type_default", "Number", true) > 0) {
					$arrBadge[$ID_sheet_key][$db->getField("ID", "Number", true)]["in"]["time"] = $db->getField("badgein", "Number", true);
					$arrBadge[$ID_sheet_key][$db->getField("ID", "Number", true)]["out"]["time"] = $db->getField("badgeout", "Number", true);
				}
			} while($db->nextRecord());
			$last_badge[$ID_sheet_key] = $db->getField("ID", "Number", true);
		} else 
		{
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.*
					FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
					WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_key, "Number") . "
					ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from";
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$db_exec = ffDB_Sql::factory();
				do 
				{
					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
							(
								ID
								, ID_owner
								, ID_sheet
								, ID_type
								, time_from
								, time_to
							) 
							VALUES 
							(
								null
								, " . $db_exec->toSql($UserNID, "Number") . "
								, " . $db_exec->toSql($ID_sheet_key, "Number") . "
								, " . $db_exec->toSql($db->getField("ID_type", "Number"), "Number") . "
								, " . $db_exec->toSql($db->getField("time_from", "Time"), "Time") . "
								, " . $db_exec->toSql($db->getField("time_to", "Time"), "Time") . "
							)";
					$db_exec->execute($sSQL);
					$arrBadge[$ID_sheet_key][$db_exec->getInsertID(true)]["in"]["time"] = 0;
					$arrBadge[$ID_sheet_key][$db_exec->getInsertID(true)]["out"]["time"] = 0;

					if(!strlen($first_badge[$ID_sheet_key]))
						$first_badge[$ID_sheet_key] = $db_exec->getInsertID(true);
				} while($db->nextRecord());	
				$last_badge[$ID_sheet_key] = $db_exec->getInsertID(true);
			}		
		}
	
		$arrBadge[$ID_sheet_key][$first_badge[$ID_sheet_key]]["in"]["time"] = $ID_sheet_value["badgein"];
		if($arrBadge[$ID_sheet_key][$last_badge[$ID_sheet_key]]["in"]["time"] > 0) {
			$arrBadge[$ID_sheet_key][$last_badge[$ID_sheet_key]]["out"]["time"] = $ID_sheet_value["badgeout"];
		}
	
	
	
		$today = new ffData(time(), "Timestamp");
		$tpl->set_var("today_date", $today->getValue("Date", FF_LOCALE));
		if(strlen($ID_sheet_value["office"]))
		{
			$tpl->set_var("office_name", ", " . $ID_sheet_value["office"]);
		}

		if(is_array($arrBadge[$ID_sheet_key]) && count($arrBadge[$ID_sheet_key]))	
		{
			$disable_badge = false;
			
			foreach($arrBadge[$ID_sheet_key] AS $arrBadge_key => $arrBadge_value) 
			{
				if(!$disable_badge) 
				{
					if(!$arrBadge_value["in"]["time"] > 0 && !$arrBadge_value["out"]["time"] > 0) 
					{
						$disable_badge = true;

						$oBadgeIn = ffButton::factory($cm->oPage);
						$oBadgeIn->id = "badgein"; 
						$oBadgeIn->action_type = "submit";
						$oBadgeIn->url = "";
						$oBadgeIn->aspect = "link";
						$oBadgeIn->class = "icon ico-badgein";
						$oBadgeIn->label = ffTemplate::_get_word_by_code("sheet_badgein");
						$oBadgeIn->form_action_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify" . "?interval=" . $arrBadge_key . ($arrBadge_key == $first_badge[$ID_sheet_key] ? "&keys[ID]=" . $ID_sheet_key : "") . "&badgein=1&ret_url=" . urlencode($cm->oPage->getRequestUri());
						if($_REQUEST["XHR_DIALOG_ID"]) {
							$oBadgeIn->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'badgein', fields : [{name: 'sheet_page', value: '" . $page . "'}, {name: 'sheet_records_per_page', value: '" . $rec_per_page . "'}], 'url' : '[[frmAction_url]]'});";
						} else {
							$oBadgeIn->jsaction = "javascript:ff.ajax.doRequest({'action': 'badgein', fields : [{name: 'sheet_page', value: '" . $page . "'}, {name: 'sheet_records_per_page', value: '" . $rec_per_page . "'}], 'url' : '[[frmAction_url]]'});";
						}   
						$oBadgeIn->parent_page = array(&$cm->oPage);

						$oBadgeOut = ffField::factory($cm->oPage);
						$oBadgeOut->id = "badgeout"; 
						$oBadgeOut->class = "icon ico-badgeinactive";
						$oBadgeOut->control_type = "label";
						$oBadgeOut->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgeout_inactive"));
						$oBadgeOut->parent_page = array(&$cm->oPage);
					} elseif($arrBadge_value["in"]["time"] > 0 && (!$arrBadge_value["out"]["time"] > 0 || $arrBadge_key == $last_badge[$ID_sheet_key])) 
					{
						$disable_badge = true;

						$oBadgeIn = ffField::factory($cm->oPage);
						$oBadgeIn->id = "badgein"; 
						$oBadgeIn->class = "icon ico-badgeinactive";
						$oBadgeIn->control_type = "label";
						$oBadgeIn->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgein_started"));
						$oBadgeIn->parent_page = array(&$cm->oPage);

						$oBadgeOut = ffButton::factory($cm->oPage);
						$oBadgeOut->id = "badgeout"; 
						$oBadgeOut->action_type = "submit";
						$oBadgeOut->url = "";
						$oBadgeOut->aspect = "link";
						$oBadgeOut->class = "icon ico-badgeout";
						$oBadgeOut->label = ffTemplate::_get_word_by_code("sheet_badgeout");
						$oBadgeOut->form_action_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify" . "?interval=" . $arrBadge_key . ($arrBadge_key == $last_badge[$ID_sheet_key] ? "&keys[ID]=" . $ID_sheet_key : "") . "&badgeout=1&ret_url=" . urlencode($cm->oPage->getRequestUri());
						if($_REQUEST["XHR_DIALOG_ID"]) {
							$oBadgeOut->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'badgeout', fields : [{name: 'sheet_page', value: '" . $page . "'}, {name: 'sheet_records_per_page', value: '" . $rec_per_page . "'}], 'url' : '[[frmAction_url]]'});";
						} else {
							$oBadgeOut->jsaction = "javascript:ff.ajax.doRequest({'action': 'badgeout', fields : [{name: 'sheet_page', value: '" . $page . "'}, {name: 'sheet_records_per_page', value: '" . $rec_per_page . "'}], 'url' : '[[frmAction_url]]'});";
						}   
						$oBadgeOut->parent_page = array(&$cm->oPage);
					} else 
					{
						$oBadgeIn = ffField::factory($cm->oPage);
						$oBadgeIn->id = "badgein"; 
						$oBadgeIn->class = "icon ico-badgeinactive";
						$oBadgeIn->control_type = "label";
						if($arrBadge_value["in"]["time"] > 0) {
							$oBadgeIn->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgein_started"));
						} else {
							$oBadgeIn->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgein_inactive"));
						}
						$oBadgeIn->parent_page = array(&$cm->oPage);

						$oBadgeOut = ffField::factory($cm->oPage);
						$oBadgeOut->id = "badgeout"; 
						$oBadgeOut->class = "icon ico-badgeinactive";
						$oBadgeOut->control_type = "label";
						if($arrBadge_value["out"]["time"] > 0) {
							$oBadgeOut->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgeout_finished"));
						} else {
							$oBadgeOut->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgeout_inactive"));
						}
						$oBadgeOut->parent_page = array(&$cm->oPage);
					}
				} else {
					
					$oBadgeIn = ffField::factory($cm->oPage);
					$oBadgeIn->id = "badgein"; 
					$oBadgeIn->class = "icon ico-badgeinactive";
					$oBadgeIn->control_type = "label";
					if($arrBadge_value["in"]["time"] > 0) {
						$oBadgeIn->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgein_started"));
					} else {
						$oBadgeIn->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgein_inactive"));
					}
					$oBadgeIn->parent_page = array(&$cm->oPage);

					$oBadgeOut = ffField::factory($cm->oPage);
					$oBadgeOut->id = "badgeout"; 
					$oBadgeOut->class = "icon ico-badgeinactive";
					$oBadgeOut->control_type = "label";
					if($arrBadge_value["out"]["time"] > 0) {
						$oBadgeOut->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgeout_finished"));
					} else {
						$oBadgeOut->value = new ffData(ffTemplate::_get_word_by_code("sheet_badgeout_inactive"));
					}
					$oBadgeOut->parent_page = array(&$cm->oPage);
				}
				
				$tpl->set_var("badgein", $oBadgeIn->process());
				if($arrBadge_value["in"]["time"] > 0) 
				{
					$badgein_time = new ffData($arrBadge_value["in"]["time"], "Timestamp");
					$tpl->set_var("badgein_time", $badgein_time->getValue("Time", FF_LOCALE));
				} else 
				{
					$tpl->set_var("badgein_time", "");
				}
				
				$tpl->set_var("badgeout", $oBadgeOut->process());
				if($arrBadge_value["out"]["time"] > 0) 
				{
					$badgeout_time = new ffData($arrBadge_value["out"]["time"], "Timestamp");
					$tpl->set_var("badgeout_time", $badgeout_time->getValue("Time", FF_LOCALE));
				} else 
				{
					$tpl->set_var("badgeout_time", "");
				}
				$tpl->parse("SezInterval", true);
			}
			
		}
		
		$tpl->set_var("badgein", $oBadgeIn->process());
		if($badgein > 0)
			$tpl->set_var("badgein_time", $badgein_time);
		
		$tpl->set_var("badgeout", $oBadgeOut->process());
		if($badgeout > 0)
			$tpl->set_var("badgeout_time", $badgeout_time);
		
		$cm->oPage->widgetLoad("dialog");
		$cm->oPage->widgets["dialog"]->process(
			 "sheetModify" . $ID_sheet_key
			 , array(
				"tpl_id" => ""
				//"name" => "myTitle"
				, "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/modify" . "?keys[ID]=" . $ID_sheet_key . "&ret_url=" . urlencode($cm->oPage->getRequestUri())
				, "title" => ffTemplate::_get_word_by_code("sheet_modify")
				, "callback" => ""
				, "class" => ""
				, "params" => array()
			)
			, $cm->oPage
		);

		$oSheetCurrent = ffButton::factory($cm->oPage);
		$oSheetCurrent->id = "sheetModify" . $ID_sheet_key; 
		$oSheetCurrent->action_type = "submit";
		$oSheetCurrent->url = "";
		$oSheetCurrent->aspect = "link";
		$oSheetCurrent->class = "icon ico-edit";
		$oSheetCurrent->label = ffTemplate::_get_word_by_code("sheet_modify_pre") . " " . $ID_sheet_value["office"] . " " . ffTemplate::_get_word_by_code("sheet_modify_post");
		$oSheetCurrent->jsaction = "ff.ffPage.dialog.doOpen('sheetModify" . $ID_sheet_key . "')";
		$oSheetCurrent->parent_page = array(&$cm->oPage);

		$tpl->set_var("sheet_modify", $oSheetCurrent->process());
		$tpl->parse("SezTodayWork", true);
	}
	$cm->oPage->addContent($tpl->rpparse("main", false));
}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE
	|| check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE
) {
	$cm->oPage->addContent(null, true, "rel");
}
/*
if($_REQUEST["frmAction"] == "sheet_export") {
    $oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_xls"));
} else {
	$oGrid = ffGrid::factory($cm->oPage);
}*/


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "sheet";
$oGrid->title = ffTemplate::_get_word_by_code("sheet_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_attendance_sheet.*
                            , IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet.day = CURDATE(), 1, 0) AS current_day
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
	                        , GROUP_CONCAT(DISTINCT CONCAT(
	                        		(SELECT " . CM_TABLE_PREFIX . "mod_attendance_type.name FROM " . CM_TABLE_PREFIX . "mod_attendance_type WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_type )
									, '###'
	                        		, IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from = '00:00:00' 
	                        			AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to = '00:00:00' 
	                        			, ''
	                        			, CONCAT(
	                        				DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '%H:%i')
	                        				, ' / '
	                        				, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '%H:%i')
	                        			)
	                        		)
	                        	)
								ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from
	                        	SEPARATOR '@@@'
	                        ) AS `interval_default`
	                        , GROUP_CONCAT(DISTINCT CONCAT(
	                        		(	
	                        			SELECT " . CM_TABLE_PREFIX . "mod_attendance_type.name 
	                        			FROM " . CM_TABLE_PREFIX . "mod_attendance_type 
	                        			WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_type 
	                        		)
									, '###'
	                        		, IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from = '00:00:00' 
	                        			AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_to = '00:00:00' 
	                        			, ''
	                        			, CONCAT(
	                        				DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from, '%H:%i')
	                        				, ' / '
	                        				, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_to, '%H:%i')
	                        			)
	                        		)
	                        	)
								ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from
	                        	SEPARATOR '@@@'
	                        ) AS `interval`
		                    , GROUP_CONCAT(DISTINCT CONCAT(
									IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '0')
									, '###'
									, IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '0')
	                        	)
	                        	ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from
	                        	SEPARATOR '@@@' 
		                    ) AS `badge_default`
		                    , GROUP_CONCAT(DISTINCT CONCAT(
									IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from, '0')
									, '###'
									, IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgein, '0')
									, '###'
									, IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_to, '0')
									, '###'
									, IFNULL(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgeout, '0')
	                        	)
	                        	ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.time_from
	                        	SEPARATOR '@@@' 
		                    ) AS `badge`
	                        , (SELECT " . CM_TABLE_PREFIX . "mod_attendance_office.name 
	                        	FROM " . CM_TABLE_PREFIX . "mod_attendance_office 
	                        	WHERE " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office 
	                        	ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_office.name
	                        ) AS office
							$sSQL_field
							$sSQL_field_anagraph 
	                        , '' AS `status`
	                        , (SELECT GROUP_CONCAT(" . CM_TABLE_PREFIX . "mod_attendance_office_employee.role) 
	                        	FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
	                        	WHERE " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user
	                        		AND " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office
	                        ) AS role
                        FROM
                            " . CM_TABLE_PREFIX . "mod_attendance_sheet
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                        WHERE 1
                            $sSQL_permission
                        [AND] [WHERE] 
                        GROUP BY " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "day";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "SheetModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = $display_addnew;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = $display_delete;
if(check_function("system_ffgrid_process_customize_field_button"))
	system_ffgrid_process_customize_field_button($oGrid, "mod_attendance_type", null, array(
																						"value" => "enable_sheet_grid"
																				)
																			);
$oGrid->addEvent("on_before_parse_row", "sheet_on_before_parse_row"); 
//$oGrid->buttons_options["export"]["display"] = true;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "day";
//$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("sheet_date");
$oField->base_type = "date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->order_dir = "ASC";
if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE) {
	//$oField->order_SQL = "day [ORDER_DIR] ";
} elseif(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	//$oField->order_SQL = "current_day DESC, day [ORDER_DIR] ";
}
$oGrid->addContent($oField);

if(check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "anagraph";
	$oField->container_class = "user";
	$oField->label = ffTemplate::_get_word_by_code("sheet_user");
	$oGrid->addContent($oField);
	
	if(is_array($arrField_anagraph) && count($arrField_anagraph)) {
		foreach($arrField_anagraph AS $arrField_anagraph_key => $arrField_anagraph_value) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = $arrField_anagraph_value["smart_url"];
			$oField->container_class = ffCommon_url_rewrite($arrField_anagraph_value["smart_url"]);
			$oField->label = $arrField_anagraph_value["label"]; //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
			$oGrid->addContent($oField); 
		} 
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "office";
	$oField->container_class = "office";
	$oField->label = ffTemplate::_get_word_by_code("sheet_office");
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "role";
	$oField->container_class = "role";
	$oField->label = ffTemplate::_get_word_by_code("sheet_role");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
	
}

//if(check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "interval_default";
	$oField->container_class = "interval-default";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_default");
	$oField->encode_entities = false;
	$oGrid->addContent($oField); 
//}

$oField = ffField::factory($cm->oPage);
$oField->id = "interval";
$oField->container_class = "interval";
$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval");
$oField->encode_entities = false;
$oGrid->addContent($oField); 


if(is_array($arrField) && count($arrField)) {
	foreach($arrField AS $arrField_key => $arrField_value) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = $arrField_value["smart_url"];
		$oField->container_class = ffCommon_url_rewrite($arrField_value["smart_url"]);
		$oField->label = $arrField_value["label"]; //ffTemplate::_get_word_by_code("sheet_modify_" . $arrField_value);
		$oGrid->addContent($oField); 
	} 
}


$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("sheet_description");
$oField->encode_entities = false;
$oGrid->addContent($oField);

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "note";
	$oField->container_class = "note";
	$oField->label = ffTemplate::_get_word_by_code("sheet_note_admin");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
}

if(1 || check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_OFFICE) {
//	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE)
//		$oGrid->open_adv_search = true;

	/*if(!isset($_REQUEST["sheet_data_ins_from_src"])) {
		$_REQUEST["sheet_data_ins_from_src"] = date("d-m-Y", time());
	}*/
	//$_REQUEST["sheet_page"] = floor($num_day / $rec_per_page);
	//die($num_day . "asd");
	//print_r($_REQUEST);
	if(!$cm->oPage->isXHR()) {
		if($page > 0) {
			$oGrid->default_page = $page;
		}
	} else {
		$oGrid->default_page = $_REQUEST["sheet_page"];
	}
	if(!isset($_REQUEST["frmAction"])) {
	//	$_REQUEST["sheet_data_ins_from_src"] = date("d/m/Y", time()); 
	}
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "data_ins";
	$oField->data_source = "day";
	$oField->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet";
	$oField->base_type = "Date";
	$oField->label = ffTemplate::_get_word_by_code("sheet_day_label");
	$oField->widget = "datepicker";
	$oField->interval_from_label = ffTemplate::_get_word_by_code("sheet_day_from");
	$oField->interval_to_label = ffTemplate::_get_word_by_code("sheet_day_to");
	$oField->src_interval = true;
	$oField->src_operation = "DATE([NAME])";
	//$oField->default_value = new ffData(date("Y-m-d", time()), "Date", FF_SYSTEM_LOCALE);
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "interval";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_type");
	$oField->extended_type = "Selection";
	$oField->src_operation = "[NAME] LIKE [VALUE]";
	$oField->src_prefix = "%";
	$oField->src_postfix = "%";
	$oField->source_SQL = "SELECT name, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_type
							WHERE 1
							ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
								, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
	$oField->src_having = true;
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_office";
	$oField->container_class = "office";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("sheet_office");
	$oField->source_SQL = "SELECT ID, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_office
							WHERE 1 
							" . ((check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE)
								? " AND " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner IN ( SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " )"
								: ""
							) . " 
							ORDER BY name";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->actex_child = array("ID_user", "role");
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user";
	$oField->label = ffTemplate::_get_word_by_code("sheet_user");
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT
					        anagraph.ID
                            , " . (check_function("get_user_data")
                                ? get_user_data("Fname", "anagraph", null, false)
                                : "''"
                            ) . " AS Fname
		                    ,  " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office AS ID_office
					    FROM anagraph
							INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = anagraph.ID
					    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						FROM " . CM_TABLE_PREFIX . "mod_security_users
					    							INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    							INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    						WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_EMPLOYEE) . "
					    					)
						" . ((check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE)
							? " AND anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user 
														FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
															INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office 
														WHERE " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner IN ( SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " )
													)"
							: ""
						) . " 
						[AND] [WHERE]
					    GROUP BY anagraph.ID
					    ORDER BY Fname";
	$oField->widget = "activecomboex";
	$oField->actex_father = "ID_office";
	$oField->actex_related_field = "ID_office";
	$oField->actex_update_from_db = true;
	$oField->actex_skip_empty = true;
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
	$oGrid->addSearchField($oField);
	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "role";
	$oField->label = ffTemplate::_get_word_by_code("sheet_role");
	$oField->source_SQL = "SELECT 
								" . CM_TABLE_PREFIX . "mod_attendance_office_employee.role AS ID 
						        , " . CM_TABLE_PREFIX . "mod_attendance_office_employee.role AS Fname
						        , " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
					        FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
							WHERE 1
								[AND] [WHERE]
							GROUP BY " . CM_TABLE_PREFIX . "mod_attendance_office_employee.role
						    ORDER BY Fname";
	$oField->widget = "activecomboex";
	$oField->actex_father = "ID_office";
	$oField->actex_related_field = "ID_office";
	$oField->actex_update_from_db = true;
	$oField->actex_skip_empty = true;
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
	$oField->src_having = true;
	$oField->src_operation = "[NAME] LIKE [VALUE]";
	$oField->src_prefix = "%";
	$oField->src_postfix = "%";
	$oGrid->addSearchField($oField);

}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "status";
	$oField->container_class = "status";
	$oField->label = ffTemplate::_get_word_by_code("sheet_status");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
}

if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE) {
	$cm->oPage->addContent($oGrid);
} else {
	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("sheet_title"))); 

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "SheetRequest";
	$oGrid->title = ffTemplate::_get_word_by_code("sheet_request_title");
	$oGrid->source_SQL = "SELECT
	                            " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.*
	                            , IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_to = '0000-00-00'
                            		, " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_since 
                            		, " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_to
	                            ) AS date_to
	                            , CONCAT(
                            		IF(" . CM_TABLE_PREFIX . "mod_attendance_type.um = 'd'
                            			, ''
										, IF(ISNULL(employee.ID)
											, ''
											, CONCAT(
	                        					'<span class=\"request-employee\">'
	                        					, IF(employee.uid > 0
						                            , IF(employee.billreference = ''
						                                , IF(CONCAT(employee.name, '', employee.surname) <> ''
															, CONCAT(employee.name, ' ', employee.surname)
                                							, IF(account_employee.username = '', account_employee.email, account_employee.username)
						                                )
														, CONCAT(employee.name, ' ', employee.surname)
						                            )
						                            , IF(employee.billreference = ''
                            							, CONCAT(employee.name, ' ', employee.surname)
                            							, employee.billreference
						                            )
						                        )
						                        , '</span>'
	                        				)
	                        				
	                        			)
									)
	                        		, '<div class=\"attendance-note\">'
	                        		, " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.note
	                        		, '</div>'
	                            ) AS `range`
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
		                        , GROUP_CONCAT(DISTINCT CONCAT(
	                        			IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from = '00:00:00' 
	                        				AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to = '00:00:00' 
	                        				, ''
	                        				, CONCAT(
	                        					DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from, '%H:%i')
	                        					, ' / '
	                        					, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to, '%H:%i')
	                        				)
	                        			)
	                        		)
									ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from
	                        		SEPARATOR '@@@'
		                        ) AS `interval`
	                        FROM
	                            " . CM_TABLE_PREFIX . "mod_attendance_sheet_request
	                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_user
	                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid

	                            LEFT JOIN anagraph AS employee ON employee.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_employee
	                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users AS account_employee ON account_employee.ID = employee.uid
	                            INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_type ON " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_type
	                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID
	                        WHERE 1
                        		$sSQL_permission_request
	                        [AND] [WHERE] 
	                        GROUP BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID
	                        [HAVING]
	                        [ORDER]";

	$oGrid->order_default = "date_since";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/request/modify";
	$oGrid->record_id = "SheetRequestModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->display_edit_bt = false;

	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
		$oGrid->display_new = true;
		$oGrid->display_edit_url = true;
		$oGrid->display_delete_bt = true;
	} else {
		$oGrid->display_new = false;
		$oGrid->display_edit_url = false;
		$oGrid->display_delete_bt = false;
	}
	$oGrid->addEvent("on_before_parse_row", "SheetRequest_on_before_parse_row");


	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi di ricerca

	// Campi visualizzati
	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "anagraph";
		$oField->container_class = "user";
		$oField->label = ffTemplate::_get_word_by_code("sheet_request_user");
		$oGrid->addContent($oField);
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_type");
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT ID, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_type
							WHERE 1
							ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
								, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "date_since";
	$oField->container_class = "date-since";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_date_since");
	$oField->base_type = "date";
	$oField->extended_type = "Date";
	$oField->app_type = "Date";
	$oField->order_dir = "DESC";
	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oField->order_SQL = "status, date_since [ORDER_DIR]";
	} else {
		//$oField->order_SQL = "date_since [ORDER_DIR]";
	}
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "date_to";
	$oField->container_class = "date-to";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_date_to");
	$oField->base_type = "date";
	$oField->extended_type = "Date";
	$oField->app_type = "Date";
	$oField->order_dir = "DESC";
	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oField->order_SQL = "status, date_to [ORDER_DIR]";
	} else {
		//$oField->order_SQL = "date_since [ORDER_DIR]";
	}
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "interval";
	$oField->container_class = "interval";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_interval");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "range";
	$oField->container_class = "range";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_range");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);

	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_EMPLOYEE) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "status";
		$oField->container_class = "status";
		$oField->label = ffTemplate::_get_word_by_code("sheet_request_status");
		$oGrid->addContent($oField);
	}

	if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "approve"; 
		$oButton->action_type = "submit";
		$oButton->url = "";
		$oButton->aspect = "link";
		//$oButton->template_file = "ffButton_link_fixed.html";                           
		$oGrid->addGridButton($oButton);

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "discard"; 
		$oButton->action_type = "submit";
		$oButton->url = "";
		$oButton->aspect = "link";
		//$oButton->template_file = "ffButton_link_fixed.html";                           
		$oGrid->addGridButton($oButton);

	}

	//$cm->oPage->addContent($oGrid);
	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("sheet_request"))); 
}

function sheet_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
    /*
    if(isset($component->grid_buttons["badge"])) {
    	if($component->db[0]->getField("current_day", "Number", true)) {
    		$component->row_class = "badge";
    		if($component->db[0]->getField("badgein", "Number", true)) {
    			$component->grid_buttons["badge"]->class = "icon ico-badgeout";
    			$component->grid_buttons["badge"]->label = ffTemplate::_get_word_by_code("sheet_badgeout");
    			
	            $component->grid_buttons["badge"]->form_action_url = $component->grid_buttons["badge"]->parent[0]->record_url . "?[KEYS]" . "badgeout=1&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["badge"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'badgeout', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["badge"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'badgeout', fields: [], 'url' : '[[frmAction_url]]'});";
	            }   
			} else {
				$component->grid_buttons["badge"]->class = "icon ico-badgein";
				$component->grid_buttons["badge"]->label = ffTemplate::_get_word_by_code("sheet_badgein");

	            $component->grid_buttons["badge"]->form_action_url = $component->grid_buttons["badge"]->parent[0]->record_url . "?[KEYS]" . "badgein=1&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["badge"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'badgein', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["badge"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'badgein', fields: [], 'url' : '[[frmAction_url]]'});";
	            }   
			}
    		$component->grid_buttons["badge"]->visible = true;
		} else {
			$component->row_class = "nobadge";
			$component->grid_buttons["badge"]->visible = false;
		}
	}
	*/

	if(isset($component->grid_fields["interval_default"])) {
		$interval = array();
		$tmp_interval = $component->db[0]->getField("interval_default", "Text", true);
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
		$first_time_interval_default = "";
		if(is_array($interval) && count($interval)) {
			foreach($interval AS $interval_key => $interval_value) {
				if(is_array($interval_value) && count($interval_value)) {
					$interval_detail = "";
					foreach($interval_value AS $interval_value_detail) {
						if(strlen($interval_value_detail)) {
							if(!strlen($first_time_interval_default)) {
								$arrFirstTime = explode(" / ", $interval_value_detail);
								if(strlen($arrFirstTime[0]))
									$first_time_interval_default = $arrFirstTime[0];
							}
							$interval_detail .= '<div class="interval-row">' . $interval_value_detail . '</div>'; 
						}
					}
				}
				
				$str_interval .= '<label class="' . ffCommon_url_rewrite($interval_key) .'">' . $interval_key . '</label>' . $interval_detail;
				
			}
		}
		$component->grid_fields["interval_default"]->setValue($str_interval);
    }
    
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
				
				$str_interval .= '<label "' . ffCommon_url_rewrite($interval_key) .'">' . $interval_key . '</label>' . $interval_detail;
	
			}
			
		}
		$component->grid_fields["interval"]->setValue($str_interval);
    }
    
    $component->row_class = "";
    if(isset($component->grid_fields["status"])) {
    	if($component->db[0]->getField("day", "Date")->getValue("Timestamp", FF_SYSTEM_LOCALE) - time() > 0) {
			$badge_status = "";
			$str_badge = "";
    	} else {
    		$badge_default = array();
    		$badge = array();
			
			$tmp_badge_default = $component->db[0]->getField("badge_default", "Text", true);
			if(strlen($tmp_badge_default)) {
				$arrBadgeDefault = explode("@@@", $tmp_badge_default);
				if(is_array($arrBadgeDefault) && count($arrBadgeDefault)) {
					foreach($arrBadgeDefault AS $arrBadgeDefault_key => $arrBadgeDefault_value) {
						if(strlen($arrBadgeDefault_value)) {
							$arrBadgeDefaultDetail = explode("###", $arrBadgeDefault_value);
							$badge[$arrBadgeDefault_key]["in"]["default"] = $arrBadgeDefaultDetail[0]; 
							$badge[$arrBadgeDefault_key]["out"]["default"] = $arrBadgeDefaultDetail[1]; 
						}
					}
				}
			}

			$tmp_badge = $component->db[0]->getField("badge", "Text", true);
			if(strlen($tmp_badge)) {
				$arrBadge = explode("@@@", $tmp_badge);
				if(is_array($arrBadge) && count($arrBadge)) {
					foreach($arrBadge AS $arrBadge_key => $arrBadge_value) {
						if(strlen($arrBadge_value)) {
							$arrBadgeDetail = explode("###", $arrBadge_value);
							$badge[$arrBadge_key]["in"]["real"] = $arrBadgeDetail[0]; 
							$badge[$arrBadge_key]["in"]["time"] = $arrBadgeDetail[1]; 
							$badge[$arrBadge_key]["out"]["real"] = $arrBadgeDetail[2]; 
							$badge[$arrBadge_key]["out"]["time"] = $arrBadgeDetail[3]; 
						}
					}
					if($badge[0]["in"]["time"] == 0 &&  $badge[0]["out"]["time"] == 0) {
						$badge[0]["in"]["time"] = $component->db[0]->getField("badgein", "Number", true);
						$badge[0]["out"]["time"] = $component->db[0]->getField("badgeout", "Number", true);
					}
				}
			}

			if(is_array($badge) && count($badge)) {
				$str_badge_row = "";
				foreach($badge AS $badge_value) {
    				if($badge_value["in"]["time"] > 0) {
						$badgein = new ffData($badge_value["in"]["time"], "Timestamp");
						if(strlen($badge_value["in"]["default"])) {
							$badge_diff_default = strtotime($badge_value["in"]["default"]) - strtotime($badgein->getValue("Time", FF_LOCALE));
							if(strtotime($badgein->getValue("Time", FF_LOCALE)) - strtotime($badge_value["in"]["default"]) > 900) {
								if(strlen($badge_value["in"]["real"])) {
									$badge_diff = strtotime($badgein->getValue("Time", FF_LOCALE)) - strtotime($badge_value["in"]["real"]);
									if(strtotime($badgein->getValue("Time", FF_LOCALE)) - strtotime($badge_value["in"]["real"]) > 900) {
										$badge_status = "alert";
									} else {
										$badge_status = "twarning";
									}
								} else {
									$badge_status = "alert";	
								}
							} else {
								$badge_status = "ok";
							}
						} else {
							$badge_status = "notset";
						}
						$str_badge = $badgein->getValue("Time", FF_LOCALE);
					} else {
						if(strlen($badge_value["in"]["default"])) {
							$badge_status = "twarning";
						} else {
							$badge_status = "notset";
						}
						$str_badge = ffTemplate::_get_word_by_code("badgein_not_set");
					}

					if($badge_value["out"]["time"] > 0) {
						$badgeout = new ffData($badge_value["out"]["time"], "Timestamp");
						
						$str_badge .= " / " . $badgeout->getValue("Time", FF_LOCALE);
					} else {
						$str_badge .= " / " . ffTemplate::_get_word_by_code("badgeout_not_set");
					}
					//echo $badge_diff_default . "<br>";

					if(strlen($str_badge)) {
						if(count($badge) > 1) {
							$str_badge_row .= '<tr class="' . $badge_status . '">';
							$str_badge_row .= '<td class="badge-status">' . $str_badge . '</td>';
						} else {
							$str_badge_row .= '<div class="' . $badge_status . '">';
							$str_badge_row .= '<span class="badge-status">' . $str_badge . '</span>';
						}
						
						if(MOD_ATTENDANCE_SHOW_BADGE_DETAIL) {
							if(!strlen($badge_diff_default) && !strlen($badge_diff)) {
							} else {
								if(strlen($badge_diff_default)) {
									if($badge_diff_default < 0)
										$badge_default_sign = "-";
									else 
										$badge_default_sign = "";

									$badge_diff_default_hour = floor(abs($badge_diff_default) / 3600); 
									$badge_diff_default_min = ceil((abs($badge_diff_default) - (3600 * $badge_diff_default_hour)) / 60);

									$str_badge_diff_default = date("H:i", mktime($badge_diff_default_hour, $badge_diff_default_min));

									$str_badge_diff_default = $badge_default_sign . $str_badge_diff_default;
								} else {
									$str_badge_diff_default = ffTemplate::_get_word_by_code("sheet_badge_na");
								}
								if(strlen($badge_diff)) {
									if($badge_diff_default < 0)
										$badge_default_sign = "-";
									else 
										$badge_default_sign = "";

									$badge_diff_hour = floor(abs($badge_diff) / 3600); 
									$badge_diff_min = ceil((abs($badge_diff) - (3600 * $badge_diff_hour)) / 60);
									$str_badge_diff = date("H:i", mktime($badge_diff_hour, $badge_diff_min));

									$str_badge_diff = $badge_sign . $str_badge_diff;
								} else {
									$str_badge_diff = ffTemplate::_get_word_by_code("sheet_badge_na");
								}
								if(count($badge) > 1) {
									$str_badge_row .= '<td>' . $str_badge_diff_default . '</td><td>' . $str_badge_diff .  '</td>';
								} else {
									$str_badge_row .= '<span>' . $str_badge_diff_default . '</span><span>' . $str_badge_diff .  '</span>';
								}
							}
						}							
						if(count($badge) > 1) {
							$str_badge_row .= '</tr>';
						} else {
							$str_badge_row .= '</div>';
						}
						
					}
				}
				
				if(strlen($str_badge_row)) {
					if(count($badge) > 1) {
						$badge_container = "
							<table>

								<tbody>
									$str_badge_row
								</tbody>
							</table>";
					} else {
						$badge_container = $str_badge_row;
					}
				} else {
					$badge_container = "";
				}

				$component->grid_fields["status"]->setValue($badge_container);
			}
		}
    		/*
    		if($component->db[0]->getField("badgein", "Number", true) > 0) {
				$badgein = $component->db[0]->getField("badgein", "Timestamp");
				if(strlen($first_time_interval_default)) {
					if(strtotime($badgein->getValue("Time", FF_LOCALE)) - strtotime($first_time_interval_default) > 900) {
						if(strlen($first_time_interval)) {
							if(strtotime($badgein->getValue("Time", FF_LOCALE)) - strtotime($first_time_interval) > 900) {
								$badge_status = "alert";
							} else {
								$badge_status = "twarning";
							}
						} else {
							$badge_status = "alert";	
						}
					} else {
						$badge_status = "ok";
					}
				} else {
					$badge_status = "";
				}
				$str_badge = $badgein->getValue("Time", FF_LOCALE);
			} else {
				if(strlen($first_time_interval_default)) {
					$badge_status = "twarning";
				} else {
					$badge_status = "";
				}
				$str_badge = ffTemplate::_get_word_by_code("badgein_not_set");
			}

			if($component->db[0]->getField("badgeout", "Number", true) > 0) {
				$badgeout = $component->db[0]->getField("badgeout", "Timestamp");
				
				$str_badge .= " / " . $badgeout->getValue("Time", FF_LOCALE);
			} else {
				$str_badge .= " / " . ffTemplate::_get_word_by_code("badgeout_not_set");
			}

		}		

//		echo $first_time_interval_default . "  " . strtotime($first_time_interval_default) . "  " . strtotime($badgein->getValue("Time", FF_LOCALE)) . "  " . $badgein->getValue("Time", FF_LOCALE) .  "  " . $component->db[0]->getField("badgein", "Number", true)	 . "<br>";
    	$component->grid_fields["status"]->setValue("");

		switch($badge_status) {
			case "ok":
				$component->grid_fields["status"]->container_class = "status-ok";
				$component->grid_fields["status"]->setValue($str_badge);
				$component->row_class = "green";
				break;
			case "twarning":
				$component->grid_fields["status"]->container_class = "status-warning";
				$component->grid_fields["status"]->setValue($str_badge);
	            $component->row_class = "yellow";
				break;
			case "alert":
				$component->grid_fields["status"]->container_class = "status-alert";
    			$component->grid_fields["status"]->setValue($str_badge);
	            $component->row_class = "red";
				break;
			default:
				$component->grid_fields["status"]->container_class = "";
				$component->grid_fields["status"]->setValue($str_badge);
				$component->row_class = "";
		}*/
	}
	
	if($component->db[0]->getField("day", "Date")->getValue("Date", FF_SYSTEM_LOCALE) == date("Y-m-d", time())) {
		$component->row_class = (strlen($component->row_class) ? $component->row_class . " " : "") . "current";
	}

}

function SheetRequest_on_before_parse_row($component) {
    
    if(isset($component->grid_fields["interval"])) {
    	$interval = array();
		$tmp_interval = $component->db[0]->getField("interval", "Text", true);
		if(strlen($tmp_interval)) {
			$arrInterval = explode("@@@", $tmp_interval);
			if(is_array($arrInterval) && count($arrInterval)) {
				foreach($arrInterval AS $arrInterval_key => $arrInterval_value) {
					$interval[] = $arrInterval_value; 
				}
			}
		}
		
		$str_interval = "";
		$first_time_interval = "";
		if(is_array($interval) && count($interval)) {
			foreach($interval AS $interval_key => $interval_value) {
				if(strlen($interval_value)) {
					
					$str_interval .= '<div class="interval-row">' . $interval_value . '</div>';
				}
			}
		}
		$component->grid_fields["interval"]->setValue($str_interval);
    }
	
	
	
	if(isset($component->grid_fields["range"])) {
	}
	
	if(isset($component->grid_fields["status"])) {
    	$component->grid_fields["status"]->setValue("");
		
		switch($component->db[0]->getField("status", "Text", true)) {
			case "":
				$component->grid_fields["status"]->container_class = "status-waiting";
				$component->row_class = "green";
				break;
			case "0":
				$component->grid_fields["status"]->container_class = "status-discarded";
	            $component->row_class = "yellow";
				break;
			case "1":
				$component->grid_fields["status"]->container_class = "status-approved";
	            $component->row_class = "red";
				break;
			default:
		}
		
		
		if(strlen($component->db[0]->getField("status", "Text", true))) {
			$component->display_edit_bt = false;
			$component->display_edit_url = false;
			$component->display_delete_bt = false;
		} else {
			$component->display_edit_url = true;
			$component->display_delete_bt = true;
		}
	}
    if(isset($component->grid_buttons["approve"])) {
    	if(strlen($component->db[0]->getField("status", "Text", true))) {
    		if($component->db[0]->getField("status", "Number", true) > 0) {
    			$component->grid_buttons["approve"]->class = "icon ico-approve";
			} else {
				$component->grid_buttons["approve"]->class = "icon ico-approve-inactive";
			}
		} else {
    		$component->grid_buttons["approve"]->class = "icon ico-approve";
		}

    	$component->grid_buttons["approve"]->label = ffTemplate::_get_word_by_code("sheet_request_approve");
		$component->grid_buttons["approve"]->form_action_url = $component->grid_buttons["approve"]->parent[0]->record_url . "?[KEYS]" . "status=1&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
		if($_REQUEST["XHR_DIALOG_ID"]) {
		    $component->grid_buttons["approve"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'status', fields: [], 'url' : '[[frmAction_url]]'});";
		} else {
		    $component->grid_buttons["approve"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'status', fields: [], 'url' : '[[frmAction_url]]'});";
		}   
		$component->grid_buttons["approve"]->template_file = "ffButton_link_fixed.html";
	}

    if(isset($component->grid_buttons["discard"])) {
    	if(strlen($component->db[0]->getField("status", "Text", true))) {
    		if($component->db[0]->getField("status", "Number", true) > 0) {
    			$component->grid_buttons["discard"]->class = "icon ico-discard-inactive";
			} else {
				$component->grid_buttons["discard"]->class = "icon ico-discard";
			}
		} else {
    		$component->grid_buttons["discard"]->class = "icon ico-discard";
    		$component->grid_buttons["discard"]->label = ffTemplate::_get_word_by_code("sheet_request_discard");
		}

		$component->grid_buttons["discard"]->form_action_url = $component->grid_buttons["discard"]->parent[0]->record_url . "?[KEYS]" . "status=0&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
		if($_REQUEST["XHR_DIALOG_ID"]) {
		    $component->grid_buttons["discard"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'status', fields: [], 'url' : '[[frmAction_url]]'});";
		} else {
		    $component->grid_buttons["discard"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'status', fields: [], 'url' : '[[frmAction_url]]'});";
		}   
		$component->grid_buttons["discard"]->template_file = "ffButton_link_fixed.html";
	}
}
?>