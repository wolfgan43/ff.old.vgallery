<?php
if (!(AREA_THEME_SHOW_MODIFY || AREA_THEME_SHOW_ADDNEW || AREA_THEME_SHOW_DELETE)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}  

$actual_path = $cm->real_path_info;

$arrExclude = array(
				"default" => true
				, "dialog" => true
				, "gallery" => true
				, "library" => true
				, "restricted" => true
			);

$arrAllowed = array(
				"library" => FF_THEME_DIR . "/library"
				, THEME_INSET => FF_THEME_DIR . "/" . THEME_INSET
				, "sys" => FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH
			);
			
$arr_real_file = glob(FF_DISK_PATH . "/themes/*");
if(is_array($arr_real_file) && count($arr_real_file)) {
	$theme_menu = '<ul class="theme-menu">';
	foreach ($arr_real_file as $real_file) { 
		if(is_dir($real_file) && !$arrExclude[basename($real_file)]) {
			$theme_menu .= '<li class="' . basename($real_file) . '"><a href="' . $cm->oPage->site_path . $cm->oPage->page_path . "/" . basename($real_file) . '">' .  ffTemplate::_get_word_by_code("theme" . basename($real_file)) . '</a></li>';
			$arrAllowed[basename($real_file)] = "/themes/" . basename($real_file);
		}
	}

	$theme_menu .= '<li class="theme-system"><a href="' . $cm->oPage->site_path . $cm->oPage->page_path . "/" . THEME_INSET . '">' .  ffTemplate::_get_word_by_code("themesystem") . '</a></li>';
	$theme_menu .= '<li class="theme-library"><a href="' . $cm->oPage->site_path . $cm->oPage->page_path . "/" . "library" . '">' .  ffTemplate::_get_word_by_code("themelibrary") . '</a></li>';
	$theme_menu .= '<li class="theme-sys"><a href="' . $cm->oPage->site_path . $cm->oPage->page_path . "/" . "sys" . '">' .  ffTemplate::_get_word_by_code("uploadsys") . '</a></li>';	
	
	$theme_menu .= '</ul>';
	$cm->oPage->addContent($theme_menu);
}

if(strlen($actual_path) &&  isset($arrAllowed[basename($actual_path)]) && is_dir(FF_DISK_PATH . $arrAllowed[basename($actual_path)]) ) {
	$layout["prefix"] = preg_replace('/[^a-zA-Z0-9\-]/', '', $arrAllowed[basename($actual_path)]);
	$layout["ID"] = "";
	$layout["title"] = "Theme " . basename($actual_path);
	$layout["type"] = "GALLERY_MENU";
	$layout["location"] = "Content";
	$layout["width"] = $sections["Content"]["width"];
	if(check_function("get_layout_settings"))
		$layout["settings"] = get_layout_settings(NULL, "GALLERY_MENU");
	$layout["visible"] = NULL;
	
	setJsRequest("cluetipclick"); 
	setJsRequest($layout["settings"]["AREA_GALLERY_MENU_PLUGIN"]);

	if(check_function("system_set_js"))
        system_set_js($cm->oPage, "/", false, null, USE_ADMIN_AJAX);

    if(check_function("process_gallery_menu"))
    	$res = process_gallery_menu($arrAllowed[basename($actual_path)], $arrAllowed[basename($actual_path)], $layout, FF_DISK_PATH, true);

	if(strlen($res["content"]))
		$cm->oPage->addContent($res["content"]); 
}



if(isset($_REQUEST["ret_url"])) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "back";
    $oButton->action_type = "gotourl";
    $oButton->url = $_REQUEST["ret_url"];
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("back");
    $cm->oPage->addContent($oButton->process());
}
