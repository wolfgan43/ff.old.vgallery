<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

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
