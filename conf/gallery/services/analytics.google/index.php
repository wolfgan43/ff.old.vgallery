<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"] && strlen($actual_srv["code"])) {
        $tpl = ffTemplate::factory(get_template_cascading("/", "google.analytics.html", "/services"));
        $tpl->load_file("google.analytics.html", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $globals->fixed_post["body"][] =  $tpl->rpparse("main", false);
    }