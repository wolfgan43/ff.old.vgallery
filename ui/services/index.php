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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
    
    if (!AREA_SERVICES_SHOW_MODIFY) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }
    
    $db = ffDB_Sql::factory();

	$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db->tosql(DOMAIN_NAME);	
    $db->query($sSQL);
    if(!$db->nextRecord()) {
        
        
    }
    
    $sSQL = "";
	$arraydir = glob(FF_DISK_PATH . VG_WEBSERVICES_PATH . "/*", GLOB_ONLYDIR);
	
	if (is_array($arraydir) && count($arraydir)) {
		foreach ($arraydir as $dirvalue) {
			if (is_dir($dirvalue)) {
				if(strlen($sSQL))
					$sSQL .= " UNION ";
				$dirvalue = $db->tosql(basename($dirvalue));
				
				$sSQL .= "SELECT " . $dirvalue . " AS ID, " . $dirvalue . " AS name, (SELECT value FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields INNER JOIN " . CM_TABLE_PREFIX . "mod_security_domains ON " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $dirvalue . " AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = 'enable' AND " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql(DOMAIN_NAME) . " LIMIT 1) AS info";
			}
		}
	}
	
	$oGrid = ffGrid::factory($cm->oPage);
	//$oGrid->full_ajax = true;
	$oGrid->id = "services";
	$oGrid->title = ffTemplate::_get_word_by_code("services_title");
	$oGrid->source_SQL = $sSQL . " [WHERE] [ORDER] ";
	$oGrid->order_default = "name";
	//$oGrid->use_search = false;
	$oGrid->use_paging = false;	
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]/config";
	$oGrid->record_id = "ServicesModify";
	$oGrid->resources[] = $oGrid->record_id;
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;
    $oGrid->display_edit_bt = false;
    $oGrid->buttons_options["export"]["display"] = false;
    

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
	$oButton->display_label = false;
    $oGrid->addGridButton($oButton);
*/

	$cm->oPage->addContent($oGrid);