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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if (!ff.cms) ff.cms = {};
ff.cms.bar = (function () {
    var targetID = "#adminPanel";
    var zIndex = 90;
    var $bar = undefined;
    var load = function(skip) {
        skip = skip || {};

    $bar = jQuery(targetID);
    $bar.css("z-index", zIndex);

    ui.menu();
    ui.toggle(!skip.toggle);
    ui.layout(!skip.layout);
    ui.info(!skip.info);

    if(ff.cms.editor && !skip.editor)
        ff.cms.editor.init();

    if(ff.cms.seo && !skip.seo)
        ff.cms.seo.init();

    }
    var ui = {
        "menu" : function() {
            jQuery("a:not(.stay-open)", $bar).on("click", function() {
                jQuery(".submenu", $bar).hide();
            });

            //ff.load("jquery.plugins.hoverintent", function() {
                jQuery("> ul:not(submenu) > li", $bar).hoverIntent({
                    sensitivity: 3,
                    over : function(){
                        if(!jQuery(this).children(".submenu").is(":visible")) {
                            jQuery(this).closest("ul").find(".submenu").hide();
                            jQuery(this).find("li.layout").each(function() {
                                var layoutRel = [];
                                if(jQuery("#L" + jQuery(this).attr("rel")).length) {
                                    layoutRel.push("L" + jQuery(this).attr("rel"));
                                }
                                if(jQuery("#L" + jQuery(this).attr("rel") + "T").length) {
                                    layoutRel.push("L" + jQuery(this).attr("rel") + "T");
                                }
                                if(jQuery("#L" + jQuery(this).attr("rel") + "V").length) {
                                    layoutRel.push("L" + jQuery(this).attr("rel") + "V");
                                }

                                if(layoutRel.length) {
                                    jQuery(this).attr("data-rel", layoutRel.join(","));
                                    jQuery(this).removeClass("notvisible");
                                    jQuery(this).removeClass("hidden");
                                } else if(0 && !jQuery(this).hasClass("ui-state-disabled")) {
                                    jQuery(this).removeAttr("data-rel");
                                    jQuery(this).addClass("ui-state-disabled");
                                    jQuery(this).addClass("notvisible");
                                    jQuery(this).addClass("hidden");
                                }
                            });
                            jQuery(this).children(".submenu").stop(true,true).slideDown();
                        }
                    },
                    out: function() {
                    }
                });
            //});

            jQuery("li.thumb-section ul.submenu", $bar).each(function() {
                var that = this;
                jQuery(this).children("li").each(function() {
                    if(!jQuery(this).hasClass("notvisible")) {
                        var layoutRel = [];
                        if(jQuery("#L" + jQuery(this).attr("rel")).length) {
                            layoutRel.push("L" + jQuery(this).attr("rel"));
                        }
                        if(jQuery("#L" + jQuery(this).attr("rel") + "T").length) {
                            layoutRel.push("L" + jQuery(this).attr("rel") + "T");
                        }
                        if(jQuery("#L" + jQuery(this).attr("rel") + "V").length) {
                            layoutRel.push("L" + jQuery(this).attr("rel") + "V");
                        }

                        if(layoutRel.length) {
                            jQuery(this).attr("data-rel", layoutRel.join(","));
                        } else if(0 && !jQuery(this).hasClass("ui-state-disabled")) {
                            jQuery(this).addClass("ui-state-disabled");
                        }
                    }
                });

                if(jQuery(this).children("li:not(.ui-state-disabled)").length > 1) {
                    jQuery(this).sortable({
                        items: "li:not(.ui-state-disabled)"
                        , placeholder: "ui-state-highlight"
                        , start: function(event, ui) {
                            if(jQuery(ui.item).attr("data-rel")) {
                                jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).helperBorder("borderGuide", jQuery(".ui-state-highlight").css("background-color"));
                                jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).css({
                                    "opacity": 0.5
                                });

                            }
                            ui.item.data('start', ui.item.index());
                        }
                        , change : function(event, ui) {

                            var swapElem = undefined;
                            if(ui.placeholder.index() > ui.item.data('start')) {
                                swapElem = ui.placeholder.prevAll(":not(.ui-state-disabled)");
                                jQuery(jQuery("#" + ui.item.data("rel").replace(",", ",#"))).insertAfter(jQuery("#" + swapElem.attr("data-rel").replace(",", ",#")));
                            } else {
                                swapElem = ui.placeholder.nextAll(":not(.ui-state-disabled)");
                                jQuery(jQuery("#" + ui.item.data("rel").replace(",", ",#"))).insertBefore(jQuery("#" + swapElem.attr("data-rel").replace(",", ",#")));
                            }
                            var dataRelOffset = jQuery("#" + ui.item.data("rel").replace(",", ",#")).offset();

                            jQuery(document).scrollTop(dataRelOffset.top - (jQuery(window).height() * 0.20));
                            jQuery("#" + ui.item.data("rel").replace(",", ",#")).helperBorder("showGuide");

                            ui.item.data('start', ui.placeholder.index());
                        }
                        , stop : function(event, ui) {
                            if(jQuery(ui.item).attr("data-rel")) {
                                jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).helperBorder("borderGuide");
                                jQuery("#" + jQuery(ui.item).attr("data-rel").replace(",", ",#")).css({
                                    "opacity" : 1
                                });
                            }

                            var location = jQuery(ui.item).closest("li.thumb-section").attr("rel");
                            var positions = [];
                            jQuery(ui.item).closest("ul").children("li.layout").each(function() {
                                positions.push(jQuery(this).attr("rel"));
                            });

                            jQuery.post("/srv/sort/layout?location=" + location + "&positions=" + positions.join(), function(data) {
                            });
                        }
                    });
                }
            });
            jQuery("ul.submenu", $bar).disableSelection();
        },
        "toggle" : function(enable) {
            var timer = "";
            if(enable) {
                jQuery(".hide-toggle", $bar).click(function() {
                    var elem = this;
                    //ff.load("jquery.plugins.cookie", function() {
                        if(document.cookie.indexOf("cms-toolbar-fixed") >= 0) {
                            jQuery(elem).removeClass("pressed");
                            jQuery.cookie("cms-toolbar-fixed", null);
                        } else {
                            jQuery(elem).addClass("pressed");
                            jQuery.cookie("cms-toolbar-fixed", true);
                        }
                    //});
                });
                $bar.hover(function() {
                    if(timer) {
                        clearTimeout(timer);
                        timer = "";
                    }

                    that.toggle(true);
                }, function() {
                    timer = setTimeout(function() {ff.cms.bar.toggle(false); }, 1000);
                });

                if(document.cookie.indexOf("cms-toolbar-fixed") < 0) {
                    that.toggle(false, true);
                } else {
                    jQuery(".hide-toggle", $bar).addClass("pressed");
                }
            } else {
                that.toggle(false, true);

                jQuery(".hide-toggle", $bar).hide();
            }
        },
        "layout" : function(enable) {
            if(enable) {
                jQuery("li.thumb-section .display-layout", $bar).each(function() {
                    var layoutLi = jQuery(this).closest("ul").find("li.layout");
                    if(!(layoutLi.length && layoutLi.hasClass("hidden"))) {
                        jQuery(this).hide();
                    }
                });
                jQuery("li.thumb-section .display-layout", $bar).click(function() {
                    var oldClass = jQuery(this).attr("class").replace("display-layout stay-open", "");
                    var newClass = jQuery(this).attr("rel");

                    jQuery(this).attr("class", "display-layout stay-open " + newClass).attr("rel", oldClass);

                    if(jQuery(this).closest("li").hasClass("hide-layout")) {
                        jQuery(this).closest("ul").find("li.layout").removeClass("hidden");
                        jQuery(this).closest("li").removeClass("hide-layout");
                    } else {
                        jQuery(this).closest("ul").find("li.layout.notvisible").addClass("hidden");
                        jQuery(this).closest("li").addClass("hide-layout");
                    }
                    return false;
                });

                jQuery("li.layout", $bar).hover(function(){
                    if(jQuery(this).attr("data-rel")) {
                        jQuery("#" + jQuery(this).attr("data-rel").replace(",", ",#")).helperBorder("showGuide");
                    }
                }, function() {
                    if(jQuery(this).attr("data-rel")) {
                        jQuery("#" + jQuery(this).attr("data-rel").replace(",", ",#")).helperBorder("hideGuide");
                    }
                });
            } else {
                jQuery("li.thumb-section .display-layout", $bar).hide();
            }
        },
        "info" : function(enable) {
            if(enable) {
                jQuery("li.layout a.block-info", $bar).click(function() {
                    var oldClass = jQuery(this).attr("class").replace("block-info stay-open", "");
                    var newClass = jQuery(this).attr("rel");

                    jQuery(this).attr("class", "block-info stay-open " + newClass).attr("rel", oldClass);

                    if(jQuery(this).closest("li").find("table").hasClass("info-hide")) {
                        jQuery(this).closest("ul").find("table:not('.info-hide')").parent().children("a.block-info").click();
                        jQuery(this).closest("li").find("table").removeClass("info-hide");
                        if(jQuery(this).closest("li").attr("data-rel")) {
                            var dataRelOffset = jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).offset();

                            jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).helperBorder("showGuideSelected");
                            jQuery(this).parent().find("SPAN.block-top").text(Math.round(dataRelOffset.top));
                            jQuery(this).parent().find("SPAN.block-left").text(Math.round(dataRelOffset.left));
                            jQuery(this).parent().find("SPAN.block-width").text(Math.round(jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).width()));
                            jQuery(this).parent().find("SPAN.block-height").text(Math.round(jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).height()));


                            jQuery(document).scrollTop(dataRelOffset.top - (jQuery(window).height() * 0.20));

                        } else {
                            jQuery(document).helperBorder("hide");
                        }

                        if(jQuery(this).closest("ul.submenu").hasClass("ui-sortable"))
                            jQuery(this).closest("ul.submenu").sortable( "disable" );
                    } else {
                        jQuery(this).closest("li").find("table").addClass("info-hide");
                        if(jQuery(this).closest("li").attr("data-rel")) {
                            jQuery("#" + jQuery(this).closest("li").attr("data-rel").replace(",", ",#")).helperBorder("hideGuideSelected");
                        }

                        if(jQuery(this).closest("ul.submenu").hasClass("ui-sortable"))
                            jQuery(this).closest("ul.submenu").sortable( "enable" );
                    }
                    return false;
                });
            } else {
                jQuery("li.layout a.block-info", $bar).hide();
            }
        }
    }
    var that = { /* publics*/
        __init : false
        , "init" : function(params) {
            that.__init = true;

            jQuery("*").each(function() {
                var current = parseInt($(this).css("z-index"), 10);
                if(current < 2000 && zIndex < current) zIndex = current;
            });
            zIndex = zIndex + 10;
            load();
        }
        , "toggle" : function(display, noAnimate) {
            if(document.cookie.indexOf("cms-toolbar-fixed") < 0) {
                if(display === undefined)
                    display = !jQuery(" > ul", $bar).is(":visible");

                jQuery(" > ul:not(submenu) > li > ul.submenu", $bar).hide();
                if(display) {
                    jQuery(" > ul", $bar).show();
                    jQuery(" > .right", $bar).show();
                    $bar.css({
                        "height": "32px"
                        , "background" : "#222"
                        , "border" : ""
                    });
                } else {
                    if(noAnimate) {
                        jQuery(" > ul", $bar).hide();
                        jQuery(" > .right ", $bar).hide();
                        $bar.css({
                            "height" : "7px"
                            , "background" : "transparent"
                            , "border-top" : "1px solid rgba(0,127,255,0.80)"
                        });
                    } else {
                        $bar.animate({
                            "height" : "0"
                        },300, function() {
                            jQuery(" > ul", $bar).hide();
                            jQuery(" > .right ", $bar).hide();
                            $bar.css({
                                "height" : "7px"
                                , "background" : "transparent"
                                , "border-top" : "1px solid rgba(0,127,255,0.80)"
                            });
                        });
                    }
                }
            }

        }
    };

    jQuery(function() {
        that.init();
    });

    return that;
})();