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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
 
/*
 * delayKeyup
 * http://code.azerti.net/javascript/jquery/delaykeyup.htm
 * Inspired by CMS in this post : http://stackoverflow.com/questions/1909441/jquery-keyup-delay
 * Written by Gaten
 * Exemple : $("#input").delayKeyup(function(){ alert("5 secondes passed from the last event keyup."); }, 5000);
 */
(function ($) {
	$.fn.delayKeyup = function(callback, ms){
	    var timer = 0;
	    var el = $(this);
		$(this).keyup(function(){                   
			clearTimeout (timer);
			timer = setTimeout(function(){
				callback(el)
			}, ms);
		});
	    return $(this);
	};
})(jQuery);
	
var whoisLegendTitle = "";
jQuery(function() {
	whoisLegendTitle = jQuery("#MCDomainModify_data .whois LEGEND").text();
	if(jQuery("#MCDomainModify_registrar_name").val().length == ""
		|| jQuery("#MCDomainModify_creation_date").val().length == ""
		|| jQuery("#MCDomainModify_update_date").val().length == ""
		|| jQuery("#MCDomainModify_expiration_date").val().length == ""
	) {
		getWhois(jQuery("#MCDomainModify_nome").val());
	}
	jQuery("#MCDomainModify_nome").delayKeyup(function(el) {
		if(el.val().length > 0) {
			getWhois(el.val());
		}
	}, 700);
	
});

function getWhois(domainName) {
	$.ajax({
		url: "http://www.whoisxmlapi.com/whoisserver/WhoisService",
		dataType: "jsonp",
		data: {
			domainName: domainName,
			outputFormat: "json"
		},
		success: function(data) {
			if(data.WhoisRecord !== undefined) {
				if(data.WhoisRecord.dataError == "") {
					jQuery("#MCDomainModify_data .whois LEGEND").html(whoisLegendTitle + '<span class="auto-whois">Domain Found</span>');
					
					var registrarName = data.WhoisRecord.registryData.registrarName;
					var createdDate = data.WhoisRecord.registryData.createdDateNormalized.split(" ")[0].split("-");
					var updatedDate = data.WhoisRecord.registryData.updatedDateNormalized.split(" ")[0].split("-");
					var expiresDate = data.WhoisRecord.registryData.expiresDateNormalized.split(" ")[0].split("-");

					jQuery("#MCDomainModify_registrar_name_label").text(registrarName);
					jQuery("#MCDomainModify_registrar_name").val(registrarName);
					
					jQuery("#MCDomainModify_creation_date_label").text(createdDate[2] + "/" + createdDate[1] + "/" + createdDate[0]);
					jQuery("#MCDomainModify_creation_date").val(createdDate[2] + "/" + createdDate[1] + "/" + createdDate[0]);
					
					jQuery("#MCDomainModify_update_date_label").text(updatedDate[2] + "/" + updatedDate[1] + "/" + updatedDate[0]);
					jQuery("#MCDomainModify_update_date").val(updatedDate[2] + "/" + updatedDate[1] + "/" + updatedDate[0]);
					
					jQuery("#MCDomainModify_day_expiration_date option").removeAttr("selected");
					jQuery("#MCDomainModify_day_expiration_date option[value=" + expiresDate[2] + "]").attr("selected", "selected");
					jQuery("#MCDomainModify_month_expiration_date option").removeAttr("selected");
					jQuery("#MCDomainModify_month_expiration_date option[value=" + expiresDate[1] + "]").attr("selected", "selected");
					jQuery("#MCDomainModify_year_expiration_date option").removeAttr("selected");
					jQuery("#MCDomainModify_year_expiration_date option[value=" + expiresDate[0] + "]").attr("selected", "selected");

					jQuery("#MCDomainModify_expiration_date").val(expiresDate[2] + "/" + expiresDate[1] + "/" + expiresDate[0]);
				} else {
					jQuery("#MCDomainModify_data .whois LEGEND").html(whoisLegendTitle + '<span class="auto-whois">' + data.WhoisRecord.dataError + '</span>');
				}
			} else {
				jQuery("#MCDomainModify_data .whois LEGEND").html(whoisLegendTitle + '<span class="auto-whois">Auto Detect Service Not Available</span>');
			}
		}
	});

}