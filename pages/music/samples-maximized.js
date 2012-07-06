$(function() {
  var flashPlayerVersion = swfobject.getFlashPlayerVersion();
  if (flashPlayerVersion.major >= 9) {
    var html = "<div style=\"display: none\">";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/01_ace.mp3\">Ace of Spades&mdash;Mot&ouml;rhead</a>";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/02_tiger.mp3\">Eye of the Tiger&mdash;Survivor</a>";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/03_kayleigh.mp3\">Kayleigh&mdash;Marillion</a>";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/04_prayer.mp3\">Livin' on a Prayer&mdash;Bon Jovi</a>";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/05_preach.mp3\">Papa Don't Preach&mdash;Madonna</a>";
    html += "<a class=\"htrack\" href=\"/Site/flash/mp3/06_countdown.mp3\">The Final Countdown&mdash;Europe</a>";
    html += "</div>";
    $("body").append(html);
    window.YMPParams = {
      defaultalbumart: "/Site/gallery/galleries/live_photos/images/band_16.jpg",
      displaystate: 1
    };
    $.getScript("http://webplayer.yahooapis.com/player.js");
  }
});
