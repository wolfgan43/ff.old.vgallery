jQuery(document).ready(function() {
    if(navigator.userAgent.indexOf('MSIE 6.0') > -1) {
        ff.pluginLoad("jquery-ui", "/themes/library/jquery-ui/jquery-ui.js",function(){
            jQuery(".Content").append('<div id="ballot-screen"><h2>Il web browser che stai utilizzando è obsoleto</h2><p style="padding:5px 0 10px;">Per poter visualizzare correttamente questo sito è necessario aggiornare internet explorer ad una versione più recente o installarne un browser alternativo (consigliato)</p><p align="center"><a href="http://www.mozilla-europe.org" target="_blank"><img src="' + ff.site_path + '/themes/gallery/images/services/browser-ff.jpg" /></a><a href="http://www.google.com/chrome" target="_blank"><img src="' + ff.site_path + '/themes/gallery/images/services/browser-ch.jpg" /></a><a href="http://www.apple.com/it/safari/" target="_blank"><img src="' + ff.site_path + '/themes/gallery/images/services/browser-sf.jpg" /></a><a href="http://www.microsoft.com/windows/Internet-explorer/default.aspx" target="_blank"><img src="' + ff.site_path + '/themes/gallery/images/services/browser-ie.jpg" /></a></p></div>');
            jQuery("#ballot-screen").dialog({
                width: 500,
                autoOpen: true,
                modal: true
            });
       }, false);
    }
});