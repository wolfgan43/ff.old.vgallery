function Gallery(displayId, linkClass, preloadClass)
{
		this.imgIndex = 0;
		this.display = displayId;
		this.links = $(linkClass).get();
		this.imgNumber = this.links.length;
		this.slideShow = null;
		
		if(preloadClass)
			$(preloadClass).css({display: "none"});
		
		this.showImg = function(_index, fade)
		{
			if(!_index)
				index = this.imgIndex;
			else
				index = _index;
			
			var link = this.links[index];
			
			if($("#displayImg"))
			{
					$("#displayImg").remove();
			}
			$("<a rel='lightbox' href='" + link.href + "'><img id='displayImg' class='fade' src='" + link.href + "'/></a>").appendTo(this.display);
			$("#displayImg").css(
				{
					position: "absolute",
					top: $(this.display).offset().top + 1,
					left: $(this.display).offset().left + 1,
					zIndex: "0"
				}
			);
			if(fade)
				$(".fade").css({display: "none"}).fadeIn(1000);
		}
		
		this.setIndex = function(index)
		{
			this.imgIndex = index;
		}
		
}
