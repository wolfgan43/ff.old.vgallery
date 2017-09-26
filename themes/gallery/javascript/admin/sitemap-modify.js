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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

ff.pluginAddInit("ff.cms.seo", function() {
	function keyWordsCheck(that, lang) {
	    if(!lang)
      		lang = ff.language;
		
		var container_class = (jQuery("#SeoModifyField").length
								? "#SeoModifyField"
								: "#SeoModify"
							);

		var keyCompare = {};
		if(jQuery(that).closest(".seo-page").find(".smart-url INPUT").length)
		    keyCompare["Smart Url"] = jQuery(that).closest(".seo-page").find(".smart-url INPUT").val().replace(/-/g, " ");
		if(jQuery(that).closest(".seo-page").find(".meta-title INPUT").length)
		    keyCompare["Title"] = jQuery(that).closest(".seo-page").find(".meta-title INPUT").val();
		if(jQuery(that).closest(".seo-page").find(".meta-desc TEXTAREA").length)
		    keyCompare["Meta Desc"] = jQuery(that).closest(".seo-page").find(".meta-desc TEXTAREA").val();
		if(jQuery(that).closest(".seo-page").find(".meta-keywords INPUT").length)
		    keyCompare["Meta KeyWords"] = jQuery(that).closest(".seo-page").find(".meta-keywords INPUT").val();
		if(jQuery(that).closest(".seo-page").find(".header INPUT").length)
		    keyCompare["H1"] = jQuery(that).closest(".seo-page").find(".header INPUT").val();
		
		if(lang && ff.cms.libs.stopWords) {
			if(jQuery.isFunction(ff.cms.libs.stopWords[lang])) {
		        ff.cms.seo.stopWords = ff.cms.libs.stopWords[lang];
			} else {
		        ff.cms.libs.stopWords[lang] = ff.cms.seo.stopWords;
			}
		}
		var seoPage = (jQuery(that).closest(".seo-page").length
		    ? jQuery(jQuery(that).closest(".seo-page").find(".check-keywords INPUT, .check-keywords TEXTAREA"))
		    : jQuery(jQuery(that).closest(container_class).find(".check-keywords INPUT, .check-keywords TEXTAREA"))
		);
		
		ff.cms.seo.check("keywords-consistency", jQuery(that).closest(".ffRecord").nextAll(".spellcheck"), seoPage, {"keyCompareFrom" : keyCompare});
	}

	function init(params, data) {
		if (params && params.component !== "SeoModify")
			return;
	
	  /*  jQuery("DIV.helper", ).hover(function() {
	        jQuery(this).find(".helper-content").removeClass("hidden");
	    }, function() {
	        jQuery(this).find(".helper-content").addClass("hidden");
	    });*/
		var $container = (jQuery("#SeoModifyField").length
						? jQuery("#SeoModifyField")
						: jQuery("#SeoModify")
					);
		
	    jQuery("DIV.helper", $container).click(function() {
	        if(jQuery(this).find(".helper-content").hasClass("hidden")) {
	            jQuery(this).find(".helper-content").removeClass("hidden");
	        } else {
	            jQuery(this).find(".helper-content").addClass("hidden");
	        }
	    });

	    jQuery("a[data-toggle=tab]", $container).on("shown.bs.tab", function (e) {
	      var target = jQuery(e.target).attr("href"); // activated tab

	      jQuery(target + " .page-meta INPUT, " + target + " .page-meta TEXTAREA").first().keyup();
	    });  
		
		/*$(".tabs[data-tab]", $container).on("toggled", function (event, tab) {
			console.log(tab);
		});*/
		
		jQuery(".page-meta INPUT, .page-meta TEXTAREA", $container).on("keyup", function() {
			var that = this;
			var lang = jQuery("li.active", $container).text();
			lang = lang.trim().substring(0, 3).toLowerCase();

			keyWordsCheck(that, lang);
		});
		jQuery("a[data-toggle=tab]", $container).first().trigger("shown.bs.tab");
	    //jQuery(".page-meta INPUT, .page-meta TEXTAREA").first().keyup();
	    //jQuery(".page-meta INPUT, .page-meta TEXTAREA").first().keyup();
	}

	ff.pluginAddInitLoad("ff.ajax", function () {
		ff.ajax.addEvent({
			"event_name" : "onUpdatedContent"
			, "func_name" :  init
		});
	});
	
	 jQuery(function() {
		 init();
	});
});

