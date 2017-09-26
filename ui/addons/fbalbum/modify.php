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

$record = system_ffComponent_resolve_record("module_fbalbum", array(
	"name" => "IF(module_fbalbum.display_name = ''
				    , REPLACE(module_fbalbum.name, '-', ' ')
				    , module_fbalbum.display_name
				)"
));

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FBAlbumConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_fbalbum";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_fbalbum.*
									, IF(module_fbalbum.display_name = ''
										, REPLACE(module_fbalbum.name, '-', ' ')
										, module_fbalbum.display_name
									) AS display_name
								FROM module_fbalbum 
								WHERE module_fbalbum.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
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
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("fbalbum_name");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
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
