<?php
function MD_shippingprice_populate_report($component, $key) {

    $db = ffDB_Sql::factory();
    
    //$component = $fields["description"]->parent[0];
    $key = $component->key_fields["shippingprice-ID"]->value;

    $sSQL = "SELECT 
                " . FF_SUPPORT_PREFIX . "state.name
            FROM 
                " . FF_SUPPORT_PREFIX . "state
            WHERE " . FF_SUPPORT_PREFIX . "state.ID_zone = " .  $db->toSql($key) . " 
            ORDER BY " . FF_SUPPORT_PREFIX . "state.name";
   // ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars());
    $db->query($sSQL);
    while ($db->nextRecord())
    {
        if (strlen($tmp_value = $db->getField("nome")->getValue()))
            $tmp_report .= " - " . $tmp_value;
    } 

    return new ffData($tmp_report);
}
?>
