<?php
if(!Auth::isLogged()) {
    exit;
}

$cm->oPage->addContent(null, true, "rel");

if(isset($_REQUEST["frmAction"])) {
    $db = ffDB_Sql::factory();

    if($_REQUEST["frmAction"] == "hideall") {
        $sSQL = "UPDATE notify_message SET visible = '0'";
        $db->execute($sSQL);
        ffRedirect($_REQUEST["ret_url"]);
    } elseif($_REQUEST["frmAction"] == "clearall") {
        $sSQL = "TRUNCATE TABLE notify_message";
        $db->execute($sSQL);
        ffRedirect($_REQUEST["ret_url"]);
    } elseif($_REQUEST["frmAction"] == "hide" && isset($_REQUEST["title"]) && strlen($_REQUEST["title"])) {
        $sSQL = "UPDATE notify_message SET visible = '0' WHERE title = " . $db->toSql($_REQUEST["title"]);
        $db->execute($sSQL);
    }
}

exit;

