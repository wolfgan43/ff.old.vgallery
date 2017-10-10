<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_PROPERTIES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Extras";
$oGrid->title = ffTemplate::_get_word_by_code("extras_title");
$oGrid->source_SQL = "SELECT
                            settings_thumb.ID
                            , settings_thumb.tbl_src
                            , IF(tbl_src = 'vgallery_nodes'
                                    , (SELECT CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM vgallery_nodes WHERE ID = items)
                                    , IF(tbl_src = 'files'
                                        , (SELECT CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM files WHERE ID = items)
                                        , IF(tbl_src = 'publishing'
                                            , (SELECT name AS path FROM publishing WHERE ID = items)
                                            , IF(tbl_src = 'anagraph'
                                                , IF(items > 0
                                                    , (SELECT CONCAT('/', name) AS path FROM anagraph_categories WHERE ID = items)
                                                    , '/'
                                                )
                                                , items
                                            )
                                            
                                        )
                                    )
                            ) AS new_items
                            , (SELECT name FROM layout WHERE settings_thumb.ID_layout = layout.ID) AS new_layout
                             
                        FROM
                            settings_thumb
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "ExtrasModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "tblsrc";
$oField->data_source = "tbl_src";
$oField->label = ffTemplate::_get_word_by_code("extras_tbl_src");
$oField->multi_pairs = array (
                            array(new ffData("files"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
                            array(new ffData("vgallery_nodes"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
                            array(new ffData("publishing"), new ffData(ffTemplate::_get_word_by_code("publishing"))),
                            array(new ffData("search"), new ffData(ffTemplate::_get_word_by_code("search"))),
                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph")))
                       );      
$oGrid->addContent($oField);
                    
$oField = ffField::factory($cm->oPage);
$oField->id = "items";
$oField->data_source = "new_items";
$oField->label = ffTemplate::_get_word_by_code("extras_items");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layout";
$oField->data_source = "new_layout";
$oField->label = ffTemplate::_get_word_by_code("extras_layout");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("extras"))); 

/*   //MODIFICA GIORGIO
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "ExtrasImage";
$oGrid->resources[] = "ExtrasImage";
$oGrid->title = ffTemplate::_get_word_by_code("extras_image_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "showfiles_modes.*
                        FROM
                            " . CM_TABLE_PREFIX . "showfiles_modes
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/image/modify";
$oGrid->record_id = "ExtrasImageModify";
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
$oField->label = ffTemplate::_get_word_by_code("extras_image_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("extras_image"))); 
*/

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "ExtrasMode";
$oGrid->title = ffTemplate::_get_word_by_code("extras_mode_title");
$oGrid->source_SQL = "SELECT
                            settings_thumb_mode.*
                        FROM
                            settings_thumb_mode
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/mode/modify";
$oGrid->record_id = "ExtrasModeModify";
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
$oField->label = ffTemplate::_get_word_by_code("extras_mode_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("extras_mode_descrption");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("extras_mode")));


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "ExtrasSocial";
$oGrid->resources[] = "ExtrasSocial";
$oGrid->title = ffTemplate::_get_word_by_code("extras_social_title");
$oGrid->source_SQL = "SELECT
                            settings_thumb_social.*
                        FROM
                            settings_thumb_social
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/social/modify";
$oGrid->record_id = "ExtrasSocialModify";
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
$oField->label = ffTemplate::_get_word_by_code("extras_social_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("extras_social")));
?>
