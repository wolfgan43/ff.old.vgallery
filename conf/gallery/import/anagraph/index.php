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

    if (!Auth::env("AREA_IMPORT_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

    $db = ffDB_Sql::factory();
    
    $frmAction = $_REQUEST["frmAction"];
    $file_scan = $_REQUEST["fs"];
    $field_sep = (isset($_REQUEST["ct"])
                    ? $_REQUEST["ct"]
                    : '\t');
    $field_enc = (isset($_REQUEST["en"])
                    ? $_REQUEST["en"]
                    : '"');
    $destination_type = $_REQUEST["destination_type"];
    $field_type = $_REQUEST["field_type"];
    $ID_lang = $_REQUEST["lang"];
    $del_anagraph = $_REQUEST["del_anagraph"];
    
    $fixed_pre_cat = $_REQUEST["fprecat"];  
    $fixed_post_cat = $_REQUEST["fpostcat"];  

    $limit_by = $_REQUEST["limit_by"];
    $limit_field = $_REQUEST["limit_field"]; 

    $visible = $_REQUEST["visible"]; 
    
    $nrec = (isset($_REQUEST["nrec"])
                    ? $_REQUEST["nrec"]
                    : 500);
    
    $parts = (isset($_REQUEST["parts"])
                    ? $_REQUEST["parts"]
                    : 0);
    
    $tot_parts = $_REQUEST["tot_parts"];
                    
    $cri = $_REQUEST["cri"];
    $cru = $_REQUEST["cru"];
    $cci = $_REQUEST["cci"];
    
    $sep = $field_sep;
    $sep = str_replace('\t', "\t", $sep);
    $sep = str_replace('\n', "\n", $sep);
    $sep = str_replace('\r', "\r", $sep);

    $enclosure = $field_enc;
    $enclosure = str_replace('\t', "\t", $enclosure);
    $enclosure = str_replace('\n', "\n", $enclosure);
    $enclosure = str_replace('\r', "\r", $enclosure);


    $tpl = ffTemplate::factory(FF_DISK_PATH . "/themes/gallery/contents/import");
    $tpl->load_file("csv.html", "Main");
	
	$tpl->set_var("import_path", $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path));
	$tpl->set_var("import_title", ffTemplate::_get_word_by_code("import_" . basename($cm->oPage->page_path)));
	
    if($frmAction == "scan") {
    	$tmp_import_data = get_session("WGimportAN");

		if(!strlen($limit_by))
			$limit_by = $tmp_import_data["limit_by"];
			
		if(!strlen($limit_field))
			$limit_field = $tmp_import_data["limit_field"];

        $tpl->set_var("limit_by", ffCommon_specialchars($limit_by));
        
        if(strlen($enclosure)) {
            if($destination_type && $field_type  && $nrec && $ID_lang > 0) {
                if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan)) {
                    $fp = fopen(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan, "r");
                    $part = fgetcsv($fp, 0, $sep, $enclosure);
                    $count_data_tot = 0;
                    
                    while (($data_csv = fgetcsv($fp, 0, $sep, $enclosure)) !== FALSE) {
                        $count_data_tot++;
                    }
                    
                    $tot_parts = ceil($count_data_tot / $nrec);
                    $tpl->set_var("tot_parts", $tot_parts);
                    $tpl->set_var("nrec", $nrec);
                    $tpl->set_var("count_rec", $count_data_tot);

                    if(is_array($part) && count($part)) {
                        $sSQL = "SELECT 
                                    anagraph_fields.ID
                                    , anagraph_fields.name
                                FROM anagraph_fields 
                                    INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
                                WHERE anagraph_type.ID = " . $db->toSql($field_type, "Number") . "
                                UNION
                                SELECT
                                    'name' AS ID
                                    , 'name' AS name
                                UNION
                                SELECT
                                    'surname' AS ID
                                    , 'surname' AS name
                                UNION
                                SELECT
                                    'email' AS ID
                                    , 'email' AS name
                                UNION
                                SELECT
                                    'tel' AS ID
                                    , 'tel' AS name
                                UNION
                                SELECT
                                    'billcf' AS ID
                                    , 'billcf' AS name
                                UNION
                                SELECT
                                    'billpiva' AS ID
                                    , 'billpiva' AS name
                                UNION
                                SELECT
                                    'billreference' AS ID
                                    , 'billreference' AS name
                                UNION
                                SELECT
                                    'billaddress' AS ID
                                    , 'billaddress' AS name
                                UNION
                                SELECT
                                    'billcap' AS ID
                                    , 'billcap' AS name
                                UNION
                                SELECT
                                    'billtown' AS ID
                                    , 'billtown' AS name
                                UNION
                                SELECT
                                    'billprovince' AS ID
                                    , 'billprovince' AS name
                                UNION
                                SELECT
                                    'billstate' AS ID
                                    , 'billstate' AS name
                                UNION
                                SELECT
                                    'shippingreference' AS ID
                                    , 'shippingreference' AS name
                                UNION
                                SELECT
                                    'shippingaddress' AS ID
                                    , 'shippingaddress' AS name
                                UNION
                                SELECT
                                    'shippingcap' AS ID
                                    , 'shippingcap' AS name
                                UNION
                                SELECT
                                    'shippingtown' AS ID
                                    , 'shippingtown' AS name
                                UNION
                                SELECT
                                    'shippingprovince' AS ID
                                    , 'shippingprovince' AS name
                                UNION
                                SELECT
                                    'shippingstate' AS ID
                                    , 'shippingstate' AS name";
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            foreach($part AS $part_key => $part_value) {
                                $tpl->set_var("field_source", $part_value);
                                $tpl->set_var("field_source_id", $part_key);

                                if(strlen($limit_field) && $limit_field == $part_key) {
                                    $tpl->set_var("selected", "selected=\"selected\"");
                                } else {
                                    $tpl->set_var("selected", "");
                                }

                                $tpl->parse("SezFieldLimit", true);
                            }
                            reset($part);
                            
                            $tpl->set_var("SezVisible", "");

                            //init data
                            
                            //primary
                            $actual_data_key = "primary";
                            $actual_data_label = "Primary key";
                            $actual_levenshtein = "id";
                            
                            $tpl->set_var("SezFieldSource", "");
                            $tpl->set_var("SezFieldDestiny", "");

							if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key])) {
								foreach($tmp_import_data["data"][$actual_data_key] AS $sess_data_key => $sess_data_value) {
									 $tpl->set_var("field_destiny_id", $sess_data_value);
									 $tpl->set_var("field_destiny", $part[$sess_data_value]);
									 $tpl->parse("SezFieldDestiny", true);
								}
								reset($tmp_import_data);
							}

                            $tpl->set_var("field_name", $actual_data_label);
                            $tpl->set_var("field_id", $actual_data_key);
                            
                            foreach($part AS $part_key => $part_value) {
                            	if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key]) && array_key_exists($part_key, $tmp_import_data["data"][$actual_data_key]))
                            		continue; 
                            	
                                $tpl->set_var("field_source", $part_value);
                                $tpl->set_var("field_source_id", $part_key);
                                
                                if(strlen($actual_levenshtein) && strlen(ffCommon_url_rewrite($part_value)) && (100 * levenshtein($actual_levenshtein, ffCommon_url_rewrite($part_value)) / strlen($part_value)) <= 33) {
                                    $tpl->set_var("selected", "selected=\"selected\"");
                                } else {
                                    $tpl->set_var("selected", "");
                                }

                                $tpl->parse("SezFieldSource", true);
                            }
                            reset($part);
                            $tpl->parse("SezFields", true);
                            
                            //real_name
                            $actual_data_key = "real_name";
                            $actual_data_label = "Real name";
                            $actual_levenshtein = "name";
                            
                            $tpl->set_var("SezFieldSource", "");
                            $tpl->set_var("SezFieldDestiny", "");

							if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key])) {
								foreach($tmp_import_data["data"][$actual_data_key] AS $sess_data_key => $sess_data_value) {
									 $tpl->set_var("field_destiny_id", $sess_data_value);
									 $tpl->set_var("field_destiny", $part[$sess_data_value]);
									 $tpl->parse("SezFieldDestiny", true);
								}
								reset($tmp_import_data);
							}

                            $tpl->set_var("field_name", $actual_data_label);
                            $tpl->set_var("field_id", $actual_data_key);
                            
                            foreach($part AS $part_key => $part_value) {
                            	if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key]) && array_key_exists($part_key, $tmp_import_data["data"][$actual_data_key]))
                            		continue; 
                            	
                                $tpl->set_var("field_source", $part_value);
                                $tpl->set_var("field_source_id", $part_key);
                                
                                if(strlen($actual_levenshtein) && strlen(ffCommon_url_rewrite($part_value)) && (100 * levenshtein($actual_levenshtein, ffCommon_url_rewrite($part_value)) / strlen($part_value)) <= 33) {
                                    $tpl->set_var("selected", "selected=\"selected\"");
                                } else {
                                    $tpl->set_var("selected", "");
                                }

                                $tpl->parse("SezFieldSource", true);
                            }
                            reset($part);
                            $tpl->parse("SezFields", true);

                            //category
                            $actual_data_key = "category";
                            $actual_data_label = "Categories";
                            $actual_levenshtein = "";
                            
                            $tpl->set_var("SezFieldSource", "");
                            $tpl->set_var("SezFieldDestiny", "");

							if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key])) {
								foreach($tmp_import_data["data"][$actual_data_key] AS $sess_data_key => $sess_data_value) {
									 $tpl->set_var("field_destiny_id", $sess_data_value);
									 $tpl->set_var("field_destiny", $part[$sess_data_value]);
									 $tpl->parse("SezFieldDestiny", true);
								}
								reset($tmp_import_data);
							}

                            $tpl->set_var("field_name", $actual_data_label);
                            $tpl->set_var("field_id", $actual_data_key);
                            
                            foreach($part AS $part_key => $part_value) {
                            	if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key]) && array_key_exists($part_key, $tmp_import_data["data"][$actual_data_key]))
                            		continue; 
                            	
                                $tpl->set_var("field_source", $part_value);
                                $tpl->set_var("field_source_id", $part_key);
                                
                                if(strlen($actual_levenshtein) && strlen(ffCommon_url_rewrite($part_value)) && (100 * levenshtein($actual_levenshtein, ffCommon_url_rewrite($part_value)) / strlen($part_value)) <= 33) {
                                    $tpl->set_var("selected", "selected=\"selected\"");
                                } else {
                                    $tpl->set_var("selected", "");
                                }

                                $tpl->parse("SezFieldSource", true);
                            }
                            reset($part);
                            $tpl->parse("SezFields", true);

                            //data
                            do {
	                            $actual_data_key = $db->getField("ID")->getValue();
	                            $actual_data_label = $db->getField("name")->getValue();
	                            $actual_levenshtein = $db->getField("name")->getValue();
	                            
	                            $tpl->set_var("SezFieldSource", "");
	                            $tpl->set_var("SezFieldDestiny", "");

								if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key])) {
									foreach($tmp_import_data["data"][$actual_data_key] AS $sess_data_key => $sess_data_value) {
										 $tpl->set_var("field_destiny_id", $sess_data_value);
										 $tpl->set_var("field_destiny", $part[$sess_data_value]);
										 $tpl->parse("SezFieldDestiny", true);
									}
									reset($tmp_import_data);
								}

	                            $tpl->set_var("field_name", $actual_data_label);
	                            $tpl->set_var("field_id", $actual_data_key);
	                            
	                            foreach($part AS $part_key => $part_value) {
                            		if(isset($tmp_import_data["data"][$actual_data_key]) && is_array($tmp_import_data["data"][$actual_data_key]) && count($tmp_import_data["data"][$actual_data_key]) && array_key_exists($part_key, $tmp_import_data["data"][$actual_data_key]))
                            			continue; 
                            		
	                                $tpl->set_var("field_source", $part_value);
	                                $tpl->set_var("field_source_id", $part_key);
	                                
	                                if(strlen($actual_levenshtein) && strlen(ffCommon_url_rewrite($part_value)) && (100 * levenshtein($actual_levenshtein, ffCommon_url_rewrite($part_value)) / strlen($part_value)) <= 33) {
	                                    $tpl->set_var("selected", "selected=\"selected\"");
	                                } else {
	                                    $tpl->set_var("selected", "");
	                                }

	                                $tpl->parse("SezFieldSource", true);
	                            }
	                            reset($part);
	                            $tpl->parse("SezFields", true);
                            } while($db->nextRecord());  
                        }            
                        $tpl->parse("SezRelationship", false);
                    } else {
                        $strError = "Wrong data Source (Fields not found)";
                        $tpl->set_var("SezRelationship", "");
                    }
                    fclose($fp);
                } else {
                    $strError = "Permission denied to open file";
                    $tpl->set_var("SezRelationship", "");
                }
            } else {
                $strError = "destination_data_field_empty";
                $tpl->set_var("SezRelationship", "");
            }
        } else {
            $strError = "enclosure_empty";
            $tpl->set_var("SezRelationship", "");
        }
        $tpl->set_var("SezReport", "");
    } elseif($frmAction == "import") {
    	set_session("WGimportAN", $_REQUEST);
    	
        if($enclosure && $destination_type && $field_type && $nrec && $ID_lang > 0) {
            if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan)) {
                $fp = fopen(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan, "r");
                fgetcsv($fp, 0, $sep, $enclosure);
                
                $structure = $_REQUEST["data"];
                if(isset($structure["primary"]) && isset($structure["real_name"])) {
                    $last_update = time();
                    $count_rec_insert = 0;
                    $count_rec_update = 0;
                    $count_cat_insert = 0;
                    $count_data = 0;

                    $start_category_id = ($tot_parts * $nrec) + $cci;
                    
                    while (($data_csv = fgetcsv($fp, 0, $sep, $enclosure)) !== FALSE) {
                        $count_data++;
                        
                        if($count_data <= $parts * $nrec)
                            continue;
                            
                        if($count_data > ($parts * $nrec) + $nrec) {
                            break;
                        }
                        
                        if(is_array($data_csv) && count($data_csv)) {
                        	check_function("get_structure_data");
                        		
                            
                            $limit_data = get_structure_data($data_csv, $limit_field, "-", $db);
                            
                            if(strlen($limit_data) && strlen($limit_by) && ffCommon_url_rewrite($limit_data) != ffCommon_url_rewrite($limit_by))
                                continue;

                            if(is_array($structure) && count($structure)) {
                                $arrCategory = array();
                                $primary_key = get_structure_data($data_csv, $structure["primary"], "-", $db);
                                if(!($primary_key > 0))
                                	continue;

                                $real_name = get_structure_data($data_csv, $structure["real_name"], "-", $db) . "-" . $primary_key;

                                if(strlen($fixed_pre_cat)) {
                                    $category = explode(",", $fixed_pre_cat);
                                } else {
                                    $category = array();
                                }
                                if(is_array(get_structure_data($data_csv, $structure["category"], false, $db)))
                                	$category = array_merge($category, get_structure_data($data_csv, $structure["category"], false, $db));
                                
                                if(strlen($fixed_post_cat)) {
                                    $category = array_merge($category, explode(",", $fixed_post_cat));
                                }

                                if(is_array($category) && count($category)) {
                                    foreach($category AS $category_value) {
                                        if(strlen($category_value)) {
                                            $sSQL = "SELECT * FROM anagraph_categories WHERE name = " . $db->toSql($category_value);
                                            $db->query($sSQL);
                                            if($db->nextRecord()) {
                                                $ID_category = $db->getField("ID", "Number")->getValue();
                                            } else {
			                                    $sSQL = "SELECT * FROM anagraph_categories WHERE ID = " . $db->toSql($start_category_id + $count_cat_insert + 1, "Number");
			                                    $db->query($sSQL);
			                                    if($db->nextRecord()) {
			                                        $new_key = "NULL";
			                                    } else {
			                                        $new_key = $start_category_id + $count_cat_insert + 1;
			                                    }

                                                $sSQL = "INSERT INTO `anagraph_categories` 
                                                        (
                                                            `ID`
                                                            , `name`
                                                            , `last_update`
                                                            , `owner`
                                                        )
                                                        VALUES 
                                                        (
                                                            $new_key 
                                                            , " . $db->toSql($category_value) . "
                                                            , " . $db->toSql($last_update) . "
                                                            , " . $db->toSql("-1", "Number") . "
                                                        )";
                                                $db->execute($sSQL);
                                                $ID_category = $db->getInsertID(true);
                                                
                                                $count_cat_insert++;
                                            }
                                            $arrCategory[] = $ID_category;
                                        }
                                    }
                                }
                                
                                if(is_array($arrCategory) && count($arrCategory)) {
                                    $strCategory = implode(",", $arrCategory);
                                    $strCategory = str_replace(", ", ",", trim($strCategory));
                                } else {
                                    $strCategory = "";
                                }

                                $name = get_structure_data($data_csv, $structure["name"], "-", $db);
                                $surname = get_structure_data($data_csv, $structure["surname"], "-", $db);
                                $email = get_structure_data($data_csv, $structure["email"], "-", $db);
                                $tel = get_structure_data($data_csv, $structure["tel"], "-", $db);

                                $billcf = get_structure_data($data_csv, $structure["billcf"], "-", $db);
                                $billpiva = get_structure_data($data_csv, $structure["billpiva"], "-", $db);
                                $billreference = get_structure_data($data_csv, $structure["billreference"], "-", $db);
                                $billaddress = get_structure_data($data_csv, $structure["billaddress"], "-", $db);
                                $billcap = get_structure_data($data_csv, $structure["billcap"], "-", $db);
                                $billtown = get_structure_data($data_csv, $structure["billtown"], "-", $db);
                                $billprovince = get_structure_data($data_csv, $structure["billprovince"], "-", $db);
                                $billstate = get_structure_data($data_csv, $structure["billstate"], "-", $db);

                                $shippingreference = get_structure_data($data_csv, $structure["shippingreference"], "-", $db);
                                $shippingaddress = get_structure_data($data_csv, $structure["shippingaddress"], "-", $db);
                                $shippingcap = get_structure_data($data_csv, $structure["shippingcap"], "-", $db);
                                $shippingtown = get_structure_data($data_csv, $structure["shippingtown"], "-", $db);
                                $shippingprovince = get_structure_data($data_csv, $structure["shippingprovince"], "-", $db);
                                $shippingstate = get_structure_data($data_csv, $structure["shippingstate"], "-", $db);

                                $sSQL = "SELECT * FROM anagraph WHERE name = " . $db->toSql(ffCommon_url_rewrite($real_name));
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $ID_record = $db->getField("ID", "Number")->getValue();

                                    $sSQL = "UPDATE `anagraph` 
                                            SET 
                                                `slug` = " . $db->toSql(ffCommon_url_rewrite(strlen($real_name ? $real_name : $name . " " . $surname))) . " 
                                                , `ID_type` = " . $db->toSql($field_type, "Number") . " 
                                                , `last_update` = " . $db->toSql($last_update) . "
                                                , `name` = " . $db->toSql($name) . " 
                                                , `surname` = " . $db->toSql($surname) . " 
                                                , `email` = " . $db->toSql($email) . " 
                                                , `tel` = " . $db->toSql($tel) . " 
                                                , `billcf` = " . $db->toSql($billcf) . " 
                                                , `billpiva` = " . $db->toSql($billpiva) . " 
                                                , `billreference` = " . $db->toSql($billreference) . " 
                                                , `billaddress` = " . $db->toSql($billaddress) . " 
                                                , `billcap` = " . $db->toSql($billcap) . " 
                                                , `billtown` = " . $db->toSql($billtown) . " 
                                                , `billprovince` = " . $db->toSql($billprovince) . " 
                                                , `billstate` = " . $db->toSql($billstate) . " 
                                                , `shippingreference` = " . $db->toSql($shippingreference) . " 
                                                , `shippingaddress` = " . $db->toSql($shippingaddress) . " 
                                                , `shippingcap` = " . $db->toSql($shippingcap) . " 
                                                , `shippingtown` = " . $db->toSql($shippingtown) . " 
                                                , `shippingprovince` = " . $db->toSql($shippingprovince) . " 
                                                , `shippingstate` = " . $db->toSql($shippingstate) . " 
                                                , `owner` = " . $db->toSql("-1", "Number") . " 
                                                , `categories` = " . $db->toSql($strCategory) . " 
                                            WHERE `anagraph`.`ID` = " . $db->toSql($ID_record, "Number");
                                    $db->execute($sSQL);
                                    if($db->affectedRows())
                                        $count_rec_update++;
                                        
                                } else {
                                    $sSQL = "SELECT * FROM anagraph WHERE ID = " . $db->toSql($primary_key, "Number");
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $new_key = "NULL";
                                    } else {
                                        $new_key = $primary_key;
                                    }
                                    
                                    $sSQL = "INSERT INTO `anagraph` 
                                            (
                                                `ID`
                                                , `slug`
                                                , `ID_type`
                                                , `last_update`
                                                , `name`
                                                , `surname`
                                                , `email`
                                                , `tel`
                                                , `billcf`
                                                , `billpiva`
                                                , `billreference`
                                                , `billaddress`
                                                , `billcap`
                                                , `billtown`
                                                , `billprovince`
                                                , `billstate`
                                                , `shippingreference`
                                                , `shippingaddress`
                                                , `shippingcap`
                                                , `shippingtown`
                                                , `shippingprovince`
                                                , `shippingstate`
                                                , `owner`
                                                , `categories`
                                            )
                                            VALUES 
                                            (
                                                $new_key 
                                                , " . $db->toSql(ffCommon_url_rewrite(strlen($real_name ? $real_name : $name . " " . $surname))) . "
                                                , " . $db->toSql($field_type, "Number") . "
                                                , " . $db->toSql($last_update) . "
                                                , " . $db->toSql($name) . " 
                                                , " . $db->toSql($surname) . " 
                                                , " . $db->toSql($email) . " 
                                                , " . $db->toSql($tel) . " 
                                                , " . $db->toSql($billcf) . " 
                                                , " . $db->toSql($billpiva) . " 
                                                , " . $db->toSql($billreference) . " 
                                                , " . $db->toSql($billaddress) . " 
                                                , " . $db->toSql($billcap) . " 
                                                , " . $db->toSql($billtown) . " 
                                                , " . $db->toSql($billprovince) . " 
                                                , " . $db->toSql($billstate) . " 
                                                , " . $db->toSql($shippingreference) . "
                                                , " . $db->toSql($shippingaddress) . "
                                                , " . $db->toSql($shippingcap) . "
                                                , " . $db->toSql($shippingtown) . "
                                                , " . $db->toSql($shippingprovince) . "
                                                , " . $db->toSql($shippingstate) . "
                                                , " . $db->toSql("-1", "Number") . "
                                                , " . $db->toSql($strCategory) . "
                                            )";
                                    $db->execute($sSQL);
                                    $ID_record = $db->getInsertID(true);
                                    
                                    $count_rec_insert++;
                                }
								
								$sSQL = "SELECT `anagraph_rel_nodes_fields`.*
											FROM `anagraph_rel_nodes_fields`
											WHERE `anagraph_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_record, "Number");
								$db->query($sSQL);
								if($db->nextRecord())
								{
									do {
										$arrAnagraphRelNodesFields[$db->getField("ID_anagraph", "Number", true)] = 0;
									} while ($db->nextRecord());
								}
										
                                foreach($structure AS $structure_key => $structure_value) {
                                    if($structure_key > 0) {
                                        $record_value = get_structure_data($data_csv, $structure[$structure_key], " ", $db);
										if(is_array($arrAnagraphRelNodesFields) && array_key_exists($ID_record, $arrAnagraphRelNodesFields))
										{
											$sSQL = "UPDATE `anagraph_rel_nodes_fields` 
													SET 
														`description` = " . $db->toSql($record_value) . " 
													WHERE `anagraph_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_record, "Number") . "
														AND `anagraph_rel_nodes_fields`.`ID_fields` = " . $db->toSql($structure_key, "Number");
											$db->execute($sSQL);
										} else
										{
                                            $sSQL = "INSERT INTO `anagraph_rel_nodes_fields` 
                                                    (
                                                        `ID`
                                                        , `description`
                                                        , `ID_nodes`
                                                        , `ID_fields`
                                                    )
                                                    VALUES 
                                                    (
                                                        NULL 
                                                        , " . $db->toSql($record_value) . "
                                                        , " . $db->toSql($ID_record, "Number") . "
                                                        , " . $db->toSql($structure_key, "Number") . "
                                                    )";
                                            $db->execute($sSQL);
                                        }
                                    }
                                }
                                reset($structure);
                                
                            } else {
                                $strError = "Wrong data Source (Structure not found)";
                            } 
                        } else {
                            $strError = "Wrong data Source (Fields not found)";
                        }
                    }
                    
                    if($data_csv === FALSE) {
                        $tpl->set_var("SezWait", "");
                    } else {
                        $tpl->set_var("fs", $file_scan);
                        $tpl->set_var("ct", ffCommon_specialchars($field_sep));
                        $tpl->set_var("en", ffCommon_specialchars($field_enc));

                        $tpl->set_var("destination_type", $destination_type);
                        $tpl->set_var("field_type", $field_type);
                        $tpl->set_var("lang", $ID_lang);
                        $tpl->set_var("fprecat", $fixed_pre_cat);
                        $tpl->set_var("fpostcat", $fixed_post_cat);

                        $tpl->set_var("parts", $parts + 1);
	                    $tpl->set_var("tot_parts", $tot_parts);
	                    $tpl->set_var("nrec", $nrec);
                        $tpl->set_var("cri", $cri + $count_rec_insert);
                        $tpl->set_var("cru", $cru + $count_rec_update);
                        $tpl->set_var("cci", $cci + $count_cat_insert);
                        $tpl->set_var("limit_by", $limit_by);
                        $tpl->set_var("limit_field", $limit_field);
                        
                        $tpl->set_var("visible", $visible);
                        
                        foreach($_REQUEST["data"] AS $structure_key => $structure_value) {
                            $tpl->set_var("data_id", $structure_key);
                            if(is_array($structure_value)) {
                                foreach($structure_value AS $str_sub_key => $str_sub_value) {
                                    $tpl->set_var("data_sub_id", $str_sub_key);
                                    $tpl->set_var("data_sub_value", $str_sub_value);
                                    $tpl->parse("SezData", true);
                                }
                            }
                        }

                        $tpl->set_var("count_rec_insert", $cri + $count_rec_insert);
                        $tpl->set_var("count_rec_update", $cru + $count_rec_update);
                        $tpl->set_var("count_rec_tot", $cri + $count_rec_insert + $cru + $count_rec_update);

                        $tpl->set_var("count_cat_insert", $cci + $count_cat_insert);

                        $tpl->parse("SezLang", false);
                        $tpl->parse("SezCat", false);
                        $tpl->parse("SezCatCount", false);
                        
                        $tpl->parse("SezWait", false);
                    }
                        
                } else {
                    $strError = "Primary Key and Real Name required";
                }
                fclose($fp);
            } else {
                $strError = "Permission denied to open file";
            }
        } else {
            $strError = "destination_data_field_empty";
        }
        
        
        $tpl->set_var("SezRelationship", "");
        
        
        $tpl->set_var("count_rec_insert", $cri + $count_rec_insert);
        $tpl->set_var("count_rec_update", $cru + $count_rec_update);
        $tpl->set_var("count_rec_tot", $cri + $count_rec_insert + $cru + $count_rec_update);

        $tpl->set_var("count_cat_insert", $cci + $count_cat_insert);
        
        $tpl->parse("SezCatCount", false);
        
        $tpl->parse("SezReport", false);
    } elseif($frmAction == "del") {
        if(strlen($del_anagraph)) {
            $sSQL = "DELETE FROM anagraph_rel_nodes_fields WHERE anagraph_rel_nodes_fields.ID_nodes IN ( SELECT ID FROM anagraph WHERE anagraph.last_update = " . $db->toSql($del_anagraph) . " AND anagraph.owner = '-1')";
            $db->execute($sSQL);            
            
            $sSQL = "DELETE FROM anagraph WHERE anagraph.last_update = " . $db->toSql($del_anagraph) . " AND anagraph.owner = '-1'";
            $db->execute($sSQL);
        }
        
        $tpl->set_var("SezRelationship", "");
        $tpl->set_var("SezReport", "");
    } else {
        $tpl->set_var("SezRelationship", "");
        $tpl->set_var("SezReport", "");
    }
     
    $tpl->set_var("ct", ffCommon_specialchars($field_sep));
    $tpl->set_var("en", ffCommon_specialchars($field_enc));

    $tpl->set_var("fprecat", ffCommon_specialchars($fixed_pre_cat));
    $tpl->set_var("fpostcat", ffCommon_specialchars($fixed_post_cat));

    $tpl->set_var("nrec", $nrec);
    	    
    $file_import = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH .  "/import/*");
    if(is_array($file_import) && count($file_import)) {
	    foreach ($file_import as $file) { 
	        $relative_file = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import", "", $file);
	        $tpl->set_var("source_path", $relative_file);
	        $tpl->set_var("data_source", basename($relative_file));
	        if($file_scan == $relative_file) {
	            $tpl->set_var("selected", "selected=\"selected\"");
	        } else {
	            $tpl->set_var("selected", "");
	        }
	        
	        $tpl->parse("SezDataSource", true);
	    }
	}
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "destination_type";
    $oField->label = ffTemplate::_get_word_by_code("destination_type");
    $oField->extended_type = "Selection";
    $oField->source_SQL = "
                           SELECT 
                                'anagraph' AS ID
                                , 'anagraph' AS name";
    $oField->properties["onchange"] = "document.frmMain.method = 'post'; document." . $cm->oPage->form_name . ".submit();";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";

    $oField->source_SQL = "SELECT
                                anagraph_type.ID
                                , anagraph_type.name
                           FROM
                                anagraph_type
                          ";   
    $oField->properties["onchange"] = "document.frmMain.method = 'post'; document." . $cm->oPage->form_name . ".submit();";
    $oField->value = new ffData($field_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("field_type", $oField->process());
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "lang";
    $oField->label = ffTemplate::_get_word_by_code("lang");
    $oField->extended_type = "Selection";
    $oField->source_SQL = "SELECT ID, description 
                           FROM
                                " . FF_PREFIX . "languages 
                           WHERE 1";   
    $oField->value = new ffData($ID_lang, "Number");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("lang", $oField->process());
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "del_anagraph";
    $oField->label = ffTemplate::_get_word_by_code("del_anagraph");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT
                                anagraph.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM anagraph AS tbl WHERE tbl.last_update = anagraph.last_update), ') ',  FROM_UNIXTIME(anagraph.last_update)) AS value
                           FROM
                                anagraph
                           WHERE anagraph.owner = '-1'
                          ";   
    $oField->actex_update_from_db = true;
    $oField->value = new ffData($del_anagraph, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("del_anagraph", $oField->process());
    
    
	$tpl->parse("SezLangData", false);
	$tpl->parse("SezCatData", false);
    
    $tpl->parse("SezAnagraphDel", false);
    $tpl->set_var("SezVgalleryDel", "");
    $tpl->set_var("SezOrderDel", "");

	$tpl->parse("SezFieldData", false);
    $tpl->parse("SezDelete", false);

    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    }

    $cm->oPage->addContent($tpl->rpparse("Main", false));
?>
