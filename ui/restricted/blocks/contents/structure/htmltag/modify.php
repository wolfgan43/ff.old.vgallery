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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if (!AREA_VGALLERY_HTMLTAG_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

system_ffcomponent_resolve_by_path();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryHtmlTagModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "vgallery_fields_htmltag";
$oRecord->display_required_note = FALSE;

 /* Title Block */
system_ffcomponent_set_title(
    null
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
$oField->id = "tag";
$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_modify_tag");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "attr";
$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_modify_attr");
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

