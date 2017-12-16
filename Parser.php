<?php
/**
 * Created by PhpStorm.
 * User: denverb
 * Date: 17/12/3
 * Time: 上午8:42
 */

require_once ('scanner.php');
require_once ('func.php');

class exprNode		//节点类
{
	public $opCode, $content;

	public function __construct($token, $content = null)
	{
		$this->opCode = $token;
		$this->content = $content;
	}
}

class caseOperator		//二元运算
{
	public $left, $right;

	public function __construct(exprNode $left, exprNode $right)
	{
		$this->left = $left;
		$this->right = $right;
	}
}

class caseFunc 			//函数
{
	public $child, $name;

	public function __construct(exprNode $child, $name)
	{
		$this->child = $child;
		$this->name = $name;
	}
}

function throwException($message)			//错误处理
{
	throw new Exception($message);
}

function matchToken($token, $type)
{
	if ($token->type == $type) {
		return ;
	} else {
		throwException("非预期记号");
	}
}

function expression()
{
	global $ret;
	$left = term();
	while ($ret[0]->type == "PLUS" or $ret[0]->type == "MINUS") {
		$tmp = $ret[0]->type;
		matchToken(array_shift($ret), $tmp);
		$right = term();
		$left = new exprNode($tmp, new caseOperator($left, $right));
	}
	return $left;
}

function term()
{
	global $ret;
	$left = factor();
	while ($ret[0]->type == "MUL" or $ret[0]->type == "DIV") {
		$tmp = $ret[0]->type;
		matchToken(array_shift($ret), $tmp);
		$right = factor();
		$left = new exprNode($tmp, new caseOperator($left, $right));
	}
	return $left;
}

function factor()
{
	global $ret;
	if ($ret[0]->type == "PLUS") {
		matchToken(array_shift($ret), "PLUS");
		$right = factor();
	} elseif ($ret[0]->type == "MINUS") {
		matchToken(array_shift($ret), "MINUS");
		$right = factor();
		$left = new exprNode("CONST_ID", 0.0);
		$right = new exprNode("MINUS", new caseOperator($left, $right));
	} else {
		$right = component();
	}
	return $right;
}

function component()
{
	global $ret;
	$left = atom();
	if ($ret[0]->type == "POWER") {
		matchToken(array_shift($ret), "POWER");
		$right = component();
		$left = new exprNode("POWER", new caseOperator($left, $right));
	}
	return $left;
}

function atom()
{
	global $ret, $t;
	$tmp = $ret[0];
	switch ($ret[0]->type) {
		case 'CONST_ID':
			matchToken(array_shift($ret), 'CONST_ID');
			$address = new exprNode("CONST_ID", $tmp->value);
			break;
		case 'T':
			matchToken(array_shift($ret), 'T');
			$param = &$t;
			$address = new exprNode("T", $param);
			break;
		case 'FUNC':
			$name = $ret[0]->lexeme;
			matchToken(array_shift($ret), "FUNC");
			matchToken(array_shift($ret), "L_BRACKET");
			$exTmp = expression();
			$address = new exprNode("FUNC", new caseFunc($exTmp, $name));
			matchToken(array_shift($ret), "R_BRACKET");
			break;
		case 'L_BRACKET':
			matchToken(array_shift($ret), "L_BRACKET");
			$address = expression();
			matchToken(array_shift($ret), "R_BRACKET");
			break;
		default:
			throwException("非预期记号");

	}
	return $address;
}

function originStatement()
{
	global $ret, $originX, $originY;
	matchToken(array_shift($ret), 'IS');
	matchToken(array_shift($ret), 'L_BRACKET');
	$xTree = expression();
	$originX = getValue($xTree);
	matchToken(array_shift($ret), 'COMMA');
	$yTree = expression();
	$originY = getValue($yTree);

}

function scaleStatement()
{
	global $ret, $scaleX, $scaleY;
	matchToken(array_shift($ret), "IS");
	matchToken(array_shift($ret), "L_BRACKET");
	$xTree = expression();
	$scaleX = getValue($xTree);
	matchToken(array_shift($ret), "COMMA");
	$yTree = expression();
	$scaleY = getValue($yTree);
}

function rotStatement()
{
	global $ret, $rot;
	matchToken(array_shift($ret), "IS");
	$rot = expression();
}

function forStatement()
{
	global $ret, $drawX, $drawY, $startValue, $endValue, $stepValue;
	matchToken(array_shift($ret), "T");
	matchToken(array_shift($ret), "FROM");
	$start = expression();
	$startValue = getValue($start);
	matchToken(array_shift($ret), "TO");
	$end = expression();
	$endValue = getValue($end);
	matchToken(array_shift($ret), "STEP");
	$step = expression();
	$stepValue = getValue($step);
	matchToken(array_shift($ret), "DRAW");
	matchToken(array_shift($ret), "L_BRACKET");
	$drawX = expression();
	matchToken(array_shift($ret), "COMMA");
	$drawY = expression();
	matchToken(array_shift($ret), "R_BRACKET");	
	draw();
}

function getValue($root)
{
	global $t;
	switch ($root->opCode) {
		case 'PLUS':
			return getValue($root->content->left) + getValue($root->content->right);
			break;
		case 'MINUS':
			return getValue($root->content->left) - getValue($root->content->right);
			break;
		case 'MUL':
			return getValue($root->content->left) * getValue($root->content->right);
			break;
		case 'DIV':
			return getValue($root->content->left) / getValue($root->content->right);
			break;
		case 'POWER':
			return getValue($root->content->left) ** getValue($root->content->right);
			break;
		case 'CONST_ID':
			return $root->content;
			break;
		case 'T':
			return $t;
			break;
		case 'FUNC':
			return call_user_func('My'.$root->content->name, $t);
			break;
		default:
			# code...
			break;
	}
}

function parser()		//主函数
{
	global $ret;		//全局变量
	while (!empty($ret)) {
		$eachToken = array_shift($ret);		//出栈
		switch ($eachToken->type) {
			case 'ORIGIN':
				originStatement();
				break;
			case 'SCALE':
				scaleStatement();
				break;
			case 'ROT':
				rotStatement();
				break;
			case 'FOR':
				forStatement();
				break;
		}
	}
}

function draw()
{
	global $originX, $originY, $rot, $scaleX, $scaleY, $startValue, $endValue, $stepValue, $t;
	global $drawX, $drawY, $im;
	$red = imagecolorallocate($im, 255, 0, 0); //设置一个颜色变量为红色
	for ($i = $startValue; $i < $endValue; $i += $stepValue) { 
		$t = $i;
		$rotValue = getValue($rot);
		//计算值
		$X = getValue($drawX);
		$Y = getValue($drawY);
		//比例变化
		$X *= $scaleX;
		$Y *= $scaleY;
		//旋转
		$tmp = $X * cos($rotValue) + $Y * sin($rotValue);
		$Y = $Y * cos($rotValue) - $X * sin($rotValue);
		$X = $tmp;
		//平移
		$X += $originX;
		$Y += $originY;
		//画图
		imagesetpixel($im, $X, $Y, $red);
	}
}

global $originX, $originY, $rot, $scaleX, $scaleY, $startValue, $endValue, $stepValue;
global $drawX, $drawY, $im;
global $t;
$im = imagecreatetruecolor(1000, 800); //创建画布
parser();
header('Content-type:image/png'); 
imagepng($im); //输出
