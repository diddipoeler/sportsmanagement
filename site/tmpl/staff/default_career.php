<?php
/**
 * Native Joomla 5/6 staff career layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$history = is_array($this->history ?? null) ? $this->history : [];

if (!$history) {
    return;
}

$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$database = (int) $this->input->getInt('cfg_which_database', 0);
$season = (int) $this->input->getInt('s', 0);
$tableClass = $this->escape((string) ($config['table_class'] ?? 'table'));
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$pictureWidth = (int) ($config['picture_width'] ?? 50);
$modalWidth = (int) ($this->modalwidth ?? 100);
$modalHeight = (int) ($this->modalheight ?? 200);
$modalMode = (int) ($overallConfig['use_jquery_modal'] ?? 0);
$personSlug = (string) ($this->person->slug ?? '');
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="staff">
    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER'); ?></h4>
    <table class="<?php echo $tableClass; ?>">
        <tr>
            <td>
                <br>
                <table id="player_history" class="<?php echo $tableClass; ?>">
                    <thead>
                        <tr class="sectiontableheader">
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $station) : ?>
                            <?php
                            $projectSlug = (string) ($station->project_slug ?? '');
                            $teamSlug = (string) ($station->team_slug ?? '');
                            $projectName = (string) ($station->project_name ?? '');
                            $seasonName = (string) ($station->season_name ?? '');
                            $teamName = (string) ($station->team_name ?? '');
                            $positionName = (string) ($station->position_name ?? '');
                            $seasonPicture = (string) ($station->season_picture ?? '');
                            $projectId = (int) ($station->project_id ?? 0);
                            $teamId = (int) ($station->team_id ?? 0);

                            $staffLink = SiteRouteHelper::view('staff', [
                                'cfg_which_database' => $database,
                                's' => $season,
                                'p' => $projectSlug,
                                'tid' => $teamSlug,
                                'pid' => $personSlug,
                            ]);
                            $rosterLink = SiteRouteHelper::view('roster', [
                                'cfg_which_database' => $database,
                                's' => $season,
                                'p' => $projectSlug,
                                'tid' => $teamSlug,
                                'ptid' => 0,
                            ]);
                            ?>
                            <tr>
                                <td class="td_l"><?php echo HTMLHelper::link($staffLink, $this->escape($projectName)); ?></td>
                                <td class="td_l"><?php echo $this->escape($seasonName); ?></td>
                                <td class="td_l"><?php echo HTMLHelper::link($rosterLink, $this->escape($teamName)); ?></td>
                                <td>
                                    <?php
                                    if ($seasonPicture !== '') {
                                        echo ModalImageHelper::render(
                                            'career' . $projectId . '-' . $teamId,
                                            $seasonPicture,
                                            $teamName,
                                            $pictureWidth,
                                            '',
                                            $modalWidth,
                                            $modalHeight,
                                            $modalMode
                                        );
                                    }
                                    ?>
                                </td>
                                <td class="td_l"><?php echo Text::_($positionName); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <br><br>
</div>
