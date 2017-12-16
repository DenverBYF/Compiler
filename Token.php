<?php


/**
 * Created by PhpStorm.
 * User: denverb
 * Date: 17/11/30
 * Time: 下午4:03
 */
class Token
{
	public $tokenType = ['ORIGIN', 'SCALE', 'ROT', 'IS',
		'TO', 'STEP', 'DRAW', 'FOR', 'FROM',
		'T',
		'SEMICO', 'L_BRACKET', 'R_BRACKET', 'COMMA',
		'PLUS', 'MINUS', 'MUL', 'DIV', 'POWER',
		'FUNC',
		'CONST_ID',
		'NONTOKEN',
		'ERRTOKEN'
	];
	public $type, $lexeme, $value, $FuncPtr, $index;

	public function __construct($type, $lexeme, $value = 0, $FuncPtr = null)
	{
		$this->type = $type;
		$this->lexeme = $lexeme;
		$this->value = $value;
		$this->FuncPtr = $FuncPtr;
		$this->index = array_search($type, $this->tokenType);
	}

}
