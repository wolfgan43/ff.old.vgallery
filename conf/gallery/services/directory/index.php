<?php
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

	$tpl = ffTemplate::factory(get_template_cascading("/", "webdir.html", "/services"));
	$tpl->load_file("webdir.html", "main");
	

	$sSql = "SELECT `nome` FROM `" . CM_TABLE_PREFIX . "mod_security_domains`";
	$db_gallery->query($sSql);
	
	if($db_gallery->nextRecord()) {
		do {
			$tpl->set_var("weburl", $db_gallery->getField("nome")->getValue());
			$tpl->parse("SezWeburl", true);
		} while ($db_gallery->nextRecord());
	}
    echo $tpl->rpparse("main", false);
