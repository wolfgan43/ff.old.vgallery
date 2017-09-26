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
    $ID_lang = $_REQUEST["lang"];
    $del_order = $_REQUEST["del_order"];
    
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
    	$tmp_import_data = get_session("WGimportOR");

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
                                    'ID_type' AS ID
                                    , 'ID_type' AS name
                                UNION
                                SELECT
                                    'ID_anagraph' AS ID
                                    , 'ID anagraph' AS name
                                UNION
                                SELECT
                                    'date' AS ID
                                    , 'date' AS name
                                UNION
                                SELECT
                                    'object' AS ID
                                    , 'object' AS name
                                UNION
                                SELECT
                                    'bill_note' AS ID
                                    , 'bill_note' AS name

								UNION                                    
                                SELECT
                                    'bill_discount' AS ID
                                    , 'bill_discount' AS name
								UNION                                    
                                SELECT
                                    'bill_vat' AS ID
                                    , 'bill_vat' AS name
								UNION                                    
                                SELECT
                                    'bill_shipping' AS ID
                                    , 'bill_shipping' AS name
								UNION                                    
                                SELECT
                                    'bill_total' AS ID
                                    , 'bill_total' AS name
								UNION                                    
                                SELECT
                                    'bill_account' AS ID
                                    , 'bill_account' AS name
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
    	set_session("WGimportOR", $_REQUEST);
    	
        if($enclosure && $destination_type && $field_type && $nrec && ($ID_lang > 0 || $ID_lang == FF_SYSTEM_LOCALE)) {
            if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan)) {
                $fp = fopen(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan, "r");
                fgetcsv($fp, 0, $sep, $enclosure);
                
                $structure = $_REQUEST["data"];
                if(isset($structure["primary"])) {
                    $last_update = time();
                    $count_rec_insert = 0;
                    $count_rec_update = 0;
                    $count_data = 0;

                    if(is_numeric($ID_lang) && $ID_lang > 0) {
	                    $sSQL = "SELECT " . FF_PREFIX . "languages.code
	                           FROM
	                                " . FF_PREFIX . "languages 
	                           WHERE " . FF_PREFIX . "languages.ID = " . $db->toSql($ID_lang, "Number");
	                    $db->query($sSQL);
						if($db->nextRecord())
							$lang = $db->getField("code")->getValue();
					} else {
						$lang = $ID_lang;
					}
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
                                $ID_type = get_structure_data($data_csv, $structure["ID_type"], "-", $db);
                                
                                $ID_anagraph = get_structure_data($data_csv, $structure["ID_anagraph"], "-", $db);
                                $date =  new ffData(get_structure_data($data_csv, $structure["date"], "-", $db), "Date", FF_SYSTEM_LOCALE);
                                $date = $date->getValue("Date", FF_SYSTEM_LOCALE); 
                                $object = get_structure_data($data_csv, $structure["object"], "-", $db);

                                $bill_note = get_structure_data($data_csv, $structure["bill_note"], "-", $db);
                                $bill_discount = get_structure_data($data_csv, $structure["bill_discount"], "-", $db);
                                $bill_vat = get_structure_data($data_csv, $structure["bill_vat"], "-", $db);
                                $bill_shipping = get_structure_data($data_csv, $structure["bill_shipping"], "-", $db);
                                $bill_total  = get_structure_data($data_csv, $structure["bill_total"], "-", $db);
                                $bill_account = get_structure_data($data_csv, $structure["bill_account"], "-", $db); 

                                $sSQL = "SELECT * FROM anagraph WHERE name LIKE '%" . $db->toSql("-" . $ID_anagraph, "Text", false) . "'";
                                $db->query($sSQL);
                                if($db->nextRecord())
									$ID_anagraph = $db->getField("ID", "Number")->getValue();
								
								$total_discount = $bill_discount;
								$real_vat = $bill_vat;
								$shipping_price = $bill_shipping;
								$total_bill = $bill_total;
								$total_account = $bill_account;
								
								$total_price = $total_bill + $total_discount - $bill_shipping;	
								$real_price = $total_price - $real_vat;
				                
	                            $sSQL = "SELECT * FROM ecommerce_documents_bill WHERE SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key);
	                            $db->query($sSQL);
	                            if($db->nextRecord()) {
	                                $ID_bill = $db->getField("ID", "Number")->getValue();
	                                
	                                $sSQL = "UPDATE `ecommerce_documents_bill` 
	                                        SET 
	                                            `ID_anagraph` = " . $db->toSql($ID_anagraph, "Number") . " 
	                                            , `ID_ddt` = " . $db->toSql("0", "Number") . " 
	                                            , `owner` = " . $db->toSql("-1", "Number") . "
	                                            , `last_update` = " . $db->toSql($last_update) . " 
	                                            , `operation` = " . $db->toSql($destination_type) . " 
	                                            , `ID_type` = " . $db->toSql($ID_type, "Number") . " 
	                                            , `pdf` = " . $db->toSql("") . " 
	                                            , `bill_id` = " . $db->toSql($bill_id, "Number") . " 
	                                            , `date` = " . $db->toSql($date, "Date") . " 
	                                            , `object` = " . $db->toSql($object) . " 
	                                            , `note` = " . $db->toSql($bill_note) . " 
	                                            , `total_price` = " . $db->toSql($total_price, "Number") . " 
	                                            , `total_discount` = " . $db->toSql($total_discount, "Number") . " 
	                                            , `real_price` = " . $db->toSql($real_price, "Number") . " 
	                                            , `real_vat` = " . $db->toSql($real_vat, "Number") . " 
	                                            , `shipping_price` = " . $db->toSql($shipping_price, "Number") . " 
	                                            , `total_bill` = " . $db->toSql($total_bill, "Number") . " 
	                                            , `total_account` = " . $db->toSql($total_account, "Number") . " 
	                                        WHERE `ecommerce_documents_bill`.`ID` = " . $db->toSql($ID_bill, "Number");
	                                $db->execute($sSQL);
	                                
	                                $count_rec_update++;
	                            } else {
	                                $sSQL = "INSERT INTO `ecommerce_documents_bill` 
	                                        (
	                                            `ID`
	                                            , SID
	                                            , `ID_anagraph_owner`
	                                            , `ID_anagraph`
	                                            , `ID_ddt`
	                                            , `owner`
	                                            , `last_update`
	                                            , `operation`
	                                            , `ID_type`
	                                            , `pdf`
	                                            , `bill_id`
	                                            , `date`
	                                            , `object`
	                                            , `note`
	                                            , `total_price`
	                                            , `total_discount`
	                                            , `real_price`
	                                            , `real_vat`
	                                            , `shipping_price`
	                                            , `total_bill`
	                                            , `total_account`
	                                        )
	                                        VALUES 
	                                        (
	                                            NULL
	                                            , " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key) . "
	                                            , " . $db->toSql("0", "Number") . "
	                                            , " . $db->toSql($ID_anagraph, "Number") . "
	                                            , " . $db->toSql("0", "Number") . "
	                                            , " . $db->toSql("-1", "Number") . "
	                                            , " . $db->toSql($last_update) . "
	                                            , " . $db->toSql($destination_type) . "
	                                            , " . $db->toSql($ID_type, "Number") . " 
												, " . $db->toSql("") . "
	                                            , " . $db->toSql($bill_id, "Number") . "
	                                            , " . $db->toSql($date, "Date") . "
	                                            , " . $db->toSql($object) . "
	                                            , " . $db->toSql($note) . "
	                                            , " . $db->toSql($total_price, "Number") . "
	                                            , " . $db->toSql($total_discount, "Number") . "
	                                            , " . $db->toSql($real_price, "Number") . "
	                                            , " . $db->toSql($real_vat, "Number") . "
	                                            , " . $db->toSql($shipping_price, "Number") . "
	                                            , " . $db->toSql($total_bill, "Number") . "
	                                            , " . $db->toSql($total_account, "Number") . "
	                                        )";
	                                $db->execute($sSQL);
	                                $ID_bill = $db->getInsertID(true);
	                                
	                                $count_rec_insert++;
	                            }
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
                        $tpl->set_var("parts", $parts + 1);
	                    $tpl->set_var("tot_parts", $tot_parts);
	                    $tpl->set_var("nrec", $nrec);
                        $tpl->set_var("cri", $cri + $count_rec_insert);
                        $tpl->set_var("cru", $cru + $count_rec_update);
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

                        $tpl->parse("SezLang", false);
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
            $sSQL = "DELETE FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.last_update = " . $db->toSql($del_order) . " AND ecommerce_documents_bill.owner = '-1' AND ecommerce_documents_bill.SID <> ''";
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
                                'bill_type' AS ID
                                , 'bill_type' AS name
                          ";   
    $oField->value = new ffData($field_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("field_type", $oField->process());

    $oField = ffField::factory($cm->oPage);
    $oField->id = "lang";
    $oField->label = ffTemplate::_get_word_by_code("lang");
    $oField->extended_type = "Selection";
    $oField->source_SQL = "SELECT '" . FF_SYSTEM_LOCALE . "', 'iso' 
    					   UNION
    					   SELECT ID, description 
                           FROM
                                " . FF_PREFIX . "languages 
                           WHERE 1";   
    $oField->value = new ffData($ID_lang, "Number");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("lang", $oField->process());
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "del_order";
    $oField->label = ffTemplate::_get_word_by_code("del_order");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT
                                ecommerce_documents_bill.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM ecommerce_documents_bill AS tbl WHERE tbl.last_update = ecommerce_documents_bill.last_update), ') ',  FROM_UNIXTIME(ecommerce_documents_bill.last_update)) AS value
                           FROM
                                ecommerce_documents_bill
                           WHERE ecommerce_documents_bill.owner = '-1'
                           	AND ecommerce_documents_bill.SID LIKE '" . $destination_type . "-" . ffGetFilename($file_scan) . "-%'
                          ";   
    $oField->actex_update_from_db = true;
    $oField->value = new ffData($del_order, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("del_order", $oField->process());

	$tpl->parse("SezLangData", false);
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
