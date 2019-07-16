<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$arrTag = array();
if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["settagstatus"]) && isset($_REQUEST["keys"]["code"])) {
	$db = ffDB_Sql::factory();
	$sSQL = "UPDATE search_tags
                    SET search_tags.status = " . $db->toSql($_REQUEST["settagstatus"], "Number") . "
                    WHERE search_tags.code = " . $db->toSql($_REQUEST["keys"]["code"], "Number");
    $db->execute($sSQL);
    
    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("TagsModify")), true));
   
}
$sSQL = "SELECT " . FF_PREFIX . "languages.ID
                , " . FF_PREFIX . "languages.tiny_code
                , IF( " . FF_PREFIX . "languages.ID = " . $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number") . ",1,0) AS language_default
            FROM " . FF_PREFIX . "languages
            WHERE " . FF_PREFIX . "languages.status > 0
            ORDER BY language_default DESC";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord())
{
    do {
        $ID_lang = $db_gallery->getField("ID", "Number", true);
        $arrLang[$ID_lang] = $db_gallery->getField("tiny_code", "Text", true);
    } while ($db_gallery->nextRecord());
}

if(isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0)
{
    if(isset($_REQUEST["keys"]["code"]) && $_REQUEST["keys"]["code"] > 0) {
        $sSQL_string = " OR search_tags.code = " . $db_gallery->toSql($_REQUEST["keys"]["code"], "Number");
    }
    $sSQL = "SELECT search_tags.*
                            FROM search_tags
                            WHERE search_tags.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") 
                            . $sSQL_string;
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord())
    {
        $tag_categories = $db_gallery->getField("categories", "Text", true);
        do {
            $ID_lang = $db_gallery->getField("ID_lang", "Number", true);
            if(array_key_exists($ID_lang, $arrLang))
            {
                    $arrTag[$arrLang[$ID_lang]] = array("ID_lang" => $ID_lang
                                                            , "ID" => $db_gallery->getField("ID", "Number", true)
                                                            , "name" => $db_gallery->getField("name", "Text", true)
                                                        );
            }
        } while ($db_gallery->nextRecord());
    }
}

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "TagsModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "search_tags";


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oRecord->addKeyField($oField); 

if(is_array($arrLang) && count($arrLang))
{
    $oRecord->addEvent("on_do_action", "TagsModify_on_do_action");
    $oRecord->user_vars["arrLang"] = $arrLang;

    foreach($arrLang AS $ID_lang => $language_name)
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "content_" . $language_name;
        $oField->label = ffTemplate::_get_word_by_code("tags_modify_" . $language_name);
        $oField->class = "tags translate"; 
        $oField->data_type = "";
        $oField->store_in_db = false;
        $oField->widget = "autocomplete";
        $oField->source_SQL = "SELECT name, name FROM search_tags WHERE ID_lang = " . $db_gallery->toSql($ID_lang, "Number") . " [AND] [WHERE] [HAVING]";
        $oField->actex_update_from_db = true;
        $oField->autocomplete_minLength = 0;
        $oField->autocomplete_readonly = false;
        $oField->autocomplete_compare_having = "name";
        $oField->autocomplete_operation = "LIKE [[VALUE]%]"; 
        $oField->default_value = new ffData($arrTag[$language_name]["name"]);
        $oField->properties["onchange"] = "javascript:getTraslation(this);";
        $oRecord->addContent($oField);
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "categories";
$oField->label = ffTemplate::_get_word_by_code("tags_modify_categories");
$oField->widget = "autocomplete";
$oField->source_SQL = "SELECT 
                            search_tags_categories.ID AS ID
                            , search_tags_categories.name AS name
                        FROM search_tags_categories
                        WHERE 1
                        [AND] [WHERE]
                        [HAVING]
                        ORDER BY name";
$oField->autocomplete_compare = "name";
$oField->actex_update_from_db = true;
$oField->autocomplete_combo = true;
$oField->autocomplete_minLength = 0;
$oField->autocomplete_multi = true;
$oField->default_value = new ffData($tag_categories);
$oRecord->addContent($oField);

$js = '<script type="text/javascript">
	function getTraslation(elem) {
            var name = jQuery(elem).val();
            if(name.length)
            {
                var fromLang = jQuery(elem).attr("id").replace("autocomplete_TagsModify_content_", "");
                jQuery(".tags.translate INPUT.ui-autocomplete-input").each(function() {  
                    if(!jQuery(this).val())
                    {
                        var idDest = jQuery(this).attr("id");
                        var idHidden = idDest.replace("autocomplete_", "");
                        var destLang = idDest.replace("autocomplete_TagsModify_content_", "");
                        $.ajax({
                            
                            url: "http://api.mymemory.translated.net/get?q=" + name + "&langpair=" + fromLang + "|" + destLang,
                            success: function(data) {
                                var translated = $(data)[0].responseData.translatedText;
                                jQuery("#" + idDest).val(translated);
                                jQuery("#" + idHidden).val(translated);
                            }
                        });
                    }
                });
            }
	}
        jQuery(".tags.translate INPUT.ui-autocomplete-input").first().load(function() {
            console.log(jQuery(".tags.translate INPUT.ui-autocomplete-input").first().val());
            console.log("hey");
            jQuery(".tags.translate INPUT.ui-autocomplete-input").first().change();
        });
</script>';
$cm->oPage->addContent($js);

$cm->oPage->addContent($oRecord);   

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "searchTagRel";
$oDetail->title = ffTemplate::_get_word_by_code("search_tag_relation");
$oDetail->src_table = "search_tags_rel";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_src" => "code"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_dest";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_plugin_limit_ext_type");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT code,name
                        FROM search_tags
                        WHERE ID_lang = " . $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
                            AND code NOT IN (
                                SELECT `ID_src` AS list_ID
                                FROM search_tags_rel
                                UNION 
                                SELECT `ID_dest` AS list_ID
                                FROM search_tags_rel
                                WHERE ID_src <> " . $db_gallery->toSql($_REQUEST["keys"]["code"], "Number") . "
                            )
                        ORDER BY name";
                            
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "searchTagRel");
$cm->oPage->addContent($oDetail);


function TagsModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) 
    {
        switch ($action) 
        {
            case "insert":
            case "update":	
                foreach($component->user_vars["arrLang"] AS $ID_lang => $name_lang)
                {
                    if(strlen($component->form_fields["content_" . $name_lang]->value_ori->getValue()))
                        $search_name = $component->form_fields["content_" . $name_lang]->value_ori->getValue();
                    if($ID_lang == LANGUAGE_DEFAULT_ID || $ID_code > 0)
                    { 
                        if(strlen($component->form_fields["content_" . $name_lang]->getValue()))
                        {
                            $name = $component->form_fields["content_" . $name_lang]->getValue();
                            if(strpos($name, ",")) {
                                $name = str_replace(",","&#x201A;", $name);
                            }
                            
                            if(!strlen($search_name)) 
                                $search_name = $component->form_fields["content_" . $name_lang]->getValue();
                            
                            $sSQL = "SELECT search_tags.*
                                        FROM search_tags
                                        WHERE search_tags.name = " . $db->toSql($search_name) . "
                                                AND search_tags.ID_lang = " . $db->toSql($ID_lang);
                            $db->query($sSQL);
                            if($db->nextRecord())
                            {
                                $ID = $db->getField("ID", "number", true);
                                $code = $db->getField("code", "Number", true);
                                
                                if($ID_code) 
                                {
                                    if($code > 0)
                                    {
                                        $sSQL = "UPDATE search_tags SET
                                                        code = 0
                                                    WHERE code = " . $db->toSql($code, "Number");
                                        $db->execute($sSQL);
                                    }
                                    $sSQL = "UPDATE search_tags SET
                                                        code = " . $db->toSql($ID_code, "Number") . "
                                                    WHERE ID = " . $db->toSql($ID, "Number");
                                    $db->execute($sSQL);
                                } elseif($ID_lang == LANGUAGE_DEFAULT_ID)
                                {
                                    $sSQL = "UPDATE search_tags SET
                                                    code = 0
                                                WHERE code = " . $db->toSql($ID, "Number") . "
                                                    OR code = " . $db->toSql($code, "Number");
                                    $db->execute($sSQL);
                                    $sSQL = "UPDATE search_tags SET
                                                    name = " . $db->toSql($name) . "
                                                    , code = " . $db->toSql($ID, "Number") . "
                                                WHERE ID = " . $db->toSql($ID, "Number");
                                    $db->execute($sSQL);
                                    $ID_code = $ID;
                                }
                            } else 
                            {
                                $sSQL = "INSERT INTO search_tags
                                            (
                                                ID
                                                , name
                                                , smart_url
                                                , ID_lang
                                                , code
                                                , status
                                            ) VALUES
                                            (
                                                null
                                                , " . $db->toSql($name) . "
                                                , " . $db->toSql(ffCommon_url_rewrite($name)) . "
                                                , " . $db->toSql($ID_lang, "Number") . "
                                                , " . $db->toSql(($ID_code > 0 ? $ID_code : 0), "Number") . "
                                                , 1
                                            )";
                                $db->execute($sSQL);
                                if($ID_lang == LANGUAGE_DEFAULT_ID && !$ID_code)
                                {
                                    $ID_code = $db->getInsertID(true);
                                    $sSQL = "UPDATE search_tags SET
                                                    code = " . $db->toSql($ID_code, "Number") . "
                                                WHERE ID = " . $db->toSql($ID_code, "Number");
                                    $db->execute($sSQL);
                                }
                            }

                        }
                    } else {
                        $component->tplDisplayError(ffTemplate::_get_word_by_code("primary_lang_undefined"));
                    }
                }
                break;
            case "confirmdelete":
                if($_REQUEST["keys"]["code"]) {
                    $sSQL = "DELETE FROM search_tags
                                WHERE code = " . $db->toSql($_REQUEST["keys"]["code"], "Number");
                    $db->execute($sSQL);
                } elseif($_REQUEST["keys"]["ID"]) {
                    $sSQL = "SELECT code
                                FROM search_tags
                                WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $code = $db->getField("code", "Number", true);
                        $sSQL = "DELETE FROM search_tags
                                    WHERE code = " . $db->toSql($code, "Number");
                        $db->execute($sSQL);
                    }
                }
            default:
                break;
        }
    }
}