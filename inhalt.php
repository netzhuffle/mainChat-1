<?php
require_once("functions/functions.php");
$bereich = filter_input(INPUT_GET, 'bereich', FILTER_SANITIZE_URL);

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_URL);
if( $id == '') {
	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_URL);
}

$user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT);

$aktion = filter_input(INPUT_GET, 'aktion', FILTER_SANITIZE_STRING);
if( $aktion == '') {
	$aktion = filter_input(INPUT_POST, 'aktion', FILTER_SANITIZE_STRING);
}

// Vergleicht Hash-Wert mit IP und liefert u_id, o_id, o_raum
id_lese($id);

// Direkten Aufruf der Datei verbieten (nicht eingeloggt)
if( !isset($u_id) || $u_id == "") {
	die;
}

if( isset($u_id) && strlen($u_id) != 0 ) {
	$user_eingeloggt = true;
	
	// Ermitteln, ob sich der Benutzer im Chat oder im Forum aufhält
	if ($o_raum && $o_raum == "-1") {
		$wo_online = "forum";
		$reset = "1";
	} else {
		$wo_online = "chat";
		$reset = "0";
	}
} else {
	$user_eingeloggt = false;
}

// Wird die Seite aufgebaut?
$kein_seitenaufruf = false;

// Übersetzungen der entsprechenden Seite einbinden
if (file_exists("languages/$sprache-$bereich.php")) {
	require_once("languages/$sprache-$bereich.php");
} else {
	$kein_seitenaufruf = true;
}

// Hole alle benötigten Einstellungen des Benutzers
$benutzerdaten = hole_benutzer_einstellungen($u_id, "standard");

if($bereich == "log" && $aktion == "abspeichern") {
	// Falls Abspeichern, Header senden
	$dateiname = "log-" . date("YmdHi") . ".htm";
	
	header("Content-Description: File Transfer");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$dateiname\"");
	header("Content-Location: $dateiname");
	header("Cache-Control: maxage=15"); //In seconds
	header("Pragma: public");
	$minimalistisch = true;
} else {
	$minimalistisch = false;
}

$meta_refresh = "";
if($bereich == "sperren" || $bereich == "freunde" || $bereich == "nachrichten") {
$meta_refresh = "<script>
		function toggle(tostat) {
			for(i=0; i<document.forms[\"eintraege_loeschen\"].elements.length; i++) {
				 e = document.forms[\"eintraege_loeschen\"].elements[i];
				 if ( e.type=='checkbox' )
					 e.checked=tostat;
			}
		}
	</script>\n";
}

// Titel aus der entsprechenden Übersetzung holen
if($t['titel'] != '') {
	$title = $body_titel . ' - ' . $t['titel'];
} else {
	$title = $body_titel;
}

zeige_header($title, $benutzerdaten['u_layout_farbe'], $meta_refresh, $minimalistisch);

// Bestimmte Seiten dürfen nur im eingeloggten Zustand aufgerufen werden
$kein_aufruf_unter_bestimmten_bedinungen = false;
$nur_eingeloggten_seiten = array('raum', 'profil', 'profilbilder', 'einstellungen', 'log', 'benutzer');
$nur_eingeloggten_seiten_und_registriert = array('nachrichten', 'top10', 'freunde');
$nur_eingeloggten_seiten_und_admin = array('statistik', 'sperren');
if( !$user_eingeloggt && in_array($bereich, $nur_eingeloggten_seiten, true) ) {
	$kein_aufruf_unter_bestimmten_bedinungen = true;
} else if( !$user_eingeloggt || ($user_eingeloggt && $u_level == "G" && in_array($bereich, $nur_eingeloggten_seiten_und_registriert, true) ) ) {
	$kein_aufruf_unter_bestimmten_bedinungen = true;
} else if( $user_eingeloggt && !$admin && in_array($bereich, $nur_eingeloggten_seiten_und_admin, true) ) {
	$kein_aufruf_unter_bestimmten_bedinungen = true;
}
?>
<body>
<br>
<?php
if(!$bereich || $kein_seitenaufruf) {
	// Seite nicht gefunden oder Datei für die Übersetzung nicht gefunden
	$box = $t['fehler'];
	$text = $t['seite_nicht_gefunden'];
	zeige_tabelle_zentriert($box, $text);
} else if($kein_aufruf_unter_bestimmten_bedinungen) {
		// Seite nicht gefunden oder Datei für die Übersetzung nicht gefunden
		$box = $t['fehler'];
		$text = $t['kein_zugriff'];
		zeige_tabelle_zentriert($box, $text);
	} else {
	// Seitenaufruf
	switch ($bereich) {
		case "hilfe":
			// Hilfe anzeigen
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=hilfe&id=$id\">$t[hilfe_menue4]</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=hilfe&aktion=hilfe-befehle&id=$id\">$t[hilfe_menue5]</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=hilfe&aktion=hilfe-sprueche&id=$id\">$t[hilfe_menue6]</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=hilfe&aktion=hilfe-community&id=$id\">$t[hilfe_menue7]</a>\n";
			zeige_tabelle_zentriert($box, $text);
			
			switch ($aktion) {
				case "hilfe-befehle":
					// Liste aller Befehle anzeigen
					require_once('templates/hilfe-befehle.php');
					
					break;
					
				case "hilfe-sprueche":
					// Liste aller Sprüche anzeigen
					require_once('templates/hilfe-sprueche.php');
					
					break;
					
				case "hilfe-community":
					// Punkte/Community anzeigen
					require_once('templates/hilfe-community.php');
					
					break;
					
				default:
					// Hilfe anzeigen
					require_once('templates/hilfe.php');
			}
			break;
		
		case "benutzer":
			// Benutzer anzeigen
			require_once("functions/functions-benutzer.php");
			require_once("functions/functions-user.php");
			require_once("functions/functions-func-nachricht.php");
			require_once("functions/functions-formulare.php");
			require_once("languages/$sprache-einstellungen.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=benutzer&id=$id\">$t[benutzer_uebersicht]</a>\n";
			if ($u_level != "G") {
				$text .= "| <a href=\"inhalt.php?bereich=benutzer&id=$id&aktion=suche\">$t[benutzer_benutzer_suchen]</a>\n";
			}
			
			if ($adminlisteabrufbar && $u_level != "G") {
				$text .= "| <a href=\"inhalt.php?bereich=benutzer&id=$id&aktion=adminliste\">$t[benutzer_adminliste_anzeigen]</a>\n";
			}
			if ($u_level != "G") {
				if ($punktefeatures) {
					$ur1 = "inhalt.php?bereich=top10&id=$id";
					$url = "href=\"$ur1\" ";
					$text .= "| <a $url>$t[benutzer_top10]</a>\n";
				}
			}
			
			zeige_tabelle_zentriert($box, $text);
	
			require_once('templates/benutzer.php');
	
			break;
		
		case "raum":
			// Raum anzeigen
			require_once("functions/functions-msg.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=raum&id=$id\">" . $t['raum_menue8'] . "</a>\n";
			if ($u_level != "G") {
				$text .= "| <a href=\"inhalt.php?bereich=raum&aktion=neu&id=$id\">" . $t['raum_menue2'] . "</a>\n";
			}
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/raum.php');
			
			break;
		
		case "nachrichten":
			// Nachrichten anzeigen
			require_once("functions/functions-nachrichten.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=nachrichten&id=$id\">" . $t['nachrichten_posteingang'] . "</a>\n|\n"
				. "<a href=\"inhalt.php?bereich=nachrichten&aktion=postausgang&id=$id\">" . $t['nachrichten_postausgang'] . "</a>\n|\n"
				. "<a href=\"inhalt.php?bereich=nachrichten&aktion=neu&id=$id\">" . $t['nachrichten_neue_nachricht'] . "</a>\n|\n"
				. "<a href=\"inhalt.php?bereich=nachrichten&aktion=papierkorb&id=$id\">" . $t['nachrichten_papierkorb'] . "</a>\n|\n"
				. "<a href=\"inhalt.php?bereich=nachrichten&aktion=mailboxzu&id=$id\">" . $t['nachrichten_nachrichten_deaktivieren'] . "</a>\n|\n"
				. "<a href=\"inhalt.php?bereich=hilfe&id=$id&aktion=hilfe-community#mail\">" . $t['nachrichten_hilfe'] . "</a>\n";
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/nachrichten.php');
			
			break;
		
		case "profil":
			// Profil anzeigen
			require_once("functions/functions-profil.php");
			require_once("functions/functions-formulare.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text .= "<a href=\"inhalt.php?bereich=profil&id=$id\">$t[profil_profil_bearbeiten]</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=profilbilder&id=$id\">$t[profil_bilder_hochladen]</a>\n";
			if ($admin) {
				$text .= "| <a href=\"inhalt.php?bereich=profil&id=$id&aktion=zeigealle\">$t[profil_alle_profile_ausgeben]</a>\n";
			}
			zeige_tabelle_zentriert($box, $text);
				
			require_once('templates/profil.php');
				
			break;
			
		case "profilbilder":
			// Profil anzeigen
			require_once("functions/functions-profilbilder.php");
			require_once("functions/functions-formulare.php");
			require_once("languages/$sprache-profil.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text .= "<a href=\"inhalt.php?bereich=profil&id=$id\">$t[profil_profil_bearbeiten]</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=profilbilder&id=$id\">$t[profil_bilder_hochladen]</a>\n";
			if ($admin) {
				$text .= "| <a href=\"inhalt.php?bereich=profil&id=$id&aktion=zeigealle\">$t[profil_alle_profile_ausgeben]</a>\n";
			}
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/profilbilder.php');
			
			break;
		
		case "einstellungen":
			// Einstellungen anzeigen
			require_once("languages/$sprache-benutzer.php");
			
			require_once("functions/functions-msg.php");
			require_once("functions/functions-einstellungen.php");
			require_once("functions/functions-formulare.php");
			
			// Menü ausgeben
			if ($u_level != "G") {
				$box = $t['titel'];
				$text .= "<a href=\"inhalt.php?bereich=einstellungen&id=$id\">$t[einstellungen_menue1]</a>\n";
				$text .= "| <a href=\"inhalt.php?bereich=einstellungen&aktion=aktion&id=$id\">$t[einstellungen_menue2]</a>\n";
				$text .= "| <a href=\"inhalt.php?bereich=hilfe&id=$id&aktion=hilfe-community#home\">$t[einstellungen_menue3]</a>\n";
				zeige_tabelle_zentriert($box, $text);
			}
			
			switch ($aktion) {
				case "aktion-eintragen":
					// Ab in die Datenbank mit dem Eintrag
					eintrag_aktionen($aktion_datensatz);
					zeige_aktionen();
					
					$box = $t['einstellungen_tipps_benachrichtigungen_titel'];
					$text = $t['einstellungen_tipps_benachrichtigungen_inhalt'];
					zeige_tabelle_zentriert($box, $text);
					break;
					
				case "aktion":
					zeige_aktionen();
					
					$box = $t['einstellungen_tipps_benachrichtigungen_titel'];
					$text = $t['einstellungen_tipps_benachrichtigungen_inhalt'];
					zeige_tabelle_zentriert($box, $text);
					break;
					
				default:
					require_once('templates/einstellungen.php');
			}
			
			break;
		
		case "statistik":
			// Statistik anzeigen
			require_once("functions/functions-statistik.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "[<a href=\"inhalt.php?bereich=statistik&aktion=monat&id=$id\">" . $t['statistik_nach_monaten'] . "</a>]\n"
					. "[<a href=\"inhalt.php?bereich=statistik&aktion=stunde&id=$id\">" . $t['statistik_nach_stunden'] . "</a>]";
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/statistik.php');
			
			break;
		
		case "sperren":
			// Sperren anzeigen
			require_once("functions/functions-sperren.php");
			
			// Menü ausgeben
			if (isset($uname)) {
				$zusatztxt = "'" . $uname . "'&nbsp;&gt;&gt;&nbsp;";
			} else {
				$zusatztxt = "";
				$uname = "";
			}
			
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=sperren&id=$id\">$t[sperren_menue1]</a>\n" . "| <a href=\"inhalt.php?bereich=sperren&id=$id&aktion=neu\">$t[sperren_menue2]</a>\n";
			
			$query = "SELECT is_domain FROM ip_sperre WHERE is_domain = '-GLOBAL-'";
			$result = sqlQuery($query);
			if ($result && mysqli_num_rows($result) > 0) {
				$text .= "| <a href=\"inhalt.php?bereich=sperren&id=$id&aktion=loginsperre0\">$t[sperren_menue5a]</a>\n";
			} else {
				$text .= "| <a href=\"inhalt.php?bereich=sperren&id=$id&aktion=loginsperre1\">$t[sperren_menue5b]</a>\n";
			}
			mysqli_free_result($result);
			
			$query = "SELECT is_domain FROM ip_sperre WHERE is_domain = '-GAST-'";
			$result = sqlQuery($query);
			if ($result && mysqli_num_rows($result) > 0) {
				$text .= "| <a href=\"inhalt.php?bereich=sperren&id=$id&aktion=loginsperregast0\">$t[sperren_menue6a]</a>\n";
			} else {
				$text .= "| <a href=\"inhalt.php?bereich=sperren&id=$id&aktion=loginsperregast1\">$t[sperren_menue6b]</a>\n";
			}
			mysqli_free_result($result);
			
			$text .= "| <a href=\"inhalt.php?bereich=sperren&aktion=blacklist&id=$id&neuer_blacklist[u_nick]=$uname\">" . $zusatztxt . $t['sperren_menue3'] . "</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=sperren&aktion=blacklist_neu&id=$id\">" . $t['sperren_menue6'] . "</a>\n";
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/sperren.php');
				
			break;
			
		case "freunde":
			// Freunde anzeigen
			require_once("functions/functions-freunde.php");
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=freunde&id=$id\">$t[freunde_meine_freunde]</a>\n"
			. "| <a href=\"inhalt.php?bereich=freunde&aktion=neu&id=$id\">$t[freunde_neuen_freund_hinzufuegen]</a>\n"
			. "| <a href=\"inhalt.php?bereich=freunde&aktion=bestaetigen&id=$id\">$t[freunde_freundesanfragen]</a>\n";
			if ($admin) {
				$text .= "| <a href=\"inhalt.php?bereich=freunde&aktion=admins&id=$id\">$t[freunde_alle_admins_als_freund_hinzufuegen]</a>\n";
			}
			$text .= "| <a href=\"inhalt.php?bereich=hilfe&id=$id&aktion=hilfe-community#freunde\">$t[freunde_hilfe]</a>\n";
			zeige_tabelle_zentriert($box, $text);
			
			require_once('templates/freunde.php');
			
			break;
				
		case "top10":
			// Top 10/100 anzeigen
			
			// Menü ausgeben
			$box = $t['titel'];
			$text = "<a href=\"inhalt.php?bereich=top10&id=$id\">".$t['top_menue2']."</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=top10&aktion=top100&id=$id\">".$t['top_menue3']."</a>\n";
			$text .= "| <a href=\"inhalt.php?bereich=hilfe&aktion=hilfe-community#punkte&id=$id\">".$t['top_menue4']."</a>\n";
			zeige_tabelle_zentriert($box, $text);
				
			require_once('templates/top10.php');
			
			break;
			
		case "log":
			// Log anzeigen
			require_once("functions/functions-log.php");
			
			require_once('templates/log.php');
			
			break;
		
		default:
			// Seite nicht gefunden
			$box = $t['fehler'];
			$text = $t['seite_nicht_gefunden'];
			zeige_tabelle_zentriert($box, $text);
	}
}
?>
</body>
</html>