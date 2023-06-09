/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage installer
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
jQuery(function(){
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
        jQuery("fieldset.default").addClass("hidden");
        jQuery("fieldset." + jQuery(this).attr("rel")).removeClass("hidden");
    });
    
    jQuery(".install-advanced-settings").click(function(){
        jQuery(".menu li.adv").toggleClass("hidden");
    });

    jQuery('input.required, select.required').keyup(function() {
        jQuery(this).siblings(".check").removeClass("missed done");
            checkInput(jQuery(this));
            
        });
        
    jQuery("LEGEND .helpB").click(function(){
        jQuery(this).toggleClass("x");
        jQuery(this).closest("fieldset").children(".helpP").toggleClass("big");
    });
    jQuery("FIELDSET.options LEGEND INPUT").change(function() {
        if(jQuery(this).is(":checked")) {
            jQuery(this).closest("FIELDSET").children("*:not(LEGEND):not(.hidden)").slideDown();
        } else {
            jQuery(this).closest("FIELDSET").children("*:not(LEGEND):not(.hidden)").slideUp();
        }
    });
    jQuery("FIELDSET .primary").change(function() {
       if(jQuery(this).is(":checked")) {
           jQuery('FIELDSET.dep[data-dep="' + jQuery(this).attr("id") + '"]').removeClass("hidden");
       } else {
           jQuery('FIELDSET.dep[data-dep="' + jQuery(this).attr("id") + '"]').addClass("hidden");
       }
    });
	jQuery("#provider_email").change(function() {
		switch(jQuery(this).val()) {
            case 'sparkpost':
            	jQuery('#field_smtp_host INPUT').val('smtp.sparkpostmail.com');
            	jQuery('#field_smtp_auth').attr('checked', 'checked');
            	jQuery('#field_smtp_user INPUT').val('SMTP_Injection');
            	
            	jQuery('#field_smtp_port INPUT').val('587');
            	jQuery('#field_smtp_secure SELECT').val('tls');

                jQuery('#field_smtp_auth').change();
            	
            	jQuery(this).after('<a id="provider_email_account" href="https://app.sparkpost.com/sign-up" target="_blank">Create Account</a>');
            	break;
            case 'custom':
            	jQuery('#field_smtp_host INPUT').val('');
            	jQuery('#field_smtp_auth').removeAttr('checked');
            	jQuery('#field_smtp_user INPUT').val('');
            	jQuery('#field_smtp_port INPUT').val('25');
            	jQuery('#field_smtp_secure SELECT').val('');

				if(jQuery("#provider_email_account").length)
            		jQuery("#provider_email_account").remove();

                jQuery('#field_smtp_auth').change();
            	break;
			default:
            	jQuery('#field_smtp_host INPUT').val('localhost');
            	jQuery('#field_smtp_auth').removeAttr('checked');
            	jQuery('#field_smtp_user INPUT').val('');
            	jQuery('#field_smtp_port INPUT').val('25');
            	jQuery('#field_smtp_secure SELECT').val('');

            	if(jQuery("#provider_email_account").length)
            		jQuery("#provider_email_account").remove();

                jQuery('#field_smtp_auth').change();
        }	
	});

    if(jQuery("#field_db_name_mongo INPUT").val()) {
        jQuery("#field_db_mongo").attr("checked", "checked");
        jQuery("#field_db_mongo").change();
    }
    checkInput();
    checkDeps();
    checkFieldset();

});

function reportRequired() {
    $('.menu UL A').each(function() {
        var textNeed = "";
        var need = jQuery("fieldset." + jQuery(this).attr("rel") + " .required").length - jQuery("fieldset." + jQuery(this).attr("rel") + " .required.done").length;
        if(need) {
            textNeed = " " + need + " Need"

            jQuery(this).addClass("required");
        } else {
            jQuery(this).removeClass("required");
        }
        jQuery("span.need", this).text(textNeed);
    });

}
function checkDeps() {
    jQuery("FIELDSET.dep").each(function() {
        var id = jQuery(this).data("dep");
       if(jQuery("#" + id).is(":checked")) {
           jQuery(this).removeClass("hidden");
       }  else {
           jQuery(this).addClass("hidden");
       }
    });
}
function checkFieldset() {
    jQuery("FIELDSET.options LEGEND INPUT").each(function() {
        if(jQuery(this).is(":checked")) {
            jQuery(this).closest("FIELDSET").children("*:not(LEGEND):not(.hidden)").show();
        } else {
            jQuery(this).closest("FIELDSET").children("*:not(LEGEND):not(.hidden)").hide();
        }
    });
}
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

    reportRequired();
}
           