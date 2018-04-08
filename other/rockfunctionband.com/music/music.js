$(function() {
  var flashPlayerVersion = swfobject.getFlashPlayerVersion();
  if (flashPlayerVersion.major >= 9) {
    $("#songs").css("list-style-type", "none");
    window.YMPParams = {
      defaultalbumart: "/Site/gallery/galleries/live_photos/images/band_16.jpg",
      displaystate: 1
    };
    $.getScript("http://webplayer.yahooapis.com/player.js");
  }
});
