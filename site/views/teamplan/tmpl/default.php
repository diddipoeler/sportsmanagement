<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplan
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>  
<div class="<?php echo $this->divclasscontainer; ?>" id="teamplan">
<button id="exportButton" class="btn btn-primary clearfix"><span class="fa fa-file-pdf-o"></span> Export to PDF</button>
<button id="btnPrint" class="btn btn-primary hidden-print"><span class="glyphicon glyphicon-print" aria-hidden="true"></span>Print Preview</button>
  
<script>
jQuery("#btnPrint").printPreview({ 
  obj2print:'#teamplanoutput' 
}); 
</script>  
	
	<?php
	if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
	{
		echo $this->loadTemplate('debug');
	}

	if (!empty($this->project->id))
	{
		echo $this->loadTemplate('projectheading');

		if ($this->config['show_sectionheader'])
		{
			echo $this->loadTemplate('sectionheader');
		}

		if ($this->config['show_plan_layout'] == 'plan_default')
		{
			echo $this->loadTemplate('plan');
		}
        elseif ($this->config['show_plan_layout'] == 'plan_sorted_by_date')
		{
			echo $this->loadTemplate('plan_sorted_by_date');
		}
	}
	else
	{
		echo '<p>' . Text::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED') . '</p>';
	}

	echo $this->loadTemplate('jsminfo');
	?>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $("#exportButton").click(function () {
var element = document.getElementById('teamplanoutput');
var opt = {
  margin:       1,
  filename:     'teamplan.pdf',
  image:        { type: 'jpeg', quality: 0.98 },
  html2canvas:  { scale: 2 },
  jsPDF:        { unit: 'in', format: 'A3', orientation: 'landscape' }
};

// New Promise-based usage:
html2pdf().set(opt).from(element).save();            
        });
    });
</script>
