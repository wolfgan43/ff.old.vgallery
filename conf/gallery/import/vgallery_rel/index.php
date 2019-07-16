<?php
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

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
    $del_item = $_REQUEST["del_item"];
    
    $fixed_pre_cat = $_REQUEST["fprecat"];  
    $fixed_post_cat = $_REQUEST["fpostcat"];  

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
    	$tmp_import_data = get_session("WGimportVG");

		if(!strlen($limit_by))
			$limit_by = $tmp_import_data["limit_by"];
			
		if(!strlen($limit_field))
			$limit_field = $tmp_import_data["limit_field"];

        $tpl->set_var("limit_by", ffCommon_specialchars($limit_by));

        if(strlen($enclosure)) {
            if($destination_type && $field_type && $nrec && $ID_lang > 0) {
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
                        $sSQL = "SELECT vgallery_fields.* 
                                FROM vgallery_fields
                                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                                WHERE vgallery_type.ID = " . $db->toSql($field_type, "Number");
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                        	//limit field
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
    	set_session("WGimportVG", $_REQUEST);
    	
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
                                
                                $arrSmartUrl = array();
                                $arrMetaDescription = array();
                                $meta_description = null;
                                $smart_url = "";
                                
                                $sSQL = "SELECT * FROM vgallery WHERE vgallery.ID = " . $db->toSql($destination_type, "Number");
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $category = "/" . $db->getField("name")->getValue();
                                    $vgallery_name = $db->getField("name")->getValue();
                                }
                                if(strlen($fixed_pre_cat)) {
                                    $fixed_pre_cat = str_replace(", ", ",", trim($fixed_pre_cat));
                                    $category = stripslash($category) . "/" . str_replace(",", "/", $fixed_pre_cat);
                                }
                                
                                $category = stripslash($category) . "/" . get_structure_data($data_csv, $structure["category"], "/", $db);
                                
                                if(strlen($fixed_post_cat)) {
                                    $fixed_post_cat = str_replace(", ", ",", trim($fixed_post_cat));
                                    $category = stripslash($category) . "/" . str_replace(",", "/", $fixed_post_cat);
                                }
                                
                                $arrCategory = explode("/", $category);
                                if(is_array($arrCategory) && count($arrCategory)) {
                                    $parent_node = "/";
                                    foreach($arrCategory AS $category_value) {
                                        if(strlen(trim($category_value, " "))) {
                                            $sSQL = "SELECT * FROM vgallery_nodes WHERE parent = " . $db->toSql($parent_node) . " AND name = " . $db->toSql(ffCommon_url_rewrite($category_value));
                                            $db->query($sSQL);
                                            if($db->nextRecord()) {
                                                $ID_category = $db->getField("ID", "Number")->getValue();
                                            } else {
			                                    $sSQL = "SELECT * FROM vgallery_nodes WHERE vgallery_nodes.ID = " . $db->toSql($start_category_id + $count_cat_insert + 1, "Number");
			                                    $db->query($sSQL);
			                                    if($db->nextRecord()) {
			                                        $new_key = "NULL";
			                                    } else {
			                                        $new_key = $start_category_id + $count_cat_insert + 1;
			                                    }

                                                $sSQL = "INSERT INTO `vgallery_nodes` 
                                                        (
                                                            `ID`
                                                            , `ID_vgallery`
                                                            , `name`
                                                            , `order`
                                                            , `parent`
                                                            , `ID_type`
                                                            , `is_dir`
                                                            , `last_update`
                                                            , `owner`
                                                        )
                                                        VALUES 
                                                        (
                                                            $new_key 
                                                            , " . $db->toSql($destination_type, "number") . "
                                                            , " . $db->toSql(ffCommon_url_rewrite($category_value)) . "
                                                            , " . $db->toSql("0") . "
                                                            , " . $db->toSql($parent_node) . "
                                                            , (SELECT ID FROM vgallery_type WHERE name = 'Directory')
                                                            , '1'
                                                            , " . $db->toSql($last_update) . "
                                                            , " . $db->toSql("-1", "Number") . "
                                                        )";
                                                $db->execute($sSQL);
                                                $ID_category = $db->getInsertID(true);
                                                
                                                $count_cat_insert++;
                                            }
                                            
                                            $sSQL = "SELECT ID
                                                        FROM `vgallery_rel_nodes_fields`
                                                        WHERE `vgallery_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_category, "Number") . "
                                                            AND `vgallery_rel_nodes_fields`.`ID_fields` = (SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'title' AND vgallery_type.name = 'Directory')
                                                            AND `vgallery_rel_nodes_fields`.`ID_lang` = " . $db->toSql($ID_lang, "Number");
                                            $db->query($sSQL);
                                            if($db->nextRecord()) {
                                                $sSQL = "UPDATE `vgallery_rel_nodes_fields` 
                                                        SET 
                                                            `description` = " . $db->toSql($category_value) . " 
                                                        WHERE `vgallery_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_category, "Number") . "
                                                            AND `vgallery_rel_nodes_fields`.`ID_fields` = (SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'title' AND vgallery_type.name = 'Directory')
                                                            AND `vgallery_rel_nodes_fields`.`ID_lang` = " . $db->toSql($ID_lang, "Number");
                                                $db->execute($sSQL);
                                            } else {
                                                $sSQL = "INSERT INTO `vgallery_rel_nodes_fields` 
                                                        (
                                                            `ID`
                                                            , `description`
                                                            , `ID_nodes`
                                                            , `ID_fields`
                                                            , `ID_lang`
                                                        )
                                                        VALUES 
                                                        (
                                                            NULL 
                                                            , " . $db->toSql($category_value) . "
                                                            , " . $db->toSql($ID_category, "Number") . "
                                                            , (SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'title' AND vgallery_type.name = 'Directory')
                                                            , " . $db->toSql($ID_lang, "Number") . "
                                                        )";
                                                $db->execute($sSQL);
                                            }

                                            if(check_function("update_vgallery_seo"))
                                            	update_vgallery_seo($category_value, $ID_category, $ID_lang, "", $parent_node);
                                            
                                            $parent_node = stripslash($parent_node) . "/" . ffCommon_url_rewrite($category_value);
                                        }
                                    }
                                }

                                $sSQL = "SELECT * FROM vgallery_nodes WHERE parent = " . $db->toSql($parent_node) . " AND name = " . $db->toSql(ffCommon_url_rewrite($real_name));
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    $ID_record = $db->getField("ID", "Number")->getValue();

                                    $sSQL = "UPDATE `vgallery_nodes` 
                                            SET 
                                                `ID_vgallery` = " . $db->toSql($destination_type, "Number") . " 
                                                , `name` = " . $db->toSql(ffCommon_url_rewrite($real_name)) . " 
                                                , `order` = " . $db->toSql("0") . " 
                                                , `parent` = " . $db->toSql($parent_node) . " 
                                                , `ID_type` = " . $db->toSql($field_type, "Number") . " 
                                                , `last_update` = " . $db->toSql($last_update) . " 
                                                , `owner` = " . $db->toSql("-1", "Number") . " 
                                            WHERE `vgallery_nodes`.`ID` = " . $db->toSql($ID_record, "Number");
                                    $db->execute($sSQL);
                                    if($db->affectedRows())
                                        $count_rec_update++;
                                        
                                } else {
                                    $sSQL = "SELECT * FROM vgallery_nodes WHERE vgallery_nodes.ID = " . $db->toSql($primary_key, "Number");
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $new_key = "NULL";
                                    } else {
                                        $new_key = $primary_key;
                                    }

                                    $sSQL = "INSERT INTO `vgallery_nodes` 
                                            (
                                                `ID`
                                                , `ID_vgallery`
                                                , `name`
                                                , `order`
                                                , `parent`
                                                , `ID_type`
                                                , `last_update`
                                                , `owner`
                                            )
                                            VALUES 
                                            (
                                                $new_key 
                                                , " . $db->toSql($destination_type, "Number") . "
                                                , " . $db->toSql(ffCommon_url_rewrite($real_name)) . "
                                                , " . $db->toSql("0") . "
                                                , " . $db->toSql($parent_node) . "
                                                , " . $db->toSql($field_type, "Number") . "
                                                , " . $db->toSql($last_update) . "
                                                , " . $db->toSql("-1", "Number") . "
                                            )";
                                    $db->execute($sSQL);
                                    $ID_record = $db->getInsertID(true);
                                    
                                    $count_rec_insert++;
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
                                
                                //if(check_function("update_vgallery_models"))
                               // 	update_vgallery_models("insert", $destination_type, $ID_record, $vgallery_name, $parent_node, ffCommon_url_rewrite($real_name));

                                foreach($structure AS $structure_key => $structure_value) {
                                    if($structure_key > 0) {
                                        $sSQL = "SELECT vgallery_fields.* 
                                                        , extended_type.name AS extended_type
                                                FROM vgallery_fields 
                                                    INNER JOIN extended_type ON extended_type.ID = vgallery_fields.ID_extended_type
                                                WHERE vgallery_fields.ID = " . $db->toSql($structure_key, "Number");
                                        $db->query($sSQL);
                                        if($db->nextRecord()) {
                                            $enable_smart_url = $db->getField("enable_smart_url")->getValue();
                                            $enable_meta_description = $db->getField("meta_description")->getValue();
                                            $extended_type = $db->getField("extended_type")->getValue();
                                        }
                                        
                                        $record_value = get_structure_data($data_csv, $structure[$structure_key], " ", $db);
                                        
                                        $sSQL = "SELECT ID
                                                    FROM `vgallery_rel_nodes_fields`
                                                    WHERE `vgallery_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_record, "Number") . "
                                                        AND `vgallery_rel_nodes_fields`.`ID_fields` = " . $db->toSql($structure_key, "Number") . "
                                                        AND `vgallery_rel_nodes_fields`.`ID_lang` = " . $db->toSql($ID_lang, "Number");
                                        $db->query($sSQL);
                                        if($db->nextRecord()) {
                                            $sSQL = "UPDATE `vgallery_rel_nodes_fields` 
                                                    SET 
                                                        `description` = " . $db->toSql($record_value) . " 
                                                    WHERE `vgallery_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_record, "Number") . "
                                                        AND `vgallery_rel_nodes_fields`.`ID_fields` = " . $db->toSql($structure_key, "Number") . "
                                                        AND `vgallery_rel_nodes_fields`.`ID_lang` = " . $db->toSql($ID_lang, "Number");
                                            $db->execute($sSQL);
                                        } else {
                                            $sSQL = "INSERT INTO `vgallery_rel_nodes_fields` 
                                                    (
                                                        `ID`
                                                        , `description`
                                                        , `ID_nodes`
                                                        , `ID_fields`
                                                        , `ID_lang`
                                                    )
                                                    VALUES 
                                                    (
                                                        NULL 
                                                        , " . $db->toSql($record_value) . "
                                                        , " . $db->toSql($ID_record, "Number") . "
                                                        , " . $db->toSql($structure_key, "Number") . "
                                                        , " . $db->toSql($ID_lang, "Number") . "
                                                    )";
                                            $db->execute($sSQL);
                                        }
                                        
                                        if($enable_smart_url > 0 && strlen($record_value)) {
                                            if(strlen($arrSmartUrl[$enable_smart_url])) {
                                                $arrSmartUrl[$enable_smart_url] .= " ";
                                            }
                                            $arrSmartUrl[$enable_smart_url] .= $record_value;
                                        }
                                        
                                        if($enable_meta_description > 0 && strlen($record_value)) {
                                            if(strlen($arrMetaDescription[$enable_meta_description])) {
                                                $arrMetaDescription[$enable_meta_description] .= " ";
                                            }
                                            $arrMetaDescription[$enable_meta_description] .= $record_value;
                                        }  
                                    }
                                }
                                reset($structure);
                                
                               	if(count($arrSmartUrl)) {
                    				ksort($arrSmartUrl);
                    				$smart_url = implode(" ", $arrSmartUrl);
                                }
                               	if(count($arrMetaDescription)) {
                    				ksort($arrMetaDescription);
                    				$meta_description["new"] = implode(" ", $arrMetaDescription);
                    				$meta_description["ori"] = implode(" ", $arrMetaDescription); 
                                }  
                                if(check_function("update_vgallery_seo"))
                                	update_vgallery_seo($smart_url, $ID_record, $ID_lang, $meta_description, ffCommon_dirname($parzial_node_src_path));
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
        if(strlen($del_item) && check_function("delete_vgallery")) {
            $sSQL = "SELECT vgallery_nodes.*
                        , vgallery.name AS vgallery_name
                    FROM vgallery_nodes 
                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                    WHERE vgallery_nodes.last_update = " . $db->toSql($del_item) . " AND vgallery_nodes.owner = '-1'";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    delete_vgallery($db->getField("parent")->getValue(), $db->getField("name")->getValue(), $db->getField("vgallery_name")->getValue());
                } while($db->nextRecord());
            }
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
                                vgallery.ID
                                , vgallery.name
                           FROM
                                vgallery
                           WHERE vgallery.status > 0";
    $oField->properties["onchange"] = "document.frmMain.method = 'post'; document." . $cm->oPage->form_name . ".submit();";
    $oField->value = new ffData($destination_type, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("destination_type", $oField->process());
                        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_type";
    $oField->label = ffTemplate::_get_word_by_code("field_type");
    $oField->extended_type = "Selection";

    if($destination_type > 0) {
    	$sSQL = "SELECT vgallery.limit_type 
    			FROM vgallery 
    			WHERE vgallery.ID = " . $db->tosql($destination_type, "Number");
	    $db->query($sSQL);
	    if($db->nextRecord())
	        $limit_type = " AND vgallery_type.ID IN ( " . $db->getField("limit_type")->getValue() . " )";
	}
    $oField->source_SQL = "SELECT
                                vgallery_type.ID
                                , vgallery_type.name
                           FROM
                                vgallery_type
                           WHERE " . (OLD_VGALLERY
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                               ) . "
                               AND vgallery_type.name <> 'Directory' 
                               $limit_type";   
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
    $oField->id = "del_item";
    $oField->label = ffTemplate::_get_word_by_code("del_item");
    $oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT DISTINCT
                                vgallery_nodes.last_update
                                ,  CONCAT('(', (SELECT COUNT(*) FROM vgallery_nodes AS tbl WHERE tbl.last_update = vgallery_nodes.last_update), ') ', (SELECT name FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery), ' ', FROM_UNIXTIME(vgallery_nodes.last_update)) AS value
                           FROM
                                vgallery_nodes
                           WHERE vgallery_nodes.owner = '-1'
                          ";   
    $oField->actex_update_from_db = true;
    $oField->value = new ffData($del_item, "Text");
    $oField->parent_page = array(&$cm->oPage);
    $tpl->set_var("del_item", $oField->process());

	$tpl->parse("SezLangData", false);
	$tpl->parse("SezCatData", false);
    
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
