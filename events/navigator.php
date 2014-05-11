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

Class NavigatorEvents{
	public function __construct(){
		$this->initializeEvents();
	}
	public function initializeEvents(){
		global $events,$headermanager;
		$events[$headermanager->Incoming("LoadFeaturedRooms")] = Util::EventMethod(get_class($this), "LoadFeaturedRooms");
		$events[$headermanager->Incoming("LoadMyRooms")] = Util::EventMethod(get_class($this), "LoadMyRooms");
		$events[$headermanager->Incoming("PopularRoomsSearchEvent")] = Util::EventMethod(get_class($this), "PopularRoomsSearchEvent");
	}
	public static function LoadFeaturedRooms($user, $buffer){
		global $user,$headermanager;
		$id = BufferManager::ReadInt32($buffer)["result"];
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("NavigatorPacket"));
		$packet->WriteInt32(0);
		$packet->WriteString("");
		$packet->WriteInt32(0);
		$packet->WriteBoolean(false);
		$user->Send($packet->Finalize());
	}
	public static function LoadMyRooms($user, $buffer){
		global $user,$headermanager;
		$myrooms = DB::Query("SELECT r.*,u.id as owner_id FROM rooms r,users u WHERE u.username = r.owner AND u.id = ?",array($user->habbo->id));
		// $myrooms = DB::Query("SELECT r.*,u.id as owner_id FROM rooms r,users u WHERE u.username = r.owner AND r.owner = ?",array("Burak"));
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("NavigatorPacket"));
		$packet->WriteInt32(16);
		$packet->WriteString("");
		$packet->WriteInt32(count($myrooms));
		foreach($myrooms as $room){
			$packet->WriteInt32($room->id);
			$packet->WriteString($room->caption);
			$packet->WriteBoolean(true);
			$packet->WriteInt32($room->owner_id);
			$packet->WriteString($room->owner);
			
			if($room->state == "open"){
				$packet->WriteInt32(0);
			}else if($room->state == "locked"){
				$packet->WriteInt32(1);
			}else if($room->state == "password"){
				$packet->WriteInt32(2);
			}else{
				$packet->WriteInt32(0);
			}
			
			$packet->WriteInt32($room->users_now);
			$packet->WriteInt32($room->users_max);
			$packet->WriteString($room->description);
			$packet->WriteInt32(0);
			$packet->WriteInt32(0);
			$packet->WriteInt32($room->score);
			$packet->WriteInt32(0);
			$packet->WriteInt32($room->category);
			//group
			$packet->WriteInt32(0);
			$packet->WriteString("");
			$packet->WriteString("");
			$packet->WriteString("");
			
			$packet->WriteInt32(0);

			$packet->WriteInt32(0);
			$packet->WriteInt32(0);
			$packet->WriteBoolean(false);
			$packet->WriteBoolean(false);
			
			$packet->WriteInt32(0);
			$packet->WriteString("");
			$packet->WriteString("");
			$packet->WriteInt32(0);
		}
		$packet->WriteBoolean(false);
		$user->Send($packet->Finalize());
	}
	public static function PopularRoomsSearchEvent($user, $buffer){
		global $user,$headermanager;
		$type = BufferManager::ReadString($buffer)["result"];
		
		if($type == "-1"){
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("NavigatorPacket"));
			$packet->WriteInt32(1);
			$packet->WriteString("-1");
			
			$roomslist = DB::Query("SELECT u.username,u.id as userid,r.id,r.caption,r.category,r.score,r.description,r.state,r.users_max,r.users_now FROM rooms r,users u WHERE u.username = r.owner AND r.users_now > 0 ORDER BY -r.users_now LIMIT 50");
			
			$packet->WriteInt32(count($roomslist));
			
			foreach($roomslist as $room){
				$packet->WriteInt32($room->id);
				$packet->WriteString($room->caption);
				$packet->WriteBoolean(true);
				$packet->WriteInt32($room->id);
				$packet->WriteString($room->username);
				
				if($room->state == "open"){
					$packet->WriteInt32(0);
				}else if($room->state == "locked"){
					$packet->WriteInt32(1);
				}else if($room->state == "password"){
					$packet->WriteInt32(2);
				}else{
					$packet->WriteInt32(0);
				}
				
				$packet->WriteInt32($room->users_now);
				$packet->WriteInt32($room->users_max);
				$packet->WriteString($room->description);
				$packet->WriteInt32(0);
				$packet->WriteInt32(0);
				$packet->WriteInt32($room->score);
				$packet->WriteInt32(0);
				$packet->WriteInt32($room->category);
				//group
				$packet->WriteInt32(0);
				$packet->WriteString("");
				$packet->WriteString("");
				$packet->WriteString("");
				
				$packet->WriteInt32(0);

				$packet->WriteInt32(0);
				$packet->WriteInt32(0);
				$packet->WriteBoolean(false);
				$packet->WriteBoolean(false);
				
				$packet->WriteInt32(0);
				$packet->WriteString("");
				$packet->WriteString("");
				$packet->WriteInt32(0);
			}
			$packet->WriteBoolean(false);
			$user->Send($packet->Finalize());
		}
	}
}
new NavigatorEvents;