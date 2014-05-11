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

Class RSAKey{
	var $n;
	var $e;
	var $d;
	public function __construct(){
		$this->n = "86851DD364D5C5CECE3C883171CC6DDC5760779B992482BD1E20DD296888DF91B33B936A7B93F06D29E8870F703A216257DEC7C81DE0058FEA4CC5116F75E6EFC4E9113513E45357DC3FD43D4EFAB5963EF178B78BD61E81A14C603B24C8BCCE0A12230B320045498EDC29282FF0603BC7B7DAE8FC1B05B52B2F301A9DC783B7";
		$this->e = "3";
		$this->d = "59AE13E243392E89DED305764BDD9E92E4EAFA67BB6DAC7E1415E8C645B0950BCCD26246FD0D4AF37145AF5FA026C0EC3A94853013EAAE5FF1888360F4F9449EE023762EC195DFF3F30CA0B08B8C947E3859877B5D7DCED5C8715C58B53740B84E11FBC71349A27C31745FCEFEEEA57CFF291099205E230E0C7C27E8E1C0512B";
	}
	public function Get($rsakey){
		Switch(strtolower($rsakey)){
			case "n":
				return $this->n;
			break;
			case "e":
				return $this->e;
			break;
			case "d":
				return $this->d;
			break;
			default:
				return false;
			break;
		}
	}
}
?>