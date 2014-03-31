<?php
	header("Content-type: text/plain");
	require_once("inc/tmsbot.class.php");
	$tmsbot = new TmsBot();
	// echo $tmsbot->dbGetOne("select count(*) from ".TMS_DB_TABLE_PREFIX.'QaA WHERE contentStr LIKE "SimSimi is tired, I only can speak 200 time a day. Please visit again tomorrow. See ya~ "');
	echo $tmsbot->dbExecute("delete from ".TMS_DB_TABLE_PREFIX.'QaA WHERE contentStr LIKE "SimSimi is tired, I only can speak 200 time a day. Please visit again tomorrow. See ya~ "');
	exit;
?>