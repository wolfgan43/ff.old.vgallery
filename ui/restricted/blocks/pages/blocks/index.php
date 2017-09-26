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
if($_REQUEST["grp"] ||  system_ffcomponent_switch_by_path(__DIR__/*, false*/))
{
	$db = ffDB_Sql::factory();
	if(check_function("system_get_sections"))
		$block_type = system_get_block_type();	

	if(!$cm->real_path_info && !$_REQUEST["grp"])
		$show_type = true;
	
	$path_info = $cm->real_path_info;
	if($cm->real_path_info == "/home")
		$cm->real_path_info = "/";

	if($show_type)
		$cm->oPage->addContent(null, true, "rel");

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "structure";
	$oGrid->source_SQL = "SELECT layout.* 
								, IF(layout.smart_url = ''
									, CONCAT('L', layout.ID)
									, layout.smart_url
								) AS block_key
								, (SELECT 
									GROUP_CONCAT(
										CONCAT(
												IF(rules.visible
													, '" . cm_getClassByFrameworkCss("eye", "icon-tag") . "'
													, '" . cm_getClassByFrameworkCss("eye-slash", "icon-tag") . "'
												)
												, rules.path
												, IF(rules.cascading
													, '*'
													, ''
											)
										) SEPARATOR '<br />'
									)
								FROM layout_path AS rules
								WHERE rules.ID_layout = layout.ID
								) AS pages
								, (CASE layout.ID_type
									WHEN " . $db->toSql($block_type["gallery"]["ID"], "Number") . " THEN layout.value
									WHEN " . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . " THEN layout.params
									WHEN " . $db->toSql($block_type["publishing"]["ID"], "Number") . " THEN layout.value
								END) AS appearance_path
								, (CASE layout.ID_type
									WHEN " . $db->toSql($block_type["gallery"]["ID"], "Number") . " THEN 'files'
									WHEN " . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . " THEN IF(layout.value = 'anagraph', 'anagraph', 'vgallery_nodes')
									WHEN " . $db->toSql($block_type["publishing"]["ID"], "Number") . " THEN 'publishing'
								END) AS appearance
								FROM layout 
								INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
							WHERE " . ($path_info
									? $db->toSql($cm->real_path_info) . " LIKE CONCAT(layout_path.ereg_path, IF(layout_path.cascading, '%', ''))"
									: "1"
								) . "
							[AND] [WHERE] 
							GROUP BY layout.ID
							[HAVING] 
							[ORDER]";
	
	$oGrid->order_default = "ID";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . (basename($cm->oPage->page_path) == "blocks" ? "" : "/blocks") . "/modify";
	$oGrid->record_id = "LayoutModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->addEvent("on_before_parse_row", "blocks_on_before_parse_row");
	$oGrid->setWidthDialog("large");
	
	/**
	* Title
	*/
	system_ffcomponent_set_title(
		null
		, array(
			"name" => "cubes"
		)
		, false
		, false
		, $oGrid
	);

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_dir = "DESC";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->label = ffTemplate::_get_word_by_code("block_id");
	$oField->base_type = "Number";
	$oGrid->addContent($oField);
	
	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("block_name");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "block_key";
	$oField->label = ffTemplate::_get_word_by_code("block_key");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "pages";
	$oField->label = ffTemplate::_get_word_by_code("block_pages");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_location";
	$oField->label = ffTemplate::_get_word_by_code("block_ID_location");
	$oField->extended_type = "Selection";
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT ID, name FROM layout_location";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "tblsrc";
	$oField->data_source = "ID_type";
	$oField->label = ffTemplate::_get_word_by_code("block_ID_type");
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT
	                           layout_type.ID
	                           , layout_type.description
	                       FROM
	                            layout_type";
	$oGrid->addContent($oField);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "appearance";
	$oButton->ajax = $oGrid->record_id;
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . (basename($cm->oPage->page_path) == "blocks" ? "" : "/blocks") . "/appearance?extype=[appearance_VALUE]&path=[appearance_path_VALUEPATH]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("static_appearance");
	$oButton->icon = cm_getClassByFrameworkCss("object-group fa", "icon-tag");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);	
	
	if($show_type) 
	{
		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("block_title"))); 

		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "Extras";
		$oGrid->source_SQL = "SELECT
		                            settings_thumb.ID
		                            , settings_thumb.tbl_src
		                            , IF(tbl_src = 'vgallery_nodes'
		                                    , (SELECT CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM vgallery_nodes WHERE ID = items)
		                                    , IF(tbl_src = 'files'
		                                        , (SELECT CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM files WHERE ID = items)
		                                        , IF(tbl_src = 'publishing'
		                                            , (SELECT name AS path FROM publishing WHERE ID = items)
		                                            , IF(tbl_src = 'anagraph'
		                                                , IF(items > 0
		                                                    , (SELECT CONCAT('/', name) AS path FROM anagraph_categories WHERE ID = items)
		                                                    , '/'
		                                                )
		                                                , items
		                                            )
		                                            
		                                        )
		                                    )
		                            ) AS new_items
		                            , (SELECT name FROM layout WHERE settings_thumb.ID_layout = layout.ID) AS new_layout
		                             
		                        FROM
		                            settings_thumb
		                        [WHERE]
		                        [ORDER]";

		$oGrid->order_default = "ID";
		$oGrid->use_search = false;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/appearance/modify";
		$oGrid->record_id = "ExtrasModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->setWidthDialog("large");

		/**
		* Title
		*/
	/*	system_ffcomponent_set_title(
			null
			, array(
				"name" => "object-group"
			)
			, false
			, false
			, $oGrid
		);*/
		
		// Campi chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Campi visualizzati
		$oField = ffField::factory($cm->oPage);
		$oField->id = "tblsrc";
		$oField->data_source = "tbl_src";
		$oField->label = ffTemplate::_get_word_by_code("extras_tbl_src");
		$oField->multi_pairs = array (
		                            array(new ffData("files"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
		                            array(new ffData("vgallery_nodes"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
		                            array(new ffData("publishing"), new ffData(ffTemplate::_get_word_by_code("publishing"))),
		                            array(new ffData("search"), new ffData(ffTemplate::_get_word_by_code("search"))),
		                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph")))
		                       );      
		$oGrid->addContent($oField);
		                    
		$oField = ffField::factory($cm->oPage);
		$oField->id = "items";
		$oField->data_source = "new_items";
		$oField->label = ffTemplate::_get_word_by_code("extras_items");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "layout";
		$oField->data_source = "new_layout";
		$oField->label = ffTemplate::_get_word_by_code("extras_layout");
		$oGrid->addContent($oField);

		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("extras"))); 	
		
		
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "layoutType";
		//$oGrid->title = ffTemplate::_get_word_by_code("layout_type_title");
		$oGrid->source_SQL = "SELECT * FROM layout_type [WHERE] [ORDER]";
		$oGrid->order_default = "description";
		$oGrid->use_search = false;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/type/modify";
		$oGrid->record_id = "LayoutTypeModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_new = false;
		$oGrid->buttons_options["export"]["display"] = false;
		$oGrid->use_paging = false;

		// Campi chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Campi visualizzati
		$oField = ffField::factory($cm->oPage);
		$oField->id = "description";
		$oField->label = ffTemplate::_get_word_by_code("layout_type_name");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "frequency";
		$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_frequency");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
		                            array(new ffData("always"), new ffData(ffTemplate::_get_word_by_code("always"))),
		                            array(new ffData("hourly"), new ffData(ffTemplate::_get_word_by_code("hourly"))),
		                            array(new ffData("daily"), new ffData(ffTemplate::_get_word_by_code("daily"))),
		                            array(new ffData("weekly"), new ffData(ffTemplate::_get_word_by_code("weekly"))),
		                            array(new ffData("monthly"), new ffData(ffTemplate::_get_word_by_code("monthly"))),
		                            array(new ffData("yearly"), new ffData(ffTemplate::_get_word_by_code("yearly"))),
		                            array(new ffData("never"), new ffData(ffTemplate::_get_word_by_code("never")))
		                       ); 
		$oField->required = true;
		$oGrid->addContent($oField);
		
		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("layout_type_title"))); 
	} else {
	    $oRecord = ffRecord::factory($cm->oPage);
	    $oRecord->ajax = true;
	    $oRecord->id = "structureModify";
	    $oRecord->resources[] = $oRecord->id;
	    //$oRecord->tab = false;
		$oRecord->skip_action = true;
		$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("update");
		$oRecord->addEvent("on_done_action", "structureModify_on_done_action");
		check_function("system_layer_gallery");
		
		if($path_info) {
			$oRecord->user_vars["tpl_name"] = str_replace("/", "_", trim($path_info, "/"));
			$oRecord->user_vars["tpl_custom"] = (is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages/" . $oRecord->user_vars["tpl_name"] . ".html")
				? $oRecord->user_vars["tpl_name"] . ".html"
				: null
			);
			$oRecord->user_vars["tpl_custom_alt"] = (is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages/" . $oRecord->user_vars["tpl_name"] . ".bkp")
				? $oRecord->user_vars["tpl_name"] . ".bkp"
				: null
			);
		}
		if(!$oRecord->user_vars["tpl_custom"])
			//$oRecord->framework_css["actions"]["class"] = "actions hidden";
		
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "is_custom";
		$oField->label = ffTemplate::_get_word_by_code("structure_custom");
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->base_type = "Number";
		$oField->control_type = "checkbox";
		$oField->extended_type = "Boolean";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = ($oRecord->user_vars["tpl_custom"]
	    	? $oField->checked_value
	    	: $oField->unchecked_value
		);
		$oRecord->addContent($oField);	
		
		$group_custom = "custom";
		$oRecord->addContent(null, true, $group_custom); 

		$oRecord->groups[$group_custom] = array(
			"title" => ffTemplate::_get_word_by_code("structure_custom_title")
			, "class" => "custom" . ($oRecord->user_vars["tpl_custom"] ? "" : " hidden")
		);

		$template = system_process_page(array(
			"no_content" => true
			, "output" => "array"	
			, "settings_path" => $cm->real_path_info
		));

		if($template) {
			$oRecord->addContent('<pre class="' . cm_getClassByFrameworkCss(array(3), "col"). '" style="height:400px;">' .structure_parse_tpl_vars($template["buffer"]) . '</pre>', $group_custom);
		}

		$html_wizard = system_process_page(array(
			"no_content_block" => true
			, "output" => "html"	
			, "settings_path" => $cm->real_path_info
		));

		if($oRecord->user_vars["tpl_custom"]) {
			$html = str_replace(array("\n", "<br />"), array("", "\n"), file_get_contents(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages/" . $oRecord->user_vars["tpl_custom"]));
		} elseif($oRecord->user_vars["tpl_custom_alt"]) {
			$html = str_replace(array("\n", "<br />"), array("", "\n"), file_get_contents(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages/" . $oRecord->user_vars["tpl_custom_alt"]));
		} else {
			$html = $html_wizard;
		}		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "html";
		//$oField->label = ffTemplate::_get_word_by_code("block_modify_css");
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->editarea_syntax = "html";
		$oField->default_value = new ffData(Html::indent($html));
		$oField->setWidthComponent("9");
		$oField->properties["style"]["height"] = "400px";
		if(check_function("set_field_textarea"))
			$oField = set_field_textarea($oField);
		$oRecord->addContent($oField, $group_custom);	
			
		$group_wizard = "wizard";
		$oRecord->addContent(null, true, $group_wizard); 

		$oRecord->groups[$group_wizard] = array(
			"title" => ffTemplate::_get_word_by_code("structure_wizard_title")
			, "class" => "wizard" . ($oRecord->user_vars["tpl_custom"] ? " hidden" : "")
		);
		
		//$oRecord->addContent($oGrid, $group_wizard);
		//$cm->oPage->addContent($oGrid);
		$html_wizard = str_replace(array("{", "}"), array('<a href="javascript:void(0);">{', '}</a>'), $html_wizard);
		
		$oRecord->addContent('<div class="workground" data-path="' . $cm->real_path_info . '">' . $html_wizard . '<div>', $group_wizard);
	
	
	
		if($_REQUEST["grp"]) {
			$cm->oPage->addContent($oRecord, $_REQUEST["grp"], null, array("title" => ffTemplate::_get_word_by_code($_REQUEST["grp"] . "_" . $oGrid->id)));
		} else {
			$cm->oPage->addContent($oRecord);
		}	
		
		$cm->oPage->tplAddJs("ff.cms.admin.block");
	}
}



	
function structure_parse_tpl_vars($tpl_vars, $father = null, $tpl = null) {
	if(is_array($tpl_vars) && count($tpl_vars)) {
		$title = ($tpl ? false : true);
		foreach($tpl_vars AS $key => $value) {
			$key_name = ($father ? $father . ":" : "") . ($title && $key != "container" ? "" : $key);
			if($key_name == "container")
				$key_name = "contents";

			if($title)
				$tpl .= '<h4>' . ffTemplate::_get_word_by_code($key) . '</h4>';
			if(is_array($value)) {
				if($key_name)
					$tpl .= '<li><code class="sel">{' . $key_name . '}</code></li>';
				$tpl .= "<ul>";
				$tpl = structure_parse_tpl_vars($value, $key_name, $tpl);
				$tpl .= "</ul>";
			} else {
				$tpl .= '<li><code class="sel">{' . $key_name . '}</code></li>';
			}		
		}
	}
	return $tpl;
}
	
	
function structureModify_on_done_action($component, $action) {
	switch($action) {
		case "insert":
			if(isset($component->form_fields["html"])) {
				check_function("write_custom_template");
				$file = $component->user_vars["tpl_custom"];
				$content = html_entity_decode($component->form_fields["html"]->getValue());
				if($component->form_fields["is_custom"]->getValue()) {
					$action = "update";	
				} else {
					$action = "delete";	
				}

				$strError = write_custom_template($component->user_vars["tpl_name"], $content, $action, FF_THEME_DIR . "/" . FRONTEND_THEME . "/pages");
			}
			return true;
			//break;
		
	}
}	

function blocks_on_before_parse_row($component) {
	$cm = cm::getInstance();

	if(isset($component->grid_fields["block_key"])) {
		$component->grid_fields["block_key"]->setValue('<code class="sel">{' . $component->grid_fields["block_key"]->getValue() . '}</code>');
	}
	
	if(isset($component->grid_buttons["appearance"])) {
	    if($component->db[0]->getField("appearance", "Text", true)) {
	    	$component->grid_buttons["appearance"]->display = true;
		} else{
			$component->grid_buttons["appearance"]->display = false;
		}
	}	
}

class Html {
	private static $_indent = "  ";

	private static function indentStr($indentlevel = 0){
		$replaceindent = null;

		//Sets the indentation from current indentlevel
		for($o = 0; $o < $indentlevel; $o++) {
		    $replaceindent .= self::$_indent;
		}

		return $replaceindent;
	}

	public static function indent($uncleanhtml) {   
		// Seperate tags
		$uncleanhtml_array = explode("<", $uncleanhtml);
		$uncleanhtml_array = array_filter($uncleanhtml_array);
		foreach($uncleanhtml_array as $unfixedtextkey => $unfixedtextvalue) {
		    if(!trim($unfixedtextvalue)){
		        continue;
		    }

		    $unfixedtextvalue = '<' . trim($unfixedtextvalue);

		    //Makes sure empty lines are ignores
		    if(!preg_match("/^(\s)*$/", $unfixedtextvalue)) {
		        $fixedtextvalue = preg_replace("/>(\s|\t)*</U", ">\n<", $unfixedtextvalue);
		        $uncleanhtml_array[$unfixedtextkey] = $fixedtextvalue;
		    }

		}

		//Sets no indentation
		$indentlevel = 0;
		foreach($uncleanhtml_array as $uncleanhtml_key => $currentuncleanhtml) {
		    //Removes all indentation
		    $currentuncleanhtml = preg_replace("/\t+/", "", $currentuncleanhtml);
		    $currentuncleanhtml = preg_replace("/^\s+/", "", $currentuncleanhtml);

		    $replaceindent = self::indentStr($indentlevel);

		    //If self-closing tag, simply apply indent
		    if(preg_match("/<(.+)\/>/", $currentuncleanhtml)) { 
		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		    } else if(preg_match("/<!(.*)>/", $currentuncleanhtml)) { 
		        //If doctype declaration, simply apply indent
		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		    } else if(preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && preg_match("/<\/(.*)>/", $currentuncleanhtml)) {
		        //If opening AND closing tag on same line, simply apply indent 
		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		    } else if(preg_match("/<\/(.*)>/", $currentuncleanhtml) || preg_match("/^(\s|\t)*\}{1}(\s|\t)*$/", $currentuncleanhtml)) {
		        //If closing HTML tag or closing JavaScript clams, decrease indentation and then apply the new level
		        $indentlevel--;
		        $replaceindent = self::indentStr($indentlevel);

		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		    } else if((preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && !preg_match("/<(link|meta|base|br|img|hr|\?)(.*)>/", $currentuncleanhtml)) || preg_match("/^(\s|\t)*\{{1}(\s|\t)*$/", $currentuncleanhtml)) {
		        //If opening HTML tag AND not a stand-alone tag, or opening JavaScript clams, increase indentation and then apply new level
		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;

		        $indentlevel++;
		        $replaceindent = self::indentStr($indentlevel);
		    } else{
		        //Else, only apply indentation
		        $cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		    }
		}

		//Return single string seperated by newline
		return implode("\n", $cleanhtml_array); 
	}
}
