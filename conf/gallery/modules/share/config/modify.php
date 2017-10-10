<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["sharecnf-ID"])) {
    $db_gallery->query("SELECT module_share.*
                            FROM 
                                module_share
                            WHERE 
                                module_share.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["sharecnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
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
if($_REQUEST["keys"]["sharecnf-ID"] > 0)
{
	$module_share_title = ffTemplate::_get_word_by_code("modify_module_share");
	$db_gallery->query("SELECT module_share.*
                            FROM 
                                module_share
                            WHERE 
                                module_share.ID = " . $db_gallery->toSql($_REQUEST["keys"]["sharecnf-ID"], "Number")
                        );
    if($db_gallery->nextRecord()) {
		$module_share_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_share_title = ffTemplate::_get_word_by_code("addnew_module_share");
}
$oRecord->id = "ShareConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("share_modify");
$oRecord->src_table = "module_share";
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "share")) . $module_share_title . '</h1>';


if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "sharecnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("share_config_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "service_type";
$oField->label = ffTemplate::_get_word_by_code("share_config_service_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("addthis"), new ffData("Add This"))
                            , array(new ffData("sharethis"), new ffData("Share This")) 
                       );
$oField->required = true;  
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "service_account";
$oField->label = ffTemplate::_get_word_by_code("share_config_service_account");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "active";
$oField->label = ffTemplate::_get_word_by_code("share_config_active");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("simple"), new ffData(ffTemplate::_get_word_by_code("share_simple")))
                            , array(new ffData("advanced"), new ffData(ffTemplate::_get_word_by_code("share_advanced"))) 
                       );
$oField->default_value = new ffData("simple");
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addTab("simple");
$oRecord->setTabTitle("simple", ffTemplate::_get_word_by_code("share_config_simple"));

$oRecord->addContent(null, true, "simple"); 
$oRecord->groups["simple"] = array(
                                 "title" => ffTemplate::_get_word_by_code("share_config_simple")
                                 , "cols" => 1
                                 , "tab" => "simple"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "simple_share";
$oField->label = ffTemplate::_get_word_by_code("share_config_simple_share");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "simple");


$oRecord->addTab("advanced");
$oRecord->setTabTitle("advanced", ffTemplate::_get_word_by_code("share_config_advanced"));

$oRecord->addContent(null, true, "advanced"); 
$oRecord->groups["advanced"] = array(
                                 "title" => ffTemplate::_get_word_by_code("share_config_advanced")
                                 , "cols" => 1
                                 , "tab" => "advanced"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_force_absolute";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_force_absolute");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_css";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_css");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_html";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_html");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_jsmain";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_jsmain");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "advanced_jsdep";
$oField->label = ffTemplate::_get_word_by_code("share_config_advanced_jsdep");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "advanced");
                              

$cm->oPage->addContent($oRecord);
?>