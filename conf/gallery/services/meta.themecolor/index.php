<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) 
    {
        $globals = ffGlobals::getInstance("gallery");

        if($actual_srv["color"])
            $globals->meta["theme-color"] = $actual_srv["color"];
            
        if($actual_srv["ie-color"])
            $globals->meta["msapplication-TileColor"] = $actual_srv["ie-color"];

    }