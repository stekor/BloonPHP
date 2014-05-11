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

Class Network{
	public function Bind($address,$port){
		$master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
		socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1)  or die("socket_option() failed");
		socket_bind($master, $address, $port)                    or die("socket_bind() failed");
		socket_listen($master,20)                                or die("socket_listen() failed");
		Console::WriteLine("Listening for connections on port : ".$port);
		return $master;
	}
	public function Connect($socket){
		global $sockets,$users;
		socket_getpeername($socket, $ip, $port);
		$user = new User();
		$user->id = uniqid();
		$user->socket = $socket;
		$user->ip = $ip;
		$user->port = $port;
		$user->rc4initialized = false;
		$user->rc4 = new RC4;
		$user->lastPing = time();
		array_push($users,$user);
		array_push($sockets,$socket);
	}
	public function Disconnect($socket){
		global $sockets,$users;
		$found = null;
		$user = $this->GetUser("socket", $socket);
		if($user->InRoom){
			$user->LeaveRoom(true);
		}
		$n = count($users);
		for($i=0; $i < $n; $i++){
			if($users[$i]->socket==$socket){
				$found=$i;
				break;
			}
		}
		if(!is_null($found)){
			array_splice($users,$found,1);
		}
		$index = array_search($socket,$sockets);
		socket_close($socket);
		if($index>=0){
			array_splice($sockets,$index,1);
		}
	}
	public function GetUser($by, $search){
		global $users;
		$found=null;
		foreach($users as $user){
			if($user->$by==$search){
				$found = $user;
				break;
			}
		}
		return $found;
	}
	public static function Send($socket, $buffer){
		// Console::WriteLine(">> ".$buffer);
		socket_write($socket,$buffer,strlen($buffer));
	}
	public static function UsersOnline(){
		global $users;
		$cpt = 0;
		if(count($users) > 0){
			foreach($users as $user){
				if(isset($user->habbo->id)){
					$cpt++;
				}
			}
		}
		return $cpt;
	}
}
?>