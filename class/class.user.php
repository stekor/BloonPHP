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

Class User{
	var $id;
	var $socket;
	var $ip;
	var $port;
	var $rc4initialized;
	var $rc4;
	var $DH;
	var $Prime;
	var $Generator;
	var $habbo;
	var $habbo_stats;
	var $LoadingRoom;
	var $LoadingChecksPassed;
	var $InRoom;
	var $CurrentRoom;
	var $x;
	var $y;
	var $z;
	var $rot;
	var $lastPing;
	public function Ping(){
		global $headermanager;
		if(time()-$this->lastPing > 30){
			$this->lastPing = time();
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("Ping"));
			$this->Send($packet->Finalize());
			Console::WriteLine("- Sended ping to ".$this->habbo->username);
		}
	}
	public function SendNotif($message, $link=""){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("SendNotif"));
		$packet->WriteString($message);
		$packet->WriteString($link);
		$this->Send($packet->Finalize());
	}
	public function UserTyping($typing){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("UserTypingMessageComposer"));
		$packet->WriteInt32($this->habbo->id);
		$packet->WriteInt32($typing ? 1 : 0);
		RoomManager::SendToAllRoom($this->CurrentRoom, $packet->Finalize());
	}
	public function UpdateActivityPointsBalance(){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("ActivityPoints"));
		$packet->WriteInt32(3);
		$packet->WriteInt32(0);
		$packet->WriteInt32($this->habbo->activity_points);
		$packet->WriteInt32(1);
		$packet->WriteInt32($this->habbo->activity_points);
		$packet->WriteInt32(2);
		$packet->WriteInt32($this->habbo->activity_points);
		$this->Send($packet->Finalize());
	}
	public function UpdateCreditsBalance(){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("CreditsBalance"));
		$packet->WriteString($this->habbo->credits .".0");
		$this->Send($packet->Finalize());
	}
	public function LeaveRoom($disconnected=false){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("UserLeftRoom"));
		$packet->WriteString($this->habbo->id);
		
		RoomManager::SendToAllRoomWithoutOwner($this->CurrentRoom, $packet->Finalize(), $this->habbo->id);
		$this->InRoom = false;
		$this->CurrentRoom = 0;
		
		/*if(!$disconnected){
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("OutOfRoom"));
			$this->Send($packet->Finalize());
		}*/
	}
	public function UpdateState($x,$y,$addin=""){
		global $headermanager;
		$this->x = $x;
		$this->y = $y;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("UpdateState"));
		$packet->WriteInt32(1);
		$packet->WriteInt32($this->habbo->id);
		$packet->WriteInt32($this->x);
		$packet->WriteInt32($this->y);
		$packet->WriteString($this->z);
		$packet->WriteInt32($this->rot);
		$packet->WriteInt32($this->rot);
		$packet->WriteString("/flatcrtl 4 useradmin/".$addin);
		RoomManager::SendToAllRoom($this->CurrentRoom, $packet->Finalize());
	}
	public function LoadFriends(){
		global $headermanager;
		$friends = DB::Query("SELECT u.id,u.username,u.look,u.online,u.motto FROM messenger_friendships m, users u WHERE m.user_one_id = ? AND u.id = m.user_two_id ORDER BY online DESC",array($this->habbo->id));

		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("InitFriends"));
		$packet->WriteInt32(2000);
		$packet->WriteInt32(300);
		$packet->WriteInt32(800);
		$packet->WriteInt32(1100);
		$packet->WriteInt32(0);
		$packet->WriteInt32(count($friends));
		foreach($friends as $friend){
			$packet->WriteInt32($friend->id);
			$packet->WriteString($friend->username);
			$packet->WriteInt32(1);
			
			if($friend->online == 1){
				$packet->WriteBoolean(true); //is online ?
			}else{
				$packet->WriteBoolean(false);
			}
			
			// $packet->WriteBoolean(false); // temp
			
			// $packet->WriteBoolean(false); // in room ?
			
			// if($friend->online == 1){
				$packet->WriteString($friend->look);
			// }else{
				// $packet->WriteString("");
			// }
			
			$packet->WriteInt32(0);
			$packet->WriteString($friend->motto);
			$packet->WriteString(""); //Facebook username
			$packet->WriteString("");
			$packet->WriteBoolean(true); // offline message ?
			$packet->WriteBoolean(false);
			$packet->WriteBoolean(false);
			$packet->WriteInt16(0);
		}
		$this->Send($packet->Finalize());
		$this->LoadFriendRequests();
	}
	public function LoadFriendRequests(){
		global $headermanager;
		$packet = new PacketConstructor;
		$packet->SetHeader($headermanager->Outgoing("InitRequests"));
		$packet->WriteInt32(0); //count
		$packet->WriteInt32(0); //count
		$this->Send($packet->Finalize());
	}
	public function Send($packet){
		Network::Send($this->socket, $packet);
	}
}
?>