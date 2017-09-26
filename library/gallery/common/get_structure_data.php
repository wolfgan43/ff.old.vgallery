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
function get_structure_data($data, $structure, $sep = "-", $db = null) {
    if(is_array($structure) && count($structure)) {
        foreach($structure AS $structure_value) {
            if($sep === false) {
                $res[] = $data[$structure_value];
            } else {
                if(strlen($res))
                    $res .= $sep;
                
                $res .= $data[$structure_value];
            }
        }
    } else {
        if($sep === false) { 
            $res[] = $data[$structure];
        } else {
            if(strlen($res))
                $res .= $sep;
            
            $res .= $data[$structure];
        }
    }
    return normalize_from_db($res, null, $db);
}

function normalize_from_db($source, $url = null, $db = null) {
	$convmap = array(0x0, 0xFFFF, 0, 0xFFFF);
	$source = mb_decode_numericentity($source, $convmap, "UTF-8");

	$source = str_replace("\\\"", "\"", $source);

	if(DB_CHARACTER_SET != "utf8") {
		$source = utf2iso($res, null, $db);
	}

	if($db !== null) {
		static $char_map;
		
		if(!is_array($map) && !count($map))
			$char_map = character_set_map_decode($db);

		$source = strtr($source, $char_map["encode"], $char_map["decode"]);
	}	
	
	return $source;
}

function character_set_map_decode($db) {
	$res = array();

	$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "charset_decode ";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$res["encode"][] = $db->getField("code", "Text", true);
			$res["decode"][] = $db->getField("value", "Text", true);
		} while($db->nextRecord());
		
	}
	return $res;	
}

# PARSING UNICODE STRING
function utf2iso($source, $url = null, $db = null) {
	static $map;

	if(!is_array($map) && !count($map))
		$map = create_map($url);

	
	$pos = 0;
	$len = strlen ($source);
	$encodedString = '';
   
	while ($pos < $len) {
	    $is_ascii = false;
	    $asciiPos = ord (substr ($source, $pos, 1));
	    if(($asciiPos >= 240) && ($asciiPos <= 255)) {
	        // 4 chars representing one unicode character
	        $thisLetter = substr ($source, $pos, 4);
	        $thisLetterOrd = uniord($thisLetter);
	        $pos += 4;
	    }
	    else if(($asciiPos >= 224) && ($asciiPos <= 239)) {
	        // 3 chars representing one unicode character
	        $thisLetter = substr ($source, $pos, 3);
	        $thisLetterOrd = uniord($thisLetter);
	        $pos += 3;
	    }
	    else if(($asciiPos >= 192) && ($asciiPos <= 223)) {
	        // 2 chars representing one unicode character
	        $thisLetter = substr ($source, $pos, 2);
	        $thisLetterOrd = uniord($thisLetter);
	        $pos += 2;
	    }
	    else{
	        // 1 char (lower ascii)
	        $thisLetter = substr ($source, $pos, 1);
	        $thisLetterOrd = uniord($thisLetter);
	        $pos += 1;
	        $encodedString .= $thisLetterOrd;
	        $is_ascii = true;
	    }
	    if(!$is_ascii){
	        $hex = sprintf("%X", $thisLetterOrd);
	        if(strlen($hex)<4) for($t=strlen($hex);$t<4;$t++)$hex = "0".$hex;
	        $hex = "0x".$hex;
			if(isset($map["utf2iso"][$hex])) {
			    $hex = $map["utf2iso"][$hex];
			    $hex = str_replace('0x','',$hex);
			    $dec = hexdec($hex);
			    $encodedString .= sprintf("%c", $dec);
			} else {
				if($db === null) {
			    	$encodedString .= $thisLetter;
				} else {
					$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "charset_decode WHERE code = " . $db->toSql($thisLetter);
					$db->query($sSQL);
					if($db->nextRecord()) {
						$value = $db->getField("value", "Text", true);
						if(strlen($value))
							$encodedString .= $value;
						else
							$encodedString .= $thisLetter;
					} else {
						$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "charset_decode (ID, code, value) VALUES( NULL, " . $db->toSql($thisLetter) . ", '')";
						$db->execute($sSQL);

						$encodedString .= $thisLetter;
					}
				}
			}
	    }
	}
	
	return $encodedString;
} 

# UNICODE MAPPING TABLE PARSING
function create_map($url = null){
	if($url === null)
		$url = "http://www.unicode.org/Public/MAPPINGS/ISO8859/8859-1.TXT";

	$fl = @(file($url)) OR (die("cannot open file : $url\n"));
	for ($i=0; $i<count($fl); $i++){
	    if($fl[$i][0] != '#' && trim($fl[$i])){
	        list($iso, $uni, $s, $desc) = split("\t",$fl[$i]);
	        $map["iso2utf"][$iso] = $uni;
	        $map["utf2iso"][$uni] = $iso;
	    }
	}
	return $map;
}

# FINDING UNICODE LETTER'S DECIMAL ASCII VALUE
function uniord($c){
	$ud = 0;
	if (ord($c{0})>=0 && ord($c{0})<=127)   $ud = $c{0};
	if (ord($c{0})>=192 && ord($c{0})<=223) $ud = (ord($c{0})-192)*64 + (ord($c{1})-128);
	if (ord($c{0})>=224 && ord($c{0})<=239) $ud = (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
	if (ord($c{0})>=240 && ord($c{0})<=247) $ud = (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
	if (ord($c{0})>=248 && ord($c{0})<=251) $ud = (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
	if (ord($c{0})>=252 && ord($c{0})<=253) $ud = (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
	if (ord($c{0})>=254 && ord($c{0})<=255) $ud = false; //error
	return $ud;
}
