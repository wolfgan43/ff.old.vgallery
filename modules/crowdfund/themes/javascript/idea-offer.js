jQuery(function(){
	jQuery(".offer-type").hide();
	
	$('body').click(function(e) 
	{ 
		if(e.target.id !== "offer-button")
		{
			jQuery(".offer-type").hide(); 
		}
	});
//	jQuery(".ffButton.offer").click(function() 
//	{
//		jQuery(this).next().toggle();
//	});
/*	$('body').click(function(e) {
		if(e.target.id === "offer-button")
		{
			jQuery(".offer-type").hide(); 
			jQuery(e.target).next().show();
		} else
		{
			jQuery(".offer-type").hide();
		}
	  });
*/	
});

function CFoffer(elem)
{
	//jQuery(".offer-type").not(elem).hide();
	//console.log(jQuery(elem).next().is(":visible"));
	//console.log(jQuery(".offer-type").not(elem).is(":visible"));
	jQuery(".ffButton.offer").not(elem).next().hide();
	jQuery(elem).next().toggle();
	
	//jQuery(".offer-type").not(elem).hide();
	//jQuery(elem).next().toggle();
}