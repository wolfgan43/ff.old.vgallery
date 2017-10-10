<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
	if($actual_srv["enable"] && $actual_srv["code"]) 
		$oPage->tplAddJs("share.addthis", "addthis_widget.js#pubid=" . $actual_srv["code"], "//s7.addthis.com/js/300", false, $oPage->isXHR(), null, true);	