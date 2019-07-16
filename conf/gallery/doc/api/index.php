<?php
  require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

  if (!Auth::env("AREA_ADMIN_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
  }

  $cm->oPage->tplAddJs("ff.cms.doc", "ff.cms.doc.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");

  $tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "api.html", "/doc"));
  $tpl->load_file("api.html", "main");

  $buffer = $tpl->rpparse("main", false);
  
  if(strlen($buffer))
  	$cm->oPage->addContent($buffer);
?>
