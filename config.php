<?php
// Database variables
require_once('./config-db.php');

$conf['session_timeout'] = 20;
$conf['session_id'] = 'boknings_session';

$conf['charset'] = 'ISO-8859-1';

$conf['default_from'] = 'info@storangssalen.se';
$conf['froms'] = array('info@storangssalen.se','arvid@storangssalen.se');

$conf['to1'] = 'arvid@bjorkstrom.se,pelle@ssark.se,niklas.b.frank@gmail.com,anders.bergman@set-revision.se';
$conf['to2'] = 'arvid@bjorkstrom.se,bengt.hollberg@telia.com,y.g@storangensmontessori.se';

$conf['ipath'] = dirname(__FILE__);
$conf['wpath'] = '/bokning/';


/*
1   = Standard
2   = STVF
3   = Egnahem
4   = Funkt - suppleant
5   = Styrelse
 -1 = Standard
 -2 = Barnkalas / Skolklass
 -3 = Utan kök
 -4 = Bara kaffe
*/
// Mon - Thu
for($i=1;$i<5;$i++){
	$conf['rates'][$i]['1'] = 2500;
	$conf['rates'][$i]['1-1'] = 2500;
	$conf['rates'][$i]['1-2'] = 1000;
	$conf['rates'][$i]['1-3'] = 750;
	$conf['rates'][$i]['1-4'] = 1250;
	
	$conf['btype'][$i]['1'] = 'Vardag';
	$conf['btype'][$i]['1-1'] = 'Vardag';
	$conf['btype'][$i]['1-2'] = 'Barn- / Skolkalas';
	$conf['btype'][$i]['1-3'] = 'Utan kök';
	$conf['btype'][$i]['1-4'] = 'Enbart kaffe';
	
	$conf['rates'][$i]['2'] = 1800;
	$conf['rates'][$i]['2-1'] = 1800;
	$conf['rates'][$i]['2-2'] = 500;
	$conf['rates'][$i]['2-3'] = 750;
	$conf['rates'][$i]['2-4'] = 1250;
	
	$conf['btype'][$i]['2'] = 'Vardag, medlem STVF';
	$conf['btype'][$i]['2-1'] = 'Vardag, medlem STVF';
	$conf['btype'][$i]['2-2'] = 'Barn- / Skolkalas, medlem STVF';
	$conf['btype'][$i]['2-3'] = 'Utan kök, medlem STVF';
	$conf['btype'][$i]['2-4'] = 'Enbart kaffe, medlem STVF';
	
	$conf['rates'][$i]['3'] = 1300;
	$conf['rates'][$i]['3-1'] = 1300;
	$conf['rates'][$i]['3-2'] = 500;
	$conf['rates'][$i]['3-3'] = 750;
	$conf['rates'][$i]['3-4'] = 850;
	
	$conf['btype'][$i]['3'] = 'Vardag, medlem Storängens Egnahemsförening';
	$conf['btype'][$i]['3-1'] = 'Vardag, medlem Storängens Egnahemsförening';
	$conf['btype'][$i]['3-2'] = 'Barn- / Skolkalas, medlem Storängens Egnahemsförening';
	$conf['btype'][$i]['3-3'] = 'Utan kök, medlem Storängens Egnahemsförening';
	$conf['btype'][$i]['3-4'] = 'Enbart kaffe, medlem Storängens Egnahemsförening';
	
	$conf['rates'][$i]['4'] = 650;
	$conf['rates'][$i]['4-1'] = 650;
	$conf['rates'][$i]['4-2'] = 250;
	$conf['rates'][$i]['4-3'] = 375;
	$conf['rates'][$i]['4-4'] = 425;
	
	$conf['btype'][$i]['4'] = 'Vardag, Suppleant / Funktionär';
	$conf['btype'][$i]['4-1'] = 'Vardag, Suppleant / Funktionär';
	$conf['btype'][$i]['4-2'] = 'Barn- / Skolkalas, Suppleant / Funktionär';
	$conf['btype'][$i]['4-3'] = 'Utan kök, Suppleant / Funktionär';
	$conf['btype'][$i]['4-4'] = 'Enbart kaffe, Suppleant / Funktionär';
	
	$conf['rates'][$i]['5'] = 0;
	$conf['rates'][$i]['5-1'] = 0;
	$conf['rates'][$i]['5-2'] = 0;
	$conf['rates'][$i]['5-3'] = 0;
	$conf['rates'][$i]['5-4'] = 0;
	
	$conf['btype'][$i]['5'] = 'Vardag, Styrelsemedlem';
	$conf['btype'][$i]['5-1'] = 'Vardag, Styrelsemedlem';
	$conf['btype'][$i]['5-2'] = 'Barn- / Skolkalas, Styrelsemedlem';
	$conf['btype'][$i]['5-3'] = 'Utan kök, Styrelsemedlem';
	$conf['btype'][$i]['5-4'] = 'Enbart kaffe, Styrelsemedlem';
}
// Fri - Sat
for($i=5;$i<7;$i++){
	$conf['rates'][$i]['1'] = 6000;
	$conf['rates'][$i]['2'] = 3900;
	$conf['rates'][$i]['3'] = 2900;
	$conf['rates'][$i]['4'] = 1450;
	$conf['rates'][$i]['5'] = 0;
	
	$conf['btype'][$i]['1'] = 'Fredag / Lördag';
	$conf['btype'][$i]['2'] = 'Fredag / Lördag, medlem STVF';
	$conf['btype'][$i]['3'] = 'Fredag / Lördag, medlem Storängens Egnahemsförening';
	$conf['btype'][$i]['4'] = 'Fredag / Lördag, Suppleant / Funktionär';
	$conf['btype'][$i]['5'] = 'Fredag / Lördag, Styrelsemedlem';
}
// Sun
$conf['rates']['7']['1'] = 3000;
$conf['rates']['7']['2'] = 2000;
$conf['rates']['7']['3'] = 1500;
$conf['rates']['7']['4'] = 750;
$conf['rates']['7']['5'] = 0;

$conf['btype']['7']['1'] = 'Söndag';
$conf['btype']['7']['2'] = 'Söndag, medlem STVF';
$conf['btype']['7']['3'] = 'Söndag, medlem Storängens Egnahemsförening';
$conf['btype']['7']['4'] = 'Söndag, Suppleant / Funktionär';
$conf['btype']['7']['5'] = 'Söndag, Styrelsemedlem';

$conf['columns'] = array('bokningsid','datum','bokn','namn','phone','typ','info','bokad','epost','phone_alt','spec','sendinfo','payed');

setlocale(LC_ALL,'sv_SE');

?>