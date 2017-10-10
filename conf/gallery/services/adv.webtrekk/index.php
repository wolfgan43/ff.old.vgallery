<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    if($actual_srv["enable"])
    {
		switch($actual_srv["version"]) {
			case "4.3.0":
				$globals->fixed_post["body"][] = '<!-- Webtrekk 4.3.0, (c) www.webtrekk.com -->
					<script type="text/javascript">
						var webtrekkConfig = webtrekkConfig || {
									trackId: "' . $actual_srv["code"] . '",
									trackDomain : "' . $actual_srv["track_domain"] . '",
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
										safetagId: "' . $actual_srv["safe_tag"] . '",
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
				break;
			case "4.0":
				$oPage->fixed_post_content .= '<!-- Webtrekk 4,(c) www.webtrekk.com-->
					<script type="text/javascript">
					var webtrekkConfig = webtrekkConfig || {
						trackId: "' . $actual_srv["code"] . '",
						trackDomain: "' . $actual_srv["track_domain"] . '",
						domain : "www.dummy-domain-do-not-change.com",
						tabBrowsing: false,
						globalVisitorIds: false,
						cookie : "3",
						mediaCode : "wt_mc",
						contentId : "",
						safetag: {
							async: true,
							timeout: 2000,
							/** Load SafeTag from Webtrekk Server */
							safetagDomain: "responder.wt-safetag.com",
							safetagId: "' . $actual_srv["safe_tag"] . '",
							/** Load SafeTag from Customer Server */
							// customDomain: "www.domain.de",
							// customPath: "javascripts/webtrekk_safetag.js",
							option: {}
						}      									
					</script>
					<script type="text/javascript" src="/themes/site/javascript/webtrekk_v4.min.js"></script><!-- /Webtrekk -->'; 
				break;
		}
        
    }
?>