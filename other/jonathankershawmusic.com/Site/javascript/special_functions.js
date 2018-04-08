  function setDivMinMaxSizes() {
     // setMinMaxSize("outer_wrapper");
  }

  function setMinMaxSize(divName) {
    // you can edit the values of the following three lines
	 // var divName = "outer_wrapper";
     // var maxWidth = 1920;
	 // var minWidth = 990;
	
	/*** DO NOT MODIFY ANYTHING BELOW THIS LINE ***/

	var ns6 = document.getElementById && !document.all		
	var sizediv = ns6 ? document.getElementById(divName) : document.all[divName];

	if (parseInt(navigator.appVersion)>3) {
	 if (navigator.appName=="Netscape") {
	   winW = window.innerWidth - 16 ;
	 
	 } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
       //IE 6+ in 'standards compliant mode'     	   
	   winW = document.documentElement.clientWidth - 20;
     
	 } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
        //IE 4 compatible
        winW = document.body.clientWidth - 20; 
     }
	 
      if (winW > maxWidth) { 
	    sizediv.style.width = maxWidth + "px"; 
	  } else if (winW < minWidth) { 
	    sizediv.style.width = minWidth + "px"; 
	  } else if (sizediv.style.width != "100%") {
	    sizediv.style.width = "100%";
	  }
	}
	/*** DO NOT MODIFY ANYTHING ABOVE THIS LINE ***/
  }
  
  
if (navigator.appName!="Netscape") {
	if (window.addEventListener) {
	  window.addEventListener("resize", setDivMinMaxSizes);
	  window.addEventListener("load", setDivMinMaxSizes);
	} else if (window.attachEvent) {
	  window.attachEvent("onresize", setDivMinMaxSizes);
	  window.attachEvent("onload", setDivMinMaxSizes);	
	}
}






 


 var ns6=document.getElementById&&!document.all

    
 function bookmark(){
  if (document.all) {
    if (window.location.href.indexOf("http")) {
      alert("You cannot bookmark a page on your local computer.");
    } else {
      window.external.AddFavorite(document.location.href,document.title)
    }
  } else {
    alert("Please select CTRL-D to bookmark this page");
  }
}

function makeHomePage() {
	if (document.all){
	  document.body.style.behavior='url(#default#homepage)';
      document.body.setHomePage(document.location);
	}
}



function getCookie(cookieName) {
  //var cookieName = "mp3player";
  //cookieName = cookieName.replace(/index.html/gi, "");
  //cookieName = cookieName.replace(/index.htm/gi, "");
  //var cookieName2 = cookieName + "index.htm";

  var cookieBox = document.cookie.split("; ");
  for (var i=0; i< cookieBox.length; i++) {
      var cookiePacket = cookieBox[i].split("=");
      if (cookieName == cookiePacket[0]) {
        return unescape(cookiePacket[1]);
      }
  }
  return 0;
}



function setCookie(cookieName, myCookieState, isPersistant) {
  if (!myCookieState) {
    myCookieState = 0;
  }
  
  if (!isPersistant) {
    isPersistant = 0;
  }
  
  myCookieInfo = cookieName + "=" + myCookieState+ "; ";

  if (isPersistant == 1) {
    cookieExpiration = new Date();
    cookieExpiration.setFullYear(cookieExpiration.getFullYear() + 1);
    myCookieInfo = myCookieInfo + "expires="+cookieExpiration.toUTCString()+"; ";
  }
  document.cookie = myCookieInfo;
  //alert(myCookieInfo);
}

var playMusic = getCookie("mp3");

function addEvent(obj, type, fn) {
	if (obj.addEventListener) {
		obj.addEventListener( type, fn, false );
	} else if (obj.attachEvent) {
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
		obj.attachEvent( "on"+type, obj[type+fn] );
	}
}


//addEvent(window,'load',setupEditor);
//addEvent(window,'load',goforit);

