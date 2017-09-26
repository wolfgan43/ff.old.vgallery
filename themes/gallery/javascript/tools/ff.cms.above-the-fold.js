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
ff.cms.getAboveTheFold = function(domElem) {
	var CSSCriticalPath = function(w, d, opts) {
	    var opt = opts || {};
	    var css = {};
	    var pushCSS = function(r) {
	      if(!!css[r.selectorText] === false) css[r.selectorText] = {};
	      var styles = r.style.cssText.split(/;(?![A-Za-z0-9])/);
	      for(var i = 0; i < styles.length; i++) {
	        if(!!styles[i] === false) continue;
	        var pair = styles[i].split(": ");
	        pair[0] = pair[0].trim(); 
	        pair[1] = pair[1].trim();
	        css[r.selectorText][pair[0]] = pair[1];
	      }
	    };
	    
	    var parseTree = function() { 
	      // Get a list of all the elements in the view.
	      var height = w.innerHeight;
	      var walker = d.createTreeWalker(d, NodeFilter.SHOW_ELEMENT, function(node) { return NodeFilter.FILTER_ACCEPT; }, true);
	  
	      while(walker.nextNode()) {
	        var node = walker.currentNode;
	        var rect = node.getBoundingClientRect();
	        if(rect.top < height || opt.scanFullPage) {
	          var rules = w.getMatchedCSSRules(node);
	          if(!!rules) {
	            for(var r = 0; r < rules.length; r++) {
	              pushCSS(rules[r]); 
	            }
	          }
	        } 
	      }
	    };
	   
	    this.generateCSS = function() {
	      var finalCSS = "";
	      for(var k in css) {
	        finalCSS += k + " { ";
	        for(var j in css[k]) {
	          finalCSS += j + ": " + css[k][j] + "; ";
	        }
	        finalCSS += "}\n";
	      }
	      
	      return finalCSS;
	    };
	    
	    parseTree();
  };
  if(!domElem)
  	domElem = document;

  var cp = new CSSCriticalPath(window, domElem);
  
  return cp.generateCSS();
};