//----------------------------------------------------------------------------------------------------
//	cXMLApp.as
//----------------------------------------------------------------------------------------------------
package as_scripts
{
import flash.display.Sprite;
import flash.net.URLLoader;
import flash.net.URLRequest;
import flash.events.Event;
import flash.xml.XMLDocument;
import flash.text.StyleSheet;


//----------------------------------------------------------------------------------------------------
//	class definition
//----------------------------------------------------------------------------------------------------
public class gXMLApp extends Sprite
{
	
//----------------------------------------------------------------------------------------------------
//	member data
//----------------------------------------------------------------------------------------------------
private var mURLLoader : URLLoader;
protected var cssLoader : URLLoader;
protected var mData : XML;
protected var stylesheet : StyleSheet;
protected var mItems : uint;
protected var mState : String;
protected var cssFileURL : String;
protected var delayTime : uint = 500;

//----------------------------------------------------------------------------------------------------
//	constructor
//----------------------------------------------------------------------------------------------------
public function 
gXMLApp(
	vFile : String,
	cssFile : String
) : void
{
		//cssFile="config/gallery.css";

	cssFileURL = cssFile;
	trace(this + " " + "gXMLApp.constructor");
	//vFile="config/gallery_primary.xml";
	//cssFileURL="config/gallery.css";
	var urlRequest : URLRequest = new URLRequest(vFile);
	mURLLoader = new URLLoader();
	mURLLoader.load(urlRequest);
	mURLLoader.addEventListener(Event.COMPLETE, fLoaded);

	
	



}

//----------------------------------------------------------------------------------------------------
//	fLoaded
//----------------------------------------------------------------------------------------------------
private function 
fLoaded(
	e :Event
):void 
{
	trace(this + " " + "gXMLApp.fLoaded()");
		
	var vDoc : XMLDocument = new XMLDocument();	
	vDoc.ignoreWhite = true;
	mData = XML(mURLLoader.data);
	vDoc.parseXML(mData.toXMLString());	
	delayTime = vDoc.firstChild.firstChild.firstChild.nodeValue;	
	mItems     = vDoc.firstChild.lastChild.childNodes.length;
	//trace(mData.toXMLString());
	//trace(mItems);
	//trace(delayTime);
	
	var urlRequest : URLRequest = new URLRequest(cssFileURL);
	cssLoader = new URLLoader();
	cssLoader.addEventListener(Event.COMPLETE, onCSSFileLoaded);

	cssLoader.load(urlRequest);

	//dispatchEvent(new Event("XMLLoaded"));

}

private function onCSSFileLoaded(e:Event): void {
	stylesheet = new StyleSheet();
    stylesheet.parseCSS(cssLoader.data);
	dispatchEvent(new Event("XMLLoaded"));
}

//----------------------------------------------------------------------------------------------------
}	// class XMLHelper
//----------------------------------------------------------------------------------------------------
}	// package