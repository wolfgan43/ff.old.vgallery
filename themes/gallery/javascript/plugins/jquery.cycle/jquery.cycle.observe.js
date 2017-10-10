ff.cms.fn.cycle = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	 
	ff.pluginLoad("jquery.fn.cycle", "/themes/library/plugins/jquery.cycle/jquery.cycle.js", function() {
		jQuery(targetid + '.cycle').closest("DIV").css("height", jQuery(targetid + '.cycle').closest("DIV").children("IMG:first").height() + "px");
		jQuery(targetid + '.cycle').closest("DIV").cycle ({
			fx:           'fade', /* name of transition effect (or comma separated names, ex: fade,scrollUp,shuffle) */
			timeout:       4000,  /* milliseconds between slide transitions (0 to disable auto advance)  */
			timeoutFn:     null,  /* callback for determining per-slide timeout value:  function(currSlideElement, nextSlideElement, options, forwardFlag)  */
			continuous:    1,     /* true to start next transition immediately after current one completes  */
			speed:         6000,  /* speed of the transition (any valid fx speed value)  */
			speedIn:       null,  /* speed of the 'in' transition  */
			speedOut:      null,  /* speed of the 'out' transition  */
			next:          null,  /* selector for element to use as click trigger for next slide  */
			prev:          null,  /* selector for element to use as click trigger for previous slide  */
			prevNextClick: null,  /* callback fn for prev/next clicks:  function(isNext, zeroBasedSlideIndex, slideElement)  */
			pager:         null,  /* selector for element to use as pager container  */
			pagerClick:    null,  /* callback fn for pager clicks:  function(zeroBasedSlideIndex, slideElement)  */
			pagerEvent:   'click', /* name of event which drives the pager navigation  */
			pagerAnchorBuilder: null, /* callback fn for building anchor links:  function(index, DOMelement)  */
			before:        null,  /* transition callback (scope set to element to be shown):     function(currSlideElement, nextSlideElement, options, forwardFlag)  */
			after:         null,  /* transition callback (scope set to element that was shown):  function(currSlideElement, nextSlideElement, options, forwardFlag)  */
			end:           null,  /* callback invoked when the slideshow terminates (use with autostop or nowrap options): function(options)  */
			easing:        null,  /* easing method for both in and out transitions  */
			easeIn:        null,  /* easing for "in" transition  */
			easeOut:       null,  /* easing for "out" transition  */
			shuffle:       null,  /* coords for shuffle animation, ex: { top:15, left: 200 }  */
			animIn:        null,  /* properties that define how the slide animates in  */
			animOut:       null,  /* properties that define how the slide animates out  */
			cssBefore:     null,  /* properties that define the initial state of the slide before transitioning in  */
			cssAfter:      null,  /* properties that defined the state of the slide after transitioning out  */
			fxFn:          null,  /* function used to control the transition: function(currSlideElement, nextSlideElement, options, afterCalback, forwardFlag)  */
			height:       'auto', /* container height  */
			startingSlide: 0,     /* zero-based index of the first slide to be displayed  */
			sync:          1,     /* true if in/out transitions should occur simultaneously  */
			random:        0,     /* true for random, false for sequence (not applicable to shuffle fx)  */
			fit:           0,     /* force slides to fit container  */
			containerResize: 1,   /* resize container to fit largest slide  */
			pause:         0,     /* true to enable "pause on hover"  */
			pauseOnPagerHover: 0, /* true to pause when hovering over pager link  */
			autostop:      0,     /* true to end slideshow after X transitions (where X == slide count)  */
			autostopCount: 0,     /* number of transitions (optionally used with autostop to define X)  */
			delay:         0,     /* additional delay (in ms) for first transition (hint: can be negative)  */
			slideExpr:     null,  /* expression for selecting slides (if something other than all children is required)  */
			cleartype:     !jQuery.support.opacity,  /* true if clearType corrections should be applied (for IE)  */
			cleartypeNoBg: false, /* set to true to disable extra cleartype fixing (leave false to force background color setting on slides)  */
			nowrap:        0,     /* true to prevent slideshow from wrapping  */
			fastOnEvent:   0,     /* force fast transitions when triggered manually (via pager or prev/next); value == time in ms  */
			randomizeEffects: 1,  /* valid when multiple effects are used; true to make the effect sequence random  */
			rev:           0,     /* causes animations to transition in reverse  */
			manualTrump:   true,  /* causes manual transition to stop an active transition instead of being ignored  */
			requeueOnImageNotLoaded: true, /* requeue the slideshow if any image slides are not yet loaded  */
			requeueTimeout: 250   /* ms delay for requeue */
		});
	}, true);
};

