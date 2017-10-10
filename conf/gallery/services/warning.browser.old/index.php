<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
	if(check_function('class.browser'))
	{
		$browser_detect = new Browser();
		if($browser_detect->isBrowser($browser_detect::BROWSER_IE) && $browser_detect->getVersion() <= 6) {
			/* Remove jquery ui css
				$css_deps 		= array(
				  "jquery.ui.core"        => array(
						  "file" => "jquery.ui.core.css"
						, "path" => null
						, "rel" => "jquery.ui"
					), 
				  "jquery.ui.theme"        => array(
						  "file" => "jquery.ui.theme.css"
						, "path" => null
						, "rel" => "jquery.ui"
					), 
				  "jquery.ui.dialog"        => array(
						  "file" => "jquery.ui.dialog.css"
						, "path" => null
						, "rel" => "jquery.ui"
					),
				  "jquery.ui.resizable"        => array(
						  "file" => "jquery.ui.resizable.css"
						, "path" => null
						, "rel" => "jquery.ui"
					)
			);	
			
			if(is_array($css_deps) && count($css_deps)) {
				foreach($css_deps AS $css_key => $css_value) {
					$rc = $oPage->widgetResolveCss($css_key, $css_value, $oPage);

					$oPage->tplAddCss(preg_replace('/[^0-9a-zA-Z]+/', "", $css_key), $rc["file"], $rc["path"], "stylesheet", "text/css", false, false, null, false, "bottom");
				}
			}*/
			    
    		setJsRequest("warningbrowserold", "system");
		}
	}