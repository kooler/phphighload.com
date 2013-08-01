<?php 

class Storage {
	protected static $instance = null;
	protected $mysqli;
	protected static $PAGES_TABLE = 'pages';
	protected static $IMAGES_TABLE = 'images';

    public static function getInstance() {
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct() {
    	$this->mysqli = new mysqli('127.0.0.1', 'root', '', 'blog_test');
		$this->mysqli->set_charset('utf8');
    }

    protected function containsUrl($tableName, $url) {
    	return ($this->mysqli->query("SELECT COUNT(*) as cnt FROM ".$tableName." WHERE url='".$url."'")->fetch_object()->cnt > 0);
    }

    public function containsPage($url) {
    	return $this->containsUrl(static::$PAGES_TABLE, $url);
    }

    public function containsImage($url) {
    	return $this->containsUrl(static::$IMAGES_TABLE, $url);
    }

    public function addPage($title, $url) {
    	$this->mysqli->query("INSERT INTO ".static::$PAGES_TABLE." (title,url)VALUES('".$title."','".$url."')");
    }

    public function addImage($url) {
    	$this->mysqli->query("INSERT INTO ".static::$IMAGES_TABLE." (url)VALUES('".$url."')");
    }
}