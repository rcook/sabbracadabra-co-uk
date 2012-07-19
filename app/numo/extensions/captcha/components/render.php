<?
print "hello world";
return;

$captchaStatus = 0;
$baseURL = "https://www.firewidget.com/(".$sessionID.")/";
$CAPTCHA_IMAGE_URI = $baseURL."bin/service/6/dl,{$_GET[domain_license_id]}/captcha_image/";
?>
	
function renderCaptcha() {
  myCaptchaDiv    = getElementById("fw__captcha");
  myCaptchaToggle = myForm['fw__form_use_captcha'];

  if ((myCaptchaToggle == null || myCaptchaToggle.value == "1") && myCaptchaDiv) {
    myCaptchaCodeTopDiv = document.createElement("div");
    myCaptchaCodeText = document.createTextNode("Enter Validation Code Below");
    myCaptchaCodeTopDiv.appendChild(myCaptchaCodeText);
	myCaptchaCodeTopDiv.setAttribute("className", "myCaptchaCodeTopDiv");
	myCaptchaCodeTopDiv.setAttribute("class", "myCaptchaCodeTopDiv");


    myCaptchaCodeLeftDiv = document.createElement("div");
    myCaptchaCodeInput = document.createElement("input");
    myCaptchaCodeInput.setAttribute("type", "text");
    myCaptchaCodeInput.setAttribute("id", "captcha_code");
    myCaptchaCodeInput.setAttribute("name", "fw__code");
    myCaptchaCodeLeftDiv.appendChild(myCaptchaCodeInput);
	myCaptchaCodeInput.setAttribute("value", "Enter Code");
	myCaptchaCodeInput.setAttribute("className", "myCaptchaInputBlank");
	myCaptchaCodeInput.setAttribute("class", "myCaptchaInputBlank");
 
	myCaptchaCodeInput.onclick = function() { 
	  if (this.value == "Enter Code") {
	    this.value = "";
		this.setAttribute("className", "myCaptchaInput");
		this.setAttribute("class", "myCaptchaInput");
		
	  } 
	}
	myCaptchaCodeInput.onfocus = function() {
	  if (this.value == "Enter Code") {
	    this.value = "";
		this.setAttribute("className", "myCaptchaInput");
		this.setAttribute("class", "myCaptchaInput");
		
	  }	
	}
	myCaptchaCodeInput.onblur  = function() { 
	  if (this.value == "") { 
	    this.value = "Enter Code"; 
		this.setAttribute("className", "myCaptchaInputBlank");
		this.setAttribute("class", "myCaptchaInputBlank");
	  } else {
		this.setAttribute("className", "myCaptchaInput");
		this.setAttribute("class", "myCaptchaInput");  
	  }
	}
	
    myCaptchaCodeLeftDiv.setAttribute("className", "myCaptchaCodeLeftDiv");
    myCaptchaCodeLeftDiv.setAttribute("class", "myCaptchaCodeLeftDiv");
 
 
    myCaptchaCodeRightDiv = document.createElement("div");
    myCaptchaImage = document.createElement("img");
    myCaptchaImage.setAttribute("border", "1");
    myCaptchaImage.setAttribute("src", "<?=$CAPTCHA_IMAGE_URI?>");
    myCaptchaCodeRightDiv.appendChild(myCaptchaImage);
    myCaptchaCodeRightDiv.setAttribute("className", "myCaptchaCodeRightDiv");
    myCaptchaCodeRightDiv.setAttribute("class", "myCaptchaCodeRightDiv");

    myCaptchaCodeClearDiv = document.createElement("div");
    myCaptchaCodeClearDiv.setAttribute("className", "myCaptchaCodeClearDiv");
    myCaptchaCodeClearDiv.setAttribute("class", "myCaptchaCodeClearDiv");
    
	myCaptchaDiv.appendChild(myCaptchaCodeTopDiv);
	myCaptchaDiv.appendChild(myCaptchaCodeRightDiv);
    myCaptchaDiv.appendChild(myCaptchaCodeLeftDiv);
    myCaptchaDiv.appendChild(myCaptchaCodeClearDiv);
  }
}

addOnloadEvent(renderCaptcha);
