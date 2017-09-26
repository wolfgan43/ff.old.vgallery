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
if (!AREA_STATIC_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

$_REQUEST["type"] = "page"; 
if(check_function("get_schema_fields_by_type"))
	$src = get_schema_fields_by_type($_REQUEST["type"]);

check_function("system_ffcomponent_set_title");
$record = system_ffComponent_resolve_record($src["table"], array(
	"meta_title" => null
));

system_ffcomponent_set_title(
);


if($_REQUEST["keys"]["ID"]) {
	$_REQUEST["key"] = $_REQUEST["keys"]["ID"];
	$_REQUEST["type"] = "page";
	
	if(0 && $cm->isXHR()) {
		require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/pages/blocks/index.php");
		require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/seo/data.php");

	//	ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
/*
		switch($_REQUEST["XHR_COMPONENT"]) {
			case "SeoModify":
				require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/seo/data.php");
				break;
			case "structureModify":
				require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/pages/blocks/index.php");
				break;
			default:
		}*/
	} else {
		$_REQUEST["grp"] = "page";	
		$cm->oPage->addContent(null, true, $_REQUEST["grp"], array("tab" => "top")); 
	//	$cm->oPage->groups[$_REQUEST["grp"]]["tab_mode"] = "top";

		require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/pages/blocks/index.php");
		//$cm->oPage->addContent("struct", $_REQUEST["grp"], null, array("title" => ffTemplate::_get_word_by_code("page_structure"))); 

		$cm->oPage->addContent('<iframe src="' . FF_SITE_PATH . $_REQUEST["keys"]["permalink"] . '" width="100%" height="600"></iframe>', $_REQUEST["grp"], null, array("title" => ffTemplate::_get_word_by_code("page_preview"))); 
		
		require(FF_DISK_PATH . VG_UI_PATH . "/restricted/blocks/seo/data.php");


		$cm->oPage->addContent("assets", $_REQUEST["grp"], null, array("title" => ffTemplate::_get_word_by_code("page_assets"))); 
	}
} else {

}