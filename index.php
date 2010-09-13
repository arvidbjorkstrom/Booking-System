<?php
// Lets start all the support services
require_once('./start.php');

//$auth->add_user('pelle','dellerds27', array('email'=>'pelle@ssark.se'));
//$auth->add_user('anders','bebbles75', array('email'=>''));

// Check if the user has tried to log in. If so let's check the info!
if($_POST['action'] == "login") {
	$auth->login($_POST['username'], $_POST['password']);
} else if($_REQUEST['action'] == "logout") {
	$auth->logout();
}

if($_GET['do']=='login') {
	$auth->show_login();
	exit();
}

// Check if the user is logged in. If so let's start with the big stuff, if not lets show the login
if($auth->loggedin()) {
	if(isset($_GET['adduser'])) $auth->add_user($_GET['adduser'],$_GET['pw']);
	if($_REQUEST['backup'] == 'yes') {
		$bkup = $bokn->backup_db();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: text/x-sql");
		header("Content-Disposition: attachment; filename=\"backup_".date('Y-m-d_H.i.s').".sql\";" );
		header("Content-Transfer-Encoding: binary");
		echo implode("\n",$bkup);
		exit();
	} else if($_REQUEST['report'] == 'yes') {
		if($_REQUEST['year'] > 0) $year = $_REQUEST['year'];
		else $year = date('Y');
		$data = array();
		for($i=1;$i<13;$i++) {
			$data = array_merge($data, str_replace("\t",";",$bokn->list_month($i,$year)));
		}
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: text/x-csv");
		header("Content-Disposition: attachment; filename=\"report_".$year.".csv\";" );
		header("Content-Transfer-Encoding: binary");
		echo implode("\n",$data);
		exit();
	} else {
		if($_REQUEST['action'] == 'save') {
			$bokn->save_booking($_REQUEST['data']);
			$datum_array = explode('-',$_REQUEST['data']['datum']);
			$year = $datum_array[0];
			$month = $datum_array[1];
		}
	}
}

if(isset($year) && isset($month)) $kalender = $bokn->show_month($month,$year);
else if($_REQUEST['year']>0 && isset($_REQUEST['month'])) $kalender = $bokn->show_month($_REQUEST['month'],$_REQUEST['year']);
else if(isset($_REQUEST['month'])) $kalender = $bokn->show_month($_REQUEST['month']);
else $kalender = $bokn->show_month();

$bokningsform = $bokn->create_bookingform();

if($_REQUEST['ajax'] == 'cal') {
	echo implode("\n",$kalender);
} else {
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Stor&auml;ngssalen bokning</title>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">	
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $conf['wpath']; ?>bokning.css" />
		<script type="text/javascript" src="<?php echo $conf['wpath']; ?>ajax.js"></script>
		<script type="text/javascript">
		/* <![CDATA[ */
		
var wpath = '<?php echo $conf['wpath']; ?>';
var columns = new Array('<?php echo implode("','",$conf['columns']); ?>');
var value_arrays = new Array();
value_arrays['bokn'] = new Array();
value_arrays['bokn'][1] = 'Preliminär';
value_arrays['bokn'][2] = 'Bokad';
value_arrays['bokn'][3] = 'Avbokad';
value_arrays['typ'] = new Array();
value_arrays['typ'][1] = 'Standard';
value_arrays['typ'][2] = 'Stor&auml;ngstrakten';
value_arrays['typ'][3] = 'Stor&auml;ngen';
value_arrays['typ'][4] = 'Funktion&auml;r';
value_arrays['typ'][5] = 'Styrelse';
value_arrays['spec'] = new Array();
value_arrays['spec'][1] = 'Standard';
value_arrays['spec'][2] = 'Barnkalas/Skolklass<&aring;k 6';
value_arrays['spec'][3] = 'Utan k&ouml;k';
value_arrays['spec'][4] = 'Bara kaffe';

function showEdit() {
	<?php if($auth->allowed(10)) { ?>
	for (var i in columns) {
		if(columns[i] != 'bokningsid') {
			hideElement(columns[i]+'_show');
			showElement(columns[i]+'_edit');
		}
	}
	showElement('savebutton');
	<?php } ?>
}
function reportcsv() {
	var year = prompt('År:');
	if(year == '')
		window.location = '<?php echo $conf['wpath']; ?>?report=yes';
	else
		window.location = '<?php echo $conf['wpath']; ?>?report=yes&year='+year;
}
		
		/* ]]> */
		</script>
		<script type="text/javascript" src="<?php echo $conf['wpath']; ?>booking.js"></script>
	</head>
	<body>
		<div id="kalender" class="show">
			<?php echo implode("\n",$kalender); ?>
		</div>
		<div id="bokningsform" class="hide">
			<?php 
			if($auth->loggedin()) echo implode("\n",$bokningsform);
			?>
		</div>
<?php if($auth->loggedin()) { ?>
		<p class="small">
			<a href="javascript: void(0)" onclick="showHide('monthlist')"><img src="<?php echo $conf['wpath']; ?>lista.png" alt="Lista" title="Lista" border="0" /></a> <img src="<?php echo $conf['wpath']; ?>avdelare.png" alt="|" /> 
			<a href="<?php echo $conf['wpath']; ?>?backup=yes"><img src="<?php echo $conf['wpath']; ?>backup.png" alt="Backup" title="Backup" border="0" /></a> <img src="<?php echo $conf['wpath']; ?>avdelare.png" alt="|" /> 
			<a href="javascript: void(0)"><img src="<?php echo $conf['wpath']; ?>rapport.png" alt="Rapport" title="Rapport" border="0" onclick="reportcsv(); return false;" /></a> <img src="<?php echo $conf['wpath']; ?>avdelare.png" alt="|" /> 
			<a href="javascript: void(0)" onclick="showHide('info')"><img src="<?php echo $conf['wpath']; ?>info.png" alt="Info" title="Info" border="0" /></a> <img src="<?php echo $conf['wpath']; ?>avdelare.png" alt="|" /> 
			<a href="<?php echo $conf['wpath']; ?>?action=logout"><img src="<?php echo $conf['wpath']; ?>logout.png" alt="Logga ut" title="Logga ut" border="0" /></a>
			<br />
		</p>
		<div id="info" class="hide">
			<p>
			Storängens Samskola AB, pg 509744-9<br />
			8 x 12 minus trappa, öppen spis<br />
			</p>
			<p>
			fredag-lördag: 12.30/12.00 - 01.00, - 11.00,<br />
			4500:- / 3900:- / 2900:-
			</p>
			<p>
			fredag + lördag 7500:-
			</p>
			<p>
			söndag: 12.00 - 23.00, - 11.00<br />
			3000:- / 2000:- / 1500:-
			</p>
			<p>
			måndag-torsdag: 13.30-23.00, - 11.00<br />
			2500:- / 1800:- / 1300:-
			</p>
			<p>
			barnkalas/skolklass 1000:- / 500:-<br />
			t o m åk 6 med föräldrar
			</p>
			<p>
			utan kök 750:-
			</p>
			<p>
			bara kaffe 1250:-
			</p>
			<p>
			30 års åldersgräns<br />
			60 pers<br />
			rökfritt<br />
			tidsgräns<br />
			dåliga knivar<br />
			marschaller<br />
			</p>
			<p>
			<b>Stående bokn</b><br />
			Klubben 3e fredagen varje månad
			Höst-/Vårmöte sista/näst-sista torsd. apr/nov
			</p>
		</div>
<?php } ?>
	</body>
</html><?php 
}
?>