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
function preposition($tipo, $keyword)
{
	$pattern_ad = "abcdfglmnpqrstvzwjx";
    $keyword = trim($keyword);
	$keyword = ltrim($keyword, "{");
	$keyword = rtrim($keyword, "}");

	if($tipo == "a")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($keyword),1)))
		{
			$preposizione = "ad ";
		}
		else
		{
			$preposizione = "a ";
		}
	}

	if($tipo == "da")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($keyword),1)))
		{
			$preposizione = "d' ";
		}
		else
		{
			$preposizione = "da ";

			if(substr($keyword,-strlen($keyword),1) == "l" || substr($keyword,-strlen($keyword),1) == "L")
			{
				if(substr($keyword,-strlen($keyword),2) == "l'" || substr($keyword,-strlen($keyword),2) == "L'")
				{
					$preposizione = "del";	
				}
				else
				{
					$preposizione = "da ";	
				}			
			}			
		}
	}

	if($tipo == "di")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
		{
			$preposizione = "d' ";
		}
		else
		{
			$preposizione = "di ";

			if(substr($keyword,-strlen($keyword),1) == "l" || substr($keyword,-strlen($keyword),1) == "L")
			{
				if(substr($keyword,-strlen($keyword),2) == "l'" || substr($keyword,-strlen($keyword),2) == "L'")
				{
					$preposizione = "del";	
				}
				else
				{
					$preposizione = "di ";	
				}			
			}				
		}
	}

	if($tipo == "ai")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
		{
			$preposizione = "agli ";
		}
		else
		{
			$preposizione = "ai ";				
		}
	}

	if($tipo == "dei")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
		{
			$preposizione = "degli ";
		}
		else
		{
			$preposizione = "dei ";				
		}
	}

	if($tipo == "i")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
		{
			$preposizione = "gli ";
		}
		else
		{
			$preposizione = "i ";				
		}
	}

	if($tipo == "dai")
	{
		if(!stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
		{
			$preposizione = "dagli ";
		}
		else
		{
			$preposizione = "dai ";				
		}
	}
    if($tipo == "su")
    {
        if(stripos($pattern_ad, substr($keyword,-strlen($zona),1)))
        {
            $preposizione = "sui ";
        }
        else
        {
            $preposizione = "su ";                
            if(substr($keyword,-strlen($keyword),1) == "o" || substr($keyword,-strlen($keyword),1) == "O")
            {
                $preposizione = "sullo ";                
            } elseif(substr($keyword,-strlen($keyword),1) == "a" || substr($keyword,-strlen($keyword),1) == "A") {
                $preposizione = "sulla ";     
            }            
        }
    }
    
    if(!$preposizione)
    	$preposizione = $tipo . " ";
    
	return $preposizione . $keyword;
}
