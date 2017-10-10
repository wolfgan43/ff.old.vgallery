<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if($actual_srv["enable"] && strlen($actual_srv["code"])) 
	{
		$js_content = '<script src="//load.sumome.com/" data-sumo-site-id="' . $actual_srv["code"] . '" async="async"></script>';
		$oPage->tplAddJs("sumome", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");
    }