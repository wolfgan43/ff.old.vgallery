<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["fbalbumcnf-ID"])) {
    $db_gallery->query("SELECT module_fbalbum.*
                            FROM 
                                module_fbalbum
                            WHERE 
                                module_fbalbum.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["fbalbumcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
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

if($_REQUEST["keys"]["fbalbumcnf-ID"] > 0)
{
	$module_fbalbum_title = ffTemplate::_get_word_by_code("modify_module_fbalbum");
	$db_gallery->query("SELECT module_fbalbum.*
                            FROM 
                                module_fbalbum
                            WHERE 
                                module_fbalbum.ID = " . $db_gallery->toSql($_REQUEST["keys"]["fbalbumcnf-ID"], "Number")
                        );
    if($db_gallery->nextRecord()) {
		$module_fbalbum_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_fbalbum_title = ffTemplate::_get_word_by_code("addnew_module_fbalbum");
}
	
$oRecord->id = "FBAlbumConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("fbalbum_modify");
$oRecord->src_table = "module_fbalbum";
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "fbalbum")) . $module_fbalbum_title . '</h1>';


if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "fbalbumcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "facebookID";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_facebookID");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "use_tooltip";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_use_tooltip");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "use_fancybox";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_use_fancybox");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "use_colorbox";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_use_colorbox");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "use_prettyphoto";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_use_prettyphoto");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);



$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_album";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_exclude_album");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_image";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_exclude_image");
$oRecord->addContent($oField);


	
$cm->oPage->addContent($oRecord);
?>
