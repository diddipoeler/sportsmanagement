<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       view.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * fehlerbehandlung
 * https://docs.joomla.org/Using_JLog
 * https://hotexamples.com/examples/-/JLog/addLogger/php-jlog-addlogger-method-examples.html
 * http://eddify.me/posts/logging-in-joomla-with-jlog.html
 * https://github.com/joomla-framework/log/blob/master/src/Logger/Database.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\HTML\HTMLHelper;


if ( ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors', 0) 
&& ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_foruser', 0)
&& ( ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_foruser', 0) == Factory::getUser()->get('id') )
)
{
ini_set('display_errors', ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_front', 0));
ini_set('display_startup_errors', ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_front', 0));  

if ( !ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_level', "") )
{
error_reporting(E_ALL);
}
else
{
$usedlevel = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_level', "");
$levels = (is_array($usedlevel)) ? implode(" | ", $usedlevel) : $usedlevel;
//error_reporting($levels);
error_reporting(E_NOTICE);     
}  
    

if ( ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('show_jsm_errors_file', 0) )
{
ini_set('error_log', "jsm-errors.log");    
}

    
}


/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
	/** Include the component HTML helpers. */
	HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
	HTMLHelper::_('behavior.formvalidator');
	HTMLHelper::_('behavior.keepalive');
	HTMLHelper::_('jquery.framework');
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('behavior.framework', true);
	HTMLHelper::_('behavior.modal');
	HTMLHelper::_('behavior.tooltip');
	HTMLHelper::_('behavior.formvalidation');
}
elseif (version_compare(substr(JVERSION, 0, 3), '2.0', 'ge'))
{
	HTMLHelper::_('behavior.mootools');
}

$document = Factory::getDocument();

$params_com     = ComponentHelper::getParams('com_sportsmanagement');
$jsmgrid        = $params_com->get('use_jsmgrid');
$jsmflex        = $params_com->get('use_jsmflex');
$cssflags       = $params_com->get('cfg_flags_css');
$usefontawesome = $params_com->get('use_fontawesome');
$addfontawesome = $params_com->get('add_fontawesome');

/** Welche joomla version ? */
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
/** css für die nachrichten */
$document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/extended-1.1.css');
$document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/style.css');   
$document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/stylebox.css');        

if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	$document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/extended_4.css');
	$document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/stylebox_4.css');        
}
?>        

<?php       
    
	if ($cssflags)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css';
		$document->addStyleSheet($stylelink);
	}

	if ($jsmflex)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/flex.css';
		$document->addStyleSheet($stylelink);
	}

	if ($jsmgrid)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/grid.css';
		$document->addStyleSheet($stylelink);
	}

	if ($usefontawesome)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/fontawesome_extend.css';
		$document->addStyleSheet($stylelink);
	}

	if ($addfontawesome)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/libraries/fontawesome/css/font-awesome.min.css';
		$document->addStyleSheet($stylelink);
	}
}
elseif (version_compare(JVERSION, '2.5.0', 'ge'))
{
	// Joomla! 2.5 code here
}
elseif (version_compare(JVERSION, '1.7.0', 'ge'))
{
	// Joomla! 1.7 code here
}
elseif (version_compare(JVERSION, '1.6.0', 'ge'))
{
	// Joomla! 1.6 code here
}
else
{
	// Joomla! 1.5 code here
}

/**
 * sportsmanagementView
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementView extends HtmlView
{
	protected $icon = '';
	protected $title = '';
	protected $layout = '';
	protected $tmpl = '';
	protected $table_data_class = '';
	protected $table_data_div = '';
	
	public $bootstrap_fileinput_version = '5.1.2';
	public $bootstrap_fileinput_bootstrapversion = '4.3.1';
	public $bootstrap_fileinput_popperversion = '1.14.7';
	public $chart_version = '2.7.3';
	public $leaflet_version = '1.7.1';
	public $leaflet_css_integrity = 'sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==';
	public $leaflet_js_integrity = 'sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==';
	
	public $leaflet_locatecontrol = '0.72.0';
	public $leaflet_routing_machine = '3.2.12';
	
	public $jsmstartzeit = 0;
	public $jsmendzeit = 0;
	public $jsmseitenaufbau = 0;
    
    /** @var    array    An array of tips */
	public $tips = array();
	/** @var    array    An array of warnings */
	public $warnings = array();
    /** @var    array    An array of notes */
	public $notes = array();

	
	/**
	 * sportsmanagementView::getStartzeit()
	 * 
	 * @return
	 */
	public function getStartzeit()
	{
	$startzeit = explode(" ", microtime());
	$this->jsmstartzeit = $startzeit[0]+$startzeit[1];	
		return $this->jsmstartzeit;
	}
	
	/**
	 * sportsmanagementView::getEndzeit()
	 * 
	 * @return
	 */
	public function getEndzeit()
	{
	$endzeit = explode(" ", microtime());
	$this->jsmendzeit = $endzeit[0]+$endzeit[1];	
		return $this->jsmendzeit;
	//$this->jsmseitenaufbau = round($this->jsmendzeit - $this->jsmstartzeit,6);	
	}
	
	
	/**
	 * sportsmanagementView::display()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	public function display($tpl = null)
	{
	   $this->jsmstartzeit = $this->getStartzeit();

		/**
		 * alle fehlermeldungen online ausgeben
		 * mit der kategorie: jsmerror
		 * JLog::INFO, JLog::WARNING, JLog::ERROR, JLog::ALL, JLog::EMERGENCY or JLog::CRITICAL
		 */
		Log::addLogger(array('logger' => 'messagequeue'), Log::ALL, array('jsmerror'));
		/** fehlermeldungen datenbankabfragen */
		Log::addLogger(array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dblog'));
		/** laufzeit datenbankabfragen */
		Log::addLogger(array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dbperformance'));

		/** Reference global application object */
		$this->app = Factory::getApplication();

		/** JInput object */
		$this->jinput = $this->app->input;

		$this->modalheight = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_height', 600);
		$this->modalwidth  = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_width', 900);

		if (version_compare(JVERSION, '4.0.0', 'ge'))
		{
			$this->uri = Uri::getInstance();
		}
		else
		{
			$this->uri = Factory::getURI();
		}

		$this->action = $this->uri->toString();
		$this->params = $this->app->getParams();
        $this->extended = array();
        $this->extended2 = array();

		/** Get a refrence of the page instance in joomla */
		$this->document           = Factory::getDocument();
		$this->option             = $this->jinput->getCmd('option');
		$this->user               = Factory::getUser();
		$this->view               = $this->jinput->getVar("view");
		$this->cfg_which_database = $this->jinput->getVar('cfg_which_database', '0');

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$this->backbuttonreferer = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$this->backbuttonreferer = getenv('HTTP_REFERER');
		}

		$this->model = $this->getModel();
		$headData    = $this->document->getHeadData();
		$scripts     = $headData['scripts'];
		$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/modalwithoutjs.css');

		$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/jcemediabox.css');
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/jcemediabox.js');

		$headData['scripts'] = $scripts;
		$this->document->setHeadData($headData);

		switch ($this->view)
		{
		case 'predictionrules':
        case 'predictionresults':
        case 'predictionranking':
        case 'predictionusers':
        case 'predictionentry':
        $this->config        = sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
		$this->overallconfig = sportsmanagementModelPrediction::getPredictionOverallConfig();
        $this->config        = array_merge($this->overallconfig, $this->config);
        break;
		case 'jltournamenttree':
		$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
		$this->config        = sportsmanagementModelProject::getTemplateConfig('treetonode', sportsmanagementModelProject::$cfg_which_database);
		$this->config        = array_merge($this->overallconfig, $this->config);
		break;
		case 'ical':
		$this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		break;
		case 'scoresheet':
		$this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		break;
		case 'resultsranking':
		case 'resultsmatrix':
		$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
		break;
		case 'curve':
		case 'stats':
		case 'teamstats':
		$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
		$this->config        = sportsmanagementModelProject::getTemplateConfig($this->getName(), sportsmanagementModelProject::$cfg_which_database);
		$this->flashconfig   = sportsmanagementModelProject::getTemplateConfig('flash', sportsmanagementModelProject::$cfg_which_database);
		$this->config        = array_merge($this->overallconfig, $this->config);
		break;
		default:
		$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
		$this->config        = sportsmanagementModelProject::getTemplateConfig($this->getName(), sportsmanagementModelProject::$cfg_which_database);
		$this->config        = array_merge($this->overallconfig, $this->config);
		break;
		}


/** pdf download */
if ( $this->config['show_button_download_pdf'] )
{
$this->document->addScript('https://unpkg.com/jspdf@2.5.2/dist/jspdf.umd.min.js'); // path to js script
$this->document->addScript('https://unpkg.com/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.js'); // path to js script		
$this->document->addScript('https://html2canvas.hertzen.com/dist/html2canvas.min.js'); // path to js script
  
  $dom = new DOMDocument;
  
$columnStyles = array();  
  
switch ( $this->view )  
{
  case 'ranking':
    $columnStyles[] = "0: {cellWidth: 40}";
    $columnStyles[] = "1: {cellWidth: 40}";
    $columnStyles[] = "2: {cellWidth: 40}";
    $columnStyles[] = "3: {cellWidth: 40}";
    $columnStyles[] = "4: {cellWidth: 320}";
    
    $columnStyles[] = "5: {cellWidth: 25}";
    $columnStyles[] = "6: {cellWidth: 25}";
    $columnStyles[] = "7: {cellWidth: 25}";
    $columnStyles[] = "8: {cellWidth: 30}";
    $columnStyles[] = "9: {cellWidth: 40}";
    $columnStyles[] = "10: {cellWidth: 40}";
    $columnStyles[] = "11: {cellWidth: 40}";
    
    $columnStyles[] = "12: {cellWidth: 'wrap'}";
    
    $this->columnwidth = implode(", ", $columnStyles);
    break;
    case 'results':
    $columnStyles[] = "0: {cellWidth: 10}";
    $columnStyles[] = "1: {cellWidth: 10}";
    $columnStyles[] = "2: {cellWidth: 40}";
    $columnStyles[] = "3: {cellWidth: 40}";
    $columnStyles[] = "4: {cellWidth: 80}";
    $this->columnwidth = implode(", ", $columnStyles);
    break;
}
  
  
  
  
  
$js = "\n";
$js .= "
window.jsPDF = window.jspdf.jsPDF;
window.html2canvas = html2canvas;
function downpdf(tableid) {

let table2 = document.querySelector('#' + tableid);

var table = document.getElementById(tableid);
console.log('table: ' + table );

//alert(document.getElementById(tableid).tHead.innerHTML);
var tableHead = document.getElementById(tableid).tHead;


let headers = document.querySelectorAll(\"#\" + tableid + \" > thead > tr > th\");
console.log('headers: ' + headers );
var arr = [];
arr.push('{title: \"ID\", dataKey: \"id\"}'   );
headers.forEach(node => {
  console.log('node ' + node.innerHTML);
  console.log('node id: ' + node.id);
  console.log('node text: ' + node.innerText);
  arr.push('{title: \"' + node.innerText + '\", dataKey: \"' + node.id + '\"}'   );
});
const sentence = arr.join(\",\");
console.log('sentence ' + sentence); 

const columns = [sentence];
console.log('columns ' + columns); 


let tablerows = document.querySelectorAll(\"#\" + tableid + \" > tbody > tr \");
console.log('tablerows: ' + tablerows );
var anzahlzeilen = document.getElementById(tableid).rows.length; 
console.log('anzahlzeilen: ' + anzahlzeilen );

var arrrows = [];
var i;
i = 1;
tablerows.forEach((row) => {
const cells = Array.from( row.querySelectorAll(\"td\") );
console.log('cells ' + cells); 

let text1 = '{\"id\" :' + i + ',';
cells.forEach((cell) => {
console.log('cell id: ' + cell.id);
console.log('cell text: ' + cell.innerText);
let text2 = '\"' + cell.id + '\" : \"' + cell.innerText + '\",';
text1 = text1.concat(\" \", text2);

});
text1 = text1.concat(\" \", '}');
console.log('text1 ' + text1); 

i++;

});









var doc = new jsPDF('l', 'pt', 'a4');
//doc.autoTable({ html: '#' + tableid })

// Header
const header = '".$this->project->name."';
const view = '".$this->view."';

doc.text(header, 40, 15, { baseline: 'top' });

console.log('header: ' + header );
console.log('view: ' + view );

//var arr = [];
// https://stackoverflow.com/questions/38787437/different-width-for-each-columns-in-jspdf-autotable

//    if ( view === 'ranking' ) {
//    // append multiple values to the array
//arr.push('0: {cellWidth: 40}');
//arr.push('1: {cellWidth: 40}');
//arr.push('2: {cellWidth: 40}');
//arr.push('3: {cellWidth: 40}');
//arr.push('4: {cellWidth: 140}');
//    }
    
//console.log('arr: ' + JSON.stringify(arr));    

//const sentence = arr.join(\",\");
//console.log('sentence ' + sentence); 



doc.autoTable({
    html: '#' + tableid,
    bodyStyles: {minCellHeight: 15},
    //tableWidth: 'auto',
    //styles: {overflow: 'linebreak', columnWidth: '100', font: 'arial', fontSize: 10, cellPadding: 4, overflowColumns: 'linebreak'},
    //columnStyles: { text: { columnWidth: 'auto' } },
    columnStyles: { ".$this->columnwidth." },
    //styles: { cellWidth: 'auto' },
    //tableWidth: doc.internal.pageSize.getWidth(),
    
    
    didDrawCell: function(data) {
    console.log('index: ' + data.column.index );
    
    

    
    //var theli = document.getElementsByTagName('img');
    //console.log('the img: ' + JSON.stringify(theImg));

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
    
    //for (let ele of li) {
    //console.log('li ele: ' + JSON.stringify(ele));
    
    //}
    
    
    var tdklasse = td.getElementsByTagName('img');
    console.log('tdklasse : ' + td.className);
    
   
    
    //if ( data.column.index === 3 ) {
    if ( bilddruck === 'logodruck' ) {
    
    console.log('data cell styles: ' + JSON.stringify(data.cell.styles));
    data.cell.styles.cellWidth = '40';
    data.cell.styles.contentWidth = '40';
    //data.cell.styles.minCellWidth = '40';
    
    
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
}

/** excel download */
if ( $this->config['show_button_download_excel'] )
{
$this->document->addScript("https://unpkg.com/xlsx/dist/shim.min.js");
$this->document->addScript("https://unpkg.com/xlsx/dist/xlsx.full.min.js");
$this->document->addScript("https://unpkg.com/blob.js@1.0.1/Blob.js");
$this->document->addScript("https://unpkg.com/file-saver@1.3.3/FileSaver.js");
$js = "\n";
$js .= "
function downexcel(tableid, fn, dl) {
var type = 'xlsx';
var elt = document.getElementById(tableid);
var wb = XLSX.utils.table_to_book(elt, {sheet:'Sheet JS'});
return dl ?
XLSX.write(wb, {bookType:type, bookSST:true, type: 'base64'}) :
XLSX.writeFile(wb, fn || ('SheetJSTableExport.' + (type || 'xlsx')));
}
";

$this->document->addScriptDeclaration($js);    
}

		/**
		 * flexible einstellung der div klassen im frontend
		 * da man nicht alle templates mit unterschiedlich bootstrap versionen
		 * abfangen kann. hier muss der anwender bei den templates hand anlegen
		 */
		$this->divclasscontainer = isset($this->config['divclasscontainer']) ? $this->config['divclasscontainer'] : 'container-fluid';
		$this->divclassrow       = isset($this->config['divclassrow']) ? $this->config['divclassrow'] : 'row-fluid';

		$this->init();

		$this->addToolbar();

 $this->jsmendzeit  = $this->getEndzeit();
	$this->jsmseitenaufbau = round($this->jsmendzeit - $this->jsmstartzeit,6);
    
		parent::display($tpl);
	}

	/**
	 * sportsmanagementView::init()
	 *
	 * @return void
	 */
	protected function init()
	{

	}

	protected function addToolbar()
	{

	}

}
