<?php
/**
  * wechat php test
  */
require_once("inc/tmsbot.class.php");
// $tmsbot = new TmsSimSimi(); echo $tmsbot->talk("你是谁呀","utf8","utf8"); exit;

//define your token
define("TOKEN", iconv("UTF-8","GBK","zrx805502507"));
$wechatObj = new wechatCallbackapiTest();
// $wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapiTest {
	public function valid() {
		@$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()) {
			echo $echoStr;
			exit;
		}
	}

	public function responseMsg() {
		//get post data, May be due to the different environments
		@$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)) {
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$createTime = $postObj->CreateTime;
			$msgType = $postObj->MsgType;
			$msgId = $postObj->MsgId;
			
			$time = time();
			switch ($msgType) {
			case 'voice':
				$mediaId = $postObj->MediaId;
				$contentStr = '不要说话～听不懂听不懂听不懂～';
				$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr, '1' );
				$tmsbot = new TmsBot(false); $keyword = $mediaId;
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'image':
				$picUrl = $postObj->PicUrl;
				$title = '不要发图啦~'; $description=$picUrl; $url=$picUrl;
				$resultStr = $this->createNewsResultString( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, '1' );
				$tmsbot = new TmsBot(false); $keyword = $picUrl; $contentStr = $title;
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'location':
				$locationX = $postObj->Location_X;
				$locationY = $postObj->Location_Y;
				$scale = $postObj->Scale;
				$label = $postObj->Label;
				$contentStr = "经度：$locationX\n纬度：$locationY\n$label\n所以呢？";
				$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr, '1' );
				$tmsbot = new TmsBot(false); $keyword = "[$locationX,$locationY,$scale,$label]";
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'text':	# 回复类型为text的模板
			default:
				$tmsbot = new TmsBot();
				$keyword = trim(preg_replace('/\s\s+/', ' ', $postObj->Content ));
				if($fromUsername=='oDMR9jlit1v24L0cuoHcPYtWLa0I!'&&$keyword!="活着没？") { # Debug
					$contentStr = $tmsbot->talk( $keyword, "utf8", "utf8" );
					// $contentStr = "$fromUsername|$toUsername|$keyword|$createTime|$msgType|$msgId|$time";
					// $contentStr = $postStr ;
					// $title = '标题';$description='描述';$picURL='http://www.zhaiyiming.com/css/images/img1.png';$url='http://www.zhaiyiming.com';
					// $resultStr = $this->createNewsResultString( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, '0' );
					$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr );
					
					$file = fopen(__file__.'\\..\\inc\\record\\debug.txt','a');
					fwrite($file,"\n`$time`,`text`,`$fromUsername`,`$toUsername`,`$keyword`,`".$contentStr."`");
					fclose($file);
					
					echo $resultStr;
					exit;
				}
				if(empty( $keyword )) {
					$contentStr = "说点什么吧\ntip: 回复/help可以获取更多帮助~";
					$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr );
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'empty', $contentStr, "utf8") ;
				} else if( $keyword == 'Hello2BizUser' ) {	# 初次输入
					$contentStr = "我是小黄鸡~和我聊天吧~\ntip: 回复/help可以获取更多帮助~";
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'add', $contentStr, "utf8") ;
				} else if( $keyword == '/help' ) {	# 获取帮助
					$contentStr = "❀教我说话:\nteach 问题 回答\n❀我会算术~试试问我：\n\"log(5,25)*9^2等于几\"\n❀天气预报?试试问我：\n\"广德的天气？\"\n❀关心地震?试试问我：\n\"最近哪里地震了？\"\n❀...\n㊣更多功能正在开发~";
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'help', $contentStr, "utf8") ;
				} else if( strpos($keyword, 'teach ') === 0 && count($teach = split( ' ', $keyword )) == 3 ) {	# 调教机器人
					$contentStr = "我学会啦：\n".$teach[1].' -> '.$teach[2];
					$tmsbot->teach($teach[1],$teach[2],'utf8');
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'teach', $contentStr, "utf8") ;
				} else {	# 普通聊天
					// $contentStr = "洗洗睡吧~明天再玩~";
					$contentStr = $tmsbot->talk( $keyword, "utf8", "utf8" );
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'text', $contentStr, "utf8") ;
				}
				$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr );	# 生成xml格式的回复字符串
			}
			echo $resultStr;
		} else {
			echo "参数错误。";
			exit;
		}
	}
	private function createTextResultString ( $fromUsername, $toUsername, $time, $contentStr, $funcFlag='0' ){
		# 回复类型为text的模板
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>%s</FuncFlag>
					</xml>";
		$contentStr = str_replace(']]>','] ]>',$contentStr);
		$msgType = "text";
		return sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr, $funcFlag);
	}
	private function createNewsResultString( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, $funcFlag='0' ){
		# 回复类型为news的模板
		$newsTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<ArticleCount>1</ArticleCount>
					<Articles>
					<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>
					</Articles>
					<FuncFlag>%s</FuncFlag>
					</xml>";
		return sprintf($newsTpl, $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, $funcFlag);
	}
	private function checkSignature() {
		@$signature = $_GET["signature"];
		@$timestamp = $_GET["timestamp"];
		@$nonce = $_GET["nonce"];

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