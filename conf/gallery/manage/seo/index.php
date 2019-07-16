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
						WHERE ID_group = " . $db_gallery->toSql(Auth::GUEST_GROUP_ID, "Number") . "
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