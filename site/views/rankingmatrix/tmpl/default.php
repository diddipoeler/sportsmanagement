<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage Resultsmatrix
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
/**
https://github.com/dompdf/dompdf
 */
require_once JPATH_COMPONENT_SITE  . '/libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


$templatesToLoad = array('globalviews', 'matrix', 'ranking');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
/**
 * kml file laden
 */
if (!empty($this->mapconfig))
{
    if ($this->mapconfig['map_kmlfile'] && $this->project)
    {
        $this->kmlpath = Uri::root() . 'tmp' . DIRECTORY_SEPARATOR . $this->project->id . '-ranking.kml';
        $this->kmlfile = $this->project->id . '-ranking.kml';
    }
}

$columnStylesranking[] = "0: {cellWidth: 40}";
$columnStylesranking[] = "1: {cellWidth: 40}";
$columnStylesranking[] = "2: {cellWidth: 40}";
$columnStylesranking[] = "3: {cellWidth: 40}";
$columnStylesranking[] = "4: {cellWidth: 320}";
$columnStylesranking[] = "5: {cellWidth: 25}";
$columnStylesranking[] = "6: {cellWidth: 25}";
$columnStylesranking[] = "7: {cellWidth: 25}";
$columnStylesranking[] = "8: {cellWidth: 30}";
$columnStylesranking[] = "9: {cellWidth: 40}";
$columnStylesranking[] = "10: {cellWidth: 40}";
$columnStylesranking[] = "11: {cellWidth: 40}";
$columnStylesranking[] = "12: {cellWidth: 'wrap'}";

$countteams = sportsmanagementModelProject::getTeams();
//$this->app->enqueueMessage('<pre>'.print_r(sizeof($countteams),true).'</pre>', 'notice');
$columnStylesmatrix[] = "0: {cellWidth: 20}";
$columnStylesmatrix[] = "1: {cellWidth: 200}";
for($a=2; $a <= sizeof($countteams);$a++)
{
$columnStylesmatrix[] = $a.": {cellWidth: 30}";
}

$mediawikitable = array();
$mediawikitable[] = '{| class="wikitable sortable"';
$mediawikitable[] = '|+ '.$this->project->name;
$mediawikitable[] = '|-';
$mediawikitable[] = '! Platz !! Mannschaft !! gespielt !! gewonnen !! unentschieden !! verloren !! Tore !! Tordifferenz !! Punkte';

/** tabelle als mediawiki tabelle*/
foreach ($this->currentRanking as $division => $cu_rk)
{
//echo __LINE__.'<pre>'.print_r($cu_rk,true).'</pre>';
foreach ($cu_rk as $ptid => $team)
{
$mediawikitable[] = '|-';
$mediawikitable[] = '|'.$team->_finaltablerank.'||'.$team->_name.'||'.$team->_matches_finally.'||'.$team->_won_finally.'||'.$team->_draws_finally.'||'.$team->_lost_finally.'||'.$team->_homegoals_finally.':'.$team->_guestgoals_finally.'||'.$team->_diffgoals_finally.'||'.$team->_points_finally.':'.$team->_neg_points_finally;
                }
}
//echo __LINE__.'<pre>'.print_r($this->currentRanking,true).'</pre>';

$mediawikitable[] = '|}';

/** matrix als mediawiki tabelle*/
$mediawikitable[] = '{| class="wikitable sortable"';
$mediawikitable[] = '|+ Matrix '.$this->project->name;
$mediawikitable[] = '|-';

$ausgabeteams = array();
$ausgabeteams[] = 'Team';
for($a=1; $a <= sizeof($this->teams); $a++)
{
$ausgabeteams[] = $a;
}
$mediawikitable[] = '|'.implode("||",$ausgabeteams);


$team_nummer_row = 1;
/** anfang reihe */
foreach ($this->teams as $team_row_id => $team_row)
{
$ausgabeergebnisse = array();
$mediawikitable[] = '|-';
$team_nummer_col = 1;
/** anfang spalte */
foreach ($this->teams as $team_col_id => $team_col)
{
//$ausgabeteams = array();
//$ausgabeteams[] = '|'.$team_row->name;

if ( $team_nummer_row == $team_nummer_col )
{
$ausgabeergebnisse[] = '---';
}
else
{
$Allresults = '';
$match_result = '';
/** anfang ergebnisse */
foreach ($this->results as $result)
{

if (($result->projectteam1_id == $team_row->projectteamid) && ($result->projectteam2_id == $team_col->projectteamid))
{
$match_result = $result->e1.'-'.$result->e2;
//$ausgabeergebnisse[] = $result->e1.'-'.$result->e2;

/** Don’t break, allow for multiple results */
if ($Allresults == '')
{
$Allresults = $match_result;
}
else
{
$Allresults .= '<br>' . $match_result;
}
}
}
$ausgabeergebnisse[] = $Allresults;
/** ende ergebnisse */
}
$team_nummer_col++;
}
/** ende spalte */
$mediawikitable[] = '|'.$team_row->name.'||'.implode("||",$ausgabeergebnisse);
$team_nummer_row++;
}
/** ende reihe */

$mediawikitable[] = '|}';

$mediawikitable[] = '';
$mediawikitable[] = '';

if (preg_match("/gelsenkirchen/i", $this->project->league_name)) {
$mediawikitable[] = '[[Kategorie:Tabellen Gelsenkirchen]]';
}

$mediawikitable[] = '[[Kategorie:Tabellen '.$this->project->league_name.']]';
$mediawikitable[] = '[[Kategorie:Tabellen Saison '.$this->project->season_name.']]';



$mediawikitext = implode(",",$mediawikitable);

$js = "\n";
$js .= "
window.jsPDF = window.jspdf.jsPDF;
window.html2canvas = html2canvas;
function downpdf() {

var doc = new jsPDF('l', 'pt', 'a4');
//doc.autoTable({ html: '#' + tableid })

// Header
const header = '".$this->project->name."';
const view = '".$this->view."';

doc.text(header, 40, 15, { baseline: 'top' });

console.log('header: ' + header );
console.log('view: ' + view );

doc.autoTable({
    html: '#' + 'rankingall',
    bodyStyles: {minCellHeight: 15},
    columnStyles: { ".implode(", ", $columnStylesranking)." },

    didDrawCell: function(data) {
    console.log('index: ' + data.column.index );
    console.log('data cell: ' + JSON.stringify(data.cell));
    var td = data.cell.raw;
    var bilddruck = td.id;
    console.log('td: ' + td.id);
    console.log('td json: ' + JSON.stringify(td));
    console.log('data.cell.raw json: ' + JSON.stringify(data.cell.raw));
    var textPos = data.cell.getTextPos();
    console.log('textPos.x: ' + textPos.x);
    console.log('textPos.y: ' + textPos.y);
    var li = td.getElementsByTagName('li');
    console.log('li: ' + li);
    console.log('li json: ' + JSON.stringify(li));
    var tdklasse = td.getElementsByTagName('img');
    console.log('tdklasse : ' + td.className);

    if ( bilddruck === 'logodruck' ) {
    console.log('data cell styles: ' + JSON.stringify(data.cell.styles));
    data.cell.styles.cellWidth = '40';
    data.cell.styles.contentWidth = '40';
    var img = td.getElementsByTagName('img');
    console.log('img: ' + img);
    console.log('img json: ' + JSON.stringify(img));
for (let ele of img) {
  console.log('image ele: ' + JSON.stringify(ele));
  console.log('image ele src: ' + ele.src);
  console.log('image ele itemprop: ' + ele.itemprop);
  doc.addImage(ele.src, 'JPEG', textPos.x , textPos.y , 15, 15);
}
}
    var section = data.cell.section;
    console.log('section: ' + section);

    }

  });

doc.addPage();

doc.autoTable({
    html: '#' + 'matrix',
    bodyStyles: {minCellHeight: 15},
    columnStyles: { ".implode(", ", $columnStylesmatrix)." },

    didDrawCell: function(data) {
    console.log('index: ' + data.column.index );
    console.log('data cell: ' + JSON.stringify(data.cell));
    var td = data.cell.raw;
    var bilddruck = td.id;
    console.log('td: ' + td.id);
    console.log('td json: ' + JSON.stringify(td));
    console.log('data.cell.raw json: ' + JSON.stringify(data.cell.raw));
    var textPos = data.cell.getTextPos();
    console.log('textPos.x: ' + textPos.x);
    console.log('textPos.y: ' + textPos.y);
    var li = td.getElementsByTagName('li');
    console.log('li: ' + li);
    console.log('li json: ' + JSON.stringify(li));
    var tdklasse = td.getElementsByTagName('img');
    console.log('tdklasse : ' + td.className);

    if ( bilddruck === 'logodruck' ) {
    console.log('data cell styles: ' + JSON.stringify(data.cell.styles));
    data.cell.styles.cellWidth = '40';
    data.cell.styles.contentWidth = '40';
    var img = td.getElementsByTagName('img');
    console.log('img: ' + img);
    console.log('img json: ' + JSON.stringify(img));
for (let ele of img) {
  console.log('image ele: ' + JSON.stringify(ele));
  console.log('image ele src: ' + ele.src);
  console.log('image ele itemprop: ' + ele.itemprop);
  doc.addImage(ele.src, 'JPEG', textPos.x , textPos.y , 15, 15);
}
}
    var section = data.cell.section;
    console.log('section: ' + section);

    }

  });



doc.save('".$this->project->name.".pdf');




}

function downmediwiki() {
//alert('downmediwiki');
alert('".implode('\r',$mediawikitable)."');

//try {
//        //const text = document.getElementById('textToCopy').value;
//        const text = '".implode('\r',$mediawikitable)."';
//        await navigator.clipboard.writeText(text);
//    } catch (err) {
//        console.error('Fehler beim Kopieren in die Zwischenablage:', err);
//    }






}
";

$this->document->addScriptDeclaration($js);





if ( $this->config['show_button_download_pdf'] )
{
?>
<button onclick="javascript:downpdf()"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'), array(' width' => 40));?>  PDF</button>
<?php
}

if ( $this->config['show_button_download_mediawiki'] )
{
?>
<button onclick="javascript:downmediwiki()"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/mediawiki.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_MEDIAWIKI'), array(' width' => 40));?>  Mediawiki</button>
<?php
}


?>
<div class="<?php echo $this->divclasscontainer; ?>" id="rankingmatrix">

    <?php
    if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
    {
        echo $this->loadTemplate('debug');
    }

    echo $this->loadTemplate('projectheading');

    if (array_key_exists('show_matchday_dropdown', $this->config))
    {
        if ($this->config['show_matchday_dropdown'])
        {
            echo $this->loadTemplate('selectround');
        }
    }

    /**
     * diddipoeler
     * aufbau der templates
     */
    $this->output = array();

    if ($this->params->get('what_to_show_first', 0))
    {
        $this->output['COM_SPORTSMANAGEMENT_MATRX_ROUND_MATRX'] = 'matrix';
        $this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE']    = 'ranking';
    }
    else
    {
        $this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE']    = 'ranking';
        $this->output['COM_SPORTSMANAGEMENT_MATRX_ROUND_MATRX'] = 'matrix';
    }

    if ($this->params->get('show_ranking_reiter', 0))
    {
        echo $this->loadTemplate('show_tabs');
    }
    else
    {
        echo $this->loadTemplate('no_tabs');
    }

    if (array_key_exists('show_colorlegend', $this->config))
    {
        if ($this->config['show_colorlegend'])
        {
            echo $this->loadTemplate('colorlegend');
        }
    }

    if (array_key_exists('show_explanation', $this->config))
    {
        if ($this->config['show_explanation'])
        {
            echo $this->loadTemplate('explanation');
        }
    }

    if ($this->params->get('show_map', 0))
    {
        echo $this->loadTemplate('googlemap');
    }

    if (array_key_exists('show_pagnav', $this->config))
    {
        if ($this->config['show_pagnav'])
        {
            echo $this->loadTemplate('pagnav');
        }
    }

    echo $this->loadTemplate('jsminfo');
    ?>
</div>
