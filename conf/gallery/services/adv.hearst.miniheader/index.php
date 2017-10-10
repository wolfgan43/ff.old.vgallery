<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"]) 
    {
        $globals->fixed_pre["body"][] = $actual_srv["html"];

		if($actual_srv["css"])
	        $oPage->tplAddCss("HearstMiniHeaderCss", null, null, "stylesheet", "text/css", false, $oPage->isXHR(), null, false, "bottom", $actual_srv["css"]);  

		if($actual_srv["js"])
	        $oPage->tplAddJs("HearstMiniHeaderJs", null, null, false, $oPage->isXHR(), $actual_srv["js"], false, "bottom");  
	        
    }