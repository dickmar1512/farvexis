<?php
class Database {
	public static $db;
	public static $con;
	// Declara las propiedades
    private $host;
    private $user;
    private $pass;
    private $ddbb;
	
	//function Database(){
	function __construct() {
		$this->user="milenio";$this->pass="armagedon";$this->host="localhost";$this->ddbb="dbfarvexis";
	}

	function connect(){
		$con = new mysqli($this->host,$this->user,$this->pass,$this->ddbb);
		$con->query("set sql_mode=''");
		return $con;
	}

	public static function getCon(){
		if(self::$con==null && self::$db==null){
			self::$db = new Database();
			self::$con = self::$db->connect();
		}
		return self::$con;
	}
	
}
?>
