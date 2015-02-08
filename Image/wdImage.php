<?php

namespace WernerDweight\ImageManagerBundle\Image;

Class wdImage{
	private $width;
	private $height;
	private $ext;
	private $data;
	private $secret;
	private $encrypted;

	public function __construct($path = null,$ext = null){
		$this->secret = mhash(MHASH_MD5,'I did not want to tell you, but this is not secret at all (change this)!');
		if($path) $this->load($path);
		if($ext) $this->ext = $ext;
	}

	public function create($dimensions){
		$this->width = $dimensions['width'];
		$this->height = $dimensions['height'];
		$this->data = imagecreatetruecolor($this->width,$this->height);
		if($this->ext == 'png') $this->setTransparency(false,true);
		if($this->ext == 'gif') $this->setTransparency();
		$this->encrypted = false;
	}

	private function imagecreatefromjpeg($path){
		$this->data = imagecreatefromjpeg($path);
		$this->encrypted = false;
	}

	private function imagecreatefrompng($path){
		$this->data = imagecreatefrompng($path);
		$this->encrypted = false;
	}

	private function imagecreatefromgif($path){
		$this->data = imagecreatefromgif($path);
		$this->encrypted = false;
	}

	private function imagecreatefromwdImage($path){
		$this->data = file_get_contents($path);
		$this->encrypted = true;
	}

	public function load($path){
		$this->ext = strtolower(substr(strrchr($path,'.'),1));

		try {
			$func = 'imagecreatefrom'.$this->getType($this->ext);
			$this->$func($path);
		} catch (\Exception $e) {
			throw $e;
		}

		if(!$this->encrypted) $this->getDimensions();
	}

	private function getDimensions(){
		$this->width = imagesx($this->data);
		$this->height = imagesy($this->data);
	}

	public function save($path,$name,$ext,$quality){
		if(!is_dir($path)) {
			mkdir($path,0777,true);
		}
		if($ext == null) $ext = $this->ext;
		if($this->encrypted){
			file_put_contents($path.$name.'.wdImage',$this->data);
			return true;
		}
		else if(imagetypes()){
			switch ($ext) {
				case 'gif':
					if(IMG_GIF) imagegif($this->data,$path.$name.'.'.$ext);
					break;
				case 'jpeg':
				case 'jpg':
					if(IMG_JPG) imagejpeg($this->data,$path.$name.'.'.$ext,$quality);
					break;
				case 'png':
					if(IMG_PNG) imagepng($this->data,$path.$name.'.'.$ext,round(9 - ((9*$quality)/100)));
					break;
				default:
					throw new \Exception("Unsupported file type", 1);
			}
		}
	}

	private function getType($ext){
		switch ($ext) {
			case 'jpg':
			case 'jpeg':
				return 'jpeg';
				break;
			case 'png':
				return 'png';
				break;
			case 'gif':
				return 'gif';
				break;
			case 'wdimage':
				return 'wdImage';
				break;
			default:
				throw new \Exception("Unsupported file type", 1);
				break;
		}
	}

	public function encrypt(){
		if($this->encrypted) throw new \Exception("Can't encrypt encrypted image", 1);

		/// use buffer to get image content
		ob_start();
		imagejpeg($this->data,NULL,100);
		$this->data =  ob_get_contents();
		ob_end_clean();

		$this->data = rtrim(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_128,
					$this->secret, $this->data,
					MCRYPT_MODE_ECB,
					mcrypt_create_iv(
						mcrypt_get_iv_size(
							MCRYPT_RIJNDAEL_128,
							MCRYPT_MODE_ECB
						),
						MCRYPT_RAND
					)
			), "\0"
		);

		$this->encrypted = true;
	}

	public function decrypt(){
		if(!$this->encrypted) throw new \Exception("Can't decrypt unencrypted image", 1);

		$this->data = rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$this->secret,
				$this->data,
				MCRYPT_MODE_ECB,
				mcrypt_create_iv(
					mcrypt_get_iv_size(
						MCRYPT_RIJNDAEL_128,
						MCRYPT_MODE_ECB
					),
					MCRYPT_RAND
				)
			), "\0"
		);
		$this->encrypted = false;

		$this->data = imagecreatefromstring($this->data);

		if(!$this->width or !$this->height) $this->getDimensions();
	}

	public function getEncrypted(){
		return $this->encrypted;
	}

	public function destroy(){
		imagedestroy($this->data);
	}

	public function getData(){
		return $this->data;
	}

	public function setData($data){
		$this->data = $data;
	}

	public function getWidth(){
		return $this->width;
	}

	public function getHeight(){
		return $this->height;
	}

	public function getExt(){
		return $this->ext;
	}

	private function setTransparency($alphaBlending = null,$saveAlpha = null){
		imagecolortransparent($this->data, imagecolorallocate($this->data, 0, 0, 0));
		if($alphaBlending !== null) imagealphablending($this->data, $alphaBlending);
		if($saveAlpha !== null) imagesavealpha($this->data, $saveAlpha);
	}

}
