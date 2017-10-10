<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage contents
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2010, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */

$db = ffDB_Sql::factory();

$disable_path = false;
$path = $_REQUEST["path"];
if(strlen($path)) {
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "layout.* 
			FROM " . CM_TABLE_PREFIX . "layout
			WHERE " . CM_TABLE_PREFIX . "layout.path = " . $db->toSql($path, "Text") . "
				AND (" . CM_TABLE_PREFIX . "layout.domains = ''
					OR " . $db->toSql($_SERVER["HTTP_HOST"]) . " LIKE " . CM_TABLE_PREFIX . "layout.domains
				)
			ORDER BY " . CM_TABLE_PREFIX . "layout.path ASC";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);	
		$disable_path = true;	
	}
}
if ($_REQUEST["keys"]["ID"])
{
	$path = $db->lookup(CM_TABLE_PREFIX . "layout", "ID", new ffData($_REQUEST["keys"]["ID"]), null, "path", null, true);
	$disable_path = true;
	//ffErrorHandler::raise("debug", E_USER_ERROR, null, get_defined_vars());
}
if(strlen($path)) {
	$res = cm_getLayoutDepsByPath(ffCommon_dirname($path));
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->resources[] = "cmLayoutModify";
$oRecord->title = "Layout";
$oRecord->src_table = CM_TABLE_PREFIX . "layout";
$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");
//$oRecord->addContent(null, true, "layout");
//$oRecord->groups["layout"]["title"] = "Layout";
if(strlen($res["reset_cascading"]["value"])) {
	$oRecord->additional_fields["reset_cascading"] = new ffData($res["reset_cascading"]["value"]);
} else {
	$oRecord->additional_fields["reset_cascading"] = new ffData("0");
}
if(strlen($res["ignore_defaults"]["value"])) {
	$oRecord->additional_fields["ignore_defaults"] = new ffData($res["ignore_defaults"]["value"]);
} else {
	$oRecord->additional_fields["ignore_defaults"] = new ffData("0");
}
if(strlen($res["ignore_defaults_main"]["value"])) {
	$oRecord->additional_fields["ignore_defaults_main"] = new ffData($res["ignore_defaults_main"]["value"]);
} else {
	$oRecord->additional_fields["ignore_defaults_main"] = new ffData("1");
}
if(strlen($res["main_theme"]["value"])) {
	$oRecord->additional_fields["main_theme"] = new ffData($res["main_theme"]["value"]);
} else {
	$oRecord->additional_fields["main_theme"] = new ffData("restricted");
}

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("cm_layout_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_general")
                                         , "cols" => 1
                                         , "tab" => "general"
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_path");
$oField->default_value = new ffData($path, "Text");
if($disable_path)
	$oField->control_type = "label";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_cascading";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_enable_cascading");
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
if (strlen($res["enable_cascading"]["value"])) {
	$oField->default_value = new ffData($res["enable_cascading"]["value"]);
} else {
	$oField->default_value = new ffData("1");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_ff_js";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_exclude_ff");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("layout_include")))
	, array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("layout_exclude")))
);
$oField->multi_select_one = false;

if (strlen($res["exclude_ff_js"])) {
	$oField->default_value = new ffData($res["exclude_ff_js"]);
} else {
	$oField->default_value = new ffData("0");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_form";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_exclude_form");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("layout_include")))
	, array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("layout_exclude")))
);
$oField->multi_select_one = false;
if (strlen($res["exclude_form"]["value"])) {
	$oField->default_value = new ffData($res["exclude_form"]["value"]);
} else {
	$oField->default_value = new ffData("0");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_gzip";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_enable_gzip");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no")))
	, array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes")))
);
$oField->multi_select_one = false;
if (strlen($res["enable_gzip"]["value"])) {
	$oField->default_value = new ffData($res["enable_gzip"]["value"]);
} else {
	$oField->default_value = new ffData("1");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "compact_js";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_compact_js");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no")))
	, array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("compact")))
	, array(new ffData("2"), new ffData(ffTemplate::_get_word_by_code("compact_minify"))) 
);
$oField->multi_select_one = false;
if (strlen($res["compact_js"]["value"])) {
	$oField->default_value = new ffData($res["compact_js"]["value"]);
} else {
	$oField->default_value = new ffData("2");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "compact_css";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_compact_css");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no")))
	, array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("compact")))
	, array(new ffData("2"), new ffData(ffTemplate::_get_word_by_code("compact_minify"))) 
);
$oField->multi_select_one = false;
if (strlen($res["compact_css"]["value"])) {
	$oField->default_value = new ffData($res["compact_css"]["value"]);
} else {
	$oField->default_value = new ffData("2");
}
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_title");
if (strlen($res["title"]["value"]))
	$oField->fixed_post_content = ffTemplate::_get_word_by_code("layout_inherited") . " " . $res["title"]["value"];
$oRecord->addContent($oField, "general");


$system_themes = array("default", "restricted", "dialog", "library", "gallery");

$arrLayer = array();

//Layout di base
$add_layer = glob(FF_DISK_PATH . FF_THEME_DIR . "/restricted/layouts/*");
if(is_array($add_layer) && count($add_layer)) {
	foreach($add_layer AS $real_file) {
		if(is_file($real_file) && strpos(ffGetFilename($real_file), "layer_") === 0 && ffGetFilename($real_file)) {
			$arrLayer[] = substr(ffGetFilename($real_file), strlen("layer_"));
		}
	}
}        	


//Theme
$add_theme = glob(FF_DISK_PATH . FF_THEME_DIR . "/*", GLOB_ONLYDIR);
if(is_array($add_theme) && count($add_theme)) {
    foreach($add_theme AS $real_dir) {
        if(is_dir($real_dir) && array_search(basename($real_dir), $system_themes) === false) {
			if(strlen($sSQL_theme))
				$sSQL_theme .= " UNION ";

            $sSQL_theme .= " (SELECT 
                                " . $db->toSql(basename($real_dir), "Text") . " AS nameID
                                , " . $db->toSql(ucfirst(basename($real_dir)), "Text") . " AS name
                            )";
			//Page di base
			if(strlen($sSQL_page))
				$sSQL_page .= " UNION ";
        	
			$sSQL_page .= " (SELECT 
			                    " . $db->toSql("default", "Text") . " AS nameID
			                    , " . $db->toSql("Default (" . ffTemplate::_get_word_by_code("layout_inherited"). ")", "Text") . " AS name
			                    , " . $db->toSql(basename($real_dir), "Text") . " AS type
			                )
			                UNION
							(SELECT 
			                    " . $db->toSql("empty", "Text") . " AS nameID
			                    , " . $db->toSql("Empty (" . ffTemplate::_get_word_by_code("layout_inherited"). ")", "Text") . " AS name
			                    , " . $db->toSql(basename($real_dir), "Text") . " AS type
			                )";

			//Page per theme
			$add_page = glob($real_dir . "/ff/ffPage/*");
			if(is_array($add_page) && count($add_page)) {
				foreach($add_page AS $real_file) {
					if(is_file($real_file) 
                        && strpos(ffGetFilename($real_file), "ffPage_") === 0
                    ) {
						if(strlen($sSQL_page))
							$sSQL_page .= " UNION ";

			            $sSQL_page .= " (SELECT 
			                                " . $db->toSql(substr(ffGetFilename($real_file), strlen("ffPage_")), "Text") . " AS nameID
			                                , " . $db->toSql(ucfirst(substr(ffGetFilename($real_file), strlen("ffPage_"))), "Text") . " AS name
			                                , " . $db->toSql(basename($real_dir), "Text") . " AS type
			                            )";						
					}
				}
			}

			//Layout di base
			if(is_array($arrLayer) && count($arrLayer)) {
				foreach($arrLayer AS $arrLayer_value) {
					if(strlen($sSQL_layer))
						$sSQL_layer .= " UNION ";

			        $sSQL_layer .= " (SELECT 
			                            " . $db->toSql($arrLayer_value, "Text") . " AS nameID
			                            , " . $db->toSql(ucfirst($arrLayer_value . " (" . ffTemplate::_get_word_by_code("layout_inherited"). ")"), "Text") . " AS name
			                            , " . $db->toSql(basename($real_dir), "Text") . " AS type
			                        )";						
				}
			}
			
			//Layout per theme
			$add_layer = glob($real_dir . "/layouts/*");
			if(is_array($add_layer) && count($add_layer)) {
				foreach($add_layer AS $real_file) {
					if(is_file($real_file) && strpos(ffGetFilename($real_file), "layer_") === 0) {
						if(strlen($sSQL_layer))
							$sSQL_layer .= " UNION ";

			            $sSQL_layer .= " (SELECT 
			                                " . $db->toSql(substr(ffGetFilename($real_file), strlen("layer_")), "Text") . " AS nameID
			                                , " . $db->toSql(ucfirst(substr(ffGetFilename($real_file), strlen("layer_"))), "Text") . " AS name
			                                , " . $db->toSql(basename($real_dir), "Text") . " AS type
			                            )";						
					}
				}
			}        	
        }
    }
}



$oField = ffField::factory($cm->oPage);
$oField->id = "theme";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_additional_theme");
if(strlen($sSQL_theme)) {
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT nameID, name 
							FROM (
								$sSQL_theme							
							) AS tbl_src
							[WHERE]
							ORDER BY name";
	$oField->actex_child = array("page", "layer");
	//$oField->actex_related_field = "type";
	$oField->actex_update_from_db = true;

	if (strlen($res["theme"]["value"])) {
		$oField->default_value = new ffData($res["theme"]["value"]);
	}
}
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "page";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_page");
if(strlen($sSQL_layer)) {
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT DISTINCT nameID, name, type 
							FROM (
								$sSQL_page
							) AS tbl_src
							[WHERE]
							ORDER BY name";
	$oField->actex_father = "theme";
	$oField->actex_related_field = "type";
	$oField->actex_update_from_db = true;
	//*************************************************
//	$oField->actex_dialog_url = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify?prototype_path=" . urlencode(FF_THEME_DIR . "/[theme]/ff/ffPage/ffPage_[name]");
	//BUG NN Valorizza piu parametri. Ne valorizza solo 1. SE si aggiungono 2+ parametri esce undefined.
//	$oField->actex_dialog_edit_params = array("theme" => $oRecord->id . "_theme", "name" => null);
//	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailAddressModify_confirmdelete";
	//*************************************************
	if (strlen($res["page"]["value"])) {
		$oField->default_value = new ffData($res["page"]["value"]);
	} else {
		$oField->default_value = new ffData("default");
	}
}
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "layer";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_layer");
if(strlen($sSQL_layer)) {
	$oField->widget = "activecomboex";
	$oField->source_SQL = "SELECT DISTINCT nameID, name, type 
							FROM (
								$sSQL_layer							
							) AS tbl_src
							[WHERE]
							ORDER BY name";
	$oField->actex_father = "theme";
	$oField->actex_related_field = "type";
	$oField->actex_update_from_db = true;
	//*************************************************
	//Da creare il file di editing e finire il tutto
	//*************************************************
//	$oField->actex_dialog_url = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify/[[" . $oRecord->id . "_theme" . "]]/?prototype_path=" . urlencode(FF_THEME_DIR . "/[theme]/ff/ffPage/ffPage_[name]");
//	$oField->actex_dialog_edit_params = array("theme" => $oRecord->id . "_theme", "name" => null);
//	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailAddressModify_confirmdelete";
	//*************************************************
	if (strlen($res["layer"]["value"])) {
		$oField->default_value = new ffData($res["layer"]["value"]);
	} else {
		$oField->default_value = new ffData("gallery");	
	}
}
$oField->required = true;
$oRecord->addContent($oField, "general");

$framework_css_multi_pairs = array();
if(is_array($cm->oPage->framework_css_setting) && count($cm->oPage->framework_css_setting)) {
	foreach($cm->oPage->framework_css_setting AS $framework_css => $framework_css_value) {
		$framework_css_multi_pairs[$framework_css] = array(new ffData($framework_css), new ffData(ucfirst($framework_css)));
		if(isset($framework_css_value["class-fluid"]))
			$framework_css_multi_pairs[$framework_css . "~fluid"] = array(new ffData($framework_css . "-fluid"), new ffData(ucfirst($framework_css) . " Fluid"));

		if(is_array($framework_css_value["theme"]) && count($framework_css_value["theme"])) {
			foreach($framework_css_value["theme"] AS $framework_css_theme_key => $framework_css_theme_value) {
				$framework_css_multi_pairs[$framework_css . "-" . $framework_css_theme_key] = array(new ffData($framework_css . "-" . $framework_css_theme_key), new ffData(ucfirst($framework_css) . " " . ucfirst($framework_css_theme_key)));
				if(isset($framework_css_value["class-fluid"]))
					$framework_css_multi_pairs[$framework_css . "~fluid-" . $framework_css_theme_key] = array(new ffData($framework_css . "-fluid-" . $framework_css_theme_key), new ffData(ucfirst($framework_css) . " Fluid " . ucfirst($framework_css_theme_key)));
			}
		}
	}
	ksort($framework_css_multi_pairs);
}
array_unshift($framework_css_multi_pairs, array(new ffData("no"), new ffData("Nessuno")));



$oField = ffField::factory($cm->oPage);
$oField->id = "framework_css";
$oField->label = "framework css";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = $framework_css_multi_pairs;
$oRecord->addContent($oField, "general");

$font_icon_multi_pairs = array();
if(is_array($cm->oPage->font_icon_setting) && count($cm->oPage->font_icon_setting)) {
	foreach($cm->oPage->font_icon_setting AS $font_icon_key => $font_icon_value) {
		$font_icon_multi_pairs[$font_icon_key] = array(new ffData($font_icon_key), new ffData(ucfirst($font_icon_key)));
	}
	ksort($font_icon_multi_pairs);
}
array_unshift($font_icon_multi_pairs, array(new ffData("no"), new ffData("Nessuno")));

$oField = ffField::factory($cm->oPage);
$oField->id = "font_icon";
$oField->label = "font icon";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
    array(new ffData(""), new ffData("Nessuno"))
    , array(new ffData("base"), new ffData("Base"))
    , array(new ffData("glyphicons"), new ffData("GlyphIcons"))
    , array(new ffData("fontawesome"), new ffData("Font Awesome"))
);
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "class_body";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_body_class");
if (strlen($res["class_body"]["value"]))
	$oField->fixed_post_content = ffTemplate::_get_word_by_code("layout_inherited") . " " . $res["class_body"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "domains";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_domains_trust");
$oField->base_type = "Text";
$oField->extended_type = "Text";
if (strlen($res["domains"]["value"]))
	$oField->fixed_post_content = ffTemplate::_get_word_by_code("layout_inherited") . " " . $res["domains"]["value"];
$oRecord->addContent($oField, "general");

/*$oField = ffField::factory($cm->oPage);
$oField->id = "reset_css";
$oField->label = "Reset CSS";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("");
$oField->checked_value = new ffData("1");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reset_js";
$oField->label = "Reset JS";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("");
$oField->checked_value = new ffData("1");
$oRecord->addContent($oField);
*/
$cm->oPage->addContent($oRecord);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("sections");
$oRecord->setTabTitle("sections", ffTemplate::_get_word_by_code("cm_layout_sections"));

$oRecord->addContent(null, true, "sections"); 
$oRecord->groups["sections"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_sections")
                                         , "cols" => 1
                                         , "tab" => "sections"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailSect";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_sect";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sect";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_section_name");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_section_value");
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/

$oField = ffField::factory($cm->oPage);
$oField->id = "theme_include";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_section_theme_include");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_section_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "sections");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
/*
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailInhSect";
$oDetail->title = "Sezioni Ereditate";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_sect";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sect";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome Sezione";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = "Nome Variante";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "sections");
$cm->oPage->addContent($oDetail);
*/
// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("css");
$oRecord->setTabTitle("css", ffTemplate::_get_word_by_code("cm_layout_css"));

$oRecord->addContent(null, true, "css"); 
$oRecord->groups["css"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_css")
                                         , "cols" => 1
                                         , "tab" => "css"
                                      );
                                      
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailCss";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_css";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_css";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_css_name");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_css_file_name");
//$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_css_path");
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "priority";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_css_priority");
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("top"), new ffData("Top"))
	, array(new ffData("bottom"), new ffData("Bottom"))
);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_css_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "css");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("js");
$oRecord->setTabTitle("js", ffTemplate::_get_word_by_code("cm_layout_js"));

$oRecord->addContent(null, true, "js"); 
$oRecord->groups["js"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_js")
                                         , "cols" => 1
                                         , "tab" => "js"
                                      );
                                      
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailJs";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_js";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_js";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$js_plugin = array();
$js_plugin_path = glob(FF_DISK_PATH . "/themes/library/plugins/*");
if(is_array($js_plugin_path) && count($js_plugin_path)) {
    foreach ($js_plugin_path AS $real_file) {
        if(is_dir($real_file)) {
            $real_file = str_replace(FF_DISK_PATH, "", $real_file);

            $js_plugin[] = array(new ffData($real_file . "/" . basename($real_file) . ".js"), new ffData(basename($real_file)));
        }
    }
}
         
$oField = ffField::factory($cm->oPage);
$oField->id = "plugin_path";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_plugin");
$oField->extended_type = "Selection";
$oField->multi_pairs = $js_plugin;
$oField->multi_select_noone = true;
$oField->multi_select_noone_val = new ffData("");
$oField->multi_select_one = false;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "js_path";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_path");
$oDetail->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_name");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_file_name");
//$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_file_path");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "theme_include";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_theme_include");
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "priority";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_priority");
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("top"), new ffData("Top"))
	, array(new ffData("bottom"), new ffData("Bottom"))
);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_js_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "js");
$cm->oPage->addContent($oDetail);


// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("meta");
$oRecord->setTabTitle("meta", ffTemplate::_get_word_by_code("cm_layout_meta"));

$oRecord->addContent(null, true, "meta"); 
$oRecord->groups["meta"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_meta")
                                         , "cols" => 1
                                         , "tab" => "meta"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailMeta";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_meta";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_meta";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_meta_name");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = "Tipo";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("name"), new ffData("name"))
	, array(new ffData("property"), new ffData("property"))
	, array(new ffData("http-equiv"), new ffData("http-equiv"))
);
$oField->multi_select_one = false;
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "content";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_meta_value");
$oField->required = true;
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_meta_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "meta");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("cdn");
$oRecord->setTabTitle("cdn", ffTemplate::_get_word_by_code("cm_layout_cdn"));

$oRecord->addContent(null, true, "cdn"); 
$oRecord->groups["cdn"] = array(
                                         "title" => ffTemplate::_get_word_by_code("cm_layout_cdn")
                                         , "cols" => 1
                                         , "tab" => "cdn"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailCDN";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_cdn";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdn";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_cdn_name");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_cdn_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
    array(new ffData("css"), new ffData("Css"))
    , array(new ffData("js"), new ffData("Javascript"))
);
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "url";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_cdn_url");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("cm_layout_cdn_status");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "cdn");
$cm->oPage->addContent($oDetail);

function cm_getLayoutDepsByPath($layout_path)
{ 
	$db = ffDB_Sql::factory();

	$layout_vars = array();
	$layout_vars["main_theme"] = null;
	$layout_vars["theme"] = null;
	$layout_vars["page"] = null;
	$layout_vars["layer"] = null;
	$layout_vars["title"] = null;
	$layout_vars["class_body"] = null;
	$layout_vars["sect"] = array();
	$layout_vars["css"] = array();
	$layout_vars["js"] = array();
	$layout_vars["meta"] = array();
	$layout_vars["exclude_ff_js"] = false;
    $layout_vars["framework_css"] = null;

	$tmp = $layout_path;
	$paths = "";
	do
	{
		if (strlen($paths))
			$paths .= " OR ";
		$paths .= "path = '" . $db->toSql(new ffData($tmp), NULL, false) . "'";
	} while($tmp != "/" && $tmp = ffCommon_dirname($tmp));

	$sSQL = "SELECT
					*
				FROM
					" . CM_TABLE_PREFIX . "layout
				WHERE
					" . $paths . "
				ORDER BY
					path ASC
			";

	$db->query($sSQL);
	if ($db->nextRecord())
	{
		$db2 = ffDb_Sql::factory();
		do
		{
			$ID = $db->getField("ID")->getValue();
			$bMatchPath = $db->getField("path")->getValue() == $layout_path;

			if(!$db->getField("enable_cascading")->getValue() && !$bMatchPath)
				continue;

			if ($db->getField("reset_cascading")->getValue())
			{
				$layout_vars = array();
				$layout_vars["main_theme"] = null;
				$layout_vars["theme"] = null;
				$layout_vars["page"] = null;
				$layout_vars["layer"] = null;
                $layout_vars["framework_css"] = null;
				$layout_vars["title"] = null;
				$layout_vars["class_body"] = null;
				$layout_vars["sect"] = array();
				$layout_vars["css"] = array();
				$layout_vars["js"] = array();
				$layout_vars["meta"] = array();
				$layout_vars["exclude_ff_js"] = true;
			}

			if (strlen($db->getField("main_theme")->getValue()))
			{
				$layout_vars["main_theme"]["id"] = $ID;
				$layout_vars["main_theme"]["value"] = $db->getField("main_theme")->getValue();
			}

			$layout_vars["exclude_ff_js"] = $db->getField("exclude_ff_js")->getValue();
			
			if (strlen($db->getField("theme")->getValue()))
			{
				$layout_vars["theme"]["id"] = $ID;
				$layout_vars["theme"]["value"] = $db->getField("theme")->getValue();
			}

			if (strlen($db->getField("page")->getValue()))
			{
				$layout_vars["page"]["id"] = $ID;
				$layout_vars["page"]["value"] = $db->getField("page")->getValue();
			}

			if (strlen($db->getField("layer")->getValue()))
			{
				$layout_vars["layer"]["id"] = $ID;
				$layout_vars["layer"]["value"] = $db->getField("layer")->getValue();
			}

			if (strlen($db->getField("title")->getValue()))
			{
				$layout_vars["title"]["id"] = $ID;
				$layout_vars["title"]["value"] = $db->getField("title")->getValue();
			}

			if (strlen($db->getField("class_body")->getValue()))
			{
				$layout_vars["class_body"]["id"] = $ID;
				$layout_vars["class_body"]["value"] = $db->getField("class_body")->getValue();
			}
            if (strlen($db->getField("framework_css")->getValue()))
            {
                $layout_vars["framework_css"]["id"] = $ID;
                $layout_vars["framework_css"]["value"] = $db->getField("framework_css")->getValue();
            }            

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_sect WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					$layout_vars["sect"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["sect"][$db2->getField("name")->getValue()]["value"] = $db2->getField("value")->getValue();
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_css WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					if(!strlen($db2->getField("priority")->getValue()))
						$priority = "top";
					else
						$priority = $db2->getField("priority")->getValue();

					$layout_vars["css"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["css"][$db2->getField("name")->getValue()]["value"]["path"] = ($db2->getField("path")->getValue() ? $db2->getField("path")->getValue() : null);
					$layout_vars["css"][$db2->getField("name")->getValue()]["value"]["file"] = $db2->getField("file")->getValue();
					$layout_vars["css"][$db2->getField("name")->getValue()]["exclude_compact"] =  $db2->getField("exclude_compact")->getValue();
					$layout_vars["css"][$db2->getField("name")->getValue()]["priority"] = $priority;
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_js WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					if(!strlen($db2->getField("priority")->getValue()))
						$priority = "top";
					else
						$priority = $db2->getField("priority")->getValue();

					if(strlen($db2->getField("plugin_path")->getValue()))
					{
						if(file_exists(FF_DISK_PATH . $db2->getField("plugin_path")->getValue())) {
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["id"] = $ID;
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["value"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["value"]["file"] = basename($db2->getField("plugin_path")->getValue());
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["priority"] = $priority;
						}
						if(strlen($db2->getField("js_path")->getValue()))
						{
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["path"] = "/themes/" . $layout_vars["theme"] . "/javascript" . ffCommon_dirname($db2->getField("js_path")->getValue());
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["file"] = basename($db2->getField("js_path")->getValue());
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["priority"] = $priority;
						}
						else
						{
							if(file_exists(FF_DISK_PATH . ffCommon_dirname($db2->getField("plugin_path")->getValue()) . "/observe.js"))
							{
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["id"] = $ID;
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["value"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["value"]["file"] = "observe.js";
                                $layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
                                $layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["priority"] = $priority;
							}
                            elseif(file_exists(FF_DISK_PATH . ffCommon_dirname($db2->getField("plugin_path")->getValue()) . "/" . basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe.js"))
							{
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["id"] = $ID;
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["file"] = basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe.js";
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["priority"] = $priority;
							}
						}
					}
					elseif (strlen($db2->getField("js_path")->getValue()))
					{
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["id"] = $ID;
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["path"] = "/themes/" . $layout_vars["theme"] . "/javascript" . ffCommon_dirname($db2->getField("js_path")->getValue());
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["file"] = basename($db2->getField("js_path")->getValue());
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["priority"] = $priority;
					}
					elseif (strlen($db2->getField("file")->getValue()))
					{
						$layout_vars["js"][$db2->getField("name")->getValue()]["id"] = $ID;
						$layout_vars["js"][$db2->getField("name")->getValue()]["value"]["path"] = ($db2->getField("path")->getValue() ? $db2->getField("path")->getValue() : null);
						$layout_vars["js"][$db2->getField("name")->getValue()]["value"]["file"] = $db2->getField("file")->getValue();
						$layout_vars["js"][$db2->getField("name")->getValue()]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
						$layout_vars["js"][$db2->getField("name")->getValue()]["priority"] = $priority;
					}
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_meta WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					$layout_vars["meta"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["meta"][$db2->getField("name")->getValue()]["value"] = $db2->getField("content")->getValue();
				} while ($db2->nextRecord());
			}
		} while($db->nextRecord());
	}

	return $layout_vars;
}

function MainRecord_on_done_action(ffRecord_base $oRecord, $frmAction)
{
	if (CM_ENABLE_MEM_CACHING)
		ffMemCache::getInstance()->clear("__cm_layout__");
}
