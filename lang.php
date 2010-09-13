<?php
class Lang {
	private $conf;
	private $DB;
	private $err;
	private $lang;
	
	public function __construct(&$conf, $DB,$err) {
		$this->conf =& $conf;
		$this->DB = $DB;
		$this->err = $err;
		$this->lang = array(
		'lbl_username' => 'Användarnamn',
		'lbl_password' => 'Lösenord',
		'lbl_login' => 'Logga in',
		'lbl_save_changes' => 'Spara ändringar',
		'lbl_save_patient' => 'Spara patient',
		'lbl_delete_patient' => 'Radera patient',
		'lbl_monday' => 'måndag',
		'lbl_tuesday' => 'tisdag',
		'lbl_wednesday' => 'onsdag',
		'lbl_thursday' => 'torsdag',
		'lbl_friday' => 'fredag',
		'lbl_saturday' => 'lördag',
		'lbl_sunday' => 'söndag',
		'lbl_add_visit' => 'Lägg till besök',
		'lbl_week' => 'Vecka',
		'txt_booking_details' => "
Hyran av salen är beräknad till {{rent}}, och erläggs minst tre veckor i förskott genom insättning på AB Storängens Samskolas plusgirokonto 50 97 44-9.

Bokningsdatum: {{datum}}
Bokningstyp: {{bookingtype}}
Beräknad hyra: {{rent}}

Namn: {{namn}}
Telefon: {{phone}}
Alternativ tel: {{phone_alt}}
Epost: {{epost}}

Om du vill avboka Storängssalen måste du göra det senast 14 dagar innan bokningsdatumet för att undvika straffavgifter. Svara på det här mailet eller ring på 08 - 4100 7997.
",
		'txt_reminder_subj' => 'Din bokning av Storängssalen, ',
		'txt_reminder_message' => "Hej {{namn}}!

Det här är en påminnelse om din bokning av Storängssalen {{datum}}.
{{addin}}",
		'txt_reminder_prel' => "Din bokning är än så länge preliminär, för att bekräfta eller säga upp den kontakta oss på info@storangssalen.se.
",
		'txt_reminder_prel_21' => "Din bokning är fortfarande markerad som preliminär. Om du inte bekräftar din bokning inom en vecka blir den tillgänglig för andra som vill boka lokalen.
",
		'txt_reminder_keys' => "Det är en vecka till din bokning av Storängssalen. Svara på det här mailet, eller slå en signal till 08 - 4100 7997 och meddelan när du har möjlighet att hämta nyckeln så stämmer bestämmer vi en tid som passar oss båda!
",
		'txt_booking_subj' => "Bokning av Storängssalen ",
		'txt_booking_subj_prel' => "Preliminär bokning av Storängssalen ",
		'txt_booking_subj_cancel' => "Avbokning av Storängssalen ",

		'txt_booking_update_prel' => "Hej {{namn}}!

Informationen kring din bokning av Storängssalen är uppdaterad.

Din bokning är fortfarande markerad som preliminär. Bekräfta gärna din bokning så snart som möjligt genom att svara på det här mailet, eller ringa på 08 - 4100 7997.
",
		'txt_booking_update' => "Hej {{namn}}!

Informationen kring din bokning av Storängssalen är uppdaterad.
",
		'txt_booking_prel' => "Hej {{namn}}!

Detta är en bekräftelse av din preliminära bokning av Storängssalen {{datum}}. Bekräfta gärna så snart som möjligt, men senast en månad innan bokningsdatumet, om du vill behålla bokningen.
",
		'txt_booking_final' => "Hej {{namn}}!

Detta är en bekräftelse av din bokning av Storängssalen {{datum}}.
",
		'txt_booking_cancel' => "Hej {{namn}}!

Detta är en bekräftelse av din AVBOKNING av Storängssalen {{datum}}. Om detta är felaktigt, svara på det här mailet så snart som möjligt så vi kan ordna misstaget som måste ha skett!
",
		'txt_signature' => "
--
Läs mer om salen och se bokningskalendern på: http://www.storangssalen.se/
Det här mailet är automatiskt genererat. Om du anser att något är felaktigt, svara gärna på mailet och berätta vad som är fel.

vänliga hälsningar,
Arvid Björkström
Storängssalen
08 - 4100 7997
info@storangssalen.se",
		);
	}
	
	public function get($label) {
		return $this->lang[$label];
	}
}
?>