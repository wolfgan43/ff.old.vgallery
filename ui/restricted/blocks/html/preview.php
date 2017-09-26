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
 
if (!AREA_HTML_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
check_function("system_ffcomponent_set_title");

system_ffcomponent_resolve_by_path();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "HtmlModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "";
$oRecord->skip_action = true;

check_function("process_html_page_error");
/**
* Title
*/
if(isset($_REQUEST["keys"]["permalink"]))
{
	$file_name = ffGetFilename($_REQUEST["keys"]["permalink"]);
	$file_title = $_REQUEST["keys"]["permalink"];
	if(check_function("get_locale")) {
		$arrLang = get_locale("lang", true);
	}	
} else 
{
	$file_title = ffTemplate::_get_word_by_code("record_not_found");
}


system_ffcomponent_set_title(
	$file_title
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
	$oField->id = "permalink";
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

		$content = "";
		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $lang_code . "/" . $_REQUEST["keys"]["permalink"])) {
			$content = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $lang_code . "/" . $_REQUEST["keys"]["permalink"]);
		} elseif(LANGUAGE_DEFAULT == $lang_code && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $_REQUEST["keys"]["permalink"])) {
			$content = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $_REQUEST["keys"]["permalink"]);
		}

		if(!$content)
			$content =  process_html_page_error(404);

		$oRecord->addContent($content, "html_" . $lang_code);
	}
} else {
	$oRecord->hide_all_controls = true;
	$oRecord->fixed_pre_content = process_html_page_error(404);
}

$cm->oPage->addContent($oRecord);   
  
