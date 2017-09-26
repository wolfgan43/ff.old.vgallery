<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

/*$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));

if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffGrid.html")) {
	$oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
}*/
$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_report_type.* 
		FROM " . CM_TABLE_PREFIX . "mod_attendance_report_type 
		WHERE 
	        " . (check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE
	            ? " 1 "
	            :"	(" . CM_TABLE_PREFIX . "mod_attendance_report_type.limit_by_groups = '' 
	                    OR FIND_IN_SET((SELECT " . CM_TABLE_PREFIX . "mod_security_groups.gid FROM " . CM_TABLE_PREFIX . "mod_security_groups WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(check_attendance_permission(true)) . "), " . CM_TABLE_PREFIX . "mod_attendance_report_type.limit_by_groups)
		            )"
		    ) . "
		ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_report_type.name";
$db->query($sSQL);
if($db->nextRecord()) {
	if($db->numRows() > 1) {
		$cm->oPage->addContent(null, true, "rel");
	}
	do {
		$ID_category = $db->getField("ID", "Number", true);
		$category = $db->getField("name", "Text", true);
		
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "report" . preg_replace('/[^a-zA-Z0-9]/', '', $category);
		$oGrid->title = ffTemplate::_get_word_by_code("report_title");
		$oGrid->source_SQL = "SELECT
		                            " . CM_TABLE_PREFIX . "mod_attendance_report.*
		                            , " . CM_TABLE_PREFIX . "mod_attendance_report_type.name AS type_name
		                        FROM
		                            " . CM_TABLE_PREFIX . "mod_attendance_report
		                            INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_report_type ON " . CM_TABLE_PREFIX . "mod_attendance_report_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_report.ID_type
		                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_report.ID_type = " . $db->toSql($ID_category, "Number") . "
		                        [AND] [WHERE] 
		                        [HAVING]
		                        [ORDER]";
		$oGrid->order_default = "title";
		$oGrid->use_search = true;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
		$oGrid->record_id = "ReportModify";
		$oGrid->resources[] = $oGrid->record_id;
		if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_ATTENDANCE) {
			$oGrid->display_new = true;
			$oGrid->display_edit_bt = false;
			$oGrid->display_edit_url = true;
			$oGrid->display_delete_bt = true;
		} else {
			$oGrid->display_new = false;
			$oGrid->display_edit_bt = false;
			$oGrid->display_edit_url = false;
			$oGrid->display_delete_bt = false;
		}
		$oGrid->addEvent("on_before_parse_row", "report_on_before_parse_row");

		// Campi chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Campi di ricerca

		// Campi visualizzati
		$oField = ffField::factory($cm->oPage);
		$oField->id = "title";
		$oField->label = ffTemplate::_get_word_by_code("report_title");
		$oField->encode_entities = false;
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "type_name";
		$oField->label = ffTemplate::_get_word_by_code("report_type_name");
		$oField->encode_entities = false;
		$oGrid->addContent($oField);

		if($db->numRows() > 1) {
			$cm->oPage->addContent($oGrid, "rel", null, array("title" => $category));
		} else {
			$cm->oPage->addContent($oGrid);
		}
	} while($db->nextRecord());
}


function report_on_before_parse_row($component) {
	if(isset($component->grid_fields["title"])) {
		$link = $component->db[0]->getField("link", "Text", true);
		if(substr(strtolower($link), 0, 7) != "http://" && substr(strtolower($link), 0, 8) != "https://") {
			$link = "http://" . $link;
		}
		
		$component->grid_fields["title"]->setValue('<a href="' . $link . '" target="_blank">' . $component->grid_fields["title"]->getValue() . '</a>');
	}
}
?>