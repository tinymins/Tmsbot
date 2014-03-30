<?php
require_once('eval_my_function.php');

function kernel_nec($array)
{
	return 0 - $array[0];
}

function kernel_add($array)
{
	return $array[0] + $array[1];
}

function kernel_sub($array)
{
	return $array[0] - $array[1];
}

function kernel_mul($array)
{
	return $array[0] * $array[1];
}

function kernel_mod($array)
{
	return $array[0] % $array[1];
}

function kernel_div($array)
{
	if ($array[1] == 0) 
	{
		error_rpn('除零错误');
		return false;
	}
	else 
	{
		return $array[0] / $array[1];
	}
}

function kernel_pow($array)
{
	$temp = pow($array[0],$array[1]);
	if ($temp === false) 
	{
		error_rpn('指数计算错误');
		return false;
	}
	else 
	{
		return $temp;
	}
}

function error_rpn($info)
{
	global $g_error_rpn;
	if ($g_error_rpn === null) 
	{
		$g_error_rpn = $info;
	}
}
function get_error_rpn()
{
	global $g_error_rpn;
	return $g_error_rpn;
}
function eval_rpn($rpn_string)
{
	global $g_error_rpn;
	$g_error_rpn = null;
	$token = explode(' ',$rpn_string);
	$stack = array();
	while (true)
	{
		$temp = array_shift($token);
		if ('(' === $temp) 
		{
			array_push($stack,$temp);
		}
		elseif (1 === preg_match('/^[\\+\\-]?(([0-9]+[\\.]?[0-9]*)|([0-9]*[\\.]?[0-9]+))([eE][+-]?[0-9]+)?$/',$temp)) 
		{
			array_push($stack,$temp);
		}
		elseif (1 === preg_match('/^(ADD)|(SUB)|(MUL)|(DIV)|(MOD)|(POW)|(NEC)$/',$temp))
		{
			$param = array();
			if ($temp === 'NEC') 
			{
				$param_1 = array_pop($stack);
				array_push($param,$param_1);
				$ans = kernel_nec($param);
				if ($ans === false) 
				{
					return false;
				}
				else 
				{
					array_push($stack,$ans);
				}
			}
			else 
			{
				$param_1 = array_pop($stack);
				$param_2 = array_pop($stack);
				array_push($param,$param_2,$param_1);
				$ans = call_user_func('kernel_'.strtolower($temp),$param);
				if ($ans === false) 
				{
					return false;
				}
				else 
				{
					array_push($stack,$ans);
				}
			}
		}
		elseif (1 === preg_match('/^(\\))[a-zA-Z_][0-9a-zA-Z_]*$/',$temp))
		{
			$fun_name = substr($temp,1);
			if (!function_exists('my_'.strtolower($fun_name)))
			{
				error_rpn("{$fun_name} 函数 未定义");
				return false;
			}
			$param = array();
			$temp_param = array_pop($stack);
			while ($temp_param !== '(') 
			{
				$param[] = $temp_param;
				$temp_param = array_pop($stack);
				if ($temp_param === null) 
				{
					error_rpn('RPN表达式错误');
					return false;
				}
			}
			$param = array_reverse($param);
			$ans = call_user_func('my_'.strtolower($fun_name),$param);
			if ($ans === false)
			{
				return false;
			}
			else
			{
				array_push($stack,$ans);
			}
		}
		elseif (1 === preg_match('/^(\\(\\))[a-zA-Z_][0-9a-zA-Z_]*$/',$temp))
		{
			$fun_name = substr($temp,2);
			if (!function_exists('my_'.strtolower($fun_name)))
			{
				error_rpn("{$fun_name} 函数 未定义");
				return false;
			}
			$ans = call_user_func('my_'.strtolower($fun_name),null);
			if ($ans === false)
			{
				return false;
			}
			else
			{
				array_push($stack,$ans);
			}
		}
		elseif ($temp === null)
		{
			break;
		}
		else 
		{
			error_rpn('RPN表达式错误1');
			return false;
		}
	}
	if (sizeof($token) !== 0 or sizeof($stack) !== 1)
	{
		error_rpn('RPN表达式错误2');
		return false;
	}
	else 
	{
		return array_pop($stack);
	}
}

//$a = eval_rpn('3 ( 3 2 DIV 3.1415 ADD )sin MUL 3 2 POW ADD');
//var_dump($a);
//var_dump(get_error_rpn());
?>