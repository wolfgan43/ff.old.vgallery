<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$filename = cm_cascadeFindTemplate("/contents/index.html", "collection");
//$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/index.html", $cm->oPage->theme);
$tpl = ffTemplate::factory(ffCommon_dirname($filename));

$tpl->load_file("index.html", "main");

$cm->oPage->tplAddCSS("collection"
    , array(
        "file" => "collection.css"
        , "path" => "/modules/collection/themes/restricted/css"
));
$cm->oPage->tplAddCSS("welcome"
    , array(
        "file" => "welcome.css"
        , "path" => "/modules/collection/themes/restricted/css"
));

$cm->oPage->addContent($tpl->rpparse("main", false));