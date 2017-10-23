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
 * @subpackage config
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
  define("CM_DEFAULT_THEME", "responsive");
 
  define("CM_SHOWFILES_ENABLE_DEBUG", false);
  define("CM_SHOWFILES_FORCE_PATH", true);
  define("CM_SHOWFILES_SKIP_DB", true);
  define("CM_SHOWFILES_THEME", "gallery");
  define("CM_SHOWFILES_ICON_PATH", "/images/icons/default/thumb");
  define("CM_SHOWFILES_ENABLE_GZIP", true);
  define("CM_SHOWFILES_EXTEND", true);
  define("CM_SHOWFILES_THUMB_IN_CACHE", true);
  
  define("CM_PAGECACHE_KEEP_ALIVE", true);
  define("CM_MULTIDOMAIN_ROUTING", false);
  
  if(!defined("DISABLE_CACHE")) {
	  /*if(
  		strpos($_SERVER["PATH_INFO"], "/admin") !== 0
  		&& strpos($_SERVER["PATH_INFO"], "/restricted") !== 0
  		&& strpos($_SERVER["PATH_INFO"],  "/manage") !== 0
	  ) {*/
		  define("CM_JSCACHE_DEFERLOADING", true);
		  define("CM_CSSCACHE_DEFERLOADING", true);
		 /* define("CM_CSSCACHE_DEFERLOADING", is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/css/above-the-fold.css")
		  										? true
		  										: false
		  									);   */ 
	  //}
	  define("CM_CSSCACHE_RENDER_PATH", true);
	  //define("CM_ENABLE_MEM_CACHING", false);			/* bufferizza i settaggi del CM */
	  define("CM_CSSCACHE_MINIFIER", "minify");
	  define("CM_JSCACHE_MINIFIER", "minify");

	  define("CM_CACHE_PURGE_JS", true);
	  define("CM_CACHE_IMG_SET_DIMENSION", true);
	  define("CM_CACHE_PATH_CONVERT_SHOWFILES", true);
	  define("CM_CACHE_CSS_INLINE_TO_STYLE", true); 
  }
  
  define("CM_CACHE_IMG_LAZY_LOAD", true);

  //define("CM_SHOWFILES_MODULES", true);
  //define("CM_SHOWFILES_OPTIMIZE", true);
  
  define("CM_CACHE_STORAGE_SAVING_MODE", 3);
  define("CM_CSSCACHE_SHOWPATH", FF_SITE_PATH . "/asset/css");
  define("CM_JSCACHE_SHOWPATH", FF_SITE_PATH . "/asset/js");
  //define("CM_MEDIACACHE_SHOWPATH", CM_SHOWFILES);