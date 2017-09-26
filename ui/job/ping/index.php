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
$sSQL = "SELECT layout_type.frequency AS frequency 
		FROM layout 
			INNER JOIN layout_type ON layout_type.ID = layout.ID_type 
		WHERE layout.last_update > " . $db_job->toSql($last_job);
$db_job->query($sSQL);
if($db_job->nextRecord()) {
	$searchengine["google"] = "http://www.google.com/webmasters/tools/ping?sitemap=";
	$searchengine["bing"] = "http://www.bing.com/webmaster/ping.aspx?siteMap=";
	$searchengine["yahoo"] = "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url=";
	$searchengine["ask.com"] = "http://submissions.ask.com/ping?sitemap=";
	$searchengine["moreover"] = "http://api.moreover.com/ping?u=";
	//$searchengine["pingomatic"] = "http://pingomatic.com/ping/?title=" . urlencode(CM_LOCAL_APP_NAME) . "&blogurl=" . urlencode("http://" . DOMAIN_INSET)  . "&chk_weblogscom=on&chk_blogs=on&chk_technorati=on&chk_feedburner=on&chk_syndic8=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_blogstreet=on&chk_moreover=on&chk_weblogalot=on&chk_icerocket=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_bloglines=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on&rssurl=";
	$searchengine["pingomatic"] = "http://pingomatic.com/ping/?title=" . urlencode(CM_LOCAL_APP_NAME) . "&blogurl=" . urlencode("http://" . DOMAIN_INSET)  . "&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on&chk_audioweblogs=on&chk_rubhub=on&chk_a2b=on&chk_blogshares=on&rssurl=http%3A%2F%2F";

	$arrFrequency = array("always" => 10
	                        , "hourly" => 9
	                        , "daily" => 8
	                        , "weekly" => 7
	                        , "monthly" => 6
	                        , "yearly" => 5
	                        , "never" => 4
	                    );
	$arrPeriod = array("always" => 0
	                        , "hourly" => 60 * 60
	                        , "daily" => 24 * 60 * 60
	                        , "weekly" => 7 * 24 * 60 * 60
	                        , "monthly" => 30 * 24 * 60 * 60
	                        , "yearly" => 365 * 24 * 60 * 60
	                        , "never" => 100 * 365 * 24 * 60 * 60
	                    );

	$frequency = "";
	do {
		if($arrFrequency[$db_job->getField("frequency", "Text", true)] > $arrFrequency[$frequency])
			$frequency = $db_job->getField("frequency", "Text", true);

	} while ($db_job->nextRecord());

	$actual_job = time();
	if($actual_job >= ($last_job + $arrPeriod[$frequency])) {
		$strPing = "";
		foreach($searchengine AS $searchengine_key => $searchengine_value) {
			$body_only = array();
			
			$strData = @file_get_contents($searchengine_value . urlencode("http://" . DOMAIN_INSET . "/sitemap.xml"));
			
			preg_match('/<body(.*)>(.*)<\/body>/s', $strData, $body_only);
			
			if(count($body_only)) {
				$strPing .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . $searchengine_key . " </h2><p>" . $body_only[count($body_only)-1] . "</p></div>";
			} else {
				$xmlPing = @simplexml_load_string($strData);
				if(is_object($xmlPing) && get_class($xmlPing) === "SimpleXMLElement") {
					$strPing .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . $searchengine_key . " </h2><pre>" . print_r((array)$xmlPing, true) . "</pre></div>";
				} else {
					$strPing .= "<div class=\"job_" . basename(ffCommon_dirname(__FILE__)) . "\"><h2>" . $searchengine_key . " </h2><p>" . strip_tags(ffCommon_specialchars($strData)) . "</p></div>";
				}
			}
		}
		
		$last_job = $actual_job;
		if($strPing && check_function("write_notification")) {
			write_notification("_job_" . basename(ffCommon_dirname(__FILE__)), $strPing, "information", $area);
		}
	}
}
