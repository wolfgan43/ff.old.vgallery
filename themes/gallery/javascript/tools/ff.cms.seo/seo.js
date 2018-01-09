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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
ff.cms.seo = {
	__init : false,
	$container : {},
	gPageSpeed : {
		key : "",
		url: "https://www.googleapis.com/pagespeedonline/v1/runPagespeed",
		responseD : undefined,
		responseM : undefined,
		status: false
	},
	ySlow: {
		key : "",
		url: "",//da trovare
		response : undefined,
		status: false
	},
	key : "",
	analysis : undefined,
	total: {
		success: 0,
		warning: 0,
		error: 0,
		count: 0,
		score: 0,
		scoreTotal: 0,
		frame : {
			pagespeedD : 0, 
			pagespeedM : 0, 
			yslow : 0
		}
	}, 
	tpl: undefined,
	statusIcons: {},
	stopWords : function() {},	
	init:function(container) {
		var that = this; 
		if(!that.__init) {
			that.$container = jQuery(".adminbar .seo-link").parent();

			that.$container.hover(function() {
				jQuery(this).addClass("selected");
				jQuery(".seo-report", that.$container).removeClass("hidden");
			}
			, function() {
				jQuery(this).removeClass("selected");
				jQuery(".seo-report", that.$container).addClass("hidden");
			});
			
			jQuery(".seo-link-detail", that.$container).click(function() {
				var elem = this;
				that.loadData(function(reload) {
					if(jQuery(elem).hasClass("refresh")) {
						that.total = {
							success: 0,
							warning: 0,
							error: 0,
							count: 0,
							score: 0,
							scoreTotal: 0,
							frame : {
								pagespeedD : 0, 
								pagespeedM : 0, 
								yslow : 0
							}
						};
						that.setEditor(true);
					} else {
						that.setEditor(reload);
					}
				});
			});
			
		}
	},
	"loadData" : function(callback) {
		var that = this;
		if(that.tpl) {
			if(callback) callback(false);
		} else {
			jQuery.getJSON("/srv/sitemap/crawler" + window.location.pathname, function(data) {
				that.analysis = data["analysis"];
				that.tpl = data["tpl"];
				that.statusIcons = data["icons"];
				that.gPageSpeed.key = data["key"];
				that.cache = data["cache"];
				
				if(callback) callback(true);
			});		
		}

	},
	"setEditor" : function(reload) {
		var that = this;
		ff.cms.editor.display((reload ? true : undefined), "edit", {"tpl" : that.tpl}, function(targetElem, display) {
			if(display !== false && reload) {
				jQuery('.seo-analysis-status', targetElem).html(that.statusIcons["loading"]);
				if(that.gPageSpeed.key) {
					jQuery.getJSON(that.gPageSpeed.url + "?url=" + encodeURIComponent(window.location.href) + "&locale=" + ff.locale + "&strategy=desktop&key=" + that.gPageSpeed.key, function(response) {
						that.total.frame.pagespeedD = response.score;
						that.gPageSpeed.responseD = response;
						jQuery.getJSON(that.gPageSpeed.url + "?url=" + encodeURIComponent(window.location.href) + "&locale=" + ff.locale + "&strategy=mobile&key=" + that.gPageSpeed.key, function(response) {
							that.total.frame.pagespeedM = response.score;
							that.gPageSpeed.responseM = response;
							//that.total.frame.pagespeedM = response.score;
							//console.log(response);
							that.gPageSpeed.status = true;
							that.tpl = that.processAnalysis(targetElem);
						});
					});
				} else {
					that.tpl = that.processAnalysis(targetElem);
				}
				
			}
		});
	},
	"setReport" : function() {
		var setGraph = function(elem, perc) {
			var $pie = jQuery(elem).filter(".pie");
			var $bar = jQuery(elem).filter(".bar");
			var $circle = $('svg .bar', $pie);

			if (isNaN(perc)) {
				perc = 100; 
			} else {
				var r = $circle.attr('r');
				var c = Math.PI*(r*2);

				if (perc < 0) { perc = 0;}
				if (perc > 100) { perc = 100;}

				var pct = ((100-perc)/100)*c;

				$circle.css({ strokeDashoffset: pct});

				$(elem).attr('data-pct',perc);
			}
			$bar.height(2*perc + "px");		
		}
		jQuery(".seo-report", this.$container).removeClass("hidden");
		jQuery(".adminbar").mouseover();

		jQuery(".seo-success .seo-count", this.$container).text(this.total.success);
		jQuery(".seo-warning .seo-count", this.$container).text(this.total.warning);
		jQuery(".seo-error .seo-count", this.$container).text(this.total.error);
		
		jQuery(".seo-success .seo-progress", this.$container).width(100 * this.total.success / this.total.count);
		jQuery(".seo-warning .seo-progress", this.$container).width(100 * this.total.warning / this.total.count);
		jQuery(".seo-error .seo-progress", this.$container).width(100 * this.total.error / this.total.count);
		
		setGraph(jQuery(".seo-total", this.$container), Math.floor(100 * this.total.score / this.total.scoreTotal));
		
		if(this.gPageSpeed.status) {
			setGraph(jQuery(".seo-pspeedD", this.$container), this.total.frame.pagespeedD);
			setGraph(jQuery(".seo-pspeedM", this.$container), this.total.frame.pagespeedM);
		}
		if(this.ySlow.status) {
			setGraph(jQuery(".seo-yslow", this.$container), this.total.frame.yslow)
		}
	},	
	processAnalysis : function(targetElem, analysis) {
		if(!analysis)
			analysis = this.analysis;
	
		if(analysis) {
			for(var category in analysis) {
				if(analysis[category]) {
					for(var type in analysis[category]) {
						analysis[category][type]["score"] = this.check(type, jQuery('.seo-analysis-' + type, targetElem), undefined, analysis[category][type]);
					}
				}
			}
		}	
		this.setReport();

		return targetElem.html();
	},
	countChar: function(elem, max, min, parentClass) {
		if(!max)
			max = 150;
		if(!min)
			min = 0;

		var $parent = (parentClass 
						? jQuery(elem).closest("." + parentClass)
						: jQuery(elem).parent()
					);

		var value = jQuery(elem).val().length;
		var textLabel = (min > 0 ? "Min: " + min + "<br />" : "") + "Max: " + max + "<br /><br />" + " Count: " + value;
		var textValue = parseInt(max - value);
		
		if($parent.find("label .legend-char").length) {
			$parent.find("label .legend-char").html(textLabel); 
		} else {
			$parent.find("label").append('<div class="legend-char">' + textLabel + '</div>');
		}

		/*if(jQuery(elem).next().hasClass("character-left")) {
			$(elem).next().text(textValue); 
		} else {
			jQuery(elem).after('<div class="character-left">' + textValue + '</div>');
		}*/
		if(!jQuery(elem).attr("data-border"))
			jQuery(elem).attr("data-border", jQuery(elem).css("border"));

		if(value > max)	{
			jQuery(elem).css("border", "1px solid #DD4330");
		} else if(value == 0 || (min > 0 && value < min)) {
			jQuery(elem).css("border", "1px solid #F6C327");
		} else {
			jQuery(elem).css("border", jQuery(elem).attr("data-border"));
		}
		
	},
	wordStats: function(content, limit, grab) {
		function analyze(textPlain, limit, grab) {
			if(!textPlain)
				return null;

			if(!limit)
				limit = 5;

			if(!grab)
				grab = 3;

			//var reOne = /\b(\w+?)\b/gi;
			var reOne = /[\w\u0430-\u044f]+/gi;
			var stopWords = ff.cms.seo.stopWords() || "";

			var result = {
				"No StopWords" : []
				, "All" : []
			};
			var rawWords = textPlain.match(reOne);

			if(rawWords) {
				for(var i=1; i<=grab; i++) {
					if(stopWords) {
						result["No StopWords"].push(countWords(grabKeywords(stripStopWords(rawWords, stopWords),i), limit)[0]); //Without StopWords
					}
					result["All"].push(countWords(grabKeywords(rawWords,i), limit)[0]); //With StopWords
				}
			}
			
			return result;
		};

		function stripStopWords(arKw,stopWords) {
			var res = [];
			var reOmit = (stopWords
					? new RegExp('\\b('+stopWords+')\\b',"gi")
					: null
				);	

			if(reOmit) {
				for (var i=0;i<arKw.length;i++) {
					if(!arKw[i].match(reOmit))
						res.push(arKw[i]);
				}
			} else {
				res = arKw;
			}
			return res;		
		};
		
		function grabKeywords(arKw,w) {
			var res = [];
			for (var i=w-1;i<arKw.length;i++) {
				var currentPhrase = "";
				for (var j=0;j<w;j++) {
					currentPhrase = arKw[i-j] +' '+currentPhrase;
				}
				res.push(currentPhrase);
			}
			return res;		
		};
		function sortByNumericValue(A, limit, stopWords) {//using objects
			var B=[];
			var C=[];

			var reOmit = (stopWords
					? new RegExp('\\b('+stopWords+')\\b',"gi")
					: null
				);

			for (i in A) {
				if(reOmit && !i.match(reOmit))
					C.push({v:i,c:A[i]});
				B.push({v:i,c:A[i]});
			}

			B.sort(function(x,y){return y.c-x.c})
			B.splice(limit);

			C.sort(function(x,y){return y.c-x.c})
			C.splice(limit);

			return [B,C];
		}
		function countWords(arKw, limit) {
			var kwResults = new Object();
			for (var i=0;i<arKw.length;i++) {
				var w = arKw[i].toLowerCase();
				//console.log(w);
				if (kwResults[w] != undefined) {
					kwResults[w]++;
				} else {
					kwResults[w] = 1;
				}
			}
			return sortByNumericValue(kwResults, limit);
		};	
		function getElementText(el) {
		    var text = '';
		    // Text node (3) or CDATA node (4) - return its text
		    if ( (el.nodeType === 3) || (el.nodeType === 4) ) {
		        text = el.nodeValue;
		    // If node is an element (1) and an img, input[type=image], or area element, return its alt text
		    } else if ( (el.nodeType === 1) && (
		            (el.tagName.toLowerCase() == 'img') ||
		            (el.tagName.toLowerCase() == 'area') ||
		            ((el.tagName.toLowerCase() == 'input') && el.getAttribute('type') && (el.getAttribute('type').toLowerCase() == 'image'))
		            ) ) {
		        text = el.getAttribute('alt') || '';
		    } else if ( (el.nodeType === 1) && (
		            ((el.tagName.toLowerCase() == 'input') && el.getAttribute('type') && (el.getAttribute('type').toLowerCase() != 'hidden'))
		            || (el.tagName.toLowerCase() == 'textarea')
		            || (el.tagName.toLowerCase() == 'selection')
		            ) ) {
		        text = el.value;
		    // Traverse children unless this is a script or style element
		    } else if ( (el.nodeType === 1) && !el.tagName.match(/^(script|style)$/i) ) {
		        var children = el.childNodes;
		        for (var i = 0, l = children.length; i < l; i++) {
		            text += getElementText(children[i]);
		        }
		    }
		    return text;
		};
		function stripHtmlTags(selector) {
			var res = "";
			var elem = jQuery(selector).clone();

			if(elem.length > 0) {
				jQuery(".adminbar", elem).remove();
				jQuery("#cms-editor-container", elem).remove();
				jQuery(".toolbar", elem).remove();
				jQuery("SCRIPT", elem).remove();
				jQuery("STYLE", elem).remove();
				jQuery(selector).each(function() {
					res += getElementText(this) + " ";
				});
			} else {
				res = selector;
			}
			
			return res;
		};

		return analyze(stripHtmlTags(content), limit, grab);
	},
	check : function(type, elem, content, params) {
		var that = this;

		function title(content) {
			var score = 0;
			var description = '';
			var min = 10;
			var max = 70;

			if(!content) {
				content = jQuery("TITLE", document.head).text();
			}
			var countChar = content.length;
			if(countChar >= min) {
				if(countChar <= max) {
					score = 2;
				}
			} else {
				score = 1;
			}

			description = '<table><thead><tr><th>Count</th><th>Min</th><th>Max</th></tr></thead><tbody><tr><td>' + countChar + '</td><td>' + min + '</td><td>' + max + '</td><tr></tbody></table>';

			

			return {
				"score" : score
				, "description" : description
			};
		};
		function description(content) {
			var score = 0;
			var description = '';
			var min = 70;
			var max = 160;

			if(!content) {
				content = jQuery("META[name='description']", document.head).attr("content");
			}

			var countChar = content.length;
			if(countChar >= min) {
				if(countChar <= max) {
					score = 2;
				}
			} else {
				score = 1;
			}

			description = '<table><thead><tr><th>Count</th><th>Min</th><th>Max</th></tr></thead><tbody><tr><td>' + countChar + '</td><td>' + min + '</td><td>' + max + '</td><tr></tbody></table>';

			return {
				"score" : score
				, "description" : description
			};
		};
		function headings(content) {
			var score = 0;
			var description = '';
			var otherH = false;
			var min = 10;
			var max = 70;

			if(!content) {
				content = jQuery("h1, h2, h3", ".container, .container-fluid", document.body);
			}
			
			description = '<table><thead><tr><th>Header</th><th>Count</th><th>Min</th><th>Max</th></tr></thead><tbody>';
			jQuery(content).each(function() {
				var countChar = jQuery(this).text().length;
				if(jQuery(this).hasClass("h1")) {
					if(countChar >= min) {
						if(countChar <= max) {
							score = 2;
						}
					} else {
						score = 1;
					}
				} else {
					otherH = true;
				}
				description += '<tr><td>' + jQuery(this).prop("tagName") + '</td><td>' + countChar + '</td><td>' + min + '</td><td>' + max + '</td><tr>';
			});
			description += '</tbody></table>';
			
			if(status == 2 && !otherH)
				 status = 1;

			return {
				"score" : score
				, "description" : description
			};
		};
		function keyWordsCloud(content, limit, grab) {
			var score = 0;
			var description = '';

			if(!content) {
				content = jQuery(".container, .container-fluid", document.body).clone();	
				var keyCompareFrom = {
					"Smart Url" : window.location.pathname.replace(/^.*[\\\/]/, '')
					, "Title" : (jQuery("head > title", document).length ? jQuery("head > title", document).text() : "")
					, "Meta Desc" : (jQuery("head > meta[name=description]", document).length ? jQuery("head > meta[name=description]", document).attr("content") : "")
					, "Meta KeyWords" : (jQuery("head > meta[name=keywords]", document).length ? jQuery("head > meta[name=keywords]", document).attr("content").replace(",", " ") : "")
					, "H1" : (jQuery("body > H1", document).length ? jQuery("body > H1", document).text() : "")
					, "H2" : (jQuery("body > H2", document).length ? jQuery("body > H2", document).text() : "")
					, "H3" : (jQuery("body > H3", document).length ? jQuery("body > H3", document).text() : "")
				};
				for(var keyCompare in keyCompareFrom) {
					content.append(" " + keyCompareFrom[keyCompare]);
				}	
							
			}

			var keywordsStats = ff.cms.seo.wordStats(content, limit, grab) || [];

			return {
				"score" : score
				, "description" : description
			};
		};
		function keyWordsConsistency(content, keyCompareFrom, limit, grab) {
			var score = 0;
			var keyConsistencyHtmlHeader = ''
			var keyConsistencyHtml = '';

			function compareKeyWord(keyWord, pool) {
				var res = '';
				var countFailed = 0;
				keyWord = keyWord.replace(/ /g, ".*");
				if(pool) {
					for(elemKey in pool) {
						if(pool[elemKey]) {
							//if(pool[elemKey].match(new RegExp('\\b('+keyWord+')\\b',"gi"))) {
							if(pool[elemKey].match(new RegExp('(^|[^a-z0-9+])' + keyWord + '([^a-z0-9+]|$)',"gi"))) {
								res += '<td><i class="fa ' + "fa-check-circle vg-success" + '"></i></td>';	
							} else {
								res += '<td><i class="fa ' + "fa-times-circle vg-danger" + '"></i></td>';
								
								countFailed++;
							}
						
							
						}
					}			
				}
				return { "content" : res, "bold" : (countFailed ? false : true)};
			}

			if(!content) {
				content = jQuery(".container, .container-fluid", document.body).clone();	
				if(!keyCompareFrom) {
					keyCompareFrom = {
						"Smart Url" : window.location.pathname.replace(/^.*[\\\/]/, '')
						, "Title" : (jQuery("head > title", document).length ? jQuery("head > title", document).text() : "")
						, "Meta Desc" : (jQuery("head > meta[name=description]", document).length ? jQuery("head > meta[name=description]", document).attr("content") : "")
						, "Meta KeyWords" : (jQuery("head > meta[name=keywords]", document).length ? jQuery("head > meta[name=keywords]", document).attr("content").replace(",", " ") : "")
						, "H1" : (jQuery("body > H1", document).length ? jQuery("body > H1", document).text() : "")
						, "H2" : (jQuery("body > H2", document).length ? jQuery("body > H2", document).text() : "")
						, "H3" : (jQuery("body > H3", document).length ? jQuery("body > H3", document).text() : "")
					};
				}
				for(var keyCompare in keyCompareFrom) {
					content.append(" " + keyCompareFrom[keyCompare]);
				}
			}
			var keywordsStats = ff.cms.seo.wordStats(content, limit, grab) || [];

			if(keyCompareFrom) {
				for(elemKey in keyCompareFrom) {
					if(keyCompareFrom[elemKey])
						keyConsistencyHtmlHeader += '<th>' + elemKey + '</th>';
				}
			}

			
			for(type in keywordsStats) {
				if(keywordsStats[type].length) {
					keywordsStats[type].each(function(grab, keyWords) {
						if(keyWords.length) {
							keyConsistencyHtml += '<div class="keywords-cloud">'
												+'<table class="table word-' + parseInt(grab + 1) + '">'
													+ '<thead>'
														+ '<tr>'
															+ '<th>' + parseInt(grab + 1) + ' Word (' + type + ')</th>'
															+ '<th>Freq</th>'
															+ keyConsistencyHtmlHeader
														+ '</tr>'
													+ '</thead>'
													+ '<tbody>';
							keyWords.each(function(rate, objWord) {
								resCompare = compareKeyWord(objWord["v"].trim(), keyCompareFrom);
							
								keyConsistencyHtml += '<tr>'
													+ (resCompare["bold"]
														? '<td><a href="javascript:void(0);"><mark>' + objWord["v"].trim() + '</mark></a></td>' 
														: '<td><a href="javascript:void(0);">' + objWord["v"].trim() + '</a></td>'
													)
													+ '<td>' + objWord["c"]+ '</td>'
													+ resCompare["content"]
												+ '</tr>';
							});
							keyConsistencyHtml += '</tbody><table></div>';
						}
					});
				}
			}

			return {
				"score" : score
				, "description" : keyConsistencyHtml
			};
		};		
		function images(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function textHtmlRatio(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function googlePublisher(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function inPageLinks(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function brokenLinks(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function wwwResolve(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function ipCanonicalization(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function robotsTxt(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function xmlSitemap(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function urlRewrite(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function underscoresInTheUrls(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function flash(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function domainExpiration(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function blog(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function mobileRendering(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function mobileLoadTime(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function mobileOptimization(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function url(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function favicon(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function custom404Page(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function conversionForm(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function pageSize(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function language(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function Printability(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function microformats(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function dublinCore(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function domainAvailability(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function domainAvailability(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function emailPrivacy(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function spamBlock(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function serverIp(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function technologies(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function speedTips(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function analisys(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function w3cValidity(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function doctype(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function encoding(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function directoryBrowsing(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function serverSignature(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function socialShareability(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function twitterAccount(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function facebookPage(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function googlePage(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		function localDirectories(content) {
			var score = 0;
			var description = '';

			if(!content) {
				content = document.body;		
			}
			
			return {
				"score" : score
				, "description" : description
			};
		};
		
		var status = '';
		var res = {};
		if(!params)
			params = {};
		
		if(params["external"]) {
			res = this.getResponseByPageSpeed(params["external"]);
		} else {
			switch(type) {
				case "title":
					res = title(content);
					break;
				case "description":
					res = description(content);
					break;
				case "headings":
					res = headings(content);
					break;
				case "keywords-cloud":
					res = keyWordsCloud(content, params["limit"], params["grab"]);
					break;
				case "keywords-consistency":
					res = keyWordsConsistency(content, params["keyCompareFrom"], params["limit"], params["grab"]);
					break;
				case "images":
					//res = images(content);
					break;
				case "text-html-ratio":
					//res = textHtmlRatio(content);
					break;
				case "google-publisher":
					//res = googlePublisher(content);
					break;
				case "in-page-links":
					//res = inPageLinks(content);
					break;
				case "broken-links":
					//res = brokenLinks(content);
					break;
				case "www-resolve":
					//res = wwwResolve(content);
					break;
				case "ip-canonicalization":
					//res = ipCanonicalization(content);
					break;
				case "robots-txt":
					//res = robotsTxt(content);
					break;
				case "xml-sitemap":
					//res = xmlSitemap(content);
					break;
				case "url-rewrite":
					//res = urlRewrite(content);
					break;
				case "underscores-in-the-urls":
					//res = underscoresInTheUrls(content);
					break;
				case "flash":
					//res = flash(content);
					break;
				case "domain-expiration":
					//res = domainExpiration(content);
					break;
				case "blog":
					//res = blog(content);
					break;
				case "mobile-rendering":
					//res = mobileRendering(content);
					break;
				case "mobile-load-time":
					//res = mobileLoadTime(content);
					break;
				case "mobile-optimization":
					//res = mobileOptimization(content);
					break;
				case "url":
					//res = url(content);
					break;
				case "favicon":
					//res = favicon(content);
					break;
				case "custom-404-page":
					//res = custom404Page(content);
					break;
				case "conversion-form":
					//res = conversionForm(content);
					break;
				case "page-size":
					//res = pageSize(content);
					break;
				case "language":
					//res = language(content);
					break;
				case "printability":
					//res = Printability(content);
					break;
				case "microformats":
					//res = microformats(content);
					break;
				case "dublin-core":
					//res = dublinCore(content);
					break;
				case "domain-availability":
					//res = domainAvailability(content);
					break;
				case "typo-availability":
					//res = typoAvailability(content);
					break;
				case "email-privacy":
					//res = emailPrivacy(content);
					break;
				case "spam-block":
					//res = spamBlock(content);
					break;
				case "safe-browsing":
					//res = safeBrowsing(content);
					break;
				case "server-ip":
					//res = serverIp(content);
					break;
				case "technologies":
					//res = technologies(content);
					break;
				case "speed-tips":
					//res = speedTips(content);
					break;
				case "analisys":
					//res = analisys(content);
					break;
				case "w3c-validity":
					//res = w3cValidity(content);
					break;
				case "doctype":
					//res = doctype(content);
					break;
				case "encoding":
					//res = encoding(content);
					break;
				case "directory-browsing":
					//res = directoryBrowsing(content);
					break;
				case "serverSignature":
					//res = server-signature(content);
					break;
				case "social-shareability":
					//res = socialShareability(content);
					break;
				case "twitter-account":
					//res = twitterAccount(content);
					break;
				case "facebook-page":
					//res = facebookPage(content);
					break;
				case "google-page":
					//res = googlePage(content);
					break;
				case "local-directories":
					//res = localDirectories(content);
					break;
				default:
					res = {};
			}
		}
		
		switch(res["score"]) {
			case 0:
				status = "error";
				this.total.scoreTotal += (2 * (params["importance"] || 0));

				this.total.error++;
				this.total.count++;
				break;
			case 1:
				status = "warning";
				this.total.score += (res["score"] * (params["importance"] || 0));
				this.total.scoreTotal += (2 * (params["importance"] || 0));

				this.total.warning++;
				this.total.count++;
				break;
			case 2:
				status = "success";
				this.total.score += (res["score"] * (params["importance"] || 0));
				this.total.scoreTotal += (2 * (params["importance"] || 0));

				this.total.success++;
				this.total.count++;
				break;
			default:		
				status = "standby";
		}

		if(!jQuery(".seo-analysis-status", elem).length)
			jQuery(elem).append('<span class="seo-analysis-status" />');

		if(!jQuery(".seo-analysis-content", elem).length)
			jQuery(elem).append('<span class="seo-analysis-content" />');

		jQuery(".seo-analysis-status", elem).html(that.statusIcons[status]);
		jQuery(".seo-analysis-content", elem).html(res["description"]);
		
		return res["score"];
	}, 
	getResponseByPageSpeed : function(type) {
		var res = {};
		if(this.gPageSpeed.responseD) {
		//console.log(this.gPageSpeed.responseD.formattedResults.ruleResults[type]);
			if(this.gPageSpeed.responseD.formattedResults.ruleResults[type]) {
				res["description"] = this.gPageSpeed.responseD.formattedResults.ruleResults[type]["localizedRuleName"];
				this.gPageSpeed.responseD.formattedResults.ruleResults[type]["urlBlocks"].each(function(key, urlBlocks) {
					
				});

				if(!this.gPageSpeed.responseD.formattedResults.ruleResults[type]["ruleImpact"])
					res["score"] = 2;
				else if(this.gPageSpeed.responseD.formattedResults.ruleResults[type]["ruleImpact"] < 1)
					res["score"] = 1;
				else if(this.gPageSpeed.responseD.formattedResults.ruleResults[type]["ruleImpact"] > 1)
					res["score"] = 0;
			} else {
				res["score"] = 2;
			}
		} else {
			res["description"] = "Need PageSpeed Api Key";
		}
		return res;
	}
};