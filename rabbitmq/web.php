<?php

abstract class Web {
	protected $url;
	protected $xPath = null;
	protected $dom = null;

	public function __construct($url) {
		$this->url = $url;
	}

	protected function getDom() {
		if ($this->dom == null) {
			$dom = new DOMDocument();
			$dom->loadHTML(file_get_contents($this->url));	
		}
		return $dom;
	}

	protected function getXPath() {
		if ($this->xPath == null) {
			$this->xPath = new DOMXPath($this->getDom());
		}

		return $this->xPath;
	}
}