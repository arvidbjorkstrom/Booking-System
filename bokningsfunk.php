<?php
class Bokningsfunk {

	private $conf;
	private $DB;
	private $err;
	private $lang;
	private $auth;
	
	public function __construct(&$conf,$DB,$err,$lang,$auth) {
		$this->conf =& $conf;
		$this->DB = $DB;
		$this->err = $err;
		$this->lang = $lang;
		$this->auth = $auth;
	}
	
	public function backup_db() {
		$bokningar = $this->DB->db_query_array("SELECT * FROM bokningar;");
		foreach($bokningar AS $bokning) {
			$sets = array();
			foreach($bokning AS $col => $val) {
				$sets[] = "$col = '$val'";
			}
			$output[] = "INSERT INTO bokningar SET ".implode(', ',$sets).";";
		}
		$users = $this->DB->db_query_array("SELECT * FROM users;");
		foreach($users AS $user) {
			$sets = array();
			foreach($user AS $col => $val) {
				$sets[] = "$col = '$val'";
			}
			$output[] = "INSERT INTO users SET ".implode(', ',$sets).";";
		}
		return $output;
	}
	
	public function get_bookings($firstDay, $lastDay, $conditions) {
		$bokningar_qry = "SELECT * FROM bokningar WHERE datum>='".date('Y-m-d',$firstDay)."' AND  datum<='".date('Y-m-d',$lastDay)."'".(count($conditions)>0?" AND ".implode(' AND ',$conditions):'')." ORDER BY datum;";
		return $this->DB->db_query_array($bokningar_qry);
	}
	
	public function list_month($month, $year, $allinfo=true) {
		$output = array();
		$today = mktime();
		$firstDay = mktime(0,0,0,$month,1,$year);
		$lastDay = mktime(0,0,0,$month+1,0,$year);
		
		$conditions = array();
		$conditions[] = "bokn != '3'";
		
		$bokningar = $this->get_bookings($firstDay, $lastDay, $conditions);
		foreach($bokningar AS $bokning) {
			$ts = $this->DB->date_to_timestamp($bokning['datum']);
			$daynum = strftime('%u',$ts);
			
			if($daynum < 5 && $bokning['spec'] > 1) {
				$kod = $bokning['typ'].'-'.$bokning['spec'];
			} else {
				$kod = $bokning['typ'];
			}
			
			$output[] = strftime('%d %b',$ts)."	".utf8_encode($bokning['namn'])."	".utf8_encode($bokning['phone'])."	".($allinfo?($bokning['bokad']<'2008-12-12' && $kod==1 && ($daynum==5 || $daynum==6)?'4500kr (bokad innan prishöjning)':$this->conf['rates'][$daynum][$kod]."kr"):'').($bokning['bokn']==1?'	(Preliminär!)':'');
		}
		return $output;
	}
	
	public function show_month($month = false,$year = false) {
		if(!$month) $month = date('m');
		if(!$year) $year = date('Y');
		
		$today = mktime();
		$firstDay = mktime(0,0,0,$month,1,$year);
		$lastDay = mktime(0,0,0,$month+1,0,$year);
		
		$conditions = array();
		$conditions[] = "bokn != '3'";
		
		$bokningar = $this->get_bookings($firstDay, $lastDay, $conditions);

		for($i=0;$i<strftime('%d',$lastDay);$i++) {
			$bokningarna[$i+1] = array('-1',date('Y-m-d',strtotime("+$i days",$firstDay)),'2','','','1','',date('Y-m-d'),'','',(strftime('%u',strtotime("+$i days",$firstDay))>4?'-1':'1'));
		}
		
		foreach($bokningar AS $bokning) {
			$ts = $this->DB->date_to_timestamp($bokning['datum']);
			unset($keys);
			unset($vals);
			foreach($bokning AS $key=>$val) {
				$bokning[$key] = utf8_encode($val);
				$vals[] = (strftime('%u',$ts)>4&&$key=='spec'?'-1':utf8_encode(str_replace(array("\n","\r\n","\r")," \\n",$val)));
				$keys[] = utf8_encode($key);
			}
			$bokningarna[intval(strftime('%e',$ts))] = $vals;
			$bokn_assoc[intval(strftime('%e',$ts))] = $bokning;
		}
		if(!is_array($keys)) {
			$columns = $this->DB->db_query_array("SHOW COLUMNS IN bokningar;");
			foreach($columns AS $col) {
				$keys[] = $col['Field'];
			}
		}
		
		$output[] = '<table border="0" cellpadding="0" cellspacing="0">';
		$output[] = '	<tr><th colspan="8"><a href="javascript: void(0)" onClick="changeMonth('.strftime("'%m','%Y','prev'",$firstDay).')" id="bak"><img src="'.$this->conf['wpath'].'bak.png" alt="←" title="Bakåt" border="0" /></a> &nbsp;'.strftime('%B, %Y',$firstDay).' &nbsp;<a href="javascript: void(0)" onClick="changeMonth('.strftime("'%m','%Y','next'",$firstDay).')" id="fram"><img src="'.$this->conf['wpath'].'fram.png" alt="→" title="Framåt" border="0" /></a></th></tr>';
		$output[] = '	<tr class="days">';
		$output[] = '		<td class="week"></td><td>mån</td><td>tis</td><td>ons</td><td>tors</td><td>fre</td><td>lör</td><td>sön</td>';
		$output[] = '	</tr>';
		
		$w = 0;
		for($i=(1-strftime('%u',$firstDay));$i<strftime('%d',$lastDay);$i++) {
			$day = strtotime("+$i days",$firstDay);
			if($w != strftime('%V',$day)) {
  			$w = strftime('%V',$day);
				$output[] = '	<tr>';
  			$output[] = '		<td class="week">'.$w.'</td>';
  		}
  		
			if($i<0) {
				$output[] = '		<td>&nbsp;</td>';
			} else {
				$output[] = '<td'.
					($this->auth->loggedin() || (!$this->auth->loggedin() && $bokn_assoc[$i+1]['bokn'] != 1 && $bokn_assoc[$i+1]['bokn']!=2)?
						' onclick="edit_booking(new Array(\''.implode("','",$bokningarna[$i+1]).'\'),new Array(\''.implode("','",$keys).'\'))" '.
							($bokn_assoc[$i+1]['namn']!=''?'title="'.$bokn_assoc[$i+1]['namn'].'"':''):
						''
					).
					' class="datum'.
					($bokn_assoc[$i+1]['bokn']==1?'_prel':($bokn_assoc[$i+1]['bokn']==2?'_bok':'')).
					(date('Y-m-d')==date('Y-m-d',$day)?' today':'').
					'">'.($i+1).'</td>';
				
				if(strftime('%u',$day)==7) {
					$output[] = '</tr>';
				} else if(($i+1) == strftime('%d',$lastDay)) {
					for($j=strftime('%u',$lastDay);$j<7;$j++) $output[] = '<td>&nbsp;</td>';
					$output[] = '</tr>';
				}
			}
		}
		if($this->auth->loggedin()) {
			$output[] = '</table>';
			$output[] = '<div id="monthlist" class="hide">';
			$output[] = '	<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
			$output[] = '		<input type="hidden" name="sendemail" value="yes" />';
			$output[] = '		<input type="text" name="to1" value="'.$this->conf['to1'].'"> ';
			$output[] = '		<input type="text" name="subj1" value="Bokningar '.strftime('%B, %Y',$firstDay).'"><br />';
			$output[] = '		<textarea name="body2" rows="8" cols="25" readonly="readonly" onclick="this.select()">'.implode("\n",$this->list_month($month,$year)).'</textarea><br />';
			$output[] = '		<br />';
			$output[] = '		<input type="text" name="to2" value="'.$this->conf['to1'].'"> ';
			$output[] = '		<input type="text" name="subj2" value="Bokningar '.strftime('%B, %Y',$firstDay).'"><br />';
			$output[] = '		<textarea name="body2" rows="8" cols="25" readonly="readonly" onclick="this.select()">'.implode("\n",$this->list_month($month,$year,false)).'</textarea><br />';
			$output[] = '		<input type="image" src="'.$this->conf['wpath'].'mail.png" alt="Skicka Bokningslista" title="Skicka Bokningslista" border="0" />';
			$output[] = '	</form>';
			$output[] = '</div>';
		}
		return $output;
	}
	
	public function save_booking($data) {
		foreach($data AS $key=>$val) {
			if($key != 'bokningsid' && $key != 'sendinfo') $sets[] = "$key='".mysql_real_escape_string($val)."'";
		}
		if($data['bokningsid'] == '-1') {
			$data_updated = true;
			$bokn_updated = true;
			$query = "INSERT INTO bokningar SET ".implode(',',$sets).";";
		} else {
			$old_data = $this->DB->db_query_first("SELECT * FROM bokningar WHERE bokningsid='".mysql_real_escape_string($data['bokningsid'])."';");
			$data_updated = false;
			$bokn_updated = false;
			foreach($old_data AS $key=>$val) {
				if($val != $data[$key]) {
					$data_updated = true;
					if($key=='bokn') $bokn_updated = true;
				}
			}
			$query = "UPDATE bokningar SET ".implode(',',$sets)." WHERE bokningsid='".mysql_real_escape_string($data['bokningsid'])."';";
		}
		$this->DB->db_query_no_data($query);
		
		if($data['sendinfo'] == '1' && $data['epost'] != '' && $data_updated) {
			$ts = $this->DB->date_to_timestamp($data['datum']);
			
			if($data['bokn'] == 1) { // Preliminär
				$message = ($data_updated?$this->lang->get('txt_booking_update_prel'):$this->lang->get('txt_booking_prel')).$this->lang->get('txt_booking_details');
				$subject = $this->lang->get('txt_booking_subj_prel').strftime('%d %b',$ts);
				
			} else if($data['bokn'] == 2) { // Bokning
				$message = ($data_updated && !$bokn_updated?$this->lang->get('txt_booking_update'):$this->lang->get('txt_booking_final')).$this->lang->get('txt_booking_details');
				$subject = $this->lang->get('txt_booking_subj').strftime('%d %b',$ts);
				
			} else if($data['bokn'] == 3) { // Avbokad
				$message = $this->lang->get('txt_booking_cancel');
				$subject = $this->lang->get('txt_booking_subj_cancel').strftime('%d %b',$ts);
			}
			$this->email($this->conf['default_from'],$data['epost'],$subject,$message,$data);
		}
	}
	
	public function create_bookingform() {
		$output[] = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" name="bokning" id="bokning" onsubmit="save_booking(); return false;">';
		$output[] = '<input type="hidden" name="bokningsid" value="-1" />';
		$output[] = '<div class="dates">';
		$output[] = '	<a href="javascript: void(0)" onclick="cancelEdit()" class="closebutton"><img src="'.$this->conf['wpath'].'close.png" alt="X" title="Stäng" /></a>';
		$output[] = '	<p>';
		$output[] = '		<span class="small">Datum:</span>';
		$output[] = '		<input type="text" name="datum" value="" size="10" onchange="setEditShow(\'datum\',this.value,\'\')" class="hide" id="datum_edit"'.($this->auth->loggedin()?'':' readonly="readonly"').' />';
		$output[] = '		<span id="datum_show" class="show" onclick="showEdit();"></span>';
		$output[] = '	</p>';
		$output[] = '	<p>';
		$output[] = '		<span class="small">Bokad:</span><br />';
		$output[] = '		<input type="text" name="bokad" value="'.date('Y-m-d').'" size="10" onchange="setEditShow(\'bokad\',this.value,\'\')" class="hide" id="bokad_edit" />';
		$output[] = '		<span id="bokad_show" class="show" onclick="showEdit();"></span>';
		$output[] = '	</p>';
		$output[] = '	<p>';
		$output[] = '		<span class="small">Betald:</span><br />';
		$output[] = '		<input type="text" name="payed" value="" size="10" onchange="setEditShow(\'payed\',this.value,\'\')" class="hide" id="payed_edit" />';
		$output[] = '		<span id="payed_show" class="show" onclick="showEdit();"></span>';
		$output[] = '	</p>';
		$output[] = '	<div style="clear:both;"></div>';
		$output[] = '</div>';
		if($this->auth->loggedin()) {
			$output[] = '<p>';
			$output[] = '	<span class="small">Bokningstyp:</span><br />';
			$output[] = '	<div id="bokn_edit" class="hide">';
			$output[] = '	<div class="radiobuttons">';
			$output[] = '		<input type="radio" name="bokn" value="1" onchange="setEditShow(\'bokn\',this.value,\'\')" /><span onclick="setEditShow(\'bokn\',1,\'\')"> Prelemin&auml;r</span><br />';
			$output[] = '		<input type="radio" name="bokn" value="2" checked="checked" onchange="setEditShow(\'bokn\',this.value,\'\')" /><span onclick="setEditShow(\'bokn\',2,\'\')"> Bokad</span><br />';
			$output[] = '		<input type="radio" name="bokn" value="3" onchange="setEditShow(\'bokn\',this.value,\'\')" /><span onclick="setEditShow(\'bokn\',3,\'\')"> Avbokad</span><br />';
			$output[] = '		<input type="radio" name="bokn" value="4" onchange="setEditShow(\'bokn\',this.value,\'\');setEditShow(\'payed\',\''.date('Y-m-d').'\',\'\');" /><span onclick="setEditShow(\'bokn\',4,\'\');setEditShow(\'payed\',\''.date('Y-m-d').'\',\'\')"> Betald</span>';
			$output[] = '	</div>';
			$output[] = '	</div>';
			$output[] = '	<span id="bokn_show" class="show" onclick="showEdit();"></span>';
			$output[] = '</p>';
		} else {
			$output[] = '<p>';
			$output[] = '	<span class="small">Bokningstyp:</span><br />';
			$output[] = '	<span id="bokn_edit" class="hide">Bokningsf&ouml;rfr&aring;gan</span>';
			$output[] = '	<span id="bokn_show" class="show" onclick="showEdit();">Bokningsf&ouml;rfr&aring;gan</span>';
			$output[] = '</p>';
			$output[] = '<input type="hidden" name="bokn" value="4" />';
		}
		$output[] = '<p>';
		$output[] = '	<span class="small">Namn:</span><br />';
		$output[] = '	<input type="text" name="namn" value="" size="25" id="namn_edit" class="hide" onchange="setEditShow(\'namn\',this.value,\'\')" />';
		$output[] = '	<span id="namn_show" class="show" onclick="showEdit();"></span>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<span class="small">Epost:</span><br />';
		$output[] = '	<input type="text" name="epost" value="" size="25" id="epost_edit" class="hide" onchange="setEditShow(\'epost\',this.value,\'mailto:\'+this.value)" />';
		$output[] = '	<a href="" id="epost_show" class="show"></a>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<span class="small">Telefon:</span><br />';
		$output[] = '	<input type="text" name="phone" value="" size="25" id="phone_edit" class="hide" onchange="setEditShow(\'phone\',this.value,\'tel:\'+this.value)" />';
		$output[] = '	<a href="" id="phone_show" class="show"></a>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<span class="small">Alternativ telefon:</span><br />';
		$output[] = '	<input type="text" name="phone_alt" value="" size="25" id="phone_alt_edit" class="hide" onchange="setEditShow(\'phone_alt\',this.value,\'tel:\'+this.value)" />';
		$output[] = '	<a href="" id="phone_alt_show" class="show"></a>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<span class="small">G&auml;st:</span><br />';
		$output[] = '	<div id="typ_edit" class="hide">';
		$output[] = '	<div class="radiobuttons">';
		$output[] = '		<input type="radio" name="typ" value="1" checked="checked" onchange="setEditShow(\'typ\',this.value,\'\')" /><span onclick="setEditShow(\'typ\',1,\'\')"> Standard</span><br />';
		$output[] = '		<input type="radio" name="typ" value="2" onchange="setEditShow(\'typ\',this.value,\'\')" /><span onclick="setEditShow(\'typ\',2,\'\')"> Stor&auml;ngstrakten</span><br />';
		$output[] = '		<input type="radio" name="typ" value="3" onchange="setEditShow(\'typ\',this.value,\'\')" /><span onclick="setEditShow(\'typ\',3,\'\')"> Stor&auml;ngen</span><br />';
		if($this->auth->loggedin()) {
			$output[] = '		<input type="radio" name="typ" value="4" onchange="setEditShow(\'typ\',this.value,\'\')" /><span onclick="setEditShow(\'typ\',4,\'\')"> Funktion&auml;r</span><br />';
			$output[] = '		<input type="radio" name="typ" value="5" onchange="setEditShow(\'typ\',this.value,\'\')" /><span onclick="setEditShow(\'typ\',5,\'\')"> Styrelse</span>';
		}
		$output[] = '	</div>';
		$output[] = '	</div>';
		$output[] = '	<span id="typ_show" class="show" onclick="showEdit();"></span>';
		$output[] = '</p>';
		$output[] = '<div id="spec_field" class="hide">';
		$output[] = '<p>';
		$output[] = '	<span class="small">Special:</span><br />';
		$output[] = '	<div id="spec_edit" class="hide">';
		$output[] = '	<div class="radiobuttons">';
		$output[] = '		<input type="radio" name="spec" value="1" checked="checked" onchange="setEditShow(\'spec\',this.value,\'\')" /><span onclick="setEditShow(\'spec\',1,\'\')"> Standard</span><br />';
		$output[] = '		<input type="radio" name="spec" value="2" onchange="setEditShow(\'spec\',this.value,\'\')" /><span onclick="setEditShow(\'spec\',2,\'\')"> Barnkalas/Skolklass<&aring;k 6</span><br />';
		$output[] = '		<input type="radio" name="spec" value="3" onchange="setEditShow(\'spec\',this.value,\'\')" /><span onclick="setEditShow(\'spec\',3,\'\')"> Utan k&ouml;k</span><br />';
		$output[] = '		<input type="radio" name="spec" value="4" onchange="setEditShow(\'spec\',this.value,\'\')" /><span onclick="setEditShow(\'spec\',4,\'\')"> Bara kaffe</span><br />';
		$output[] = '	</div>';
		$output[] = '	</div>';
		$output[] = '	<span id="spec_show" class="show" onclick="showEdit();"></span>';
		$output[] = '</p>';
		$output[] = '</div>';
		$output[] = '<p>';
		$output[] = '	<span class="small">Info:</span><br />';
		$output[] = '	<textarea rows="3" cols="25" name="info" id="info_edit" class="hide" onchange="setEditShow(\'info\',this.value,\'\')"></textarea>';
		$output[] = '	<span id="info_show" class="show" onclick="showEdit();"></span>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<span id="sendinfo_show" class="show" onclick="showEdit();">X</span>';
		$output[] = '	<div id="sendinfo_edit" class="hide">';
		$output[] = '		<input type="checkbox" name="sendinfo" value="1" checked="checked" onchange="setEditShow(\'sendinfo\',\'X\',\'\')" />';
		$output[] = '		<span class="small">Skicka epost</span><br /><br />';
		$output[] = '	</div>';
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '	<input type="button" value="&nbsp;&nbsp; Avbryt &nbsp;&nbsp;" onclick="cancelEdit()" style="float:left;" id="cancelbutton" />';
		$output[] = '	<input type="button" value="&nbsp;&nbsp; Spara &nbsp;&nbsp;" onclick="save_booking()" style="float:right;" class="hide" id="savebutton" />';
		$output[] = '</p>';
		$output[] = '</form>';
		$output[] = '<div style="clear:both;"></div>';
		
		return $output;
	}
	
	function send_calendar($month,$year=false) {
		if(!$year) $year = date('Y');
		$return = "Månadrapport $month, $year\n";
		$return .= $this->email($this->conf['default_from'],($_POST['to1']==''?$this->conf['to1']:$_POST['to1']),($_POST['subj1']==''?'Bokningar '.strftime('%B, %Y',mktime(0,0,0,$month,1,$year)):$_POST['subj1']),($_POST['body1']==''?implode("\n",$this->list_month($month,$year)):$_POST['body1']));
		$return .= $this->email($this->conf['default_from'],($_POST['to2']==''?$this->conf['to2']:$_POST['to2']),($_POST['subj2']==''?'Bokningar '.strftime('%B, %Y',mktime(0,0,0,$month,1,$year)):$_POST['subj2']),($_POST['body2']==''?implode("\n",$this->list_month($month,$year,false)):$_POST['body2']));
		
		return $return;
	}
	
	function send_reminders($days) {
		
		$today = mktime();
		$firstDay = $today+$days*24*3600;
		$lastDay = $today+$days*24*3600;
		
		$conditions = array();
		$conditions[] = "bokn != '3'";
		
		$bokningar = $this->get_bookings($firstDay, $lastDay, $conditions);
		$return = $days." dagar: \n";
		foreach($bokningar AS $bokning) {
			if($bokning['epost'] == '') $bokning['epost'] = 'info@storangssalen.se';
			$ts = $this->DB->date_to_timestamp($bokning['datum']);
			$daynum = strftime('%u',$ts);
			
			if($daynum < 5 && $bokning['spec'] > 1) {
				$kod = $bokning['typ'].'-'.$bokning['spec'];
			} else {
				$kod = $bokning['typ'];
			}
			
			$message = $this->lang->get('txt_reminder_message').$this->lang->get('txt_booking_details');
			
			if($bokning['bokn'] == 1 && $days<=21) $message = str_replace('{{addin}}',$this->lang->get('txt_reminder_prel_21'),$message);
			else if($bokning['bokn'] == 1 && $days>7) $message = str_replace('{{addin}}',$this->lang->get('txt_reminder_prel'),$message);
			else if($bokning['bokn'] == 1 && $days<=7) $message = str_replace('{{addin}}',$this->lang->get('txt_reminder_prel').$this->lang->get('txt_reminder_keys'),$message);
			else if($days<=7) $message = str_replace('{{addin}}',$this->lang->get('txt_reminder_keys'),$message);
			
			$return .= $this->email($this->lang->get('default_from'),$bokning['epost'],$this->lang->get('txt_reminder_subj').strftime('%d %b',$ts),$message,$bokning);
		}
		return $return;
	}
	
	function email($from, $to, $subject, $message, $bookinginfo = false) {
		if(!in_array($from, $this->conf['froms'])) $from = $this->conf['default_from'];
		$headers = 'From: ' . $from . "\r\n" .
			'Cc: ' . $from . "\r\n" .
			'Reply-To: ' . $from . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		
		$message = utf8_decode($message.$this->lang->get('txt_signature'));
		
		if($bookinginfo) {
			$ts = $this->DB->date_to_timestamp($bookinginfo['datum']);
			$daynum = strftime('%u',$ts);
			
			if($daynum < 5 && $bookinginfo['spec'] > 1) {
				$kod = $bookinginfo['typ'].'-'.$bookinginfo['spec'];
			} else {
				$kod = $bookinginfo['typ'];
			}
			
			$search[] = '{{rent}}';
			$replace[] = $this->conf['rates'][$daynum][$kod]." kr";
			$search[] = '{{bookingtype}}';
			$replace[] = utf8_decode($this->conf['btype'][$daynum][$kod]);
			$search[] = '{{addin}}';
			$replace[] = '';
			
			foreach($bookinginfo AS $key=>$val) {
				$search[] = '{{'.$key.'}}';
				$replace[] = $val;
			}
			$message = str_replace($search,$replace,$message);
		}
		$mailok = mail($to, $subject, $message,$headers);
		
		return ($mailok?'OK ':'ERR ').$to."\n".$subject."\n".utf8_encode($message)."\n".$headers."\n\n";
	}
}
?>