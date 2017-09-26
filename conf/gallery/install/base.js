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
function updateFile() {
    jQuery.ajax({
           async: true,    
           type: "GET",
           url: jQuery("#site_path").val() + "/conf/gallery/install", 
           data: "force=1&json=1",
           dataType: "json",
           cache: false
    }).done(function(item) {
            var total = jQuery("#total").val();
            if(item["result"]) {
                        var result = parseInt(total) - parseInt(item["result"]);

                        jQuery("#progress-label").text(result + " of " + total);
                        jQuery(".progress").width(Math.ceil(jQuery(".pace").width() * parseInt(result) / parseInt(total)));
                        jQuery(".pace-activity").width(Math.ceil(jQuery(".pace").width() * parseInt(result) / parseInt(total)));

                        updateFile();
                } else {
                        jQuery("#progress-label").text(total + " of " + total);
                        jQuery(".progress").width(jQuery(".pace").width());
                        jQuery(".pace-activity").width(jQuery(".pace").width());

                        setTimeout("waitSecond(false)", 500);
                }
        }).fail(function(data) {
                    var checkError = data.responseText;
                    var item = "";

                    checkError = checkError.toLowerCase();

                    if(checkError.indexOf("<body>") >= 0) {
                    item = "<li>" + data.statusText.replace("<br>", "</li><li>") + '</li>';
                    } else {
                    item = "<li>" + data.responseText.replace("<br>", "</li><li>") + '</li>';
                    }
                    jQuery("#check").html("Check Update File.... Error");
                    jQuery("#error").append(item);
                    jQuery(".error").fadeIn();
                    jQuery(".content").hide();
        });
}

function waitSecond(display) {
        if(display) {
                jQuery("#check").html("Updating File in progress... Please Wait");
                jQuery(".error").hide();
                jQuery(".content").fadeIn();
                jQuery("#update-progress").fadeIn();

                updateFile();
        } else {
                jQuery("#check").html("Updating File in progress... Done");
                jQuery(".error").hide();
                jQuery(".content").hide();
                jQuery("#update-progress").fadeOut();

                window.location.href = jQuery("#site_path").val() + "";
        }						
}

function checkFile(masterSite, ftpUser, ftpPassword, authUser, authPassword) {
        jQuery("#check").html("Check Update File.... Please Wait");
        jQuery(".error").hide();
        jQuery("#check").show();
        jQuery(".content").show();

        if(masterSite.length > 0 && ftpUser.length > 0 && ftpPassword.length > 0) {
                var authParams = "";
                if(authUser && authPassword)
                        authParams = "&auth_name=" + authUser + "&auth_value=" + authPassword;

                var strAddParams = "&domain=" + masterSite + "&name=" + ftpUser + "&value=" + ftpPassword + authParams;
        } else {
                var strAddParams = "";
        }

    jQuery.ajax({
           async: true,    
           type: "GET",
           url: jQuery("#site_path").val() + "/conf/gallery/install", 
           data: "check=1" + strAddParams,
           dataType: "json",
           cache: false
    }).done(function(item) {
                if(item["total"]) {
                var total = item["total"];
                        var result = 0;

                        jQuery("#total").val(total);

                        jQuery("#check").text("Check Update File.... Done");
                        jQuery(".content").hide();

                        jQuery("#progress-label").text(result + " of " + total);
                        jQuery("#container-params").remove();
                        setTimeout("waitSecond(true)", 500);
                } else {
                        window.location.href = jQuery("#site_path").val() + "/setup";
                }					    
    }).fail(function(data) {
           var checkError = data.responseText;
           var item = "";

           checkError = checkError.toLowerCase();


           if(checkError.indexOf("<body>") >= 0) {
               item = "<li>" + data.statusText.replace('<br>', '</li><li>') + "</li>";
           } else {
               item = "<li>" + data.responseText.replace('<br>', '</li><li>') + "</li>";
           }
           jQuery("#check").html("Check Update File.... Error");
           jQuery("#error").append(item);
           jQuery(".error").fadeIn();
           jQuery(".content").hide();
           if(jQuery("#install").attr("id") !== undefined) {
               jQuery("#install").show();
           }					    
    });
}
function removeError() {
        if(jQuery("#warning").attr("id") !== undefined)
                jQuery("#warning").fadeOut(1000, function() {
                        jQuery(this).remove();
                });
}
jQuery(document).ready(function(){
        jQuery("#update-progress").hide();

        setTimeout("removeError()", 1000);

        if(jQuery("#install").attr("id") === undefined) {
                checkFile("", "", "");
        } else {
                jQuery("#check").hide();
                jQuery(".content").hide();

                jQuery("#install").click(function() {
                        var masterSite = jQuery("#master_site").val();
                        var ftpUser = jQuery("#ftp_user").val();
                        var ftpPassword = jQuery("#ftp_pwd").val();
                        var ftpConfPassword = jQuery("#ftp_conf_pwd").val();

                        var authUser = jQuery("#auth_user").val();
                        var authPassword = jQuery("#auth_pwd").val();
                        var authConfPassword = jQuery("#auth_conf_pwd").val();

                        if(masterSite.length > 0 && ftpUser.length > 0 && ftpPassword.length > 0 && ftpPassword == ftpConfPassword) {
                                jQuery(this).hide();
                                checkFile(masterSite, ftpUser, ftpPassword, authUser, authPassword);
                        } else {
                                alert("Fill all Fields");
                        }
                });
        }
});
;
