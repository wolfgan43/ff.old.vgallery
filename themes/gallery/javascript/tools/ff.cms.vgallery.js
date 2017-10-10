if (!ff.cms) ff.cms = {};

ff.cms.vgallery = (function () {

    var that = { /* publics*/
        __init : false
        , "init": function() {
            this.fullclick();
            this.filter();
            this.__init = true;
        }
        , "fullclick": function(elem) {
            if(!elem)
                elem = jQuery("body");

            jQuery(".vg-item[data-fullclick]", elem).click(function(e) {
                if(jQuery(this).attr("data-fullclick") && !jQuery(e.srcElement).is("a") && !jQuery(e.srcElement).closest("a").length)  {
                    window.location.href = jQuery(this).attr("data-fullclick");
                    return false;
                }
            }).css("cursor", "pointer");
        }
        , "filter": function(elem) {
            if(!elem)
                elem = jQuery("body");

            jQuery(".vg-item[data-ffl]", elem).click(function(e) {
                //ff.cms.getBlock();
            
            });
        }
    };

    jQuery(function() {
        ff.cms.vgallery.init();
    });    

    return that;
})();

//jQuery(function() {
//});

/*
jQuery(function() {
	alert("ciao");
	checkEnableField();

	jQuery("INPUT.enable-field").click(function() {
		console.log("salve");
		checkEnableField();
	});
	
});

function checkEnableField() {
	jQuery(".enable-field").each(function() {
		
		var selected_class = jQuery(this).parents("TH").attr("class");
		if(jQuery(this).is(":not(:checked)")) {
			console.log("TD." + selected_class + "-field");
			jQuery("TD." + selected_class + "-field").attr('readonly','readonly');
		} else{
			console.log("mah " + "TD." + selected_class + "-field");
			jQuery("TD." + selected_class + "-field").attr('readonly','');
		}
	});
}*/