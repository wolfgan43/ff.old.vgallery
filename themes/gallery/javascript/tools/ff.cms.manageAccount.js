jQuery(function() {
    if(jQuery("#UserAccount_billtoshipping").length) {
        if(jQuery("#UserAccount_billtoshipping").is(":checked")) {
            disableManageCheckbox();
        }
        jQuery("#UserAccount_billtoshipping").click(function() {
            if(jQuery(this).is(":checked")) {
                disableManageCheckbox();
            } else {
                enableManageCheckbox();
            }
        });
    }
    
});

function disableManageCheckbox () {
    jQuery("#UserAccount_shippingreference,#UserAccount_shippingaddress,#UserAccount_shippingcap,#UserAccount_shippingtown,#UserAccount_shippingprovince,#UserAccount_shippingstate").prop('disabled',true);
}

function enableManageCheckbox () {
    jQuery("#UserAccount_shippingreference,#UserAccount_shippingaddress,#UserAccount_shippingcap,#UserAccount_shippingtown,#UserAccount_shippingprovince,#UserAccount_shippingstate").prop('disabled',false);
}