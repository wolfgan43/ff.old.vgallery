<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(Auth::env("AREA_DRAFT_SHOW_MODIFY") || Auth::env("AREA_DRAFT_SHOW_ADDNEW") || Auth::env("AREA_DRAFT_SHOW_DELETE"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();
$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "draft";
$oGrid->title = ffTemplate::_get_word_by_code("drafts_title");
$oGrid->source_SQL = "SELECT
                            drafts.*
                        FROM
                            drafts
                        WHERE att = '' 
                        	" . ($globals->ID_domain > 0
                        		? " AND drafts.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
                        		: ""
                        	) . "
                        	[AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?ret_url=". urlencode($cm->oPage->getRequestUri());
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/[name_VALUE]";
$oGrid->record_id = "DraftModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = Auth::env("AREA_DRAFT_SHOW_ADDNEW");
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = Auth::env("AREA_DRAFT_SHOW_MODIFY");
$oGrid->display_delete_bt = Auth::env("AREA_DRAFT_SHOW_DELETE");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("drafts_name");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview"; 
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview?[KEYS][GLOBALS]ret_url=" . urlencode($cm->oPage->getRequestUri());  
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("preview");
//$oButton->image = "preview.png";
$oButton->template_file = "ffButton_link_image.html";                           
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("drafts"))); 


if ((Auth::env("AREA_HTML_SHOW_MODIFY") || Auth::env("AREA_HTML_SHOW_ADDNEW") || Auth::env("AREA_HTML_SHOW_DELETE"))) {
    $sSQL_file = "";
    $file_list = array();

	$sSQL = "SELECT " . FF_PREFIX . "languages.ID AS ID_languages
					, " . FF_PREFIX . "languages.description AS language 
					, " . FF_PREFIX . "languages.code AS code_lang 
				FROM " . FF_PREFIX . "languages
				WHERE
				" . FF_PREFIX . "languages.status > 0";
	$db->query($sSQL);
	if($db->nextRecord())
	{
		do {
			$code_lang = $db->getField("code_lang", "Text", true);
			$enabled_lang[$code_lang] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH. "/template/" . $code_lang;
		} while ($db->nextRecord());
	}
	foreach($enabled_lang AS $code_lang => $path)
	{
		${"static_file_" . $code_lang} = glob($path . "/*");
		if(is_array(${"static_file_" . $code_lang}) && count(${"static_file_" . $code_lang})) 
		{
			foreach(${"static_file_" . $code_lang} AS ${"real_file_" . $code_lang})
			{
				 if(is_file(${"real_file_" . $code_lang})) 
				 {
					 ${"relative_path_" . $code_lang} = str_replace($path, "", ${"real_file_" . $code_lang});
					 if(!array_key_exists(${"relative_path_" . $code_lang}, $file_list))
						$file_list[${"relative_path_" . $code_lang}] = ${"real_file_" . $code_lang};
				 }
			}
		}
	}

	$static_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/*");
	//print_r($static_file);
	if(is_array($static_file) && count($static_file)) {
		foreach($static_file AS $real_file) {
			if(is_file($real_file)) {
				$relative_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file);
				if(!array_key_exists($relative_path, $file_list))
					$file_list[$relative_path] = $real_file;
			}
		}
	}

	foreach($file_list AS $name => $path)
	{
		if(strlen($sSQL_file))
			$sSQL_file .= " UNION ";

		$sSQL_file .= " (
							SELECT 
								" . $db_gallery->toSql(ffCommon_url_rewrite(basename($path)), "Text") . " AS nameID
								, " . $db_gallery->toSql(basename($path), "Text") . " AS name
						)";
	}
	
    if(strlen($sSQL_file))
        $sSQL_file = "SELECT * FROM ( " . $sSQL_file  . " ) AS tbl_src [WHERE] [HAVING] [ORDER]";
    
	if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "html";
		$oGrid->title = ffTemplate::_get_word_by_code("html_title");
		$oGrid->source_SQL = $sSQL_file;
		
		$oGrid->order_default = "name";
		$oGrid->use_search = true;
		$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/modify?ret_url=". urlencode($cm->oPage->getRequestUri());
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/modify";
		$oGrid->record_id = "HtmlModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_new = Auth::env("AREA_HTML_SHOW_ADDNEW");
		$oGrid->display_edit_bt = false;
		$oGrid->display_edit_url = Auth::env("AREA_HTML_SHOW_MODIFY");
		$oGrid->display_delete_bt = Auth::env("AREA_HTML_SHOW_DELETE");

		// Campi chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "nameID";
		$oGrid->addKeyField($oField);

		// Campi di ricerca

		// Campi visualizzati
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->label = ffTemplate::_get_word_by_code("html_name");
		$oGrid->addContent($oField);


		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "preview"; 
		$oButton->action_type = "gotourl";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/preview?[KEYS][GLOBALS]ret_url=" . urlencode($cm->oPage->getRequestUri());  
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("preview");
		//$oButton->image = "preview.png";
		$oButton->template_file = "ffButton_link_image.html";                           
		$oGrid->addGridButton($oButton);
		
		$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("file")));
		/*
		if(is_array($enabled_lang) && count($enabled_lang))
		{
			foreach($enabled_lang AS $lang => $value)
			{
				if(strlen(${"sSQL_file_" . $lang}))
				{
					$oGrid = ffGrid::factory($cm->oPage);
					$oGrid->full_ajax = true;
					$oGrid->id = "html_" . $lang;
					$oGrid->title = ffTemplate::_get_word_by_code("html_" . $lang . "_title");
					if(strlen)
					$oGrid->source_SQL = ${"sSQL_file_" . $lang};

					$oGrid->order_default = "name";
					$oGrid->use_search = true;
					$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/modify?ret_url=". urlencode($cm->oPage->getRequestUri());
					$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/modify";
					$oGrid->record_id = "HtmlModify_" . $lang;
					$oGrid->resources[] = $oGrid->record_id;
					$oGrid->display_new = Auth::env("AREA_HTML_SHOW_ADDNEW");
					$oGrid->display_edit_bt = false;
					$oGrid->display_edit_url = Auth::env("AREA_HTML_SHOW_MODIFY");
					$oGrid->display_delete_bt = Auth::env("AREA_HTML_SHOW_DELETE");

					// Campi chiave
					$oField = ffField::factory($cm->oPage);
					$oField->id = "nameID";
					$oGrid->addKeyField($oField);

					// Campi di ricerca

					// Campi visualizzati
					$oField = ffField::factory($cm->oPage);
					$oField->id = "name";
					$oField->label = ffTemplate::_get_word_by_code("html_name");
					$oGrid->addContent($oField);


					$oButton = ffButton::factory($cm->oPage);
					$oButton->id = "preview"; 
					$oButton->action_type = "gotourl";
					$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/html/preview?[KEYS][GLOBALS]ret_url=" . urlencode($cm->oPage->getRequestUri());  
					$oButton->aspect = "link";
					$oButton->label = ffTemplate::_get_word_by_code("preview");
					//$oButton->image = "preview.png";
					$oButton->template_file = "ffButton_link_image.html";                           
					$oGrid->addGridButton($oButton);
					
					$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("file_" . $lang)));
				}
			}
		}
		*/

		 
	} else {
		$cm->oPage->addContent(ffTemplate::_get_word_by_code("ftp_not_configutated"), "rel", null, array("title" => ffTemplate::_get_word_by_code("file"))); 
	}
}

?>