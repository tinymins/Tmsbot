<?php
/* 

运算符表：
------------------------------
| 优先级 | 结合性 |  运算符  |
------------------------------
|   4    |   R    | ^        |
|   3    |   N    | - +(一元)|
|   2    |   L    | * / %    |
|   1    |   L    | + -      |
------------------------------

文法：

A -> BA'
A'-> +BA'| -BA'| e

B -> CB'
B'-> *CB'| /CB'| %CB'| CB'| e

C -> +C | -C | D

D -> X^D | X^+C | X^-C | X

F -> A,F | A

X -> (A) | digit | fun(F) | fun()

fun -> /^[a-zA-Z_][0-9a-zA-Z_]*$/
digit -> /^(([0-9]+[\.]?[0-9]*)|([0-9]*[\.]?[0-9]+))$/

*/

function get_token_array($string)
{
	/* 构造记号流*/
	$token = array();
	$pos = array();
	$str_len = 0;
	while (true)
	{
		if (1 === preg_match('/^([%,\\^\\+\\-\\*\\/\\(\\)]).*$/',$string,$sub))
		{
			array_push($token,$sub[1]);
			array_push($pos,$str_len);
			$string = substr($string,strlen($sub[1]));
			$str_len += strlen($sub[1]);
			continue;
		}
		elseif (1 === preg_match('/^(([0-9]+[\\.]?[0-9]*)|([0-9]*[\\.]?[0-9]+)).*$/',$string,$sub))
		{
			array_push($token,floatval($sub[1]));
			array_push($pos,$str_len);
			$string = substr($string,strlen($sub[1]));
			$str_len += strlen($sub[1]);
			continue;
		}
		elseif (1 === preg_match('/^([a-zA-Z_][0-9a-zA-Z_]*\\().*$/',$string,$sub))
		{
			array_push($token,$sub[1]);
			array_push($pos,$str_len);
			$string = substr($string,strlen($sub[1]));
			$str_len += strlen($sub[1]);
			continue;
		}
		elseif (1 === preg_match('/^(\\s+).*$/',$string,$sub))
		{
			$string = substr($string,strlen($sub[1]));
			$str_len += strlen($sub[1]);
			continue;
		}
		else 
		{
			break;
		}
	}
	array_push($pos,$str_len);
	if ($string != '') 
	{
		return $str_len;
	}
	return array($token,$pos);
}

function current_token()
{
	global $g_count,$g_token;
	return $g_token[$g_count];
}

function next_token()
{
	global $g_count,$g_token;
	if (isset($g_token[$g_count+1]))
	{
		$g_count++;
		return $g_token[$g_count];
	}
	else 
	{
		return false;
	}
}

function error_pos()
{
	global $g_count,$g_pos;
	return $g_pos[$g_count];
}

function eval_expr_to_rpn($string)
{
	global $g_count,$g_token,$g_pos,$g_error;
	$g_error = null;
	$g_count = 0;
	$info = get_token_array($string);
	if (!is_array($info))
	{
		error(25,$info);
	}
	else 
	{
		$g_token = $info[0];
		$g_pos = $info[1];
//		print_r($info);
		array_push($g_token,'End');
		$ans = Fun_A();
		if ($g_count != sizeof($g_token)-1)
		{
			error(24,error_pos());
		}
		return $ans;
	}
}

function Fun_A()
{
	$ans_1 = Fun_B();
	$ans_2 = Fun_A_();
	if ($ans_1 === false or $ans_2 === false) 
	{
		error(1,error_pos());
		return false;
	}
	if ($ans_2 === '')
	{
		return $ans_1;
	}
	else 
	{
		return $ans_1.' '.$ans_2;
	}
}

function Fun_A_()
{
	if ('+' === current_token() or '-' === current_token())
	{
		$op = current_token();
		if ($op === '+')
		{
			$op = 'ADD';
		}
		elseif ($op === '-')
		{
			$op = 'SUB';
		}
		if (false === next_token())
		{
			error(2,error_pos());
			return false;
		}
		else 
		{
			$ans_1 = Fun_B();
			$ans_2 = Fun_A_();
			if ($ans_1 === false or $ans_2 === false) 
			{
				error(3,error_pos());
				return false;
			}
			if ($ans_2 === '')
			{
				return $ans_1.' '.$op;
			}
			else 
			{
				return $ans_1.' '.$op.' '.$ans_2;
			}
		}
	}
	else 
	{
		return '';
	}
}

function Fun_B()
{
	$ans_1 = Fun_C();
	$ans_2 = Fun_B_();
	if ($ans_1 === false or $ans_2 === false) 
	{
		error(4,error_pos());
		return false;
	}
	if ($ans_2 === '')
	{
		return $ans_1;
	}
	else 
	{
		return $ans_1.' '.$ans_2;
	}
}

function Fun_B_()
{
	if ('*' === current_token() or '/' === current_token() or '%' === current_token())
	{
		$op = current_token();
		if ($op === '*')
		{
			$op = 'MUL';
		}
		elseif ($op === '/')
		{
			$op = 'DIV';
		}
		elseif ($op === '%')
		{
			$op = 'MOD';
		}
		if (false === next_token())
		{
			error(5,error_pos());
			return false;
		}
		else 
		{
			$ans_1 = Fun_C();
			$ans_2 = Fun_B_();
			if ($ans_1 === false or $ans_2 === false) 
			{
				error(6,error_pos());
				return false;
			}
			if ($ans_2 === '')
			{
				return $ans_1.' '.$op;
			}
			else 
			{
				return $ans_1.' '.$op.' '.$ans_2;
			}
		}
	}
	elseif ( 1 === preg_match('/^[\\+\\-]?(([0-9]+[\\.]?[0-9]*)|([0-9]*[\\.]?[0-9]+))([eE][+-]?[0-9]+)?$/',current_token()) 
			or '(' === current_token()
			or 1 === preg_match('/^([a-zA-Z_][0-9a-zA-Z_]*\\()$/',current_token())
			)
	{
		$ans_1 = Fun_C();
		$ans_2 = Fun_B_();
		if ($ans_1 === false or $ans_2 === false)
		{
			error(30,error_pos());
			return false;
		}
		if ($ans_2 === '')
		{
			return $ans_1.' '.'MUL';
		}
		else
		{
			return $ans_1.' '.'MUL'.' '.$ans_2;
		}
	}
	else 
	{			
		return '';
	}
}

function Fun_C()
{
	if ('-' === current_token() or '+' === current_token())
	{
		if ('-' === current_token())
		{
			$op = ' NEC';
		}
		if ('+' === current_token())
		{
			$op = '';
		}
		if (false === next_token())
		{
			error(7,error_pos());
			return false;
		}
		$ans = Fun_C();
		if ($ans === false)
		{
			error(8,error_pos());
			return false;
		}
		else
		{
			return $ans.$op;
		}
	}
	else 
	{
		$ans = Fun_D();
		if ($ans === false)
		{
			error(9,error_pos());
			return false;
		}
		else
		{
			return $ans;
		}
	}
}

function Fun_D()
{
	$ans_1 = Fun_X();
	if ($ans_1 === false)
	{
		error(10,error_pos());
		return false;
	}
	if ('^' === current_token())
	{
		if (false === next_token())
		{
			error(11,error_pos());
			return false;
		}
		if ('-' === current_token() or '+' === current_token())
		{
			$ans_2 = Fun_C();
			if ($ans_2 === false)
			{
				error(12,error_pos());
				return false;
			}
			return $ans_1.' '.$ans_2.' '.'POW';
		}
		else 
		{
			$ans_2 = Fun_D();
			if ($ans_2 === false)
			{
				error(13,error_pos());
				return false;
			}
			return $ans_1.' '.$ans_2.' '.'POW';
		}
	}
	else 
	{
		return $ans_1;
	}
}

function Fun_F()
{
	$ans_1 = Fun_A();
	if ($ans_1 === false)
	{
		error(14,error_pos());
		return false;
	}
	if (',' === current_token())
	{
		if (false === next_token())
		{
			error(15,error_pos());
			return false;
		}
		$ans_2 = Fun_F();
		if ($ans_2 === false)
		{
			error(16,error_pos());
			return false;
		}
		return $ans_1.' '.$ans_2;
	}
	else 
	{
		return $ans_1;
	}
}

function Fun_X()
{
	if ( '(' === current_token() ) 
	{
		if (false === next_token())
		{
			error(17,error_pos());
			return false;
		}
		$ans = Fun_A();
		if ($ans === false)
		{
			error(18,error_pos());
			return false;
		}
		if ( ')' != current_token() )
		{
			error(19,error_pos());
			return false;
		}
		next_token();
		return $ans;
	}
	elseif ( 1 === preg_match('/^[\\+\\-]?(([0-9]+[\\.]?[0-9]*)|([0-9]*[\\.]?[0-9]+))([eE][+-]?[0-9]+)?$/',current_token()) )
	{
		$ans = current_token();
		next_token();
		return $ans;
	}
	elseif ( 1 === preg_match('/^([a-zA-Z_][0-9a-zA-Z_]*\\()$/',current_token()) )
	{
		$fun_name = substr(current_token(),0,-1);
		if (false === next_token())
		{
			error(20,error_pos());
			return false;
		}
		if (')' === current_token()) 
		{
			if (false === next_token())
			{
				error(26,error_pos());
				return false;
			}
			return '()'.$fun_name;
		}
		else 
		{
			$ans = Fun_F();
			if ($ans === false)
			{
				error(21,error_pos());
				return false;
			}
			if ( ')' != current_token() )
			{
				error(22,error_pos());
				return false;
			}
			next_token();
			return '( '.$ans.' )'.$fun_name;
		}
	}
	else 
	{
		error(23,error_pos());
		return false;
	}
}

function error($info,$pos)
{
	global $g_error;
	if ($g_error === null)
	{
		$g_error = array($info,$pos);
	}
}

function get_error_info()
{
	global $g_error;
	$info_array = array
	(
		19 => '缺少右括号',
		22 => '缺少右括号',
		23 => '语法错误',
		24 => '语法错误',
		25 => '输入错误'
	);
	if (array_key_exists($g_error[0],$info_array))
	{
		return $info_array[$g_error[0]];
	}
	else 
	{
		if ($g_error === null)
		{
			return null;
		}
		else 
		{
			return "错误：#{$g_error[0]}";
		}
	}
}
function get_error_pos()
{
	global $g_error;
	if ($g_error === null) 
	{
		return null;
	}
	else 
	{
		return $g_error[1];
	}
}
//$rpn = eval_expr_to_rpn('345sin(45656 123)');
//echo $rpn;
//echo "\n";
//$info = get_error_info();
//$pos = get_error_pos();
//var_dump($info);
//var_dump($pos);
//echo 214748364743541;
?>