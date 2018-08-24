jQuery(function() {
    ff.cms.get("notify", {
        "template" : "notification-item"
        , "target": "#notification-handle"
        , "vars" : {
           // "counter" : "#dd-notifications-count"
        }
        , "callback" : function(data) {
            if(data["result"].length || jQuery("#notification-handle").children().length) {
                jQuery(".noty-box .notification-empty").addClass("hidden");
                jQuery("#notification-dropdown").removeClass("noty-empty");
                var countTotal      = jQuery("#notification-handle").children().length;
                var countDelivered  = jQuery("#notification-handle > .notification.delivered").length;
                var countNew        = countTotal - countDelivered;

                if(countNew) {
                    jQuery("#notifications-count").text(countNew).show();
                }
                jQuery("#dd-notifications-count").text(countTotal);
            } else {
                jQuery(".noty-box .notification-empty").removeClass("hidden");
                jQuery("#notification-dropdown").addClass("noty-empty");
            }
        }
    });

    jQuery(document).on("click", "#bell", function (e){
        e.preventDefault();
        if (jQuery("#notification-dropdown").is(":hidden")) {
            jQuery(".noty-background").show();
            jQuery("#notification-dropdown").css('display', 'block').removeClass('fadeOutUp').addClass('fadeInDown');
            jQuery("#notifications-count").text("").hide();
            ff.cms.get("notifyDelivered");
        } else {
            jQuery("#notification-handle > .notification:not(.delivered)").addClass("delivered");
            jQuery("#notification-dropdown").removeClass('fadeInDown').addClass('fadeOutUp')
                .on('animationend webkitAnimationEnd oanimationend MSAnimationEnd', function(){
                    jQuery(this).off('animationend webkitAnimationEnd oanimationend MSAnimationEnd');
                    jQuery(this).hide();
                    jQuery(".noty-background").hide();
                });
        }
    });

    jQuery(document).on("click", ".noty-background", function (e){
        jQuery("#bell").click();
    });
});