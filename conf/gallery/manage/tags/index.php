<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_INTERNATIONAL_SHOW_MODIFY || AREA_LANGUAGES_SHOW_MODIFY || AREA_CHARSET_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$sSQL = "SELECT *,
				IF( " . FF_PREFIX . "languages.ID = " . $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number") . ",1,0) AS language_default
			FROM " . FF_PREFIX . "languages
			WHERE " . FF_PREFIX . "languages.status > 0
			ORDER BY ID";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord())
{
	do {
		$ID_lang = $db_gallery->getField("ID", "Number", true);
		if(strlen($stringLang))
			$stringLang .= ",";
		$stringLang .= $ID_lang;
		$arrLang[$ID_lang] = $db_gallery->getField("description", "Text", true);
	} while ($db_gallery->nextRecord());
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
                                        WHERE ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                AND code NOT IN (
                                                        SELECT code
                                                        FROM search_tags
                                                        WHERE 1 
                                                        AND code > 0
                                                        GROUP BY code
                                                        HAVING GROUP_CONCAT( ID_lang ORDER BY ID_lang ) = " . $db_gallery->toSql($stringLang) . "
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

                                ORDER BY IF(search_tags.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    , 0
                                    , 1
                                )
                                SEPARATOR '<br /> '
                              )
                            ) AS name
                            , IF(GROUP_CONCAT( ID_lang ORDER BY ID_lang) = " . $db_gallery->toSql($stringLang) . ",1,0 ) AS listLanguages
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
$oField->widget = "activecomboex";
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
    $oField->widget = "activecomboex";
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
$oButton->template_file = "ffButton_link_image.html";                           
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


foreach($arrLang AS $ID_lang => $lang_description) {
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
								AND page_by_lang.ID_lang = " . $db_gallery->toSql($ID_lang, "Number") . "
						) AS name" . $ID_lang;

}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsPage";
$oGrid->source_SQL = "SELECT code
						$sSQL_page_field
						, IF(GROUP_CONCAT( ID_lang ORDER BY ID_lang) = " . $db_gallery->toSql($stringLang) . ",1,0 ) AS listLanguages
                    FROM search_tags_page 
                    [WHERE] 
                    GROUP BY search_tags_page.code
                    [HAVING]
                    [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/page/modify";
$oGrid->record_id = "TagsPageModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->resources[] = "SeoModify";
$oGrid->addEvent("on_before_parse_row", "tagPage_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "code";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if(is_array($arrLang) && count($arrLang) > 1) {
	foreach($arrLang AS $ID_lang => $lang_description) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name" . $ID_lang;
		$oField->label = ffTemplate::_get_word_by_code("tags_page") . " " . $lang_description;
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
    $oButton->form_action_url = ""; //impostato nell'evento
    $oButton->jsaction = "";
    $oButton->aspect = "link";
	//$oButton->image = "seo.png";
	$oButton->label = ffTemplate::_get_word_by_code("seo");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

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


function tagPage_on_before_parse_row($component)
{
	$cm = cm::getInstance();
	if(isset($component->grid_buttons["seo"])) {
	    if($component->grid_buttons["seo"]->action_type == "submit") {
	        $cm->oPage->widgetLoad("dialog");
	        $cm->oPage->widgets["dialog"]->process(
	             $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue()
	             , array(
	                "tpl_id" => $component->id
	                //"name" => "myTitle"
	                , "url" => FF_SITE_PATH . VG_SITE_RESTRICTED . "/sitemap/tag/modify"
	                            . "?key=" . $component->key_fields["ID"]->getValue() 
	                , "title" => ffTemplate::_get_word_by_code("seo")
	                , "callback" => ""
	                , "class" => ""
	                , "params" => array()
	            )
	            , $cm->oPage
	        );
	        $component->grid_buttons["seo"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue() . "')";
	    }
		$component->grid_buttons["seo"]->visible = true;
	} 

}

function enableTag_on_before_parse_row($component) {
	
	if(isset($component->grid_buttons["enableTag"])) {
		if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["enableTag"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["enableTag"]->icon = null;
            $component->grid_buttons["enableTag"]->action_type = "submit"; 
            $component->grid_buttons["enableTag"]->form_action_url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["enableTag"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["enableTag"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["enableTag"]->action_type = "gotourl";
                //$component->grid_buttons["enableTag"]->url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=0&frmAction=settagstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
	    } else {
			$component->grid_buttons["enableTag"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["enableTag"]->icon = null;
            $component->grid_buttons["enableTag"]->action_type = "submit";     
            $component->grid_buttons["enableTag"]->form_action_url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["enableTag"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["enableTag"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'settagstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["enableTag"]->action_type = "gotourl";
                //$component->grid_buttons["enableTag"]->url = $component->grid_buttons["enableTag"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["enableTag"]->parent[0]->addit_record_param . "settagstatus=1&frmAction=settagstatus&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }    
	    }
	}
	
}