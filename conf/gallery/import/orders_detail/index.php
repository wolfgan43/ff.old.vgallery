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
                                    'ID_order' AS ID
                                    , 'ID order' AS name
                                UNION
                                SELECT
                                    'ID_item' AS ID
                                    , 'ID item' AS name
                                UNION
                                SELECT
                                    'description' AS ID
                                    , 'description' AS name
                                UNION
                                SELECT
                                    'date_since' AS ID
                                    , 'date_since' AS name
                                UNION
                                SELECT
                                    'date_to' AS ID
                                    , 'date_to' AS name
                                UNION
                                SELECT
                                    'qta' AS ID
                                    , 'qta' AS name
                                UNION
                                SELECT
                                    'stock_used' AS ID
                                    , 'stock_used' AS name
                                UNION
                                SELECT
                                    'reserve_stock_used' AS ID
                                    , 'reserve_stock_used' AS name
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

                                $real_name = get_structure_data($data_csv, $structure["real_name"], "-", $db) . "-" . $primary_key;
                                
                                
                                $ID_order = get_structure_data($data_csv, $structure["ID_order"], "-", $db);
                                $ID_item = get_structure_data($data_csv, $structure["ID_item"], "-", $db);
                                
                                if(isset($structure["description"]))
                                	$description = get_structure_data($data_csv, $structure["description"], "-", $db);
                                else
                                	$description = false;
                                
                                
                                if(isset($structure["date_since"]))
                                	$date_since = new ffData(get_structure_data($data_csv, $structure["date_since"], "-", $db), "Date", $lang);
                                else
                                	$date_since = false;

                                if(isset($structure["date_to"]))
                                	$date_to = new ffData(get_structure_data($data_csv, $structure["date_to"], "-", $db), "Date", $lang);
                                else
                                	$date_to = false;

                                if(isset($structure["qta"]))
                                	$qta = get_structure_data($data_csv, $structure["qta"], "-", $db);
                                else
                                	$qta = 0;
                                
                                if(isset($structure["stock_used"]))
                                	$stock_used = get_structure_data($data_csv, $structure["stock_used"], "-", $db);
                                else 
                                	$stock_used = 0;
                                	
                                if(isset($structure["reserve_stock_used"]))
                                	$reserve_stock_used = get_structure_data($data_csv, $structure["reserve_stock_used"], "-", $db);
                                else 
                                	$reserve_stock_used = 0;

                                $sSQL = "SELECT vgallery_nodes.*
                                			, vgallery.name AS vgallery_name 
                                		FROM vgallery_nodes 
                                			INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                		WHERE vgallery_nodes.name = " . $db->toSql(ffCommon_url_rewrite($real_name));
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $ID_item = $db->getField("ID", "Number")->getValue();
                                    $full_path = stripslash($db->getField("parent")->getValue()) . "/" . $db->getField("name")->getValue();
									$vgallery_name = $db->getField("vgallery_name")->getValue();
									
									$sSQL = "SELECT * FROM ecommerce_order WHERE SID = " . $db->toSql($ID_order);
	                                $db->query($sSQL);
	                                if($db->nextRecord()) {
	                                    $ID_order = $db->getField("ID", "Number")->getValue();
	                                    $ID_shipping = $db->getField("ID_ecommerce_shipping", "Number")->getValue();
	                                    $ID_anagraph = $db->getField("ID_anagraph", "Number")->getValue();

										if($stock_used + $reserve_stock_used > 0)
											$qta = ($stock_used + $reserve_stock_used);

	                                    if($description === false) {
											if(check_function("get_vgallery_card")) {
												$vgallery_data =  get_vgallery_card_by_id($ID_item);
												$description = $vgallery_data["card"];
											} 
											/*if(check_function("get_vgallery_description")) {
												$description = get_vgallery_description($full_path, "[TAG]"); 
											}*/
										}
										
										if($date_since === false || $date_to === false) {
											$pricelist = NULL;
											$date_since = "0";
											$date_to = "0";
											$strDate = "";
										} else {
			                               	$pricelist["range"]["since"] = $date_since->getValue("Timestamp", FF_SYSTEM_LOCALE); //$recordset_values[$rel_key]["date_since"];
			                               	$pricelist["range"]["to"] = $date_to->getValue("Timestamp", FF_SYSTEM_LOCALE); //$recordset_values[$rel_key]["date_to"];

											$date_since = $pricelist["range"]["since"];
											$date_to = $pricelist["range"]["to"];
											$strDate = " (" . $date_since->getValue("Date", FF_LOCALE) . "-" . $date_to->getValue("Date", FF_LOCALE) .")";
										}
											
										if(check_function("ecommerce_get_file_properties_ecommerce"))
											$file_properties_ecommerce = ecommerce_get_file_properties_ecommerce($full_path, "vgallery_nodes");
										
										if($file_properties_ecommerce["useunic"]) {
							                if($file_properties_ecommerce["qta_min"] > 0) {
							                    $qta = $file_properties_ecommerce["qta_min"];
							                } else {
							                    $qta = 1;
							                }
										}
																				
										if(check_function("ecommerce_get_file_pricelist"))
											$file_pricelist_ecommerce = ecommerce_get_file_pricelist($full_path, "vgallery_nodes", $qta, true, $file_properties_ecommerce["type"], $pricelist, true, null, $file_properties_ecommerce);

		                                if(isset($file_pricelist_ecommerce["price"]) && $file_pricelist_ecommerce["price"] > 0) {
		                                   $real_price = $file_pricelist_ecommerce["price"];
		                                } else {
											$real_price = -1;
										}

		                                if(isset($file_pricelist_ecommerce["discount"]) && $file_pricelist_ecommerce["discount"] !== NULL && $file_pricelist_ecommerce["discount"] > 0) {
		                                   $real_discount = $file_pricelist_ecommerce["discount"];
		                                } else {
		                                   $real_discount = 0;
		                                }

		                                $sSQL = "SELECT ecommerce_documents_bill.* 
		                                        FROM ecommerce_documents_bill
		                                            INNER JOIN ecommerce_order ON ecommerce_order.ID_bill = ecommerce_documents_bill.ID
		                                        WHERE ecommerce_order.ID = " . $db->toSql($ID_order, "Number");
		                                $db->query($sSQL);
		                                if($db->nextRecord()) {
		                                	$ID_bill = $db->getField("ID", "Number")->getValue();

			                                $sSQL = "SELECT ecommerce_documents_bill_detail.* 
			                                        FROM ecommerce_documents_bill_detail
			                                            INNER JOIN ecommerce_order_detail ON ecommerce_order_detail.ID_bill_detail = ecommerce_documents_bill_detail.ID
			                                        WHERE ecommerce_order_detail.ID_order = " . $db->toSql($ID_order, "Number") . "
														 AND ID_items = " . $db->toSql($ID_item, "Number");
			                                $db->query($sSQL);
			                                if($db->nextRecord()) {
			                                    $ID_bill_detail = $db->getField("ID", "Number")->getValue();

			                                    $sSQL = "UPDATE `ecommerce_documents_bill_detail` 
			                                            SET 
		                                                    `ID_bill` =  " . $db->toSql($ID_bill, "Number") . "
		                                                    , `description` =  " . $db->toSql($description . $strDate) . "
		                                                    , `owner` = " . $db->toSql("-1", "Number") . "
		                                                    , `last_update` = " . $db->toSql($last_update) . "
		                                                    , `qta` = " . $db->toSql($qta, "Number") . "
		                                                    , `price` = " . $db->toSql($real_price, "Number") . "
		                                                    , `discount` = " . $db->toSql($real_discount, "Number") . "
		                                                    , `vat` = " . $db->toSql($file_properties_ecommerce["vat"], "Number") . "
		                                                    , `vat_indetraible` = " . $db->toSql($file_properties_ecommerce["vat_indetraible"], "Number") . "
		                                                    , `decumulation` = " . $db->toSql($file_properties_ecommerce["decumulation"]) . "
			                                            WHERE `ecommerce_documents_bill_detail`.`ID` = " . $db->toSql($ID_bill_detail, "Number");
			                                    $db->execute($sSQL);
			                                } else {
			                                    $sSQL = "INSERT INTO `ecommerce_documents_bill_detail` 
			                                            (
			                                                `ID`
			                                                , `ID_bill`
			                                                , `description`
			                                                , `owner`
			                                                , `last_update`
			                                                , `qta`
			                                                , `price`
			                                                , `discount`
			                                                , `vat`
			                                                , `vat_indetraible`
			                                                , `decumulation`
			                                            ) VALUES (
			                                                NULL 
		                                                    , " . $db->toSql($ID_bill, "Number") . "
		                                                    , " . $db->toSql($description . $strDate) . "
		                                                    , " . $db->toSql("-1", "Number") . "
		                                                    , " . $db->toSql($last_update) . "
		                                                    , " . $db->toSql($qta, "Number") . "
		                                                    , " . $db->toSql($real_price, "Number") . "
		                                                    , " . $db->toSql($real_discount, "Number") . "
		                                                    , " . $db->toSql($file_properties_ecommerce["vat"], "Number") . "
		                                                    , " . $db->toSql($file_properties_ecommerce["vat_indetraible"], "Number") . "
		                                                    , " . $db->toSql($file_properties_ecommerce["decumulation"]) . "
			                                            )";
			                                    $db->execute($sSQL);
			                                    $ID_bill_detail = $db->getInsertID(true);
			                                }
										} else {
											$ID_bill_detail = 0;
										}		                                

	                                    
		                                $sSQL = "SELECT * FROM ecommerce_order_detail WHERE ID_order = " . $db->toSql($ID_order, "Number") . " AND ID_items = " . $db->toSql($ID_item, "Number");
		                                $db->query($sSQL);
		                                if($db->nextRecord()) {
		                                    $ID_record = $db->getField("ID", "Number")->getValue();

		                                    $sSQL = "UPDATE ecommerce_order_detail
		                                            SET 
		                                                `ID_order` = " . $db->toSql($ID_order, "Number") . " 
		                                                , `ID_bill_detail` = " . $db->toSql($ID_bill_detail, "Number") . " 
		                                                , `tbl_src` = " . $db->toSql("vgallery_nodes") . " 
		                                                , `ID_items` = " . $db->toSql($ID_item, "Number") . " 
		                                                , `description` = " . $db->toSql($description . $strDate) . " 
		                                                , `date_since` = " . $db->toSql($date_since, "Number") . " 
		                                                , `date_to` = " . $db->toSql($date_to, "Number") . "
		                                                , `type` = " . $db->toSql($file_properties_ecommerce["type"]) . "
		                                                , `owner` = " . $db->toSql("-1", "Number") . "
		                                                , `last_update` = " . $db->toSql($last_update) . "
		                                                , `qta` = " . $db->toSql($qta, "Number") . "
		                                                , `stock_used` = " . $db->toSql($stock_used, "Number") . "
		                                                , `reserve_stock_used` = " . $db->toSql($reserve_stock_used, "Number") . "
		                                                , `price` = " . $db->toSql($real_price, "Number") . "
		                                                , `discount` = " . $db->toSql($real_discount, "Number") . "
		                                                , `account` = " . $db->toSql($file_properties_ecommerce["account"], "Number") . "
		                                                , `weight` = " . $db->toSql($file_properties_ecommerce["weight"], "float") . "
		                                                , `vat` = " . $db->toSql($file_properties_ecommerce["vat"], "Number") . "
		                                                , `vat_indetraible` = " . $db->toSql($file_properties_ecommerce["vat_indetraible"], "Number") . "
		                                                , `decumulation` = " . $db->toSql($file_properties_ecommerce["decumulation"]) . "
		                                            WHERE `ecommerce_order_detail`.`ID` = " . $db->toSql($ID_record, "Number");
		                                    $db->execute($sSQL);
		                                    if($db->affectedRows())
		                                        $count_rec_update++;
		                                        
		                                } else {
		                                    $sSQL = "INSERT INTO `ecommerce_order_detail` 
		                                            (
		                                                `ID`
		                                                , `ID_order`
		                                                , `ID_bill_detail`
		                                                , `tbl_src`
		                                                , `ID_items`
		                                                , `description`
		                                                , `date_since`
		                                                , `date_to`
		                                                , `type`
		                                                , `owner`
		                                                , `last_update`
		                                                , `qta`
		                                                , `stock_used`
		                                                , `reserve_stock_used`
		                                                , `price`
		                                                , `discount`
		                                                , `account`
		                                                , `weight`
		                                                , `vat`
		                                                , `vat_indetraible`
		                                                , `decumulation`
		                                            )
		                                            VALUES 
		                                            (
		                                                NULL
		                                                , " . $db->toSql($ID_order, "Number") . " 
		                                                , " . $db->toSql($ID_bill_detail, "Number") . " 
		                                                , " . $db->toSql("vgallery_nodes") . " 
		                                                , " . $db->toSql($ID_item, "Number") . " 
		                                                , " . $db->toSql($description . $strDate) . " 
		                                                , " . $db->toSql($date_since, "Number") . " 
		                                                , " . $db->toSql($date_to, "Number") . "
		                                                , " . $db->toSql($file_properties_ecommerce["type"]) . "
		                                                , " . $db->toSql("-1", "Number") . "
		                                                , " . $db->toSql($last_update) . "
		                                                , " . $db->toSql($qta, "Number") . "
		                                                , " . $db->toSql($stock_used, "Number") . "
		                                                , " . $db->toSql($reserve_stock_used, "Number") . "
		                                                , " . $db->toSql($real_price, "Number") . "
		                                                , " . $db->toSql($real_discount, "Number") . "
		                                                , " . $db->toSql($file_properties_ecommerce["account"], "Number") . "
		                                                , " . $db->toSql($file_properties_ecommerce["weight"], "float") . "
		                                                , " . $db->toSql($file_properties_ecommerce["vat"], "Number") . "
		                                                , " . $db->toSql($file_properties_ecommerce["vat_indetraible"], "Number") . "
		                                                , " . $db->toSql($file_properties_ecommerce["decumulation"]) . "
		                                            )";
		                                    $db->execute($sSQL);
		                                    $ID_record = $db->getInsertID(true);
		                                    
		                                    $count_rec_insert++;
		                                }

										if(check_function("ecommerce_get_total_by_order"))
											$total_order = ecommerce_get_total_by_order($ID_order);
										if(is_array($total_order) && count($total_order)) {
											if($ID_shipping > 0) {
												if($total_order["total_shipping_order"] > 0 || $total_order["total_shipping_weight"] > 0) {
												    if(check_function("ecommerce_get_shipping_price"))
											    		$shipping_price = ecommerce_get_shipping_price($ID_shipping, $total_order["total_shipping_weight"], resolve_zona_by_id($ID_anagraph, "anagraph", true), $total_order["total_shipping_order"]);

												    if(!($shipping_price > 0)) {
											            $shipping_price = 0;
												    }
												} else {
													$shipping_price = 0;
												}
											} else {
											    $shipping_price = 0;
											}
											
											if(check_function("ecommerce_recalc_documents_bill"))
												ecommerce_recalc_documents_bill($ID_bill);
										    /*$sSQL = "UPDATE ecommerce_documents_bill
										                SET
										                owner = " . $db->toSql("-1", "Number") . " 
										                , last_update = " . $db->toSql($last_update) . " 
										                , operation = 'sent'
										                , ID_type = " . $db->toSql($total_order["ID_type"], "Number") . "
										                , bill_id = " . $db->toSql($total_order["order_id"], "Number") . "
										                , date = " . $db->toSql($total_order["date"], "Date") . "
										                , object = " . $db->toSql($total_order["object"]) . "
										                , total_price = " . $db->toSql($total_order["total_price"], "Number") . "
										                , total_discount = " . $db->toSql($total_order["total_discount"], "Number") . "
										                , real_price = " . $db->toSql($total_order["real_price"], "Number") . "
										                , real_vat = " . $db->toSql($total_order["real_vat"], "Number") . "
										                , shipping_price = " . $db->toSql($shipping_price, "Number") . "
										                , total_bill = " . $db->toSql($total_order["total_order"], "Number") . "
										                , total_account = " . $db->toSql($total_order["total_account"], "Number") . "
										                WHERE ecommerce_documents_bill.ID = " . $db->toSql($ID_bill, "Number");
										    $db->execute($sSQL);*/
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
            $sSQL = "DELETE FROM ecommerce_documents_bill_detail WHERE ecommerce_documents_bill_detail.ID IN (SELECT ecommerce_order_detail.ID_bill_detail FROM ecommerce_order_detail WHERE ecommerce_order_detail.last_update = " . $db->toSql($del_order) . " AND ecommerce_order_detail.owner = '-1')";
            $db->execute($sSQL);

            $sSQL = "DELETE FROM ecommerce_order_detail WHERE ecommerce_order_detail.last_update = " . $db->toSql($del_order) . " AND ecommerce_order_detail.owner = '-1'";
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
                                'order_detail' AS ID
                                , 'order_detail' AS name";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";

    $oField->source_SQL = "SELECT
                                'order_detail_type' AS ID
                                , 'order_detail_type' AS name
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
                                ecommerce_order_detail.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM ecommerce_order_detail AS tbl WHERE tbl.last_update = ecommerce_order_detail.last_update), ') ',  FROM_UNIXTIME(ecommerce_order_detail.last_update)) AS value
                           FROM
                                ecommerce_order_detail
                           WHERE ecommerce_order_detail.owner = '-1'
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
