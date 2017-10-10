<?php
function MD_newsletter_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT * FROM module_newsletter WHERE email = " . $db->toSql($component->form_fields["email"]->value);
    $db->query($sSQL);
    if($db->nextRecord()) {
        return true;
    }
} 
?>
