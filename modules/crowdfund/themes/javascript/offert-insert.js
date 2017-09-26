jQuery(function(){
    if(jQuery(".row.mifid").length)
    {
       jQuery("INPUT#UserModify_ActionButtonUpdate").attr('disabled','disabled');
       jQuery("INPUT#UserModify_ActionButtonInsert").attr('disabled','disabled');
    }
    
    $("INPUT.checkbox").change(function() {
        var bool = $("INPUT.checkbox:not(:checked)").length !==0;
        $("INPUT#UserModify_ActionButtonUpdate").attr('disabled', bool);
        $("INPUT#UserModify_ActionButtonInsert").attr('disabled', bool);
    });
});