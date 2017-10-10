<?php
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

    if (!AREA_IMPORT_SHOW_MODIFY) {
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

    $del_order = $_REQUEST["del_order"];
    
    $limit_by = $_REQUEST["limit_by"];
    $limit_field = $_REQUEST["limit_field"]; 

    $visible = $_REQUEST["visible"];
    $visible_operation = $_REQUEST["visible_operation"];  
    
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
    	$tmp_import_data = get_session("WGimportOP");

		if(!strlen($limit_by))
			$limit_by = $tmp_import_data["limit_by"];
			
		if(!strlen($limit_field))
			$limit_field = $tmp_import_data["limit_field"];

        $tpl->set_var("limit_by", ffCommon_specialchars($limit_by));
        
        if(strlen($enclosure)) {
            if($destination_type && $field_type && $nrec) {
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
                        $sSQL = "
                                SELECT
                                    'bill_id' AS ID
                                    , 'ID bill' AS name
								UNION                                    
                                SELECT
                                    'mpay' AS ID
                                    , 'payment_method' AS name
								UNION                                    
                                SELECT
                                    'payments_note' AS ID
                                    , 'payments_note' AS name
								UNION                                    
                                SELECT
                                    'payments_total' AS ID
                                    , 'payments_total' AS name
								UNION                                    
                                SELECT
                                    'payed_value' AS ID
                                    , 'payed_value' AS name
                                    ";
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
                            
                            //visible
							if(!strlen($visible))
								$visible = $tmp_import_data["visible"];
							if(!strlen($visible_operation))
								$visible_operation = $tmp_import_data["visible_operation"];
							
							if(strtolower($visible_operation) == "normal")
								$tpl->set_var("selected_normal", "selected=\"selected\"");
							elseif(strtolower($visible_operation) == "not")
								$tpl->set_var("selected_not", "selected=\"selected\"");

							
							if(strtolower($visible) == "yes")
								$tpl->set_var("selected_yes", "selected=\"selected\"");
							elseif(strtolower($visible) == "no")
								$tpl->set_var("selected_no", "selected=\"selected\"");
						
                            foreach($part AS $part_key => $part_value) {
                                $tpl->set_var("field_visible_value", $part_value);
                                $tpl->set_var("field_visible_id", $part_key);

                                if(strlen($visible) && $visible != "yes" && $visible != "no" && ($visible == $part_key)) {
                                    $tpl->set_var("selected", "selected=\"selected\"");
                                } else {
                                    $tpl->set_var("selected", "");
                                }

                                $tpl->parse("SezFieldVisible", true);
                            }
                            reset($part);
                            $tpl->parse("SezVisible", false);

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
    	set_session("WGimportOP", $_REQUEST);
    	
        if($enclosure && $destination_type && $field_type && $nrec) {
            if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan)) {
                $fp = fopen(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan, "r");
                fgetcsv($fp, 0, $sep, $enclosure);
                
                $structure = $_REQUEST["data"];
                if(isset($structure["primary"])) {
                    $last_update = time();
                    $count_rec_insert = 0;
                    $count_rec_update = 0;
                    $count_data = 0;

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
                                $primary_key = get_structure_data($data_csv, $structure["primary"], "-", $db);
                                if(!($primary_key > 0))
                                	continue;
                                
                                $bill_id = get_structure_data($data_csv, $structure["bill_id"], "-", $db);
                                $mpay  = get_structure_data($data_csv, $structure["mpay"], "-", $db);
                                $payments_note = get_structure_data($data_csv, $structure["payments_note"], "-", $db);
                                $payments_total  = get_structure_data($data_csv, $structure["payments_total"], "-", $db);
                                $payed_value  = get_structure_data($data_csv, $structure["payed_value"], "-", $db);

								$sSQL = "SELECT * FROM ecommerce_documents_bill WHERE SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $bill_id);
	                            $db->query($sSQL);
	                            if($db->nextRecord()) {
	                                $ID_bill = $db->getField("ID", "Number")->getValue();

									$fill_bill[$ID_bill]["bill_id"] = $ID_bill;
									$fill_bill[$ID_bill]["value"] = $fill_bill[$ID_bill]["value"] + $payments_total;
									$fill_bill[$ID_bill]["payed_value"] = $fill_bill[$ID_bill]["payed_value"] + $payed_value;
									
	                                if(is_numeric($mpay) && $mpay > 0) {
                                		$ID_mpay = $mpay;
									} elseif(strlen($mpay)) {
		                                $sSQL = "SELECT * FROM ecommerce_mpay WHERE name LIKE '%" . $db->toSql($mpay, "Text", false) . "%'";
		                                $db->query($sSQL);
										if($db->nextRecord()) {
											$ID_mpay = $db->getField("ID", "Number")->getValue();
										} else {
		                                    $sSQL = "INSERT INTO `ecommerce_mpay` 
		                                            (
		                                                `ID`
		                                                , `name`
		                                                , `days`
		                                                , `path`
		                                                , `status`
		                                                , `ecommerce`
		                                            )
		                                            VALUES 
		                                            (
		                                                NULL 
		                                                , " . $db->toSql($mpay) . "
		                                                , " . $db->toSql("0", "Number") . "
		                                                , " . $db->toSql("") . "
		                                                , " . $db->toSql("1") . "
		                                                , " . $db->toSql("1") . "
		                                            )";
		                                    $db->execute($sSQL);
		                                    $ID_mpay = $db->getInsertID(true);
										}
									} else {
		                                $sSQL = "SELECT * FROM ecommerce_mpay WHERE name = " . $db->toSql("mpay_not_set");
		                                $db->query($sSQL);
										if($db->nextRecord()) {
											$ID_mpay = $db->getField("ID", "Number")->getValue();
										} else {
		                                    $sSQL = "INSERT INTO `ecommerce_mpay` 
		                                            (
		                                                `ID`
		                                                , `name`
		                                                , `days`
		                                                , `path`
		                                                , `status`
		                                                , `ecommerce`
		                                            )
		                                            VALUES 
		                                            (
		                                                NULL 
		                                                , " . $db->toSql("mpay_not_set") . "
		                                                , " . $db->toSql("0", "Number") . "
		                                                , " . $db->toSql("") . "
		                                                , " . $db->toSql("1") . "
		                                                , " . $db->toSql("0") . "
		                                            )";
		                                    $db->execute($sSQL);
		                                    $ID_mpay = $db->getInsertID(true);
										}
									}	
	                                
	                                

	                                if($ID_bill > 0 && $ID_mpay > 0) {
		                                $sSQL = "SELECT * 
		                                		FROM ecommerce_documents_payments 
		                                		WHERE ecommerce_documents_payments.status > 0 
		                                			AND ecommerce_documents_payments.ID_bill = " . $db->toSql($ID_bill, "Number") . " 
		                                			AND ecommerce_documents_payments.ID_ecommerce_mpay = " . $db->toSql($ID_mpay, "Number") . "
		                                			AND ecommerce_documents_payments.SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key);
		                                $db->query($sSQL);
		                                if($db->nextRecord()) {
	                                		$ID_record = $db->getField("ID", "Number")->getValue();
	                                		
		                                    $sSQL = "UPDATE `ecommerce_documents_payments` 
		                                            SET 
		                                            	SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key) . " 
		                                                , `ID_bill` = " . $db->toSql($ID_bill, "Number") . " 
		                                                , `ID_ecommerce_mpay` = " . $db->toSql($ID_mpay, "Number") . " 
		                                                , `operation` = " . $db->toSql($destination_type) . "
		                                                , `description` = " . $db->toSql($payments_note) . " 
		                                                , `value` = " . $db->toSql($payments_total, "Number") . " 
		                                                , `payed_value` = " . $db->toSql($payed_value, "Number") . " 
		                                                , `status` = " . $db->toSql("1") . " 
		                                                , `owner` = " . $db->toSql("-1", "Number") . " 
		                                                , `last_update` = " . $db->toSql($last_update) . " 
		                                            WHERE `ecommerce_documents_payments`.`ID` = " . $db->toSql($ID_record, "Number");
		                                    $db->execute($sSQL);
		                                    if($db->affectedRows())
                                        		$count_rec_update++;
                                        		
		                                } else {
		                                    $sSQL = "INSERT INTO `ecommerce_documents_payments` 
		                                            (
		                                                `ID`
		                                                , SID
		                                                , `ID_bill`
		                                                , `ID_ecommerce_mpay`
		                                                , `operation`
		                                                , `description`
		                                                , `value`
		                                                , `payed_value`
		                                                , `status`
		                                                , `owner`
		                                                , `last_update`
		                                                , `tax_price`
		                                            )
		                                            VALUES 
		                                            (
		                                                NULL 
		                                                , " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key) . " 
		                                                , " . $db->toSql($ID_bill, "Number") . " 
		                                                , " . $db->toSql($ID_mpay, "Number") . " 
		                                                , " . $db->toSql($destination_type) . "
		                                                , " . $db->toSql($payments_note) . " 
		                                                , " . $db->toSql($payments_total, "Number") . " 
		                                                , " . $db->toSql($payed_value, "Number") . " 
														, " . $db->toSql("1") . " 
		                                                , " . $db->toSql("-1", "Number") . "
		                                                , " . $db->toSql($last_update) . "
		                                                , (SELECT ecommerce_mpay.price FROM ecommerce_mpay WHERE ecommerce_mpay.ID = " . $db->toSql($ID_mpay, "Number") . ") 
		                                            )";
		                                    $db->execute($sSQL);
		                                    $ID_record = $db->getInsertID(true);
		                                    
		                                    $count_rec_insert++;
		                                }
									}
								}
                            } else {
                                $strError = "Wrong data Source (Structure not found)";
                            } 
                        } else {
                            $strError = "Wrong data Source (Fields not found)";
                        }
                    }
                    
                    if(strtolower($visible) == "yes") {
                        if(strtolower($visible_operation) == "not")
                            $visible_data = 0;
                        else
                            $visible_data = 1;
                    } elseif(strtolower($visible) == "no") {
                        if(strtolower($visible_operation) == "not")
                            $visible_data = 1;
                        else
                            $visible_data = 0;
                    } else {
                        $visible_data = get_structure_data($data_csv, $visible, "-", $db);
                        if($visible_data) {
                            if(strtolower($visible_operation) == "not")
                                $visible_data = 0;
	                        else
                                $visible_data = 1;
                        } else {
                            if(strtolower($visible_operation) == "not")
                                $visible_data = 1;
	                        else
                                $visible_data = 0;
                        }
                    }

                    if($visible) {/*
                    	if(is_array($fill_bill) && count($fill_bill)) {
                    		foreach($fill_bill AS $fill_bill_key => $fill_bill_value) {
                    			if(check_function("ecommerce_get_total_by_order"))
                    				$total_order = ecommerce_get_total_by_order($fill_bill_value["bill_id"]);
                    			if($total_order["total_order"] > $fill_bill_value["value"]) {
                    				$total_discount = ($total_order["total_order"] - $fill_bill_value["value"]);
	                                $sSQL = "UPDATE `ecommerce_documents_bill` 
	                                        SET 
	                                            `total_discount` = (" . $db->toSql($total_discount, "Number") . ")
	                                            , total_bill = " . ($total_order["total_order"] - $total_discount) . "
	                                        WHERE `ecommerce_documents_bill`.`ID` = " . $db->toSql($fill_bill_key, "Number");
	                                $db->execute($sSQL);
								}
							}
						}*/
					}
                    
                    
                    if($data_csv === FALSE) {
                        $tpl->set_var("SezWait", "");
                    } else {
                        $tpl->set_var("fs", $file_scan);
                        $tpl->set_var("ct", ffCommon_specialchars($field_sep));
                        $tpl->set_var("en", ffCommon_specialchars($field_enc));

                        $tpl->set_var("destination_type", $destination_type);
                        $tpl->set_var("field_type", $field_type);

                        $tpl->set_var("parts", $parts + 1);
	                    $tpl->set_var("tot_parts", $tot_parts);
	                    $tpl->set_var("nrec", $nrec);
                        $tpl->set_var("cri", $cri + $count_rec_insert);
                        $tpl->set_var("cru", $cru + $count_rec_update);
                        $tpl->set_var("limit_by", $limit_by);
                        $tpl->set_var("limit_field", $limit_field);
                        
                        $tpl->set_var("visible", $visible);
                        $tpl->set_var("visible_operation", $visible_operation);
                        
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

                        $tpl->set_var("SezLang", "");
                        $tpl->set_var("SezCat", "");
                        $tpl->set_var("SezCatCount", "");

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

        $tpl->set_var("SezCatCount", "");
        
        $tpl->parse("SezReport", false);
    } elseif($frmAction == "del") {
        if(strlen($del_order)) {
            $sSQL = "DELETE FROM ecommerce_documents_payments 
            		WHERE ecommerce_documents_payments.last_update = " . $db->toSql($del_order) . " AND ecommerce_documents_payments.owner = '-1'";
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
                                'sent' AS ID
                                , 'bill sent' AS name
                           UNION
                           SELECT 
                                'received' AS ID
                                , 'bill received' AS name";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";

    $oField->source_SQL = "SELECT
                                'bill_payments' AS ID
                                , 'Bill Payments' AS name
                          ";   
    $oField->value = new ffData($field_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("field_type", $oField->process());

    $oField = ffField::factory($cm->oPage);
    $oField->id = "del_order";
    $oField->label = ffTemplate::_get_word_by_code("del_order");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT
                                ecommerce_documents_payments.last_update
                                , CONCAT('(', (SELECT COUNT(*) FROM ecommerce_documents_payments AS tbl WHERE tbl.status > 0 AND tbl.last_update = ecommerce_documents_payments.last_update), ') ',  FROM_UNIXTIME(ecommerce_documents_payments.last_update)) AS value
                           FROM
                                ecommerce_documents_payments
                           WHERE ecommerce_documents_payments.owner = '-1'
						   		AND ecommerce_documents_payments.SID LIKE '" . $destination_type . "-" . ffGetFilename($file_scan) . "-%'
                          ";   
    $oField->actex_update_from_db = true;
    $oField->value = new ffData($del_order, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("del_order", $oField->process());

	$tpl->set_var("SezLangData", "");
	$tpl->set_var("SezCatData", "");
    
    $tpl->set_var("SezAnagraphDel", "");
    $tpl->set_var("SezVgalleryDel", "");
    $tpl->parse("SezOrderDel", false);

    $tpl->parse("SezFieldData", false);
    $tpl->parse("SezDelete", false);

    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    }
    
    $cm->oPage->addContent($tpl->rpparse("Main", false));
?>
