<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) 
    {
        $globals = ffGlobals::getInstance("gallery");

        if($actual_srv["base"])
            $globals->favicon["icon"] = $actual_srv["base"];
        if($actual_srv["shortcut-icon"])
            $globals->favicon["shortcut icon"] = $actual_srv["shortcut-icon"];
        if($actual_srv["mask-icon"])
            $globals->favicon["mask-icon"] = $actual_srv["mask-icon"];
        if($actual_srv["apple-touch-icon"])
            $globals->favicon["apple-touch-icon"] = $actual_srv["apple-touch-icon"];

        if($actual_srv["icons"]) {
        	//$globals->favicon["base"] = $actual_srv["base"];
		}
    }
