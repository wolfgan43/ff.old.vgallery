if (!ff.cms) ff.cms = {};
ff.cms.blockLogin = (function () {
    var that = { /* publics*/
        "makeVisible": function (selector) {
            console.log("#" + selector);
            jQuery("#" + selector).toggle();
        }
    
    };

    return that;
})();


