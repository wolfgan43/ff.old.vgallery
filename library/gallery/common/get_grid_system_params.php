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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function get_grid_system_params() {

}

function get_grid_system_menu($type, $follow_default_setting = true, $is_child = false) {
	$cm = cm::getInstance();
	if($follow_default_setting)
		$template_framework = $cm->oPage->framework_css["name"];
		
	switch($type . ($is_child ? "-child" : "")) {
		case "offcanvas":
			$res = get_grid_system_menu_side_offcanvas($template_framework);
			break;
		case "offcanvas-right":
			$res = get_grid_system_menu_side_offcanvas($template_framework, "right");
			break;
		case "offcanvas-child":
			$res = get_grid_system_menu_side_offcanvas_child($template_framework);
			break;
		case "offcanvas-right-child":
			$res = get_grid_system_menu_side_offcanvas_child($template_framework, "right");
			break;
		case "side":
			$res = get_grid_system_menu_side($template_framework);
			break;
		case "side-child":
			$res = get_grid_system_menu_side_child($template_framework);
			break;
		default:
			if($is_child)
				$res = get_grid_system_menu_default_child($template_framework);
			else 
				$res = get_grid_system_menu_default($template_framework);
	}
	
	return $res;
}


function get_grid_system_menu_default($template_framework)
{
	switch ($template_framework) {
		case "bootstrap":
			$tpl_name 						= "default_bootstrap.html";
			$icon  							= cm_getClassByFrameworkCss("more", "icon");
			$class["current"] 				= "active";
			$class["has_child"] 			= "dropdown";
			$class["sticky"] 				= "navbar-fixed-top";
			$class["nav"] 					= "navbar-default";
			break;
		case "foundation":
			$tpl_name 						= "default_foundation.html";
			$class["current"] 				= "active";
			$class["has_child"] 			= "has-dropdown";
			$class["sticky"] 				= "sticky";
			$class["nav"] 					= "";
			break;
		default:
			$tpl_name 						= "default.html";
			
			$class["current"] 				= "current";
			$class["has_child"] 			= "caret";
			$class["sticky"] 				= "";
	}
	
	return array("tpl_name" 				=> $tpl_name
					, "icon"				=> $icon
					, "class" 				=> $class
				);
}

function get_grid_system_menu_default_child($template_framework)
{
	$tpl_name = "default_child.html";
	
	switch ($template_framework) {
		case "bootstrap":
			$class["current"] 				= "active";
			$class["has_child"] 			= "has-dropdown";
			$class["dropdown"] 				= "dropdown-menu";
			$class["dropdown_sub"] 			= "dropdown-submenu";
			break;
		case "foundation":
			$class["current"] 				= "active";
			$class["has_child"] 			= "has-dropdown";
			$class["dropdown"] 				= "dropdown";
			$class["dropdown_sub"] 			= "dropdown-sub";
			break;
		default:
			$class["current"] 				= "current";
			$class["has_child"] 			= "caret";
			$class["dropdown"] 				= "child";
			$class["dropdown_sub"]          = "child";
	}
	
	return array("tpl_name" 				=> $tpl_name
					, "class" 				=> $class
				);
}

function get_grid_system_menu_side($template_framework) 
{
	switch ($template_framework) {
		case "bootstrap":
			$tpl_name 						= "side_bootstrap.html";
			$class["nav"] 					= "sidebar-offcanvas sidebar-offcanvas";
			break;
		case "foundation":
			$tpl_name 						= "side_foundation.html";
			$class["nav"] 					= "sidebar";
			break;
		default:
			return get_grid_system_menu_default($template_framework);
	}
	
	$class["current"] 						= "active";	
	
	return array("tpl_name" 				=> $tpl_name
					, "class" 				=> $class
				);
}

function get_grid_system_menu_side_child($template_framework)
{
	if(strlen($template_framework)) 
	{
		$tpl_name 							= "side_child.html";
		$class["current"] 					= "active";
	} else {
		return get_grid_system_menu_default_child($template_framework);
	}
	
	return array("tpl_name" 				=> $tpl_name
					, "class" 				=> $class
				);
}

function get_grid_system_menu_side_offcanvas($template_framework, $side = "left") 
{
	switch ($template_framework) {
		case "foundation":
	        $tpl_name 						= "offcanvas_foundation.html";
	        
	        $class["has_child"] 			= "has-submenu";
	        $class["nav"] 					= "off-canvas-wrap";
	        $class["class_menu_toggle"] 	= $side . "-off-canvas-toggle";
	        break;
		case "bootstrap": //da fare l'offcanvas per bootstrap
            $tpl_name 						= "offcanvas_bootstrap.html";
            
            $class["has_child"] 			= "has-dropdown";
            $class["dropdown"] 				= "dropdown-menu";
            break;
		default:
			return get_grid_system_default_menu($template_framework);
	}

	$class["current"] 						= "active";
	$class["side"] 							= $side;
	
	$template["container"]["class"] 		= "off-canvas-wrap";
	$template["container"]["wrap"] 			= true;
	$template["container"]["wrap_class"] 	= "inner-wrap";
	$template["container"]["properties"] 	= "data-offcanvas";	
    $template["content"] 					= '<nav class="tab-bar hide-for-large-up"> 
					                            <a class="menu-icon ' . $class["class_menu_toggle"] . '">
					                                <span></span>
					                            </a>
					                        </nav>';
	
	return array("tpl_name" 				=> $tpl_name
					, "template" 			=> $template
					, "class" 				=> $class
				);
}

function get_grid_system_menu_side_offcanvas_child($template_framework, $side = "left")
{
	switch ($template_framework) {
		case "foundation":
            $tpl_name 						= "offcanvas_child.html";
            
			$class["has_child"] 			= "has-submenu";
			$class["dropdown"] 				= $side . "-submenu";
			break;
		case "bootstrap": //da fare per bootstrap
            $tpl_name 						= "offcanvas_child.html";
            
			$class["has_child"] 			= "has-submenu";
			$class["dropdown"] 				= $side . "-submenu";
			break;
		default:
			return get_grid_system_menu_default_child($template_framework);
	}
	
	$class["current"] 						= "active";		
	
	return array("tpl_name" 				=> $tpl_name
					, "class" 				=> $class
				);
}

function get_grid_system_breadcrumb($follow_default_setting = true) {
	$cm = cm::getInstance();
	if($follow_default_setting)
		$template_framework = $cm->oPage->framework_css["name"];

    if(strlen($template_framework))
    {
        switch ($template_framework) {
            case "bootstrap":
                    $class["current"] 		= "active";
                    break;
            case "foundation":
                    $class["current"] 		= "current";
                    break;
            default:
                    break;
        }
    }
	
    return array(	"class" 				=> $class
				);
}