<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_HTML_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}    
$db = ffDB_Sql::factory();

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
		$enabled_lang[$code_lang] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH. "/" . $code_lang;
	} while ($db->nextRecord());
}

$static_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/*");
$is_valid = false;

if(is_array($static_file) && count($static_file)) {
	foreach($static_file AS $real_file) {
	    if(is_file($real_file)) {
	        $relative_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file);
	        
	        if(ffCommon_url_rewrite(basename($real_file)) == $_REQUEST["keys"]["nameID"]) {
				$draft_content[LANGUAGE_DEFAULT] = array (
					"path" => FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/"
					, "relative_path" => ffGetFilename($real_file)
					, "file_ext" => ffGetFilename($real_file, false)
					, "content" => file_get_contents($real_file)
					, "is_valid" => true
				);
            	$file_name = ffGetFilename($real_file);
            	$file_ext = ffGetFilename($real_file, false);
            	$content = file_get_contents($real_file);
            	$is_valid = true;
			}
	    }
	}
}

foreach($enabled_lang AS $code_lang => $path) 
{
	//echo $code_lang;
	//echo FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/*";
	${"static_file_" . $code_lang} = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/*");
	//print_r(${"static_file_" . $code_lang});
	${"is_valid_" . $code_lang} = false;

	if(is_array(${"static_file_" . $code_lang}) && count(${"static_file_" . $code_lang})) {
		foreach(${"static_file_" . $code_lang} AS ${"real_file_" . $code_lang}) {
			if(is_file(${"real_file_" . $code_lang})) {
				${"relative_path_" . $code_lang} = str_replace($path, "", $real_file);

				if(ffCommon_url_rewrite(basename(${"real_file_" . $code_lang})) == $_REQUEST["keys"]["nameID"]) {
					$draft_content[$code_lang] = array (
						"path" => FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/"
						, "relative_path" => ffGetFilename(${"real_file_" . $code_lang})
						, "file_ext" => ffGetFilename(${"real_file_" . $code_lang}, false)
						, "content" => file_get_contents(${"real_file_" . $code_lang})
						, "is_valid" => true
					);
					${"file_name_" . $code_lang} = ffGetFilename(${"real_file_" . $code_lang});
					${"file_ext_" . $code_lang} = ffGetFilename(${"real_file_" . $code_lang}, false);
					${"content_" . $code_lang} = file_get_contents(${"real_file_" . $code_lang});
					${"is_valid_" . $code_lang} = true;
					$is_valid = true;
					$file_name = ffGetFilename(${"real_file_" . $code_lang});
					$file_ext = ffGetFilename(${"real_file_" . $code_lang}, false);
				}
			}
		}
	}

}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "HtmlPreview";
$oRecord->src_table = "";
$oRecord->skip_action = true;
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["cancel"]["display"] = false;

foreach($enabled_lang AS $key => $value)
{
	$oRecord->addTab("html_" . $key);
	$oRecord->setTabTitle("html_" . $key, ffTemplate::_get_word_by_code("html_" . $key));

	$oRecord->addContent(null, true, "html_" . $key); 
	$oRecord->groups["html_" . $key] = array(
											 "title" => ffTemplate::_get_word_by_code("html_" . $key)
											 , "cols" => 1
											 , "tab" => "html_" . $key
										  );


	$oField = ffField::factory($cm->oPage);
	$oField->id = "content_" . $key;
	$oField->label = ffTemplate::_get_word_by_code("html_modify_content");
	$oField->display_label = false;
	$oField->extended_type = "Text";
	$oField->default_value = new ffData($draft_content[$key]["content"]);
	$oField->encode_entities = false;
	$oField->editarea_syntax = $draft_content[$key]["file_ext"];
	$oField->extended_type = "HTML";
	$oField->properties["readonly"] = "readonly";
	
	$oRecord->addContent($oField, "html_" . $key);
}

//$cm->oPage->fixed_pre_content = $content;

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "cancel";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);
$oRecord->addActionButton($oButton);

$cm->oPage->addContent($oRecord); 
?>
