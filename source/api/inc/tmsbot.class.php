<?php
// error_reporting(0);
require_once('db.class.php');
require_once("splitword.class.php");
class TmsBot{
	var $db;														//用户数据库
	function __construct($dbUsage = true) {										//构造函数，连接数据库
		if( $dbUsage ) {
			@$this->db = new DB(TMS_DB_DSN, TMS_DB_USER, TMS_DB_PW);
			$this->db->query("SET NAMES '".TMS_DB_CHARSET."';");
			$this->db->query("SET CHARACTER SET '".TMS_DB_CHARSET."';");
			$this->db->query("SET CHARACTER_SET_RESULTS='".TMS_DB_CHARSET."';");
		}
		$this->contentStr = '';
		$this->contentType = '';
		$this->newsURL = '';
		$this->newsTitle = '';
		$this->newsPicURL = '';
		$this->newsDescription = '';
		$this->sessionType = '';
        $this->RegisterQueryCall('Tmsbot::filter_talk','Tmsbot::query_talk',512);
	}
	var $contentStr, $contentType, $newsURL, $newsTitle, $newsPicURL, $newsDescription, $sessionType, $queryCall = array();
	public function getSessionType() 	{ return $this->sessionType; }
	public function getContentType() 	{ return $this->contentType; }
	public function getContentStr() 	{ return $this->contentStr; }
	public function getContentURL() 	{ return $this->newsURL; }
	public function getContentTitle() 	{ return $this->newsTitle; }
	public function getContentPicURL() 	{ return $this->newsPicURL; }
	public function getContentDescription() { return $this->newsDescription; }
	public function setTextContent( $cSessionType, $cString ) {	$this->contentType = 'text'; $this->sessionType = $cSessionType; $this->contentStr = $cString; }
	public function setNewsContent( $cSessionType, $cString, $nURL, $nTitle, $nPicURL, $nDescription) {	$this->contentType = 'news'; $this->sessionType = $cSessionType; $this->contentStr = $cString; $this->newsURL = $nURL; $this->newsTitle = $nTitle; $this->newsPicURL = $nPicURL; $this->newsDescription = $nDescription; }
	public function iconvAll( $encodingTo ) {
		$encodingTo = str_replace('UTF8','UTF-8',strtoupper($encodingTo));
		$this->sessionType = $this->iconv( $encodingTo, $this->getSessionType() );
		$this->contentType = $this->iconv( $encodingTo, $this->getContentType() );
		$this->contentStr = $this->iconv( $encodingTo, $this->getContentStr() );
		$this->newsURL = $this->iconv( $encodingTo, $this->getContentURL() );
		$this->newsTitle = $this->iconv( $encodingTo, $this->getContentTitle() );
		$this->newsPicURL = $this->iconv( $encodingTo, $this->getContentPicURL() );
		$this->newsDescription = $this->iconv( $encodingTo, $this->getContentDescription() );
	}
    /**
     * (void) RegisterQueryCall(string calc_fn_id, optional string filter_fn_id = null, optional int priority = 255)
     **/
    public function RegisterQueryCall($filter_fn_id, $calc_fn_id, $priority = 255) {
        if ( is_callable($calc_fn_id) && is_callable($filter_fn_id) ) $this->queryCall []= array(
            'priority' => $priority,
            'filter_fn_id' => $filter_fn_id,
            'calc_fn_id' => $calc_fn_id,
        );
        @$this->sort_array($this->queryCall, 'priority', 'asc', 'number');
    }
    /**
     * (bool)talk(string msg, optional string requestEncoding, optional string responseEncoding)
     **/
	public function talk($msg,$requestEncoding='utf8',$responseEncoding='utf8',$i=0){ #对外接口：获取指定字符串的回复
		if( $requestEncoding == 'gbk' ) $msg = iconv('GBK', 'UTF-8', $msg);
        
        foreach ( $this->queryCall as $k => $qc ) {
            $msg_tmp = ( is_callable( $qc['filter_fn_id'] ) ) ? call_user_func_array( $qc['filter_fn_id'] , array( $msg, $this ) ) : $msg;
            if ( $msg_tmp!==null ) if( $result = call_user_func_array( $qc['calc_fn_id'] , array( $msg_tmp, $this ) ) ) {
                if( $result['type']=='text' ) $this->setTextContent( $result['data']['type'], $result['data']['content'] );
                elseif( $result['type']=='news' ) $this->setNewsContent(
                    $result['data']['type'],
                    $result['data']['content'],
                    $result['data']['url'],
                    $result['data']['title'],
                    $result['data']['pic_url'], 
                    $result['data']['description']
                );
                return true;
            }
        }
		return false;
	}
	public function teach($msg,$content,$requestEncoding='utf8'){	#对外接口：用户自己调教机器人
		if( $requestEncoding == 'gbk' ) { $msg = iconv('GBK', 'UTF-8', $msg); $content = iconv('GBK', 'UTF-8', $content); }
		// if( $requestEncoding == 'utf8' ) { $msg = iconv('UTF-8', 'GBK', $msg); $content = iconv('UTF-8', 'GBK', $content); }
		$_checker = $this->db->get_one('SELECT * FROM '.TMS_DB_TABLE_PREFIX.'QaA WHERE msgStr = ? AND contentStr = ?',array($msg, $content));
		if($_checker) {	# 数据库记录已存在
			if( $_checker['deleted'] == 0 ) {	# 没人举报过，不操作。
				return 0;
			} else {	# 有人举报过，归位。
				$this->db->update(TMS_DB_TABLE_PREFIX.'QaA', array('deleted'=> 0), array('msgStr'=> $msg, 'contentStr'=> $content));
				return 1;
			}
		} else {	# 数据库中不存在，添加新记录。
			$this->db->insert(TMS_DB_TABLE_PREFIX.'QaA', array('msgStr'=> $msg, 'contentStr'=> $content, 'deleted'=> 0));
			return 2;
		}
		return false;
	}
    public function filter_talk( $str, $me ) {
        return $str;
    }
	public function query_talk( $msg, $me ) {
		$rtnString = $me->getFromDb($msg);
		if( $rtnString == false ) {	# 如果数据库中没有
			$rtnString = $me->sendMsg(urlencode($msg));	# 尝试连接simsimi
			if( $rtnString == false ) {	# 如果被simsimi封掉
				$filtered_msg = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u"," ",$msg);
				$sp = new SplitWord();	# 尝试数据库分词搜索（采取最长两个分词匹配）
				$splitedArr = split(' ',trim(iconv('GBK', 'UTF-8', $sp->SplitRMM(iconv('UTF-8', 'GBK', $filtered_msg)))));
				$keyword1 = $splitedArr[0];$keyword2 = $splitedArr[count($splitedArr)-1];$keywordlen1 = strlen($keyword1);$keywordlen2 = strlen($keyword2);
				for($i=1; $i<count($splitedArr)-2; $i++) {
					if(strlen($splitedArr[$i])>$keywordlen1){
						$keyword1 = $splitedArr[$i];
					} else if(strlen($splitedArr[$i])>$keywordlen2) {
						$keyword2 = $splitedArr[$i];
					}
				}
				$rtnString = $me->getFromDb($keyword1,$keyword2);
				# 失败的话，显示无能为力了。
				if( $rtnString == false ) $rtnString = "唔。智商有限，不知道该怎么回答了。教教我吧~\n回复以下格式教我：\nteach 早安 早呀~\n这样当你再次对我说'早安'时我就会回答你'早呀~'";
			} else {
				$rtnString = urldecode($rtnString);
				$me->teach($msg,$rtnString,'utf8');
			}
		}
        return array(
            'type'=>'text',
            'data'=>array(
                'type'=>'normal',
                'content'=>$rtnString
            )
        );
	}
	public function sendMsg($msg) { // #发送给其他的SIMSIMI中转服务器请求数据
		if( $ret = $this->send2simsimi($msg) ) return $ret;
		if( $ret = $this->send2maizihuakai($msg) ) return $ret;
		return false;
	}
	public function send2simsimi($msg,$i=0){
		$responseHTML = $this->get_var_curl('http://www.simsimi.com/func/req?lc=ch&msg='.$msg,'JSESSIONID=F9BB999CD7919C27E724D53171A3D3F3','http://www.simsimi.com/talk.htm?lc=ch');
		$Message = json_decode($responseHTML,true);
		if(@$Message['result']=='100' && $Message['response'] <> 'hi'){
			$rtnString = $Message['response'];
			if( $i<5 ) {
				if( strpos($rtnString, '微信') ) return $this->send2simsimi($msg, $i+1);
				if( strpos($rtnString, 'developer.simsimi.com') ) {
					return false;
				}
				if( strpos($rtnString, 'SimSimi is tired, I only can speak 200 time a day.')!==false ) {
					return false;
				}
			} else {
				$rtnString = false;
			}
		} else {
			if( $i<5 ) return $this->send2simsimi($msg, $i+1);
			$rtnString = false;//'服务器异常，请联系管理员（http://www.zhaiyiming.com）新浪微博：@翟小明tinymins';
			// print_r($Message);
		}
		return $rtnString ;
	}
	public function send2maizihuakai($msg,$i=0){
		$responseHTML = $this->get_var_curl('http://maizihuakai.com/xiaohuangji/get.php?Msg='.$msg,'','http://maizihuakai.com/xiaohuangji/');
		if(preg_match_all("/<strong>小黄鸡[^<]*<\/strong><br\/>([^<]*)<\/div>/iu",$responseHTML,$arr)){
			$rtnString = $arr[1][0];
			if( $i<5 ) {
				if( strpos($rtnString, '微信') ) return $this->send2maizihuakai($msg, $i+1);
				if( strpos($rtnString, 'developer.simsimi.com') ) {
					return false;
				}
				if( strpos($rtnString, 'SimSimi is tired, I only can speak 200 time a day.')!==false ) {
					return false;
				}
			} else {
				$rtnString = false;
			}
		} else {
			if( $i<5 ) return $this->send2maizihuakai($msg, $i+1);
			$rtnString = false;//'服务器异常，请联系管理员（http://www.zhaiyiming.com）新浪微博：@翟小明tinymins';
			// print_r($Message);
		}
		return $rtnString ;
	}
	public function get_var_curl($url,$cookie='',$Ref=''){ // #发送给其他的SIMSIMI中转服务器请求数据
		if( stripos($url, '://')!==false ) {
			$urlprot = preg_replace('/:\\/\\/.*$/i','',$url);
			$url  = preg_replace('/^https*:\\/\\//i','',$url);
			$host = preg_replace('/\\/.*$/i','',$url);
			$url  = preg_replace('/^.*?\\//i','/',$url);
		} else if( stripos($url, '/')===0 ) {
			$urlprot = preg_replace('/\\/.*$/i','',$_SERVER['SERVER_PROTOCOL']);
			$host = $_SERVER['REMOTE_HOST'];
		} else {
			$urlprot = preg_replace('/\\/.*$/i','',$_SERVER['SERVER_PROTOCOL']);
			$host = $_SERVER['REMOTE_HOST'];
			$url  = preg_replace('/\\/[^\\/]*$/i','/',$_SERVER['REQUEST_URI']).$url;
		}
		if( strlen($Ref)===0 ) {$Ref = "$urlprot://$host/";}
		$header = array();
		$header[]= 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * '. '/* ';  
		$header[]= 'Accept-Language: zh-cn ';  
		$header[]= 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';  
		$header[]= 'Host: '.$host;  
		$header[]= 'Connection: Keep-Alive ';  
		$header[]= 'Cookie: '.$cookie;//JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F
		$Ch = curl_init();
		$Options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_URL => "$urlprot://$host$url",       
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_REFERER	=> $Ref,
		);
		curl_setopt_array($Ch, $Options);
		$responseHTML = curl_exec($Ch);
		curl_close($Ch);
		return $responseHTML ;
	}
	public function saveLog($msgStr, $fromUsername, $toUsername, $time, $msgType, $contentStr, $encoding="utf8") {	#把各种用户操作记录写入存盘
		error_reporting(0);
		$msgStr 	= $this->dbEncode($msgStr);
		$fromUsername = $this->dbEncode($fromUsername);
		$toUsername = $this->dbEncode($toUsername);
		$time 		= $this->dbEncode($time);
		$msgType 	= $this->dbEncode($msgType);
		$contentStr = $this->dbEncode($contentStr);
		
		date_default_timezone_set('PRC');
		if( !is_dir(__file__.'\\..\\record\\'.date("Y-m").'\\'.date("Y-m-d").'\\') ) {
			if( !is_dir(__file__.'\\..\\record\\'.date("Y-m").'\\') ) {
				if( !is_dir(__file__.'\\..\\record\\') ) {
					mkdir(__file__.'\\..\\record\\');
				}
				mkdir(__file__.'\\..\\record\\'.date("Y-m").'\\');
			}
			mkdir(__file__.'\\..\\record\\'.date("Y-m").'\\'.date("Y-m-d").'\\');
		}
		$file = fopen(__file__.'\\..\\record\\'.date("Y-m").'\\'.date("Y-m-d").'\\'.date("Y-m-d_H").'.txt','a');
		fwrite($file,"\n`$time`,`$msgType`,`$fromUsername`,`$toUsername`,`$msgStr`,`$contentStr`");
		fclose($file);
		return 0;
	}
	public function getFromDb($msgStr1,$msgStr2='') {	# 从数据库查询回复
		if($msgStr2=='') $msgStr2=$msgStr1;
		$msgStr1 = $this->iconv(TMS_DB_CHARSET,$msgStr1,"UTF-8");
		$msgStr2 = $this->iconv(TMS_DB_CHARSET,$msgStr2,"UTF-8");
		
		$arrData = array( '%'.$msgStr1.'%', '%'.$msgStr2.'%' );		
		$sql="SELECT * FROM ".TMS_DB_TABLE_PREFIX.'QaA WHERE msgStr LIKE ? AND msgStr LIKE ? AND deleted < 2';
		$_dish = $this->db->get_all($sql,$arrData);
		if( count($_dish)>0 ) {
			$rtnString = $this->iconv("UTF-8",$this->dbDecode($_dish[mt_rand(0,count($_dish)-1)]['contentStr']),TMS_DB_CHARSET);
		} else
			$rtnString = false;
		return $rtnString;
	}
    public function dbExecute($sql,$para=array()){
        return $this->db->execute($sql,$para);
    }
    public function dbGetAll($sql,$para=array()){
        return $this->db->get_all($sql,$para);
    }
    public function dbGetOne($sql,$para=array()){
        return $this->db->get_one($sql,$para);
    }
	private function dbEncode($str) {
		$str = addcslashes($str, "\\");
		$str = addcslashes($str, "`");
		$str = addcslashes($str, "'");
		$str = addcslashes($str, '"');
		$str = str_replace("\n", "\\n", $str);
		$str = str_replace("\r", "\\r", $str);
		$str = str_replace("\t", "\\t", $str);
		return $str;
	}
	private function dbDecode($str) {
		return stripcslashes($str);
	}
	function iconv( $toEncoding, $string, $from_encoding_list = '' ) { # 判断文本编码类型
		$toEncoding = trim(str_replace('UTF8','UTF-8',strtoupper($toEncoding)));
		$from_encoding_list = explode(' ', trim(str_replace('UTF8','UTF-8',strtoupper($from_encoding_list))));
		$fromEncoding = (empty($from_encoding_list)) ? Tmsbot::detectEncoding( $string, $toEncoding ) : Tmsbot::detectEncoding( $string, $from_encoding_list );
		if( $fromEncoding && $fromEncoding!=$toEncoding ) $string = iconv( $fromEncoding, $toEncoding, $string );
		return $string;
	}
	function detectEncoding( $string, $encoding_list = array('GBK', 'GB2312', 'ASCII', 'UTF-8') ) { # 判断文本编码类型(是否为$is_encode)
		// if($this->is_utf8($string)) return 'UTF-8';
		// if(preg_match("/[".chr(0xa1)."-".chr(0xff)."]/",$string)) return 'GBK';
		// if(preg_match("/[x{4e00}-x{9fa5}]/u",$string)) return 'UTF-8';
		foreach($encoding_list as $c){
			if( $string === @iconv(($c=='UTF-8')?'GB2312':'UTF-8', $c, iconv($c, ($c=='UTF-8')?'GB2312':'UTF-8', $string))){ return $c; }
		}
		return null;
	}
    /**
     * 对二维数组进行排序
     * @param $array
     * @param $keyid 排序的键值
     * @param $order 排序方式 'asc':升序 'desc':降序
     * @param $type  键值类型 'number':数字 'string':字符串
     */
    function sort_array(&$array, $keyid, $order = 'asc', $type = 'number') {
        if (is_array($array)) {
            foreach($array as $val) {
                $order_arr[] = $val[$keyid];
            }

            $order = ($order == 'asc') ? SORT_ASC: SORT_DESC;
            $type = ($type == 'number') ? SORT_NUMERIC: SORT_STRING;

            array_multisort($order_arr, $order, $type, $array);
        }
    }
}
?>