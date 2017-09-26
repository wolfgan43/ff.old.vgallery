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

switch(check_attendance_permission(true)) {
	case MOD_ATTENDANCE_GROUP_ATTENDANCE:
		$sSQL_permission = " ";
		$display_addnew = true;
		$display_delete = true;
		break;
	case MOD_ATTENDANCE_GROUP_OFFICE:
		$sSQL_permission = " AND " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_office IN ( SELECT " . CM_TABLE_PREFIX . "mod_attendance_office.ID FROM " . CM_TABLE_PREFIX . "mod_attendance_office WHERE " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner = (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " ))";
		$display_addnew = false;
		$display_delete = false;
		break;
	case MOD_ATTENDANCE_GROUP_EMPLOYEE:
		$sSQL_permission = " AND " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_user IN ( SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " )";
		$display_addnew = false;
		$display_delete = false;
		break;
	default:
		$sSQL_permission = " AND 0 ";
		$display_addnew = false;
		$display_delete = false;
	
}

//$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "photo";
$oGrid->title = ffTemplate::_get_word_by_code("photo_title");
$oGrid->source_SQL = "SELECT
							" . CM_TABLE_PREFIX . "mod_attendance_photo.`ID` AS `ID`
							, " . CM_TABLE_PREFIX . "mod_attendance_photo_detail.`path` AS `path`
							, " . CM_TABLE_PREFIX . "mod_attendance_photo_detail.`description` AS `description`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`date` AS `date`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_office` AS `ID_office`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_user` AS `ID_user`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_event` AS `ID_event`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_argument` AS `ID_argument`
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_detail` AS `ID_detail`
                            , " . CM_TABLE_PREFIX . "mod_attendance_office.name AS office_name
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo_event.name AS event_name
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo_argument.name AS argument_name
                            , " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail.name AS argument_detail_name
	                        , (IF(anagraph.uid > 0
	                            , IF(anagraph.billreference = ''
		                            , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                		, CONCAT(anagraph.name, ' ', anagraph.surname)
                                		, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
		                            )
                                	, anagraph.billreference
	                            )
	                            , IF(anagraph.billreference = ''
                            		, CONCAT(anagraph.name, ' ', anagraph.surname)
                            		, anagraph.billreference
	                            )
	                        )) AS anagraph
	                        , (SELECT GROUP_CONCAT(" . CM_TABLE_PREFIX . "mod_attendance_office_employee.role) 
	                        	FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
	                        	WHERE " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_user
	                        		AND " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office = " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_office
	                        ) AS role
                        FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_detail
							INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_photo ON " . CM_TABLE_PREFIX . "mod_attendance_photo.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo_detail.ID_photo
							INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_office
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo.ID_user
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_photo_event ON " . CM_TABLE_PREFIX . "mod_attendance_photo_event.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_event`
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_photo_argument ON " . CM_TABLE_PREFIX . "mod_attendance_photo_argument.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_argument`
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail ON " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail.ID = " . CM_TABLE_PREFIX . "mod_attendance_photo.`ID_detail`
                        WHERE 1
                        	$sSQL_permission
                        [AND] [WHERE] 
                        GROUP BY " . CM_TABLE_PREFIX . "mod_attendance_photo_detail.`path`
                        [HAVING]
                        ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_office.name [COLON] [ORDER]";

$oGrid->order_default = "date";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PhotoModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = false;
$oGrid->addEvent("on_before_parse_row", "photo_on_before_parse_row");
$oGrid->addEvent("on_before_process_grid", "photo_on_before_process_grid");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca
if(check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	//if(check_attendance_permission(true) == MOD_ATTENDANCE_GROUP_OFFICE)
	//	$oGrid->open_adv_search = true;

	$oField = ffField::factory($cm->oPage);
	$oField->id = "data_ins";
	$oField->data_source = "date";
	$oField->src_table = CM_TABLE_PREFIX . "mod_attendance_photo";
	$oField->base_type = "Date";
	$oField->label = ffTemplate::_get_word_by_code("photo_date_label");
	$oField->widget = "datepicker";
	$oField->interval_from_label = ffTemplate::_get_word_by_code("photo_date_from");
	$oField->interval_to_label = ffTemplate::_get_word_by_code("photo_date_to");
	$oField->src_interval = true;
	$oField->src_operation = "DATE([NAME])";
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_office";
	$oField->container_class = "office";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_office");
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
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("attendance_no_office");
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user";
	$oField->label = ffTemplate::_get_word_by_code("photo_user");
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

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_event";
	$oField->container_class = "event";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_event");
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_event ORDER BY name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_event_not_set");
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oGrid->addSearchField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_argument";
	$oField->container_class = "argument";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_argument");
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument ORDER BY name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_argument_not_set");
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->src_having = true;
	$oField->actex_child = "ID_detail";
	$oGrid->addSearchField($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_detail";
	$oField->container_class = "detail";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_detail");
	$oField->source_SQL = "SELECT ID, name, ID_argument 
						FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail 
						[WHERE]
						ORDER BY name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_argument_detail_not_set");
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->actex_father = "ID_argument";
	$oField->actex_related_field = "ID_argument";
	$oGrid->addSearchField($oField);
}

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->container_class = "photo";
$oField->label = ffTemplate::_get_word_by_code("photo_path");
$oField->file_storing_path = DISK_UPDIR . "/attendance/[ID_office_VALUE]/[ID_user_VALUE]/[ID_event_VALUE][ID_argument_VALUE]";
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/attendance-photo[_FILENAME_]";
$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/attendance-photo[_FILENAME_]";
//$oField->control_type = "picture";
$oField->encode_entities = false;
//$oField->uploadify_model = $avatar_model;
//$oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("photo_date");
$oField->base_type = "date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_date") . "</label>";
$oGrid->addContent($oField);

if(check_attendance_permission(true) != MOD_ATTENDANCE_GROUP_EMPLOYEE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_office";
	$oField->container_class = "office";
	$oField->base_type = "Number";
	$oField->label = ffTemplate::_get_word_by_code("photo_office");
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_office ORDER BY name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("attendance_no_office");
	$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_office") . "</label>";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "anagraph";
	$oField->container_class = "user";
	$oField->label = ffTemplate::_get_word_by_code("photo_user");
	$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_user") . "</label>";
	$oGrid->addContent($oField);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "role";
	$oField->container_class = "role";
	$oField->label = ffTemplate::_get_word_by_code("photo_role"); 
	$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_role") . "</label>";
	$oGrid->addContent($oField);*/
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_event";
$oField->container_class = "event";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("photo_event");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_event ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_event_not_set");
$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_event") . "</label>";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_argument";
$oField->container_class = "argument";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("photo_argument");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_argument_not_set");
$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_argument") . "</label>";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_detail";
$oField->container_class = "detail";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("photo_detail");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("photo_detail_not_set");
$oField->fixed_pre_content = "<label>" . ffTemplate::_get_word_by_code("photo_detail") . "</label>";
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "download";
$oButton->class = "download";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/download";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("download");
$oGrid->addActionButtonHeader($oButton);

$cm->oPage->addContent($oGrid);

function photo_on_before_parse_row($component) {
	if(isset($component->grid_fields["path"])) {
		if(strlen($component->db[0]->getField("description", "Text", true))) {
			$description = '<div class="description">' . $component->db[0]->getField("description", "Text", true) . '</div>';	
		}

		// da sistemare seriamente
		if(is_file(DISK_UPDIR . $component->grid_fields["path"]->getValue())) {
			$image = '<a class="fancybox" href="' . str_replace("[_FILENAME_]", $component->grid_fields["path"]->getValue(), $component->grid_fields["path"]->file_saved_view_url) . '" rel="fancybox" title="' . $component->db[0]->getField("description", "Text", true) . '"><img src="' . str_replace("[_FILENAME_]", $component->grid_fields["path"]->getValue(), $component->grid_fields["path"]->file_saved_preview_url) . '" /></a>';
		} else {
			$image = '<a class="fancybox" href="' . "http://2.233.156.69/uploads" . $component->grid_fields["path"]->getValue() . '" rel="fancybox" title="' . $component->db[0]->getField("description", "Text", true) . '"><img src="' . "http://2.233.156.69/uploads" . $component->grid_fields["path"]->getValue() . '" /></a>';
		}

		$component->grid_fields["path"]->setValue($image);
	}
}

function photo_on_before_process_grid($oGrid, $oTpl = null) {
	set_session("mod_attendance_photo_sql", $oGrid->processed_SQL);

	
}
?>