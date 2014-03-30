<?php
	require_once("simsimi.php");
	// $simsimi = new TmsSimSimi(); echo $simsimi->talk("ÄãÊÇË­Ñ½","gbk","gbk"); exit;
/**
  * wechat php test
  */

//define your token
define("TOKEN", "zrx805502507");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();
$wechatObj->responseMsg();

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
			$time = time();
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
				// $contentStr = "Welcome to wechat world!";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			} else {
				echo "Input something...";
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