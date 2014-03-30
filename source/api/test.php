<?php
	require_once("inc/simsimi.class.php");
	$simsimi = new TmsSimSimi();
	echo $simsimi->talk("你是谁呀");
	// echo $simsimi->saveToDb("你是谁呀", "测试账户", "测试账户2", "20130001", "1", $simsimi->talk("你是谁呀"));
	exit;
?>