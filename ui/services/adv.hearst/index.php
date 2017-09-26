<?php 
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"]) 
    {
	   	$oPage->tplAddJs("Hearst", array(
	   		"path" => FF_THEME_DIR . "/" . FRONTEND_THEME . "/javascript/" . "adv.hearst" //$actual_srv["js.skin"]
	   		, "file" => "skin.js"
			, "css_deps" => array(
				".style" => array(
					"path" => FF_THEME_DIR . "/" . FRONTEND_THEME . "/css/" . "adv.hearst" //$actual_srv["css.skin"]
					, "file" => "skin.css"
				)
			)
			, "js_deps" => array(
				".init" => array(
					"path" => FF_THEME_DIR . "/" . FRONTEND_THEME . "/css/" . "adv.hearst" //$actual_srv["js.init"]
					, "file" => "interstitial.js"
				)
			)
	   	));

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

			$oPage->tplAddJs("HearstTagManager", array(
	   			"embed" => $actual_srv["tagmanager"]
	   			, "js_deps" => array(
					".init" => array(
						"embed" => $js_content
					)
				)
	   		));	
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