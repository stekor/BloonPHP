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

Class RSA{
	var $n;
	var $e;
	var $d;
	var $p;
	var $q;
	var $dmp1;
	var $dmq1;
	var $coeff;
	var $canDecrypt;
	var $canEncrypt;

	public function __construct(){
		$this->n = 0;
		$this->e = 0;
		$this->d = 0;
		$this->p = 0;
		$this->q = 0;
		$this->dmp1 = 0;
		$this->dmq1 = 0;
		$this->coeff = 0;
		$this->canDecrypt = false;
		$this->canEncrypt = false;
	}
	
	public function SetPublic($n, $e){
		$n = str_replace("\r", "", $n);
		$n = str_replace("\n", "", $n);
		$e = str_replace("\r", "", $e);
		$e = str_replace("\n", "", $e);
		
		$this->n = gmp_init($n, 16);
		$this->e = gmp_init($n, 16);
		
		$this->canEncrypt = true;
	}
	public function SetPrivate($n, $e, $d){
		$n = str_replace("\r", "", $n);
		$n = str_replace("\n", "", $n);
		$e = str_replace("\r", "", $e);
		$e = str_replace("\n", "", $e);
		$d = str_replace("\r", "", $d);
		$d = str_replace("\n", "", $d);
		
		$this->n = gmp_init($n, 16);
		$this->e = gmp_init($e, 16);
		$this->d = gmp_init($d, 16);
		$this->canEncrypt = true;
		$this->canDecrypt = true;
	}
	private function GetBlockSize(){
		return floor((strlen(gmp_strval($this->n,16)))/2);
	}
	private function DoPrivate($x){
		if($this->canDecrypt){
			return gmp_powm($x, $this->d, $this->n);
		}
		return 0;
	}
	private function pkcs1unpad2($d, $n){
		$b = Util::toByteArray($d);
		$i = 0;
		
		while($i < count($b) && $b[$i] == 0) ++$i;
		if(count($b)-$i != $n-1 || $b[$i] != 2)	return null;
		++$i;
		while($b[$i] != 0){
			if(++$i >= count($b)) return null;
		}
		$result = "";
		while(++$i < count($b)){
			$c = $b[$i] & 255;
			if($c < 128){
				$result.= chr($c);
			}else if(($c > 191) && ($c < 224)){
				$result.= chr((($c & 31) << 6) | ($b[$i+1] & 63));
				++$i;
			}else{
				$result.= chr((($c & 15) << 12) | (($b[$i+1] & 63) << 6) | ($b[$i+2] & 63));
				$i += 2;
			}
		}
		return $result;
	}
	public function Decrypt($bytes){
		$c = gmp_init($bytes, 16);
		$m = $this->DoPrivate($c);
		return $this->pkcs1unpad2($m, $this->GetBlockSize());
	}
}
?>