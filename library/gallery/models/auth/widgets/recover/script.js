ff.modules.auth = (function () {
    var social_window = undefined;
    var that = {
        social: {
            "requestLogin" : function (title, url) {
                social_window = window.open(
                    ff.fixPath(url)
                    , title
                    , "menubar=no, status=no, height=500, width= 500"
                );


            }
        },
        login : {
            "submit": function (url, action, selector, elem, ret_url) {
                if(!selector)
                    selector = dialog_opened;

                var selectorID = "#" + selector;

                var domain = jQuery(selectorID).find("INPUT[name='domain']").val() || undefined;
                var username = jQuery(selectorID).find("INPUT[name='username']").val() || undefined;
                var password = jQuery(selectorID).find("INPUT[name='password']").val() || undefined;
                var token = jQuery(selectorID).find("INPUT[name='csrf']").val() || "";
                console.log(token);
                if(!action)
                    action = (!ff.group ? "login" : "logout");

                if(!url)
                    url = '/login';

                if(ret_url)
                    url = ff.urlAddParam(url, "ret_url", ret_url);

                $.ajax({
                    url: url,
                    headers: {
                        "domain": domain
                        , "csrf": token
                    },
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        "username": username
                        , "password": password
                    },
                    success: function(data){
                      if(data.status === 0) {

                       } else{
                           jQuery(elem).find(".disabled").css({'opacity': '', 'pointer-events': ''}).removeClass("disabled");
                            if(jQuery(selectorID + " .error-container").length) {
                                jQuery(selectorID + " .error-container").html(data.error);
                            }
                       }
                    }
                });

                return false;
            }
        },
        "submitProcessKey" : function (e, button) {
            if (null == e)
                e = window.event;
            if (e.keyCode == 13)  {
                document.getElementById(button).focus();
                document.getElementById(button).click();
                return false;
            }
        }

    };
    return that;
})();