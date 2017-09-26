ff.cms.fn.dynatree = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	
	ff.load("jquery.plugins.dynatree", function() {
		jQuery(targetid + ".dynatree ul").parent().addClass("folder");
		jQuery(targetid + ".dynatree a[rel]:not(:empty)").parent().addClass("folder");
		jQuery(targetid + ".dynatree a[rel]:not(:empty)").parent().addClass("lazy");
		jQuery(targetid + ".dynatree a[rel]:not(:empty)").parent().addClass("expanded");
		jQuery(targetid + "link[href*='/jquery.dynatree/src/skin']").attr("href", ff.base_path + "/themes/library/plugins/jquery.dynatree/" + jQuery(targetid + ".dynatree").parent().attr("class").replace("menu ", "") + "/ui.dynatree.css");

		jQuery(targetid + ".dynatree").parent().dynatree({
		    title: "Home", /* Name of the root node. */
		    rootVisible: false, /* Set to true, to make the root node visible. */
		    minExpandLevel: 2, /* 1: root node is not collapsible */
			imagePath: ff.base_path + "/themes/library/plugins/jquery.dynatree/src/" + jQuery(targetid + ".dynatree").parent().attr("class").replace("menu ", "") + "/", /* Path to a folder containing icons. Defaults to 'skin/' subdirectory. */
		    children: null, /* Init tree structure from this object array. */
		    initId: jQuery(this).id, /* Init tree structure from a <ul> element with this ID. */
			initAjax: {
				url: ""
				, data: {key: "",
                	sleep: 1,
		            depth: 3,
		            mode: "baseFolders"
		        }
            	, addExpandedKeyList: true /* add &expandedKeyList= parameter to URL */
		    },
			 onPostInit: function(isReloading, isError) {
					/* In lazy mode, this will be called *after* the initAjax request returned.
                                            'this' is the current tree
                                            isReloading is set, if status wss read from existing cookies
                                            isError is set, if Ajax failed
                                            Fire an onActivate() event for the currently active node */
					this.reactivate();
			 },
		    autoFocus: true, /* Set focus to first child, when expanding or lazy-loading. */
		    keyboard: true, /* Support keyboard navigation. */
		    persist: true, /* Persist expand-status to a cookie */
		    autoCollapse: true, /* Automatically collapse all siblings, when a node is expanded. */
		    clickFolderMode: 3, /* 1:activate, 2:expand, 3:activate and expand */
		    activeVisible: true, /* Make sure, active nodes are visible (expanded). */
		    checkbox: false, /* Show checkboxes. */
		    selectMode: 2, /* 1:single, 2:multi, 3:multi-hier */
		    fx: { height: "toggle", duration: 200 }, /* Animations, e.g. null or { height: "toggle", duration: 200 } */

		    /* Low level event handlers: onEvent(dtnode, event): return false, to stop default processing */
		    onClick: null, /* null: generate focus, expand, activate, select events. */
		    onDblClick: null, /* (No default actions.) */
		    onKeydown: null, /* null: generate keyboard navigation (focus, expand, activate). */
		    onKeypress: null, /* (No default actions.) */
		    onFocus: null, /* null: handle focus. */
		    onBlur: null, /* null: handle unfocus. */

		    /* Pre-event handlers onQueryEvent(flag, dtnode): return false, to stop processing */
		    onQueryActivate: null, /* Callback(flag, dtnode) before a node is (de)activated. */
		    onQuerySelect: null, /* Callback(flag, dtnode) before a node is (de)selected. */
		    onQueryExpand: null, /* Callback(flag, dtnode) before a node is expanded/collpsed. */
		    
		    /* High level event handlers */
		    onActivate: function(dtnode) {
        	/* In real life we would call something like this: */
			}, /* Callback(dtnode) when a node is activated. */
		    onDeactivate: null, /* Callback(dtnode) when a node is deactivated. */
		    onSelect: null, /* Callback(flag, dtnode) when a node is (de)selected. */
		    onExpand: null, /* Callback(flag, dtnode) when a node is expanded. */
			onLazyRead: function(dtnode) {
			
        	/* In real life we would call something like this: */

		      
		      dtnode.appendAjax({
              	url: jQuery("#" + jQuery(this).attr("id") + " a").next().attr("rel"),
		        data: {key: dtnode.data.key,
                		mode: "all"
					}
		      });
			},            
		    ajaxDefaults: { /* Used by initAjax option */
		        cache: false, /* false: Append random '_' argument to the request url to prevent caching. */
		        dataType: "html" /* Expect json format and pass json object to callbacks. */
		    },
		    strings: {
		        loading: "Loading�",
		        loadError: "Load error!"
		    },
		    idPrefix: "ui-dynatree-id-", /* Used to generate node id's like <span id="ui-dynatree-id-<key>">. */
		    cookieId: "ui-dynatree-cookie", /* Choose a more unique name, to allow multiple trees. */
		    cookie: { /* Options passed to jQuery.cookie (see jquery.cookie.js) */
		        expires: null, /* Days or Date; null: session cookie */
		        path: '/', /* String; Defaults to current page */
		        domain: undefined, /* String; Defaults to creator's domain */
		        secure: undefined /* Boolean; Defaults to false */
		    },
		    debugLevel: 1 /* 0:quiet, 1:normal, 2:debug */
		});
		/*jQuery(this).css("display", "none"); */
	});

};