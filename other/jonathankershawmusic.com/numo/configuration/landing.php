<!--<div>
<h2>Welcome  <?php echo $_SESSION['full_name']; ?>!</h2>
<p>Your server timezone is currently set to <?php echo date("e"); ?>.</p>
</div>
-->
<?php if ($adminVersion == "3") { ?>
  <style>
   #top_bar {margin-top: 3%;}
   
  /* Main marketing message and sign up button */
      .jumbotron {
        margin: 40px 0 80px 0px;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .lead {
        font-size: 24px;
        line-height: 1.25;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
  </style>
  <script type="text/javascript">
//jQuery('a').bind('click', function() {
//  jQuery("#top_bar").animate({marginTop: "0"}, 500);
 // jQuery("#content").animate({opacity: 0}, 500);
//return true;

						

  //return true;
//});
  </script>
  <!--
<div class="hero-unit">
        <h1>Surprise!  New Features Included!</h1>
        <p>We've poured over the Numo System over the last month, upgraded a multitude of systems, and 
        added a plethura of features.</p>
        <p>
        <a class="btn btn-large btn-success" href="#">Learn More</a>
        <a class="btn btn-large" href="#">Hide</a>
        </p>
      </div>
      -->
<?php } ?>