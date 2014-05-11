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

Class HabboEncoding{
	public static function DecodeBit16($v){
		$v = str_split($v);
		if(!isset($v[0]) || !isset($v[1])){
			return -1;
		}
		if ((ord($v[0]) | ord($v[1])) < 0)
				return -1;
		return ((ord($v[0]) << 8) + (ord($v[1]) << 0));
	}
	public static function DecodeBit32($v) {
		$v = str_split($v);
		if(!isset($v[0]) || !isset($v[1]) || !isset($v[2]) || !isset($v[3])){
			return -1;
		}
		if ((ord($v[0]) | ord($v[1]) | ord($v[2]) | ord($v[3])) < 0)
				return -1;
		return ((ord($v[0]) << 24) + (ord($v[1]) << 16) + (ord($v[2]) << 8) + (ord($v[3]) << 0));
	}
	public static function EncodeBit16($value){
		$result = chr(($value >> 8) & 0xFF);
		$result.= chr($value & 0xFF);
		return $result;
	}
	public static function EncodeBit32($value){
		$result = chr(($value >> 24) & 0xFF);
		$result.= chr(($value >> 16) & 0xFF);
		$result.= chr(($value >> 8) & 0xFF);
		$result.= chr($value & 0xFF);
		return $result;
	}
	public static function EncodeString($string){
		return self::EncodeBit16(strlen($string)).$string;
	}
	public static function EncodeBoolean($bool){
		if($bool) return chr(1);
		return chr(0);
	}
}
?>