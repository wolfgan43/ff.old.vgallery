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
    	$tmp_import_data = get_session("WGimportOD");

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
                                    'ID_bill' AS ID
                                    , 'ID bill' AS name
                                UNION
                                SELECT
                                    'bill_id' AS ID
                                    , 'bill id' AS name
                                UNION
                                SELECT
                                    'bill_ID_anagraph' AS ID
                                    , 'bill ID anagraph' AS name
                                UNION
                                SELECT
                                    'bill_date' AS ID
                                    , 'bill date' AS name
                                UNION
                                SELECT
                                    'bill_object' AS ID
                                    , 'bill object' AS name
                                UNION
                                SELECT
                                    'bill_note' AS ID
                                    , 'bill note' AS name
                                UNION
                                SELECT
                                    'ID_items' AS ID
                                    , 'ID item' AS name
                                UNION
                                SELECT
                                    'description' AS ID
                                    , 'description' AS name
                                UNION
                                SELECT
                                    'qta' AS ID
                                    , 'qta' AS name
                                UNION
                                SELECT
                                    'price' AS ID
                                    , 'price' AS name
                                UNION
                                SELECT
                                    'discount' AS ID
                                    , 'discount' AS name
                                UNION
                                SELECT
                                    'vat' AS ID
                                    , 'vat' AS name
                                UNION
                                SELECT
                                    'vat_indetraible' AS ID
                                    , 'vat_indetraible' AS name
                                UNION
                                SELECT
                                    'decumulation' AS ID
                                    , 'decumulation' AS name
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
    	set_session("WGimportOD", $_REQUEST);
    	
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

                                if(isset($structure["ID_bill"]))
                                	$ID_bill = get_structure_data($data_csv, $structure["ID_bill"], "-", $db);
                                else
                                	$ID_bill = false;

                                if(isset($structure["bill_id"]))
                                	$bill_id = get_structure_data($data_csv, $structure["bill_id"], "-", $db);
                                else
                                	$bill_id = null;

                                if(isset($structure["bill_ID_type"]))
                                	$bill_ID_type = get_structure_data($data_csv, $structure["bill_ID_type"], "-", $db);
                                else
                                	$bill_ID_type = null;

                                if(isset($structure["bill_ID_anagraph"]))
                                	$bill_ID_anagraph = get_structure_data($data_csv, $structure["bill_ID_anagraph"], "-", $db);
                                else
                                	$bill_ID_anagraph = null;

                                if(isset($structure["bill_date"])) {
	                                $bill_date =  new ffData(get_structure_data($data_csv, $structure["bill_date"], "-", $db), "Date", FF_SYSTEM_LOCALE);
	                                $bill_date = $bill_date->getValue("Date", FF_SYSTEM_LOCALE); 
								} else
                                	$bill_date = null;

                                if(isset($structure["bill_object"]))
                                	$bill_object = get_structure_data($data_csv, $structure["bill_object"], "-", $db);
                                else
                                	$bill_object = null;

                                if(isset($structure["bill_note"]))
                                	$bill_note = get_structure_data($data_csv, $structure["bill_note"], "-", $db);
                                else
                                	$bill_note = null;

                                if(isset($structure["ID_items"]))
                                	$ID_items = get_structure_data($data_csv, $structure["ID_items"], "-", $db);
                                else
                                	$ID_items = false;

                                if(isset($structure["description"]))
                                	$description = get_structure_data($data_csv, $structure["description"], "-", $db);
                                else
                                	$description = false;
                                
                                if(isset($structure["qta"]))
                                	$qta = get_structure_data($data_csv, $structure["qta"], "-", $db);
                                else
                                	$qta = false;
                                
                                if(isset($structure["price"]))
                                	$price = get_structure_data($data_csv, $structure["price"], "-", $db);
                                else 
                                	$price = false;
                                	
                                if(isset($structure["discount"]))
                                	$discount = get_structure_data($data_csv, $structure["discount"], "-", $db);
                                else 
                                	$discount = false;

                                if(isset($structure["vat"]))
                                	$vat = get_structure_data($data_csv, $structure["vat"], "-", $db);
                                else 
                                	$vat = false;

                                if(isset($structure["vat_indetraible"]))
                                	$vat_indetraible = get_structure_data($data_csv, $structure["vat_indetraible"], "-", $db);
                                else 
                                	$vat_indetraible = false;

                                if(isset($structure["decumulation"]))
                                	$decumulation = get_structure_data($data_csv, $structure["decumulation"], "-", $db);
                                else 
                                	$decumulation = false;
                                	
			                    
			                    if(!is_numeric($ID_items)) {
	                                $sSQL = "SELECT * FROM vgallery_nodes WHERE name = " . $db->toSql(ffCommon_url_rewrite($ID_items));
	                                $db->query($sSQL);
	                                if($db->nextRecord()) {
	                                    $ID_items = $db->getField("ID", "Number")->getValue();
									}
								}
			                    
			                    $sSQL = "SELECT ecommerce_documents_bill_detail.* 
			                            FROM ecommerce_documents_bill_detail
			                            WHERE ecommerce_documents_bill_detail.SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key, "Text");
			                    $db->query($sSQL);
			                    if($db->nextRecord()) {
			                        $ID_bill_detail = $db->getField("ID", "Number")->getValue();
			                        $ID_bill = $db->getField("ID_bill", "Number")->getValue();

			                        $sSQL = "UPDATE `ecommerce_documents_bill_detail` 
			                                SET 
		                                        `owner` = " . $db->toSql("-1", "Number") . "
		                                        , `last_update` = " . $db->toSql($last_update) . "
		                                        " . ($ID_bill === false
		                                        	? ""
		                                        	: ", `ID_bill` =  " . $db->toSql($ID_bill, "Number")
		                                        ) . 
		                                        ($ID_items === false
		                                        	? ""
		                                        	: " , tbl_src = 'vgallery_nodes'
		                                        		, `ID_items` =  " . $db->toSql($ID_items)
		                                        ) . 
		                                        ($description === false
		                                        	? ""
		                                        	: ", `description` =  " . $db->toSql($description)
		                                        ) . 
		                                        ($qta === false
		                                        	? ""
		                                        	: ", `qta` =  " . $db->toSql($qta)
		                                        ) . 
		                                        ($price === false
		                                        	? ""
		                                        	: ", `price` =  " . $db->toSql($price)
		                                        ) . 
		                                        ($discount === false
		                                        	? ""
		                                        	: ", `discount` =  " . $db->toSql($discount)
		                                        ) . 
		                                        ($vat === false
		                                        	? ""
		                                        	: ", `vat` =  " . $db->toSql($vat)
		                                        ) . 
		                                        ($vat_indetraible === false
		                                        	? ""
		                                        	: ", `vat_indetraible` =  " . $db->toSql($vat_indetraible)
		                                        ) . 
		                                        ($decumulation === false
		                                        	? ""
		                                        	: ", `decumulation` =  " . $db->toSql($decumulation)
		                                        ) . "
			                                WHERE `ecommerce_documents_bill_detail`.`ID` = " . $db->toSql($ID_bill_detail, "Number");
			                        $db->execute($sSQL);

			                        $count_rec_update++;
			                    } else {
			                    	if($ID_bill > 0) {
			                    		$sSQL = "SELECT * FROM ecommerce_documents_bill WHERE SID = " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $ID_bill, "Text");
 										$db->query($sSQL);
			                    		if($db->nextRecord()) {
			                    			$ID_bill = $db->getField("ID", "Number", true);
										} else {
											$ID_bill = false;
										} 
									}

					                if(!$ID_bill) {
										if($bill_ID_anagraph === null)
											$bill_data["ID_anagraph"] = 0;
										else {
											$sSQL = "SELECT * FROM anagraph WHERE name LIKE '%" . $db->toSql("-" . $bill_ID_anagraph, "Text", false) . "'";
			                                $db->query($sSQL);
			                                if($db->nextRecord())
												$bill_data["ID_anagraph"] = $db->getField("ID", "Number")->getValue();
											else
												$bill_data["ID_anagraph"] = 0;
										}
											
											
										if($bill_ID_type === null)
											$bill_data["ID_type"] = 0;
										else
											$bill_data["ID_type"] = $bill_ID_type;
											
										if($bill_id === null) {
											$sSQL = "(SELECT IF(ISNULL(MAX(CAST(ecommerce_documents_bill.bill_id AS SIGNED))), 1, MAX(CAST(ecommerce_documents_bill.bill_id AS SIGNED)) + 1) AS bill_id FROM ecommerce_documents_bill WHERE (YEAR(CURDATE()) = YEAR(ecommerce_documents_bill.date) OR ecommerce_documents_bill.date = '0000-00-00') AND ecommerce_documents_bill.ID_type = " . $db->toSql($bill_data["ID_type"]) . ")";
											$db->query($sSQL);
											if($db->nextRecord()) 
												$bill_data["bill_id"] = $db->getField("bill_id", "Number", true);
											else 
												$bill_data["bill_id"] = 0;
										} else {
											$bill_data["bill_id"] = $bill_id;
										}
										
										if($bill_date === null)
											$bill_data["date"] = "0000-00-00";
										else
											$bill_data["date"] = $bill_date;
											
										if($destination_type === null)
											$bill_data["operation"] = "";
										else
											$bill_data["operation"] = $destination_type;
											
										if($bill_object === null)
											$bill_data["object"] = "";
										else
											$bill_data["object"] = $bill_object;
											
										if($bill_note === null)
											$bill_data["note"] = "";
										else
											$bill_data["note"] = $bill_note;

								        $sSQL = "INSERT INTO `ecommerce_documents_bill` 
								                (
								                    `ID`
								                    , SID
								                    , `ID_ddt`
								                    , `ID_anagraph_owner`
								                    , `ID_anagraph`
								                    , `owner`
								                    , `last_update`
								                    , `operation`
								                    , `ID_type`
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
								                ) VALUES (
								                    NULL 
								                    , " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key) . "
								                    , '0'
								                    , '0'
								                    , " . $db->toSql($bill_data["ID_anagraph"]) . "
								                    , " . $db->toSql("-1", "Number") . " 
								                    , " . $db->toSql(time()) . " 
								                    , " . $db->toSql($bill_data["operation"]) . "
								                    , " . $db->toSql($bill_data["ID_type"]) . "
													, " . $db->toSql($bill_data["bill_id"]) . "
								                    , " . $db->toSql($bill_data["date"], "Date") . "
								                    , " . $db->toSql($bill_data["object"]) . "
								                    , " . $db->toSql($bill_data["note"]) . "
								                    , '0'
								                    , '0'
								                    , '0'
								                    , '0'
								                    , '0'
								                    , '0'
								                    , '0'
								                )";
								        $db->execute($sSQL);
								        $ID_bill = $db->getInsertID(true);
											
									}
			                    	
			                        $sSQL = "INSERT INTO `ecommerce_documents_bill_detail` 
			                                (
			                                    `owner`
			                                    , `last_update`
			                                    , SID
		                                        " . ($ID_bill === false
		                                        	? ""
		                                        	: ", `ID_bill`"
		                                        ) . 
		                                        ($ID_items === false
		                                        	? ""
		                                        	: " , tbl_src 
		                                        		, `ID_items`"
		                                        ) . 
		                                        ($description === false
		                                        	? ""
		                                        	: ", `description`"
		                                        ) . 
		                                        ", `qta`" . 
		                                        ($price === false
		                                        	? ""
		                                        	: ", `price`"
		                                        ) . 
		                                        ($discount === false
		                                        	? ""
		                                        	: ", `discount`"
		                                        ) . 
		                                        ($vat === false
		                                        	? ""
		                                        	: ", `vat`"
		                                        ) . 
		                                        ($vat_indetraible === false
		                                        	? ""
		                                        	: ", `vat_indetraible`"
		                                        ) . 
												", `decumulation`
			                                ) VALUES (
		                                        " . $db->toSql("-1", "Number") . "
		                                        , " . $db->toSql($last_update) . "
		                                        , " . $db->toSql($destination_type . "-" . ffGetFilename($file_scan) . "-" . $primary_key) . "
		                                        " . ($ID_bill === false
		                                        	? ""
		                                        	: ", " . $db->toSql($ID_bill, "Number")
		                                        ) . 
		                                        ($ID_items === false
		                                        	? ""
		                                        	: " , 'vgallery_nodes'
		                                        		, " . $db->toSql($ID_items)
		                                        ) . 
		                                        ($description === false
		                                        	? ""
		                                        	: ", " . $db->toSql($description)
		                                        ) . 
		                                        ($qta === false
		                                        	? ", 1 "
		                                        	: ", " . $db->toSql($qta)
		                                        ) . 
		                                        ($price === false
		                                        	? ""
		                                        	: ", " . $db->toSql($price)
		                                        ) . 
		                                        ($discount === false
		                                        	? ""
		                                        	: ", " . $db->toSql($discount)
		                                        ) . 
		                                        ($vat === false
		                                        	? ""
		                                        	: ", " . $db->toSql($vat)
		                                        ) . 
		                                        ($vat_indetraible === false
		                                        	? ""
		                                        	: ", " . $db->toSql($vat_indetraible)
		                                        ) . 
		                                        ($decumulation === false
		                                        	? ", 'scorporo'"
		                                        	: ", " . $db->toSql($decumulation)
		                                        ) . "
			                                )";
			                        $db->execute($sSQL);
			                        $ID_bill_detail = $db->getInsertID(true);

			                        $count_rec_insert++;
			                    }
								
								if(check_function("ecommerce_recalc_documents_bill"))
									ecommerce_recalc_documents_bill($ID_bill);
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
            $sSQL = "DELETE FROM ecommerce_documents_bill_detail WHERE ecommerce_documents_bill_detail.last_update = " . $db->toSql($del_order) . " AND ecommerce_documents_bill_detail.owner = '-1' AND ecommerce_documents_bill_detail.SID <> ''";
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
                                , 'Bill Detail sent' AS name
                           UNION
                           SELECT 
                                'received' AS ID
                                , 'Bill Detail received' AS name";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";

    $oField->source_SQL = "SELECT
                                'bill_detail_type' AS ID
                                , 'bill_detail_type' AS name
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
                                ecommerce_documents_bill_detail.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM ecommerce_documents_bill_detail AS tbl WHERE tbl.last_update = ecommerce_documents_bill_detail.last_update), ') ',  FROM_UNIXTIME(ecommerce_documents_bill_detail.last_update)) AS value
                           FROM
                                ecommerce_documents_bill_detail
                           WHERE ecommerce_documents_bill_detail.owner = '-1'
                           	AND ecommerce_documents_bill_detail.SID LIKE '" . $destination_type . "-" . ffGetFilename($file_scan) . "-%'
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
