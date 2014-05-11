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

Class HeaderManager{
	var $Incoming;
	var $Outgoing;
	public function LoadHeader(){
		$filename = "Incoming.conf";
		if(file_exists($filename)){
			$file = file_get_contents($filename);
			$file = str_replace("\r", "", $file);
			$file = explode("\n", $file);
			$this->Incoming = array();
			foreach($file as $line => $data){
				if(preg_match("/ = /i", $data)){
					$exp = explode(" = ", $data);
					$this->Incoming[trim($exp[0])] = (int)$exp[1];
				}
			}
		}else{
			Console::WriteLine("Can't load ".$file.", is missing !");
			exit;
		}
		$filename = "Outgoing.conf";
		if(file_exists($filename)){
			$file = file_get_contents($filename);
			$file = str_replace("\r", "", $file);
			$file = explode("\n", $file);
			$this->Outgoing = array();
			foreach($file as $line => $data){
				if(preg_match("/ = /i", $data)){
					$exp = explode(" = ", $data);
					$this->Outgoing[trim($exp[0])] = (int)$exp[1];
				}
			}
		}else{
			Console::WriteLine("Can't load ".$file.", is missing !");
			exit;
		}
	}
	public function Incoming($name){
		if(is_numeric($this->Incoming[$name])) return $this->Incoming[$name];
		return 0;
	}
	public function Outgoing($name){
		if(is_numeric($this->Outgoing[$name])) return $this->Outgoing[$name];
		return 0;
	}
}
?>