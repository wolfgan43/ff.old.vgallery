<?php
use_cache(false);

$permission = check_coudocs_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$category = basename($cm->real_path_info);

$arrPermission = explode(",", $permission["anagraph"]);
if(is_array($arrPermission) && count($arrPermission)) {
	$sSQL_permission = "";
	foreach($arrPermission AS $arrPermission_value) {
		if(strlen($arrPermission_value)) {
			if(strlen($sSQL_permission))
				$sSQL_permission .= " OR ";

			$sSQL_permission .= " FIND_IN_SET(" . $db->toSql($arrPermission_value, "Number") . ", " . CM_TABLE_PREFIX . "mod_cloudocs_docs.customers) 
                                    OR " . CM_TABLE_PREFIX . "mod_cloudocs_docs.customers = ''";
		}
	}

    if(strlen($sSQL_permission)) {
	    $sSQL_permission = " OR  ( $sSQL_permission ) ";
    }
}


$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_cloudocs_category.* 
		FROM " . CM_TABLE_PREFIX . "mod_cloudocs_category 
		WHERE " . CM_TABLE_PREFIX . "mod_cloudocs_category.ID IN(
            SELECT
                DISTINCT " . CM_TABLE_PREFIX . "mod_cloudocs_docs.ID_category
            FROM
                " . CM_TABLE_PREFIX . "mod_cloudocs_docs
            WHERE 
            (
                " . CM_TABLE_PREFIX . "mod_cloudocs_docs.ID_owner = " . $db->toSql($UserNID, "Number") . " 
                $sSQL_permission
            )   
        )
		ORDER BY " . CM_TABLE_PREFIX . "mod_cloudocs_category.name";
$db->query($sSQL);
if($db->nextRecord()) {
	do {
		if(strlen($category) && ffCommon_url_rewrite($db->getField("name")->getValue()) == $category) {
        	$ID_category = $db->getField("ID")->getValue();
        }

		$arrCategory[] = $db->getField("name", "Text", true);
	} while($db->nextRecord());	
}

$arrSpecialCategories = array("all" => " AND 1 "
						);


$filename = cm_cascadeFindTemplate("/contents/docs/menu_category.html", "cloudocs");
/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/menu_category.html", $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/cloudocs/contents/docs/menu_category.html", $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/docs/menu_category.html", $cm->oPage->theme);*/

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("menu_category.html", "main");

$tpl->set_var("site_path", FF_SITE_PATH);
$tpl->set_var("ret_url", urlencode($cm->oPage->getRequestUri()));


if(is_array($arrSpecialCategories) && count($arrSpecialCategories)) {
    foreach($arrSpecialCategories AS $cat_key => $cat_value) {
        $tpl->set_var("item_url", $cm->oPage->page_path . "/" . $cat_key);
        $tpl->set_var("item_name", ffTemplate::_get_word_by_code("cloudocs_category_" . $cat_key));
        if(basename($cm->real_path_info) == $cat_key) {
            $tpl->set_var("selected", ' selected');
        } else {
            $tpl->set_var("selected", '');
        }
        $tpl->parse("SezItem", true);
    }
}

if(is_array($arrCategory) && count($arrCategory)) {
    foreach($arrCategory AS $cat_key => $cat_value) {
        $tpl->set_var("item_url", $cm->oPage->page_path . "/" . ffCommon_url_rewrite($cat_value));
        $tpl->set_var("item_name", ffTemplate::_get_word_by_code($cat_value));
        if(basename($cm->real_path_info) == ffCommon_url_rewrite($cat_value)) {
            $tpl->set_var("selected", ' selected');
        } else {
            $tpl->set_var("selected", '');
        }
        $tpl->parse("SezItem", true);
    }
}

//$cm->oPage->addContent($tpl->rpparse("main", false));


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "docs";
$oGrid->fixed_heading_content = $tpl->rpparse("main", false);
//$oGrid->title = ffTemplate::_get_word_by_code("docs_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_cloudocs_docs.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_cloudocs_docs
                        WHERE 
                        (
                            " . CM_TABLE_PREFIX . "mod_cloudocs_docs.ID_owner = " . $db->toSql($UserNID, "Number") . " 
				            $sSQL_permission                
                        )   
                        " . (strlen($category)
			                ? (array_key_exists($category, $arrSpecialCategories)
			                    ? $arrSpecialCategories[$category]
                                : " AND " . CM_TABLE_PREFIX . "mod_cloudocs_docs.ID_category = " . $db->tosql($ID_category, "Number") 
                            )
                            : ""
                        ) . "
                        " . ($permission["owner"]
                            ? ""
                            : " AND " . CM_TABLE_PREFIX . "mod_cloudocs_docs.status > 0 "
                        ) . " 
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "DocsModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = ($permission["owner"] ? true : false);
$oGrid->display_edit_bt = ($permission["owner"] ? true : false);
$oGrid->display_edit_url = false;
$oGrid->display_delete_bt = ($permission["owner"] ? true : false);
$oGrid->addEvent("on_before_parse_row", "docs_on_before_parse_row"); 

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->container_class = "last-update";
$oField->label = ffTemplate::_get_word_by_code("docs_last_update");
$oField->base_type = "Timestamp";
$oField->extended_type = "DateTime";
$oField->app_type = "DateTime";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("docs_name");
$oField->order_SQL = " last_update DESC, name ";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("docs_description");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->container_class = "category";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("docs_category");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_cloudocs_category ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("cloudocs_no_category");
$oGrid->addContent($oField);

if($permission["owner"]) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "customers";
    $oField->container_class = "customers";
	$oField->label = ffTemplate::_get_word_by_code("docs_customers");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
}
$oButton = ffButton::factory($cm->oPage);
$oButton->id = "download";
$oButton->class = "download";
$oButton->target = "_blank";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("docs_download");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

if($permission["owner"]) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "status";
    $oButton->action_type = "gotourl";
    $oButton->url = "";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("docs_status");
    $oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

$cm->oPage->addContent($oGrid);


function docs_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
	if(isset($component->grid_fields["customers"])) {
		$customers = $component->db[0]->getField("customers", "Text", true);
		if(strlen($customers)) {
			$arrCustomers = array();

			if(check_function("get_user_data"))
				$Fname_sql = get_user_data("Fname", "anagraph", null, false);
			
			$sSQL = "SELECT 
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
				    WHERE anagraph.ID IN(" . $db->toSql($customers, "Text", false) . ")
				    ORDER BY Fname";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$arrCustomers[] = '<li class="customer">' . $db->getField("Fname", "Text", true) . '</li>';
				} while($db->nextRecord());
			}
			
			$component->grid_fields["customers"]->setValue('<div class="list-customer"><ul>' . implode(" ", $arrCustomers). '</ul></div>'); 
		}
	}
	if(isset($component->grid_buttons["download"])) {
		$component->grid_buttons["download"]->url = CM_SHOWFILES . $component->db[0]->getField("file_path", "Text", true);
	}
    if(isset($component->grid_buttons["status"])) {
	    if($component->db[0]->getField("status", "Number", true)) {
            $component->row_class = "on";
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit"; 
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=0&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setstatus=0&frmAction=setstatus&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
            }   
	    } else {
            $component->row_class = "off";
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit";     
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=1&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setstatus=1&frmAction=setstatus&ret_url=" . urlencode($_SERVER["REQUEST_URI"]);
            }    
	    }
	}
}

?>
