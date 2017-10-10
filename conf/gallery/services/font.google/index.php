<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
	if($actual_srv["enable"]) 
    {
		foreach($actual_srv AS $key => $value) {
			if(strpos($key, "font") === 0 && $value) {
		    	$oPage->tplAddCss("google." . $key, "css?family=" . urlencode(str_replace("+", " ", $value)), "https://fonts.googleapis.com");	
			}
		}
	}