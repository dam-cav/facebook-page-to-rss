<?php
	class Database {
		private $_connection;
		private static $_instance;
		private $_host = "localhost";
		private $_username = "YOURUSERNAME";
		private $_password = "YOURPASSWORD";
		private $_database = "YOURDBNAME";

		public static function getInstance() {
			if(!self::$_instance) {
				self::$_instance = new self();
			}
			return self::$_instance;
        }

		private function __construct() {
			@$this->_connection = new mysqli($this->_host, $this->_username,
				$this->_password, $this->_database);
			if (mysqli_connect_errno()) {
				echo "Connessione Fallita: ".mysqli_connect_error();
				exit();
			}
		}

		private function __clone() {}

		public function getConnection() {
			return $this->_connection;
		}

		//not used anymore
		/*public function getPages(){
			$mysqli = $this->getConnection();
			$sql_query = "SELECT title FROM channel ORDER BY title DESC";
			$result= $mysqli->query($sql_query);
			$pages= array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$pages[] = $row["title"];
			}
			$result->free();
			return $pages;
		}*/

		public function getOlderPage(){
			$mysqli = $this->getConnection();
			$sql_query = "SELECT title FROM channel WHERE lastUpdate = (SELECT MIN(lastupdate) AS olderUpdate FROM channel) LIMIT 1";
			$result= $mysqli->query($sql_query);
			$row = $result->fetch_array(MYSQLI_ASSOC)["title"];
			$result->free();
			return $row;
		}

		public function registerUpdate($title){
			$mysqli = $this->getConnection();
			$sql_query = "UPDATE channel SET lastupdate= '".date("Y-m-d H:i:s")."' WHERE title=?";
			if($prep = $mysqli->prepare($sql_query)){
				$prep-> bind_param("s",$title);
				$prep-> execute();
				$prep-> close();
			}
		}

		public function createItem($title,$link,$description,$imgurl,$time,$chtitle){
			$mysqli = $this->getConnection();
			$sql_query = "INSERT INTO item VALUES (?,?,?,?,?,?)";
			if($prep = $mysqli->prepare($sql_query)){
				$prep-> bind_param("ssssss",$title,$link,$description,$imgurl,$time,$chtitle);
				$prep-> execute();
				$prep-> close();
			}
		}

		//not used anymore
		/*public function cleanAllItem(){
			$mysqli = $this->getConnection();
			$sql_query = "TRUNCATE TABLE item";
			$mysqli->query($sql_query);
		}*/

		public function cleanItem($chtitle){
			$mysqli = $this->getConnection();
			$sql_query = "DELETE FROM item WHERE chtitle=?";
			if($prep = $mysqli->prepare($sql_query)){
				$prep-> bind_param("s",$chtitle);
				$prep-> execute();
				$prep-> close();
			}
		}

		public function getFeed($chtitle){
			$mysqli = $this->getConnection();
			$sql_query = "SELECT chtitle, channel.link AS chlink, channel.description AS chdescription, item.title, item.link, item.description, imgurl, ptime FROM channel INNER JOIN item ON channel.title=chtitle WHERE chtitle = ? ORDER BY ptime DESC";
			$feeds= Array();
			if($prep = $mysqli->prepare($sql_query)){
				$prep-> bind_param("s",$chtitle);
				$prep-> execute();
				$result= $prep->get_result();
				$prep-> close();
				while ($row = $result->fetch_array(MYSQLI_ASSOC)){
					$feeds[] = $row;
				}
				$result->free();
			}
			return $feeds;
		}
	}
	//make sure there are no blank lines at the end of this file after the end of php!
?>