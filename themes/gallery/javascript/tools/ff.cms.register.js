ff.cms.register = {};

ff.cms.register.insert = function(component) {
    jQuery("#" + component).children().prop('disabled',true);
    jQuery("#" + component + " .error").remove();
    ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function() { 
       // document.getElementById('frmAction').value = component + '_insert';  
        ff.ajax.doRequest({'component' : component, 'action' : component + '_insert'}); 
        
        $("html, body").animate({ scrollTop: 0 }, 2000);
    });
};