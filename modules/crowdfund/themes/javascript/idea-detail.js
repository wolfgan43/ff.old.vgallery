$(function() {
  var player = $(".flexslider.video .video iframe")[0];
  if(player !== undefined) {
      ff.pluginLoad("froogaloop", '/themes/library/plugins/froogaloop/froogaloop.js', function() {
  	$f(player).addEvent('ready', ready); 
    });
  }

  function addEvent(element, eventName, callback) {
	if (element.addEventListener) {
	  element.addEventListener(eventName, callback, false)
	} else {
	  element.attachEvent(eventName, callback, false);
	}
  }
 
  function ready(player_id) {
	var froogaloop = $f(player_id);
	froogaloop.addEvent('play', function(data) {
	  $('.flexslider.video').flexslider("pause");
	});
	froogaloop.addEvent('pause', function(data) {
	  $('.flexslider.video').flexslider("play");
	});
  }
 
   
  // Call fitVid before FlexSlider initializes, so the proper initial height can be retrieved.
  $(".flexslider.video").flexslider({
	  animation: "slide",
	  useCSS: false,
	  animationLoop: true,
	  smoothHeight: true,
	  before: function(slider){
		$f(player).api('pause');
	  }
  });

 $(".flexslider").flexslider({
	  animation: "slide",
	  useCSS: false,
	  smoothHeight: true
  });
  
  var addthis_config = {"data_track_addressbar":true};
  ff.pluginLoad("addThis", "http://s7.addthis.com/js/300/addthis_widget.js#pubid=ra-519357ee6431fb1e");
});