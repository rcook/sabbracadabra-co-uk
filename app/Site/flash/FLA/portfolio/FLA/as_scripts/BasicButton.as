package as_scripts {
	import flash.display.*;
	import flash.events.*;
    import flash.net.*;	
    import flash.utils.Timer;
	import flash.ui.Keyboard;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.text.*;
	
	public class BasicButton extends MovieClip {
		//private var myImage:URLRequest;
		private var myImageLoader:Loader = new Loader();
		public var buttonNumber:Number;
		private var parentObject:MovieClip;
		public var imageURL:String;
		public var longDescription:String;
		public var myLinkHref:String;
		public var myLinkTarget:String;



		
		function BasicButton(mySettings:Object, myStyles:StyleSheet, introPortfolio:MovieClip, buttonNumber:Number) {
			this.buttonNumber = buttonNumber;
			myImageLoader.contentLoaderInfo.addEventListener(Event.COMPLETE, resizeImage);
			
			heading.styleSheet = myStyles;
			heading.htmlText = "<h1>" + mySettings.title + "</h1>";
		    
			description.styleSheet = myStyles;
			description.htmlText = "<p>" + mySettings.shortDescription + "</p>";
			
			//description.buttonMode = true;

            this.imageURL = "../../" + mySettings.image;
			var myImage:URLRequest = new URLRequest(this.imageURL);
			myImageLoader.load(myImage);
			image.addChild(myImageLoader);
			this.parentObject = introPortfolio;

			var myLink:String = this.getLinkHTML(mySettings.link, mySettings.linkLabel, mySettings.target);
			this.myLinkHref = mySettings.link;
			this.myLinkTarget = mySettings.target;

			this.longDescription = "<p class='longDescription'>" + mySettings.description + "</p>" + myLink;
		}
		
		public function reInit(mySettings:Object) {
			heading.htmlText = "<h1>" + mySettings.title + "</h1>";
			description.htmlText = "<p>" + mySettings.shortDescription + "</p>";
			 this.imageURL = "../../" + mySettings.image;
			var myImage:URLRequest = new URLRequest(this.imageURL);
			myImageLoader.load(myImage);

			var myLink:String = this.getLinkHTML(mySettings.link, mySettings.linkLabel, mySettings.target);
			
			// following two lines added to fix portfolio linking not working (June 29/2010)
			this.myLinkHref = mySettings.link;
			this.myLinkTarget = mySettings.target;

			this.longDescription = "<p class='longDescription'>" + mySettings.description + "</p>" + myLink;
		}
		
		private function getLinkHTML(link:String, linkText:String, target:String): String {
			if (target == "") {
			  target = "_top";
			}
			
			//trace(this.parentObject.loaderInfo);
				
			if (this.parentObject.loaderInfo.url.indexOf("file://") == 0) {
				target = "_blank";
			}
            if (link == "" || linkText == "") {
				return "";
			} else {
			  if (link.indexOf("http") == 0) {
				return "<a class='link' href='"+link+"' target='"+target+"'>"+linkText+"</a>";
	
			  } else {
				return "<a class='link' href='../../"+link+"' target='"+target+"'>"+linkText+"</a>";
			  }
			}
		}
		
		public function resizeImage(event:Event):void {
			var movieClip:Object = event.target.content as Object;
			//movieClip.useHandCursor = true
		
			var ratio:Number = movieClip.height / movieClip.width;
			
			if (ratio >= 480/230) {
				movieClip.width = 62;
			    movieClip.height = 62 * ratio;
			} else {
				ratio = movieClip.width / movieClip.height;
				movieClip.width = 29 * ratio;
			    movieClip.height = 29;

			}
			movieClip.width = 73;
			movieClip.height = 30;

		}
		
		public function mousingOver(event:Event):void {
			bg.gotoAndStop(2);
			var clickedButton:Object = this;
			
			// clickedButton.buttonNumber is zero based, so we add 1 to make it 1 based
			var myClickedButton = clickedButton.buttonNumber + 1;
			
			this.parentObject.buttonSelectClick(myClickedButton);
		}
		
		public function mousingOut(event:Event):void {
			if (this.parentObject.currentlySelected != this.buttonNumber + 1) {
			  bg.gotoAndStop(1);
			}
		}
		
		public function mouseClick(event:Event):void {
			
			if (this.myLinkTarget == "") {
			  this.myLinkTarget = "_top";
			}
				
			if (loaderInfo.url.indexOf("file://") == 0) {
				this.myLinkTarget = "_blank";
			}
			
			
			if (this.myLinkHref != "") {
			  var urlRequest:URLRequest;
			  
			  if (this.myLinkHref.indexOf("http") == 0) {
							 urlRequest = new URLRequest(this.myLinkHref);
  
			  } else {
							  urlRequest = new URLRequest("../../" + this.myLinkHref);
  
			  }
			  navigateToURL(urlRequest, this.myLinkTarget);
			}

		}
		


		
	}
}
