<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_previousx.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch">
	<?php
	foreach ($this->teams as $currentteam)
	{
		?>

		<?php if (isset($this->previousx[$currentteam->id])) : ?>
        <!-- Start of last 5 matches -->
    <?php
unset($this->notes);
$this->notes[] = Text::sprintf('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS', $this->allteams[$currentteam->id]->name) . " " . $this->club->name;
echo $this->loadTemplate('jsm_notes'); 
?>

        <table class="table">
            <tr>
                <td>
                    <table class="<?php echo $this->config['hystory_table_class']; ?>">
						<?php
						$pr_id = 0;
						$k     = 0;

						foreach ($this->previousx[$currentteam->id] as $game)
						{
							$routeparameter                       = array();
							$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
							$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
							$routeparameter['p']                  = $game->project_slug;
							$routeparameter['r']                  = $game->round_slug;
							$routeparameter['division']           = 0;
							$routeparameter['mode']               = 0;
							$routeparameter['order']              = '';
							$routeparameter['layout']             = '';
							$result_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

							$routeparameter                       = array();
							$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
							$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
							$routeparameter['p']                  = $game->project_slug;
							$routeparameter['mid']                = $game->match_slug;
							$report_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter);

							$home = $this->allteams[$game->projectteam1_id];
							$away = $this->allteams[$game->projectteam2_id];
							?>
                            <tr class="">
                                <td><?php
									echo HTMLHelper::link($result_link, $game->roundcode);
									?></td>
                                <td nowrap="nowrap"><?php
									echo HTMLHelper::date($game->match_date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
									?></td>
                                <td><?php
									echo substr($game->match_date, 11, 5);
									?></td>
                                <td nowrap="nowrap"><?php
									echo $home->name;
									?></td>

                                <td class="nowrap"><?php
									echo sportsmanagementHelperHtml::getBootstrapModalImage(
										'nextmatchprev' . $game->id . '-' . $game->projectteam1_id,
										$game->home_picture,
										$home->name,
										'20',
										'',
										$this->modalwidth,
										$this->modalheight,
										$this->overallconfig['use_jquery_modal']
									);


									?></td>

                                <td nowrap="nowrap">-</td>

                                <td class="nowrap"><?php
									echo sportsmanagementHelperHtml::getBootstrapModalImage(
										'nextmatchprev' . $game->id . '-' . $game->projectteam2_id,
										$game->away_picture,
										$away->name,
										'20',
										'',
										$this->modalwidth,
										$this->modalheight,
										$this->overallconfig['use_jquery_modal']
									);
									?></td>

                                <td nowrap="nowrap"><?php
									echo $away->name;
									?></td>
                                <td nowrap="nowrap"><?php
									echo $game->team1_result;
									?></td>
                                <td nowrap="nowrap"><?php echo $this->overallconfig['seperator']; ?></td>
                                <td nowrap="nowrap"><?php
									echo $game->team2_result;
									?></td>
                                <td nowrap="nowrap"><?php
									if ($game->show_report == 1)
									{
										$desc = HTMLHelper::image(
											Uri::base() . "media/com_sportsmanagement/jl_images/zoom.png",
											Text::_('Match Report'),
											array("title" => Text::_('Match Report'))
										);
										echo HTMLHelper::link($report_link, $desc);
									}
									$k = 1 - $k;
									?></td>
								<?php if (($this->config['show_thumbs_picture'])) : ?>
                                    <td><?php echo sportsmanagementHelperHtml::getThumbUpDownImg($game, $currentteam->id); ?></td>
								<?php endif; ?>
                            </tr>
							<?php
						}
						?>
                    </table>
                </td>
            </tr>
        </table>
        <!-- End of  show matches -->
	<?php
	endif;

	}
	?>
</div>
