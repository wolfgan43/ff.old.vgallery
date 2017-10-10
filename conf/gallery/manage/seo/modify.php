<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_SEO_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$src_type = $cm->real_path_info;
if(!$src_type)
	$src_type = "vgallery";

$ID_node = $_REQUEST["key"];

if(check_function("get_schema_fields_by_type"))
	$src = get_schema_fields_by_type($src_type);

$key = ($src["key"]
	? $src["key"]
	: "ID"
);

if(check_function("get_locale")) {
	$locale = get_locale(null, true);
}

if($ID_node && is_array($src) && count($src)) {
	if(check_function("set_field_permalink"))
		$arrPermalink = set_field_permalink($src["table"], $ID_node, false, true);

	$node_url = $arrPermalink[LANGUAGE_DEFAULT];
	if(!$node_url || $node_url == "/")
		$disable_smart_url = true;
		
	$sSQL = "SELECT `" . $src["table"] . "`.*
			FROM `" . $src["table"]. "`
			WHERE `" . $src["table"]. "`.`" . $key . "` = " . $db_gallery->toSql($ID_node, "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
	}
}

if(!isset($_REQUEST["keys"]["ID"]))
	$strError = ffTemplate::_get_word_by_code("page_not_found");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SeoModify";
$oRecord->resources[] = $oRecord->id;
if(strlen($strError)) {
	$oRecord->strError = $strError;
	$oRecord->contain_error = true;
} else {
    $oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-' . $src["class"] . '">' . cm_getClassByFrameworkCss($src["icon"], "icon-tag", array("2x", $src["class"])) . $src["label"] 
        . ($node_url 
            ? '<span class="smart-url">' . $node_url . '</span>' . '<a class="slug-gotourl ' . cm_getClassByFrameworkCss("external-link", "icon") . '" href="' . $node_url . '" target="_blank">'
            : ''
        ) . '</a>' .'</h1>';
}
$oRecord->src_table = $src["table"];
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->skip_action = $skip_action;
if($src["field"]["last_update"])
	$oRecord->additional_fields[$src["field"]["last_update"]] = new ffData(time(), "Number");
$oRecord->setWidthComponent(array(8)); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = $key;
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
if(!$strError) {
    if($src["seo"]["primary_table"] != $src["seo"]["table"] && $src["seo"]["rel_lang"]) {
	    $sSQL_field = array();
        
        $js_container_id = "SeoModifyField";
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
	    $arrField = array(
		    "alt_url" 				=> $src["seo"]["alt_url"]
		    , "permalink_parent" 	=> $src["seo"]["permalink_parent"]
		    , "smart_url" 			=> $src["seo"]["smart_url"]
		    , "title" 				=> $src["seo"]["title"]
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
			    if($field_name) {
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
                            $field_data = "`" . $field_name . "`";
                        }

						$sSQL_field[$field_key] = " (
							    SELECT " . $field_data . " 
							    FROM " . $src["seo"]["table"] . " 
							    WHERE `" . $src["seo"]["rel_key"] . "` = [ID_FATHER] 
							        AND `" . $src["seo"]["rel_lang"] . "` = " . FF_PREFIX . "languages.ID
							)";

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
	    }
			                
		$oDetail = ffDetails::factory($cm->oPage);
        if(count($locale["lang"]) > 1) {
			$oDetail->tab = true;
	        $oDetail->tab_label = "language";
			$oDetail->framework_css["widget"]["tab"]["pane-item"]["class"] = "seo-page";	        
        } else {
        	$oDetail->class .= " seo-page";
        }

        $oDetail->id = "SeoModifyField";
        $oDetail->widget_discl_enable = false;
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
                                        ORDER BY " . FF_PREFIX . "languages.description";
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
                                        ORDER BY " . FF_PREFIX . "languages.description";
        $oDetail->addEvent("on_do_action", "SeoModifyField_on_do_action");
        $oDetail->user_vars["src"] = $src;
        $oDetail->user_vars["locale"] = $locale;
        
        
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
	        if($arrField["alt_url"]) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "alt_url";
			    $oField->container_class = "check-keyup";
			    $oField->label = ffTemplate::_get_word_by_code("seo_alt_url");
			    $oField->store_in_db = false;
			    $oField->properties["onkeyup"] = "if(jQuery(this).val().length) { jQuery(this).closest('.seo-page').find('.page-meta').fadeOut(); } else { jQuery(this).closest('.seo-page').find('.page-meta').fadeIn(); }";
			    $oField->placeholder = false;
			    $oField->setWidthLabel(5);
			    $oDetail->addContent($oField);
		    }
		    if($arrField["permalink_parent"]) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "permalink_parent";
			    $oField->container_class = "permalink page-meta";
			    $oField->label = ffTemplate::_get_word_by_code("seo_permalink_parent");
			    $oField->store_in_db = false;
			    $oField->placeholder = false;
			    //$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
			    $oField->setWidthLabel(5);
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
			    $oField->setWidthLabel(5);
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
	        $oField->setWidthLabel(5);
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
	        $oField->setWidthLabel(5);
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
	        //$oField->description = '<span class="countChar"></span>';
	        $oField->placeholder = false;
	        $oField->setWidthLabel(5);
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
                        		        . (count($locale["lang"]) > 1
                        			        ? "[ID_languages_VALUE]" 
                        			        : $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number")
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
	        $oField->setWidthLabel(5);
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
            $oField->setWidthLabel(5);
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
	        $oField->setWidthLabel(5);
	        $oDetail->addContent($oField);
        }  
        if($arrField["canonical"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["canonical"];
	        $oField->container_class = "canonical page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_canonical");
	        $oField->placeholder = false;
	        $oField->setWidthLabel(5);
	        $oDetail->addContent($oField);
	    }

        $oRecord->addContent($oDetail);
        $cm->oPage->addContent($oDetail);
    } else {
        $js_container_id = "SeoModify";
	    $arrField = array(
		    "alt_url" 				=> $src["field"]["alt_url"]
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
	        if($arrField["alt_url"]) {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["alt_url"];
		        $oField->container_class = "check-keyup";
		        $oField->label = ffTemplate::_get_word_by_code("seo_alt_url");
		        $oField->properties["onkeyup"] = "if(jQuery(this).val().length) { jQuery(this).closest('.seo-page').find('.page-meta').fadeOut(); } else { jQuery(this).closest('.seo-page').find('.page-meta').fadeIn(); }";
		        $oField->placeholder = false;
		        $oField->setWidthLabel(5);
		        $oRecord->addContent($oField);
		    }
	        if($arrField["permalink_parent"]) {
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["permalink_parent"];
		        $oField->container_class = "permalink page-meta";
		        $oField->label = ffTemplate::_get_word_by_code("seo_permalink_parent");
		        $oField->placeholder = false;
		        //$oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
		        $oField->setWidthLabel(5);
		        $oRecord->addContent($oField);    
		    }  
		    if($arrField["smart_url"]) {   
		        $oField = ffField::factory($cm->oPage);
		        $oField->id = $arrField["smart_url"];
		        $oField->container_class = "smart-url page-meta check-keyup check-keywords";
		        $oField->label = ffTemplate::_get_word_by_code("seo_smart_url");
		        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 60, 0, 'check-keyup');";
		        $oField->placeholder = false;
		        $oField->setWidthLabel(5);
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
	        $oField->setWidthLabel(5);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["header"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["header"];
	        $oField->container_class = "header page-meta check-keyup";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_title_alt");
	        $oField->properties["onkeyup"] = "ff.cms.seo.countChar(this, 70, 10, 'check-keyup');";
	        $oField->placeholder = false;
	        $oField->setWidthLabel(5);
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
	       // $oField->description = ffTemplate::_get_word_by_code("rel_static_pages_meta_description_left_character");
	        $oField->placeholder = false;
	        $oField->setWidthLabel(5);
	        $oRecord->addContent($oField);
	    }
	    if($arrField["keywords"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["keywords"];
	        $oField->container_class = "meta-keywords page-meta helper";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_keywords");
	        $oField->description = '<a class="' . cm_getClassByFrameworkCss("help", "icon") . '" href="javascript:void(0);"></a><p class="hidden helper-content">' . ffTemplate::_get_word_by_code("seo_meta_keywords_help") . '</p>';
	        //$oField->extended_type = "Selection";
	        $oField->source_SQL = "SELECT search_tags.name
	                                    , search_tags.name 
	                                FROM search_tags
	                                WHERE search_tags.ID_lang = " 
	                                    . (count($locale["lang"]) > 1
	                                        ? "[ID_languages_VALUE]" 
	                                        : $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number")
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
	        $oField->setWidthLabel(5);
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
            $oField->setWidthLabel(5);
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
            $oField->setWidthLabel(5);
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
            $oField->setWidthLabel(5);
            $oRecord->addContent($oField);
        }  
        if($arrField["canonical"]) {   	
	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $arrField["canonical"];
	        $oField->container_class = "canonical page-meta";
	        $oField->label = ffTemplate::_get_word_by_code("seo_meta_canonical");
	        $oField->placeholder = false;
            $oField->setWidthLabel(5);
            $oRecord->addContent($oField);
	    }        
    }
}
$cm->oPage->addContent($oRecord);

//$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(9), "push") . " " . cm_getClassByFrameworkCss(array(3), "col") . '" style="position:absolute;"></div>');
$cm->oPage->addContent('<div class="spellcheck ' . cm_getClassByFrameworkCss(array(4), "col") . '"></div>');
			
$cm->oPage->tplAddJs("ff.cms.seo", "ff.cms.seo.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");
if(is_file(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/javascript/tools/stopwords/ff.cms.seo.stopwords." . strtolower(LANGUAGE_INSET) . ".js"))
	$cm->oPage->tplAddJs("ff.cms.seo.stopWords", "ff.cms.seo.stopwords." . strtolower(LANGUAGE_INSET) . ".js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/stopwords");

$js = '<script type="text/javascript">
        jQuery(function() {
            ' . $js . '
            
            jQuery("#' . $js_container_id . ' DIV.helper").hover(function() {
            	jQuery(this).find(".helper-content").removeClass("hidden");
            }, function() {
            	jQuery(this).find(".helper-content").addClass("hidden");
            });
            jQuery("#' . $js_container_id . ' a.helper").click(function() {
            	if(jQuery(this).find(".helper-content").hasClass("hidden")) {
            		jQuery(this).find(".helper-content").removeClass("hidden");
            	} else {
            		jQuery(this).find(".helper-content").addClass("hidden");
            	}
            });
            
            jQuery("a[data-toggle=tab]").on("shown.bs.tab", function (e) {
                var target = $(e.target).attr("href"); // activated tab

                jQuery(target + " .page-meta INPUT, " + target + " .page-meta TEXTAREA").first().keyup();
            });  
		});


		
      	function keyWordsCheck(that, lang) {
      		if(!lang)
      			lang = "' . strtolower(LANGUAGE_INSET) . '";

			ff.pluginLoad("ff.cms.libs.stopWords." + lang, "' . FF_THEME_DIR . '/' . THEME_INSET . '/javascript/tools/stopwords/ff.cms.seo.stopwords." + lang + ".js", function() {
				var keyCompare = {};
	            if(jQuery(that).closest(".seo-page").find(".smart-url INPUT").length)
	                keyCompare["Smart Url"] = jQuery(that).closest(".seo-page").find(".smart-url INPUT").val().replace(/-/g, " ");
	            if(jQuery(that).closest(".seo-page").find(".meta-title INPUT").length)
	                keyCompare["Title"] = jQuery(that).closest(".seo-page").find(".meta-title INPUT").val();
	            if(jQuery(that).closest(".seo-page").find(".meta-desc TEXTAREA").length)
	                keyCompare["Meta Desc"] = jQuery(that).closest(".seo-page").find(".meta-desc TEXTAREA").val();
	            if(jQuery(that).closest(".seo-page").find(".meta-keywords INPUT").length)
	                keyCompare["Meta KeyWords"] = jQuery(that).closest(".seo-page").find(".meta-keywords INPUT").val();
	            if(jQuery(that).closest(".seo-page").find(".header INPUT").length)
	                keyCompare["H1"] = jQuery(that).closest(".seo-page").find(".header INPUT").val();

	            if(jQuery.isFunction(ff.cms.libs.stopWords[lang])) {
                    ff.cms.seo.stopWords = ff.cms.libs.stopWords[lang];
	            } else {
                    ff.cms.libs.stopWords[lang] = ff.cms.seo.stopWords;
	            }

	            var seoPage = (jQuery(that).closest(".seo-page").length
	            	? jQuery(jQuery(that).closest(".seo-page").find(".check-keywords INPUT, .check-keywords TEXTAREA"))
	            	: jQuery(jQuery(that).closest("#' . $js_container_id . '").find(".check-keywords INPUT, .check-keywords TEXTAREA"))
	            );

				ff.cms.seo.check("keywords-consistency", jQuery(that).closest(".ffRecord").next(".spellcheck"), seoPage, {"keyCompareFrom" : keyCompare});
        	});      	
      	}
        
        ff.pluginLoad("ff.cms.seo", "' . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.seo.js" . '", function() {
				jQuery("#' . $js_container_id . ' .page-meta INPUT, #' . $js_container_id . ' .page-meta TEXTAREA").on("keyup", function() {
					var that = this;
					var lang = jQuery("#' . $js_container_id . ' li.active").text();
					lang = lang.trim().substring(0, 3).toLowerCase();
					console.log("ASD");
					if(jQuery(that).hasClass("loaded")) {
						keyWordsCheck(that, lang);
					}
        			
				});
				
        		jQuery(".page-meta INPUT, .page-meta TEXTAREA").keyup().addClass("loaded");
        		jQuery(".page-meta INPUT, .page-meta TEXTAREA").first().keyup();
        });
        
    </script>';
$cm->oPage->addContent($js);


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
		            		, null
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

					if(check_function("refresh_cache")) {
						refresh_cache($component->user_vars["src"]["cache"]["type"], $ID_node->getValue(), $action, stripslash($actual_path) . "/" . $item_name);
					}					
				}
			}
/*
        	switch($component->user_vars["src"]["table"]) {
        		case "vgallery_nodes":
        			$type_cache = "V";
					
			        $db->query("SELECT vgallery_nodes.*
			                    FROM vgallery_nodes 
			                    WHERE vgallery_nodes.ID = " . $db->toSql($ID_node)
			                );
			        if($db->nextRecord()) {
				        $ID_category = $db->getField("ID_vgallery", "Number", true);
				        $actual_path = $db->getField("parent", "Text", true);
				        $item_name = $db->getField("name", "Text", true);
			        }        	
        			
		            if(is_array($component->recordset) && count($component->recordset)) {
		                foreach($component->recordset AS $rst_key => $rst_value) {
                            if(OLD_VGALLERY) {
		                        if(is_array($rst_value) && count($rst_value)) {
		                            foreach($component->recordset[$rst_key] AS $field_key => $field_value) {
		                                if(!isset($component->form_fields[$field_key]))
		                        	        continue;

		                                switch ($field_key) {
			                                case "smart_url":
			                                    $sSQL = "SELECT * 
			                                            FROM vgallery_rel_nodes_fields
	                                            	        INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
			                                            WHERE 
	                                            	        vgallery_nodes.parent = (SELECT vgallery_nodes.parent FROM vgallery_nodes WHERE vgallery_nodes.ID = " . $db->toSql($ID_node) . ")
			                                                AND `description` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($field_value->getValue()))) . " 
			                                                AND `ID_fields` = (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = " . $db->toSql($field_key) . ")
			                                                AND `ID_nodes` <> " . $db->toSql($ID_node) . "
			                                                AND `ID_lang` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
			                                    $db->query($sSQL);
			                                    if($db->nextRecord()) {
			                                        $component->displayError(ffTemplate::_get_word_by_code("smart_url_not_unic"));
			                                        return true;
			                                    } else {
			                                        $real_value = ffCommon_url_rewrite(strip_tags($field_value->getValue()));    
			                                    }
			                                    
		                                        break;
		                                    case "meta_title":
		                                    case "meta_title_alt":
		                                        $real_value = strip_tags($field_value->getValue());
		                                        break;
		                                    default:
		                                        $real_value = $field_value->getValue(); 
		                                }
                                                
                                        $sSQL = "SELECT ID
                                                    FROM `vgallery_rel_nodes_fields`
                                                    WHERE `ID_fields` = (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = " . $db->toSql($field_key) . ")
                                                        AND `ID_nodes` = " . $db->toSql($ID_node) . "
                                                        AND `ID_lang` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
                                        $db->query($sSQL);
                                        if($db->nextRecord()) {
                                            $sSQL = "UPDATE 
                                                `vgallery_rel_nodes_fields` 
                                            SET 
                                                `description` = " . $db->toSql($real_value) . " 
                                            WHERE `ID_fields` = (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = " . $db->toSql($field_key) . ")
                                                AND `ID_nodes` = " . $db->toSql($ID_node) . "
                                                AND `ID_lang` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
                                            $db->execute($sSQL);
                                        } else {
		                                    $sSQL = "INSERT INTO  
		                                                `vgallery_rel_nodes_fields` 
		                                            ( 
		                                                `ID`  
		                                                , `description`
		                                                , `ID_fields` 
		                                                , `ID_nodes`
		                                                , `ID_lang`
		                                            )
		                                            VALUES
		                                            (
		                                                null
		                                                , " . $db->toSql($real_value) . " 
		                                                , (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = " . $db->toSql($field_key) . ")
		                                                , " . $db->toSql($ID_node) . " 
		                                                , " . $db->toSql($component->recordset[$rst_key]["ID_languages"]) . " 
		                                            )";
		                                    $db->execute($sSQL);
		                                }
		                            } 
		                        }
                            } else {
                                $sSQL = "SELECT * 
                                        FROM vgallery_nodes_rel_languages
                                            INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_nodes_rel_languages.ID_nodes
                                        WHERE 
                                            vgallery_nodes.parent = (SELECT vgallery_nodes.parent FROM vgallery_nodes WHERE vgallery_nodes.ID = " . $db->toSql($ID_node) . ")
                                            AND `smart_url` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($component->recordset[$rst_key]["smart_url"]->getValue()))) . " 
                                            AND `ID_nodes` <> " . $db->toSql($ID_node) . "
                                            AND `ID_lang` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $component->displayError(ffTemplate::_get_word_by_code("smart_url_not_unic"));
                                    return true;
                                }                                                    
                                
                            }
		                }
		            }
        			break;
				case "static_pages":
					$type_cache = "S";
					$ID_category = "";

			        $db->query("SELECT static_pages.*
			                    FROM static_pages 
			                    WHERE static_pages.ID = " . $db->toSql($ID_node));
			        if($db->nextRecord()) {
				        $actual_path = $db->getField("parent", "Text", true);
				        $item_name = $db->getField("name", "Text", true);
			        }  
		            if(isset($component->form_fields["smart_url"])) {
			            if(is_array($component->recordset) && count($component->recordset)) {
			                foreach($component->recordset AS $rst_key => $rst_value) {
								$sSQL = "SELECT * 
						                FROM static_pages_rel_languages
				                            INNER JOIN static_pages ON static_pages.ID = static_pages_rel_languages.ID_static_pages
						                WHERE 
				                            static_pages.parent = (SELECT static_pages.parent FROM static_pages WHERE static_pages.ID = " . $db->toSql($ID_node) . ")
						                    AND `smart_url` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($component->recordset[$rst_key]["smart_url"]->getValue()))) . " 
						                    AND `ID_static_pages` <> " . $db->toSql($ID_node) . "
						                    AND `ID_languages` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
						        $db->query($sSQL);
						        if($db->nextRecord()) {
						            $component->displayError(ffTemplate::_get_word_by_code("smart_url_not_unic"));
						            return true;
						        }			        
							}
						}
					}
					break;
				case "files":
					$type_cache = "G";
					$ID_category = "";
					
			        $db->query("SELECT files.*
			                    FROM files 
			                    WHERE static_pages.ID = " . $db->toSql($ID_node));
			        if($db->nextRecord()) {
				        $actual_path = $db->getField("parent", "Text", true);
				        $item_name = $db->getField("name", "Text", true);
			        }  		
					if(isset($component->form_fields["smart_url"])) {
			            if(is_array($component->recordset) && count($component->recordset)) {
			                foreach($component->recordset AS $rst_key => $rst_value) {
								$sSQL = "SELECT * 
						                FROM files_rel_languages
				                            INNER JOIN files ON files.ID = files_rel_languages.ID_files
						                WHERE 
				                            files.parent = (SELECT files.parent FROM files WHERE files.ID = " . $db->toSql($ID_node) . ")
						                    AND `smart_url` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($component->recordset[$rst_key]["smart_url"]->getValue()))) . " 
						                    AND `ID_files` <> " . $db->toSql($ID_node) . "
						                    AND `ID_lang` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
						        $db->query($sSQL);
						        if($db->nextRecord()) {
						            $component->displayError(ffTemplate::_get_word_by_code("smart_url_not_unic"));
						            return true;
						        }	
							}
						}
					}
					break;
				default:        	
        	}*/

        	
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
       // break;
    }
}