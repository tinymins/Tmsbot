<?php
class TmsSimSimi{
	public function talk($msg,$requestEncoding="utf8",$responseEncoding="utf8",$i=0){ // key='658782'
		if( $requestEncoding == "gbk" ) $msg = iconv("GBK", "UTF-8", $msg);
		$msg = urlencode($msg);
		$rtnString = $this->sendMsg($msg,$i);
		if( $responseEncoding == "gbk" ) $rtnString = iconv("UTF-8", "GBK", $rtnString);
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
		$header[]= 'Cookie: JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F';
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
				if( strpos($rtnString, "微信") ) return $this->talk($msg, $i+1);
			} else {
				$rtnString = '不知道。';
			}
		}else{
			if( $i<5 ) return $this->talk($msg, $i+1);
			$rtnString = '服务器异常，请联系管理员（http://www.zhaiyiming.com）新浪微博：@翟小明tinymins';
			// print_r($Message);
		}
		return $rtnString ;
	}
	public function saveToDb($msgStr, $fromUsername, $toUsername, $time, $msgType, $contentStr) {
		$msgStr 		= urlencode($msgStr);
		$fromUsername 	= urlencode($fromUsername);
		$toUsername 	= urlencode($toUsername);
		$time 			= urlencode($time);
		$msgType	 	= urlencode($msgType);
		$contentStr 	= urlencode($contentStr);
		$fp = fsockopen("$_SERVER[HTTP_HOST]", $_SERVER['SERVER_PORT'], $errno, $errstr, 30);
		if ($fp) {
			$out = "GET /Software/robot/api/simsimi_sync.php?a=save&m=$msgStr&f=$fromUsername&to=$toUsername&t=$time&mt=$msgType&cs=$contentStr / HTTP/1.0\r\n";
			$out .= "Host: $_SERVER[HTTP_HOST]\r\n";
			$out .= "Connection: Close\r\n\r\n";
		echo $out."<br/>";
		
	// fwrite($socket, "POST $remote_path HTTP/1.0\r\n");
	// fwrite($socket, "User-Agent: Socket Example\r\n");
	// fwrite($socket, "HOST: $remote_server\r\n");
	// fwrite($socket, "Content-type: application/x-www-form-urlencoded\r\n");
	// fwrite($socket, "Content-length: " . (strlen($post_string) + 8) . '\r\n');
	// fwrite($socket, "Accept:*/*\r\n");
	// fwrite($socket, "\r\n");
	// fwrite($socket, "mypost=$post_string\r\n");
	// fwrite($socket, "\r\n");
	// $header = "";
	// while ($str = trim(fgets($socket, 4096))) {
		// $header .= $str;
	// } 
	// $data = "";
	// while (!feof($socket)) {
		// $data .= fgets($socket, 4096);
	// } 
		 
			fwrite($fp, $out);
			// 忽略执行结果
			while (!feof($fp)) {
				echo fgets($fp, 128);
			}
			fclose($fp);
		// } else {
			// echo "$errstr ($errno)<br />\n";
		}
	}
}
?>