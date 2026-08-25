<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       defaul_careerstats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
                        <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                        <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                        <th class="td_c"><?php
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
                            echo HTMLHelper::image(
                                'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/played.png',
                                $imageTitle,
                                ['title' => $imageTitle, 'width' => 20, 'height' => 20]
                            );
                            ?></th>
                        <?php
                        if ($this->config['show_careerstats'] && !empty($this->stats))
                        {
                            foreach ($this->stats as $stat)
                            {
                                ?>
                                <th class="td_c"><?php echo $stat->getImage(); ?></th>
                                <?php
                            }
                        }
                        ?>
                    </tr>
                    <?php
                    $career = ['played' => 0];

                    if (count($this->history) > 0)
                    {
                        $model = $this->getModel();

                        foreach ($this->history as $player_hist)
                        {
                            $present = $model->getPresenceStats($player_hist->project_id, $player_hist->pid);
                            ?>
                            <tr>
                                <td class="td_l" nowrap="nowrap"><?php echo $player_hist->project_name; ?></td>
                                <td class="td_l nowrap"><?php echo $player_hist->team_name; ?></td>

                                <td>
                                    <?php
                                    echo ModalImageHelper::render(
                                        'careerstats' . $player_hist->project_id . '-' . $player_hist->team_id,
                                        (string) $player_hist->season_picture,
                                        (string) $player_hist->team_name,
                                        $this->config['picture_width'],
                                        '',
                                        $this->modalwidth,
                                        $this->modalheight,
                                        (int) $this->overallconfig['use_jquery_modal']
                                    );
                                    ?>
                                </td>
                                <td class="td_c"><?php
                                    echo ($present > 0) ? $present : '-';
                                    $career['played'] += $present;
                                    ?></td>
                                <?php
                                if ($this->config['show_careerstats'] && !empty($this->staffstats))
                                {
                                    foreach ($this->stats as $stat)
                                    {
                                        ?>
                                        <td class="td_c">
                                            <?php echo isset($this->staffstats[$stat->id][$player_hist->project_id])
                                                ? $this->staffstats[$stat->id][$player_hist->project_id]
                                                : '-'; ?>
                                        </td>
                                        <?php
                                    }
                                }
                                ?>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    <tr class="career_stats_total">
                        <td class="td_r" colspan="3">
                            <b><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
                        <td class="td_c"><?php echo ($career['played'] > 0) ? $career['played'] : '-'; ?></td>
                        <?php
                        if ($this->config['show_careerstats'] && !empty($this->historystats))
                        {
                            foreach ($this->stats as $stat)
                            {
                                ?>
                                <td class="td_c">
                                    <?php echo isset($this->historystats[$stat->id])
                                        ? $this->historystats[$stat->id]
                                        : '-'; ?>
                                </td>
                                <?php
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
