<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage sishandball
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('behavior.modal'); 
require_once('components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xshv2.lib.core.php');
$modal_popup_width = ComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_width',0) ;
$modal_popup_height = ComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_height',0) ;

$document =  Factory::getDocument();
$cssHTML = '<link href="components/com_sportsmanagement/assets/css/sis.css" rel="stylesheet" type="text/css" />' . "\n";
//$app->addCustomHeadTag( $cssHTML );
$document->addCustomTag( $cssHTML );

echo HTMLHelper::image('components/com_sportsmanagement/assets/images/sislogo.png', $this->params->get('page_title'), array('title' => $this->params->get('page_title') ));

// Anzeige Titel
if ($this->params->get('show_page_title', 1) && $this->params->get('page_title') != $this->article->title) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
<?php endif;
// Anzeige Titelend

//Anzeige tabelle
if ($this->tabelle) {
	$colspan = 9;

	if ( $this->params->get( 'sis_getxmldatei') ) {

	}

	echo '<table id="sistabelle">';

	foreach ($this->tabelle->Spielklasse as $klasse) {
		echo '<tr>';
		echo '<th align="left" colspan="'.$colspan.'">';
		echo $klasse->Name;
		if( $this->params->get( 'sis_ueberschrift') ) {
			echo ' '.$this->params->get( 'sis_ueberschrift');
		}
		echo '</th>';
		echo '</tr>';
	}

	$lnr = (string) $klasse->Liga;
	//echo $lnr;
	// XML File
	$filepath='components/com_sportsmanagement/data/';
	//File laden
	
	$datei = ($filepath.'tab_sis_art_'.$this->params->get( 'sis_art').'_ln_'.$lnr.'.xml');
	if (file_exists($datei)) {
		$lastmodify = filemtime($datei);
	}
	   
	echo '<tr class="sisdesc">';
	echo '<td style="text-align:left;">Platz</td>';
	echo '<td style="text-align:left;">Mannschaft</td>';
	echo '<td style="text-align:center;">Spiele</td>';
	echo '<td style="text-align:center;">G</td>';
	echo '<td style="text-align:center;">U</td>';
	echo '<td style="text-align:center;">V</td>';
	echo '<td style="text-align:center;">Tore</td>';
	echo '<td style="text-align:center;">D</td>';
	echo '<td style="text-align:center;">Punkte</td>';
	echo '</tr>';

	$i=1;
	foreach ($this->tabelle->Platzierung as $platz) {
		if( $this->params->get( 'sis_clubnummer') === (string) $platz->Verein || $this->params->get( 'sis_clubnummer1') === (string) $platz->Verein ) {
			echo '<tr class="team">';
		} elseif ($i%2 != 0) {
			echo '<tr class="odd">';
		} else {
			echo '<tr class="even">';
		}
	
		echo '<td>';
		echo $platz->Nr;
		echo '</td>';
		echo '<td>';
		echo $platz->Name;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->Spiele.'/'.$platz->SpieleInsgesamt;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->Gewonnen;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->Unentschieden;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->Verloren;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->TorePlus.':'.$platz->ToreMinus;
		echo '</td>';

		echo '<td align="center">';
		echo $platz->D;
		echo '</td>';
		echo '<td align="center">';
		echo $platz->PunktePlus.':'.$platz->PunkteMinus;
		echo '</td>';
		
		echo '</tr>';
		$i++;
	}
	echo '<tr>';
	echo '<td class="small" colspan="'.$colspan.'">';
	echo '© <a href="'.$this->paramscomponent->get( 'sis_xmllink').'" target="_blank">'.substr($this->paramscomponent->get( 'sis_xmllink'), 7).'</a> - Letzte Aktualisierung: '.date("d.m.Y H:i:s", $lastmodify);
	echo "</td>";
	echo "</tr>";
	echo '</table>';
	//echo xshv2Footer();

}

//Anzeige spielplan
if ($this->spielplan) {
	if( $this->params->get( 'sis_art') == "x" ) {
		date_default_timezone_set('Europe/Vienna');
		//Ablauftest
		/*$heutetest="10.10.2011";
		$heutetest_f = explode('.', $heutetest); 
		$heute = mktime(0, 0, 0, $heutetest_f[1], $heutetest_f[0], $heutetest_f[2]);*/
		//Ablauftest end
		$heute = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		$futuredate = mktime(0, 0, 0, 1, 1, $this->params->get( 'sis_jahr'));
		$zaehler = 1;
		$colspan = 9;
		
		$wtag = Array(
	"Mon" => "Mo.",
	"Tue" => "Di.",
	"Wed" => "Mi.",
	"Thu" => "Do.",
	"Fri" => "Fr.",
	"Sat" => "Sa.",
	"Sun" => "So."
);

		$monat = Array(
	"Jan" => "Jänner",
	"Feb" => "Februar",
	"Mar" => "März",
	"Apr" => "April",
	"May" => "Mai",
	"Jun" => "Juni",
	"Jul" => "Juli",
	"Aug" => "August",
	"Sep" => "September",
	"Oct" => "Oktober",
	"Nov" => "November",
	"Dec" => "Dezember"
);
		

		$lnr = $this->spielplan->Spielklasse->Liga;
		$filepath='components/com_sportsmanagement/data/';
		//File laden
		$datei = ($filepath.'sp_sis_art_1_ln_'.$lnr.'.xml');
		if (file_exists($datei)) {
			$lastmodify = filemtime($datei);
		}
		
		if ( $this->params->get( 'sis_getxmldatei') ) {
		
		}
		

		if( $this->params->get( 'sis_ueberschrift') ) {
			echo '<div class="ueberschrift">';
			echo $this->params->get( 'sis_ueberschrift');
			echo '</div>';
		}

		if ( $this->params->get( 'sis_getliganummer') ) {
			echo '<div class="liganame">';
			echo 'Liganame: ';
			echo $this->spielplan->Spielklasse->Name;
			echo '</div>';
		}
		
		foreach ($this->spielplan->Spiel as $spiel) {
			
			if((string) $spiel->Mannschaft1 === $this->params->get( 'sis_clubnummer') || (string) $spiel->Mannschaft2 === $this->params->get( 'sis_clubnummer') || (string) $spiel->Mannschaft1 === $this->params->get( 'sis_clubnummer1') || (string) $spiel->Mannschaft2 === $this->params->get( 'sis_clubnummer1')) {
				$wtagsp = $wtag[date('D', strtotime ($spiel->Datum))];
				$monatsp = $monat[date('M', strtotime ($spiel->Datum))];
				$sdatum = mktime(0, 0, 0, date('m', strtotime ($spiel->Datum))  , date('d', strtotime ($spiel->Datum)), date('Y', strtotime ($spiel->Datum)));
				//echo 'Datum: '.$sdatum.' - heute: '.$heute;
				if($heute <= $sdatum && $sdatum < $futuredate) {
					echo '<span class="contentheading">'.$spiel->LigaName.' ';
					echo ((string) $spiel->Mannschaft1 === $this->params->get( 'sis_clubnummer') || (string) $spiel->Mannschaft1 === $this->params->get( 'sis_clubnummer1')) ? ''.$spiel->Heim.' ' : ''.$spiel->Gast.' ';
					echo 'Spiel: '.$wtagsp.', '.$spiel->Datum.'</span><br><br>';
					echo '<span class="contentpaneopen"><b>Termin:</b> '.$wtagsp.', '.date('j.', strtotime ($spiel->Datum)).' '.$monatsp.' '.date('Y', strtotime ($spiel->Datum)).' <br />';
					echo '<b>Zeit:</b> '.substr($spiel->vonUhrzeit, 0, -3).' Uhr <br />';
					echo '<b>Mannschaften:</b> '.$spiel->Heim.' - '.$spiel->Gast.' <br />';
					echo '<b>Spielort:</b> '.$spiel->HallenName.' (<a href="http://maps.google.com/maps?hl=de&ie=UTF8&q='.$spiel->HallenStrasse.', '.$spiel->HallenOrt.'&t=m&z=70" target="_blank">Karte</a>)<br />';
					echo ($this->params->get( 'sis_link1')) ? '<a href="'.$this->params->get( 'sis_link1').'" target="_top">'.$this->params->get( 'sis_link1name').'</a>' : '';
					echo ($this->params->get( 'sis_link1') && $this->params->get( 'sis_link2')) ? ' | ' : '';
					echo ($this->params->get( 'sis_link2')) ? '<a href="'.$this->params->get( 'sis_link2').'" target="_top">'.$this->params->get( 'sis_link2name').'</a>' : '';
					echo ($this->params->get( 'sis_link2') && $this->params->get( 'sis_linkhilfeics') || $this->params->get( 'sis_link1') && $this->params->get( 'sis_linkhilfeics')) ? ' | ' : '';
					echo ($this->params->get( 'sis_ics_dir') && $this->params->get( 'sis_ics_vereinsnameshort') &&$this->params->get( 'sis_ics')) ? '<a href="'.$SERVER['SERVER_NAME'].DIRECTORY_SEPARATOR.$this->params->get( 'sis_ics_dir').'/'.$this->params->get( 'sis_ics_vereinsnameshort').'-spiele_'.$this->params->get( 'sis_ics').'.ics" title="Bitte den Link kopieren, und in iCal oder Outlook f&uuml;r Kalender abonnieren verwenden" target="_blank">Spielplan abonnieren</a>' : '';
					echo ($this->params->get( 'sis_linkhilfeics')) ? ' (<a href="'.$this->params->get( 'sis_linkhilfeics').'" target="_top">Hilfe</a>)' : '';
					echo '</span><span class="article_seperator">&nbsp;</span><br><br>';
				}
			}
		
		}
		
		echo '<div class="small">';
		echo '© <a href="'.$this->paramscomponent->get( 'sis_xmllink').'" target="_blank">'.substr($this->paramscomponent->get( 'sis_xmllink'), 7).'</a> - Letzte Aktualisierung: '.date("d.m.Y H:i:s", $lastmodify);
		echo "</div>";
		//echo '</table>';
		//echo xshv2Footer();

	} else {
	
	date_default_timezone_set('Europe/Vienna');
	
	$wtag = Array(
	"Mon" => "Mo.",
	"Tue" => "Di.",
	"Wed" => "Mi.",
	"Thu" => "Do.",
	"Fri" => "Fr.",
	"Sat" => "Sa.",
	"Sun" => "So."
	);


	$zaehler = 1;

	if($this->params->get( 'sis_art') == "1a") {
		if ( $this->params->get( 'sis_getschiedsrichter') && !$this->params->get( 'sis_getspielort') || !$this->params->get( 'sis_getschiedsrichter') && $this->params->get( 'sis_getspielort')) {
			$colspan = 6;
		} elseif ( $this->params->get( 'sis_getschiedsrichter') && $this->params->get( 'sis_getspielort')) {
			$colspan = 7;
		} else {
			$colspan = 5;
		}
	} else {
		if ( $this->params->get( 'sis_getschiedsrichter') && !$this->params->get( 'sis_getspielort') || !$this->params->get( 'sis_getschiedsrichter') && $this->params->get( 'sis_getspielort')) {
			$colspan = 8;
		} elseif ( $this->params->get( 'sis_getschiedsrichter') && $this->params->get( 'sis_getspielort')) {
			$colspan = 9;
		} else {
			$colspan = 7;
		}
	}

	if ( $this->params->get( 'sis_getxmldatei') ) {

}

	if( $this->params->get( 'sis_ueberschrift') ) {
		echo '<div class="ueberschrift">';
		echo $this->params->get( 'sis_ueberschrift');
		echo '</div>';
	}

	if ( $this->params->get( 'sis_getliganummer') ) {
		echo '<div class="liganame">';
		echo 'Liganame: '.$this->spielplan->Spielklasse->Name;
		echo '</div>';
	}

	echo '<table id="table_spielplan">';

	echo '<tr>';
	echo '<th style="text-align:left;">Datum</th>';
	echo '<th style="text-align:left;">Zeit</th>';
	echo '<th style="text-align:right;">Heim</th>';
	echo '<th style="text-align:center;"> : </th>';
	echo '<th style="text-align:left;">Gast</th>';
	if($this->params->get( 'sis_art') != "1a") {
		echo '<th style="text-align:center;">Ergebnis</th>';
		echo '<th style="text-align:center;">Punkte</th>';
	}
	if ( $this->params->get( 'sis_getschiedsrichter') ) {
		echo '<th style="text-align:left;">Schiedsrichter</th>';
	}
	if ( $this->params->get( 'sis_getspielort') ) {
		echo '<th style="text-align:left;">Halle</th>';
	}
	echo '</tr>';

	$heute = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	if($this->params->get( 'sis_art') == "1a") {
		$futuredate = mktime(0, 0, 0, 1, 1, $this->params->get( 'sis_jahr'));
	}
	foreach ($this->spielplan->Spiel as $spielplan) {
	//foreach ($this->spielplan as $spielplan) {
		//$row_color = ($zaehler % 2) ? 'odd' : 'even';
		if( $this->params->get( 'sis_clubnummer') === (string) $spielplan->Mannschaft1 || $this->params->get( 'sis_clubnummer1') === (string) $spielplan->Mannschaft2 || $this->params->get( 'sis_clubnummer1') === (string) $spielplan->Mannschaft1 || $this->params->get( 'sis_clubnummer') === (string) $spielplan->Mannschaft2) {
			$row_color = "team";
		} elseif ($zaehler%2 != 0) {
			$row_color = "odd";
		} else {
			$row_color = "even";
		}
		
		if($this->params->get( 'sis_art') == "1a") {
			$wtagsp = $wtag[date('D', strtotime ($spielplan->Datum))];
			$monatsp = $monat[date('M', strtotime ($spielplan->Datum))];
			$sdatum = mktime(0, 0, 0, date('m', strtotime ($spielplan->Datum))  , date('d', strtotime ($spielplan->Datum)), date('Y', strtotime ($spielplan->Datum)));
			if($heute <= $sdatum && $sdatum < $futuredate) {
				echo '<tr class="'.$row_color.'">';
				echo '<td>';
				echo $spielplan->Datum.' ('.$wtag[date('D', strtotime ($spielplan->Datum))].')';
				echo '</td>';
				echo '<td>';
				echo substr($spielplan->vonUhrzeit, 0, -3).' Uhr';
				echo '</td>';
				echo '<td align="right">';
				echo $spielplan->Heim;
				echo '</td>';
				echo '<td align="center"> : </td>';
				echo '<td align="left">';
				echo $spielplan->Gast;
				echo '</td>';
				if ( $this->params->get( 'sis_getschiedsrichter') ) {
					echo '<td>'.$spielplan->GespannName.'</td>';
				}
				if ( $this->params->get( 'sis_getspielort') ) {
					echo '<td>'.$spielplan->HallenName.'<br>'.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt;
					//echo ' (<a href="http://maps.google.com/maps?hl=de&ie=UTF8&q='.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt.'&t=m&z=70" target="_blank">Karte</a>)';
                    echo ' (<a class="modal" rel="{handler: \'iframe\', size: {x: 570, y: 200} }"  href="http://maps.google.com/maps?hl=de&ie=UTF8&q='.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt.'&t=m&z=70" target="_blank">Karte</a>)';
					echo '</td>';
				}
				echo '</tr>';
				$zaehler++;
			}	
		} else {
			echo '<tr class="'.$row_color.'">';
			echo '<td>';
			echo $spielplan->Datum.' ('.$wtag[date('D', strtotime ($spielplan->Datum))].')';
			echo '</td>';
			echo '<td>';
			echo substr($spielplan->vonUhrzeit, 0, -3).' Uhr';
			echo '</td>';
			echo '<td align="right">';
			echo $spielplan->Heim;
			echo '</td>';
			echo '<td align="center"> : </td>';
			echo '<td align="left">';
			echo $spielplan->Gast;
			echo '</td>';
			echo '<td align="center">';
			echo $spielplan->Tore1.':'.$spielplan->Tore2;
			echo '</td>';
			echo '<td align="center">';
			echo $spielplan->Punkte1.':'.$spielplan->Punkte2;
			echo '</td>';
			if ( $this->params->get( 'sis_getschiedsrichter') ) {
				echo '<td>'.$spielplan->GespannName.'</td>';
			}
			if ( $this->params->get( 'sis_getspielort') ) {
				echo '<td>'.$spielplan->HallenName.'<br>'.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt;
				//echo ' (<a href="http://maps.google.com/maps?hl=de&ie=UTF8&q='.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt.'&t=m&z=70" target="_blank">Karte</a>)';
                echo ' (<a class="modal" rel="{handler: \'iframe\' , size: {x: 570, y: 200} }"  href="http://maps.google.com/maps?hl=de&ie=UTF8&q='.$spielplan->HallenStrasse.', '.$spielplan->HallenOrt.'&t=m&z=70" target="_blank">Karte</a>)';
				echo '</td>';
			}
			echo '</tr>';
			$zaehler++;
		} //end else 1a
	}

//}
$lnr = $this->spielplan->Spielklasse->Liga;
$filepath='components/com_sportsmanagement/data/';
//File laden
$datei = ($filepath.'sp_sis_art_'.($this->params->get( 'sis_art')=="1a" ? "1" : $this->params->get( 'sis_art')).'_ln_'.$lnr.'.xml');
if (file_exists($datei)) {
	$lastmodify = filemtime($datei);
}

echo '<tr class="small">';
echo '<td colspan="'.$colspan.'">© <a href="'.$this->paramscomponent->get( 'sis_xmllink').'" target="_blank">'.substr($this->paramscomponent->get( 'sis_xmllink'), 7).'</a> - Letzte Aktualisierung: '.date("d.m.Y H:i:s", $lastmodify).'</td>';
echo "</tr>";
echo '</table>';
//echo xshv2Footer();

}
}


//Anzeige statistik
if ($this->statistik) {
	$colspan = 5;

	if ( $this->params->get( 'sis_getxmldatei') ) {
	}

	echo '<table id="sistabelle">';

	foreach ($this->statistik->Spielklasse as $klasse) {
		echo '<tr>';
		echo '<th align="left" colspan="'.$colspan.'">';
		echo $klasse->Name;
		if( $this->params->get( 'sis_ueberschrift') ) {
			echo ' '.$this->params->get( 'sis_ueberschrift');
		}
		echo '</th>';
		echo '</tr>';
	}

	$lnr = (string) $klasse->Liga;
	//echo $lnr;
	// XML File
	$filepath='components/com_sportsmanagement/data/';
	//File laden
	
	$datei = ($filepath.'stat_'.$lnr.'.xml');
	if (file_exists($datei)) {
		$lastmodify = filemtime($datei);
	}
	   
	echo '<tr class="sisdesc">';
	echo '<td>Name</td>';
	echo '<td align="center">Tore</td>';
	echo '<td align="center">7m -/+</td>';
	echo '<td align="center">Spiele</td>';
	echo '<td>Mannschaft</td>';
	echo '</tr>';

	$i=1;
	foreach ($this->statistik->Spieler as $spl) {
		if ($i%2 != 0) {
			echo '<tr class="odd">';
		} else {
			echo '<tr class="even">';
		}
		if( $this->params->get( 'sis_art') == "12a") {
			echo '<td>';
			echo $spl->Name;
			echo '</td>';
			echo '<td align="center">';
			echo $spl->AnzTore;
			echo '</td>';
			echo '<td align="center">';
			echo $spl->SiebenMeterVersuche.'/'.$spl->SiebenMeterGetroffen;
			echo '</td>';
			echo '<td align="center">';
			echo $spl->AnzahlSpiele;
			echo '</td>';
			echo '<td>';
			echo $spl->MannschaftsName;
			echo '</td>';
			echo '</tr>';
			$i++;
		} else {
			if( $this->params->get( 'sis_clubnummer') === (string) $spl->Mannschaft) {
				echo '<td>';
				echo $spl->Name;
				echo '</td>';
				echo '<td align="center">';
				echo $spl->AnzTore;
				echo '</td>';
				echo '<td align="center">';
				echo $spl->SiebenMeterVersuche.'/'.$spl->SiebenMeterGetroffen;
				echo '</td>';
				echo '<td align="center">';
				echo $spl->AnzahlSpiele;
				echo '</td>';
				echo '<td>';
				echo $spl->MannschaftsName;
				echo '</td>';
				echo '</tr>';
				$i++;	
			}
		}
	}
	echo '<tr>';
	echo '<td class="small" colspan="'.$colspan.'">';
	echo '© <a href="'.$this->paramscomponent->get( 'sis_xmllink').'" target="_blank">'.substr($this->paramscomponent->get( 'sis_xmllink'), 7).'</a> - Letzte Aktualisierung: '.date("d.m.Y H:i:s", $lastmodify);
	echo "</td>";
	echo "</tr>";   
	echo '</table>';
	//echo xshv2Footer();

}

?>
