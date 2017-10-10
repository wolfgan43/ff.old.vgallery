<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) 
    {
        $globals = ffGlobals::getInstance("gallery");
        
        foreach($actual_srv AS $viewport_attr => $viewport_value) {
            if($viewport_attr != "enable" && strlen($viewport_value)) {
                $arrViewport[] = $viewport_attr . "=" . $viewport_value;
            }
        }
        if(is_array($arrViewport))
            $globals->meta["viewport"] = implode(", ", $arrViewport);

    }
