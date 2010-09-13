<?php
require_once('./config.php');
require_once('./errors.php');
$err = new Errors(&$conf);

require_once('./database.php');
$DB = new Database(&$conf,$err);
$err->register_db($DB);

require_once('./lang.php');
$lang = new Lang(&$conf,$DB,$err);
$err->register_lang($lang);

require_once('./auth.php');
$auth = new Auth(&$conf,$DB,$err,$lang);
$err->register_auth($auth);

require_once('./bokningsfunk.php');
$bokn = new Bokningsfunk(&$conf,$DB,$err,$lang,$auth);
?>