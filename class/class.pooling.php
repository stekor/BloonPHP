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

Class Pooling{
	public static function ManagePool(){
		global $sqls;
		$users_online = Network::UsersOnline();
		if($users_online == 0){
			$users_online = 1;
		}
		$needed = ceil($users_online/4);
		$count = count($sqls["instance"]);
		if($needed < $count){
			$for = $count-$needed;
			for($i = 0; $i < $for; $i++):
				self::RemovePool();
			endfor;
		}else if($needed > $count){
			$for = $needed-$count;
			for($i = 0; $i < $for; $i++):
				self::AddPool();
			endfor;
		}
	}
	public static function AddPool(){
		global $sqls,$config;
		try{
			$count = count($sqls["instance"])+1;
			$sqls["instance"][$count] = new PDO('mysql:host='.$config->Get("db.hostname").Util::FixMySQLPort($config->Get("db.port")).';dbname='.$config->Get("db.name"), $config->Get("db.username"), $config->Get("db.password"));
			$sqls["lastuse"][$count] = time();
			Console::WriteLine("Created pool #".$count);
		}catch(Exception $error){
			Console::WriteLine("Error in new pool creation ! ".$error->getMessage());
		}
	}
	public static function RemovePool(){
		global $sqls;
		array_pop($sqls["instance"]);
		array_pop($sqls["lastuse"]);
	}
	public static function GetPool(){
		global $sqls;
		$small = time();
		$result = 1;
		foreach($sqls["lastuse"] as $key => $lastuse):
			if($lastuse < $small){
				$small = $lastuse;
				$result = $key;
			}
		endforeach;
		
		$sqls["lastuse"][$result] = time();
		return $sqls["instance"][$result];
	}
}
?>