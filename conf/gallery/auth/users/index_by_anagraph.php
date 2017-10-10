<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_USERS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$filter = basename($cm->real_path_info);

switch($filter) {
    case "noactive":
        $sSQL_where = "AND anagraph.status = 0";
        break;
    case "active":
        $sSQL_where = "AND anagraph.status = 1";
        break;
    case "nopublic":
        $sSQL_where = "AND anagraph.visible = 0";
        break;
    case "public":
        $sSQL_where = "AND anagraph.visible = 1";
        break;
    case "nocategory":
        $sSQL_where = "AND anagraph.categories = ''";
        break;
    default:
        $sSQL_from = "INNER JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories)";
        $sSQL_where = "AND anagraph_categories.smart_url = " . $db_gallery->toSql($filter);
    
    
    
}



$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Users";
//$oGrid->title = ffTemplate::_get_word_by_code("block_title");
$oGrid->source_SQL = "SELECT anagraph.ID
                            , anagraph.name
                            , anagraph.surname
                            , anagraph.email
                            , anagraph.ID_type
                        FROM anagraph
                            $sSQL_from
                        WHERE 1
                            $sSQL_where
                        [AND] [WHERE] 
                        [HAVING] 
                        [ORDER]
                        ";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/vgallery/anagraph/modify";
$oGrid->addEvent("on_before_parse_row", "Users_on_before_parse_row");
$oGrid->addit_record_param = "type=node&vname=anagraph&src=anagraph&extype=vgallery_nodes&";
$oGrid->addit_insert_record_param = "type=node&vname=anagraph&src=anagraph&extype=vgallery_nodes&";

$oGrid->record_id = "VGalleryNodesModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->resources[] = "UserModify";

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("users_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "surname";
$oField->label = ffTemplate::_get_word_by_code("users_surname");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("users_email");
$oGrid->addContent($oField);


$cm->oPage->addContent($oGrid); 


function Users_on_before_parse_row($component) {
    $cm = cm::getInstance();     
    
    if($component->db[0]->getField("ID_type", "Number", true) > 0) {
        $component->bt_edit_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/vgallery/anagraph/modify?keys[ID]=" . $component->db[0]->getField("ID", "Number", true) . "&type=node&vname=anagraph&src=anagraph&extype=vgallery_nodes";
    } else {
        $component->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?keys[ID]=" . $component->db[0]->getField("ID", "Number", true);
    }
}
?>
