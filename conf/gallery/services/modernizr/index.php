<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) {
        if(!$actual_srv["version"])
            $actual_srv["version"] = "2.8.3";

        $oPage->tplAddJs("Modernizr", "modernizr.min.js", "https://cdnjs.cloudflare.com/ajax/libs/modernizr/" . $actual_srv["version"], false, $oPage->isXHR(), null, false, "first");        
	}