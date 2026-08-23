<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       defaul_career.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (count($this->history) > 0)
{
	?>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="staff">
        <!-- staff history START -->
        <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER'); ?></h4>
        <table class="<?php echo $this->config['table_class']; ?>">
            <tr>
                <td>
                    <br/>
                    <table id="player_history" class="<?php echo $this->config['table_class']; ?>">
                        <tr class="sectiontableheader">
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
                        </tr>
						<?php
						$k = 0;
                        $database = $this->input->getInt('cfg_which_database', 0);
                        $season = $this->input->getInt('s', 0);

						foreach ($this->history AS $station)
						{
                            $link1 = SiteRouteHelper::view('staff', [
                                'cfg_which_database' => $database,
                                's' => $season,
                                'p' => $station->project_slug,
                                'tid' => $station->team_slug,
                                'pid' => $this->person->slug,
                            ]);
                            $link2 = SiteRouteHelper::view('roster', [
                                'cfg_which_database' => $database,
                                's' => $season,
                                'p' => $station->project_slug,
                                'tid' => $station->team_slug,
                                'ptid' => 0,
                            ]);

							?>
                            <tr class="">
                                <td class="td_l"><?php echo HTMLHelper::link($link1, $station->project_name); ?></td>
                                <td class="td_l"><?php echo $station->season_name; ?></td>
                                <td class="td_l"><?php echo HTMLHelper::link($link2, $station->team_name); ?></td>

                                <td>
									<?php
                                    echo ModalImageHelper::render(
                                        'career' . $station->project_id . '-' . $station->team_id,
                                        (string) $station->season_picture,
                                        (string) $station->team_name,
                                        $this->config['picture_width'],
                                        '',
                                        $this->modalwidth,
                                        $this->modalheight,
                                        (int) $this->overallconfig['use_jquery_modal']
                                    );
									?>
                                </td>

                                <td class="td_l"><?php echo Text::_($station->position_name); ?></td>
                            </tr>
							<?php
							$k = (1 - $k);
						}
						?>
                    </table>
                </td>
            </tr>
        </table>
        <br/><br/>
    </div>
    <!-- staff history END -->
	<?php
}
