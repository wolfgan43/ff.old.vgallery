<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["newslettercnf-ID"])) {
    $db_gallery->query("SELECT module_newsletter.*
                            FROM 
                                module_newsletter
                            WHERE 
                                module_newsletter.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["newslettercnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
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

if(isset($_REQUEST["keys"]["newslettercnf-ID"]))
{
	$module_newsletter_title = ffTemplate::_get_word_by_code("modify_module_newsletter");
	$sSQL = "SELECT module_newsletter.name
				FROM module_newsletter
				WHERE module_newsletter.ID = " . $_REQUEST["keys"]["newslettercnf-ID"];
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord())
	{
		$module_newsletter_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_newsletter_title = ffTemplate::_get_word_by_code("addnew_module_newsletter");
}
$oRecord->id = "NewsletterConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("newsletter_modify");
$oRecord->src_table = "module_newsletter";
$oRecord->addEvent("on_do_action", "NewsletterConfigModify_on_do_action");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "newsletter")) . $module_newsletter_title . '</h1>';
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "newslettercnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "service_type";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_service_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("mailchimp"), new ffData("Mail Chimp")) 
                       );
$oField->required = true;  
$oRecord->addContent($oField);

$oRecord->addTab("form");
$oRecord->setTabTitle("form", ffTemplate::_get_word_by_code("newsletter_config_forms"));

$oRecord->addContent(null, true, "form"); 
$oRecord->groups["form"] = array(
                                 "title" => ffTemplate::_get_word_by_code("newsletter_config_forms")
                                 , "cols" => 1
                                 , "tab" => "form"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "url";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_url");
$oRecord->addContent($oField, "form");

$oField = ffField::factory($cm->oPage);
$oField->id = "url_width";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_url_width");
$oField->default_value = new ffData("100%", "Text");
$oRecord->addContent($oField, "form");

$oField = ffField::factory($cm->oPage);
$oField->id = "url_height";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_url_height");
$oField->default_value = new ffData("400px", "Text");
$oRecord->addContent($oField, "form");
                              
$oField = ffField::factory($cm->oPage);
$oField->id = "form";
$oField->label = ffTemplate::_get_word_by_code("newsletter_config_form");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "form");

$cm->oPage->addContent($oRecord);

function NewsletterConfigModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
	}	
}
?>
