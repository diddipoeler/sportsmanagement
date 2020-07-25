<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_staff_card.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

?>

<link rel="stylesheet" href="<?php echo Uri::base(true); ?>/components/com_sportsmanagement/assets/css/matchreport_2.css">


<!-- START: game roster card -->
<!-- Show Match players card-->
<?php
if (!empty($this->matchstaffpositions))
{
	foreach ($this->matchstaffpositions as $pos)
	{
		$personCount = 0;

		//foreach ($this->matchplayers as $player)
//		{
//			if ($player->position_id == $pos->position_id)
//			{
//				$personCount++;
//			}
//		}
		?>
<div class="row-fluid" id="">
            <div class="col-md-12" id="position">
                <div class="col-md-5" id="clubhomename">
                    <?php echo $this->team1_club->name; ?>
                </div>
                <div class="col-md-2" id="posname">
					<?php echo Text::_($pos->name); ?>
                </div>
                <div class="col-md-5" id="clubawayteam">
                    <?php echo $this->team2_club->name; ?>
                </div>
            </div>

<div class="col-md-12" id="staffrow">
<!-- list of home-team card-->
<div class="col-md-5" id="homestaff">
<?php
foreach ($this->matchstaffs as $player)
{
if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id)
{
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->project->slug;
$routeparameter['tid']                = $player->team_slug;
$routeparameter['pid']                = $player->person_slug;

$player_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
$match_player = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player);
$picture  = $player->picture;
if (!file_exists($picture))
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}

?>
<div class="text-right" id="homesinglestaff">
<?php

if ( $this->config['show_player_profile_link'] )
{
echo HTMLHelper::link($player_link, $match_player);
}
else
{
echo $match_player;    
}
echo '&nbsp;';
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff' . $player->person_id, 
$picture, 
$imgTitle, 
$this->config['staff_picture_height'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);   
?>
</div>
<?php 
 
 
}
}
?>
</div>
<!-- list of line -->
<div class="col-md-2"></div>
<!-- list of guest-team card-->
<div class="col-md-5" id="awaystaff">
<?php
foreach ($this->matchstaffs as $player)
{
if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id)
{
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p']                  = $this->project->slug;
$routeparameter['tid']                = $player->team_slug;
$routeparameter['pid']                = $player->person_slug;

$player_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
$match_player = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);



$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player);
$picture  = $player->picture;
if (!file_exists($picture))
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}

?>
<div class="text-left" id="awaysinglestaff">
<?php

echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff' . $player->person_id, 
$picture, 
$imgTitle, 
$this->config['staff_picture_height'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
echo '&nbsp;';

if ( $this->config['show_player_profile_link'] )
{
echo HTMLHelper::link($player_link, $match_player);
}
else
{
echo $match_player;    
}

?>
</div>
<?php


      
}
}
?>
</div>
</div>
</div>
<?php
}
}
?>

<!-- END of Match staff card-->
