<!--<div>
<h2>Welcome  <?php echo $_SESSION['full_name']; ?>!</h2>
<p>Your server timezone is currently set to <?php echo date("e"); ?>.</p>
</div>
-->
<?php if ($adminVersion == "3") { ?>
  <style>
  #top_bar {margin-top: 8%;}
  </style>
  <script type="text/javascript">
jQuery('a').bind('click', function() {
  jQuery("#top_bar").animate({marginTop: "0"}, 500);
  jQuery("#content").animate({opacity: 0}, 500);
//return true;

						

  //return true;
});
  </script>

<?php } ?>