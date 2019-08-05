<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews', 'results', 'ranking');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
/**
 * kml file laden
 */            
if ( !empty($this->mapconfig) )
{
if ( $this->mapconfig['map_kmlfile'] && $this->project )
{
$this->kmlpath = Uri::root().'tmp'.DIRECTORY_SEPARATOR.$this->project->id.'-ranking.kml';
$this->kmlfile = $this->project->id.'-ranking.kml';
}
}
?>
<div class="<?php echo $this->divclasscontainer;?>" id="resultsranking">

<?php 
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}

echo $this->loadTemplate('projectheading');

if ( array_key_exists('show_matchday_dropdown', $this->config) )
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
  
if ( $this->params->get('what_to_show_first', 0) )
{
$this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';
$this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE'] = 'ranking';  
}
else
{
$this->output['COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE'] = 'ranking';
$this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';
}

if ( $this->params->get('show_ranking_reiter', 0) )
{ 	
echo $this->loadTemplate('show_tabs');
}
else
{
echo $this->loadTemplate('no_tabs');	
}
	
if ( array_key_exists('show_colorlegend', $this->config) )
{
if ($this->config['show_colorlegend'])
{
echo $this->loadTemplate('colorlegend');
}
}

if ( array_key_exists('show_explanation', $this->config) )
{	
if ( $this->config['show_explanation'] )
{
echo $this->loadTemplate('explanation');
}
}
	
if ( $this->params->get('show_map', 0) )
{ 
echo $this->loadTemplate('googlemap');
}   

if ( array_key_exists('show_pagnav', $this->config) )
{
if ( $this->config['show_pagnav'] )
{
echo $this->loadTemplate('pagnav');
}
}
	
echo $this->loadTemplate('jsminfo');
?>
</div>
