<?php
function MD_maps_on_before_parse_row($component) {
    $component->row_class = ffCommon_url_rewrite($component->db[0]->getField("vgallery_name", "Text", true)) . " mrk-" . $component->db[0]->getField("ID", "Number", true);
    $component->row_properties = array("data-rel" => $component->db[0]->getField("ID", "Number", true)); 
}

function module_maps_tabs($arrVgallery) {
    $tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "marker_tab.html", "/modules/maps", __DIR__));
    $tpl->load_file("marker_tab.html", "main");
    
    array_unshift($arrVgallery, ffTemplate::_get_word_by_code("all"));
    
    foreach($arrVgallery AS $name => $value) {
        $tpl->set_var("vgallery_name_norm", ffCommon_url_rewrite($value));
        $tpl->set_var("vgallery_name", $value);
        $tpl->parse("SezVgallerySelector", true);
    }
    
    return $tpl->rpparse("main", false);
}
	