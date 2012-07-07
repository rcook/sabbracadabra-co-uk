<?php

/**
 * Project:     GOTCHA!: the PHP implementation of captcha.
 * File:        gotcha.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please write to: sol2ray at gmail dot com
 *
 * @link http://phpbtree.com/captcha/
 * @copyright 2003-2005 Smart Friend Network, Inc.
 * @author Sol Toure <sol2ray at gmail dot com>
 * @version alpha 0.02;
 */

class Image{
	var $type;
	var $width;
	var $height;
	var $backgroundColor;
	function Image($type, $width, $height){
		$this->type = $type;
		$this->width = $width;
		$this->height = $height;
	}
	function create(){
		$this->handle = @imagecreate($this->width, $this->height);
		@imagecolorallocate($this->handle, rand(0, 255), rand(0, 255), rand(0, 255));
		//@imagecolorallocate($this->handle, 255, 255, 255);
		return $this->handle;
	}
}

class GotchaImage extends Image{
	var $handle;
	var $fonts;
	var $bg;
	function GotchaImage($type, $width, $height){
		$this->handle = NULL;
		$this->fonts = array();
		$this->Image($type, $width, $height);
		
	}

	function apply($effect){
		$effect->apply($this);
	}
	function render($quality = 100){
		header("Content-type: image/$this->type");
		@imageinterlace($this->handle, 1);
		@imagepng($this->handle);
		@imagedestroy($this->handle);
	}
}


class GotchaPng extends GotchaImage{
	function GotchaPng($width, $height){
		$this->GotchaImage('PNG', $width, $height);
	}
	function createFrom($src){
		return ($this->handle = @imagecreatefrompng($src));
	}
	function render(){
		header("Content-type: image/$this->type");
		@imageinterlace($this->handle, 1);
		@imagepng($this->handle);
		@imagedestroy($this->handle);
	}
}
class GotchaJpeg extends GotchaImage{
	function GotchaJpeg( $width, $height){
		$this->GotchaImage('JPG', $width, $height);
	}
	function createFrom($src){
		return ($this->handle = @imagecreatefromjpeg($src));
	}
	function render($quality = 100){
		header("Content-type: image/$this->type");
		@imageinterlace($this->handle, 1);
		@imagejpeg($this->handle, NULL, $quality);
		@imagedestroy($this->handle);
	}
}
class GotchaGif extends GotchaImage{
	function GotchaGif($width, $height){
		$this->GotchaImage('GIF', $width, $height);
	}
	function createFrom($src){
		return ($this->handle = @imagecreatefromgif($src));
	}
	function render(){
		header("Content-type: image/$this->type");
		@imageinterlace($this->handle, 1);
		@imagegif($this->handle);
		@imagedestroy($this->handle);
	}
}









class Effect{
	function apply($image){
		die('---');
	}
}

class GridEffect extends Effect{
	function GridEffect($size){
		$this->size = $size; //rand($size, 10);
	}
	function apply($image){
		for($i = 0, $x = 0, $z = $image->width; $i < $image->width; $i++, $z -= $this->size, $x += $this->size){
			@imageline($image->handle, $x, 0, $x+10, $image->height, $image->backgroundColor);
			@imageline($image->handle, $z, 0, $z-10, $image->height, $image->backgroundColor);
		} 
	}
}



class DotEffect extends Effect{
	function apply($image){
		for($i = 0; $i < $image->width; $i++){
			imagesetpixel ( $image->handle, rand(0, $image->width), rand(0, $image->height), @imagecolorallocate($image->handle, rand(0, 255), rand(0, 255), rand(0, 255)) );
		}
	}
}

class GradientEffect extends Effect{
	function apply($image){
	//	for($i = 0, $rd = rand(0, 100), $gr = rand(0, 100), $bl= rand(0, 100); $i <= $image->height; $i++){
		for($i = 0, $rd = 0xcc, $gr = 0xcc, $bl= 0xcc; $i <= $image->height; $i++){
			$g = @imagecolorallocate($image->handle, $rd+=2, $gr+=2, $bl+=2);
			@imageline($image->handle, 0, $i, $image->width, $i, $g);
		}
		$image->backgroundColor = $g;
	}
}

class TextEffect extends Effect{
	var $text;
	var $size;
	var $depth;
	var $fonts;
	function TextEffect($text, $size, $depth=5){
		$this->text = $text;
		$this->size = $size;
		$this->depth = $depth;
		$this->fonts = array();
	}
	
	function addFont($path){
		if(file_exists($path)){
			$this->fonts[] = realpath($path);
		}
	}
	
	function apply($image){
		$c = @imagecolorallocate($image->handle, rand(0, 255), rand(0, 255), rand(0, 255));
		$c2 = @imagecolorallocate($image->handle, 0, 0, 0);
		$c3 = @imagecolorallocate($image->handle, 0xff,0xff, 0xff);
		$width = $image->width;
		$height = $image->height;
		$text = strtoupper($this->text);
		$charCount = count($this->fonts);

		if($charCount > 0){
			for($i = 0, $strlen = strlen($this->text), $p = floor(abs((($width-($this->size*$strlen))/2)-floor($this->size/2))); $i < $strlen; $i++, $p +=$this->size){
				$f = $this->fonts[rand(0, $charCount-1)];
				$d = rand(-8, 8);
				$y = rand(floor($height/4) + floor($this->size/2), $height - floor($this->size/2));
				for($b = 0; $b <= $this->depth; $b++){
				  
					imagettftext($image->handle, $this->size, $d, $p++, $y++, $c3, $f, $this->text{$i});
				}
				//print $p;
				@imagettftext($image->handle, $this->size, $d, $p, $y, $c2, $f, $this->text{$i});
			}
		}
		else{
			imagestring ($image->handle, $this->size, floor(abs(((($width/2)-($this->size*strlen($this->text)))/2))), floor(($height/2)-($this->size/2)), $this->text, $c2 );
		
		}
	}

}

class Tagline extends Effect{
	var $text;
	var $size;
	var $depth;
	var $fonts;
	function TextEffect($text, $size, $depth=5){
		$this->text = $text;
		$this->size = $size;
		$this->depth = $depth;
		$this->fonts = array();
	}
	
	function addFont($path){
		if(file_exists($path)){
			$this->fonts[] = realpath($path);
		}
	}
	
	function apply($image){
		/*$c = @imagecolorallocate($image->handle, rand(0, 255), rand(0, 255), rand(0, 255));
		$c2 = @imagecolorallocate($image->handle, 0, 0, 0);
		$c3 = @imagecolorallocate($image->handle, 0xff,0xff, 0xff);
		$width = $image->width;
		$height = $image->height;
		$text = strtoupper($this->text);
		$charCount = count($this->fonts);
*/
       $f = $this->fonts[0];
       $c = @imagecolorallocate($image->handle, 0x00,0x00, 0x00);
        imagettftext($image->handle, $this->size, 1, 0, 0, $c, $f, $this->text);
		/*
		if($charCount > 0){
			for($i = 0, $strlen = strlen($this->text), $p = floor(abs((($width-($this->size*$strlen))/2)-floor($this->size/2))); $i < $strlen; $i++, $p +=$this->size){
				$f = $this->fonts[rand(0, $charCount-1)];
				$d = rand(-8, 8);
				$y = rand(floor($height/2)+floor($this->size/2), $height-floor($this->size/2));
				for($b = 0; $b <= $this->depth; $b++){
				  
					imagettftext($image->handle, $this->size, $d, $p++, $y++, $c3, $f, $this->text{$i});
				}
				@imagettftext($image->handle, $this->size, $d, $p, $y, $c2, $f, $this->text{$i});
			}
		}
		else{
			imagestring ($image->handle, $this->size, floor(abs(((($width/2)-($this->size*strlen($this->text)))/2))), floor(($height/2)-($this->size/2)), $this->text, $c2 );
		
		}
		*/
	}

}
?>