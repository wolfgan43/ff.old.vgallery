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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

    if (!AREA_CHECKER_SHOW_MODIFY) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }
    
    $db = ffDB_Sql::factory();

    if($cm->oPage->isXHR()) {
        switch(basename($cm->real_path_info)) {
            case "cache":
                $check = check_cache();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "international":
                $check = check_international();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "config":
                $check = check_config();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "uploads":
                $check = check_uploads();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "system":
                if(check_function("check_system"))
                    $check = check_system();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "thumb":
                $check = check_thumb();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "trash":
                $check = check_trash();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "database":
                $check = check_database();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "bill":
                if(check_function("check_bill"))
                    $check = check_bill();
                echo ffCommon_jsonenc($check, true);
                exit;
            case "payments":
                if(check_function("check_payments"))
                    $check = check_payments();
                    
                echo ffCommon_jsonenc($check, true);
                exit;
            case "bill-description":
                $check = array();
                echo ffCommon_jsonenc($check, true);
                exit;
            default:
        }
        
    }
    
    if(isset($_REQUEST["frmAction"])) {
    	if(isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"]))
    		$ret_url = $_REQUEST["ret_url"];
    	else
    		$ret_url = $cm->oPage->site_path . $cm->oPage->page_path;
    	
	    $frmAction = $_REQUEST["frmAction"];
	    $res = true;
	    
	    switch($frmAction) {
			case "cacheClearAll":
				$strError = set_cache_clear_all();
		        if(!$strError) {
		            ffRedirect($ret_url);
		        }
				break;
	        case "cacheRepair": // cache repair
				$strError = set_cache_repair();
		        if(!$strError) {
		            ffRedirect($ret_url);
		        } 
	            break;
	        case "cacheClear": // cache clear
				$strError = set_cache_clear();		        
		        if(!$strError) {
		            ffRedirect($ret_url);
		        }
	            break;
	        case "cacheClearDB": // cache clearDB
	        	$strError = set_cache_clear_db();
		        if(!$strError) {
		            ffRedirect($ret_url);
		        } 
	            break;
	        case "cacheClearSID": // cache clearSID
				$strError = set_cache_clear_sid();
		        if(!$strError) {
		            ffRedirect($ret_url);
		        } 
	            break;
	        case "internationalRepair": //international repair
				$strError = set_international_repair();		        	
		        if(!$strError) {
		            ffRedirect($ret_url); 
		        } 
	        case "internationalReset": //international reset
	            //UPDATE CACHE
	            if(!$strError) {
					$strError = set_international_reset();
					if(!$strError) {		            
		            	ffRedirect($ret_url);
					}
				}
	            break;
			case "configRepair": // /conf/gallery/config
				$strError = set_config_repair();
		        if(!$strError) {
		            ffRedirect($ret_url);
		        } 
				break;
	        case "uploadsRepair": // /upload
				$strError = set_uploads_repair();

                if(check_function("check_fs"))
				    check_fs(DISK_UPDIR, "/");

		        if(!$strError) {
		            ffRedirect($ret_url);
		        } 
	            break;
	        case "systemRepair": // /themes/site/_sys
                if(check_function("set_system_repair"))
				    $strError = set_system_repair();
	            if(!$strError) {
					ffRedirect($ret_url);
	            }
	            break;


	        case "thumbClear":
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;
	        
	        case "trashRemove":
	            //deve calcellare tutti i file tipo .svn e altra spazzatura da tutto il sito
	        
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;
	        case "databaseRepair":
	            // deve ripristinare gli indici e altre cosucce
	        
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;
	        case "databaseCompact":
	            // deve compattare il databae
	        
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;
	        case "billRepair":
                if(check_function("set_bill_repair"))
				    $strError = set_bill_repair();	        
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;

	        case "paymentsRepair":
                if(check_function("set_payments_repair"))
				    $strError = set_payments_repair();
	            //$strError = set_fs("/uploads", "delete", array("/uploads/" . GALLERY_TPL_PATH => true));
	            //if(!$strError) {
	                ffRedirect($ret_url);
	            //} 
	            break;

			case "resetBillDescription":
                if(check_function("set_bill_description"))
				    $strError = set_bill_description();
										            
	                ffRedirect($ret_url);
	                
				break;
	        
	        default:
	        
	        
	        
	    }
		if(isset($_REQUEST["ret_url"]))
			ffRedirect($_REQUEST["ret_url"]);
	}	
    $tpl = ffTemplate::factory(FF_DISK_PATH . "/themes/gallery/contents");
    $tpl->load_file("checker.html", "Main");
	$tpl->set_var("ajax_loader", cm_getClassByFrameworkCss("spinner", "icon-tag", "spin"));
/*
	// cache
	$selector = "cache";
	$check = check_cache();
  
	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

    // international
    $selector = "international";
	$check = check_international();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));
    
	// /conf/gallery/config
	$selector = "config";
	$check = check_config();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

	// /uploads
	$selector = "uploads";
	$check = check_uploads(); 
//$check = array();
	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

    // /uploads/_sys
    $selector = "system";
    if(check_function("check_system"))
	    $check = check_system();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

    //thumb
    $selector = "thumb";
	$check = check_thumb();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

	//trash
    $selector = "trash";
	$check = check_trash();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

	//database
    $selector = "database";
	$check = check_database();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

		
	//bill
    $selector = "bill";
    if(check_function("check_bill"))
	    $check = check_bill();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));

	//payments
    $selector = "payments";
    if(check_function("check_payments"))
	    $check = check_payments();

	$tpl->set_var("info_" . $selector, $check["info"]);
	if($check["status"])
		$tpl->set_var("status_" . $selector, $check["status"]);
	else
		$tpl->set_var("status_" . $selector, ffTemplate::_get_word_by_code("ok"));
*/	

    if($strError) {
        $tpl->set_var("error", $strError);
        $tpl->parse("SezError", false);
    } else {
        $tpl->set_var("SezError", "");
    }
    
    $cm->oPage->fixed_pre_content = $tpl->rpparse("Main", false);   
