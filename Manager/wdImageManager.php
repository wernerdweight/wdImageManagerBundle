<?php

namespace WernerDweight\Bundle\ImageManagerBundle\Manager;

use WernerDweight\Bundle\ImageManagerBundle\Image\wdImage as wdImage;

Class wdImageManager{
	private $image;

	public function __construct($path = null){
		if($path) $this->loadImage($path);
	}

	public function loadImage($path){
		try {
			$this->image = new wdImage($path);	
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function saveImage($path,$name,$ext,$quality = 100){
		try {
			$this->image->save($path,$name,$ext,$quality);	
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function resize($width,$height,$crop = false){
		if($this->image->getEncrypted()){
			$this->decrypt();
			$encrypt = true;
		}
		else $encrypt = false;

		$dimensions = $this->adjustDimenstions($width,$height,$crop);
		$tmp = new wdImage(null,$this->image->getExt());
		$tmp->create($dimensions);
		imagecopyresampled($tmp->getData(),$this->image->getData(),0,0,0,0,$dimensions['width'],$dimensions['height'],$this->image->getWidth(),$this->image->getHeight());
		$this->image->destroy();
		$this->image = $tmp;
		if($crop) $this->crop($width,$height);
		
		if($encrypt){
			$this->encrypt();
		}
	}

	public function crop($width,$height){
		if($this->image->getEncrypted()){
			$this->decrypt();
			$encrypt = true;
		}
		else $encrypt = false;

		$crop = $this->adjustCrop($width,$height);
		$centerX = ($this->image->getWidth()/2) - ($crop['width']/2);
		$centerY = ($this->image->getHeight()/2) - ($crop['height']/2);
		$tmp = new wdImage(null,$this->image->getExt());
		$tmp->create(array('width' => $width,'height' => $height));
		imagecopyresampled($tmp->getData(),$this->image->getData(),0,0,$centerX,$centerY,$width,$height,$crop['width'],$crop['height']);
		$this->image->destroy();
		$this->image = $tmp;

		if($encrypt){
			$this->encrypt();
		}
	}

	public function encrypt(){
		$this->image->encrypt();
	}

	public function decrypt(){
		$this->image->decrypt();
	}

	private function adjustCrop($width,$height){
		$w = $this->image->getWidth()/$width;
		$h = $this->image->getHeight()/$height;
		if($w < 1 or $h < 1){
			if($w < $h) return array('width' => ($width * $w),'height' => ($height * $w));
			else return array('width' => ($width * $h),'height' => ($height * $h));
		}
		else return array('width' => $width,'height' => $height);
	}

	private function adjustDimenstions($width,$height,$crop = false){
		if($this->image->getWidth() > $this->image->getHeight()){		/// current is wide
			if($crop) return array('width' => $this->getWidth($height),'height' => $height);	/// upscale prevention
			else return array('width' => $width,'height' => $this->getHeight($width));
		}
		else if($this->image->getHeight() > $this->image->getWidth()){	/// current is tall
			if($crop) return array('width' => $width,'height' => $this->getHeight($width));		/// upscale prevention
			else return array('width' => $this->getWidth($height),'height' => $height);
		}
		else{									/// current is square
			if($width > $height){				/// new is wide
				if($crop) return array('width' => $this->getWidth($height),'height' => $height);	/// upscale prevention
				return array('width' => $width,'height' => $this->getHeight($width));
			}
			else if($height > $width){			/// new is tall
				if($crop) return array('width' => $width,'height' => $this->getHeight($width));		/// upscale prevention
				else return array('width' => $this->getWidth($height),'height' => $height);
			}
			else{								/// new is square
				return array('width' => $width,'height' => $height);
			}
		}
	}

	private function getWidth($height){
		return $height * ($this->image->getWidth() / $this->image->getHeight());
	}

	private function getHeight($width){
		return $width * ($this->image->getHeight() / $this->image->getWidth());
	}
}