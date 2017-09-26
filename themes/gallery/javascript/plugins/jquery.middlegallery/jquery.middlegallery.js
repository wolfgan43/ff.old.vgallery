function Gallery(displayId, linkClass)
{
		this.imgIndex = 0;
		this.display = displayId;
		this.links = jQuery(linkClass).get();
		this.imgNumber = this.links.length;
		this.slideShow = null;
		
		this.showImg = function(_index, fade)
		{
			if(!_index)
				index = this.imgIndex;
			else
				index = _index;
			
			var link = this.links[index];
			
			
            jQuery(this.display).attr("src") = link.href;

            
            if(fade)
                jQuery(this.display).css({display: "none"}).fadeIn("normal");

            /*
            if(jQuery("#displayImg"))
			{
					jQuery("#displayImg").remove();
			}
			jQuery("<img id='displayImg' class='fade' src='" + link.href + "'/>").appendTo(this.display);
			jQuery("#displayImg").css(
				{
					position: "absolute",
					top: jQuery(this.display).offset().top + 1,
					left: jQuery(this.display).offset().left + 1,
					zIndex: "0"
				}
			);  
			if(fade)
				jQuery(".fade").css({display: "none"}).fadeIn(1000);
                */
		}
		
		this.setIndex = function(index)
		{
			this.imgIndex = index;
		}
		
		this.showNext = function(fade)
		{
			this.imgIndex = ++this.imgIndex % this.imgNumber;
			this.showImg(null, fade);
		}
		
		this.showPrev = function(fade)
		{
			this.imgIndex = --this.imgIndex;
			if(this.imgIndex < 0) this.imgIndex = this.imgNumber - 1;
			this.showImg(null, fade);
		}
		
		this.load = function(path)
		{
			var fileName = this.links[this.imgIndex].href.split("/").pop();
			var top = jQuery(this.display).offset().top + (jQuery(this.display).height() / 2);
			var left = jQuery(this.display).offset().left + (jQuery(this.display).width() / 2);
			
			jQuery("<img id='indicator' src='script/img/indicator_medium.gif' alt='ajax indicator'/>").css(
				{
					position: "absolute",
					top: top,
					left: left
				}
			).appendTo(this.display);
			return jQuery("<div id='loadReturn'></div>").load(path + "fileData.php?filename=" + fileName, function()
				{
					jQuery("#indicator").remove();
				}
			);
		}
		
		this.createStatusBar = function(content, height)
		{
			h = height
			if(!height) h = 20
			/* Creazione della statusBar */
			jQuery("<div id='statusBar'></div>").appendTo(this.display)
			jQuery("#statusBar").append(content);
			jQuery("#statusBar").css(
				{
					position: "absolute",
					top: jQuery(this.display).offset().top + 1,
					left: jQuery(this.display).offset().left + 1,
					zIndex: "1",
					width: jQuery(this.display).width() - 2,
					display: "none",
					height: h,
					border: "1px solid",
					background: "#000000",
					color: "#FFFFFF",
					fontSize: "10px",
					opacity: "0.50"
				}
			).slideDown(700);
		}
		
		this.destroyStatusBar = function()
		{
			jQuery("#statusBar").slideUp(700, 
			function()
				{
					jQuery("#statusBar").remove();
				}
			);
		}
		
		this.start = function(_obj)
		{
			obj = _obj;
			
			obj.destroyStatusBar();
			obj.showNext(true);
			setTimeout("obj.createStatusBar(obj.load('imgs/'))", 1000);
			this.slideShow = setTimeout("obj.start(obj)", 5000);
		}
		
		this.stop = function()
		{
			clearTimeout(this.slideShow);
		}
}