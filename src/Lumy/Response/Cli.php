<?php

namespace Lumy\Response;

/*
	CLI response object
*/
class Cli extends AbstractResponse{

	/*
		string BLACK	: black sequence
		string RED		: red sequence
		string GREEN	: green sequence
		string YELLOW	: yellow sequence
		string BLUE		: blue sequence
		string PURPLE	: purple sequence
		string CYAN		: cyan sequence
		string GREY		: grey sequence
		string BOLD		: bold sequence
		string DIM		: dim sequence
		string UNDERLINE: underline sequence
		string BLINK	: blink sequence
		string HIGHLIGHT: highlight sequence
		string HIDE		: hide sequence
		string CROSS	: cross sequence
	*/
	const BLACK		= '0m';
	const RED		= '1m';
	const GREEN		= '2m';
	const YELLOW	= '3m';
	const BLUE		= '4m';
	const PURPLE	= '5m';
	const CYAN		= '6m';
	const GREY		= '7m';
	const BOLD		= '1;';
	const DIM		= '2;';
	const UNDERLINE	= '4;';
	const BLINK		= '5;';
	const HIGHLIGHT	= '7;';
	const HIDE		= '8;';
	const STROKE	= '9;';

	/*
		Set an environment variable

		Parameters
			string $name
			string $value
	*/
	public function setVariable($name,$value){
		if($value===null){
			return putenv("$name=");
		}
		else{
			return putenv("$name=$value");
		}
	}

	/*
		Get an environment variable

		Parameters
			string $name

		Return
			string, false
	*/
	public function getVariable($name){
		return getenv((string)$name);
	}

	/*
		Unset an environment variable

		Parameters
			string $name
	*/
	public function unsetVariable($name){
		putenv((string)$name);
	}

	/*
		Generate a color chain to print to the console

		Parameters
			string $color       : forecolor
			string $background  : background color
			boolean $bold       : true to bold the message

		Return
			string
	*/
	public function colorize($color,$background='',$style='0;'){
		// Format
		$color=$color?'3'.$color:'37m';
		$background=$background?"\033[4".$background:$background;
		// Generate formatting
		return "$background\033[$style$color";
	}

	/*
		Reset console color formatting

		Return
			string
	*/
	public function reset(){
		return "\033[0m";
	}

}
