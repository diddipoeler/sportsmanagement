<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage results
 * @file       form_row_bootstrap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

$match = $this->game;
$i = $this->i;
$thismatch = Table::getInstance('Match', 'sportsmanagementTable');
$thismatch->bind(get_object_vars($match));

list($datum,$uhrzeit) = explode(' ', $thismatch->match_date);

if (isset($this->teams[$thismatch->projectteam1_id]))
{
	$team1 = $this->teams[$thismatch->projectteam1_id];
}


if (isset($this->teams[$thismatch->projectteam2_id]))
{
	$team2 = $this->teams[$thismatch->projectteam2_id];
}

	  $user = Factory::getUser();

if (isset($team1) && isset($team2))
{
	$userIsTeamAdmin = ( $user->id == $team1->admin || $user->id == $team2->admin );
}
else
{
	$userIsTeamAdmin = $this->isAllowed;
}

$teams = $this->teams;
$teamsoptions[] = HTMLHelper::_('select.option', '0', '- ' . Text::_('Select Team') . ' -');

foreach ($teams AS $team)
{
	$teamsoptions[] = HTMLHelper::_('select.option', $team->projectteamid, $team->name, 'value', 'text');
}







?>

<div class="row-fluid" style="">
<div class="<?php echo $this->divclass; ?>" style="">
<input type='checkbox' id='cb<?php echo $i; ?>' name='cid[]' value='<?php echo $thismatch->id; ?>' />
</div>
<!-- Edit match details -->
<div class="<?php echo $this->divclass; ?>" style="">
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'edit', $team1->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit' . $thismatch->id, 'administrator/components/com_sportsmanagement/assets/images/edit.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_MATCH_DETAILS_BACKEND'), '20', $url);
?>
</div>
<!-- Edit round -->
<div class="<?php echo $this->divclass; ?>" style="">
<?PHP
$append = ' class="inputbox" size="1" onchange="document.getElementById(\'cb<?php echo $i; ?>\').checked=true; " style="font-size:9px;" ';
echo HTMLHelper::_('select.genericlist', $this->roundsoption, 'round_id' . $thismatch->id, $append, 'value', 'text', $thismatch->round_id);
	?>

</div>

<!-- Edit match number -->
<div class="<?php echo $this->divclass; ?>" style="">
<input type='text' style='font-size: 9px;' class='inputbox' size='3' name='match_number<?php echo $thismatch->id; ?>'
value="<?php echo $thismatch->match_number;?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
</div>

<!-- Edit date -->
<div class="<?php echo $this->divclass; ?>" style="">
<?php
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
?>
<div class="well">
<input type="text" class="span2" name='match_date<?php echo $thismatch->id; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " value="<?php echo sportsmanagementHelper::convertDate($datum, 1);?>" data-date-format="dd-mm-yyyy" id="<?php echo 'match_date' . $thismatch->id;?>" >
</div>

				  <script>
jQuery('#<?php echo 'match_date' . $thismatch->id;?>').datepicker();
</script>      
<?PHP
}
else
{
	echo HTMLHelper::calendar(
		sportsmanagementHelper::convertDate($datum, 1),
		'match_date' . $thismatch->id,
		'match_date' . $thismatch->id,
		'%d-%m-%Y',
		'size="9"  style="font-size:9px;" onchange="document.getElementById(\'cb' . $i . '\').checked=true; "'
	);
}
					?>

</div>

<!-- Edit start time -->
<div class="<?php echo $this->divclass; ?>" style="">
<input type='text' style='font-size: 9px;' size='3' name='match_time<?php echo $thismatch->id; ?>' value='<?php echo substr($uhrzeit, 0, 5); ?>'
class='inputbox' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
</div>

<!-- Edit home team -->
<div class="<?php echo $this->divclass; ?>" style="">
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'editlineup', $team1->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('home_lineup' . $team1->projectteamid, 'administrator/components/com_sportsmanagement/assets/images/players_add.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_HOME'), '20', $url);
?>  


<!-- Edit home team -->
<?php
$append = ' class="inputbox" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true; " style="font-size:9px;" ';

if ((!$userIsTeamAdmin) && (!$match->allowed))
{
	$append .= ' disabled="disabled"';
}


if (!isset($team1->projectteamid))
{
	$team1->projectteamid = 0;
}

echo HTMLHelper::_('select.genericlist', $teamsoptions, 'projectteam1_id' . $thismatch->id, $append, 'value', 'text', $team1->projectteamid);
?>

</div>
<!-- Edit away team -->
<div class="<?php echo $this->divclass; ?>" style="">
<?php
if (!isset($team2->projectteamid))
{
	$team2->projectteamid = 0;
}

echo HTMLHelper::_('select.genericlist', $teamsoptions, 'projectteam2_id' . $thismatch->id, $append, 'value', 'text', $team2->projectteamid);
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'editlineup', $team2->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('away_lineup' . $team2->projectteamid, 'administrator/components/com_sportsmanagement/assets/images/players_add.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_AWAY'), '20', $url);
?>
</div>

<!-- Edit match results -->
<div class="<?php echo $this->divclass; ?>" style="">


</div>

<div class="<?php echo $this->divclass; ?>" style="">
<!-- Edit match events -->
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'editevents', $team1->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_events' . $thismatch->id, 'administrator/components/com_sportsmanagement/assets/images/events.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_BACKEND'), '20', $url);
?>
<!-- Edit match statistics -->
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'editstats', $team1->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_statistics' . $thismatch->id, 'administrator/components/com_sportsmanagement/assets/images/calc16.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS_BACKEND'), '20', $url);
?>
<!-- Edit referee -->
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid, $thismatch->id, 'editreferees', $team1->projectteamid, $datum, null, sportsmanagementModelResults::$cfg_which_database, sportsmanagementModelProject::$seasonid, sportsmanagementModelProject::$roundslug, 0, 'form');
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('editreferees' . $thismatch->id, 'administrator/components/com_sportsmanagement/assets/images/players_add.png', Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE_BACKEND'), '20', $url);
?>




</div>






</div>

<?php




