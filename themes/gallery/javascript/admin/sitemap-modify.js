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
		    : jQuery(jQuery(that).closest("#SeoModifyField").find(".check-keywords INPUT, .check-keywords TEXTAREA"))
		);
		
		ff.cms.seo.check("keywords-consistency", jQuery(that).closest(".ffRecord").nextAll(".spellcheck"), seoPage, {"keyCompareFrom" : keyCompare});
	}

	 jQuery(function() {
	    jQuery("#SeoModifyField DIV.helper").hover(function() {
	        jQuery(this).find(".helper-content").removeClass("hidden");
	    }, function() {
	        jQuery(this).find(".helper-content").addClass("hidden");
	    });
	    jQuery("#SeoModifyField a.helper").click(function() {
	        if(jQuery(this).find(".helper-content").hasClass("hidden")) {
	            jQuery(this).find(".helper-content").removeClass("hidden");
	        } else {
	            jQuery(this).find(".helper-content").addClass("hidden");
	        }
	    });

	    $("#SeoModifyField a[data-toggle=tab]").on("shown.bs.tab", function (e) {
	      var target = $(e.target).attr("href"); // activated tab

	      jQuery(target + " .page-meta INPUT, target .page-meta TEXTAREA").keyup();
	    });  
		
		/*$("#SeoModifyField .tabs[data-tab]").on("toggled", function (event, tab) {
			console.log(tab);
		});*/
		
		jQuery("#SeoModifyField .page-meta INPUT, #SeoModifyField .page-meta TEXTAREA").on("keyup", function() {
			var that = this;
			var lang = jQuery("#SeoModifyField li.active").text();
			lang = lang.trim().substring(0, 3).toLowerCase();

			if(jQuery(that).hasClass("loaded"))
				keyWordsCheck(that, lang);
	        
		});

	    jQuery(".page-meta INPUT, .page-meta TEXTAREA").keyup().addClass("loaded");
	    jQuery(".page-meta INPUT, .page-meta TEXTAREA").first().keyup();
		
	});
});