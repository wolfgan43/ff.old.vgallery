<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_PUBLISHING_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(!isset($_REQUEST["keys"]["ID"]) && strlen(basename($cm->real_path_info))) {
	$arrPublishing = explode("_", basename($cm->real_path_info));

    $db_gallery->query("SELECT publishing.*
                            FROM 
                                publishing
                            WHERE 
                                publishing.area = " . $db_gallery->toSql($arrPublishing[0]) . "
                                AND publishing.ID = " . $db_gallery->toSql($arrPublishing[1])
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number")->getValue();
	}
}


// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PublishingModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "publishing";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT publishing.*
									, IF(publishing.display_name = ''
										, REPLACE(publishing.name, '-', ' ')
										, publishing.display_name
									) AS display_name
								FROM publishing 
								WHERE publishing.ID =" . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->buttons_options["delete"]["display"] = Auth::env("AREA_PUBLISHING_SHOW_DELETE");
$oRecord->addEvent("on_do_action", "PublishingModify_on_do_action");
$oRecord->addEvent("on_done_action", "PublishingModify_on_done_action");


if(isset($_REQUEST["keys"]["ID"]))
{
	$publishing_title = ffTemplate::_get_word_by_code("modify_publishing");
	$sSQL = "SELECT publishing.*
					, IF(publishing.display_name = ''
						, REPLACE(publishing.name, '-', ' ')
						, publishing.display_name
					) AS display_name		
				FROM publishing
				WHERE publishing.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord())
	{
		$publishing_title .= ": " . ucwords($db_gallery->getField("display_name", "Text", true));
		$publishing_area = $db_gallery->getField("area", "Text", true);
	    switch($publishing_area) {
	        case "anagraph":
	            $src_type = "anagraph";
	            break;
	        case "gallery":
	            $src_type = "files";
	            break;
	        default:
	            $src_type = "vgallery";
	    }
	    
	    $oRecord->user_vars["area"] = $publishing_area;
	    $oRecord->user_vars["src_type"] = $src_type;
	}
} else
{
	$publishing_title = ffTemplate::_get_word_by_code("addnew_publishing");
}




/* Title Block */
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content-adv">' . Cms::getInstance("frameworkcss")->get("vg-publishing", "icon-tag", array("2x", "content-adv")) . $publishing_title . '</h1>';
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


/***********
*  Group General
*/
if(isset($_REQUEST["keys"]["ID"]) && $publishing_area != "gallery") { 
    $group_general = "general";
    $oRecord->addContent(null, true, $group_general); 

    $oRecord->groups[$group_general] = array(
											    "title" => ffTemplate::_get_word_by_code("publishing_" . $group_general)
											    //, "title_class" => "dialogSubTitleTab dep-general"
												, "tab_dialog" => true
											    , "cols" => 1
											    , "class" => ""
                                              //   , "tab" => $group_general
                                              );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("publishing_name");
$oField->required = true;
if(isset($_REQUEST["keys"]["ID"]))
	$oField->setWidthComponent(array(3,4,12));

$oRecord->addContent($oField, $group_general);

if(!isset($_REQUEST["keys"]["ID"])) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "area";
	$oField->label = ffTemplate::_get_word_by_code("publishing_area");
	$oField->widget = "activecomboex";
	$oField->multi_pairs = array (
	                            array(new ffData("gallery"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
	                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
	                            array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery")))
	                       );      
	$oField->required = true;
	$oField->actex_child = array("contest");
	$oField->setWidthComponent(array(3,4,12));
	$oRecord->addContent($oField, $group_general);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT nameID, name, type FROM
                    (
                        (
	                        SELECT 
	                            CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS nameID
	                            , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS name
	                            , 'gallery' AS type
	                        FROM
	                            files
	                        WHERE files.name <> ''
	                        	AND files.is_dir > 0
                        )
                        UNION
                        (
	                        SELECT 
	                            anagraph_categories.ID AS nameID
	                            , anagraph_categories.name AS name
	                            , 'anagraph' AS type
	                        FROM
	                            anagraph_categories
                        ) 
                        UNION 
                        (
	                        SELECT 
	                            name AS nameID, 
	                            name,
	                            'vgallery' AS type
	                        FROM 
	                            vgallery
	                        WHERE vgallery.status > 0
                        ) 
                    ) AS tbl_src
                    " . (isset($_REQUEST["keys"]["ID"])
                    	? "WHERE tbl_src.`type` = " . $db_gallery->toSql($publishing_area) 
                    	: "[WHERE]"
                    ) . "
                    ORDER BY tbl_src.name";  

if(!isset($_REQUEST["keys"]["ID"]))
	$oField->actex_father = "area";
$oField->actex_child = array("relative_path");
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
//$oField->actex_hide_empty = "all";
//$oField->required = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("all");
//$oField->multi_select_one_val = new ffData("");
$oField->setWidthComponent(array(4,8,12));
$oRecord->addContent($oField, $group_general);

$oField = ffField::factory($cm->oPage);
$oField->id = "relative_path";
$oField->label = ffTemplate::_get_word_by_code("publishing_relative_path");
$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT nameID, name, type FROM
                    (
                        (
	                        SELECT 
	                            IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS nameID
	                            , IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS name
	                            , vgallery.name AS type
	                        FROM
	                            vgallery_nodes
	                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                        WHERE vgallery_nodes.name <> ''
	                        	AND vgallery_nodes.is_dir > 0
	                        HAVING name <> '/'
	                        ORDER BY type, name
                        )
                    ) AS tbl_src
                    [WHERE]
                    ORDER BY tbl_src.name";
$oField->actex_father = "contest";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
$oField->actex_hide_empty = "all";
//$oField->required = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("all");
$oField->multi_select_one_val = new ffData("/");
$oField->setWidthComponent(array(5,12));
$oRecord->addContent($oField, $group_general);

$oField = ffField::factory($cm->oPage);
$oField->id = "full_selection";
$oField->label = ffTemplate::_get_word_by_code("publishing_full_selection");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("1", "Number");
$oField->setWidthComponent(array(6,6,12));
$oRecord->addContent($oField, $group_general);

$oField = ffField::factory($cm->oPage);
$oField->id = "random";
$oField->label = ffTemplate::_get_word_by_code("publishing_random");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->setWidthComponent(array(6,6,12));
$oRecord->addContent($oField, $group_general);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->label = ffTemplate::_get_word_by_code("publishing_limit");
$oField->base_type = "Number";
$oField->required = true;
$oField->setWidthComponent(array(3));
$oRecord->addContent($oField, $group_general);


$cm->oPage->addContent($oRecord);

if(isset($_REQUEST["keys"]["ID"]) && $publishing_area != "gallery") 
{
	/***********
	*  Group Fields
	*/
	$group_fields = "fields";
	$oRecord->addContent(null, true, $group_fields); 

	$oRecord->groups[$group_fields] = array(
												"title" => ffTemplate::_get_word_by_code("publishing_" . $group_fields)
												//, "title_class" => "dialogSubTitleTab dep-fields"
												, "tab_dialog" => true
												, "cols" => 1
												, "class" => ""
	                                          //   , "tab" => $group_general
	                                          );


	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->dialog_action_button = true;
	//$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
	$oGrid->id = "PublishingModifyFields";
	$oGrid->source_SQL = "SELECT publishing_fields.* 
								, " . $src_type . "_fields.name AS name
	                        FROM publishing_fields
                                INNER JOIN " . $src_type . "_fields ON " . $src_type . "_fields.ID = publishing_fields.ID_fields
	                        WHERE publishing_fields.ID_publishing = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                            [AND] [WHERE] 
	                        [HAVING] 
	                        [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = false;
	$oGrid->use_order = false;
	$oGrid->use_paging = false;
	$oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/publishing/extra/modify";
    $oGrid->addit_insert_record_param = "src=" . $publishing_area . "&publishing=" . $_REQUEST["keys"]["ID"] . "&";
    $oGrid->addit_record_param = "src=" . $publishing_area . "&publishing=" . $_REQUEST["keys"]["ID"] . "&";
	$oGrid->record_id = "PublishingExtraFieldModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->buttons_options["export"]["display"] = false;
	$oGrid->widget_deps[] = array(
	    "name" => "dragsort"
	    , "options" => array(
	          &$oGrid
	        , array(
	            "resource_id" => "publishing_fields"
	            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	        )
	        , "ID"
	    )
	);
	//$oGrid->addEvent("on_before_parse_row", "PublishingModifyFields_on_before_parse_row");


	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_SQL = " `parent_thumb`, `order_thumb`, ID";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_name");
	$oGrid->addContent($oField);	
	
	$oRecord->addContent($oGrid, $group_fields);
	$cm->oPage->addContent($oGrid);

    $group_filter = "filter";
    $oRecord->addContent(null, true, $group_filter); 

    $oRecord->groups[$group_filter] = array(
											    "title" => ffTemplate::_get_word_by_code("publishing_" . $group_filter)
											    //, "title_class" => "dialogSubTitleTab dep-filter"
												, "tab_dialog" => true
											    , "cols" => 1
											    , "class" => ""
                                              //   , "tab" => $group_general
                                              ); 
     
     
     
                              
    $oDetail_criteria = ffDetails::factory($cm->oPage);
    $oDetail_criteria->id = "PublishingModifyDCriteria";
    $oDetail_criteria->title = ffTemplate::_get_word_by_code("publishing_modify_dcriteria_title");
    $oDetail_criteria->src_table = "publishing_criteria";
    $oDetail_criteria->order_default = "ID";
    $oDetail_criteria->fields_relationship = array ("ID_publishing" => "ID");
    $oDetail_criteria->display_new = true;
    $oDetail_criteria->display_delete = true;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oDetail_criteria->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "src_fields";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_dcriteria_fields");
    $oDetail_criteria->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "operator";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_dcriteria_operator");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
                                array(new ffData("="), new ffData(ffTemplate::_get_word_by_code("="))),
                                array(new ffData("<"), new ffData(ffTemplate::_get_word_by_code("<"))),
                                array(new ffData(">"), new ffData(ffTemplate::_get_word_by_code(">"))),
                                array(new ffData("<="), new ffData(ffTemplate::_get_word_by_code("<="))),
                                array(new ffData(">="), new ffData(ffTemplate::_get_word_by_code(">="))),
                                array(new ffData("<>"), new ffData(ffTemplate::_get_word_by_code("<>"))),
                                array(new ffData("LIKE"), new ffData(ffTemplate::_get_word_by_code("LIKE")))
                           );
    $oField->required = true;
    $oDetail_criteria->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "value";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_dcriteria_value");
    $oDetail_criteria->addContent($oField);

    $oRecord->addContent($oDetail_criteria, $group_filter);
    $cm->oPage->addContent($oDetail_criteria);
}                            

//if($_REQUEST["keys"]["IDs"]) {



// -------------------------
//          EVENTI
// -------------------------
function extras_check_load_template($oField) {
    $oField[0]->tpl[0]->set_var("link", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/extras/modify?type=" . "publishing" . "&item=" . $_REQUEST["keys"]["ID"] . "&ret_url=" . urlencode($oField->parent[0]->getRequestUri()));
}

function PublishingModify_on_do_action($component, $action) {

}

function PublishingModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    
    if(strlen($action)) {
        $ID_node = $component->key_fields["ID"]->getValue();
        if(isset($component->form_fields["area"]))
        	$publishing_area = $component->form_fields["area"]->getValue();
        else 
        	$publishing_area = $oRecord->user_vars["area"];
        
	    switch($publishing_area) {
	        case "anagraph":
	            $src_type = "anagraph";
	            $sSQL = "SELECT DISTINCT anagraph.ID_type 
	            		FROM anagraph "
	            			. ($component->form_fields["contest"]->getValue()
	            				? " INNER JOIN anagraph_categories ON anagraph_categories.name = " . $db->toSql($component->form_fields["contest"]->value) . "
	            						AND FIND_IN_SET(anagraph_categories.ID, anagraph_categories)"
	            				: ""
	            			) . "
	            		WHERE 1 ";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$arrType[] = $db->getField("ID_type", "Number", true);
					} while($db->nextRecord());
				}	            		
	            break;
	        case "gallery":
	            $src_type = "files";
	            break;
	        default:
	            $src_type = "vgallery";
	            $sSQL = "SELECT DISTINCT vgallery.limit_type 
	            		FROM vgallery
	            		WHERE 1 "
	            			. ($component->form_fields["contest"]->getValue()
	            				? " AND vgallery.name = " . $db->toSql($component->form_fields["contest"]->value)
	            				: ""
	            			);
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$arrType[] = $db->getField("limit_type", "Text", true);
					} while($db->nextRecord());
				}	            		
	    }
	    switch($action) {
	        case "insert":
	        	if(is_array($arrType) && count($arrType)) {
					$sSQL = "INSERT INTO publishing_fields
							(
								`ID`
								, `ID_publishing`
								, `ID_fields`
								, `order_thumb`
								, `enable_lastlevel`
								, `enable_thumb_label`
								, `enable_thumb_empty`
								, `thumb_limit`
								, `parent_thumb`
								, `enable_thumb_cascading`
								, `display_view_mode_thumb`
								, `enable_sort`
								, `settings_type_thumb`
								, `ID_thumb_htmltag`
								, `custom_thumb_field`
								, `ID_label_thumb_htmltag`
								, `fixed_pre_content_thumb`
								, `fixed_post_content_thumb`
							)
							SELECT 
								null 													AS ID
								, " . $db->toSql($ID_node, "Number") . " 				AS ID_publishing
								, `" . $src_type . "_fields`.ID 						AS ID_fields
								, `" . $src_type . "_fields`.order_thumb 				AS order_thumb
								, `" . $src_type . "_fields`.enable_lastlevel 			AS enable_lastlevel
								, `" . $src_type . "_fields`.enable_thumb_label 		AS enable_thumb_label
								, `" . $src_type . "_fields`.enable_thumb_empty 		AS enable_thumb_empty
								, `" . $src_type . "_fields`.thumb_limit 				AS thumb_limit
								, `" . $src_type . "_fields`.parent_thumb 				AS parent_thumb
								, `" . $src_type . "_fields`.enable_thumb_cascading 	AS enable_thumb_cascading
								, `" . $src_type . "_fields`.display_view_mode_thumb 	AS display_view_mode_thumb
								, `" . $src_type . "_fields`.enable_sort 				AS enable_sort
								, `" . $src_type . "_fields`.settings_type_thumb 		AS settings_type_thumb
								, `" . $src_type . "_fields`.ID_thumb_htmltag 			AS ID_thumb_htmltag
								, `" . $src_type . "_fields`.custom_thumb_field 		AS custom_thumb_field
								, `" . $src_type . "_fields`.ID_label_thumb_htmltag 	AS ID_label_thumb_htmltag
								, `" . $src_type . "_fields`.fixed_pre_content_thumb 	AS fixed_pre_content_thumb
								, `" . $src_type . "_fields`.fixed_post_content_thumb	AS fixed_post_content_thumb
							FROM `" . $src_type . "_fields`
								INNER JOIN `" . $src_type . "_type` ON `" . $src_type . "_type`.ID = `" . $src_type . "_fields`.ID_type
							WHERE `" . $src_type . "_fields`.`enable_thumb` > 0
								AND `" . $src_type . "_type`.ID IN(" . $db->toSql(implode(",", $arrType), "Text", false) . ")";
					$db->execute($sSQL);
				}
				
				

	        case "update":
	        	$sSQL = "UPDATE publishing SET 
	        				publishing.name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["display_name"]->getValue())) . "
	        			WHERE publishing.ID = " . $db->toSql($ID_node, "Number");
	        	$db->execute($sSQL);
	            break;
	        default:
	    
	    }

		if(check_function("refresh_cache")) {
        	refresh_cache_get_blocks_by_layout($publishing_area . "_" . $ID_node);
		}
		
        //UPDATE CACHE
/*
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `publishing`.last_update FROM publishing WHERE publishing.ID = " . $db->toSql($ID_node, "Number") . ") 
                WHERE 
                    (
                        REPLACE(layout.value, " . $db->toSql("vgallery_") . ", '') = " . $db->toSql($ID_node, "Number") . "
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);
*/
        //UPDATE CACHE 
    }
}
?>
