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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */ 
	// $globals : globals settings
    // $actual_srv = params defined by system
    if($actual_srv["enable"])
    {
        $globals->fixed_post["body"][] = '<!-- Webtrekk 4.3.0, (c) www.webtrekk.com -->
        								<script type="text/javascript">
											var webtrekkConfig = webtrekkConfig || {
														trackId: "' . $actual_srv["code"] . '",
														trackDomain : "hearstmagazineitalia01.webtrekk.net",
														domain : "'. DOMAIN_INSET . '",
														cookie : "3",
														mediaCode : "wt_mc",
														contentId : "",
														tabBrowsing: false,
														execCDB: false,
														execRTA: false,
														safetag: {
															async: true,
															timeout: 2000,
															/** Load SafeTag from Webtrekk Server */
															safetagDomain: "responder.wt-safetag.com",
															safetagId: "637331967074211",
															/** Load SafeTag from Customer Server */
															// customDomain: "www.domain.de",
															// customPath: "javascripts/webtrekk_safetag.js",
															option: {}
														}
													};        									
        								</script>
										<script type="text/javascript" src="/themes/site/javascript/webtrekk_v4.min.js"></script>
										<script type="text/javascript">
										<!--
										function getContentIdByURL(){
										   var url = document.location.href;
										   if(url && url !== null) {
											  return url.split("?")[0].toLowerCase();
										   }
										   return "no_content";
										}
										var pageConfig = {
											  link Track : "", // Attivare la rilevazione dei LINK ["link" o "standard"]
											  heatmap : "",           // Attivare la rilevazione della HEATMAP ["1" = on | "0" = off]
											  form : "",        // Attivare la rilevazione dei FORM ["1" = on | "0" = off]
											  contentId : getContentIdByURL()          // Popolare con il NOME della pagina
										};
										var wt = new webtrekkV3(pageConfig);
										wt.contentGroup = {
										1 : "Hearst-Italia", //Network
										2 : "' . DOMAIN_NAME . '" //Sito
										 
										};
										wt.sendinfo();
										//-->
										</script>
										<noscript><div><img src="http://hearstmagazineitalia01.webtrekk.net/637331967074211/wt.pl?p=321,0" height="1" width="1" alt="" /></div></noscript>
										<!-- /Webtrekk -->';
    }
?>