<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
	if(check_function('class.browser'))
	{
		$browser_detect = new Browser();
		if($browser_detect->isBrowser($browser_detect::BROWSER_IE) && $browser_detect->getVersion() <= 6) {
    		setJsRequest("warningbrowserold", "system");
		}
	}