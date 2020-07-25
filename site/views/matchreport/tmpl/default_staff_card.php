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
<div class="">
            <div class="d-flex flex-row justify-content-between p-2 mb-2 position">
                <div class="5">
                    <?php echo $this->team1_club->name; ?>
                </div>
                <div class="positionid">
					<?php echo Text::_($pos->name); ?>
                </div>
                <div class="">
                    <?php echo $this->team2_club->name; ?>
                </div>
            </div>

<div class="d-flex flex-row justify-content-center">
<!-- list of home-team card-->
<div class="list d-flex flex-row flex-wrap justify-content-start">

</div>
<!-- list of line -->
<div class="line mb-2"></div>
<!-- list of guest-team card-->
<div class="list d-flex flex-row flex-wrap justify-content-end">

</div>
</div>
</div>
<?php
}
}
?>




<!-- Show Match staff card -->
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="matchreport-staff-card">
	<?php
	if (!empty($this->matchstaffpositions))
	{
		?>
        <table class="table">
			<?php
			foreach ($this->matchstaffpositions as $pos)
			{
				?>
                <tr>
                    <td colspan="2" class="positionid"><?php echo Text::_($pos->name); ?></td>
                </tr>
                <tr>
                    <!-- list of home-team -->
                    <td class="list">
                        <div style="text-align: left; ">
                            <ul style="list-style-type: none;">
								<?php
								foreach ($this->matchstaffs as $player)
								{
									if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id)
									{
										?>
                                        <li class="list">
											<?php
											$routeparameter                       = array();
											$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
											$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
											$routeparameter['p']                  = $this->project->slug;
											$routeparameter['tid']                = $player->team_slug;
											$routeparameter['pid']                = $player->person_slug;

											$player_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
											$match_player = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
											echo HTMLHelper::link($player_link, $match_player);
											$imgTitle = Text::sprintf('Picture of %1$s', $match_player);
											$picture  = $player->picture;
											if (!file_exists($picture))
											{
												$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
											}
											echo '&nbsp;';
											echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff' . $player->person_id, $picture, $imgTitle, $this->config['staff_picture_width'],
                                            '',
                                            $this->modalwidth,
                                            $this->modalheight,
                                            $this->overallconfig['use_jquery_modal']);
											?>

                                        </li>
										<?php
									}
								}
								?>
                            </ul>
                        </div>
                    </td>
                    <!-- list of guest-team -->
                    <td class="list">
                        <div style="text-align: right;">
                            <ul style="list-style-type: none;">
								<?php
								foreach ($this->matchstaffs as $player)
								{
									if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id)
									{
										?>
                                        <li class="list">
											<?php
											$match_player = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
											$imgTitle     = Text::sprintf('Picture of %1$s', $match_player);
											$picture      = $player->picture;
											if (!file_exists($picture))
											{
												$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
											}

											echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff' . $player->person_id, $picture, $imgTitle, $this->config['staff_picture_width'],
                                            '',
                                            $this->modalwidth,
                                            $this->modalheight,
                                            $this->overallconfig['use_jquery_modal']);

											?>


											<?php
											echo '&nbsp;';
											$routeparameter                       = array();
											$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
											$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
											$routeparameter['p']                  = $this->project->slug;
											$routeparameter['tid']                = $player->team_slug;
											$routeparameter['pid']                = $player->person_slug;
											$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);

											echo HTMLHelper::link($player_link, $match_player);
											?>
                                        </li>
										<?php
									}
								}
								?>
                            </ul>
                        </div>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
		<?php
	}
	?>
</div>
<!-- END of Match staff card-->
