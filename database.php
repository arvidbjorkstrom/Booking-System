<?php
class Database {
	
	private $conf;
	private $err;
	private $db_link;
	
	public function __construct (&$conf, $err) {
		$this->conf =& $conf;
		$this->err = $err;
		$this->db_link = mysql_connect($conf['db_host'],$conf['db_user'],$conf['db_pass']);
		if(mysql_error()!="") $this->err->record_error("MySQL-error: ".mysql_error(),"Felaktiga MySQL-data", true);
		mysql_select_db($conf['db'], $this->db_link);
		if(mysql_error()!="") $this->err->record_error("MySQL-error: ".mysql_error(),"Felaktiga MySQL-data", true);
	}
	
	public function db_query_no_data($query) {
		$result =  mysql_query($query, $this->db_link);
		if(mysql_error()!="") $this->err->record_error("MySQL-query: ".$query." , Error: ".mysql_error(),"Kunde inte exekvera MySQL-data");
		$status['rows'] = mysql_affected_rows($this->db_link);
		$status['id'] = mysql_insert_id($this->db_link);
		return $status;
	}
	
	public function db_query_array($query) {
		$array = array();
		$result =  mysql_query($query, $this->db_link);
		if(mysql_error()!="") {
			$this->err->record_error("MySQL-query: ".$query." , Error: ".mysql_error(),"Kunde inte exekvera MySQL-data");
			return false;
		}
		if(mysql_num_rows($result) == 0) {
			return array();
		} else {
			while($row = mysql_fetch_assoc($result)) {
				$array[] = $row;
			}
			mysql_free_result($result);
			return $array;
		}
	}
	
	public function db_query_first($query) {
		$result = $this->db_query_array($query);
		if($result) return $result[0];
		else return false;
	}
	
	public function db_error() {
		return mysql_error($this->db_link);
	}

	public function date_to_timestamp($input) {
		$date_time = explode(' ',$input);
		if(count($date_time) == 2) {
			$date = explode('-',$date_time[0]);
			$time = explode(':',$date_time[1]);
		} else {
			$date = explode('-',$input);
			$time = array(0,0,0);
		}
		if(count($date) != 3 || count($time) != 3) return false;
		
		// Return Unix timestamp of time
		return mktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);
	}
}
?>