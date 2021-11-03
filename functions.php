<?php
// Version / Copyright - nicht entfernen!
$mainchat_version = "mainChat 7.0.6 © by <a href=\"https://www.fidion.de\" target=\"_blank\">fidion GmbH</a> 1999-2018 - PHP7 Anpassung ab 2021 durch <a href=\"https://www.anime-community.de\" target=\"_blank\">Andreas Völkl</a> auf <a href=\"https://github.com/ePfirsich/mainChat\" target=\"_blank\">GitHub</a>";

// Caching unterdrücken
Header("Last-Modified: " . gmDate("D, d M Y H:i:s", Time()) . " GMT");
Header("Expires: " . gmDate("D, d M Y H:i:s", Time() - 3601) . " GMT");
Header("Pragma: no-cache");
Header("Cache-Control: no-cache");

// Variable initialisieren, einbinden, Community-Funktionen laden
if (!(isset($functions_init_geladen))) {
	require_once "functions-init.php";
}
if ($communityfeatures) {
	require_once "functions-community.php";
}

// DB-Connect, ggf. 3 mal versuchen
for ($c = 0; $c++ < 3 AND (!(isset($mysqli_link)));) {
	$mysqli_link = mysqli_connect('p:'.$mysqlhost, $mysqluser, $mysqlpass, $dbase);
	if ($mysqli_link) {
		mysqli_set_charset($mysqli_link, "utf8mb4");
		mysqli_select_db($mysqli_link, $dbase);
	}
}

if (!(isset($mysqli_link))) {
	echo "Beim Zugriff auf die Datenbank ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal!<br>";
	exit;
}

// Deklaration der gültigen Tabellenfelder die in die Datenbank geschrieben werden dürfen 
$valid_fields = array(
	'aktion' => array('a_id', 'a_user', 'a_wann', 'a_was', 'a_wie', 'a_text', 'a_zeit'),
	'bild' => array('b_id', 'b_user', 'b_name', 'b_bild', 'b_mime', 'b_width', 'b_height'),
	'blacklist' => array('f_id', 'f_userid', 'f_blacklistid', 'f_zeit', 'f_text'),
	'chat' => array('c_id', 'c_von_user', 'c_an_user', 'c_typ', 'c_raum', 'c_text', 'c_zeit', 'c_farbe', 'c_von_user_id', 'c_br'),
	'forum' => array('fo_id', 'fo_name', 'fo_order', 'fo_admin'),
	'freunde' => array('f_id', 'f_userid', 'f_freundid', 'f_zeit', 'f_text', 'f_status'),
	'iignore' => array('i_id', 'i_user_aktiv', 'i_user_passiv'),
	'invite' => array('inv_id', 'inv_raum', 'inv_user'),
	'ip_sperre' => array('is_id', 'is_ip', 'is_domain', 'is_zeit', 'is_ip_byte', 'is_owner', 'is_infotext', 'is_warn'),
	'mail' => array('m_id', 'm_status', 'm_von_uid', 'm_an_uid', 'm_zeit', 'm_geloescht_ts', 'm_betreff', 'm_text'),
	'mail_check' => array('email', 'datum', 'u_id'),
	'moderation' => array('c_id', 'c_von_user', 'c_an_user', 'c_typ', 'c_raum', 'c_text', 'c_zeit', 'c_farbe', 'c_von_user_id', 'c_moderator'),
	'online' => array('o_id', 'o_user', 'o_raum', 'o_hash', 'o_timestamp', 'o_ip', 'o_who', 'o_aktiv', 'o_chat_id', 'o_browser', 'o_name', 'o_js', 'o_knebel', 
		'o_http_stuff', 'o_http_stuff2', 'o_userdata', 'o_userdata2', 'o_userdata3', 'o_userdata4', 'o_level', 'o_ignore', 'o_login', 'o_punkte', 'o_aktion', 
		'o_timeout_zeit', 'o_timeout_warnung', 'o_chat_historie', 'o_spam_zeilen', 'o_spam_byte', 'o_spam_zeit'),
	'posting' => array('po_id', 'po_th_id', 'po_u_id', 'po_vater_id', 'po_ts', 'po_tiefe', 'po_threadorder', 'po_threadts', 'po_gesperrt', 'po_threadgesperrt', 
		'po_topposting', 'po_titel', 'po_text'),
	'raum' => array('r_id', 'r_name', 'r_eintritt', 'r_austritt', 'r_status1', 'r_besitzer', 'r_topic', 'r_status2', 'r_smilie', 'r_werbung', 'r_min_punkte'), 
	'sequence' => array('se_name', 'se_nextid'),
	'sperre' => array('s_id', 's_user', 's_raum', 's_zeit'),
	'thema' => array('th_id', 'th_fo_id', 'th_name', 'th_desc', 'th_anzthreads', 'th_anzreplys', 'th_postings', 'th_order'),
	'top10cache' => array('t_id', 't_zeit', 't_eintrag', 't_daten'),
	'user' => array('u_id', 'u_neu', 'u_login', 'u_auth', 'u_nick', 'u_passwort', 'u_adminemail', 'u_email', 'u_url', 'u_level', 'u_farbe', 
		'u_farbe_alle', 'u_farbe_noise', 'u_farbe_priv', 'u_farbe_bg', 'u_farbe_sys', 'u_clearedit', 'u_away', 'u_ip_historie', 'u_smilie', 'u_agb', 
		'u_zeilen', 'u_punkte_gesamt', 'u_punkte_monat', 'u_punkte_jahr', 'u_punkte_datum_monat', 'u_punkte_datum_jahr', 'u_punkte_gruppe', 'u_gelesene_postings',
		'u_chathomepage', 'u_eintritt', 'u_austritt', 'u_signatur', 'u_lastclean', 'u_loginfehler', 
		'u_nick_historie', 'u_profil_historie', 'u_kommentar', 'u_forum_postingproseite', 'u_systemmeldungen', 'u_punkte_anzeigen', 'u_knebel', 'avatar_status', 'ui_avatar'),
	'userinfo' => array('ui_id', 'ui_userid', 'ui_strasse', 'ui_plz', 'ui_ort', 'ui_land', 'ui_geburt', 'ui_geschlecht', 'ui_beziehung', 'ui_typ', 'ui_beruf', 'ui_hobby', 
		'ui_tel', 'ui_fax', 'ui_handy', 'ui_icq', 'ui_text', 'ui_farbe', 'ui_einstellungen')
);

// Funktionen
function mysqli_result($res,$row=0,$col=0){
	$numrows = mysqli_num_rows($res);
	if ($numrows && $row <= ($numrows-1) && $row >=0){
		mysqli_data_seek($res,$row);
		$resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
		if (isset($resrow[$col])){
			return $resrow[$col];
		}
	}
	return false;
}

function onlinezeit($onlinezeit) {
	// Alternative zu gmdate("H:i:s", $onlinezeit), jedoch geht hier Std > 24
	$zeit = ":" . substr("00" . ($onlinezeit % 60), -2); // Sekunden
	$onlinezeit = floor($onlinezeit / 60);
	$zeit = ":" . substr("00" . ($onlinezeit % 60), -2) . $zeit;
	$onlinezeit = floor($onlinezeit / 60);
	$zeit = substr("00" . ($onlinezeit), -2) . $zeit;
	return ($zeit);
}

function raum_user($r_id, $u_id, $id) {
	// Gibt die Benutzer im Raum r_id im Text an $u_id aus
	
	global $timeout, $t, $leveltext, $mysqli_link, $beichtstuhl, $admin, $lobby, $unterdruecke_user_im_raum_anzeige;
	
	if ($unterdruecke_user_im_raum_anzeige != "1") {
		$query = "SELECT r_name,r_besitzer,o_user,o_name,o_userdata,o_userdata2,o_userdata3,o_userdata4 "
			. "FROM raum,online WHERE r_id=$r_id AND o_raum=r_id "
			. "AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(o_aktiv)) <= $timeout "
			. "ORDER BY o_name";
		
		$result = mysqli_query($mysqli_link, $query);
		$rows = @mysqli_num_rows($result);
		
		if ($result && $rows > 0) {
			$i = 0;
			while ($row = mysqli_fetch_object($result)) {
				// Beim ersten Durchlauf Namen des Raums einfügen
				if ($i == 0) {
					$text = str_replace("%r_name%", $row->r_name, $t['raum_user1']);
				}
				
				// Benutzerdaten lesen, Liste ausgeben
				$userdata = unserialize(
					$row->o_userdata . $row->o_userdata2 . $row->o_userdata3
						. $row->o_userdata4);
				
				// Variable aus o_userdata setzen, Level und away beachten
				$uu_id = $userdata['u_id'];
				$uu_level = $userdata['u_level'];
				
				// Im Beichtstuhl-Modus Benutzernamen anonymisieren
				if ($beichtstuhl && !$admin && $row->r_name != $lobby
					&& $uu_id != $u_id && $uu_level != "C" && $uu_level != "S") {
					$uu_nick = $t['raum_user11'];
				} else {
					$uu_nick = user($userdata['u_id'], $userdata, FALSE, FALSE);
				}
				
				if ($userdata['u_away'] != "") {
					$text .= "(" . $uu_nick . ")";
				} else {
					$text .= $uu_nick;
				}
				
				$i++;
				if ($i < $rows) {
					$text = $text . ", ";
				}
			}
			
		} else {
			$text = "Da ist niemand.";
		}
		$back = system_msg("", 0, $u_id, "", $text);
		
		mysqli_free_result($result);
	} else {
		$back = 1;
	}
	
	return ($back);
	
}

function ist_online($user) {
	// Prüft ob Benutzer noch online ist
	// liefert 1 oder 0 zurück
	
	global $timeout, $ist_online_raum, $mysqli_link, $whotext;
	
	$ist_online_raum = "";
	
	$user = mysqli_real_escape_string($mysqli_link, $user); // sec
	
	$query = "SELECT o_id,r_name FROM online left join raum on r_id=o_raum "
		. "WHERE o_user=$user "
		. "AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(o_aktiv)) <= $timeout";
	
	$result = mysqli_query($mysqli_link, $query);
	
	if ($result && mysqli_num_rows($result) > 0) {
		$ist_online_raum = mysqli_result($result, 0, "r_name");
		if (!$ist_online_raum || $ist_online_raum == "NULL")
			$ist_online_raum = "[" . $whotext[2] . "]";
		mysqli_free_result($result);
		return (1);
	} else {
		mysqli_free_result($result);
		return (0);
	}
}

function schreibe_moderiert($f) {
	// Schreibt Chattext in DB
	
	global $mysqli_link;
	// Schreiben falls text>0
	if (strlen($f['c_text']) > 0) {
		$back = schreibe_db("moderation", $f, "", "c_id");
	} else {
		$back = 0;
	}
	return ($back);
}

function schreibe_moderation() {
	global $u_id;
	global $mysqli_link;
	
	// alles aus der moderationstabelle schreiben, bei der u_id==c_moderator;
	$query = "SELECT * FROM moderation WHERE c_moderator=$u_id AND c_typ='N'";
	$result = mysqli_query($mysqli_link, $query);
	if ($result > 0) {
		while ($f = mysqli_fetch_array($result)) {
			unset($c);
			// vorbereiten für umspeichern... geht leider nicht 1:1, 
			// weil fetch_array mehr zurückliefert als in $f[] sein darf...
			$c['c_von_user'] = $f['c_von_user'];
			$c['c_an_user'] = $f['c_an_user'];
			$c['c_typ'] = $f['c_typ'];
			$c['c_raum'] = $f['c_raum'];
			$c['c_text'] = $f['c_text'];
			$c['c_farbe'] = $f['c_farbe'];
			$c['c_zeit'] = $f['c_zeit'];
			$c['c_von_user_id'] = $f['c_von_user_id'];
			// und in moderations-tabelle schreiben
			schreibe_chat($c);
			// und datensatz löschen...
			$query = "DELETE FROM moderation WHERE c_id=$f[c_id]";
			$result2 = mysqli_query($mysqli_link, $query);
		}
	}
}

function schreibe_chat($f) {
	// Schreibt Chattext in DB
	global $mysqli_link;
	
	// Schreiben falls text > 0
	if (isset($f['c_text']) && strlen($f['c_text']) > 0) {
		// Falls Länge c_text mehr als 256 Zeichen, auf mehrere Zeilen aufteilen
		if (strlen($f['c_text']) > 256) {
			$temp = $f['c_text'];
			$laenge = strlen($temp);
			$i = 0;
			// Tabelle LOCK
			$result = mysqli_query($mysqli_link, "LOCK TABLES chat WRITE");
			while ($i < $laenge) {
				$f['c_text'] = substr($temp, $i, 255);
				if ($i == 0) {
					// erste Zeile
					$f['c_br'] = "erste";
				} elseif (($i + 255) >= $laenge) {
					// letzte Zeile
					$f['c_br'] = "letzte";
				} else {
					// mittlere Zeile
					$f['c_br'] = "mitte";
				}
				$i = $i + 255;
				$back = schreibe_db("chat", $f, "", "c_id");
			}
			$result = mysqli_query($mysqli_link, "UNLOCK TABLES chat");
		} else {
			// Normale Zeile in Tabelle schreiben
			$f['c_br'] = "normal";
			$back = schreibe_db("chat", $f, "", "c_id");
		}
	} else {
		$back = 0;
	}
	return ($back);
}

function logout($o_id, $u_id, $info = "") {
	// Logout aus dem Gesamtsystem
	
	global $u_farbe, $mysqli_link, $communityfeatures;
	
	// Tabellen online+user exklusiv locken
	$query = "LOCK TABLES online WRITE, user WRITE";
	$result = mysqli_query($mysqli_link, $query);
	
	$o_id = mysqli_real_escape_string($mysqli_link, $o_id); // sec
	
	// Aktuelle Punkte auf Punkte in Benutzertabelle addieren
	$result = @mysqli_query($mysqli_link,  "SELECT o_punkte,o_name,o_knebel, UNIX_TIMESTAMP(o_knebel)-UNIX_TIMESTAMP(NOW()) AS knebelrest FROM online WHERE o_id=$o_id");
	if ($result && mysqli_num_rows($result) == 1) {
		$row = mysqli_fetch_object($result);
		$u_nick = $row->o_name;
		if ($row->knebelrest > 0) {
			$knebelzeit = $row->o_knebel;
		} else {
			$knebelzeit = NULL;
		}
		
		$query = "update user set "
			. "u_punkte_monat=u_punkte_monat+$row->o_punkte, "
			. "u_punkte_jahr=u_punkte_jahr+$row->o_punkte, "
			. "u_punkte_gesamt=u_punkte_gesamt+$row->o_punkte, "
			. "u_knebel='$knebelzeit' " . "where u_id=$u_id";
		$result2 = mysqli_query($mysqli_link, $query);
	}
	mysqli_free_result($result);
	
	// Benutzer löschen
	$result2 = mysqli_query($mysqli_link, 
		"DELETE FROM online WHERE o_id=$o_id OR o_user=$u_id");
	
	// Lock freigeben
	$query = "UNLOCK TABLES";
	$result = mysqli_query($mysqli_link, $query);
	
	// Punkterepair 
	$repair1 = "UPDATE user SET u_punkte_jahr = 0, u_punkte_monat = 0, u_punkte_datum_jahr = YEAR(NOW()), u_punkte_datum_monat = MONTH(NOW()), u_login=u_login WHERE u_punkte_datum_jahr != YEAR(NOW()) AND u_id=$u_id";
	mysqli_query($mysqli_link, $repair1);
	$repair2 = "UPDATE user SET u_punkte_monat = 0, u_punkte_datum_monat = MONTH(NOW()), u_login=u_login WHERE u_punkte_datum_monat != MONTH(NOW()) AND u_id=$u_id";
	mysqli_query($mysqli_link, $repair2);
	
	// Nachrichten an Freunde verschicken
	if ($communityfeatures) {
		$query = "SELECT f_id,f_text,f_userid,f_freundid,f_zeit FROM freunde WHERE f_userid=$u_id AND f_status = 'bestaetigt' "
			. "UNION "
			. "SELECT f_id,f_text,f_userid,f_freundid,f_zeit FROM freunde WHERE f_freundid=$u_id AND f_status = 'bestaetigt' "
			. "ORDER BY f_zeit desc ";
		
		$result = mysqli_query($mysqli_link, $query);
		
		if ($result && mysqli_num_rows($result) > 0) {
			
			while ($row = mysqli_fetch_object($result)) {
				unset($f);
				$f['aktion'] = "Logout";
				$f['f_text'] = $row->f_text;
				if ($row->f_userid == $u_id) {
					if (ist_online($row->f_freundid)) {
						$wann = "Sofort/Online";
						$an_u_id = $row->f_freundid;
					} else {
						$wann = "Sofort/Offline";
						$an_u_id = $row->f_freundid;
					}
				} else {
					if (ist_online($row->f_userid)) {
						$wann = "Sofort/Online";
						$an_u_id = $row->f_userid;
					} else {
						$wann = "Sofort/Offline";
						$an_u_id = $row->f_userid;
					}
				}
				// Aktion ausführen
				aktion($wann, $an_u_id, $u_nick, "", "Freunde", $f);
			}
		}
		mysqli_free_result($result);
	}
}

function global_msg($u_id, $r_id, $text) {
	// Schreibt Text $text in Raum $r_id an alle Benutzer
	// Art:		   N: Normal
	//				  S: Systemnachricht
	//				P: Privatnachticht
	//				H: Versteckte Nachricht
	
	global $mysqli_link;
	
	if (strlen($r_id) > 0) {
		$f['c_raum'] = $r_id;
	}
	$f['c_typ'] = "S";
	$f['c_von_user_id'] = $u_id;
	$f['c_text'] = $text;
	$f['c_an_user'] = 0;
	
	$back = schreibe_chat($f);
	
	// In Session merken, dass Text im Chat geschrieben wurde
	if ($u_id) {
		$query = "UPDATE online SET o_timeout_zeit=DATE_FORMAT(NOW(),\"%Y%m%d%H%i%s\"), o_timeout_warnung='N' "
			. "WHERE o_user=$u_id";
		$result = mysqli_query($mysqli_link, $query);
	}
	return ($back);
}

function hidden_msg($von_user, $von_user_id, $farbe, $r_id, $text) {
	// Schreibt Text in Raum r_id
	// Art:		   N: Normal
	//				  S: Systemnachricht
	//				P: Privatnachticht
	//				H: Versteckte Nachricht
	
	global $mysqli_link;
	
	$f['c_von_user'] = $von_user;
	$f['c_von_user_id'] = $von_user_id;
	$f['c_farbe'] = $farbe;
	$f['c_raum'] = $r_id;
	$f['c_typ'] = "H";
	$f['c_text'] = $text;
	
	$back = schreibe_chat($f);
	
	// In Session merken, dass Text im Chat geschrieben wurde
	if ($von_user_id) {
		$query = "UPDATE online SET o_timeout_zeit=DATE_FORMAT(NOW(),\"%Y%m%d%H%i%s\"), o_timeout_warnung='N' "
			. "WHERE o_user=$von_user_id";
		$result = mysqli_query($mysqli_link, $query);
	}
	
	return ($back);
}

function priv_msg(
	$von_user,
	$von_user_id,
	$an_user,
	$farbe,
	$text,
	$userdata = "")
{
	// Schreibt privaten Text von $von_user an Benutzer $an_user
	// Art:		   N: Normal
	//				  S: Systemnachricht
	//				P: Privatnachticht
	//				H: Versteckte Nachricht
	
	global $mysqli_link;
	
	// Optional Link auf Benutzer erzeugen
	
	if ($von_user_id && is_array($userdata)) {
		$f['c_von_user'] = user($von_user_id, $userdata, TRUE, FALSE, "&nbsp;",
			"", "", FALSE, TRUE);
	} else {
		$f['c_von_user'] = $von_user;
	}
	$f['c_von_user_id'] = $von_user_id;
	$f['c_an_user'] = $an_user;
	$f['c_farbe'] = $farbe;
	$f['c_typ'] = "P";
	$f['c_text'] = $text;
	
	$back = schreibe_chat($f);
	
	// In Session merken, dass Text im Chat geschrieben wurde
	if ($von_user_id) {
		$von_user_id = mysqli_real_escape_string($mysqli_link, $von_user_id); // sec
		$query = "UPDATE online SET o_timeout_zeit=DATE_FORMAT(NOW(),\"%Y%m%d%H%i%s\"), o_timeout_warnung='N' "
			. "WHERE o_user=$von_user_id";
		$result = mysqli_query($mysqli_link, $query);
	}
	
	return ($back);
}

function system_msg($von_user, $von_user_id, $an_user, $farbe, $text) {
	// Schreibt privaten Text als Systemnachricht an Benutzer $an_user
	// $von_user wird nicht benutzt
	// $von_user_id ist Absender der Nachricht (normalerweise wie $an_user, notwendig für Spamschutz)
	// Art:		   N: Normal
	//				  S: Systemnachricht
	//				P: Privatnachticht
	//				H: Versteckte Nachricht
	
	$f['c_an_user'] = $an_user;
	$f['c_von_user_id'] = $von_user_id;
	$f['c_farbe'] = $farbe;
	$f['c_typ'] = "S";
	$f['c_text'] = $text;
	
	$back = schreibe_chat($f);
	
	return ($back);
}

function aktualisiere_online($u_id, $o_raum) {
	// Timestamp im Datensatz aktualisieren -> Benutzer gilt als online
	global $mysqli_link;
	// sec ??
	$query = "UPDATE online SET o_aktiv=NULL WHERE o_user=$u_id";
	
	$result = mysqli_query($mysqli_link, $query);
}

function id_lese($id, $auth_id = "", $ipaddr = "", $agent = "", $referrer = "") {
	// Vergleicht Hash-Wert mit IP und Browser des Benutzers
	// Liefert Benutzer- und Online-Variable
	
	global $u_id, $u_nick, $o_id, $o_raum, $o_js, $u_level, $u_farbe, $u_smilie, $u_systemmeldungen, $u_punkte_anzeigen, $u_zeilen;
	global $admin, $system_farbe, $chat_back, $ignore, $userdata, $o_punkte, $o_aktion;
	global $u_farbe_alle, $u_farbe_sys, $u_farbe_bg, $u_farbe_priv, $u_farbe_noise, $u_clearedit;
	global $u_away, $o_knebel, $u_punkte_gesamt, $u_punkte_gruppe, $moderationsmodul, $mysqli_link;
	global $o_who, $o_timeout_zeit, $o_timeout_warnung;
	global $o_spam_zeilen, $o_spam_byte, $o_spam_zeit, $o_dicecheck;
	global $chat, $t, $erweitertefeatures;
	
	// IP und Browser ermittlen
	$ip = $ipaddr ? $ipaddr : $_SERVER["REMOTE_ADDR"];
	$browser = $agent ? $agent : $_SERVER["HTTP_USER_AGENT"];
	
	$browser = str_replace("MSIE 8.0", "MSIE 7.0", $browser);
	
	$id = mysqli_real_escape_string($mysqli_link, $id);
	
	// u_id und o_id aus Objekt ermitteln, o_hash, o_browser müssen übereinstimmen
	
	$query = "SELECT HIGH_PRIORITY *,UNIX_TIMESTAMP(o_timeout_zeit) as o_timeout_zeit,"
		. "UNIX_TIMESTAMP(o_knebel)-UNIX_TIMESTAMP(NOW()) as o_knebel FROM online "
		. "WHERE o_hash='$id' ";
	
	$result = mysqli_query($mysqli_link, $query);
	if (!$result) {
		echo "Fehler: " . mysqli_error($mysqli_link) . "<br><b>$query</b><br>";
		exit;
	}
	
	if ($ar = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		
		// userdaten und ignore Arrays setzen
		$userdata = unserialize(
			$ar['o_userdata'] . $ar['o_userdata2'] . $ar['o_userdata3']
				. $ar['o_userdata4']);
		$ignore = unserialize($ar['o_ignore']);
		
		unset($ar['o_ignore']);
		unset($ar['o_userdata']);
		unset($ar['o_userdata2']);
		unset($ar['o_userdata3']);
		unset($ar['o_userdata4']);
		
		// Schleife über alle Variable; Variable setzen
		while (list($k, $v) = each($ar)) {
			$$k = $v;
		}
		mysqli_free_result($result);
		
		// o_browser prüfen Benutzerdaten in Array schreiben
		if (is_array($userdata) && $ar['o_browser'] == $browser) {
			// Schleife über Benutzerdaten, Variable setzen
			while (list($k, $v) = each($userdata)) {
				@$$k = $v;
			}
			
			// Benutzereinstellungen überschreiben Default-Einstellungen
			if ($u_zeilen) {
				$chat_back = $u_zeilen;
			}
			
			// ChatAdmin oder Superuser oder moderator?
			if ($u_level == "S" || $u_level == "C"
				|| ($moderationsmodul == 1 && $u_level == "M"
					&& isset($r_status1) && strtolower($r_status1) == "m")) {
				$admin = 1;
			} else {
				$admin = 0;
			}
			
		} else {
			
			// Aus Sicherheitsgründen die Variablen löschen
			$userdata = "";
			$u_id = "";
			$u_nick = "";
			$o_id = "";
			$u_level = "";
			$admin = 0;
		}
	} else {
		
		// Aus Sicherheitsgründen die Variablen löschen
		$u_id = "";
		$u_nick = "";
		$o_id = "";
		$u_level = "";
		$admin = 0;
	}
	
	//load user color
	global $u_farbe, $user_farbe;
	if (!empty($u_id)) {
		$result = mysqli_query($mysqli_link, "SELECT u_farbe FROM user WHERE u_id = " . intval($u_id));
		$row = mysqli_fetch_row($result);
		if (empty($row[0])) {
			return;
		}
		$user_farbe = $u_farbe = $row[0];
		mysqli_free_result($result);
	}
}

function iCrypt($passwort, $salt) {
	
	global $crypted_password_extern, $upgrade_password;
	
	$v_passwort = "";
	if ($upgrade_password == 1 && $crypted_password_extern == 0) {
		$salt = "";
		if (defined("CRYPT_SHA256")) {
			$salt = '$5$rounds=5000$' . gensalt(16) . '$';
		} elseif (CRYPT_MD5 == 1) {
			$salt = '$1$' . gensalt(8) . '$';
		} else {
			$salt = gensalt(2); // für den Notfall Std. DES
		}
		$upgrade_password = 0;
		$v_passwort = crypt($passwort, $salt);
	} elseif ($crypted_password_extern == 0) {
		$upgrade_password = 0;
		if ($salt == 'MD5') {
			$v_passwort = md5($passwort);
		} else {
			$v_passwort = crypt($passwort, $salt);
		}
	} else {
		$v_passwort = $passwort;
	}
	return ($v_passwort);
}

function schreibe_db($db, $f, $id, $id_name) {
	// Assoziatives Array $f in DB $db schreiben 
	// Liefert als Ergebnis die ID des geschriebenen Datensatzes zurück
	// Sonderbehandlung für Passwörter
	// Akualiert ggf Kopie des Datensatzes in online-Tabelle
	global $mysqli_link, $u_id, $valid_fields;
	
	$neu = array();
	foreach ($valid_fields[$db] as $field) {
		if (isset($f[$field])) {
			$neu[$field] = $f[$field];
		}
	}
	$f = $neu;
	
	$id == intval($id);
	
	if (strlen($id) == 0 || $id == 0) {
		// ID generieren
		if ($db == "online" || $db == "chat") {
			// ID aus sequence verwenden
			$query = "LOCK TABLES sequence WRITE";
			$result = mysqli_query($mysqli_link, $query);
			$query = "SELECT se_nextid FROM sequence WHERE se_name='$db'";
			$result = mysqli_query($mysqli_link, $query);
			if ($result) {
				$id = mysqli_result($result, 0, 0);
				mysqli_free_result($result);
				$query = "UPDATE sequence SET se_nextid='" . ($id + 1)
					. "' WHERE se_name='$db'";
				$result = mysqli_query($mysqli_link, $query);
			} else {
				echo "Schwerer Fehler in $query: " . mysqli_errno($mysqli_link) . " - "
					. mysqli_error($mysqli_link);
				$query = "UNLOCK TABLES";
				$result = mysqli_query($mysqli_link, $query);
				die();
			}
			$query = "UNLOCK TABLES";
			$result = mysqli_query($mysqli_link, $query);
			
		} else {
			// ID mit auto_increment erzeugen
			$id = 0;
		}
		
		// Datensatz neu schreiben
		$q = "";
		for (reset($f); list($name, $inhalt) = each($f);) {
			if (($name != $id_name) && ($name != "u_salt")) {
				$q .= "," . mysqli_real_escape_string($mysqli_link, $name);
				if ($name == "u_passwort") {
					if (!isset($f['u_salt']))
						$f['u_salt'] = substr($inhalt, 0, 2);
					// Verschlüsseln
						$q .= "='" . mysqli_real_escape_string($mysqli_link, iCrypt($inhalt, $f['u_salt'])) . "'";
				} else {
					$q .= "='" . mysqli_real_escape_string($mysqli_link, $inhalt) . "'";
				}
			}
		}
		
		$query = "INSERT INTO $db SET $id_name=$id " . $q;
		$result = mysqli_query($mysqli_link, $query);
		if (!$result) {
			echo "Fataler Fehler in $query: " . mysqli_errno($mysqli_link) . " - "
				. mysqli_error($mysqli_link);
			die();
		}
		if ($id == 0) {
			$id = mysqli_insert_id($mysqli_link);
		}
		
	} else {
		// bestehenden Datensatz updaten
		$q = "";
		for (reset($f); list($name, $inhalt) = each($f);) {
			if ($name != "u_salt") {
				if ($q == "") {
					$q = mysqli_real_escape_string($mysqli_link, $name);
				} else {
					$q .= "," . mysqli_real_escape_string($mysqli_link, $name);
				}
				if ($name == "u_passwort") {
					// Verschlüsseln
					if (!isset($f['u_salt'])) {
						$f['u_salt'] = substr($inhalt, 0, 2);
					}
					$q .= "='" . mysqli_real_escape_string($mysqli_link, iCrypt($inhalt, $f['u_salt'])) . "'";
				} else {
					$q .= "='" . mysqli_real_escape_string($mysqli_link, $inhalt) . "'";
				}
			}
		}
		$q = "UPDATE $db SET " . $q . " WHERE $id_name=$id";
		$result = mysqli_query($mysqli_link, $q);
	}
	
	if ($db == "user" && $id_name == "u_id") {
		// Kopie in Onlinedatenbank aktualisieren
		// Query muss mit dem Code in login() übereinstimmen
		$query = "SELECT `u_id`, `u_nick`, `u_level`, `u_farbe`, `u_zeilen`, `u_farbe_bg`, "
			. "`u_farbe_alle`, `u_farbe_priv`, `u_farbe_noise`, `u_farbe_sys`, `u_clearedit`, "
			. "`u_away`, `u_email`, `u_adminemail`, `u_smilie`, `u_punkte_gesamt`, `u_punkte_gruppe`, "
			. "`u_chathomepage`, `u_systemmeldungen`, `u_punkte_anzeigen` "
			. "FROM `user` WHERE `u_id`=$id";
		$result = mysqli_query($mysqli_link, $query);
		if ($result && mysqli_num_rows($result) == 1) {
			$userdata = mysqli_fetch_array($result, MYSQLI_ASSOC);
			
			// Slashes in jedem Eintrag des Array ergänzen
			reset($userdata);
			while (list($ukey, $udata) = each($userdata)) {
				$udata = mysqli_real_escape_string($mysqli_link, $udata);
			}
			
			// Benutzerdaten in 255-Byte Häppchen zerlegen
			$userdata_array = zerlege(serialize($userdata));
			
			if (!isset($userdata_array[0])) {
				$userdata_array[0] = "";
			}
			if (!isset($userdata_array[1])) {
				$userdata_array[1] = "";
			}
			if (!isset($userdata_array[2])) {
				$userdata_array[2] = "";
			}
			if (!isset($userdata_array[3])) {
				$userdata_array[3] = "";
			}
			
			$query = "UPDATE online SET "
				. "o_userdata='" . mysqli_real_escape_string($mysqli_link, $userdata_array[0]) . "', "
				. "o_userdata2='" . mysqli_real_escape_string($mysqli_link, $userdata_array[1]) . "', "
				. "o_userdata3='" . mysqli_real_escape_string($mysqli_link, $userdata_array[2]) . "', "
				. "o_userdata4='" . mysqli_real_escape_string($mysqli_link, $userdata_array[3]) . "', "
				. "o_level='" . mysqli_real_escape_string($mysqli_link, $userdata['u_level']) . "', "
				. "o_name='" . mysqli_real_escape_string($mysqli_link, $userdata['u_nick']) . "' "
					. "WHERE o_user=$id";
			mysqli_query($mysqli_link, $query);
			mysqli_free_result($result);
			
		}
	}
	
	return ($id);
}

function zerlege($daten) {
	// Zerlegt $daten in 255byte-Häppchen und liefert diese in einem Array zurück
	
	$i = 0;
	$laenge = strlen($daten);
	$fertig = array();
	
	if ($laenge != 0) {
		$j = 0;
		$i = 0;
		while ($j < $laenge) {
			$fertig[] = substr($daten, $j, 255);
			// system_msg("",0,1225,0,"<b>DEBUG:</b> $i,$j,'$fertig[$i]'");
			$j = $j + 255;
			$i++;
		}
	}
	
	return ($fertig);
}

function show_box($box, $text, $width = "") {
	// Gibt Tabelle mit URL im Kopf und Inhalt aus 
	
	if (strlen($width) > 0) {
		$width = "style=\"width:" . $width . ";\"";
	}
	?>
	
	<table class="tabelle_kopf2" <?php echo $width; ?>>
	
		<tr>
			<td class="tabelle_kopfzeile"><?php echo $box; ?></td>
		</tr>
		<tr>
			<td class="tabelle_koerper_login"><?php echo $text; ?></td>
		</tr>
	</table>
	<?php
}

function show_box_title_content($box, $text, $zeilenumbruch = false) {
	// Gibt Tabelle mit Kopf, Optional Schließ-Button und Inhalt aus
	global $f1;
	global $f2;
	?>
	
	<table class="tabelle_kopf">
		<tr>
			<td class="tabelle_kopfzeile"><?php echo $box; ?></td>
		</tr>
		<tr>
			<td class="tabelle_koerper"><?php echo  $f1 . $text . $f2; ?></td>
		</tr>
	</table>
	<?php
	if($zeilenumbruch) {
		echo "<br>";
	}
}

function show_kopfzeile_login() {
	// Gibt die Kopfzeile im Login aus
	global $t, $layout_kopf, $communityfeatures, $id;
	
	// Willkommen wird nur angezeigt, wenn kein eigener Kopf definiert ist
	if (isset($layout_kopf) && $layout_kopf != "") {
		$willkommen = "";
	} else {
		$willkommen = $t['willkommen2'];
	}
	
	zeige_header_ende();
	echo "<body>";
	
	$box = $t['menue1'];
	$text = "<a href=\"index.php\">$t[menue2]</a>\n";
	if( $id == '' ) { // Registrierung nur anzeigen, wenn man nicht eingeloggt ist
		$text .= "| <a href=\"index.php?id=$id&aktion=neu\">$t[menue3]</a>\n";
	}
	$text .= "| <a href=\"index.php?id=$id&aktion=hilfe\">$t[menue4]</a>\n";
	$text .= "| <a href=\"index.php?id=$id&aktion=hilfe-befehle\">$t[menue5]</a>\n";
	$text .= "| <a href=\"index.php?id=$id&aktion=hilfe-sprueche\">$t[menue6]</a>\n";
	if ($communityfeatures) {
		$text .= "| <a href=\"index.php?id=$id&aktion=hilfe-community\">$t[menue7]</a>\n";
	}
	$text .= "| <a href=\"index.php?id=$id&aktion=chatiquette\">$t[menue8]</a>\n";
	$text .= "| <a href=\"index.php?id=$id&aktion=nutzungsbestimmungen\">$t[menue9]</a>\n";
	$text .= "| <a href=\"index.php?id=$id&aktion=datenschutz\">$t[menue10]</a>\n";
	
	show_box_title_content($box, $text);
	
	zeige_kopf();
	
	echo $willkommen;
}

function schliessen_link() {
	// Gibt einen Link zum Schließen des Fensters aus
	global $t;
	global $f1;
	global $f2;
	
	return $f1 . "<p style=\"text-align:center;\">[<a href=\"javascript:window.close();\">$t[sonst1]</a>]</p>" . $f2 . "\n";
}

function coreCheckName($name, $check_name) {
	global $upper_name;
	// Nicht-druckbare Zeichen, Leerzeichen, " und ' entfernen
	$name = preg_replace("/[ '" . chr(34) . chr(1) . "-" . chr(31) . "]/",
		"", $name);
	$name = str_replace("#", "", $name);
	$name = str_replace("+", "", $name);
	$name = trim($name);
	
	if ((strlen($check_name) > 0) && (strlen($name) > 0)) {
		$i = 0;
		while ($i < strlen($name)) {
			if (!strstr($check_name, substr($name, $i, 1))) {
				if ($i > 0 && $i < strlen($name) - 1) {
					$name = substr($name, 0, $i) . substr($name, $i + 1);
				} elseif ($i > 0) {
					$name = substr($name, 0, -1);
				} else {
					$name = substr($name, $i + 1);
				}
			} else {
				$i++;
			}
		}
		
		if (!strstr($check_name, substr($name, 0, 1))) {
			return (substr($name, 1));
		}
	}
	
	if ($upper_name) {
		$name = UCFirst($name);
	}
	
	return $name;
}

function raum_ist_moderiert($raum) {
	// Liefert Status des Raums zurück: Moderiert ja/nein
	// Liefert zusätzlich in $raum_einstellungen alle Einstellungen des Raums zurück
	// Liefert in ist_moderiert zurück: Moderiert ja/nein
	
	// Liefert in ist_eingang zurück: Stiller Eingangsraum ja/nein
	global $mysqli_link, $u_id, $system_farbe, $moderationsmodul, $raum_einstellungen, $ist_moderiert, $ist_eingang;
	$moderiert = 0;
	$raum = intval($raum);
	
	$query = "SELECT * FROM raum WHERE r_id=$raum";
	$result = mysqli_query($mysqli_link, $query);
	if ($result && mysqli_num_rows($result) > 0) {
		$raum_einstellungen = mysqli_fetch_array($result);
		$r_status1 = $raum_einstellungen['r_status1'];
	}
	mysqli_free_result($result);
	if (isset($r_status1) && ($r_status1 == "m" || $r_status1 == "M")) {
		$query = "SELECT o_user FROM online "
			. "WHERE o_raum=$raum AND o_level='M' ";
		$result = mysqli_query($mysqli_link, $query);
		if (mysqli_num_rows($result) > 0)
			$moderiert = 1;
		mysqli_free_result($result);
	}
	$ist_moderiert = $moderiert;
	$ist_eingang = $r_status1 == "E";
	return $moderiert;
}

function debug($text = "", $rundung = 3) {
	
	static $zeit;
	static $durchlauf;
	
	if (!$text || !$zeit) {
		list($usec, $sec) = explode(" ", microtime());
		$zeit = (float) $usec + (float) $sec;
		$durchlauf = 0;
		$erg = "";
	} else {
		list($usec, $sec) = explode(" ", microtime());
		$zeit_neu = (float) $usec + (float) $sec;
		$durchlauf++;
		$laufzeit = $zeit_neu - $zeit;
		$zeit = $zeit_neu;
		$erg = $text . "_" . $durchlauf . ": " . round($laufzeit, $rundung)
			. "s";
		if ($durchlauf > 1)
			$erg = ", " . $erg;
	}
	return ($erg);
}

function user(
	$zeige_user_id,
	$userdaten = 0,
	$link = TRUE,
	$online = FALSE,
	$trenner = "&nbsp;",
	$online_zeit = "",
	$letzter_login = "",
	$mit_id = TRUE,
	$extra_kompakt = FALSE,
	$felder = 31)
{
	// Liefert Benutzernamen + Level + Gruppe + E-Mail + Homepage zurück
	// Bei link=TRUE wird Link auf Benutzerinfo ausgegeben
	// Bei online=TRUE wird der Status online/offline und opt die Onlinezeit oder der letzte Login ausgegeben
	// Falls trenner gesetzt, wird Mail/Home Symbol ausgegeben und trenner vor Mail/Home Symbol eingefügt
	// $online_zeit -> Zeit in Sekunden seit Login
	// $letzter_login -> Datum des letzten Logins
	// Falls mit_id=TRUE wird Session-ID ausgegeben, ansonsten Platzhalter
	// Falls extra_kompakt=TRUE wird nur Nick ausgegeben
	// $felder ist bitweise kodiert welche felder ausgegeben werden sollen
	// Aufschlüsselung wie folgt:
	// 1 = Benutzernamen zeigen
	// 2 = Level zeigen
	// 4 = Gruppe zeigen
	// 8 = EMail zeigen
	// 16 = Homepage zeigen
	
	global $id, $system_farbe, $mysqli_link, $communityfeatures, $t, $show_geschlecht;
	global $f1, $f2, $leveltext, $punkte_grafik, $chat_grafik, $o_js, $homep_ext_link;
	
	$text = "";
	if ($mit_id) {
		$idtag = $id;
	} else {
		$idtag = "<ID>";
	}
	
	if (is_array($userdaten)) {
		// Array wurde übergeben
		
		if (!isset($userdaten['u_chathomepage'])) {
			$userdaten['u_chathomepage'] = 'N';
		}
		if (!isset($userdaten['u_punkte_anzeigen'])) {
			$userdaten['u_punkte_anzeigen'] = 'N';
		}
		
		$user_id = $userdaten['u_id'];
		$user_nick = $userdaten['u_nick'];
		$user_level = $userdaten['u_level'];
		$user_punkte_gesamt = $userdaten['u_punkte_gesamt'];
		$user_punkte_gruppe = hexdec($userdaten['u_punkte_gruppe']);
		$user_chathomepage = htmlspecialchars($userdaten['u_chathomepage']);
		
		if ($show_geschlecht == true)
			$user_geschlecht = hole_geschlecht($zeige_user_id);
		
		$user_punkte_anzeigen = $userdaten['u_punkte_anzeigen'];
		
	} elseif (is_object($userdaten)) {
		// Object wurde übergeben
		$user_id = $userdaten->u_id;
		$user_nick = $userdaten->u_nick;
		$user_level = $userdaten->u_level;
		$user_punkte_gesamt = $userdaten->u_punkte_gesamt;
		$user_punkte_gruppe = hexdec($userdaten->u_punkte_gruppe);
		if (isset($userdaten->u_chathomepage)) {
			$user_chathomepage = htmlspecialchars($userdaten->u_chathomepage);
		} else {
			$user_chathomepage = "";
		}
		
		if ($show_geschlecht == true)
			$user_geschlecht = hole_geschlecht($zeige_user_id);
		
		if (isset($userdaten->u_punkte_anzeigen)) {
			$user_punkte_anzeigen = $userdaten->u_punkte_anzeigen;
		} else {
			$user_punkte_anzeigen = "";
		}
		
	} else if ($zeige_user_id) {
		
		// Benutzerdaten aus DB lesen
		$query = "SELECT u_id,u_nick,u_level,u_away,u_punkte_gesamt,u_punkte_gruppe,u_chathomepage,u_punkte_anzeigen, "
			. "UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(o_login) AS online, "
			. "date_format(u_login,'%d.%m.%y %H:%i') AS login "
			. "FROM user LEFT JOIN online ON o_user=u_id "
			. "WHERE u_id=" . intval($zeige_user_id);
		$result = mysqli_query($mysqli_link, $query);
		if ($result && mysqli_num_rows($result) == 1) {
			$userdaten = mysqli_fetch_object($result);
			$user_id = $userdaten->u_id;
			$user_nick = $userdaten->u_nick;
			$user_level = $userdaten->u_level;
			$user_punkte_gesamt = $userdaten->u_punkte_gesamt;
			$user_punkte_gruppe = hexdec($userdaten->u_punkte_gruppe);
			$user_chathomepage = htmlspecialchars($userdaten->u_chathomepage);
			$user_punkte_anzeigen = $userdaten->u_punkte_anzeigen;
			
			$online_zeit = $userdaten->online;
			$letzter_login = $userdaten->login;
			
		}
		mysqli_free_result($result);
		
		if ($show_geschlecht == true) {
			$user_geschlecht = hole_geschlecht($zeige_user_id);
		}
		
	} else {
		echo "<p><b>Fehler:</b> Falscher Aufruf von user() für Benutzer ";
		if (isset($zeige_user_id))
			echo $zeige_user_id;
		if (isset($userdaten['u_id']))
			echo $userdaten['u_id'];
		if (isset($userdaten->u_id))
			echo $userdaten->u_id;
		echo "</p>";
		
		return "";
	}
	
	// Wenn die $user_punkte_anzeigen nicht im Array war, dann seperat abfragen
	if (!isset($user_punkte_anzeigen) || ($user_punkte_anzeigen != "Y" and $user_punkte_anzeigen != "N")) {
		$query = "SELECT `u_punkte_anzeigen` FROM `user` WHERE `u_id`=" . intval($user_id);
		$result = mysqli_query($mysqli_link, $query);
		if ($result && mysqli_num_rows($result) == 1) {
			$userdaten = mysqli_fetch_object($result);
			$user_punkte_anzeigen = $userdaten->u_punkte_anzeigen;
		}
		mysqli_free_result($result);
	}
	
	if ($user_id != $zeige_user_id) {
		echo "<P><b>Fehler: </b> $user_id!=$zeige_user_id</P>\n";
		return "";
	}
	
	// Fensternamen aus Benutzernamen erzeugen
	$fenstername = str_replace("-", "", $user_nick);
	$fenstername = str_replace("+", "", $fenstername);
	$fenstername = str_replace("ä", "", $fenstername);
	$fenstername = str_replace("ö", "", $fenstername);
	$fenstername = str_replace("ü", "", $fenstername);
	$fenstername = str_replace("Ä", "", $fenstername);
	$fenstername = str_replace("Ö", "", $fenstername);
	$fenstername = str_replace("Ü", "", $fenstername);
	$fenstername = str_replace("ß", "", $fenstername);
	
	if (($felder & 1) != 1) {
		$user_nick_sik = $user_nick;
		$user_nick = "";
	}
	
	if ($link) {
		$url = "user.php?id=$idtag&aktion=zeig&user=$user_id";
		$text = "<a href=\"#\"  target=\"$fenstername\" onclick=\"neuesFenster('$url','$fenstername'); return(false);\">"
			. $user_nick . "</A>";
	} else {
		$text = $user_nick;
	}
	
	if ($show_geschlecht AND $user_geschlecht)
		$text .= $chat_grafik[$user_geschlecht];
	
	if (($felder && 1) != 1) {
		$user_nick = $user_nick_sik;
	}
	
	// Levels, Gruppen, Home & Mail Grafiken
	$text2 = "";
	if (!isset($leveltext[$user_level]))
		$leveltext[$user_level] = "";
	if (!$extra_kompakt && $leveltext[$user_level] != ""
		&& (($felder & 2) == 2))
		$text2 .= "&nbsp;(" . $leveltext[$user_level] . ")";
	
	if (!$extra_kompakt && $link) {
		$grafikurl1 = "<a href=\"index.php?aktion=hilfe-community\" target=\"_blank\">";
		$grafikurl2 = "</a>";
	} else {
		$grafikurl1 = "";
		$grafikurl2 = "";
	}
	
	if (!$extra_kompakt && $user_punkte_gruppe != 0 && $communityfeatures
		&& $user_punkte_anzeigen == "Y" && (($felder & 4) == 4)) {
		
		if ($user_level == "C" || $user_level == "S") {
			$text2 .= "&nbsp;" . $grafikurl1 . $punkte_grafik[0]
				. $user_punkte_gruppe . $punkte_grafik[1] . $grafikurl2;
		} else {
			$text2 .= "&nbsp;" . $grafikurl1 . $punkte_grafik[2]
				. $user_punkte_gruppe . $punkte_grafik[3] . $grafikurl2;
		}
	}
	
	if (!$extra_kompakt && ($user_chathomepage == "J" OR $homep_ext_link != "") && $communityfeatures && $link && (($felder & 16) == 16)) {
		if ($homep_ext_link != "" AND $user_level != "G") {
			$url = $homep_ext_link . $user_nick;
			$text2 .= "&nbsp;"
				. "<a href=\"#\" target=\"640_$fenstername\" onClick=\"neuesFenster2('$url'); return(false)\">$chat_grafik[home]</A>";
		} elseif ($user_chathomepage == "J") {
			$url = "home.php?ui_userid=$user_id&id=$idtag";
			$text2 .= "&nbsp;" . "<a href=\"#\" target=\"640_$fenstername\" onClick=\"neuesFenster2('$url'); return(false)\">$chat_grafik[home]</A>";
		}
	}
	
	if (!$extra_kompakt && $link && $trenner != "" && $communityfeatures && (($felder & 8) == 8)) {
		$url = "mail.php?aktion=neu2&neue_email[an_nick]="
			. URLENCODE($user_nick) . "&id=" . $idtag;
		$text2 .= $trenner
			. "<a href=\"#\" target=\"640_$fenstername\" onMouseOver=\"return(true)\" onClick=\"neuesFenster2('$url'); return(false)\" title=\"E-Mail\">"
			
			. "<span class=\"fa fa-envelope icon16\" alt=\"Mail\" title=\"Mail\"></span>" . "</a>";
	} else if (!$extra_kompakt && $link && $trenner != "") {
		$text2 .= $trenner;
	}
	
	// Onlinezeit oder Datum des letzten Logins einfügen, falls Online Text fett ausgeben
	if ($online_zeit && $online_zeit != "NULL" && $online) {
		$text2 .= $trenner
			. str_replace("%online%", gmdate("H:i:s", $online_zeit),
				$t['chat_msg92']);
		$fett1 = "<b>";
		$fett2 = "</b>";
	} elseif ($letzter_login && $letzter_login != "NULL" && $online) {
		$text2 .= $trenner
			. str_replace("%login%", $letzter_login, $t['chat_msg94']);
		$fett1 = "";
		$fett2 = "";
	} elseif ($online) {
		$text2 .= $trenner . $t['chat_msg93'];
		$fett1 = "";
		$fett2 = "";
	} else {
		$fett1 = "";
		$fett2 = "";
	}
	
	if ($text2 != "")
		$text .= $f1 . $text2 . $f2;
	return ($fett1 . $text . $fett2);
}

function chat_parse($text) {
	// Filtert Text und ersetzt folgende Zeichen:
	// http://###### oder www.###### in <a href="http://###" target=_blank>http://###</A>
	// E-Mail Adressen in A-Tag mit Mailto
	
	global $admin, $sprachconfig, $u_id, $u_level;
	global $t, $system_farbe, $erweitertefeatures;
	
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	
	// doppelte/ungültige Zeichen merken und wegspeichern...
	$text = str_replace("__", "###substr###", $text);
	$text = str_replace("**", "###stern###", $text);
	$text = str_replace("@@", "###klaffe###", $text);
	$text = str_replace("[", "###auf###", $text);
	$text = str_replace("]", "###zu###", $text);
	$text = str_replace("|", "###strich###", $text);
	$text = str_replace("!", "###ausruf###", $text);
	$text = str_replace("+", "###plus###", $text);
	$text = str_replace("<br />", "<br>", $text);
	
	// jetzt nach nach italic und bold parsen...  * in <I>, $_ in <b>
	$text = preg_replace('|\*(.*?)\*|', '<i>\1</i>',
		preg_replace('|_(.*?)_|', '<b>\1</b>', $text));
	
	// erst mal testen ob www oder http oder email vorkommen
	if (preg_match("/(http:|www\.|@)/i", $text)) {
		// Zerlegen der Zeile in einzelne Bruchstücke. Trennzeichen siehe $split
		// leider müssen zunächst erstmal in $text die gefundenen urls durch dummies 
		// ersetzt werden, damit bei der Angabge von 2 gleichen urls nicht eine bereits 
		// ersetzte url nochmal ersetzt wird -> gibt sonst Müll bei der Ausgabe.
		
		// wird evtl. später nochmal gebraucht...
		$split = '/[ \r\n\t,\)\(]/';
		$txt = preg_split($split, $text);
		
		// wieviele Worte hat die Zeile?
		for ($i = 500; $i >= 0; $i--) {
			if (isset($txt[$i]) && $txt[$i] != "")
				break;
		}
		$text2 = $text;
		// Schleife über alle Worte...
		for ($j = 0; $j <= $i; $j++) {
			// test, ob am Ende der URL noch ein Sonderzeichen steht...
			$txt[$j] = preg_replace("!\?$!", "", $txt[$j]);
			$txt[$j] = preg_replace("!\.$!", "", $txt[$j]);
		}
		for ($j = 0; $j <= $i; $j++) {
			
			// E-Mail Adressen in A-Tag mit Mailto
			// E-Mail-Adresse -> Format = *@*.*
			if (preg_match("/^.+@.+\..+/i", $txt[$j])
				&& !preg_match("/^href=\"mailto:.+@.+\..+/i", $txt[$j])) {
				// Wort=Mailadresse? -> im text durch dummie ersetzen, im wort durch href.
				$text = preg_replace("!$txt[$j]!", "####$j####", $text);
				$rep = "<a href=\"mailto:" . str_replace("<br>", "", $txt[$j])
					. "\">" . $txt[$j] . "</a>";
				$txt[$j] = str_replace($txt[$j], $rep, $txt[$j]);
			}
			
			// www.###### in <a href="http://###" target=_blank>http://###</A>
			if (preg_match("/^www\..*\..*/i", $txt[$j])) {
				// sonderfall -> "?" in der URL -> dann "?" als Sonderzeichen behandeln...
				$txt2 = preg_replace("!\?!", "\\?", $txt[$j]);
				
				// Doppelcheck, kann ja auch mit http:// in gleicher Zeile nochmal vorkommen. also erst die 
				// http:// mitrausnehmen, damit dann in der Ausgabe nicht http://http:// steht...
				$text = preg_replace("!http://$txt2!", "-###$j####", $text);
				
				// Wort=URL mit www am Anfang? -> im text durch dummie ersetzen, im wort durch href.
				$text = preg_replace("!$txt2!", "####$j####", $text);
				
				// und den ersten Fall wieder Rückwärts, der wird ja später in der schleife nochmal behandelt.
				$text = preg_replace("!-###\d*####/!", "http://$txt2/", $text);
				
				// Ersetzte Zeichen für die URL wieder zurückwandeln
				$txt3 = str_replace("###plus###", "+", $txt2);
				$txt3 = str_replace("###strich###", "|", $txt3);
				$txt3 = str_replace("###auf###", "[", $txt3);
				$txt3 = str_replace("###zu###", "]", $txt3);
				$txt3 = str_replace("###substr###", "_", $txt3);
				$txt3 = str_replace("###stern###", "*", $txt3);
				$txt3 = str_replace("###klaffe###", "@", $txt3);
				
				// url aufbereiten
				$txt[$j] = str_replace($txt2,
					"<a href=\"redirect.php?url="
						. urlencode(
							"http://"
								. strtr(preg_replace("!\\\\\\?!", "?", $txt3),
									$trans)) . "\" target=\"_blank\">http://"
						. preg_replace("!\\\\\\?!", "?", $txt2) . "</a>",
					$txt[$j]);
				
				$txt[$j] = str_replace("###ausruf###", "!", $txt[$j]);
				$txt[$j] = str_replace("%23%23%23ausruf%23%23%23", "!",
					$txt[$j]);
				
			}
			// http://###### in <a href="http://###" target=_blank>http://###</A>
			if (preg_match("!^https?://!", $txt[$j])) {
				// Wort=URL mit http:// am Anfang? -> im text durch dummie ersetzen, im wort durch href.
				// Zusatzproblematik.... könnte ein http-get-URL sein, mit "?" am Ende oder zwischendrin... urgs.
				
				// sonderfall -> "?" in der URL -> dann "?" als Sonderzeichen behandeln...
				$txt2 = preg_replace("!\?!", "\\?", $txt[$j]);
				$text = preg_replace("!$txt2!", "####$j####", $text);
				
				// und wieder Rückwärts, falls zuviel ersetzt wurde...
				$text = preg_replace("!####\d*####/!", "$txt[$j]/", $text);
				
				// Ersetzte Zeichen für die URL wieder zurückwandeln
				$txt3 = str_replace("###plus###", "+", $txt2);
				$txt3 = str_replace("###strich###", "|", $txt3);
				$txt3 = str_replace("###auf###", "[", $txt3);
				$txt3 = str_replace("###zu###", "]", $txt3);
				$txt3 = str_replace("###substr###", "_", $txt3);
				$txt3 = str_replace("###stern###", "*", $txt3);
				$txt3 = str_replace("###klaffe###", "@", $txt3);
				
				// url aufbereiten, \? in ? wandeln
				$txt[$j] = preg_replace("!$txt2!",
					"<a href=\"redirect.php?url="
						. urlencode(
							strtr(preg_replace("!\\\\\\?!", "?", $txt3), $trans))
						. "\" target=\"_blank\">"
						. preg_replace("!\\\\\\?!", "?", $txt2) . "</a>",
					$txt[$j]);
				// echo "\n<br>####<br>\n".$txt2."<br>\n".urlencode(strtr($txt2,$trans))."<br>\n".urldecode(urlencode(strtr($txt2,$trans)))."<br>\n\n";
				
				$txt[$j] = str_replace("###ausruf###", "!", $txt[$j]);
				$txt[$j] = str_replace("%23%23%23ausruf%23%23%23", "!",
					$txt[$j]);
				
			}
		}
		
		// nun noch die Dummy-Strings durch die urls ersetzen...
		for ($j = 0; $j <= $i; $j++) {
			// erst mal "_" in den dummy-strings vormerken...
			// es soll auch urls mit "_" geben ;-)
			$txt[$j] = str_replace("_", "###substr###", $txt[$j]);
			$text = preg_replace("![-#]###$j####!", $txt[$j], $text);
		}
	} // ende http, mailto, etc.
	
	// gemerkte Zeichen zurückwandeln.
	$text = str_replace("###plus###", "+", $text);
	$text = str_replace("###strich###", "|", $text);
	$text = str_replace("###auf###", "[", $text);
	$text = str_replace("###zu###", "]", $text);
	$text = str_replace("###substr###", "_", $text);
	$text = str_replace("###stern###", "*", $text);
	$text = str_replace("###klaffe###", "@", $text);
	$text = str_replace("###ausruf###", "!", $text);
	
	return $text;
}

function gensalt($length)
{
	return genpassword($length);
}

function genpassword($length)
{
	// Generiert ein Passwort
	// wird auch für gensalt() genutzt
	$vowels = array("a", "e", "i", "o", "u");
	$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r",
		"s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr", "ch",
		"ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
	$num_vowels = count($vowels);
	$num_cons = count($cons);
	
	$password = "";
	for ($i = 0; $i < $length; $i++) {
		$password .= $cons[mt_rand(0, $num_cons - 1)]
			. $vowels[mt_rand(0, $num_vowels - 1)];
	}
	
	return substr($password, 0, $length);
}

function hole_geschlecht($userid) {
	global $mysqli_link;
	
	$query = "SELECT ui_geschlecht FROM userinfo WHERE ui_userid=" . intval($userid);
	$result = mysqli_query($mysqli_link, $query);
	if ($result AND mysqli_num_rows($result) == 1) {
		$userinfo = mysqli_fetch_object($result);
		$user_geschlecht = $userinfo->ui_geschlecht;
	}
	mysqli_free_result($result);
	
	if ($user_geschlecht == "männlich")
		$user_geschlecht = "geschlecht_maennlich";
	elseif ($user_geschlecht == "weiblich")
		$user_geschlecht = "geschlecht_weiblich";
	else
		$user_geschlecht = "";
	
	return $user_geschlecht;
}

function mailsmtp($mailempfaenger, $mailbetreff, $text2, $header, $chat, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption, $smtp_auth, $smtp_autoTLS) {
	require 'PHPMailerAutoload.php';
	
	$mail = new PHPMailer;
	
	$mail->isSMTP();
	//$mail->SMTPDebug = 2;
	//$mail->Debugoutput = 'html';
	$mail->Host = $smtp_host;
	$mail->Port = $smtp_port;
	$mail->SMTPSecure = $smtp_encryption;
	$mail->SMTPAuth = $smtp_auth;
	$mail->SMTPAutoTLS = $smtp_autoTLS;
	$mail->Username = $smtp_username;
	$mail->Password = $smtp_password;
	$mail->setFrom($header, $chat);
	$mail->addAddress($mailempfaenger);
	$mail->Subject = $mailbetreff;
	$mail->isHtml(true);
	$mail->Body = $text2;
	
	return $mail->send();
}

function avadel($userid){
	global $mysqli_link;
	
	$query = "SELECT ui_avatar FROM user WHERE u_id=" . intval($userid);
	$result = mysqli_query($mysqli_link, $query);
	if ($result AND mysqli_num_rows($result) == 1) {
		$userdata = mysqli_fetch_object($result);
		$ui_avatar = $userdata->ui_avatar;
	}
	
	$ava = "./avatars/" . $ui_avatar;
	
	unlink($ava);
	
	$querydel = "UPDATE user SET ui_avatar='' WHERE u_id=" . intval($userid);
	$resultdel = mysqli_query($mysqli_link, $querydel);
	
	if(!file_exists($ava) && $resultdel == 1){
		$return = true;
	} else {
		$return = false;
	}
	
	return $return;
}
?>