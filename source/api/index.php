<?php
	header("Content-type: text/plain");
	require_once("inc/tmsbot.class.php");
	if (@$_GET['msg']<>''){
		$tmsbot = new TmsBot();
		echo "Input : \n" . $msg = $_GET['msg'];
		echo "\n--------------------------------------------\n";
		echo "Encode: \n" . urlencode($msg);
		echo "\n--------------------------------------------\n";
		$tmsbot->talk($msg,"utf8","utf8");
		$rtnString = $tmsbot->getContentStr();
		switch ( $tmsbot->getSessionType() ) {
			case 'mobile':
				$rtnString = $rtnString . '我是号码鸡~\(≧▽≦)/~';
				break;
			case 'calc':
				$rtnString = $rtnString . '我是计算鸡~\(≧▽≦)/~';
				break;
			case 'weather':
				$rtnString = $rtnString . '我是气象鸡~\(≧▽≦)/~';
				break;
			case 'earthquake':
				$rtnString = $rtnString . '我是地震鸡(⊙o⊙)…';
				break;
			case 'express':
				$rtnString = $rtnString . '我是物流鸡~\(≧▽≦)/~';
				break;
			case 'translate':
				$rtnString = $rtnString . '我是翻译鸡~\(≧▽≦)/~';
				break;
			case 'talk':
				break;
		}
		echo "Output: \n" . $rtnString;
		exit;
		if($_GET['skey'] != '658782'){
			die('非法访问');
		}
	}
?>