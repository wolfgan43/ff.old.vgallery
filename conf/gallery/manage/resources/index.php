<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_GALLERY_SHOW_MODIFY || AREA_GALLERY_SHOW_ADDNEW || AREA_GALLERY_SHOW_DELETE || AREA_GALLERY_SHOW_RELATIONSHIP || AREA_GALLERY_SHOW_PERMISSION || AREA_GALLERY_SHOW_SEO || AREA_ECOMMERCE_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}  

$layout["prefix"] = "resources";
$layout["ID"] = 0;
$layout["title"] = "Resources";
$layout["type"] = "GALLERY_MENU";
$layout["location"] = "Content";
$layout["width"] = $sections["Content"]["width"];
if(check_function("get_layout_settings"))
	$layout["settings"] = get_layout_settings(NULL, "GALLERY_MENU");
$layout["visible"] = NULL;

//if(check_function("check_fs"))
//  check_fs(DISK_UPDIR, "/");

setJsRequest("cluetipclick");
setJsRequest($layout["settings"]["AREA_GALLERY_MENU_PLUGIN"]);

if(check_function("system_set_js"))
    system_set_js($cm->oPage, "/", false);

if(check_function("process_gallery_menu"))
	$res = process_gallery_menu("/", NULL, $layout, DISK_UPDIR, true);

if(strlen($res["content"]))
    $cm->oPage->addContent($res["content"]);

if(isset($_REQUEST["ret_url"])) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "back";
    $oButton->action_type = "gotourl";
    $oButton->url = $_REQUEST["ret_url"];
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("back");
    $cm->oPage->addContent($oButton->process());
}
    


?>
