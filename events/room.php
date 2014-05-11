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

Class RoomEvents{
	public function __construct(){
		$this->initializeEvents();
	}
	public function initializeEvents(){
		global $events,$headermanager;
		$events[$headermanager->Incoming("LoadFirstRoomData")] = Util::EventMethod(get_class($this), "LoadFirstRoomData");
		$events[$headermanager->Incoming("AutoRoom")] = Util::EventMethod(get_class($this), "AutoRoom");
		$events[$headermanager->Incoming("GetRoomData1")] = Util::EventMethod(get_class($this), "GetRoomData");
		$events[$headermanager->Incoming("CanCreateRoom")] = Util::EventMethod(get_class($this), "CanCreateRoom");
		$events[$headermanager->Incoming("CreateRoom")] = Util::EventMethod(get_class($this), "CreateRoom");
	}
	public static function CreateRoom($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadString($buffer);
		$roomname = $read["result"];
		$buffer = $read["buffer"];
		$modelname = BufferManager::ReadString($buffer)["result"];
		$NewRoom = RoomManager::CreateRoom($roomname, $modelname,$user->habbo->username);
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("OnCreateRoomInfo"));
		$packet->WriteInt32($NewRoom["data"]->id);
		$packet->WriteString($NewRoom["data"]->caption);
		$user->Send($packet->Finalize());
	}
	public static function CanCreateRoom($user, $buffer){
		global $user,$headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("CanCreateRoom"));
		$packet->WriteInt32(0);
		$packet->WriteInt32(150);
		$user->Send($packet->Finalize());
	}
	public static function LoadFirstRoomData($user, $buffer){
		global $user,$headermanager;
		$read = BufferManager::ReadInt32($buffer);
		$id = $read["result"];
		$buffer = $read["buffer"];
		$password = BufferManager::ReadString($buffer)["result"];
		
		$user->LoadingRoom = 0;
		$user->LoadingChecksPassed = false;
		
		if($user->InRoom){
			$user->LeaveRoom();
		}
		
		$room = RoomManager::GetRoom($id);
		
		$user->LoadingRoom = $id;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("PrepareRoomForUsers"));
		$user->Send($packet->Finalize());
		
		$user->LoadingChecksPassed = true;
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SerializeRoomBadges"));
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("InitialRoomInformation"));
		$packet->WriteString($room["data"]->model_name);
		$packet->WriteInt32($room["data"]->id);
		$user->Send($packet->Finalize());
		
		if($room["data"]->wallpaper != "0.0"){
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("RoomDecoration"));
			$packet->WriteString("wallpaper");
			$packet->WriteString($room["data"]->wallpaper);
			$user->Send($packet->Finalize());
		}
		
		if($room["data"]->floor != "0.0"){
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("RoomDecoration"));
			$packet->WriteString("floor");
			$packet->WriteString($room["data"]->floor);
			$user->Send($packet->Finalize());
		}
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("RoomDecoration"));
		$packet->WriteString("landscape");
		$packet->WriteString($room["data"]->landscape);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("RoomRightsLevel"));
		$packet->WriteInt32(4);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("HasOwnerRights"));
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("RateRoom"));
		$packet->WriteInt32($room["data"]->score);
		$packet->WriteBoolean(false);
		$user->Send($packet->Finalize());
	}
	public static function AutoRoom($user, $buffer){
		global $user,$headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("AutoRoom"));
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
	}
	
	public static function GetRoomData($user, $buffer){
		global $user,$headermanager;		
		$room = RoomManager::GetRoom($user->LoadingRoom);
		$model = RoomManager::GetModel($room["data"]->model_name);
		$user->CurrentRoom = $user->LoadingRoom;
		$user->x = $model->door_x;
		$user->y = $model->door_y;
		$user->z = $model->door_z;
		$user->rot = $model->door_dir;
		
		$heightmap = str_replace(chr(10), "", $model->heightmap .chr(0x0D));
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("HeightMap"));
		$packet->WriteString($heightmap);
		$user->Send($packet->Finalize());
		
		//need cache
		$heightmap_step1 = explode(chr(0x0D), $heightmap);
		$heightmap_door = "";
		foreach($heightmap_step1 as $heightmapkey => $heightmapdata){
			if($heightmapkey != $model->door_y){
				$heightmap_door .= $heightmapdata.chr(0x0D);
			}else{
				$heightmap_split = str_split($heightmapdata);
				foreach($heightmap_split as $keysplit => $splitdata){
					if($keysplit != $model->door_x){
						$heightmap_door.= $splitdata;
					}else{
						$heightmap_door.= $model->door_z;
					}
				}
				$heightmap_door = $heightmap_door.chr(0x0D);
			}
		}
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("RelativeMap"));
		$packet->WriteString($heightmap_door);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SerializeFloorItems"));
		$packet->WriteInt32(1);
		$packet->WriteInt32($room["owner"]->id);
		$packet->WriteString($room["owner"]->username);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SerializeWallItems"));
		$packet->WriteInt32(1);
		$packet->WriteInt32($room["owner"]->id);
		$packet->WriteString($room["owner"]->username);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
		
		$userslist = RoomManager::GetUserByRoom($user->LoadingRoom);
		
		$packetother = new PacketConstructor;
		$packetother->SetHeader($headermanager->Outgoing("SetRoomUser"));
		$packetother->WriteInt32(1);
		$packetother->WriteInt32($user->habbo->id);
		$packetother->WriteString($user->habbo->username);
		$packetother->WriteString($user->habbo->motto);
		$packetother->WriteString($user->habbo->look);
		$packetother->WriteInt32($user->habbo->id);
		$packetother->WriteInt32($user->x);
		$packetother->WriteInt32($user->y);
		$packetother->WriteString($user->z);
		
		$packetother->WriteInt32(2);
		$packetother->WriteInt32(1);
		$packetother->WriteString(strtolower($user->habbo->gender));
		
		$packetother->WriteInt32(0);
		$packetother->WriteInt32(0);
		$packetother->WriteString("");
		
		$packetother->WriteString("");
		$packetother->WriteInt32(0);
		
		// $packetother = $packetother->Finalize();
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SetRoomUser"));
		$packet->WriteInt32(count($userslist));
		
		foreach($userslist as $roomuser){
			$packet->WriteInt32($roomuser->habbo->id);
			$packet->WriteString($roomuser->habbo->username);
			$packet->WriteString($roomuser->habbo->motto);
			$packet->WriteString($roomuser->habbo->look);
			$packet->WriteInt32($roomuser->habbo->id);
			$packet->WriteInt32($roomuser->x);
			$packet->WriteInt32($roomuser->y);
			$packet->WriteString($roomuser->z);
			
			// $packet->WriteInt32($roomuser->rot);
			$packet->WriteInt32(2);
			
			$packet->WriteInt32(1);
			$packet->WriteString(strtolower($roomuser->habbo->gender));
			
			$packet->WriteInt32(0);
			$packet->WriteInt32(0);
			$packet->WriteString("");
			
			$packet->WriteString("");
			$packet->WriteInt32(0);
			// if($roomuser->habbo->id != $user->habbo->id){
				// $roomuser->Send($packetother);
				// $roomuser->UpdateState($roomuser->x, $roomuser->y);
			// }
		}
		RoomManager::SendToAllRoomWithoutOwner($user->CurrentRoom, $packetother->Finalize(), $user->habbo->id);
		$user->Send($packet->Finalize());
		
		$user->UpdateState($user->x, $user->y);
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ConfigureWallandFloor"));
		$packet->WriteBoolean(false);
		$packet->WriteInt32(0);
		$packet->WriteInt32(0);
		$user->Send($packet->Finalize());
		
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ValidRoom"));
		$packet->WriteBoolean(true);
		$packet->WriteInt32($user->CurrentRoom);
		$packet->WriteBoolean(true);
		$user->Send($packet->Finalize());
		
		foreach($userslist as $roomuser){
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("UpdateUserInformation"));
			$packet->WriteInt32($roomuser->habbo->id);
			$packet->WriteString($roomuser->habbo->look);
			$packet->WriteString(strtolower($roomuser->habbo->gender));
			$packet->WriteString($roomuser->habbo->motto);
			$packet->WriteInt32(0);
			$user->Send($packet->Finalize());
		}
		
		$user->InRoom = true;
		$user->LoadingRoom = 0;
		$user->LoadingChecksPassed = false;
	}
}
new RoomEvents;
?>