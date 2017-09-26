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

if (!(AREA_HTML_SHOW_MODIFY || AREA_HTML_SHOW_ADDNEW || AREA_HTML_SHOW_DELETE)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");
if(system_ffcomponent_switch_by_path(__DIR__)) {
	$db = ffDB_Sql::factory();

	$sSQL_file = "";

	if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template")) {
		$directory = new RecursiveDirectoryIterator(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template");
		$flattened = new RecursiveIteratorIterator($directory);

		$static_file = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:html)$#Di');

		$arrStaticFile = array();
		foreach($static_file AS $file) {
			$filename = $file->getFilename();
			if(!isset($arrStaticFile[$filename])) {
				if(strlen($sSQL_file))
					$sSQL_file .= " UNION ";

				$sSQL_file .= " (
									SELECT 
										" . $db->toSql($filename) . " AS permalink
										, " . $db->toSql($filename) . " AS name
								)";
				$arrStaticFile[$filename] = true;
			}
		}
	}	
	if(strlen($sSQL_file))
	    $sSQL_file = "SELECT * FROM ( " . $sSQL_file  . " ) AS tbl_src [WHERE] [HAVING] [ORDER]";

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "html";
	$oGrid->source_SQL = $sSQL_file;

	$oGrid->order_default = "name";
	$oGrid->use_search = true;
	$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/add";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
	$oGrid->record_id = "HtmlModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->display_new = AREA_HTML_SHOW_ADDNEW;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = AREA_HTML_SHOW_MODIFY;
	$oGrid->display_delete_bt = AREA_HTML_SHOW_DELETE;
	
	/**
	* Title
	*/
	system_ffcomponent_set_title(
		ffTemplate::_get_word_by_code("html_title")
		, true
		, false
		, false
		, $oGrid
	);

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "permalink";
	$oGrid->addKeyField($oField);

	// Campi di ricerca

	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("html_name");
	$oGrid->addContent($oField);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "preview"; 
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[permalink_VALUE]/preview?[KEYS]";  
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("preview");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
	
	
	$cm->oPage->addContent($oGrid);
}