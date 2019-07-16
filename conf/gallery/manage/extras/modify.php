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

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(Auth::env("AREA_PROPERTIES_SHOW_MODIFY") || Auth::env("AREA_PROPERTIES_DESIGN_SHOW_MODIFY"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
// da gestire discriminanti per i plugin
// INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND (layout_type.name = IF('[tblsrc_FATHER]' = 'vgallery_nodes', 'VIRTUAL_GALLERY', IF('[tblsrc_FATHER]' = 'files', 'GALLERY', 'PUBLISHING')))

$tbl_src = $_REQUEST["extype"];
$skip_detail = $_REQUEST["skipd"];

if (isset($_REQUEST["layout"]) && $_REQUEST["layout"] > 0)
    $ID_layout = $_REQUEST["layout"];

/*
$sSQL = "SELECT cm_layout.* 
			FROM cm_layout 
			WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if ($db_gallery->nextRecord()) {
    $framework_css = Cms::getInstance("frameworkcss")->getFramework($db_gallery->getField("framework_css", "Text", true));
    $framework_css_name = $framework_css["name"];
}*/

$framework_css = Cms::getInstance("frameworkcss")->getFramework();
$template_framework = $framework_css["name"];
    
$hide_source = false;
if (strlen($tbl_src)) {
	$hide_source = true;
	$item_path = $_REQUEST["path"];
	if (check_function("get_file_properties"))
		$file_properties = get_file_properties($item_path, $tbl_src, "thumb", null, null, $ID_layout);

	if (isset($_REQUEST["keys"]["ID"]))
		$ID_item = $_REQUEST["keys"]["ID"];

	$item_tbl = $tbl_src;
	unset($_REQUEST["keys"]["ID"]);

	if ($file_properties["ID"] > 0) {
		if (!$ID_layout || $file_properties["ID_layout"] == $ID_layout || $file_properties["tblsrc"] != "vgallery_nodes") {
			$ID_item = $file_properties["item"];
			if ($file_properties["source"] != "/")
				$item_path = $file_properties["source"];

			$item_tbl = $file_properties["tblsrc"];

			$_REQUEST["keys"]["ID"] = $file_properties["ID"];
		}
	}
} elseif ($_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "SELECT settings_thumb.tbl_src
    			, settings_thumb.items 
            FROM settings_thumb 
            WHERE settings_thumb.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
		$item_tbl = $db_gallery->getField("tbl_src", "Text", true);
		$ID_item = $db_gallery->getField("items", "Text", true);
		
		if(($item_tbl == "files" || $item_tbl == "vgallery_nodes") && is_numeric($ID_item) && $ID_item > 0 ) {
			$sSQL = "SELECT " . $item_tbl . ".parent
			  			, " . $item_tbl . ".name
					  FROM " . $item_tbl . "
					  WHERE
					  " . $item_tbl . ".ID = " . $db_gallery->toSql($ID_item, "Number");
			$db_gallery->query($sSQL);
			if($db_gallery->nextRecord()) {
			  	$item_path = stripslash($db_gallery->getField("parent", "Text", true)) . "/" . $db_gallery->getField("name", "Text", true);
			}
		}
    }
}
$item_source_tbl = $item_tbl;
$item_source_path = $item_path;

$simple_interface = false;
$icon["group"] = "vg-content";
$icon["type"] = "vg-virtual-gallery";

switch ($item_source_tbl) {
    case "search":
    case "tag":
    case "overview":
    	$item_tbl_layout = false;

		$source_search_title = "Search";
		$source_tag_title = "Tag";
        $source_overview_title = "Overview";
		$ID_item = $item_path;
		$skip_detail = true;

		switch ($_REQUEST["src"]) {
		    case "anagraph":
		    $src_tbl_field = "anagraph_fields";
			$sSQL_primary_image = "SELECT anagraph_fields.ID
				                                , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
				                            FROM anagraph_fields
				                                INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
				                            WHERE anagraph_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
				                                AND anagraph_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
				                            [HAVING]
				                            ORDER BY name";
			$sSQL_sort 			= "	(
										SELECT '0' AS ID
											, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
									) UNION (
										SELECT '-1' AS ID
											, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
									) UNION (
										SELECT '-3' AS ID
											, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
									) UNION (
										SELECT '-4' AS ID
											, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
									) UNION (
										SELECT anagraph_fields.ID
					                        , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
					                    FROM anagraph_fields
					                        INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
					                    WHERE 1
					                    ORDER BY name
					                )";
			break;
		    case "files":
				$src_tbl_field = false;
			break;
		    default:
		    	$src_tbl_field = "vgallery_fields";
				$sSQL_primary_image = "SELECT vgallery_fields.ID
					                                , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
					                            FROM vgallery_fields
					                                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
					                            WHERE vgallery_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
					                                AND vgallery_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
					                            [HAVING]
					                            ORDER BY name";
				$sSQL_sort 			= "	(
											SELECT '0' AS ID
												, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
										) UNION (
											SELECT '-1' AS ID
												, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
										) UNION (
											SELECT '-2' AS ID
												, '" . ffTemplate::_get_word_by_code("sort_published_at") . "' AS name
										) UNION (
											SELECT '-3' AS ID
												, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
										) UNION (
											SELECT '-4' AS ID
												, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
										) UNION (
											SELECT vgallery_fields.ID
							                    , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
							                FROM vgallery_fields
							                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
							                WHERE " . (OLD_VGALLERY
			                                    ? "vgallery_type.name <> 'system'"
			                                    : "1"
			                                ) . "
							                ORDER BY name
							            )";
		}
		if ($item_path == "/search")
		    $extras_title = $source_search_title . ": " . ffTemplate::_get_word_by_code("all");
		elseif ($item_path == "/tag")
		    $extras_title = $source_tag_title . ": " . ffTemplate::_get_word_by_code("all");
        elseif ($item_path == "/overview")
            $extras_title = $source_overview_title . ": " . ffTemplate::_get_word_by_code("all");
		else
		    $extras_title = $source_title . ": " . ucwords(str_replace(array("/search", "/tag", "/overview", "/"), array("", "", " "), $item_path));
	break;
    case "publishing":
		$item_tbl_layout = false;
    
		$source_title = "Publishing";
		$skip_detail = true;
		$icon["group"] = "vg-content-adv";
		$icon["type"] = "vg-publishing";

		$sSQL = "SELECT publishing.*
						, IF(publishing.display_name = ''
							, REPLACE(publishing.name, '-', ' ')
							, publishing.display_name
						) AS display_name        
        			FROM publishing
        			WHERE " . ($ID_item > 0 ? "publishing.ID = " . $db_gallery->toSql($ID_item, "Number") : "publishing.name = " . $db_gallery->toSql(basename($item_source_path))
			);
		$db_gallery->query($sSQL);
		if ($db_gallery->nextRecord()) {
		    $ID_item = $db_gallery->getField("ID", "Number", true);
		    $src_type = $db_gallery->getField("area", "Text", true);
		    $item_path = "/" . $db_gallery->getField("name", "Text", true);

		    $extras_title = $source_title . ": " . ucwords($db_gallery->getField("display_name", "Text", true));
		    switch ($src_type) {
			case "anagraph":
				$src_tbl_field = "anagraph_fields";
			    $sSQL_primary_image = "SELECT anagraph_fields.ID
			                                , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
			                            FROM anagraph_fields
			                                INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
			                            WHERE anagraph_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
			                                AND anagraph_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
			                                AND anagraph_fields.ID IN (
				                                SELECT publishing_fields.ID_fields
				                                FROM publishing_fields
				                                WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($ID_item, "Number") . "
			                                )
			                            [HAVING]
			                            ORDER BY name";
			    $sSQL_sort = "	(
			    					SELECT '0' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
								) UNION (
									SELECT '-1' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
								) UNION (
									SELECT '-3' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
								) UNION (
									SELECT '-4' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
								) UNION (
			    					SELECT anagraph_fields.ID
		                                , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
		                            FROM anagraph_fields
		                                INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
		                            WHERE anagraph_fields.ID IN (
			                            SELECT publishing_fields.ID_fields
			                            FROM publishing_fields
			                            WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($ID_item, "Number") . "
		                            )
		                            ORDER BY name
		                        )";
			    break;
			case "gallery":
				$src_tbl_field = false;
			    break;
			default:
				$src_tbl_field = "vgallery_fields";
			    $sSQL_primary_image = "SELECT vgallery_fields.ID
	                                , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
	                            FROM vgallery_fields
	                                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
	                            WHERE vgallery_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
	                                AND vgallery_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
	                                AND vgallery_fields.ID IN (
	                                    SELECT publishing_fields.ID_fields
	                                    FROM publishing_fields
	                                    WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($ID_item, "Number") . "
	                                )
	                            [HAVING]    
	                            ORDER BY name";
			    $sSQL_sort = "	(
			    					SELECT '0' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
								) UNION (
									SELECT '-1' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
								) UNION (
									SELECT '-2' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_published_at") . "' AS name
								) UNION (
									SELECT '-3' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
								) UNION (
									SELECT '-4' AS ID
										, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
								) UNION (
			    					SELECT vgallery_fields.ID
		                                , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
		                            FROM vgallery_fields
		                                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
		                            WHERE vgallery_fields.ID IN (
		                                    SELECT publishing_fields.ID_fields
		                                    FROM publishing_fields
		                                    WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($ID_item, "Number") . "
		                                )
		                            ORDER BY name
		                        )";
		    }
		}
	break;
    case "files":
		$item_tbl_layout = "vgallery_nodes";
    
		$source_title .= " Gallery";
		$allow_fs = true;
		$icon["type"] = "vg-file";

		$primary_image_simple = true;
		$skip_detail = true;
		$skip_sort = true;

	    if($item_source_path && $item_source_path != "/" && check_function("check_fs")) {
	        check_fs_closest_db($item_source_path);
		}
		
		/*$sSQL = "SELECT files.*
        			FROM files
        			WHERE " . ($ID_item > 0 ? "files.ID = " . $db_gallery->toSql($ID_item, "Number") : "files.name = " . $db_gallery->toSql(basename($item_source_path)) . "
        					AND files.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path))
			);
		$db_gallery->query($sSQL);
		if (!$db_gallery->nextRecord()) {
	        if($item_source_path != "/" && check_function("check_fs"))
	        	check_fs_closest_db($item_source_path);
				//check_fs(FF_DISK_UPDIR . $item_source_path, $item_source_path);

			$sSQL = "SELECT files.*
        				FROM files
        				WHERE " . ($ID_item > 0 ? "files.ID = " . $db_gallery->toSql($ID_item, "Number") : "files.name = " . $db_gallery->toSql(basename($item_source_path)) . "
        						AND files.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path))
				);
			$db_gallery->query($sSQL);
		}*/
		$sSQL = "SELECT files.*
        			FROM files
        			WHERE " . ($ID_item > 0 
        					? "files.ID = " . $db_gallery->toSql($ID_item, "Number") 
        					: "files.name = " . $db_gallery->toSql(basename($item_source_path)
        				) . "
        				AND files.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path))
			);
		$db_gallery->query($sSQL);
		if ($db_gallery->nextRecord()) {
		    $ID_item = $db_gallery->getField("ID", "Number", true);
		    $item_path = stripslash($db_gallery->getField("parent", "Text", true)) . "/" . $db_gallery->getField("name", "Text", true);

		    if ($db_gallery->getField("name", "Text", true)) {
				if ($db_gallery->getField("parent", "Text", true) == "/") {
				    $extras_title = $source_title . ": " . ucwords(str_replace("-", " ", $db_gallery->getField("name", "Text", true)));
				} else {
				    $extras_title = $source_title . ": " . stripslash($db_gallery->getField("parent", "Text", true)) . "/" . $db_gallery->getField("name", "Text", true);
				}
		    } else {
				$extras_title = $source_title . ": " . ffTemplate::_get_word_by_code("all");
		    }
		}
	break;
    case "anagraph":
		$item_tbl_layout = "vgallery_nodes";
    
    	$src_tbl_field = "anagraph_fields";
		$source_title .= " Anagraph";

		$sSQL = "SELECT anagraph_categories.*
        			FROM anagraph_categories
        			WHERE " . ($ID_item > 0 ? "anagraph_categories.ID = " . $db_gallery->toSql($ID_item, "Number") : "anagraph_categories.name = " . $db_gallery->toSql(basename($item_source_path))
			);
		$db_gallery->query($sSQL);
		if ($db_gallery->nextRecord()) {
		    $ID_item = $db_gallery->getField("ID", "Number", true);
		    $item_path = "/" . $db_gallery->getField("name", "Text", true);

		    if ($db_gallery->getField("name", "Text", true)) {
			$extras_title = $source_title . ": " . ucwords(str_replace("-", " ", $db_gallery->getField("name", "Text", true)));
		    } else {
			$extras_title = $source_title . ": " . ffTemplate::_get_word_by_code("all");
		    }
		}

		$sSQL_primary_image = "SELECT anagraph_fields.ID
	                                , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
	                            FROM anagraph_fields
	                                INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
	                            WHERE anagraph_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
	                                AND anagraph_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
	                                AND anagraph_fields.ID_type IN
	                                    (
	                                        SELECT DISTINCT anagraph.ID_type
	                                        FROM anagraph
	                                        WHERE " . ($ID_item > 0 ? " FIND_IN_SET(" . $db_gallery->toSql($ID_item, "Number") . ", anagraph.categories) " : " 1 ") . "
	                                    )
	        					[HAVING]		
	                            ORDER BY name";
		$sSQL_sort = "	(
			    			SELECT '0' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
						) UNION (
							SELECT '-1' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
						) UNION (
							SELECT '-3' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
						) UNION (
							SELECT '-4' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
						) UNION (
							SELECT anagraph_fields.ID
		                        , CONCAT(anagraph_type.name, ' - ', anagraph_fields.name) AS name
		                    FROM anagraph_fields
		                        INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
		                    WHERE anagraph_fields.ID_type IN
		                            (
		                                SELECT DISTINCT anagraph.ID_type
		                                FROM anagraph
		                                WHERE " . ($ID_item > 0 ? " FIND_IN_SET(" . $db_gallery->toSql($ID_item, "Number") . ", anagraph.categories) " : " 1 ") . "
		                            )
		                    ORDER BY name
		                )";
	break;
    default:
		$item_tbl_layout = "vgallery_nodes";
    
    	$src_tbl_field = "vgallery_fields";
		$source_title .= " VGallery";

		$sSQL = "SELECT vgallery_nodes.*
        			FROM vgallery_nodes
        				INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
        			WHERE " . (is_numeric($ID_item) ? "vgallery_nodes.ID = " . $db_gallery->toSql($ID_item, "Number") : ($item_source_path ? "vgallery_nodes.name = " . $db_gallery->toSql(basename($item_source_path)) . "
	                            AND  vgallery_nodes.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path)) : "1"
				)
			);
		$db_gallery->query($sSQL);
		if ($db_gallery->nextRecord()) {
		    $ID_item = $db_gallery->getField("ID", "Number", true);
		    $item_path = stripslash($db_gallery->getField("parent", "Text", true)) . "/" . $db_gallery->getField("name", "Text", true);

		    if ($db_gallery->getField("name", "Text", true)) {
			if ($db_gallery->getField("parent", "Text", true) == "/") {
			    $extras_title = $source_title . ": " . ucwords(str_replace("-", " ", $db_gallery->getField("name", "Text", true)));
			} else {
			    $extras_title = $source_title . ": " . stripslash($db_gallery->getField("parent", "Text", true)) . "/" . $db_gallery->getField("name", "Text", true);
			}
		    } else {
			$extras_title = $source_title . ": " . ffTemplate::_get_word_by_code("all");
		    }
		}

		$sSQL_primary_image = "SELECT vgallery_fields.ID
	                                , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
	                            FROM vgallery_fields
	                                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
	                            WHERE vgallery_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
	                                AND vgallery_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
									" . ($ID_item > 0 || $item_source_path ? " AND FIND_IN_SET(vgallery_fields.ID_type,
		                                    (  
		                                        SELECT GROUP_CONCAT(DISTINCT vgallery.limit_type SEPARATOR ',')
		                                        FROM vgallery_nodes
		                                            INNER JOIN vgallery_fields ON vgallery_nodes.ID_type = vgallery_fields.ID_type
		                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
		                                        WHERE " . ($ID_item > 0 ? "vgallery_nodes.ID = " . $db_gallery->toSql($ID_item, "Number") : "vgallery_nodes.name = " . $db_gallery->toSql(basename($item_source_path)) . "
                                        					AND  vgallery_nodes.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path))
													) . "
		                                    )
		                                )" : ""
									) . "
								[HAVING]
	                            ORDER BY name";
		$sSQL_sort = "	(
			    			SELECT '0' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
						) UNION (
							SELECT '-1' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
						) UNION (
							SELECT '-2' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_published_at") . "' AS name
						) UNION (
							SELECT '-3' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
						) UNION (
							SELECT '-4' AS ID
								, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
						) UNION (		
							SELECT vgallery_fields.ID
	                            , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
	                        FROM vgallery_fields
	                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
	                        WHERE " . (OLD_VGALLERY
	                                ? "vgallery_type.name <> 'system'"
	                                : "1"
	                            ) . "
								" . ($ID_item > 0 || $item_source_path ? " AND FIND_IN_SET(vgallery_fields.ID_type,
		                                (  
		                                    SELECT GROUP_CONCAT(DISTINCT vgallery.limit_type SEPARATOR ',') 
		                                    FROM vgallery_nodes
		                                        INNER JOIN vgallery_fields ON vgallery_nodes.ID_type = vgallery_fields.ID_type
		                                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
		                                    WHERE " . ($ID_item > 0 ? "vgallery_nodes.ID = " . $db_gallery->toSql($ID_item, "Number") : "vgallery_nodes.name = " . $db_gallery->toSql(basename($item_source_path)) . "
                                        				AND  vgallery_nodes.parent = " . $db_gallery->toSql(ffCommon_dirname($item_source_path))
												) . "
		                                )
                                	)" : ""
								) . "
	                        ORDER BY name
	                    )";
}

if (!$hide_source) {
	$src_tbl_field = "vgallery_fields";
    //da finire in base al tblsrc
    $sSQL_primary_image = "SELECT vgallery_fields.ID
                                , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
                            FROM vgallery_fields
                                INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                            WHERE vgallery_fields.ID_extended_type IN(SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload')
                                AND vgallery_fields.ID_data_type IN (SELECT vgallery_fields_data_type.ID FROM vgallery_fields_data_type WHERE vgallery_fields_data_type.name IN ('data','table.alt'))
                                AND FIND_IN_SET(vgallery_fields.ID_type,
                                (  
                                    SELECT GROUP_CONCAT(DISTINCT vgallery.limit_type SEPARATOR ',') 
                                    FROM vgallery_nodes
                                        INNER JOIN vgallery_fields ON vgallery_nodes.ID_type = vgallery_fields.ID_type
                                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                )
                            )
                            [HAVING]
                            ORDER BY name";
    $sSQL_sort = "	(
			    		SELECT '0' AS ID
							, '" . ffTemplate::_get_word_by_code("sort_meta_title_alt") . "' AS name
					) UNION (
						SELECT '-1' AS ID
							, '" . ffTemplate::_get_word_by_code("sort_created") . "' AS name
					) UNION (
						SELECT '-2' AS ID
							, '" . ffTemplate::_get_word_by_code("sort_published_at") . "' AS name
					) UNION (
						SELECT '-3' AS ID
							, '" . ffTemplate::_get_word_by_code("sort_manual") . "' AS name
					) UNION (
						SELECT '-4' AS ID
							, '" . ffTemplate::_get_word_by_code("sort_last_update") . "' AS name
					) UNION (
    					SELECT vgallery_fields.ID
                            , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS name
                        FROM vgallery_fields
                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                        WHERE FIND_IN_SET(vgallery_fields.ID_type,
                            (
                                SELECT GROUP_CONCAT(DISTINCT vgallery.limit_type SEPARATOR ',') 
                                FROM vgallery_nodes
                                    INNER JOIN vgallery_fields ON vgallery_nodes.ID_type = vgallery_fields.ID_type
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                [WHERE]
                            )
                        )
                        ORDER BY name
                    )";
}

if (check_function("get_file_properties")) {
    if (strlen($item_tbl) && strlen($item_path)) {
	$file_properties_thumb = get_file_properties($item_path, $item_tbl, "thumb");
	//if(!$skip_detail)
	$file_properties_preview = get_file_properties($item_path, $item_tbl, "detail");

	if (!strlen($extras_title))
	    $extras_title = $source_title . ": " . ($file_properties_thumb["source"] == "/" ? ffTemplate::_get_word_by_code("all") : (substr_count($file_properties_thumb["source"], "/") > 1 ? $file_properties_thumb["source"] : ucwords(ltrim(str_replace("-", " ", $file_properties_thumb["source"]), "/"))
			    )
		    );
    } else {
	$file_properties_thumb = get_file_properties("/", "", "thumb");
	//if(!$skip_detail)
	$file_properties_preview = get_file_properties("/", "", "detail");

	if (!strlen($extras_title))
	    $extras_title = ffTemplate::_get_word_by_code("extras_modify_title");
    }
}



// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ExtrasModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("extras_modify_title");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title ' . $icon["group"] . '">' . Cms::getInstance("frameworkcss")->get($icon["type"], "icon-tag", array("2x", "content")) . $extras_title . '</h1>';

$oRecord->addEvent("on_done_action", "ExtrasModify_on_done_action");
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->src_table = "settings_thumb";
//$oRecord->setWidthComponent(array(8, 12));

//$oRecord->additional_fields = array("last_update" => new ffData(time(), "Number"));
$oRecord->user_vars["tblsrc"] = $item_tbl;
$oRecord->user_vars["ID_item"] = $ID_item;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//da sistemare questo gruppo deve essere visibile sia nei thumb che nelle preview
/* $oRecord->addContent(null, true, "subject"); 
  $oRecord->groups["subject"] = array(
  "title" => ffTemplate::_get_word_by_code("extras_subject")
  , "cols" => 1
  , "tab" => "subject"
  );
 */
 $sSQL = "SELECT search_tags_group.*
            FROM search_tags_group
            WHERE search_tags_group.`status`";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $sSQL_search_group = "
            ) UNION (
                SELECT CONCAT('" . VG_SITE_SEARCH . "', '/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'search' AS type
                FROM search_tags_group
            ) UNION (
                SELECT CONCAT('/tag/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'tag' AS type
                FROM search_tags_group
            ) UNION (
                SELECT CONCAT('/overview/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'overview' AS type
                FROM search_tags_group
            ";                
    } else {
        $sSQL_search_group = "
            ) UNION (
                SELECT CONCAT('" . VG_SITE_SEARCH . "', '/', name) AS ID, CONCAT('/', name) AS path, 'search' AS type
                FROM vgallery
            ) UNION (
                SELECT CONCAT('" . VG_SITE_SEARCH . "', '/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'search' AS type
                FROM anagraph_categories
            ) UNION (
                SELECT CONCAT('/tag/', name) AS ID, CONCAT('/', name) AS path, 'tag' AS type
                FROM vgallery
            ) UNION (
                SELECT CONCAT('/tag/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'tag' AS type
                FROM anagraph_categories
            ) UNION (
                SELECT CONCAT('/overview/', name) AS ID, CONCAT('/', name) AS path, 'overview' AS type
                FROM vgallery
            ) UNION (
                SELECT CONCAT('/overview/', smart_url) AS ID, CONCAT('/', smart_url) AS path, 'overview' AS type
                FROM anagraph_categories
            ";
    }    
/*
            ) UNION (
                SELECT '" . VG_SITE_SEARCH . "/files' AS ID, '/gallery' AS path, 'search' AS type
            ) UNION (
                SELECT '" . VG_SITE_SEARCH . "/wishlist' AS ID, '/wishlist' AS path, 'search' AS type

            ) UNION (
                SELECT '/tag/files' AS ID, '/gallery' AS path, 'tag' AS type
            ) UNION (
                SELECT '/tag/wishlist' AS ID, '/wishlist' AS path, 'tag' AS type

*/
    $sSQL_search_group .= "
        ) UNION (
            SELECT '" . VG_SITE_SEARCH . "' AS ID, '/' AS path, 'search' AS type
        ) UNION (
            SELECT '/tag' AS ID, '/' AS path, 'tag' AS type
        ) UNION (
            SELECT '/overview' AS ID, '/' AS path, 'overview' AS type
        ";
 
if ($hide_source) {
    $oRecord->insert_additional_fields["tbl_src"] = new ffData($item_tbl);
    //$oRecord->insert_additional_fields["items"] = new ffData($ID_item);
   // $oRecord->insert_additional_fields["cascading"] = new ffData("1", "Number");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "items";
    $oField->data_source = "items";
    $oField->label = ffTemplate::_get_word_by_code("extras_items");
    $oField->widget = "actex";
    $oField->actex_autocomp = true;
    //$oField->widget = "activecomboex";
    $oField->source_SQL = "
                        SELECT DISTINCT ID, path, type  FROM 
                        (
                            (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'files' AS type
                                FROM files
                                WHERE files.is_dir > 0
                                	AND files.parent <> ''
                            ) UNION (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'vgallery_nodes' AS type
                                FROM vgallery_nodes
                                WHERE vgallery_nodes.is_dir > 0
                                    OR vgallery_nodes.name = ''
                                GROUP BY parent, name
                            ) UNION (
                                SELECT '0' AS ID, '/' AS path, 'anagraph' AS type
                            ) UNION (
                                SELECT ID, CONCAT('/', name) AS path, 'anagraph' AS type
                                FROM anagraph_categories
                            ) UNION (
                                SELECT ID, name AS path, 'publishing' AS type
                                FROM publishing
                             $sSQL_search_group
                            ) UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '" . VG_SITE_SEARCH . "', '/', REPLACE(settings_thumb.items, '" . VG_SITE_SEARCH . "', '')) AS path, 'search' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'search'
                            " . (!is_numeric($ID_item) && $item_tbl == "search" 
                                ? ") UNION (
                                    SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/search" ? "/" : str_replace("/search", "", $ID_item)) . " AS path, 'search' AS type
                                " 
                                : ""
                            ) . ")
                            UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '/tag', '/', REPLACE(settings_thumb.items, '/tag', '')) AS path, 'tag' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'tag'
                            " . (!is_numeric($ID_item) && $item_tbl == "tag" 
                                ? ") UNION (
                                    SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/tag" ? "/" : str_replace("/tag", "", $ID_item)) . " AS path, 'tag' AS type 
                                " 
                                : ""
                            ) . ")
                            UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '/overview', '/', REPLACE(settings_thumb.items, '/overview', '')) AS path, 'overview' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'overview'
                            " . (!is_numeric($ID_item) && $item_tbl == "overview" 
                                ? ") UNION (
                                    SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/overview" ? "/" : str_replace("/overview", "", $ID_item)) . " AS path, 'overview' AS type 
                                " 
                                : ""
                            ) . ")
                        ) AS tbl_src
                        WHERE type = " . $db_gallery->toSql($item_tbl) . "
                         ORDER BY type, path
                        ";
                        
    $oField->actex_update_from_db = true;
    $oField->required = true;
    if (strlen($ID_item)) {
    $oField->default_value = new ffData($ID_item);

    /* if($_REQUEST["keys"]["ID"] > 0) {
      $oField->multi_limit_select = true;
      } */
    }
    if($item_tbl_layout)
    	$oField->setWidthComponent(6);

    $oRecord->addContent($oField);

    if($item_tbl_layout)
    {
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "layout";
	    $oField->data_source = "ID_layout";
	    $oField->label = ffTemplate::_get_word_by_code("extras_layout");
	    $oField->base_type = "Number";
	    $oField->widget = "actex";
	    //$oField->widget = "activecomboex";
	    $oField->source_SQL = "
	                        SELECT ID, path, type FROM 
	                        (
	                            (
	                                SELECT layout.ID, layout.name AS path, 'files' AS type
	                                FROM layout
	                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
	                                WHERE layout_type.name = 'GALLERY'
	                            ) UNION (
	                                SELECT layout.ID, layout.name AS path, 'vgallery_nodes' AS type
	                                FROM layout
	                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
	                                WHERE layout_type.name = 'VIRTUAL_GALLERY'
	                                    AND layout.value <> 'anagraph'
	                            ) UNION (
	                                SELECT layout.ID, layout.name AS path, 'vgallery_nodes' AS type
	                                FROM layout
	                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
	                                WHERE layout_type.name = 'VIRTUAL_GALLERY'
	                                    AND layout.value = 'anagraph'
	                            ) UNION (
	                                SELECT layout.ID, layout.name AS path, 'search' AS type
	                                FROM layout
	                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
	                                WHERE layout_type.name = 'SEARCH'
	                            )
	                        
	                        ) AS tbl_src
	                        WHERE type = " . $db_gallery->toSql($item_tbl_layout) . "
	                        ORDER BY type, path";
	    $oField->actex_update_from_db = true;
	    if (strlen($ID_layout)) {
	   		$oField->default_value = new ffData($ID_layout, "Number");
	    }
	    $oField->setWidthComponent(6);
	    $oRecord->addContent($oField);  
	}  
} else {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "tblsrc";
    $oField->data_source = "tbl_src";
    $oField->label = ffTemplate::_get_word_by_code("extras_tbl_src");
    $oField->widget = "actex";
    //$oField->widget = "activecomboex";
    $oField->multi_pairs = array(
		array(new ffData("files"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
		array(new ffData("vgallery_nodes"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
		array(new ffData("publishing"), new ffData(ffTemplate::_get_word_by_code("publishing"))),
		array(new ffData("search"), new ffData(ffTemplate::_get_word_by_code("search"))),
		array(new ffData("tag"), new ffData(ffTemplate::_get_word_by_code("tag"))),
        array(new ffData("overview"), new ffData(ffTemplate::_get_word_by_code("overview"))),
		array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph")))
    );
    $oField->required = true;
    if (strlen($item_tbl)) {
		$oField->default_value = new ffData($item_tbl);
    }
    $oField->actex_child = array("items", "layout");
    /* if(strlen($item_tbl)) {
      $oField->default_value = new ffData($item_tbl, "Text");
      $oField->multi_limit_select = true;
      } */
    $oField->setWidthComponent(4);
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "items";
    $oField->data_source = "items";
    $oField->label = ffTemplate::_get_word_by_code("extras_items");
    $oField->widget = "actex";
    //$oField->widget = "activecomboex";
    $oField->source_SQL = "
                        SELECT DISTINCT ID, path, type  FROM 
                        (
                            (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'files' AS type
                                FROM files
                                WHERE files.is_dir > 0
                                	AND files.parent <> ''
                            ) UNION (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'vgallery_nodes' AS type
                                FROM vgallery_nodes
                                WHERE vgallery_nodes.is_dir > 0
                                	OR vgallery_nodes.name = ''
                                GROUP BY parent, name
                            ) UNION (
                                SELECT '0' AS ID, '/' AS path, 'anagraph' AS type
                            ) UNION (
                                SELECT ID, CONCAT('/', name) AS path, 'anagraph' AS type
                                FROM anagraph_categories
                            ) UNION (
                                SELECT ID, name AS path, 'publishing' AS type
                                FROM publishing
							$sSQL_search_group
                            ) UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '" . VG_SITE_SEARCH . "', '/', REPLACE(settings_thumb.items, '" . VG_SITE_SEARCH . "', '')) AS path, 'search' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'search'
							" . (!is_numeric($ID_item) && $item_tbl == "search" 
								? ") UNION (
                                	SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/search" ? "/" : str_replace("/search", "", $ID_item)) . " AS path, 'search' AS type
								" 
								: ""
	    					) . ")
                            UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '/tag', '/', REPLACE(settings_thumb.items, '/tag', '')) AS path, 'tag' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'tag'
							" . (!is_numeric($ID_item) && $item_tbl == "tag" 
								? ") UNION (
                                	SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/tag" ? "/" : str_replace("/tag", "", $ID_item)) . " AS path, 'tag' AS type 
								" 
								: ""
	    					) . ")
                            UNION (
                                SELECT settings_thumb.items AS ID, IF(settings_thumb.items = '/overview', '/', REPLACE(settings_thumb.items, '/overview', '')) AS path, 'overview' AS type
                                FROM settings_thumb
                                WHERE settings_thumb.tbl_src = 'overview'
                            " . (!is_numeric($ID_item) && $item_tbl == "overview" 
                                ? ") UNION (
                                    SELECT " . $db_gallery->toSql($ID_item) . " AS ID, " . $db_gallery->toSql($ID_item == "/overview" ? "/" : str_replace("/overview", "", $ID_item)) . " AS path, 'overview' AS type 
                                " 
                                : ""
                            ) . ")
                        ) AS tbl_src
                        [WHERE]
                         ORDER BY type, path
                        ";
    $oField->actex_father = "tblsrc";
    $oField->actex_related_field = "type";
    if (!$skip_detail && $src_tbl_field) {
		$oField->actex_child[] = "preview_image";
		
		$js_thumb_image = "
        	var previewImage = ff.ffField.actex.getInstance('ExtrasModify_preview_image');
    		if(jQuery('#ExtrasModify_tblsrc').val() == 'files' && jQuery('#ExtrasModify_preview_image option').length == 1) {
				previewImage.options.select_one_val = '1';
				jQuery('#ExtrasModify_preview_image').closest('.actex-wrapper').hide();
		    } else {
				previewImage.options.select_one_val = '';
				jQuery('#ExtrasModify_preview_image').closest('.actex-wrapper').show();
    		} 
		";
	}
    if (!$primary_image_simple) {
    	if($src_tbl_field)
			$oField->actex_child[] = "thumb_image";
        $oField->actex_on_change = "function(obj, old_value, action) { 
        	if(action == 'change') {
        		var thumbImage = ff.ffField.actex.getInstance('ExtrasModify_thumb_image');
    			if(jQuery('#ExtrasModify_tblsrc').val() == 'files' && jQuery('#ExtrasModify_thumb_image option').length == 1) {
					thumbImage.options.select_one_val = '1';
					jQuery('#ExtrasModify_thumb_image').closest('.actex-wrapper').hide();
			    } else {
					thumbImage.options.select_one_val = '';
					jQuery('#ExtrasModify_thumb_image').closest('.actex-wrapper').show();
    			} 
    			$js_thumb_image
    		}
	    }";
	}
    if (!$skip_sort)
	$oField->actex_child[] = "sort";


    $oField->actex_update_from_db = true;
    $oField->required = true;
    if (strlen($ID_item)) {
	$oField->default_value = new ffData($ID_item);

	/* if($_REQUEST["keys"]["ID"] > 0) {
	  $oField->multi_limit_select = true;
	  } */
    }
    $oField->setWidthComponent(4);
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "layout";
    $oField->data_source = "ID_layout";
    $oField->label = ffTemplate::_get_word_by_code("extras_layout");
    $oField->base_type = "Number";
    $oField->widget = "actex";
    //$oField->widget = "activecomboex";
    $oField->source_SQL = "
                        SELECT ID, path, type FROM 
                        (
                            (
                                SELECT layout.ID, layout.name AS path, 'files' AS type
                                FROM layout
                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
                                WHERE layout_type.name = 'GALLERY'
                            ) UNION (
                                SELECT layout.ID, layout.name AS path, 'vgallery_nodes' AS type
                                FROM layout
                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
                                WHERE layout_type.name = 'VIRTUAL_GALLERY'
                                    AND layout.value <> 'anagraph'
                            ) UNION (
                                SELECT layout.ID, layout.name AS path, 'vgallery_nodes' AS type
                                FROM layout
                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
                                WHERE layout_type.name = 'VIRTUAL_GALLERY'
                                    AND layout.value = 'anagraph'
                            ) UNION (
                                SELECT layout.ID, layout.name AS path, 'search' AS type
                                FROM layout
                                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type
                                WHERE layout_type.name = 'SEARCH'
                            )
                        
                        ) AS tbl_src
                        [WHERE]
                        ORDER BY type, path";
    $oField->actex_father = "tblsrc";
    $oField->actex_related_field = "type";
    $oField->actex_update_from_db = true;
    if (strlen($ID_layout)) {
	$oField->default_value = new ffData($ID_layout, "Number");
    }
    $oField->setWidthComponent(4);
    $oRecord->addContent($oField);
       /*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "cascading";
    $oField->label = ffTemplate::_get_word_by_code("extras_cascading");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("1", "Number");
    $oRecord->addContent($oField);  */
}

if (Auth::env("AREA_PROPERTIES_DESIGN_SHOW_MODIFY"))
{
    $oRecord->addContent(null, true, "thumb");
    $oRecord->groups["thumb"] = array(
	"title" => ffTemplate::_get_word_by_code("extras_thumb")
	//, "title_class" => ($skip_detail && !$allow_fs ? "" : "dialogSubTitleTab dep-thumb")
	, "primary_field" => "thumb_hide"
	, "tab_dialog" => ($skip_detail && !$allow_fs ? false : true)
	, "cols" => 1
    );


    /**
     *  THUMB TEMPLATE
     */
    /*
      $sSQL = "SELECT ID FROM settings_thumb_mode WHERE name = " . $db_gallery->toSql($file_properties_thumb["container_mode"]);
      $db_gallery->query($sSQL);
      if($db_gallery->nextRecord())
      $ID_thumb_mode = $db_gallery->getField("ID", "Number");
     */
    if($skip_detail && !$allow_fs) {
        $oRecord->insert_additional_fields["thumb_hide"]	    			= new ffData(0, "Number");
        $oRecord->update_additional_fields["thumb_hide"]	    			= new ffData(0, "Number");
    } else {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "thumb_hide";
        $oField->label = ffTemplate::_get_word_by_code("extras_thumb_show");
        $oField->base_type = "Number";
        $oField->extended_type = "Boolean";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData($file_properties_thumb["hide"], "Number");
        $oRecord->addContent($oField, $group_thumb);     
    }   
     
    $oField = ffField::factory($cm->oPage);
    $oField->id = "thumb_container_ID_mode";
    $oField->base_type = "Number";
    $oField->label = ffTemplate::_get_word_by_code("extras_thumb_container_ID_mode");
    $oField->widget = "actex";
    //$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/mode/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasModeModify_confirmdelete";
    $oField->resources[] = "ExtrasModeModify";
    $oField->source_SQL = "SELECT ID, CONCAT(name, ' (', description, ')') FROM settings_thumb_mode WHERE `type` LIKE '%thumb%' ORDER BY name";
    $oField->default_value = new ffData($file_properties_thumb["container_ID_mode"], "Number");
    $oField->required = true;
    //$oField->setWidthLabel(array(3, 5, 12));
    $oRecord->addContent($oField, "thumb");


    /**
     *  THUMB ITEMS
     */
    $oRecord->addContent(null, true, "ThumbItems");
    $oRecord->groups["ThumbItems"] = array(
		"title" => ffTemplate::_get_word_by_code("extras_aspect")
		//, "title_class" => "dialogSubTitleTab dep-thumb notab"
		//, "title_field" => "thumb_fluid"
		, "primary_field" => "thumb_fluid"
		, "tab_dialog" => "thumb"
    );
    if (check_function("set_fields_grid_system")) {
		set_fields_grid_system($oRecord, array(
		    "group" => "ThumbItems"
		    , "fluid" => array(
				"name" => "thumb_fluid"
				, "label" => ffTemplate::_get_word_by_code("grid_thumb_modify_fluid")
				, "prefix" => "thumb_grid"
				, "one_field" => $file_properties_thumb["default_grid"]
				, "hide" => false
				, "full_row" => true
		    )
		    , "class" => array(
				"name" => "thumb_class"
		    )
		    , "wrap" => array(
				"name" => "thumb_wrap"
				, "one_field" => $file_properties_thumb["wrap"]
				, "multi" => array(
				    "container" => array(
						"multi_pairs" => array(
						    array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": " . ffTemplate::_get_word_by_code("grid_skip_all"))),
						    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) ? "." . Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) : "") . "")),
						    array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) ? "." . Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) : "") . ""))
						)
				    )
				    , "row" => array(
						"multi_pairs" => array(
						    array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": " . ffTemplate::_get_word_by_code("grid_skip_all"))),
						    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) ? "." . Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) : "") . "")),
						    array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) ? "." . Cms::getInstance("frameworkcss")->get("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) : "") . ""))
						)
				    )
				)
		    )
		), $framework_css);
    }

    /**
     *  THUMB PRIMARY IMAGE
     */
    $oRecord->addContent(null, true, "ThumbImage");
    $oRecord->groups["ThumbImage"] = array(
	"title" => ffTemplate::_get_word_by_code("extras_primary_image")
	// , "title_class" => "dialogSubTitleTab dep-thumb notab"
	// , "title_field" => "thumb_image"
	, "primary_field" => "thumb_image"
	, "tab_dialog" => "thumb"
	, "cols" => 1
    );

    $oField = ffField::factory($cm->oPage);
    $oField->label = ffTemplate::_get_word_by_code("extras_thumb_image");
    $oField->id = "thumb_image";

    if ($primary_image_simple) {
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");

	$oField->default_value = new ffData("1", "Number");
    } else {
		if (!$hide_source) {
		    $oField->widget = "actex";
		    //$oField->widget = "activecomboex";
		} else {
			$oField->widget = "actex";
			$oField->actex_autocomp = true;
			$oField->actex_multi = true;
			//$oField->actex_having_field = "name";

/*		    $oField->widget = "autocomplete";
		    $oField->autocomplete_multi = true;
		    $oField->autocomplete_combo = true;
		    $oField->autocomplete_compare_having = "name";
		    $oField->autocomplete_minLength = 0;*/
		}

		$oField->source_SQL = $sSQL_primary_image;
		if (!$hide_source && $src_tbl_field) {
		    $oField->actex_father = "items";
			$oField->actex_related_field = $src_tbl_field . ".ID";
		}
		$oField->actex_update_from_db = true;
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");

		$oField->default_value = new ffData((is_array($file_properties_thumb["image"]["fields"]) ? implode(",", $file_properties_thumb["image"]["fields"]) : ""));
    }
    //$oField->setWidthComponent(3);
    $oRecord->addContent($oField, "ThumbImage");

   

    /* $oField = ffField::factory($cm->oPage);
      $oField->id = "thumb_ID_social";
      $oField->label = ffTemplate::_get_word_by_code("extras_thumb_ID_social");
      $oField->widget = "activecomboex";
      $oField->actex_update_from_db = true;

      $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/social/modify";
      $oField->actex_dialog_edit_params = array("keys[ID]" => null);
      $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasSocialModify_confirmdelete";
      $oField->resources[] = "ExtrasSocialModify";

      $oField->source_SQL = "SELECT ID, name FROM settings_thumb_social ORDER BY name";
      $oField->default_value = new ffData($file_properties_thumb["social"]["ID"], "Number");
      $oRecord->addContent($oField, "Thumb"); */

	$img_setting_columns = array(4,4,4,4);
    if (strlen($template_framework)) {
		if (check_function("set_fields_grid_system")) {
		    set_fields_grid_system($oRecord, array(
					"group" => "ThumbImage"
					, "fluid" => false
					, "class" => false
					, "wrap" => false
					, "extra" => array(
					    "prefix" => "thumb_extra"
					    , "one_field" => $file_properties_thumb["default_extra"]
					    , "default_location" => $file_properties_thumb["default_extra_location"]
					    , "default_value" => 0
					)
					, "image" => array(
						"prefix" => "thumb_ID_image"
						, "default_value" => array(
							$file_properties_thumb["image"]["src"]["default"]["ID"]
							, $file_properties_thumb["image"]["src"]["md"]["ID"]
							, $file_properties_thumb["image"]["src"]["sm"]["ID"]
							, $file_properties_thumb["image"]["src"]["xs"]["ID"]
						)
					)
			    ), $framework_css
		    );
		}
		
		if($template_framework == "bootstrap" || $template_framework == "foundation") {
		    $img_setting_columns = array(6,6,6,6);
		}		

 		$oField = ffField::factory($cm->oPage);
	    $oField->id = "thumb_image_detail";
	    $oField->label = ffTemplate::_get_word_by_code("extras_thumb_image_link");
	    $oField->widget = "actex";
	    //$oField->widget = "activecomboex";
	    //$oField->actex_update_from_db = true;
	    $oField->multi_pairs = array(
			array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
			array(new ffData("image"), new ffData(ffTemplate::_get_word_by_code("to_large_image")))
	    );
	    if (!$primary_image_simple)
			$oField->multi_pairs[] = array(new ffData("content"), new ffData(ffTemplate::_get_word_by_code("to_detail_content")));
	    $oField->required = true;
	    $oField->actex_child = "thumb_display_view_mode";
	    $oField->default_value = new ffData($file_properties_thumb["image"]["link_to"]);
	    $oField->multi_select_one = false;
	    $oField->setWidthComponent($img_setting_columns);
	    $oRecord->addContent($oField, "ThumbImage");

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "thumb_display_view_mode";
	    $oField->label = ffTemplate::_get_word_by_code("extras_thumb_display_view_mode");
	    $oField->widget = "actex";
	    //$oField->widget = "activecomboex";
	    $oField->actex_update_from_db = true;
	    if(check_function("query_plugin_js"))
	    	$oField->source_SQL = query_plugin_js();
/*
	    $oField->source_SQL = "SELECT ID, name, type FROM 
		                    (
		                        SELECT DISTINCT
		                            js.name AS ID
		                            , js.name AS name
		                            , layout_type_plugin.type AS type 
		                        FROM layout_type_plugin
		                            INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status > 0
		                            INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND (layout_type.name = 'VIRTUAL_GALLERY' OR layout_type.name = 'GALLERY' OR layout_type.name = 'PUBLISHING')
		                        WHERE layout_type_plugin.type <> ''
		                    ) AS tbl_src
		                    [WHERE]
	                        ORDER BY name";
*/
	    $oField->actex_father = "thumb_image_detail";
	    $oField->actex_related_field = "type";
	    $oField->default_value = new ffData($file_properties_thumb["image"]["plugin"], "Text");
	    $oField->setWidthComponent($img_setting_columns);
	    $oRecord->addContent($oField, "ThumbImage");	    
    }

    /**
     *  THUMB SORT
     */
    if (!$skip_sort) {
		$oRecord->addContent(null, true, "ThumbSort");
		$oRecord->groups["ThumbSort"] = array(
		    "title" => ffTemplate::_get_word_by_code("extras_sort")
		    //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		    , "tab_dialog" => "thumb"
		    , "cols" => 1
		);
		$oField = ffField::factory($cm->oPage);
		$oField->label = ffTemplate::_get_word_by_code("extras_sort");
		$oField->id = "sort";
		$oField->base_type = "Number";
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->source_SQL = $sSQL_sort;
		if (!$hide_source)
		    $oField->actex_father = "items";
		
		$oField->actex_compare_field = "vgallery_nodes.ID";
		$oField->actex_related_field = "vgallery_nodes.ID";
		$oField->actex_update_from_db = true;
		$oField->multi_select_one = false;
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbSort");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "sort_method";
		$oField->label = ffTemplate::_get_word_by_code("extras_sort_method");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		    array(new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("discending"))),
		    array(new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ascending")))
		);
		$oField->default_value = new ffData("DESC");
		$oField->multi_select_one = false;
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbSort");
    }
    /** 
     *  THUMB PAGENAVIGATOR
     */
    if (!$skip_navigation) {
		$oRecord->addContent(null, true, "ThumbPageNav");
		$oRecord->groups["ThumbPageNav"] = array(
		    "title" => ffTemplate::_get_word_by_code("extras_pagenav")
		    //, "title_class" => "dialogSubTitleTab dep-thumb notab"
		    //, "title_field" => "thumb_pagenav_location"
		    , "primary_field" => "thumb_pagenav_location"
		    , "tab_dialog" => "thumb"
		    , "cols" => 1
		);
		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_pagenav_location";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_pagenav_location");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		    array(new ffData("bottom"), new ffData(ffTemplate::_get_word_by_code("pagenav_bottom"))),
		    array(new ffData("top"), new ffData(ffTemplate::_get_word_by_code("pagenav_top"))),
		    array(new ffData("both"), new ffData(ffTemplate::_get_word_by_code("pagenav_both"))),
		    array(new ffData("hide"), new ffData(ffTemplate::_get_word_by_code("pagenav_hide")))
		);
		$oField->default_value = new ffData($file_properties_thumb["pagenav_location"]);
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
		$oRecord->addContent($oField, "ThumbPageNav");
                    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_rec_per_page";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_rec_per_page");
		$oField->base_type = "Number";
		$oField->default_value = new ffData($file_properties_thumb["rec_per_page"], "Number");
		$oField->required = true;
		$oField->setWidthComponent(6);
		$oField->setWidthLabel(8, true, false);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_npage_per_frame";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_npage_per_frame");
		$oField->base_type = "Number";
		$oField->default_value = new ffData($file_properties_thumb["npage_per_frame"], "Number");
		$oField->setWidthComponent(6);
		$oField->setWidthLabel(8, true, false);
		$oRecord->addContent($oField, "ThumbPageNav");

        $oField = ffField::factory($cm->oPage);
        $oField->id = "thumb_pagenav_infinite";
        $oField->label = ffTemplate::_get_word_by_code("extras_thumb_pagenav_infinite");
        $oField->base_type = "Number";
        $oField->extended_type = "Boolean";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData($file_properties_thumb["infinite"], "Number");
        $oField->setWidthComponent(6);
        $oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_direction_arrow";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_direction_arrow");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["direction_arrow"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_frame_arrow";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_frame_arrow");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["frame_arrow"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_custom_page";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_custom_page");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["custom_page"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_tot_elem";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_tot_elem");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["tot_elem"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_frame_per_page";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_frame_per_page");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["frame_per_page"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_rec_per_page_all";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_rec_per_page_all");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_thumb["rec_per_page_all"], "Number");
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "ThumbPageNav");
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "thumb_pagenav_alphanum";
		$oField->label = ffTemplate::_get_word_by_code("extras_thumb_pagenav_alphanum");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		    array(new ffData("top"), new ffData(ffTemplate::_get_word_by_code("pagenav_top"))),
		    array(new ffData("bottom"), new ffData(ffTemplate::_get_word_by_code("pagenav_bottom"))),
		    array(new ffData("both"), new ffData(ffTemplate::_get_word_by_code("pagenav_both"))) 
		    
		);
		$oField->default_value = new ffData($file_properties_thumb["alphanum"]);
		$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing"); 
		$oField->setWidthComponent(6);
		$oField->setWidthLabel(8);
		$oRecord->addContent($oField, "ThumbPageNav");		
    }

    /* $sSQL = "SELECT ID FROM settings_thumb_mode WHERE name = " . $db_gallery->toSql($file_properties_preview["container_mode"]);
      $db_gallery->query($sSQL);
      if($db_gallery->nextRecord())
      $ID_preview_mode = $db_gallery->getField("ID", "Number");
     */
    if ($skip_detail) 
    {
		$oRecord->insert_additional_fields["preview_hide"]	    			= new ffData($file_properties_preview["hide"], "Number");
		$oRecord->insert_additional_fields["preview_display_view_mode"]	    = new ffData($file_properties_preview["image"]["plugin"]);
		$oRecord->insert_additional_fields["preview_ID_image"]		    	= new ffData($file_properties_preview["image"]["src"]["default"]["ID"], "Number");
		$oRecord->insert_additional_fields["preview_ID_image_md"]		    = new ffData($file_properties_preview["image"]["src"]["md"]["ID"], "Number");
		$oRecord->insert_additional_fields["preview_ID_image_sm"]		    = new ffData($file_properties_preview["image"]["src"]["sm"]["ID"], "Number");
		$oRecord->insert_additional_fields["preview_ID_image_xs"]		    = new ffData($file_properties_preview["image"]["src"]["xs"]["ID"], "Number");
		$oRecord->insert_additional_fields["preview_ID_social"]		    	= new ffData($file_properties_preview["social"]["ID"], "Number");
		$oRecord->insert_additional_fields["preview_image"]		    		= new ffData((is_array($file_properties_preview["image"]["fields"]) ? implode(",", $file_properties_preview["image"]["fields"]) : ""));
		$oRecord->insert_additional_fields["preview_image_detail"]	    	= new ffData($file_properties_preview["image"]["link_to"]);
		$oRecord->insert_additional_fields["preview_class"]		    		= new ffData($file_properties_preview["default_class"]);
		$oRecord->insert_additional_fields["preview_grid"]		    		= new ffData((is_array($file_properties_preview["default_grid"]) ? implode(",", $file_properties_preview["default_grid"]) : ""));
		$oRecord->insert_additional_fields["preview_extra"]		    		= new ffData((is_array($file_properties_preview["default_extra"]) ? implode(",", $file_properties_preview["default_extra"]) : ""));
		$oRecord->insert_additional_fields["preview_extra_class_left"]		= new ffData($file_properties_preview["default_extra_class"]["left"]);
		$oRecord->insert_additional_fields["preview_extra_class_right"]		= new ffData($file_properties_preview["default_extra_class"]["right"]);
		$oRecord->insert_additional_fields["preview_extra_location"]		= new ffData($file_properties_preview["default_extra_location"], "Number");
		$oRecord->insert_additional_fields["preview_fluid"]		    		= new ffData($file_properties_preview["fluid"], "Number");
    } 
    else 
    {
		/**
		 *  PREVIEW TEMPLATE
		 */
		$oRecord->addContent(null, true, "preview");
		$oRecord->groups["preview"] = array(
		    "title" => ffTemplate::_get_word_by_code("extras_preview")
		    //, "title_class" => "dialogSubTitleTab dep-preview"
		    , "primary_field" => "preview_hide"
		    , "tab_dialog" => true
		    , "cols" => 1
		);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "preview_hide";
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_show"); 
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->default_value = new ffData($file_properties_preview["hide"], "Number");
		$oRecord->addContent($oField, "preview");  	
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "preview_container_ID_mode";
		$oField->base_type = "Number";
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_container_ID_mode");
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
		$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/mode/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasModeModify_confirmdelete";
		$oField->resources[] = "ExtrasModeModify";
		$oField->source_SQL = "SELECT ID, CONCAT(name, ' (', description, ')') FROM settings_thumb_mode WHERE `type` LIKE '%detail%' ORDER BY name";
		$oField->default_value = new ffData($file_properties_preview["container_ID_mode"], "Number");
		$oField->required = true;
		//$oField->setWidthLabel(array(3, 5, 12));
		$oRecord->addContent($oField, "preview");

		/**
		 *  PREVIEW BLOCK OVERRIDE
		 */
		$oRecord->addContent(null, true, "PreviewBlock");
		$oRecord->groups["PreviewBlock"] = array(
		    "title" => ffTemplate::_get_word_by_code("extras_block_override")
		    //, "title_class" => "dialogSubTitleTab dep-preview notab"
		    //, "title_field" => "preview_fluid"
		    , "primary_field" => "preview_fluid"
		    , "tab_dialog" => "preview"
		    , "cols" => 1
		);
		if (strlen($template_framework)) {
		    if (check_function("set_fields_grid_system")) {
				set_fields_grid_system($oRecord, array(
					    "group" => "PreviewBlock"
					    , "fluid" => array(
							"name" => "preview_fluid"
						, "label" => ffTemplate::_get_word_by_code("grid_preview_modify_fluid")
						, "prefix" => "preview_grid"
						, "one_field" => $file_properties_preview["default_grid"]
						, "hide" => ffTemplate::_get_word_by_code("no")
						, "full_row" => true
						, "default_value" => new ffData("-3", "Number")
					    )
					    , "class" => array(
							"name" => "preview_class"
					    )
					    , "wrap" => false
					), $framework_css
				);
		    }
		}

		/**
		 *  PREVIEW PRIMARY IMAGE
		 */
		$oRecord->addContent(null, true, "PreviewImage");
		$oRecord->groups["PreviewImage"] = array(
		    "title" => ffTemplate::_get_word_by_code("extras_primary_image")
		    //, "title_class" => "dialogSubTitleTab dep-preview notab"
		    //, "title_field" => "preview_image"
		    , "primary_field" => "preview_image"
		    , "tab_dialog" => "preview"
		    , "cols" => 1
		);
		$oField = ffField::factory($cm->oPage);
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_image");
		$oField->id = "preview_image";

		if ($primary_image_simple) {
		    $oField->base_type = "Number";
		    $oField->extended_type = "Boolean";
		    $oField->control_type = "checkbox";
		    $oField->checked_value = new ffData("1", "Number");
		    $oField->unchecked_value = new ffData("0", "Number");

		    $oField->default_value = new ffData("1", "Number");
		} else {
		    if (!$hide_source) {
				$oField->widget = "actex";
				//$oField->widget = "activecomboex";
			} else {
				$oField->widget = "actex";
				$oField->actex_autocomp = true;
				$oField->actex_multi = true;
				//$oField->actex_having_field = "name";

/*				$oField->widget = "autocomplete";
				$oField->autocomplete_multi = true;
				$oField->autocomplete_combo = true;
				$oField->autocomplete_compare_having = "name";
				$oField->autocomplete_minLength = 0;*/
		    }
		    $oField->source_SQL = $sSQL_primary_image;
			if (!$hide_source && $src_tbl_field) {
			    $oField->actex_father = "items";
				$oField->actex_related_field = $src_tbl_field . ".ID";
			}

		    $oField->actex_update_from_db = true;
		    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");

		    $oField->default_value = new ffData((is_array($file_properties_preview["image"]["fields"]) ? implode(",", $file_properties_preview["image"]["fields"]) : ""));
		}
		//$oField->setWidthComponent(6);
		$oRecord->addContent($oField, "PreviewImage");

		$img_setting_columns = array(4,4,4,4);
		if (strlen($template_framework)) {
		    if (check_function("set_fields_grid_system")) {
				set_fields_grid_system($oRecord, array(
					    "group" => "PreviewImage"
					    , "fluid" => false
					    , "class" => false
					    , "wrap" => false
					    , "extra" => array(
							"prefix" => "preview_extra"
							, "one_field" => $file_properties_preview["default_extra"]
							, "default_location" => $file_properties_preview["default_extra_location"]
							, "default_value" => 0
					    )
					    , "image" => array(
							"prefix" => "preview_ID_image"
							, "default_value" => array(
								$file_properties_preview["image"]["src"]["default"]["ID"]
								, $file_properties_preview["image"]["src"]["md"]["ID"]
								, $file_properties_preview["image"]["src"]["sm"]["ID"]
								, $file_properties_preview["image"]["src"]["xs"]["ID"]
							)
						)
				    ), $framework_css
			    );
			}
			
			if($template_framework == "bootstrap" || $template_framework == "foundation") {
			    $img_setting_columns = array(6,6,6,6);
			}
	    }
	    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "preview_image_detail";
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_image_link");
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		//$oField->actex_update_from_db = true;
		$oField->multi_pairs = array(
		    array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
		    array(new ffData("image"), new ffData(ffTemplate::_get_word_by_code("to_large_image")))
		);
		$oField->required = true;
		$oField->actex_child = "preview_display_view_mode";
		$oField->default_value = new ffData($file_properties_preview["image"]["link_to"]);
		$oField->multi_select_one = false;
		$oField->setWidthComponent($img_setting_columns);
		$oRecord->addContent($oField, "PreviewImage");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "preview_display_view_mode";
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_display_view_mode");
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
	    if(check_function("query_plugin_js"))
	    	$oField->source_SQL = query_plugin_js();		
/*
		$oField->source_SQL = "SELECT ID, name, type FROM 
			                    (
			                        SELECT DISTINCT
			                            js.name AS ID
			                            , js.name AS name
			                            , layout_type_plugin.type AS type 
			                        FROM layout_type_plugin
			                            INNER JOIN js ON layout_type_plugin.ID_js = js.ID AND js.status > 0
			                            INNER JOIN layout_type ON layout_type.ID = layout_type_plugin.ID_layout_type AND (layout_type.name = 'VIRTUAL_GALLERY' OR layout_type.name = 'GALLERY' OR layout_type.name = 'PUBLISHING')
			                        WHERE layout_type_plugin.type <> ''
			                    ) AS tbl_src
			                    [WHERE]
	                            ORDER BY name";
*/
		$oField->actex_father = "preview_image_detail";
		$oField->actex_related_field = "type";
		$oField->default_value = new ffData($file_properties_preview["image"]["plugin"], "Text");
		$oField->setWidthComponent($img_setting_columns);
		$oRecord->addContent($oField, "PreviewImage");
		/**
		 *  PREVIEW SOCIAL
		 */
		$oRecord->addContent(null, true, "PreviewSocial");
		$oRecord->groups["PreviewSocial"] = array(
			"title" => ffTemplate::_get_word_by_code("extras_social")
			//, "title_class" => "dialogSubTitleTab dep-preview notab"
			, "tab_dialog" => "preview"
			, "cols" => 1
		);
		$oField = ffField::factory($cm->oPage);
		$oField->id = "preview_ID_social";
		$oField->label = ffTemplate::_get_word_by_code("extras_preview_ID_social");
		$oField->widget = "actex";
		//$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
		$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/social/modify";
		$oField->actex_dialog_edit_params = array("keys[ID]" => null);
		$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasSocialModify_confirmdelete";
		$oField->resources[] = "ExtrasSocialModify";

		$oField->source_SQL = "SELECT ID, name FROM settings_thumb_social ORDER BY name";
		$oField->default_value = new ffData($file_properties_preview["social"]["ID"], "Number");
		$oRecord->addContent($oField, "PreviewSocial");    
	}
    if ($allow_fs) 
    {
	/**
	 *  FS SETTINGS
	 */
	$oRecord->addContent(null, true, "settings");
	$oRecord->groups["settings"] = array(
	    "title" => ffTemplate::_get_word_by_code("extras_settings")
	    //, "title_class" => "dialogSubTitleTab dep-settings"
	    , "tab_dialog" => true
	    , "cols" => 1
	);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "allow_insert_dir";
	$oField->label = ffTemplate::_get_word_by_code("extras_allow_insert_dir");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData((!strlen($file_properties_thumb["allow_insert_dir"]) ? "1" : $file_properties_thumb["allow_insert_dir"]), "Number");
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField, "settings");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "hide_dir";
	$oField->label = ffTemplate::_get_word_by_code("extras_hide_dir");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData((!strlen($file_properties_thumb["hide_dir"]) ? "1" : $file_properties_thumb["hide_dir"]), "Number");
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField, "settings");

	$oRecord->addContent(null, true, "SettingsFile");
	$oRecord->groups["SettingsFile"] = array(
	    "title" => ffTemplate::_get_word_by_code("extras_settings_file")
	    //, "title_class" => "dialogSubTitleTab dep-settings notab"
	    //, "title_field" => "allow_insert_file"
	    , "primary_field" => "allow_insert_file"
	    , "tab_dialog" => "settings"
	    , "cols" => 1
	);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "allow_insert_file";
	$oField->label = ffTemplate::_get_word_by_code("extras_allow_insert_file");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData((!strlen($file_properties_thumb["allow_insert_file"]) ? "1" : $file_properties_thumb["allow_insert_file"]), "Number");
	$oRecord->addContent($oField, "SettingsFile");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "max_upload";
	$oField->label = ffTemplate::_get_word_by_code("extras_max_upload");
	$oField->base_type = "Number";
	$oField->default_value = new ffData($file_properties_thumb["max_upload"], "Number");
	$oField->setWidthComponent(3);
	$oRecord->addContent($oField, "SettingsFile");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "allowed_ext";
	$oField->label = ffTemplate::_get_word_by_code("extras_allowed_ext");
	$oField->default_value = new ffData($file_properties_thumb["allowed_ext"], "Text");
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField, "SettingsFile");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "max_items";
	$oField->label = ffTemplate::_get_word_by_code("extras_max_items");
	$oField->base_type = "Number";
	$oField->default_value = new ffData($file_properties_thumb["max_items"], "Number");
	$oField->setWidthComponent(3);
	$oRecord->addContent($oField, "SettingsFile");
    }
}

$cm->oPage->addContent($oRecord);
/*
$tpl = ffTemplate::factory($cm->oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/contents/admin/extras");
$tpl->load_file("preview.html", "main");

$cm->oPage->addContent($tpl);*/

function ExtrasModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) {
		$arrOnField = null;

		switch ($action) {
		    case "insert":
			$sSQL = "DELETE FROM settings_thumb 
	                        WHERE settings_thumb.ID <> " . $db->toSql($component->key_fields["ID"]->value) . "
	                            AND tbl_src = " . $db->toSql($component->form_fields["tblsrc"]->value) . "
	                            AND items = " . $db->toSql($component->form_fields["items"]->value) . "
	                            AND ID_layout = " . $db->toSql($component->form_fields["layout"]->value);
			$db->execute($sSQL);
		    case "update":
			if (isset($component->form_fields["thumb_grid"]))
			    $arrOnField[] = ($component->form_fields["thumb_grid"]->getValue() ? floor(12 / $component->form_fields["thumb_grid"]->getValue()) : 0);
			if (isset($component->form_fields["thumb_grid_md"]))
			    $arrOnField[] = ($component->form_fields["thumb_grid_md"]->getValue() ? floor(12 / $component->form_fields["thumb_grid_md"]->getValue()) : 0);
			if (isset($component->form_fields["thumb_grid_sm"]))
			    $arrOnField[] = ($component->form_fields["thumb_grid_sm"]->getValue() ? floor(12 / $component->form_fields["thumb_grid_sm"]->getValue()) : 0);
			if (isset($component->form_fields["thumb_grid_xs"]))
			    $arrOnField[] = ($component->form_fields["thumb_grid_xs"]->getValue() ? floor(12 / $component->form_fields["thumb_grid_xs"]->getValue()) : 0);

			if (count($arrOnField) < 4)
			    $arrOnField = array_merge($arrOnField, array_fill(count($arrOnField), 4 - count($arrOnField), $arrOnField[count($arrOnField) - 1]));

			$arrOnField = array_reverse($arrOnField);

			if ($arrOnField) {
			    $sSQL = "UPDATE `settings_thumb`
	                            SET `thumb_item` = " . $db->toSql(implode(",", $arrOnField)) . "
	                            WHERE ID = " . $db->toSql($component->key_fields["ID"]->value);
			    $db->execute($sSQL);
			}

			break;
		    default:
		}

		if (check_function("refresh_cache")) {
		    $tblsrc = (isset($component->form_fields["tblsrc"]) 
	    		? $component->form_fields["tblsrc"]->getValue() 
	    		: $component->user_vars["tblsrc"]
			);
		    $ID_item = (isset($component->form_fields["items"]) 
	    		? $component->form_fields["items"]->getValue() 
	    		: $component->user_vars["ID_item"]
			);

		    switch ($tblsrc) {
				case "files":
				    $cache_type = "G";
				    break;
				case "vgallery_nodes":
				    $cache_type = "V";
				    break;
				case "publishing":
				case "search":
				case "tag":
				default:
				    $cache_type = "";
		    }
			if($component->form_fields["layout"])		    
		    	$ID_block = $component->form_fields["layout"]->getValue();
		    
		    if($ID_block > 0) {
	    		refresh_cache_block($ID_block);
		    } else {
				refresh_cache($cache_type, $ID_item, "update");
			}

            ffCache::getInstance()->clear("/vg/thumbs");
		}
    }
}