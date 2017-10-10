<?php
	$strDiagnostic = "";
	
	$diagnostic = check_cache(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "cache" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_international(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "international" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_config(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "config" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_uploads(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "uploads" . " </h2><p>" . $diagnostic["status"] . "</p></div>";

	if(check_function("check_system"))
        $diagnostic = check_system(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "system" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_thumb(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "thumb" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_trash(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "trash" . " </h2><p>" . $diagnostic["status"] . "</p></div>";
	$diagnostic = check_database(false);
	if($diagnostic["status"])
		$strDiagnostic .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . "database" . " </h2><p>" . $diagnostic["status"] . "</p></div>";

	$last_job = time();
	if($strDiagnostic && check_function("write_notification")) {
		write_notification("_job_" . basename(ffCommon_dirname(__FILE__)), $strDiagnostic, "warning", $area, FF_SITE_PATH . VG_SITE_ADMINCHECKER);
	}
?>
