<?php

// functions nur für user.php

require_once("functions/functions-func-raeume_auswahl.php");

function user_pm_list($larr, $anzahl) {
	// Gibt Benutzerliste $larr als Tabelle aus
	global $t, $admin, $u_level, $adminfeatures, $aktion, $u_id, $id, $show_geschlecht, $mysqli_link;
	global $f1, $f2, $f3, $f4, $homep_ext_link;
	global $punkte_grafik, $leveltext, $chat_grafik;
	
	
	$text = '';
		
	//Wichtiger SQL Teil
	//SQL Teil für die richtige Berechnung der einzelnen PM Nachrichten
	$pmu2 = mysqli_query($mysqli_link, "SELECT DISTINCT c_an_user FROM chat WHERE c_typ='P' AND c_von_user_id=".$u_id);
	$pmue22 = mysqli_num_rows($pmu2);
	$pmue2 = mysqli_fetch_all($pmu2);
		
	$pmu = mysqli_query($mysqli_link, "SELECT DISTINCT c_von_user_id FROM chat WHERE c_typ='P' AND c_an_user=".$u_id);
	$pmuee = mysqli_num_rows($pmu);
	$pmue = mysqli_fetch_all($pmu);	
		
	// Array mit oder ohne Javascript ausgeben
	// Kopf Tabelle
	$box = $t['sonst18'];
	
	flush();
		$v = $larr[$k];
	// Anzeige der Benutzer ohne JavaScript
	if($pmue22 != 0) {
		for ($k = 0; $k < $pmue22; $k++) {
			$pmuea2 = $pmue2[$k][0]; 
			
			//SQL Teil für die Anzeige der nicht gelesenen Nachrichten.
			$pmu3 = mysqli_query($mysqli_link, "SELECT c_an_user FROM chat WHERE c_gelesen=0 AND c_typ='P' AND c_von_user_id=".$pmuea2." AND c_an_user=".$u_id);
			$pmue33 = mysqli_num_rows($pmu3);
			
			if ( $k % 2 != 0 ) {
				$farbe_tabelle = 'class="tabelle_zeile1"';
			} else {
				$farbe_tabelle = 'class="tabelle_zeile2"';
			}
			
			if($pmue33 > 0) {
				$pmue33t = " <b>(".$pmue33.")</b>";
			} else {
				$pmue33t = "";
			}
			
			$user = zeige_userdetails($pmuea2, $v) . "" . $pmue33t;
			
			$trow .= "<tr>";
			$trow .= "<td $farbe_tabelle>" . $f1 . "<b>";
			
			$trow .= "</b>" . $user . $f2;
			$trow .= "</td></tr>";
		}
	}
	
	if($pmuee !=0) {
		$pxx = false;
		
		for ($k = 0; $k < $pmuee; $k++) {
			$pmuea = $pmue[$k][0]; 
			
			$pmu4 = mysqli_query($mysqli_link, "SELECT c_an_user FROM chat WHERE c_gelesen=0 AND c_typ='P' AND c_von_user_id=".$pmuea);
			$pmue44 = mysqli_num_rows($pmu4);
			$pmue4 = mysqli_fetch_all($pmu4);
			
			for ($k = 0; $k < $pmue22; $k++) {
				$pmuea2 = $pmue2[$k][0]; 
				$pmuea3 = $pmue4[$k][0];
				
					if($pmuea == $pmuea2) {
						$pxx = true;
						//$pmue44 = "0";
					}
			}	
			
			if(!$pxx) {
				if ( $k % 2 != 0 ) {
					$farbe_tabelle = 'class="tabelle_zeile1"';
				} else {
					$farbe_tabelle = 'class="tabelle_zeile2"';
				}
				
				if($pmue44 > 0) {
					$pmue44t = " <b>(".$pmue44.")</b>";
				} else {
					$pmue44t = "";
				}
				
				$user = zeige_userdetails($pmuea, $v) . "" . $pmue44t;
				
				$trow .= "<tr>";
				$trow .= "<td $farbe_tabelle>" . $f1 . "<b>";
				
				
				$trow .= "</b>" . $user . $f2;
				$trow .= "</td></tr>";
			}
		}
	}
		
	$text .= "<table style=\"width:100%;\">$trow</table>\n";
	
	return $text;
}

function user_liste($larr, $anzahl, $seitenleiste = false) {
	// Gibt Benutzerliste $larr als Tabelle aus
	global $t, $admin, $u_level, $aktion, $u_id, $id, $show_geschlecht, $mysqli_link;
	global $f1, $f2, $f3, $f4, $homep_ext_link;
	global $punkte_grafik, $leveltext, $chat_grafik;
	
	$text = '';
	
	// Array mit oder ohne Javascript ausgeben
	// Kopf Tabelle
	$box = $t['sonst18'];
	
	if (!isset($larr[0]['r_name'])) {
		$larr[0]['r_name'] = "";
	}
	if (!isset($larr[0]['r_besitzer'])) {
		$larr[0]['r_besitzer'] = "";
	}
	if (!isset($larr[0]['r_topic'])) {
		$larr[0]['r_topic'] = "";
	}
	
	$r_name = $larr[0]['r_name'];
	$r_besitzer = $larr[0]['r_besitzer'];
	$r_topic = $larr[0]['r_topic'];
	$level = "user";
	$level2 = "user";
	if ($r_besitzer == $u_id) {
		$level = "owner";
	}
	if ($admin || $u_level == "A") {
		$level = "admin";
	}
	if ($u_level == "C" || $u_level == "S") {
		$level2 = "admin";
	}
	flush();
		
	// Anzeige der Benutzer
	for ($k = 0; is_array($larr[$k]) && $v = $larr[$k]; $k++) {
		if ( $k % 2 != 0 ) {
			$farbe_tabelle = 'class="tabelle_zeile1"';
		} else {
			$farbe_tabelle = 'class="tabelle_zeile2"';
		}
		
		if ($v['u_away']) {
			$user = "(" . zeige_userdetails($v['u_id'], $v, FALSE, "&nbsp;", "", "", TRUE, FALSE, FALSE) . ")";
		} else {
			$user = zeige_userdetails($v['u_id'], $v);
		}
		
		$trow .= "<tr>";
		$trow .= "<td $farbe_tabelle>" . $f1 . "<b>";
		
		if ($seitenleiste) {
			if ($level == "admin") {
				$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"gaguser('" . $v['u_nick'] . "'); return(false)\">G</a>&nbsp;";
			}
			
			if ($level == "admin" || $level == "owner") {
				$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"kickuser('" . $v['u_nick'] . "'); return(false)\">K</a>&nbsp;";
			}
			
			if ($level2 == "admin") {
				$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"sperren('" . $v['u_nick'] . "'); return(false)\">S</a>&nbsp;";
			}
			
			if ($level == "admin" || $level == "owner") {
				$trow .= "&nbsp;";
			}
			
			$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"appendtext_chat(' @" . $v['u_nick'] . " '); return(false)\">@</a>&nbsp;";
			$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"appendtext_chat('/msg " . $v['u_nick'] . " '); return(false)\">&gt;</a>&nbsp;";
		} else {
			if ($level == "admin" || $level == "owner") {
				$trow .= "<a href=\"#\" onMouseOver=\"return(true)\" onClick=\"einladung('" . $v['u_nick'] . "'); return(false)\">E</a>&nbsp;";
			}
			if ($level == "admin" || $level == "owner") {
				$trow .= "&nbsp;";
			}
		}
		
		$trow .= "</b>" . $user . $f2;
		$trow .= "</td></tr>";
	}
	
	$text .= "<table style=\"width:100%;\">$trow</table>\n";
	
	return $text;
}

?>