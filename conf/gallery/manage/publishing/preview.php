<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(Auth::env("AREA_PUBLISHING_SHOW_MODIFY") || Auth::env("AREA_PUBLISHING_SHOW_DETAIL"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_publishing = $_REQUEST["keys"]["ID"];

// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PublishingPreview";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("publishing_detail_title");
$oRecord->src_table = "publishing";
$oRecord->allow_update = false;
$oRecord->allow_insert = false;
$oRecord->allow_delete = false;
$oRecord->addEvent("on_done_action", "PublishingPreview_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("publishing_name");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("publishing_area");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->label = ffTemplate::_get_word_by_code("publishing_limit");
$oField->base_type = "Number";
$oField->control_type = "label";
$oRecord->addContent($oField);

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$sSQL = "SELECT * FROM publishing WHERE ID = " . $db_gallery->toSql(new ffData($ID_publishing, "Number"));
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $publishing = array();
    $layout = array();

    $publishing["ID"] = $ID_publishing;

    if($db_gallery->getField("area")->getValue() == "gallery") {
        $layout["prefix"] = "PM";
        $layout["ID"] = 0;
        $layout["title"] = $db_gallery->getField("name")->getValue();
        $layout["type"] = "PUBLISHING";
        $layout["location"] = "Content";
        //if(check_function("get_layout_settings"))
        	$layout["settings"] = Cms::getPackage("publishing"); //get_layout_settings(NULL, "PUBLISHING");
        $layout["visible"] = NULL;

        if(check_function("process_gallery_thumb")) {
			$res = process_gallery_thumb(NULL, NULL, NULL, NULL, $publishing, $layout);
        	$oRecord->fixed_post_content = $res["content"];
		}
    } elseif ($db_gallery->getField("area")->getValue() == "vgallery") {
        $layout["prefix"] = "PM";
        $layout["ID"] = 0;
        $layout["title"] = $db_gallery->getField("name")->getValue();
        $layout["type"] = "PUBLISHING";
        $layout["location"] = "Content";
        //if(check_function("get_layout_settings"))
        	$layout["settings"] = Cms::getPackage("publishing"); //get_layout_settings(NULL, "PUBLISHING");
        $layout["visible"] = NULL;

        if(check_function("process_vgallery_thumb")) {
			$res = process_vgallery_thumb(
					null
					, "publishing"
					, array(
						"publishing" => $publishing
						, "allow_insert" => false
					)
					, $layout
				);
        	$oRecord->fixed_post_content = $res["content"];
		}
    } else {
        $oRecord->fixed_post_content = ffTemplate::_get_word_by_code("error_automation_no_valid_area");
    }
}

$cm->oPage->addContent($oRecord);
// -------------------------
//          EVENTI
// -------------------------

function PublishingPreview_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    
    if(strlen($action)) {
        $ID_node = $component->key_fields["ID"]->getValue();


        //UPDATE CACHE
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `publishing`.last_update FROM publishing WHERE publishing.ID = " . $db->toSql($ID_node, "Number") . ") 
                WHERE 
                    (
                        REPLACE(layout.value, " . $db->toSql("vgallery_") . ", '') = " . $db->toSql($ID_node, "Number") . "
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);
        //UPDATE CACHE 
    }
}
?>
