<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$maps_position = array (
							array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
							array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
							array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
							array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
							array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
							array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
							array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
							array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
							array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
							array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
							array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
							array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
						);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["mapscnf-ID"])) {
    $db_gallery->query("SELECT module_maps.*
                            FROM 
                                module_maps
                            WHERE 
                                module_maps.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["mapscnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
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

if($_REQUEST["keys"]["mapscnf-ID"] > 0)
{
	$module_maps_title = ffTemplate::_get_word_by_code("modify_module_maps");
	$db_gallery->query("SELECT module_maps.*
                            FROM 
                                module_maps
                            WHERE 
                                module_maps.ID = " . $db_gallery->toSql($_REQUEST["keys"]["mapscnf-ID"], "Number")
                        );
    if($db_gallery->nextRecord()) {
        $module_maps_title .= ": " . $db_gallery->getField("name", "Text", true);
        $data_limit = $db_gallery->getField("data_limit", "Text", true);
    }
} else
{
	$module_maps_title = ffTemplate::_get_word_by_code("addnew_module_maps");
}
$oRecord->id = "MapsConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("maps_modify");
$oRecord->src_table = "module_maps";
$oRecord->addEvent("on_do_action", "MapsConfigModify_on_do_action");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "maps")) . $module_maps_title . '</h1>';


if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

if(isset($_REQUEST[$oRecord->id . "_data_limit"])) {
    $ID_vgallery = $_REQUEST[$oRecord->id . "_data_limit"];
} elseif(strlen($data_limit)) {
    $ID_vgallery = $data_limit;
}
    
$oField = ffField::factory($cm->oPage);
$oField->id = "mapscnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("module_maps_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_maps_general")
                                 , "cols" => 1
                                 , "tab" => "general"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("maps_name");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("maps_contest");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT nameID, name, type, type_description FROM
                    (
                        (
                        SELECT 
                            'nomarker' AS nameID, 
                            " . $db_gallery->tosql(ffTemplate::_get_word_by_code("no_marker"))    . " AS name,
                            '' AS type
                            , '' AS type_description
                        ) 
                        UNION                        
                        (
                        SELECT 
                            'all' AS nameID, 
                            " . $db_gallery->tosql(ffTemplate::_get_word_by_code("all"))    . " AS name,
                            '' AS type
                            , '' AS type_description
                        ) 
                        UNION                        
                        (
                        SELECT 
                            'anagraph' AS nameID, 
                            " . $db_gallery->tosql(ffTemplate::_get_word_by_code("anagraph")) . " AS name,
                            '' AS type
                            , '' AS type_description
                        ) 
                        UNION                        
                        (
                        SELECT 
                            'custom' AS nameID, 
                            " . $db_gallery->tosql(ffTemplate::_get_word_by_code("custom")) . " AS name,
                            '' AS type
                            , '' AS type_description
                        ) 
                        UNION
                        (
                        SELECT 
                            'vgallery' AS nameID, 
                            " . $db_gallery->tosql(ffTemplate::_get_word_by_code("vgallery")) . " AS name,
                            '' AS type
                            , 'vgallery' AS type_description
                        ) 
                        
                    ) AS tbl_src
                    [WHERE]";  
$oField->actex_child = array("relative_path", "data_limit","description_type");
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("maps_load_marker_by_map_center");
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_limit";
$oField->container_class = "data_limit";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_data_limit");
$oField->widget = "activecomboex";
$oField->control_type = "checkbox";
$oField->actex_update_from_db = true;
$oField->grouping_separator = ",";  
$oField->source_SQL = "SELECT nameID, name, type FROM
                            (SELECT ID AS nameID, 
                                name,
                                'vgallery' AS type
                                FROM vgallery
                                WHERE vgallery.status > 0
                            )AS tbl_src
                    [WHERE]";
$oField->actex_related_field = "type";
$oField->actex_father = "contest";
$oField->actex_hide_empty = "all"; 
$oRecord->addContent($oField, "general");  

$oField = ffField::factory($cm->oPage);
$oField->id = "description_type";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_description_type");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT nameID, name, type FROM
                            (SELECT 'ajax_descrition' AS nameID
                                , 'Ajax description' AS name
                                , 'vgallery' AS type
                                UNION
                            SELECT 'custom_descrition' AS nameID
                                , 'Custom description' AS name
                                , 'vgallery' AS type
                            )AS tbl_src
                    [WHERE]";
$oField->actex_related_field = "type";
$oField->actex_father = "contest";
$oField->actex_hide_empty = "all"; 
if($_REQUEST["XHR_DIALOG_ID"])
    $oField->actex_on_change = "function(obj, old_value, action) { 
    	if(action == 'change') {
    		ff.ffPage.dialog.doRequest('" . $_REQUEST["XHR_DIALOG_ID"] . "', {'action' : 'refresh'}); 
    	}
    }";
else
    $oField->actex_on_change = "function(obj, old_value, action) { 
    	if(action == 'change') {
    		ff.ajax.doRequest({'action' : 'refresh'}); 
    	}
    }";
$oRecord->addContent($oField, "general"); 

if(strlen($ID_vgallery)) 
{   
    $sSQL = "SELECT GROUP_CONCAT(limit_type) AS type_considered
                FROM vgallery
                WHERE ID IN (" . $db_gallery->toSql($ID_vgallery, "Text", true) . ")";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $limit = $db_gallery->getField("type_considered", "text", true);
    } 
    
    if(strlen($limit)) 
    { 
        $oField = ffField::factory($cm->oPage);
        $oField->id = "description_limit";
        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_description_limit");
        $oField->widget = "activecomboex";
        $oField->control_type = "checkbox";
        $oField->actex_update_from_db = true;
        $oField->grouping_separator = ",";  
        $oField->required = true;
        $oField->source_SQL = "SELECT vgallery_fields.ID AS nameID
                                    , CONCAT (vgallery_type.name, ' - ', vgallery_fields.name) AS name
                                    , 'custom_descrition' AS type
                                    , vgallery_type.ID AS ID_vgallery
                                FROM vgallery_fields
                                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                                WHERE vgallery_type.ID > 2
                                    AND FIND_IN_SET(vgallery_fields.ID_type,'" . $db_gallery->toSql($limit, "Text", false) . "')";
        $oField->actex_hide_empty = "all"; 
        $oRecord->addContent($oField, "general");
    }
}


$oField = ffField::factory($cm->oPage);
$oField->id = "relative_path";
$oField->label = ffTemplate::_get_word_by_code("maps_relative_path");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT nameID, name, type FROM
                    (
                        (
	                        SELECT 
	                            anagraph_categories.ID AS nameID
	                            , anagraph_categories.name AS name
	                            , 'anagraph' AS type
	                        FROM
	                            anagraph_categories
	                        ORDER BY name
                        )
						UNION
                        (
	                        SELECT 
	                            IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS nameID
	                            , IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS name
	                            , vgallery.name AS type
	                        FROM
	                            vgallery_nodes
	                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                        WHERE
                        		(vgallery_nodes.is_dir > 0)
	                        ORDER BY type, name
                        )
                    ) AS tbl_src
                    [WHERE]";
$oField->actex_father = "contest";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
$oField->actex_hide_empty = "all"; 
//$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_grid";
$oField->label = ffTemplate::_get_word_by_code("maps_enable_grid");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_grid_search";
$oField->label = ffTemplate::_get_word_by_code("maps_enable_grid_search");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "general");

$oRecord->addTab("map");
$oRecord->setTabTitle("map", ffTemplate::_get_word_by_code("module_maps_map"));

$oRecord->addContent(null, true, "map"); 
$oRecord->groups["map"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_maps_map")
                                 , "cols" => 1
                                 , "tab" => "map"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "coords";
$oField->label = ffTemplate::_get_word_by_code("maps_coords");
$oField->widget = "gmap";
$oField->gmap_draggable = true;
$oField->gmap_start_zoom = 10;
$oField->gmap_force_search = true;
if(check_function("set_field_gmap")) { 
	$oField = set_field_gmap($oField);
}
$oField->properties["style"]["height"] = "600px";
$oRecord->addContent($oField, "map");


$oRecord->addTab("mapcontrol");
$oRecord->setTabTitle("mapcontrol", ffTemplate::_get_word_by_code("module_maps_mapcontrol"));

$oRecord->addContent(null, true, "mapcontrol"); 
$oRecord->groups["mapcontrol"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_maps_mapcontrol")
                                 , "cols" => 1
                                 , "tab" => "mapcontrol"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "enableMarkerCluster";
$oField->label = ffTemplate::_get_word_by_code("maps_enableMarkerCluster");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "markerClusterMaxZoom";
$oField->label = ffTemplate::_get_word_by_code("maps_MarkerClusterMaxZoom");
$oField->base_type = "Number";
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "markerClusterDim";
$oField->label = ffTemplate::_get_word_by_code("maps_MarkerClusterDim");
$oField->base_type = "Number";
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "enableZoomControl";
$oField->label = ffTemplate::_get_word_by_code("maps_ZoomControl");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "ZoomControlStyle";
$oField->label = ffTemplate::_get_word_by_code("maps_ZoomControlStyle");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
						array(new ffData(SMALL), new ffData("SMALL")),
						array(new ffData(LARGE), new ffData("LARGE")),
//						array(new ffData(DEFAULT), new ffData("DEFAULT"))
					);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "ZoomControlPosition";
$oField->label = ffTemplate::_get_word_by_code("maps_ZoomControlPosition");
$oField->extended_type = "Selection";
$oField->multi_pairs = $maps_position;
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "enableMapTypeControl";
$oField->label = ffTemplate::_get_word_by_code("maps_MapTypeControl");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "MapTypeControlStyle";
$oField->label = ffTemplate::_get_word_by_code("maps_MapTypeControlStyle");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
						array(new ffData(HORIZONTAL_BAR), new ffData("HORIZONTAL_BAR")),
						array(new ffData(DROPDOWN_MENU), new ffData("DROPDOWN_MENU")),
//						array(new ffData(DEFAULT), new ffData("DEFAULT"))
					);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "enablePanControl";
$oField->label = ffTemplate::_get_word_by_code("maps_PanControl");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "PanControlPosition";
$oField->label = ffTemplate::_get_word_by_code("maps_PanControlPosition");
$oField->extended_type = "Selection";
$oField->multi_pairs = $maps_position;
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "enableScaleControl";
$oField->label = ffTemplate::_get_word_by_code("maps_ScaleControl");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "ScaleControlPosition";
$oField->label = ffTemplate::_get_word_by_code("maps_ScaleControlPosition");
$oField->extended_type = "Selection";
$oField->multi_pairs = $maps_position;
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "enableStreetViewControl";
$oField->label = ffTemplate::_get_word_by_code("maps_StreetViewControl");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "StreetViewControlPosition";
$oField->label = ffTemplate::_get_word_by_code("maps_StreetViewControlPosition");
$oField->extended_type = "Selection";
$oField->multi_pairs = $maps_position;
$oRecord->addContent($oField, "mapcontrol");

	$oField = ffField::factory($cm->oPage);
$oField->id = "enablePersonalColor";
$oField->label = ffTemplate::_get_word_by_code("maps_enablePersonalColor");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
	//$oField->fixed_post_content = '<iframe src="http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html" width="800" height="800"></iframe>';
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

	$oField = ffField::factory($cm->oPage);
$oField->id = "disableScroll";
$oField->label = ffTemplate::_get_word_by_code("maps_disableScroll");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

	$oField = ffField::factory($cm->oPage);
$oField->id = "disableDrag";
$oField->label = ffTemplate::_get_word_by_code("maps_disableDrag");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "mapcontrol");

$oField = ffField::factory($cm->oPage);
$oField->id = "PersonalColor";
$oField->label = ffTemplate::_get_word_by_code("maps_PersonalColor");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "mapcontrol");

	$oRecord->addTab("mapwizard");
	$oRecord->setTabTitle("mapwizard", ffTemplate::_get_word_by_code("module_maps_mapwizard"));

	$oRecord->addContent(null, true, "mapwizard");
	$oRecord->groups["mapwizard"] = array(
									 "title" => ffTemplate::_get_word_by_code("module_maps_mapwizard")
									 , "cols" => 1
									 , "tab" => "mapwizard"
								  );
	$oRecord->addContent('<iframe src="http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html" width="100%" height="800"></iframe>', "mapwizard");

$oRecord->addTab("markericon");
$oRecord->setTabTitle("markericon", ffTemplate::_get_word_by_code("module_markericon"));

$oRecord->addContent(null, true, "markericon"); 
$oRecord->groups["markericon"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_markericon")
                                 , "cols" => 1
                                 , "tab" => "markericon"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "icon";
$oField->label = ffTemplate::_get_word_by_code("maps_icon");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->control_type = "file";
$oField->file_max_size = Auth::env("MAX_UPLOAD");
$oField->file_base_path = FF_DISK_PATH . FF_THEME_DIR;
$oField->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/" . "[name_VALUE]";
$oField->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps";
$oField->file_show_filename = true;  
$oField->file_show_delete = true;
$oField->file_normalize = true;
$oField->file_show_preview = true; 
$oField->file_check_exist = false; 
$oField->file_full_path = false;
$oField->file_saved_view_url = CM_SHOWFILES . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/[name_VALUE]/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/thumb/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/[name_VALUE]/[_FILENAME_]";
$oField->file_temp_view_url = CM_SHOWFILES . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/[_FILENAME_]";
$oField->file_temp_preview_url = CM_SHOWFILES . "/thumb/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oRecord->addContent($oField, "markericon");


	$oField = ffField::factory($cm->oPage);
	$oField->id = "icon_width";
	$oField->label = ffTemplate::_get_word_by_code("maps_icon_width");
	$oField->base_type = "Number";
	$oRecord->addContent($oField, "markericon");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "icon_height";
	$oField->label = ffTemplate::_get_word_by_code("maps_icon_height");
	$oField->base_type = "Number";
	$oRecord->addContent($oField, "markericon");



$cm->oPage->addContent($oRecord);

function MapsConfigModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
	}	
}
