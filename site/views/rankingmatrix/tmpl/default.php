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

var table1 = document.getElementById('rankingall');
console.log('table1: ' + table1.toString() );

var table2 = document.getElementById('matrix');
console.log('table2: ' + JSON.stringify(table2) );


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
