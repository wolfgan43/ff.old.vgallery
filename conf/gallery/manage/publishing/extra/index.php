<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_PUBLISHING_SHOW_DETAIL) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_publishing = $_REQUEST["keys"]["ID"];
$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        break;
    default:
        $src_table = $src_type;
}

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->dialog_action_button = true;
	//$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
	$oGrid->id = "PublishingModifyFields";
	$oGrid->source_SQL = "SELECT publishing_fields.* 
								, CONCAT(" . $src_type . "_type.name, ' - ', " . $src_type . "_fields.name) AS name
	                        FROM publishing_fields
                                INNER JOIN " . $src_type . "_fields ON " . $src_type . "_fields.ID = publishing_fields.ID_fields
                                INNER JOIN " . $src_type . "_type ON " . $src_type . "_type.ID = " . $src_type . "_fields.ID_type
	                        WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($ID_publishing, "Number") . "
	                            [AND] [WHERE] 
	                        [HAVING] 
	                        [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = false;
	$oGrid->use_order = false;
	$oGrid->use_paging = false;
	$oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/publishing/extra/modify";
    $oGrid->addit_insert_record_param = "src=" . $src_type . "&publishing=" . $ID_publishing . "&";
    $oGrid->addit_record_param = "src=" . $src_type . "&publishing=" . $ID_publishing . "&";
	$oGrid->record_id = "PublishingExtraFieldModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->buttons_options["export"]["display"] = false;
	$oGrid->widget_deps[] = array(
	    "name" => "dragsort"
	    , "options" => array(
	          &$oGrid
	        , array(
	            "resource_id" => "publishing_fields"
	            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	        )
	        , "ID"
	    )
	);
	//$oGrid->addEvent("on_before_parse_row", "PublishingModifyFields_on_before_parse_row");


	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_SQL = " `parent_thumb`, `order_thumb`, ID";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_name");
	$oGrid->addContent($oField);	
	
	$cm->oPage->addContent($oGrid);
?>
