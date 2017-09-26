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
 if (!AREA_SEO_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
$_REQUEST["ret_url"] = $_SERVER["REQUEST_URI"];

$src_type = ($_REQUEST["type"]
	? $_REQUEST["type"]
	: $cm->real_path_info
);
if(!$src_type)
	$src_type = "vgallery";

$ID_node = $_REQUEST["key"];

if(!is_array($src) && check_function("get_schema_fields_by_type"))
	$src = get_schema_fields_by_type($src_type);

$key = ($src["key"]
	? $src["key"]
	: "ID"
);
	
if($ID_node && is_array($src) && count($src) && $src["table"]) {
	$sSQL = "SELECT `" . $src["table"] . "`.*
			FROM `" . $src["table"]. "`
			WHERE `" . $src["table"]. "`.`" . $key . "` = " . $db->toSql($ID_node, "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
		if($src["field"]) {
			$record["name"] = $db->getField($src["field"]["title"], "Text", true);
			if(!$record["name"])
				$record["name"] = ucwords(str_replace("-" , " " , $db->getField($src["field"]["smart_url"], "Text", true)));
		} else {
			$record["name"] = ffTemplate::_get_word_by_code("seo_modify_" . $src["type"]);
		}
	}
}

if(!isset($_REQUEST["keys"]["ID"]) || !$src["table"])
	$strError = ffTemplate::_get_word_by_code("page_not_found");

if(check_function("set_field_permalink"))
	$arrPermalink = set_field_permalink($src["table"], $_REQUEST["keys"]["ID"], false, true);

$node_url = $arrPermalink[LANGUAGE_DEFAULT];
if(!$node_url || $node_url == "/")
	$disable_smart_url = true;	
	
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SeoModify";
$oRecord->ajax = true;
$oRecord->resources[] = $oRecord->id;
if(strlen($strError)) {
	$oRecord->strError = $strError;
	$oRecord->hide_all_controls = true;
	$record["name"] = ffTemplate::_get_word_by_code("record_not_found");
}

if(!$_REQUEST["grp"] && check_function("system_ffcomponent_set_title")) {
	system_ffcomponent_set_title(
		$record["name"]
		, array(
			"name" => $src["icon"]
			, "type" => $src["class"]
		)
		, false
		, $node_url
		, $oRecord
	);
}

$oRecord->src_table = $src["table"];
$oRecord->addEvent("on_done_action", function($component, $action) {
	if($action == "insert" || $action == "update") {
		return true;
	}
});

$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["insert"]["display"] = false;
if(!$cm->isXHR())
	$oRecord->buttons_options["cancel"]["display"] = false;
if($src["field"]["last_update"])
	$oRecord->additional_fields[$src["field"]["last_update"]] = new ffData(time(), "Number");
$oRecord->setWidthComponent(array(8, 8, 12, 12)); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = $key;
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(!$strError) {
	if($src["parent_editable"] && $src["field"]["parent"]) {
		$root_path = "/";

		$oField = ffField::factory($cm->oPage);
		$oField->id = $src["field"]["parent"];
		$oField->label = ffTemplate::_get_word_by_code("seo_parent");
		$oField->widget = "actex";
		$oField->actex_update_from_db = true;
		$oField->required = true;
		//$oField->actex_autocomp = true;
			
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
	    $oField->setWidthLabel(2);           
		$oRecord->addContent($oField);
	}

	//if(!$cm->isXHR())	                cambia la visualizzazione neille pagine incluse
		$labelWidth = array(2,3,5,5);	                

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
			, "permalink" 			=> ($src["seo"]["permalink_parent"] && $src["seo"]["smart_url"]
				? "CONCAT(IF(" . $src["seo"]["permalink_parent"] . " = '/', '', " . $src["seo"]["permalink_parent"] . "), '/', " . $src["seo"]["smart_url"] . ")"
				: false 
			)
			, "permalink_parent" 	=> $src["seo"]["permalink_parent"]
			, "smart_url" 			=> $src["seo"]["smart_url"]
			, "title" 				=> array($src["seo"]["title"], $src["seo"]["smart_url"])
			, "header" 				=> array($src["seo"]["header"], $src["seo"]["title"])
			, "description" 		=> $src["seo"]["description"]
            , "keywords" 			=> $src["seo"]["keywords"]

            , "robots"              => $src["seo"]["robots"]
            , "canonical" 			=> $src["seo"]["canonical"]
            , "meta"                => $src["seo"]["meta"]
            , "httpstatus" 			=> $src["seo"]["httpstatus"]            
		);
		if(is_array($arrField) && count($arrField))	{
			foreach($arrField AS $field_key => $field_name) {
				if(!$field_name)
					continue;

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
				
				if($src["key"] == $src["seo"]["rel_key"]) 
					$sSQL_field[$field_key] .= " AS `" . $field_key . "`";						    
				else
					$sSQL_field[$field_key] = " IF(" . FF_PREFIX . "languages.ID = " . LANGUAGE_DEFAULT_ID . "
				    	, (
							SELECT " . ($src["field"][$field_key]
					    		? "`" . $src["field"][$field_key] . "`"
					    		: "''"
							) . " 
							FROM " . $src["seo"]["primary_table"] . " 
							WHERE `ID` = [ID_FATHER] 
						)
						, " . $sSQL_field[$field_key] . "
					) AS `" . $field_key . "`";				
			}
		}

		$oDetail = ffDetails::factory($cm->oPage);
		if(check_function("get_locale")) {
			$locale = get_locale(null, true);
			$count_lang = count($locale["lang"]);
			if($count_lang > 1) {
			    $oDetail->tab = "default";
    			$oDetail->tab_label = "language";
				$oDetail->framework_css["widget"]["tab"]["pane-item"]["class"] = "seo-page";	        
	        } else {
        		$oDetail->class .= " seo-page";
			}
		}
        $oDetail->id = "SeoModifyField";
        $oDetail->src_table = $src["seo"]["table"];
        $oDetail->order_default = "ID";
        $oDetail->fields_relationship = array($src["seo"]["rel_key"] => "ID");
        $oDetail->display_new = false;
        $oDetail->display_delete = false;
        $oDetail->auto_populate_insert = true;
        $oDetail->populate_insert_SQL = "SELECT 
	                                        " . FF_PREFIX . "languages.ID AS ID_languages
	                                        , " . FF_PREFIX . "languages.description AS language
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
        $oDetail->auto_populate_edit = true;
        $oDetail->populate_edit_SQL = "SELECT 
	                                        " . FF_PREFIX . "languages.ID AS ID_languages
	                                        , " . FF_PREFIX . "languages.description AS language
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

        $oDetail->addEvent("on_do_action", "SeoModifyField_on_do_action");
        $oDetail->user_vars["src"] = $src;
        $oDetail->user_vars["locale"] = $locale;
        $oDetail->framework_css["widget"]["tab"]["pane-item"]["class"] = "seo-page";
		//$oDetail->skip_action = true;  //se abilitato nn cancella i contenuti della tabella accessoria in fase di confirm delete
        //$oDetail->framework_css["component"]["type"] = "inline";
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oDetail->addKeyField($oField);  

        $oField = ffField::factory($cm->oPage);
        $oField->id = "language";
        $oField->store_in_db = false;
        $oDetail->addHiddenField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_languages";
        $oField->base_type = "Number";
        $oDetail->addHiddenField($oField);

        if(!$disable_smart_url) {
		    if($arrField["permalink"]) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "permalink";
			    $oField->container_class = "permalink page-meta";
			    $oField->label = ffTemplate::_get_word_by_code("seo_permalink");
			    $oField->store_in_db = false;
			    $oField->placeholder = false;
			    //$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
			    $oField->control_type = "label";
			    if($labelWidth)
			    	$oField->setWidthLabel($labelWidth);
			    $oDetail->addContent($oField);	
		    }	
		    if($arrField["smart_url"]) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "smart_url";
			    $oField->container_class = "smart-url page-meta check-keyup check-keywords";
			    $oField->label = ffTemplate::_get_word_by_code("seo_smart_url");
			    $oField->store_in_db = false;
			    $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
			    $oField->placeholder = false;
			    if($labelWidth)
			    	$oField->setWidthLabel($labelWidth);
			    $oDetail->addContent($oField);
		    }
	    }
	    if($arrField["title"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "title";
	        $oField->container_class = "meta-title page-meta check-keyup check-keywords";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_title");
	        $oField->store_in_db = false;
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10, 'check-keyup');";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
	    }
	    if($arrField["header"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "header";
	        $oField->container_class = "header page-meta check-keyup";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_title_alt");
	        $oField->store_in_db = false;
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10, 'check-keyup');";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
	    }
	    if($arrField["description"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "description";
	        $oField->container_class = "meta-desc page-meta check-keyup check-keywords";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_description");
	        $oField->store_in_db = false;
	        $oField->base_type = "Text";
	        $oField->extended_type = "Text";
	        $oField->control_type = "textarea";
	        $oField->properties["maxlength"] = 255;
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 160, 70, 'check-keyup');";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
	    }
	    if($arrField["keywords"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = "keywords";
	        $oField->container_class = "meta-keywords page-meta helper";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_keywords");
	        $oField->store_in_db = false;
	        $oField->description = '<a class="' . cm_getClassByFrameworkCss("help", "icon") . '" href="javascript:void(0);"></a><p class="hidden helper-content">' . ffTemplate::_get_word_by_code("seo_meta_keywords_help") . '</p>';
	        //$oField->extended_type = "Selection";
	        $oField->source_SQL = "SELECT search_tags.name
	                                    , search_tags.name 
	                                FROM search_tags
	                                WHERE search_tags.ID_lang = " 
                        		        . ($count_lang > 1 
                        			        ? "[ID_languages_VALUE]" 
                        			        : $db->toSql(LANGUAGE_DEFAULT_ID, "Number")
                        		        ) . "
	                                    [AND] [WHERE] 
	                                [ORDER] [COLON] search_tags.name
	                                [LIMIT]";
	        $oField->widget = "autocomplete";
	        $oField->multi_select_one = false;
	        $oField->actex_update_from_db = true;
	        $oField->autocomplete_multi = true;
	        $oField->autocomplete_readonly = false;
	        $oField->autocomplete_minLength = 0;
	        $oField->autocomplete_combo = true; 
	        $oField->autocomplete_compare = "name";
	        $oField->grouping_separator = ",";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
	    }
        if($arrField["meta"]) {
            if(0) {
                /**
                 * Social Meta
                 */
                if ($arrField["og:image"]) {
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = "og:image";
                    //$oField->container_class = "header page-meta check-keyup";
                    $oField->label = ffTemplate::_get_word_by_code("seo_meta_og_image");
                    $oField->store_in_db = false;
                    $oField->placeholder = false;
                    $oField->setWidthLabel(5);
                    $oDetail->addContent($oField);
                }
                if ($arrField["og:title"]) {
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = "og:title";
                    $oField->container_class = "meta-title page-meta check-keyup check-keywords";
                    $oField->label = ffTemplate::_get_word_by_code("seo_meta_og_title");
                    $oField->store_in_db = false;
                    $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 75, 10, 'check-keyup');";
                    $oField->placeholder = false;
                    $oField->setWidthLabel(5);
                    $oDetail->addContent($oField);
                }
                if ($arrField["og:description"]) {
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = "og:description";
                    $oField->container_class = "meta-desc page-meta check-keyup check-keywords";
                    $oField->label = ffTemplate::_get_word_by_code("seo_meta_og_description");
                    $oField->store_in_db = false;
                    $oField->base_type = "Text";
                    $oField->extended_type = "Text";
                    $oField->control_type = "textarea";
                    $oField->properties["maxlength"] = 255;
                    $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 165, 70, 'check-keyup');";
                    //$oField->description = '<span class="countChar"></span>';
                    $oField->placeholder = false;
                    $oField->setWidthLabel(5);
                    $oDetail->addContent($oField);
                }
            } else {
                $oField = ffField::factory($cm->oPage);
                $oField->id = $arrField["meta"];
                $oField->container_class = "meta page-meta";
                $oField->label = ffTemplate::_get_word_by_code("seo_meta_other");
                $oField->base_type = "Text";
                $oField->extended_type = "Text";
                $oField->control_type = "textarea";
                $oField->placeholder = false;
                $oField->setWidthLabel(5);
                $oDetail->addContent($oField);
            }
        }
        if($arrField["robots"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["robots"];
	        $oField->container_class = "robots page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_robots");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = array(
                                        array(new ffData("noindex, follow"), new ffData("noindex, follow")),
                                        array(new ffData("noindex, nofollow"), new ffData("noindex, nofollow")),
                                        array(new ffData("index, nofollow"), new ffData("index, nofollow"))
                                    );
            $oField->multi_select_one_label = "index, follow";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
        }
	    if($arrField["httpstatus"]) {
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["httpstatus"];
	        $oField->container_class = "httpstatus page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_httpstatus");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = ffGetHTTPStatus();
            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
        }  
        if($arrField["canonical"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["canonical"];
	        $oField->container_class = "canonical page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_canonical");
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oDetail->addContent($oField);
	    }
        
      	if(!$disable_smart_url) {
	        if($arrField["alt_url"]) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "alt_url";
			    $oField->container_class = "check-keyup";
			    $oField->label = ffTemplate::_get_word_by_code("seo_alt_url");
			    $oField->store_in_db = false;
			    $oField->properties["onkeyup"] = "if(jQuery(this).val().length) { jQuery(this).closest('.seo-page').find('.page-meta').fadeOut(); } else { jQuery(this).closest('.seo-page').find('.page-meta').fadeIn(); }";
			    $oField->placeholder = false;
			    if($labelWidth)
			    	$oField->setWidthLabel($labelWidth);
			    $oDetail->addContent($oField);
		    }
		}

        $oRecord->addContent($oDetail);
        $cm->oPage->addContent($oDetail);
    } else {

	    $arrField = array(
		    "alt_url" 				=> $src["field"]["alt_url"]
		    , "permalink" 			=> array($src["field"]["permalink_parent"], $src["field"]["smart_url"])
		    , "permalink_parent" 	=> $src["field"]["permalink_parent"]
		    , "smart_url" 			=> $src["field"]["smart_url"]
		    , "title" 				=> $src["field"]["title"]
		    , "header" 				=> $src["field"]["header"]
		    , "description" 		=> $src["field"]["description"]
		    , "keywords" 			=> $src["field"]["keywords"]
            
            , "robots"              => $src["field"]["robots"]
            , "canonical" 			=> $src["field"]["canonical"]
            , "meta"                => $src["field"]["meta"]
            , "httpstatus" 			=> $src["field"]["httpstatus"]
	    );
	    
	    if(!$disable_smart_url) {
	        if($arrField["permalink"]) {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["permalink"];
		        $oField->container_class = "permalink page-meta";
		        $oField->label = ffTemplate::_get_word_by_code("seo_permalink");
		        $oField->placeholder = false;
		        //$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
		        if($labelWidth)
		        	$oField->setWidthLabel($labelWidth);
		        $oRecord->addContent($oField);    
		    }  
		    if($arrField["smart_url"]) {   
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["smart_url"];
		        $oField->container_class = "smart-url page-meta check-keyup check-keywords";
		        $oField->label = ffTemplate::_get_word_by_code("seo_smart_url");
		        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
		        $oField->placeholder = false;
		        if($labelWidth)
		        	$oField->setWidthLabel($labelWidth);
		        $oRecord->addContent($oField);
		    }
	    }
	    if($arrField["title"]) {   
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["title"];
	        $oField->container_class = "meta-title page-meta check-keyup check-keywords";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_title");
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10, 'check-keyup');";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["header"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["header"];
	        $oField->container_class = "header page-meta check-keyup";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_title_alt");
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10, 'check-keyup');";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["description"]) {   
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["description"];
	        $oField->container_class = "meta-desc page-meta check-keyup check-keywords";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_description");
	        $oField->base_type = "Text";
	        $oField->extended_type = "Text";
	        $oField->control_type = "textarea";
	        $oField->properties["maxlength"] = 255;
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 160, 70, 'check-keyup');";
	        //$oField->description = ffTemplate::_get_word_by_code("rel_static_pages_meta_description_left_character");
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["keywords"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["keywords"];
	        $oField->container_class = "meta-keywords page-meta helper";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_keywords");
	        //$oField->description = '<a class="' . cm_getClassByFrameworkCss("help", "icon") . '" href="javascript:void(0);"></a><p class="hidden helper-content">' . ffTemplate::_get_word_by_code("seo_meta_keywords_help") . '</p>';
	        //$oField->extended_type = "Selection";
	        $oField->source_SQL = "SELECT search_tags.name
	                                    , search_tags.name 
	                                FROM search_tags
	                                WHERE search_tags.ID_lang = " 
	                                    . ($count_lang > 1 
	                                        ? "[ID_languages_VALUE]" 
	                                        : $db->toSql(LANGUAGE_DEFAULT_ID, "Number")
	                                    ) . "
	                                    [AND] [WHERE] 
	                                [ORDER] [COLON] search_tags.name
	                                [LIMIT]";
	        $oField->widget = "autocomplete";
	        $oField->multi_select_one = false;
	        $oField->actex_update_from_db = true;
	        $oField->autocomplete_multi = true;
	        $oField->autocomplete_readonly = false;
	        $oField->autocomplete_minLength = 0;
	        $oField->autocomplete_combo = true; 
	        $oField->autocomplete_compare = "name";
	        $oField->grouping_separator = ",";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);    
	    }
	    if($arrField["robots"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["robots"];
	        $oField->container_class = "robots page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_robots");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = array(
                                        array(new ffData("noindex, follow"), new ffData("noindex, follow")),
                                        array(new ffData("noindex, nofollow"), new ffData("noindex, nofollow")),
                                        array(new ffData("index, nofollow"), new ffData("index, nofollow"))
                                    );
            $oField->multi_select_one_label = "index, follow";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
        }
	    if($arrField["meta"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["meta"];
	        $oField->container_class = "meta page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_other");
	        $oField->base_type = "Text";
	        $oField->extended_type = "Text";
	        $oField->control_type = "textarea";
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["httpstatus"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["httpstatus"];
	        $oField->container_class = "httpstatus page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_httpstatus");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = ffGetHTTPStatus();
            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
        }  
        if($arrField["canonical"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["canonical"];
	        $oField->container_class = "canonical page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_canonical");
	        $oField->placeholder = false;
	        if($labelWidth)
	        	$oField->setWidthLabel($labelWidth);
	        $oRecord->addContent($oField);
	    }
        
	    if(!$disable_smart_url) {
	        if($arrField["alt_url"]) {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["alt_url"];
		        $oField->container_class = "check-keyup";
		        $oField->label = ffTemplate::_get_word_by_code("seo_alt_url");
		        $oField->properties["onkeyup"] = "if(jQuery(this).val().length) { jQuery(this).closest('.seo-page').find('.page-meta').fadeOut(); } else { jQuery(this).closest('.seo-page').find('.page-meta').fadeIn(); }";
		        $oField->placeholder = false;
		        if($labelWidth)
		        	$oField->setWidthLabel($labelWidth);
		        $oRecord->addContent($oField);
		    }
		}	    
    }
}
if($_REQUEST["grp"]) {
	$cm->oPage->addContent($oRecord, $_REQUEST["grp"], null, array("title" => ffTemplate::_get_word_by_code($_REQUEST["grp"] . "_" . $oRecord->id)));
	//$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(9), "push") . " " . cm_getClassByFrameworkCss(array(3), "col") . '" style="position:absolute;"></div>', $_REQUEST["grp"]);
	$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(0, 0, 4, 4), "col") . '"></div>', $_REQUEST["grp"], $oRecord->id);
} else {
	$cm->oPage->addContent($oRecord);
	//$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(9), "push") . " " . cm_getClassByFrameworkCss(array(3), "col") . '" style="position:absolute;"></div>');
	$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(0, 0, 4, 4), "col") . '"></div>');
}


$cm->oPage->tplAddJs("ff.cms.admin.sitemap-modify");


function SeoModifyField_on_do_action($component, $action) {
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
			
			//DISABLE_SMARTURL_CONTROL
        	if(isset($component->form_fields["smart_url"])) {
				if(is_array($component->recordset) && count($component->recordset)) {
				    foreach($component->recordset AS $rst_key => $rst_value) {
				    	if(strlen($component->recordset[$rst_key]["smart_url"]->getValue()))
				    		continue;

				    	$lang = $component->user_vars["locale"]["lang"][$component->user_vars["locale"]["rev"]["key"][$component->recordset[$rst_key]["ID_languages"]->getValue()]];
				    		
				    	if($component->recordset[$rst_key]["ID_languages"]->getValue() == LANGUAGE_DEFAULT_ID) {
 							$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty") . " (" . $lang["description"] . ")");
			                return true;
				    	} elseif(!DISABLE_SMARTURL_CONTROL) {
 							$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty") . " (" . $lang["description"] . ")");
			                return true;
				    	}
					}
				}
			}

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
                                , "robots" => ($component->user_vars["src"]["seo"]["robots"] && isset($rst_value["robots"])
									? $rst_value["robots"]->getValue()
									: ""
								)
								, "meta" => ($component->user_vars["src"]["seo"]["meta"] && isset($rst_value["meta"])
									? $rst_value["meta"]->getValue()
									: ""
								)
								, "httpstatus" => ($component->user_vars["src"]["seo"]["httpstatus"] && isset($rst_value["httpstatus"])
									? $rst_value["httpstatus"]->getValue()
									: ""
								)
								, "canonical" => ($component->user_vars["src"]["seo"]["canonical"] && isset($rst_value["canonical"])
									? $rst_value["canonical"]->getValue()
									: ""
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