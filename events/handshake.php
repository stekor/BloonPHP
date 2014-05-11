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

Class HandshakeEvents{
	public function __construct(){
		$this->initializeEvents();
	}
	public function initializeEvents(){
		global $events,$headermanager;
		$events[$headermanager->Incoming("GetClientVersionMessageEvent")] = Util::EventMethod(get_class($this), "GetClientVersionMessageEvent");
		$events[$headermanager->Incoming("InitCryptoMessageEvent")] = Util::EventMethod(get_class($this), "InitCryptoMessageEvent");
		$events[$headermanager->Incoming("GenerateSecretKeyMessageEvent")] = Util::EventMethod(get_class($this), "GenerateSecretKeyMessageEvent");
		$events[$headermanager->Incoming("ClientVars")] = Util::EventMethod(get_class($this), "ClientVars");
		$events[$headermanager->Incoming("SSOTicketMessageEvent")] = Util::EventMethod(get_class($this), "SSOTicketMessageEvent");
		$events[$headermanager->Incoming("InfoRetrieveMessageEvent")] = Util::EventMethod(get_class($this), "UserObjectComposer");
		$events[$headermanager->Incoming("SerializeClub")] = Util::EventMethod(get_class($this), "SerializeClub");
		$events[$headermanager->Incoming("LoadSettings")] = Util::EventMethod(get_class($this), "LoadSettings");
	}
	public static function GetClientVersionMessageEvent($user, $buffer){
		global $user;
	}
	public static function InitCryptoMessageEvent($user, $buffer){
		global $user,$headermanager;
		$user->DH = new DiffieHellman;
		$user->DH->GenerateDH();
		$user->Prime = $user->DH->GetPrime();
		$user->Generator = $user->DH->GetGenerator();
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("InitCryptoMessageComposer"));
		// $packet->WriteString(Util::GenerateRandomHexString(32));
		$packet->WriteString(sha1($user->id));
		$packet->WriteBoolean(false);
		$user->Send($packet->Finalize());
	}
	public static function GenerateSecretKeyMessageEvent($user, $buffer){
		global $user,$headermanager,$rsa;
		$rsadata = BufferManager::ReadString($buffer)["result"];
		/*$Prime = "114670925920269957593299136150366957983142588366300079186349531";
		$Generator = "1589935137502239924254699078669119674538324391752663931735947";
		$DH = new DiffieHellman;
		$DH->InitDH($Prime, $Generator);
		$DH->GenerateSharedKey(str_replace(chr(0), "", $rsa->Decrypt($rsadata)));*/
		
		$user->DH->GenerateSharedKey(str_replace(chr(0), "", $rsa->Decrypt($rsadata)));
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SecretKeyMessageComposer"));
		// $packet->WriteString($DH->GetPublicKey());
		$packet->WriteString($user->DH->GetPublicKey());
		$user->Send($packet->Finalize());
		
		// $user->rc4->Init($DH->GetSharedKey(true));
		$user->rc4->Init($user->DH->GetSharedKey(true));
		$user->rc4initialized = true;
	}
	public static function ClientVars($user, $buffer){
		global $user;
		$read = BufferManager::ReadInt32($buffer);
		$buffer = $read["buffer"];
		$id = $read["result"];
		// Console::WriteLine("- ID : ".$id);
		
		$read = BufferManager::ReadString($buffer);
		$buffer = $read["buffer"];
		$flashbase = $read["result"];
		// Console::WriteLine("- Flashbase: ".$flashbase);
		
		$read = BufferManager::ReadString($buffer);
		$buffer = $read["buffer"];
		$variables = $read["result"];
		// Console::WriteLine("- Variables: ".$variables);
	}
	public static function SSOTicketMessageEvent($user, $buffer){
		global $user,$headermanager;
		$ticket = BufferManager::ReadString($buffer)["result"];
		
		$query = DB::Query("SELECT * FROM users WHERE auth_ticket = ? LIMIT 1",array($ticket));
		
		if(count($query) == 0){			
			$user->SendNotif("Aucun utilisateur trouvé, veuillez recharger le client !");
		}else{
			$user->habbo = $query[0];
			Console::WriteLine("- ". $user->habbo->username ." logged in !");
			// $user->habbo->home_room = 218224;
			$user->InRoom = false;
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("AuthenticationOKMessageComposer"));
			$user->Send($packet->Finalize());
			
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("HomeRoom"));
			$packet->WriteInt32($user->habbo->home_room);
			$packet->WriteInt32($user->habbo->home_room);
			$user->Send($packet->Finalize());
			
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("SerializeMiniMailCount"));
			$packet->WriteInt32(1);
			$user->Send($packet->Finalize());
			
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("bools1"));
			$packet->WriteBoolean(true);
			$packet->WriteBoolean(false);
			$user->Send($packet->Finalize());
			
			$user->UpdateActivityPointsBalance();
			$user->UpdateCreditsBalance();
		}
	}
	public static function UserObjectComposer($user, $buffer){
		global $user,$headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("UserObjectMessageComposer"));
		$packet->WriteInt32($user->habbo->id);
		$packet->WriteString($user->habbo->username);
		$packet->WriteString($user->habbo->look);
		$packet->WriteString(strtoupper($user->habbo->gender));
		$packet->WriteString($user->habbo->motto);
		$packet->WriteString("");
		$packet->WriteBoolean(false);
		$packet->WriteInt32($user->habbo->respect);
		$packet->WriteInt32($user->habbo->daily_respect_points);
		$packet->WriteInt32($user->habbo->daily_pet_respect_points);
		$packet->WriteBoolean(true);
		// $packet->WriteString($user->habbo->last_online);
		$packet->WriteString("11-11-2012 14:46:41");
		$packet->WriteBoolean(false);
		$packet->WriteBoolean(false);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("UserPerksMessageComposer"));
		$packet->WriteInt32(9);
		$packet->WriteString("SAFE_CHAT");
		$packet->WriteBoolean(true);
		$packet->WriteString("");
		$packet->WriteString("USE_GUIDE_TOOL");
		$packet->WriteBoolean(false);
		$packet->WriteString("requirement.unfulfilled.helper_le");
		$packet->WriteString("GIVE_GUIDE_TOURS");
		$packet->WriteBoolean(false);
		$packet->WriteString("");
		$packet->WriteString("JUDGE_CHAT_REVIEWS");
		$packet->WriteBoolean(false);
		$packet->WriteString("");
		$packet->WriteString("CALL_ON_HELPERS");
		$packet->WriteBoolean(false);
		$packet->WriteString("");
		$packet->WriteString("CITIZEN");
		$packet->WriteBoolean(true);
		$packet->WriteString("");
		$packet->WriteString("FULL_CHAT");
		$packet->WriteBoolean(true);
		$packet->WriteString("");
		$packet->WriteString("TRADE");
		$packet->WriteBoolean(true);
		$packet->WriteString("");
		$packet->WriteString("VOTE_IN_COMPETITIONS");
		$packet->WriteBoolean(false);
		$packet->WriteString("requirement.unfulfilled.helper_level_2");
		$user->Send($packet->Finalize());
		
		$user->LoadFriends();
	}
	public static function SerializeClub($user, $buffer){
		global $user,$headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SerializeClub"));
		$packet->WriteString("club_habbo");
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteBoolean(false);
		$packet->WriteBoolean(true);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteInt32(495);
		$user->Send($packet->Finalize());
	}
	public static function LoadSettings($user, $buffer){
		global $user,$headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("LoadVolume"));
		$packet->WriteInt32($user->habbo->volume);
		$packet->WriteInt32($user->habbo->volume);
		$packet->WriteInt32($user->habbo->volume);
		$user->Send($packet->Finalize());
	}
}
new HandshakeEvents;
?>