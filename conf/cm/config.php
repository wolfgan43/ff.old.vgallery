<?php
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
  
  if(!defined("DISABLE_CACHE")) {
	  /*if(
  		strpos($_SERVER["PATH_INFO"], "/admin") !== 0
  		&& strpos($_SERVER["PATH_INFO"], "/restricted") !== 0
  		&& strpos($_SERVER["PATH_INFO"],  "/manage") !== 0
	  ) {*/
		  define("CM_JSCACHE_DEFERLOADING", true);
		  define("CM_CSSCACHE_DEFERLOADING", is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/css/above-the-fold.css")
		  										? true
		  										: false
		  									);    
	  //}
	  define("CM_CSSCACHE_RENDER_PATH", true);
	  //define("CM_ENABLE_MEM_CACHING", false);			/* bufferizza i settaggi del CM */
	  define("CM_CSSCACHE_MINIFIER", "minify");
	  define("CM_JSCACHE_MINIFIER", "minify");

	  define("CM_CACHE_PURGE_JS", true);
	  define("CM_CACHE_IMG_SET_DIMENSION", true);
	  define("CM_CACHE_CSS_INLINE_TO_STYLE", true);
  }

  define("CM_CACHE_PATH_CONVERT_SHOWFILES", true);
  define("CM_CACHE_IMG_LAZY_LOAD", true);

  //define("CM_SHOWFILES_MODULES", true);
  define("CM_SHOWFILES_OPTIMIZE", true);

  define("CM_CACHE_STORAGE_SAVING_MODE", 3);
  define("CM_CSSCACHE_SHOWPATH", FF_SITE_PATH . "/asset/css");
  define("CM_JSCACHE_SHOWPATH", FF_SITE_PATH . "/asset/js");
  //define("CM_MEDIACACHE_SHOWPATH", CM_SHOWFILES);
