<?php
class Numo {
	function Numo() {
		// Define GLOBAL variables
		DEFINE('NUMO_VERSION', '1.0');
		DEFINE('EXTENSIONS_FOLDER_NAME', 'extensions');

		$this->extensions = array();
		$this->extensions['captcha'] = true;
	}
}
$numo = new Numo();
?>