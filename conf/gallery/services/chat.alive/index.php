<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        $tpl = ffTemplate::factory(get_template_cascading("/", "chat.alive.html", "/services"));
        $tpl->load_file("chat.alive.html", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $oPage->fixed_post_content .= $tpl->rpparse("main", false);
    }