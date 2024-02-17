<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


//echo 'config<pre>'.print_r($this->config,true).'</pre>';
//echo 'leaguechampions<pre>'.print_r($this->leaguechampions,true).'</pre>';
//echo 'leaguechampions_detail<pre>'.print_r($this->leaguechampions_detail,true).'</pre>';

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_tips');
echo $this->loadTemplate('jsm_notes');
    
?>

<style>
li.hm2 {
    /* Textfluss ändern */
    float:left;
    /* Aufzählungspunkte entfernen */
    list-style-type:none;
    /* Abstand */
    margin-right:15px;
}

.legend .row:nth-of-type(odd) div {
background: #f8f9fa;
}
.legend .row:nth-of-type(even) div {
background: #FFFFFF;
}
    
</style>




<div class="<?php echo $this->divclasscontainer; ?> table-responsive legend" id="defaultleaguechampionoverview">
<?php
echo $this->loadTemplate('projectheading');
    
ksort($this->leaguechampions);

$this->notes = array();
$this->notes[] = Text::_('Übersicht nach Saisons');

if ( $this->project->champions_complete )
{
$this->notes[] = Text::_('Alle Meister/Erstplazierte Mannschaften der Saisons vorhanden.');
}
echo $this->loadTemplate('jsm_notes');

?>

  
<?php  
$output = array();
$output_detail = array();
$gesamtspiele_detail = 0;
foreach ($this->leaguechampions_detail as $this->season => $this->project_id)
{  
foreach ($this->project_id as $this->project => $this->team)
{ 
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);      
$output_detail[$this->season][] = $this->config['show_leaguechampionoverview_season'] ? HTMLHelper::link($link, $this->season.' - '.$this->team->project_name).' : ' : '<div class="col-sm-6" id="seasonname">'.HTMLHelper::link($link, $this->season.' - '.$this->team->project_name).' : '.'</div>'   ;    
if ( $this->team->teamid )
{     
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['tid']                = $this->team->teamid;
$routeparameter['ptid']               = $this->team->ptid_slug;
$teaminfo1_link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);  
  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-4">' : ''   ;  
$output_detail[$this->season][] =  HTMLHelper::_('image', $this->team->logo_big, $this->team->teamname, array('width' => '25','height' => 'auto'));  
$output_detail[$this->season][] =  HTMLHelper::link($teaminfo1_link, $this->team->teamname);  
  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;  
  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-2">' : ''   ;  
$output_detail[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$this->team->project_count_matches;  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;    
$gesamtspiele_detail += $this->team->project_count_matches;
}
else
{
if ( $this->team->teamname )
{
$output_detail[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? $this->team->teamname : '<div class="col-sm-4">'.$this->team->teamname.'</div>';        
}
else
{
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);    
$output_detail[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? HTMLHelper::link($link, $this->season) : '<div class="col-sm-4">'.HTMLHelper::link($link, $this->season).'</div>';
}
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-2">' : ''   ;  
$output_detail[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$this->team->project_count_matches;  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;    
$gesamtspiele_detail += $this->team->project_count_matches;
}      
    
}    
}
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-10"></div><div class="col-sm-2">' : ''   ;  
$output_detail[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$gesamtspiele_detail;  
$output_detail[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;       


$gesamtspiele = 0;
foreach ($this->leaguechampions as $this->season => $this->team)
{  
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);   
  
$output[$this->season][] = $this->config['show_leaguechampionoverview_season'] ? HTMLHelper::link($link, $this->season.' - '.$this->team->project_name).' : ' : '<div class="col-sm-4" id="seasonname">'.HTMLHelper::link($link, $this->season.' - '.$this->team->project_name).' : '.'</div>'   ;
if ( $this->team->teamid )
{     
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['tid']                = $this->team->teamid;
$routeparameter['ptid']               = $this->team->ptid_slug;
$teaminfo1_link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);  
  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-6">' : ''   ;  
$output[$this->season][] =  HTMLHelper::_('image', $this->team->logo_big, $this->team->teamname, array('width' => '25','height' => 'auto'));  
$output[$this->season][] =  HTMLHelper::link($teaminfo1_link, $this->team->teamname);  
  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;  
  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-2">' : ''   ;  
$output[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$this->team->project_count_matches;  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;    
$gesamtspiele += $this->team->project_count_matches;
}
else
{
if ( $this->team->teamname )
{
$output[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? $this->team->teamname : '<div class="col-sm-6">'.$this->team->teamname.'</div>';        
}
else
{
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);    
$output[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? HTMLHelper::link($link, $this->season) : '<div class="col-sm-6">'.HTMLHelper::link($link, $this->season).'</div>';
}
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-2">' : ''   ;  
$output[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$this->team->project_count_matches;  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;    
$gesamtspiele += $this->team->project_count_matches;
}  

}  

$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '<div class="col-sm-10"></div><div class="col-sm-2">' : ''   ;  
$output[$this->season][] = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES').':'.$gesamtspiele;  
$output[$this->season][] = !$this->config['show_leaguechampionoverview_season'] ? '</div>' : ''   ;       

//echo 'output<pre>'.print_r($output,true).'</pre>';

if ( $this->config['paulpanzer'] )  
{
?>

<?php
foreach ($output_detail as $season => $printoutput)
{  
?>
<div class="row">  
<?php   
echo implode("", $printoutput);  
?>
  </div>
<?php
}
?>

<?php
}  
elseif ( $this->config['show_leaguechampionoverview_season'] )  
{
?>
<div class="row">
<ul>  
<?php
foreach ($output as $season => $printoutput)
{  
?>
<li class="hm2">   
<?php   
echo implode("", $printoutput);  
?>
</li>   
<?php
}
?>
</ul>  
  </div>
<?php
}  
else
{
foreach ($output_detail as $season => $printoutput)
{   
echo '<div class="row">'.implode("", $printoutput).'</div>';   
}
}
  
?>  
  

<?php
$this->notes = array();
$this->notes[] = Text::_('Übersicht nach Mannschaft');
echo $this->loadTemplate('jsm_notes');



 ?>
<div class="row">
<table class="<?php echo $this->config['table_class'];?> ">
<thead>
<th>
<?php echo Text::_('Mannschaft'); ?>
</th>
<th>
<?php echo Text::_('Titel'); ?>
</th>
<th>
<?php echo Text::_('Saisons'); ?>
</th>
</thead>

<?php
foreach ($this->teamstotal as $this->count_i => $this->team)
{
?>
<tr>
<td>
<?php  
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->leagueteamchampions[$this->team['team_id']]->project_id;
$routeparameter['tid']                = $this->leagueteamchampions[$this->team['team_id']]->teamid;
$routeparameter['ptid']               = $this->leagueteamchampions[$this->team['team_id']]->ptid_slug;
$teaminfo1_link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);  
// echo $teaminfo1_link ;  
?>  
<?php 
echo HTMLHelper::_('image', $this->leagueteamchampions[$this->team['team_id']]->logo_big, $this->leagueteamchampions[$this->team['team_id']]->teamname, array('width' => '25','height' => 'auto'));  
echo HTMLHelper::link($teaminfo1_link, $this->leagueteamchampions[$this->team['team_id']]->teamname);  
//echo $this->leagueteamchampions[$this->team['team_id']]->teamname; ?>
</td>
<td>
<?php echo $this->teamseason[$this->team['team_id']]['title']; ?>
</td>
<td style="word-break:break-all;word-wrap:break-word">
<?php echo implode(",",$this->teamseason[$this->team['team_id']]['season']); ?>
</td>
</tr>
<?php
}
?>
</table>
<div>
<?php

echo $this->loadTemplate('jsminfo');
?>
</div>
