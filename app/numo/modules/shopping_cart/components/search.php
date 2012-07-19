<form method="post" action="<?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/manage.numo?module=shopping_cart&component=catalog">
<input type="text" name="search_terms" value="" /><input type="hidden" name="numo_cmd" value="search" /><input type="submit" name="nocmd" value="<?=NUMO_SYNTAX_SHOPPING_CART_SEARCH_BUTTON_LABEL?>" />
</form>