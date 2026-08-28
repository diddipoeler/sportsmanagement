<?php
/** Generic native ranking table renderer. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\RankingPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$rankingGroups = $this->activeRanking;
$tableIdBase = $this->activeTableId !== '' ? $this->activeTableId : 'ranking';
$allowSorting = !empty($this->config['column_sorting']);
$showLastRank = !empty($this->config['last_ranking']);
$showLogo = (string) ($this->config['show_logo_small_table'] ?? 'no_logo') !== 'no_logo';
$logoSetting = (string) ($this->config['show_logo_small_table'] ?? 'no_logo');
$logoHeight = max(1, (int) ($this->config['team_picture_width'] ?? 20));
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$tableClass = trim((string) ($this->config['table_class'] ?? 'table')) . ' ranking-exportable';

$sortLink = function (string $key): string {
    $uri = clone $this->uri;
    $nextDirection = $this->sortOrder === $key && $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
    $uri->setVar('order', $key);
    $uri->setVar('dir', $nextDirection);

    return $uri->toString();
};
$heading = function (string $label, string $sortKey) use ($allowSorting, $sortLink): string {
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    if (!$allowSorting || $sortKey === '') {
        return $label;
    }

    return HTMLHelper::link($sortLink($sortKey), $label);
};
$teamIcon = function (object $team, string $target) use ($logoSetting, $logoHeight, $modalMode): string {
    $flag = static fn (): string => CountryPresentationHelper::flag((string) ($team->club_country ?? $team->country ?? ''));
    $logo = function (string $property) use ($team, $target, $logoHeight, $modalMode): string {
        return TeamLogoHelper::renderVariant(
            $team,
            $property,
            $target,
            $logoHeight,
            $this->modalwidth,
            $this->modalheight,
            $modalMode
        );
    };

    return match ($logoSetting) {
        'country_flag' => $flag(),
        'logo_small_country_flag' => $logo('logo_small') . ' ' . $flag(),
        'country_flag_logo_small' => $flag() . ' ' . $logo('logo_small'),
        'logo_big_country_flag' => $logo('logo_big') . ' ' . $flag(),
        'country_flag_logo_big' => $flag() . ' ' . $logo('logo_big'),
        'projectteam_picture' => $logo('projectteam_picture'),
        'team_picture' => $logo('team_picture'),
        'logo_middle' => $logo('logo_middle'),
        'logo_big' => $logo('logo_big'),
        default => $logo('logo_small'),
    };
};
?>
<?php foreach ($rankingGroups as $divisionId => $ranking) : ?>
    <?php
    $divisionId = (int) $divisionId;
    $ranking = (array) $ranking;
    if ($ranking === []) {
        continue;
    }
    if ($divisionId > 0 && !isset($this->divisions[$divisionId])) {
        continue;
    }
    $division = $divisionId > 0 ? $this->divisions[$divisionId] : null;
    $colors = $this->colorsByDivision[$divisionId] ?? $this->colors;
    $tableId = $tableIdBase . ($divisionId > 0 ? '-' . $divisionId : '-all');
    ?>

    <?php if ($division) : ?>
        <h3 class="h5 mt-3 mb-2"><?php echo $this->escape((string) ($division->name ?? '')); ?></h3>
    <?php endif; ?>

    <div class="table-responsive mb-3">
        <table class="<?php echo $this->escape($tableClass); ?>" id="<?php echo $this->escape($tableId); ?>">
            <thead>
            <tr>
                <th class="text-end"><?php echo $heading(Text::_('COM_SPORTSMANAGEMENT_RANKING_POSITION'), 'rank'); ?></th>
                <?php if ($showLastRank) : ?>
                    <th class="text-center" title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_RANKING_PREVIOUS_RANK')); ?>">±</th>
                    <th class="text-end"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_PREVIOUS_RANK'); ?></th>
                <?php endif; ?>
                <?php if ($showLogo) : ?>
                    <th class="text-center" aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_RANKING_TEAM')); ?>">&nbsp;</th>
                <?php endif; ?>
                <th><?php echo $heading(Text::_('COM_SPORTSMANAGEMENT_RANKING_TEAM'), 'name'); ?></th>
                <?php foreach ($this->columns as $column) : ?>
                    <th class="text-end" title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_' . $column['code'])); ?>">
                        <?php echo $heading((string) $column['label'], (string) $column['sort']); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ranking as $projectTeamId => $rankingTeam) : ?>
                <?php
                $projectTeamId = (int) $projectTeamId;
                $team = $this->teams[$projectTeamId] ?? null;
                if (!$team || !is_object($rankingTeam)) {
                    continue;
                }

                $displayTeam = clone $team;
                $rankingName = trim((string) ($rankingTeam->teamname ?? ''));
                if ($rankingName !== '') {
                    $displayTeam->name = $rankingName;
                }

                $teamId = (int) ($displayTeam->id ?? $displayTeam->team_id ?? 0);
                $isFavourite = in_array($teamId, $this->favteams, true);
                $rank = (int) ($rankingTeam->rank ?? 0);
                $previous = $this->previousRanking[$divisionId][$projectTeamId] ?? null;
                $color = RankingPresentationHelper::colorForRank($rank, $colors);
                $rowStyle = $color !== '' && !empty($this->config['use_background_row_color'])
                    ? 'background-color:' . $color . ';'
                    : '';
                $rankStyle = $color !== '' && empty($this->config['use_background_row_color'])
                    ? 'background-color:' . $color . ';'
                    : '';
                ?>
                <tr class="team<?php echo $projectTeamId; ?>"<?php echo $rowStyle !== '' ? ' style="' . $this->escape($rowStyle) . '"' : ''; ?>>
                    <td class="text-end"<?php echo $rankStyle !== '' ? ' style="' . $this->escape($rankStyle) . '"' : ''; ?>>
                        <?php echo $rank > 0 ? $rank : '&nbsp;'; ?>
                    </td>
                    <?php if ($showLastRank) : ?>
                        <td class="text-center"><?php echo RankingPresentationHelper::trend($rankingTeam, is_object($previous) ? $previous : null); ?></td>
                        <td class="text-end"><?php echo is_object($previous) && isset($previous->rank) ? (int) $previous->rank : '&nbsp;'; ?></td>
                    <?php endif; ?>
                    <?php if ($showLogo) : ?>
                        <td class="text-center">
                            <?php echo $teamIcon($displayTeam, $tableId . '-team-' . $teamId); ?>
                        </td>
                    <?php endif; ?>
                    <td class="text-nowrap">
                        <?php
                        echo TeamPresentationHelper::formatName(
                            $displayTeam,
                            $tableId . '-',
                            $this->config,
                            $isFavourite,
                            $this->project,
                            $this->cfg_which_database,
                            $this->season_id
                        );
                        if (!empty($this->config['show_unique_id']) && trim((string) ($displayTeam->unique_id ?? '')) !== '') {
                            echo ' (' . $this->escape((string) $displayTeam->unique_id) . ')';
                        }
                        ?>
                    </td>

                    <?php foreach ($this->columns as $column) : ?>
                        <?php
                        $code = (string) $column['code'];
                        $value = RankingPresentationHelper::value($rankingTeam, $code);
                        $mode = match ($code) {
                            'WINS' => 1,
                            'TIES', 'DRAWS' => 2,
                            'LOSSES' => 3,
                            default => 0,
                        };
                        if ($mode > 0 && !empty($this->config['show_wdl_teamplan_link'])) {
                            $value = HTMLHelper::link(
                                SiteRouteHelper::view('teamplan', [
                                    'cfg_which_database' => $this->cfg_which_database,
                                    's' => $this->season_id,
                                    'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                                    'tid' => (string) ($displayTeam->team_slug ?? $teamId),
                                    'division' => $divisionId,
                                    'mode' => $mode,
                                    'ptid' => (string) ($displayTeam->projectteam_slug ?? $projectTeamId),
                                ]),
                                $value
                            );
                        }
                        ?>
                        <td class="text-end text-nowrap"><?php echo $value !== '' ? $value : '&nbsp;'; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>
