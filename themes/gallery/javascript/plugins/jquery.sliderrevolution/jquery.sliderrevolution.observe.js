ff.cms.fn.sliderrevolution = function(targetid) {
    var targetid = targetid;
    if(targetid.length > 0)
        targetid = targetid + " ";

    if(jQuery(targetid + '.sliderrevolution').length > 0) {
        jQuery("#L49,#L116,#L53,#L55,.cover").hide();
        jQuery("#L16V").removeClass("medium-8");
        var i = 0;
        jQuery(targetid + ".sliderrevolution").each(function() {
            i++;
            var title = jQuery(this).attr("title");
            var absract = jQuery(this).attr("alt"); 
            jQuery(this).parent().attr("data-transition", "slidehorizontal").append('<div class="tp-caption tp-shape tp-shapewrapper tp-resizeme rs-parallaxlevel-0" id="slide-' + i + '-layer-3" data-x="[\'center\',\'center\',\'center\',\'center\']" data-hoffset="[\'0\',\'0\',\'0\',\'0\']" data-y="[\'middle\',\'middle\',\'middle\',\'middle\']" data-voffset="[\'0\',\'0\',\'0\',\'0\']" data-width="full"data-height="full"data-whitespace="normal"data-transform_idle="o:1;"data-transform_in="opacity:0;s:1500;e:Power3.easeInOut;" data-transform_out="opacity:0;s:1000;e:Power3.easeInOut;s:1000;e:Power3.easeInOut;" data-start="1000" data-basealign="slide" data-responsive_offset="on" style="z-index: 5;border-color:rgba(0, 0, 0, 1.00);"></div>');
            jQuery(this).parent().append('<div class="tp-caption tp-resizeme rs-parallaxlevel-0" id="slide-' + i + '-layer-1" data-x="[\'left\',\'left\',\'left\',\'left\']" data-hoffset="[\'50\',\'50\',\'50\',\'30\']"data-y="[\'bottom\',\'bottom\',\'bottom\',\'center\']"data-voffset="[\'12\',\'12\',\'12\',\'10\']"data-width="[\'800\',\'600\',\'600\',\'420\']"data-height="none"data-whitespace="normal"data-transform_idle="o:1;"data-transform_in="y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;s:1500;e:Power3.easeInOut;"data-transform_out="auto:auto;s:1000;e:Power3.easeInOut;"data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"data-mask_out="x:0;y:0;s:inherit;e:inherit;"data-start="1000"data-splitin="none"data-splitout="none"data-responsive_offset="on"style="z-index: 6; min-width: 600px; max-width: 600px; white-space: normal;"><div class="number-container">' + i + '</div><div class="abstract-slide">' + absract + '</div><div class="title-slide">' + title + '</div></div>');
        });
        
        
        jQuery(targetid + ".sliderrevolution").closest("UL").wrap('<div id="rev_slider_gallery_wrapper" class="rev_slider_wrapper fullwidthbanner-container" data-alias="news-galleryHomepage" style="margin:0px auto;background-color:#ffffff;padding:0px;margin-top:0px;margin-bottom:0px;"><div id="rev_slider_gallery" class="rev_slider fullwidthabanner" style="display:none;" data-version="5.0.7"></div></div>');
        ff.injectCSS("revolution-settings", "/themes/library/plugins/jquery.sliderrevolution/css/settings.css", function() {
            ff.injectCSS("revolution-layers", "/themes/library/plugins/jquery.sliderrevolution/css/layers.css", function() {
                ff.injectCSS("revolution-navigation", "/themes/library/plugins/jquery.sliderrevolution/css/navigation.css", function() {
                    
                    ff.pluginLoad("jquery.themepunch.tools", "/themes/library/plugins/jquery.sliderrevolution/jquery.themepunch.tools.min.js", function() {
                        ff.pluginLoad("jquery.themepunch.revolution", "/themes/library/plugins/jquery.sliderrevolution/jquery.themepunch.revolution.min.js", function() {
                            ff.pluginLoad("jquery.revolution.extension.navigation", "/themes/library/plugins/jquery.sliderrevolution/extensions/revolution.extension.navigation.min.js", function() {i
                                ff.pluginLoad("jquery.revolution.extension.parallax", "/themes/library/plugins/jquery.sliderrevolution/extensions/revolution.extension.parallax.min.js", function() {
                                    ff.pluginLoad("jquery.revolution.extension.slideanims", "/themes/library/plugins/jquery.sliderrevolution/extensions/revolution.extension.slideanims.min.js", function() {
                                        ff.pluginLoad("jquery.revolution.extension.layeranimation", "/themes/library/plugins/jquery.sliderrevolution/extensions/revolution.extension.layeranimation.min.js", function() {
                                            var tpj=jQuery;					
                                            var revapiGallery;
                                            tpj(document).ready(function() {
                                                if(tpj("#rev_slider_gallery").revolution == undefined){
                                                    revslider_showDoubleJqueryError("#rev_slider_gallery");
                                                }else{
                                                    revapiGallery = tpj("#rev_slider_gallery").show().revolution({
                                                            sliderType:"standard",
                                                            sliderLayout:"auto",
                                                            dottedOverlay:"none",
                                                            delay:9000,
                                                            autoHeight:"on",
                                                            navigation: {
                                                                    keyboardNavigation:"on",
                                                                    keyboard_direction: "horizontal",
                                                                    mouseScrollNavigation:"off",
                                                                    onHoverStop:"on",
                                                                    touch:{
                                                                            touchenabled:"on",
                                                                            swipe_threshold: 75,
                                                                            swipe_min_touches: 1,
                                                                            swipe_direction: "horizontal",
                                                                            drag_block_vertical: false
                                                                    }
                                                                    ,
                                                                    arrows: {
                                                                        style: "uranus",
                                                                        enable: true,
                                                                        hide_onmobile: false,
                                                                        hide_onleave: false,
                                                                        tmp: '',
                                                                        left: {
                                                                            h_align: "left",
                                                                            v_align: "center",
                                                                            h_offset: 10,
                                                                            v_offset: 0
                                                                        },
                                                                        right: {
                                                                            h_align: "right",
                                                                            v_align: "center",
                                                                            h_offset: 10,
                                                                            v_offset: 0
                                                                        }
                                                                    }
                                                            },
                                                            viewPort: {
                                                                    enable:true,
                                                                    outof:"pause",
                                                                    visible_area:"80%"
                                                            },
                                                            responsiveLevels:[1240,1024,778,480],
                                                            gridwidth:[970,768,480,480],
                                                            gridheight:[546,432,270,270],
                                                            lazyType:"none",
                                                            parallax: {
                                                                    type:"scroll",
                                                                    origo:"enterpoint",
                                                                    speed:400,
                                                                    levels:[5,10,15,20,25,30,35,40,45,50],
                                                            },
                                                            shadow:0,
                                                            spinner:"off",
                                                            stopLoop:"off",
                                                            stopAfterLoops:-1,
                                                            stopAtSlide:-1,
                                                            shuffle:"off",
                                                            hideThumbsOnMobile:"off",
                                                            hideSliderAtLimit:0,
                                                            hideCaptionAtLimit:0,
                                                            hideAllCaptionAtLilmit:0,
                                                            debugMode:false,
                                                            fallbacks: {
                                                                    simplifyAll:"off",
                                                                    nextSlideOnWindowFocus:"off",
                                                                    disableFocusListener:false,
                                                            }
                                                    });
						}
                                            });
					});  
                                    });
                                });
                            });
                        });
                    });
                });
            });
        });
    } 
};