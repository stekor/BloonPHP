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

Class PacketConstructor{
	var $packet;
	public function SetHeader($header){
		if(is_numeric($header)){
			$this->packet = HabboEncoding::EncodeBit16($header);
			return true;
		}else{
			return false;
		}
	}
	public function WriteInt16($int){
		if(is_numeric($int)){
			$this->packet .= HabboEncoding::EncodeBit16($int);
			return true;
		}else{
			return false;
		}
	}
	public function WriteInt32($int){
		if(is_numeric($int)){
			$this->packet .= HabboEncoding::EncodeBit32($int);
			return true;
		}else{
			return false;
		}
	}
	public function WriteString($string){
		$this->packet .= HabboEncoding::EncodeString($string);
		return true;
	}
	public function WriteBoolean($bool){
		$this->packet .= HabboEncoding::EncodeBoolean($bool);
	}
	public function Finalize(){
		$this->packet = HabboEncoding::EncodeBit32(strlen($this->packet)).$this->packet;
		return $this->packet;
	}
}
?>