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

if (!(Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY") || Auth::env("AREA_LANGUAGES_SHOW_MODIFY") || Auth::env("AREA_CHARSET_SHOW_MODIFY"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();

$sSQL = "SELECT *,
				IF( " . FF_PREFIX . "languages.ID = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . ",1,0) AS language_default
			FROM " . FF_PREFIX . "languages
			WHERE " . FF_PREFIX . "languages.status > 0
			ORDER BY ID";
$db->query($sSQL);
if($db->nextRecord())
{
	do {
		$ID_lang = $db->getField("ID", "Number", true);
		if(strlen($stringLang))
			$stringLang .= ",";
		$stringLang .= $ID_lang;
		$arrLang[$ID_lang] = $db->getField("tiny_code", "Text", true);
	} while ($db->nextRecord());
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "newTags";
$oGrid->source_SQL = "SELECT search_tags.* 
                                                , (SELECT GROUP_CONCAT(search_tags_categories.name SEPARATOR ', ')
                                                        FROM search_tags_categories
                                                        WHERE FIND_IN_SET(search_tags_categories.ID, search_tags.categories)
                                                ) AS categories
                                        FROM search_tags 
                                        WHERE ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                AND code NOT IN (
                                                        SELECT code
                                                        FROM search_tags
                                                        WHERE 1 
                                                        AND code > 0
                                                        GROUP BY code
                                                        HAVING GROUP_CONCAT( ID_lang ORDER BY ID_lang ) = " . $db->toSql($stringLang) . "
                                                ) [AND] [WHERE] [HAVING] [ORDER]";
$oGrid->source_SQL = "SELECT ID
                            , code
                            , (GROUP_CONCAT(
                                CONCAT(
                                    IFNULL((SELECT CONCAT(
                                                " . FF_PREFIX . "languages.description
                                                , ' - '
                                            )
                                            FROM " . FF_PREFIX . "languages
                                                WHERE " . FF_PREFIX . "languages.ID = search_tags.ID_lang
                                    ), '')
                                    , search_tags.name 
                                )

                                ORDER BY IF(search_tags.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    , 0
                                    , 1
                                )
                                SEPARATOR '<br /> '
                              )
                            ) AS name
                            , IF(GROUP_CONCAT( ID_lang ORDER BY ID_lang) = " . $db->toSql($stringLang) . ",1,0 ) AS listLanguages
                            , (SELECT GROUP_CONCAT(search_tags_categories.name SEPARATOR ', ')
                                    FROM search_tags_categories
                                    WHERE FIND_IN_SET(search_tags_categories.ID, search_tags.categories)
                            ) AS categories
                            , status
                    FROM search_tags 
                    [WHERE] 
                    GROUP BY search_tags.code
                    [HAVING]
                    [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "TagsModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "enableTag_on_before_parse_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "categories";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("tags_categories");
$oField->source_SQL = "SELECT ID, name
						FROM search_tags_categories
						WHERE 1
						ORDER BY name";
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->src_operation = "FIND_IN_SET([VALUE], search_tags.[NAME])";
$oGrid->addSearchField($oField);

if(is_array($arrLang) && count($arrLang) > 1) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "listLanguages";
    $oField->label = ffTemplate::_get_word_by_code("tag_complete");
    $oField->multi_pairs = array (
        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("incomplete"))),
        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("complete")))
    );
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->src_having = true;
    $oGrid->addSearchField($oField);
}

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_name");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "categories";
$oField->label = ffTemplate::_get_word_by_code("tags_categories");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "enableTag";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link"; 
$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("Tag"))); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsCategories";
$oGrid->source_SQL = "SELECT search_tags_categories.*
                    FROM search_tags_categories 
                    [WHERE] 
                    [HAVING]
                    [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/categories/modify";
$oGrid->record_id = "TagsCategoriesModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
            "resource_id" => "search_tags_categories"
            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
        )
        , "ID"
    )
);

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_categories_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("TagCategories"))); 


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsPage";
$oGrid->source_SQL = "SELECT ID
                            , code
                            , (GROUP_CONCAT(
                                CONCAT(
                                    IFNULL((SELECT CONCAT(
                                                " . FF_PREFIX . "languages.description
                                                , ' - '
                                            )
                                            FROM " . FF_PREFIX . "languages
                                                WHERE " . FF_PREFIX . "languages.ID = search_tags_page.ID_lang
                                    ), '')
                                    , search_tags_page.name    
                                )

                                ORDER BY IF(search_tags_page.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    , 0
                                    , 1
                                )
                                SEPARATOR '<br /> '
                              )
                            ) AS name
                            , IF(GROUP_CONCAT( ID_lang ORDER BY ID_lang) = " . $db->toSql($stringLang) . ",1,0 ) AS listLanguages
                    FROM search_tags_page 
                    [WHERE] 
                    GROUP BY search_tags_page.code
                    [HAVING]
                    [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/page/modify";
$oGrid->record_id = "TagsPageModify";
$oGrid->resources[] = $oGrid->record_id;

if(is_array($arrLang) && count($arrLang) > 1) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "listLanguages";
    $oField->label = ffTemplate::_get_word_by_code("tag_complete");
    $oField->multi_pairs = array (
        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("incomplete"))),
        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("complete")))
    );
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->src_having = true;
    $oGrid->addSearchField($oField);
}

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_page_title");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("LandingPage"))); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsGroup";
$oGrid->source_SQL = "SELECT search_tags_group.*
                        FROM search_tags_group 
                        [WHERE]
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = false; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify";
$oGrid->record_id = "TagsGroupModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
            "resource_id" => "search_tags_group"
            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
        )
        , "ID"
    )
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`order`, ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_page_title");
$oGrid->addContent($oField); 

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("GroupPage"))); 

$oGrid_overview = ffGrid::factory($cm->oPage);
$oGrid_overview->full_ajax = true;
$oGrid_overview->id = "TagsGroupOverview";
$oGrid_overview->source_SQL = "SELECT search_tags_group.*
                        FROM search_tags_group 
                        [WHERE]
                        [ORDER]";
$oGrid_overview->order_default = "ID";
$oGrid_overview->use_search = false; 
$oGrid_overview->display_delete_bt = false;
$oGrid_overview->display_new = false;
$oGrid_overview->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify";
$oGrid_overview->record_id = "TagsGroupOverview";
$oGrid_overview->resources[] = $oGrid_overview->record_id;
$oGrid_overview->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid_overview
        , array(
            "resource_id" => "search_tags_group_overview"
            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
        )
        , "ID"
    )
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`overview_order`, ID";
$oGrid_overview->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_page_title");
$oGrid_overview->addContent($oField); 

$cm->oPage->addContent($oGrid_overview, "rel", null, array("title" => ffTemplate::_get_word_by_code("GroupPageOverview"))); 


function enableTag_on_before_parse_row($component) {
	
	if(isset($component->grid_buttons["enableTag"])) {
		if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["enableTag"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
            $component->grid_buttons["enableTag"]->icon = null;
            $component->grid_buttons["enableTag"]->action_type = "submit"; 
            $component->grid_buttons["enableTag"]->form_action_url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["enableTag"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["enableTag"]->jsaction = "ff.ajax.doRequest({'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["enableTag"]->action_type = "gotourl";
                //$component->grid_buttons["enableTag"]->url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=0&frmAction=settagstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
	    } else {
			$component->grid_buttons["enableTag"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
            $component->grid_buttons["enableTag"]->icon = null;
            $component->grid_buttons["enableTag"]->action_type = "submit";     
            $component->grid_buttons["enableTag"]->form_action_url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["enableTag"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["enableTag"]->jsaction = "ff.ajax.doRequest({'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["enableTag"]->action_type = "gotourl";
                //$component->grid_buttons["enableTag"]->url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=1&frmAction=settagstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }    
	    }
	}
	
}