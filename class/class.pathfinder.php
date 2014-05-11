<?php
/*
 * BloonPHP
 * Habbo R63 Post-Shuffle
 * Based on the work of Burak, edited by BloonPHP Git Community.
 *
 * RaGEZONE @BurakDev
 * 
 * Pathfinder was found here : http://waher.net/archives/313
 * 
 * https://github.com/BurakDev/BloonPHP
 */

class PathFinder{
    
    var $map=array();
    var $origX=0;
    var $origY=0;
    var $destX=0;
    var $destY=0;
    var $path=array();
    var $weights=array();
    var $walked=array();
    var $steps=0;
    var $mapSquares=0;
    var $mapWidth=0;
    var $found=0;
    var $failSafe=0;
    var $diagonal=1;
    var $impassable=100.0;

    function __construct(){
    }
    
    function __destruct(){

    }
    
    function noDiagonalMovement(){
        $this->diagonal=0;
    }
    
    function setImpassable($impassable){
        $this->impassable=$impassable;
    }
    
    public function setMap($map){
        $this->map=$map;
        $this->mapSquares=count($this->map);
        $this->mapWidth=sqrt($this->mapSquares);
    }
    
    public function setOrigin($origX,$origY){
        $this->origX=$origX;
        $this->origY=$origY;
        
    }
    
    public function setDestination($destX,$destY){
        $this->destX=$destX;
        $this->destY=$destY;        
    }
    
    public function returnPath(){
        $this->path=array();
        $this->weights=array();
        $this->walked=array();
        $this->steps=0;
        $this->found=0;
        $this->failsafe=0;
        if($this->origX!=0 && $this->origY!=0 && $this->destX!=0 && $this->destY!=0 && !empty($this->map)){
            $this->findPath($this->origX,$this->origY);
            return $this->path;
        } else {
            // echo 'Insufficient data, please define origin and destination points and a map';
            // die();
			Console::WriteLine("WARNING !! PATHFINDER CRITICAL ERROR !");
        }
    }
    
    public function returnWeights(){
        $return=array();
        if(!empty($this->weights)){
            $return=$this->weights;
        }
        return $return;
    }

    private function findPath($curX,$curY){
        $this->failSafe++;
        if($this->failSafe>=100){
            return false;
        }
        $lowestEstimatedCost=0;
        $shortestX=0;
        $shortestY=0;
        for($x=-1;$x<=1;$x++){
            if($this->found==0){
                for($y=-1;$y<=1;$y++){
                    if($this->found==0){
                        $checkX=$x+$curX;
                        $checkY=$y+$curY;
                        if($checkX>=1 && $checkY>=1 && $checkX<=$this->mapWidth && $checkY<=$this->mapWidth){
                            if($checkX==$curX && $checkY==$curY){
                                //not needed
                            } else if($checkX==$this->destX && $checkY==$this->destY){
                                if($this->diagonal==1 || !(($x==-1 && $y==-1) || ($x==-1 && $y==1) || ($x==1 && $y==-1) || ($x==1 && $y==1))){
                                    $this->found=1;
                                    $this->steps++;
                                    $this->path[]=$this->destX.'x'.$this->destY;
                                    $this->origX=$this->destX;
                                    $this->origY=$this->destY;
                                    $this->weights[]=(($x==-1 && $y==-1) || ($x==-1 && $y==1) || ($x==1 && $y==-1) || ($x==1 && $y==1))?$this->map[$checkX.'x'.$checkY]['weight']*1.4:$this->map[$checkX.'x'.$checkY]['weight'];
                                }
                            } else {
                                if(!in_array($checkX.'x'.$checkY,$this->walked)){
                                    $weight=$this->map[$checkX.'x'.$checkY]['weight'];
                                    if($this->diagonal==1 || !(($x==-1 && $y==-1) || ($x==-1 && $y==1) || ($x==1 && $y==-1) || ($x==1 && $y==1)) && $weight<$this->impassable){
                                        $weight=(($x==-1 && $y==-1) || ($x==-1 && $y==1) || ($x==1 && $y==-1) || ($x==1 && $y==1))?$weight:$weight;
                                        $estimatedCost=$this->estimatedCost($checkX,$checkY,$weight);
                                        if($estimatedCost<$lowestEstimatedCost || $lowestEstimatedCost==0){
                                            $lowestEstimatedCost=$estimatedCost;
                                            $shortestX=$checkX;
                                            $shortestY=$checkY;
                                            $goodweight=$weight;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if($this->found!=1){
            if($shortestX==0 && $shortestY==0){
                $this->walked=array();
                $this->walked[]=$curX.'x'.$curY;
                $this->findPath($curX,$curY,$this->destX,$this->destY);
            } else {
                $this->steps++;
                $this->walked[]=$shortestX.'x'.$shortestY;
                $this->path[]=$shortestX.'x'.$shortestY;
                $this->origX=$shortestX;
                $this->origY=$shortestY;
                $this->weights[]=$goodweight;
                $this->findPath($shortestX,$shortestY,$this->destX,$this->destY);
            }
        }
    }
    private function estimatedCost($curX,$curY,$weight){
        $dx=$this->destX-$curX;
        $dy=$this->destY-$curY;
        $value=sqrt(($dx*$dx)+($dy*$dy));
        return $value*$weight;
    }

}

?>