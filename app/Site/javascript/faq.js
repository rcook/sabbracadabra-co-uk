var TRange = null;
var dupeRange = null;
var TestRange = null;
var win = null;


var nom = navigator.appName.toLowerCase();
var agt = navigator.userAgent.toLowerCase();
var is_major   = parseInt(navigator.appVersion);
var is_minor   = parseFloat(navigator.appVersion);
var is_ie      = (agt.indexOf("msie") != -1);
if (is_ie && (is_major >=4)) {
  var is_ie4up = true;
} else {
  var is_ie4up = false;
}
var is_ie4up   = (is_ie && (is_major >= 4));
var is_not_moz = (agt.indexOf('netscape')!=-1)
var is_nav     = (nom.indexOf('netscape')!=-1);
var is_nav4    = (is_nav && (is_major == 4));
var is_mac     = (agt.indexOf("mac")!=-1);
var is_gecko   = (agt.indexOf('gecko') != -1);
var is_opera   = (agt.indexOf("opera") != -1);

var is_rev=0
if (is_gecko) {
temp = agt.split("rv:")
is_rev = parseFloat(temp[1])
}

var frametosearch = self;


function search(whichform, whichframe) {


if (is_ie4up & is_mac) return;


if (is_gecko & (is_rev <1)) return;


if (is_opera) return;


if(whichform.findthis.value!=null & whichform.findthis.value!='') {

       str = whichform.findthis.value;
       win = whichframe;
       var frameval=false;
       if(win!=self)
{

       frameval=true;  
       win = parent.frames[whichframe];

}

    
}

else return;  

var strFound;


if(is_nav4 & (is_minor < 5)) {
   
  strFound=win.find(str); 


 
        }


if (is_gecko & (is_rev >= 1)) {
   
    if(frameval!=false) win.focus();
    strFound=win.find(str, false, false, true, false, frameval, false);


    if (is_not_moz)  whichform.findthis.focus();


}

 if (is_ie4up) {
  if (TRange!=null) {
	  
   TestRange=win.document.body.createTextRange();
 
	  

   if (dupeRange.inRange(TestRange)) {

   TRange.collapse(false);
   strFound=TRange.findText(str);
    if (strFound) {
        win.document.body.scrollTop = win.document.body.scrollTop + TRange.offsetTop;
        TRange.select();
        }


   }
   
   else {

     TRange=win.document.body.createTextRange();
     TRange.collapse(false);
     strFound=TRange.findText(str);
     if (strFound) {
        win.document.body.scrollTop = TRange.offsetTop;
        TRange.select();
        }



   }
  }
  
   if (TRange==null || strFound==0) {
   TRange=win.document.body.createTextRange();
   dupeRange = TRange.duplicate();
   strFound=TRange.findText(str);
    if (strFound) {
        win.document.body.scrollTop = TRange.offsetTop;
        TRange.select();
        }

   
   }

 }

  if (!strFound) alert ("String '"+str+"' not found!")

        
}