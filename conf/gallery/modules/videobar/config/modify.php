<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["videobarcnf-ID"])) {
    $db_gallery->query("SELECT module_videobar.*
                            FROM 
                                module_videobar
                            WHERE 
                                module_videobar.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["videobarcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } else {
		if($_REQUEST["keys"]["ID"] > 0) {
	    	$db_gallery->execute("DELETE
		                            FROM 
		                                modules
		                            WHERE 
		                                modules.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
		                        );
		    if($_REQUEST["XHR_DIALOG_ID"]) {
			    die(ffCommon_jsonenc(array("resources" => array("modules"), "close" => true, "refresh" => true), true));
		    } else {
			    ffRedirect($_REQUEST["ret_url"]);
		    }
        } 
	}
}

if($_REQUEST["keys"]["videobarcnf-ID"] > 0)
{
	$module_videobar_title = ffTemplate::_get_word_by_code("modify_module_videobar");
	$db_gallery->query("SELECT module_videobar.*
                            FROM 
                                module_videobar
                            WHERE 
                                module_videobar.ID = " . $db_gallery->toSql($_REQUEST["keys"]["videobarcnf-ID"], "Number")
                        );
    if($db_gallery->nextRecord()) {
		$module_videobar_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_videobar_title = ffTemplate::_get_word_by_code("addnew_module_videobar");
}

$oRecord->id = "VideobarConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("videobar_modify");
$oRecord->src_table = "module_videobar";
$oRecord->addEvent("on_do_action", "VideobarConfigModify_on_do_action");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "videobar")) . $module_videobar_title . '</h1>';

if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "videobarcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("module_videobar_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_videobar_general")
                                 , "cols" => 1
                                 , "tab" => "general"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("videobar_name");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "quantity";
$oField->label = ffTemplate::_get_word_by_code("videobar_quantity");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->multi_pairs =  array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("small"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("large")))
                       ); 
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "mode";
$oField->label = ffTemplate::_get_word_by_code("videobar_mode");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->multi_pairs =  array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("horizzontal"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("vertical")))
                       ); 
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "std_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_std_list");
$oField->extended_type = "Selection";
$oField->multi_pairs =  array (
                            array(new ffData("ytfeed:most_viewed.this_week"), new ffData(ffTemplate::_get_word_by_code("youtube_most_viewed"))),
                            array(new ffData("ytfeed:top_rated.this_week"), new ffData(ffTemplate::_get_word_by_code("youtube_top_rated"))),
                            array(new ffData("ytfeed:google_news"), new ffData(ffTemplate::_get_word_by_code("youtube_google_news"))),
                            array(new ffData("ytfeed:recently_featured"), new ffData(ffTemplate::_get_word_by_code("youtube_recently_featured")))
                       ); 
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "channel_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_channel_list");
$oField->widget = "listgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "search_list";
$oField->label = ffTemplate::_get_word_by_code("videobar_search_list");
$oField->widget = "listgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$cm->oPage->addContent($oRecord);

function VideobarConfigModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
	}	
}
?>
