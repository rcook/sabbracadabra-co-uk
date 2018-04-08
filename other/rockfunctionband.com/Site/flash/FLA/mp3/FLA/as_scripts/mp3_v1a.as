package as_scripts 
{
import flash.display.*;
import flash.text.*;
import flash.events.*;
import flash.media.*;
import flash.net.*;
import flash.geom.*;
import flash.filters.GlowFilter;
import flash.external.ExternalInterface;

public class mp3_v1a extends xmlloader
{
private var mChannel : SoundChannel;
private var mSound : Sound;
private var mPosition : int;
private var mCurrentPosition : int;
private var mVolume : int;
private var mTrack : int = 0;
private var mLoading : Boolean;
private var mVisual : extras;
private var mBoundt : Rectangle; 	
private var mBoundv : Rectangle; 	
private var mBoundp : Rectangle; 	
private var glowOn : GlowFilter = new GlowFilter(0xFFFFFF,1,5,5,2,2,false,false);
private var glowOff : GlowFilter = new GlowFilter(0xFFFFFF,0,5,5,2,2,false,false);
private var mScrubber : String;
private var mTotalSec : String;

public function mp3_v1a() : void
{
	var xmlLocation:String;
	
	 if (!root.loaderInfo.parameters.configLocation) {
		xmlLocation = "config/mp3.xml";
	} else {
		xmlLocation = root.loaderInfo.parameters.configLocation+ "/mp3." + root.loaderInfo.parameters.configType;
	}
	
	super(xmlLocation);	

	mChannel = new SoundChannel();
	
	super.addEventListener("XMLLoaded", fStart);						
}

private function fStart(e : Event) : void
{
	trace(this + " " + "mp3_v1a.fStart()");
	
	var i : int;	
	
	stage.addEventListener(Event.ENTER_FRAME, fRefresh, false, 0, true);
	stage.addEventListener(MouseEvent.MOUSE_DOWN, fOnMouseDown, false, 0, true);
	stage.addEventListener(MouseEvent.MOUSE_UP, fOnMouseUp, false, 0, true);
	stage.addEventListener(MouseEvent.CLICK, fOnClick, false, 0, true);

	mBoundt = new Rectangle(0,tBar.tBarKnob.y,tBar.tBarBg.width,0);		
	mBoundv = new Rectangle(0,vBar.vBarKnob.y,vBar.vBarBg.width,0);	
	mBoundp = new Rectangle(0,pBar.pBarKnob.y,pBar.pBarBg.width,0);	
	vBar.vBarKnob.x = vBar.vBarBg.width / 2;
	pBar.pBarKnob.x = pBar.pBarBg.width / 2;
	
	mLoading = true;												
	if (stage) {
		mSound = new Sound(new URLRequest(mData.mp3s.mp3[0].url.replace("/", "\\")));	

	} else {
		mSound = new Sound(new URLRequest(mData.mp3s.mp3[0].url));	
	}
	
	if (root.loaderInfo.parameters.playMusic == 1) {
	  mChannel = mSound.play();
	  mState = "PLAY";
	} else {
	 mState = "STOP";
	 pbStop.addEventListener("dontPlay", fOnClick);
 	 pbStop.dispatchEvent(new MouseEvent("dontPlay"));
	}
	

	
}

private function fOnComplete(e : Event) : void
{
	if (txtRepeat.text == "PLAY ALL")
		mChannel.stop();
	else
	{
		mTrack++;
		mChannel.stop();
		if (mTrack > mItems - 1) {
			mTrack = 0;
		} 
	}
	fLoad(mTrack,0);
}

protected function 
fOnMouseDown(e : MouseEvent) : void
{
	if (e.target.name.substring(1, 4) == "Bar" && e.target.name.substring(1, 5) == "Knob")
	{
		this[e.target.parent.name][e.target.name].filters = [glowOn];
		this[e.target.parent.name][e.target.name].startDrag(true, this["mBound" + e.target.name.substring(0, 1)]);
		mScrubber = e.target.parent.name;
	}
	else if (e.target.name.substring(1, 4) == "Bar" && e.target.name.substring(1, 5) != "Knob")
	{
		this[e.target.parent.name][e.target.parent.name.substring(0, 1) + "BarKnob"].filters = [glowOn];
		this[e.target.parent.name][e.target.parent.name.substring(0, 1) + "BarKnob"].startDrag(true, this["mBound" + e.target.name.substring(0, 1)]);
		mScrubber = e.target.parent.name;
	}
}

private function fOnMouseUp(e : MouseEvent) : void
{
	if (mScrubber != null)
	{		
		this[mScrubber][mScrubber + "Knob"].stopDrag();
		this[mScrubber][mScrubber + "Knob"].filters = [glowOff];
		
		switch (mScrubber)
		{
		case "tBar":
			switch (mState)
			{
			case "PLAY":
				mChannel.stop();
				mChannel = mSound.play(mSound.length / 100 * Math.floor(tBar.tBarKnob.x/(tBar.tBarBg.width)*100));
				break;
				
			case "STOP":
				mCurrentPosition = mSound.length / 100 * Math.floor(tBar.tBarKnob.x/(tBar.tBarBg.width)*100);
				break;
			}
			break;
		}				
		mScrubber = null;
	}
}

private function fOnClick(e : MouseEvent) : void
{
	switch (e.target.name)
	{
		case "pbForward":
			mTrack++;
			if (mTrack > mItems - 1) {
				mTrack = 0;			
			} 
			break;
			
		case "pbBack":			
			mTrack--;
			if (mTrack < 0) 
				mTrack = mItems - 1;
			break;
			
		case "pbPlayPause":
			if (mState == "PLAY") 
			{
				mCurrentPosition = mPosition;
				mChannel.stop();
				mState = "STOP";
				ExternalInterface.call("setCookie", "mp3", 0);
				// set cookie to 0
			}
			else
			{
				mChannel = mSound.play(mCurrentPosition);
				mState = "PLAY";
				// set cookie to 1
							ExternalInterface.call("setCookie", "mp3", 1);
}
			return;
		
		case "pbStop":
			mState = "STOP";
			mChannel.stop();				
			tBar.tBarKnob.x = 0;
			mCurrentPosition = 0;
							ExternalInterface.call("setCookie", "mp3", 0);

			return;
			
		case "hsConfig_Repeat":
			txtRepeat.text == "PLAY ALL" ? txtRepeat.text = "LOOP SONG" : txtRepeat.text = "PLAY ALL";
			return;
		
		default:
			return;
			
	}
	if (mState == "STOP")
		mState = "PLAY";
	mChannel.stop();
	fLoad(mTrack, 0);	
}

private function fLoad(vTrack : int, vPos : int) : void
{
	if (mLoading == true)
	{
		mSound.close();
		mLoading = false;
	}
	mChannel.stop();
	mSound = new Sound(new URLRequest(mData.mp3s.mp3[vTrack].url));
	mChannel = mSound.play(vPos);
}

private function fRefresh(e : Event) : void
{
	mChannel.soundTransform = new SoundTransform(vBar.vBarKnob.x / vBar.vBarBg.width, - 1 + 2 * (pBar.pBarKnob.x / pBar.pBarBg.width));
	mChannel.addEventListener(Event.SOUND_COMPLETE, fOnComplete, false, 0, true);

	if (mSound.bytesTotal > 0)										
	{

		if (mSound.bytesLoaded < mSound.bytesTotal) 				
		{

			txtLoaded.text = Math.floor(mSound.bytesLoaded / mSound.bytesTotal * 100).toString() + "%";
			mLoading = true;
		}
		else
		{

			txtLoaded.text = "";
			mLoading = false;
		}

		tBar.tScrubBarBg.width = 150 * mSound.bytesLoaded / mSound.bytesTotal;

		tBar.tBarBg.width = 150 * mSound.bytesLoaded / mSound.bytesTotal;

		mPosition = mChannel.position;								

		txtDisplay.text = String(mData.mp3s.mp3[mTrack].artist) + " - " + String(mData.mp3s.mp3[mTrack].title);

		if (mScrubber != null)
		{

			mPosition = mSound.length * tBar.tBarKnob.x / tBar.tBarBg.width;

			mCurrentPosition = mSound.length * tBar.tBarKnob.x / tBar.tBarBg.width;

		}
		else
		{
			if (mState == "PLAY") {
				tBar.tBarKnob.x = tBar.tBarBg.width / 100 * Math.floor(mPosition / mSound.length * 100);
			}
		}
		fUpdateTime();
		//mVisual.fUpdate();
	}
	else
	{

		mLoading = true;
		txtTime.text = "Please wait..";
		txtDisplay.text = "Buffering.. ";
	}
}

private function fUpdateTime() : void
{
	var vMin : Number;
	var vSec : Number;

	switch (mState)
	{
		case "PLAY":			
			vMin = Math.floor(mPosition / 1000) / 60 >> 0;
			vSec = Math.floor(mPosition / 1000) % 60 >> 0;

			break;
		case "STOP":
			vMin = Math.floor(mCurrentPosition / 1000) / 60 >> 0;
			vSec = Math.floor(mCurrentPosition / 1000) % 60 >> 0;
			break;
	}
	
	if (vSec >= 0 && vSec < 10)
		txtTime.text = String(vMin) + ":0" + String(vSec);
	else
		txtTime.text = String(vMin) + ":" + String(vSec);
		
		}
	} 
} 