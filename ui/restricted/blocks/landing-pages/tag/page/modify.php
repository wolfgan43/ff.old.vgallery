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

if (!Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

$arrTag = array();

$sSQL = "SELECT ID, ID_lang, name
            FROM search_tags";
$db->query($sSQL);
if($db->nextRecord()) {
    do {
        $arrSearchTag[$db->getField("ID_lang", "Number", true)][$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
    } while ($db->nextRecord());
}

$sSQL = "SELECT " . FF_PREFIX . "languages.ID
                , " . FF_PREFIX . "languages.tiny_code
                , IF( " . FF_PREFIX . "languages.ID = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . ",1,0) AS language_default
            FROM " . FF_PREFIX . "languages
            WHERE " . FF_PREFIX . "languages.status > 0
            ORDER BY language_default DESC";
$db->query($sSQL);
if($db->nextRecord())
{
    do {
        $ID_lang = $db->getField("ID", "Number", true);
        $arrLang[$ID_lang] = $db->getField("tiny_code", "Text", true);
    } while ($db->nextRecord());
}


if(isset($_REQUEST["keys"]["code"]) && $_REQUEST["keys"]["code"] > 0) {
    $sSQL_string = "search_tags_page.code = " . $db->toSql($_REQUEST["keys"]["code"], "Number");
} elseif(isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL_string = "search_tags_page.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
}

if(strlen($sSQL_string)) {
    $sSQL = "SELECT search_tags_page.*
                FROM search_tags_page
                WHERE " . $sSQL_string;
    $db->query($sSQL);
    if($db->nextRecord())
    { 
        do {
            $ID_lang = $db->getField("ID_lang", "Number", true);
            if(array_key_exists($ID_lang, $arrLang))
            {
                $arrTag[$arrLang[$ID_lang]] = array("ID_lang" => $ID_lang
                                                        , "ID" => $db->getField("ID", "Number", true)
                                                        , "name" => $db->getField("name", "Text", true)
                                                        , "smart_url" => $db->getField("smart_url", "Text", true)
                                                        , "parent" => $db->getField("parent", "Text", true)
                                                        , "title" => $db->getField("meta_title", "Text", true)
                                                        , "description" => $db->getField("meta_description", "Text", true)
                                                        , "keywords" => $db->getField("keywords", "Text", true)
                                                        , "h1" => $db->getField("h1", "Text", true)
                                                        , "h2" => $db->getField("h2", "Text", true)
                                                        , "pre_content" => $db->getField("pre_content", "Text", true)
                                                        , "code" => $db->getField("code", "Number", true)
                                                        , "post_content" => $db->getField("post_content", "Text", true)
                                                    );
            }
        } while ($db->nextRecord());
    }
} 

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "TagsPageModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "";
$oRecord->skip_action = true;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oRecord->addKeyField($oField); 

if(is_array($arrLang) && count($arrLang))
{
    $oRecord->addEvent("on_do_action", "TagsPageModify_on_do_action");
    $oRecord->user_vars["arrLang"] = $arrLang;

    foreach($arrLang AS $ID_lang => $language_name)
    {
        $oRecord->groups["header"] = array(
            "title" => ffTemplate::_get_word_by_code("header")
            , "cols" => 1
            , "class" => Cms::getInstance("frameworkcss")->get(array(12,12,7,7), "col")
        );
        $oField = ffField::factory($cm->oPage);
        $oField->id = "name_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_name");
        $oField->class = "tags translate"; 
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["name"]);
        if($ID_lang == LANGUAGE_DEFAULT_ID) {
            $oField->required = true;
        }
        $oRecord->addContent($oField, "header");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "parent_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_parent");
        $oField->store_in_db = false;
        $oField->extended_type = "Selection";
        $oField->multi_pairs = array(
                array(new ffData("/"), new ffData("/"))
        );
        $oField->default_value = new ffData($arrTag[$language_name]["parent"]);
        $oRecord->addContent($oField, $language_name);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "smart_url_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_smart_url");
        $oField->widget = "slug";
	    $oField->slug_title_field = "name_" . $language_name;
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["smart_url"]);
        $oRecord->addContent($oField, $language_name);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "title_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_title");
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["title"]);
        $oRecord->addContent($oField, $language_name);
                                        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "description_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_description");
        $oField->extended_type = "Text";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["description"]);
        $oRecord->addContent($oField, $language_name);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "keywords_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_related_keywords");
        $oField->extended_type = "Selection";

        /*$oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name";*/
        
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;	
        
        $oField->source_SQL = "SELECT ID
                                    , name
                                FROM search_tags
                                WHERE 1
                                [HAVING] 
                                [ORDER]
                                [LIMIT]";
        $oField->default_value = new ffData($arrTag[$language_name]["ID_group"]);
        $oField->store_in_db = false;
        $oRecord->addContent($oField, $language_name);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "h1_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_h1");
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["h1"]);
        $oRecord->addContent($oField, "header");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "h2_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_h2");
        $oField->extended_type = "Text";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["h2"]);
        $oRecord->addContent($oField, "header");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "pre_content_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_pre_content");
        $oField->extended_type = "Text";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["pre_content"]);
        $oRecord->addContent($oField, "header");
        
          
        $oField = ffField::factory($cm->oPage);
        $oField->id = "post_content_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_post_content");
        $oField->extended_type = "Text";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($arrTag[$language_name]["post_content"]);
        $oRecord->addContent($oField, "header");
    }
    
}

$sSQL = "SELECT search_tags_group.ID
                , search_tags_group.name
            FROM search_tags_group";
$db->query($sSQL);
if($db->nextRecord()) {
    $oRecord->addEvent("on_done_action", "TagsPageModify_on_done_action");
    $oRecord->groups["categories"] = array(
        "title" => ffTemplate::_get_word_by_code("tag_page_modify_group")
        , "cols" => 1
        , "class" => Cms::getInstance("frameworkcss")->get(array(12), "col")
    );

    do {
        $sSQL2 = "SELECT GROUP_CONCAT(search_tags_page_rel_group.ID_tag) AS value
                    FROM search_tags_page_rel_group
                    WHERE search_tags_page_rel_group.ID_tag_group = " . $db->toSql($db->getField("ID", "Number", true), "Number") . "
                        AND search_tags_page_rel_group.ID_tag_page = " . $db->toSql($_REQUEST["keys"]["code"], "Number");
        $db->query($sSQL2); 
        if($db->nextRecord()) {
            ${"value" . $db->getField("ID", "Number", true)} = $db->getField("value", "Text", true);
        }
        $oField = ffField::factory($cm->oPage);
        $oField->id = "tagGroupRelation_" . $db->getField("ID", "Number", true);
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_tag_per_type_" . $db->getField("name", "Text", true));
        $oField->extended_type = "Selection";
/*        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name";
        $oField->actex_update_from_db = true;
*/
		$oField->widget = "actex";
		$oField->actex_autocomp = true;	
		$oField->actex_multi = true;
		$oField->actex_update_from_db = true;	
        
        $oField->source_SQL = "SELECT ID
                                    , name
                                FROM search_tags
                                WHERE 1
                                [HAVING] 
                                [ORDER]
                                [LIMIT]";
        $oField->store_in_db = false;
        $oField->default_value = new ffData(${"value" . $db->getField("ID", "Number", true)}, "Text");
        $oRecord->addContent($oField, "categories");
    } while($db->nextRecord());
}


$cm->oPage->addContent($oRecord);   

function TagsPageModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    
    switch ($action) 
    {
        case "insert":
        case "update":
            foreach($component->user_vars["arrLang"] AS $ID_lang => $name_lang)
            {
                $old_smart_url = ($component->form_fields["smart_url_" . $name_lang]->default_value->getValue()
                    ? $component->form_fields["smart_url_" . $name_lang]->default_value->getValue()
                    : $component->form_fields["smart_url_" . $name_lang]->getValue()
                );

                $smart_url = $component->form_fields["smart_url_" . $name_lang]->getValue();
                $permalink_parent = $component->form_fields["parent_" . $name_lang]->getValue();
                $permalink = stripslash($permalink_parent) . "/" . $smart_url;
                
                $sSQL = "SELECT search_tags_page.*
                        FROM search_tags_page
                        WHERE 
                            search_tags_page.smart_url = " . $db->toSql($old_smart_url) . "
                            AND search_tags_page.ID_lang = " . $db->toSql($ID_lang);
                $db->query($sSQL);
                if($db->nextRecord())
                {
                    $ID_tag_page = $db->getField("ID", "number", true);
                    if($ID_lang = LANGUAGE_DEFAULT_ID)
                        $ID_code = $ID_tag_page;
                    
                    $sSQL = "UPDATE search_tags_page SET
                                 code = " . $db->toSql($ID_code, "Number") . "
                                 , name = " . $db->toSql($component->form_fields["name_" . $name_lang]->value) . "
                                 , smart_url = " . $db->toSql($smart_url) . "
                                 , parent = " . $db->toSql($permalink_parent) . "
                                 , meta_title = " . $db->toSql($component->form_fields["title_" . $name_lang]->value) . "
                                 , meta_description = " . $db->toSql($component->form_fields["description_" . $name_lang]->value) . "
                                 , keywords = " . $db->toSql($component->form_fields["keywords_" . $name_lang]->value) . "
                                 , h1 = " . $db->toSql($component->form_fields["h1_" . $name_lang]->value) . "
                                 , h2 = " . $db->toSql($component->form_fields["h2_" . $name_lang]->value) . "
                                 , pre_content = " . $db->toSql($component->form_fields["pre_content_" . $name_lang]->value) . "
                                 , post_content = " . $db->toSql($component->form_fields["post_content_" . $name_lang]->value) . "
                                 , permalink = " . $db->toSql($permalink) . "
                            WHERE ID = " . $db->toSql($ID_tag_page, "Number");
                    $db->execute($sSQL);                    
                } else 
                {
                    $sSQL = "INSERT INTO search_tags_page
                                (
                                    ID
                                    , ID_lang
                                    , code
                                    , visible
                                    , name
                                    , smart_url
                                    , parent
                                    , meta_title
                                    , meta_description
                                    , keywords
                                    , h1
                                    , h2
                                    , pre_content
                                    , post_content
                                    , permalink
                                ) VALUES
                                (
                                    null
                                    , " . $db->toSql($ID_lang, "Number") . "
                                    , " . $db->toSql($ID_code, "Number") . "
                                    , 1
                                    , " . $db->toSql($component->form_fields["name_" . $name_lang]->value) . "
                                    , " . $db->toSql($smart_url) . "
                                    , " . $db->toSql($permalink_parent) . "
                                    , " . $db->toSql($component->form_fields["title_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["description_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["keywords_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["h1_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["h2_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["pre_content_" . $name_lang]->value) . "
                                    , " . $db->toSql($component->form_fields["post_content_" . $name_lang]->value) . "
                                    , " . $db->toSql($permalink) . "
                                )";
                    $db->execute($sSQL);
                    $ID_tag_page = $db->getInsertID(true);
                }
                $sSQL = "UPDATE search_tags
                            SET permalink = " . $db->toSql($permalink) . "
                                , ID_tag_page = " . $db->toSql($ID_tag_page, "Number") . "
                        WHERE smart_url = " . $db->toSql($smart_url) . "
                            AND ID_lang = " . $db->toSql($ID_lang, "Number");
                $db->execute($sSQL);                 
            }
            $component->user_vars["ID_code"] = $ID_code;
            break;
        case "confirmdelete":
            if($_REQUEST["keys"]["code"]) {
                $code = $_REQUEST["keys"]["code"];
                $sSQL = "DELETE FROM search_tags_page
                            WHERE code = " . $db->toSql($code, "Number");
                $db->execute($sSQL);
            } elseif($_REQUEST["keys"]["ID"]) {
                $sSQL = "SELECT code
                            FROM search_tags_page
                            WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $code = $db->getField("code", "Number", true);
                    $sSQL = "DELETE FROM search_tags_page
                                WHERE code = " . $db->toSql($code, "Number");
                    $db->execute($sSQL);
                }
            }
            $sSQL = "UPDATE search_tags
                        SET permalink = ''
                                , ID_tag_page = 0
                        WHERE ID_tag_page = " . $db->toSql($code, "Number");
            $db->execute($sSQL);
            $component->user_vars["ID_code"] = $code;
        default:
            break;
    }
}

function TagsPageModify_on_done_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    if(strlen($action)) 
    {
        switch ($action) 
        {
            case "insert":
            case "update":
                $sSQL = "SELECT ID_tag,ID_tag_group
                            FROM search_tags_page_rel_group
                            WHERE ID_tag_page = " . $db->toSql($component->user_vars["ID_code"], "Number");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    do {
                        $arrTag[$db->getField("ID_tag_group","Number",true)][$db->getField("ID_tag", "Number", true)] = $db->getField("ID_tag", "Number", true);
                    } while($db->nextRecord());
                }
                foreach($component->form_fields AS $key => $value) {
                    if(strpos($key, "tagGroupRelation") === 0) {
                        
                            $arrTagAdd = array();
                            $arrNewTag = array();
                            $ID_field = str_replace("tagGroupRelation_", "", $key);
                            
                            if(strlen($value->getValue()))
                                $arrNewTag = explode(",", $value->getValue());
                           
                            if(is_array($arrNewTag) && count($arrNewTag)) {
                                foreach($arrNewTag AS $key => $value) {
                                    if(is_array($arrTag[$ID_field]) && count($arrTag[$ID_field]) && array_key_exists($value, $arrTag))
                                        unset($arrTag[$ID_field][$value]);
                                    else
                                        $arrTagAdd[$value] = 0;
                                }
                            }
                            
                            if(is_array($arrTag[$ID_field]) && count($arrTag[$ID_field])) {
                                $sSQL = "DELETE FROM search_tags_page_rel_group
                                            WHERE ID_tag_group = " . $db->toSql($ID_field, "Number") . "
                                                AND ID_tag_page = " . $db->toSql($component->user_vars["ID_code"], "Number") . "
                                                AND ID_tag IN (" . $db->toSql(implode(",",$arrTag[$ID_field]), "Number") . ")"; 
                                $db->execute($sSQL);
                            }
                            
                            if(is_array($arrTagAdd) && count($arrTagAdd)) {
                                foreach($arrTagAdd AS $key => $value) {
                                    $sSQL = "INSERT INTO search_tags_page_rel_group
                                                (
                                                    ID
                                                    , ID_tag
                                                    , ID_tag_group
                                                    , ID_tag_page
                                                ) VALUES
                                                (
                                                    null
                                                    , " . $db->toSql($key, "Number") . "
                                                    , " . $db->toSql($ID_field, "Number") . "
                                                    , " . $db->toSql($component->user_vars["ID_code"], "Number") . "
                                                )";
                                    $db->execute($sSQL);
                                }
                            }
                        
                    }
                }
            break;
        }
    }
}