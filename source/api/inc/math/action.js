var map = Array();
map[0] = '';
map[1] = '7';
map[2] = '4';
map[3] = '1';
map[4] = '0';

map[5] = '8';
map[6] = '5';
map[7] = '2';
map[8] = '.';

map[9] = '9';
map[10] = '6';
map[11] = '3';
map[12] = '-';

map[13] = '/';
map[14] = '*';
map[15] = '-';
map[16] = '+';

map[17] = '^';
map[18] = '%';
map[19] = 'sqrt(';
map[20] = '(';

map[21] = 'sin(';
map[22] = 'cos(';
map[23] = 'tan(';
map[24] = ')';

map[25] = 'asin(';
map[26] = 'acos(';
map[27] = 'atan(';
map[28] = ',';

map[29] = 'log(';
map[30] = 'ln(';
map[31] = 'exp(';
map[32] = 'pi()';

map[33] = 'abs(';
map[34] = 'ceil(';
map[35] = 'floor(';
map[36] = 'round(';

//download by http://www.codefans.net
function CreateMouseEvent()
{
	if (!document.getElementsByTagName)
		return;
	var anchors = document.getElementsByTagName("li");
	for (var i=0; i<anchors.length; i++)
	{
		var anchor = anchors[i];
		var id = anchor.id.toString();
		var rslt = id.match(/^key_(\d+)$/);
		if(rslt != null)
		{
			anchor.onmouseover = mouseover;
			anchor.onmouseout = mouseout;
			anchor.onclick = mouseclick;
			anchor.ondblclick = mouseclick;
		}
	}
	U = document.getElementById('userinput');
	U.onkeydown = InputSubmit;
}
function mouseover(evt)
{
	if(evt == null)
	{
		evt = window.event;
	}
	var X = evt.srcElement?evt.srcElement:evt.target;
	X.style.backgroundColor = '#D1DCEB';
}
function mouseout(evt)
{
	if(evt == null)
	{
		evt = window.event;
	}
	var X = evt.srcElement?evt.srcElement:evt.target;
	X.style.backgroundColor = '#F0F5FA';
}
function mouseclick(evt)
{
	if(evt == null)
	{
		evt = window.event;
	}
	var X = evt.srcElement?evt.srcElement:evt.target;
	var I = X.id;
	var M = I.match(/^(\D*)(\d+)$/);
	var P = document.getElementById('userinput');
	if(M[2] >= 1 && M[2] <= 36)
	{
		P.value = P.value + map[M[2]];
	}
	else if(M[2] == 37)
	{
		P.value = P.value.substring(0,P.value.length-1);
	}
	else if(M[2] == 38)
	{
		P.value = '';
	}
	else if(M[2] == 39)
	{
		if(P.value != '')
		{
			document.getElementById('exprinput').value = P.value;
			document.getElementById('exprform').submit();
		}
	}
	else
	{
		alert('error!');
	}
}
function InputSubmit(evt)
{
	if(evt == null)
	{
		evt = window.event;
	}
	if(evt.keyCode == 13)
	{
		var P = document.getElementById('userinput');
		if(P.value != '')
		{
			document.getElementById('exprinput').value = P.value;
			document.getElementById('exprform').submit();
		}
	}
}
function InputFocus(evt)
{
	var X = document.getElementById('userinput');
	var R = X.createTextRange();
	R.moveStart('character',0);
	R.moveEnd('character',X.value.length);
	R.select();
}

function PageLoad(evt)
{
	CreateMouseEvent();
	InputFocus();
}
window.onload = PageLoad;