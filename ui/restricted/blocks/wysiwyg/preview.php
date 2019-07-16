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

 if (!Auth::env("AREA_DRAFT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}    

check_function("system_ffcomponent_set_title");
check_function("process_html_page_error");

system_ffcomponent_resolve_by_path();
$db = ffDB_Sql::factory();

if(check_function("get_locale")) {
	$arrLang = get_locale("lang", true);
}
if(isset($_REQUEST["keys"]["ID"]))
	$sWhere = "drafts.ID = " . $db->tosql($_REQUEST["keys"]["ID"], "Number");

if(isset($_REQUEST["keys"]["permalink"]))
	$sWhere = "drafts.name = " . $db->tosql(basename($_REQUEST["keys"]["permalink"]));

if($sWhere)
{
	$sSQL = "SELECT 
				drafts.ID AS ID
				, drafts.display_name AS title
				, drafts_rel_languages.value AS value
				, " . FF_PREFIX . "languages.code AS lang_code
			FROM drafts
				INNER JOIN drafts_rel_languages ON drafts_rel_languages.ID_drafts = drafts.ID
				INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = drafts_rel_languages.ID_languages
			WHERE " . $sWhere;
	$db->query($sSQL);
	if($db->nextRecord())
	{
		$_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
		$title_draft = $db->getField("title", "Text", true);
		do {
			$arrValue[$db->getField("lang_code", "Text", true)] = $db->getField("value", "Text", true);
		} while($db->nextRecord());
	}
} else
{
	$title_draft = ffTemplate::_get_word_by_code("record_not_found");
	$oRecord->fixed_pre_content = process_html_page_error(404);
	
	$oRecord->hide_all_controls = true;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "HtmlModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "";
$oRecord->skip_action = true;

/**
* Title
*/
system_ffcomponent_set_title(
	$title_draft
	, true
	, false
	, false
	, $oRecord
);

if(is_array($arrLang) && count($arrLang))
{
	$oRecord->buttons_options["insert"]["display"] = false;
	$oRecord->buttons_options["update"]["display"] = false;
	$oRecord->buttons_options["delete"]["display"] = false;	
	$oRecord->user_vars["arrLang"] = $locale["lang"];

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oRecord->addKeyField($oField);

	foreach($arrLang AS $lang_code => $lang)
	{
		$oRecord->addTab("html_" . $lang_code);
		$oRecord->setTabTitle("html_" . $lang_code, $lang["description"]);
		
		$oRecord->addContent(null, true, "html_" . $lang_code); 
		$oRecord->groups["html_" . $lang_code] = array(
												 "title" => $lang["description"]
												 , "cols" => 1
												 , "tab" => "html_" . $lang_code
											  );
		$content = $arrValue[$lang_code];
		if(!$content)
			$content = process_html_page_error(404);

		$oRecord->addContent($content, "html_" . $lang_code);
	}
} else {
	$oRecord->hide_all_controls = true;
	$oRecord->fixed_pre_content = process_html_page_error(404);
}

$cm->oPage->addContent($oRecord);   
