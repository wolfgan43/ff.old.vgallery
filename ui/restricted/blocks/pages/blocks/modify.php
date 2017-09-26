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

if (!AREA_LAYOUT_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

$globals = ffGlobals::getInstance("gallery");
$db = ffDB_Sql::factory();

/**
 * Check Repair
 */
if($_REQUEST["repair"]) {
    /*if(check_function("check_fs"))
        check_fs(DISK_UPDIR, "/"); */

    $sSQL = "SELECT COUNT( * ) 
	            , layout_settings_rel.*
	        FROM layout_settings_rel
	        GROUP BY `ID_layout` , `ID_layout_settings`
	        HAVING COUNT( * ) > 1";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $arrCheckSettings[] = "DELETE FROM layout_settings_rel 
	                WHERE ID_layout = " . $db->toSql($db->getField("ID_layout", "Number")) . " 
	                    AND ID_layout_settings = " . $db->toSql($db->getField("ID_layout_settings", "Number")) . " 
	                    AND ID <> " . $db->toSql($db->getField("ID", "Number"));

        } while ($db->nextRecord());

        if(is_array($arrCheckSettings) && count($arrCheckSettings))
        {
            foreach($arrCheckSettings AS $arrCheckSettings_value) {
                $db->execute($arrCheckSettings_value);
            }
        }
    }

    if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/template")) {
        if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/template")) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/template");
        }
    }
    if(check_function("get_locale")) {
        $arrLang = get_locale("lang", true);

        if(is_array($arrLang) && count($arrLang)) {
            foreach($arrLang AS $lang_code => $lang) {
                if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template" . "/" . $lang_code)) {
                    if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template" . "/" . $lang_code)) {
                        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template" . "/" . $lang_code);
                    }
                }
            }
        }
    }

}

$real_path_info = trim($cm->real_path_info, "/");
$location = $_REQUEST["location"];
$layout_path = stripslash($_REQUEST["path"]);
if($layout_path == "")
    $layout_path = "/";


/***
 *  Load Block Type Settings
 */
if(check_function("system_get_sections"))
    $block_type = system_get_block_type();

if(is_array($block_type["smart_url"]) && count($block_type["smart_url"]))
{
    foreach($block_type["smart_url"] AS $block_type_smart_url)
    {
        if($block_type[$block_type_smart_url]["url"] || $block_type[$block_type_smart_url]["sub_url"])
        {
            $str_layout_type .= '
                ff.cms.admin.type["LT' . $block_type[$block_type_smart_url]["ID"] . '"] = {
                        "url" : "' . $block_type[$block_type_smart_url]["url"] . '",
                        "add" : "' . $block_type[$block_type_smart_url]["file_add"] . '",
                        "edit" : "' . $block_type[$block_type_smart_url]["file_edit"] . '",
                        "delete" : "' . ffDialog(TRUE,
                    "yesno",
                    ffTemplate::_get_word_by_code("ffDialog_title"),
                    ffTemplate::_get_word_by_code("ffDialog_description"),
                    "[CLOSEDIALOG]",
                    FF_SITE_PATH . $block_type[$block_type_smart_url]["url"] . "/" . $block_type[$block_type_smart_url]["file_delete"] . "--key--",
                    stripslash($block_type[$block_type_smart_url]["file_delete"]) . "/dialog"
                ) . '",
                        "key" : "' . $block_type[$block_type_smart_url]["key_name"] . '",
                        "resource" : "' . $block_type[$block_type_smart_url]["resource"] . '",
                        "useID" : "' . $block_type[$block_type_smart_url]["use_key_ID"] . '",
                        "sub" : {
                                "url" : "' . $block_type[$block_type_smart_url]["sub_url"] . '",
                                "add" : "' . $block_type[$block_type_smart_url]["sub_file_add"] . '",
                                "edit" : "' . $block_type[$block_type_smart_url]["sub_file_edit"] . '",
                                "delete" : "' . ffDialog(TRUE,
                    "yesno",
                    ffTemplate::_get_word_by_code("ffDialog_title"),
                    ffTemplate::_get_word_by_code("ffDialog_description"),
                    "[CLOSEDIALOG]",
                    FF_SITE_PATH . $block_type[$block_type_smart_url]["sub_url"] . "/" . $block_type[$block_type_smart_url]["sub_file_delete"] . "--key--",
                    stripslash($block_type[$block_type_smart_url]["sub_file_delete"]) . "/dialog"
                ) . '",
                                "key" : "' . $block_type[$block_type_smart_url]["sub_key_name"] . '",
                                "resource" : "' . $block_type[$block_type_smart_url]["sub_resource"] . '",
                                "useID" : "' . $block_type[$block_type_smart_url]["sub_use_key_ID"] . '"
                        }
                };';
            if(strlen($block_type[$block_type_smart_url]["resource"])) {
                $actex_resources[] = $block_type[$block_type_smart_url]["resource"];
            }
            if(strlen($block_type[$block_type_smart_url]["sub_resource"])) {
                if(strlen($block_type[$block_type_smart_url]["url"])) {
                    $actex_sub_resources[] = $block_type[$block_type_smart_url]["sub_resource"];
                    if(strlen($block_type[$block_type_smart_url]["resource"])) {
                        $actex_sub_resources[] = $block_type[$block_type_smart_url]["resource"];
                    }
                } else {
                    if(strlen($block_type[$block_type_smart_url]["resource"])) {
                        $actex_sub_resources[] = $block_type[$block_type_smart_url]["resource"];
                    } else {
                        $actex_sub_resources[] = $block_type[$block_type_smart_url]["sub_resource"];
                    }
                }
            }
        }
    }
}

$static_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/*");
if(is_array($static_file) && count($static_file)) {
    foreach($static_file AS $real_file) {
        if(is_file($real_file)) {
            $relative_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file);
            $sSQL_file .= " (
                            SELECT 
                                " . $db->toSql($relative_path, "Text") . " AS nameID
                                , " . $db->toSql(basename($real_file), "Text") . " AS name
                                , " .  $block_type["static-page-by-file"]["ID"] . " AS type
                                , " . $db->toSql(basename($real_file), "Text") . " AS real_name
                            )
                            UNION";
        }
    }
}

//$arrApplets = system_get_block_applets($block_type);

$sSQL_tblsrc = "
	
				SELECT tbl_src.*
				FROM (
                    (SELECT
                            layout_type.ID
                            , layout_type.description
                            , layout_type.`group` AS `group`
                            , layout_type.`class` AS `class`
							, layout_type.row_template AS row_template
							, layout_type.coloumn_template AS coloumn_template
                            , layout_type.`priority` AS `priority`
							, layout_type.order
                        FROM
                                layout_type
                        WHERE 
                                NOT(layout_type.disable_in_block > 0)
                        ORDER BY `group`
                                , IF(layout_type.url = '' AND layout_type.sub_url = '', 1, 0)
                                , layout_type.description
					)
					" . ($block_type["sql"]["tblsrc"]
        ? " UNION " . $block_type["sql"]["tblsrc"]
        : ""
    ) . "
                ) AS tbl_src
				ORDER BY row_template, `order`, description";

$sSQL_items = "SELECT nameID, name, type, real_name FROM
	            (
	                SELECT nameID, name, type, real_name FROM
	                (
	                    (
	                        SELECT 
	                            ID AS nameID
	                            , REPLACE(name, '-', ' ') AS name
	                            , " . $block_type["static-page-by-db"]["ID"] . " AS type
	                            , name AS real_name
	                        FROM 
	                            drafts
	                        WHERE drafts.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
	                    ) 
	                    UNION 
	                        $sSQL_file
	                    (
	                        SELECT 
	                            module_params AS nameID 
	                            , REPLACE(module_params, '-', ' ') AS name
	                            , module_name AS type
	                            , CONCAT(module_name, '-', module_params) AS real_name
	                        FROM
	                            modules
	                    )
	                    UNION
	                    (
	                        SELECT 
	                            CONCAT(IF(parent = '/', '', parent), '/', name) AS nameID 
	                            , CONCAT(IF(parent = '/', '', parent), '/', name) AS name
	                            , " . $block_type["gallery"]["ID"] . " AS type
	                            , name AS real_name
	                        FROM
	                            files
	                        WHERE files.is_dir > 0
	                    )
	                    UNION
	                    (
	                        SELECT 
	                            CONCAT(IF(parent = '/', '', parent), '/', name) AS nameID 
	                            , CONCAT(IF(parent = '/', '', parent), '/', name) AS name
	                            , " . $block_type["gallery-menu"]["ID"] . " AS type
	                            , name AS real_name
	                        FROM
	                            files
	                        WHERE files.is_dir > 0
	                    )
	                    UNION 
	                    (
	                        SELECT 
	                            name AS nameID 
	                            , REPLACE(name, '-', ' ') AS name 
	                            , " . $block_type["virtual-gallery"]["ID"] . " AS type
	                            , name AS real_name
	                        FROM
	                            vgallery
	                        WHERE vgallery.status > 0
                            	AND vgallery.public = 0
	                    )
	                    UNION 
	                    (
	                        SELECT 
	                            'anagraph' AS nameID 
	                            , 'anagraph' AS name 
	                            , " . $block_type["virtual-gallery"]["ID"] . " AS type
	                            , 'anagraph' AS real_name
	                        FROM anagraph_type
	                        LIMIT 1
	                    )
	                    UNION 
	                    (
	                        SELECT 
	                            CONCAT(IF(parent = '/', '', parent), '/', name) AS nameID 
	                            , CONCAT(IF(parent = '/', '', parent), '/', name) AS name
	                            , " . $block_type["vgallery-menu"]["ID"] . " AS type
	                            , name AS real_name
	                        FROM
	                            vgallery_nodes
	                        WHERE name <> ''
	                        	AND is_dir > 0
	                    )
		                UNION 
		                (
		                    SELECT 
		                        CONCAT(IF(parent = '/', '', parent), '/', name) AS nameID 
		                        , CONCAT(IF(parent = '/', '', parent), '/', name) AS name
		                        , " . $block_type["static-pages-menu"]["ID"] . " AS type
		                        , name AS real_name
		                    FROM
		                        static_pages
		                    WHERE 1
		                        AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
		                )
		                UNION 
		                (
		                    SELECT DISTINCT 
		                        CONCAT(area, '_', ID) AS nameID 
		                        , REPLACE(CONCAT(name, ' (', IF(contest = '', " . $db->toSql(ffTemplate::_get_word_by_code("all")) . ", contest), ')'), '-', ' ') AS name 
		                        , " . $block_type["publishing"]["ID"] . " AS type
		                        , name AS real_name
		                    FROM
		                        publishing
		                )
						UNION 
						(
		                    SELECT DISTINCT 
		                        vgallery_groups_menu.ID AS nameID 
		                        , REPLACE(vgallery_groups_menu.name, '-', ' ') AS name
		                        , " . $block_type["vgallery-group"]["ID"] . " AS type
		                        , name AS real_name
		                    FROM
		                        vgallery_groups_menu
		                )
		                " . ($block_type["sql"]["items"]
        ? " UNION " . $block_type["sql"]["items"]
        : ""
    ) . "
		            ) AS tbl_src ORDER BY type, name
		        ) AS macro_tbl
		        [WHERE]
		        ORDER BY macro_tbl.type, macro_tbl.name";

$db->query("SELECT layout_location.* 
	        FROM layout_location 
	        WHERE " . (is_numeric($location)
        ? "layout_location.ID = " . $db->toSql($location, "Number")
        : "name = " . $db->toSql($location)
    ));
if($db->nextRecord()) {
    $ID_location = $db->getField("ID", "Number", true);
}

$currentType = $block_type[$block_type["smart_url"][$real_path_info]];
/*if(!$currentType) {
    $block_key = substr($real_path_info, 0, strpos($real_path_info, "-"));
    if($block_key == "applet") {
        $currentType = $block_type["forms-framework"];
    } else {
        $currentType = $block_type[$block_type["smart_url"][$block_key]];
    }

    if($currentType) {
        $item_name = substr($real_path_info, strpos($real_path_info, "-") + 1);
    }
}*/

if(!$_REQUEST["keys"]["ID"] && $real_path_info) {
    if($item_name) {
        $db->query(str_replace(array("[WHERE]", "ORDER BY"), array("WHERE 1", "HAVING type = " . $currentType["ID"] . " AND real_name = " . $db->toSql($item_name) . " ORDER BY"), $sSQL_items));
        if($db->nextRecord()) {
            //$item = $db->getField("nameID", "Text", true);
            $block_title = $item_name;
            $item_default_value = $db->getField("nameID", "Text", true);
        }
    }



    $sSQL = "SELECT layout.ID				AS ID
	         FROM layout
	         WHERE layout.smart_url = " . $db->toSql($real_path_info)
        . ($item_default_value
            ? "OR (layout.ID_type = " . $db->toSql($currentType["ID"], "Number") . "
	         			AND layout.value = " . $db->toSql($item_default_value) . "
	         		)"
            : ""
        );
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
    }
}

if(isset($_REQUEST["keys"]["ID"]))
{
    $sSQL = "SELECT layout.ID_type 						AS ID_type
                , layout.params 						AS layout_params
                , layout.value 							AS layout_value
                , layout.smart_url 						AS smart_url
                , layout.template 						AS template_thumb
                , layout.template_detail 				AS template_detail
             FROM layout
             WHERE layout.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $block_smart_url = $db->getField("smart_url", "Text", true);
        $currentType = $block_type[$block_type["rev"][$db->getField("ID_type", "Number", true)]];
        $tbl_src = $currentType["ID"];
        //$item = $db->getField("layout_value", "Text", true);
        //$subitem = $db->getField("layout_params", "Text", true);
        if($currentType["group"] == "addon" || $currentType["group"] == "applet")
            $item = $db->getField("layout_value", "Text", true);
    }
} else {
    $tbl_src = $currentType["ID"];
    if($_REQUEST["item"])
        $item_name = $_REQUEST["item"];
    elseif($currentType["group"] == "addon")
        $item = $real_path_info;
    elseif($currentType["group"] == "applet")
        $item = "/" . $real_path_info;
}

if($currentType["child"][$item]["icon"])
    $currentType["icon"] = $currentType["child"][$item]["icon"];

if(!$tbl_src)
    $tbl_src_value_js = ''; // 'jQuery("#LayoutModify_tblsrc option:selected").text()';

if(!$block_title)
    $block_title = ($item ? $item : $currentType["description"]);

if($cm->real_path_info == "/add") {
    $cm->real_path_info = "";
    $add_block_type = "creation";
}

if(!isset($_REQUEST["keys"]["ID"]) && !strlen(basename($cm->real_path_info)))
{
    $cm->oPage->addContent(add_block($add_block_type));
    //$cm->oPage->tplAddJs("ff.cms.admin.block");
} else {
    $oRecord = ffRecord::factory($cm->oPage);
    $oRecord->id = "LayoutModify";
    $oRecord->resources[] = $oRecord->id;
    $oRecord->tab = true;

    system_ffcomponent_set_title(
        $st_layout_name
        , array(
            "name" => $currentType["icon"]
        , "type" => $currentType["group"]
        )
        , false
        , false
        , $oRecord
    );

    $oRecord->src_table = "layout";
    $oRecord->addEvent("on_do_action", "LayoutModify_on_do_action");
    $oRecord->addEvent("on_done_action", "LayoutModify_on_done_action");
    $oRecord->addEvent("on_loaded_data", "LayoutModify_on_loaded_data");
    $oRecord->user_vars["currentType"] = $currentType;
    $oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oRecord->addKeyField($oField);

    /***********
     *  Group General
     */

    $group_general = "source";
    $oRecord->addContent(null, true, $group_general);

    $oRecord->groups[$group_general] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_general)
    );

    $source_grid = 3;
    $enable_subitem = true;
    if($tbl_src)
    {
        $oRecord->insert_additional_fields["ID_type"] = new ffData($tbl_src, "Number");
        if($currentType["smart_url"] != "content" && $currentType["group"] != "addon" && $currentType["group"] != "applet")
            $enable_subitem = false;

        $source_grid++;
    } else
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "tblsrc";
        $oField->data_source = "ID_type";
        $oField->label = ffTemplate::_get_word_by_code("block_modify_ID_type");
        //$oField->widget = "activecomboex";
        $oField->widget = "actex";
        $oField->source_SQL = $sSQL_tblsrc;
        $oField->required = true;
        $oField->actex_child = array("items");
        $oField->actex_group = "group";
        /*  $oField->actex_plugin = array("name" => "selectBoxIt"
                                                                  , "path" => "selectboxit"
                                                                  , "css" => "selectboxit"
                                                                  , "js" => "selectboxit"
                                                                  , "params" => array(
                                                                          "theme" => "jqueryui"
                                                                          , "showFirstOption" => false
                                                                  )
                                                          );*/
        /* $oField->actex_attr = array("data-icon" => array("prefix" => ""
                                                                                                         , "field" => "icon"
                                                                                                         , "postfix" => ""
                                                                 )
                                                                 , "data-iconurl" => array("prefix" => "https://www.google.it/images/srpr/logo6w.png")
                                                         );*/
        $oField->actex_update_from_db = true;
        //$oField->actex_on_change = "function(obj, old_value, action) { if(action == 'change') { ff.cms.admin.getTypeUrl('LT' + obj.value, '" . $oRecord->id . "_items', obj); } }";
        $oField->actex_on_update_bt = "function(obj){ ff.cms.admin.getTypeUrl('LT' + obj.value, '" . $oRecord->id . "_items', obj); }";
        $oField->setWidthComponent($source_grid);
        $source_grid++;

        $oRecord->addContent($oField, $group_general);
    }

    if(strlen($item))
    {
        $oRecord->insert_additional_fields["value"] = new ffData($item);
        /* $js .= "ff.cms.admin.getBlock('LT' + " . ($tbl_src
                                     ? "'" . $tbl_src . "'"
                                     : "jQuery('#" . $oRecord->id . "_tblsrc option:selected').val()"
                                 ) . ", '" . $oRecord->id . "_subitems', '" . $item . "');";
       */
        //  $js .= " ff.cms.admin.getBlock('LT' + '" . $tbl_src ."', '" . $oRecord->id . "', '" . $item . "');";
    } else
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "items";
        $oField->container_class = "items";
        $oField->data_source = "value";
        //$oField->widget = "activecomboex";
        $oField->widget = "actex";
        if($tbl_src) {
            $oField->label = $currentType["description"] . ": ";
            $oField->setWidthLabel(array(4,4,12));

            $oField->source_SQL = str_replace("[WHERE]", "WHERE type = " . $db->toSql($tbl_src, "Numnber"), $sSQL_items);
        } else {
            //$oField->label = ffTemplate::_get_word_by_code("block_modify_value");
            $oField->display_label = false;
            $oField->source_SQL = $sSQL_items;
            $oField->actex_father = "tblsrc";
        }

        if($enable_subitem)
            $oField->actex_child = "subitems";

        $oField->actex_related_field = "type";
        $oField->actex_update_from_db = true;
        $oField->actex_dialog_url = "javascript:void(0);";
        $oField->actex_dialog_edit_params = array("keys[ID]" => null);
        $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigSelectionModify_confirmdelete";
//da gestire con le condizioni in base alla tipologia
        if(strpos($currentType["smart_url"], "widget-") === 0)
            $oField->actex_hide_empty = "all";

        $oField->multi_select_one_label = ffTemplate::_get_word_by_code($currentType["description"] . "_not_set");
        $oField->resources = $actex_resources;
        /*  $oField->actex_plugin = array("name" => "selectBoxIt"
                                              , "path" => "selectboxit"
                                              , "css" => "selectboxit"
                                              , "js" => "selectboxit"
                                              , "params" => array(
                                                      "theme" => "jqueryui"
                                                      , "showFirstOption" => false
                                              )
                                      );*/
        $oField->actex_on_update_bt = "function(obj) {
	    								" . ($tbl_src
                ? " ff.cms.admin.getTypeUrl('LT' + '" . $tbl_src . "', '" . $oRecord->id . "_items', obj); 
    											ff.cms.admin.getRemoteData('LT' + '" . $tbl_src . "', '/" . $real_path_info  . "', '" . $oRecord->id . "_items', obj);"
                :  ""
            ) .
            ($enable_subitem
                ? ""
                : "ff.cms.admin.setNameByBlock("
                . ($_REQUEST["keys"]["ID"]
                    ? "false"
                    : "false"
                )
                . ", " . ($tbl_src
                    ? "'" . $currentType["description"] . "'"
                    : "undefined"
                )
                . ", " . ($item && 0
                    ? "'" . $item . "'"
                    : "undefined"
                )
                . ", " . ($subitem && 0
                    ? "'" . $subitem . "'"
                    : "undefined"
                )
                . ", " . (strlen($tbl_src_value_js)
                    ? "'" . $tbl_src_value_js . "'"
                    : "'" . $currentType["description"] . "'"
                )
                . ", {
	                                        			'item' : '" . ffTemplate::_get_word_by_code("all") . "'
	                                        			, 'subitem' : '" . ffTemplate::_get_word_by_code("all") . "'
	                                        		} "
                . ");"
            ) .
            /*"ff.cms.admin.getBlock('LT' + " . ($tbl_src
                                        ? "'" . $tbl_src . "'"
                                        : "jQuery('#" . $oRecord->id . "_tblsrc option:selected').val()"
                                    ) . ", '" . $oRecord->id . "_subitems', " . ($tbl_src ? "'" . $tbl_src . "'" : "obj.getFatherValue()") . ", obj);" . */"}";
        //echo $oField->actex_on_update_bt;
        //$oField->required = true;
        $oField->default_value = new ffData($item_default_value);
        $oField->setWidthComponent($source_grid + ($currentType["smart_url"] == "content"
                ? 0
                : 5
            ) + (!isset($_REQUEST["keys"]["ID"]) && $ID_location
                ? 3
                : 0
            ) + ($tbl_src
                ? 0
                : -3
            ));
        $source_grid++;

        $oRecord->addContent($oField, $group_general);
    }
    if($enable_subitem) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "subitems";
        $oField->container_class = "subitems";
        $oField->data_source = "params";
        $oField->label = ffTemplate::_get_word_by_code("block_modify_params");
        // $oField->display_label = false;
        //$oField->widget = "activecomboex";
        $oField->widget = "actex";

        $oField->source_SQL = "
	                    SELECT nameID, name, type FROM
	                    (
	                        SELECT nameID, name, type FROM
	                        (
								(
	                            SELECT 
	                                IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS nameID
	                                , IF(CONCAT(vgallery_nodes.parent, vgallery_nodes.name) = CONCAT('/', vgallery.name)
                                		, " . $db->toSql(ffTemplate::_get_word_by_code("all")) . "
                                		, IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1))
	                                ) AS name
	                                , vgallery.name AS `type`
	                            FROM
	                                vgallery_nodes
	                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                            WHERE vgallery_nodes.name <> ''
                            		AND vgallery_nodes.is_dir > 0
                            		AND vgallery.public = 0
	                            )
	                            UNION                            
	                            (
	                            SELECT 
	                                CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS nameID 
	                                , CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS name
	                                , layout.value AS `type`
	                                FROM static_pages
	                                    INNER JOIN layout ON layout.ID_type = " . $block_type["gallery"]["ID"] . "
	                                    INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.path LIKE CONCAT('%', IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name)
	                                    WHERE 1
	                                            AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
	                            )
	                            UNION
	                            (
	                            SELECT DISTINCT 
	                                vgallery_nodes.parent AS nameID
	                                , vgallery_nodes.parent AS name
	                                , vgallery_fields.ID AS `type`
	                            FROM
	                                vgallery_nodes
	                                INNER JOIN vgallery_fields ON vgallery_fields.ID_type = vgallery_nodes.ID_type
	                            )
	                            UNION
	                            (
	                            SELECT DISTINCT 
	                                CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params)) AS nameID 
	                                , CONCAT('/', layout.value, IF(layout.params = '/', '', layout.params)) AS name 
	                                , vgallery_groups.ID_menu AS `type`
	                            FROM
	                                vgallery_nodes
	                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery  
	                                INNER JOIN layout ON layout.value = vgallery.name AND layout.ID_type = " . $block_type["virtual-gallery"]["ID"] . " 
	                                INNER JOIN vgallery_fields ON vgallery_fields.ID_type = vgallery_nodes.ID_type
	                                INNER JOIN vgallery_groups_fields ON vgallery_groups_fields.ID_fields = vgallery_fields.ID
	                                INNER JOIN vgallery_groups ON vgallery_groups.ID = vgallery_groups_fields.ID_group
	                            )
	                            UNION
	                            (
	                            SELECT 
	                                '0' AS nameID
	                                , '" . ffTemplate::_get_word_by_code("all") . "' AS name
	                                , 'anagraph' AS `type`
	                            )
	                            UNION
	                            (
	                            SELECT DISTINCT 
	                                anagraph_categories.ID AS nameID
	                                , anagraph_categories.name AS name
	                                , 'anagraph' AS `type`
	                            FROM
	                                anagraph_categories
	                            )
	                            UNION
	                            (
	                            SELECT 
	                                module_params AS nameID 
	                                , module_params AS name
	                                , module_name AS `type`
	                            FROM
	                                modules
	                            )
	                            " . ($block_type["sql"]["subitems"]
                ? " UNION " . $block_type["sql"]["subitems"]
                : ""
            ) . "
	                        ) AS tbl_src 
	                        ORDER BY `type`, name
	                    ) AS macro_tbl
	                    " . (strlen($item)
                ? " WHERE type = " . $db->toSql($item)
                : " [WHERE] "
            ) . "
	                    ORDER BY macro_tbl.type, macro_tbl.nameID";
        if(!strlen($item))
            $oField->actex_father = "items";
        $oField->actex_related_field = "type";
        $oField->actex_update_from_db = true;
        $oField->actex_dialog_url = "javascript:void(0);";
        $oField->actex_dialog_edit_params = array("keys[ID]" => null);
        $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigSelectionModify_confirmdelete";
//da gestire con le condizioni in base alla tipologia
        //$oField->actex_hide_empty = "all";
        if(strlen($item))
            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
        else
            $oField->multi_select_one = false;

        $oField->resources = $actex_sub_resources;
        $oField->actex_on_update_bt = "function(obj) {"
            . ($tbl_src
                ? "ff.cms.admin.getTypeUrl('LT' + '" . $tbl_src . "', '" . $oRecord->id . "_items', obj); "
                :  ""
            )
            . ($item
                ? " ff.cms.admin.getRemoteData('LT' + '" . $tbl_src . "', '/" . $real_path_info  . "', '" . $oRecord->id . "_subitems', obj); "
                : ""
            )
            . " ff.cms.admin.getBlock('LT' + " . ($tbl_src
                ? "'" . $tbl_src . "'"
                : "jQuery('#" . $oRecord->id . "_tblsrc option:selected').val()"
            ) . ", '" . $oRecord->id . "_subitems', " . ($item ? "'" . $item . "'" : " obj.getFatherValue()" ) . ", obj); 
											ff.cms.admin.setNameByBlock("
            . ($_REQUEST["keys"]["ID"]
                ? "false"
                : "false"
            )
            . ", " . ($tbl_src
                ? "'" . $currentType["description"] . "'"
                : "undefined"
            )
            . ", " . ($item && 0
                ? "'" . $item . "'"
                : "undefined"
            )
            . ", " . ($subitem && 0
                ? "'" . $subitem . "'"
                : "undefined"
            )
            . ", " . (strlen($tbl_src_value_js)
                ? "'" . $tbl_src_value_js . "'"
                : "'" . $currentType["description"] . "'"
            )
            . ", {
	                                        			'item' : '" . ffTemplate::_get_word_by_code("all") . "'
	                                        			, 'subitem' : '" . ffTemplate::_get_word_by_code("all") . "'
	                                        		} "
            . ");"
            . "}";

        /* $oField->actex_plugin = array("name" => "selectBoxIt"
                                                                 , "path" => "selectboxit"
                                                                 , "css" => "selectboxit"
                                                                 , "js" => "selectboxit"
                                                                 , "params" => array(
                                                                         "theme" => "jqueryui"
                                                                         , "showFirstOption" => false
                                                                 )
                                                         );*/
        /*
$oField->actex_on_change = 'function(obj, old_value, action) { if(action == 'change') { ff.cms.admin.setNameByBlock(' . ($_REQUEST["keys"]["ID"] ? "true" : "false") . ', ' . ($tbl_src
                    ? "'" . $tbl_src_name . "'"
                    : "") . ($item ? ", '" . $item . "'" : "") . '); } }';*/
        $oField->setWidthComponent($source_grid + ($currentType["group"] == "addon" || $currentType["group"] == "applet"
                ? 5
                : 0
            ));
        $source_grid++;
        $oRecord->addContent($oField, $group_general);
    }

    if(!isset($_REQUEST["keys"]["ID"])) {
        if($ID_location) {
            $oRecord->insert_additional_fields["ID_location"] = new ffData($ID_location, "Number");
        } else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_location";
            $oField->container_class = "location";
            $oField->label = ffTemplate::_get_word_by_code("block_modify_ID_location");
            $oField->extended_type = "Selection";
            $oField->base_type = "Number";
            $oField->source_SQL = "SELECT ID, name 
		                                    FROM layout_location
		                                    WHERE 1
		                                    ORDER BY layout_location.interface_level, ID";

            $oField->required = true;
            if($ID_location > 0) {
                $oField->default_value = new ffData($ID_location, "Number");
                if(!isset($_REQUEST["keys"]["ID"])) {
                    $oField->required = false;
                    $oField->control_type = "label";
                }
            }

            $oField->properties["onchange"] = "javascript:ff.cms.admin.location('" . $location . "');";
            $oField->setWidthLabel(array(2,3,12));
            $oField->setWidthComponent(3);
            $oRecord->addContent($oField, $group_general);
        }
    } else {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_location";
        $oField->container_class = "location";
        $oField->label = ffTemplate::_get_word_by_code("block_modify_ID_location");
        $oField->extended_type = "Selection";
        $oField->base_type = "Number";
        $oField->source_SQL = "SELECT ID, name 
		                                FROM layout_location
		                                WHERE 1
		                                ORDER BY layout_location.interface_level, ID";

        $oField->required = true;
        $oField->setWidthLabel(array(2,3,12));
        $oField->setWidthComponent(3);
        $oRecord->addContent($oField, $group_general);

    }

    $group_rules = $group_general;

    //if($_REQUEST["keys"]["IDs"]) {
    //$oDetail_path = ffDetails::factory($cm->oPage);
    $oDetail_path = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
    $oDetail_path->id = "LayoutModifyPath";
    $oDetail_path->title = ffTemplate::_get_word_by_code("block_modify_path_title");
    $oDetail_path->src_table = "layout_path";
    $oDetail_path->order_default = "ID";
    $oDetail_path->fields_relationship = array ("ID_layout" => "ID");
    //$oDetail_path->display_new_location = "Footer";
    $oDetail_path->display_grid_location = "Footer";
    $oDetail_path->min_rows = 1;
    $oDetail_path->display_rowstoadd = false;
    $oDetail_path->addEvent("on_done_action", "LayoutModifyPath_on_done_action");
    $oDetail_path->insert_additional_fields["last_update"] = new ffData(time(), "Number");
    $oDetail_path->update_additional_fields["last_update"] = new ffData(time(), "Number");
    // $oDetail_path->setWidthComponent("6");

    if(strlen($layout_path)) {
        if($layout_path == "/") {
            $layout_ereg_path = $layout_path;
            $cascading = "0";
        } else {
            $layout_ereg_path = $layout_path . "*";
            $cascading = "1";
        }


        $sSQL_update_path = "UNION
		                    (
		                        SELECT 
		                            null AS ID
		                            , " . $db->toSql($layout_path) . " AS real_path
		                            , " . $db->toSql($layout_ereg_path) . " AS path
		                            , '1' AS visible
		                            , " . $db->toSql($cascading, "Number") . " AS cascading
		                            , '' AS class
		                            , '12' AS default_grid
		                            , '12' AS grid_md
		                            , '12' AS grid_sm
		                            , '12' AS grid_xs
		                            , '0' AS fluid
		                            , '' AS wrap
		                    )";

        $oDetail_path->auto_populate_insert = true;
        $oDetail_path->populate_insert_SQL = "SELECT 
                                                " . $db->tosql($layout_ereg_path, "Text") .  " AS path
                                                , 1 AS visible
                                                , " . $db->toSql($cascading, "Number") . " AS cascading";
        $oDetail_path->auto_populate_edit = true;
        $oDetail_path->populate_edit_SQL = "
                                            SELECT *
                                            FROM
                                            (
                                                (
                                                    SELECT 
                                                        layout_path.ID AS ID
                                                        , layout_path.path AS real_path
                                                        , IF(layout_path.ereg_path = '', REPLACE(REPLACE(REPLACE(layout_path.path, '%', '*'), '(.*)', '*'), '(.+)', '*'), REPLACE(REPLACE(REPLACE(layout_path.ereg_path, '%', '*'), '(.*)', '*'), '(.+)', '*')) AS path
                                                        , layout_path.visible AS visible
                                                        , layout_path.cascading AS cascading
                                                        , layout_path.class AS class
                                                        , layout_path.default_grid AS default_grid
                                                        , layout_path.grid_md AS grid_md
                                                        , layout_path.grid_sm AS grid_sm
                                                        , layout_path.grid_xs AS grid_xs
                                                        , layout_path.fluid AS fluid
                                                        , layout_path.wrap AS wrap
                                                    FROM layout_path
                                                    WHERE layout_path.ID_layout = [ID_FATHER]
                                                    ORDER BY layout_path.path DESC
                                                        , layout_path.ereg_path DESC
                                                        , layout_path.ID
                                                )
                                                $sSQL_update_path
                                            ) AS tbl_src
                                            GROUP BY path
                                            ORDER BY LENGTH(real_path)
                                        ";
        /*
                                                    UNION
                                                    (
                                                        SELECT
                                                            '' AS ID
                                                            , " . $db->toSql($layout_path, "Text") .  " AS path
                                                            , 1 AS visible
                                                            , 0 AS cascading
                                                    )
        */
    } else {
        $oDetail_path->auto_populate_insert = false;
        $oDetail_path->populate_insert_SQL = "";
        $oDetail_path->auto_populate_edit = false;
        $oDetail_path->populate_edit_SQL = "";
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oDetail_path->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "path";
    $oField->class = "layout-path";
    $oField->container_class = "layout-path-rule " . cm_getClassByFrameworkCss(array(4), "col");
    $oField->label = ffTemplate::_get_word_by_code("block_modify_path");
    $oField->extended_type = "Selection";
    $oField->widget = "autocomplete"; //"actex"; e fondamentale rendere il plugin che mantiene i valori anche se nn in combo
    $oField->actex_autocomp = true;
    $oField->autocomplete_minLength = 0;
    $oField->autocomplete_combo = true;
    $oField->autocomplete_compare_having = "name";
    $oField->autocomplete_readonly = false;
    $oField->autocomplete_operation = "LIKE [[VALUE]%]";
    $oField->source_SQL = "SELECT 
		                        CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS ID 
		                        , CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS name
		                    FROM static_pages
		                    WHERE 1
		                        AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
		                    [AND] [WHERE]
		                    [HAVING]
		                    [ORDER] [COLON] name
		                    [LIMIT]";
    $oField->actex_update_from_db = true;
    $oField->store_in_db = false;
    $oField->properties["onkeyup"] = "javascript:ff.cms.admin.path(this)";
    $oField->properties["onblur"] = "javascript:ff.cms.admin.path(this, true)";
    $oField->required = true;
    $oField->multi_select_one = false;
    $oField->default_value = new ffData("*");
    $oDetail_path->addContent($oField);
    /*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ereg_path";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_path_ereg");
    $oDetail_path->addContent($oField);*/

    $oField = ffField::factory($cm->oPage);
    $oField->id = "visible";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_path_visible");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->extended_type = "Boolean";
    $oField->checked_value = new ffData("1", "Number");
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->default_value = $oField->checked_value;
    $oDetail_path->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cascading";
    $oField->class = "layout-cascading";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_path_cascading");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->extended_type = "Boolean";
    $oField->checked_value = new ffData("1", "Number");
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->default_value = $oField->checked_value;
    $oField->properties["onchange"] = "javascript:ff.cms.admin.pathCascading(this);";
    $oDetail_path->addContent($oField);


    if(check_function("set_fields_grid_system"))
        set_fields_grid_system($oDetail_path, array(
            "fluid" => array(
                "hide" => false
            )
        , "width" => false
        ));


    $oRecord->addContent($oDetail_path, $group_rules);
    $cm->oPage->addContent($oDetail_path);





    if($currentType["tpl_path"]) {
        $template_thumb = array();
        $template_detail = array();

        $tpl_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/" . GALLERY_TPL_PATH . $currentType["tpl_path"] . "/*.html");

        if(is_array($tpl_file) && count($tpl_file)) {
            foreach($tpl_file AS $real_file) {
                if(ffGetFilename($real_file) == "empty")
                    continue;

                if(strpos(basename($real_file), "_") !== false) {
                    $arrFile = explode("_", basename($real_file));
                    if($arrFile[0] != "default")
                        $template_thumb[$arrFile[0]] = array(new ffData($arrFile[0]), new ffData(ucfirst($arrFile[0])));
                } elseif($currentType["multi_id"]) {
                    $arrFile = ffGetFilename($real_file);
                    if($arrFile != "default")
                        $template_detail[$arrFile] = array(new ffData($arrFile), new ffData(ucfirst($arrFile)));
                }
            }
        }

        /***********
         *  Group Template thumb
         */
        if(count($template_thumb)) {
            $group_template = "template" . (count($template_detail) ? "_thumb" : "");
            $template_thumb["custom"] = array(new ffData("custom"), new ffData(ffTemplate::_get_word_by_code("layout_custom")));
            $oRecord->addContent(null, true, $group_template);
            $oRecord->groups[$group_template] = array(
                "title" => ffTemplate::_get_word_by_code("layout_" . $group_template)
            );
            $oField = ffField::factory($cm->oPage);
            $oField->id = "template";
            // $oField->label = ffTemplate::_get_word_by_code("block_modify_template");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = $template_thumb;
            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
            $oField->properties["onchange"] = "if(jQuery('option:selected', this).text() == '" . ffTemplate::_get_word_by_code("layout_custom") . "') { 
				if(jQuery('.custom-thumb').hasClass('hidden')) 
					jQuery('.custom-thumb').hide().removeClass('hidden');

				jQuery('.custom-thumb').slideDown(function () {
					jQuery('.custom-thumb TEXTAREA').data('codeMirrorInstance').refresh();				
				}); 
			} else { 
				jQuery('.custom-thumb').slideUp(); 
			}";
            $oRecord->addContent($oField, $group_template);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "html";
            $oField->container_class = "custom-thumb hidden";
            if($block_smart_url) {
                $file_custom = "";
                $template_custom = $block_smart_url . (count($template_detail) ? "_thumb" : "");
                if(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".html")) {
                    $file_custom = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".html";

                    //$js .= 'jQuery("#LayoutModify_template option:last").attr("selected", "selected");';
                    $oField->container_class = "custom-thumb";
                    $oRecord->user_vars["tpl_thumb"] = "custom";
                } elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".bkp")) {
                    $file_custom = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".bkp";
                }
                if($file_custom) {
                    $oField->default_value = new ffData(file_get_contents($file_custom));
                }
            }
            //$oField->label = ffTemplate::_get_word_by_code("block_modify_html");
            $oField->encode_entities = false;
            $oField->editarea_syntax = "html";
            if(check_function("set_field_textarea"))
                $oField = set_field_textarea($oField);

            $oField->data_type = "";
            $oField->store_in_db = false;
            $oRecord->addContent($oField, $group_template);
        }

        /***********
         *  Group Template detail
         */
        if(count($template_detail) && $currentType["multi_id"]) {
            $template_detail = array_diff_assoc($template_detail, $template_thumb);
            $template_detail["custom"] = array(new ffData("custom"), new ffData(ffTemplate::_get_word_by_code("layout_custom")));
            $group_template = "template_detail";
            $oRecord->addContent(null, true, $group_template);
            $oRecord->groups[$group_template] = array(
                "title" => ffTemplate::_get_word_by_code("layout_" . $group_template)
            );
            $oField = ffField::factory($cm->oPage);
            $oField->id = "template_detail";
            // $oField->label = ffTemplate::_get_word_by_code("block_modify_template");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = ($template_detail);
            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
            $oField->properties["onchange"] = "if(jQuery('option:selected', this).text() == '" . ffTemplate::_get_word_by_code("layout_custom") . "') { 
				if(jQuery('.custom-detail').hasClass('hidden'))
					jQuery('.custom-detail').hide().removeClass('hidden');
				
				jQuery('.custom-detail').slideDown(function() {
					jQuery('.custom-detail TEXTAREA').data('codeMirrorInstance').refresh();				
				}); 
			
			} else { 
				jQuery('.custom-detail').slideUp(); 
			}";

            $oRecord->addContent($oField, $group_template);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "html_detail";
            $oField->container_class = "custom-detail hidden";
            if($block_smart_url) {
                $file_custom = "";
                $template_custom = $block_smart_url . "_detail";
                if(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".html")) {
                    $file_custom = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".html";
                    //$js .= 'jQuery("#LayoutModify_template_detail option:last").attr("selected", "selected");';
                    $oField->container_class = "custom-detail";
                    $oRecord->user_vars["tpl_detail"] = "custom";
                } elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".bkp")) {
                    $file_custom = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/contents/" . $template_custom . ".bkp";
                }
                if($file_custom) {
                    $oField->default_value = new ffData(file_get_contents($file_custom));
                }
            }
            //$oField->label = ffTemplate::_get_word_by_code("block_modify_html_detail");
            $oField->encode_entities = false;
            $oField->editarea_syntax = "html";
            if(check_function("set_field_textarea"))
                $oField = set_field_textarea($oField);

            $oField->data_type = "";
            $oField->store_in_db = false;
            $oRecord->addContent($oField, $group_template);
        }
    }


    /***********
     *  Group Js
     */
    $group_js = "js";
    $oRecord->addContent(null, true, $group_js);
    $oRecord->groups[$group_js] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_js)
    );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "js";
    // $oField->label = ffTemplate::_get_word_by_code("block_modify_js");
    $oField->editarea_syntax = "javascript";
    if(check_function("set_field_textarea"))
        $oField = set_field_textarea($oField);
    $oRecord->addContent($oField, $group_js);


    $oField = ffField::factory($cm->oPage);
    $oField->id = "js_lib";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_js_lib");
    $oField->widget = "autocomplete";
    //$oField->actex_autocomp = true;
    //$oField->actex_multi = true;
    $oField->autocomplete_readonly = false;
    $oField->autocomplete_multi = true;
    $oField->autocomplete_combo = true;
    $oField->autocomplete_minLength = 0;
    $oField->actex_update_from_db = true;
    if(check_function("system_get_js_plugins"))
        $oField->source_SQL = system_get_js_libs(null, "sql_distinct");

    $oRecord->addContent($oField, $group_js);

    /***********
     *  Group Css
     */
    $group_css = "css";
    $oRecord->addContent(null, true, $group_css);
    $oRecord->groups[$group_css] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_css)
    );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "css";
    //$oField->label = ffTemplate::_get_word_by_code("block_modify_css");
    $oField->editarea_syntax = "css";
    if(check_function("set_field_textarea"))
        $oField = set_field_textarea($oField);
    $oRecord->addContent($oField, $group_css);

    /***********
     *  Group Settings
     */

    //$oRecord->addTab("settings");
    //$oRecord->setTabTitle("settings", ffTemplate::_get_word_by_code("layout_settings"));
    $group_settings = "settings";
    $oRecord->addContent(null, true, $group_settings);

    $oRecord->groups[$group_settings] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_settings)
    );


    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->container_class = "name";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_name");
    $oField->required = true;
    // $oField->control_type = "label";
    $oField->setWidthComponent("6");
    $oField->default_value = new ffData($tbl_src_value_fixed);
    $oRecord->addContent($oField, $group_settings);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "smart_url";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_key");
    $oField->required = true;
    if(!$_REQUEST["keys"]["ID"]) {
        $oField->widget = "slug";
        $oField->slug_title_field = "name";
    }
    $oField->setWidthComponent("6");
    $oField->default_value = new ffData(ffCommon_url_rewrite($tbl_src_value_fixed));
    $oRecord->addContent($oField, $group_settings);

    $oDetail_settings = ffDetails::factory($cm->oPage);
    $oDetail_settings->id = "LayoutSettingsDetail";
    $oDetail_settings->tab = "left";
    $oDetail_settings->tab_label = "settings_group";

    $oDetail_settings->src_table = "layout_settings_rel";
    $oDetail_settings->order_default = "ID";
    $oDetail_settings->addEvent("on_before_process_row", "LayoutSettingsDetail_on_before_process_row");
    $oDetail_settings->addEvent("on_before_parse_row", "LayoutSettingsDetail_on_before_parse_row");
    $oDetail_settings->display_delete = false;
    $oDetail_settings->display_new = false;
    $oDetail_settings->fields_relationship = array ("ID_layout" => "ID");
    // $oDetail_settings->user_vars["tbl_src"] = $tbl_src;
    //$oDetail_settings->user_vars["tbl_src_name"] = $tbl_src_name;
    $oDetail_settings->user_vars["tpl_path"] = $currentType["tpl_path"];
    if(check_function("get_layout_settings") && $tbl_src)
        $oDetail_settings->user_vars["layout_settings"] = get_layout_settings(NULL, $tbl_src);

    $oDetail_settings->auto_populate_insert = true;
    $oDetail_settings->populate_insert_SQL = "SELECT 
                                                null AS ID
                                                , null AS ID_layout
                                                , layout_settings.ID AS ID_layout_settings
                                                , layout_settings.name AS layout_settings
                                                , IF(layout_settings.description = '', layout_settings.name, layout_settings.description) AS layout_settings_description
                                                , (
                                                        SELECT value
                                                        FROM layout_settings_rel
                                                        WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                        AND layout_settings_rel.ID_layout = 0
                                                    )  AS value
                                                , extended_type.name AS extended_type
                                                , SUBSTRING( layout_settings.`group` FROM LOCATE('-', layout_settings.`group` ) + 1 ) AS  settings_group
                                             FROM 
                                                layout_settings
                                                INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = " . $db->toSql($currentType["ID"], "Number") . "
                                                INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                             ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name";
    $oDetail_settings->auto_populate_edit = true;
    $oDetail_settings->populate_edit_SQL = "
                                        SELECT 
                                                (
                                                    SELECT ID
                                                    FROM layout_settings_rel
                                                    WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                    AND layout_settings_rel.ID_layout = [ID_FATHER]
                                                ) 
                                                AS ID
                                                , [ID_FATHER] AS ID_layout
                                                , layout_settings.ID AS ID_layout_settings
                                                , layout_settings.name AS layout_settings
                                                , IF(layout_settings.description = '', layout_settings.name, layout_settings.description) AS layout_settings_description
                                                , 
                                                    (
                                                        SELECT value
                                                        FROM layout_settings_rel
                                                        WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                        AND layout_settings_rel.ID_layout = [ID_FATHER]
                                                    ) 
                                                AS value
                                                , extended_type.name AS extended_type
												, SUBSTRING( layout_settings.`group` FROM LOCATE('-', layout_settings.`group` ) + 1 ) AS  settings_group
                                             FROM 
                                                layout_settings
                                                INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = " . $db->toSql($currentType["ID"], "Number") . "
                                                INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                             ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name";

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oDetail_settings->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_layout_settings";
    $oDetail_settings->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "value";
    $oField->label = "";
    $oDetail_settings->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "layout_settings";
    $oField->store_in_db = false;
    $oDetail_settings->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "layout_settings_description";
    $oField->store_in_db = false;
    $oDetail_settings->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "extended_type";
    $oField->store_in_db = false;
    $oDetail_settings->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "settings_group";
    $oField->store_in_db = false;
    $oDetail_settings->addHiddenField($oField);

    $oRecord->addContent($oDetail_settings, $group_settings);
    $cm->oPage->addContent($oDetail_settings);
    $cm->oPage->addContent($oRecord);

    $js = '
		jQuery(function() {
			ff.pluginAddInit("ff.cms.admin", function() {
				' . $str_layout_type . '

				ff.cms.admin.id = "' . (isset($_REQUEST["XHR_CTX_ID"]) ? "LayoutModify_data" : "LayoutModify") . '";
				ff.cms.admin.locationName = "' . $location . '";
				ff.cms.admin.location();
				ff.cms.admin.UseAjax();

				jQuery("#" + ff.cms.admin.id + " .name input[type=hidden]").parent().hide();
			});
		});';
    //$cm->oPage->addContent('<script>' . $js . '</script>');

    /***********
     *  Group Ajax
     */
    $group_ajax = "ajax";
    $oRecord->addContent(null, true, $group_ajax);
    $oRecord->groups[$group_ajax] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_ajax)
    , "tab" => $group_settings
    );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "use_in_content";
    $oField->container_class = "use-in-content";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_use_in_content");
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("block_content_default"))),
        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("block_content_yes"))),
        array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("block_content_no")))
    );
    $oField->multi_select_one = false;
    // $oField->setWidthComponent(6);
    $oField->setWidthLabel(6);
    $oField->setWidthControl(3);
    $oRecord->addContent($oField, $group_ajax);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "use_ajax";
    $oField->container_class = cm_getClassByFrameworkCss("align-right", "util", "use-ajax");
    $oField->label = ffTemplate::_get_word_by_code("block_modify_use_ajax");
    $oField->control_type = "checkbox";
    $oField->extended_type = "Boolean";
    $oField->checked_value = new ffData("1");
    $oField->unchecked_value = new ffData("0");
    $oField->properties["onchange"] = "javascript:ff.cms.admin.UseAjax();";
    $oField->setWidthComponent(4);
    $oRecord->addContent($oField, $group_ajax);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ajax_on_ready";
    $oField->container_class = "use-ajax-dep on-ready";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_ajax_on_ready");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
        array(new ffData("preload"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_preload"))),
        array(new ffData("inview"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_inview"))),
        array(new ffData("standby"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_standby"))),
        array(new ffData("load show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_load_show"))),
        array(new ffData("load fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_load_fade"))),
        array(new ffData("load hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_ready_load_hide")))
    );
    $oField->default_value = new ffData("inview");
    $oField->multi_select_one = false;
    $oField->   setWidthLabel(6);
    $oField->setWidthComponent(4);
    $oRecord->addContent($oField, $group_ajax);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ajax_on_event";
    $oField->container_class = "use-ajax-dep on-event";
    $oField->label = ffTemplate::_get_word_by_code("block_modify_ajax_on_event");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
        array(new ffData("load fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fade"))),
        array(new ffData("load show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_show"))),
        array(new ffData("load fadeToggle"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fadeToggle"))),
        array(new ffData("load slideToggle"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_slideToggle"))),
        array(new ffData("load hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_hide"))),
        array(new ffData("reload show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_show"))),
        array(new ffData("reload fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_fade"))),
        array(new ffData("reload hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_hide")))
    );
    $oField->default_value = new ffData("load fadeIn");
    $oField->multi_select_one = false;
    $oField->setWidthLabel(6);
    $oField->setWidthComponent(4);

    $oRecord->addContent($oField, $group_ajax);

    $cm->oPage->tplAddJs("ff.cms.admin.block-modify", array(
        "embed" => $js
    ));


}
// -------------------------
//          EVENTI
// -------------------------
function LayoutModifyPath_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    $globals = ffGlobals::getInstance("gallery");

    switch($action) {
        case "insert":
        case "update":
            if(is_array($component->recordset) && count($component->recordset)) {
                $arrLayoutPath = array();
                $arrLayoutPathDelete = array();

                foreach($component->recordset AS $key => $value) {
                    $tmp_path = $value["path"]->getValue();
                    do {
                        $tmp_path = str_replace("//", "/*/", $tmp_path);
                    } while(str_replace("//", "/*/", $tmp_path) != $tmp_path);

                    $cascading = $value["cascading"]->getValue();
                    $ID_layout_path = $value["ID"]->getValue();
                    $real_path = "";
                    $real_ereg_path = "";
                    $is_cascading = false;

                    if(preg_replace('/[^a-zA-Z0-9\-\/]/', '', $tmp_path) != $tmp_path) {
                        $arrPath = explode("/", $tmp_path);
                        if(is_array($arrPath) && count($arrPath)) {
                            foreach($arrPath AS $arrPath_key => $arrPath_value) {
                                if(preg_replace('/[^a-zA-Z0-9\-\/]/', '', $arrPath_value) != $arrPath_value)
                                    break;

                                if(strlen($arrPath_value)) {
                                    if(strlen($real_path))
                                        $real_path .= "/";

                                    $real_path .= $arrPath_value;
                                }
                            }
                        }

                        $real_path = "/" . $real_path;

                        $real_ereg_path = $tmp_path;
                        if(substr($real_ereg_path, -1) == "*") {
                            $real_ereg_path = substr($real_ereg_path, 0,-1);
                            $is_cascading = true;
                        }

                        //$real_ereg_path = str_replace("*", "(.+)", $real_ereg_path);
                        $real_ereg_path = str_replace("*", "%", $real_ereg_path); //experimental

                        if($is_cascading && $cascading) {
                            //$real_ereg_path .= "(.*)";
                            if(substr($real_ereg_path, -1) != "/")
                                $real_path = stripslash($real_path) . "/" . basename($real_ereg_path);

                            $real_ereg_path .= "%"; //experimental
                        }
                        //$real_ereg_path = str_replace("(.+)(.*)", "(.+)/(.*)", $real_ereg_path);
                    } else {
                        $real_path = $tmp_path;
                        $real_ereg_path = $tmp_path;
                    }
                    if(array_key_exists($real_path . $real_ereg_path, $arrLayoutPath)) {
                        $arrLayoutPathDelete[] = $ID_layout_path;
                    } else {
                        $arrLayoutPath[$real_path . $real_ereg_path] = array("ID" => $ID_layout_path
                        , "path" => $real_path
                        , "ereg_path" => $real_ereg_path
                        );
                    }
                }

                if(is_array($arrLayoutPath) && count($arrLayoutPath)) {
                    ksort($arrLayoutPath);

                    foreach($arrLayoutPath AS $arrLayoutPath_key => $arrLayoutPath_value) {
                        if(strlen($arrLayoutPath_value["path"]) && strlen(basename($arrLayoutPath_value["path"]))) {
                            $sSQL = "SELECT static_pages.*
									FROM static_pages
									WHERE static_pages.parent = " . $db->toSql(ffCommon_dirname($arrLayoutPath_value["path"])) . "
										AND static_pages.name = " . $db->toSql(basename($arrLayoutPath_value["path"]));
                            $db->query($sSQL);
                            if(!$db->nextRecord()) {
                                $sSQL = "INSERT INTO static_pages
										(
											ID
											, name
											, parent
											, last_update
											, ID_domain
										)
										VALUES
										(
											null
											, " . $db->toSql(basename($arrLayoutPath_value["path"])) . "
											, " . $db->toSql(ffCommon_dirname($arrLayoutPath_value["path"])) . "
											, " . $db->toSql(time(), "Number") . "
											, " . $db->toSql($globals->ID_domain, "Number") . "
										)";
                                $db->execute($sSQL);
                                $ID_static_page = $db->getInsertID(true);
                                $static_smart_url = basename($arrLayoutPath_value["path"]);
                                $static_name  = ucfirst(str_replace("-", " " , $static_smart_url));

                                $sSQL = "INSERT INTO static_pages_rel_languages
										(
											ID
											, ID_static_pages
											, ID_languages
											, visible
											, smart_url
											, meta_title
											, permalink_parent
											, permalink
										)
										VALUES
										(
											null
											, " . $db->toSql($ID_static_page, "Number") . "
											, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
											, " . $db->toSql("1", "Number") . "
											, " . $db->toSql($static_smart_url) . "
											, " . $db->toSql($static_name) . "
											, " . $db->toSql(ffCommon_dirname($arrLayoutPath_value["path"])) . "
											, " . $db->toSql($arrLayoutPath_value["path"]) . "
										)";
                                $db->execute($sSQL);

                            }
                        }
                        $sSQL = "UPDATE layout_path SET 
									path = " . $db->toSql($arrLayoutPath_value["path"]) . "
									, ereg_path = " . $db->toSql($arrLayoutPath_value["ereg_path"]) . "
								WHERE layout_path.ID = " . $db->toSql($arrLayoutPath_value["ID"], "Number");
                        $db->execute($sSQL);

                    }
                }
                if(is_array($arrLayoutPathDelete) && count($arrLayoutPathDelete)) {
                    $sSQL = "DELETE FROM layout_path WHERE ID IN(" . $db->toSql(implode(",", $arrLayoutPathDelete), "Text", false) . ")";
                    $db->execute($sSQL);
                }
            }
            break;
        default:
    }

}

function LayoutSettingsDetail_on_before_process_row($component, $record) {
    $db = ffDB_Sql::factory();

    if(defined("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue())) && !constant("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue()))) {
        return true;
    }

    $obj_page_field = $component->form_fields["value"];
    // $component->user_vars["group"][(strlen($record["settings_group"]->getValue()) ? "general" : $record["settings_group"]->getValue())] = true;
    $field_params = array(
        "extended_type" => $record["extended_type"]->getValue()
    , "label" => ffTemplate::_get_word_by_code($record["layout_settings_description"]->getValue())
    , "placeholder" => true
    , "container_class" => $record["settings_group"]->getValue()
    );

    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "PLUGIN") !== false)
    {
        $field_params["extended_type"] = "AutocompleteWritable";
        if(check_function("system_get_js_plugins"))
            $field_params["source_SQL"] = system_get_js_libs(null, "sql_distinct");
    }

    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "HTMLTAG") !== false)
    {
        $field_params["extended_type"] = "AutocompleteWritable";
        $field_params["source_SQL"] = "SELECT vgallery_fields_htmltag.tag
    									, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')') AS tagattr
    									FROM vgallery_fields_htmltag
    									WHERE 1
    									ORDER BY tagattr";

    }

    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "TEMPLATE") !== false)
    {
        //$st_class = $component->main_record[0]->user_vars["st_class"];
        //$tpl_path = $st_class[strtolower($record["settings_group"]->getValue())]["tpl_path"];
        $tpl_path = $component->user_vars["tpl_path"];
        if(!$tpl_path)
            return true;

        $sSQL_template_file = array();
        $tpl_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/" . GALLERY_TPL_PATH . $tpl_path . "/*");
        if(is_array($tpl_file) && count($tpl_file)) {
            foreach($tpl_file AS $real_file) {
                if(is_file($real_file)) {
                    $file_name = ffGetFilename($real_file);
                    if(strpos($file_name, "_") !== false)
                        continue;

                    $sSQL_template_file[] = " (SELECT 
		                                " . $db->toSql($file_name) . " AS nameID
		                                , " . $db->toSql(ucfirst($file_name)) . " AS name
		                            )";
                }
            }
        }

        if(is_array($sSQL_template_file) && count($sSQL_template_file)) {
            $field_params["extended_type"] = "Selection";
            //$field_params["enable_actex"] = true;
            $field_params["disable_select_one"] = true;
            $field_params["source_SQL"] = implode(" UNION ", $sSQL_template_file);
        } else {
            return true;
        }
    }

    if(check_function("get_field_by_extension"))
        get_field_by_extension($obj_page_field, $field_params);

    if($component->db[0]->record["value"] === NULL) {
        $record["value"]->setValue($component->user_vars["layout_settings"][$record["layout_settings"]->getValue()], $obj_page_field->base_type);
    } else {
        $record["value"]->setValue($record["value"]->getValue(), $obj_page_field->base_type);
    }




}

function LayoutSettingsDetail_on_before_parse_row($component, $record, $record_key) {
    $component->tpl[0]->set_var("SectFormTitle", "");
}

function LayoutModify_on_loaded_data($component) {
    if($component->first_access) {
        if(isset($component->form_fields["template"]) && $component->user_vars["tpl_thumb"] == "custom") {
            $component->form_fields["template"]->setValue("custom");
        }

        if(isset($component->form_fields["template_detail"]) && $component->user_vars["tpl_detail"] == "custom") {
            $component->form_fields["template_detail"]->setValue("custom");
        }
    }
}


function LayoutModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
    if($action == "insert" || $action == "update") {
        if(isset($component->form_fields["template_detail"])) {
            $component->user_vars["tpl_detail"] = $component->form_fields["template_detail"]->getValue();
            if($component->form_fields["template_detail"]->getValue() == "custom")
                $component->form_fields["template_detail"]->setValue("");
        }
        if(isset($component->form_fields["template"])) {
            $component->user_vars["tpl_thumb"] = $component->form_fields["template"]->getValue();
            if($component->form_fields["template"]->getValue() == "custom")
                $component->form_fields["template"]->setValue("");
        }

        $sSQL = "SELECT layout_type.* 
				FROM layout_type 
				WHERE layout_type.ID = " . $db->toSql(
                (isset($component->form_fields["tblsrc"])
                    ? $component->form_fields["tblsrc"]->value
                    : $component->user_vars["currentType"]["ID"]
                ));
        $db->query($sSQL);
        if($db->nextRecord()) {
            $key_required = $db->getField("key_required", "Number", true);
            $sub_key_required = $db->getField("sub_key_required", "Number", true);
            $layout_type_class = $db->getField("class", "Text", true);
        }

        if($key_required && isset($component->form_fields["items"]) && !strlen($component->form_fields["items"]->getValue())) {
            $component->tplDisplayError(ffTemplate::_get_word_by_code("layout_type_items_required"));
            return true;
        }
        if($sub_key_required && isset($component->form_fields["subitems"]) && !strlen($component->form_fields["subitems"]->getValue())) {
            $component->tplDisplayError(ffTemplate::_get_word_by_code("layout_type_subitems_required"));
            return true;
        }
        if(isset($component->form_fields["tblsrc"]))
        {
            if(strpos($component->form_fields["tblsrc"]->getValue(), ":") !== false) {
                $arrTblSrc = explode(":", $component->form_fields["tblsrc"]->getValue());
                if(array_key_exists(strtoupper($arrTblSrc[0]), $component->user_vars["st"])) {
                    $component->form_fields["subitems"]->setValue($component->form_fields["items"]->getValue());
                    $component->form_fields["items"]->setValue($arrTblSrc[1]);
                    $component->form_fields["tblsrc"]->setValue($component->user_vars["st"][strtoupper($arrTblSrc[0])]);
                }
            }
        }
        if(isset($component->form_fields["smart_url"]))
        {
            $block_smart_url = ffCommon_url_rewrite($component->form_fields["smart_url"]->getValue());
            if(!$block_smart_url)
                $block_smart_url = "L" . $component->key_fields["ID"]->getValue();

            $component->form_fields["smart_url"]->setValue($block_smart_url);
        }
        //ffErrorHandler::raise("ASD", E_USER_ERROR, null,get_defined_vars());

    }
}
function LayoutModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    check_function("write_custom_template");

    if(strlen($action)) {
        $layout_smart_url = $component->form_fields["smart_url"]->getValue();

        if(isset($component->form_fields["html_detail"])) {
            $file = $layout_smart_url . "_detail";
            $content = $component->form_fields["html_detail"]->getValue();
            if($component->user_vars["tpl_detail"] == "custom") {
                $action = "update";
            } else {
                $action = "delete";
            }

            $strError = write_custom_template($file, $content, $action);
        }

        if(isset($component->form_fields["html"])) {
            $file = $layout_smart_url . (isset($component->form_fields["html_detail"]) ? "_thumb" : "" );
            $content = $component->form_fields["html"]->getValue();
            if($component->user_vars["tpl_thumb"] == "custom") {
                $action = "update";
            } else {
                $action = "delete";
            }

            $strError = write_custom_template($file, $content, $action);
        }

        if($strError) {
            $component->tplDisplayError($strError);
            return false;
        }

        $sSQL = "SELECT layout.*
    			FROM layout
    			WHERE layout.smart_url = " . $db->toSql($layout_smart_url) . "
    			 	AND layout.ID <> " . $db->toSql($component->key_fields["ID"]->value);
        $db->query($sSQL);
        if($db->nextRecord()) {
            $layout_smart_url .= "-" . $component->key_fields["ID"]->getValue();

            $sSQL = "UPDATE 
	                    `layout` 
	                SET 
	                    smart_url = " . $db->toSql($layout_smart_url) . "
	                WHERE layout.ID = " . $db->toSql($component->key_fields["ID"]->value);
            $db->execute($sSQL);
        }

        //UPDATE CACHE
        if(check_function("refresh_cache")) {
            refresh_cache_block($component->key_fields["ID"]->getValue());
        }
    }
}


function add_block($action = "")
{
    $cm = cm::getInstance();
    $type_group = ($action
        ? "group-" . $action
        : "group"
    );

    if(check_function("system_get_sections"))
        $block_type = system_get_block_type();

    if(is_array($block_type[$type_group]) && count($block_type[$type_group]))
    {
        //$block_type[$block_type["smart_url"]["applet"]]["child"] = system_get_block_applets($block_type, "array");

        $tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents/admin/block");
        $tpl->load_file("add.html","main");
        $tpl->set_var("ico_help", cm_getClassByFrameworkCss("question-circle", "icon") . " " . cm_getClassByFrameworkCss("right", "util"));
        $tpl->set_var("ico_add", cm_getClassByFrameworkCss("primary", "button", array("size" => "small")) . " " . cm_getClassByFrameworkCss("plus", "icon") . " " . cm_getClassByFrameworkCss("right", "util"));

        foreach($block_type[$type_group] AS $group_key => $block_by_group)
        {
            $group_title = ($block_type[$group_key]["description"]
                ? $block_type[$group_key]["description"]
                : $group_key
            );
            foreach($block_by_group AS $block_smart_url => $block_key)
            {
                if($block_type[$block_key]["child"][$block_smart_url]) {
                    $block_title =  (isset($block_type[$block_key]["child"][$block_smart_url]["name"])
                        ? $block_type[$block_key]["child"][$block_smart_url]["name"]
                        : $block_smart_url
                    );
                    $block_icon = (isset($block_type[$block_key]["child"][$block_smart_url]["icon"])
                        ? $block_type[$block_key]["child"][$block_smart_url]["icon"]
                        : $block_type[$block_key]["icon"]
                    );
                    $block_description = (isset($block_type[$block_key]["child"][$block_smart_url]["description"])
                        ? $block_type[$block_key]["child"][$block_smart_url]["description"]
                        : ffTemplate::_get_word_by_code(str_replace("-", "_", $block_smart_url) . "_description")
                    );
                } else {
                    $block_title = $block_type[$block_key]["description"];
                    $block_icon = $block_type[$block_key]["icon"];
                    $block_description = ffTemplate::_get_word_by_code(str_replace("-", "_", $block_smart_url) . "_description");
                }

                $block_col = cm_getClassByFrameworkCss(array(
                    "xs" => 12
                , "sm" => 12
                , "md" => $block_type[$block_key]["coloumn_template"]
                , "lg" => $block_type[$block_key]["coloumn_template"]
                ), "col");

                if($action)
                    $block_url = $cm->oPage->site_path . ($block_type[$block_key]["url"]
                            ? $block_type[$block_key]["url"] . "/" . $block_type[$block_key]["file_add"]
                            : str_replace("[father]", $block_smart_url, $block_type[$block_key]["sub_url"]) . "/" . $block_type[$block_key]["sub_file_add"]
                        );
                else
                    $block_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/modify/" . $block_smart_url . "?location=" . $_REQUEST["location"] . "&path=" . $_REQUEST["path"];

                if($cm->isXHR()) {
                    $block_click = "ff.ffPage.dialog.doOpen('" . ($_REQUEST["XHR_CTX_ID"] ? $_REQUEST["XHR_CTX_ID"] : "dialogManage") . "', '" . $block_url . "','" . $block_title . "', true);";
                    $tpl->set_var("block_actions", 'href="javascript:void(0);" onclick="' . $block_click . '"');
                } else {
                    $tpl->set_var("block_actions", 'href="' . $block_url . '"');
                }

                $tpl->set_var("block_title", $block_title);
                $tpl->set_var("block_class", "item vg-" . $block_type[$block_key]["group"] . " " . $block_col);
                $tpl->set_var("block_img", cm_getClassByFrameworkCss("left", "util", "item-img"));
                $tpl->set_var("block_icon", cm_getClassByFrameworkCss($block_icon, "icon-tag", array("6x")));
                // $tpl->set_var("element_icon_large", cm_getClassByFrameworkCss("vg-" . $real_icon, "icon-tag", array("5x", $group_name, $blockProperty["class"])));
                $tpl->set_var("block_description", $block_description);
                $tpl->set_var("ID_dialog", ($_REQUEST["XHR_CTX_ID"] ? $_REQUEST["XHR_CTX_ID"] : "dialogManage"));
                $tpl->parse("SezBlockItem", true);
            }
            $tpl->set_var("group_name",  $group_title);
            $tpl->set_var("group_class", $group_title);

            $tpl->parse("SezBlockGroup", true);
            $tpl->set_var("SezBlockItem", "");
        }

        $tpl_layout = $tpl->rpparse("main", false);
    }

    return $tpl_layout;
}