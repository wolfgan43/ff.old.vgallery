<?php
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);
    
    if (!AREA_SERVICES_SHOW_MODIFY) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

	$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db_gallery->tosql(DOMAIN_NAME);	
    $db_gallery->query($sSQL);
    if(!$db_gallery->nextRecord()) {
        
        
    }
    
    $sSQL = "";
	$arraydir = glob(VG_WEBSERVICES_PATH . "/*", GLOB_ONLYDIR);
	
	if (is_array($arraydir) && count($arraydir)) {
		foreach ($arraydir as $dirvalue) {
			if (is_dir($dirvalue)) {
				if(strlen($sSQL))
					$sSQL .= " UNION ";
				$dirvalue = $db_gallery->tosql(basename($dirvalue));
				
				$sSQL .= "SELECT " . $dirvalue . " AS ID, " . $dirvalue . " AS name, (SELECT value FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields INNER JOIN " . CM_TABLE_PREFIX . "mod_security_domains ON " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $dirvalue . " AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = 'enable' AND " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_gallery->toSql(DOMAIN_NAME) . " LIMIT 1) AS info";
			}
		}
	}
	
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "services";
	$oGrid->title = ffTemplate::_get_word_by_code("services_title");
	$oGrid->source_SQL = $sSQL . " [WHERE] [ORDER] ";
	$oGrid->order_default = "name";
	$oGrid->use_search = false;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]/config";
	$oGrid->record_id = "ServicesModify";
	$oGrid->resources[] = $oGrid->record_id;
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;
    $oGrid->display_edit_bt = false;

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

/*	// Campi di ricerca
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("drafts_name");
	$oField->src_table = "drafts";
	$oField->src_operation     = "[NAME] LIKE [VALUE]";
	$oField->src_prefix     = "%";
	$oField->src_postfix     = "%";
	$oGrid->addSearchField($oField);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "parent";
	$oField->label = ffTemplate::_get_word_by_code("Path");
	$oField->extended_type = "Selection";
	$oField->source_SQL = " SELECT DISTINCT
	                        IF(static_pages.parent = '/', CONCAT( static_pages.parent, static_pages.name ), CONCAT( static_pages.parent, '/', static_pages.name )) ,
	                        IF(static_pages.name = '', 'home', CONCAT( 'home', IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name )) AS name
	                        FROM static_pages ORDER BY name";
	$oField->src_operation     = "[NAME] LIKE [VALUE]";
	$oField->src_prefix     = "";
	$oField->src_postfix     = "%";
	$oGrid->addSearchField($oField);*/


	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("services_name");
	$oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "info";
    $oField->label = ffTemplate::_get_word_by_code("services_info");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
                                      array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))) 
                                    , array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("no_active")))
                                    , array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no_active")))
                                );
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no_set");
    $oGrid->addContent($oField);
    
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "parent";
	$oField->label = ffTemplate::_get_word_by_code("edit_general_path");
	$oGrid->addContent($oField);*/

/*
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "editrow";
    //$oButton->class = "icon ico-edit";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[services_name_VALUE]/config?ret_url=" . urlencode($cm->oPage->getRequestUri());  
    $oButton->aspect = "link";
    //$oButton->image = "edit.png";
	$oButton->label = ffTemplate::_get_word_by_code("edit");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
*/

	$cm->oPage->addContent($oGrid);
?>