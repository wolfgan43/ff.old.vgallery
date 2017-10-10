<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_LAYER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$sSQL = "SELECT cm_layout.* 
        FROM cm_layout 
        WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
    $template_framework = $framework_css["name"];
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Layer";
$oGrid->title = ffTemplate::_get_word_by_code("layer_title");
$oGrid->source_SQL = "SELECT layout_layer.* 
                            FROM layout_layer 
                            WHERE 1
                            [AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "order";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LayerModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
	"name" => "dragsort"
	, "options" => array(
		  &$oGrid
		, array(
			"resource_id" => "layout_layer"
			, "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
		)
		, "ID"
	)
);
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca
 
// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("section_name");
$oGrid->addContent($oField);

if(!$template_framework) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "width";
	$oField->label = ffTemplate::_get_word_by_code("layer_width");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "show_empty";
	$oField->label = ffTemplate::_get_word_by_code("layer_show_empty");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
		                        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
		                   );   
	$oField->multi_select_one = false;
	$oGrid->addContent($oField);	
}

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("layer_order");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);

?>
