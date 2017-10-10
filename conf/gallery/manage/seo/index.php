<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

/*
$start_path = substr($cm->real_path_info, 0, strpos($cm->real_path_info, "/params"));
$params = str_replace($start_path . "/params", "", $cm->real_path_info);

if(!strlen($start_path))
    $start_path = "/";

 if($start_path != "/")
    $user_path = str_replace($start_path, "", $cm->path_info);
 else
    $user_path = $cm->path_info;

 switch($params) {
  	case ".json":
  		$arrSiteMap = cached_site_map($start_path);
		echo ffCommon_jsonenc($arrSiteMap, true);  		

		exit;
 	case ".xml":

  		$arrSiteMap = cached_site_map($start_path);

		$template_name = "sitemap" . $params;
		$template_path = get_template_cascading($user_path, $template_name);
		if(is_file($template_path . "/" . $template_name)) { 
		    $tpl = ffTemplate::factory($template_path);
		    $tpl->load_file($template_name, "main");

		    $tpl->set_var("domain_inset", DOMAIN_INSET);
		    $tpl->set_var("site_path", FF_SITE_PATH);
		    $tpl->set_var("theme_inset", THEME_INSET);
			
			foreach($arrSiteMap AS $path => $params) {
				$tpl->set_var("url", $path);
				$tpl->set_var("last_update", $params["last_update"]);
				$tpl->set_var("frequency", $params["frequency"]);
				$tpl->set_var("priority", $params["priority"]);

				$tpl->parse("SezSiteUrl", true);
			}
			
			http_response_code(200);
			echo $tpl->rpparse("main", false);
		}
		
		exit;
 	case ".html":
	default:
 }*/



$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "CachePage";
$oGrid->source_SQL = "SELECT *
						, IF(lang = " . $db_gallery->toSql(strtolower(LANGUAGE_DEFAULT)) . "
							, 0
							, lang
						) AS languages
						FROM cache_page 
						WHERE ID_group = " . $db_gallery->toSql(MOD_SEC_GUEST_USER_ID, "Number") . "
						[AND] [WHERE] [HAVING] [ORDER], languages";
$oGrid->order_default = "user_path";
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->display_new = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "user_path";
$oField->label = ffTemplate::_get_word_by_code("cache_page_user_path");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_group";
$oField->label = ffTemplate::_get_word_by_code("cache_page_ID_group");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "lang";
$oField->label = ffTemplate::_get_word_by_code("cache_page_languages");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "get";
$oField->label = ffTemplate::_get_word_by_code("cache_page_get");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "post";
$oField->label = ffTemplate::_get_word_by_code("cache_page_post");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->label = ffTemplate::_get_word_by_code("cache_page_last_update");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "frequency";
$oField->label = ffTemplate::_get_word_by_code("cache_page_frequency");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "XHR";
$oField->label = ffTemplate::_get_word_by_code("cache_page_XHR");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
						array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
						array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
					);
$oField->multi_select_one = false;
$oField->base_type = "Number";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_domain";
$oField->label = ffTemplate::_get_word_by_code("cache_page_ID_domain");
$oField->base_type = "Number";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "page_speed";
$oField->label = ffTemplate::_get_word_by_code("cache_page_page_speed");
$oField->base_type = "Number";
$oGrid->addContent($oField); 

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("cache_page"))); 

$oGrid_seo = ffGrid::factory($cm->oPage);
$oGrid_seo->full_ajax = true;
$oGrid_seo->ajax_addnew = true;
$oGrid_seo->ajax_delete = true;
$oGrid_seo->ajax_search = true;
//$oGrid_seo->title = ffTemplate::_get_word_by_code("form_config_fields");
$oGrid_seo->id = "CachePageSeo"; 
$oGrid_seo->source_SQL = "SELECT cache_page_seo.*  
						FROM cache_page_seo
						WHERE 1
						[AND] [WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid_seo->order_default = "ID";
$oGrid_seo->record_url = $cm->oPage->site_path . $user_path . "/seo/modify";
$oGrid_seo->record_id = "CachePageSeoModify";
$oGrid_seo->resources[] = $oGrid_seo->record_id;
$oGrid_seo->buttons_options["export"]["display"] = false;
$oGrid_seo->order_default = "name";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid_seo->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_name");
$oField->base_type = "Text";
$oGrid_seo->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "importance";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_importance");
$oField->base_type = "Number";
$oGrid_seo->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "resolution";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_resolution");
$oField->base_type = "Number";
$oGrid_seo->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "external";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_external");
$oField->base_type = "Text";
$oGrid_seo->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "group";
$oField->label = ffTemplate::_get_word_by_code("cache_page_seo_group");
$oField->base_type = "Text";
$oGrid_seo->addContent($oField); 

$cm->oPage->addContent($oGrid_seo, "rel", null, array("title" => ffTemplate::_get_word_by_code("cache_page_seo"))); 

/*
function cached_site_map($user_path, $user = null, $language = null) {
    $globals = ffGlobals::getInstance("gallery");

    $db = ffDB_Sql::factory();  
    $arrItem = array();
    $arrFrequency = array("always" => 10
                            , "hourly" => 9
                            , "daily" => 8
                            , "weekly" => 7
                            , "monthly" => 6
                            , "yearly" => 5
                            , "never" => 4
                        );
	if($user === null)
		$user = MOD_SEC_GUEST_USER_NAME;


    $sSQL = "SELECT (" . (strlen($globals->strip_user_path)
	                    ? "IF(user_path = " . $db->toSql($globals->strip_user_path) . "
	                            , '/'
	                            , SUBSTRING(user_path, " . strlen($globals->strip_user_path) . " + 1)
	                        )"
	                    : " user_path "
	                ) . ") AS user_path
                    , frequency
                    , last_update
                    , section_blocks
                    , layout_blocks
                    , data_blocks
                    , ff_blocks
            FROM cache_page 
            WHERE cache_page.ID_domain = " . $db->toSql($globals->ID_domain, "Number") 
                . (strlen($globals->strip_user_path)
                    ? " AND user_path LIKE '" . $db->toSql($globals->strip_user_path, "Text", false) . "%'"
                    : ""
                )
                . ($language === null
                    ? ""
                    : " AND (lang = " . $db->toSql($language) . " OR (lang = '' AND force_visualization > 0))"
                ) . "
                AND (force_visualization > 0 OR use_in_sitemap > 0)
            ORDER BY (LENGTH(user_path) - LENGTH(REPLACE(user_path, '/', ''))) ASC, user_path";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$path = $db->getField("user_path", "Text", true);
			if(strlen($path)) {
				$check_data_path = md5($db->getField("section_blocks", "Text", true)
												. $db->getField("layout_blocks", "Text", true) 
												. $db->getField("data_blocks", "Text", true) 
												. $db->getField("ff_blocks", "Text", true) 
											);

				if(is_array($arrItem[$path]) && array_key_exists($check_data_path, $arrItem[$path])) {
					continue;
				} 
					
				$arrItem[$path] = array();
				//$arrItem[$path][$check_data_path] = true;
				$arrItem[$path]["cached"] =  false;
				if($path == "/")
					$arrItem[$path]["priority"] = "1.00";
				else
					$arrItem[$path]["priority"] = round((100 * (pow(0.8, (count(explode("/", $path)	) - 1)))) / 100, 2);

				if(strlen($arrItem[$path]["priority"]) < 4)
					$arrItem[$path]["priority"] = $arrItem[$path]["priority"] . str_repeat("0", 4 - strlen($arrItem[$path]["priority"]));
				$arrItem[$path]["frequency"] = $db->getField("frequency", "Text", true);
				$arrItem[$path]["last_update"] = date("Y-m-d", $db->getField("last_update", "Text", true));
				$arrItem[$path]["page_speed"] = $db->getField("page_speed", "Number", true);
			}
		} while($db->nextRecord());
	}
	return $arrItem;
}*/