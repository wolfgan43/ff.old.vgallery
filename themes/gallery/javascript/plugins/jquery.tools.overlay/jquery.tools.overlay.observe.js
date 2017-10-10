ff.cms.fn.overlay = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	var toolsoverlayarray = new Array();
	/*css*/
	ff.pluginLoad("jquery.tools", "/themes/library/jquery.tools/jquery.tools.js", function() {	
		jQuery(targetid + "a.toolsoverlay").each(function(){
			toolsoverlayarray[jQuery(this).attr("rel")]++;
		});

		for(var toolsoverlaykey in toolsoverlayarray) {
			if(!jQuery.isFunction(toolsoverlayarray[toolsoverlaykey])) {
				var target = jQuery(targetid + "a.toolsoverlay[rel=" + toolsoverlaykey + "]").closest("table").parent().attr("id") + "-tools-overlay";
				var tablelement = jQuery(targetid + "a.toolsoverlay[rel=" + toolsoverlaykey + "]").closest("table");

				tablelement.parent().append(
					'<div class="simple_overlay" id="'+ target +'"><a class="prev">prev</a><a class="next">next</a><div class="info"></div><img class="progress" src="http://static.flowplayer.org/tools/img/overlay/loading.gif" /></div>'
				);
				jQuery(targetid + "a.toolsoverlay[rel=" + toolsoverlaykey + "]").overlay({
					absolute: false, /* If set to true, the overlay position is measured from the top-left corner of the browser window and the scrolling position has no impact. By default, the scrolling position is added. */
					api: false, /* When this tool is initialized (constructed), the return value is a jQuery object associated with the selector. By setting this property to true the return value is this tool's JavaScript API instead. If the selector returns multiple elements, the API of the last element will be returned. Read more about accessing the tool API.  */
					closeOnClick: true, /* By default, overlays are closed when the mouse is clicked outside the overlay area. Setting this property to false suppresses this behaviour which is suitable for modal dialogs.  */
					closeOnEsc: true, /* By default, overlays are closed when the ESC keyboard key is pressed. Setting this property to false suppresses this behaviour.  */
					effect: 'apple', /*  The effect to be used when an overlay is opened and closed. This can dramatically change the behaviour of the overlay. By default this tool uses an effect called "default" which is a simple show / hide effect.		Here is a list of currently available effects and you can also make your own effects. */
					expose: '#f1f1f1',  /* Overlay is very often used together with the expose tool. Because of this, the support for this feature has been built inside the tool. This configuration accepts the expose tool's configuration. This is either a simple string specifying the background color of the mask or a more complex object literal specifying more configuration variables.			See an example of an overlay together with expose. By default the expose feature is disabled. */
					close: null, /* A jQuery selector for the closing elements inside the overlay. These can be any elements such as links, buttons or images. If this is not supplied, a close element is auto-generated. Read more about this defining close actions.  */
					target: '#' + target, /* The element to be overlayed (if not specified in the rel attribute of the triggering element). */
					left: 'center', /* Specifies how far from the left-hand edge of the screen the overlay should be placed. By default the overlay is horizontally centered with the value "center" but you can also supply a numerical value specifying a distance in pixels. */
					oneInstance: true, /* By default, there can be only one overlay on the page at once. Setting this property to false allows you to have multiple overlay instances.  */
					speed: 'normal', /* The speed of the fade-in animation on the "default" effect. Valid values are 'slow', 'normal' and 'fast', or you can supply a numerical value (in milliseconds). By setting this property to 0, the overlay will appear immediately without any animation. */
					top: '10%' /* Specifies how far from the top edge of the screen the overlay should be placed. Acceptable values are an integer number specifying a distance in pixels, a string (such as '15%') specifying a percentage value or "center" in which case the overlay is vertically centered. Percentage values work consistently in different screen resolutions. */

				}).gallery({
					activeClass: 'active', /* The CSS class name for the trigger element which corresponds to the current image. */
					autohide: true, /* Specifies whether the next/prev buttons and the info box are automatically hidden. If this is set to false then these elements are always shown. */
					disabledClass: 'disabled', /*The CSS class name for disabled next and prev elements. For example, the prev element is disabled when there are no previous items to scroll to. */
					imgId:	'img', /*The id of the image that will be generated inside the overlay. */
					info: '.info', /* Selector for the nested element where the image information is placed. */
					next: '.next', /* Selector for the nested element to which a "next image" action should be bound. */
					preload: true, /* Specifies whether the previous and next images are automatically loaded into the browser's cache. */
					prev: '.prev', /* Selector for the nested element to which a "previous image" action should be bound. */
					progress: '.progress', /* Selector for the nested element that works as the loading indicator. Currently an animated GIF is the only viable option but in the future this may be an animated PNG. */
					opacity: '0.8', /* The transparency setting for tne next/prev and information elements. A decimal value from 0 to 1. A bigger value means less transparency while a value of 0 is fully transparent (invisible). */
					speed: 'slow', /* The speed of how fast the overlay is resized when the image is changed. */
					template: '<strong>${title}</strong> <span>Image ${index} of ${total}</span>' /* A pattern that specifies how the information is formatted inside the info box. By default following string will be used */
				});
			}
		};
	}, true);
};


