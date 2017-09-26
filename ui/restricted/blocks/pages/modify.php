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


if($_REQUEST["keys"]["ID"] > 0 && isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"])) {
	if(check_function("get_locale")) {
		$arrLang = get_locale("lang", true);
	}

    if(is_array($arrLang) && count($arrLang)) { 
        check_function("update_vgallery_seo");
        check_function("get_schema_fields_by_type");

		$src = get_schema_fields_by_type("page");
        foreach($arrLang AS $lang_code => $lang) {    
            update_vgallery_seo(
            	null
            	, $_REQUEST["keys"]["ID"]
            	, $lang["ID"]
            	, null
            	, null
            	, null
            	, $_REQUEST["setvisible"]
            	, null
            	, $src["seo"]
            	, $src["field"]
            	, ($lang["ID"] == LANGUAGE_DEFAULT_ID ? "primary" : null)
            );
        }
    } 
    
    if(check_function("refresh_cache")) {
	    refresh_cache($src["cache"]["type"]
            , $_REQUEST["keys"]["ID"]
            , ($_REQUEST["setvisible"] 
                ? "insert" 
                : "update"
            )
            , ($record["permalink"]
                ? $record["permalink"]
                : stripslash($record["parent"]) . "/" . $record["name"]
            )
        );
	}

	//if($_REQUEST["XHR_CTX_ID"]) {
	    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("PageModify")), true));
	//} else {
	//    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("PageModify")), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	//}
} 

$labelWidth = array(2,3,5,5);	                

$cm->oPage->tplAddJs("ff.cms.admin.static-modify");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PageModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "static_pages";
//$oRecord->addEvent("on_done_action", "StaticModify_on_done_action");
$oRecord->insert_additional_fields["visible"] = new ffData("1", "Number");
$oRecord->insert_additional_fields["owner"] =  new ffData(get_session("UserNID"), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->tab = true;	

/* Title Block */
system_ffcomponent_set_title(
	$record["name"]
	, true
	, false
	, false
	, $oRecord
);	

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->addContent(null, true, "general");
$oRecord->groups["general"] = array(
	"title" => ffTemplate::_get_word_by_code("page_general")
);	

if($globals->ID_domain > 0) {
	$oRecord->insert_additional_fields["ID_domain"] =  new ffData($globals->ID_domain, "Number");
} else {
	$sSQL = "SELECT cache_page_alias.* FROM cache_page_alias WHERE status > 0";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_domain";
		$oField->label = ffTemplate::_get_word_by_code("page_modify_domain");
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->source_SQL = "SELECT cache_page_alias.ID
									, cache_page_alias.host
								FROM cache_page_alias
								WHERE cache_page_alias.status > 0
								ORDER BY cache_page_alias.host";
		$oField->actex_update_from_db = true;
		$oRecord->addContent($oField, "general");
	}	
}	

$root_path = "/";
$is_owner = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("page_mpdify_parent");
$oField->source_SQL = " SELECT
							IF(static_pages.parent = '/', CONCAT( static_pages.parent, static_pages.name ), CONCAT( static_pages.parent, '/', static_pages.name )) AS ID
							, IF(static_pages.name = ''
								, '/'
								, " . ($root_path == "/"
									? " CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name )"
									: " CONCAT(IF(LOCATE(" . $db->toSql($root_path) . ", CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name)) = 1
												, SUBSTRING(CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name, '/'), " . $db->toSql(strlen($root_path) + 1, "Number") . ")
												, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name)
											)
										)	"
								) . "
							) AS name
						FROM static_pages 
	                    WHERE 1
						" . ($globals->ID_domain > 0
							? " AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
							: ""
						) . "
	                    " . ($is_owner
                        	? "AND static_pages.owner = " . $db->toSql(get_session("UserNID"), "Number")
                        	: ""
	                    ) . "
						[AND] [WHERE]
		                [HAVING]
	                    [ORDER] [COLON] name
	                    [LIMIT]";
$oField->multi_select_one = false;
$oField->actex_on_refill = 'function(obj) {
	ff.cms.admin.makeNewUrl("INPUT.title-page:first", "INPUT.alt-url:first", obj.value);
}';
$oField->actex_on_change = "function(obj, old_value, action) {
	if(action == 'change') {
		ff.cms.admin.makeNewUrl(undefined, undefined, obj.value);
	}
}";
$oField->widget = "actex";
$oField->actex_update_from_db = true; 
$oField->required = true;
$oField->default_value = new ffData($_REQUEST["path"]);
$oRecord->addContent($oField, "general");	

if(check_function("get_schema_fields_by_type"))
	$src = get_schema_fields_by_type("page");

if($_REQUEST["keys"]["ID"] && isset($_REQUEST["repair"])) {
	$sSQL = "DELETE FROM static_pages_rel_languages  WHERE `ID_static_pages` = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . " AND ID NOT IN(
				SELECT * FROM (
				  SELECT ID FROM `static_pages_rel_languages` WHERE `ID_static_pages` = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . " GROUP BY ID_languages
			    ) AS tbl_src
			)";
	$db->execute($sSQL);

	$sSQL = "DELETE FROM static_pages_rel_languages  WHERE `ID_static_pages` = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . " AND ID_languages = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number");
	$db->execute($sSQL);	
}
	
if($src["seo"]["primary_table"] != $src["seo"]["table"] && $src["seo"]["rel_lang"]) {
	$sSQL_field = array();

	if($src["seo"]["rel_field"]) {
		$sSQL_field["ID"] = " 0 AS `ID`";
	} else {
		$sSQL_field["ID"] = " (
			                SELECT `ID` 
			                FROM " . $src["seo"]["table"] . " 
			                WHERE `" . $src["seo"]["rel_key"] . "` = [ID_FATHER] 
			                    AND `" . $src["seo"]["rel_lang"] . "` = " . FF_PREFIX . "languages.ID
			            )";
		$sSQL_field["ID"] = " IF(" . FF_PREFIX . "languages.ID = " . LANGUAGE_DEFAULT_ID . "
			, 0
			, " . $sSQL_field["ID"]  . "
		) AS `ID`";
	}
	$arrFieldPrimary = array(
		"title" 				=> array($src["seo"]["title"], $src["seo"]["smart_url"])
		, "header" 				=> array($src["seo"]["header"], $src["seo"]["title"])
	);
	$arrField = array(
		"alt_url" 				=> $src["seo"]["alt_url"]
		, "permalink" 			=> "CONCAT(IF(" . $src["seo"]["permalink_parent"] . " = '/', '', " . $src["seo"]["permalink_parent"] . "), '/', " . $src["seo"]["smart_url"] . ")"
		, "permalink_parent" 	=> $src["seo"]["permalink_parent"]
		, "smart_url" 			=> $src["seo"]["smart_url"]
		, "title" 				=> array($src["seo"]["title"], $src["seo"]["smart_url"])
		, "header" 				=> array($src["seo"]["header"], $src["seo"]["title"])
		, "description" 		=> $src["seo"]["description"]
		, "keywords" 			=> $src["seo"]["keywords"]
	);
	if(is_array($arrField) && count($arrField))	{
		foreach($arrField AS $field_key => $field_name) {
			if($src["seo"]["rel_field"] && is_array($field_name)) {
				$sSQL_field[$field_key] = " (
					SELECT `" . $field_name["field"] . "`
					FROM " . $src["seo"]["table"] . " 
					WHERE `" . $src["seo"]["rel_key"] . "` = [ID_FATHER] 
					    AND `" . $src["seo"]["rel_field"] . "` = " . $field_name["field"]["where"] . "
					    AND `" . $src["seo"]["rel_lang"] . "` = " . FF_PREFIX . "languages.ID
					)";
			} else {
                if(is_array($field_name) && count($field_name)) {
                    $field_data = "";
                    $arrFieldName = array_filter($field_name);
                    foreach($arrFieldName AS $field_sub_name) {
                        $field_data .= "IF(`" . $field_sub_name . "` <> '', `" . $field_sub_name . "`, ";
                    }
                    $field_data .= "''" . str_repeat(")", count($arrFieldName));
                } else {
                    $field_data = "" . $field_name . "";
                }
                
				$sSQL_field[$field_key] = " (
						SELECT " . $field_data . " 
						FROM " . $src["seo"]["table"] . " 
						WHERE `" . $src["seo"]["rel_key"] . "` = [ID_FATHER] 
						    AND `" . $src["seo"]["rel_lang"] . "` = " . FF_PREFIX . "languages.ID
					)";
			}
			$field_data_primary = "";
			if($arrFieldPrimary[$field_key]) {
	             if(is_array($arrFieldPrimary[$field_key]) && count($arrFieldPrimary[$field_key])) {
	             	$arrFieldName = array_filter($field_name);
                    foreach($arrFieldPrimary[$field_key] AS $field_sub_name) {
                        $field_data_primary .= "IF(`" . $field_sub_name . "` <> '', `" . $field_sub_name . "`, ";
                    }
                    $field_data_primary .= "''" . str_repeat(")", count($arrFieldName));
                } else {
                    $field_data_primary = "" . $arrFieldPrimary[$field_key] . "";
                }
			}
			
			
			$sSQL_field[$field_key] = " IF(" . FF_PREFIX . "languages.ID = " . LANGUAGE_DEFAULT_ID . "
				, (
					SELECT " . ($field_data_primary
						? $field_data_primary
						: "`" . $src["field"][$field_key] . "`"
					) . " 
					FROM " . $src["seo"]["primary_table"] . " 
					WHERE `ID` = [ID_FATHER] 
				)
				, " . $sSQL_field[$field_key] . "
			) AS `" . $field_key . "`";
			
		}
	}
}

$oDetail = ffDetails::factory($cm->oPage);
if(check_function("get_locale")) {
	$arrLang = get_locale("lang", true);
	if(count($arrLang) > 1) {
		$oDetail->tab = "right";
    	$oDetail->tab_label = "language";
        $oDetail->display_group_title = false;
	}
}

$oDetail->id = "PageModifyLanguages";
$oDetail->src_table = "static_pages_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_static_pages" => "ID");
$oDetail->addEvent("on_do_action", "PageModify_on_do_action");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
	                                " . FF_PREFIX . "languages.ID AS ID_languages
	                                , " . FF_PREFIX . "languages.description AS language
	                                , " . FF_PREFIX . "languages.code AS code_lang
	                               	, '' AS title
	                               	, '' AS description
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                    " . FF_PREFIX . "languages.status > 0
                                ORDER BY IF(" . FF_PREFIX . "languages.ID = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
                                	, 0
                                	, 1
                                )
                                , " . FF_PREFIX . "languages.code";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
	                                " . FF_PREFIX . "languages.ID AS ID_languages
	                                , " . FF_PREFIX . "languages.description AS language
	                                , " . FF_PREFIX . "languages.code AS code_lang
	                                " . (is_array($sSQL_field) && count($sSQL_field)
                                    	? ", " . implode(", ", $sSQL_field)
                                    	: ""
	                                ) . "
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                    " . FF_PREFIX . "languages.status > 0
                                ORDER BY IF(" . FF_PREFIX . "languages.ID = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
                                	, 0
                                	, 1
                                )
                                , " . FF_PREFIX . "languages.code";
                                
//$oDetail->addEvent("on_do_action", "SeoModifyField_on_do_action");
$oDetail->user_vars["src"] = $src;
//$oDetail->skip_action = true;  //se abilitato nn cancella i contenuti della tabella accessoria in fase di confirm delete
	    
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);  	    
	                                
$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_title");
$oField->store_in_db = false;
$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10);";
$oField->placeholder = false;
if(!$cm->isXHR())
	$oField->setWidthLabel($labelWidth);
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_description");
$oField->store_in_db = false;
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->control_type = "textarea";
$oField->properties["maxlength"] = 255;
$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 160, 70);";
$oField->placeholder = false;
if(!$cm->isXHR())
	$oField->setWidthLabel($labelWidth);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "general");
$cm->oPage->addContent($oDetail);







$oRecord->addContent(null, true, "advanced");
$oRecord->groups["advanced"] = array(
	"title" => ffTemplate::_get_word_by_code("page_advanced")
);

$oField = ffField::factory($cm->oPage);
$oField->id = "location";
$oField->label = ffTemplate::_get_word_by_code("static_page_location");
$oField->extended_type = "Selection";
$oField->widget = "actex";
$oField->actex_autocomp = true;	
$oField->actex_multi = true;
$oField->actex_update_from_db = true;	
/*
$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare = "name"; 
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_delimiter = ",";
$oField->autocompletetoken_combo = true;*/

$oField->source_SQL = "SELECT name, name
    				FROM layout_location
					WHERE 1
					[AND] [WHERE]
					[ORDER] [COLON] interface_level, name
					[LIMIT]";
$oField->default_value = new ffData($location);
$oRecord->addContent($oField, "advanced");

if(!ENABLE_STD_PERMISSION  && !ENABLE_ADV_PERMISSION) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "permission";
	$oField->label = ffTemplate::_get_word_by_code("static_page_permission");
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT gid, name
    					FROM " . CM_TABLE_PREFIX . "mod_security_groups
						[WHERE]
						ORDER BY name";
	$oField->widget = "actex";
	$oField->actex_autocomp = true;	
	$oField->actex_multi = true;
	$oField->actex_update_from_db = true;	
	/*
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare = "name"; 
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_delimiter = ",";
	$oField->autocompletetoken_combo = true;*/
	
	$oRecord->addContent($oField, "advanced");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "use_ajax";
$oField->container_class = "use-ajax";
$oField->label = ffTemplate::_get_word_by_code("static_page_use_ajax");
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oField->properties["onchange"] = "javascript:ff.cms.admin.UseAjax();";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "ajax_on_event";
$oField->container_class = "use-ajax-dep on-event";
$oField->label = ffTemplate::_get_word_by_code("static_page_ajax_on_event");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
							array(new ffData("load fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fade"))),
							array(new ffData("load show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_show"))),
							array(new ffData("load fadeToggle"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fadeToggle"))),
							array(new ffData("load hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_hide"))),
							array(new ffData("reload show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_show"))), 
							array(new ffData("reload fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_fade"))),
							array(new ffData("reload hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_hide")))
					   );
$oField->default_value = new ffData("load fadeIn");
$oField->multi_select_one = false;
$oRecord->addContent($oField, "advanced");	


$cm->oPage->addContent($oRecord);



function PageModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory(); 

    switch($action) {
        case "insert":
        case "update":
        	$arrTag = array();

        	$ID_node = $component->main_record[0]->key_fields["ID"]->value;
			$sSQL = "SELECT `" . $component->user_vars["src"]["table"] . "`.*
			            FROM `" . $component->user_vars["src"]["table"] . "` 
			            WHERE `" . $component->user_vars["src"]["table"] . "`.ID = " . $db->toSql($ID_node);
			$db->query($sSQL);
			if($db->nextRecord()) {
				if($component->user_vars["src"]["field"]["ID_category"])
					$ID_category = $db->getField($component->user_vars["src"]["field"]["ID_category"], "Number", true);
				if($component->user_vars["src"]["field"]["parent"])
					$actual_path = $db->getField($component->user_vars["src"]["field"]["parent"], "Text", true);
				if($component->user_vars["src"]["field"]["name"])
					$item_name = $db->getField($component->user_vars["src"]["field"]["name"], "Text", true);
			}       
			//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
        	//DISABLE_SMARTURL_CONTROL
        	/*if(isset($component->form_fields["smart_url"])) {
				if(is_array($component->recordset) && count($component->recordset)) {
				    foreach($component->recordset AS $rst_key => $rst_value) {
				    	if(strlen($component->recordset[$rst_key]["smart_url"]->getValue()))
				    		continue;

				    	if($component->recordset[$rst_key]["ID_languages"]->getValue() == LANGUAGE_DEFAULT_ID) {
 							$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty") . " (" . $component->recordset[$rst_key]["code_lang"]->getValue() . ")");
			                return true;
				    	} elseif(!DISABLE_SMARTURL_CONTROL) {
 							$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty") . " (" . $component->recordset[$rst_key]["code_lang"]->getValue() . ")");
			                return true;
				    	}
					}
				}
			}*/

			if(is_array($component->recordset) && count($component->recordset)) {
			    foreach($component->recordset AS $rst_key => $rst_value) {
					if(isset($rst_value["keywords"])) {
		                $str_compare_keywords = "";
		                $arrTags = explode(",", $rst_value["keywords"]->getValue());
		                if(is_array($arrTags) && count($arrTags)) {
		                    foreach($arrTags AS $tag_value) {
		                        $tag_value = trim($tag_value);
		                        if(strlen($tag_value)) {
									$sSQL = "SELECT search_tags.* 
											FROM search_tags 
											WHERE search_tags.smart_url = " . $db->toSql(ffCommon_url_rewrite($tag_value)) . "
												AND search_tags.ID_lang = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
									$db->query($sSQL) ;
									if($db->nextRecord()) {
										$ID_tag = $db->getField("ID", "Number", true);
									} else {
		                                $sSQL = "INSERT INTO search_tags
		                                        (
		                                            ID
		                                            , name
		                                            , smart_url
		                                            , ID_lang
		                                            , code
		                                            , status
		                                        ) VALUES (
		                                            null
		                                            , " . $db->toSql($tag_value) . "
		                                            , " . $db->toSql(ffCommon_url_rewrite($tag_value)) . "
		                                            , " . $db->toSql($component->recordset[$rst_key]["ID_languages"]) . "
		                                            , ''
		                                            , 0
		                                        )";
		                                $db->execute($sSQL);
		                                $ID_tag = $db->getInsertID(true);
		                            }

		                            if(strlen($str_compare_keywords))
		                                $str_compare_keywords .= ",";

		                            $str_compare_keywords .= $db->toSql($tag_value, "Number");
		                            
		                            $arrTag[$ID_tag] = $ID_tag;
		                        }                
		                    }
		                }
		                $component->recordset[$rst_key]["keywords"]->setValue($str_compare_keywords);
		            } 

		            if(check_function("update_vgallery_seo")) {
		            	$seo_update = update_vgallery_seo(
		            		array(
		            			"smart_url" => ($component->user_vars["src"]["seo"]["smart_url"] && isset($rst_value["smart_url"])
		            				? $rst_value["smart_url"]->getValue()
		            				: null
		            			)
		            			, "title" => ($component->user_vars["src"]["seo"]["title"] && isset($rst_value["title"])
		            				? $rst_value["title"]->getValue()
		            				: null
		            			)
		            			, "header" => ($component->user_vars["src"]["seo"]["header"] && isset($rst_value["header"])
		            				? $rst_value["header"]->getValue()
		            				: null
		            			)
		            		)
		            		, $ID_node->getValue()
		            		, $rst_value["ID_languages"]->getValue()
		            		, ($component->user_vars["src"]["seo"]["description"] && isset($rst_value["description"])
		            			? $rst_value["description"]->getValue()
		            			: null
		            		)
		            		, null
		            		, ($component->user_vars["src"]["seo"]["keywords"] && isset($rst_value["keywords"])
		            			? $rst_value["keywords"]->getValue()
		            			: null
		            		)
		            		, null
		            		, null
		            		, $component->user_vars["src"]["seo"]
		            		, $component->user_vars["src"]["field"]
		            		, ($rst_value["ID_languages"]->getValue() == LANGUAGE_DEFAULT_ID ? "primary" : null)
		            		, ($component->user_vars["src"]["seo"]["alt_url"] && isset($rst_value["alt_url"])
		            			? $rst_value["alt_url"]->getValue()
		            			: null
		            		)
		            		, ($component->user_vars["src"]["seo"]["permalink_parent"] && isset($rst_value["permalink_parent"])
		            			? $rst_value["permalink_parent"]->getValue()
		            			: null
		            		)
		            	);
					}

					if(check_function("set_field_permalink"))
						$arrPermalink = set_field_permalink($component->user_vars["src"]["table"], $ID_node->getValue(), false, false);

					if(check_function("refresh_cache")) {
						refresh_cache($component->user_vars["src"]["cache"]["type"], $ID_node->getValue(), $action, stripslash($actual_path) . "/" . $item_name);
					}					
				}
			}
        	
			if(is_array($arrTag) && count($arrTag)) {
				$arrTagNew = array();
				$sSQL = "SELECT search_tags.*
						FROM search_tags
						WHERE search_tags.ID IN(" . $db->toSql(implode(",", $arrTag), "Text", false) . ")
						GROUP BY IF(search_tags.code = ''
									, search_tags.ID
									, search_tags.code
								)";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$arrTagNew[] = $db->getField("ID", "Number", true);
					} while($db->nextRecord());
				}
				
				if(is_array($arrTagNew) && count($arrTagNew)) {
					$sSQL = "UPDATE `" . $component->user_vars["src"]["table"] . "` SET
								tags = " . $db->toSql(implode(",", $arrTagNew)) . "
			                WHERE `" . $component->user_vars["src"]["table"] . "`.ID = " . $db->toSql($ID_node);
					$db->execute($sSQL);
				}
			}  
			
        //ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());  
        return false;
        //break;
    }
    

}