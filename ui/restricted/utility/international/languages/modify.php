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

if (!AREA_LANGUAGES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

system_ffcomponent_resolve_by_path();

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && isset($_REQUEST["keys"]["ID"])) {
	$db = ffDB_Sql::factory();
	$sSQL = "UPDATE " . FF_PREFIX . "languages
				SET " . FF_PREFIX . "languages.status = " . $db->toSql($_REQUEST["setvisible"], "Number") . "
				WHERE " . FF_PREFIX . "languages.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);

    @unlink(CM_CACHE_PATH . "/locale." . FF_PHP_EXT);
    @unlink(CM_CACHE_PATH . "/locale-nocurrent." . FF_PHP_EXT);
    
	die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("LanguagesModify")), true));
   
}
// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LanguagesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = FF_PREFIX . "languages";

/**
* Title
*/
system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("languages_title")
	, array("name" => "language")
	, false
	, false
	, $oRecord
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->label = ffTemplate::_get_word_by_code("languages_code");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("languages_status") . (($_REQUEST["keys"]["ID"] == LANGUAGE_DEFAULT_ID) ? " (" . ffTemplate::_get_word_by_code("language_default") . ")" : "");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("not_active"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("active")))
                       );
if($_REQUEST["keys"]["ID"] == LANGUAGE_DEFAULT_ID) {
	$oField->control_type = "label";
} else {
	$oField->required = true;
}
$oField->default_value = new ffData("1", "Number"); 
$oField->multi_select_one = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tiny_code";
$oField->label = ffTemplate::_get_word_by_code("languages_tiny_code");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("languages_description");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "stopwords";
$oField->label = ffTemplate::_get_word_by_code("stopwords");
$oField->extended_type = "Text";
/*$oField->widget = "autocomplete";
$oField->source_SQL = "SELECT stopwords
						, stopwords 
					FROM " . FF_PREFIX . "languages 
					WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . " 
					[AND] [WHERE] 
					[HAVING]
					[ORDER] [COLON] stopwords
					[LIMIT]";
$oField->actex_update_from_db = true;
$oField->autocomplete_minLength = 0;
$oField->autocomplete_readonly = false;
$oField->autocomplete_compare = "stopwords";
$oField->autocomplete_operation = "LIKE [[VALUE]%]";*/
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);