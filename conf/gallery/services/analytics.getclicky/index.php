<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        $tpl = ffTemplate::factory(get_template_cascading("/", "getclicky.html", "/services"));
        $tpl->load_file("getclicky.html", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $globals->fixed_post["body"][] = $tpl->rpparse("main", false);
    }