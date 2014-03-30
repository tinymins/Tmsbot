<?php
# 确保magic_quotes_gpc为关闭状态
function stripslashesArray(&$array) {
	is_array($array) ? array_walk($array,'stripslashesArray') : $array = stripslashes($array);
}
if(get_magic_quotes_gpc()) {
	stripslashesArray($_GET);
	stripslashesArray($_POST);
	stripslashesArray($_COOKIE);
	stripslashesArray($_FILES);
	stripslashesArray($_REQUEST);
}
ini_set('magic_quotes_runtime','0');	# 关闭溢出字符自动转义
ini_set('magic_quotes_sybase','0');
// define("TMS_DB_ENGINE","mysql");
define("TMS_DB_TABLE_PREFIX","tmsbot_");
define("TMS_DB_CHARSET","UTF8");
define("TMS_DB_DSN","odbc:driver={microsoft access driver (*.mdb)};dbq=".__FILE__."\\..\\simsimi.mdb");
// define("TMS_DB_DSN",TMS_DB_ENGINE.":dbname=".TMS_DB_NAME.";host=".TMS_DB_HOST.";charset=".TMS_DB_CHARSET);
?>