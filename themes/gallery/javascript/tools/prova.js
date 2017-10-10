document.observe('dom:loaded', function() {
    var fatherElem = document.getElementsByTagName("body").item(0);
    var newEL = "";
    var tagHTML = new Array(); 
    var atrHTML = new Array(); 
    var a;

    var prefix = "pct";
    
    tagHTML = ["a", 
                "abbr", 
                "acronym", 
                "address", 
                "area", 
                "b", 
                "base", 
                "bdo", 
                "big", 
                "blockquote", 
                "body", 
                "br", 
                "button", 
                "caption", 
                "cite", 
                "code", 
                "col", 
                "colgroup", 
                "dd", 
                "del", 
                "div", 
                "dfn", 
                "dl", 
                "dt", 
                "em", 
                "fieldset", 
                "form", 
                "frame", 
                "frameset", 
                "h1", 
                "h2", 
                "h3", 
                "h4", 
                "h5", 
                "h6", 
                "hr", 
                "i", 
                "iframe", 
                "img", 
                "input", 
                "ins", 
                "kbd", 
                "label", 
                "legend", 
                "li", 
                "link", 
                "map", 
                "meta", 
                "noframes", 
                "noscript", 
                "ol", 
                "p", 
                "pre", 
                "q", 
                "samp", 
                "script", 
                "select", 
                "small", 
                "span", 
                "strong", 
                "style", 
                "sub", 
                "sup", 
                "table", 
                "tbody", 
                "td", 
                "textarea", 
                "tfoot", 
                "th", 
                "thead", 
                "tr", 
                "tt", 
                "ul", 
                "var"
              ];
    
    atrHTML = ["class",
                "style"
               ];
    
    newEl = document.createElement('div');
    //newEl.style.width = "100px";
    newEl.setAttribute('class', prefix + '_interface');
    newEl.style.position = "fixed";
    newEl.style.left = "0px";
    newEl.style.top = "0px";
    newEl.style.zIndex = 999;
    fatherElem.appendChild(newEl);

    
    fatherElem = newEl;
    
    newEl = document.createElement('h4');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.innerHTML = "Debugger";
    fatherElem.appendChild(newEl);
    
    
    newEl = document.createElement('fieldset');
    newEl.setAttribute('class', prefix + '_interface');
    fatherElem.appendChild(newEl);
    
    fatherElem = newEl;

    newEl = document.createElement('legend');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.innerHTML = "Tag Elements";
    fatherElem.appendChild(newEl);
    
    newEl = document.createElement('div');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.style.height = "200px";       
    newEl.style.overflow = "auto";
    newEl.style.textAlign = "left";
    fatherElem.appendChild(newEl);
    
    fatherElem = newEl;


    for (a = 0; a < tagHTML.length; a++) {
   
        newEl = document.createElement('input');
        newEl.setAttribute('class', prefix + '_interface');
        newEl.setAttribute("type", "checkbox");
        newEl.setAttribute("id", prefix + "tag" + a);
        newEl.setAttribute("value", tagHTML[a]);
        fatherElem.appendChild(newEl);
        
        newEl = document.createElement('label');
        newEl.setAttribute('class', prefix + '_interface');
        newEl.innerHTML = tagHTML[a];
        newEl.style.fontVariant = "small-caps";
        fatherElem.appendChild(newEl);

        newEl = document.createElement('br');
        newEl.setAttribute('class', prefix + '_interface');
        fatherElem.appendChild(newEl);

        
    }     
    
    fatherElem = fatherElem.parentNode.parentNode;
    
    newEl = document.createElement('fieldset');
    newEl.setAttribute('class', prefix + '_interface');
    fatherElem.appendChild(newEl);
    
    fatherElem = newEl;

    newEl = document.createElement('legend');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.innerHTML = "Attr Elements";
    fatherElem.appendChild(newEl);
    
    newEl = document.createElement('div');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.style.textAlign = "left";
    fatherElem.appendChild(newEl);
    
    fatherElem = newEl;

    
    for (a = 0; a < atrHTML.length; a++) {
        newEl = document.createElement('input');
        newEl.setAttribute('class', prefix + '_interface');
        newEl.setAttribute("type", "checkbox");
        newEl.setAttribute("id", prefix + "atr" + a);
        newEl.setAttribute("value", atrHTML[a]);
        fatherElem.appendChild(newEl);
        
        newEl = document.createElement('label');
        newEl.setAttribute('class', prefix + '_interface');
        newEl.innerHTML = atrHTML[a];
        newEl.style.fontVariant = "small-caps";
        fatherElem.appendChild(newEl);

        newEl = document.createElement('br');
        newEl.setAttribute('class', prefix + '_interface');
        fatherElem.appendChild(newEl);
    }         

     fatherElem = fatherElem.parentNode.parentNode;

    newEl = document.createElement('div');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.style.textAlign = "left";
    fatherElem.appendChild(newEl);
    
    fatherElem = newEl;

    newEl = document.createElement('input');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.setAttribute("type", "button");
    newEl.setAttribute("name", "debugstart");
    newEl.setAttribute("value", "start");
    fatherElem.appendChild(newEl);

    $(newEl).observe('click', function(event) {
        var filterTagParam = "";
        var filterAtrParam = "";
        
        for (a = 0; a < tagHTML.length; a++) {
            if(document.getElementById(prefix + "tag" + a).checked == true) {
                filterTagParam = filterTagParam + "," + tagHTML[a];
            }
        }

        for (a = 0; a < atrHTML.length; a++) {
            if(document.getElementById(prefix + "atr" + a).checked == true) {
                filterAtrParam = filterAtrParam + "," + atrHTML[a];
            }
        }

        debugClearElements(prefix + "_debug");
        treeTraverseIterative2(document.getElementsByTagName("body").item(0), filterTagParam, filterAtrParam, prefix);

    });
     
    newEl = document.createElement('input');
    newEl.setAttribute('class', prefix + '_interface');
    newEl.setAttribute("type", "button");
    newEl.setAttribute("name", "debugstart");
    newEl.setAttribute("value", "clear");
    fatherElem.appendChild(newEl);
    
    $(newEl).observe('click', function(event) {
        debugClearElements(prefix + "_debug");
    });
});

function debugClearElements(className) {
    var fatherElem = "";

    $$('.' + className).each(function(element) {
            fatherElem = element.parentNode;

            fatherElem.removeChild(element);
        });

}


function treeTraverseIterative2(elem, filterTagParam, filterAtrParam, prefix) {
    var queue = new Array();
    var i, currentElem, childs;
    var count = 0;

    dialogLoading(true);
    queue.push(elem);
    while (queue.length > 0) {
        count++;
        currentElem = queue.pop();
        visit2(currentElem, count, filterTagParam, filterAtrParam, prefix);
        childs = currentElem.childNodes.length;
        for (i = 0; i < childs; i++) {
            queue.push(currentElem.childNodes[i]);
        }
    }
    dialogLoading(false);
}

function visit2(elem, count, filterTagParam, filterAtrParam, prefix) {
//alert(filterTagParam + "\n" + filterAtrParam + "\n" + prefix + "\n" + elem.nodeName);

    if(elem.nodeName != "#text" 
        && elem.nodeName != "#comment" 
        && elem.className != prefix + "_interface"
        && (
            (filterTagParam.search("," + elem.nodeName.toLowerCase()) >= 0)
            ||
                (
                 (elem.className != "")
                 &&
                 (filterAtrParam.search("class") >= 0)
                )
           )
    ) {
    
    
 //    if((elem.nodeName != "#text" && elem.nodeName != "#comment") && elem.nodeName != "SCRIPT" && elem.nodeName != "STYLE" && elem.className != "") {
        var fatherElem = "";
        var newEl = "";
        var bgcolor = "";
        var multi = 6;
        var elemData = "";
     
        fatherElem = document.getElementsByTagName("body").item(0);

        newEl = document.createElement('div');
        newEl.setAttribute('class', prefix + '_debug');
        newEl.style.opacity = ".80";
        newEl.style.border = "1px solid #000000";
        fatherElem.appendChild(newEl);

        
        if(elem.className == "") {
            bgcolor = elem.nodeName;
            elemData = elem.nodeName;
        } else {
            bgcolor = elem.className;
            elemData = "(" + elem.nodeName + ") " + elem.className;
        }
        
        bgcolor = Math.round((bgcolor.charCodeAt(0) + bgcolor.charCodeAt(Math.round(bgcolor.length/2) -1) + bgcolor.charCodeAt(bgcolor.length -1)) / 3) + bgcolor.length;
        
        var primo = (bgcolor % multi) * 51
        var secondo = ((bgcolor + 2) % multi) * 51
        
        newEl.style.backgroundColor =  "rgb(" + "255" + ", " + primo + ", " + secondo + ")";
        newEl.style.position = "absolute";
        newEl.innerHTML = elemData;
        newEl.style.overflow = "hidden";
        newEl.style.zIndex = count;
       
       newEl.clonePosition(elem);
       newEl.absolutize(elem);
       
//       elem.style.visibility = "false";
    }    
}

function dialogLoading(visible) {
    var fatherElem = document.getElementsByTagName("body").item(0);
    var newEl = "";

    if(visible) {
        newEl = document.createElement('div');
        newEl.setAttribute("id", "dialogLoading");
        newEl.style.position = "absolute";
        newEl.style.zIndex = 998;
        newEl.style.opacity = ".80";
        newEl.style.backgroundColor = "black";
        newEl.style.height = document.viewport.getHeight();
        newEl.style.width = document.viewport.getWidth();
        newEl.style.left = 0;
        newEl.style.top = 0;
        fatherElem.appendChild(newEl);
    } else {
        newEl = document.getElementById("dialogLoading");
        fatherElem.removeChild(newEl);  
    }
}
