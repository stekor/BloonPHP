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

Class DiffieHellman{
	var $Prime;
	var $Generator;
	var $PrivateKey;
	var $PublicKey;
	var $PublicClientKey;
	var $SharedKey;
	public function GenerateDH(){
		$this->Prime = gmp_nextprime(gmp_init(Util::GenerateRandomHexString(32), 16));
		$this->Generator = gmp_nextprime(gmp_init(Util::GenerateRandomHexString(30), 16));
		$this->PrivateKey = gmp_init(Util::GenerateRandomHexString(30), 16);
		
		if(gmp_cmp($this->Generator, $this->Prime) == 1){
			$temp = $this->Prime;
			$this->Prime = $this->Generator;
			$this->Generator = $temp;
		}
		
		$this->PublicKey = gmp_powm($this->Generator, $this->PrivateKey, $this->Prime);
	}
	public function InitDH($prime, $generator, $base=10){
		$this->Prime = gmp_init($prime,$base);
		$this->Generator = gmp_init($generator,$base);
		$this->PrivateKey = gmp_init(Util::GenerateRandomHexString(30), 16);
		
		if(gmp_cmp($this->Generator, $this->Prime) == 1){
			$temp = $this->Prime;
			$this->Prime = $this->Generator;
			$this->Generator = $temp;
		}
		
		$this->PublicKey = gmp_powm($this->Generator, $this->PrivateKey, $this->Prime);
	}
	public function GenerateSharedKey($clientkey, $base=10){
		$this->PublicClientKey = gmp_init($clientkey, $base);
		$this->SharedKey = gmp_powm($this->PublicClientKey, $this->PrivateKey, $this->Prime);
	}
	public function GetPublicKey($bytearray=false){
		if($bytearray)	return Util::toByteArray($this->PublicKey);
		return gmp_strval($this->PublicKey);
	}
	public function GetSharedKey($bytearray=false){
		if($bytearray)	return Util::toByteArray($this->SharedKey);
		return gmp_strval($this->SharedKey);
	}
	public function GetPrime($bytearray=false){
		if($bytearray)	return Util::toByteArray($this->Prime);
		return gmp_strval($this->Prime);
	}
	public function GetGenerator($bytearray=false){
		if($bytearray)	return Util::toByteArray($this->Generator);
		return gmp_strval($this->Generator);
	}
}
?>