jQuery(function() {
    ff.cms.get("notify", {
        "template" : "notification-item"
        , "target": "#notification-handle"
        , "vars" : {
            "counter" : ".noty-box .count"
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