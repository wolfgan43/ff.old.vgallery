<?php
$permission = check_attendance_permission();
if($permission !== true &&  !(is_array($permission) && count($permission))) {
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
$oGrid->id = "SheetRequest";
$oGrid->title = ffTemplate::_get_word_by_code("sheet_request_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.*
                            , IF(" . CM_TABLE_PREFIX . "mod_attendance_type.um = 'd'
                            	, ''
								, CONCAT(
	                        		" . CM_TABLE_PREFIX . "mod_attendance_sheet_request.time_from
	                        		, ' / '
	                        		, " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.time_to
	                        	)
                            ) AS `range`
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
                        FROM
                            " . CM_TABLE_PREFIX . "mod_attendance_sheet_request
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_user
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                            INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_type ON " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_type
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "date_since";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "SheetRequestModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "SheetRequest_on_before_parse_row");


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "date_since";
$oField->container_class = "date-since";
$oField->label = ffTemplate::_get_word_by_code("sheet_request_date_since");
$oField->base_type = "date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "range";
$oField->container_class = "range";
$oField->label = ffTemplate::_get_word_by_code("sheet_request_range");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anagraph";
$oField->container_class = "user";
$oField->label = ffTemplate::_get_word_by_code("sheet_request_user");
$oGrid->addContent($oField);

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
$oField->id = "status";
$oField->container_class = "status";
$oField->label = ffTemplate::_get_word_by_code("sheet_request_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	                        array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("sheet_request_waiting")))
	                        , array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("sheet_request_approved")))
	                        , array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("sheet_request_discarded")))
	                   );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("sheet_search_select_all");
$oGrid->addContent($oField);


$cm->oPage->addContent($oGrid);

function SheetRequest_on_before_parse_row($component) {
	if(isset($component->grid_fields["range"])) {
		if(!strlen($component->db[0]->getField("range", "Text", true))) {
			$date_to = $component->db[0]->getField("date_to", "Date");
			$component->grid_fields["range"]->setValue($date_to->getValue("Date", FF_LOCALE));
		}
	}
}
?>
