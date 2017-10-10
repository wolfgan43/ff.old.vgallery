<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
        
    if($actual_srv["enable"] && strlen($actual_srv["code"])) 
	{
		$js_content = "
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '" . $actual_srv["code"] . "', '" . DOMAIN_NAME . "');
			ga('require', 'displayfeatures');
			" . $actual_srv["other_params"] . "
			ga('send', 'pageview');";
		$oPage->tplAddJs("ga", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");
    }