<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_EMAIL_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_email = $_REQUEST["keys"]["email-ID"];

//$domain = $db_main->getField("id")->getValue();

  if($ID_email > 0) {
   /* $sSQL = "SELECT * FROM email
                WHERE email.ID = " . $db_gallery->toSql(new ffData($ID_email, "Number", FF_SYSTEM_LOCALE));
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
        $tpl_email_path = $db_gallery->getField("tpl_email_path")->getValue();
        $email_name = $db_gallery->getField("name")->getValue();
        $email_debug = $db_gallery->getField("email_debug", "Text", true);
    }
	if(!strlen($email_debug))
		$email_debug = EMAIL_DEBUG;*/
		
   /* if(!$tpl_email_path || !file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_email_path)) {
        $tpl_email_path = null;
	}*/
	$tpl_email_path = null;
   /* $fields["group1"]["label1"] = "test test test";
    $fields["group1"]["label2"] = "test test test";

	$fields["group2"]["settings"]["type"] = "Table";
    $fields["group2"][0]["label1"] = "test test test";
    $fields["group2"][0]["label2"] = "test test test";
    $fields["group2"][1]["label1"] = "test test test";
    $fields["group2"][1]["label2"] = "test test test";

	$fields["group3"]["settings"]["type"] = "Table";
    $fields["group3"]["label1"] = "test1 test1";
    $fields["group3"]["label2"] = "test2 test2";
    $fields["group3"]["label3"] = "test3 test3";

    $fields["group4"]["label"] = "test test test";

    $fields[]["label"] = "test test test";*/

    //$fields = null;
    
    if($_REQUEST["frmAction"] == "send") {
        //Caricamento del template di base html
        //$to[] = $email_debug;  
       // $from[] = "noreply@" . DOMAIN_NAME;
	   
	   if(check_function("process_mail")) {
	       $email_struct = email_system($ID_email);
           if($email_struct["debug"])
               $res = process_mail($email_struct, null, NULL, null, null, null, false, false, "send");
	   }
		
       if($res)
           $buffer = $email_name . " " . $res;
       else
           ffRedirect($_REQUEST["ret_url"]);

	} elseif($_REQUEST["frmAction"] == "reset") {
		$sSQL = "UPDATE email SET fields_example = '' 
				WHERE email.ID = " . $db_gallery->toSql($ID_email, "Number");
		$db_gallery->execute($sSQL);
		
		ffRedirect($_REQUEST["ret_url"]);
    } else {                                               
        //Caricamento del template di base html
	    $to[] = $email_name . "@example.ex";
        if(check_function("process_mail")) {
			$email_struct = email_system($ID_email);
        	$buffer = process_mail($email_struct, $to, NULL, null, null, NULL, NULL, NULL, true, false);
		
	        if($email_struct["debug"]) {
		        $oButton_send = ffButton::factory($cm->oPage);
		        $oButton_send->id = "send";
		        $oButton_send->action_type = "gotourl";
		        $oButton_send->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview?keys[email-ID]=" . $ID_email . "&frmAction=send&ret_url=" . urlencode($cm->oPage->site_path . $cm->oPage->page_path . "/preview?keys[email-ID]=" . $ID_email . "&ret_url=" . urlencode($_REQUEST["ret_url"]));
		        $oButton_send->aspect = "link";
                        $oButton_send->label = ffTemplate::_get_word_by_code("email_test_send") . " (" . $email_struct["debug"] . ")";
		        $oButton_send->parent_page = array(&$cm->oPage);
			}        
		}
        $oButton_customize = ffButton::factory($cm->oPage);
		$oButton_customize->id = "customize";
			
        $cm->oPage->widgetLoad("dialog");
        $cm->oPage->widgets["dialog"]->process(
             $oButton_customize->id
             , array(
                //"name" => "myTitle"
                "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/template/modify?keys[path]=" . urlencode($tpl_email_path) . "&ret_url=" . urlencode($cm->oPage->site_path . $cm->oPage->page_path . "/preview?keys[email-ID]=" . $ID_email . "&ret_url=" . urlencode($_REQUEST["ret_url"]))
                , "title" => ffTemplate::_get_word_by_code("email_template_modify_title")
                , "callback" => ""
                , "class" => ""
                , "params" => array()
                , "doredirects" => true
            )
            , $cm->oPage
        );
        $oButton_customize->action_type = "submit";
        $oButton_customize->label = ffTemplate::_get_word_by_code("email_customize");
        $oButton_customize->aspect = "link";
        $oButton_customize->jsaction = "ff.ffPage.dialog.doOpen('" . $oButton_customize->id . "')";
        $oButton_customize->parent_page = array(&$cm->oPage);
        
        $oButton_reset = ffButton::factory($cm->oPage);
        $oButton_reset->id = "reset";
        $oButton_reset->action_type = "gotourl";
        $oButton_reset->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview?keys[email-ID]=" . $ID_email . "&frmAction=reset&ret_url=" . urlencode($cm->oPage->site_path . $cm->oPage->page_path . "/preview?keys[email-ID]=" . $ID_email . "&ret_url=" . urlencode($_REQUEST["ret_url"]));
        $oButton_reset->aspect = "link";
        $oButton_reset->label = ffTemplate::_get_word_by_code("email_test_reset_fields");
        $oButton_reset->parent_page = array(&$cm->oPage);

    }
}


$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = urldecode($_REQUEST["ret_url"]);
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);

//$cm->oPage->process_params();

$cm->oPage->addContent("<div class=\"prev_top\" >" . (isset($oButton_send) ? $oButton_send->process() : "") . (isset($oButton_customize) ? $oButton_customize->process() : "") . (isset($oButton_reset) ? $oButton_reset->process() : "") . "</div>");
$cm->oPage->addContent($buffer);
$cm->oPage->addContent("<div class=\"prev_bottom\" >" . $oButton->process() . "</div>");
?>
