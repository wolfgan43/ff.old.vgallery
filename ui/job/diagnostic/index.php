<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage cronjob
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
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
