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
if (!(AREA_VGALLERY_SHOW_MODIFY || AREA_VGALLERY_TYPE_SHOW_MODIFY || AREA_VGALLERY_SELECTION_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");
check_function("get_vgallery_card");

if(system_ffcomponent_switch_by_path(__DIR__, array("modify", "field"))) {
	if(isset($_REQUEST["repair"])) {
		$sSQL = "UPDATE vgallery_type SET
					display_name = REPLACE(name, '-', ' ')
				WHERE display_name = '' AND name <> ''";
		$db->execute($sSQL);

		$sSQL = "SELECT vgallery_type.*
				FROM vgallery_type
				WHERE 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$arrVgalleryType[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
			} while($db->nextRecord());

			if(is_array($arrVgalleryType) && count($arrVgalleryType)) {
				foreach($arrVgalleryType AS $ID_type => $smart_url) {
					$sSQL = "UPDATE vgallery_type SET
								name = " . $db->toSql($smart_url) . "
							WHERE vgallery_type.ID = " . $db->toSql($ID_type, "Number");
					$db->execute($sSQL);				
				}
			}
		}

		$sSQL = "UPDATE anagraph_type SET
					display_name = REPLACE(name, '-', ' ')
				WHERE display_name = '' AND name <> ''";
		$db->execute($sSQL);

		$sSQL = "SELECT anagraph_type.*
				FROM anagraph_type
				WHERE 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$arrAnagraphType[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
			} while($db->nextRecord());

			if(is_array($arrAnagraphType) && count($arrAnagraphType)) {
				foreach($arrAnagraphType AS $ID_type => $smart_url) {
					$sSQL = "UPDATE anagraph_type SET
								name = " . $db->toSql($smart_url) . "
							WHERE anagraph_type.ID = " . $db->toSql($ID_type, "Number");
					$db->execute($sSQL);				
				}
			}
		}
	}

	$cm->oPage->addContent(null, true, "rel"); 

	$oGrid = ffGrid::factory($cm->oPage);
    $oGrid->ajax_addnew = !$disable_dialog;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true; 
	$oGrid->id = "vgalleryType";
	$oGrid->source_SQL = "SELECT tbl_src.*
    					  	FROM
    						(
    							(
    								SELECT
			                            vgallery_type.ID
			                            , vgallery_type.public_cover
			                            , vgallery_type.public_description
			                            , vgallery_type.public_link_doc
                            			, vgallery_type.name
                            			, IF(vgallery_type.display_name = ''
											, REPLACE(vgallery_type.name, '-', ' ')
											, vgallery_type.display_name
										) AS display_name
				                        , 'vgallery' AS src
			                        FROM 
			                            vgallery_type
			                        WHERE vgallery_type.name <> 'System'
			                        	AND vgallery_type.public = 0
		                        ) UNION (
    								SELECT
			                            anagraph_type.ID
			                            , anagraph_type.public_cover
			                            , anagraph_type.public_description
			                            , anagraph_type.public_link_doc
                            			, CONCAT(anagraph_type.name, '-anagraph') AS name
                            			, IF(anagraph_type.display_name = ''
											, REPLACE(anagraph_type.name, '-', ' ')
											, anagraph_type.display_name
										) AS display_name
										, 'anagraph' AS src
			                        FROM 
			                            anagraph_type
                                    WHERE anagraph_type.public = 0
		                        )
		                    ) AS tbl_src
	                        [AND]
	                        [WHERE]
	                        [HAVING]
	                        [ORDER] ";
	$oGrid->order_default = "display_name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path  . "/add";
	$oGrid->record_id = "VGalleryTypeModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->display_edit_bt = false;
	$oGrid->display_delete_bt = AREA_VGALLERY_TYPE_SHOW_MODIFY;
	$oGrid->display_new = AREA_VGALLERY_SHOW_TYPE_MODIFY;
	$oGrid->addEvent("on_before_parse_row", "vgalleryType_on_before_parse_row");

	/**
	* Title
	*/
	system_ffcomponent_set_title(
		ffTemplate::_get_word_by_code("vgallery_type")
		, true
		, false
		, false
		, $oGrid
	);			
	// Ricerca

	// Chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Visualizzazione
	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("vgallery_type_name");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "src";
	$oField->label = ffTemplate::_get_word_by_code("vgallery_type_src");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "clone"; 
	$oButton->ajax = true;
	$oButton->action_type = "gotourl";
	$oButton->frmAction = "clone";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_clone");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
  
	if(MASTER_CONTROL) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "public";
		$oButton->ajax = true;
		$oButton->action_type = "gotourl";
		$oButton->frmAction = "setpublic";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]&setpublic=1";
		$oButton->aspect = "link";
        $oButton->icon = cm_getClassByFrameworkCss("globe", "icon-tag", "transparent");
		$oButton->display_label = false;
		$oGrid->addGridButton($oButton);
	}

	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type"))); 

	if (AREA_VGALLERY_SELECTION_SHOW_MODIFY) 
    {
		$oGrid = ffGrid::factory($cm->oPage);
	    $oGrid->ajax_addnew = !$disable_dialog;
	    $oGrid->ajax_edit = !$disable_dialog;
	    $oGrid->ajax_delete = true;
	    $oGrid->ajax_search = true; 
		$oGrid->id = "vgallerySelection";
		$oGrid->source_SQL = "SELECT vgallery_fields_selection.ID
		                            , vgallery_fields_selection.name
		                            , (SELECT GROUP_CONCAT(vgallery_fields_selection_value.name SEPARATOR ', ') FROM vgallery_fields_selection_value WHERE vgallery_fields_selection_value.ID_selection = vgallery_fields_selection.ID ORDER BY vgallery_fields_selection_value.`order`, vgallery_fields_selection_value.name) AS multi_value  
		                        FROM vgallery_fields_selection
		                        [WHERE]
		                        [HAVING]
		                        [ORDER] ";
		$oGrid->order_default = "name";
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/[name_VALUE]";
		$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/add";
		$oGrid->record_id = "VGallerySelectionModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_edit_bt = false;
		$oGrid->display_edit_url = AREA_VGALLERY_SELECTION_SHOW_MODIFY;
		$oGrid->display_delete_bt = AREA_VGALLERY_SELECTION_SHOW_MODIFY;
		$oGrid->display_new = AREA_VGALLERY_SELECTION_SHOW_MODIFY;

		// Chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Visualizzazione
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_name");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "multi_value";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_multi_value");
		$oGrid->addContent($oField);

	    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_selection"))); 
	}

	if(AREA_VGALLERY_GROUP_SHOW_MODIFY) 
    {
	    $oGrid = ffGrid::factory($cm->oPage);
	    $oGrid->full_ajax = true;
	    $oGrid->id = "vgalleryGroup";
	    $oGrid->source_SQL = "SELECT 
	                                vgallery_groups.ID
	                                , vgallery_groups.name
	                            FROM 
	                                vgallery_groups
	                            [WHERE]
	                            [HAVING]
	                            [ORDER] ";

	    $oGrid->order_default = "name";
        $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/[name_VALUE]";
        $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/add";
	    $oGrid->record_id = "VGalleryGroupModify";
	    $oGrid->resources[] = $oGrid->record_id;
	    $oGrid->display_edit_bt = false;
	    $oGrid->display_edit_url = AREA_VGALLERY_GROUP_SHOW_MODIFY;
	    $oGrid->display_delete_bt = AREA_VGALLERY_GROUP_SHOW_MODIFY;
	    $oGrid->display_new = AREA_VGALLERY_GROUP_SHOW_MODIFY;

	    // Chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oGrid->addKeyField($oField);

	    // Visualizzazione
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "name";
	    $oField->label = ffTemplate::_get_word_by_code("vgallery_groups_name");
	    $oGrid->addContent($oField);

	    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_group"))); 
	} 



	if(AREA_VGALLERY_HTMLTAG_SHOW_MODIFY) 
    {
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "vgalleryHtmlTag";
		$oGrid->source_SQL = "SELECT 
									vgallery_fields_htmltag.*
		                        FROM 
		                            vgallery_fields_htmltag
		                        [WHERE]
		                        [HAVING]
		                        [ORDER] ";

		$oGrid->order_default = "tag";
        $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/htmltag/[tag_VALUE]";
        $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/htmltag/add";
		$oGrid->record_id = "VGalleryHtmlTagModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_edit_bt = false;
		$oGrid->display_edit_url = AREA_VGALLERY_HTMLTAG_SHOW_MODIFY;
		$oGrid->display_delete_bt = AREA_VGALLERY_HTMLTAG_SHOW_MODIFY;
		$oGrid->display_new = AREA_VGALLERY_HTMLTAG_SHOW_MODIFY;

		// Chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Visualizzazione
		$oField = ffField::factory($cm->oPage);
		$oField->id = "tag";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_tag");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "attr";
		$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_attr");
		$oGrid->addContent($oField);

	    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_htmltag"))); 
	}  
    
    if(MASTER_CONTROL) {
        $oGrid_models = ffGrid::factory($cm->oPage);
        $oGrid_models->ajax_addnew = !$disable_dialog;
        $oGrid_models->ajax_delete = true;
        $oGrid_models->ajax_search = true; 
        $oGrid_models->id = "vgalleryTypeModels";
        $oGrid_models->source_SQL = "SELECT tbl_src.*
                                  FROM
                                (
                                    (
                                        SELECT
                                            vgallery_type.ID
                                            , vgallery_type.public_cover
                                            , vgallery_type.public_description
                                            , vgallery_type.public_link_doc
                                            , vgallery_type.name
                                            , IF(vgallery_type.display_name = ''
											    , REPLACE(vgallery_type.name, '-', ' ')
											    , vgallery_type.display_name
										    ) AS display_name
                                            , 'vgallery' AS src
                                        FROM 
                                            vgallery_type
                                        WHERE vgallery_type.name <> 'System'
                                            AND vgallery_type.public > 0
                                    ) UNION (
                                        SELECT
                                            anagraph_type.ID
                                            , anagraph_type.public_cover
                                            , anagraph_type.public_description
                                            , anagraph_type.public_link_doc
                                            , anagraph_type.name
                                            , IF(anagraph_type.display_name = ''
											    , REPLACE(anagraph_type.name, '-', ' ')
											    , anagraph_type.display_name
										    ) AS display_name
                                            , 'anagraph' AS src
                                        FROM 
                                            anagraph_type
                                        WHERE anagraph_type.public > 0
                                    )
                                ) AS tbl_src
                                [AND]
                                [WHERE]
                                [HAVING]
                                [ORDER] ";
        $oGrid_models->order_default = "display_name";
        $oGrid_models->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
        $oGrid_models->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
        $oGrid_models->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path  . "/add";
        $oGrid_models->record_id = "VGalleryTypeModify";
        $oGrid_models->resources[] = $oGrid_models->record_id;
        $oGrid_models->display_edit_url = AREA_VGALLERY_TYPE_SHOW_MODIFY;
        $oGrid_models->display_delete_bt = AREA_VGALLERY_TYPE_SHOW_MODIFY;
        $oGrid_models->display_new = AREA_VGALLERY_SHOW_TYPE_MODIFY;
        $oGrid_models->addEvent("on_before_parse_row", "vgalleryType_on_before_parse_row");

        // Chiave
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oGrid_models->addKeyField($oField);

        // Visualizzazione
        $oField = ffField::factory($cm->oPage);
        $oField->id = "display_name";
        $oField->label = ffTemplate::_get_word_by_code("vgallery_type_name");
        $oField->data_type = "";
        $oField->encode_entities = false;
        $oGrid_models->addContent($oField);
        
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "clone"; 
        $oButton->ajax = true;
        $oButton->action_type = "gotourl";
        $oButton->frmAction = "clone";
        $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]";
        $oButton->aspect = "link";
        $oButton->label = ffTemplate::_get_word_by_code("vgallery_type_clone");
        $oButton->display_label = false;
        $oGrid_models->addGridButton($oButton);
        
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "public";
        $oButton->ajax = true;
        $oButton->action_type = "gotourl";
        $oButton->frmAction = "setpublic";
        $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]&setpublic=0";
        $oButton->aspect = "link";
        $oButton->icon = cm_getClassByFrameworkCss("globe", "icon-tag");
        $oButton->display_label = false;
        $oGrid_models->addGridButton($oButton);

        $cm->oPage->addContent($oGrid_models, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type_models"))); 
    }    
}    

function vgalleryType_on_before_parse_row($component) {
	$cm = cm::getInstance();
	if(isset($component->grid_fields["display_name"]) && !$component->grid_fields["display_name"]->getValue()) {
		$title = $component->db[0]->getField("display_name", "Text", true);
		$description[] = $component->db[0]->getField("public_description", "Text", true);

		$cover = $component->db[0]->getField("public_cover", "Text", true);
		if(strlen($cover)) {
			if(strpos($cover, "://") === false) {
				if(strpos(CM_SHOWFILES, "://") === false)
					$showfile_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . MASTER_SITE . "/" . CM_SHOWFILES . "/32x32";
				else 
					$showfile_url = CM_SHOWFILES . "/32x32";
			} else {
				$showfile_url = "";
			}
			$cover = $showfile_url . $cover; 
		}
		if($component->db[0]->getField("src", "Text", true) != "vgallery") {
			$description["Type"] = $component->db[0]->getField("src", "Text", true);
		}
		$component->grid_fields["display_name"]->setValue(get_vgallery_card($title, $cover, $description, $component->db[0]->getField("public_link_doc", "Text", true)));
	}	
	
    if($component->db[0]->getField("is_clone", "Number", true) > 0) {
		$component->row_class = "clone";
    } else {
		$component->row_class = "";
    }
}
