<?php
$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

$url = $user_path;
$social_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $url;

if (global_settings("MOD_INTERACTOR_ENABLE_SHARE")) 
{
    $filename = cm_cascadeFindTemplate("/contents/social-html.html", "interactor");
    //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/interactor/themes", "/contents/social-html.html", $cm->oPage->theme);
    $tpl = ffTemplate::factory(ffCommon_dirname($filename));
    $tpl->load_file(basename($filename), "main");
    
    if (global_settings("MOD_INTERACTOR_FACEBOOK_SHARE")) {
        $tpl->set_var("social_url", $social_url);
        $tpl->set_var("path", $url);
        
        if (global_settings("MOD_INTERACTOR_FACEBOOK_SHARE_COUNT")) {
            $tpl->parse("SezFacebookSocialCount", false);
        } else {
            $tpl->set_var("SezFacebookSocialCount", "");
        }
        $tpl->parse("SezFacebook", false);
    } else {
        $tpl->set_var("SezFacebook", "");
    }
    
    if (global_settings("MOD_INTERACTOR_TWITTER_SHARE")) {
        $tpl->set_var("social_url", $social_url);
        $tpl->set_var("path", $url);
        
        if (global_settings("MOD_INTERACTOR_TWITTER_SHARE_COUNT")) {
            $tpl->parse("SezTwitterSocialCount", false);
        } else {
            $tpl->set_var("SezTwitterSocialCount", "");
        }
        $tpl->parse("SezTwitter", false);
    } else {
        $tpl->set_var("SezTwitter", "");
    }
    
    if (global_settings("MOD_INTERACTOR_LINKEDIN_SHARE")) {
        $tpl->set_var("social_url", $social_url);
        $tpl->set_var("path", $url);
        
        if (global_settings("MOD_INTERACTOR_LINKEDIN_SHARE_COUNT")) {
            $tpl->parse("SezLinkedinSocialCount", false);
        } else {
            $tpl->set_var("SezLinkedinSocialCount", "");
        }
        $tpl->parse("SezLinkedin", false);
    } else {
        $tpl->set_var("SezLinkedin", "");
    }
    
    if (global_settings("MOD_INTERACTOR_GPLUS_SHARE")) {
        $tpl->set_var("social_url", $social_url);
        $tpl->set_var("path", $url);
        
        if (global_settings("MOD_INTERACTOR_GPLUS_SHARE_COUNT")) {
            $tpl->parse("SezGooglePlusSocialCount", false);
        } else {
            $tpl->set_var("SezGooglePlusSocialCount", "");
        }
        $tpl->parse("SezGooglePlus", false);
    } else {
        $tpl->set_var("SezGooglePlus", "");
    }
    $cm->oPage->tplAddCss("social-counter-css"
        , array(
            "file" => "social-counter.css"
            , "path" => "/modules/interactor/themes/css"
            , "async" => $cm->oPage->isXHR()
    ));
    $cm->oPage->tplAddJs("social-counter"
        , array(
            "file" => "social-counter.js"
            , "path" => "/modules/interactor/themes/javascript"
            , "async" => $cm->oPage->isXHR()
    ));

    $out_buffer = $tpl->rpparse("main", false); 
}
