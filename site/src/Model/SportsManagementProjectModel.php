<?php
/**
 * Base model for native Joomla 5/6 SportsManagement project-based site models.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

abstract class SportsManagementProjectModel extends SportsManagementModel
{
    protected int $projectId = 0;
    protected int $divisionId = 0;

    /**
     * Initialize the project-scoped native frontend model from Joomla request state.
     * Calls the base Joomla model constructor and reads the selected project and division as filtered integers, so later lookups share the same scope.
     *
     * @param array $config Joomla MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $input = $this->siteApplication()->getInput();
        $this->projectId = $input->getInt('p', 0);
        $this->divisionId = $input->getInt('division', 0);
    }

    /**
     * Return the project identifier currently assigned to this model.
     * Other project-bound queries use this identifier to constrain database reads.
     *
     * @return int Selected project ID; zero means no project.
     */
    public function getProjectId(): int
    {
        return $this->projectId;
    }

    /**
     * Update the current project context for subsequent model reads.
     * Normalises negative identifiers to zero to prevent invalid project scopes.
     *
     * @param int $projectId Requested project ID.
     * @return void
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = max(0, $projectId);
    }

    /**
     * Return the division selected in the current frontend request.
     * The division is used as the default filter for team and division queries.
     *
     * @return int Selected division ID, or zero when not filtered.
     */
    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    /**
     * Load the selected project with its sports type, league, season and round.
     * Joins the project metadata, creates stable route slugs, resolves the season-specific league logo and returns null when the requested project is absent.
     *
     * @return object|null Enriched project record or null for an invalid or missing project.
     */
    public function getProject(): ?object
    {
        if ($this->projectId <= 0) {
            return null;
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'p.*',
                $db->quoteName('l.country'),
                $db->quoteName('st.id', 'sport_type_id'),
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('st.icon', 'sport_type_picture'),
                $db->quoteName('st.eventtime', 'useeventtime'),
                $db->quoteName('l.picture', 'leaguepicture'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('r.name', 'round_name'),
                $db->quoteName('l.cr_picture', 'cr_leaguepicture'),
                $db->quoteName('l.champions_complete'),
                $db->quoteName('asso.name', 'assoname'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('l.id', 'league_slug_id'),
                $db->quoteName('l.alias', 'league_alias'),
                $db->quoteName('s.id', 'season_slug_id'),
                $db->quoteName('s.alias', 'season_alias'),
                $db->quoteName('r.id', 'round_slug_id'),
                $db->quoteName('r.alias', 'round_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('l.associations'))
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $project->slug = (int) ($project->id ?? 0) . ':' . (string) ($project->project_alias ?? '');
        $project->league_slug = (int) ($project->league_slug_id ?? 0) . ':' . (string) ($project->league_alias ?? '');
        $project->season_slug = (int) ($project->season_slug_id ?? 0) . ':' . (string) ($project->season_alias ?? '');
        $project->round_slug = (int) ($project->round_slug_id ?? 0) . ':' . (string) ($project->round_alias ?? '');

        $sportName = (string) ($project->sport_type_name ?? '');
        $prefix = 'COM_SPORTSMANAGEMENT_ST_';
        $project->fs_sport_type_name = strtolower(str_starts_with($sportName, $prefix) ? substr($sportName, strlen($prefix)) : $sportName);

        $leagueId = (int) $project->league_id;
        $seasonId = (int) $project->season_id;
        $logoQuery = $db->createQuery()
            ->select($db->quoteName('logo_big'))
            ->from($db->quoteName('#__sportsmanagement_league_logos'))
            ->where($db->quoteName('league_id') . ' = :projectLeagueId')
            ->where($db->quoteName('season_id') . ' = :projectSeasonId')
            ->bind(':projectLeagueId', $leagueId, ParameterType::INTEGER)
            ->bind(':projectSeasonId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($logoQuery, 0, 1);
        $seasonLogo = $db->loadResult();

        if ($seasonLogo) {
            $project->leaguepicture = $seasonLogo;
        }

        return $project;
    }

    /**
     * Resolve the identifier of the effective round for this project.
     * Uses the project's manual or automatic round selection and returns zero when no eligible round exists.
     *
     * @return int Current round ID, or zero if none can be resolved.
     */
    public function getCurrentRound(): int
    {
        $round = $this->resolveCurrentRound();
        return $round ? (int) $round->id : 0;
    }

    /**
     * Resolve the competition round number for the effective round.
     * Shares the manual/automatic round selection policy used by getCurrentRound, returning zero when no round exists.
     *
     * @return int Current round code, or zero if unavailable.
     */
    public function getCurrentRoundNumber(): int
    {
        $round = $this->resolveCurrentRound();
        return $round ? (int) $round->roundcode : 0;
    }

    /**
     * List all rounds belonging to the selected project in round-code order.
     * The requested sort direction is restricted to ASC/DESC; when enabled, IDs are converted to Joomla ID:alias slugs for frontend links.
     *
     * @param string $ordering Sort direction, ASC by default or DESC.
     * @param bool $slug Whether to convert round IDs into ID:alias values.
     * @return array List of project rounds, or an empty list without a project.
     */
    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.alias'),
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                "CASE LENGTH(r.name) WHEN 0 THEN r.roundcode ELSE r.name END AS name",
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundsProjectId')
            ->bind(':roundsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        if ($slug) {
            foreach ($rows as $row) {
                $row->id = (int) ($row->id ?? 0) . ':' . (string) ($row->alias ?? '');
            }
        }

        return $rows;
    }

    /**
     * Determine the project's effective round without modifying project state.
     *
     * Applies manual selection or date-based thresholds to project-owned rounds
     * and fixtures. Missing automatic candidates fall back to the configured
     * project round or another project-owned round. The resolved value is
     * returned solely for display: anonymous page views must never write
     * current_round or any other administrator-managed project field.
     *
     * @return object|null Resolved round record, or null when none exists.
     */
    private function resolveCurrentRound(): ?object
    {
        $project = $this->getProject();
        if (!$project) {
            return null;
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $mode = (int) ($project->current_round_auto ?? 0);
        $autoTime = (int) ($project->auto_time ?? 0);
        if ($autoTime <= 0) {
            $autoTime = 7200;
        }
        $currentDate = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        $cutoffAfter = $currentDate->modify('+' . $autoTime . ' minutes')->format('Y-m-d H:i:s');
        $cutoffBefore = $currentDate->modify('-' . $autoTime . ' minutes')->format('Y-m-d H:i:s');

        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :currentRoundProjectId')
            ->bind(':currentRoundProjectId', $projectId, ParameterType::INTEGER);

        switch ($mode) {
            case 0:
                if ((int) ($project->current_round ?? 0) > 0) {
                    $currentRoundId = (int) $project->current_round;
                    $query->where($db->quoteName('r.id') . ' = :currentRoundId')
                        ->bind(':currentRoundId', $currentRoundId, ParameterType::INTEGER);
                }
                break;
            case 1:
                $query->where($db->quoteName('r.round_date_first') . ' < :currentRoundCutoffAfter')
                    ->bind(':currentRoundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 2:
                $query->where($db->quoteName('r.round_date_last') . ' < :currentRoundCutoffAfter')
                    ->bind(':currentRoundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 3:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where($db->quoteName('m.match_date') . ' < :currentRoundCutoffAfter')
                    ->bind(':currentRoundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('m.match_date') . ' DESC');
                break;
            case 4:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where($db->quoteName('m.match_date') . ' < :currentRoundCutoffBefore')
                    ->bind(':currentRoundCutoffBefore', $cutoffBefore, ParameterType::STRING)
                    ->order($db->quoteName('m.match_date') . ' ASC');
                break;
        }

        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();

        if (!$round && (int) ($project->current_round ?? 0) > 0) {
            $fallbackRoundId = (int) $project->current_round;
            $fallbackProjectId = $projectId;
            $fallback = $db->createQuery()
                ->select([
                    $db->quoteName('r.id'),
                    $db->quoteName('r.roundcode'),
                    $db->quoteName('r.alias'),
                ])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.id') . ' = :fallbackRoundId')
                ->where($db->quoteName('r.project_id') . ' = :fallbackProjectId')
                ->bind(':fallbackRoundId', $fallbackRoundId, ParameterType::INTEGER)
                ->bind(':fallbackProjectId', $fallbackProjectId, ParameterType::INTEGER);
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if (!$round) {
            $fallbackProjectId = $projectId;
            $fallback = $db->createQuery()
                ->select([
                    $db->quoteName('r.id'),
                    $db->quoteName('r.roundcode'),
                    $db->quoteName('r.alias'),
                ])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.project_id') . ' = :lastFallbackProjectId')
                ->bind(':lastFallbackProjectId', $fallbackProjectId, ParameterType::INTEGER)
                ->order($db->quoteName('r.roundcode') . (in_array($mode, [0, 2], true) ? ' DESC' : ' ASC'));
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if ($round) {
            $round->round_slug = (int) ($round->id ?? 0) . ':' . (string) ($round->alias ?? '');
        }

        // A site GET must not persist the derived round. An authenticated
        // administrator action is responsible for updating current_round.
        return $round ?: null;
    }

    /**
     * Load the merged configuration for the overall frontend template.
     * Delegates to the project-aware template reader so overrides and XML defaults follow the same precedence as other view templates.
     *
     * @return array<string,mixed> Effective overall-template settings.
     */
    public function getOverallConfig(): array
    {
        return $this->getTemplateConfig('overall');
    }

    /**
     * Resolve effective template parameters for a project-bound view.
     * Reads XML defaults, then the selected project's stored settings or its master-template project, and merges the parsed Registry values with the defaults.
     *
     * @param string $template Logical SportsManagement template name.
     * @return array<string,mixed> Effective settings, falling back to defaults if a stored configuration is unavailable or invalid.
     */
    public function getTemplateConfig(string $template): array
    {
        $defaults = $this->loadDefaultTemplateConfig($template);

        if ($this->projectId <= 0) {
            return $defaults;
        }

        $params = $this->loadSavedTemplateParams($template, $this->projectId);
        if ($params === null) {
            $project = $this->getProject();
            $masterId = (int) ($project->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $this->projectId) {
                $params = $this->loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString((string) $params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Load the statistics configured for the selected project and optional position.
     * Filters requested statistics, joins project position and statistic configuration, then prepares the statistic instances used by legacy and native views.
     *
     * @param mixed $statId Optional statistic ID or collection of IDs to select.
     * @param int $positionId Optional project position identifier.
     * @return array Project statistic definitions available for this selection.
     */
    public function getProjectStats($statId = 0, int $positionId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        if (!class_exists('SMStatistic')) {
            $baseFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/base.php';
            if (!is_file($baseFile)) {
                return [];
            }
            require_once $baseFile;
        }

        $statIds = [];
        foreach ((array) $statId as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $statIds[$id] = $id;
            }
        }

        $statsProjectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('ppos.position_id', 'position_id'),
                $db->quoteName('stat.id'),
                $db->quoteName('stat.name'),
                $db->quoteName('stat.short'),
                $db->quoteName('stat.class'),
                $db->quoteName('stat.icon'),
                $db->quoteName('stat.calculated'),
                $db->quoteName('stat.params'),
                $db->quoteName('stat.baseparams'),
                $db->quoteName('stat.ordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('stat.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('ps.position_id')
                . ' AND ' . $db->quoteName('ppos.project_id') . ' = :statsProjectId')
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ps.position_id'))
            ->where($db->quoteName('stat.published') . ' = 1')
            ->where($db->quoteName('pos.published') . ' = 1')
            ->bind(':statsProjectId', $statsProjectId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('pos.ordering') . ' ASC',
                $db->quoteName('ps.ordering') . ' ASC',
            ]);

        if ($statIds) {
            $query->whereIn($db->quoteName('stat.id'), array_values($statIds), ParameterType::INTEGER);
        }

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = :statsPositionId')
                ->bind(':statsPositionId', $positionId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $stats = [];

        foreach ($rows as $row) {
            try {
                $stat = \SMStatistic::getInstance((string) $row->class);
                if (!$stat) {
                    continue;
                }
                $stat->bind($row);
                $stat->set('position_id', (int) $row->position_id);
            } catch (\Throwable) {
                continue;
            }

            $rowPositionId = (int) $row->position_id;
            $rowStatId = (int) $row->id;
            if ($positionId > 0) {
                $stats[$rowStatId] = $stat;
            } else {
                $stats[$rowPositionId][$rowStatId] = $stat;
            }
        }

        return $stats;
    }

    /**
     * Load team assignments and display metadata for the selected project.
     * Combines project-team records with team, club and playing-ground data and supports optional division and venue filters for ranking and roster views.
     *
     * @param int|null $divisionId Division filter; null uses the current model division.
     * @param int $playgroundId Optional playing-ground filter.
     * @return array Project team records with related display fields.
     */
    public function getProjectTeams(?int $divisionId = null, int $playgroundId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $divisionId ??= $this->divisionId;
        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.standard_playground'),
                $db->quoteName('pt.admin'),
                $db->quoteName('pt.start_points'),
                $db->quoteName('pt.points_finally'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('pt.matches_finally'),
                $db->quoteName('pt.won_finally'),
                $db->quoteName('pt.draws_finally'),
                $db->quoteName('pt.lost_finally'),
                $db->quoteName('pt.homegoals_finally'),
                $db->quoteName('pt.guestgoals_finally'),
                $db->quoteName('pt.diffgoals_finally'),
                $db->quoteName('pt.info'),
                $db->quoteName('pt.reason'),
                $db->quoteName('pt.team_id', 'project_team_team_id'),
                $db->quoteName('pt.checked_out'),
                $db->quoteName('pt.checked_out_time'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.notes'),
                $db->quoteName('t.club_id'),
                $db->quoteName('t.website', 'team_www'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('u.username'),
                $db->quoteName('u.email'),
                $db->quoteName('st.team_id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('c.address', 'club_address'),
                $db->quoteName('c.zipcode', 'club_zipcode'),
                $db->quoteName('c.state', 'club_state'),
                $db->quoteName('c.location', 'club_location'),
                $db->quoteName('c.unique_id'),
                $db->quoteName('c.country', 'club_country'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.phone', 'club_phone'),
                $db->quoteName('c.fax', 'club_fax'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
                $db->quoteName('c.website', 'club_www'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.facebook'),
                $db->quoteName('c.twitter'),
                $db->quoteName('c.instagram'),
                $db->quoteName('c.trikot_home'),
                $db->quoteName('c.trikot_away'),
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('d.shortname', 'division_shortname'),
                $db->quoteName('d.parent_id', 'parent_division_id'),
                $db->quoteName('plg.name', 'playground_name'),
                $db->quoteName('plg.short_name', 'playground_short_name'),
                "COALESCE(NULLIF(t.picture, ''), c.logo_small) AS picture",
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('t.alias', 'team_alias'),
                $db->quoteName('d.alias', 'division_alias'),
                $db->quoteName('c.alias', 'club_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('pt.admin') . ' = ' . $db->quoteName('u.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'plg') . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('pt.standard_playground'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('pt.project_id') . ' = :projectTeamsProjectId')
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->bind(':projectTeamsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name') . ' ASC');

        if ($divisionId > 0) {
            $divisionIds = $this->getDivisionTreeIds($divisionId);
            if (!$divisionIds) {
                return [];
            }
            $query->whereIn($db->quoteName('pt.division_id'), array_values(array_map('intval', $divisionIds)), ParameterType::INTEGER);
        }

        if ($playgroundId > 0) {
            $query->where($db->quoteName('pt.standard_playground') . ' = :projectTeamsPlaygroundId')
                ->bind(':projectTeamsPlaygroundId', $playgroundId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $projectId = (int) ($row->project_id ?? 0);
            $teamId = (int) ($row->id ?? 0);
            $projectTeamId = (int) ($row->projectteamid ?? 0);
            $divisionId = (int) ($row->division_id ?? 0);
            $clubId = (int) ($row->club_id ?? 0);
            $teamAlias = (string) ($row->team_alias ?? '');

            $row->project_slug = $projectId . ':' . (string) ($row->project_alias ?? '');
            $row->team_slug = $teamId . ':' . $teamAlias;
            $row->projectteam_slug = $projectTeamId . ':' . $teamAlias;
            $row->division_slug = $divisionId > 0
                ? $divisionId . ':' . (string) ($row->division_alias ?? '')
                : '';
            $row->club_slug = $clubId > 0
                ? $clubId . ':' . (string) ($row->club_alias ?? '')
                : '';
        }

        return $rows;
    }

    /**
     * Create a team-ID lookup from the project-team results.
     * Reuses the division-aware project team loader and skips rows with invalid IDs before indexing by the underlying team identifier.
     *
     * @param int|null $divisionId Optional division filter.
     * @return array<int,object> Team rows indexed by positive team ID.
     */
    public function getTeamsIndexedById(?int $divisionId = null): array
    {
        $teams = [];
        foreach ($this->getProjectTeams($divisionId) as $team) {
            $teamId = (int) ($team->id ?? 0);
            if ($teamId > 0) {
                $teams[$teamId] = $team;
            }
        }
        return $teams;
    }

    /**
     * Read the project's configured favourite team identifiers.
     *
     * Parses the comma-separated legacy list without PHP's partial integer
     * casts. Skips malformed values, overflows and duplicates, while
     * retaining valid positive decimal IDs with leading zeros.
     *
     * @return array<int,int> Unique positive favourite team IDs.
     */
    public function getFavTeams(): array
    {
        $project = $this->getProject();
        if (!$project) {
            return [];
        }

        $favorites = [];
        foreach (explode(',', (string) ($project->fav_team ?? '')) as $value) {
            $value = trim($value);

            if (strlen($value) > 32 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                continue;
            }

            $digits = ltrim($value, '0');

            if ($digits === '') {
                continue;
            }

            $teamId = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($teamId !== false) {
                $favorites[$teamId] = $teamId;
            }
        }
        return array_values($favorites);
    }

    /**
     * Load a division only if it belongs to the selected project.
     *
     * Validates both project and division IDs before creating a database
     * query. Binds both identifiers so a visitor cannot inspect a different
     * project's division simply by changing the division request parameter.
     *
     * @param int|null $divisionId Division to load; null uses the request selection.
     * @return object|null Division database record or null if unavailable.
     */
    public function getDivision(?int $divisionId = null): ?object
    {
        $divisionId ??= $this->divisionId;

        if ($this->projectId <= 0 || $divisionId <= 0) {
            return null;
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('id') . ' = :divisionId')
            ->where($db->quoteName('project_id') . ' = :divisionProjectId')
            ->bind(':divisionId', $divisionId, ParameterType::INTEGER)
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Collect a project-owned division and all of its descendants.
     *
     * Reads divisions scoped to the selected project and verifies that the
     * requested root exists within that result before walking its children.
     * Invalid, foreign-project and orphan root IDs return an empty set, never
     * a fabricated root ID that could influence later team filters.
     *
     * @param int|null $divisionId Root division; null uses the current selection.
     * @return array<int,int> IDs of the root and its descendants.
     */
    protected function getDivisionTreeIds(?int $divisionId = null): array
    {
        $divisionId ??= $this->divisionId;
        if ($this->projectId <= 0 || $divisionId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('parent_id')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = :divisionTreeProjectId')
            ->bind(':divisionTreeProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $divisions = $db->loadObjectList() ?: [];

        $ownedIds = [];
        foreach ($divisions as $division) {
            if (is_object($division) && (int) ($division->id ?? 0) > 0) {
                $ownedIds[(int) $division->id] = true;
            }
        }

        if (!isset($ownedIds[$divisionId])) {
            return [];
        }

        $ids = [$divisionId];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($divisions as $division) {
                if (!is_object($division)) {
                    continue;
                }

                $id = (int) ($division->id ?? 0);
                $parentId = (int) ($division->parent_id ?? 0);
                if ($id > 0 && in_array($parentId, $ids, true) && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                    $changed = true;
                }
            }
        }

        return $ids;
    }

    /**
     * Read a project's saved configuration text for a particular template.
     * Looks up template_config using bound template and project parameters, preserving null when no override record exists.
     *
     * @param string $template Name of the requested template.
     * @param int $projectId Project whose settings should be read.
     * @return string|null Serialized template parameters, or null when absent.
     */
    private function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = :projectTemplateName')
            ->where($db->quoteName('project_id') . ' = :projectTemplateId')
            ->bind(':projectTemplateName', $template, ParameterType::STRING)
            ->bind(':projectTemplateId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

    /**
     * Load default view settings from the installed XML template definition.
     * Restricts the filename to its basename, parses field defaults from the XML settings file and returns an empty set if the file cannot be loaded.
     *
     * @param string $template Name of the template definition.
     * @return array<string,string> Default setting values keyed by field name.
     */
    private function loadDefaultTemplateConfig(string $template): array
    {
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file)) {
            return [];
        }

        try {
            $xml = simplexml_load_file($file);
        } catch (\Throwable) {
            return [];
        }
        if ($xml === false) {
            return [];
        }

        $defaults = [];
        foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
            $attributes = $field->attributes();
            if (isset($attributes['default'])) {
                $defaults[(string) $attributes['name']] = (string) $attributes['default'];
            }
        }
        return $defaults;
    }
}
