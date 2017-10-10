<?php

$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "CachePageSeoModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("cache_page_seo_modify"); 
$oRecord->src_table = "cache_page_seo";
$oRecord->buttons_options["print"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_name");
$oField->base_type = "Text";
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "importance";
$oField->label = ffTemplate::_get_word_by_code("cache_page_importance");
$oField->base_type = "Number";
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "resolution";
$oField->label = ffTemplate::_get_word_by_code("cache_page_resolution");
$oField->base_type = "Number";
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "external";
$oField->label = ffTemplate::_get_word_by_code("cache_page_external");
$oField->base_type = "Text";
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "group";
$oField->label = ffTemplate::_get_word_by_code("cache_page_group");
$oField->base_type = "Text";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);   

$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
$db->query($sSQL);
if($db->nextRecord()) {
    $count_lang = $db->getField("count_lang", "Number", true);
}

$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}
$oDetail->id = "cachePageSeoLanguages";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = "cache_page_rel_seo_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_cache_page_seo" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    cache_page_rel_seo_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , cache_page_rel_seo_languages.success AS success
                                    , cache_page_rel_seo_languages.warning AS warning
									, cache_page_rel_seo_languages.error AS error
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN cache_page_rel_seo_languages ON cache_page_rel_seo_languages.ID_languages = " . FF_PREFIX . "languages.ID AND cache_page_rel_seo_languages.ID_cache_page_seo = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_language");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_ID_languages");
$oField->base_type = "Number";
$oField->required = true;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "success";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_success");
$oField->display_label = false;
$oField->control_type = "textarea";
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "warning";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_warning");
$oField->display_label = false;
$oField->control_type = "textarea";
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "error";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_error");
$oField->display_label = false;
$oField->control_type = "textarea";
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);


$cm->oPage->addContent($oRecord);