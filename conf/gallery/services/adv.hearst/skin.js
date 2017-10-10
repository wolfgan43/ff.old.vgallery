/*
 * Parametri da configurare per la chiamata a doubleclick
 */

/* 'sito' per DART */
var sito = "hmiit-shoppingmap";

/* Array con le associazioni "url": "zona" */
var zone = {
    "https://www.shoppingmap.it": "homepage",
    "https://www.shoppingmap.it/": "ros"//,

};


var zona = "test";
/*for (var urlZona in zone) {
  if (location.href.toLowerCase().indexOf( urlZona.toLowerCase() ) >= 0)
    zona = zone[urlZona];
}*/

var curURL = location.protocol + '//' + location.host + location.pathname;
for (var urlZona in zone) {
  if (!!curURL.match(urlZona.toLowerCase()))
    zona = zone[urlZona];
}

siteKeyname = sito + "/" + zona;

//google event dispatching is flawed (e.g. multiple events dispatching). Falling back to standard evts system
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
googletag.cmd.push(function() {
	googletag.pubads().addEventListener("slotRenderEnded", function(e) {
		var event = new CustomEvent("stdSlotRenderEnded", {
		  detail: e
		});
		window.dispatchEvent(new CustomEvent('stdSlotRenderEnded', event));
	});
});




