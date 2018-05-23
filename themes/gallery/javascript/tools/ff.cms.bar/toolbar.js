if (!ff.cms) ff.cms = {};
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
ff.cms.block = (function () {
    var targetID = '.block[id!=""]';
    var $bars = undefined;
    var cache = {};
    var libs = ["jquery.plugins.helperborder"];

    var load = function() {
        $blocks = jQuery(targetID);
        $blocks.each(function() {
            var $source = jQuery(this);
            var $target = $source;
            var blockID = $source.attr("id");

            if($source.attr("rel")) {
                if($source.find("." + $source.attr("rel")).is("div")) {
                    $target = $source.find("." + $source.attr("rel"));
                } else {
                    $target = $source.children(":first");
                }
            }

            if(!$target.height() && !$target.children()) {
                var ctxW = $target.width() || 100;
                var ctxH = $target.height() || 100;

                $target.css({"overflow" : "hidden", "width" : ctxW + "px", "height" : ctxH + "px"});
                if(document.getCSSCanvasContext) {
                    $target.css({"background": "-webkit-canvas(squares)"});

                    var ctx = document.getCSSCanvasContext("2d", "squares", ctxW, ctxH);
                } else {
                    if(!jQuery("#ctx-empty").length) {
                        jQuery("body").append('<canvas id="ctx-empty" width="' + ctxW + '" height="' + ctxH + '"></canvas>');
                    }
                    $target.css({"background": "-moz-element(#ctx-empty)"});

                    var ctx = document.getElementById("ctx-empty").getContext("2d");
                }

                ctx.rect(0,0,ctxW,ctxH);
                ctx.strokeStyle = '#d3d3d3';
                ctx.stroke();
                ctx.moveTo(0,0);
                ctx.lineTo(ctxW,ctxH);
                ctx.strokeStyle = '#d3d3d3';
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(ctxW,0);
                ctx.lineTo(0 ,ctxH);
                ctx.strokeStyle = '#d3d3d3';
                ctx.stroke();
            }

            $target.helperBorder({
                container : "body",
                guide : {
                    enable : true,
                    elemId : "#hb-outline-guide",
                    exclude : [],
                    showInner : true,
                    innerCallback : undefined,
                    margin : 6
                },
                guideSelected : {
                    enable : true,
                    elemId : "#hb-outline-selected",
                    exclude : [],
                    timer : 800,
                    useDrag : false,
                    useResize : false,
                    showInner : true,
                    margin : 3,
                    innerCallback : function(elem, toolbarContainer) {
                        var link = $source.data("admin");
                        if($source.hasClass("draft")
                            || $source.hasClass("file")
                        )
                            link = ff.site_path + "/admin/block/" + blockID;

                        //jQuery(toolbarContainer).html("");
                        if(cache[blockID] !== undefined) {
                            jQuery(toolbarContainer).html(cache[blockID]).children().show();
                        } else {
                            cache[blockID] = true;
                            jQuery.get(link, function(block) {
                                cache[blockID] = block;
                                jQuery(toolbarContainer).html(cache[blockID]).children().show();
                            });
                        }
                    }
                }
            });

            jQuery('[data-admin!=""]', $target).hover(function() {
                var $item = jQuery(this);
                var link = $item.data("admin");
                if($item && link) {
                    if (!$item.css("position") || $item.css("position") == "static")
                        $item.addClass(".vg-popup-visible");

                    if (cache[blockID + link] !== undefined) {
                        $item.prepend(cache[blockID + link]);
                    } else {
                        jQuery.get(jQuery(this).data("admin"), function (item) {
                            cache[blockID + link] = item;
                            $item.prepend(cache[blockID + link]);
                        });
                    }
                }
            }, function() {
                var $item = jQuery(this);

                $item.removeClass(".vg-popup-visible");
                jQuery(".vg-popup", $item).remove();
            });
        });
    }
    var that = { /* publics*/
        __init : false
        , "init" : function(params) {
            that.__init = true;

            load();
        }
    };

    jQuery(function() {
        that.init();
    });

    return that;
})();