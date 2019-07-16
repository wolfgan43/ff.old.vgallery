<?php
$db = ffDB_Sql::factory();
//ini_set('memory_limit', '256M'); 

check_function("normalize_url");
check_function("get_table_support");

$tbl_supp = get_table_support();			

$tpl_name = "products.xml";

$tpl = ffTemplate::factory(__DIR__);
$tpl->load_file($tpl_name, "main");

$tpl->set_var("domain_inset", DOMAIN_INSET);
$tpl->set_var("site_path", FF_SITE_PATH);
$tpl->set_var("theme_inset", THEME_INSET);

$sSQL = "SELECT settings_thumb.* FROM settings_thumb WHERE 1";
$db->query($sSQL);
if($db->nextRecord()) {
	do {
		if($db->getField("thumb_image", "Number", true))
			$arrFieldCover[$db->getField("thumb_image", "Number", true)] = $db->getField("thumb_image", "Number", true);
		if($db->getField("preview_image", "Number", true))
			$arrFieldCover[$db->getField("preview_image", "Number", true)] = $db->getField("preview_image", "Number", true);
	} while($db->nextRecord());
}


if(is_array($tbl_supp["vgallery"]["smart_url"]) && count($tbl_supp["vgallery"]["smart_url"])) {
	foreach($tbl_supp["vgallery"]["smart_url"] AS $vgallery_name => $vgallery) {
	
		if($vgallery["enable_ecommerce"]) {
			$arrLimitType = explode(",", $vgallery["limit_type"]);
			foreach($arrLimitType AS $ID_type) {
				$arrType[$ID_type] = $ID_type;
			}
		}
	}
}
if(is_array($arrType) && count($arrType)) {
	$sSQL = "SELECT vgallery_fields.* 
			FROM vgallery_fields 
			WHERE vgallery_fields.ID_type IN(" . $db->toSql(implode(",", $arrType), "Number", false) . ")";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			if($db->getField("ID_data_type", "Number", true) == $tbl_supp["data_type"]["smart_url"]["media"]["ID"]) {
				$arrFieldMedia[$db->getField("ID", "Number", true)] = $db->getField("ID", "Number", true);
			}
			if($db->getField("ID_data_type", "Number", true) == $tbl_supp["data_type"]["smart_url"]["data"]["ID"] && array_search($db->getField("ID_extended_type", "Number", true), array_keys($tbl_supp["extended_type"]["group"]["upload"])) !== false) {
				$arrFieldCover[$db->getField("ID", "Number", true)] = $db->getField("ID", "Number", true);
			}
		} while($db->nextRecord());
	}
}

$limit["cats"] = array();
$limit["count"] = 0;

if(is_array($limit["cats"]) && count($limit["cats"])) {
	foreach($limit["cats"] AS $cat) {
		$query[] = "vgallery_nodes.parent LIKE '%/" . $db->toSql($cat, "Text", false) . "%'";
	}
}

$sSQL = "SELECT vgallery_nodes.*
			, ecommerce_settings.basic_price_gross
			, ecommerce_settings.basic_price_discounted_gross
			, ecommerce_settings.weight
			, ecommerce_settings.actual_qta AS stock
			, " . (is_array($arrFieldMedia) && count($arrFieldMedia)
				? "(SELECT vgallery_rel_nodes_fields.description 
					FROM vgallery_rel_nodes_fields
					WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
						AND vgallery_rel_nodes_fields.ID_fields = " . $db->toSql(implode(",", $arrFieldMedia), "Number", false) . "
						AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
					LIMIT 1
				)"
				: "''"
			) . " AS media	
			, " . (is_array($arrFieldCover) && count($arrFieldCover)
				? "	(SELECT vgallery_rel_nodes_fields.description 
						FROM vgallery_rel_nodes_fields
						WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
							AND vgallery_rel_nodes_fields.ID_fields IN(" . $db->toSql(implode(",", $arrFieldCover), "Number", false) . ")
							AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
						LIMIT 1
					)"
				: "''"
			) . " AS cover		
		FROM vgallery_nodes
			INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
			INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID AND ecommerce_settings.tbl_src = 'vgallery_nodes'
		WHERE vgallery.enable_ecommerce > 0
        	AND vgallery_nodes.visible > 0
        	" . ($query
        		? " AND (" . implode(" OR ", $query) . ")"
        		: ""
        	) . "
		ORDER BY vgallery_nodes.last_update DESC
			" . ($limit["count"]
				? " LIMIT " . $limit["count"]
				: ""
			);
$db->query($sSQL);
if($db->nextRecord()) {
	do {
		$meta_title 							= $db->getField("meta_title_alt", "Text", true);
		if(!$meta_title)
			$meta_title 						= $db->getField("meta_title", "Text", true);
		
		$ID_node 								= $db->getField("ID", "Number", true);
		$meta_description 						= $db->getField("meta_description", "Text", true);
		$permalink 								= $db->getField("permalink", "Text", true);
		$price_original 						= $db->getField("basic_price_gross", "Number", true);
		$price_discouted						= $db->getField("basic_price_discounted_gross", "Number", true);
		$stock 									= $db->getField("stock", "Number", true);
		$isbn 									= $db->getField("isbn", "Text", true);
		$weight 								= $db->getField("weight", "Number", true);
		//$keywords								= $db->getField("keywords", "Text", true);
		$keywords 								= "Home" . str_replace(array("/", "-"), array(",", " "), dirname($permalink));
		$cover 									= $db->getField("cover", "Text", true);
		$gallery 								= ($db->getField("media", "Text", true)
													? explode(",", $db->getField("media", "Text", true))
													: array()
												);

		if($stock <= 0)
			continue;
		
		/*
		$gallery = array();
		$cover = "";
		$full_path = stripslash($db->getField("parent", "Text", true)) . "/" . $db->getField("name", "Text", true);
		if(is_dir(FF_DISK_UPDIR . $full_path)) { 
			$directory = new RecursiveDirectoryIterator(FF_DISK_UPDIR . $full_path);
			$flattened = new RecursiveIteratorIterator($directory);

			$files = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:jpg|png|gif|svg)$#Di');
			$files = array_keys(iterator_to_array($files));
			sort($files);

			foreach($files as $file) {
				$file_path = str_replace(FF_DISK_UPDIR, "", $file);

				if(ffCommon_dirname($file) == FF_DISK_UPDIR . $full_path) {
					if(!$cover)
						$cover = $file_path;
				} else {
					$gallery[] = $file_path;
				}
			}		 
		}			*/
		

		$tpl->set_var("meta_title"				, htmlspecialchars($meta_title, ENT_XML1));
		$tpl->set_var("product"					, "");
		$tpl->set_var("meta_description"		, htmlspecialchars($meta_description, ENT_XML1));
		$tpl->set_var("price_original"			, $price_original);
		$tpl->set_var("price_discounted"		, $price_discouted);
		$tpl->set_var("id"						, $ID_node);
		$tpl->set_var("permalink"				, normalize_url_by_current_lang($permalink, true, true));
		$tpl->set_var("stock"					, $stock);
		$tpl->set_var("categories"				, htmlspecialchars($keywords, ENT_XML1));
		$tpl->set_var("shipping_price"			, "0");
		$tpl->set_var("isbn"					, $isbn);
		$tpl->set_var("weight"					, $weight);

		if(!$cover && count($gallery)) {
			$cover = $gallery[0];
			unset($gallery[0]); 
			$gallery = array_values($gallery); 
		}
		
		if($cover) {
			$tpl->set_var("cover"					, cm_showfiles_get_abs_url($cover));
			$tpl->parse("SezImage", false);
		}

			
		
		foreach($gallery AS $media_count => $media) {		
			$media_url = str_replace(FF_DISK_PATH, "", $media);

			$tpl->set_var("media_url", cm_showfiles_get_abs_url($media_url));
			$tpl->set_var("media_count", $media_count + 2);
			$tpl->parse("SezMedia", true);
		}
		
		$tpl->parse("SezItem", true);
		$tpl->set_var("SezImage", "");
		$tpl->set_var("SezMedia", "");
	} while($db->nextRecord());
}



$buffer = ffCommon_utf8_for_xml($tpl->rpparse("main", false));
if($buffer) {
	$mime = ffMedia::getMimeTypeByExtension("xml");
	if($mime)
		header("Content-type: " . $mime);

	echo $buffer;
} else {
	if($cm->isXHR())
		http_response_code(500);
	else
		http_response_code(404);
}
exit;
