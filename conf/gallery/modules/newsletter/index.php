<?php              
//require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oRecord = ffRecord::factory($oPage);

$db_gallery->query("SELECT module_newsletter.*
                        FROM 
                            module_newsletter
                        WHERE 
                            module_newsletter.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
if($db_gallery->nextRecord()) {
	$service_type = $db_gallery->getField("service_type", "Text", true);
	$service_url = $db_gallery->getField("url", "Text", true);
	$service_url_width = $db_gallery->getField("url_width", "Text", true);
	$service_url_height = $db_gallery->getField("url_height", "Text", true);
	$service_form = $db_gallery->getField("form", "Text", true);
        
        switch($service_type) {
            case "mailchimp":
                $template = "newsletter-mailchimp.html";
                if(strlen($service_form)) {
                    $tpl = ffTemplate::factory(get_template_cascading($user_path, $template, "/modules/newsletter", ffCommon_dirname(__FILE__)));
                    $tpl->load_file($template, "main");
                    /*
                    $cm->oPage->tplAddCss("mailchimp-css"
                                                    , "classic-081711.css"
                                                    , "http://cdn-images.mailchimp.com/embedcode"						
                                                    , "stylesheet"
                                                    , "text/css"
                                                    , false
                                                    , false
                                                    , null
                                                    , false
                                                    , "bottom");*/

                   // $cm->oPage->tplAddJs("mailchimp", "mc-validate.js", "//s3.amazonaws.com/downloads.mailchimp.com/js", false, false, null, false, "bottom");	
                   // $cm->oPage->tplAddJs("mailchimp-check", "mailchimp.js", FF_THEME_DIR . "/gallery/javascript/system", false, false, null, false, "bottom");
                    
                    $tpl->set_var("service_type", $service_type);
                    
                    $service_form = preg_replace("/<script.*?\/script>/s", "", $service_form);
                    
                    $service_form = preg_replace('/<style.*?\/style>/s', '', $service_form);
                    $service_form = preg_replace('/<link.*?>/s', '', $service_form);
                    $tpl->set_var("embed", $service_form);
                    $tpl->parse("SezEmbed", false);
                    
                    $cm->oPage->addContent($tpl->rpparse("main", false), null, $oRecord->user_vars["MD_chk"]["id"]);
                }
                
                break;
            default:
                $template = "newsletter.html";
                $tpl = ffTemplate::factory(get_template_cascading($user_path, $template, "/modules/newsletter", ffCommon_dirname(__FILE__)));
                $tpl->load_file($template, "main");
                
                $tpl->set_var("service_type", $service_type);
	
                if(strlen($service_form)) {

                    $tpl->set_var("embed", $service_form);
                    $tpl->parse("SezEmbed", false);
                } else {
                    if(strlen($service_url)) {
                        $tpl->set_var("width", ($service_url_width ? $service_url_width : "100%"));
                        $tpl->set_var("height", ($service_url_height ? $service_url_height : "auto"));
                        $tpl->set_var("url", $service_url);
                        $tpl->parse("SezFrame", false);
                    } else {
                        $tpl->set_var("SezFrame", "");
                    }

                    $tpl->set_var("SezEmbed", "");

                    $oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
                    $oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
                    $oRecord->src_table = ""; 
                    $oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"]; 
                    $oRecord->skip_action = true;
                    $oRecord->hide_all_controls = true;

                    $oRecord->fixed_post_content = $tpl->rpparse("main", false);

                    $oPage->addContent($oRecord);
                }

        }

	
	
	

	
	
}
?>
