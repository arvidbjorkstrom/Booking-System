<?php
class Errors {
	private $conf;
	private $DB;
	private $lang;
	private $auth;
	private $current_errors;
	private $error_occurred;
	
	public function __construct(&$conf) {
		$this->conf =& $conf;
		$this->DB = false;
		$this->lang = false;
		$this->auth = false;
		$this->current_errors['display'] = array();
		$this->current_errors['admin'] = array();
		$this->error_occurred = false;
	}
	
	public function register_db($DB) {
		$this->DB = $DB;
	}
	
	public function register_lang($lang) {
		$this->lang = $lang;
	}
	
	public function register_auth($auth) {
		$this->auth = $auth;
		foreach($this->current_errors['display'] AS $key => $display_msg) {
			$admin_msg = $this->current_errors['admin'][$key];
			$userid = $this->auth->get_userid();
			$record_error_qry = "INSERT INTO errors SET admin_msg='$admin_msg', display_msg='$display_msg', userid='$userid';";
			$this->DB->db_query_no_data($record_error_qry);
		}
	}
	
	public function record_error($admin_msg,$display_msg, $fatal = false) {
		$this->error_occurred = true;
		$this->current_errors['display'][] = $display_msg;
		$this->current_errors['admin'][] = $display_msg;
		if($this->auth) {
			$userid = $this->auth->get_userid();
			$record_error_qry = "INSERT INTO errors SET admin_msg='$admin_msg', display_msg='$display_msg', userid='$userid';";
			$this->DB->db_query_no_data($record_error_qry);
		}
		if($fatal) {
			echo get_errors();
			die();
		}
	}
	
	public function errors_occurred() {
		return $this->error_occurred;
	}
	
	public function get_errors() {
		if($this->error_occured)
			return implode("<br />\n",$this->current_errors['disp']);
		else
			return "";
	}
}
?>