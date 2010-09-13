<?php
class Auth {
	
	private $conf;
	private $DB;
	private $err;
	private $lang;
	private $session;
	private $session_timeout;
	private $session_id;
	private $init;
	
	public function __construct(&$conf,$DB,$err,$lang) {
		$this->conf =& $conf;
		$this->DB = $DB;
		$this->err = $err;
		$this->lang = $lang;
		$this->session_timeout = $conf['session_timeout'];
		$this->session_id = $conf['session_id'];
		$this->init = time();
		
		if(isset($_COOKIE[$this->session_id])) {
			//user seems to be logged in, lets validate the presumed login!
			
			$session_qry = "SELECT users.*, sessions.sessionid, sessions.timeout FROM sessions 
			LEFT JOIN users ON sessions.userid=users.userid 
			WHERE sessionid='".$_COOKIE[$this->session_id]."';";
			
			$session = $DB->db_query_first($session_qry);
			
			if($session) {
				if($session['timeout'] < date("Y-m-d H:i:s", $this->init)) {
					$this->err->record_error("Session error (session timeout): ".$_COOKIE['macmail_sessoin_id'], "Din session har g&aring;tt ut. Du m&aring;ste ha logga in igen.");
					setcookie($this->session_id, "", time()-3600, '/', str_replace('www.','', $_SERVER['HTTP_HOST']));
					unset($session);
				} else if($session['timeout'] < date("Y-m-d H:i:s", $this->init+$this->session_timeout*86400-$this->session_timeout*1800)) {
					
					$sessiontimeout = date("Y-m-d H:i:s", ($this->init+$this->session_timeout*86400)); //$this->session_timeout days from now
					$cookietimeout = $this->init+($this->session_timeout+1)*86400; //$this->session_timeout + 1 days from now
					$sid = $_COOKIE[$this->session_id];
					
					if($relogin_successful = setcookie($this->session_id, "$sid", "$cookietimeout", '/', str_replace('www.','', $_SERVER['HTTP_HOST']))) {
						$upd_session_qry = "UPDATE sessions SET timeout='$sessiontimeout' WHERE sessionid='$sid';";
						$upd_session_res = $this->DB->db_query_no_data($upd_session_qry);
						$session["timeout"] = $sessiontimeout;
					} else {
						$this->err->record_error("Relogin failed (cookie-error): ".$_COOKIE[$this->session_id], "&Aring;terinloggningen misslyckades. Du m&aring;ste ha cookies p&aring;slaget.");
					}
				}
			} else {
				unset($session);
				$this->err->record_error("Invalid cookie: ".$_COOKIE[$this->session_id], "Invalid cookie");
				setcookie($this->session_id, "", time()-3600, '/', str_replace('www.','', $_SERVER['HTTP_HOST']));
			}
		}
		$remove_old_sessions_qry = "DELETE FROM sessions WHERE timeout < '".date('Y-m-d, $this->init')."'";
		$remove_old_sessions = $this->DB->db_query_no_data($remove_old_sessions_qry);
		
		
		if(isset($session)) $this->session = $session;
		else $this->session = false;
	}
	
	public function login($username, $password) {
		$this->init = time();
		//user tried to login
		$uname = mysql_real_escape_string($username);
		$pword = md5($password);
		$login_qry = "SELECT * FROM users WHERE username='$uname' AND password='$pword';";
		$login_info = $this->DB->db_query_first($login_qry);
		
		if($login_info) {
			$logintime = date("Y-m-d H:i:s", $this->init);
			$sessiontimeout = date("Y-m-d H:i:s", ($this->init+$this->session_timeout*60*60*24)); //$this->session_timeout days from now
			$cookietimeout = $this->init+($this->session_timeout+1)*60*60*24; //$this->session_timeout + 1 days from now
			$sid = md5(microtime());
			if($login_successful = setcookie($this->session_id, "$sid", "$cookietimeout", '/', str_replace('www.','', $_SERVER['HTTP_HOST']))) {
				/*
				$del_old_session_qry = "
				DELETE FROM sessions WHERE userid='$login_info[userid]'";
				$del_old_session_res = $this->DB->db_query_no_data($del_old_session_qry);
				*/
				$add_session_qry = "
				REPLACE INTO sessions SET 
				sessionid='$sid', 
				userid='$login_info[userid]', 
				login='$logintime',
				timeout='$sessiontimeout';";
				$add_session_res = $this->DB->db_query_no_data($add_session_qry);
				$session = $login_info;
				$session["sessionid"] = $sid;
				$session["timeout"] = $sessiontimeout;
			} else {
				$this->err->record_error("Login failed (cookie-error): ".$username, "Inloggningen misslyckades. Du m&aring;ste ha cookies p&aring;slaget.");
				unset($session);
			}
		} else {
			$this->err->record_error("Login failed (login/password-error): ".$username, "Inloggningen misslyckades. Kontrollera ditt inloggningsnamn och l&ouml;senord.");
			unset($session);
		}
		if(isset($session)) $this->session = $session;
	}
	
	public function show_login() {
		echo '
<html>
	<head>
		<title>Inloggning</title>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">  
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body bgcolor="#FFFFFF">
		<br /><br /><br /><br />
		<table border="0" align="center">
			<tr>
				<td colspan="2" align="center" style="color:#FF0000;font-weight:bold;">'.$this->err->get_errors().'</td>
			</tr>
			<form action="'.$_SERVER['PHP_SELF'].'" method="POST" name="login">
			<input type="hidden" name="action" value="login">
			<tr>
				<td align="right">'.$this->lang->get('lbl_username').': </td>
				<td><input type="text" name="username"></td>
			</tr>
			<tr>
				<td align="right">'.$this->lang->get('lbl_password').': </td>
				<td><input type="password" name="password"></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="  '.$this->lang->get('lbl_login').'  "></td>
			</tr>
			</form>
		</table>
	</body>
</html>';
	}
	
	public function logout() {
		//user tried to logout
		$cookietimeout = $this->init;
		$sid = $_COOKIE[$this->session_id];
		if($logout_successful = setcookie($this->session_id, "", "$cookietimeout", '/', str_replace('www.','', $_SERVER['HTTP_HOST']))) {
			$logout_qry = "DELETE FROM sessions WHERE sessionid='$sid' OR userid='".$this->session['userid']."';";
			$logout_res = $this->DB->db_query_no_data($logout_qry);
			header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
		} else {
			$this->err->record_error("Logout failed (cookie-error): ".$_POST['userid'], "Utloggningen misslyckades. Du m&aring;ste ha cookies p&aring;slaget.");
		}
	}
	
	public function loggedin() {
		if($this->session === false) return false;
		else return true;
	}
	
	public function allowed($level) {
		if($this->session['userlevel'] >= $level) return true;
		else return false;
	}
	
	public function get_userinfo($userid = false) {
		if(!$userid) return $this->session;
		else {
			$get_user_qry = "SELECT * from users WHERE userid='$userid';";
			return $this->DB->db_query_first($get_user_qry);
		}
	}
	
	public function get_userid($username = false) {
		if(!$username) return $this->session['userid'];
		else {
			$get_user_qry = "SELECT userid from users WHERE username='$username';";
			if($userinfo = $this->DB->db_query_first($get_user_qry))
				return $userinfo['userid'];
			else
				return false;
		}
	}
	
	public function get_user_value($column) {
		return $this->session[$column];
	}
	
	public function get_all_users() {
		$get_user_qry = "SELECT * from users;";
		return $this->DB->db_query_array($get_user_qry);
	}
	
	public function add_user($username,$password,$info = array()) {
		
		if($this->user_exists($username)) return false;
		
		
		$uname = mysql_real_escape_string($username);
		$pword = md5($password);
		$user_qry = "INSERT INTO users SET username='$uname', password='$pword'";
		foreach($info AS $key=>$val)
			$user_qry .= ", $key='".mysql_real_escape_string($val)."'";
		// If there is no email in the infoarray and the username seems to be an email address add the username as emailaddress
		if(!array_key_exists('email',$info) && strpos($username,'@') !== false) $user_qry .= ", email='$uname'";
		$user_qry .= ";";
		
		$user_res = $this->DB->db_query_no_data($user_qry);
		if($user_res['rows'] != 1) return false;
		else return $user_res['id'];
	}
	
	public function user_exists($username){
		$userid = $this->get_userid($username);
		if($userid === false) return false;
		else return true;
	}
	
	public function update_user($username,$password,$info = array()) {
		
		$uname = mysql_real_escape_string($username);
		$pword = md5($password);
		$user_qry = "UPDATE users SET password='$pword'";
		foreach($info AS $key=>$val)
			$user_qry .= ", $key='".mysql_real_escape_string($val)."'";
		// If there is no email in the infoarray and the username seems to be an email address add the username as emailaddress
		if(!array_key_exists('email',$info) && strpos($username,'@') !== false) $user_qry .= ", email='$uname'";
		$user_qry .= " WHERE username='$uname';";
			
		$user_res = $this->DB->db_query_no_data($user_qry);
	}	
}
?>