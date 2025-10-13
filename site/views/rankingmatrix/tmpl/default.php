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
    columnStyles: { ".$this->columnwidth." },

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
  doc.addImage(ele.src, 'JPEG', textPos.x , textPos.y , 20, 20);
}
}
    var section = data.cell.section;
    console.log('section: ' + section);

    }

  });

doc.autoTable({
    html: '#' + 'matrix',
    bodyStyles: {minCellHeight: 15},
    columnStyles: { ".$this->columnwidth." },

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
  doc.addImage(ele.src, 'JPEG', textPos.x , textPos.y , 20, 20);
}
}
    var section = data.cell.section;
    console.log('section: ' + section);

    }

  });



doc.save('".$this->view.'-'.$this->project->name.".pdf');




}
";

$this->document->addScriptDeclaration($js);





if ( $this->config['show_button_download_pdf'] )
{


?>
<button onclick="javascript:downpdf()"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'), array(' width' => 40));?>  PDF</button>
<?php
}




?>
<div class="<?php echo $this->divclasscontainer; ?>" id="resultsmatrix">

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
