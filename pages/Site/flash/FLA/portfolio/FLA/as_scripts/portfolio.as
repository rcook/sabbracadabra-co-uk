package as_scripts {
	import flash.display.*;
	import flash.events.*;
    import flash.net.*;	
    import flash.utils.Timer;
	import flash.ui.Keyboard;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.text.*;
	
	public class portfolio extends MovieClip {
		private var externalXML:XML;
		private var xmlLoader:URLLoader = new URLLoader();
		private var cssLoader:URLLoader = new URLLoader();
		private var imgloader:Loader = new Loader();
		private var xmlFileName:String;
		private var xmlRequest:URLRequest;
		private var cssRequest:URLRequest;
		private var myLoader1:Loader = new Loader();
		private var myLoader2:Loader = new Loader();
		private var myLoader3:Loader = new Loader();
		private var myLoader4:Loader = new Loader();
		private var myFirst:Number = 0;
		private var mySecond:Number = 1;
		private var myThird:Number = 2;
		private var myFourth:Number = 3;
		private var myURL1:URLRequest;
		private var myURL2:URLRequest;
		private var myURL3:URLRequest;
		private var myURL4:URLRequest;
		private var max:Number;
		private var select:Number=0;
	    public var currentlySelected:Number=0;
		private var count:Number=0;
		private var long1:String;
		private var long2:String;
		private var long3:String;
		private var long4:String;
		private var sheet:StyleSheet = new StyleSheet();
		public var timer:Timer;
		private var myButtons:Array = new Array();
		public var testVariable:String = new String("test");
		private var numberOfButtonsToShift:Number;
		private var totalNumberOfButtons:Number;
	    private var totalNumberOfButtonsToDisplayAtOneTime:Number;
		private var buttonPositionOffset:Number = 0;
		
		public function portfolio() {
	 		if (!root.loaderInfo.parameters.configLocation) {
				this.xmlRequest = new URLRequest("config/portfolio.xml");
				this.cssRequest = new URLRequest("config/portfolio.css");
			} else if (!root.loaderInfo.parameters.configType) {
				this.xmlRequest = new URLRequest(root.loaderInfo.parameters.configLocation + "/portfolio.xml");
				this.cssRequest = new URLRequest(root.loaderInfo.parameters.configLocation + "/portfolio.css");
			} else {
				this.xmlRequest = new URLRequest(root.loaderInfo.parameters.configLocation + "/portfolio." + root.loaderInfo.parameters.configType);
				this.cssRequest = new URLRequest(root.loaderInfo.parameters.configLocation + "/portfolio.css");
			}
			
			
			if (!root.loaderInfo.parameters.configLocation) {
      			init();
   			} else {
      			addEventListener(Event.ADDED_TO_STAGE, init);
   			}
		}
		private function init(evt:Event=null):void {
			xmlLoader.addEventListener(Event.COMPLETE, loadStyles);
			xmlLoader.load(xmlRequest);
			mainImageLoader.mainImage.addChild(imgloader);
			mainImageLoader.buttonMode = true;
			mainImageLoader.useHandCursor = true;
			mainImageLoader.addEventListener(MouseEvent.CLICK, clickMain);
			testVariable = "good times";
			
			forward.addEventListener(MouseEvent.CLICK, moveOn);
			back.addEventListener(MouseEvent.CLICK, moveBack);
			stage.addEventListener(Event.ENTER_FRAME, countUp);

		}
		
		private function addButtonsToStage(event:Event = null):void {
			
		  sheet.parseCSS(cssLoader.data);
		  
		  
		  if (xmlLoader != null) {
	        externalXML = new XML(xmlLoader.data);
	        var stageWidth:Number    = externalXML.stageWidth;
		    var stageCurrentX:Number = externalXML.buttonBarStartX;
		    var stageCurrentY:Number = externalXML.buttonBarStartY;
		    var movieClipWidth:Number = externalXML.buttonWidth;
		    var movieClipHeight:Number = externalXML.buttonHeight;
			
		    totalNumberOfButtons = externalXML.images.pic.length();
			totalNumberOfButtonsToDisplayAtOneTime = externalXML.numberOfButtonsOnStage;
			numberOfButtonsToShift = externalXML.numberOfButtonsToShift;
            var buttonDisplayDirection:String = externalXML.buttonDisplayDirection;
			var buttonAlign:String = externalXML.buttonAlign;

			if (buttonAlign == "left") {
				
			} else if (buttonAlign == "right") {
			 if (buttonDisplayDirection == "horizontal") {
			   stageCurrentX = stageWidth - (movieClipWidth * totalNumberOfButtonsToDisplayAtOneTime);
			 } else {
			   stageCurrentX = stageWidth - movieClipWidth;
			 }
			} else {
			  if (buttonDisplayDirection == "horizontal") {
			    stageCurrentX = (stageWidth - (totalNumberOfButtonsToDisplayAtOneTime * movieClipWidth)) / 2;	
			  } else {
				stageCurrentX = (stageWidth - movieClipWidth) / 2;  
			  }
			}
			var myButtonIndex:Number;
			for (var index:Number = 0; index < totalNumberOfButtonsToDisplayAtOneTime; index++) {
				var tempMovieClip = new BasicButton(externalXML.images.pic[index], sheet, this, index);
				tempMovieClip.x = stageCurrentX;
				tempMovieClip.y = stageCurrentY;

				tempMovieClip.addEventListener(MouseEvent.MOUSE_OVER, tempMovieClip.mousingOver);
				tempMovieClip.addEventListener(MouseEvent.MOUSE_OUT, tempMovieClip.mousingOut);
				tempMovieClip.addEventListener(MouseEvent.CLICK, tempMovieClip.mouseClick)
				tempMovieClip.buttonMode = true;
				tempMovieClip.useHandCursor = true;
				tempMovieClip.mouseChildren = false;
				if (buttonDisplayDirection == "horizontal") {
				  if (buttonAlign == "right") {
					stageCurrentX = stageCurrentX + movieClipWidth;
				  } else {
					stageCurrentX = stageCurrentX + movieClipWidth;
				  }
				} else {
				  stageCurrentY = stageCurrentY + movieClipHeight;
				}
				
				addChild(tempMovieClip);
				myButtons.push(tempMovieClip);
			}

		  }
		  
		  firstComplete(event);
		}
		
		
		public function loadStyles(event:Event): void {
			cssLoader.addEventListener(Event.COMPLETE, addButtonsToStage);
			cssLoader.load(cssRequest);

		}
		
		public function getLinkHTML(link:String, linkText:String, target:String): String {
			if (target == "") {
			  target = "_top";
			}
				
			if (loaderInfo.url.indexOf("file://") == 0) {
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
		
		
		public function clickMain(event:Event): void {
			var movieClip:Object = event.target.content as Object;

			var currentlyClicked:Number = myFirst + currentlySelected - 1;
			//trace("clickMain currentlyClicked A: " + currentlyClicked);
			//trace("clickMain currentlySelected A: " + currentlySelected);
			if (currentlyClicked >= externalXML.images.pic.length()) {
				currentlyClicked = currentlyClicked - externalXML.images.pic.length();
			}
			//trace("clickMain totalImages: " + externalXML.images.pic.length);
			//trace("clickMain currentlyClicked B: " + currentlyClicked);
			//trace("clickMain myFirst: " + myFirst);
			
			externalXML = new XML(xmlLoader.data);
			
			var myLink:String = externalXML.images.pic[currentlyClicked].link;
			var myTarget:String = externalXML.images.pic[currentlyClicked].target;
			
			if (myTarget == "") {
			  myTarget = "_top";
			}
				
			if (loaderInfo.url.indexOf("file://") == 0) {
				myTarget = "_blank";
			}
			
			if (myLink != "") {
			  var urlRequest:URLRequest;
			  
			  if (myLink.indexOf("http") == 0) {
				urlRequest = new URLRequest(myLink);
			  } else {
				urlRequest = new URLRequest("../../" + myLink);
			  }
			  navigateToURL(urlRequest, myTarget);
			}

		}
		
		public function resizeImage(event:Event):void {
			var movieClip:Object = event.target.content as Object;
			
			var ratio:Number = movieClip.height / movieClip.width;
			
			if (ratio > 3/7) {
				movieClip.width = 84;
			movieClip.height = 37 * ratio;
			} else {
				ratio = movieClip.width / movieClip.height;
				movieClip.width = 84 * ratio;
			    movieClip.height = 37;

			}
			movieClip.width = 98;
			movieClip.height = 38;
		}
		
		public function firstComplete(event:Event): void {
			
			if (xmlLoader != null) {
			  externalXML = new XML(xmlLoader.data);
			  timer = new Timer(externalXML.transitionDelay);
			} else {
				timer = new Timer(10000);
			}
			timer.addEventListener("timer", moveNext);
			//trace("total buttons" + externalXML.images.pic.length() + " <= " + totalNumberOfButtonsToDisplayAtOneTime);
			if (externalXML.images.pic.length() <= totalNumberOfButtonsToDisplayAtOneTime) {
				back.visible = false;
				forward.visible = false;
			}

			buttonSelectClick(1);

			timer.start();
		}
		
		public function loadDataIntoButtons():void {
			//trace ("my button position offset = " + buttonPositionOffset);
			if (buttonPositionOffset >= totalNumberOfButtons) {
				buttonPositionOffset = buttonPositionOffset - totalNumberOfButtons;
			} else if (buttonPositionOffset < 0) {
				buttonPositionOffset += totalNumberOfButtons;
			}
			for (var index:Number = 0; index < totalNumberOfButtonsToDisplayAtOneTime; index++) {
                var myButtonIndex = buttonPositionOffset + index;
				if (myButtonIndex >= totalNumberOfButtons) {
				  myButtonIndex = myButtonIndex - totalNumberOfButtons;					
				}
				myButtons[index].reInit(externalXML.images.pic[myButtonIndex]);
			}
		}
		
		
		function unselectButton(currentlySelected:Number): void {
			if (currentlySelected > 0 && currentlySelected <= myButtons.length) {
			  myButtons[currentlySelected - 1].bg.gotoAndStop(1);
			}
		}
		
		function buttonClick(event:MouseEvent):void {
			var clickedButton:Object = event.currentTarget as Object;
			
			// clickedButton.buttonNumber is zero based, so we add 1 to make it 1 based
			var myClickedButton = clickedButton.buttonNumber + 1;
			
			buttonSelectClick(myClickedButton);
		}
		
		function buttonSelectClick(myClickedButton:Number):void {
			if (currentlySelected != myClickedButton) {
			  unselectButton(currentlySelected);
			}
			transition.play();
			select            = myClickedButton;
			currentlySelected = myClickedButton;
			spin.stop();
			timer.reset();
			timer.start();
			//trace("button select clicked, currently selected: " + currentlySelected);
            myButtons[currentlySelected - 1].bg.gotoAndStop(2);

		}
		
		
		function clickOne(Event:MouseEvent):void {
			if (currentlySelected != 1) {
			  unselectButton(currentlySelected);
			}
			transition.play();
			select            = 1;
			currentlySelected = 1;
			spin.stop();
			timer.reset();
			timer.start();
		}
		
		function clickTwo(Event:MouseEvent):void {
			if (currentlySelected != 2) {
			  unselectButton(currentlySelected);
			}
			transition.play();
			select=2;
			currentlySelected = 2;
			spin.stop();
			timer.reset();
			timer.start();
		}
		
		function clickThree(Event:MouseEvent):void {
			if (currentlySelected != 3) {
			  unselectButton(currentlySelected);
			}
			transition.play();
			select=3;
			currentlySelected = 3;
			spin.stop();
			timer.reset();
			timer.start();
		}

		function clickFour(Event:MouseEvent):void {
			if (currentlySelected != 4) {
			  unselectButton(currentlySelected);
			}
			transition.play();
			select=4;
			currentlySelected = 4;
			spin.stop();
			timer.reset();
			timer.start();
		}		
		
		function moveNext(event:TimerEvent): void {
			//trace("in move next " + currentlySelected);
			if (currentlySelected >= totalNumberOfButtonsToDisplayAtOneTime) {
				moveOn(null);
				buttonSelectClick(1);
			} else {
				buttonSelectClick(currentlySelected + 1);
			}

		}
		
		
		function moveOn(Event:MouseEvent):void {
			buttonPositionOffset += numberOfButtonsToShift;
			//trace("numberOfButtonsToShift: " + numberOfButtonsToShift);
			//trace("buttonPositionOffset: " + buttonPositionOffset);

			if (buttonPositionOffset + totalNumberOfButtonsToDisplayAtOneTime <= totalNumberOfButtons || true) {
				loadDataIntoButtons();
				transition.play();
				transition2.play();
				myFirst = buttonPositionOffset;
			} else {
				buttonPositionOffset = 0;
				myFirst = 0;
				loadDataIntoButtons();
				transition.play();

			}
			
			buttonSelectClick(1);
			
		}
		function moveBack(Event:MouseEvent):void {
			//trace("move back, current buttonPosition = " + buttonPositionOffset);
			myFirst = myFirst - numberOfButtonsToShift;
			if (myFirst < 0) {
				myFirst = totalNumberOfButtons + myFirst;
			}
			if (buttonPositionOffset >= 0) {
				buttonPositionOffset -= numberOfButtonsToShift;
				loadDataIntoButtons();
				transition.play();
				
			} else {
				buttonPositionOffset = totalNumberOfButtons - 1;
				loadDataIntoButtons();
				transition.play();
			}
			buttonSelectClick(1);


		}
		private function countUp(event:Event):void {
			if (select!=0) {
				count++;
			}
			if (count>=20) {
				imgloader.load(new URLRequest(myButtons[select - 1].imageURL));
				longDesc.styleSheet = sheet;
				longDesc.htmlText = myButtons[select - 1].longDescription;

				select=0;
				count=0;
			}
		}
	}
}