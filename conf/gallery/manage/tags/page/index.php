<?php
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsPage";
$oGrid->source_SQL = "SELECT search_tags_page.* 
                        FROM search_tags_page 
                        WHERE ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
                        [AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "TagsPageModify";
$oGrid->resources[] = $oGrid->record_id;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_title";
$oField->label = ffTemplate::_get_word_by_code("tags_page_title");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);  