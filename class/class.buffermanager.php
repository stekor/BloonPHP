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

Class BufferManager{
	public static function ReadString($buffer){
		$len = HabboEncoding::DecodeBit16($buffer);
		$result = substr($buffer, 2, $len);
		$buffer = substr($buffer, $len+2);
		return array("result" => $result, "buffer" => $buffer);
	}
	public static function ReadInt32($buffer){
		$result = HabboEncoding::DecodeBit32($buffer);
		$buffer = substr($buffer, 4);
		return array("result" => $result, "buffer" => $buffer);
	}
	public static function ReadInt16($buffer){
		$result = HabboEncoding::DecodeBit16($buffer);
		$buffer = substr($buffer, 2);
		return array("result" => $result, "buffer" => $buffer);
	}
	public static function ReadBoolean($buffer){
		if(ord($buffer[0]) == 0){
			$result = false;
		}else{
			$result = true;
		}
		$buffer = substr($buffer, 1);
		return array("result" => $result, "buffer" => $buffer);
	}
	public static function Parser($buffer){
		$packet = array();
		while(strlen($buffer) > 3){
			$len = HabboEncoding::DecodeBit32($buffer)+4;
			$packet[] = substr($buffer, 0, $len);
			$buffer = substr($buffer, $len);
		}
		return $packet;
	}
}
?>