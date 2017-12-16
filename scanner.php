<?php
/**
 * Created by PhpStorm.
 * User: denverb
 * Date: 17/11/30
 * Time: 下午4:12
 */

require_once ('Token.php');


$tokenTab = [
	'PI' => new Token("CONST_ID",	"PI",		3.1415926,	null),
	'E' => new Token("CONST_ID",   "E",		2.71828,	null),
	'T' => new Token("T",			"T",		0.0,		null),
	'SIN' => new Token("FUNC",		"SIN",		0.0,		"sin"),
	'COS' => new Token("FUNC",		"COS",		0.0,		"cos"),
	'TAN' => new Token("FUNC",		"TAN",		0.0,		"tan"),
	'LN' => new Token("FUNC",		"LN",		0.0,		"log"),
	'EXP' => new Token("FUNC",		"EXP",		0.0,		"exp"),
	'SQRT' => new Token("FUNC",		"SQRT",		0.0,		"sqrt"),
	'ORIGIN' => new Token("ORIGIN",	"ORIGIN",	0.0,		null),
	'SCALE' => new Token("SCALE",		"SCALE",	0.0,		null),
	'ROT' => new Token("ROT",		"ROT",		0.0,		null),
	'IS' => new Token("IS",		"IS",		0.0,		null),
	'FOR' => new Token("FOR",		"FOR",		0.0,		null),
	'FROM' => new Token("FROM",		"FROM",		0.0,		null),
	'TO' => new Token("TO",		"TO",		0.0,		null),
	'STEP' => new Token("STEP",		"STEP",		0.0,		null),
	'DRAW' => new Token("DRAW",		"DRAW",		0.0,		null)
];

function judgeToken($type)
{
	global $tokenTab;
	if (in_array($type, array_keys($tokenTab))) {
		return $tokenTab[$type];
	}
}

function getToken ()
{
	$contentStack = array();
	$ret = [];
	$content = fopen('test.txt', 'r');
	$fileContent = fread($content, filesize('test.txt'));
	for ($i = 0; $i < strlen($fileContent); $i++) {
		array_push($contentStack, $fileContent[$i]);
	}
	$tmp = "";
	while (!empty($contentStack)) {
		$char = array_shift($contentStack);
		$tmp = $char;
		if(ctype_alpha($char)) {
			while (1) {
				$tmpChar = array_shift($contentStack);
				if (ctype_alnum($tmpChar)) {
					$tmp .= $tmpChar;
				} else {
					break;
				}
			}
			$token = judgeToken(strtoupper($tmp));
			if(!empty($token)) {
				$ret[] = $token;
			}
			$tmp = "";
			array_unshift($contentStack, $tmpChar);
		} elseif (ctype_digit($char)) {
			while (1) {
				$tmpChar = array_shift($contentStack);
				if (ctype_digit($tmpChar) or $tmpChar == ".") {
					$tmp .= $tmpChar;
				} else {
					break;
				}
			}
			$ret[] = new Token("CONST_ID", $tmp, floatval($tmp) );
			$tmp = "";
			array_unshift($contentStack, $tmpChar);
		} else {
			switch ($char) {
				case ';':
					$ret[] = new Token("SEMICO", $char);
					break;
				case '(':
					$ret[] = new Token("L_BRACKET", $char);
					break;
				case ')':
					$ret[] = new Token("R_BRACKET", $char);
					break;
				case ',':
					$ret[] = new Token("COMMA", $char);
					break;
				case '+':
					$ret[] = new Token("PLUS", $char);
					break;
				case '*':
					$char = array_shift($contentStack);
					if ($char == '*') {
						$ret[] = new Token("POWER", "**");
					} else{
						array_unshift($contentStack, $char);
						$ret[] = new Token("MUL", "*");
					}
					break;
				case '-':
					$char = array_shift($contentStack);
					if ($char == '-') {
						while (!empty($contentStack) and array_shift($contentStack) != '\n') {

						}
					} else {
						array_unshift($contentStack, $char);
						$ret[] = new Token("MINUS", "-");
					}
					break;
				case '/':
					$char = array_shift($contentStack);
					if ($char == '/') {
						while (!empty($contentStack) and array_shift($contentStack) != '\n') {

						}
					} else {
						array_unshift($contentStack, $char);
						$ret[] = new Token("DIV", "/");
					}
					break;
			}
		}
	}
	return $ret;

}

$ret = getToken();
/*	foreach ($ret as $eachToken) {
		printf("%s 		%s 		%.6f 	%s \n",
			$eachToken->index, $eachToken->lexeme, $eachToken->value, $eachToken->FuncPtr??'0');
	}*/

