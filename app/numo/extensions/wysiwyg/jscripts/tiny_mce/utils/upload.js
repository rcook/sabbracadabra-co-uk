// JavaScript Document
function submitUpload(theButton) {
  	var theForm = theButton.form;
	theButton.disabled = true;
	theForm.target = "upload";
	theForm.action = "../upload/upload.php";
	theForm.method = "post";
	theForm.submit();
	
}

function doneUpload() {
window.location.href = window.location.href + "?x";
}