INTERSTITIAL_MANAGER = (function($) {
	var param = null;
	$interstitialCanvas = null;
	
	function closeInterstitial()
	{
		$interstitialCanvas.remove();
	    $("body").removeClass("interstitial");
	}
	
	function appendInterstitialMainCode()
	{
		$("body").addClass("interstitial");
		var $interstitialSnippet = $('' +
		'<div id="interstitialCanvas">' +
			'<div id="interstitialWrapper">' +
				'<div id="interstitialContainer">' +
					'<div id="interstitial">' +
						'<a id="clickThroughURL_' + params.random + '" href="' + params.clickThroughURL + '" target="_blank"></a>' +
						'<a href="#" id="interstitialCloseButton"><img src="http://www.elle.it/files/publi/bottone_chiudi_interstitial01162013.gif" " width="162" height="38" /></a>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>' +
		'');
		$interstitialCanvas = $interstitialSnippet.prependTo("body");
		
		if(params.tracker)
		{
			var trackingIframe = '<iframe id="interstitialTracking" src="' + params.tracker + '" height="1" width="1"></iframe>';
			$("#interstitial").prepend(trackingIframe);
		}
	}
	
	function appendImage()
	{
		var imgSnippet = '<img src="' + params.backupImageURL + '" width="1000" height="600" />';
		$("#clickThroughURL_" + params.random).append(imgSnippet);
	}
	
	function appendVideo()
	{
		var videoSnippet = '' + 
		'<video id="DFPVideo" width="1000" height="600" autoplay muted poster="' + params.backupImageURL + '">' +
		  '<source src="' + params.moviemp4 + '" type="video/mp4">' +
		'</video>' +
		'';
		$("#clickThroughURL_" + params.random).append(videoSnippet);
		var muteButtonSnippet = '<button type="button" id="videoMuteInterstitial" class="videoControls" style="background-position: -80px 0px;"></button>';
		$("#interstitial").append(muteButtonSnippet);
		$("#videoMuteInterstitial").on("click", function(){
			DFPVideo = $("#DFPVideo");
			if(DFPVideo.get(0).muted)
			{
				$(this).css({"background-position" : "-120px 0"});
				DFPVideo.get(0).muted = false;
			}
			else
			{
				$(this).css({"background-position" : "-80px 0"});
				DFPVideo.get(0).muted = true;
			}
		});
		/*$("#DFPVideo").on("ended", function() {
			$("#videoMute").css({"background-position" : "-80px 0"});
			$(this).get(0).muted = true;
			this.play();
		});*/
	}
	
	function setupCloseButtonEvts()
	{
		$("#interstitialCloseButton").on("click", function(e){
			e.preventDefault();
			closeInterstitial();
		});
	}
	
	function setupCloseImg()
	{
		setTimeout(function() {
	        closeInterstitial();
	    }, (parseInt(params.displayTime) * 1000));
	}
	
	function setupCloseVideo()
	{
		$("#DFPVideo").on("ended", function() {
			closeInterstitial();
		});
	}
	
	function interstitialDef(p_params) {
		params = p_params;
		appendInterstitialMainCode();
		if(params.moviemp4 == "")
		{
			appendImage();
	    	setupCloseImg();
	    }
		else
		{
			appendVideo();
			setupCloseVideo();
		}
		setupCloseButtonEvts();
	}
	
	return {
		interstitialDef : interstitialDef
	};
}(jQuery));