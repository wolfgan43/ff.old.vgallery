<?php
	$db = ffDB_Sql::factory();

	$path = $cm->real_path_info;
	if(!strlen($path))
		$path = "/";

	if($path == "/") {
		$tpl_list_path = "/category/list.html";
		$webdir_detail = "";
		$webdir_list = "macrocat";
		$webdir_list_sql = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_1.*
								, CONCAT('" . $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . "', " . CM_TABLE_PREFIX . "mod_webdir_cat_1.slug) AS url
							FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_1
							WHERE 1
							ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_1.name";
	} else {
		$arrPath = explode("/", $path);
		$webdir_schema = array("macrocat" 	=> array("slug" => (array_key_exists("1", $arrPath) && strlen($arrPath[1]) ? $arrPath[1] : "")
													, "id" => ""
												)
	  						, "category" 	=> array("slug" => (array_key_exists("2", $arrPath) && strlen($arrPath[2]) ? $arrPath[2] : "")
													, "id" => ""
												)
	  						, "subcat" 		=> array("slug" => (array_key_exists("3", $arrPath) && strlen($arrPath[3]) ? $arrPath[3] : "")
													, "id" => ""
												)
	  						, "company" 	=> array("slug" => (array_key_exists("4", $arrPath) && strlen($arrPath[4]) ? $arrPath[4] : "")
													, "id" => ""
												)
	  					);


		if(strlen($webdir_schema["macrocat"]["slug"])) {	  
			$tpl_list_path = "/category/list.html";
			$tpl_detail_path = "/category/detail.html";
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_1.*
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_1
					WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_1.slug = " . $db->toSql($webdir_schema["macrocat"]["slug"]);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$webdir_detail = "macrocat";

				$webdir_schema["macrocat"]["id"] = $db->getField("ID", "Number", true);
				$webdir_schema["macrocat"]["name"] = $db->getField("name", "Text", true);
				$webdir_schema["macrocat"]["keywords"] = $db->getField("keywords", "Text", true);
				$webdir_schema["macrocat"]["description"] = $db->getField("description", "Text", true);
				$webdir_schema["macrocat"]["popup"] = $db->getField("popup", "Text", true);
				$webdir_schema["macrocat"]["title"] = $db->getField("title", "Text", true);
				$webdir_schema["macrocat"]["h1"] = $db->getField("h1", "Text", true);
				$webdir_schema["macrocat"]["h2"] = $db->getField("h2", "Text", true);
				$webdir_schema["macrocat"]["content1"] = $db->getField("content1", "Text", true);
				$webdir_schema["macrocat"]["content2"] = $db->getField("content2", "Text", true);
				$webdir_schema["macrocat"]["image"] = $db->getField("image", "Text", true);
				$webdir_schema["macrocat"]["favicon"] = $db->getField("favicon", "Text", true);
				$webdir_schema["macrocat"]["visible"] = $db->getField("visible", "Number", true);
				$webdir_schema["macrocat"]["back_url"] = $cm->router->named_rules["webdir_frontend"]->reverse;
				
				$webdir_list = "category";
				$webdir_list_sql = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_2.*
										, CONCAT('" . $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . "', " . $db->toSql($webdir_schema["macrocat"]["slug"]) . ", '/', " . CM_TABLE_PREFIX . "mod_webdir_cat_2.slug) AS url
									FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_2
									WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID_cat_1 = " . $db->toSql($webdir_schema["macrocat"]["id"]) . "
									ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_2.name";
			}
		} 

		if(strlen($webdir_schema["category"]["slug"])) {
			$tpl_list_path = "/category/list.html";
			$tpl_detail_path = "/category/detail.html";

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_2.*
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_2
					WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_2.slug = " . $db->toSql($webdir_schema["category"]["slug"]) . "
						AND " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID_cat_1 = " . $db->toSql($webdir_schema["macrocat"]["id"]);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$webdir_detail = "category";
				
				$webdir_schema["category"]["id"] = $db->getField("ID", "Number", true);
				$webdir_schema["category"]["name"] = $db->getField("name", "Text", true);
				$webdir_schema["category"]["keywords"] = $db->getField("keywords", "Text", true);
				$webdir_schema["category"]["description"] = $db->getField("description", "Text", true);
				$webdir_schema["category"]["popup"] = $db->getField("popup", "Text", true);
				$webdir_schema["category"]["title"] = $db->getField("title", "Text", true);
				$webdir_schema["category"]["h1"] = $db->getField("h1", "Text", true);
				$webdir_schema["category"]["h2"] = $db->getField("h2", "Text", true);
				$webdir_schema["category"]["content1"] = $db->getField("content1", "Text", true);
				$webdir_schema["category"]["content2"] = $db->getField("content2", "Text", true);
				$webdir_schema["category"]["image"] = $db->getField("image", "Text", true);
				$webdir_schema["category"]["favicon"] = $db->getField("favicon", "Text", true);
				$webdir_schema["category"]["visible"] = $db->getField("visible", "Number", true);
				$webdir_schema["category"]["back_url"] = $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . $webdir_schema["macrocat"]["slug"];
				
				$webdir_list = "subcat";
				$webdir_list_sql = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_3.*
										, CONCAT('" . $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . "', " . $db->toSql($webdir_schema["macrocat"]["slug"]) . ", '/', " . $db->toSql($webdir_schema["category"]["slug"]) .", '/', " . CM_TABLE_PREFIX . "mod_webdir_cat_3.slug) AS url
									FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_3
									WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID_cat_2 = " . $db->toSql($webdir_schema["category"]["id"]) . "
									ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_3.name";
			}
		} 

		if(strlen($webdir_schema["subcat"]["slug"])) {
			$tpl_list_path = "/company/list.html";
			$tpl_detail_path = "/category/detail.html";

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_3.*
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_3
					WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_3.slug = " . $db->toSql($webdir_schema["subcat"]["slug"]) . "
						AND " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID_cat_2 = " . $db->toSql($webdir_schema["category"]["id"]);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$webdir_detail = "subcat";
				
				$webdir_schema["subcat"]["id"] = $db->getField("ID", "Number", true);
				$webdir_schema["subcat"]["name"] = $db->getField("name", "Text", true);
				$webdir_schema["subcat"]["keywords"] = $db->getField("keywords", "Text", true);
				$webdir_schema["subcat"]["description"] = $db->getField("description", "Text", true);
				$webdir_schema["subcat"]["popup"] = $db->getField("popup", "Text", true);
				$webdir_schema["subcat"]["title"] = $db->getField("title", "Text", true);
				$webdir_schema["subcat"]["h1"] = $db->getField("h1", "Text", true);
				$webdir_schema["subcat"]["h2"] = $db->getField("h2", "Text", true);
				$webdir_schema["subcat"]["content1"] = $db->getField("content1", "Text", true);
				$webdir_schema["subcat"]["content2"] = $db->getField("content2", "Text", true);
				$webdir_schema["subcat"]["image"] = $db->getField("image", "Text", true);
				$webdir_schema["subcat"]["favicon"] = $db->getField("favicon", "Text", true);
				$webdir_schema["subcat"]["visible"] = $db->getField("visible", "Number", true);
				$webdir_schema["subcat"]["back_url"] = $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . $webdir_schema["macrocat"]["slug"] . "/" . $webdir_schema["category"]["slug"];

				$webdir_list = "company";
				$webdir_list_sql = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_company.*
										, CONCAT('" . $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . "', " . $db->toSql($webdir_schema["macrocat"]["slug"]) . ", '/', " . $db->toSql($webdir_schema["category"]["slug"]) .", '/', " . $db->toSql($webdir_schema["subcat"]["slug"]) . ", '/', " . CM_TABLE_PREFIX . "mod_webdir_company.slug) AS url
									FROM " . CM_TABLE_PREFIX . "mod_webdir_company
									WHERE 1
										AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_1 = " . $db->toSql($webdir_schema["macrocat"]["id"]) . "
										AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_2 = " . $db->toSql($webdir_schema["category"]["id"]) . "
										AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_3 = " . $db->toSql($webdir_schema["subcat"]["id"]) . "
									ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_company.name";
			}
		} 
		
		if(strlen($webdir_schema["company"]["slug"])) {
			$tpl_list_path = "";
			$tpl_detail_path = "/company/detail.html";

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_company.*
					FROM " . CM_TABLE_PREFIX . "mod_webdir_company
					WHERE " . CM_TABLE_PREFIX . "mod_webdir_company.visible > 0
						AND " . CM_TABLE_PREFIX . "mod_webdir_company.slug = " . $db->toSql($webdir_schema["company"]["slug"]) . "
						AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_1 = " . $db->toSql($webdir_schema["macrocat"]["id"]) . "
						AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_2 = " . $db->toSql($webdir_schema["category"]["id"]) . "
						AND " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_3 = " . $db->toSql($webdir_schema["subcat"]["id"]);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$webdir_detail = "company";
				
				$webdir_schema["company"]["id"] = $db->getField("ID", "Number", true);
				$webdir_schema["company"]["name"] = $db->getField("name", "Text", true);
				$webdir_schema["company"]["keywords"] = $db->getField("keywords", "Text", true);
				$webdir_schema["company"]["description"] = $db->getField("description", "Text", true);
				$webdir_schema["company"]["popup"] = $db->getField("popup", "Text", true);
				$webdir_schema["company"]["title"] = $db->getField("title", "Text", true);
				$webdir_schema["company"]["h1"] = $db->getField("h1", "Text", true);
				$webdir_schema["company"]["h2"] = $db->getField("h2", "Text", true);
				$webdir_schema["company"]["content1"] = $db->getField("content1", "Text", true);
				$webdir_schema["company"]["content2"] = $db->getField("content2", "Text", true);
				$webdir_schema["company"]["image"] = $db->getField("image", "Text", true);
				$webdir_schema["company"]["favicon"] = $db->getField("favicon", "Text", true);
				$webdir_schema["company"]["visible"] = $db->getField("visible", "Number", true);
				$webdir_schema["company"]["back_url"] = $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . $webdir_schema["macrocat"]["slug"] . "/" . $webdir_schema["category"]["slug"] . "/" . $webdir_schema["subcat"]["slug"];
				
				$webdir_list = "";
				$webdir_list_sql = "";
			}
		} 
	}
	if(strlen($webdir_detail)) {
		if(strlen($webdir_schema[$webdir_detail]["favicon"])) {
			$cm->oPage->tplAddCss("favicon"
				, array(
					"file" => basename($webdir_schema[$webdir_detail]["favicon"])
					, "path" => ffCommon_dirname($webdir_schema[$webdir_detail]["favicon"])
					, "css_rel" => "icon"
					, "css_type" => "image/" . pathinfo($webdir_schema[$webdir_detail]["favicon"], PATHINFO_EXTENSION)
			));
		}
		if(strlen($webdir_schema[$webdir_detail]["title"])) {
			$cm->oPage->title = $webdir_schema[$webdir_detail]["title"];
		}
		if(strlen($webdir_schema[$webdir_detail]["description"])) {
			$cm->oPage->tplAddMeta("description", $webdir_schema[$webdir_detail]["description"]);
		}
		if(strlen($webdir_schema[$webdir_detail]["keywords"])) {
			$cm->oPage->tplAddMeta("keywords", $webdir_schema[$webdir_detail]["keywords"]);
		}
        $filename = cm_cascadeFindTemplate("/contents" . $tpl_detail_path, "webdir");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . $tpl_detail_path, $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/webdir/contents" . $tpl_detail_path, $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents" . $tpl_detail_path, $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file(basename($filename), "main");

		$tpl->set_var("site_path", FF_SITE_PATH);
		$tpl->set_var("ret_url", urlencode($cm->oPage->getRequestUri()));

		if(strlen($webdir_schema[$webdir_detail]["back_url"])) {
			
			$tpl->set_var("back_url", $webdir_schema[$webdir_detail]["back_url"]);
			$tpl->set_var("back", ffTemplate::_get_word_by_code("webdir_back_to"));
			$tpl->parse("SezBack", false);
		} else {
			$tpl->set_var("SezBack", "");
		}
				
		
		if(strlen($webdir_schema[$webdir_detail]["h1"]) || strlen($webdir_schema[$webdir_detail]["name"])) {
			if(strlen($webdir_schema[$webdir_detail]["h1"])) {
				$tpl->set_var("h1", $webdir_schema[$webdir_detail]["h1"]);
			} else {
				$tpl->set_var("h1", $webdir_schema[$webdir_detail]["name"]);
			}
			$tpl->parse("SezH1", false);
		} else {
			$tpl->set_var("SezH1", "");
		}
		if(strlen($webdir_schema[$webdir_detail]["h2"])) {
			$tpl->set_var("h2", $webdir_schema[$webdir_detail]["h2"]);
			$tpl->parse("SezH2", false);
		} else {
			$tpl->set_var("SezH2", "");
		}
		if(strlen($webdir_schema[$webdir_detail]["image"])) {
			$tpl->set_var("image", FF_UPDIR . "/webdir/" . $webdir_detail . "/" . $webdir_schema[$webdir_detail]["image"]);
			$tpl->parse("SezImage", false);
		} else {
			$tpl->set_var("SezImage", "");
		}
		if(strlen($webdir_schema[$webdir_detail]["content1"])) {
			$tpl->set_var("content1", $webdir_schema[$webdir_detail]["content1"]);
			$tpl->parse("SezContent1", false);
		} else {
			$tpl->set_var("SezContent1", "");
		}
		if(strlen($webdir_schema[$webdir_detail]["content2"])) {
			$tpl->set_var("content2", $webdir_schema[$webdir_detail]["content2"]);
			$tpl->parse("SezContent2", false);
		} else {
			$tpl->set_var("SezContent2", "");
		}
		if(strlen($webdir_schema[$webdir_detail]["popup"])) {
			$tpl->set_var("popup", $webdir_schema[$webdir_detail]["popup"]);
			$tpl->parse("SezPopup", false);
		} else {
			$tpl->set_var("SezPopup", "");
		}

		
		$cm->oPage->addContent($tpl->rpparse("main", false));
	}
	
	if(strlen($webdir_list_sql) && strlen($tpl_list_path)) {
        $filename = cm_cascadeFindTemplate("/contents" . $tpl_list_path, "webdir");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . $tpl_list_path, $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/webdir/contents" . $tpl_list_path, $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents" . $tpl_list_path, $cm->oPage->theme);*/

		if(is_file($filename)) {
			$tpl = ffTemplate::factory(ffCommon_dirname($filename));
			$tpl->load_file(basename($filename), "main");

			$db->query($webdir_list_sql);
			if($db->nextRecord()) {
				$tpl->set_var("site_path", FF_SITE_PATH);
				$tpl->set_var("ret_url", urlencode($cm->oPage->getRequestUri()));
				do {
					$tpl->set_var("url", $db->getField("url", "Text", true));
					
					if(strlen($db->getField("h1", "Text", true)) || strlen($db->getField("name", "Text", true))) {
						if(strlen($db->getField("h1", "Text", true))) {
							$tpl->set_var("h1", $db->getField("h1", "Text", true));
						} else {
							$tpl->set_var("h1", $db->getField("name", "Text", true));
						}
						$tpl->parse("SezH1", false);
					} else {
						$tpl->set_var("SezH1", "");
					}
					if(strlen($db->getField("h2", "Text", true))) {
						$tpl->set_var("h2", $db->getField("h2", "Text", true));
						$tpl->parse("SezH2", false);
					} else {
						$tpl->set_var("SezH2", "");
					}
					if(strlen($db->getField("image", "Text", true))) {
						$tpl->set_var("image", FF_UPDIR . "/webdir/" . $webdir_list . "/" . $db->getField("image", "Text", true));
						$tpl->parse("SezImage", false);
					} else {
						$tpl->set_var("SezImage", "");
					}
					if(strlen($db->getField("content1", "Text", true))) {
						$tpl->set_var("content1", $db->getField("content1", "Text", true));
						$tpl->parse("SezContent1", false);
					} else {
						$tpl->set_var("SezContent1", "");
					}
					if(strlen($db->getField("content2", "Text", true))) {
						$tpl->set_var("content2", $db->getField("content2", "Text", true));
						$tpl->parse("SezContent2", false);
					} else {
						$tpl->set_var("SezContent2", "");
					}
					if(strlen($db->getField("popup", "Text", true))) {
						$tpl->set_var("popup", $db->getField("popup", "Text", true));
						$tpl->parse("SezPopup", false);
					} else {
						$tpl->set_var("SezPopup", "");
					}
					$tpl->parse("SezListItem", true);
				} while($db->nextRecord());
				
				$tpl->parse("SezList", false);
				$tpl->set_var("SezError", "");
			} else {
				$tpl->set_var("strError", ffTemplate::_get_word_by_code("webdir_element_not_found"));
				$tpl->parse("SezError", false);
			}
			$cm->oPage->addContent($tpl->rpparse("main", false));
		}
	}
?>
