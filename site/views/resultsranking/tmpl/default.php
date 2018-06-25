<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined('_JEXEC') or die('Restricted access');

//echo 'config <pre>'.print_r($this->config ,true).'</pre>';
//echo 'params <pre>'.print_r($this->params ,true).'</pre>';

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews', 'results', 'ranking');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$this->kmlpath = JURI::root().'tmp'.DS.$this->project->id.'-ranking.kml';
$this->kmlfile = $this->project->id.'-ranking.kml';

?>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<!--	<a name="jl_top" id="jl_top"></a> -->
	<?php 
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    echo $this->loadTemplate('debug');
}

	echo $this->loadTemplate('projectheading');

	if ($this->config['show_matchday_dropdown'])
	{
		echo $this->loadTemplate('selectround');
	}

// diddipoeler
  // aufbau der templates
  $this->output = array();
  
  if ( $this->params->get('what_to_show_first', 0) == 0 )
  {
  $this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';
  $this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE'] = 'ranking';
  }
  else
  {
  $this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE'] = 'ranking';
  $this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';  
  }
  
  
  
echo $this->loadTemplate('show_tabs');
   
    
    if ($this->config['show_colorlegend'])
		{
			echo $this->loadTemplate('colorlegend');
		}
		
		if ( $this->config['show_explanation'] )
		{
			echo $this->loadTemplate('explanation');
		}
        if ( $this->config['show_ranking_maps'] )
	{ 
		echo $this->loadTemplate('googlemap');
	}   
    	
	if ( $this->config['show_pagnav'] )
	{
		echo $this->loadTemplate('pagnav');
	}
?>
	<div>
    <?PHP
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	?>
	</div>
    <?PHP
	?>
</div>
