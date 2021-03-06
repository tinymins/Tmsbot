<?php
class QueryCall {
    public static function TestFilter($str,$me){ return $str; }
    public static function TestCalc($str,$me){ return array(
        'type'=>'text',
        'data'=>array(
            'type'=>'test',
            'content'=>'这是返回值'
        )
    ); }
    public static function filter_calc($expr,$me) {
        $expr = str_replace('π','pi()',$expr);	# 替换pi()
        $expr = str_replace('加','+',$expr);	# 替换加
        $expr = str_replace('减','-',$expr);	# 替换减
        $expr = str_replace('乘','*',$expr);	# 替换乘
        $expr = str_replace('除','/',$expr);	# 替换除
        $expr = str_replace('平方','^2',$expr);	# 替换平方
        $expr = str_replace('以','',$expr);	# 替换以
        $expr = str_replace('的','',$expr);	# 替换的
        preg_match_all("/([\d\.\/\*\-+^%√\(sincota\),lgexpbeforud]{3,}(?=.*(?=等于什么|等于几|等于多少|是几|是多少))|^[\d\.\/\*\-+^%√\(sincota\),lgexpbeforud]{3,}$)/", $expr, $matches);
        if( count($matches[1])>0 ) return $matches[1];
        else return null;
    }
    public static function query_calc($matches,$me) {
        require_once('math/eval_expr_to_rpn.php');
		require_once('math/eval_rpn.php');
		require_once('math/eval_my_function.php');
        $rtnString = ''; $succeed = false;
        foreach( $matches as $expr ) {
            $ans = eval_expr_to_rpn($expr);	# 获取表达式
            $error = get_error_info();	# 获取错误
            if ($error === null) {	# 表达式无非法字符
                $ans = eval_rpn($ans);
                $error = get_error_rpn();
                if ($error !== null) {	# 表达式格式错误处理
                    $ans = get_error_rpn();
                } else if( strpos($ans,'INF')!==false ) {	# 溢出提示
                    $ans = '这么大的数,鸡才懒得算╮(╯▽╰)╭';
                }
                $ans = preg_replace('/E\\+*/i','×10^',$ans);	# 替换掉结果中的次方符号E,便于阅读。
                $rtnString .= "{$expr}={$ans}\n";$succeed = true;
            } else {	# 表达式非法字符错误处理
                $ans = get_error_info();
                $rtnString .= "{$expr}={$ans}\n";
            }
        }
        if($succeed) return array(
            'type'=>'text',
            'data'=>array(
                'type'=>'calc',
                'content'=>$rtnString
            )
        );
    }
    public static function filter_earthquake($expr,$me) {
        preg_match_all("/(?:最近|哪|有).*地震/u", $expr, $matches);
        if( count($matches[0])>0 ) return true;
    }
    public static function query_earthquake($expr,$me) {
        # 获取地震信息。
        $rtnString = '未查询到相关数据';
        // $responseHTML = $me->iconv('UTF-8',$me->get_var_curl("http://www.csndmc.ac.cn/newweb/recent_quickdata.jsp"),TMS_DB_CHARSET.' utf-8');
        // preg_match_all("/<tr>[^<]*<[^>]+>([\\-\\d\\s\\.\\:]+)<[^>]+>[^<]*<[^>]+>([\\d\\.]+)<\\/td>[^<]*<[^>]+>([\\d\\.]+)<\\/td>[^<]*<[^>]+>([\\d\\.]+)<\\/td>[^<]*<[^>]+>([\\d\\.]+)<\\/td>[^<]*<[^>]+>([^>]+)<\\/td>[^<]*<\\/tr>/uims", $responseHTML, $matches);
        $responseHTML = $me->iconv('UTF-8',$me->get_var_curl("http://www.ceic.ac.cn/speedsearch?time=6"),TMS_DB_CHARSET.' utf-8');
        preg_match_all("/<tr[^>]*>[^<]*<td[^>]*>(?'eq_level'[^<]+)<\/td>[^<]*<td[^>]*>(?'eq_time'[^<]+)<\/td>[^<]*<td[^>]*>(?'eq_lon'[^<]+)<\/td>[^<]*<td[^>]*>(?'eq_lat'[^<]+)<\/td>[^<]*<td[^>]*>(?'eq_depth'[^<]+)<\/td>[^<]*<td[^>]*>[^<]*<a\shref=\"(?'eq_url'[^<]+)\">(?'eq_name'[^<]+)<\/a>[^<]*<\/td>.*?<\/tr>/uims", $responseHTML, $matches);//print_r($matches);die();
        if( count($matches[0])>0 ) {
            $rtnString = '';
            for($i=0;$i<((count($matches[0])-1)>3?3:(count($matches[0])-1));$i++) {
                $rtnString .= "❀{$matches['eq_name'][$i]}\n{$matches['eq_time'][$i]}\n经{$matches['eq_lon'][$i]},纬{$matches['eq_lat'][$i]},深{$matches['eq_depth'][$i]}km\n震级：{$matches['eq_level'][$i]}级\n";
            }
             return array(
                'type'=>'text',
                'data'=>array(
                    'type'=>'earthquake',
                    'content'=>$rtnString
                )
            ); 
        } else {
            return null;
        }
    }
    public static function filter_express($expr,$me) {
		preg_match_all("/([\x{4e00}-\x{9fa5}]+)[^\x{4e00}-\x{9fa5}]*?([a-zA-Z0-9]{7,})/u", $expr, $matches);
		if( count($matches[1])>0 ) { return $matches; }
    }
    public static function query_express($matches,$me) {
		$expresses = array (
			'auspost' => '澳大利亚邮政(英文结果）',
			'aae' => 'AAE',
			'anxindakuaixi' => '安信达',

			'baifudongfang' => '百福东方',
			'bht' => 'BHT',
			'youzhengguonei' => '包裹/平邮/挂号信',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'bangsongwuliu' => '邦送物流',

			'cces' => '希伊艾斯（CCES）',
			'coe' => '中国东方（COE）',
			'city100' => '城市100',
			'chuanxiwuliu' => '传喜物流',
			'canpost' => '加拿大邮政Canada Post（英文结果）',
			'canpostfr' => '加拿大邮政Canada Post(德文结果）',

			'datianwuliu' => '大田物流',
			'debangwuliu' => '德邦物流',
			'dpex' => 'DPEX',
			'dhl' => 'DHL(中文结果）',
			'dhlen' => 'DHL(英文结果）',
			'dhlde' => 'DHL（德文结果）',
			'dsukuaidi' => 'D速快递',
			'disifang' => '递四方',

			'ems' => 'EMS(中文结果)',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'ems' => 'E邮宝',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'emsen' => 'EMS（英文结果）',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）

			'fedex' => 'Fedex（国际）',//（说明：Fedex是国际，国内的请用“lianbangkuaidi”）
			'fedexus' => 'FedEx-美国',
			'feikangda' => '飞康达物流',
			'feikuaida' => '飞快达',
			'rufengda' => '凡客如风达',
			'fengxingtianxia' => '风行天下',
			'feibaokuaidi' => '飞豹快递',

			'ganzhongnengda' => '港中能达',
			'guangdongyouzhengwuliu' => '广东邮政',
			'youzhengguonei' => '挂号信',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'youzhengguonei' => '国内邮件',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'youzhengguoji' => '国际邮件',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'gls' => 'GLS',
			'gongsuda' => '共速达',

			'huitongkuaidi' => '汇通快运',
			'tiandihuayu' => '华宇物流',
			'hengluwuliu' => '恒路物流',
			'huaxialongwuliu' => '华夏龙',
			'tiantian' => '海航天天',
			'haiwaihuanqiu' => '海外环球',
			'hebeijianhua' => '河北建华',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限
			'haimengsudi' => '海盟速递',
			'huaqikuaiyun' => '华企快运',
			'haihongwangsong' => '山东海红',

			'jiajiwuliu' => '佳吉物流',
			'jiayiwuliu' => '佳怡物流',
			'jiayunmeiwuliu' => '加运美',
			'jinguangsudikuaijian' => '京广速递',
			'jixianda' => '急先达',
			'jinyuekuaidi' => '晋越快递',
			'jietekuaidi' => '捷特快递',
			'jindawuliu' => '金大物流',
			'jialidatong' => '嘉里大通',

			'kuaijiesudi' => '快捷速递',
			'kangliwuliu' => '康力物流',
			'kuayue' => '跨越物流',

			'lianhaowuliu' => '联昊通',
			'longbanwuliu' => '龙邦物流',
			'lanbiaokuaidi' => '蓝镖快递',
			'lejiedi' => '乐捷递',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限
			'lianbangkuaidi' => '联邦快递（国内）',//（说明：国外的请用 fedex）
			'lijisong' => '立即送',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限)
			'longlangkuaidi' => '隆浪快递',

			'minghangkuaidi' => '民航快递',
			'menduimen' => '门对门',
			'meiguokuaidi' => '美国快递',
			'mingliangwuliu' => '明亮物流',

			'ocs' => 'OCS',
			'ontrac' => 'onTrac',

			'quanchenkuaidi' => '全晨快递',
			'quanjitong' => '全际通',
			'quanritongkuaidi' => '全日通',
			'quanyikuaidi' => '全一快递',
			'quanfengkuaidi' => '全峰快递',
			'sevendays' => '七天连锁',

			'rufengda' => '如风达快递',

			'shentong' => '申通',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'shentong' => '申通E物流',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'shunfeng' => '顺丰速递（中文结果）',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'shunfengen' => '顺丰（英文结果）',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'santaisudi' => '三态速递',
			'shenghuiwuliu' => '盛辉物流',
			'suer' => '速尔物流',
			'shengfengwuliu' => '盛丰物流',
			'shangda' => '上大物流',
			'santaisudi' => '三态速递',
			'haihongwangsong' => '山东海红',
			'saiaodi' => '赛澳递',
			'haihongwangsong' => '山东海红',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限）
			'sxhongmajia' => '山西红马甲',//（暂只能查天天网的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限)
			'shenganwuliu' => '圣安物流',
			'suijiawuliu' => '穗佳物流',

			'tiandihuayu' => '天地华宇',
			'tiantian' => '天天快递',
			'tnt' => 'TNT（中文结果）',
			'tnten' => 'TNT（英文结果）',
			'tonghetianxia' => '通和天下',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限）

			'ups' => 'UPS（中文结果）',
			'upsen' => 'UPS（英文结果）',
			'youshuwuliu' => '优速物流',
			'usps' => 'USPS（中英文）',

			'wanjiawuliu' => '万家物流',
			'wxwl' => '万象物流',
			'weitepai' => '微特派',//（暂只能查天天网的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限)

			'xinbangwuliu' => '新邦物流',
			'xinfengwuliu' => '信丰物流',
			'xingchengjibian' => '星晨急便',//（暂不支持，请转用HtmlAPI,要无验证码查询请联系企业QQ4008800898）
			'xinhongyukuaidi' => '鑫飞鸿',
			'cces' => '希伊艾斯(CCES)',
			'xinbangwuliu' => '新邦物流',
			'neweggozzo' => '新蛋奥硕物流',
			'hkpost' => '香港邮政',

			'yuantong' => '圆通速递',
			'yunda' => '韵达快运',
			'yuntongkuaidi' => '运通快递',
			'youzhengguonei' => '邮政国内给据',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'youzhengguoji' => '邮政国际',//（暂只支持HTML格式，使用方法见HtmlAPI,要JSON、XML格式结果的请联系企业QQ4008800898）
			'yuanchengwuliu' => '远成物流',
			'yafengsudi' => '亚风速递',
			'yibangwuliu' => '一邦速递',
			'youshuwuliu' => '优速物流',
			'yuanweifeng' => '源伟丰快递',
			'yuanzhijiecheng' => '元智捷诚',
			'yuefengwuliu' => '越丰物流',
			'yuananda' => '源安达',
			'yuanfeihangwuliu' => '原飞航',
			'zhongxinda' => '忠信达快递',
			'zhimakaimen' => '芝麻开门',
			'yinjiesudi' => '银捷速递',
			'yitongfeihong' => '一统飞鸿',//（暂只能查天天网的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限)

			'zhongtong' => '中通速递',
			'zhaijisong' => '宅急送',
			'ztky' => '中铁物流',//（特指：http://www.kuaidi100.com/all/ztwl.shtml ）
			'zhongtiewuliu' => '中铁快运',//（特指：http://www.kuaidi100.com/all/ztky.shtml ）
			'zhongyouwuliu' => '中邮物流',
			'zhongxinda' => '忠信达',
			'zhongsukuaidi' => '中速快件',
			'zhimakaimen' => '芝麻开门',
			'zhengzhoujianhua' => '郑州建华',//（暂只能查好乐买的单，其他商家要查，请发邮件至 wensheng_chen#kingdee.com(将#替换成@)开通权限）
			'zhongtianwanyun' => '中天万运',
		);
        $rtnString = "";
        for($i=0;$i<count($matches[1]);$i++) {
            $is_matched = false;	# 是否匹配到查询物流的语句
            $is_valid = false;		# 是否有查询成功的
            foreach( $expresses as $key=>$exp ) {
                if( strpos( $exp, $matches[1][$i] ) !== false ) {
                    $exprid = $matches[2][$i];
                    // $responseHTML = $me->get_var_curl("http://baidu.kuaidi100.com/query?type=$key&postid=$exprid&id=4&valicode=&temp=0.7806499029975384&tmp=0.04940579901449382",$cookie='inputpostid=EY166724682CS; comcode=ems',$Ref='');
                    $responseHTML = $me->get_var_curl("http://www.kuaidi100.com/query?type={$key}&postid={$exprid}&id=11&valicode=&temp=0.5595061660278589&sessionid=&tmp=0.6303061710204929",$cookie='bdshare_firstime=1360754065620; indexCompanyCode=$key',$Ref='http://www.kuaidi100.com/frame/index.htm');
                    
                    $Message = json_decode( $responseHTML, true );
                    if( !is_array($Message) ) {	// 不标准Json标准化（单引号包裹字符串的）
                        $responseHTML = preg_replace("/\"/u","\\\"",$responseHTML);
                        $responseHTML = preg_replace("/(?!\\\\)'/u","\"",$responseHTML);
                        $Message = json_decode( $responseHTML, true );
                    }
                    if( is_array($Message) && array_key_exists('status',$Message) ) {
                        $is_matched = true;
                        $rtnString .= $exprid . "($exp): \n";
                        if( $Message['status'] == '200' ) {
                            $is_valid = true;
                            foreach( $Message['data'] as $dkey=>$dexp ) {
                                $rtnString .= $Message['data'][$dkey]['time'].":\n".$Message['data'][$dkey]['context']."\n";
                            }
                        } else {
                            $rtnString .= $Message['message'] . "\n";
                        }
                        $rtnString .= "\n";
                    }
                }
            }
            if( $is_valid ) { 
                return array(
                    'type'=>'text',
                    'data'=>array(
                        'type'=>'express',
                        'content'=>$rtnString
                    )
                ); 
            } else {
                return array(
                    'type'=>'news',
                    'data'=>array(
                        'type'       => 'express',
                        'content'    => $rtnString,
                        'url'        => TMS_BOT_BASE_URL . 'data/redirect/query_express.php',
                        'title'      => '物流快递查询',
                        'pic_url'    => '',
                        'description'=> '单号不存在 点击进入人工查询',
                    )
                ); 
            }
        }
        #'未匹配到快递';
        return null;
    }
    public static function filter_mobile($expr,$me) {
        preg_match_all("/(?:\D|^)(1\d{10})(?:(?!\d)|$)/u", $expr, $matches);
        if( count($matches[1])>0 ) return $matches; else return null;
    }
    public static function query_mobile($matches,$me) {
        if( count($matches[1])>0 ) {
            $rtnString = '';
            for($i=0;$i<count($matches[1]);$i++) {
                $mobno = $matches[1][$i];
                $responseHTML = $me->get_var_curl("http://api.showji.com/Locating/www.showji.co.m.aspx?m={$mobno}&output=json&callback=",$cookie='__utma=162563454.137133608.1360768495.1360768495.1360768495.1; __utmb=162563454.3.10.1360768495; __utmc=162563454; __utmz=162563454.1360768495.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)',$Ref='');
                $Message = json_decode( $responseHTML, true );
                if( !is_array($Message) ) {	// 不标准Json标准化（单引号包裹字符串的）
                    $responseHTML = preg_replace("/\"/u","\\\"",$responseHTML);
                    $responseHTML = preg_replace("/(?!\\\\)'/u","\"",$responseHTML);
                    $Message = json_decode( $responseHTML, true );
                }
                if( is_array($Message) && array_key_exists('QueryResult',$Message) ) {
                    if( $Message['QueryResult'] == 'True' ) {//{"Mobile":"13083277390","QueryResult":"True","Province":"安徽","City":"蚌埠","AreaCode":"0552","PostCode":"233000","Corp":"中国联通","Card":"GSM"}
                    $rtnString .= "所查号码：". $Message['Mobile']
                                ."\n归属省份：". $Message['Province']
                                ."\n归属城市：". $Message['City']
                                ."\n城市区号：". $Message['AreaCode']
                                ."\n城市邮编：". $Message['PostCode']
                                ."\n卡 类 型：". $Message['Corp']. $Message['Card']
                                ."\n";
                    } else {
                        $rtnString .= $Message['Mobile'] . "我也不知是个啥。\n";
                    }
                    $rtnString .= "\n";
                }
            }
            if(!empty($rtnString)) return array(
                'type'=>'text',
                'data'=>array(
                    'type'=>'mobile',
                    'content'=>$rtnString
                )
            ); 
        }
        #'未匹配到手机号码';
        return null;
    }
    public static function filter_translate($expr,$me) {
        if ( strpos($expr,'@')!==0 ) return null;
        preg_match_all("/^@([\x{4e00}-\x{9fa5}]+)\\s+(.*)/u", $expr, $matches);
        if( count($matches[1])>0 ) { return $matches; }
    }
	public static function query_translate($matches,$me) {
		$languages = array(
			'sq' => '阿尔巴尼亚语', 
			'ar' => '阿拉伯语', 
			'az' => '阿塞拜疆语', 
			'ga' => '爱尔兰语', 
			'et' => '爱沙尼亚语', 
			'be' => '白俄罗斯语', 
			'bg' => '保加利亚语', 
			'is' => '冰岛语', 
			'pl' => '波兰语', 
			'fa' => '波斯语', 
			'af' => '布尔语(南非荷兰语)', 
			'da' => '丹麦语', 
			'de' => '德语', 
			'ru' => '俄语', 
			'fr' => '法语', 
			'tl' => '菲律宾语', 
			'fi' => '芬兰语', 
			'ka' => '格鲁吉亚语', 
			'gu' => '古吉拉特语', 
			'ht' => '海地克里奥尔语', 
			'ko' => '韩语', 
			'nl' => '荷兰语', 
			'gl' => '加利西亚语', 
			'ca' => '加泰罗尼亚语', 
			'cs' => '捷克语', 
			'hr' => '克罗地亚语', 
			'la' => '拉丁语', 
			'lv' => '拉脱维亚语', 
			'lt' => '立陶宛语', 
			'ro' => '罗马尼亚语', 
			'mt' => '马耳他语', 
			'ms' => '马来语', 
			'mk' => '马其顿语', 
			'bn' => '孟加拉语', 
			'no' => '挪威语', 
			'pt' => '葡萄牙语', 
			'ja' => '日语', 
			'sv' => '瑞典语', 
			'sr' => '塞尔维亚语', 
			'eo' => '世界语', 
			'sk' => '斯洛伐克语', 
			'sl' => '斯洛文尼亚语', 
			'sw' => '斯瓦希里语', 
			'th' => '泰语', 
			'tr' => '土耳其语', 
			'cy' => '威尔士语', 
			'uk' => '乌克兰语', 
			'iw' => '希伯来语', 
			'el' => '希腊语', 
			'eu' => '西班牙的巴斯克语', 
			'es' => '西班牙语', 
			'hu' => '匈牙利语', 
			'hy' => '亚美尼亚语', 
			'it' => '意大利语', 
			'yi' => '意第绪语', 
			'hi' => '印地语', 
			'kn' => '印度的卡纳达语', 
			'te' => '印度的泰卢固语', 
			'ta' => '印度的泰米尔语', 
			'ur' => '印度乌尔都语', 
			'id' => '印尼语', 
			'en' => '英语', 
			'vi' => '越南语', 
			'zh-CN' => '汉语/中文(简体)', 
			'zh-TW' => '汉语/中文(繁体)', 
		);
        for($i=0;$i<count($matches[1]);$i++) {
            foreach( $languages as $tl=>$tlCN ) {
                if( strpos($tlCN,$matches[1][$i])!==false ) {
                    #http://translate.google.cn/translate_a/t?client=t&text=".urlencode($expr)."&hl=zh-CN&sl=zh-CN&tl=en&ie=UTF-8&oe=UTF-8&multires=1&prev=conf&psl=en&ptl=en&otf=1&it=sel.17684&ssel=6&tsel=3&sc=1
                    $expr = $matches[2][$i];
                    $rtnString = $me->get_var_curl("http://translate.google.cn/translate_a/t?client=t&text=".urlencode($expr)."&sl=auto&tl=$tl&ie=UTF-8&oe=UTF-8",'NID=64=idr3PUI4SnehIiuk6q7S72l594VnnfIeSzaXJUCvrh381-qrXy624JKgFxmeqEVjZRJ6XW-QDKmaMNYsrRCLWU2qdI5956PNeMLaqTU2y24zRXbtwOgHW21B1c7DUZwe; PREF=ID=62979bbf4df9088a:U=d8a692627d055573:NW=1:TM=1355749651:LM=1355763670:S=8XxGt9PbZWJ3XCJv',$Ref='');
                    $rtnString = preg_replace('/,(?=,)/',',[]',$rtnString);
                    $Message = json_decode( $rtnString, true );
                    if( is_array($Message) && is_array($Message[0]) && is_array($Message[0][0]) ) {
                        @$rtnString = $Message[0][0][1]."\n".$Message[0][0][3]."\n".$Message[0][0][0]."\n".$Message[0][0][2]."\n";
                        return array(
                            'type'=>'text',
                            'data'=>array(
                                'type'=>'translate',
                                'content'=>$rtnString
                            )
                        ); 
                    }
                }
            }
        }
        return null;
    }
    public static function filter_weather($expr,$me) {
        preg_match_all("/([\x{4e00}-\x{9fa5}]{2,7}?(?=.*[\\s|的]*天气))/u", $expr, $matches);
        if( count($matches[1])>0 ) { 
            $expr = array();
            foreach( $matches[1] as $key=>$city_name ) {
                if( strlen($city_name)>strlen('广德') ) {
                    $city_name = str_replace('省','',$city_name);	# 替换省
                    $city_name = str_replace('市','',$city_name);	# 替换市
                    $city_name = str_replace('县','',$city_name);	# 替换县
                    $city_name = str_replace('镇','',$city_name);	# 替换镇
                    $city_name = str_replace('乡','',$city_name);	# 替换乡
                }
                $_city = $me->db->get_all('SELECT * FROM '.TMS_DB_TABLE_PREFIX.'weather_code WHERE city_name LIKE ?',array($me->iconv(TMS_DB_CHARSET,"%{$city_name}%",'UTF-8')));
                if($_city) {	# 数据库记录已存在
                    foreach( $_city as $key=>$exp ) {
                        $city_id      = $me->iconv('UTF-8',$_city[$key]['city_id'],  TMS_DB_CHARSET);	# 蛋疼的Access
                        $city_name    = $me->iconv('UTF-8',$_city[$key]['city_name'],TMS_DB_CHARSET);	# 蛋疼的Access
                        $expr []= array(
                            'city_id'=>$city_id,
                            'city_name'=>$city_name,
                        );
                    }
                }
            }
            return $expr;
        }
        return null;
    }
    public static function query_weather($matches,$me) {
		# 根据ID获取气象信息。
		$rtnString = '';
        foreach( $matches as $match ) {
            $city_id = $match['city_id'];
            $city_name = $match['city_name'];
            $responseHTML = $me->get_var_curl("http://m.weather.com.cn/data/{$city_id}.html");
            $Message = json_decode($responseHTML,true);
            if( is_array($Message['weatherinfo']) ) {
                $rtnString .= sprintf("%s天气情况：\n今天：%s %s %s %s %s\n明天：%s %s\n后天：%s %s\n", $city_name, $Message['weatherinfo']['temp1'], $Message['weatherinfo']['weather1'], $Message['weatherinfo']['wind1'], $Message['weatherinfo']['fl1'], $Message['weatherinfo']['index_d'], $Message['weatherinfo']['temp2'], $Message['weatherinfo']['weather2'], $Message['weatherinfo']['temp3'], $Message['weatherinfo']['weather3']);
            } else {
                $rtnString .= "{$city_name}天气信息查询失败。\n";
            }
        }
        return array(
            'type'=>'text',
            'data'=>array(
                'type'=>'weather',
                'content'=>$rtnString
            )
        );
	}

}
?>