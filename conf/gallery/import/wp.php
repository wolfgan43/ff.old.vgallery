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

if (!Auth::env("AREA_IMPORT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
// -------------------------
// check key & form url
$globals = ffGlobals::getInstance("wizard");
$globals->var_prefix = "wizcsv_";

$globals->transit_params = $cm->oPage->get_globals() . "ret_url=" . rawurlencode($_REQUEST["ret_url"]);
// -------------------------

$cm->oPage->form_method = "POST";

$db_wp = new ffDB_Sql();
$db_wp->on_error = "ignore";
if($db_wp->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"))) {
	if(isset($_REQUEST["record"]) && $_REQUEST["record"] >= 0)
		set_session("importwprecord", $_REQUEST["record"]);

	$wp_settings = importWP_settings();

	if($_REQUEST["exec"]) {
		importWP_exec($wp_settings);
		
		if($_REQUEST["exec"] == "continue" && get_session("importwprecord") < $wp_settings["count_post"])	
			ffRedirect("?exec=continue&record=" . (get_session("importwprecord") + 1));
	}

	$db = ffDB_Sql::factory();
	if(check_function("get_locale"))
		$arrLang = get_locale("lang", true);
		
		
	
//		importcsv_open();

//		$arrData = get_importcsv_fields($globals->import_fields[0]);
	$js_action = " ff.ajax.doRequest({'component' : 'WPModify', 'action' : 'WPModify_insert'}) ";

	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "WPModify";
	$oRecord->resources = $oRecord->id;
	$oRecord->title = ffTemplate::_get_word_by_code("import_wp_title") 
		. (get_session("importwprecord") < $wp_settings["count_post"]
			? '<a href="?record=' . (get_session("importwprecord") + 1) . '" class="' . Cms::getInstance("frameworkcss")->get("right", "util") . '">' . Cms::getInstance("frameworkcss")->get("next", "icon-tag", "2x") . '</a>' 
			: ""
		)
		. '<a href="javascript:void(0);" onclick="' . $js_action . '" class="' . Cms::getInstance("frameworkcss")->get("right", "util") . " " . Cms::getInstance("frameworkcss")->get(($wp_settings["ID_node"] ? "success" : "primary") , "button") . '">' . ($wp_settings["ID_node"] ? "Update" : "Import") . " Post: " . (get_session("importwprecord") + 1) . "/" . ($wp_settings["count_post"] + 1) . '</a>' 
		. (get_session("importwprecord")
			? '<a href="?record=' . (get_session("importwprecord") - 1) . '" class="' . Cms::getInstance("frameworkcss")->get("right", "util") . '">' . Cms::getInstance("frameworkcss")->get("prev", "icon-tag", "2x")  . '</a>'
			: ""
	);
	$oRecord->framework_css["title"]["col"] = array(
				"xs" => 12
				, "sm" => 12
				, "md" => 12
				, "lg" => 12
			);
	$oRecord->buttons_options["cancel"]["display"] = false;
	$oRecord->buttons_options["insert"]["display"] = false;
	$oRecord->skip_action = true;
	$oRecord->addEvent("on_do_action", "Step2_on_do_action");
	$oRecord->user_vars["wp_settings"] = $wp_settings;

	$bt = ffButton::factory($cm->oPage);
	$bt->id = "back";
	$bt->label = ffTemplate::_get_word_by_code("wizcsv_back");
	$bt->action_type = "submit";
	$bt->frmAction = "back";
	$bt->aspect = "link";
	$oRecord->addActionButton($bt);

	$bt = ffButton::factory($cm->oPage);
	$bt->ajax = true;
	$bt->id = "ActionButtonInsert";
	$bt->label = ffTemplate::_get_word_by_code("wizcsv_run") . " (" . $wp_settings["count_post"] . ")";
	$bt->action_type = "submit";
	$bt->frmAction = "autoinsert";
//	$bt->action_type = "gotourl";
//	$bt->url = "?exec=continue";
	$bt->aspect = "link";
	$oRecord->addActionButton($bt);

    $vg_fields = importWP_get_vg_fields();

	$fields = importWP_fields("strict");
	if(is_array($fields) && count($fields))  {
		$importwpfields = get_session("importwpfields");
 		
 		$oRecord->addContent(null, true, "fields"); 
	    $oRecord->groups["fields"] = array(
	                                        "title" => ffTemplate::_get_word_by_code("import_wp_fields")
	                                        , "class" => Cms::getInstance("frameworkcss")->get(array(6), "col") 
	                                     );    

		foreach($fields AS $fields_key => $fields_example) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = $fields_key;
			$oField->label = ffTemplate::_get_word_by_code("wizcsv_field") . " " . $fields_key;
			$oField->extended_type = "Selection";
			$oField->multi_pairs = $vg_fields["multi_pairs"];
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . (is_array($fields_example) ? $fields_example["default"] : $fields_example) . "</p>";
			$oField->default_value = new ffData(array_key_exists($fields_key, $importwpfields) 
												? $importwpfields[$fields_key] 
												: ffCommon_url_rewrite($fields_key)
											);
			$oRecord->addContent($oField, "fields");
		}
	}

	$fields_meta = importWP_fields("meta");
	if(is_array($arrLang) && count($arrLang)) {
		foreach($arrLang AS $lang_code => $lang) {
			$group_key = "meta_" . $lang_code;
			$oRecord->addContent(null, true, $group_key); 
			$oRecord->groups[$group_key] = array(
			                                "title" => ffTemplate::_get_word_by_code("import_wp_meta") . ($lang_code == LANGUAGE_DEFAULT ? "" : " - " . $lang_code)
			                                , "class" => Cms::getInstance("frameworkcss")->get(array(6), "col") 
			                             );    

			$oField = ffField::factory($cm->oPage);
			$oField->id = "seo_name" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code);
			$oField->label = "smart_url";
			$oField->extended_type = "Selection";
			if($lang_code != LANGUAGE_DEFAULT)
				$oField->multi_pairs[] = array(new ffData("translate"), new ffData("Translate from " . LANGUAGE_DEFAULT));
			$oField->multi_pairs[] = array(new ffData("name"), new ffData("post name"));
			$oField->multi_select_one = false;
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . $fields_meta[$oField->multi_pairs[0][0]->getValue()] . "</p>";
			$oField->default_value = (array_key_exists("seo_name" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code), $importwpfields)
										? new ffData($importwpfields["seo_name" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code)])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, $group_key);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "seo_parent" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code);
			$oField->label = "parent";
			$oField->extended_type = "Selection";
			if($lang_code != LANGUAGE_DEFAULT)
				$oField->multi_pairs[] = array(new ffData("translate"), new ffData("Translate from " . LANGUAGE_DEFAULT));
			$oField->multi_pairs[] = array(new ffData("parent"), new ffData("post parent"));
			$oField->multi_select_one = false;
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . importWP_get_vg_parent($fields_meta[$oField->multi_pairs[0][0]->getValue()]) . "</p>";
			$oField->default_value = (array_key_exists("seo_parent" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code), $importwpfields)
										? new ffData($importwpfields["seo_parent" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code)])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, $group_key);
			
			$oField = ffField::factory($cm->oPage);
			$oField->id = "seo_visible" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code);
			$oField->label = "Visible";
			$oField->extended_type = "Selection";
			if($lang_code != LANGUAGE_DEFAULT)
				$oField->multi_pairs[] = array(new ffData("translate"), new ffData("Translate from " . LANGUAGE_DEFAULT));
			$oField->multi_pairs[] = array(new ffData("visible"), new ffData("post visible"));
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . ($fields_meta[$oField->multi_pairs[0][0]->getValue()] ? "Yes" : "No") . "</p>";
			$oField->default_value = (array_key_exists("seo_visible" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code), $importwpfields) 
										? new ffData($importwpfields["seo_visible" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code)])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, $group_key);	

			$oField = ffField::factory($cm->oPage);
			$oField->id = "seo_meta_title" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code);
			$oField->label = "meta title";
			$oField->extended_type = "Selection";
			$oField->multi_select_one = false;
			if($lang_code != LANGUAGE_DEFAULT)
				$oField->multi_pairs[] = array(new ffData("translate"), new ffData("Translate from " . LANGUAGE_DEFAULT));
			if($fields_meta["_yoast_wpseo_title"])
				$oField->multi_pairs[] = array(new ffData("_yoast_wpseo_title"), new ffData("yoast seo title"));

			$oField->multi_pairs[] = array(new ffData("title"), new ffData("post title"));

			$fields_example = "";
			foreach($oField->multi_pairs AS $key => $value) {
				$fields_example .= "<li><strong>" . $value[1]->getValue() . ":</strong> " . $fields_meta[$value[0]->getValue()] . "</li>";
			}
			$fields_example = "<ul>" . $fields_example . "</ul>";
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label>" 
				. $fields_example . "</p>";
			
			$oField->default_value = (array_key_exists("seo_meta_title" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code), $importwpfields) 
										? new ffData($importwpfields["seo_meta_title" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code)])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, $group_key);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "seo_meta_description" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code);
			$oField->label = "meta description";
			$oField->extended_type = "Selection";
			$oField->multi_select_one = false;
			if($lang_code != LANGUAGE_DEFAULT)
				$oField->multi_pairs[] = array(new ffData("translate"), new ffData("Translate from " . LANGUAGE_DEFAULT));
			if($fields_meta["_yoast_wpseo_metadesc"])
				$oField->multi_pairs[] = array(new ffData("_yoast_wpseo_metadesc"), new ffData("yoast wpseo metadesc"));

			$oField->multi_pairs[] = array(new ffData("content"), new ffData("post content"));

			$fields_example = "";
			foreach($oField->multi_pairs AS $key => $value) {
				$desc = get_short_description($fields_meta[$value[0]->getValue()]); 
				$fields_example .= "<li><strong>" . $value[1]->getValue() . ":</strong> " . $desc["content"] . "</li>";
			}
			$fields_example = "<ul>" . $fields_example . "</ul>";
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label>" 
				. $fields_example . "</p>";

			$oField->default_value = (array_key_exists("seo_meta_description" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code), $importwpfields) 
										? new ffData($importwpfields["seo_meta_description" . ($lang_code == LANGUAGE_DEFAULT ? "" : "_" . $lang_code)])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, $group_key);
		}
	}
	

	$oField = ffField::factory($cm->oPage);
	$oField->id = "seo_owner";
	$oField->label = "owner";
	$oField->extended_type = "Selection";
	$oField->multi_pairs[] = array(new ffData("author"), new ffData("post author"));
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
	$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . $fields_meta[$oField->multi_pairs[0][0]->getValue()] . "</p>";
	$oField->default_value = (array_key_exists("seo_owner", $importwpfields)
								? new ffData($importwpfields["seo_owner"])
								: $oField->multi_pairs[0][0]
							);
	$oRecord->addContent($oField, "meta_" . LANGUAGE_DEFAULT);		

	$oField = ffField::factory($cm->oPage);
	$oField->id = "seo_tags";
	$oField->label = "Tags";
	$oField->extended_type = "Selection";
	$oField->multi_pairs[] = array(new ffData("tags"), new ffData("post tags"));
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
	$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . implode(", ", $fields_meta[$oField->multi_pairs[0][0]->getValue()]) . "</p>";
	$oField->default_value = (array_key_exists("seo_tags", $importwpfields) 
								? new ffData($importwpfields["seo_tags"])
								: $oField->multi_pairs[0][0]
							);
	$oRecord->addContent($oField, "meta_" . LANGUAGE_DEFAULT);	

	if(is_array($vg_fields["media"]["cover"]) && count($vg_fields["media"]["cover"])) {
		foreach($vg_fields["media"]["cover"] AS $ID_vg_field => $vg_field_name) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "media_cover_" . $ID_vg_field;
			$oField->label = $vg_field_name;
			$oField->extended_type = "Selection";
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
			if($fields_meta["_yoast_wpseo_opengraph-image"])
				$oField->multi_pairs[] = array(new ffData("_yoast_wpseo_opengraph-image"), new ffData("yoast opengraph image"));

			$oField->multi_pairs[] = array(new ffData("cover"), new ffData("post cover"));
			
			$fields_example = "";
			foreach($oField->multi_pairs AS $key => $value) {
				$fields_example .= "<li><strong>" . $value[1]->getValue() . ":</strong>" . $fields_meta[$value[0]->getValue()] . "</li>";
			}
			$fields_example = "<ul>" . $fields_example . "</ul>";
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label>" 
				. $fields_example . "</p>";

			$oField->default_value = (array_key_exists("media_cover_" . $ID_vg_field, $importwpfields) 
										? new ffData($importwpfields["media_cover_" . $ID_vg_field])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, "meta_" . LANGUAGE_DEFAULT);			
		}
	}
	if(is_array($vg_fields["media"]["gallery"]) && count($vg_fields["media"]["gallery"])) {
		foreach($vg_fields["media"]["gallery"] AS $ID_vg_field => $vg_field_name) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "media_gallery_" . $ID_vg_field;
			$oField->label = $vg_field_name;
			$oField->extended_type = "Selection";
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
			$oField->multi_pairs[] = array(new ffData("gallery"), new ffData("post gallery"));
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label> " . $fields_meta[$oField->multi_pairs[0][0]->getValue()] . "</p>";
			$oField->default_value = (array_key_exists("media_gallery_" . $ID_vg_field, $importwpfields) 
										? new ffData($importwpfields["media_gallery_" . $ID_vg_field])
										: $oField->multi_pairs[0][0]
									);
			$oRecord->addContent($oField, "meta_" . LANGUAGE_DEFAULT);	
		}
	}

	
	$cm->oPage->addContent($oRecord);
} else {
	$strError = "connection to database failed";
}




function Step2_on_do_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();

	switch ($frmAction)
	{
		case "autoinsert":
		case "insert":
			foreach ($oRecord->form_fields as $key => $value)
			{
				$importwpfields[$key] = $value->getValue();
			}
			set_session("importwpfields", $importwpfields);
			
			
			importWP_exec($oRecord->user_vars["wp_settings"]);

			break;
		case "back":	
			ffRedirect(FF_SITE_PATH . $cm->oPage->page_path . "?" . $cm->oPage->get_globals());
			break;
	}
}

function importWP_get_vg_fields() {
    static $fields = null;
	
    if(!$fields) {
	    $db = ffDB_Sql::factory();

        if(check_function("get_table_support"))
            $fields["tbl_supp"] = get_table_support();

        $sSQL = "SELECT vgallery_fields.*
                FROM vgallery_fields
                WHERE vgallery_fields.ID_type = " . $db->toSql(get_session("importwpvgtype"), "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                if(!($fields["tbl_supp"]["data_type"]["rev"][$db->record["ID_data_type"]] == "table.alt" && $db->record["data_source"] == "vgallery_nodes")) {
                    if($fields["tbl_supp"]["data_type"]["rev"][$db->record["ID_data_type"]] == "media") {
                        $fields["media"]["gallery"][$db->record["ID"]] = $db->record["name"];
                    } elseif($fields["tbl_supp"]["extended_type"]["group"]["upload"][$db->record["ID_extended_type"]]) {
                        $fields["media"]["cover"][$db->record["ID"]] = $db->record["name"];
                    } else {
                        $fields["multi_pairs"][$db->record["ID"]] = array(new ffData($db->record["name"]), new ffData($db->record["name"]));
                    }
                    $fields["smart_url"][$db->record["name"]] = $db->record;
                    $fields["rev"][$db->record["ID"]] = $db->record;
                }
            } while($db->nextRecord());
        }
    }
    return $fields;
}

function importWP_get_vg_name() {
	static $vgallery_name = null;

	if(!$vgallery_name) {
		$db = ffDB_Sql::factory();

		$sSQL = "SELECT vgallery.name FROM vgallery WHERE vgallery.ID = " . $db->toSql(get_session("importwpvg"), "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {
			$vgallery_name = $db->getField("name", "Text", true);
		}
		
	}
	return $vgallery_name;
}

function importWP_get_vg_parent($path) {
	$db = ffDB_Sql::factory();
	$vgallery_name = importWP_get_vg_name();

	$sSQL = "SELECT vgallery_nodes.parent, vgallery_nodes.name 
			FROM vgallery_nodes 
			WHERE vgallery_nodes.name = " . $db->toSql(basename($path)). "
				AND vgallery_nodes.ID_vgallery = " . $db->toSql(get_session("importwpvg"), "Number") . "
				AND vgallery_nodes.is_dir > 0";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$full_path = str_replace("/" . $vgallery_name, "", stripslash($db->getField("parent", "Text", true)) . "/" . $db->getField("name", "Text", true));
	}

	return $full_path;
}

function importWP_get_vg_dir() {
	static $ID_type = null;

	if(!$ID_type) {
		$db = ffDB_Sql::factory();

		$sSQL = "SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = " . $db->toSql("Directory");
		$db->query($sSQL);
		if($db->nextRecord()) {
			$ID_type = $db->getField("ID", "Number", true);
		}
	}

	return $ID_type;
}


function importWP_exec($settings, $db_wp = null) {
	if(!$db_wp) {
		$db_wp = new ffDB_Sql();
		$db_wp->on_error = "ignore";
		$db_wp->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
	}
	if($db_wp) {
		check_function("get_short_description");

		$importwpfields = get_session("importwpfields");
		$node_type = "vgallery";

		$post = importWP_fields("all");

		$vgallery_name = importWP_get_vg_name();

		$parent = "/" . $vgallery_name . importWP_get_vg_parent($post[$importwpfields["seo_parent"]]);
		$permalink = $parent . "/" . $post[$importwpfields["seo_name"]];

		$cats = importWP_exec_terms($post["cats"], "category");
		$tags = importWP_exec_terms($post["tags"], "post_tag");
		if($post[$importwpfields["seo_owner"]])
			$owner = importWP_exec_owner($post[$importwpfields["seo_owner"]]);

		$last_update = new ffData($post["last_update"], "DateTime", FF_SYSTEM_LOCALE);
		$publishing = new ffData($post["date"], "DateTime", FF_SYSTEM_LOCALE);

		$node = array(
			"ID" 						=> $settings["ID_node"]
			, "sid" 					=> $post["ID"]
			, "ID_type" 				=> null
			, "ID_vgallery" 			=> null
			, "is_dir" 					=> "0"
			, "last_update" 			=> $last_update->getValue("Timestamp")
			, "published_at" 			=> $publishing->getValue("Timestamp")
			, "name" 					=> $post[$importwpfields["seo_name"]]
			, "parent" 					=> $parent
			, "permalink" 				=> $permalink
			, "meta_title" 				=> $post[$importwpfields["seo_meta_title"]]
			, "meta_title_alt" 			=> $post[$importwpfields["seo_meta_title"]]
			, "meta_description" 		=> (is_array($post[$importwpfields["seo_meta_description"]])
											? $post[$importwpfields["seo_meta_description"]]["default"]
											: get_short_description($post[$importwpfields["seo_meta_description"]])
										)	
			, "owner" 					=> $owner
			, "tags" 					=> $tags["rev"]
			, "keywords" 				=> $tags["smart_url"]
			, "cats" 					=> $cats["rev"]
			, "visible" 				=> $post["visible"]
		);
		
		$ID_node = importWP_exec_node($node, $node_type);
		if($ID_node) {
			if(check_function("set_field_permalink"))
				$arrPermalink = set_field_permalink("vgallery_nodes", $ID_node);		
		
			importWP_exec_fields($ID_node, $post, $arrPermalink);
		}
		
	}
}

function importWP_exec_dir($relative_dir) {
	$db = ffDB_Sql::factory();

	$relative_dir = stripslash($relative_dir);
	if(strlen($relative_dir) && $relative_dir != FF_SITE_UPDIR && strpos($relative_dir, FF_SITE_UPDIR) === 0) {
		$local_dir = str_replace(FF_SITE_UPDIR, "", $relative_dir);
		$sSQL = "SELECT files.*
				FROM files
				WHERE files.parent = " . $db->toSql(ffCommon_dirname($local_dir)) . "
					AND files.name = " . $db->toSql(basename($local_dir)) . "
					AND files.is_dir > 0";
		$db->query($sSQL);
		if(!$db->nextRecord()) {
			$sSQL = "INSERT INTO files
					(
						ID
						, name
						, parent
						, created
						, last_update
						, owner
						, is_dir
						, alt
						, title
					)
					VALUES
					(
						null
						, " . $db->toSql(basename($local_dir))  . "
						, " . $db->toSql(ffCommon_dirname($local_dir))  . "
						, " . $db->toSql(time(), "Number")  . "
						, " . $db->toSql(time(), "Number")  . "
						, '-1'
						, '1'
						, ''
						, ''
					)";
				$db->execute($sSQL);
		}
		
		importWP_exec_dir(ffCommon_dirname($relative_dir));
	}
}

function importWP_exec_file($remote_file, $relative_path, $meta = null, $base_path = FF_SITE_UPDIR) {
	$db = ffDB_Sql::factory();
	check_function("get_locale");

	$file = file_get_contents($remote_file);
	if($file) {
		$filename = ffGetFilename($remote_file, true);
		$filebasename = ffCommon_url_rewrite($filename) . "." . ffGetFilename($remote_file, false);
		if(!is_dir(FF_DISK_PATH . $base_path . $relative_path))
			mkdir(FF_DISK_PATH . $base_path . $relative_path, 0777, true);

		if(file_put_contents(FF_DISK_PATH . $base_path . $relative_path . "/" . $filebasename, $file)) {
			chmod(FF_DISK_PATH . $base_path . $relative_path . "/" . $filebasename, 0777);
            ffMedia::optimize(FF_DISK_PATH . $base_path . $relative_path . "/" . $filebasename);

			$new_file = $base_path . $relative_path . "/" . $filebasename;

			importWP_exec_dir(ffCommon_dirname($new_file));
			$sSQL = "SELECT files.*
					FROM files
					WHERE files.parent = " . $db->toSql($relative_path) . "
						AND files.name = " . $db->toSql($filebasename);
			$db->query($sSQL);
			if(!$db->nextRecord()) {
				$sSQL = "INSERT INTO files
						(
							ID
							, name
							, parent
							, created
							, last_update
							, owner
							, is_dir
							, alt
							, title
						)
						VALUES
						(
							null
							, " . $db->toSql($filebasename)  . "
							, " . $db->toSql($relative_path)  . "
							, " . $db->toSql(time(), "Number")  . "
							, " . $db->toSql(time(), "Number")  . "
							, '-1'
							, '0'
							, " . $db->toSql($meta[LANGUAGE_DEFAULT_ID]["alt"])  . "
							, " . $db->toSql($meta[LANGUAGE_DEFAULT_ID]["title"])  . "
						)";
				$db->execute($sSQL);
				$ID_files = $db->getInsertID(true);
			} else {
				$ID_files = $db->getField("ID", "Number", true);
				if($meta) {
					$sSQL = "UPDATE files SET
								alt = " . (isset($meta[LANGUAGE_DEFAULT_ID]["alt"]) 
									? $db->toSql((strlen($meta[LANGUAGE_DEFAULT_ID]["alt"]) ? $meta[LANGUAGE_DEFAULT_ID]["alt"] . " - " : "") . $filename) 
									: "alt" 
								) . "
								, title = " . (isset($meta[LANGUAGE_DEFAULT_ID]["title"]) 
									? $db->toSql((strlen($meta[LANGUAGE_DEFAULT_ID]["title"]) ? $meta[LANGUAGE_DEFAULT_ID]["title"] . " - " : "") . $filename) 
									: "title" 
								) . "
							WHERE files.ID = " . $db->toSql($ID_files, "Number");
					$db->execute($sSQL);				
				}
			}

			if($meta) {
				$arrLang = get_locale("lang");
				if(is_array($arrLang) && count($arrLang)) {
					foreach($arrLang AS $lang_code => $lang) {
						if(!$meta[$lang["ID"]] && $meta[LANGUAGE_DEFAULT_ID])
							$meta[$lang["ID"]] = $meta[LANGUAGE_DEFAULT_ID];

						if(!$meta[$lang["ID"]])
							continue;

						$sSQL = "SELECT files_rel_languages.*
								FROM files_rel_languages
								WHERE files_rel_languages.ID_files = " . $db->toSql($ID_files, "Number") 
									. " AND files_rel_languages.ID_languages = " . $db->toSql($lang["ID"], "Number");
						$db->query($sSQL);
						if(!$db->nextRecord()) {
							$sSQL = "INSERT INTO files_rel_languages
									(
										ID
										, ID_files
										, ID_languages
										, alias
										, description
									)
									VALUES
									(
										null
										, " . $db->toSql($ID_files, "Number") . "
										, " . $db->toSql($lang["ID"], "Number") . "
										, " . (isset($meta[$lang["ID"]]["alt"]) 
											? $db->toSql((strlen($meta[$lang["ID"]]["alt"]) ? $meta[$lang["ID"]]["alt"] . " - " : "") . $filename) 
											: "alias" 
										) . "
										, " . (isset($meta[$lang["ID"]]["title"]) 
											? $db->toSql((strlen($meta[$lang["ID"]]["title"]) ? $meta[$lang["ID"]]["title"] . " - " : "") . $filename) 
											: "description" 
										) . "
									)";
							$db->execute($sSQL);
						} else {
							$sSQL = "UPDATE files_rel_languages SET
										alias = " . (isset($meta[$lang["ID"]]["alt"]) 
											? $db->toSql((strlen($meta[$lang["ID"]]["alt"]) ? $meta[$lang["ID"]]["alt"] . " - " : "") . $filename) 
											: "alias" 
										) . "
										, description = " . (isset($meta[$lang["ID"]]["title"]) 
											? $db->toSql((strlen($meta[$lang["ID"]]["title"]) ? $meta[$lang["ID"]]["title"] . " - " : "") . $filename) 
											: "description" 
										) . "
									WHERE files_rel_languages.ID_files = " . $db->toSql($ID_files, "Number") . 
										" AND files_rel_languages.ID_languages = " . $db->toSql($lang["ID"], "Number");
							$db->execute($sSQL);
						}
					}
				}
			}
		}
	}	
	
	if(!$new_file)
		$new_file = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
	
	return $new_file;
}

function importWP_exec_fields_purge($ID_node, $permalink = null) {
	$db = ffDB_Sql::factory();

	$vgallery_name = importWP_get_vg_name();

	$sSQL = "DELETE FROM vgallery_rel_nodes_fields WHERE ID_nodes = " . $db->toSql($ID_node, "Number");
	$db->execute($sSQL);
	
	$sSQL = "DELETE FROM rel_nodes WHERE contest_src = " . $db->toSql($vgallery_name) . " AND ID_node_src = " . $db->toSql($ID_node, "Number");
	$db->execute($sSQL);
	
	
    if($permalink && check_function("fs_operation"))
        xpurge_dir(FF_DISK_UPDIR . $permalink);
}

function importWP_exec_fields($ID_node, $node, $arrPermalink) {
	$db = ffDB_Sql::factory();

	check_function("get_locale");

	$importwpfields = get_session("importwpfields");
	if(is_array($importwpfields)) {
		$vg_fields = importWP_get_vg_fields();
		$fields = array_filter($importwpfields);

		if(count($fields)) {
			$resources = array();
			$url_rewrite = array();
			$vgallery_name = importWP_get_vg_name();

			importWP_exec_fields_purge($ID_node);

			//if(is_array($node["gallery"]) && count($node["gallery"]))
			$resources = array_merge($resources, array_fill_keys(explode("<br />", $node["gallery"]), "/gallery"));
			//if(is_array($node["cover"]) && count($node["cover"]))
			$resources = array_merge($resources, array_fill_keys(explode("<br />", $node["cover"]), ""));
			
			/*if(is_array($vg_fields["media"]) && count($vg_fields["media"])) {
				$node_cover = "";
				foreach($vg_fields["media"] AS $media_key => $media_value) {
					foreach($media_value AS $ID_vg_field => $vg_field_name) {
						$node_key = $fields["media_" . $media_key . "_" . $ID_vg_field];
						$resources = array_merge($resources, array_fill_keys(explode("<br />", $node[$node_key]), ($media_key == "cover" ? "" : "/" . $media_key)));
						
						if($media_key == "cover" && !$node_cover)
							$node_cover = $node_key;
					}
				}
				
				$resources = array_merge($resources, array_fill_keys(explode("<br />", $node[$node_cover]), ""));

			}*/

			foreach($resources AS $remote_file => $folder) {
				if(importWP_check_resource($remote_file)) {
					$url_rewrite[$remote_file] = importWP_exec_file($remote_file, stripslash($arrPermalink[LANGUAGE_DEFAULT]) . $folder);
				}
			}

			$arrLang = get_locale("lang");

//	print_r($resources);
//	print_r($node);	

			if(is_array($arrPermalink) && count($arrPermalink)) {
				foreach($arrPermalink AS $lang_code => $permalink) {
					if($lang_code != LANGUAGE_DEFAULT)
						continue;

					$ID_lang = $arrLang[$lang_code]["ID"];
					foreach($fields AS $field_key => $vg_field_smart_url) {
					 	if(strpos($field_key, "seo_") === 0)
					 		continue;
					 	if(strpos($field_key, "media_") === 0) {
					 		$arrFieldKey = explode("_", $field_key);
							$ID_field = $arrFieldKey[count($arrFieldKey) - 1];
							$field_value = str_replace("<br />", ",", $node[$vg_field_smart_url]);
							if($vg_field_smart_url != "cover" && strpos($field_value, $node["cover"]) !== false)
								$field_value = str_replace(array("," . $node["cover"], $node["cover"] . ",", $node["cover"]), "", $field_value);
						} else {
							$ID_field = $vg_fields["smart_url"][$vg_field_smart_url]["ID"];
							$field_value = (is_array($node[$field_key]) ? $node[$field_key][$field_key] : $node[$field_key]);
						}
						if($ID_field) {
							$field_value = str_replace(array_keys($url_rewrite), array_values($url_rewrite), $field_value);

							switch($vg_fields["tbl_supp"]["extended_type"]["smart_url"][$vg_fields["tbl_supp"]["extended_type"]["rev"][$vg_fields["rev"][$ID_field]["ID_extended_type"]]]["group"]) {
								case "upload":
									if($field_value) {
										$arrField = explode(",", $field_value);
										foreach($arrField AS $arrField_value) {
											if(strpos($arrField_value, FF_SITE_UPDIR) === 0) {
												$arrFinalField[] = str_replace(FF_SITE_UPDIR, "", $arrField_value);
											}
										}
										$field_value = implode(",", $arrFinalField);
									} 
									break;
								case "date";
									$tmp_field_value = new ffData($field_value, "DateTime");
									$field_value = $tmp_field_value->getValue("Date", FF_SYSTEM_LOCALE);
									break;
								default:
							
							}
							$nodes = "";
							switch($vg_fields["tbl_supp"]["data_type"]["rev"][$vg_fields["rev"][$ID_field]["ID_data_type"]]) {
								case "relationship":
									$ID_rel_node = 23;//importWP_exec_node($rel_node);
									if($ID_rel_node) {
										$nodes = $ID_rel_node;
										$sSQL = "INSERT INTO rel_nodes
												(
													ID
													, cascading
													, contest_src
													, ID_node_src
													, contest_dst
													, ID_node_dst
													
												)
												VALUES
												(
													null
													, '0'
													, " . $db->toSql($vgallery_name) . "
													, " . $db->toSql($ID_node, "Number") . "
													, " . $db->toSql($vg_fields["smart_url"][$vg_field_smart_url]["data_source"]) . "
													, " . $db->toSql($ID_rel_node, "Number") . "
												)";	
										$db->execute($sSQL);
										//echo $sSQL . "\n\n";
									}								
								
								default:	
									$sSQL = "INSERT INTO vgallery_rel_nodes_fields
											(
												ID
												, ID_fields
												, ID_nodes
												, ID_lang
												, description
												, description_text
												, nodes
											)
											VALUES
											(
												null
												, " . $db->toSql($ID_field, "Number") .  "
												, " . $db->toSql($ID_node, "Number") .  "
												, " . $db->toSql($ID_lang, "Number") .  "
												, " . $db->toSql($field_value) .  "
												, " . $db->toSql(strip_tags($field_value)) .  "
												, " . $db->toSql($nodes) .  "
											)";
									$db->execute($sSQL);
									//echo $sSQL . "\n\n";
							
							
							}
						 
						}
					}

						
				
				}
			}
			
		/*	foreach($fields AS $key => $value) {
				if(strpos($key, "seo_") === 0) {
					$wp_fields[$value] = $key;
				} else {
					$wp_fields[$key] = $value;				
				}

				$post = importWP_fields($wp_fields);	
			}*/
//asdasd da rivavare le resources



	//die();

		}

		
		

	}


}


function importWP_exec_node($node, $type = "vgallery") {
	switch($type) {
		case "anagraph":
			$ID_node = importWP_exec_node_anagraph($node);
			break;
		case "vgallery":
		case "category":
			$ID_node = importWP_exec_node_vgallery($node);
			break;
		case "files":
			$ID_node = importWP_exec_node_files($node);
			break;
		case "tags":
		case "post_tag":
			$ID_node = importWP_exec_node_tags($node);
			break;
		default:
	
	
	}
	return $ID_node;
}

function importWP_exec_node_anagraph($node) {

	return $ID_node;
}

function importWP_exec_node_vgallery($node) {
	$db = ffDB_Sql::factory();
	$ID_node = 0;
	$ID_type = ($node["ID_type"]
					? $node["ID_type"]
					: get_session("importwpvgtype")
				);
	$ID_vgallery = ($node["ID_vgallery"]
					? $node["ID_vgallery"]
					: get_session("importwpvg")
				);
	
	$cats = (is_array($node["cats"])
				? implode(",", $node["cats"])
				: $node["cats"]
			);
	$tags = (is_array($node["tags"])
				? implode(",", $node["tags"])
				: $node["tags"]
			);
	$keywords = (is_array($node["keywords"])
				? implode(",", $node["keywords"])
				: $node["keywords"]
			);
	if($node["ID"])
	{
		if(!$node["is_dir"]) {
			$sSQL = "UPDATE vgallery_nodes SET 
						ID_type = " .  $db->toSql($ID_type, "Number") . "
						, ID_vgallery = " .  $db->toSql($ID_vgallery, "Number") . "
						, is_dir = " .  $db->toSql($node["is_dir"], "Number") . "
						, last_update = " .  $db->toSql($node["last_update"], "Number") . "
						, published_at = " .  $db->toSql($node["published_at"], "Number") . "
						, created = " .  $db->toSql(time(), "Number") . "
						, name = " .  $db->toSql($node["name"]) . "
						, parent = IF(parent = '', " .  $db->toSql($node["parent"]) . ", parent)
						, permalink = " .  $db->toSql($node["permalink"]) . "
						, meta_title = " .  $db->toSql($node["meta_title"]) . "
						, meta_title_alt = " .  $db->toSql($node["meta_title_alt"]) . "
						, meta_description = " .  $db->toSql($node["meta_description"]) . "
						, owner = " .  $db->toSql($node["owner"], "Number") . "
						, tags = " .  $db->toSql($tags) . "
						, keywords = " .  $db->toSql($keywords) . "
						, cats = " .  $db->toSql($cats) . "
						, visible = " .  $db->toSql($node["visible"], "Number") . "
					WHERE vgallery_nodes.ID = " . $db->toSql($node["ID"], "Number");
			$db->execute($sSQL);
		}		
		$ID_node = $node["ID"];
	}
	else
	{
	//$node["tags"]
		$sSQL = "INSERT INTO vgallery_nodes
				(
					ID
					, sid
					, ID_type
					, ID_vgallery
					, is_dir
					, last_update
					, published_at
					, created
					, name
					, parent
					, permalink
					, meta_title
					, meta_title_alt
					, meta_description
					, owner
					, tags
					, keywords
					, cats
					, visible
				)
				VALUES
				(
					null
					, " . $db->toSql($node["sid"]) . "
					, " . $db->toSql($ID_type, "Number") . "
					, " . $db->toSql($ID_vgallery, "Number") . "
					, " . $db->toSql($node["is_dir"], "Number") . "
					, " . $db->toSql($node["last_update"], "Number") . "
					, " . $db->toSql($node["published_at"], "Number") . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql($node["name"]) . "
					, " . $db->toSql($node["parent"]) . "
					, " . $db->toSql($node["permalink"]) . "
					, " . $db->toSql($node["meta_title"]) . "
					, " . $db->toSql($node["meta_title_alt"]) . "
					, " . $db->toSql($node["meta_description"]) . "
					, " . $db->toSql($node["owner"], "Number") . "
					, " . $db->toSql($tags) . "
					, " . $db->toSql($keywords) . "
					, " . $db->toSql($cats) . "
					, " . $db->toSql($node["visible"], "Number") . "
				)
		";
		
		$db->execute($sSQL);
		$ID_node = $db->getInsertID(true);

		
	}	
	//echo $sSQL . "\n\n";
	
	return $ID_node;
}

function importWP_exec_node_files($node) {

	return $ID_node;
}

function importWP_exec_node_tags($node) {
	$db = ffDB_Sql::factory();
	$ID_node = 0;
	$ID_lang = LANGUAGE_DEFAULT_ID;

	$permalink = "/" . $node["name"];

	if($node["ID"])
	{
		$sSQL = "UPDATE search_tags SET "
			. ""
			. " WHERE search_tags.ID = " . $db->toSql($node["ID"], "Number");
			
		$ID_node = $node["ID"];
	}
	else
	{
	//$node["tags"]
		$code = 0;
		$sSQL = "SELECT ID FROM search_tags_page WHERE 1 ORDER BY ID DESC LIMIT 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$code = $db->getField("ID", "Number", true);
		}
		$code++;
		
		$sSQL = "INSERT INTO search_tags_page
				(
					ID
					, sid
					, ID_lang
					, code
					, created
					, last_update
					, meta_title
					, meta_description
					, name
					, parent
					, smart_url
					, permalink
					, visible
				)
				VALUES
				(
					null
					, " . $db->toSql($node["sid"], "Number") . "
					, " . $db->toSql($ID_lang, "Number") . "
					, " . $db->toSql($code, "Number") . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql($node["meta_title"]) . "
					, " . $db->toSql($node["meta_description"]) . "
					, " . $db->toSql($node["name"]) . "
					, " . $db->toSql($node["parent"]) . "
					, " . $db->toSql($node["name"]) . "
					, " . $db->toSql($permalink) . "
					, '1'
				)";
		$db->execute($sSQL);
		$ID_tag_page = $db->getInsertID(true);
		//echo $sSQL . "\n\n";	
		
		$code = 0;
		$sSQL = "SELECT ID FROM search_tags WHERE 1 ORDER BY ID DESC LIMIT 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$code = $db->getField("ID", "Number", true);
		}
		$code++;
		
		$sSQL = "INSERT INTO search_tags
				(
					ID
					, sid
					, ID_lang
					, ID_tag_page
					, categories
					, code
					, cover
					, name
					, permalink
					, smart_url
					, status
				)
				VALUES
				(
					null
					, " . $db->toSql($node["sid"], "Number") . "
					, " . $db->toSql($ID_lang, "Number") . "
					, " . $db->toSql($ID_tag_page, "Number") . "
					, ''
					, " . $db->toSql($code, "Number") . "
					, ''
					, " . $db->toSql($node["meta_title"]) . "
					, " . $db->toSql($permalink) . "
					, " . $db->toSql($node["name"]) . "
					, '1'
				)
		";
		$db->execute($sSQL);
		$ID_node = $db->getInsertID(true);
		//echo $sSQL . "\n\n";
	}	
	

	return $ID_node;
}


function importWP_exec_node_vgallery_rel_lang($node) {
	$db = ffDB_Sql::factory();
	$ID_node = 0;
	$ID_type = ($node["ID_type"]
					? $node["ID_type"]
					: get_session("importwpvgtype")
				);
	$ID_vgallery = ($node["ID_vgallery"]
					? $node["ID_vgallery"]
					: get_session("importwpvg")
				);
	
	$cats = (is_array($node["cats"])
				? implode(",", $node["cats"])
				: $node["cats"]
			);
	$tags = (is_array($node["tags"])
				? implode(",", $node["tags"])
				: $node["tags"]
			);
	$keywords = (is_array($node["keywords"])
				? implode(",", $node["keywords"])
				: $node["keywords"]
			);
	if($node["ID"])
	{
		if(!$node["is_dir"]) {
			$sSQL = "UPDATE vgallery_rel_languages SET 
						ID_type = " .  $db->toSql($ID_type, "Number") . "
						, ID_vgallery = " .  $db->toSql($ID_vgallery, "Number") . "
						, is_dir = " .  $db->toSql($node["is_dir"], "Number") . "
						, last_update = " .  $db->toSql($node["last_update"], "Number") . "
						, published_at = " .  $db->toSql($node["published_at"], "Number") . "
						, created = " .  $db->toSql(time(), "Number") . "
						, name = " .  $db->toSql($node["name"]) . "
						, parent = " .  $db->toSql($node["parent"]) . "
						, permalink = " .  $db->toSql($node["permalink"]) . "
						, meta_title = " .  $db->toSql($node["meta_title"]) . "
						, meta_title_alt = " .  $db->toSql($node["meta_title_alt"]) . "
						, meta_description = " .  $db->toSql($node["meta_description"]) . "
						, owner = " .  $db->toSql($node["owner"], "Number") . "
						, tags = " .  $db->toSql($tags) . "
						, keywords = " .  $db->toSql($keywords) . "
						, cats = " .  $db->toSql($cats) . "
						, visible = " .  $db->toSql($node["visible"], "Number") . "
					WHERE vgallery_nodes.ID = " . $db->toSql($node["ID"], "Number");
			$db->execute($sSQL);
		}		
		$ID_node = $node["ID"];
	}
	else
	{
	//$node["tags"]
		$sSQL = "INSERT INTO vgallery_nodes
				(
					ID
					, sid
					, ID_type
					, ID_vgallery
					, is_dir
					, last_update
					, published_at
					, created
					, name
					, parent
					, permalink
					, meta_title
					, meta_title_alt
					, meta_description
					, owner
					, tags
					, keywords
					, cats
					, visible
				)
				VALUES
				(
					null
					, " . $db->toSql($node["sid"]) . "
					, " . $db->toSql($ID_type, "Number") . "
					, " . $db->toSql($ID_vgallery, "Number") . "
					, " . $db->toSql($node["is_dir"], "Number") . "
					, " . $db->toSql($node["last_update"], "Number") . "
					, " . $db->toSql($node["published_at"], "Number") . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql($node["name"]) . "
					, " . $db->toSql($node["parent"]) . "
					, " . $db->toSql($node["permalink"]) . "
					, " . $db->toSql($node["meta_title"]) . "
					, " . $db->toSql($node["meta_title_alt"]) . "
					, " . $db->toSql($node["meta_description"]) . "
					, " . $db->toSql($node["owner"], "Number") . "
					, " . $db->toSql($tags) . "
					, " . $db->toSql($keywords) . "
					, " . $db->toSql($cats) . "
					, " . $db->toSql($node["visible"], "Number") . "
				)
		";
		
		$db->execute($sSQL);
		$ID_node = $db->getInsertID(true);

		
	}	
	//echo $sSQL . "\n\n";
	
	return $ID_node;
}

function importWP_exec_owner() {
    
    
}

function importWP_exec_terms($limit = null, $type = "category") {
	$db = ffDB_Sql::factory();
	
	switch($type) {
		case "category":
			$sSQL = "SELECT vgallery_nodes.sid 
						, vgallery_nodes.ID 
						, vgallery_nodes.name 
					FROM vgallery_nodes
					WHERE vgallery_nodes.ID_vgallery = " . $db->toSql(get_session("importwpvg"), "Number"). "
						AND vgallery_nodes.is_dir > 0";
			break;
		case "post_tag":
			$sSQL = "SELECT search_tags.ID AS sid
						, search_tags.ID 
						, search_tags.smart_url AS name 
					FROM search_tags
					WHERE 1";
			break;
		default:
	}
	if(strlen($sSQL)) {
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$arrCategory[$db->getField("sid", "Text", true)] = $db->getField("ID", "Number", true);
				$arrCategoryAlt[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
						
			} while($db->nextRecord());
		}

		$arrWPCategory = importWP_terms($type, "smart_url");
		if(is_array($arrWPCategory) && count($arrWPCategory)) {
			foreach($arrWPCategory AS $full_path => $category) {
				if(is_array($limit) && !$limit[$category["ID"]] && $full_path != "/senza-categoria")
					continue;

				$node = array(
					"ID" 						=> ($arrCategory[$category["ID"]]
													? $arrCategory[$category["ID"]]
													: ($arrCategoryAlt[basename($full_path)]
														? $arrCategoryAlt[basename($full_path)]
														: null
													)
												)
					, "sid" 					=> $category["ID"]
					, "ID_type" 				=> importWP_get_vg_dir()
					, "ID_vgallery" 			=> null
					, "is_dir" 					=> "1"
					, "last_update" 			=> time()
					, "published_at" 			=> time()
					, "name" 					=> basename($full_path)
					, "parent" 					=> "/" . importWP_get_vg_name() . stripslash(ffCommon_dirname($full_path))
					, "permalink" 				=> ""
					, "meta_title" 				=> $category["name"]
					, "meta_title_alt" 			=> $category["name"]
					, "meta_description" 		=> $category["name"]
					, "owner" 					=> "-1"
					, "tags" 					=> ""
					, "keywords" 				=> ""
					, "cats" 					=> ""
					, "visible" 				=> "0"
				);
				$terms["smart_url"][$category["ID"]] = basename($full_path);
				$terms["rev"][$category["ID"]] = importWP_exec_node($node, $type);

				if($type == "category" && check_function("set_field_permalink"))
					$arrPermalink = set_field_permalink("vgallery_nodes", $terms["rev"][$category["ID"]]);		
				
				
			}
		}
	}	
	return $terms;
}


function importWP_settings($db_wp = null) {
	$db = ffDB_Sql::factory();
	
	if(!$db_wp) {
		$db_wp = new ffDB_Sql();
		$db_wp->on_error = "ignore";
		$db_wp->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
	}
	if($db_wp) {
		$sSQL = "SELECT COUNT(ID) AS cont
				FROM wp_posts
				WHERE wp_posts.post_type = 'post'";
		$db_wp->query($sSQL);
		if($db_wp->nextRecord()) {
			$res["count_post"] = $db_wp->getField("cont", "Number", true);
		}
		
		$sSQL = "SELECT wp_posts.ID AS sid
				FROM wp_posts
				 WHERE wp_posts.post_type = 'post'
				 ORDER BY wp_posts.ID DESC
				 LIMIT " . $db->toSql(get_session("importwprecord"), "Number") . ", 1";
		$db_wp->query($sSQL);
		if($db_wp->nextRecord()) {
			$res["sid"] = $db_wp->getField("sid", "Number", true);
		}
 	}
 	
 	if($res["sid"]) {
 		$sSQL = "SELECT vgallery_nodes.ID
 				FROM vgallery_nodes
 				WHERE vgallery_nodes.sid = " . $db->toSql($res["sid"]);
 		$db->query($sSQL);
 		if($db->nextRecord()) {
 			$res["ID_node"] = $db->getField("ID", "Number", true);
 		}
	} 	
 	return $res;
}

function importWP_check_resource($resource) {
	if(strpos(ffMedia::getMimeTypeByFilename($resource), "image/") !== false
		|| strpos(ffMedia::getMimeTypeByFilename($resource), "application/excel") !== false
		|| strpos(ffMedia::getMimeTypeByFilename($resource), "application/pdf") !== false
		|| strpos(ffMedia::getMimeTypeByFilename($resource), "application/msword") !== false
	) {
		return true;
	}

}

function importWP_find_resource($content) {
	preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $media);
	
	if(is_array($media) && count($media[0])) {
		foreach($media[0] AS $media_value) {
			if(importWP_check_resource($media_value)) {
				$gallery[] = $media_value;
			}
		}
	}
	
	return $gallery;
}

function importWP_post($limit = null, $db = null) {
	check_function("get_short_description");
	
	if(!$db) {
		$db = new ffDB_Sql();
		$db->on_error = "ignore";
		$db->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
	}
	if($db) {
		if($limit === null)
			$limit = get_session("importwprecord");
		
		$sSQL = "SELECT wp_posts.*
					, (SELECT GROUP_CONCAT(wp_term_relationships.term_taxonomy_id SEPARATOR ',')
						FROM wp_term_taxonomy
							INNER JOIN wp_term_relationships ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
						WHERE wp_term_taxonomy.taxonomy = 'category'
							AND wp_term_relationships.object_id = wp_posts.ID
					) AS cats
					, (SELECT GROUP_CONCAT(wp_term_relationships.term_taxonomy_id SEPARATOR ',')
						FROM wp_term_taxonomy
							INNER JOIN wp_term_relationships ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
						WHERE wp_term_taxonomy.taxonomy = 'post_tag'
							AND wp_term_relationships.object_id = wp_posts.ID
					) AS tags					
					, (SELECT wp_users.display_name
						FROM wp_users
						WHERE wp_users.ID = wp_posts.post_author
						LIMIT 1
					) AS post_author_label
				FROM wp_posts
				WHERE  wp_posts.post_type = 'post'
				ORDER BY ID DESC
				LIMIT " . $db->toSql($limit, "Number") . ", 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$cats = $db->getField("cats", "Text", true);
			$tags = $db->getField("tags", "Text", true);
			$content = $db->getField("post_content", "Text", true);
			$short_desc = get_short_description($content);
			$gallery = importWP_find_resource($content);

			$wp_post["ID"] = $db->getField("ID", "Number", true);
			$wp_post["title"] = $db->getField("post_title", "Text", true);
			$wp_post["content"] = array(
				"default" => $short_desc["content"]
				, "content" => preg_replace('#\[[^\]]+\]#', '', $content)
			);
			$wp_post["date"] = $db->getField("post_date_gmt", "Text", true);
			$wp_post["last_update"] = $db->getField("post_modified_gmt", "Text", true);
			$wp_post["author"] = $db->getField("post_author_label", "Text", true);
			$wp_post["name"] = $db->getField("post_name", "Text", true);
			$wp_post["cover"] = $gallery[0];
			$wp_post["gallery"] = implode("<br />", $gallery);
			$wp_post["visible"] = ($db->getField("post_status", "Text", true) == "publish"
										? true
										: false
									);	
			
			if($cats) {
				$arrCategory = importWP_terms("category", "id", $db);
				$post_cats = explode(",", $cats);
				$wp_post["parent"] = $arrCategory[$post_cats[0]];
				foreach($post_cats AS $ID_cat) {
					$wp_post["cats"][$ID_cat] = basename($arrCategory[$ID_cat]);
				}
			}
			if(!$wp_post["parent"]) {
				$wp_post["parent"] = "/senza-categoria";
				$wp_post["cats"][0] = "senza-categoria";
			}

			if($tags) {
				$arrTags = importWP_terms("post_tag", "id", $db);
				$post_tags = explode(",", $tags);
				foreach($post_tags AS $ID_tag) {
					$wp_post["tags"][$ID_tag] = basename($arrTags[$ID_tag]);
				}
			}
		}
	}
	
	return $wp_post;
}

function importWP_fields($type = null, $db = null) {
	check_function("get_short_description");
	
	if(!$db) {
		$db = new ffDB_Sql();
		$db->on_error = "ignore";
		$db->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
	}
	if($db) {
		$fields_default = importWP_post();

		switch($type) {
			case "all":
				$fields = $fields_default;
				break;
			case "noembed":
				$arrWhere[] = "LOCATE('_oembed', wp_postmeta.meta_key) = 0";
				break;
			case "meta":
				$fields["author"] = $fields_default["author"];
				$fields["name"] = $fields_default["name"];
				$fields["title"] = $fields_default["title"];
				$fields["content"] = $fields_default["content"];
				$fields["parent"] = $fields_default["parent"];
				$fields["cover"] = $fields_default["cover"];
				$fields["gallery"] = $fields_default["gallery"];
				$fields["tags"] = $fields_default["tags"];
				$fields["visible"] = $fields_default["visible"];
				$arrWhere[] = "LOCATE('_yoast', wp_postmeta.meta_key) = 1";
				break;
			case "strict":
				$fields["title"] = $fields_default["title"];
				$fields["content"] = $fields_default["content"];
				$fields["author"] = $fields_default["author"];
				$fields["date"] = $fields_default["date"];

				$arrWhere[] = "LOCATE('_', wp_postmeta.meta_key) != 1";
				break;
			default:
				$fields = array_intersect_key($fields_default, $type);
				if(is_array($type))
					$arrWhere[] = "wp_postmeta.meta_key IN('" . implode("','", array_keys($type)) . "')";
					
				$arrWhere[] = "wp_postmeta.meta_value <> ''";
                if($fields_default["ID"] > 0)
                    $arrWhere[] = "wp_posts.ID = " . $db->toSql($fields_default["ID"], "Number");
         
		}
	
		$sSQL = "SELECT wp_postmeta.meta_key 
					, wp_postmeta.meta_value
				FROM wp_postmeta
					INNER JOIN wp_posts ON wp_posts.ID = wp_postmeta.post_id
				WHERE wp_posts.post_type = 'post'"
					. (is_array($arrWhere) && count($arrWhere)
						? " AND " . implode(" AND ", $arrWhere)
						: ""
					) . "
				GROUP BY wp_postmeta.meta_key
				ORDER BY wp_postmeta.meta_key";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$fields[$db->getField("meta_key", "Text", true)] = $db->getField("meta_value", "Text", true);
			} while($db->nextRecord());
		}

	}

    return $fields;
}


function importWP_terms($type = "category", $out = null, $db = null) {
	static $terms = null;
	
	if(!$terms[$type]) 
	{
		if(!$db) {
			$db = new ffDB_Sql();
			$db->on_error = "ignore";
			$db->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
		}

		if($db) {
			$sSQL = "SELECT DISTINCT wp_terms.*
					FROM wp_term_taxonomy
						INNER JOIN wp_terms ON wp_terms.term_id = wp_term_taxonomy.term_id
					WHERE wp_term_taxonomy.taxonomy = " . $db->toSql($type) . "
					ORDER BY wp_terms.term_group";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$terms[$type]["id"][$db->getField("term_id", "Number", true)] = $terms[$type]["id"][$db->getField("term_group", "Number", true)] . "/" . $db->getField("slug", "Text", true);
					$terms[$type]["smart_url"][($db->getField("term_group", "Number", true)
						? $terms[$type]["id"][$db->getField("term_group", "Number", true)]
						: ""
					) . "/" . $db->getField("slug", "Text", true)] = array(
																		"name" => $db->getField("name", "Text", true)
																		, "ID" => $db->getField("term_id", "Number", true)
																	);
				} while($db->nextRecord());
			}
		}
	}
	
	return ($out ? $terms[$type][$out] : $terms[$type]);
}

function importWP_terms_rel_post($ID_post, $db = null) {
	if(!$db) {
		$db = new ffDB_Sql();
		$db->on_error = "ignore";
		$db->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"));
	}

	if($db) {
		$sSQL = "SELECT DISTINCT wp_terms.*
				FROM wp_term_relationships
					INNER JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
				WHERE wp_term_relationships.object_id = " . $db->toSql($ID_post, "Number") . "
				ORDER BY wp_terms.term_group";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
			//da scrivere
			} while($db->nextRecord());
		}
	}

	return null;
}
