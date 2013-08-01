<?php

class ImageAnalyzer extends Web {
	protected static $ALLOWED_WIDTH = 10;
	protected static $ALLOWED_HEIGHT = 10;

	protected function isValid() {
		$size = getimagesize($this->url);
		return ($size[0] >= static::$ALLOWED_WIDTH && $size[1] >= static::$ALLOWED_HEIGHT);
	}

	public function analyze() {
		if (!Storage::getInstance()->containsImage($this->url)) {
			if ($this->isValid()) {
				Storage::getInstance()->addImage($this->url);	
			}
		}
	}	
}
