<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"]) 
    {
    	//if($actual_srv["css.skin"])
        	$oPage->tplAddCss("HearstCSS", "skin.css", FF_THEME_DIR . "/" . FRONTEND_THEME . "/css/" . "adv.hearst");
        //if($actual_srv["js.init"])
        	$oPage->tplAddJs("HearstJSInit", "interstitial.js", FF_THEME_DIR . "/" . FRONTEND_THEME . "/javascript/" . "adv.hearst");
       // if($actual_srv["js.skin"])
        	$oPage->tplAddJs("HearstJSSkin", "skin.js", FF_THEME_DIR . "/" . FRONTEND_THEME . "/javascript/" . "adv.hearst");

    	if($actual_srv["tagmanager"]) {
			$js_content = "var googletag = googletag || {};
					      googletag.cmd = googletag.cmd || [];
					      (function() {
					            var gads = document.createElement('script');
					            gads.async = true;
					            gads.type = 'text/javascript';
					            var useSSL = 'https:' == document.location.protocol;
					            gads.src = ( useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';
					            var node = document.getElementsByTagName('script')[0];
					            node.parentNode.insertBefore(gads, node);
					      })();";
	        $oPage->tplAddJs("HearstTagManager", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");  
			
			
    		$js_content = $actual_srv["tagmanager"];
	        $oPage->tplAddJs("HearstTagManagerParams", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");  
		}

         
        $globals->fixed_pre["body"][] = '<div id="div-gpt-ad-interstitial" class="GAdsContainer div-gpt-ad-interstitial">
											  <!-- begin ad tag (tile=interstitial) -->
											  <script type="text/javascript">
												    googletag.cmd.push(function() {
												          googletag.display("div-gpt-ad-interstitial");
												    });
											  </script>
										</div>';
    }