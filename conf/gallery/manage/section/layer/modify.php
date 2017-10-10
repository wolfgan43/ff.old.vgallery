<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_LAYER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if($_REQUEST["section"] > 0) {
	$sSQL = "SELECT layout_location.* 
	        FROM layout_location 
	        WHERE layout_location.ID = " . $db_gallery->toSql($_REQUEST["section"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db_gallery->getField("ID_layer", "Number", true);
	}
}


//$ID_template = $_REQUEST["template"];
/*
$sSQL = "SELECT cm_layout.* 
        FROM cm_layout 
        WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
    $template_framework = $framework_css["name"];
}*/

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LayerModify";
$oRecord->resources[] = $oRecord->id; 
$oRecord->title = ffTemplate::_get_word_by_code("layer_modify_title");
$oRecord->src_table = "layout_layer";
$oRecord->addEvent("on_do_action", "LayerModify_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("layer_modify_name");
$oField->required = true;
$oRecord->addContent($oField);
/*
if(check_function("set_fields_grid_system"))
	$js = set_fields_grid_system($oRecord, array(
											"fluid" => array(
												"col" => false
											)
										), $framework_css);  */

$oField = ffField::factory($cm->oPage);
$oField->id = "show_empty";
$oField->label = ffTemplate::_get_word_by_code("layer_show_empty");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField);
                  /*
$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("layout_order");
$oField->base_type = "Number";
$oRecord->addContent($oField);      */


$oRecord->addContent(null, true, "path"); 
$oRecord->groups["path"] = array(
				                 "title" => ffTemplate::_get_word_by_code("layout_layer_path_title")
				                 , "cols" => 1
				                 
				              );
$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "LayoutLocationPath";
$oDetail->title = ffTemplate::_get_word_by_code("layout_layer_path_title");
$oDetail->src_table = "layout_layer_path";
$oDetail->order_default = "path";
$oDetail->fields_relationship = array ("ID_layout_layer" => "ID");
//$oDetail->display_new_location = "Footer";
$oDetail->display_grid_location = "Footer";
$oDetail->min_rows = 1;
$oDetail->display_rowstoadd = false;
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
										'*' AS path
										, 1 AS visible
										, 1 AS cascading";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT *
									FROM
									(
										(
											SELECT 
												layout_layer_path.ID AS ID
												, layout_layer_path.path AS real_path
												, (REPLACE(REPLACE(REPLACE(layout_layer_path.path, '%', '*'), '(.*)', '*'), '(.+)', '*')) AS path
												, layout_layer_path.visible AS visible
												, layout_layer_path.cascading AS cascading
												, layout_layer_path.class
												, layout_layer_path.fluid
												, layout_layer_path.wrap
												, layout_layer_path.width
											FROM layout_layer_path
												INNER JOIN layout_layer on layout_layer.ID = layout_layer_path.ID_layout_layer
											WHERE layout_layer_path.ID_layout_layer = [ID_FATHER]
											ORDER BY layout_layer_path.path DESC
												, layout_layer_path.ID
										)
									) AS tbl_src
									ORDER BY LENGTH(real_path)
								";
$oDetail->addEvent("on_done_action", "LayerPath_on_done_action"); 
$oDetail->addEvent("on_before_process_row", "LayerPath_on_before_process_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->class = "layout-path";
$oField->container_class = "layout-path-rule " . cm_getClassByFrameworkCss(array(4), "col");
$oField->label = ffTemplate::_get_word_by_code("layout_layer_path");
$oField->extended_type = "Selection";
$oField->widget = "autocomplete";
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
							AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number") . "
						[AND] [WHERE]
						[HAVING]
						[ORDER] [COLON] name
						[LIMIT]";
$oField->actex_update_from_db = true;
$oField->required = true;
$oField->multi_select_one = false;
$oField->default_value = new ffData("*");
$oField->properties["onkeyup"] = "javascript:ff.cms.admin.path(this)"; 
$oField->properties["onblur"] = "javascript:ff.cms.admin.path(this, true)"; 
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = ffTemplate::_get_word_by_code("layout_layer_visible");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = $oField->checked_value;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->class = "layout-cascading";
$oField->label = ffTemplate::_get_word_by_code("layout_layer_cascading");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = $oField->checked_value;
$oField->properties["onchange"] = "javascript:ff.cms.admin.pathCascading(this);";      
$oDetail->addContent($oField);

if(check_function("set_fields_grid_system"))
	set_fields_grid_system($oDetail, array(
										"fluid" => array(
											"col" => false
										)
									));

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);




function LayerPath_on_done_action($component, $action) {
	$db = ffDB_Sql::factory();

	switch($action) {
		case "insert":
		case "update":
			if(is_array($component->recordset) && count($component->recordset)) {
				$arrLayerPath = array();
				$arrLayerPathDelete = array();

				foreach($component->recordset AS $key => $value) {
					$tmp_path = $value["path"]->getValue();
					do {
						$tmp_path = str_replace("//", "/*/", $tmp_path);
					} while(str_replace("//", "/*/", $tmp_path) != $tmp_path);

					$cascading = $value["cascading"]->getValue();
					$ID_layer_path = $value["ID"]->getValue();
					$real_path = "";
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
						
						$real_path = $tmp_path;
						if(substr($real_path, -1) == "*") {
							$real_path = substr($real_path, 0,-1);
							$is_cascading = true;
						}

						//$real_path = str_replace("*", "(.+)", $real_path);
						$real_path = str_replace("*", "%", $real_path); //experimental

						if($is_cascading && $cascading) {
							//$real_path .= "(.*)";
							$real_path .= "%"; //experimental
						} 
						//$real_ereg_path = str_replace("(.+)(.*)", "(.+)/(.*)", $real_ereg_path);
					} else {
						$real_path = $tmp_path;
					}
					if(array_key_exists($real_path, $arrLayerPath)) {
						$arrLayerPathDelete[] = $ID_layer_path;
					} else {
						$arrLayerPath[$real_path] = array("ID" => $ID_layer_path
																, "path" => $real_path
															);
					}					
				}

				if(is_array($arrLayerPath) && count($arrLayerPath)) {
					ksort($arrLayerPath);
					
					foreach($arrLayerPath AS $arrLayerPath_key => $arrLayerPath_value) {
						if(strlen($arrLayerPath_value["path"])) {
							$sSQL = "UPDATE layout_layer_path SET 
										path = " . $db->toSql($arrLayerPath_value["path"]) . "
									WHERE layout_layer_path.ID = " . $db->toSql($arrLayerPath_value["ID"], "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(is_array($arrLayerPathDelete) && count($arrLayerPathDelete)) {
					$sSQL = "DELETE FROM  layout_layer_path WHERE ID IN(" . $db->toSql(implode(",", $arrLayerPathDelete), "Text", false) . ")";
					$db->execute($sSQL);
				}				
			}
			break;
		default:
	}
	
}

function LayerPath_on_before_process_row($component, $record, $elem) 
{
	if(isset($component->recordset[$elem]["grid_md"]) && isset($component->recordset[$elem]["grid_sm"]))
	{
		$sum_col = $component->recordset[$elem]["default_grid"]->getValue() + $component->recordset[$elem]["grid_md"]->getValue() + $component->recordset[$elem]["grid_sm"]->getValue();
		if(isset($component->recordset[$elem]["grid_xs"]))
			$sum_col += $component->recordset[$elem]["grid_xs"]->getValue();
		if(!$sum_col)
		{
			$component->recordset[$elem]["default_grid"]->setValue("12");
			$component->recordset[$elem]["grid_md"]->setValue("12");
			$component->recordset[$elem]["grid_sm"]->setValue("12");
			$component->recordset_ori[$elem]["default_grid"]->setValue("12");
			$component->recordset_ori[$elem]["grid_md"]->setValue("12");
			$component->recordset_ori[$elem]["grid_sm"]->setValue("12");
			if(isset($component->recordset[$elem]["grid_xs"])) {
				$component->recordset[$elem]["grid_xs"]->setValue("12");
				$component->recordset_ori[$elem]["grid_xs"]->setValue("12");
			}
		}
	}
	
}

function LayerModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(preg_replace('/[^a-zA-Z0-9\-]/', '', ffCommon_remove_accents($component->form_fields["name"]->getValue())));
	}	
}
//mod_notifier_add_message_to_queue("test", );
?>