<?php

// chat.php muss mit id=$hash_id aufgerufen werden
// Optional kann $trigger_letzte_Zeilen als Trigger für die Ausgabe der letzten n-Zeilen angegeben werden

require_once("functions.php");

// Benutzerdaten setzen
id_lese($id);

// Zeit in Sekunden bis auch im Normalmodus die Seite neu geladen wird
$refresh_zeit = 600;

// Systemnachrichten ausgeben
$sysmsg = TRUE;

$title = $body_titel;
zeige_header_anfang($title, 'chatausgabe', '', $u_layout_farbe);

// Benutzerdaten gesetzt?
if ($u_id) {
	
	// Timestamp im Datensatz aktualisieren -> User im Chat / o_who=0
	aktualisiere_online($u_id, $o_raum, 0);
	
	// eigene Farbe für BG gesetzt? dann die nehmen.
	if ($u_farbe_bg != "" && $u_farbe_bg != "-") {
		$farbe_chat_background1 = $u_farbe_bg;
		$meta_refresh = "<style>body {background-color:#$u_farbe_bg !important;}</style>";
	} else {
		$meta_refresh = "";
	}
	
	// Algorithmus wählen
	if ($sicherer_modus == 1 || $u_sicherer_modus == "Y") {
		// n-Zeilen ausgeben und nach Timeout neu laden
		$meta_refresh .= '<meta http-equiv="refresh" content="7; URL=chat.php?id=' . $id . '">';
		$meta_refresh .= "<script>\n setInterval(\"window.scrollTo(1,300000)\",100)\n</script>";
		zeige_header_ende($meta_refresh);
		?>
		<body>
		<?php
		
		// Chatausgabe, $letzte_id ist global
		chat_lese($o_id, $o_raum, $u_id, $sysmsg, $ignore, $chat_back);
	} else {
		// Endlos-Push-Methode - Normalmodus
		
		// Kopf ausgeben
		// header("Content-Type: multipart/mixed;boundary=myboundary");
		// echo "\n--myboundary\n";
		// echo "Content-Type: text/html\n\n";
		//echo "<html><head>".
		//	"<script language=JavaScript>\n".
		//	"function scroll() {\n".
		//	"window.scrollTo(0,50000);\n".
		//	"setTimeout(\"scroll()\",200);\n".
		//	"}\n".
		//	"setTimeout(\"scroll()\",100);\n".
		//	"</script>\n".
		//	"$stylesheet</HEAD>\n";
		
		$meta_refresh .= "<meta http-equiv=\"expires\" content=\"0\" />\n"
			. "<script language=JavaScript>\n"
			. "setInterval(\"window.scrollTo(1,300000)\",100)\n</script>\n";
		zeige_header_ende($meta_refresh);
		
		// Voreinstellungen
		$j = 0;
		$i = 0;
		$beende_prozess = FALSE;
		set_time_limit($refresh_zeit + 30);
		ignore_user_abort(FALSE);
		
		// 1 Sek pro Durchlauf fest eingestellt
		$durchlaeufe = $refresh_zeit;
		$zeige_userliste = 100;
		
		while ($j < ($durchlaeufe) && !$beende_prozess){
			// Raum merken
			$o_raum_alt = $o_raum;
			
			// Bin ich noch online?
			$result = mysqli_query(	$conn,	"SELECT HIGH_PRIORITY o_raum,o_ignore FROM online WHERE o_id=" . $o_id . " ");
			if ($result > 0) {
				if (mysqli_Num_Rows($result) == 1) {
					$row = mysqli_fetch_object($result);
					$o_raum = $row->o_raum;
					$ignore = unserialize($row->o_ignore);
				} else {
					// Raus aus dem Chat, vorher kurz warten
					sleep(10);
					$beende_prozess = TRUE;
				}
				mysqli_free_result($result);
			}
			
			$j++;
			$i++;
			
			// Falls nach mehr als 100 sek. keine Ausgabe erfolgt, Userliste anzeigen
			// Nach > 120 Sekunden schlagen bei einigen Browsern Timeouts zu ;)
			if ($i > $zeige_userliste) {
				if (isset($raum_msg) && $raum_msg != "AUS") {
					system_msg("", 0, $u_id, $system_farbe, $raum_msg);
				} else {
					raum_user($o_raum, $u_id);
				}
				$i = 0;
			}
			
			// Raumwechsel?
			if (($trigger_letzte_Zeilen == 0) && ($o_raum != $o_raum_alt)) {
				// Trigger für die letzten Nachrichten setzen
				$trigger_letzte_Zeilen = 1;
			}
			
			// Chatausgabe, $letzte_id ist global
			// Falls Result=wahr wurde Text ausgegeben, Timer für Userliste zurücksetzen
			if (chat_lese($o_id, $o_raum, $u_id, $sysmsg, $ignore, $trigger_letzte_Zeilen)) {
				$i = 0;
			}
			
			// Trigger zurücksetzen
			$trigger_letzte_Zeilen = 0;
			
			// 0,2 oder 1 Sekunde warten
			sleep(1);
			
			// Ausloggen falls Browser abgebrochen hat
			if (connection_status() != 0) {
				// Verbindung wurde abgebrochen -> Schleife verlassen
				$beende_prozess = TRUE;
			}
			
		}
		
		// Trigger für die Ausgabe der letzten 20 Nachrichten setzen
		$trigger_letzte_Zeilen = 20;
		
		// echo "\n\n--myboundary\nContent-Type: text/html\n\n";
		echo "<body onLoad='parent.chat.location=\"chat.php?id=$id&back=$trigger_letzte_Zeilen\"'>";
		flush();
		
	}

} else {
	zeige_header_ende();
	// Auf Chat-Eingangsseite leiten
	?>
	<body onLoad='javascript:parent.location.href="index.php"'>
	<?php
}
?>
</body>
</html>