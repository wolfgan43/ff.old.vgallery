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

if (!AREA_INTERNATIONAL_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
check_function("system_ffcomponent_set_title");

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && $_REQUEST["frmAction"] == "revert") {
    $ID_international = $_REQUEST["keys"]["ID"];
    if($ID_international > 0) {
        $sSQL = "UPDATE " . FF_PREFIX . "international 
                        SET " . FF_PREFIX . "international.exclude_update = ''
                        WHERE 
                            " . FF_PREFIX . "international.ID = " . $db->toSql($ID_international, "Number");
        $db->execute($sSQL);
        
        file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . "/conf/gallery/updater/data." . FF_PHP_EXT . "/international?json=1&exec=1");
    }    
    if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("InternationalModify")), true));
    } else {
        ffRedirect($_REQUEST["ret_url"]);
    }
}

$word_code = trim($_REQUEST["wc"]);

if($word_code) {
    $sSQL = "SELECT " . FF_PREFIX . "international.* 
                FROM " . FF_PREFIX . "international 
                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = " . FF_PREFIX . "international.ID_lang
                WHERE word_code = " . $db->toSql($word_code, "Text") . "
                    AND code = " . $db->toSql(LANGUAGE_INSET, "Text");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db->getField("ID")->getValue();
    }
}
// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "InternationalModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = FF_PREFIX . "international";
$oRecord->addEvent("on_done_action", "InternationalModify_on_done_action");
if(strpos(MASTER_SITE, DOMAIN_NAME) === false) {
	$oRecord->update_additional_fields["exclude_update"] = new ffData("1", "Number");
} else {
	$oRecord->update_additional_fields["exclude_update"] = new ffData("0", "Number");
}
$oRecord->update_additional_fields["is_new"] = new ffData("0", "Number");    
$oRecord->update_additional_fields["last_update"] = new ffData(time(), "Number");     
$oRecord->insert_additional_fields["last_update"] = new ffData(time(), "Number");     

/**
* Title
*/
system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("international_title")
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
$oField->id = "word_code";
$oField->class = "autocomplete wordcode";
$oField->label = ffTemplate::_get_word_by_code("international_word_code");
//$oField->extended_type = "Selection";
$oField->widget = "autocomplete";
$oField->autocomplete_minLength = 0;
$oField->autocomplete_combo = true;
$oField->autocomplete_compare = "word_code";
$oField->autocomplete_readonly = false;
$oField->autocomplete_operation = "LIKE [%[VALUE]%]"; 
$oField->source_SQL = "SELECT 
							" . FF_PREFIX . "international.word_code AS ID 
							, " . FF_PREFIX . "international.word_code AS name
						FROM " . FF_PREFIX . "international
						WHERE 1
						[AND] [WHERE]
						GROUP BY word_code
						[HAVING]
						[ORDER] [COLON] is_new DESC, word_code
						[LIMIT]";
$oField->actex_update_from_db = true;
$oField->required = true;
if(!$_REQUEST["keys"]["ID"]) {
    $oField->default_value = new ffData($word_code, "Text");
} 
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "ID_lang";
$oField->label = ffTemplate::_get_word_by_code("international_languages");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT
                           " . FF_PREFIX . "languages.ID,
                           " . FF_PREFIX . "languages.description
                       FROM
                           " . FF_PREFIX . "languages
                       WHERE " . FF_PREFIX . "languages.status = 1
                       [OR] [WHERE]
                       ORDER BY " . FF_PREFIX . "languages.description ";
$oField->required = true;
$oField->multi_preserve_field = "ID";
$oField->default_value = new ffData(LANGUAGE_INSET_ID, "Number");
$oRecord->addContent($oField);

$system_modules = array("restricted", "notifier", "security");

$arrModules[] = array(new ffData("area_admin"), new ffData(ffTemplate::_get_word_by_code("area_admin")));
$arrModules[] = array(new ffData("area_restricted"), new ffData(ffTemplate::_get_word_by_code("area_restricted")));
$arrModules[] = array(new ffData("area_manage"), new ffData(ffTemplate::_get_word_by_code("area_manage")));
$arrModules[] = array(new ffData("area_email"), new ffData(ffTemplate::_get_word_by_code("area_email")));

$module_file = glob(FF_DISK_PATH . "/modules/*");
if(is_array($module_file) && count($module_file)) {
    foreach($module_file AS $real_dir) {
        if(is_dir($real_dir) && array_search(basename($real_dir), $system_modules) === false) {
            $arrModules[] = array(new ffData("module_" . basename($real_dir)), new ffData(ffTemplate::_get_word_by_code("module") . " " . ucfirst(basename($real_dir))));
        }
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("international_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = $arrModules;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("international_type_default");
$oRecord->addContent($oField);



$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("international_description");
$oField->control_type = "textarea";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);


function InternationalModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if(strlen($action)) {
    	$sSQL = "
                DELETE FROM " . FF_PREFIX . "international 
                WHERE " . FF_PREFIX . "international.word_code = " . $db->toSql($component->form_fields["word_code"]->value) . "
                    AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql($component->form_fields["ID_lang"]->value) . "
                    AND " . FF_PREFIX . "international.ID <> " . $db->toSql($component->key_fields["ID"]->value);
        $db->execute($sSQL);
    	
    	
        //UPDATE CACHE
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
                ";
        $db->execute($sSQL);

        //UPDATE CACHE 
        $db->query("SELECT * FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'"); 
        if($db->nextRecord()) {
            $i18n_error = false;
            do {
                if($handle = @fopen(FF_DISK_PATH . ffTemplate::$_MultiLang_cache_path . "/" . strtoupper($db->getField("code")->getValue()) . "." . FF_PHP_EXT, 'w')) {
                    $i18n_content = "";
                    if(@fwrite($handle, $i18n_content) === FALSE) {
                        $i18n_error = true;
                    }
                    @fclose($handle);
                } else {
                    $i18n_error = true;
                }
            } while($db->nextRecord());
        }

        return $i18n_error;
    }
}
