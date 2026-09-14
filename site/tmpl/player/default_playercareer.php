<?php
/**
 * Native Joomla 5/6 player career layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$history = is_array($this->historyPlayer ?? null) ? $this->historyPlayer : [];

if (!$history) {
    return;
}

$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$clubPlaceholder = trim((string) $componentParams->get('ph_logo_big', ''));
$teamPlaceholder = trim((string) $componentParams->get('ph_team', ''));
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$tableClass = $this->escape((string) ($config['history_table_class'] ?? 'table'));
$databaseSelector = (int) $this->input->getInt('cfg_which_database', 0);
$seasonId = (int) $this->input->getInt('s', 0);
$modalWidth = (int) ($this->modalwidth ?? 100);
$modalHeight = (int) ($this->modalheight ?? 200);
$modalMode = (int) ($overallConfig['use_jquery_modal'] ?? 0);
$showTeam = !empty($config['show_plcareer_team']);
$showPersonPicture = !empty($config['show_plcareer_ppicture']);
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="defaultplayercareer">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER'); ?></h2>

    <table id="playerhistory" class="<?php echo $tableClass; ?> table-responsive">
        <thead>
            <tr class="sectiontableheader">
                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
                <?php if ($showTeam) : ?>
                    <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                <?php endif; ?>
                <?php if ($showPersonPicture) : ?>
                    <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                <?php endif; ?>
                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $station) : ?>
                <?php
                $projectId = (int) ($station->project_id ?? 0);
                $teamId = (int) ($station->team_id ?? 0);
                $projectName = $this->escape((string) ($station->project_name ?? ''));
                $seasonName = $this->escape((string) ($station->season_name ?? ''));
                $teamName = $this->escape((string) ($station->team_name ?? ''));

                $playerRoute = SiteRouteHelper::view('player', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => (string) ($station->project_slug ?? ''),
                    'tid' => (string) ($station->team_slug ?? ''),
                    'pid' => (string) ($station->person_slug ?? ''),
                ]);
                $teamRoute = SiteRouteHelper::view('teaminfo', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => (string) ($station->project_slug ?? ''),
                    'tid' => (string) ($station->team_slug ?? ''),
                    'ptid' => 0,
                ]);

                $projectPicture = trim((string) ($station->project_picture ?? ''));
                if ($projectPicture === '') {
                    $projectPicture = $clubPlaceholder;
                } elseif ($projectPicture === 'images/com_sportsmanagement/database/placeholders/placeholder_150.png') {
                    $projectPicture = trim((string) ($station->league_picture ?? '')) ?: $clubPlaceholder;
                }

                $clubPicture = trim((string) ($station->club_picture ?? '')) ?: $clubPlaceholder;
                $teamPicture = trim((string) ($station->team_picture ?? '')) ?: $teamPlaceholder;
                $seasonPicture = trim((string) ($station->season_picture ?? '')) ?: $teamPlaceholder;
                ?>
                <tr>
                    <td id="show_project_logo">
                        <?php
                        if (!empty($config['show_project_logo']) && $projectPicture !== '') {
                            echo ModalImageHelper::render(
                                'playercareerproject' . $projectId . '-' . $teamId,
                                $projectPicture,
                                $projectName,
                                (int) ($config['project_logo_height'] ?? 0),
                                '',
                                $modalWidth,
                                $modalHeight,
                                $modalMode
                            );
                        }

                        echo HTMLHelper::link($playerRoute, $projectName);
                        ?>
                    </td>
                    <td class="td_l"><?php echo $seasonName; ?></td>

                    <?php if ($showTeam) : ?>
                        <td id="show_plcareer_team">
                            <?php
                            if (!empty($config['show_team_logo']) && $clubPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playercareerteam' . $projectId . '-' . $teamId,
                                    $clubPicture,
                                    $teamName,
                                    (int) ($config['team_logo_height'] ?? 0),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }

                            if (!empty($config['show_team_picture']) && $teamPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playercareerteampicture' . $projectId . '-' . $teamId,
                                    $teamPicture,
                                    $teamName,
                                    (int) ($config['team_picture_height'] ?? 0),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }

                            echo !empty($config['show_playercareer_teamlink'])
                                ? HTMLHelper::link($teamRoute, $teamName)
                                : $teamName;
                            ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($showPersonPicture) : ?>
                        <td id="show_plcareer_ppicture">
                            <?php
                            if ($seasonPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playercareerperson' . $projectId . '-' . $teamId,
                                    $seasonPicture,
                                    $teamName,
                                    (int) ($config['plcareer_ppicture_height'] ?? 0),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="td_l"><?php echo Text::_((string) ($station->position_name ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
