<?php
// Direkten Aufruf der Datei verbieten
if( !isset($u_id) || $u_id == NULL || $u_id == "") {
	die;
}

// Benutzerseite für Benutzer $u_id bearbeiten
$text = "";

// Bild löschen
if (substr($bildname, 0, 7) == "ui_bild") {
	$text .= bild_loeschen($bildname, $u_id);
}

// Prüfen & in DB schreiben
if ($ui_id != "") {
	$home = array();
	$home['ui_id'] = $ui_id;
	// Bei Änderung der Einstellung speichern
	// hochgeladene Bilder in DB speichern
	$bildliste = ARRAY("ui_bild1", "ui_bild2", "ui_bild3", "ui_bild4", "ui_bild5", "ui_bild6");
	foreach ($bildliste as $val) {
		if (isset($_FILES[$val]) && is_uploaded_file($_FILES[$val]['tmp_name'])) {
			// Abspeichern
			$fehlermeldung = bild_holen($u_id, $val, $_FILES[$val]['tmp_name'], $_FILES[$val]['size']);
			
			if ($fehlermeldung != "") {
				// Fehlermeldungen anzeigen
				$text .= hinweis($fehlermeldung, "fehler");
			} else {
				$erfolgsmeldung = $lang['profilbilder_erfolgsmeldung_bild_hochgeladen'];
				$text .= hinweis($erfolgsmeldung, "erfolgreich");
			}
		}
	}
	
	// Änderungen in DB schreiben
	$ui_id = schreibe_db("userinfo", $home, $home['ui_id'], "ui_id");
}

// Daten laden und Editor anzeigen
$query = "SELECT * FROM userinfo WHERE ui_userid=$u_id";
$result = sqlQuery($query);
if ($result && mysqli_num_rows($result) == 1) {
	unset($home);
	$home = array();
	$home = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	// HP-Tabelle ausgeben
	$box = $lang['profil_bilder_hochladen'];
	
	$text .= "<form enctype=\"multipart/form-data\" name=\"home\" action=\"inhalt.php?bereich=profilbilder\" method=\"post\">\n"
	$text .= "<input type=\"hidden\" name=\"aktion\" value=\"aendern\">\n";
	$text .= "<input type=\"hidden\" name=\"ui_userid\" value=\"$u_id\">\n";
	$text .= "<input type=\"hidden\" name=\"ui_id\" value=\"" . $home['ui_id'] . "\">\n";
	
	$text .= home_info($home);
	
	$text .= "</form>";
	
	// Box anzeigen
	zeige_tabelle_zentriert($box, $text);
} else {
	// Erst Profil anlegen
	zeige_tabelle_zentriert($lang['neues_profil'], $lang['neues_profil_beschreibung']);
}
mysqli_free_result($result);
?>