jQuery(function() {
    ff.cms.get("notify", {
        "template" : "notification-item"
        , "target": "#notification-handle"
        , "vars" : {

        }
        , "callback" : function(service, vars) {
            var counter = jQuery("#notification-handle > *:not(.delivered)").length;
            if(counter)
                jQuery(".noty-box .count").text(counter).fadeIn();
            else
                jQuery(".noty-box .count").text("").hide();
        }
    }, null, {
        "inject" : "prepend"
    });

    jQuery(document).on("click", "#bell", function (e){
        e.preventDefault();
        if ($("#notification-dropdown").is(":hidden")) {
            jQuery(".noty-box .count").text("").hide();
           // jQuery("#notification-handle > *:not(.delivered)").addClass("delivered");

            ff.cms.set("notify", {"delivered" : true});

            $("#notification-dropdown").css('display', 'block').removeClass('fadeOutUp').addClass('fadeInDown');
        } else {
            $("#notification-dropdown").removeClass('fadeInDown').addClass('fadeOutUp')
                .on('animationend webkitAnimationEnd oanimationend MSAnimationEnd', function(){
                    $(this).off('animationend webkitAnimationEnd oanimationend MSAnimationEnd');
                    $(this).hide();
                });
        }
    });
});