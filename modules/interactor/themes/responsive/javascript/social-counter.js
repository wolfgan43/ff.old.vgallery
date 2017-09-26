jQuery(function() {
    var linkCount = {};
    jQuery(".social-share-container").each(function() {
        linkCount.push(jQuery(this).attr("data-link"));
    });
    
    $.post("/services/get-social-count", {
        params: JSON.stringify(linkCount)
    }).done(function (data) {
        console.log(data);
    });
});