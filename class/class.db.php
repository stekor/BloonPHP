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

Class DB{
	public static function Query($query, $params=array()){
		$pool = Pooling::GetPool();
		$exec = $pool->prepare($query);
		$exec->execute($params);
		$result = array();
		if($exec->rowCount() > 0){
			while($query = $exec->fetch(PDO::FETCH_OBJ)){
				$result[] = $query;
			}
		}
		return $result;
	}
	public static function Exec($query, $params=array()){
		$pool = Pooling::GetPool();
		$exec = $pool->prepare($query);
		$exec->execute($params);
		return true;
	}
	public static function LastInsert($column, $table){
		$pool = Pooling::GetPool();
		$exec = $pool->prepare("SELECT LAST_INSERT_ID(".$column.") as lastinsert FROM ".$table." ORDER BY ".$column." DESC LIMIT 1;");
		$exec->execute();
		$result = $exec->fetch(PDO::FETCH_OBJ);
		return $result->lastinsert;
	}
}
?>