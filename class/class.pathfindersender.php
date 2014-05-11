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

Class PathfinderSender extends Thread{
	public function SetData($packetRequests, $socketRequests, $x, $y){
		$this->packet = $packetRequests;
		$this->socketcount = count($socketRequests);
		foreach($socketRequests as $key => $value){
			eval('$this->socket'.$key.' = $value;');
		}
		$this->allow = true;
		$this->xa = $x;
		$this->ya = $y;
	}
	public function stop(){
		$this->allow = false;
	}
	public function run(){
		$cpt = 0;
		foreach($this->packet as $packet){
			if($this->allow){
				$this->x = $this->xa[$cpt];
				$this->y = $this->ya[$cpt];
				for($i = 0; $i < $this->socketcount; $i++):
					eval('$socket = $this->socket'.$i.';');
					socket_write($socket, $packet, strlen($packet));
				endfor;
				usleep(500000);
				$this->x = $this->xa[$cpt];
				$this->y = $this->ya[$cpt];
				$cpt++;
			}else{
				break;
			}
		}
	}
}
?>