<?php
function my_abs($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('abs 函数 参数个数错误');
		return false;
	}
	else 
	{
		return abs($array[0]);
	}
}
function my_exp($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('exp 函数 参数个数错误');
		return false;
	}
	else 
	{
		return exp($array[0]);
	}
}
function my_sin($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('sin 函数 参数个数错误');
		return false;
	}
	else 
	{
		return sin($array[0]);
	}
}
function my_cos($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('cos 函数 参数个数错误');
		return false;
	}
	else 
	{
		return cos($array[0]);
	}
}
function my_asin($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('asin 函数 参数个数错误');
		return false;
	}
	else 
	{
		return asin($array[0]);
	}
}
function my_acos($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('acos 函数 参数个数错误');
		return false;
	}
	else 
	{
		return acos($array[0]);
	}
}
function my_tan($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('tan 函数 参数个数错误');
		return false;
	}
	else 
	{
		return tan($array[0]);
	}
}
function my_atan($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('atan 函数 参数个数错误');
		return false;
	}
	else 
	{
		return atan($array[0]);
	}
}
function my_ceil($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('ceil 函数 参数个数错误');
		return false;
	}
	else 
	{
		return ceil($array[0]);
	}
}
function my_floor($array)
{
	if (sizeof($array) !== 1) 
	{
		error_rpn('floor 函数 参数个数错误');
		return false;
	}
	else 
	{
		return floor($array[0]);
	}
}
function my_round($array)
{
	if (sizeof($array) === 1) 
	{
		return round($array[0]);
	}
	elseif (sizeof($array) === 2)
	{
		return round($array[0],$array[1]);
	}
	else 
	{
		error_rpn('round 函数 参数个数错误');
		return false;
	}
}
function my_pi()
{
	return pi();
}
function my_sqrt($array)
{
	if (sizeof($array) === 1) 
	{
		return sqrt($array[0]);
	}
	else 
	{
		error_rpn('sqrt 函数 参数个数错误');
		return false;
	}
}
function my_log($array)
{
	if (sizeof($array) === 1) 
	{
		return log($array[0]);
	}
	elseif (sizeof($array) === 2) 
	{
		return log($array[0],$array[1]);
	}
	else 
	{
		error_rpn('log 函数 参数个数错误');
		return false;
	}
}

function my_ln($array)
{
	if (sizeof($array) === 1) 
	{
		return log($array[0]);
	}
	else 
	{
		error_rpn('ln 函数 参数个数错误');
		return false;
	}
}
?>