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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

function process_gallery_thumb($rst, $user_path, $search_param = NULL, $souce_user_path = NULL, $publishing = NULL, &$layout, $tpl_data = null, $is_owner = null) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    $enable_error = false;

    check_function("normalize_url");

    $db = ffDB_Sql::factory();

    $layout["unic_id"] = $layout["prefix"] . $layout["ID"] . "T";
    $layout_settings = $layout["settings"];

    $unic_id_lower = strtolower($layout["unic_id"]);
    $layout_settings = $layout["settings"];

//    $gallery_prefix = "frmThumb" . $unic_id . "_";
    $enable_title = (isset($layout_settings["AREA_VGALLERY_RELFILES_SHOW_TITLE"]) ? $layout_settings["AREA_VGALLERY_RELFILES_SHOW_TITLE"] : $layout_settings["AREA_VGALLERY_LIST_SHOW_TITLE"]);
    //da togliere

    $enable_date = (isset($layout_settings["AREA_THUMB_SHOW_FILETIME"]) ? $layout_settings["AREA_THUMB_SHOW_FILETIME"] : false);

    $father_name = preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"]) . "_title";

    if (is_array($publishing) && count($publishing) > 0) {
	$ID_publish = $publishing["ID"];

	$sSQL = "SELECT * FROM publishing WHERE ID = " . $db->toSql(new ffData($ID_publish, "Number"));
	$db->query($sSQL);
	if ($db->nextRecord()) {
	    if ($rst === NULL)
		$rst = array();
	    $publishing_user_path = "/" . $db->getField("name")->getValue();
	    $father_name = $db->getField("name")->getValue() . "_title";
	    $publish_limit = $db->getField("limit")->getValue();
	    $publish_random = $db->getField("random")->getValue();

	    $publish_relative_path = ($db->getField("contest")->getValue() == "files" ? ($db->getField("relative_path")->getValue() ? $db->getField("relative_path")->getValue() : "/") : $db->getField("contest")->getValue()
		    );

	    if ($user_path === NULL)
		$user_path = $publish_relative_path;
	    /*
	      $publish_hide_dir = $db->getField("hide_dir", "Number", true);
	      $enable_title = $db->getField("show_title")->getValue();
	      $enable_description = $db->getField("show_description")->getValue();
	      $enable_date = $db->getField("show_date")->getValue();
	     */
	    $SQL_criteria = "";

	    $sSQL = "SELECT * FROM publishing_criteria WHERE ID_publishing = " . $db->toSql($ID_publish, "Number");
	    $db->query($sSQL);
	    if ($db->nextRecord()) {
		do {
		    if (substr($db->getField("value")->getValue(), 0, 1) === "[" && substr($db->getField("value")->getValue(), -1, 1) === "]") {
			$critetia_value = substr($db->getField("value")->getValue(), 1, -1);
			$critetia_value_encloser = false;
		    } else {
			$critetia_value = $db->getField("value")->getValue();
			$critetia_value_encloser = true;
		    }


		    $SQL_criteria .= " AND ";
		    $SQL_criteria .= "
                                        " . $db->getField("src_fields")->getValue() . " " . $db->getField("operator")->getValue() . " " . $db->toSql($critetia_value, "Text", $critetia_value_encloser) . "
                                    ";
		} while ($db->nextRecord());
	    }

	    if ($publish_random) {
		$sSQL_rst = "SELECT RAND() AS rnd, tblrnd.* 
                        FROM ( ";
	    } else {
		$sSQL_rst = "";
	    }

	    $sSQL_rst .= " SELECT DISTINCT tblsrc.*
                    FROM ( ";

	    if ($publish_random == "full") {
		$sSQL_rst .= "
                          (
                            SELECT files.*, CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
                            FROM files
                            WHERE
                                (
                                    SELECT count(*) FROM files AS count_files WHERE count_files.parent = CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name)
                                ) <= 0
                          )";
	    } else {

		$sSQL_rst .= "
                        (
                             SELECT files.*, 'Manual' AS data_type_publish
                             FROM files
                                INNER JOIN rel_nodes ON (
                                    rel_nodes.ID_node_src = files.ID 
                                    AND rel_nodes.contest_src = " . $db->toSql("files", "Text") . "
                                    AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publish, "Number") . "
                                    AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . "
                                )
                             WHERE 
                                ( 
                                    1 
                                        AND (
                                                (   
                                                    rel_nodes.date_begin = '0000-00-00'
                                                    OR  rel_nodes.date_begin >= CURDATE() 
                                                )
                                            AND 
                                                (
                                                    rel_nodes.date_end = '0000-00-00'
                                                    OR  rel_nodes.date_end < CURDATE() 
                                                )
                                            )
                                )
                        )
                        ";
		if ($SQL_criteria) {
		    $sSQL_rst .= " UNION ";
		    $sSQL_rst .= " 
                            (
                                SELECT
                                    files.*, 'Automatic' AS data_type_publish 
                                FROM files 
                                    INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.code = " . $db->toSql(new ffData(LANGUAGE_INSET)) . "
                                    LEFT JOIN files_description ON 
                                        files_description.ID_files = files.ID
                                        AND files_description.ID_languages = " . FF_PREFIX . "languages.ID 
                                    LEFT JOIN files_rel_languages ON 
                                        files_rel_languages.ID_files = files.ID
                                        AND files_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID 
                                WHERE
                                    1
                                    " . $SQL_criteria . "
                            )
                              ";
		}
	    }

	    if ($publish_random) {
		$sSQL_rst .= " ) AS tblsrc 
                            WHERE tblsrc.parent LIKE '" . $db->toSql($publish_relative_path, "Text", false) . "%'
	                            " . ($publish_hide_dir ? " AND NOT(files.is_dir > 0) " : ""
			) . "
                            ";

		$sSQL_rst .= " ) AS tblrnd
                            ORDER BY rnd
                            " . ($publish_limit ? " LIMIT " . $publish_limit : "");
	    } else {
		$sSQL_rst .= " ) AS tblsrc
                            WHERE tblsrc.parent LIKE '" . $db->toSql($publish_relative_path, "Text", false) . "%' 
	                            " . ($publish_hide_dir ? " AND NOT(tblsrc.is_dir > 0) " : ""
			) . "
                            ORDER BY tblsrc.ID DESC, tblsrc.parent DESC, tblsrc.name DESC
                            " . ($publish_limit ? " LIMIT " . $publish_limit : "");
	    }

	    $db->query($sSQL_rst);
	    if ($db->nextRecord()) {
		do {
		    $file = stripslash($db->getField("parent")->getValue()) . "/" . $db->getField("name")->getValue();
		    $real_file = DISK_UPDIR . $file;
		    if ((is_dir($real_file) && basename($real_file) != CM_SHOWFILES_THUMB_PATH /* && basename($real_file) != GALLERY_TPL_PATH */) || (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false) && strpos(basename($real_file), ".") !== 0) {
			$rst[$file]["ID_node"] = $db->getField("ID")->getValue();
			$rst[$file]["data_type_publish"] = $db->getField("data_type_publish")->getValue();

			$rst_key[] = $rst[$file]["ID_node"];
		    }
		} while ($db->nextRecord());

		if (is_array($rst) && count($rst)) {
		    if ((is_array($rst_key) && count($rst_key)))
			$rst_key = null;

		    if (ENABLE_STD_PERMISSION && check_function("get_file_permission"))
			get_file_permission(null, "files", $rst_key);

		    foreach ($rst AS $file => $file_value) {
			if (ENABLE_STD_PERMISSION && check_function("get_file_permission"))
			    $file_permission = get_file_permission($file, "files");

			if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
			    $rst[$file]["permission"] = $file_permission;
			} else {
			    unset($rst[$file]);
			}
		    } reset($rst);
		}
	    }
	}
    }

    if ($search_param !== NULL) {
	$settings_thumb_path = VG_SITE_SEARCH . "/" . "files";
	$settings_thumb_type = basename(VG_SITE_SEARCH);
    } elseif ($publishing !== NULL) {
	$settings_thumb_path = $publishing_user_path;
	$settings_thumb_type = "publishing";
    } else {
	$settings_thumb_path = $user_path;
	$settings_thumb_type = "files";
	if (!is_array($globals->seo) && !isset($globals->seo["media"])) {
	    $sSQL = "SELECT
	                    files.*
	                    , files_rel_languages.alias AS alias
	                    , files_rel_languages.description AS description
	                    , files_rel_languages.alt_url AS alt_url
	                 FROM files
	                    INNER JOIN files_rel_languages ON files.ID = files_rel_languages.ID_files
                    		AND files_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
	                 WHERE
	                    files.parent =  " . $db->toSql(ffCommon_dirname($user_path), "Text") . "
	                    AND files.name =  " . $db->toSql(basename($user_path), "Text");
	    $db->query($sSQL);
	    if ($db->nextRecord()) {
		$globals->seo["media"]["ID"] = $db->getField("ID", "Number", true);
		$globals->seo["media"]["title"] = $db->getField("alias", "Text", true);
		$globals->seo["media"]["title_header"] = $db->getField("name", "Text", true);
		if (strlen($db->getField("description", "Text", true)))
		    $globals->seo["media"]["meta"]["description"][] = $db->getField("description", "Text", true);
	    }
	}
    }

    if (check_function("get_file_properties"))
	$file_properties = get_file_properties($settings_thumb_path, $settings_thumb_type, "thumb", $layout["ID"]);

    /**
     * Admin Father Bar
     */
    if (is_array($publishing) && count($publishing) > 0) {
	if (AREA_PUBLISHING_SHOW_MODIFY || AREA_PUBLISHING_SHOW_DETAIL || AREA_PUBLISHING_SHOW_DELETE) {
	    $enable_error = true;

	    $admin_menu["admin"]["unic_name"] = $layout["unic_id"];
	    $admin_menu["admin"]["title"] = $layout["title"] . ": " . $settings_thumb_path;
	    $admin_menu["admin"]["class"] = $layout["type_class"];
	    $admin_menu["admin"]["group"] = $layout["type_group"];

	    $admin_menu["admin"]["disable_huge"] = true;

	    if (AREA_PUBLISHING_SHOW_DETAIL) {
		    $admin_menu["admin"]["addnew"] = get_path_by_rule("widgets", "restricted") . "/" . ffCommon_url_rewrite(basename($settings_thumb_path)) . "/contents?keys[ID]=" . $ID_publish;
	    }
	    if (AREA_PUBLISHING_SHOW_MODIFY) {
			$admin_menu["admin"]["modify"] = get_path_by_rule("widgets", "restricted") . "?keys[ID]=" . $ID_publish;
	    }
	    if (AREA_PUBLISHING_SHOW_DELETE) {
			$admin_menu["admin"]["delete"] = ffDialog(TRUE
													, "yesno"
													, ffTemplate::_get_word_by_code("vgallery_erase_title")
													, ffTemplate::_get_word_by_code("vgallery_erase_description")
													, "--returl--"
													, get_path_by_rule("widgets", "restricted") . "/" . ffCommon_url_rewrite(basename($settings_thumb_path)) . "?keys[ID]=" . $ID_publish . "&PublishingModify_frmAction=confirmdelete"
													, get_path_by_rule("widgets", "restricted") . "/" . ffCommon_url_rewrite(basename($settings_thumb_path)) . "/dialog"
												);			
	    }
	    if (AREA_PROPERTIES_SHOW_MODIFY) {
	    	$admin_menu["admin"]["extra"] = get_path_by_rule("blocks-appearance") . "?path=" . $settings_thumb_path . "&extype=" . "publishing" . "&layout=" . $layout["ID"];
	    }
	    if (AREA_ECOMMERCE_SHOW_MODIFY) {
			$admin_menu["admin"]["ecommerce"] = "";
	    }
	    if (AREA_LAYOUT_SHOW_MODIFY) {
		$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
		$admin_menu["admin"]["layout"]["type"] = $layout["type"];
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY) {
		$admin_menu["admin"]["setting"] = ""; //"PUBLISHING";
	    }

	    $admin_menu["sys"]["path"] = $globals->user_path;
	    $admin_menu["sys"]["type"] = "admin_toolbar";
	    //$admin_menu["sys"]["ret_url"] = $ret_url;
	}

	$is_owner = false;
    } else {
	if (ENABLE_STD_PERMISSION) {
	    if (check_function("get_file_permission"))
		$father_permission = get_file_permission($user_path, "files");

	    if ($is_owner === null) {
		if ($father_permission["owner"] > 0 && ($father_permission["owner"] === get_session("UserNID")))
		    $is_owner = true;
		else
		    $is_owner = false;
	    }
	}

	if (check_mod($father_permission, 2) && (
		AREA_GALLERY_SHOW_ADDNEW || AREA_GALLERY_SHOW_MODIFY || AREA_GALLERY_SHOW_DELETE || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_LAYOUT_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY || $is_owner
		)
	) {
	    $enable_error = true;

	    $admin_menu["admin"]["unic_name"] = $layout["unic_id"] . $user_path . "-" . $is_owner;
	    if ($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
		$admin_menu["admin"]["title"] = ffTemplate::_get_word_by_code("gallery_modify_owner");
	    } else {
		$admin_menu["admin"]["title"] = $layout["title"] . ": " . $settings_thumb_path;
	    }
	    $admin_menu["admin"]["class"] = $layout["type_class"];
	    $admin_menu["admin"]["group"] = $layout["type_group"];

	    $admin_menu["admin"]["disable_huge"] = true;

	    if (AREA_GALLERY_SHOW_ADDNEW && is_dir(DISK_UPDIR . $user_path) && $search_param === null) {
		if ($file_properties["allow_insert_dir"])
		    $admin_menu["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/add/dir?path=" . urlencode($settings_thumb_path) . "&extype=" . $settings_thumb_type;
		else
		    $admin_menu["admin"]["adddir"] = "";

		if ($file_properties["allow_insert_file"])
		    $admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/add/item?path=" . urlencode($settings_thumb_path) . "&extype=" . $settings_thumb_type;
		else
		    $admin_menu["admin"]["addnew"] = "";
	    } else {
		$admin_menu["admin"]["adddir"] = "";
		$admin_menu["admin"]["addnew"] = "";
	    }
	    if ($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
		$admin_menu["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_GALLERY . "/add/item?path=" . urlencode($settings_thumb_path) . "&owner=" . get_session("UserNID") . "&extype=" . $settings_thumb_type;
	    }
	    $admin_menu["admin"]["modify"] = "";
	    $admin_menu["admin"]["delete"] = "";

	    $arrSettingThumbPath = explode("/", $settings_thumb_path);
	    if (AREA_PROPERTIES_SHOW_MODIFY) {
		$admin_menu["admin"]["extra"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/properties?path=" . urlencode("/" . $arrSettingThumbPath[1]) . "&extype=" . $settings_thumb_type . "&layout=" . $layout["ID"];
	    }
	    if (AREA_ECOMMERCE_SHOW_MODIFY && $search_param === null && ENABLE_ECOMMERCE_FILES) {
		$admin_menu["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/ecommerce?path=" . urlencode("/" . $arrSettingThumbPath[1]) . "&extype=" . $settings_thumb_type;
	    }

	    if (AREA_LAYOUT_SHOW_MODIFY && !$layout["is_rel"]) {
		$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
		$admin_menu["admin"]["layout"]["type"] = $layout["type"];
	    }
	    if (AREA_SETTINGS_SHOW_MODIFY && $search_param === null) {
		$admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
	    }

	    $admin_menu["sys"]["path"] = $globals->user_path;
	    $admin_menu["sys"]["type"] = "admin_toolbar";
	    //$admin_menu["sys"]["ret_url"] = $ret_url;
	}
    }


    /**
     * Process Block Header
     */
    if ($file_properties["container_mode"] == "HIDE" && check_function("set_template_var")) {
		$block = get_template_header($user_path, $admin_menu, $layout);

		$buffer = ($enable_error ? ffTemplate::_get_word_by_code("gallery_is_hidden_by_properties") : "");
	    return array(
			"pre" 			=> $block["tpl"]["header"]
			, "post" 		=> $block["tpl"]["footer"]
			, "content" 	=> $buffer
			, "default" 	=> $block["tpl"]["header"] . $buffer . $block["tpl"]["footer"]
		);		
    }

    $real_rec_per_page = $file_properties["rec_per_page"];
    $real_rec_per_page_all = $file_properties["rec_per_page_all"];
    $real_npage_per_frame = $file_properties["npage_per_frame"];
    $real_direction_arrow = $file_properties["direction_arrow"];
    $real_frame_arrow = $file_properties["frame_arrow"];
    $real_custom_page = $file_properties["custom_page"];
    $real_tot_elem = $file_properties["tot_elem"];
    $real_frame_per_page = $file_properties["frame_per_page"];
    $real_pagenav_location = $file_properties["pagenav_location"];

    $view_mode = $file_properties["plugin"]["name"];
    if ($view_mode)
	setJsRequest($view_mode);

    $thumb_per_row = $file_properties["item_size"][0];

    if (substr($layout["prefix"], -2, 1) == "R")
	$rel_tpl = "_rel";
    else
	$rel_tpl = "";
	
	if(!$layout["tpl_path"])
		$layout["tpl_path"] = "/tpl/gallery";

	if(isset($tpl_data["type"])) {
	    $tpl_data["custom"] = basename($settings_thumb_path) . "." . $tpl_data["type"];
	    $tpl_data["base"] = "gallery" . $rel_tpl . "." . $tpl_data["type"];
	    $tpl_data["path"] = $layout["tpl_path"];
	    $tpl_data["is_html"] = false;
	} else {
	    $tpl_data["custom"] = basename($settings_thumb_path) . $rel_tpl . ".html";
	    $tpl_data["base"] = strtolower($file_properties["container_mode"]) . $rel_tpl . ".html";
	    $tpl_data["path"] = $layout["tpl_path"];
	    $tpl_data["is_html"] = true;
	}

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");  

	/*
    if (isset($tpl_data["type"]) && $tpl_data["type"] == "xml") {
		$tpl = ffTemplate::factory(get_template_cascading($user_path, "gallery.xml", "/gallery", null, $layout["location"]));
		$tpl->load_file("gallery" . $rel_tpl . ".xml", "main");
		$tpl_data_valid = true;
    } elseif (isset($tpl_data["type"]) && $tpl_data["type"] == "json") {
		$tpl = ffTemplate::factory(get_template_cascading($user_path, "gallery.json", "/gallery", null, $layout["location"]));
		$tpl->load_file("gallery" . $rel_tpl . ".json", "main");
		$tpl_data_valid = true;
    } else {
		$tpl = ffTemplate::factory(get_template_cascading($user_path, "gallery_" . strtolower($file_properties["container_mode"]) . ".html", "/gallery", null, $layout["location"]));
		$tpl->load_file("gallery_" . strtolower($file_properties["container_mode"]) . $rel_tpl . ".html", "main");
		$tpl_data_valid = false;
    }*/

    /**
     * Process Block Header
     */
    if (check_function("set_template_var"))
	$block = get_template_header($user_path, $admin_menu, $layout, $tpl);

    $tpl->set_var("page", ffCommon_specialchars($_REQUEST[$unic_id_lower . "_page"]));
    $tpl->set_var("rec_per_page", ffCommon_specialchars($_REQUEST[$unic_id_lower . "_records_per_page"]));

    $tpl->set_var("search_param", $search_param);
    $tpl->set_var("this_url", ffCommon_specialchars($_SERVER["REQUEST_URI"]));

    /*                         Page Navigator                                */

    $page_nav = ffPageNavigator::factory($cm->oPage, FF_DISK_PATH, FF_SITE_PATH, null, $cm->oPage->theme);
    $page_nav->oPage = array(&$cm->oPage);
    $page_nav->id = $unic_id_lower;
    if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
	$page_nav->doAjax = true;
    else
	$page_nav->doAjax = false;

    $page_nav->PagePerFrame = $real_npage_per_frame;

    $page_nav->prefix = $unic_id_lower . "_";
    $page_nav->nav_selector_elements = array(floor($real_rec_per_page / 2), $real_rec_per_page, $real_rec_per_page * 2);
    $page_nav->nav_selector_elements_all = $real_rec_per_page_all;
    $page_nav->display_prev = $real_direction_arrow;
    $page_nav->display_next = $real_direction_arrow;
    $page_nav->display_first = $real_direction_arrow;
    $page_nav->display_last = $real_direction_arrow;

    $page_nav->with_frames = $real_frame_arrow;
    $page_nav->with_choice = $real_custom_page;
    $page_nav->with_totelem = $real_tot_elem;
    $page_nav->nav_display_selector = $real_frame_per_page;

    $page_nav->page = intval($_REQUEST[$page_nav->prefix . "page"]) > 0 ? $_REQUEST[$page_nav->prefix . "page"] : 1;
    $page_nav->records_per_page = intval($_REQUEST[$page_nav->prefix . "records_per_page"]) > 0 ? $_REQUEST[$page_nav->prefix . "records_per_page"] : $real_rec_per_page;

    $tot_page = ceil(count($rst) / $page_nav->records_per_page);

    if ($page_nav->page >= $tot_page)
	$page_nav->page = $tot_page;


    if ($tpl_data_valid) {
	if (isset($tpl_data["tag"]["gallery"]) && strlen($tpl_data["tag"]["gallery"])) {
	    $tpl->set_var("tag_gallery", $tpl_data["tag"]["gallery"]);
	} else {
	    $tpl->set_var("tag_gallery", "gallery");
	}
    }

    if ($tpl_data_valid) {
	if (!strlen($rel_tpl) && strlen($tpl_data["tag"]["title"])) {
	    if (isset($tpl_data["tag"]["title"]) && strlen($tpl_data["tag"]["title"])) {
		$tpl->set_var("tag_title", $tpl_data["tag"]["title"]);
	    } else {
		$tpl->set_var("tag_title", "title");
	    }
	    $tpl->parse("SezGalleryTitle", false);
	} else {
	    $tpl->set_var("SezGalleryTitle", "");
	}
    } else {
	if ($enable_title && strlen($father_name)) {
	    $tpl->set_var("name_title", ffTemplate::_get_word_by_code($father_name));

	    $tpl->parse("SezGalleryTitle", false);
	} else {
	    $tpl->set_var("SezGalleryTitle", "");
	}
    }

    $i = -1;
    $col = 0;
    $count_files = 0;

    //check extension
    if (strlen($file_properties["allowed_ext"])) {
	$arrFileExt = explode(",", $file_properties["allowed_ext"]);
    }

    foreach ($rst as $file => $value) {
	if ($file_properties["hide_dir"] && is_dir(DISK_UPDIR . $file))
	    unset($rst[$file]);

	if (is_array($arrFileExt) && count($arrFileExt)) {
	    $file_ext = ffGetFilename($file, false);
	    if (array_search($file_ext, $arrFileExt) === false)
		unset($rst[$file]);
	}
    }

    $page_nav->num_rows = count($rst);

    $rst = array_slice($rst, ($page_nav->page - 1) * $page_nav->records_per_page, $page_nav->records_per_page, true);
    reset($rst);

    $switch_style = false;
    foreach ($rst as $file => $value) {
	$col++;
	if (is_array($publishing) && count($publishing) > 0) {
	    $settings_thumb_path = $publishing_user_path;
	    $settings_thumb_type = "publishing";
	    $settings_thumb_prefix = "P0";
	} else {
	    $settings_thumb_path = ffCommon_dirname($file);
	    $settings_thumb_type = "files";
	    $settings_thumb_prefix = "T0";
	}

    set_cache_data("G", md5($file), $settings_thumb_prefix, $file);
	//$globals->cache["data_blocks"]["G" . $settings_thumb_prefix . "-" . md5($file)] = $file;

	$i++;
	$description = "";

	$count_files++;

	$real_file = DISK_UPDIR . $file;
	$file_permission = & $rst[$file]["permission"];
	$mime = ffMimeType($real_file);

	$tpl->set_var("user_path", FF_SITE_PATH . VG_SITE_GALLERYMODIFY . $file); //. "?ret_url=" . urlencode($_SERVER['REQUEST_URI']));

	$sSql = "SELECT
                    files.*
                    , files_rel_languages.alias AS alias
                    , files_rel_languages.description AS description
                    , files_rel_languages.alt_url AS alt_url
                 FROM files
                    INNER JOIN files_rel_languages ON files.ID = files_rel_languages.ID_files
                    	AND files_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                 WHERE
                    files.parent =  " . $db->toSql(ffCommon_dirname($file), "Text") . "
                    AND files.name =  " . $db->toSql(basename($file), "Text");
	$db->query($sSql);
	if ($db->nextRecord()) {
	    $name = $db->getField("name", "Text", true);
	    $alias = $db->getField("alias", "Text", true);
	    $description = $db->getField("description", "Text", true);
	    $alternative_path = $db->getField("alt_url", "Text", true);
	} else {
	    $name = "";
	    $alias = "";
	    $description = "";
	}

	if (!strlen($description) && $layout_settings["AREA_THUMB_ENABLE_CASCADING"]) {
	    if (check_function("get_gallery_information_by_lang")) {
		$description = get_gallery_information_by_lang($file, "description", $layout_settings["AREA_THUMB_ENABLE_CASCADING"]);
	    }
	}

	if (check_function("get_short_description"))
	    $res = get_short_description($description, $layout_settings["AREA_THUMB_SHOW_DESCRIPTION_LIMIT"]);
	
	$description = $res["content"];
	if (strlen($alias)) {
	    $name = $alias;
	} else {
	    if ($layout_settings["AREA_THUMB_SHOW_EXTENSION"]) {
		$name = basename($file);
	    } else {
		$name = ffGetFilename($real_file);
	    }
	}

	$tpl->set_var("show_name", ffGetFilename($file)); //variabile di ambiente per visualizzare solo il nome file

	if ($file_properties["image"]["fields"]) {
	    $image_path = $file;
	    if (is_dir(DISK_UPDIR . $file) && $layout_settings["AREA_GALLERY_ENABLE_COVER"]) {
		if (strlen($layout_settings["AREA_GALLERY_ENABLE_COVER_NAME"]) && is_file(realpath(DISK_UPDIR . stripslash($file) . "/" . $layout_settings["AREA_GALLERY_ENABLE_COVER_NAME"]))) {
		    $image_path = stripslash($file) . "/" . $layout_settings["AREA_GALLERY_ENABLE_COVER"];
		} else {
		    $tmp_img = glob(DISK_UPDIR . stripslash($file) . "/*");
		    if (is_array($tmp_img) && count($tmp_img)) {
			sort($tmp_img);
			foreach ($tmp_img AS $tmp_img_key => $tmp_img_value) {
			    if (is_file($tmp_img_value)) {
				$mime = ffMimeType(DISK_UPDIR . stripslash($file) . "/" . basename($tmp_img_value));
				if (strpos($mime, "image") !== false) {
				    $image_path = stripslash($file) . "/" . basename($tmp_img_value);
				    break;
				}
			    }
			}
		    }
		}
	    }

	    if (is_file(DISK_UPDIR . $image_path)) {
		if ($file_properties["image"]["src"]["default"]["ID"] > 0) {
		    if (check_function("get_thumb"))
				$arrThumb = get_thumb(
					$image_path
					, array(
						 "fake_name" => $alias
						, "thumb" => $file_properties["image"]["src"]
					)
					, "default"
				);	
				
		    /*
			$arrThumb = get_thumb($image_path
			    , array(
				"fake_name" => $alias
				, "mode" => $file_properties["image"]["src"]["default"]["name"]
				, "format" => $file_properties["image"]["format"]
			    )
			);*/
		    $tpl->set_var("show_thumb", $arrThumb["src"]);
		} else {
		    $tpl->set_var("show_thumb", FF_SITE_PATH . constant("CM_SHOWFILES") . $image_path);
		}
	    } else {
			$tpl->set_var("show_thumb", FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . CM_DEFAULT_THEME . "/images/spacer.gif");
	    }

	    $tpl->set_var("alt_name", ffCommon_specialchars($name));
	    if (strlen($description)) {
		$tpl->set_var("title_thumb", trim(strip_tags($description)));
	    } else {
		$tpl->set_var("title_thumb", "");
	    }
	    // else 
	    //     $tpl->set_var("title_thumb", ffCommon_specialchars($name));

	    $tpl->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"] . ffGetFilename($real_file)) . "_image");

	    if (is_dir($real_file)) {
		if ($search_param === NULL) {

		    $tpl->set_var("target", "");
		    if ($alternative_path) {
			if (
				substr($alternative_path, 0, 1) != "/"
			) {
			    $tpl->set_var("show_file", $alternative_path);
			    if (
				    substr(strtolower($alternative_path), 0, 7) == "http://" || substr(strtolower($alternative_path), 0, 8) == "https://" || substr($alternative_path, 0, 2) == "//"
			    ) {
				$tpl->set_var("target", " target=\"_blank\" ");
			    } else {
				$tpl->set_var("target", "");
			    }
			} else {
			    if (strpos($alternative_path, "#") !== false) {
				$part_alternative_hash = substr($alternative_path, strpos($alternative_path, "#"));
				$alternative_path = substr($alternative_path, 0, strpos($alternative_path, "#"));
			    }

			    if (strpos($alternative_path, "?") !== false) {
				$part_alternative_path = substr($alternative_path, 0, strpos($alternative_path, "?"));
				$part_alternative_url = substr($alternative_path, strpos($alternative_path, "?")); // . ($search_param ? "&ret_url=" . urlencode($ret_url) : "");
			    } else {
				$part_alternative_path = $alternative_path;
				$part_alternative_url = ""; //($search_param ? "?ret_url=" . urlencode($ret_url) : "");
			    }
			    if (check_function("get_international_settings_path")) {
				$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
				$tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
			    }
			}
		    } else {
			if ($souce_user_path === NULL) {
			    $tpl->set_var("show_file", normalize_url_by_current_lang($file));
			} else {
			    $tpl->set_var("show_file", normalize_url_by_current_lang($souce_user_path . "/" . basename($file)));
			}
		    }
		    $tpl->set_var("rel_plugin", "");
		    $tpl->set_var("class_plugin", "image");

		    $tpl->parse("SezGalleryImageViewLink", false);
		    $tpl->set_var("SezGalleryImageViewNoLink", "");
		} else {
		    $tpl->set_var("SezGalleryImageViewLink", "");
		    $tpl->parse("SezGalleryImageViewNoLink", false);
		}
	    } elseif (is_file($real_file)) {
		if ($file_properties["image"]["link_to"] == "image") {
		    $tpl->set_var("target", "");
		    if ($alternative_path) {
			if (
				substr($alternative_path, 0, 1) != "/"
			) {
			    $tpl->set_var("show_file", $alternative_path);
			    if (
				    substr(strtolower($alternative_path), 0, 7) == "http://" || substr(strtolower($alternative_path), 0, 8) == "https://" || substr($alternative_path, 0, 2) == "//"
			    ) {
				$tpl->set_var("target", " target=\"_blank\" ");
			    } else {
				$tpl->set_var("target", "");
			    }
			} else {
			    if (strpos($alternative_path, "#") !== false) {
				$part_alternative_hash = substr($alternative_path, strpos($alternative_path, "#"));
				$alternative_path = substr($alternative_path, 0, strpos($alternative_path, "#"));
			    }

			    if (strpos($alternative_path, "?") !== false) {
				$part_alternative_path = substr($alternative_path, 0, strpos($alternative_path, "?"));
				$part_alternative_url = substr($alternative_path, strpos($alternative_path, "?")); // . ($search_param ? "&ret_url=" . urlencode($ret_url) : "");
			    } else {
				$part_alternative_path = $alternative_path;
				$part_alternative_url = ""; //($search_param ? "?ret_url=" . urlencode($ret_url) : "");
			    }
			    if (check_function("get_international_settings_path")) {
				$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
				$tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
			    }
			}
		    } else {
			$tpl->set_var("show_file", CM_SHOWFILES . $file);
		    }
		    if ($view_mode) {
			$tpl->set_var("rel_plugin", "gallery[" . strtolower(ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"] . basename($user_path)))) . "]");
			$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $view_mode));
		    } else {
			$tpl->set_var("rel_plugin", "");
			$tpl->set_var("class_plugin", "image");
		    }
		    $tpl->parse("SezGalleryImageViewLink", false);
		    $tpl->set_var("SezGalleryImageViewNoLink", "");
		} elseif ($file_properties["image"]["link_to"] == "content") {
		    if ($view_mode) {
			$frame["sys"]["layouts"] = preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"]);
			$serial_frame = json_encode($frame);

			if ($source_user_path === NULL) {
			    $tpl->set_var("show_file", FF_SITE_PATH . VG_SITE_FRAME . $file . "?sid=" . set_sid($serial_frame));
			} else {
			    $tpl->set_var("show_file", FF_SITE_PATH . VG_SITE_FRAME . stripslash($source_user_path) . "/" . basename($file) . "?sid=" . set_sid($serial_frame));
			}
			$tpl->set_var("rel_plugin", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"])));
			$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $view_mode));
		    } else {
			$tpl->set_var("target", "");
			if ($alternative_path) {
			    if (
				    substr($alternative_path, 0, 1) != "/"
			    ) {
				$tpl->set_var("show_file", $alternative_path);
				if (
					substr(strtolower($alternative_path), 0, 7) == "http://" || substr(strtolower($alternative_path), 0, 8) == "https://" || substr($alternative_path, 0, 2) == "//"
				) {
				    $tpl->set_var("target", " target=\"_blank\" ");
				} else {
				    $tpl->set_var("target", "");
				}
			    } else {
				if (strpos($alternative_path, "#") !== false) {
				    $part_alternative_hash = substr($alternative_path, strpos($alternative_path, "#"));
				    $alternative_path = substr($alternative_path, 0, strpos($alternative_path, "#"));
				}

				if (strpos($alternative_path, "?") !== false) {
				    $part_alternative_path = substr($alternative_path, 0, strpos($alternative_path, "?"));
				    $part_alternative_url = substr($alternative_path, strpos($alternative_path, "?")); // . ($search_param ? "&ret_url=" . urlencode($ret_url) : "");
				} else {
				    $part_alternative_path = $alternative_path;
				    $part_alternative_url = ""; //($search_param ? "?ret_url=" . urlencode($ret_url) : "");
				}
				if (check_function("get_international_settings_path")) {
				    $res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
				    $tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
				}
			    }
			} else {
			    if ($souce_user_path === NULL) {
				$tpl->set_var("show_file", normalize_url_by_current_lang($file));
			    } else {
				$tpl->set_var("show_file", normalize_url_by_current_lang($souce_user_path . "/" . basename($file)));
			    }
			}
			$tpl->set_var("rel_plugin", "");
			$tpl->set_var("class_plugin", "image");
		    }
		    $tpl->parse("SezGalleryImageViewLink", false);
		    $tpl->set_var("SezGalleryImageViewNoLink", "");
		} else {
		    $tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $view_mode));
		    $tpl->set_var("SezGalleryImageViewLink", "");
		    $tpl->parse("SezGalleryImageViewNoLink", false);
		}
	    }

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_image"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_image"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_image", $tpl_data["tag" . $rel_tpl]["gallery_row_image"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_image", "image");
		}
	    }

	    $tpl->parse("SezGalleryImage", false);
	} else {
	    $is_linked = false;
	    if (is_dir($real_file)) {
		$tpl->set_var("target", "");
		if ($alternative_path) {
		    if (
			    substr($alternative_path, 0, 1) != "/"
		    ) {
			$tpl->set_var("show_file", $alternative_path);
			if (
				substr(strtolower($alternative_path), 0, 7) == "http://" || substr(strtolower($alternative_path), 0, 8) == "https://" || substr($alternative_path, 0, 2) == "//"
			) {
			    $tpl->set_var("target", " target=\"_blank\" ");
			} else {
			    $tpl->set_var("target", "");
			}
		    } else {
			if (strpos($alternative_path, "#") !== false) {
			    $part_alternative_hash = substr($alternative_path, strpos($alternative_path, "#"));
			    $alternative_path = substr($alternative_path, 0, strpos($alternative_path, "#"));
			}

			if (strpos($alternative_path, "?") !== false) {
			    $part_alternative_path = substr($alternative_path, 0, strpos($alternative_path, "?"));
			    $part_alternative_url = substr($alternative_path, strpos($alternative_path, "?")); // . ($search_param ? "&ret_url=" . urlencode($ret_url) : "");
			} else {
			    $part_alternative_path = $alternative_path;
			    $part_alternative_url = ""; //($search_param ? "?ret_url=" . urlencode($ret_url) : "");
			}
			if (check_function("get_international_settings_path")) {
			    $res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
			    $tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
			}
		    }
		} else {
		    if ($souce_user_path === NULL) {
			$tpl->set_var("show_file", normalize_url_by_current_lang($file));
		    } else {
			$tpl->set_var("show_file", normalize_url_by_current_lang($souce_user_path . "/" . basename($file)));
		    }
		}
		$is_linked = true;
	    } elseif (is_file($real_file)) {
		if ($file_properties["image"]["link_to"] == "image") {
		    $tpl->set_var("show_file", CM_SHOWFILES . $file);
		    $is_linked = true;
		} elseif ($file_properties["image"]["link_to"] == "content") {
		    $tpl->set_var("target", "");
		    if ($alternative_path) {
			if (
				substr($alternative_path, 0, 1) != "/"
			) {
			    $tpl->set_var("show_file", $alternative_path);
			    if (
				    substr(strtolower($alternative_path), 0, 7) == "http://" || substr(strtolower($alternative_path), 0, 8) == "https://" || substr($alternative_path, 0, 2) == "//"
			    ) {
				$tpl->set_var("target", " target=\"_blank\" ");
			    } else {
				$tpl->set_var("target", "");
			    }
			} else {
			    if (strpos($alternative_path, "#") !== false) {
				$part_alternative_hash = substr($alternative_path, strpos($alternative_path, "#"));
				$alternative_path = substr($alternative_path, 0, strpos($alternative_path, "#"));
			    }

			    if (strpos($alternative_path, "?") !== false) {
				$part_alternative_path = substr($alternative_path, 0, strpos($alternative_path, "?"));
				$part_alternative_url = substr($alternative_path, strpos($alternative_path, "?")); // . ($search_param ? "&ret_url=" . urlencode($ret_url) : "");
			    } else {
				$part_alternative_path = $alternative_path;
				$part_alternative_url = ""; //($search_param ? "?ret_url=" . urlencode($ret_url) : "");
			    }
			    if (check_function("get_international_settings_path")) {
				$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
				$tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
			    }
			}
		    } else {
			if ($souce_user_path === NULL) {
			    $tpl->set_var("show_file", normalize_url_by_current_lang($file));
			} else {
			    $tpl->set_var("show_file", normalize_url_by_current_lang($souce_user_path . "/" . basename($file)));
			}
		    }
		    $is_linked = true;
		}
	    }
	    $tpl->set_var("SezGalleryImage", "");
	}

	$count_desc = 0;
	if ($layout_settings["AREA_THUMB_SHOW_NAME"]) {
	    if ($search_param)
		$tpl->set_var("name", preg_replace("/(" . preg_quote($search_param) . ")/i", "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars($name)));
	    else
		$tpl->set_var("name", ffCommon_specialchars($name));

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "name");
		}
	    }
	    if ($is_linked) {
		$tpl->parse("SezGalleryWordNameLink", false);
		$tpl->set_var("SezGalleryWordName", "");
	    } else {
		$tpl->set_var("SezGalleryWordNameLink", "");
		$tpl->parse("SezGalleryWordName", false);
	    }
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordNameLink", "");
	    $tpl->set_var("SezGalleryWordName", "");
	}
	if ($layout_settings["AREA_THUMB_SHOW_TYPE"]) {  //Variabile d'ambiente Thumb
	    if (is_file($real_file)) {
		if ($search_param)
		    $tpl->set_var("type", preg_replace("/(" . preg_quote($search_param) . ")/i", "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars($mime)));
		else
		    $tpl->set_var("type", ffCommon_specialchars($mime));
	    } else {
		$tpl->set_var("type", ffTemplate::_get_word_by_code("type_directory"));
	    }

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "type");
		}
	    }
	    $tpl->parse("SezGalleryWordType", false);
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordType", "");
	}
	if ($layout_settings["AREA_THUMB_SHOW_SIZE"] && !is_dir($real_file)) {  //Variabile d'ambiente Thumb
		check_function("get_literal_size");

	    if ($search_param)
			$tpl->set_var("size", preg_replace("/(" . preg_quote($search_param) . ")/i", "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars(get_literal_size(filesize($real_file)))));
	    else
			$tpl->set_var("size", get_literal_size(filesize($real_file)));

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "size");
		}
	    }
	    $tpl->parse("SezGalleryWordSize", false);
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordSize", "");
	}
	if ($layout_settings["AREA_THUMB_SHOW_PATH"]) {  //Variabile d'ambiente Thumb
	    if ($search_param)
		$tpl->set_var("path", preg_replace("/(" . preg_quote($search_param) . ")/i", "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars(ffCommon_dirname($file))));
	    else
		$tpl->set_var("path", ffCommon_specialchars(ffCommon_dirname($file)));

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "path");
		}
	    }
	    $tpl->parse("SezGalleryWordPath", false);
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordPath", "");
	}
	if ($layout_settings["AREA_THUMB_SHOW_DESCRIPTION"] && (strlen(trim(strip_tags($description))) || strpos($description, "<img") !== false)) {
	    if ($search_param)
		$tpl->set_var("description", preg_replace("/(" . preg_quote($search_param) . ")/i", "<strong class=\"theone\">\${1}</strong>", $description));
	    else
		$tpl->set_var("description", $description);

	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "description");
		}
	    }
	    $tpl->parse("SezGalleryWordDescription", false);
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordDescription", "");
	}

	if ($enable_date) {  //Variabile d'ambiente Thumb
	    $file_time = new ffData(filemtime($real_file), "Timestamp", "ISO9075");
	    $file_time = $file_time->getValue("DateTime", LANGUAGE_INSET);
	    $tpl->set_var("filetime", $file_time ? ffCommon_specialchars($file_time) : ffTemplate::_get_word_by_code("system_unknow"));
	    if ($tpl_data_valid) {
		if (isset($tpl_data["tag" . $rel_tpl]["gallery_row_field"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row_field"])) {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", $tpl_data["tag" . $rel_tpl]["gallery_row_field"]);
		} else {
		    $tpl->set_var("tag" . $rel_tpl . "_gallery_row_field", "filetime");
		}
	    }
	    $tpl->parse("SezGalleryWordFileTime", false);
	    $count_desc++;
	} else {
	    $tpl->set_var("SezGalleryWordFileTime", "");
	}

	if (ENABLE_ECOMMERCE_FILES && is_file($real_file) && !$tpl_data_valid) {
	    if (check_function("process_addon_ecommerce_cart"))
		$tpl->set_var("ecommerce_cart", process_addon_ecommerce_cart($layout, $file, $count_files, "files", true, NULL, NULL, $user_path, "", null, null, null));

	    $count_desc++;
	}

	if ($count_desc) {
	    $tpl->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"] . ffGetFilename($real_file)) . "_description");
	    if ($view_mode)
		$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $view_mode));

	    $tpl->parse("SezGalleryWord", false);
	}

	if ($publishing === NULL) {
	    if (
		    (
		    check_mod($file_permission, 2) && (
		    AREA_GALLERY_SHOW_ADDNEW || AREA_GALLERY_SHOW_MODIFY || AREA_GALLERY_SHOW_DELETE || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_LAYOUT_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY
		    )
		    ) || $is_owner
	    ) {
		$popup["admin"]["unic_name"] = $layout["unic_id"] . $file . "-" . $is_owner;
		if ($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
		    $popup["admin"]["title"] = ffTemplate::_get_word_by_code("gallery_modify_owner");
		} else {
		    $popup["admin"]["title"] = $layout["title"] . ": " . $file;
		}
		$popup["admin"]["class"] = $layout["type_class"];
		$popup["admin"]["group"] = $layout["type_group"];
		/*                if(AREA_GALLERY_SHOW_ADDNEW && is_dir($real_file)) {
		  $popup["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/add/dir?path=" . urlencode($file);
		  $popup["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/add/item?path=" . urlencode($file);
		  } else {
		  $popup["admin"]["adddir"] = "";
		  $popup["admin"]["addnew"] = "";
		  }
		 */
		$popup["admin"]["disable_huge"] = true;

		if ($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
		    $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_GALLERY . "/modify?path=" . urlencode($file) . "&extype=files&owner=" . get_session("UserNID");
		    $popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_GALLERY . "/modify?path=" . urlencode($file) . "&extype=files&owner=" . get_session("UserNID") . "&ret_url=" . "--encodereturl--" . "&GalleryModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_GALLERY . "/dialog");
		} else {
		    if (AREA_GALLERY_SHOW_MODIFY) {
			$popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/modify?path=" . urlencode($file) . "&extype=files";
		    }
		    if (AREA_GALLERY_SHOW_DELETE) {
			$popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/modify?path=" . urlencode($file) . "&extype=files&ret_url=" . "--encodereturl--" . "&GalleryModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/dialog");
		    }
		}
		if (AREA_PROPERTIES_SHOW_MODIFY) {
		    // $popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/properties?path=" . urlencode($file) . "&extype=files" . "&layout=" . $layout["ID"];
		}
		if (AREA_ECOMMERCE_SHOW_MODIFY && ENABLE_ECOMMERCE_FILES) {
		    $popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/ecommerce?path=" . urlencode($file) . "&extype=files";
		}
		if (AREA_SETTINGS_SHOW_MODIFY) {
		    $popup["admin"]["setting"] = "";
		}

		$popup["sys"]["path"] = $globals->user_path;
		$popup["sys"]["type"] = "admin_popup";
	    }
	} elseif (is_array($publishing)) {
	    if (check_mod($file_permission, 2)) {
		$popup["admin"]["unic_name"] = $layout["unic_id"] . $file;
		$popup["admin"]["title"] = $layout["title"] . ": " . $file;
		$popup["admin"]["class"] = $layout["type_class"];
		$popup["admin"]["group"] = $layout["type_group"];
		$popup["admin"]["addnew"] = "";

		if (AREA_GALLERY_SHOW_MODIFY) {
		    $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/modify?path=" . urlencode($file) . "&extype=files";
		}
		if (AREA_GALLERY_SHOW_DELETE) {
		    $popup["admin"]["delete"] = ffDialog(TRUE, "yesno", ffTemplate::_get_word_by_code("vgallery_erase_title"), ffTemplate::_get_word_by_code("vgallery_erase_description"), "--returl--", FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/modify?path=" . urlencode($file) . "&extype=files&ret_url=" . "--encodereturl--" . "&GalleryModify_frmAction=confirmdelete", FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/dialog");
		}
		if (AREA_PROPERTIES_SHOW_MODIFY) {
		    // $popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/properties?path=" . urlencode($file) . "&extype=files" . "&layout=" . $layout["ID"];
		}
		if (AREA_ECOMMERCE_SHOW_MODIFY && ENABLE_ECOMMERCE_FILES) {
		    $popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_GALLERYMODIFY . "/ecommerce?path=" . urlencode($file) . "&extype=files";
		}
		if (AREA_SETTINGS_SHOW_MODIFY) {
		    $popup["admin"]["setting"] = "";
		}

		$popup["sys"]["path"] = $globals->user_path;
		$popup["sys"]["type"] = "admin_popup";

	    }
	} else {
	    $tpl->set_var("SezGalleryEdit", "");
	    $strError = ffTemplate::_get_word_by_code("error_system_abnormal_params");
	}

	$item_class = array();
	if ($popup) {
		if(check_function("set_template_var"))
			$tpl->set_var("admin", ' data-admin="' . get_admin_bar($popup, VG_SITE_FRAME . $globals->user_path) . '"');

	    //$serial_popup = json_encode($popup);
	    //$tpl->set_var("admin", ' data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $globals->user_path . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"');
	    
	    $item_class["admin"] = "admin-bar";
	}
	if (is_array($file_properties["default_grid"]) && count($file_properties["default_grid"]))
	    $item_class["grid"] = cm_getClassByFrameworkCss($file_properties["default_grid"], "col");
	$item_class["base"] = "gallery_col" . $col;
	$tpl->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"] . ffGetFilename($real_file)));
	$tpl->set_var("class_col", implode(" ", $item_class));

	if ($tpl_data_valid) {
	    if (isset($tpl_data["tag" . $rel_tpl]["gallery_row"]) && strlen($tpl_data["tag" . $rel_tpl]["gallery_row"])) {
		$tpl->set_var("tag" . $rel_tpl . "_gallery_row", $tpl_data["tag" . $rel_tpl]["gallery_row"]);
	    } else {
		$tpl->set_var("tag" . $rel_tpl . "_gallery_row", "item");
	    }
	}

	$tpl->set_var("g_switch_style", ($switch_style ? "positive" : "negative"
		)
	);
	$tpl->parse("SezGallery", true);
	if (is_int($count_files / $thumb_per_row)) {
	    $col = 0;

	    if ($switch_style)
		$switch_style = false;
	    else
		$switch_style = true;

	    $tpl->parse("SezGalleryRow", true);
	    $tpl->set_var("SezGallery", "");
	}
    }

    if ($count_files && !is_int($count_files / $thumb_per_row))
	$tpl->parse("SezGalleryRow", true);

    /*
      if(!is_int($count_files / $thumb_per_row)) {
      $col = 0;

      $tpl->set_var("g_switch_style", ($switch_style
      ? "positive"
      : "negative"
      )
      );

      $tpl->parse("SezGalleryRow", true);
      } */

    /*                         Page Navigator                                */
    if ($tot_page > 1 || $search_param !== NULL) {
	if ($tot_page > 1) {
	    $tpl->set_var("PageNavigator", $page_nav->process(false));
	    if (strtolower($real_pagenav_location) == "top" || strtolower($real_pagenav_location) == "both") {
		$tpl->parse("SezPageNavigatorTop", false);
	    }
	    if ($real_pagenav_location == "" || strtolower($real_pagenav_location) == "bottom" || strtolower($real_pagenav_location) == "both") {
		$tpl->parse("SezPageNavigatorBottom", false);
	    }
	    $tpl->parse("SezPageNavigatorControl", false);
	    $tpl->parse("SezPageNavigator", false);
	} else {
	    $tpl->set_var("SezPageNavigatorControl", "");
	    $tpl->set_var("SezPageNavigator", "");
	}

	if ($search_param === NULL) {
	    $tpl->set_var("SezSearchControl", "");
	} else {
	    $tpl->parse("SezSearchControl", false);
	}
	$tpl->parse("SezControls", false);
    } else {
	$tpl->set_var("SezControls", "");
    }
    /*                         Page Navigator                                */

    if ($count_files) {
	if ($tpl_data_valid) {
	    if (isset($tpl_data["tag" . $rel_tpl]["gallerys"]) && strlen($tpl_data["tag" . $rel_tpl]["gallerys"])) {
		$tpl->set_var("tag" . $rel_tpl . "_gallerys", $tpl_data["tag" . $rel_tpl]["gallerys"]);
	    } else {
		$tpl->set_var("tag" . $rel_tpl . "_gallerys", "data");
	    }
	}

	$tpl->parse("SezGallerys", false);
    } else {
	if (strlen($layout_settings["AREA_GALLERY_PRELOAD_IMAGE"])) {
	    $arrPreloadImage = explode(",", $layout_settings["AREA_GALLERY_PRELOAD_IMAGE"]);
	    if (is_array($arrPreloadImage) && count($arrPreloadImage)) {
		foreach ($arrPreloadImage AS $file) {
		    $count_files++;

		    $tpl->set_var("show_name", ffGetFilename($file));

		    if (
			    substr(strtolower($file), 0, 7) == "http://" || substr(strtolower($file), 0, 8) == "https://" || substr($file, 0, 2) == "//"
		    ) {
			$tpl->set_var("show_thumb", $file);
		    } else {
			$tpl->set_var("show_thumb", FF_SITE_PATH . constant("CM_SHOWFILES") . $file);
		    }

		    $tpl->set_var("alt_name", basename($file));
		    $tpl->set_var("title_thumb", "");

		    $tpl->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $layout["unic_id"] . ffGetFilename($file)) . "_image");

		    $tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $view_mode));
		    $tpl->set_var("SezGalleryImageViewLink", "");
		    $tpl->parse("SezGalleryImageViewNoLink", false);

		    $tpl->parse("SezGalleryImage", false);

		    $tpl->set_var("g_switch_style", ($switch_style ? "positive" : "negative"
			    )
		    );
		    $tpl->parse("SezGallery", true);
		}
		$tpl->parse("SezGalleryRow", true);
		$tpl->parse("SezGallerys", false);
	    }
	}

	if (!$count_files) {
	    $strError = ffTemplate::_get_word_by_code("error_thumb_nofilematch");
	    $tpl->set_var("SezGallerys", "");
	}
    }

    if (strlen($strError) && $enable_error) {
	$tpl->set_var("strError", ffCommon_specialchars($strError));
	$tpl->parse("SezError", false);
    } else {
	$tpl->set_var("SezError", "");
    }

    if (!$count_files && !$enable_error) {
		$buffer = "";
    } else {
		$buffer = $tpl->rpparse("main", false);
    }

	return array(
		"pre" 			=> $block["tpl"]["header"]
		, "post" 		=> $block["tpl"]["footer"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["header"] . $buffer . $block["tpl"]["footer"]
	);		
}

