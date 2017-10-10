<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_NOTIFY_SHOW_MODIFY || AREA_SCHEDULE_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "notifypanel";
$oGrid->title = ffTemplate::_get_word_by_code("notify_title");
$oGrid->source_SQL = "SELECT * FROM notify_message [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "last_update";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "NotifyModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "notify_on_before_parse_row");


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->label = ffTemplate::_get_word_by_code("notify_last_update");
$oField->base_type = "Timestamp";
$oField->extended_type = "DateTime";
$oField->app_type = "DateTime";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("notify_area");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("notify_type");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("notify_title");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->label = ffTemplate::_get_word_by_code("notify_message");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = FF_SITE_PATH . $cm->oPage->page_path . "/preview?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("notify_preview");
$oButton->template_file = "ffButton_link_image.html";                           
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->template_file = "ffButton_link_image.html";
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "clearAll";
$oButton->action_type = "gotourl";
$oButton->url = FF_SITE_PATH . "/srv/notify?frmAction=clearall&ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("notify_clear_all");
$oGrid->addActionButtonHeader($oButton);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("message"))); 

if(AREA_SCHEDULE_SHOW_MODIFY) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "schedule";
	$oGrid->title = ffTemplate::_get_word_by_code("schedule_title");
	$oGrid->source_SQL = "SELECT *
							, CONCAT('every ', period, ' day(s)', ' start by ', hour) AS description
							
							, CONCAT(
								TIMESTAMPADD(
									DAY 
									, `period`
									, FROM_UNIXTIME(`last_update`, '%Y-%m-%d')
								)
								, ' '
								, `hour`
							)
						 AS next_update 
							FROM notify_schedule [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "name";
	$oGrid->use_search = false;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/schedule/modify";
	$oGrid->record_id = "ScheduleModify";
	$oGrid->resources[] = $oGrid->record_id;

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi di ricerca

	// Campi visualizzati

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("schedule_name");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "area";
	$oField->label = ffTemplate::_get_word_by_code("schedule_area");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "job";
	$oField->label = ffTemplate::_get_word_by_code("schedule_job");
	$oField->data_info["field"] = "description";
	$oField->data_info["multilang"] = false;
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "last_update";
	$oField->label = ffTemplate::_get_word_by_code("schedule_update");
	$oField->base_type = "Timestamp";
	$oField->extended_type = "DateTime";
	$oField->app_type = "DateTime";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "next_update";
	$oField->label = ffTemplate::_get_word_by_code("schedule_next_update");
	$oField->base_type = "DateTime";
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "last_job";
	$oField->label = ffTemplate::_get_word_by_code("schedule_last_job");
	$oField->base_type = "Timestamp";
	$oField->extended_type = "DateTime";
	$oField->app_type = "DateTime";
	$oGrid->addContent($oField);

	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("schedule"))); 
}

function notify_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
	$sSQL = "SELECT * 
				FROM notify_message 
				WHERE notify_message.ID = " . $db->toSql($component->key_fields["ID"]->value);
	$db->query($sSQL);
	if($db->nextRecord()) {
		if($db->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->url = $component->parent[0]->site_path . $component->parent[0]->page_path . "/modify/?[KEYS]ret_url=" . urlencode($component->parent[0]->getRequestUri()) . "&NotifyModify_frmAction=hide";
			$component->grid_buttons["visible"]->label = ffTemplate::_get_word_by_code("notifies_visible");
		} else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
			$component->grid_buttons["visible"]->url = $component->parent[0]->site_path . $component->parent[0]->page_path . "/modify/?[KEYS]ret_url=" . urlencode($component->parent[0]->getRequestUri()) . "&NotifyModify_frmAction=show";
			$component->grid_buttons["visible"]->label = ffTemplate::_get_word_by_code("notifies_hidden");
		}
	} else {
        $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
        $component->grid_buttons["visible"]->icon = null;
		$component->grid_buttons["visible"]->url = $component->parent[0]->site_path . $component->parent[0]->page_path . "/modify/?[KEYS]ret_url=" . urlencode($component->parent[0]->getRequestUri()) . "&NotifyModify_frmAction=show";
		$component->grid_buttons["visible"]->label = ffTemplate::_get_word_by_code("notifies_hidden");
	}
}

?>