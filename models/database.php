<?php



final class database{

	public $link;
	private static $instance = null;
	private function __construct(){
		$this->link = new PDO('mysql:dbname=tracker;host=127.0.0.1','root','mariadb');
	}


	public static function __getDB(){
		if(null == static::$instance){
			static::$instance = new static();
		}
		return static::$instance;
	}
}
?>