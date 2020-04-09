<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       defaul_careerstats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="staff">
    <!-- Player stats History START -->
    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS'); ?></h4>
    <table class="<?php echo $this->config['table_class']; ?>">
        <tr>
            <td>
                <br/>
                <table id="stats_history" class="<?php echo $this->config['table_class']; ?>">
                    <tr class="sectiontableheader">
                        <th class="td_l"
                            class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                        <th class="td_l" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                        <th class="td_c"><?php
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
							echo HTMLHelper::image(
								'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/played.png',
								$imageTitle, array(' title' => $imageTitle, ' width' => 20, ' height' => 20)
							);
							?></th>
						<?php
						if ($this->config['show_careerstats'])
						{
							if (!empty($stats))
							{
								foreach ($this->stats as $stat)
								{ ?>
                                    <th class="td_c"><?php echo $stat->getImage(); ?></th><?php
								}
							}
						}
						?>
                    </tr>
					<?php
					$k                = 0;
					$career           = array();
					$career['played'] = 0;
					$mod              = BaseDatabaseModel::getInstance('Staff', 'sportsmanagementModel');
					if (count($this->history) > 0)
					{
						foreach ($this->history as $player_hist)
						{
							$model   = $this->getModel();
							$present = $model->getPresenceStats($player_hist->project_id, $player_hist->pid);
							?>
                            <tr class="">
                                <td class="td_l" nowrap="nowrap"><?php
									echo $player_hist->project_name;
									// echo " (".$player_hist->project_id.")";
									?></td>
                                <td class="td_l" class="nowrap"><?php echo $player_hist->team_name; ?></td>

                                <td>
									<?PHP
									//echo $player_hist->season_picture;
									echo sportsmanagementHelperHtml::getBootstrapModalImage('careerstats' . $player_hist->project_id . '-' . $player_hist->team_id, $player_hist->season_picture, $player_hist->team_name, '50');
									?>
                                </td>
                                <!-- Player stats History - played start -->
                                <td class="td_c"><?php
									echo ($present > 0) ? $present : '-';
									$career['played'] += $present;
									?></td>
                                <!-- Player stats History - allevents start -->
								<?php
								if ($this->config['show_careerstats'])
								{
									if (!empty($staffstats))
									{
										foreach ($this->stats as $stat)
										{
											?>
                                            <td class="td_c">
												<?php echo(isset($this->staffstats[$stat->id][$player_hist->project_id]) ? $this->staffstats[$stat->id][$player_hist->project_id] : '-'); ?>
                                            </td>
											<?php
										}
									}
								}
								?>
                                <!-- Player stats History - allevents end -->
                            </tr>
							<?php
							$k = (1 - $k);
						}
					}
					?>
                    <tr class="career_stats_total">
                        <td class="td_r" colspan="2">
                            <b><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
                        <td class="td_c"><?php echo ($career['played'] > 0) ? $career['played'] : '-'; ?></td>
						<?php // stats per project
						if ($this->config['show_careerstats'])
						{
							if (!empty($historystats))
							{
								foreach ($this->stats as $stat)
								{
									?>
                                    <td class="td_c">
										<?php echo(isset($this->historystats[$stat->id]) ? $this->historystats[$stat->id] : '-'); ?>
                                    </td>
									<?php
								}
							}
						}
						?>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<br/>
<!-- staff stats History END -->
