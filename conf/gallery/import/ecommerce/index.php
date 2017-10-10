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
    $del_item = $_REQUEST["del_item"];
    
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
    	$tmp_import_data = get_session("WGimportEC");

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
                        $sSQL = "	SELECT
		                                'stock' AS ID
		                                , 'stock' AS name
    								UNION
    								SELECT
		                                'reserve_stock' AS ID
		                                , 'reserve_stock' AS name
    								UNION
    								SELECT
		                                'weight' AS ID
		                                , 'weight' AS name
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
		                                'buy_price' AS ID
		                                , 'buy_price' AS name
									UNION
									SELECT
		                                'account' AS ID
		                                , 'account' AS name
									UNION
									SELECT
		                                'qta_min' AS ID
		                                , 'qta_min' AS name
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
    	set_session("WGimportEC", $_REQUEST);
    	
        if($enclosure && $destination_type && $field_type && $nrec) {
            if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan)) {
                $fp = fopen(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . $file_scan, "r");
                fgetcsv($fp, 0, $sep, $enclosure);
                
                $structure = $_REQUEST["data"];
                if(isset($structure["primary"]) && isset($structure["real_name"])) {
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
                                
                                $real_name = get_structure_data($data_csv, $structure["real_name"], "-", $db) . "-" . $primary_key;
                                
                                if(isset($structure["stock"])) {
                                	$stock = get_structure_data($data_csv, $structure["stock"], "-", $db);
									$stock = str_replace(",", ".", $stock);
								} else
                                	$stock = false;

                                if(isset($structure["reserve_stock"])) {
                                	$reserve_stock = get_structure_data($data_csv, $structure["reserve_stock"], "-", $db);
									$reserve_stock = str_replace(",", ".", $reserve_stock);
								} else
                                	$reserve_stock = false;

                                if(isset($structure["weight"])) {
                                	$weight = get_structure_data($data_csv, $structure["weight"], "-", $db);
									$weight = str_replace(",", ".", $weight);
								} else
                                	$weight = false;

                                if(isset($structure["price"])) {
                                	$basic_price = get_structure_data($data_csv, $structure["price"], "-", $db);
									$basic_price = str_replace(",", ".", $basic_price);
								} else
                                	$basic_price = false;

                                if(isset($structure["discount"])) {
                                	$basic_discount = get_structure_data($data_csv, $structure["discount"], "-", $db);
                                	$basic_discount = str_replace(",", ".", $basic_discount);
								} else
                                	$basic_discount = false;

                                if(isset($structure["account"])) {
                                	$account = get_structure_data($data_csv, $structure["account"], "-", $db);
                                	$account = str_replace(",", ".", $account);
								} else
                                	$account = false;

                                if(isset($structure["qta_min"])) {
                                	$qta_min = get_structure_data($data_csv, $structure["qta_min"], "-", $db);
                                	$qta_min = str_replace(",", ".", $qta_min);
								} else
                                	$qta_min = false;

                                if(isset($structure["vat"])) {
                                	$vat = get_structure_data($data_csv, $structure["vat"], "-", $db);
                                	$vat = str_replace(",", ".", $vat);
								} else
                                	$vat = false;

                                if(isset($structure["vat_indetraible"])) {
                                	$vat_indetraible = get_structure_data($data_csv, $structure["vat_indetraible"], "-", $db);
									$vat_indetraible = str_replace(",", ".", $vat_indetraible);
								} else
                                	$vat_indetraible = false;

                                if(isset($structure["buy_price"])) {
                                	$buy_price = get_structure_data($data_csv, $structure["buy_price"], "-", $db);
                                	$buy_price = str_replace(",", ".", $buy_price);
								} else
                                	$buy_price = false;

                                $sSQL = "SELECT * FROM vgallery_nodes WHERE name = " . $db->toSql(ffCommon_url_rewrite($real_name));
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $ID_node = $db->getField("ID", "Number")->getValue();

                                    if(check_function("ecommerce_get_file_properties_ecommerce"))
                                    	$file_properties = ecommerce_get_file_properties_ecommerce(stripslash($db->getField("parent")->getValue()) . "/" . $db->getField("name")->getValue(), "vgallery_nodes");

	                                $sSQL = "SELECT * FROM ecommerce_settings WHERE tbl_src = 'vgallery_nodes' AND ID_items = " . $db->toSql($ID_node, "Number");
	                                $db->query($sSQL);
	                                if($db->nextRecord()) {
	                                    $ID_record = $db->getField("ID", "Number")->getValue();

	                                    $sSQL = "UPDATE `ecommerce_settings` 
	                                            SET 
	                                                `tbl_src` = 'vgallery_nodes' 
	                                                , `ID_items` = " . $db->toSql($ID_node, "Number") . " 
	                                                , `type` = " . $db->toSql($file_properties["type"]) . " 
	                                                , `mode` = " . $db->toSql($file_properties["mode"]) . " 
	                                                , `usestock` = " . $db->toSql($file_properties["usestock"]) . " 
	                                                , `usereservestock` = " . $db->toSql($file_properties["usereservestock"]) . " 
	                                                , `useunic` = " . $db->toSql($file_properties["useunic"]) . "
	                                                , `useshipping` = " . $db->toSql($file_properties["useshipping"]) . "
	                                                , `usediscount` = " . $db->toSql($file_properties["usediscount"]) . "
	                                                , `useaccount` = " . $db->toSql($file_properties["useaccount"]) . "
	                                                , `useqta_min` = " . $db->toSql($file_properties["useqta_min"]) . "
	                                                , `usepricelist` = " . $db->toSql($file_properties["usepricelist"]) . "
	                                                , `usespecialsupport` = " . $db->toSql($file_properties["usespecialsupport"]) . "
	                                                , `stock` = IF(`stock` = 0, " . $db->toSql(($stock === false ? $file_properties["stock"] : $stock), "Number") . ", `stock`)
	                                                , `reserve_stock` = IF(`reserve_stock` = 0, " . $db->toSql(($reserve_stock === false ? $file_properties["reserve_stock"] : $reserve_stock), "Number") . ", `reserve_stock`)
	                                                , `weight` = IF(`weight` = 0, " . $db->toSql(($weight === false ? $file_properties["weight"] : $weight), "Number") . ", `weight`)
													, `buy_price` = IF(`buy_price` = 0, " . $db->toSql(($buy_price === false ? $file_properties["buy_price"] : $buy_price), "Number") . ", `buy_price`)
	                                                , `basic_price` = IF(`basic_price` = 0, " . $db->toSql(($basic_price === false ? $file_properties["price"] : $basic_price), "Number") . ", `basic_price`)
	                                                , `basic_discount` = IF(`basic_discount` = 0, " . $db->toSql(($basic_discount === false ? $file_properties["discount"] : $basic_discount), "Number") . ", `basic_discount`)
	                                                , `account` = IF(`account` = 0, " . $db->toSql(($account === false ? $file_properties["account"] : $account), "Number") . ", `account`)
	                                                , `qta_min` = IF(`qta_min` = 0, " . $db->toSql(($qta_min === false ? $file_properties["qta_min"] : $qta_min), "Number") . ", `qta_min`)
	                                                , `show_vat` = " . $db->toSql($file_properties["show_vat"]) . "
	                                                , `vat` = IF(`vat` = '', " . $db->toSql(($vat === false ? $file_properties["vat"] : $vat), "Number") . ", `vat`)
	                                                , `vat_indetraible` = IF(`vat_indetraible` = 0, " . $db->toSql(($vat_indetraible === false ? $file_properties["vat_indetraible"] : $vat_indetraible), "Number") . ", `vat_indetraible`)
	                                                , `decumulation` = " . $db->toSql($file_properties["decumulation"]) . "
	                                                , `last_update` = " . $db->toSql($last_update) . "
	                                            WHERE `ecommerce_settings`.`ID` = " . $db->toSql($ID_record, "Number");
	                                    $db->execute($sSQL);
	                                    if($db->affectedRows())
	                                        $count_rec_update++;
	                                        
	                                } else {
	                                    $sSQL = "INSERT INTO `ecommerce_settings` 
	                                            (
	                                                `ID`
	                                                , `tbl_src`
	                                                , `ID_items`
	                                                , `type`
	                                                , `mode`
	                                                , `usestock`
	                                                , `usereservestock`
	                                                , `useunic`
	                                                , `useshipping`
	                                                , `usediscount`
	                                                , `useaccount`
	                                                , `useqta_min`
	                                                , `usepricelist`
	                                                , `usespecialsupport`
	                                                , `stock`
	                                                , `reserve_stock`
	                                                , `weight`
	                                                , `buy_price`
	                                                , `basic_price`
	                                                , `basic_discount`
	                                                , `account`
	                                                , `qta_min`
	                                                , `show_vat`
	                                                , `vat`
	                                                , `vat_indetraible`
	                                                , `decumulation`
	                                                , `owner`
	                                                , `last_update`
	                                            )
	                                            VALUES 
	                                            (
	                                                NULL 
	                                                , 'vgallery_nodes' 
	                                                , " . $db->toSql($ID_node, "Number") . " 
	                                                , " . $db->toSql($file_properties["type"]) . " 
	                                                , " . $db->toSql($file_properties["mode"]) . " 
	                                                , " . $db->toSql($file_properties["usestock"]) . " 
	                                                , " . $db->toSql($file_properties["usereservestock"]) . " 
	                                                , " . $db->toSql($file_properties["useunic"]) . "
	                                                , " . $db->toSql($file_properties["useshipping"]) . "
	                                                , " . $db->toSql($file_properties["usediscount"]) . "
	                                                , " . $db->toSql($file_properties["useaccount"]) . "
	                                                , " . $db->toSql($file_properties["useqta_min"]) . "
	                                                , " . $db->toSql($file_properties["usepricelist"]) . "
	                                                , " . $db->toSql($file_properties["usespecialsupport"]) . "
	                                                , " . $db->toSql(($stock === false ? $file_properties["stock"] : $stock), "Number") . "
	                                                , " . $db->toSql(($reserve_stock === false ? $file_properties["reserve_stock"] : $reserve_stock), "Number") . "
	                                                , " . $db->toSql(($weight === false ? $file_properties["weight"] : $weight), "Number") . "
	                                                , " . $db->toSql(($buy_price === false ? $file_properties["buy_price"] : $buy_price), "Number") . "
	                                                , " . $db->toSql(($basic_price === false ? $file_properties["price"] : $basic_price), "Number") . "
	                                                , " . $db->toSql(($basic_discount === false ? $file_properties["discount"] : $basic_discount), "Number") . "
	                                                , " . $db->toSql(($account === false ? $file_properties["account"] : $account), "Number") . "
	                                                , " . $db->toSql(($qta_min === false ? $file_properties["qta_min"] : $qta_min), "Number") . "
	                                                , " . $db->toSql($file_properties["show_vat"]) . "
	                                                , " . $db->toSql(($vat === false ? $file_properties["vat"] : $vat), "Number") . "
	                                                , " . $db->toSql(($vat_indetraible === false ? $file_properties["vat_indetraible"] : $vat_indetraible), "Number") . "
	                                                , " . $db->toSql($file_properties["decumulation"]) . "
	                                                , " . $db->toSql("-1", "Number") . "
	                                                , " . $db->toSql($last_update) . "
	                                            )";
	                                    $db->execute($sSQL);
	                                    $ID_record = $db->getInsertID(true);
	                                    
	                                    $count_rec_insert++;
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
        if(strlen($del_item)) {
            $sSQL = "DELETE FROM ecommerce_settings WHERE ecommerce_settings.last_update = " . $db->toSql($del_item) . " AND ecommerce_settings.owner = '-1'";
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
                                vgallery.ID
                                , vgallery.name
                           FROM
                                vgallery
                           WHERE vgallery.status > 0";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";
    $oField->source_SQL = "
							SELECT
                                'ecommerce' AS ID
                                , 'ecommerce' AS name
                                ";
    $oField->value = new ffData($field_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $oField->multi_select_one = false;
    $tpl->set_var("field_type", $oField->process());
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "del_item";
    $oField->label = ffTemplate::_get_word_by_code("del_item");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT
                                ecommerce_settings.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM ecommerce_settings AS tbl WHERE tbl.last_update = ecommerce_settings.last_update), ') ', FROM_UNIXTIME(ecommerce_settings.last_update)) AS value
                           FROM
                                ecommerce_settings
                           WHERE ecommerce_settings.owner = '-1'
                          ";   
    $oField->actex_update_from_db = true;
    $oField->value = new ffData($del_item, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("del_item", $oField->process());

	$tpl->set_var("SezLangData", "");
	$tpl->set_var("SezCatData", "");
    
    $tpl->set_var("SezAnagraphDel", "");
    $tpl->parse("SezVgalleryDel", false);
    $tpl->set_var("SezOrderDel", "");

    $tpl->parse("SezFieldData", false);
    $tpl->parse("SezDelete", false);

    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    }
        
    $cm->oPage->addContent($tpl->rpparse("Main", false));
?>
