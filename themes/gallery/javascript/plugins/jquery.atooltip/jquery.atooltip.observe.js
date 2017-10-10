ff.cms.fn.atooltip = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	/*css*/
	ff.pluginLoad("jquery.atooltip", "/themes/library/plugins/jquery.atooltip/jquery.atooltip.js", function() {	
		jQuery( targetid + ".atooltip").aToolTip({  
		         clickIt: false,                     /* set to true for click activated tooltip  */
		         closeTipBtn: 'aToolTipCloseBtn',    /* you can set custom class name for close button on tooltip  */ 
		         fixed: false,                       /* Set true to activate fixed position  */  
		         inSpeed: 400,                       /* Speed tooltip fades in    */
		         outSpeed: 100,                      /* Speed tooltip fades out  */  
		         tipContent: '',                     /* Pass in content or it will use objects 'title' attribute    */
		         toolTipClass: 'aToolTip',           /* Set custom class for tooltip    */
		         xOffset: 5,                         /* x Position    */
		         yOffset: 5                          /* y position    */
		     });
	}, true);
};