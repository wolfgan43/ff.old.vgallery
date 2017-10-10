document.observe('dom:loaded', function() {
    var pippo = new wordcode(document.getElementsByTagName("body").item(0));  
    //treeTraverseIterative(document.getElementsByTagName("body").item(0));
});

function wordcode(firstElem) {

    var treeTraverseIterative = function(elem) {
        var queue = new Array();
        var i, currentElem, childs;
        var count = 0;

        queue.push(elem);
        while (queue.length > 0) {
            count++;
            currentElem = queue.pop();
            visit(currentElem, count);
            childs = currentElem.childNodes.length;
            for (i = 0; i < childs; i++) {
                queue.push(currentElem.childNodes[i]);
            }
        }
    }

    var visit = function(elem, count) {
        if(((elem.nodeName == "#text" && elem.length > 0) || elem.nodeName == "INPUT") && elem.parentNode.nodeName != "SCRIPT") {
            var fatherElem = "";
            var elemData = "";
            var elemDataPos = -1;
            var icoType = "";
            var newEl = "";
            var newData = "";
            var code = "";
            
            if(elem.nodeName == "#text") {
                elemData = elem.data;
            } else {
                elemData = elem.value;
            }

            if(elemData.search('#wc#') >= 0) {
                code = elemData.substr(elemData.search('#wc#') + 4, elemData.indexOf('#wc#', elemData.search('#wc#') + 4) - elemData.search('#wc#') - 4);
                elemDataPos = elemData.search('#wc#');
                if(code == "") {
                    icoType = 'wci';
                    newData = elemData.replace('#wc##wc#', '');
                    code = newData;
                } else {
                    icoType = 'wcu';
                    newData = elemData.replace('#wc#'+code+'#wc#', '');
                }
            }

            if(elemDataPos >= 0)  { 
                switch(elem.parentNode.nodeName) {    
                    case "OPTION":
                        fatherElem = elem.parentNode.parentNode.parentNode;
                        break;
                    case "A":
                        fatherElem = elem.parentNode.parentNode;
                        break;
                    case "TEXTAREA":
                        fatherElem = elem.parentNode.parentNode;
                        elem.parentNode.value = newData;
                        break;
                    default:
                        fatherElem = elem.parentNode;
                }
                newEl = document.createElement('img')
                newEl.setAttribute('src', ff.site_path+'/themes/gallery/images/'+icoType+'.png');
                newEl.setAttribute('alt','Edit WordCode');
                fatherElem.appendChild(newEl);
               
                newEl.style.cursor = 'pointer';

                if(elem.parentNode.nodeName == "TEXTAREA") {
                    elem.parentNode.value = newData;
                } else if(elem.nodeName == "#text") {
                    elem.data = newData;
                } else {
                    elem.value = newData;
                }

                new Tip(newEl, {
                    title : 'WordCode Editor'
                    , ajax: {
                        url: ff.site_path+'/admin/international/modify.php?ret_url='+location.href+'&wc='+code+'&unic='+count
                        , options: {
                            method: 'get' 
                            , onComplete: function(transport) {
                                $$('form').each(function(item) {
                                    if(item.name == "frmInternational"+count) {
                                        item.action = ff.site_path+'/admin/international/modify.php?ret_url='+location.href+'&wc='+code+'&unic='+count;
                                    }
                                });
                                // you could do something here after the ajax call is finished
                            }
                        }
                    }
                    , hideOn: { element: 'closeButton', event: 'click' }
                    , showOn: 'click'
                    , width: 'auto' // We don't want the default 250px.
                                   // Images inside the tooltip will need to have dimensions set since Prototip needs to fixate width for proper rendering.
                    , hook: { target: 'bottomMiddle', tip: 'topMiddle' }
                    , stem: 'topMiddle'
                    , offset: { x: 7, y: 18 }
                });
                
            }
        }    
        // visits the element
    }
    function init (firstElem) {
        treeTraverseIterative(firstElem);
    }
    this.init = init;
        
    this.init(firstElem);
}
