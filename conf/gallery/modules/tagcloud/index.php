<?php
$oRecord = ffRecord::factory($oPage);    

$sSQL = "SELECT search_tags_page.*
                            FROM search_tags_page
                            WHERE search_tags_page.visible > 0
                            ORDER BY search_tags_page.smart_url";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $tpl = ffTemplate::factory(get_template_cascading($user_path, "tagcloud.html", "/modules/tagcloud", __DIR__));
    $tpl->load_file("tagcloud.html", "main");

    $tpl->set_var("site_path", FF_SITE_PATH);
    do {
        $tpl->set_var("title", $db_gallery->getField("title", "Text", true));
        $tpl->set_var("smart_url", $db_gallery->getField("smart_url", "Text", true));
        $tpl->set_var("hits", $db_gallery->getField("hits", "Text", true));
        $tpl->parse("SezTagCloud", true);
    } while($db_gallery->nextRecord());

    $oRecord->fixed_post_content = $tpl->rpparse("main", false);
}

$oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
$oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
$oRecord->src_table = ""; 
$oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"]; 
$oRecord->skip_action = true;
$oRecord->hide_all_controls = true;


$oPage->addContent($oRecord);
?>