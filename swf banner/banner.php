<?php
/*
 * BloonPHP
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonPHP Git Community.
 *
 * RaGEZONE @BurakDev
 *
 * Based on capostrike banner
 * 
 * https://github.com/BurakDev/BloonPHP
 */

Class Banner{
	var $width;
	var $height;
	var $pixels;
	var $token;
	var $prime;
	var $generator;
	public function __construct(){
		$this->width = 100;
		$this->height = 114;
	}
	public function LoadPixels($filename){
		$this->pixels = file_get_contents($filename);
	}
	public function SetToken($token){
		$this->token = $token;
	}
	public function LoadDHfromServer($host, $port){
		if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
			$host = gethostbyname($host);
		}
		
		$fp = fsockopen($host, $port, $errno, $errstr, 1);
		
		if (!is_resource($fp)) return false;
		
		$packet = 'GET'.chr(1).$this->token;
		
		fwrite($fp, $packet);
		fflush($fp);
		stream_set_timeout($fp, 1);
		
		$data = fgets($fp, 512);
		list($this->prime, $this->generator) = explode(':', $data);
		
		fclose($fp);
	}
	public function LoadDHfromStatic($prime, $generator){
		$this->prime = $prime;
		$this->generator = $generator;
	}
	public function Calculate(){
		$insert = chr(strlen($this->prime)).$this->prime.chr(strlen($this->generator)).$this->generator;
		
		$Length = strlen($token);
		$Length2 = strlen($insert);
		$p = 0;
		$bitsnum = "";
		
		for($i=0;$i<$Length2;$i++){
			$bits = base_convert(ord($insert[$i]) ^ ord($token[$p]),10,2);
			$need = 8 - strlen($bits);
			for($o=0;$o<$need;$o++)$bits = "0".$bits;
			$bitsnum .= $bits;
			if (++$p == $Length) $p = 0;
		}
		
		$insertpos = 0;
		$Length = strlen($bitsnum);
		for ($y = 39; $y < 69; $y++)
		{
			$a = 0;
			for ($r = 4; $r < 84; $r++)
			{
				$pos = (($y + $a) * $width + $r) * 4;
				$b = 1;
				while ($b < 4)
				{
					if($insertpos < $Length)
					{
						$binaryData = base_convert(ord($this->pixels[$pos + $b]),10,2);
						$need = 8 - strlen($binaryData);
						for($o=0;$o<$need;$o++) $binaryData = "0".$binaryData;
						$binaryData[7] = $bitsnum[$insertpos];
						$this->pixels[$pos + $b] = chr(base_convert($binaryData,2,10));
						$insertpos++;$b++;
						continue;
					}
					break 3;
				}
				if ($r % 2 == 0) $a++;
			}
		}
	}
	public function Build(){
		$img=imagecreatetruecolor($this->width, $this->height);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		$x=0;
		$y=0;
		$colors=unpack("N*", $this->pixels);
		foreach($colors as $color)
		{
			imagesetpixel($img, $x, $y, (0x7f-($color>>25)<<24)|($color&0xffffff));
			if(++$x==$width)
			{$x=0;$y++;}
		}
		header('Content-Type: image/png');
		imagepng($img);
	}
}

$banner = new Banner;
$banner->LoadPixels('banner.txt');

if(isset($_GET['token']) && strlen($_GET['token']) == 40){
	$banner->SetToken(trim($_GET['token']));
	$banner->LoadDHfromServer("127.0.0.1", 300); // arg1 = Emulator IP, arg2 = Emulator PORT
	$banner->Calculate();
}
$banner->Build();
?>