ff.pluginLoad("jquery.fn.jqbar", "/themes/library/plugins/jquery.jqbar/jquery.jqbar.js", function(){
	jQuery("#Idea.ffGrid TD.progress_goal").each(function(){
			jQuery(this).find(".data").children().wrapAll('<div class="goal-values"></div>');
			jQuery(this).find(".data").find(".goal").prepend("<span> / </span>").append("<span> €</span>");
			percentageCF = jQuery(this).find(".percentage_value").html();
			if (percentageCF <= 100 ) {
				jQuery(this).find("A .data").jqbar({ value: percentageCF, barColor: '#96c632', orientation: 'h', barWidth: '25', barLength:185 });
			};
	});
});