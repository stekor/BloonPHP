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

Class HabboEvents{
	public function __construct(){
		$this->initializeEvents();
	}
	public function initializeEvents(){
		global $events,$headermanager;
		$events[$headermanager->Incoming("LoadProfile")] = Util::EventMethod(get_class($this), "LoadProfile");
		$events[$headermanager->Incoming("GetRelationships")] = Util::EventMethod(get_class($this), "GetRelationships");
		$events[$headermanager->Incoming("GetUserBadges")] = Util::EventMethod(get_class($this), "GetUserBadges");
		$events[$headermanager->Incoming("ChatMessageEvent")] = Util::EventMethod(get_class($this), "ChatMessageEvent");
		$events[$headermanager->Incoming("ShoutMessageEvent")] = Util::EventMethod(get_class($this), "ShoutMessageEvent");
		$events[$headermanager->Incoming("Move")] = Util::EventMethod(get_class($this), "Move");
		$events[$headermanager->Incoming("Pong")] = Util::EventMethod(get_class($this), "Pong");
		$events[$headermanager->Incoming("StartTypingMessageEvent")] = Util::EventMethod(get_class($this), "StartTypingMessageEvent");
		$events[$headermanager->Incoming("CancelTypingMessageEvent")] = Util::EventMethod(get_class($this), "CancelTypingMessageEvent");
		$events[$headermanager->Incoming("LookTo")] = Util::EventMethod(get_class($this), "LookTo");
		$events[$headermanager->Incoming("GetUserTags")] = Util::EventMethod(get_class($this), "GetUserTags");
		$events[$headermanager->Incoming("GetMarketplaceConfigurationMessageEvent")] = Util::EventMethod(get_class($this), "GetMarketplaceConfigurationMessageEvent");
		$events[$headermanager->Incoming("OpenInventory")] = Util::EventMethod(get_class($this), "OpenInventory");
		$events[$headermanager->Incoming("PetInventory")] = Util::EventMethod(get_class($this), "PetInventory");
		$events[$headermanager->Incoming("ChangeLook")] = Util::EventMethod(get_class($this), "ChangeLook");
	}
	public static function ChangeLook($user, $buffer){
		global $user,$headermanager;
		
		$read = BufferManager::ReadString($buffer);
		$gender = strtoupper($read["result"]);
		$buffer = $read["buffer"];
		$figure = BufferManager::ReadString($buffer)["result"];
		
		//need add mutant check for figure
		if($gender == "M" || $gender == "F"){
			DB::Exec("UPDATE users SET gender = ?,look = ? WHERE id = ?",array($gender,$figure,$user->habbo->id));
			$user->habbo->look = $figure;
			$user->habbo->gender = $gender;
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("UpdateUserInformation"));
			$packet->WriteInt32(-1);
			$packet->WriteString($figure);
			$packet->WriteString(strtolower($gender));
			$packet->WriteString($user->habbo->motto);
			$packet->WriteInt32(0);
			$user->Send($packet->Finalize());
			
			if($user->InRoom){
				$packet = new PacketConstructor;
				$packet->SetHeader($headermanager->Outgoing("UpdateUserInformation"));
				$packet->WriteInt32($user->habbo->id);
				$packet->WriteString($figure);
				$packet->WriteString(strtolower($gender));
				$packet->WriteString($user->habbo->motto);
				$packet->WriteInt32(0);
				RoomManager::SendToAllRoom($user->CurrentRoom, $packet->Finalize());
			}
		}else{
			$user->SendNotif("Error in gender");
		}
	}
	public static function PetInventory($user, $buffer){
		global $user,$headermanager;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("PetInventory"));
		$packet->WriteInt32(1);
		$packet->WriteInt32(1);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
	}
	public static function OpenInventory($user, $buffer){
		global $user,$headermanager;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("Inventory"));
		$packet->WriteInt32(1);
		$packet->WriteInt32(1);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
	}
	public static function GetMarketplaceConfigurationMessageEvent($user, $buffer){
		global $user,$headermanager;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("MarketplaceConfigurationMessageComposer"));
		$packet->WriteBoolean(true);
		$packet->WriteInt32(1);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteInt32(1);
		$packet->WriteInt32(10000);
		$packet->WriteInt32(48);
		$packet->WriteInt32(7);
		$user->Send($packet->Finalize());
	}
	public static function GetUserTags($user, $buffer){
		global $user,$headermanager;
		$id = BufferManager::ReadInt32($buffer)["result"];
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("GetUserTags"));
		$packet->WriteInt32($id);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
	}
	public static function StartTypingMessageEvent($user, $buffer){
		global $user,$headermanager;
		$user->UserTyping(true);
	}
	public static function LookTo($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadInt32($buffer);
		$X = $read["result"];
		$buffer = $read["buffer"];
		$Y = BufferManager::ReadInt32($buffer)["result"];
		
		if($user->x == $X && $user->y == $Y) return;
		
		$user->rot = Util::RotationCalculate(array($user->x,$user->y),array($X,$Y));
		$user->UpdateState($user->x, $user->y);
	}
	public static function CancelTypingMessageEvent($user, $buffer){
		global $user,$headermanager;
		$user->UserTyping(false);
	}
	public static function LoadProfile($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadInt32($buffer);
		$id = $read["result"];
		$buffer = $read["buffer"];
		
		$read = BufferManager::ReadBoolean($buffer);
		$isme = $read["result"];
		$isme = false;
		if($isme){
			$profile = $user->habbo;
		}else{
			$profile = DB::Query("SELECT id,username,look,motto,account_created,last_online,online FROM users WHERE id = ? LIMIT 1", array($id));
			if(count($profile) == 0){
				return false;
			}
			$profile = $profile[0];
		}
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ProfileInformation"));
		$packet->WriteInt32($profile->id);
		$packet->WriteString($profile->username);
		$packet->WriteString($profile->look);
		$packet->WriteString($profile->motto);
		$packet->WriteString(str_replace("-", "/", $profile->account_created));
		$packet->WriteInt32(5000);
		$packet->WriteInt32(0);
		$packet->WriteBoolean(false);
		$packet->WriteBoolean(false);
		$packet->WriteBoolean($profile->online == 1 ? true : false);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$packet->WriteBoolean(true);
		$user->Send($packet->Finalize());
	}
	public static function GetRelationships($user, $buffer){
		global $user,$headermanager;
		$id = BufferManager::ReadInt32($buffer)["result"];
		$relationships = DB::Query("SELECT r.target,r.type,u.username,u.look FROM user_relationships r, users u WHERE u.id = r.target AND r.user_id = ? ORDER BY r.id", array($id));
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("GetRelationships"));
		$packet->WriteInt32($id);
		$packet->WriteInt32(count($relationships));
		
		$Loves = 0;
		$Likes = 0;
		$Hates = 0;
		
		foreach($relationships as $relation){
			if($relation->type == 1){
				$Loves++;
			}else if($relation->type == 2){
				$Likes++;
			}else if($relation->type == 3){
				$Hates++;
			}
		}
		foreach($relationships as $relation){
			$packet->WriteInt32($relation->type);
			if($relation->type == 1){
				$packet->WriteInt32($Loves);
			}else if($relation->type == 2){
				$packet->WriteInt32($Likes);
			}else if($relation->type == 3){
				$packet->WriteInt32($Hates);
			}
			$packet->WriteInt32($relation->target);
			$packet->WriteString($relation->username);
			$packet->WriteString($relation->look);
		}
		
		$user->Send($packet->Finalize());
	}
	public static function GetUserBadges($user, $buffer){
		global $user,$headermanager;
		$id = BufferManager::ReadInt32($buffer)["result"];
		$badges = DB::Query("SELECT badge_id,badge_slot FROM user_badges WHERE user_id = ?", array($id));
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("GetUserBadges"));
		$packet->WriteInt32($id);
		$equiped = 0;
		foreach($badges as $badge){
			if($badge->badge_slot > 0){
				$equiped++;
			}
		}
		$packet->WriteInt32($equiped);
		foreach($badges as $badge){
			if($badge->badge_slot <= 0) continue;
			
			$packet->WriteInt32($badge->badge_slot);
			$packet->WriteString($badge->badge_id);
		}
		$user->Send($packet->Finalize());
	}
	public static function ChatMessageEvent($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadString($buffer);
		$string = $read["result"];
		$buffer = $read["buffer"];
		$colorid = BufferManager::ReadInt32($buffer)["result"];
		
		$string = trim($string);
		if($string == "") return;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ChatMessageComposer"));
		$packet->WriteInt32($user->habbo->id);
		$packet->WriteString($string);
		$packet->WriteInt32(0);
		$packet->WriteInt32($colorid);
		$packet->WriteInt32(0);
		$packet->WriteInt32(-1);
		RoomManager::SendToAllRoom($user->CurrentRoom, $packet->Finalize());
	}
	public static function ShoutMessageEvent($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadString($buffer);
		$string = $read["result"];
		$buffer = $read["buffer"];
		$colorid = BufferManager::ReadInt32($buffer)["result"];
		
		$string = trim($string);
		if($string == "") return;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ShoutMessageComposer"));
		$packet->WriteInt32($user->habbo->id);
		$packet->WriteString($string);
		$packet->WriteInt32(0);
		$packet->WriteInt32($colorid);
		$packet->WriteInt32(0);
		$packet->WriteInt32(-1);
		RoomManager::SendToAllRoom($user->CurrentRoom, $packet->Finalize());
	}
	public static function Move($user, $buffer){
		global $user,$headermanager,$threadsender;
		include "pathfinder_temp.php";
	}
	public static function Pong($user, $buffer){
		global $user,$headermanager,$threadsender;
		Console::WriteLine("- Received pong from ".$user->habbo->username);
	}
}
new HabboEvents;