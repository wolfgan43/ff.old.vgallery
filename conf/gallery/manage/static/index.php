<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_STATIC_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
if(isset($_REQUEST["repair"])) {
	$db = ffDB_Sql::factory();

	$sSQL = "SELECT static_pages.* 
			FROM static_pages
			WHERE static_pages.name = ''
				AND static_pages.parent = '/'";
	$db->query($sSQL);
	if(!$db->nextRecord()) {
		$sSQL = "INSERT INTO static_pages
				(
					ID
					, name
					, parent
					, owner
					, ID_domain
					, visible
					, meta_title
					, permalink
				)
				VALUES
				(
					null
					, ''
					, '/'
					, '-1'
					, '0'
					, '1'
					, ''
					, '/'				
				)";
		$db->execute($sSQL);
	}
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "static";
$oGrid->title = ffTemplate::_get_word_by_code("static_title");
$oGrid->source_SQL = "SELECT
                            static_pages.ID AS ID
                            , IF(static_pages.name = '', 'Home', static_pages.name) AS name
                            , static_pages.parent
                            , static_pages.visible AS visible
                            , static_pages.name AS smart_url
                            , static_pages.location AS location
                        FROM
                            static_pages
                        WHERE 1
							" . ($globals->ID_domain > 0
								? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
								: ""
							) . "
                        [AND] [WHERE]
                        [HAVING]
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "StaticModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "static_on_before_parse_row");
$oGrid->display_new = AREA_STATIC_SHOW_ADDNEW;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_STATIC_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_STATIC_SHOW_DELETE;
$oGrid->widget_deps[] = array(
	"name" => "labelsort"
	, "options" => array(
	      &$oGrid
	    , array(
	        "resource_id" => "static_dir"
	        , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	    )
	)
);

$oGrid->widget_deps[] = array(
	"name" => "dragsort"
	, "options" => array(
	      &$oGrid
	    , array(
	        "resource_id" => "static_dir"
	        , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	    )
	    , "ID"
	)
);
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "sort, ID";
$oGrid->addKeyField($oField);

// Campi di ricerca


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("static_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("static_parent");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "location";
$oField->label = ffTemplate::_get_word_by_code("static_location");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

if(AREA_SEO_SHOW_MODIFY) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "seo";
    $oButton->form_action_url = ""; //impostato nell'evento
    $oButton->jsaction = "";
    $oButton->aspect = "link";
	//$oButton->image = "seo.png";
	$oButton->label = ffTemplate::_get_word_by_code("seo");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}
if(ENABLE_STD_PERMISSION) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "permissions"; 
    $oButton->form_action_url = ""; //impostato nell'evento
    $oButton->jsaction = "";
    $oButton->aspect = "link";
	//$oButton->image = "permissions.png";
	$oButton->label = ffTemplate::_get_word_by_code("permissions");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

$cm->oPage->addContent($oGrid);

function static_on_before_parse_row($component) {
	$cm = cm::getInstance();

	if($component->db[0]->getField("smart_url", "Text", true)) {
		$component->display_delete_bt = true;
	} else {
		$component->display_delete_bt = false;
	}
	
	
	if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit"; 
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
            }   
	    } else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit";     
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible";
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
	                , "url" => FF_SITE_PATH . VG_SITE_RESTRICTED . "/seo/page/modify"
	                            . "?key=" . $component->key_fields["ID"]->getValue() 
	                            . "&ret_url=" . urlencode($component->parent[0]->getRequestUri())
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
	if(isset($component->grid_buttons["permissions"])) {
	    if($component->grid_buttons["permissions"]->action_type == "submit") {
	        $cm->oPage->widgetLoad("dialog");
	        $cm->oPage->widgets["dialog"]->process(
	             $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue()
	             , array(
	                "tpl_id" => $component->id
	                //"name" => "myTitle"
	                , "url" => FF_SITE_PATH . $cm->oPage->page_path . $cm->real_path_info . "/permission"
	                        . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                        . "&" . $component->addit_record_param
	                        . "ret_url=" . urlencode($component->parent[0]->getRequestUri())
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
?>