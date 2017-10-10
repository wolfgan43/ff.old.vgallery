<?php
if(defined("MASTER_SITE") && strlen(MASTER_SITE)) {
	if(MASTER_SITE != DOMAIN_INSET) {
	    $operations["/files.php/updater"] = "json=1";
	    $operations["/structure.php"] = "json=1";
	    $operations["/files.php"] = "json=1";
	    $operations["/indexes.php"] = "json=1";
	    $operations["/data.php/basic"] = "json=1";
	    $operations["/data.php/international"] = "json=1";

	    if(check_function("get_externals")) {
	        $externals = get_externals();
	        if(is_array($externals) && count($externals)) {
        		foreach($externals AS $externals_key => $externals_value) {
        			$operations["/externals.php" . $externals_key] = "json=1";
				}
			}
		}
	    $strUpdater = "";
	    foreach($operations AS $operations_key => $operations_value) {
        	$json = @file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . "/conf/gallery/updater" . $operations_key . "?" . $operations_value);
        	$arr_json = json_decode($json, true);

        	if(count($arr_json)) {
				$strUpdater .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\">" . count($arr_json)  . " " . $operations_key . "</div>";
			}
		}

		$last_job = time();
		if(strlen($strUpdater) && check_function("write_notification"))
			write_notification("_job_" . basename(ffCommon_dirname(__FILE__)), $strUpdater, "information", $area, FF_SITE_PATH . VG_SITE_ADMINUPDATER);
	}
}
?>
