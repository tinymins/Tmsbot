<?php
	require_once("inc/simsimi.class.php");
	// $simsimi = new TmsSimSimi(); echo $simsimi->talk("你是谁呀","gbk","gbk"); exit;
/**
  * wechat php test
  */

//define your token
define("TOKEN", "zrx805502507");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();
// $wechatObj->responseMsg();

class wechatCallbackapiTest {
	public function valid() {
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()) {
			echo $echoStr;
			exit;
		}
	}

	public function responseMsg() {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)) {

			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$createTime = $postObj->CreateTime;
			$msgType = $postObj->MsgType;
			$msgId = $postObj->MsgId;
			$time = time();
			# 回复类型为text的模板
			$textTpl = "<xml>
			           <ToUserName><![CDATA[%s]]></ToUserName>
			           <FromUserName><![CDATA[%s]]></FromUserName>
			           <CreateTime>%s</CreateTime>
			           <MsgType><![CDATA[%s]]></MsgType>
			           <Content><![CDATA[%s]]></Content>
			           <FuncFlag>0</FuncFlag>
			           </xml>";
			if(!empty( $keyword )) {
				$msgType = "text";
				$simsimi = new TmsSimSimi();
				$contentStr = $simsimi->talk( $keyword, "utf8", "utf8" );
				if($keyword=="test") // 开发测试
					$contentStr = "$fromUsername|$toUsername|$keyword|$createTime|$msgType|$msgId|$time";
					// $contentStr = str_replace("]]","] ]",$postStr);
				else 
					$simsimi->saveToDb($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
				// $contentStr = "Welcome to wechat world!";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				
				echo $resultStr;
			} else {
				$msgType = "text";
				$contentStr = "说点什么吧|$fromUsername|$toUsername|$keyword|$createTime|$msgType|$msgId|$time";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				
				echo $resultStr;
			}

		} else {
			echo "";
			exit;
		}
	}

	private function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ) {
			return true;
		} else {
			return false;
		}
	}
}

?>