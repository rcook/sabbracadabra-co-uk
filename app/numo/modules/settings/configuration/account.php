<?php if ($row['is_admin'] == "2" && $access->hasAccess("settings", "manage-admin-secruity")) { ?>

<fieldset>
<legend>Security Settings</legend>
<form method="post" action="module/settings/manage-admin-security/">
<input type="hidden" value="<?=$row['id']?>" name="account_id" />
This user is an Administrator.  Click <input type="submit" value="Edit" name="nocmd" /> to edit their access levels.
</form>


</fieldset>
<?php } ?>