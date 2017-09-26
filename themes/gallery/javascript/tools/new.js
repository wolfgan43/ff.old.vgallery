function cssInspector(firstElem) {
    var _tagHTML = ""; 
    var _atrHTML = "class"; 
    var _classHTML = "";
    var _idHTML = "";
    
    var _prefix = "cssIns";

    var _filterTag = "";
    var _filterAtr = "";
    var _filterClass = "";
    var _filterId = "";
    
    var mouseOverHandler = function(event){
      var elem =  $(Event.element(event));
      
        switch ($(elem).className) {
            case _prefix + "_debug":
                /*Tips.remove($(elem));*/
                if($(elem).prototip) {
                } else {
                new Tip($(elem), $(getInfo($(elem))), {
                    title : 'Information by Element'
                    , hideOn: { element: 'closeButton' }
                    , showOn: 'click'
                    , width: 'auto' /* We don't want the default 250px.
                                   // Images inside the tooltip will need to have dimensions set since Prototip needs to fixate width for proper rendering. */
                    , hook: { tip: 'topMiddle', mouse: true }
                    , stem: 'topMiddle'
                    , offset: { x: 7, y: 18 }
                }); 
                }
                /*Tips.shown($(elem));

//                getInfo(elem);*/
                break;
        }
    };

    var mouseClickHandler = function(event){
      var elem =  $(Event.element(event));

        var tagHTML = new Array();
        var atrHTML = new Array();
        var classHTML = new Array();
        var idHTML = new Array();
        var a = 0;
        
        tagHTML = _tagHTML.split(",");
        atrHTML = _atrHTML.split(",");
        classHTML = _classHTML.split(",");
        idHTML = _idHTML.split(",");
        tagHTML.sort();
        atrHTML.sort();
        classHTML.sort();
        idHTML.sort();

      
      switch ($(elem).id) {
        case _prefix + "_interface":
        case _prefix + "_information":
            display($(elem.id + "_detail"));
            
            break;
        case _prefix + "_start":
            createFilterElemCss(false);
            break;
        case _prefix + "_clear":
            clearElementsCss();

            for (a = 0; a < tagHTML.length; a++) {
                $(_prefix + "tag" + a).checked = false;
            }
            
            for (a = 0; a < atrHTML.length; a++) {
                $(_prefix + "atr" + a).checked = false;
            } 

            for (a = 0; a < classHTML.length; a++) {
                $(_prefix + "class" + a).checked = false;
            } 

            for (a = 0; a < idHTML.length; a++) {
                $(_prefix + "id" + a).checked = false;
            } 

            break;
        default:
            createFilterElemCss($(elem).rel);
            break;
      }
      
    };
    
    var getInfo = function(elem) {
    /*da fare col tooltip tutto*/
        var mainElem = "";
        var target = "";
        var path = $(elem.firstDescendant()).value.split(",");
        var subPath = "";
        var subPathClass = "";
        var subPathClassValue = "";
        var newEl = "";
        var relPath = "";

        
        mainElem = document.createElement('div');
        $(mainElem).addClassName(_prefix + '_interface');
        $(mainElem).style.backgroundColor = $(elem).style.backgroundColor;
        $(mainElem).style.textAlign = "left";
        /*$(target).innerHTML = "";*/
        
        newEl = document.createElement('div');
        $(mainElem).appendChild(newEl);    

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).innerHTML = "Left: " + $(elem).positionedOffset("left") + " Top: " + $(elem).positionedOffset("top") + "\nWidth: " + $(elem).getWidth() + " Height: " + $(elem).getHeight();
         /* da fare meglio*/
        
        target = $(mainElem);
        path.reverse();
        while (path.length > 0) { 
            newEl = document.createElement('ul');
            $(target).appendChild(newEl);    

            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).style.marginLeft = "4px";
            $(newEl).style.paddingLeft = "0";

            target = $(newEl);
            
            newEl = document.createElement('li');
            $(target).appendChild(newEl);    
            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).style.listStylePosition = "inside";
            $(newEl).style.listStyleType = "none";
            
            target = $(newEl);
        
            subPath = path.pop();
            if(relPath == "")
                relPath = relPath + subPath;
            else
                relPath = "," + relPath + subPath;
            
            subPath = subPath.split(":");
            subPath.reverse();

            while (subPath.length > 0) { 
                subPathClass = subPath.pop();

                subPathClass = subPathClass.split(" ");
                subPathClass.reverse();
                while (subPathClass.length > 0) {
                    subPathClassValue = subPathClass.pop(); 
                    if(subPathClassValue) {
                        newEl = document.createElement('a');
                        newEl.setAttribute("href", "#");
                        newEl.setAttribute("rel", relPath);
                        $(target).appendChild(newEl);    

                        $(newEl).addClassName(_prefix + '_interface');
                        $(newEl).innerHTML = subPathClassValue;
                        $(newEl).observe('click', mouseClickHandler);

                        newEl = document.createElement('br');
                        $(target).appendChild(newEl);    
                        $(newEl).addClassName(_prefix + '_interface');
                    }
                }
            }
        }

        return $(mainElem);
    };

    var display = function(target) {
        if($(target).style.display == "none") {
            $(target).style.display = "block";
        } else {
            $(target).style.display = "none";
        }
    };

    var setFilterElem = function(filterElem) {
        
    
    
    };
    
    var travelDom = function(fatherElem, action) {
        var queue = new Array();
        var i, currentElem, childs;
        var count = 0;

        queue.push(fatherElem);
        while (queue.length > 0) {
            count++;
            currentElem = queue.pop();
            switch(action) {
                case "inspectHtml":
                    inspectTagHtml(currentElem, count);
                    inspectClassHtml(currentElem, count);
                    inspectIdHtml(currentElem, count);
                    break;
                case "createCssElem":
                    createCssElem(currentElem, count);
                    break;
                    
                default:
            }
            childs = currentElem.childNodes.length;
            for (i = 0; i < childs; i++) {
                queue.push(currentElem.childNodes[i]);
            }
        }
    };
    
    var createCssElem = function(elem, count) {
        var fatherElem = firstElem;
        
        var newEl = "";
        var subNewEl = "";
        var bgcolor = "";
        var primo = 0;
        var secondo = 0;
        var multi = 6;
        var currElem = "";
        var treeElem = ""
        var treeElemValue = "";
        var subNewEl = "";
        var checkElem = false;
    
        if(elem.nodeName != "#text" 
            && elem.nodeName != "#comment" 
            && $(elem).hasClassName(_prefix + "_interface") === false 
        ) {

            if(_filterTag.search(":" + elem.nodeName.toLowerCase() + ":") >= 0)
                checkElem = true;

            if(_filterAtr.search(":" + "class" + ":") >= 0 && elem.className != "")
                checkElem = true;
            
            if(_filterId.search(":" + elem.id + ":") >= 0)
                checkElem = true;
                
            treeElem = $(elem).className.split(" ");
            treeElemValue = "";
            
            while (treeElem.length > 0) {      
                treeElemValue = treeElem.pop();
                if(_filterClass.search(":" + treeElemValue + ":") >= 0)
                    checkElem = true;
            }

            if(checkElem) {        
                newEl = document.createElement('div');
                fatherElem.appendChild(newEl);    

                $(newEl).addClassName(_prefix + '_debug');
                $(newEl).style.position = "absolute";
                $(newEl).clonePosition(elem);  
                
                                                   
                
                if(elem.className == "") {
                    bgcolor = elem.nodeName;
                } else {
                    bgcolor = elem.className;
                }

                
                bgcolor = Math.round((bgcolor.charCodeAt(0) + bgcolor.charCodeAt(Math.round(bgcolor.length/2) -1) + bgcolor.charCodeAt(bgcolor.length -1)) / 3) + bgcolor.length;
                primo = (bgcolor % multi) * 51;
                secondo = ((bgcolor + 2) % multi) * 51;

                if($(newEl).getHeight() <= 1 || $(newEl).getWidth() <= 1) {
                    $(newEl).style.height = "16px";
                    $(newEl).style.width = "16px";
                    $(newEl).style.backgroundImage = "url(http://" + ff.domain + ff.base_path + "/themes/gallery/images/edit.gif)";
                    $(newEl).style.backgroundColor =  "rgb(" + "255" + ", " + "255" + ", " + "255" + ")";
                    $(newEl).style.opacity = "1";
                } else {
                    $(newEl).style.opacity = ".80";
                    $(newEl).style.backgroundColor =  "rgb(" + "255" + ", " + primo + ", " + secondo + ")";
                }

                $(newEl).style.border = "1px solid #000000";
                $(newEl).style.overflow = "hidden";
                $(newEl).style.zIndex = count;

               /* $(newEl).observe('click', mouseOverHandler);*/

                treeElem = $(elem).ancestors();
                treeElemValue = "";
                
                while (treeElem.length > 0) {      
                    currElem = treeElem.pop();
                    if(treeElemValue == "")
                        treeElemValue = currElem.nodeName + ":" + (currElem.className == "" ? "" : "." + currElem.className.replace(" ", " .")) + ":" + (currElem.id == "" ? "" : "#" + currElem.id);
                    else
                        treeElemValue = treeElemValue + ", " + currElem.nodeName + ":" + (currElem.className == "" ? "" : "." + currElem.className.replace(" ", " .")) + ":" + (currElem.id == "" ? "" : "#" + currElem.id);
                }

                treeElemValue = treeElemValue + ", " + elem.nodeName + ":" + (elem.className == "" ? "" : "." + elem.className.replace(" ", " .")) + ":" + (elem.id == "" ? "" : "#" + elem.id);            
                
                subNewEl = document.createElement('input');
                subNewEl.setAttribute("type", "hidden");
                subNewEl.setAttribute("value", treeElemValue);
                $(newEl).appendChild(subNewEl);

                $(subNewEl).addClassName(_prefix + '_debug');
                
                treeElem = "";
                treeElemValue = "";
                
                subNewEl = document.createElement('a');
                subNewEl.setAttribute("href", "#");
                subNewEl.setAttribute("rel", elem.nodeName);
                $(newEl).appendChild(subNewEl);    

                $(subNewEl).addClassName(_prefix + '_interface');
                $(subNewEl).innerHTML = elem.nodeName;
                
                $(subNewEl).observe('click', mouseClickHandler);
                
                if(elem.className != "") {
                    treeElem = $(elem).className.split(" ");
                    
                    while (treeElem.length > 0) {      
                        treeElemValue = treeElem.pop();

                        subNewEl = document.createElement('a');
                        subNewEl.setAttribute("href", "#");
                        subNewEl.setAttribute("rel", "." + treeElemValue);
                        $(newEl).appendChild(subNewEl);    

                        $(subNewEl).addClassName(_prefix + '_interface');
                        $(subNewEl).innerHTML = "." + treeElemValue;
                        
                        $(subNewEl).observe('click', mouseClickHandler);
                    }
                }
                
                if(elem.id != "") {
                    subNewEl = document.createElement('a');
                    subNewEl.setAttribute("href", "#");
                    subNewEl.setAttribute("rel", "#" + elem.id);
                    $(newEl).appendChild(subNewEl);    

                    $(subNewEl).addClassName(_prefix + '_interface');
                    $(subNewEl).innerHTML = "#" + elem.id;
                    
                    $(subNewEl).observe('click', mouseClickHandler);
                }
                
                $(newEl).observe('mousemove', mouseOverHandler);
                /*
                new Tip($(newEl), $(getInfo($(newEl))), {
                    title : 'Information by Element'
                    , hideOn: { element: 'closeButton', event: 'click' }
                    , showOn: 'click'
                    , width: 'auto' // We don't want the default 250px.
                                   // Images inside the tooltip will need to have dimensions set since Prototip needs to fixate width for proper rendering.
                    , hook: { tip: 'topMiddle', mouse: true }
                    , stem: 'topMiddle'
                    , offset: { x: 7, y: 18 }
                });  */
            }    
        }
    };
    
    var inspectTagHtml = function(elem, count) {
       if($(elem).nodeName != "#text" 
            && $(elem).nodeName != "#comment" 
            && $(elem).hasClassName(_prefix + "_interface") == false
            && _tagHTML.search("," + $(elem).nodeName.toLowerCase()) < 0
       ) {
            if(_tagHTML == "")
                _tagHTML = $(elem).nodeName.toLowerCase();
            else 
                _tagHTML = _tagHTML + "," + $(elem).nodeName.toLowerCase();
       }
    };
    
    var inspectClassHtml = function(elem, count) {     
        var treeClass = "";
        var treeClassValue = "";
        
        if(elem.nodeName != "#text" 
            && elem.nodeName != "#comment" 
            && elem.hasClassName(_prefix + "_interface") == false
        ) {

            treeClass = elem.className.split(" ");
            while (treeClass.length > 0) {
                treeClassValue = treeClass.pop();
                if(_classHTML == "")
                    _classHTML = treeClassValue;
                else if(_classHTML.search("," + treeClassValue) < 0)
                    _classHTML = _classHTML + "," + treeClassValue;
            }
       }   
    };

    var inspectIdHtml = function(elem, count) {     
        if(elem.nodeName != "#text" 
            && elem.nodeName != "#comment" 
            && elem.hasClassName(_prefix + "_interface") == false
            && _idHTML.search("," + elem.id) < 0
            && elem.id != ""
        ) {

            if(_idHTML == "")
                _idHTML = elem.id;
            else 
                _idHTML = _idHTML + "," + elem.id;
       }    
    };
    
    
    var clearElementsCss = function() {
        var className = _prefix + "_debug"
        var fatherElem = firstElem;
        
        Tips.removeAll();
        
        $$('.' + className).each(function(element) {
            fatherElem = element.parentNode;
            fatherElem.removeChild(element);
        });
    };

       
    var createFilterElemCss = function(filter) {    
        var tagHTML = new Array();
        var atrHTML = new Array();
        var classHTML = new Array();
        var idHTML = new Array();
        var a = 0;
        
        var subFilter = "";
        var subFilterClass = "";
        var subFilterValue = "";
        
        /*Tips.removeAll();*/
        
        tagHTML = _tagHTML.split(",");
        atrHTML = _atrHTML.split(",");
        classHTML = _classHTML.split(",");
        idHTML = _idHTML.split(",");
        tagHTML.sort();
        atrHTML.sort();
        classHTML.sort();
        idHTML.sort();

        _filterTag = ":";
        _filterAtr = ":";
        _filterClass = ":";
        _filterId = ":";

        if(filter) {
            filter = filter.split(",");
            filter.reverse();
            while(filter.length > 0) { 
                subFilter = filter.pop();
                subFilter = subFilter.split(":");
                subFilter.reverse();

                while (subFilter.length > 0) { 
                    subFilterClass = subFilter.pop();

                    subFilterClass = subFilterClass.split(" ");
                    subFilterClass.reverse();

                    while (subFilterClass.length > 0) {
                        subFilterValue = subFilterClass.pop(); 

                        switch(subFilterValue.substr(0,1)) {
                            case ".":
                                if(_filterClass.search(":" + subFilterValue.substr(1) + ":") < 0) 
                                    _filterClass = _filterClass + subFilterValue.substr(1) + ":";  
                                break;
                            case "#":
                                if(_filterId.search(":" + subFilterValue.substr(1) + ":") < 0) 
                                    _filterId = _filterId + subFilterValue.substr(1) + ":";
                                break;
                            default:
                                if(_filterTag.search(":" + subFilterValue.toLowerCase() + ":") < 0) 
                                    _filterTag = _filterTag + subFilterValue.toLowerCase()  + ":";
                        }
                    }
                }
            }
            
            for (a = 0; a < tagHTML.length; a++) {
                if(_filterTag.search(":" + tagHTML[a] + ":") >= 0) {
                    $(_prefix + "tag" + a).checked = true;
                } else {
                    $(_prefix + "tag" + a).checked = false;
                }
            }

            for (a = 0; a < atrHTML.length; a++) {
                $(_prefix + "atr" + a).checked = false;
            } 

            for (a = 0; a < classHTML.length; a++) {
                if(_filterClass.search(":" + classHTML[a] + ":") >= 0) {
                    $(_prefix + "class" + a).checked = true;
                } else {
                    $(_prefix + "class" + a).checked = false;
                }
            } 

            for (a = 0; a < idHTML.length; a++) {
                if(_filterId.search(":" + idHTML[a] + ":") >= 0) {
                    $(_prefix + "id" + a).checked = true;
                } else {
                    $(_prefix + "id" + a).checked = false;
                }
            } 
        } else {
            for (a = 0; a < tagHTML.length; a++) {
                if($(_prefix + "tag" + a).checked == true) {
                    _filterTag = _filterTag + tagHTML[a] + ":";
                }
            }
            
            for (a = 0; a < atrHTML.length; a++) {
                if($(_prefix + "atr" + a).checked == true) {
                    _filterAtr = _filterAtr + atrHTML[a] + ":";
                }
            } 

            for (a = 0; a < classHTML.length; a++) {
                if($(_prefix + "class" + a).checked == true) {
                    _filterClass = _filterClass + classHTML[a] + ":";
                }
            } 

            for (a = 0; a < idHTML.length; a++) {
                if($(_prefix + "id" + a).checked == true) {
                    _filterId = _filterId + idHTML[a] + ":";
                }
            } 
        }
        
        if(_filterTag == ":")
            _filterTag = "";
        
        if(_filterAtr == ":")
            _filterAtr = "";

        if(_filterClass == ":")
            _filterClass = "";

        if(_filterId == ":")
            _filterId = "";
        
        clearElementsCss();
        travelDom(firstElem, "createCssElem");
    };

    var loading = function loading(display) {
        var newEl = "";
/*se funzionasse sarebbe magnifico*/
        if(display == true) {
            newEl = document.createElement('div');
            newEl.setAttribute("id", _prefix + '_loading');
            $(firstElem).appendChild(newEl);
            
            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).style.position = "absolute";
            $(newEl).style.left = "0px";
            $(newEl).style.top = "0px";
            $(newEl).style.backgroundColor = "#000000";
            $(newEl).style.width = document.viewport.getWidth() + "px";
            $(newEl).style.height = document.viewport.getHeight() + "px";
            $(newEl).style.zIndex = 9999;
        } else if(display == false) {
            $(firstElem).removeChild($(_prefix + '_loading'));
        }

    }
   
    function initInterface() {
        var newEl = "";
        var fatherElem = firstElem;
        var tagHTML = new Array();
        var atrHTML = new Array();
        var a = 0;
       
        tagHTML = _tagHTML.split(",");
        atrHTML = _atrHTML.split(",");
        classHTML = _classHTML.split(",");
        idHTML = _idHTML.split(",");
        
        tagHTML.sort();
        atrHTML.sort();
        classHTML.sort();
        idHTML.sort();

        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);
        
        $(newEl).addClassName(_prefix + '_interface');
        
        $(newEl).style.position = "absolute";
        $(newEl).style.left = "0px";
        $(newEl).style.top = "20px";
        $(newEl).style.zIndex = 9998;

        
        fatherElem = newEl;
        
        newEl = document.createElement('h4');
        newEl.setAttribute('id', _prefix + "_interface");
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.cursor = 'pointer';
        $(newEl).style.maxWidth = "200px";
        $(newEl).innerHTML = "CSS Inspector";
        $(newEl).observe('click', mouseClickHandler);       

        newEl = document.createElement('div');
        newEl.setAttribute('id', _prefix + "_interface_detail");
        fatherElem.appendChild(newEl);
        
        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.maxWidth = "200px";
                
        fatherElem = newEl;
        
        newEl = document.createElement('fieldset');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        
        fatherElem = newEl;

        newEl = document.createElement('legend');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).innerHTML = "Tag Elements";
        
        
        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.maxHeight = "200px";       
        $(newEl).style.overflow = "auto";
        $(newEl).style.textAlign = "left";
        
        
        fatherElem = newEl;

        for (a = 0; a < tagHTML.length; a++) {
            newEl = document.createElement('input');
            newEl.setAttribute("type", "checkbox");
            newEl.setAttribute("id", _prefix + "tag" + a);
            newEl.setAttribute("value", tagHTML[a]);
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');

            
            newEl = document.createElement('label');
            fatherElem.appendChild(newEl);
            
            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).innerHTML = tagHTML[a];
            $(newEl).style.fontVariant = "small-caps";
            
            newEl = document.createElement('br');
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');
            
        }     
        
        fatherElem = fatherElem.parentNode.parentNode;
        
        newEl = document.createElement('fieldset');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        
        fatherElem = newEl;

        newEl = document.createElement('legend');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).innerHTML = "Attr Elements";

        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.maxHeight = "200px";
        $(newEl).style.overflow = "auto";
        $(newEl).style.textAlign = "left";
        
        fatherElem = newEl;
        
        for (a = 0; a < atrHTML.length; a++) {
            newEl = document.createElement('input');
            newEl.setAttribute("type", "checkbox");
            newEl.setAttribute("id", _prefix + "atr" + a);
            newEl.setAttribute("value", atrHTML[a]);
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');

            
            newEl = document.createElement('label');
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).innerHTML = atrHTML[a];
            $(newEl).style.fontVariant = "small-caps";

            newEl = document.createElement('br');
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');

        }         

        fatherElem = fatherElem.parentNode.parentNode;

        newEl = document.createElement('fieldset');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        
        fatherElem = newEl;

        newEl = document.createElement('legend');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).innerHTML = "Class Elements";
        
        
        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.maxHeight = "200px";
        $(newEl).style.overflow = "auto";
        $(newEl).style.textAlign = "left";
        
        
        fatherElem = newEl;

        for (a = 0; a < classHTML.length; a++) {
            newEl = document.createElement('input');
            newEl.setAttribute("type", "checkbox");
            newEl.setAttribute("id", _prefix + "class" + a);
            newEl.setAttribute("value", classHTML[a]);
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');

            
            newEl = document.createElement('label');
            fatherElem.appendChild(newEl);
            
            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).innerHTML = classHTML[a];
            /*$(newEl).style.fontVariant = "small-caps";*/
            
            newEl = document.createElement('br');
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');
            
        }     
        
        fatherElem = fatherElem.parentNode.parentNode;


        newEl = document.createElement('fieldset');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        
        fatherElem = newEl;

        newEl = document.createElement('legend');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).innerHTML = "ID Elements";
        
        
        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.maxHeight = "200px";
        $(newEl).style.overflow = "auto";
        $(newEl).style.textAlign = "left";
        
        
        fatherElem = newEl;

        for (a = 0; a < idHTML.length; a++) {
            newEl = document.createElement('input');
            newEl.setAttribute("type", "checkbox");
            newEl.setAttribute("id", _prefix + "id" + a);
            newEl.setAttribute("value", idHTML[a]);
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');

            
            newEl = document.createElement('label');
            fatherElem.appendChild(newEl);
            
            $(newEl).addClassName(_prefix + '_interface');
            $(newEl).innerHTML = idHTML[a];
            /*$(newEl).style.fontVariant = "small-caps";*/
            
            newEl = document.createElement('br');
            fatherElem.appendChild(newEl);

            $(newEl).addClassName(_prefix + '_interface');
            
        }     
        
        fatherElem = fatherElem.parentNode.parentNode;
        
        
        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);
        
        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.textAlign = "left";
        
        
        fatherElem = newEl;

        newEl = document.createElement('input');
        newEl.setAttribute("id", _prefix + "_start");
        newEl.setAttribute("type", "button");
        newEl.setAttribute("name", _prefix + "_start");
        newEl.setAttribute("value", "start");
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).observe('click', mouseClickHandler);

        
        
        newEl = document.createElement('input');
        newEl.setAttribute("id", _prefix + "_clear");
        newEl.setAttribute("type", "button");
        newEl.setAttribute("name", _prefix + "_clear");
        newEl.setAttribute("value", "clear");
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).observe('click', mouseClickHandler);

        
        fatherElem = fatherElem.parentNode.parentNode;

        newEl = document.createElement('div');
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.border = "1px solid #000000";
    
        fatherElem = newEl;
        
        newEl = document.createElement('h4');
        newEl.setAttribute("id", _prefix + "_information");
        fatherElem.appendChild(newEl);

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.cursor = "pointer";
        $(newEl).innerHTML = "Information";
        $(newEl).observe('click', mouseClickHandler);
        
        newEl = document.createElement('div');
        newEl.setAttribute("id", _prefix + "_information_detail");
        fatherElem.appendChild(newEl);    

        $(newEl).addClassName(_prefix + '_interface');
        $(newEl).style.textAlign = "left";
        $(newEl).style.maxWidth = "200px";
        $(newEl).style.overflow = "auto";

         /* da sistemare le variabili
            // inserire dimensioni del documento
            // + image broken
            // + link broken
            //sistemare dimensioni degli elementi sopra all'interno del tooltip
            //sistemare tooltip su ie
            //sistemare cascata degli elementi da ricercare di modo che ricerchi solo gli elementi del nodo
            //inserire gestione errori di pagina
            // + id dublicati
            // + classi vuote
            // + stili in linea nn corretti
            //lista degli stili in linea per elemento dentro al tooltip*/
        var pippo = document.URL;
        pippo = pippo.replace('http://' + ff.domain + ff.site_path + site_gallery_path, '');
        pippo = pippo.replace('http://' + ff.domain + ff.site_path, '');

        if(pippo.indexOf("#") >= 0)
            pippo = pippo.substr(0, pippo.indexOf("#")); 

        if(pippo.indexOf("?") >= 0)
            pippo = pippo.substr(0, pippo.indexOf("?")); 

        pippo = pippo.split("/");

        var pluto = "";
        $(newEl).innerHTML = "main.css <br>";
        for (a = 0; a < pippo.length; a++) {
            if(pippo[a] != "") {
                if(pluto == "")
                    pluto = pippo[a];
                else
                    pluto = pluto + "_" + pippo[a];
                $(newEl).innerHTML = $(newEl).innerHTML + pluto + ".css <br>";
            }
        }
        
        
        

        
    }
    this.initInterface = initInterface;

/*
    var displayInterface = function(target) {
        var elem = document.getElementById(target);
        if(elem.style.display == "none") {
            elem.style.display = "block";
        } else {
            elem.style.display = "none";
        }
    }
    
    var displayInformation = function() {
        var elem = document.getElementById(target);
        if(elem.style.display == "none") {
            elem.style.display = "block";
        } else {
            elem.style.display = "none";
        }
    }
*/
    function init () {
        travelDom(firstElem, "inspectHtml");
        
        this.initInterface();
    }
    this.init = init;
    
    this.init(firstElem);
    
}

document.observe('dom:loaded', function() {

    var pippo = new cssInspector(document.getElementsByTagName("body").item(0));  
  
});

            
