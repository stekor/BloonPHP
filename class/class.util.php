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

Class Util{
	public function DiffTime($microtime1, $microtime2){
		list($micro1, $time1) = explode(' ', $microtime1);
		list($micro2, $time2) = explode(' ', $microtime2);
		$time = $time2 - $time1;
		
		if ($micro1 > $micro2){
			$time--;
			$micro = 1 + $micro2 - $micro1;	 
		}else{
			$micro = $micro2 - $micro1;
		}
		
		$micro += $time;
		$split = explode(".", $micro);
		return $split;
	}
	public function CheckExtensions(){
		self::ExtensionLoaded("pthreads");
		self::ExtensionLoaded("sockets");
		self::ExtensionLoaded("pdo_mysql");
		return true;
	}
	private function ExtensionLoaded($ext){
		if(!extension_loaded($ext)){
			Console::WriteLine("Please install php ".$ext." ! Program can't run without it.");
			exit;
		}
		return true;
	}
	public static function toByteArray($gmp){
		$result = Array();
		$base16 = gmp_strval($gmp,16);
		if(strlen($base16) % 2 != 0){
			$base16 = "0".$base16;
		}
		$hexs = str_split($base16, 2);
		foreach($hexs as $hex){
			$result[] = hexdec($hex);
		}
		return $result;
	}
	public static function GenerateRandomHexString($len){
		$output = sha1( uniqid() . md5( rand() . uniqid() ) ).sha1( uniqid() . md5( rand() . uniqid() ) );
		return substr($output, 0, $len);
	}
	public static function ParseString($string){
		for($i = 0; $i < 20; $i++){
			$string = str_replace(chr($i), "[".$i."]", $string);
		}
		return $string;
	}
	public static function Crossdomain(){
		return '<?xml version="1.0"?>
		<!DOCTYPE cross-domain-policy SYSTEM "/xml/dtds/cross-domain-policy.dtd">
		<cross-domain-policy>
		<allow-access-from domain="*" to-ports="1-31111" />
		</cross-domain-policy>'.chr(0);
	}
	public static function EventMethod($classname, $method){
		return array("parent" => $classname, "method" => $method);
	}
	public static function FixMySQLPort($port){
		if($port != 3306){
			return chr(58).$port;
		}else{
			return "";
		}
	}
	public function EventTasks($bool=true){
		if($bool){
			Console::SetTitle('BloonCrypto - Users online : '.Network::UsersOnline().' - Rooms loaded : '.RoomManager::RoomsLoaded().' - Memory : '.self::get_php_memory());
		}
		Pooling::ManagePool();
	}
	public static function get_php_memory(){
		$mem_usage = memory_get_usage(true); 
		if ($mem_usage < 1024){
			$result = $mem_usage." o"; 
		}else if ($mem_usage < 1048576){
			$result = round($mem_usage/1024,2)." Ko"; 
		}else{
			$result = round($mem_usage/1048576,2)." Mo"; 
		}
		return $result;
	}
	public static function RotationCalculate($one,$two){
		$one_x = $one[0];
		$one_y = $one[1];
		$two_x = $two[0];
		$two_y = $two[1];
		
		if($one_x > $two_x && $one_y > $two_y){
			return 7;
		}else if($one_x < $two_x && $one_y < $two_y){
			return 3;
		}else if($one_x > $two_x && $one_y < $two_y){
			return 5;
		}else if($one_x < $two_x && $one_y > $two_y){
			return 1;
		}else if($one_x > $two_x){
			return 6;
		}else if($one_x < $two_x){
			return 2;
		}else if($one_y < $two_y){
			return 4;
		}else if($one_y > $two_y){
			return 0;
		}
		return 0;
	}
}
?>