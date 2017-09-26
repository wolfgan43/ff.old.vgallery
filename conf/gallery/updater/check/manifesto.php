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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
    $manifesto      = array();
    $manifesto_dep  = array();
    
    function get_exclude() {
        $db = new ffDB_Sql;
        
        $fs_exclude = array();

        $sSQL = "SELECT Table_Name
                FROM information_schema.TABLES
                WHERE Table_Name = 'updater_exclude'
                    AND TABLE_SCHEMA = " . $db->toSql(FF_DATABASE_NAME);
        $db->query($sSQL);
        if($db->nextRecord()) {
            $sSQL = "SELECT updater_exclude.path
                        , updater_exclude.status 
                    FROM updater_exclude
                    WHERE updater_exclude.path NOT IN (SELECT updater_externals.path FROM updater_externals WHERE updater_externals.status > 0)";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $fs_exclude[$db->getField("path", "Text", true)] = $db->getField("status", "Number", true);
                } while($db->nextRecord());
            }
        }
        return $fs_exclude;
    } 
    $fs_manifesto_exclude = get_exclude();
    
    //FRAMEWORK CORE
    if(is_dir(FF_DISK_PATH . "/cm") && is_dir(FF_DISK_PATH . "/ff")) { 
        $manifesto["forms_framework"]["enable"] = true;
        $manifesto["forms_framework"]["type"] = "Framework Core";
        $manifesto["forms_framework"]["path"] = array(
            "/applets"
            , "/cm"
            , "/ff"
            , "/modules/restricted"
            , "/modules/security"
            , "/modules/notifier"
            , "/library/cssmin"
            , "/library/jsmin"
            , "/library/phpmailer"
            , "/themes/responsive"
            //, "/themes/restricted"
            //, "/themes/default"
            //, "/themes/dialog"
            , "/themes/library/ff"
            , "/themes/library/jquery"
            , "/themes/library/jquery-ui"
            , "/themes/library/jquery-ui.themes"
            , "/themes/library/swfobject"
        );
        $manifesto["forms_framework"]["db"] = array(
        	"exclude" => array(
        		"cm_layout"
  				, "cm_layout_cdn"
  				, "cm_layout_css"
  				, "cm_layout_js"
  				, "cm_layout_meta"
  				, "cm_layout_sect"
        	)
        ); 

        $manifesto["forms_framework"]["dep"][] = "external_plugin/jquery.blockui";
        $manifesto["forms_framework"]["dep"][] = "external_plugin/jquery.uploadify";
        $manifesto["forms_framework"]["dep"][] = "external_plugin/jquery.uploadifive";
        $manifesto["forms_framework"]["dep"][] = "external_plugin/jquery.tokeninput";
        $manifesto_dep["external_plugin/jquery.blockui"] = true;
        $manifesto_dep["external_plugin/jquery.uploadify"] = true;
        $manifesto_dep["external_plugin/jquery.uploadifive"] = true;
        $manifesto_dep["external_plugin/jquery.tokeninput"] = true;
        $manifesto_dep["external_plugin/gmap3.markerclusterer"] = true;
        $manifesto_dep["external_plugin/magicsuggest"] = true;
    }
    $vgallery_core_path = array(
        "/conf/gallery"
        , "/library/gallery"
        , "/themes/admin"
        , "/themes/gallery"
        , "/themes/site"
        , "/themes/library/codemirror"
    );

    //FRAMEWORK APPLETS
    $module_file = glob(FF_DISK_PATH . "/applets/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
                    && array_key_exists($relative_path, $fs_manifesto_exclude) == false
                ) {
                    $manifesto["ff_applet/" . basename($relative_path)]["enable"] = false;
                    $manifesto["ff_applet/" . basename($relative_path)]["type"] = "Framework Applet";
                    $manifesto["ff_applet/" . basename($relative_path)]["path"] = array(
                        $relative_path
                        , "/conf/applets/" . basename($relative_path)
                    );
                    $manifesto["ff_applet/" . basename($relative_path)]["db"] = array();
                    
                    if(file_exists($real_file . "/conf/schema.php")) {
                        require($real_file . "/conf/schema.php");
                        if(is_array($schema) && count($schema)) {
                            $manifesto["ff_applet/" . basename($relative_path)] = array_merge($manifesto["ff_applet/" . basename($relative_path)], $schema);
                        }
                    }
                }
            }
        }
    }
    
    //FRAMEWORK MODULE
    $module_file = glob(FF_DISK_PATH . "/modules/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
                    && array_key_exists($relative_path, $fs_manifesto_exclude) == false
                ) {
                    $manifesto["ff_module/" . basename($relative_path)]["enable"] = false;
                    $manifesto["ff_module/" . basename($relative_path)]["type"] = "Framework Module";
                    $manifesto["ff_module/" . basename($relative_path)]["path"] = array(
                        $relative_path
                        , "/conf/modules/" . basename($relative_path)
                    );
                    $manifesto["ff_module/" . basename($relative_path)]["db"] = array();
                    
                    if(file_exists($real_file . "/conf/schema.php")) {
                        require($real_file . "/conf/schema.php");
                        if(is_array($schema) && count($schema)) {
                            $manifesto["ff_module/" . basename($relative_path)] = array_merge($manifesto["ff_module/" . basename($relative_path)], $schema);
                        }
                    }
                }
            }
        }
    }
    //FRAMEWORK THEME
    $module_file = glob(FF_DISK_PATH . "/themes/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
               		&& array_key_exists($relative_path, $fs_manifesto_exclude) == false
                    && array_search($relative_path, $vgallery_core_path) === false
                    && $relative_path != "/themes/library"
                ) {
                    $manifesto["ff_theme/" . basename($relative_path)]["enable"] = false;
                    $manifesto["ff_theme/" . basename($relative_path)]["type"] = "Framework Theme";
                    $manifesto["ff_theme/" . basename($relative_path)]["path"][] = $relative_path;
                    $manifesto["ff_theme/" . basename($relative_path)]["db"] = array();
                    
                    if(file_exists(FF_DISK_PATH . $relative_path . "/theme_settings.xml")) {
                        $theme_settings = new SimpleXMLElement(FF_DISK_PATH . $relative_path . "/theme_settings.xml", null, true);
                        if (isset($theme_settings->default_css) && count($theme_settings->default_css)) {
                            foreach ($theme_settings->default_css->children() as $key => $value) {
                                if(strlen((string)$value->path) && strlen((string)$value->file) && strpos((string)$value->path, "/themes/library/plugins") === 0) {
                                    $manifesto["ff_theme/" . basename($relative_path)]["dep"][] = "external_plugin/" . basename((string)$value->path);
                                    if($manifesto["ff_theme/" . basename($relative_path)]["enable"])                                
                                        $manifesto_dep["external_plugin/" . basename((string)$value->path)] = true;
                                }
                            }
                        }
                        if (isset($theme_settings->default_js) && count($theme_settings->default_js)) {
                            foreach ($theme_settings->default_js->children() as $key => $value) {
                                if(strlen((string)$value->path) && strlen((string)$value->file) && strpos((string)$value->path, "/themes/library/plugins") === 0) {
                                    $manifesto["ff_theme/" . basename($relative_path)]["dep"][] = "external_plugin/" . basename((string)$value->path);
                                    if($manifesto["ff_theme/" . basename($relative_path)]["enable"])
                                        $manifesto_dep["external_plugin/" . basename((string)$value->path)] = true;
                                }
                            }
                        }
                        if (isset($theme_settings->default_jqueryui_theme) && count($theme_settings->default_jqueryui_theme)) {
                            foreach ($theme_settings->default_jqueryui_theme->children() as $key => $value) {
                                $attrs = $value->attributes();
                                if(strlen((string)$attrs["name"])) {
                                    $theme_ui = (string)$attrs["name"];
                                } else {
                                    $theme_ui = $key;
                                }
                                
                                $manifesto["ff_theme/" . basename($relative_path)]["dep"][] = "jqueryui_theme/" . $theme_ui;
                                if($manifesto["ff_theme/" . basename($relative_path)]["enable"])
                                    $manifesto_dep["jqueryui_theme/" . $theme_ui] = true;
                            }
                        }
                    }
                }
            }
        }
    }     
    //VGALLERY CORE
    if(is_dir(FF_DISK_PATH . "/conf/gallery") && is_dir(FF_DISK_PATH . "/library/gallery")) { 
        $manifesto["vgallery_cms"]["enable"] = true;
        $manifesto["vgallery_cms"]["type"] = "VGallery Core";
        $manifesto["vgallery_cms"]["path"] = $vgallery_core_path;
        $manifesto["vgallery_cms"]["db"] = array();

        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/jquery.cluetip";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/jquery.nicescroll";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/jquery.pngfix";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/jquery.printelement";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/jquery.checkbox";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/respond";
        $manifesto["vgallery_cms"]["dep"][] = "external_plugin/freewall";
        $manifesto_dep["external_plugin/jquery.cluetip"] = true;
        $manifesto_dep["external_plugin/jquery.nicescroll"] = true;
        $manifesto_dep["external_plugin/jquery.pngfix"] = true;
        $manifesto_dep["external_plugin/jquery.printelement"] = true;
        $manifesto_dep["external_plugin/jquery.checkbox"] = true;
        $manifesto_dep["external_plugin/respond"] = true;
        $manifesto_dep["external_plugin/freewall"] = true;
    }
    
    //VGALLERY ECOMMERCE
    if(is_dir(FF_DISK_PATH . "/conf/gallery/ecommerce") && is_dir(FF_DISK_PATH . "/library/gallery/ecommerce")) { 
        $manifesto["vgallery_ecommerce"]["enable"] = true;
        $manifesto["vgallery_ecommerce"]["type"] = "VGallery Ecommerce";
        $manifesto["vgallery_ecommerce"]["path"] = array(
        	"/conf/gallery/ecommerce"
        	, "/library/gallery/ecommerce"
        );
        $manifesto["vgallery_ecommerce"]["db"]["table_prefix"] = "ecommerce_";
    }
    
    //VGALLERY MASTER CONTROL
    if(is_dir(FF_DISK_PATH . "/conf/gallery/mc")) {
        $manifesto["vgallery_master_control"]["enable"] = false;
        $manifesto["vgallery_master_control"]["type"] = "VGallery Updater";
        $manifesto["vgallery_master_control"]["path"] = array(
            "/conf/gallery/mc"
            , "/themes/site/manifesto.xml"
        );
        $manifesto["vgallery_master_control"]["db"] = array();
    }
    //VGALLERY PLUGINS
    $module_file = glob(FF_DISK_PATH . "/themes/gallery/javascript/plugin/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
                	&& array_key_exists($relative_path, $fs_manifesto_exclude) == false
                    && array_search($relative_path, $vgallery_core_path) === false
                ) {
                    $manifesto["vgallery_plugin/" . basename($relative_path)]["enable"] = true;
                    $manifesto["vgallery_plugin/" . basename($relative_path)]["type"] = "VGallery Plugin";
                    $manifesto["vgallery_plugin/" . basename($relative_path)]["path"] = $relative_path;
                    $manifesto["vgallery_plugin/" . basename($relative_path)]["db"] = array();

                    $manifesto["vgallery_plugin/" . basename($relative_path)]["dep"][] = "external_plugin/" . basename($relative_path);
                    $manifesto_dep["external_plugin/" . basename($relative_path)] = true;
                }
            }
        }
    }   

    
    //VGALLERY MODULE
    $module_file = glob(FF_DISK_PATH . VG_ADDONS_PATH . "/*" /*"/conf/gallery/modules/*"*/);
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
				if(array_key_exists($relative_path, $fs_manifesto_exclude) == false
                ) {
	                $manifesto["vgallery_module/" . basename($relative_path)]["enable"] = true;
	                $manifesto["vgallery_module/" . basename($relative_path)]["type"] = "VGallery Module";
	                $manifesto["vgallery_module/" . basename($relative_path)]["path"] = $relative_path;
	                $manifesto["vgallery_module/" . basename($relative_path)]["db"] = array();
				}
            }
        }
    }
    
    //EXTERNAL APPLICATIONS
    $module_file = glob(FF_DISK_PATH . "/themes/library/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
                	&& array_key_exists($relative_path, $fs_manifesto_exclude) == false
                    && array_search($relative_path, $vgallery_core_path) === false
                    && basename($relative_path) != "plugins"
                ) {
                    $manifesto["external_app/" . basename($relative_path)]["enable"] = (basename($relative_path) == "ckeditor" || basename($relative_path)  == "kcfinder" ? true : false) ;
                    $manifesto["external_app/" . basename($relative_path)]["type"] = "External Application";
                    $manifesto["external_app/" . basename($relative_path)]["path"][] = $relative_path;
                    if(is_dir(FF_DISK_PATH . "/themes/responsive/ff/ffField/widgets/" . basename($relative_path)))
                        $manifesto["external_app/" . basename($relative_path)]["path"][] = "/themes/responsive/ff/ffField/widgets/" . basename($relative_path);

                    $manifesto["external_app/" . basename($relative_path)]["db"] = array();
                }
            }
        }
    }

    //EXTERNAL APPLICATIONS FIX
    if(array_key_exists("external_app/ckfinder", $manifesto)) 
        $manifesto["external_app/ckfinder"]["path"][] = "/themes/responsive/ff/ffField/widgets/ckuploadify";
    if(array_key_exists("external_app/kcfinder", $manifesto)) 
        $manifesto["external_app/kcfinder"]["path"][] = "/themes/responsive/ff/ffField/widgets/kcuploadify";
    
    //JQUERY UI THEME
   /* $module_file = glob(FF_DISK_PATH . "/themes/library/jquery-ui.themes/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
				if(array_key_exists($relative_path, $fs_manifesto_exclude) == false
                ) {
	                $fix_value = (isset($manifesto_dep["jqueryui_theme/" . basename($relative_path)]) ? true : false);
	                
	                $manifesto["jqueryui_theme/" . basename($relative_path)]["enable"] = ($fix_value || basename($relative_path) == "base" ? true : false);
	                $manifesto["jqueryui_theme/" . basename($relative_path)]["fix"] = $fix_value;
	                $manifesto["jqueryui_theme/" . basename($relative_path)]["type"] = "jQuery UI Theme";
	                $manifesto["jqueryui_theme/" . basename($relative_path)]["path"] = $relative_path;
	                $manifesto["jqueryui_theme/" . basename($relative_path)]["db"] = array();
				}
            }
        }
    } */  

    //EXTERNAL PLUGINS
    $module_file = glob(FF_DISK_PATH . "/themes/library/plugins/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
                if(array_search($relative_path, $manifesto["forms_framework"]["path"]) === false
                	&& array_key_exists($relative_path, $fs_manifesto_exclude) == false
                    && array_search($relative_path, $vgallery_core_path) === false
                ) {
                    $fix_value = (isset($manifesto_dep["external_plugin/" . basename($relative_path)]) ? true : false);
                    if(strpos($relative_path, "jquery") !== false && !file_exists($real_file . "/" . basename($real_file) . ".observe.js")) {
                        $is_addon = true;
                    } else {
                        $is_addon = false;
                    }
                        
                    //|| strpos($relative_path, "jquery") !== false || strpos($relative_path, "swfobject") !== false
                    $manifesto["external_plugin/" . basename($relative_path)]["enable"] = ($fix_value || $is_addon ? true : false);
                    $manifesto["external_plugin/" . basename($relative_path)]["fix"] = $fix_value;
                    $manifesto["external_plugin/" . basename($relative_path)]["type"] = "External Plugin";
                    $manifesto["external_plugin/" . basename($relative_path)]["path"] = $relative_path;
                    $manifesto["external_plugin/" . basename($relative_path)]["db"] = array();
                }
            }
        }
    }

    if(file_exists(FF_DISK_PATH . "/themes/site/manifesto.xml")) {
        $arrManifesto = new SimpleXMLElement(FF_DISK_PATH . "/themes/site/manifesto.xml", null, true);
        foreach($arrManifesto->children() AS $arrManifesto_key => $arrManifesto_value) {
            $tmpManifestoAttr = $arrManifesto_value->attributes();
            
            $tmp_manifesto_key = (string) $tmpManifestoAttr["id"];
            $tmp_manifesto_value = (string) $tmpManifestoAttr["enable"];

            if(array_key_exists($tmp_manifesto_key, $manifesto)) {
                $manifesto[$tmp_manifesto_key]["enable"] = $tmp_manifesto_value;
            }
        }
    }
