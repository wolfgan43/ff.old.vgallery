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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
define("REAL_PATH", "/conf/gallery");

if(!defined("FF_DISK_PATH"))
    exit;

if (!Auth::env("AREA_UPDATER_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
}

if(defined("MASTER_SITE") && strlen(MASTER_SITE)) {
    $operations["/files/updater"] = "/file.php/updater?mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/structure"] = "/db/structure.php?mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/files"] = "/file.php?mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/indexes"] = "/db/indexes.php?mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/data/basic"] = "/db/data.php?contest=basic&mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/data/international"] = "/db/data.php?contest=international&mode=compact&s=" . urlencode(DOMAIN_INSET);
    $operations["/data/support"] = "/db/data.php?contest=support&mode=compact&s=" . urlencode(DOMAIN_INSET);
    
    //$operations["/data/unatantum"] = "/db/data.php?contest=unatantum&mode=compact&s=" . urlencode(DOMAIN_INSET);
    //$operations["/db/data/locations"] = "/db/data?contest=locations&mode=compact&s=" . urlencode(DOMAIN_INSET);
	$db = ffDB_Sql::factory();
	
    $sSQL = "SELECT Table_Name
					FROM information_schema.TABLES
					WHERE Table_Name = 'updater_externals'
						AND TABLE_SCHEMA = " . $db->toSql(FF_DATABASE_NAME);
	$db->query($sSQL);
    if($db->nextRecord() && check_function("get_externals")) {
        $externals = get_externals();

	    if(is_array($externals) && count($externals)) {
        	foreach($externals AS $externals_key => $externals_value) {
        		$operations["/externals" . $externals_key] = "/external.php" . $externals_key . "?mode=compact&s=" . urlencode(DOMAIN_INSET);
			}
		}
	}

    if(defined("PRODUCTION_SITE") && strlen(PRODUCTION_SITE)) {
	    $operations["production/sync/files/uploads"] = "/file.php/sync/uploads?mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["production/sync/data/basic"] = "/db/data.php/sync?contest=basic&mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["production/sync/data/international"] = "/db/data.php/sync?contest=international&mode=compact&s=" . urlencode(DOMAIN_INSET);
    }

    if(defined("DEVELOPMENT_SITE") && strlen(DEVELOPMENT_SITE)) {
	    $operations["development/sync/files/applets"] = "/file.php/sync/applets?mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["development/sync/files/contents"] = "/file.php/sync/contents?mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["development/sync/files/themes/site"] = "/file.php/sync/themes/site?mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["development/sync/data/basic"] = "/db/data.php/sync?contest=basic&mode=compact&s=" . urlencode(DOMAIN_INSET);
	    $operations["development/sync/data/international"] = "/db/data.php/sync?contest=international&mode=compact&s=" . urlencode(DOMAIN_INSET);
    }

    
    $sSQL = "";
    $count_operation = 0;
    $operation_result = "";
    foreach($operations AS $operation_key => $operation_value) {
        if(strlen($sSQL)) 
            $sSQL .= " UNION ";
            
        $sSQL .= " ( SELECT 
                " . $db->toSql(new ffData($count_operation, "Number")) . " AS `ID`
                , " . $db->toSql(new ffData($operation_key, "Text")) . " AS `subject`
                , " . $db->toSql(new ffData($operation_result, "Text")) . " AS `operation` ) ";

        $count_operation++;
    }   
    
    $cm->oPage->addContent(null, true, "rel");

	if(MASTER_SITE != DOMAIN_INSET || (is_array($externals) && count($externals))) { 
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "Updater";
        $oGrid->title = ffTemplate::_get_word_by_code("updater_title");
        $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER]";
        $oGrid->order_default = "ID";
        $oGrid->order_method = "none";
        $oGrid->display_edit_bt = false;
        $oGrid->display_edit_url = false;
        $oGrid->display_delete_bt = false;
        $oGrid->display_new = false;
        $oGrid->use_paging = false;
        $oGrid->use_search = false;
        $oGrid->buttons_options["export"]["display"] = false;

        // Campi chiave
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        // Campi visualizzati
        $oField = ffField::factory($cm->oPage);
        $oField->id = "subject";
        $oField->label = ffTemplate::_get_word_by_code("updater_operation");
        $oField->container_class = "operation";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "operation";
        $oField->label = ffTemplate::_get_word_by_code("updater_operation");
        $oField->container_class = "result";
        $oGrid->addContent($oField);

        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "refresh";
        //$oButton->label = "preview";
        //$oButton->action_type = "gotourl";
        //$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "[Updater_subject_VALUE]?ret_url=" . urlencode($_SERVER['REQUEST_URI']);
        //$oButton->action_type = "submit";
        $oButton->jsaction = "Updater(true, jQuery(this).parent().prev().prev(), undefined, true);";
        $oButton->aspect = "link";
        $oButton->label = ffTemplate::_get_word_by_code("edit");
	    //$oButton->image = "edit.png";
        $oButton->display_label = false;
        $oGrid->addGridButton($oButton);
        
        $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("updater"))); 
        
        $cm->oPage->tplAddJs("updater"
            , array(
                "file" => "updater.js"
                , "path" => REAL_PATH . "/updater/js"
        ));
    }
    
	//EXTERNALS
	$sSQL = "SELECT Table_Name
			FROM information_schema.TABLES
			WHERE Table_Name = 'updater_externals'
				AND TABLE_SCHEMA = " . $db->toSql(FF_DATABASE_NAME);
	$db->query($sSQL);
	if($db->nextRecord()) {
		$oGrid = ffGrid::factory($cm->oPage);
        $oGrid->full_ajax = true;
		$oGrid->id = "externals";
		$oGrid->title = ffTemplate::_get_word_by_code("external_title");
		$oGrid->source_SQL = "SELECT * FROM updater_externals [WHERE] [HAVING] [ORDER]";
		$oGrid->order_default = "path";
		$oGrid->use_search = true;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/externals/modify";
		$oGrid->record_id = "ExternalsModify";
        $oGrid->resources[] = $oGrid->record_id;

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "path";
		$oField->label = ffTemplate::_get_word_by_code("externals_path");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "domain";
		$oField->label = ffTemplate::_get_word_by_code("externals_domain");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "status";
		$oField->label = ffTemplate::_get_word_by_code("externals_status");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
				                    array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))),
				                    array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("disactive")))
				               );
		$oGrid->addContent($oField);

		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("externals"))); 
	}

	//EXCLUDE
	$sSQL = "SELECT Table_Name
			FROM information_schema.TABLES
			WHERE Table_Name = 'updater_exclude'
				AND TABLE_SCHEMA = " . $db->toSql(FF_DATABASE_NAME);
	$db->query($sSQL);
	if($db->nextRecord()) {
		$oGrid = ffGrid::factory($cm->oPage);
        $oGrid->full_ajax = true;
		$oGrid->id = "exclude";
		$oGrid->title = ffTemplate::_get_word_by_code("exclude_title");
		$oGrid->source_SQL = "SELECT * FROM updater_exclude [WHERE] [HAVING] [ORDER]"; 
		$oGrid->order_default = "path";
		$oGrid->use_search = true;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/exclude/modify";
		$oGrid->record_id = "ExcludeModify";
        $oGrid->resources[] = $oGrid->record_id;

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "path";
		$oField->label = ffTemplate::_get_word_by_code("exclude_path");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "status";
		$oField->label = ffTemplate::_get_word_by_code("exclude_status");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
				                    array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))),
				                    array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("disactive")))
				               );
		$oGrid->addContent($oField);

		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("exclude"))); 
	}
} else {
	if(is_object($cm))
    	$cm->oPage->fixed_pre_content = ffTemplate::_get_word_by_code("updater_not_configurated");
    else
    	echo "{_updater_not_configurated}";
}