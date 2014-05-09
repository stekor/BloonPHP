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

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

/*spl_autoload_register(function ($class) {
	require('class/class.' . strtolower($class) . '.php');
});*/

foreach(glob("class/*.php") as $filename){
	require($filename);
}

$benchtime = Array("start" => microtime());

$util = new Util;
$config = new Config;
$network = new Network;
$rsa = new RSA;
$rsakey = new RSAKey;
$headermanager = new HeaderManager;
$headermanager->LoadHeader();

$rsa->SetPrivate($rsakey->Get("n"), $rsakey->Get("e"), $rsakey->Get("d"));

$util->CheckExtensions();

Console::SetTitle("Loading BloonPHP...");

$config->Init("habbo.conf");

$util->EventTasks(false);
$loader = new Loader;

$users   = array();
$events = array();
$rooms = array();

foreach(glob("events/*.php") as $filename){
	require($filename);
}

Console::WriteLine("Loaded ".count($events)." handler !");

$master = $network->Bind($config->Get("game.tcp.bindip"), $config->Get("game.tcp.port"));
$sockets = array($master);

$benchtime["end"] = microtime();
$benchtime = $util->DiffTime($benchtime["start"], $benchtime["end"]);

Console::WriteLine("Server -> READY! (".$benchtime[0]." s, ".$benchtime[1]." ms)");
$util->EventTasks();

while(true){
	$util->EventTasks();
	$changed = $sockets;
	$write=NULL;
	$except=NULL;
	socket_select($changed,$write,$except,NULL);
	foreach($changed as $socket){
		if($socket==$master){
			$client=socket_accept($master);
			if($client<0){
				continue;
			}else{
				$network->Connect($client);
			}
		}else{
			$bytes = @socket_recv($socket,$buffer,2048,0);
			if($bytes==0){
				$network->Disconnect($socket);
			}else{
				if($buffer[0] == "<"){
					Network::Send($socket, Util::Crossdomain());
					break;
				}else if($buffer[0] == "G" && $buffer[1] == "E" && $buffer[2] == "T"){
					$token = explode(chr(1), $buffer)[1];
					$found = 0;
					foreach($users as $user){
						if(sha1($user->id)==$token){
							$found = $user;
							break;
						}
					}
					if(!is_object($found)){
						Network::Send($socket, "0:0");
					}else{
						Network::Send($socket, $found->Prime .":".$found->Generator);
					}
					break;
				}
				$user = $network->GetUser("socket", $socket);

				if($user->rc4initialized){
					$buffer = $user->rc4->Parse($buffer);
					// $user->Ping();
				}
				if($buffer[0] != chr(0)){
					break;
				}
				foreach(BufferManager::Parser($buffer) as $packet){
					$packet = substr($packet, 4);
					$header = HabboEncoding::DecodeBit16($packet);
					$packet = substr($packet, 2);
					if(isset($events[$header])){
						Console::WriteLine("- Executed event for ".$header." (".$events[$header]["parent"].'::'.$events[$header]["method"].")");
						call_user_func_array($events[$header]["parent"].'::'.$events[$header]["method"], array($user, $packet)); // best method
						// eval($events[$header]["parent"].'::'.$events[$header]["method"].'($user, $packet);'); // old bad method
					}else{
						Console::WriteLine("- Not found event for ".$header." : ".$packet);
					}
				}
			}
		}
	}
}
?>