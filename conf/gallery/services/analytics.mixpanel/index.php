<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        $tpl = ffTemplate::factory(get_template_cascading("/", "mixpanel.js", "/services"));
        $tpl->load_file("mixpanel.js", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $oPage->tplAddJs("mixpanel", null, null, false, false, $tpl->rpparse("main", false), false, "bottom");
    }