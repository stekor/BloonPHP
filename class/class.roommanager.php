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

Class RoomManager{
	public static function GetRoom($id){
		global $rooms;
		if(isset($rooms[$id])){
			return $rooms[$id];
		}else{
			return self::LoadRoom($id);
		}
	}
	public static function LoadRoom($id){
		global $rooms;
		$rooms[$id] = array();
		$rooms[$id]["data"] = DB::Query("SELECT * FROM rooms WHERE id = ?", array($id))[0];
		$rooms[$id]["owner"] = DB::Query("SELECT id,username,look FROM users WHERE username = ?", array($rooms[$id]["data"]->owner))[0];
		// $rooms[$id]["flooritems"] = DB::Query("SELECT * FROM items WHERE wall_pos = ? AND room_id = ?",array("",$id));
		// $rooms[$id]["wallitems"] = DB::Query("SELECT * FROM items WHERE x = ? AND y = ? AND z = ? AND rot = ? AND room_id = ?",array(0,0,0,0,$id));
		$model = self::GetModel($rooms[$id]["data"]->model_name);
		$rooms[$id]["pathmap"] = self::PathMap($model->heightmap);
		$rooms[$id]["relativemap"] = self::RelativeMap($model->heightmap,array($model->door_x,$model->door_y,$model->door_z));
		return $rooms[$id];
	}
	public static function CreateRoom($name, $model,$username){
		DB::Exec("INSERT INTO rooms (caption,owner,model_name) VALUES (?,?,?)",array($name,$username,$model));
		$lastinsert = DB::LastInsert("id", "rooms");
		return self::GetRoom($lastinsert);
	}
	public static function RelativeMap($heightmap,$coord){
		//need cache
		$heightmap_step1 = explode(chr(0x0D), $heightmap);
		$heightmap_door = "";
		foreach($heightmap_step1 as $heightmapkey => $heightmapdata){
			if($heightmapkey != $coord[1]){
				$heightmap_door .= $heightmapdata.chr(0x0D);
			}else{
				$heightmap_split = str_split($heightmapdata);
				foreach($heightmap_split as $keysplit => $splitdata){
					if($keysplit != $coord[0]){
						$heightmap_door.= $splitdata;
					}else{
						$heightmap_door.= $coord[2];
					}
				}
				$heightmap_door = $heightmap_door.chr(0x0D);
			}
		}
	}
	public static function UnLoadRoom($id){
		global $rooms;
		unset($rooms[$id]);
		return true;
	}
	public static function RoomsLoaded(){
		global $rooms;
		return count($rooms);
	}
	public static function GetModel($name){
		global $roommodels;
		foreach($roommodels as $model){
			if($name == $model->id){
				return $model;
			}
		}
	}
	public static function GetUserByRoom($id){
		global $users;
		$array = array();
		foreach($users as $user){
			if($id == $user->CurrentRoom){
				$array[] = $user;
			}
		}
		return $array;
	}
	public static function SendToAllRoom($id, $packet){
		global $users;
		foreach($users as $user){
			if($id == $user->CurrentRoom){
				$user->Send($packet);
			}
		}
	}
	public static function SendToAllRoomWithoutOwner($id, $packet, $userid){
		global $users;
		foreach($users as $user){
			if($id == $user->CurrentRoom && $userid != $user->habbo->id){
				$user->Send($packet);
			}
		}
	}
	public static function PathMap($heightmap){
		$map=array();
		
		$split = explode(chr(0x0D), str_replace(chr(0x0A), null, $heightmap));
		$linelen = strlen($split[0]);
		foreach($split as $y => $line){
			if(strlen($line) != $linelen){
				continue;
			}
			
			$linesplit = str_split($line, 1);
			foreach($linesplit as $x => $tile){
				// if(strtolower($tile) == "x"){
					// $map[$x.'x'.$y]=array('weight'=>'3.0');
				// }else{
					$map[$x.'x'.$y]=array('weight'=>'1.0');
				// }
			}
		}
		
		/*for($x=1;$x<=100;$x++){
			for($y=1;$y<=100;$y++){
				// $rand=rand(1,4);
				// if($rand==1){
					// $map[$x.'x'.$y]=array('weight'=>'3.0');
				// } else {
					$map[$x.'x'.$y]=array('weight'=>'1.0');
				// }
			}
		}*/
		return $map;
	}
}
?>