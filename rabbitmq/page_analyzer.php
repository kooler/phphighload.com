<?php

class PageAnalyzer extends Web {
	public function analyze() {
		if (!Storage::getInstance()->containsPage($this->url)) {
			$title = $this->getDom()->getElementsByTagName("title")->item(0)->textContent;
			Storage::getInstance()->addPage($title, $this->url);
		}
	}
}