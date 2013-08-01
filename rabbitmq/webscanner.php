<?php

class WebScanner extends Web {	
	protected function getPageElements($tagName, $attributeName) {
		$elements = $this->getXPath()->evaluate('/html/body//'.$tagName);
 
 		$elementsList = array();
 		for ($i = 0; $i < $elements->length; $i++) {
			$elementsList[] = $elements->item($i)->getAttribute($attributeName);
		}

		return $elementsList;
	}

	public function getAllLinks() {
		return $this->getPageElements('a', 'href');
	}

	public function getAllImages() {
		return $this->getPageElements('img', 'src');
	}
}