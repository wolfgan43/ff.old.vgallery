jQuery(function() {
    ff.cms.get("notify", {
        "template" : "notification-item"
        , "target": "#notification-handle"
        , "vars" : {
            "counter" : ".noty-box .count"
        }
        , "callback" : function(data) {
            if(data["result"].length) {
                jQuery(".noty-box .notification-empty").addClass("hidden");
                jQuery("#notification-dropdown").removeClass("noty-empty");
            } else {
                jQuery(".noty-box .notification-empty").removeClass("hidden");
                jQuery("#notification-dropdown").addClass("noty-empty");
            }
        }
    });

    jQuery(document).on("click", "#bell", function (e){
        e.preventDefault();
        if ($("#notification-dropdown").is(":hidden")) {
            $(".noty-background").show();
            $("#notification-dropdown").css('display', 'block').removeClass('fadeOutUp').addClass('fadeInDown');
        } else {
            $("#notification-dropdown").removeClass('fadeInDown').addClass('fadeOutUp')
                .on('animationend webkitAnimationEnd oanimationend MSAnimationEnd', function(){
                    $(this).off('animationend webkitAnimationEnd oanimationend MSAnimationEnd');
                    $(this).hide();
                    $(".noty-background").hide();
                });
        }
    });

    jQuery(document).on("click", ".noty-background", function (e){
        jQuery("#bell").click();
    });
});