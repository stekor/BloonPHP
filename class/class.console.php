<?php
/*
 * BloonPHP
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonPHP Git Community.
 *
 * RaGEZONE @BurakDev
 * 
 * https://github.com/BurakDev/BloonPHP
 */

Class Console{
	public static function Write($str=""){
		print($str);
	}
	public static function WriteLine($str=""){
		print(Util::ParseString($str)."\n");
	}
	public static function Beep(){
		print(chr(7));
	}
	public static function SetTitle($title){
		system("title ".$title);
	}
}
?>