<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if($actual_srv["enable"]) 
    { 
        $js_content = "
		        var e = document.createElement('script');
		        e.type = 'text/javascript'; 
		        e.src = '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
		        document.head.appendChild(e);";
		if($actual_srv["code"])
		{
			$js_content .= "
				(adsbygoogle = window.adsbygoogle || []).push({
				    google_ad_client: '" . $actual_srv["code"] . "'
				    , enable_page_level_ads: true
				  });";
		}    
        $oPage->tplAddJs("adsbygoogle", null, null, false, $oPage->isXHR(), $js_content, true, "bottom");
    }