<?php if(isset($_SESSION['account_id'])) { ?>
<li class='dropdown numo-top-menu'>
  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user icon-user"></i> <?php echo $_SESSION['full_name']; ?><?php if ($bootstrapVersion != "3") { ?><strong class="caret"></strong><?php } ?></a>
  <ul class="dropdown-menu">
    [NUMO.ACCESS CONTROL: LINKS(render_within_submenu=1&include_user_links=1&check=1)]
    [NUMO.BLOG: LINKS(check=1)]
    [NUMO.SHOPPING CART: MANAGE ACCOUNT LINK(&check=1)]
    [NUMO.CONTENT SECTIONS: LINKS(&check=1)]
	[NUMO.HELP DESK: LINKS(&check=1)]    
	[NUMO.LISTING SERVICE: CONTRIBUTOR LINKS]  
    [NUMO.ACCOUNTS: LINKS(show_admin_link=1&show_accounts_page=1&check=1)]
  </ul>
</li>
<style>
/*
div#contact { width: auto !important; }
li.numo-top-menu ul.dropdown-menu { left: auto; right: -1px; }
li.numo-top-menu div.dropdown-menu { left: auto; right: -1px; }
*/
</style>
<?php } else { ?>
<style>
div#contact { width: auto !important; }
li.numo-top-menu ul.dropdown-menu { left: auto; right: -1px; }
li.numo-top-menu div.dropdown-menu { left: auto; right: -1px;  }
</style>
<li class="dropdown numo-top-menu">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
			<i class="fa fa-lock icon-lock"></i>  <?php echo NUMO_SYNTAX_ACCOUNT_LOGIN_BUTTON_LABEL; ?><?php if ($bootstrapVersion != "3") { ?><strong class="caret"></strong><?php } ?></a>
			<div class="dropdown-menu contactdrop" style="padding: 15px;">
			
				[NUMO.ACCOUNTS: LOGIN_BOX]
				
			</div>
			</li>
<?php } ?>            