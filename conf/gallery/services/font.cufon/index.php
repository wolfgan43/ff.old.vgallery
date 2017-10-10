<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
	$cufon_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . $oPage->theme . "/fonts/cufon/*");

	if(is_array($cufon_file) && count($cufon_file)) {
		$oPage->tplAddJs("cufon-yui", "cufon-yui.js?v=1.09i", "http://cufon.shoqolate.com/js", true, false, null, true);
	    foreach($cufon_file AS $real_file) {
	        if(is_file($real_file)) {
	            $relative_path = str_replace(FF_DISK_PATH, FF_SITE_PATH, $real_file);

	            $oPage->tplAddJs(ffGetFilename($relative_path), basename($relative_path), ffCommon_dirname($relative_path));
	        }
	    }
	}