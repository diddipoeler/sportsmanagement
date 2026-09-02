<?php
/**
 * Joomla 5/6 Teaminfo simple-league history layout.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$databaseSelector = $this->input->getInt('cfg_which_database', 0);
$seasonId = $this->input->getInt('s', 0);
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$params = ComponentHelper::getParams('com_sportsmanagement');
$pictureBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : '';
$imageUrl = static function (string $picture) use ($pictureBase): string {
    $picture = trim($picture);

    if ($picture === '' || preg_match('#^https?://#i', $picture)) {
        return $picture;
    }

    return rtrim($pictureBase, '/') . '/' . ltrim($picture, '/');
};

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SIMPLE_LEAGUE')];
echo $this->loadTemplate('jsm_notes');

$mediaWikiTable = [
    '{| class="wikitable sortable"',
    '|+ Ligenzugehörigkeit ' . (string) ($this->team->tname ?? ''),
    '|-',
    '! Saison !! Liga !! Platz !! gespielt !! gewonnen !! unentschieden !! verloren !! Tore !! Punkte',
];

foreach ($this->seasons as $season) {
    if (($season->project_type ?? '') !== 'SIMPLE_LEAGUE') {
        continue;
    }

    $mediaWikiTable[] = '|-';
    $mediaWikiTable[] = '|' . $season->season
        . '||' . $season->league
        . '||' . $season->rank
        . '||' . $season->matches_finally
        . '||' . $season->won_finally
        . '||' . $season->draws_finally
        . '||' . $season->lost_finally
        . '||' . $season->homegoals_finally . '-' . $season->guestgoals_finally
        . '||' . $season->points_finally . ':' . $season->neg_points_finally;
}
$mediaWikiTable[] = '|}';
$mediaWikiContent = implode("\n", $mediaWikiTable);

$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.teaminfo-mediawiki',
    'components/com_sportsmanagement/assets/js/teaminfo-mediawiki.js',
    ['version' => 'auto'],
    ['defer' => true]
);
?>
<button
    type="button"
    class="btn btn-outline-secondary mb-3"
    data-jsm-mediawiki
    data-jsm-mediawiki-content="<?php echo $escape($mediaWikiContent); ?>"
>
    <?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/mediawiki.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_MEDIAWIKI'), ['width' => 40]); ?>
    Mediawiki
</button>

<div class="table-responsive">
    <table class="<?php echo $escape($this->config['table_class']); ?> align-middle">
        <thead>
            <tr>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                <?php if (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE') : ?>
                    <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_DIVISION'); ?></th>
                <?php endif; ?>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_RANK'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_POINTS'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS'); ?></th>
                <?php if (!empty($this->config['show_teams_roster_mean_age'])) : ?>
                    <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE'); ?></th>
                <?php endif; ?>
                <?php if (!empty($this->config['show_teams_roster_market_value'])) : ?>
                    <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->seasons as $season) : ?>
                <?php if (($season->project_type ?? '') !== 'SIMPLE_LEAGUE') : ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php
                $commonRoute = [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => $season->project_slug,
                ];
                $rankingLink = SiteRouteHelper::view('ranking', $commonRoute + [
                    'type' => 0,
                    'r' => $season->round_slug,
                    'from' => 0,
                    'to' => 0,
                    'division' => $season->division_slug,
                ]);
                $resultsLink = SiteRouteHelper::view('results', $commonRoute + [
                    'r' => $season->round_slug,
                    'division' => $season->division_slug,
                    'mode' => '',
                    'order' => '',
                    'layout' => '',
                ]);
                $teamplanLink = SiteRouteHelper::view('teamplan', $commonRoute + [
                    'tid' => $this->team->slug,
                    'division' => $season->division_slug,
                    'mode' => 0,
                    'ptid' => $season->ptid,
                ]);
                $teamstatsLink = SiteRouteHelper::view('teamstats', $commonRoute + [
                    'tid' => $this->team->slug,
                ]);
                $playersLink = SiteRouteHelper::view('roster', $commonRoute + [
                    'tid' => $season->team_slug,
                    'ptid' => $season->ptid,
                ]);

                $historyPicture = trim((string) ($season->season_picture ?? ''));
                if ($historyPicture === '') {
                    $historyPicture = trim((string) $params->get('ph_team', ''));
                }
                $historyPicture = $imageUrl($historyPicture);
                ?>
                <tr>
                    <td><?php echo $escape($season->season); ?></td>
                    <td><?php echo JSMCountries::getCountryFlag($season->leaguecountry); ?> <?php echo $escape($season->league); ?></td>
                    <td>
                        <?php if (!empty($this->config['show_team_hist_picture'])) : ?>
                            <?php
                            echo ModalImageHelper::render(
                                'teaminfohistory' . (int) $season->ptid . '-' . (int) $season->projectid,
                                $historyPicture,
                                (string) ($this->team->name ?? $this->team->tname ?? ''),
                                50,
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        <?php else : ?>
                            <?php
                            echo ModalImageHelper::render(
                                'teaminfohistory' . (int) $season->ptid . '-' . (int) $season->projectid,
                                'media/com_sportsmanagement/jl_images/icon_copyright_2.png',
                                (string) ($this->team->name ?? $this->team->tname ?? ''),
                                50,
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        <?php endif; ?>

                        <?php if ($this->showediticon) : ?>
                            <?php
                            $editLink = 'index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=' . (int) $season->ptid
                                . '&tid=' . (int) $this->teamid
                                . '&p=' . (int) $season->projectid;
                            echo ModalImageHelper::render(
                                'teamedit' . (int) $season->ptid,
                                'administrator/components/com_sportsmanagement/assets/images/teams.png',
                                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
                                20,
                                $editLink,
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        <?php endif; ?>
                    </td>
                    <?php if (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE') : ?>
                        <td><?php echo $escape($season->division_name); ?></td>
                    <?php endif; ?>
                    <td><?php echo !empty($this->config['show_teams_ranking_link']) ? HTMLHelper::link($rankingLink, $escape($season->rank)) : $escape($season->rank); ?></td>
                    <td><?php echo $escape($season->games); ?></td>
                    <td><?php echo !empty($this->config['show_teams_results_link']) ? HTMLHelper::link($resultsLink, $escape($season->points)) : $escape($season->points); ?></td>
                    <td><?php echo !empty($this->config['show_teams_teamplan_link']) ? HTMLHelper::link($teamplanLink, $escape($season->series)) : $escape($season->series); ?></td>
                    <td><?php echo !empty($this->config['show_teams_teamstats_link']) ? HTMLHelper::link($teamstatsLink, $escape($season->goals)) : $escape($season->goals); ?></td>
                    <td><?php echo !empty($this->config['show_teams_roster_link']) ? HTMLHelper::link($playersLink, $escape($season->playercnt)) : $escape($season->playercnt); ?></td>
                    <?php if (!empty($this->config['show_teams_roster_mean_age'])) : ?>
                        <td class="text-end"><?php echo HTMLHelper::link($playersLink, $escape($season->playermeanage)); ?></td>
                    <?php endif; ?>
                    <?php if (!empty($this->config['show_teams_roster_market_value'])) : ?>
                        <td class="text-end"><?php echo HTMLHelper::link($playersLink, $escape(number_format((float) $season->market_value, 0, ',', '.'))); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
