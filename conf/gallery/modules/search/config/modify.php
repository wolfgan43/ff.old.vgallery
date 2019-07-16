<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(!isset($_REQUEST["keys"]["searchcnf-ID"])) {
    $db_gallery->query("SELECT module_search.*
                            FROM 
                                module_search
                            WHERE 
                                module_search.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["searchcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } else {
		if($_REQUEST["keys"]["ID"] > 0) {
	    	$db_gallery->execute("DELETE
		                            FROM 
		                                modules
		                            WHERE 
		                                modules.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
		                        );
		    if($_REQUEST["XHR_DIALOG_ID"]) {
			    die(ffCommon_jsonenc(array("resources" => array("modules"), "close" => true, "refresh" => true), true));
		    } else {
			    ffRedirect($_REQUEST["ret_url"]);
		    }
        } 
	}
}

if($_REQUEST["keys"]["searchcnf-ID"] > 0)
{
	$module_search_title = ffTemplate::_get_word_by_code("modify_module_search");
	$db_gallery->query("SELECT module_search.*
                            FROM 
                                module_search
                            WHERE 
                                module_search.ID = " . $db_gallery->toSql($_REQUEST["keys"]["searchcnf-ID"], "Number")
                        );
    if($db_gallery->nextRecord()) {
		$module_search_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_search_title = ffTemplate::_get_word_by_code("addnew_module_search");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SearchConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("search_modify");
$oRecord->src_table = "module_search";
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "search")) . $module_search_title . '</h1>';

$sSQL = "SELECT cm_layout.* 
        FROM cm_layout 
        WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $framework_css = Cms::getInstance("frameworkcss")->getFramework($db_gallery->getField("framework_css", "Text", true));
    $template_framework = $framework_css["name"];
} 

/*
if(check_function("MD_search_config_on_do_action"))
	$oRecord->addEvent("on_do_action", "MD_search_config_on_do_action");
*/
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "searchcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

if($_REQUEST["keys"]["searchcnf-ID"]) {
    
    
    $group_settings = "settings";
    $oRecord->addContent(null, true, $group_settings); 
    
    $oRecord->groups[$group_settings] = array(
                                                "title" => ffTemplate::_get_word_by_code("layout_" . $group_settings)
                                                //, "title_class" => "dialogSubTitleTab dep-settings"
												, "tab_dialog" => true
                                                , "cols" => 1
                                                , "class" => ""
                                                 //, "tab" => "settings"
                                              );  
    
}

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("search_config_name");
$oField->required = true;
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("publishing_area");
$oField->widget = "activecomboex";
$oField->multi_pairs = array (
                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
                            array(new ffData("gallery"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
                            array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery")))
                       );      
$oField->actex_child = "contest";
if($_REQUEST["keys"]["searchcnf-ID"]) {
    $oField->properties["disabled"] = "disabled"; 
}
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                        SELECT nameID, name, type FROM
                        (
	                        (
		                        SELECT 
		                            name AS nameID, 
		                            name,
		                            'vgallery' AS type
		                        FROM 
		                            vgallery
		                        WHERE vgallery.status > 0
	                        ) 
	                        UNION 
	                        (
		                        SELECT 
		                            'files' AS nameID, 
		                            'files' AS name,
		                            'gallery' AS type
	                        )
                                UNION 
	                        (
		                        SELECT 
                                                anagraph_categories.smart_url AS nameID
                                                , anagraph_categories.name AS name
                                                , 'anagraph' AS type
                                            FROM
                                                anagraph_categories
	                        )
                        ) AS tbl_src
                        [WHERE]";  
$oField->actex_father = "area";
$oField->actex_child = "relative_path";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
if($_REQUEST["keys"]["searchcnf-ID"]) {
    $oField->properties["disabled"] = "disabled"; 
}
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "relative_path";
$oField->label = ffTemplate::_get_word_by_code("search_relative_path");
$oField->actex_hide_empty = true;
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT nameID, name, type FROM
                    (
                        (
	                        SELECT 
	                            IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS nameID
	                            , IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS name
	                            , vgallery.name AS type
	                        FROM
	                            vgallery_nodes
	                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                        WHERE vgallery_nodes.name <> ''
	                        	AND vgallery_nodes.is_dir > 0
	                        HAVING name <> '/'
	                        ORDER BY type, name
                        )
                    ) AS tbl_src
                    [WHERE]
                    ORDER BY tbl_src.name";
$oField->actex_father = "contest";
$oField->actex_hide_empty = "all";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;    
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("all");
$oField->multi_select_one_val = new ffData("/");
$oRecord->addContent($oField, $group_settings);

if($_REQUEST["keys"]["searchcnf-ID"] > 0)
{
$oRecord->addContent(null, true, "field"); 

$group_field = "field";

$oRecord->groups[$group_field] = array(
                                "title" => ffTemplate::_get_word_by_code("search_config_fields")
                                , "tab_dialog" => true
                                , "cols" => 1
                                , "class" => ""
                            );


$oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    //$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
    $oGrid->id = "SearchFields";
    $oGrid->source_SQL = "SELECT module_search_fields.*  
                                                    FROM module_search_fields
                                                    WHERE module_search_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["searchcnf-ID"], "Number") . "
                                                    [AND] [WHERE] 
                                                    [HAVING] 
                                                    [ORDER]";
    $oGrid->order_default = "searchcnfield-ID";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->use_paging = false;
    $oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/modules/search/extra/modify";
    $oGrid->record_id = "SearchExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "search_fields"
                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
            )
            , "searchcnfield-ID"
        )
    );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "searchcnfield-ID";
    $oField->base_type = "Number";
    $oField->data_source = "ID";
    $oField->order_SQL = " `order`";
    $oGrid->addKeyField($oField, $group_field);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_name");
    $oField->required = true;
    $oGrid->addContent($oField, $group_field);

    $oRecord->addContent($oGrid, "field");
    $cm->oPage->addContent($oGrid);
}   
    
$cm->oPage->addContent($oRecord);
?>
