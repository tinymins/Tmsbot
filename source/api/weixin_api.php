﻿<?php
/**
  * wechat php test
  */

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
        require_once("inc/tmsbot.class.php");
        require_once("inc/query.call.php");
        $tmsbot = new TmsBot();
        $tmsbot->RegisterQueryCall('QueryCall::filter_mobile','QueryCall::query_mobile',251);
        $tmsbot->RegisterQueryCall('QueryCall::filter_calc','QueryCall::query_calc',252);
        $tmsbot->RegisterQueryCall('QueryCall::filter_translate','QueryCall::query_translate',253);
        $tmsbot->RegisterQueryCall('QueryCall::filter_earthquake','QueryCall::query_earthquake',254);
        $tmsbot->RegisterQueryCall('QueryCall::filter_weather','QueryCall::query_weather',254);
        $tmsbot->RegisterQueryCall('QueryCall::filter_express','QueryCall::query_express',254);

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
				$keyword = $mediaId;
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'image':
				$picUrl = $postObj->PicUrl;
				$title = '不要发图啦~'; $description=$picUrl; $url=$picUrl;
				$resultStr = $this->createNewsResultString( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, '1' );
				$keyword = $picUrl; $contentStr = $title;
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'location':
				$locationX = $postObj->Location_X;
				$locationY = $postObj->Location_Y;
				$scale = $postObj->Scale;
				$label = $postObj->Label;
				$contentStr = "经度：$locationX\n纬度：$locationY\n$label\n所以呢？";
				$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr, '1' );
				$keyword = "[$locationX,$locationY,$scale,$label]";
				$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, $msgType, $contentStr, "utf8") ;
			break;
			case 'text':	# 回复类型为text的模板
			default:
				$keyword = trim(preg_replace('/\s\s+/', ' ', $postObj->Content ));
				if(empty( $keyword )) {
					$contentStr = "说点什么吧\ntip: 回复/help可以获取更多帮助~";
					$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr );
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'empty', $contentStr, "utf8") ;
				} else if( $keyword == 'Hello2BizUser' ) {	# 初次输入
					$contentStr = "我是小黄鸡~和我聊天吧~\ntip: 回复/help可以获取更多帮助~";
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'add', $contentStr, "utf8") ;
				} else if( $keyword == '/help' ) {	# 获取帮助
					$contentStr = "❀教我说话:\nteach 问题 回答\n"
					."❀查询手机号码?回我：\n\"13701191098\"\n"
					."❀我会算术~试试问我：\n\"log(5,25)*9^2等于几\"\n"
					."❀天气预报?试试问我：\n\"广德的天气？\"\n"
					."❀关心地震?试试问我：\n\"最近哪里地震了？\"\n"
					."❀关心快递?试试回我：\n\"申通468209487473\"\n"
					."❀寻求翻译?试试回我：\n\"@法语 早上好\"\n"
					."❀...\n"
					."㊣更多功能正在开发~";
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'help', $contentStr, "utf8") ;
				} else if( strpos($keyword, 'teach ') === 0 && count($teach = split( ' ', preg_replace('/\s+/',' ',$keyword) )) == 3 ) {	# 调教机器人
					$contentStr = "我学会啦：\n".$teach[1].' -> '.$teach[2];
					$tmsbot->teach($teach[1],$teach[2],'utf8');
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'teach', $contentStr, "utf8") ;
				} else {	# 普通聊天
					// $contentStr = "洗洗睡吧~明天再玩~";
					if( $tmsbot->talk( $keyword, "utf8", "utf8" ) ) {
						$contentStr = $tmsbot->getContentStr();
						switch ( $tmsbot->getSessionType() ) {
							case 'mobile':
								$contentStr .= '我是号码鸡~\(≧▽≦)/~';
								break;
							case 'calc':
								$contentStr .= '我是计算鸡~\(≧▽≦)/~';
								break;
							case 'weather':
								$contentStr .= '我是气象鸡~\(≧▽≦)/~';
								break;
							case 'earthquake':
								$contentStr .= '我是地震鸡(⊙o⊙)…';
								break;
							case 'express':
								$contentStr .= '我是物流鸡~\(≧▽≦)/~';
								break;
							case 'translate':
								$contentStr .= '我是翻译鸡~\(≧▽≦)/~';
								break;
							case 'normal':
								break;
						}
					} else {
						// $contentStr = "洗洗睡吧~明天再玩~";
						$contentStr = "唔。智商有限，不知道该怎么回答了。教教我吧~\n回复以下格式教我：\nteach 早安 早呀~\n这样当你再次对我说'早安'时我就会回答你'早呀~'";
					}
					$tmsbot->saveLog($keyword, $fromUsername, $toUsername, $time, 'text', $contentStr, "utf8") ;
				}
				switch ( $tmsbot->getContentType() ) {
					case 'news':
						$title = $tmsbot->getContentTitle(); $description = $tmsbot->getContentDescription();
						$picURL = $tmsbot->getContentPicURL(); $url = $tmsbot->getContentURL();
						$resultStr = $this->createNewsResultString ( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, '0' );	# 生成xml格式的回复字符串
						break;
					case 'text':
					default:
						$resultStr = $this->createTextResultString ( $fromUsername, $toUsername, $time, $contentStr );	# 生成xml格式的回复字符串
				}
			}
			echo $resultStr;
		} else {
			echo "参数错误。";
			exit;
		}
	}
	# 回复类型为text的模板
	private function createTextResultString ( $fromUsername, $toUsername, $time, $contentStr, $funcFlag='0' ){
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
	# 回复类型为news的模板
	private function createNewsResultString( $fromUsername, $toUsername, $time, $contentStr, $title, $description, $picURL, $url, $funcFlag='0' ){
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