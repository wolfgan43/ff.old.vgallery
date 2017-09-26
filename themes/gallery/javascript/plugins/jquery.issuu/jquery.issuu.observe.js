ff.cms.fn.issuu = function(targetid) { 
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	var user = "CHANGEMEPLS";
	var width = "980";
	var height = "693";
	var mode = "embed";
	var layout = "http%3A%2F%2Fskin.issuu.com%2Fv%2Flight%2Flayout.xml";
	var showFlipBtn = "false";
	/*jQuery(targetid + ' .issuu').attr("href", "javascript:void(0);");*/
	jQuery(targetid + '.issuu').click(function(){
		var altTitle = jQuery(this).children("img").attr("alt");
		if(altTitle === undefined) 
			altTitle = "";
			
		var altName = "";
		
		if(!altName.length > 0) {
			var arrFile = jQuery(this).attr("href").split("/");
			var lastValue = arrFile[arrFile.length -1];
			if(lastValue.indexOf(".") > 0) {

				altName = lastValue.substr(0, lastValue.indexOf("."));
			} else if(lastValue.indexOf("?") > 0) {
				altName = lastValue.substr(0, lastValue.indexOf("?"));
			} else {
				altName = lastValue;
			}
		}

		var url = 'http://issuu.com/' + user + '/docs/' + altName + '?mode=' + mode + '&layout=' + layout + '&showFlipBtn=' + showFlipBtn;

		if(jQuery(".issuucontent").children("iframe").attr("id") === undefined ) {
			jQuery(this).parent().parent().append('<div class="issuucontent"><iframe id="issuuviewer" src="#" width="' + width + '" height="' + height + '" ></iframe></div>');
			jQuery(".issuucontent").height(height);
			jQuery(".issuucontent").width(width);
			jQuery(".issuucontent").hide();		
		}

		jQuery("#issuuviewer").attr("src", url);
		/*jQuery("#issuuviewer").contentDocument.location.reload(true);*/
		jQuery(".issuucontent").dialog({
			title: altTitle,
			width: 'auto'
		});

		return false;
	});
};
