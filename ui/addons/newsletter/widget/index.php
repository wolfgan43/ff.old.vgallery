<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */              

$db = ffDB_Sql::factory();


$db->query("SELECT module_newsletter.*
                        FROM 
                            module_newsletter
                        WHERE 
                            module_newsletter.name = " . $db->toSql($MD_chk["params"][0]));
if($db->nextRecord()) {
	$service_type = $db->getField("service_type", "Text", true);
	$service_url = $db->getField("url", "Text", true);
	$service_url_width = $db->getField("url_width", "Text", true);
	$service_url_height = $db->getField("url_height", "Text", true);
	$service_form = $db->getField("form", "Text", true);
        
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

                    $cm->oPage->tplAddJs("library.mailchimp.subscribe");
                    
                   // $cm->oPage->tplAddJs("mailchimp", "mc-validate.js", "//s3.amazonaws.com/downloads.mailchimp.com/js", false, false, null, false, "bottom");	
                   // $cm->oPage->tplAddJs("mailchimp-check", "mailchimp.js", FF_THEME_DIR . "/gallery/javascript/system", false, false, null, false, "bottom");
                    
                    $tpl->set_var("service_type", $service_type);
                    
                    $service_form = preg_replace("/<script.*?\/script>/s", "", $service_form);
                    
                    $service_form = preg_replace('/<style.*?\/style>/s', '', $service_form);
                    $service_form = preg_replace('/<link.*?>/s', '', $service_form);
                    $tpl->set_var("embed", $service_form);
                    $tpl->parse("SezEmbed", false);
                    
                    $cm->oPage->addContent($tpl->rpparse("main", false), null, $MD_chk["id"]);
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

					$oRecord = ffRecord::factory($cm->oPage);
                    $oRecord->id = $MD_chk["id"];
                    $oRecord->class = $MD_chk["id"];
                    $oRecord->src_table = ""; 
                    $oRecord->use_own_location = $MD_chk["own_location"]; 
                    $oRecord->skip_action = true;
                    $oRecord->hide_all_controls = true;

                    $oRecord->fixed_post_content = $tpl->rpparse("main", false);

                    $cm->oPage->addContent($oRecord);
                }

        }

	
	
	

	
	
}
