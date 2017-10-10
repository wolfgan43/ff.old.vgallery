jQuery(document).ready(function(){
    jQuery(".basic-install").click(function() {
        jQuery(".error").hide();
        jQuery(".install-container , .menu , .actions").fadeOut(function() {
            jQuery(".install-db").show();

            jQuery("#frmAction").val("install");
            jQuery("#frmMain").submit();
        });
        return false;
    });

    jQuery(".menu UL A").click(function(e){
    	e.preventDefault();

        jQuery(".menu li").removeClass("selected");
        jQuery(this).parent().addClass("selected");
        jQuery("fieldset").addClass("hidden");
        jQuery("fieldset." + jQuery(this).attr("rel")).removeClass("hidden");
    });
    
    jQuery(".install-advanced-settings").click(function(){
        jQuery(".menu li.adv").toggleClass("hidden");
    });

    jQuery('input.required, select.required').keyup(function() {
        jQuery(this).siblings(".check").removeClass("missed done");
            checkInput(jQuery(this));
            
        });
        
    checkInput();
    
    jQuery(".helpB").click(function(){
        jQuery(this).toggleClass("x");
        jQuery(".helpP[number='"+ jQuery(this).attr('number') +"']").toggleClass("big");
    });
    
	jQuery("#provider_email").change(function() {
		switch(jQuery(this).val()) {
            case 'mandril':
            	jQuery('#field_smtp_host INPUT').val('smtp.mandrillapp.com');
            	jQuery('#field_smtp_auth INPUT').attr('checked', 'checked');
            	jQuery('#field_smtp_port INPUT').val('587');
            	jQuery('#field_smtp_secure SELECT').val('tls');
            	
            	jQuery(this).after('<a id="provider_email_account" href="https://www.mandrill.com/signup/" target="_blank">Create Account</a>');
            	break;
            case 'custom':
            	jQuery('#field_smtp_host INPUT').val('');
            	jQuery('#field_smtp_auth INPUT').removeAttr('checked');
            	jQuery('#field_smtp_port INPUT').val('25');
            	jQuery('#field_smtp_secure SELECT').val('');

				if(jQuery("#provider_email_account").length)
            		jQuery("#provider_email_account").remove();
            	break;
			default:
            	jQuery('#field_smtp_host INPUT').val('localhost');
            	jQuery('#field_smtp_auth INPUT').removeAttr('checked');
            	jQuery('#field_smtp_port INPUT').val('25');
            	jQuery('#field_smtp_secure SELECT').val('');

            	if(jQuery("#provider_email_account").length)
            		jQuery("#provider_email_account").remove();
        }	
	});
});
function checkInput(variable){
    if(variable == undefined){
    	$('input.required, select.required').each(function() {
	        if (!jQuery(this).val()) {
	          $(this).removeClass("done");
	          $(this).siblings(".check").addClass("missed");
	        } else {
	          $(this).addClass("done");
	          $(this).siblings(".check").addClass("done");
	        }
	     });
    } else {
        if (!variable.val()) {
          variable.removeClass("done");
          variable.siblings(".check").addClass("missed");
        } else {
          variable.addClass("done");
          variable.siblings(".check").addClass("done");
        }
    }
    if(jQuery(".missed").length>0){
        jQuery(".btn").addClass("Off");
    } else {
        jQuery(".btn").removeClass("Off");
    }
}
           