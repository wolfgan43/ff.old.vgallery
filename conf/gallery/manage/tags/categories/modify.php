<?php

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "TagsCategoriesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "search_tags_categories";


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tag_group_categories_name");
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord); 