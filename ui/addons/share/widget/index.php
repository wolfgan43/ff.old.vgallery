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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */              

 $db = ffDB_Sql::factory();
 

$db->query("SELECT module_share.*
                        FROM 
                            module_share
                        WHERE 
                            module_share.name = " . $db->toSql($MD_chk["params"][0]));
if($db->nextRecord()) {
	$service_type = $db->getField("service_type", "Text", true);
	$service_account = $db->getField("service_account", "Text", true);
	$simple_share = $db->getField("simple_share", "Text", true);
	
	$advanced_force_absolute = $db->getField("advanced_force_absolute", "Text", true);
	$advanced_css = $db->getField("advanced_css", "Text", true);
	$advanced_html = $db->getField("advanced_html", "Text", true);
	$advanced_jsmain = $db->getField("advanced_jsmain", "Text", true);
	$advanced_jsdep = $db->getField("advanced_jsdep", "Text", true);
	$active = $db->getField("active", "Text", true);
	
	switch($service_type) {
		case "addthis":
			$simple_share = str_replace("YOUR-ACCOUNT-ID", $service_account, $simple_share);
			$advanced_jsmain = str_replace("YOUR-ACCOUNT-ID", $service_account, $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.min\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);

			if($advanced_force_absolute) {
				$advanced_css = preg_replace('#(url)\\(([^:"]*)(|(?:(?:%20|\s|\+)[^"]*\\)))#','$1(http://www.addthis.com/$2$3', $advanced_css);
				$advanced_jsmain = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://www.addthis.com/$2$3', $advanced_jsmain);
				$advanced_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://www.addthis.com/$2$3', $advanced_html);
			}
			break;
		case "sharethis":

			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.min\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);

			if($advanced_force_absolute) {
				$advanced_css = preg_replace('#(url)\\(([^:"]*)(|(?:(?:%20|\s|\+)[^"]*\\)))#','$1(http://sharethis.com/$2$3', $advanced_css);
				$advanced_jsmain = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://sharethis.com/$2$3', $advanced_jsmain);
				$advanced_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://sharethis.com/$2$3', $advanced_html);
			}
			break;
		default:
		
	}

	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = $MD_chk["id"];
	$oRecord->class = $MD_chk["id"];
	$oRecord->src_table = ""; 
	$oRecord->use_own_location = $MD_chk["own_location"];
	$oRecord->skip_action = true;
	$oRecord->hide_all_controls = true;

	if($active == "simple") {
		$oRecord->fixed_post_content = $simple_share;
	} elseif($active == "advanced") {
		// $cm->oPage->tplAddCss($oRecord->id, 
		if(strlen($advanced_css))
			$style = "<style>" . $advanced_css . "</style>";
			
        $tpl = ffTemplate::factory(null);
        $tpl->load_content($style . $advanced_jsmain . $advanced_html . $advanced_jsdep, "main");

		$oRecord->fixed_post_content = $tpl->rpparse("main", false);
	} else {
		$oRecord->fixed_post_content = "";
	}
	
	
	

	$cm->oPage->addContent($oRecord);
}
