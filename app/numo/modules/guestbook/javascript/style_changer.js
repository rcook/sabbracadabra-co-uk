var styleId = "";

function recolor(item) {
  styleId = item.id;
  if(styleId != null || styleId != "") {
  	updateH(rgbConvert(item.style.backgroundColor));
  }
}

function rgbConvert(str) {
   str = str.replace(/rgb\(|\)/g, "").split(",");
   str[0] = parseInt(str[0], 10).toString(16).toLowerCase();
   str[1] = parseInt(str[1], 10).toString(16).toLowerCase();
   str[2] = parseInt(str[2], 10).toString(16).toLowerCase();
   str[0] = (str[0].length == 1) ? '0' + str[0] : str[0];
   str[1] = (str[1].length == 1) ? '0' + str[1] : str[1];
   str[2] = (str[2].length == 1) ? '0' + str[2] : str[2];
   return (str.join(""));
}

function mkColor(v){
	$S(styleId).background='#'+v;
	document.getElementById("input_"+styleId).value = '#'+v;
	
	//alert("input_"+styleId);
	//$name."_".$value['style']
}

function addEvent(type, fn) {
	obj = getFrameDocument("layout");
	
	if(obj.addEventListener) {
		obj.addEventListener(type, fn, false);
	} else if(obj.attachEvent) {
		obj.attachEvent("on"+type, fn);
	}
}

function handleLayoutClick(e) {
	var obj = null;

	if(e.srcElement) {
		obj = e.srcElement;
	} else if(e.target) {
		obj = e.target;
	}
	
	closeStyles();
	openStyles(obj);

}

//get the parent editable DIV cell
function openStyles(obj) {	
	//activate style properties display
	//alert(obj.tagName);
	//alert(obj.className);
	
	if(document.getElementById("style__stylerclass_"+obj.className.toLowerCase())) {
		document.getElementById("style__stylerclass_"+obj.className.toLowerCase()).style.display = "block";
	}
	
	if(document.getElementById("style__"+obj.tagName.toLowerCase())) {
		document.getElementById("style__"+obj.tagName.toLowerCase()).style.display = "block";
	}
	
	//alert(obj.tagName);
	if(obj.tagName == "BODY" || obj.tagName == "HTML") {		
		//alert(obj.tagName);
		return;
	}
	
	return openStyles(obj.parentNode);
}

function closeStyles() {
	//close all style properties display
	var ulTags = document.getElementsByTagName("ul");

	for(var x = 0; x < ulTags.length; x++) {
		if(ulTags[x].className == "styleProperties") {
			ulTags[x].style.display = "none";
		}
	}
}

function getFrameDocument(elementId) {
  var frameObj = document.getElementById(elementId);
  
  if(frameObj.contentDocument) {
    frameObj = frameObj.contentDocument;
  } else {
    frameObj = frameObj.contentWindow.document;
  }
  
  return frameObj;
}

function setupStyler() {
	disableLinks();
  addEvent('mousedown', handleLayoutClick);
}

function disableLinks() {
	var doc = getFrameDocument("layout");
	
	links=doc.getElementsByTagName('A');
	
	for(var i=0; i<links.length; i++) {
		links[i].href="javascript:return false;";
	}
}

function updateStylePreview(theClass, element, value) {
	if(element == "background") {
		element = "background-color";
	}

	var doc = getFrameDocument("layout");
	var cssRules;

	if (doc.all) {
		cssRules = 'rules';
	} else if (doc.getElementById) {
		cssRules = 'cssRules';
	}
	
	var added = false;
	
	for (var S = 0; S < doc.styleSheets.length; S++) {
		for (var R = 0; R < doc.styleSheets[S][cssRules].length; R++) {
			if (doc.styleSheets[S][cssRules][R].selectorText == theClass) {
				if(doc.styleSheets[S][cssRules][R].style[element]){
					doc.styleSheets[S][cssRules][R].style[element] = value;
					added=true;
					break;
				}
			}
		}

		if(!added){
			if(doc.styleSheets[S].insertRule){
				doc.styleSheets[S].insertRule(theClass+' { '+element+': '+value+'; }',doc.styleSheets[S][cssRules].length);
			} else if (doc.styleSheets[S].addRule) {
				doc.styleSheets[S].addRule(theClass,element+': '+value+';');
			}
		}
	}
}


window.onload=setupStyler;
//var layoutDocument = null;