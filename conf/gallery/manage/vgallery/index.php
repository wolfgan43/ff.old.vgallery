<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_VGALLERY_SHOW_MODIFY || AREA_VGALLERY_TYPE_SHOW_MODIFY || AREA_VGALLERY_SELECTION_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

if(isset($_REQUEST["repair"])) {
	$sSQL = "SELECT vgallery.*
			FROM vgallery
			WHERE 1";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		do {
			$arrVgallery[$db_gallery->getField("ID", "Number", true)] = "/" . $db_gallery->getField("name", "Text", true);
		} while($db_gallery->nextRecord());
	}

	if(is_array($arrVgallery) && count($arrVgallery)) {
		foreach($arrVgallery AS $ID_vgallery => $parent) {
			$sSQL = "SELECT vgallery_nodes.ID 
	            	FROM vgallery_nodes 
	            	WHERE vgallery_nodes.name = " . $db_gallery->toSql(basename($parent)) . " 
	            		AND vgallery_nodes.parent = '/'
	            		AND vgallery_nodes.public = 0
	            	ORDER BY IF(vgallery_nodes.ID_vgallery <> " . $ID_vgallery . "
	            		, 9999
	            		, vgallery_nodes.ID
	            	)";
	        $db_gallery->query($sSQL);
	        if($db_gallery->numRows() > 1) {
		        if($db_gallery->nextRecord()) {
		     		while($db_gallery->nextRecord()) {
		     			$arrDelNode[] = $db_gallery->getField("ID", "Number", true);
		     		};   
				}
			}
			$sSQL = "UPDATE vgallery_nodes SET
						ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number") . "
					WHERE 
						parent LIKE '" . $db_gallery->toSql($parent, "Text", false) . "%'
						AND ID_vgallery <> " . $db_gallery->toSql($ID_vgallery, "Number");
			$db_gallery->execute($sSQL);
		}
		if(is_array($arrDelNode) && count($arrDelNode)) {
			$sSQL = "DELETE FROM vgallery_nodes WHERE ID IN(" . $db_gallery->toSql(implode(", ", $arrDelNode), "Text", true) . ")";
			$db_gallery->execute($sSQL);
		}
	}
}


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true; 
$oGrid->id = "VGallery";
//$oGrid->title = ffTemplate::_get_word_by_code("vgallery");
$oGrid->source_SQL = "SELECT tbl_src.*
					FROM (
						SELECT 
                            vgallery.ID
                            , vgallery.name AS name
                            , vgallery.public AS public
                            , vgallery.public_cover AS public_cover
                            , vgallery_nodes.visible AS status
                            , CONCAT(
                            	IF(vgallery.public > 0
                            		, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            		, ''
                            	)
                            	, vgallery_nodes.name
                            	, ' ('
                            	, IFNULL(
                            		(SELECT count(nodes.ID) FROM vgallery_nodes AS nodes WHERE nodes.parent LIKE CONCAT('/', vgallery.name, '%') AND nodes.is_dir = 0 )
                            		, 0
                            	)
                            	, ')'
                            ) AS display_name
                            , vgallery_nodes.ID AS ID_vgallery_nodes
                            , 'vgallery' AS src_type
                        FROM 
                            vgallery  
                            LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID AND vgallery_nodes.parent = '/'
                        WHERE 1
                        ORDER BY vgallery.ID, LENGTH(vgallery_nodes.name) ASC
                    ) AS tbl_src
                    [WHERE]
                    [HAVING]
                    [ORDER] ";
                    //                    GROUP BY tbl_src.ID

$oGrid->order_default = "display_name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "VGalleryModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_VGALLERY_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_VGALLERY_SHOW_MODIFY;
$oGrid->display_new = AREA_VGALLERY_SHOW_MODIFY;
$oGrid->addEvent("on_before_parse_row", "VGallery_on_before_parse_row");
//NOT(vgallery.public > 0)
//$oGrid->addit_record_param = "type=node&vname=[name_VALUE]&path=/[name_VALUE]&extype=vgallery_nodes&";
// Ricerca

// Chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_vgallery_nodes";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Visualizzazione
$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_name");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "database";
$oButton->action_type = "gotourl";
//$oButton->url = FF_SITE_PATH . $cm->oPage->page_path . "/nodes/[name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("vgallery_nodes");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

if(AREA_SEO_SHOW_MODIFY) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "seo";
    $oButton->form_action_url = ""; //impostato nell'evento
    $oButton->jsaction = "";
    $oButton->aspect = "link";
    //$oButton->image = "seo.png";
    $oButton->label = ffTemplate::_get_word_by_code("seo");
	$oButton->display_label = false;
    $oGrid->addGridButton($oButton);
}

if(AREA_VGALLERY_SHOW_PERMISSION && ENABLE_STD_PERMISSION) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "permissions"; 
    if(1 || $_REQUEST["XHR_CTX_ID"]) {
        $oButton->form_action_url = ""; //impostato nell'evento
        $oButton->jsaction = "";
    } else {
	    $oButton->action_type = "gotourl";
	    //$oButton->url = FF_SITE_PATH . $cm->oPage->page_path . "/nodes/[name_VALUE]" . "/permission?keys[ID]=[ID_vgallery_nodes_VALUE]&" . $oGrid->addit_record_param . "ret_url=" . urlencode($cm->oPage->getRequestUri());
	}
    $oButton->aspect = "link";
	//$oButton->image = "permissions.png";
	$oButton->label = ffTemplate::_get_word_by_code("permissions");
	$oButton->display_label = false;
    $oGrid->addGridButton($oButton);
}

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "status";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("status");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

if(MASTER_CONTROL) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "public";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
}

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery"))); 


if (AREA_VGALLERY_TYPE_SHOW_MODIFY) {
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true; 
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->id = "vgalleryType";
    //$oGrid->title = ffTemplate::_get_word_by_code("vgallery_type");
    $oGrid->source_SQL = "SELECT tbl_src.*
    					  	FROM
    						(
    							(
    								SELECT
		                                vgallery_type.ID
		                                , vgallery_type.public_cover
		                                , vgallery_type.public_description
		                                , vgallery_type.public_link_doc
			                            , CONCAT(
                            				IF(vgallery_type.public > 0
                            					, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            					, ''
                            				)
                            				, vgallery_type.name
			                            ) AS name
			                            , 'vgallery' AS src_type		                            
		                            FROM 
		                                vgallery_type
		                            WHERE " . (OLD_VGALLERY 
		                                    ? "vgallery_type.name <> 'System'"
		                                    : "1"
		                                ) . "
	                            ) UNION (
    								SELECT
		                                anagraph_type.ID
		                                , anagraph_type.public_cover
		                                , anagraph_type.public_description
		                                , anagraph_type.public_link_doc
			                            , CONCAT(
                            				IF(anagraph_type.public > 0
                            					, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            					, ''
                            				)
                            				, anagraph_type.name
			                            ) AS name
										, 'anagraph' AS src_type		                            
		                            FROM 
		                                anagraph_type
	                            )
	                        ) AS tbl_src
                            [AND]
                            [WHERE]
                            [HAVING]
                            [ORDER] ";

    $oGrid->order_default = "name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/type/extra";
    $oGrid->record_id = "VGalleryTypeModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = AREA_VGALLERY_TYPE_SHOW_MODIFY;
    $oGrid->display_delete_bt = AREA_VGALLERY_TYPE_SHOW_MODIFY;
    $oGrid->display_new = AREA_VGALLERY_SHOW_TYPE_MODIFY;
    $oGrid->addEvent("on_before_parse_row", "vgalleryType_on_before_parse_row");

    // Ricerca

    // Chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    // Visualizzazione
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("vgallery_type_name");
    $oField->encode_entities = false;
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "src_type";
    $oField->label = ffTemplate::_get_word_by_code("vgallery_type_src");
    $oField->encode_entities = false;
    $oGrid->addContent($oField);
    
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "clone"; 
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_clone");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
  
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "group";
	$oButton->aspect = "link";
    if(1 || $_REQUEST["XHR_CTX_ID"]) {
        $oButton->form_action_url = ""; //impostato nell'evento
        $oButton->jsaction = "";
    } else {
	    $oButton->action_type = "gotourl";
	    //$oButton->url = FF_SITE_PATH . $cm->oPage->page_path . "/nodes/[name_VALUE]" . "/permission?keys[ID]=[ID_vgallery_nodes_VALUE]&" . $oGrid->addit_record_param . "ret_url=" . urlencode($cm->oPage->getRequestUri());
	}
    $oButton->aspect = "link";
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
	
    if(MASTER_CONTROL) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "public";
	    $oButton->action_type = "gotourl";
	    $oButton->url = "";
	    $oButton->aspect = "link";
		$oButton->display_label = false;
	    $oGrid->addGridButton($oButton);
	}

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_type"))); 
}

if (AREA_VGALLERY_SELECTION_SHOW_MODIFY) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true; 
	$oGrid->id = "vgallerySelection";
	//$oGrid->title = ffTemplate::_get_word_by_code("vgallery_selection");
	$oGrid->source_SQL = "SELECT vgallery_fields_selection.ID
	                            , vgallery_fields_selection.name
	                            , (SELECT GROUP_CONCAT(vgallery_fields_selection_value.name SEPARATOR ', ') FROM vgallery_fields_selection_value WHERE vgallery_fields_selection_value.ID_selection = vgallery_fields_selection.ID ORDER BY vgallery_fields_selection_value.`order`, vgallery_fields_selection_value.name) AS multi_value  
	                        FROM vgallery_fields_selection
	                        [WHERE]
	                        [HAVING]
	                        [ORDER] ";

	$oGrid->order_default = "name";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/modify";
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

if(AREA_VGALLERY_GROUP_SHOW_MODIFY) {
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "vgalleryGroup";
    //$oGrid->title = ffTemplate::_get_word_by_code("vgallery_group");
    $oGrid->source_SQL = "SELECT 
                                vgallery_groups.ID
                                , vgallery_groups.name
                            FROM 
                                vgallery_groups
                            [WHERE]
                            [HAVING]
                            [ORDER] ";

    $oGrid->order_default = "name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify";
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



if(AREA_VGALLERY_HTMLTAG_SHOW_MODIFY) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "vgalleryHtmlTag";
	//$oGrid->title = ffTemplate::_get_word_by_code("vgallery_htmltag");
	$oGrid->source_SQL = "SELECT 
								vgallery_fields_htmltag.*
	                        FROM 
	                            vgallery_fields_htmltag
	                        [WHERE]
	                        [HAVING]
	                        [ORDER] ";

	$oGrid->order_default = "tag";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/htmltag/modify";
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
    
    
function VGallery_on_before_parse_row($component) {
    $cm = cm::getInstance();
	$component->addit_record_param = "type=node&vname=" . $component->db[0]->getField("name", "Text", true) . "&path=/" . $component->db[0]->getField("name", "Text", true) . "&extype=vgallery_nodes&src=" . $component->db[0]->getField("src_type", "Text", true) . "&";

	if(isset($component->grid_fields["name"]) && check_function("get_update_by_service")) {
		$component->grid_fields["name"]->setValue(interface_set_public_field($component->grid_fields["name"], $component->db[0]));
	}

    if(isset($component->grid_buttons["database"])) {
    	if($component->db[0]->getField("public", "Number", true)) {
    	    $component->grid_buttons["database"]->visible = false;
		} else {
			$component->grid_buttons["database"]->url = FF_SITE_PATH . $cm->oPage->page_path . "/nodes/" . $component->db[0]->getField("name", "Text", true);
			$component->grid_buttons["database"]->visible = true;
		}
	}
	if(isset($component->grid_buttons["status"])) {
	    if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit"; 
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=0";
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["status"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["status"]->action_type = "gotourl";
                //$component->grid_buttons["status"]->url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=0&frmAction=setstatus";
            }   
	    } else {
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit";     
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=1";
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["status"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["status"]->action_type = "gotourl";
                //$component->grid_buttons["status"]->url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=1&frmAction=setstatus";
            }    
	    }
	}
	
    if(isset($component->grid_buttons["seo"])) {
         if($component->grid_buttons["seo"]->action_type == "submit") {
            $cm->oPage->widgetLoad("dialog");
            $cm->oPage->widgets["dialog"]->process(
                 $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue()
                 , array(
                    "tpl_id" => $component->id
                    //"name" => "myTitle"
                    , "url" => FF_SITE_PATH . VG_SITE_ADMIN . "/utility/seo/modify"
                            . "?key=" . $component->key_fields["ID_vgallery_nodes"]->getValue() 
                    , "title" => ffTemplate::_get_word_by_code("seo")
                    , "callback" => ""
                    , "class" => ""
                    , "params" => array()
                )
                , $cm->oPage
            );
            $component->grid_buttons["seo"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue() . "')";
        }
        $component->grid_buttons["seo"]->visible = true;
    } 
    
    if(isset($component->grid_buttons["public"])) {
    	if(!$component->db[0]->getField("is_clone", "Number", true)) {
		    if($component->db[0]->getField("public", "Number", true)) {
		        //$component->grid_buttons["public"]->image = "visible.png"; 
                $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon");
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_no_public");
	            $component->grid_buttons["public"]->action_type = "submit"; 
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0";
	            if($_REQUEST["XHR_CTX_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&frmAction=setpublic";
	            }   
		    } else {
	            //$component->grid_buttons["public"]->image = "notvisible.png";
	            $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon", array("transparent"));
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_public");
                $component->grid_buttons["public"]->action_type = "submit";     
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1";
	            if($_REQUEST["XHR_CTX_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&frmAction=setpublic";
	            }    
		    }
		    $component->grid_buttons["public"]->visible = true;
		} else {
			$component->grid_buttons["public"]->visible = false;
		}
	}

	if(isset($component->grid_buttons["permissions"])) {
	    if($component->grid_buttons["permissions"]->action_type == "submit") {
	        $cm->oPage->widgetLoad("dialog");
	        $cm->oPage->widgets["dialog"]->process(
	             $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue()
	             , array(
	                "tpl_id" => $component->id
	                //"name" => "myTitle"
	                , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/nodes/" . $component->db[0]->getField("name", "Text", true) . "/permission"
	                        . "?keys[ID]=" . $component->key_fields["ID_vgallery_nodes"]->getValue() 
	                        . "&type=node"
	                        . "&vname=" . urlencode($component->db[0]->getField("name", "Text", true))
	                        . "&path=" . urlencode("/" . $component->db[0]->getField("name", "Text", true))
	                        . "&extype=vgallery_nodes" 
	                , "title" => ffTemplate::_get_word_by_code("permissions")
	                , "callback" => ""
	                , "class" => ""
	                , "params" => array()
	            )
	            , $cm->oPage
	        );
	        $component->grid_buttons["permissions"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue() . "')";
	    }
		$component->grid_buttons["permissions"]->visible = true;
	}
}


function vgalleryType_on_before_parse_row($component) {
	$cm = cm::getInstance();
	$component->addit_record_param = "src=" . $component->db[0]->getField("src_type", "Text", true) . "&";
    $component->bt_edit_url = $component->record_url . "?keys[ID]=" . $component->db[0]->getField("ID", "Number", true) . "&src=" . $component->db[0]->getField("src_type", "Text", true) ;
    
	if(isset($component->grid_fields["name"]) && check_function("get_update_by_service")) {
		$component->grid_fields["name"]->setValue(interface_set_public_field($component->grid_fields["name"], $component->db[0]));
	}
	
    if($component->db[0]->getField("is_clone", "Number", true) > 0) {
		$component->row_class = "clone";
    } else {
		$component->row_class = "";
    }
    
	if(isset($component->grid_buttons["clone"])) {
	    $component->grid_buttons["clone"]->action_type = "submit"; 
	    $component->grid_buttons["clone"]->form_action_url = $component->grid_buttons["clone"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["clone"]->parent[0]->addit_record_param;
	    if($_REQUEST["XHR_CTX_ID"]) {
	        $component->grid_buttons["clone"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	    } else {
	        $component->grid_buttons["clone"]->jsaction = "ff.ajax.doRequest({'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	        //$component->grid_buttons["visible"]->action_type = "gotourl";
	        //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
		}   
		$component->grid_buttons["clone"]->visible = true;
	}
        
	if(isset($component->grid_buttons["group"])) {
	    if($component->grid_buttons["group"]->action_type == "submit") { 
	        $cm->oPage->widgetLoad("dialog");
	        $cm->oPage->widgets["dialog"]->process(
	             $component->id . "_modifyGroup_" . $component->key_fields["ID"]->getValue()
	             , array(
	                "tpl_id" => $component->id
	                //"name" => "myTitle"
	                , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/type/group"
	                        . "?ID_type=" . $component->key_fields["ID"]->getValue() 
	                , "title" => ffTemplate::_get_word_by_code("type_group_title")
	                , "callback" => ""
	                , "class" => ""
	                , "params" => array()
	            )
	            , $cm->oPage
	        );
	        $component->grid_buttons["group"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyGroup_" . $component->key_fields["ID"]->getValue() . "')";
	    }
		$component->grid_buttons["group"]->visible = true;
	}
	
    if(isset($component->grid_buttons["public"])) {
    	if(!$component->db[0]->getField("is_clone", "Number", true)) {
		    if($component->db[0]->getField("public", "Number", true)) {
		        //$component->grid_buttons["public"]->image = "visible.png";
                $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon");
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_no_public");
	            $component->grid_buttons["public"]->action_type = "submit"; 
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0";
	            if($_REQUEST["XHR_CTX_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&frmAction=setpublic";
	            }   
		    } else { 
	            //$component->grid_buttons["public"]->image = "notvisible.png";
                $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon", array("transparent"));
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_public");
	            $component->grid_buttons["public"]->action_type = "submit";     
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1";
	            if($_REQUEST["XHR_CTX_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&frmAction=setpublic";
	            }    
		    }
		    $component->grid_buttons["public"]->visible = true;
		} else {
			$component->grid_buttons["public"]->visible = false;
		}
	}
}
?>
