<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_newsletter");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "NewsletterConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_newsletter";
$oRecord->addEvent("on_do_action", "NewsletterConfigModify_on_do_action");
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
    $record["name"]
    , true
    , false
    , false
    , $oRecord
);  	
	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
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
