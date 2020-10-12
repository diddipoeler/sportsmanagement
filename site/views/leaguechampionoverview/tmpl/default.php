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

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
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
?>
<h1 class="componentheading">
<?php echo Text::_('Übersicht nach Mannschaft'); ?>
</h1>
<table class="table">
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

<?php












echo $this->loadTemplate('jsminfo');
?>
</div>
