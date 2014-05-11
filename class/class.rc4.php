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

Class RC4{
	var $key;
	var $i;
	var $j;
	var $table;
	public function __construct(){
		$this->i = 0;
		$this->j = 0;
		$this->table = Array();
	}
	public function Init($key){
		$this->key = $key;
		$k =  count($this->key);
		
		while($this->i < 256){
			$this->table[$this->i] = $this->i;
			$this->i++;
		}
		$this->i = 0;
		$this->j = 0;
		while($this->i < 256){
			$this->j = ((($this->j + $this->table[$this->i]) + $this->key[($this->i % $k)])% 256);
			$this->Swap($this->i, $this->j);
			$this->i++;
		}
		$this->i = 0;
		$this->j = 0;
	}
	private function Swap($a, $b){
		$k = $this->table[$a];
		$this->table[$a] = $this->table[$b];
		$this->table[$b] = $k;
	}
	public function Parse($bytes){
		$k = 0;
		$result = "";
		for($a = 0; $a < strlen($bytes); $a++){
			$this->i = (($this->i + 1) % 256);
			$this->j = (($this->j + $this->table[$this->i]) % 256);
			$this->Swap($this->i, $this->j);
			$result .= chr(ord($bytes[$a]) ^ $this->table[($this->table[$this->i] + $this->table[$this->j]) % 256]);
		}
		return $result;
	}
}
?>