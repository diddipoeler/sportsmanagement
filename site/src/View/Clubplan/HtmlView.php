<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Clubplan view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubplanModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ClubplanViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $favteams = [];
    public array $favoriteSettings = [];
    public array $matchReferees = [];
    public ?object $club = null;
    public int $clubId = 0;
    public int $databaseSelector = 0;
    public int $seasonId = 0;
    public int $type = 0;
    public int $teamartsel = 0;
    public int $teamprojectssel = 0;
    public int $teamseasonssel = 0;
    public array $allmatches = [];
    public array $homematches = [];
    public array $awaymatches = [];
    public array $matches = [];
    public string $startdate = '';
    public string $enddate = '';
    public array $teams = [];
    public array $teamart = [];
    public array $teamprojects = [];
    public array $teamseasons = [];
    public array $lists = [];

    /**
     * Disable legacy presentation dependencies in the club schedule view.
     * @return bool False because the native view supplies its own services.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Do not require jQuery for the base club schedule presentation.
     * @return bool False for the default page layout.
     */
    protected function requiresJquery(): bool
    {
        return false;
    }

    /**
     * Check whether the configured historical media box is needed.
     * @return bool True only when the legacy modal mode is selected.
     */
    protected function requiresJceMediaBox(): bool
    {
        return (int) ($this->overallconfig['use_jquery_modal'] ?? 0) === 2;
    }

    /**
     * Build the club fixture schedule and its interactive filters.
     * Requires the native ClubplanModel, selects the configured club and
     * match type, loads home/away fixtures and optional referee details,
     * then prepares selection lists, RSS metadata and Joomla web assets.
     * @return void
     * @throws \RuntimeException If ClubplanModel is unavailable.
     */
    protected function prepareView(): void
    {
        /** @var ClubplanModel $model */
        $model = $this->getModel();
        if (!$model instanceof ClubplanModel) {
            throw new \RuntimeException('Clubplan view requires ClubplanModel.', 500);
        }

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseScript(
                'com_sportsmanagement.clubplan.filters',
                Uri::root(true) . '/components/com_sportsmanagement/assets/js/clubplan-filters.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
        }

        $this->databaseSelector = ClubplanModel::$cfg_which_database === 1 ? 1 : 0;
        $this->seasonId = $this->input->getInt('s', 0);
        $this->favteams = $model->getFavTeams();
        $this->club = $model->getClub();
        $this->clubId = (int) ($this->club->id ?? ClubplanModel::$clubid);
        $this->type = ClubplanModel::$type;
        $this->teamartsel = ClubplanModel::$teamartsel;
        $this->teamprojectssel = ClubplanModel::$teamprojectssel;
        $this->teamseasonssel = ClubplanModel::$teamseasonssel;

        if ($this->teamprojectssel > 0) {
            ClubplanModel::$project_id = $this->teamprojectssel;
        }
        if ($this->teamseasonssel > 0 || $this->teamartsel > 0) {
            ClubplanModel::$project_id = 0;
        }

        if ($this->type <= 0) {
            $this->type = (int) ($this->config['type_matches'] ?? 4);
        } else {
            $this->config['type_matches'] = $this->type;
        }

        $matchType = (int) ($this->config['type_matches'] ?? 4);
        $orderBy = (string) ($this->config['MatchesOrderBy'] ?? 'ASC');
        switch ($matchType) {
            case 1:
                $this->homematches = $model->getAllMatches($orderBy, 1);
                break;
            case 2:
                $this->awaymatches = $model->getAllMatches($orderBy, 2);
                break;
            case 0:
            case 3:
            case 4:
                $this->allmatches = $model->getAllMatches($orderBy, $matchType);
                break;
            default:
                $this->homematches = $model->getAllMatches($orderBy, 1);
                $this->awaymatches = $model->getAllMatches($orderBy, 2);
                break;
        }

        $this->preparePresentationData();

        $this->startdate = $model->getStartDate();
        $this->enddate = $model->getEndDate();
        $this->teams = $model->getTeams();
        $this->teamart = $model->getTeamsArt();
        $this->teamprojects = $model->getTeamsProjects();
        $this->teamseasons = $model->getTeamsSeasons();
        $this->lists = $this->buildLists();

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TITLE');
        if ($this->club && isset($this->club->name)) {
            $pageTitle .= ': ' . $this->club->name;
        }
        $document->setTitle($pageTitle);

        $projectId = $this->project && !empty($this->project->id) ? '&p=' . (int) $this->project->id : '';
        $clubId = $this->club && !empty($this->club->id) ? '&cid=' . (int) $this->club->id : '';
        $rssVar = $clubId !== '' ? $clubId : $projectId;
        $feed = 'index.php?option=com_sportsmanagement&view=clubplan' . $rssVar . '&format=feed';
        if ($document instanceof HtmlDocument) {
            $document->addHeadLink(
                Route::_($feed . '&type=rss'),
                'alternate',
                'rel',
                ['type' => 'application/rss+xml', 'title' => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_RSSFEED')]
            );
        }

        $clubName = $this->club && isset($this->club->name) ? (string) $this->club->name : '';
        $this->headertitle = trim(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PAGE_TITLE') . ' ' . $clubName);
        $this->config['table_class'] = $this->config['table_class'] ?? 'table';
    }

    /**
     * Prepare extra match information in batched model queries.
     * Collects unique valid fixture and project IDs, loads favourite team
     * settings and optionally assigns referees for the displayed matches.
     * @return void
     */
    private function preparePresentationData(): void
    {
        $matches = [];
        foreach ([$this->allmatches, $this->homematches, $this->awaymatches] as $matchList) {
            foreach ($matchList as $match) {
                $matchId = (int) ($match->match_id ?? $match->id ?? 0);
                if ($matchId > 0) {
                    $matches[$matchId] = $match;
                }
            }
        }

        if ($matches === []) {
            return;
        }

        $projectIds = [];
        foreach ($matches as $match) {
            $projectId = (int) ($match->project_id ?? $match->prid ?? 0);
            if ($projectId > 0) {
                $projectIds[$projectId] = $projectId;
            }
        }

        $viewDataModel = new ClubplanViewDataModel();
        $viewDataModel->setDatabaseSelector($this->databaseSelector);
        $this->favoriteSettings = $viewDataModel->getFavoriteSettings(array_values($projectIds));

        if (!empty($this->config['show_referee'])) {
            $this->matchReferees = $viewDataModel->getMatchReferees(array_keys($matches));
        }
    }

    /**
     * Build Joomla select options for team categories, projects and seasons.
     * Also prepares the home, away and combined match-type choices.
     * @return array<string,array> Filter options for the club schedule.
     */
    private function buildLists(): array
    {
        $fromTeamArt = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAMART'))];
        $fromTeamArt = array_merge($fromTeamArt, $this->teamart);

        $fromTeamProjects = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT'))];
        $fromTeamProjects = array_merge($fromTeamProjects, $this->teamprojects);

        $fromTeamSeasons = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASON'))];
        $fromTeamSeasons = array_merge($fromTeamSeasons, $this->teamseasons);

        $types = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_ALL')),
            HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_HOME')),
            HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_AWAY')),
        ];

        return [
            'fromteamart' => $fromTeamArt,
            'fromteamprojects' => $fromTeamProjects,
            'fromteamseasons' => $fromTeamSeasons,
            'type' => $types,
        ];
    }
}
