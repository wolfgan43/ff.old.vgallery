ff.cms.fn.customwall = function(targetid) {
    var targetid = targetid;
    if(targetid.length > 0)
        targetid = targetid + " ";
    
    createGroup(targetid);
    
};

function createGroup(targetid) {
    var i = 0;
    var wrapper = "group_";
    var class_value;
    jQuery(targetid + '.customwall').each(function() {
        if(class_value !== undefined && wrapper + (i / 7) !== class_value)
            jQuery("." + class_value).wrapAll("<div class='page" + (i / 7) +"'>");
        class_value= wrapper + (i / 7);
        jQuery(this).closest("DIV").addClass(class_value);
        i++;
    });
    
    if(!jQuery(".page" + (i / 7)).length)
        jQuery("." + class_value).wrapAll("<div class='page" + (i / 7) +"'>");
}