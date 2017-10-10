<?php
function MD_tagcloud_name_on_load_template($oField) {
     $oGrid =& $oField->parent[0];

    $user_path = FF_SITE_PATH . "/search?search_inset=" . $oGrid->db[0]->record["contest"] . "&sap[keywords]=" . $oGrid->grid_fields["name"]->value->getValue();
     
     $oField->tpl[0]->set_var("link", $user_path);
     
     
     $fsize = MODULE_TAGCLOUD_FONT_MIN + $oGrid->db[0]->record["count"];
     if($fsize > MODULE_TAGCLOUD_FONT_MAX)
        $fsize = MODULE_TAGCLOUD_FONT_MAX;
        
     $oField->properties["style"]["font-size"] = $fsize . "px";
    
}
?>
