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
function get_short_description($description, $limit_char = 320, $type = null, $link = false)
{
	$page_break = array();
	$page_break["TextBB"]['<!-- pagebreak -->'] = "";
	$page_break["TextCK"]['<div style="page-break-after: always">'] = '<div class="page-break">';
	$page_break["TextCK"]['<div style="page-break-after: always;">'] = '<div class="page-break">';
	$page_break["TextCK"]['<div style="page-break-after: always"><span style="display: none;">&nbsp;</span></div>'] = '<div class="page-break">';
	$page_break["TextCK"]['<div style="page-break-after: always;"><span style="display: none;">&nbsp;</span></div>'] = '<div class="page-break">';
	
	$page_break_rev['<!-- pagebreak -->'] = "";
	$page_break_rev['<div style="page-break-after: always">'] = '<div class="page-break">';
	$page_break_rev['<div style="page-break-after: always;">'] = '<div class="page-break">';	
	$page_break_rev['<div style="page-break-after: always"><span style="display: none;">&nbsp;</span></div>'] = '<div class="page-break">';
	$page_break_rev['<div style="page-break-after: always;"><span style="display: none;">&nbsp;</span></div>'] = '<div class="page-break">';
	
    if($limit_char > 10 && strlen($type)) {         
        $type = "";
    }
    
    $str_char_limit = ffTemplate::_get_word_by_code("characters_limitator");
    if($link) {
        $str_char_limit = ffTemplate::_get_word_by_code("characters_break") . '<a class="charlimit-link" href="' . $link . '">' . $str_char_limit . '</a>';
    }

    if(ffCommon_charset_decode($description)) 
		$description = html_entity_decode($description, ENT_QUOTES, "UTF-8");

	$description = htmlspecialchars_decode($description);

    if(mb_strlen($description, 'UTF-8') > $limit_char && $limit_char > 0) {
    	if($type === null) {
            foreach($page_break_rev AS $page_break_key => $page_break_value) {
            	if(strpos($description, $page_break_key) !== false) {
					$part_description = array_filter(explode($page_break_key, $description));
	                $res = $part_description[0] . " ";
					break;            	
            	}
			}    	
    	}
    
    
        switch ($type) {
            case "TextBB":
            	foreach($page_break[$type] AS $page_break_key => $page_break_value) {
					$page_start_replace = "<p>";
					$page_break_replace = "</p>";

	                if(strpos($description, $page_break_key) !== false) {
	                	$block_limit = true;
	                    $part_description = explode($page_break_key, $description);
	                    if(count($part_description) > $limit_char) {
	                    	$res = $part_description[0];
						} else {
						 	$res = $part_description[$limit_char - 1];
						}
	                } 
				} reset($page_break[$type]);
                break;
            case "TextCK":
            	foreach($page_break[$type] AS $page_break_key => $page_break_value) {
	                if(strpos($description, $page_break_key) !== false) {
	                	$block_limit = true;
	                    $part_description = explode($page_break_key, $description);
	                    if(count($part_description) > $limit_char) {
	                    	$res = $part_description[0];
						} else {
	                        $res = $part_description[$limit_char - 1];
	                    }
	                } 
				} reset($page_break[$type]);
                break;
            default:
                $description = strip_tags($description);
                //$limit_char = $limit_char + (strlen($description) - strlen(strip_tags($description)));
        }
	    
	    if(!strlen($res))
	        $res = $description . " ";

	    if($limit_char) {
	    	if(!$block_limit)
	    		$res = mb_substr($res, 0, $limit_char + 1, 'UTF-8'); 

		    $res = trim(preg_replace("/\s+/", " ", (mb_substr($res, 0, mb_strrpos($res, " ", null, 'UTF-8'), 'UTF-8'))));
		    $default = $page_start_replace . $res . $str_char_limit . $page_break_replace;
		} 
    } else {
    	$res = $description;
    	$default = $res;
    }  

	return array(
		"text" => $res
		, "char_limit" => $str_char_limit
		, "content" => $default
	);
}
