<?php
// 确保magic_quotes_gpc为关闭状态
function stripslashesArray(&$array)
{
	is_array($array) ? array_walk($array,'stripslashesArray') : $array = stripslashes($array);
}
if(get_magic_quotes_gpc())
{
	stripslashesArray($_GET);
	stripslashesArray($_POST);
	stripslashesArray($_COOKIE);
	stripslashesArray($_FILES);
	stripslashesArray($_REQUEST);
}
ini_set('magic_quotes_runtime','0');
ini_set('magic_quotes_sybase','0');

//ini_set('error_reporting','0');
//ini_set('display_errors','0');

require_once('eval_expr_to_rpn.php');
require_once('eval_rpn.php');
require_once('eval_my_function.php');

function html_transform_with_space($string)
{
	$string = htmlentities($string,ENT_QUOTES,'GB2312');
	$string = preg_replace('/ /','&nbsp;',$string);
	return $string;
}
function html_transform_without_space($string)
{
	$string = htmlentities($string,ENT_QUOTES,'GB2312');
	return $string;
}
$expr = '';
$ans = '';
$ans_style = 'ans_normal';
if (array_key_exists('expr',$_GET)) 
{
	$expr = $_GET['expr'];
	$ans = eval_expr_to_rpn($expr);
	$error = get_error_info();
	if ($error === null) 
	{
		$ans = eval_rpn($ans);
		$error = get_error_rpn();
		if ($error !== null) 
		{
			$ans_style = 'ans_error';
			$ans = get_error_rpn();
		}
	}
	else 
	{
		$ans_style = 'ans_error';
		$ans = get_error_info();
	}
}
$re_userinput = html_transform_without_space($expr);
$expr = html_transform_with_space($expr);
$ans = html_transform_with_space($ans);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>通用初等函数计算器</title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="style.css" type="text/css" media="all" />
<script type="text/javascript" src="action.js"></script>
</head>
<body>
<div id="box">
	<div class="<?php echo $ans_style ?>">
		<div id="line">
			<div class="q"><?php echo $expr ?></div>
			<div class="a"><?php echo $ans ?></div>
		</div>
	</div>
	<div id ="keyboard">
		<div id="function">
			<ul class="number">
				<li id="key_1">7</li>
				<li id="key_2">4</li>
				<li id="key_3">1</li>
				<li id="key_4">0</li>
			</ul>
			<ul class="number">
				<li id="key_5">8</li>
				<li id="key_6">5</li>
				<li id="key_7">2</li>
				<li id="key_8">.</li>
			</ul>
			<ul class="number">
				<li id="key_9">9</li>
				<li id="key_10">6</li>
				<li id="key_11">3</li>
				<li id="key_12">( - )</li>
			</ul>
			<ul class="fun">
				<li id="key_13">/</li>
				<li id="key_14">*</li>
				<li id="key_15">-</li>
				<li id="key_16">+</li>
			</ul>
			<ul class="fun">
				<li id="key_17">^</li>
				<li id="key_18">%</li>
				<li id="key_19">&radic;(</li>
				<li id="key_20">(</li>
			</ul>
			<ul class="fun">
				<li id="key_21">sin(</li>
				<li id="key_22">cos(</li>
				<li id="key_23">tan(</li>
				<li id="key_24">)</li>
			</ul>
			<ul class="fun">
				<li id="key_25">asin(</li>
				<li id="key_26">acos(</li>
				<li id="key_27">atan(</li>
				<li id="key_28">,</li>
			</ul>
			<ul class="fun">
				<li id="key_29">log(</li>
				<li id="key_30">ln(</li>
				<li id="key_31">exp(</li>
				<li id="key_32">pi()</li>
			</ul>
			<ul class="fun">
				<li id="key_33">abs(</li>
				<li id="key_34">ceil(</li>
				<li id="key_35">floor(</li>
				<li id="key_36">round(</li>
			</ul>
			<ul class="op">
				<li id="key_37">&larr;</li>
				<li id="key_38">CLEAR</li>
				<li id="key_39" style="height:37px;line-height:37px">ENTER</li>
			</ul>
		</div>
	</div>
	<div id="expression">
		<input name="userinput" type="text" id="userinput" value="<?php echo $re_userinput ?>" maxlength="128" />
	</div>
</div>
<form id="exprform" action="index.php" method="get"><input name="expr" id='exprinput' type="hidden" value="" /></form>
<!--
   ┏━━━━━━━━━━━━━━━━━━━━━┓
   ┃             源 码 爱 好 者               ┃
   ┣━━━━━━━━━━━━━━━━━━━━━┫
   ┃                                          ┃
   ┃           提供源码发布与下载             ┃
   ┃                                          ┃
   ┃        http://www.codefans.net           ┃
   ┃                                          ┃
   ┃            互助、分享、提高              ┃
   ┗━━━━━━━━━━━━━━━━━━━━━┛
-->
</body>
</html>
