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
			write_notification("_job_" . basename(ffCommon_dirname(__FILE__)), $strUpdater, "information", $area, FF_SITE_PATH . VG_RULE_UPDATER);
	}
}
