<?php
include('db.class.php');
class TmsSimSimi{
	public function talk($msg,$requestEncoding='utf8',$responseEncoding='utf8',$i=0){ // key='658782'
		if( $requestEncoding == 'gbk' ) $msg = iconv('GBK', 'UTF-8', $msg);
		$msg = urlencode($msg);
		$rtnString = $this->sendMsg($msg,$i);
		if( $responseEncoding == 'gbk' ) $rtnString = iconv('UTF-8', 'GBK', $rtnString);
		$rtnString = urldecode($rtnString);
		return $rtnString;
	}
	public function sendMsg($msg,$i=0){ // key='658782'
		$header = array();
		$header[]= 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * '. '/* ';  
		$header[]= 'Accept-Language: zh-cn ';  
		$header[]= 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';  
		$header[]= 'Host: www.simsimi.com';  
		$header[]= 'Connection: Keep-Alive ';  
		$header[]= 'Cookie: JSESSIONID=F9BB999CD7919C27E724D53171A3D3F3';//JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F
		$Ref="http://www.simsimi.com/talk.htm?lc=ch";
		$Ch = curl_init();
		$Options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_URL => 'http://www.simsimi.com/func/req?msg='.$msg.'&lc=ch',       
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_REFERER	=> $Ref,
		);
		curl_setopt_array($Ch, $Options);
		$Message = json_decode(curl_exec($Ch),true);
		curl_close($Ch);
		if(@$Message['result']=='100' && $Message['response'] <> 'hi'){
			$rtnString = $Message['response'];
			if( $i<5 ) {
				if( strpos($rtnString, '微信') ) return $this->sendMsg($msg, $i+1);
				if( strpos($rtnString, 'developer.simsimi.com') ) {
					return "不好意思，死鸡了。";
				}
			} else {
				$rtnString = '不知道。';
			}
		}else{
			if( $i<5 ) return $this->sendMsg($msg, $i+1);
			$rtnString = '服务器异常，请联系管理员（http://www.zhaiyiming.com）新浪微博：@翟小明tinymins';
			// print_r($Message);
		}
		return $rtnString ;
	}
	public function saveToDb($msgStr, $fromUsername, $toUsername, $time, $msgType, $contentStr, $encoding="utf8") {
		error_reporting(0);
		$msgStr 	= $this->dbEncode($msgStr);
		$fromUsername = $this->dbEncode($fromUsername);
		$toUsername = $this->dbEncode($toUsername);
		$time 		= $this->dbEncode($time);
		$msgType 	= $this->dbEncode($msgType);
		$contentStr = $this->dbEncode($contentStr);
		
		
date_default_timezone_set('PRC');
		$file = fopen(__file__.'\\..\\record\\'.date("Y-m-d_H").'.txt','a');
		fwrite($file,"\n`$time`,`$msgType`,`$fromUsername`,`$toUsername`,`$msgStr`,`$contentStr`");
		fclose($file);
		return 0;
		
		$db = new DB(TMS_DB_DSN, TMS_DB_USER, TMS_DB_PW);
		if($encoding=="utf8") $arrData = array('time'=> iconv("UTF-8","GBK",$time), 'msgType'=> iconv("UTF-8","GBK",$msgType), 'fromUsername'=> iconv("UTF-8","GBK",$fromUsername), 'toUsername'=> iconv("UTF-8","GBK",$toUsername), 'msgStr'=> iconv("UTF-8","GBK",$msgStr), 'contentStr'=> iconv("UTF-8","GBK",$contentStr) );
		else $arrData = array('time'=>$time, 'msgType'=>$msgType, 'fromUsername'=>$fromUsername, 'toUsername'=>$toUsername, 'msgStr'=>$msgStr, 'contentStr'=>$contentStr );
		$db->query("SET NAMES '".TMS_DB_CHARSET."';");
		$db->query("SET CHARACTER SET '".TMS_DB_CHARSET."';");
		$db->query("SET CHARACTER_SET_RESULTS='".TMS_DB_CHARSET."';");
		return $db->insert(TMS_DB_TABLE_PREFIX.'chatRecord', $arrData);
	}
	private function dbEncode($str) {
		$str = str_replace('`',"'",$str);
		$str = str_replace("\n",'\n',$str);
		$str = str_replace("\r",'\r',$str);
		return $str;
	}
}
?>