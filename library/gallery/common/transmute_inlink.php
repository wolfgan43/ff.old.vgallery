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
	function transmute_inlink($description, $alias = null, $email_alias = null) {
	//IMPORTANTE Da mettere a posto la preg. 
	// Il concetto e:
	// se non ce un tag hfref o un javascript window.open e il link si costruisce con il seguente esempio:
	// protocol://domain/path?query_string
	// Allora trasforma il link senno no.
	// stessa cosa per la mail.
	$escaped_url = $_SERVER["HTTP_HOST"] . FF_SITE_PATH;     
	$escaped_url = str_replace(".", "\.", $escaped_url);
	$escaped_url = str_replace("-", "\-", $escaped_url);

	    if(!global_settings("TRANSMUTE_ALIAS") || $alias === null) {
	/*
	        $description = preg_replace( '|\w{3,10}://[\w\.\-_]+(:\d+)?[^\s\"\'<>\(\)\{\}]*|',  
	                    '<a href="\\0" target="_blank">' . '\\0' . '</a>', $description);
	*/
	        $description = preg_replace( '%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i',  
	                    '<a href="http://\\0" target="_blank" rel="nofollow">' . '\\0' . '</a>', $description);
	    } else {
	        $old_description = $description;
	/*
	        $description = preg_replace( '|\w{3,10}://[\w\.\-_]+(:\d+)?[^\s\"\'<>\(\)\{\}]*|', 
	                   '<a href="\\0" target="_blank">' . "[--alias--]" . '</a>', $description);
	*/
	        $description = preg_replace( '%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i', 
	                   '<a href="http://\\0" target="_blank" rel="nofollow">' . "[--alias--]" . '</a>', $description);

	        if($old_description != $description) {
	            $description = str_replace("[--alias--]", ffTemplate::_get_word_by_code($alias), $description);
	        }
	    }

		$description = str_replace("http://http://", "http://", $description);

	    if(!global_settings("TRANSMUTE_ALIAS") || $email_alias === null) {
	        /*$description = eregi_replace('[-a-z0-9!#$%&\'*+/=?^_`{|}~.]+@([.]?[a-zA-Z0-9_/-])*',
	           '<a href="mailto:\\0">' . '\\0' . '</a>', $description);*/
	        $description = preg_replace('/([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})/i','<a href="mailto:$1@$2">$1@$2</a>', $description);
	    } else {
	        $old_description = $description;
	        /*$description = eregi_replace('[-a-z0-9!#$%&\'*+/=?^_`{|}~.]+@([.]?[a-zA-Z0-9_/-])*',
	           '<a href="mailto:\\0">' . "[--mailalias--]" . '</a>', $description);*/
	        $description = preg_replace('/([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})/i','<a href="mailto:$1@$2">[--mailalias--]</a>', $description);
	        if($old_description != $description) {
	            $description = str_replace("[--mailalias--]", ffTemplate::_get_word_by_code($email_alias), $description);
	        }
	    }
	    
	    //javascript:void(window.open(''))
	                                
	// Da rivedere le preg per ora sto pezzo di codice provvisorio:
	   // $description = ereg_replace("(^| |.)(www([.]?[a-zA-Z0-9_/-])*)", "\\1 http://\\2", $description );
	   // $description = str_replace("http:// http://", "http://", $description );

	    return $description;
	}
