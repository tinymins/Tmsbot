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
define("TMS_BOT_BASE_URL","http://zhaiyiming.com/software/robot/");
define("TMS_DB_TABLE_PREFIX","tmsbot_");
/*Local Debug*/
define("TMS_DB_HOST","localhost");
define("TMS_DB_NAME","tmsbot");
define("TMS_DB_USER","tmsbot");
define("TMS_DB_PW","tmsbot");
define("TMS_DB_CHARSET","UTF8");
/*Local Debug*/
/*End*/
// define("TMS_DB_DSN","odbc:driver={microsoft access driver (*.mdb)};dbq=".__FILE__."\\..\\simsimi.mdb");
define("TMS_DB_DSN","mysql:host=".TMS_DB_HOST.";port=3306;dbname=".TMS_DB_NAME.";charset=".TMS_DB_CHARSET);
?>