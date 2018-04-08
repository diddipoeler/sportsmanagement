<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage resultsmatrix
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews', 'results', 'matrix');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<!-- <a name="jl_top" id="jl_top"></a> -->
	<?php 
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    echo $this->loadTemplate('debug');
}
	echo $this->loadTemplate('projectheading');
		
	echo $this->loadTemplate('selectround');

// diddipoeler
  // aufbau der templates
  $this->output = array();
  
  $this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';
  if(isset($this->divisions) && count($this->divisions) > 1) 
  {
	//echo $this->loadTemplate('matrix_division');
    $this->output['COM_SPORTSMANAGEMENT_MATRIX'] = 'matrix_division';
    }
    else
    {
	//echo $this->loadTemplate('matrix');
    $this->output['COM_SPORTSMANAGEMENT_MATRIX'] = 'matrix';
    }

echo $this->loadTemplate('show_tabs');
  


	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
