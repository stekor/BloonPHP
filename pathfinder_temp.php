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

		$read = BufferManager::ReadInt32($buffer);
		$MoveX = $read["result"];
		$buffer = $read["buffer"];
		$MoveY = BufferManager::ReadInt32($buffer)["result"];
		
		if($MoveX == $user->x && $MoveY == $user->y){
			return;
		}
		if(isset($threadsender[$user->habbo->id])){
			foreach($threadsender[$user->habbo->id] as $keyp => $threadp){
				if($threadp->isRunning()){
					$threadp->stop();
					unset($threadsender[$user->habbo->id][$keyp]);
					$user->x = $threadp->x;
					$user->y = $threadp->y;
					// $user->z = $threadp->z;
					// return;
					usleep(100000);
				}else{
					unset($threadsender[$user->habbo->id][$keyp]);
				}
			}
		}
		
		$room = RoomManager::GetRoom($user->CurrentRoom);
		
		$origx = $user->x;
		$origy = $user->y;
		if($user->x > $MoveX && $user->y > $MoveY){
			$origx++;
			$origy++;
		}else if($user->x < $MoveX && $user->y < $MoveY){
			$origx--;
			$origy--;
		}else if($user->x > $MoveX && $user->y < $MoveY){
			$origx++;
			$origy--;
		}else if($user->x < $MoveX && $user->y > $MoveY){
			$origx--;
			$origy++;
		}else if($user->x > $MoveX){
			$origx++;
		}else if($user->x < $MoveX){
			$origx--;
		}else if($user->y < $MoveY){
			$origy--;
		}else if($user->y > $MoveY){
			$origy++;
		}
		if($origx <= 0){
			$origx = 1;
		}else if($origy <= 0){
			$origy = 1;
		}
		$packetarray = array();
		$pathfinder = new PathFinder();
		$pathfinder->setOrigin($origx, $origy);
		$pathfinder->setDestination($MoveX, $MoveY);
		$pathfinder->setMap($room["pathmap"]);
		$result = $pathfinder->returnPath();
		
		$xfthread = array();
		$yfthread = array();
		foreach($result as $coordkey => $coord):
			$split = explode("x", $coord);
			$xc = $split[0];
			$yc = $split[1];
			$addin = "";
			$needsleep = true;
			if(isset($result[($coordkey+1)])){
				$split = explode("x", $result[($coordkey+1)]);
				$xf = $split[0];
				$yf = $split[1];
				$zf = 0; // Z
				$addin = "mv ".$xf.",".$yf.",".$zf."//";
				$user->rot = Util::RotationCalculate(array($xc,$yc),array($xf,$yf));
				$xfthread[] = $xf;
				$yfthread[] = $yf;
			}else{
				$user->rot = Util::RotationCalculate(array($user->x,$user->y),array($xc,$yc));
				$needsleep = false;
				$xfthread[] = $xc;
				$yfthread[] = $yc;
			}
			$user->z = 0;
			
			// $user->UpdateState($xc, $yc,$addin);
			$user->x = $xc;
			$user->y = $yc;
			// $xfthread[] = $xc;
			// $yfthread[] = $yc;
			$packet = new PacketConstructor;
			$packet->SetHeader($headermanager->Outgoing("UpdateState"));
			$packet->WriteInt32(1);
			$packet->WriteInt32($user->habbo->id);
			$packet->WriteInt32($user->x);
			$packet->WriteInt32($user->y);
			$packet->WriteString($user->z);
			$packet->WriteInt32($user->rot);
			$packet->WriteInt32($user->rot);
			$packet->WriteString("/flatcrtl 4 useradmin/".$addin);
			
			$packetarray[] = $packet->Finalize();
			// RoomManager::SendToAllRoom($this->CurrentRoom, $packet->Finalize());
			
			// if($needsleep) usleep(500000);
		endforeach;
		
		$userslist = RoomManager::GetUserByRoom($user->CurrentRoom);
		$socketarray = array();
		foreach($userslist as $usersocket):
			$socketarray[] = $usersocket->socket;
		endforeach;
		if(!isset($threadsender[$user->habbo->id])){
			$threadsender[$user->habbo->id] = array();
			$tid = 0;
		}else{
			$tid = count($threadsender[$user->habbo->id]);
		}
		$threadsender[$user->habbo->id][$tid] = new PathfinderSender;
		$threadsender[$user->habbo->id][$tid]->SetData($packetarray,$socketarray,$xfthread,$yfthread);
		$threadsender[$user->habbo->id][$tid]->start();
		
		// Console::WriteLine("X : ".$MoveX.", Y : ".$MoveY);
		// $user->UpdateState($MoveX, $MoveY);
?>