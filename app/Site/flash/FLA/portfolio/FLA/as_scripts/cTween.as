//----------------------------------------------------------------------------------------------------
//	cTween.as : v0.1 : 7 Feb 2009
//	Code License : MIT License (http://www.opensource.org/licenses/mit-license.php)
//	simplistika.com >> modify anyway you want but give credit where it's due.
//----------------------------------------------------------------------------------------------------
package as_scripts
{
import flash.events.Event;
import flash.utils.getTimer;
import fl.transitions.easing.Regular;

//----------------------------------------------------------------------------------------------------
//	class definition
//----------------------------------------------------------------------------------------------------
public class cTween 
{
//----------------------------------------------------------------------------------------------------
//	static data
//----------------------------------------------------------------------------------------------------
static public var xList : Array = new Array()
static private var xChild : * ;
static private var xComplete : Function;

//----------------------------------------------------------------------------------------------------
//	fStart
//----------------------------------------------------------------------------------------------------
static public function
to(
	vChild : *,
	vTarget : Object,
	vDuration : Number,
	vComplete : Function = null
) : void
{
	var v : String;
	xChild = vChild;
	xComplete = vComplete;
	for (v in vTarget)
		xList.push(
			{	
				mChild: vChild,
				mProp: v,
				mEasing: (vTarget["ease"] == undefined ? Regular.easeOut : vTarget["ease"]),
				mBegin: vChild[v],
				mEnd: vTarget[v],
				mDuration: vDuration,
				mElapsed : 0,
				mLastTime: getTimer()
			}
		);

	xChild.addEventListener(Event.ENTER_FRAME, fTween, false, 0, true);
}

//----------------------------------------------------------------------------------------------------
//	tween event
//----------------------------------------------------------------------------------------------------
static private function
fTween(
	e : Event
) : void
{
	var i : int;
	
	for (i = 0; i < xList.length; i++)
	{
		xList[i].mElapsed += getTimer() - xList[i].mLastTime;
		xList[i].mLastTime = getTimer();
		if (xList[i].mElapsed < xList[i].mDuration * 1000)
			xList[i].mChild[xList[i].mProp] = xList[i].mEasing(
				xList[i].mElapsed, 
				xList[i].mBegin, 
				xList[i].mEnd - xList[i].mBegin, 
				xList[i].mDuration * 1000);
		else
		{
			xList.splice(i, 1);
			if (xComplete != null) xComplete();			
			if (xList.length == 0) xChild.removeEventListener(Event.ENTER_FRAME, fTween);
			xComplete = null;
		}
	}
}

//----------------------------------------------------------------------------------------------------
}	// class
//----------------------------------------------------------------------------------------------------
}	// package