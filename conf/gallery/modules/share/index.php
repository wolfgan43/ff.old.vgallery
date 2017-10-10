<?php              
//require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oRecord = ffRecord::factory($oPage);

$db_gallery->query("SELECT module_share.*
                        FROM 
                            module_share
                        WHERE 
                            module_share.name = " . $db_gallery->toSql($oRecord->user_vars["MD_chk"]["params"][0]));
if($db_gallery->nextRecord()) {
	$service_type = $db_gallery->getField("service_type", "Text", true);
	$service_account = $db_gallery->getField("service_account", "Text", true);
	$simple_share = $db_gallery->getField("simple_share", "Text", true);
	
	$advanced_force_absolute = $db_gallery->getField("advanced_force_absolute", "Text", true);
	$advanced_css = $db_gallery->getField("advanced_css", "Text", true);
	$advanced_html = $db_gallery->getField("advanced_html", "Text", true);
	$advanced_jsmain = $db_gallery->getField("advanced_jsmain", "Text", true);
	$advanced_jsdep = $db_gallery->getField("advanced_jsdep", "Text", true);
	$active = $db_gallery->getField("active", "Text", true);
	
	switch($service_type) {
		case "addthis":
			$simple_share = str_replace("YOUR-ACCOUNT-ID", $service_account, $simple_share);
			$advanced_jsmain = str_replace("YOUR-ACCOUNT-ID", $service_account, $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.min\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);

			if($advanced_force_absolute) {
				$advanced_css = preg_replace('#(url)\\(([^:"]*)(|(?:(?:%20|\s|\+)[^"]*\\)))#','$1(http://www.addthis.com/$2$3', $advanced_css);
				$advanced_jsmain = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://www.addthis.com/$2$3', $advanced_jsmain);
				$advanced_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://www.addthis.com/$2$3', $advanced_html);
			}
			break;
		case "sharethis":

			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);
			$advanced_jsmain = preg_replace('/<script(.*)src=(.*)jquery\.min\.js(.*)>(.*)<\/script>/', '', $advanced_jsmain);

			if($advanced_force_absolute) {
				$advanced_css = preg_replace('#(url)\\(([^:"]*)(|(?:(?:%20|\s|\+)[^"]*\\)))#','$1(http://sharethis.com/$2$3', $advanced_css);
				$advanced_jsmain = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://sharethis.com/$2$3', $advanced_jsmain);
				$advanced_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://sharethis.com/$2$3', $advanced_html);
			}
			break;
		default:
		
	}

	$oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
	$oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
	$oRecord->src_table = ""; 
	$oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"];
	$oRecord->skip_action = true;
	$oRecord->hide_all_controls = true;

	if($active == "simple") {
		$oRecord->fixed_post_content = $simple_share;
	} elseif($active == "advanced") {
		// $oPage->tplAddCss($oRecord->id, 
		if(strlen($advanced_css))
			$style = "<style>" . $advanced_css . "</style>";
			
        $tpl = ffTemplate::factory(null);
        $tpl->load_content($style . $advanced_jsmain . $advanced_html . $advanced_jsdep, "main");

		$oRecord->fixed_post_content = $tpl->rpparse("main", false);
	} else {
		$oRecord->fixed_post_content = "";
	}
	
	
	

	$oPage->addContent($oRecord);
}
?>
