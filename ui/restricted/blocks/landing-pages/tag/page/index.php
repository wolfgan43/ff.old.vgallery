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
 
$db = ffDB_Sql::factory();
if(check_function("get_locale"))
	$arrLang = get_locale("lang", true);
	
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsPage";
$oGrid->order_default = "ID";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "TagsPageModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "code";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);


if(is_array($arrLang) && count($arrLang) > 1) {
	foreach($arrLang AS $lang_code => $lang) {
		$sSQL_page_field .= ", (SELECT 
								CONCAT(
									'title: '
									, page_by_lang.meta_title
									, '<br />'
									'description: '
									, page_by_lang.meta_description
								)
							FROM search_tags_page AS page_by_lang
							WHERE page_by_lang.code = search_tags_page.code
								AND page_by_lang.ID_lang = " . $db->toSql($lang["ID"], "Number") . "
						) AS name" . $lang["ID"];
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name" . $lang["ID"];
		$oField->label = ffTemplate::_get_word_by_code("tags_page") . " " . $lang["description"];
		$oField->encode_entities = false;
		$oGrid->addContent($oField);
	}


    $oField = ffField::factory($cm->oPage);
    $oField->id = "listLanguages";
    $oField->label = ffTemplate::_get_word_by_code("tag_complete");
    $oField->multi_pairs = array (
        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("incomplete"))),
        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("complete")))
    );
    $oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->src_having = true;
    $oGrid->addSearchField($oField);
}

if(AREA_SEO_SHOW_MODIFY) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "seo";
	$oButton->ajax = $oGrid->record_id;
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . "/admin/seo?key=[ID_VALUE]&type=tag";
	$oButton->aspect = "link";
	//$oButton->image = "seo.png";
	//$oButton->label = ffTemplate::_get_word_by_code("seo");
    $oGrid->addGridButton($oButton);
}

$oGrid->source_SQL = "SELECT code
						$sSQL_page_field
						, IF(COUNT(DISTINCT ID_lang) > 1, 1, 0 ) AS listLanguages
                    FROM search_tags_page 
                    [WHERE] 
                    GROUP BY search_tags_page.code
                    [HAVING]
                    [ORDER]";

$cm->oPage->addContent($oGrid);  