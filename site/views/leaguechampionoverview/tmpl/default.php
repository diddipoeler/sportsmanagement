<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


//echo 'config<pre>'.print_r($this->config,true).'</pre>';

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
 
</style>




<div class="<?php echo $this->divclasscontainer; ?> table-responsive" id="defaultleaguechampionoverview">
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
foreach ($this->leaguechampions as $this->season => $this->team)
{  
$output[$this->season][] = $this->config['show_leaguechampionoverview_season'] ? $this->season.' : ' : '<div class="col-sm-4">'.$this->season.' : '.'</div>'   ;
if ( $this->team->teamid )
{     
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->team->project_id;
$routeparameter['tid']                = $this->team->teamid;
$routeparameter['ptid']               = $this->team->ptid_slug;
$teaminfo1_link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);  
if ( !$this->config['show_leaguechampionoverview_season']  )  
{
$output[$this->season][] = '<div class="col-sm-8">';
}  
$output[$this->season][] =  HTMLHelper::_('image', $this->team->logo_big, $this->team->teamname, array('width' => 'auto','height' => '25'));  
$output[$this->season][] =  HTMLHelper::link($teaminfo1_link, $this->team->teamname);  
if ( !$this->config['show_leaguechampionoverview_season']  )  
{
$output[$this->season][] = '</div>';
}
  
}
else
{
if ( $this->team->teamname )
{
$output[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? $this->team->teamname : '<div class="col-sm-8">'.$this->team->teamname.'</div>';        
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
$output[$this->season][] =  $this->config['show_leaguechampionoverview_season'] ? HTMLHelper::link($link, $this->season) : '<div class="col-sm-8">'.HTMLHelper::link($link, $this->season).'</div>';
}
}  
}  


//echo 'output<pre>'.print_r($output,true).'</pre>';

if ( $this->config['show_leaguechampionoverview_season'] )  
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
foreach ($output as $season => $printoutput)
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
echo HTMLHelper::_('image', $this->leagueteamchampions[$this->team['team_id']]->logo_big, $this->leagueteamchampions[$this->team['team_id']]->teamname, array('width' => 'auto','height' => '25'));  
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
