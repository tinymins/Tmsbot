<?php
	require_once("inc/tmsbot.class.php");
	if (@$_GET['msg']<>''){
		$tmsbot = new TmsBot();
		echo "Input : " . $msg = $_GET['msg'];
		echo "<br/>";
		echo "Encode: " . urlencode($msg);
		echo "<br/>";
		echo "Output: " . $tmsbot->talk($msg,"utf8","utf8");
		exit;
		if($_GET['skey'] != '658782'){
			die('非法访问');
		}
	}
?>