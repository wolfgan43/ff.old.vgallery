<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_SCHEDULE_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

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
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
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
$oField->label = ffTemplate::_get_word_by_code("schedule_last_update");
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
$oField->base_type = "DateTime";
$oGrid->addContent($oField);


$cm->oPage->addContent($oGrid);

?>